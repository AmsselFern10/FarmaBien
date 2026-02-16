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
     * Realizar ajuste de inventario manual (append-only, vía movimiento).
     *
     * @param array $data
     * @return MovimientoInventario
     * @throws Exception
     */
    public function ajustarInventario(array $data): MovimientoInventario
    {
        return DB::transaction(function () use ($data) {

            $lote = Lote::with('producto')->lockForUpdate()->findOrFail($data['lote_id']);

            if (!$lote->activo) {
                throw new Exception("El lote {$lote->numero_lote} está inactivo.");
            }

            if (!empty($lote->bloqueado_at) || $lote->estado === 'bloqueado') {
                throw new Exception("El lote {$lote->numero_lote} está bloqueado y no permite movimientos.");
            }

            $stockActual = (int) $lote->stock_actual;
            $stockNuevo = (int) $data['stock_nuevo'];
            $diferencia = $stockNuevo - $stockActual;

            if ($diferencia === 0) {
                throw new Exception("El stock nuevo es igual al stock actual.");
            }

            $movimiento = MovimientoInventario::create([
                'producto_id' => $lote->producto_id, // se forzará a coincidir con el lote en el modelo
                'lote_id' => $lote->id,
                'user_id' => Auth::id(),
                'tipo' => 'ajuste',
                'cantidad' => $diferencia, // +/- según ajuste
                'origen' => 'ajuste_manual',
                'origen_id' => null,
                'motivo' => $data['motivo'] ?? 'Ajuste manual de inventario',
                'fecha_movimiento' => now(),
            ]);

            return $movimiento->load(['lote', 'producto', 'usuario']);
        });
    }

    /**
     * Productos con stock bajo
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
     * Lotes próximos a vencer con stock
     */
    public function lotesProximosVencer(int $dias = 30)
    {
        return Lote::with(['producto', 'proveedor'])
            ->activos()
            ->proximosVencer($dias)
            ->where('stock_actual', '>', 0)
            ->orderBy('fecha_vencimiento', 'asc')
            ->get()
            ->map(function ($lote) use ($dias) {
                $lote->stock_disponible = $lote->stock_actual;
                $lote->dias_para_vencer = now()->diffInDays($lote->fecha_vencimiento);
                return $lote;
            });
    }

    /**
     * Lotes vencidos con stock
     */
    public function lotesVencidos()
    {
        return Lote::with(['producto', 'proveedor'])
            ->activos()
            ->vencidos()
            ->where('stock_actual', '>', 0)
            ->orderBy('fecha_vencimiento', 'asc')
            ->get()
            ->map(function ($lote) {
                $lote->stock_disponible = $lote->stock_actual;
                return $lote;
            });
    }

    /**
     * Kardex de un producto (secuencia de movimientos por producto).
     * Nota: El stock acumulado aquí es "global por producto" (sumatoria de deltas),
     * no el saldo del lote (que va en movimiento.saldo_nuevo).
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

        $stockAcumulado = 0;

        return $movimientos->map(function ($movimiento) use (&$stockAcumulado) {
            $stockAcumulado += (int) $movimiento->cantidad;
            $movimiento->stock_acumulado = $stockAcumulado;
            return $movimiento;
        });
    }

    /**
     * Kardex de un lote específico (usa saldo_nuevo, sin recalcular).
     */
    public function kardexLote(int $loteId)
    {
        $movimientos = MovimientoInventario::with(['usuario'])
            ->where('lote_id', $loteId)
            ->orderBy('fecha_movimiento', 'asc')
            ->get();

        return $movimientos->map(function ($movimiento) {
            $movimiento->stock_acumulado = $movimiento->saldo_nuevo; // directo de BD
            return $movimiento;
        });
    }

    /**
     * Resumen por categoría (sin subconsultas pesadas, usando lotes.stock_actual).
     */
    public function resumenPorCategoria()
    {
        return DB::table('categorias')
            ->join('productos', 'productos.categoria_id', '=', 'categorias.id')
            ->leftJoin('lotes', function ($join) {
                $join->on('lotes.producto_id', '=', 'productos.id')
                    ->where('lotes.activo', '=', 1);
            })
            ->where('productos.activo', '=', 1)
            ->groupBy('categorias.id', 'categorias.nombre')
            ->select(
                'categorias.nombre as categoria_nombre',
                DB::raw('COUNT(DISTINCT productos.id) as total_productos'),
                DB::raw('COALESCE(SUM(lotes.stock_actual), 0) as stock_total'),
                DB::raw('COALESCE(SUM(lotes.stock_actual * lotes.precio_compra), 0) as valor_total')
            )
            ->get();
    }

    /**
     * Desactivar lotes vencidos automáticamente.
     * - Marca activo = false
     * - Registra ajuste para llevar stock a 0
     */
    public function desactivarLotesVencidos(): int
    {
        return DB::transaction(function () {
            $lotesVencidos = Lote::activos()
                ->vencidos()
                ->where('stock_actual', '>', 0)
                ->lockForUpdate()
                ->get();

            $contador = 0;

            foreach ($lotesVencidos as $lote) {
                // Marca el lote como inactivo (operativo) y vencido
                $lote->update([
                    'activo' => false,
                    'estado' => 'vencido',
                ]);

                // Ajuste para dejar el stock en 0 (append-only)
                MovimientoInventario::create([
                    'producto_id' => $lote->producto_id,
                    'lote_id' => $lote->id,
                    'user_id' => Auth::id(),
                    'tipo' => 'ajuste',
                    'cantidad' => -((int) $lote->stock_actual),
                    'origen' => 'vencimiento_automatico',
                    'origen_id' => null,
                    'motivo' => "Lote vencido - Fecha: {$lote->fecha_vencimiento->format('d/m/Y')}",
                    'fecha_movimiento' => now(),
                ]);

                $contador++;
            }

            return $contador;
        });
    }

    /**
     * Valorización del inventario (usa lotes.stock_actual directo)
     */
    public function valorizacionInventario(): array
    {
        $lotes = Lote::with('producto')->activos()->where('stock_actual', '>', 0)->get();

        $valorTotal = 0;
        $cantidadTotal = 0;
        $productos = [];
        $detalles = [];

        foreach ($lotes as $lote) {
            $stockActual = (int) $lote->stock_actual;

            $valorLote = $stockActual * (float) $lote->precio_compra;
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

        return [
            'valor_total' => $valorTotal,
            'total_unidades' => $cantidadTotal,
            'total_lotes_activos' => count($detalles),
            'total_productos' => count($productos),
            'detalles' => collect($detalles)->sortByDesc('valor_total')->values()->all(),
        ];
    }
}
