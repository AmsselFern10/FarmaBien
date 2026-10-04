<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Producto;
use App\Models\PresentacionProducto;
use App\Models\Categoria;
use App\Models\Laboratorio;
use App\Models\Proveedor;
use App\Models\PrecioVenta;
use App\Models\Promocion;
use App\Models\HistorialPrecio;
use App\Services\PrecioVentaService;
use App\Services\PromocionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PreciosPromocionesTest extends TestCase
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
            'ver precios', 'editar precios',
            'ver promociones', 'crear promociones', 'editar promociones', 'desactivar promociones',
            'ver compras', 'registrar compras',
            'ver productos', 'editar productos',
        ];

        foreach ($permisos as $permiso) {
            Permission::findOrCreate($permiso, 'web');
        }

        $roleAdmin = Role::findOrCreate('Administrador', 'web');
        $roleAdmin->syncPermissions($permisos);

        $this->admin = User::factory()->create();
        $this->admin->assignRole($roleAdmin);

        $this->usuarioSinPermisos = User::factory()->create();

        $this->categoria = Categoria::factory()->create(['nombre' => 'Cardiovascular']);
        $this->laboratorio = Laboratorio::factory()->create(['nombre' => 'Novartis']);
        $this->proveedor = Proveedor::factory()->create(['nombre' => 'Droguería Central']);
    }

    /* ══════════════════════════════════════════════════════════════
       1. PERMISOS Y ACCESO
       ══════════════════════════════════════════════════════════════ */

    public function test_usuario_sin_permisos_no_puede_acceder_a_promociones(): void
    {
        $response = $this->actingAs($this->usuarioSinPermisos)->get(route('promociones.index'));
        $response->assertStatus(403);
    }

    public function test_admin_puede_listar_precios_y_promociones(): void
    {
        Producto::factory()->create([
            'nombre'       => 'Losartán 50mg',
            'precio_venta' => 25.00,
        ]);

        Promocion::factory()->create([
            'nombre' => 'Super Oferta Verano',
        ]);

        $resPrecios = $this->actingAs($this->admin)->get(route('precios.index'));
        $resPrecios->assertStatus(200);
        $resPrecios->assertSee('Losartán 50mg');

        $resPromos = $this->actingAs($this->admin)->get(route('promociones.index'));
        $resPromos->assertStatus(200);
        $resPromos->assertSee('Super Oferta Verano');
    }

    /* ══════════════════════════════════════════════════════════════
       2. FLUJO 1: ACTUALIZACIÓN DE PRECIOS CON CIERRE DE VIGENCIAS
       ══════════════════════════════════════════════════════════════ */

    public function test_flujo_1_actualizar_precio_base_y_presentaciones_cierra_vigencias(): void
    {
        $producto = Producto::factory()->create([
            'nombre'        => 'Enalapril 20mg',
            'precio_compra' => 10.00,
            'precio_venta'  => 18.00,
        ]);

        $presentacion = PresentacionProducto::factory()->create([
            'producto_id'  => $producto->id,
            'nombre'       => 'Caja x 30',
            'precio_venta' => 50.00,
        ]);

        // Registrar precio base vigente inicial
        PrecioVenta::create([
            'producto_id'     => $producto->id,
            'presentacion_id' => null,
            'precio'          => 18.00,
            'vigente_desde'   => now()->subDays(10),
            'vigente_hasta'   => null,
            'motivo'          => 'Precio inicial',
            'user_id'         => $this->admin->id,
        ]);

        // Actualizar precio a 22.00 para base y 60.00 para presentación
        $updateData = [
            'precio_base'    => 22.00,
            'motivo'         => 'Ajuste inflacionario',
            'presentaciones' => [
                [
                    'id'           => $presentacion->id,
                    'precio_venta' => 60.00,
                ]
            ]
        ];

        $response = $this->actingAs($this->admin)->put(route('precios.update', $producto), $updateData);

        $response->assertRedirect(route('precios.show', $producto));

        // Verificar que el producto y presentación tienen los nuevos precios
        $this->assertEquals(22.00, (float)$producto->fresh()->precio_venta);
        $this->assertEquals(60.00, (float)$presentacion->fresh()->precio_venta);

        // Verificar historial de precios: el anterior debe tener vigente_hasta != null y el nuevo vigente_hasta == null
        $preciosBase = PrecioVenta::where('producto_id', $producto->id)->whereNull('presentacion_id')->get();
        $this->assertCount(2, $preciosBase);

        $precioAnterior = $preciosBase->firstWhere('precio', 18.00);
        $precioNuevo = $preciosBase->firstWhere('precio', 22.00);

        $this->assertNotNull($precioAnterior->vigente_hasta);
        $this->assertNull($precioNuevo->vigente_hasta);
    }

    public function test_actualizacion_rapida_inline_ajax(): void
    {
        $producto = Producto::factory()->create([
            'nombre'        => 'Amlodipino 5mg',
            'precio_compra' => 5.00,
            'precio_venta'  => 10.00,
        ]);

        $response = $this->actingAs($this->admin)->postJson(route('precios.inline-update', $producto), [
            'precio_venta' => 12.50,
            'motivo'       => 'Ajuste rápido de margen',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success'      => true,
            'producto_id'  => $producto->id,
            'precio_nuevo' => 12.50,
        ]);

        $this->assertEquals(12.50, (float)$producto->fresh()->precio_venta);
        $this->assertDatabaseHas('precios_venta', [
            'producto_id' => $producto->id,
            'precio'      => 12.50,
            'motivo'      => 'Ajuste rápido de margen',
        ]);
    }

    /* ══════════════════════════════════════════════════════════════
       3. FLUJO 2: ACTUALIZACIÓN MASIVA DE PRECIOS CON FILTROS
       ══════════════════════════════════════════════════════════════ */

    public function test_flujo_2_actualizacion_masiva_por_categoria_con_redondeo(): void
    {
        $catA = Categoria::factory()->create();
        $catB = Categoria::factory()->create();

        $p1 = Producto::factory()->create([
            'nombre'       => 'Prod 1',
            'categoria_id' => $catA->id,
            'precio_venta' => 10.00,
        ]);

        $p2 = Producto::factory()->create([
            'nombre'       => 'Prod 2',
            'categoria_id' => $catA->id,
            'precio_venta' => 20.00,
        ]);

        $p3 = Producto::factory()->create([
            'nombre'       => 'Prod 3',
            'categoria_id' => $catB->id,
            'precio_venta' => 30.00,
        ]);

        $service = app(PrecioVentaService::class);

        // Vista previa: aumento del 10% para categoría A
        $preview = $service->generarVistaPreviaMasiva('categoria', [$catA->id], 'porcentaje_aumento', 10.00, 'sin');
        $this->assertEquals(2, $preview['total_afectados']);

        // Aplicar ajuste masivo
        $resultado = $service->aplicarAjusteMasivo('categoria', [$catA->id], 'porcentaje_aumento', 10.00, 'sin', 'Aumento general 10%');
        $this->assertTrue($resultado['success']);
        $this->assertEquals(2, $resultado['productos_actualizados']);

        // Verificar nuevos precios
        $this->assertEquals(11.00, (float)$p1->fresh()->precio_venta);
        $this->assertEquals(22.00, (float)$p2->fresh()->precio_venta);
        $this->assertEquals(30.00, (float)$p3->fresh()->precio_venta); // No afectada
    }

    /* ══════════════════════════════════════════════════════════════
       4. FLUJO 3: EVALUACIÓN Y CÁLCULO DE PROMOCIONES
       ══════════════════════════════════════════════════════════════ */

    public function test_flujo_3_calculo_descuentos_porcentaje_monto_fijo_2x1_y_3x2(): void
    {
        // 1. Promoción Porcentaje (20%)
        $promoPct = Promocion::factory()->create([
            'tipo'         => 'porcentaje',
            'valor'        => 20.00,
            'min_unidades' => 1,
        ]);
        $this->assertEquals(4.00, $promoPct->calcularDescuento(20.00, 1)); // 20 * 20% = 4
        $this->assertEquals(16.00, $promoPct->calcularPrecioUnitario(20.00));

        // 2. Promoción Monto Fijo ($5 de descuento)
        $promoFijo = Promocion::factory()->montoFijo(5.00)->create([
            'min_unidades' => 1,
        ]);
        $this->assertEquals(5.00, $promoFijo->calcularDescuento(20.00, 1)); // 5 desc
        $this->assertEquals(15.00, $promoFijo->calcularPrecioUnitario(20.00));

        // 3. Promoción 2x1 (llevas 2 pagas 1)
        $promo2x1 = Promocion::factory()->dosPorUno()->create([
            'min_unidades' => 1,
        ]);
        $this->assertEquals(20.00, $promo2x1->calcularDescuento(20.00, 2)); // 1 gratis = 20
        $this->assertEquals(20.00, $promo2x1->calcularDescuento(20.00, 3)); // 1 gratis de los 2 primeros
        $this->assertEquals(40.00, $promo2x1->calcularDescuento(20.00, 4)); // 2 gratis

        // 4. Promoción 3x2 (llevas 3 pagas 2)
        $promo3x2 = Promocion::factory()->tresPorDos()->create([
            'min_unidades' => 1,
        ]);
        $this->assertEquals(20.00, $promo3x2->calcularDescuento(20.00, 3)); // 1 gratis = 20
        $this->assertEquals(40.00, $promo3x2->calcularDescuento(20.00, 6)); // 2 gratis = 40
    }

    public function test_jerarquia_de_resolucion_de_promocion_en_producto(): void
    {
        $cat = Categoria::factory()->create();
        $lab = Laboratorio::factory()->create();

        $prod = Producto::factory()->create([
            'categoria_id'   => $cat->id,
            'laboratorio_id' => $lab->id,
            'precio_venta'   => 100.00,
        ]);

        // Crear promo General (10%)
        Promocion::factory()->create([
            'nombre'  => 'Promo General',
            'alcance' => 'general',
            'tipo'    => 'porcentaje',
            'valor'   => 10.00,
        ]);

        // Crear promo por Laboratorio (15%)
        Promocion::factory()->create([
            'nombre'         => 'Promo Lab',
            'alcance'        => 'laboratorio',
            'laboratorio_id' => $lab->id,
            'tipo'           => 'porcentaje',
            'valor'          => 15.00,
        ]);

        // Crear promo por Categoría (20%)
        Promocion::factory()->create([
            'nombre'       => 'Promo Cat',
            'alcance'      => 'categoria',
            'categoria_id' => $cat->id,
            'tipo'         => 'porcentaje',
            'valor'        => 20.00,
        ]);

        // Crear promo por Producto (30%)
        $promoProd = Promocion::factory()->create([
            'nombre'      => 'Promo Específica Producto',
            'alcance'     => 'producto',
            'producto_id' => $prod->id,
            'tipo'        => 'porcentaje',
            'valor'       => 30.00,
        ]);

        $promocionService = app(PromocionService::class);
        $promocionElegida = $promocionService->obtenerPromocionVigenteParaProducto($prod);

        // Debe priorizar la específica por Producto
        $this->assertNotNull($promocionElegida);
        $this->assertEquals($promoProd->id, $promocionElegida->id);
        $this->assertEquals(30.00, (float)$promocionElegida->valor);
    }

    public function test_promocion_no_aplica_si_esta_vencida_o_agotada(): void
    {
        $prod = Producto::factory()->create(['precio_venta' => 50.00]);

        // Promo vencida
        $promoVencida = Promocion::factory()->vencida()->create([
            'alcance'     => 'producto',
            'producto_id' => $prod->id,
        ]);

        $this->assertFalse($promoVencida->esVigente());
        $this->assertEquals(0.00, $promoVencida->calcularDescuento(50.00, 2));

        // Promo con stock agotado
        $promoAgotada = Promocion::factory()->create([
            'alcance'         => 'producto',
            'producto_id'     => $prod->id,
            'stock_limite'    => 10,
            'stock_consumido' => 10,
        ]);

        $this->assertFalse($promoAgotada->esVigente());
        $this->assertEquals(0.00, $promoAgotada->calcularDescuento(50.00, 2));
    }

    public function test_toggle_activo_promocion(): void
    {
        $promo = Promocion::factory()->create(['activo' => true]);

        $response = $this->actingAs($this->admin)->post(route('promociones.toggle-activo', $promo));

        $this->assertDatabaseHas('promociones', ['id' => $promo->id, 'activo' => false]);
    }

    /* ══════════════════════════════════════════════════════════════
       5. FLUJO 4: COMPARADOR DE PRECIOS Y COTIZACIONES
       ══════════════════════════════════════════════════════════════ */

    public function test_flujo_4_comparador_de_precios_y_cotizaciones(): void
    {
        $prod = Producto::factory()->create(['nombre' => 'Ceftriaxona 1g']);
        $provA = Proveedor::factory()->create(['nombre' => 'Proveedor Alfa']);
        $provB = Proveedor::factory()->create(['nombre' => 'Proveedor Beta']);

        // Historial proveedor Alfa ($8.00)
        HistorialPrecio::factory()->create([
            'producto_id'          => $prod->id,
            'proveedor_id'         => $provA->id,
            'precio_unitario_base' => 8.00,
            'tipo'                 => 'compra',
            'fecha'                => now()->subDays(5),
        ]);

        // Historial proveedor Beta ($6.50)
        HistorialPrecio::factory()->cotizacion()->create([
            'producto_id'          => $prod->id,
            'proveedor_id'         => $provB->id,
            'precio_unitario_base' => 6.50,
            'tipo'                 => 'cotizacion',
            'fecha'                => now()->subDays(1),
        ]);

        // Obtener el mejor precio para el producto
        $mejorPrecio = HistorialPrecio::mejorPrecioParaProducto($prod->id);
        $this->assertNotNull($mejorPrecio);
        $this->assertEquals($provB->id, $mejorPrecio->proveedor_id);
        $this->assertEquals(6.50, (float)$mejorPrecio->precio_unitario_base);

        // Obtener la comparativa completa
        $comparativa = HistorialPrecio::comparativaPorProducto($prod->id);
        $this->assertCount(2, $comparativa);
        $this->assertEquals($provB->id, $comparativa->first()['proveedor']->id); // Ordenado por mejor precio
    }

    /* ══════════════════════════════════════════════════════════════
       6. HISTORIAL AUDITORÍA, EXPORTACIÓN CSV Y AUTOCOMPLETADO
       ══════════════════════════════════════════════════════════════ */

    public function test_historial_general_auditoria_y_filtros(): void
    {
        $prod = Producto::factory()->create(['nombre' => 'Omeprazol 20mg']);

        PrecioVenta::factory()->create([
            'producto_id'   => $prod->id,
            'precio'        => 45.00,
            'motivo'        => 'Ajuste inicial',
            'user_id'       => $this->admin->id,
            'vigente_desde' => now()->subDays(2),
        ]);

        $response = $this->actingAs($this->admin)->get(route('precios.historial', ['q' => 'Omeprazol']));
        $response->assertStatus(200);
        $response->assertSee('Omeprazol 20mg');
        $response->assertSee('Ajuste inicial');
    }

    public function test_exportar_precios_csv(): void
    {
        Producto::factory()->create([
            'nombre'        => 'Amoxicilina 500mg',
            'codigo_barra'  => '743000111222',
            'precio_venta'  => 120.00,
            'precio_compra' => 80.00,
        ]);

        $response = $this->actingAs($this->admin)->get(route('precios.exportar'));
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_api_buscar_autocompletado_para_precios(): void
    {
        $prod = Producto::factory()->create(['nombre' => 'Metformina 850mg']);

        $response = $this->actingAs($this->admin)->get(route('precios.buscar-ajax', [
            'tipo' => 'producto',
            'q'    => 'Metformina',
        ]));

        $response->assertStatus(200);
        $response->assertJsonFragment(['id' => $prod->id]);
    }
}
