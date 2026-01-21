<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVentaRequest;
use App\Http\Requests\UpdateVentaRequest;
use App\Http\Requests\AnularVentaRequest;
use App\Services\VentaService;
use App\Models\Venta;
use App\Models\Cliente;
use App\Models\Producto;
use Illuminate\Http\Request;

class VentaController extends Controller
{
    protected $ventaService;

    public function __construct(VentaService $ventaService)
    {
        $this->ventaService = $ventaService;
        
        // Middleware de permisos
        $this->middleware('permission:ver ventas')->only(['index', 'show']);
        $this->middleware('permission:realizar ventas')->only(['create', 'store']);
        $this->middleware('permission:anular ventas')->only(['edit', 'update', 'anular']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Venta::with(['cliente', 'usuario', 'detalles.producto'])
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

        if ($request->filled('cliente_id')) {
            $query->where('cliente_id', $request->cliente_id);
        }
        /** @var \App\Models\User $user */
        $user = auth()->user();
        // Solo el cajero ve sus propias ventas
        if ($user->hasRole('Cajero')) {
            $query->where('user_id', $user->id);
        }

        $ventas = $query->paginate(15);

        return view('ventas.index', compact('ventas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $clientes = Cliente::activos()->orderBy('nombre')->get();
        
        return view('ventas.create', compact('clientes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreVentaRequest $request)
    {
        try {
            $venta = $this->ventaService->procesarVenta($request->validated());
            
            return redirect()
                ->route('ventas.show', $venta)
                ->with('success', "Venta #{$venta->id} registrada correctamente. Total: S/ " . number_format($venta->total, 2));
                
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Error al procesar la venta: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Venta $venta)
    {
        $venta->load([
            'cliente',
            'usuario',
            'anuladoPor',
            'detalles.producto',
            'detalles.lote',
            'recetas',
            'ventaOriginal',
            'reemplazadaPor'
        ]);

        // Obtener historial de modificaciones si existe
        $historial = null;
        if ($venta->esModificacion() || $venta->fueModificada()) {
            $historial = $this->ventaService->historialModificacionesVenta($venta->id);
        }

        return view('ventas.show', compact('venta', 'historial'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Venta $venta)
    {
        // Verificar que puede modificarse
        if (!$venta->puedeModificarse()) {
            return redirect()
                ->route('ventas.show', $venta)
                ->with('error', 'Esta venta no puede modificarse.');
        }

        $clientes = Cliente::activos()->orderBy('nombre')->get();
        $venta->load('detalles.producto', 'detalles.lote', 'recetas');

        return view('ventas.edit', compact('venta', 'clientes'));
    }

    /**
     * Update the specified resource in storage (Modificar).
     */
    public function update(UpdateVentaRequest $request, Venta $venta)
    {
        try {
            $nuevaVenta = $this->ventaService->modificarVenta(
                ventaId: $venta->id,
                data: $request->except('motivo'),
                motivo: $request->motivo
            );
            
            return redirect()
                ->route('ventas.show', $nuevaVenta)
                ->with('success', "Venta modificada correctamente. Venta original: #{$venta->id} → Nueva venta: #{$nuevaVenta->id}");
                
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Error al modificar la venta: ' . $e->getMessage());
        }
    }

    /**
     * Anular una venta.
     */
    public function anular(AnularVentaRequest $request, Venta $venta)
    {
        try {
            $ventaAnulada = $this->ventaService->anularVenta(
                ventaId: $venta->id,
                motivo: $request->motivo
            );
            
            return redirect()
                ->route('ventas.index')
                ->with('success', "Venta #{$venta->id} anulada correctamente.");
                
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Error al anular la venta: ' . $e->getMessage());
        }
    }

    /**
     * Buscar productos para venta (AJAX).
     */
    public function buscarProductos(Request $request)
    {
        if (!$request->filled('termino')) {
            return response()->json([]);
        }

        $productos = $this->ventaService->buscarProductosParaVenta($request->termino);

        return response()->json($productos);
    }

    /**
     * Obtener lotes disponibles de un producto (AJAX).
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
                    'fecha_vencimiento' => $lote->fecha_vencimiento->format('d/m/Y'),
                    'stock_actual' => $lote->stock_actual,
                    'precio_compra' => $lote->precio_compra,
                    'dias_para_vencer' => now()->diffInDays($lote->fecha_vencimiento),
                ];
            });

        return response()->json($lotes);
    }

    /**
     * Imprimir ticket de venta (PDF).
     */
    public function imprimir(Venta $venta)
    {
        $venta->load('cliente', 'detalles.producto', 'detalles.lote');

        // Aquí puedes usar DomPDF o blade para generar el ticket
        return view('ventas.ticket', compact('venta'));
    }
}