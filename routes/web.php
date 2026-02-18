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
use App\Http\Controllers\ProductoAIController;



use App\Http\Controllers\Api\ProductoPresentacionController;

/*
|--------------------------------------------------------------------------
| Ruta Pública
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('welcome');
});


Route::post('/productos/{producto}/ia/ficha', [ProductoAIController::class, 'ficha'])
    ->name('productos.ia.ficha')
    ->middleware(['auth']);

Route::post('/productos/ia/buscar', [ProductoAIController::class, 'buscar'])
    ->name('productos.ia.buscar')
    ->middleware(['auth']);

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
// =========================================================================
// MÓDULO DE VENTAS
// =========================================================================
Route::group(['middleware' => ['auth']], function () {

    // Recurso principal (index, create, store, show, edit, update, destroy)
    Route::resource('ventas', VentaController::class);

    // Acciones de impresión y documentos
    Route::controller(VentaController::class)->prefix('ventas/{venta}')->group(function () {
        
        Route::get('ticket', 'ticket')
            ->name('ventas.ticket')
            ->middleware('permission:ver ventas');

        Route::get('imprimir-a4', 'imprimirA4')
            ->name('ventas.imprimir')
            ->middleware('permission:ver ventas');

        Route::get('pdf', 'generarPDF')
            ->name('ventas.pdf')
            ->middleware('permission:ver ventas');

        Route::post('anular', 'anular')
            ->name('ventas.anular')
            ->middleware('permission:anular ventas');
    });

    // Búsqueda específica (Fuera del prefijo {venta} porque usa un ID genérico)
    Route::get('ventas/buscar/{id}', [VentaController::class, 'buscarPorId'])
        ->name('ventas.buscar')
        ->middleware('permission:ver ventas');

});
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

    Route::get('/kardex-lote/{lote}', [InventarioController::class, 'kardexLote'])
        ->name('kardex-lote');

  
    Route::get('/lote/{lote}', [InventarioController::class, 'showLote'])
        ->name('show-lote');

    Route::get('/valorizacion', [InventarioController::class, 'valorizacion'])
        ->name('valorizacion');
        

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
        ->name('store-ajuste') // <-- Antes decía 'ajustar.store'
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

     Route::get('/flujo-caja', [ReporteController::class, 'flujoCaja'])
    ->name('flujo-caja'); // Este es el nombre real
    Route::get('/productos-mas-vendidos', [ReporteController::class, 'productosMasVendidos'])
        ->name('productos-mas-vendidos')
        ->middleware('permission:ver reportes ventas');

    Route::get('/productos-bajo-stock', [ReporteController::class, 'productosBajoStock'])
        ->name('productos-bajo-stock')
        ->middleware('permission:ver reportes inventario');

    

    Route::get('/ajustes', [ReporteController::class, 'ajustes'])
        ->name('ajustes')
        ->middleware('permission:ver reportes inventario'); // Ajusta el permiso según necesites

    Route::get('/lotes', [ReporteController::class, 'lotes'])
        ->name('lotes')
        ->middleware('permission:ver reportes inventario');

    Route::get('/movimientos', [ReporteController::class, 'movimientos'])
        ->name('movimientos')
        ->middleware('permission:ver reportes inventario');

    Route::get('/vencimientos', [ReporteController::class, 'vencimientos'])
        ->name('vencimientos')
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