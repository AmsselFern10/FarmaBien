<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\RecetaController;
use App\Http\Controllers\ReporteController;

// ✅ API (JSON) para presentaciones por producto (para el create de compras)
use App\Http\Controllers\Api\ProductoPresentacionController;

/*
|--------------------------------------------------------------------------
| Ruta Pública
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Dashboard Principal
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

/*
|--------------------------------------------------------------------------
| Rutas Autenticadas
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | PERFIL DE USUARIO
    |--------------------------------------------------------------------------
    */
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    /*
    |--------------------------------------------------------------------------
    | ENDPOINTS JSON (tipo API) bajo sesión WEB
    | Para cargar/crear presentaciones desde el create de compras
    |--------------------------------------------------------------------------
    */
    Route::prefix('api')->name('api.')->group(function () {

        // Cargar presentaciones por producto (para combobox)
        Route::get('/productos/{producto}/presentaciones', [ProductoPresentacionController::class, 'index'])
            ->name('productos.presentaciones.index')
            ->middleware('permission:registrar compras');

        // Crear presentación desde modal
        Route::post('/productos/{producto}/presentaciones', [ProductoPresentacionController::class, 'store'])
            ->name('productos.presentaciones.store')
            ->middleware('permission:registrar compras');
    });

    /*
    |--------------------------------------------------------------------------
    | VENTAS - CRUD COMPLETO
    |--------------------------------------------------------------------------
    */
    Route::resource('ventas', VentaController::class);

    // Rutas adicionales para ventas
    Route::post('ventas/{venta}/anular', [VentaController::class, 'anular'])
        ->name('ventas.anular')
        ->middleware('permission:anular ventas');

    Route::get('ventas/{venta}/ticket', [VentaController::class, 'ticket'])
        ->name('ventas.ticket')
        ->middleware('permission:ver ventas');
    Route::get('ventas/{venta}/imprimir-a4', [VentaController::class, 'imprimirA4'])
    ->name('ventas.imprimir')
    ->middleware('permission:ver ventas');

    Route::get('ventas/{venta}/pdf', [VentaController::class, 'generarPDF'])
    ->name('ventas.pdf')
    ->middleware('permission:ver ventas');
    Route::get('ventas/buscar/{id}', [VentaController::class, 'buscarPorId'])
    ->name('ventas.buscar')
    ->middleware('permission:ver ventas');
    /*
    |--------------------------------------------------------------------------
    | COMPRAS - CRUD COMPLETO
    |--------------------------------------------------------------------------
    */// Rutas personalizadas primero
Route::get('/compras/buscar/{id}', [CompraController::class, 'buscarPorId'])
    ->name('compras.buscar');

Route::post('compras/{compra}/anular', [CompraController::class, 'anular'])
    ->name('compras.anular');

Route::get('/compras/{compra}/imprimir', [CompraController::class, 'imprimir'])
    ->name('compras.imprimir');

Route::get('/compras/{compra}/pdf', [CompraController::class, 'generarPDF'])
    ->name('compras.pdf');

Route::get('/compras/{compra}/ticket', [CompraController::class, 'imprimirTicket'])
    ->name('compras.ticket');

Route::get('compras/verificar-lote', [CompraController::class, 'verificarNumeroLote'])
    ->name('compras.verificar-lote');

