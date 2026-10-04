<?php

namespace App\Http\Controllers;

use App\Models\PresentacionProducto;
use App\Models\Producto;
use App\Http\Requests\StorePresentacionRequest;
use App\Http\Requests\UpdatePresentacionRequest;
use App\Services\PresentacionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use Exception;

class PresentacionController extends Controller
{
    public function __construct(
        protected PresentacionService $presentacionService
    ) {
        $this->middleware('permission:ver presentaciones|ver productos')->only(['index', 'show']);
        $this->middleware('permission:crear presentaciones|crear productos')->only(['create', 'store']);
        $this->middleware('permission:editar presentaciones|editar productos')->only(['edit', 'update', 'toggleActivo']);
        $this->middleware('permission:desactivar presentaciones|desactivar productos')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $filtros = $request->only(['buscar', 'producto_id', 'estado']);
        $presentaciones = $this->presentacionService->listarPresentaciones($filtros, perPage(20))->withQueryString();
        $productos = Producto::activos()->orderBy('nombre')->get(['id', 'nombre']);

        return view('presentaciones.index', compact('presentaciones', 'productos'));
    }

    public function create()
    {
        $productos = Producto::activos()->orderBy('nombre')->get(['id', 'nombre']);
        $productosJson = json_encode($productos->map(fn($p) => ['id' => $p->id, 'nombre' => $p->nombre])->values());

        return view('presentaciones.create', compact('productos', 'productosJson'));
    }

    public function store(StorePresentacionRequest $request)
    {
        try {
            $presentacion = $this->presentacionService->crearPresentacion($request->validated());

            if ($request->boolean('crear_otro')) {
                return redirect()->route('presentaciones.create')
                    ->with('success', "Presentación '{$presentacion->nombre}' creada exitosamente. Listo para registrar la siguiente presentación.");
            }

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
        $productosJson = json_encode($productos->map(fn($p) => ['id' => $p->id, 'nombre' => $p->nombre])->values());

        return view('presentaciones.edit', compact('presentacion', 'productos', 'productosJson'));
    }

    public function update(UpdatePresentacionRequest $request, PresentacionProducto $presentacion)
    {
        try {
            $this->presentacionService->actualizarPresentacion($presentacion, $request->validated());

            return redirect()->route('presentaciones.show', $presentacion)
                ->with('success', "Presentación '{$presentacion->nombre}' actualizada correctamente.");
        } catch (Exception $e) {
            Log::error('Error al actualizar presentación', [
                'presentacion_id' => $presentacion->id,
                'user_id'         => auth()->id(),
                'message'         => $e->getMessage(),
            ]);

            return back()->withInput()->with('error', 'Error al actualizar la presentación: ' . $e->getMessage());
        }
    }

    public function destroy(PresentacionProducto $presentacion)
    {
        try {
            $this->presentacionService->eliminarPresentacion($presentacion);

            return redirect()->route('presentaciones.index')
                ->with('success', "Presentación '{$presentacion->nombre}' eliminada correctamente.");
        } catch (Exception $e) {
            Log::warning('Intento fallido de eliminación de presentación', [
                'presentacion_id' => $presentacion->id,
                'user_id'         => auth()->id(),
                'message'         => $e->getMessage(),
            ]);

            return back()->with('error', $e->getMessage());
        }
    }

    public function toggleActivo(PresentacionProducto $presentacion)
    {
        try {
            $estado = $this->presentacionService->cambiarEstado($presentacion);

            return back()->with('success', "Presentación '{$presentacion->nombre}' {$estado} correctamente.");
        } catch (Exception $e) {
            Log::error('Error al modificar estado de presentación', [
                'presentacion_id' => $presentacion->id,
                'user_id'         => auth()->id(),
                'message'         => $e->getMessage(),
            ]);

            return back()->with('error', 'No se pudo modificar el estado de la presentación: ' . $e->getMessage());
        }
    }
}
