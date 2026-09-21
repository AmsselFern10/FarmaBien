@extends('layouts.app')

@section('title', 'Centro de Reportes Gerenciales - FarmaBien')

@section('content')
<div class="space-y-5">

    {{-- Breadcrumb --}}
    <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-800 dark:text-slate-200 font-semibold">Centro de Reportes</span>
    </nav>

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-1 border-b border-slate-300/80 dark:border-slate-800">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">Centro de Reportes y Estadísticas</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Métricas financieras, rotación farmacéutica y análisis operativo consolidado.</p>
        </div>
        @can('ver reportes ventas')
        <a href="{{ route('reportes.ventas') }}"
           class="inline-flex items-center space-x-1.5 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-sm transition shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            <span>Reporte de Ventas</span>
        </a>
        @endcan
    </div>

    {{-- KPI Cards del Mes --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        {{-- Ventas Hoy --}}
        <div class="bg-white dark:bg-slate-900 rounded-xl p-3.5 border border-slate-300 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Ventas Hoy</p>
                <p class="text-lg font-bold text-slate-900 dark:text-white mt-0.5">${{ number_format($ventasHoy, 2) }}</p>
                <p class="text-[10px] text-slate-400 mt-0.5">Día actual</p>
            </div>
            <div class="w-9 h-9 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
        {{-- Ventas del Mes --}}
        <div class="bg-white dark:bg-slate-900 rounded-xl p-3.5 border border-slate-300 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Ventas del Mes</p>
                <p class="text-lg font-bold text-emerald-600 dark:text-emerald-400 mt-0.5">${{ number_format($ventasMes, 2) }}</p>
                <p class="text-[10px] text-slate-400 mt-0.5">{{ $cantidadVentasMes }} transacciones</p>
            </div>
            <div class="w-9 h-9 rounded-lg bg-teal-50 dark:bg-teal-950/60 text-teal-600 dark:text-teal-400 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            </div>
        </div>
        {{-- Compras del Mes --}}
        <div class="bg-white dark:bg-slate-900 rounded-xl p-3.5 border border-slate-300 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Compras del Mes</p>
                <p class="text-lg font-bold text-amber-600 dark:text-amber-400 mt-0.5">${{ number_format($comprasMes, 2) }}</p>
                <p class="text-[10px] text-slate-400 mt-0.5">Reabastecimiento</p>
            </div>
            <div class="w-9 h-9 rounded-lg bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
            </div>
        </div>
        {{-- Valorización --}}
        <div class="bg-white dark:bg-slate-900 rounded-xl p-3.5 border border-slate-300 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Valor Stock</p>
                <p class="text-lg font-bold text-indigo-600 dark:text-indigo-400 mt-0.5">${{ number_format($valorizacion['valor_total'] ?? ($valorizacion['costo_total'] ?? 0), 2) }}</p>
                <p class="text-[10px] text-slate-400 mt-0.5">Valorización costo</p>
            </div>
            <div class="w-9 h-9 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </div>
        </div>
    </div>

    {{-- Alertas rápidas --}}
    @if($lotesVencidosCount > 0 || $productosBajoStockCount > 0)
    <div class="flex flex-wrap gap-3">
        @if($lotesVencidosCount > 0)
        <a href="{{ route('reportes.inventario', ['estado_vencimiento' => 'vencidos']) }}"
           class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl border border-rose-300 dark:border-rose-700 bg-rose-50 dark:bg-rose-950/30 text-rose-700 dark:text-rose-400 text-xs font-bold hover:bg-rose-100 dark:hover:bg-rose-900/40 transition">
            <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
            {{ $lotesVencidosCount }} {{ $lotesVencidosCount === 1 ? 'lote vencido' : 'lotes vencidos' }} con stock
        </a>
        @endif
        @if($lotesCriticosCount > 0)
        <a href="{{ route('reportes.inventario', ['estado_vencimiento' => 'critico_30']) }}"
           class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl border border-amber-300 dark:border-amber-700 bg-amber-50 dark:bg-amber-950/30 text-amber-700 dark:text-amber-400 text-xs font-bold hover:bg-amber-100 dark:hover:bg-amber-900/40 transition">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            {{ $lotesCriticosCount }} vencen en ≤ 30 días
        </a>
        @endif
        @if($productosBajoStockCount > 0)
        <a href="{{ route('reportes.productos-bajo-stock') }}"
           class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl border border-amber-300 dark:border-amber-700 bg-amber-50 dark:bg-amber-950/30 text-amber-700 dark:text-amber-400 text-xs font-bold hover:bg-amber-100 dark:hover:bg-amber-900/40 transition">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/></svg>
            {{ $productosBajoStockCount }} productos bajo mínimo
        </a>
        @endif
    </div>
    @endif

    {{-- Catálogo de Módulos --}}
    <div>
        <h2 class="text-sm font-bold text-slate-900 dark:text-white mb-3">📑 Catálogo de Informes Gerenciales</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">

            {{-- 1. Ventas e Ingresos --}}
            @can('ver reportes ventas')
            <a href="{{ route('reportes.ventas') }}"
               class="group bg-white dark:bg-slate-900 rounded-xl p-5 border border-emerald-300 dark:border-emerald-800/80 shadow-xs hover:shadow-md hover:border-emerald-500 dark:hover:border-emerald-500 transition-all block">
                <div class="flex items-start justify-between">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </div>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">Disponible</span>
                </div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white mt-3 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition">Ventas e Ingresos</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Desglose por fecha, ticket promedio, métodos de pago, cajero y exportación CSV.</p>
                <div class="mt-4 flex items-center text-xs font-bold text-emerald-600 dark:text-emerald-400">
                    <span>Acceder al reporte</span><span class="ml-1 group-hover:translate-x-1 transition-transform">&rarr;</span>
                </div>
            </a>
            @endcan

            {{-- 2. Inventario & Caducidad --}}
            @can('ver reportes inventario')
            <a href="{{ route('reportes.inventario') }}"
               class="group bg-white dark:bg-slate-900 rounded-xl p-5 border border-indigo-300 dark:border-indigo-800/80 shadow-xs hover:shadow-md hover:border-indigo-500 dark:hover:border-indigo-500 transition-all block">
                <div class="flex items-start justify-between">
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-300">Disponible</span>
                </div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white mt-3 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition">Inventario y Caducidad</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Semáforo PEPS a 30, 60 y 90 días, valorización total a costo y venta, y exportación CSV.</p>
                <div class="mt-4 flex items-center text-xs font-bold text-indigo-600 dark:text-indigo-400">
                    <span>Acceder al reporte</span><span class="ml-1 group-hover:translate-x-1 transition-transform">&rarr;</span>
                </div>
            </a>
            @endcan

            {{-- 3. Proveedores y Compras --}}
            @can('ver reportes compras')
            <a href="{{ route('reportes.compras') }}"
               class="group bg-white dark:bg-slate-900 rounded-xl p-5 border border-amber-300 dark:border-amber-800/80 shadow-xs hover:shadow-md hover:border-amber-500 dark:hover:border-amber-500 transition-all block">
                <div class="flex items-start justify-between">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300">Disponible</span>
                </div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white mt-3 group-hover:text-amber-600 dark:group-hover:text-amber-400 transition">Proveedores y Compras</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Órdenes recibidas, proveedor líder, ticket promedio y exportación CSV por período.</p>
                <div class="mt-4 flex items-center text-xs font-bold text-amber-600 dark:text-amber-400">
                    <span>Acceder al reporte</span><span class="ml-1 group-hover:translate-x-1 transition-transform">&rarr;</span>
                </div>
            </a>
            @endcan

            {{-- 4. Top Medicamentos --}}
            @can('ver reportes ventas')
            <a href="{{ route('reportes.productos-mas-vendidos') }}"
               class="group bg-white dark:bg-slate-900 rounded-xl p-5 border border-teal-300 dark:border-teal-800/80 shadow-xs hover:shadow-md hover:border-teal-500 dark:hover:border-teal-500 transition-all block">
                <div class="flex items-start justify-between">
                    <div class="w-10 h-10 rounded-xl bg-teal-50 dark:bg-teal-950/60 text-teal-600 dark:text-teal-400 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                    </div>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-teal-100 text-teal-800 dark:bg-teal-950 dark:text-teal-300">Disponible</span>
                </div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white mt-3 group-hover:text-teal-600 dark:group-hover:text-teal-400 transition">Top Medicamentos</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Ranking de los 20 medicamentos más vendidos por unidades despachadas e ingresos.</p>
                <div class="mt-4 flex items-center text-xs font-bold text-teal-600 dark:text-teal-400">
                    <span>Acceder al reporte</span><span class="ml-1 group-hover:translate-x-1 transition-transform">&rarr;</span>
                </div>
            </a>
            @endcan

            {{-- 5. Stock Mínimo --}}
            @can('ver reportes inventario')
            <a href="{{ route('reportes.productos-bajo-stock') }}"
               class="group bg-white dark:bg-slate-900 rounded-xl p-5 border border-rose-300 dark:border-rose-800/80 shadow-xs hover:shadow-md hover:border-rose-500 dark:hover:border-rose-500 transition-all block">
                <div class="flex items-start justify-between">
                    <div class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/></svg>
                    </div>
                    @if($productosBajoStockCount > 0)
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>{{ $productosBajoStockCount }} alertas
                    </span>
                    @else
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300">Disponible</span>
                    @endif
                </div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white mt-3 group-hover:text-rose-600 dark:group-hover:text-rose-400 transition">Alertas de Stock Mínimo</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Productos con stock actual por debajo del nivel mínimo configurado. Reposición urgente.</p>
                <div class="mt-4 flex items-center text-xs font-bold text-rose-600 dark:text-rose-400">
                    <span>Acceder al reporte</span><span class="ml-1 group-hover:translate-x-1 transition-transform">&rarr;</span>
                </div>
            </a>
            @endcan

            {{-- 6. Clientes y Frecuencia --}}
            @can('ver reportes ventas')
            <a href="{{ route('reportes.clientes') }}"
               class="group bg-white dark:bg-slate-900 rounded-xl p-5 border border-violet-300 dark:border-violet-800/80 shadow-xs hover:shadow-md hover:border-violet-500 dark:hover:border-violet-500 transition-all block">
                <div class="flex items-start justify-between">
                    <div class="w-10 h-10 rounded-xl bg-violet-50 dark:bg-violet-950/60 text-violet-600 dark:text-violet-400 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-violet-100 text-violet-800 dark:bg-violet-950 dark:text-violet-300">Disponible</span>
                </div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white mt-3 group-hover:text-violet-600 dark:group-hover:text-violet-400 transition">Clientes y Frecuencia</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Ranking de pacientes por volumen de compra, frecuencia de visita y ticket promedio.</p>
                <div class="mt-4 flex items-center text-xs font-bold text-violet-600 dark:text-violet-400">
                    <span>Acceder al reporte</span><span class="ml-1 group-hover:translate-x-1 transition-transform">&rarr;</span>
                </div>
            </a>
            @endcan

            {{-- 7. Recetas Médicas --}}
            @can('ver reportes compras')
            <a href="{{ route('reportes.recetas') }}"
               class="group bg-white dark:bg-slate-900 rounded-xl p-5 border border-sky-300 dark:border-sky-800/80 shadow-xs hover:shadow-md hover:border-sky-500 dark:hover:border-sky-500 transition-all block">
                <div class="flex items-start justify-between">
                    <div class="w-10 h-10 rounded-xl bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-100 text-sky-800 dark:bg-sky-950 dark:text-sky-300">Disponible</span>
                </div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white mt-3 group-hover:text-sky-600 dark:group-hover:text-sky-400 transition">Recetas Médicas</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Recetas procesadas, pendientes, vencidas y rechazadas. Control de dispensación controlada.</p>
                <div class="mt-4 flex items-center text-xs font-bold text-sky-600 dark:text-sky-400">
                    <span>Acceder al reporte</span><span class="ml-1 group-hover:translate-x-1 transition-transform">&rarr;</span>
                </div>
            </a>
            @endcan

        </div>
    </div>

    {{-- Widgets de resumen: Top 5 y Métodos de Pago --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        {{-- Top 5 Productos del Mes --}}
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-300 dark:border-slate-800 shadow-xs p-5">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3 mb-4">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Top 5 Medicamentos del Mes</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ now()->translatedFormat('F Y') }}</p>
                </div>
                @can('ver reportes ventas')
                <a href="{{ route('reportes.productos-mas-vendidos') }}" class="text-xs font-bold text-emerald-600 dark:text-emerald-400 hover:underline">Ver ranking &rarr;</a>
                @endcan
            </div>
            <div class="space-y-3">
                @forelse($topProductosMes as $idx => $prod)
                <div>
                    <div class="flex items-center justify-between text-xs mb-1">
                        <span class="font-semibold text-slate-800 dark:text-slate-200 truncate max-w-[200px]">
                            <span class="text-slate-400 mr-1">#{{ $idx + 1 }}</span>{{ $prod->nombre }}
                        </span>
                        <div class="text-right shrink-0">
                            <span class="font-bold text-slate-900 dark:text-white">${{ number_format($prod->total_monto, 2) }}</span>
                            <span class="text-slate-400 ml-1">({{ number_format($prod->total_unidades) }} un.)</span>
                        </div>
                    </div>
                    @php $maxU = $topProductosMes->first()->total_unidades ?? 1; $pct = $maxU > 0 ? min(100, round(($prod->total_unidades / $maxU) * 100)) : 0; @endphp
                    <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-1.5">
                        <div class="bg-emerald-500 h-1.5 rounded-full transition-all" style="width: {{ $pct }}%"></div>
                    </div>
                </div>
                @empty
                <div class="text-center py-6 text-xs text-slate-400">Sin ventas registradas este mes.</div>
                @endforelse
            </div>
        </div>

        {{-- Distribución por Método de Pago --}}
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-300 dark:border-slate-800 shadow-xs p-5">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3 mb-4">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Métodos de Pago del Mes</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Total: ${{ number_format($ventasMes, 2) }}</p>
                </div>
                @can('ver reportes ventas')
                <a href="{{ route('reportes.ventas') }}" class="text-xs font-bold text-emerald-600 dark:text-emerald-400 hover:underline">Detalle &rarr;</a>
                @endcan
            </div>
            <div class="space-y-3">
                @forelse($metodosMes as $m)
                @php
                    $pct = $ventasMes > 0 ? round(($m->total / $ventasMes) * 100, 1) : 0;
                    $color = match($m->metodo_pago) {
                        'efectivo'      => 'bg-emerald-500',
                        'tarjeta'       => 'bg-blue-500',
                        'transferencia' => 'bg-violet-500',
                        default         => 'bg-slate-400',
                    };
                    $dot = match($m->metodo_pago) {
                        'efectivo'      => 'bg-emerald-500',
                        'tarjeta'       => 'bg-blue-500',
                        'transferencia' => 'bg-violet-500',
                        default         => 'bg-slate-400',
                    };
                @endphp
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700">
                    <div class="flex items-center justify-between text-xs mb-1.5">
                        <div class="flex items-center space-x-2">
                            <span class="w-2 h-2 rounded-full {{ $dot }}"></span>
                            <span class="font-bold text-slate-800 dark:text-slate-200">{{ ucfirst($m->metodo_pago) }}</span>
                            <span class="text-slate-400">({{ $m->cantidad }})</span>
                        </div>
                        <div>
                            <span class="font-bold text-slate-900 dark:text-white">${{ number_format($m->total, 2) }}</span>
                            <span class="text-slate-400 font-mono ml-1">{{ $pct }}%</span>
                        </div>
                    </div>
                    <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-1.5">
                        <div class="{{ $color }} h-1.5 rounded-full" style="width: {{ $pct }}%"></div>
                    </div>
                </div>
                @empty
                <div class="text-center py-6 text-xs text-slate-400">Sin transacciones este mes.</div>
                @endforelse
            </div>
        </div>
    </div>

</div>
@endsection
