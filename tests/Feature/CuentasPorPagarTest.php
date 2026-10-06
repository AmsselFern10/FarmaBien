<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Proveedor;
use App\Models\Categoria;
use App\Models\Laboratorio;
use App\Models\Producto;
use App\Models\Compra;
use App\Models\Lote;
use App\Models\MovimientoInventario;
use App\Models\MovimientoCaja;
use App\Models\Caja;
use App\Models\SesionCaja;
use App\Models\PagoCuentaPorPagar;
use App\Services\CuentaPorPagarService;
use App\Services\CompraService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CuentasPorPagarTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $usuarioSinPermisos;
    protected Proveedor $proveedor;
    protected Categoria $categoria;
    protected Laboratorio $laboratorio;
    protected Producto $producto;
    protected Caja $caja;
    protected SesionCaja $sesionCaja;
    protected CuentaPorPagarService $cuentaPorPagarService;
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
            'administrar cajas',
        ];

        foreach ($permisos as $permiso) {
            Permission::findOrCreate($permiso, 'web');
        }

        $roleAdmin = Role::findOrCreate('Administrador', 'web');
        $roleAdmin->syncPermissions($permisos);

        $this->admin = User::factory()->create();
        $this->admin->assignRole($roleAdmin);

        $this->usuarioSinPermisos = User::factory()->create();

        $this->proveedor = Proveedor::factory()->create(['nombre' => 'Droguería Central']);
        $this->categoria = Categoria::factory()->create(['nombre' => 'Analgésicos']);
        $this->laboratorio = Laboratorio::factory()->create(['nombre' => 'Bayer']);

        $this->producto = Producto::factory()->create([
            'categoria_id'   => $this->categoria->id,
            'laboratorio_id' => $this->laboratorio->id,
            'nombre'         => 'Aspirina 500mg',
            'precio_compra'  => 10.00,
            'precio_venta'   => 15.00,
            'activo'         => true,
        ]);

        $this->caja = Caja::create([
            'nombre'      => 'Caja Principal 01',
            'codigo'      => 'CAJA-01',
            'descripcion' => 'Caja principal del turno',
            'activo'      => true,
        ]);
        $this->sesionCaja = SesionCaja::create([
            'caja_id'           => $this->caja->id,
            'user_id'           => $this->admin->id,
            'monto_inicial'     => 1000.00,
            'total_ventas'      => 0,
            'total_egresos'     => 0,
            'total_ingresos'    => 0,
            'estado'            => 'abierta',
            'fecha_apertura'    => now(),
        ]);

        $this->cuentaPorPagarService = app(CuentaPorPagarService::class);
        $this->compraService = app(CompraService::class);
    }

    /**
     * Test: Acceso a Cuentas por Pagar requiere permisos
     */
    public function test_cuentas_por_pagar_requiere_permiso(): void
    {
        $responseGuest = $this->get(route('cuentas-por-pagar.index'));
        $responseGuest->assertRedirect(route('login'));

        $responseSinPermiso = $this->actingAs($this->usuarioSinPermisos)->get(route('cuentas-por-pagar.index'));
        $responseSinPermiso->assertStatus(403);

        $responseAdmin = $this->actingAs($this->admin)->get(route('cuentas-por-pagar.index'));
        $responseAdmin->assertStatus(200);
        $responseAdmin->assertViewIs('compras.cuentas-por-pagar.index');
    }

    /**
     * Flujo 1: Compra de mercancía a crédito genera cuenta por pagar con saldo igual al total
     */
    public function test_flujo_1_compra_credito_genera_cuenta_por_pagar_correcta(): void
    {
        $compra = $this->compraService->registrarCompra([
            'proveedor_id'           => $this->proveedor->id,
            'tipo_comprobante'       => 'Factura',
            'numero_comprobante'     => 'FACT-CRED-01',
            'fecha_emision'          => now()->toDateString(),
            'condicion_pago'         => 'credito',
            'dias_credito'           => 30,
            'fecha_vencimiento_pago' => now()->addDays(30)->toDateString(),
            'productos'              => [
                [
                    'producto_id'             => $this->producto->id,
                    'cantidad_presentaciones' => 40,
                    'precio_unitario'         => 10.00,
                    'numero_lote'             => 'LOT-CRED-01',
                    'fecha_vencimiento'       => now()->addMonths(18)->toDateString(),
                ],
            ],
        ]);

        $this->assertEquals('credito', $compra->condicion_pago);
        $this->assertEquals(400.00, $compra->total);
        $this->assertEquals(400.00, $compra->saldo_pendiente);
        $this->assertEquals('pendiente', $compra->estado_pago);

        // Aparece en el listado de Cuentas por Pagar
        $response = $this->actingAs($this->admin)->get(route('cuentas-por-pagar.index'));
        $response->assertSee('FACT-CRED-01');
        $response->assertSee('Droguería Central');
    }

    /**
     * Flujo 2: Factura / Gasto directo crea CxP con CERO lotes y CERO Kardex
     */
    public function test_flujo_2_factura_directa_crea_cxp_sin_lotes_ni_kardex(): void
    {
        $payload = [
            'proveedor_id'           => $this->proveedor->id,
            'numero_comprobante'     => 'FACT-LUZ-001',
            'concepto'               => 'Servicio de Energía Eléctrica',
            'fecha'                  => now()->toDateString(),
            'total'                  => 1250.00,
            'dias_credito'           => 15,
            'fecha_vencimiento_pago' => now()->addDays(15)->toDateString(),
        ];

        $response = $this->actingAs($this->admin)->post(route('cuentas-por-pagar.directa.store'), $payload);
        $response->assertRedirect();

        $compraGasto = Compra::where('numero_comprobante', 'like', 'FACT-LUZ-001%')->first();
        $this->assertNotNull($compraGasto);
        $this->assertEquals(1250.00, $compraGasto->total);
        $this->assertEquals(1250.00, $compraGasto->saldo_pendiente);
        $this->assertEquals('pendiente', $compraGasto->estado_pago);

        // INVARIANTE CRÍTICO: Cero lotes y cero Kardex
        $this->assertEquals(0, $compraGasto->detalles()->count(), 'Gasto directo no debe tener detalles de producto');
        $this->assertEquals(0, Lote::where('compra_id', $compraGasto->id)->count(), 'Gasto directo no debe generar lotes');
        $this->assertEquals(0, MovimientoInventario::where('compra_id', $compraGasto->id)->count(), 'Gasto directo no debe generar Kardex');
    }

    /**
     * Flujo 3: Abono parcial actualiza saldo pendiente y estado a 'parcial'
     */
    public function test_flujo_3_abono_parcial_actualiza_saldo_y_estado_parcial(): void
    {
        $compra = $this->compraService->registrarCompra([
            'proveedor_id'           => $this->proveedor->id,
            'tipo_comprobante'       => 'Factura',
            'numero_comprobante'     => 'FACT-ABONO-01',
            'fecha_emision'          => now()->toDateString(),
            'condicion_pago'         => 'credito',
            'dias_credito'           => 30,
            'productos'              => [
                [
                    'producto_id'             => $this->producto->id,
                    'cantidad_presentaciones' => 40,
                    'precio_unitario'         => 10.00,
                    'numero_lote'             => 'LOT-01',
                    'fecha_vencimiento'       => now()->addMonths(12)->toDateString(),
                ],
            ],
        ]);

        // Total 400. Abonar 150 -> Saldo debe quedar en 250
        $pago = $this->cuentaPorPagarService->registrarAbono([
            'compra_id'         => $compra->id,
            'monto'             => 150.00,
            'metodo_pago'       => 'transferencia',
            'banco'             => 'BAC Credomatic',
            'numero_referencia' => 'TR-BAC-99881',
            'observaciones'     => 'Primer abono quincenal',
        ], $this->admin->id);

        $compra->refresh();
        $this->assertEquals(250.00, $compra->saldo_pendiente);
        $this->assertEquals('parcial', $compra->estado_pago);
        $this->assertStringStartsWith('PAG-', $pago->numero_pago);
        $this->assertCount(1, $compra->pagos);
    }

    /**
     * Flujo 4: Abono total liquida la cuenta y actualiza estado a 'pagado'
     */
    public function test_flujo_4_abono_total_liquida_cuenta_a_pagado(): void
    {
        $compra = $this->compraService->registrarCompra([
            'proveedor_id'           => $this->proveedor->id,
            'tipo_comprobante'       => 'Factura',
            'numero_comprobante'     => 'FACT-LIQUIDA-01',
            'fecha_emision'          => now()->toDateString(),
            'condicion_pago'         => 'credito',
            'dias_credito'           => 30,
            'productos'              => [
                [
                    'producto_id'             => $this->producto->id,
                    'cantidad_presentaciones' => 10,
                    'precio_unitario'         => 10.00,
                    'numero_lote'             => 'LOT-02',
                    'fecha_vencimiento'       => now()->addMonths(12)->toDateString(),
                ],
            ],
        ]);

        // Total 100. Abonar los 100 completos
        $pago = $this->cuentaPorPagarService->registrarAbono([
            'compra_id'         => $compra->id,
            'monto'             => 100.00,
            'metodo_pago'       => 'transferencia',
            'banco'             => 'Banpro Grupo Promerica',
            'numero_referencia' => 'TR-BANPRO-1122',
        ], $this->admin->id);

        $compra->refresh();
        $this->assertEquals(0.00, $compra->saldo_pendiente);
        $this->assertEquals('pagado', $compra->estado_pago);
    }

    /**
     * Test: Abono en efectivo con caja abierta crea egreso atómico en SesionCaja
     */
    public function test_abono_efectivo_con_caja_abierta_crea_egreso_en_sesion_caja(): void
    {
        $compra = $this->compraService->registrarCompra([
            'proveedor_id'           => $this->proveedor->id,
            'tipo_comprobante'       => 'Factura',
            'numero_comprobante'     => 'FACT-CAJA-01',
            'fecha_emision'          => now()->toDateString(),
            'condicion_pago'         => 'credito',
            'dias_credito'           => 30,
            'productos'              => [
                [
                    'producto_id'             => $this->producto->id,
                    'cantidad_presentaciones' => 20,
                    'precio_unitario'         => 10.00,
                    'numero_lote'             => 'LOT-CAJA-01',
                    'fecha_vencimiento'       => now()->addMonths(12)->toDateString(),
                ],
            ],
        ]);

        $response = $this->actingAs($this->admin)->post(route('cuentas-por-pagar.abonos.store', $compra), [
            'monto'             => 200.00,
            'metodo_pago'       => 'efectivo',
            'registrar_en_caja' => 1,
            'observaciones'     => 'Pago en efectivo directo al repartidor',
        ]);

        $response->assertSessionHas('success');

        $pago = PagoCuentaPorPagar::where('compra_id', $compra->id)->first();
        $this->assertNotNull($pago);
        $this->assertEquals($this->sesionCaja->id, $pago->sesion_caja_id);

        // Movimiento de egreso registrado en caja
        $movCaja = MovimientoCaja::where('sesion_caja_id', $this->sesionCaja->id)
            ->where('tipo', 'egreso')
            ->first();

        $this->assertNotNull($movCaja);
        $this->assertEquals(200.00, $movCaja->monto);
        $this->assertEquals($pago->numero_pago, $movCaja->comprobante_referencia);
    }

    /**
     * Test: Abono superior al saldo pendiente es bloqueado
     */
    public function test_abono_superior_al_saldo_es_bloqueado(): void
    {
        $compra = $this->compraService->registrarCompra([
            'proveedor_id'           => $this->proveedor->id,
            'tipo_comprobante'       => 'Factura',
            'numero_comprobante'     => 'FACT-OVERPAY-01',
            'fecha_emision'          => now()->toDateString(),
            'condicion_pago'         => 'credito',
            'dias_credito'           => 30,
            'productos'              => [
                [
                    'producto_id'             => $this->producto->id,
                    'cantidad_presentaciones' => 10,
                    'precio_unitario'         => 10.00,
                    'numero_lote'             => 'LOT-01',
                    'fecha_vencimiento'       => now()->addMonths(12)->toDateString(),
                ],
            ],
        ]);

        // Total es 100. Intentar abonar 150
        $response = $this->actingAs($this->admin)->post(route('cuentas-por-pagar.abonos.store', $compra), [
            'monto'             => 150.00,
            'metodo_pago'       => 'transferencia',
        ]);

        $response->assertSessionHasErrors('monto');
        $this->assertEquals(100.00, $compra->fresh()->saldo_pendiente);
    }

    /**
     * Test: Cuentas vencidas se calculan y filtran correctamente
     */
    public function test_cuentas_vencidas_calcula_mora_correctamente(): void
    {
        // Factura vencida hace 5 días
        $compraVencida = Compra::create([
            'proveedor_id'           => $this->proveedor->id,
            'user_id'                => $this->admin->id,
            'numero_comprobante'     => 'FACT-MORA-01',
            'subtotal'               => 500.00,
            'impuesto'               => 0,
            'total'                  => 500.00,
            'condicion_pago'         => 'credito',
            'dias_credito'           => 30,
            'fecha_vencimiento_pago' => now()->subDays(5)->toDateString(),
            'saldo_pendiente'        => 500.00,
            'estado_pago'            => 'pendiente',
            'estado'                 => 'recibida',
            'fecha'                  => now()->subDays(35),
        ]);

        $metricas = $this->cuentaPorPagarService->getMetricas();
        $this->assertGreaterThanOrEqual(500.00, $metricas['deuda_vencida']);

        $response = $this->actingAs($this->admin)->get(route('cuentas-por-pagar.index', ['estado_pago' => 'vencidas']));
        $response->assertStatus(200);
        $response->assertSee('FACT-MORA-01');
    }

    /**
     * Test: Comando farma:verificar-cxp ejecuta exitosamente
     */
    public function test_comando_verificar_cxp_ejecuta_correctamente(): void
    {
        $this->artisan('farma:verificar-cxp')
            ->expectsOutputToContain('AUDITORÍA DE INTEGRIDAD DE CUENTAS POR PAGAR')
            ->expectsOutputToContain('0 INCONSISTENCIAS DETECTADAS')
            ->assertExitCode(0);
    }
}
