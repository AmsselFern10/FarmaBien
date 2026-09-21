<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\Compra;
use App\Models\Producto;
use App\Models\Lote;
use App\Models\Categoria;
use App\Models\Laboratorio;
use App\Models\DetalleVenta;
use App\Models\Cliente;
use App\Models\Receta;
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
        $this->middleware(function ($request, $next) {
            $user = $request->user();
            if ($user && $user->canAny(['ver reportes ventas', 'ver reportes inventario', 'ver reportes compras'])) {
                return $next($request);
            }
            abort(403, 'No tienes permisos para ver los reportes gerenciales.');
        })->only(['index']);
        $this->middleware('permission:ver reportes ventas')->only(['ventas', 'productosMasVendidos', 'clientes']);
        $this->middleware('permission:ver reportes compras')->only(['compras', 'recetas']);
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

        // 4. Métricas de caducidad e inventario para tarjeta de resumen
        $lotesVencidosCount = Lote::activos()->vencidos()->where('stock_actual', '>', 0)->count();
        $lotesCriticosCount = Lote::activos()->where('stock_actual', '>', 0)->whereBetween('fecha_vencimiento', [now()->toDateString(), now()->addDays(30)->toDateString()])->count();
        $productosBajoStockCount = Producto::activos()->bajoStock()->count();

        return view('reportes.index', compact(
            'ventasMes',
            'cantidadVentasMes',
            'ventasHoy',
            'comprasMes',
            'valorizacion',
            'topProductosMes',
            'metodosMes',
            'lotesVencidosCount',
            'lotesCriticosCount',
            'productosBajoStockCount'
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
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

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

    /**
     * Reporte Gerencial de Control de Inventario, Valorización y Caducidad
     */
    public function inventario(Request $request)
    {
        $buscar = trim($request->input('buscar', ''));
        $categoriaId = $request->input('categoria_id');
        $laboratorioId = $request->input('laboratorio_id');
        $estadoVencimiento = $request->input('estado_vencimiento', 'todos');
        $estadoStock = $request->input('estado_stock', 'todos');

        // 1. Semáforo Global de Caducidad (para todos los lotes activos con stock)
        $today = now()->toDateString();
        $semVencidos = Lote::activos()->where('stock_actual', '>', 0)->where('fecha_vencimiento', '<=', $today)->count();
        $semCritico30 = Lote::activos()->where('stock_actual', '>', 0)->whereBetween('fecha_vencimiento', [now()->addDay()->toDateString(), now()->addDays(30)->toDateString()])->count();
        $semAlerta60 = Lote::activos()->where('stock_actual', '>', 0)->whereBetween('fecha_vencimiento', [now()->addDays(31)->toDateString(), now()->addDays(60)->toDateString()])->count();
        $semPreventivo90 = Lote::activos()->where('stock_actual', '>', 0)->whereBetween('fecha_vencimiento', [now()->addDays(61)->toDateString(), now()->addDays(90)->toDateString()])->count();
        $semVigentes = Lote::activos()->where('stock_actual', '>', 0)->where('fecha_vencimiento', '>', now()->addDays(90)->toDateString())->count();

        // 2. Consulta de Lotes filtrada
        $query = Lote::with(['producto.categoria', 'producto.laboratorio', 'proveedor'])
            ->where('activo', true);

        if (!empty($buscar)) {
            $query->where(function ($q) use ($buscar) {
                $q->where('numero_lote', 'like', "%{$buscar}%")
                  ->orWhereHas('producto', function ($pq) use ($buscar) {
                      $pq->where('nombre', 'like', "%{$buscar}%")
                         ->orWhere('codigo_barra', 'like', "%{$buscar}%")
                         ->orWhere('principio_activo', 'like', "%{$buscar}%");
                  });
            });
        }

        if (!empty($categoriaId)) {
            $query->whereHas('producto', fn($q) => $q->where('categoria_id', $categoriaId));
        }

        if (!empty($laboratorioId)) {
            $query->whereHas('producto', fn($q) => $q->where('laboratorio_id', $laboratorioId));
        }

        // Filtro por Estado de Vencimiento
        if ($estadoVencimiento === 'vencidos') {
            $query->where('fecha_vencimiento', '<=', $today);
        } elseif ($estadoVencimiento === 'critico_30') {
            $query->whereBetween('fecha_vencimiento', [now()->toDateString(), now()->addDays(30)->toDateString()]);
        } elseif ($estadoVencimiento === 'alerta_60') {
            $query->whereBetween('fecha_vencimiento', [now()->addDays(31)->toDateString(), now()->addDays(60)->toDateString()]);
        } elseif ($estadoVencimiento === 'preventivo_90') {
            $query->whereBetween('fecha_vencimiento', [now()->addDays(61)->toDateString(), now()->addDays(90)->toDateString()]);
        } elseif ($estadoVencimiento === 'vigentes') {
            $query->where('fecha_vencimiento', '>', now()->addDays(90)->toDateString());
        }

        // Filtro por Estado de Stock
        if ($estadoStock === 'agotado') {
            $query->where('stock_actual', 0);
        } elseif ($estadoStock === 'disponible') {
            $query->where('stock_actual', '>', 0);
        } elseif ($estadoStock === 'bajo_stock') {
            $query->whereHas('producto', function ($q) {
                $q->bajoStock();
            });
        }

        // Exportación a CSV
        if ($request->input('export') === 'csv') {
            return $this->exportarInventarioCSV($query->orderBy('fecha_vencimiento', 'asc')->get());
        }

        // 3. Cálculos de Valorización de la Consulta Filtrada
        $lotesColeccion = (clone $query)->get();
        $totalValorCosto = 0;
        $totalValorVenta = 0;
        $totalUnidadesStock = 0;

        foreach ($lotesColeccion as $l) {
            $costo = round($l->stock_actual * (float)$l->precio_compra, 2);
            $venta = round($l->stock_actual * (float)($l->producto->precio_venta ?? 0), 2);
            $totalValorCosto += $costo;
            $totalValorVenta += $venta;
            $totalUnidadesStock += $l->stock_actual;
        }

        $margenProyectado = $totalValorCosto > 0 
            ? round((($totalValorVenta - $totalValorCosto) / $totalValorCosto) * 100, 1) 
            : 0;

        $totalProductosBajoStock = Producto::activos()->bajoStock()->count();

        // 4. Paginación
        $lotes = $query->orderBy('fecha_vencimiento', 'asc')->paginate(25)->withQueryString();

        // 5. Catálogos para los selectores
        $categorias = Categoria::activos()->orderBy('nombre')->get(['id', 'nombre']);
        $laboratorios = Laboratorio::activos()->orderBy('nombre')->get(['id', 'nombre']);

        return view('reportes.inventario', compact(
            'lotes',
            'totalValorCosto',
            'totalValorVenta',
            'totalUnidadesStock',
            'margenProyectado',
            'totalProductosBajoStock',
            'semVencidos',
            'semCritico30',
            'semAlerta60',
            'semPreventivo90',
            'semVigentes',
            'categorias',
            'laboratorios',
            'buscar',
            'categoriaId',
            'laboratorioId',
            'estadoVencimiento',
            'estadoStock'
        ));
    }

    /**
     * Exportación de Inventario a CSV con codificación UTF-8 BOM
     */
    protected function exportarInventarioCSV($lotes): StreamedResponse
    {
        $fecha = now()->format('Y-m-d_H-i');
        $filename = "reporte_inventario_valorizado_{$fecha}.csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($lotes) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, [
                'N° Lote',
                'Medicamento',
                'Principio Activo',
                'Categoría',
                'Laboratorio',
                'Fecha Vencimiento',
                'Días para Vencer',
                'Estado Caducidad',
                'Stock Actual',
                'Precio Compra ($)',
                'Valor Costo Total ($)',
                'Precio Venta ($)',
                'Valor Venta Proyectado ($)'
            ], ';');

            $today = now();

            foreach ($lotes as $l) {
                $dias = (int) $today->diffInDays($l->fecha_vencimiento, false);
                $estado = 'Vigente (>90d)';
                if ($dias < 0) $estado = 'VENCIDO';
                elseif ($dias <= 30) $estado = 'Crítico (≤30d)';
                elseif ($dias <= 60) $estado = 'Alerta (31-60d)';
                elseif ($dias <= 90) $estado = 'Preventivo (61-90d)';

                $valorCosto = round($l->stock_actual * (float)$l->precio_compra, 2);
                $valorVenta = round($l->stock_actual * (float)($l->producto->precio_venta ?? 0), 2);

                fputcsv($handle, [
                    $l->numero_lote,
                    $l->producto->nombre ?? 'N/A',
                    $l->producto->principio_activo ?? '',
                    $l->producto->categoria->nombre ?? 'Sin categoría',
                    $l->producto->laboratorio->nombre ?? 'Sin laboratorio',
                    $l->fecha_vencimiento ? $l->fecha_vencimiento->format('d/m/Y') : 'N/A',
                    $dias,
                    $estado,
                    $l->stock_actual,
                    number_format($l->precio_compra, 2, '.', ''),
                    number_format($valorCosto, 2, '.', ''),
                    number_format($l->producto->precio_venta ?? 0, 2, '.', ''),
                    number_format($valorVenta, 2, '.', '')
                ], ';');
            }

            fclose($handle);
        }, 200, $headers);
    }

    public function compras(Request $request)
    {
        $fechaDesde = $request->input('fecha_desde', now()->startOfMonth()->toDateString());
        $fechaHasta = $request->input('fecha_hasta', now()->endOfMonth()->toDateString());

        $query = Compra::with(['proveedor', 'usuario'])
            ->whereBetween(DB::raw('DATE(fecha)'), [$fechaDesde, $fechaHasta])
            ->recibidas()
            ->orderBy('fecha', 'desc');

        $totalComprado = Compra::whereBetween(DB::raw('DATE(fecha)'), [$fechaDesde, $fechaHasta])
            ->recibidas()
            ->sum('total');

        // Exportación CSV
        if ($request->input('export') === 'csv') {
            return $this->exportarComprasCSV($query->get(), $fechaDesde, $fechaHasta);
        }

        $compras = $query->paginate(20)->withQueryString();

        return view('reportes.compras', compact('compras', 'totalComprado', 'fechaDesde', 'fechaHasta'));
    }

    /**
     * Exportación de Compras a CSV con codificación UTF-8 BOM
     */
    protected function exportarComprasCSV($compras, $desde, $hasta): StreamedResponse
    {
        $filename = "reporte_compras_{$desde}_al_{$hasta}.csv";

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return response()->stream(function () use ($compras) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, [
                'N° Orden',
                'Proveedor',
                'RIF/NIT Proveedor',
                'N° Factura',
                'Fecha',
                'Responsable',
                'Estado',
                'Total ($)'
            ], ';');

            foreach ($compras as $c) {
                fputcsv($handle, [
                    str_pad($c->id, 4, '0', STR_PAD_LEFT),
                    $c->proveedor?->nombre ?? 'N/A',
                    $c->proveedor?->rif ?? '',
                    $c->numero_factura ?? '',
                    $c->fecha ? \Carbon\Carbon::parse($c->fecha)->format('d/m/Y') : '',
                    $c->usuario?->name ?? 'Sistema',
                    ucfirst($c->estado ?? 'N/A'),
                    number_format($c->total, 2, '.', '')
                ], ';');
            }

            fclose($handle);
        }, 200, $headers);
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

    /**
     * Reporte de Clientes con mayor frecuencia y volumen de compras
     */
    public function clientes(Request $request)
    {
        $fechaDesde = $request->input('fecha_desde', now()->startOfMonth()->toDateString());
        $fechaHasta = $request->input('fecha_hasta', now()->endOfMonth()->toDateString());

        // Top clientes por monto gastado en el período
        $topClientes = Cliente::withCount([
                'ventas as total_ventas' => function ($q) use ($fechaDesde, $fechaHasta) {
                    $q->where('estado', 'completada')
                      ->whereBetween(DB::raw('DATE(fecha)'), [$fechaDesde, $fechaHasta]);
                }
            ])
            ->withSum([
                'ventas as monto_total' => function ($q) use ($fechaDesde, $fechaHasta) {
                    $q->where('estado', 'completada')
                      ->whereBetween(DB::raw('DATE(fecha)'), [$fechaDesde, $fechaHasta]);
                }
            ], 'total')
            ->having('total_ventas', '>', 0)
            ->orderByDesc('monto_total')
            ->take(20)
            ->get();

        $totalClientes = Cliente::where('activo', true)->count();
        $clientesConCompras = $topClientes->count();
        $totalFacturadoClientes = $topClientes->sum('monto_total');
        $ticketPromedio = $clientesConCompras > 0
            ? $topClientes->sum('total_ventas') > 0
                ? $totalFacturadoClientes / $topClientes->sum('total_ventas')
                : 0
            : 0;

        return view('reportes.clientes', compact(
            'topClientes',
            'totalClientes',
            'clientesConCompras',
            'totalFacturadoClientes',
            'ticketPromedio',
            'fechaDesde',
            'fechaHasta'
        ));
    }

    /**
     * Reporte de Recetas Médicas: procesadas, pendientes, vencidas
     */
    public function recetas(Request $request)
    {
        $fechaDesde = $request->input('fecha_desde', now()->startOfMonth()->toDateString());
        $fechaHasta = $request->input('fecha_hasta', now()->endOfMonth()->toDateString());
        $estadoFiltro = $request->input('estado');

        $query = Receta::with(['cliente'])
            ->whereBetween(DB::raw('DATE(created_at)'), [$fechaDesde, $fechaHasta]);

        if (!empty($estadoFiltro)) {
            $query->where('estado', $estadoFiltro);
        }

        // KPIs globales (sin filtro de estado, solo por fecha)
        $baseQuery = Receta::whereBetween(DB::raw('DATE(created_at)'), [$fechaDesde, $fechaHasta]);
        $totalRecetas    = (clone $baseQuery)->count();
        $procesadas      = (clone $baseQuery)->where('estado', 'procesada')->count();
        $pendientes      = (clone $baseQuery)->where('estado', 'pendiente')->count();
        $vencidas        = (clone $baseQuery)->where('estado', 'vencida')->count();
        $rechazadas      = (clone $baseQuery)->where('estado', 'rechazada')->count();

        $recetas = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        return view('reportes.recetas', compact(
            'recetas',
            'totalRecetas',
            'procesadas',
            'pendientes',
            'vencidas',
            'rechazadas',
            'fechaDesde',
            'fechaHasta',
            'estadoFiltro'
        ));
    }
}


