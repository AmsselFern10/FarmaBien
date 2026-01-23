<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Venta;
use App\Models\Compra;
use App\Models\Producto;
use App\Models\User;
use App\Models\Cliente;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Muestra el dashboard según el rol del usuario
     */
    public function index()
    { /** @var \App\Models\User $user */
    $user = auth()->user();
      
        
        if ($user->hasRole('Admin')) {
            return $this->dashboardAdmin();
        }
        
        if ($user->hasRole('Cajero')) {
            return $this->dashboardCajero();
        }
        
        if ($user->hasRole('Inventario')) {
            return $this->dashboardInventario();
        }
        
        // Dashboard genérico
        return view('dashboard');
    }

    /**
     * Dashboard para Administradores
     */
    private function dashboardAdmin()
    {
        $hoy = Carbon::today();
        $mesActual = Carbon::now()->startOfMonth();
        
        // Ventas
        $ventasHoy = Venta::whereDate('fecha', $hoy)
            ->where('estado', 'completada')
            ->sum('total');
            
        $ventasMes = Venta::whereDate('fecha', '>=', $mesActual)
            ->where('estado', 'completada')
            ->sum('total');
            
        $cantidadVentasHoy = Venta::whereDate('fecha', $hoy)
            ->where('estado', 'completada')
            ->count();

        // Compras
        $comprasHoy = Compra::whereDate('fecha', $hoy)
            ->where('estado', 'completada')
            ->sum('total');
            
        $comprasMes = Compra::whereDate('fecha', '>=', $mesActual)
            ->where('estado', 'completada')
            ->sum('total');


         $productos = Producto::with('lotes')
    ->where('activo', true)
    ->get();

$totalProductos = $productos->count();

$productosBajoStock = $productos->filter(fn($p) => $p->stock_total <= $p->stock_minimo)->count();
$productosAgotados = $productos->filter(fn($p) => $p->stock_total === 0)->count();

$productosAlerta = $productos->filter(fn($p) => $p->stock_total <= $p->stock_minimo)
    ->sortBy('stock_total')
    ->take(5);

        // Clientes
        $totalClientes = Cliente::count();
        $clientesNuevosHoy = Cliente::whereDate('created_at', $hoy)->count();

        // Productos más vendidos del mes
     $productosMasVendidos = DB::table('detalle_venta')
    ->join('ventas', 'detalle_venta.venta_id', '=', 'ventas.id')
    ->join('productos', 'detalle_venta.producto_id', '=', 'productos.id')
    ->whereDate('ventas.fecha', '>=', $mesActual)
    ->where('ventas.estado', 'completada')
    ->select(
        'productos.nombre',
        DB::raw('SUM(detalle_venta.cantidad) as total_vendido'),
        DB::raw('SUM(detalle_venta.subtotal) as total_ingresos')
    )
    ->groupBy('productos.id', 'productos.nombre')
    ->orderBy('total_vendido', 'desc')
    ->limit(5)
    ->get();

       $productos = Producto::with('lotes')
    ->where('activo', true)
    ->get();

// Total productos con alerta de stock
$productosAlerta = $productos->filter(fn($p) => $p->lotes->sum('stock') <= $p->stock_minimo)
    ->sortBy('stock_total') // opcional, para mostrar los más bajos primero
    ->take(5);

        // Ventas por día (últimos 7 días)
        $ventasPorDia = Venta::whereDate('fecha', '>=', Carbon::now()->subDays(7))
            ->where('estado', 'completada')
            ->select(
                DB::raw('DATE(fecha) as dia'),
                DB::raw('COUNT(*) as cantidad'),
                DB::raw('SUM(total) as total')
            )
            ->groupBy('dia')
            ->orderBy('dia', 'asc')
            ->get();

        // Usuarios activos
        $totalUsuarios = User::count();

        return view('dashboard.admin', compact(
            'ventasHoy',
            'ventasMes',
            'cantidadVentasHoy',
            'comprasHoy',
            'comprasMes',
            'totalProductos',
            'productosBajoStock',
            'productosAgotados',
            'totalClientes',
            'clientesNuevosHoy',
            'productosMasVendidos',
            'productosAlerta',
            'ventasPorDia',
            'totalUsuarios'
        ));
    }

    /**
     * Dashboard para Cajeros
     */
    private function dashboardCajero()
    {
        $hoy = Carbon::today();
        $usuario = auth()->user();
        
        // Ventas del día del cajero
        $misVentasHoy = Venta::whereDate('fecha', $hoy)
            ->where('usuario_id', $usuario->id)
            ->where('estado', 'completada')
            ->get();
            
        $totalVentasHoy = $misVentasHoy->sum('total');
        $cantidadVentasHoy = $misVentasHoy->count();
        
        // Última venta
        $ultimaVenta = Venta::where('usuario_id', $usuario->id)
            ->latest('created_at')
            ->first();

        // Productos más vendidos por el cajero hoy
        $productosMasVendidos = DB::table('detalle_ventas')
            ->join('ventas', 'detalle_ventas.venta_id', '=', 'ventas.id')
            ->join('productos', 'detalle_ventas.producto_id', '=', 'productos.id')
            ->whereDate('ventas.fecha', $hoy)
            ->where('ventas.usuario_id', $usuario->id)
            ->where('ventas.estado', 'completada')
            ->select(
                'productos.nombre',
                DB::raw('SUM(detalle_ventas.cantidad) as total_vendido')
            )
            ->groupBy('productos.id', 'productos.nombre')
            ->orderBy('total_vendido', 'desc')
            ->limit(5)
            ->get();

        return view('dashboard.cajero', compact(
            'misVentasHoy',
            'totalVentasHoy',
            'cantidadVentasHoy',
            'ultimaVenta',
            'productosMasVendidos'
        ));
    }

    /**
     * Dashboard para Encargado de Inventario
     */
    private function dashboardInventario()
    {
        $hoy = Carbon::today();
        
        // Productos con stock bajo
        $productosBajoStock = Producto::whereRaw('stock_total <= stock_minimo')
            ->where('activo', true)
            ->orderBy('stock_total', 'asc')
            ->get();

        // Productos agotados
        $productosAgotados = Producto::where('stock_total', 0)
            ->where('activo', true)
            ->get();

        // Lotes próximos a vencer (30 días)
        $lotesProximosVencer = DB::table('lotes')
            ->join('productos', 'lotes.producto_id', '=', 'productos.id')
            ->where('lotes.estado', 'disponible')
            ->whereDate('lotes.fecha_vencimiento', '<=', Carbon::now()->addDays(30))
            ->select('lotes.*', 'productos.nombre as producto_nombre')
            ->orderBy('lotes.fecha_vencimiento', 'asc')
            ->get();

        // Movimientos de inventario del día
        $movimientosHoy = DB::table('movimientos_inventario')
            ->whereDate('created_at', $hoy)
            ->count();

        // Compras realizadas hoy
        $comprasHoy = Compra::whereDate('fecha', $hoy)
            ->where('estado', 'completada')
            ->get();

        // Valor total del inventario
        $valorInventario = Producto::where('activo', true)
            ->get()
            ->sum(function ($producto) {
                return $producto->stock_total * $producto->precio_compra;
            });

        // Total de productos
        $totalProductos = Producto::where('activo', true)->count();

        return view('dashboard.inventario', compact(
            'productosBajoStock',
            'productosAgotados',
            'lotesProximosVencer',
            'movimientosHoy',
            'comprasHoy',
            'valorInventario',
            'totalProductos'
        ));
    }
}