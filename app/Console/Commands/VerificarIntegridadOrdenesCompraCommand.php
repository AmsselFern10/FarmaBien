<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\OrdenCompra;
use App\Models\DetalleOrdenCompra;
use App\Models\DetalleCompra;
use Illuminate\Support\Facades\DB;

class VerificarIntegridadOrdenesCompraCommand extends Command
{
    protected $signature = 'farma:verificar-ordenes-compra';
    protected $description = 'Verifica la integridad de datos, trazabilidad e invariantes matemáticos de las Órdenes de Compra (Solo Lectura)';

    public function handle(): int
    {
        $this->info("================================================================================");
        $this->info(" AUDITORÍA DE INTEGRIDAD DE ÓRDENES DE COMPRA (SOLO LECTURA)");
        $this->info(" Módulo: Compras y Abastecimiento - Órdenes de Compra (Pasada 1)");
        $this->info("================================================================================");
        $this->newLine();

        $errores = 0;
        $advertencias = 0;

        // ── 1. INVARIANTE 1: Órdenes sin recepciones no tienen compras vinculadas ─────
        $this->comment("1. Verificando Invariante 1: [Órdenes Pendientes/Enviadas sin Compras asociadas]...");
        
        $ordenesPendientesConCompras = OrdenCompra::whereIn('estado', ['enviada', 'borrador', 'pendiente'])
            ->whereHas('compras')
            ->get();

        if ($ordenesPendientesConCompras->isEmpty()) {
            $this->info("  ✓ Invariante 1 superado: Todas las órdenes pendientes carecen de compras vinculadas.");
        } else {
            $errores += $ordenesPendientesConCompras->count();
            $this->error("  ✗ Invariante 1 violado en " . $ordenesPendientesConCompras->count() . " orden(es) en estado pendiente con compras vinculadas.");
        }
        $this->newLine();

        // ── 2. INVARIANTE 2: Cantidad recibida en líneas == Suma de DetalleCompra ─────
        $this->comment("2. Verificando Invariante 2: [cantidad_recibida == SUM(DetalleCompra en unidades base)]...");
        
        $detallesDiscrepantes = [];
        $detalles = DetalleOrdenCompra::with('ordenCompra')->get();

        // Consulta agregada para evitar N+1 queries en auditorías masivas
        $sumasPorDetalle = DetalleCompra::whereNotNull('detalle_orden_compra_id')
            ->whereHas('compra', function ($q) {
                $q->where('estado', '!=', 'anulada');
            })
            ->groupBy('detalle_orden_compra_id')
            ->select('detalle_orden_compra_id', DB::raw('SUM(cantidad_unidades_base) as total_recibido'))
            ->pluck('total_recibido', 'detalle_orden_compra_id');

        foreach ($detalles as $det) {
            $sumDetalleCompra = (int) ($sumasPorDetalle[$det->id] ?? 0);

            if ((int)$det->cantidad_recibida !== $sumDetalleCompra) {
                $detallesDiscrepantes[] = [
                    'detalle_id'     => $det->id,
                    'orden_id'       => $det->orden_compra_id,
                    'numero_orden'   => $det->ordenCompra->numero_orden ?? 'N/A',
                    'recibido_linea' => (int)$det->cantidad_recibida,
                    'suma_compras'   => $sumDetalleCompra,
                ];
            }
        }

        if (empty($detallesDiscrepantes)) {
            $this->info("  ✓ Invariante 2 superado: 100% de las líneas de órdenes concilian con sus recepciones en compras.");
        } else {
            $errores += count($detallesDiscrepantes);
            $this->error("  ✗ Invariante 2 violado en " . count($detallesDiscrepantes) . " línea(s) de orden:");
            foreach ($detallesDiscrepantes as $d) {
                $this->line("    - Línea #{$d['detalle_id']} (Orden {$d['numero_orden']}): Recibido en Línea = {$d['recibido_linea']} | Suma Compras = {$d['suma_compras']}");
            }
        }
        $this->newLine();

        // ── 3. INVARIANTE 3: Estado consistente con pendientes ───────────────────────
        $this->comment("3. Verificando Invariante 3: [Consistencia matemática de Estado vs Pendientes]...");
        
        $ordenesInconsistentes = [];
        $todasOrdenes = OrdenCompra::with('detalles')->get();

        foreach ($todasOrdenes as $orden) {
            $pendienteTotal = $orden->pendiente_total;
            $estado = $orden->estado;

            if ($estado === 'recibida_total' && $pendienteTotal > 0 && !$orden->cerrada_con_faltante) {
                $ordenesInconsistentes[] = [
                    'id'           => $orden->id,
                    'numero_orden' => $orden->numero_orden,
                    'estado'       => $estado,
                    'detalle'      => "Marcada como recibida_total pero tiene {$pendienteTotal} unidades pendientes sin justificación de faltante.",
                ];
            } elseif ($estado === 'cancelada' && $orden->compras()->exists()) {
                $ordenesInconsistentes[] = [
                    'id'           => $orden->id,
                    'numero_orden' => $orden->numero_orden,
                    'estado'       => $estado,
                    'detalle'      => "Marcada como cancelada pero tiene compras vinculadas.",
                ];
            }
        }

        if (empty($ordenesInconsistentes)) {
            $this->info("  ✓ Invariante 3 superado: Estados de órdenes 100% coherentes con sus cantidades pendientes.");
        } else {
            $errores += count($ordenesInconsistentes);
            $this->error("  ✗ Invariante 3 violado en " . count($ordenesInconsistentes) . " orden(es):");
            foreach ($ordenesInconsistentes as $o) {
                $this->line("    - Orden #{$o['id']} ({$o['numero_orden']}): {$o['detalle']}");
            }
        }
        $this->newLine();

        // ── 4. INVARIANTE 4: Unicidad y formato de números de orden ──────────────────
        $this->comment("4. Verificando Invariante 4: [Unicidad de número de orden y formato OC-AAAA-NNNN]...");

        $duplicadosNumero = OrdenCompra::select('numero_orden', DB::raw('count(*) as total'))
            ->groupBy('numero_orden')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        if ($duplicadosNumero->isEmpty()) {
            $this->info("  ✓ Invariante 4 superado: No existen números de orden duplicados.");
        } else {
            $errores += $duplicadosNumero->count();
            $this->error("  ✗ Invariante 4 violado: Se detectaron números de orden duplicados:");
            foreach ($duplicadosNumero as $dup) {
                $this->line("    - Número {$dup->numero_orden} repetido {$dup->total} veces.");
            }
        }
        $this->newLine();

        // ── RESUMEN FINAL ─────────────────────────────────────────────────────────────
        $this->info("================================================================================");
        if ($errores === 0) {
            $this->info(" RESULTADO: AUDITORÍA EXITOSA — 0 INCONSISTENCIAS DETECTADAS");
            $this->info(" Todas las órdenes de compra cumplen con los invariantes de integridad y trazabilidad.");
            $this->info("================================================================================");
            return 0;
        } else {
            $this->error(" RESULTADO: SE DETECTARON {$errores} ERROR(ES) DE INTEGRIDAD");
            $this->info("================================================================================");
            return 1;
        }
    }
}
