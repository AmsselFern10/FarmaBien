<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\PresentacionProducto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Exception;

class ProductoPresentacionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:ver presentaciones|ver productos')->only(['index']);
        $this->middleware('permission:crear presentaciones|crear productos')->only(['store']);
    }

    public function index(Producto $producto)
    {
        $presentaciones = $producto->presentacionesActivas()
            ->orderBy('unidades_por_presentacion', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'producto_id' => $producto->id,
            'presentaciones' => $presentaciones
        ]);
    }

    public function store(Request $request, Producto $producto)
    {
        try {
            $validated = $request->validate([
                'nombre' => ['required', 'string', 'max:100'],
                'descripcion' => ['nullable', 'string', 'max:150'],
                'unidades_por_presentacion' => ['required', 'integer', 'min:1', 'max:100000'],
                'precio_compra' => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
                'precio_venta' => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
                'codigo_barras' => ['nullable', 'string', 'max:50'],
                'es_unidad_base' => ['nullable', 'boolean'],
                'orden' => ['nullable', 'integer', 'min:0'],
            ]);

            $presentacion = DB::transaction(function () use ($validated, $producto) {
                if (!empty($validated['es_unidad_base'])) {
                    PresentacionProducto::where('producto_id', $producto->id)
                        ->lockForUpdate()
                        ->update(['es_unidad_base' => false]);
                }

                $validated['producto_id'] = $producto->id;
                $validated['activo'] = true;
                $validated['codigo_barras'] = !empty($validated['codigo_barras']) ? trim($validated['codigo_barras']) : null;

                $presentacion = PresentacionProducto::create($validated);

                Log::info('Presentación agregada por API', [
                    'presentacion_id' => $presentacion->id,
                    'producto_id' => $producto->id,
                    'user_id' => auth()->id(),
                ]);

                return $presentacion;
            });

            return response()->json([
                'success' => true,
                'presentacion' => $presentacion,
                'message' => 'Presentación agregada exitosamente.'
            ], 201);
        } catch (ValidationException $ve) {
            return response()->json([
                'success' => false,
                'message' => 'Los datos enviados no son válidos.',
                'errors' => $ve->errors()
            ], 422);
        } catch (Exception $e) {
            Log::error('Error API al agregar presentación', [
                'producto_id' => $producto->id,
                'user_id' => auth()->id(),
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al registrar la presentación en el sistema.'
            ], 500);
        }
    }
}
