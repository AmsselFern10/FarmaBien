<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\Compra;
use App\Models\Producto;
use App\Models\DetalleVenta;
use App\Services\InventarioService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    protected InventarioService $inventarioService;

    public function __construct(InventarioService $inventarioService)
    {
        $this->inventarioService = $inventarioService;
        $this->middleware('permission:ver reportes ventas')->only(['index', 'ventas', 'productosMasVendidos']);
        $this->middleware('permission:ver reportes compras')->only(['compras']);
        $this->middleware('permission:ver reportes inventario')->only(['inventario', 'productosBajoStock']);
    }

    public function index()
    {
        $ventasMes = Venta::whereMonth('fecha', now()->month)
            ->whereYear('fecha', now()->year)
            ->completadas()
            ->sum('total');

        $comprasMes = Compra::whereMonth('fecha', now()->month)
            ->whereYear('fecha', now()->year)
            ->recibidas()
            ->sum('total');

        $valorizacion = $this->inventarioService->valorizacionInventario();

        return view('reportes.index', compact('ventasMes', 'comprasMes', 'valorizacion'));
    }

    public function ventas(Request $request)
    {
        $fechaDesde = $request->input('fecha_desde', now()->startOfMonth()->toDateString());
        $fechaHasta = $request->input('fecha_hasta', now()->endOfMonth()->toDateString());

        $ventas = Venta::with(['cliente', 'usuario'])
            ->whereBetween(DB::raw('DATE(fecha)'), [$fechaDesde, $fechaHasta])
            ->completadas()
            ->orderBy('fecha', 'desc')
            ->paginate(20)
            ->withQueryString();

        $totalVendido = Venta::whereBetween(DB::raw('DATE(fecha)'), [$fechaDesde, $fechaHasta])
            ->completadas()
            ->sum('total');

        $ventasPorMetodo = Venta::whereBetween(DB::raw('DATE(fecha)'), [$fechaDesde, $fechaHasta])
            ->completadas()
            ->select('metodo_pago', DB::raw('SUM(total) as total'), DB::raw('COUNT(id) as cantidad'))
            ->groupBy('metodo_pago')
            ->get();

        return view('reportes.ventas', compact('ventas', 'totalVendido', 'ventasPorMetodo', 'fechaDesde', 'fechaHasta'));
    }

    public function compras(Request $request)
    {
        $fechaDesde = $request->input('fecha_desde', now()->startOfMonth()->toDateString());
        $fechaHasta = $request->input('fecha_hasta', now()->endOfMonth()->toDateString());

        $compras = Compra::with(['proveedor', 'usuario'])
            ->whereBetween(DB::raw('DATE(fecha)'), [$fechaDesde, $fechaHasta])
            ->recibidas()
            ->orderBy('fecha', 'desc')
            ->paginate(20)
            ->withQueryString();

        $totalComprado = Compra::whereBetween(DB::raw('DATE(fecha)'), [$fechaDesde, $fechaHasta])
            ->recibidas()
            ->sum('total');

        return view('reportes.compras', compact('compras', 'totalComprado', 'fechaDesde', 'fechaHasta'));
    }

    public function inventario()
    {
        $valorizacion = $this->inventarioService->valorizacionInventario();
        return view('reportes.inventario', compact('valorizacion'));
    }

    public function productosMasVendidos(Request $request)
    {
        $fechaDesde = $request->input('fecha_desde', now()->startOfMonth()->toDateString());
        $fechaHasta = $request->input('fecha_hasta', now()->endOfMonth()->toDateString());

        $ranking = DetalleVenta::join('productos', 'detalle_venta.producto_id', '=', 'productos.id')
            ->join('ventas', 'detalle_venta.venta_id', '=', 'ventas.id')
            ->where('ventas.estado', 'completada')
            ->whereBetween(DB::raw('DATE(ventas.fecha)'), [$fechaDesde, $fechaHasta])
            ->select(
                'productos.id',
                'productos.nombre',
                'productos.principio_activo',
                DB::raw('SUM(detalle_venta.cantidad_unidades_base) as total_unidades_vendidas'),
                DB::raw('SUM(detalle_venta.subtotal) as total_ingresos')
            )
            ->groupBy('productos.id', 'productos.nombre', 'productos.principio_activo')
            ->orderByDesc('total_unidades_vendidas')
            ->take(20)
            ->get();

        return view('reportes.productos-mas-vendidos', compact('ranking', 'fechaDesde', 'fechaHasta'));
    }

    public function productosBajoStock()
    {
        $productos = $this->inventarioService->productosConStockBajo();
        return view('reportes.productos-bajo-stock', compact('productos'));
    }
}
