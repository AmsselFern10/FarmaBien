<?php

namespace App\Http\Controllers;

use App\Http\Requests\Reportes\AjustesInventarioReporteRequest;
use App\Http\Requests\Reportes\ComprasReporteRequest;
use App\Http\Requests\Reportes\LotesReporteRequest;
use App\Http\Requests\Reportes\MovimientosInventarioReporteRequest;
use App\Http\Requests\Reportes\ProductosMasVendidosReporteRequest;
use App\Http\Requests\Reportes\VencimientosReporteRequest;
use App\Http\Requests\Reportes\VentasReporteRequest;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\User;
use App\Models\Compra;
use App\Models\Venta;
use App\Services\InventarioService;
use App\Services\ReporteExportService;
use App\Services\ReporteService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ReporteController extends Controller
{
    protected InventarioService $inventarioService;
    protected ReporteService $reporteService;
    protected ReporteExportService $exportService;

    public function __construct(InventarioService $inventarioService, ReporteService $reporteService, ReporteExportService $exportService)
    {
        $this->inventarioService = $inventarioService;
        $this->reporteService = $reporteService;
        $this->exportService = $exportService;

        $this->middleware('permission:ver reportes ventas')->only(['ventas', 'flujoCaja']);
        $this->middleware('permission:ver reportes compras')->only(['compras']);
        $this->middleware('permission:ver reportes inventario')->only([
            'inventario', 'valorizacion', 'movimientos', 'ajustes', 'lotes', 'vencimientos'
        ]);
    }

    public function index()
    {
        return view('reportes.index');
    }

    public function ventas(VentasReporteRequest $request)
    {
        $filters = $request->filters();
        $payload = $this->reporteService->ventas($filters);

        $export = $request->input('export');
        if ($export) {
            $rows = $this->reporteService->ventasExportRows($filters);
            $columns = !empty($rows)
                ? array_keys($rows[0])
                : ['ID', 'Fecha', 'Cliente', 'Cajero', 'Método Pago', 'Subtotal', 'Descuento %', 'Descuento $', 'Total'];
            $fileBase = 'ventas_' . now()->format('Ymd_His');

            if ($export === 'pdf') {
                $resp = $this->exportService->downloadPdfTable($fileBase, 'Reporte de Ventas', $columns, $rows);
                return $resp ?: back()->with('error', 'Para exportar PDF instala dompdf/dompdf (o barryvdh/laravel-dompdf).');
            }

                        if (in_array($export, ['excel', 'xlsx'], true)) {
                $resp = $this->exportService->downloadExcel($fileBase, 'Reporte de Ventas', $columns, $rows);
                return $resp ?: back()->with('error', 'Para exportar a Excel instala: composer require phpoffice/phpspreadsheet');
            }

            return $this->exportService->downloadCsv($fileBase, $columns, $rows);
        }

        $fechaInicio = $request->fechaInicioString();
        $fechaFin = $request->fechaFinString();

        $clientes = Cliente::query()->orderBy('nombre')->get(['id', 'nombre']);
        $usuarios = User::query()->orderBy('name')->get(['id', 'name']);
        $productos = Producto::query()->orderBy('nombre')->get(['id', 'nombre']);
        $categorias = DB::table('categorias')->orderBy('nombre')->get(['id', 'nombre']);

        return view('reportes.ventas', array_merge($payload, compact(
            'fechaInicio',
            'fechaFin',
            'clientes',
            'usuarios',
            'productos',
            'categorias'
        )));
    }

    public function compras(ComprasReporteRequest $request)
    {
        $filters = $request->filters();
        $payload = $this->reporteService->compras($filters);

        $export = $request->input('export');
        if ($export) {
            $rows = $this->reporteService->comprasExportRows($filters);
            $columns = !empty($rows)
                ? array_keys($rows[0])
                : ['ID', 'Fecha', 'Proveedor', 'Usuario', 'Subtotal', 'Descuento %', 'Descuento $', 'Total'];
            $fileBase = 'compras_' . now()->format('Ymd_His');

            if ($export === 'pdf') {
                $resp = $this->exportService->downloadPdfTable($fileBase, 'Reporte de Compras', $columns, $rows);
                return $resp ?: back()->with('error', 'Para exportar PDF instala dompdf/dompdf (o barryvdh/laravel-dompdf).');
            }

                        if (in_array($export, ['excel', 'xlsx'], true)) {
                $resp = $this->exportService->downloadExcel($fileBase, 'Reporte de Compras', $columns, $rows);
                return $resp ?: back()->with('error', 'Para exportar a Excel instala: composer require phpoffice/phpspreadsheet');
            }

            return $this->exportService->downloadCsv($fileBase, $columns, $rows);
        }

        $fechaInicio = $request->fechaInicioString();
        $fechaFin = $request->fechaFinString();

        $proveedores = Proveedor::query()->orderBy('nombre')->get(['id', 'nombre']);
        $usuarios = User::query()->orderBy('name')->get(['id', 'name']);
        $productos = Producto::query()->orderBy('nombre')->get(['id', 'nombre']);
        $categorias = DB::table('categorias')->orderBy('nombre')->get(['id', 'nombre']);

        return view('reportes.compras', array_merge($payload, compact(
            'fechaInicio',
            'fechaFin',
            'proveedores',
            'usuarios',
            'productos',
            'categorias'
        )));
    }

    public function inventario()
    {
        $resumenCategorias = $this->inventarioService->resumenPorCategoria();
        $productosStockBajo = $this->inventarioService->productosConStockBajo();
        $lotesProximosVencer = $this->inventarioService->lotesProximosVencer(30);
        $lotesVencidos = $this->inventarioService->lotesVencidos();
        $valorizacion = $this->inventarioService->valorizacionInventario();

        return view('reportes.inventario', compact(
            'resumenCategorias',
            'productosStockBajo',
            'lotesProximosVencer',
            'lotesVencidos',
            'valorizacion'
        ));
    }

    public function valorizacion()
    {
        $valorizacion = $this->inventarioService->valorizacionInventario();

        return view('reportes.valorizacion', compact('valorizacion'));
    }

    public function movimientos(MovimientosInventarioReporteRequest $request)
    {
        $filters = $request->filters();
        $payload = $this->reporteService->movimientosInventario($filters);

        $export = $request->input('export');
        if ($export) {
            $rows = $this->reporteService->movimientosExportRows($filters);
            $columns = !empty($rows)
                ? array_keys($rows[0])
                : ['Fecha', 'Tipo', 'Producto', 'Lote', 'Cantidad', 'Saldo Anterior', 'Saldo Nuevo', 'Origen', 'Origen ID', 'Motivo', 'Usuario'];
            $fileBase = 'movimientos_inventario_' . now()->format('Ymd_His');

            if ($export === 'pdf') {
                $resp = $this->exportService->downloadPdfTable($fileBase, 'Movimientos de Inventario', $columns, $rows);
                return $resp ?: back()->with('error', 'Para exportar PDF instala dompdf/dompdf (o barryvdh/laravel-dompdf).');
            }

                        if (in_array($export, ['excel', 'xlsx'], true)) {
                $resp = $this->exportService->downloadExcel($fileBase, 'Movimientos de Inventario', $columns, $rows);
                return $resp ?: back()->with('error', 'Para exportar a Excel instala: composer require phpoffice/phpspreadsheet');
            }

            return $this->exportService->downloadCsv($fileBase, $columns, $rows);
        }

        $fechaInicio = $request->fechaInicioString();
        $fechaFin = $request->fechaFinString();
        $tipo = $request->input('tipo');

        $productos = Producto::query()->orderBy('nombre')->get(['id', 'nombre']);
        $usuarios = User::query()->orderBy('name')->get(['id', 'name']);

        return view('reportes.movimientos', array_merge($payload, compact(
            'fechaInicio',
            'fechaFin',
            'tipo',
            'productos',
            'usuarios'
        )));
    }

    public function ajustes(AjustesInventarioReporteRequest $request)
    {
        $filters = $request->filters();
        $payload = $this->reporteService->ajustesInventario($filters);

        // FIX: asegurar contador para tarjetas (evita 0 cuando el servicio no lo envía)
        if (!isset($payload['cantidadMovimientos'])) {
            $payload['cantidadMovimientos'] = isset($payload['movimientos']) && is_array($payload['movimientos'])
                ? count($payload['movimientos'])
                : 0;
        }

        $export = $request->input('export');
        if ($export) {
            $rows = $this->reporteService->movimientosExportRows($filters);
            $columns = !empty($rows)
                ? array_keys($rows[0])
                : ['Fecha', 'Tipo', 'Producto', 'Lote', 'Cantidad', 'Saldo Anterior', 'Saldo Nuevo', 'Origen', 'Origen ID', 'Motivo', 'Usuario'];
            $fileBase = 'ajustes_inventario_' . now()->format('Ymd_His');

            if ($export === 'pdf') {
                $resp = $this->exportService->downloadPdfTable($fileBase, 'Ajustes de Inventario', $columns, $rows);
                return $resp ?: back()->with('error', 'Para exportar PDF instala dompdf/dompdf (o barryvdh/laravel-dompdf).');
            }

                        if (in_array($export, ['excel', 'xlsx'], true)) {
                $resp = $this->exportService->downloadExcel($fileBase, 'Ajustes de Inventario', $columns, $rows);
                return $resp ?: back()->with('error', 'Para exportar a Excel instala: composer require phpoffice/phpspreadsheet');
            }

            return $this->exportService->downloadCsv($fileBase, $columns, $rows);
        }

        $fechaInicio = $request->fechaInicioString();
        $fechaFin = $request->fechaFinString();

        $productos = Producto::query()->orderBy('nombre')->get(['id', 'nombre']);
        $usuarios = User::query()->orderBy('name')->get(['id', 'name']);

        return view('reportes.ajustes', array_merge($payload, compact(
            'fechaInicio',
            'fechaFin',
            'productos',
            'usuarios'
        )));
    }

    public function lotes(LotesReporteRequest $request)
    {
        $filters = $request->filters();
        $payload = $this->reporteService->lotes($filters);

        $export = $request->input('export');
        if ($export) {
            $rows = $this->reporteService->lotesExportRows($filters);
            $columns = !empty($rows)
                ? array_keys($rows[0])
                : ['ID', 'Producto', 'Proveedor', 'Compra ID', 'Lote', 'Vencimiento', 'Ingreso', 'Stock Inicial', 'Stock Actual', 'Precio Compra', 'Valor', 'Estado', 'Activo'];
            $fileBase = 'lotes_' . now()->format('Ymd_His');

            if ($export === 'pdf') {
                $resp = $this->exportService->downloadPdfTable($fileBase, 'Reporte de Lotes', $columns, $rows);
                return $resp ?: back()->with('error', 'Para exportar PDF instala dompdf/dompdf (o barryvdh/laravel-dompdf).');
            }

                        if (in_array($export, ['excel', 'xlsx'], true)) {
                $resp = $this->exportService->downloadExcel($fileBase, 'Reporte de Lotes', $columns, $rows);
                return $resp ?: back()->with('error', 'Para exportar a Excel instala: composer require phpoffice/phpspreadsheet');
            }

            return $this->exportService->downloadCsv($fileBase, $columns, $rows);
        }

        $productos = Producto::query()->orderBy('nombre')->get(['id', 'nombre']);
        $proveedores = Proveedor::query()->orderBy('nombre')->get(['id', 'nombre']);

        return view('reportes.lotes', array_merge($payload, compact(
            'productos',
            'proveedores'
        )));
    }

    public function vencimientos(VencimientosReporteRequest $request)
    {
        $filters = $request->filters();
        $payload = $this->reporteService->vencimientos($filters);

        $export = $request->input('export');
        if ($export) {
            $rows = $this->reporteService->vencimientosExportRows($filters);
            $columns = !empty($rows)
                ? array_keys($rows[0])
                : ['ID', 'Producto', 'Proveedor', 'Compra ID', 'Lote', 'Vencimiento', 'Días', 'Stock Actual', 'Precio Compra', 'Valor', 'Estado', 'Activo'];
            $fileBase = 'vencimientos_' . now()->format('Ymd_His');

            if ($export === 'pdf') {
                $resp = $this->exportService->downloadPdfTable($fileBase, 'Reporte de Vencimientos', $columns, $rows);
                return $resp ?: back()->with('error', 'Para exportar PDF instala dompdf/dompdf (o barryvdh/laravel-dompdf).');
            }

                        if (in_array($export, ['excel', 'xlsx'], true)) {
                $resp = $this->exportService->downloadExcel($fileBase, 'Reporte de Vencimientos', $columns, $rows);
                return $resp ?: back()->with('error', 'Para exportar a Excel instala: composer require phpoffice/phpspreadsheet');
            }

            return $this->exportService->downloadCsv($fileBase, $columns, $rows);
        }

        $productos = Producto::query()->orderBy('nombre')->get(['id', 'nombre']);
        $proveedores = Proveedor::query()->orderBy('nombre')->get(['id', 'nombre']);

        $dias = (int) ($filters['dias'] ?? 30);
        $modo = (string) ($filters['modo'] ?? 'todos');

        return view('reportes.vencimientos', array_merge($payload, compact(
            'productos',
            'proveedores',
            'dias',
            'modo'
        )));
    }

    
    public function flujoCaja(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');

        // Defaults: hoy
        if (!$fechaInicio) $fechaInicio = now()->format('Y-m-d');
        if (!$fechaFin) $fechaFin = now()->format('Y-m-d');

        $inicio = Carbon::parse($fechaInicio)->startOfDay();
        $fin = Carbon::parse($fechaFin)->endOfDay();

        $ventas = Venta::query()
            ->with(['cliente:id,nombre', 'usuario:id,name'])
            ->where('estado', 'completada')
            ->where(function ($q) use ($inicio, $fin) {
                $q->whereBetween('fecha', [$inicio, $fin])
                  ->orWhereBetween('created_at', [$inicio, $fin]);
            })
            ->orderByRaw('COALESCE(fecha, created_at) DESC')
            ->get();

        $compras = Compra::query()
            ->with(['proveedor:id,nombre', 'usuario:id,name'])
            ->where('estado', 'recibida')
            ->where(function ($q) use ($inicio, $fin) {
                $q->whereBetween('fecha', [$inicio, $fin])
                  ->orWhereBetween('created_at', [$inicio, $fin]);
            })
            ->orderByRaw('COALESCE(fecha, created_at) DESC')
            ->get();

        $totalVentas = (float) $ventas->sum('total');
        $totalCompras = (float) $compras->sum('total');
        $balance = $totalVentas - $totalCompras;

        // Ventas por método (KPI)
        $ventasEfectivo = (float) $ventas->where('metodo_pago', 'efectivo')->sum('total');
        $ventasTransferencia = (float) $ventas->where('metodo_pago', 'transferencia')->sum('total');
        $ventasTarjetas = (float) $ventas->whereIn('metodo_pago', ['credito', 'debito'])->sum('total');
        $ventasPagarLuego = (float) $ventas->where('metodo_pago', 'pagar_luego')->sum('total');
        $ventasOtros = (float) $ventas->where('metodo_pago', 'otros')->sum('total');

        $comprasEfectivo = (float) $compras->where('metodo_pago', 'efectivo')->sum('total');
        $comprasTransferencia = (float) $compras->where('metodo_pago', 'transferencia')->sum('total');
        $comprasTarjetas = (float) $compras->whereIn('metodo_pago', ['credito', 'debito'])->sum('total');
        $comprasPagarLuego = (float) $compras->where('metodo_pago', 'pagar_luego')->sum('total');
        $comprasOtros = (float) $compras->where('metodo_pago', 'otros')->sum('total');

        // Series (por día) para gráficos
        $ventasPorDia = $ventas->groupBy(function ($v) {
            $f = $v->fecha ?: $v->created_at;
            return optional($f)->format('Y-m-d') ?: 'sin_fecha';
        })->map(fn($g) => (float) $g->sum('total'))->sortKeys();

        $comprasPorDia = $compras->groupBy(function ($c) {
            $f = $c->fecha ?: $c->created_at;
            return optional($f)->format('Y-m-d') ?: 'sin_fecha';
        })->map(fn($g) => (float) $g->sum('total'))->sortKeys();

        // Tabla resumen por método
        $ventasPorMetodo = $ventas->groupBy(fn($v) => $v->metodo_pago ?: 'sin_metodo')->map(function ($g, $metodo) {
            return [
                'metodo' => $metodo,
                'cantidad' => $g->count(),
                'total' => (float) $g->sum('total'),
                'recibido' => (float) $g->sum('monto_recibido'),
                'cambio' => (float) $g->sum('cambio'),
            ];
        })->values();

        return view('reportes.flujo-caja', compact(
            'fechaInicio',
            'fechaFin',
            'ventas',
            'compras',
            'totalVentas',
            'totalCompras',
            'balance',
            'ventasEfectivo',
            'ventasTransferencia',
            'ventasTarjetas',
            'ventasPagarLuego',
            'ventasOtros',
            'comprasEfectivo',
            'comprasTransferencia',
            'comprasTarjetas',
            'comprasPagarLuego',
            'comprasOtros',
            'ventasPorDia',
            'comprasPorDia',
            'ventasPorMetodo'
        ));
    }


    public function productosMasVendidos(ProductosMasVendidosReporteRequest $request)
    {
        $fechaInicio = $request->fechaInicioString();
        $fechaFin = $request->fechaFinString();
        $limite = (int) $request->input('limite', 20);

        $filters = $request->filters();
        $productos = $this->reporteService->productosMasVendidos($filters);

        $export = $request->input('export');
        if ($export) {
            $rows = $this->reporteService->productosMasVendidosExportRows($filters);
            $columns = !empty($rows)
                ? array_keys($rows[0])
                : ['Producto', 'Categoría', 'Unidades (base)', 'Ingresos', 'N° Ventas'];
            $fileBase = 'productos_mas_vendidos_' . now()->format('Ymd_His');

            if ($export === 'pdf') {
                $resp = $this->exportService->downloadPdfTable($fileBase, 'Productos más Vendidos', $columns, $rows);
                return $resp ?: back()->with('error', 'Para exportar PDF instala dompdf/dompdf (o barryvdh/laravel-dompdf).');
            }

                        if (in_array($export, ['excel', 'xlsx'], true)) {
                $resp = $this->exportService->downloadExcel($fileBase, 'Productos más Vendidos', $columns, $rows);
                return $resp ?: back()->with('error', 'Para exportar a Excel instala: composer require phpoffice/phpspreadsheet');
            }

            return $this->exportService->downloadCsv($fileBase, $columns, $rows);
        }

        return view('reportes.productos-mas-vendidos', compact(
            'productos',
            'fechaInicio',
            'fechaFin',
            'limite'
        ));
    }
}
