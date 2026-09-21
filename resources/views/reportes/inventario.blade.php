@extends('layouts.app')
@section('title', 'Reporte de Inventario y Caducidad - FarmaBien')
@section('content')
<div class="space-y-5">

    {{-- Breadcrumb --}}
    <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('reportes.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Centro de Reportes</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-800 dark:text-slate-200 font-semibold">Inventario y Caducidad</span>
    </nav>

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-1 border-b border-slate-300/80 dark:border-slate-800 print:hidden">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">Inventario, Valorización y Caducidad</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Semáforo PEPS, valor a costo y venta proyectada, control por lote.</p>
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
            <a href="{{ route('reportes.inventario', array_merge(request()->query(), ['export' => 'pdf'])) }}" target="_blank"
               class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white rounded-xl text-xs font-bold shadow-sm transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                <span>Exportar PDF</span>
            </a>
            <a href="{{ route('reportes.inventario', array_merge(request()->query(), ['export' => 'csv'])) }}"
               class="inline-flex items-center space-x-1.5 px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white rounded-xl text-xs font-bold shadow-sm transition">
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
                    <p class="text-xs font-bold text-emerald-800">VALORIZACIÓN DE INVENTARIO Y CADUCIDAD</p>
                    <p class="text-[9px] text-emerald-700 mt-0.5">Emisión: {{ now()->format('d/m/Y H:i') }}</p>
                    <p class="text-[9px] text-emerald-700">Por: {{ Auth::user()->name ?? 'Sistema' }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Semáforo de Caducidad (clickable filters) --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2">
        @php
            $semaforos = [
                ['vencidos',    $semVencidos,    'Vencidos',        'bg-rose-50 dark:bg-rose-950/40 border-rose-300 dark:border-rose-800 text-rose-700 dark:text-rose-400', 'rose'],
                ['critico_30',  $semCritico30,   '≤ 30 días',       'bg-amber-50 dark:bg-amber-950/40 border-amber-300 dark:border-amber-800 text-amber-700 dark:text-amber-400', 'amber'],
                ['alerta_60',   $semAlerta60,    '31–60 días',      'bg-yellow-50 dark:bg-yellow-950/40 border-yellow-300 dark:border-yellow-800 text-yellow-700 dark:text-yellow-400', 'yellow'],
                ['preventivo_90',$semPreventivo90,'61–90 días',     'bg-blue-50 dark:bg-blue-950/40 border-blue-300 dark:border-blue-800 text-blue-700 dark:text-blue-400', 'blue'],
                ['vigentes',    $semVigentes,    '> 90 días',       'bg-emerald-50 dark:bg-emerald-950/40 border-emerald-300 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400', 'emerald'],
            ];
        @endphp
        @foreach($semaforos as [$val, $count, $label, $classes, $color])
        <a href="{{ route('reportes.inventario', array_merge(request()->except('estado_vencimiento', 'page'), ['estado_vencimiento' => $estadoVencimiento === $val ? 'todos' : $val])) }}"
           class="rounded-xl p-3 border text-center transition hover:shadow-sm {{ $classes }} {{ $estadoVencimiento === $val ? 'ring-2 ring-offset-1 ring-current dark:ring-offset-slate-900' : '' }}">
            <div class="text-2xl font-extrabold">{{ $count }}</div>
            <div class="text-[10px] font-bold uppercase tracking-wider mt-0.5">{{ $label }}</div>
        </a>
        @endforeach
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="bg-white dark:bg-slate-900 rounded-xl p-3.5 border border-slate-300 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Valor a Costo</p>
                <p class="text-lg font-bold text-indigo-600 dark:text-indigo-400 mt-0.5">${{ number_format($totalValorCosto, 2) }}</p>
                <p class="text-[10px] text-slate-400 mt-0.5">Inventario filtrado</p>
            </div>
            <div class="w-9 h-9 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-xl p-3.5 border border-slate-300 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Valor Venta Proy.</p>
                <p class="text-lg font-bold text-emerald-600 dark:text-emerald-400 mt-0.5">${{ number_format($totalValorVenta, 2) }}</p>
                <p class="text-[10px] text-slate-400 mt-0.5">A precio de venta</p>
            </div>
            <div class="w-9 h-9 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-xl p-3.5 border border-slate-300 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Unidades en Stock</p>
                <p class="text-lg font-bold text-slate-900 dark:text-white mt-0.5">{{ number_format($totalUnidadesStock) }}</p>
                <p class="text-[10px] text-slate-400 mt-0.5">Total unidades</p>
            </div>
            <div class="w-9 h-9 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-xl p-3.5 border border-slate-300 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Margen Proyectado</p>
                <p class="text-lg font-bold {{ $margenProyectado > 20 ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400' }} mt-0.5">{{ $margenProyectado }}%</p>
                <p class="text-[10px] text-slate-400 mt-0.5">Sobre costo</p>
            </div>
            <div class="w-9 h-9 rounded-lg bg-teal-50 dark:bg-teal-950/60 text-teal-600 dark:text-teal-400 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </div>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-300 dark:border-slate-800 shadow-xs print:hidden">
        <form method="GET" action="{{ route('reportes.inventario') }}" class="flex flex-wrap items-end gap-3">
            <div>
                <label for="ri_buscar" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Buscar</label>
                <input type="text" id="ri_buscar" name="buscar" value="{{ $buscar }}" placeholder="Medicamento, lote, principio..."
                       class="px-3 py-1.5 rounded-xl text-xs border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-400 w-52">
            </div>
            <div>
                <label for="ri_cat" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Categoría</label>
                <select id="ri_cat" name="categoria_id" class="px-3 py-1.5 rounded-xl text-xs border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-400">
                    <option value="">Todas</option>
                    @foreach($categorias as $cat)
                    <option value="{{ $cat->id }}" {{ $categoriaId == $cat->id ? 'selected' : '' }}>{{ $cat->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="ri_lab" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Laboratorio</label>
                <select id="ri_lab" name="laboratorio_id" class="px-3 py-1.5 rounded-xl text-xs border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-400">
                    <option value="">Todos</option>
                    @foreach($laboratorios as $lab)
                    <option value="{{ $lab->id }}" {{ $laboratorioId == $lab->id ? 'selected' : '' }}>{{ $lab->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="ri_stock" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Stock</label>
                <select id="ri_stock" name="estado_stock" class="px-3 py-1.5 rounded-xl text-xs border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-400">
                    <option value="todos" {{ $estadoStock === 'todos' ? 'selected' : '' }}>Todos</option>
                    <option value="disponible" {{ $estadoStock === 'disponible' ? 'selected' : '' }}>Con stock</option>
                    <option value="agotado" {{ $estadoStock === 'agotado' ? 'selected' : '' }}>Agotados</option>
                    <option value="bajo_stock" {{ $estadoStock === 'bajo_stock' ? 'selected' : '' }}>Bajo mínimo</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-1.5 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm transition">Filtrar</button>
            @if(request()->hasAny(['buscar','categoria_id','laboratorio_id','estado_stock','estado_vencimiento']))
            <a href="{{ route('reportes.inventario') }}" class="px-3 py-1.5 rounded-xl text-xs font-bold border border-slate-300 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition">Limpiar</a>
            @endif
        </form>
    </div>

    {{-- Tabla de Lotes --}}
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-300 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="flex items-center justify-between px-5 py-3 border-b border-slate-200 dark:border-slate-800">
            <div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Lotes Activos de Inventario</h3>
                <p class="text-xs text-slate-400">{{ number_format($lotes->total()) }} lotes — ordenados por proximidad a vencer</p>
            </div>
        </div>
        @if($lotes->isEmpty())
        <div class="flex flex-col items-center justify-center py-14">
            <svg class="w-10 h-10 text-slate-300 dark:text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Sin lotes que coincidan con los filtros</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/60">
                    <tr>
                        <th class="px-4 py-2.5 text-left font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Lote</th>
                        <th class="px-4 py-2.5 text-left font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Medicamento</th>
                        <th class="px-4 py-2.5 text-left font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Vencimiento</th>
                        <th class="px-4 py-2.5 text-left font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Estado</th>
                        <th class="px-4 py-2.5 text-right font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Stock</th>
                        <th class="px-4 py-2.5 text-right font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Costo</th>
                        <th class="px-4 py-2.5 text-right font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Venta Proy.</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($lotes as $l)
                    @php
                        $dias = (int) now()->diffInDays($l->fecha_vencimiento, false);
                        [$sem, $semClass] = $dias < 0
                            ? ['VENCIDO', 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300']
                            : ($dias <= 30
                                ? ['≤ 30d', 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300']
                                : ($dias <= 60
                                    ? ['31-60d', 'bg-yellow-100 text-yellow-800 dark:bg-yellow-950 dark:text-yellow-300']
                                    : ($dias <= 90
                                        ? ['61-90d', 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300']
                                        : ['Vigente', 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300'])));
                        $valCosto = round($l->stock_actual * (float)$l->precio_compra, 2);
                        $valVenta = round($l->stock_actual * (float)($l->producto->precio_venta ?? 0), 2);
                    @endphp
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors {{ $dias < 0 ? 'bg-rose-50/30 dark:bg-rose-950/20' : '' }}">
                        <td class="px-4 py-2.5"><span class="font-mono font-bold text-slate-700 dark:text-slate-300">{{ $l->numero_lote }}</span></td>
                        <td class="px-4 py-2.5">
                            <p class="font-semibold text-slate-800 dark:text-slate-200">{{ $l->producto->nombre ?? 'N/A' }}</p>
                            <p class="text-slate-400">{{ $l->producto->laboratorio->nombre ?? '' }}</p>
                        </td>
                        <td class="px-4 py-2.5 text-slate-600 dark:text-slate-300 whitespace-nowrap">
                            {{ $l->fecha_vencimiento?->format('d/m/Y') ?? '—' }}
                            <span class="block text-[10px] text-slate-400">{{ $dias >= 0 ? "En {$dias} días" : abs($dias).' días vencido' }}</span>
                        </td>
                        <td class="px-4 py-2.5">
                            <span class="px-2 py-0.5 rounded-full font-semibold {{ $semClass }}">{{ $sem }}</span>
                        </td>
                        <td class="px-4 py-2.5 text-right font-bold text-slate-800 dark:text-slate-200">{{ number_format($l->stock_actual) }}</td>
                        <td class="px-4 py-2.5 text-right text-slate-600 dark:text-slate-300">${{ number_format($valCosto, 2) }}</td>
                        <td class="px-4 py-2.5 text-right font-semibold text-emerald-600 dark:text-emerald-400">${{ number_format($valVenta, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-slate-100 dark:bg-slate-800/90 border-t border-slate-300 dark:border-slate-700">
                        <td colspan="5" class="px-4 py-2.5 text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">Total ({{ number_format($lotes->total()) }} lotes)</td>
                        <td class="px-4 py-2.5 text-right text-xs font-extrabold text-indigo-600 dark:text-indigo-400">${{ number_format($totalValorCosto, 2) }}</td>
                        <td class="px-4 py-2.5 text-right text-xs font-extrabold text-emerald-600 dark:text-emerald-400">${{ number_format($totalValorVenta, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @if($lotes->hasPages())
        <div class="px-5 py-3 border-t border-slate-200 dark:border-slate-800 print:hidden">
            {{ $lotes->links() }}
        </div>
        @endif
        @endif
    </div>
</div>
<style>@media print { .print\:hidden { display: none !important; } }</style>
@endsection
