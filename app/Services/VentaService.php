<?php

namespace App\Services;

use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\Lote;
use App\Models\Producto;
use App\Models\MovimientoInventario;
use App\Models\Receta;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class VentaService
{
    /**
     * Procesar una venta completa
     * 
     * @param array $data Datos de la venta
     * @return Venta
     * @throws Exception
     */
    public function procesarVenta(array $data): Venta
    {
        return DB::transaction(function () use ($data) {
            
            // 1. Validar que todos los productos requieren receta (si aplica)
            $this->validarRecetasMedicas($data);
            
            // 2. Crear la venta (sin total aún)
            $venta = Venta::create([
                'cliente_id' => $data['cliente_id'] ?? null,
                'user_id' => Auth::id(),
                'total' => 0,
                'metodo_pago' => $data['metodo_pago'],
                'estado' => 'completada',
                'fecha' => now(),
            ]);

            $total = 0;

            // 3. Procesar cada producto
            foreach ($data['productos'] as $item) {
                $total += $this->procesarDetalleVenta($venta, $item);
            }

            // 4. Actualizar el total de la venta
            $venta->update(['total' => $total]);

            // 5. Asociar recetas si existen
            if (!empty($data['recetas'])) {
                $venta->recetas()->attach($data['recetas']);
            }

            // 6. Retornar venta con relaciones cargadas
            return $venta->load([
                'detalles.producto',
                'detalles.lote',
                'cliente',
                'recetas'
            ]);
        });
    }

    /**
     * Procesar un detalle de venta individual
     * 
     * @param Venta $venta
     * @param array $item
     * @return float Subtotal del detalle
     * @throws Exception
     */
    protected function procesarDetalleVenta(Venta $venta, array $item): float
    {
        // 1. Obtener el lote
        $lote = Lote::with('producto')->findOrFail($item['lote_id']);
        
        // 2. Validaciones
        $this->validarLoteParaVenta($lote, $item['cantidad']);

        // 3. Calcular subtotal
        $precioUnitario = $item['precio_unitario'] ?? $lote->producto->precio_venta;
        $subtotal = $item['cantidad'] * $precioUnitario;

        // 4. Crear detalle de venta
        DetalleVenta::create([
            'venta_id' => $venta->id,
            'producto_id' => $lote->producto_id,
            'lote_id' => $lote->id,
            'cantidad' => $item['cantidad'],
            'precio_unitario' => $precioUnitario,
            'subtotal' => $subtotal,
        ]);

        // 5. Registrar movimiento de inventario (SALIDA)
        MovimientoInventario::create([
            'producto_id' => $lote->producto_id,
            'lote_id' => $lote->id,
            'user_id' => Auth::id(),
            'tipo' => 'salida',
            'cantidad' => -$item['cantidad'], // Negativo para salida
            'origen' => 'venta',
            'origen_id' => $venta->id,
            'motivo' => "Venta #{$venta->id}",
            'fecha_movimiento' => now(),
        ]);

        return $subtotal;
    }

    /**
     * Validar que el lote esté disponible para venta
     * 
     * @param Lote $lote
     * @param int $cantidad
     * @throws Exception
     */
    protected function validarLoteParaVenta(Lote $lote, int $cantidad): void
    {
        // 1. Verificar que el lote esté activo
        if (!$lote->activo) {
            throw new Exception(
                "El lote {$lote->numero_lote} del producto {$lote->producto->nombre} está inactivo."
            );
        }

        // 2. Verificar que no esté vencido
        if ($lote->estaVencido()) {
            throw new Exception(
                "El lote {$lote->numero_lote} del producto {$lote->producto->nombre} está vencido (Vencimiento: {$lote->fecha_vencimiento->format('d/m/Y')})."
            );
        }

        // 3. Verificar stock disponible
        if (!$lote->tieneStock($cantidad)) {
            throw new Exception(
                "Stock insuficiente para {$lote->producto->nombre}. " .
                "Disponible en lote {$lote->numero_lote}: {$lote->stock_actual}, Solicitado: {$cantidad}"
            );
        }
    }

    /**
     * Validar que los productos que requieren receta tengan una asociada
     * 
     * @param array $data
     * @throws Exception
     */
    protected function validarRecetasMedicas(array $data): void
    {
        // Obtener IDs de productos en la venta
        $productosIds = collect($data['productos'])->pluck('producto_id')->unique();
        
        // Verificar cuáles requieren receta
        $productosConReceta = Producto::whereIn('id', $productosIds)
            ->where('requiere_receta', true)
            ->get();

        // Si hay productos que requieren receta
        if ($productosConReceta->isNotEmpty()) {
            // Verificar que se haya proporcionado al menos una receta
            if (empty($data['recetas'])) {
                $nombres = $productosConReceta->pluck('nombre')->join(', ');
                throw new Exception(
                    "Los siguientes productos requieren receta médica: {$nombres}"
                );
            }

            // Validar que las recetas existan
            $recetasIds = $data['recetas'];
            $recetasValidas = Receta::whereIn('id', $recetasIds)->count();
            
            if ($recetasValidas !== count($recetasIds)) {
                throw new Exception("Una o más recetas médicas no son válidas.");
            }
        }
    }

    /**
     * Anular una venta
     * 
     * @param int $ventaId
     * @param string $motivo
     * @return Venta
     * @throws Exception
     */
    public function anularVenta(int $ventaId, string $motivo): Venta
    {
        return DB::transaction(function () use ($ventaId, $motivo) {
            
            // 1. Obtener la venta
            $venta = Venta::with('detalles')->findOrFail($ventaId);

            // 2. Validar que pueda anularse
            if (!$venta->puedeAnularse()) {
                throw new Exception("La venta #{$ventaId} no puede ser anulada porque ya está anulada.");
            }

            // 3. Revertir movimientos de inventario (crear movimientos inversos)
            foreach ($venta->detalles as $detalle) {
                MovimientoInventario::create([
                    'producto_id' => $detalle->producto_id,
                    'lote_id' => $detalle->lote_id,
                    'user_id' => Auth::id(),
                    'tipo' => 'entrada',
                    'cantidad' => $detalle->cantidad, // Positivo para entrada (revertir salida)
                    'origen' => 'anulacion_venta',
                    'origen_id' => $venta->id,
                    'motivo' => "Anulación de venta #{$venta->id}: {$motivo}",
                    'fecha_movimiento' => now(),
                ]);
            }

            // 4. Actualizar estado de la venta
            $venta->update([
                'estado' => 'anulada',
                'anulado_por' => Auth::id(),
                'fecha_anulacion' => now(),
                'motivo_anulacion' => $motivo,
            ]);

            return $venta->fresh(['detalles', 'anuladoPor']);
        });
    }

    /**
     * Obtener ventas del día actual
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function ventasDelDia()
    {
        return Venta::with(['cliente', 'usuario', 'detalles.producto'])
            ->whereDate('fecha', today())
            ->completadas()
            ->orderBy('fecha', 'desc')
            ->get();
    }

    /**
     * Obtener total de ventas del día
     * 
     * @return float
     */
    public function totalVentasDelDia(): float
    {
        return Venta::whereDate('fecha', today())
            ->completadas()
            ->sum('total');
    }

    /**
     * Obtener ventas de un usuario específico
     * 
     * @param int $userId
     * @param string|null $fechaInicio
     * @param string|null $fechaFin
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function ventasDelUsuario(int $userId, ?string $fechaInicio = null, ?string $fechaFin = null)
    {
        $query = Venta::with(['cliente', 'detalles.producto'])
            ->where('user_id', $userId);

        if ($fechaInicio) {
            $query->whereDate('fecha', '>=', $fechaInicio);
        }

        if ($fechaFin) {
            $query->whereDate('fecha', '<=', $fechaFin);
        }

        return $query->orderBy('fecha', 'desc')->get();
    }

    /**
     * Buscar productos disponibles para venta
     * 
     * @param string $termino Término de búsqueda (nombre o código)
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function buscarProductosParaVenta(string $termino)
    {
        return Producto::with(['categoria', 'lotes' => function ($query) {
                $query->disponibles()
                    ->orderBy('fecha_vencimiento', 'asc'); // FIFO: primero los que vencen antes
            }])
            ->activos()
            ->where(function ($query) use ($termino) {
                $query->where('nombre', 'like', "%{$termino}%")
                    ->orWhere('codigo_barra', 'like', "%{$termino}%");
            })
            ->get()
            ->filter(function ($producto) {
                // Solo mostrar productos con lotes disponibles
                return $producto->lotes->isNotEmpty();
            });
    }

    /**
     * Modificar una venta existente
     * 
     * Este método:
     * 1. Anula la venta original
     * 2. Crea una nueva venta con los datos corregidos
     * 3. Mantiene la trazabilidad entre ambas
     * 
     * @param int $ventaId ID de la venta a modificar
     * @param array $data Nuevos datos de la venta
     * @param string $motivo Motivo de la modificación
     * @return Venta Nueva venta creada
     * @throws Exception
     */
    public function modificarVenta(int $ventaId, array $data, string $motivo): Venta
    {
        return DB::transaction(function () use ($ventaId, $data, $motivo) {
            
            // 1. Obtener la venta original
            $ventaOriginal = Venta::with('detalles')->findOrFail($ventaId);

            // 2. Validar que pueda modificarse
            if (!$ventaOriginal->puedeModificarse()) {
                throw new Exception(
                    "La venta #{$ventaId} no puede modificarse. " .
                    "Estado: {$ventaOriginal->estado}, " .
                    "Reemplazada: " . ($ventaOriginal->reemplazada_por ? 'Sí' : 'No')
                );
            }

            // 3. Revertir inventario de la venta original
            foreach ($ventaOriginal->detalles as $detalle) {
                MovimientoInventario::create([
                    'producto_id' => $detalle->producto_id,
                    'lote_id' => $detalle->lote_id,
                    'user_id' => Auth::id(),
                    'tipo' => 'entrada',
                    'cantidad' => $detalle->cantidad, // Positivo para revertir
                    'origen' => 'modificacion_venta',
                    'origen_id' => $ventaOriginal->id,
                    'motivo' => "Modificación de venta #{$ventaOriginal->id}: {$motivo}",
                    'fecha_movimiento' => now(),
                ]);
            }

            // 4. Crear la nueva venta
            $nuevaVenta = $this->procesarVenta($data);

            // 5. Establecer relaciones de trazabilidad
            
            // Marcar la original como anulada y reemplazada
            $ventaOriginal->update([
                'estado' => 'anulada',
                'anulado_por' => Auth::id(),
                'fecha_anulacion' => now(),
                'motivo_anulacion' => "Modificada - Razón: {$motivo}. Nueva venta: #{$nuevaVenta->id}",
                'reemplazada_por' => $nuevaVenta->id,
            ]);

            // Marcar la nueva como modificación de la original
            $nuevaVenta->update([
                'venta_original_id' => $ventaOriginal->id,
            ]);

            // 6. Retornar nueva venta con relaciones
            return $nuevaVenta->load([
                'detalles.producto',
                'detalles.lote',
                'cliente',
                'recetas',
                'ventaOriginal'
            ]);
        });
    }

    /**
     * Obtener historial de modificaciones de una venta
     * 
     * @param int $ventaId
     * @return \Illuminate\Support\Collection
     */
    public function historialModificacionesVenta(int $ventaId)
    {
        $venta = Venta::findOrFail($ventaId);
        
        return $venta->cadenaModificaciones()->map(function ($v, $index) {
            return [
                'version' => $index + 1,
                'id' => $v->id,
                'fecha' => $v->fecha,
                'total' => $v->total,
                'estado' => $v->estado,
                'usuario' => $v->usuario->name,
                'cliente' => $v->cliente ? $v->cliente->nombre : 'Público general',
                'es_original' => is_null($v->venta_original_id),
                'es_activa' => is_null($v->reemplazada_por) && $v->estado === 'completada',
                'motivo_anulacion' => $v->motivo_anulacion,
            ];
        });
    }
}