<?php

namespace App\Http\Controllers;

use App\Models\DevolucionCompra;
use App\Models\DetalleDevolucionCompra;
use App\Models\Lote;
use App\Models\Proveedor;
use App\Models\Compra;
use App\Services\DevolucionCompraService;
use App\Http\Requests\StoreDevolucionCompraRequest;
use App\Http\Requests\AnularDevolucionCompraRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

class DevolucionCompraController extends Controller
{
    protected DevolucionCompraService $devolucionService;

    public function __construct(DevolucionCompraService $devolucionService)
    {
        $this->devolucionService = $devolucionService;

        $this->middleware('permission:ver compras')->only(['index', 'show']);
        $this->middleware('permission:registrar compras')->only(['create', 'store', 'marcarEnviada', 'confirmar']);
        $this->middleware('permission:anular compras')->only(['anular']);
    }

    public function index(Request $request)
    {
        $query = DevolucionCompra::with(['proveedor', 'usuario', 'detalles.producto'])
            ->withCount('detalles');

        if ($request->filled('buscar')) {
            $buscar = trim($request->input('buscar'));
            $query->where(function ($q) use ($buscar) {
                $q->where('numero_devolucion', 'like', "%{$buscar}%")
                  ->orWhereHas('proveedor', fn($p) => $p->where('nombre', 'like', "%{$buscar}%")->orWhere('ruc', 'like', "%{$buscar}%"));
            });
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->input('estado'));
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('created_at', '>=', $request->input('fecha_desde'));
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('created_at', '<=', $request->input('fecha_hasta'));
        }

        $devoluciones = $query->orderBy('created_at', 'desc')->paginate(perPage(20))->withQueryString();
        $metricas = $this->devolucionService->getMetricas();

