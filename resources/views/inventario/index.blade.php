@extends('layouts.app')
@section('title', 'Inventario - FarmaBien')
@section('content')
@php
    $moneda = config('app.moneda', 'C$');
    $vencidosCount = count($lotesVencidos ?? []);
    $porVencerCount = count($lotesPorVencer ?? []);
    $bajoStockCount = count($productosBajoStock ?? []);
    $alertasTotal = $vencidosCount + $porVencerCount + $bajoStockCount;
@endphp

<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-300/80 dark:border-slate-800 pb-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">Control de Inventario</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Valorización, lotes, alertas de vencimiento y stock.</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('inventario.alertas') }}" class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-xl border border-rose-300 dark:border-rose-700 text-rose-700 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 text-xs font-bold transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                <span>Alertas @if($alertasTotal > 0)<span class="ml-1 px-1.5 py-0.5 rounded-full bg-rose-500 text-white text-[10px] font-extrabold">{{ $alertasTotal }}</span>@endif</span>
            </a>
            <a href="{{ route('inventario.ajustar') }}" class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Ajustar Stock</span>
            </a>
            <a href="{{ route('inventario.lotes') }}" class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 text-slate-700 dark:text-slate-300 text-xs font-bold transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                <span>Ver Lotes</span>
            </a>
            <a href="{{ route('inventario.movimientos') }}" class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 text-slate-700 dark:text-slate-300 text-xs font-bold transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <span>Kardex</span>
            </a>
        </div>
    </div>

    {{-- Stat Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Valor Total</span>
                <div class="w-8 h-8 rounded-xl bg-emerald-100 dark:bg-emerald-950/50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="text-2xl font-extrabold text-slate-900 dark:text-white font-mono">{{ $moneda }} {{ number_format($valorizacion['valor_total'] ?? 0, 2) }}</div>
            <div class="text-xs text-slate-400 mt-1">{{ number_format($valorizacion['total_unidades'] ?? 0) }} unidades en stock</div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Lotes Activos</span>
                <div class="w-8 h-8 rounded-xl bg-indigo-100 dark:bg-indigo-950/50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
            </div>
            <div class="text-2xl font-extrabold text-slate-900 dark:text-white">{{ $valorizacion['total_lotes_activos'] ?? 0 }}</div>
            <div class="text-xs text-slate-400 mt-1">{{ $valorizacion['total_productos'] ?? 0 }} medicamentos distintos</div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm {{ $bajoStockCount > 0 ? 'border-amber-300 dark:border-amber-700' : '' }}">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Bajo Stock</span>
                <div class="w-8 h-8 rounded-xl bg-amber-100 dark:bg-amber-950/50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/></svg>
                </div>
            </div>
            <div class="text-2xl font-extrabold {{ $bajoStockCount > 0 ? 'text-amber-600' : 'text-slate-900 dark:text-white' }}">{{ $bajoStockCount }}</div>
            <div class="text-xs text-slate-400 mt-1">productos bajo su minimo</div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm {{ ($vencidosCount + $porVencerCount) > 0 ? 'border-rose-300 dark:border-rose-700' : '' }}">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Alertas Lotes</span>
                <div class="w-8 h-8 rounded-xl bg-rose-100 dark:bg-rose-950/50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
            </div>
            <div class="text-2xl font-extrabold {{ ($vencidosCount + $porVencerCount) > 0 ? 'text-rose-600' : 'text-slate-900 dark:text-white' }}">{{ $vencidosCount + $porVencerCount }}</div>
            <div class="text-xs text-slate-400 mt-1">{{ $vencidosCount }} vencidos · {{ $porVencerCount }} por vencer</div>
        </div>
    </div>

    {{-- Main Content Grid --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        {{-- Lotes Vencidos con Stock --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-rose-200 dark:border-rose-800/60 shadow-sm">
            <div class="flex items-center justify-between p-4 border-b border-rose-100 dark:border-rose-900/50">
                <div class="flex items-center space-x-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-rose-500 animate-pulse"></span>
                    <h3 class="text-sm font-bold text-rose-700 dark:text-rose-400">Lotes Vencidos con Stock</h3>
                </div>
                <span class="text-xs font-extrabold px-2 py-0.5 rounded-full bg-rose-100 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300">{{ $vencidosCount }}</span>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-800 max-h-72 overflow-y-auto">
                @forelse($lotesVencidos as $lote)
                <div class="px-4 py-3 flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <p class="text-xs font-semibold text-slate-800 dark:text-slate-200 truncate">{{ $lote->producto->nombre ?? 'N/A' }}</p>
                        <p class="text-[10px] text-slate-400 font-mono mt-0.5">Lote: {{ $lote->numero_lote }}</p>
                        <p class="text-[10px] text-rose-600 dark:text-rose-400 font-semibold">Venció: {{ $lote->fecha_vencimiento->format('d/m/Y') }}</p>
                    </div>
                    <div class="text-right shrink-0">
                        <span class="text-xs font-bold text-rose-700 dark:text-rose-300">{{ $lote->stock_actual }} un.</span>
                    </div>
                </div>
                @empty
                <div class="px-4 py-8 text-center text-sm text-slate-400">
                    <svg class="w-8 h-8 mx-auto mb-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Sin lotes vencidos con stock
                </div>
                @endforelse
            </div>
            @if($vencidosCount > 0)
            <div class="p-3 border-t border-rose-100 dark:border-rose-900/50">
                <a href="{{ route('inventario.alertas') }}" class="block text-center text-xs font-bold text-rose-600 hover:text-rose-700 transition">Ver todas las alertas →</a>
            </div>
            @endif
        </div>

        {{-- Proximos a Vencer --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-amber-200 dark:border-amber-800/60 shadow-sm">
            <div class="flex items-center justify-between p-4 border-b border-amber-100 dark:border-amber-900/50">
                <div class="flex items-center space-x-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                    <h3 class="text-sm font-bold text-amber-700 dark:text-amber-400">Proximos a Vencer ≤60d</h3>
                </div>
                <span class="text-xs font-extrabold px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300">{{ $porVencerCount }}</span>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-800 max-h-72 overflow-y-auto">
                @forelse($lotesPorVencer as $lote)
                @php
                    $dias = $lote->dias_para_vencer ?? 0;
                    $color = $dias <= 15 ? 'text-rose-600' : ($dias <= 30 ? 'text-amber-600' : 'text-emerald-600');
                @endphp
                <div class="px-4 py-3 flex items-start justify-between gap-2">
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
                <div class="px-4 py-8 text-center text-sm text-slate-400">
                    <svg class="w-8 h-8 mx-auto mb-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Sin lotes proximos a vencer
                </div>
                @endforelse
            </div>
        </div>

        {{-- Bajo Stock --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="flex items-center justify-between p-4 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center space-x-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200">Medicamentos Bajo Stock</h3>
                </div>
                <span class="text-xs font-extrabold px-2 py-0.5 rounded-full bg-indigo-100 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300">{{ $bajoStockCount }}</span>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-800 max-h-72 overflow-y-auto">
                @forelse($productosBajoStock as $producto)
                <div class="px-4 py-3 flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <p class="text-xs font-semibold text-slate-800 dark:text-slate-200 truncate">{{ $producto->nombre }}</p>
                        <p class="text-[10px] text-slate-400 mt-0.5">{{ $producto->categoria->nombre ?? '' }} · Min: {{ $producto->stock_minimo }}</p>
                    </div>
                    <div class="text-right shrink-0">
                        <span class="text-sm font-extrabold text-amber-600 dark:text-amber-400">{{ $producto->stock_disponible }}</span>
                        <p class="text-[10px] text-slate-400">disponible</p>
                    </div>
                </div>
                @empty
                <div class="px-4 py-8 text-center text-sm text-slate-400">
                    <svg class="w-8 h-8 mx-auto mb-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Todos los productos tienen stock suficiente
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Valorización tabla --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
        <div class="flex items-center justify-between p-5 border-b border-slate-100 dark:border-slate-800">
            <div><h3 class="text-sm font-bold text-slate-900 dark:text-white">Valorización del Inventario (PEPS)</h3><p class="text-xs text-slate-400 mt-0.5">Costo de adquisición por lote activo con stock disponible.</p></div>
            <div class="text-right">
                <p class="text-xs text-slate-400">Valor total</p>
                <p class="text-lg font-extrabold text-emerald-600 font-mono">{{ $moneda }} {{ number_format($valorizacion['valor_total'] ?? 0, 2) }}</p>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/50">
                    <tr>
                        <th class="text-left px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-400">Medicamento</th>
                        <th class="text-left px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-400">Categoria</th>
                        <th class="text-left px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-400">Lote</th>
                        <th class="text-left px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-400">Vencimiento</th>
                        <th class="text-right px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-400">Stock</th>
                        <th class="text-right px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-400">P. Compra</th>
                        <th class="text-right px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-400">Valor Total</th>
                        <th class="px-4 py-2.5"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse(collect($valorizacion['detalles'] ?? [])->take(15) as $item)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition">
                        <td class="px-4 py-2.5 font-semibold text-slate-800 dark:text-slate-200">{{ $item['producto'] }}</td>
                        <td class="px-4 py-2.5 text-slate-500 dark:text-slate-400">{{ $item['categoria'] }}</td>
                        <td class="px-4 py-2.5 font-mono text-slate-600 dark:text-slate-400">{{ $item['lote'] }}</td>
                        <td class="px-4 py-2.5 text-slate-500">{{ $item['fecha_vencimiento'] }}</td>
                        <td class="px-4 py-2.5 text-right font-mono text-slate-800 dark:text-slate-200">{{ number_format($item['stock']) }}</td>
                        <td class="px-4 py-2.5 text-right font-mono text-slate-600 dark:text-slate-400">{{ $moneda }} {{ number_format($item['precio_compra'], 2) }}</td>
                        <td class="px-4 py-2.5 text-right font-mono font-bold text-emerald-700 dark:text-emerald-400">{{ $moneda }} {{ number_format($item['valor_total'], 2) }}</td>
                        <td class="px-4 py-2.5">
                            <a href="{{ route('inventario.kardex-producto', $item['producto_id']) }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 font-semibold text-[10px]">Kardex</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="px-4 py-8 text-center text-slate-400">Sin datos de valorización</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(count($valorizacion['detalles'] ?? []) > 15)
        <div class="px-5 py-3 border-t border-slate-100 dark:border-slate-800 text-xs text-slate-400 text-center">
            Mostrando 15 de {{ count($valorizacion['detalles']) }} lotes. <a href="{{ route('inventario.lotes') }}" class="text-indigo-600 font-semibold">Ver todos →</a>
        </div>
        @endif
    </div>
</div>
@endsection
