<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\PrecioVenta;
use App\Models\Categoria;
use App\Models\Laboratorio;
use App\Models\Proveedor;
use App\Models\Promocion;
use App\Http\Requests\ActualizarPrecioVentaRequest;
use App\Http\Requests\ActualizarPrecioInlineRequest;
use App\Http\Requests\ActualizarPrecioMasivoRequest;
use App\Services\PrecioVentaService;
use App\Services\PromocionService;
use Illuminate\Http\Request;

class PrecioVentaController extends Controller
{
    public function __construct(
        protected PrecioVentaService $precioService,
        protected PromocionService $promocionService
    ) {
        $this->middleware('auth');
    }

    /**
     * Listado principal de Precios de Venta con métricas, filtros y edición rápida
     */
    public function index(Request $request)
    {
        $filtros = $request->only(['q', 'categoria_id', 'laboratorio_id', 'proveedor_id', 'estado_margen']);
        $perPage = in_array((int)$request->per_page, [15, 25, 50, 100]) ? (int)$request->per_page : 15;

        $productos = $this->precioService->listarProductosConPrecios($filtros, $perPage)->withQueryString();
        $metricas = $this->precioService->obtenerMetricasPrecios();
        $promocionesActivas = Producto::getPromocionesVigentes();

        return view('precios.index', array_merge(compact('productos', 'promocionesActivas'), $metricas));
    }

    /**
     * Ficha de Precio de Venta (Show) con historial de cambios y presentaciones
     */
    public function show(Producto $producto)
    {
        $producto->load([
            'categoria',
            'laboratorio',
            'presentaciones' => function ($q) {
                $q->where('activo', true)->orderBy('orden', 'asc');
            },
            'precioVentaVigente.usuario',
        ]);

        $historial = PrecioVenta::with(['presentacion', 'usuario'])
            ->where('producto_id', $producto->id)
            ->orderByDesc('vigente_desde')
            ->orderByDesc('id')
            ->get();

        $promocionVigente = $this->promocionService->obtenerPromocionVigenteParaProducto($producto);

        $costoReferencia = (float)($producto->precio_compra ?? 0);
        $precioBase = (float)($producto->precio_venta ?? 0);
        $margenEstimado = $precioBase > 0 ? (($precioBase - $costoReferencia) / $precioBase) * 100 : 0;

        return view('precios.show', compact(
            'producto',
            'historial',
            'promocionVigente',
            'costoReferencia',
            'precioBase',
            'margenEstimado'
        ));
    }

    /**
     * Formulario de Edición de Precio de Venta (Edit)
     */
    public function edit(Producto $producto)
    {
        $producto->load([
            'categoria',
            'laboratorio',
            'presentaciones' => function ($q) {
                $q->where('activo', true)->orderBy('orden', 'asc');
            },
            'precioVentaVigente',
        ]);

        $costoReferencia = (float)($producto->precio_compra ?? 0);
        $precioBase = (float)($producto->precio_venta ?? 0);

        return view('precios.edit', compact('producto', 'costoReferencia', 'precioBase'));
    }

    /**
     * Guardar cambios de precio de venta (Update)
     */
    public function update(ActualizarPrecioVentaRequest $request, Producto $producto)
    {
        $data = $request->validated();
        $nuevoPrecioBase = (float)$data['precio_base'];
        $motivo = $data['motivo'];
        $fechaVigencia = $data['vigente_desde'] ?? null;
        $presentaciones = $data['presentaciones'] ?? [];

        $this->precioService->actualizarPreciosProducto($producto, $nuevoPrecioBase, $motivo, $fechaVigencia, $presentaciones);

        return redirect()->route('precios.show', $producto)
            ->with('success', "Precios de venta actualizados correctamente para {$producto->nombre}.");
    }

    /**
     * Edición Rápida en Línea desde la tabla del Index (AJAX)
     */
    public function inlineUpdate(ActualizarPrecioInlineRequest $request, Producto $producto)
    {
        $data = $request->validated();
        $resultado = $this->precioService->actualizarPrecioInline($producto, (float)$data['precio_venta'], $data['motivo']);

        return response()->json($resultado);
    }

    /**
     * Vista de Actualización Masiva de Precios
     */
    public function masivo()
    {
        $categorias = Categoria::getCachedActivos();
        $laboratorios = Laboratorio::getCachedActivos();
        $proveedores = Proveedor::getCachedActivos();

        return view('precios.masivo', compact('categorias', 'laboratorios', 'proveedores'));
    }

