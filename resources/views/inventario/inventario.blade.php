@extends('layouts.app')

@section('title', 'Reporte de Inventario')

@section('header')
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-black text-slate-900 dark:text-white">Inventario</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">Resumen por categoría, stock bajo, lotes y vencimientos</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('reportes.inventario', array_merge(request()->all(), ['export' => 'pdf'])) }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-slate-700 hover:bg-slate-800 text-white font-black">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z"/></svg>
                PDF
            </a>
            <a href="{{ route('reportes.inventario', array_merge(request()->all(), ['export' => 'excel'])) }}"
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
                <label class="block text-xs font-black uppercase text-slate-600 dark:text-slate-300 mb-1">Categoría</label>
                <select name="categoria_id" class="w-full px-3 py-2 rounded-lg bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-slate-900 dark:text-white">
                    <option value="">Todas</option>
                    @foreach($categorias as $cat)
                        <option value="{{ $cat->id }}" @selected((string)$categoriaId === (string)$cat->id)>{{ $cat->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-3">
                <label class="block text-xs font-black uppercase text-slate-600 dark:text-slate-300 mb-1">Días para vencer</label>
                <input type="number" min="1" name="dias" value="{{ $dias }}"
                    class="w-full px-3 py-2 rounded-lg bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-slate-900 dark:text-white" />
            </div>

            <div class="md:col-span-5">
                <label class="block text-xs font-black uppercase text-slate-600 dark:text-slate-300 mb-1">Buscar producto</label>
                <input type="text" name="q" value="{{ $q }}" placeholder="Nombre del producto..."
                    class="w-full px-3 py-2 rounded-lg bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-slate-900 dark:text-white" />
            </div>

            <div class="md:col-span-12 flex items-end gap-2">
                <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-black">
                    Ver / Actualizar
                </button>
                <a href="{{ route('reportes.inventario') }}" class="px-4 py-2 rounded-lg bg-slate-200 hover:bg-slate-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-slate-900 dark:text-white font-black">
                    Limpiar
                </a>
            </div>
        </form>
    </div>

    {{-- KPIs / Resumen rápido --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
            <div class="text-xs font-black uppercase text-slate-500 dark:text-slate-400">Valor inventario (estimado)</div>
            <div class="mt-2 text-xl font-black text-slate-900 dark:text-white">C$ {{ number_format((float)($valorizacion['valor_total'] ?? 0), 2) }}</div>
            <div class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">Unidades: {{ (int)($valorizacion['total_unidades'] ?? 0) }} · Productos: {{ (int)($valorizacion['total_productos'] ?? 0) }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
            <div class="text-xs font-black uppercase text-slate-500 dark:text-slate-400">Stock bajo</div>
            <div class="mt-2 text-xl font-black text-amber-600 dark:text-amber-400">{{ $productosStockBajo->count() }}</div>
            <div class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">Productos marcados como bajo stock</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
            <div class="text-xs font-black uppercase text-slate-500 dark:text-slate-400">Próximos a vencer</div>
            <div class="mt-2 text-xl font-black text-violet-700 dark:text-violet-300">{{ $lotesProximosVencer->count() }}</div>
            <div class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">En {{ $dias }} días</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
            <div class="text-xs font-black uppercase text-slate-500 dark:text-slate-400">Vencidos (con stock)</div>
            <div class="mt-2 text-xl font-black text-red-600 dark:text-red-400">{{ $lotesVencidos->count() }}</div>
            <div class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">Lotes vencidos activos</div>
        </div>
    </div>

    {{-- Gráficos (preview) --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-base font-black text-slate-900 dark:text-white">Stock total por categoría</h3>
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400">Preview</span>
            </div>
            <div class="relative h-64"><canvas id="categoriaBar"></canvas></div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-base font-black text-slate-900 dark:text-white">Valor por categoría</h3>
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400">Preview</span>
            </div>
            <div class="relative h-64"><canvas id="categoriaPie"></canvas></div>
        </div>
    </div>

    {{-- TABLA: Resumen por categoría --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 mb-6">
        <div class="p-5">
            <h3 class="text-lg font-black text-slate-900 dark:text-white">Resumen por categoría</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400">Productos activos y stock acumulado (lotes activos)</p>
        </div>
        <div class="px-5 pb-5 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700/60 sticky top-0">
                    <tr>
                        <th class="px-4 py-3 text-left font-black uppercase text-xs text-slate-600 dark:text-slate-200">Categoría</th>
                        <th class="px-4 py-3 text-right font-black uppercase text-xs text-slate-600 dark:text-slate-200">Productos</th>
                        <th class="px-4 py-3 text-right font-black uppercase text-xs text-slate-600 dark:text-slate-200">Stock</th>
                        <th class="px-4 py-3 text-right font-black uppercase text-xs text-slate-600 dark:text-slate-200">Valor</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($resumenCategorias as $r)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                            <td class="px-4 py-3 font-semibold text-slate-900 dark:text-white">{{ $r->categoria_nombre }}</td>
                            <td class="px-4 py-3 text-right text-slate-700 dark:text-slate-200">{{ (int)$r->total_productos }}</td>
                            <td class="px-4 py-3 text-right font-bold text-slate-900 dark:text-white">{{ number_format((float)$r->stock_total, 2) }}</td>
                            <td class="px-4 py-3 text-right font-black text-emerald-600 dark:text-emerald-400">C$ {{ number_format((float)$r->valor_total, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-6 text-center text-slate-500 dark:text-slate-400">Sin datos</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- TABLA: Productos con stock bajo --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 mb-6">
        <div class="p-5">
            <h3 class="text-lg font-black text-slate-900 dark:text-white">Productos con stock bajo</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400">Según umbral configurado en el producto</p>
        </div>
        <div class="px-5 pb-5 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700/60 sticky top-0">
                    <tr>
                        <th class="px-4 py-3 text-left font-black uppercase text-xs text-slate-600 dark:text-slate-200">Producto</th>
                        <th class="px-4 py-3 text-left font-black uppercase text-xs text-slate-600 dark:text-slate-200">Categoría</th>
                        <th class="px-4 py-3 text-right font-black uppercase text-xs text-slate-600 dark:text-slate-200">Stock</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($productosStockBajo as $p)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                            <td class="px-4 py-3 font-semibold text-slate-900 dark:text-white">{{ $p->nombre }}</td>
                            <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ optional($p->categoria)->nombre ?? '—' }}</td>
                            <td class="px-4 py-3 text-right font-black text-amber-600 dark:text-amber-400">{{ number_format((float)($p->stock_disponible ?? $p->stock_total ?? 0), 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-4 py-6 text-center text-slate-500 dark:text-slate-400">Sin productos en bajo stock</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- TABLA: Lotes próximos a vencer --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 mb-6">
        <div class="p-5">
            <h3 class="text-lg font-black text-slate-900 dark:text-white">Lotes próximos a vencer</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400">Con stock mayor a 0, dentro de {{ $dias }} días</p>
        </div>
        <div class="px-5 pb-5 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700/60 sticky top-0">
                    <tr>
                        <th class="px-4 py-3 text-left font-black uppercase text-xs text-slate-600 dark:text-slate-200">Producto</th>
                        <th class="px-4 py-3 text-left font-black uppercase text-xs text-slate-600 dark:text-slate-200">Proveedor</th>
                        <th class="px-4 py-3 text-left font-black uppercase text-xs text-slate-600 dark:text-slate-200">Lote</th>
                        <th class="px-4 py-3 text-left font-black uppercase text-xs text-slate-600 dark:text-slate-200">Vence</th>
                        <th class="px-4 py-3 text-right font-black uppercase text-xs text-slate-600 dark:text-slate-200">Días</th>
                        <th class="px-4 py-3 text-right font-black uppercase text-xs text-slate-600 dark:text-slate-200">Stock</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($lotesProximosVencer as $l)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                            <td class="px-4 py-3 font-semibold text-slate-900 dark:text-white">{{ optional($l->producto)->nombre ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ optional($l->proveedor)->nombre ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ $l->numero_lote }}</td>
                            <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ optional($l->fecha_vencimiento)->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 text-right font-bold text-violet-700 dark:text-violet-300">{{ (int)($l->dias_para_vencer ?? 0) }}</td>
                            <td class="px-4 py-3 text-right font-black text-slate-900 dark:text-white">{{ number_format((float)($l->stock_disponible ?? $l->stock_actual ?? 0), 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-6 text-center text-slate-500 dark:text-slate-400">Sin lotes próximos a vencer</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- TABLA: Lotes vencidos --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 mb-6">
        <div class="p-5">
            <h3 class="text-lg font-black text-slate-900 dark:text-white">Lotes vencidos</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400">Lotes vencidos con stock mayor a 0</p>
        </div>
        <div class="px-5 pb-5 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700/60 sticky top-0">
                    <tr>
                        <th class="px-4 py-3 text-left font-black uppercase text-xs text-slate-600 dark:text-slate-200">Producto</th>
                        <th class="px-4 py-3 text-left font-black uppercase text-xs text-slate-600 dark:text-slate-200">Proveedor</th>
                        <th class="px-4 py-3 text-left font-black uppercase text-xs text-slate-600 dark:text-slate-200">Lote</th>
                        <th class="px-4 py-3 text-left font-black uppercase text-xs text-slate-600 dark:text-slate-200">Vence</th>
                        <th class="px-4 py-3 text-right font-black uppercase text-xs text-slate-600 dark:text-slate-200">Stock</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($lotesVencidos as $l)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                            <td class="px-4 py-3 font-semibold text-slate-900 dark:text-white">{{ optional($l->producto)->nombre ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ optional($l->proveedor)->nombre ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ $l->numero_lote }}</td>
                            <td class="px-4 py-3 text-red-600 dark:text-red-400 font-bold">{{ optional($l->fecha_vencimiento)->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 text-right font-black text-red-600 dark:text-red-400">{{ number_format((float)($l->stock_disponible ?? $l->stock_actual ?? 0), 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-6 text-center text-slate-500 dark:text-slate-400">Sin lotes vencidos con stock</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- TABLA: Valorización detalle (top) --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
        <div class="p-5">
            <h3 class="text-lg font-black text-slate-900 dark:text-white">Valorización (detalle)</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400">Top por valor (lotes activos con stock)</p>
        </div>
        <div class="px-5 pb-5 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700/60 sticky top-0">
                    <tr>
                        <th class="px-4 py-3 text-left font-black uppercase text-xs text-slate-600 dark:text-slate-200">Producto</th>
                        <th class="px-4 py-3 text-left font-black uppercase text-xs text-slate-600 dark:text-slate-200">Lote</th>
                        <th class="px-4 py-3 text-right font-black uppercase text-xs text-slate-600 dark:text-slate-200">Stock</th>
                        <th class="px-4 py-3 text-right font-black uppercase text-xs text-slate-600 dark:text-slate-200">Precio compra</th>
                        <th class="px-4 py-3 text-right font-black uppercase text-xs text-slate-600 dark:text-slate-200">Valor</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse(($valorizacion['detalles'] ?? []) as $d)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                            <td class="px-4 py-3 font-semibold text-slate-900 dark:text-white">{{ $d['producto'] }}</td>
                            <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ $d['lote'] }}</td>
                            <td class="px-4 py-3 text-right font-bold text-slate-900 dark:text-white">{{ number_format((float)$d['stock'], 2) }}</td>
                            <td class="px-4 py-3 text-right text-slate-700 dark:text-slate-200">C$ {{ number_format((float)$d['precio_compra'], 2) }}</td>
                            <td class="px-4 py-3 text-right font-black text-emerald-600 dark:text-emerald-400">C$ {{ number_format((float)$d['valor_total'], 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-6 text-center text-slate-500 dark:text-slate-400">Sin datos</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        (function () {
            const rows = @json($resumenCategorias);
            const labels = rows.map(r => r.categoria_nombre);
            const stock = rows.map(r => Number(r.stock_total || 0));
            const valor = rows.map(r => Number(r.valor_total || 0));

            const barCtx = document.getElementById('categoriaBar');
            if (barCtx) {
                new Chart(barCtx, {
                    type: 'bar',
                    data: { labels, datasets: [{ label: 'Stock', data: stock }] },
                    options: { responsive: true, maintainAspectRatio: false }
                });
            }

            const pieCtx = document.getElementById('categoriaPie');
            if (pieCtx) {
                new Chart(pieCtx, {
                    type: 'pie',
                    data: { labels, datasets: [{ label: 'Valor', data: valor }] },
                    options: { responsive: true, maintainAspectRatio: false }
                });
            }
        })();
    </script>
@endpush