        return view('compras.devoluciones.index', compact('devoluciones', 'metricas'));
    }

    public function create(Request $request)
    {
        $proveedores = Proveedor::activos()->orderBy('nombre')->get(['id', 'nombre', 'ruc']);

        $compraId = $request->input('compra_id');
        $loteIds = $request->input('lote_ids');
        if (is_string($loteIds)) {
            $loteIds = array_filter(explode(',', $loteIds));
        }

        $compra = null;
        $detallesDisponibles = [];
        $proveedorSeleccionado = null;

        if ($compraId) {
            $compra = Compra::with(['proveedor', 'usuario', 'lotes.producto.laboratorio'])->find($compraId);
            if ($compra) {
                $proveedorSeleccionado = $compra->proveedor;

                $loteIds = $compra->lotes->pluck('id');
                $devueltasPorLote = DetalleDevolucionCompra::whereIn('lote_id', $loteIds)
                    ->whereHas('devolucionCompra', fn($q) => $q->where('estado', '!=', 'rechazada'))
                    ->groupBy('lote_id')
                    ->selectRaw('lote_id, SUM(cantidad) as total')
                    ->pluck('total', 'lote_id');

                foreach ($compra->lotes as $lote) {
                    $yaDevuelta = (int) ($devueltasPorLote[$lote->id] ?? 0);
                    $disponible = max(0, min($lote->stock_inicial - $yaDevuelta, $lote->stock_actual));

                    $detallesDisponibles[] = [
                        'lote'                 => $lote,
                        'cantidad_comprada'    => $lote->stock_inicial,
                        'cantidad_ya_devuelta' => $yaDevuelta,
                        'cantidad_disponible'  => $disponible,
                        'precio_unitario'      => (float) ($lote->precio_compra ?? 0),
                        'es_controlado'        => (bool) ($lote->producto?->esControlado()),
                    ];
                }
            }

        } elseif (!empty($loteIds)) {
            $lotesSel = Lote::with(['producto.laboratorio', 'proveedor', 'compra'])->whereIn('id', (array)$loteIds)->get();
            if ($lotesSel->isNotEmpty()) {
                $proveedorSeleccionado = $lotesSel->first()->proveedor;

                $devueltasPorLote = DetalleDevolucionCompra::whereIn('lote_id', $lotesSel->pluck('id'))
                    ->whereHas('devolucionCompra', fn($q) => $q->where('estado', '!=', 'rechazada'))
                    ->groupBy('lote_id')
                    ->selectRaw('lote_id, SUM(cantidad) as total')
                    ->pluck('total', 'lote_id');

                foreach ($lotesSel as $lote) {
                    $yaDevuelta = (int) ($devueltasPorLote[$lote->id] ?? 0);
                    $disponible = max(0, min($lote->stock_inicial - $yaDevuelta, $lote->stock_actual));

                    $detallesDisponibles[] = [
                        'lote'                 => $lote,
                        'cantidad_comprada'    => $lote->stock_inicial,
                        'cantidad_ya_devuelta' => $yaDevuelta,
                        'cantidad_disponible'  => $disponible,
                        'precio_unitario'      => (float) ($lote->precio_compra ?? 0),
                        'es_controlado'        => (bool) ($lote->producto?->esControlado()),
                    ];
                }
            }
        }

        // Búsqueda de compras recientes con lotes devolvibles
        $buscarCompra = $request->input('buscar_compra');
        $comprasRecientesQuery = Compra::with(['proveedor', 'usuario', 'lotes'])
            ->where('estado', 'recibida')
            ->whereHas('lotes', fn($q) => $q->where('stock_actual', '>', 0));

        if (!empty($buscarCompra)) {
            $comprasRecientesQuery->where(function($q) use ($buscarCompra) {
                $q->where('numero_comprobante', 'like', "%{$buscarCompra}%")
                  ->orWhereHas('proveedor', fn($p) => $p->where('nombre', 'like', "%{$buscarCompra}%")->orWhere('ruc', 'like', "%{$buscarCompra}%"))
                  ->orWhereHas('lotes.producto', fn($pr) => $pr->where('nombre', 'like', "%{$buscarCompra}%"));
            });
        }
        $comprasRecientes = $comprasRecientesQuery->orderBy('created_at', 'desc')->paginate(10, ['*'], 'page_compras')->withQueryString();

        // Lotes disponibles para el modo "Por Lote"
        $buscarLote = $request->input('buscar_lote');
        $lotesDisponiblesQuery = Lote::with(['producto.laboratorio', 'proveedor', 'compra'])
            ->where('activo', true)
            ->where('stock_actual', '>', 0);

        if (!empty($buscarLote)) {
            $lotesDisponiblesQuery->where(function($q) use ($buscarLote) {
                $q->where('numero_lote', 'like', "%{$buscarLote}%")
                  ->orWhereHas('producto', fn($p) => $p->where('nombre', 'like', "%{$buscarLote}%")->orWhere('codigo_barra', 'like', "%{$buscarLote}%"))
                  ->orWhereHas('proveedor', fn($pr) => $pr->where('nombre', 'like', "%{$buscarLote}%"));
            });
        }
        $lotesDisponibles = $lotesDisponiblesQuery->orderBy('fecha_vencimiento', 'asc')->paginate(15, ['*'], 'page_lotes')->withQueryString();

        // Catálogo para adición rápida
        $todosLotes = Lote::with(['producto', 'proveedor'])
            ->where('activo', true)
            ->where('stock_actual', '>', 0)
            ->orderBy('fecha_vencimiento')
            ->get();

        return view('compras.devoluciones.create', compact(
            'proveedores',
            'compra',
            'detallesDisponibles',
            'proveedorSeleccionado',
            'comprasRecientes',
            'lotesDisponibles',
            'todosLotes'
        ));
    }

    public function store(StoreDevolucionCompraRequest $request)
    {
        try {
            $devolucion = $this->devolucionService->registrarDevolucion($request->validated(), Auth::id() ?? 1);

            return redirect()->route('compras.devoluciones.show', $devolucion)
                ->with('success', "Devolución {$devolucion->numero_devolucion} registrada. Stock descontado y Kardex actualizado.");

        } catch (Exception $e) {
            Log::error('Error al registrar devolución a proveedor', [
                'user_id' => Auth::id(),
                'message' => $e->getMessage(),
            ]);

            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(DevolucionCompra $devolucionCompra)
    {
        $devolucionCompra->load([
            'proveedor', 'usuario', 'compra',
            'detalles.lote', 'detalles.producto.laboratorio',
        ]);

        return view('compras.devoluciones.show', compact('devolucionCompra'));
    }

    public function marcarEnviada(DevolucionCompra $devolucionCompra)
    {
        try {
            $this->devolucionService->marcarEnviada($devolucionCompra);

            return back()->with('success', "Devolución {$devolucionCompra->numero_devolucion} marcada como enviada al proveedor.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function confirmar(DevolucionCompra $devolucionCompra)
    {
        try {
            $this->devolucionService->confirmarDevolucion($devolucionCompra);

            return back()->with('success', "Devolución {$devolucionCompra->numero_devolucion} confirmada por el proveedor.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function anular(AnularDevolucionCompraRequest $request, DevolucionCompra $devolucionCompra)
    {
        try {
            $motivo = $request->validated()['motivo'];
            $this->devolucionService->anularDevolucion($devolucionCompra, $motivo, Auth::id() ?? 1);

            return redirect()->route('compras.devoluciones.show', $devolucionCompra)
                ->with('success', "Devolución {$devolucionCompra->numero_devolucion} anulada. Stock y Kardex restituidos correctamente.");
        } catch (Exception $e) {
            Log::error("Error al anular devolución {$devolucionCompra->id}: " . $e->getMessage(), [
                'user_id' => Auth::id(),
            ]);

            return back()->with('error', 'No se pudo anular la devolución: ' . $e->getMessage());
        }
    }
}
