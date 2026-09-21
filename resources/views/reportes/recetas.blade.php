@extends('layouts.app')

@section('title', 'Reporte de Recetas Médicas - FarmaBien')

@section('content')
<div class="space-y-5">

    {{-- Breadcrumb --}}
    <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('reportes.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Centro de Reportes</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-800 dark:text-slate-200 font-semibold">Recetas Médicas</span>
    </nav>

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-1 border-b border-slate-300/80 dark:border-slate-800 print:hidden">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">Reporte de Recetas Médicas</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Control de dispensación: procesadas, pendientes, vencidas y rechazadas.</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('reportes.index') }}"
               class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-bold transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Volver</span>
            </a>
            <button type="button" onclick="window.print()"
                    class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-bold transition">
                <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                <span>Exportar PDF / Imprimir</span>
            </button>
            <a href="{{ route('reportes.recetas', array_merge(request()->query(), ['export' => 'csv'])) }}"
               class="inline-flex items-center space-x-1.5 px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white rounded-xl text-xs font-bold shadow-sm transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <span>Exportar CSV</span>
            </a>
            @can('crear recetas')
            <a href="{{ route('recetas.create') }}"
               class="inline-flex items-center space-x-1.5 px-3.5 py-1.5 bg-sky-600 hover:bg-sky-700 text-white rounded-xl text-xs font-bold shadow-sm transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Nueva Receta</span>
            </a>
            @endcan
        </div>
    </div>

    {{-- Filtros --}}
    <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-300 dark:border-slate-800 shadow-xs print:hidden">
        <form method="GET" action="{{ route('reportes.recetas') }}" class="flex flex-wrap items-end gap-3">
            <div>
                <label for="rrec_desde" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Desde</label>
                <input type="date" id="rrec_desde" name="fecha_desde" value="{{ $fechaDesde }}"
                       class="px-3 py-1.5 rounded-xl text-xs border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-400">
            </div>
            <div>
                <label for="rrec_hasta" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Hasta</label>
                <input type="date" id="rrec_hasta" name="fecha_hasta" value="{{ $fechaHasta }}"
                       class="px-3 py-1.5 rounded-xl text-xs border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-400">
            </div>
            <div>
                <label for="rrec_est" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Estado</label>
                <select id="rrec_est" name="estado" class="px-3 py-1.5 rounded-xl text-xs border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-400">
                    <option value="">Todos</option>
                    <option value="pendiente"  {{ $estadoFiltro === 'pendiente'  ? 'selected' : '' }}>Pendiente</option>
                    <option value="procesada"  {{ $estadoFiltro === 'procesada'  ? 'selected' : '' }}>Procesada</option>
                    <option value="vencida"    {{ $estadoFiltro === 'vencida'    ? 'selected' : '' }}>Vencida</option>
                    <option value="rechazada"  {{ $estadoFiltro === 'rechazada'  ? 'selected' : '' }}>Rechazada</option>
                </select>
            </div>
            @php
                $presetsR = [
                    ['Hoy',      now()->toDateString(), now()->toDateString()],
                    ['7 días',   now()->subDays(6)->toDateString(), now()->toDateString()],
                    ['Este mes', now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()],
                    ['Mes ant.', now()->subMonth()->startOfMonth()->toDateString(), now()->subMonth()->endOfMonth()->toDateString()],
                ];
            @endphp
            @foreach($presetsR as [$lbl, $d1, $d2])
            <a href="{{ route('reportes.recetas', ['fecha_desde'=>$d1,'fecha_hasta'=>$d2,'estado'=>$estadoFiltro]) }}"
               class="px-2.5 py-1.5 rounded-xl text-xs font-bold border transition {{ ($fechaDesde===$d1 && $fechaHasta===$d2) ? 'bg-sky-600 text-white border-sky-600 dark:bg-sky-600' : 'border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:border-sky-400 hover:text-sky-700 dark:hover:text-sky-400' }}">{{ $lbl }}</a>
            @endforeach
            <button type="submit" class="px-4 py-1.5 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm transition">Filtrar</button>
            @if(request()->hasAny(['fecha_desde','fecha_hasta','estado']))
            <a href="{{ route('reportes.recetas') }}"
               class="px-3 py-1.5 rounded-xl text-xs font-bold border border-slate-300 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition">Limpiar</a>
            @endif
        </form>
    </div>

    {{-- KPI Cards: mini badges de estado, cliqueables para filtrar --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
        {{-- Total --}}
        <a href="{{ route('reportes.recetas', ['fecha_desde'=>$fechaDesde,'fecha_hasta'=>$fechaHasta]) }}"
           class="bg-white dark:bg-slate-900 rounded-xl p-3.5 border border-slate-300 dark:border-slate-800 shadow-xs flex items-center justify-between hover:shadow-sm transition {{ !$estadoFiltro ? 'ring-2 ring-sky-400 dark:ring-offset-slate-900 ring-offset-1' : '' }}">
            <div>
                <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Total</p>
                <p class="text-lg font-bold text-slate-900 dark:text-white mt-0.5">{{ number_format($totalRecetas) }}</p>
                <p class="text-[10px] text-slate-400 mt-0.5">Recetas</p>
            </div>
            <div class="w-9 h-9 rounded-lg bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
        </a>
        {{-- Procesadas --}}
        <a href="{{ route('reportes.recetas', ['fecha_desde'=>$fechaDesde,'fecha_hasta'=>$fechaHasta,'estado'=>'procesada']) }}"
           class="bg-white dark:bg-slate-900 rounded-xl p-3.5 border border-slate-300 dark:border-slate-800 shadow-xs flex items-center justify-between hover:shadow-sm transition {{ $estadoFiltro==='procesada' ? 'ring-2 ring-emerald-400 dark:ring-offset-slate-900 ring-offset-1' : '' }}">
            <div>
                <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Procesadas</p>
                <p class="text-lg font-bold text-emerald-600 dark:text-emerald-400 mt-0.5">{{ number_format($procesadas) }}</p>
                <p class="text-[10px] text-slate-400 mt-0.5">Dispensadas</p>
            </div>
            <div class="w-9 h-9 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>
        </a>
        {{-- Pendientes --}}
        <a href="{{ route('reportes.recetas', ['fecha_desde'=>$fechaDesde,'fecha_hasta'=>$fechaHasta,'estado'=>'pendiente']) }}"
           class="bg-white dark:bg-slate-900 rounded-xl p-3.5 border border-slate-300 dark:border-slate-800 shadow-xs flex items-center justify-between hover:shadow-sm transition {{ $estadoFiltro==='pendiente' ? 'ring-2 ring-amber-400 dark:ring-offset-slate-900 ring-offset-1' : '' }}">
            <div>
                <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Pendientes</p>
                <p class="text-lg font-bold text-amber-600 dark:text-amber-400 mt-0.5">{{ number_format($pendientes) }}</p>
                <p class="text-[10px] text-slate-400 mt-0.5">Por dispensar</p>
            </div>
            <div class="w-9 h-9 rounded-lg bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </a>
        {{-- Vencidas --}}
        <a href="{{ route('reportes.recetas', ['fecha_desde'=>$fechaDesde,'fecha_hasta'=>$fechaHasta,'estado'=>'vencida']) }}"
           class="bg-white dark:bg-slate-900 rounded-xl p-3.5 border border-slate-300 dark:border-slate-800 shadow-xs flex items-center justify-between hover:shadow-sm transition {{ $estadoFiltro==='vencida' ? 'ring-2 ring-rose-400 dark:ring-offset-slate-900 ring-offset-1' : '' }}">
            <div>
                <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Vencidas</p>
                <p class="text-lg font-bold text-rose-600 dark:text-rose-400 mt-0.5">{{ number_format($vencidas) }}</p>
                <p class="text-[10px] text-slate-400 mt-0.5">Expiradas</p>
            </div>
            <div class="w-9 h-9 rounded-lg bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </a>
        {{-- Rechazadas --}}
        <a href="{{ route('reportes.recetas', ['fecha_desde'=>$fechaDesde,'fecha_hasta'=>$fechaHasta,'estado'=>'rechazada']) }}"
           class="bg-white dark:bg-slate-900 rounded-xl p-3.5 border border-slate-300 dark:border-slate-800 shadow-xs flex items-center justify-between hover:shadow-sm transition {{ $estadoFiltro==='rechazada' ? 'ring-2 ring-slate-500 dark:ring-offset-slate-900 ring-offset-1' : '' }}">
            <div>
                <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Rechazadas</p>
                <p class="text-lg font-bold text-slate-600 dark:text-slate-400 mt-0.5">{{ number_format($rechazadas) }}</p>
                <p class="text-[10px] text-slate-400 mt-0.5">No dispensadas</p>
            </div>
            <div class="w-9 h-9 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
            </div>
        </a>
    </div>

    {{-- Tabla de Recetas --}}
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-300 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="flex items-center justify-between px-5 py-3 border-b border-slate-200 dark:border-slate-800">
            <div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                    Listado de Recetas
                    @if($estadoFiltro)
                    <span class="ml-2 px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-100 text-sky-800 dark:bg-sky-950 dark:text-sky-300">{{ ucfirst($estadoFiltro) }}</span>
                    @endif
                </h3>
                <p class="text-xs text-slate-400">{{ number_format($recetas->total()) }} registros</p>
            </div>
        </div>

        @if($recetas->isEmpty())
        <div class="flex flex-col items-center justify-center py-14">
            <svg class="w-10 h-10 text-slate-300 dark:text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Sin recetas en este período</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/60">
                    <tr>
                        <th class="px-4 py-2.5 text-left font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">N° Receta</th>
                        <th class="px-4 py-2.5 text-left font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Paciente</th>
                        <th class="px-4 py-2.5 text-left font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Médico</th>
                        <th class="px-4 py-2.5 text-left font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Tipo</th>
                        <th class="px-4 py-2.5 text-left font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Emisión</th>
                        <th class="px-4 py-2.5 text-left font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Vencimiento</th>
                        <th class="px-4 py-2.5 text-left font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Estado</th>
                        <th class="px-4 py-2.5 print:hidden"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($recetas as $r)
                    @php
                        $stClasses = match($r->estado) {
                            'procesada' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300',
                            'pendiente' => 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300',
                            'vencida'   => 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300',
                            'rechazada' => 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300',
                            default     => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300',
                        };
                        $esPendiente = $r->estado === 'pendiente';
                        $esVencida = $r->estado === 'vencida';
                    @endphp
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors {{ $esVencida ? 'bg-rose-50/20 dark:bg-rose-950/10' : '' }}">
                        <td class="px-4 py-2.5 font-mono font-bold text-slate-700 dark:text-slate-300">
                            {{ $r->numero_receta ?? '#'.str_pad($r->id, 5, '0', STR_PAD_LEFT) }}
                        </td>
                        <td class="px-4 py-2.5">
                            <p class="font-semibold text-slate-800 dark:text-slate-200">{{ $r->paciente_nombre ?? ($r->cliente?->nombre ?? '—') }}</p>
                            @if($r->paciente_documento)
                            <p class="text-slate-400">{{ $r->paciente_documento }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-2.5">
                            <p class="font-semibold text-slate-700 dark:text-slate-300">{{ $r->medico_nombre ?? '—' }}</p>
                            @if($r->medico_especialidad)
                            <p class="text-slate-400">{{ $r->medico_especialidad }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-2.5 text-slate-500 dark:text-slate-400">{{ ucfirst($r->tipo_receta ?? '—') }}</td>
                        <td class="px-4 py-2.5 text-slate-500 dark:text-slate-400 whitespace-nowrap">
                            {{ $r->fecha_emision ? \Carbon\Carbon::parse($r->fecha_emision)->format('d/m/Y') : $r->created_at->format('d/m/Y') }}
                        </td>
                        <td class="px-4 py-2.5 whitespace-nowrap">
                            @if($r->fecha_vencimiento)
                            @php $dv = \Carbon\Carbon::parse($r->fecha_vencimiento); @endphp
                            <span class="{{ $dv->isPast() ? 'text-rose-600 dark:text-rose-400 font-semibold' : 'text-slate-500 dark:text-slate-400' }}">
                                {{ $dv->format('d/m/Y') }}
                            </span>
                            @if($esPendiente && !$dv->isPast())
                            <span class="block text-[10px] text-amber-500">Expira {{ $dv->diffForHumans() }}</span>
                            @endif
                            @else
                            <span class="text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-2.5">
                            <span class="px-2 py-0.5 rounded-full font-semibold {{ $stClasses }}">{{ ucfirst($r->estado) }}</span>
                        </td>
                        <td class="px-4 py-2.5 print:hidden">
                            @can('ver recetas')
                            <a href="{{ route('recetas.show', $r) }}" class="text-xs font-bold text-sky-600 dark:text-sky-400 hover:underline">Ver</a>
                            @endcan
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-slate-100 dark:bg-slate-800/90 border-t border-slate-300 dark:border-slate-700">
                        <td colspan="6" class="px-4 py-2.5 text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">Total ({{ number_format($recetas->total()) }} recetas)</td>
                        <td colspan="2" class="px-4 py-2.5 text-xs font-extrabold text-sky-600 dark:text-sky-400">{{ $procesadas }} procesadas · {{ $pendientes }} pendientes</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @if($recetas->hasPages())
        <div class="px-5 py-3 border-t border-slate-200 dark:border-slate-800 print:hidden">
            {{ $recetas->links() }}
        </div>
        @endif
        @endif
    </div>

</div>
<style>@media print { .print\:hidden { display: none !important; } }</style>
@endsection
