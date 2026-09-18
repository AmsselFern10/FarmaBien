@extends('layouts.app')
@section('title', 'Lotes de Inventario - FarmaBien')
@section('content')
<div class="space-y-5">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-300/80 dark:border-slate-800 pb-3">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">Lotes de Inventario</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">Estado, stock, proveedor y vencimiento por lote.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('inventario.ajustar') }}" class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Ajustar Stock</span>
            </a>
            <a href="{{ route('inventario.index') }}" class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Volver</span>
            </a>
        </div>
    </div>

    {{-- Filtros --}}
    <form method="GET" action="{{ route('inventario.lotes') }}" class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-4">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Buscar lote o medicamento</label>
                <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Numero de lote, nombre medicamento..." class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Estado de Vencimiento</label>
                <select name="filtro_vencimiento" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                    <option value="">Todos</option>
                    <option value="proximos_30" {{ request('filtro_vencimiento')=='proximos_30'?'selected':'' }}>Proximos 30 dias</option>
                    <option value="proximos_60" {{ request('filtro_vencimiento')=='proximos_60'?'selected':'' }}>Proximos 60 dias</option>
                    <option value="vencidos" {{ request('filtro_vencimiento')=='vencidos'?'selected':'' }}>Vencidos</option>
                </select>
            </div>
        </div>
        <div class="flex items-center gap-2 mt-3">
            <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition">Filtrar</button>
            <a href="{{ route('inventario.lotes') }}" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-xl transition">Limpiar</a>
            <span class="text-xs text-slate-400 ml-2">{{ $lotes->total() }} lotes encontrados</span>
        </div>
    </form>

    {{-- Tabla --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/50">
                    <tr>
                        <th class="text-left px-4 py-3 font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">N Lote</th>
                        <th class="text-left px-4 py-3 font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Medicamento</th>
                        <th class="text-left px-4 py-3 font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Proveedor</th>
                        <th class="text-right px-4 py-3 font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Stock Ini.</th>
                        <th class="text-right px-4 py-3 font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Stock Act.</th>
                        <th class="text-left px-4 py-3 font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Vencimiento</th>
                        <th class="text-left px-4 py-3 font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Estado</th>
                        <th class="text-left px-4 py-3 font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($lotes as $lote)
                    @php
                        $hoy = now()->toDateString();
                        $venc = $lote->fecha_vencimiento->toDateString();
                        $dias = (int) now()->diffInDays($lote->fecha_vencimiento, false);
                        $estadoClass = $venc < $hoy ? 'bg-rose-100 dark:bg-rose-950/40 text-rose-700 dark:text-rose-400' : ($dias <= 30 ? 'bg-amber-100 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400' : 'bg-emerald-100 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400');
                        $estadoLabel = $venc < $hoy ? 'Vencido' : ($dias <= 30 ? 'Por vencer' : 'Vigente');
                    @endphp
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition {{ $venc < $hoy ? 'bg-rose-50/30 dark:bg-rose-950/10' : '' }}">
                        <td class="px-4 py-3 font-mono font-bold text-slate-700 dark:text-slate-300">{{ $lote->numero_lote }}</td>
                        <td class="px-4 py-3">
                            <p class="font-semibold text-slate-800 dark:text-slate-200">{{ $lote->producto->nombre ?? 'N/A' }}</p>
                            <p class="text-[10px] text-slate-400">{{ $lote->producto->categoria->nombre ?? '' }} · {{ $lote->producto->laboratorio->nombre ?? '' }}</p>
                        </td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $lote->proveedor->nombre ?? '—' }}</td>
                        <td class="px-4 py-3 text-right font-mono text-slate-600 dark:text-slate-400">{{ number_format($lote->stock_inicial) }}</td>
                        <td class="px-4 py-3 text-right">
                            <span class="font-mono font-bold text-slate-900 dark:text-white">{{ number_format($lote->stock_actual) }}</span>
                            @if($lote->stock_actual == 0)<span class="ml-1 text-[10px] text-rose-500 font-semibold">AGOTADO</span>@endif
                        </td>
                        <td class="px-4 py-3">
                            <p class="font-semibold {{ $venc < $hoy ? 'text-rose-600 dark:text-rose-400' : 'text-slate-700 dark:text-slate-300' }}">{{ $lote->fecha_vencimiento->format('d/m/Y') }}</p>
                            @if($dias > 0 && $dias <= 60)<p class="text-[10px] text-amber-500">{{ $dias }} dias restantes</p>@endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $estadoClass }}">{{ $estadoLabel }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('inventario.kardex-producto', $lote->producto_id) }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 font-semibold text-[10px]">Kardex</a>
                                @can('ajustar inventario')
                                <a href="{{ route('inventario.ajustar') }}?lote={{ $lote->id }}" class="text-amber-600 hover:text-amber-800 dark:text-amber-400 font-semibold text-[10px]">Ajustar</a>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="px-4 py-12 text-center text-slate-400">
                        <svg class="w-10 h-10 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        No se encontraron lotes con los filtros aplicados.
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($lotes->hasPages())
        <div class="px-4 py-3 border-t border-slate-100 dark:border-slate-800">{{ $lotes->links() }}</div>
        @endif
    </div>
</div>
@endsection
