<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVentaRequest;
use App\Http\Requests\UpdateVentaRequest;
use App\Http\Requests\AnularVentaRequest;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Venta;
use App\Services\VentaService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Carbon\Carbon;

class VentaController extends Controller
{
    public function __construct(protected VentaService $ventaService)
    {
        // Middleware de permisos
        $this->middleware('permission:ver ventas')->only(['index', 'show', 'ticket', 'imprimir', 'imprimirA4', 'generarPDF', 'buscarPorId']);
        $this->middleware('permission:realizar ventas')->only(['create', 'store']);
        $this->middleware('permission:anular ventas')->only(['edit', 'update', 'anular']);
    }

    /**
     * Listado de ventas
     */
    public function index(Request $request)
{
    $query = Venta::with(['cliente', 'usuario', 'detalles.producto']);

    /** @var \App\Models\User $user */
    $user = auth()->user();

    // 1. Lógica de Roles y Filtro por Cajero
    if ($user->hasRole('Admin')) {
        // Si el Admin selecciona un cajero específico en el filtro
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
    } else {
        // Si no es Admin (es Cajero), forzamos que solo vea sus ventas
        $query->where('user_id', $user->id);
    }

    // 2. Otros Filtros
    if ($request->filled('estado')) {
        $query->where('estado', $request->estado);
    }

    if ($request->filled('fecha_inicio')) {
        $query->whereDate('fecha', '>=', $request->fecha_inicio);
    }

    if ($request->filled('fecha_fin')) {
        $query->whereDate('fecha', '<=', $request->fecha_fin);
    }

    if ($request->filled('cliente_id')) {
        $query->where('cliente_id', $request->cliente_id);
    }

    // ==============================
    // Estadísticas (tarjetas)
    // ==============================
    $statsQuery = clone $query;

    $semanaInicio = \Carbon\Carbon::now()->startOfWeek(\Carbon\Carbon::MONDAY)->startOfDay();
    $semanaFin    = \Carbon\Carbon::now()->endOfWeek(\Carbon\Carbon::SUNDAY)->endOfDay();

    $usaFiltroFecha = $request->filled('fecha_inicio') || $request->filled('fecha_fin');
    if (!$usaFiltroFecha) {
        $statsQuery->whereBetween('fecha', [$semanaInicio, $semanaFin]);
    }

    $stats = [
        'total'       => (clone $statsQuery)->count(),
        'completadas' => (clone $statsQuery)->where('estado', 'completada')->count(),
        'anuladas'    => (clone $statsQuery)->where('estado', 'anulada')->count(),
        'ingresos'    => (clone $statsQuery)->where('estado', 'completada')->sum('total'),
        'rango'       => [
            'inicio' => $semanaInicio,
            'fin'    => $semanaFin,
            'es_semana' => !$usaFiltroFecha,
        ],
    ];

    // ==============================
    // Orden
    // ==============================
    $orden = $request->input('orden', 'fecha_desc');
    switch ($orden) {
        case 'id_asc': $query->orderBy('id', 'asc'); break;
        case 'id_desc': $query->orderBy('id', 'desc'); break;
        case 'fecha_asc': $query->orderBy('fecha', 'asc')->orderBy('id', 'asc'); break;
        case 'fecha_desc':
        default: $query->orderBy('fecha', 'desc')->orderBy('id', 'desc'); break;
    }

    // 3. Carga de datos para los Selects del buscador
    $ventas = $query->paginate(15)->withQueryString();
    $clientes = \App\Models\Cliente::activos()->orderBy('nombre')->get();
    
    // IMPORTANTE: Cargamos los usuarios para el select del Admin
    $usuarios = \App\Models\User::orderBy('name')->get();

    return view('ventas.index', compact('ventas', 'clientes', 'stats', 'usuarios'));
}
    /**
     * Formulario de creación
     */
    public function create()
    {
        $clientes = Cliente::activos()->orderBy('nombre')->get();

        // ✅ create() no tiene venta previa; estas colecciones deben existir para no romper el query.
        $loteIdsVenta = collect();
        $productoIdsVenta = collect();

        // ✅ Catálogo POS (igual enfoque que compras) + lo necesario para ventas:
        // - Productos activos
        // - Presentaciones activas
        // - Lotes disponibles FEFO (para elegir lote y asegurar trazabilidad)
        $productos = Producto::query()
            ->where(function ($q) use ($productoIdsVenta) {
                $q->where('activo', true);
                if ($productoIdsVenta->isNotEmpty()) {
                    $q->orWhereIn('id', $productoIdsVenta);
                }
            })
            ->select(['id', 'nombre', 'descripcion', 'codigo_barra', 'precio_venta', 'requiere_receta', 'imagen', 'activo'])
            ->with([
                'presentacionesActivas:id,producto_id,nombre,descripcion,unidades_por_presentacion,precio_sugerido,activo,orden',
                'lotes' => function ($q) use ($loteIdsVenta) {
                    $q->select(['id', 'producto_id', 'numero_lote', 'fecha_vencimiento', 'stock_inicial', 'activo'])
                        ->where(function ($qq) use ($loteIdsVenta) {
                            $qq->disponibles();

                            // incluir lotes ya usados en esta venta (aunque tengan stock 0 o estén inactivos)
                            if ($loteIdsVenta->isNotEmpty()) {
                                $qq->orWhereIn('id', $loteIdsVenta->all());
                            }
                        })
                        ->orderBy('fecha_vencimiento', 'asc'); // FEFO
                }
            ])
            ->orderBy('nombre')
            ->get();

        return view('ventas.create', compact('clientes', 'productos'));
    }
public function store(StoreVentaRequest $request)
    {
        try {
            $data = $request->validated();

            // Si productos vienen como JSON string (por JS), decodificar
            if (isset($data['productos']) && is_string($data['productos'])) {
                $data['productos'] = json_decode($data['productos'], true) ?? [];
            }

            // Normalizaciones esperadas por BD nueva
            // - descuento global: request usa "descuento" (%), BD usa "descuento_porcentaje"
            if (array_key_exists('descuento', $data)) {
                $data['descuento_porcentaje'] = $data['descuento'] ?? 0;
                unset($data['descuento']);
            }

            // - descuento por línea: request usa "descuento" (%), BD usa "descuento_porcentaje"
            if (!empty($data['productos']) && is_array($data['productos'])) {
                foreach ($data['productos'] as $i => $item) {
                    if (is_array($item) && array_key_exists('descuento', $item)) {
                        $data['productos'][$i]['descuento_porcentaje'] = $item['descuento'] ?? 0;
                        unset($data['productos'][$i]['descuento']);
                    }
                }
            }

            // IMPORTANTÍSIMO:
            // El Request ya normaliza "fecha" para que incluya hora (Y-m-d H:i:s).
            // La VentaService debe respetar $data['fecha'] (si viene) al crear la venta.
            $venta = $this->ventaService->procesarVenta($data);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Venta #{$venta->id} registrada correctamente",
                    'redirect' => route('ventas.show', $venta),
                ]);
            }

            return redirect()
                ->route('ventas.show', $venta)
                ->with('success', "Venta #{$venta->id} registrada correctamente. Total: " . number_format((float) $venta->total, 2));

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()
                ->withInput()
                ->with('error', 'Error al procesar la venta: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar venta
     */
    public function show(Venta $venta)
    {
        $venta->load([
            'cliente',
            'usuario',
            'anuladoPor',
            'detalles.producto',
            'detalles.lote',
            'detalles.presentacion',
            'recetas',
            'ventaOriginal',
            'reemplazadaPor',
        ]);

        // Historial de modificaciones si aplica
        $historial = null;

        $esModificacion = !is_null($venta->venta_original_id);
        $fueModificada = !is_null($venta->reemplazada_por);

        if ($esModificacion || $fueModificada) {
            $historial = $this->ventaService->historialModificacionesVenta($venta->id);
        }

        return view('ventas.show', compact('venta', 'historial'));
    }

    /**
     * Buscar venta por ID (para modal de búsqueda rápida)
     */
    public function buscarPorId($id)
    {
        try {
            $venta = Venta::with([
                'cliente',
                'usuario',
                'detalles.producto',
            ])->findOrFail($id);

            $urlImprimir = \Illuminate\Support\Facades\Route::has('ventas.imprimir')
                ? route('ventas.imprimir', $venta)
                : route('ventas.ticket', $venta);

            $urlPdf = \Illuminate\Support\Facades\Route::has('ventas.pdf')
                ? route('ventas.pdf', $venta)
                : route('ventas.ticket', $venta);

            return response()->json([
                'success' => true,
                'venta' => [
                    'id' => $venta->id,
                    'fecha' => $venta->fecha ? $venta->fecha->format('d/m/Y H:i') : '—',
                    'cliente' => $venta->cliente ? $venta->cliente->nombre : 'Público general',
                    'total' => number_format((float) $venta->total, 2),
                    'estado' => $venta->estado,
                    'productos_count' => $venta->detalles->count(),
                    'url_show' => route('ventas.show', $venta),
                    'url_pdf' => $urlPdf,
                    'url_imprimir' => $urlImprimir,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró la venta con ID: ' . $id
            ], 404);
        }
    }

    /**
     * Formulario de modificación
     */
    public function edit(Venta $venta)
    {
        if (!$venta->puedeModificarse()) {
            return redirect()
                ->route('ventas.show', $venta)
                ->with('error', 'Esta venta no puede modificarse.');
        }

        // ✅ Cargar cliente y detalles para precargar la edición
        $venta->load('cliente', 'detalles.producto', 'detalles.presentacion', 'detalles.lote', 'recetas');

        // ✅ Clientes: normalmente solo activos; PERO si la venta tiene un cliente inactivo,
        // lo incluimos para que el <select> pueda precargarlo y no caiga por defecto en "Cliente de mostrador".
        $clientesQuery = Cliente::query()->where('activo', true);
        if ($venta->cliente_id) {
            $clientesQuery->orWhere('id', $venta->cliente_id);
        }
        $clientes = $clientesQuery->orderBy('nombre')->get();

        // ✅ Incluir lotes de la venta actual aunque su stock sea 0 (para no "ocultar" el lote original en edición)
        $loteIdsVenta = $venta->detalles->pluck('lote_id')->filter()->unique()->values();
        $productoIdsVenta = $venta->detalles->pluck('producto_id')->filter()->unique()->values();

        // ✅ Catálogo POS (mismo que create): productos + presentaciones activas + lotes FEFO disponibles
        $productos = Producto::query()
            ->where(function ($q) use ($productoIdsVenta) {
                $q->where('activo', true);
                if ($productoIdsVenta->isNotEmpty()) {
                    $q->orWhereIn('id', $productoIdsVenta);
                }
            })
            ->select(['id', 'nombre', 'descripcion', 'codigo_barra', 'precio_venta', 'requiere_receta', 'imagen', 'activo'])
            ->with([
                'presentacionesActivas:id,producto_id,nombre,descripcion,unidades_por_presentacion,precio_sugerido,activo,orden',
                'lotes' => function ($q) use ($loteIdsVenta) {
                    $q->select(['id', 'producto_id', 'numero_lote', 'fecha_vencimiento', 'stock_inicial', 'activo'])
                        ->where(function ($qq) use ($loteIdsVenta) {
                            $qq->where(function ($q2) {
                                $q2->disponibles();
                            });
                            if ($loteIdsVenta->isNotEmpty()) {
                                $qq->orWhereIn('id', $loteIdsVenta);
                            }
                        })
                        ->orderBy('fecha_vencimiento', 'asc'); // FEFO
                }
            ])
            ->orderBy('nombre')
            ->get();

        return view('ventas.edit', compact('venta', 'clientes', 'productos'));
    }

    /**
     * Modificar venta (anula y recrea con trazabilidad)
     */
    public function update(UpdateVentaRequest $request, Venta $venta)
    {
        try {
            $validated = $request->validated();

            $motivo = $validated['motivo'] ?? null;
            unset($validated['motivo']);

            $data = $validated;

            // Si productos vienen como JSON string (por JS), decodificar
            if (isset($data['productos']) && is_string($data['productos'])) {
                $data['productos'] = json_decode($data['productos'], true) ?? [];
            }

            // Normalizaciones (misma lógica que store)
            if (array_key_exists('descuento', $data)) {
                $data['descuento_porcentaje'] = $data['descuento'] ?? 0;
                unset($data['descuento']);
            }

            if (!empty($data['productos']) && is_array($data['productos'])) {
                foreach ($data['productos'] as $i => $item) {
                    if (is_array($item) && array_key_exists('descuento', $item)) {
                        $data['productos'][$i]['descuento_porcentaje'] = $item['descuento'] ?? 0;
                        unset($data['productos'][$i]['descuento']);
                    }
                }
            }

            $nuevaVenta = $this->ventaService->modificarVenta(
                ventaId: $venta->id,
                data: $data,
                motivo: (string) $motivo
            );

            $mensaje = "Venta modificada. Venta original: #{$venta->id} → Nueva venta: #{$nuevaVenta->id}";

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $mensaje,
                    'venta_id' => $nuevaVenta->id,
                    'redirect' => route('ventas.show', $nuevaVenta),
                ]);
            }

            return redirect()
                ->route('ventas.show', $nuevaVenta)
                ->with('success', $mensaje);

        } catch (\Exception $e) {

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al modificar la venta: ' . $e->getMessage(),
                ], 422);
            }

            return back()
                ->withInput()
                ->with('error', 'Error al modificar la venta: ' . $e->getMessage());
        }
    }

    /**
     * Anular una venta
     */
    public function anular(AnularVentaRequest $request, Venta $venta)
    {
        try {
            $this->ventaService->anularVenta(
                ventaId: $venta->id,
                motivo: $request->motivo
            );

            return redirect()
                ->route('ventas.index')
                ->with('success', "Venta #{$venta->id} anulada correctamente.");

        } catch (\Exception $e) {
            return back()->with('error', 'Error al anular la venta: ' . $e->getMessage());
        }
    }

    /**
     * Buscar productos para venta (AJAX)
     */
    public function buscarProductos(Request $request)
    {
        if (!$request->filled('termino')) {
            return response()->json([]);
        }

        return response()->json(
            $this->ventaService->buscarProductosParaVenta($request->termino)
        );
    }

    /**
     * Lotes disponibles de un producto (AJAX) - FEFO por vencimiento
     */
    public function obtenerLotesProducto(Request $request, Producto $producto)
    {
        $lotes = $producto->lotes()
            ->disponibles()
            ->orderBy('fecha_vencimiento', 'asc')
            ->get()
            ->map(function ($lote) {
                return [
                    'id' => $lote->id,
                    'numero_lote' => $lote->numero_lote,
                    'fecha_vencimiento' => optional($lote->fecha_vencimiento)->format('d/m/Y'),
                    'stock_actual' => $lote->stock_actual,
                    'precio_compra' => $lote->precio_compra,
                    'dias_para_vencer' => $lote->fecha_vencimiento ? now()->diffInDays($lote->fecha_vencimiento, false) : null,
                ];
            });

        return response()->json($lotes);
    }

    /**
     * Imprimir ticket de venta (vista)
     */
    

    /**
     * Alias para ruta ventas.ticket (compatibilidad con web.php)
     * Muestra el ticket térmico de la venta.
     */
    public function ticket(Venta $venta)
    {
        return $this->imprimir($venta);
    }

    /**
     * Imprimir A4 (vista)
     */
    public function imprimirA4(Venta $venta)
    {
        $venta->load([
            'cliente',
            'usuario',
            'anuladoPor',
            'detalles.producto',
            'detalles.presentacion',
            'detalles.lote',
            'recetas',
        ]);

        // Configuración empresa (misma idea que Compras; ajusta a tu .env si quieres)
        $empresa = [
            'nombre' => config('app.name', 'FarmaBien'),
            'ruc' => env('EMPRESA_RUC', '—'),
            'direccion' => env('EMPRESA_DIRECCION', '—'),
            'telefono' => env('EMPRESA_TELEFONO', '—'),
            'email' => env('EMPRESA_EMAIL', '—'),
        ];

        return view('ventas.imprimir', compact('venta', 'empresa'));
    }

    /**
     * Generar PDF A4 (DomPDF)
     */
    public function generarPDF(Venta $venta)
    {
        $venta->load([
            'cliente',
            'usuario',
            'anuladoPor',
            'detalles.producto',
            'detalles.presentacion',
            'detalles.lote',
            'recetas',
        ]);

        $empresa = [
            'nombre' => config('app.name', 'FarmaBien'),
            'ruc' => env('EMPRESA_RUC', '—'),
            'direccion' => env('EMPRESA_DIRECCION', '—'),
            'telefono' => env('EMPRESA_TELEFONO', '—'),
            'email' => env('EMPRESA_EMAIL', '—'),
        ];

        $pdf = Pdf::loadView('ventas.pdf', compact('venta', 'empresa'));
        $pdf->setPaper('A4', 'portrait');

        $nombreArchivo = sprintf(
            'Venta_%s_%s.pdf',
            str_pad((string)$venta->id, 6, '0', STR_PAD_LEFT),
            ($venta->fecha?->format('Ymd') ?? now()->format('Ymd'))
        );

        return $pdf->download($nombreArchivo);
    }

public function imprimir(Venta $venta)
    {
        $venta->load('cliente', 'usuario', 'detalles.producto', 'detalles.presentacion', 'detalles.lote');

        return view('ventas.ticket', compact('venta'));
    }
}
