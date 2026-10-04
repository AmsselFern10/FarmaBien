<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Producto;
use App\Models\Cliente;
use App\Models\Proveedor;
use App\Models\Laboratorio;
use App\Models\Categoria;
use App\Models\Lote;
use App\Services\FarmaIaService;
use App\Services\ProductoService;
use App\Services\ProveedorService;
use App\Services\LaboratorioService;
use App\Services\CategoriaService;
use App\Services\InventarioService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class IntegracionesIaAlertasTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $usuarioSinPermisos;
    protected Categoria $categoria;
    protected Laboratorio $laboratorio;
    protected Proveedor $proveedor;

    protected function setUp(): void
    {
        parent::setUp();

        $permisos = [
            'ver productos', 'crear productos', 'editar productos',
            'ver inventario', 'ajustar inventario',
            'ver clientes', 'ver proveedores', 'ver laboratorios', 'ver categorias',
        ];

        foreach ($permisos as $permiso) {
            Permission::findOrCreate($permiso, 'web');
        }

        $roleAdmin = Role::findOrCreate('Administrador', 'web');
        $roleAdmin->syncPermissions($permisos);

        $this->admin = User::factory()->create();
        $this->admin->assignRole($roleAdmin);

        $this->usuarioSinPermisos = User::factory()->create();

        $this->categoria = Categoria::factory()->create(['nombre' => 'Analgesicos']);
        $this->laboratorio = Laboratorio::factory()->create(['nombre' => 'Bayer']);
        $this->proveedor = Proveedor::factory()->create(['nombre' => 'Droguería Americana']);
    }

    /* ══════════════════════════════════════════════════════════════
       1. FLUJO 1: BÚSQUEDAS AJAX DE CATÁLOGOS CON CONTRATO UNIFICADO
       ══════════════════════════════════════════════════════════════ */

    public function test_flujo_1_busqueda_ajax_medicamentos_contrato_unificado(): void
    {
        $p1 = Producto::factory()->create([
            'nombre'           => 'Ibuprofeno 400mg',
            'principio_activo' => 'Ibuprofeno',
            'codigo_barra'     => '7701234567890',
            'precio_venta'     => 15.50,
            'categoria_id'     => $this->categoria->id,
            'laboratorio_id'   => $this->laboratorio->id,
            'activo'           => true,
        ]);

        $productoService = app(ProductoService::class);
        $resultados = $productoService->buscarAjax('Ibuprofeno', 10);

        $this->assertNotEmpty($resultados);
        $item = $resultados[0];

        $this->assertEquals($p1->id, $item['id']);
        $this->assertStringContainsString('Ibuprofeno', $item['nombre']);
        $this->assertEquals('7701234567890', $item['codigo']);
        $this->assertEquals(15.50, $item['precio_venta']);

        // Endpoint en InventarioController
        $response = $this->actingAs($this->admin)->getJson(route('inventario.buscar-medicamentos-ajax', ['q' => 'Ibuprofeno']));
        $response->assertStatus(200);
        $response->assertJsonFragment(['id' => $p1->id, 'codigo' => '7701234567890']);
    }

    public function test_busqueda_ajax_proveedores_laboratorios_y_categorias(): void
    {
        $provService = app(ProveedorService::class);
        $labService = app(LaboratorioService::class);
        $catService = app(CategoriaService::class);

        // Proveedores
        $resProv = $provService->buscarAjax('Americana', 5);
        $this->assertNotEmpty($resProv);
        $this->assertEquals($this->proveedor->id, $resProv[0]['id']);

        // Laboratorios
        $resLab = $labService->buscarAjax('Bayer', 5);
        $this->assertNotEmpty($resLab);
        $this->assertEquals($this->laboratorio->id, $resLab[0]['id']);

        // Categorías
        $resCat = $catService->buscarAjax('Analgesicos', 5);
        $this->assertNotEmpty($resCat);
        $this->assertEquals($this->categoria->id, $resCat[0]['id']);
    }

    /* ══════════════════════════════════════════════════════════════
       2. FLUJO 2: ALERTAS DE INVENTARIO Y STOCK CRÍTICO
       ══════════════════════════════════════════════════════════════ */

    public function test_flujo_2_alertas_stock_bajo_y_vencimientos(): void
    {
        $prodCritico = Producto::factory()->create([
            'nombre'       => 'Amoxicilina 500mg',
            'stock_minimo' => 20,
            'activo'       => true,
        ]);

        // Lote con solo 5 unidades (menor a stock mínimo 20)
        Lote::factory()->create([
            'producto_id'       => $prodCritico->id,
            'stock_actual'      => 5,
            'fecha_vencimiento' => now()->addMonths(6),
            'activo'            => true,
        ]);

        $prodNormal = Producto::factory()->create([
            'nombre'       => 'Paracetamol 500mg',
            'stock_minimo' => 10,
            'activo'       => true,
        ]);

        // Lote con stock suficiente (50 unidades)
        Lote::factory()->create([
            'producto_id'       => $prodNormal->id,
            'stock_actual'      => 50,
            'fecha_vencimiento' => now()->addMonths(6),
            'activo'            => true,
        ]);

        // Lote próximo a vencer (15 días)
        $lotePorVencer = Lote::factory()->porVencer()->create([
            'producto_id'  => $prodNormal->id,
            'stock_actual' => 10,
        ]);

        $inventarioService = app(InventarioService::class);
        $bajos = $inventarioService->productosConStockBajo();

        $this->assertTrue($bajos->pluck('id')->contains($prodCritico->id));
        $this->assertFalse($bajos->pluck('id')->contains($prodNormal->id));

        $lotesPorVencerList = $inventarioService->lotesProximosVencer(30);
        $this->assertTrue($lotesPorVencerList->pluck('id')->contains($lotePorVencer->id));
    }

    /* ══════════════════════════════════════════════════════════════
       3. FLUJO 3: BÚSQUEDA GLOBAL (Ctrl+K)
       ══════════════════════════════════════════════════════════════ */

    public function test_flujo_3_busqueda_global_por_nombre_principio_y_codigo(): void
    {
        $prod = Producto::factory()->create([
            'nombre'           => 'Omeprazol 20mg Cápsulas',
            'principio_activo' => 'Omeprazol',
            'codigo_barra'     => '7501000123456',
        ]);

        $cliente = Cliente::factory()->create([
            'nombre'    => 'Carlos Mendoza',
            'documento' => '001-010190-0001A',
        ]);

        // 1. Búsqueda por nombre de producto
        $res1 = $this->actingAs($this->admin)->getJson(route('busqueda.global', ['q' => 'Omeprazol']));
        $res1->assertStatus(200);
        $this->assertCount(1, $res1->json('productos'));
        $this->assertEquals($prod->id, $res1->json('productos.0.id'));

        // 2. Búsqueda por código de barras
        $res2 = $this->actingAs($this->admin)->getJson(route('busqueda.global', ['q' => '7501000123456']));
        $res2->assertStatus(200);
        $this->assertCount(1, $res2->json('productos'));

        // 3. Búsqueda por cliente
        $res3 = $this->actingAs($this->admin)->getJson(route('busqueda.global', ['q' => 'Mendoza']));
        $res3->assertStatus(200);
        $this->assertCount(1, $res3->json('clientes'));
        $this->assertEquals($cliente->id, $res3->json('clientes.0.id'));
    }

    /* ══════════════════════════════════════════════════════════════
       4. FLUJO 4: RESILIENCIA Y MOTOR FARMACOLÓGICO DE IA
       ══════════════════════════════════════════════════════════════ */

    public function test_flujo_4_resiliencia_de_ia_y_fallback_a_motor_farmacologico(): void
    {
        $prod = Producto::factory()->create([
            'nombre'             => 'Acetaminofen 500mg',
            'principio_activo'   => 'Paracetamol',
            'concentracion'      => '500mg',
            'forma_farmaceutica' => 'Tabletas',
        ]);

        $iaService = app(FarmaIaService::class);

        // Búsqueda semántica por síntoma ("dolor de cabeza")
        $sugeridos = $iaService->buscarSemantica('dolor de cabeza');
        $this->assertNotEmpty($sugeridos);
        $this->assertTrue(collect($sugeridos)->pluck('id')->contains($prod->id));

        // Generar ficha clínica asistida por IA (Fallback transparente al motor local si no hay API externa configurada)
        $ficha = $iaService->generarFichaProducto($prod);

        $this->assertTrue($ficha['ok']);
        $this->assertEquals($prod->id, $ficha['producto_id']);
        $this->assertNotEmpty($ficha['posologia']);
        $this->assertNotEmpty($ficha['contraindicaciones']);
        $this->assertNotEmpty($ficha['advertencias']);
    }

    public function test_endpoint_ia_buscar_y_ficha_en_producto_controller(): void
    {
        $prod = Producto::factory()->create([
            'nombre'           => 'Dramamine 50mg',
            'principio_activo' => 'Dimenhidrinato',
        ]);

        // POST /productos/ia/buscar
        $resBuscar = $this->actingAs($this->admin)->postJson(route('productos.ia.buscar'), [
            'q' => 'mareo',
        ]);
        $resBuscar->assertStatus(200);
        $resBuscar->assertJson(['ok' => true]);

        // POST /productos/{producto}/ia-ficha
        $resFicha = $this->actingAs($this->admin)->postJson(route('productos.ia.ficha', $prod));
        $resFicha->assertStatus(200);
        $resFicha->assertJson(['ok' => true, 'producto_id' => $prod->id]);
    }
}
