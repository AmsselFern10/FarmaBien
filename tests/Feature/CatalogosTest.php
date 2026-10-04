<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Cliente;
use App\Models\Proveedor;
use App\Models\Laboratorio;
use App\Models\Categoria;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CatalogosTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $usuarioSinPermisos;

    protected function setUp(): void
    {
        parent::setUp();

        // Crear permisos requeridos
        $permisos = [
            'ver clientes', 'crear clientes', 'editar clientes', 'desactivar clientes',
            'ver proveedores', 'crear proveedores', 'editar proveedores', 'desactivar proveedores',
            'ver laboratorios', 'crear laboratorios', 'editar laboratorios', 'desactivar laboratorios',
            'ver categorias', 'crear categorias', 'editar categorias', 'desactivar categorias',
        ];

        foreach ($permisos as $permiso) {
            Permission::findOrCreate($permiso, 'web');
        }

        $roleAdmin = Role::findOrCreate('Administrador', 'web');
        $roleAdmin->syncPermissions($permisos);

        $this->admin = User::factory()->create();
        $this->admin->assignRole($roleAdmin);

        $this->usuarioSinPermisos = User::factory()->create();
    }

    /* ══════════════════════════════════════════════════════════════
       1. CLIENTES & PACIENTES
       ══════════════════════════════════════════════════════════════ */

    public function test_admin_puede_listar_clientes(): void
    {
        Cliente::factory()->create(['nombre' => 'Juan Pérez']);

        $response = $this->actingAs($this->admin)->get(route('clientes.index'));

        $response->assertStatus(200);
        $response->assertSee('Juan Pérez');
    }

    public function test_admin_puede_crear_cliente_con_validacion_y_normalizacion(): void
    {
        $payload = [
            'nombre'    => '  María Gómez  ',
            'documento' => '001-150290-0021A',
            'telefono'  => '+505 8888-9999',
            'email'     => 'maria@gmail.com',
            'direccion' => 'Managua, Nicaragua',
            'activo'    => 1,
        ];

        $response = $this->actingAs($this->admin)->post(route('clientes.store'), $payload);

        $response->assertRedirect(route('clientes.index'));
        $this->assertDatabaseHas('clientes', [
            'nombre'    => 'María Gómez',
            'documento' => '001-150290-0021A',
            'activo'    => true,
        ]);
    }

    public function test_valida_unicidad_documento_en_cliente(): void
    {
        Cliente::factory()->create(['documento' => '001-150290-0021A']);

        $response = $this->actingAs($this->admin)->post(route('clientes.store'), [
            'nombre'    => 'Otro Cliente',
            'documento' => '001-150290-0021A',
        ]);

        $response->assertSessionHasErrors(['documento']);
    }

    public function test_admin_puede_actualizar_cliente_con_mismo_documento(): void
    {
        $cliente = Cliente::factory()->create([
            'nombre'    => 'Pedro Rizo',
            'documento' => '001-111111-0001A',
        ]);

        $response = $this->actingAs($this->admin)->put(route('clientes.update', $cliente), [
            'nombre'    => 'Pedro Rizo Actualizado',
            'documento' => '001-111111-0001A',
        ]);

        $response->assertRedirect(route('clientes.index'));
        $this->assertDatabaseHas('clientes', [
            'id'        => $cliente->id,
            'nombre'    => 'Pedro Rizo Actualizado',
            'documento' => '001-111111-0001A',
        ]);
    }

    public function test_admin_puede_alternar_estado_cliente(): void
    {
        $cliente = Cliente::factory()->create(['activo' => true]);

        $response = $this->actingAs($this->admin)->delete(route('clientes.destroy', $cliente));

        $response->assertRedirect(route('clientes.index'));
        $this->assertDatabaseHas('clientes', [
            'id'     => $cliente->id,
            'activo' => false,
        ]);
    }

    public function test_busqueda_ajax_de_clientes(): void
    {
        Cliente::factory()->create(['nombre' => 'Carlos Blandón', 'documento' => '441-200585-0003K', 'activo' => true]);

        $response = $this->actingAs($this->admin)->get(route('api.clientes.buscar-ajax', ['q' => 'Blandón']));

        $response->assertStatus(200);
        $response->assertJsonFragment(['nombre' => 'Carlos Blandón']);
    }

    /* ══════════════════════════════════════════════════════════════
       2. PROVEEDORES
       ══════════════════════════════════════════════════════════════ */

    public function test_admin_puede_listar_proveedores(): void
    {
        Proveedor::factory()->create(['nombre' => 'Droguería Central']);

        $response = $this->actingAs($this->admin)->get(route('proveedores.index'));

        $response->assertStatus(200);
        $response->assertSee('Droguería Central');
    }

    public function test_admin_puede_crear_proveedor_con_ruc_nicaraguense_14_caracteres(): void
    {
        $payload = [
            'nombre'   => 'Distribuidora Ramos S.A.',
            'ruc'      => 'J0310000012345',
            'contacto' => 'Lic. Roberto Ramos',
            'telefono' => '+505 2270-1122',
            'email'    => 'ventas@ramos.ni',
            'activo'   => 1,
        ];

        $response = $this->actingAs($this->admin)->post(route('proveedores.store'), $payload);

        $response->assertRedirect(route('proveedores.index'));
        $this->assertDatabaseHas('proveedores', [
            'nombre' => 'Distribuidora Ramos S.A.',
            'ruc'    => 'J0310000012345',
            'activo' => true,
        ]);
    }

    public function test_valida_unicidad_ruc_en_proveedor(): void
    {
        Proveedor::factory()->create(['ruc' => 'J0310000012345']);

        $response = $this->actingAs($this->admin)->post(route('proveedores.store'), [
            'nombre' => 'Proveedor Duplicado',
            'ruc'    => 'J0310000012345',
        ]);

        $response->assertSessionHasErrors(['ruc']);
    }

    public function test_admin_puede_actualizar_proveedor(): void
    {
        $proveedor = Proveedor::factory()->create([
            'nombre' => 'Proveedor Inicial',
            'ruc'    => 'J0310000055555',
        ]);

        $response = $this->actingAs($this->admin)->put(route('proveedores.update', $proveedor), [
            'nombre' => 'Proveedor Modificado',
            'ruc'    => 'J0310000055555',
        ]);

        $response->assertRedirect(route('proveedores.index'));
        $this->assertDatabaseHas('proveedores', [
            'id'     => $proveedor->id,
            'nombre' => 'Proveedor Modificado',
        ]);
    }

    public function test_admin_puede_alternar_estado_proveedor(): void
    {
        $proveedor = Proveedor::factory()->create(['activo' => true]);

        $response = $this->actingAs($this->admin)->delete(route('proveedores.destroy', $proveedor));

        $response->assertRedirect(route('proveedores.index'));
        $this->assertDatabaseHas('proveedores', [
            'id'     => $proveedor->id,
            'activo' => false,
        ]);
    }

    public function test_busqueda_ajax_de_proveedores(): void
    {
        Proveedor::factory()->create(['nombre' => 'Farmacéutica Continental', 'ruc' => 'J0310000099999', 'activo' => true]);

        $response = $this->actingAs($this->admin)->get(route('api.proveedores.buscar-ajax', ['q' => 'Continental']));

        $response->assertStatus(200);
        $response->assertJsonFragment(['nombre' => 'Farmacéutica Continental']);
    }

    /* ══════════════════════════════════════════════════════════════
       3. LABORATORIOS FARMACÉUTICOS
       ══════════════════════════════════════════════════════════════ */

    public function test_admin_puede_listar_laboratorios(): void
    {
        Laboratorio::factory()->create(['nombre' => 'Laboratorios Roemmers']);

        $response = $this->actingAs($this->admin)->get(route('laboratorios.index'));

        $response->assertStatus(200);
        $response->assertSee('Laboratorios Roemmers');
    }

    public function test_admin_puede_crear_laboratorio(): void
    {
        $payload = [
            'nombre'      => 'Laboratorios Stein',
            'codigo'      => 'LAB-STE',
            'pais_origen' => 'Costa Rica',
            'activo'      => 1,
        ];

        $response = $this->actingAs($this->admin)->post(route('laboratorios.store'), $payload);

        $response->assertRedirect(route('laboratorios.index'));
        $this->assertDatabaseHas('laboratorios', [
            'nombre' => 'Laboratorios Stein',
            'codigo' => 'LAB-STE',
            'activo' => true,
        ]);
    }

    public function test_valida_unicidad_nombre_y_codigo_en_laboratorio(): void
    {
        Laboratorio::factory()->create(['nombre' => 'Laboratorios Stein', 'codigo' => 'LAB-STE']);

        $response = $this->actingAs($this->admin)->post(route('laboratorios.store'), [
            'nombre' => 'Laboratorios Stein',
            'codigo' => 'LAB-STE',
        ]);

        $response->assertSessionHasErrors(['nombre', 'codigo']);
    }

    public function test_admin_puede_actualizar_laboratorio(): void
    {
        $lab = Laboratorio::factory()->create(['nombre' => 'Lab Antiguo', 'codigo' => 'LAB-ANT']);

        $response = $this->actingAs($this->admin)->put(route('laboratorios.update', $lab), [
            'nombre' => 'Lab Renombrado',
            'codigo' => 'LAB-ANT',
        ]);

        $response->assertRedirect(route('laboratorios.index'));
        $this->assertDatabaseHas('laboratorios', [
            'id'     => $lab->id,
            'nombre' => 'Lab Renombrado',
        ]);
    }

    public function test_admin_puede_alternar_estado_laboratorio(): void
    {
        $lab = Laboratorio::factory()->create(['activo' => true]);

        $response = $this->actingAs($this->admin)->delete(route('laboratorios.destroy', $lab));

        $response->assertRedirect(route('laboratorios.index'));
        $this->assertDatabaseHas('laboratorios', [
            'id'     => $lab->id,
            'activo' => false,
        ]);
    }

    public function test_busqueda_ajax_de_laboratorios(): void
    {
        Laboratorio::factory()->create(['nombre' => 'Laboratorios Vijosa', 'activo' => true]);

        $response = $this->actingAs($this->admin)->get(route('api.laboratorios.buscar-ajax', ['q' => 'Vijosa']));

        $response->assertStatus(200);
        $response->assertJsonFragment(['nombre' => 'Laboratorios Vijosa']);
    }

    /* ══════════════════════════════════════════════════════════════
       4. CATEGORÍAS & GRUPOS TERAPÉUTICOS
       ══════════════════════════════════════════════════════════════ */

    public function test_admin_puede_listar_categorias(): void
    {
        Categoria::factory()->create(['nombre' => 'Antibióticos']);

        $response = $this->actingAs($this->admin)->get(route('categorias.index'));

        $response->assertStatus(200);
        $response->assertSee('Antibióticos');
    }

    public function test_admin_puede_crear_categoria(): void
    {
        $payload = [
            'nombre'      => 'Antihistamínicos',
            'descripcion' => 'Para alergias y cuadros respiratorios',
            'activo'      => 1,
        ];

        $response = $this->actingAs($this->admin)->post(route('categorias.store'), $payload);

        $response->assertRedirect(route('categorias.index'));
        $this->assertDatabaseHas('categorias', [
            'nombre' => 'Antihistamínicos',
            'activo' => true,
        ]);
    }

    public function test_valida_unicidad_nombre_en_categoria(): void
    {
        Categoria::factory()->create(['nombre' => 'Antibióticos']);

        $response = $this->actingAs($this->admin)->post(route('categorias.store'), [
            'nombre' => 'Antibióticos',
        ]);

        $response->assertSessionHasErrors(['nombre']);
    }

    public function test_admin_puede_actualizar_categoria(): void
    {
        $cat = Categoria::factory()->create(['nombre' => 'Antiinflamatorios']);

        $response = $this->actingAs($this->admin)->put(route('categorias.update', $cat), [
            'nombre'      => 'Antiinflamatorios y Analgésicos',
            'descripcion' => 'Actualizado',
        ]);

        $response->assertRedirect(route('categorias.index'));
        $this->assertDatabaseHas('categorias', [
            'id'     => $cat->id,
            'nombre' => 'Antiinflamatorios y Analgésicos',
        ]);
    }

    public function test_admin_puede_alternar_estado_categoria(): void
    {
        $cat = Categoria::factory()->create(['activo' => true]);

        $response = $this->actingAs($this->admin)->delete(route('categorias.destroy', $cat));

        $response->assertRedirect(route('categorias.index'));
        $this->assertDatabaseHas('categorias', [
            'id'     => $cat->id,
            'activo' => false,
        ]);
    }

    public function test_busqueda_ajax_de_categorias(): void
    {
        Categoria::factory()->create(['nombre' => 'Cardiovasculares', 'activo' => true]);

        $response = $this->actingAs($this->admin)->get(route('api.categorias.buscar-ajax', ['q' => 'Cardio']));

        $response->assertStatus(200);
        $response->assertJsonFragment(['nombre' => 'Cardiovasculares']);
    }

    /* ══════════════════════════════════════════════════════════════
       5. CONTROL DE ACCESO Y PERMISOS (403 FORBIDDEN)
       ══════════════════════════════════════════════════════════════ */

    public function test_usuario_sin_permisos_recibe_403_al_intentar_modificar_catalogos(): void
    {
        $cliente = Cliente::factory()->create();
        $proveedor = Proveedor::factory()->create();

        // Intento de listar clientes sin permiso
        $this->actingAs($this->usuarioSinPermisos)
            ->get(route('clientes.index'))
            ->assertStatus(403);

        // Intento de crear proveedor sin permiso
        $this->actingAs($this->usuarioSinPermisos)
            ->post(route('proveedores.store'), ['nombre' => 'Test', 'ruc' => '12345'])
            ->assertStatus(403);

        // Intento de eliminar cliente sin permiso
        $this->actingAs($this->usuarioSinPermisos)
            ->delete(route('clientes.destroy', $cliente))
            ->assertStatus(403);

        // Intento de eliminar proveedor sin permiso
        $this->actingAs($this->usuarioSinPermisos)
            ->delete(route('proveedores.destroy', $proveedor))
            ->assertStatus(403);
    }
}
