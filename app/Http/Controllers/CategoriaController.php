<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Http\Requests\StoreCategoriaRequest;
use App\Http\Requests\UpdateCategoriaRequest;
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
        $query = Categoria::withCount('productos');

        if ($request->filled('buscar')) {
            $buscar = trim($request->input('buscar'));
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('descripcion', 'like', "%{$buscar}%");
            });
        }

        if ($request->filled('estado')) {
            if ($request->estado === 'activos') {
                $query->where('activo', true);
            } elseif ($request->estado === 'inactivos') {
                $query->where('activo', false);
            }
        }

        if ($request->filled('con_productos')) {
            if ($request->con_productos === 'si') {
                $query->has('productos');
            } elseif ($request->con_productos === 'no') {
                $query->doesntHave('productos');
            }
        }

        $categorias = $query->orderBy('nombre', 'asc')->paginate(15)->withQueryString();

        return view('categorias.index', compact('categorias'));
    }

    public function create()
    {
        return view('categorias.create');
    }

    public function store(StoreCategoriaRequest $request)
    {
        $categoria = Categoria::create($request->validated());

        return redirect()->route('categorias.index')
            ->with('success', "Categoría '{$categoria->nombre}' creada exitosamente.");
    }

    public function show(Categoria $categoria)
    {
        $categoria->loadCount('productos');
        $productos = $categoria->productos()->with('laboratorio')->paginate(10);
        return view('categorias.show', compact('categoria', 'productos'));
    }

    public function edit(Categoria $categoria)
    {
        return view('categorias.edit', compact('categoria'));
    }

    public function update(UpdateCategoriaRequest $request, Categoria $categoria)
    {
        $categoria->update($request->validated());

        return redirect()->route('categorias.index')
            ->with('success', "Categoría '{$categoria->nombre}' actualizada exitosamente.");
    }

    public function destroy(Categoria $categoria)
    {
        $categoria->update(['activo' => !$categoria->activo]);
        $estado = $categoria->activo ? 'activada' : 'desactivada';

        return redirect()->route('categorias.index')
            ->with('success', "Categoría '{$categoria->nombre}' {$estado} correctamente.");
    }
}
