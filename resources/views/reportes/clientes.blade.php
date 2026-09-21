@extends('layouts.app')

@section('title', 'Reporte de Clientes y Frecuencia - FarmaBien')

@section('content')
<div class="space-y-5">

    {{-- Breadcrumb --}}
    <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('reportes.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Centro de Reportes</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-800 dark:text-slate-200 font-semibold">Clientes y Frecuencia</span>
    </nav>

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-1 border-b border-slate-300/80 dark:border-slate-800 print:hidden">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">Reporte de Clientes y Frecuencia de Compra</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Ranking de pacientes por volumen, frecuencia de visitas y ticket promedio.</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('reportes.index') }}"
               class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-bold transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Volver</span>
            </a>
            <button type="button" onclick="window.print()"
                    class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-bold transition">
                <svg class="w-3.5 h-3.5 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Imprimir</span>
            </button>
            <a href="{{ route('reportes.clientes', array_merge(request()->query(), ['export' => 'pdf'])) }}" target="_blank"
               class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white rounded-xl text-xs font-bold shadow-sm transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                <span>Exportar PDF</span>
            </a>
            <a href="{{ route('reportes.clientes', array_merge(request()->query(), ['export' => 'csv'])) }}"
               class="inline-flex items-center space-x-1.5 px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white rounded-xl text-xs font-bold shadow-sm transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <span>Exportar CSV</span>
            </a>
        </div>
    </div>

    {{-- Print header institucional --}}
    <div class="hidden print:block border-b-2 border-violet-600 pb-3 mb-4">
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-xl font-bold text-emerald-700 uppercase tracking-wide">FARMABIEN</h1>
                <p class="text-xs text-slate-600">Farmacia & Droguería FarmaBien C.A. · Sistema de Gestión Farmacéutica</p>
                <p class="text-[10px] text-slate-500 mt-0.5"><strong>RIF / RUC:</strong> J-40892154-0 &bull; <strong>Teléfono:</strong> (0212) 555-0199 / +58 412-1234567</p>
                <p class="text-[10px] text-slate-500"><strong>Dirección:</strong> Av. Principal Los Próceres, Edif. FarmaBien, Caracas - Venezuela</p>
            </div>
            <div class="text-right">
                <div class="inline-block border border-violet-600 bg-violet-50 px-3 py-1.5 rounded text-center">
                    <p class="text-xs font-bold text-violet-800">REPORTE DE CLIENTES Y FRECUENCIA</p>
                    <p class="text-[9px] text-violet-700 mt-0.5">Emisión: {{ now()->format('d/m/Y H:i') }}</p>
                    <p class="text-[9px] text-violet-700">Por: {{ Auth::user()->name ?? 'Sistema' }}</p>
                </div>
            </div>
        </div>
        <div class="mt-2 text-xs bg-slate-100 p-2 rounded flex items-center justify-between">
            <span><strong>Período:</strong> {{ \Carbon\Carbon::parse($fechaDesde)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($fechaHasta)->format('d/m/Y') }}</span>
            <span><strong>Clientes con compras:</strong> {{ number_format($clientesConCompras) }}</span>
            <span><strong>Total facturado:</strong> ${{ number_format($totalFacturadoClientes, 2) }}</span>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-300 dark:border-slate-800 shadow-xs print:hidden">
        <form method="GET" action="{{ route('reportes.clientes') }}" class="flex flex-wrap items-end gap-3">
            <div>
                <label for="rcl_desde" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Desde</label>
                <input type="date" id="rcl_desde" name="fecha_desde" value="{{ $fechaDesde }}"
                       class="px-3 py-1.5 rounded-xl text-xs border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-400">
            </div>
            <div>
                <label for="rcl_hasta" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Hasta</label>
                <input type="date" id="rcl_hasta" name="fecha_hasta" value="{{ $fechaHasta }}"
                       class="px-3 py-1.5 rounded-xl text-xs border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-400">
            </div>
            @php
                $presetsC = [
                    ['Hoy',       now()->toDateString(), now()->toDateString()],
                    ['7 días',    now()->subDays(6)->toDateString(), now()->toDateString()],
                    ['Este mes',  now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()],
                    ['Mes ant.',  now()->subMonth()->startOfMonth()->toDateString(), now()->subMonth()->endOfMonth()->toDateString()],
                    ['Este año',  now()->startOfYear()->toDateString(), now()->endOfYear()->toDateString()],
                ];
            @endphp
            @foreach($presetsC as [$lbl, $d1, $d2])
            <a href="{{ route('reportes.clientes', ['fecha_desde'=>$d1,'fecha_hasta'=>$d2]) }}"
               class="px-2.5 py-1.5 rounded-xl text-xs font-bold border transition {{ ($fechaDesde===$d1 && $fechaHasta===$d2) ? 'bg-violet-600 text-white border-violet-600 dark:bg-violet-600' : 'border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:border-violet-400 hover:text-violet-700 dark:hover:text-violet-400' }}">{{ $lbl }}</a>
            @endforeach
            <button type="submit" class="px-4 py-1.5 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm transition">Filtrar</button>
            @if(request()->hasAny(['fecha_desde','fecha_hasta']))
            <a href="{{ route('reportes.clientes') }}" class="px-3 py-1.5 rounded-xl text-xs font-bold border border-slate-300 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition">Limpiar</a>
            @endif
        </form>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="bg-white dark:bg-slate-900 rounded-xl p-3.5 border border-slate-300 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Padrón Total</p>
                <p class="text-lg font-bold text-slate-900 dark:text-white mt-0.5">{{ number_format($totalClientes) }}</p>
                <p class="text-[10px] text-slate-400 mt-0.5">Clientes activos</p>
            </div>
            <div class="w-9 h-9 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-xl p-3.5 border border-slate-300 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Con Compras</p>
                <p class="text-lg font-bold text-violet-600 dark:text-violet-400 mt-0.5">{{ number_format($clientesConCompras) }}</p>
                <p class="text-[10px] text-slate-400 mt-0.5">En el período</p>
            </div>
            <div class="w-9 h-9 rounded-lg bg-violet-50 dark:bg-violet-950/60 text-violet-600 dark:text-violet-400 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-xl p-3.5 border border-slate-300 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Total Facturado</p>
                <p class="text-lg font-bold text-emerald-600 dark:text-emerald-400 mt-0.5">${{ number_format($totalFacturadoClientes, 2) }}</p>
                <p class="text-[10px] text-slate-400 mt-0.5">A clientes identificados</p>
            </div>
            <div class="w-9 h-9 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-xl p-3.5 border border-slate-300 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Ticket Promedio</p>
                <p class="text-lg font-bold text-slate-900 dark:text-white mt-0.5">${{ number_format($ticketPromedio, 2) }}</p>
                <p class="text-[10px] text-slate-400 mt-0.5">Por transacción/cliente</p>
            </div>
            <div class="w-9 h-9 rounded-lg bg-teal-50 dark:bg-teal-950/60 text-teal-600 dark:text-teal-400 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            </div>
        </div>
    </div>

    {{-- Tabla de Top Clientes --}}
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-300 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="flex items-center justify-between px-5 py-3 border-b border-slate-200 dark:border-slate-800">
            <div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Top 20 Clientes por Volumen</h3>
                <p class="text-xs text-slate-400">
                    {{ \Carbon\Carbon::parse($fechaDesde)->format('d/m/Y') }} — {{ \Carbon\Carbon::parse($fechaHasta)->format('d/m/Y') }}
                </p>
            </div>
        </div>
        @if($topClientes->isEmpty())
        <div class="flex flex-col items-center justify-center py-14">
            <svg class="w-10 h-10 text-slate-300 dark:text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Sin clientes con compras en este período</p>
        </div>
        @else
        @php $maxMonto = $topClientes->first()?->monto_total ?? 1; @endphp
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/60">
                    <tr>
                        <th class="px-4 py-2.5 text-left font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-10">#</th>
                        <th class="px-4 py-2.5 text-left font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Paciente</th>
                        <th class="px-4 py-2.5 text-left font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Documento</th>
                        <th class="px-4 py-2.5 text-center font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Visitas</th>
                        <th class="px-4 py-2.5 text-right font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Gastado</th>
                        <th class="px-4 py-2.5 text-right font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Ticket Prom.</th>
                        <th class="px-4 py-2.5 font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Participación</th>
                        <th class="px-4 py-2.5 print:hidden"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($topClientes as $i => $c)
                    @php
                        $pct = $maxMonto > 0 ? round(($c->monto_total / $maxMonto) * 100, 1) : 0;
                        $medal = match($i) { 0 => 'bg-amber-400 text-white', 1 => 'bg-slate-400 text-white', 2 => 'bg-orange-400 text-white', default => 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300' };
                        $barC = $i === 0 ? 'bg-amber-400' : ($i <= 2 ? 'bg-violet-400' : 'bg-violet-300 dark:bg-violet-700');
                        $ticketC = $c->total_ventas > 0 ? $c->monto_total / $c->total_ventas : 0;
                    @endphp
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="px-4 py-2.5"><span class="w-6 h-6 rounded-lg {{ $medal }} flex items-center justify-center font-extrabold text-xs">{{ $i+1 }}</span></td>
                        <td class="px-4 py-2.5">
                            <p class="font-semibold text-slate-800 dark:text-slate-200">{{ $c->nombre }}</p>
                            @if($c->email)
                            <p class="text-slate-400">{{ $c->email }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-2.5 text-slate-500 dark:text-slate-400 font-mono">{{ $c->documento ?? '—' }}</td>
                        <td class="px-4 py-2.5 text-center">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-lg bg-violet-50 dark:bg-violet-950/60 text-violet-700 dark:text-violet-400 font-bold">{{ number_format($c->total_ventas) }}</span>
                        </td>
                        <td class="px-4 py-2.5 text-right font-bold text-emerald-600 dark:text-emerald-400">${{ number_format($c->monto_total, 2) }}</td>
                        <td class="px-4 py-2.5 text-right text-slate-600 dark:text-slate-300">${{ number_format($ticketC, 2) }}</td>
                        <td class="px-4 py-2.5 min-w-[130px]">
                            <div class="flex items-center gap-2">
                                <div class="flex-1 bg-slate-100 dark:bg-slate-800 rounded-full h-1.5">
                                    <div class="{{ $barC }} h-1.5 rounded-full" style="width: {{ $pct }}%"></div>
                                </div>
                                <span class="text-slate-400 w-8 text-right">{{ $pct }}%</span>
                            </div>
                        </td>
                        <td class="px-4 py-2.5 print:hidden">
                            @can('ver clientes')
                            <a href="{{ route('clientes.show', $c) }}" class="text-xs font-bold text-violet-600 dark:text-violet-400 hover:underline">Ver</a>
                            @endcan
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-slate-100 dark:bg-slate-800/90 border-t border-slate-300 dark:border-slate-700">
                        <td colspan="4" class="px-4 py-2.5 text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">
                            Total ({{ number_format($clientesConCompras) }} clientes identificados)
                        </td>
                        <td class="px-4 py-2.5 text-right text-xs font-extrabold text-emerald-600 dark:text-emerald-400">${{ number_format($totalFacturadoClientes, 2) }}</td>
                        <td class="px-4 py-2.5 text-right text-xs font-extrabold text-slate-800 dark:text-slate-200">${{ number_format($ticketPromedio, 2) }}</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @endif
    </div>

    {{-- Nota aclaratoria --}}
    <p class="text-xs text-slate-400 dark:text-slate-500 text-center print:hidden">
        * Solo se muestran ventas vinculadas a clientes identificados. Las ventas a público general no están incluidas.
    </p>

</div>
<style>@media print { .print\:hidden { display: none !important; } }</style>
@endsection
