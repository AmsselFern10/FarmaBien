<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\ConteoInventarioController;
use App\Http\Controllers\DevolucionCompraController;
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
use App\Http\Controllers\HistorialPrecioController;
use App\Http\Controllers\PromocionController;
use App\Http\Controllers\BenchmarkController;
use App\Http\Controllers\ImagenController;
use App\Http\Controllers\BusquedaController;
use App\Http\Controllers\ControladoController;
use App\Http\Controllers\DevolucionController;
use App\Http\Controllers\CuentaPorPagarController;
use App\Http\Controllers\OrdenCompraController;
use App\Http\Controllers\NotificacionController;
use App\Http\Controllers\PrecioVentaController;

/*
|--------------------------------------------------------------------------
| Imágenes Privadas — solo usuarios autenticados
|--------------------------------------------------------------------------
*/
Route::get('/img/{path}', [ImagenController::class, 'serve'])
    ->where('path', '.+')
    ->middleware('auth')
    ->name('img.serve');

/*
|--------------------------------------------------------------------------
| Imágenes Públicas de Productos — accesible sin login (solo carpeta productos/)
| Permite que el catálogo público muestre las fotos de medicamentos.
|--------------------------------------------------------------------------
*/
Route::get('/img-producto/{path}', [ImagenController::class, 'servePublic'])
    ->where('path', '.+')
    ->name('img.producto.public');

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

// Telemetría y Métricas de Rendimiento ISO/IEC 25023 (requiere autenticación — expone KPIs internos)
Route::get('/benchmark/metricas', [BenchmarkController::class, 'metricas'])
    ->middleware(['auth', 'role:Admin'])
    ->name('benchmark.metricas');

// Health Check Endpoint (Para Monitoreo, Docker, Render y Cloud Hosting)
// Solo devuelve status/timestamp en producción para no exponer stack tecnológico
Route::get('/health', function () {
    $dbConnected = false;
    try {
        \Illuminate\Support\Facades\DB::connection()->getPdo();
        $dbConnected = true;
    } catch (\Throwable $e) {}

    $status = $dbConnected ? 200 : 503;

    $payload = [
        'status'    => $dbConnected ? 'healthy' : 'unhealthy',
        'timestamp' => now()->toIsoString(),
    ];

    // En entornos no-producción se añade info de diagnóstico (útil en local/staging)
    if (!app()->environment('production')) {
        $payload['app']      = config('app.name', 'FarmaBien');
        $payload['version']  = '2.0.0';
        $payload['env']      = config('app.env');
        $payload['database'] = ['driver' => config('database.default'), 'connected' => $dbConnected];
        $payload['cache']    = config('cache.default');
    }

    return response()->json($payload, $status);
})->name('health');