// AL FINAL el resource
Route::resource('compras', CompraController::class);

    /*
    |--------------------------------------------------------------------------
    | PRODUCTOS - CRUD COMPLETO
    |--------------------------------------------------------------------------
    */
    Route::resource('productos', ProductoController::class);

    /*
    |--------------------------------------------------------------------------
    | CATEGORÍAS - CRUD COMPLETO
    |--------------------------------------------------------------------------
    */
    Route::resource('categorias', CategoriaController::class);

    /*
    |--------------------------------------------------------------------------
    | INVENTARIO
    |--------------------------------------------------------------------------
    */
    Route::prefix('inventario')->name('inventario.')->group(function () {
        Route::get('/', [InventarioController::class, 'index'])
            ->name('index')
            ->middleware('permission:ver movimientos inventario');

        Route::get('/movimientos', [InventarioController::class, 'movimientos'])
            ->name('movimientos')
            ->middleware('permission:ver movimientos inventario');

        Route::get('/lotes', [InventarioController::class, 'lotes'])
            ->name('lotes')
            ->middleware('permission:ver movimientos inventario');

        Route::get('/kardex-producto/{producto}', [InventarioController::class, 'kardexProducto'])
            ->name('kardex-producto');

        Route::get('/alertas', [InventarioController::class, 'alertas'])
            ->name('alertas')
            ->middleware('permission:ver movimientos inventario');

        Route::get('/ajustar', [InventarioController::class, 'ajustar'])
            ->name('ajustar')
            ->middleware('permission:ajustar inventario');

        Route::post('/ajustar', [InventarioController::class, 'storeAjuste'])
            ->name('ajustar.store')
            ->middleware('permission:ajustar inventario');
    });

    /*
    |--------------------------------------------------------------------------
    | CLIENTES
    |--------------------------------------------------------------------------
    */
    Route::resource('clientes', ClienteController::class);

    /*
    |--------------------------------------------------------------------------
    | PROVEEDORES
    |--------------------------------------------------------------------------
    */

Route::resource('proveedores', ProveedorController::class)
    ->parameters([
        'proveedores' => 'proveedor'
    ]);

/*
    /*
    |--------------------------------------------------------------------------
    | RECETAS MÉDICAS
    |--------------------------------------------------------------------------
    */
    Route::resource('recetas', RecetaController::class);

    // Rutas adicionales para recetas
    Route::post('recetas/{receta}/validar', [RecetaController::class, 'validar'])
        ->name('recetas.validar')
        ->middleware('permission:validar recetas');

    /*
    |--------------------------------------------------------------------------
    | REPORTES
    |--------------------------------------------------------------------------
    */
    Route::prefix('reportes')->name('reportes.')->group(function () {
        Route::get('/', [ReporteController::class, 'index'])
            ->name('index')
            ->middleware('permission:ver reportes ventas');

        Route::get('/ventas', [ReporteController::class, 'ventas'])
            ->name('ventas')
            ->middleware('permission:ver reportes ventas');

        Route::get('/compras', [ReporteController::class, 'compras'])
            ->name('compras')
            ->middleware('permission:ver reportes compras');

        Route::get('/inventario', [ReporteController::class, 'inventario'])
            ->name('inventario')
            ->middleware('permission:ver reportes inventario');

        Route::get('/productos-mas-vendidos', [ReporteController::class, 'productosMasVendidos'])
            ->name('productos-mas-vendidos')
            ->middleware('permission:ver reportes ventas');

        Route::get('/productos-bajo-stock', [ReporteController::class, 'productosBajoStock'])
            ->name('productos-bajo-stock')
            ->middleware('permission:ver reportes inventario');
    });
});

// Rutas personalizadas primero
Route::get('/compras/buscar/{id}', [CompraController::class, 'buscarPorId'])
    ->name('compras.buscar');

Route::post('compras/{compra}/anular', [CompraController::class, 'anular'])
    ->name('compras.anular');

Route::get('/compras/{compra}/imprimir', [CompraController::class, 'imprimir'])
    ->name('compras.imprimir');

Route::get('/compras/{compra}/pdf', [CompraController::class, 'generarPDF'])
    ->name('compras.pdf');

Route::get('/compras/{compra}/ticket', [CompraController::class, 'imprimirTicket'])
    ->name('compras.ticket');

Route::get('compras/verificar-lote', [CompraController::class, 'verificarNumeroLote'])
    ->name('compras.verificar-lote');

// AL FINAL el resource
Route::resource('compras', CompraController::class);



Route::middleware(['auth', 'role:Admin'])->group(function () {

    /*
    | Panel de Administración
    */
    Route::get('/admin', function () {
        return view('dashboard.admin');
    })->name('admin.dashboard');

    /*
    | USUARIOS - CRUD COMPLETO (Solo Admin)
    */
    Route::resource('usuarios', UserController::class);
});

/*
|--------------------------------------------------------------------------
| Autenticación (Laravel Breeze)
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';