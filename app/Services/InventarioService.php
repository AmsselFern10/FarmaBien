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
     * Realizar ajuste de inventario manual
     * 
     * @param array $data
     * @return MovimientoInventario
     * @throws Exception
     */
    public function ajustarInventario(array $data): MovimientoInventario
    {
        return DB::transaction(function () use ($data) {
            
            // 1. Obtener el lote
            $lote = Lote::with('producto')->findOrFail($data['lote_id']);

            // 2. Validar que el lote esté activo
            if (!$lote->activo) {
                throw new Exception("El lote {$lote->numero_lote} está inactivo.");
            }

            // 3. Calcular la cantidad del ajuste
            $stockActual = $lote->stock_actual;
            $stockNuevo = $data['stock_nuevo'];
            $diferencia = $stockNuevo - $stockActual;

            // Si no hay diferencia, no hacer nada
            if ($diferencia === 0) {
                throw new Exception("El stock nuevo es igual al stock actual.");
            }

            // 4. Crear movimiento de ajuste
            $movimiento = MovimientoInventario::create([
                'producto_id' => $lote->producto_id,
                'lote_id' => $lote->id,
                'user_id' => Auth::id(),
                'tipo' => 'ajuste',
                'cantidad' => $diferencia, // Positivo o negativo según el ajuste
                'origen' => 'ajuste_manual',
                'origen_id' => null,
                'motivo' => $data['motivo'] ?? 'Ajuste manual de inventario',
                'fecha_movimiento' => now(),
            ]);

            return $movimiento->load(['lote', 'producto', 'usuario']);
        });
    }

    /**
     * Obtener productos con stock bajo
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function productosConStockBajo()
    {
        return Producto::with(['categoria', 'lotes' => function ($query) {
                $query->activos();
            }])
            ->activos()
            ->bajoStock()
            ->get()
            ->map(function ($producto) {
                $producto->stock_disponible = $producto->stock_total;
                return $producto;
            });
    }

    /**
     * Obtener lotes próximos a vencer
     * 
     * @param int $dias Días de anticipación (default: 30)
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function lotesProximosVencer(int $dias = 30)
    {
        return Lote::with(['producto', 'proveedor'])
            ->activos()
            ->proximosVencer($dias)
            ->whereRaw('stock_inicial + (
                SELECT COALESCE(SUM(cantidad), 0) 
                FROM movimientos_inventario 
                WHERE movimientos_inventario.lote_id = lotes.id
            ) > 0') // Solo lotes con stock
            ->orderBy('fecha_vencimiento', 'asc')
            ->get()
            ->map(function ($lote) {
                $lote->stock_disponible = $lote->stock_actual;
                $lote->dias_para_vencer = now()->diffInDays($lote->fecha_vencimiento);
                return $lote;
            });
    }

    /**
     * Obtener lotes vencidos con stock
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function lotesVencidos()
    {
        return Lote::with(['producto', 'proveedor'])
            ->activos()
            ->vencidos()
            ->whereRaw('stock_inicial + (
                SELECT COALESCE(SUM(cantidad), 0) 
                FROM movimientos_inventario 
                WHERE movimientos_inventario.lote_id = lotes.id
            ) > 0')
            ->orderBy('fecha_vencimiento', 'asc')
            ->get()
            ->map(function ($lote) {
                $lote->stock_disponible = $lote->stock_actual;
                return $lote;
            });
    }

    /**
     * Obtener kardex de un producto (historial de movimientos)
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

        $movimientos = $query->orderBy('fecha_movimiento', 'asc')->get();

        // Calcular stock acumulado
        $stockAcumulado = 0;
        
        return $movimientos->map(function ($movimiento) use (&$stockAcumulado) {
            $stockAcumulado += $movimiento->cantidad;
            $movimiento->stock_acumulado = $stockAcumulado;
            return $movimiento;
        });
    }

    /**
     * Obtener kardex de un lote específico
     * 
     * @param int $loteId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function kardexLote(int $loteId)
    {
        $lote = Lote::findOrFail($loteId);
        
        $movimientos = MovimientoInventario::with(['usuario'])
            ->where('lote_id', $loteId)
            ->orderBy('fecha_movimiento', 'asc')
            ->get();

        // Calcular stock acumulado partiendo del stock inicial
        $stockAcumulado = $lote->stock_inicial;
        
        return $movimientos->map(function ($movimiento) use (&$stockAcumulado) {
            $stockAcumulado += $movimiento->cantidad;
            $movimiento->stock_acumulado = $stockAcumulado;
            return $movimiento;
        });
    }

    /**
     * Obtener resumen de inventario por categoría
     * 
     * @return \Illuminate\Support\Collection
     */
   public function resumenPorCategoria()
{
    return DB::table('productos')
        ->join('categorias', 'productos.categoria_id', '=', 'categorias.id')
        ->select(
            'categorias.nombre as categoria_nombre',
            DB::raw('COUNT(productos.id) as total_productos'),
            DB::raw('SUM(COALESCE((
                SELECT SUM(l.stock_inicial + COALESCE((
                    SELECT SUM(m.cantidad)
                    FROM movimientos_inventario m
                    WHERE m.lote_id = l.id
                ), 0))
                FROM lotes l
                WHERE l.producto_id = productos.id
                AND l.activo = 1
            ), 0)) as stock_total'),
            DB::raw('SUM(COALESCE((
                SELECT SUM((l.stock_inicial + COALESCE((
                    SELECT SUM(m.cantidad)
                    FROM movimientos_inventario m
                    WHERE m.lote_id = l.id
                ), 0)) * l.precio_compra)
                FROM lotes l
                WHERE l.producto_id = productos.id
                AND l.activo = 1
            ), 0)) as valor_total') // ✅ agregado
        )
        ->where('productos.activo', true)
        ->groupBy('categorias.id', 'categorias.nombre')
        ->get();
}


    /**
     * Desactivar lotes vencidos automáticamente
     * 
     * @return int Cantidad de lotes desactivados
     */
    public function desactivarLotesVencidos(): int
    {
        return DB::transaction(function () {
            $lotesVencidos = Lote::activos()
                ->vencidos()
                ->get();

            $contador = 0;

            foreach ($lotesVencidos as $lote) {
                // Solo desactivar si tiene stock
                if ($lote->stock_actual > 0) {
                    $lote->update(['activo' => false]);
                    
                    // Registrar movimiento
                    MovimientoInventario::create([
                        'producto_id' => $lote->producto_id,
                        'lote_id' => $lote->id,
                        'user_id' => Auth::id(),
                        'tipo' => 'ajuste',
                        'cantidad' => -$lote->stock_actual,
                        'origen' => 'vencimiento_automatico',
                        'origen_id' => null,
                        'motivo' => "Lote vencido - Fecha: {$lote->fecha_vencimiento->format('d/m/Y')}",
                        'fecha_movimiento' => now(),
                    ]);

                    $contador++;
                }
            }

            return $contador;
        });
    }

    /**
     * Obtener valorización del inventario
     * 
     * @return array
     */
   public function valorizacionInventario(): array
{
    $lotes = Lote::with('producto')->activos()->get();

    $valorTotal = 0;
    $cantidadTotal = 0;
    $productos = [];

    foreach ($lotes as $lote) {
        $stockActual = $lote->stock_actual;

        if ($stockActual > 0) {
            $valorLote = $stockActual * $lote->precio_compra;
            $valorTotal += $valorLote;
            $cantidadTotal += $stockActual;

            $productos[$lote->producto_id] = $lote->producto->nombre;

            $detalles[] = [
                'producto' => $lote->producto->nombre,
                'lote' => $lote->numero_lote,
                'stock' => $stockActual,
                'precio_compra' => $lote->precio_compra,
                'valor_total' => $valorLote,
            ];
        }
    }

    return [
        'valor_total' => $valorTotal,
        'total_unidades' => $cantidadTotal,
        'total_lotes_activos' => count($detalles ?? []),
        'total_productos' => count($productos), // ✅ AQUÍ agregamos total_productos
        'detalles' => collect($detalles ?? [])->sortByDesc('valor_total')->values()->all(),
    ];
}

    
}