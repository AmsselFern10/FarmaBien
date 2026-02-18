@extends('layouts.app')

@section('title', 'Reporte de Lotes')

@section('header')
    Reportes / Lotes
@endsection

@section('page-actions')
    <div>
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Lotes</h2>
        <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">Estado, stock, proveedor y vencimiento (con export).</p>
    </div>
@endsection

@section('content')
@php
    $moneda = config('app.moneda', 'C$');

    $porEstado = collect($lotesPorEstado ?? []);
    $labelsEstado = $porEstado->pluck('estado');
    $cntEstado = $porEstado->pluck('cantidad');

    $topProv = collect($lotes ?? [])->groupBy(fn($l) => $l->proveedor?->nombre ?? '—')
        ->map(fn($g) => (int)$g->count())
        ->sortDesc()->take(10);
    $labelsProv = $topProv->keys()->values();
    $cntProv = $topProv->values();
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-1 lg:px-3 py-2">

    @if (session('error'))
        <div class="mb-4 rounded-xl border border-rose-200 dark:border-rose-800 bg-rose-50 dark:bg-rose-900/20 p-4 text-rose-800 dark:text-rose-200">
            <p class="font-bold">{{ session('error') }}</p>
        </div>
    @endif

    {{-- Filtros --}}
    <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-gray-800 shadow-sm p-5 mb-4">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-3" id="formFiltrosLotes">
            <input type="hidden" name="export" id="export" value="">

            <div class="md:col-span-3">
                <label class="block text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-tight">Producto</label>
                <select name="producto_id"
                        class="mt-1 w-full px-3 py-2 bg-white dark:bg-gray-700 border border-slate-300 dark:border-slate-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-cyan-500">
                    <option value="">Todos</option>
                    @foreach(($productos ?? []) as $p)
                        <option value="{{ $p->id }}" {{ (string)request('producto_id') === (string)$p->id ? 'selected' : '' }}>{{ $p->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-3">
                <label class="block text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-tight">Proveedor</label>
                <select name="proveedor_id"
                        class="mt-1 w-full px-3 py-2 bg-white dark:bg-gray-700 border border-slate-300 dark:border-slate-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-cyan-500">
                    <option value="">Todos</option>
                    @foreach(($proveedores ?? []) as $pr)
                        <option value="{{ $pr->id }}" {{ (string)request('proveedor_id') === (string)$pr->id ? 'selected' : '' }}>{{ $pr->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-tight">Estado</label>
                <select name="estado"
                        class="mt-1 w-full px-3 py-2 bg-white dark:bg-gray-700 border border-slate-300 dark:border-slate-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-cyan-500">
                    <option value="">Todos</option>
                    @foreach(['activo'=>'Activo','agotado'=>'Agotado','vencido'=>'Vencido','bloqueado'=>'Bloqueado'] as $k=>$v)
                        <option value="{{ $k }}" {{ request('estado')===$k ? 'selected' : '' }}>{{ $v }}</option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-tight">Próximos (días)</label>
                <input type="number" min="1" max="365" name="proximos_dias" value="{{ request('proximos_dias') }}" placeholder="30"
                       class="mt-1 w-full px-3 py-2 bg-white dark:bg-gray-700 border border-slate-300 dark:border-slate-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-cyan-500" />
            </div>

            <div class="md:col-span-2 flex items-end gap-3">
                <label class="inline-flex items-center gap-2 text-sm font-bold text-slate-700 dark:text-slate-300">
                    <input type="checkbox" name="con_stock" value="1" {{ request('con_stock') ? 'checked' : '' }}
                           class="rounded border-slate-300 dark:border-slate-600 text-cyan-600 focus:ring-cyan-500" />
                    Con stock
                </label>
                <label class="inline-flex items-center gap-2 text-sm font-bold text-slate-700 dark:text-slate-300">
                    <input type="checkbox" name="vencidos" value="1" {{ request('vencidos') ? 'checked' : '' }}
                           class="rounded border-slate-300 dark:border-slate-600 text-cyan-600 focus:ring-cyan-500" />
                    Vencidos
                </label>
            </div>

            <div class="md:col-span-2 flex items-end">
                <label class="inline-flex items-center gap-2 text-sm font-bold text-slate-700 dark:text-slate-300">
                    <input type="checkbox" name="activo" value="1" {{ request()->has('activo') ? (request('activo') ? 'checked' : '') : '' }}
                           class="rounded border-slate-300 dark:border-slate-600 text-cyan-600 focus:ring-cyan-500" />
                    Solo activos
                </label>
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-tight">Ingreso inicio</label>
                <input type="date" name="ingreso_inicio" value="{{ request('ingreso_inicio') }}"
                       class="mt-1 w-full px-3 py-2 bg-white dark:bg-gray-700 border border-slate-300 dark:border-slate-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-cyan-500" />
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-tight">Ingreso fin</label>
                <input type="date" name="ingreso_fin" value="{{ request('ingreso_fin') }}"
                       class="mt-1 w-full px-3 py-2 bg-white dark:bg-gray-700 border border-slate-300 dark:border-slate-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-cyan-500" />
            </div>

            <div class="md:col-span-12 flex flex-col md:flex-row gap-2 md:items-end md:justify-end mt-1">
                <button type="submit"
                        class="inline-flex w-full md:w-auto justify-center items-center px-4 py-2 bg-cyan-600 hover:bg-cyan-700 text-white font-extrabold rounded-lg shadow-sm">
                    Aplicar
                </button>

                <button type="button"
                        onclick="document.getElementById('export').value='pdf'; document.getElementById('formFiltrosLotes').submit();"
                        class="inline-flex w-full md:w-auto justify-center items-center px-4 py-2 bg-white dark:bg-gray-800 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 font-bold rounded-lg hover:bg-slate-50 dark:hover:bg-gray-700 shadow-sm">
                    PDF
                </button>

                <button type="button"
                        onclick="document.getElementById('export').value='excel'; document.getElementById('formFiltrosLotes').submit();"
                        class="inline-flex w-full md:w-auto justify-center items-center px-4 py-2 bg-white dark:bg-gray-800 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 font-bold rounded-lg hover:bg-slate-50 dark:hover:bg-gray-700 shadow-sm">
                    Excel
                </button>

                @if(request()->hasAny(['producto_id','proveedor_id','estado','activo','con_stock','vencidos','proximos_dias','ingreso_inicio','ingreso_fin']))
                    <a href="{{ route('reportes.lotes') }}"
                       class="inline-flex w-full md:w-auto justify-center items-center px-4 py-2 bg-cyan-50 dark:bg-cyan-900/20 border border-cyan-200 dark:border-cyan-800 text-cyan-700 dark:text-cyan-300 font-bold rounded-lg hover:bg-cyan-100 dark:hover:bg-cyan-900/30 shadow-sm">
                        Limpiar
                    </a>
                @endif
            </div>
        </form>

        <p class="mt-3 text-xs text-slate-500 dark:text-slate-400">Exportación: PDF (tabla) / Excel (CSV). Primero revisa la vista previa y luego exporta.</p>
    </div>

    {{-- KPIs --}}
    <div class="grid grid-cols-1 md:grid-cols-5 gap-3 mb-4">
        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
            <p class="text-xs font-black text-slate-600 dark:text-slate-400 uppercase tracking-tight">Lotes</p>
            <p class="mt-1 text-2xl font-extrabold text-cyan-600 dark:text-cyan-300">{{ (int)($totalLotes ?? 0) }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
            <p class="text-xs font-black text-slate-600 dark:text-slate-400 uppercase tracking-tight">Stock total</p>
            <p class="mt-1 text-2xl font-extrabold text-indigo-600 dark:text-indigo-300">{{ number_format((int)($stockTotal ?? 0), 0) }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
            <p class="text-xs font-black text-slate-600 dark:text-slate-400 uppercase tracking-tight">Valor</p>
            <p class="mt-1 text-2xl font-extrabold text-emerald-600 dark:text-emerald-300">{{ $moneda }} {{ number_format((float)($valorTotal ?? 0), 2) }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
            <p class="text-xs font-black text-slate-600 dark:text-slate-400 uppercase tracking-tight">Vencidos</p>
            <p class="mt-1 text-2xl font-extrabold text-rose-600 dark:text-rose-300">{{ (int)($vencidosCount ?? 0) }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
            <p class="text-xs font-black text-slate-600 dark:text-slate-400 uppercase tracking-tight">Próx. 30 días</p>
            <p class="mt-1 text-2xl font-extrabold text-amber-600 dark:text-amber-300">{{ (int)($proximosCount ?? 0) }}</p>
        </div>
    </div>

    {{-- Gráficos --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 mb-4">
        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-black text-slate-700 dark:text-slate-300 uppercase tracking-tight">Por estado</h3>
                <span class="text-xs text-slate-500 dark:text-slate-400">Cantidad</span>
            </div>
            <div class="mt-3 h-64">
                <canvas id="chartLotesEstado"></canvas>
            </div>
        </div>

        <div class="xl:col-span-2 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-black text-slate-700 dark:text-slate-300 uppercase tracking-tight">Top proveedores</h3>
                <span class="text-xs text-slate-500 dark:text-slate-400">Cantidad de lotes</span>
            </div>
            <div class="mt-3 h-64">
                <canvas id="chartLotesProv"></canvas>
            </div>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-gray-800 shadow-sm p-5">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-black text-slate-700 dark:text-slate-300 uppercase tracking-tight">Detalle de lotes</h3>
            <span class="text-xs text-slate-500 dark:text-slate-400">{{ (int)($totalLotes ?? 0) }} registros</span>
        </div>

        <div class="mt-3 overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 dark:bg-gray-900/40">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-black text-slate-600 dark:text-slate-400 uppercase tracking-tight">ID</th>
                        <th class="px-3 py-2 text-left text-xs font-black text-slate-600 dark:text-slate-400 uppercase tracking-tight">Producto</th>
                        <th class="px-3 py-2 text-left text-xs font-black text-slate-600 dark:text-slate-400 uppercase tracking-tight">Proveedor</th>
                        <th class="px-3 py-2 text-left text-xs font-black text-slate-600 dark:text-slate-400 uppercase tracking-tight">Lote</th>
                        <th class="px-3 py-2 text-left text-xs font-black text-slate-600 dark:text-slate-400 uppercase tracking-tight">Vencimiento</th>
                        <th class="px-3 py-2 text-right text-xs font-black text-slate-600 dark:text-slate-400 uppercase tracking-tight">Stock</th>
                        <th class="px-3 py-2 text-right text-xs font-black text-slate-600 dark:text-slate-400 uppercase tracking-tight">Precio</th>
                        <th class="px-3 py-2 text-right text-xs font-black text-slate-600 dark:text-slate-400 uppercase tracking-tight">Valor</th>
                        <th class="px-3 py-2 text-left text-xs font-black text-slate-600 dark:text-slate-400 uppercase tracking-tight">Estado</th>
                        <th class="px-3 py-2 text-left text-xs font-black text-slate-600 dark:text-slate-400 uppercase tracking-tight">Activo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    @foreach(($lotes ?? []) as $l)
                        @php
                            $valor = ((float)($l->precio_compra ?? 0)) * (int)($l->stock_actual ?? 0);
                            $fv = $l->fecha_vencimiento ? \Illuminate\Support\Carbon::parse($l->fecha_vencimiento) : null;
                            $vencido = $fv ? $fv->startOfDay()->lt(today()) : false;
                        @endphp
                        <tr class="hover:bg-slate-50 dark:hover:bg-gray-700/40">
                            <td class="px-3 py-2 whitespace-nowrap text-slate-700 dark:text-slate-200">{{ $l->id }}</td>
                            <td class="px-3 py-2 text-slate-700 dark:text-slate-200">{{ $l->producto?->nombre ?? '—' }}</td>
                            <td class="px-3 py-2 text-slate-700 dark:text-slate-200">{{ $l->proveedor?->nombre ?? '—' }}</td>
                            <td class="px-3 py-2 text-slate-700 dark:text-slate-200">{{ $l->numero_lote ?? '—' }}</td>
                            <td class="px-3 py-2 whitespace-nowrap {{ $vencido ? 'text-rose-700 dark:text-rose-300 font-bold' : 'text-slate-700 dark:text-slate-200' }}">
                                {{ $fv ? $fv->format('Y-m-d') : '—' }}
                            </td>
                            <td class="px-3 py-2 text-right text-slate-700 dark:text-slate-200">{{ (int)($l->stock_actual ?? 0) }}</td>
                            <td class="px-3 py-2 text-right text-slate-700 dark:text-slate-200">{{ number_format((float)($l->precio_compra ?? 0), 2) }}</td>
                            <td class="px-3 py-2 text-right font-bold text-emerald-700 dark:text-emerald-300">{{ number_format($valor, 2) }}</td>
                            <td class="px-3 py-2 text-slate-700 dark:text-slate-200">{{ $l->estado ?? '—' }}</td>
                            <td class="px-3 py-2 text-slate-700 dark:text-slate-200">{{ $l->activo ? 'Sí' : 'No' }}</td>
                        </tr>
                    @endforeach

                    @if(empty($lotes) || count($lotes) === 0)
                        <tr>
                            <td colspan="10" class="px-3 py-8 text-center text-slate-500 dark:text-slate-400">
                                No hay lotes con esos filtros.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof Chart === 'undefined') return;

        const labelsEstado = @json($labelsEstado);
        const cntEstado = @json($cntEstado);

        const labelsProv = @json($labelsProv);
        const cntProv = @json($cntProv);

        const c1 = document.getElementById('chartLotesEstado');
        if (c1) {
            new Chart(c1, {
                type: 'pie',
                data: { labels: labelsEstado, datasets: [{ label: 'Lotes', data: cntEstado }] },
                options: { responsive: true, maintainAspectRatio: false }
            });
        }

        const c2 = document.getElementById('chartLotesProv');
        if (c2) {
            new Chart(c2, {
                type: 'bar',
                data: { labels: labelsProv, datasets: [{ label: 'Lotes', data: cntProv }] },
                options: { responsive: true, maintainAspectRatio: false }
            });
        }
    });
</script>
@endsection
