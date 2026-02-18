@extends('layouts.app')

@section('title', 'Productos Más Vendidos')

@section('header')
    Top Productos
@endsection

@section('page-actions')
    <div>
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Top Productos Más Vendidos 🏆</h2>
        <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">Ranking por unidades base e ingresos (vista previa + exportación).</p>
    </div>
    <div class="flex gap-3">
        <a href="{{ route('reportes.index') }}"
           class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-slate-700 dark:text-slate-300 font-semibold rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200 shadow-sm">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Volver
        </a>
    </div>
@endsection

@push('styles')
<style>
    .table-excel th{position:sticky; top:0; z-index:10;}
</style>
@endpush

@section('content')
@php
    $moneda = 'S/';
    $rows = collect($productos ?? []);
    $top = $rows->take(12);
    $labels = $top->pluck('nombre');
    $unidades = $top->pluck('total_vendido');
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-1 lg:px-3 py-2">

    {{-- Filtros --}}
    <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-gray-800 shadow-sm p-5 mb-4">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-3" id="formFiltrosTop">
            <input type="hidden" name="export" id="export" value="">

            <div class="md:col-span-3">
                <label class="block text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-tight">Inicio</label>
                <input type="date" name="fecha_inicio" value="{{ $fechaInicio }}"
                       class="mt-1 w-full px-3 py-2 bg-white dark:bg-gray-700 border border-slate-300 dark:border-slate-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-fuchsia-500" />
            </div>

            <div class="md:col-span-3">
                <label class="block text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-tight">Fin</label>
                <input type="date" name="fecha_fin" value="{{ $fechaFin }}" max="{{ date('Y-m-d') }}"
                       class="mt-1 w-full px-3 py-2 bg-white dark:bg-gray-700 border border-slate-300 dark:border-slate-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-fuchsia-500" />
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-tight">Límite</label>
                <select name="limite"
                        class="mt-1 w-full px-3 py-2 bg-white dark:bg-gray-700 border border-slate-300 dark:border-slate-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-fuchsia-500">
                    @foreach([10,20,50,100] as $lim)
                        <option value="{{ $lim }}" {{ (int)$limite === (int)$lim ? 'selected' : '' }}>Top {{ $lim }}</option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-4 flex items-end gap-2">
                <button type="submit" onclick="document.getElementById('export').value='';"
                        class="inline-flex w-full md:w-auto justify-center items-center px-4 py-2 bg-fuchsia-600 hover:bg-fuchsia-700 text-white font-bold rounded-lg shadow-sm">
                    Ver / Actualizar
                </button>

                <button type="button" onclick="document.getElementById('export').value='pdf'; document.getElementById('formFiltrosTop').submit();"
                        class="inline-flex w-full md:w-auto justify-center items-center px-4 py-2 bg-white dark:bg-gray-800 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 font-bold rounded-lg hover:bg-slate-50 dark:hover:bg-gray-700 shadow-sm">
                    PDF
                </button>

                <button type="button" onclick="document.getElementById('export').value='excel'; document.getElementById('formFiltrosTop').submit();"
                        class="inline-flex w-full md:w-auto justify-center items-center px-4 py-2 bg-white dark:bg-gray-800 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 font-bold rounded-lg hover:bg-slate-50 dark:hover:bg-gray-700 shadow-sm">
                    Excel
                </button>

                @if(request()->hasAny(['fecha_inicio','fecha_fin','limite']))
                    <a href="{{ route('reportes.productosMasVendidos') }}"
                       class="inline-flex w-full md:w-auto justify-center items-center px-4 py-2 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300 font-bold rounded-lg hover:bg-rose-100 dark:hover:bg-rose-900/30 shadow-sm">
                        Limpiar
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Chart --}}
    <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-gray-800 p-5 shadow-sm mb-4">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-black text-slate-700 dark:text-slate-300 uppercase tracking-tight">Unidades vendidas (base)</h3>
            <span class="text-xs text-slate-500 dark:text-slate-400">Top 12</span>
        </div>
        <div class="mt-3 h-72">
            <canvas id="chartTop"></canvas>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-gray-800 shadow-sm">
        <div class="p-5 border-b border-slate-200 dark:border-slate-700">
            <h3 class="text-sm font-black text-slate-700 dark:text-slate-300 uppercase tracking-tight">Ranking</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Incluye categoría, ingresos y número de ventas.</p>
        </div>
        <div class="overflow-auto max-h-[520px]">
            <table class="min-w-full table-excel text-sm">
                <thead class="bg-slate-50 dark:bg-slate-900/40 border-b border-slate-200 dark:border-slate-700">
                    <tr class="text-xs text-slate-600 dark:text-slate-300 uppercase tracking-tight">
                        <th class="px-4 py-3 text-center font-black">#</th>
                        <th class="px-4 py-3 text-left font-black">Producto</th>
                        <th class="px-4 py-3 text-left font-black">Categoría</th>
                        <th class="px-4 py-3 text-right font-black">Unidades</th>
                        <th class="px-4 py-3 text-right font-black">Ingresos</th>
                        <th class="px-4 py-3 text-center font-black">Ventas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    @forelse($rows as $i => $p)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-900/30">
                            <td class="px-4 py-3 text-center font-black text-slate-700 dark:text-slate-200">{{ $i+1 }}</td>
                            <td class="px-4 py-3 font-semibold text-slate-900 dark:text-white">{{ $p->nombre }}</td>
                            <td class="px-4 py-3 text-slate-700 dark:text-slate-300">{{ $p->categoria ?? 'Sin categoría' }}</td>
                            <td class="px-4 py-3 text-right font-bold text-indigo-700 dark:text-indigo-300">{{ number_format((float)$p->total_vendido) }}</td>
                            <td class="px-4 py-3 text-right font-extrabold text-emerald-700 dark:text-emerald-300">{{ $moneda }} {{ number_format((float)$p->total_ingresos, 2) }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-1 text-xs font-bold rounded-full bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200">{{ (int)$p->numero_ventas }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-slate-500 dark:text-slate-400">No hay datos para estos filtros.</td>
                        </tr>
                    @endforelse
                </tbody>
                @if($rows->count() > 0)
                    <tfoot class="bg-slate-50 dark:bg-slate-900/40 border-t border-slate-200 dark:border-slate-700">
                        <tr>
                            <td colspan="3" class="px-4 py-3 text-right font-black text-slate-700 dark:text-slate-200">Totales</td>
                            <td class="px-4 py-3 text-right font-black text-indigo-700 dark:text-indigo-300">{{ number_format((float)$rows->sum('total_vendido')) }}</td>
                            <td class="px-4 py-3 text-right font-black text-emerald-700 dark:text-emerald-300">{{ $moneda }} {{ number_format((float)$rows->sum('total_ingresos'), 2) }}</td>
                            <td class="px-4 py-3 text-center font-black text-slate-700 dark:text-slate-200">{{ (int)$rows->sum('numero_ventas') }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const isDark = document.documentElement.classList.contains('dark');
        const gridColor = isDark ? 'rgba(148,163,184,0.18)' : 'rgba(100,116,139,0.15)';
        const tickColor = isDark ? 'rgba(226,232,240,0.85)' : 'rgba(15,23,42,0.75)';

        const labels = @json($labels->values());
        const unidades = @json($unidades->values());

        const commonScales = {
            x: { ticks: { color: tickColor }, grid: { color: gridColor } },
            y: { ticks: { color: tickColor }, grid: { color: gridColor } },
        };

        const el = document.getElementById('chartTop');
        if (el) {
            new Chart(el, {
                type: 'bar',
                data: { labels, datasets: [{ label: 'Unidades', data: unidades, borderWidth: 1 }] },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { labels: { color: tickColor } } },
                    scales: commonScales,
                }
            });
        }
    });
</script>
@endsection
