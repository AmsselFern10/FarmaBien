<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Producto;
use App\Models\Lote;
use App\Models\Receta;
use App\Models\RecetaDetalle;
use App\Models\RegistroVentaControlado;
use App\Models\MovimientoInventario;

class VerificarIntegridadControladosCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'farma:verificar-controlados {--producto= : ID de producto específico a auditar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Audita la integridad del Libro Oficial MINSA, recetas médicas y stock de medicamentos controlados.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('================================================================');
        $this->info('  FARMA BIEN — AUDITORÍA DE MEDICAMENTOS CONTROLADOS Y MINSA   ');
        $this->info('================================================================');
        $this->newLine();

        $productoId = $this->option('producto');
        $inconsistencias = 0;

        // 1. Auditoría de Productos Controlados y Balance de Libro MINSA vs Lotes
        $this->info('1. Verificando consistencia de saldo Libro MINSA vs Stock Lotes...');
        
        $productosQuery = Producto::controlados()->with('lotes');
        if ($productoId) {
            $productosQuery->where('id', $productoId);
        }
        $productos = $productosQuery->get();

        $tablaProductos = [];

        foreach ($productos as $producto) {
            $stockActualLotes = (int) $producto->lotes->where('estado', 'activo')->sum('cantidad_actual');
            
            // Calcular balance acumulado en Libro Oficial MINSA
            $entradasMinsa = (float) RegistroVentaControlado::where('producto_id', $producto->id)
                ->whereIn('tipo_movimiento', [
                    RegistroVentaControlado::TIPO_COMPRA,
                    RegistroVentaControlado::TIPO_DEVOLUCION_STOCK,
                    RegistroVentaControlado::TIPO_AJUSTE_INGRESO,
                    RegistroVentaControlado::TIPO_ANULACION_VENTA,
                ])->sum('cantidad');

            $salidasMinsa = (float) RegistroVentaControlado::where('producto_id', $producto->id)
                ->whereIn('tipo_movimiento', [
                    RegistroVentaControlado::TIPO_VENTA,
                    RegistroVentaControlado::TIPO_DEVOLUCION_MERMA,
                    RegistroVentaControlado::TIPO_AJUSTE_EGRESO,
                    RegistroVentaControlado::TIPO_ANULACION_COMPRA,
                ])->sum('cantidad');

            $saldoMinsa = (int) ($entradasMinsa - $salidasMinsa);
            $diferencia = $stockActualLotes - $saldoMinsa;

            $estado = ($diferencia === 0) ? '<fg=green>OK</>' : '<fg=red>DESALINEADO (' . $diferencia . ')</>';
            if ($diferencia !== 0) {
                $inconsistencias++;
            }

            $tablaProductos[] = [
                $producto->id,
                mb_strimwidth($producto->nombre, 0, 30, '...'),
                $producto->tipo_control ?? 'N/A',
                $stockActualLotes,
                $entradasMinsa,
                $salidasMinsa,
                $saldoMinsa,
                $estado,
            ];
        }

        $this->table(
            ['ID', 'Producto', 'Control', 'Stock Lotes', 'Entradas MINSA', 'Salidas MINSA', 'Saldo MINSA', 'Estado'],
            $tablaProductos
        );
        $this->newLine();

        // 2. Auditoría de Kardex vs Libro MINSA (Detección de movimientos huérfanos)
        $this->info('2. Verificando correspondencia Kardex ↔ Libro MINSA...');
        $movimientosControladosSinMinsa = MovimientoInventario::whereHas('producto', function ($q) {
                $q->controlados();
            })
            ->whereDoesntHave('registroVentaControlado')
            ->count();

        if ($movimientosControladosSinMinsa > 0) {
            $this->warn("  [ADVERTENCIA] Se encontraron {$movimientosControladosSinMinsa} movimientos en Kardex de productos controlados sin asiento en Libro MINSA.");
            $inconsistencias++;
        } else {
            $this->info('  [OK] Todos los movimientos en Kardex de controlados cuentan con trazabilidad en Libro MINSA.');
        }
        $this->newLine();

        // 3. Auditoría de Recetas Médicas (Sobre-dispensación y vigencia)
        $this->info('3. Verificando integridad de prescripciones médicas...');
        
        $sobredispensados = RecetaDetalle::whereRaw('cantidad_dispensada > cantidad_recetada')->count();
        if ($sobredispensados > 0) {
            $this->error("  [ERROR CRÍTICO] Se encontraron {$sobredispensados} renglones de receta con cantidad dispensada mayor a la recetada.");
            $inconsistencias++;
        } else {
            $this->info('  [OK] Ninguna receta presenta sobre-dispensación.');
        }

        $vencidasPendientes = Receta::whereNotNull('fecha_vencimiento')
            ->where('fecha_vencimiento', '<', now()->toDateString())
            ->where('estado', 'pendiente')
            ->count();

        if ($vencidasPendientes > 0) {
            $this->comment("  [INFO] Existen {$vencidasPendientes} recetas vencidas en estado pendiente.");
        } else {
            $this->info('  [OK] No hay inconsistencias en estados de recetas vencidas.');
        }
        $this->newLine();

        // Resumen final
        $this->info('================================================================');
        if ($inconsistencias === 0) {
            $this->info('  RESULTADO: LIBRO OFICIAL MINSA Y RECETAS 100% ÍNTEGROS');
            $this->info('================================================================');
            return 0;
        }

        $this->error("  RESULTADO: SE ENCONTRARON {$inconsistencias} INCONSISTENCIA(S)");
        $this->info('================================================================');
        return 1;
    }
}
