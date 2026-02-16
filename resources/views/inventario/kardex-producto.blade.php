@extends('layouts.app')

@section('title', 'Kardex del Producto')

@section('header')
    Inventario
@endsection

@section('page-actions')
    <div>
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Kardex del Producto</h2>
        <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
            Historial de movimientos (por producto). Producto: <span class="font-semibold">{{ $producto->nombre }}</span>
        </p>
    </div>
    <div class="flex flex-wrap gap-3">
        <a href="{{ route('inventario.index') }}"
           class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-slate-700 dark:text-slate-300 font-semibold rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200 shadow-sm">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Volver
        </a>
    </div>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Filtros -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-xl border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-gray-50 to-white dark:from-gray-800 dark:to-gray-800/50">
            <div class="flex items-center space-x-3">
                <div class="p-2 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg">
                    <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Rango de fechas</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Filtra movimientos por fecha</p>
                </div>
            </div>
        </div>

        <form method="GET" action="{{ route('inventario.kardex-producto', $producto) }}" class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Desde</label>
                    <input type="date" name="fecha_inicio" value="{{ $fechaInicio }}"
                           class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900/40 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Hasta</label>
                    <input type="date" name="fecha_fin" value="{{ $fechaFin }}"
                           class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900/40 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                </div>
                <div class="flex items-end gap-3">
                    <a href="{{ route('inventario.kardex-producto', $producto) }}"
                       class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-slate-700 dark:text-slate-300 font-semibold rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors shadow-sm">
                        Limpiar
                    </a>
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-indigo-500 to-indigo-600 hover:from-indigo-600 hover:to-indigo-700 text-white font-semibold rounded-lg shadow-sm transition-all duration-200 hover:shadow-md">
                        Filtrar
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Movimientos -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-xl border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-gray-50 to-white dark:from-gray-800 dark:to-gray-800/50">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Movimientos</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ $movimientos->count() }} registro(s)</p>
                </div>
            </div>
        </div>

        <div class="p-6">
            @if($movimientos->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Fecha</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Lote</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Tipo</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Entrada</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Salida</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Saldo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Origen</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Usuario</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Motivo</th>
                        </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($movimientos as $m)
                            @php
                                $tipo = $m->tipo;
                                $tipoBadge = match($tipo) {
                                    'entrada' => 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300',
                                    'salida' => 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300',
                                    'ajuste' => 'bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-300',
                                    default => 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300',
                                };

                                $origen = $m->origen ?? '—';
                                $origenId = $m->origen_id;
                                $origenLink = null;
                                if ($origenId) {
                                    if (str_contains($origen, 'compra')) $origenLink = route('compras.show', $origenId);
                                    elseif (str_contains($origen, 'venta')) $origenLink = route('ventas.show', $origenId);
                                }

                                $entrada = $m->cantidad > 0 ? (int)$m->cantidad : null;
                                $salida = $m->cantidad < 0 ? abs((int)$m->cantidad) : null;
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900 dark:text-white">
                                    {{ $m->fecha_movimiento?->format('d/m/Y H:i') ?? $m->created_at?->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <a href="{{ route('inventario.show-lote', $m->lote_id) }}" class="text-blue-600 dark:text-blue-400 hover:underline font-mono">
                                        {{ $m->lote?->numero_lote ?? '—' }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-3 py-1 inline-flex items-center text-xs font-semibold rounded-full {{ $tipoBadge }}">
                                        {{ ucfirst($tipo) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-green-600 dark:text-green-400">
                                    {{ $entrada !== null ? $entrada : '—' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-red-600 dark:text-red-400">
                                    {{ $salida !== null ? $salida : '—' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <span class="text-sm font-bold text-blue-600 dark:text-blue-400">{{ (int)($m->stock_acumulado ?? 0) }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if($origenLink)
                                        <a href="{{ $origenLink }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                                            {{ ucfirst(str_replace('_',' ',$origen)) }} #{{ $origenId }}
                                        </a>
                                    @else
                                        <span class="text-slate-600 dark:text-slate-400">{{ ucfirst(str_replace('_',' ',$origen)) }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600 dark:text-slate-400">
                                    {{ $m->usuario?->name ?? '—' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">
                                    {{ $m->motivo ? \Illuminate\Support\Str::limit($m->motivo, 60) : '—' }}
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-12">
                    <div class="mx-auto w-16 h-16 bg-slate-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8 text-slate-400 dark:text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3"/>
                        </svg>
                    </div>
                    <h3 class="mt-4 text-lg font-semibold text-slate-900 dark:text-white">Sin movimientos</h3>
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">No se encontraron movimientos para este rango.</p>
                </div>
            @endif
        </div>
    </div>

</div>
@endsection
