<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Caja;
use App\Models\SesionCaja;
use App\Models\MovimientoCaja;
use App\Models\Venta;
use App\Services\CajaService;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use DomainException;
use Exception;

class CajasControlTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $cajeroUser;
    protected CajaService $cajaService;
    protected Caja $caja;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cajaService = app(CajaService::class);

        // Crear permisos y roles
        Permission::firstOrCreate(['name' => 'ver cajas']);
        Permission::firstOrCreate(['name' => 'crear cajas']);
        Permission::firstOrCreate(['name' => 'editar cajas']);
        Permission::firstOrCreate(['name' => 'desactivar cajas']);
        Permission::firstOrCreate(['name' => 'abrir caja']);
        Permission::firstOrCreate(['name' => 'cerrar caja']);
        Permission::firstOrCreate(['name' => 'registrar movimientos caja']);

        $roleAdmin = Role::firstOrCreate(['name' => 'admin']);
        $roleAdmin->syncPermissions(Permission::all());

        $roleCajero = Role::firstOrCreate(['name' => 'Cajero']);
        $roleCajero->givePermissionTo(['ver cajas', 'abrir caja', 'cerrar caja', 'registrar movimientos caja']);

        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('admin');

        $this->cajeroUser = User::factory()->create();
        $this->cajeroUser->assignRole('Cajero');

        $this->caja = Caja::create([
            'nombre'      => 'Caja Principal 01',
            'codigo'      => 'CAJA-01',
            'ubicacion'   => 'Mostrador Central',
            'descripcion' => 'Caja de punto de venta principal',
            'activo'      => true,
        ]);
    }

    public function test_usuario_sin_permisos_no_puede_acceder_a_cajas(): void
    {
        $sinPermisos = User::factory()->create();
        $response = $this->actingAs($sinPermisos)->get(route('cajas.index'));

        $response->assertStatus(403);
    }

    public function test_admin_puede_listar_cajas_y_kpis(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('cajas.index'));

        $response->assertStatus(200);
        $response->assertSee('Caja Principal 01');
        $response->assertSee('CAJA-01');
    }

    public function test_crear_y_actualizar_caja_fisica(): void
    {
        // Crear
        $response = $this->actingAs($this->adminUser)->post(route('cajas.store'), [
            'nombre'      => 'Caja Auxiliar 02',
            'codigo'      => 'CAJA-02',
            'ubicacion'   => 'Mostrador Secundario',
            'descripcion' => 'Caja de apoyo para horas pico',
        ]);

        $response->assertRedirect(route('cajas.index'));
        $this->assertDatabaseHas('cajas', [
            'codigo' => 'CAJA-02',
            'nombre' => 'Caja Auxiliar 02',
            'activo' => true,
        ]);

        $cajaCreada = Caja::where('codigo', 'CAJA-02')->first();

        // Actualizar
        $updateResponse = $this->actingAs($this->adminUser)->put(route('cajas.update', $cajaCreada), [
            'nombre'      => 'Caja Auxiliar 02 Modificada',
            'codigo'      => 'CAJA-02',
            'ubicacion'   => 'Lado Este',
            'descripcion' => 'Nueva descripción',
            'activo'      => true,
        ]);

        $updateResponse->assertRedirect(route('cajas.index'));
        $this->assertDatabaseHas('cajas', [
            'id'     => $cajaCreada->id,
            'nombre' => 'Caja Auxiliar 02 Modificada',
        ]);
    }

    public function test_activar_desactivar_caja_y_bloqueo_si_esta_abierta(): void
    {
        // Desactivar sin turno abierto
        $response = $this->actingAs($this->adminUser)->post(route('cajas.toggle', $this->caja));
        $response->assertRedirect(route('cajas.index'));
        $this->assertFalse($this->caja->fresh()->activo);

        // Reactivar
        $this->actingAs($this->adminUser)->post(route('cajas.toggle', $this->caja));
        $this->assertTrue($this->caja->fresh()->activo);

        // Abrir turno y probar que no se puede desactivar
        $this->cajaService->abrirCaja($this->caja, $this->cajeroUser, 100.00);

        $responseBloqueo = $this->actingAs($this->adminUser)->post(route('cajas.toggle', $this->caja));
        $responseBloqueo->assertSessionHas('error');
        $this->assertTrue($this->caja->fresh()->activo);
    }

    public function test_abrir_turno_de_caja_con_monto_inicial(): void
    {
        $sesion = $this->cajaService->abrirCaja($this->caja, $this->cajeroUser, 150.50, 'Fondo de apertura billetes y monedas');

        $this->assertDatabaseHas('sesiones_caja', [
            'id'                      => $sesion->id,
            'caja_id'                 => $this->caja->id,
            'user_id'                 => $this->cajeroUser->id,
            'monto_inicial'           => 150.50,
            'monto_esperado_efectivo' => 150.50,
            'estado'                  => 'abierta',
        ]);

        $this->assertTrue($this->caja->estaAbierta());
    }

    public function test_bloqueo_de_doble_apertura_en_misma_caja_o_mismo_usuario(): void
    {
        // 1. Primera apertura
        $this->cajaService->abrirCaja($this->caja, $this->cajeroUser, 100.00);

        // 2. Intentar abrir la misma caja con otro usuario
        $otroCajero = User::factory()->create();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('ya cuenta con una sesión abierta');

        $this->cajaService->abrirCaja($this->caja, $otroCajero, 50.00);
    }

    public function test_bloqueo_de_apertura_si_el_usuario_ya_tiene_otra_caja_abierta(): void
    {
        $otraCaja = Caja::create([
            'nombre' => 'Caja Turno Tarde',
            'codigo' => 'CAJA-TARDE',
            'activo' => true,
        ]);

        $this->cajaService->abrirCaja($this->caja, $this->cajeroUser, 100.00);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Ya tienes la sesión');

        $this->cajaService->abrirCaja($otraCaja, $this->cajeroUser, 50.00);
    }

    public function test_registrar_ingreso_y_egreso_manual_con_recalculo_en_tiempo_real(): void
    {
        $sesion = $this->cajaService->abrirCaja($this->caja, $this->cajeroUser, 200.00);

        // Ingreso manual de $50 (cambio extra)
        $ingreso = $this->cajaService->registrarMovimiento(
            $sesion,
            $this->cajeroUser,
            'ingreso',
            50.00,
            'Ingreso de sencillo para cambio',
            'REC-ING-01'
        );

        $this->assertDatabaseHas('movimientos_caja', [
            'id'             => $ingreso->id,
            'sesion_caja_id' => $sesion->id,
            'tipo'           => 'ingreso',
            'monto'          => 50.00,
        ]);

        $sesion->refresh();
        $this->assertEquals(250.00, (float) $sesion->monto_esperado_efectivo);
        $this->assertEquals(50.00, (float) $sesion->total_ingresos_manuales);

        // Egreso manual de $30 (compra de insumos de limpieza)
        $egreso = $this->cajaService->registrarMovimiento(
            $sesion,
            $this->cajeroUser,
            'egreso',
            30.00,
            'Pago de artículos de limpieza',
            'FACT-LIMP-99'
        );

        $this->assertDatabaseHas('movimientos_caja', [
            'id'             => $egreso->id,
            'sesion_caja_id' => $sesion->id,
            'tipo'           => 'egreso',
            'monto'          => 30.00,
        ]);

        $sesion->refresh();
        $this->assertEquals(220.00, (float) $sesion->monto_esperado_efectivo);
        $this->assertEquals(30.00, (float) $sesion->total_egresos_manuales);
    }

    public function test_rechazo_de_egreso_si_monto_supera_efectivo_disponible(): void
    {
        $sesion = $this->cajaService->abrirCaja($this->caja, $this->cajeroUser, 100.00);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('No se puede retirar un monto');

        // Intentar retirar $150 cuando solo hay $100
        $this->cajaService->registrarMovimiento(
            $sesion,
            $this->cajeroUser,
            'egreso',
            150.00,
            'Retiro mayor al saldo'
        );
    }

    public function test_arqueo_y_cierre_formal_con_cuadre_sobrante_y_faltante(): void
    {
        // 1. Cierre con Cuadre Exacto
        $sesion = $this->cajaService->abrirCaja($this->caja, $this->cajeroUser, 100.00);
        $this->cajaService->registrarMovimiento($sesion, $this->cajeroUser, 'ingreso', 50.00, 'Ingreso extra');
        
        // Efectivo esperado = $150.00
        $sesionCerrada = $this->cajaService->cerrarCaja($sesion, $this->cajeroUser, 150.00, 'Cierre sin novedades');

        $this->assertEquals('cerrada', $sesionCerrada->estado);
        $this->assertEquals(150.00, (float) $sesionCerrada->monto_final_efectivo);
        $this->assertEquals(0.00, (float) $sesionCerrada->diferencia_efectivo);

        // 2. Cierre con Sobrante de $10 (+10)
        $otraCaja = Caja::create(['nombre' => 'Caja 2', 'codigo' => 'CAJA-2', 'activo' => true]);
        $sesion2 = $this->cajaService->abrirCaja($otraCaja, $this->cajeroUser, 100.00);
        $sesion2Cerrada = $this->cajaService->cerrarCaja($sesion2, $this->cajeroUser, 110.00, 'Sobrante de $10');

        $this->assertEquals(10.00, (float) $sesion2Cerrada->diferencia_efectivo);

        // 3. Cierre con Faltante de $5 (-5)
        $caja3 = Caja::create(['nombre' => 'Caja 3', 'codigo' => 'CAJA-3', 'activo' => true]);
        $sesion3 = $this->cajaService->abrirCaja($caja3, $this->cajeroUser, 100.00);
        $sesion3Cerrada = $this->cajaService->cerrarCaja($sesion3, $this->cajeroUser, 95.00, 'Faltante de $5');

        $this->assertEquals(-5.00, (float) $sesion3Cerrada->diferencia_efectivo);
    }

    public function test_movimiento_caja_es_inmutable_ante_modificacion_y_eliminacion(): void
    {
        $sesion = $this->cajaService->abrirCaja($this->caja, $this->cajeroUser, 100.00);
        $movimiento = $this->cajaService->registrarMovimiento($sesion, $this->cajeroUser, 'ingreso', 40.00, 'Ingreso seguro');

        // Modificación rechazada
        try {
            $movimiento->update(['monto' => 10.00]);
            $this->fail('Se esperaba DomainException al intentar modificar un movimiento de caja.');
        } catch (DomainException $e) {
            $this->assertStringContainsString('inmutables', $e->getMessage());
        }

        // Eliminación rechazada
        try {
            $movimiento->delete();
            $this->fail('Se esperaba DomainException al intentar eliminar un movimiento de caja.');
        } catch (DomainException $e) {
            $this->assertStringContainsString('inmutables', $e->getMessage());
        }
    }

    public function test_sesion_caja_cerrada_es_inmutable_ante_eliminacion(): void
    {
        $sesion = $this->cajaService->abrirCaja($this->caja, $this->cajeroUser, 100.00);
        $this->cajaService->cerrarCaja($sesion, $this->cajeroUser, 100.00);
        $sesion->refresh();

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('No se puede eliminar una sesión de caja formalmente cerrada');

        $sesion->delete();
    }

    public function test_ticket_de_arqueo_y_comando_auditoria(): void
    {
        $sesion = $this->cajaService->abrirCaja($this->caja, $this->cajeroUser, 100.00);
        $this->cajaService->registrarMovimiento($sesion, $this->cajeroUser, 'ingreso', 25.00, 'Cambio');
        $this->cajaService->cerrarCaja($sesion, $this->cajeroUser, 125.00);
        $sesion->refresh();

        // Visualización de Ticket de Arqueo
        $response = $this->actingAs($this->adminUser)->get(route('cajas.ticket', $sesion));
        $response->assertStatus(200);
        $response->assertSee('ARQUEO Y CORTE DE CAJA');

        // Ejecución de comando de auditoría
        $this->artisan('farma:verificar-cajas')
            ->assertExitCode(0)
            ->expectsOutputToContain('AUDITORÍA DE CAJAS, TURNOS Y ARQUEOS');
    }
}
