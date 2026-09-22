<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Http\Requests\StoreCategoriaRequest;
use App\Http\Requests\UpdateCategoriaRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use Exception;

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
        try {
            $categoria = DB::transaction(function () use ($request) {
                $categoria = Categoria::create($request->validated());

                Log::info('Categoría registrada exitosamente', [
                    'categoria_id' => $categoria->id,
                    'nombre' => $categoria->nombre,
                    'user_id' => auth()->id(),
                ]);

                return $categoria;
            });

            return redirect()->route('categorias.index')
                ->with('success', "Categoría '{$categoria->nombre}' creada exitosamente.");
        } catch (QueryException $qe) {
            Log::error('Error de base de datos al crear categoría', [
                'user_id' => auth()->id(),
                'message' => $qe->getMessage(),
            ]);

            return back()->withInput()->with('error', 'No se pudo registrar la categoría. Ya existe un registro con ese nombre.');
        } catch (Exception $e) {
            Log::error('Error al registrar categoría', [
                'user_id' => auth()->id(),
                'message' => $e->getMessage(),
            ]);

            return back()->withInput()->with('error', 'Error al guardar la categoría: ' . $e->getMessage());
        }
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
        try {
            DB::transaction(function () use ($request, $categoria) {
                $locked = Categoria::where('id', $categoria->id)->lockForUpdate()->firstOrFail();
                $locked->update($request->validated());

                Log::info('Categoría actualizada exitosamente', [
                    'categoria_id' => $locked->id,
                    'nombre' => $locked->nombre,
                    'user_id' => auth()->id(),
                ]);
            });

            return redirect()->route('categorias.index')
                ->with('success', "Categoría '{$categoria->nombre}' actualizada exitosamente.");
        } catch (QueryException $qe) {
            Log::error('Error de base de datos al actualizar categoría', [
                'categoria_id' => $categoria->id,
                'user_id' => auth()->id(),
                'message' => $qe->getMessage(),
            ]);

            return back()->withInput()->with('error', 'No se pudo actualizar la categoría debido a un conflicto de duplicidad de nombre.');
        } catch (Exception $e) {
            Log::error('Error al actualizar categoría', [
                'categoria_id' => $categoria->id,
                'user_id' => auth()->id(),
                'message' => $e->getMessage(),
            ]);

            return back()->withInput()->with('error', 'Error al actualizar la categoría: ' . $e->getMessage());
        }
    }

    public function destroy(Categoria $categoria)
    {
        try {
            $estado = DB::transaction(function () use ($categoria) {
                $locked = Categoria::where('id', $categoria->id)->lockForUpdate()->firstOrFail();
                $nuevoEstado = !$locked->activo;
                $locked->update(['activo' => $nuevoEstado]);

                Log::info('Estado de categoría modificado', [
                    'categoria_id' => $locked->id,
                    'nombre' => $locked->nombre,
                    'nuevo_estado' => $nuevoEstado ? 'activada' : 'desactivada',
                    'user_id' => auth()->id(),
                ]);

                return $nuevoEstado ? 'activada' : 'desactivada';
            });

            return redirect()->route('categorias.index')
                ->with('success', "Categoría '{$categoria->nombre}' {$estado} correctamente.");
        } catch (Exception $e) {
            Log::error('Error al modificar estado de categoría', [
                'categoria_id' => $categoria->id,
                'user_id' => auth()->id(),
                'message' => $e->getMessage(),
            ]);

            return back()->with('error', 'No se pudo modificar el estado de la categoría: ' . $e->getMessage());
        }
    }
}
