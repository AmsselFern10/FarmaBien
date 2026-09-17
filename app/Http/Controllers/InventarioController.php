<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Lote;
use App\Models\MovimientoInventario;
use App\Services\InventarioService;
use App\Http\Requests\AjusteInventarioRequest;
use Illuminate\Http\Request;
use Exception;

class InventarioController extends Controller
{
    protected InventarioService $inventarioService;

    public function __construct(InventarioService $inventarioService)
    {
        $this->inventarioService = $inventarioService;
        $this->middleware('permission:ver movimientos inventario')->only(['index', 'movimientos', 'lotes', 'kardexProducto', 'alertas']);
        $this->middleware('permission:ajustar inventario')->only(['ajustar', 'storeAjuste']);
    }

    public function index()
    {
        $valorizacion = $this->inventarioService->valorizacionInventario();
        $productosBajoStock = $this->inventarioService->productosConStockBajo();
        $lotesPorVencer = $this->inventarioService->lotesProximosVencer(60);
        $lotesVencidos = $this->inventarioService->lotesVencidos();

        return view('inventario.index', compact('valorizacion', 'productosBajoStock', 'lotesPorVencer', 'lotesVencidos'));
    }

    public function movimientos(Request $request)
    {
        $query = MovimientoInventario::with(['producto.laboratorio', 'lote', 'usuario']);

        if ($request->filled('producto_id')) {
            $query->where('producto_id', $request->input('producto_id'));
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->input('tipo'));
        }

        if ($request->filled('subtipo')) {
            $query->where('subtipo', $request->input('subtipo'));
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_movimiento', '>=', $request->input('fecha_desde'));
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_movimiento', '<=', $request->input('fecha_hasta'));
        }

        $movimientos = $query->orderBy('fecha_movimiento', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(20)
            ->withQueryString();

        $productos = Producto::activos()->orderBy('nombre')->get();

        return view('inventario.movimientos', compact('movimientos', 'productos'));
    }

    public function lotes(Request $request)
    {
        $query = Lote::with(['producto.categoria', 'producto.laboratorio', 'proveedor'])
            ->where('activo', true);

        if ($request->filled('buscar')) {
            $buscar = trim($request->input('buscar'));
            $query->where(function ($q) use ($buscar) {
                $q->where('numero_lote', 'like', "%{$buscar}%")
                  ->orWhereHas('producto', function ($qp) use ($buscar) {
                      $qp->where('nombre', 'like', "%{$buscar}%")
                         ->orWhere('principio_activo', 'like', "%{$buscar}%");
                  });
            });
        }

        if ($request->filled('filtro_vencimiento')) {
            if ($request->input('filtro_vencimiento') === 'vencidos') {
                $query->vencidos();
            } elseif ($request->input('filtro_vencimiento') === 'proximos_30') {
                $query->proximosVencer(30);
            } elseif ($request->input('filtro_vencimiento') === 'proximos_60') {
                $query->proximosVencer(60);
            }
        }

        $lotes = $query->orderBy('fecha_vencimiento', 'asc')->paginate(15)->withQueryString();

        return view('inventario.lotes', compact('lotes'));
    }

    public function kardexProducto(Producto $producto, Request $request)
    {
        $fechaDesde = $request->input('fecha_desde');
        $fechaHasta = $request->input('fecha_hasta');

        $movimientos = $this->inventarioService->kardexProducto($producto->id, $fechaDesde, $fechaHasta);
        $producto->load(['categoria', 'laboratorio', 'presentacionesActivas', 'lotes']);

        return view('inventario.kardex-producto', compact('producto', 'movimientos'));
    }

    public function alertas()
    {
        $productosBajoStock = $this->inventarioService->productosConStockBajo();
        $lotesPorVencer = $this->inventarioService->lotesProximosVencer(60);
        $lotesVencidos = $this->inventarioService->lotesVencidos();

        return view('inventario.alertas', compact('productosBajoStock', 'lotesPorVencer', 'lotesVencidos'));
    }

    public function ajustar()
    {
        $lotes = Lote::with(['producto.laboratorio'])
            ->where('activo', true)
            ->orderBy('numero_lote', 'asc')
            ->get();

        return view('inventario.ajustar', compact('lotes'));
    }

    public function storeAjuste(AjusteInventarioRequest $request)
    {
        try {
            $movimiento = $this->inventarioService->ajustarInventario($request->validated());

            return redirect()->route('inventario.movimientos')
                ->with('success', "Ajuste registrado exitosamente en el Kardex para el lote '{$movimiento->lote->numero_lote}'.");
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Error al procesar el ajuste de inventario: ' . $e->getMessage());
        }
    }
}
