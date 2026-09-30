@extends('layouts.app')

@section('title', 'Dashboard - FarmaBien')

@push('scripts')
<script>
function dashboardLive() {
    return {
        cargando: false,
        autoRefresh: false,
        timer: null,
        ultimaActualizacion: '{{ now()->format('h:i:s A') }}',
        metricas: {
            ventasHoy: {{ (float)$ventasHoy }},
            ventasHoyFormateado: '{{ number_format($ventasHoy, 2) }}',
            cantidadVentasHoy: {{ (int)$cantidadVentasHoy }},
            comprasMes: {{ (float)$comprasMes }},
            comprasMesFormateado: '{{ number_format($comprasMes, 2) }}',
            productosBajoStockCount: {{ (int)$productosBajoStockCount }},
            lotesPorVencerCount: {{ (int)$lotesPorVencerCount }},
            recetasPendientesCount: {{ (int)$recetasPendientesCount }},
            ultimasVentas: @js($ultimasVentas->map(fn($v) => [
                'id' => $v->id,
                'numero_comprobante' => $v->numero_comprobante,
                'tipo_comprobante' => $v->tipo_comprobante,
                'cliente' => $v->cliente?->nombre ?? 'Público General / Venta Mostrador',
                'usuario' => $v->usuario?->name ?? 'Sistema',
                'total' => number_format($v->total, 2),
                'metodo_pago' => ucfirst($v->metodo_pago),
                'fecha_hora' => $v->fecha ? \Carbon\Carbon::parse($v->fecha)->format('d/m H:i') : $v->created_at->format('d/m H:i'),
                'url' => route('ventas.show', $v->id)
            ])),
            ultimasCompras: @js($ultimasCompras->map(fn($c) => [
                'id' => $c->id,
                'numero_comprobante' => $c->numero_comprobante ?? 'S/N',
                'proveedor' => $c->proveedor?->nombre ?? 'Proveedor Droguería',
                'usuario' => $c->usuario?->name ?? 'Sistema',
                'total' => number_format($c->total, 2),
                'estado' => ucfirst($c->estado),
                'fecha' => $c->fecha ? \Carbon\Carbon::parse($c->fecha)->format('d/m/Y') : $c->created_at->format('d/m/Y'),
                'url' => route('compras.show', $c->id)
            ]))
        },
        init() {
            const savedAuto = localStorage.getItem('farma_dashboard_autorefresh');
            if (savedAuto === 'true') {
                this.toggleAutoRefresh(true);
            }
        },
        toggleAutoRefresh(force = null) {
            this.autoRefresh = force !== null ? force : !this.autoRefresh;
            localStorage.setItem('farma_dashboard_autorefresh', this.autoRefresh);
            if (this.autoRefresh) {
                this.timer = setInterval(() => this.refrescar(false), 30000);
            } else {
                clearInterval(this.timer);
                this.timer = null;
            }
        },
        async refrescar(manual = true) {
            if (this.cargando) return;
            this.cargando = true;
            try {
                const res = await fetch(`{{ route('dashboard.api.metricas') }}?fresh=${manual ? 1 : 0}`);
                if (res.ok) {
                    const data = await res.json();
                    if (data.success) {
                        this.metricas.ventasHoy = data.ventasHoy;
                        this.metricas.ventasHoyFormateado = data.ventasHoyFormateado;
                        this.metricas.cantidadVentasHoy = data.cantidadVentasHoy;
                        this.metricas.comprasMes = data.comprasMes;
                        this.metricas.comprasMesFormateado = data.comprasMesFormateado;
                        this.metricas.productosBajoStockCount = data.productosBajoStockCount;
                        this.metricas.lotesPorVencerCount = data.lotesPorVencerCount;
                        this.metricas.recetasPendientesCount = data.recetasPendientesCount;
                        if (data.ultimasVentas) this.metricas.ultimasVentas = data.ultimasVentas;
                        if (data.ultimasCompras) this.metricas.ultimasCompras = data.ultimasCompras;
                        this.ultimaActualizacion = data.horaActualizacion;
                    }
                }
            } catch (e) {
                console.error('Error al actualizar dashboard:', e);
            } finally {
                this.cargando = false;
            }
        }
    };
}
</script>
@endpush

