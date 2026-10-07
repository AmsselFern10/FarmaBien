<?php

namespace App\Services;

use App\Models\DevolucionCompra;
use App\Models\DetalleDevolucionCompra;
use App\Models\Lote;
use App\Models\Proveedor;
use App\Models\Compra;
use App\Models\MovimientoInventario;
use App\Models\RegistroVentaControlado;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Services\NotificacionService;
use Exception;

class DevolucionCompraService
{
    /**
     * Registrar una devolución formal a proveedor de manera atómica,
     * descontando stock de lotes, asentando Kardex y Libro MINSA.
     *
     * @param array $data
     * @param int $userId
     * @return DevolucionCompra
     * @throws Exception
     */
    public function registrarDevolucion(array $data, int $userId): DevolucionCompra
    {
        return DB::transaction(function () use ($data, $userId) {
            $proveedorId = (int) $data['proveedor_id'];
            $proveedor = Proveedor::findOrFail($proveedorId);
            if (!$proveedor->activo) {
                throw new Exception("El proveedor '{$proveedor->nombre}' se encuentra inactivo.");
            }

            $compra = null;
            if (!empty($data['compra_id'])) {
                $compra = Compra::where('id', $data['compra_id'])->lockForUpdate()->first();
                if ($compra && $compra->estado === 'anulada') {
                    throw new Exception("No se pueden registrar devoluciones sobre una compra que ya está anulada.");
                }
            }

            $numeroDevolucion = $this->generarNumeroDevolucion();

            $devolucion = DevolucionCompra::create([
                'numero_devolucion' => $numeroDevolucion,
                'proveedor_id'      => $proveedor->id,
                'compra_id'         => $compra?->id,
                'usuario_id'        => $userId,
                'estado'            => 'confirmada', // Estado efectivo inmediato con salida de inventario
                'motivo'            => trim($data['motivo']),
                'total_devolucion'  => 0,
            ]);

            $totalAcumulado = 0;

            foreach ($data['items'] as $index => $item) {
                $loteId = (int) $item['lote_id'];
                $cantidad = (int) $item['cantidad'];

                if ($cantidad <= 0) {
                    continue;
                }

                $lote = Lote::with(['producto.laboratorio'])
                    ->where('id', $loteId)
                    ->lockForUpdate()
                    ->firstOrFail();

                $producto = $lote->producto;
                if (!$producto) {
                    throw new Exception("El lote {$lote->numero_lote} no tiene un producto asociado.");
                }

                // 1. Validar disponibilidad física actual del lote
                if ($cantidad > $lote->stock_actual) {
                    throw new Exception(
                        "Stock insuficiente para el lote '{$lote->numero_lote}' del producto '{$producto->nombre}'. "
                      . "Stock actual disponible: {$lote->stock_actual}, solicitado a devolver: {$cantidad}."
                    );
                }

                // 2. Validar que no se devuelva más de lo que originalmente ingresó en la compra (si aplica)
                if ($compra) {
                    $totalYaDevuelto = (int) DetalleDevolucionCompra::whereHas('devolucionCompra', function ($q) {
                        $q->where('estado', '!=', 'rechazada');
                    })->where('lote_id', $lote->id)->sum('cantidad');

                    $maxDevolvible = max(0, $lote->stock_inicial - $totalYaDevuelto);
                    if ($cantidad > $maxDevolvible) {
                        throw new Exception(
                            "La cantidad a devolver ({$cantidad}) supera el remanente disponible de la compra para el lote '{$lote->numero_lote}' (Máx: {$maxDevolvible})."
                        );
                    }
                }

                $precioUnitario = (float) ($item['precio_unitario'] ?? $lote->precio_compra ?? 0);
                $subtotal = round($precioUnitario * $cantidad, 2);
                $totalAcumulado += $subtotal;

                // 3. Crear línea de detalle
                DetalleDevolucionCompra::create([
                    'devolucion_compra_id' => $devolucion->id,
                    'lote_id'              => $lote->id,
                    'producto_id'          => $producto->id,
                    'cantidad'             => $cantidad,
                    'precio_unitario'      => $precioUnitario,
                    'subtotal'             => $subtotal,
                    'motivo_detalle'       => !empty($item['motivo_detalle']) ? trim($item['motivo_detalle']) : null,
                ]);

                // 4. Descontar stock del lote
                $stockAnterior = (int) $lote->stock_actual;
                $stockPosterior = max(0, $stockAnterior - $cantidad);

                $lote->stock_actual = $stockPosterior;
                if ($stockPosterior === 0) {
                    $lote->activo = false;
                }
                $lote->save();

                // 5. Asentar salida en Kardex
                $movKardex = MovimientoInventario::create([
                    'producto_id'      => $producto->id,
                    'lote_id'          => $lote->id,
                    'user_id'          => $userId,
                    'tipo'             => 'salida',
                    'subtipo'          => 'ajuste_manual',
                    'cantidad'         => -$cantidad,
                    'stock_anterior'   => $stockAnterior,
                    'stock_posterior'  => $stockPosterior,
                    'costo_unitario'   => $precioUnitario,
                    'costo_total'      => $subtotal,
                    'origen'           => 'devolucion_compra',
                    'origen_id'        => $devolucion->id,
                    'motivo'           => "Devolución a proveedor {$devolucion->numero_devolucion}: " . trim($data['motivo']),
                    'fecha_movimiento' => now(),
                ]);

                // 6. Asentar egreso en Libro Oficial MINSA si el producto es fiscalizado
                if ($producto->esControlado()) {
                    RegistroVentaControlado::create([
                        'tipo_movimiento'          => RegistroVentaControlado::TIPO_AJUSTE_EGRESO,
                        'movimiento_inventario_id' => $movKardex->id,
                        'producto_id'              => $producto->id,
                        'lote_id'                  => $lote->id,
                        'nivel_controlado'         => 1,
                        'paciente_nombre'          => 'Proveedor: ' . $proveedor->nombre,
                        'paciente_cedula'          => $proveedor->ruc,
                        'motivo_omision'           => "Devolución a proveedor: {$devolucion->numero_devolucion} (Doc Compra: " . ($compra?->numero_comprobante ?? 'N/A') . ")",
                        'cantidad'                 => $cantidad,
                        'unidad'                   => 'unidad',
                        'user_id'                  => $userId,
                    ]);
                }
            }

            $totalFinal = round($totalAcumulado, 2);
            $devolucion->update(['total_devolucion' => $totalFinal]);

            // 7. Si la compra de origen tenía saldo a crédito pendiente, aplicar ajuste
            if ($compra && (float) $compra->saldo_pendiente > 0) {
                $saldoAntes = (float) $compra->saldo_pendiente;
                $nuevoSaldo = max(0, round($saldoAntes - $totalFinal, 2));
                $nuevoEstadoPago = $nuevoSaldo <= 0.001 ? 'pagado' : 'parcial';

                $compra->update([
                    'saldo_pendiente' => $nuevoSaldo,
                    'estado_pago'     => $nuevoEstadoPago,
                ]);

                Log::info("Saldo de compra #{$compra->id} reducido por devolución {$devolucion->numero_devolucion}. Saldo: {$saldoAntes} -> {$nuevoSaldo}");
            }

            Cache::forget('inventario_valorizacion');
            NotificacionService::clearCache();

            AuditLog::log('compras', 'devolucion_compra', "Devolución a proveedor registrada: {$devolucion->numero_devolucion}", [
                'devolucion_id'     => $devolucion->id,
                'numero_devolucion' => $devolucion->numero_devolucion,
                'proveedor_id'      => $devolucion->proveedor_id,
                'compra_id'         => $devolucion->compra_id,
                'total'             => $devolucion->total_devolucion,
                'total_lineas'      => count($data['items']),
            ]);

            Log::info("Devolución a proveedor {$devolucion->numero_devolucion} completada exitosamente.", [
                'devolucion_id' => $devolucion->id,
                'total'         => $totalFinal,
            ]);

            return $devolucion->load(['proveedor', 'usuario', 'compra', 'detalles.lote.producto.laboratorio']);
        });
    }

