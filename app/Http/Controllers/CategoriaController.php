<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Services\CategoriaService;
use App\Http\Requests\StoreCategoriaRequest;
use App\Http\Requests\UpdateCategoriaRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\QueryException;
use Exception;
use Illuminate\Support\Facades\Log;

class CategoriaController extends Controller
{
    public function __construct(
        protected CategoriaService $categoriaService
    ) {
        $this->middleware('permission:ver categorias')->only(['index', 'show', 'buscarAjax']);
        $this->middleware('permission:crear categorias')->only(['create', 'store']);
        $this->middleware('permission:editar categorias')->only(['edit', 'update']);
        $this->middleware('permission:desactivar categorias')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $categorias = $this->categoriaService->listar($request->all(), perPage(15));

        return view('categorias.index', compact('categorias'));
    }

    public function create()
    {
        return view('categorias.create');
    }

    public function store(StoreCategoriaRequest $request)
    {
        try {
            $categoria = $this->categoriaService->crear($request->validated());

            if ($request->boolean('crear_otro')) {
                return redirect()->route('categorias.create')
                    ->with('success', "Categoría '{$categoria->nombre}' creada exitosamente. Listo para registrar la siguiente categoría.");
            }

            return redirect()->route('categorias.index')
                ->with('success', "Categoría '{$categoria->nombre}' creada exitosamente.");
        } catch (QueryException $qe) {
            Log::error('Error de unicidad al crear categoría', [
                'user_id' => auth()->id(),
                'error'   => $qe->getMessage(),
            ]);

            return back()->withInput()->with('error', 'No se pudo registrar la categoría. Ya existe un registro con ese nombre.');
        } catch (Exception $e) {
            Log::error('Error al registrar categoría', [
                'user_id' => auth()->id(),
                'error'   => $e->getMessage(),
            ]);

            return back()->withInput()->with('error', 'Error al guardar la categoría: ' . $e->getMessage());
        }
    }

    public function show(Categoria $categoria)
    {
        $categoria->loadCount('productos');
        $productos = $categoria->productos()->with('laboratorio')->paginate(perPage(10));

        return view('categorias.show', compact('categoria', 'productos'));
    }

    public function edit(Categoria $categoria)
    {
        return view('categorias.edit', compact('categoria'));
    }

    public function update(UpdateCategoriaRequest $request, Categoria $categoria)
    {
        try {
            $this->categoriaService->actualizar($categoria, $request->validated());

            return redirect()->route('categorias.index')
                ->with('success', "Categoría '{$categoria->nombre}' actualizada exitosamente.");
        } catch (QueryException $qe) {
            Log::error('Error de unicidad al actualizar categoría', [
                'categoria_id' => $categoria->id,
                'user_id'      => auth()->id(),
                'error'        => $qe->getMessage(),
            ]);

            return back()->withInput()->with('error', 'No se pudo actualizar la categoría debido a un conflicto de duplicidad de nombre.');
        } catch (Exception $e) {
            Log::error('Error al actualizar categoría', [
                'categoria_id' => $categoria->id,
                'user_id'      => auth()->id(),
                'error'        => $e->getMessage(),
            ]);

            return back()->withInput()->with('error', 'Error al actualizar la categoría: ' . $e->getMessage());
        }
    }

    public function destroy(Categoria $categoria)
    {
        try {
            $estado = $this->categoriaService->toggleEstado($categoria);

            return redirect()->route('categorias.index')
                ->with('success', "Categoría '{$categoria->nombre}' {$estado} correctamente.");
        } catch (Exception $e) {
            Log::error('Error al modificar estado de categoría', [
                'categoria_id' => $categoria->id,
                'user_id'      => auth()->id(),
                'error'        => $e->getMessage(),
            ]);

            return back()->with('error', 'No se pudo modificar el estado de la categoría: ' . $e->getMessage());
        }
    }

    /**
     * Búsqueda AJAX de Categorías (Componente C / Tomas / POS).
     */
    public function buscarAjax(Request $request): JsonResponse
    {
        $q = trim((string)$request->input('q', ''));
        $categorias = $this->categoriaService->buscarAjax($q, 10);

        return response()->json($categorias);
    }
}
