@extends('layouts.app')
@section('title', 'Control de Inventario - FarmaBien')
@section('content')
@php
    $moneda = config('app.moneda', 'C$');
    $vencidosCount = count($lotesVencidos ?? []);
    $porVencerCount = count($lotesPorVencer ?? []);
    $bajoStockCount = count($productosBajoStock ?? []);
    $alertasTotal = $vencidosCount + $porVencerCount + $bajoStockCount;
@endphp

<div class="space-y-5">

    <!-- Breadcrumb -->
    <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-800 dark:text-slate-200 font-semibold">Control de Inventario</span>
    </nav>

    <!-- Header & Action Buttons -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <span>Control de Inventario</span>
                <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                    {{ $valorizacion['total_productos'] ?? 0 }} productos
                </span>
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Valorización, lotes, alertas de vencimiento y stock.</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap shrink-0">
            <!-- 1. Modo Full (Primer botón del grupo en índice) -->
            <button type="button" 
                    @click="$dispatch('toggle-pos-fullscreen')"
                    title="Modo Pantalla Completa / Ocultar Barras"
                    class="inline-flex items-center space-x-1.5 px-3.5 py-2 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold shadow-2xs transition cursor-pointer">
                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                <span>Modo Full</span>
            </button>

            <!-- 2. Alertas (Pastel Rojo) -->
            <a href="{{ route('inventario.alertas') }}" 
               class="inline-flex items-center space-x-1.5 px-3.5 py-2 rounded-xl bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/40 dark:hover:bg-rose-900/60 border border-rose-200 dark:border-rose-800 text-rose-900 dark:text-rose-300 text-xs font-bold transition shadow-2xs">
                <svg class="w-4 h-4 text-rose-700 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                <span>Alertas @if($alertasTotal > 0)<span class="ml-1 px-1.5 py-0.5 rounded-full bg-rose-600 text-white text-[10px] font-bold">{{ $alertasTotal }}</span>@endif</span>
            </a>

            <!-- 3. Ver Lotes (Pastel Índigo) -->
            <a href="{{ route('inventario.lotes') }}" 
               class="inline-flex items-center space-x-1.5 px-3.5 py-2 rounded-xl bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/40 dark:hover:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 text-xs font-bold border border-indigo-200 dark:border-indigo-800 shadow-2xs transition">
                <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                <span>Ver Lotes</span>
            </a>

            <!-- 4. Kardex (Pastel Indigo) -->
            <a href="{{ route('inventario.movimientos') }}" 
               class="inline-flex items-center space-x-1.5 px-3.5 py-2 rounded-xl bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/40 dark:hover:bg-indigo-900/60 text-indigo-950 dark:text-indigo-300 text-xs font-bold border border-indigo-200 dark:border-indigo-800 shadow-2xs transition">
                <svg class="w-4 h-4 text-indigo-700 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <span>Kardex</span>
            </a>

            @can('ajustar inventario')
            <!-- 5. Ajustar Stock (Verde Sólido Institucional) -->
            <a href="{{ route('inventario.ajustar') }}" 
               class="inline-flex items-center space-x-1.5 px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-semibold shadow-2xs transition shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Ajustar Stock</span>
            </a>
            @endcan
        </div>
    </div>

    <!-- 4 Quick Stats Cards Row -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Valorización Total</p>
                <p class="text-xl font-bold text-slate-900 dark:text-white mt-0.5 font-mono">{{ $moneda }} {{ number_format($valorizacion['valor_total'] ?? 0, 2) }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Lotes Activos</p>
                <p class="text-xl font-bold text-slate-900 dark:text-white mt-0.5">{{ number_format($valorizacion['total_lotes_activos'] ?? 0) }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Bajo Stock Mínimo</p>
                <p class="text-xl font-bold {{ $bajoStockCount > 0 ? 'text-amber-900 dark:text-amber-400' : 'text-slate-900 dark:text-white' }} mt-0.5">{{ number_format($bajoStockCount) }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/></svg>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Alertas de Vencimiento</p>
                <p class="text-xl font-bold {{ ($vencidosCount + $porVencerCount) > 0 ? 'text-red-900 dark:text-rose-400' : 'text-slate-900 dark:text-white' }} mt-0.5">{{ number_format($vencidosCount + $porVencerCount) }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-5">

        <!-- Lotes Vencidos con Stock -->
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
            <div class="flex items-center justify-between p-4 border-b border-rose-100 dark:border-rose-900/50 bg-rose-50/50 dark:bg-rose-950/20">
                <div class="flex items-center space-x-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-rose-500 animate-pulse"></span>
                    <h3 class="text-sm font-bold text-red-900 dark:text-rose-400">Lotes Vencidos con Stock</h3>
                </div>
                <span class="text-xs font-extrabold px-2 py-0.5 rounded-full bg-rose-100 dark:bg-rose-950/60 text-red-900 dark:text-rose-300 border border-rose-200 dark:border-rose-800">{{ $vencidosCount }}</span>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-800/60 max-h-72 overflow-y-auto">
                @forelse($lotesVencidos as $lote)
                <div class="px-4 py-3 flex items-start justify-between gap-2 hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition">
                    <div class="min-w-0">
                        <p class="text-xs font-semibold text-slate-800 dark:text-slate-200 truncate">{{ $lote->producto->nombre ?? 'N/A' }}</p>
                        <p class="text-[10px] text-slate-400 font-mono mt-0.5">Lote: {{ $lote->numero_lote }}</p>
                        <p class="text-[10px] text-red-900 dark:text-rose-400 font-bold">Venció: {{ $lote->fecha_vencimiento->format('d/m/Y') }}</p>
                    </div>
                    <div class="text-right shrink-0">
                        <span class="text-xs font-bold text-red-900 dark:text-rose-300">{{ $lote->stock_actual }} un.</span>
                    </div>
                </div>
                @empty
                <div class="px-4 py-8 text-center text-xs text-slate-400">
                    <svg class="w-8 h-8 mx-auto mb-2 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Sin lotes vencidos con stock
                </div>
                @endforelse
            </div>
            @if($vencidosCount > 0)
            <div class="p-3 border-t border-rose-100 dark:border-rose-900/50 bg-slate-50/50 dark:bg-slate-900">
                <a href="{{ route('inventario.alertas') }}" class="block text-center text-xs font-bold text-red-900 hover:text-red-950 dark:text-rose-400 transition">Ver todas las alertas &rarr;</a>
            </div>
            @endif
        </div>

        <!-- Proximos a Vencer -->
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
            <div class="flex items-center justify-between p-4 border-b border-amber-100 dark:border-amber-900/50 bg-amber-50/50 dark:bg-amber-950/20">
                <div class="flex items-center space-x-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                    <h3 class="text-sm font-bold text-amber-900 dark:text-amber-400">Próximos a Vencer &le;60d</h3>
                </div>
                <span class="text-xs font-extrabold px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-950/60 text-amber-900 dark:text-amber-300 border border-amber-200 dark:border-amber-800">{{ $porVencerCount }}</span>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-800/60 max-h-72 overflow-y-auto">
                @forelse($lotesPorVencer as $lote)
                @php
                    $dias = $lote->dias_restantes ?? 0;
                    $color = $dias <= 15 ? 'text-red-900 dark:text-rose-400' : ($dias <= 30 ? 'text-amber-900 dark:text-amber-400' : 'text-emerald-900 dark:text-emerald-400');
                @endphp
                <div class="px-4 py-3 flex items-start justify-between gap-2 hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition">
                    <div class="min-w-0">
                        <p class="text-xs font-semibold text-slate-800 dark:text-slate-200 truncate">{{ $lote->producto->nombre ?? 'N/A' }}</p>
                        <p class="text-[10px] text-slate-400 font-mono mt-0.5">Lote: {{ $lote->numero_lote }} · {{ $lote->stock_actual }} un.</p>
                    </div>
                    <div class="text-right shrink-0">
                        <span class="text-xs font-bold {{ $color }}">{{ $dias }}d</span>
                        <p class="text-[10px] text-slate-400">{{ $lote->fecha_vencimiento->format('d/m/Y') }}</p>
                    </div>
                </div>
                @empty
                <div class="px-4 py-8 text-center text-xs text-slate-400">
                    <svg class="w-8 h-8 mx-auto mb-2 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Sin lotes próximos a vencer
                </div>
                @endforelse
            </div>
            @if($porVencerCount > 0)
            <div class="p-3 border-t border-amber-100 dark:border-amber-900/50 bg-slate-50/50 dark:bg-slate-900">
                <a href="{{ route('inventario.alertas') }}" class="block text-center text-xs font-bold text-amber-900 hover:text-amber-950 dark:text-amber-400 transition">Ver alertas de vencimiento &rarr;</a>
            </div>
            @endif
        </div>

        <!-- Bajo Stock -->
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
            <div class="flex items-center justify-between p-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/40">
                <div class="flex items-center space-x-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200">Medicamentos Bajo Stock</h3>
                </div>
                <span class="text-xs font-extrabold px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-900 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">{{ $bajoStockCount }}</span>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-800/60 max-h-72 overflow-y-auto">
                @forelse($productosBajoStock as $producto)
                <div class="px-4 py-3 flex items-start justify-between gap-2 hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition">
                    <div class="min-w-0">
                        <p class="text-xs font-semibold text-slate-800 dark:text-slate-200 truncate">{{ $producto->nombre }}</p>
                        <p class="text-[10px] text-slate-400 mt-0.5">{{ $producto->categoria->nombre ?? '' }} · Min: {{ $producto->stock_minimo }}</p>
                    </div>
                    <div class="text-right shrink-0">
                        <span class="text-sm font-extrabold text-amber-900 dark:text-amber-400">{{ $producto->stock_disponible }}</span>
                        <p class="text-[10px] text-slate-400">disponible</p>
                    </div>
                </div>
                @empty
                <div class="px-4 py-8 text-center text-xs text-slate-400">
                    <svg class="w-8 h-8 mx-auto mb-2 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Todos los productos tienen stock suficiente
                </div>
                @endforelse
            </div>
            @if($bajoStockCount > 0)
            <div class="p-3 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900">
                <a href="{{ route('inventario.alertas') }}" class="block text-center text-xs font-bold text-slate-700 hover:text-slate-950 dark:text-slate-300 transition">Ver productos críticos &rarr;</a>
            </div>
            @endif
        </div>
    </div>

    <!-- Valorización tabla -->
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 sm:p-5 border-b border-slate-100 dark:border-slate-800 gap-2">
            <div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Valorización del Inventario (PEPS)</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Costo de adquisición por lote activo con stock disponible.</p>
            </div>
            <div class="sm:text-right">
                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Valor total inventario</p>
                <p class="text-lg font-bold text-emerald-600 dark:text-emerald-400 font-mono">{{ $moneda }} {{ number_format($valorizacion['valor_total'] ?? 0, 2) }}</p>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                        <th class="px-5 py-3.5">Medicamento</th>
                        <th class="px-5 py-3.5">Categoría</th>
                        <th class="px-5 py-3.5">Lote</th>
                        <th class="px-5 py-3.5">Vencimiento</th>
                        <th class="px-5 py-3.5 text-right">Stock</th>
                        <th class="px-5 py-3.5 text-right">P. Compra</th>
                        <th class="px-5 py-3.5 text-right">Valor Total</th>
                        <th class="px-5 py-3.5 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="table-cv divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-700 dark:text-slate-300">
                    @forelse(collect($valorizacion['detalles'] ?? [])->take(15) as $item)
                    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition">
                        <td class="px-5 py-3.5 font-semibold text-slate-900 dark:text-white">{{ $item['producto'] }}</td>
                        <td class="px-5 py-3.5 text-slate-500 dark:text-slate-400">{{ $item['categoria'] }}</td>
                        <td class="px-5 py-3.5 font-mono text-slate-700 dark:text-slate-300">{{ $item['lote'] }}</td>
                        <td class="px-5 py-3.5 text-slate-600 dark:text-slate-400">{{ $item['fecha_vencimiento'] }}</td>
                        <td class="px-5 py-3.5 text-right font-mono font-semibold text-slate-900 dark:text-white">{{ number_format($item['stock']) }}</td>
                        <td class="px-5 py-3.5 text-right font-mono text-slate-600 dark:text-slate-400">{{ $moneda }} {{ number_format($item['precio_compra'], 2) }}</td>
                        <td class="px-5 py-3.5 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400">{{ $moneda }} {{ number_format($item['valor_total'], 2) }}</td>
                        <td class="px-5 py-3.5 text-center whitespace-nowrap">
                            <a href="{{ route('inventario.kardex-producto', $item['producto_id']) }}" 
                               title="Ver Kardex del Producto"
                               class="w-8 h-8 rounded-lg bg-sky-50 dark:bg-sky-950/60 text-sky-700 dark:text-sky-300 hover:bg-sky-100 dark:hover:bg-sky-900/60 border border-sky-200 dark:border-sky-800/60 inline-flex items-center justify-center transition shadow-2xs">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="px-5 py-12 text-center text-slate-400 text-xs">Sin datos de valorización registrados</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(count($valorizacion['detalles'] ?? []) > 15)
        <div class="px-5 py-3.5 border-t border-slate-100 dark:border-slate-800 text-xs text-slate-500 dark:text-slate-400 text-center bg-slate-50/50 dark:bg-slate-900">
            Mostrando 15 de {{ count($valorizacion['detalles']) }} lotes activos. 
            <a href="{{ route('inventario.lotes') }}" class="text-emerald-600 dark:text-emerald-400 font-bold hover:underline ml-1">Ver todos los lotes &rarr;</a>
        </div>
        @endif
    </div>
</div>
@endsection
