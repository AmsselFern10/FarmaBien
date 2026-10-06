<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Proveedor;
use App\Models\Categoria;
use App\Models\Laboratorio;
use App\Models\Producto;
use App\Models\Lote;
use App\Models\PresentacionProducto;
use App\Models\PrecioVenta;
use App\Models\Caja;
use App\Models\SesionCaja;
use App\Models\Receta;
use App\Models\RecetaDetalle;
use App\Services\CompraService;
use App\Services\VentaService;
use App\Services\ProductoService;
use App\Services\PresentacionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Exception;

class PreciosSeguridadPosTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Proveedor $proveedor;
    protected Categoria $categoria;
    protected Laboratorio $laboratorio;
    protected Caja $caja;
    protected SesionCaja $sesionCaja;
    protected CompraService $compraService;
    protected VentaService $ventaService;
    protected ProductoService $productoService;
    protected PresentacionService $presentacionService;

    protected function setUp(): void
    {
        parent::setUp();

        $permisos = [
            'ver compras',
            'registrar compras',
            'realizar ventas',
            'ver ventas',
            'crear productos',
            'editar productos',
            'crear presentaciones',
            'editar presentaciones',
            'ver movimientos inventario',
        ];

        foreach ($permisos as $permiso) {
            Permission::findOrCreate($permiso, 'web');
        }

        $roleAdmin = Role::findOrCreate('Administrador', 'web');
        $roleAdmin->syncPermissions($permisos);

        $this->admin = User::factory()->create();
        $this->admin->assignRole($roleAdmin);

        $this->proveedor = Proveedor::factory()->create(['nombre' => 'Distribuidora Central', 'activo' => true]);
        $this->categoria = Categoria::factory()->create(['nombre' => 'Analgésicos']);
        $this->laboratorio = Laboratorio::factory()->create(['nombre' => 'Laboratorios Ramos']);

        $this->caja = Caja::create([
            'nombre'    => 'Caja 01',
            'codigo'    => 'CAJA-01',
            'ubicacion' => 'Mostrador Principal',
            'activo'    => true,
        ]);
        $this->sesionCaja = SesionCaja::create([
            'caja_id'        => $this->caja->id,
            'user_id'        => $this->admin->id,
            'monto_apertura' => 1000.00,
            'fecha_apertura' => now(),
            'estado'         => 'abierta',
        ]);

        $this->compraService = app(CompraService::class);
        $this->ventaService = app(VentaService::class);
        $this->productoService = app(ProductoService::class);
        $this->presentacionService = app(PresentacionService::class);
    }

    /** @test */
    public function reingreso_a_mismo_lote_calcula_precio_promedio_ponderado_ppp(): void
    {
        $producto = Producto::factory()->create([
            'categoria_id'   => $this->categoria->id,
            'laboratorio_id' => $this->laboratorio->id,
            'nombre'         => 'Ibuprofeno 400mg',
            'precio_compra'  => 10.00,
            'precio_venta'   => 15.00,
            'activo'         => true,
        ]);

        // Compra 1: 100 unidades a C$ 10.00 = C$ 1,000.00
        $this->actingAs($this->admin);
        $this->compraService->registrarCompra([
            'proveedor_id'       => $this->proveedor->id,
            'numero_comprobante' => 'F-001',
            'condicion_pago'     => 'contado',
            'productos'          => [
                [
                    'producto_id'             => $producto->id,
                    'cantidad_presentaciones' => 100,
                    'precio_unitario'         => 10.00,
                    'numero_lote'             => 'LOT-PPP-01',
                    'fecha_vencimiento'       => now()->addMonths(12)->toDateString(),
                ]
            ]
        ]);

        $lote = Lote::where('numero_lote', 'LOT-PPP-01')->first();
        $this->assertEquals(100, $lote->stock_actual);
        $this->assertEquals(10.00, $lote->precio_compra);

        // Compra 2: 50 unidades al MISMO lote a C$ 16.00 = C$ 800.00
        // PPP esperado: (100 * 10 + 50 * 16) / 150 = (1000 + 800) / 150 = 1800 / 150 = C$ 12.00
        $this->compraService->registrarCompra([
            'proveedor_id'       => $this->proveedor->id,
            'numero_comprobante' => 'F-002',
            'condicion_pago'     => 'contado',
            'productos'          => [
                [
                    'producto_id'             => $producto->id,
                    'cantidad_presentaciones' => 50,
                    'precio_unitario'         => 16.00,
                    'numero_lote'             => 'LOT-PPP-01',
                    'fecha_vencimiento'       => now()->addMonths(12)->toDateString(),
                ]
            ]
        ]);

        $loteActualizado = Lote::where('numero_lote', 'LOT-PPP-01')->first();
        $this->assertEquals(150, $loteActualizado->stock_actual);
        $this->assertEquals(12.0000, $loteActualizado->precio_compra);
    }

    /** @test */
    public function crear_y_actualizar_producto_registra_historial_scd_tipo_2_y_sincroniza_unidad_base(): void
    {
        $this->actingAs($this->admin);

        // 1. Crear producto con precio C$ 25.00
        $producto = $this->productoService->crearProducto([
            'nombre'         => 'Amoxicilina 500mg Polvo',
            'categoria_id'   => $this->categoria->id,
            'laboratorio_id' => $this->laboratorio->id,
            'precio_compra'  => 10.00,
            'precio_venta'   => 25.00,
            'stock_minimo'   => 10,
            'tipo_control'   => 'venta_libre',
            'activo'         => true,
        ]);

        // Verificar registro inicial SCD Tipo 2
        $this->assertDatabaseHas('precios_venta', [
            'producto_id'     => $producto->id,
            'presentacion_id' => null,
            'precio'          => 25.00,
            'vigente_hasta'   => null,
        ]);

        // 2. Crear presentación unidad base
        $presBase = $this->presentacionService->crearPresentacion([
            'producto_id'               => $producto->id,
            'nombre'                    => 'Frasco Unidad',
            'unidades_por_presentacion' => 1,
            'precio_venta'              => 25.00,
            'es_unidad_base'            => true,
            'activo'                    => true,
        ]);

        // 3. Actualizar precio del producto a C$ 30.00
        $this->productoService->actualizarProducto($producto, [
            'nombre'       => 'Amoxicilina 500mg Polvo',
            'precio_venta' => 30.00,
        ]);

        // Debe haberse cerrado el precio anterior de 25 y abierto el nuevo de 30
        $this->assertDatabaseHas('precios_venta', [
            'producto_id'   => $producto->id,
            'precio'        => 25.00,
        ]);
        $this->assertDatabaseHas('precios_venta', [
            'producto_id'   => $producto->id,
            'precio'        => 30.00,
            'vigente_hasta' => null,
        ]);

        // Sincronización bidireccional: la presentación base debe haberse actualizado a C$ 30.00
        $this->assertEquals(30.00, $presBase->fresh()->precio_venta);
    }

    /** @test */
    public function pos_ignora_precios_arbitrarios_del_frontend_y_aplica_precio_oficial_de_catalogo(): void
    {
        $this->actingAs($this->admin);

        $producto = Producto::factory()->create([
            'categoria_id'   => $this->categoria->id,
            'laboratorio_id' => $this->laboratorio->id,
            'nombre'         => 'Omeprazol 20mg Caps',
            'precio_compra'  => 5.00,
            'precio_venta'   => 20.00, // Precio oficial C$ 20.00
            'activo'         => true,
        ]);

        $lote = Lote::factory()->create([
            'producto_id'       => $producto->id,
            'proveedor_id'      => $this->proveedor->id,
            'stock_actual'      => 50,
            'stock_inicial'     => 50,
            'fecha_vencimiento' => now()->addMonths(12)->toDateString(),
            'activo'            => true,
        ]);

        // Frontend malicioso intenta enviar precio_unitario = 1.00
        $venta = $this->ventaService->procesarVenta([
            'tipo_comprobante' => 'ticket',
            'metodo_pago'      => 'efectivo',
            'monto_recibido'   => 100.00,
            'productos'        => [
                [
                    'producto_id'     => $producto->id,
                    'lote_id'         => $lote->id,
                    'cantidad'        => 2,
                    'precio_unitario' => 1.00, // Intento de manipulación de precio
                ]
            ]
        ]);

        // El backend debe cobrar a C$ 20.00 x 2 = C$ 40.00
        $this->assertEquals(40.00, (float) $venta->total);
        $this->assertEquals(20.00, (float) $venta->detalles->first()->precio_unitario);
    }

    /** @test */
    public function previene_sobregiro_de_receta_cuando_hay_multiples_lineas_en_el_carrito(): void
    {
        $this->actingAs($this->admin);

        $productoControlado = Producto::factory()->create([
            'categoria_id'   => $this->categoria->id,
            'laboratorio_id' => $this->laboratorio->id,
            'nombre'         => 'Clonazepam 2mg Tab',
            'precio_compra'  => 8.00,
            'precio_venta'   => 15.00,
            'tipo_control'   => 'controlado',
            'activo'         => true,
        ]);

        $loteA = Lote::factory()->create([
            'producto_id'       => $productoControlado->id,
            'proveedor_id'      => $this->proveedor->id,
            'numero_lote'       => 'LOT-CLON-A',
            'stock_actual'      => 10,
            'fecha_vencimiento' => now()->addMonths(12)->toDateString(),
            'activo'            => true,
        ]);

        $loteB = Lote::factory()->create([
            'producto_id'       => $productoControlado->id,
            'proveedor_id'      => $this->proveedor->id,
            'numero_lote'       => 'LOT-CLON-B',
            'stock_actual'      => 10,
            'fecha_vencimiento' => now()->addMonths(12)->toDateString(),
            'activo'            => true,
        ]);

        // Receta autoriza 10 unidades en total
        $receta = Receta::create([
            'numero_receta'      => 'RX-CTRL-999',
            'paciente_nombre'    => 'Juan Pérez',
            'medico_nombre'      => 'Dr. Médico',
            'medico_colegiatura' => 'CMP-9988',
            'fecha_emision'      => now()->toDateString(),
            'fecha_vencimiento'  => now()->addDays(30)->toDateString(),
            'estado'             => 'pendiente',
        ]);
        $recetaDetalle = RecetaDetalle::create([
            'receta_id'           => $receta->id,
            'producto_id'         => $productoControlado->id,
            'cantidad_recetada'   => 10,
            'cantidad_dispensada' => 0,
        ]);

        // Intento de comprar 8 del lote A y 5 del lote B (Total 13 > Saldo 10)
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('excede el saldo disponible en la receta médica');

        $this->ventaService->procesarVenta([
            'tipo_comprobante' => 'ticket',
            'metodo_pago'      => 'efectivo',
            'monto_recibido'   => 300.00,
            'receta_modalidad' => 'vinculada',
            'receta_id'        => $receta->id,
            'productos'        => [
                [
                    'producto_id'       => $productoControlado->id,
                    'lote_id'           => $loteA->id,
                    'cantidad'          => 8,
                    'receta_detalle_id' => $recetaDetalle->id,
                ],
                [
                    'producto_id'       => $productoControlado->id,
                    'lote_id'           => $loteB->id,
                    'cantidad'          => 5,
                    'receta_detalle_id' => $recetaDetalle->id,
                ],
            ]
        ]);
    }
}
