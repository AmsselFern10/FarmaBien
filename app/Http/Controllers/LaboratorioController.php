<?php

namespace App\Http\Controllers;

use App\Models\Laboratorio;
use App\Http\Requests\StoreLaboratorioRequest;
use App\Http\Requests\UpdateLaboratorioRequest;
use Illuminate\Http\Request;

class LaboratorioController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:ver laboratorios')->only(['index', 'show']);
        $this->middleware('permission:crear laboratorios')->only(['create', 'store']);
        $this->middleware('permission:editar laboratorios')->only(['edit', 'update']);
        $this->middleware('permission:desactivar laboratorios')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $query = Laboratorio::withCount('productos');

        if ($request->filled('buscar')) {
            $buscar = trim($request->input('buscar'));
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('codigo', 'like', "%{$buscar}%")
                  ->orWhere('pais_origen', 'like', "%{$buscar}%")
                  ->orWhere('contacto', 'like', "%{$buscar}%");
            });
        }

        if ($request->filled('estado')) {
            if ($request->estado === 'activos') {
                $query->where('activo', true);
            } elseif ($request->estado === 'inactivos') {
                $query->where('activo', false);
            }
        }

        $laboratorios = $query->orderBy('nombre', 'asc')->paginate(15)->withQueryString();

        return view('laboratorios.index', compact('laboratorios'));
    }

    public function create()
    {
        return view('laboratorios.create');
    }

    public function store(StoreLaboratorioRequest $request)
    {
        $laboratorio = Laboratorio::create($request->validated());

        return redirect()->route('laboratorios.index')
            ->with('success', "Laboratorio '{$laboratorio->nombre}' registrado correctamente.");
    }

    public function show(Laboratorio $laboratorio)
    {
        $productos = $laboratorio->productos()->with('categoria')->paginate(10);

        return view('laboratorios.show', compact('laboratorio', 'productos'));
    }

    public function edit(Laboratorio $laboratorio)
    {
        return view('laboratorios.edit', compact('laboratorio'));
    }

    public function update(UpdateLaboratorioRequest $request, Laboratorio $laboratorio)
    {
        $laboratorio->update($request->validated());

        return redirect()->route('laboratorios.index')
            ->with('success', "Laboratorio '{$laboratorio->nombre}' actualizado correctamente.");
    }

    public function destroy(Laboratorio $laboratorio)
    {
        $laboratorio->update(['activo' => !$laboratorio->activo]);
        $estado = $laboratorio->activo ? 'activado' : 'desactivado';

        return redirect()->route('laboratorios.index')
            ->with('success', "Laboratorio '{$laboratorio->nombre}' {$estado} correctamente.");
    }
}
