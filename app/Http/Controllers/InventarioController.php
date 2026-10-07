<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Lote;
use App\Models\Proveedor;
use App\Models\Laboratorio;
use App\Models\Categoria;
use App\Models\MovimientoInventario;
use App\Models\AuditLog;
use App\Services\InventarioService;
use App\Http\Requests\AjusteInventarioRequest;
use App\Http\Requests\StoreLoteManualRequest;
use App\Http\Requests\UpdateLoteRequest;
use App\Support\RequestCache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use Exception;

class InventarioController extends Controller
{
    protected InventarioService $inventarioService;

    public function __construct(InventarioService $inventarioService)
    {
        $this->inventarioService = $inventarioService;
        $this->middleware('permission:ver movimientos inventario')->only(['index', 'movimientos', 'lotes', 'kardexProducto', 'alertas']);
        $this->middleware('permission:ajustar inventario')->only(['ajustar', 'storeAjuste', 'bajaVencidos', 'createLote', 'storeLote']);
        $this->middleware('permission:editar lotes|ajustar inventario')->only(['updateLote']);
    }

    public function index()
    {
        // Valorización del inventario completa (SUM sobre lotes) — cacheada 60s
        // Se invalida con Cache::forget('inventario_valorizacion') en movimientos de stock
        $valorizacion = Cache::remember('inventario_valorizacion', 60, function () {
            return $this->inventarioService->valorizacionInventario(true);
        });
        $productosBajoStock = $this->inventarioService->productosConStockBajo();
        $lotesPorVencer    = $this->inventarioService->lotesProximosVencer(60);
        $lotesVencidos     = $this->inventarioService->lotesVencidos();

        return view('inventario.index', compact('valorizacion', 'productosBajoStock', 'lotesPorVencer', 'lotesVencidos'));
    }

    public function movimientos(Request $request)
    {
        $query = MovimientoInventario::with([
            'producto:id,nombre,principio_activo,laboratorio_id',
            'producto.laboratorio:id,nombre',
            'lote:id,numero_lote,fecha_vencimiento',
            'lote.detallesCompra',
            'usuario:id,name',
        ]);


        if ($request->filled('producto_id')) {
            $query->where('producto_id', $request->input('producto_id'));
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->input('tipo'));
        }

        if ($request->filled('subtipo')) {
            $query->where('subtipo', $request->input('subtipo'));
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_movimiento', '>=', $request->input('fecha_desde'));
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_movimiento', '<=', $request->input('fecha_hasta'));
        }

        $movimientos = $query->orderBy('fecha_movimiento', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(perPage(20))
            ->withQueryString();

        $productos = RequestCache::rememberStatic('productos:activos:select', fn () => Producto::activos()->orderBy('nombre')->get(['id', 'nombre']));

        return view('inventario.movimientos', compact('movimientos', 'productos'));
    }

    public function lotes(Request $request)
    {
        $query = Lote::with([
            'producto:id,nombre,principio_activo,categoria_id,laboratorio_id',
            'producto.categoria:id,nombre',
            'producto.laboratorio:id,nombre',
            'proveedor:id,nombre',
            'detallesCompra',
        ])->where('activo', true);

        if ($request->filled('buscar')) {
            $query->buscar($request->input('buscar'));
        }

        if ($request->filled('filtro_vencimiento')) {
            if ($request->input('filtro_vencimiento') === 'vencidos') {
                $query->vencidos();
            } elseif ($request->input('filtro_vencimiento') === 'proximos_30') {
                $query->proximosVencer(30);
            } elseif ($request->input('filtro_vencimiento') === 'proximos_60') {
                $query->proximosVencer(60);
            }
        }

        $orden = $request->input('orden', 'vencimiento_asc');
        $query->ordenarPor($orden);

        $lotes = $query->paginate(perPage(15))->withQueryString();
        $proveedores = Proveedor::getCachedActivos();

        return view('inventario.lotes', compact('lotes', 'proveedores'));
    }

    /**
     * Actualizar metadatos de un lote (número de lote, fecha de vencimiento, proveedor)
     */
    public function updateLote(UpdateLoteRequest $request, Lote $lote)
    {
        try {
            $this->inventarioService->actualizarMetadatosLote($lote, $request->validated());

            return redirect()->route('inventario.lotes')
                ->with('success', "Los metadatos del lote '{$lote->numero_lote}' han sido actualizados exitosamente con auditoría.");
        } catch (Exception $e) {
            Log::error('Error al actualizar metadatos del lote', [
                'lote_id' => $lote->id,
                'user_id' => auth()->id(),
                'error'   => $e->getMessage(),
            ]);

            return back()->withInput()->with('error', 'Error al actualizar metadatos del lote: ' . $e->getMessage());
        }
    }

    public function kardexProducto(Producto $producto, Request $request)
    {
        $fechaDesde = $request->input('fecha_desde');
        $fechaHasta = $request->input('fecha_hasta');

        $movimientos = $this->inventarioService->kardexProducto($producto->id, $fechaDesde, $fechaHasta);
        $producto->load([
            'categoria:id,nombre',
            'laboratorio:id,nombre',
            'presentacionesActivas',
            'lotes' => fn ($q) => $q->orderBy('fecha_vencimiento', 'asc'),
            'lotes.detallesCompra',
            'lotes.compra:id,codigo_compra',
        ]);

        return view('inventario.kardex-producto', compact('producto', 'movimientos'));
    }

