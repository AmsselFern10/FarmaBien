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
use App\Models\Configuracion;
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

        $this->perfilados[$nombre] = [
            'total' => count($queries),
            'time_ms' => round($totalTime, 2),
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

        // 1. Listado de Productos (index con 12 medicamentos, promociones y filtros)
        $this->actingAs($this->admin);
        $this->perfilarOperacion('1_listado_productos_index', function () {
            $resp = $this->get(route('productos.index'));
            $resp->assertOk();
            $resp->assertSee('Medicamento 1');
        });

        // 2. Formulario Crear Producto
        $this->perfilarOperacion('2_crear_producto_formulario', function () {
            $resp = $this->get(route('productos.create'));
            $resp->assertOk();
            $resp->assertSee('Analgésicos');
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
        });

        $productoNuevo = Producto::where('nombre', 'Amoxicilina 500mg')->firstOrFail();

        // 4. Ficha Completa del Producto (show con relaciones)
        $this->perfilarOperacion('4_ver_ficha_completa_producto', function () use ($productoNuevo) {
            $resp = $this->get(route('productos.show', $productoNuevo));
            $resp->assertOk();
            $resp->assertSee('Amoxicilina 500mg');
        });

        // 5. Formulario Editar Producto
        $this->perfilarOperacion('5_editar_producto_formulario', function () use ($productoNuevo) {
            $resp = $this->get(route('productos.edit', $productoNuevo));
            $resp->assertOk();
            $resp->assertSee('Amoxicilina 500mg');
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
        });

        // 7. Servir Imagen del Producto (Verificación ETag, Cache-Control e Inmutabilidad)
        if ($productoNuevo->imagen) {
            $this->perfilarOperacion('7_servir_imagen_producto', function () use ($productoNuevo) {
                $resp = $this->get(route('img.producto.public', ['path' => $productoNuevo->imagen]));
                $resp->assertOk();
                $this->assertStringContainsString('max-age=604800', $resp->headers->get('Cache-Control'));
                $this->assertStringContainsString('public', $resp->headers->get('Cache-Control'));
                $this->assertNotEmpty($resp->headers->get('ETag'));
            });
        }

        // 8. Búsqueda Autocompletado AJAX
        $this->perfilarOperacion('8_busqueda_ajax_autocompletado', function () {
            $productoService = app(\App\Services\ProductoService::class);
            $resultados = $productoService->buscarAjax('Amox', 10);
            $this->assertNotEmpty($resultados);
        });

        // 9. Catálogo Público de Medicamentos (sin autenticación)
        auth()->logout();
        $this->perfilarOperacion('9_catalogo_publico_clientes', function () {
            $resp = $this->get(route('catalogo.publico'));
            $resp->assertOk();
            $resp->assertSee('Amoxicilina');
        });

        // Guardar reporte
        file_put_contents(
            storage_path('logs/diagnostico_productos_imagenes.json'),
            json_encode($this->perfilados, JSON_PRETTY_PRINT)
        );

        $this->assertTrue(true);
    }
}
