@extends('layouts.app')

@section('title', 'Dashboard de Inventario')

@section('header')
    Inventario
@endsection

@section('page-actions')
    <div>
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white">
            Dashboard de Inventario
        </h2>
        <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
            Estado general, valorización, stock bajo y vencimientos.
        </p>
    </div>
    <div class="flex flex-wrap gap-3">
        @can('ajustar inventario')
        <a href="{{ route('inventario.ajustar') }}"
           class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-yellow-500 to-yellow-600 hover:from-yellow-600 hover:to-yellow-700 text-white font-semibold rounded-lg shadow-sm transition-all duration-200 hover:shadow-md">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Ajustar
        </a>
        @endcan

        <a href="{{ route('inventario.lotes') }}"
           class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-slate-700 dark:text-slate-300 font-semibold rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200 shadow-sm">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
            Ver lotes
        </a>

        <a href="{{ route('inventario.alertas') }}"
           class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-semibold rounded-lg shadow-sm transition-all duration-200 hover:shadow-md">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            Alertas
        </a>
    </div>
@endsection

@section('content')
<div class="space-y-6">

    <!-- KPIs -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-xl border border-gray-200 dark:border-gray-700">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600 dark:text-slate-400">Valor total inventario</p>
                        <p class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">
                            {{ number_format((float)($valorizacion['valor_total'] ?? 0), 2) }}
                        </p>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Basado en lotes con stock_actual &gt; 0</p>
                    </div>
                    <div class="p-3 rounded-xl bg-blue-100 dark:bg-blue-900/30">
                        <svg class="w-7 h-7 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 8c-1.657 0-3 1.343-3 3v8h6v-8c0-1.657-1.343-3-3-3z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M7 8V7a5 5 0 0110 0v1"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-xl border border-gray-200 dark:border-gray-700">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600 dark:text-slate-400">Unidades totales</p>
                        <p class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">
                            {{ number_format((int)($valorizacion['total_unidades'] ?? 0)) }}
                        </p>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Suma de stock_actual en lotes activos</p>
                    </div>
                    <div class="p-3 rounded-xl bg-emerald-100 dark:bg-emerald-900/30">
                        <svg class="w-7 h-7 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M20 13V7a2 2 0 00-2-2H6a2 2 0 00-2 2v6m16 0H4m16 0v6a2 2 0 01-2 2H6a2 2 0 01-2-2v-6"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-xl border border-gray-200 dark:border-gray-700">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600 dark:text-slate-400">Productos con stock</p>
                        <p class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">
                            {{ number_format((int)($valorizacion['total_productos'] ?? 0)) }}
                        </p>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Distintos productos con stock en lotes</p>
                    </div>
                    <div class="p-3 rounded-xl bg-purple-100 dark:bg-purple-900/30">
                        <svg class="w-7 h-7 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M20 13V7a2 2 0 00-2-2H6a2 2 0 00-2 2v6m16 0H4m16 0v6a2 2 0 01-2 2H6a2 2 0 01-2-2v-6"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-xl border border-gray-200 dark:border-gray-700">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600 dark:text-slate-400">Lotes activos con stock</p>
                        <p class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">
                            {{ number_format((int)($valorizacion['total_lotes_activos'] ?? 0)) }}
                        </p>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Lotes activos con stock_actual &gt; 0</p>
                    </div>
                    <div class="p-3 rounded-xl bg-amber-100 dark:bg-amber-900/30">
                        <svg class="w-7 h-7 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 17v-2a4 4 0 014-4h4m0 0l-3-3m3 3l-3 3"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 7a2 2 0 012-2h6a2 2 0 012 2v2H3V7z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen por categorías -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-xl border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-gray-50 to-white dark:from-gray-800 dark:to-gray-800/50">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Resumen por categoría</h3>
            <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">Stock y valoración acumulada por categoría.</p>
        </div>
        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Categoría</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Productos</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Stock total</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Valor</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($resumenCategorias as $fila)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-4 py-3 text-sm text-slate-900 dark:text-white">{{ $fila->categoria_nombre }}</td>
                                <td class="px-4 py-3 text-sm text-right text-slate-700 dark:text-slate-200">{{ number_format((int)$fila->total_productos) }}</td>
                                <td class="px-4 py-3 text-sm text-right text-slate-700 dark:text-slate-200">{{ number_format((int)$fila->stock_total) }}</td>
                                <td class="px-4 py-3 text-sm text-right text-slate-900 dark:text-white font-semibold">{{ number_format((float)$fila->valor_total, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-slate-500 dark:text-slate-400">No hay datos para mostrar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Stock bajo + Vencimientos -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

        <!-- Productos con stock bajo -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-xl border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-yellow-50 to-white dark:from-gray-800 dark:to-gray-800/50">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Productos con stock bajo</h3>
                        <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">Productos activos bajo su stock mínimo.</p>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300">
                        {{ $productosStockBajo->count() }}
                    </span>
                </div>
            </div>
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Producto</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Stock</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Mínimo</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($productosStockBajo as $producto)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-4 py-3 text-sm">
                                        <div class="font-semibold text-slate-900 dark:text-white">{{ $producto->nombre }}</div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400">{{ $producto->categoria?->nombre }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-right text-red-600 dark:text-red-400 font-semibold">{{ number_format((int)$producto->stock_total) }}</td>
                                    <td class="px-4 py-3 text-sm text-right text-slate-700 dark:text-slate-200">{{ number_format((int)$producto->stock_minimo) }}</td>
                                    <td class="px-4 py-3 text-sm text-right">
                                        <a href="{{ route('inventario.kardex-producto', $producto) }}"
                                           class="inline-flex items-center px-3 py-1.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors shadow-sm">
                                            Ver kardex
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-6 text-center text-slate-500 dark:text-slate-400">No hay productos con stock bajo.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Lotes próximos a vencer / vencidos -->
        <div class="space-y-6">

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-xl border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-orange-50 to-white dark:from-gray-800 dark:to-gray-800/50">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Próximos a vencer</h3>
                            <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">Lotes con stock y vencimiento cercano.</p>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-300">
                            {{ $lotesProximosVencer->count() }}
                        </span>
                    </div>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        @forelse($lotesProximosVencer->take(6) as $lote)
                            <a href="{{ route('inventario.show-lote', $lote) }}"
                               class="block p-4 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <div class="font-semibold text-slate-900 dark:text-white">{{ $lote->producto?->nombre }}</div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Lote: <span class="font-mono">{{ $lote->numero_lote }}</span></div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-sm font-semibold text-slate-900 dark:text-white">{{ number_format((int)$lote->stock_actual) }}</div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400">Stock</div>
                                    </div>
                                </div>
                                <div class="mt-3 flex items-center justify-between text-xs text-slate-600 dark:text-slate-400">
                                    <span>Vence: {{ optional($lote->fecha_vencimiento)->format('d/m/Y') }}</span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-300 font-semibold">
                                        {{ $lote->dias_para_vencer ?? '' }} días
                                    </span>
                                </div>
                            </a>
                        @empty
                            <p class="text-slate-500 dark:text-slate-400 text-sm">No hay lotes próximos a vencer.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-xl border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-red-50 to-white dark:from-gray-800 dark:to-gray-800/50">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Vencidos con stock</h3>
                            <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">Lotes vencidos que aún tienen unidades.</p>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300">
                            {{ $lotesVencidos->count() }}
                        </span>
                    </div>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        @forelse($lotesVencidos->take(6) as $lote)
                            <a href="{{ route('inventario.show-lote', $lote) }}"
                               class="block p-4 rounded-lg border border-red-200 dark:border-red-900/40 bg-red-50 dark:bg-red-900/10 hover:bg-red-100/60 dark:hover:bg-red-900/20 transition">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <div class="font-semibold text-slate-900 dark:text-white">{{ $lote->producto?->nombre }}</div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Lote: <span class="font-mono">{{ $lote->numero_lote }}</span></div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-sm font-semibold text-red-700 dark:text-red-300">{{ number_format((int)$lote->stock_actual) }}</div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400">Stock</div>
                                    </div>
                                </div>
                                <div class="mt-3 text-xs text-slate-600 dark:text-slate-400">
                                    Venció: {{ optional($lote->fecha_vencimiento)->format('d/m/Y') }}
                                </div>
                            </a>
                        @empty
                            <p class="text-slate-500 dark:text-slate-400 text-sm">No hay lotes vencidos con stock.</p>
                        @endforelse
</div>
                </div>
            </div>

        </div>
    </div>

</div>
@endsection
