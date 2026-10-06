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
use App\Models\PresentacionProducto;
use App\Models\Receta;
use App\Models\RecetaDetalle;
use App\Models\MovimientoInventario;
use App\Models\RegistroVentaControlado;
use App\Models\DevolucionVenta;
use App\Services\VentaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class VentaCicloVidaTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $cajero;
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
        Permission::create(['name' => 'ver ventas propias']);
        Permission::create(['name' => 'realizar ventas']);
        Permission::create(['name' => 'anular ventas']);

        $roleAdmin = Role::create(['name' => 'administrador']);
        $roleAdmin->givePermissionTo(['ver ventas', 'realizar ventas', 'anular ventas']);

        $roleCajero = Role::create(['name' => 'cajero']);
        $roleCajero->givePermissionTo(['ver ventas propias', 'realizar ventas', 'anular ventas']);

        $this->admin = User::factory()->create();
        $this->admin->assignRole($roleAdmin);

        $this->cajero = User::factory()->create();
        $this->cajero->assignRole($roleCajero);

        $this->caja = Caja::create([
            'codigo' => 'CAJA-01',
            'nombre' => 'Caja Principal',
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
            'nombre'    => 'Juan Pérez',
            'documento' => '001-010190-0001A',
            'telefono'  => '8888-8888',
            'activo'    => true,
        ]);

        $this->productoNormal = Producto::create([
            'codigo_barra'      => '775000111222',
            'nombre'            => 'Paracetamol 500mg',
            'principio_activo'  => 'Paracetamol',
            'categoria_id'      => $cat->id,
            'laboratorio_id'    => $lab->id,
            'precio_compra'     => 10.00,
            'precio_venta'      => 15.00,
            'requiere_receta'   => false,
            'es_controlado'     => false,
            'activo'            => true,
        ]);

        $this->loteNormal = Lote::create([
            'producto_id'       => $this->productoNormal->id,
            'numero_lote'       => 'LOT-NORM-01',
            'fecha_vencimiento' => now()->addMonths(12),
            'stock_inicial'     => 100,
            'stock_actual'      => 100,
            'precio_compra'     => 10.00,
            'activo'            => true,
        ]);

        $this->productoControlado = Producto::create([
            'codigo_barra'      => '775000999888',
            'nombre'            => 'Clonazepam 2mg',
            'principio_activo'  => 'Clonazepam',
            'categoria_id'      => $cat->id,
            'laboratorio_id'    => $lab->id,
            'precio_compra'     => 50.00,
            'precio_venta'      => 80.00,
            'requiere_receta'   => true,
            'es_controlado'     => true,
            'activo'            => true,
        ]);

        $this->loteControlado = Lote::create([
            'producto_id'       => $this->productoControlado->id,
            'numero_lote'       => 'LOT-CTRL-01',
            'fecha_vencimiento' => now()->addMonths(18),
            'stock_inicial'     => 50,
            'stock_actual'      => 50,
            'precio_compra'     => 50.00,
            'activo'            => true,
        ]);
    }

    public function test_anulacion_venta_simple_restituye_stock_y_kardex_y_caja()
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
                    'cantidad'    => 4,
                ]
            ]
        ]);

        $this->loteNormal->refresh();
        $this->assertEquals(96, $this->loteNormal->stock_actual);

        // Anular la venta
        $response = $this->actingAs($this->admin)->post(route('ventas.anular', $venta), [
            'motivo' => 'Error en digitación de cantidad por el cajero'
        ]);

        $response->assertRedirect(route('ventas.show', $venta));
        $response->assertSessionHas('success');

        $venta->refresh();
        $this->assertEquals('anulada', $venta->estado);
        $this->assertEquals('Error en digitación de cantidad por el cajero', $venta->motivo_anulacion);
        $this->assertEquals($this->admin->id, $venta->anulado_por);

        // Stock restituido
        $this->loteNormal->refresh();
        $this->assertEquals(100, $this->loteNormal->stock_actual);

        // Kardex contra-movimiento
        $mov = MovimientoInventario::where('origen_id', $venta->id)
            ->where('subtipo', 'anulacion_venta')
            ->first();
        $this->assertNotNull($mov);
        $this->assertEquals('entrada', $mov->tipo);
        $this->assertEquals(4, $mov->cantidad);
        $this->assertEquals(96, $mov->stock_anterior);
        $this->assertEquals(100, $mov->stock_posterior);

        // Caja recalculada
        $this->sesion->refresh();
        $this->assertEquals(0, (float)$this->sesion->total_ventas_efectivo);
        $this->assertEquals(1000.00, (float)$this->sesion->monto_esperado_efectivo);
    }

    public function test_anulacion_de_controlado_restituye_receta_y_asienta_libro_minsa()
    {
        $receta = Receta::create([
            'numero_receta'     => 'RX-TEST-999',
            'paciente_nombre'   => 'Juan Pérez',
            'cliente_id'        => $this->cliente->id,
            'medico_nombre'     => 'Dr. Roberto Vargas',
            'medico_colegiatura'=> 'MINSA-8877',
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
                    'cantidad'          => 6,
                ]
            ]
        ]);

        $recetaDetalle->refresh();
        $this->assertEquals(6, $recetaDetalle->cantidad_dispensada);
        $receta->refresh();
        $this->assertEquals('dispensada_parcial', $receta->estado);

        // Anular la venta
        $this->actingAs($this->admin)->post(route('ventas.anular', $venta), [
            'motivo' => 'Paciente canceló compra de psicotrópico'
        ]);

        // Receta revertida
        $recetaDetalle->refresh();
        $this->assertEquals(0, $recetaDetalle->cantidad_dispensada);
        $receta->refresh();
        $this->assertEquals('pendiente', $receta->estado);

        // Asiento MINSA de anulación
        $asientoMinsa = RegistroVentaControlado::where('venta_id', $venta->id)
            ->where('tipo_movimiento', RegistroVentaControlado::TIPO_ANULACION_VENTA)
            ->first();
        $this->assertNotNull($asientoMinsa);
        $this->assertEquals(6, $asientoMinsa->cantidad);
    }

    public function test_modificar_venta_genera_nueva_venta_con_trazabilidad_y_reversion_previa()
    {
        $ventaService = app(VentaService::class);

        $ventaOriginal = $ventaService->procesarVenta([
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

        // Modificar venta (cambiar a 5 unidades)
        $updateData = [
            'cliente_id'          => $this->cliente->id,
            'tipo_comprobante'    => 'ticket',
            'metodo_pago'         => 'efectivo',
            'monto_recibido'      => 100.00,
            'motivo_modificacion' => 'Cliente solicitó 3 unidades adicionales',
            'receta_modalidad'    => 'sin_receta',
            'productos'           => [
                [
                    'producto_id' => $this->productoNormal->id,
                    'lote_id'     => $this->loteNormal->id,
                    'cantidad'    => 5,
                ]
            ]
        ];

        $response = $this->actingAs($this->admin)->put(route('ventas.update', $ventaOriginal), $updateData);

        $ventaOriginal->refresh();
        $this->assertEquals('anulada', $ventaOriginal->estado);
        $this->assertNotNull($ventaOriginal->reemplazada_por);

        $nuevaVenta = \App\Models\Venta::find($ventaOriginal->reemplazada_por);
        $this->assertNotNull($nuevaVenta);
        $this->assertEquals($ventaOriginal->id, $nuevaVenta->venta_original_id);
        $this->assertEquals('completada', $nuevaVenta->estado);
        $this->assertEquals(75.00, (float)$nuevaVenta->total); // 5 x 15

        // Stock final: 100 - 5 = 95
        $this->loteNormal->refresh();
        $this->assertEquals(95, $this->loteNormal->stock_actual);

        $response->assertRedirect(route('ventas.show', $nuevaVenta));
    }

    public function test_bloqueo_anulacion_o_modificacion_si_venta_tiene_devoluciones()
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

        // Registrar una devolución completada sobre la venta
        DevolucionVenta::create([
            'numero_devolucion' => 'DEV-001',
            'venta_id'          => $venta->id,
            'user_id'           => $this->admin->id,
            'tipo'              => 'parcial',
            'motivo'            => 'cliente_desistio',
            'monto_total'       => 15.00,
            'metodo_reembolso'  => 'efectivo',
            'estado'            => 'completada',
            'fecha'             => now(),
        ]);

        $this->assertFalse($venta->puedeAnularse());
        $this->assertFalse($venta->puedeModificarse());

        // Intentar anular
        $response = $this->actingAs($this->admin)->post(route('ventas.anular', $venta), [
            'motivo' => 'Intento ilegal de anular venta con devoluciones'
        ]);

        $response->assertSessionHas('error');
        $venta->refresh();
        $this->assertEquals('completada', $venta->estado);
    }

    public function test_historial_ventas_y_visualizacion_ticket_pdf()
    {
        $ventaService = app(VentaService::class);

        $venta = $ventaService->procesarVenta([
            'cliente_id'       => $this->cliente->id,
            'tipo_comprobante' => 'factura',
            'metodo_pago'      => 'efectivo',
            'monto_recibido'   => 50.00,
            'receta_modalidad' => 'sin_receta',
            'productos'        => [
                [
                    'producto_id' => $this->productoNormal->id,
                    'lote_id'     => $this->loteNormal->id,
                    'cantidad'    => 1,
                ]
            ]
        ]);

        // Ver index
        $resIndex = $this->actingAs($this->admin)->get(route('ventas.index'));
        $resIndex->assertOk();
        $resIndex->assertSee($venta->numero_comprobante);

        // Ver show
        $resShow = $this->actingAs($this->admin)->get(route('ventas.show', $venta));
        $resShow->assertOk();
        $resShow->assertSee('Paracetamol 500mg');

        // Ver ticket térmico
        $resTicket = $this->actingAs($this->admin)->get(route('ventas.ticket', $venta));
        $resTicket->assertOk();
        $resTicket->assertSee('VENTA');

        // Generar PDF
        $resPdf = $this->actingAs($this->admin)->get(route('ventas.pdf', $venta));
        $resPdf->assertOk();
        $this->assertEquals('application/pdf', $resPdf->headers->get('Content-Type'));
    }
}
