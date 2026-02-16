<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompraRequest;
use App\Http\Requests\UpdateCompraRequest;
use App\Http\Requests\AnularCompraRequest;
use App\Services\CompraService;
use App\Models\Compra;
use App\Models\Proveedor;
use App\Models\Producto;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;


class CompraController extends Controller
{
    protected $compraService;

    public function __construct(CompraService $compraService)
    {
        $this->compraService = $compraService;
        
        // Middleware de permisos
        $this->middleware('permission:ver compras')->only(['index', 'show']);
        $this->middleware('permission:registrar compras')->only(['create', 'store']);
        $this->middleware('permission:anular compras')->only(['edit', 'update', 'anular']);
    }

    /**
     * Display a listing of the resource.
     */
   public function index(Request $request)
{
    $query = Compra::with(['proveedor', 'usuario', 'detalles.producto']);

    // Filtros
    if ($request->filled('estado')) {
        $query->where('estado', $request->estado);
    }
    if ($request->filled('fecha_inicio')) {
        $query->whereDate('fecha', '>=', $request->fecha_inicio);
    }
    if ($request->filled('fecha_fin')) {
        $query->whereDate('fecha', '<=', $request->fecha_fin);
    }
    if ($request->filled('proveedor_id')) {
        $query->where('proveedor_id', $request->proveedor_id);
    }

    // ✅ Stats correctos (no solo la página actual)
    $statsQuery = clone $query;
    $stats = [
        'total' => (clone $statsQuery)->count(),
        'recibidas' => (clone $statsQuery)->where('estado', 'recibida')->count(),
        'anuladas' => (clone $statsQuery)->where('estado', 'anulada')->count(),
        'monto_recibidas' => (clone $statsQuery)->where('estado', 'recibida')->sum('total'),
    ];


    $orden = $request->input('orden', 'id_asc');
    switch ($orden) {
        case 'id_desc':
            $query->orderBy('id', 'desc');
            break;
        case 'fecha_asc':
            $query->orderBy('fecha', 'asc')->orderBy('id', 'asc');
            break;
        case 'fecha_desc':
            $query->orderBy('fecha', 'desc')->orderBy('id', 'desc');
            break;
        case 'id_asc':
        default:
            $query->orderBy('id', 'asc');
            break;
    }
$compras = $query->paginate(15);
$compras->withQueryString();
    $proveedores = Proveedor::activos()->orderBy('nombre')->get();

    return view('compras.index', compact('compras', 'proveedores', 'stats'));
}
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $proveedores = Proveedor::activos()->orderBy('nombre')->get();
        $productos = Producto::activos()->orderBy('nombre')->get();
        
