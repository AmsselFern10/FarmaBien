<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Producto;
use App\Models\PresentacionProducto;
use App\Models\Categoria;
use App\Models\Laboratorio;
use App\Models\Proveedor;
use App\Models\Lote;
use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\Compra;
use App\Models\DetalleCompra;
use App\Services\ProductoService;
use App\Services\PresentacionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductosPresentacionesTest extends TestCase
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
            'ver productos', 'crear productos', 'editar productos', 'desactivar productos',
            'ver presentaciones', 'crear presentaciones', 'editar presentaciones', 'desactivar presentaciones',
            'ver categorias', 'ver laboratorios', 'ver proveedores', 'ver clientes',
        ];

        foreach ($permisos as $permiso) {
            Permission::findOrCreate($permiso, 'web');
        }

        $roleAdmin = Role::findOrCreate('Administrador', 'web');
        $roleAdmin->syncPermissions($permisos);

        $this->admin = User::factory()->create();
        $this->admin->assignRole($roleAdmin);

        $this->usuarioSinPermisos = User::factory()->create();

        $this->categoria = Categoria::factory()->create(['nombre' => 'Antibióticos']);
        $this->laboratorio = Laboratorio::factory()->create(['nombre' => 'Bayer']);
        $this->proveedor = Proveedor::factory()->create(['nombre' => 'Distribuidora Farmacéutica']);
    }

    /* ══════════════════════════════════════════════════════════════
       1. PERMISOS Y ACCESO
       ══════════════════════════════════════════════════════════════ */

    public function test_usuario_sin_permisos_no_puede_listar_productos(): void
    {
        $response = $this->actingAs($this->usuarioSinPermisos)->get(route('productos.index'));
        $response->assertStatus(403);
    }

    public function test_admin_puede_listar_productos(): void
    {
        Producto::factory()->create([
            'nombre'         => 'Amoxicilina 500mg',
            'categoria_id'   => $this->categoria->id,
            'laboratorio_id' => $this->laboratorio->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('productos.index'));

        $response->assertStatus(200);
        $response->assertSee('Amoxicilina 500mg');
    }

    /* ══════════════════════════════════════════════════════════════
       2. CREACIÓN DE PRODUCTOS Y PRESENTACIONES
       ══════════════════════════════════════════════════════════════ */

    public function test_crear_producto_sin_presentaciones_genera_unidad_base_automatica(): void
    {
        $data = [
            'nombre'         => 'Paracetamol 500mg',
            'categoria_id'   => $this->categoria->id,
            'laboratorio_id' => $this->laboratorio->id,
            'tipo_control'   => 'venta_libre',
            'precio_compra'  => 5.00,
            'precio_venta'   => 8.50,
            'stock_minimo'   => 10,
        ];

        $response = $this->actingAs($this->admin)->post(route('productos.store'), $data);

        $response->assertRedirect(route('productos.index'));
        $this->assertDatabaseHas('productos', ['nombre' => 'Paracetamol 500mg']);

        $producto = Producto::where('nombre', 'Paracetamol 500mg')->first();
        $this->assertNotNull($producto);
        $this->assertCount(1, $producto->presentaciones);
        $this->assertTrue((bool)$producto->presentaciones->first()->es_unidad_base);
        $this->assertEquals(1, $producto->presentaciones->first()->unidades_por_presentacion);
    }

    public function test_crear_producto_con_multiples_presentaciones(): void
    {
        $data = [
            'nombre'         => 'Ibuprofeno 400mg',
            'categoria_id'   => $this->categoria->id,
            'laboratorio_id' => $this->laboratorio->id,
            'tipo_control'   => 'venta_libre',
            'precio_compra'  => 2.00,
            'precio_venta'   => 4.00,
            'stock_minimo'   => 20,
            'presentaciones' => [
                [
                    'nombre'                    => 'Pastilla Suelta',
                    'unidades_por_presentacion' => 1,
                    'precio_compra'             => 2.00,
                    'precio_venta'              => 4.00,
                    'es_unidad_base'            => 1,
                ],
                [
                    'nombre'                    => 'Caja x 20',
                    'unidades_por_presentacion' => 20,
                    'precio_compra'             => 35.00,
                    'precio_venta'              => 70.00,
                    'es_unidad_base'            => 0,
                ],
            ],
        ];

        $response = $this->actingAs($this->admin)->post(route('productos.store'), $data);

        $response->assertRedirect(route('productos.index'));
        $producto = Producto::where('nombre', 'Ibuprofeno 400mg')->firstOrFail();
        $this->assertCount(2, $producto->presentaciones);
        $this->assertEquals(2, $producto->presentacionesActivas()->count());
    }

    /* ══════════════════════════════════════════════════════════════
       3. EDICIÓN Y ACTUALIZACIÓN
       ══════════════════════════════════════════════════════════════ */

    public function test_actualizar_producto_y_sus_presentaciones(): void
    {
        $producto = Producto::factory()->create([
            'nombre'         => 'Azitromicina 500mg',
            'categoria_id'   => $this->categoria->id,
            'laboratorio_id' => $this->laboratorio->id,
        ]);

        $presentacion = PresentacionProducto::factory()->create([
            'producto_id'               => $producto->id,
            'nombre'                    => 'Caja x 3',
            'unidades_por_presentacion' => 3,
            'es_unidad_base'            => true,
        ]);

        $updateData = [
            'nombre'         => 'Azitromicina 500mg Modificada',
            'categoria_id'   => $this->categoria->id,
            'laboratorio_id' => $this->laboratorio->id,
            'tipo_control'   => 'controlado',
            'precio_compra'  => 15.00,
            'precio_venta'   => 25.00,
            'stock_minimo'   => 5,
            'presentaciones' => [
                [
                    'id'                        => $presentacion->id,
                    'nombre'                    => 'Caja x 3 Editada',
                    'unidades_por_presentacion' => 3,
                    'precio_compra'             => 15.00,
                    'precio_venta'              => 25.00,
                    'es_unidad_base'            => 1,
                ]
            ]
        ];

        $response = $this->actingAs($this->admin)->put(route('productos.update', $producto), $updateData);

        $response->assertRedirect(route('productos.index'));
        $this->assertDatabaseHas('productos', ['id' => $producto->id, 'nombre' => 'Azitromicina 500mg Modificada']);
        $this->assertDatabaseHas('presentaciones_producto', ['id' => $presentacion->id, 'nombre' => 'Caja x 3 Editada']);
    }

    /* ══════════════════════════════════════════════════════════════
       4. INTEGRIDAD Y DESACTIVACIÓN DE PRESENTACIONES
       ══════════════════════════════════════════════════════════════ */

    public function test_no_se_puede_eliminar_presentacion_con_historial_en_ventas(): void
    {
        $producto = Producto::factory()->create();
        $presentacion = PresentacionProducto::factory()->create(['producto_id' => $producto->id]);

        $venta = Venta::create([
            'user_id'            => $this->admin->id,
            'numero_comprobante' => 'B001-00000001',
            'tipo_comprobante'   => 'boleta',
            'fecha'              => now(),
            'total'              => 100,
            'estado'             => 'completada',
        ]);

        $lote = Lote::factory()->create(['producto_id' => $producto->id]);

        DetalleVenta::create([
            'venta_id'                  => $venta->id,
            'producto_id'               => $producto->id,
            'lote_id'                   => $lote->id,
            'presentacion_id'           => $presentacion->id,
            'cantidad'                  => 1,
            'unidades_por_presentacion' => $presentacion->unidades_por_presentacion,
            'cantidad_unidades_base'    => $presentacion->unidades_por_presentacion,
            'precio_unitario'           => 100,
            'subtotal'                  => 100,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('presentaciones.destroy', $presentacion));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('presentaciones_producto', ['id' => $presentacion->id]);
    }

    public function test_toggle_activo_producto(): void
    {
        $producto = Producto::factory()->create(['activo' => true]);

        $response = $this->actingAs($this->admin)->delete(route('productos.destroy', $producto));

        $response->assertRedirect(route('productos.index'));
        $this->assertDatabaseHas('productos', ['id' => $producto->id, 'activo' => false]);
    }

    public function test_toggle_activo_presentacion(): void
    {
        $producto = Producto::factory()->create();
        $presentacion = PresentacionProducto::factory()->create(['producto_id' => $producto->id, 'activo' => true]);

        $response = $this->actingAs($this->admin)->post(route('presentaciones.toggle-activo', $presentacion));

        $this->assertDatabaseHas('presentaciones_producto', ['id' => $presentacion->id, 'activo' => false]);
    }

    /* ══════════════════════════════════════════════════════════════
       5. LOS 4 FLUJOS DE REGRESIÓN OBLIGATORIOS
       ══════════════════════════════════════════════════════════════ */

    /**
     * Flujo 1: Vender simple y por presentación (unidades base)
     */
    public function test_flujo_1_vender_por_presentacion_calcula_unidades_base(): void
    {
        $presentacionService = app(PresentacionService::class);

        $producto = Producto::factory()->create();
        $presentacionCaja = PresentacionProducto::factory()->create([
            'producto_id'               => $producto->id,
            'nombre'                    => 'Caja x 50',
            'unidades_por_presentacion' => 50,
        ]);

        $cantidadVendida = 3; // 3 cajas
        $unidadesBaseEsperadas = $presentacionService->calcularUnidadesBase($cantidadVendida, $presentacionCaja->unidades_por_presentacion);

        $this->assertEquals(150, $unidadesBaseEsperadas);
        $this->assertEquals(150, $producto->calcularUnidadesBase($cantidadVendida, $presentacionCaja));
    }

    /**
     * Flujo 2: Recibir compra por presentación (lote y costo)
     */
    public function test_flujo_2_recibir_compra_por_presentacion(): void
    {
        $producto = Producto::factory()->create();
        $presentacion = PresentacionProducto::factory()->create([
            'producto_id'               => $producto->id,
            'nombre'                    => 'Caja x 100',
            'unidades_por_presentacion' => 100,
            'precio_compra'             => 200.00,
        ]);

        $compra = Compra::create([
            'proveedor_id'       => $this->proveedor->id,
            'user_id'            => $this->admin->id,
            'numero_comprobante' => 'F001-00000001',
            'tipo_comprobante'   => 'factura',
            'fecha'              => now(),
            'total'              => 400.00,
            'estado'             => 'recibida',
        ]);

        $cantidadComprada = 2; // 2 cajas = 200 unidades base
        $unidadesBase = $cantidadComprada * $presentacion->unidades_por_presentacion;

        $lote = Lote::create([
            'producto_id'       => $producto->id,
            'compra_id'         => $compra->id,
            'proveedor_id'      => $this->proveedor->id,
            'numero_lote'       => 'LOT-ABC-123',
            'fecha_vencimiento' => now()->addYears(2),
            'stock_inicial'     => $unidadesBase,
            'stock_actual'      => $unidadesBase,
            'precio_compra'     => 200.00 / 100, // Costo unitario base
            'activo'            => true,
        ]);

        $detalleCompra = DetalleCompra::create([
            'compra_id'                 => $compra->id,
            'producto_id'               => $producto->id,
            'lote_id'                   => $lote->id,
            'presentacion_id'           => $presentacion->id,
            'cantidad'                  => $cantidadComprada,
            'unidades_por_presentacion' => $presentacion->unidades_por_presentacion,
            'cantidad_unidades_base'    => $unidadesBase,
            'precio_unitario'           => 200.00,
            'subtotal'                  => 400.00,
        ]);

        $this->assertEquals(200, $producto->fresh()->stock_total);
        $this->assertEquals($lote->stock_actual, $detalleCompra->cantidad_unidades_base);
    }

    /**
     * Flujo 3: Desactivar producto (salida de POS/reorden sin borrar historial)
     */
    public function test_flujo_3_desactivar_producto_preserva_historial_y_excluye_de_activos(): void
    {
        $producto = Producto::factory()->create([
            'nombre' => 'Omeprazol 20mg',
            'activo' => true
        ]);

        $this->assertTrue(Producto::activos()->where('id', $producto->id)->exists());

        // Desactivar producto
        app(ProductoService::class)->cambiarEstado($producto);

        $producto->refresh();
        $this->assertFalse((bool)$producto->activo);
        $this->assertFalse(Producto::activos()->where('id', $producto->id)->exists());
        $this->assertDatabaseHas('productos', ['id' => $producto->id, 'nombre' => 'Omeprazol 20mg']);
    }

    /**
     * Flujo 4: Listado y filtros por laboratorio, categoría y régimen
     */
    public function test_flujo_4_filtros_por_laboratorio_categoria_y_regimen(): void
    {
        $cat1 = Categoria::factory()->create(['nombre' => 'Analgésicos']);
        $cat2 = Categoria::factory()->create(['nombre' => 'Vitaminas']);
        $lab1 = Laboratorio::factory()->create(['nombre' => 'Pfizer']);
        $lab2 = Laboratorio::factory()->create(['nombre' => 'Roche']);

        $p1 = Producto::factory()->create([
            'nombre'         => 'Paracetamol Forte',
            'categoria_id'   => $cat1->id,
            'laboratorio_id' => $lab1->id,
            'tipo_control'   => Producto::TIPO_VENTA_LIBRE,
            'requiere_receta'=> false,
        ]);

        $p2 = Producto::factory()->create([
            'nombre'         => 'Vitamina C 1000mg',
            'categoria_id'   => $cat2->id,
            'laboratorio_id' => $lab2->id,
            'tipo_control'   => Producto::TIPO_VENTA_LIBRE,
            'requiere_receta'=> false,
        ]);

        $p3 = Producto::factory()->controlado()->create([
            'nombre'         => 'Clonazepam 2mg',
            'categoria_id'   => $cat1->id,
            'laboratorio_id' => $lab1->id,
        ]);

        // Filtro por categoría
        $resCat = Producto::porCategoria($cat1->id)->pluck('id');
        $this->assertTrue($resCat->contains($p1->id));
        $this->assertTrue($resCat->contains($p3->id));
        $this->assertFalse($resCat->contains($p2->id));

        // Filtro por laboratorio
        $resLab = Producto::porLaboratorio($lab2->id)->pluck('id');
        $this->assertTrue($resLab->contains($p2->id));
        $this->assertFalse($resLab->contains($p1->id));

        // Filtro por régimen controlado
        $resControlados = Producto::porRegimen('controlado')->pluck('id');
        $this->assertTrue($resControlados->contains($p3->id));
        $this->assertFalse($resControlados->contains($p1->id));
    }

    /* ══════════════════════════════════════════════════════════════
       6. ENDPOINTS API DE PRESENTACIONES
       ══════════════════════════════════════════════════════════════ */

    public function test_api_puede_listar_presentaciones_de_un_producto(): void
    {
        $producto = Producto::factory()->create();
        PresentacionProducto::factory()->create([
            'producto_id' => $producto->id,
            'nombre'      => 'Blíster x 10',
            'activo'      => true,
        ]);

        $response = $this->actingAs($this->admin)->getJson(route('api.productos.presentaciones.index', $producto));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'producto_id',
            'presentaciones'
        ]);
        $this->assertEquals('Blíster x 10', $response->json('presentaciones.0.nombre'));
    }

    public function test_api_puede_crear_presentacion_para_un_producto(): void
    {
        $producto = Producto::factory()->create();

        $payload = [
            'nombre'                    => 'Frasco x 100ml',
            'unidades_por_presentacion' => 1,
            'precio_venta'              => 12.50,
            'es_unidad_base'            => 1,
        ];

        $response = $this->actingAs($this->admin)->postJson(route('api.productos.presentaciones.store', $producto), $payload);

        $response->assertStatus(201);
        $this->assertDatabaseHas('presentaciones_producto', [
            'producto_id' => $producto->id,
            'nombre'      => 'Frasco x 100ml',
        ]);
    }
}
