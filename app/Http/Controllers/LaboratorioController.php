<?php

namespace App\Http\Controllers;

use App\Models\Laboratorio;
use App\Services\LaboratorioService;
use App\Http\Requests\StoreLaboratorioRequest;
use App\Http\Requests\UpdateLaboratorioRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\QueryException;
use Exception;
use Illuminate\Support\Facades\Log;

class LaboratorioController extends Controller
{
    public function __construct(
        protected LaboratorioService $laboratorioService
    ) {
        $this->middleware('permission:ver laboratorios')->only(['index', 'show', 'buscarAjax']);
        $this->middleware('permission:crear laboratorios')->only(['create', 'store']);
        $this->middleware('permission:editar laboratorios')->only(['edit', 'update']);
        $this->middleware('permission:desactivar laboratorios')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $laboratorios = $this->laboratorioService->listar($request->all(), perPage(15));

        return view('laboratorios.index', compact('laboratorios'));
    }

    public function create()
    {
        return view('laboratorios.create');
    }

    public function store(StoreLaboratorioRequest $request)
    {
        try {
            $laboratorio = $this->laboratorioService->crear($request->validated());

            if ($request->boolean('crear_otro')) {
                return redirect()->route('laboratorios.create')
                    ->with('success', "Laboratorio '{$laboratorio->nombre}' registrado correctamente. Listo para registrar el siguiente laboratorio.");
            }

            return redirect()->route('laboratorios.index')
                ->with('success', "Laboratorio '{$laboratorio->nombre}' registrado correctamente.");
        } catch (QueryException $qe) {
            Log::error('Error de unicidad al registrar laboratorio', [
                'user_id' => auth()->id(),
                'error'   => $qe->getMessage(),
            ]);

            return back()->withInput()->with('error', 'No se pudo guardar el laboratorio. El nombre o código ingresado ya existe.');
        } catch (Exception $e) {
            Log::error('Error al registrar laboratorio', [
                'user_id' => auth()->id(),
                'error'   => $e->getMessage(),
            ]);

            return back()->withInput()->with('error', 'Error al guardar el laboratorio: ' . $e->getMessage());
        }
    }

    public function show(Laboratorio $laboratorio)
    {
        $productos = $laboratorio->productos()->with('categoria')->paginate(perPage(10));

        return view('laboratorios.show', compact('laboratorio', 'productos'));
    }

    public function edit(Laboratorio $laboratorio)
    {
        return view('laboratorios.edit', compact('laboratorio'));
    }

    public function update(UpdateLaboratorioRequest $request, Laboratorio $laboratorio)
    {
        try {
            $this->laboratorioService->actualizar($laboratorio, $request->validated());

            return redirect()->route('laboratorios.index')
                ->with('success', "Laboratorio '{$laboratorio->nombre}' actualizado correctamente.");
        } catch (QueryException $qe) {
            Log::error('Error de unicidad al actualizar laboratorio', [
                'laboratorio_id' => $laboratorio->id,
                'user_id'        => auth()->id(),
                'error'          => $qe->getMessage(),
            ]);

            return back()->withInput()->with('error', 'No se pudo actualizar el laboratorio. Conflicto de nombre o código duplicado.');
        } catch (Exception $e) {
            Log::error('Error al actualizar laboratorio', [
                'laboratorio_id' => $laboratorio->id,
                'user_id'        => auth()->id(),
                'error'          => $e->getMessage(),
            ]);

            return back()->withInput()->with('error', 'Error al actualizar el laboratorio: ' . $e->getMessage());
        }
    }

    public function destroy(Laboratorio $laboratorio)
    {
        try {
            $estado = $this->laboratorioService->toggleEstado($laboratorio);

            return redirect()->route('laboratorios.index')
                ->with('success', "Laboratorio '{$laboratorio->nombre}' {$estado} correctamente.");
        } catch (Exception $e) {
            Log::error('Error al modificar estado de laboratorio', [
                'laboratorio_id' => $laboratorio->id,
                'user_id'        => auth()->id(),
                'error'          => $e->getMessage(),
            ]);

            return back()->with('error', 'No se pudo modificar el estado del laboratorio: ' . $e->getMessage());
        }
    }

    /**
     * Búsqueda AJAX de Laboratorios (Componente C / Filtros).
     */
    public function buscarAjax(Request $request): JsonResponse
    {
        $q = trim((string)$request->input('q', ''));
        $laboratorios = $this->laboratorioService->buscarAjax($q, 15);

        return response()->json($laboratorios);
    }
}
