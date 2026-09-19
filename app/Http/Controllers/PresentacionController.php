<?php

namespace App\Http\Controllers;

use App\Models\PresentacionProducto;
use App\Models\Producto;
use App\Http\Requests\StorePresentacionRequest;
use App\Http\Requests\UpdatePresentacionRequest;
use Illuminate\Http\Request;

class PresentacionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:ver productos')->only(['index', 'show']);
        $this->middleware('permission:crear productos')->only(['create', 'store']);
        $this->middleware('permission:editar productos')->only(['edit', 'update', 'toggleActivo']);
        $this->middleware('permission:eliminar productos')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $query = PresentacionProducto::with('producto')
            ->withCount(['detallesVentas', 'detallesCompras']);

        if ($request->filled('buscar')) {
            $buscar = trim($request->input('buscar'));
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('descripcion', 'like', "%{$buscar}%")
                  ->orWhere('codigo_barras', 'like', "%{$buscar}%")
                  ->orWhereHas('producto', fn ($p) => $p->where('nombre', 'like', "%{$buscar}%"));
            });
        }

        if ($request->filled('producto_id')) {
            $query->where('producto_id', $request->input('producto_id'));
        }

        if ($request->filled('estado')) {
            $query->where('activo', $request->estado === 'activos');
        }

        $presentaciones = $query->orderBy('producto_id')->orderBy('orden')->paginate(20)->withQueryString();
        $productos = Producto::activos()->orderBy('nombre')->get(['id', 'nombre']);

        return view('presentaciones.index', compact('presentaciones', 'productos'));
    }

    public function create()
    {
        $productos = Producto::activos()->orderBy('nombre')->get(['id', 'nombre']);
        return view('presentaciones.create', compact('productos'));
    }

    public function store(StorePresentacionRequest $request)
    {
        $presentacion = PresentacionProducto::create($request->validated());

        return redirect()->route('presentaciones.show', $presentacion)
            ->with('success', "Presentación '{$presentacion->nombre}' creada exitosamente.");
    }

    public function show(PresentacionProducto $presentacion)
    {
        $presentacion->load([
            'producto.laboratorio',
            'detallesVentas.venta',
            'detallesCompras.compra',
        ])->loadCount(['detallesVentas', 'detallesCompras']);

        $totalVentaUnidades = $presentacion->detallesVentas->sum('cantidad');
        $totalCompraUnidades = $presentacion->detallesCompras->sum('cantidad');

        return view('presentaciones.show', compact('presentacion', 'totalVentaUnidades', 'totalCompraUnidades'));
    }

    public function edit(PresentacionProducto $presentacion)
    {
        $presentacion->load('producto');
        $productos = Producto::activos()->orderBy('nombre')->get(['id', 'nombre']);
        return view('presentaciones.edit', compact('presentacion', 'productos'));
    }

    public function update(UpdatePresentacionRequest $request, PresentacionProducto $presentacion)
    {
        $presentacion->update($request->validated());

        return redirect()->route('presentaciones.show', $presentacion)
            ->with('success', "Presentación '{$presentacion->nombre}' actualizada correctamente.");
    }

    public function destroy(PresentacionProducto $presentacion)
    {
        if ($presentacion->detallesVentas()->exists() || $presentacion->detallesCompras()->exists()) {
            return back()->with('error', "No se puede eliminar '{$presentacion->nombre}' porque tiene ventas o compras registradas. Puedes desactivarla.");
        }

        $nombre = $presentacion->nombre;
        $presentacion->delete();

        return redirect()->route('presentaciones.index')
            ->with('success', "Presentación '{$nombre}' eliminada correctamente.");
    }

    public function toggleActivo(PresentacionProducto $presentacion)
    {
        $presentacion->update(['activo' => !$presentacion->activo]);
        $estado = $presentacion->activo ? 'activada' : 'desactivada';

        return back()->with('success', "Presentación '{$presentacion->nombre}' {$estado} correctamente.");
    }
}

