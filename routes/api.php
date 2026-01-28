<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SearchController;
use App\Models\Producto; 

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/search', [SearchController::class, 'search']);
});

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/productos/{producto}/presentaciones', function (Producto $producto) {
    return response()->json([
        'producto' => [
            'id' => $producto->id,
            'nombre' => $producto->nombre,
            'precio_compra' => $producto->precio_compra,
        ],
        'presentaciones' => $producto->presentacionesActivas->map(function ($pres) {
            return [
                'id' => $pres->id,
                'nombre' => $pres->nombre,
                'descripcion' => $pres->descripcion,
                'unidades_por_presentacion' => $pres->unidades_por_presentacion,
                'precio_sugerido' => $pres->precio_sugerido,
            ];
        })
    ]);
});