        return view('compras.create', compact('proveedores', 'productos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCompraRequest $request)
    {
        try {
            $compra = $this->compraService->registrarCompra($request->validated());
            
            return redirect()
                ->route('compras.show', $compra)
                ->with('success', "Compra #{$compra->id} registrada correctamente. Total: S/ " . number_format($compra->total, 2));
                
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Error al registrar la compra: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Compra $compra)
    {
        $compra->load([
            'proveedor',
            'usuario',
            'anuladoPor',
            'detalles.producto',
            'detalles.lote',
            'lotes.producto',
            'compraOriginal',
            'reemplazadaPor'
        ]);

        // Obtener historial de modificaciones si existe
        $historial = null;
        if ($compra->esModificacion() || $compra->fueModificada()) {
            $historial = $this->compraService->historialModificacionesCompra($compra->id);
        }

        return view('compras.show', compact('compra', 'historial'));
    }

    /**
     * Show the form for editing the specified resource.
     */
/**
 * Show the form for editing the specified resource.
 */

public function edit(Compra $compra)
{
    // Verificar que puede modificarse
    if (!$compra->puedeModificarse()) {
        return redirect()
            ->route('compras.show', $compra)
            ->with('error', 'Esta compra no puede modificarse.');
    }

    $compra->loadMissing([
        'proveedor:id,nombre',
        'usuario:id,name',
        'detalles' => function ($q) {
            $q->orderBy('id', 'asc');
        },
        'detalles.producto:id,nombre,descripcion,codigo_barra,imagen,precio_compra,activo',
        'detalles.presentacion:id,producto_id,nombre,descripcion,unidades_por_presentacion,precio_sugerido,activo,orden',
        'detalles.lote:id,producto_id,numero_lote,fecha_vencimiento',
        'lotes:id,compra_id,producto_id,numero_lote,fecha_vencimiento,stock_inicial,stock_actual,activo',
    ]);

    // Proveedores para select (incluye el actual aunque esté inactivo)
    $proveedores = Proveedor::activos()
        ->select('id', 'nombre')
        ->orderBy('nombre')
        ->get();

    if ($compra->proveedor_id && !$proveedores->contains('id', $compra->proveedor_id)) {
        $provActual = Proveedor::select('id', 'nombre')->find($compra->proveedor_id);
        if ($provActual) {
            $proveedores->push($provActual);
            $proveedores = $proveedores->sortBy('nombre')->values();
        }
    }

    // Productos para modal/grid + barcode (asegura campos usados por JS y UI)
    $productos = Producto::activos()
        ->select('id', 'nombre', 'descripcion', 'codigo_barra', 'imagen', 'precio_compra')
        ->orderBy('nombre')
        ->get();

    return view('compras.edit', compact('compra', 'proveedores', 'productos'));
}

public function update(UpdateCompraRequest $request, Compra $compra)
{
    try {
        $data = $request->validated();
        $motivo = trim((string) ($data['motivo_cambios'] ?? $data['motivo_anulacion'] ?? ''));

        $compra->loadMissing([
            'detalles.lote:id,producto_id,numero_lote,fecha_vencimiento',
            'detalles.presentacion:id,unidades_por_presentacion',
            'lotes:id,stock_actual',
        ]);

        $productosRequest = $data['productos'] ?? [];
        $productosCambiaron = $this->productosCambiaron($compra, $productosRequest);

        // ✅ Regla de negocio:
        // - Si cambian productos/lotes/cantidades/precios => requiere revertir inventario (puede fallar si ya hubo ventas).
        // - Si NO cambian => versiona solo cabecera (sin tocar inventario) y siempre guarda trazabilidad.
        if ($productosCambiaron) {
            $nuevaCompra = $this->compraService->modificarCompra(
                $compra->id,
                $data,
                $motivo ?: 'Modificación de compra'
            );
        } else {
            $nuevaCompra = $this->compraService->versionarCompraSoloDatos(
                $compra->id,
                $data,
                $motivo ?: 'Actualización de datos'
            );
        }

        return redirect()
            ->route('compras.show', $nuevaCompra)
            ->with('success', 'Compra actualizada correctamente. Se guardó una versión anterior para trazabilidad.');

    } catch (\Exception $e) {
        $msg = (string) $e->getMessage();

        // Mensaje más claro cuando no se puede revertir inventario porque ya hubo salidas desde el/los lote(s).
        if (str_contains($msg, 'no tiene stock suficiente') || str_contains($msg, 'Stock actual')) {
            return back()
                ->withInput()
                ->with('error', 'No se puede modificar el detalle de esta compra porque ya hubo salidas (ventas/ajustes) desde uno o más lotes. Puedes editar únicamente datos de cabecera (proveedor/fecha/observaciones/descuento) sin cambiar productos, lotes, cantidades ni precios.');
        }

        return back()
            ->withInput()
            ->with('error', 'Error al modificar la compra: ' . $msg);
    }
}


    /**
     * Anular una compra.
     */
    public function anular(AnularCompraRequest $request, Compra $compra)
    {
        try {
            $compraAnulada = $this->compraService->anularCompra(
                compraId: $compra->id,
                motivo: $request->motivo
            );
            
            return redirect()
                ->route('compras.index')
                ->with('success', "Compra #{$compra->id} anulada correctamente.");
                
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Error al anular la compra: ' . $e->getMessage());
        }
    }

    /**
     * Verificar si número de lote es único (AJAX).
     */
    public function verificarNumeroLote(Request $request)
    {
        $esUnico = $this->compraService->esNumeroLoteUnico(
            productoId: $request->producto_id,
            numeroLote: $request->numero_lote
        );

        return response()->json(['es_unico' => $esUnico]);
    }
    public function imprimir(Compra $compra)
{
    $compra->load([
        'proveedor',
        'usuario',
        'detalles.producto.categoria',
        'detalles.lote'
    ]);

    return view('compras.imprimir', compact('compra'));
}

/**
 * Generar PDF de la compra
 */
public function generarPDF(Compra $compra)
{
    $compra->load([
        'proveedor',
        'usuario',
        'detalles.producto.categoria',
        'detalles.lote'
    ]);

    // Configuración de empresa (puedes moverlo a config o BD)
    $empresa = [
        'nombre' => config('app.name', 'FarmaBien'),
        'ruc' => '20123456789',
        'direccion' => 'Av. Principal 123, Lima',
        'telefono' => '(01) 234-5678',
        'email' => 'contacto@farmabien.com'
    ];

    $pdf = Pdf::loadView('compras.pdf', compact('compra', 'empresa'));
    
    // Configurar orientación y tamaño
    $pdf->setPaper('A4', 'portrait');
    
    $nombreArchivo = sprintf(
        'Compra_%s_%s.pdf',
        str_pad($compra->id, 6, '0', STR_PAD_LEFT),
        $compra->fecha->format('Ymd')
    );

    return $pdf->download($nombreArchivo);
}

/**
 * Generar ticket térmico (58mm o 80mm)
 */
public function imprimirTicket(Compra $compra)
{
    $compra->load([
        'proveedor',
        'usuario',
        'detalles.producto.categoria',
        'detalles.lote'
    ]);

    $empresa = [
        'nombre' => config('app.name', 'FarmaBien'),
        'ruc' => '20123456789',
        'direccion' => 'Av. Principal 123, Lima',
        'telefono' => '(01) 234-5678'
    ];

    return view('compras.ticket', compact('compra', 'empresa'));
}

/**
 * Buscar compra por ID (para modal de búsqueda rápida)
 */
public function buscarPorId($id)
{
    try {
        $compra = Compra::with([
            'proveedor',
            'usuario',
            'detalles.producto'
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'compra' => [
                'id' => $compra->id,
                'fecha' => $compra->fecha->format('d/m/Y'),
                'proveedor' => $compra->proveedor->nombre,
                'total' => number_format($compra->total, 2),
                'estado' => $compra->estado,
                'productos_count' => $compra->detalles->count(),
                'url_show' => route('compras.show', $compra),
                'url_pdf' => route('compras.pdf', $compra),
                'url_imprimir' => route('compras.imprimir', $compra),
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'No se encontró la compra con ID: ' . $id
        ], 404);
    }
}


// ============================================================
// Helpers: detectar si cambiaron productos/lotes/cantidades/precios
// ============================================================
private function normalizarNumeroLote(?string $numero): string
{
    $numero = trim((string) $numero);
    $numero = preg_replace('/\s+/', ' ', $numero);
    return mb_strtoupper($numero);
}

private function fingerprintProductosCompra(Compra $compra): array
{
    $items = [];

    foreach ($compra->detalles as $d) {
        $vence = optional(optional($d->lote)->fecha_vencimiento);
        $items[] = [
            'producto_id' => (int) $d->producto_id,
            'presentacion_id' => (int) ($d->presentacion_id ?? 0),
            'unidades' => (int) ($d->unidades_por_presentacion ?? 1),
            'cantidad' => (int) ($d->cantidad_presentaciones ?? 1),
            'numero_lote' => $this->normalizarNumeroLote(optional($d->lote)->numero_lote),
            'vence' => $vence ? $vence->toDateString() : '',
            'precio_unitario' => round((float) ($d->precio_unitario ?? 0), 6),
            'descuento' => round((float) ($d->descuento_porcentaje ?? 0), 6),
        ];
    }

    usort($items, function ($a, $b) {
        return strcmp(
            implode('|', [$a['producto_id'], $a['numero_lote'], $a['presentacion_id']]),
            implode('|', [$b['producto_id'], $b['numero_lote'], $b['presentacion_id']])
        );
    });

    return $items;
}

private function fingerprintProductosRequest(array $productos): array
{
    $items = [];

    foreach ($productos as $p) {
        if (!is_array($p)) {
            continue;
        }

        $items[] = [
            'producto_id' => (int) ($p['producto_id'] ?? 0),
            'presentacion_id' => (int) ($p['presentacion_id'] ?? 0),
            'unidades' => (int) ($p['unidades_por_presentacion'] ?? 1),
            'cantidad' => (int) ($p['cantidad_presentaciones'] ?? 1),
            'numero_lote' => $this->normalizarNumeroLote($p['numero_lote'] ?? ''),
            'vence' => (string) ($p['fecha_vencimiento'] ?? ''),
            'precio_unitario' => round((float) ($p['precio_unitario'] ?? 0), 6),
            'descuento' => round((float) ($p['descuento'] ?? 0), 6),
        ];
    }

    usort($items, function ($a, $b) {
        return strcmp(
            implode('|', [$a['producto_id'], $a['numero_lote'], $a['presentacion_id']]),
            implode('|', [$b['producto_id'], $b['numero_lote'], $b['presentacion_id']])
        );
    });

    return $items;
}

private function productosCambiaron(Compra $compra, array $productosRequest): bool
{
    $a = $this->fingerprintProductosCompra($compra);
    $b = $this->fingerprintProductosRequest($productosRequest);

    return json_encode($a) !== json_encode($b);
}


}