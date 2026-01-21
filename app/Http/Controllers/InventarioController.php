<?php

namespace App\Http\Controllers;

use App\Http\Requests\AjusteInventarioRequest;
use App\Services\InventarioService;
use App\Models\Producto;
use App\Models\Lote;
use App\Models\Categoria;
use Illuminate\Http\Request;

class InventarioController extends Controller
{
    protected $inventarioService;

    public function __construct(InventarioService $inventarioService)
    {
        $this->inventarioService = $inventarioService;
        
        $this->middleware('permission:ver movimientos inventario')->only([
            'index', 'kardexProducto', 'kardexLote'
        ]);
        $this->middleware('permission:ajustar inventario')->only([
            'ajustar', 'storeAjuste'
        ]);
    }

    /**
     * Dashboard de inventario.
     */
    public function index(Request $request)
    {
        // Resumen por categoría
        $resumenCategorias = $this->inventarioService->resumenPorCategoria();

        // Productos con stock bajo
        $productosStockBajo = $this->inventarioService->productosConStockBajo();

        // Lotes próximos a vencer (30 días)
        $lotesProximosVencer = $this->inventarioService->lotesProximosVencer(30);

        // Lotes vencidos
        $lotesVencidos = $this->inventarioService->lotesVencidos();

        // Valorización del inventario
        $valorizacion = $this->inventarioService->valorizacionInventario();

        return view('inventario.index', compact(
            'resumenCategorias',
            'productosStockBajo',
            'lotesProximosVencer',
            'lotesVencidos',
            'valorizacion'
        ));
    }

    /**
     * Mostrar formulario de ajuste de inventario.
     */
    public function ajustar(Request $request)
    {
        $productos = Producto::with(['lotes' => function ($query) {
            $query->activos()->orderBy('fecha_vencimiento', 'asc');
        }])
        ->activos()
        ->orderBy('nombre')
        ->get();

        return view('inventario.ajustar', compact('productos'));
    }

    /**
     * Procesar ajuste de inventario.
     */
    public function storeAjuste(AjusteInventarioRequest $request)
    {
        try {
            $movimiento = $this->inventarioService->ajustarInventario($request->validated());
            
            $lote = $movimiento->lote;
            $stockAnterior = $lote->stock_actual - $movimiento->cantidad;
            
            return redirect()
                ->route('inventario.index')
                ->with('success', 
                    "Ajuste realizado correctamente. " .
                    "Lote: {$lote->numero_lote}, " .
                    "Stock anterior: {$stockAnterior}, " .
                    "Stock nuevo: {$lote->stock_actual}"
                );
                
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Error al realizar el ajuste: ' . $e->getMessage());
        }
    }

    /**
     * Ver kardex de un producto.
     */
    public function kardexProducto(Request $request, Producto $producto)
    {
        $fechaInicio = $request->fecha_inicio;
        $fechaFin = $request->fecha_fin ?? now()->format('Y-m-d');

        $movimientos = $this->inventarioService->kardexProducto(
            productoId: $producto->id,
            fechaInicio: $fechaInicio,
            fechaFin: $fechaFin
        );

        return view('inventario.kardex-producto', compact('producto', 'movimientos', 'fechaInicio', 'fechaFin'));
    }

    /**
     * Ver kardex de un lote específico.
     */
    public function kardexLote(Lote $lote)
    {
        $movimientos = $this->inventarioService->kardexLote($lote->id);
        $lote->load('producto', 'proveedor', 'compra');

        return view('inventario.kardex-lote', compact('lote', 'movimientos'));
    }

    /**
     * Alertas de inventario.
     */
    public function alertas(Request $request)
    {
        // Filtro de días para vencimiento
        $diasVencimiento = $request->input('dias_vencimiento', 30);

        // Productos con stock bajo
        $productosStockBajo = $this->inventarioService->productosConStockBajo();

        // Lotes próximos a vencer
        $lotesProximosVencer = $this->inventarioService->lotesProximosVencer($diasVencimiento);

        // Lotes vencidos
        $lotesVencidos = $this->inventarioService->lotesVencidos();

        return view('inventario.alertas', compact(
            'productosStockBajo',
            'lotesProximosVencer',
            'lotesVencidos',
            'diasVencimiento'
        ));
    }

    /**
     * Desactivar lotes vencidos automáticamente.
     */
    public function desactivarLotesVencidos()
    {
        try {
            $cantidad = $this->inventarioService->desactivarLotesVencidos();
            
            return redirect()
                ->route('inventario.alertas')
                ->with('success', "{$cantidad} lote(s) vencido(s) desactivado(s) correctamente.");
                
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Error al desactivar lotes vencidos: ' . $e->getMessage());
        }
    }

    /**
     * Valorización del inventario.
     */
    public function valorizacion()
    {
        $valorizacion = $this->inventarioService->valorizacionInventario();

        return view('inventario.valorizacion', compact('valorizacion'));
    }

    /**
     * Obtener stock actual de un lote (AJAX).
     */
    public function obtenerStockLote(Lote $lote)
    {
        return response()->json([
            'stock_actual' => $lote->stock_actual,
            'stock_inicial' => $lote->stock_inicial,
            'numero_lote' => $lote->numero_lote,
            'producto' => $lote->producto->nombre,
        ]);
    }

    /**
     * Listado de todos los lotes.
     */
    public function lotes(Request $request)
    {
        $query = Lote::with(['producto', 'proveedor', 'compra'])
            ->orderBy('fecha_vencimiento', 'asc');

        // Filtros
        if ($request->filled('producto_id')) {
            $query->where('producto_id', $request->producto_id);
        }

        if ($request->filled('estado')) {
            if ($request->estado === 'disponible') {
                $query->disponibles();
            } elseif ($request->estado === 'vencido') {
                $query->vencidos();
            } elseif ($request->estado === 'proximo_vencer') {
                $query->proximosVencer(30);
            }
        }

        if ($request->filled('activo')) {
            $query->where('activo', $request->activo);
        } else {
            $query->activos();
        }

        $lotes = $query->paginate(20);
        $productos = Producto::activos()->orderBy('nombre')->get();

        return view('inventario.lotes', compact('lotes', 'productos'));
    }

    /**
     * Ver detalle de un lote.
     */
    public function showLote(Lote $lote)
    {
        $lote->load([
            'producto.categoria',
            'proveedor',
            'compra.usuario',
            'movimientos.usuario'
        ]);

        // Calcular movimientos totales
        $totalEntradas = $lote->movimientos()->entradas()->sum('cantidad');
        $totalSalidas = abs($lote->movimientos()->salidas()->sum('cantidad'));
        $totalAjustes = $lote->movimientos()->ajustes()->sum('cantidad');

        return view('inventario.show-lote', compact(
            'lote',
            'totalEntradas',
            'totalSalidas',
            'totalAjustes'
        ));
    }
}