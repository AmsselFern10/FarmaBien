<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Producto;
use App\Models\Lote;
use App\Models\Proveedor;
use App\Models\Categoria;
use App\Models\Laboratorio;
use App\Models\MovimientoInventario;
use App\Services\InventarioService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LotesKardexTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $usuarioSinPermisos;
    protected Categoria $categoria;
    protected Laboratorio $laboratorio;
    protected Proveedor $proveedor;
    protected Producto $producto;

    protected function setUp(): void
    {
        parent::setUp();

        $permisos = [
            'ver movimientos inventario',
            'ajustar inventario',
            'editar lotes',
            'ver productos',
            'ver compras',
        ];

        foreach ($permisos as $permiso) {
            Permission::findOrCreate($permiso, 'web');
        }

        $roleAdmin = Role::findOrCreate('Administrador', 'web');
        $roleAdmin->syncPermissions($permisos);

        $this->admin = User::factory()->create();
        $this->admin->assignRole($roleAdmin);

        $this->usuarioSinPermisos = User::factory()->create();

        $this->categoria = Categoria::factory()->create(['nombre' => 'Analgésicos']);
        $this->laboratorio = Laboratorio::factory()->create(['nombre' => 'Bayer']);
        $this->proveedor = Proveedor::factory()->create(['nombre' => 'Droguería Médica']);

        $this->producto = Producto::factory()->create([
            'categoria_id'   => $this->categoria->id,
            'laboratorio_id' => $this->laboratorio->id,
            'nombre'         => 'Paracetamol 500mg',
            'precio_compra'  => 10.00,
            'precio_venta'   => 15.00,
            'stock_minimo'   => 20,
            'activo'         => true,
        ]);
    }

    /* ══════════════════════════════════════════════════════════════
       1. CONTROL DE ACCESO Y PERMISOS
       ══════════════════════════════════════════════════════════════ */

    public function test_usuario_sin_permisos_recibe_403_al_acceder_a_inventario(): void
    {
        $response = $this->actingAs($this->usuarioSinPermisos)->get(route('inventario.movimientos'));
        $response->assertStatus(403);

        $resAjuste = $this->actingAs($this->usuarioSinPermisos)->get(route('inventario.ajustar'));
        $resAjuste->assertStatus(403);
    }

    public function test_admin_puede_listar_lotes_y_movimientos(): void
    {
        $lote = Lote::factory()->create([
            'producto_id'  => $this->producto->id,
            'proveedor_id' => $this->proveedor->id,
            'numero_lote'  => 'LOT-TEST-001',
            'stock_actual' => 50,
        ]);

        MovimientoInventario::factory()->create([
            'producto_id'     => $this->producto->id,
            'lote_id'         => $lote->id,
            'user_id'         => $this->admin->id,
            'tipo'            => 'entrada',
            'subtipo'         => 'ajuste_manual',
            'cantidad'        => 50,
            'stock_anterior'  => 0,
            'stock_posterior' => 50,
        ]);

        $resLotes = $this->actingAs($this->admin)->get(route('inventario.lotes'));
        $resLotes->assertStatus(200);
        $resLotes->assertSee('LOT-TEST-001');

        $resMovs = $this->actingAs($this->admin)->get(route('inventario.movimientos'));
        $resMovs->assertStatus(200);
        $resMovs->assertSee('Paracetamol 500mg');
    }

    /* ══════════════════════════════════════════════════════════════
       2. FLUJO 1: CREACIÓN DE LOTE MANUAL Y ASIENTO EN KARDEX
       ══════════════════════════════════════════════════════════════ */

    public function test_flujo_1_crear_lote_manual_registra_entrada_en_kardex_con_invariantes(): void
    {
        $payload = [
            'producto_id'       => $this->producto->id,
            'numero_lote'       => 'LOT-MANUAL-2026',
            'fecha_vencimiento' => now()->addMonths(18)->toDateString(),
            'cantidad'          => 150,
            'precio_compra'     => 12.50,
            'proveedor_id'      => $this->proveedor->id,
            'motivo'            => 'Ingreso de stock de apertura de farmacia',
        ];

        $response = $this->actingAs($this->admin)->post(route('inventario.lotes.store'), $payload);
        $response->assertRedirect(route('inventario.lotes'));

        $this->assertDatabaseHas('lotes', [
            'producto_id'   => $this->producto->id,
            'numero_lote'   => 'LOT-MANUAL-2026',
            'stock_actual'  => 150,
            'precio_compra' => 12.50,
            'activo'        => true,
        ]);

        $lote = Lote::where('numero_lote', 'LOT-MANUAL-2026')->first();
        $this->assertNotNull($lote);

        $this->assertDatabaseHas('movimientos_inventario', [
            'producto_id'     => $this->producto->id,
            'lote_id'         => $lote->id,
            'tipo'            => 'entrada',
            'subtipo'         => 'ajuste_manual',
            'cantidad'        => 150,
            'stock_anterior'  => 0,
            'stock_posterior' => 150,
        ]);

        // Validar Invariante 1: Stock Lote == SUM(Movimientos)
        $this->assertEquals(150, (int)$lote->stock_actual);
        $this->assertEquals(150, (int)$lote->movimientos()->sum('cantidad'));
    }

    public function test_valida_unicidad_de_numero_lote_por_producto(): void
    {
        Lote::factory()->create([
            'producto_id' => $this->producto->id,
            'numero_lote' => 'LOT-REPETIDO-01',
        ]);

        $payload = [
            'producto_id'       => $this->producto->id,
            'numero_lote'       => 'LOT-REPETIDO-01',
            'fecha_vencimiento' => now()->addYear()->toDateString(),
            'cantidad'          => 50,
            'motivo'            => 'Intento de duplicación',
        ];

        $response = $this->actingAs($this->admin)->post(route('inventario.lotes.store'), $payload);
        $response->assertSessionHasErrors(['numero_lote']);
    }

    /* ══════════════════════════════════════════════════════════════
       3. FLUJO 2: DESCUENTO FEFO/FIFO Y REVERSIÓN EN KARDEX
       ══════════════════════════════════════════════════════════════ */

    public function test_flujo_2_descontar_stock_fefo_consume_lote_mas_proximo_a_vencer(): void
    {
        // Lote A: Vence en 3 meses (50 unidades)
        $loteA = Lote::factory()->create([
            'producto_id'       => $this->producto->id,
            'numero_lote'       => 'LOT-A-3MESES',
            'fecha_vencimiento' => now()->addMonths(3)->toDateString(),
            'stock_actual'      => 50,
        ]);

        // Lote B: Vence en 12 meses (100 unidades)
        $loteB = Lote::factory()->create([
            'producto_id'       => $this->producto->id,
            'numero_lote'       => 'LOT-B-12MESES',
            'fecha_vencimiento' => now()->addMonths(12)->toDateString(),
            'stock_actual'      => 100,
        ]);

        $service = app(InventarioService::class);
        $asignaciones = $service->descontarStockFIFO($this->producto, 70, 'venta', 99, 'Venta #99');

        $this->assertCount(2, $asignaciones);
        // Debe haber agotado las 50 de loteA y 20 de loteB
        $this->assertEquals(0, $loteA->fresh()->stock_actual);
        $this->assertEquals(80, $loteB->fresh()->stock_actual);

        // Verificar movimientos en Kardex
        $this->assertDatabaseHas('movimientos_inventario', [
            'lote_id'         => $loteA->id,
            'tipo'            => 'salida',
            'cantidad'        => -50,
            'stock_anterior'  => 50,
            'stock_posterior' => 0,
        ]);

        $this->assertDatabaseHas('movimientos_inventario', [
            'lote_id'         => $loteB->id,
            'tipo'            => 'salida',
            'cantidad'        => -20,
            'stock_anterior'  => 100,
            'stock_posterior' => 80,
        ]);
    }

    public function test_descontar_stock_fefo_bloquea_si_stock_es_insuficiente(): void
    {
        Lote::factory()->create([
            'producto_id'       => $this->producto->id,
            'fecha_vencimiento' => now()->addMonths(6)->toDateString(),
            'stock_actual'      => 10,
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Stock insuficiente para 'Paracetamol 500mg'");

        $service = app(InventarioService::class);
        $service->descontarStockFIFO($this->producto, 50, 'venta');
    }

    /* ══════════════════════════════════════════════════════════════
       4. FLUJO 3: AJUSTE DE INVENTARIO Y BAJA DE LOTES VENCIDOS
       ══════════════════════════════════════════════════════════════ */

    public function test_flujo_3_ajuste_manual_positivo_y_negativo(): void
    {
        $lote = Lote::factory()->create([
            'producto_id'  => $this->producto->id,
            'numero_lote'  => 'LOT-AJUSTE-01',
            'stock_actual' => 100,
        ]);

        // 1. Ajuste negativo (de 100 a 80 por daño)
        $payloadNeg = [
            'lote_id'     => $lote->id,
            'stock_nuevo' => 80,
            'subtipo'     => 'merma_danio',
            'motivo'      => 'Frascos rotos durante acomodo',
        ];

        $resNeg = $this->actingAs($this->admin)->post(route('inventario.ajustar.store'), $payloadNeg);
        $resNeg->assertRedirect(route('inventario.movimientos'));

        $this->assertEquals(80, $lote->fresh()->stock_actual);
        $this->assertDatabaseHas('movimientos_inventario', [
            'lote_id'         => $lote->id,
            'tipo'            => 'salida',
            'subtipo'         => 'merma_danio',
            'cantidad'        => -20,
            'stock_anterior'  => 100,
            'stock_posterior' => 80,
        ]);

        // 2. Ajuste positivo (de 80 a 95 por sobrante físico)
        $payloadPos = [
            'lote_id'     => $lote->id,
            'stock_nuevo' => 95,
            'subtipo'     => 'ajuste_manual',
            'motivo'      => 'Sobrante encontrado en auditoría física',
        ];

        $resPos = $this->actingAs($this->admin)->post(route('inventario.ajustar.store'), $payloadPos);
        $resPos->assertRedirect(route('inventario.movimientos'));

        $this->assertEquals(95, $lote->fresh()->stock_actual);
        $this->assertDatabaseHas('movimientos_inventario', [
            'lote_id'         => $lote->id,
            'tipo'            => 'entrada',
            'subtipo'         => 'ajuste_manual',
            'cantidad'        => 15,
            'stock_anterior'  => 80,
            'stock_posterior' => 95,
        ]);
    }

    public function test_baja_automatica_de_lotes_vencidos(): void
    {
        $loteVencido = Lote::factory()->vencido()->create([
            'producto_id'  => $this->producto->id,
            'stock_actual' => 30,
            'activo'       => true,
        ]);

        $service = app(InventarioService::class);
        $totalBajas = $service->desactivarLotesVencidos();

        $this->assertEquals(1, $totalBajas);
        $this->assertEquals(0, $loteVencido->fresh()->stock_actual);
        $this->assertFalse($loteVencido->fresh()->activo);

        $this->assertDatabaseHas('movimientos_inventario', [
            'lote_id'         => $loteVencido->id,
            'tipo'            => 'salida',
            'subtipo'         => 'vencimiento_automatico',
            'cantidad'        => -30,
            'stock_anterior'  => 30,
            'stock_posterior' => 0,
        ]);
    }

    /* ══════════════════════════════════════════════════════════════
       5. FLUJO 4: INMUTABILIDAD DE KARDEX Y COMANDO DE INTEGRIDAD
       ══════════════════════════════════════════════════════════════ */

    public function test_flujo_4_kardex_es_inmutable_y_no_permite_modificacion_ni_borrado(): void
    {
        $lote = Lote::factory()->create(['producto_id' => $this->producto->id]);

        $mov = MovimientoInventario::factory()->create([
            'producto_id' => $this->producto->id,
            'lote_id'     => $lote->id,
            'cantidad'    => 100,
        ]);

        // Intento de actualización debe lanzar DomainException
        try {
            $mov->update(['cantidad' => 200]);
            $this->fail('Se esperaba DomainException al intentar modificar un registro de Kardex');
        } catch (DomainException $e) {
            $this->assertStringContainsString('inmutables', $e->getMessage());
        }

        // Intento de eliminación debe lanzar DomainException
        try {
            $mov->delete();
            $this->fail('Se esperaba DomainException al intentar eliminar un registro de Kardex');
        } catch (DomainException $e) {
            $this->assertStringContainsString('inmutables', $e->getMessage());
        }
    }

    public function test_comando_verificar_kardex_ejecuta_correctamente(): void
    {
        $lote = Lote::factory()->create([
            'producto_id'  => $this->producto->id,
            'stock_actual' => 50,
        ]);

        MovimientoInventario::factory()->create([
            'producto_id'     => $this->producto->id,
            'lote_id'         => $lote->id,
            'user_id'         => $this->admin->id,
            'tipo'            => 'entrada',
            'subtipo'         => 'ajuste_manual',
            'cantidad'        => 50,
            'stock_anterior'  => 0,
            'stock_posterior' => 50,
        ]);

        $this->artisan('farma:verificar-kardex')
            ->expectsOutputToContain('AUDITORÍA DE INTEGRIDAD DE DATOS Y KARDEX')
            ->expectsOutputToContain('ESTADO: SISTEMA ÍNTEGRO')
            ->assertExitCode(0);
    }

    public function test_actualizar_metadatos_lote_con_auditoria(): void
    {
        $lote = Lote::factory()->create([
            'producto_id'       => $this->producto->id,
            'numero_lote'       => 'LOT-ORIGINAL',
            'fecha_vencimiento' => now()->addMonths(12)->toDateString(),
            'stock_actual'      => 50,
        ]);

        $nuevaFecha = now()->addMonths(24)->toDateString();
        $payload = [
            'numero_lote'       => 'LOT-CORREGIDO',
            'fecha_vencimiento' => $nuevaFecha,
            'proveedor_id'      => $this->proveedor->id,
            'motivo_cambio'     => 'Corrección de fecha según certificado de análisis',
        ];

        $response = $this->actingAs($this->admin)->put(route('inventario.lotes.update', $lote), $payload);
        $response->assertRedirect(route('inventario.lotes'));

        $loteActualizado = $lote->fresh();
        $this->assertEquals('LOT-CORREGIDO', $loteActualizado->numero_lote);
        $this->assertEquals($nuevaFecha, $loteActualizado->fecha_vencimiento->format('Y-m-d'));
        $this->assertEquals($this->proveedor->id, $loteActualizado->proveedor_id);
        $this->assertEquals(50, $loteActualizado->stock_actual); // Stock permanece inalterado
    }
}
