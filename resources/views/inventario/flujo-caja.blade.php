@extends('layouts.app')

@section('title', 'Flujo de Caja')

@php
    $flujoRouteName = \Illuminate\Support\Facades\Route::has('reportes.flujoCaja')
        ? 'reportes.flujoCaja'
        : (\Illuminate\Support\Facades\Route::has('reportes.flujo-caja') ? 'reportes.flujo-caja' : 'reportes.index');
@endphp

@section('header')
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-black text-slate-900 dark:text-white">Flujo de Caja</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">Entradas por ventas y salidas por compras</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route($flujoRouteName, array_merge(request()->all(), ['export' => 'pdf'])) }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-slate-700 hover:bg-slate-800 text-white font-black">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z"/></svg>
                PDF
            </a>
            <a href="{{ route($flujoRouteName, array_merge(request()->all(), ['export' => 'excel'])) }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-black">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m3-3H9"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h12a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V6z"/></svg>
                Excel
            </a>
            <a href="{{ route('reportes.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-slate-200 hover:bg-slate-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-slate-900 dark:text-white font-black">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Volver
            </a>
        </div>
    </div>
@endsection

@section('content')
<style>
  canvas{max-height:16rem;}
</style>

    {{-- Filtros (arriba) --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 mb-6">
        <form method="GET" class="p-5 grid grid-cols-1 md:grid-cols-12 gap-4">
            <div class="md:col-span-4">
                <label class="block text-xs font-black uppercase text-slate-600 dark:text-slate-300 mb-1">Fecha inicio</label>
                <input type="date" name="fecha_inicio" value="{{ $fechaInicio }}"
                    class="w-full px-3 py-2 rounded-lg bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-slate-900 dark:text-white" />
            </div>
            <div class="md:col-span-4">
                <label class="block text-xs font-black uppercase text-slate-600 dark:text-slate-300 mb-1">Fecha fin</label>
                <input type="date" name="fecha_fin" value="{{ $fechaFin }}"
                    class="w-full px-3 py-2 rounded-lg bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-slate-900 dark:text-white" />
            </div>
            <div class="md:col-span-4 flex items-end gap-2">
                <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-black">Ver</button>
                <a href="{{ route($flujoRouteName) }}" class="px-4 py-2 rounded-lg bg-slate-200 hover:bg-slate-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-slate-900 dark:text-white font-black">Limpiar</a>
            </div>
        </form>
    </div>

    {{-- Resumen --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
            <div class="text-xs font-black uppercase text-slate-500 dark:text-slate-400">Total ventas</div>
            <div class="mt-2 text-xl font-black text-slate-900 dark:text-white">C$ {{ number_format($ventasTotal, 2) }}</div>
            <div class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">{{ $ventas->count() }} operaciones</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
            <div class="text-xs font-black uppercase text-slate-500 dark:text-slate-400">Efectivo en caja (rec - vuelto)</div>
            <div class="mt-2 text-xl font-black text-emerald-600 dark:text-emerald-400">C$ {{ number_format($efectivoNeto, 2) }}</div>
            <div class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">Recibido: {{ number_format($efectivoRecibido, 2) }} · Vuelto: {{ number_format($efectivoVuelto, 2) }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
            <div class="text-xs font-black uppercase text-slate-500 dark:text-slate-400">Total compras (salidas)</div>
            <div class="mt-2 text-xl font-black text-red-600 dark:text-red-400">C$ {{ number_format($comprasTotal, 2) }}</div>
            <div class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">{{ $compras->count() }} operaciones</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
            <div class="text-xs font-black uppercase text-slate-500 dark:text-slate-400">Neto teórico (efectivo - compras)</div>
            <div class="mt-2 text-xl font-black text-amber-600 dark:text-amber-400">C$ {{ number_format($netoTeorico, 2) }}</div>
            <div class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">Referencia (no reemplaza arqueo)</div>
        </div>
    </div>

    {{-- Gráficos (preview) --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-base font-black text-slate-900 dark:text-white">Ventas por método</h3>
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400">Preview</span>
            </div>
            <div class="relative h-64"><canvas id="metodoPie"></canvas></div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-base font-black text-slate-900 dark:text-white">Ventas por día</h3>
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400">Preview</span>
            </div>
            <canvas id="diaBar" height="170"></canvas>
        </div>
    </div>

    {{-- TABLA: Efectivo --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 mb-6">
        <div class="p-5 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-black text-slate-900 dark:text-white">Efectivo</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400">Recibido, vuelto y neto en caja</p>
            </div>
            <div class="text-sm font-black text-emerald-600 dark:text-emerald-400">Neto: C$ {{ number_format($efectivoNeto, 2) }}</div>
        </div>
        <div class="px-5 pb-5 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700/60 sticky top-0">
                    <tr>
                        <th class="px-4 py-3 text-left font-black uppercase text-xs text-slate-600 dark:text-slate-200">Venta</th>
                        <th class="px-4 py-3 text-left font-black uppercase text-xs text-slate-600 dark:text-slate-200">Fecha</th>
                        <th class="px-4 py-3 text-left font-black uppercase text-xs text-slate-600 dark:text-slate-200">Cliente</th>
                        <th class="px-4 py-3 text-left font-black uppercase text-xs text-slate-600 dark:text-slate-200">Cajero</th>
                        <th class="px-4 py-3 text-right font-black uppercase text-xs text-slate-600 dark:text-slate-200">Total</th>
                        <th class="px-4 py-3 text-right font-black uppercase text-xs text-slate-600 dark:text-slate-200">Recibido</th>
                        <th class="px-4 py-3 text-right font-black uppercase text-xs text-slate-600 dark:text-slate-200">Vuelto</th>
                        <th class="px-4 py-3 text-right font-black uppercase text-xs text-slate-600 dark:text-slate-200">En caja</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($ventasEfectivo as $v)
                        @php
                            $rec = (float)($v->monto_recibido ?? 0);
                            $vuel = (float)($v->cambio ?? 0);
                            $net = $rec - $vuel;
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                            <td class="px-4 py-3 font-semibold text-slate-900 dark:text-white">#{{ $v->id }}</td>
                            <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ optional($v->fecha ?? $v->created_at)->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ optional($v->cliente)->nombre ?? 'Mostrador' }}</td>
                            <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ optional($v->usuario)->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-right font-bold text-slate-900 dark:text-white">C$ {{ number_format((float)$v->total, 2) }}</td>
                            <td class="px-4 py-3 text-right text-slate-700 dark:text-slate-200">C$ {{ number_format($rec, 2) }}</td>
                            <td class="px-4 py-3 text-right text-slate-700 dark:text-slate-200">C$ {{ number_format($vuel, 2) }}</td>
                            <td class="px-4 py-3 text-right font-black text-emerald-600 dark:text-emerald-400">C$ {{ number_format($net, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-4 py-6 text-center text-slate-500 dark:text-slate-400">Sin ventas en efectivo</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- TABLA: Crédito/Débito/Transferencia (una sola) --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 mb-6">
        <div class="p-5 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-black text-slate-900 dark:text-white">Crédito / Débito / Transferencia</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400">Ventas bancarizadas</p>
            </div>
            <div class="text-sm font-black text-blue-600 dark:text-blue-400">Total: C$ {{ number_format($bancarizadoTotal, 2) }}</div>
        </div>
        <div class="px-5 pb-5 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700/60 sticky top-0">
                    <tr>
                        <th class="px-4 py-3 text-left font-black uppercase text-xs text-slate-600 dark:text-slate-200">Venta</th>
                        <th class="px-4 py-3 text-left font-black uppercase text-xs text-slate-600 dark:text-slate-200">Fecha</th>
                        <th class="px-4 py-3 text-left font-black uppercase text-xs text-slate-600 dark:text-slate-200">Método</th>
                        <th class="px-4 py-3 text-left font-black uppercase text-xs text-slate-600 dark:text-slate-200">Cliente</th>
                        <th class="px-4 py-3 text-left font-black uppercase text-xs text-slate-600 dark:text-slate-200">Cajero</th>
                        <th class="px-4 py-3 text-right font-black uppercase text-xs text-slate-600 dark:text-slate-200">Total</th>
                        <th class="px-4 py-3 text-left font-black uppercase text-xs text-slate-600 dark:text-slate-200">Referencia</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($ventasBancarizado as $v)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                            <td class="px-4 py-3 font-semibold text-slate-900 dark:text-white">#{{ $v->id }}</td>
                            <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ optional($v->fecha ?? $v->created_at)->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ strtoupper($v->metodo_pago) }}</td>
                            <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ optional($v->cliente)->nombre ?? 'Mostrador' }}</td>
                            <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ optional($v->usuario)->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-right font-black text-blue-600 dark:text-blue-400">C$ {{ number_format((float)$v->total, 2) }}</td>
                            <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ $v->referencia_pago ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-6 text-center text-slate-500 dark:text-slate-400">Sin ventas bancarizadas</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- TABLA: Pagar luego --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 mb-6">
        <div class="p-5 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-black text-slate-900 dark:text-white">Pagar luego</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400">Ventas a crédito / pendiente</p>
            </div>
            <div class="text-sm font-black text-amber-600 dark:text-amber-400">Total: C$ {{ number_format($pagarLuegoTotal, 2) }}</div>
        </div>
        <div class="px-5 pb-5 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700/60 sticky top-0">
                    <tr>
                        <th class="px-4 py-3 text-left font-black uppercase text-xs text-slate-600 dark:text-slate-200">Venta</th>
                        <th class="px-4 py-3 text-left font-black uppercase text-xs text-slate-600 dark:text-slate-200">Fecha</th>
                        <th class="px-4 py-3 text-left font-black uppercase text-xs text-slate-600 dark:text-slate-200">Cliente</th>
                        <th class="px-4 py-3 text-left font-black uppercase text-xs text-slate-600 dark:text-slate-200">Cajero</th>
                        <th class="px-4 py-3 text-right font-black uppercase text-xs text-slate-600 dark:text-slate-200">Total</th>
                        <th class="px-4 py-3 text-left font-black uppercase text-xs text-slate-600 dark:text-slate-200">Obs.</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($ventasPagarLuego as $v)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                            <td class="px-4 py-3 font-semibold text-slate-900 dark:text-white">#{{ $v->id }}</td>
                            <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ optional($v->fecha ?? $v->created_at)->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ optional($v->cliente)->nombre ?? 'Mostrador' }}</td>
                            <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ optional($v->usuario)->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-right font-black text-amber-600 dark:text-amber-400">C$ {{ number_format((float)$v->total, 2) }}</td>
                            <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ $v->observaciones ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-6 text-center text-slate-500 dark:text-slate-400">Sin ventas en pagar luego</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- TABLA: Otros --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 mb-6">
        <div class="p-5 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-black text-slate-900 dark:text-white">Otros</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400">Métodos alternativos</p>
            </div>
            <div class="text-sm font-black text-slate-900 dark:text-white">Total: C$ {{ number_format($otrosTotal, 2) }}</div>
        </div>
        <div class="px-5 pb-5 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700/60 sticky top-0">
                    <tr>
                        <th class="px-4 py-3 text-left font-black uppercase text-xs text-slate-600 dark:text-slate-200">Venta</th>
                        <th class="px-4 py-3 text-left font-black uppercase text-xs text-slate-600 dark:text-slate-200">Fecha</th>
                        <th class="px-4 py-3 text-left font-black uppercase text-xs text-slate-600 dark:text-slate-200">Cliente</th>
                        <th class="px-4 py-3 text-left font-black uppercase text-xs text-slate-600 dark:text-slate-200">Cajero</th>
                        <th class="px-4 py-3 text-right font-black uppercase text-xs text-slate-600 dark:text-slate-200">Total</th>
                        <th class="px-4 py-3 text-left font-black uppercase text-xs text-slate-600 dark:text-slate-200">Obs.</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($ventasOtros as $v)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                            <td class="px-4 py-3 font-semibold text-slate-900 dark:text-white">#{{ $v->id }}</td>
                            <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ optional($v->fecha ?? $v->created_at)->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ optional($v->cliente)->nombre ?? 'Mostrador' }}</td>
                            <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ optional($v->usuario)->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-right font-black text-slate-900 dark:text-white">C$ {{ number_format((float)$v->total, 2) }}</td>
                            <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ $v->observaciones ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-6 text-center text-slate-500 dark:text-slate-400">Sin ventas en otros</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- TABLA: Compras (salidas) --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
        <div class="p-5 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-black text-slate-900 dark:text-white">Compras (salidas)</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400">Compras recibidas en el rango</p>
            </div>
            <div class="text-sm font-black text-red-600 dark:text-red-400">Total: C$ {{ number_format($comprasTotal, 2) }}</div>
        </div>
        <div class="px-5 pb-5 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700/60 sticky top-0">
                    <tr>
                        <th class="px-4 py-3 text-left font-black uppercase text-xs text-slate-600 dark:text-slate-200">Compra</th>
                        <th class="px-4 py-3 text-left font-black uppercase text-xs text-slate-600 dark:text-slate-200">Fecha</th>
                        <th class="px-4 py-3 text-left font-black uppercase text-xs text-slate-600 dark:text-slate-200">Proveedor</th>
                        <th class="px-4 py-3 text-left font-black uppercase text-xs text-slate-600 dark:text-slate-200">Usuario</th>
                        <th class="px-4 py-3 text-right font-black uppercase text-xs text-slate-600 dark:text-slate-200">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($compras as $c)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                            <td class="px-4 py-3 font-semibold text-slate-900 dark:text-white">#{{ $c->id }}</td>
                            <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ optional($c->fecha ?? $c->created_at)->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ optional($c->proveedor)->nombre ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ optional($c->usuario)->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-right font-black text-red-600 dark:text-red-400">C$ {{ number_format((float)$c->total, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-6 text-center text-slate-500 dark:text-slate-400">Sin compras</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>



<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        (function () {
            const metodo = @json($ventasPorMetodo);
            const labels = metodo.map(x => (x.metodo || '').toUpperCase());
            const totals = metodo.map(x => Number(x.total || 0));

            const pie = document.getElementById('metodoPie');
            if (pie) {
                new Chart(pie, {
                    type: 'pie',
                    data: { labels, datasets: [{ label: 'Total', data: totals }] },
                    options: { responsive: true, maintainAspectRatio: false }
                });
            }

            const dia = @json($ventasPorDia);
            const dLabels = dia.map(x => x.fecha);
            const dTotals = dia.map(x => Number(x.total || 0));

            const bar = document.getElementById('diaBar');
            if (bar) {
                new Chart(bar, {
                    type: 'bar',
                    data: { labels: dLabels, datasets: [{ label: 'Total', data: dTotals }] },
                    options: { responsive: true, maintainAspectRatio: false }
                });
            }
        })();
    </script>

@endsection
