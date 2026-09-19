@extends('layouts.app')

@section('title', 'Reporte de Proveedores y Compras - FarmaBien')

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
                <span class="text-slate-800 dark:text-slate-200 font-semibold">Proveedores &amp; Compras</span>
            </nav>
            <div class="flex items-center space-x-2">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300">
                    Gestión de Abastecimiento
                </span>
                <span class="text-xs text-slate-400">&bull;</span>
                <span class="text-xs text-slate-500 dark:text-slate-400">Órdenes Recibidas</span>
            </div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white mt-1">Reporte de Proveedores y Compras</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Control de adquisiciones, análisis de proveedores y montos por período.
            </p>
        </div>

        <div class="flex items-center space-x-2 flex-wrap">
            <a href="{{ route('reportes.compras', array_merge(request()->query(), ['export' => 'csv'])) }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:border-amber-400 dark:hover:border-amber-500 hover:text-amber-700 dark:hover:text-amber-400 transition-colors shadow-sm print:hidden">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                CSV Excel
            </a>
            <button onclick="window.print()"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:border-slate-400 transition-colors shadow-sm print:hidden">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Imprimir
            </button>
        </div>
    </div>

    <!-- Filtros de Fecha -->
    <div class="bg-white dark:bg-slate-800/60 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-4 print:hidden">
        <form method="GET" action="{{ route('reportes.compras') }}" class="flex flex-wrap items-end gap-3">
            <div>
                <label for="rpc_desde" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Desde</label>
                <input type="date" id="rpc_desde" name="fecha_desde" value="{{ $fechaDesde }}"
                       class="px-3 py-1.5 rounded-lg text-xs border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-amber-400">
            </div>
            <div>
                <label for="rpc_hasta" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Hasta</label>
                <input type="date" id="rpc_hasta" name="fecha_hasta" value="{{ $fechaHasta }}"
                       class="px-3 py-1.5 rounded-lg text-xs border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-amber-400">
            </div>
            <!-- Accesos rápidos de fecha -->
            <div class="flex gap-1.5 flex-wrap">
                @php
                    $presets = [
                        'hoy'    => ['Hoy',         now()->toDateString(),           now()->toDateString()],
                        '7d'     => ['Últ. 7 días', now()->subDays(6)->toDateString(), now()->toDateString()],
                        'mes'    => ['Este mes',    now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()],
                        'mesant' => ['Mes ant.',    now()->subMonth()->startOfMonth()->toDateString(), now()->subMonth()->endOfMonth()->toDateString()],
                        'año'    => ['Este año',    now()->startOfYear()->toDateString(), now()->toDateString()],
                    ];
                @endphp
                @foreach($presets as [$label, $desde, $hasta])
                    <a href="{{ route('reportes.compras', ['fecha_desde' => $desde, 'fecha_hasta' => $hasta]) }}"
                       class="px-2.5 py-1.5 rounded-lg text-xs font-medium border transition-colors
                              {{ ($fechaDesde === $desde && $fechaHasta === $hasta) ? 'bg-amber-500 text-white border-amber-500' : 'bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:border-amber-400' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
            <button type="submit"
                    class="px-4 py-1.5 rounded-lg text-xs font-semibold bg-amber-500 hover:bg-amber-600 text-white transition-colors shadow-sm">
                Aplicar filtros
            </button>
        </form>
    </div>

    <!-- KPIs -->
    @php
        $cantOrdenes = $compras->total();
        $promedioOrden = $cantOrdenes > 0 ? $totalComprado / $cantOrdenes : 0;
        // Proveedor líder
        $proveedorLider = \App\Models\Compra::whereBetween(\Illuminate\Support\Facades\DB::raw('DATE(fecha)'), [$fechaDesde, $fechaHasta])
            ->recibidas()
            ->join('proveedores', 'compras.proveedor_id', '=', 'proveedores.id')
            ->select('proveedores.nombre', \Illuminate\Support\Facades\DB::raw('SUM(compras.total) as monto_total'))
            ->groupBy('proveedores.id', 'proveedores.nombre')
            ->orderByDesc('monto_total')
            ->first();
    @endphp
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Comprado -->
        <div class="bg-white dark:bg-slate-800/60 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Comprado</span>
                <span class="w-8 h-8 rounded-xl bg-amber-100 dark:bg-amber-950/50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </span>
            </div>
            <div class="text-2xl font-bold text-slate-900 dark:text-white">${{ number_format($totalComprado, 2) }}</div>
            <div class="text-xs text-slate-400 mt-0.5">Período seleccionado</div>
        </div>
        <!-- N° Órdenes -->
        <div class="bg-white dark:bg-slate-800/60 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400">Órdenes</span>
                <span class="w-8 h-8 rounded-xl bg-indigo-100 dark:bg-indigo-950/50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </span>
            </div>
            <div class="text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($cantOrdenes) }}</div>
            <div class="text-xs text-slate-400 mt-0.5">Órdenes recibidas</div>
        </div>
        <!-- Proveedor Líder -->
        <div class="bg-white dark:bg-slate-800/60 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400">Proveedor Líder</span>
                <span class="w-8 h-8 rounded-xl bg-emerald-100 dark:bg-emerald-950/50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </span>
            </div>
            <div class="text-sm font-bold text-slate-900 dark:text-white truncate">{{ $proveedorLider?->nombre ?? '—' }}</div>
            <div class="text-xs text-slate-400 mt-0.5">
                @if($proveedorLider)
                    ${{ number_format($proveedorLider->monto_total, 2) }} acumulado
                @else
                    Sin datos
                @endif
            </div>
        </div>
        <!-- Promedio por Orden -->
        <div class="bg-white dark:bg-slate-800/60 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400">Prom. por Orden</span>
                <span class="w-8 h-8 rounded-xl bg-rose-100 dark:bg-rose-950/50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </span>
            </div>
            <div class="text-2xl font-bold text-slate-900 dark:text-white">${{ number_format($promedioOrden, 2) }}</div>
            <div class="text-xs text-slate-400 mt-0.5">Ticket promedio</div>
        </div>
    </div>

    <!-- Tabla de Compras -->
    <div class="bg-white dark:bg-slate-800/60 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-bold text-slate-900 dark:text-white">Órdenes de Compra Recibidas</h2>
                <p class="text-xs text-slate-400">{{ number_format($compras->total()) }} registros — {{ \Carbon\Carbon::parse($fechaDesde)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($fechaHasta)->format('d/m/Y') }}</p>
            </div>
        </div>

        @if($compras->isEmpty())
            <div class="flex flex-col items-center justify-center py-16 text-center">
                <div class="w-14 h-14 rounded-2xl bg-slate-100 dark:bg-slate-700 flex items-center justify-center mb-3">
                    <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                <p class="text-sm font-semibold text-slate-600 dark:text-slate-400">Sin compras en este período</p>
                <p class="text-xs text-slate-400 mt-1">Ajusta el rango de fechas para ver resultados.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-900/40 text-left">
                            <th class="px-4 py-2.5 font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400">N° Orden</th>
                            <th class="px-4 py-2.5 font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400">Proveedor</th>
                            <th class="px-4 py-2.5 font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400">N° Factura</th>
                            <th class="px-4 py-2.5 font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400">Fecha</th>
                            <th class="px-4 py-2.5 font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400">Responsable</th>
                            <th class="px-4 py-2.5 font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400">Estado</th>
                            <th class="px-4 py-2.5 font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400 text-right">Total</th>
                            <th class="px-4 py-2.5 font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400 print:hidden"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                        @foreach($compras as $compra)
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/30 transition-colors">
                                <td class="px-4 py-2.5">
                                    <span class="font-bold text-slate-800 dark:text-slate-200">#{{ str_pad($compra->id, 4, '0', STR_PAD_LEFT) }}</span>
                                </td>
                                <td class="px-4 py-2.5">
                                    <div class="font-semibold text-slate-800 dark:text-slate-200">{{ $compra->proveedor?->nombre ?? '—' }}</div>
                                    @if($compra->proveedor?->rif)
                                        <div class="text-slate-400">{{ $compra->proveedor->rif }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-2.5 text-slate-600 dark:text-slate-300">
                                    {{ $compra->numero_factura ?? '—' }}
                                </td>
                                <td class="px-4 py-2.5 text-slate-600 dark:text-slate-300 whitespace-nowrap">
                                    {{ $compra->fecha ? \Carbon\Carbon::parse($compra->fecha)->format('d/m/Y') : '—' }}
                                </td>
                                <td class="px-4 py-2.5 text-slate-600 dark:text-slate-300">
                                    {{ $compra->usuario?->name ?? 'Sistema' }}
                                </td>
                                <td class="px-4 py-2.5">
                                    @php
                                        $estadoClasses = match($compra->estado ?? '') {
                                            'recibida'   => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300',
                                            'pendiente'  => 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300',
                                            'parcial'    => 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300',
                                            'cancelada'  => 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300',
                                            default      => 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300',
                                        };
                                    @endphp
                                    <span class="px-2 py-0.5 rounded-full font-semibold {{ $estadoClasses }}">
                                        {{ ucfirst($compra->estado ?? 'N/A') }}
                                    </span>
                                </td>
                                <td class="px-4 py-2.5 text-right font-bold text-slate-800 dark:text-slate-200 whitespace-nowrap">
                                    ${{ number_format($compra->total, 2) }}
                                </td>
                                <td class="px-4 py-2.5 print:hidden">
                                    @can('ver compras')
                                        <a href="{{ route('compras.show', $compra) }}"
                                           class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-amber-50 dark:bg-amber-950/30 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800 hover:bg-amber-100 dark:hover:bg-amber-900/50 transition-colors">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            Ver
                                        </a>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <!-- Total -->
                    <tfoot>
                        <tr class="bg-amber-50 dark:bg-amber-950/20 border-t-2 border-amber-200 dark:border-amber-800">
                            <td colspan="6" class="px-4 py-2.5 font-extrabold text-xs uppercase tracking-wider text-amber-700 dark:text-amber-400">
                                Total del período ({{ number_format($compras->total()) }} órdenes)
                            </td>
                            <td class="px-4 py-2.5 text-right font-extrabold text-amber-700 dark:text-amber-400 whitespace-nowrap">
                                ${{ number_format($totalComprado, 2) }}
                            </td>
                            <td class="print:hidden"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Paginación -->
            @if($compras->hasPages())
                <div class="px-5 py-3 border-t border-slate-100 dark:border-slate-700 print:hidden">
                    {{ $compras->links() }}
                </div>
            @endif
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
