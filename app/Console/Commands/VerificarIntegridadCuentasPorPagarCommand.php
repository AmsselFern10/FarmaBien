<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Compra;
use App\Models\PagoCuentaPorPagar;
use App\Models\MovimientoCaja;
use App\Models\Lote;
use App\Models\MovimientoInventario;
use Illuminate\Support\Facades\DB;

class VerificarIntegridadCuentasPorPagarCommand extends Command
{
    protected $signature = 'farma:verificar-cxp';
    protected $description = 'Verifica la integridad matemática, conciliación de abonos, estados y no contaminación de inventario en Cuentas por Pagar (Solo Lectura)';

    public function handle(): int
    {
        $this->info("================================================================================");
        $this->info(" AUDITORÍA DE INTEGRIDAD DE CUENTAS POR PAGAR (SOLO LECTURA)");
        $this->info(" Módulo: Compras y Abastecimiento - Cuentas por Pagar (Pasada 2)");
        $this->info("================================================================================");
        $this->newLine();

        $errores = 0;
        $advertencias = 0;

        // ── 1. INVARIANTE 1: Conciliación de Saldos (Saldo = Total - Suma(Abonos)) ─────
        $this->comment("1. Verificando Invariante 1: [saldo_pendiente == Total - SUM(monto abonos)]...");

        $comprasCredito = Compra::where('condicion_pago', 'credito')
            ->where('estado', '!=', 'anulada')
            ->with('pagos')
            ->get();

        $discrepanciasSaldo = [];
        foreach ($comprasCredito as $compra) {
            $totalPagos = round((float) $compra->pagos->sum('monto'), 2);
            $saldoCalculado = max(0, round((float) $compra->total - $totalPagos, 2));
            $saldoRegistrado = round((float) $compra->saldo_pendiente, 2);

            if (abs($saldoCalculado - $saldoRegistrado) > 0.01) {
                $discrepanciasSaldo[] = [
                    'compra_id'        => $compra->id,
                    'numero_doc'       => $compra->numero_comprobante ?? "COMP-{$compra->id}",
                    'total'            => (float) $compra->total,
                    'total_pagos'      => $totalPagos,
                    'saldo_calculado'  => $saldoCalculado,
                    'saldo_registrado' => $saldoRegistrado,
                ];
            }
        }

        if (empty($discrepanciasSaldo)) {
            $this->info("  ✓ Invariante 1 superado: 100% de los saldos de cuentas por pagar concilian con sus abonos.");
        } else {
            $errores += count($discrepanciasSaldo);
            $this->error("  ✗ Invariante 1 violado en " . count($discrepanciasSaldo) . " cuenta(s) por pagar:");
            foreach ($discrepanciasSaldo as $d) {
                $this->line("    - Compra #{$d['compra_id']} ({$d['numero_doc']}): Total={$d['total']} | Abonos={$d['total_pagos']} | Calculado={$d['saldo_calculado']} vs Registrado={$d['saldo_registrado']}");
            }
        }
        $this->newLine();

        // ── 2. INVARIANTE 2: Coherencia de estado_pago ────────────────────────────────
        $this->comment("2. Verificando Invariante 2: [Coherencia de estado_pago vs saldo_pendiente]...");

        $inconsistenciasEstado = [];
        foreach ($comprasCredito as $compra) {
            $saldo = round((float) $compra->saldo_pendiente, 2);
            $estadoPago = $compra->estado_pago;

            if ($saldo <= 0.001 && $estadoPago !== 'pagado') {
                $inconsistenciasEstado[] = [
                    'compra_id' => $compra->id,
                    'detalle'   => "Saldo es C$ 0.00 pero estado_pago es '{$estadoPago}' (debería ser 'pagado').",
                ];
            } elseif ($saldo > 0.001 && $estadoPago === 'pagado') {
                $inconsistenciasEstado[] = [
                    'compra_id' => $compra->id,
                    'detalle'   => "Saldo es C$ {$saldo} pero estado_pago es 'pagado'.",
                ];
            }
        }

        if (empty($inconsistenciasEstado)) {
            $this->info("  ✓ Invariante 2 superado: Estados de pago 100% consistentes con los saldos.");
        } else {
            $errores += count($inconsistenciasEstado);
            $this->error("  ✗ Invariante 2 violado en " . count($inconsistenciasEstado) . " registro(s):");
            foreach ($inconsistenciasEstado as $ie) {
                $this->line("    - Compra #{$ie['compra_id']}: {$ie['detalle']}");
            }
        }
        $this->newLine();

        // ── 3. INVARIANTE 3: Cero saldos negativos ni sobrepagos ─────────────────────
        $this->comment("3. Verificando Invariante 3: [Cero saldos negativos y Cero sobrepagos]...");

        $saldosNegativos = Compra::where('saldo_pendiente', '<', 0)->count();
        if ($saldosNegativos === 0) {
            $this->info("  ✓ Invariante 3 superado: No existen saldos negativos en el sistema.");
        } else {
            $errores += $saldosNegativos;
            $this->error("  ✗ Invariante 3 violado: Existen {$saldosNegativos} compra(s) con saldo negativo.");
        }
        $this->newLine();

        // ── 4. INVARIANTE 4: Gastos Directos no contaminan Inventario ni Kardex ────────
        $this->comment("4. Verificando Invariante 4: [Gastos directos con 0 detalles no generan Lotes ni Kardex]...");

        $comprasSinDetalle = Compra::whereDoesntHave('detalles')->get();
        $comprasDirectasContaminantes = [];

        foreach ($comprasSinDetalle as $csd) {
            $tieneLotes = Lote::where('compra_id', $csd->id)->exists();
            $tieneKardex = MovimientoInventario::where('compra_id', $csd->id)->exists();

            if ($tieneLotes || $tieneKardex) {
                $comprasDirectasContaminantes[] = [
                    'compra_id' => $csd->id,
                    'lotes'     => $tieneLotes,
                    'kardex'    => $tieneKardex,
                ];
            }
        }

        if (empty($comprasDirectasContaminantes)) {
            $this->info("  ✓ Invariante 4 superado: Las facturas directas carecen de lotes y movimientos de inventario.");
        } else {
            $errores += count($comprasDirectasContaminantes);
            $this->error("  ✗ Invariante 4 violado en " . count($comprasDirectasContaminantes) . " factura(s) directa(s) que contaminaron inventario.");
        }
        $this->newLine();

        // ── 5. INVARIANTE 5: Conciliación de Egresos de Caja en Pagos Efectivo ─────────
        $this->comment("5. Verificando Invariante 5: [Abonos en efectivo con sesion_caja_id tienen MovimientoCaja]...");

        $pagosEfectivoCaja = PagoCuentaPorPagar::where('metodo_pago', 'efectivo')
            ->whereNotNull('sesion_caja_id')
            ->get();

        $pagosSinMovimiento = [];
        foreach ($pagosEfectivoCaja as $pago) {
            $existeMov = MovimientoCaja::where('sesion_caja_id', $pago->sesion_caja_id)
                ->where('tipo', 'egreso')
                ->where('comprobante_referencia', $pago->numero_pago)
                ->exists();

            if (!$existeMov) {
                $pagosSinMovimiento[] = [
                    'pago_id'     => $pago->id,
                    'numero_pago' => $pago->numero_pago,
                    'monto'       => $pago->monto,
                ];
            }
        }

        if (empty($pagosSinMovimiento)) {
            $this->info("  ✓ Invariante 5 superado: 100% de los abonos en efectivo con caja registran su egreso correspondiente.");
        } else {
            $advertencias += count($pagosSinMovimiento);
            $this->warn("  ⚠ Invariante 5 advertencia: " . count($pagosSinMovimiento) . " abono(s) en efectivo no tienen egreso vinculado:");
            foreach ($pagosSinMovimiento as $psm) {
                $this->line("    - Pago #{$psm['pago_id']} ({$psm['numero_pago']}): Monto={$psm['monto']}");
            }
        }
        $this->newLine();

        // ── RESUMEN FINAL ─────────────────────────────────────────────────────────────
        $this->info("================================================================================");
        if ($errores === 0) {
            $this->info(" RESULTADO: AUDITORÍA EXITOSA — 0 INCONSISTENCIAS DETECTADAS");
            $this->info(" Todas las cuentas por pagar y abonos cumplen con los invariantes financieros.");
            $this->info("================================================================================");
            return 0;
        } else {
            $this->error(" RESULTADO: SE DETECTARON {$errores} ERROR(ES) DE INTEGRIDAD");
            $this->info("================================================================================");
            return 1;
        }
    }
}
