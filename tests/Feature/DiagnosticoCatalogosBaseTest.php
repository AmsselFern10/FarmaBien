<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Cliente;
use App\Models\Proveedor;
use App\Models\Categoria;
use App\Models\Laboratorio;
use App\Models\Producto;
use App\Models\Venta;
use App\Models\Compra;
use App\Models\Receta;
use App\Facades\RequestCache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DiagnosticoCatalogosBaseTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected array $perfilados = [];

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles & permissions for the 4 catalogs
        $permisos = [
            'ver clientes', 'crear clientes', 'editar clientes', 'desactivar clientes',
            'ver proveedores', 'crear proveedores', 'editar proveedores', 'desactivar proveedores',
            'ver categorias', 'crear categorias', 'editar categorias', 'desactivar categorias',
            'ver laboratorios', 'crear laboratorios', 'editar laboratorios', 'desactivar laboratorios',
            'ver productos', 'ver ventas', 'ver compras', 'ver recetas',
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

    public function test_diagnostico_completo_catalogos_base()
    {
        $this->actingAs($this->admin);

        // -------------------------------------------------------------
        // SEED DATA: Clientes, Proveedores, Categorias, Laboratorios
        // -------------------------------------------------------------
        for ($i = 1; $i <= 15; $i++) {
            Cliente::create([
                'nombre'    => "Cliente Paciente {$i}",
                'documento' => '08011990' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'telefono'  => '9988' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'email'     => "cliente{$i}@farmabien.hn",
                'direccion' => "Barrio El Centro Casa #{$i}, Tegucigalpa",
                'activo'    => true,
            ]);

            Proveedor::create([
                'nombre'    => "Distribuidora Farmacéutica {$i} S.A.",
                'ruc'       => '08019012' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'contacto'  => "Lic. Agente {$i}",
                'telefono'  => '2233' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'email'     => "proveedor{$i}@distribuidora.hn",
                'direccion' => "Parque Industrial Nave #{$i}, San Pedro Sula",
                'activo'    => true,
            ]);

            Categoria::create([
                'nombre'      => "Categoría Farmacéutica {$i}",
                'descripcion' => "Grupo terapéutico y familia farmacológica número {$i}",
                'activo'      => true,
            ]);

            Laboratorio::create([
                'nombre'      => "Laboratorio Internacional {$i}",
                'codigo'      => "LAB-" . str_pad($i, 3, '0', STR_PAD_LEFT),
                'contacto'    => "Dr. Representante {$i}",
                'telefono'    => '2244' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'email'       => "lab{$i}@laboratorio.hn",
                'pais_origen' => 'Honduras',
                'activo'      => true,
            ]);
        }

        $clienteObj = Cliente::first();
        $proveedorObj = Proveedor::first();
        $categoriaObj = Categoria::first();
        $laboratorioObj = Laboratorio::first();

        // Populate relations for detail views
        for ($j = 1; $j <= 5; $j++) {
            Producto::create([
                'codigo_barra'       => '7701100' . str_pad($j, 5, '0', STR_PAD_LEFT),
                'nombre'             => "Medicamento Demo {$j}",
                'principio_activo'   => "Principio Activo {$j}",
                'concentracion'      => '500mg',
                'forma_farmaceutica' => 'Tableta',
                'categoria_id'       => $categoriaObj->id,
                'laboratorio_id'     => $laboratorioObj->id,
                'precio_compra'      => 10.00,
                'precio_venta'       => 15.00,
                'stock_minimo'       => 5,
                'activo'             => true,
            ]);
        }

        // =============================================================
        // 1. CLIENTES (Listado, Crear, Store, Show, Edit, Update, Toggle, Ajax, Cache)
        // =============================================================
        $this->perfilarOperacion('1_clientes_listado', function () {
            $resp = $this->get(route('clientes.index'));
            $resp->assertOk();
            $resp->assertSee('Padrón de Clientes y Pacientes');
        });

        $this->perfilarOperacion('2_clientes_crear_formulario', function () {
            $resp = $this->get(route('clientes.create'));
            $resp->assertOk();
        });

        $this->perfilarOperacion('3_clientes_guardar', function () {
            $resp = $this->post(route('clientes.store'), [
                'nombre'    => 'Carlos Roberto Reina',
                'documento' => '0801198500123',
                'telefono'  => '95551234',
                'email'     => 'creina@correo.hn',
                'direccion' => 'Colonia Palmira, Tegucigalpa',
                'activo'    => 1,
            ]);
            $resp->assertRedirect(route('clientes.index'));
        });

        $this->perfilarOperacion('4_clientes_detalle_show', function () use ($clienteObj) {
            $resp = $this->get(route('clientes.show', $clienteObj));
            $resp->assertOk();
        });

        $this->perfilarOperacion('5_clientes_editar_formulario', function () use ($clienteObj) {
            $resp = $this->get(route('clientes.edit', $clienteObj));
            $resp->assertOk();
        });

        $this->perfilarOperacion('6_clientes_actualizar', function () use ($clienteObj) {
            $resp = $this->put(route('clientes.update', $clienteObj), [
                'nombre'    => 'Cliente Paciente 1 Modificado',
                'documento' => $clienteObj->documento,
                'telefono'  => '99880001',
                'email'     => 'cliente1_mod@farmabien.hn',
                'direccion' => 'Direccion Actualizada',
                'activo'    => 1,
            ]);
            $resp->assertRedirect(route('clientes.index'));
        });

        $this->perfilarOperacion('7_clientes_desactivar_activar', function () {
            $cliToggle = Cliente::create(['nombre' => 'Cliente Para Desactivar', 'activo' => true]);
            $resp = $this->delete(route('clientes.destroy', $cliToggle));
            $resp->assertRedirect(route('clientes.index'));
        });

        $this->perfilarOperacion('8_clientes_busqueda_ajax', function () {
            $resp = $this->get(route('api.clientes.buscar-ajax', ['q' => 'Paciente']));
            $resp->assertOk();
            $data = $resp->json();
            $this->assertNotEmpty($data);
            $this->assertLessThanOrEqual(10, count($data));
        });

        $this->perfilarOperacion('9_clientes_cache_invalidation_test', function () use ($clienteObj) {
            RequestCache::flush();
            $cliService = app(\App\Services\ClienteService::class);
            
            // Primer llamado consulta BD y memoriza
            $res1 = $cliService->buscarAjax('Paciente', 10);
            $this->assertNotEmpty($res1);

            // Segundo llamado idéntico retorna del RequestCache (0 consultas BD)
            $res2 = $cliService->buscarAjax('Paciente', 10);
            $this->assertEquals(count($res1), count($res2));

            // Actualización del modelo dispara evento saved y limpia prefijo clientes:
            $clienteObj->update(['nombre' => 'Paciente Actualizado Hook']);

            // Tercer llamado consulta con valor nuevo actualizado
            $res3 = $cliService->buscarAjax('Paciente Actualizado', 10);
            $this->assertNotEmpty($res3);
        });

        // =============================================================
        // 2. PROVEEDORES (Listado, Crear, Store, Show, Edit, Update, Toggle, Ajax, Cache)
        // =============================================================
        $this->perfilarOperacion('10_proveedores_listado', function () {
            $resp = $this->get(route('proveedores.index'));
            $resp->assertOk();
            $resp->assertSee('Directorio de Proveedores');
        });

        $this->perfilarOperacion('11_proveedores_crear_formulario', function () {
            $resp = $this->get(route('proveedores.create'));
            $resp->assertOk();
        });

        $this->perfilarOperacion('12_proveedores_guardar', function () {
            $resp = $this->post(route('proveedores.store'), [
                'nombre'    => 'Droguería Americana S. de R.L.',
                'ruc'       => '08019999000123',
                'contacto'  => 'Ing. Roberto Gómez',
                'telefono'  => '22998877',
                'email'     => 'ventas@drogueriaamericana.hn',
                'direccion' => 'Blvd. Suyapa, Tegucigalpa',
                'activo'    => 1,
            ]);
            $resp->assertRedirect(route('proveedores.index'));
        });

        $this->perfilarOperacion('13_proveedores_detalle_show', function () use ($proveedorObj) {
            $resp = $this->get(route('proveedores.show', $proveedorObj));
            $resp->assertOk();
        });

        $this->perfilarOperacion('14_proveedores_editar_formulario', function () use ($proveedorObj) {
            $resp = $this->get(route('proveedores.edit', $proveedorObj));
            $resp->assertOk();
        });

        $this->perfilarOperacion('15_proveedores_actualizar', function () use ($proveedorObj) {
            $resp = $this->put(route('proveedores.update', $proveedorObj), [
                'nombre'    => 'Distribuidora 1 Modificada S.A.',
                'ruc'       => $proveedorObj->ruc,
                'contacto'  => 'Lic. Contacto Nuevo',
                'telefono'  => '22330001',
                'email'     => 'proveedor1_mod@distribuidora.hn',
                'direccion' => 'Nueva Direccion Proveedor',
                'activo'    => 1,
            ]);
            $resp->assertRedirect(route('proveedores.index'));
        });

        $this->perfilarOperacion('16_proveedores_desactivar_activar', function () {
            $provToggle = Proveedor::create(['nombre' => 'Proveedor Para Desactivar', 'ruc' => '08019999888877', 'activo' => true]);
            $resp = $this->delete(route('proveedores.destroy', $provToggle));
            $resp->assertRedirect(route('proveedores.index'));
        });

        $this->perfilarOperacion('17_proveedores_busqueda_ajax', function () {
            $resp = $this->get(route('api.proveedores.buscar-ajax', ['q' => 'Distribuidora']));
            $resp->assertOk();
            $data = $resp->json();
            $this->assertNotEmpty($data);
            $this->assertLessThanOrEqual(10, count($data));
        });

        $this->perfilarOperacion('18_proveedores_cache_invalidation_test', function () use ($proveedorObj) {
            RequestCache::flush();
            $provService = app(\App\Services\ProveedorService::class);
            
            // Primer llamado consulta BD y memoriza
            $res1 = $provService->buscarAjax('Distribuidora', 10);
            $this->assertNotEmpty($res1);

            // Segundo llamado idéntico retorna del RequestCache
            $res2 = $provService->buscarAjax('Distribuidora', 10);
            $this->assertEquals(count($res1), count($res2));

            // Actualización del modelo dispara evento saved y limpia prefijo proveedores:
            $proveedorObj->update(['nombre' => 'Distribuidora Hook Modificada']);

            // Tercer llamado consulta con valor nuevo actualizado
            $res3 = $provService->buscarAjax('Distribuidora Hook', 10);
            $this->assertNotEmpty($res3);
        });

        // =============================================================
        // 3. CATEGORÍAS (Listado, Crear, Store, Show, Edit, Update, Toggle, Ajax, Cache)
        // =============================================================
        $this->perfilarOperacion('19_categorias_listado', function () {
            $resp = $this->get(route('categorias.index'));
            $resp->assertOk();
            $resp->assertSee('Categorías Terapéuticas');
        });

        $this->perfilarOperacion('20_categorias_crear_formulario', function () {
            $resp = $this->get(route('categorias.create'));
            $resp->assertOk();
        });

        $this->perfilarOperacion('21_categorias_guardar', function () {
            $resp = $this->post(route('categorias.store'), [
                'nombre'      => 'Antihistamínicos y Antialérgicos',
                'descripcion' => 'Fármacos para rinitis, urticaria y reacciones alérgicas',
                'activo'      => 1,
            ]);
            $resp->assertRedirect(route('categorias.index'));
        });

        $this->perfilarOperacion('22_categorias_detalle_show', function () use ($categoriaObj) {
            $resp = $this->get(route('categorias.show', $categoriaObj));
            $resp->assertOk();
        });

        $this->perfilarOperacion('23_categorias_editar_formulario', function () use ($categoriaObj) {
            $resp = $this->get(route('categorias.edit', $categoriaObj));
            $resp->assertOk();
        });

        $this->perfilarOperacion('24_categorias_actualizar', function () use ($categoriaObj) {
            $resp = $this->put(route('categorias.update', $categoriaObj), [
                'nombre'      => 'Categoría Farmacéutica 1 Actualizada',
                'descripcion' => 'Descripción actualizada del grupo terapéutico',
                'activo'      => 1,
            ]);
            $resp->assertRedirect(route('categorias.index'));
        });

        $this->perfilarOperacion('25_categorias_desactivar_activar', function () {
            $catSinProds = Categoria::create(['nombre' => 'Categoría Sin Productos', 'activo' => true]);
            $resp = $this->delete(route('categorias.destroy', $catSinProds));
            $resp->assertRedirect(route('categorias.index'));
        });

        $this->perfilarOperacion('26_categorias_busqueda_ajax', function () {
            $resp = $this->get(route('api.categorias.buscar-ajax', ['q' => 'Farmacéutica']));
            $resp->assertOk();
            $data = $resp->json();
            $this->assertNotEmpty($data);
            $this->assertLessThanOrEqual(10, count($data));
        });

        $this->perfilarOperacion('27_categorias_cache_invalidation_test', function () use ($categoriaObj) {
            RequestCache::flush();
            $catService = app(\App\Services\CategoriaService::class);
            
            // Primer llamado consulta BD y memoriza
            $res1 = $catService->buscarAjax('Farmacéutica', 10);
            $this->assertNotEmpty($res1);

            // Segundo llamado idéntico retorna del RequestCache
            $res2 = $catService->buscarAjax('Farmacéutica', 10);
            $this->assertEquals(count($res1), count($res2));

            // Actualización del modelo dispara evento saved y limpia prefijo categorias:
            $categoriaObj->update(['nombre' => 'Farmacéutica Hook Modificada']);

            // Tercer llamado consulta con valor nuevo actualizado
            $res3 = $catService->buscarAjax('Farmacéutica Hook', 10);
            $this->assertNotEmpty($res3);
        });

        // =============================================================
        // 4. LABORATORIOS (Listado, Crear, Store, Show, Edit, Update, Toggle, Ajax, Cache)
        // =============================================================
        $this->perfilarOperacion('28_laboratorios_listado', function () {
            $resp = $this->get(route('laboratorios.index'));
            $resp->assertOk();
            $resp->assertSee('Catálogo de Laboratorios');
        });

        $this->perfilarOperacion('29_laboratorios_crear_formulario', function () {
            $resp = $this->get(route('laboratorios.create'));
            $resp->assertOk();
        });

        $this->perfilarOperacion('30_laboratorios_guardar', function () {
            $resp = $this->post(route('laboratorios.store'), [
                'nombre'      => 'Laboratorios Finlay de Honduras',
                'codigo'      => 'LAB-FIN',
                'contacto'    => 'Lic. Farmacéutico Central',
                'telefono'    => '22334455',
                'email'       => 'contacto@finlay.hn',
                'pais_origen' => 'Honduras',
                'activo'      => 1,
            ]);
            $resp->assertRedirect(route('laboratorios.index'));
        });

        $this->perfilarOperacion('31_laboratorios_detalle_show', function () use ($laboratorioObj) {
            $resp = $this->get(route('laboratorios.show', $laboratorioObj));
            $resp->assertOk();
        });

        $this->perfilarOperacion('32_laboratorios_editar_formulario', function () use ($laboratorioObj) {
            $resp = $this->get(route('laboratorios.edit', $laboratorioObj));
            $resp->assertOk();
        });

        $this->perfilarOperacion('33_laboratorios_actualizar', function () use ($laboratorioObj) {
            $resp = $this->put(route('laboratorios.update', $laboratorioObj), [
                'nombre'      => 'Laboratorio Internacional 1 Actualizado',
                'codigo'      => 'LAB-001',
                'contacto'    => 'Contacto Modificado',
                'telefono'    => '22440001',
                'email'       => 'lab1_mod@laboratorio.hn',
                'pais_origen' => 'Alemania',
                'activo'      => 1,
            ]);
            $resp->assertRedirect(route('laboratorios.index'));
        });

        $this->perfilarOperacion('34_laboratorios_desactivar_activar', function () {
            $labSinProds = Laboratorio::create(['nombre' => 'Laboratorio Sin Productos', 'codigo' => 'LAB-SIN', 'activo' => true]);
            $resp = $this->delete(route('laboratorios.destroy', $labSinProds));
            $resp->assertRedirect(route('laboratorios.index'));
        });

        $this->perfilarOperacion('35_laboratorios_busqueda_ajax', function () {
            $resp = $this->get(route('api.laboratorios.buscar-ajax', ['q' => 'Internacional']));
            $resp->assertOk();
            $data = $resp->json();
            $this->assertNotEmpty($data);
            $this->assertLessThanOrEqual(10, count($data));
        });

        $this->perfilarOperacion('36_laboratorios_cache_invalidation_test', function () use ($laboratorioObj) {
            RequestCache::flush();
            $labService = app(\App\Services\LaboratorioService::class);
            
            // Primer llamado consulta BD y memoriza
            $res1 = $labService->buscarAjax('Internacional', 10);
            $this->assertNotEmpty($res1);

            // Segundo llamado idéntico retorna del RequestCache
            $res2 = $labService->buscarAjax('Internacional', 10);
            $this->assertEquals(count($res1), count($res2));

            // Actualización del modelo dispara evento saved y limpia prefijo laboratorios:
            $laboratorioObj->update(['nombre' => 'Internacional Hook Modificado']);

            // Tercer llamado consulta con valor nuevo actualizado
            $res3 = $labService->buscarAjax('Internacional Hook', 10);
            $this->assertNotEmpty($res3);
        });

        // Guardar reporte
        file_put_contents(
            storage_path('logs/diagnostico_catalogos_base.json'),
            json_encode($this->perfilados, JSON_PRETTY_PRINT)
        );

        $this->assertTrue(true);
    }
}
