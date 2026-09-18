@extends('layouts.app')
@section('title', 'Alertas de Inventario - FarmaBien')
@section('content')
<div class="space-y-6">

    
    <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('inventario.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inventario</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-800 dark:text-slate-200 font-semibold">Alertas de Stock</span>
    </nav>

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">Alertas de Inventario</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">Lotes vencidos, proximos a vencer y productos bajo stock.</p>
        </div>
        <a href="{{ route('inventario.index') }}" class="inline-flex items-center space-x-2 px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-xl transition shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Volver al Dashboard</span>
        </a>
    </div>

    @php
        $vencidosCount = count($lotesVencidos ?? []);
        $porVencerCount = count($lotesPorVencer ?? []);
        $bajoStockCount = count($productosBajoStock ?? []);
    @endphp

    {{-- Resumen de alertas --}}
    <div class="grid grid-cols-3 gap-4">
        <div class="bg-rose-50 dark:bg-rose-950/30 rounded-2xl border border-rose-200 dark:border-rose-800 p-4 text-center">
            <div class="text-3xl font-extrabold text-rose-600 dark:text-rose-400">{{ $vencidosCount }}</div>
            <div class="text-xs font-semibold text-rose-700 dark:text-rose-300 mt-1">Lotes Vencidos con Stock</div>
        </div>
        <div class="bg-amber-50 dark:bg-amber-950/30 rounded-2xl border border-amber-200 dark:border-amber-800 p-4 text-center">
            <div class="text-3xl font-extrabold text-amber-600 dark:text-amber-400">{{ $porVencerCount }}</div>
            <div class="text-xs font-semibold text-amber-700 dark:text-amber-300 mt-1">Lotes Proximos a Vencer</div>
        </div>
        <div class="bg-emerald-50 dark:bg-emerald-950/30 rounded-2xl border border-emerald-200 dark:border-emerald-800 p-4 text-center">
            <div class="text-3xl font-extrabold text-emerald-600 dark:text-emerald-400">{{ $bajoStockCount }}</div>
            <div class="text-xs font-semibold text-emerald-700 dark:text-emerald-300 mt-1">Productos Bajo Stock Minimo</div>
        </div>
    </div>

    {{-- Lotes Vencidos --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-rose-200 dark:border-rose-800/60 shadow-sm">
        <div class="flex items-center justify-between px-5 py-4 border-b border-rose-100 dark:border-rose-900/50">
            <div class="flex items-center space-x-2">
                <span class="w-3 h-3 rounded-full bg-rose-500 animate-pulse"></span>
                <h3 class="text-sm font-bold text-rose-700 dark:text-rose-400">Lotes Vencidos con Stock Disponible</h3>
            </div>
            <span class="text-xs font-extrabold px-2.5 py-1 rounded-full bg-rose-100 dark:bg-rose-950/60 text-rose-700">{{ $vencidosCount }}</span>
        </div>
        @if($vencidosCount > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-rose-50/50 dark:bg-rose-950/10"><tr>
                    <th class="text-left px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-400">Medicamento</th>
                    <th class="text-left px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-400">N Lote</th>
                    <th class="text-left px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-400">Proveedor</th>
                    <th class="text-left px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-400">Vencio</th>
                    <th class="text-right px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-400">Stock</th>
                    <th class="text-left px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-400">Accion</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($lotesVencidos as $lote)
                    <tr class="hover:bg-rose-50/30 dark:hover:bg-rose-950/10 transition">
                        <td class="px-4 py-2.5 font-semibold text-slate-800 dark:text-slate-200">{{ $lote->producto->nombre ?? 'N/A' }}</td>
                        <td class="px-4 py-2.5 font-mono text-slate-500">{{ $lote->numero_lote }}</td>
                        <td class="px-4 py-2.5 text-slate-500">{{ $lote->proveedor->nombre ?? '—' }}</td>
                        <td class="px-4 py-2.5 text-rose-600 dark:text-rose-400 font-semibold">{{ $lote->fecha_vencimiento->format('d/m/Y') }}</td>
                        <td class="px-4 py-2.5 text-right font-mono font-bold text-rose-700 dark:text-rose-300">{{ $lote->stock_actual }}</td>
                        <td class="px-4 py-2.5">
                            @can('ajustar inventario')
                            <a href="{{ route('inventario.ajustar') }}?lote={{ $lote->id }}" class="inline-flex items-center space-x-1 px-2.5 py-1 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-[10px] font-bold transition">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                <span>Registrar Baja</span>
                            </a>
                            @endcan
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="px-5 py-8 text-center text-sm text-slate-400">
            <svg class="w-10 h-10 mx-auto mb-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Sin lotes vencidos con stock
        </div>
        @endif
    </div>

    {{-- Proximos a Vencer --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-amber-200 dark:border-amber-800/60 shadow-sm">
        <div class="flex items-center justify-between px-5 py-4 border-b border-amber-100 dark:border-amber-900/50">
            <div class="flex items-center space-x-2">
                <span class="w-3 h-3 rounded-full bg-amber-400"></span>
                <h3 class="text-sm font-bold text-amber-700 dark:text-amber-400">Lotes Proximos a Vencer (proximos 60 dias)</h3>
            </div>
            <span class="text-xs font-extrabold px-2.5 py-1 rounded-full bg-amber-100 dark:bg-amber-950/60 text-amber-700">{{ $porVencerCount }}</span>
        </div>
        @if($porVencerCount > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-amber-50/50 dark:bg-amber-950/10"><tr>
                    <th class="text-left px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-400">Medicamento</th>
                    <th class="text-left px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-400">N Lote</th>
                    <th class="text-right px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-400">Dias Rest.</th>
                    <th class="text-left px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-400">Vencimiento</th>
                    <th class="text-right px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-400">Stock</th>
                    <th class="text-left px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-400">Accion</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($lotesPorVencer as $lote)
                    @php $dias = $lote->dias_para_vencer ?? 0; $diasColor = $dias <= 15 ? 'text-rose-600' : ($dias <= 30 ? 'text-amber-600' : 'text-slate-700 dark:text-slate-300'); @endphp
                    <tr class="hover:bg-amber-50/20 dark:hover:bg-amber-950/10 transition">
                        <td class="px-4 py-2.5 font-semibold text-slate-800 dark:text-slate-200">{{ $lote->producto->nombre ?? 'N/A' }}</td>
                        <td class="px-4 py-2.5 font-mono text-slate-500">{{ $lote->numero_lote }}</td>
                        <td class="px-4 py-2.5 text-right font-bold {{ $diasColor }}">{{ $dias }}d</td>
                        <td class="px-4 py-2.5 text-slate-600 dark:text-slate-400">{{ $lote->fecha_vencimiento->format('d/m/Y') }}</td>
                        <td class="px-4 py-2.5 text-right font-mono font-bold text-slate-800 dark:text-slate-200">{{ $lote->stock_actual }}</td>
                        <td class="px-4 py-2.5">
                            <a href="{{ route('inventario.kardex-producto', $lote->producto_id) }}" class="text-emerald-600 hover:underline text-[10px] font-semibold">Ver Kardex</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="px-5 py-8 text-center text-sm text-slate-400">Sin lotes proximos a vencer en los proximos 60 dias.</div>
        @endif
    </div>

    {{-- Bajo Stock --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-emerald-200 dark:border-emerald-800/60 shadow-sm">
        <div class="flex items-center justify-between px-5 py-4 border-b border-indigo-100 dark:border-indigo-900/50">
            <div class="flex items-center space-x-2">
                <span class="w-3 h-3 rounded-full bg-indigo-500"></span>
                <h3 class="text-sm font-bold text-emerald-700 dark:text-emerald-400">Medicamentos Bajo Stock Minimo</h3>
            </div>
            <span class="text-xs font-extrabold px-2.5 py-1 rounded-full bg-emerald-50 dark:bg-emerald-950/60 text-indigo-700">{{ $bajoStockCount }}</span>
        </div>
        @if($bajoStockCount > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-indigo-50/50 dark:bg-emerald-950/10"><tr>
                    <th class="text-left px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-400">Medicamento</th>
                    <th class="text-left px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-400">Categoria</th>
                    <th class="text-right px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-400">Stock Min.</th>
                    <th class="text-right px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-400">Disponible</th>
                    <th class="text-left px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-400">Acciones</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($productosBajoStock as $producto)
                    <tr class="hover:bg-indigo-50/20 dark:hover:bg-indigo-950/10 transition">
                        <td class="px-4 py-2.5 font-semibold text-slate-800 dark:text-slate-200">{{ $producto->nombre }}</td>
                        <td class="px-4 py-2.5 text-slate-500">{{ $producto->categoria->nombre ?? '—' }}</td>
                        <td class="px-4 py-2.5 text-right font-mono text-slate-500">{{ $producto->stock_minimo }}</td>
                        <td class="px-4 py-2.5 text-right font-mono font-bold text-amber-600 dark:text-amber-400">{{ $producto->stock_disponible }}</td>
                        <td class="px-4 py-2.5">
                            <a href="{{ route('inventario.kardex-producto', $producto->id) }}" class="text-emerald-600 hover:underline text-[10px] font-semibold mr-2">Kardex</a>
                            <a href="{{ route('compras.create') }}" class="text-emerald-600 hover:underline text-[10px] font-semibold">Generar Compra</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="px-5 py-8 text-center text-sm text-slate-400">Todos los medicamentos tienen stock suficiente.</div>
        @endif
    </div>
</div>
@endsection
