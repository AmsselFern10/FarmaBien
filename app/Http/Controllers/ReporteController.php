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
use App\Models\SesionCaja;
use App\Models\MovimientoCaja;
use App\Models\Caja;
use App\Models\AuditLog;
use App\Services\InventarioService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Http\Response;

class ReporteController extends Controller
{
    protected InventarioService $inventarioService;

    public function __construct(InventarioService $inventarioService)
    {
        $this->inventarioService = $inventarioService;
        $this->middleware(function ($request, $next) {
            $user = $request->user();
            if ($user && $user->canAny(['ver reportes ventas', 'ver reportes inventario', 'ver reportes compras', 'ver cajas', 'ver usuarios'])) {
                return $next($request);
            }
            abort(403, 'No tienes permisos para ver los reportes gerenciales.');
        })->only(['index']);
        $this->middleware('permission:ver reportes ventas')->only(['ventas', 'productosMasVendidos', 'clientes', 'cajas']);
        $this->middleware('permission:ver reportes compras')->only(['compras', 'recetas']);
        $this->middleware('permission:ver reportes inventario')->only(['inventario', 'productosBajoStock']);
        $this->middleware(function ($request, $next) {
            $user = $request->user();
            if ($user && ($user->hasRole('Admin') || $user->hasRole('Farmaceutico') || $user->can('ver usuarios') || $user->can('ver reportes ventas'))) {
                return $next($request);
            }
            abort(403, 'No tienes permisos para consultar los registros de auditoría del sistema.');
        })->only(['auditorias']);
    }

