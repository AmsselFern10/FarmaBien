@extends('layouts.app')
@section('title', 'Alertas de Inventario - FarmaBien')
@section('content')
@php
    $vencidosCount = count($lotesVencidos ?? []);
    $porVencerCount = count($lotesPorVencer ?? []);
    $bajoStockCount = count($productosBajoStock ?? []);
    $totalAlertas = $vencidosCount + $porVencerCount + $bajoStockCount;
@endphp

<div class="space-y-5">

    <!-- Breadcrumb -->
    <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('inventario.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inventario</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-800 dark:text-slate-200 font-semibold">Alertas de Inventario</span>
    </nav>

    <!-- Header & Action Buttons -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <span>Centro de Alertas de Inventario</span>
                <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-rose-50 dark:bg-rose-950/60 text-rose-900 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                    {{ $totalAlertas }} alertas
                </span>
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Lotes vencidos, próximos a caducar y productos con bajo stock crítico.</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap shrink-0">
            <a href="{{ route('inventario.index') }}" 
               class="inline-flex items-center space-x-1.5 px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold rounded-xl shadow-2xs transition shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Inventario</span>
            </a>
            <button type="button" 
                    @click="$dispatch('toggle-pos-fullscreen')"
                    title="Modo Pantalla Completa / Ocultar Barras"
                    class="inline-flex items-center space-x-1.5 px-3.5 py-2 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold shadow-2xs transition cursor-pointer">
                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                <span>Modo Full</span>
            </button>
        </div>
    </div>

    <!-- 3 Summary KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-rose-950 dark:text-rose-400">Lotes Vencidos</p>
                <p class="text-2xl font-bold text-red-950 dark:text-rose-400 font-mono mt-0.5">{{ $vencidosCount }}</p>
                <p class="text-[11px] text-slate-400 mt-0.5">Con stock activo disponible</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-950/60 text-red-950 dark:text-rose-400 border border-rose-200 dark:border-rose-900/60 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-amber-950 dark:text-amber-400">Próximos a Vencer</p>
                <p class="text-2xl font-bold text-amber-950 dark:text-amber-400 font-mono mt-0.5">{{ $porVencerCount }}</p>
                <p class="text-[11px] text-slate-400 mt-0.5">Caducan en &le; 60 días</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-950 dark:text-amber-400 border border-amber-200 dark:border-amber-900/60 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-700 dark:text-slate-400">Bajo Stock Mínimo</p>
                <p class="text-2xl font-bold text-slate-900 dark:text-white font-mono mt-0.5">{{ $bajoStockCount }}</p>
                <p class="text-[11px] text-slate-400 mt-0.5">Requieren orden de compra</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-950 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-900/60 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/></svg>
            </div>
        </div>
    </div>

    <!-- Lotes Vencidos Table Card -->
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-rose-50/40 dark:bg-rose-950/20 gap-3">
            <div class="flex items-center space-x-2">
                <span class="w-2.5 h-2.5 rounded-full bg-rose-500 animate-pulse"></span>
                <h3 class="text-sm font-bold text-rose-950 dark:text-rose-400">Lotes Vencidos con Stock Disponible</h3>
            </div>
            <div class="flex items-center space-x-2">
                @can('ajustar inventario')
                @if($vencidosCount > 0)
                <form method="POST" action="{{ route('inventario.baja-vencidos') }}"
                      data-confirm
                      data-confirm-title="¿Dar de baja todos los lotes vencidos?"
                      data-confirm-body="Se registrará la merma de {{ $vencidosCount }} lote(s) vencido(s) en el Kardex. Esta operación afecta el inventario y no puede deshacerse."
                      data-confirm-type="danger"
                      data-confirm-ok="Sí, dar de baja masiva">
                    @csrf
                    <button type="submit" class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold transition shadow-xs cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        <span>Dar de Baja Masiva</span>
                    </button>
                </form>
                @endif
                @endcan
                <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-rose-50 dark:bg-rose-950/60 text-rose-950 dark:text-rose-300 border border-rose-200 dark:border-rose-800">{{ $vencidosCount }}</span>
            </div>
        </div>
        @if($vencidosCount > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                        <th class="px-5 py-3.5">Medicamento</th>
                        <th class="px-5 py-3.5">N° Lote</th>
                        <th class="px-5 py-3.5">Proveedor</th>
                        <th class="px-5 py-3.5">Venció</th>
                        <th class="px-5 py-3.5 text-right">Stock</th>
                        <th class="px-5 py-3.5 text-center">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-700 dark:text-slate-300">
                    @foreach($lotesVencidos as $lote)
                    <tr class="hover:bg-rose-50/30 dark:hover:bg-rose-950/10 transition">
                        <td class="px-5 py-3.5 font-semibold text-slate-900 dark:text-white">
                            <span>{{ $lote->producto->nombre ?? 'N/A' }}</span>
                            @if($lote->producto && $lote->producto->esControlado())
                                <span class="ml-1.5 px-1.5 py-0.5 text-[10px] font-bold rounded bg-amber-100 dark:bg-amber-900/60 text-amber-900 dark:text-amber-200 border border-amber-300 dark:border-amber-700">Controlado</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 font-mono text-slate-600 dark:text-slate-400">{{ $lote->numero_lote }}</td>
                        <td class="px-5 py-3.5 text-slate-500 dark:text-slate-400">{{ $lote->proveedor->nombre ?? '—' }}</td>
                        <td class="px-5 py-3.5 text-red-950 dark:text-rose-400 font-semibold">{{ $lote->fecha_vencimiento->format('d/m/Y') }}</td>
                        <td class="px-5 py-3.5 text-right font-mono font-bold text-red-950 dark:text-rose-400">{{ $lote->stock_actual }}</td>
                        <td class="px-5 py-3.5 text-center whitespace-nowrap">
                            @can('ajustar inventario')
                            <a href="{{ route('inventario.ajustar') }}?lote={{ $lote->id }}" 
                               class="inline-flex items-center space-x-1 px-2.5 py-1 rounded-lg bg-rose-50 dark:bg-rose-950/60 text-rose-950 dark:text-rose-300 hover:bg-rose-100 dark:hover:bg-rose-900/60 border border-rose-200 dark:border-rose-800 text-[11px] font-semibold shadow-2xs transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                <span>Dar de Baja</span>
                            </a>
                            @endcan
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="px-5 py-10 text-center text-xs text-slate-400">
            <svg class="w-8 h-8 mx-auto mb-2 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Sin lotes vencidos con stock disponible
        </div>
        @endif
    </div>

    <!-- Próximos a Vencer Table Card -->
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-amber-50/40 dark:bg-amber-950/20">
            <div class="flex items-center space-x-2">
                <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                <h3 class="text-sm font-bold text-amber-950 dark:text-amber-400">Lotes Próximos a Vencer (en &le; 60 días)</h3>
            </div>
            <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-amber-50 dark:bg-amber-950/60 text-amber-950 dark:text-amber-300 border border-amber-200 dark:border-amber-800">{{ $porVencerCount }}</span>
        </div>
        @if($porVencerCount > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                        <th class="px-5 py-3.5">Medicamento</th>
                        <th class="px-5 py-3.5">N° Lote</th>
                        <th class="px-5 py-3.5 text-right">Días Restantes</th>
                        <th class="px-5 py-3.5">Vencimiento</th>
                        <th class="px-5 py-3.5 text-right">Stock</th>
                        <th class="px-5 py-3.5 text-center">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-700 dark:text-slate-300">
                    @foreach($lotesPorVencer as $lote)
                    @php $dias = $lote->dias_restantes ?? 0; $diasColor = $dias <= 15 ? 'text-rose-900 dark:text-rose-400' : ($dias <= 30 ? 'text-amber-900 dark:text-amber-400' : 'text-slate-700 dark:text-slate-300'); @endphp
                    <tr class="hover:bg-amber-50/20 dark:hover:bg-amber-950/10 transition">
                        <td class="px-5 py-3.5 font-semibold text-slate-900 dark:text-white">
                            <span>{{ $lote->producto->nombre ?? 'N/A' }}</span>
                            @if($lote->producto && $lote->producto->esControlado())
                                <span class="ml-1.5 px-1.5 py-0.5 text-[10px] font-bold rounded bg-amber-100 dark:bg-amber-900/60 text-amber-900 dark:text-amber-200 border border-amber-300 dark:border-amber-700">Controlado</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 font-mono text-slate-600 dark:text-slate-400">{{ $lote->numero_lote }}</td>
                        <td class="px-5 py-3.5 text-right font-bold {{ $diasColor }}">{{ $dias }} días</td>
                        <td class="px-5 py-3.5 text-slate-700 dark:text-slate-300">{{ $lote->fecha_vencimiento->format('d/m/Y') }}</td>
                        <td class="px-5 py-3.5 text-right font-mono font-bold text-slate-900 dark:text-slate-200">{{ $lote->stock_actual }}</td>
                        <td class="px-5 py-3.5 text-center whitespace-nowrap">
                            <a href="{{ route('inventario.kardex-producto', $lote->producto_id) }}" 
                               title="Ver Kardex"
                               class="w-8 h-8 rounded-lg bg-sky-50 dark:bg-sky-950/60 text-sky-900 dark:text-sky-300 hover:bg-sky-100 dark:hover:bg-sky-900/60 border border-sky-200 dark:border-sky-800/60 inline-flex items-center justify-center transition shadow-2xs">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="px-5 py-10 text-center text-xs text-slate-400">Sin lotes próximos a vencer en los próximos 60 días.</div>
        @endif
    </div>

    <!-- Medicamentos Bajo Stock Card -->
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/40">
            <div class="flex items-center space-x-2">
                <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                <h3 class="text-sm font-bold text-slate-900 dark:text-slate-200">Medicamentos Bajo Stock Mínimo</h3>
            </div>
            <div class="flex items-center space-x-2">
                @can('registrar compras')
                @if($bajoStockCount > 0)
                <a href="{{ route('compras.sugerencias-reorden') }}" 
                    class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-xl bg-amber-50 dark:bg-amber-950/50 border border-amber-200 dark:border-amber-800 text-amber-950 dark:text-amber-300 text-xs font-semibold hover:bg-amber-100 dark:hover:bg-amber-900/60 transition shadow-2xs">
                    <svg class="w-3.5 h-3.5 text-amber-950 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <span>Reorden Inteligente</span>
                </a>
                @endif
                @endcan
                <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-indigo-50 dark:bg-indigo-950/60 text-indigo-950 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">{{ $bajoStockCount }}</span>
            </div>
        </div>
        @if($bajoStockCount > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                        <th class="px-5 py-3.5">Medicamento</th>
                        <th class="px-5 py-3.5">Categoría</th>
                        <th class="px-5 py-3.5 text-right">Stock Mínimo</th>
                        <th class="px-5 py-3.5 text-right">Disponible</th>
                        <th class="px-5 py-3.5 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-700 dark:text-slate-300">
                    @foreach($productosBajoStock as $producto)
                    <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition">
                        <td class="px-5 py-3.5 font-semibold text-slate-900 dark:text-white">
                            <span>{{ $producto->nombre }}</span>
                            @if($producto->esControlado())
                                <span class="ml-1.5 px-1.5 py-0.5 text-[10px] font-bold rounded bg-amber-100 dark:bg-amber-900/60 text-amber-900 dark:text-amber-200 border border-amber-300 dark:border-amber-700">Controlado</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-slate-500 dark:text-slate-400">{{ $producto->categoria->nombre ?? '—' }}</td>
                        <td class="px-5 py-3.5 text-right font-mono text-slate-500 dark:text-slate-400">{{ $producto->stock_minimo }}</td>
                        <td class="px-5 py-3.5 text-right font-mono font-bold text-amber-950 dark:text-amber-400">{{ $producto->stock_disponible }}</td>
                        <td class="px-5 py-3.5 text-center whitespace-nowrap">
                            <div class="inline-flex items-center justify-center gap-1.5">
                                <a href="{{ route('inventario.kardex-producto', $producto->id) }}" 
                                   title="Ver Kardex"
                                   class="w-8 h-8 rounded-lg bg-sky-50 dark:bg-sky-950/60 text-sky-900 dark:text-sky-300 hover:bg-sky-100 dark:hover:bg-sky-900/60 border border-sky-200 dark:border-sky-800/60 inline-flex items-center justify-center transition shadow-2xs">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                </a>
                                @can('registrar compras')
                                <a href="{{ route('compras.create') }}?producto_id={{ $producto->id }}" 
                                   title="Generar Compra"
                                   class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-900 dark:text-emerald-300 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 border border-emerald-200 dark:border-emerald-800/60 inline-flex items-center justify-center transition shadow-2xs">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                </a>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="px-5 py-10 text-center text-xs text-slate-400">Todos los medicamentos tienen stock suficiente.</div>
        @endif
    </div>
</div>
@endsection
