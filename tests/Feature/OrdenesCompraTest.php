<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Producto;
use App\Models\Lote;
use App\Models\Proveedor;
use App\Models\Categoria;
use App\Models\Laboratorio;
use App\Models\OrdenCompra;
use App\Models\DetalleOrdenCompra;
use App\Models\Compra;
use App\Models\MovimientoInventario;
use App\Models\MovimientoCaja;
use App\Services\OrdenCompraService;
use App\Services\CompraService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OrdenesCompraTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $usuarioSinPermisos;
    protected Categoria $categoria;
    protected Laboratorio $laboratorio;
    protected Proveedor $proveedor;
    protected Producto $productoA;
    protected Producto $productoB;
    protected OrdenCompraService $ordenCompraService;
    protected CompraService $compraService;

    protected function setUp(): void
    {
        parent::setUp();

        $permisos = [
            'ver compras',
            'registrar compras',
            'anular compras',
            'ver movimientos inventario',
            'ver productos',
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
        $this->laboratorio = Laboratorio::factory()->create(['nombre' => 'Laboratorios Ramos']);
        $this->proveedor = Proveedor::factory()->create(['nombre' => 'Distribuidora Farmacéutica']);

        $this->productoA = Producto::factory()->create([
            'categoria_id'   => $this->categoria->id,
            'laboratorio_id' => $this->laboratorio->id,
            'nombre'         => 'Amoxicilina 500mg',
            'precio_compra'  => 5.00,
            'precio_venta'   => 8.00,
            'stock_minimo'   => 10,
            'activo'         => true,
        ]);

        $this->productoB = Producto::factory()->create([
            'categoria_id'   => $this->categoria->id,
            'laboratorio_id' => $this->laboratorio->id,
            'nombre'         => 'Azitromicina 500mg',
            'precio_compra'  => 12.00,
            'precio_venta'   => 18.00,
            'stock_minimo'   => 5,
            'activo'         => true,
        ]);

        $this->ordenCompraService = app(OrdenCompraService::class);
        $this->compraService = app(CompraService::class);
    }

    /**
     * Test: Acceso a Órdenes de Compra requiere permisos
     */
    public function test_ordenes_compra_requiere_permiso(): void
    {
        $responseGuest = $this->get(route('ordenes-compras.index'));
        $responseGuest->assertRedirect(route('login'));

        $responseSinPermiso = $this->actingAs($this->usuarioSinPermisos)->get(route('ordenes-compras.index'));
        $responseSinPermiso->assertStatus(403);

        $responseAdmin = $this->actingAs($this->admin)->get(route('ordenes-compras.index'));
        $responseAdmin->assertStatus(200);
        $responseAdmin->assertViewIs('compras.ordenes.index');
    }

    /**
     * Flujo 1: Crear una orden de compra se guarda SIN efectos en inventario, kardex, caja ni CxP
     */
    public function test_flujo_1_crear_orden_sin_efectos_en_inventario_kardex_caja_ni_cxp(): void
    {
        $payload = [
            'proveedor_id'           => $this->proveedor->id,
            'fecha_emision'          => now()->toDateString(),
            'fecha_esperada_entrega' => now()->addDays(3)->toDateString(),
            'condicion_pago'         => 'contado',
            'observaciones'          => 'Pedido urgente para reposición de stock',
            'items'                  => [
                [
                    'producto_id'     => $this->productoA->id,
                    'cantidad'        => 50,
                    'precio_unitario' => 5.00,
                ],
                [
                    'producto_id'     => $this->productoB->id,
                    'cantidad'        => 20,
                    'precio_unitario' => 12.00,
                ],
            ],
        ];

        $response = $this->actingAs($this->admin)->post(route('ordenes-compras.store'), $payload);
        $response->assertRedirect();

        $orden = OrdenCompra::first();
        $this->assertNotNull($orden);
        $this->assertStringStartsWith('OC-' . now()->year . '-', $orden->numero_orden);
        $this->assertEquals(490.00, $orden->total); // (50*5) + (20*12) = 250 + 240 = 490
        $this->assertEquals('enviada', $orden->estado);
        $this->assertCount(2, $orden->detalles);
        $this->assertEquals(70, $orden->pendiente_total);

        // INVARIANTE CRÍTICO: Cero efectos secundarios en inventario o finanzas
        $this->assertEquals(0, Lote::count(), 'No debe crearse ningún lote al emitir una orden');
        $this->assertEquals(0, MovimientoInventario::count(), 'No debe generarse movimiento de Kardex');
        $this->assertEquals(0, MovimientoCaja::count(), 'No debe tocarse la caja');
        $this->assertEquals(0, Compra::count(), 'No debe crearse registro de compra aún');
    }

    /**
     * Flujo 2: Recepción parcial actualiza la orden a 'recibida_parcial' con lotes exactos
     */
    public function test_flujo_2_recepcion_parcial_actualiza_orden_a_parcial_con_lotes_exactos(): void
    {
        $orden = $this->ordenCompraService->crearOrden([
            'proveedor_id'           => $this->proveedor->id,
            'fecha_emision'          => now()->toDateString(),
            'condicion_pago'         => 'contado',
            'items'                  => [
                [
                    'producto_id'     => $this->productoA->id,
                    'cantidad'        => 10,
                    'precio_unitario' => 5.00,
                ],
            ],
        ], $this->admin->id);

        $detalleOrden = $orden->detalles->first();

        // Recepcionar parcialmente: 9 unidades de 10
        $compraData = [
            'proveedor_id'        => $this->proveedor->id,
            'orden_compra_id'     => $orden->id,
            'tipo_comprobante'    => 'Factura',
            'numero_comprobante'  => 'FACT-REC-01',
            'fecha_emision'       => now()->toDateString(),
            'condicion_pago'      => 'contado',
            'productos'           => [
                [
                    'producto_id'             => $this->productoA->id,
                    'detalle_orden_compra_id' => $detalleOrden->id,
                    'cantidad_presentaciones' => 9,
                    'precio_unitario'         => 5.00,
                    'numero_lote'             => 'LOT-PARCIAL-01',
                    'fecha_vencimiento'       => now()->addMonths(12)->toDateString(),
                ],
            ],
        ];

        $compra = $this->compraService->registrarCompra($compraData);

        $orden->refresh();
        $detalleOrden->refresh();

        $this->assertEquals('recibida_parcial', $orden->estado);
        $this->assertEquals(9, $detalleOrden->cantidad_recibida);
        $this->assertEquals(1, $orden->pendiente_total);
        $this->assertFalse($orden->estaCompleta());

        // Lotes y Kardex solo por las 9 unidades recibidas
        $this->assertEquals(1, Lote::count());
        $lote = Lote::first();
        $this->assertEquals(9, $lote->stock_actual);
        $this->assertEquals('LOT-PARCIAL-01', $lote->numero_lote);

        $movKardex = MovimientoInventario::first();
        $this->assertEquals(9, $movKardex->cantidad);
        $this->assertEquals('entrada', $movKardex->tipo);
    }

    /**
     * Flujo 3: Segunda recepción completa la orden
     */
    public function test_flujo_3_segunda_recepcion_completa_la_orden(): void
    {
        $orden = $this->ordenCompraService->crearOrden([
            'proveedor_id'           => $this->proveedor->id,
            'fecha_emision'          => now()->toDateString(),
            'condicion_pago'         => 'contado',
            'items'                  => [
                [
                    'producto_id'     => $this->productoA->id,
                    'cantidad'        => 10,
                    'precio_unitario' => 5.00,
                ],
            ],
        ], $this->admin->id);

        $detalleOrden = $orden->detalles->first();

        // Primera recepción: 6 unidades
        $this->compraService->registrarCompra([
            'proveedor_id'        => $this->proveedor->id,
            'orden_compra_id'     => $orden->id,
            'tipo_comprobante'    => 'Factura',
            'numero_comprobante'  => 'FACT-01',
            'fecha_emision'       => now()->toDateString(),
            'condicion_pago'      => 'contado',
            'productos'           => [
                [
                    'producto_id'             => $this->productoA->id,
                    'detalle_orden_compra_id' => $detalleOrden->id,
                    'cantidad_presentaciones' => 6,
                    'precio_unitario'         => 5.00,
                    'numero_lote'             => 'LOT-01',
                    'fecha_vencimiento'       => now()->addMonths(12)->toDateString(),
                ],
            ],
        ]);

        $orden->refresh();
        $this->assertEquals('recibida_parcial', $orden->estado);
        $this->assertEquals(4, $orden->pendiente_total);

        // Segunda recepción: 4 unidades restantes
        $this->compraService->registrarCompra([
            'proveedor_id'        => $this->proveedor->id,
            'orden_compra_id'     => $orden->id,
            'tipo_comprobante'    => 'Factura',
            'numero_comprobante'  => 'FACT-02',
            'fecha_emision'       => now()->toDateString(),
            'condicion_pago'      => 'contado',
            'productos'           => [
                [
                    'producto_id'             => $this->productoA->id,
                    'detalle_orden_compra_id' => $detalleOrden->id,
                    'cantidad_presentaciones' => 4,
                    'precio_unitario'         => 5.00,
                    'numero_lote'             => 'LOT-02',
                    'fecha_vencimiento'       => now()->addMonths(12)->toDateString(),
                ],
            ],
        ]);

        $orden->refresh();
        $detalleOrden->refresh();

        $this->assertEquals('recibida_total', $orden->estado);
        $this->assertEquals(10, $detalleOrden->cantidad_recibida);
        $this->assertEquals(0, $orden->pendiente_total);
        $this->assertTrue($orden->estaCompleta());
    }

    /**
     * Flujo 4: Cierre con faltante registra auditoría y completa la orden
     */
    public function test_flujo_4_cierre_con_faltante_registra_auditoria_y_completa_orden(): void
    {
        $orden = $this->ordenCompraService->crearOrden([
            'proveedor_id'           => $this->proveedor->id,
            'fecha_emision'          => now()->toDateString(),
            'condicion_pago'         => 'contado',
            'items'                  => [
                [
                    'producto_id'     => $this->productoA->id,
                    'cantidad'        => 20,
                    'precio_unitario' => 5.00,
                ],
            ],
        ], $this->admin->id);

        $detalleOrden = $orden->detalles->first();

        // Recepción parcial con cierre forzoso por faltante
        $this->compraService->registrarCompra([
            'proveedor_id'           => $this->proveedor->id,
            'orden_compra_id'        => $orden->id,
            'cerrar_orden_completa'  => true,
            'motivo_faltante'        => 'Proveedor agotó existencia del lote en fábrica',
            'tipo_comprobante'       => 'Factura',
            'numero_comprobante'     => 'FACT-FALTANTE-01',
            'fecha_emision'          => now()->toDateString(),
            'condicion_pago'         => 'contado',
            'productos'              => [
                [
                    'producto_id'             => $this->productoA->id,
                    'detalle_orden_compra_id' => $detalleOrden->id,
                    'cantidad_presentaciones' => 15,
                    'precio_unitario'         => 5.00,
                    'numero_lote'             => 'LOT-FALTANTE-01',
                    'fecha_vencimiento'       => now()->addMonths(12)->toDateString(),
                ],
            ],
        ]);

        $orden->refresh();

        $this->assertEquals('recibida_total', $orden->estado);
        $this->assertTrue($orden->cerrada_con_faltante);
        $this->assertEquals(5, $orden->faltante_unidades);
        $this->assertEquals('Proveedor agotó existencia del lote en fábrica', $orden->motivo_faltante);
    }

    /**
     * Test: Cancelar orden con recepciones previas es bloqueado
     */
    public function test_cancelar_orden_con_recepciones_es_bloqueado(): void
    {
        $orden = $this->ordenCompraService->crearOrden([
            'proveedor_id'   => $this->proveedor->id,
            'fecha_emision'  => now()->toDateString(),
            'condicion_pago' => 'contado',
            'items'          => [
                [
                    'producto_id'     => $this->productoA->id,
                    'cantidad'        => 10,
                    'precio_unitario' => 5.00,
                ],
            ],
        ], $this->admin->id);

        // Recepción parcial
        $this->compraService->registrarCompra([
            'proveedor_id'        => $this->proveedor->id,
            'orden_compra_id'     => $orden->id,
            'tipo_comprobante'    => 'Factura',
            'numero_comprobante'  => 'FACT-01',
            'fecha_emision'       => now()->toDateString(),
            'condicion_pago'      => 'contado',
            'productos'           => [
                [
                    'producto_id'             => $this->productoA->id,
                    'detalle_orden_compra_id' => $orden->detalles->first()->id,
                    'cantidad_presentaciones' => 5,
                    'precio_unitario'         => 5.00,
                    'numero_lote'             => 'LOT-01',
                    'fecha_vencimiento'       => now()->addMonths(12)->toDateString(),
                ],
            ],
        ]);

        $response = $this->actingAs($this->admin)->post(route('ordenes-compras.cancelar', $orden));
        $response->assertSessionHas('error');

        $this->assertEquals('recibida_parcial', $orden->fresh()->estado);
    }

    /**
     * Test: Cancelar orden sin recepciones es exitoso
     */
    public function test_cancelar_orden_sin_recepciones_es_exitoso(): void
    {
        $orden = $this->ordenCompraService->crearOrden([
            'proveedor_id'   => $this->proveedor->id,
            'fecha_emision'  => now()->toDateString(),
            'condicion_pago' => 'contado',
            'items'          => [
                [
                    'producto_id'     => $this->productoA->id,
                    'cantidad'        => 10,
                    'precio_unitario' => 5.00,
                ],
            ],
        ], $this->admin->id);

        $response = $this->actingAs($this->admin)->post(route('ordenes-compras.cancelar', $orden), [
            'motivo_cancelacion' => 'Error en cantidades acordadas con el proveedor',
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals('cancelada', $orden->fresh()->estado);
    }

    /**
     * Test: Formulario create carga laboratorio correctamente y no muestra 'Sin Lab'
     */
    public function test_formulario_create_carga_laboratorio_correctamente(): void
    {
        $response = $this->actingAs($this->admin)->get(route('ordenes-compras.create'));
        $response->assertStatus(200);
        $response->assertSee('Laboratorios Ramos');
        $response->assertDontSee('• Lab: Sin Lab');
    }

    /**
     * Test: Comando de verificación de integridad ejecuta correctamente
     */
    public function test_comando_verificar_ordenes_compra_ejecuta_correctamente(): void
    {
        $this->artisan('farma:verificar-ordenes-compra')
            ->expectsOutputToContain('AUDITORÍA DE INTEGRIDAD DE ÓRDENES DE COMPRA')
            ->expectsOutputToContain('0 INCONSISTENCIAS DETECTADAS')
            ->assertExitCode(0);
    }
}
