<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductoRequest;
use App\Http\Requests\UpdateProductoRequest;
use App\Models\Producto;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductoController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:ver productos')->only(['index', 'show']);
        $this->middleware('permission:crear productos')->only(['create', 'store']);
        $this->middleware('permission:editar productos')->only(['edit', 'update']);
        $this->middleware('permission:desactivar productos')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
   public function index(Request $request)
{
    $query = Producto::with(['categoria', 'lotes'])
        ->orderBy('nombre');

    // Filtros
    if ($request->filled('categoria_id')) {
        $query->where('categoria_id', $request->categoria_id);
    }

    if ($request->filled('buscar')) {
        $query->where(function ($q) use ($request) {
            $q->where('nombre', 'like', "%{$request->buscar}%")
              ->orWhere('codigo_barra', 'like', "%{$request->buscar}%")
              ->orWhere('principio_activo', 'like', "%{$request->buscar}%")
              ->orWhere('laboratorio', 'like', "%{$request->buscar}%")
              ->orWhere('concentracion', 'like', "%{$request->buscar}%")
              ->orWhere('via_administracion', 'like', "%{$request->buscar}%");
        });
    }

    if ($request->filled('requiere_receta')) {
        $query->where('requiere_receta', $request->requiere_receta);
    }

    // ✅ CAMBIO PRINCIPAL: Filtro de activos/inactivos
    if (!$request->has('mostrar_inactivos')) {
        $query->where('activo', true);  // Solo activos por defecto
    }
    // Si checkbox marcado, muestra TODOS (activos + inactivos)

    $productos = $query->paginate(20)->appends($request->query());
    $categorias = Categoria::where('activo', true)->orderBy('nombre')->get();

    return view('productos.index', compact('productos', 'categorias'));
}
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categorias = Categoria::activos()->orderBy('nombre')->get();
        
        return view('productos.create', compact('categorias'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductoRequest $request)
    {
        try {
            $data = $request->validated();

            // Manejar imagen si existe
            if ($request->hasFile('imagen')) {
                $data['imagen'] = $request->file('imagen')->store('productos', 'public');
            }

            $producto = Producto::create($data);
            
            return redirect()
                ->route('productos.show', $producto)
                ->with('success', "Producto '{$producto->nombre}' creado correctamente.");
                
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Error al crear el producto: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Producto $producto)
    {
        $producto->load([
            'categoria',
            'lotes' => function ($query) {
                $query->orderBy('fecha_vencimiento', 'asc');
            },
            'lotes.proveedor',
            'lotes.compra'
        ]);

        // Calcular stock total
        $stockTotal = $producto->stock_total;
        $stockDisponible = $producto->lotes()->disponibles()->get()->sum('stock_actual');

        return view('productos.show', compact('producto', 'stockTotal', 'stockDisponible'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Producto $producto)
    {
        $categorias = Categoria::activos()->orderBy('nombre')->get();
        
        return view('productos.edit', compact('producto', 'categorias'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductoRequest $request, Producto $producto)
    {
        try {
            $data = $request->validated();

            // Manejar imagen si existe
            if ($request->hasFile('imagen')) {
                // Eliminar imagen anterior si existe
                if ($producto->imagen) {
                    Storage::disk('public')->delete($producto->imagen);
                }
                $data['imagen'] = $request->file('imagen')->store('productos', 'public');
            }

            $producto->update($data);
            
            return redirect()
                ->route('productos.show', $producto)
                ->with('success', "Producto '{$producto->nombre}' actualizado correctamente.");
                
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Error al actualizar el producto: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage (Soft Delete).
     */
    public function destroy(Producto $producto)
    {
        try {
            // Verificar que no tenga lotes con stock
            $stockTotal = $producto->stock_total;
            
            if ($stockTotal > 0) {
                return back()
                    ->with('error', "No se puede desactivar el producto porque tiene stock disponible ({$stockTotal} unidades).");
            }

            $producto->update(['activo' => false]);
            
            return redirect()
                ->route('productos.index')
                ->with('success', "Producto '{$producto->nombre}' desactivado correctamente.");
                
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Error al desactivar el producto: ' . $e->getMessage());
        }
    }

    /**
     * Reactivar un producto.
     */
    public function reactivar(Producto $producto)
    {
        try {
            $producto->update(['activo' => true]);
            
            return redirect()
                ->route('productos.show', $producto)
                ->with('success', "Producto '{$producto->nombre}' reactivado correctamente.");
                
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Error al reactivar el producto: ' . $e->getMessage());
        }
    }
}