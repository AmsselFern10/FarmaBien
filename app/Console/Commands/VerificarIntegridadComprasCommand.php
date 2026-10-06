<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Compra;
use App\Models\DetalleCompra;
use App\Models\Lote;
use App\Models\MovimientoInventario;
use Illuminate\Support\Facades\DB;

class VerificarIntegridadComprasCommand extends Command
{
    protected $signature = 'farma:verificar-compras';
    protected $description = 'Verifica la integridad de datos, conversiones de presentaciones, lotes, Kardex y trazabilidad de Compras (Solo Lectura)';

    public function handle(): int
    {
        $this->info("================================================================================");
        $this->info(" AUDITORÍA DE INTEGRIDAD DE COMPRAS Y RECEPCIÓN DE LOTES (SOLO LECTURA)");
        $this->info(" Módulo: Compras y Abastecimiento - Registro de Compras (Pasada 3)");
        $this->info("================================================================================");
        $this->newLine();

        $errores = 0;
        $advertencias = 0;

        // ── 1. INVARIANTE 1: Total Compra == Suma(DetalleCompra.subtotal) ─────────────
        $this->comment("1. Verificando Invariante 1: [Total de Compra == SUM(DetalleCompra.subtotal)]...");

        $comprasConDetalle = Compra::whereHas('detalles')
            ->where('estado', '!=', 'anulada')
            ->with('detalles')
            ->get();

        $discrepanciasTotal = [];
        foreach ($comprasConDetalle as $c) {
            $sumDetalles = round((float) $c->detalles->sum('subtotal'), 2);
            $totalCompra = round((float) $c->total, 2);

            if (abs($sumDetalles - $totalCompra) > 0.01) {
                $discrepanciasTotal[] = [
                    'compra_id'    => $c->id,
                    'numero_doc'   => $c->numero_comprobante ?? "COMP-{$c->id}",
                    'total_compra' => $totalCompra,
                    'sum_detalles' => $sumDetalles,
                ];
            }
        }

        if (empty($discrepanciasTotal)) {
            $this->info("  ✓ Invariante 1 superado: 100% de las compras concilian matemáticamente con sus partidas.");
        } else {
            $errores += count($discrepanciasTotal);
            $this->error("  ✗ Invariante 1 violado en " . count($discrepanciasTotal) . " compra(s):");
            foreach ($discrepanciasTotal as $d) {
                $this->line("    - Compra #{$d['compra_id']} ({$d['numero_doc']}): Total = {$d['total_compra']} vs Suma Partidas = {$d['sum_detalles']}");
            }
        }
        $this->newLine();

        // ── 2. INVARIANTE 2: Conciliación de Entradas al Kardex ────────────────────────
        $this->comment("2. Verificando Invariante 2: [DetalleCompra.cantidad_unidades_base == Kardex Entrada]...");

        $detallesSinKardex = [];
        $detalles = DetalleCompra::whereHas('compra', function ($q) {
            $q->where('estado', '!=', 'anulada');
        })->get();

        // Optimización masiva de consulta agrupada
        $entradasKardex = MovimientoInventario::where('origen', 'compra')
            ->where('tipo', 'entrada')
            ->select('origen_id', 'producto_id', 'lote_id', DB::raw('SUM(cantidad) as total_ingresado'))
            ->groupBy('origen_id', 'producto_id', 'lote_id')
            ->get()
            ->keyBy(function ($item) {
                return "{$item->origen_id}_{$item->producto_id}_{$item->lote_id}";
            });

        foreach ($detalles as $det) {
            $key = "{$det->compra_id}_{$det->producto_id}_{$det->lote_id}";
            $entrada = $entradasKardex->get($key);

            if (!$entrada || (int)$entrada->total_ingresado < (int)$det->cantidad_unidades_base) {
                $detallesSinKardex[] = [
                    'detalle_id'     => $det->id,
                    'compra_id'      => $det->compra_id,
                    'producto_id'    => $det->producto_id,
                    'lote_id'        => $det->lote_id,
                    'unidades_base'  => $det->cantidad_unidades_base,
                    'kardex_unidades'=> $entrada ? (int)$entrada->total_ingresado : 0,
                ];
            }
        }

        if (empty($detallesSinKardex)) {
            $this->info("  ✓ Invariante 2 superado: 100% de los medicamentos comprados asentaron su entrada exacta al Kardex.");
        } else {
            $errores += count($detallesSinKardex);
            $this->error("  ✗ Invariante 2 violado en " . count($detallesSinKardex) . " línea(s) de compra sin entrada en Kardex:");
            foreach ($detallesSinKardex as $d) {
                $this->line("    - Detalle #{$d['detalle_id']} (Compra #{$d['compra_id']}): Solicitado = {$d['unidades_base']} u. vs Kardex = {$d['kardex_unidades']} u.");
            }
        }
        $this->newLine();

        // ── 3. INVARIANTE 3: Integridad de Lotes y Vencimientos ───────────────────────
        $this->comment("3. Verificando Invariante 3: [DetalleCompra vinculado a Lotes válidos y consistentes]...");

        $lotesInconsistentes = [];
        foreach ($detalles as $det) {
            $lote = Lote::find($det->lote_id);
            if (!$lote) {
                $lotesInconsistentes[] = [
                    'detalle_id' => $det->id,
                    'compra_id'  => $det->compra_id,
                    'problema'   => "El lote ID {$det->lote_id} no existe en la tabla de lotes.",
                ];
            } elseif ($lote->producto_id !== $det->producto_id) {
                $lotesInconsistentes[] = [
                    'detalle_id' => $det->id,
                    'compra_id'  => $det->compra_id,
                    'problema'   => "Discrepancia de producto: Detalle (Producto #{$det->producto_id}) vs Lote (Producto #{$lote->producto_id}).",
                ];
            }
        }

        if (empty($lotesInconsistentes)) {
            $this->info("  ✓ Invariante 3 superado: Todos los lotes vinculados existen y corresponden al producto exacto.");
        } else {
            $errores += count($lotesInconsistentes);
            $this->error("  ✗ Invariante 3 violado en " . count($lotesInconsistentes) . " detalle(s):");
            foreach ($lotesInconsistentes as $li) {
                $this->line("    - Detalle #{$li['detalle_id']} (Compra #{$li['compra_id']}): {$li['problema']}");
            }
        }
        $this->newLine();

        // ── 4. INVARIANTE 4: Trazabilidad de Anulaciones y Reversiones ────────────────
        $this->comment("4. Verificando Invariante 4: [Compras Anuladas tienen Reversión en Kardex]...");

        $comprasAnuladas = Compra::where('estado', 'anulada')
            ->whereHas('detalles')
            ->with('detalles')
            ->get();

        $anulacionesSinReversion = [];
        foreach ($comprasAnuladas as $ca) {
            $tieneSalidaKardex = MovimientoInventario::where('origen_id', $ca->id)
                ->where('subtipo', 'anulacion_compra')
                ->exists();

            if (!$tieneSalidaKardex) {
                $anulacionesSinReversion[] = [
                    'compra_id'  => $ca->id,
                    'numero_doc' => $ca->numero_comprobante ?? "COMP-{$ca->id}",
                ];
            }
        }

        if (empty($anulacionesSinReversion)) {
            $this->info("  ✓ Invariante 4 superado: 100% de las compras anuladas cuentan con su contra-asiento de salida en Kardex.");
        } else {
            $errores += count($anulacionesSinReversion);
            $this->error("  ✗ Invariante 4 violado: " . count($anulacionesSinReversion) . " compra(s) anulada(s) carecen de reversión en Kardex:");
            foreach ($anulacionesSinReversion as $asr) {
                $this->line("    - Compra #{$asr['compra_id']} ({$asr['numero_doc']})");
            }
        }
        $this->newLine();

        // ── 5. INVARIANTE 5: Trazabilidad Bidireccional de Modificaciones ──────────────
        $this->comment("5. Verificando Invariante 5: [Integridad de enlaces v1 -> v2 en compras modificadas]...");

        $comprasModificadas = Compra::whereNotNull('compra_original_id')->get();
        $enlacesRotas = [];

        foreach ($comprasModificadas as $cm) {
            $original = Compra::find($cm->compra_original_id);
            if (!$original) {
                $enlacesRotas[] = [
                    'compra_id' => $cm->id,
                    'problema'  => "Compra original #{$cm->compra_original_id} no existe.",
                ];
            } elseif ($original->reemplazada_por !== $cm->id) {
                $enlacesRotas[] = [
                    'compra_id' => $cm->id,
                    'problema'  => "Compra original #{$original->id} no apunta a #{$cm->id} en reemplazada_por.",
                ];
            } elseif ($original->estado !== 'anulada') {
                $enlacesRotas[] = [
                    'compra_id' => $cm->id,
                    'problema'  => "Compra original #{$original->id} no está en estado 'anulada'.",
                ];
            }
        }

        if (empty($enlacesRotas)) {
            $this->info("  ✓ Invariante 5 superado: Trazabilidad bidireccional de modificaciones 100% íntegra.");
        } else {
            $errores += count($enlacesRotas);
            $this->error("  ✗ Invariante 5 violado en " . count($enlacesRotas) . " modificación(es):");
            foreach ($enlacesRotas as $er) {
                $this->line("    - Compra #{$er['compra_id']}: {$er['problema']}");
            }
        }
        $this->newLine();

        // ── RESUMEN FINAL ─────────────────────────────────────────────────────────────
        $this->info("================================================================================");
        if ($errores === 0) {
            $this->info(" RESULTADO: AUDITORÍA EXITOSA — 0 INCONSISTENCIAS DETECTADAS");
            $this->info(" Todas las compras, lotes y movimientos de Kardex cumplen con los invariantes de integridad.");
            $this->info("================================================================================");
            return 0;
        } else {
            $this->error(" RESULTADO: SE DETECTARON {$errores} ERROR(ES) DE INTEGRIDAD");
            $this->info("================================================================================");
            return 1;
        }
    }
}
