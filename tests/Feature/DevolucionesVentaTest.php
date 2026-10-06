<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Caja;
use App\Models\SesionCaja;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Lote;
use App\Models\Categoria;
use App\Models\Laboratorio;
use App\Models\Receta;
use App\Models\RecetaDetalle;
use App\Models\MovimientoInventario;
use App\Models\RegistroVentaControlado;
use App\Models\MovimientoCaja;
use App\Models\DevolucionVenta;
use App\Services\VentaService;
use App\Services\DevolucionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DevolucionesVentaTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Caja $caja;
    protected SesionCaja $sesion;
    protected Producto $productoNormal;
    protected Producto $productoControlado;
    protected Lote $loteNormal;
    protected Lote $loteControlado;
    protected Cliente $cliente;

    protected function setUp(): void
    {
        parent::setUp();

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        Permission::create(['name' => 'ver ventas']);
        Permission::create(['name' => 'realizar ventas']);
        Permission::create(['name' => 'anular ventas']);

        $roleAdmin = Role::create(['name' => 'administrador']);
        $roleAdmin->givePermissionTo(['ver ventas', 'realizar ventas', 'anular ventas']);

        $this->admin = User::factory()->create();
        $this->admin->assignRole($roleAdmin);

        $this->caja = Caja::create([
            'codigo' => 'CAJA-DEV-01',
            'nombre' => 'Caja Devoluciones',
            'activo' => true,
        ]);

        $this->sesion = SesionCaja::create([
            'caja_id'                 => $this->caja->id,
            'user_id'                 => $this->admin->id,
            'monto_inicial'           => 1000.00,
            'fecha_apertura'          => now(),
            'estado'                  => 'abierta',
            'monto_esperado_efectivo' => 1000.00,
        ]);

        $cat = Categoria::create(['nombre' => 'Analgésicos', 'activo' => true]);
        $lab = Laboratorio::create(['nombre' => 'Bayer', 'activo' => true]);

        $this->cliente = Cliente::create([
            'nombre'    => 'María López',
            'documento' => '001-200590-0002B',
            'telefono'  => '8999-9999',
            'activo'    => true,
        ]);

        $this->productoNormal = Producto::create([
            'codigo_barra'      => '775111222333',
            'nombre'            => 'Ibuprofeno 400mg',
            'principio_activo'  => 'Ibuprofeno',
            'categoria_id'      => $cat->id,
            'laboratorio_id'    => $lab->id,
            'precio_compra'     => 10.00,
            'precio_venta'      => 20.00,
            'requiere_receta'   => false,
            'es_controlado'     => false,
            'activo'            => true,
        ]);

        $this->loteNormal = Lote::create([
            'producto_id'       => $this->productoNormal->id,
            'numero_lote'       => 'LOT-IBU-01',
            'fecha_vencimiento' => now()->addMonths(12),
            'stock_inicial'     => 100,
            'stock_actual'      => 100,
            'precio_compra'     => 10.00,
            'activo'            => true,
        ]);

        $this->productoControlado = Producto::create([
            'codigo_barra'      => '775999888777',
            'nombre'            => 'Diazepam 10mg',
            'principio_activo'  => 'Diazepam',
            'categoria_id'      => $cat->id,
            'laboratorio_id'    => $lab->id,
            'precio_compra'     => 40.00,
            'precio_venta'      => 60.00,
            'requiere_receta'   => true,
            'es_controlado'     => true,
            'activo'            => true,
        ]);

        $this->loteControlado = Lote::create([
            'producto_id'       => $this->productoControlado->id,
            'numero_lote'       => 'LOT-DIAZ-01',
            'fecha_vencimiento' => now()->addMonths(18),
            'stock_inicial'     => 50,
            'stock_actual'      => 50,
            'precio_compra'     => 40.00,
            'activo'            => true,
        ]);
    }

    public function test_devolucion_parcial_reingresa_stock_kardex_y_reembolso_efectivo()
    {
        $ventaService = app(VentaService::class);

        $venta = $ventaService->procesarVenta([
            'cliente_id'       => $this->cliente->id,
            'tipo_comprobante' => 'ticket',
            'metodo_pago'      => 'efectivo',
            'monto_recibido'   => 100.00,
            'receta_modalidad' => 'sin_receta',
            'productos'        => [
                [
                    'producto_id' => $this->productoNormal->id,
                    'lote_id'     => $this->loteNormal->id,
                    'cantidad'    => 4, // 4 x 20 = 80
                ]
            ]
        ]);

        $this->loteNormal->refresh();
        $this->assertEquals(96, $this->loteNormal->stock_actual);

        $detalleVenta = $venta->detalles->first();

        // Procesar devolución de 2 unidades
        $devolucionData = [
            'venta_id'          => $venta->id,
            'motivo'            => 'cliente_desiste',
            'observaciones'     => 'Cliente compró de más',
            'metodo_reembolso'  => 'efectivo',
            'items'             => [
                [
                    'detalle_venta_id'  => $detalleVenta->id,
                    'cantidad'          => 2,
                    'reingresa_a_stock' => 1,
                    'estado_producto'   => 'buen_estado',
                ]
            ]
        ];

        $response = $this->actingAs($this->admin)->post(route('devoluciones.store'), $devolucionData);
        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Stock reingresado
        $this->loteNormal->refresh();
        $this->assertEquals(98, $this->loteNormal->stock_actual);

        // Kardex
        $mov = MovimientoInventario::where('origen', 'devolucion_venta')->latest()->first();
        $this->assertNotNull($mov);
        $this->assertEquals('entrada', $mov->tipo);
        $this->assertEquals('anulacion_venta', $mov->subtipo);
        $this->assertEquals(2, $mov->cantidad);
        $this->assertEquals(96, $mov->stock_anterior);
        $this->assertEquals(98, $mov->stock_posterior);

        // Caja
        $this->sesion->refresh();
        $egreso = MovimientoCaja::where('sesion_caja_id', $this->sesion->id)
            ->where('tipo', 'egreso')
            ->first();
        $this->assertNotNull($egreso);
        $this->assertEquals(40.00, (float)$egreso->monto); // 2 x 20
        $this->assertEquals(1040.00, (float)$this->sesion->monto_esperado_efectivo); // 1000 + 80 - 40
    }

    public function test_devolucion_danado_no_reingresa_stock_y_registra_merma()
    {
        $ventaService = app(VentaService::class);

        $venta = $ventaService->procesarVenta([
            'cliente_id'       => $this->cliente->id,
            'tipo_comprobante' => 'ticket',
            'metodo_pago'      => 'efectivo',
            'monto_recibido'   => 100.00,
            'receta_modalidad' => 'sin_receta',
            'productos'        => [
                [
                    'producto_id' => $this->productoNormal->id,
                    'lote_id'     => $this->loteNormal->id,
                    'cantidad'    => 2,
                ]
            ]
        ]);

        $this->loteNormal->refresh();
        $this->assertEquals(98, $this->loteNormal->stock_actual);

        $detalleVenta = $venta->detalles->first();

        // Devolución por empaque dañado (no reingresa a stock vendible)
        $devolucionData = [
            'venta_id'          => $venta->id,
            'motivo'            => 'producto_defectuoso',
            'observaciones'     => 'Blister roto por cliente en transporte',
            'metodo_reembolso'  => 'saldo_favor',
            'items'             => [
                [
                    'detalle_venta_id'  => $detalleVenta->id,
                    'cantidad'          => 1,
                    'reingresa_a_stock' => 0,
                    'estado_producto'   => 'danado',
                ]
            ]
        ];

        $response = $this->actingAs($this->admin)->post(route('devoluciones.store'), $devolucionData);
        $response->assertRedirect();

        // Stock NO sube
        $this->loteNormal->refresh();
        $this->assertEquals(98, $this->loteNormal->stock_actual);

        // Kardex registra merma_danio
        $mov = MovimientoInventario::where('subtipo', 'merma_danio')->latest()->first();
        $this->assertNotNull($mov);
        $this->assertEquals(0, $mov->cantidad);
    }

    public function test_devolucion_con_descuento_calcula_precio_efectivo_exacto()
    {
        $ventaService = app(VentaService::class);

        // Venta con 50% de descuento en la línea (2 unidades x 20 = 40, descuento 20 => subtotal 20, precio efectivo = 10 c/u)
        $venta = $ventaService->procesarVenta([
            'cliente_id'       => $this->cliente->id,
            'tipo_comprobante' => 'ticket',
            'metodo_pago'      => 'efectivo',
            'monto_recibido'   => 50.00,
            'receta_modalidad' => 'sin_receta',
            'productos'        => [
                [
                    'producto_id'          => $this->productoNormal->id,
                    'lote_id'              => $this->loteNormal->id,
                    'cantidad'             => 2,
                    'tipo_descuento'       => 'porcentaje',
                    'porcentaje_descuento' => 50,
                ]
            ]
        ]);

        $detalleVenta = $venta->detalles->first();
        $this->assertEquals(20.00, (float)$detalleVenta->subtotal);

        // Devolver 1 unidad
        $devolucionData = [
            'venta_id'          => $venta->id,
            'motivo'            => 'cliente_desiste',
            'metodo_reembolso'  => 'efectivo',
            'items'             => [
                [
                    'detalle_venta_id'  => $detalleVenta->id,
                    'cantidad'          => 1,
                    'reingresa_a_stock' => 1,
                    'estado_producto'   => 'buen_estado',
                ]
            ]
        ];

        $devolucionService = app(DevolucionService::class);
        $devolucion = $devolucionService->procesarDevolucion($devolucionData);

        // El reembolso debe ser exactamente 10.00 (el precio neto efectivamente pagado por unidad)
        $this->assertEquals(10.00, (float)$devolucion->monto_total);
    }

    public function test_devolucion_controlado_asienta_libro_minsa_y_restituye_receta()
    {
        $receta = Receta::create([
            'numero_receta'     => 'RX-DEV-CTRL-01',
            'paciente_nombre'   => 'María López',
            'cliente_id'        => $this->cliente->id,
            'medico_nombre'     => 'Dr. Alejandro Ruiz',
            'medico_colegiatura'=> 'MINSA-3322',
            'fecha_emision'     => now(),
            'fecha_vencimiento' => now()->addDays(30),
            'estado'            => 'pendiente',
        ]);

        $recetaDetalle = RecetaDetalle::create([
            'receta_id'           => $receta->id,
            'producto_id'         => $this->productoControlado->id,
            'cantidad_recetada'   => 10,
            'cantidad_dispensada' => 0,
            'dosis'               => '1 tableta noche',
        ]);

        $ventaService = app(VentaService::class);

        $venta = $ventaService->procesarVenta([
            'cliente_id'       => $this->cliente->id,
            'tipo_comprobante' => 'boleta',
            'metodo_pago'      => 'efectivo',
            'monto_recibido'   => 500.00,
            'receta_modalidad' => 'vinculada',
            'receta_id'        => $receta->id,
            'recetas'          => [$receta->id],
            'productos'        => [
                [
                    'producto_id'       => $this->productoControlado->id,
                    'lote_id'           => $this->loteControlado->id,
                    'receta_detalle_id' => $recetaDetalle->id,
                    'cantidad'          => 5,
                ]
            ]
        ]);

        $recetaDetalle->refresh();
        $this->assertEquals(5, $recetaDetalle->cantidad_dispensada);

        $detalleVenta = $venta->detalles->first();

        // Devolver 2 unidades reingresando a stock
        $devolucionService = app(DevolucionService::class);
        $devolucion = $devolucionService->procesarDevolucion([
            'venta_id'          => $venta->id,
            'motivo'            => 'cliente_desiste',
            'metodo_reembolso'  => 'efectivo',
            'items'             => [
                [
                    'detalle_venta_id'  => $detalleVenta->id,
                    'cantidad'          => 2,
                    'reingresa_a_stock' => 1,
                    'estado_producto'   => 'buen_estado',
                ]
            ]
        ]);

        // Receta revertida en 2 unidades (quedan 3 dispensadas)
        $recetaDetalle->refresh();
        $this->assertEquals(3, $recetaDetalle->cantidad_dispensada);

        // Asiento MINSA de devolución
        $asientoMinsa = RegistroVentaControlado::where('devolucion_id', $devolucion->id)
            ->where('tipo_movimiento', RegistroVentaControlado::TIPO_DEVOLUCION_STOCK)
            ->first();
        $this->assertNotNull($asientoMinsa);
        $this->assertEquals(2, $asientoMinsa->cantidad);
    }

    public function test_bloqueo_devolucion_que_excede_saldo_disponible()
    {
        $ventaService = app(VentaService::class);

        $venta = $ventaService->procesarVenta([
            'cliente_id'       => $this->cliente->id,
            'tipo_comprobante' => 'ticket',
            'metodo_pago'      => 'efectivo',
            'monto_recibido'   => 50.00,
            'receta_modalidad' => 'sin_receta',
            'productos'        => [
                [
                    'producto_id' => $this->productoNormal->id,
                    'lote_id'     => $this->loteNormal->id,
                    'cantidad'    => 2,
                ]
            ]
        ]);

        $detalleVenta = $venta->detalles->first();

        // Intentar devolver 3 unidades cuando solo se compraron 2
        $this->expectException(\Exception::class);

        $devolucionService = app(DevolucionService::class);
        $devolucionService->procesarDevolucion([
            'venta_id'          => $venta->id,
            'motivo'            => 'cliente_desiste',
            'metodo_reembolso'  => 'efectivo',
            'items'             => [
                [
                    'detalle_venta_id'  => $detalleVenta->id,
                    'cantidad'          => 3,
                    'reingresa_a_stock' => 1,
                    'estado_producto'   => 'buen_estado',
                ]
            ]
        ]);
    }

    public function test_comando_verificar_devoluciones_ventas_ejecuta_correctamente()
    {
        $ventaService = app(VentaService::class);
        $devolucionService = app(DevolucionService::class);

        $venta = $ventaService->procesarVenta([
            'cliente_id'       => $this->cliente->id,
            'tipo_comprobante' => 'ticket',
            'metodo_pago'      => 'efectivo',
            'monto_recibido'   => 50.00,
            'receta_modalidad' => 'sin_receta',
            'productos'        => [
                [
                    'producto_id' => $this->productoNormal->id,
                    'lote_id'     => $this->loteNormal->id,
                    'cantidad'    => 2,
                ]
            ]
        ]);

        $detalleVenta = $venta->detalles->first();

        $devolucionService->procesarDevolucion([
            'venta_id'          => $venta->id,
            'motivo'            => 'cliente_desiste',
            'metodo_reembolso'  => 'efectivo',
            'items'             => [
                [
                    'detalle_venta_id'  => $detalleVenta->id,
                    'cantidad'          => 1,
                    'reingresa_a_stock' => 1,
                    'estado_producto'   => 'buen_estado',
                ]
            ]
        ]);

        $this->artisan('farma:verificar-devoluciones-ventas')
            ->expectsOutputToContain('100% Integridad Verificada')
            ->assertExitCode(0);
    }
}
