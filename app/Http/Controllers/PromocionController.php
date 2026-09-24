<?php

namespace App\Http\Controllers;

use App\Models\Promocion;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Laboratorio;
use App\Models\AuditLog;
use App\Http\Requests\StorePromocionRequest;
use App\Http\Requests\UpdatePromocionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\QueryException;
use Exception;

class PromocionController extends Controller
{
    public function __construct()
    {
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
        $query = Promocion::with([
            'producto:id,nombre,precio_venta,codigo_barra',
            'categoria:id,nombre',
            'laboratorio:id,nombre',
        ]);

        // Filtro de búsqueda por texto
        if ($request->filled('buscar')) {
            $buscar = trim($request->input('buscar'));
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('descripcion', 'like', "%{$buscar}%")
                  ->orWhereHas('producto', function ($qp) use ($buscar) {
                      $qp->where('nombre', 'like', "%{$buscar}%")
                         ->orWhere('codigo_barra', 'like', "%{$buscar}%");
                  })
                  ->orWhereHas('categoria', function ($qc) use ($buscar) {
                      $qc->where('nombre', 'like', "%{$buscar}%");
                  })
                  ->orWhereHas('laboratorio', function ($ql) use ($buscar) {
                      $ql->where('nombre', 'like', "%{$buscar}%");
                  });
            });
        }

        // Filtro por tipo de beneficio
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->input('tipo'));
        }

        // Filtro por alcance
        if ($request->filled('alcance')) {
            $query->where('alcance', $request->input('alcance'));
        }

        // Filtro por estado / vigencia
        if ($request->filled('estado')) {
            $now = now();
            switch ($request->input('estado')) {
                case 'vigentes':
                    $query->where('activo', true)
                          ->where('fecha_inicio', '<=', $now)
                          ->where('fecha_fin', '>=', $now)
                          ->where(function ($q) {
                              $q->whereNull('stock_limite')
                                ->orWhereRaw('stock_consumido < stock_limite');
                          });
                    break;
                case 'programadas':
                    $query->where('activo', true)->where('fecha_inicio', '>', $now);
                    break;
                case 'vencidas':
                    $query->where('fecha_fin', '<', $now);
                    break;
                case 'inactivas':
                    $query->where('activo', false);
                    break;
            }
        }

        $promociones = $query->orderBy('activo', 'desc')
            ->orderBy('fecha_fin', 'asc')
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        // Estadísticas rápidas para las KPI cards
        $now = now();
        $stats = [
            'total'       => Promocion::count(),
            'vigentes'    => Promocion::vigentes()->count(),
            'programadas' => Promocion::where('activo', true)->where('fecha_inicio', '>', $now)->count(),
            'vencidas'    => Promocion::where('fecha_fin', '<', $now)->count(),
        ];

        return view('promociones.index', compact('promociones', 'stats'));
    }

    /**
     * Formulario de creación de promoción
     */
    public function create()
    {
        $categorias = Cache::remember('catalog_categorias_base', 300, function () {
            return Categoria::select(['id', 'nombre', 'codigo'])->activos()->orderBy('nombre')->get();
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
            $promocion = DB::transaction(function () use ($request) {
                $data = $request->validated();
                $data['activo'] = $request->boolean('activo', true);

                // Limpiar llaves foráneas no aplicables según el alcance seleccionado
                if ($data['alcance'] !== 'producto') $data['producto_id'] = null;
                if ($data['alcance'] !== 'categoria') $data['categoria_id'] = null;
                if ($data['alcance'] !== 'laboratorio') $data['laboratorio_id'] = null;

                $promo = Promocion::create($data);

                Log::info('Promoción registrada exitosamente', [
                    'promocion_id' => $promo->id,
                    'nombre'       => $promo->nombre,
                    'tipo'         => $promo->tipo,
                    'alcance'      => $promo->alcance,
                    'user_id'      => auth()->id(),
                ]);

                AuditLog::log('promociones', 'crear', "Promoción '{$promo->nombre}' ({$promo->badge_texto}) creada", [
                    'promocion_id' => $promo->id,
                    'tipo'         => $promo->tipo,
                    'alcance'      => $promo->alcance,
                    'valor'        => $promo->valor,
                ]);

                return $promo;
            });

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

        // Cargar productos alcanzados por la promoción
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
            return Categoria::select(['id', 'nombre', 'codigo'])->activos()->orderBy('nombre')->get();
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
            DB::transaction(function () use ($request, $promocion) {
                $locked = Promocion::where('id', $promocion->id)->lockForUpdate()->firstOrFail();

                $data = $request->validated();
                $data['activo'] = $request->boolean('activo', true);

                if ($data['alcance'] !== 'producto') $data['producto_id'] = null;
                if ($data['alcance'] !== 'categoria') $data['categoria_id'] = null;
                if ($data['alcance'] !== 'laboratorio') $data['laboratorio_id'] = null;

                $locked->update($data);

                Log::info('Promoción actualizada', [
                    'promocion_id' => $locked->id,
                    'nombre'       => $locked->nombre,
                    'user_id'      => auth()->id(),
                ]);

                AuditLog::log('promociones', 'actualizar', "Promoción '{$locked->nombre}' actualizada", [
                    'promocion_id' => $locked->id,
                    'cambios'      => array_keys($data),
                ]);
            });

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
            $nuevoEstado = DB::transaction(function () use ($promocion) {
                $locked = Promocion::where('id', $promocion->id)->lockForUpdate()->firstOrFail();
                $nuevo = !$locked->activo;
                $locked->update(['activo' => $nuevo]);

                AuditLog::log('promociones', $nuevo ? 'activar' : 'desactivar', "Promoción '{$locked->nombre}' " . ($nuevo ? 'activada' : 'desactivada'), [
                    'promocion_id' => $locked->id,
                ]);

                return $nuevo;
            });

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'activo'  => $nuevoEstado,
                    'mensaje' => "Promoción " . ($nuevoEstado ? 'activada' : 'desactivada') . " correctamente."
                ]);
            }

            return back()->with('success', "Promoción '{$promocion->nombre}' " . ($nuevoEstado ? 'activada' : 'desactivada') . " correctamente.");
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
            $id = $promocion->id;

            $promocion->delete();

            AuditLog::log('promociones', 'eliminar', "Promoción '{$nombre}' eliminada", [
                'promocion_id' => $id,
            ]);

            return redirect()->route('promociones.index')
                ->with('success', "Promoción '{$nombre}' eliminada correctamente.");
        } catch (Exception $e) {
            return back()->with('error', 'Error al eliminar la promoción: ' . $e->getMessage());
        }
    }
}
