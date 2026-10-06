<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DevolucionCompra;
use App\Models\DetalleDevolucionCompra;
use App\Models\Lote;
use App\Models\MovimientoInventario;
use App\Models\RegistroVentaControlado;
use Illuminate\Support\Facades\DB;

class VerificarIntegridadDevolucionesProveedorCommand extends Command
{
    protected $signature = 'farma:verificar-devoluciones-proveedor';
    protected $description = 'Auditoría de integridad de solo lectura para el módulo de Devoluciones a Proveedor (Pasada 1)';

    public function handle(): int
    {
        $this->info("================================================================================");
        $this->info(" AUDITORÍA DE INTEGRIDAD DE DEVOLUCIONES A PROVEEDOR (SOLO LECTURA)");
        $this->info(" Módulo: Compras & Abastecimiento — Devoluciones (Pasada 1)");
        $this->info("================================================================================");

        $errores = 0;

        // Invariante 1: Total Devolución == SUM(DetalleDevolucionCompra.subtotal)
        $this->newLine();
        $this->info("1. Verificando Invariante 1: [Total de Devolución == SUM(DetalleDevolucionCompra.subtotal)]...");
        $devoluciones = DevolucionCompra::with('detalles')->get();
        $descuadresTotal = 0;

        foreach ($devoluciones as $dev) {
            $sumaDetalles = round((float) $dev->detalles->sum('subtotal'), 2);
            $totalCabecera = round((float) $dev->total_devolucion, 2);

            if (abs($sumaDetalles - $totalCabecera) > 0.01) {
                $this->error("  ✖ Descuadre en Devolución {$dev->numero_devolucion}: Total cabecera C$ {$totalCabecera} != Suma partidas C$ {$sumaDetalles}");
                $descuadresTotal++;
                $errores++;
            }
        }

        if ($descuadresTotal === 0) {
            $this->line("  <info>✓ Invariante 1 superado:</info> 100% de las devoluciones concilian matemáticamente con sus partidas.");
        }

        // Invariante 2: DetalleDevolucionCompra tiene salida en Kardex
        $this->newLine();
        $this->info("2. Verificando Invariante 2: [Cada devolución activa cuenta con salida en Kardex]...");
        $devolucionesActivas = DevolucionCompra::where('estado', '!=', 'rechazada')->with('detalles')->get();
        $kardexHuerfanos = 0;

        foreach ($devolucionesActivas as $dev) {
            $movsKardex = MovimientoInventario::where('origen', 'devolucion_compra')
                ->where('origen_id', $dev->id)
                ->get();

            if ($movsKardex->count() < $dev->detalles->count()) {
                $this->error("  ✖ Devolución {$dev->numero_devolucion} tiene {$dev->detalles->count()} partidas pero solo {$movsKardex->count()} movimientos en Kardex.");
                $kardexHuerfanos++;
                $errores++;
            }
        }

        if ($kardexHuerfanos === 0) {
            $this->line("  <info>✓ Invariante 2 superado:</info> 100% de las devoluciones activas registraron sus salidas exactas en Kardex.");
        }

        // Invariante 3: Controlados con asiento en Libro Oficial MINSA
        $this->newLine();
        $this->info("3. Verificando Invariante 3: [Devolución de productos controlados tiene asiento en Libro MINSA]...");
        $detallesControlados = DetalleDevolucionCompra::whereHas('producto', function ($q) {
            $q->where('tipo_control', 'controlado')->orWhere('requiere_receta', true);
        })->whereHas('devolucionCompra', function ($q) {
            $q->where('estado', '!=', 'rechazada');
        })->with(['devolucionCompra', 'producto'])->get();

        $minsaFaltantes = 0;
        foreach ($detallesControlados as $det) {
            $asiento = RegistroVentaControlado::where('lote_id', $det->lote_id)
                ->where('motivo_omision', 'like', "%{$det->devolucionCompra->numero_devolucion}%")
                ->first();

            if (!$asiento) {
                $this->error("  ✖ Detalle de devolución #{$det->id} (Producto '{$det->producto->nombre}') carece de asiento fiscal en el Libro MINSA.");
                $minsaFaltantes++;
                $errores++;
            }
        }

        if ($minsaFaltantes === 0) {
            $this->line("  <info>✓ Invariante 3 superado:</info> 100% de los fármacos controlados devueltos están fiscalizados en el Libro MINSA.");
        }

        // Invariante 4: Cero Lotes con stock negativo post-devolución
        $this->newLine();
        $this->info("4. Verificando Invariante 4: [Cero lotes con stock negativo]...");
        $lotesNegativos = Lote::where('stock_actual', '<', 0)->count();

        if ($lotesNegativos > 0) {
            $this->error("  ✖ Se detectaron {$lotesNegativos} lotes con stock negativo.");
            $errores++;
        } else {
            $this->line("  <info>✓ Invariante 4 superado:</info> No existen lotes con stock negativo en el sistema.");
        }

        // Invariante 5: Integridad de Proveedor con Compra de origen
        $this->newLine();
        $this->info("5. Verificando Invariante 5: [Coherencia de Proveedor en Devolución vs Compra]...");
        $devolucionesConCompra = DevolucionCompra::whereNotNull('compra_id')->with('compra')->get();
        $proveedoresInconsistentes = 0;

        foreach ($devolucionesConCompra as $dev) {
            if ($dev->compra && $dev->proveedor_id !== $dev->compra->proveedor_id) {
                $this->error("  ✖ Devolución {$dev->numero_devolucion} asignada al proveedor ID {$dev->proveedor_id} pero su compra origen #{$dev->compra_id} pertenece al proveedor ID {$dev->compra->proveedor_id}.");
                $proveedoresInconsistentes++;
                $errores++;
            }
        }

        if ($proveedoresInconsistentes === 0) {
            $this->line("  <info>✓ Invariante 5 superado:</info> Coherencia de entidad Proveedor 100% consistente.");
        }

        $this->newLine();
        $this->info("================================================================================");
        if ($errores === 0) {
            $this->info(" RESULTADO: AUDITORÍA EXITOSA — 0 INCONSISTENCIAS DETECTADAS");
            $this->info(" Todas las devoluciones a proveedor, lotes, Kardex y Libro MINSA cumplen los invariantes.");
        } else {
            $this->error(" RESULTADO: SE ENCONTRARON {$errores} INCONSISTENCIAS.");
        }
        $this->info("================================================================================");

        return $errores === 0 ? 0 : 1;
    }
}
