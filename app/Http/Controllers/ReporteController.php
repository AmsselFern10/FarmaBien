<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\Compra;
use App\Models\Producto;
use App\Models\MovimientoInventario;
use App\Services\InventarioService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    protected $inventarioService;

    public function __construct(InventarioService $inventarioService)
    {
        $this->inventarioService = $inventarioService;
        
        $this->middleware('permission:ver reportes ventas')->only(['ventas', 'ventasPorPeriodo']);
        $this->middleware('permission:ver reportes compras')->only(['compras', 'comprasPorPeriodo']);
        $this->middleware('permission:ver reportes inventario')->only([
            'inventario', 'valorizacion', 'movimientos'
        ]);
    }

    /**
     * Menú principal de reportes.
     */
    public function index()
    {
        return view('reportes.index');
    }

    /**
     * Reporte de ventas.
     */
    public function ventas(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->input('fecha_fin', now()->format('Y-m-d'));

        $ventas = Venta::with(['cliente', 'usuario'])
            ->completadas()
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->orderBy('fecha', 'desc')
            ->get();

        $totalVentas = $ventas->sum('total');
        $cantidadVentas = $ventas->count();
        $promedioVenta = $cantidadVentas > 0 ? $totalVentas / $cantidadVentas : 0;

        // Ventas por día
        $ventasPorDia = $ventas->groupBy(function ($venta) {
            return $venta->fecha->format('Y-m-d');
        })->map(function ($ventasDia) {
            return [
                'fecha' => $ventasDia->first()->fecha->format('d/m/Y'),
                'cantidad' => $ventasDia->count(),
                'total' => $ventasDia->sum('total'),
            ];
        });

        // Ventas por usuario
        $ventasPorUsuario = $ventas->groupBy('user_id')->map(function ($ventasUsuario) {
            return [
                'usuario' => $ventasUsuario->first()->usuario->name,
                'cantidad' => $ventasUsuario->count(),
                'total' => $ventasUsuario->sum('total'),
            ];
        });

        // Ventas por método de pago
        $ventasPorMetodoPago = $ventas->groupBy('metodo_pago')->map(function ($ventasMetodo) {
            return [
                'metodo' => $ventasMetodo->first()->metodo_pago,
                'cantidad' => $ventasMetodo->count(),
                'total' => $ventasMetodo->sum('total'),
            ];
        });

        return view('reportes.ventas', compact(
            'ventas',
            'fechaInicio',
            'fechaFin',
            'totalVentas',
            'cantidadVentas',
            'promedioVenta',
            'ventasPorDia',
            'ventasPorUsuario',
            'ventasPorMetodoPago'
        ));
    }

    /**
     * Reporte de compras.
     */
    public function compras(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->input('fecha_fin', now()->format('Y-m-d'));

        $compras = Compra::with(['proveedor', 'usuario', 'detalles'])
            ->recibidas()
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->orderBy('fecha', 'desc')
            ->get();

        $totalCompras = $compras->sum('total');
        $cantidadCompras = $compras->count();
        $promedioCompra = $cantidadCompras > 0 ? $totalCompras / $cantidadCompras : 0;

        // Compras por proveedor
        $comprasPorProveedor = $compras->groupBy('proveedor_id')->map(function ($comprasProveedor) {
            return [
                'proveedor' => $comprasProveedor->first()->proveedor->nombre,
                'cantidad' => $comprasProveedor->count(),
                'total' => $comprasProveedor->sum('total'),
            ];
        })->sortByDesc('total');

        // Total de productos comprados
        $totalProductosComprados = DB::table('detalle_compra')
            ->join('compras', 'detalle_compra.compra_id', '=', 'compras.id')
            ->whereBetween('compras.fecha', [$fechaInicio, $fechaFin])
            ->where('compras.estado', 'recibida')
            ->sum('detalle_compra.cantidad');

        return view('reportes.compras', compact(
            'compras',
            'fechaInicio',
            'fechaFin',
            'totalCompras',
            'cantidadCompras',
            'promedioCompra',
            'comprasPorProveedor',
            'totalProductosComprados'
        ));
    }

    /**
     * Reporte de inventario.
     */
    public function inventario()
    {
        // Resumen por categoría
        $resumenCategorias = $this->inventarioService->resumenPorCategoria();

        // Productos con stock bajo
        $productosStockBajo = $this->inventarioService->productosConStockBajo();

        // Lotes próximos a vencer
        $lotesProximosVencer = $this->inventarioService->lotesProximosVencer(30);

        // Lotes vencidos
        $lotesVencidos = $this->inventarioService->lotesVencidos();

        // Valorización
        $valorizacion = $this->inventarioService->valorizacionInventario();

        return view('reportes.inventario', compact(
            'resumenCategorias',
            'productosStockBajo',
            'lotesProximosVencer',
            'lotesVencidos',
            'valorizacion'
        ));
    }

    /**
     * Reporte de valorización del inventario.
     */
    public function valorizacion()
    {
        $valorizacion = $this->inventarioService->valorizacionInventario();

        return view('reportes.valorizacion', compact('valorizacion'));
    }

    /**
     * Reporte de movimientos de inventario.
     */
    public function movimientos(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->input('fecha_fin', now()->format('Y-m-d'));
        $tipo = $request->input('tipo'); // entrada, salida, ajuste

        $query = MovimientoInventario::with(['producto', 'lote', 'usuario'])
            ->whereBetween('fecha_movimiento', [$fechaInicio, $fechaFin])
            ->orderBy('fecha_movimiento', 'desc');

        if ($tipo) {
            $query->where('tipo', $tipo);
        }

        $movimientos = $query->get();

        // Estadísticas
        $totalEntradas = $movimientos->where('tipo', 'entrada')->sum('cantidad');
        $totalSalidas = abs($movimientos->where('tipo', 'salida')->sum('cantidad'));
        $totalAjustes = $movimientos->where('tipo', 'ajuste')->sum('cantidad');

        // Movimientos por tipo
        $movimientosPorTipo = $movimientos->groupBy('tipo')->map(function ($movsTipo) {
            return [
                'tipo' => $movsTipo->first()->tipo,
                'cantidad' => $movsTipo->count(),
                'total_unidades' => $movsTipo->sum('cantidad'),
            ];
        });

        return view('reportes.movimientos', compact(
            'movimientos',
            'fechaInicio',
            'fechaFin',
            'tipo',
            'totalEntradas',
            'totalSalidas',
            'totalAjustes',
            'movimientosPorTipo'
        ));
    }

    /**
     * Productos más vendidos.
     */
    public function productosMasVendidos(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->input('fecha_fin', now()->format('Y-m-d'));
        $limite = $request->input('limite', 20);

        $productos = DB::table('detalle_venta')
            ->join('ventas', 'detalle_venta.venta_id', '=', 'ventas.id')
            ->join('productos', 'detalle_venta.producto_id', '=', 'productos.id')
            ->join('categorias', 'productos.categoria_id', '=', 'categorias.id')
            ->whereBetween('ventas.fecha', [$fechaInicio, $fechaFin])
            ->where('ventas.estado', 'completada')
            ->select(
                'productos.id',
                'productos.nombre',
                'categorias.nombre as categoria',
                DB::raw('SUM(detalle_venta.cantidad) as total_vendido'),
                DB::raw('SUM(detalle_venta.subtotal) as total_ingresos'),
                DB::raw('COUNT(DISTINCT detalle_venta.venta_id) as numero_ventas')
            )
            ->groupBy('productos.id', 'productos.nombre', 'categorias.nombre')
            ->orderBy('total_vendido', 'desc')
            ->limit($limite)
            ->get();

        return view('reportes.productos-mas-vendidos', compact(
            'productos',
            'fechaInicio',
            'fechaFin',
            'limite'
        ));
    }

    /**
     * Exportar reporte a Excel (requiere maatwebsite/excel).
     */
    public function exportarVentas(Request $request)
    {
        // Implementar con Laravel Excel
        // return Excel::download(new VentasExport($request->all()), 'ventas.xlsx');
        
        return back()->with('info', 'Exportación en desarrollo...');
    }
}