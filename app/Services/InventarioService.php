<?php

namespace App\Services;

use App\Models\Lote;
use App\Models\Producto;
use App\Models\MovimientoInventario;
use App\Support\RequestCache;
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

            // Asentar en Libro Oficial MINSA si el producto es controlado
            if ($producto->esControlado()) {
                $tipoMovCtrl = $diferencia > 0
                    ? \App\Models\RegistroVentaControlado::TIPO_AJUSTE_INGRESO
                    : \App\Models\RegistroVentaControlado::TIPO_AJUSTE_EGRESO;

                $motivoTexto = $diferencia > 0
                    ? "Ajuste Físico Positivo (+) [{$subtipo}]: " . trim($data['motivo'])
                    : "Baja por Ajuste Físico (-) [{$subtipo}]: " . trim($data['motivo']);

                \App\Models\RegistroVentaControlado::create([
                    'tipo_movimiento'          => $tipoMovCtrl,
                    'movimiento_inventario_id' => $movimiento->id,
                    'producto_id'              => $producto->id,
                    'lote_id'                  => $lote->id,
                    'nivel_controlado'         => 1,
                    'paciente_nombre'          => 'Regencia Farmacéutica / Auditoría',
                    'motivo_omision'           => $motivoTexto,
                    'cantidad'                 => abs($diferencia),
                    'unidad'                   => 'unidad',
                    'user_id'                  => $userId,
                ]);
            }

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

            // Invalidar caché de valorización del inventario y notificaciones
            \Illuminate\Support\Facades\Cache::forget('inventario_valorizacion');
            \App\Services\NotificacionService::clearCache();

            return $movimiento->load(['lote', 'producto', 'usuario']);
        });
    }

    /**
     * Registra un nuevo lote manual (stock de apertura, donación, migración)
     * y genera su correspondiente movimiento de entrada en Kardex y Libro MINSA si aplica.
     * 
     * @param array $data ['producto_id', 'numero_lote', 'fecha_vencimiento', 'cantidad', 'precio_compra', 'proveedor_id', 'motivo']
     * @return Lote
     * @throws Exception
     */
    public function crearLoteManual(array $data): Lote
    {
        return DB::transaction(function () use ($data) {
            $producto = Producto::where('id', $data['producto_id'])->lockForUpdate()->firstOrFail();
            $cantidad = (int) $data['cantidad'];
            $precioCompra = !empty($data['precio_compra']) ? (float) $data['precio_compra'] : (float) ($producto->precio_compra ?? 0);
            $userId = Auth::id() ?? 1;

            $lote = Lote::create([
                'producto_id'       => $producto->id,
                'compra_id'         => null,
                'proveedor_id'      => !empty($data['proveedor_id']) ? (int) $data['proveedor_id'] : null,
                'numero_lote'       => trim($data['numero_lote']),
                'fecha_vencimiento' => $data['fecha_vencimiento'],
                'stock_inicial'     => $cantidad,
                'stock_actual'      => $cantidad,
                'precio_compra'     => $precioCompra,
                'activo'            => true,
            ]);

            $costoTotal = round($cantidad * $precioCompra, 2);

            $movimiento = MovimientoInventario::create([
                'producto_id'      => $producto->id,
                'lote_id'          => $lote->id,
                'user_id'          => $userId,
                'tipo'             => 'entrada',
                'subtipo'          => 'ajuste_manual',
                'cantidad'         => $cantidad,
                'stock_anterior'   => 0,
                'stock_posterior'  => $cantidad,
                'costo_unitario'   => $precioCompra,
                'costo_total'      => $costoTotal,
                'origen'           => 'lote_manual',
                'origen_id'        => $lote->id,
                'motivo'           => 'Apertura / Lote manual: ' . trim($data['motivo']),
                'fecha_movimiento' => now(),
            ]);

            // Asentar en Libro Oficial MINSA si el producto es controlado
            if ($producto->esControlado()) {
                \App\Models\RegistroVentaControlado::create([
                    'tipo_movimiento'          => \App\Models\RegistroVentaControlado::TIPO_AJUSTE_INGRESO,
                    'movimiento_inventario_id' => $movimiento->id,
                    'producto_id'              => $producto->id,
                    'lote_id'                  => $lote->id,
                    'nivel_controlado'         => 1,
                    'paciente_nombre'          => 'Stock Inicial / Ingreso Manual',
                    'motivo_omision'           => "Ingreso de Lote Manual [{$lote->numero_lote}]: " . trim($data['motivo']),
                    'cantidad'                 => $cantidad,
                    'unidad'                   => 'unidad',
                    'user_id'                  => $userId,
                ]);
            }

            \App\Models\AuditLog::log('inventario', 'lote_manual', "Lote manual creado: {$lote->numero_lote} ({$producto->nombre})", [
                'lote_id'     => $lote->id,
                'producto_id' => $producto->id,
                'cantidad'    => $cantidad,
                'motivo'      => $data['motivo'],
            ]);

            Log::info('Lote manual registrado en inventario y Kardex', [
                'lote_id'       => $lote->id,
                'producto_id'   => $producto->id,
                'movimiento_id' => $movimiento->id,
                'cantidad'      => $cantidad,
                'user_id'       => $userId,
            ]);

            \Illuminate\Support\Facades\Cache::forget('inventario_valorizacion');
            \App\Services\NotificacionService::clearCache();

            return $lote;
        });
    }

    /**
     * Actualizar metadatos de un lote (número de lote, vencimiento, proveedor)
     * dejando trazabilidad de auditoría estricta sin alterar stock.
     */
    public function actualizarMetadatosLote(Lote $lote, array $data): Lote
    {
        return DB::transaction(function () use ($lote, $data) {
            $lote->load('producto');
            $lote->lockForUpdate();

            $cambios = [];
            
            if (isset($data['numero_lote']) && trim($data['numero_lote']) !== $lote->numero_lote) {
                $cambios['numero_lote'] = [
                    'anterior' => $lote->numero_lote,
                    'nuevo' => trim($data['numero_lote']),
                ];
                $lote->numero_lote = trim($data['numero_lote']);
            }

            if (isset($data['fecha_vencimiento'])) {
                $nuevaFecha = \Carbon\Carbon::parse($data['fecha_vencimiento'])->format('Y-m-d');
                $fechaAnterior = $lote->fecha_vencimiento ? $lote->fecha_vencimiento->format('Y-m-d') : null;
                if ($nuevaFecha !== $fechaAnterior) {
                    $cambios['fecha_vencimiento'] = [
                        'anterior' => $fechaAnterior,
                        'nuevo' => $nuevaFecha,
                    ];
                    $lote->fecha_vencimiento = $nuevaFecha;
                }
            }

            if (array_key_exists('proveedor_id', $data)) {
                $nuevoProvId = !empty($data['proveedor_id']) ? (int)$data['proveedor_id'] : null;
                $anteriorProvId = $lote->proveedor_id ? (int)$lote->proveedor_id : null;
                if ($nuevoProvId !== $anteriorProvId) {
                    $cambios['proveedor_id'] = [
                        'anterior' => $anteriorProvId,
                        'nuevo' => $nuevoProvId,
                    ];
                    $lote->proveedor_id = $nuevoProvId;
                }
            }

            $lote->save();

            \App\Models\AuditLog::log('inventario', 'editar_lote', "Modificación de metadatos en lote {$lote->numero_lote} (" . ($lote->producto->nombre ?? 'N/A') . ")", [
                'lote_id' => $lote->id,
                'producto_id' => $lote->producto_id,
                'cambios' => $cambios,
                'motivo' => $data['motivo_cambio'] ?? 'Corrección de metadatos',
            ]);

            Log::info('Metadatos de lote actualizados', [
                'lote_id' => $lote->id,
                'cambios' => $cambios,
                'user_id' => Auth::id(),
            ]);

            return $lote;
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
     * Obtener productos con stock bajo (optimizado con consulta directa y memoizado por ciclo de petición)
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function productosConStockBajo()
    {
        return RequestCache::rememberStatic('alertas:productos_bajo_stock', function () {
            return Producto::with([
                'categoria:id,nombre',
                'laboratorio:id,nombre',
                'lotes' => function ($query) {
                    $query->disponibles()->orderBy('fecha_vencimiento', 'asc');
                }
            ])
                ->activos()
                ->bajoStock()
                ->get();
        });
    }

    /**
     * Obtener lotes próximos a vencer con stock disponible
     * 
     * @param int $dias
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function lotesProximosVencer(int $dias = 60)
    {
        return RequestCache::rememberStatic("alertas:lotes_proximos_vencer:{$dias}", function () use ($dias) {
            return Lote::with([
                'producto:id,nombre,principio_activo,categoria_id,laboratorio_id',
                'producto.categoria:id,nombre',
                'producto.laboratorio:id,nombre',
                'proveedor:id,nombre',
            ])
                ->proximosVencer($dias)
                ->orderBy('fecha_vencimiento', 'asc')
                ->get()
                ->map(function ($lote) {
                    $lote->dias_para_vencer = (int) $lote->dias_restantes;
                    return $lote;
                });
        });
    }

    /**
     * Obtener lotes vencidos que aún tienen stock
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function lotesVencidos()
    {
        return RequestCache::rememberStatic('alertas:lotes_vencidos', function () {
            return Lote::with([
                'producto:id,nombre,principio_activo,categoria_id,laboratorio_id',
                'producto.categoria:id,nombre',
                'producto.laboratorio:id,nombre',
                'proveedor:id,nombre',
            ])
                ->activos()
                ->vencidos()
                ->where('stock_actual', '>', 0)
                ->orderBy('fecha_vencimiento', 'asc')
                ->get();
        });
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
        $query = MovimientoInventario::with([
            'lote:id,numero_lote,fecha_vencimiento,precio_compra',
            'lote.detallesCompra:id,lote_id,compra_id,tipo_presentacion,cantidad_presentaciones,unidades_por_presentacion',
            'usuario:id,name',
        ])
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
        return MovimientoInventario::with(['usuario:id,name', 'producto:id,nombre'])
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

            \Illuminate\Support\Facades\Cache::forget('inventario_valorizacion');
            \App\Services\NotificacionService::clearCache();

            return $contador;
        });
    }

    /**
     * Valorización integral del inventario (PEPS / Costo de adquisición por lote)
     * Utiliza agregación SQL directa de alto rendimiento para 0 consumo de memoria RAM.
     * 
     * @param bool $incluirDetalles
     * @return array
     */
    public function valorizacionInventario(bool $incluirDetalles = false): array
    {
        $resumen = DB::table('lotes')
            ->where('activo', true)
            ->where('stock_actual', '>', 0)
            ->selectRaw('
                COALESCE(SUM(stock_actual * precio_compra), 0) as valor_total,
                COALESCE(SUM(stock_actual), 0) as total_unidades,
                COUNT(id) as total_lotes_activos,
                COUNT(DISTINCT producto_id) as total_productos
            ')
            ->first();

        $detalles = [];
        if ($incluirDetalles) {
            $lotes = Lote::with(['producto.categoria', 'producto.laboratorio'])
                ->where('activo', true)
                ->where('stock_actual', '>', 0)
                ->limit(200)
                ->get();

            foreach ($lotes as $lote) {
                $valorLote = round($lote->stock_actual * (float)$lote->precio_compra, 2);
                $detalles[] = [
                    'producto_id'        => $lote->producto_id,
                    'producto'           => $lote->producto?->nombre_completo ?? $lote->producto?->nombre ?? 'Medicamento',
                    'categoria'          => $lote->producto?->categoria?->nombre ?? 'Sin categoría',
                    'laboratorio'        => $lote->producto?->laboratorio?->nombre ?? 'Sin laboratorio',
                    'lote'               => $lote->numero_lote,
                    'fecha_vencimiento'  => $lote->fecha_vencimiento ? $lote->fecha_vencimiento->format('d/m/Y') : 'N/A',
                    'stock'              => $lote->stock_actual,
                    'precio_compra'      => (float) $lote->precio_compra,
                    'valor_total'        => $valorLote,
                ];
            }
        }

        return [
            'valor_total'        => round((float) ($resumen->valor_total ?? 0), 2),
            'total_unidades'     => (int) ($resumen->total_unidades ?? 0),
            'total_lotes_activos'=> (int) ($resumen->total_lotes_activos ?? 0),
            'total_productos'    => (int) ($resumen->total_productos ?? 0),
            'detalles'           => $detalles,
        ];
    }
}
