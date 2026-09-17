<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Laboratorio;
use App\Models\PresentacionProducto;
use App\Http\Requests\StoreProductoRequest;
use App\Http\Requests\UpdateProductoRequest;
use App\Services\FarmaIaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
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
        $query = Producto::with(['categoria', 'laboratorio', 'presentacionesActivas'])
            ->withSum(['lotes as stock_total' => function ($q) {
                $q->where('activo', true);
            }], 'stock_actual');

        if ($request->filled('buscar')) {
            $buscar = trim($request->input('buscar'));
            
            // Si el término es una consulta en lenguaje natural / síntoma, obtener principios sugeridos
            $iaMatches = $iaService->buscarSemantica($buscar);
            $iaIds = !empty($iaMatches) ? array_column($iaMatches, 'id') : [];

            $query->where(function ($q) use ($buscar, $iaIds) {
                $q->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('principio_activo', 'like', "%{$buscar}%")
                  ->orWhere('descripcion', 'like', "%{$buscar}%")
                  ->orWhere('codigo_barra', 'like', "%{$buscar}%");

                if (!empty($iaIds)) {
                    $q->orWhereIn('id', $iaIds);
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
        $categorias = Categoria::activos()->orderBy('nombre')->get();
        $laboratorios = Laboratorio::activos()->orderBy('nombre')->get();

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
        $categorias = Categoria::activos()->orderBy('nombre')->get();
        $laboratorios = Laboratorio::activos()->orderBy('nombre')->get();

        return view('productos.create', compact('categorias', 'laboratorios'));
    }

    public function store(StoreProductoRequest $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $data = $request->validated();

                if ($request->hasFile('imagen')) {
                    $path = $request->file('imagen')->store('productos', 'public');
                    $data['imagen'] = $path;
                }

                $producto = Producto::create($data);

                // Guardar presentaciones comerciales
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
                            'nombre' => $p['nombre'],
                            'descripcion' => $p['descripcion'] ?? null,
                            'unidades_por_presentacion' => max(1, (int)($p['unidades_por_presentacion'] ?? 1)),
                            'precio_compra' => !empty($p['precio_compra']) ? $p['precio_compra'] : ($esBase ? $producto->precio_compra : null),
                            'precio_venta' => !empty($p['precio_venta']) ? $p['precio_venta'] : ($esBase ? $producto->precio_venta : null),
                            'codigo_barras' => $p['codigo_barras'] ?? null,
                            'es_unidad_base' => $esBase,
                            'activo' => true,
                            'orden' => $orden++,
                        ]);
                    }
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
            });

            return redirect()->route('productos.index')
                ->with('success', 'Producto y presentaciones registrados exitosamente.');
        } catch (Exception $e) {
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
        $categorias = Categoria::activos()->orderBy('nombre')->get();
        $laboratorios = Laboratorio::activos()->orderBy('nombre')->get();
        $producto->load('presentaciones');

        return view('productos.edit', compact('producto', 'categorias', 'laboratorios'));
    }

    public function update(UpdateProductoRequest $request, Producto $producto)
    {
        try {
            DB::transaction(function () use ($request, $producto) {
                $data = $request->validated();

                if ($request->hasFile('imagen')) {
                    if ($producto->imagen && Storage::disk('public')->exists($producto->imagen)) {
                        Storage::disk('public')->delete($producto->imagen);
                    }
                    $data['imagen'] = $request->file('imagen')->store('productos', 'public');
                }

                $producto->update($data);

                // Actualizar presentaciones comerciales si se enviaron
                if ($request->has('presentaciones')) {
                    $producto->presentaciones()->delete();
                    $presentaciones = $request->input('presentaciones', []);
                    $orden = 1;
                    $hasBase = false;
                    foreach ($presentaciones as $p) {
                        if (empty($p['nombre'])) continue;
                        $esBase = !empty($p['es_unidad_base']) || ((int)($p['unidades_por_presentacion'] ?? 1) === 1 && !$hasBase);
                        if ($esBase) $hasBase = true;

                        PresentacionProducto::create([
                            'producto_id' => $producto->id,
                            'nombre' => $p['nombre'],
                            'descripcion' => $p['descripcion'] ?? null,
                            'unidades_por_presentacion' => max(1, (int)($p['unidades_por_presentacion'] ?? 1)),
                            'precio_compra' => !empty($p['precio_compra']) ? $p['precio_compra'] : ($esBase ? $producto->precio_compra : null),
                            'precio_venta' => !empty($p['precio_venta']) ? $p['precio_venta'] : ($esBase ? $producto->precio_venta : null),
                            'codigo_barras' => $p['codigo_barras'] ?? null,
                            'es_unidad_base' => $esBase,
                            'activo' => true,
                            'orden' => $orden++,
                        ]);
                    }
                }
            });

            return redirect()->route('productos.index')
                ->with('success', "Producto '{$producto->nombre}' actualizado exitosamente.");
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Error al actualizar el producto: ' . $e->getMessage());
        }
    }

    public function destroy(Producto $producto)
    {
        $producto->update(['activo' => !$producto->activo]);
        $estado = $producto->activo ? 'activado' : 'desactivado';

        return redirect()->route('productos.index')
            ->with('success', "Producto '{$producto->nombre}' {$estado} correctamente.");
    }
}
