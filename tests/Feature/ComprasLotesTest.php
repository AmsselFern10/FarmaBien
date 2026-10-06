<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Proveedor;
use App\Models\Categoria;
use App\Models\Laboratorio;
use App\Models\Producto;
use App\Models\PresentacionProducto;
use App\Models\Compra;
use App\Models\Lote;
use App\Models\DetalleVenta;
use App\Models\Venta;
use App\Models\Cliente;
use App\Models\MovimientoInventario;
use App\Models\RegistroVentaControlado;
use App\Services\CompraService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ComprasLotesTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $usuarioSinPermisos;
    protected Proveedor $proveedor;
    protected Categoria $categoria;
    protected Laboratorio $laboratorio;
    protected Producto $productoNormal;
    protected Producto $productoControlado;
    protected PresentacionProducto $presentacionCaja;
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

        $this->proveedor = Proveedor::factory()->create(['nombre' => 'DILABSA Nicaragua', 'activo' => true]);
        $this->categoria = Categoria::factory()->create(['nombre' => 'Antibióticos']);
        $this->laboratorio = Laboratorio::factory()->create(['nombre' => 'Laboratorios Ramos']);

        // Producto regular con presentaciones
        $this->productoNormal = Producto::factory()->create([
            'categoria_id'   => $this->categoria->id,
            'laboratorio_id' => $this->laboratorio->id,
            'nombre'         => 'Ciprofloxacino 500mg',
            'precio_compra'  => 5.00,
            'precio_venta'   => 10.00,
            'activo'         => true,
            'tipo_control'   => Producto::TIPO_VENTA_LIBRE,
            'requiere_receta'=> false,
        ]);

        $this->presentacionCaja = PresentacionProducto::create([
            'producto_id'               => $this->productoNormal->id,
            'nombre'                    => 'Caja x 30 tabletas',
            'unidades_por_presentacion' => 30,
            'precio_compra'             => 150.00,
            'precio_venta'              => 300.00,
            'activo'                    => true,
        ]);

        // Producto controlado MINSA
        $this->productoControlado = Producto::factory()->controlado()->create([
            'categoria_id'   => $this->categoria->id,
            'laboratorio_id' => $this->laboratorio->id,
            'nombre'         => 'Clonazepam 2mg',
            'precio_compra'  => 12.00,
            'precio_venta'   => 25.00,
            'activo'         => true,
        ]);

        $this->compraService = app(CompraService::class);
    }

    /**
     * Test: Acceso a Compras requiere permisos
     */
    public function test_compras_requiere_permiso(): void
    {
        $responseGuest = $this->get(route('compras.index'));
        $responseGuest->assertRedirect(route('login'));

        $responseSinPermiso = $this->actingAs($this->usuarioSinPermisos)->get(route('compras.index'));
        $responseSinPermiso->assertStatus(403);

        $responseAdmin = $this->actingAs($this->admin)->get(route('compras.index'));
        $responseAdmin->assertStatus(200);
        $responseAdmin->assertViewIs('compras.index');
    }

    /**
     * Flujo 1: Compra de contado con presentaciones calcula unidades base, costos y Kardex
     */
    public function test_flujo_1_compra_contado_con_presentaciones_calcula_unidades_base_y_lotes(): void
    {
        // Comprar 5 Cajas (cada caja tiene 30 tabletas) = 150 unidades base.
        // Precio por caja C$ 150.00 -> Total C$ 750.00 -> Costo unitario base C$ 5.00
        $payload = [
            'proveedor_id'       => $this->proveedor->id,
            'numero_comprobante' => 'FAC-DILABSA-001',
            'fecha'              => now()->toDateString(),
            'condicion_pago'     => 'contado',
            'productos'          => [
                [
                    'producto_id'             => $this->productoNormal->id,
                    'presentacion_id'         => $this->presentacionCaja->id,
                    'cantidad_presentaciones' => 5,
                    'precio_unitario'         => 150.00,
                    'numero_lote'             => 'LOT-CIPRO-2026',
                    'fecha_vencimiento'       => now()->addMonths(24)->toDateString(),
                ],
            ],
        ];

        $response = $this->actingAs($this->admin)->post(route('compras.store'), $payload);
        $response->assertRedirect();

        $compra = Compra::first();
        $this->assertNotNull($compra);
        $this->assertEquals(750.00, $compra->total);
        $this->assertEquals(0.00, $compra->saldo_pendiente);
        $this->assertEquals('pagado', $compra->estado_pago);
        $this->assertEquals('recibida', $compra->estado);

        // Verificación de Detalle
        $detalle = $compra->detalles->first();
        $this->assertNotNull($detalle);
        $this->assertEquals(5, $detalle->cantidad_presentaciones);
        $this->assertEquals(150, $detalle->cantidad_unidades_base);
        $this->assertEquals(750.00, $detalle->subtotal);

        // Verificación de Lote
        $lote = Lote::first();
        $this->assertNotNull($lote);
        $this->assertEquals('LOT-CIPRO-2026', $lote->numero_lote);
        $this->assertEquals(150, $lote->stock_inicial);
        $this->assertEquals(150, $lote->stock_actual);
        $this->assertEquals(5.00, $lote->precio_compra);

        // Verificación de Kardex
        $kardex = MovimientoInventario::first();
        $this->assertNotNull($kardex);
        $this->assertEquals('entrada', $kardex->tipo);
        $this->assertEquals('compra', $kardex->subtipo);
        $this->assertEquals(150, $kardex->cantidad);
        $this->assertEquals(0, $kardex->stock_anterior);
        $this->assertEquals(150, $kardex->stock_posterior);
        $this->assertEquals(5.00, $kardex->costo_unitario);
        $this->assertEquals(750.00, $kardex->costo_total);
    }

    /**
     * Flujo 2: Compra de crédito genera lotes, Kardex y saldo en Cuentas por Pagar
     */
    public function test_flujo_2_compra_credito_crea_lotes_y_cuenta_por_pagar(): void
    {
        $payload = [
            'proveedor_id'           => $this->proveedor->id,
            'numero_comprobante'     => 'FAC-CREDITO-99',
            'fecha'                  => now()->toDateString(),
            'condicion_pago'         => 'credito',
            'dias_credito'           => 45,
            'fecha_vencimiento_pago' => now()->addDays(45)->toDateString(),
            'productos'              => [
                [
                    'producto_id'             => $this->productoNormal->id,
                    'cantidad_presentaciones' => 20,
                    'precio_unitario'         => 6.00,
                    'numero_lote'             => 'LOT-CRED-20',
                    'fecha_vencimiento'       => now()->addMonths(12)->toDateString(),
                ],
            ],
        ];

        $this->actingAs($this->admin)->post(route('compras.store'), $payload);

        $compra = Compra::first();
        $this->assertEquals('credito', $compra->condicion_pago);
        $this->assertEquals(120.00, $compra->total);
        $this->assertEquals(120.00, $compra->saldo_pendiente);
        $this->assertEquals('pendiente', $compra->estado_pago);
        $this->assertEquals(45, $compra->dias_credito);
    }

    /**
     * Flujo 3: Reingreso al mismo número de lote acumula stock sin duplicar registros de Lote
     */
    public function test_flujo_3_reingreso_a_mismo_lote_acumula_stock_correctamente(): void
    {
        // Primera compra: 10 unidades
        $this->compraService->registrarCompra([
            'proveedor_id'       => $this->proveedor->id,
            'numero_comprobante' => 'FAC-01',
            'fecha'              => now()->toDateString(),
            'condicion_pago'     => 'contado',
            'productos'          => [
                [
                    'producto_id'             => $this->productoNormal->id,
                    'cantidad_presentaciones' => 10,
                    'precio_unitario'         => 5.00,
                    'numero_lote'             => 'LOT-ACUMULABLE',
                    'fecha_vencimiento'       => now()->addMonths(12)->toDateString(),
                ],
            ],
        ]);

        $this->assertEquals(1, Lote::count());
        $this->assertEquals(10, Lote::first()->stock_actual);

        // Segunda compra: 15 unidades al MISMO lote
        $this->compraService->registrarCompra([
            'proveedor_id'       => $this->proveedor->id,
            'numero_comprobante' => 'FAC-02',
            'fecha'              => now()->toDateString(),
            'condicion_pago'     => 'contado',
            'productos'          => [
                [
                    'producto_id'             => $this->productoNormal->id,
                    'cantidad_presentaciones' => 15,
                    'precio_unitario'         => 5.50,
                    'numero_lote'             => 'LOT-ACUMULABLE',
                    'fecha_vencimiento'       => now()->addMonths(12)->toDateString(),
                ],
            ],
        ]);

        // Debe seguir existiendo 1 solo lote, pero con 25 unidades acumuladas
        $this->assertEquals(1, Lote::count());
        $lote = Lote::first();
        $this->assertEquals(25, $lote->stock_actual);
        $this->assertEquals(25, $lote->stock_inicial);
        // Precio Promedio Ponderado (PPP): (10 * 5.00 + 15 * 5.50) / 25 = 132.50 / 25 = 5.30
        $this->assertEquals(5.30, $lote->precio_compra);

        // Debe haber 2 movimientos de Kardex
        $this->assertEquals(2, MovimientoInventario::count());
    }

    /**
     * Flujo 4: Anulación de compra revierte stock y asienta salida en Kardex
     */
    public function test_flujo_4_anulacion_compra_revierte_stock_y_asienta_salida_en_kardex(): void
    {
        $compra = $this->compraService->registrarCompra([
            'proveedor_id'       => $this->proveedor->id,
            'numero_comprobante' => 'FAC-ANULAR',
            'fecha'              => now()->toDateString(),
            'condicion_pago'     => 'contado',
            'productos'          => [
                [
                    'producto_id'             => $this->productoNormal->id,
                    'cantidad_presentaciones' => 50,
                    'precio_unitario'         => 5.00,
                    'numero_lote'             => 'LOT-ANULAR-01',
                    'fecha_vencimiento'       => now()->addMonths(12)->toDateString(),
                ],
            ],
        ]);

        $this->assertEquals(50, Lote::first()->stock_actual);

        // Anular compra
        $response = $this->actingAs($this->admin)->post(route('compras.anular', $compra), [
            'motivo' => 'Factura anulada por proveedor por error de facturación',
        ]);

        $response->assertRedirect();
        $compra->refresh();

        $this->assertEquals('anulada', $compra->estado);
        $this->assertEquals('Factura anulada por proveedor por error de facturación', $compra->motivo_anulacion);

        // Stock del lote revertido a 0 y lote desactivado
        $lote = Lote::first();
        $this->assertEquals(0, $lote->stock_actual);
        $this->assertFalse($lote->activo);

        // Movimiento de salida en Kardex
        $movSalida = MovimientoInventario::where('subtipo', 'anulacion_compra')->first();
        $this->assertNotNull($movSalida);
        $this->assertEquals(-50, $movSalida->cantidad);
        $this->assertEquals(50, $movSalida->stock_anterior);
        $this->assertEquals(0, $movSalida->stock_posterior);
    }

    /**
     * Test: Bloqueo de anulación si el lote ya tiene ventas asociadas
     */
    public function test_bloqueo_anulacion_si_lote_tiene_ventas_o_stock_insuficiente(): void
    {
        $compra = $this->compraService->registrarCompra([
            'proveedor_id'       => $this->proveedor->id,
            'numero_comprobante' => 'FAC-BLOQUEO',
            'fecha'              => now()->toDateString(),
            'condicion_pago'     => 'contado',
            'productos'          => [
                [
                    'producto_id'             => $this->productoNormal->id,
                    'cantidad_presentaciones' => 20,
                    'precio_unitario'         => 5.00,
                    'numero_lote'             => 'LOT-BLOQUEO',
                    'fecha_vencimiento'       => now()->addMonths(12)->toDateString(),
                ],
            ],
        ]);

        $lote = Lote::first();

        // Simular que se vendieron 5 unidades del lote
        $cliente = Cliente::factory()->create();
        $venta = Venta::create([
            'cliente_id'      => $cliente->id,
            'user_id'         => $this->admin->id,
            'numero_factura'  => 'V-001',
            'subtotal'        => 50.00,
            'total'           => 50.00,
            'estado'          => 'completada',
            'metodo_pago'     => 'efectivo',
            'fecha'           => now(),
        ]);

        DetalleVenta::create([
            'venta_id'                  => $venta->id,
            'producto_id'               => $this->productoNormal->id,
            'lote_id'                   => $lote->id,
            'cantidad'                  => 5,
            'unidades_por_presentacion' => 1,
            'cantidad_unidades_base'    => 5,
            'precio_unitario'           => 10.00,
            'subtotal'                  => 50.00,
        ]);

        $lote->update(['stock_actual' => 15]);

        // Intentar anular la compra debe ser bloqueado
        $response = $this->actingAs($this->admin)->post(route('compras.anular', $compra), [
            'motivo' => 'Intento de anulación indebida',
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals('recibida', $compra->fresh()->estado);
    }

    /**
     * Flujo 5: Modificación de compra versiona v1 a v2 con trazabilidad completa
     */
    public function test_flujo_5_modificacion_compra_versiona_v1_a_v2_con_trazabilidad(): void
    {
        $compraOriginal = $this->compraService->registrarCompra([
            'proveedor_id'       => $this->proveedor->id,
            'numero_comprobante' => 'FAC-ORIGINAL',
            'fecha'              => now()->toDateString(),
            'condicion_pago'     => 'contado',
            'productos'          => [
                [
                    'producto_id'             => $this->productoNormal->id,
                    'cantidad_presentaciones' => 10,
                    'precio_unitario'         => 5.00,
                    'numero_lote'             => 'LOT-MOD-01',
                    'fecha_vencimiento'       => now()->addMonths(12)->toDateString(),
                ],
            ],
        ]);

        $payloadUpdate = [
            'proveedor_id'        => $this->proveedor->id,
            'numero_comprobante'  => 'FAC-ORIGINAL-CORR',
            'fecha'               => now()->toDateString(),
            'motivo_modificacion' => 'Corrección de cantidad y número de lote por proveedor',
            'productos'           => [
                [
                    'producto_id'             => $this->productoNormal->id,
                    'cantidad_presentaciones' => 12,
                    'precio_unitario'         => 5.00,
                    'numero_lote'             => 'LOT-MOD-02',
                    'fecha_vencimiento'       => now()->addMonths(18)->toDateString(),
                ],
            ],
        ];

        $response = $this->actingAs($this->admin)->put(route('compras.update', $compraOriginal), $payloadUpdate);
        $response->assertRedirect();

        $compraOriginal->refresh();
        $this->assertEquals('anulada', $compraOriginal->estado);
        $this->assertNotNull($compraOriginal->reemplazada_por);

        $nuevaCompra = Compra::find($compraOriginal->reemplazada_por);
        $this->assertNotNull($nuevaCompra);
        $this->assertEquals($compraOriginal->id, $nuevaCompra->compra_original_id);
        $this->assertEquals('FAC-ORIGINAL-CORR', $nuevaCompra->numero_comprobante);
        $this->assertEquals(60.00, $nuevaCompra->total); // 12 * 5.00
    }

    /**
     * Test: Fármacos controlados generan asiento en Libro MINSA al comprarse
     */
    public function test_medicamento_controlado_genera_asiento_en_libro_minsa(): void
    {
        $compra = $this->compraService->registrarCompra([
            'proveedor_id'       => $this->proveedor->id,
            'numero_comprobante' => 'FAC-CONTROLADO',
            'fecha'              => now()->toDateString(),
            'condicion_pago'     => 'contado',
            'productos'          => [
                [
                    'producto_id'             => $this->productoControlado->id,
                    'cantidad_presentaciones' => 30,
                    'precio_unitario'         => 12.00,
                    'numero_lote'             => 'LOT-CLONA-99',
                    'fecha_vencimiento'       => now()->addMonths(24)->toDateString(),
                ],
            ],
        ]);

        $registroMinsa = RegistroVentaControlado::where('compra_id', $compra->id)->first();
        $this->assertNotNull($registroMinsa);
        $this->assertEquals(RegistroVentaControlado::TIPO_COMPRA, $registroMinsa->tipo_movimiento);
        $this->assertEquals(30, $registroMinsa->cantidad);
        $this->assertEquals($this->productoControlado->id, $registroMinsa->producto_id);
    }

    /**
     * Test: Comando farma:verificar-compras ejecuta exitosamente
     */
    public function test_comando_verificar_compras_ejecuta_correctamente(): void
    {
        $this->artisan('farma:verificar-compras')
            ->expectsOutputToContain('AUDITORÍA DE INTEGRIDAD DE COMPRAS')
            ->expectsOutputToContain('0 INCONSISTENCIAS DETECTADAS')
            ->assertExitCode(0);
    }
}
