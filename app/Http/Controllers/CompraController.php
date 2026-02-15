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

use Illuminate\Support\Facades\View;

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
    ]);

    // Proveedores para select
    $proveedores = Proveedor::activos()
        ->select('id', 'nombre')
        ->orderBy('nombre')
        ->get();

    // Productos para modal/grid + barcode (asegura campos usados por JS y UI)
    $productos = Producto::activos()
        ->select('id', 'nombre', 'descripcion', 'codigo_barra', 'imagen', 'precio_compra')
        ->orderBy('nombre')
        ->get();

    return view('compras.edit', compact('compra', 'proveedores', 'productos'));
}
    /**

     */
    public function update(UpdateCompraRequest $request, Compra $compra)
    {
        try {
            // Tomamos solo datos validados (evita _token/_method y campos extra)
            $data = $request->validated();

            // Motivo viene del campo correcto del formulario
            $motivo = trim((string) ($data['motivo_anulacion'] ?? ''));

            // El service no espera el motivo dentro del array $data
            unset($data['motivo_anulacion']);

            $nuevaCompra = $this->compraService->modificarCompra(
                compraId: $compra->id,
                data: $data,
                motivo: $motivo
            );

            return redirect()
                ->route('compras.show', $nuevaCompra)
                ->with('success', "Compra modificada correctamente. Compra original: #{$compra->id} → Nueva compra: #{$nuevaCompra->id}");

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Error al modificar la compra: ' . $e->getMessage());
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

    $pdf = PDF::loadView('compras.pdf', compact('compra', 'empresa'));
    
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
}