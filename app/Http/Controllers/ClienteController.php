<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Services\ClienteService;
use App\Http\Requests\StoreClienteRequest;
use App\Http\Requests\UpdateClienteRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ClienteController extends Controller
{
    public function __construct(
        protected ClienteService $clienteService
    ) {
        $this->middleware('permission:ver clientes')->only(['index', 'show', 'buscarAjax']);
        $this->middleware('permission:crear clientes')->only(['create', 'store']);
        $this->middleware('permission:editar clientes')->only(['edit', 'update']);
        $this->middleware('permission:desactivar clientes')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $clientes = $this->clienteService->listar($request->all(), perPage(15));

        return view('clientes.index', compact('clientes'));
    }

    public function create()
    {
        return view('clientes.create');
    }

    public function store(StoreClienteRequest $request)
    {
        $cliente = $this->clienteService->crear($request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'cliente' => $cliente,
                'message' => 'Cliente registrado exitosamente.'
            ]);
        }

        if ($request->boolean('crear_otro')) {
            return redirect()->route('clientes.create')
                ->with('success', "Cliente '{$cliente->nombre}' registrado exitosamente. Listo para registrar el siguiente cliente.");
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
        $this->clienteService->actualizar($cliente, $request->validated());

        return redirect()->route('clientes.index')
            ->with('success', "Cliente '{$cliente->nombre}' actualizado exitosamente.");
    }

    public function destroy(Cliente $cliente)
    {
        $nuevoEstado = $this->clienteService->toggleEstado($cliente);

        return redirect()->route('clientes.index')
            ->with('success', "Cliente '{$cliente->nombre}' {$nuevoEstado} correctamente.");
    }

    /**
     * Búsqueda AJAX de clientes para POS y selección reactiva.
     */
    public function buscarAjax(Request $request): JsonResponse
    {
        $q = trim((string)$request->input('q', ''));
        $clientes = $this->clienteService->buscarAjax($q, 10);

        return response()->json($clientes);
    }
}
