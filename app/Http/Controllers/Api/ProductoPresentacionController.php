<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\PresentacionProducto;
use Illuminate\Http\Request;

class ProductoPresentacionController extends Controller
{
    public function index(Producto $producto)
    {
        $presentaciones = $producto->presentacionesActivas()
            ->orderBy('unidades_por_presentacion', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'presentaciones' => $presentaciones
        ]);
    }

    public function store(Request $request, Producto $producto)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string', 'max:150'],
            'unidades_por_presentacion' => ['required', 'integer', 'min:1'],
            'precio_compra' => ['nullable', 'numeric', 'min:0'],
            'precio_venta' => ['nullable', 'numeric', 'min:0'],
            'codigo_barras' => ['nullable', 'string', 'max:50'],
            'orden' => ['nullable', 'integer'],
        ]);

        $presentacion = $producto->presentaciones()->create($validated);

        return response()->json([
            'success' => true,
            'presentacion' => $presentacion,
            'message' => 'Presentación agregada exitosamente.'
        ]);
    }
}
