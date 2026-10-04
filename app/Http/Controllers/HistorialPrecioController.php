<?php

namespace App\Http\Controllers;

use App\Models\HistorialPrecio;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Categoria;
use App\Models\Laboratorio;
use App\Models\PresentacionProducto;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class HistorialPrecioController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:ver compras')->only(['comparador', 'sugerenciasReorden', 'apiHistorialProducto']);
        $this->middleware('permission:registrar compras')->only(['storeCotizacion']);
    }

    /**
     * Comparador de Precios y Cotizaciones entre Proveedores
     */
    public function comparador(Request $request)
    {
        $productoId = $request->input('producto_id');
        $proveedorId = $request->input('proveedor_id');
        $categoriaId = $request->input('categoria_id');
        $laboratorioId = $request->input('laboratorio_id');
        $buscar = trim($request->input('buscar', ''));

        // Catálogos para filtros rápidos
        $proveedores = Proveedor::activos()->orderBy('nombre')->get(['id', 'nombre', 'ruc', 'telefono']);
        $categorias = Categoria::activos()->orderBy('nombre')->get(['id', 'nombre']);
        $laboratorios = Laboratorio::activos()->orderBy('nombre')->get(['id', 'nombre']);

        // Lista de medicamentos para el selector
        $productosQuery = Producto::with(['categoria:id,nombre', 'laboratorio:id,nombre', 'presentacionesActivas'])
            ->activos();

        if ($categoriaId) {
            $productosQuery->where('categoria_id', $categoriaId);
        }
        if ($laboratorioId) {
            $productosQuery->where('laboratorio_id', $laboratorioId);
        }
        if ($buscar) {
            $productosQuery->where(function($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('codigo_barra', 'like', "%{$buscar}%")
                  ->orWhere('principio_activo', 'like', "%{$buscar}%");
            });
        }

        $productos = $productosQuery->orderBy('nombre')->get(['id', 'nombre', 'codigo_barra', 'principio_activo', 'categoria_id', 'laboratorio_id', 'precio_compra', 'stock_minimo']);

        $productoSeleccionado = null;
        $comparativa = collect();
        $historialDetallado = collect();
        $mejorPrecio = null;
        $mayorPrecio = null;
        $ahorroMaximo = 0;
        $proveedorRecomendado = null;
        $precioPromedio = 0;
        $ultimoPrecioRegistrado = null;

        if ($productoId) {
            $productoSeleccionado = Producto::with(['categoria', 'laboratorio', 'presentacionesActivas'])->find($productoId);
        } elseif ($buscar && $productos->count() === 1) {
            $productoSeleccionado = $productos->first();
            $productoSeleccionado->load(['categoria', 'laboratorio', 'presentacionesActivas']);
        }

        if ($productoSeleccionado) {
            // Comparativa estructurada por proveedor
            $comparativa = HistorialPrecio::comparativaPorProducto($productoSeleccionado->id);

            // Historial cronológico detallado
            $historialQuery = HistorialPrecio::with(['proveedor', 'presentacion', 'compra.usuario'])
                ->where('producto_id', $productoSeleccionado->id);

            if ($proveedorId) {
                $historialQuery->where('proveedor_id', $proveedorId);
            }

            $historialDetallado = $historialQuery->orderBy('fecha', 'desc')->orderBy('id', 'desc')->paginate(perPage(10))->withQueryString();

            if ($comparativa->isNotEmpty()) {
                $mejorPrecio = $comparativa->min('mejor_precio_base');
                $mayorPrecio = $comparativa->max('ultimo_precio_base');
                $proveedorRecomendado = $comparativa->sortBy('ultimo_precio_base')->first()['proveedor'] ?? null;
                $precioPromedio = round($comparativa->avg('ultimo_precio_base'), 4);
                $ultimoPrecioRegistrado = $historialDetallado->first();
                $ahorroMaximo = max(0, round($mayorPrecio - $mejorPrecio, 4));
            }
        }

        // Listado resumen de productos con historial (para vista general cuando no hay producto seleccionado)
        $productosConHistorial = collect();
        if (!$productoSeleccionado) {
            $productoIdsConHistorial = HistorialPrecio::distinct()->pluck('producto_id');
            $productosConHistorial = Producto::with(['categoria', 'laboratorio'])
                ->whereIn('id', $productoIdsConHistorial)
                ->activos()
                ->orderBy('nombre')
                ->paginate(perPage(12))
                ->withQueryString();
        }

        return view('compras.comparador-precios', compact(
            'productos',
            'proveedores',
            'categorias',
            'laboratorios',
            'productoSeleccionado',
            'comparativa',
            'historialDetallado',
            'mejorPrecio',
            'mayorPrecio',
            'ahorroMaximo',
            'proveedorRecomendado',
            'precioPromedio',
            'ultimoPrecioRegistrado',
            'productosConHistorial',
            'productoId',
            'proveedorId',
            'categoriaId',
            'laboratorioId',
            'buscar'
        ));
    }

    /**
     * Registrar una nueva Cotización recibida de un Proveedor
     */
    public function storeCotizacion(Request $request)
    {
        $validated = $request->validate([
            'producto_id'       => ['required', 'exists:productos,id'],
            'proveedor_id'      => ['required', 'exists:proveedores,id'],
            'presentacion_id'   => ['nullable', 'exists:presentaciones_producto,id'],
            'precio_compra'     => ['required', 'numeric', 'min:0.0001'],
            'fecha'             => ['required', 'date'],
            'observaciones'     => ['nullable', 'string', 'max:500'],
        ]);

        $producto = Producto::findOrFail($validated['producto_id']);
        $proveedor = Proveedor::findOrFail($validated['proveedor_id']);

        $unidadesPorPresentacion = 1;
        $tipoPresentacion = 'Unidad Base';

        if (!empty($validated['presentacion_id'])) {
            $pres = PresentacionProducto::where('producto_id', $producto->id)
                ->where('id', $validated['presentacion_id'])
                ->firstOrFail();
            $unidadesPorPresentacion = max(1, (int)$pres->unidades_por_presentacion);
            $tipoPresentacion = $pres->nombre;
        }

        $precioCompra = (float)$validated['precio_compra'];
        $precioUnitarioBase = round($precioCompra / $unidadesPorPresentacion, 4);

        $registro = HistorialPrecio::create([
            'producto_id'               => $producto->id,
            'proveedor_id'              => $proveedor->id,
            'compra_id'                 => null,
            'presentacion_id'           => $validated['presentacion_id'] ?? null,
            'tipo_presentacion'         => $tipoPresentacion,
            'unidades_por_presentacion' => $unidadesPorPresentacion,
            'precio_compra'             => $precioCompra,
            'precio_unitario_base'      => $precioUnitarioBase,
            'tipo'                      => 'cotizacion',
            'fecha'                     => $validated['fecha'],
            'observaciones'             => $validated['observaciones'] ?? 'Cotización manual de proveedor',
        ]);

        AuditLog::log('compras', 'cotizacion', "Cotización registrada para {$producto->nombre} por proveedor {$proveedor->nombre}: \${$precioCompra}", [
            'historial_id' => $registro->id,
            'producto_id' => $producto->id,
            'proveedor_id' => $proveedor->id,
            'precio' => $precioCompra,
        ]);

        return redirect()->route('compras.comparador-precios', ['producto_id' => $producto->id])
            ->with('success', "Cotización de '{$proveedor->nombre}' guardada exitosamente para '{$producto->nombre}'.");
    }

    /**
     * Alertas y Sugerencias de Reorden Automáticas con Recomendación de Proveedor y Precio
     */
    public function sugerenciasReorden(Request $request)
    {
        $proveedores = Proveedor::activos()->orderBy('nombre')->get();
        $proveedorFiltro = $request->input('proveedor_id');
        $categoriaFiltro = $request->input('categoria_id');
        $soloAgotados = $request->boolean('solo_agotados');
        $buscar = trim($request->input('buscar', ''));

        $categorias = Categoria::activos()->orderBy('nombre')->get();

        // 1. Obtener todos los productos con stock disponible calculado
        $productosQuery = Producto::with([
            'categoria',
            'laboratorio',
            'presentacionesActivas',
            'lotesActivos.proveedor'
        ])
        ->activos()
        ->withSum(['lotes as stock_disponible' => function ($q) {
            $q->where('activo', true)
              ->where('fecha_vencimiento', '>', now()->toDateString());
        }], 'stock_actual');

        if ($categoriaFiltro) {
            $productosQuery->where('categoria_id', $categoriaFiltro);
        }

        if ($buscar) {
            $productosQuery->where(function($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('codigo_barra', 'like', "%{$buscar}%")
                  ->orWhere('principio_activo', 'like', "%{$buscar}%");
            });
        }

        $productos = $productosQuery->get();

        // 2. Filtrar aquellos donde stock_disponible <= stock_minimo
        $sugerencias = collect();
        $totalInversionEstimada = 0;
        $totalAhorroEstimado = 0;
        $proveedoresInvolucrados = collect();

        foreach ($productos as $p) {
            $stockDisp = (int)($p->stock_disponible ?? 0);
            $stockMin = (int)($p->stock_minimo ?? 10);

            if ($soloAgotados && $stockDisp > 0) {
                continue;
            }

            if ($stockDisp <= $stockMin) {
                // Cantidad sugerida: reponer hasta alcanzar el doble del stock mínimo (o mínimo 10)
                $deficit = max(0, $stockMin - $stockDisp);
                $cantidadSugerida = max(10, ($stockMin * 2) - $stockDisp);

                // Buscar el mejor proveedor e historial de precios
                $comparativa = HistorialPrecio::comparativaPorProducto($p->id);

                $proveedorRecomendado = null;
                $mejorPrecioBase = (float)$p->precio_compra;
                $ultimoPrecioBase = (float)$p->precio_compra;
                $ahorroPorUnidad = 0;

                if ($comparativa->isNotEmpty()) {
                    // Tomar el proveedor con el menor precio registrado
                    $mejorOpcion = $comparativa->sortBy('mejor_precio_base')->first();
                    $proveedorRecomendado = $mejorOpcion['proveedor'] ?? null;
                    $mejorPrecioBase = (float)$mejorOpcion['mejor_precio_base'];
                    $ultimoPrecioBase = (float)$mejorOpcion['ultimo_precio_base'];

                    // Ahorro vs el precio más alto de otros proveedores
                    $precioMax = $comparativa->max('ultimo_precio_base');
                    if ($precioMax > $mejorPrecioBase) {
                        $ahorroPorUnidad = $precioMax - $mejorPrecioBase;
                    }
                } else {
                    // Fallback: Proveedor del último lote o primer proveedor activo
                    $ultimoLote = $p->lotesActivos->first();
                    $proveedorRecomendado = $ultimoLote->proveedor ?? $proveedores->first();
                }

                // Si hay filtro de proveedor y no coincide, omitir
                if ($proveedorFiltro && (!$proveedorRecomendado || $proveedorRecomendado->id != $proveedorFiltro)) {
                    continue;
                }

                $costoEstimado = round($cantidadSugerida * $mejorPrecioBase, 2);
                $ahorroTotal = round($cantidadSugerida * $ahorroPorUnidad, 2);

                $totalInversionEstimada += $costoEstimado;
                $totalAhorroEstimado += $ahorroTotal;

                if ($proveedorRecomendado) {
                    $proveedoresInvolucrados->put($proveedorRecomendado->id, $proveedorRecomendado->nombre);
                }

                $sugerencias->push([
                    'producto'              => $p,
                    'stock_actual'          => $stockDisp,
                    'stock_minimo'          => $stockMin,
                    'deficit'               => $deficit,
                    'cantidad_sugerida'     => $cantidadSugerida,
                    'proveedor_recomendado' => $proveedorRecomendado,
                    'mejor_precio_base'     => $mejorPrecioBase,
                    'ultimo_precio_base'    => $ultimoPrecioBase,
                    'costo_estimado'        => $costoEstimado,
                    'ahorro_estimado'       => $ahorroTotal,
                    'presentaciones'        => $p->presentacionesActivas,
                    'urgencia'              => $stockDisp == 0 ? 'critica' : ($stockDisp < ($stockMin * 0.5) ? 'alta' : 'media'),
                ]);
            }
        }

        // Ordenar por urgencia (agotados primero, luego mayor déficit)
        $sugerencias = $sugerencias->sortBy([
            ['stock_actual', 'asc'],
            ['deficit', 'desc']
        ])->values();

        return view('compras.sugerencias-reorden', compact(
            'sugerencias',
            'proveedores',
            'categorias',
            'totalInversionEstimada',
            'totalAhorroEstimado',
            'proveedoresInvolucrados',
            'proveedorFiltro',
            'categoriaFiltro',
            'soloAgotados',
            'buscar'
        ));
    }

    /**
     * API JSON: Consultar historial y comparativa de precios de un producto en tiempo real
     */
    public function apiHistorialProducto(Producto $producto)
    {
        $comparativa = HistorialPrecio::comparativaPorProducto($producto->id);
        $ultimoGlobal = HistorialPrecio::with('proveedor')
            ->where('producto_id', $producto->id)
            ->orderBy('fecha', 'desc')
            ->first();

        $mejorGlobal = HistorialPrecio::with('proveedor')
            ->where('producto_id', $producto->id)
            ->orderBy('precio_unitario_base', 'asc')
            ->first();

        return response()->json([
            'producto_id'      => $producto->id,
            'nombre'           => $producto->nombre,
            'precio_base_ref'  => (float)$producto->precio_compra,
            'ultimo_registro'  => $ultimoGlobal ? [
                'proveedor_id'         => $ultimoGlobal->proveedor_id,
                'proveedor_nombre'     => $ultimoGlobal->proveedor->nombre ?? 'N/A',
                'precio_compra'        => (float)$ultimoGlobal->precio_compra,
                'precio_unitario_base' => (float)$ultimoGlobal->precio_unitario_base,
                'tipo_presentacion'    => $ultimoGlobal->tipo_presentacion,
                'fecha'                => $ultimoGlobal->fecha->format('d/m/Y'),
                'tipo'                 => $ultimoGlobal->tipo,
            ] : null,
            'mejor_registro'   => $mejorGlobal ? [
                'proveedor_id'         => $mejorGlobal->proveedor_id,
                'proveedor_nombre'     => $mejorGlobal->proveedor->nombre ?? 'N/A',
                'precio_unitario_base' => (float)$mejorGlobal->precio_unitario_base,
                'fecha'                => $mejorGlobal->fecha->format('d/m/Y'),
            ] : null,
            'comparativa'      => $comparativa->map(function($item) {
                return [
                    'proveedor_id'        => $item['proveedor']->id ?? null,
                    'proveedor_nombre'    => $item['proveedor']->nombre ?? 'N/A',
                    'ultimo_precio_base'  => $item['ultimo_precio_base'],
                    'mejor_precio_base'   => $item['mejor_precio_base'],
                    'total_operaciones'   => $item['total_operaciones'],
                    'fecha_ultimo'        => $item['ultimo_registro']->fecha->format('d/m/Y'),
                ];
            }),
        ]);
    }
}
