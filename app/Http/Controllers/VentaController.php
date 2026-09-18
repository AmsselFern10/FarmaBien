<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\Cliente;
use App\Models\Producto;
use App\Services\VentaService;
use App\Http\Requests\StoreVentaRequest;
use App\Http\Requests\UpdateVentaRequest;
use App\Http\Requests\AnularVentaRequest;
use Illuminate\Http\Request;
use Exception;

class VentaController extends Controller
{
    protected VentaService $ventaService;

    public function __construct(VentaService $ventaService)
    {
        $this->ventaService = $ventaService;
        $this->middleware('permission:ver ventas')->only(['index', 'show', 'ticket']);
        $this->middleware('permission:realizar ventas')->only(['create', 'store', 'edit', 'update', 'buscarProductos']);
        $this->middleware('permission:anular ventas')->only(['anular']);
    }

    public function index(Request $request)
    {
        $query = Venta::with(['cliente', 'usuario'])->withCount('detalles');

        // Si es cajero sin permiso de ver todas las ventas, solo ve las suyas
        if (!$request->user()->can('ver ventas') && $request->user()->can('ver ventas propias')) {
            $query->where('user_id', $request->user()->id);
        }

        if ($request->filled('buscar')) {
            $buscar = trim($request->input('buscar'));
            $query->where(function ($q) use ($buscar) {
                $q->where('numero_comprobante', 'like', "%{$buscar}%")
                  ->orWhereHas('cliente', function ($qc) use ($buscar) {
                      $qc->where('nombre', 'like', "%{$buscar}%")
                         ->orWhere('documento', 'like', "%{$buscar}%");
                  });
            });
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->input('estado'));
        }

        if ($request->filled('tipo_comprobante')) {
            $query->where('tipo_comprobante', $request->input('tipo_comprobante'));
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha', '>=', $request->input('fecha_desde'));
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha', '<=', $request->input('fecha_hasta'));
        }

        $ventas = $query->orderBy('fecha', 'desc')->paginate(15)->withQueryString();

        $stats = [
            'total' => Venta::count(),
            'completadas' => Venta::where('estado', 'completada')->count(),
            'anuladas' => Venta::where('estado', 'anulada')->count(),
            'ingresos' => Venta::where('estado', 'completada')->sum('total'),
        ];

        return view('ventas.index', compact('ventas', 'stats'));
    }

    public function create()
    {
        $clientes = Cliente::activos()->orderBy('nombre')->get();
        $categorias = \App\Models\Categoria::activas()->orderBy('nombre')->get();
        $productos = Producto::with([
                'categoria',
                'laboratorio',
                'presentacionesActivas',
                'lotes' => function ($query) {
                    $query->disponibles()->orderBy('fecha_vencimiento', 'asc'); // FEFO
                }
            ])
            ->activos()
            ->get()
            ->filter(fn($p) => $p->lotes->isNotEmpty())
            ->values();

        return view('ventas.create', compact('clientes', 'categorias', 'productos'));
    }

    public function store(StoreVentaRequest $request)
    {
        try {
            $venta = $this->ventaService->procesarVenta($request->validated());

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'venta' => $venta,
                    'ticket_url' => route('ventas.ticket', $venta),
                    'message' => 'Venta procesada con éxito.'
                ]);
            }

            return redirect()->route('ventas.show', $venta)
                ->with('success', "Venta #{$venta->id} procesada exitosamente.");
        } catch (Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 422);
            }

            return back()->withInput()->with('error', 'Error al procesar la venta: ' . $e->getMessage());
        }
    }

    public function show(Venta $venta)
    {
        $venta->load([
            'cliente',
            'usuario',
            'anuladoPor',
            'detalles.producto.laboratorio',
            'detalles.presentacion',
            'detalles.lote',
            'detalles.recetaDetalle.receta',
            'recetas',
            'ventaOriginal',
            'reemplazadaPor'
        ]);

        return view('ventas.show', compact('venta'));
    }

    public function edit(Venta $venta)
    {
        if (!$venta->puedeModificarse()) {
            return redirect()->route('ventas.show', $venta)
                ->with('error', 'Esta venta no puede ser modificada en su estado actual.');
        }

        $venta->load([
            'detalles.producto.presentacionesActivas',
            'detalles.producto.lotes',
            'detalles.lote',
            'detalles.presentacion',
            'cliente',
            'recetas'
        ]);

        $clientes = Cliente::activos()->orderBy('nombre')->get();
        $categorias = \App\Models\Categoria::activas()->orderBy('nombre')->get();
        $productos = Producto::with([
                'categoria',
                'laboratorio',
                'presentacionesActivas',
                'lotes' => function ($query) {
                    $query->disponibles()->orderBy('fecha_vencimiento', 'asc');
                }
            ])
            ->activos()
            ->get()
            ->filter(fn($p) => $p->lotes->isNotEmpty())
            ->values();

        return view('ventas.edit', compact('venta', 'clientes', 'categorias', 'productos'));
    }

    public function update(UpdateVentaRequest $request, Venta $venta)
    {
        try {
            $data = $request->validated();
            $motivo = $data['motivo_modificacion'] ?? 'Corrección en mostrador';

            $nuevaVenta = $this->ventaService->modificarVenta($venta->id, $data, $motivo);

            return redirect()->route('ventas.show', $nuevaVenta)
                ->with('success', "Venta actualizada exitosamente. Se generó la nueva venta #{$nuevaVenta->id}.");
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Error al modificar la venta: ' . $e->getMessage());
        }
    }

    public function anular(AnularVentaRequest $request, Venta $venta)
    {
        try {
            $this->ventaService->anularVenta($venta->id, $request->input('motivo'));

            return redirect()->route('ventas.show', $venta)
                ->with('success', "Venta #{$venta->id} anulada exitosamente y stock reincorporado al lote.");
        } catch (Exception $e) {
            return back()->with('error', 'No se pudo anular la venta: ' . $e->getMessage());
        }
    }

    public function ticket(Venta $venta)
    {
        $venta->load([
            'cliente',
            'usuario',
            'detalles.producto',
            'detalles.presentacion',
            'detalles.lote'
        ]);

        return view('ventas.ticket', compact('venta'));
    }

    public function buscarProductos(Request $request)
    {
        $termino = trim($request->input('q', ''));
        if (strlen($termino) < 2) {
            return response()->json([]);
        }

        $productos = $this->ventaService->buscarProductosParaVenta($termino);

        return response()->json($productos);
    }
}
