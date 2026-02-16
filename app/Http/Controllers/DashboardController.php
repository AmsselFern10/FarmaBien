<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Venta;
use App\Models\Compra;
use App\Models\Producto;
use App\Models\Lote;
use App\Models\MovimientoInventario;
use App\Models\User;
use App\Models\Cliente;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;



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

        // Compras (solo recibidas)
        $comprasHoy = Compra::whereDate('fecha', $hoy)
            ->where('estado', 'recibida')
            ->sum('total');

        $comprasMes = Compra::whereDate('fecha', '>=', $mesActual)
            ->where('estado', 'recibida')
            ->sum('total');

        // Productos (stock_total es accessor basado en lotes.stock_actual)
        $productos = Producto::with('lotes')->where('activo', true)->get();

        $totalProductos = $productos->count();
        $productosBajoStock = $productos->filter(fn ($p) => $p->stock_total > 0 && $p->stock_total <= $p->stock_minimo)->count();
        $productosAgotados = $productos->filter(fn ($p) => $p->stock_total === 0)->count();

        $productosAlerta = $productos
            ->filter(fn ($p) => $p->stock_total <= $p->stock_minimo)
            ->sortBy('stock_total')
            ->take(5);

        // Clientes
        $totalClientes = Cliente::count();
        $clientesNuevosHoy = Cliente::whereDate('created_at', $hoy)->count();

        // Productos más vendidos del mes (en UNIDADES BASE)
        $productosMasVendidos = DB::table('detalle_venta')
            ->join('ventas', 'detalle_venta.venta_id', '=', 'ventas.id')
            ->join('productos', 'detalle_venta.producto_id', '=', 'productos.id')
            ->whereDate('ventas.fecha', '>=', $mesActual)
            ->where('ventas.estado', 'completada')
            ->select(
                'productos.nombre',
                DB::raw('SUM(detalle_venta.cantidad_unidades_base) as total_vendido'),
                DB::raw('SUM(detalle_venta.subtotal) as total_ingresos')
            )
            ->groupBy('productos.id', 'productos.nombre')
            ->orderByDesc('total_vendido')
            ->limit(5)
            ->get();

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
    $lotesProximosVencer = Lote::query()
        ->with('producto:id,nombre')
        ->where('activo', true)
        ->where('stock_actual', '>', 0)
        ->whereDate('fecha_vencimiento', '>=', $hoy)
        ->whereDate('fecha_vencimiento', '<=', Carbon::now()->addDays(30))
        ->orderBy('fecha_vencimiento', 'asc')
        ->limit(50)
        ->get();
