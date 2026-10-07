<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Proveedor;
use App\Models\Categoria;
use App\Models\Laboratorio;
use App\Models\Producto;
use App\Models\PresentacionProducto;
use App\Models\Lote;
use App\Models\Compra;
use App\Models\OrdenCompra;
use App\Models\DetalleOrdenCompra;
use App\Models\SesionCaja;
use App\Models\Caja;
use App\Models\HistorialPrecio;
use App\Services\CompraService;
use App\Services\OrdenCompraService;
use App\Services\CuentaPorPagarService;
use App\Services\DevolucionCompraService;
use App\Services\PrecioProveedorService;
use App\Services\ReordenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

class DiagnosticoComprasTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Proveedor $proveedor1;
    protected Proveedor $proveedor2;
    protected Categoria $categoria;
    protected Laboratorio $laboratorio;
    protected array $productos = [];

    protected function setUp(): void
    {
        parent::setUp();

        Permission::firstOrCreate(['name' => 'ver compras']);
        Permission::firstOrCreate(['name' => 'registrar compras']);
        Permission::firstOrCreate(['name' => 'anular compras']);

        $this->user = User::factory()->create();
        $this->user->givePermissionTo(['ver compras', 'registrar compras', 'anular compras']);
        $this->actingAs($this->user);

        $this->proveedor1 = Proveedor::create([
            'nombre' => 'Drogueria Central',
            'ruc' => 'J0310000000001',
            'contacto' => 'Carlos Lopez',
            'telefono' => '8888-0001',
            'activo' => true,
        ]);

        $this->proveedor2 = Proveedor::create([
            'nombre' => 'Distribuidora Farmaceutica',
            'ruc' => 'J0310000000002',
            'contacto' => 'Maria Perez',
            'telefono' => '8888-0002',
            'activo' => true,
        ]);

        $this->categoria = Categoria::create(['nombre' => 'Antibioticos', 'activo' => true]);
        $this->laboratorio = Laboratorio::create(['nombre' => 'Laboratorios Ramos', 'activo' => true]);

        for ($i = 1; $i <= 10; $i++) {
            $p = Producto::create([
                'categoria_id'   => $this->categoria->id,
                'laboratorio_id' => $this->laboratorio->id,
                'nombre'         => "Medicamento {$i} 500mg",
                'codigo_barra'   => "7509000{$i}",
                'precio_compra'  => 10.00 + $i,
                'precio_venta'   => 20.00 + ($i * 2),
                'stock_minimo'   => 15,
                'activo'         => true,
                'tipo_control'   => Producto::TIPO_VENTA_LIBRE,
                'requiere_receta'=> false,
            ]);

            $pres = PresentacionProducto::create([
                'producto_id'               => $p->id,
                'nombre'                    => 'Caja x 10',
                'unidades_por_presentacion' => 10,
                'precio_compra'             => (10.00 + $i) * 10,
                'precio_venta'              => (20.00 + ($i * 2)) * 10,
                'es_unidad_base'            => false,
                'activo'                    => true,
            ]);

            HistorialPrecio::create([
                'producto_id'               => $p->id,
                'proveedor_id'              => $this->proveedor1->id,
                'precio_compra'             => $p->precio_compra,
                'precio_unitario_base'      => $p->precio_compra,
                'tipo'                      => 'compra',
                'fecha'                     => now()->subDays(5),
            ]);

            HistorialPrecio::create([
                'producto_id'               => $p->id,
                'proveedor_id'              => $this->proveedor2->id,
                'precio_compra'             => $p->precio_compra * 0.95,
                'precio_unitario_base'      => $p->precio_compra * 0.95,
                'tipo'                      => 'cotizacion',
                'fecha'                     => now()->subDays(2),
            ]);

            $this->productos[] = $p;
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
            'total'      => count($queries),
            'time_ms'    => $durationMs,
            'duplicates' => $dups,
            'queries'    => $queries,
            'result'     => $res,
        ];
    }

    public function test_diagnostico_compras_abastecimiento()
    {
        // 1. Listado de compras (CompraController::index)
        $d1 = $this->captureQueries(function () {
            $req = new \Illuminate\Http\Request();
            $req->setUserResolver(fn() => $this->user);
            return app(\App\Http\Controllers\CompraController::class)->index($req);
        });

        // 2. Guardar una compra con varias líneas (CompraService::registrarCompra)
        $p1 = $this->productos[0];
        $p2 = $this->productos[1];
        $p3 = $this->productos[2];

        $compraData = [
            'proveedor_id'       => $this->proveedor1->id,
            'numero_comprobante' => 'FACT-2026-0001',
            'condicion_pago'     => 'credito',
            'dias_credito'       => 30,
            'fecha'              => now()->toDateString(),
            'productos'          => [
                [
                    'producto_id'       => $p1->id,
                    'presentacion_id'   => null,
                    'cantidad'          => 20,
                    'precio_unitario'   => 10.50,
                    'numero_lote'       => 'LOTE-A01',
                    'fecha_vencimiento' => now()->addYear()->toDateString(),
                ],
                [
                    'producto_id'       => $p2->id,
                    'presentacion_id'   => null,
                    'cantidad'          => 30,
                    'precio_unitario'   => 12.00,
                    'numero_lote'       => 'LOTE-B02',
                    'fecha_vencimiento' => now()->addMonths(18)->toDateString(),
                ],
                [
                    'producto_id'       => $p3->id,
                    'presentacion_id'   => null,
                    'cantidad'          => 50,
                    'precio_unitario'   => 14.00,
                    'numero_lote'       => 'LOTE-C03',
                    'fecha_vencimiento' => now()->addMonths(24)->toDateString(),
                ]
            ]
        ];

        $d2 = $this->captureQueries(function () use ($compraData) {
            return app(CompraService::class)->registrarCompra($compraData);
        });
        /** @var Compra $compra */
        $compra = $d2['result'];

        // 3. Crear y recibir una orden de compra parcial y total
        $orden = app(OrdenCompraService::class)->crearOrden([
            'proveedor_id'           => $this->proveedor1->id,
            'fecha_emision'          => now()->toDateString(),
            'condicion_pago'         => 'credito',
            'dias_credito'           => 30,
            'items'                  => [
                [
                    'producto_id'     => $p1->id,
                    'cantidad'        => 50,
                    'precio_unitario' => 10.00,
                ],
                [
                    'producto_id'     => $p2->id,
                    'cantidad'        => 40,
                    'precio_unitario' => 12.00,
                ]
            ]
        ], $this->user->id);

        // 4. Listado de órdenes de compra (OrdenCompraController::index)
        $d4 = $this->captureQueries(function () {
            $req = new \Illuminate\Http\Request();
            $req->setUserResolver(fn() => $this->user);
            return app(\App\Http\Controllers\OrdenCompraController::class)->index($req);
        });

        // 5. Recepción de orden de compra (parcial)
        $d3 = $this->captureQueries(function () use ($orden, $p1, $p2) {
            $det1 = $orden->detalles->first();
            return app(CompraService::class)->registrarCompra([
                'proveedor_id'           => $orden->proveedor_id,
                'orden_compra_id'        => $orden->id,
                'numero_comprobante'     => 'FACT-REC-001',
                'condicion_pago'         => 'credito',
                'dias_credito'           => 30,
                'fecha'                  => now()->toDateString(),
                'productos'              => [
                    [
                        'producto_id'             => $p1->id,
                        'detalle_orden_compra_id' => $det1->id,
                        'cantidad'                => 25,
                        'precio_unitario'         => 10.00,
                        'numero_lote'             => 'LOTE-REC-01',
                        'fecha_vencimiento'       => now()->addYear()->toDateString(),
                    ]
                ]
            ]);
        });

        // 6. Cuentas por Pagar Index y Métricas
        $d5 = $this->captureQueries(function () {
            $req = new \Illuminate\Http\Request();
            $req->setUserResolver(fn() => $this->user);
            return app(\App\Http\Controllers\CuentaPorPagarController::class)->index($req);
        });

        // 7. Registrar Abono a Cuenta por Pagar
        $caja = Caja::create(['nombre' => 'Caja 1', 'codigo' => 'CAJA-01', 'activo' => true]);
        $sesionCaja = SesionCaja::create([
            'caja_id'        => $caja->id,
            'user_id'        => $this->user->id,
            'monto_inicial'  => 1000,
            'estado'         => 'abierta',
            'fecha_apertura' => now(),
        ]);

        $d6 = $this->captureQueries(function () use ($compra) {
            return app(CuentaPorPagarService::class)->registrarAbono([
                'compra_id'         => $compra->id,
                'monto'             => 200.00,
                'metodo_pago'       => 'efectivo',
                'fecha_pago'        => now()->toDateString(),
                'registrar_en_caja' => true,
            ], $this->user->id);
        });

        // 8. Registrar Devolución a Proveedor
        $loteP1 = Lote::where('producto_id', $p1->id)->where('compra_id', $compra->id)->first();
        $d7 = $this->captureQueries(function () use ($compra, $loteP1) {
            return app(DevolucionCompraService::class)->registrarDevolucion([
                'proveedor_id' => $compra->proveedor_id,
                'compra_id'    => $compra->id,
                'motivo'       => 'Producto en mal estado detectado en recepción',
                'items'        => [
                    [
                        'lote_id'         => $loteP1->id,
                        'cantidad'        => 5,
                        'precio_unitario' => $loteP1->precio_compra,
                        'motivo_detalle'  => 'Empaque dañado',
                    ]
                ]
            ], $this->user->id);
        });

        // 9. Comparativa de precios de un producto
        $d8 = $this->captureQueries(function () use ($p1) {
            return app(PrecioProveedorService::class)->getComparativaProducto($p1->id);
        });

        // 10. Sugerencias de reorden inteligente
        $d9 = $this->captureQueries(function () {
            return app(ReordenService::class)->calcularSugerencias();
        });

        $report = [
            '1_listado_compras_index'         => $d1,
            '2_guardar_compra_varias_lineas'  => $d2,
            '3_listado_ordenes_compra_index'  => $d4,
            '4_recibir_orden_compra_parcial'  => $d3,
            '5_cuentas_por_pagar_index'       => $d5,
            '6_registrar_abono_cxp'           => $d6,
            '7_devolucion_proveedor_store'    => $d7,
            '8_comparador_precios_producto'   => $d8,
            '9_sugerencias_reorden'           => $d9,
        ];

        foreach ($report as &$item) {
            unset($item['result']);
        }

        file_put_contents(storage_path('logs/diagnostico_compras_abastecimiento.json'), json_encode($report, JSON_PRETTY_PRINT));
        $this->assertTrue(true);
    }
}
