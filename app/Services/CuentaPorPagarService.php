<?php

namespace App\Services;

use App\Models\Compra;
use App\Models\PagoCuentaPorPagar;
use App\Models\SesionCaja;
use App\Models\MovimientoCaja;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class CuentaPorPagarService
{
    /**
     * Registrar un abono o pago a una factura de compra a crédito
     *
     * @param array $data ['compra_id', 'monto', 'metodo_pago', 'banco', 'numero_referencia', 'observaciones']
     * @return PagoCuentaPorPagar
     * @throws Exception
     */
    public function registrarAbono(array $data): PagoCuentaPorPagar
    {
        return DB::transaction(function () use ($data) {
            $compra = Compra::with(['proveedor'])
                ->where('id', $data['compra_id'])
                ->lockForUpdate()
                ->firstOrFail();

            if ($compra->condicion_pago !== 'credito') {
                throw new Exception("La compra #{$compra->id} fue registrada de contado.");
            }

            if ($compra->estado === 'anulada') {
                throw new Exception("No se pueden registrar pagos a una compra anulada.");
            }

            $monto = round((float) $data['monto'], 2);
            if ($monto <= 0) {
                throw new Exception("El monto a abonar debe ser mayor a cero.");
            }

            $saldoActual = (float) $compra->saldo_pendiente;
            if ($monto > ($saldoActual + 0.01)) {
                throw new Exception("El monto a abonar (" . formato_moneda($monto) . ") supera el saldo pendiente (" . formato_moneda($saldoActual) . ").");
            }

            $userId = Auth::id() ?? 1;

            // Sesión de caja abierta del usuario
            $sesionCaja = SesionCaja::where('user_id', $userId)
                ->where('estado', 'abierta')
                ->first();

            // Consecutivo único de pago
            $consecutivo = PagoCuentaPorPagar::whereDate('created_at', now()->toDateString())->count() + 1;
            $numeroPago = 'PAG-' . now()->format('Ymd') . '-' . str_pad($consecutivo, 4, '0', STR_PAD_LEFT);

            // Crear registro de abono
            $pago = PagoCuentaPorPagar::create([
                'compra_id'         => $compra->id,
                'user_id'           => $userId,
                'sesion_caja_id'    => $sesionCaja?->id,
                'numero_pago'       => $numeroPago,
                'monto'             => $monto,
                'metodo_pago'       => $data['metodo_pago'] ?? 'efectivo',
                'banco'             => $data['banco'] ?? null,
                'numero_referencia' => $data['numero_referencia'] ?? null,
                'fecha_pago'        => !empty($data['fecha_pago']) ? $data['fecha_pago'] : now(),
                'observaciones'     => $data['observaciones'] ?? null,
            ]);

            // Actualizar saldo pendiente y estado de la compra
            $nuevoSaldo = max(0, round($saldoActual - $monto, 2));
            $nuevoEstado = $nuevoSaldo <= 0.001 ? 'pagado' : 'parcial';

            $compra->update([
                'saldo_pendiente' => $nuevoSaldo,
                'estado_pago'     => $nuevoEstado,
            ]);

            // Si el pago es en efectivo y hay caja abierta, registrar movimiento de egreso
            if ($pago->metodo_pago === 'efectivo' && $sesionCaja && !empty($data['registrar_en_caja'])) {
                MovimientoCaja::create([
                    'sesion_caja_id'         => $sesionCaja->id,
                    'user_id'                => $userId,
                    'tipo'                   => 'egreso',
                    'monto'                  => $monto,
                    'concepto'               => "Pago a Proveedor {$compra->proveedor->nombre_empresa} - Recibo {$numeroPago} (Factura #{$compra->numero_comprobante})",
                    'comprobante_referencia' => $numeroPago,
                ]);
            }

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
     * Resumen general y métricas de cuentas por pagar
     *
     * @return array
     */
    public function getMetricas(): array
    {
        $cuentasPendientes = Compra::where('condicion_pago', 'credito')
            ->where('estado', 'recibida')
            ->whereIn('estado_pago', ['pendiente', 'parcial', 'vencido'])
            ->where('saldo_pendiente', '>', 0)
            ->get();

        $totalDeuda = 0;
        $deudaVencida = 0;
        $deudaPorVencer7Dias = 0;
        $totalFacturasPendientes = $cuentasPendientes->count();

        $hoy = now()->startOfDay();

        foreach ($cuentasPendientes as $c) {
            $saldo = (float) $c->saldo_pendiente;
            $totalDeuda += $saldo;

            if ($c->fecha_vencimiento_pago) {
                $venc = $c->fecha_vencimiento_pago->startOfDay();
                if ($venc->lt($hoy)) {
                    $deudaVencida += $saldo;
                } elseif ($venc->diffInDays($hoy) <= 7) {
                    $deudaPorVencer7Dias += $saldo;
                }
            }
        }

        return [
            'total_deuda'               => $totalDeuda,
            'deuda_vencida'             => $deudaVencida,
            'deuda_por_vencer_7_dias'   => $deudaPorVencer7Dias,
            'total_facturas_pendientes' => $totalFacturasPendientes,
        ];
    }
}
