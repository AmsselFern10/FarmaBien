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
            $query->where('cliente_id', $request->cliente_id);
        }

        if ($request->filled('numero_receta')) {
            $query->where('numero_receta', 'like', "%{$request->numero_receta}%");
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
            
            return redirect()
                ->route('recetas.index')
                ->with('success', "Receta #{$receta->numero_receta} registrada correctamente.");
                
        } catch (\Exception $e) {
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
            
            return redirect()
                ->route('recetas.show', $receta)
                ->with('success', "Receta #{$receta->numero_receta} actualizada correctamente.");
                
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Error al actualizar la receta: ' . $e->getMessage());
        }
    }
}