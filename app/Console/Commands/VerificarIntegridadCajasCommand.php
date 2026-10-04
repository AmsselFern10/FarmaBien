<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Caja;
use App\Models\SesionCaja;
use App\Models\MovimientoCaja;
use App\Models\Venta;

class VerificarIntegridadCajasCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'farma:verificar-cajas {--caja= : ID de la caja física específica a auditar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Audita la integridad contable, balances de efectivo, arqueos y turnos de cajas.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('================================================================');
        $this->info('    FARMA BIEN — AUDITORÍA DE CAJAS, TURNOS Y ARQUEOS          ');
        $this->info('================================================================');
        $this->newLine();

        $cajaId = $this->option('caja');
        $inconsistencias = 0;

        // 1. Auditoría de Cajas Físicas y Singularidad de Turnos Activos
        $this->info('1. Verificando estado y singularidad de turnos activos...');
        $cajasQuery = Caja::with('sesiones');
        if ($cajaId) {
            $cajasQuery->where('id', $cajaId);
        }
        $cajas = $cajasQuery->get();

        $tablaCajas = [];
        foreach ($cajas as $caja) {
            $sesionesAbiertas = $caja->sesiones->where('estado', 'abierta')->count();
            $estadoTurno = match(true) {
                $sesionesAbiertas === 0 => '<fg=gray>CERRADA</>',
                $sesionesAbiertas === 1 => '<fg=green>ABIERTA (OK)</>',
                default                 => '<fg=red>ERROR: MÚLTIPLES SESIONES (' . $sesionesAbiertas . ')</>',
            };

            if ($sesionesAbiertas > 1) {
                $inconsistencias++;
            }

            $tablaCajas[] = [
                $caja->id,
                $caja->nombre,
                $caja->codigo,
                $caja->activo ? '<fg=green>ACTIVA</>' : '<fg=yellow>INACTIVA</>',
                $sesionesAbiertas,
                $estadoTurno,
            ];
        }

        $this->table(['ID', 'Caja', 'Código', 'Estado Caja', 'Sesiones Abiertas', 'Diagnóstico'], $tablaCajas);
        $this->newLine();

        // 2. Auditoría de Fórmulas Matemáticas y Arqueos en Sesiones
        $this->info('2. Verificando integridad matemática de balances y arqueos...');
        $sesionesQuery = SesionCaja::with(['caja', 'usuario']);
        if ($cajaId) {
            $sesionesQuery->where('caja_id', $cajaId);
        }
        $sesiones = $sesionesQuery->get();

        $sesionesConError = 0;
        foreach ($sesiones as $sesion) {
            // Verificar cálculo de efectivo esperado
            $ventasEfectivo = (float) Venta::where('sesion_caja_id', $sesion->id)
                ->where('estado', 'completada')
                ->where('metodo_pago', 'efectivo')
                ->sum('total');

            $ingresosManuales = (float) MovimientoCaja::where('sesion_caja_id', $sesion->id)
                ->where('tipo', 'ingreso')
                ->sum('monto');

            $egresosManuales = (float) MovimientoCaja::where('sesion_caja_id', $sesion->id)
                ->where('tipo', 'egreso')
                ->sum('monto');

            $esperadoReal = round((float) $sesion->monto_inicial + $ventasEfectivo + $ingresosManuales - $egresosManuales, 2);
            $esperadoGuardado = round((float) $sesion->monto_esperado_efectivo, 2);

            // Si está cerrada, validar diferencia
            if ($sesion->estado === 'cerrada') {
                $declarado = round((float) $sesion->monto_final_efectivo, 2);
                $diferenciaReal = round($declarado - $esperadoGuardado, 2);
                $diferenciaGuardada = round((float) $sesion->diferencia_efectivo, 2);

                if (abs($diferenciaReal - $diferenciaGuardada) > 0.01) {
                    $sesionesConError++;
                    $inconsistencias++;
                }
            }

            if (abs($esperadoReal - $esperadoGuardado) > 0.05 && $sesion->estado === 'cerrada') {
                $sesionesConError++;
                $inconsistencias++;
            }
        }

        if ($sesionesConError > 0) {
            $this->error("  [ERROR CRÍTICO] Se detectaron {$sesionesConError} sesiones de caja con descuadre en fórmulas de balance o arqueo.");
        } else {
            $this->info("  [OK] Todas las sesiones analizadas (" . $sesiones->count() . ") presentan coherencia matemática exacta.");
        }
        $this->newLine();

        // 3. Auditoría de Movimientos Manuales
        $this->info('3. Verificando movimientos manuales de caja...');
        $movimientosInvalidos = MovimientoCaja::where('monto', '<=', 0)->count();
        if ($movimientosInvalidos > 0) {
            $this->error("  [ERROR CRÍTICO] Se encontraron {$movimientosInvalidos} movimientos de caja con monto menor o igual a 0.");
            $inconsistencias++;
        } else {
            $this->info('  [OK] Todos los movimientos manuales cuentan con importes válidos.');
        }
        $this->newLine();

        // Resumen
        $this->info('================================================================');
        if ($inconsistencias === 0) {
            $this->info('  RESULTADO: SISTEMA DE CAJAS Y ARQUEOS 100% ÍNTEGRO');
            $this->info('================================================================');
            return 0;
        }

        $this->error("  RESULTADO: SE ENCONTRARON {$inconsistencias} INCONSISTENCIA(S)");
        $this->info('================================================================');
        return 1;
    }
}