    /**
     * API Vista Previa en Vivo para Actualización Masiva
     */
    public function apiPreviewMasivo(Request $request)
    {
        $tipoAlcance = $request->get('tipo_alcance', 'todo');
        $alcanceIds = $request->get('alcance_ids', []);
        if (is_string($alcanceIds)) {
            $alcanceIds = array_filter(explode(',', $alcanceIds));
        }

        $tipoAjuste = $request->get('tipo_ajuste', 'porcentaje_aumento');
        $valorAjuste = (float)$request->get('valor_ajuste', 0);
        $redondeo = $request->get('redondeo', 'sin');

        $preview = $this->precioService->generarVistaPreviaMasiva($tipoAlcance, $alcanceIds, $tipoAjuste, $valorAjuste, $redondeo);

        return response()->json(array_merge(['success' => true], $preview));
    }

    /**
     * Guardar Actualización Masiva de Precios
     */
    public function aplicarMasivo(ActualizarPrecioMasivoRequest $request)
    {
        $data = $request->validated();
        $tipoAlcance = $data['tipo_alcance'];
        $alcanceIds = $data['alcance_ids'] ?? [];
        if (is_string($alcanceIds)) {
            $alcanceIds = array_filter(explode(',', $alcanceIds));
        }

        $tipoAjuste = $data['tipo_ajuste'];
        $valorAjuste = (float)$data['valor_ajuste'];
        $redondeo = $data['redondeo'] ?? 'sin';
        $motivo = $data['motivo'];

        $resultado = $this->precioService->aplicarAjusteMasivo($tipoAlcance, $alcanceIds, $tipoAjuste, $valorAjuste, $redondeo, $motivo);

        if ($request->wantsJson()) {
            return response()->json($resultado);
        }

        return redirect()->route('precios.index')->with('success', $resultado['message']);
    }

    /**
     * Historial General de Cambios de Precio (Auditoría)
     */
    public function historialGeneral(Request $request)
    {
        $filtros = $request->only(['q', 'desde', 'hasta', 'user_id']);
        $historial = $this->precioService->listarHistorialAuditoria($filtros, 25)->withQueryString();
        $usuarios = \App\Models\User::orderBy('name')->get(['id', 'name']);

        return view('precios.historial', compact('historial', 'usuarios'));
    }

    /**
     * Exportar Precios de Venta Vigentes a CSV con UTF-8 BOM
     */
    public function exportar(Request $request)
    {
        $productos = Producto::with(['categoria:id,nombre', 'laboratorio:id,nombre', 'presentacionesActivas'])
            ->whereNull('deleted_at')
            ->orderBy('nombre', 'asc')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="precios_venta_' . now()->format('Ymd_His') . '.csv"',
        ];

        $callback = function () use ($productos) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8

            fputcsv($file, [
                'ID',
                'Medicamento / Producto',
                'Código de Barras',
                'Categoría',
                'Laboratorio',
                'Costo Compra (C$)',
                'Precio Venta Base (C$)',
                'Margen Bruto (%)',
                'Presentaciones (Nombre / Factor / Precio)',
                'Estado Sanitario'
            ]);

            foreach ($productos as $p) {
                $costo = (float)($p->precio_compra ?? 0);
                $precio = (float)($p->precio_venta ?? 0);
                $margen = $precio > 0 ? round((($precio - $costo) / $precio) * 100, 1) . '%' : '0%';

                $presList = [];
                foreach ($p->presentacionesActivas as $pres) {
                    $presList[] = "{$pres->nombre} (x{$pres->unidades_por_presentacion}): C$ " . number_format($pres->precio_venta, 2);
                }

                fputcsv($file, [
                    $p->id,
                    $p->nombre,
                    $p->codigo_barra ?? 'S/C',
                    $p->categoria->nombre ?? 'Sin categoría',
                    $p->laboratorio->nombre ?? 'Sin laboratorio',
                    number_format($costo, 2),
                    number_format($precio, 2),
                    $margen,
                    implode(' | ', $presList),
                    $p->tipo_control ?? 'venta_libre',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * API Autocompletado / Búsqueda Asíncrona (AJAX) para Categoría, Laboratorio, Proveedor y Medicamento
     */
    public function apiBuscar(Request $request)
    {
        $tipo = (string)$request->get('tipo', 'producto');
        $q = trim((string)$request->get('q', ''));

        $results = $this->precioService->buscarParaAutocompletar($tipo, $q, 10);

        return response()->json(['results' => $results]);
    }
}
