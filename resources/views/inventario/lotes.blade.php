@extends('layouts.app')

@section('title', 'Lotes')

@section('header')
    Inventario
@endsection

@section('page-actions')
    <div>
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Lotes</h2>
        <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
            Gestión de lotes, vencimientos y stock por lote.
        </p>
    </div>
    <div class="flex flex-wrap gap-3">
        <a href="{{ route('inventario.index') }}"
           class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-slate-700 dark:text-slate-300 font-semibold rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200 shadow-sm">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
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
            <div class="flex items-center gap-3">
                <div class="p-2 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg">
                    <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L15 12.414V19a1 1 0 01-.553.894l-4 2A1 1 0 019 21v-8.586L3.293 6.707A1 1 0 013 6V4z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Filtros</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Refina por producto, estado o actividad</p>
                </div>
            </div>
        </div>

        <form method="GET" action="{{ route('inventario.lotes') }}" class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

                <!-- Producto -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Producto</label>
                    <select name="producto_id"
                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900/40 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Todos</option>
                        @foreach($productos as $producto)
                            <option value="{{ $producto->id }}" {{ request('producto_id') == $producto->id ? 'selected' : '' }}>
                                {{ $producto->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Estado -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Estado</label>
                    <select name="estado"
                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900/40 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Todos</option>
                        <option value="disponible" {{ request('estado') === 'disponible' ? 'selected' : '' }}>Disponibles (vendibles)</option>
                        <option value="proximo_vencer" {{ request('estado') === 'proximo_vencer' ? 'selected' : '' }}>Próximos a vencer</option>
                        <option value="vencido" {{ request('estado') === 'vencido' ? 'selected' : '' }}>Vencidos</option>
                    </select>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">"Disponibles" excluye bloqueados, vencidos y sin stock.</p>
                </div>

                <!-- Días para próximos -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Días (próximos)</label>
                    <input type="number" min="1" max="365" name="dias_vencimiento" value="{{ (int)($diasProximo ?? request('dias_vencimiento', 30)) }}"
                           class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900/40 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Aplica cuando el estado es “Próximos a vencer”.</p>
                </div>

                <!-- Activo -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Activo</label>
                    <select name="activo"
                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900/40 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="" {{ request('activo') === null || request('activo') === '' ? 'selected' : '' }}>Solo activos</option>
                        <option value="1" {{ request('activo') === '1' ? 'selected' : '' }}>Activos</option>
                        <option value="0" {{ request('activo') === '0' ? 'selected' : '' }}>Inactivos</option>
                    </select>
                </div>

            </div>

            <div class="flex flex-wrap items-center justify-end gap-3 mt-6">
                <a href="{{ route('inventario.lotes') }}"
                   class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-slate-700 dark:text-slate-300 font-semibold rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors shadow-sm">
                    Limpiar
                </a>
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-indigo-500 to-indigo-600 hover:from-indigo-600 hover:to-indigo-700 text-white font-semibold rounded-lg shadow-sm transition-all duration-200 hover:shadow-md">
                    Filtrar
                </button>
            </div>
        </form>
    </div>

    <!-- Tabla -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-xl border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-gray-50 to-white dark:from-gray-800 dark:to-gray-800/50">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Listado</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ $lotes->total() }} lote(s)</p>
                </div>
            </div>
        </div>

        <div class="p-6">
            @if($lotes->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Lote</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Producto</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Proveedor</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Vencimiento</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Stock</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Estado</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($lotes as $lote)
                                @php
                                    $estado = $lote->estado;
                                    if ($lote->estaBloqueado()) $estado = 'bloqueado';
                                    elseif ($lote->estaVencido()) $estado = 'vencido';
                                    elseif ((int)$lote->stock_actual <= 0) $estado = 'agotado';
                                    elseif (!$estado) $estado = 'disponible';

                                    $badge = match($estado) {
                                        'disponible' => 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300',
                                        'proximo_vencer' => 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300',
                                        'vencido' => 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300',
                                        'agotado' => 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300',
                                        'bloqueado' => 'bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-300',
                                        default => 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300',
                                    };

                                    $label = match($estado) {
                                        'disponible' => 'Disponible',
                                        'vencido' => 'Vencido',
                                        'agotado' => 'Agotado',
                                        'bloqueado' => 'Bloqueado',
                                        default => ucfirst(str_replace('_',' ',$estado)),
                                    };
                                @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-mono font-semibold text-slate-900 dark:text-white">{{ $lote->numero_lote }}</div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400">#{{ $lote->id }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-semibold text-slate-900 dark:text-white">{{ $lote->producto?->nombre }}</div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400">{{ $lote->producto?->categoria?->nombre }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600 dark:text-slate-400">
                                        {{ $lote->proveedor?->nombre ?? '—' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-slate-900 dark:text-white font-medium">
                                            {{ $lote->fecha_vencimiento ? $lote->fecha_vencimiento->format('d/m/Y') : '—' }}
                                        </div>
                                        @if($lote->fecha_vencimiento)
                                            <div class="text-xs text-slate-500 dark:text-slate-400">
                                                {{ $lote->fecha_vencimiento->diffForHumans() }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="text-lg font-bold {{ (int)$lote->stock_actual <= 0 ? 'text-slate-500 dark:text-slate-400' : 'text-blue-600 dark:text-blue-400' }}">
                                            {{ (int)$lote->stock_actual }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-3 py-1.5 inline-flex items-center text-xs font-semibold rounded-full {{ $badge }}">
                                            {{ $label }}
                                        </span>
                                        @if($lote->estaBloqueado() && $lote->motivo_bloqueo)
                                            <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                                {{ \Illuminate\Support\Str::limit($lote->motivo_bloqueo, 35) }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('inventario.show-lote', $lote) }}"
                                               class="inline-flex items-center px-3 py-1.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                                Ver
                                            </a>
                                            <a href="{{ route('inventario.kardex-lote', $lote) }}"
                                               class="inline-flex items-center px-3 py-1.5 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-all duration-200">
                                                Kardex
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    {{ $lotes->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <div class="mx-auto w-16 h-16 bg-slate-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8 text-slate-400 dark:text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V7a2 2 0 00-2-2H6a2 2 0 00-2 2v6m16 0v6a2 2 0 01-2 2H6a2 2 0 01-2-2v-6m16 0H4"/>
                        </svg>
                    </div>
                    <h3 class="mt-4 text-lg font-semibold text-slate-900 dark:text-white">Sin resultados</h3>
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">No hay lotes que coincidan con los filtros actuales.</p>
                </div>
            @endif
        </div>
    </div>

</div>
@endsection
