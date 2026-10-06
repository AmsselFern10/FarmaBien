<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Laboratorio;
use App\Models\User;
use App\Models\Caja;
use App\Models\AuditLog;
use App\Services\ReporteService;
use App\Services\InventarioService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Http\Response;

class ReporteController extends Controller
{
    protected ReporteService $reporteService;
    protected InventarioService $inventarioService;

    public function __construct(ReporteService $reporteService, InventarioService $inventarioService)
    {
        $this->reporteService = $reporteService;
        $this->inventarioService = $inventarioService;

        $this->middleware(function ($request, $next) {
            $user = $request->user();
            if ($user && ($user->canAny(['ver reportes ventas', 'ver reportes inventario', 'ver reportes compras', 'ver cajas', 'ver usuarios']) || $user->hasAnyRole(['administrador', 'admin', 'farmaceutico']))) {
                return $next($request);
            }
            abort(403, 'No tienes permisos para ver los reportes gerenciales.');
        })->only(['index']);

        $this->middleware('permission:ver reportes ventas')->only(['ventas', 'productosMasVendidos', 'clientes', 'cajas']);
        $this->middleware('permission:ver reportes compras')->only(['compras', 'recetas']);
        $this->middleware('permission:ver reportes inventario')->only(['inventario', 'productosBajoStock']);

        $this->middleware(function ($request, $next) {
            $user = $request->user();
            if ($user && ($user->hasAnyRole(['administrador', 'admin', 'farmaceutico']) || $user->can('ver usuarios') || $user->can('ver reportes ventas'))) {
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
        $kpis = $this->reporteService->getDashboardKpis();
        return view('reportes.index', $kpis);
    }

    /**
     * Reporte detallado de ventas e ingresos con filtros y exportación
     */
    public function ventas(Request $request)
    {
        $filters = [
            'fecha_desde' => $request->input('fecha_desde', now()->startOfMonth()->toDateString()),
            'fecha_hasta' => $request->input('fecha_hasta', now()->endOfMonth()->toDateString()),
            'metodo_pago' => $request->input('metodo_pago'),
            'cajero_id'   => $request->input('cajero_id'),
        ];

        $query = $this->reporteService->getQueryVentas($filters);
        $estadisticas = $this->reporteService->getEstadisticasVentas($query, $filters);

        $fechaDesde = $filters['fecha_desde'];
        $fechaHasta = $filters['fecha_hasta'];
        $metodoPago = $filters['metodo_pago'];
        $cajeroId = $filters['cajero_id'];

        // Exportación Excel con estilos y anchos de columna
        if (in_array($request->input('export'), ['excel', 'xlsx', 'xls'])) {
            $ventas = $query->orderBy('fecha', 'desc')->get();
            return $this->responseExcel('reportes.excel.ventas', array_merge([
                'ventas'     => $ventas,
                'fechaDesde' => $fechaDesde,
                'fechaHasta' => $fechaHasta,
                'metodoPago' => $metodoPago,
                'cajeroId'   => $cajeroId,
            ], $estadisticas), "reporte_ventas_{$fechaDesde}_al_{$fechaHasta}.xls");
        }

        // Exportación CSV
        if ($request->input('export') === 'csv') {
            return $this->exportarVentasCSV($query->orderBy('fecha', 'desc')->get(), $fechaDesde, $fechaHasta);
        }

        // Exportación PDF
        if ($request->input('export') === 'pdf') {
            $ventas = $query->orderBy('fecha', 'desc')->get();
            $pdf = Pdf::loadView('reportes.pdf.ventas', array_merge([
                'ventas'     => $ventas,
                'fechaDesde' => $fechaDesde,
                'fechaHasta' => $fechaHasta,
                'metodoPago' => $metodoPago,
                'cajeroId'   => $cajeroId,
            ], $estadisticas))->setPaper('letter', 'portrait');

            return $pdf->stream("reporte_ventas_{$fechaDesde}_al_{$fechaHasta}.pdf");
        }

        $ventas = $query->orderBy('fecha', 'desc')->paginate(perPage(25))->withQueryString();
        $cajeros = User::whereHas('ventas')->orderBy('name')->get(['id', 'name']);

        return view('reportes.ventas', array_merge([
            'ventas'     => $ventas,
            'fechaDesde' => $fechaDesde,
            'fechaHasta' => $fechaHasta,
            'metodoPago' => $metodoPago,
            'cajeroId'   => $cajeroId,
            'cajeros'    => $cajeros,
        ], $estadisticas));
    }

    protected function exportarVentasCSV($ventas, $desde, $hasta): StreamedResponse
    {
        $filename = "reporte_ventas_{$desde}_al_{$hasta}.csv";
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return response()->stream(function () use ($ventas) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, ['N° Comprobante', 'Fecha y Hora', 'Cliente', 'Documento Cliente', 'Cajero / Usuario', 'Método de Pago', 'Estado', 'Total (C$)'], ';');

            foreach ($ventas as $v) {
                fputcsv($handle, [
                    $v->numero_comprobante ? ($v->serie ? $v->serie . '-' : '') . $v->numero_comprobante : ('TICK-' . str_pad($v->id, 6, '0', STR_PAD_LEFT)),
                    $v->fecha ? \Carbon\Carbon::parse($v->fecha)->format('d/m/Y H:i') : $v->created_at->format('d/m/Y H:i'),
                    $v->cliente ? $v->cliente->nombre : 'Público General',
                    $v->cliente ? ($v->cliente->documento ?? 'S/N') : 'N/A',
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
        $filters = [
            'buscar'             => trim($request->input('buscar', '')),
            'categoria_id'       => $request->input('categoria_id'),
            'laboratorio_id'     => $request->input('laboratorio_id'),
            'estado_vencimiento' => $request->input('estado_vencimiento', 'todos'),
            'estado_stock'       => $request->input('estado_stock', 'todos'),
        ];

        $query = $this->reporteService->getQueryInventario($filters);
        $estadisticas = $this->reporteService->getEstadisticasInventario($query);

        $buscar = $filters['buscar'];
        $categoriaId = $filters['categoria_id'];
        $laboratorioId = $filters['laboratorio_id'];
        $estadoVencimiento = $filters['estado_vencimiento'];
        $estadoStock = $filters['estado_stock'];

        // Exportación Excel con estilos y anchos
        if (in_array($request->input('export'), ['excel', 'xlsx', 'xls'])) {
            $lotes = $query->orderBy('fecha_vencimiento', 'asc')->get();
            $lotesVencidosCount = $estadisticas['semVencidos'];
            $lotesCriticosCount = $estadisticas['semCritico30'];
            $lotesAlertaCount = $estadisticas['semAlerta60'];
            return $this->responseExcel('reportes.excel.inventario', array_merge([
                'lotes'              => $lotes,
                'lotesVencidosCount' => $lotesVencidosCount,
                'lotesCriticosCount' => $lotesCriticosCount,
                'lotesAlertaCount'   => $lotesAlertaCount,
            ], $estadisticas), "reporte_inventario_vencimientos_" . now()->format('Y-m-d') . ".xls");
        }

        if ($request->input('export') === 'csv') {
            return $this->exportarInventarioCSV($query->orderBy('fecha_vencimiento', 'asc')->get());
        }

        // Exportación PDF
        if ($request->input('export') === 'pdf') {
            $lotes = $query->orderBy('fecha_vencimiento', 'asc')->get();
            $pdf = Pdf::loadView('reportes.pdf.inventario', array_merge([
                'lotes'             => $lotes,
                'buscar'            => $buscar,
                'categoriaId'       => $categoriaId,
                'laboratorioId'     => $laboratorioId,
                'estadoVencimiento' => $estadoVencimiento,
                'estadoStock'       => $estadoStock,
            ], $estadisticas))->setPaper('letter', 'landscape');

            $fechaHoy = now()->format('Y-m-d');
            return $pdf->stream("reporte_inventario_caducidad_{$fechaHoy}.pdf");
        }

        $lotes = $query->orderBy('fecha_vencimiento', 'asc')->paginate(perPage(25))->withQueryString();
        $categorias = Categoria::activas()->orderBy('nombre')->get(['id', 'nombre']);
        $laboratorios = Laboratorio::activos()->orderBy('nombre')->get(['id', 'nombre']);

        return view('reportes.inventario', array_merge([
            'lotes'             => $lotes,
            'categorias'        => $categorias,
            'laboratorios'      => $laboratorios,
            'buscar'            => $buscar,
            'categoriaId'       => $categoriaId,
            'laboratorioId'     => $laboratorioId,
            'estadoVencimiento' => $estadoVencimiento,
            'estadoStock'       => $estadoStock,
        ], $estadisticas));
    }

    protected function exportarInventarioCSV($lotes): StreamedResponse
    {
        $fecha = now()->format('Y-m-d_H-i');
        $filename = "reporte_inventario_valorizado_{$fecha}.csv";
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return response()->stream(function () use ($lotes) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, [
                'N° Lote', 'Medicamento', 'Principio Activo', 'Categoría', 'Laboratorio',
                'Fecha Vencimiento', 'Días para Vencer', 'Estado Caducidad',
                'Stock Actual', 'Precio Compra (C$)', 'Valor Costo Total (C$)',
                'Precio Venta (C$)', 'Valor Venta Proyectado (C$)'
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
        $filters = [
            'fecha_desde'    => $request->input('fecha_desde', now()->startOfMonth()->toDateString()),
            'fecha_hasta'    => $request->input('fecha_hasta', now()->endOfMonth()->toDateString()),
            'proveedor_id'   => $request->input('proveedor_id'),
            'condicion_pago' => $request->input('condicion_pago'),
        ];

        $query = $this->reporteService->getQueryCompras($filters);
        $estadisticas = $this->reporteService->getEstadisticasCompras($query);

        $fechaDesde = $filters['fecha_desde'];
        $fechaHasta = $filters['fecha_hasta'];
        $proveedorId = $filters['proveedor_id'];
        $condicionPago = $filters['condicion_pago'];

        // Exportación Excel con estilos
        if (in_array($request->input('export'), ['excel', 'xlsx', 'xls'])) {
            $compras = $query->orderBy('fecha', 'desc')->get();
            return $this->responseExcel('reportes.excel.compras', array_merge([
                'compras'       => $compras,
                'fechaDesde'    => $fechaDesde,
                'fechaHasta'    => $fechaHasta,
                'proveedorId'   => $proveedorId,
                'condicionPago' => $condicionPago,
            ], $estadisticas), "reporte_compras_{$fechaDesde}_al_{$fechaHasta}.xls");
        }

        if ($request->input('export') === 'csv') {
            return $this->exportarComprasCSV($query->orderBy('fecha', 'desc')->get(), $fechaDesde, $fechaHasta);
        }

        if ($request->input('export') === 'pdf') {
            $compras = $query->orderBy('fecha', 'desc')->get();
            $pdf = Pdf::loadView('reportes.pdf.compras', array_merge([
                'compras'       => $compras,
                'fechaDesde'    => $fechaDesde,
                'fechaHasta'    => $fechaHasta,
                'proveedorId'   => $proveedorId,
                'condicionPago' => $condicionPago,
            ], $estadisticas))->setPaper('letter', 'portrait');

            return $pdf->stream("reporte_compras_{$fechaDesde}_al_{$fechaHasta}.pdf");
        }

        $compras = $query->orderBy('fecha', 'desc')->paginate(perPage(20))->withQueryString();

        return view('reportes.compras', array_merge([
            'compras'       => $compras,
            'fechaDesde'    => $fechaDesde,
            'fechaHasta'    => $fechaHasta,
            'proveedorId'   => $proveedorId,
            'condicionPago' => $condicionPago,
        ], $estadisticas));
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
            fputcsv($handle, ['N° Compra', 'Proveedor', 'RIF/RUC Proveedor', 'N° Factura', 'Fecha', 'Responsable', 'Estado', 'Total (C$)'], ';');

            foreach ($compras as $c) {
                fputcsv($handle, [
                    $c->numero_compra ?? ('COM-' . str_pad($c->id, 6, '0', STR_PAD_LEFT)),
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

        $ranking = $this->reporteService->getRankingProductosMasVendidos($fechaDesde, $fechaHasta, 20);

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
            fputcsv($handle, ['Posición', 'Medicamento', 'Principio Activo', 'Unidades Vendidas', 'Total Ingresos (C$)'], ';');

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

        $topClientes = $this->reporteService->getRankingClientes($fechaDesde, $fechaHasta, 20);

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
            fputcsv($handle, ['Posición', 'Cliente / Paciente', 'Documento', 'Teléfono', 'Email', 'Cantidad Compras', 'Total Gastado (C$)', 'Ticket Promedio (C$)'], ';');

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
        $filters = [
            'fecha_desde' => $request->input('fecha_desde', now()->startOfMonth()->toDateString()),
            'fecha_hasta' => $request->input('fecha_hasta', now()->endOfMonth()->toDateString()),
            'estado'      => $request->input('estado'),
        ];

        $data = $this->reporteService->getReporteRecetasData($filters);
        $query = $data['query'];
        $fechaDesde = $filters['fecha_desde'];
        $fechaHasta = $filters['fecha_hasta'];
        $estadoFiltro = $filters['estado'];

        // Exportación Excel
        if (in_array($request->input('export'), ['excel', 'xlsx', 'xls'])) {
            $recetas = $query->orderBy('created_at', 'desc')->get();
            return $this->responseExcel('reportes.excel.recetas', array_merge([
                'recetas'      => $recetas,
                'fechaDesde'   => $fechaDesde,
                'fechaHasta'   => $fechaHasta,
                'estadoFiltro' => $estadoFiltro,
            ], $data), "reporte_recetas_{$fechaDesde}_al_{$fechaHasta}.xls");
        }

        if ($request->input('export') === 'csv') {
            return $this->exportarRecetasCSV($query->orderBy('created_at', 'desc')->get(), $fechaDesde, $fechaHasta);
        }

        if ($request->input('export') === 'pdf') {
            $recetas = $query->orderBy('created_at', 'desc')->get();
            $pdf = Pdf::loadView('reportes.pdf.recetas', array_merge([
                'recetas'      => $recetas,
                'fechaDesde'   => $fechaDesde,
                'fechaHasta'   => $fechaHasta,
                'estadoFiltro' => $estadoFiltro,
            ], $data))->setPaper('letter', 'portrait');

            return $pdf->stream("reporte_recetas_{$fechaDesde}_al_{$fechaHasta}.pdf");
        }

        $recetas = $query->orderBy('created_at', 'desc')->paginate(perPage(20))->withQueryString();

        return view('reportes.recetas', array_merge([
            'recetas'      => $recetas,
            'fechaDesde'   => $fechaDesde,
            'fechaHasta'   => $fechaHasta,
            'estadoFiltro' => $estadoFiltro,
        ], $data));
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
        $filters = [
            'fecha_desde' => $request->input('fecha_desde', now()->startOfMonth()->toDateString()),
            'fecha_hasta' => $request->input('fecha_hasta', now()->endOfMonth()->toDateString()),
            'caja_id'     => $request->input('caja_id'),
            'cajero_id'   => $request->input('cajero_id'),
            'estado'      => $request->input('estado'),
        ];

        $data = $this->reporteService->getReporteCajasData($filters);
        $query = $data['query'];
        $fechaDesde = $filters['fecha_desde'];
        $fechaHasta = $filters['fecha_hasta'];
        $cajaId = $filters['caja_id'];
        $cajeroId = $filters['cajero_id'];
        $estado = $filters['estado'];

        // Exportación Excel
        if (in_array($request->input('export'), ['excel', 'xlsx', 'xls'])) {
            $sesiones = $query->orderBy('fecha_apertura', 'desc')->get();
            return $this->responseExcel('reportes.excel.cajas', array_merge([
                'sesiones'   => $sesiones,
                'fechaDesde' => $fechaDesde,
                'fechaHasta' => $fechaHasta,
                'cajaId'     => $cajaId,
                'cajeroId'   => $cajeroId,
                'estado'     => $estado,
            ], $data), "reporte_cajas_{$fechaDesde}_al_{$fechaHasta}.xls");
        }

        // Exportación CSV
        if ($request->input('export') === 'csv') {
            return $this->exportarCajasCSV($query->orderBy('fecha_apertura', 'desc')->get(), $fechaDesde, $fechaHasta);
        }

        // Exportación PDF
        if ($request->input('export') === 'pdf') {
            $sesiones = $query->orderBy('fecha_apertura', 'desc')->get();
            $pdf = Pdf::loadView('reportes.pdf.cajas', array_merge([
                'sesiones'   => $sesiones,
                'fechaDesde' => $fechaDesde,
                'fechaHasta' => $fechaHasta,
                'cajaId'     => $cajaId,
                'cajeroId'   => $cajeroId,
                'estado'     => $estado,
            ], $data))->setPaper('letter', 'landscape');

            return $pdf->stream("reporte_cajas_{$fechaDesde}_al_{$fechaHasta}.pdf");
        }

        $sesiones = $query->orderBy('fecha_apertura', 'desc')->paginate(perPage(20))->withQueryString();
        $cajas = Caja::orderBy('nombre')->get(['id', 'nombre', 'codigo']);
        $cajeros = User::whereHas('sesionesCaja')->orderBy('name')->get(['id', 'name']);

        return view('reportes.cajas', array_merge([
            'sesiones'   => $sesiones,
            'fechaDesde' => $fechaDesde,
            'fechaHasta' => $fechaHasta,
            'cajaId'     => $cajaId,
            'cajeroId'   => $cajeroId,
            'estado'     => $estado,
            'cajas'      => $cajas,
            'cajeros'    => $cajeros,
        ], $data));
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
                'Monto Inicial (C$)', 'Ventas Efectivo (C$)', 'Ventas Tarjeta (C$)', 'Ventas Transf (C$)',
                'Total Ventas (C$)', 'Ingresos Manuales (C$)', 'Egresos Manuales (C$)',
                'Efectivo Esperado (C$)', 'Efectivo Declarado (C$)', 'Diferencia (C$)', 'Estado'
            ], ';');

            foreach ($sesiones as $s) {
                fputcsv($handle, [
                    '#' . str_pad($s->id, 5, '0', STR_PAD_LEFT),
                    $s->caja?->nombre ?? ('Caja #' . ($s->caja?->codigo ?? $s->caja_id)),
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
        $filters = [
            'fecha_desde' => $request->input('fecha_desde', now()->subDays(30)->toDateString()),
            'fecha_hasta' => $request->input('fecha_hasta', now()->toDateString()),
            'modulo'      => $request->input('modulo'),
            'accion'      => $request->input('accion'),
            'user_id'     => $request->input('user_id'),
            'buscar'      => trim($request->input('buscar', '')),
        ];

        $data = $this->reporteService->getReporteAuditoriasData($filters);
        $query = $data['query'];
        $fechaDesde = $filters['fecha_desde'];
        $fechaHasta = $filters['fecha_hasta'];
        $modulo = $filters['modulo'];
        $accion = $filters['accion'];
        $userId = $filters['user_id'];
        $buscar = $filters['buscar'];

        // Exportación Excel
        if (in_array($request->input('export'), ['excel', 'xlsx', 'xls'])) {
            $logs = $query->orderBy('created_at', 'desc')->get();
            return $this->responseExcel('reportes.excel.auditorias', array_merge([
                'logs'       => $logs,
                'fechaDesde' => $fechaDesde,
                'fechaHasta' => $fechaHasta,
                'modulo'     => $modulo,
                'accion'     => $accion,
                'userId'     => $userId,
                'buscar'     => $buscar,
            ], $data), "reporte_auditoria_{$fechaDesde}_al_{$fechaHasta}.xls");
        }

        // Exportación CSV
        if ($request->input('export') === 'csv') {
            return $this->exportarAuditoriasCSV($query->orderBy('created_at', 'desc')->get(), $fechaDesde, $fechaHasta);
        }

        // Exportación PDF
        if ($request->input('export') === 'pdf') {
            $logs = $query->orderBy('created_at', 'desc')->get();
            $pdf = Pdf::loadView('reportes.pdf.auditorias', array_merge([
                'logs'       => $logs,
                'fechaDesde' => $fechaDesde,
                'fechaHasta' => $fechaHasta,
                'modulo'     => $modulo,
                'accion'     => $accion,
                'userId'     => $userId,
                'buscar'     => $buscar,
            ], $data))->setPaper('letter', 'landscape');

            return $pdf->stream("reporte_auditoria_{$fechaDesde}_al_{$fechaHasta}.pdf");
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(perPage(30))->withQueryString();
        $modulos = AuditLog::select('modulo')->distinct()->whereNotNull('modulo')->orderBy('modulo')->pluck('modulo');
        $acciones = AuditLog::select('accion')->distinct()->whereNotNull('accion')->orderBy('accion')->pluck('accion');
        $usuarios = User::whereHas('auditLogs')->orWhereIn('id', AuditLog::select('user_id')->distinct())->orderBy('name')->get(['id', 'name', 'email']);

        return view('reportes.auditorias', array_merge([
            'logs'       => $logs,
            'fechaDesde' => $fechaDesde,
            'fechaHasta' => $fechaHasta,
            'modulo'     => $modulo,
            'accion'     => $accion,
            'userId'     => $userId,
            'buscar'     => $buscar,
            'modulos'    => $modulos,
            'acciones'   => $acciones,
            'usuarios'   => $usuarios,
        ], $data));
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
