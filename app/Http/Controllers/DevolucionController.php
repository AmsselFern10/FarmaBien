<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\DevolucionVenta;
use App\Services\DevolucionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class DevolucionController extends Controller
{
    protected DevolucionService $devolucionService;

    public function __construct(DevolucionService $devolucionService)
    {
        $this->devolucionService = $devolucionService;
        $this->middleware('permission:ver ventas')->only(['index', 'show', 'ticket']);
        $this->middleware('permission:realizar ventas')->only(['create', 'store']);
    }

    public function index(Request $request)
    {
        $query = DevolucionVenta::with(['venta.cliente', 'usuario', 'detalles.producto'])
            ->orderBy('fecha', 'desc');

        if ($request->filled('buscar')) {
            $buscar = $request->input('buscar');
            $query->where(function ($q) use ($buscar) {
                $q->where('numero_devolucion', 'like', "%{$buscar}%")
                  ->orWhereHas('venta', function ($qv) use ($buscar) {
                      $qv->where('numero_comprobante', 'like', "%{$buscar}%")
                         ->orWhereHas('cliente', function ($qc) use ($buscar) {
                             $qc->where('nombre', 'like', "%{$buscar}%");
                         });
                  });
            });
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->input('tipo'));
        }

        if ($request->filled('metodo_reembolso')) {
            $query->where('metodo_reembolso', $request->input('metodo_reembolso'));
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha', '>=', $request->input('fecha_desde'));
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha', '<=', $request->input('fecha_hasta'));
        }

        $totalMonto = (clone $query)->sum('monto_total');
        $totalDevoluciones = (clone $query)->count();

        $devoluciones = $query->paginate(15)->withQueryString();

        return view('devoluciones.index', compact('devoluciones', 'totalMonto', 'totalDevoluciones'));
    }

    public function create(Request $request)
    {
        $venta = null;
        $detallesDisponibles = [];

        if ($request->filled('venta_id')) {
            $venta = Venta::with(['detalles.producto', 'detalles.lote', 'detalles.presentacion', 'cliente', 'usuario', 'devoluciones.detalles'])
                ->where('estado', 'completada')
                ->findOrFail($request->input('venta_id'));

            // Calcular saldo de devoluciones previas
            $devolucionesPrevias = [];
            foreach ($venta->devoluciones as $dev) {
                if ($dev->estado === 'completada') {
                    foreach ($dev->detalles as $dDev) {
                        $devolucionesPrevias[$dDev->detalle_venta_id] = ($devolucionesPrevias[$dDev->detalle_venta_id] ?? 0) + $dDev->cantidad;
                    }
                }
            }

            foreach ($venta->detalles as $det) {
                $devuelto = $devolucionesPrevias[$det->id] ?? 0;
                $disponible = $det->cantidad - $devuelto;
                $detallesDisponibles[] = [
                    'detalle' => $det,
                    'cantidad_original' => $det->cantidad,
                    'cantidad_devuelta_previa' => $devuelto,
                    'cantidad_disponible' => max(0, $disponible),
                ];
            }
        }

        return view('devoluciones.create', compact('venta', 'detallesDisponibles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'venta_id'               => 'required|exists:ventas,id',
            'motivo'                 => 'required|string|max:100',
            'observaciones'          => 'nullable|string|max:500',
            'metodo_reembolso'       => 'required|in:efectivo,transferencia,tarjeta,saldo_favor,sin_reembolso',
            'banco'                  => 'nullable|string|max:100',
            'numero_transaccion'     => 'nullable|string|max:100',
            'items'                  => 'required|array|min:1',
            'items.*.detalle_venta_id' => 'required|exists:detalle_venta,id',
            'items.*.cantidad'       => 'required|integer|min:0',
            'items.*.reingresa_a_stock' => 'nullable|boolean',
            'items.*.estado_producto'=> 'required|in:buen_estado,danado,vencido',
        ]);

        try {
            $devolucion = $this->devolucionService->procesarDevolucion($validated);

            return redirect()->route('devoluciones.show', $devolucion)
                ->with('success', "Devolución {$devolucion->numero_devolucion} procesada exitosamente por " . formato_moneda($devolucion->monto_total) . ".");
        } catch (Exception $e) {
            Log::error("Error al procesar devolución: " . $e->getMessage());
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(DevolucionVenta $devolucion)
    {
        $devolucion->load(['venta.cliente', 'venta.usuario', 'usuario', 'sesionCaja', 'detalles.producto', 'detalles.lote', 'detalles.detalleVenta']);
        return view('devoluciones.show', compact('devolucion'));
    }

    public function ticket(DevolucionVenta $devolucion)
    {
        $devolucion->load(['venta.cliente', 'usuario', 'detalles.producto', 'detalles.lote']);
        return view('devoluciones.ticket', compact('devolucion'));
    }
}
