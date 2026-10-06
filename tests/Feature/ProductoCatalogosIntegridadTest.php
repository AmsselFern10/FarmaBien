<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Laboratorio;
use App\Models\PresentacionProducto;
use App\Models\PrecioVenta;
use App\Services\CategoriaService;
use App\Services\LaboratorioService;
use App\Services\PrecioVentaService;
use App\Services\ProductoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ProductoCatalogosIntegridadTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Categoria $categoria;
    protected Laboratorio $laboratorio;

    protected function setUp(): void
    {
        parent::setUp();

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        Permission::create(['name' => 'ver productos']);
        Permission::create(['name' => 'crear productos']);
        Permission::create(['name' => 'editar productos']);
        Permission::create(['name' => 'desactivar productos']);
        Permission::create(['name' => 'ver categorias']);
        Permission::create(['name' => 'crear categorias']);
        Permission::create(['name' => 'editar categorias']);
        Permission::create(['name' => 'desactivar categorias']);
        Permission::create(['name' => 'ver laboratorios']);
        Permission::create(['name' => 'crear laboratorios']);
        Permission::create(['name' => 'editar laboratorios']);
        Permission::create(['name' => 'desactivar laboratorios']);

        $roleAdmin = Role::create(['name' => 'administrador']);
        $roleAdmin->givePermissionTo(Permission::all());

        $this->admin = User::factory()->create();
        $this->admin->assignRole($roleAdmin);

        $this->categoria = Categoria::create([
            'nombre' => 'Analgésicos',
            'activo' => true,
        ]);

        $this->laboratorio = Laboratorio::create([
            'nombre' => 'Laboratorio Ramos',
            'codigo' => 'LAB-RAMOS',
            'activo' => true,
        ]);
    }

    public function test_sincronizacion_automatica_precio_base_y_presentacion_en_scd_tipo_2()
    {
        $productoService = app(ProductoService::class);

        // 1. Crear producto con precio 50.00
        $producto = $productoService->crearProducto([
            'nombre'        => 'Paracetamol 500mg',
            'categoria_id'  => $this->categoria->id,
            'laboratorio_id'=> $this->laboratorio->id,
            'precio_compra' => 20.00,
            'precio_venta'  => 50.00,
            'tipo_control'  => 'venta_libre',
            'activo'        => true,
            'codigo_barra'  => '775000111222',
        ]);

        // Verificar que la presentación base se creó con el mismo precio y heredó el código de barras
        $unidadBase = $producto->presentaciones()->where('es_unidad_base', true)->first();
        $this->assertNotNull($unidadBase);
        $this->assertEquals(50.00, (float)$unidadBase->precio_venta);
        $this->assertEquals('775000111222', $unidadBase->codigo_barras);

        // 2. Actualizar precio vía PrecioVentaService::actualizarPrecioInline a 65.00
        $precioVentaService = app(PrecioVentaService::class);
        $this->actingAs($this->admin);
        $precioVentaService->actualizarPrecioInline($producto, 65.00, 'Ajuste inflacionario');

        $producto->refresh();
        $unidadBase->refresh();

        $this->assertEquals(65.00, (float)$producto->precio_venta);
        $this->assertEquals(65.00, (float)$unidadBase->precio_venta);

        // Verificar historial SCD Tipo 2
        $historialProducto = PrecioVenta::where('producto_id', $producto->id)
            ->whereNull('presentacion_id')
            ->whereNull('vigente_hasta')
            ->first();
        $this->assertNotNull($historialProducto);
        $this->assertEquals(65.00, (float)$historialProducto->precio);

        $historialPresentacion = PrecioVenta::where('producto_id', $producto->id)
            ->where('presentacion_id', $unidadBase->id)
            ->whereNull('vigente_hasta')
            ->first();
        $this->assertNotNull($historialPresentacion);
        $this->assertEquals(65.00, (float)$historialPresentacion->precio);
    }

    public function test_bloqueo_desactivacion_categoria_con_productos_activos()
    {
        $producto = Producto::create([
            'nombre'        => 'Ibuprofeno 400mg',
            'categoria_id'  => $this->categoria->id,
            'laboratorio_id'=> $this->laboratorio->id,
            'precio_compra' => 10.00,
            'precio_venta'  => 20.00,
            'tipo_control'  => 'venta_libre',
            'activo'        => true,
        ]);

        $categoriaService = app(CategoriaService::class);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("No se puede desactivar la categoría '{$this->categoria->nombre}' porque cuenta con medicamentos activos asociados.");

        $categoriaService->toggleEstado($this->categoria);
    }

    public function test_bloqueo_desactivacion_laboratorio_con_productos_activos()
    {
        $producto = Producto::create([
            'nombre'        => 'Amoxicilina 500mg',
            'categoria_id'  => $this->categoria->id,
            'laboratorio_id'=> $this->laboratorio->id,
            'precio_compra' => 15.00,
            'precio_venta'  => 30.00,
            'tipo_control'  => 'venta_libre',
            'activo'        => true,
        ]);

        $laboratorioService = app(LaboratorioService::class);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("No se puede desactivar el laboratorio '{$this->laboratorio->nombre}' porque cuenta con medicamentos activos asociados.");

        $laboratorioService->toggleEstado($this->laboratorio);
    }

    public function test_validacion_cruzada_de_codigo_de_barras()
    {
        Producto::create([
            'nombre'        => 'Aspirina 100mg',
            'categoria_id'  => $this->categoria->id,
            'laboratorio_id'=> $this->laboratorio->id,
            'codigo_barra'  => 'BARRA-ASPIRINA-01',
            'precio_compra' => 5.00,
            'precio_venta'  => 10.00,
            'tipo_control'  => 'venta_libre',
            'activo'        => true,
        ]);

        $response = $this->actingAs($this->admin)->post(route('productos.store'), [
            'nombre'        => 'Nuevo Medicamento',
            'categoria_id'  => $this->categoria->id,
            'laboratorio_id'=> $this->laboratorio->id,
            'precio_compra' => 10.00,
            'precio_venta'  => 25.00,
            'tipo_control'  => 'venta_libre',
            'codigo_barra'  => 'BARRA-NUEVA-01',
            'presentaciones'=> [
                [
                    'nombre' => 'Caja x 30',
                    'unidades_por_presentacion' => 30,
                    'precio_venta' => 200.00,
                    'codigo_barras' => 'BARRA-ASPIRINA-01', // COLISIÓN con producto existente
                ]
            ]
        ]);

        $response->assertSessionHasErrors(['presentaciones.0.codigo_barras']);
    }

    public function test_delegacion_calculo_unidades_base()
    {
        $producto = Producto::create([
            'nombre'        => 'Loratadina 10mg',
            'categoria_id'  => $this->categoria->id,
            'laboratorio_id'=> $this->laboratorio->id,
            'precio_compra' => 5.00,
            'precio_venta'  => 15.00,
            'tipo_control'  => 'venta_libre',
            'activo'        => true,
        ]);

        $presentacion = PresentacionProducto::create([
            'producto_id'               => $producto->id,
            'nombre'                    => 'Caja x 20 tabletas',
            'unidades_por_presentacion' => 20,
            'precio_venta'              => 80.00,
            'es_unidad_base'            => false,
            'activo'                    => true,
        ]);

        // Probar cálculo desde PresentacionProducto
        $this->assertEquals(60, $presentacion->calcularUnidadesBase(3));

        // Probar delegación desde Producto
        $this->assertEquals(60, $producto->calcularUnidadesBase(3, $presentacion));
        $this->assertEquals(3, $producto->calcularUnidadesBase(3, null));
    }
}
