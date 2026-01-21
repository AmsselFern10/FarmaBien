<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClienteRequest;
use App\Http\Requests\UpdateClienteRequest;
use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:ver clientes')->only(['index', 'show']);
        $this->middleware('permission:crear clientes')->only(['create', 'store']);
        $this->middleware('permission:editar clientes')->only(['edit', 'update']);
        $this->middleware('permission:desactivar clientes')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $query = Cliente::orderBy('nombre');

        if ($request->filled('buscar')) {
            $query->where(function ($q) use ($request) {
                $q->where('nombre', 'like', "%{$request->buscar}%")
                  ->orWhere('documento', 'like', "%{$request->buscar}%")
                  ->orWhere('telefono', 'like', "%{$request->buscar}%");
            });
        }

        if ($request->filled('activo')) {
            $query->where('activo', $request->activo);
        } else {
            $query->activos();
        }

        $clientes = $query->paginate(20);

        return view('clientes.index', compact('clientes'));
    }

    public function create()
    {
        return view('clientes.create');
    }

    public function store(StoreClienteRequest $request)
    {
        try {
            $cliente = Cliente::create($request->validated());
            
            return redirect()
                ->route('clientes.index')
                ->with('success', "Cliente '{$cliente->nombre}' creado correctamente.");
                
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Error al crear el cliente: ' . $e->getMessage());
        }
    }

    public function show(Cliente $cliente)
    {
        $cliente->load(['ventas' => function ($query) {
            $query->orderBy('fecha', 'desc')->limit(10);
        }, 'recetas']);

        return view('clientes.show', compact('cliente'));
    }

    public function edit(Cliente $cliente)
    {
        return view('clientes.edit', compact('cliente'));
    }

    public function update(UpdateClienteRequest $request, Cliente $cliente)
    {
        try {
            $cliente->update($request->validated());
            
            return redirect()
                ->route('clientes.show', $cliente)
                ->with('success', "Cliente '{$cliente->nombre}' actualizado correctamente.");
                
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Error al actualizar el cliente: ' . $e->getMessage());
        }
    }

    public function destroy(Cliente $cliente)
    {
        try {
            $cliente->update(['activo' => false]);
            
            return redirect()
                ->route('clientes.index')
                ->with('success', "Cliente '{$cliente->nombre}' desactivado correctamente.");
                
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Error al desactivar el cliente: ' . $e->getMessage());
        }
    }
}