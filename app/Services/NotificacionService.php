<?php

namespace App\Services;

use App\Models\Producto;
use App\Models\Lote;
use App\Models\Compra;
use App\Models\HistorialPrecio;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class NotificacionService
{
    /**
     * Obtener resumen unificado de notificaciones para el Centro de Notificaciones
     *
     * @return array
     */
    public function getResumenNotificaciones(): array
    {
        // Cachear por 30 segundos para evitar saturación de BD en polling de topbar
        return Cache::remember('farma_notificaciones_resumen', 30, function () {
            $stockAlertas = $this->getStockAlerts();
            $vencimientosAlertas = $this->getVencimientosAlerts();
            $cuentasPorPagarAlertas = $this->getCuentasPorPagarAlerts();
            $reordenAlertas = $this->getReordenAlerts();

            $totalNotificaciones = count($stockAlertas) 
                + count($vencimientosAlertas) 
                + count($cuentasPorPagarAlertas) 
                + count($reordenAlertas);

            return [
                'total_count'          => $totalNotificaciones,
                'stock'                => $stockAlertas,
                'stock_count'          => count($stockAlertas),
                'vencimientos'         => $vencimientosAlertas,
                'vencimientos_count'   => count($vencimientosAlertas),
                'cuentas_pagar'        => $cuentasPorPagarAlertas,
                'cuentas_pagar_count'  => count($cuentasPorPagarAlertas),
                'reorden'              => $reordenAlertas,
                'reorden_count'        => count($reordenAlertas),
            ];
        });
    }

    /**
     * Alertas de Stock Crítico y Agotado
     */
    protected function getStockAlerts(): array
    {
        $productos = DB::table('productos as p')
            ->select('p.id', 'p.nombre', 'p.stock_minimo',
                DB::raw('COALESCE(SUM(l.stock_actual), 0) as stock_total'))
            ->leftJoin('lotes as l', function ($join) {
                $join->on('p.id', '=', 'l.producto_id')
                     ->where('l.activo', '=', 1)
                     ->where('l.stock_actual', '>', 0);
            })
            ->where('p.activo', true)
            ->whereNull('p.deleted_at')
            ->groupBy('p.id', 'p.nombre', 'p.stock_minimo')
            ->havingRaw('COALESCE(SUM(l.stock_actual), 0) <= p.stock_minimo')
            ->orderBy('stock_total', 'asc')
            ->limit(10)
            ->get();

        $alertas = [];
        foreach ($productos as $p) {
            $stock = (int) ($p->stock_total ?? 0);
            $esAgotado = $stock === 0;

            $alertas[] = [
                'id'       => 'stock_' . $p->id,
                'tipo'     => $esAgotado ? 'agotado' : 'stock_bajo',
                'icono'    => $esAgotado ? 'circle-x' : 'alert-triangle',
                'urgencia' => $esAgotado ? 'critica' : 'media',
                'titulo'   => $esAgotado ? 'Producto Agotado' : 'Stock Crítico',
                'mensaje'  => "{$p->nombre}: {$stock} unidades (Mín: {$p->stock_minimo})",
                'url'      => route('inventario.alertas'),
                'fecha'    => now()->toISOString(),
            ];
        }

        return $alertas;
    }

    /**
     * Alertas de Lotes Vencidos y por Vencer
     */
    protected function getVencimientosAlerts(): array
    {
        $lotes = Lote::with(['producto:id,nombre'])
            ->where('activo', true)
            ->where('stock_actual', '>', 0)
            ->where('fecha_vencimiento', '<=', now()->addDays(60)->toDateString())
            ->orderBy('fecha_vencimiento', 'asc')
            ->limit(10)
            ->get();

        $alertas = [];
        foreach ($lotes as $l) {
            $dias = (int) now()->startOfDay()->diffInDays($l->fecha_vencimiento->startOfDay(), false);
            $estaVencido = $dias <= 0;

            $alertas[] = [
                'id'       => 'lote_' . $l->id,
                'tipo'     => $estaVencido ? 'lote_vencido' : 'lote_por_vencer',
                'icono'    => $estaVencido ? 'flame' : 'clock',
                'urgencia' => $estaVencido ? 'critica' : ($dias <= 15 ? 'alta' : 'media'),
                'titulo'   => $estaVencido ? 'Lote Vencido en Stock' : 'Lote por Vencer (' . $dias . 'd)',
                'mensaje'  => ($l->producto->nombre ?? 'Medicamento') . " - Lote: {$l->numero_lote} ({$l->stock_actual} uds) vence {$l->fecha_vencimiento->format('d/m/Y')}",
                'url'      => route('inventario.lotes'),
                'fecha'    => now()->toISOString(),
            ];
        }

        return $alertas;
    }

    /**
     * Alertas de Cuentas por Pagar (Compras a Crédito por Vencer o Vencidas)
     */
    protected function getCuentasPorPagarAlerts(): array
    {
        $cuentas = Compra::with(['proveedor:id,nombre'])
            ->where('condicion_pago', 'credito')
            ->where('estado', 'recibida')
            ->whereIn('estado_pago', ['pendiente', 'parcial', 'vencido'])
            ->where('saldo_pendiente', '>', 0)
            ->orderBy('fecha_vencimiento_pago', 'asc')
            ->limit(10)
            ->get();

        $alertas = [];
        foreach ($cuentas as $c) {
            $fechaVenc = $c->fecha_vencimiento_pago;
            $dias = $fechaVenc ? (int) now()->startOfDay()->diffInDays($fechaVenc->startOfDay(), false) : 0;
            $vencida = $fechaVenc && $dias < 0;

            if ($vencida || $dias <= 7) {
                $alertas[] = [
                    'id'       => 'cxp_' . $c->id,
                    'tipo'     => $vencida ? 'cxp_vencida' : 'cxp_por_vencer',
                    'icono'    => 'credit-card',
                    'urgencia' => $vencida ? 'critica' : 'alta',
                    'titulo'   => $vencida ? 'Factura Proveedor Vencida' : 'Pago a Proveedor Próximo',
                    'mensaje'  => ($c->proveedor->nombre ?? 'Proveedor') . ": " . formato_moneda($c->saldo_pendiente) . ($fechaVenc ? " (Vence {$fechaVenc->format('d/m/Y')})" : ''),
                    'url'      => route('cuentas-por-pagar.show', $c->id),
                    'fecha'    => now()->toISOString(),
                ];
            }
        }

        return $alertas;
    }

    /**
     * Alertas de Sugerencias de Reorden Automático
     */
    protected function getReordenAlerts(): array
    {
        $sugerenciasCount = DB::table('productos as p')
            ->leftJoin('lotes as l', function ($join) {
                $join->on('p.id', '=', 'l.producto_id')
                     ->where('l.activo', '=', 1)
                     ->where('l.stock_actual', '>', 0);
            })
            ->where('p.activo', true)
            ->groupBy('p.id', 'p.nombre', 'p.stock_minimo')
            ->havingRaw('COALESCE(SUM(l.stock_actual), 0) <= p.stock_minimo')
            ->count();

        if ($sugerenciasCount > 0) {
            return [
                [
                    'id'       => 'reorden_sugerencias',
                    'tipo'     => 'reorden',
                    'icono'    => 'shopping-bag',
                    'urgencia' => 'media',
                    'titulo'   => 'Sugerencias de Reorden',
                    'mensaje'  => "Hay {$sugerenciasCount} producto(s) que requieren reorden urgente según stock e historial.",
                    'url'      => route('compras.sugerencias-reorden'),
                    'fecha'    => now()->toISOString(),
                ]
            ];
        }

        return [];
    }
}
