<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\HistorialPrecio;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Compra;
use App\Models\PresentacionProducto;

class VerificarIntegridadComparadorPreciosCommand extends Command
{
    protected $signature = 'farma:verificar-comparador-precios';
    protected $description = 'Auditoría de integridad de solo lectura para el Comparador de Precios y Cotizaciones (Pasada 2)';

    public function handle(): int
    {
        $this->info("================================================================================");
        $this->info(" AUDITORÍA DE INTEGRIDAD DE COMPARADOR DE PRECIOS Y COTIZACIONES (SOLO LECTURA)");
        $this->info(" Módulo: Compras & Abastecimiento — Comparador & Cotizaciones (Pasada 2)");
        $this->info("================================================================================");

        $errores = 0;

        // Invariante 1: precio_unitario_base == round(precio_compra / unidades_por_presentacion, 4)
        $this->newLine();
        $this->info("1. Verificando Invariante 1: [precio_unitario_base == round(precio_compra / unidades_por_presentacion, 4)]...");
        $historiales = HistorialPrecio::all();
        $descuadresCosto = 0;

        foreach ($historiales as $h) {
            $unidades = max(1, (int) $h->unidades_por_presentacion);
            $calculado = round((float) $h->precio_compra / $unidades, 4);
            $registrado = (float) $h->precio_unitario_base;

            if (abs($calculado - $registrado) > 0.001) {
                $this->error("  ✖ Descuadre en HistorialPrecio #{$h->id}: Registrado C$ {$registrado} != Calculado C$ {$calculado} ({$h->precio_compra} / {$unidades})");
                $descuadresCosto++;
                $errores++;
            }
        }

        if ($descuadresCosto === 0) {
            $this->line("  <info>✓ Invariante 1 superado:</info> 100% de los precios unitarios base concilian matemáticamente con sus factores.");
        }

        // Invariante 2: Registros de tipo 'compra' vinculados a compras existentes
        $this->newLine();
        $this->info("2. Verificando Invariante 2: [Historiales de tipo 'compra' con compra_id válido]...");
        $comprasHuerfanas = HistorialPrecio::where('tipo', 'compra')
            ->whereNotNull('compra_id')
            ->whereDoesntHave('compra')
            ->count();

        if ($comprasHuerfanas > 0) {
            $this->error("  ✖ Se detectaron {$comprasHuerfanas} registros de compra huérfanos.");
            $errores++;
        } else {
            $this->line("  <info>✓ Invariante 2 superado:</info> 100% de los registros de compras históricas están correctamente vinculados.");
        }

        // Invariante 3: Cero precios <= 0
        $this->newLine();
        $this->info("3. Verificando Invariante 3: [Cero precios negativos o en cero en historiales]...");
        $preciosInvalidos = HistorialPrecio::where('precio_compra', '<=', 0)
            ->orWhere('precio_unitario_base', '<=', 0)
            ->count();

        if ($preciosInvalidos > 0) {
            $this->error("  ✖ Se detectaron {$preciosInvalidos} registros con precio menor o igual a cero.");
            $errores++;
        } else {
            $this->line("  <info>✓ Invariante 3 superado:</info> Todos los precios de compra y unitarios base son estrictamente positivos.");
        }

        // Invariante 4: Coherencia de Producto y Presentación
        $this->newLine();
        $this->info("4. Verificando Invariante 4: [Presentaciones asignadas corresponden al producto exacto]...");
        $historialesConPres = HistorialPrecio::whereNotNull('presentacion_id')->with('presentacion')->get();
        $presInconsistentes = 0;

        foreach ($historialesConPres as $hp) {
            if ($hp->presentacion && $hp->presentacion->producto_id !== $hp->producto_id) {
                $this->error("  ✖ HistorialPrecio #{$hp->id} (Prod ID {$hp->producto_id}) apunta a Presentación ID {$hp->presentacion_id} de Producto ID {$hp->presentacion->producto_id}.");
                $presInconsistentes++;
                $errores++;
            }
        }

        if ($presInconsistentes === 0) {
            $this->line("  <info>✓ Invariante 4 superado:</info> Coherencia de presentaciones con sus productos 100% íntegra.");
        }

        // Invariante 5: Integridad de Proveedores activos/existentes
        $this->newLine();
        $this->info("5. Verificando Invariante 5: [Proveedores referenciados existen en el catálogo]...");
        $provHuerfanos = HistorialPrecio::whereDoesntHave('proveedor')->count();

        if ($provHuerfanos > 0) {
            $this->error("  ✖ Se detectaron {$provHuerfanos} registros con proveedores no existentes.");
            $errores++;
        } else {
            $this->line("  <info>✓ Invariante 5 superado:</info> Todos los proveedores referenciados son válidos.");
        }

        $this->newLine();
        $this->info("================================================================================");
        if ($errores === 0) {
            $this->info(" RESULTADO: AUDITORÍA EXITOSA — 0 INCONSISTENCIAS DETECTADAS");
            $this->info(" Todo el historial de precios, cotizaciones y comparativas cumple los invariantes.");
        } else {
            $this->error(" RESULTADO: SE ENCONTRARON {$errores} INCONSISTENCIAS.");
        }
        $this->info("================================================================================");

        return $errores === 0 ? 0 : 1;
    }
}