    /**
     * Anular una devolución a proveedor mediante contra-asientos de reversión en Kardex,
     * reactivación de lotes y restitución sanitaria MINSA.
     *
     * @param DevolucionCompra $devolucion
     * @param string $motivo
     * @param int $userId
     * @return DevolucionCompra
     * @throws Exception
     */
    public function anularDevolucion(DevolucionCompra $devolucion, string $motivo, int $userId): DevolucionCompra
    {
        return DB::transaction(function () use ($devolucion, $motivo, $userId) {
            $lockedDev = DevolucionCompra::with(['detalles.lote.producto', 'compra', 'proveedor'])
                ->where('id', $devolucion->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedDev->estado === 'rechazada') {
                throw new Exception("La devolución {$lockedDev->numero_devolucion} ya se encuentra anulada/rechazada.");
            }

            foreach ($lockedDev->detalles as $det) {
                $lote = Lote::where('id', $det->lote_id)->lockForUpdate()->firstOrFail();
                $producto = $lote->producto;

                $cantidadARestituir = (int) $det->cantidad;
                $stockAnterior = (int) $lote->stock_actual;
                $stockPosterior = $stockAnterior + $cantidadARestituir;

                // 1. Restituir stock en Lote
                $lote->stock_actual = $stockPosterior;
                $lote->activo = true;
                $lote->save();

                // 2. Contra-asiento en Kardex (ENTRADA por anulación de devolución)
                $movKardex = MovimientoInventario::create([
                    'producto_id'      => $lote->producto_id,
                    'lote_id'          => $lote->id,
                    'user_id'          => $userId,
                    'tipo'             => 'entrada',
                    'subtipo'          => 'ajuste_manual',
                    'cantidad'         => $cantidadARestituir,
                    'stock_anterior'   => $stockAnterior,
                    'stock_posterior'  => $stockPosterior,
                    'costo_unitario'   => (float) $det->precio_unitario,
                    'costo_total'      => (float) $det->subtotal,
                    'origen'           => 'anulacion_devolucion_compra',
                    'origen_id'        => $lockedDev->id,
                    'motivo'           => "Reversión por anulación de devolución {$lockedDev->numero_devolucion}: {$motivo}",
                    'fecha_movimiento' => now(),
                ]);

                // 3. Contra-asiento en Libro MINSA si aplica
                if ($producto && $producto->esControlado()) {
                    RegistroVentaControlado::create([
                        'tipo_movimiento'          => RegistroVentaControlado::TIPO_AJUSTE_INGRESO,
                        'movimiento_inventario_id' => $movKardex->id,
                        'producto_id'              => $producto->id,
                        'lote_id'                  => $lote->id,
                        'nivel_controlado'         => 1,
                        'paciente_nombre'          => 'Proveedor: ' . ($lockedDev->proveedor?->nombre ?? 'N/A'),
                        'paciente_cedula'          => $lockedDev->proveedor?->ruc,
                        'motivo_omision'           => "Reversión sanitaria por anulación de devolución {$lockedDev->numero_devolucion}: {$motivo}",
                        'cantidad'                 => $cantidadARestituir,
                        'unidad'                   => 'unidad',
                        'user_id'                  => $userId,
                    ]);
                }
            }

            // 4. Si la compra de origen fue ajustada, reponer su saldo de crédito
            if ($lockedDev->compra && $lockedDev->compra->condicion_pago === 'credito') {
                $compra = $lockedDev->compra;
                $nuevoSaldo = round((float)$compra->saldo_pendiente + (float)$lockedDev->total_devolucion, 2);
                $compra->update([
                    'saldo_pendiente' => $nuevoSaldo,
                    'estado_pago'     => 'parcial',
                ]);
            }

            // 5. Marcar devolución como rechazada/anulada
            $lockedDev->update([
                'estado' => 'rechazada',
                'motivo' => trim($lockedDev->motivo . " | Anulada: {$motivo}"),
            ]);

            Cache::forget('inventario_valorizacion');
            NotificacionService::clearCache();

            AuditLog::log('compras', 'anular_devolucion_compra', "Devolución a proveedor anulada: {$lockedDev->numero_devolucion}", [
                'devolucion_id'     => $lockedDev->id,
                'numero_devolucion' => $lockedDev->numero_devolucion,
                'motivo'            => $motivo,
                'total_revertido'   => $lockedDev->total_devolucion,
            ]);

            Log::info("Devolución a proveedor {$lockedDev->numero_devolucion} anulada. Stock revertido.", [
                'devolucion_id' => $lockedDev->id,
                'user_id'       => $userId,
            ]);

            return $lockedDev;
        });
    }

