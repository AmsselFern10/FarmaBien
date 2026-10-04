<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Laboratorio;
use App\Http\Requests\StoreProductoRequest;
use App\Http\Requests\UpdateProductoRequest;
use App\Services\ProductoService;
use App\Services\FarmaIaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use Exception;

class ProductoController extends Controller
{
    public function __construct(
        protected ProductoService $productoService
    ) {
        $this->middleware('permission:ver productos')->only(['index', 'show', 'iaBuscar', 'iaFicha']);
        $this->middleware('permission:crear productos')->only(['create', 'store']);
        $this->middleware('permission:editar productos')->only(['edit', 'update']);
        $this->middleware('permission:desactivar productos')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $filtros = $request->only(['buscar', 'categoria_id', 'laboratorio_id', 'tipo_control', 'bajo_stock']);
        $productos = $this->productoService->listarProductos($filtros, perPage(12))->withQueryString();

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
            'ok'      => true,
            'query'   => $q,
            'count'   => count($results),
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
        try {
            $data = $request->validated();
            $imagen = $request->file('imagen');
            $presentaciones = $request->input('presentaciones', []);

            $producto = $this->productoService->crearProducto($data, $imagen, $presentaciones);

            if ($request->boolean('crear_otro')) {
                return redirect()->route('productos.create')
                    ->with('success', "Medicamento '{$producto->nombre}' registrado exitosamente. Listo para registrar el siguiente producto.");
            }

            return redirect()->route('productos.index')
                ->with('success', "Producto '{$producto->nombre}' y sus presentaciones fueron registrados exitosamente.");
        } catch (QueryException $qe) {
            Log::error('Error de base de datos al registrar producto', [
                'user_id'    => auth()->id(),
                'error_code' => $qe->getCode(),
                'message'    => $qe->getMessage(),
            ]);

            return back()->withInput()->with('error', 'No se pudo guardar el medicamento debido a un conflicto de datos o duplicidad en la base de datos.');
        } catch (Exception $e) {
            Log::error('Error general al registrar producto', [
                'user_id' => auth()->id(),
                'message' => $e->getMessage(),
            ]);

            return back()->withInput()->with('error', 'Error al guardar el producto: ' . $e->getMessage());
        }
    }

    public function show(Producto $producto)
    {
        $producto = $this->productoService->obtenerFichaCompleta($producto);

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
        try {
            $data = $request->validated();
            $nuevaImagen = $request->file('imagen');
            $presentaciones = $request->has('presentaciones') ? $request->input('presentaciones', []) : null;

            $this->productoService->actualizarProducto($producto, $data, $nuevaImagen, $presentaciones);

            return redirect()->route('productos.index')
                ->with('success', "Medicamento '{$producto->nombre}' actualizado correctamente.");
        } catch (QueryException $qe) {
            Log::error('Error de base de datos al actualizar producto', [
                'producto_id' => $producto->id,
                'user_id'     => auth()->id(),
                'message'     => $qe->getMessage(),
            ]);

            return back()->withInput()->with('error', 'No se pudo actualizar el medicamento debido a un conflicto de unicidad o integridad en la base de datos.');
        } catch (Exception $e) {
            Log::error('Error general al actualizar producto', [
                'producto_id' => $producto->id,
                'user_id'     => auth()->id(),
                'message'     => $e->getMessage(),
            ]);

            return back()->withInput()->with('error', 'Error al actualizar el producto: ' . $e->getMessage());
        }
    }

    public function destroy(Producto $producto)
    {
        try {
            $estado = $this->productoService->cambiarEstado($producto);

            return redirect()->route('productos.index')
                ->with('success', "Producto '{$producto->nombre}' {$estado} correctamente.");
        } catch (Exception $e) {
            Log::error('Error al modificar estado de producto', [
                'producto_id' => $producto->id,
                'user_id'     => auth()->id(),
                'message'     => $e->getMessage(),
            ]);

            return back()->with('error', 'No se pudo cambiar el estado del medicamento: ' . $e->getMessage());
        }
    }
}