@section('content')
<div x-data="dashboardLive()" x-init="init()" class="space-y-6 animate-fadeIn">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-1 border-b border-slate-200 dark:border-slate-800">
        <div>
            <div class="flex items-center space-x-2">
                <h1 class="text-xl font-bold text-slate-900 dark:text-white">Panel Principal</h1>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1 animate-pulse"></span>
                    En Vivo
                </span>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 flex items-center space-x-2">
                <span>Resumen operativo &bull; {{ now()->translatedFormat('l, d \d\e F \d\e Y') }}</span>
                <span>&bull;</span>
                <span class="text-slate-400">Actualizado: <strong class="font-mono" x-text="ultimaActualizacion"></strong></span>
            </p>
        </div>

        <div class="flex items-center flex-wrap gap-2">
            <!-- Botón Refrescar en Vivo -->
            <button type="button" 
                    @click="refrescar(true)"
                    :disabled="cargando"
                    title="Actualizar datos en tiempo real"
                    class="inline-flex items-center space-x-1.5 px-3 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold rounded-xl shadow-2xs transition cursor-pointer disabled:opacity-50">
                <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" :class="{ 'animate-spin': cargando }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <span x-text="cargando ? 'Actualizando...' : 'Refrescar'">Refrescar</span>
            </button>

            <!-- Auto Refresh Toggle -->
            <button type="button" 
                    @click="toggleAutoRefresh()"
                    :class="autoRefresh ? 'bg-emerald-50 text-emerald-700 border-emerald-300 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800 font-bold' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border-slate-300 dark:border-slate-700'"
                    class="inline-flex items-center space-x-1.5 px-3 py-2 border rounded-xl text-xs font-semibold shadow-2xs transition cursor-pointer">
                <span class="w-2 h-2 rounded-full" :class="autoRefresh ? 'bg-emerald-500 animate-ping' : 'bg-slate-400'"></span>
                <span x-text="autoRefresh ? 'Auto (30s) ON' : 'Auto'">Auto</span>
            </button>

            @can('realizar ventas')
            <a href="{{ route('ventas.create') }}" 
               class="inline-flex items-center space-x-2 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-semibold rounded-xl shadow-xs transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Nueva Venta</span>
                <kbd class="ml-1 px-1 py-0.2 bg-emerald-800 text-emerald-100 rounded text-[10px] font-mono">F2</kbd>
            </a>
            @endcan

            @can('registrar compras')
            <a href="{{ route('compras.create') }}" 
               class="inline-flex items-center space-x-2 px-3.5 py-2 bg-slate-800 hover:bg-slate-700 dark:bg-slate-700 dark:hover:bg-slate-600 text-white text-xs font-semibold rounded-xl shadow-xs transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <span>Recepción Compra</span>
                <kbd class="ml-1 px-1 py-0.2 bg-slate-900 text-slate-300 rounded text-[10px] font-mono">F4</kbd>
            </a>
            @endcan
        </div>
    </div>

    <!-- KPI Cards: Filtradas estrictamente por RBAC -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Ventas Hoy -->
        @canany(['ver ventas', 'ver ventas propias', 'ver reportes ventas'])
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm transition hover:shadow-md">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                    @if(!auth()->user()->can('ver ventas') && auth()->user()->can('ver ventas propias'))
                        Mis Ventas Hoy
                    @else
                        Ventas Hoy
                    @endif
                </span>
                <div class="w-8 h-8 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black text-slate-900 dark:text-white">
                    $<span x-text="metricas.ventasHoyFormateado">{{ number_format($ventasHoy, 2) }}</span>
                </div>
                <div class="flex items-center justify-between mt-2 text-xs">
                    <span class="text-slate-500 dark:text-slate-400">
                        <strong x-text="metricas.cantidadVentasHoy">{{ $cantidadVentasHoy }}</strong> tickets emitidos
                    </span>
                    @can('ver ventas')
                    <a href="{{ route('ventas.index') }}" class="font-bold text-emerald-600 dark:text-emerald-400 hover:underline">Ver todas &rarr;</a>
                    @else
                    <span class="text-slate-400 dark:text-slate-500">Turno en curso</span>
                    @endcan
                </div>
            </div>
        </div>
        @endcanany

        <!-- Compras del Mes -->
        @canany(['ver compras', 'ver reportes compras'])
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm transition hover:shadow-md">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Compras del Mes</span>
                <div class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black text-slate-900 dark:text-white">
                    $<span x-text="metricas.comprasMesFormateado">{{ number_format($comprasMes, 2) }}</span>
                </div>
                <div class="flex items-center justify-between mt-2 text-xs">
                    <span class="text-slate-500 dark:text-slate-400">Inversión acumulada</span>
                    @can('ver compras')
                    <a href="{{ route('compras.index') }}" class="font-bold text-slate-700 dark:text-slate-300 hover:underline">Ver compras &rarr;</a>
                    @else
                    <span class="text-slate-400 dark:text-slate-500">Acumulado</span>
                    @endcan
                </div>
            </div>
        </div>
        @endcanany

        <!-- Stock Crítico -->
        @canany(['ver alertas stock bajo', 'ver productos'])
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm transition hover:shadow-md">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Stock Crítico</span>
                <div class="w-8 h-8 rounded-xl flex items-center justify-center" 
                     :class="metricas.productosBajoStockCount > 0 ? 'bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400' : 'bg-slate-100 dark:bg-slate-800 text-slate-400'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
            </div>
            <div class="mt-3">
                <div class="flex items-baseline space-x-1.5">
                    <span class="text-2xl font-black" 
                          :class="metricas.productosBajoStockCount > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-slate-900 dark:text-white'"
                          x-text="metricas.productosBajoStockCount">
                        {{ $productosBajoStockCount }}
                    </span>
                    <span class="text-xs text-slate-500 font-semibold">fármacos</span>
                </div>
                <div class="flex items-center justify-between mt-2 text-xs">
                    <span class="text-slate-500 dark:text-slate-400">Bajo el mínimo</span>
                    @can('ver alertas stock bajo')
                    <a href="{{ route('inventario.alertas') }}" class="font-bold text-amber-600 dark:text-amber-400 hover:underline">Ver alertas &rarr;</a>
                    @else
                    <a href="{{ route('productos.index') }}" class="font-bold text-amber-600 dark:text-amber-400 hover:underline">Catálogo &rarr;</a>
                    @endcan
                </div>
            </div>
        </div>
        @endcanany

        <!-- Lotes por Vencer -->
        @canany(['ver alertas vencimientos', 'ver lotes'])
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm transition hover:shadow-md">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Lotes &lt; 30 días</span>
                <div class="w-8 h-8 rounded-xl flex items-center justify-center" 
                     :class="metricas.lotesPorVencerCount > 0 ? 'bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400' : 'bg-slate-100 dark:bg-slate-800 text-slate-400'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
            </div>
            <div class="mt-3">
                <div class="flex items-baseline space-x-1.5">
                    <span class="text-2xl font-black" 
                          :class="metricas.lotesPorVencerCount > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-900 dark:text-white'"
                          x-text="metricas.lotesPorVencerCount">
                        {{ $lotesPorVencerCount }}
                    </span>
                    <span class="text-xs text-slate-500 font-semibold">lotes</span>
                </div>
                <div class="flex items-center justify-between mt-2 text-xs">
                    <span class="text-slate-500 dark:text-slate-400">Por expirar</span>
                    @can('ver lotes')
                    <a href="{{ route('inventario.lotes') }}" class="font-bold text-rose-600 dark:text-rose-400 hover:underline">Auditar &rarr;</a>
                    @else
                    <span class="text-slate-400 dark:text-slate-500">En monitoreo</span>
                    @endcan
                </div>
            </div>
        </div>
        @endcanany
    </div>

    <!-- Barra de Alerta Clínica Si Hay Recetas Pendientes -->
    @canany(['ver recetas', 'validar recetas', 'dispensar recetas'])
    <div x-show="metricas.recetasPendientesCount > 0" 
         x-cloak 
         class="bg-amber-50/90 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 rounded-2xl p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 shadow-xs">
        <div class="flex items-center space-x-3">
            <span class="text-amber-600 dark:text-amber-400 text-xl">⚠️</span>
            <div>
                <h4 class="text-sm font-bold text-slate-900 dark:text-slate-100">
                    Hay <span x-text="metricas.recetasPendientesCount"></span> receta(s) médica(s) pendiente(s) de validación
                </h4>
                <p class="text-xs text-slate-600 dark:text-slate-400 mt-0.5">
                    Requiere confirmación farmacéutica antes del despacho en caja.
                </p>
            </div>
        </div>
        <a href="{{ route('recetas.index') }}" 
           class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl shadow-xs transition shrink-0">
            Atender Recetas
        </a>
    </div>
    @endcanany

    <!-- Tablas de Actividad Reciente (Condicionadas por Rol) -->
    @php
        $canSeeVentas = auth()->user()->canAny(['ver ventas', 'ver ventas propias']);
        $canSeeCompras = auth()->user()->can('ver compras');
    @endphp

    @if($canSeeVentas || $canSeeCompras)
    <div class="grid grid-cols-1 {{ $canSeeVentas && $canSeeCompras ? 'lg:grid-cols-2' : '' }} gap-6">
        <!-- Últimas Ventas -->
        @if($canSeeVentas)
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/30">
                <div class="flex items-center space-x-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white">
                        @if(!auth()->user()->can('ver ventas') && auth()->user()->can('ver ventas propias'))
                            Mis Ventas Recientes
                        @else
                            Últimas Ventas
                        @endif
                    </h3>
                </div>
                @can('ver ventas')
                <a href="{{ route('ventas.index') }}" class="text-xs font-bold text-emerald-600 dark:text-emerald-400 hover:underline">
                    Ver historial &rarr;
                </a>
                @endcan
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-800/60 overflow-x-auto">
                <template x-for="venta in metricas.ultimasVentas" :key="venta.id">
                    <div class="px-5 py-3.5 flex items-center justify-between hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition">
                        <div class="flex items-center space-x-3 min-w-0">
                            <span class="text-xs font-mono font-bold text-slate-400 dark:text-slate-500 shrink-0" x-text="'#' + venta.id"></span>
                            <div class="truncate">
                                <p class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate" x-text="venta.cliente"></p>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                    <span x-text="venta.fecha_hora"></span> &bull; Cajero: <span x-text="venta.usuario"></span>
                                </p>
                            </div>
                        </div>
                        <div class="text-right shrink-0 ml-4">
                            <span class="text-sm font-extrabold text-slate-900 dark:text-white block" x-text="'$' + venta.total"></span>
                            <div class="flex items-center justify-end space-x-1 mt-0.5">
                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300" x-text="venta.metodo_pago"></span>
                                @canany(['ver ventas', 'ver detalle ventas'])
                                <a :href="venta.url" class="text-slate-400 hover:text-emerald-600 dark:hover:text-emerald-400 text-xs pl-1 font-bold">
                                    &rarr;
                                </a>
                                @endcanany
                            </div>
                        </div>
                    </div>
                </template>
                <div x-show="metricas.ultimasVentas.length === 0" class="p-8 text-center text-slate-400 text-xs">
                    No hay ventas registradas hoy.
                </div>
            </div>
        </div>
        @endif

        <!-- Últimas Compras -->
        @if($canSeeCompras)
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/30">
                <div class="flex items-center space-x-2">
                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white">Últimas Recepciones de Compra</h3>
                </div>
                <a href="{{ route('compras.index') }}" class="text-xs font-bold text-slate-700 dark:text-slate-300 hover:underline">
                    Ver registro &rarr;
                </a>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-800/60 overflow-x-auto">
                <template x-for="compra in metricas.ultimasCompras" :key="compra.id">
                    <div class="px-5 py-3.5 flex items-center justify-between hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition">
                        <div class="flex items-center space-x-3 min-w-0">
                            <span class="text-xs font-mono font-bold text-slate-400 dark:text-slate-500 shrink-0" x-text="'#' + compra.id"></span>
                            <div class="truncate">
                                <p class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate" x-text="compra.proveedor"></p>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                    Factura: <span x-text="compra.numero_comprobante"></span> &bull; <span x-text="compra.fecha"></span>
                                </p>
                            </div>
                        </div>
                        <div class="text-right shrink-0 ml-4">
                            <span class="text-sm font-extrabold text-slate-900 dark:text-white block" x-text="'$' + compra.total"></span>
                            <div class="flex items-center justify-end space-x-1 mt-0.5">
                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 dark:bg-blue-950/50 dark:text-blue-300" x-text="compra.estado"></span>
                                @can('ver detalle compras')
                                <a :href="compra.url" class="text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 text-xs pl-1 font-bold">
                                    &rarr;
                                </a>
                                @endcan
                            </div>
                        </div>
                    </div>
                </template>
                <div x-show="metricas.ultimasCompras.length === 0" class="p-8 text-center text-slate-400 text-xs">
                    No hay compras recientes registradas.
                </div>
            </div>
        </div>
        @endif
    </div>
    @endif
</div>
@endsection

