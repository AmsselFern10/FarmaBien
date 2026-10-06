<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Proveedor;
use App\Models\Categoria;
use App\Models\Laboratorio;
use App\Models\Producto;
use App\Models\Lote;
use App\Models\HistorialPrecio;
use App\Models\OrdenCompra;
use App\Models\DetalleOrdenCompra;
use App\Services\ReordenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReordenInteligenteTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $usuarioSinPermisos;
    protected Proveedor $proveedorA;
    protected Proveedor $proveedorB;
    protected Categoria $categoria;
    protected Laboratorio $laboratorio;
    protected Producto $productoAgotado;
    protected Producto $productoBajoStock;
    protected Producto $productoConTransito;
    protected Producto $productoSaludable;
    protected ReordenService $reordenService;

    protected function setUp(): void
    {
        parent::setUp();

        $permisos = [
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

        $this->proveedorA = Proveedor::factory()->create([
            'nombre' => 'Distribuidora Farma Centro',
            'activo' => true,
        ]);

        $this->proveedorB = Proveedor::factory()->create([
            'nombre' => 'Droguería Médica Universal',
            'activo' => true,
        ]);

        $this->categoria = Categoria::factory()->create(['nombre' => 'Antibióticos']);
        $this->laboratorio = Laboratorio::factory()->create(['nombre' => 'Laboratorios Ramos']);

        // 1. Producto Agotado (Stock = 0, Min = 20)
        $this->productoAgotado = Producto::factory()->create([
            'nombre' => 'Amoxicilina 500mg Caps',
            'stock_minimo' => 20,
            'precio_compra' => 0.50,
            'categoria_id' => $this->categoria->id,
            'laboratorio_id' => $this->laboratorio->id,
            'activo' => true,
        ]);

        // 2. Producto Bajo Stock (Stock = 5, Min = 20 -> 25% <= 50% -> Urgencia Alta)
        $this->productoBajoStock = Producto::factory()->create([
            'nombre' => 'Azitromicina 500mg Tab',
            'stock_minimo' => 20,
            'precio_compra' => 1.20,
            'categoria_id' => $this->categoria->id,
            'laboratorio_id' => $this->laboratorio->id,
            'activo' => true,
        ]);
        Lote::factory()->create([
            'producto_id' => $this->productoBajoStock->id,
            'proveedor_id' => $this->proveedorA->id,
            'stock_actual' => 5,
            'fecha_vencimiento' => now()->addMonths(12)->toDateString(),
            'activo' => true,
        ]);

        // 3. Producto con Orden en Tránsito (Stock = 2, Min = 20, OC Abierta = 30)
        $this->productoConTransito = Producto::factory()->create([
            'nombre' => 'Ciprofloxacino 500mg',
            'stock_minimo' => 20,
            'precio_compra' => 0.80,
            'categoria_id' => $this->categoria->id,
            'laboratorio_id' => $this->laboratorio->id,
            'activo' => true,
        ]);
        Lote::factory()->create([
            'producto_id' => $this->productoConTransito->id,
            'proveedor_id' => $this->proveedorB->id,
            'stock_actual' => 2,
            'fecha_vencimiento' => now()->addMonths(10)->toDateString(),
            'activo' => true,
        ]);
        $orden = OrdenCompra::create([
            'numero_orden' => 'OC-TEST-001',
            'proveedor_id' => $this->proveedorB->id,
            'user_id' => $this->admin->id,
            'fecha_emision' => now()->toDateString(),
            'subtotal' => 24.00,
            'total' => 24.00,
            'estado' => 'enviada',
        ]);
        DetalleOrdenCompra::create([
            'orden_compra_id' => $orden->id,
            'producto_id' => $this->productoConTransito->id,
            'cantidad_solicitada' => 30,
            'cantidad_recibida' => 0,
            'precio_unitario_estimado' => 0.80,
            'subtotal' => 24.00,
        ]);

        // 4. Producto Saludable (Stock = 50, Min = 20)
        $this->productoSaludable = Producto::factory()->create([
            'nombre' => 'Ceftriaxona 1g Inyectable',
            'stock_minimo' => 20,
            'precio_compra' => 2.50,
            'categoria_id' => $this->categoria->id,
            'laboratorio_id' => $this->laboratorio->id,
            'activo' => true,
        ]);
        Lote::factory()->create([
            'producto_id' => $this->productoSaludable->id,
            'proveedor_id' => $this->proveedorA->id,
            'stock_actual' => 50,
            'fecha_vencimiento' => now()->addMonths(18)->toDateString(),
            'activo' => true,
        ]);

        $this->reordenService = app(ReordenService::class);
    }

    /** @test */
    public function usuario_sin_permiso_no_puede_acceder_a_sugerencias_reorden(): void
    {
        $this->actingAs($this->usuarioSinPermisos)
            ->get(route('compras.sugerencias-reorden'))
            ->assertForbidden();
    }

    /** @test */
    public function servicio_calcula_sugerencias_excluyendo_productos_con_stock_saludable(): void
    {
        $resultado = $this->reordenService->calcularSugerencias();
        $sugerencias = $resultado['sugerencias'];

        $idsEnSugerencias = $sugerencias->pluck('producto.id')->all();

        $this->assertContains($this->productoAgotado->id, $idsEnSugerencias);
        $this->assertContains($this->productoBajoStock->id, $idsEnSugerencias);
        $this->assertContains($this->productoConTransito->id, $idsEnSugerencias);
        $this->assertNotContains($this->productoSaludable->id, $idsEnSugerencias);
    }

    /** @test */
    public function clasifica_urgencias_adecuadamente_considerando_stock_actual_y_transito(): void
    {
        $resultado = $this->reordenService->calcularSugerencias();
        $sugerencias = $resultado['sugerencias'];

        $sugAgotado = $sugerencias->firstWhere('producto.id', $this->productoAgotado->id);
        $sugBajo = $sugerencias->firstWhere('producto.id', $this->productoBajoStock->id);
        $sugTransito = $sugerencias->firstWhere('producto.id', $this->productoConTransito->id);

        $this->assertEquals('critica', $sugAgotado['urgencia']);
        $this->assertEquals(0, $sugAgotado['stock_actual']);
        $this->assertEquals(40, $sugAgotado['cantidad_sugerida']); // (20 * 2) - 0 = 40

        $this->assertEquals('alta', $sugBajo['urgencia']);
        $this->assertEquals(5, $sugBajo['stock_actual']);
        $this->assertEquals(35, $sugBajo['cantidad_sugerida']); // (20 * 2) - 5 = 35

        $this->assertEquals('transito', $sugTransito['urgencia']);
        $this->assertEquals(30, $sugTransito['stock_en_transito']);
        $this->assertEquals(32, $sugTransito['stock_proyectado']); // 2 disp + 30 transito
    }

    /** @test */
    public function asocia_el_mejor_proveedor_segun_el_historial_de_cotizaciones(): void
    {
        // Proveedor A cotiza Amoxicilina a 0.50
        HistorialPrecio::create([
            'producto_id' => $this->productoAgotado->id,
            'proveedor_id' => $this->proveedorA->id,
            'tipo' => 'cotizacion',
            'precio_compra' => 0.50,
            'precio_unitario_base' => 0.5000,
            'fecha' => now()->subDay(),
        ]);

        // Proveedor B cotiza Amoxicilina a 0.40 (Mejor precio)
        HistorialPrecio::create([
            'producto_id' => $this->productoAgotado->id,
            'proveedor_id' => $this->proveedorB->id,
            'tipo' => 'cotizacion',
            'precio_compra' => 0.40,
            'precio_unitario_base' => 0.4000,
            'fecha' => now(),
        ]);

        $resultado = $this->reordenService->calcularSugerencias();
        $sugAgotado = $resultado['sugerencias']->firstWhere('producto.id', $this->productoAgotado->id);

        $this->assertEquals($this->proveedorB->id, $sugAgotado['proveedor_recomendado']->id);
        $this->assertEquals(0.4000, $sugAgotado['mejor_precio_base']);
    }

    /** @test */
    public function filtro_solo_agotados_filtra_exclusivamente_productos_con_stock_cero(): void
    {
        $resultado = $this->reordenService->calcularSugerencias(['solo_agotados' => true]);
        $sugerencias = $resultado['sugerencias'];

        $this->assertCount(1, $sugerencias);
        $this->assertEquals($this->productoAgotado->id, $sugerencias->first()['producto']->id);
    }

    /** @test */
    public function vista_de_sugerencias_reorden_renderiza_correctamente_para_administrador(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('compras.sugerencias-reorden'));

        $response->assertOk();
        $response->assertViewIs('compras.sugerencias-reorden');
        $response->assertSee('Alertas y Sugerencias de Reorden');
        $response->assertSee($this->productoAgotado->nombre);
    }

    /** @test */
    public function comando_de_integridad_de_reorden_valida_todos_los_invariantes(): void
    {
        $this->artisan('farma:verificar-reorden')
            ->expectsOutputToContain('AUDITORÍA EXITOSA — 0 INCONSISTENCIAS DETECTADAS')
            ->assertExitCode(0);
    }
}
