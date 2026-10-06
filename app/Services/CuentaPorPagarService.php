<?php

namespace App\Services;

use App\Models\Compra;
use App\Models\PagoCuentaPorPagar;
use App\Models\SesionCaja;
use App\Models\MovimientoCaja;
use App\Models\Proveedor;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class CuentaPorPagarService
{
    /**
     * Registrar un abono o pago a una factura de compra a crédito de forma atómica y auditada.
     *
     * @param array $data ['compra_id', 'monto', 'metodo_pago', 'banco', 'numero_referencia', 'fecha_pago', 'observaciones', 'registrar_en_caja']
     * @param int $userId
     * @return PagoCuentaPorPagar
     * @throws Exception
     */
    public function registrarAbono(array $data, int $userId): PagoCuentaPorPagar
    {
        return DB::transaction(function () use ($data, $userId) {
            $compra = Compra::with(['proveedor'])
                ->where('id', $data['compra_id'])
                ->lockForUpdate()
                ->firstOrFail();

            if ($compra->condicion_pago !== 'credito') {
                throw new Exception("La compra #{$compra->id} fue registrada de contado y no admite abonos a crédito.");
            }

            if ($compra->estado === 'anulada') {
                throw new Exception("No se pueden registrar pagos a una compra que se encuentra anulada.");
            }

            $monto = round((float) $data['monto'], 2);
            if ($monto <= 0) {
                throw new Exception("El monto a abonar debe ser mayor a C$ 0.00.");
            }

            $saldoActual = (float) $compra->saldo_pendiente;
            if ($monto > ($saldoActual + 0.009)) {
                throw new Exception("El monto a abonar (" . formato_moneda($monto) . ") supera el saldo adeudado (" . formato_moneda($saldoActual) . ").");
            }

            // Sesión de caja activa del usuario (para egresos en efectivo)
            $sesionCaja = SesionCaja::where('user_id', $userId)
                ->where('estado', 'abierta')
                ->first();

            $numeroPago = $this->generarNumeroPago();
            $fechaPago = !empty($data['fecha_pago']) ? Carbon::parse($data['fecha_pago']) : now();

            // 1. Crear registro inmutable de abono
            $pago = PagoCuentaPorPagar::create([
                'compra_id'         => $compra->id,
                'user_id'           => $userId,
                'sesion_caja_id'    => $sesionCaja?->id,
                'numero_pago'       => $numeroPago,
                'monto'             => $monto,
                'metodo_pago'       => $data['metodo_pago'] ?? 'efectivo',
                'banco'             => !empty($data['banco']) ? trim($data['banco']) : null,
                'numero_referencia' => !empty($data['numero_referencia']) ? trim($data['numero_referencia']) : null,
                'fecha_pago'        => $fechaPago,
                'observaciones'     => !empty($data['observaciones']) ? trim($data['observaciones']) : null,
            ]);

            // 2. Actualizar saldo pendiente y estado de la factura
            $nuevoSaldo = max(0, round($saldoActual - $monto, 2));
            $nuevoEstado = $nuevoSaldo <= 0.001 ? 'pagado' : 'parcial';

            $compra->update([
                'saldo_pendiente' => $nuevoSaldo,
                'estado_pago'     => $nuevoEstado,
            ]);

            $nombreProveedor = $compra->proveedor->nombre ?? 'Proveedor';

            // 3. Si el pago es en efectivo y se solicitó registro en caja
            if ($pago->metodo_pago === 'efectivo' && !empty($data['registrar_en_caja'])) {
                if ($sesionCaja) {
                    MovimientoCaja::create([
                        'sesion_caja_id'         => $sesionCaja->id,
                        'user_id'                => $userId,
                        'tipo'                   => 'egreso',
                        'monto'                  => $monto,
                        'concepto'               => "Abono CxP a Proveedor {$nombreProveedor} - Recibo {$numeroPago} (Factura #{$compra->numero_comprobante})",
                        'comprobante_referencia' => $numeroPago,
                    ]);
                } else {
                    Log::warning("Abono {$numeroPago} solicitado para registro en caja pero el usuario {$userId} no tiene sesión de caja abierta.");
                }
            }

            // 4. Auditoría de seguridad
            AuditLog::log('compras', 'abono_cuenta_por_pagar', "Abono registrado a factura {$compra->numero_comprobante}: " . formato_moneda($monto), [
                'pago_id'         => $pago->id,
                'numero_pago'     => $numeroPago,
                'compra_id'       => $compra->id,
                'proveedor_id'    => $compra->proveedor_id,
                'monto'           => $monto,
                'saldo_anterior'  => $saldoActual,
                'saldo_restante'  => $nuevoSaldo,
                'estado_result'   => $nuevoEstado,
                'metodo_pago'     => $pago->metodo_pago,
                'sesion_caja_id'  => $sesionCaja?->id,
            ]);

            Log::info("Pago {$numeroPago} registrado para compra #{$compra->id}. Nuevo saldo: {$nuevoSaldo}", [
                'pago_id'   => $pago->id,
                'compra_id' => $compra->id,
                'monto'     => $monto,
                'saldo'     => $nuevoSaldo,
            ]);

            return $pago->load(['compra.proveedor', 'usuario']);
        });
    }

    /**
     * Registrar una Cuenta por Pagar directa (gastos de servicios, insumos o acreedores)
     * garantizando CERO efectos en Kardex ni lotes de inventario.
     *
     * @param array $data
     * @param int $userId
     * @return Compra
     * @throws Exception
     */
    public function registrarDirecta(array $data, int $userId): Compra
    {
        return DB::transaction(function () use ($data, $userId) {
            $proveedorId = $data['proveedor_id'] ?? null;
            if (empty($proveedorId)) {
                $nombreProv = trim($data['proveedor_nombre'] ?? '');
                if (empty($nombreProv)) {
                    throw new Exception("Debe especificar o seleccionar un proveedor o acreedor para la cuenta por pagar.");
                }
                $proveedor = Proveedor::firstOrCreate(
                    ['nombre' => $nombreProv],
                    ['activo' => true]
                );
                $proveedorId = $proveedor->id;
            }

            $diasCredito = (int) ($data['dias_credito'] ?? 30);
            $fecha = !empty($data['fecha']) ? Carbon::parse($data['fecha']) : now();
            $fechaVencimiento = !empty($data['fecha_vencimiento_pago'])
                ? Carbon::parse($data['fecha_vencimiento_pago'])
                : (clone $fecha)->addDays($diasCredito);

            $numComp = trim($data['numero_comprobante']);
            if (!empty($data['concepto'])) {
                $numComp = substr($numComp . ' (' . trim($data['concepto']) . ')', 0, 50);
            }

            $total = round((float) $data['total'], 2);
            if ($total <= 0) {
                throw new Exception("El monto total de la cuenta por pagar debe ser mayor a C$ 0.00.");
            }

            // Invariante: Se registra en compras pero SIN detalles ni lotes asociados
            $compra = Compra::create([
                'proveedor_id'           => $proveedorId,
                'user_id'                => $userId,
                'numero_comprobante'     => $numComp,
                'subtotal'               => $total,
                'impuesto'               => 0,
                'total'                  => $total,
                'condicion_pago'         => 'credito',
                'dias_credito'           => $diasCredito,
                'fecha_vencimiento_pago' => $fechaVencimiento,
                'saldo_pendiente'        => $total,
                'estado_pago'            => 'pendiente',
                'estado'                 => 'recibida',
                'fecha'                  => $fecha,
            ]);

            AuditLog::log('compras', 'crear_cuenta_por_pagar_directa', "Cuenta por pagar directa registrada: {$compra->numero_comprobante}", [
                'compra_id'           => $compra->id,
                'proveedor_id'        => $compra->proveedor_id,
                'numero_comprobante'  => $compra->numero_comprobante,
                'total'               => $compra->total,
                'fecha_vencimiento'   => $fechaVencimiento->toDateString(),
            ]);

            return $compra->load(['proveedor', 'usuario']);
        });
    }

    /**
     * Resumen general y métricas de cuentas por pagar optimizadas en SQL de alto rendimiento.
     *
     * @return array
     */
    public function getMetricas(): array
    {
        $hoy = now()->toDateString();
        $enSieteDias = now()->addDays(7)->toDateString();

        $stats = Compra::where('condicion_pago', 'credito')
            ->where('estado', 'recibida')
            ->where('saldo_pendiente', '>', 0)
            ->selectRaw("
                COALESCE(SUM(saldo_pendiente), 0) as total_deuda,
                COUNT(*) as total_facturas_pendientes,
                COALESCE(SUM(CASE WHEN fecha_vencimiento_pago < ? THEN saldo_pendiente ELSE 0 END), 0) as deuda_vencida,
                COALESCE(SUM(CASE WHEN fecha_vencimiento_pago >= ? AND fecha_vencimiento_pago <= ? THEN saldo_pendiente ELSE 0 END), 0) as deuda_por_vencer_7_dias
            ", [$hoy, $hoy, $enSieteDias])
            ->first();

        return [
            'total_deuda'               => (float) ($stats->total_deuda ?? 0),
            'deuda_vencida'             => (float) ($stats->deuda_vencida ?? 0),
            'deuda_por_vencer_7_dias'   => (float) ($stats->deuda_por_vencer_7_dias ?? 0),
            'total_facturas_pendientes' => (int) ($stats->total_facturas_pendientes ?? 0),
        ];
    }

    /**
     * Generar número correlativo consecutivo PAG-YYYYMMDD-NNNN blindado contra concurrencia.
     *
     * @return string
     */
    public function generarNumeroPago(): string
    {
        $fechaPrefix = now()->format('Ymd');

        $ultimoPago = PagoCuentaPorPagar::where('numero_pago', 'like', "PAG-{$fechaPrefix}-%")
            ->lockForUpdate()
            ->orderBy('id', 'desc')
            ->first();

        $consecutivo = 1;
        if ($ultimoPago && preg_match('/PAG-\d{8}-(\d+)/', $ultimoPago->numero_pago, $matches)) {
            $consecutivo = ((int) $matches[1]) + 1;
        } else {
            $consecutivo = PagoCuentaPorPagar::whereDate('created_at', now()->toDateString())->count() + 1;
        }

        return sprintf('PAG-%s-%04d', $fechaPrefix, $consecutivo);
    }
}