    /**
     * Marcar devolución como enviada físicamente al proveedor.
     *
     * @param DevolucionCompra $devolucion
     * @return DevolucionCompra
     * @throws Exception
     */
    public function marcarEnviada(DevolucionCompra $devolucion): DevolucionCompra
    {
        if ($devolucion->estado === 'rechazada') {
            throw new Exception('No se puede enviar una devolución anulada/rechazada.');
        }

        $devolucion->update([
            'estado'      => 'enviada',
            'fecha_envio' => now(),
        ]);

        return $devolucion;
    }

    /**
     * Confirmar recepción formal del proveedor.
     *
     * @param DevolucionCompra $devolucion
     * @return DevolucionCompra
     * @throws Exception
     */
    public function confirmarDevolucion(DevolucionCompra $devolucion): DevolucionCompra
    {
        if ($devolucion->estado === 'rechazada') {
            throw new Exception('No se puede confirmar una devolución rechazada o anulada.');
        }

        $devolucion->update(['estado' => 'confirmada']);

        return $devolucion;
    }

    /**
     * Generar número correlativo consecutivo DEV-COMP-NNNNNN blindado contra concurrencia.
     *
     * @return string
     */
    public function generarNumeroDevolucion(): string
    {
        $ultimaDev = DevolucionCompra::lockForUpdate()
            ->orderBy('id', 'desc')
            ->first();

        $consecutivo = 1;
        if ($ultimaDev && preg_match('/DEV-COMP-(\d+)/', $ultimaDev->numero_devolucion, $matches)) {
            $consecutivo = ((int) $matches[1]) + 1;
        } else {
            $consecutivo = (DevolucionCompra::count()) + 1;
        }

        return sprintf('DEV-COMP-%06d', $consecutivo);
    }

