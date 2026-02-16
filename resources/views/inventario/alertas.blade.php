@extends('layouts.app')

@section('title', 'Alertas de Inventario')

@section('header')
    Inventario
@endsection

@section('page-actions')
    <div>
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white">
            Alertas de Inventario
        </h2>
        <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
            Stock bajo, lotes por vencer y lotes vencidos con stock.
        </p>
    </div>
    <div class="flex flex-wrap gap-3">
        <a href="{{ route('inventario.index') }}"
           class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-slate-700 dark:text-slate-300 font-semibold rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200 shadow-sm">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Volver
        </a>
        <a href="{{ route('compras.create') }}"
           class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-semibold rounded-lg shadow-sm transition-all duration-200 hover:shadow-md">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 4v16m8-8H4"/>
            </svg>
            Nueva compra
        </a>
    </div>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Resumen -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-xl border border-gray-200 dark:border-gray-700">
            <div class="p-6 flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-600 dark:text-slate-400">Stock bajo</p>
                    <p class="mt-1 text-3xl font-bold text-slate-900 dark:text-white">{{ $productosStockBajo->count() }}</p>
                </div>
                <div class="p-3 rounded-xl bg-yellow-100 dark:bg-yellow-900/30">
                    <svg class="w-7 h-7 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-xl border border-gray-200 dark:border-gray-700">
            <div class="p-6 flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-600 dark:text-slate-400">Próximos a vencer</p>
                    <p class="mt-1 text-3xl font-bold text-slate-900 dark:text-white">{{ $lotesProximosVencer->count() }}</p>
                </div>
                <div class="p-3 rounded-xl bg-orange-100 dark:bg-orange-900/30">
                    <svg class="w-7 h-7 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-xl border border-gray-200 dark:border-gray-700">
            <div class="p-6 flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-600 dark:text-slate-400">Vencidos con stock</p>
                    <p class="mt-1 text-3xl font-bold text-slate-900 dark:text-white">{{ $lotesVencidos->count() }}</p>
                </div>
                <div class="p-3 rounded-xl bg-red-100 dark:bg-red-900/30">
                    <svg class="w-7 h-7 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Productos stock bajo -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-xl border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-yellow-50 to-white dark:from-gray-800 dark:to-gray-800/50">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Productos con stock bajo</h3>
                    <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">Bajo stock mínimo configurado.</p>
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

    <!-- Lotes por vencer -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-xl border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-orange-50 to-white dark:from-gray-800 dark:to-gray-800/50">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Próximos a vencer</h3>
                        <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">Ordenados por fecha de vencimiento.</p>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-300">
                        {{ $lotesProximosVencer->count() }}
                    </span>
                </div>
            </div>
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Producto</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Lote</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Stock</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Vence</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($lotesProximosVencer as $lote)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-4 py-3 text-sm text-slate-900 dark:text-white">{{ $lote->producto?->nombre }}</td>
                                    <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-200 font-mono">{{ $lote->numero_lote }}</td>
                                    <td class="px-4 py-3 text-sm text-right text-slate-900 dark:text-white font-semibold">{{ number_format((int)$lote->stock_actual) }}</td>
                                    <td class="px-4 py-3 text-sm text-right text-orange-700 dark:text-orange-300 font-semibold">
                                        {{ optional($lote->fecha_vencimiento)->format('d/m/Y') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-6 text-center text-slate-500 dark:text-slate-400">No hay lotes próximos a vencer.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-xl border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-red-50 to-white dark:from-gray-800 dark:to-gray-800/50">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Vencidos con stock</h3>
                        <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">Requiere acción (devolución/baja).</p>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300">
                        {{ $lotesVencidos->count() }}
                    </span>
                </div>
            </div>
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Producto</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Lote</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Stock</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Venció</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($lotesVencidos as $lote)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-4 py-3 text-sm text-slate-900 dark:text-white">{{ $lote->producto?->nombre }}</td>
                                    <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-200 font-mono">{{ $lote->numero_lote }}</td>
                                    <td class="px-4 py-3 text-sm text-right text-red-700 dark:text-red-300 font-semibold">{{ number_format((int)$lote->stock_actual) }}</td>
                                    <td class="px-4 py-3 text-sm text-right text-red-700 dark:text-red-300 font-semibold">
                                        {{ optional($lote->fecha_vencimiento)->format('d/m/Y') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-6 text-center text-slate-500 dark:text-slate-400">No hay lotes vencidos con stock.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
</div>
        </div>
    </div>

</div>
@endsection
