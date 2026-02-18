@extends('layouts.app')

@section('title', 'Reporte de Ajustes de Inventario')

@section('header')
    Reportes / Ajustes de Inventario
@endsection

@section('page-actions')
    <div>
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Ajustes de Inventario</h2>
        <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">Filtra, revisa la vista previa y exporta a PDF/Excel.</p>
    </div>
@endsection

@section('content')
@php
    $moneda = config('app.moneda', 'C$');

    $motivosTop = collect($ajustesPorMotivo ?? [])->sortByDesc('cantidad')->take(10);
    $labelsMotivos = $motivosTop->pluck('motivo');
    $cntMotivos = $motivosTop->pluck('cantidad');

    $usuariosTop = collect($ajustesPorUsuario ?? [])->sortByDesc('cantidad')->take(10);
    $labelsUsuarios = $usuariosTop->pluck('usuario');
    $cntUsuarios = $usuariosTop->pluck('cantidad');

    $neto = (float) ($totalAjustes ?? 0);
    $aumentos = (int) collect($movimientos ?? [])->filter(fn($m) => (int)$m->cantidad > 0)->sum('cantidad');
    $reducciones = (int) abs(collect($movimientos ?? [])->filter(fn($m) => (int)$m->cantidad < 0)->sum('cantidad'));
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-1 lg:px-3 py-2">

    @if (session('error'))
        <div class="mb-4 rounded-xl border border-rose-200 dark:border-rose-800 bg-rose-50 dark:bg-rose-900/20 p-4 text-rose-800 dark:text-rose-200">
            <p class="font-bold">{{ session('error') }}</p>
        </div>
    @endif

    {{-- Filtros --}}
    <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-gray-800 shadow-sm p-5 mb-4">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-3" id="formFiltrosAjustes">
            <input type="hidden" name="export" id="export" value="">

            <div class="md:col-span-2">
                <label class="block text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-tight">Inicio</label>
                <input type="date" name="fecha_inicio" value="{{ $fechaInicio }}"
                       class="mt-1 w-full px-3 py-2 bg-white dark:bg-gray-700 border border-slate-300 dark:border-slate-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-rose-500" />
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-tight">Fin</label>
                <input type="date" name="fecha_fin" value="{{ $fechaFin }}" max="{{ date('Y-m-d') }}"
                       class="mt-1 w-full px-3 py-2 bg-white dark:bg-gray-700 border border-slate-300 dark:border-slate-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-rose-500" />
            </div>

            <div class="md:col-span-3">
                <label class="block text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-tight">Producto</label>
                <select name="producto_id"
                        class="mt-1 w-full px-3 py-2 bg-white dark:bg-gray-700 border border-slate-300 dark:border-slate-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-rose-500">
                    <option value="">Todos</option>
                    @foreach(($productos ?? []) as $p)
                        <option value="{{ $p->id }}" {{ (string)request('producto_id') === (string)$p->id ? 'selected' : '' }}>{{ $p->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-tight">Usuario</label>
                <select name="user_id"
                        class="mt-1 w-full px-3 py-2 bg-white dark:bg-gray-700 border border-slate-300 dark:border-slate-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-rose-500">
                    <option value="">Todos</option>
                    @foreach(($usuarios ?? []) as $u)
                        <option value="{{ $u->id }}" {{ (string)request('user_id') === (string)$u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-1">
                <label class="block text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-tight">Lote ID</label>
                <input type="number" min="1" name="lote_id" value="{{ request('lote_id') }}"
                       class="mt-1 w-full px-3 py-2 bg-white dark:bg-gray-700 border border-slate-300 dark:border-slate-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-rose-500" />
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-tight">Origen</label>
                <input type="text" name="origen" value="{{ request('origen') }}" placeholder="venta / compra / ajuste"
                       class="mt-1 w-full px-3 py-2 bg-white dark:bg-gray-700 border border-slate-300 dark:border-slate-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-rose-500" />
            </div>

            <div class="md:col-span-12">
                <label class="block text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-tight">Motivo (contiene)</label>
                <input type="text" name="motivo" value="{{ request('motivo') }}" placeholder="ej: conteo, daño, ajuste manual"
                       class="mt-1 w-full px-3 py-2 bg-white dark:bg-gray-700 border border-slate-300 dark:border-slate-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-rose-500" />
            </div>

            <div class="md:col-span-12 flex flex-col md:flex-row gap-2 md:items-end md:justify-end mt-1">
                <button type="submit"
                        class="inline-flex w-full md:w-auto justify-center items-center px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-extrabold rounded-lg shadow-sm">
                    Aplicar
                </button>

                <button type="button"
                        onclick="document.getElementById('export').value='pdf'; document.getElementById('formFiltrosAjustes').submit();"
                        class="inline-flex w-full md:w-auto justify-center items-center px-4 py-2 bg-white dark:bg-gray-800 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 font-bold rounded-lg hover:bg-slate-50 dark:hover:bg-gray-700 shadow-sm">
                    PDF
                </button>

                <button type="button"
                        onclick="document.getElementById('export').value='excel'; document.getElementById('formFiltrosAjustes').submit();"
                        class="inline-flex w-full md:w-auto justify-center items-center px-4 py-2 bg-white dark:bg-gray-800 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 font-bold rounded-lg hover:bg-slate-50 dark:hover:bg-gray-700 shadow-sm">
                    Excel
                </button>

                @if(request()->hasAny(['fecha_inicio','fecha_fin','producto_id','user_id','lote_id','origen','motivo']))
                    <a href="{{ route('reportes.ajustes') }}"
                       class="inline-flex w-full md:w-auto justify-center items-center px-4 py-2 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300 font-bold rounded-lg hover:bg-rose-100 dark:hover:bg-rose-900/30 shadow-sm">
                        Limpiar
                    </a>
                @endif
            </div>
        </form>

        <p class="mt-3 text-xs text-slate-500 dark:text-slate-400">Exportación: PDF (tabla) / Excel (CSV). Primero revisa la vista previa y luego exporta.</p>
    </div>

    {{-- KPIs --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-4">
        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
            <p class="text-xs font-black text-slate-600 dark:text-slate-400 uppercase tracking-tight">Cantidad ajustes</p>
            <p class="mt-1 text-2xl font-extrabold text-rose-600 dark:text-rose-300">{{ (int)($cantidadMovimientos ?? 0) }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
            <p class="text-xs font-black text-slate-600 dark:text-slate-400 uppercase tracking-tight">Neto unidades</p>
            <p class="mt-1 text-2xl font-extrabold text-indigo-600 dark:text-indigo-300">{{ number_format($neto, 0) }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
            <p class="text-xs font-black text-slate-600 dark:text-slate-400 uppercase tracking-tight">Aumentos</p>
            <p class="mt-1 text-2xl font-extrabold text-emerald-600 dark:text-emerald-300">+{{ number_format($aumentos, 0) }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
            <p class="text-xs font-black text-slate-600 dark:text-slate-400 uppercase tracking-tight">Reducciones</p>
            <p class="mt-1 text-2xl font-extrabold text-amber-600 dark:text-amber-300">-{{ number_format($reducciones, 0) }}</p>
        </div>
    </div>

    {{-- Gráficos --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 mb-4">
        <div class="xl:col-span-2 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-black text-slate-700 dark:text-slate-300 uppercase tracking-tight">Ajustes por motivo</h3>
                <span class="text-xs text-slate-500 dark:text-slate-400">Top 10</span>
            </div>
            <div class="mt-3 h-64">
                <canvas id="chartAjustesMotivo"></canvas>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-black text-slate-700 dark:text-slate-300 uppercase tracking-tight">Ajustes por usuario</h3>
                <span class="text-xs text-slate-500 dark:text-slate-400">Top 10</span>
            </div>
            <div class="mt-3 h-64">
                <canvas id="chartAjustesUsuario"></canvas>
            </div>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-gray-800 shadow-sm p-5">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-black text-slate-700 dark:text-slate-300 uppercase tracking-tight">Detalle de ajustes</h3>
            <span class="text-xs text-slate-500 dark:text-slate-400">{{ (int)($cantidadMovimientos ?? 0) }} registros</span>
        </div>

        <div class="mt-3 overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 dark:bg-gray-900/40">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-black text-slate-600 dark:text-slate-400 uppercase tracking-tight">Fecha</th>
                        <th class="px-3 py-2 text-left text-xs font-black text-slate-600 dark:text-slate-400 uppercase tracking-tight">Producto</th>
                        <th class="px-3 py-2 text-left text-xs font-black text-slate-600 dark:text-slate-400 uppercase tracking-tight">Lote</th>
                        <th class="px-3 py-2 text-right text-xs font-black text-slate-600 dark:text-slate-400 uppercase tracking-tight">Cantidad</th>
                        <th class="px-3 py-2 text-right text-xs font-black text-slate-600 dark:text-slate-400 uppercase tracking-tight">Saldo ant.</th>
                        <th class="px-3 py-2 text-right text-xs font-black text-slate-600 dark:text-slate-400 uppercase tracking-tight">Saldo nuevo</th>
                        <th class="px-3 py-2 text-left text-xs font-black text-slate-600 dark:text-slate-400 uppercase tracking-tight">Origen</th>
                        <th class="px-3 py-2 text-left text-xs font-black text-slate-600 dark:text-slate-400 uppercase tracking-tight">Motivo</th>
                        <th class="px-3 py-2 text-left text-xs font-black text-slate-600 dark:text-slate-400 uppercase tracking-tight">Usuario</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    @foreach(($movimientos ?? []) as $m)
                        <tr class="hover:bg-slate-50 dark:hover:bg-gray-700/40">
                            <td class="px-3 py-2 whitespace-nowrap text-slate-700 dark:text-slate-200">
                                {{ $m->created_at ? $m->created_at->format('Y-m-d H:i') : '—' }}
                            </td>
                            <td class="px-3 py-2 text-slate-700 dark:text-slate-200">{{ $m->producto?->nombre ?? '—' }}</td>
                            <td class="px-3 py-2 text-slate-700 dark:text-slate-200">{{ $m->lote?->numero_lote ?? ($m->lote_id ? ('#'.$m->lote_id) : '—') }}</td>
                            <td class="px-3 py-2 text-right font-bold {{ (int)$m->cantidad >= 0 ? 'text-emerald-700 dark:text-emerald-300' : 'text-rose-700 dark:text-rose-300' }}">
                                {{ (int)$m->cantidad }}
                            </td>
                            <td class="px-3 py-2 text-right text-slate-700 dark:text-slate-200">{{ (int)($m->saldo_anterior ?? 0) }}</td>
                            <td class="px-3 py-2 text-right text-slate-700 dark:text-slate-200">{{ (int)($m->saldo_nuevo ?? 0) }}</td>
                            <td class="px-3 py-2 text-slate-700 dark:text-slate-200">
                                {{ $m->origen ?? '—' }}{{ $m->origen_id ? (' #'.$m->origen_id) : '' }}
                            </td>
                            <td class="px-3 py-2 text-slate-700 dark:text-slate-200">{{ $m->motivo ?? '—' }}</td>
                            <td class="px-3 py-2 text-slate-700 dark:text-slate-200">{{ $m->usuario?->name ?? '—' }}</td>
                        </tr>
                    @endforeach

                    @if(empty($movimientos) || count($movimientos) === 0)
                        <tr>
                            <td colspan="9" class="px-3 py-8 text-center text-slate-500 dark:text-slate-400">
                                No hay ajustes con esos filtros.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- Charts --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof Chart === 'undefined') return;

        const labelsMotivos = @json($labelsMotivos);
        const dataMotivos = @json($cntMotivos);

        const labelsUsuarios = @json($labelsUsuarios);
        const dataUsuarios = @json($cntUsuarios);

        const ctx1 = document.getElementById('chartAjustesMotivo');
        if (ctx1) {
            new Chart(ctx1, {
                type: 'bar',
                data: { labels: labelsMotivos, datasets: [{ label: 'Ajustes', data: dataMotivos }] },
                options: { responsive: true, maintainAspectRatio: false }
            });
        }

        const ctx2 = document.getElementById('chartAjustesUsuario');
        if (ctx2) {
            new Chart(ctx2, {
                type: 'bar',
                data: { labels: labelsUsuarios, datasets: [{ label: 'Ajustes', data: dataUsuarios }] },
                options: { responsive: true, maintainAspectRatio: false }
            });
        }
    });
</script>
@endsection
