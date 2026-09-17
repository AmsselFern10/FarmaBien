<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\Compra;
use App\Models\Producto;
use App\Models\Receta;
use App\Services\InventarioService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected InventarioService $inventarioService;

    public function __construct(InventarioService $inventarioService)
    {
        $this->inventarioService = $inventarioService;
    }

    public function index(Request $request)
    {
        // 1. Métricas Principales del Día y Mes
        $ventasHoy = Venta::whereDate('fecha', today())->completadas()->sum('total');
        $cantidadVentasHoy = Venta::whereDate('fecha', today())->completadas()->count();
        $comprasMes = Compra::whereMonth('fecha', now()->month)->recibidas()->sum('total');
        
        // 2. Alertas Clínicas y de Inventario
        $productosBajoStockCount = Producto::activos()->bajoStock()->count();
        $lotesPorVencerCount = $this->inventarioService->lotesProximosVencer(30)->count();
        $recetasPendientesCount = Receta::pendientes()->vigentes()->count();

        // 3. Tablas de actividad reciente
        $ultimasVentas = Venta::with(['cliente', 'usuario'])
            ->completadas()
            ->orderBy('fecha', 'desc')
            ->take(5)
            ->get();

        $ultimasCompras = Compra::with(['proveedor', 'usuario'])
            ->recibidas()
            ->orderBy('fecha', 'desc')
            ->take(5)
            ->get();

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
