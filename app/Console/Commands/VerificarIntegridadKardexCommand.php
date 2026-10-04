<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Lote;
use App\Models\Producto;
use App\Models\MovimientoInventario;
use Illuminate\Support\Facades\DB;

class VerificarIntegridadKardexCommand extends Command
{
    protected $signature = 'farma:verificar-kardex';
    protected $description = 'Verifica la integridad de datos, trazabilidad y los invariantes matemáticos del Kardex y Lotes (Solo Lectura)';

    public function handle(): int
    {
        $this->info("================================================================================");
        $this->info(" AUDITORÍA DE INTEGRIDAD DE DATOS Y KARDEX (SOLO LECTURA)");
        $this->info(" Módulo: Lotes y Registro de Movimientos de Stock (Pasada 1)");
        $this->info("================================================================================");
        $this->newLine();

        $errores = 0;
        $advertencias = 0;

        // ── 1. INVARIANTE 1: Stock del lote == Suma de sus movimientos ────────────────
        $this->comment("1. Verificando Invariante 1: [Stock del Lote == SUM(Movimientos)]...");
        
        $lotes = Lote::with('movimientos')->get();
        $lotesDiscrepantes = [];

        foreach ($lotes as $lote) {
            $sumaKardex = 0;
            foreach ($lote->movimientos as $m) {
                $delta = ($m->stock_posterior - $m->stock_anterior);
                $sumaKardex += $delta;
            }

            if ($lote->movimientos->isNotEmpty() && (int)$lote->stock_actual !== $sumaKardex) {
                $lotesDiscrepantes[] = [
                    'id'          => $lote->id,
                    'numero_lote' => $lote->numero_lote,
                    'stock_lote'  => (int)$lote->stock_actual,
                    'suma_kardex' => $sumaKardex,
                ];
            }
        }

        if (empty($lotesDiscrepantes)) {
            $this->info("  ✓ Invariante 1 superado: 100% de los lotes concilian exactamente con su Kardex.");
        } else {
            $errores += count($lotesDiscrepantes);
            $this->error("  ✗ Invariante 1 violado en " . count($lotesDiscrepantes) . " lote(s):");
            foreach ($lotesDiscrepantes as $d) {
                $this->line("    - Lote #{$d['id']} ({$d['numero_lote']}): Stock Lote = {$d['stock_lote']} | Suma Kardex = {$d['suma_kardex']}");
            }
        }
        $this->newLine();

        // ── 2. INVARIANTE 2: Stock del Producto == Suma de Lotes ──────────────────────
        $this->comment("2. Verificando Invariante 2: [Stock del Producto == SUM(Lotes Activos)]...");
        
        $productos = Producto::with(['lotes' => fn($q) => $q->where('activo', true)])->get();
        $prodDiscrepantes = 0;

        foreach ($productos as $p) {
            $stockLotes = $p->lotes->sum('stock_actual');
            $stockCalculado = $p->stock_total;

            if ($stockLotes !== $stockCalculado) {
                $prodDiscrepantes++;
                $this->warn("    - Producto #{$p->id} ({$p->nombre}): Stock Lotes = {$stockLotes} | Stock Calculado = {$stockCalculado}");
            }
        }

        if ($prodDiscrepantes === 0) {
            $this->info("  ✓ Invariante 2 superado: 100% de los medicamentos concilian entre sus lotes y su stock total.");
        } else {
            $advertencias += $prodDiscrepantes;
            $this->warn("  ! Invariante 2 tiene {$prodDiscrepantes} discrepancia(s) por lotes inactivos.");
        }
        $this->newLine();

        // ── 3. INVARIANTE 3: Continuidad de Saldos en Kardex ─────────────────────────
        $this->comment("3. Verificando Invariante 3: [Continuidad Consecutiva de Saldos sin huecos]...");
        
        $lotesConMovimientos = Lote::has('movimientos')->pluck('id');
        $huecosDetectados = 0;

        foreach ($lotesConMovimientos as $loteId) {
            $movimientos = MovimientoInventario::where('lote_id', $loteId)
                ->orderBy('fecha_movimiento', 'asc')
                ->orderBy('id', 'asc')
                ->get(['id', 'stock_anterior', 'stock_posterior', 'cantidad']);

            $saldoEsperado = 0;
            foreach ($movimientos as $idx => $m) {
                if ($idx === 0) {
                    $saldoEsperado = $m->stock_posterior;
                    continue;
                }

                if ($m->stock_anterior !== $saldoEsperado) {
                    $huecosDetectados++;
                    $this->error("    - Discontinuidad en Movimiento #{$m->id} (Lote #{$loteId}): Anterior = {$m->stock_anterior} | Esperado = {$saldoEsperado}");
                }
                $saldoEsperado = $m->stock_posterior;
            }
        }

        if ($huecosDetectados === 0) {
            $this->info("  ✓ Invariante 3 superado: La cadena de saldos anterior/posterior es 100% consecutiva.");
        } else {
            $errores += $huecosDetectados;
            $this->error("  ✗ Invariante 3 violado en {$huecosDetectados} punto(s) de la línea temporal.");
        }
        $this->newLine();

        // ── 4. INVARIANTE 4: No Negatividad ──────────────────────────────────────────
        $this->comment("4. Verificando Invariante 4: [No Negatividad de Stock en Lotes]...");
        
        $lotesNegativos = Lote::where('stock_actual', '<', 0)->count();
        if ($lotesNegativos === 0) {
            $this->info("  ✓ Invariante 4 superado: Cero lotes con stock negativo en la base de datos.");
        } else {
            $errores += $lotesNegativos;
            $this->error("  ✗ Invariante 4 violado: Se detectaron {$lotesNegativos} lote(s) con stock negativo.");
        }
        $this->newLine();

        // ── 5. INVARIANTE 5: Integridad Referencial de Kardex ────────────────────────
        $this->comment("5. Verificando Invariante 5: [Integridad Referencial de Movimientos]...");
        
        $movsHuerfanos = MovimientoInventario::whereDoesntHave('producto')
            ->orWhereDoesntHave('lote')
            ->orWhereDoesntHave('usuario')
            ->count();

        if ($movsHuerfanos === 0) {
            $this->info("  ✓ Invariante 5 superado: Cero movimientos huérfanos sin producto, lote o usuario.");
        } else {
            $errores += $movsHuerfanos;
            $this->error("  ✗ Invariante 5 violado: Se detectaron {$movsHuerfanos} movimiento(s) huérfanos.");
        }
        $this->newLine();

        // ── RESUMEN FINAL ───────────────────────────────────────────────────────────
        $this->info("================================================================================");
        if ($errores === 0) {
            $this->info(" ESTADO: SISTEMA ÍNTEGRO (0 Inconsistencias Críticas detectadas)");
            $this->info("================================================================================");
            return Command::SUCCESS;
        } else {
            $this->error(" ESTADO: SE DETECTARON {$errores} ERROR(ES) DE INTEGRIDAD CRÍTICA.");
            $this->info("================================================================================");
            return Command::FAILURE;
        }
    }
}