    /**
     * Métricas consolidadas para el módulo de Devoluciones.
     *
     * @return array
     */
    public function getMetricas(): array
    {
        return \App\Facades\RequestCache::remember('devoluciones_compra:metricas', function () {
            $inicioMes = now()->startOfMonth()->toDateTimeString();
            $finMes = now()->endOfMonth()->toDateTimeString();

            $stats = DevolucionCompra::where('estado', '!=', 'rechazada')
                ->selectRaw("
                    COUNT(*) as total_devoluciones,
                    COALESCE(SUM(total_devolucion), 0) as total_monto_devuelto,
                    COALESCE(SUM(CASE WHEN created_at >= ? AND created_at <= ? THEN 1 ELSE 0 END), 0) as devoluciones_mes
                ", [$inicioMes, $finMes])
                ->first();

            $lotesAfectadosCount = DetalleDevolucionCompra::whereHas('devolucionCompra', function ($q) {
                $q->where('estado', '!=', 'rechazada');
            })->count();

            return [
                'total_devoluciones'   => (int) ($stats->total_devoluciones ?? 0),
                'total_monto_devuelto' => (float) ($stats->total_monto_devuelto ?? 0),
                'lotes_afectados'      => $lotesAfectadosCount,
                'devoluciones_mes'     => (int) ($stats->devoluciones_mes ?? 0),
            ];
        });
    }
}
