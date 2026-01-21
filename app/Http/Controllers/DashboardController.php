<?php

namespace App\Http\Controllers;

use App\Services\VentaService;
use App\Services\CompraService;
use App\Services\InventarioService;
use App\Models\Venta;
use App\Models\Compra;
use App\Models\Producto;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    protected $ventaService;
    protected $compraService;
    protected $inventarioService;

    public function __construct(
        VentaService $ventaService,
        CompraService $compraService,
        InventarioService $inventarioService
    ) {
        $this->ventaService = $ventaService;
        $this->compraService = $compraService;
        $this->inventarioService = $inventarioService;

        $this->middleware('permission:ver dashboard');
    }

    /**
     * Dashboard principal.
     */
    public function index()
    {
        $user = auth()->user();

        // Estadísticas según el rol
        if ($user->hasRole('Cajero')) {
            return $this->dashboardCajero();
        } elseif ($user->hasRole('Inventario')) {
            return $this->dashboardInventario();
        } else {
            return $this->dashboardAdmin();
        }
    }

    /**
     * Dashboard para Administrador.
     */
    protected function dashboardAdmin()
    {
        // Ventas del día
        $ventasHoy = $this->ventaService->totalVentasDelDia();
        $cantidadVentasHoy = Venta::whereDate('fecha', today())
            ->completadas()
            ->count();

        // Ventas del mes
        $ventasMes = Venta::whereMonth('fecha', now()->month)
            ->whereYear('fecha', now()->year)
            ->completadas()
            ->sum('total');

        // Compras del mes
        $comprasMes = $this->compraService->totalComprasDelMes();

        // Productos con stock bajo
        $productosStockBajo = $this->inventarioService->productosConStockBajo()->count();

        // Lotes próximos a vencer
        $lotesProximosVencer = $this->inventarioService->lotesProximosVencer(30)->count();

        // Lotes vencidos
        $lotesVencidos = $this->inventarioService->lotesVencidos()->count();

        // Total productos activos
        $totalProductos = Producto::activos()->count();

        // Total clientes
        $totalClientes = Cliente::activos()->count();

        // Ventas recientes
        $ventasRecientes = Venta::with(['cliente', 'usuario'])
            ->completadas()
            ->orderBy('fecha', 'desc')
            ->limit(10)
            ->get();

        // Compras recientes
        $comprasRecientes = $this->compraService->comprasRecientes(10);

        // Productos más vendidos del mes
        $productosMasVendidos = DB::table('detalle_venta')
            ->join('ventas', 'detalle_venta.venta_id', '=', 'ventas.id')
            ->join('productos', 'detalle_venta.producto_id', '=', 'productos.id')
            ->whereMonth('ventas.fecha', now()->month)
            ->whereYear('ventas.fecha', now()->year)
            ->where('ventas.estado', 'completada')
            ->select(
                'productos.id',
                'productos.nombre',
                DB::raw('SUM(detalle_venta.cantidad) as total_vendido'),
                DB::raw('SUM(detalle_venta.subtotal) as total_ingresos')
            )
            ->groupBy('productos.id', 'productos.nombre')
            ->orderBy('total_vendido', 'desc')
            ->limit(10)
            ->get();

        // Gráfico de ventas por día (últimos 7 días)
        $ventasPorDia = Venta::selectRaw('DATE(fecha) as dia, SUM(total) as total')
            ->completadas()
            ->whereBetween('fecha', [now()->subDays(6)->startOfDay(), now()->endOfDay()])
            ->groupBy('dia')
            ->orderBy('dia')
            ->get();

        return view('dashboard.admin', compact(
            'ventasHoy',
            'cantidadVentasHoy',
            'ventasMes',
            'comprasMes',
            'productosStockBajo',
            'lotesProximosVencer',
            'lotesVencidos',
            'totalProductos',
            'totalClientes',
            'ventasRecientes',
            'comprasRecientes',
            'productosMasVendidos',
            'ventasPorDia'
        ));
    }

    /**
     * Dashboard para Cajero.
     */
    protected function dashboardCajero()
    {
        $userId = auth()->id();

        // Ventas del cajero hoy
        $ventasHoy = Venta::whereDate('fecha', today())
            ->where('user_id', $userId)
            ->completadas()
            ->sum('total');

        $cantidadVentasHoy = Venta::whereDate('fecha', today())
            ->where('user_id', $userId)
            ->completadas()
            ->count();

        // Ventas del mes del cajero
        $ventasMes = Venta::whereMonth('fecha', now()->month)
            ->whereYear('fecha', now()->year)
            ->where('user_id', $userId)
            ->completadas()
            ->sum('total');

        // Mis ventas recientes
        $ventasRecientes = Venta::with(['cliente'])
            ->where('user_id', $userId)
            ->completadas()
            ->orderBy('fecha', 'desc')
            ->limit(10)
            ->get();

        // Productos próximos a vencer (alerta para no vender)
        $lotesProximosVencer = $this->inventarioService->lotesProximosVencer(15)->take(10);

        // Productos con stock bajo
        $productosStockBajo = $this->inventarioService->productosConStockBajo()->take(10);

        return view('dashboard.cajero', compact(
            'ventasHoy',
            'cantidadVentasHoy',
            'ventasMes',
            'ventasRecientes',
            'lotesProximosVencer',
            'productosStockBajo'
        ));
    }

    /**
     * Dashboard para Inventario.
     */
    protected function dashboardInventario()
    {
        // Compras del mes
        $comprasMes = $this->compraService->totalComprasDelMes();
        $cantidadComprasMes = Compra::whereMonth('fecha', now()->month)
            ->whereYear('fecha', now()->year)
            ->recibidas()
            ->count();

        // Productos con stock bajo
        $productosStockBajo = $this->inventarioService->productosConStockBajo();

        // Lotes próximos a vencer
        $lotesProximosVencer = $this->inventarioService->lotesProximosVencer(30);

        // Lotes vencidos
        $lotesVencidos = $this->inventarioService->lotesVencidos();

        // Valorización del inventario
        $valorizacion = $this->inventarioService->valorizacionInventario();

        // Resumen por categoría
        $resumenCategorias = $this->inventarioService->resumenPorCategoria();

        // Compras recientes
        $comprasRecientes = $this->compraService->comprasRecientes(10);

        // Movimientos recientes
        $movimientosRecientes = \App\Models\MovimientoInventario::with([
            'producto',
            'lote',
            'usuario'
        ])
        ->orderBy('fecha_movimiento', 'desc')
        ->limit(15)
        ->get();

        return view('dashboard.inventario', compact(
            'comprasMes',
            'cantidadComprasMes',
            'productosStockBajo',
            'lotesProximosVencer',
            'lotesVencidos',
            'valorizacion',
            'resumenCategorias',
            'comprasRecientes',
            'movimientosRecientes'
        ));
    }
}
    