<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Venta;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Laboratorio;
use App\Models\Lote;
use App\Models\PresentacionProducto;
use App\Models\SesionCaja;
use App\Models\Caja;
use App\Models\DevolucionVenta;
use App\Services\VentaService;
use App\Services\DevolucionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

class DiagnosticoVentasTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Categoria $categoria;
    protected Laboratorio $laboratorio;
    protected Caja $caja;
    protected SesionCaja $sesionCaja;
    protected Cliente $cliente;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::firstOrCreate(['name' => 'ver ventas']);
        Permission::firstOrCreate(['name' => 'ver ventas propias']);
        Permission::firstOrCreate(['name' => 'realizar ventas']);
        Permission::firstOrCreate(['name' => 'anular ventas']);

        $this->user = User::factory()->create();
        $this->user->givePermissionTo(['ver ventas', 'realizar ventas', 'anular ventas']);
        $this->actingAs($this->user);

        $this->categoria = Categoria::create(['nombre' => 'Analgesicos', 'activo' => true]);
        $this->laboratorio = Laboratorio::create(['nombre' => 'Bayer', 'activo' => true]);

        $this->caja = Caja::create(['nombre' => 'Caja 1', 'codigo' => 'CAJA-01', 'activo' => true]);
        $this->sesionCaja = SesionCaja::create([
            'caja_id' => $this->caja->id,
            'user_id' => $this->user->id,
            'monto_inicial' => 1000,
            'estado' => 'abierta',
            'fecha_apertura' => now(),
        ]);

        $this->cliente = Cliente::create(['nombre' => 'Juan Perez', 'documento' => '001-000000-0000A', 'activo' => true]);

        for ($i = 1; $i <= 10; $i++) {
            $p = Producto::create([
                'categoria_id' => $this->categoria->id,
                'laboratorio_id' => $this->laboratorio->id,
                'nombre' => "Paracetamol {$i}00mg",
                'codigo_barra' => "7501000{$i}",
                'precio_compra' => 10.00,
                'precio_venta' => 20.00,
                'activo' => true,
                'requiere_receta' => false,
                'tipo_control' => Producto::TIPO_VENTA_LIBRE,
            ]);

            Lote::create([
                'producto_id' => $p->id,
                'numero_lote' => "LOT-{$i}",
                'stock_inicial' => 100,
                'stock_actual' => 100,
                'fecha_vencimiento' => now()->addMonths(6),
                'precio_compra' => 10.00,
                'activo' => true,
            ]);

            PresentacionProducto::create([
                'producto_id' => $p->id,
                'nombre' => 'Caja x 10',
                'unidades_por_presentacion' => 10,
                'precio_venta' => 180.00,
                'activo' => true,
            ]);
        }
    }

    protected function captureQueries(callable $callback): array
    {
        $queries = [];
        $listener = function ($query) use (&$queries) {
            $queries[] = [
                'sql' => $query->sql,
                'time' => $query->time,
                'bindings' => $query->bindings,
            ];
        };

        DB::listen($listener);
        $start = microtime(true);
        $res = $callback();
        $durationMs = round((microtime(true) - $start) * 1000, 2);

        $sqlCounts = [];
        foreach ($queries as $q) {
            $sql = $q['sql'];
            $sqlCounts[$sql] = ($sqlCounts[$sql] ?? 0) + 1;
        }

        $dups = array_filter($sqlCounts, fn($c) => $c > 1);

        return [
            'total' => count($queries),
            'time_ms' => $durationMs,
            'duplicates' => $dups,
            'queries' => $queries,
            'result' => $res,
        ];
    }

    public function test_diagnostico_ventas()
    {
        // 1. Buscar producto en el POS
        $d1 = $this->captureQueries(function () {
            return app(VentaService::class)->buscarProductosParaVenta('Paracetamol', null, 20);
        });

        // 2. Vista POS create (VentaController::create)
        $d2 = $this->captureQueries(function () {
            $req = new \Illuminate\Http\Request();
            $req->setUserResolver(fn() => $this->user);
            return app(\App\Http\Controllers\VentaController::class)->create($req);
        });

        // 3. Confirmar venta (VentaService::procesarVenta)
        $p1 = Producto::first();
        $l1 = Lote::where('producto_id', $p1->id)->first();
        $pres1 = PresentacionProducto::where('producto_id', $p1->id)->first();
        $p2 = Producto::skip(1)->first();
        $l2 = Lote::where('producto_id', $p2->id)->first();

        $ventaData = [
            'cliente_id' => $this->cliente->id,
            'tipo_comprobante' => 'ticket',
            'metodo_pago' => 'efectivo',
            'monto_recibido' => 500,
            'productos' => [
                [
                    'producto_id' => $p1->id,
                    'lote_id' => $l1->id,
                    'presentacion_id' => $pres1->id,
                    'cantidad' => 2,
                ],
                [
                    'producto_id' => $p2->id,
                    'lote_id' => $l2->id,
                    'presentacion_id' => null,
                    'cantidad' => 5,
                ]
            ]
        ];

        $d3 = $this->captureQueries(function () use ($ventaData) {
            return app(VentaService::class)->procesarVenta($ventaData);
        });
        /** @var Venta $venta */
        $venta = $d3['result'];

        // 4. Historial Ventas (VentaController::index)
        $d4 = $this->captureQueries(function () {
            $req = new \Illuminate\Http\Request();
            $req->setUserResolver(fn() => $this->user);
            return app(\App\Http\Controllers\VentaController::class)->index($req);
        });

        // 5. Detalle de venta (VentaController::show)
        $d5 = $this->captureQueries(function () use ($venta) {
            return app(\App\Http\Controllers\VentaController::class)->show($venta);
        });

        // 6. Nueva devolución (DevolucionController::create)
        $d7 = $this->captureQueries(function () use ($venta) {
            $req = new \Illuminate\Http\Request(['venta_id' => $venta->id]);
            return app(\App\Http\Controllers\DevolucionController::class)->create($req);
        });

        // 7. Confirmar devolución (DevolucionService::procesarDevolucion)
        $det1 = $venta->detalles->first();
        $devData = [
            'venta_id' => $venta->id,
            'motivo' => 'cliente_desiste',
            'metodo_reembolso' => 'efectivo',
            'items' => [
                [
                    'detalle_venta_id' => $det1->id,
                    'cantidad' => 1,
                    'reingresa_a_stock' => true,
                    'estado_producto' => 'buen_estado',
                ]
            ]
        ];

        $d8 = $this->captureQueries(function () use ($devData) {
            return app(DevolucionService::class)->procesarDevolucion($devData);
        });

        // 8. Anular venta (VentaService::anularVenta)
        $venta2 = app(VentaService::class)->procesarVenta($ventaData);
        $d6 = $this->captureQueries(function () use ($venta2) {
            return app(VentaService::class)->anularVenta($venta2->id, 'Motivo de anulación justificado');
        });

        $report = [
            '1_buscar_producto_pos' => $d1,
            '2_abrir_pos_create' => $d2,
            '3_confirmar_venta_store' => $d3,
            '4_historial_ventas_index' => $d4,
            '5_detalle_venta_show' => $d5,
            '6_anular_venta' => $d6,
            '7_nueva_devolucion_create' => $d7,
            '8_confirmar_devolucion_store' => $d8,
        ];

        // Clean result objects for json export
        foreach ($report as &$item) {
            unset($item['result']);
        }

        file_put_contents(storage_path('logs/diagnostico_ventas.json'), json_encode($report, JSON_PRETTY_PRINT));
        $this->assertTrue(true);
    }
}
