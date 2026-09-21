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
use Barryvdh\DomPDF\Facade\Pdf;
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

        $metodosMes = Venta::whereMonth('fecha', now()->month)
            ->whereYear('fecha', now()->year)
            ->completadas()
            ->select('metodo_pago', DB::raw('SUM(total) as total'), DB::raw('COUNT(id) as cantidad'))
            ->groupBy('metodo_pago')
            ->get();

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

        $query = Venta::with(['cliente', 'usuario'])
            ->completadas()
            ->whereBetween(DB::raw('DATE(fecha)'), [$fechaDesde, $fechaHasta]);

        if (!empty($metodoPago)) {
            $query->where('metodo_pago', $metodoPago);
        }

        if (!empty($cajeroId)) {
            $query->where('usuario_id', $cajeroId);
        }

        // Exportación CSV
        if ($request->input('export') === 'csv') {
            return $this->exportarVentasCSV($query->get(), $fechaDesde, $fechaHasta);
        }

        $totalVendido = (clone $query)->sum('total');
        $cantidadVentas = (clone $query)->count();
        $ticketPromedio = $cantidadVentas > 0 ? ($totalVendido / $cantidadVentas) : 0;

        $ventasPorMetodo = (clone $query)
            ->select('metodo_pago', DB::raw('SUM(total) as total'), DB::raw('COUNT(id) as cantidad'))
            ->groupBy('metodo_pago')
            ->get();

        // Exportación PDF
        if ($request->input('export') === 'pdf') {
            $ventas = $query->orderBy('fecha', 'desc')->get();
            $pdf = Pdf::loadView('reportes.pdf.ventas', compact(
                'ventas', 'totalVendido', 'cantidadVentas', 'ticketPromedio',
                'ventasPorMetodo', 'fechaDesde', 'fechaHasta', 'metodoPago', 'cajeroId'
            ))->setPaper('letter', 'portrait');

            return $pdf->stream("reporte_ventas_{$fechaDesde}_al_{$fechaHasta}.pdf");
        }

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

        $ventas = $query->orderBy('fecha', 'desc')->paginate(25)->withQueryString();
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
            fputcsv($handle, ['N° Ticket', 'Fecha y Hora', 'Cliente', 'Documento Cliente', 'Cajero / Usuario', 'Método de Pago', 'Estado', 'Total ($)'], ';');

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

        $today = now()->toDateString();
        $semVencidos = Lote::activos()->where('stock_actual', '>', 0)->where('fecha_vencimiento', '<=', $today)->count();
        $semCritico30 = Lote::activos()->where('stock_actual', '>', 0)->whereBetween('fecha_vencimiento', [now()->addDay()->toDateString(), now()->addDays(30)->toDateString()])->count();
        $semAlerta60 = Lote::activos()->where('stock_actual', '>', 0)->whereBetween('fecha_vencimiento', [now()->addDays(31)->toDateString(), now()->addDays(60)->toDateString()])->count();
        $semPreventivo90 = Lote::activos()->where('stock_actual', '>', 0)->whereBetween('fecha_vencimiento', [now()->addDays(61)->toDateString(), now()->addDays(90)->toDateString()])->count();
        $semVigentes = Lote::activos()->where('stock_actual', '>', 0)->where('fecha_vencimiento', '>', now()->addDays(90)->toDateString())->count();

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

        if ($estadoStock === 'agotado') {
            $query->where('stock_actual', 0);
        } elseif ($estadoStock === 'disponible') {
            $query->where('stock_actual', '>', 0);
        } elseif ($estadoStock === 'bajo_stock') {
            $query->whereHas('producto', function ($q) {
                $q->bajoStock();
            });
        }

        if ($request->input('export') === 'csv') {
            return $this->exportarInventarioCSV($query->orderBy('fecha_vencimiento', 'asc')->get());
        }

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

        // Exportación PDF
        if ($request->input('export') === 'pdf') {
            $lotes = $query->orderBy('fecha_vencimiento', 'asc')->get();
            $pdf = Pdf::loadView('reportes.pdf.inventario', compact(
                'lotes', 'totalValorCosto', 'totalValorVenta', 'totalUnidadesStock',
                'margenProyectado', 'semVencidos', 'semCritico30', 'buscar',
                'categoriaId', 'laboratorioId', 'estadoVencimiento', 'estadoStock'
            ))->setPaper('letter', 'landscape');

            $fechaHoy = now()->format('Y-m-d');
            return $pdf->stream("reporte_inventario_caducidad_{$fechaHoy}.pdf");
        }

        $totalProductosBajoStock = Producto::activos()->bajoStock()->count();
        $lotes = $query->orderBy('fecha_vencimiento', 'asc')->paginate(25)->withQueryString();
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
                'N° Lote', 'Medicamento', 'Principio Activo', 'Categoría', 'Laboratorio',
                'Fecha Vencimiento', 'Días para Vencer', 'Estado Caducidad',
                'Stock Actual', 'Precio Compra ($)', 'Valor Costo Total ($)',
                'Precio Venta ($)', 'Valor Venta Proyectado ($)'
            ], ';');

            $today = now();
            foreach ($lotes as $l) {
                $dias = (int) $today->diffInDays($l->fecha_vencimiento, false);
                $estado = $dias < 0 ? 'VENCIDO' : ($dias <= 30 ? 'CRÍTICO' : ($dias <= 60 ? 'ALERTA' : ($dias <= 90 ? 'PREVENTIVO' : 'VIGENTE')));
                $valCosto = round($l->stock_actual * (float)$l->precio_compra, 2);
                $valVenta = round($l->stock_actual * (float)($l->producto->precio_venta ?? 0), 2);

                fputcsv($handle, [
                    $l->numero_lote,
                    $l->producto->nombre ?? 'N/A',
                    $l->producto->principio_activo ?? 'N/A',
                    $l->producto->categoria->nombre ?? 'Sin categoría',
                    $l->producto->laboratorio->nombre ?? 'Sin laboratorio',
                    $l->fecha_vencimiento ? $l->fecha_vencimiento->format('d/m/Y') : 'N/A',
                    $dias,
                    $estado,
                    $l->stock_actual,
                    number_format($l->precio_compra, 2, '.', ''),
                    number_format($valCosto, 2, '.', ''),
                    number_format($l->producto->precio_venta ?? 0, 2, '.', ''),
                    number_format($valVenta, 2, '.', '')
                ], ';');
            }
            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Reporte de compras y adquisiciones
     */
    public function compras(Request $request)
    {
        $fechaDesde = $request->input('fecha_desde', now()->startOfMonth()->toDateString());
        $fechaHasta = $request->input('fecha_hasta', now()->endOfMonth()->toDateString());

        $query = Compra::with(['proveedor', 'usuario'])
            ->recibidas()
            ->whereBetween(DB::raw('DATE(fecha)'), [$fechaDesde, $fechaHasta]);

        if ($request->input('export') === 'csv') {
            return $this->exportarComprasCSV($query->orderBy('fecha', 'desc')->get(), $fechaDesde, $fechaHasta);
        }

        $totalComprado = (clone $query)->sum('total');

        if ($request->input('export') === 'pdf') {
            $compras = $query->orderBy('fecha', 'desc')->get();
            $pdf = Pdf::loadView('reportes.pdf.compras', compact(
                'compras', 'totalComprado', 'fechaDesde', 'fechaHasta'
            ))->setPaper('letter', 'portrait');

            return $pdf->stream("reporte_compras_{$fechaDesde}_al_{$fechaHasta}.pdf");
        }

        $compras = $query->orderBy('fecha', 'desc')->paginate(20)->withQueryString();

        return view('reportes.compras', compact('compras', 'totalComprado', 'fechaDesde', 'fechaHasta'));
    }

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
            fputcsv($handle, ['N° Orden', 'Proveedor', 'RIF/NIT Proveedor', 'N° Factura', 'Fecha', 'Responsable', 'Estado', 'Total ($)'], ';');

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

    /**
     * Reporte de Productos Más Vendidos
     */
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

        if ($request->input('export') === 'csv') {
            return $this->exportarTopProductosCSV($ranking, $fechaDesde, $fechaHasta);
        }

        if ($request->input('export') === 'pdf') {
            $pdf = Pdf::loadView('reportes.pdf.productos-mas-vendidos', compact('ranking', 'fechaDesde', 'fechaHasta'))
                ->setPaper('letter', 'portrait');
            return $pdf->stream("reporte_top_medicamentos_{$fechaDesde}_al_{$fechaHasta}.pdf");
        }

        return view('reportes.productos-mas-vendidos', compact('ranking', 'fechaDesde', 'fechaHasta'));
    }

    protected function exportarTopProductosCSV($ranking, $desde, $hasta): StreamedResponse
    {
        $filename = "reporte_top_medicamentos_{$desde}_al_{$hasta}.csv";
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return response()->stream(function () use ($ranking) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, ['Posición', 'Medicamento', 'Principio Activo', 'Unidades Vendidas', 'Total Ingresos ($)'], ';');

            foreach ($ranking as $i => $item) {
                fputcsv($handle, [
                    $i + 1,
                    $item->nombre,
                    $item->principio_activo ?? 'N/A',
                    $item->total_unidades_vendidas,
                    number_format($item->total_ingresos, 2, '.', '')
                ], ';');
            }
            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Reporte de Alertas de Stock Mínimo
     */
    public function productosBajoStock(Request $request)
    {
        $productos = $this->inventarioService->productosConStockBajo();

        if ($request->input('export') === 'csv') {
            return $this->exportarBajoStockCSV($productos);
        }

        if ($request->input('export') === 'pdf') {
            $pdf = Pdf::loadView('reportes.pdf.productos-bajo-stock', compact('productos'))
                ->setPaper('letter', 'portrait');
            $fechaHoy = now()->format('Y-m-d');
            return $pdf->stream("reporte_stock_minimo_{$fechaHoy}.pdf");
        }

        return view('reportes.productos-bajo-stock', compact('productos'));
    }

    protected function exportarBajoStockCSV($productos): StreamedResponse
    {
        $fecha = now()->format('Y-m-d_H-i');
        $filename = "reporte_stock_minimo_{$fecha}.csv";
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return response()->stream(function () use ($productos) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, ['Medicamento', 'Principio Activo', 'Categoría', 'Laboratorio', 'Stock Actual', 'Stock Mínimo', 'Déficit', 'Estado'], ';');

            foreach ($productos as $p) {
                $sa = $p->stock_disponible ?? 0;
                $sm = $p->stock_minimo ?? 0;
                $def = max(0, $sm - $sa);
                $estado = $sa === 0 ? 'AGOTADO' : 'BAJO MÍNIMO';

                fputcsv($handle, [
                    $p->nombre,
                    $p->principio_activo ?? 'N/A',
                    $p->categoria->nombre ?? 'Sin categoría',
                    $p->laboratorio->nombre ?? 'Sin laboratorio',
                    $sa,
                    $sm,
                    $def,
                    $estado
                ], ';');
            }
            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Reporte de Clientes con mayor frecuencia y volumen de compras
     */
    public function clientes(Request $request)
    {
        $fechaDesde = $request->input('fecha_desde', now()->startOfMonth()->toDateString());
        $fechaHasta = $request->input('fecha_hasta', now()->endOfMonth()->toDateString());

        $topClientes = Cliente::whereHas('ventas', function ($q) use ($fechaDesde, $fechaHasta) {
                $q->where('estado', 'completada')
                  ->whereBetween(DB::raw('DATE(fecha)'), [$fechaDesde, $fechaHasta]);
            })
            ->withCount([
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
            ->orderByDesc('monto_total')
            ->take(20)
            ->get();

        if ($request->input('export') === 'csv') {
            return $this->exportarClientesCSV($topClientes, $fechaDesde, $fechaHasta);
        }

        $totalClientes = Cliente::where('activo', true)->count();
        $clientesConCompras = $topClientes->count();
        $totalFacturadoClientes = $topClientes->sum('monto_total');
        $ticketPromedio = $clientesConCompras > 0
            ? ($topClientes->sum('total_ventas') > 0
                ? $totalFacturadoClientes / $topClientes->sum('total_ventas')
                : 0)
            : 0;

        if ($request->input('export') === 'pdf') {
            $pdf = Pdf::loadView('reportes.pdf.clientes', compact(
                'topClientes', 'totalClientes', 'clientesConCompras',
                'totalFacturadoClientes', 'ticketPromedio', 'fechaDesde', 'fechaHasta'
            ))->setPaper('letter', 'portrait');

            return $pdf->stream("reporte_top_clientes_{$fechaDesde}_al_{$fechaHasta}.pdf");
        }

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

    protected function exportarClientesCSV($topClientes, $desde, $hasta): StreamedResponse
    {
        $filename = "reporte_clientes_top_{$desde}_al_{$hasta}.csv";
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return response()->stream(function () use ($topClientes) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, ['Posición', 'Cliente / Paciente', 'Documento', 'Teléfono', 'Email', 'Cantidad Compras', 'Total Gastado ($)', 'Ticket Promedio ($)'], ';');

            foreach ($topClientes as $i => $c) {
                $ticket = $c->total_ventas > 0 ? $c->monto_total / $c->total_ventas : 0;
                fputcsv($handle, [
                    $i + 1,
                    $c->nombre,
                    $c->documento ?? 'N/A',
                    $c->telefono ?? 'N/A',
                    $c->email ?? 'N/A',
                    $c->total_ventas,
                    number_format($c->monto_total, 2, '.', ''),
                    number_format($ticket, 2, '.', '')
                ], ';');
            }
            fclose($handle);
        }, 200, $headers);
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

        if ($request->input('export') === 'csv') {
            return $this->exportarRecetasCSV($query->orderBy('created_at', 'desc')->get(), $fechaDesde, $fechaHasta);
        }

        $baseQuery = Receta::whereBetween(DB::raw('DATE(created_at)'), [$fechaDesde, $fechaHasta]);
        $totalRecetas    = (clone $baseQuery)->count();
        $procesadas      = (clone $baseQuery)->where('estado', 'procesada')->count();
        $pendientes      = (clone $baseQuery)->where('estado', 'pendiente')->count();
        $vencidas        = (clone $baseQuery)->where('estado', 'vencida')->count();
        $rechazadas      = (clone $baseQuery)->where('estado', 'rechazada')->count();

        if ($request->input('export') === 'pdf') {
            $recetas = $query->orderBy('created_at', 'desc')->get();
            $pdf = Pdf::loadView('reportes.pdf.recetas', compact(
                'recetas', 'totalRecetas', 'procesadas', 'pendientes',
                'vencidas', 'rechazadas', 'fechaDesde', 'fechaHasta', 'estadoFiltro'
            ))->setPaper('letter', 'portrait');

            return $pdf->stream("reporte_recetas_{$fechaDesde}_al_{$fechaHasta}.pdf");
        }

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

    protected function exportarRecetasCSV($recetas, $desde, $hasta): StreamedResponse
    {
        $filename = "reporte_recetas_medicas_{$desde}_al_{$hasta}.csv";
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return response()->stream(function () use ($recetas) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, ['N° Receta', 'Paciente', 'Documento Paciente', 'Médico', 'Especialidad', 'Institución', 'Tipo Receta', 'Fecha Emisión', 'Fecha Vencimiento', 'Estado'], ';');

            foreach ($recetas as $r) {
                fputcsv($handle, [
                    $r->numero_receta ?? ('#' . str_pad($r->id, 5, '0', STR_PAD_LEFT)),
                    $r->paciente_nombre ?? ($r->cliente->nombre ?? 'N/A'),
                    $r->paciente_documento ?? ($r->cliente->documento ?? 'N/A'),
                    $r->medico_nombre ?? 'N/A',
                    $r->medico_especialidad ?? 'N/A',
                    $r->institucion_salud ?? 'N/A',
                    ucfirst($r->tipo_receta ?? 'N/A'),
                    $r->fecha_emision ? \Carbon\Carbon::parse($r->fecha_emision)->format('d/m/Y') : '',
                    $r->fecha_vencimiento ? \Carbon\Carbon::parse($r->fecha_vencimiento)->format('d/m/Y') : '',
                    ucfirst($r->estado ?? 'N/A')
                ], ';');
            }
            fclose($handle);
        }, 200, $headers);
    }
}
