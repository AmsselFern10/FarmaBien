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

    /*
    |--------------------------------------------------------------------------
    | COMPRAS - CRUD COMPLETO
    |--------------------------------------------------------------------------
    */
    Route::resource('compras', CompraController::class);
    
    // Rutas adicionales para compras
    Route::post('compras/{compra}/anular', [CompraController::class, 'anular'])
        ->name('compras.anular')
        ->middleware('permission:anular compras');
    
    Route::get('compras/verificar-lote', [CompraController::class, 'verificarNumeroLote'])
        ->name('compras.verificar-lote')
        ->middleware('permission:registrar compras');

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
        
        Route::get('/alertas', [InventarioController::class, 'alertas'])
            ->name('alertas')
            ->middleware('permission:ver movimientos inventario');
        
        Route::post('/ajuste', [InventarioController::class, 'ajuste'])
            ->name('ajuste')
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
    Route::resource('proveedores', ProveedorController::class);

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

/*
|--------------------------------------------------------------------------
| Rutas SOLO ADMINISTRADOR
|--------------------------------------------------------------------------
*/
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