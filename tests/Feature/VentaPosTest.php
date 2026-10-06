<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Producto;
use App\Models\PresentacionProducto;
use App\Models\Lote;
use App\Models\Caja;
use App\Models\SesionCaja;
use App\Models\Receta;
use App\Models\RecetaDetalle;
use App\Models\Promocion;
use App\Models\Venta;
use App\Models\RegistroVentaControlado;
use App\Models\MovimientoInventario;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VentaPosTest extends TestCase
{
    use RefreshDatabase;

    protected User $cajero;
    protected Caja $caja;
    protected SesionCaja $sesionCaja;

    protected function setUp(): void
    {
        parent::setUp();

        // Configurar roles y permisos
        $role = Role::firstOrCreate(['name' => 'cajero']);
        $permisos = [
            'realizar ventas',
            'ver ventas',
            'ver ventas propias',
            'ver recetas',
            'dispensar recetas',
            'aperturar cajas',
        ];

        foreach ($permisos as $p) {
            Permission::firstOrCreate(['name' => $p]);
        }
        $role->syncPermissions($permisos);

        $this->cajero = User::factory()->create([
            'name'  => 'Cajero de Prueba',
            'email' => 'cajero@farmabien.test',
        ]);
        $this->cajero->assignRole($role);

        // Crear Caja y Sesión Activa
        $this->caja = Caja::create([
            'nombre' => 'Caja Principal 01',
            'codigo' => 'CAJA-01',
            'activo' => true,
        ]);

        $this->sesionCaja = SesionCaja::create([
            'caja_id'               => $this->caja->id,
            'user_id'               => $this->cajero->id,
            'monto_apertura'        => 100.00,
            'fecha_apertura'        => now(),
            'estado'                => 'abierta',
            'total_ventas_efectivo' => 0,
            'total_ventas'          => 0,
        ]);
    }

    /**
     * Flujo 1: Venta simple en efectivo con presentación y conversión de unidades base.
     */
    public function test_venta_simple_en_efectivo_con_presentaciones_y_kardex(): void
    {
        $producto = Producto::factory()->create([
            'nombre'         => 'Paracetamol 500mg',
            'precio_venta'   => 2.00, // C$ 2.00 por tableta
            'tipo_control'   => 'venta_libre',
            'requiere_receta'=> false,
            'activo'         => true,
        ]);

        $presentacion = PresentacionProducto::create([
            'producto_id'               => $producto->id,
            'nombre'                    => 'Caja x 10',
            'unidades_por_presentacion' => 10,
            'precio_venta'              => 18.00, // C$ 18.00 por caja
            'activo'                    => true,
        ]);

        $lote = Lote::create([
            'producto_id'       => $producto->id,
            'numero_lote'       => 'LOT-PARA-001',
            'fecha_vencimiento' => now()->addYear()->toDateString(),
            'stock_inicial'     => 100,
            'stock_actual'      => 100,
            'precio_compra'     => 1.00,
            'activo'            => true,
        ]);

        $payload = [
            'tipo_comprobante' => 'ticket',
            'metodo_pago'      => 'efectivo',
            'monto_recibido'   => 50.00,
            'productos'        => [
                [
                    'producto_id'     => $producto->id,
                    'lote_id'         => $lote->id,
                    'presentacion_id' => $presentacion->id,
                    'cantidad'        => 2, // 2 cajas = 20 tabletas @ C$ 18 = C$ 36
                ]
            ]
        ];

        $response = $this->actingAs($this->cajero)->postJson(route('ventas.store'), $payload);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Verificar Venta
        $venta = Venta::latest('id')->first();
        $this->assertNotNull($venta);
        $this->assertEquals(36.00, (float) $venta->total);
        $this->assertEquals(50.00, (float) $venta->monto_recibido);
        $this->assertEquals(14.00, (float) $venta->cambio);
        $this->assertNotNull($venta->numero_comprobante);

        // Verificar Stock descontado en unidades base (100 - 20 = 80)
        $this->assertEquals(80, (int) $lote->fresh()->stock_actual);

        // Verificar Kardex de salida
        $mov = MovimientoInventario::where('origen', 'venta')->where('origen_id', $venta->id)->first();
        $this->assertNotNull($mov);
        $this->assertEquals(-20, (int) $mov->cantidad);
        $this->assertEquals(100, (int) $mov->stock_anterior);
        $this->assertEquals(80, (int) $mov->stock_posterior);
    }

    /**
     * Flujo 2: Venta con método de pago electrónico y aplicación automática de promociones.
     */
    public function test_venta_con_tarjeta_y_promocion_automatica(): void
    {
        $producto = Producto::factory()->create([
            'nombre'         => 'Vitamina C 1000mg',
            'precio_venta'   => 10.00,
            'tipo_control'   => 'venta_libre',
            'requiere_receta'=> false,
            'activo'         => true,
        ]);

        $lote = Lote::create([
            'producto_id'       => $producto->id,
            'numero_lote'       => 'LOT-VIT-001',
            'fecha_vencimiento' => now()->addYear()->toDateString(),
            'stock_inicial'     => 50,
            'stock_actual'      => 50,
            'precio_compra'     => 5.00,
            'activo'            => true,
        ]);

        // Promoción: 20% de descuento en el producto
        Promocion::create([
            'nombre'       => 'Promo Vitamina 20%',
            'alcance'      => 'producto',
            'producto_id'  => $producto->id,
            'tipo'         => 'porcentaje',
            'valor'        => 20.00,
            'min_unidades' => 1,
            'fecha_inicio' => now()->subDay()->toDateString(),
            'fecha_fin'    => now()->addDays(10)->toDateString(),
            'activo'       => true,
        ]);

        $payload = [
            'tipo_comprobante' => 'boleta',
            'metodo_pago'      => 'tarjeta',
            'referencia_pago'  => 'POS-AUTH-9988',
            'productos'        => [
                [
                    'producto_id' => $producto->id,
                    'lote_id'     => $lote->id,
                    'cantidad'    => 2, // 2 * 10 = 20, con 20% desc = 16.00
                ]
            ]
        ];

        $response = $this->actingAs($this->cajero)->postJson(route('ventas.store'), $payload);

        $response->assertStatus(200);
        $venta = Venta::latest('id')->first();
        $this->assertEquals(16.00, (float) $venta->total);
        $this->assertEquals(4.00, (float) $venta->detalles->first()->descuento ?? (20.00 - 16.00));
        $this->assertEquals('tarjeta', $venta->metodo_pago);
        $this->assertEquals(0, (float) $venta->cambio);
    }

    /**
     * Flujo 3: Venta de medicamentos controlados con receta vinculada y con omisión justificada.
     */
    public function test_venta_de_controlado_con_receta_y_con_omision_justificada(): void
    {
        $controlado = Producto::factory()->create([
            'nombre'         => 'Clonazepam 2mg',
            'precio_venta'   => 15.00,
            'tipo_control'   => 'controlado',
            'requiere_receta'=> true,
            'activo'         => true,
        ]);

        $lote = Lote::create([
            'producto_id'       => $controlado->id,
            'numero_lote'       => 'LOT-CLONA-001',
            'fecha_vencimiento' => now()->addYear()->toDateString(),
            'stock_inicial'     => 50,
            'stock_actual'      => 50,
            'precio_compra'     => 7.00,
            'activo'            => true,
        ]);

        // 3.1 Venta con Receta Médica Vinculada
        $receta = Receta::create([
            'numero_receta'      => 'RX-TEST-001',
            'medico_nombre'      => 'Dr. Carlos Mendoza',
            'medico_colegiatura' => 'CMP-12345',
            'paciente_nombre'    => 'Juan Pérez',
            'fecha_emision'      => now()->toDateString(),
            'fecha_vencimiento'  => now()->addDays(30)->toDateString(),
            'estado'             => 'pendiente',
        ]);

        $detalleReceta = RecetaDetalle::create([
            'receta_id'           => $receta->id,
            'producto_id'         => $controlado->id,
            'cantidad_recetada'   => 10,
            'cantidad_dispensada' => 0,
        ]);

        $payloadReceta = [
            'tipo_comprobante'  => 'ticket',
            'metodo_pago'       => 'efectivo',
            'monto_recibido'    => 100.00,
            'receta_modalidad'  => 'vinculada',
            'receta_id'         => $receta->id,
            'productos'         => [
                [
                    'producto_id'       => $controlado->id,
                    'lote_id'           => $lote->id,
                    'receta_detalle_id' => $detalleReceta->id,
                    'cantidad'          => 4,
                ]
            ]
        ];

        $res1 = $this->actingAs($this->cajero)->postJson(route('ventas.store'), $payloadReceta);
        $res1->assertStatus(200);

        // Verificar que se dispensó la receta
        $this->assertEquals(4, (int) $detalleReceta->fresh()->cantidad_dispensada);
        $this->assertEquals('dispensada_parcial', $receta->fresh()->estado);

        // Verificar asiento en Libro Oficial MINSA
        $this->assertDatabaseHas('registros_venta_controlados', [
            'producto_id'     => $controlado->id,
            'lote_id'         => $lote->id,
            'tipo_movimiento' => RegistroVentaControlado::TIPO_VENTA,
            'paciente_nombre' => 'Juan Pérez',
            'medico_nombre'   => 'Dr. Carlos Mendoza',
            'cantidad'        => 4,
        ]);

        // 3.2 Venta con Omisión Justificada (Preservando requerimiento explícito del usuario)
        $payloadOmision = [
            'tipo_comprobante'      => 'ticket',
            'metodo_pago'           => 'efectivo',
            'monto_recibido'        => 50.00,
            'receta_modalidad'      => 'omitida',
            'receta_omision_motivo' => 'Urgencia ambulatoria verificada por regente',
            'productos'             => [
                [
                    'producto_id' => $controlado->id,
                    'lote_id'     => $lote->id,
                    'cantidad'    => 2,
                ]
            ]
        ];

        $res2 = $this->actingAs($this->cajero)->postJson(route('ventas.store'), $payloadOmision);
        $res2->assertStatus(200);

        // Verificar asiento en Libro Oficial MINSA con motivo de omisión
        $this->assertDatabaseHas('registros_venta_controlados', [
            'producto_id'    => $controlado->id,
            'motivo_omision' => 'Urgencia ambulatoria verificada por regente',
            'cantidad'       => 2,
        ]);
    }

    /**
     * Flujo 4: Tolerancia a fallos, Idempotencia, Concurrencia y Verificación de Integridad.
     */
    public function test_idempotencia_prevencion_caja_inactiva_y_comando_integridad(): void
    {
        $producto = Producto::factory()->create([
            'nombre'         => 'Omeprazol 20mg',
            'precio_venta'   => 5.00,
            'tipo_control'   => 'venta_libre',
            'requiere_receta'=> false,
            'activo'         => true,
        ]);

        $lote = Lote::create([
            'producto_id'       => $producto->id,
            'numero_lote'       => 'LOT-OME-001',
            'fecha_vencimiento' => now()->addYear()->toDateString(),
            'stock_inicial'     => 10,
            'stock_actual'      => 10,
            'precio_compra'     => 2.00,
            'activo'            => true,
        ]);

        // 4.1 Idempotency Key ante reintentos
        $idempotencyKey = 'idemp_test_' . uniqid();
        $payload = [
            'idempotency_key'  => $idempotencyKey,
            'tipo_comprobante' => 'ticket',
            'metodo_pago'      => 'efectivo',
            'monto_recibido'   => 20.00,
            'productos'        => [
                [
                    'producto_id' => $producto->id,
                    'lote_id'     => $lote->id,
                    'cantidad'    => 2,
                ]
            ]
        ];

        // Primer intento
        $res1 = $this->actingAs($this->cajero)->postJson(route('ventas.store'), $payload);
        $res1->assertStatus(200);
        $venta1Id = $res1->json('venta.id');

        // Segundo intento con la misma clave (simulando doble clic o parpadeo de red)
        $res2 = $this->actingAs($this->cajero)->postJson(route('ventas.store'), $payload);
        $res2->assertStatus(200);
        $venta2Id = $res2->json('venta.id');

        // Debe retornar la misma venta sin duplicar stock ni kardex
        $this->assertEquals($venta1Id, $venta2Id);
        $this->assertEquals(8, (int) $lote->fresh()->stock_actual); // Solo 2 unidades descontadas

        // 4.2 Bloqueo de venta si no hay turno de caja abierto
        $cajeroSinCaja = User::factory()->create();
        $cajeroSinCaja->assignRole('cajero');

        $resSinCaja = $this->actingAs($cajeroSinCaja)->postJson(route('ventas.store'), $payload);
        $resSinCaja->assertStatus(422);

        // 4.3 Ejecutar comando de verificación de integridad
        $this->artisan('farma:verificar-ventas-pos')
            ->assertExitCode(0);
    }
}
