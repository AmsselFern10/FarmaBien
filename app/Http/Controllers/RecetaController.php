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
        $this->middleware('permission:ver recetas')->only(['index', 'show', 'verArchivo']);
        $this->middleware('permission:registrar recetas')->only(['create', 'store', 'edit', 'update', 'buscarRecetas']);
        $this->middleware('permission:validar recetas')->only(['validar']);
    }

    /**
     * Descargar o visualizar de forma segura el archivo adjunto de una receta médica.
     * Solo accesible para usuarios autenticados con permisos sobre recetas (Admin, Farmacéutico, o Cajero autorizado).
     */
    public function verArchivo(Receta $receta)
    {
        $user = auth()->user();

        // 1. Verificación de autorización
        if (!$user || (!$user->can('ver recetas') && !$user->hasRole(['Admin', 'Farmaceutico']) && !$user->can('dispensar recetas'))) {
            AuditLog::log('recetas', 'acceso_no_autorizado', "Intento no autorizado de visualización de archivo de receta #{$receta->numero_receta}", [
                'receta_id' => $receta->id,
                'user_id' => $user?->id,
                'ip' => request()->ip(),
            ]);
            abort(403, 'No tienes autorización para acceder a los archivos clínicos de recetas médicas.');
        }

        if (empty($receta->archivo_receta)) {
            abort(404, 'La receta médica no cuenta con un archivo digitalizado adjunto.');
        }

        // 2. Localizar archivo (privado primero en local, fallback a public para archivos previos)
        $path = null;
        if (Storage::disk('local')->exists($receta->archivo_receta)) {
            $path = Storage::disk('local')->path($receta->archivo_receta);
        } elseif (Storage::disk('public')->exists($receta->archivo_receta)) {
            $path = Storage::disk('public')->path($receta->archivo_receta);
        }

        if (!$path || !file_exists($path)) {
            abort(404, 'El archivo de la receta no se encuentra en el servidor.');
        }

        // 3. Registrar auditoría de acceso al documento clínico
        AuditLog::log('recetas', 'ver_archivo', "Visualización segura de archivo de receta #{$receta->numero_receta}", [
            'receta_id' => $receta->id,
            'user_id' => $user->id,
        ]);

        $mimeType = mime_content_type($path) ?: 'application/octet-stream';
        $fileName = basename($receta->archivo_receta);

        return response()->file($path, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => "inline; filename=\"{$fileName}\"",
            'Cache-Control' => 'private, no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
            'X-Content-Type-Options' => 'nosniff',
        ]);
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

        $recetas = $query->orderBy('fecha_emision', 'desc')->orderBy('id', 'desc')->paginate(15)->withQueryString();

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
                // Almacenar en disco privado 'local' fuera del directorio público web
                $path = $request->file('archivo_receta')->store('recetas', 'local');
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
        $productos = Producto::select(['id', 'nombre', 'principio_activo', 'concentracion', 'laboratorio_id'])
            ->with('laboratorio:id,nombre')
            ->conReceta()
            ->activos()
            ->orderBy('nombre')
            ->get();

        $receta->load(['detalles.producto', 'cliente']);

        return view('recetas.edit', compact('receta', 'clientes', 'productos'));
    }

    public function update(UpdateRecetaRequest $request, Receta $receta)
    {
        try {
            $data = $request->validated();

            if ($request->hasFile('archivo_receta')) {
                // Eliminar archivo anterior si existe (buscar en local o en public)
                if ($receta->archivo_receta) {
                    if (Storage::disk('local')->exists($receta->archivo_receta)) {
                        Storage::disk('local')->delete($receta->archivo_receta);
                    } elseif (Storage::disk('public')->exists($receta->archivo_receta)) {
                        Storage::disk('public')->delete($receta->archivo_receta);
                    }
                }
                // Guardar en almacenamiento seguro 'local'
                $data['archivo_receta'] = $request->file('archivo_receta')->store('recetas', 'local');
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

