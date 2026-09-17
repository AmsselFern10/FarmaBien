<?php

namespace App\Services;

use App\Models\Lote;
use App\Models\Producto;
use App\Models\MovimientoInventario;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;

class InventarioService
{
    /**
     * Realizar ajuste de inventario manual con bloqueo de concurrencia y Kardex auditado
     * 
     * @param array $data ['lote_id', 'stock_nuevo', 'motivo', 'subtipo']
     * @return MovimientoInventario
     * @throws Exception
     */
    public function ajustarInventario(array $data): MovimientoInventario
    {
        return DB::transaction(function () use ($data) {
            
            // 1. Obtener el lote con bloqueo pesimista (lockForUpdate)
            $lote = Lote::with('producto')
                ->where('id', $data['lote_id'])
                ->lockForUpdate()
                ->firstOrFail();

            if (!$lote->activo) {
                throw new Exception("El lote {$lote->numero_lote} del producto {$lote->producto->nombre} está inactivo.");
            }

            $stockAnterior = $lote->stock_actual;
            $stockNuevo = (int) $data['stock_nuevo'];
            
            if ($stockNuevo < 0) {
                throw new Exception("El nuevo stock no puede ser un número negativo.");
            }

            $diferencia = $stockNuevo - $stockAnterior;

            if ($diferencia === 0) {
                throw new Exception("El stock nuevo ({$stockNuevo}) es idéntico al stock actual ({$stockAnterior}). No hay cambios que registrar.");
            }

            // 2. Determinar tipo y subtipo
            $tipo = $diferencia > 0 ? 'entrada' : 'salida';
            $subtipo = $data['subtipo'] ?? ($diferencia > 0 ? 'ajuste_positivo' : 'ajuste_negativo');
            
            // 3. Actualizar el stock del lote
            $lote->stock_actual = $stockNuevo;
            $lote->save();

            // 4. Registrar movimiento en Kardex
            $costoUnitario = (float) $lote->precio_compra;
            $costoTotal = round(abs($diferencia) * $costoUnitario, 2);

            $movimiento = MovimientoInventario::create([
                'producto_id' => $lote->producto_id,
                'lote_id' => $lote->id,
                'user_id' => Auth::id() ?? 1,
                'tipo' => $tipo,
                'subtipo' => $subtipo,
                'cantidad' => $diferencia,
                'stock_anterior' => $stockAnterior,
                'stock_posterior' => $stockNuevo,
                'costo_unitario' => $costoUnitario,
                'costo_total' => $costoTotal,
                'origen' => 'ajuste_manual',
                'origen_id' => null,
                'motivo' => $data['motivo'] ?? 'Ajuste manual de inventario',
                'fecha_movimiento' => now(),
            ]);

            return $movimiento->load(['lote', 'producto', 'usuario']);
        });
    }

