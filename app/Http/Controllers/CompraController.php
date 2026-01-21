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
        $query = Compra::with(['proveedor', 'usuario', 'detalles.producto'])
            ->orderBy('fecha', 'desc');

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

        $compras = $query->paginate(15);
        $proveedores = Proveedor::activos()->orderBy('nombre')->get();

        return view('compras.index', compact('compras', 'proveedores'));
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
    public function edit(Compra $compra)
    {
        // Verificar que puede modificarse
        if (!$compra->puedeModificarse()) {
            return redirect()
                ->route('compras.show', $compra)
                ->with('error', 'Esta compra no puede modificarse.');
        }

        $proveedores = Proveedor::activos()->orderBy('nombre')->get();
        $productos = Producto::activos()->orderBy('nombre')->get();
        $compra->load('detalles.producto', 'detalles.lote');

        return view('compras.edit', compact('compra', 'proveedores', 'productos'));
    }

    /**
     * Update the specified resource in storage (Modificar).
     */
    public function update(UpdateCompraRequest $request, Compra $compra)
    {
        try {
            $nuevaCompra = $this->compraService->modificarCompra(
                compraId: $compra->id,
                data: $request->except('motivo'),
                motivo: $request->motivo
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
}