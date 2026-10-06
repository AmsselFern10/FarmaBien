<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Proveedor;
use App\Models\Categoria;
use App\Models\Laboratorio;
use App\Models\Producto;
use App\Models\HistorialPrecio;
use App\Models\PresentacionProducto;
use App\Services\PrecioProveedorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ComparadorPreciosTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $usuarioSinPermisos;
    protected Proveedor $proveedorA;
    protected Proveedor $proveedorB;
    protected Categoria $categoria;
    protected Laboratorio $laboratorioA;
    protected Laboratorio $laboratorioB;
    protected Producto $productoParacetamol;
    protected Producto $productoBioequivalente;
    protected PresentacionProducto $presentacionCaja;
    protected PrecioProveedorService $precioService;

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
            'nombre' => 'Droguería Central S.A.',
            'activo' => true,
        ]);

        $this->proveedorB = Proveedor::factory()->create([
            'nombre' => 'Distribuidora Farmacéutica Norte',
            'activo' => true,
        ]);

        $this->categoria = Categoria::factory()->create(['nombre' => 'Analgésicos']);
        $this->laboratorioA = Laboratorio::factory()->create(['nombre' => 'Laboratorios Bagó']);
        $this->laboratorioB = Laboratorio::factory()->create(['nombre' => 'Laboratorios Genfar']);

        $this->productoParacetamol = Producto::factory()->create([
            'nombre' => 'Paracetamol 500mg Tab',
            'principio_activo' => 'Paracetamol',
            'concentracion' => '500mg',
            'forma_farmaceutica' => 'Tabletas',
            'precio_compra' => 0.10,
            'precio_venta' => 0.50,
            'categoria_id' => $this->categoria->id,
            'laboratorio_id' => $this->laboratorioA->id,
            'activo' => true,
        ]);

        $this->presentacionCaja = PresentacionProducto::factory()->create([
            'producto_id' => $this->productoParacetamol->id,
            'nombre' => 'Caja x 100',
            'unidades_por_presentacion' => 100,
            'activo' => true,
        ]);

        $this->productoBioequivalente = Producto::factory()->create([
            'nombre' => 'Acetaminofén Genfar 500mg',
            'principio_activo' => 'Paracetamol',
            'concentracion' => '500mg',
            'forma_farmaceutica' => 'Tabletas',
            'precio_compra' => 0.08,
            'precio_venta' => 0.40,
            'categoria_id' => $this->categoria->id,
            'laboratorio_id' => $this->laboratorioB->id,
            'activo' => true,
        ]);

        $this->precioService = app(PrecioProveedorService::class);
    }

    /** @test */
    public function usuario_sin_permiso_no_puede_acceder_al_comparador_ni_registrar_cotizaciones(): void
    {
        $this->actingAs($this->usuarioSinPermisos)
            ->get(route('compras.comparador-precios'))
            ->assertForbidden();

        $this->actingAs($this->usuarioSinPermisos)
            ->post(route('compras.cotizaciones.store'), [
                'producto_id' => $this->productoParacetamol->id,
                'proveedor_id' => $this->proveedorA->id,
                'precio_compra' => 12.50,
                'presentacion_id' => $this->presentacionCaja->id,
            ])
            ->assertForbidden();
    }

    /** @test */
    public function registra_cotizacion_correctamente_calculando_precio_unitario_base_en_servidor(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('compras.cotizaciones.store'), [
                'producto_id' => $this->productoParacetamol->id,
                'proveedor_id' => $this->proveedorA->id,
                'precio_compra' => 12.00,
                'presentacion_id' => $this->presentacionCaja->id,
                'observaciones' => 'Descuento pronto pago 5%',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('historial_precios', [
            'producto_id' => $this->productoParacetamol->id,
            'proveedor_id' => $this->proveedorA->id,
            'tipo' => 'cotizacion',
            'precio_compra' => 12.00,
            'unidades_por_presentacion' => 100,
            'precio_unitario_base' => 0.1200,
            'tipo_presentacion' => 'Caja x 100',
        ]);
    }

    /** @test */
    public function validacion_de_cotizacion_rechaza_precios_invalidos(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('compras.cotizaciones.store'), [
                'producto_id' => $this->productoParacetamol->id,
                'proveedor_id' => $this->proveedorA->id,
                'precio_compra' => -5.00,
            ]);

        $response->assertSessionHasErrors(['precio_compra']);
    }

    /** @test */
    public function servicio_compara_precios_entre_multiples_proveedores_ordenados_por_mejor_precio(): void
    {
        // Proveedor A cotiza Caja x 100 a $15.00 -> Base = 0.15
        HistorialPrecio::create([
            'producto_id' => $this->productoParacetamol->id,
            'proveedor_id' => $this->proveedorA->id,
            'tipo' => 'cotizacion',
            'precio_compra' => 15.00,
            'tipo_presentacion' => 'Caja x 100',
            'unidades_por_presentacion' => 100,
            'precio_unitario_base' => 0.1500,
            'fecha' => now()->subDays(2),
        ]);

        // Proveedor B cotiza Blíster x 10 a $1.20 -> Base = 0.12 (Mejor precio)
        HistorialPrecio::create([
            'producto_id' => $this->productoParacetamol->id,
            'proveedor_id' => $this->proveedorB->id,
            'tipo' => 'cotizacion',
            'precio_compra' => 1.20,
            'tipo_presentacion' => 'Blíster x 10',
            'unidades_por_presentacion' => 10,
            'precio_unitario_base' => 0.1200,
            'fecha' => now()->subDay(),
        ]);

        $resultado = $this->precioService->getComparativaProducto($this->productoParacetamol->id);

        $this->assertTrue($resultado['comparativa']->isNotEmpty());
        $this->assertEquals(0.1200, $resultado['mejor_precio']);
        $this->assertEquals(0.1500, $resultado['mayor_precio']);
        $this->assertEquals(0.1350, $resultado['precio_promedio']);
        $this->assertEquals(0.0300, $resultado['ahorro_maximo']); // 0.15 - 0.12 = 0.03

        // El primer elemento ordenado debe ser el Proveedor B (menor precio base)
        $primero = $resultado['comparativa']->sortBy('ultimo_precio_base')->first();
        $this->assertEquals($this->proveedorB->id, $primero['proveedor']->id);
        $this->assertEquals(0.1200, $primero['ultimo_precio_base']);
    }

    /** @test */
    public function distingue_origen_entre_cotizacion_y_compra_real(): void
    {
        // Compra real previa
        HistorialPrecio::create([
            'producto_id' => $this->productoParacetamol->id,
            'proveedor_id' => $this->proveedorA->id,
            'tipo' => 'compra',
            'precio_compra' => 10.00,
            'tipo_presentacion' => 'Caja x 100',
            'unidades_por_presentacion' => 100,
            'precio_unitario_base' => 0.1000,
            'fecha' => now()->subDays(10),
        ]);

        // Cotización nueva
        HistorialPrecio::create([
            'producto_id' => $this->productoParacetamol->id,
            'proveedor_id' => $this->proveedorA->id,
            'tipo' => 'cotizacion',
            'precio_compra' => 11.00,
            'tipo_presentacion' => 'Caja x 100',
            'unidades_por_presentacion' => 100,
            'precio_unitario_base' => 0.1100,
            'fecha' => now(),
        ]);

        $resultado = $this->precioService->getComparativaProducto($this->productoParacetamol->id);
        $provData = $resultado['comparativa']->firstWhere('proveedor.id', $this->proveedorA->id);

        $this->assertNotNull($provData);
        $this->assertEquals(2, $provData['total_operaciones']);
        $this->assertEquals('cotizacion', $provData['tipo_origen']);
        $this->assertEquals('cotizacion', $provData['ultimo_registro']->tipo);
        $this->assertEquals('compra', $provData['mejor_registro']->tipo);
        $this->assertEquals(0.1000, $provData['mejor_precio_base']);
    }

    /** @test */
    public function encuentra_bioequivalentes_farmaceuticos_por_principio_activo_y_concentracion(): void
    {
        $equivalentes = $this->precioService->getEquivalentesFarmaceuticos($this->productoParacetamol);

        $this->assertCount(1, $equivalentes);
        $this->assertEquals($this->productoBioequivalente->id, $equivalentes->first()->id);
        $this->assertEquals('Laboratorios Genfar', $equivalentes->first()->laboratorio->nombre);
    }

    /** @test */
    public function comando_de_integridad_del_comparador_ejecuta_y_valida_invariantes(): void
    {
        $this->artisan('farma:verificar-comparador-precios')
            ->expectsOutputToContain('AUDITORÍA EXITOSA — 0 INCONSISTENCIAS DETECTADAS')
            ->assertExitCode(0);
    }
}
