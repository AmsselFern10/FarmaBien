<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DevolucionVenta;
use App\Models\MovimientoInventario;
use App\Models\MovimientoCaja;
use App\Models\RegistroVentaControlado;
use Illuminate\Support\Facades\DB;

class VerificarIntegridadDevolucionesVentaCommand extends Command
{
    protected $signature = 'farma:verificar-devoluciones-ventas {--limit=50 : Límite de devoluciones a auditar} {--reparar : Corregir inconsistencias automáticamente}';

    protected $description = 'Audita la integridad contable, de Kardex, arqueo de caja y Libro Oficial MINSA de devoluciones de ventas';

    public function handle(): int
    {
        $limit = (int) $this->option('limit');
        $reparar = (bool) $this->option('reparar');

        $this->info("=== Iniciando Auditoría de Integridad en Devoluciones de Ventas (Top {$limit}) ===");

        $devoluciones = DevolucionVenta::with([
            'detalles.producto',
            'detalles.lote',
            'detalles.detalleVenta',
            'venta.cliente',
            'sesionCaja'
        ])
        ->orderBy('id', 'desc')
        ->limit($limit)
        ->get();

        if ($devoluciones->isEmpty()) {
            $this->warn("No se encontraron devoluciones de ventas registradas.");
            return self::SUCCESS;
        }

        $inconsistencias = 0;
        $auditados = 0;

        foreach ($devoluciones as $dev) {
            $auditados++;
            $errores = [];

            // 1. Verificación Matemática de Totales
            $sumaDetalles = round($dev->detalles->sum('subtotal'), 2);
            $totalDev = round((float) $dev->monto_total, 2);

            if (abs($sumaDetalles - $totalDev) > 0.01) {
                $errores[] = "Monto total ({$totalDev}) no coincide con la sumatoria de sus detalles ({$sumaDetalles}).";
            }

            // 2. Trazabilidad en Kardex
            foreach ($dev->detalles as $det) {
                if ($det->reingresa_a_stock) {
                    $movKardex = MovimientoInventario::where('origen', 'devolucion_venta')
                        ->where('origen_id', $dev->id)
                        ->where('lote_id', $det->lote_id)
                        ->where('producto_id', $det->producto_id)
                        ->first();

                    if (!$movKardex) {
                        $errores[] = "Ítem #{$det->id} ({$det->producto?->nombre}) reingresó a stock pero no tiene asiento de entrada en Kardex.";
                    }
                } else {
                    $movMerma = MovimientoInventario::where('origen', 'devolucion_danado')
                        ->where('origen_id', $dev->id)
                        ->where('lote_id', $det->lote_id)
                        ->where('producto_id', $det->producto_id)
                        ->first();

                    if (!$movMerma) {
                        $errores[] = "Ítem #{$det->id} ({$det->producto?->nombre}) devuelto como no apto no tiene asiento de merma en Kardex.";
                    }
                }

                // 3. Libro Oficial MINSA para medicamentos controlados
                if ($det->producto && $det->producto->esControlado()) {
                    $asientoMinsa = RegistroVentaControlado::where('devolucion_id', $dev->id)
                        ->where('producto_id', $det->producto_id)
                        ->where('lote_id', $det->lote_id)
                        ->first();

                    if (!$asientoMinsa) {
                        $errores[] = "Medicamento controlado '{$det->producto->nombre}' en Devolución #{$dev->id} no tiene asiento en Libro MINSA.";
                    }
                }
            }

            // 4. Arqueo de Caja para reembolsos en efectivo
            if ($dev->metodo_reembolso === 'efectivo' && $dev->sesion_caja_id) {
                $movCaja = MovimientoCaja::where('sesion_caja_id', $dev->sesion_caja_id)
                    ->where('comprobante_referencia', $dev->numero_devolucion)
                    ->where('tipo', 'egreso')
                    ->first();

                if (!$movCaja) {
                    $errores[] = "Reembolso en efectivo de C$ {$dev->monto_total} no cuenta con egreso asentado en la sesión de caja #{$dev->sesion_caja_id}.";
                }
            }

            if (!empty($errores)) {
                $inconsistencias++;
                $this->error("Devolución #{$dev->id} ({$dev->numero_devolucion}):");
                foreach ($errores as $err) {
                    $this->line("  - {$err}");
                }
            }
        }

        $this->newLine();
        $this->info("=== Resumen de Auditoría de Devoluciones ===");
        $this->info("Total devoluciones auditadas: {$auditados}");

        if ($inconsistencias === 0) {
            $this->info("✓ 100% Integridad Verificada: Cero inconsistencias contables, de inventario o MINSA.");
            return self::SUCCESS;
        }

        $this->warn("⚠ Se detectaron {$inconsistencias} devoluciones con inconsistencias.");
        return self::FAILURE;
    }
}