    /**
     * Obtener productos con stock bajo (optimizado con consulta directa)
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function productosConStockBajo()
    {
        return Producto::with(['categoria', 'laboratorio', 'lotes' => function ($query) {
                $query->disponibles()->orderBy('fecha_vencimiento', 'asc');
            }])
            ->activos()
            ->bajoStock()
            ->get()
            ->map(function ($producto) {
                $producto->stock_disponible = $producto->stock_disponible;
                return $producto;
            });
    }

    /**
     * Obtener lotes próximos a vencer con stock disponible
     * 
     * @param int $dias
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function lotesProximosVencer(int $dias = 30)
    {
        return Lote::with(['producto.categoria', 'producto.laboratorio', 'proveedor'])
            ->proximosVencer($dias)
            ->orderBy('fecha_vencimiento', 'asc')
            ->get()
            ->map(function ($lote) {
                $lote->dias_para_vencer = (int) now()->diffInDays($lote->fecha_vencimiento, false);
                return $lote;
            });
    }

    /**
     * Obtener lotes vencidos que aún tienen stock
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function lotesVencidos()
    {
        return Lote::with(['producto.categoria', 'producto.laboratorio', 'proveedor'])
            ->activos()
            ->vencidos()
            ->where('stock_actual', '>', 0)
            ->orderBy('fecha_vencimiento', 'asc')
            ->get();
    }

    /**
     * Obtener Kardex completo de un producto
     * 
     * @param int $productoId
     * @param string|null $fechaInicio
     * @param string|null $fechaFin
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function kardexProducto(int $productoId, ?string $fechaInicio = null, ?string $fechaFin = null)
    {
        $query = MovimientoInventario::with(['lote', 'usuario'])
            ->where('producto_id', $productoId);

        if ($fechaInicio) {
            $query->whereDate('fecha_movimiento', '>=', $fechaInicio);
        }

        if ($fechaFin) {
            $query->whereDate('fecha_movimiento', '<=', $fechaFin);
        }

        return $query->orderBy('fecha_movimiento', 'desc')
            ->orderBy('id', 'desc')
            ->get();
    }

    /**
     * Obtener Kardex de un lote específico
     * 
     * @param int $loteId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function kardexLote(int $loteId)
    {
        return MovimientoInventario::with(['usuario', 'producto'])
            ->where('lote_id', $loteId)
            ->orderBy('fecha_movimiento', 'desc')
            ->orderBy('id', 'desc')
            ->get();
    }

    /**
     * Desactivar lotes vencidos automáticamente y registrar merma en Kardex
     * 
     * @return int Cantidad de lotes dados de baja
     */
    public function desactivarLotesVencidos(): int
    {
        return DB::transaction(function () {
            $lotesVencidos = Lote::where('activo', true)
                ->where('fecha_vencimiento', '<=', now()->toDateString())
                ->where('stock_actual', '>', 0)
                ->lockForUpdate()
                ->get();

            $contador = 0;

            foreach ($lotesVencidos as $lote) {
                $stockAnterior = $lote->stock_actual;
                $costoUnitario = (float) $lote->precio_compra;
                $costoTotal = round($stockAnterior * $costoUnitario, 2);

                // Actualizar lote
                $lote->stock_actual = 0;
                $lote->activo = false;
                $lote->save();

                // Registrar en Kardex como merma por vencimiento
                MovimientoInventario::create([
                    'producto_id' => $lote->producto_id,
                    'lote_id' => $lote->id,
                    'user_id' => Auth::id() ?? 1,
                    'tipo' => 'salida',
                    'subtipo' => 'merma_vencimiento',
                    'cantidad' => -$stockAnterior,
                    'stock_anterior' => $stockAnterior,
                    'stock_posterior' => 0,
                    'costo_unitario' => $costoUnitario,
                    'costo_total' => $costoTotal,
                    'origen' => 'vencimiento_automatico',
                    'origen_id' => null,
                    'motivo' => "Baja automática por vencimiento (Fecha: {$lote->fecha_vencimiento->format('d/m/Y')})",
                    'fecha_movimiento' => now(),
                ]);

                $contador++;
            }

            return $contador;
        });
    }

    /**
     * Valorización integral del inventario (PEPS / Costo de adquisición por lote)
     * 
     * @return array
     */
    public function valorizacionInventario(): array
    {
        $lotes = Lote::with(['producto.categoria', 'producto.laboratorio'])
            ->where('activo', true)
            ->where('stock_actual', '>', 0)
            ->get();

        $valorTotal = 0;
        $cantidadTotal = 0;
        $productosIds = [];
        $detalles = [];

        foreach ($lotes as $lote) {
            $valorLote = round($lote->stock_actual * $lote->precio_compra, 2);
            $valorTotal += $valorLote;
            $cantidadTotal += $lote->stock_actual;
            $productosIds[$lote->producto_id] = true;

            $detalles[] = [
                'producto_id' => $lote->producto_id,
                'producto' => $lote->producto->nombre_completo ?? $lote->producto->nombre,
                'categoria' => $lote->producto->categoria->nombre ?? 'Sin categoría',
                'laboratorio' => $lote->producto->laboratorio->nombre ?? 'Sin laboratorio',
                'lote' => $lote->numero_lote,
                'fecha_vencimiento' => $lote->fecha_vencimiento->format('d/m/Y'),
                'stock' => $lote->stock_actual,
                'precio_compra' => (float) $lote->precio_compra,
                'valor_total' => $valorLote,
            ];
        }

        return [
            'valor_total' => round($valorTotal, 2),
            'total_unidades' => $cantidadTotal,
            'total_lotes_activos' => count($detalles),
            'total_productos' => count($productosIds),
            'detalles' => collect($detalles)->sortByDesc('valor_total')->values()->all(),
        ];
    }
}
