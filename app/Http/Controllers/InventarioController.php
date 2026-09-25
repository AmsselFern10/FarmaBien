<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Lote;
use App\Models\MovimientoInventario;
use App\Models\AuditLog;
use App\Services\InventarioService;
use App\Http\Requests\AjusteInventarioRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use Exception;

class InventarioController extends Controller
{
    protected InventarioService $inventarioService;

    public function __construct(InventarioService $inventarioService)
    {
        $this->inventarioService = $inventarioService;
        $this->middleware('permission:ver movimientos inventario')->only(['index', 'movimientos', 'lotes', 'kardexProducto', 'alertas']);
        $this->middleware('permission:ajustar inventario')->only(['ajustar', 'storeAjuste', 'bajaVencidos']);
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

        $productos = Producto::activos()->orderBy('nombre')->get(['id', 'nombre']);

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
        $lotes = Lote::select(['id', 'producto_id', 'numero_lote', 'stock_actual', 'fecha_vencimiento'])
            ->with(['producto:id,nombre,principio_activo'])
            ->where('activo', true)
            ->where('stock_actual', '>', 0)
            ->orderBy('numero_lote', 'asc')
            ->get();

        return view('inventario.ajustar', compact('lotes'));
    }

    public function storeAjuste(AjusteInventarioRequest $request)
    {
        try {
            $movimiento = $this->inventarioService->ajustarInventario($request->validated());

            AuditLog::log('inventario', 'ajuste', "Ajuste de inventario en lote {$movimiento->lote->numero_lote} ({$movimiento->producto->nombre})", [
                'movimiento_id' => $movimiento->id,
                'tipo' => $movimiento->tipo,
                'cantidad' => $movimiento->cantidad,
                'motivo' => $movimiento->motivo,
            ]);

            return redirect()->route('inventario.movimientos')
                ->with('success', "Ajuste de inventario aplicado exitosamente en el Kardex para el lote '{$movimiento->lote->numero_lote}' ({$movimiento->producto->nombre}).");
        } catch (QueryException $qe) {
            Log::error('Error de base de datos en ajuste de inventario', [
                'user_id' => auth()->id(),
                'payload' => $request->except(['_token']),
                'message' => $qe->getMessage(),
            ]);

            return back()->withInput()->with('error', 'Error al procesar el ajuste en la base de datos debido a un conflicto de concurrencia o integridad.');
        } catch (Exception $e) {
            Log::error('Excepción en ajuste de inventario', [
                'user_id' => auth()->id(),
                'payload' => $request->except(['_token']),
                'message' => $e->getMessage(),
            ]);

            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Endpoint para dar de baja automática a lotes vencidos
     */
    public function bajaVencidos()
    {
        try {
            $totalBajas = $this->inventarioService->desactivarLotesVencidos();

            AuditLog::log('inventario', 'baja_vencidos', "Baja automática de {$totalBajas} lote(s) vencido(s)", [
                'total_bajas' => $totalBajas,
            ]);

            return redirect()->route('inventario.alertas')
                ->with('success', "Se procesó la baja automática de {$totalBajas} lote(s) vencido(s) con registro en Kardex.");
        } catch (Exception $e) {
            Log::error('Error al ejecutar baja de lotes vencidos', [
                'user_id' => auth()->id(),
                'message' => $e->getMessage(),
            ]);

            return back()->with('error', 'Error al procesar la baja de lotes vencidos: ' . $e->getMessage());
        }
    }
}
