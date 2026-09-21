@extends('layouts.app')

@section('title', 'Reporte de Ventas e Ingresos - FarmaBien')

@section('content')
<div class="space-y-5" x-data="{
    setDates(preset) {
        const now = new Date();
        let from = new Date(), to = new Date();
        if (preset === 'hoy') { /* same */ }
        else if (preset === '7dias')  { from.setDate(now.getDate() - 6); }
        else if (preset === 'mes')    { from = new Date(now.getFullYear(), now.getMonth(), 1); to = new Date(now.getFullYear(), now.getMonth()+1, 0); }
        else if (preset === 'mesant') { from = new Date(now.getFullYear(), now.getMonth()-1, 1); to = new Date(now.getFullYear(), now.getMonth(), 0); }
        else if (preset === 'anio')   { from = new Date(now.getFullYear(), 0, 1); to = new Date(now.getFullYear(), 11, 31); }
        const fmt = d => d.toISOString().split('T')[0];
        document.getElementById('rv_desde').value = fmt(from);
        document.getElementById('rv_hasta').value = fmt(to);
    }
}">

    {{-- Breadcrumb --}}
    <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('reportes.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Centro de Reportes</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-800 dark:text-slate-200 font-semibold">Ventas e Ingresos</span>
    </nav>

    {{-- Header & Actions --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-1 border-b border-slate-300/80 dark:border-slate-800 print:hidden">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">Reporte de Ventas e Ingresos</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Período: <span class="font-semibold text-slate-700 dark:text-slate-200">{{ \Carbon\Carbon::parse($fechaDesde)->format('d/m/Y') }}</span>
                al <span class="font-semibold text-slate-700 dark:text-slate-200">{{ \Carbon\Carbon::parse($fechaHasta)->format('d/m/Y') }}</span>
            </p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('reportes.index') }}"
               class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-bold transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Volver</span>
            </a>
            <button type="button" onclick="window.print()"
                    class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-bold transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Imprimir</span>
            </button>
            <a href="{{ route('reportes.ventas', array_merge(request()->query(), ['export' => 'csv'])) }}"
               class="inline-flex items-center space-x-1.5 px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-sm transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <span>Exportar CSV</span>
            </a>
        </div>
    </div>

    {{-- Print header --}}
    <div class="hidden print:block border-b border-slate-300 pb-4 mb-4">
        <h1 class="text-2xl font-bold text-slate-900">FarmaBien — Reporte de Ventas e Ingresos</h1>
        <p class="text-xs text-slate-500 mt-1">Período: {{ \Carbon\Carbon::parse($fechaDesde)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($fechaHasta)->format('d/m/Y') }} &bull; Generado: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="bg-white dark:bg-slate-900 rounded-xl p-3.5 border border-slate-300 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Total Facturado</p>
                <p class="text-lg font-bold text-emerald-600 dark:text-emerald-400 mt-0.5">${{ number_format($totalVendido, 2) }}</p>
                <p class="text-[10px] text-slate-400 mt-0.5">En el período</p>
            </div>
            <div class="w-9 h-9 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-xl p-3.5 border border-slate-300 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Ventas Realizadas</p>
                <p class="text-lg font-bold text-slate-900 dark:text-white mt-0.5">{{ number_format($cantidadVentas) }}</p>
                <p class="text-[10px] text-slate-400 mt-0.5">Operaciones completadas</p>
            </div>
            <div class="w-9 h-9 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-xl p-3.5 border border-slate-300 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Ticket Promedio</p>
                <p class="text-lg font-bold text-slate-900 dark:text-white mt-0.5">${{ number_format($ticketPromedio, 2) }}</p>
                <p class="text-[10px] text-slate-400 mt-0.5">Por transacción</p>
            </div>
            <div class="w-9 h-9 rounded-lg bg-teal-50 dark:bg-teal-950/60 text-teal-600 dark:text-teal-400 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-xl p-3.5 border border-slate-300 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Métodos de Pago</p>
                <p class="text-lg font-bold text-slate-900 dark:text-white mt-0.5">{{ $ventasPorMetodo->count() }}</p>
                <p class="text-[10px] text-slate-400 mt-0.5">Formas distintas</p>
            </div>
            <div class="w-9 h-9 rounded-lg bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
            </div>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-300 dark:border-slate-800 shadow-xs print:hidden">
        <form method="GET" action="{{ route('reportes.ventas') }}" class="flex flex-wrap items-end gap-3">
            <div>
                <label for="rv_desde" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Desde</label>
                <input type="date" id="rv_desde" name="fecha_desde" value="{{ $fechaDesde }}"
                       class="px-3 py-1.5 rounded-xl text-xs border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-400">
            </div>
            <div>
                <label for="rv_hasta" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Hasta</label>
                <input type="date" id="rv_hasta" name="fecha_hasta" value="{{ $fechaHasta }}"
                       class="px-3 py-1.5 rounded-xl text-xs border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-400">
            </div>
            <div>
                <label for="rv_pago" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Pago</label>
                <select id="rv_pago" name="metodo_pago" class="px-3 py-1.5 rounded-xl text-xs border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-400">
                    <option value="">Todos</option>
                    <option value="efectivo" {{ $metodoPago === 'efectivo' ? 'selected' : '' }}>Efectivo</option>
                    <option value="tarjeta" {{ $metodoPago === 'tarjeta' ? 'selected' : '' }}>Tarjeta</option>
                    <option value="transferencia" {{ $metodoPago === 'transferencia' ? 'selected' : '' }}>Transferencia</option>
                </select>
            </div>
            <div>
                <label for="rv_cajero" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Cajero</label>
                <select id="rv_cajero" name="cajero_id" class="px-3 py-1.5 rounded-xl text-xs border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-400">
                    <option value="">Todos</option>
                    @foreach($cajeros as $c)
                    <option value="{{ $c->id }}" {{ $cajeroId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            {{-- Presets --}}
            <div class="flex gap-1.5 flex-wrap">
                @foreach([['Hoy','hoy'],['7 días','7dias'],['Este mes','mes'],['Mes ant.','mesant'],['Este año','anio']] as [$lbl,$preset])
                <button type="button" @click="setDates('{{ $preset }}')"
                        class="px-2.5 py-1.5 rounded-xl text-xs font-bold border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:border-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-400 transition">
                    {{ $lbl }}
                </button>
                @endforeach
            </div>
            <button type="submit"
                    class="px-4 py-1.5 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm transition">
                Filtrar
            </button>
            @if(request()->hasAny(['fecha_desde','fecha_hasta','metodo_pago','cajero_id']))
            <a href="{{ route('reportes.ventas') }}"
               class="px-3 py-1.5 rounded-xl text-xs font-bold border border-slate-300 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                Limpiar
            </a>
            @endif
        </form>
    </div>

    {{-- Desglose por método de pago + Top productos (2 columnas) --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        {{-- Desglose por método --}}
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-300 dark:border-slate-800 shadow-xs p-5">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white border-b border-slate-100 dark:border-slate-800 pb-3 mb-4">Desglose por Método de Pago</h3>
            @forelse($ventasPorMetodo as $m)
            @php
                $pct = $totalVendido > 0 ? round(($m->total / $totalVendido) * 100, 1) : 0;
                $barColor = match($m->metodo_pago) { 'efectivo' => 'bg-emerald-500', 'tarjeta' => 'bg-blue-500', 'transferencia' => 'bg-violet-500', default => 'bg-slate-400' };
            @endphp
            <div class="mb-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700">
                <div class="flex items-center justify-between text-xs mb-1.5">
                    <span class="font-bold text-slate-800 dark:text-slate-200">{{ ucfirst($m->metodo_pago) }}</span>
                    <span class="font-bold text-slate-900 dark:text-white">${{ number_format($m->total, 2) }} <span class="font-normal text-slate-400">({{ $pct }}%)</span></span>
                </div>
                <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-1.5">
                    <div class="{{ $barColor }} h-1.5 rounded-full" style="width: {{ $pct }}%"></div>
                </div>
                <p class="text-[10px] text-slate-400 mt-1">{{ number_format($m->cantidad) }} transacciones</p>
            </div>
            @empty
            <p class="text-xs text-slate-400 text-center py-4">Sin datos para el período.</p>
            @endforelse
        </div>

        {{-- Top 10 productos --}}
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-300 dark:border-slate-800 shadow-xs p-5">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white border-b border-slate-100 dark:border-slate-800 pb-3 mb-4">Top 10 Medicamentos del Período</h3>
            @php $maxU = $topProductos->first()?->total_unidades ?? 1; @endphp
            @forelse($topProductos as $i => $p)
            <div class="mb-2">
                <div class="flex items-center justify-between text-xs mb-0.5">
                    <span class="font-semibold text-slate-700 dark:text-slate-300 truncate max-w-[180px]">
                        <span class="text-slate-400 mr-1">{{ $i+1 }}.</span>{{ $p->nombre }}
                    </span>
                    <span class="font-bold text-slate-900 dark:text-white shrink-0 ml-2">{{ number_format($p->total_unidades) }} un.</span>
                </div>
                <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-1">
                    <div class="bg-emerald-400 h-1 rounded-full" style="width: {{ $maxU > 0 ? round(($p->total_unidades/$maxU)*100) : 0 }}%"></div>
                </div>
            </div>
            @empty
            <p class="text-xs text-slate-400 text-center py-4">Sin datos para el período.</p>
            @endforelse
        </div>
    </div>

    {{-- Tabla de Ventas --}}
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-300 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="flex items-center justify-between px-5 py-3 border-b border-slate-200 dark:border-slate-800">
            <div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Detalle de Transacciones</h3>
                <p class="text-xs text-slate-400">{{ number_format($ventas->total()) }} registros</p>
            </div>
        </div>
        @if($ventas->isEmpty())
        <div class="flex flex-col items-center justify-center py-14">
            <svg class="w-10 h-10 text-slate-300 dark:text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Sin ventas en este período</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/60">
                    <tr>
                        <th class="px-4 py-2.5 text-left font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">N° Ticket</th>
                        <th class="px-4 py-2.5 text-left font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Fecha</th>
                        <th class="px-4 py-2.5 text-left font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Cliente</th>
                        <th class="px-4 py-2.5 text-left font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Cajero</th>
                        <th class="px-4 py-2.5 text-left font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Pago</th>
                        <th class="px-4 py-2.5 text-right font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total</th>
                        <th class="px-4 py-2.5 print:hidden"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($ventas as $v)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="px-4 py-2.5">
                            <span class="font-bold text-slate-800 dark:text-slate-200">#{{ str_pad($v->id, 5, '0', STR_PAD_LEFT) }}</span>
                        </td>
                        <td class="px-4 py-2.5 text-slate-600 dark:text-slate-300 whitespace-nowrap">
                            {{ $v->fecha ? \Carbon\Carbon::parse($v->fecha)->format('d/m/Y H:i') : $v->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-4 py-2.5 text-slate-600 dark:text-slate-300">{{ $v->cliente?->nombre ?? 'Público general' }}</td>
                        <td class="px-4 py-2.5 text-slate-600 dark:text-slate-300">{{ $v->usuario?->name ?? '—' }}</td>
                        <td class="px-4 py-2.5">
                            @php
                                $pyClasses = match($v->metodo_pago) {
                                    'efectivo'      => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300',
                                    'tarjeta'       => 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300',
                                    'transferencia' => 'bg-violet-100 text-violet-800 dark:bg-violet-950 dark:text-violet-300',
                                    default         => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300',
                                };
                            @endphp
                            <span class="px-2 py-0.5 rounded-full font-semibold {{ $pyClasses }}">{{ ucfirst($v->metodo_pago) }}</span>
                        </td>
                        <td class="px-4 py-2.5 text-right font-bold text-slate-900 dark:text-white">${{ number_format($v->total, 2) }}</td>
                        <td class="px-4 py-2.5 print:hidden">
                            @can('ver ventas')
                            <a href="{{ route('ventas.show', $v) }}" class="text-xs font-bold text-emerald-600 dark:text-emerald-400 hover:underline">Ver</a>
                            @endcan
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-emerald-50 dark:bg-emerald-950/20 border-t-2 border-emerald-200 dark:border-emerald-800">
                        <td colspan="5" class="px-4 py-2.5 text-xs font-bold text-emerald-700 dark:text-emerald-400 uppercase tracking-wider">
                            Total período ({{ number_format($ventas->total()) }} ventas)
                        </td>
                        <td class="px-4 py-2.5 text-right text-xs font-extrabold text-emerald-700 dark:text-emerald-400">${{ number_format($totalVendido, 2) }}</td>
                        <td class="print:hidden"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @if($ventas->hasPages())
        <div class="px-5 py-3 border-t border-slate-200 dark:border-slate-800 print:hidden">
            {{ $ventas->links() }}
        </div>
        @endif
        @endif
    </div>
</div>

<style>
@media print {
    .print\:hidden { display: none !important; }
    body { background: white !important; }
}
</style>
@endsection
