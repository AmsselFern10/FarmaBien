@extends('layouts.app')

@section('title', 'Kardex del Lote')

@section('header')
    Inventario
@endsection

@section('page-actions')
    <div>
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Kardex del Lote</h2>
        <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
            Historial de movimientos (trazabilidad) del lote <span class="font-mono font-semibold">{{ $lote->numero_lote }}</span>.
        </p>
    </div>
    <div class="flex flex-wrap gap-3">
        <a href="{{ route('inventario.show-lote', $lote) }}"
           class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-slate-700 dark:text-slate-300 font-semibold rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200 shadow-sm">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Volver al lote
        </a>
        <a href="{{ route('inventario.lotes') }}"
           class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-slate-700 dark:text-slate-300 font-semibold rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200 shadow-sm">
            Ver lotes
        </a>
    </div>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Info del lote -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-xl border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-gray-50 to-white dark:from-gray-800 dark:to-gray-800/50">
            <div class="flex items-center space-x-3">
                <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Información del lote</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Datos operativos y saldo actual</p>
                </div>
            </div>
        </div>

        <div class="p-6">
            <dl class="grid grid-cols-1 gap-x-6 gap-y-6 sm:grid-cols-2 lg:grid-cols-4">
                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                    <dt class="text-sm font-medium text-slate-500 dark:text-slate-400">Producto</dt>
                    <dd class="mt-1 text-sm font-semibold text-slate-900 dark:text-white">{{ $lote->producto?->nombre }}</dd>
                    <dd class="text-xs text-slate-500 dark:text-slate-400">{{ $lote->producto?->categoria?->nombre }}</dd>
                </div>

                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                    <dt class="text-sm font-medium text-slate-500 dark:text-slate-400">Lote</dt>
                    <dd class="mt-1 text-sm font-semibold text-slate-900 dark:text-white font-mono">{{ $lote->numero_lote }}</dd>
                    <dd class="text-xs text-slate-500 dark:text-slate-400">ID #{{ $lote->id }}</dd>
                </div>

                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                    <dt class="text-sm font-medium text-slate-500 dark:text-slate-400">Vencimiento</dt>
                    <dd class="mt-1 text-sm font-semibold text-slate-900 dark:text-white">
                        {{ $lote->fecha_vencimiento ? $lote->fecha_vencimiento->format('d/m/Y') : '—' }}
                    </dd>
                    @if($lote->fecha_vencimiento)
                        <dd class="text-xs text-slate-500 dark:text-slate-400">{{ $lote->fecha_vencimiento->diffForHumans() }}</dd>
                    @endif
                </div>

                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-lg p-4 border border-blue-200 dark:border-blue-800">
                    <dt class="text-sm font-medium text-blue-700 dark:text-blue-300">Stock actual</dt>
                    <dd class="mt-1 text-3xl font-bold text-blue-600 dark:text-blue-400">{{ (int)$lote->stock_actual }}</dd>
                    <dd class="text-xs text-blue-700 dark:text-blue-300">Inicial: {{ (int)$lote->stock_inicial }}</dd>
                </div>
            </dl>
        </div>
    </div>

    <!-- Movimientos -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-xl border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-gray-50 to-white dark:from-gray-800 dark:to-gray-800/50">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg">
                        <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6a2 2 0 012-2h2a2 2 0 012 2v6m-8 0h8" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Movimientos</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">{{ $movimientos->count() }} registro(s)</p>
                    </div>
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
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Tipo</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Cantidad</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Saldo anterior</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Saldo nuevo</th>
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
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900 dark:text-white">
                                    {{ $m->fecha_movimiento?->format('d/m/Y H:i') ?? $m->created_at?->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-3 py-1 inline-flex items-center text-xs font-semibold rounded-full {{ $tipoBadge }}">
                                        {{ ucfirst($tipo) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <span class="text-sm font-bold {{ $m->cantidad >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                        {{ $m->cantidad >= 0 ? '+' : '' }}{{ (int)$m->cantidad }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-slate-700 dark:text-slate-300">
                                    {{ (int)$m->saldo_anterior }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <span class="text-sm font-bold text-blue-600 dark:text-blue-400">{{ (int)$m->saldo_nuevo }}</span>
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2a4 4 0 014-4h2"/>
                        </svg>
                    </div>
                    <h3 class="mt-4 text-lg font-semibold text-slate-900 dark:text-white">Sin movimientos</h3>
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Este lote aún no tiene movimientos registrados.</p>
                </div>
            @endif
        </div>
    </div>

</div>
@endsection
