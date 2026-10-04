<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Producto;
use App\Models\Lote;
use App\Models\Proveedor;
use App\Models\Categoria;
use App\Models\Laboratorio;
use App\Models\MovimientoInventario;
use App\Services\InventarioService;
use App\Services\NotificacionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AlertasMonitorStockTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $usuarioSinPermisos;
    protected Categoria $categoria;
    protected Laboratorio $laboratorio;
    protected Proveedor $proveedor;
    protected InventarioService $inventarioService;
    protected NotificacionService $notificacionService;

    protected function setUp(): void
    {
        parent::setUp();

        $permisos = [
            'ver movimientos inventario',
            'ajustar inventario',
            'editar lotes',
            'ver productos',
            'ver compras',
            'registrar compras',
        ];

        foreach ($permisos as $permiso) {
            Permission::findOrCreate($permiso, 'web');
        }

        $roleAdmin = Role::findOrCreate('Administrador', 'web');
        $roleAdmin->syncPermissions($permisos);

        $this->admin = User::factory()->create();
        $this->admin->assignRole($roleAdmin);

        $this->usuarioSinPermisos = User::factory()->create();

        $this->categoria = Categoria::factory()->create(['nombre' => 'Analgésicos']);
        $this->laboratorio = Laboratorio::factory()->create(['nombre' => 'Bayer']);
        $this->proveedor = Proveedor::factory()->create(['nombre' => 'Droguería Médica']);

        $this->inventarioService = app(InventarioService::class);
        $this->notificacionService = app(NotificacionService::class);
    }

    /**
     * Test: Acceso a Alertas de Inventario requiere permiso
     */
    public function test_alertas_inventario_pantalla_requiere_permiso(): void
    {
        $responseGuest = $this->get(route('inventario.alertas'));
        $responseGuest->assertRedirect(route('login'));

        $responseSinPermiso = $this->actingAs($this->usuarioSinPermisos)->get(route('inventario.alertas'));
        $responseSinPermiso->assertStatus(403);

        $responseAdmin = $this->actingAs($this->admin)->get(route('inventario.alertas'));
        $responseAdmin->assertStatus(200);
        $responseAdmin->assertViewIs('inventario.alertas');
    }

    /**
     * Flujo 1: Notificación y visualización de lotes vencidos con stock activo
     */
    public function test_flujo_1_lotes_vencidos_con_stock_aparecen_en_alertas_y_campana(): void
    {
        $producto = Producto::factory()->create([
            'categoria_id'   => $this->categoria->id,
            'laboratorio_id' => $this->laboratorio->id,
            'nombre'         => 'Amoxicilina 500mg',
            'stock_minimo'   => 10,
            'activo'         => true,
        ]);

        // Lote 1: Vencido con stock disponible (debe aparecer)
        $loteVencidoConStock = Lote::factory()->create([
            'producto_id'       => $producto->id,
            'proveedor_id'      => $this->proveedor->id,
            'numero_lote'       => 'LOT-VENC-01',
            'fecha_vencimiento' => now()->subDays(10)->toDateString(),
            'stock_inicial'     => 50,
            'stock_actual'      => 20,
            'precio_compra'     => 15.00,
            'activo'            => true,
        ]);

        // Lote 2: Vencido con stock 0 (no debe aparecer en alertas activas)
        $loteVencidoSinStock = Lote::factory()->create([
            'producto_id'       => $producto->id,
            'proveedor_id'      => $this->proveedor->id,
            'numero_lote'       => 'LOT-VENC-02',
            'fecha_vencimiento' => now()->subDays(20)->toDateString(),
            'stock_inicial'     => 30,
            'stock_actual'      => 0,
            'precio_compra'     => 15.00,
            'activo'            => true,
        ]);

        // Lote 3: Vigente (no debe aparecer como vencido)
        $loteVigente = Lote::factory()->create([
            'producto_id'       => $producto->id,
            'proveedor_id'      => $this->proveedor->id,
            'numero_lote'       => 'LOT-VIG-01',
            'fecha_vencimiento' => now()->addMonths(6)->toDateString(),
            'stock_inicial'     => 100,
            'stock_actual'      => 100,
            'precio_compra'     => 15.00,
            'activo'            => true,
        ]);

        $vencidos = $this->inventarioService->lotesVencidos();
        $this->assertCount(1, $vencidos);
        $this->assertEquals('LOT-VENC-01', $vencidos->first()->numero_lote);

        // Visualización en Centro de Alertas
        $response = $this->actingAs($this->admin)->get(route('inventario.alertas'));
        $response->assertStatus(200);
        $response->assertSee('LOT-VENC-01');
        $response->assertSee('Amoxicilina 500mg');
        $response->assertDontSee('LOT-VENC-02');

        // Endpoint de Notificaciones (Campana)
        Cache::forget('farma_notificaciones_resumen');
        $responseJson = $this->actingAs($this->admin)->getJson('/api/notificaciones/resumen');
        $responseJson->assertStatus(200);
        $responseJson->assertJsonPath('vencimientos.0.id', 'lote_' . $loteVencidoConStock->id);
        $responseJson->assertJsonPath('vencimientos.0.tipo', 'lote_vencido');
    }

    /**
     * Flujo 2: Lotes próximos a vencer (<= 60 días) y ordenación FEFO
     */
    public function test_flujo_2_lotes_proximos_a_vencer_60_dias_ordenados_fefo(): void
    {
        $producto = Producto::factory()->create([
            'categoria_id'   => $this->categoria->id,
            'laboratorio_id' => $this->laboratorio->id,
            'nombre'         => 'Ibuprofeno 400mg',
            'stock_minimo'   => 10,
            'activo'         => true,
        ]);

        // Lote vence en 45 días
        $lote45d = Lote::factory()->create([
            'producto_id'       => $producto->id,
            'proveedor_id'      => $this->proveedor->id,
            'numero_lote'       => 'LOT-45D',
            'fecha_vencimiento' => now()->addDays(45)->toDateString(),
            'stock_inicial'     => 50,
            'stock_actual'      => 30,
            'precio_compra'     => 10.00,
            'activo'            => true,
        ]);

        // Lote vence en 15 días (debe ir primero por FEFO)
        $lote15d = Lote::factory()->create([
            'producto_id'       => $producto->id,
            'proveedor_id'      => $this->proveedor->id,
            'numero_lote'       => 'LOT-15D',
            'fecha_vencimiento' => now()->addDays(15)->toDateString(),
            'stock_inicial'     => 50,
            'stock_actual'      => 20,
            'precio_compra'     => 10.00,
            'activo'            => true,
        ]);

        // Lote vence en 90 días (fuera de la ventana de 60 días)
        $lote90d = Lote::factory()->create([
            'producto_id'       => $producto->id,
            'proveedor_id'      => $this->proveedor->id,
            'numero_lote'       => 'LOT-90D',
            'fecha_vencimiento' => now()->addDays(90)->toDateString(),
            'stock_inicial'     => 50,
            'stock_actual'      => 50,
            'precio_compra'     => 10.00,
            'activo'            => true,
        ]);

        $proximos = $this->inventarioService->lotesProximosVencer(60);

        $this->assertCount(2, $proximos);
        $this->assertEquals('LOT-15D', $proximos->first()->numero_lote, 'El lote que vence en 15 días debe ser el primero (FEFO)');
        $this->assertEquals('LOT-45D', $proximos->last()->numero_lote);

        // Verificar accessor dias_restantes
        $this->assertEquals(15, $proximos->first()->dias_restantes);
        $this->assertEquals(45, $proximos->last()->dias_restantes);

        // Verificar vista de Alertas
        $response = $this->actingAs($this->admin)->get(route('inventario.alertas'));
        $response->assertStatus(200);
        $response->assertSee('LOT-15D');
        $response->assertSee('LOT-45D');
        $response->assertDontSee('LOT-90D');
    }

    /**
     * Flujo 3: Detección de productos bajo stock mínimo considerando solo lotes vigentes
     */
    public function test_flujo_3_deteccion_productos_bajo_stock_minimo_excluyendo_lotes_vencidos(): void
    {
        // Producto 1: Stock bajo legítimo (Stock disponible = 5, Mínimo = 10)
        $prodBajo = Producto::factory()->create([
            'categoria_id'   => $this->categoria->id,
            'laboratorio_id' => $this->laboratorio->id,
            'nombre'         => 'Paracetamol Gotas',
            'stock_minimo'   => 10,
            'activo'         => true,
        ]);
        Lote::factory()->create([
            'producto_id'       => $prodBajo->id,
            'proveedor_id'      => $this->proveedor->id,
            'numero_lote'       => 'LOT-PB-01',
            'fecha_vencimiento' => now()->addMonths(5)->toDateString(),
            'stock_actual'      => 5,
            'activo'            => true,
        ]);

        // Producto 2: Stock suficiente (Stock disponible = 20, Mínimo = 10)
        $prodOk = Producto::factory()->create([
            'categoria_id'   => $this->categoria->id,
            'laboratorio_id' => $this->laboratorio->id,
            'nombre'         => 'Loratadina Jarabe',
            'stock_minimo'   => 10,
            'activo'         => true,
        ]);
        Lote::factory()->create([
            'producto_id'       => $prodOk->id,
            'proveedor_id'      => $this->proveedor->id,
            'numero_lote'       => 'LOT-POK-01',
            'fecha_vencimiento' => now()->addMonths(8)->toDateString(),
            'stock_actual'      => 20,
            'activo'            => true,
        ]);

        // Producto 3: Stock aparente en BD pero vencido (Disponible real = 0, Mínimo = 10) -> Debe reportar AGOTADO/CRÍTICO
        $prodVencidoTotal = Producto::factory()->create([
            'categoria_id'   => $this->categoria->id,
            'laboratorio_id' => $this->laboratorio->id,
            'nombre'         => 'Omeprazol Polvo',
            'stock_minimo'   => 10,
            'activo'         => true,
        ]);
        Lote::factory()->create([
            'producto_id'       => $prodVencidoTotal->id,
            'proveedor_id'      => $this->proveedor->id,
            'numero_lote'       => 'LOT-VENC-TOT',
            'fecha_vencimiento' => now()->subDays(5)->toDateString(),
            'stock_actual'      => 50, // 50 unidades vencidas no cuentan como disponibles
            'activo'            => true,
        ]);

        $productosBajoStock = $this->inventarioService->productosConStockBajo();
        $nombresBajoStock = $productosBajoStock->pluck('nombre')->toArray();

        $this->assertContains('Paracetamol Gotas', $nombresBajoStock);
        $this->assertContains('Omeprazol Polvo', $nombresBajoStock);
        $this->assertNotContains('Loratadina Jarabe', $nombresBajoStock);

        // Verificar en NotificacionService JSON
        Cache::forget('farma_notificaciones_resumen');
        $resumen = $this->notificacionService->getResumenNotificaciones();
        $stockAlerts = collect($resumen['stock']);

        $alertaOmeprazol = $stockAlerts->firstWhere('id', 'stock_' . $prodVencidoTotal->id);
        $this->assertNotNull($alertaOmeprazol);
        $this->assertEquals('agotado', $alertaOmeprazol['tipo'], 'Producto con solo stock vencido debe clasificarse como agotado');
    }

    /**
     * Flujo 4: Valorización PEPS, agregación SQL resiliente e invalidación de caché
     */
    public function test_flujo_4_valorizacion_peps_agregacion_sql_resiliente_e_invalidacion_cache(): void
    {
        $productoA = Producto::factory()->create([
            'categoria_id'   => $this->categoria->id,
            'laboratorio_id' => $this->laboratorio->id,
            'nombre'         => 'Ketorolaco 10mg',
            'stock_minimo'   => 5,
            'activo'         => true,
        ]);

        $loteA1 = Lote::factory()->create([
            'producto_id'       => $productoA->id,
            'proveedor_id'      => $this->proveedor->id,
            'numero_lote'       => 'LOT-KETO-1',
            'fecha_vencimiento' => now()->addMonths(12)->toDateString(),
            'stock_actual'      => 100,
            'precio_compra'     => 5.50,
            'activo'            => true,
        ]);

        $loteA2 = Lote::factory()->create([
            'producto_id'       => $productoA->id,
            'proveedor_id'      => $this->proveedor->id,
            'numero_lote'       => 'LOT-KETO-2',
            'fecha_vencimiento' => now()->addMonths(18)->toDateString(),
            'stock_actual'      => 50,
            'precio_compra'     => 6.00,
            'activo'            => true,
        ]);

        // Cálculo esperado: (100 * 5.50) + (50 * 6.00) = 550 + 300 = 850.00
        $valorizacion = $this->inventarioService->valorizacionInventario(true);

        $this->assertEquals(850.00, $valorizacion['valor_total']);
        $this->assertEquals(150, $valorizacion['total_unidades']);
        $this->assertEquals(2, $valorizacion['total_lotes_activos']);
        $this->assertEquals(1, $valorizacion['total_productos']);
        $this->assertNotEmpty($valorizacion['detalles']);

        // Vista Monitor Index
        $responseIndex = $this->actingAs($this->admin)->get(route('inventario.index'));
        $responseIndex->assertStatus(200);
        $responseIndex->assertSee('850.00');

        // Mutación de stock: Ajuste manual
        $this->inventarioService->ajustarInventario([
            'lote_id'     => $loteA1->id,
            'stock_nuevo' => 80, // Reducción de 20 uds
            'motivo'      => 'Ajuste de conteo por auditoría interna',
            'subtipo'     => 'ajuste_manual',
        ]);

        // Nueva valorización esperada: (80 * 5.50) + (50 * 6.00) = 440 + 300 = 740.00
        $nuevaValorizacion = $this->inventarioService->valorizacionInventario(false);
        $this->assertEquals(740.00, $nuevaValorizacion['valor_total']);
        $this->assertEquals(130, $nuevaValorizacion['total_unidades']);
    }

    /**
     * Test: Distintivo de producto controlado en el Centro de Alertas
     */
    public function test_medicamento_controlado_muestra_badge_en_alertas(): void
    {
        $prodControlado = Producto::factory()->create([
            'categoria_id'    => $this->categoria->id,
            'laboratorio_id'  => $this->laboratorio->id,
            'nombre'          => 'Alprazolam 0.5mg',
            'tipo_control'    => Producto::TIPO_CONTROLADO,
            'requiere_receta' => true,
            'stock_minimo'    => 20,
            'activo'          => true,
        ]);

        Lote::factory()->create([
            'producto_id'       => $prodControlado->id,
            'proveedor_id'      => $this->proveedor->id,
            'numero_lote'       => 'LOT-ALPRA-01',
            'fecha_vencimiento' => now()->addDays(20)->toDateString(),
            'stock_actual'      => 5,
            'activo'            => true,
        ]);

        $response = $this->actingAs($this->admin)->get(route('inventario.alertas'));
        $response->assertStatus(200);
        $response->assertSee('Alprazolam 0.5mg');
        $response->assertSee('Controlado');
    }

    /**
     * Test: Baja masiva de lotes vencidos desde el Centro de Alertas
     */
    public function test_baja_masiva_de_vencidos_desde_alertas(): void
    {
        $producto = Producto::factory()->create([
            'categoria_id'   => $this->categoria->id,
            'laboratorio_id' => $this->laboratorio->id,
            'nombre'         => 'Ceftriaxona 1g',
            'stock_minimo'   => 10,
            'activo'         => true,
        ]);

        $loteVenc1 = Lote::factory()->create([
            'producto_id'       => $producto->id,
            'numero_lote'       => 'LOT-CEF-V1',
            'fecha_vencimiento' => now()->subDays(15)->toDateString(),
            'stock_actual'      => 10,
            'precio_compra'     => 45.00,
            'activo'            => true,
        ]);

        $loteVenc2 = Lote::factory()->create([
            'producto_id'       => $producto->id,
            'numero_lote'       => 'LOT-CEF-V2',
            'fecha_vencimiento' => now()->subDays(30)->toDateString(),
            'stock_actual'      => 5,
            'precio_compra'     => 45.00,
            'activo'            => true,
        ]);

        $response = $this->actingAs($this->admin)->post(route('inventario.baja-vencidos'));
        $response->assertRedirect(route('inventario.alertas'));
        $response->assertSessionHas('success');

        $this->assertEquals(0, $loteVenc1->fresh()->stock_actual);
        $this->assertFalse($loteVenc1->fresh()->activo);
        $this->assertEquals(0, $loteVenc2->fresh()->stock_actual);
        $this->assertFalse($loteVenc2->fresh()->activo);

        // Movimientos de merma en Kardex
        $movs = MovimientoInventario::where('subtipo', 'vencimiento_automatico')->get();
        $this->assertCount(2, $movs);
    }
}
