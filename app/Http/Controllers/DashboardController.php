<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\Compra;
use App\Models\Producto;
use App\Models\Receta;
use App\Services\InventarioService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        // 1. Métricas Principales de Ventas (Control RBAC)
        $ventasHoy = 0;
        $cantidadVentasHoy = 0;
        $ultimasVentas = collect();

        if ($user->can('ver ventas') || $user->can('ver reportes ventas')) {
            $ventasHoy = Venta::whereDate('fecha', today())->completadas()->sum('total');
            $cantidadVentasHoy = Venta::whereDate('fecha', today())->completadas()->count();
            $ultimasVentas = Venta::with(['cliente', 'usuario'])
                ->completadas()
                ->orderBy('fecha', 'desc')
                ->take(5)
                ->get();
        } elseif ($user->can('ver ventas propias')) {
            $ventasHoy = Venta::where('usuario_id', $user->id)->whereDate('fecha', today())->completadas()->sum('total');
            $cantidadVentasHoy = Venta::where('usuario_id', $user->id)->whereDate('fecha', today())->completadas()->count();
            $ultimasVentas = Venta::with(['cliente', 'usuario'])
                ->where('usuario_id', $user->id)
                ->completadas()
                ->orderBy('fecha', 'desc')
                ->take(5)
                ->get();
        }

        // 2. Métricas de Compras (Control RBAC)
        $comprasMes = 0;
        $ultimasCompras = collect();

        if ($user->can('ver compras') || $user->can('ver reportes compras')) {
            $comprasMes = Compra::whereMonth('fecha', now()->month)->recibidas()->sum('total');
            $ultimasCompras = Compra::with(['proveedor', 'usuario'])
                ->recibidas()
                ->orderBy('fecha', 'desc')
                ->take(5)
                ->get();
        }
        
        // 3. Alertas Clínicas y de Inventario (Control RBAC)
        $productosBajoStockCount = 0;
        if ($user->can('ver alertas stock bajo') || $user->can('ver productos')) {
            $productosBajoStockCount = Producto::activos()->bajoStock()->count();
        }

        $lotesPorVencerCount = 0;
        if ($user->can('ver alertas vencimientos') || $user->can('ver lotes')) {
            $lotesPorVencerCount = $this->inventarioService->lotesProximosVencer(30)->count();
        }

        $recetasPendientesCount = 0;
        if ($user->can('ver recetas') || $user->can('validar recetas') || $user->can('dispensar recetas')) {
            $recetasPendientesCount = Receta::pendientes()->vigentes()->count();
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
