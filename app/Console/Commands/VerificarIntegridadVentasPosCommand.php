<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\MovimientoInventario;
use App\Models\RegistroVentaControlado;
use App\Models\RecetaDetalle;
use App\Models\SesionCaja;

class VerificarIntegridadVentasPosCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'farma:verificar-ventas-pos {--limit=50 : Límite de ventas recientes a auditar} {--reparar : Regulariza asientos faltantes de Libro MINSA en ventas históricas}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Audita la integridad de ventas en POS, importes, kardex, control sanitario MINSA y recetas.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('================================================================');
        $this->info('    FARMA BIEN — AUDITORÍA DE INTEGRIDAD: VENTAS & POS         ');
        $this->info('================================================================');
        $this->newLine();

        $limit = (int) $this->option('limit');
        $reparar = (bool) $this->option('reparar');
        $inconsistencias = 0;

        // 1. Auditoría Matemática de Importes (Subtotal - Descuento == Total)
        $this->info('1. Verificando invariante matemático de importes y totales...');
        $ventas = Venta::with('detalles')->latest('id')->limit($limit)->get();
        $ventasDescuadradas = 0;

        foreach ($ventas as $venta) {
            $subtotalDetalles = round($venta->detalles->sum('subtotal'), 2);
            $totalEsperado = max(0, round($subtotalDetalles - (float)$venta->descuento, 2));

            // Si la venta está completada, verificar que total coincida con el cálculo
            if ($venta->estado === 'completada') {
                if (abs((float)$venta->total - $totalEsperado) > 0.05) {
                    $this->warn("Venta #{$venta->id} ({$venta->numero_comprobante}): Total registrado C$ {$venta->total} != Calculado C$ {$totalEsperado}");
                    $ventasDescuadradas++;
                    $inconsistencias++;
                }
            }
        }

        if ($ventasDescuadradas === 0) {
            $this->line("   <fg=green>✓ [OK]</> Todas las ventas auditadas ({$ventas->count()}) cumplen el invariante de totales.");
        } else {
            $this->line("   <fg=red>✗ [FALLO]</> Se encontraron {$ventasDescuadradas} ventas con descuadre en totales.");
        }

        // 2. Auditoría de Trazabilidad Líneas de Venta vs Salidas de Kardex
        $this->newLine();
        $this->info('2. Verificando trazabilidad de detalles de venta con salidas en Kardex...');
        $detallesSinKardex = 0;

        $detalles = DetalleVenta::whereHas('venta', fn($q) => $q->where('estado', 'completada'))
            ->latest('id')
            ->limit($limit * 3)
            ->get();

        foreach ($detalles as $det) {
            $mov = MovimientoInventario::where('origen', 'venta')
                ->where('origen_id', $det->venta_id)
                ->where('producto_id', $det->producto_id)
                ->where('lote_id', $det->lote_id)
                ->first();

            if (!$mov) {
                $this->warn("Detalle #{$det->id} (Venta #{$det->venta_id}, Producto #{$det->producto_id}) no tiene movimiento de salida en Kardex.");
                $detallesSinKardex++;
                $inconsistencias++;
            }
        }

        if ($detallesSinKardex === 0) {
            $this->line("   <fg=green>✓ [OK]</> Todos los detalles de venta auditados ({$detalles->count()}) cuentan con movimiento de Kardex.");
        } else {
            $this->line("   <fg=red>✗ [FALLO]</> Se encontraron {$detallesSinKardex} detalles sin movimiento en Kardex.");
        }

        // 3. Auditoría de Control Sanitario MINSA
        $this->newLine();
        $this->info('3. Verificando registro en Libro Oficial MINSA para medicamentos controlados...');
        $controladosSinLibro = 0;

        $detallesControlados = DetalleVenta::whereHas('producto', function ($q) {
                $q->where('tipo_control', 'controlado')->orWhere('requiere_receta', true);
            })
            ->whereHas('venta', fn($q) => $q->where('estado', 'completada'))
            ->with(['venta.cliente', 'producto', 'lote'])
            ->latest('id')
            ->limit($limit)
            ->get();

        foreach ($detallesControlados as $detCtrl) {
            $registroMinsa = RegistroVentaControlado::where('venta_id', $detCtrl->venta_id)
                ->where('producto_id', $detCtrl->producto_id)
                ->first();

            if (!$registroMinsa) {
                if ($reparar) {
                    RegistroVentaControlado::create([
                        'tipo_movimiento'     => RegistroVentaControlado::TIPO_VENTA,
                        'venta_id'            => $detCtrl->venta_id,
                        'producto_id'         => $detCtrl->producto_id,
                        'lote_id'             => $detCtrl->lote_id,
                        'nivel_controlado'    => 1,
                        'paciente_nombre'     => $detCtrl->venta->cliente?->nombre ?? 'Público General',
                        'paciente_cedula'     => $detCtrl->venta->cliente?->documento,
                        'motivo_omision'      => 'Asiento regularizado por auditoría de ventas históricas',
                        'cantidad'            => $detCtrl->cantidad_unidades_base ?? $detCtrl->cantidad,
                        'unidad'              => 'unidad',
                        'user_id'             => $detCtrl->venta->user_id ?? 1,
                    ]);
                    $this->line("   <fg=yellow>↳ [REPARADO]</> Asiento MINSA generado para Venta #{$detCtrl->venta_id}, Producto #{$detCtrl->producto_id}.");
                } else {
                    $this->warn("Venta #{$detCtrl->venta_id} contiene medicamento controlado #{$detCtrl->producto_id} pero no existe asiento en Libro MINSA.");
                    $controladosSinLibro++;
                    $inconsistencias++;
                }
            }
        }

        if ($controladosSinLibro === 0) {
            $this->line("   <fg=green>✓ [OK]</> Todos los medicamentos controlados vendidos ({$detallesControlados->count()}) están asentados en el Libro MINSA.");
        } else {
            $this->line("   <fg=red>✗ [FALLO]</> Se encontraron {$controladosSinLibro} ventas controladas sin asiento MINSA (Use --reparar para regularizar).");
        }

        // 4. Auditoría de Saldos de Recetas Médicas
        $this->newLine();
        $this->info('4. Verificando invariante de prescripción en recetas (dispensado <= recetado)...');
        $recetasSobregiradas = 0;

        $recetaDetalles = RecetaDetalle::where('cantidad_dispensada', '>', 0)->get();
        foreach ($recetaDetalles as $rd) {
            if ($rd->cantidad_dispensada > $rd->cantidad_recetada) {
                $this->warn("RecetaDetalle #{$rd->id} (Receta #{$rd->receta_id}): Dispensado ({$rd->cantidad_dispensada}) excede lo recetado ({$rd->cantidad_recetada}).");
                $recetasSobregiradas++;
                $inconsistencias++;
            }
        }

        if ($recetasSobregiradas === 0) {
            $this->line("   <fg=green>✓ [OK]</> Todas las recetas médicas auditadas ({$recetaDetalles->count()}) cumplen con el límite de prescripción.");
        } else {
            $this->line("   <fg=red>✗ [FALLO]</> Se encontraron {$recetasSobregiradas} prescripciones sobregiradas.");
        }

        // 5. Auditoría de Efectivo y Vuelto
        $this->newLine();
        $this->info('5. Verificando cálculo de efectivo, vuelto y sesiones de caja...');
        $ventasEfectivoErroneas = 0;

        $ventasEfectivo = Venta::where('metodo_pago', 'efectivo')
            ->where('estado', 'completada')
            ->latest('id')
            ->limit($limit)
            ->get();

        foreach ($ventasEfectivo as $ve) {
            if ($ve->monto_recibido !== null) {
                $vueltoEsperado = max(0, round((float)$ve->monto_recibido - (float)$ve->total, 2));
                if (abs((float)$ve->cambio - $vueltoEsperado) > 0.05) {
                    $this->warn("Venta en efectivo #{$ve->id}: Vuelto registrado C$ {$ve->cambio} != Esperado C$ {$vueltoEsperado}");
                    $ventasEfectivoErroneas++;
                    $inconsistencias++;
                }
            }
        }

        if ($ventasEfectivoErroneas === 0) {
            $this->line("   <fg=green>✓ [OK]</> Todas las ventas en efectivo ({$ventasEfectivo->count()}) tienen cálculo exacto de cambio/vuelto.");
        } else {
            $this->line("   <fg=red>✗ [FALLO]</> Se encontraron {$ventasEfectivoErroneas} ventas con cálculo erróneo de vuelto.");
        }

        // Resumen Final
        $this->newLine();
        $this->info('================================================================');
        if ($inconsistencias === 0) {
            $this->info('  RESULTADO: 5/5 VERIFICACIONES EXITOSAS — 0 INCONSISTENCIAS   ');
        } else {
            $this->error("  RESULTADO: SE DETECTARON {$inconsistencias} INCONSISTENCIAS   ");
        }
        $this->info('================================================================');

        return $inconsistencias === 0 ? Command::SUCCESS : Command::FAILURE;
    }
}
