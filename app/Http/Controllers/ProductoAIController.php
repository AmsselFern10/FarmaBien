<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Services\Ai\ProductoAiService;
use App\Services\Ai\ProductoSmartSearchService;
use Illuminate\Http\Request;

class ProductoAIController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:ver productos');
        $this->middleware('throttle:30,1');
    }

    public function ficha(Request $request, Producto $producto, ProductoAiService $ai)
    {
        try {
            $producto->load('categoria');

            $content = $ai->ficha($producto);

            return response()->json([
                'ok' => true,
                'content' => $content,
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'ok' => false,
                'message' => 'No se pudo consultar la IA.',
            ], 500);
        }
    }

    public function buscar(Request $request, ProductoSmartSearchService $smart)
    {
        $data = $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:120'],
        ]);

        try {
            $results = $smart->buscar($data['q'], incluirInactivos: $request->boolean('mostrar_inactivos'), limit: 12);

            return response()->json([
                'ok' => true,
                'results' => $results,
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'ok' => false,
                'message' => 'No se pudo realizar la búsqueda con IA.',
            ], 500);
        }
    }
}
