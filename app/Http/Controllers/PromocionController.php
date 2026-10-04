<?php

namespace App\Http\Controllers;

use App\Models\Promocion;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Laboratorio;
use App\Http\Requests\StorePromocionRequest;
use App\Http\Requests\UpdatePromocionRequest;
use App\Services\PromocionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use Exception;

class PromocionController extends Controller
{
    public function __construct(
        protected PromocionService $promocionService
    ) {
        $this->middleware('permission:ver promociones')->only(['index', 'show']);
        $this->middleware('permission:crear promociones')->only(['create', 'store']);
        $this->middleware('permission:editar promociones')->only(['edit', 'update', 'toggleActivo']);
        $this->middleware('permission:desactivar promociones')->only(['destroy']);
    }

    /**
     * Listado general de promociones y descuentos con métricas y filtros
     */
    public function index(Request $request)
    {
        $filtros = $request->only(['buscar', 'tipo', 'alcance', 'estado']);
        $promociones = $this->promocionService->listarPromociones($filtros, perPage(15))->withQueryString();
        $stats = $this->promocionService->obtenerEstadisticas();

        return view('promociones.index', compact('promociones', 'stats'));
    }

    /**
     * Formulario de creación de promoción
     */
    public function create()
    {
        $categorias = Cache::remember('catalog_categorias_base', 300, function () {
            return Categoria::select(['id', 'nombre'])->activos()->orderBy('nombre')->get();
        });

        $laboratorios = Cache::remember('catalog_laboratorios_base', 300, function () {
            return Laboratorio::select(['id', 'nombre', 'codigo'])->activos()->orderBy('nombre')->get();
        });

        $productos = Producto::select(['id', 'nombre', 'concentracion', 'precio_venta', 'codigo_barra'])
            ->activos()
            ->orderBy('nombre')
            ->get();

        return view('promociones.create', compact('categorias', 'laboratorios', 'productos'));
    }

    /**
     * Guardar una nueva promoción
     */
    public function store(StorePromocionRequest $request)
    {
        try {
            $data = $request->validated();
            $data['activo'] = $request->boolean('activo', true);

            $promocion = $this->promocionService->crearPromocion($data);

            if ($request->boolean('crear_otro')) {
                return redirect()->route('promociones.create')
                    ->with('success', "Promoción '{$promocion->nombre}' configurada y activada exitosamente.");
            }

            return redirect()->route('promociones.index')
                ->with('success', "Promoción '{$promocion->nombre}' configurada y activada exitosamente.");
        } catch (QueryException $qe) {
            Log::error('Error de base de datos al crear promoción', [
                'user_id' => auth()->id(),
                'message' => $qe->getMessage(),
            ]);

            return back()->withInput()->with('error', 'No se pudo guardar la promoción debido a un conflicto de datos.');
        } catch (Exception $e) {
            Log::error('Error general al crear promoción', [
                'user_id' => auth()->id(),
                'message' => $e->getMessage(),
            ]);

            return back()->withInput()->with('error', 'Error al registrar la promoción: ' . $e->getMessage());
        }
    }

    /**
     * Vista de detalle de una promoción
     */
    public function show(Promocion $promocion)
    {
        $promocion->load([
            'producto.laboratorio',
            'categoria',
            'laboratorio',
        ]);

        $productosAfectados = collect();
        if ($promocion->alcance === 'producto' && $promocion->producto) {
            $productosAfectados = collect([$promocion->producto]);
        } elseif ($promocion->alcance === 'categoria' && $promocion->categoria_id) {
            $productosAfectados = Producto::where('categoria_id', $promocion->categoria_id)->activos()->take(20)->get();
        } elseif ($promocion->alcance === 'laboratorio' && $promocion->laboratorio_id) {
            $productosAfectados = Producto::where('laboratorio_id', $promocion->laboratorio_id)->activos()->take(20)->get();
        } elseif ($promocion->alcance === 'general') {
            $productosAfectados = Producto::activos()->take(20)->get();
        }

        return view('promociones.show', compact('promocion', 'productosAfectados'));
    }

    /**
     * Formulario de edición de promoción
     */
    public function edit(Promocion $promocion)
    {
        $categorias = Cache::remember('catalog_categorias_base', 300, function () {
            return Categoria::select(['id', 'nombre'])->activos()->orderBy('nombre')->get();
        });

        $laboratorios = Cache::remember('catalog_laboratorios_base', 300, function () {
            return Laboratorio::select(['id', 'nombre', 'codigo'])->activos()->orderBy('nombre')->get();
        });

        $productos = Producto::select(['id', 'nombre', 'concentracion', 'precio_venta', 'codigo_barra'])
            ->activos()
            ->orderBy('nombre')
            ->get();

        return view('promociones.edit', compact('promocion', 'categorias', 'laboratorios', 'productos'));
    }

    /**
     * Actualizar una promoción existente
     */
    public function update(UpdatePromocionRequest $request, Promocion $promocion)
    {
        try {
            $data = $request->validated();
            $data['activo'] = $request->boolean('activo', true);

            $this->promocionService->actualizarPromocion($promocion, $data);

            return redirect()->route('promociones.index')
                ->with('success', "Promoción '{$promocion->nombre}' actualizada exitosamente.");
        } catch (QueryException $qe) {
            Log::error('Error de base de datos al actualizar promoción', [
                'promocion_id' => $promocion->id,
                'user_id'      => auth()->id(),
                'message'      => $qe->getMessage(),
            ]);

            return back()->withInput()->with('error', 'No se pudo actualizar la promoción debido a un conflicto en la base de datos.');
        } catch (Exception $e) {
            Log::error('Error al actualizar promoción', [
                'promocion_id' => $promocion->id,
                'user_id'      => auth()->id(),
                'message'      => $e->getMessage(),
            ]);

            return back()->withInput()->with('error', 'Error al actualizar la promoción: ' . $e->getMessage());
        }
    }

    /**
     * Alternar estado activo / inactivo de la promoción
     */
    public function toggleActivo(Request $request, Promocion $promocion)
    {
        try {
            $estadoTexto = $this->promocionService->cambiarEstado($promocion);
            $esActivo = $promocion->fresh()->activo;

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'activo'  => $esActivo,
                    'mensaje' => "Promoción {$estadoTexto} correctamente."
                ]);
            }

            return back()->with('success', "Promoción '{$promocion->nombre}' {$estadoTexto} correctamente.");
        } catch (Exception $e) {
            return back()->with('error', 'No se pudo cambiar el estado de la promoción: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar promoción (Soft Delete)
     */
    public function destroy(Promocion $promocion)
    {
        try {
            $nombre = $promocion->nombre;
            $this->promocionService->eliminarPromocion($promocion);

            return redirect()->route('promociones.index')
                ->with('success', "Promoción '{$nombre}' eliminada correctamente.");
        } catch (Exception $e) {
            return back()->with('error', 'Error al eliminar la promoción: ' . $e->getMessage());
        }
    }
}
