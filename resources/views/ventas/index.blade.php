@extends('layouts.app')

@section('title', 'Ventas')

@section('header')
    Ventas
@endsection

@section('page-actions')
    <div>
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white">
            Ventas
        </h2>
        <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
            Gestión de ventas y facturación
        </p>
    </div>
    <div class="flex gap-3">
        @can('realizar ventas')
        <a href="{{ route('ventas.create') }}" 
           class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-semibold rounded-lg shadow-sm transition-all duration-200 hover:shadow-md hover:scale-105">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Nueva Venta
        </a>
        @endcan
    </div>
@endsection

@section('content')
<div class="space-y-6">
    
    <!-- Botón Flotante de Búsqueda Rápida -->
    <button onclick="abrirModalBuscar()" 
            class="fixed bottom-6 right-6 z-40 inline-flex items-center px-5 py-3 bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white font-semibold rounded-full shadow-lg hover:shadow-xl transition-all duration-200 hover:scale-110"
            title="Búsqueda Rápida">
        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
        </svg>
        Buscar Venta
    </button>
    
    <!-- Estadísticas -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Ventas -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600 dark:text-slate-400">Total Ventas</p>
                        <p class="text-3xl font-bold text-slate-900 dark:text-white mt-2">
                            {{ \App\Models\Venta::count() }}
                        </p>
                    </div>
                    <div class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                        <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Completadas -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600 dark:text-slate-400">Completadas</p>
                        <p class="text-3xl font-bold text-green-600 dark:text-green-400 mt-2">
                            {{ \App\Models\Venta::where('estado', 'completada')->count() }}
                        </p>
                    </div>
                    <div class="p-3 bg-green-100 dark:bg-green-900/30 rounded-lg">
                        <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Anuladas -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600 dark:text-slate-400">Anuladas</p>
                        <p class="text-3xl font-bold text-red-600 dark:text-red-400 mt-2">
                            {{ \App\Models\Venta::where('estado', 'anulada')->count() }}
                        </p>
                    </div>
                    <div class="p-3 bg-red-100 dark:bg-red-900/30 rounded-lg">
                        <svg class="w-8 h-8 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Ingresos -->
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 dark:from-purple-600 dark:to-purple-700 rounded-xl shadow-lg overflow-hidden">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-purple-100">Total Ingresos</p>
                        <p class="text-3xl font-bold text-white mt-2">
                            S/ {{ number_format(\App\Models\Venta::where('estado', 'completada')->sum('total'), 2) }}
                        </p>
                    </div>
                    <div class="p-3 bg-purple-400/30 rounded-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Filtros de Búsqueda</h3>
        </div>
        
        <form method="GET" action="{{ route('ventas.index') }}" class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                
                <!-- Estado -->
                <div>
                    <label class="block text-sm font-medium text-slate-900 dark:text-white mb-2">
                        Estado
                    </label>
                    <select name="estado" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Todos los estados</option>
                        <option value="completada" {{ request('estado') === 'completada' ? 'selected' : '' }}>Completadas</option>
                        <option value="anulada" {{ request('estado') === 'anulada' ? 'selected' : '' }}>Anuladas</option>
                    </select>
                </div>

                <!-- Cliente -->
                <div>
                    <label class="block text-sm font-medium text-slate-900 dark:text-white mb-2">
                        Cliente
                    </label>
                    <select name="cliente_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Todos los clientes</option>
                        @foreach(\App\Models\Cliente::orderBy('nombre')->get() as $cliente)
                            <option value="{{ $cliente->id }}" {{ request('cliente_id') == $cliente->id ? 'selected' : '' }}>
                                {{ $cliente->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Fecha Inicio -->
                <div>
                    <label class="block text-sm font-medium text-slate-900 dark:text-white mb-2">
                        Fecha Inicio
                    </label>
                    <input type="date" 
                           name="fecha_inicio" 
                           value="{{ request('fecha_inicio') }}"
                           class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Fecha Fin -->
                <div>
                    <label class="block text-sm font-medium text-slate-900 dark:text-white mb-2">
                        Fecha Fin
                    </label>
                    <input type="date" 
                           name="fecha_fin" 
                           value="{{ request('fecha_fin') }}"
                           class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>

            <!-- Botones -->
            <div class="mt-4 flex justify-end gap-3">
                @if(request()->hasAny(['estado', 'cliente_id', 'fecha_inicio', 'fecha_fin']))
                <a href="{{ route('ventas.index') }}" 
                   class="px-6 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-slate-700 dark:text-slate-300 font-semibold rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors duration-200">
                    Limpiar Filtros
                </a>
                @endif
                <button type="submit" 
                        class="px-6 py-2.5 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-semibold rounded-lg shadow-sm transition-all duration-200 hover:shadow-md">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                    </svg>
                    Filtrar
                </button>
            </div>
        </form>
    </div>

    <!-- Tabla de Ventas -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                            Venta
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                            Cliente
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                            Total
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                            Pago
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                            Estado
                        </th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                    @forelse($ventas as $venta)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150 {{ $venta->estado === 'anulada' ? 'opacity-60' : '' }}">
                        <!-- Venta -->
                        <td class="px-4 py-4">
                            <div class="flex items-center">
                                <div class="h-10 w-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm font-bold text-slate-900 dark:text-white">#{{ str_pad($venta->id, 6, '0', STR_PAD_LEFT) }}</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">{{ $venta->fecha->format('d/m/Y H:i') }}</div>
                                    @if($venta->venta_original_id)
                                        <span class="mt-1 inline-block px-2 py-0.5 text-xs font-semibold rounded-full bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-300">
                                            Modifica #{{ $venta->venta_original_id }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </td>

                        <!-- Cliente -->
                        <td class="px-4 py-4">
                            <div class="text-sm font-semibold text-slate-900 dark:text-white">
                                {{ $venta->cliente ? $venta->cliente->nombre : 'Público General' }}
                            </div>
                            @if($venta->cliente && $venta->cliente->documento)
                            <div class="text-xs text-slate-500 dark:text-slate-400">{{ $venta->cliente->tipo_documento }}: {{ $venta->cliente->documento }}</div>
                            @endif
                            <div class="text-xs text-slate-500 dark:text-slate-400">Por: {{ $venta->usuario->name }}</div>
                        </td>

                        <!-- Total -->
                        <td class="px-4 py-4">
                            <div class="flex flex-col">
                                <span class="inline-flex items-center px-3 py-1 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 font-bold text-sm">
                                    S/ {{ number_format($venta->total, 2) }}
                                </span>
                                <span class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                    {{ $venta->detalles->count() }} producto(s)
                                </span>
                            </div>
                        </td>

                        <!-- Método de Pago -->
                        <td class="px-4 py-4">
                            @php
                                $metodoColors = [
                                    'efectivo' => 'green',
                                    'tarjeta' => 'purple',
                                    'transferencia' => 'blue',
                                    'yape' => 'indigo',
                                    'plin' => 'pink',
                                ];
                                $color = $metodoColors[$venta->metodo_pago] ?? 'gray';
                            @endphp
                            <span class="px-3 py-1 inline-flex items-center text-xs font-semibold rounded-full bg-{{ $color }}-100 dark:bg-{{ $color }}-900/30 text-{{ $color }}-800 dark:text-{{ $color }}-300">
                                {{ ucfirst($venta->metodo_pago) }}
                            </span>
                        </td>

                        <!-- Estado -->
                        <td class="px-4 py-4">
                            @if($venta->estado === 'completada')
                                <span class="px-3 py-1.5 inline-flex items-center text-xs font-semibold rounded-full bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300">
                                    <span class="w-2 h-2 bg-green-500 rounded-full mr-2 animate-pulse"></span>
                                    Completada
                                </span>
                            @else
                                <span class="px-3 py-1.5 inline-flex items-center text-xs font-semibold rounded-full bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300">
                                    <span class="w-2 h-2 bg-red-500 rounded-full mr-2"></span>
                                    Anulada
                                </span>
                            @endif
                        </td>

                        <!-- Acciones -->
                        <td class="px-4 py-4 text-right">
                            <div class="flex justify-end gap-2">
                                @can('ver ventas')
                                <a href="{{ route('ventas.show', $venta) }}" 
                                   class="p-2 text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors duration-200"
                                   title="Ver detalles">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </a>
                                @endcan

                                @can('anular ventas')
                                    @if($venta->puedeModificarse())
                                    <a href="{{ route('ventas.edit', $venta) }}" 
                                       class="p-2 text-yellow-600 dark:text-yellow-400 hover:bg-yellow-50 dark:hover:bg-yellow-900/20 rounded-lg transition-colors duration-200"
                                       title="Modificar">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </a>

                                    <button onclick="modalAnular({{ $venta->id }})" 
                                            class="p-2 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors duration-200"
                                            title="Anular">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                    @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <svg class="mx-auto h-16 w-16 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <p class="mt-4 text-slate-500 dark:text-slate-400 font-medium">No se encontraron ventas</p>
                            <p class="text-sm text-slate-400 dark:text-slate-500 mt-1">Intenta ajustar los filtros o crear una nueva venta</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        @if($ventas->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50">
            {{ $ventas->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Modal de Anulación -->
<div id="modalAnular" class="hidden fixed z-50 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 dark:bg-gray-900 bg-opacity-75 dark:bg-opacity-80 transition-opacity" onclick="cerrarModalAnular()"></div>
        
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-red-100 dark:bg-red-900/30 rounded-lg">
                        <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Anular Venta</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Esta acción no se puede deshacer</p>
                    </div>
                </div>
            </div>
            
            <form id="formAnular" method="POST">
                @csrf
                
                <div class="p-6">
                    <label class="block text-sm font-medium text-slate-900 dark:text-white mb-2">
                        Motivo de Anulación <span class="text-red-500">*</span>
                    </label>
                    <textarea name="motivo" 
                              rows="4" 
                              required
                              class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500"
                              placeholder="Ingrese el motivo de la anulación..."></textarea>
                    
                    <div class="mt-4 bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-400 dark:border-yellow-600 p-4 rounded-lg">
                        <div class="flex">
                            <svg class="h-5 w-5 text-yellow-600 dark:text-yellow-400 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <p class="text-sm text-yellow-800 dark:text-yellow-300">
                                Esta acción anulará la venta y revertirá el stock de los productos.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 flex justify-end gap-3">
                    <button type="button" 
                            onclick="cerrarModalAnular()"
                            class="px-6 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-slate-700 dark:text-slate-300 font-semibold rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors duration-200">
                        Cancelar
                    </button>
                    <button type="submit" 
                            class="px-6 py-2.5 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-semibold rounded-lg shadow-sm transition-all duration-200 hover:shadow-md">
                        Confirmar Anulación
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de Búsqueda Rápida -->
@include('ventas.partials.modal-buscar')

@endsection

@push('scripts')
<script>
function modalAnular(ventaId) {
    const modal = document.getElementById('modalAnular');
    const form = document.getElementById('formAnular');
    form.action = `/ventas/${ventaId}/anular`;
    modal.classList.remove('hidden');
}

function cerrarModalAnular() {
    const modal = document.getElementById('modalAnular');
    modal.classList.add('hidden');
    document.querySelector('#formAnular textarea[name="motivo"]').value = '';
}

// Cerrar modal con ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        cerrarModalAnular();
    }
});

// Cerrar modal al hacer clic en el fondo
document.getElementById('modalAnular')?.addEventListener('click', function(e) {
    if (e.target === this) {
        cerrarModalAnular();
    }
});
</script>
@endpush