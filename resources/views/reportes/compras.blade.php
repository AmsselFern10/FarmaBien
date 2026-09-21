@extends('layouts.app')
@section('title', 'Reporte de Proveedores y Compras - FarmaBien')
@section('content')
<div class="space-y-5">

    {{-- Breadcrumb --}}
    <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('reportes.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Centro de Reportes</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-800 dark:text-slate-200 font-semibold">Proveedores y Compras</span>
    </nav>

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-1 border-b border-slate-300/80 dark:border-slate-800 print:hidden">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">Reporte de Proveedores y Compras</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Control de adquisiciones y análisis de abastecimiento por período.</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('reportes.index') }}" class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-bold transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Volver</span>
            </a>
            <button type="button" onclick="window.print()" class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-bold transition">
                <svg class="w-3.5 h-3.5 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Imprimir</span>
            </button>
            <a href="{{ route('reportes.compras', array_merge(request()->query(), ['export' => 'pdf'])) }}" target="_blank"
               class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white rounded-xl text-xs font-bold shadow-sm transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                <span>Exportar PDF</span>
            </a>
            <a href="{{ route('reportes.compras', array_merge(request()->query(), ['export' => 'excel'])) }}"
               class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-emerald-700 hover:bg-emerald-800 active:bg-emerald-900 text-white rounded-xl text-xs font-bold shadow-sm transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Exportar Excel</span>
            </a>
            <a href="{{ route('reportes.compras', array_merge(request()->query(), ['export' => 'csv'])) }}"
               class="inline-flex items-center space-x-1.5 px-3.5 py-1.5 bg-slate-700 hover:bg-slate-800 active:bg-slate-900 text-white rounded-xl text-xs font-bold shadow-sm transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <span>Exportar CSV</span>
            </a>
        </div>
    </div>

    {{-- Print header institucional --}}
    <div class="hidden print:block border-b-2 border-emerald-600 pb-3 mb-4">
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-xl font-bold text-emerald-700 uppercase tracking-wide">FARMABIEN</h1>
                <p class="text-xs text-slate-600">Farmacia & Droguería FarmaBien C.A. · Sistema de Gestión Farmacéutica</p>
                <p class="text-[10px] text-slate-500 mt-0.5"><strong>RIF / RUC:</strong> J-40892154-0 &bull; <strong>Teléfono:</strong> (0212) 555-0199 / +58 412-1234567</p>
                <p class="text-[10px] text-slate-500"><strong>Dirección:</strong> Av. Principal Los Próceres, Edif. FarmaBien, Caracas - Venezuela</p>
            </div>
            <div class="text-right">
                <div class="inline-block border border-emerald-600 bg-emerald-50 px-3 py-1.5 rounded text-center">
                    <p class="text-xs font-bold text-emerald-800">REPORTE DE PROVEEDORES Y COMPRAS</p>
                    <p class="text-[9px] text-emerald-700 mt-0.5">Emisión: {{ now()->format('d/m/Y H:i') }}</p>
                    <p class="text-[9px] text-emerald-700">Por: {{ Auth::user()->name ?? 'Sistema' }}</p>
                </div>
            </div>
        </div>
        <div class="mt-2 text-xs bg-slate-100 p-2 rounded flex items-center justify-between">
            <span><strong>Período:</strong> {{ \Carbon\Carbon::parse($fechaDesde)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($fechaHasta)->format('d/m/Y') }}</span>
            <span><strong>Total Comprado:</strong> ${{ number_format($totalComprado, 2) }}</span>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-300 dark:border-slate-800 shadow-xs print:hidden">
        <form method="GET" action="{{ route('reportes.compras') }}" class="flex flex-wrap items-end gap-3">
            <div>
                <label for="rc_desde" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Desde</label>
                <input type="date" id="rc_desde" name="fecha_desde" value="{{ $fechaDesde }}"
                       class="px-3 py-1.5 rounded-xl text-xs border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-400">
            </div>
            <div>
                <label for="rc_hasta" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Hasta</label>
                <input type="date" id="rc_hasta" name="fecha_hasta" value="{{ $fechaHasta }}"
                       class="px-3 py-1.5 rounded-xl text-xs border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-400">
            </div>
            @php
                $presets2 = [
                    ['Hoy', now()->toDateString(), now()->toDateString()],
                    ['7 días', now()->subDays(6)->toDateString(), now()->toDateString()],
                    ['Este mes', now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()],
                    ['Mes ant.', now()->subMonth()->startOfMonth()->toDateString(), now()->subMonth()->endOfMonth()->toDateString()]
                ];
            @endphp
            @foreach($presets2 as [$lbl2,$d1,$d2])
            <a href="{{ route('reportes.compras', ['fecha_desde'=>$d1,'fecha_hasta'=>$d2]) }}"
               class="px-2.5 py-1.5 rounded-xl text-xs font-bold border transition {{ ($fechaDesde===$d1 && $fechaHasta===$d2) ? 'bg-amber-600 text-white border-amber-600 dark:bg-amber-600' : 'border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:border-amber-400 hover:text-amber-600 dark:hover:text-amber-400' }}">{{ $lbl2 }}</a>
            @endforeach
            <button type="submit" class="px-4 py-1.5 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm transition">Filtrar</button>
            @if(request()->hasAny(['fecha_desde','fecha_hasta']))
            <a href="{{ route('reportes.compras') }}" class="px-3 py-1.5 rounded-xl text-xs font-bold border border-slate-300 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition">Limpiar</a>
            @endif
        </form>
    </div>

    {{-- KPI Cards --}}
    @php
        $cantOrdenes = $compras->total();
        $promedioOrden = $cantOrdenes > 0 ? $totalComprado / $cantOrdenes : 0;
        $proveedorLider = \App\Models\Compra::whereBetween(\Illuminate\Support\Facades\DB::raw('DATE(fecha)'), [$fechaDesde, $fechaHasta])->recibidas()->join('proveedores','compras.proveedor_id','=','proveedores.id')->select('proveedores.nombre', \Illuminate\Support\Facades\DB::raw('SUM(compras.total) as monto_total'))->groupBy('proveedores.id','proveedores.nombre')->orderByDesc('monto_total')->first();
    @endphp
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="bg-white dark:bg-slate-900 rounded-xl p-3.5 border border-slate-300 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div><p class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Total Comprado</p><p class="text-lg font-bold text-amber-600 dark:text-amber-400 mt-0.5">${{ number_format($totalComprado, 2) }}</p><p class="text-[10px] text-slate-400 mt-0.5">Período</p></div>
            <div class="w-9 h-9 rounded-lg bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg></div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-xl p-3.5 border border-slate-300 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div><p class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Órdenes</p><p class="text-lg font-bold text-slate-900 dark:text-white mt-0.5">{{ number_format($cantOrdenes) }}</p><p class="text-[10px] text-slate-400 mt-0.5">Recibidas</p></div>
            <div class="w-9 h-9 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg></div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-xl p-3.5 border border-slate-300 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div><p class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Proveedor Líder</p><p class="text-sm font-bold text-slate-900 dark:text-white mt-0.5 truncate max-w-[120px]">{{ $proveedorLider?->nombre ?? '—' }}</p><p class="text-[10px] text-slate-400 mt-0.5">{{ $proveedorLider ? '$'.number_format($proveedorLider->monto_total,2) : 'Sin datos' }}</p></div>
            <div class="w-9 h-9 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg></div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-xl p-3.5 border border-slate-300 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div><p class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Prom. por Orden</p><p class="text-lg font-bold text-slate-900 dark:text-white mt-0.5">${{ number_format($promedioOrden, 2) }}</p><p class="text-[10px] text-slate-400 mt-0.5">Ticket promedio</p></div>
            <div class="w-9 h-9 rounded-lg bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg></div>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-300 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="flex items-center justify-between px-5 py-3 border-b border-slate-200 dark:border-slate-800">
            <div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Órdenes de Compra Recibidas</h3>
                <p class="text-xs text-slate-400">{{ number_format($compras->total()) }} registros · {{ \Carbon\Carbon::parse($fechaDesde)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($fechaHasta)->format('d/m/Y') }}</p>
            </div>
        </div>
        @if($compras->isEmpty())
        <div class="flex flex-col items-center justify-center py-14">
            <svg class="w-10 h-10 text-slate-300 dark:text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Sin compras en este período</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/60">
                    <tr>
                        <th class="px-4 py-2.5 text-left font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">N° Orden</th>
                        <th class="px-4 py-2.5 text-left font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Proveedor</th>
                        <th class="px-4 py-2.5 text-left font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Factura</th>
                        <th class="px-4 py-2.5 text-left font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Fecha</th>
                        <th class="px-4 py-2.5 text-left font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Responsable</th>
                        <th class="px-4 py-2.5 text-left font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Estado</th>
                        <th class="px-4 py-2.5 text-right font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total</th>
                        <th class="px-4 py-2.5 print:hidden"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($compras as $c)
                    @php
                        $stClass = match($c->estado ?? '') {
                            'recibida'  => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300',
                            'pendiente' => 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300',
                            'parcial'   => 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300',
                            'cancelada' => 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300',
                            default     => 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300'
                        };
                    @endphp
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="px-4 py-2.5 font-bold text-slate-800 dark:text-slate-200">#{{ str_pad($c->id,4,'0',STR_PAD_LEFT) }}</td>
                        <td class="px-4 py-2.5"><p class="font-semibold text-slate-800 dark:text-slate-200">{{ $c->proveedor?->nombre ?? '—' }}</p><p class="text-slate-400">{{ $c->proveedor?->rif ?? '' }}</p></td>
                        <td class="px-4 py-2.5 text-slate-600 dark:text-slate-300">{{ $c->numero_factura ?? '—' }}</td>
                        <td class="px-4 py-2.5 text-slate-600 dark:text-slate-300 whitespace-nowrap">{{ $c->fecha ? \Carbon\Carbon::parse($c->fecha)->format('d/m/Y') : '—' }}</td>
                        <td class="px-4 py-2.5 text-slate-600 dark:text-slate-300">{{ $c->usuario?->name ?? 'Sistema' }}</td>
                        <td class="px-4 py-2.5"><span class="px-2 py-0.5 rounded-full font-semibold {{ $stClass }}">{{ ucfirst($c->estado ?? 'N/A') }}</span></td>
                        <td class="px-4 py-2.5 text-right font-bold text-slate-900 dark:text-white">${{ number_format($c->total, 2) }}</td>
                        <td class="px-4 py-2.5 print:hidden">
                            @can('ver compras')
                            <a href="{{ route('compras.show', $c) }}" class="text-xs font-bold text-amber-600 dark:text-amber-400 hover:underline">Ver</a>
                            @endcan
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-slate-100 dark:bg-slate-800/90 border-t border-slate-300 dark:border-slate-700">
                        <td colspan="6" class="px-4 py-2.5 text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">Total ({{ number_format($compras->total()) }} órdenes)</td>
                        <td class="px-4 py-2.5 text-right text-xs font-extrabold text-amber-600 dark:text-amber-400">${{ number_format($totalComprado, 2) }}</td>
                        <td class="print:hidden"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @if($compras->hasPages())
        <div class="px-5 py-3 border-t border-slate-200 dark:border-slate-800 print:hidden">{{ $compras->links() }}</div>
        @endif
        @endif
    </div>
</div>
<style>@media print { .print\:hidden { display: none !important; } }</style>
@endsection
