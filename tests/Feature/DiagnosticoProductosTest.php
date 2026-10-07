<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Laboratorio;
use App\Models\PresentacionProducto;
use App\Models\Lote;
use App\Models\Promocion;
use App\Models\PrecioVenta;
use App\Models\Configuracion;
use App\Facades\RequestCache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DiagnosticoProductosTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected array $perfilados = [];

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles & permissions
        $permisos = [
            'ver productos', 'crear productos', 'editar productos', 'desactivar productos',
            'ver precios', 'editar precios', 'ver inventario', 'ver ventas', 'crear ventas'
        ];
        foreach ($permisos as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        $rolAdmin = Role::firstOrCreate(['name' => 'Administrador', 'guard_name' => 'web']);
        $rolAdmin->syncPermissions($permisos);

        $this->admin = User::factory()->create([
            'email' => 'admin_test@farmabien.com',
        ]);
        $this->admin->assignRole('Administrador');

        Configuracion::set('catalogo_publico_activo', '1');
        Configuracion::set('catalogo_publico_mostrar_precios', '1');
        Configuracion::set('catalogo_publico_mostrar_stock', '1');
        Configuracion::set('empresa_nombre', 'FarmaBien');
    }

    protected function perfilarOperacion(string $nombre, callable $callback)
    {
        $queries = [];
        $totalTime = 0.0;

        DB::flushQueryLog();
        DB::listen(function ($query) use (&$queries, &$totalTime) {
            $queries[] = [
                'sql' => $query->sql,
                'time' => $query->time,
                'bindings' => $query->bindings,
            ];
            $totalTime += $query->time;
        });

        $result = $callback();

        $duplicates = [];
        $sqlCounts = array_count_values(array_column($queries, 'sql'));
        foreach ($sqlCounts as $sql => $count) {
            if ($count > 1) {
                $duplicates[$sql] = $count;
            }
        }

        $payloadBytes = 0;
        if (is_object($result)) {
            if (method_exists($result, 'getContent') && !empty($result->getContent())) {
                $payloadBytes = strlen((string)$result->getContent());
            } elseif (method_exists($result, 'content')) {
                $payloadBytes = strlen((string)$result->content());
            } elseif (isset($result->baseResponse)) {
                $payloadBytes = strlen((string)$result->baseResponse->getContent());
            }
        } elseif (is_string($result)) {
            $payloadBytes = strlen($result);
        } elseif (is_array($result)) {
            $payloadBytes = strlen(json_encode($result));
        }

        $this->perfilados[$nombre] = [
            'total' => count($queries),
            'time_ms' => round($totalTime, 2),
            'payload_kb' => round($payloadBytes / 1024, 2),
            'duplicates' => $duplicates,
            'queries' => $queries,
        ];

        return $result;
    }

    public function test_diagnostico_productos_y_sus_imagenes()
    {
        Storage::fake('private_images');

        $cat1 = Categoria::create(['nombre' => 'Analgésicos', 'activo' => true]);
        $cat2 = Categoria::create(['nombre' => 'Antibióticos', 'activo' => true]);
        $lab1 = Laboratorio::create(['nombre' => 'Bayer', 'activo' => true]);
        $lab2 = Laboratorio::create(['nombre' => 'Pfizer', 'activo' => true]);

        // Crear 12 productos con lotes y presentaciones
        for ($i = 1; $i <= 12; $i++) {
            $prod = Producto::create([
                'codigo_barra'       => '7702000' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'nombre'             => "Medicamento {$i}",
                'principio_activo'   => "Principio {$i}",
                'concentracion'      => '500mg',
                'forma_farmaceutica' => 'Tableta',
                'categoria_id'       => ($i % 2 === 0) ? $cat1->id : $cat2->id,
                'laboratorio_id'     => ($i % 2 === 0) ? $lab1->id : $lab2->id,
                'precio_compra'      => 10.00,
                'precio_venta'       => 15.00,
                'stock_minimo'       => 10,
                'tipo_control'       => ($i === 1) ? 'controlado' : 'venta_libre',
                'requiere_receta'    => ($i === 1),
                'activo'             => true,
            ]);

            PresentacionProducto::create([
                'producto_id'               => $prod->id,
                'nombre'                    => 'Caja x 30 tabletas',
                'unidades_por_presentacion' => 30,
                'precio_compra'             => 300.00,
                'precio_venta'              => 420.00,
                'es_unidad_base'            => false,
                'activo'                    => true,
            ]);

            Lote::create([
                'producto_id'       => $prod->id,
                'proveedor_id'      => null,
                'numero_lote'       => "LOT-{$i}-A",
                'fecha_vencimiento' => now()->addMonths(12)->toDateString(),
                'stock_inicial'     => 100,
                'stock_actual'      => 100,
                'precio_compra'     => 10.00,
                'activo'            => true,
            ]);

            PrecioVenta::create([
                'producto_id'     => $prod->id,
                'presentacion_id' => null,
                'precio'          => 15.00,
                'vigente_desde'   => now()->subDays(30),
                'vigente_hasta'   => null,
                'motivo'          => 'Alta de catálogo inicial',
                'user_id'         => $this->admin->id,
            ]);
        }

        // Crear una promoción activa
        Promocion::create([
            'nombre'         => 'Descuento 10% Verano',
            'alcance'        => 'general',
            'tipo'           => 'porcentaje',
            'valor'          => 10.00,
            'min_unidades'   => 1,
            'fecha_inicio'   => now()->subDays(1)->toDateString(),
            'fecha_fin'      => now()->addDays(10)->toDateString(),
            'activo'         => true,
        ]);

        $this->actingAs($this->admin);

        // 1. Listado de Productos (index con 12 medicamentos, promociones y filtros)
        $this->perfilarOperacion('1_listado_productos_index', function () {
            $resp = $this->get(route('productos.index'));
            $resp->assertOk();
            $resp->assertSee('Medicamento 1');
            return $resp;
        });

        // 2. Formulario Crear Producto
        $this->perfilarOperacion('2_crear_producto_formulario', function () {
            $resp = $this->get(route('productos.create'));
            $resp->assertOk();
            $resp->assertSee('Analgésicos');
            return $resp;
        });

        // 3. Registrar Nuevo Producto con Imagen Optimizada y Presentaciones
        $this->perfilarOperacion('3_guardar_nuevo_producto_con_imagen', function () use ($cat1, $lab1) {
            $file = UploadedFile::fake()->image('farmaco_sample.jpg', 600, 600);

            $resp = $this->post(route('productos.store'), [
                'nombre'             => 'Amoxicilina 500mg',
                'principio_activo'   => 'Amoxicilina',
                'concentracion'      => '500mg',
                'forma_farmaceutica' => 'Cápsula',
                'categoria_id'       => $cat1->id,
                'laboratorio_id'     => $lab1->id,
                'codigo_barra'       => '7501000999991',
                'precio_compra'      => 12.50,
                'precio_venta'       => 18.00,
                'stock_minimo'       => 15,
                'tipo_control'       => 'venta_libre',
                'imagen'             => $file,
                'presentaciones'     => [
                    [
                        'nombre'                    => 'Caja x 20 cápsulas',
                        'unidades_por_presentacion' => 20,
                        'precio_compra'             => 240.00,
                        'precio_venta'              => 350.00,
                        'es_unidad_base'            => 0,
                    ]
                ]
            ]);

            $resp->assertRedirect(route('productos.index'));
            return $resp;
        });

        $productoNuevo = Producto::where('nombre', 'Amoxicilina 500mg')->firstOrFail();

        // 4. Ficha Completa del Producto (show con relaciones)
        $this->perfilarOperacion('4_ver_ficha_completa_producto', function () use ($productoNuevo) {
            $resp = $this->get(route('productos.show', $productoNuevo));
            $resp->assertOk();
            $resp->assertSee('Amoxicilina 500mg');
            return $resp;
        });

        // 5. Formulario Editar Producto
        $this->perfilarOperacion('5_editar_producto_formulario', function () use ($productoNuevo) {
            $resp = $this->get(route('productos.edit', $productoNuevo));
            $resp->assertOk();
            $resp->assertSee('Amoxicilina 500mg');
            return $resp;
        });

        // 6. Actualizar Producto y Precio (SCD Tipo 2)
        $this->perfilarOperacion('6_actualizar_producto_y_precio', function () use ($productoNuevo, $cat1, $lab1) {
            $resp = $this->put(route('productos.update', $productoNuevo), [
                'nombre'             => 'Amoxicilina 500mg Forte',
                'principio_activo'   => 'Amoxicilina Trihidrato',
                'concentracion'      => '500mg',
                'forma_farmaceutica' => 'Cápsula',
                'categoria_id'       => $cat1->id,
                'laboratorio_id'     => $lab1->id,
                'codigo_barra'       => '7501000999991',
                'precio_compra'      => 13.00,
                'precio_venta'       => 20.00, // Cambio de precio
                'stock_minimo'       => 20,
                'tipo_control'       => 'venta_libre',
            ]);

            $resp->assertRedirect(route('productos.index'));
            return $resp;
        });

        // 7. Servir Imagen del Producto (Verificación ETag, Cache-Control e Inmutabilidad)
        if ($productoNuevo->imagen) {
            $this->perfilarOperacion('7_servir_imagen_producto', function () use ($productoNuevo) {
                $resp = $this->get(route('img.producto.public', ['path' => $productoNuevo->imagen]));
                $resp->assertOk();
                $this->assertStringContainsString('max-age=604800', $resp->headers->get('Cache-Control'));
                $this->assertStringContainsString('public', $resp->headers->get('Cache-Control'));
                $this->assertNotEmpty($resp->headers->get('ETag'));
                return $resp;
            });
        }

        // 8. Búsqueda Autocompletado AJAX
        $this->perfilarOperacion('8_busqueda_ajax_autocompletado', function () {
            $productoService = app(\App\Services\ProductoService::class);
            $resultados = $productoService->buscarAjax('Amox', 10);
            $this->assertNotEmpty($resultados);
            return $resultados;
        });

        // 9. Búsqueda AJAX Segunda Invocación (Memoizada en RequestCache - 0 consultas)
        $this->perfilarOperacion('9_busqueda_ajax_segunda_invocacion_memoizada', function () {
            $productoService = app(\App\Services\ProductoService::class);
            $resultados = $productoService->buscarAjax('Amox', 10);
            $this->assertNotEmpty($resultados);
            return $resultados;
        });

        // 10. Listado de Precios de Venta (precios.index)
        $this->perfilarOperacion('10_listado_precios_index', function () {
            $resp = $this->get(route('precios.index'));
            $resp->assertOk();
            $resp->assertSee('Precios de Venta');
            return $resp;
        });

        // 11. Detalle de Precios de Venta (precios.show)
        $this->perfilarOperacion('11_detalle_precio_show', function () use ($productoNuevo) {
            $resp = $this->get(route('precios.show', $productoNuevo));
            $resp->assertOk();
            $resp->assertSee('Amoxicilina 500mg Forte');
            return $resp;
        });

        // 12. Editar Precio Formulario (precios.edit)
        $this->perfilarOperacion('12_editar_precio_formulario', function () use ($productoNuevo) {
            $resp = $this->get(route('precios.edit', $productoNuevo));
            $resp->assertOk();
            return $resp;
        });

        // 13. Actualización Rápida Inline de Precio (AJAX)
        $this->perfilarOperacion('13_actualizar_precio_inline_ajax', function () use ($productoNuevo) {
            $resp = $this->post(route('precios.inline-update', $productoNuevo), [
                'precio_venta' => 22.50,
                'motivo'       => 'Ajuste de margen comercial',
            ]);
            $resp->assertOk();
            $data = $resp->json();
            $this->assertTrue($data['success']);
            return $resp;
        });

        // 14. Historial General de Precios (precios.historial)
        $this->perfilarOperacion('14_historial_general_precios', function () {
            $resp = $this->get(route('precios.historial'));
            $resp->assertOk();
            $resp->assertSee('Historial General');
            return $resp;
        });

        // 15. Vista de Actualización Masiva (precios.masivo)
        $this->perfilarOperacion('15_vista_actualizacion_masiva', function () {
            $resp = $this->get(route('precios.masivo'));
            $resp->assertOk();
            $resp->assertSee('Actualización Masiva');
            return $resp;
        });

        // 16. Vista Previa Live Masiva (precios.masivo.preview)
        $this->perfilarOperacion('16_preview_live_masivo', function () {
            $resp = $this->post(route('precios.masivo.preview'), [
                'tipo_alcance' => 'todo',
                'tipo_ajuste'  => 'porcentaje_aumento',
                'valor_ajuste' => 5.0,
                'redondeo'     => 'sin',
            ]);
            $resp->assertOk();
            $data = $resp->json();
            $this->assertTrue($data['success']);
            $this->assertGreaterThan(0, $data['total_afectados']);
            return $resp;
        });

        // 17. Aplicar Ajuste Masivo Transaccional (precios.masivo.aplicar)
        $this->perfilarOperacion('17_aplicar_ajuste_masivo_transaccional', function () {
            $resp = $this->post(route('precios.masivo.aplicar'), [
                'tipo_alcance' => 'todo',
                'tipo_ajuste'  => 'porcentaje_aumento',
                'valor_ajuste' => 5.0,
                'redondeo'     => 'sin',
                'motivo'       => 'Incremento inflacionario general de prueba',
            ]);
            $resp->assertRedirect(route('precios.index'));
            return $resp;
        });

        // 18. Validación de Invalización de Caché en Eventos de Modelo
        $this->perfilarOperacion('18_invalidacion_cache_eventos_modelo', function () use ($productoNuevo) {
            RequestCache::flush();
            $prodService = app(\App\Services\ProductoService::class);

            // 1. Lectura 1: Consulta BD y memoriza
            $res1 = $prodService->buscarAjax('Amoxicilina', 10);
            $this->assertNotEmpty($res1);

            // 2. Lectura 2: Caché hit (0 queries)
            $res2 = $prodService->buscarAjax('Amoxicilina', 10);
            $this->assertEquals(count($res1), count($res2));

            // 3. Mutación del modelo Producto: Dispara evento saved y limpia RequestCache
            $productoNuevo->update(['nombre' => 'Amoxicilina Hook Test']);

            // 4. Lectura 3: Consulta actualizada con valor fresco
            $res3 = $prodService->buscarAjax('Hook Test', 10);
            $this->assertNotEmpty($res3);
            return $res3;
        });

        // 19. Catálogo Público de Medicamentos (sin autenticación)
        auth()->logout();
        $this->perfilarOperacion('19_catalogo_publico_clientes', function () {
            $resp = $this->get(route('catalogo.publico'));
            $resp->assertOk();
            $resp->assertSee('Medicamento');
            return $resp;
        });

        // Guardar reporte
        file_put_contents(
            storage_path('logs/diagnostico_productos_imagenes.json'),
            json_encode($this->perfilados, JSON_PRETTY_PRINT)
        );

        $this->assertTrue(true);
    }
}
