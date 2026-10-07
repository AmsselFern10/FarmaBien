<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use App\Services\ProveedorService;
use App\Http\Requests\StoreProveedorRequest;
use App\Http\Requests\UpdateProveedorRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProveedorController extends Controller
{
    public function __construct(
        protected ProveedorService $proveedorService
    ) {
        $this->middleware('permission:ver proveedores')->only(['index', 'show', 'buscarAjax']);
        $this->middleware('permission:crear proveedores')->only(['create', 'store']);
        $this->middleware('permission:editar proveedores')->only(['edit', 'update']);
        $this->middleware('permission:desactivar proveedores')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $proveedores = $this->proveedorService->listar($request->all(), perPage(15));

        return view('proveedores.index', compact('proveedores'));
    }

    public function create()
    {
        return view('proveedores.create');
    }

    public function store(StoreProveedorRequest $request)
    {
        $proveedor = $this->proveedorService->crear($request->validated());

        if ($request->boolean('crear_otro')) {
            return redirect()->route('proveedores.create')
                ->with('success', "Proveedor '{$proveedor->nombre}' registrado exitosamente. Listo para registrar el siguiente proveedor.");
        }

        return redirect()->route('proveedores.index')
            ->with('success', "Proveedor '{$proveedor->nombre}' registrado exitosamente.");
    }

    public function show(Proveedor $proveedor)
    {
        $proveedor->load(['compras' => function ($q) {
            $q->orderBy('fecha', 'desc')->take(10);
        }]);

        return view('proveedores.show', compact('proveedor'));
    }

    public function edit(Proveedor $proveedor)
    {
        return view('proveedores.edit', compact('proveedor'));
    }

    public function update(UpdateProveedorRequest $request, Proveedor $proveedor)
    {
        $this->proveedorService->actualizar($proveedor, $request->validated());

        return redirect()->route('proveedores.index')
            ->with('success', "Proveedor '{$proveedor->nombre}' actualizado exitosamente.");
    }

    public function destroy(Proveedor $proveedor)
    {
        $nuevoEstado = $this->proveedorService->toggleEstado($proveedor);

        return redirect()->route('proveedores.index')
            ->with('success', "Proveedor '{$proveedor->nombre}' {$nuevoEstado} correctamente.");
    }

    /**
     * Búsqueda AJAX de Proveedores (Componente C / Compras).
     */
    public function buscarAjax(Request $request): JsonResponse
    {
        $q = trim((string)$request->input('q', ''));
        $proveedores = $this->proveedorService->buscarAjax($q, 10);

        return response()->json($proveedores);
    }
}