    /**
     * Generar respuesta HTTP para descarga de hoja de cálculo Excel (.xls) con estilos completos
     */
    protected function responseExcel(string $view, array $data, string $filename): Response
    {
        $content = view($view, $data)->render();
        return response($content, 200, [
            'Content-Type'        => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ]);
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

        $query = Venta::with(['cliente', 'usuario', 'detalles'])
            ->completadas()
            ->whereBetween('fecha', ["{$fechaDesde} 00:00:00", "{$fechaHasta} 23:59:59"]);

        if (!empty($metodoPago)) {
            $query->where('metodo_pago', $metodoPago);
        }

        if (!empty($cajeroId)) {
            $query->where('user_id', $cajeroId);
        }

        $totalVendido = (clone $query)->sum('total');
        $cantidadVentas = (clone $query)->count();
        $ticketPromedio = $cantidadVentas > 0 ? ($totalVendido / $cantidadVentas) : 0;

        $ventasPorMetodo = (clone $query)
            ->select('metodo_pago', DB::raw('SUM(total) as total'), DB::raw('COUNT(id) as cantidad'))
            ->groupBy('metodo_pago')
            ->get();

        // Exportación Excel con estilos y anchos de columna
        if (in_array($request->input('export'), ['excel', 'xlsx', 'xls'])) {
            $ventas = $query->orderBy('fecha', 'desc')->get();
            return $this->responseExcel('reportes.excel.ventas', compact(
                'ventas', 'totalVendido', 'cantidadVentas', 'ticketPromedio',
                'ventasPorMetodo', 'fechaDesde', 'fechaHasta', 'metodoPago', 'cajeroId'
            ), "reporte_ventas_{$fechaDesde}_al_{$fechaHasta}.xls");
        }

        // Exportación CSV
        if ($request->input('export') === 'csv') {
            return $this->exportarVentasCSV($query->get(), $fechaDesde, $fechaHasta);
        }

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
            ->when(!empty($cajeroId), fn($q) => $q->where('ventas.user_id', $cajeroId))
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
                    $v->numero_factura ?? ('FAC-' . str_pad($v->id, 6, '0', STR_PAD_LEFT)),
                    $v->fecha ? \Carbon\Carbon::parse($v->fecha)->format('d/m/Y H:i') : $v->created_at->format('d/m/Y H:i'),
                    $v->cliente ? $v->cliente->nombre : 'Público General',
                    $v->cliente ? ($v->cliente->identificacion ?? $v->cliente->documento ?? 'S/N') : 'N/A',
                    $v->usuario->name ?? 'Sistema',
                    ucfirst(str_replace('_', ' ', $v->metodo_pago)),
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

        $valorizacion = $this->inventarioService->valorizacionInventario();

        // Exportación Excel con estilos y anchos
        if (in_array($request->input('export'), ['excel', 'xlsx', 'xls'])) {
            $lotes = $query->orderBy('fecha_vencimiento', 'asc')->get();
            $lotesVencidosCount = $semVencidos;
            $lotesCriticosCount = $semCritico30;
            $lotesAlertaCount = $semAlerta60;
            return $this->responseExcel('reportes.excel.inventario', compact(
                'lotes', 'valorizacion', 'lotesVencidosCount', 'lotesCriticosCount', 'lotesAlertaCount'
            ), "reporte_inventario_vencimientos_" . now()->format('Y-m-d') . ".xls");
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
        $proveedorId = $request->input('proveedor_id');
        $condicionPago = $request->input('condicion_pago');

        $query = Compra::with(['proveedor', 'usuario'])
            ->recibidas()
            ->whereBetween('fecha', ["{$fechaDesde} 00:00:00", "{$fechaHasta} 23:59:59"]);

        if (!empty($proveedorId)) {
            $query->where('proveedor_id', $proveedorId);
        }

        if (!empty($condicionPago)) {
            $query->where('condicion_pago', $condicionPago);
        }

        $totalComprado = (clone $query)->sum('total');
        $cantidadCompras = (clone $query)->count();
        $promedioCompra = $cantidadCompras > 0 ? ($totalComprado / $cantidadCompras) : 0;

        $comprasPorProveedor = (clone $query)
            ->join('proveedores', 'compras.proveedor_id', '=', 'proveedores.id')
            ->select('proveedores.nombre', DB::raw('SUM(compras.total) as total'), DB::raw('COUNT(compras.id) as cantidad'))
            ->groupBy('proveedores.id', 'proveedores.nombre')
            ->orderByDesc('total')
            ->get();

        // Exportación Excel con estilos
        if (in_array($request->input('export'), ['excel', 'xlsx', 'xls'])) {
            $compras = $query->orderBy('fecha', 'desc')->get();
            return $this->responseExcel('reportes.excel.compras', compact(
                'compras', 'totalComprado', 'cantidadCompras', 'promedioCompra',
                'comprasPorProveedor', 'fechaDesde', 'fechaHasta', 'proveedorId', 'condicionPago'
            ), "reporte_compras_{$fechaDesde}_al_{$fechaHasta}.xls");
        }

        if ($request->input('export') === 'csv') {
            return $this->exportarComprasCSV($query->orderBy('fecha', 'desc')->get(), $fechaDesde, $fechaHasta);
        }

        if ($request->input('export') === 'pdf') {
            $compras = $query->orderBy('fecha', 'desc')->get();
            $pdf = Pdf::loadView('reportes.pdf.compras', compact(
                'compras', 'totalComprado', 'cantidadCompras', 'promedioCompra',
                'comprasPorProveedor', 'fechaDesde', 'fechaHasta', 'proveedorId', 'condicionPago'
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
                    $c->numero_factura ?? ('COM-' . str_pad($c->id, 6, '0', STR_PAD_LEFT)),
                    $c->proveedor?->nombre ?? 'N/A',
                    $c->proveedor?->ruc ?? $c->proveedor?->rif ?? '',
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

        // Exportación Excel
        if (in_array($request->input('export'), ['excel', 'xlsx', 'xls'])) {
            return $this->responseExcel('reportes.excel.productos-mas-vendidos', compact(
                'ranking', 'fechaDesde', 'fechaHasta'
            ), "reporte_top_medicamentos_{$fechaDesde}_al_{$fechaHasta}.xls");
        }

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

        // Exportación Excel
        if (in_array($request->input('export'), ['excel', 'xlsx', 'xls'])) {
            return $this->responseExcel('reportes.excel.productos-bajo-stock', compact(
                'productos'
            ), "reporte_stock_minimo_" . now()->format('Y-m-d') . ".xls");
        }

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
                  ->whereBetween('fecha', ["{$fechaDesde} 00:00:00", "{$fechaHasta} 23:59:59"]);
            })
            ->withCount([
                'ventas as total_ventas' => function ($q) use ($fechaDesde, $fechaHasta) {
                    $q->where('estado', 'completada')
                      ->whereBetween('fecha', ["{$fechaDesde} 00:00:00", "{$fechaHasta} 23:59:59"]);
                }
            ])
            ->withSum([
                'ventas as monto_total' => function ($q) use ($fechaDesde, $fechaHasta) {
                    $q->where('estado', 'completada')
                      ->whereBetween('fecha', ["{$fechaDesde} 00:00:00", "{$fechaHasta} 23:59:59"]);
                }
            ], 'total')
            ->orderByDesc('monto_total')
            ->take(20)
            ->get();

        $totalClientes = Cliente::where('activo', true)->count();
        $clientesConCompras = $topClientes->count();
        $totalFacturadoClientes = $topClientes->sum('monto_total');
        $ticketPromedio = $clientesConCompras > 0
            ? ($topClientes->sum('total_ventas') > 0
                ? $totalFacturadoClientes / $topClientes->sum('total_ventas')
                : 0)
            : 0;

        // Exportación Excel
        if (in_array($request->input('export'), ['excel', 'xlsx', 'xls'])) {
            return $this->responseExcel('reportes.excel.clientes', compact(
                'topClientes', 'totalClientes', 'clientesConCompras',
                'totalFacturadoClientes', 'ticketPromedio', 'fechaDesde', 'fechaHasta'
            ), "reporte_top_clientes_{$fechaDesde}_al_{$fechaHasta}.xls");
        }

        if ($request->input('export') === 'csv') {
            return $this->exportarClientesCSV($topClientes, $fechaDesde, $fechaHasta);
        }

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

        $baseQuery = Receta::whereBetween(DB::raw('DATE(created_at)'), [$fechaDesde, $fechaHasta]);
        $totalRecetas    = (clone $baseQuery)->count();
        $procesadas      = (clone $baseQuery)->where('estado', 'procesada')->count();
        $pendientes      = (clone $baseQuery)->where('estado', 'pendiente')->count();
        $vencidas        = (clone $baseQuery)->where('estado', 'vencida')->count();
        $rechazadas      = (clone $baseQuery)->where('estado', 'rechazada')->count();

        // Exportación Excel
        if (in_array($request->input('export'), ['excel', 'xlsx', 'xls'])) {
            $recetas = $query->orderBy('created_at', 'desc')->get();
            return $this->responseExcel('reportes.excel.recetas', compact(
                'recetas', 'totalRecetas', 'procesadas', 'pendientes',
                'vencidas', 'rechazadas', 'fechaDesde', 'fechaHasta', 'estadoFiltro'
            ), "reporte_recetas_{$fechaDesde}_al_{$fechaHasta}.xls");
        }

        if ($request->input('export') === 'csv') {
            return $this->exportarRecetasCSV($query->orderBy('created_at', 'desc')->get(), $fechaDesde, $fechaHasta);
        }

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

    /**
     * Reporte Gerencial de Cajas, Turnos, Arqueos y Movimientos
     */
    public function cajas(Request $request)
    {
        $fechaDesde = $request->input('fecha_desde', now()->startOfMonth()->toDateString());
        $fechaHasta = $request->input('fecha_hasta', now()->endOfMonth()->toDateString());
        $cajaId = $request->input('caja_id');
        $cajeroId = $request->input('cajero_id');
        $estado = $request->input('estado');

        $query = SesionCaja::with(['caja', 'usuario', 'usuarioCierre', 'movimientos'])
            ->whereBetween(DB::raw('DATE(fecha_apertura)'), [$fechaDesde, $fechaHasta]);

        if (!empty($cajaId)) {
            $query->where('caja_id', $cajaId);
        }

        if (!empty($cajeroId)) {
            $query->where('user_id', $cajeroId);
        }

        if (!empty($estado)) {
            $query->where('estado', $estado);
        }

        $baseQuery = clone $query;
        $totalSesiones = (clone $baseQuery)->count();
        $totalVentasCajas = (clone $baseQuery)->sum('total_ventas');
        $totalVentasEfectivo = (clone $baseQuery)->sum('total_ventas_efectivo');
        $totalVentasTarjeta = (clone $baseQuery)->sum('total_ventas_tarjeta');
        $totalVentasTransferencia = (clone $baseQuery)->sum('total_ventas_transferencia');
        $totalIngresosManuales = (clone $baseQuery)->sum('total_ingresos_manuales');
        $totalEgresosManuales = (clone $baseQuery)->sum('total_egresos_manuales');
        $diferenciaTotal = (clone $baseQuery)->where('estado', 'cerrada')->sum('diferencia_efectivo');
        $sesionesConDiferencia = (clone $baseQuery)->where('estado', 'cerrada')->where('diferencia_efectivo', '!=', 0)->count();

        // Exportación Excel
        if (in_array($request->input('export'), ['excel', 'xlsx', 'xls'])) {
            $sesiones = $query->orderBy('fecha_apertura', 'desc')->get();
            return $this->responseExcel('reportes.excel.cajas', compact(
                'sesiones', 'totalSesiones', 'totalVentasCajas', 'totalVentasEfectivo',
                'totalVentasTarjeta', 'totalVentasTransferencia', 'totalIngresosManuales',
                'totalEgresosManuales', 'diferenciaTotal', 'sesionesConDiferencia',
                'fechaDesde', 'fechaHasta', 'cajaId', 'cajeroId', 'estado'
            ), "reporte_cajas_{$fechaDesde}_al_{$fechaHasta}.xls");
        }

        // Exportación CSV
        if ($request->input('export') === 'csv') {
            return $this->exportarCajasCSV($query->orderBy('fecha_apertura', 'desc')->get(), $fechaDesde, $fechaHasta);
        }

        // Exportación PDF
        if ($request->input('export') === 'pdf') {
            $sesiones = $query->orderBy('fecha_apertura', 'desc')->get();
            $pdf = Pdf::loadView('reportes.pdf.cajas', compact(
                'sesiones', 'totalSesiones', 'totalVentasCajas', 'totalVentasEfectivo',
                'totalVentasTarjeta', 'totalVentasTransferencia', 'totalIngresosManuales',
                'totalEgresosManuales', 'diferenciaTotal', 'sesionesConDiferencia',
                'fechaDesde', 'fechaHasta', 'cajaId', 'cajeroId', 'estado'
            ))->setPaper('letter', 'landscape');

            return $pdf->stream("reporte_cajas_{$fechaDesde}_al_{$fechaHasta}.pdf");
        }

        $sesiones = $query->orderBy('fecha_apertura', 'desc')->paginate(20)->withQueryString();
        $cajas = Caja::orderBy('nombre')->get(['id', 'nombre', 'numero']);
        $cajeros = User::whereHas('sesionesCaja')->orderBy('name')->get(['id', 'name']);

        return view('reportes.cajas', compact(
            'sesiones',
            'totalSesiones',
            'totalVentasCajas',
            'totalVentasEfectivo',
            'totalVentasTarjeta',
            'totalVentasTransferencia',
            'totalIngresosManuales',
            'totalEgresosManuales',
            'diferenciaTotal',
            'sesionesConDiferencia',
            'fechaDesde',
            'fechaHasta',
            'cajaId',
            'cajeroId',
            'estado',
            'cajas',
            'cajeros'
        ));
    }

    protected function exportarCajasCSV($sesiones, $desde, $hasta): StreamedResponse
    {
        $filename = "reporte_cajas_{$desde}_al_{$hasta}.csv";
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return response()->stream(function () use ($sesiones) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, [
                'ID Turno', 'Caja', 'Cajero Responsable', 'Fecha Apertura', 'Fecha Cierre',
                'Monto Inicial ($)', 'Ventas Efectivo ($)', 'Ventas Tarjeta ($)', 'Ventas Transf ($)',
                'Total Ventas ($)', 'Ingresos Manuales ($)', 'Egresos Manuales ($)',
                'Efectivo Esperado ($)', 'Efectivo Declarado ($)', 'Diferencia ($)', 'Estado'
            ], ';');

            foreach ($sesiones as $s) {
                fputcsv($handle, [
                    '#' . str_pad($s->id, 5, '0', STR_PAD_LEFT),
                    $s->caja?->nombre ?? ('Caja #' . ($s->caja?->numero ?? $s->caja_id)),
                    $s->usuario?->name ?? 'N/A',
                    $s->fecha_apertura ? $s->fecha_apertura->format('d/m/Y H:i') : 'N/A',
                    $s->fecha_cierre ? $s->fecha_cierre->format('d/m/Y H:i') : 'En curso',
                    number_format($s->monto_inicial, 2, '.', ''),
                    number_format($s->total_ventas_efectivo, 2, '.', ''),
                    number_format($s->total_ventas_tarjeta, 2, '.', ''),
                    number_format($s->total_ventas_transferencia, 2, '.', ''),
                    number_format($s->total_ventas, 2, '.', ''),
                    number_format($s->total_ingresos_manuales, 2, '.', ''),
                    number_format($s->total_egresos_manuales, 2, '.', ''),
                    number_format($s->monto_esperado_efectivo ?? $s->efectivo_esperado_calculado, 2, '.', ''),
                    number_format($s->monto_final_efectivo ?? 0, 2, '.', ''),
                    number_format($s->diferencia_efectivo ?? 0, 2, '.', ''),
                    ucfirst($s->estado)
                ], ';');
            }
            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Reporte de Auditoría y Trazabilidad del Sistema (Audit Logs)
     */
    public function auditorias(Request $request)
    {
        $fechaDesde = $request->input('fecha_desde', now()->subDays(30)->toDateString());
        $fechaHasta = $request->input('fecha_hasta', now()->toDateString());
        $modulo = $request->input('modulo');
        $accion = $request->input('accion');
        $userId = $request->input('user_id');
        $buscar = trim($request->input('buscar', ''));

        $query = AuditLog::with('user')
            ->whereBetween(DB::raw('DATE(created_at)'), [$fechaDesde, $fechaHasta]);

        if (!empty($modulo)) {
            $query->where('modulo', $modulo);
        }

        if (!empty($accion)) {
            $query->where('accion', $accion);
        }

        if (!empty($userId)) {
            $query->where('user_id', $userId);
        }

        if (!empty($buscar)) {
            $query->where(function ($q) use ($buscar) {
                $q->where('descripcion', 'like', "%{$buscar}%")
                  ->orWhere('ip', 'like', "%{$buscar}%")
                  ->orWhereHas('user', function ($uq) use ($buscar) {
                      $uq->where('name', 'like', "%{$buscar}%")
                         ->orWhere('email', 'like', "%{$buscar}%");
                  });
            });
        }

        $baseQuery = clone $query;
        $totalLogs = (clone $baseQuery)->count();
        $usuariosActivos = (clone $baseQuery)->distinct('user_id')->count('user_id');
        $modulosAuditados = (clone $baseQuery)->distinct('modulo')->count('modulo');
        $accionesCriticas = (clone $baseQuery)->whereIn(DB::raw('UPPER(accion)'), [
            'ELIMINAR', 'DELETE', 'ANULAR', 'AJUSTE', 'DESACTIVAR', 'UPDATE', 'EDITAR'
        ])->count();

        // Exportación Excel
        if (in_array($request->input('export'), ['excel', 'xlsx', 'xls'])) {
            $logs = $query->orderBy('created_at', 'desc')->get();
            return $this->responseExcel('reportes.excel.auditorias', compact(
                'logs', 'totalLogs', 'usuariosActivos', 'modulosAuditados', 'accionesCriticas',
                'fechaDesde', 'fechaHasta', 'modulo', 'accion', 'userId', 'buscar'
            ), "reporte_auditoria_{$fechaDesde}_al_{$fechaHasta}.xls");
        }

        // Exportación CSV
        if ($request->input('export') === 'csv') {
            return $this->exportarAuditoriasCSV($query->orderBy('created_at', 'desc')->get(), $fechaDesde, $fechaHasta);
        }

        // Exportación PDF
        if ($request->input('export') === 'pdf') {
            $logs = $query->orderBy('created_at', 'desc')->get();
            $pdf = Pdf::loadView('reportes.pdf.auditorias', compact(
                'logs', 'totalLogs', 'usuariosActivos', 'modulosAuditados', 'accionesCriticas',
                'fechaDesde', 'fechaHasta', 'modulo', 'accion', 'userId', 'buscar'
            ))->setPaper('letter', 'landscape');

            return $pdf->stream("reporte_auditoria_{$fechaDesde}_al_{$fechaHasta}.pdf");
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(30)->withQueryString();
        $modulos = AuditLog::select('modulo')->distinct()->whereNotNull('modulo')->orderBy('modulo')->pluck('modulo');
        $acciones = AuditLog::select('accion')->distinct()->whereNotNull('accion')->orderBy('accion')->pluck('accion');
        $usuarios = User::whereHas('auditLogs')->orWhereIn('id', AuditLog::select('user_id')->distinct())->orderBy('name')->get(['id', 'name', 'email']);

        return view('reportes.auditorias', compact(
            'logs',
            'totalLogs',
            'usuariosActivos',
            'modulosAuditados',
            'accionesCriticas',
            'fechaDesde',
            'fechaHasta',
            'modulo',
            'accion',
            'userId',
            'buscar',
            'modulos',
            'acciones',
            'usuarios'
        ));
    }

    protected function exportarAuditoriasCSV($logs, $desde, $hasta): StreamedResponse
    {
        $filename = "reporte_auditorias_{$desde}_al_{$hasta}.csv";
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return response()->stream(function () use ($logs) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, ['ID', 'Fecha y Hora', 'Usuario', 'Email Usuario', 'Módulo', 'Acción', 'Descripción', 'Dirección IP', 'Navegador / User Agent'], ';');

            foreach ($logs as $l) {
                fputcsv($handle, [
                    $l->id,
                    $l->created_at ? $l->created_at->format('d/m/Y H:i:s') : 'N/A',
                    $l->user?->name ?? 'Sistema / Cron',
                    $l->user?->email ?? 'N/A',
                    strtoupper($l->modulo ?? 'GENERAL'),
                    strtoupper($l->accion ?? 'EVENTO'),
                    $l->descripcion ?? '',
                    $l->ip ?? 'N/A',
                    $l->user_agent ?? ''
                ], ';');
            }
            fclose($handle);
        }, 200, $headers);
    }
}
