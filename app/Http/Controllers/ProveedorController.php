<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use App\Models\AuditLog;
use App\Http\Requests\StoreProveedorRequest;
use App\Http\Requests\UpdateProveedorRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $query = Proveedor::withCount('compras');

        if ($request->filled('buscar')) {
            $buscar = trim($request->input('buscar'));
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('ruc', 'like', "%{$buscar}%")
                  ->orWhere('contacto', 'like', "%{$buscar}%")
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

        $proveedores = $query->orderBy('nombre', 'asc')->paginate(15)->withQueryString();

        return view('proveedores.index', compact('proveedores'));
    }

    public function create()
    {
        return view('proveedores.create');
    }

    public function store(StoreProveedorRequest $request)
    {
        $proveedor = DB::transaction(function () use ($request) {
            $proveedor = Proveedor::create($request->validated());

            AuditLog::log('proveedores', 'crear', "Proveedor '{$proveedor->nombre}' registrado", [
                'proveedor_id' => $proveedor->id,
                'ruc' => $proveedor->ruc,
            ]);

            return $proveedor;
        });

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
        DB::transaction(function () use ($request, $proveedor) {
            $locked = Proveedor::where('id', $proveedor->id)->lockForUpdate()->firstOrFail();
            $locked->update($request->validated());

            AuditLog::log('proveedores', 'actualizar', "Proveedor '{$locked->nombre}' actualizado", [
                'proveedor_id' => $locked->id,
            ]);
        });

        return redirect()->route('proveedores.index')
            ->with('success', "Proveedor '{$proveedor->nombre}' actualizado exitosamente.");
    }

    public function destroy(Proveedor $proveedor)
    {
        $nuevoEstado = DB::transaction(function () use ($proveedor) {
            $locked = Proveedor::where('id', $proveedor->id)->lockForUpdate()->firstOrFail();
            $estadoBool = !$locked->activo;
            $locked->update(['activo' => $estadoBool]);

            AuditLog::log(
                'proveedores',
                $estadoBool ? 'activar' : 'desactivar',
                "Proveedor '{$locked->nombre}' " . ($estadoBool ? 'activado' : 'desactivado'),
                ['proveedor_id' => $locked->id]
            );

            return $estadoBool ? 'activado' : 'desactivado';
        });

        return redirect()->route('proveedores.index')
            ->with('success', "Proveedor '{$proveedor->nombre}' {$nuevoEstado} correctamente.");
    }
}
