<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Laboratorio;
use App\Models\Proveedor;
use App\Models\Cliente;
use App\Models\Lote;
use App\Models\Receta;
use App\Models\RecetaDetalle;
use App\Models\RegistroVentaControlado;
use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\PresentacionProducto;
use App\Support\RequestCache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class DiagnosticoRecetasControladosTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $farmaceutico;
    protected Categoria $categoria;
    protected Laboratorio $laboratorio;
    protected Proveedor $proveedor;
    protected Cliente $cliente;
    protected Producto $productoControlado;
    protected Producto $productoSimple;
    protected Lote $loteControlado;

    protected function setUp(): void
    {
        parent::setUp();
        RequestCache::flushStatic();
        Cache::flush();
        Storage::fake('local');
        Storage::fake('public');

        // Seed roles & permissions
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
        $this->artisan('db:seed', ['--class' => 'ConfiguracionSeeder']);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('Admin');

        $this->farmaceutico = User::factory()->create();
        $this->farmaceutico->assignRole('Farmaceutico');

        $this->categoria = Categoria::create(['nombre' => 'Psicotrópicos', 'activo' => true]);
        $this->laboratorio = Laboratorio::create(['nombre' => 'Roche', 'codigo' => 'ROC', 'activo' => true]);
        $this->proveedor = Proveedor::create(['nombre' => 'Droguería Médica', 'ruc' => 'J031000000002', 'activo' => true]);
        $this->cliente = Cliente::create(['nombre' => 'Juan Pérez', 'documento' => '001-010190-0001A', 'activo' => true]);

        // Producto Controlado
        $this->productoControlado = Producto::create([
            'categoria_id'      => $this->categoria->id,
            'laboratorio_id'    => $this->laboratorio->id,
            'nombre'            => 'Clonazepam 2mg',
            'principio_activo'  => 'Clonazepam',
            'concentracion'     => '2mg',
            'codigo_barra'      => '7509999990001',
            'stock_minimo'      => 5,
            'stock_maximo'      => 50,
            'precio_compra'     => 20.00,
            'precio_venta'      => 35.00,
            'tipo_control'      => 'controlado',
            'requiere_receta'   => true,
            'activo'            => true,
        ]);

        PresentacionProducto::create([
            'producto_id'               => $this->productoControlado->id,
            'nombre'                    => 'Caja x 30 Tab',
            'unidades_por_presentacion' => 1,
            'precio_compra'             => 20.00,
            'precio_venta'              => 35.00,
            'es_unidad_base'            => true,
            'activo'                    => true,
            'orden'                     => 1,
        ]);

        $this->loteControlado = Lote::create([
            'producto_id'       => $this->productoControlado->id,
            'proveedor_id'      => $this->proveedor->id,
            'numero_lote'       => 'LOT-CLON-001',
            'fecha_vencimiento' => now()->addDays(365)->toDateString(),
            'stock_inicial'     => 100,
            'stock_actual'      => 100,
            'precio_compra'     => 20.00,
            'activo'            => true,
        ]);

        // Producto Simple con Receta
        $this->productoSimple = Producto::create([
            'categoria_id'      => $this->categoria->id,
            'laboratorio_id'    => $this->laboratorio->id,
            'nombre'            => 'Amoxicilina 500mg',
            'principio_activo'  => 'Amoxicilina',
            'concentracion'     => '500mg',
            'codigo_barra'      => '7509999990002',
            'stock_minimo'      => 10,
            'stock_maximo'      => 100,
            'precio_compra'     => 10.00,
            'precio_venta'      => 18.00,
            'tipo_control'      => 'venta_libre',
            'requiere_receta'   => true,
            'activo'            => true,
        ]);

        PresentacionProducto::create([
            'producto_id'               => $this->productoSimple->id,
            'nombre'                    => 'Caja x 20 Cápsulas',
            'unidades_por_presentacion' => 1,
            'precio_compra'             => 10.00,
            'precio_venta'              => 18.00,
            'es_unidad_base'            => true,
            'activo'                    => true,
            'orden'                     => 1,
        ]);
    }

    public function test_diagnostico_recetas_medicas_y_controlados_minsa_completo()
    {
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

        // 1. Listado de Recetas Médicas (`recetas.index`)
        $res1 = $profile('1_listado_recetas_index', function () {
            return $this->actingAs($this->admin)->get(route('recetas.index'));
        });
        $res1->assertStatus(200);

        // 2. Formulario Crear Receta Médica (`recetas.create`)
        $res2 = $profile('2_crear_receta_formulario', function () {
            return $this->actingAs($this->admin)->get(route('recetas.create'));
        });
        $res2->assertStatus(200);

        // 3. Guardar Receta Médica con Archivo Digitalizado (`recetas.store`)
        $file = UploadedFile::fake()->create('receta_medica_001.pdf', 150, 'application/pdf');
        $res3 = $profile('3_guardar_receta_con_archivo', function () use ($file) {
            return $this->actingAs($this->admin)->post(route('recetas.store'), [
                'cliente_id'          => $this->cliente->id,
                'paciente_nombre'     => 'Juan Pérez',
                'paciente_documento'  => '001-010190-0001A',
                'paciente_edad'       => 35,
                'medico_nombre'       => 'Dr. Roberto Mendoza',
                'medico_colegiatura'  => 'MINSA-12345',
                'medico_especialidad' => 'Psiquiatría',
                'institucion_salud'   => 'Hospital Militar',
                'numero_receta'       => 'REC-2026-0099',
                'fecha_emision'       => now()->toDateString(),
                'fecha_vencimiento'   => now()->addDays(30)->toDateString(),
                'tipo_receta'         => 'controlada',
                'archivo_receta'      => $file,
                'observaciones'       => 'Tratamiento por 30 días',
                'detalles'            => [
                    [
                        'producto_id'       => $this->productoControlado->id,
                        'cantidad_recetada' => 2,
                        'posologia'         => '1 tableta cada 12 horas',
                    ],
                ],
            ]);
        });
        $receta = Receta::where('numero_receta', 'REC-2026-0099')->first();
        $this->assertNotNull($receta);
        $res3->assertRedirect(route('recetas.show', $receta));

        // 4. Detalle y Trazabilidad de Receta (`recetas.show`)
        $res4 = $profile('4_ver_detalle_receta', function () use ($receta) {
            return $this->actingAs($this->admin)->get(route('recetas.show', $receta));
        });
        $res4->assertStatus(200);

        // 5. Formulario Editar Receta Médica (`recetas.edit`)
        $res5 = $profile('5_editar_receta_formulario', function () use ($receta) {
            return $this->actingAs($this->admin)->get(route('recetas.edit', $receta));
        });
        $res5->assertStatus(200);

        // 6. Actualizar Receta Médica (`recetas.update`)
        $res6 = $profile('6_actualizar_receta', function () use ($receta) {
            return $this->actingAs($this->admin)->put(route('recetas.update', $receta), [
                'cliente_id'          => $this->cliente->id,
                'paciente_nombre'     => 'Juan Pérez González',
                'paciente_documento'  => '001-010190-0001A',
                'paciente_edad'       => 35,
                'medico_nombre'       => 'Dr. Roberto Mendoza',
                'medico_colegiatura'  => 'MINSA-12345',
                'numero_receta'       => 'REC-2026-0099',
                'fecha_emision'       => now()->toDateString(),
                'fecha_vencimiento'   => now()->addDays(30)->toDateString(),
                'tipo_receta'         => 'controlada',
                'observaciones'       => 'Ajuste de posología',
            ]);
        });
        $res6->assertRedirect(route('recetas.show', $receta));

        // 7. Validar Receta (`recetas.validar`)
        $res7 = $profile('7_validar_receta_endpoint', function () use ($receta) {
            return $this->actingAs($this->admin)->postJson(route('recetas.validar', $receta));
        });
        $res7->assertStatus(200);
        $res7->assertJson(['valida' => true]);

        // 8. Búsqueda AJAX de Recetas Disponibles (1ª llamada)
        $res8 = $profile('8_buscar_recetas_ajax_1ra', function () {
            return $this->actingAs($this->admin)->getJson(route('api.recetas.buscar', ['q' => 'Pérez']));
        });
        $res8->assertStatus(200);

        // 9. Búsqueda AJAX de Recetas Disponibles (2ª llamada idéntica memoizada)
        $res9 = $profile('9_buscar_recetas_ajax_2da_memoizada', function () {
            return $this->actingAs($this->admin)->getJson(route('api.recetas.buscar', ['q' => 'Pérez']));
        });
        $res9->assertStatus(200);

        // 10. Descarga Segura de Archivo Digitalizado (`recetas.archivo`)
        $res10 = $profile('10_descarga_segura_archivo_receta', function () use ($receta) {
            return $this->actingAs($this->admin)->get(route('recetas.archivo', $receta));
        });
        $res10->assertStatus(200);
        $res10->assertHeader('X-Content-Type-Options', 'nosniff');

        // 11. Cambiar Estado de Receta (`recetas.estado`)
        $res11 = $profile('11_cambiar_estado_receta', function () use ($receta) {
            return $this->actingAs($this->admin)->post(route('recetas.estado', $receta), [
                'estado' => 'dispensada_parcial',
            ]);
        });
        $res11->assertRedirect(route('recetas.show', $receta));

        // 12. Asentar Movimiento en Libro Controlados MINSA (Simular Dispensación / Asiento Oficial)
        $asiento = RegistroVentaControlado::create([
            'tipo_movimiento'  => RegistroVentaControlado::TIPO_VENTA,
            'producto_id'      => $this->productoControlado->id,
            'lote_id'          => $this->loteControlado->id,
            'nivel_controlado' => 1,
            'paciente_nombre'  => 'Juan Pérez González',
            'paciente_cedula'  => '001-010190-0001A',
            'paciente_edad'    => 35,
            'medico_nombre'    => 'Dr. Roberto Mendoza',
            'medico_num_registro' => 'MINSA-12345',
            'cantidad'         => 1,
            'unidad'           => 'Caja x 30 Tab',
            'user_id'          => $this->admin->id,
            'diagnostico'      => 'Trastorno de Ansiedad Generalizada',
        ]);

        // 13. Bitácora de Medicamentos Controlados MINSA (`controlados.index` con KPI Cards)
        $res13 = $profile('13_controlados_index_bitacora', function () {
            return $this->actingAs($this->admin)->get(route('controlados.index'));
        });
        $res13->assertStatus(200);

        // 14. Detalle de Asiento Controlado MINSA (`controlados.show`)
        $res14 = $profile('14_controlados_show_detalle', function () use ($asiento) {
            return $this->actingAs($this->admin)->get(route('controlados.show', $asiento));
        });
        $res14->assertStatus(200);

        // 15. Libro Oficial Imprimible MINSA (`controlados.libro`)
        $res15 = $profile('15_controlados_libro_oficial', function () {
            return $this->actingAs($this->admin)->get(route('controlados.libro', [
                'desde' => now()->startOfMonth()->toDateString(),
                'hasta' => now()->toDateString(),
            ]));
        });
        $res15->assertStatus(200);

        // 16. Exportación Excel de Libro Controlados MINSA (`controlados.excel`)
        $res16 = $profile('16_controlados_exportar_excel', function () {
            return $this->actingAs($this->admin)->get(route('controlados.excel', [
                'desde' => now()->startOfMonth()->toDateString(),
                'hasta' => now()->toDateString(),
            ]));
        });
        $res16->assertStatus(200);

        // 17. Adjuntar Evidencia Digital al Asiento MINSA (`controlados.evidencia.store`)
        $fotoEvidencia = UploadedFile::fake()->image('evidencia_receta.jpg', 600, 600);
        $res17 = $profile('17_controlados_subir_evidencia', function () use ($asiento, $fotoEvidencia) {
            return $this->actingAs($this->admin)->post(route('controlados.evidencia.store', $asiento), [
                'foto_receta' => $fotoEvidencia,
            ]);
        });
        $res17->assertRedirect();

        // 18. Ver Evidencia Digital Segura (`controlados.evidencia`)
        $asiento->refresh();
        $res18 = $profile('18_controlados_ver_evidencia', function () use ($asiento) {
            return $this->actingAs($this->admin)->get(route('controlados.evidencia', $asiento));
        });
        $res18->assertStatus(200);

        // Guardar métricas en storage/logs/diagnostico_recetas_controlados.json
        file_put_contents(
            storage_path('logs/diagnostico_recetas_controlados.json'),
            json_encode($mediciones, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        $this->assertFileExists(storage_path('logs/diagnostico_recetas_controlados.json'));
    }
}
