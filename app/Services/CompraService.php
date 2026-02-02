<?php

namespace App\Services;

use App\Models\Compra;
use App\Models\DetalleCompra;
use App\Models\Lote;
use App\Models\Producto;
use App\Models\PresentacionProducto;
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
            
            // 0. Validar datos de presentaciones
            $this->validarDatosCompra($data);
            
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
                'detalles.presentacion',
                'proveedor',
                'lotes'
            ]);
        });
    }

    /**
     * Procesar un detalle de compra con soporte para presentaciones
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
        
        // 2. Obtener datos de presentación
        $presentacionId = $item['presentacion_id'] ?? null;
        $cantidadPresentaciones = $item['cantidad_presentaciones'] ?? $item['cantidad'] ?? 1;
        $precioUnitario = $item['precio_unitario'] ?? $producto->precio_compra;
        
        // 3. Determinar unidades por presentación
        if ($presentacionId) {
            $presentacion = PresentacionProducto::findOrFail($presentacionId);
            $unidadesPorPresentacion = $presentacion->unidades_por_presentacion;
            $tipoPresentacion = $presentacion->nombre;
        } else {
            // Unidad base
            $unidadesPorPresentacion = 1;
            $tipoPresentacion = 'Unidad';
        }
        
        // 4. Calcular totales
        $cantidadUnidadesBase = $cantidadPresentaciones * $unidadesPorPresentacion;
        $precioPresentacion = round($precioUnitario * $unidadesPorPresentacion, 2);
        $subtotal = round($precioPresentacion * $cantidadPresentaciones, 2);
        
        // 5. Crear el lote (con unidades base)
        $lote = Lote::create([
            'producto_id' => $producto->id,
            'compra_id' => $compra->id,
            'proveedor_id' => $compra->proveedor_id,
            'numero_lote' => $item['numero_lote'],
            'fecha_vencimiento' => $item['fecha_vencimiento'],
            'stock_inicial' => $cantidadUnidadesBase, // ← IMPORTANTE: unidades base
            'precio_compra' => $precioUnitario, // ← Precio unitario base
            'activo' => true,
        ]);

        // 6. Crear detalle de compra
        $detalle = DetalleCompra::create([
            'compra_id' => $compra->id,
            'producto_id' => $producto->id,
            'lote_id' => $lote->id,
            'presentacion_id' => $presentacionId,
            'tipo_presentacion' => $tipoPresentacion,
            'unidades_por_presentacion' => $unidadesPorPresentacion,
            'cantidad_presentaciones' => $cantidadPresentaciones,
            // cantidad_unidades_base se calcula automáticamente en la BD
            'cantidad_legacy' => $cantidadUnidadesBase, // Para compatibilidad
            'precio_unitario' => $precioUnitario,
            'subtotal' => $subtotal,
        ]);

        // 7. Registrar movimiento de inventario (ENTRADA con unidades base)
        MovimientoInventario::create([
            'producto_id' => $producto->id,
            'lote_id' => $lote->id,
            'user_id' => Auth::id(),
            'tipo' => 'entrada',
            'cantidad' => $cantidadUnidadesBase, // ← IMPORTANTE: unidades base
            'origen' => 'compra',
            'origen_id' => $compra->id,
            'motivo' => $presentacionId 
                ? "Compra #{$compra->id} - {$cantidadPresentaciones} {$tipoPresentacion}(s) x {$unidadesPorPresentacion} = {$cantidadUnidadesBase} unidades - Lote {$lote->numero_lote}"
                : "Compra #{$compra->id} - {$cantidadUnidadesBase} unidades - Lote {$lote->numero_lote}",
            'fecha_movimiento' => now(),
        ]);

    

        return $subtotal;
    }

    /**
     * Validar datos de compra (nuevo método)
     * 
     * @param array $data
     * @throws Exception
     */
    protected function validarDatosCompra(array $data): void
    {
        if (!isset($data['productos']) || !is_array($data['productos'])) {
            throw new Exception('Debe proporcionar al menos un producto.');
        }

        foreach ($data['productos'] as $index => $item) {
            // Validar que si tiene presentacion_id, exista y esté activa
            if (isset($item['presentacion_id']) && $item['presentacion_id']) {
                $presentacion = PresentacionProducto::find($item['presentacion_id']);
                
                if (!$presentacion) {
                    throw new Exception("La presentación seleccionada en el producto #{$index} no existe.");
                }
                
                if (!$presentacion->activo) {
                    throw new Exception("La presentación '{$presentacion->nombre}' en el producto #{$index} no está activa.");
                }
                
                // Validar que la presentación pertenece al producto
                if ($presentacion->producto_id != $item['producto_id']) {
                    throw new Exception("La presentación seleccionada no corresponde al producto en el ítem #{$index}.");
                }
            }
            
            // Validar cantidad de presentaciones
            $cantidad = $item['cantidad_presentaciones'] ?? $item['cantidad'] ?? 0;
            if ($cantidad < 1) {
                throw new Exception("La cantidad en el producto #{$index} debe ser mayor a 0.");
            }

            // Validar precio unitario
            $precioUnitario = $item['precio_unitario'] ?? 0;
            if ($precioUnitario < 0) {
                throw new Exception("El precio unitario en el producto #{$index} no puede ser negativo.");
            }
        }
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
            
            $compra = Compra::with(['detalles', 'lotes'])->findOrFail($compraId);

            if (!$compra->puedeAnularse()) {
                throw new Exception("La compra #{$compraId} ya está anulada.");
            }

            foreach ($compra->lotes as $lote) {
                if ($lote->stock_actual < $lote->stock_inicial) {
                    throw new Exception(
                        "No se puede anular la compra porque el lote {$lote->numero_lote} " .
                        "ya tiene productos vendidos."
                    );
                }
            }

            foreach ($compra->lotes as $lote) {
                $lote->update(['activo' => false]);

                MovimientoInventario::create([
                    'producto_id' => $lote->producto_id,
                    'lote_id' => $lote->id,
                    'user_id' => Auth::id(),
                    'tipo' => 'salida',
                    'cantidad' => -$lote->stock_inicial,
                    'origen' => 'anulacion_compra',
                    'origen_id' => $compra->id,
                    'motivo' => "Anulación de compra #{$compra->id}: {$motivo}",
                    'fecha_movimiento' => now(),
                ]);
            }

            // Recalcular precio_compra de los productos afectados
            foreach ($compra->detalles as $detalle) {
                $producto = $detalle->producto;
                
                // Buscar el último precio válido
                $ultimoPrecio = DetalleCompra::where('producto_id', $producto->id)
                    ->whereHas('compra', function($q) {
                        $q->where('estado', 'recibida')
                          ->whereNull('reemplazada_por');
                    })
                    ->orderBy('id', 'desc')
                    ->first();
                
                $producto->update([
                    'precio_compra' => $ultimoPrecio?->precio_unitario
                ]);
            }

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
            
            // 0. Validar datos de presentaciones
            $this->validarDatosCompra($data);
            
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
                'detalles.presentacion',
                'proveedor',
                'lotes',
                'compraOriginal'
            ]);
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