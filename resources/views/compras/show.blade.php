@extends('layouts.app')

@section('title', 'Detalle de Compra')

@section('header')
    Detalle de Compra
@endsection

@section('page-actions')
    <div>
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white">
            Compra #{{ $compra->id }}
        </h2>
        <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
            {{ $compra->fecha->format('d/m/Y') }} - {{ $compra->proveedor->nombre }}
        </p>
    </div>
    <div class="flex gap-3">
        <a href="{{ route('compras.index') }}" 
           class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-slate-700 dark:text-slate-300 font-semibold rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200 shadow-sm">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Volver
        </a>
        @can('anular compras')
            @if($compra->puedeModificarse())
            <a href="{{ route('compras.edit', $compra) }}" 
               class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-yellow-500 to-yellow-600 hover:from-yellow-600 hover:to-yellow-700 text-white font-semibold rounded-lg shadow-sm transition-all duration-200 hover:shadow-md hover:scale-105">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Modificar
            </a>
            <button onclick="modalAnular({{ $compra->id }})"
                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-semibold rounded-lg shadow-sm transition-all duration-200 hover:shadow-md hover:scale-105">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                Anular
            </button>
            @endif
        @endcan
    </div>
@endsection

@section('content')

<!-- Alertas de Estado -->
@if($compra->estado == 'anulada')
<div class="mb-6 bg-red-50 dark:bg-red-900/20 border-l-4 border-red-400 dark:border-red-600 p-4 rounded-lg">
    <div class="flex">
        <svg class="h-6 w-6 text-red-600 dark:text-red-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
        </svg>
        <div>
            <p class="text-sm font-semibold text-red-800 dark:text-red-300">
                Compra Anulada - {{ $compra->fecha_anulacion->format('d/m/Y H:i') }}
            </p>
            <p class="text-sm text-red-700 dark:text-red-400 mt-1">
                Motivo: {{ $compra->motivo_anulacion }}
            </p>
            <p class="text-xs text-red-600 dark:text-red-500 mt-1">
                Anulado por: {{ $compra->anuladoPor->name }}
            </p>
        </div>
    </div>
</div>
@endif

@if($compra->reemplazada_por)
<div class="mb-6 bg-purple-50 dark:bg-purple-900/20 border-l-4 border-purple-400 dark:border-purple-600 p-4 rounded-lg">
    <div class="flex">
        <svg class="h-6 w-6 text-purple-600 dark:text-purple-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
        </svg>
        <div>
            <p class="text-sm font-semibold text-purple-800 dark:text-purple-300">
                Compra Modificada - Esta compra fue reemplazada por 
                <a href="{{ route('compras.show', $compra->reemplazada_por) }}" class="underline hover:text-purple-900 dark:hover:text-purple-200">
                    Compra #{{ $compra->reemplazada_por }}
                </a>
            </p>
        </div>
    </div>
</div>
@endif

