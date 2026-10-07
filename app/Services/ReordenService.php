<?php

namespace App\Services;

use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\HistorialPrecio;
use App\Models\DetalleOrdenCompra;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReordenService
{
    protected PrecioProveedorService $precioService;

    public function __construct(PrecioProveedorService $precioService)
    {
        $this->precioService = $precioService;
    }

    /**
     * Calcular las sugerencias de reorden inteligentes con optimización de consultas masivas.
     *
     * @param array $filtros
     * @return array
     */
    public function calcularSugerencias(array $filtros = []): array
    {
        $proveedorFiltro = !empty($filtros['proveedor_id']) ? (int) $filtros['proveedor_id'] : null;
        $categoriaFiltro = !empty($filtros['categoria_id']) ? (int) $filtros['categoria_id'] : null;
        $laboratorioFiltro = !empty($filtros['laboratorio_id']) ? (int) $filtros['laboratorio_id'] : null;
        $soloAgotados = !empty($filtros['solo_agotados']);
        $buscar = trim($filtros['buscar'] ?? '');

        // 1. Cargar productos con stock disponible y stock en tránsito
        $productosQuery = Producto::with([
            'categoria:id,nombre',
            'laboratorio:id,nombre',
            'presentacionesActivas',
            'lotesActivos.proveedor:id,nombre'
        ])
        ->activos()
        ->withSum(['lotes as stock_disponible' => function ($q) {
            $q->where('activo', true)
              ->where('fecha_vencimiento', '>', now()->toDateString());
        }], 'stock_actual');

        if ($categoriaFiltro) {
            $productosQuery->where('categoria_id', $categoriaFiltro);
        }
        if ($laboratorioFiltro) {
            $productosQuery->where('laboratorio_id', $laboratorioFiltro);
        }
        if (!empty($buscar)) {
            $productosQuery->where(function ($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('codigo_barra', 'like', "%{$buscar}%")
                  ->orWhere('principio_activo', 'like', "%{$buscar}%");
            });
        }

        $productos = $productosQuery->get();

        if ($productos->isEmpty()) {
            return $this->generarRespuestaVacia();
        }

        $productoIds = $productos->pluck('id')->toArray();

        // 2. Cargar masivamente el stock en tránsito (Órdenes de Compra abiertas)
        $transitoPorProducto = DetalleOrdenCompra::whereIn('producto_id', $productoIds)
            ->whereHas('ordenCompra', function ($q) {
                $q->whereIn('estado', ['enviada', 'recibida_parcial']);
            })
            ->select('producto_id', DB::raw('SUM(cantidad_solicitada - cantidad_recibida) as total_en_transito'))
            ->groupBy('producto_id')
            ->pluck('total_en_transito', 'producto_id')
            ->map(fn($val) => max(0, (int) $val));

        // 3. Cargar masivamente el mejor precio de proveedor en 1 sola consulta
        $preciosMejores = HistorialPrecio::with('proveedor:id,nombre,ruc,telefono')
            ->whereIn('producto_id', $productoIds)
            ->whereHas('proveedor', fn($q) => $q->where('activo', true))
            ->orderBy('precio_unitario_base', 'asc')
            ->orderBy('fecha', 'desc')
            ->get()
            ->groupBy('producto_id')
            ->map(fn($historiales) => $historiales->first());

        // 4. Cargar masivamente el último registro para costo de referencia en 1 sola consulta
        $ultimosHistoriales = HistorialPrecio::whereIn('producto_id', $productoIds)
            ->whereHas('proveedor', fn($q) => $q->where('activo', true))
            ->orderBy('fecha', 'desc')
            ->orderBy('id', 'desc')
            ->get()
            ->groupBy('producto_id')
            ->map(fn($historiales) => $historiales->first());

        $sugerencias = collect();
        $totalInversionEstimada = 0.00;
        $totalAhorroEstimado = 0.00;
        $totalCriticos = 0;
        $totalEnTransito = 0;
        $proveedoresInvolucrados = collect();

        foreach ($productos as $p) {
            $stockDisp = (int) ($p->stock_disponible ?? 0);
            $stockMin = (int) ($p->stock_minimo > 0 ? $p->stock_minimo : 10);
            $stockTransito = (int) ($transitoPorProducto[$p->id] ?? 0);
            $stockProyectado = $stockDisp + $stockTransito;

            if ($soloAgotados && $stockDisp > 0) {
                continue;
            }

            // Condición de reorden: stock disponible <= stock mínimo
            if ($stockDisp <= $stockMin) {
                $deficit = max(0, ($stockMin * 2) - $stockProyectado);
                $cantidadSugerida = max(10, $deficit);

                // Resolver proveedor recomendado
                $mejorHistorial = $preciosMejores[$p->id] ?? null;
                $proveedorRecomendado = null;
                $mejorPrecioBase = (float) ($p->precio_compra > 0 ? $p->precio_compra : 0.01);
                $ultimoPrecioBase = (float) ($p->precio_compra > 0 ? $p->precio_compra : 0.01);

                if ($mejorHistorial && $mejorHistorial->proveedor) {
                    $proveedorRecomendado = $mejorHistorial->proveedor;
                    $mejorPrecioBase = (float) $mejorHistorial->precio_unitario_base;
                    $ultimoPrecioBase = (float) $mejorHistorial->precio_unitario_base;
                } else {
                    $ultimoLote = $p->lotesActivos->first();
                    $proveedorRecomendado = $ultimoLote->proveedor ?? null;
                }

                // Filtro opcional por proveedor
                if ($proveedorFiltro && (!$proveedorRecomendado || $proveedorRecomendado->id != $proveedorFiltro)) {
                    continue;
                }

                $costoEstimado = round($cantidadSugerida * $mejorPrecioBase, 2);

                // Obtener costo de referencia sin consultar en ciclo
                $ultimoHist = $ultimosHistoriales[$p->id] ?? null;
                if ($ultimoHist && (float) $ultimoHist->precio_unitario_base > 0) {
                    $costoReferencia = (float) $ultimoHist->precio_unitario_base;
                } elseif ((float) $p->precio_compra > 0) {
                    $costoReferencia = (float) $p->precio_compra;
                } else {
                    $costoReferencia = round((float) $p->precio_venta * 0.70, 4);
                }
                \App\Facades\RequestCache::put("precio_proveedor:costo_referencia:{$p->id}", $costoReferencia);

                $ahorroPorUnidad = max(0, round($costoReferencia - $mejorPrecioBase, 4));
                $ahorroTotal = round($cantidadSugerida * $ahorroPorUnidad, 2);

                $totalInversionEstimada += $costoEstimado;
                $totalAhorroEstimado += $ahorroTotal;

                if ($proveedorRecomendado) {
                    $proveedoresInvolucrados->put($proveedorRecomendado->id, $proveedorRecomendado->nombre);
                }

                // Clasificación de urgencia
                $urgencia = 'media';
                if ($stockDisp === 0 && $stockTransito === 0) {
                    $urgencia = 'critica';
                    $totalCriticos++;
                } elseif ($stockTransito > 0) {
                    $urgencia = 'transito';
                    $totalEnTransito++;
                } elseif ($stockDisp <= ($stockMin * 0.5)) {
                    $urgencia = 'alta';
                }

                $sugerencias->push([
                    'producto'              => $p,
                    'stock_actual'          => $stockDisp,
                    'stock_minimo'          => $stockMin,
                    'stock_en_transito'     => $stockTransito,
                    'stock_proyectado'      => $stockProyectado,
                    'deficit'               => $deficit,
                    'cantidad_sugerida'     => $cantidadSugerida,
                    'proveedor_recomendado' => $proveedorRecomendado,
                    'mejor_precio_base'     => $mejorPrecioBase,
                    'ultimo_precio_base'    => $ultimoPrecioBase,
                    'costo_estimado'        => $costoEstimado,
                    'ahorro_estimado'       => $ahorroTotal,
                    'presentaciones'        => $p->presentacionesActivas,
                    'urgencia'              => $urgencia,
                ]);
            }
        }

        // Ordenar sugerencias: Críticas primero, luego menor stock disponible y mayor déficit
        $urgenciasPeso = ['critica' => 1, 'alta' => 2, 'media' => 3, 'transito' => 4];
        $sugerenciasOrdenadas = $sugerencias->sortBy(function ($item) use ($urgenciasPeso) {
            return [
                $urgenciasPeso[$item['urgencia']] ?? 5,
                $item['stock_actual'],
                -$item['deficit']
            ];
        })->values();

        return [
            'sugerencias'             => $sugerenciasOrdenadas,
            'total_items'             => $sugerenciasOrdenadas->count(),
            'total_inversion_estimada'=> round($totalInversionEstimada, 2),
            'total_ahorro_estimado'   => round($totalAhorroEstimado, 2),
            'total_criticos'          => $totalCriticos,
            'total_en_transito'       => $totalEnTransito,
            'proveedores_involucrados'=> $proveedoresInvolucrados,
        ];
    }

    /**
     * Respuesta estructurada vacía cuando no hay coincidencias.
     *
     * @return array
     */
    protected function generarRespuestaVacia(): array
    {
        return [
            'sugerencias'             => collect(),
            'total_items'             => 0,
            'total_inversion_estimada'=> 0.00,
            'total_ahorro_estimado'   => 0.00,
            'total_criticos'          => 0,
            'total_en_transito'       => 0,
            'proveedores_involucrados'=> collect(),
        ];
    }
}
