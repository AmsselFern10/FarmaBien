<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\PresentacionProducto;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductoPresentacionController extends Controller
{
    /**
     * GET /api/productos/{producto}/presentaciones
     * Retorna el producto y sus presentaciones activas (y ordenadas).
     */
    public function index(Producto $producto)
    {
        $presentaciones = PresentacionProducto::query()
            ->where('producto_id', $producto->id)
            ->where('activo', true)
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get([
                'id',
                'producto_id',
                'nombre',
                'descripcion',
                'unidades_por_presentacion',
                'precio_sugerido',
                'codigo_barras',
                'activo',
                'orden',
            ]);

        return response()->json([
            'producto' => [
                'id' => $producto->id,
                'nombre' => $producto->nombre ?? null,
                'precio_compra' => $producto->precio_compra ?? 0,
            ],
            'presentaciones' => $presentaciones,
        ]);
    }

    /**
     * POST /api/productos/{producto}/presentaciones
     * Crea una presentación para el producto (desde el modal).
     */
    public function store(Request $request, Producto $producto)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:50'],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'unidades_por_presentacion' => ['required', 'integer', 'min:1'],
            'precio_sugerido' => ['nullable', 'numeric', 'min:0'],
            'codigo_barras' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('presentaciones_producto', 'codigo_barras'),
            ],
            'activo' => ['nullable', 'boolean'],
            'orden' => ['nullable', 'integer', 'min:0'],
        ]);

        $presentacion = new PresentacionProducto();
        $presentacion->producto_id = $producto->id;
        $presentacion->nombre = $data['nombre'];
        $presentacion->descripcion = $data['descripcion'] ?? null;
        $presentacion->unidades_por_presentacion = (int)$data['unidades_por_presentacion'];
        $presentacion->precio_sugerido = $data['precio_sugerido'] ?? null;
        $presentacion->codigo_barras = $data['codigo_barras'] ?? null;
        $presentacion->activo = array_key_exists('activo', $data) ? (bool)$data['activo'] : true;
        $presentacion->orden = $data['orden'] ?? 0;
        $presentacion->save();

        return response()->json([
            'ok' => true,
            'presentacion' => [
                'id' => $presentacion->id,
                'producto_id' => $presentacion->producto_id,
                'nombre' => $presentacion->nombre,
                'descripcion' => $presentacion->descripcion,
                'unidades_por_presentacion' => $presentacion->unidades_por_presentacion,
                'precio_sugerido' => $presentacion->precio_sugerido,
                'codigo_barras' => $presentacion->codigo_barras,
                'activo' => $presentacion->activo,
                'orden' => $presentacion->orden,
            ],
        ], 201);
    }
}