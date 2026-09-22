<?php

namespace App\Http\Controllers;

use App\Models\PresentacionProducto;
use App\Models\Producto;
use App\Http\Requests\StorePresentacionRequest;
use App\Http\Requests\UpdatePresentacionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use Exception;

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
        try {
            $presentacion = DB::transaction(function () use ($request) {
                $data = $request->validated();

                // Si se marca como unidad base, actualizar atómicamente las demás presentaciones del producto
                if (!empty($data['es_unidad_base'])) {
                    PresentacionProducto::where('producto_id', $data['producto_id'])
                        ->lockForUpdate()
                        ->update(['es_unidad_base' => false]);
                }

                $presentacion = PresentacionProducto::create($data);

                Log::info('Presentación comercial registrada', [
                    'presentacion_id' => $presentacion->id,
                    'producto_id' => $presentacion->producto_id,
                    'nombre' => $presentacion->nombre,
                    'unidades' => $presentacion->unidades_por_presentacion,
                    'user_id' => auth()->id(),
                ]);

                return $presentacion;
            });

            return redirect()->route('presentaciones.show', $presentacion)
                ->with('success', "Presentación '{$presentacion->nombre}' creada exitosamente.");
        } catch (QueryException $qe) {
            Log::error('Error de base de datos al registrar presentación', [
                'user_id' => auth()->id(),
                'message' => $qe->getMessage(),
            ]);

            return back()->withInput()->with('error', 'Error al guardar la presentación comercial debido a un conflicto de datos.');
        } catch (Exception $e) {
            Log::error('Error general al registrar presentación', [
                'user_id' => auth()->id(),
                'message' => $e->getMessage(),
            ]);

            return back()->withInput()->with('error', 'Error al crear la presentación: ' . $e->getMessage());
        }
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
        try {
            DB::transaction(function () use ($request, $presentacion) {
                $locked = PresentacionProducto::where('id', $presentacion->id)->lockForUpdate()->firstOrFail();
                $data = $request->validated();

                if (!empty($data['es_unidad_base'])) {
                    PresentacionProducto::where('producto_id', $locked->producto_id)
                        ->where('id', '!=', $locked->id)
                        ->lockForUpdate()
                        ->update(['es_unidad_base' => false]);
                }

                $locked->update($data);

                Log::info('Presentación comercial actualizada', [
                    'presentacion_id' => $locked->id,
                    'producto_id' => $locked->producto_id,
                    'nombre' => $locked->nombre,
                    'user_id' => auth()->id(),
                ]);
            });

            return redirect()->route('presentaciones.show', $presentacion)
                ->with('success', "Presentación '{$presentacion->nombre}' actualizada correctamente.");
        } catch (Exception $e) {
            Log::error('Error al actualizar presentación', [
                'presentacion_id' => $presentacion->id,
                'user_id' => auth()->id(),
                'message' => $e->getMessage(),
            ]);

            return back()->withInput()->with('error', 'Error al actualizar la presentación: ' . $e->getMessage());
        }
    }

    public function destroy(PresentacionProducto $presentacion)
    {
        try {
            DB::transaction(function () use ($presentacion) {
                $locked = PresentacionProducto::where('id', $presentacion->id)->lockForUpdate()->firstOrFail();

                // Validación estricta de integridad referencial
                if ($locked->detallesVentas()->exists() || $locked->detallesCompras()->exists()) {
                    throw new Exception("No se puede eliminar la presentación '{$locked->nombre}' porque cuenta con registros históricos en ventas o compras. Desactívala para ocultarla.");
                }

                $nombre = $locked->nombre;
                $locked->delete();

                Log::info('Presentación eliminada exitosamente', [
                    'presentacion_id' => $presentacion->id,
                    'nombre' => $nombre,
                    'user_id' => auth()->id(),
                ]);
            });

            return redirect()->route('presentaciones.index')
                ->with('success', "Presentación '{$presentacion->nombre}' eliminada correctamente.");
        } catch (Exception $e) {
            Log::warning('Intento fallido de eliminación de presentación', [
                'presentacion_id' => $presentacion->id,
                'user_id' => auth()->id(),
                'message' => $e->getMessage(),
            ]);

            return back()->with('error', $e->getMessage());
        }
    }

    public function toggleActivo(PresentacionProducto $presentacion)
    {
        try {
            $estado = DB::transaction(function () use ($presentacion) {
                $locked = PresentacionProducto::where('id', $presentacion->id)->lockForUpdate()->firstOrFail();
                $nuevoEstado = !$locked->activo;
                $locked->update(['activo' => $nuevoEstado]);

                Log::info('Estado de presentación modificado', [
                    'presentacion_id' => $locked->id,
                    'nombre' => $locked->nombre,
                    'nuevo_estado' => $nuevoEstado ? 'activada' : 'desactivada',
                    'user_id' => auth()->id(),
                ]);

                return $nuevoEstado ? 'activada' : 'desactivada';
            });

            return back()->with('success', "Presentación '{$presentacion->nombre}' {$estado} correctamente.");
        } catch (Exception $e) {
            Log::error('Error al modificar estado de presentación', [
                'presentacion_id' => $presentacion->id,
                'user_id' => auth()->id(),
                'message' => $e->getMessage(),
            ]);

            return back()->with('error', 'No se pudo modificar el estado de la presentación: ' . $e->getMessage());
        }
    }
}
