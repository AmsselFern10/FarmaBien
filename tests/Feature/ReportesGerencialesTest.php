<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Caja;
use App\Models\SesionCaja;
use App\Models\Cliente;
use App\Models\Proveedor;
use App\Models\Producto;
use App\Models\Lote;
use App\Models\Categoria;
use App\Models\Laboratorio;
use App\Models\Compra;
use App\Models\DetalleCompra;
use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\Receta;
use App\Models\AuditLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ReportesGerencialesTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $cajero;
    protected User $usuarioSinPermiso;
    protected Categoria $categoria;
    protected Laboratorio $laboratorio;
    protected Proveedor $proveedor;
    protected Producto $producto;
    protected Lote $lote;
    protected Cliente $cliente;

    protected function setUp(): void
    {
        parent::setUp();

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Crear permisos de reportes
        Permission::create(['name' => 'ver reportes ventas']);
        Permission::create(['name' => 'ver reportes compras']);
        Permission::create(['name' => 'ver reportes inventario']);
        Permission::create(['name' => 'ver cajas']);
        Permission::create(['name' => 'ver usuarios']);

        $roleAdmin = Role::create(['name' => 'administrador']);
        $roleAdmin->givePermissionTo([
            'ver reportes ventas',
            'ver reportes compras',
            'ver reportes inventario',
            'ver cajas',
            'ver usuarios'
        ]);

        $roleCajero = Role::create(['name' => 'cajero']);
        $roleCajero->givePermissionTo(['ver reportes ventas']);

        $this->admin = User::factory()->create();
        $this->admin->assignRole($roleAdmin);

        $this->cajero = User::factory()->create();
        $this->cajero->assignRole($roleCajero);

        $this->usuarioSinPermiso = User::factory()->create();

        $this->categoria = Categoria::create(['nombre' => 'Antibióticos', 'activo' => true]);
        $this->laboratorio = Laboratorio::create(['nombre' => 'Pfizer', 'activo' => true]);
        $this->proveedor = Proveedor::create(['nombre' => 'Droguería Central', 'ruc' => 'J-12345678-0', 'activo' => true]);

        $this->cliente = Cliente::create([
            'nombre'    => 'Carlos Mendoza',
            'documento' => '001-150885-0003C',
            'telefono'  => '8777-7777',
            'activo'    => true,
        ]);

        $this->producto = Producto::create([
            'codigo_barra'      => '775333444555',
            'nombre'            => 'Amoxicilina 500mg',
            'principio_activo'  => 'Amoxicilina',
            'categoria_id'      => $this->categoria->id,
            'laboratorio_id'    => $this->laboratorio->id,
            'precio_compra'     => 12.00,
            'precio_venta'      => 25.00,
            'stock_minimo'      => 10,
            'requiere_receta'   => true,
            'es_controlado'     => false,
            'activo'            => true,
        ]);

        $this->lote = Lote::create([
            'producto_id'       => $this->producto->id,
            'proveedor_id'      => $this->proveedor->id,
            'numero_lote'       => 'LOT-AMOX-01',
            'fecha_vencimiento' => now()->addMonths(6),
            'stock_inicial'     => 100,
            'stock_actual'      => 80,
            'precio_compra'     => 12.00,
            'activo'            => true,
        ]);
    }

    public function test_usuario_sin_permisos_recibe_403_en_reportes()
    {
        $response = $this->actingAs($this->usuarioSinPermiso)->get(route('reportes.index'));
        $response->assertStatus(403);

        $responseVentas = $this->actingAs($this->usuarioSinPermiso)->get(route('reportes.ventas'));
        $responseVentas->assertStatus(403);

        $responseInventario = $this->actingAs($this->usuarioSinPermiso)->get(route('reportes.inventario'));
        $responseInventario->assertStatus(403);
    }

    public function test_dashboard_reportes_index_muestra_kpis_consolidados()
    {
        // Registrar una venta
        $venta = Venta::create([
            'cliente_id'        => $this->cliente->id,
            'user_id'           => $this->admin->id,
            'tipo_comprobante'  => 'ticket',
            'numero_comprobante'=> 'TICK-0001',
            'total'             => 50.00,
            'subtotal'          => 50.00,
            'metodo_pago'       => 'efectivo',
            'estado'            => 'completada',
            'fecha'             => now(),
        ]);

        DetalleVenta::create([
            'venta_id'               => $venta->id,
            'producto_id'            => $this->producto->id,
            'lote_id'                => $this->lote->id,
            'cantidad'               => 2,
            'cantidad_unidades_base' => 2,
            'precio_unitario'        => 25.00,
            'subtotal'               => 50.00,
        ]);

        $response = $this->actingAs($this->admin)->get(route('reportes.index'));
        $response->assertOk();
        $response->assertViewHas('ventasMes');
        $response->assertViewHas('valorizacion');
        $response->assertViewHas('topProductosMes');
    }

    public function test_reporte_ventas_filtrado_y_exportaciones()
    {
        $venta = Venta::create([
            'cliente_id'        => $this->cliente->id,
            'user_id'           => $this->admin->id,
            'tipo_comprobante'  => 'factura',
            'numero_comprobante'=> 'FAC-0001',
            'total'             => 100.00,
            'subtotal'          => 100.00,
            'metodo_pago'       => 'efectivo',
            'estado'            => 'completada',
            'fecha'             => now(),
        ]);

        // Ver HTML
        $response = $this->actingAs($this->admin)->get(route('reportes.ventas'));
        $response->assertOk();
        $response->assertSee('FAC-0001');

        // Exportar Excel
        $resExcel = $this->actingAs($this->admin)->get(route('reportes.ventas', ['export' => 'excel']));
        $resExcel->assertOk();
        $this->assertStringContainsString('application/vnd.ms-excel', $resExcel->headers->get('Content-Type'));

        // Exportar CSV
        $resCsv = $this->actingAs($this->admin)->get(route('reportes.ventas', ['export' => 'csv']));
        $resCsv->assertOk();
        $this->assertStringContainsString('text/csv', $resCsv->headers->get('Content-Type'));

        // Exportar PDF
        $resPdf = $this->actingAs($this->admin)->get(route('reportes.ventas', ['export' => 'pdf']));
        $resPdf->assertOk();
        $this->assertEquals('application/pdf', $resPdf->headers->get('Content-Type'));
    }

    public function test_reporte_inventario_y_semaforos_caducidad()
    {
        $response = $this->actingAs($this->admin)->get(route('reportes.inventario'));
        $response->assertOk();
        $response->assertViewHas('totalValorCosto');
        $response->assertViewHas('semVigentes');
        $response->assertSee('LOT-AMOX-01');

        // Exportar PDF Inventario
        $resPdf = $this->actingAs($this->admin)->get(route('reportes.inventario', ['export' => 'pdf']));
        $resPdf->assertOk();
        $this->assertEquals('application/pdf', $resPdf->headers->get('Content-Type'));
    }

    public function test_reporte_compras_y_top_productos()
    {
        $compra = Compra::create([
            'proveedor_id'   => $this->proveedor->id,
            'user_id'        => $this->admin->id,
            'numero_compra'  => 'COM-0001',
            'numero_factura' => 'F-9988',
            'total'          => 1200.00,
            'subtotal'       => 1200.00,
            'estado'         => 'recibida',
            'fecha'          => now(),
        ]);

        $responseCompras = $this->actingAs($this->admin)->get(route('reportes.compras'));
        $responseCompras->assertOk();
        $responseCompras->assertSee('Droguería Central');

        $responseTop = $this->actingAs($this->admin)->get(route('reportes.productos-mas-vendidos'));
        $responseTop->assertOk();
        $responseTop->assertViewHas('ranking');
    }

    public function test_reporte_cajas_y_auditorias()
    {
        $caja = Caja::create([
            'codigo' => 'CAJA-REP-01',
            'nombre' => 'Caja Reportes',
            'activo' => true,
        ]);

        $sesion = SesionCaja::create([
            'caja_id'                 => $caja->id,
            'user_id'                 => $this->admin->id,
            'monto_inicial'           => 500.00,
            'fecha_apertura'          => now(),
            'estado'                  => 'abierta',
            'monto_esperado_efectivo' => 500.00,
        ]);

        AuditLog::create([
            'user_id'     => $this->admin->id,
            'modulo'      => 'ventas',
            'accion'      => 'ANULAR',
            'descripcion' => 'Anulación de prueba en auditoría',
            'ip'          => '127.0.0.1',
        ]);

        $responseCajas = $this->actingAs($this->admin)->get(route('reportes.cajas'));
        $responseCajas->assertOk();
        $responseCajas->assertSee('Caja Reportes');

        $responseAudit = $this->actingAs($this->admin)->get(route('reportes.auditorias'));
        $responseAudit->assertOk();
        $responseAudit->assertSee('Anulación de prueba en auditoría');
    }
}
