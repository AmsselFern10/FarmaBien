<?php

namespace App\Http\Controllers;

use App\Models\Receta;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\AuditLog;
use App\Services\RecetaService;
use App\Http\Requests\StoreRecetaRequest;
use App\Http\Requests\UpdateRecetaRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Exception;

class RecetaController extends Controller
{
    protected RecetaService $recetaService;

    public function __construct(RecetaService $recetaService)
    {
        $this->recetaService = $recetaService;
        $this->middleware('permission:ver recetas')->only(['index', 'show']);
        $this->middleware('permission:registrar recetas')->only(['create', 'store', 'edit', 'update', 'buscarRecetas']);
        $this->middleware('permission:validar recetas')->only(['validar']);
    }

    public function index(Request $request)
    {
        $query = Receta::select([
                'id',
                'cliente_id',
                'paciente_nombre',
                'paciente_documento',
                'medico_nombre',
                'medico_colegiatura',
                'numero_receta',
                'fecha_emision',
                'fecha_vencimiento',
                'tipo_receta',
                'estado',
            ])
            ->with(['cliente:id,nombre,documento'])
            ->withCount('detalles');

        if ($request->filled('buscar')) {
            $buscar = trim($request->input('buscar'));
            $query->where(function ($q) use ($buscar) {
                $q->where('numero_receta', 'like', "%{$buscar}%")
                  ->orWhere('paciente_nombre', 'like', "%{$buscar}%")
                  ->orWhere('medico_nombre', 'like', "%{$buscar}%")
                  ->orWhere('medico_colegiatura', 'like', "%{$buscar}%");
            });
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->input('estado'));
        }

        if ($request->filled('tipo_receta')) {
            $query->where('tipo_receta', $request->input('tipo_receta'));
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_emision', '>=', $request->input('fecha_desde'));
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_emision', '<=', $request->input('fecha_hasta'));
        }

        $recetas = $query->orderBy('fecha_emision', 'desc')->paginate(15)->withQueryString();

        return view('recetas.index', compact('recetas'));
    }

    public function create()
    {
        $clientes = Cliente::select(['id', 'nombre', 'documento'])->activos()->orderBy('nombre')->get();
        $productos = Producto::select(['id', 'nombre', 'principio_activo', 'concentracion', 'laboratorio_id'])
            ->with('laboratorio:id,nombre')
            ->conReceta()
            ->activos()
            ->orderBy('nombre')
            ->get();

        return view('recetas.create', compact('clientes', 'productos'));
    }

    public function store(StoreRecetaRequest $request)
    {
        try {
            $data = $request->validated();

            if ($request->hasFile('archivo_receta')) {
                $path = $request->file('archivo_receta')->store('recetas', 'public');
                $data['archivo_receta'] = $path;
            }

            $receta = $this->recetaService->registrarReceta($data);

            AuditLog::log('recetas', 'crear', "Receta médica #{$receta->numero_receta} registrada", [
                'receta_id' => $receta->id,
                'paciente' => $receta->paciente_nombre,
                'medico' => $receta->medico_nombre,
            ]);

            Cache::forget('dashboard_recetas_pendientes_count');

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'receta' => $receta,
                    'message' => 'Receta médica registrada exitosamente.'
                ]);
            }

            return redirect()->route('recetas.show', $receta)
                ->with('success', "Receta médica #{$receta->numero_receta} registrada exitosamente.");
        } catch (Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return back()->withInput()->with('error', 'Error al registrar la receta: ' . $e->getMessage());
        }
    }

    public function show(Receta $receta)
    {
        $receta->load([
            'cliente',
            'detalles.producto.laboratorio',
            'detalles.detallesVenta.venta.usuario',
            'ventas.usuario'
        ]);

        return view('recetas.show', compact('receta'));
    }

    public function edit(Receta $receta)
    {
        $clientes = Cliente::select(['id', 'nombre', 'documento'])->activos()->orderBy('nombre')->get();
        $receta->load('detalles.producto');

        return view('recetas.edit', compact('receta', 'clientes'));
    }

    public function update(UpdateRecetaRequest $request, Receta $receta)
    {
        try {
            $data = $request->validated();

            if ($request->hasFile('archivo_receta')) {
                if ($receta->archivo_receta && Storage::disk('public')->exists($receta->archivo_receta)) {
                    Storage::disk('public')->delete($receta->archivo_receta);
                }
                $data['archivo_receta'] = $request->file('archivo_receta')->store('recetas', 'public');
            }

            $receta->update($data);

            AuditLog::log('recetas', 'actualizar', "Receta médica #{$receta->numero_receta} actualizada", [
                'receta_id' => $receta->id,
            ]);

            Cache::forget('dashboard_recetas_pendientes_count');

            return redirect()->route('recetas.show', $receta)
                ->with('success', "Receta #{$receta->numero_receta} actualizada exitosamente.");
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Error al actualizar la receta: ' . $e->getMessage());
        }
    }

    public function destroy(Receta $receta)
    {
        if ($receta->detalles()->where('cantidad_dispensada', '>', 0)->exists()) {
            return back()->with('error', 'No se puede eliminar una receta que ya tiene medicamentos dispensados.');
        }

        $num = $receta->numero_receta;
        $id = $receta->id;
        $receta->delete();

        AuditLog::log('recetas', 'eliminar', "Receta médica #{$num} eliminada", [
            'receta_id' => $id,
        ]);

        Cache::forget('dashboard_recetas_pendientes_count');

        return redirect()->route('recetas.index')
            ->with('success', "Receta eliminada correctamente.");
    }

    public function validar(Receta $receta)
    {
        if ($receta->estaVencida()) {
            return response()->json([
                'valida' => false,
                'motivo' => "La receta venció el {$receta->fecha_vencimiento->format('d/m/Y')}."
            ]);
        }

        return response()->json([
            'valida' => true,
            'receta' => $receta->load('detalles.producto')
        ]);
    }

    public function cambiarEstado(Request $request, Receta $receta)
    {
        $request->validate([
            'estado' => ['required', 'in:pendiente,dispensada_parcial,dispensada_total,anulada'],
        ]);

        $receta->update(['estado' => $request->estado]);

        AuditLog::log('recetas', 'cambiar_estado', "Estado de receta #{$receta->numero_receta} cambiado a {$request->estado}", [
            'receta_id' => $receta->id,
            'nuevo_estado' => $request->estado,
        ]);

        Cache::forget('dashboard_recetas_pendientes_count');

        return redirect()->route('recetas.show', $receta)
            ->with('success', 'Estado de la receta actualizado correctamente.');
    }

    public function buscarRecetas(Request $request)
    {
        $termino = trim($request->input('q', ''));
        $limit = min(50, max(5, (int) $request->input('limit', 20)));

        $recetas = $this->recetaService->buscarRecetasDisponibles($termino, $limit);

        return response()->json($recetas);
    }
}

