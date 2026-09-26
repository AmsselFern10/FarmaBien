<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\LaboratorioController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\RecetaController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\Api\ProductoPresentacionController;
use App\Http\Controllers\PresentacionController;
use App\Http\Controllers\AjusteController;
use App\Http\Controllers\CajaController;
use App\Http\Controllers\PublicCatalogoController;
use App\Http\Controllers\PromocionController;
use App\Http\Controllers\BenchmarkController;

/*
|--------------------------------------------------------------------------
| Rutas Públicas
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// Catálogo Público de Medicamentos para Clientes
Route::get('/catalogo', [PublicCatalogoController::class, 'index'])->name('catalogo.publico');

// Telemetría y Métricas de Rendimiento ISO/IEC 25023 (Para Render, Local y Monitoreo)
Route::get('/benchmark/metricas', [BenchmarkController::class, 'metricas'])->name('benchmark.metricas');

// Health Check Endpoint (Para Monitoreo, Docker, Render y Cloud Hosting)
Route::get('/health', function () {
    $dbConnected = false;
    $dbDriver = config('database.default');
    try {
        \Illuminate\Support\Facades\DB::connection()->getPdo();
        $dbConnected = true;
    } catch (\Throwable $e) {}

    $status = $dbConnected ? 200 : 503;
    return response()->json([
        'status'      => $dbConnected ? 'healthy' : 'unhealthy',
        'app'         => config('app.name', 'FarmaBien'),
        'version'     => '2.0.0',
        'environment' => config('app.env'),
        'database'    => [
            'driver'    => $dbDriver,
            'connected' => $dbConnected,
        ],
        'cache'       => config('cache.default'),
        'timestamp'   => now()->toIso8601String(),
    ], $status);
})->name('health');

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
    | ENDPOINTS JSON / API BAJO SESIÓN WEB
    |--------------------------------------------------------------------------
    */
    Route::prefix('api')->name('api.')->middleware('throttle:100,1')->group(function () {
        Route::get('/productos/{producto}/presentaciones', [ProductoPresentacionController::class, 'index'])
            ->name('productos.presentaciones.index');

        Route::post('/productos/{producto}/presentaciones', [ProductoPresentacionController::class, 'store'])
            ->name('productos.presentaciones.store');

        Route::get('/productos/buscar', [VentaController::class, 'buscarProductos'])
            ->name('productos.buscar');

        Route::get('/recetas/buscar', [RecetaController::class, 'buscarRecetas'])
            ->name('recetas.buscar');
    });

    /*
    |--------------------------------------------------------------------------
    | VENTAS - CRUD Y ACCIONES
    |--------------------------------------------------------------------------
    */
    Route::post('ventas/{venta}/anular', [VentaController::class, 'anular'])->name('ventas.anular');
    Route::get('ventas/{venta}/ticket', [VentaController::class, 'ticket'])->name('ventas.ticket');
    Route::resource('ventas', VentaController::class);

    /*
    |--------------------------------------------------------------------------
    | COMPRAS - CRUD Y ACCIONES
    |--------------------------------------------------------------------------
    */
    Route::post('compras/{compra}/anular', [CompraController::class, 'anular'])->name('compras.anular');
    Route::get('compras/{compra}/imprimir', [CompraController::class, 'imprimir'])->name('compras.imprimir');
    Route::get('compras/{compra}/pdf', [CompraController::class, 'generarPDF'])->name('compras.pdf');
    Route::get('compras/{compra}/ticket', [CompraController::class, 'imprimirTicket'])->name('compras.ticket');
    Route::get('compras/verificar-lote', [CompraController::class, 'verificarNumeroLote'])->name('compras.verificar-lote');
    Route::resource('compras', CompraController::class);

    /*
    |--------------------------------------------------------------------------
    | CATÁLOGOS: PRODUCTOS, LABORATORIOS, CATEGORÍAS, CLIENTES, PROVEEDORES
    |--------------------------------------------------------------------------
    */
    Route::post('productos/ia/buscar', [ProductoController::class, 'iaBuscar'])->name('productos.ia.buscar');
    Route::post('productos/{producto}/ia-ficha', [ProductoController::class, 'iaFicha'])->name('productos.ia.ficha');
    Route::resource('productos', ProductoController::class);
    Route::resource('laboratorios', LaboratorioController::class);
    Route::resource('categorias', CategoriaController::class);
    Route::resource('clientes', ClienteController::class);
    Route::resource('proveedores', ProveedorController::class)->parameters(['proveedores' => 'proveedor']);
    Route::post('promociones/{promocion}/toggle-activo', [PromocionController::class, 'toggleActivo'])->name('promociones.toggle-activo');
    Route::resource('promociones', PromocionController::class)->parameters(['promociones' => 'promocion']);
    Route::post('presentaciones/{presentacion}/toggle-activo', [PresentacionController::class, 'toggleActivo'])->name('presentaciones.toggle-activo');
    Route::resource('presentaciones', PresentacionController::class)->parameters(['presentaciones' => 'presentacion']);

    /*
    |--------------------------------------------------------------------------
    | INVENTARIO Y KARDEX
    |--------------------------------------------------------------------------
    */
    Route::prefix('inventario')->name('inventario.')->group(function () {
        Route::get('/', [InventarioController::class, 'index'])->name('index');
        Route::get('/movimientos', [InventarioController::class, 'movimientos'])->name('movimientos');
        Route::get('/lotes', [InventarioController::class, 'lotes'])->name('lotes');
        Route::get('/kardex-producto/{producto}', [InventarioController::class, 'kardexProducto'])->name('kardex-producto');
        Route::get('/alertas', [InventarioController::class, 'alertas'])->name('alertas');
        Route::post('/baja-vencidos', [InventarioController::class, 'bajaVencidos'])->name('baja-vencidos');
        Route::get('/ajustar', [InventarioController::class, 'ajustar'])->name('ajustar');
        Route::post('/ajustar', [InventarioController::class, 'storeAjuste'])->name('ajustar.store');
    });

    /*
    |--------------------------------------------------------------------------
    | RECETAS MÉDICAS
    |--------------------------------------------------------------------------
    */
    Route::get('recetas/{receta}/archivo', [RecetaController::class, 'verArchivo'])->name('recetas.archivo');
    Route::post('recetas/{receta}/validar', [RecetaController::class, 'validar'])->name('recetas.validar');
    Route::post('recetas/{receta}/estado', [RecetaController::class, 'cambiarEstado'])->name('recetas.estado');
    Route::resource('recetas', RecetaController::class);

    /*
    |--------------------------------------------------------------------------
    | REPORTES GERENCIALES
    |--------------------------------------------------------------------------
    */
    Route::prefix('reportes')->name('reportes.')->group(function () {
        Route::get('/', [ReporteController::class, 'index'])->name('index');
        Route::get('/ventas', [ReporteController::class, 'ventas'])->name('ventas');
        Route::get('/compras', [ReporteController::class, 'compras'])->name('compras');
        Route::get('/inventario', [ReporteController::class, 'inventario'])->name('inventario');
        Route::get('/productos-mas-vendidos', [ReporteController::class, 'productosMasVendidos'])->name('productos-mas-vendidos');
        Route::get('/productos-bajo-stock', [ReporteController::class, 'productosBajoStock'])->name('productos-bajo-stock');
        Route::get('/clientes', [ReporteController::class, 'clientes'])->name('clientes');
        Route::get('/recetas', [ReporteController::class, 'recetas'])->name('recetas');
    });

    /*
    |--------------------------------------------------------------------------
    | GESTIÓN Y CONTROL DE CAJAS (Multicaja, Arqueos, Turnos)
    |--------------------------------------------------------------------------
    */
    Route::prefix('cajas')->name('cajas.')->group(function () {
        Route::get('/', [CajaController::class, 'index'])->name('index');
        Route::post('/', [CajaController::class, 'store'])->name('store');
        Route::put('/{caja}', [CajaController::class, 'update'])->name('update');
        Route::post('/{caja}/toggle', [CajaController::class, 'toggleEstado'])->name('toggle');
        Route::delete('/{caja}', [CajaController::class, 'destroy'])->name('destroy');
        Route::post('/{caja}/abrir', [CajaController::class, 'abrir'])->name('abrir');
        Route::get('/sesiones', [CajaController::class, 'sesiones'])->name('sesiones');
        Route::get('/sesion/{sesion}', [CajaController::class, 'show'])->name('show');
        Route::post('/sesion/{sesion}/cerrar', [CajaController::class, 'cerrar'])->name('cerrar');
        Route::post('/sesion/{sesion}/movimientos', [CajaController::class, 'storeMovimiento'])->name('movimientos.store');
        Route::get('/sesion/{sesion}/ticket', [CajaController::class, 'ticketArqueo'])->name('ticket');
    });

    /*
    |--------------------------------------------------------------------------
    | AJUSTES Y CONFIGURACIÓN DEL SISTEMA
    |--------------------------------------------------------------------------
    */
    Route::get('/ajustes', [AjusteController::class, 'index'])->name('ajustes.index');
    Route::post('/ajustes', [AjusteController::class, 'update'])->name('ajustes.update');
    Route::post('/ajustes/toggle-catalogo', [AjusteController::class, 'toggleCatalogo'])->name('ajustes.toggle-catalogo');
});

/*
|--------------------------------------------------------------------------
| PANEL DE ADMINISTRACIÓN (Solo Admin)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:Admin'])->group(function () {
    Route::get('/admin', function () {
        return view('dashboard.admin');
    })->name('admin.dashboard');

    Route::resource('usuarios', UserController::class);
});

/*
|--------------------------------------------------------------------------
| Autenticación (Laravel Breeze)
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';