    public function alertas()
    {
        $productosBajoStock = $this->inventarioService->productosConStockBajo();
        $lotesPorVencer = $this->inventarioService->lotesProximosVencer(60);
        $lotesVencidos = $this->inventarioService->lotesVencidos();

        return view('inventario.alertas', compact('productosBajoStock', 'lotesPorVencer', 'lotesVencidos'));
    }

    public function ajustar()
    {
        $lotes = Lote::select(['id', 'producto_id', 'numero_lote', 'stock_actual', 'fecha_vencimiento'])
            ->with(['producto:id,nombre,principio_activo'])
            ->where('activo', true)
            ->where('stock_actual', '>', 0)
            ->orderBy('numero_lote', 'asc')
            ->get();

        return view('inventario.ajustar', compact('lotes'));
    }

    public function storeAjuste(AjusteInventarioRequest $request)
    {
        try {
            $movimiento = $this->inventarioService->ajustarInventario($request->validated());

            AuditLog::log('inventario', 'ajuste', "Ajuste de inventario en lote {$movimiento->lote->numero_lote} ({$movimiento->producto->nombre})", [
                'movimiento_id' => $movimiento->id,
                'tipo' => $movimiento->tipo,
                'cantidad' => $movimiento->cantidad,
                'motivo' => $movimiento->motivo,
            ]);

            return redirect()->route('inventario.movimientos')
                ->with('success', "Ajuste de inventario aplicado exitosamente en el Kardex para el lote '{$movimiento->lote->numero_lote}' ({$movimiento->producto->nombre}).");
        } catch (QueryException $qe) {
            Log::error('Error de base de datos en ajuste de inventario', [
                'user_id' => auth()->id(),
                'payload' => $request->except(['_token']),
                'message' => $qe->getMessage(),
            ]);

            return back()->withInput()->with('error', 'Error al procesar el ajuste en la base de datos debido a un conflicto de concurrencia o integridad.');
        } catch (Exception $e) {
            Log::error('Excepción en ajuste de inventario', [
                'user_id' => auth()->id(),
                'payload' => $request->except(['_token']),
                'message' => $e->getMessage(),
            ]);

            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Dar de baja automática a lotes vencidos
     */
    public function bajaVencidos()
    {
        try {
            $totalBajas = $this->inventarioService->desactivarLotesVencidos();

            AuditLog::log('inventario', 'baja_vencidos', "Baja automática de {$totalBajas} lote(s) vencido(s)", [
                'total_bajas' => $totalBajas,
            ]);

            return redirect()->route('inventario.alertas')
                ->with('success', "Se procesó la baja automática de {$totalBajas} lote(s) vencido(s) con registro en Kardex.");
        } catch (Exception $e) {
            Log::error('Error al ejecutar baja de lotes vencidos', [
                'user_id' => auth()->id(),
                'message' => $e->getMessage(),
            ]);

            return back()->with('error', 'Error al procesar la baja de lotes vencidos: ' . $e->getMessage());
        }
    }

    /**
     * Formulario para crear un lote manualmente (stock de apertura, migración, donaciones, etc.)
     */
    public function createLote(Request $request)
    {
        $productos = RequestCache::rememberStatic('productos:activos:select_detallado', fn () => Producto::activos()->orderBy('nombre')->get(['id', 'nombre', 'principio_activo']));
        $proveedores = Proveedor::getCachedActivos();
        $productoPreseleccionado = $request->filled('producto_id')
            ? Producto::find($request->input('producto_id'))
            : null;

        return view('inventario.lote-crear', compact('productos', 'proveedores', 'productoPreseleccionado'));
    }

    /**
     * Guardar lote manual con movimiento de entrada en Kardex
     */
    public function storeLote(StoreLoteManualRequest $request)
    {
        try {
            $lote = $this->inventarioService->crearLoteManual($request->validated());

            return redirect()->route('inventario.lotes')
                ->with('success', "Lote '{$lote->numero_lote}' de {$lote->producto->nombre} creado con {$lote->stock_actual} unidades en inventario.");

        } catch (Exception $e) {
            Log::error('Error al crear lote manual', [
                'user_id' => auth()->id(),
                'payload' => $request->except(['_token']),
                'message' => $e->getMessage(),
            ]);

            return back()->withInput()->with('error', 'Error al crear el lote: ' . $e->getMessage());
        }
    }

    /**
     * Búsqueda AJAX de Medicamentos (Componente C)
     */
    public function buscarMedicamentosAjax(Request $request)
    {
        $q = trim((string)$request->input('q', ''));
        $productoService = app(\App\Services\ProductoService::class);
        return response()->json($productoService->buscarAjax($q, 10));
    }

    /**
     * Búsqueda AJAX de Proveedores (Componente C)
     */
    public function buscarProveedoresAjax(Request $request)
    {
        $q = trim((string)$request->input('q', ''));
        $proveedorService = app(\App\Services\ProveedorService::class);
        return response()->json($proveedorService->buscarAjax($q, 15));
    }

    /**
     * Búsqueda AJAX de Laboratorios (Componente C)
     */
    public function buscarLaboratoriosAjax(Request $request)
    {
        $q = trim((string)$request->input('q', ''));
        $laboratorioService = app(\App\Services\LaboratorioService::class);
        return response()->json($laboratorioService->buscarAjax($q, 15));
    }

    /**
     * Búsqueda AJAX de Categorías (Componente C)
     */
    public function buscarCategoriasAjax(Request $request)
    {
        $q = trim((string)$request->input('q', ''));
        $categoriaService = app(\App\Services\CategoriaService::class);
        return response()->json($categoriaService->buscarAjax($q, 15));
    }
}
