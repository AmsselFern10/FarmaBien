@extends('layouts.app')

@section('title', 'Reporte de Vencimientos')

@section('header')
    Reportes / Vencimientos
@endsection

@section('page-actions')
    <div>
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Vencimientos</h2>
        <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">Vencidos y próximos (vista previa + export PDF/Excel).</p>
    </div>
@endsection

@section('content')
@php
    $moneda = config('app.moneda', 'C$');

    $porDia = collect($vencimientosPorDia ?? []);
    $labelsDia = $porDia->pluck('fecha');
    $cntDia = $porDia->pluck('cantidad');

    $labelsEstado = ['Vencidos', 'Próximos'];
    $cntEstado = [(int)($vencidosCount ?? 0), (int)($proximosCount ?? 0)];
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-1 lg:px-3 py-2">

    @if (session('error'))
        <div class="mb-4 rounded-xl border border-rose-200 dark:border-rose-800 bg-rose-50 dark:bg-rose-900/20 p-4 text-rose-800 dark:text-rose-200">
            <p class="font-bold">{{ session('error') }}</p>
        </div>
    @endif

    {{-- Filtros --}}
    <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-gray-800 shadow-sm p-5 mb-4">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-3" id="formFiltrosVenc">
            <input type="hidden" name="export" id="export" value="">

            <div class="md:col-span-3">
                <label class="block text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-tight">Modo</label>
                <select name="modo"
                        class="mt-1 w-full px-3 py-2 bg-white dark:bg-gray-700 border border-slate-300 dark:border-slate-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-orange-500">
                    @foreach(['todos'=>'Vencidos + próximos','vencidos'=>'Solo vencidos','proximos'=>'Solo próximos'] as $k=>$v)
                        <option value="{{ $k }}" {{ (string)request('modo', $modo ?? 'todos') === (string)$k ? 'selected' : '' }}>{{ $v }}</option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-tight">Días</label>
                <input type="number" min="1" max="365" name="dias" value="{{ request('dias', $dias ?? 30) }}"
                       class="mt-1 w-full px-3 py-2 bg-white dark:bg-gray-700 border border-slate-300 dark:border-slate-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-orange-500" />
            </div>

            <div class="md:col-span-3">
                <label class="block text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-tight">Producto</label>
                <select name="producto_id"
                        class="mt-1 w-full px-3 py-2 bg-white dark:bg-gray-700 border border-slate-300 dark:border-slate-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-orange-500">
                    <option value="">Todos</option>
                    @foreach(($productos ?? []) as $p)
                        <option value="{{ $p->id }}" {{ (string)request('producto_id') === (string)$p->id ? 'selected' : '' }}>{{ $p->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-3">
                <label class="block text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-tight">Proveedor</label>
                <select name="proveedor_id"
                        class="mt-1 w-full px-3 py-2 bg-white dark:bg-gray-700 border border-slate-300 dark:border-slate-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-orange-500">
                    <option value="">Todos</option>
                    @foreach(($proveedores ?? []) as $pr)
                        <option value="{{ $pr->id }}" {{ (string)request('proveedor_id') === (string)$pr->id ? 'selected' : '' }}>{{ $pr->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-3 flex items-end gap-3">
                <label class="inline-flex items-center gap-2 text-sm font-bold text-slate-700 dark:text-slate-300">
                    <input type="checkbox" name="con_stock" value="1" {{ request('con_stock') ? 'checked' : '' }}
                           class="rounded border-slate-300 dark:border-slate-600 text-orange-600 focus:ring-orange-500" />
                    Con stock
                </label>
                <label class="inline-flex items-center gap-2 text-sm font-bold text-slate-700 dark:text-slate-300">
                    <input type="checkbox" name="activo" value="1" {{ request()->has('activo') ? (request('activo') ? 'checked' : '') : '' }}
                           class="rounded border-slate-300 dark:border-slate-600 text-orange-600 focus:ring-orange-500" />
                    Solo activos
                </label>
            </div>

            <div class="md:col-span-12 flex flex-col md:flex-row gap-2 md:items-end md:justify-end mt-1">
                <button type="submit"
                        class="inline-flex w-full md:w-auto justify-center items-center px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white font-extrabold rounded-lg shadow-sm">
                    Aplicar
                </button>

                <button type="button"
                        onclick="document.getElementById('export').value='pdf'; document.getElementById('formFiltrosVenc').submit();"
                        class="inline-flex w-full md:w-auto justify-center items-center px-4 py-2 bg-white dark:bg-gray-800 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 font-bold rounded-lg hover:bg-slate-50 dark:hover:bg-gray-700 shadow-sm">
                    PDF
                </button>

                <button type="button"
                        onclick="document.getElementById('export').value='excel'; document.getElementById('formFiltrosVenc').submit();"
                        class="inline-flex w-full md:w-auto justify-center items-center px-4 py-2 bg-white dark:bg-gray-800 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 font-bold rounded-lg hover:bg-slate-50 dark:hover:bg-gray-700 shadow-sm">
                    Excel
                </button>

                @if(request()->hasAny(['modo','dias','producto_id','proveedor_id','activo','con_stock']))
                    <a href="{{ route('reportes.vencimientos') }}"
                       class="inline-flex w-full md:w-auto justify-center items-center px-4 py-2 bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 text-orange-700 dark:text-orange-300 font-bold rounded-lg hover:bg-orange-100 dark:hover:bg-orange-900/30 shadow-sm">
                        Limpiar
                    </a>
                @endif
            </div>
        </form>

        <p class="mt-3 text-xs text-slate-500 dark:text-slate-400">Modo “todos” muestra vencidos (cualquier fecha anterior) + próximos (hasta {{ request('dias', $dias ?? 30) }} días).</p>
    </div>

    {{-- KPIs --}}
    <div class="grid grid-cols-1 md:grid-cols-5 gap-3 mb-4">
        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
            <p class="text-xs font-black text-slate-600 dark:text-slate-400 uppercase tracking-tight">Lotes listados</p>
            <p class="mt-1 text-2xl font-extrabold text-orange-600 dark:text-orange-300">{{ (int)($totalLotes ?? 0) }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
            <p class="text-xs font-black text-slate-600 dark:text-slate-400 uppercase tracking-tight">Vencidos</p>
            <p class="mt-1 text-2xl font-extrabold text-rose-600 dark:text-rose-300">{{ (int)($vencidosCount ?? 0) }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
            <p class="text-xs font-black text-slate-600 dark:text-slate-400 uppercase tracking-tight">Próximos</p>
            <p class="mt-1 text-2xl font-extrabold text-amber-600 dark:text-amber-300">{{ (int)($proximosCount ?? 0) }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
            <p class="text-xs font-black text-slate-600 dark:text-slate-400 uppercase tracking-tight">Stock total</p>
            <p class="mt-1 text-2xl font-extrabold text-indigo-600 dark:text-indigo-300">{{ number_format((int)($stockTotal ?? 0), 0) }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
            <p class="text-xs font-black text-slate-600 dark:text-slate-400 uppercase tracking-tight">Valor</p>
            <p class="mt-1 text-2xl font-extrabold text-emerald-600 dark:text-emerald-300">{{ $moneda }} {{ number_format((float)($valorTotal ?? 0), 2) }}</p>
        </div>
    </div>

    {{-- Gráficos --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 mb-4">
        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-black text-slate-700 dark:text-slate-300 uppercase tracking-tight">Vencidos vs próximos</h3>
            </div>
            <div class="mt-3 h-64">
                <canvas id="chartVencEstado"></canvas>
            </div>
        </div>

        <div class="xl:col-span-2 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-black text-slate-700 dark:text-slate-300 uppercase tracking-tight">Vencimientos por día</h3>
                <span class="text-xs text-slate-500 dark:text-slate-400">Cantidad de lotes</span>
            </div>
            <div class="mt-3 h-64">
                <canvas id="chartVencDia"></canvas>
            </div>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-gray-800 shadow-sm p-5">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-black text-slate-700 dark:text-slate-300 uppercase tracking-tight">Detalle</h3>
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
                        <th class="px-3 py-2 text-right text-xs font-black text-slate-600 dark:text-slate-400 uppercase tracking-tight">Días</th>
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
                            $fv = $l->fecha_vencimiento ? \Illuminate\Support\Carbon::parse($l->fecha_vencimiento)->startOfDay() : null;
                            $diasRest = $fv ? today()->diffInDays($fv, false) : null;
                            $vencido = $diasRest !== null ? ($diasRest < 0) : false;
                        @endphp
                        <tr class="hover:bg-slate-50 dark:hover:bg-gray-700/40">
                            <td class="px-3 py-2 whitespace-nowrap text-slate-700 dark:text-slate-200">{{ $l->id }}</td>
                            <td class="px-3 py-2 text-slate-700 dark:text-slate-200">{{ $l->producto?->nombre ?? '—' }}</td>
                            <td class="px-3 py-2 text-slate-700 dark:text-slate-200">{{ $l->proveedor?->nombre ?? '—' }}</td>
                            <td class="px-3 py-2 text-slate-700 dark:text-slate-200">{{ $l->numero_lote ?? '—' }}</td>
                            <td class="px-3 py-2 whitespace-nowrap {{ $vencido ? 'text-rose-700 dark:text-rose-300 font-bold' : 'text-slate-700 dark:text-slate-200' }}">
                                {{ $fv ? $fv->format('Y-m-d') : '—' }}
                            </td>
                            <td class="px-3 py-2 text-right font-bold {{ $vencido ? 'text-rose-700 dark:text-rose-300' : 'text-amber-700 dark:text-amber-300' }}">
                                {{ $diasRest === null ? '—' : $diasRest }}
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
                            <td colspan="11" class="px-3 py-8 text-center text-slate-500 dark:text-slate-400">
                                No hay vencimientos con esos filtros.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof Chart === 'undefined') return;

        const labelsEstado = @json($labelsEstado);
        const dataEstado = @json($cntEstado);

        const labelsDia = @json($labelsDia);
        const dataDia = @json($cntDia);

        const c1 = document.getElementById('chartVencEstado');
        if (c1) {
            new Chart(c1, {
                type: 'pie',
                data: { labels: labelsEstado, datasets: [{ label: 'Lotes', data: dataEstado }] },
                options: { responsive: true, maintainAspectRatio: false }
            });
        }

        const c2 = document.getElementById('chartVencDia');
        if (c2) {
            new Chart(c2, {
                type: 'bar',
                data: { labels: labelsDia, datasets: [{ label: 'Lotes', data: dataDia }] },
                options: { responsive: true, maintainAspectRatio: false }
            });
        }
    });
</script>
@endsection