$productosStockBajo = Producto::query()
    ->leftJoin('lotes', function ($join) {
        $join->on('lotes.producto_id', '=', 'productos.id')
            ->where('lotes.activo', '=', 1);
    })
    ->where('productos.activo', true)
    ->whereNull('productos.deleted_at') // Es buena práctica añadirlo si usas SoftDeletes
    ->select(
        'productos.id', 
        'productos.nombre', 
        'productos.stock_minimo', 
        DB::raw('COALESCE(SUM(lotes.stock_actual), 0) as stock_total')
    )
    ->groupBy('productos.id', 'productos.nombre', 'productos.stock_minimo') // <--- Columnas agregadas aquí
    ->havingRaw('COALESCE(SUM(lotes.stock_actual), 0) <= productos.stock_minimo')
    ->orderBy('stock_total', 'asc')
    ->limit(50)
    ->get();
    $usuario = auth()->user();
    $hoyStr = now()->toDateString();

    // Ventas del día
    $ventasHoyQuery = Venta::whereDate('fecha', $hoyStr)
        ->where('user_id', $usuario->id)
        ->where('estado', 'completada');

    $totalVentasHoy = $ventasHoyQuery->sum('total');
    $cantidadVentasHoy = $ventasHoyQuery->count();

   
    $ventasMes = Venta::whereMonth('fecha', now()->month)
        ->whereYear('fecha', now()->year)
        ->where('user_id', $usuario->id)
        ->where('estado', 'completada')
        ->sum('total');

    
    $ultimaVenta = Venta::where('user_id', $usuario->id)
        ->where('estado', 'completada')
        ->latest('created_at')
        ->first();


    $ventasRecientes= Venta::where('user_id', $usuario->id)
        ->where('estado', 'completada')
        ->latest('created_at')
        ->take(5)
        ->get();

    // Productos más vendidos hoy
    $productosMasVendidos = DB::table('detalle_venta')
        ->join('ventas', 'detalle_venta.venta_id', '=', 'ventas.id')
        ->join('productos', 'detalle_venta.producto_id', '=', 'productos.id')
        ->whereDate('ventas.fecha', $hoyStr)
        ->where('ventas.user_id', $usuario->id)
        ->where('ventas.estado', 'completada')
        ->select(
            'productos.nombre',
            DB::raw('SUM(detalle_venta.cantidad_unidades_base) as total_vendido')
        )
        ->groupBy('productos.id', 'productos.nombre')
        ->orderByDesc('total_vendido')
        ->limit(15)
        ->get();

   return view('dashboard.cajero', compact(
    'totalVentasHoy',
    'cantidadVentasHoy',
    'ventasMes',
    'ultimaVenta',
    'productosMasVendidos',
    'lotesProximosVencer',
    'productosStockBajo',
    'ventasRecientes'
));

}


    /**
     * Dashboard para Encargado de Inventario
     */
    private function dashboardInventario()
    {
        $hoy = Carbon::today();
        $mesActual = Carbon::now()->startOfMonth();

        // Compras del mes (solo recibidas)
        $comprasMesQuery = Compra::where('estado', 'recibida')
            ->whereDate('fecha', '>=', $mesActual);

        $comprasMes = $comprasMesQuery->sum('total');
        $cantidadComprasMes = $comprasMesQuery->count();

        $comprasRecientes = Compra::with('proveedor:id,nombre')
            ->where('estado', 'recibida')
            ->latest('fecha')
            ->take(5)
            ->get();

      $productosStockBajo = Producto::query()
    ->leftJoin('lotes', function ($join) {
        $join->on('lotes.producto_id', '=', 'productos.id')
            ->where('lotes.activo', '=', 1);
    })
    ->where('productos.activo', true)
    // Agregamos nombre y stock_minimo al agrupamiento
    ->groupBy('productos.id', 'productos.nombre', 'productos.stock_minimo')
    ->select(
        'productos.id',
        'productos.nombre',
        'productos.stock_minimo',
        DB::raw('COALESCE(SUM(lotes.stock_actual), 0) as stock_total')
    )
    ->havingRaw('COALESCE(SUM(lotes.stock_actual), 0) <= productos.stock_minimo')
    ->orderBy('stock_total', 'asc')
    ->get();

        // Lotes vencidos / próximos a vencer (con stock)
        $lotesVencidos = Lote::with('producto:id,nombre')
            ->where('activo', true)
            ->where('stock_actual', '>', 0)
            ->whereDate('fecha_vencimiento', '<', $hoy)
            ->orderBy('fecha_vencimiento', 'asc')
            ->get();

        $lotesProximosVencer = Lote::with('producto:id,nombre')
            ->where('activo', true)
            ->where('stock_actual', '>', 0)
            ->whereDate('fecha_vencimiento', '>=', $hoy)
            ->whereDate('fecha_vencimiento', '<=', Carbon::now()->addDays(30))
            ->orderBy('fecha_vencimiento', 'asc')
            ->get();

        // Movimientos recientes (kardex)
        $movimientosRecientes = MovimientoInventario::with([
                'producto:id,nombre',
                'lote:id,numero_lote',
                'usuario:id,name',
            ])
            ->latest('fecha_movimiento')
            ->take(10)
            ->get();

        // Valorización (basada en lotes)
        $cantidadTotalUnidades = (int) Lote::where('activo', true)->sum('stock_actual');

        $valorTotal = (float) Lote::where('activo', true)
            ->select(DB::raw('COALESCE(SUM(stock_actual * precio_compra),0) as v'))
            ->value('v');

        $totalLotesActivos = (int) Lote::where('activo', true)
            ->where('stock_actual', '>', 0)
            ->whereNull('bloqueado_at')
            ->whereDate('fecha_vencimiento', '>=', $hoy)
            ->count();

        $valorizacion = [
            'valor_total' => $valorTotal,
            'cantidad_total_unidades' => $cantidadTotalUnidades,
            'total_lotes_activos' => $totalLotesActivos,
        ];

        // Resumen por categoría (stock_total y cantidad de productos)
        $resumenCategorias = DB::table('categorias')
            ->join('productos', 'productos.categoria_id', '=', 'categorias.id')
            ->leftJoin('lotes', function ($join) {
                $join->on('lotes.producto_id', '=', 'productos.id')
                    ->where('lotes.activo', '=', 1);
            })
            ->whereNull('productos.deleted_at')
            ->where('productos.activo', true)
            ->groupBy('categorias.id', 'categorias.nombre')
            ->select(
                DB::raw('categorias.nombre as categoria'),
                DB::raw('COUNT(DISTINCT productos.id) as total_productos'),
                DB::raw('COALESCE(SUM(lotes.stock_actual),0) as stock_total')
            )
            ->orderByDesc('stock_total')
            ->get();

        return view('dashboard.inventario', compact(
            'comprasMes',
            'cantidadComprasMes',
            'comprasRecientes',
            'productosStockBajo',
            'lotesVencidos',
            'lotesProximosVencer',
            'movimientosRecientes',
            'valorizacion',
            'resumenCategorias'
        ));
    }
}