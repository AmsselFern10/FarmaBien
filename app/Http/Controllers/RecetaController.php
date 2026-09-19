<?php

namespace App\Http\Controllers;

use App\Models\Receta;
use App\Models\Cliente;
use App\Models\Producto;
use App\Services\RecetaService;
use App\Http\Requests\StoreRecetaRequest;
use App\Http\Requests\UpdateRecetaRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
        $query = Receta::with(['cliente'])->withCount('detalles');

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
        $clientes = Cliente::activos()->orderBy('nombre')->get();
        $productos = Producto::with('laboratorio')->conReceta()->activos()->orderBy('nombre')->get();

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
        $clientes = Cliente::activos()->orderBy('nombre')->get();
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

        $receta->delete();

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
            'estado' => ['required', 'in:pendiente,validada,dispensada,dispensada_parcial,anulada'],
        ]);

        $receta->update(['estado' => $request->estado]);

        return redirect()->route('recetas.show', $receta)
            ->with('success', 'Estado de la receta actualizado correctamente.');
    }

    public function buscarRecetas(Request $request)
    {
        $termino = trim($request->input('q', ''));
        if (strlen($termino) < 2) {
            return response()->json([]);
        }

        $recetas = $this->recetaService->buscarRecetasDisponibles($termino);

        return response()->json($recetas);
    }
}

