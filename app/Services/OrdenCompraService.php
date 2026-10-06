<?php

namespace App\Services;

use App\Models\OrdenCompra;
use App\Models\DetalleOrdenCompra;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class OrdenCompraService
{
    /**
     * Crear una nueva Orden de Compra de manera atómica sin tocar inventario ni finanzas.
     *
     * @param array $data
     * @param int $userId
     * @return OrdenCompra
     * @throws Exception
     */
    public function crearOrden(array $data, int $userId): OrdenCompra
    {
        return DB::transaction(function () use ($data, $userId) {
            $numeroOrden = $this->generarNumeroOrden();

            $total = 0;
            $detalles = [];

            foreach ($data['items'] as $item) {
                $cant = (int) $item['cantidad'];
                $precio = (float) $item['precio_unitario'];
                $subtotal = round($cant * $precio, 2);
                $total += $subtotal;

                $detalles[] = [
                    'producto_id'              => (int) $item['producto_id'],
                    'cantidad_solicitada'      => $cant,
                    'cantidad_recibida'        => 0,
                    'precio_unitario_estimado' => $precio,
                    'subtotal'                 => $subtotal,
                ];
            }

            $condicionPago = $data['condicion_pago'] ?? 'contado';
            $diasCredito = $condicionPago === 'credito' ? (int) ($data['dias_credito'] ?? 30) : 0;

            $orden = OrdenCompra::create([
                'proveedor_id'           => (int) $data['proveedor_id'],
                'user_id'                => $userId,
                'numero_orden'           => $numeroOrden,
                'fecha_emision'          => $data['fecha_emision'],
                'fecha_esperada_entrega' => $data['fecha_esperada_entrega'] ?? null,
                'estado'                 => 'enviada',
                'condicion_pago'         => $condicionPago,
                'dias_credito'           => $diasCredito,
                'subtotal'               => $total,
                'impuesto'               => 0,
                'total'                  => $total,
                'observaciones'          => !empty($data['observaciones']) ? trim($data['observaciones']) : null,
            ]);

            foreach ($detalles as $det) {
                $det['orden_compra_id'] = $orden->id;
                DetalleOrdenCompra::create($det);
            }

            AuditLog::log('compras', 'crear_orden_compra', "Orden de compra creada: {$orden->numero_orden}", [
                'orden_id'     => $orden->id,
                'numero_orden' => $orden->numero_orden,
                'proveedor_id' => $orden->proveedor_id,
                'total'        => $orden->total,
                'total_lineas' => count($detalles),
            ]);

            Log::info("Orden de Compra {$orden->numero_orden} registrada", [
                'orden_id' => $orden->id,
                'user_id'  => $userId,
                'total'    => $orden->total,
            ]);

            return $orden->load(['proveedor', 'detalles.producto', 'usuario']);
        });
    }

    /**
     * Cancelar una orden de compra siempre que no posea recepciones previas.
     *
     * @param OrdenCompra $orden
     * @param string|null $motivo
     * @return OrdenCompra
     * @throws Exception
     */
    public function cancelarOrden(OrdenCompra $orden, ?string $motivo = null): OrdenCompra
    {
        return DB::transaction(function () use ($orden, $motivo) {
            $lockedOrden = OrdenCompra::where('id', $orden->id)->lockForUpdate()->firstOrFail();

            if (in_array($lockedOrden->estado, ['recibida_total', 'recibida_parcial']) || $lockedOrden->compras()->exists()) {
                throw new Exception("No se puede cancelar la orden '{$lockedOrden->numero_orden}' porque ya tiene recepciones registradas en compras.");
            }

            if ($lockedOrden->estado === 'cancelada') {
                throw new Exception("La orden '{$lockedOrden->numero_orden}' ya se encuentra cancelada.");
            }

            $lockedOrden->estado = 'cancelada';
            if ($motivo) {
                $lockedOrden->observaciones = trim(($lockedOrden->observaciones ? $lockedOrden->observaciones . ' | ' : '') . "Cancelada: {$motivo}");
            }
            $lockedOrden->save();

            AuditLog::log('compras', 'cancelar_orden_compra', "Orden de compra cancelada: {$lockedOrden->numero_orden}", [
                'orden_id'     => $lockedOrden->id,
                'numero_orden' => $lockedOrden->numero_orden,
                'motivo'       => $motivo,
            ]);

            Log::info("Orden de Compra {$lockedOrden->numero_orden} cancelada");

            return $lockedOrden;
        });
    }

    /**
     * Generar número correlativo consecutivo OC-YYYY-NNNN blindado contra concurrencia.
     *
     * @param int|null $year
     * @return string
     */
    public function generarNumeroOrden(?int $year = null): string
    {
        $year = $year ?? (int) now()->format('Y');

        $ultimaOrden = OrdenCompra::where('numero_orden', 'like', "OC-{$year}-%")
            ->lockForUpdate()
            ->orderBy('id', 'desc')
            ->first();

        $consecutivo = 1;
        if ($ultimaOrden && preg_match('/OC-\d{4}-(\d+)/', $ultimaOrden->numero_orden, $matches)) {
            $consecutivo = ((int) $matches[1]) + 1;
        } else {
            $consecutivo = OrdenCompra::whereYear('created_at', $year)->count() + 1;
        }

        return sprintf('OC-%d-%04d', $year, $consecutivo);
    }
}
