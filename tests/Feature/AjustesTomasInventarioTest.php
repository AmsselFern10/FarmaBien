<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Laboratorio;
use App\Models\Lote;
use App\Models\ConteoInventario;
use App\Models\DetalleConteo;
use App\Models\MovimientoInventario;
use App\Models\RegistroVentaControlado;
use App\Services\InventarioService;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AjustesTomasInventarioTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $sinPermisoUser;
    protected Producto $productoNormal;
    protected Producto $productoControlado;
    protected Lote $loteNormal;
    protected Lote $loteControlado;
    protected InventarioService $inventarioService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->inventarioService = app(InventarioService::class);

        Permission::firstOrCreate(['name' => 'ver movimientos inventario']);
        Permission::firstOrCreate(['name' => 'ajustar inventario']);

        $roleAdmin = Role::firstOrCreate(['name' => 'admin']);
        $roleAdmin->syncPermissions(Permission::all());

        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('admin');

        $this->sinPermisoUser = User::factory()->create();

        $cat = Categoria::create(['nombre' => 'Analgésicos']);
        $lab = Laboratorio::create(['nombre' => 'Laboratorio Farma']);

        $this->productoNormal = Producto::create([
            'nombre'          => 'Ibuprofeno 400mg',
            'codigo_barra'    => '770000000001',
            'categoria_id'    => $cat->id,
            'laboratorio_id'  => $lab->id,
            'precio_compra'   => 10.00,
            'precio_venta'    => 15.00,
            'tipo_control'    => 'venta_libre',
            'requiere_receta' => false,
            'activo'          => true,
        ]);

        $this->productoControlado = Producto::create([
            'nombre'          => 'Alprazolam 0.5mg',
            'codigo_barra'    => '770000000002',
            'categoria_id'    => $cat->id,
            'laboratorio_id'  => $lab->id,
            'precio_compra'   => 20.00,
            'precio_venta'    => 35.00,
            'tipo_control'    => 'controlado',
            'requiere_receta' => true,
            'activo'          => true,
        ]);

        $this->loteNormal = Lote::create([
            'producto_id'       => $this->productoNormal->id,
            'numero_lote'       => 'LOT-NORM-01',
            'fecha_vencimiento' => now()->addYear()->toDateString(),
            'stock_inicial'     => 100,
            'stock_actual'      => 100,
            'precio_compra'     => 10.00,
            'activo'            => true,
        ]);

        $this->loteControlado = Lote::create([
            'producto_id'       => $this->productoControlado->id,
            'numero_lote'       => 'LOT-CTRL-01',
            'fecha_vencimiento' => now()->addYear()->toDateString(),
            'stock_inicial'     => 50,
            'stock_actual'      => 50,
            'precio_compra'     => 20.00,
            'activo'            => true,
        ]);
    }

    public function test_usuario_sin_permiso_no_puede_ajustar_ni_crear_tomas(): void
    {
        $responseAjuste = $this->actingAs($this->sinPermisoUser)->get(route('inventario.ajustar'));
        $responseAjuste->assertStatus(403);

        $responseTomas = $this->actingAs($this->sinPermisoUser)->get(route('inventario.conteos.index'));
        $responseTomas->assertStatus(403);
    }

    public function test_ajuste_manual_positivo_y_negativo_con_kardex(): void
    {
        // 1. Ajuste negativo (-20 por merma de daño)
        $response = $this->actingAs($this->adminUser)->post(route('inventario.ajustar.store'), [
            'lote_id'     => $this->loteNormal->id,
            'stock_nuevo' => 80,
            'subtipo'     => 'merma_danio',
            'motivo'      => 'Frascos rotos durante transporte',
        ]);

        $response->assertRedirect(route('inventario.movimientos'));
        $this->loteNormal->refresh();
        $this->assertEquals(80, $this->loteNormal->stock_actual);

        $this->assertDatabaseHas('movimientos_inventario', [
            'lote_id'         => $this->loteNormal->id,
            'tipo'            => 'salida',
            'subtipo'         => 'merma_danio',
            'cantidad'        => -20,
            'stock_anterior'  => 100,
            'stock_posterior' => 80,
        ]);

        // 2. Ajuste positivo (+15 por reencuentro de stock)
        $this->actingAs($this->adminUser)->post(route('inventario.ajustar.store'), [
            'lote_id'     => $this->loteNormal->id,
            'stock_nuevo' => 95,
            'subtipo'     => 'ajuste_manual',
            'motivo'      => 'Reconteo físico en estantería superior',
        ]);

        $this->loteNormal->refresh();
        $this->assertEquals(95, $this->loteNormal->stock_actual);

        $this->assertDatabaseHas('movimientos_inventario', [
            'lote_id'         => $this->loteNormal->id,
            'tipo'            => 'entrada',
            'subtipo'         => 'ajuste_manual',
            'cantidad'        => 15,
            'stock_anterior'  => 80,
            'stock_posterior' => 95,
        ]);
    }

    public function test_ajuste_manual_sobre_medicamento_controlado_genera_asiento_minsa(): void
    {
        $this->actingAs($this->adminUser)->post(route('inventario.ajustar.store'), [
            'lote_id'     => $this->loteControlado->id,
            'stock_nuevo' => 45,
            'subtipo'     => 'merma_vencimiento',
            'motivo'      => 'Baja por blíster deteriorado',
        ]);

        $this->loteControlado->refresh();
        $this->assertEquals(45, $this->loteControlado->stock_actual);

        $this->assertDatabaseHas('registros_venta_controlados', [
            'producto_id'     => $this->productoControlado->id,
            'lote_id'         => $this->loteControlado->id,
            'tipo_movimiento' => RegistroVentaControlado::TIPO_AJUSTE_EGRESO,
            'cantidad'        => 5.00,
        ]);
    }

    public function test_ajuste_manual_rechaza_motivo_menor_a_cinco_caracteres(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('inventario.ajustar.store'), [
            'lote_id'     => $this->loteNormal->id,
            'stock_nuevo' => 80,
            'subtipo'     => 'ajuste_manual',
            'motivo'      => 'abc', // menor a 5 caracteres
        ]);

        $response->assertSessionHasErrors('motivo');
        $this->assertEquals(100, $this->loteNormal->fresh()->stock_actual);
    }

    public function test_crear_toma_de_inventario_genera_snapshot_correcto(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('inventario.conteos.store'), [
            'nombre'          => 'Toma General Anual 2026',
            'notas'           => 'Conteo físico general de fin de trimestre',
            'regimen_venta'   => 'todos',
            'idempotency_key' => 'toma_test_key_123',
        ]);

        $conteo = ConteoInventario::where('idempotency_key', 'toma_test_key_123')->first();
        $this->assertNotNull($conteo);
        $this->assertEquals('en_proceso', $conteo->estado);
        $this->assertEquals(2, $conteo->total_lotes); // loteNormal + loteControlado

        $this->assertDatabaseHas('detalles_conteo', [
            'conteo_id'     => $conteo->id,
            'lote_id'       => $this->loteNormal->id,
            'stock_sistema' => 100,
            'stock_fisico'  => null,
        ]);

        $this->assertDatabaseHas('detalles_conteo', [
            'conteo_id'     => $conteo->id,
            'lote_id'       => $this->loteControlado->id,
            'stock_sistema' => 50,
            'stock_fisico'  => null,
        ]);

        $response->assertRedirect(route('inventario.conteos.show', $conteo));
    }

    public function test_guardar_fila_ajax_actualiza_conteo_y_diferencia(): void
    {
        $conteo = ConteoInventario::create([
            'nombre'         => 'Toma Prueba AJAX',
            'estado'         => 'en_proceso',
            'usuario_id'     => $this->adminUser->id,
            'total_lotes'    => 1,
            'lotes_contados' => 0,
        ]);

        $detalle = DetalleConteo::create([
            'conteo_id'     => $conteo->id,
            'lote_id'       => $this->loteNormal->id,
            'producto_id'   => $this->productoNormal->id,
            'stock_sistema' => 100,
            'stock_fisico'  => null,
            'diferencia'    => 0,
            'ajustado'      => false,
        ]);

        $response = $this->actingAs($this->adminUser)->postJson(route('inventario.conteos.guardar-fila', $conteo), [
            'detalle_id'   => $detalle->id,
            'stock_fisico' => 96,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success'      => true,
            'stock_fisico' => 96,
            'diferencia'   => -4,
            'contados'     => 1,
        ]);

        $detalle->refresh();
        $this->assertEquals(96, $detalle->stock_fisico);
        $this->assertEquals(-4, $detalle->diferencia);
    }

    public function test_aprobar_toma_aplica_ajustes_en_kardex_con_subtipo_ajuste_toma(): void
    {
        $conteo = ConteoInventario::create([
            'nombre'         => 'Toma Trimestral',
            'estado'         => 'en_proceso',
            'usuario_id'     => $this->adminUser->id,
            'total_lotes'    => 2,
            'lotes_contados' => 2,
        ]);

        // Lote 1: Con diferencia (-5)
        $det1 = DetalleConteo::create([
            'conteo_id'     => $conteo->id,
            'lote_id'       => $this->loteNormal->id,
            'producto_id'   => $this->productoNormal->id,
            'stock_sistema' => 100,
            'stock_fisico'  => 95,
            'diferencia'    => -5,
            'ajustado'      => false,
        ]);

        // Lote 2: Controlado con diferencia (+2)
        $det2 = DetalleConteo::create([
            'conteo_id'     => $conteo->id,
            'lote_id'       => $this->loteControlado->id,
            'producto_id'   => $this->productoControlado->id,
            'stock_sistema' => 50,
            'stock_fisico'  => 52,
            'diferencia'    => 2,
            'ajustado'      => false,
        ]);

        $response = $this->actingAs($this->adminUser)->post(route('inventario.conteos.aprobar', $conteo));
        $response->assertRedirect(route('inventario.conteos.show', $conteo));

        $conteo->refresh();
        $this->assertEquals('completado', $conteo->estado);

        // Verificar Lote 1 actualizado
        $this->assertEquals(95, $this->loteNormal->fresh()->stock_actual);
        $this->assertDatabaseHas('movimientos_inventario', [
            'lote_id'         => $this->loteNormal->id,
            'tipo'            => 'salida',
            'subtipo'         => 'ajuste_manual',
            'origen'          => 'toma_inventario',
            'cantidad'        => -5,
            'stock_anterior'  => 100,
            'stock_posterior' => 95,
        ]);

        // Verificar Lote 2 actualizado y asentado en Libro MINSA
        $this->assertEquals(52, $this->loteControlado->fresh()->stock_actual);
        $this->assertDatabaseHas('movimientos_inventario', [
            'lote_id'         => $this->loteControlado->id,
            'tipo'            => 'entrada',
            'subtipo'         => 'ajuste_manual',
            'origen'          => 'toma_inventario',
            'cantidad'        => 2,
            'stock_anterior'  => 50,
            'stock_posterior' => 52,
        ]);

        $this->assertDatabaseHas('registros_venta_controlados', [
            'producto_id'     => $this->productoControlado->id,
            'lote_id'         => $this->loteControlado->id,
            'tipo_movimiento' => RegistroVentaControlado::TIPO_AJUSTE_INGRESO,
            'cantidad'        => 2.00,
        ]);
    }

    public function test_no_se_puede_aprobar_toma_con_lotes_sin_contar(): void
    {
        $conteo = ConteoInventario::create([
            'nombre'         => 'Toma Incompleta',
            'estado'         => 'en_proceso',
            'usuario_id'     => $this->adminUser->id,
            'total_lotes'    => 1,
            'lotes_contados' => 0,
        ]);

        DetalleConteo::create([
            'conteo_id'     => $conteo->id,
            'lote_id'       => $this->loteNormal->id,
            'producto_id'   => $this->productoNormal->id,
            'stock_sistema' => 100,
            'stock_fisico'  => null, // pendiente de contar
            'diferencia'    => 0,
            'ajustado'      => false,
        ]);

        $response = $this->actingAs($this->adminUser)->post(route('inventario.conteos.aprobar', $conteo));
        $response->assertSessionHas('error');

        $this->assertEquals('en_proceso', $conteo->fresh()->estado);
        $this->assertEquals(100, $this->loteNormal->fresh()->stock_actual);
    }

    public function test_cancelar_toma_en_proceso_no_altera_stock_ni_kardex(): void
    {
        $conteo = ConteoInventario::create([
            'nombre'         => 'Toma a Cancelar',
            'estado'         => 'en_proceso',
            'usuario_id'     => $this->adminUser->id,
            'total_lotes'    => 1,
            'lotes_contados' => 1,
        ]);

        DetalleConteo::create([
            'conteo_id'     => $conteo->id,
            'lote_id'       => $this->loteNormal->id,
            'producto_id'   => $this->productoNormal->id,
            'stock_sistema' => 100,
            'stock_fisico'  => 80,
            'diferencia'    => -20,
            'ajustado'      => false,
        ]);

        $response = $this->actingAs($this->adminUser)->post(route('inventario.conteos.cancelar', $conteo));
        $response->assertRedirect(route('inventario.conteos.index'));

        $this->assertEquals('cancelado', $conteo->fresh()->estado);
        $this->assertEquals(100, $this->loteNormal->fresh()->stock_actual);
        $this->assertDatabaseMissing('movimientos_inventario', [
            'motivo' => "Ajuste por toma física: {$conteo->nombre}",
        ]);
    }
}
