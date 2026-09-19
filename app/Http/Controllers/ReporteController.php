<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\Compra;
use App\Models\Producto;
use App\Models\DetalleVenta;
use App\Models\User;
use App\Services\InventarioService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    /**
     * Vista principal del centro de reportes gerenciales
     */
    public function index()
    {
        // 1. Resumen mensual y diario
        $ventasMes = Venta::whereMonth('fecha', now()->month)
            ->whereYear('fecha', now()->year)
            ->completadas()
            ->sum('total');

        $cantidadVentasMes = Venta::whereMonth('fecha', now()->month)
            ->whereYear('fecha', now()->year)
            ->completadas()
            ->count();

        $ventasHoy = Venta::whereDate('fecha', today())
            ->completadas()
            ->sum('total');

        $comprasMes = Compra::whereMonth('fecha', now()->month)
            ->whereYear('fecha', now()->year)
            ->recibidas()
            ->sum('total');

        $valorizacion = $this->inventarioService->valorizacionInventario();

        // 2. Top 5 productos del mes en curso
        $topProductosMes = DetalleVenta::join('productos', 'detalle_venta.producto_id', '=', 'productos.id')
            ->join('ventas', 'detalle_venta.venta_id', '=', 'ventas.id')
            ->where('ventas.estado', 'completada')
            ->whereMonth('ventas.fecha', now()->month)
            ->whereYear('ventas.fecha', now()->year)
            ->select(
                'productos.id',
                'productos.nombre',
                DB::raw('SUM(detalle_venta.cantidad_unidades_base) as total_unidades'),
                DB::raw('SUM(detalle_venta.subtotal) as total_monto')
            )
            ->groupBy('productos.id', 'productos.nombre')
            ->orderByDesc('total_unidades')
            ->take(5)
            ->get();

        // 3. Distribución por método de pago del mes
        $metodosMes = Venta::whereMonth('fecha', now()->month)
            ->whereYear('fecha', now()->year)
            ->completadas()
            ->select('metodo_pago', DB::raw('SUM(total) as total'), DB::raw('COUNT(id) as cantidad'))
            ->groupBy('metodo_pago')
            ->get();

        return view('reportes.index', compact(
            'ventasMes',
            'cantidadVentasMes',
            'ventasHoy',
            'comprasMes',
            'valorizacion',
            'topProductosMes',
            'metodosMes'
        ));
    }

    /**
     * Reporte detallado de ventas e ingresos con filtros y exportación
     */
    public function ventas(Request $request)
    {
        $fechaDesde = $request->input('fecha_desde', now()->startOfMonth()->toDateString());
        $fechaHasta = $request->input('fecha_hasta', now()->endOfMonth()->toDateString());
        $metodoPago = $request->input('metodo_pago');
        $cajeroId = $request->input('cajero_id');

        // Construir consulta base
        $query = Venta::with(['cliente', 'usuario'])
            ->completadas()
            ->whereBetween(DB::raw('DATE(fecha)'), [$fechaDesde, $fechaHasta]);

        if (!empty($metodoPago)) {
            $query->where('metodo_pago', $metodoPago);
        }

        if (!empty($cajeroId)) {
            $query->where('usuario_id', $cajeroId);
        }

        // Si se solicita exportación en CSV
        if ($request->input('export') === 'csv') {
            return $this->exportarVentasCSV($query->get(), $fechaDesde, $fechaHasta);
        }

        // Métricas del período filtrado
        $totalVendido = (clone $query)->sum('total');
        $cantidadVentas = (clone $query)->count();
        $ticketPromedio = $cantidadVentas > 0 ? ($totalVendido / $cantidadVentas) : 0;

        // Desglose por método de pago en el período
        $ventasPorMetodo = (clone $query)
            ->select('metodo_pago', DB::raw('SUM(total) as total'), DB::raw('COUNT(id) as cantidad'))
            ->groupBy('metodo_pago')
            ->get();

        // Top 10 medicamentos más vendidos en el período
        $topProductos = DetalleVenta::join('productos', 'detalle_venta.producto_id', '=', 'productos.id')
            ->join('ventas', 'detalle_venta.venta_id', '=', 'ventas.id')
            ->where('ventas.estado', 'completada')
            ->whereBetween(DB::raw('DATE(ventas.fecha)'), [$fechaDesde, $fechaHasta])
            ->when(!empty($metodoPago), fn($q) => $q->where('ventas.metodo_pago', $metodoPago))
            ->when(!empty($cajeroId), fn($q) => $q->where('ventas.usuario_id', $cajeroId))
            ->select(
                'productos.id',
                'productos.nombre',
                'productos.principio_activo',
                DB::raw('SUM(detalle_venta.cantidad_unidades_base) as total_unidades'),
                DB::raw('SUM(detalle_venta.subtotal) as total_ingreso')
            )
            ->groupBy('productos.id', 'productos.nombre', 'productos.principio_activo')
            ->orderByDesc('total_unidades')
            ->take(10)
            ->get();

        // Lista paginada para la tabla
        $ventas = $query->orderBy('fecha', 'desc')->paginate(25)->withQueryString();

        // Lista de cajeros/usuarios para el filtro
        $cajeros = User::whereHas('ventas')->orderBy('name')->get(['id', 'name']);

        return view('reportes.ventas', compact(
            'ventas',
            'totalVendido',
            'cantidadVentas',
            'ticketPromedio',
            'ventasPorMetodo',
            'topProductos',
            'fechaDesde',
            'fechaHasta',
            'metodoPago',
            'cajeroId',
            'cajeros'
        ));
    }

    /**
     * Exportación de Ventas a formato CSV con codificación UTF-8 BOM
     */
    protected function exportarVentasCSV($ventas, $desde, $hasta): StreamedResponse
    {
        $filename = "reporte_ventas_{$desde}_al_{$hasta}.csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($ventas) {
            $handle = fopen('php://output', 'w');
            // Agregar BOM UTF-8 para visualización correcta en Excel
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Encabezados
            fputcsv($handle, [
                'N° Ticket',
                'Fecha y Hora',
                'Cliente',
                'Documento Cliente',
                'Cajero / Usuario',
                'Método de Pago',
                'Estado',
                'Total ($)'
            ], ';');

            foreach ($ventas as $v) {
                fputcsv($handle, [
                    $v->id,
                    $v->fecha ? \Carbon\Carbon::parse($v->fecha)->format('d/m/Y H:i') : $v->created_at->format('d/m/Y H:i'),
                    $v->cliente ? $v->cliente->nombre : 'Público General',
                    $v->cliente ? ($v->cliente->identificacion ?? 'S/N') : 'N/A',
                    $v->usuario->name ?? 'Sistema',
                    ucfirst($v->metodo_pago),
                    ucfirst($v->estado),
                    number_format($v->total, 2, '.', '')
                ], ';');
            }

            fclose($handle);
        }, 200, $headers);
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
