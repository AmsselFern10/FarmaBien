<?php

namespace App\Http\Controllers;

use App\Models\HistorialPrecio;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Categoria;
use App\Models\Laboratorio;
use App\Services\PrecioProveedorService;
use App\Services\ReordenService;
use App\Http\Requests\StoreCotizacionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

class HistorialPrecioController extends Controller
{
    protected PrecioProveedorService $precioService;
    protected ReordenService $reordenService;

    public function __construct(PrecioProveedorService $precioService, ReordenService $reordenService)
    {
        $this->precioService = $precioService;
        $this->reordenService = $reordenService;
        $this->middleware('permission:ver compras')->only(['comparador', 'sugerenciasReorden', 'apiHistorialProducto']);
        $this->middleware('permission:registrar compras')->only(['storeCotizacion']);
    }

    /**
     * Comparador de Precios y Cotizaciones entre Proveedores y Marcas Equivalentes
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

        $productos = $productosQuery->orderBy('nombre')->get(['id', 'nombre', 'codigo_barra', 'principio_activo', 'concentracion', 'categoria_id', 'laboratorio_id', 'precio_compra', 'stock_minimo']);

        $productoSeleccionado = null;
        $comparativaData = [
            'comparativa'          => collect(),
            'mejor_precio'         => null,
            'mayor_precio'         => null,
            'ahorro_maximo'        => 0.00,
            'precio_promedio'      => null,
            'proveedor_recomendado'=> null,
            'total_distribuidores' => 0,
        ];
        $historialDetallado = collect();
        $ultimoPrecioRegistrado = null;
        $equivalentes = collect();

        if ($productoId) {
            $productoSeleccionado = Producto::with(['categoria', 'laboratorio', 'presentacionesActivas'])->find($productoId);
        } elseif ($buscar && $productos->count() === 1) {
            $productoSeleccionado = $productos->first();
            $productoSeleccionado->load(['categoria', 'laboratorio', 'presentacionesActivas']);
        }

        if ($productoSeleccionado) {
            // 1. Comparativa estructurada y métricas resueltas por el servicio
            $comparativaData = $this->precioService->getComparativaProducto($productoSeleccionado->id);

            // 2. Historial cronológico detallado
            $historialQuery = HistorialPrecio::with(['proveedor', 'presentacion', 'compra.usuario'])
                ->where('producto_id', $productoSeleccionado->id);

            if ($proveedorId) {
                $historialQuery->where('proveedor_id', $proveedorId);
            }

            $historialDetallado = $historialQuery->orderBy('fecha', 'desc')->orderBy('id', 'desc')->paginate(perPage(10))->withQueryString();
            $ultimoPrecioRegistrado = $historialDetallado->first();

            // 3. Productos bioequivalentes de otros laboratorios
            $equivalentes = $this->precioService->getEquivalentesFarmaceuticos($productoSeleccionado);
        }

        // Listado resumen de productos con historial cuando no hay producto seleccionado
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

        $comparativa = $comparativaData['comparativa'];
        $mejorPrecio = $comparativaData['mejor_precio'];
        $mayorPrecio = $comparativaData['mayor_precio'];
        $ahorroMaximo = $comparativaData['ahorro_maximo'];
        $proveedorRecomendado = $comparativaData['proveedor_recomendado'];
        $precioPromedio = $comparativaData['precio_promedio'];

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
            'equivalentes',
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
    public function storeCotizacion(StoreCotizacionRequest $request)
    {
        try {
            $registro = $this->precioService->registrarCotizacion($request->validated(), Auth::id() ?? 1);

            return redirect()->route('compras.comparador-precios', ['producto_id' => $registro->producto_id])
                ->with('success', "Cotización de '{$registro->proveedor->nombre}' guardada exitosamente para '{$registro->producto->nombre}'.");
        } catch (Exception $e) {
            Log::error("Error al registrar cotización: " . $e->getMessage(), [
                'user_id' => Auth::id(),
                'payload' => $request->except(['_token']),
            ]);

            return back()->withInput()->with('error', 'Error al guardar la cotización: ' . $e->getMessage());
        }
    }

    /**
     * Alertas y Sugerencias de Reorden Automáticas Inteligentes
     */
    public function sugerenciasReorden(Request $request)
    {
        $filtros = [
            'proveedor_id'   => $request->input('proveedor_id'),
            'categoria_id'   => $request->input('categoria_id'),
            'laboratorio_id' => $request->input('laboratorio_id'),
            'solo_agotados'  => $request->boolean('solo_agotados'),
            'buscar'         => trim($request->input('buscar', '')),
        ];

        $proveedores = Proveedor::activos()->orderBy('nombre')->get(['id', 'nombre', 'ruc', 'telefono']);
        $categorias = Categoria::activos()->orderBy('nombre')->get(['id', 'nombre']);
        $laboratorios = Laboratorio::activos()->orderBy('nombre')->get(['id', 'nombre']);

        $resultado = $this->reordenService->calcularSugerencias($filtros);

        $sugerencias = $resultado['sugerencias'];
        $totalInversionEstimada = $resultado['total_inversion_estimada'];
        $totalAhorroEstimado = $resultado['total_ahorro_estimado'];
        $totalCriticos = $resultado['total_criticos'];
        $totalEnTransito = $resultado['total_en_transito'];
        $proveedoresInvolucrados = $resultado['proveedores_involucrados'];

        $proveedorFiltro = $filtros['proveedor_id'];
        $categoriaFiltro = $filtros['categoria_id'];
        $laboratorioFiltro = $filtros['laboratorio_id'];
        $soloAgotados = $filtros['solo_agotados'];
        $buscar = $filtros['buscar'];

        return view('compras.sugerencias-reorden', compact(
            'sugerencias',
            'proveedores',
            'categorias',
            'laboratorios',
            'totalInversionEstimada',
            'totalAhorroEstimado',
            'totalCriticos',
            'totalEnTransito',
            'proveedoresInvolucrados',
            'proveedorFiltro',
            'categoriaFiltro',
            'laboratorioFiltro',
            'soloAgotados',
            'buscar'
        ));
    }

    /**
     * API JSON: Consultar historial y comparativa de precios de un producto en tiempo real
     */
    public function apiHistorialProducto(Producto $producto)
    {
        $compData = $this->precioService->getComparativaProducto($producto->id);

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
            'precio_base_ref'  => $this->precioService->getCostoReferencia($producto),
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
            'comparativa'      => $compData['comparativa']->map(function($item) {
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
