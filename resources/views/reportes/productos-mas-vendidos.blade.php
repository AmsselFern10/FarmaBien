@extends('layouts.app')
@section('title', 'Top Medicamentos Más Vendidos - FarmaBien')
@section('content')
<div class="space-y-5" x-data="{ setDates(p){ const n=new Date(),f=new Date(),t=new Date(); if(p==='7d'){f.setDate(n.getDate()-6);}else if(p==='mes'){f.setDate(1);t.setMonth(n.getMonth()+1,0);}else if(p==='mesant'){f.setMonth(n.getMonth()-1,1);t.setMonth(n.getMonth(),0);}else if(p==='anio'){f.setMonth(0,1);t.setMonth(11,31);}const fmt=d=>d.toISOString().split('T')[0]; document.getElementById('pmv_d').value=fmt(f); document.getElementById('pmv_h').value=fmt(t); } }">

    <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('reportes.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Centro de Reportes</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-800 dark:text-slate-200 font-semibold">Top Medicamentos</span>
    </nav>

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-1 border-b border-slate-300/80 dark:border-slate-800 print:hidden">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">Medicamentos Más Vendidos</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Ranking por unidades despachadas e ingresos — Top 20.</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('reportes.index') }}" class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-bold transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Volver</span>
            </a>
            <button type="button" onclick="window.print()" class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-bold transition">
                <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                <span>Exportar PDF / Imprimir</span>
            </button>
            <a href="{{ route('reportes.productos-mas-vendidos', array_merge(request()->query(), ['export' => 'csv'])) }}"
               class="inline-flex items-center space-x-1.5 px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white rounded-xl text-xs font-bold shadow-sm transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <span>Exportar CSV</span>
            </a>
        </div>
    </div>

    {{-- Filtro Período --}}
    <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-300 dark:border-slate-800 shadow-xs print:hidden">
        <form method="GET" action="{{ route('reportes.productos-mas-vendidos') }}" class="flex flex-wrap items-end gap-3">
            <div>
                <label for="pmv_d" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Desde</label>
                <input type="date" id="pmv_d" name="fecha_desde" value="{{ $fechaDesde }}" class="px-3 py-1.5 rounded-xl text-xs border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-400">
            </div>
            <div>
                <label for="pmv_h" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Hasta</label>
                <input type="date" id="pmv_h" name="fecha_hasta" value="{{ $fechaHasta }}" class="px-3 py-1.5 rounded-xl text-xs border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-400">
            </div>
            @foreach([['Hoy','hoy'],['7 días','7d'],['Este mes','mes'],['Mes ant.','mesant'],['Año','anio']] as [$lb,$pr])
            <button type="button" @click="setDates('{{ $pr }}')"
                    class="px-2.5 py-1.5 rounded-xl text-xs font-bold border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:border-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-400 transition">{{ $lb }}</button>
            @endforeach
            <button type="submit" class="px-4 py-1.5 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm transition">Filtrar</button>
            @if(request()->hasAny(['fecha_desde','fecha_hasta']))
            <a href="{{ route('reportes.productos-mas-vendidos') }}" class="px-3 py-1.5 rounded-xl text-xs font-bold border border-slate-300 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition">Limpiar</a>
            @endif
        </form>
    </div>

    {{-- KPIs --}}
    @php $top1=$ranking->first(); $totalU=$ranking->sum('total_unidades_vendidas'); $totalI=$ranking->sum('total_ingresos'); $maxU=$ranking->max('total_unidades_vendidas')?:1; @endphp
    <div class="grid grid-cols-2 lg:grid-cols-3 gap-3">
        <div class="bg-white dark:bg-slate-900 rounded-xl p-3.5 border border-slate-300 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div><p class="text-[11px] font-medium text-slate-500 dark:text-slate-400">🥇 Producto #1</p><p class="text-sm font-bold text-slate-900 dark:text-white mt-0.5 truncate max-w-[150px]">{{ $top1?->nombre ?? '—' }}</p><p class="text-[10px] text-slate-400 mt-0.5">{{ $top1 ? number_format($top1->total_unidades_vendidas).' unidades' : 'Sin datos' }}</p></div>
            <div class="w-9 h-9 rounded-lg bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg></div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-xl p-3.5 border border-slate-300 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div><p class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Unidades Despachadas</p><p class="text-lg font-bold text-slate-900 dark:text-white mt-0.5">{{ number_format($totalU) }}</p><p class="text-[10px] text-slate-400 mt-0.5">Top 20</p></div>
            <div class="w-9 h-9 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg></div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-xl p-3.5 border border-slate-300 dark:border-slate-800 shadow-xs flex items-center justify-between col-span-2 lg:col-span-1">
            <div><p class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Ingresos Generados</p><p class="text-lg font-bold text-emerald-600 dark:text-emerald-400 mt-0.5">${{ number_format($totalI, 2) }}</p><p class="text-[10px] text-slate-400 mt-0.5">Top 20</p></div>
            <div class="w-9 h-9 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
        </div>
    </div>

    {{-- Ranking Table --}}
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-300 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="px-5 py-3 border-b border-slate-200 dark:border-slate-800">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Ranking de Medicamentos</h3>
            <p class="text-xs text-slate-400">{{ \Carbon\Carbon::parse($fechaDesde)->format('d/m/Y') }} — {{ \Carbon\Carbon::parse($fechaHasta)->format('d/m/Y') }}</p>
        </div>
        @if($ranking->isEmpty())
        <div class="flex flex-col items-center justify-center py-14">
            <svg class="w-10 h-10 text-slate-300 dark:text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Sin ventas en este período</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/60">
                    <tr>
                        <th class="px-4 py-2.5 text-left font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-10">#</th>
                        <th class="px-4 py-2.5 text-left font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Medicamento</th>
                        <th class="px-4 py-2.5 text-left font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Principio Activo</th>
                        <th class="px-4 py-2.5 text-right font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Unidades</th>
                        <th class="px-4 py-2.5 text-right font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Ingresos</th>
                        <th class="px-4 py-2.5 font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Participación</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($ranking as $i => $item)
                    @php
                        $pct = $maxU > 0 ? round(($item->total_unidades_vendidas/$maxU)*100,1) : 0;
                        $medal = match($i){ 0=>'bg-amber-400 text-white', 1=>'bg-slate-400 text-white', 2=>'bg-orange-400 text-white', default=>'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300' };
                        $bar = $i===0 ? 'bg-amber-400' : ($i<=2 ? 'bg-teal-400' : 'bg-indigo-400');
                    @endphp
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="px-4 py-2.5"><span class="w-6 h-6 rounded-lg {{ $medal }} flex items-center justify-center font-extrabold text-xs">{{ $i+1 }}</span></td>
                        <td class="px-4 py-2.5 font-semibold text-slate-800 dark:text-slate-200">{{ $item->nombre }}</td>
                        <td class="px-4 py-2.5 text-slate-500 dark:text-slate-400">{{ $item->principio_activo ?? '—' }}</td>
                        <td class="px-4 py-2.5 text-right"><span class="font-bold text-indigo-600 dark:text-indigo-400">{{ number_format($item->total_unidades_vendidas) }}</span> un.</td>
                        <td class="px-4 py-2.5 text-right font-bold text-emerald-600 dark:text-emerald-400">${{ number_format($item->total_ingresos, 2) }}</td>
                        <td class="px-4 py-2.5 min-w-[130px]">
                            <div class="flex items-center gap-2">
                                <div class="flex-1 bg-slate-100 dark:bg-slate-800 rounded-full h-1.5">
                                    <div class="{{ $bar }} h-1.5 rounded-full" style="width: {{ $pct }}%"></div>
                                </div>
                                <span class="text-slate-400 w-8 text-right">{{ $pct }}%</span>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-slate-100 dark:bg-slate-800/90 border-t border-slate-300 dark:border-slate-700">
                        <td colspan="3" class="px-4 py-2.5 text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">Totales (Top {{ $ranking->count() }})</td>
                        <td class="px-4 py-2.5 text-right text-xs font-extrabold text-indigo-600 dark:text-indigo-400">{{ number_format($totalU) }} un.</td>
                        <td class="px-4 py-2.5 text-right text-xs font-extrabold text-emerald-600 dark:text-emerald-400">${{ number_format($totalI, 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @endif
    </div>
</div>
<style>@media print { .print\:hidden { display: none !important; } }</style>
@endsection
