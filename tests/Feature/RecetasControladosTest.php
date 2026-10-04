<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use App\Models\User;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Laboratorio;
use App\Models\Cliente;
use App\Models\Receta;
use App\Models\RecetaDetalle;
use App\Models\Lote;
use App\Models\RegistroVentaControlado;
use App\Services\RecetaService;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use DomainException;
use Exception;

class RecetasControladosTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected RecetaService $recetaService;
    protected Producto $productoControlado;

    protected function setUp(): void
    {
        parent::setUp();

        $this->recetaService = app(RecetaService::class);

        // Crear permisos
        Permission::firstOrCreate(['name' => 'ver recetas']);
        Permission::firstOrCreate(['name' => 'registrar recetas']);
        Permission::firstOrCreate(['name' => 'validar recetas']);

        $role = Role::firstOrCreate(['name' => 'Farmaceutico']);
        $role->givePermissionTo(['ver recetas', 'registrar recetas', 'validar recetas']);

        $this->user = User::factory()->create();
        $this->user->assignRole('Farmaceutico');

        $categoria = Categoria::create(['nombre' => 'Psicotrópicos']);
        $laboratorio = Laboratorio::create(['nombre' => 'Laboratorio Farma']);

        $this->productoControlado = Producto::create([
            'nombre'           => 'Clonazepam 2mg',
            'codigo_barra'     => '7750000000099',
            'categoria_id'     => $categoria->id,
            'laboratorio_id'   => $laboratorio->id,
            'precio_compra'    => 50.00,
            'precio_venta'     => 85.00,
            'stock_minimo'     => 5,
            'tipo_control'     => 'controlado',
            'requiere_receta'  => true,
            'activo'           => true,
        ]);
    }

    public function test_puede_registrar_receta_medica_correctamente(): void
    {
        $cliente = Cliente::create([
            'nombre'    => 'Juan Pérez',
            'documento' => '001-010190-0001A',
        ]);

        $data = [
            'cliente_id'          => $cliente->id,
            'paciente_nombre'     => 'Juan Pérez',
            'paciente_documento'  => '001-010190-0001A',
            'paciente_edad'       => 35,
            'medico_nombre'       => 'Dr. Roberto Mendoza',
            'medico_colegiatura'  => 'MINSA-12345',
            'medico_especialidad' => 'Psiquiatría',
            'institucion_salud'   => 'Hospital Central',
            'numero_receta'       => 'REC-2026-0001',
            'fecha_emision'       => now()->toDateString(),
            'fecha_vencimiento'   => now()->addDays(30)->toDateString(),
            'tipo_receta'         => 'retenida',
            'detalles'            => [
                [
                    'producto_id'       => $this->productoControlado->id,
                    'cantidad_recetada' => 30,
                    'posologia'         => '1 tableta cada 24 horas por las noches',
                ]
            ]
        ];

        $receta = $this->recetaService->registrarReceta($data);

        $this->assertDatabaseHas('recetas', [
            'id'            => $receta->id,
            'numero_receta' => 'REC-2026-0001',
            'estado'        => 'pendiente',
        ]);

        $this->assertDatabaseHas('receta_detalles', [
            'receta_id'           => $receta->id,
            'producto_id'         => $this->productoControlado->id,
            'cantidad_recetada'   => 30,
            'cantidad_dispensada' => 0,
        ]);

        $this->assertEquals(30, $receta->saldo_pendiente_total);
    }

    public function test_dispensar_medicamento_actualiza_saldo_y_estado_receta(): void
    {
        $receta = Receta::create([
            'paciente_nombre'    => 'María López',
            'medico_nombre'      => 'Dr. Carlos Vega',
            'medico_colegiatura' => 'MINSA-9988',
            'numero_receta'      => 'REC-DISP-01',
            'fecha_emision'      => now()->toDateString(),
            'fecha_vencimiento'  => now()->addDays(15)->toDateString(),
            'estado'             => 'pendiente',
        ]);

        $detalle = RecetaDetalle::create([
            'receta_id'           => $receta->id,
            'producto_id'         => $this->productoControlado->id,
            'cantidad_recetada'   => 20,
            'cantidad_dispensada' => 0,
        ]);

        // 1. Dispensación parcial de 10 unidades
        $this->recetaService->dispensarMedicamento($detalle->id, 10);
        $detalle->refresh();
        $receta->refresh();

        $this->assertEquals(10, $detalle->cantidad_dispensada);
        $this->assertEquals(10, $detalle->pendiente_dispensar);
        $this->assertEquals('dispensada_parcial', $receta->estado);

        // 2. Dispensación restante de 10 unidades
        $this->recetaService->dispensarMedicamento($detalle->id, 10);
        $detalle->refresh();
        $receta->refresh();

        $this->assertEquals(20, $detalle->cantidad_dispensada);
        $this->assertEquals(0, $detalle->pendiente_dispensar);
        $this->assertEquals('dispensada_total', $receta->estado);
    }

    public function test_no_se_puede_dispensar_mas_de_la_cantidad_recetada(): void
    {
        $receta = Receta::create([
            'paciente_nombre'    => 'Carlos Ruiz',
            'medico_nombre'      => 'Dra. Ana Solís',
            'medico_colegiatura' => 'MINSA-7766',
            'numero_receta'      => 'REC-EXCEDER-01',
            'fecha_emision'      => now()->toDateString(),
            'fecha_vencimiento'  => now()->addDays(10)->toDateString(),
            'estado'             => 'pendiente',
        ]);

        $detalle = RecetaDetalle::create([
            'receta_id'           => $receta->id,
            'producto_id'         => $this->productoControlado->id,
            'cantidad_recetada'   => 15,
            'cantidad_dispensada' => 10,
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('excede el saldo pendiente');

        // Intentar dispensar 10 cuando solo quedan 5 disponibles
        $this->recetaService->dispensarMedicamento($detalle->id, 10);
    }

    public function test_no_se_puede_dispensar_receta_vencida_o_anulada(): void
    {
        // Receta Vencida
        $recetaVencida = Receta::create([
            'paciente_nombre'    => 'Elena Ortiz',
            'medico_nombre'      => 'Dr. Manuel Paz',
            'medico_colegiatura' => 'MINSA-3322',
            'numero_receta'      => 'REC-VENCIDA-01',
            'fecha_emision'      => now()->subDays(60)->toDateString(),
            'fecha_vencimiento'  => now()->subDays(15)->toDateString(),
            'estado'             => 'pendiente',
        ]);

        $detalleVencido = RecetaDetalle::create([
            'receta_id'           => $recetaVencida->id,
            'producto_id'         => $this->productoControlado->id,
            'cantidad_recetada'   => 10,
            'cantidad_dispensada' => 0,
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('venció el');

        $this->recetaService->dispensarMedicamento($detalleVencido->id, 5);
    }

    public function test_revertir_dispensacion_restablece_saldo_y_estado(): void
    {
        $receta = Receta::create([
            'paciente_nombre'    => 'Gabriel Silva',
            'medico_nombre'      => 'Dr. José Luna',
            'medico_colegiatura' => 'MINSA-5544',
            'numero_receta'      => 'REC-REV-01',
            'fecha_emision'      => now()->toDateString(),
            'fecha_vencimiento'  => now()->addDays(20)->toDateString(),
            'estado'             => 'dispensada_total',
        ]);

        $detalle = RecetaDetalle::create([
            'receta_id'           => $receta->id,
            'producto_id'         => $this->productoControlado->id,
            'cantidad_recetada'   => 10,
            'cantidad_dispensada' => 10,
        ]);

        // Revertir 5 unidades
        $this->recetaService->revertirDispensacion($detalle->id, 5);
        $detalle->refresh();
        $receta->refresh();

        $this->assertEquals(5, $detalle->cantidad_dispensada);
        $this->assertEquals(5, $detalle->pendiente_dispensar);
        $this->assertEquals('dispensada_parcial', $receta->estado);

        // Revertir las 5 restantes
        $this->recetaService->revertirDispensacion($detalle->id, 5);
        $detalle->refresh();
        $receta->refresh();

        $this->assertEquals(0, $detalle->cantidad_dispensada);
        $this->assertEquals(10, $detalle->pendiente_dispensar);
        $this->assertEquals('pendiente', $receta->estado);
    }

    public function test_registro_venta_controlado_es_inmutable_ante_eliminacion(): void
    {
        $lote = Lote::create([
            'producto_id'       => $this->productoControlado->id,
            'numero_lote'       => 'LOT-CTRL-01',
            'fecha_vencimiento' => now()->addYear()->toDateString(),
            'stock_inicial'     => 50,
            'stock_actual'      => 50,
            'precio_compra'     => 50.00,
            'estado'            => 'activo',
        ]);

        $registro = RegistroVentaControlado::create([
            'tipo_movimiento'  => RegistroVentaControlado::TIPO_COMPRA,
            'producto_id'      => $this->productoControlado->id,
            'lote_id'          => $lote->id,
            'nivel_controlado' => 2,
            'cantidad'         => 50,
            'unidad'           => 'cajas',
            'user_id'          => $this->user->id,
        ]);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('son inmutables y no pueden ser eliminados');

        $registro->delete();
    }

    public function test_registro_venta_controlado_es_inmutable_ante_modificacion_de_campos_fiscales(): void
    {
        $lote = Lote::create([
            'producto_id'       => $this->productoControlado->id,
            'numero_lote'       => 'LOT-CTRL-02',
            'fecha_vencimiento' => now()->addYear()->toDateString(),
            'stock_inicial'     => 30,
            'stock_actual'      => 30,
            'precio_compra'     => 50.00,
            'estado'            => 'activo',
        ]);

        $registro = RegistroVentaControlado::create([
            'tipo_movimiento'  => RegistroVentaControlado::TIPO_COMPRA,
            'producto_id'      => $this->productoControlado->id,
            'lote_id'          => $lote->id,
            'cantidad'         => 30,
            'user_id'          => $this->user->id,
        ]);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('No se permite modificar datos contables');

        // Intentar alterar la cantidad fiscalizada retroactivamente
        $registro->update(['cantidad' => 15]);
    }

    public function test_registro_venta_controlado_permite_adjuntar_evidencia_sin_violar_inmutabilidad(): void
    {
        $lote = Lote::create([
            'producto_id'       => $this->productoControlado->id,
            'numero_lote'       => 'LOT-CTRL-03',
            'fecha_vencimiento' => now()->addYear()->toDateString(),
            'stock_inicial'     => 20,
            'stock_actual'      => 20,
            'precio_compra'     => 50.00,
            'estado'            => 'activo',
        ]);

        $registro = RegistroVentaControlado::create([
            'tipo_movimiento'  => RegistroVentaControlado::TIPO_VENTA,
            'producto_id'      => $this->productoControlado->id,
            'lote_id'          => $lote->id,
            'cantidad'         => 5,
            'user_id'          => $this->user->id,
        ]);

        // Actualizar únicamente la ruta de evidencia escaneada
        $registro->update(['ruta_foto_receta' => 'recetas_controlados/evidencia_123.jpg']);
        $registro->refresh();

        $this->assertEquals('recetas_controlados/evidencia_123.jpg', $registro->ruta_foto_receta);
    }

    public function test_comando_verificar_controlados_ejecuta_correctamente(): void
    {
        $this->artisan('farma:verificar-controlados')
            ->assertExitCode(0)
            ->expectsOutputToContain('AUDITORÍA DE MEDICAMENTOS CONTROLADOS');
    }

    public function test_descarga_segura_de_archivo_de_receta(): void
    {
        Storage::fake('local');
        $pdfContent = "%PDF-1.4\n1 0 obj\n<<\n/Type /Catalog\n>>\nendobj\ntrailer\n<<\n/Root 1 0 R\n>>\n%%EOF";
        $file = UploadedFile::fake()->createWithContent('receta_medica.pdf', $pdfContent);
        $path = $file->store('recetas', 'local');

        $receta = Receta::create([
            'paciente_nombre'    => 'Ignacio Ramos',
            'medico_nombre'      => 'Dr. Fernando Bravo',
            'medico_colegiatura' => 'MINSA-1100',
            'numero_receta'      => 'REC-SECURE-01',
            'fecha_emision'      => now()->toDateString(),
            'archivo_receta'     => $path,
            'estado'             => 'pendiente',
        ]);

        $response = $this->actingAs($this->user)->get(route('recetas.archivo', $receta));

        $response->assertStatus(200);
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
    }
}
