@extends('layouts.app')

@section('title', 'Top Medicamentos Más Vendidos - FarmaBien')

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 print:hidden">
        <div>
            <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
                <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <a href="{{ route('reportes.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition font-medium">Centro de Reportes</a>
                <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="text-slate-800 dark:text-slate-200 font-semibold">Top Medicamentos</span>
            </nav>
            <div class="flex items-center space-x-2">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                    Ranking de Ventas
                </span>
                <span class="text-xs text-slate-400">&bull;</span>
                <span class="text-xs text-slate-500 dark:text-slate-400">Top 20 por unidades vendidas</span>
            </div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white mt-1">Medicamentos Más Vendidos</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Ranking por unidades despachadas e ingresos generados en el período seleccionado.
            </p>
        </div>

        <div class="flex items-center space-x-2 flex-wrap">
            <button onclick="window.print()"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:border-slate-400 transition-colors shadow-sm print:hidden">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Imprimir
            </button>
        </div>
    </div>

    <!-- Filtro de Período -->
    <div class="bg-white dark:bg-slate-800/60 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-4 print:hidden">
        <form method="GET" action="{{ route('reportes.productos-mas-vendidos') }}" class="flex flex-wrap items-end gap-3">
            <div>
                <label for="pmv_desde" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Desde</label>
                <input type="date" id="pmv_desde" name="fecha_desde" value="{{ $fechaDesde }}"
                       class="px-3 py-1.5 rounded-lg text-xs border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-400">
            </div>
            <div>
                <label for="pmv_hasta" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Hasta</label>
                <input type="date" id="pmv_hasta" name="fecha_hasta" value="{{ $fechaHasta }}"
                       class="px-3 py-1.5 rounded-lg text-xs border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-400">
            </div>
            @php
                $presets = [
                    'hoy'    => ['Hoy',         now()->toDateString(),                          now()->toDateString()],
                    '7d'     => ['Últ. 7 días', now()->subDays(6)->toDateString(),               now()->toDateString()],
                    'mes'    => ['Este mes',    now()->startOfMonth()->toDateString(),           now()->endOfMonth()->toDateString()],
                    'mesant' => ['Mes ant.',    now()->subMonth()->startOfMonth()->toDateString(), now()->subMonth()->endOfMonth()->toDateString()],
                    'año'    => ['Este año',    now()->startOfYear()->toDateString(),            now()->toDateString()],
                ];
            @endphp
            <div class="flex gap-1.5 flex-wrap">
                @foreach($presets as [$label, $desde, $hasta])
                    <a href="{{ route('reportes.productos-mas-vendidos', ['fecha_desde' => $desde, 'fecha_hasta' => $hasta]) }}"
                       class="px-2.5 py-1.5 rounded-lg text-xs font-medium border transition-colors
                              {{ ($fechaDesde === $desde && $fechaHasta === $hasta) ? 'bg-emerald-500 text-white border-emerald-500' : 'bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:border-emerald-400' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
            <button type="submit"
                    class="px-4 py-1.5 rounded-lg text-xs font-semibold bg-emerald-500 hover:bg-emerald-600 text-white transition-colors shadow-sm">
                Aplicar
            </button>
        </form>
    </div>

    <!-- KPIs -->
    @php
        $top1 = $ranking->first();
        $totalUnidades = $ranking->sum('total_unidades_vendidas');
        $totalIngresos = $ranking->sum('total_ingresos');
        $maxUnidades = $ranking->max('total_unidades_vendidas') ?: 1;
    @endphp
    <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
        <!-- Top 1 -->
        <div class="bg-white dark:bg-slate-800/60 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400">🥇 Producto #1</span>
                <span class="w-8 h-8 rounded-xl bg-amber-100 dark:bg-amber-950/50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                </span>
            </div>
            <div class="text-sm font-bold text-slate-900 dark:text-white truncate">{{ $top1?->nombre ?? '—' }}</div>
            <div class="text-xs text-slate-400 mt-0.5">
                {{ $top1 ? number_format($top1->total_unidades_vendidas) . ' unidades vendidas' : 'Sin datos en el período' }}
            </div>
        </div>
        <!-- Total Unidades -->
        <div class="bg-white dark:bg-slate-800/60 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400">Unidades Despachadas</span>
                <span class="w-8 h-8 rounded-xl bg-indigo-100 dark:bg-indigo-950/50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </span>
            </div>
            <div class="text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($totalUnidades) }}</div>
            <div class="text-xs text-slate-400 mt-0.5">Top 20 medicamentos</div>
        </div>
        <!-- Ingresos Generados -->
        <div class="bg-white dark:bg-slate-800/60 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-4 col-span-2 lg:col-span-1">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400">Ingresos Generados</span>
                <span class="w-8 h-8 rounded-xl bg-emerald-100 dark:bg-emerald-950/50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <div class="text-2xl font-bold text-slate-900 dark:text-white">${{ number_format($totalIngresos, 2) }}</div>
            <div class="text-xs text-slate-400 mt-0.5">Top 20 medicamentos</div>
        </div>
    </div>

    <!-- Ranking Table -->
    <div class="bg-white dark:bg-slate-800/60 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-700">
            <h2 class="text-sm font-bold text-slate-900 dark:text-white">Ranking de Medicamentos</h2>
            <p class="text-xs text-slate-400">Período: {{ \Carbon\Carbon::parse($fechaDesde)->format('d/m/Y') }} — {{ \Carbon\Carbon::parse($fechaHasta)->format('d/m/Y') }}</p>
        </div>

        @if($ranking->isEmpty())
            <div class="flex flex-col items-center justify-center py-16 text-center">
                <div class="w-14 h-14 rounded-2xl bg-slate-100 dark:bg-slate-700 flex items-center justify-center mb-3">
                    <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
                <p class="text-sm font-semibold text-slate-600 dark:text-slate-400">Sin ventas en este período</p>
                <p class="text-xs text-slate-400 mt-1">Ajusta el rango de fechas para ver el ranking.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-900/40 text-left">
                            <th class="px-4 py-2.5 font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400 w-12">#</th>
                            <th class="px-4 py-2.5 font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400">Medicamento</th>
                            <th class="px-4 py-2.5 font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400">Principio Activo</th>
                            <th class="px-4 py-2.5 font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400 text-right">Unidades</th>
                            <th class="px-4 py-2.5 font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400 text-right">Ingresos</th>
                            <th class="px-4 py-2.5 font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400">Participación</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                        @foreach($ranking as $i => $item)
                            @php
                                $porcentaje = $maxUnidades > 0 ? round(($item->total_unidades_vendidas / $maxUnidades) * 100, 1) : 0;
                                $medalClasses = match($i) {
                                    0 => 'bg-amber-400 text-white',
                                    1 => 'bg-slate-400 text-white',
                                    2 => 'bg-orange-400 text-white',
                                    default => 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300',
                                };
                            @endphp
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/30 transition-colors">
                                <td class="px-4 py-3">
                                    <span class="w-6 h-6 rounded-lg {{ $medalClasses }} flex items-center justify-center font-extrabold text-xs">
                                        {{ $i + 1 }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-semibold text-slate-800 dark:text-slate-200">{{ $item->nombre }}</div>
                                </td>
                                <td class="px-4 py-3 text-slate-500 dark:text-slate-400">
                                    {{ $item->principio_activo ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="font-bold text-indigo-700 dark:text-indigo-400">{{ number_format($item->total_unidades_vendidas) }}</span>
                                    <span class="text-slate-400"> un.</span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="font-bold text-emerald-700 dark:text-emerald-400">${{ number_format($item->total_ingresos, 2) }}</span>
                                </td>
                                <td class="px-4 py-3 min-w-[140px]">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 bg-slate-100 dark:bg-slate-700 rounded-full h-2">
                                            <div class="h-2 rounded-full transition-all duration-500
                                                        {{ $i === 0 ? 'bg-amber-400' : ($i <= 2 ? 'bg-emerald-400' : 'bg-indigo-400') }}"
                                                 style="width: {{ $porcentaje }}%"></div>
                                        </div>
                                        <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 w-10 text-right">{{ $porcentaje }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-emerald-50 dark:bg-emerald-950/20 border-t-2 border-emerald-200 dark:border-emerald-800">
                            <td colspan="3" class="px-4 py-2.5 font-extrabold text-xs uppercase tracking-wider text-emerald-700 dark:text-emerald-400">
                                Totales (Top {{ $ranking->count() }})
                            </td>
                            <td class="px-4 py-2.5 text-right font-extrabold text-emerald-700 dark:text-emerald-400">
                                {{ number_format($totalUnidades) }} un.
                            </td>
                            <td class="px-4 py-2.5 text-right font-extrabold text-emerald-700 dark:text-emerald-400">
                                ${{ number_format($totalIngresos, 2) }}
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    </div>
</div>

<style>
@media print {
    .print\:hidden { display: none !important; }
    body { background: white !important; }
    .rounded-2xl { border-radius: 0 !important; }
    .shadow-sm { box-shadow: none !important; }
}
</style>
@endsection
