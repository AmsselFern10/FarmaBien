<?php
namespace App\Http\Controllers;

use App\Http\Requests\StoreProveedorRequest;
use App\Http\Requests\UpdateProveedorRequest;
use App\Models\Proveedor;
use Illuminate\Http\Request;

class ProveedorController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:ver proveedores')->only(['index', 'show']);
        $this->middleware('permission:crear proveedores')->only(['create', 'store']);
        $this->middleware('permission:editar proveedores')->only(['edit', 'update']);
        $this->middleware('permission:desactivar proveedores')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $query = Proveedor::orderBy('nombre');

        if ($request->filled('buscar')) {
            $query->where(function ($q) use ($request) {
                $q->where('nombre', 'like', "%{$request->buscar}%")
                  ->orWhere('ruc', 'like', "%{$request->buscar}%");
            });
        }

        if ($request->filled('activo')) {
            $query->where('activo', $request->activo);
        } else {
            $query->activos();
        }

        $proveedores = $query->paginate(20);

        return view('proveedores.index', compact('proveedores'));
    }

    public function create()
    {
        return view('proveedores.create');
    }

    public function store(StoreProveedorRequest $request)
    {
        try {
            $proveedor = Proveedor::create($request->validated());

            // ✅ Soporte AJAX (modal en Compras): devolver JSON
            if ($request->expectsJson()) {
                return response()->json([
                    'proveedor' => $proveedor,
                ], 201);
            }
            
            return redirect()
                ->route('proveedores.index')
                ->with('success', "Proveedor '{$proveedor->nombre}' creado correctamente.");
                
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Error al crear el proveedor.',
                ], 500);
            }
            return back()
                ->withInput()
                ->with('error', 'Error al crear el proveedor: ' . $e->getMessage());
        }
    }

    public function show(Proveedor $proveedor)
    {
        $proveedor->load(['compras' => function ($query) {
            $query->orderBy('fecha', 'desc')->limit(10);
        }]);

        return view('proveedores.show', compact('proveedor'));
    }

    public function edit(Proveedor $proveedor)
    {
        return view('proveedores.edit', compact('proveedor'));
    }

    public function update(UpdateProveedorRequest $request, Proveedor $proveedor)
    {
        try {
            $proveedor->update($request->validated());
            
            return redirect()
                ->route('proveedores.show', $proveedor)
                ->with('success', "Proveedor '{$proveedor->nombre}' actualizado correctamente.");
                
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Error al actualizar el proveedor: ' . $e->getMessage());
        }
    }

    public function destroy(Proveedor $proveedor)
    {
        try {
            $proveedor->update(['activo' => false]);
            
            return redirect()
                ->route('proveedores.index')
                ->with('success', "Proveedor '{$proveedor->nombre}' desactivado correctamente.");
                
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Error al desactivar el proveedor: ' . $e->getMessage());
        }
    }
}
