<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Http\Requests\StoreClienteRequest;
use App\Http\Requests\UpdateClienteRequest;
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
        $query = Cliente::withCount(['ventas', 'recetas']);

        if ($request->filled('buscar')) {
            $buscar = trim($request->input('buscar'));
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('documento', 'like', "%{$buscar}%")
                  ->orWhere('telefono', 'like', "%{$buscar}%")
                  ->orWhere('email', 'like', "%{$buscar}%")
                  ->orWhere('direccion', 'like', "%{$buscar}%");
            });
        }

        if ($request->filled('estado')) {
            if ($request->estado === 'activos') {
                $query->where('activo', true);
            } elseif ($request->estado === 'inactivos') {
                $query->where('activo', false);
            }
        }

        if ($request->filled('con_recetas')) {
            if ($request->con_recetas === 'si') {
                $query->has('recetas');
            } elseif ($request->con_recetas === 'no') {
                $query->doesntHave('recetas');
            }
        }

        $clientes = $query->orderBy('nombre', 'asc')->paginate(15)->withQueryString();

        return view('clientes.index', compact('clientes'));
    }

    public function create()
    {
        return view('clientes.create');
    }

    public function store(StoreClienteRequest $request)
    {
        $cliente = Cliente::create($request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'cliente' => $cliente,
                'message' => 'Cliente registrado exitosamente.'
            ]);
        }

        return redirect()->route('clientes.index')
            ->with('success', "Cliente '{$cliente->nombre}' registrado exitosamente.");
    }

    public function show(Cliente $cliente)
    {
        $cliente->load([
            'ventas' => function ($q) {
                $q->orderBy('fecha', 'desc')->take(10);
            },
            'recetas' => function ($q) {
                $q->orderBy('fecha_emision', 'desc')->take(10);
            }
        ]);

        return view('clientes.show', compact('cliente'));
    }

    public function edit(Cliente $cliente)
    {
        return view('clientes.edit', compact('cliente'));
    }

    public function update(UpdateClienteRequest $request, Cliente $cliente)
    {
        $cliente->update($request->validated());

        return redirect()->route('clientes.index')
            ->with('success', "Cliente '{$cliente->nombre}' actualizado exitosamente.");
    }

    public function destroy(Cliente $cliente)
    {
        $cliente->update(['activo' => !$cliente->activo]);
        $estado = $cliente->activo ? 'activado' : 'desactivado';

        return redirect()->route('clientes.index')
            ->with('success', "Cliente '{$cliente->nombre}' {$estado} correctamente.");
    }
}