@if($compra->compra_original_id)
<div class="mb-6 bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-400 dark:border-blue-600 p-4 rounded-lg">
    <div class="flex">
        <svg class="h-6 w-6 text-blue-600 dark:text-blue-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
        </svg>
        <div>
            <p class="text-sm font-semibold text-blue-800 dark:text-blue-300">
                Esta compra es una modificación de 
                <a href="{{ route('compras.show', $compra->compra_original_id) }}" class="underline hover:text-blue-900 dark:hover:text-blue-200">
                    Compra #{{ $compra->compra_original_id }}
                </a>
            </p>
        </div>
    </div>
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <!-- Columna Principal -->
    <div class="lg:col-span-2 space-y-6">
        
        <!-- Información General -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-blue-50 to-white dark:from-gray-800 dark:to-gray-800/50">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Información de la Compra</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Datos generales</p>
                    </div>
                </div>
            </div>
            
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    
                    <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Fecha de Compra</p>
                        <p class="text-sm font-bold text-slate-900 dark:text-white">{{ $compra->fecha->format('d/m/Y') }}</p>
                    </div>

                    <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Estado</p>
                        @if($compra->estado == 'recibida')
                            <span class="px-3 py-1.5 inline-flex items-center text-xs font-semibold rounded-full bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300">
                                <span class="w-2 h-2 bg-green-500 rounded-full mr-2 animate-pulse"></span>
                                Recibida
                            </span>
                        @else
                            <span class="px-3 py-1.5 inline-flex items-center text-xs font-semibold rounded-full bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300">
                                <span class="w-2 h-2 bg-red-500 rounded-full mr-2"></span>
                                Anulada
                            </span>
                        @endif
                    </div>

                    <div class="md:col-span-2 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-2">Proveedor</p>
                        <p class="text-sm font-bold text-slate-900 dark:text-white">{{ $compra->proveedor->nombre }}</p>
                        <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">RUC: {{ $compra->proveedor->ruc }}</p>
                    </div>

                    <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Registrado por</p>
                        <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $compra->usuario->name }}</p>
                    </div>

                    <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Fecha de Registro</p>
                        <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $compra->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detalle de Productos -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-green-50 to-white dark:from-gray-800 dark:to-gray-800/50">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-green-100 dark:bg-green-900/30 rounded-lg">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Detalle de Productos</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">{{ $compra->detalles->count() }} producto(s)</p>
                    </div>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Producto</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Lote</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Vencimiento</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Cantidad</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider">P. Unit.</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                        @foreach($compra->detalles as $detalle)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150">
                            <td class="px-4 py-3">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <span class="text-white font-bold text-sm">{{ substr($detalle->producto->nombre, 0, 2) }}</span>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-semibold text-slate-900 dark:text-white">{{ $detalle->producto->nombre }}</div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400">{{ $detalle->producto->categoria->nombre }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-sm font-mono font-semibold text-slate-900 dark:text-white">{{ $detalle->lote->numero_lote }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-sm text-slate-900 dark:text-white">{{ $detalle->lote->fecha_vencimiento->format('d/m/Y') }}</span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <span class="text-sm font-bold text-slate-900 dark:text-white">{{ $detalle->cantidad }}</span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <span class="text-sm text-slate-900 dark:text-white">S/ {{ number_format($detalle->precio_unitario, 2) }}</span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <span class="inline-flex items-center px-3 py-1 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 font-bold text-sm">
                                    S/ {{ number_format($detalle->subtotal, 2) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Lotes Generados -->
        @if($compra->lotes->count() > 0)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-purple-50 to-white dark:from-gray-800 dark:to-gray-800/50">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Lotes Generados</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">{{ $compra->lotes->count() }} lote(s)</p>
                    </div>
                </div>
            </div>
            
            <div class="p-6 space-y-3">
                @foreach($compra->lotes as $lote)
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150 {{ !$lote->activo ? 'opacity-60' : '' }}">
                    <div class="flex justify-between items-start">
                        <div class="flex items-start space-x-3">
                            <div class="h-10 w-10 bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="font-bold text-slate-900 dark:text-white">{{ $lote->producto->nombre }}</p>
                                <p class="text-sm text-slate-600 dark:text-slate-400 font-mono">Lote: {{ $lote->numero_lote }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Vencimiento: {{ $lote->fecha_vencimiento->format('d/m/Y') }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-slate-600 dark:text-slate-400">Stock</p>
                            <p class="text-lg font-bold text-slate-900 dark:text-white">{{ $lote->stock_actual }} / {{ $lote->stock_inicial }}</p>
                            @if($lote->activo)
                                <span class="inline-flex items-center mt-1 px-2 py-1 text-xs font-semibold rounded-full bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300">
                                    <span class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1.5 animate-pulse"></span>
                                    Disponible
                                </span>
                            @else
                                <span class="inline-flex items-center mt-1 px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 dark:bg-gray-900/30 text-gray-800 dark:text-gray-300">
                                    Inactivo
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Historial de Modificaciones -->
        @if($historial && count($historial) > 0)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-indigo-50 to-white dark:from-gray-800 dark:to-gray-800/50">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg">
                        <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Historial de Modificaciones</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">{{ count($historial) }} versión(es)</p>
                    </div>
                </div>
            </div>
            
            <div class="p-6 space-y-4">
                @foreach($historial as $index => $registro)
                <div class="border-l-4 {{ $registro['id'] == $compra->id ? 'border-indigo-400 dark:border-indigo-600' : 'border-gray-300 dark:border-gray-700' }} pl-4 py-2">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="font-bold text-slate-900 dark:text-white">
                                Compra #{{ $registro['id'] }}
                                @if($registro['id'] == $compra->id)
                                    <span class="ml-2 px-2 py-1 text-xs bg-indigo-100 dark:bg-indigo-900/30 text-indigo-800 dark:text-indigo-300 rounded-full font-semibold">Actual</span>
                                @endif
                            </p>
                            <p class="text-sm text-slate-600 dark:text-slate-400">{{ \Carbon\Carbon::parse($registro['fecha'])->format('d/m/Y') }}</p>
                            @if($registro['motivo_anulacion'])
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $registro['motivo_anulacion'] }}</p>
                            @endif
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-slate-900 dark:text-white">S/ {{ number_format($registro['total'], 2) }}</p>
                            @if($registro['es_activa'])
                                <span class="mt-1 inline-block px-2 py-1 text-xs font-semibold rounded-full bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300">Vigente</span>
                            @elseif($registro['estado'] == 'anulada')
                                <span class="mt-1 inline-block px-2 py-1 text-xs font-semibold rounded-full bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300">Anulada</span>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>

    <!-- Columna Lateral -->
    <div class="lg:col-span-1 space-y-6">
        
        <!-- Resumen Financiero -->
        <div class="relative bg-gradient-to-br from-blue-500 to-blue-600 dark:from-blue-600 dark:to-blue-700 rounded-xl shadow-lg overflow-hidden">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-white">Total de Compra</h3>
                    <svg class="w-8 h-8 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                
                <div class="space-y-4">
                    <div class="flex justify-between items-center text-white">
                        <span class="text-sm font-medium text-blue-100">Total:</span>
                        <span class="text-3xl font-bold">S/ {{ number_format($compra->total, 2) }}</span>
                    </div>
                </div>
            </div>
            <div class="absolute bottom-0 right-0 -mr-8 -mb-8 w-32 h-32 bg-blue-400/20 rounded-full"></div>
        </div>

        <!-- Estadísticas -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Estadísticas</h3>
                
                <div class="space-y-4">
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm text-slate-600 dark:text-slate-400">Productos</span>
                            <span class="font-bold text-slate-900 dark:text-white">{{ $compra->detalles->count() }}</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full" style="width: 100%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm text-slate-600 dark:text-slate-400">Unidades Totales</span>
                            <span class="font-bold text-slate-900 dark:text-white">{{ $compra->detalles->sum('cantidad') }}</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                            <div class="bg-green-600 h-2 rounded-full" style="width: 100%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm text-slate-600 dark:text-slate-400">Lotes Generados</span>
                            <span class="font-bold text-slate-900 dark:text-white">{{ $compra->lotes->count() }}</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                            <div class="bg-purple-600 h-2 rounded-full" style="width: 100%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Acciones Rápidas -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Acciones de Impresión</h3>
                
                <div class="space-y-2">
                    <!-- Imprimir (navegador) -->
                    <a href="{{ route('compras.imprimir', $compra) }}" 
                       target="_blank"
                       class="w-full inline-flex justify-center items-center px-4 py-3 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-semibold rounded-lg transition-all duration-200 hover:shadow-md">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                        </svg>
                        Imprimir Comprobante
                    </a>

                    <!-- Descargar PDF -->
                    <a href="{{ route('compras.pdf', $compra) }}" 
                       class="w-full inline-flex justify-center items-center px-4 py-3 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-semibold rounded-lg transition-all duration-200 hover:shadow-md">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Descargar PDF
                    </a>

                    <!-- Ticket Térmico -->
                    <a href="{{ route('compras.ticket', $compra) }}" 
                       target="_blank"
                       class="w-full inline-flex justify-center items-center px-4 py-3 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-semibold rounded-lg transition-all duration-200 hover:shadow-md">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z"></path>
                        </svg>
                        Imprimir Ticket (POS)
                    </a>

                    <div class="border-t border-gray-200 dark:border-gray-700 my-3"></div>

                    <!-- Ver Todas -->
                    <a href="{{ route('compras.index') }}" 
                       class="w-full inline-flex justify-center items-center px-4 py-3 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-slate-800 dark:text-slate-200 font-semibold rounded-lg transition-colors duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                        </svg>
                        Ver Todas las Compras
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Modal Anular -->
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
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Anular Compra #{{ $compra->id }}</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Esta acción no se puede deshacer</p>
                    </div>
                </div>
            </div>
            
            <form action="{{ route('compras.anular', $compra) }}" method="POST">
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
                                Esta acción anulará la compra y revertirá el stock de los productos.
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

@push('scripts')
<script>
function modalAnular(compraId) {
    document.getElementById('modalAnular').classList.remove('hidden');
}

function cerrarModalAnular() {
    document.getElementById('modalAnular').classList.add('hidden');
}

// Cerrar modal con ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        cerrarModalAnular();
    }
});
</script>
@endpush
@endsection