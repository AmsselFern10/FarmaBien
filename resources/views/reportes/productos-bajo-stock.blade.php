@extends('layouts.app')
@section('title', 'Stock Mínimo - Alertas de Reposición - FarmaBien')
@section('content')
<div class="space-y-5">

    <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('reportes.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Centro de Reportes</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-800 dark:text-slate-200 font-semibold">Stock Mínimo</span>
    </nav>

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-1 border-b border-slate-300/80 dark:border-slate-800 print:hidden">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">Alertas de Stock Mínimo</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Productos con stock actual por debajo del nivel mínimo configurado.</p>
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
            <a href="{{ route('reportes.productos-bajo-stock', ['export' => 'pdf']) }}" target="_blank"
               class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white rounded-xl text-xs font-bold shadow-sm transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                <span>Exportar PDF</span>
            </a>
            <a href="{{ route('reportes.productos-bajo-stock', ['export' => 'excel']) }}"
               class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-emerald-700 hover:bg-emerald-800 active:bg-emerald-900 text-white rounded-xl text-xs font-bold shadow-sm transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Exportar Excel</span>
            </a>
            <a href="{{ route('reportes.productos-bajo-stock', ['export' => 'csv']) }}"
               class="inline-flex items-center space-x-1.5 px-3.5 py-1.5 bg-slate-700 hover:bg-slate-800 active:bg-slate-900 text-white rounded-xl text-xs font-bold shadow-sm transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <span>Exportar CSV</span>
            </a>
            @can('gestionar compras')
            <a href="{{ route('compras.create') }}" class="inline-flex items-center space-x-1.5 px-3.5 py-1.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold shadow-sm transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Nueva Orden</span>
            </a>
            @endcan
        </div>
    </div>

    {{-- Print header institucional --}}
    <div class="hidden print:block border-b-2 border-rose-600 pb-3 mb-4">
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-xl font-bold text-emerald-700 uppercase tracking-wide">FARMABIEN</h1>
                <p class="text-xs text-slate-600">Farmacia & Droguería FarmaBien C.A. · Sistema de Gestión Farmacéutica</p>
                <p class="text-[10px] text-slate-500 mt-0.5"><strong>RIF / RUC:</strong> J-40892154-0 &bull; <strong>Teléfono:</strong> (0212) 555-0199 / +58 412-1234567</p>
                <p class="text-[10px] text-slate-500"><strong>Dirección:</strong> Av. Principal Los Próceres, Edif. FarmaBien, Caracas - Venezuela</p>
            </div>
            <div class="text-right">
                <div class="inline-block border border-rose-600 bg-rose-50 px-3 py-1.5 rounded text-center">
                    <p class="text-xs font-bold text-rose-800">ALERTAS DE STOCK MÍNIMO</p>
                    <p class="text-[9px] text-rose-700 mt-0.5">Emisión: {{ now()->format('d/m/Y H:i') }}</p>
                    <p class="text-[9px] text-rose-700">Por: {{ Auth::user()->name ?? 'Sistema' }}</p>
                </div>
            </div>
        </div>
    </div>

    @php
        $criticos = collect($productos)->filter(fn($p) => ($p->stock_disponible ?? 0) === 0);
        $bajos = collect($productos)->filter(fn($p) => ($p->stock_disponible ?? 0) > 0);
        $total = count($productos);
    @endphp

    @if($criticos->count() > 0)
    <div class="p-3 rounded-xl bg-rose-50 dark:bg-rose-950/60 border border-rose-300 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-xs flex items-start gap-3">
        <svg class="w-4 h-4 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        <div><p class="font-bold">{{ $criticos->count() }} {{ $criticos->count()===1?'medicamento agotado':'medicamentos agotados' }} — Stock en cero</p><p class="text-rose-600 dark:text-rose-400 mt-0.5">Requieren reposición inmediata. No pueden venderse.</p></div>
    </div>
    @endif

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="bg-white dark:bg-slate-900 rounded-xl p-3.5 border border-rose-300 dark:border-rose-800/50 shadow-xs flex items-center justify-between">
            <div><p class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Bajo Mínimo</p><p class="text-lg font-bold text-rose-700 dark:text-rose-400 mt-0.5">{{ $total }}</p><p class="text-[10px] text-slate-400 mt-0.5">Productos afectados</p></div>
            <div class="w-9 h-9 rounded-lg bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/></svg></div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-xl p-3.5 border border-rose-300 dark:border-rose-800/50 shadow-xs flex items-center justify-between">
            <div><p class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Agotados</p><p class="text-lg font-bold text-rose-700 dark:text-rose-400 mt-0.5">{{ $criticos->count() }}</p><p class="text-[10px] text-slate-400 mt-0.5">Stock = 0</p></div>
            <div class="w-9 h-9 rounded-lg bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-xl p-3.5 border border-amber-300 dark:border-amber-800/50 shadow-xs flex items-center justify-between">
            <div><p class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Stock Bajo</p><p class="text-lg font-bold text-amber-600 dark:text-amber-400 mt-0.5">{{ $bajos->count() }}</p><p class="text-[10px] text-slate-400 mt-0.5">Por debajo del mínimo</p></div>
            <div class="w-9 h-9 rounded-lg bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg></div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-xl p-3.5 border border-slate-300 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div><p class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Actualizado</p><p class="text-lg font-bold text-slate-900 dark:text-white mt-0.5">{{ now()->format('d/m/Y') }}</p><p class="text-[10px] text-slate-400 mt-0.5">{{ now()->format('H:i') }} — En tiempo real</p></div>
            <div class="w-9 h-9 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 flex items-center justify-center shrink-0"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-300 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="flex items-center justify-between px-5 py-3 border-b border-slate-200 dark:border-slate-800">
            <div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Productos con Stock Crítico</h3>
                <p class="text-xs text-slate-400">{{ $total }} {{ $total===1?'producto requiere':'productos requieren' }} atención</p>
            </div>
        </div>
        @if(empty($productos) || count($productos) === 0)
        <div class="flex flex-col items-center justify-center py-14">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 flex items-center justify-center mb-3"><svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
            <p class="text-sm font-bold text-emerald-700 dark:text-emerald-400">¡Inventario saludable!</p>
            <p class="text-xs text-slate-400 mt-1">Todos los productos están sobre el mínimo.</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/60">
                    <tr>
                        <th class="px-4 py-2.5 text-left font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Estado</th>
                        <th class="px-4 py-2.5 text-left font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Medicamento</th>
                        <th class="px-4 py-2.5 text-left font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Categoría</th>
                        <th class="px-4 py-2.5 text-center font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Stock Actual</th>
                        <th class="px-4 py-2.5 text-center font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Mínimo</th>
                        <th class="px-4 py-2.5 text-center font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Déficit</th>
                        <th class="px-4 py-2.5 print:hidden"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($productos as $p)
                    @php $sa=$p->stock_disponible??0; $sm=$p->stock_minimo??0; $def=max(0,$sm-$sa); $agotado=$sa===0; @endphp
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors {{ $agotado ? 'bg-rose-50/30 dark:bg-rose-950/20' : '' }}">
                        <td class="px-4 py-2.5">
                            @if($agotado)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300"><span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>Agotado</span>
                            @else
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300">Stock bajo</span>
                            @endif
                        </td>
                        <td class="px-4 py-2.5"><p class="font-semibold text-slate-800 dark:text-slate-200">{{ $p->nombre }}</p><p class="text-slate-400">{{ $p->principio_activo ?? '' }}</p></td>
                        <td class="px-4 py-2.5 text-slate-500 dark:text-slate-400">{{ $p->categoria?->nombre ?? '—' }}</td>
                        <td class="px-4 py-2.5 text-center font-bold {{ $agotado ? 'text-rose-700 dark:text-rose-400' : 'text-amber-600 dark:text-amber-400' }}">{{ number_format($sa) }}</td>
                        <td class="px-4 py-2.5 text-center font-semibold text-slate-600 dark:text-slate-300">{{ number_format($sm) }}</td>
                        <td class="px-4 py-2.5 text-center font-bold text-rose-700 dark:text-rose-400">−{{ number_format($def) }}</td>
                        <td class="px-4 py-2.5 print:hidden">
                            <div class="flex items-center gap-1.5">
                                @can('ver productos')
                                <a href="{{ route('productos.show', $p) }}" class="text-xs font-bold text-slate-600 dark:text-slate-400 hover:underline">Ver</a>
                                @endcan
                                @can('gestionar compras')
                                <span class="text-slate-300 dark:text-slate-600">·</span>
                                <a href="{{ route('compras.create') }}" class="text-xs font-bold text-rose-600 dark:text-rose-400 hover:underline">Reponer</a>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-slate-100 dark:bg-slate-800/90 border-t border-slate-300 dark:border-slate-700">
                        <td colspan="3" class="px-4 py-2.5 text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">
                            Resumen ({{ $criticos->count() }} agotados · {{ $bajos->count() }} bajo mínimo)
                        </td>
                        <td class="px-4 py-2.5 text-center text-xs font-extrabold text-slate-800 dark:text-slate-200">{{ number_format(collect($productos)->sum('stock_disponible')) }}</td>
                        <td class="px-4 py-2.5 text-center text-xs font-extrabold text-slate-600 dark:text-slate-300">{{ number_format(collect($productos)->sum('stock_minimo')) }}</td>
                        <td class="px-4 py-2.5 text-center text-xs font-extrabold text-rose-600 dark:text-rose-400">−{{ number_format(collect($productos)->sum(fn($p)=>max(0,($p->stock_minimo??0)-($p->stock_disponible??0)))) }}</td>
                        <td class="print:hidden"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @endif
    </div>
</div>
<style>@media print { .print\:hidden { display: none !important; } .animate-pulse { animation: none !important; } }</style>
@endsection
