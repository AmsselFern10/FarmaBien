<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\Compra;
use App\Models\Producto;
use App\Models\Receta;
use App\Services\InventarioService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    protected InventarioService $inventarioService;

    public function __construct(InventarioService $inventarioService)
    {
        $this->inventarioService = $inventarioService;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $today = today()->toDateString();
        $currentMonth = now()->month;
        $currentYear = now()->year;

        // 1. Métricas Principales de Ventas (Control RBAC)
        $ventasHoy = 0;
        $cantidadVentasHoy = 0;
        $ultimasVentas = collect();

        if ($user->can('ver ventas') || $user->can('ver reportes ventas')) {
            $cacheKeyVentas = "dashboard_ventas_global_{$today}";
            $ventasMetrics = Cache::remember($cacheKeyVentas, 30, function () use ($today) {
                return [
                    'total' => (float) Venta::whereDate('fecha', $today)->completadas()->sum('total'),
                    'count' => (int) Venta::whereDate('fecha', $today)->completadas()->count(),
                ];
            });

            $ventasHoy = $ventasMetrics['total'];
            $cantidadVentasHoy = $ventasMetrics['count'];

            $ultimasVentas = Venta::select(['id', 'numero_comprobante', 'tipo_comprobante', 'fecha', 'total', 'metodo_pago', 'cliente_id', 'user_id', 'estado', 'created_at'])
                ->with([
                    'cliente:id,nombre',
                    'usuario:id,name',
                ])
                ->completadas()
                ->orderBy('fecha', 'desc')
                ->take(5)
                ->get();
        } elseif ($user->can('ver ventas propias')) {
            $cacheKeyVentas = "dashboard_ventas_user_{$user->id}_{$today}";
            $ventasMetrics = Cache::remember($cacheKeyVentas, 30, function () use ($user, $today) {
                return [
                    'total' => (float) Venta::where('user_id', $user->id)->whereDate('fecha', $today)->completadas()->sum('total'),
                    'count' => (int) Venta::where('user_id', $user->id)->whereDate('fecha', $today)->completadas()->count(),
                ];
            });

            $ventasHoy = $ventasMetrics['total'];
            $cantidadVentasHoy = $ventasMetrics['count'];

            $ultimasVentas = Venta::select(['id', 'numero_comprobante', 'tipo_comprobante', 'fecha', 'total', 'metodo_pago', 'cliente_id', 'user_id', 'estado', 'created_at'])
                ->with([
                    'cliente:id,nombre',
                    'usuario:id,name',
                ])
                ->where('user_id', $user->id)
                ->completadas()
                ->orderBy('fecha', 'desc')
                ->take(5)
                ->get();
        }

        // 2. Métricas de Compras (Control RBAC)
        $comprasMes = 0;
        $ultimasCompras = collect();

        if ($user->can('ver compras') || $user->can('ver reportes compras')) {
            $comprasMes = Cache::remember("dashboard_compras_mes_{$currentYear}_{$currentMonth}", 60, function () use ($currentMonth, $currentYear) {
                return (float) Compra::whereYear('fecha', $currentYear)
                    ->whereMonth('fecha', $currentMonth)
                    ->recibidas()
                    ->sum('total');
            });

            $ultimasCompras = Compra::select(['id', 'numero_factura', 'tipo_comprobante', 'fecha', 'total', 'proveedor_id', 'user_id', 'estado', 'created_at'])
                ->with([
                    'proveedor:id,razon_social,nombre',
                    'usuario:id,name',
                ])
                ->recibidas()
                ->orderBy('fecha', 'desc')
                ->take(5)
                ->get();
        }
        
        // 3. Alertas Clínicas y de Inventario (Control RBAC y Cache TTL de 60s)
        $productosBajoStockCount = 0;
        if ($user->can('ver alertas stock bajo') || $user->can('ver productos')) {
            $productosBajoStockCount = Cache::remember('dashboard_stock_critico_count', 60, function () {
                return (int) Producto::activos()->bajoStock()->count();
            });
        }

        $lotesPorVencerCount = 0;
        if ($user->can('ver alertas vencimientos') || $user->can('ver lotes')) {
            $lotesPorVencerCount = Cache::remember('dashboard_lotes_por_vencer_count', 60, function () {
                return (int) $this->inventarioService->lotesProximosVencer(30)->count();
            });
        }

        $recetasPendientesCount = 0;
        if ($user->can('ver recetas') || $user->can('validar recetas') || $user->can('dispensar recetas')) {
            $recetasPendientesCount = Cache::remember('dashboard_recetas_pendientes_count', 30, function () {
                return (int) Receta::pendientes()->vigentes()->count();
            });
        }

        return view('dashboard', compact(
            'ventasHoy',
            'cantidadVentasHoy',
            'comprasMes',
            'productosBajoStockCount',
            'lotesPorVencerCount',
            'recetasPendientesCount',
            'ultimasVentas',
            'ultimasCompras'
        ));
    }
}
