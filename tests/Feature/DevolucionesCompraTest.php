<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Proveedor;
use App\Models\Categoria;
use App\Models\Laboratorio;
use App\Models\Producto;
use App\Models\Compra;
use App\Models\Lote;
use App\Models\DevolucionCompra;
use App\Models\DetalleDevolucionCompra;
use App\Models\MovimientoInventario;
use App\Models\RegistroVentaControlado;
use App\Services\CompraService;
use App\Services\DevolucionCompraService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DevolucionesCompraTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $usuarioSinPermisos;
    protected Proveedor $proveedor;
    protected Categoria $categoria;
    protected Laboratorio $laboratorio;
    protected Producto $productoNormal;
    protected Producto $productoControlado;
    protected CompraService $compraService;
    protected DevolucionCompraService $devolucionService;

    protected function setUp(): void
    {
        parent::setUp();

        $permisos = [
            'ver compras',
            'registrar compras',
            'anular compras',
            'ver movimientos inventario',
        ];

        foreach ($permisos as $permiso) {
            Permission::findOrCreate($permiso, 'web');
        }

        $roleAdmin = Role::findOrCreate('Administrador', 'web');
        $roleAdmin->syncPermissions($permisos);

        $this->admin = User::factory()->create();
        $this->admin->assignRole($roleAdmin);

        $this->usuarioSinPermisos = User::factory()->create();

        $this->proveedor = Proveedor::factory()->create(['nombre' => 'Distribuidora Ramos', 'activo' => true]);
        $this->categoria = Categoria::factory()->create(['nombre' => 'Analgésicos']);
        $this->laboratorio = Laboratorio::factory()->create(['nombre' => 'Laboratorios Ramos']);

        $this->productoNormal = Producto::factory()->create([
            'categoria_id'   => $this->categoria->id,
            'laboratorio_id' => $this->laboratorio->id,
            'nombre'         => 'Paracetamol 500mg',
            'precio_compra'  => 5.00,
            'precio_venta'   => 10.00,
            'tipo_control'   => Producto::TIPO_VENTA_LIBRE,
            'requiere_receta'=> false,
            'activo'         => true,
        ]);

        $this->productoControlado = Producto::factory()->controlado()->create([
            'categoria_id'   => $this->categoria->id,
            'laboratorio_id' => $this->laboratorio->id,
            'nombre'         => 'Morfina 10mg Inyectable',
            'precio_compra'  => 50.00,
            'precio_venta'   => 120.00,
            'activo'         => true,
        ]);

        $this->compraService = app(CompraService::class);
        $this->devolucionService = app(DevolucionCompraService::class);
    }

    public function test_devoluciones_requiere_permiso(): void
    {
        $responseGuest = $this->get(route('compras.devoluciones.index'));
        $responseGuest->assertRedirect(route('login'));

        $responseSinPermiso = $this->actingAs($this->usuarioSinPermisos)->get(route('compras.devoluciones.index'));
        $responseSinPermiso->assertStatus(403);

        $responseAdmin = $this->actingAs($this->admin)->get(route('compras.devoluciones.index'));
        $responseAdmin->assertStatus(200);
        $responseAdmin->assertViewIs('compras.devoluciones.index');
    }

    public function test_flujo_1_devolucion_parcial_compra_descuenta_lote_y_kardex(): void
    {
        // Registrar compra inicial de 50 unidades @ C$ 5.00
        $compra = $this->compraService->registrarCompra([
            'proveedor_id'       => $this->proveedor->id,
            'numero_comprobante' => 'FAC-DEV-01',
            'fecha'              => now()->toDateString(),
            'condicion_pago'     => 'contado',
            'productos'          => [
                [
                    'producto_id'             => $this->productoNormal->id,
                    'cantidad_presentaciones' => 50,
                    'precio_unitario'         => 5.00,
                    'numero_lote'             => 'LOT-DEV-01',
                    'fecha_vencimiento'       => now()->addMonths(18)->toDateString(),
                ],
            ],
        ]);

        $lote = Lote::where('numero_lote', 'LOT-DEV-01')->first();
        $this->assertEquals(50, $lote->stock_actual);

        // Devolver 15 unidades
        $payload = [
            'proveedor_id' => $this->proveedor->id,
            'compra_id'    => $compra->id,
            'motivo'       => 'Empaques dañados en transporte por el proveedor',
            'items'        => [
                [
                    'lote_id'         => $lote->id,
                    'cantidad'        => 15,
                    'precio_unitario' => 5.00,
                    'motivo_detalle'  => 'Cajas rotas',
                ],
            ],
        ];

        $response = $this->actingAs($this->admin)->post(route('compras.devoluciones.store'), $payload);
        $response->assertRedirect();

        $devolucion = DevolucionCompra::first();
        $this->assertNotNull($devolucion);
        $this->assertEquals(75.00, $devolucion->total_devolucion);
        $this->assertEquals('confirmada', $devolucion->estado);

        // Lote descontado a 35
        $lote->refresh();
        $this->assertEquals(35, $lote->stock_actual);

        // Kardex registrado con salida
        $movKardex = MovimientoInventario::where('origen', 'devolucion_compra')->first();
        $this->assertNotNull($movKardex);
        $this->assertEquals('salida', $movKardex->tipo);
        $this->assertEquals('ajuste_manual', $movKardex->subtipo);
        $this->assertEquals('devolucion_compra', $movKardex->origen);
        $this->assertEquals(-15, $movKardex->cantidad);
        $this->assertEquals(50, $movKardex->stock_anterior);
        $this->assertEquals(35, $movKardex->stock_posterior);
    }

    public function test_flujo_2_devolucion_producto_controlado_genera_asiento_libro_minsa(): void
    {
        $compra = $this->compraService->registrarCompra([
            'proveedor_id'       => $this->proveedor->id,
            'numero_comprobante' => 'FAC-CTRL-99',
            'fecha'              => now()->toDateString(),
            'condicion_pago'     => 'contado',
            'productos'          => [
                [
                    'producto_id'             => $this->productoControlado->id,
                    'cantidad_presentaciones' => 20,
                    'precio_unitario'         => 50.00,
                    'numero_lote'             => 'LOT-MORF-99',
                    'fecha_vencimiento'       => now()->addMonths(24)->toDateString(),
                ],
            ],
        ]);

        $lote = Lote::where('numero_lote', 'LOT-MORF-99')->first();

        $payload = [
            'proveedor_id' => $this->proveedor->id,
            'compra_id'    => $compra->id,
            'motivo'       => 'Devolución de ampollas con lote observado por MINSA',
            'items'        => [
                [
                    'lote_id'         => $lote->id,
                    'cantidad'        => 8,
                    'precio_unitario' => 50.00,
                ],
            ],
        ];

        $this->actingAs($this->admin)->post(route('compras.devoluciones.store'), $payload);

        $asientoMinsa = RegistroVentaControlado::where('lote_id', $lote->id)
            ->where('tipo_movimiento', RegistroVentaControlado::TIPO_AJUSTE_EGRESO)
            ->first();

        $this->assertNotNull($asientoMinsa);
        $this->assertEquals(8, $asientoMinsa->cantidad);
        $this->assertStringContainsString('DEV-COMP-', $asientoMinsa->motivo_omision);
    }

    public function test_flujo_3_bloqueo_cantidad_superior_a_disponible(): void
    {
        $this->compraService->registrarCompra([
            'proveedor_id'       => $this->proveedor->id,
            'numero_comprobante' => 'FAC-EXCESO',
            'fecha'              => now()->toDateString(),
            'condicion_pago'     => 'contado',
            'productos'          => [
                [
                    'producto_id'             => $this->productoNormal->id,
                    'cantidad_presentaciones' => 10,
                    'precio_unitario'         => 5.00,
                    'numero_lote'             => 'LOT-EXCESO',
                    'fecha_vencimiento'       => now()->addMonths(12)->toDateString(),
                ],
            ],
        ]);

        $lote = Lote::where('numero_lote', 'LOT-EXCESO')->first();

        // Intentar devolver 15 cuando solo hay 10
        $payload = [
            'proveedor_id' => $this->proveedor->id,
            'motivo'       => 'Intento de devolución excesiva',
            'items'        => [
                [
                    'lote_id'         => $lote->id,
                    'cantidad'        => 15,
                    'precio_unitario' => 5.00,
                ],
            ],
        ];

        $response = $this->actingAs($this->admin)->post(route('compras.devoluciones.store'), $payload);
        $response->assertSessionHas('error');

        // Stock del lote no debe cambiar
        $this->assertEquals(10, $lote->fresh()->stock_actual);
        $this->assertEquals(0, DevolucionCompra::count());
    }

    public function test_flujo_4_anulacion_devolucion_revierte_stock_y_asienta_kardex(): void
    {
        $compra = $this->compraService->registrarCompra([
            'proveedor_id'       => $this->proveedor->id,
            'numero_comprobante' => 'FAC-ANUL-DEV',
            'fecha'              => now()->toDateString(),
            'condicion_pago'     => 'contado',
            'productos'          => [
                [
                    'producto_id'             => $this->productoNormal->id,
                    'cantidad_presentaciones' => 30,
                    'precio_unitario'         => 5.00,
                    'numero_lote'             => 'LOT-ANUL-DEV',
                    'fecha_vencimiento'       => now()->addMonths(12)->toDateString(),
                ],
            ],
        ]);

        $lote = Lote::where('numero_lote', 'LOT-ANUL-DEV')->first();

        $devolucion = $this->devolucionService->registrarDevolucion([
            'proveedor_id' => $this->proveedor->id,
            'compra_id'    => $compra->id,
            'motivo'       => 'Devolución de prueba para posterior anulación',
            'items'        => [
                [
                    'lote_id'         => $lote->id,
                    'cantidad'        => 10,
                    'precio_unitario' => 5.00,
                ],
            ],
        ], $this->admin->id);

        $this->assertEquals(20, $lote->fresh()->stock_actual);

        // Anular la devolución
        $response = $this->actingAs($this->admin)->post(route('compras.devoluciones.anular', $devolucion), [
            'motivo' => 'Proveedor no aceptó la devolución por falta de sello de recepción',
        ]);

        $response->assertRedirect();
        $devolucion->refresh();
        $this->assertEquals('rechazada', $devolucion->estado);

        // Stock restituido a 30
        $this->assertEquals(30, $lote->fresh()->stock_actual);

        // Contra-asiento de entrada en Kardex
        $contraMov = MovimientoInventario::where('origen', 'anulacion_devolucion_compra')->first();
        $this->assertNotNull($contraMov);
        $this->assertEquals('entrada', $contraMov->tipo);
        $this->assertEquals(10, $contraMov->cantidad);
        $this->assertEquals(20, $contraMov->stock_anterior);
        $this->assertEquals(30, $contraMov->stock_posterior);
    }

    public function test_flujo_5_devolucion_compra_credito_ajusta_saldo_pendiente(): void
    {
        $compraCredito = $this->compraService->registrarCompra([
            'proveedor_id'           => $this->proveedor->id,
            'numero_comprobante'     => 'FAC-CRED-DEV',
            'fecha'                  => now()->toDateString(),
            'condicion_pago'         => 'credito',
            'dias_credito'           => 30,
            'fecha_vencimiento_pago' => now()->addDays(30)->toDateString(),
            'productos'              => [
                [
                    'producto_id'             => $this->productoNormal->id,
                    'cantidad_presentaciones' => 40,
                    'precio_unitario'         => 10.00, // Total C$ 400.00
                    'numero_lote'             => 'LOT-CRED-DEV',
                    'fecha_vencimiento'       => now()->addMonths(12)->toDateString(),
                ],
            ],
        ]);

        $this->assertEquals(400.00, $compraCredito->saldo_pendiente);

        $lote = Lote::where('numero_lote', 'LOT-CRED-DEV')->first();

        // Devolver 10 unidades @ C$ 10.00 = C$ 100.00
        $this->devolucionService->registrarDevolucion([
            'proveedor_id' => $this->proveedor->id,
            'compra_id'    => $compraCredito->id,
            'motivo'       => 'Reclamo de calidad a cuenta de crédito',
            'items'        => [
                [
                    'lote_id'         => $lote->id,
                    'cantidad'        => 10,
                    'precio_unitario' => 10.00,
                ],
            ],
        ], $this->admin->id);

        $compraCredito->refresh();
        // Saldo pendiente reducido de 400 a 300
        $this->assertEquals(300.00, $compraCredito->saldo_pendiente);
        $this->assertEquals('parcial', $compraCredito->estado_pago);
    }

    public function test_comando_verificar_devoluciones_proveedor_ejecuta_correctamente(): void
    {
        $this->artisan('farma:verificar-devoluciones-proveedor')
            ->expectsOutputToContain('AUDITORÍA DE INTEGRIDAD DE DEVOLUCIONES A PROVEEDOR')
            ->expectsOutputToContain('0 INCONSISTENCIAS DETECTADAS')
            ->assertExitCode(0);
    }
}
