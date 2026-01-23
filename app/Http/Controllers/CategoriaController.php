<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoriaRequest;
use App\Http\Requests\UpdateCategoriaRequest;
use App\Models\Categoria;
use Illuminate\Http\Request;



class CategoriaController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:ver categorias')->only(['index', 'show']);
        $this->middleware('permission:crear categorias')->only(['create', 'store']);
        $this->middleware('permission:editar categorias')->only(['edit', 'update']);
        $this->middleware('permission:desactivar categorias')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $query = Categoria::withCount('productos')->orderBy('nombre');

        if ($request->filled('activo')) {
            $query->where('activo', $request->activo);
        } else {
            $query->activos();
        }

        $categorias = $query->paginate(15);

        return view('categorias.index', compact('categorias'));
    }

    public function create()
    {
        return view('categorias.create');
    }

    public function store(StoreCategoriaRequest $request)
    {
        try {
            $categoria = Categoria::create($request->validated());
            
            return redirect()
                ->route('categorias.index')
                ->with('success', "Categoría '{$categoria->nombre}' creada correctamente.");
                
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Error al crear la categoría: ' . $e->getMessage());
        }
    }

    public function show(Categoria $categoria)
    {
        $categoria->load('productos');

        return view('categorias.show', compact('categoria'));
    }

    public function edit(Categoria $categoria)
    {
        return view('categorias.edit', compact('categoria'));
    }

    public function update(UpdateCategoriaRequest $request, Categoria $categoria)
    {
        try {
            $categoria->update($request->validated());
            
            return redirect()
                ->route('categorias.index')
                ->with('success', "Categoría '{$categoria->nombre}' actualizada correctamente.");
                
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Error al actualizar la categoría: ' . $e->getMessage());
        }
    }

    public function destroy(Categoria $categoria)
    {
        try {
            // Verificar que no tenga productos activos
            if ($categoria->productos()->activos()->count() > 0) {
                return back()
                    ->with('error', 'No se puede desactivar la categoría porque tiene productos activos asociados.');
            }

            $categoria->update(['activo' => false]);
            
            return redirect()
                ->route('categorias.index')
                ->with('success', "Categoría '{$categoria->nombre}' desactivada correctamente.");
                
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Error al desactivar la categoría: ' . $e->getMessage());
        }
    }
}

