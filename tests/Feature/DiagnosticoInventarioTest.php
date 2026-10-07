<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Laboratorio;
use App\Models\Proveedor;
use App\Models\Lote;
use App\Models\MovimientoInventario;
use App\Models\ConteoInventario;
use App\Models\DetalleConteo;
use App\Models\PresentacionProducto;
use App\Support\RequestCache;
use App\Services\NotificacionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class DiagnosticoInventarioTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Categoria $categoria;
    protected Laboratorio $laboratorio;
    protected Proveedor $proveedor;

    protected function setUp(): void
    {
        parent::setUp();
        RequestCache::flushStatic();
        Cache::flush();

        // Seed roles & permissions & configuraciones
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
        $this->artisan('db:seed', ['--class' => 'ConfiguracionSeeder']);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('Admin');

        $this->categoria = Categoria::create(['nombre' => 'Antibióticos', 'activo' => true]);
        $this->laboratorio = Laboratorio::create(['nombre' => 'Bayer', 'codigo' => 'BAY', 'activo' => true]);
        $this->proveedor = Proveedor::create(['nombre' => 'Droguería Central', 'ruc' => 'J031000000001', 'activo' => true]);
    }

    public function test_diagnostico_inventario_y_almacen_completo()
    {
        // 1. Crear productos con presentaciones y lotes para benchmarking
        $productos = [];
        for ($i = 1; $i <= 10; $i++) {
            $p = Producto::create([
                'categoria_id'      => $this->categoria->id,
                'laboratorio_id'    => $this->laboratorio->id,
                'nombre'            => "Medicamento {$i}",
                'principio_activo'  => "Principio {$i}",
                'codigo_barra'      => "75000000000{$i}",
                'stock_minimo'      => 10,
                'stock_maximo'      => 100,
                'precio_compra'     => 10.00,
                'precio_venta'      => 15.00,
                'activo'            => true,
            ]);

            PresentacionProducto::create([
                'producto_id'               => $p->id,
                'nombre'                    => 'Unidad Base',
                'unidades_por_presentacion' => 1,
                'precio_compra'             => 10.00,
                'precio_venta'              => 15.00,
                'es_unidad_base'            => true,
                'activo'                    => true,
                'orden'                     => 1,
            ]);

            // Crear 2 lotes por producto (uno vigente, uno por vencer)
            Lote::create([
                'producto_id'       => $p->id,
                'proveedor_id'      => $this->proveedor->id,
                'numero_lote'       => "LOT-VIG-{$i}",
                'fecha_vencimiento' => now()->addDays(180)->toDateString(),
                'stock_inicial'     => 50,
                'stock_actual'      => 50,
                'precio_compra'     => 10.00,
                'activo'            => true,
            ]);

            Lote::create([
                'producto_id'       => $p->id,
                'proveedor_id'      => $this->proveedor->id,
                'numero_lote'       => "LOT-PV-{$i}",
                'fecha_vencimiento' => now()->addDays(20)->toDateString(),
                'stock_inicial'     => 5,
                'stock_actual'      => 5,
                'precio_compra'     => 10.00,
                'activo'            => true,
            ]);

            $productos[] = $p;
        }

        $mediciones = [];

        $profile = function (string $key, callable $fn) use (&$mediciones) {
            $queries = [];
            DB::listen(function ($query) use (&$queries) {
                $queries[] = [
                    'sql'      => $query->sql,
                    'time'     => $query->time,
                    'bindings' => $query->bindings,
                ];
            });

            $t0 = microtime(true);
            $response = $fn();
            $t1 = microtime(true);

            $sqls = array_map(fn ($q) => $q['sql'], $queries);
            $duplicates = array_filter(array_count_values($sqls), fn ($c) => $c > 1);

            $payloadKb = 0;
            if ($response instanceof \Illuminate\Http\Response || $response instanceof \Illuminate\Http\JsonResponse) {
                $payloadKb = round(strlen($response->getContent()) / 1024, 2);
            }

            $mediciones[$key] = [
                'total'      => count($queries),
                'time_ms'    => round(($t1 - $t0) * 1000, 2),
                'payload_kb' => $payloadKb,
                'duplicates' => array_keys($duplicates),
                'queries'    => $queries,
            ];

            return $response;
        };

        // 1. Listado de Lotes con filtros y orden FEFO
        $res1 = $profile('1_listado_lotes_index', function () {
            return $this->actingAs($this->admin)->get(route('inventario.lotes', [
                'orden' => 'vencimiento_asc',
            ]));
        });
        $res1->assertStatus(200);

        // 2. Formulario Crear Lote Manual
        $res2 = $profile('2_crear_lote_formulario', function () {
            return $this->actingAs($this->admin)->get(route('inventario.lotes.create'));
        });
        $res2->assertStatus(200);

        // 3. Guardar Lote Manual con Kardex
        $res3 = $profile('3_guardar_lote_manual', function () use ($productos) {
            return $this->actingAs($this->admin)->post(route('inventario.lotes.store'), [
                'producto_id'       => $productos[0]->id,
                'proveedor_id'      => $this->proveedor->id,
                'numero_lote'       => 'LOT-MANUAL-001',
                'fecha_vencimiento' => now()->addDays(90)->toDateString(),
                'cantidad'          => 20,
                'precio_compra'     => 10.00,
                'motivo'            => 'Donación inicial de laboratorio',
            ]);
        });
        $res3->assertRedirect(route('inventario.lotes'));

        // 4. Kardex General con filtros
        $res4 = $profile('4_kardex_general_movimientos', function () {
            return $this->actingAs($this->admin)->get(route('inventario.movimientos'));
        });
        $res4->assertStatus(200);

        // 5. Kardex Individual por Producto
        $res5 = $profile('5_kardex_individual_producto', function () use ($productos) {
            return $this->actingAs($this->admin)->get(route('inventario.kardex-producto', $productos[0]->id));
        });
        $res5->assertStatus(200);

        // 6. Formulario Ajustar Stock
        $res6 = $profile('6_ajustar_stock_formulario', function () {
            return $this->actingAs($this->admin)->get(route('inventario.ajustar'));
        });
        $res6->assertStatus(200);

        // 7. Registrar Ajuste Manual de Stock
        $loteParaAjuste = Lote::where('numero_lote', 'LOT-MANUAL-001')->first();
        $res7 = $profile('7_registrar_ajuste_stock', function () use ($loteParaAjuste) {
            return $this->actingAs($this->admin)->post(route('inventario.ajustar.store'), [
                'lote_id'     => $loteParaAjuste->id,
                'stock_nuevo' => 15,
                'motivo'      => 'Ajuste por conteo rápido',
                'subtipo'     => 'ajuste_manual',
            ]);
        });
        $res7->assertRedirect(route('inventario.movimientos'));

        // 8. Tomas de Inventario: Listado
        $res8 = $profile('8_tomas_inventario_index', function () {
            return $this->actingAs($this->admin)->get(route('inventario.tomas.index'));
        });
        $res8->assertStatus(200);

        // 9. Tomas de Inventario: Formulario Crear con Alcance
        $res9 = $profile('9_tomas_inventario_create', function () {
            return $this->actingAs($this->admin)->get(route('inventario.tomas.create'));
        });
        $res9->assertStatus(200);

        // 10. Conteo previo AJAX según filtros de alcance
        $res10 = $profile('10_tomas_conteo_previo_ajax', function () {
            return $this->actingAs($this->admin)->postJson(route('inventario.conteos.conteo-previo'), [
                'laboratorios_ids' => [$this->laboratorio->id],
                'categorias_ids'   => [$this->categoria->id],
                'regimen_venta'    => 'todos',
            ]);
        });
        $res10->assertStatus(200);

        // 11. Iniciar Toma y Crear Snapshot de Lotes
        $res11 = $profile('11_tomas_iniciar_snapshot', function () {
            return $this->actingAs($this->admin)->post(route('inventario.conteos.store'), [
                'nombre'           => 'Toma General Mensual',
                'notas'            => 'Auditoría mensual',
                'laboratorios_ids' => [$this->laboratorio->id],
                'categorias_ids'   => [$this->categoria->id],
                'regimen_venta'    => 'todos',
                'idempotency_key'  => 'toma_uuid_test_1',
            ]);
        });
        $conteo = ConteoInventario::where('idempotency_key', 'toma_uuid_test_1')->first();
        $this->assertNotNull($conteo);
        $res11->assertRedirect(route('inventario.conteos.show', $conteo));

        // 12. Vista de Conteo Físico
        $res12 = $profile('12_tomas_vista_conteo_show', function () use ($conteo) {
            return $this->actingAs($this->admin)->get(route('inventario.conteos.show', $conteo));
        });
        $res12->assertStatus(200);

        // 13. Guardar Fila AJAX de Conteo Físico
        $primerDetalle = DetalleConteo::where('conteo_id', $conteo->id)->first();
        $res13 = $profile('13_tomas_guardar_fila_ajax', function () use ($conteo, $primerDetalle) {
            return $this->actingAs($this->admin)->postJson(route('inventario.conteos.guardar-fila', $conteo), [
                'detalle_id'   => $primerDetalle->id,
                'stock_fisico' => $primerDetalle->stock_sistema - 2, // Diferencia de -2
            ]);
        });
        $res13->assertStatus(200);

        // 14. Guardar Avance Masivo de Conteo
        $res14 = $profile('14_tomas_guardar_avance_masivo', function () use ($conteo) {
            $detalles = DetalleConteo::where('conteo_id', $conteo->id)->get();
            $cantidades = [];
            foreach ($detalles as $det) {
                $cantidades[$det->id] = $det->stock_sistema; // Todos exactos excepto el primero
            }
            return $this->actingAs($this->admin)->post(route('inventario.conteos.guardar', $conteo), [
                'cantidades' => $cantidades,
            ]);
        });
        $res14->assertRedirect(route('inventario.conteos.show', $conteo));

        // 15. Aprobar Toma de Inventario y Aplicar Ajustes
        $res15 = $profile('15_tomas_aprobar_y_ajustar', function () use ($conteo) {
            return $this->actingAs($this->admin)->post(route('inventario.conteos.aprobar', $conteo));
        });
        $res15->assertRedirect(route('inventario.conteos.show', $conteo));

        // 16. Monitor de Inventario (Index)
        $res16 = $profile('16_inventario_monitor_index', function () {
            return $this->actingAs($this->admin)->get(route('inventario.index'));
        });
        $res16->assertStatus(200);

        // 17. Centro de Alertas
        $res17 = $profile('17_centro_alertas', function () {
            return $this->actingAs($this->admin)->get(route('inventario.alertas'));
        });
        $res17->assertStatus(200);

        // 18. Notificaciones Topbar (Campana 🔔) - 1ª Llamada
        $res18 = $profile('18_campana_notificaciones_1ra_llamada', function () {
            return $this->actingAs($this->admin)->getJson(route('api.notificaciones.resumen'));
        });
        $res18->assertStatus(200);
        $etag = $res18->headers->get('ETag');

        // 19. Notificaciones Topbar (Campana 🔔) - 2ª Llamada con ETag (Respuesta 304)
        $res19 = $profile('19_campana_notificaciones_etag_304', function () use ($etag) {
            return $this->actingAs($this->admin)->getJson(route('api.notificaciones.resumen'), [
                'If-None-Match' => $etag,
            ]);
        });
        $res19->assertStatus(304);

        // 20. Baja Automática de Lotes Vencidos
        $res20 = $profile('20_baja_automatica_lotes_vencidos', function () {
            return $this->actingAs($this->admin)->post(route('inventario.baja-vencidos'));
        });
        $res20->assertRedirect(route('inventario.alertas'));

        // Guardar métricas en storage/logs/diagnostico_inventario.json
        file_put_contents(
            storage_path('logs/diagnostico_inventario.json'),
            json_encode($mediciones, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        $this->assertFileExists(storage_path('logs/diagnostico_inventario.json'));
    }
}
