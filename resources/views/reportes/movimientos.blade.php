@extends('layouts.app')

@section('title', 'Movimientos de Inventario')

@section('header')
    Movimientos de Inventario
@endsection

@section('page-actions')
    <div>
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Movimientos de Inventario 🔎</h2>
        <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">Trazabilidad: entradas, salidas y ajustes con filtros y exportación.</p>
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
    $tipoData = collect($movimientosPorTipo ?? []);
    $labelsTipo = $tipoData->pluck('tipo');
    $cantTipo = $tipoData->pluck('cantidad');

    $origenData = collect($movimientosPorOrigen ?? [])->take(12);
    $labelsOrigen = $origenData->pluck('origen');
    $cantOrigen = $origenData->pluck('cantidad');
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-1 lg:px-3 py-2">

    {{-- Filtros --}}
    <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-gray-800 shadow-sm p-5 mb-4">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-3" id="formFiltrosMovs">
            <input type="hidden" name="export" id="export" value="">

            <div class="md:col-span-2">
                <label class="block text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-tight">Inicio</label>
                <input type="date" name="fecha_inicio" value="{{ $fechaInicio }}"
                       class="mt-1 w-full px-3 py-2 bg-white dark:bg-gray-700 border border-slate-300 dark:border-slate-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-amber-500" />
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-tight">Fin</label>
                <input type="date" name="fecha_fin" value="{{ $fechaFin }}" max="{{ date('Y-m-d') }}"
                       class="mt-1 w-full px-3 py-2 bg-white dark:bg-gray-700 border border-slate-300 dark:border-slate-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-amber-500" />
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-tight">Tipo</label>
                <select name="tipo"
                        class="mt-1 w-full px-3 py-2 bg-white dark:bg-gray-700 border border-slate-300 dark:border-slate-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-amber-500">
                    <option value="">Todos</option>
                    @foreach(['entrada'=>'Entrada','salida'=>'Salida','ajuste'=>'Ajuste'] as $k=>$lbl)
                        <option value="{{ $k }}" {{ (string)request('tipo')===(string)$k ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-3">
                <label class="block text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-tight">Producto</label>
                <select name="producto_id"
                        class="mt-1 w-full px-3 py-2 bg-white dark:bg-gray-700 border border-slate-300 dark:border-slate-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-amber-500">
                    <option value="">Todos</option>
                    @foreach(($productos ?? []) as $p)
                        <option value="{{ $p->id }}" {{ (string)request('producto_id') === (string)$p->id ? 'selected' : '' }}>{{ $p->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-3">
                <label class="block text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-tight">Usuario</label>
                <select name="user_id"
                        class="mt-1 w-full px-3 py-2 bg-white dark:bg-gray-700 border border-slate-300 dark:border-slate-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-amber-500">
                    <option value="">Todos</option>
                    @foreach(($usuarios ?? []) as $u)
                        <option value="{{ $u->id }}" {{ (string)request('user_id') === (string)$u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-tight">Lote ID</label>
                <input type="number" name="lote_id" value="{{ request('lote_id') }}" placeholder="#"
                       class="mt-1 w-full px-3 py-2 bg-white dark:bg-gray-700 border border-slate-300 dark:border-slate-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-amber-500" />
            </div>

            <div class="md:col-span-3">
                <label class="block text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-tight">Origen</label>
                <input type="text" name="origen" value="{{ request('origen') }}" placeholder="venta / compra / ajuste ..."
                       class="mt-1 w-full px-3 py-2 bg-white dark:bg-gray-700 border border-slate-300 dark:border-slate-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-amber-500" />
            </div>

            <div class="md:col-span-3">
                <label class="block text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-tight">Motivo</label>
                <input type="text" name="motivo" value="{{ request('motivo') }}" placeholder="texto contiene..."
                       class="mt-1 w-full px-3 py-2 bg-white dark:bg-gray-700 border border-slate-300 dark:border-slate-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-amber-500" />
            </div>

            <div class="md:col-span-4 flex items-end gap-2">
                <button type="submit"
                        onclick="document.getElementById('export').value='';"
                        class="inline-flex w-full md:w-auto justify-center items-center px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-lg shadow-sm">
                    Ver / Actualizar
                </button>

                <button type="button"
                        onclick="document.getElementById('export').value='pdf'; document.getElementById('formFiltrosMovs').submit();"
                        class="inline-flex w-full md:w-auto justify-center items-center px-4 py-2 bg-white dark:bg-gray-800 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 font-bold rounded-lg hover:bg-slate-50 dark:hover:bg-gray-700 shadow-sm">
                    PDF
                </button>

                <button type="button"
                        onclick="document.getElementById('export').value='excel'; document.getElementById('formFiltrosMovs').submit();"
                        class="inline-flex w-full md:w-auto justify-center items-center px-4 py-2 bg-white dark:bg-gray-800 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 font-bold rounded-lg hover:bg-slate-50 dark:hover:bg-gray-700 shadow-sm">
                    Excel
                </button>

                @if(request()->hasAny(['fecha_inicio','fecha_fin','tipo','producto_id','lote_id','user_id','origen','motivo']))
                    <a href="{{ route('reportes.movimientos') }}"
                       class="inline-flex w-full md:w-auto justify-center items-center px-4 py-2 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300 font-bold rounded-lg hover:bg-rose-100 dark:hover:bg-rose-900/30 shadow-sm">
                        Limpiar
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- KPIs --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4">
        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
            <p class="text-xs font-black text-slate-600 dark:text-slate-400 uppercase tracking-tight">Total entradas (unid.)</p>
            <p class="mt-1 text-2xl font-extrabold text-emerald-600 dark:text-emerald-300">{{ (int)($totalEntradas ?? 0) }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
            <p class="text-xs font-black text-slate-600 dark:text-slate-400 uppercase tracking-tight">Total salidas (unid.)</p>
            <p class="mt-1 text-2xl font-extrabold text-rose-600 dark:text-rose-300">{{ (int)($totalSalidas ?? 0) }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
            <p class="text-xs font-black text-slate-600 dark:text-slate-400 uppercase tracking-tight">Total ajustes (unid.)</p>
            <p class="mt-1 text-2xl font-extrabold text-amber-600 dark:text-amber-300">{{ (int)($totalAjustes ?? 0) }}</p>
        </div>
    </div>

    {{-- Gráficos --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 mb-4">
        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-black text-slate-700 dark:text-slate-300 uppercase tracking-tight">Movimientos por tipo</h3>
                <span class="text-xs text-slate-500 dark:text-slate-400">Cantidad</span>
            </div>
            <div class="mt-3 h-64">
                <canvas id="chartTipo"></canvas>
            </div>
        </div>

        <div class="xl:col-span-2 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-black text-slate-700 dark:text-slate-300 uppercase tracking-tight">Movimientos por origen</h3>
                <span class="text-xs text-slate-500 dark:text-slate-400">Top 12</span>
            </div>
            <div class="mt-3 h-64">
                <canvas id="chartOrigen"></canvas>
            </div>
        </div>
    </div>

    {{-- Detalle --}}
    <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-gray-800 shadow-sm">
        <div class="p-5 border-b border-slate-200 dark:border-slate-700">
            <h3 class="text-sm font-black text-slate-700 dark:text-slate-300 uppercase tracking-tight">Detalle de movimientos</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Cada fila es un movimiento (trazabilidad completa).</p>
        </div>
        <div class="overflow-auto max-h-[520px]">
            <table class="min-w-full table-excel text-sm">
                <thead class="bg-slate-50 dark:bg-slate-900/40 border-b border-slate-200 dark:border-slate-700">
                    <tr class="text-xs text-slate-600 dark:text-slate-300 uppercase tracking-tight">
                        <th class="px-4 py-3 text-left font-black">Fecha</th>
                        <th class="px-4 py-3 text-center font-black">Tipo</th>
                        <th class="px-4 py-3 text-left font-black">Producto</th>
                        <th class="px-4 py-3 text-left font-black">Lote</th>
                        <th class="px-4 py-3 text-right font-black">Cant.</th>
                        <th class="px-4 py-3 text-right font-black">Saldo ant.</th>
                        <th class="px-4 py-3 text-right font-black">Saldo nuevo</th>
                        <th class="px-4 py-3 text-left font-black">Origen</th>
                        <th class="px-4 py-3 text-left font-black">Motivo</th>
                        <th class="px-4 py-3 text-left font-black">Usuario</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    @forelse(($movimientos ?? []) as $m)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-900/30">
                            <td class="px-4 py-3 text-slate-700 dark:text-slate-300">{{ optional($m->fecha_movimiento)->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3 text-center">
                                @php $t = (string)($m->tipo ?? ''); @endphp
                                <span class="px-2 py-1 text-xs font-bold rounded-full
                                    {{ $t==='entrada' ? 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800' : '' }}
                                    {{ $t==='salida' ? 'bg-rose-50 dark:bg-rose-900/20 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800' : '' }}
                                    {{ $t==='ajuste' ? 'bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800' : '' }}
                                ">
                                    {{ ucfirst($t ?: '—') }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-semibold text-slate-900 dark:text-white">{{ $m->producto?->nombre ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-700 dark:text-slate-300">{{ $m->lote?->numero_lote ?? '—' }}</td>
                            <td class="px-4 py-3 text-right font-bold text-slate-900 dark:text-white">{{ (int)($m->cantidad ?? 0) }}</td>
                            <td class="px-4 py-3 text-right text-slate-700 dark:text-slate-300">{{ (int)($m->saldo_anterior ?? 0) }}</td>
                            <td class="px-4 py-3 text-right text-slate-700 dark:text-slate-300">{{ (int)($m->saldo_nuevo ?? 0) }}</td>
                            <td class="px-4 py-3 text-slate-700 dark:text-slate-300">{{ $m->origen ?? '—' }}{{ $m->origen_id ? ' #'.$m->origen_id : '' }}</td>
                            <td class="px-4 py-3 text-slate-700 dark:text-slate-300">{{ $m->motivo ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-700 dark:text-slate-300">{{ $m->usuario?->name ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-6 py-10 text-center text-slate-500 dark:text-slate-400">No hay movimientos para estos filtros.</td>
                        </tr>
                    @endforelse
                </tbody>
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

        const labelsTipo = @json($labelsTipo->values());
        const cantTipo = @json($cantTipo->values());

        const labelsOrigen = @json($labelsOrigen->values());
        const cantOrigen = @json($cantOrigen->values());

        const commonScales = {
            x: { ticks: { color: tickColor }, grid: { color: gridColor } },
            y: { ticks: { color: tickColor }, grid: { color: gridColor } },
        };

        const elTipo = document.getElementById('chartTipo');
        if (elTipo) {
            new Chart(elTipo, {
                type: 'pie',
                data: {
                    labels: labelsTipo,
                    datasets: [{ data: cantTipo, borderWidth: 1 }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { color: tickColor } },
                    }
                }
            });
        }

        const elOrigen = document.getElementById('chartOrigen');
        if (elOrigen) {
            new Chart(elOrigen, {
                type: 'bar',
                data: {
                    labels: labelsOrigen,
                    datasets: [{ label: 'Cantidad', data: cantOrigen, borderWidth: 1 }]
                },
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
