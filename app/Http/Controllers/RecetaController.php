<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRecetaRequest;
use App\Http\Requests\UpdateRecetaRequest;
use App\Models\Receta;
use App\Models\Cliente;
use Illuminate\Http\Request;

class RecetaController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:ver recetas')->only(['index', 'show']);
        $this->middleware('permission:registrar recetas')->only(['create', 'store', 'edit', 'update']);
    }

    public function index(Request $request)
    {
        $query = Receta::with('cliente')->orderBy('fecha', 'desc');

        if ($request->filled('cliente_id')) {
            $query->where('cliente_id', $request->input('cliente_id'));
        }

        if ($request->filled('numero_receta')) {
            $query->where('numero_receta', 'like', '%' . $request->input('numero_receta') . '%');
        }

        // Soporte JSON para modal en ventas (listado rápido)
        if ($request->expectsJson() || $request->boolean('json')) {
            $limit = (int) $request->input('limit', 30);
            $limit = max(1, min($limit, 100));

            $recetas = $query->limit($limit)->get();

            return response()->json([
                'data' => $recetas->map(function (Receta $r) {
                    return [
                        'id' => $r->id,
                        'numero_receta' => $r->numero_receta,
                        'fecha' => optional($r->fecha)->format('Y-m-d'),
                        'fecha_fmt' => optional($r->fecha)->format('d/m/Y'),
                        'cliente_id' => $r->cliente_id,
                        'cliente_nombre' => $r->cliente?->nombre,
                        'medico' => $r->medico,
                        'especialidad' => $r->especialidad,
                    ];
                })->values(),
            ]);
        }

        $recetas = $query->paginate(20);
        $clientes = Cliente::activos()->orderBy('nombre')->get();

        return view('recetas.index', compact('recetas', 'clientes'));
    }

    public function create()
    {
        $clientes = Cliente::activos()->orderBy('nombre')->get();
        return view('recetas.create', compact('clientes'));
    }

    public function store(StoreRecetaRequest $request)
    {
        try {
            $receta = Receta::create($request->validated());
            $receta->load('cliente');

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'receta' => [
                        'id' => $receta->id,
                        'numero_receta' => $receta->numero_receta,
                        'fecha' => optional($receta->fecha)->format('Y-m-d'),
                        'fecha_fmt' => optional($receta->fecha)->format('d/m/Y'),
                        'cliente_id' => $receta->cliente_id,
                        'cliente_nombre' => $receta->cliente?->nombre,
                        'medico' => $receta->medico,
                        'especialidad' => $receta->especialidad,
                    ],
                    'message' => "Receta #{$receta->numero_receta} registrada correctamente.",
                ]);
            }

            return redirect()
                ->route('recetas.index')
                ->with('success', "Receta #{$receta->numero_receta} registrada correctamente.");
        } catch (\Throwable $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al registrar la receta: ' . $e->getMessage(),
                ], 422);
            }

            return back()
                ->withInput()
                ->with('error', 'Error al registrar la receta: ' . $e->getMessage());
        }
    }

    public function show(Receta $receta)
    {
        $receta->load('cliente', 'ventas');
        return view('recetas.show', compact('receta'));
    }

    public function edit(Receta $receta)
    {
        $clientes = Cliente::activos()->orderBy('nombre')->get();
        return view('recetas.edit', compact('receta', 'clientes'));
    }

    public function update(UpdateRecetaRequest $request, Receta $receta)
    {
        try {
            $receta->update($request->validated());

            if ($request->expectsJson()) {
                $receta->load('cliente');
                return response()->json([
                    'success' => true,
                    'receta' => [
                        'id' => $receta->id,
                        'numero_receta' => $receta->numero_receta,
                        'fecha' => optional($receta->fecha)->format('Y-m-d'),
                        'fecha_fmt' => optional($receta->fecha)->format('d/m/Y'),
                        'cliente_id' => $receta->cliente_id,
                        'cliente_nombre' => $receta->cliente?->nombre,
                        'medico' => $receta->medico,
                        'especialidad' => $receta->especialidad,
                    ],
                    'message' => "Receta #{$receta->numero_receta} actualizada correctamente.",
                ]);
            }

            return redirect()
                ->route('recetas.show', $receta)
                ->with('success', "Receta #{$receta->numero_receta} actualizada correctamente.");
        } catch (\Throwable $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar la receta: ' . $e->getMessage(),
                ], 422);
            }

            return back()
                ->withInput()
                ->with('error', 'Error al actualizar la receta: ' . $e->getMessage());
        }
    }
}
