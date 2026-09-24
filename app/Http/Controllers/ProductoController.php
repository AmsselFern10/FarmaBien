<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Laboratorio;
use App\Models\PresentacionProducto;
use App\Models\AuditLog;
use App\Http\Requests\StoreProductoRequest;
use App\Http\Requests\UpdateProductoRequest;
use App\Services\FarmaIaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\QueryException;
use Exception;

class ProductoController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:ver productos')->only(['index', 'show', 'iaBuscar', 'iaFicha']);
        $this->middleware('permission:crear productos')->only(['create', 'store']);
        $this->middleware('permission:editar productos')->only(['edit', 'update']);
        $this->middleware('permission:desactivar productos')->only(['destroy']);
    }

    public function index(Request $request, FarmaIaService $iaService)
    {
        $query = Producto::with(['categoria:id,nombre', 'laboratorio:id,nombre', 'presentacionesActivas'])
            ->withSum(['lotes as stock_total' => function ($q) {
                $q->where('activo', true);
            }], 'stock_actual');

        if ($request->filled('buscar')) {
            $buscar = trim($request->input('buscar'));
            
            // Principios activos sugeridos en memoria según lenguaje natural / síntomas
            $principiosIa = $iaService->obtenerPrincipiosPorSintoma($buscar);

            $query->where(function ($q) use ($buscar, $principiosIa) {
                $q->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('principio_activo', 'like', "%{$buscar}%")
                  ->orWhere('descripcion', 'like', "%{$buscar}%")
                  ->orWhere('codigo_barra', 'like', "%{$buscar}%");

                if (!empty($principiosIa)) {
                    foreach ($principiosIa as $pActivo) {
                        $q->orWhere('principio_activo', 'like', "%{$pActivo}%");
                    }
                }
            });
        }

        if ($request->filled('categoria_id')) {
            $query->where('categoria_id', $request->input('categoria_id'));
        }

        if ($request->filled('laboratorio_id')) {
            $query->where('laboratorio_id', $request->input('laboratorio_id'));
        }

        if ($request->filled('tipo_control')) {
            $query->where('tipo_control', $request->input('tipo_control'));
        }

        if ($request->boolean('bajo_stock')) {
            $query->bajoStock();
        }

        $productos = $query->orderBy('nombre', 'asc')->paginate(12)->withQueryString();
        
        $categorias = Cache::remember('catalog_categorias_base', 300, function () {
            return Categoria::select(['id', 'nombre'])->activos()->orderBy('nombre')->get();
        });

        $laboratorios = Cache::remember('catalog_laboratorios_base', 300, function () {
            return Laboratorio::select(['id', 'nombre', 'codigo'])->activos()->orderBy('nombre')->get();
        });

        return view('productos.index', compact('productos', 'categorias', 'laboratorios'));
    }

    /**
     * Endpoint API para la Lupa Inteligente (búsqueda semántica)
     */
    public function iaBuscar(Request $request, FarmaIaService $iaService)
    {
        $q = (string)$request->input('q', '');
        $results = $iaService->buscarSemantica($q);

        return response()->json([
            'ok' => true,
            'query' => $q,
            'count' => count($results),
            'results' => $results,
        ]);
    }

    /**
     * Endpoint API para la Ficha Clínica con IA del producto
     */
    public function iaFicha(Producto $producto, FarmaIaService $iaService)
    {
        $ficha = $iaService->generarFichaProducto($producto);

        return response()->json($ficha);
    }

    public function create()
    {
        $categorias = Cache::remember('catalog_categorias_base', 300, function () {
            return Categoria::select(['id', 'nombre'])->activos()->orderBy('nombre')->get();
        });

        $laboratorios = Cache::remember('catalog_laboratorios_base', 300, function () {
            return Laboratorio::select(['id', 'nombre', 'codigo'])->activos()->orderBy('nombre')->get();
        });

        return view('productos.create', compact('categorias', 'laboratorios'));
    }

    public function store(StoreProductoRequest $request)
    {
        $uploadedPath = null;

        try {
            $producto = DB::transaction(function () use ($request, &$uploadedPath) {
                $data = $request->validated();

                // Manejo seguro y optimizado de archivo de imagen (conversión automática a WebP)
                if ($request->hasFile('imagen')) {
                    $uploadedPath = app(\App\Services\ImageOptimizerService::class)->optimizarYGuardarWebp($request->file('imagen'));
                    $data['imagen'] = $uploadedPath;
                }

                $producto = Producto::create($data);

                // Procesamiento atómico de presentaciones comerciales
                $presentaciones = $request->input('presentaciones', []);
                if (empty($presentaciones)) {
                    PresentacionProducto::create([
                        'producto_id' => $producto->id,
                        'nombre' => 'Unidad Base',
                        'descripcion' => 'Unidad individual / pastilla / ampolla',
                        'unidades_por_presentacion' => 1,
                        'precio_compra' => $producto->precio_compra,
                        'precio_venta' => $producto->precio_venta,
                        'es_unidad_base' => true,
                        'activo' => true,
                        'orden' => 1,
                    ]);
                } else {
                    $orden = 1;
                    $hasBase = false;
                    foreach ($presentaciones as $p) {
                        if (empty($p['nombre'])) continue;
                        $esBase = !empty($p['es_unidad_base']) || ((int)($p['unidades_por_presentacion'] ?? 1) === 1 && !$hasBase);
                        if ($esBase) $hasBase = true;

                        PresentacionProducto::create([
                            'producto_id' => $producto->id,
                            'nombre' => trim($p['nombre']),
                            'descripcion' => !empty($p['descripcion']) ? trim($p['descripcion']) : null,
                            'unidades_por_presentacion' => max(1, (int)($p['unidades_por_presentacion'] ?? 1)),
                            'precio_compra' => !empty($p['precio_compra']) ? (float)$p['precio_compra'] : ($esBase ? $producto->precio_compra : null),
                            'precio_venta' => !empty($p['precio_venta']) ? (float)$p['precio_venta'] : ($esBase ? $producto->precio_venta : null),
                            'codigo_barras' => !empty($p['codigo_barras']) ? trim($p['codigo_barras']) : null,
                            'es_unidad_base' => $esBase,
                            'activo' => true,
                            'orden' => $orden++,
                        ]);
                    }

                    // Garantizar que siempre exista al menos una presentación como unidad base
                    if (!$hasBase) {
                        PresentacionProducto::create([
                            'producto_id' => $producto->id,
                            'nombre' => 'Unidad Base',
                            'unidades_por_presentacion' => 1,
                            'precio_compra' => $producto->precio_compra,
                            'precio_venta' => $producto->precio_venta,
                            'es_unidad_base' => true,
                            'activo' => true,
                            'orden' => 0,
                        ]);
                    }
                }

                Log::info('Producto registrado exitosamente en el catálogo', [
                    'producto_id' => $producto->id,
                    'nombre' => $producto->nombre,
                    'codigo_barra' => $producto->codigo_barra,
                    'user_id' => auth()->id(),
                ]);

                AuditLog::log('productos', 'crear', "Medicamento '{$producto->nombre}' registrado en catálogo", [
                    'producto_id' => $producto->id,
                    'codigo_barra' => $producto->codigo_barra,
                    'categoria_id' => $producto->categoria_id,
                    'laboratorio_id' => $producto->laboratorio_id,
                ]);

                Cache::forget('dashboard_stock_critico_count');

                return $producto;
            });

            return redirect()->route('productos.index')
                ->with('success', "Producto '{$producto->nombre}' y sus presentaciones fueron registrados exitosamente.");
        } catch (QueryException $qe) {
            if ($uploadedPath && Storage::disk('public')->exists($uploadedPath)) {
                Storage::disk('public')->delete($uploadedPath);
            }

            Log::error('Error de base de datos al registrar producto', [
                'user_id' => auth()->id(),
                'error_code' => $qe->getCode(),
                'message' => $qe->getMessage(),
            ]);

            return back()->withInput()->with('error', 'No se pudo guardar el medicamento debido a un conflicto de datos o duplicidad en la base de datos.');
        } catch (Exception $e) {
            if ($uploadedPath && Storage::disk('public')->exists($uploadedPath)) {
                Storage::disk('public')->delete($uploadedPath);
            }

            Log::error('Error general al registrar producto', [
                'user_id' => auth()->id(),
                'message' => $e->getMessage(),
            ]);

            return back()->withInput()->with('error', 'Error al guardar el producto: ' . $e->getMessage());
        }
    }

    public function show(Producto $producto)
    {
        $producto->load([
            'categoria',
            'laboratorio',
            'presentaciones',
            'lotes' => function ($q) {
                $q->orderBy('fecha_vencimiento', 'asc');
            }
        ]);

        return view('productos.show', compact('producto'));
    }

    public function edit(Producto $producto)
    {
        $categorias = Cache::remember('catalog_categorias_base', 300, function () {
            return Categoria::select(['id', 'nombre'])->activos()->orderBy('nombre')->get();
        });

        $laboratorios = Cache::remember('catalog_laboratorios_base', 300, function () {
            return Laboratorio::select(['id', 'nombre', 'codigo'])->activos()->orderBy('nombre')->get();
        });

        $producto->load('presentaciones');

        return view('productos.edit', compact('producto', 'categorias', 'laboratorios'));
    }

    public function update(UpdateProductoRequest $request, Producto $producto)
    {
        $uploadedPath = null;
        $oldImagePath = $producto->imagen;

        try {
            DB::transaction(function () use ($request, $producto, &$uploadedPath) {
                // Bloqueo pesimista para evitar colisiones concurrentes de actualización
                $lockedProducto = Producto::where('id', $producto->id)->lockForUpdate()->firstOrFail();

                $data = $request->validated();

                if ($request->hasFile('imagen')) {
                    $uploadedPath = app(\App\Services\ImageOptimizerService::class)->optimizarYGuardarWebp($request->file('imagen'));
                    $data['imagen'] = $uploadedPath;
                }

                $lockedProducto->update($data);

                // Sincronización Segura de Presentaciones (Preserva integridad referencial en ventas y compras)
                if ($request->has('presentaciones')) {
                    $presentacionesInput = $request->input('presentaciones', []);
                    $processedIds = [];
                    $orden = 1;
                    $hasBase = false;

                    foreach ($presentacionesInput as $p) {
                        if (empty($p['nombre'])) continue;

                        $esBase = !empty($p['es_unidad_base']) || ((int)($p['unidades_por_presentacion'] ?? 1) === 1 && !$hasBase);
                        if ($esBase) $hasBase = true;

                        $presentacionData = [
                            'producto_id' => $lockedProducto->id,
                            'nombre' => trim($p['nombre']),
                            'descripcion' => !empty($p['descripcion']) ? trim($p['descripcion']) : null,
                            'unidades_por_presentacion' => max(1, (int)($p['unidades_por_presentacion'] ?? 1)),
                            'precio_compra' => !empty($p['precio_compra']) ? (float)$p['precio_compra'] : ($esBase ? $lockedProducto->precio_compra : null),
                            'precio_venta' => !empty($p['precio_venta']) ? (float)$p['precio_venta'] : ($esBase ? $lockedProducto->precio_venta : null),
                            'codigo_barras' => !empty($p['codigo_barras']) ? trim($p['codigo_barras']) : null,
                            'es_unidad_base' => $esBase,
                            'activo' => true,
                            'orden' => $orden++,
                        ];

                        if (!empty($p['id'])) {
                            // Actualizar presentación existente
                            $existing = PresentacionProducto::where('id', $p['id'])
                                ->where('producto_id', $lockedProducto->id)
                                ->lockForUpdate()
                                ->first();

                            if ($existing) {
                                $existing->update($presentacionData);
                                $processedIds[] = $existing->id;
                            } else {
                                $newP = PresentacionProducto::create($presentacionData);
                                $processedIds[] = $newP->id;
                            }
                        } else {
                            // Crear nueva presentación
                            $newP = PresentacionProducto::create($presentacionData);
                            $processedIds[] = $newP->id;
                        }
                    }

                    // Para presentaciones eliminadas por el usuario:
                    // Si tienen historial financiero en ventas o compras, se desactivan (activo = false) para no romper relaciones.
                    // Si no tienen historial, se eliminan con seguridad.
                    $presentacionesNoEnviadas = PresentacionProducto::where('producto_id', $lockedProducto->id)
                        ->whereNotIn('id', $processedIds)
                        ->get();

                    foreach ($presentacionesNoEnviadas as $pBorrar) {
                        $tieneVentas = $pBorrar->detallesVentas()->exists();
                        $tieneCompras = $pBorrar->detallesCompras()->exists();

                        if ($tieneVentas || $tieneCompras) {
                            $pBorrar->update(['activo' => false]);
                        } else {
                            $pBorrar->delete();
                        }
                    }

                    // Asegurar que siempre haya una unidad base
                    if (!$hasBase && count($processedIds) > 0) {
                        PresentacionProducto::where('id', $processedIds[0])->update(['es_unidad_base' => true]);
                    }
                }

                Log::info('Producto actualizado exitosamente', [
                    'producto_id' => $lockedProducto->id,
                    'nombre' => $lockedProducto->nombre,
                    'user_id' => auth()->id(),
                ]);

                AuditLog::log('productos', 'actualizar', "Medicamento '{$lockedProducto->nombre}' actualizado", [
                    'producto_id' => $lockedProducto->id,
                    'cambios' => array_keys($data),
                ]);

                Cache::forget('dashboard_stock_critico_count');
            });

            // Limpieza segura de imagen anterior solo tras confirmación exitosa en BD
            if ($uploadedPath && $oldImagePath && Storage::disk('public')->exists($oldImagePath)) {
                Storage::disk('public')->delete($oldImagePath);
            }

            return redirect()->route('productos.index')
                ->with('success', "Medicamento '{$producto->nombre}' actualizado correctamente.");
        } catch (QueryException $qe) {
            if ($uploadedPath && Storage::disk('public')->exists($uploadedPath)) {
                Storage::disk('public')->delete($uploadedPath);
            }

            Log::error('Error de base de datos al actualizar producto', [
                'producto_id' => $producto->id,
                'user_id' => auth()->id(),
                'message' => $qe->getMessage(),
            ]);

            return back()->withInput()->with('error', 'No se pudo actualizar el medicamento debido a un conflicto de unicidad o integridad en la base de datos.');
        } catch (Exception $e) {
            if ($uploadedPath && Storage::disk('public')->exists($uploadedPath)) {
                Storage::disk('public')->delete($uploadedPath);
            }

            Log::error('Error general al actualizar producto', [
                'producto_id' => $producto->id,
                'user_id' => auth()->id(),
                'message' => $e->getMessage(),
            ]);

            return back()->withInput()->with('error', 'Error al actualizar el producto: ' . $e->getMessage());
        }
    }

    public function destroy(Producto $producto)
    {
        try {
            $estado = DB::transaction(function () use ($producto) {
                // Bloqueo pesimista para evitar condiciones de carrera al activar/desactivar
                $locked = Producto::where('id', $producto->id)->lockForUpdate()->firstOrFail();
                $nuevoEstado = !$locked->activo;
                $locked->update(['activo' => $nuevoEstado]);

                Log::info('Estado de producto modificado', [
                    'producto_id' => $locked->id,
                    'nombre' => $locked->nombre,
                    'nuevo_estado' => $nuevoEstado ? 'activado' : 'desactivado',
                    'user_id' => auth()->id(),
                ]);

                AuditLog::log(
                    'productos',
                    $nuevoEstado ? 'activar' : 'desactivar',
                    "Medicamento '{$locked->nombre}' " . ($nuevoEstado ? 'activado' : 'desactivado'),
                    ['producto_id' => $locked->id]
                );

                Cache::forget('dashboard_stock_critico_count');

                return $nuevoEstado ? 'activado' : 'desactivado';
            });

            return redirect()->route('productos.index')
                ->with('success', "Producto '{$producto->nombre}' {$estado} correctamente.");
        } catch (Exception $e) {
            Log::error('Error al modificar estado de producto', [
                'producto_id' => $producto->id,
                'user_id' => auth()->id(),
                'message' => $e->getMessage(),
            ]);

            return back()->with('error', 'No se pudo cambiar el estado del medicamento: ' . $e->getMessage());
        }
    }
}
