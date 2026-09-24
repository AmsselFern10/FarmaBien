<?php

namespace App\Http\Controllers;

use App\Models\Laboratorio;
use App\Models\AuditLog;
use App\Http\Requests\StoreLaboratorioRequest;
use App\Http\Requests\UpdateLaboratorioRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\QueryException;
use Exception;

class LaboratorioController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:ver laboratorios')->only(['index', 'show']);
        $this->middleware('permission:crear laboratorios')->only(['create', 'store']);
        $this->middleware('permission:editar laboratorios')->only(['edit', 'update']);
        $this->middleware('permission:desactivar laboratorios')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $query = Laboratorio::withCount('productos');

        if ($request->filled('buscar')) {
            $buscar = trim($request->input('buscar'));
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('codigo', 'like', "%{$buscar}%")
                  ->orWhere('pais_origen', 'like', "%{$buscar}%")
                  ->orWhere('contacto', 'like', "%{$buscar}%");
            });
        }

        if ($request->filled('estado')) {
            if ($request->estado === 'activos') {
                $query->where('activo', true);
            } elseif ($request->estado === 'inactivos') {
                $query->where('activo', false);
            }
        }

        $laboratorios = $query->orderBy('nombre', 'asc')->paginate(15)->withQueryString();

        return view('laboratorios.index', compact('laboratorios'));
    }

    public function create()
    {
        return view('laboratorios.create');
    }

    public function store(StoreLaboratorioRequest $request)
    {
        try {
            $laboratorio = DB::transaction(function () use ($request) {
                $laboratorio = Laboratorio::create($request->validated());

                Log::info('Laboratorio registrado exitosamente', [
                    'laboratorio_id' => $laboratorio->id,
                    'nombre' => $laboratorio->nombre,
                    'codigo' => $laboratorio->codigo,
                    'user_id' => auth()->id(),
                ]);

                AuditLog::log('laboratorios', 'crear', "Laboratorio '{$laboratorio->nombre}' creado", [
                    'laboratorio_id' => $laboratorio->id,
                    'codigo' => $laboratorio->codigo,
                ]);

                Cache::forget('catalog_laboratorios_base');

                return $laboratorio;
            });

            return redirect()->route('laboratorios.index')
                ->with('success', "Laboratorio '{$laboratorio->nombre}' registrado correctamente.");
        } catch (QueryException $qe) {
            Log::error('Error de base de datos al registrar laboratorio', [
                'user_id' => auth()->id(),
                'message' => $qe->getMessage(),
            ]);

            return back()->withInput()->with('error', 'No se pudo guardar el laboratorio. El nombre o código ingresado ya existe.');
        } catch (Exception $e) {
            Log::error('Error al registrar laboratorio', [
                'user_id' => auth()->id(),
                'message' => $e->getMessage(),
            ]);

            return back()->withInput()->with('error', 'Error al guardar el laboratorio: ' . $e->getMessage());
        }
    }

    public function show(Laboratorio $laboratorio)
    {
        $productos = $laboratorio->productos()->with('categoria')->paginate(10);

        return view('laboratorios.show', compact('laboratorio', 'productos'));
    }

    public function edit(Laboratorio $laboratorio)
    {
        return view('laboratorios.edit', compact('laboratorio'));
    }

    public function update(UpdateLaboratorioRequest $request, Laboratorio $laboratorio)
    {
        try {
            DB::transaction(function () use ($request, $laboratorio) {
                $locked = Laboratorio::where('id', $laboratorio->id)->lockForUpdate()->firstOrFail();
                $locked->update($request->validated());

                Log::info('Laboratorio actualizado exitosamente', [
                    'laboratorio_id' => $locked->id,
                    'nombre' => $locked->nombre,
                    'user_id' => auth()->id(),
                ]);

                AuditLog::log('laboratorios', 'actualizar', "Laboratorio '{$locked->nombre}' actualizado", [
                    'laboratorio_id' => $locked->id,
                ]);

                Cache::forget('catalog_laboratorios_base');
            });

            return redirect()->route('laboratorios.index')
                ->with('success', "Laboratorio '{$laboratorio->nombre}' actualizado correctamente.");
        } catch (QueryException $qe) {
            Log::error('Error de base de datos al actualizar laboratorio', [
                'laboratorio_id' => $laboratorio->id,
                'user_id' => auth()->id(),
                'message' => $qe->getMessage(),
            ]);

            return back()->withInput()->with('error', 'No se pudo actualizar el laboratorio. Conflicto de nombre o código duplicado.');
        } catch (Exception $e) {
            Log::error('Error al actualizar laboratorio', [
                'laboratorio_id' => $laboratorio->id,
                'user_id' => auth()->id(),
                'message' => $e->getMessage(),
            ]);

            return back()->withInput()->with('error', 'Error al actualizar el laboratorio: ' . $e->getMessage());
        }
    }

    public function destroy(Laboratorio $laboratorio)
    {
        try {
            $estado = DB::transaction(function () use ($laboratorio) {
                $locked = Laboratorio::where('id', $laboratorio->id)->lockForUpdate()->firstOrFail();
                $nuevoEstado = !$locked->activo;
                $locked->update(['activo' => $nuevoEstado]);

                Log::info('Estado de laboratorio modificado', [
                    'laboratorio_id' => $locked->id,
                    'nombre' => $locked->nombre,
                    'nuevo_estado' => $nuevoEstado ? 'activado' : 'desactivado',
                    'user_id' => auth()->id(),
                ]);

                AuditLog::log(
                    'laboratorios',
                    $nuevoEstado ? 'activar' : 'desactivar',
                    "Laboratorio '{$locked->nombre}' " . ($nuevoEstado ? 'activado' : 'desactivado'),
                    ['laboratorio_id' => $locked->id]
                );

                Cache::forget('catalog_laboratorios_base');

                return $nuevoEstado ? 'activado' : 'desactivado';
            });

            return redirect()->route('laboratorios.index')
                ->with('success', "Laboratorio '{$laboratorio->nombre}' {$estado} correctamente.");
        } catch (Exception $e) {
            Log::error('Error al modificar estado de laboratorio', [
                'laboratorio_id' => $laboratorio->id,
                'user_id' => auth()->id(),
                'message' => $e->getMessage(),
            ]);

            return back()->with('error', 'No se pudo modificar el estado del laboratorio: ' . $e->getMessage());
        }
    }
}
