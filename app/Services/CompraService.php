<?php

namespace App\Services;

use App\Models\Compra;
use App\Models\DetalleCompra;
use App\Models\Lote;
use App\Models\Producto;
use App\Models\MovimientoInventario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class CompraService
{
    /**
     * Registrar una compra completa
     * 
     * @param array $data Datos de la compra
     * @return Compra
     * @throws Exception
     */
    public function registrarCompra(array $data): Compra
    {
        return DB::transaction(function () use ($data) {
            
            // 1. Crear la compra
            $compra = Compra::create([
                'proveedor_id' => $data['proveedor_id'],
                'user_id' => Auth::id(),
                'total' => 0,
                'estado' => 'recibida',
                'fecha' => $data['fecha'] ?? now(),
            ]);

            $total = 0;

            // 2. Procesar cada producto
            foreach ($data['productos'] as $item) {
                $total += $this->procesarDetalleCompra($compra, $item);
            }

            // 3. Actualizar total
            $compra->update(['total' => $total]);

            // 4. Retornar compra con relaciones
            return $compra->load([
                'detalles.producto',
                'detalles.lote',
                'proveedor',
                'lotes'
            ]);
        });
    }

    /**
     * Procesar un detalle de compra (crear lote y registrar movimiento)
     * 
     * @param Compra $compra
     * @param array $item
     * @return float Subtotal
     * @throws Exception
     */
    protected function procesarDetalleCompra(Compra $compra, array $item): float
    {
        // 1. Validar que el producto exista
        $producto = Producto::findOrFail($item['producto_id']);

        // 2. Crear el lote
        $lote = Lote::create([
            'producto_id' => $producto->id,
            'compra_id' => $compra->id,
            'proveedor_id' => $compra->proveedor_id,
            'numero_lote' => $item['numero_lote'],
            'fecha_vencimiento' => $item['fecha_vencimiento'],
            'stock_inicial' => $item['cantidad'],
            'precio_compra' => $item['precio_unitario'],
            'activo' => true,
        ]);

        // 3. Calcular subtotal
        $subtotal = $item['cantidad'] * $item['precio_unitario'];

        // 4. Crear detalle de compra
        DetalleCompra::create([
            'compra_id' => $compra->id,
            'producto_id' => $producto->id,
            'lote_id' => $lote->id,
            'cantidad' => $item['cantidad'],
            'precio_unitario' => $item['precio_unitario'],
            'subtotal' => $subtotal,
        ]);

        // 5. Registrar movimiento de inventario (ENTRADA)
        MovimientoInventario::create([
            'producto_id' => $producto->id,
            'lote_id' => $lote->id,
            'user_id' => Auth::id(),
            'tipo' => 'entrada',
            'cantidad' => $item['cantidad'], // Positivo para entrada
            'origen' => 'compra',
            'origen_id' => $compra->id,
            'motivo' => "Compra #{$compra->id} - Lote {$lote->numero_lote}",
            'fecha_movimiento' => now(),
        ]);

        return $subtotal;
    }

    /**
     * Anular una compra
     * 
     * @param int $compraId
     * @param string $motivo
     * @return Compra
     * @throws Exception
     */
    public function anularCompra(int $compraId, string $motivo): Compra
    {
        return DB::transaction(function () use ($compraId, $motivo) {
            
            // 1. Obtener la compra
            $compra = Compra::with(['detalles', 'lotes'])->findOrFail($compraId);

            // 2. Validar que pueda anularse
            if (!$compra->puedeAnularse()) {
                throw new Exception("La compra #{$compraId} ya está anulada.");
            }

            // 3. Verificar que los lotes no hayan sido vendidos
            foreach ($compra->lotes as $lote) {
                if ($lote->stock_actual < $lote->stock_inicial) {
                    throw new Exception(
                        "No se puede anular la compra porque el lote {$lote->numero_lote} " .
                        "ya tiene productos vendidos. Stock inicial: {$lote->stock_inicial}, " .
                        "Stock actual: {$lote->stock_actual}"
                    );
                }
            }

            // 4. Desactivar lotes asociados
            foreach ($compra->lotes as $lote) {
                $lote->update(['activo' => false]);

                // Registrar movimiento de anulación
                MovimientoInventario::create([
                    'producto_id' => $lote->producto_id,
                    'lote_id' => $lote->id,
                    'user_id' => Auth::id(),
                    'tipo' => 'salida',
                    'cantidad' => -$lote->stock_inicial, // Negativo para revertir entrada
                    'origen' => 'anulacion_compra',
                    'origen_id' => $compra->id,
                    'motivo' => "Anulación de compra #{$compra->id}: {$motivo}",
                    'fecha_movimiento' => now(),
                ]);
            }

            // 5. Actualizar estado de la compra
            $compra->update([
                'estado' => 'anulada',
                'anulado_por' => Auth::id(),
                'fecha_anulacion' => now(),
                'motivo_anulacion' => $motivo,
            ]);

            return $compra->fresh(['detalles', 'lotes', 'anuladoPor']);
        });
    }

    /**
     * Obtener compras recientes
     * 
     * @param int $limite
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function comprasRecientes(int $limite = 10)
    {
        return Compra::with(['proveedor', 'usuario', 'detalles.producto'])
            ->recibidas()
            ->orderBy('fecha', 'desc')
            ->limit($limite)
            ->get();
    }

    /**
     * Obtener total de compras del mes
     * 
     * @return float
     */
    public function totalComprasDelMes(): float
    {
        return Compra::whereMonth('fecha', now()->month)
            ->whereYear('fecha', now()->year)
            ->recibidas()
            ->sum('total');
    }

    /**
     * Validar número de lote único para un producto
     * 
     * @param int $productoId
     * @param string $numeroLote
     * @return bool
     */
    public function esNumeroLoteUnico(int $productoId, string $numeroLote): bool
    {
        return !Lote::where('producto_id', $productoId)
            ->where('numero_lote', $numeroLote)
            ->exists();
    }

    /**
     * Modificar una compra existente
     * 
     * Este método:
     * 1. Anula la compra original (y sus lotes)
     * 2. Crea una nueva compra con los datos corregidos
     * 3. Mantiene la trazabilidad entre ambas
     * 
     * @param int $compraId ID de la compra a modificar
     * @param array $data Nuevos datos de la compra
     * @param string $motivo Motivo de la modificación
     * @return Compra Nueva compra creada
     * @throws Exception
     */
    public function modificarCompra(int $compraId, array $data, string $motivo): Compra
    {
        return DB::transaction(function () use ($compraId, $data, $motivo) {
            
            // 1. Obtener la compra original
            $compraOriginal = Compra::with(['detalles', 'lotes'])->findOrFail($compraId);

            // 2. Validar que pueda modificarse
            if (!$compraOriginal->puedeModificarse()) {
                throw new Exception(
                    "La compra #{$compraId} no puede modificarse. " .
                    "Estado: {$compraOriginal->estado}, " .
                    "Reemplazada: " . ($compraOriginal->reemplazada_por ? 'Sí' : 'No')
                );
            }

            // 3. Verificar que los lotes no hayan sido vendidos
            foreach ($compraOriginal->lotes as $lote) {
                if ($lote->stock_actual < $lote->stock_inicial) {
                    throw new Exception(
                        "No se puede modificar la compra porque el lote {$lote->numero_lote} " .
                        "ya tiene productos vendidos. Stock inicial: {$lote->stock_inicial}, " .
                        "Stock actual: {$lote->stock_actual}"
                    );
                }
            }

            // 4. Desactivar lotes de la compra original y revertir inventario
            foreach ($compraOriginal->lotes as $lote) {
                $lote->update(['activo' => false]);

                MovimientoInventario::create([
                    'producto_id' => $lote->producto_id,
                    'lote_id' => $lote->id,
                    'user_id' => Auth::id(),
                    'tipo' => 'salida',
                    'cantidad' => -$lote->stock_inicial, // Negativo para revertir
                    'origen' => 'modificacion_compra',
                    'origen_id' => $compraOriginal->id,
                    'motivo' => "Modificación de compra #{$compraOriginal->id}: {$motivo}",
                    'fecha_movimiento' => now(),
                ]);
            }

            // 5. Crear la nueva compra
            $nuevaCompra = $this->registrarCompra($data);

            // 6. Establecer relaciones de trazabilidad
            
            // Marcar la original como anulada y reemplazada
            $compraOriginal->update([
                'estado' => 'anulada',
                'anulado_por' => Auth::id(),
                'fecha_anulacion' => now(),
                'motivo_anulacion' => "Modificada - Razón: {$motivo}. Nueva compra: #{$nuevaCompra->id}",
                'reemplazada_por' => $nuevaCompra->id,
            ]);

            // Marcar la nueva como modificación de la original
            $nuevaCompra->update([
                'compra_original_id' => $compraOriginal->id,
            ]);

            // 7. Retornar nueva compra con relaciones
            return $nuevaCompra->load([
                'detalles.producto',
                'detalles.lote',
                'proveedor',
                'lotes',
                'compraOriginal'
            ]);
        });
    }

    /**
     * Obtener historial de modificaciones de una compra
     * 
     * @param int $compraId
     * @return \Illuminate\Support\Collection
     */
    public function historialModificacionesCompra(int $compraId)
    {
        $compra = Compra::findOrFail($compraId);
        
        return $compra->cadenaModificaciones()->map(function ($c, $index) {
            return [
                'version' => $index + 1,
                'id' => $c->id,
                'fecha' => $c->fecha,
                'total' => $c->total,
                'estado' => $c->estado,
                'usuario' => $c->usuario->name,
                'proveedor' => $c->proveedor->nombre,
                'es_original' => is_null($c->compra_original_id),
                'es_activa' => is_null($c->reemplazada_por) && $c->estado === 'recibida',
                'motivo_anulacion' => $c->motivo_anulacion,
                'total_lotes' => $c->lotes->count(),
            ];
        });
    }
}