@extends('layouts.app')

@section('title', 'Detalle del Lote')

@section('header')
    Inventario
@endsection

@section('page-actions')
    <div>
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white">
            Lote <span class="font-mono">{{ $lote->numero_lote }}</span>
        </h2>
        <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
            {{ $lote->producto?->nombre }} — {{ $lote->producto?->categoria?->nombre }}
        </p>
    </div>
    <div class="flex flex-wrap gap-3">
        <a href="{{ route('inventario.kardex-lote', $lote) }}"
           class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white font-semibold rounded-lg shadow-sm transition-all duration-200 hover:shadow-md">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Ver kardex
        </a>

        <a href="{{ route('inventario.lotes') }}"
           class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-slate-700 dark:text-slate-300 font-semibold rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200 shadow-sm">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Volver
        </a>
    </div>
@endsection

@section('content')
<div class="space-y-6">

    {{-- Estados --}}
    @if($lote->estaBloqueado())
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800/40 rounded-xl p-4">
            <div class="flex gap-3">
                <div class="mt-0.5">
                    <svg class="w-5 h-5 text-red-600 dark:text-red-300" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                              d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                              clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="text-sm text-red-800 dark:text-red-200">
                    <span class="font-semibold">Lote bloqueado:</span>
                    {{ $lote->motivo_bloqueo ?: 'Sin motivo registrado.' }}
                    @if($lote->bloqueado_at)
                        <span class="text-red-700 dark:text-red-300"> ({{ $lote->bloqueado_at->format('d/m/Y H:i') }})</span>
                    @endif
                </div>
            </div>
        </div>
    @endif

    @if($lote->estaVencido())
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800/40 rounded-xl p-4">
            <div class="flex gap-3">
                <div class="mt-0.5">
                    <svg class="w-5 h-5 text-red-600 dark:text-red-300" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                              d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                              clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="text-sm text-red-800 dark:text-red-200">
                    <span class="font-semibold">Lote vencido:</span>
                    Venció el {{ optional($lote->fecha_vencimiento)->format('d/m/Y') }} ({{ optional($lote->fecha_vencimiento)->diffForHumans() }})
                </div>
            </div>
        </div>
    @elseif($lote->proximoAVencer(30))
        <div class="bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800/40 rounded-xl p-4">
            <div class="flex gap-3">
                <div class="mt-0.5">
                    <svg class="w-5 h-5 text-orange-600 dark:text-orange-300" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                              d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                              clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="text-sm text-orange-800 dark:text-orange-200">
                    <span class="font-semibold">Próximo a vencer:</span>
                    Vence el {{ optional($lote->fecha_vencimiento)->format('d/m/Y') }} ({{ optional($lote->fecha_vencimiento)->diffForHumans() }})
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-2 space-y-6">

            <!-- Información del lote -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-xl border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-gray-50 to-white dark:from-gray-800 dark:to-gray-800/50">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Información del lote</h3>
                    <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">Datos básicos y control sanitario.</p>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl p-4">
                            <p class="text-xs text-slate-500 dark:text-slate-400">Número de lote</p>
                            <p class="mt-1 font-semibold text-slate-900 dark:text-white font-mono">{{ $lote->numero_lote }}</p>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl p-4">
                            <p class="text-xs text-slate-500 dark:text-slate-400">Proveedor</p>
                            <p class="mt-1 font-semibold text-slate-900 dark:text-white">{{ $lote->proveedor?->nombre ?? '—' }}</p>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl p-4">
                            <p class="text-xs text-slate-500 dark:text-slate-400">Fecha ingreso</p>
                            <p class="mt-1 font-semibold text-slate-900 dark:text-white">
                                {{ $lote->fecha_ingreso ? $lote->fecha_ingreso->format('d/m/Y H:i') : ($lote->created_at?->format('d/m/Y H:i') ?? '—') }}
                            </p>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl p-4">
                            <p class="text-xs text-slate-500 dark:text-slate-400">Fecha vencimiento</p>
                            <p class="mt-1 font-semibold {{ $lote->estaVencido() ? 'text-red-700 dark:text-red-300' : 'text-slate-900 dark:text-white' }}">
                                {{ optional($lote->fecha_vencimiento)->format('d/m/Y') ?? '—' }}
                            </p>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl p-4">
                            <p class="text-xs text-slate-500 dark:text-slate-400">Estado</p>
                            @php
                                $estado = $lote->estado ?: ($lote->estaVencido() ? 'vencido' : ($lote->estaBloqueado() ? 'bloqueado' : 'disponible'));
                                $badge = match($estado) {
                                    'vencido' => 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300',
                                    'bloqueado' => 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300',
                                    'agotado' => 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200',
                                    default => 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-800 dark:text-emerald-300',
                                };
                            @endphp
                            <span class="mt-1 inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $badge }}">
                                {{ ucfirst($estado) }}
                            </span>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl p-4">
                            <p class="text-xs text-slate-500 dark:text-slate-400">Compra (origen)</p>
                            @if($lote->compra)
                                <a class="mt-1 inline-flex items-center text-sm font-semibold text-blue-700 dark:text-blue-300 hover:underline"
                                   href="{{ route('compras.show', $lote->compra) }}">
                                    Compra #{{ $lote->compra->id }}
                                </a>
                            @else
                                <p class="mt-1 text-sm font-semibold text-slate-900 dark:text-white">—</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Resumen movimientos -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-xl border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-gray-50 to-white dark:from-gray-800 dark:to-gray-800/50">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Resumen de movimientos</h3>
                    <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">Totales históricos del lote (según kardex).</p>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="border-l-4 border-emerald-400 pl-4">
                            <p class="text-sm text-slate-600 dark:text-slate-400">Entradas</p>
                            <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ number_format((int)$totalEntradas) }}</p>
                        </div>
                        <div class="border-l-4 border-red-400 pl-4">
                            <p class="text-sm text-slate-600 dark:text-slate-400">Salidas</p>
                            <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ number_format((int)$totalSalidas) }}</p>
                        </div>
                        <div class="border-l-4 border-amber-400 pl-4">
                            <p class="text-sm text-slate-600 dark:text-slate-400">Ajustes (neto)</p>
                            <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ number_format((int)$totalAjustes) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Últimos movimientos -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-xl border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-gray-50 to-white dark:from-gray-800 dark:to-gray-800/50">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Últimos movimientos</h3>
                            <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">Vista rápida (5 últimos).</p>
                        </div>
                        <a href="{{ route('inventario.kardex-lote', $lote) }}"
                           class="text-sm font-semibold text-blue-700 dark:text-blue-300 hover:underline">
                            Ver todos →
                        </a>
                    </div>
                </div>

                <div class="p-6">
                    @php
                        $ultimos = $lote->movimientos?->sortByDesc('fecha_movimiento')->take(5) ?? collect();
                    @endphp

                    @if($ultimos->count() === 0)
                        <p class="text-slate-500 dark:text-slate-400 text-center py-6">No hay movimientos registrados.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700/50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Fecha</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Tipo</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Cantidad</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Saldo</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Origen</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($ultimos as $mov)
                                        @php
                                            $tipoLabel = match($mov->tipo) {
                                                'entrada' => ['Entrada', 'text-emerald-700 dark:text-emerald-300'],
                                                'salida' => ['Salida', 'text-red-700 dark:text-red-300'],
                                                default => ['Ajuste', 'text-amber-700 dark:text-amber-300'],
                                            };

                                            $origen = $mov->origen ?? '';
                                            $origenId = $mov->origen_id;

                                            $origenTexto = $origen ? str_replace('_', ' ', $origen) : 'manual';
                                        @endphp
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                            <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-200">
                                                {{ $mov->fecha_movimiento?->format('d/m/Y H:i') ?? $mov->created_at?->format('d/m/Y H:i') }}
                                            </td>
                                            <td class="px-4 py-3 text-sm font-semibold {{ $tipoLabel[1] }}">{{ $tipoLabel[0] }}</td>
                                            <td class="px-4 py-3 text-sm text-right font-semibold text-slate-900 dark:text-white">
                                                {{ $mov->cantidad > 0 ? '+' : '' }}{{ (int)$mov->cantidad }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-right text-slate-900 dark:text-white font-semibold">
                                                {{ number_format((int)$mov->saldo_nuevo) }}
                                            </td>
                                            <td class="px-4 py-3 text-sm">
                                                @if($origenId && str_contains($origen, 'venta'))
                                                    <a href="{{ route('ventas.show', $origenId) }}" class="text-blue-700 dark:text-blue-300 hover:underline">
                                                        {{ ucfirst($origenTexto) }} #{{ $origenId }}
                                                    </a>
                                                @elseif($origenId && str_contains($origen, 'compra'))
                                                    <a href="{{ route('compras.show', $origenId) }}" class="text-blue-700 dark:text-blue-300 hover:underline">
                                                        {{ ucfirst($origenTexto) }} #{{ $origenId }}
                                                    </a>
                                                @else
                                                    <span class="text-slate-600 dark:text-slate-400">{{ ucfirst($origenTexto) }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

        </div>

        <!-- Sidebar -->
        <div class="xl:col-span-1 space-y-6">

            <!-- Stock -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-xl border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-gray-50 to-white dark:from-gray-800 dark:to-gray-800/50">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Stock</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div class="bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl p-4">
                        <p class="text-xs text-slate-500 dark:text-slate-400">Stock actual</p>
                        <p class="mt-1 text-3xl font-bold text-slate-900 dark:text-white">{{ number_format((int)$lote->stock_actual) }}</p>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl p-4">
                        <p class="text-xs text-slate-500 dark:text-slate-400">Stock inicial (entradas acumuladas)</p>
                        <p class="mt-1 text-xl font-bold text-slate-900 dark:text-white">{{ number_format((int)$lote->stock_inicial) }}</p>
                        @php
                            $utilizado = ((int)$lote->stock_inicial) > 0 ? ((int)$lote->stock_inicial - (int)$lote->stock_actual) : 0;
                            $porc = ((int)$lote->stock_inicial) > 0 ? round(($utilizado / (int)$lote->stock_inicial) * 100, 1) : 0;
                        @endphp
                        <div class="mt-3">
                            <div class="flex justify-between text-xs text-slate-500 dark:text-slate-400 mb-1">
                                <span>Usado</span>
                                <span>{{ $porc }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
                                <div class="h-2 bg-blue-600 dark:bg-blue-400" style="width: {{ min(100, max(0, $porc)) }}%"></div>
                            </div>
                            <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Unidades usadas: {{ number_format($utilizado) }}</p>
                        </div>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl p-4">
                        <p class="text-xs text-slate-500 dark:text-slate-400">Precio compra (lote)</p>
                        <p class="mt-1 text-lg font-bold text-slate-900 dark:text-white">{{ number_format((float)$lote->precio_compra, 2) }}</p>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Referencia para valoración del inventario.</p>
                    </div>

                    <a href="{{ route('productos.show', $lote->producto) }}"
                       class="w-full inline-flex justify-center items-center px-4 py-2 bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-200 font-semibold rounded-lg hover:bg-blue-200 dark:hover:bg-blue-900/40 transition-colors">
                        Ver producto
                    </a>
                </div>
            </div>

        </div>
    </div>

</div>
@endsection