/*
|--------------------------------------------------------------------------
| Dashboard Principal
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/dashboard/api/metricas', [DashboardController::class, 'metricas'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard.api.metricas');

/*
|--------------------------------------------------------------------------
| Rutas Autenticadas
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | BÚSQUEDA GLOBAL
    |--------------------------------------------------------------------------
    */
    Route::get('/busqueda', [BusquedaController::class, 'global'])->name('busqueda.global');

    /*
    |--------------------------------------------------------------------------
    | MEDICAMENTOS CONTROLADOS (MINSA Nicaragua)
    |--------------------------------------------------------------------------
    */
    Route::get('/controlados/libro', [ControladoController::class, 'libroControl'])->name('controlados.libro');
    Route::get('/controlados/exportar/excel', [ControladoController::class, 'exportarExcel'])->name('controlados.excel');
    Route::post('/controlados/{registro}/evidencia', [ControladoController::class, 'subirEvidencia'])->name('controlados.evidencia.store');
    Route::get('/controlados/{registro}/evidencia', [ControladoController::class, 'verEvidencia'])->name('controlados.evidencia');
    Route::get('/controlados/{registro}', [ControladoController::class, 'show'])->name('controlados.show');
    Route::post('/controlados', [ControladoController::class, 'store'])->name('controlados.store');
    Route::get('/controlados', [ControladoController::class, 'index'])->name('controlados.index');

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

        Route::get('/productos/{producto}/historial-precios', [HistorialPrecioController::class, 'apiHistorialProducto'])
            ->name('productos.historial-precios');

        Route::get('/notificaciones/resumen', [NotificacionController::class, 'resumen'])
            ->name('notificaciones.resumen');

        // Búsqueda AJAX Catálogos y Medicamentos (Componente C / Filtros / POS)
        Route::get('/clientes/buscar-ajax', [ClienteController::class, 'buscarAjax'])->name('clientes.buscar-ajax');
        Route::get('/medicamentos/buscar-ajax', [InventarioController::class, 'buscarMedicamentosAjax'])->name('medicamentos.buscar-ajax');
        Route::get('/proveedores/buscar-ajax', [ProveedorController::class, 'buscarAjax'])->name('proveedores.buscar-ajax');
        Route::get('/laboratorios/buscar-ajax', [LaboratorioController::class, 'buscarAjax'])->name('laboratorios.buscar-ajax');
        Route::get('/categorias/buscar-ajax', [CategoriaController::class, 'buscarAjax'])->name('categorias.buscar-ajax');
    });

    /*
    |--------------------------------------------------------------------------
    | VENTAS Y DEVOLUCIONES - CRUD Y ACCIONES
    |--------------------------------------------------------------------------
    */
    Route::post('ventas/{venta}/anular', [VentaController::class, 'anular'])->name('ventas.anular');
    Route::get('ventas/{venta}/ticket', [VentaController::class, 'ticket'])->name('ventas.ticket');
    Route::get('ventas/{venta}/pdf', [VentaController::class, 'generarPDF'])->name('ventas.pdf');
    Route::resource('ventas', VentaController::class);

    Route::get('devoluciones/{devolucion}/ticket', [DevolucionController::class, 'ticket'])->name('devoluciones.ticket');
    Route::resource('devoluciones', DevolucionController::class)->parameters(['devoluciones' => 'devolucion'])->except(['edit', 'update', 'destroy']);

    // Devoluciones a proveedor (DevolucionCompra)
    Route::prefix('compras/devoluciones')->name('compras.devoluciones.')->group(function () {
        Route::get('/', [DevolucionCompraController::class, 'index'])->name('index');
        Route::get('/nueva', [DevolucionCompraController::class, 'create'])->name('create');
        Route::post('/', [DevolucionCompraController::class, 'store'])->name('store');
        Route::get('/{devolucionCompra}', [DevolucionCompraController::class, 'show'])->name('show');
        Route::post('/{devolucionCompra}/enviar', [DevolucionCompraController::class, 'marcarEnviada'])->name('enviar');
        Route::post('/{devolucionCompra}/confirmar', [DevolucionCompraController::class, 'confirmar'])->name('confirmar');
        Route::post('/{devolucionCompra}/anular', [DevolucionCompraController::class, 'anular'])->name('anular');
    });


    /*
    |--------------------------------------------------------------------------
    | COMPRAS, CUENTAS POR PAGAR, ÓRDENES DE COMPRA Y REORDEN
    |--------------------------------------------------------------------------
    */
    Route::get('compras/cuentas-por-pagar', [CuentaPorPagarController::class, 'index'])->name('cuentas-por-pagar.index');
    Route::post('compras/cuentas-por-pagar/directa', [CuentaPorPagarController::class, 'storeDirecta'])->name('cuentas-por-pagar.directa.store');
    Route::get('compras/cuentas-por-pagar/{compra}', [CuentaPorPagarController::class, 'show'])->name('cuentas-por-pagar.show');
    Route::post('compras/cuentas-por-pagar/{compra}/abonos', [CuentaPorPagarController::class, 'storeAbono'])->name('cuentas-por-pagar.abonos.store');

    Route::get('compras/ordenes/{ordenes_compra}/imprimir', [OrdenCompraController::class, 'imprimir'])->name('ordenes-compras.imprimir');
    Route::get('compras/ordenes/{ordenes_compra}/recibir', [OrdenCompraController::class, 'recibirMercancia'])->name('ordenes-compras.recibir');
    Route::post('compras/ordenes/{ordenes_compra}/cancelar', [OrdenCompraController::class, 'cancelar'])->name('ordenes-compras.cancelar');
    Route::resource('compras/ordenes', OrdenCompraController::class)->names('ordenes-compras')->parameters(['ordenes' => 'ordenes_compra']);

    Route::get('compras/comparador-precios', [HistorialPrecioController::class, 'comparador'])->name('compras.comparador-precios');
    Route::post('compras/cotizaciones', [HistorialPrecioController::class, 'storeCotizacion'])->name('compras.cotizaciones.store');
    Route::get('compras/sugerencias-reorden', [HistorialPrecioController::class, 'sugerenciasReorden'])->name('compras.sugerencias-reorden');
    Route::post('compras/{compra}/anular', [CompraController::class, 'anular'])->name('compras.anular');
    Route::get('compras/{compra}/imprimir', [CompraController::class, 'imprimir'])->name('compras.imprimir');
    Route::get('compras/{compra}/pdf', [CompraController::class, 'generarPDF'])->name('compras.pdf');
    Route::get('compras/{compra}/ticket', [CompraController::class, 'imprimirTicket'])->name('compras.ticket');
    Route::get('compras/verificar-lote', [CompraController::class, 'verificarNumeroLote'])->name('compras.verificar-lote');
    Route::resource('compras', CompraController::class);

    /*
    |--------------------------------------------------------------------------
    | PRECIOS DE VENTA (Catálogos & Maestros)
    |--------------------------------------------------------------------------
    */
    Route::get('precios/masivo', [PrecioVentaController::class, 'masivo'])->name('precios.masivo');
    Route::post('precios/masivo', [PrecioVentaController::class, 'aplicarMasivo'])->name('precios.masivo.aplicar');
    Route::post('precios/masivo/preview', [PrecioVentaController::class, 'apiPreviewMasivo'])->name('precios.masivo.preview');
    Route::get('precios/historial-general', [PrecioVentaController::class, 'historialGeneral'])->name('precios.historial');
    Route::get('precios/exportar', [PrecioVentaController::class, 'exportar'])->name('precios.exportar');
    Route::get('precios/buscar-ajax', [PrecioVentaController::class, 'apiBuscar'])->name('precios.buscar-ajax');
    Route::post('precios/{producto}/inline', [PrecioVentaController::class, 'inlineUpdate'])->name('precios.inline-update');
    Route::get('precios/{producto}/edit', [PrecioVentaController::class, 'edit'])->name('precios.edit');
    Route::put('precios/{producto}', [PrecioVentaController::class, 'update'])->name('precios.update');
    Route::get('precios/{producto}', [PrecioVentaController::class, 'show'])->name('precios.show');
    Route::get('precios', [PrecioVentaController::class, 'index'])->name('precios.index');

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
        Route::get('/lotes/crear', [InventarioController::class, 'createLote'])->name('lotes.create');
        Route::post('/lotes', [InventarioController::class, 'storeLote'])->name('lotes.store');
        Route::put('/lotes/{lote}', [InventarioController::class, 'updateLote'])->name('lotes.update');
        Route::get('/kardex-producto/{producto}', [InventarioController::class, 'kardexProducto'])->name('kardex-producto');
        Route::get('/alertas', [InventarioController::class, 'alertas'])->name('alertas');
        Route::post('/baja-vencidos', [InventarioController::class, 'bajaVencidos'])->name('baja-vencidos');

        // Conteos / Tomas de inventario físico
        Route::get('/tomas', [ConteoInventarioController::class, 'index'])->name('tomas.index');
        Route::get('/tomas/crear', [ConteoInventarioController::class, 'create'])->name('tomas.create');
        Route::post('/tomas', [ConteoInventarioController::class, 'store'])->name('tomas.store');
        Route::get('/tomas/{conteo}', [ConteoInventarioController::class, 'show'])->name('tomas.show');

        Route::prefix('conteos')->name('conteos.')->group(function () {
            Route::get('/', [ConteoInventarioController::class, 'index'])->name('index');
            Route::get('/nuevo', [ConteoInventarioController::class, 'create'])->name('create');
            Route::post('/', [ConteoInventarioController::class, 'store'])->name('store');
            Route::post('/conteo-previo', [ConteoInventarioController::class, 'conteoPrevio'])->name('conteo-previo');
            Route::get('/{conteo}', [ConteoInventarioController::class, 'show'])->name('show');
            Route::post('/{conteo}/guardar', [ConteoInventarioController::class, 'guardarConteo'])->name('guardar');
            Route::post('/{conteo}/guardar-fila', [ConteoInventarioController::class, 'guardarFila'])->name('guardar-fila');
            Route::post('/{conteo}/aprobar', [ConteoInventarioController::class, 'aprobar'])->name('aprobar');
            Route::post('/{conteo}/cancelar', [ConteoInventarioController::class, 'cancelar'])->name('cancelar');
        });
        Route::get('/ajustar', [InventarioController::class, 'ajustar'])->name('ajustar');
        Route::post('/ajustar', [InventarioController::class, 'storeAjuste'])->name('ajustar.store');
        Route::get('/buscar-medicamentos-ajax', [InventarioController::class, 'buscarMedicamentosAjax'])->name('buscar-medicamentos-ajax');
        Route::get('/buscar-proveedores-ajax', [InventarioController::class, 'buscarProveedoresAjax'])->name('buscar-proveedores-ajax');
        Route::get('/buscar-laboratorios-ajax', [InventarioController::class, 'buscarLaboratoriosAjax'])->name('buscar-laboratorios-ajax');
        Route::get('/buscar-categorias-ajax', [InventarioController::class, 'buscarCategoriasAjax'])->name('buscar-categorias-ajax');
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
        Route::get('/cajas', [ReporteController::class, 'cajas'])->name('cajas');
        Route::get('/auditorias', [ReporteController::class, 'auditorias'])->name('auditorias');
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
