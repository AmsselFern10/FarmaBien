<?php

namespace App\Services;

use App\Models\Lote;
use App\Models\Producto;
use App\Models\MovimientoInventario;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class InventarioService
{
    /**
     * Realizar ajuste de inventario manual con transacción ACID, bloqueo pesimista y Kardex auditado
     * 
     * @param array $data ['lote_id', 'stock_nuevo', 'motivo', 'subtipo']
     * @return MovimientoInventario
     * @throws Exception
     */
    public function ajustarInventario(array $data): MovimientoInventario
    {
        return DB::transaction(function () use ($data) {
            
            // 1. Obtener el lote con bloqueo pesimista estricto (lockForUpdate)
            $lote = Lote::with('producto')
                ->where('id', $data['lote_id'])
                ->lockForUpdate()
                ->firstOrFail();

            // Bloquear también el producto para serializar operaciones concurrentes
            $producto = Producto::where('id', $lote->producto_id)
                ->lockForUpdate()
                ->firstOrFail();

            if (!$lote->activo) {
                throw new Exception("El lote '{$lote->numero_lote}' del producto '{$producto->nombre}' está desactivado.");
            }

            $stockAnterior = (int) $lote->stock_actual;
            $stockNuevo = (int) $data['stock_nuevo'];
            
            if ($stockNuevo < 0) {
                throw new Exception("El nuevo stock no puede ser un valor negativo ({$stockNuevo}).");
            }

            $diferencia = $stockNuevo - $stockAnterior;

            if ($diferencia === 0) {
                throw new Exception("El stock nuevo ({$stockNuevo}) es idéntico al stock actual ({$stockAnterior}). No hay discrepancia que registrar.");
            }

            // 2. Determinar tipo y normalizar subtipo contra el enum de la base de datos
            $tipo = $diferencia > 0 ? 'entrada' : 'salida';
            $subtipoRaw = $data['subtipo'] ?? 'ajuste_manual';
            
            // Normalizar a los enums soportados por la tabla movimientos_inventario
            $subtiposValidos = [
                'ajuste_manual',
                'merma_vencimiento',
                'merma_danio',
                'vencimiento_automatico',
                'compra',
                'venta',
                'anulacion_venta',
                'anulacion_compra'
            ];

            $subtipo = in_array($subtipoRaw, $subtiposValidos, true) ? $subtipoRaw : 'ajuste_manual';
            
            // 3. Actualizar el stock del lote
            $lote->stock_actual = $stockNuevo;
            
            // Si se vacía por merma de vencimiento o daño, se desactiva el lote
            if ($stockNuevo === 0 && in_array($subtipo, ['merma_vencimiento', 'merma_danio'])) {
                $lote->activo = false;
            }

            $lote->save();

            // 4. Invariante matemático del Kardex
            $costoUnitario = (float) $lote->precio_compra;
            $costoTotal = round(abs($diferencia) * $costoUnitario, 2);
            $userId = Auth::id() ?? 1;

            $movimiento = MovimientoInventario::create([
                'producto_id'      => $lote->producto_id,
                'lote_id'          => $lote->id,
                'user_id'          => $userId,
                'tipo'             => $tipo,
                'subtipo'          => $subtipo,
                'cantidad'         => $diferencia,
                'stock_anterior'   => $stockAnterior,
                'stock_posterior'  => $stockNuevo,
                'costo_unitario'   => $costoUnitario,
                'costo_total'      => $costoTotal,
                'origen'           => 'ajuste_manual',
                'origen_id'        => null,
                'motivo'           => trim($data['motivo']),
                'fecha_movimiento' => now(),
            ]);

            Log::info('Ajuste de inventario procesado en Kardex', [
                'movimiento_id'   => $movimiento->id,
                'lote_id'         => $lote->id,
                'producto_id'     => $lote->producto_id,
                'stock_anterior'  => $stockAnterior,
                'stock_posterior' => $stockNuevo,
                'diferencia'      => $diferencia,
                'subtipo'         => $subtipo,
                'user_id'         => $userId,
            ]);

            return $movimiento->load(['lote', 'producto', 'usuario']);
        });
    }

    /**
     * Descontar stock bajo algoritmo FIFO estricto con bloqueo pesimista para evitar sobreventas
     * 
     * @param Producto $producto
     * @param int $unidadesRequeridas
     * @param string $origen
     * @param int|null $origenId
     * @param string|null $motivo
     * @return array Array de asignaciones por lote [{lote, cantidad_descontada, costo_unitario, costo_total}]
     * @throws Exception
     */
    public function descontarStockFIFO(Producto $producto, int $unidadesRequeridas, string $origen = 'venta', ?int $origenId = null, ?string $motivo = null): array
    {
        if ($unidadesRequeridas <= 0) {
            throw new Exception("La cantidad de unidades a descontar debe ser mayor a cero.");
        }

        // Obtener todos los lotes vigentes y activos del producto ordenados por fecha de vencimiento ascendente (FIFO)
        $lotes = Lote::where('producto_id', $producto->id)
            ->where('activo', true)
            ->where('fecha_vencimiento', '>', now()->toDateString())
            ->where('stock_actual', '>', 0)
            ->orderBy('fecha_vencimiento', 'asc')
            ->orderBy('id', 'asc')
            ->lockForUpdate()
            ->get();

        $stockDisponibleTotal = $lotes->sum('stock_actual');

        if ($stockDisponibleTotal < $unidadesRequeridas) {
            throw new Exception("Stock insuficiente para '{$producto->nombre}'. Solicitado: {$unidadesRequeridas}, Disponible: {$stockDisponibleTotal}.");
        }

        $pendientes = $unidadesRequeridas;
        $asignaciones = [];
        $userId = Auth::id() ?? 1;

        foreach ($lotes as $lote) {
            if ($pendientes <= 0) break;

            $aDescontar = min($lote->stock_actual, $pendientes);
            $stockAnterior = $lote->stock_actual;
            $stockNuevo = $stockAnterior - $aDescontar;

            $lote->stock_actual = $stockNuevo;
            $lote->save();

            $costoUnitario = (float) $lote->precio_compra;
            $costoTotal = round($aDescontar * $costoUnitario, 2);

            // Registrar movimiento en Kardex
            MovimientoInventario::create([
                'producto_id'      => $producto->id,
                'lote_id'          => $lote->id,
                'user_id'          => $userId,
                'tipo'             => 'salida',
                'subtipo'          => $origen === 'venta' ? 'venta' : 'ajuste_manual',
                'cantidad'         => -$aDescontar,
                'stock_anterior'   => $stockAnterior,
                'stock_posterior'  => $stockNuevo,
                'costo_unitario'   => $costoUnitario,
                'costo_total'      => $costoTotal,
                'origen'           => $origen,
                'origen_id'        => $origenId,
                'motivo'           => $motivo ?? "Descuento FIFO por {$origen}",
                'fecha_movimiento' => now(),
            ]);

            $asignaciones[] = [
                'lote'                 => $lote,
                'cantidad_descontada'  => $aDescontar,
                'costo_unitario'       => $costoUnitario,
                'costo_total'          => $costoTotal,
            ];

            $pendientes -= $aDescontar;
        }

        return $asignaciones;
    }

    /**
     * Revertir stock a un lote específico (anulaciones de venta o devoluciones)
     * 
     * @param Lote $lote
     * @param int $unidadesRevertir
     * @param string $subtipo
     * @param string|null $origen
     * @param int|null $origenId
     * @param string|null $motivo
     * @return MovimientoInventario
     * @throws Exception
     */
    public function revertirStockLote(Lote $lote, int $unidadesRevertir, string $subtipo = 'anulacion_venta', ?string $origen = 'venta', ?int $origenId = null, ?string $motivo = null): MovimientoInventario
    {
        if ($unidadesRevertir <= 0) {
            throw new Exception("La cantidad a revertir debe ser mayor a cero.");
        }

        $lockedLote = Lote::where('id', $lote->id)->lockForUpdate()->firstOrFail();
        $stockAnterior = (int) $lockedLote->stock_actual;
        $stockNuevo = $stockAnterior + $unidadesRevertir;

        $lockedLote->stock_actual = $stockNuevo;
        $lockedLote->activo = true;
        $lockedLote->save();

        $costoUnitario = (float) $lockedLote->precio_compra;
        $costoTotal = round($unidadesRevertir * $costoUnitario, 2);
        $userId = Auth::id() ?? 1;

        return MovimientoInventario::create([
            'producto_id'      => $lockedLote->producto_id,
            'lote_id'          => $lockedLote->id,
            'user_id'          => $userId,
            'tipo'             => 'entrada',
            'subtipo'          => $subtipo,
            'cantidad'         => $unidadesRevertir,
            'stock_anterior'   => $stockAnterior,
            'stock_posterior'  => $stockNuevo,
            'costo_unitario'   => $costoUnitario,
            'costo_total'      => $costoTotal,
            'origen'           => $origen,
            'origen_id'        => $origenId,
            'motivo'           => $motivo ?? "Reversión de stock por {$subtipo}",
            'fecha_movimiento' => now(),
        ]);
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
            ->get();
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
            $userId = Auth::id() ?? 1;

            foreach ($lotesVencidos as $lote) {
                $stockAnterior = (int) $lote->stock_actual;
                $costoUnitario = (float) $lote->precio_compra;
                $costoTotal = round($stockAnterior * $costoUnitario, 2);

                // Actualizar lote
                $lote->stock_actual = 0;
                $lote->activo = false;
                $lote->save();

                // Registrar en Kardex como merma por vencimiento
                MovimientoInventario::create([
                    'producto_id'      => $lote->producto_id,
                    'lote_id'          => $lote->id,
                    'user_id'          => $userId,
                    'tipo'             => 'salida',
                    'subtipo'          => 'vencimiento_automatico',
                    'cantidad'         => -$stockAnterior,
                    'stock_anterior'   => $stockAnterior,
                    'stock_posterior'  => 0,
                    'costo_unitario'   => $costoUnitario,
                    'costo_total'      => $costoTotal,
                    'origen'           => 'vencimiento_automatico',
                    'origen_id'        => null,
                    'motivo'           => "Baja automática por vencimiento (Fecha: {$lote->fecha_vencimiento->format('d/m/Y')})",
                    'fecha_movimiento' => now(),
                ]);

                $contador++;
            }

            Log::info('Proceso automático de baja de lotes vencidos ejecutado', [
                'total_lotes_baja' => $contador,
                'user_id'          => $userId,
            ]);

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
                'producto_id'        => $lote->producto_id,
                'producto'           => $lote->producto->nombre_completo ?? $lote->producto->nombre,
                'categoria'          => $lote->producto->categoria->nombre ?? 'Sin categoría',
                'laboratorio'        => $lote->producto->laboratorio->nombre ?? 'Sin laboratorio',
                'lote'               => $lote->numero_lote,
                'fecha_vencimiento'  => $lote->fecha_vencimiento->format('d/m/Y'),
                'stock'              => $lote->stock_actual,
                'precio_compra'      => (float) $lote->precio_compra,
                'valor_total'        => $valorLote,
            ];
        }

        return [
            'valor_total'        => round($valorTotal, 2),
            'total_unidades'     => $cantidadTotal,
            'total_lotes_activos'=> count($detalles),
            'total_productos'    => count($productosIds),
            'detalles'           => collect($detalles)->sortByDesc('valor_total')->values()->all(),
        ];
    }
}
