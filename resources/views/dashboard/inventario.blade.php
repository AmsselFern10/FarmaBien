@extends('layouts.app')

@section('title', 'Dashboard Inventario')

@section('header')
    Dashboard Inventario
@endsection

@section('page-actions')
    <div>
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white">
            Dashboard Inventario
        </h2>
        <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
            Control y gestión de stock
        </p>
    </div>
    <div>
        <a href="{{ route('compras.create') }}" 
           class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-semibold rounded-lg shadow-sm transition-all duration-200 hover:shadow-md hover:scale-105">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Nueva Compra
        </a>
    </div>
@endsection

@section('content')

<!-- Estadísticas Principales -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    
    <!-- Compras del Mes -->
    <div class="group bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-800/50 overflow-hidden shadow-card hover:shadow-card-hover rounded-xl transition-all duration-300 hover:-translate-y-1 border border-gray-100 dark:border-gray-700">
        <div class="p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl p-3 shadow-lg group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                </div>
                <div class="ml-4 flex-1">
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">
                        Compras del Mes
                    </p>
                    <p class="text-2xl font-bold text-slate-900 dark:text-white mt-1">
                        S/ {{ number_format($comprasMes, 2) }}
                    </p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        {{ $cantidadComprasMes }} {{ $cantidadComprasMes === 1 ? 'compra' : 'compras' }}
                    </p>
                </div>
            </div>
        </div>
        <div class="bg-indigo-50 dark:bg-indigo-900/20 px-6 py-3 border-t border-gray-200 dark:border-gray-700">
            <a href="{{ route('compras.index') }}" 
               class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 flex items-center">
                Ver compras
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>
    </div>

    <!-- Valorización Total -->
    <div class="group bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-800/50 overflow-hidden shadow-card hover:shadow-card-hover rounded-xl transition-all duration-300 hover:-translate-y-1 border border-gray-100 dark:border-gray-700">
        <div class="p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-gradient-to-br from-green-500 to-green-600 rounded-xl p-3 shadow-lg group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4 flex-1">
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">
                        Valor Inventario
                    </p>
                    <p class="text-2xl font-bold text-slate-900 dark:text-white mt-1">
                        S/ {{ number_format($valorizacion['valor_total'], 2) }}
                    </p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        {{ number_format($valorizacion['cantidad_total_unidades']) }} unidades
                    </p>
                </div>
            </div>
        </div>
        <div class="bg-green-50 dark:bg-green-900/20 px-6 py-3 border-t border-gray-200 dark:border-gray-700">
            <a href="{{ route('inventario.valorizacion') }}" 
               class="text-sm font-medium text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-300 flex items-center">
                Ver detalle
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>
    </div>

    <!-- Stock Bajo -->
    <div class="group bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-800/50 overflow-hidden shadow-card hover:shadow-card-hover rounded-xl transition-all duration-300 hover:-translate-y-1 border border-gray-100 dark:border-gray-700">
        <div class="p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-xl p-3 shadow-lg group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <div class="ml-4 flex-1">
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">
                        Stock Bajo
                    </p>
                    <p class="text-2xl font-bold text-slate-900 dark:text-white mt-1">
                        {{ $productosStockBajo->count() }}
                    </p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        productos
                    </p>
                </div>
            </div>
        </div>
        <div class="bg-yellow-50 dark:bg-yellow-900/20 px-6 py-3 border-t border-gray-200 dark:border-gray-700">
            <a href="{{ route('inventario.alertas') }}" 
               class="text-sm font-medium text-yellow-600 dark:text-yellow-400 hover:text-yellow-800 dark:hover:text-yellow-300 flex items-center">
                Ver alertas
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>
    </div>

    <!-- Lotes Activos -->
    <div class="group bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-800/50 overflow-hidden shadow-card hover:shadow-card-hover rounded-xl transition-all duration-300 hover:-translate-y-1 border border-gray-100 dark:border-gray-700">
        <div class="p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl p-3 shadow-lg group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
                <div class="ml-4 flex-1">
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">
                        Lotes Activos
                    </p>
                    <p class="text-2xl font-bold text-slate-900 dark:text-white mt-1">
                        {{ $valorizacion['total_lotes_activos'] }}
                    </p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        con stock
                    </p>
                </div>
            </div>
        </div>
        <div class="bg-purple-50 dark:bg-purple-900/20 px-6 py-3 border-t border-gray-200 dark:border-gray-700">
            <a href="{{ route('inventario.lotes') }}" 
               class="text-sm font-medium text-purple-600 dark:text-purple-400 hover:text-purple-800 dark:hover:text-purple-300 flex items-center">
                Ver lotes
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>
    </div>
</div>

<!-- Alertas Críticas -->
@if($lotesVencidos->isNotEmpty() || $lotesProximosVencer->isNotEmpty() || $productosStockBajo->isNotEmpty())
<div class="group bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-800/50 overflow-hidden shadow-card hover:shadow-card-hover rounded-xl transition-all duration-300 hover:-translate-y-1 border border-gray-100 dark:border-gray-700 mb-6">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-red-50 to-white dark:from-gray-800 dark:to-gray-800/50">
        <div class="flex items-center space-x-3">
            <div class="p-2 bg-red-100 dark:bg-red-900/30 rounded-lg">
                <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">🚨 Alertas Críticas</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400">Requieren atención inmediata</p>
            </div>
        </div>
    </div>
    
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            
            <!-- Lotes Vencidos -->
            @if($lotesVencidos->isNotEmpty())
            <div class="border-l-4 border-red-500 dark:border-red-400 bg-red-50 dark:bg-red-900/20 p-4 rounded-r-lg">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-red-500 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3 flex-1">
                        <h4 class="text-sm font-semibold text-red-800 dark:text-red-200">Lotes Vencidos</h4>
                        <p class="mt-1 text-sm text-red-700 dark:text-red-300">
                            <strong class="text-2xl">{{ $lotesVencidos->count() }}</strong> lotes vencidos con stock disponible
                        </p>
                        <a href="{{ route('inventario.alertas') }}" 
                           class="mt-2 inline-flex items-center text-sm font-medium text-red-700 dark:text-red-300 hover:text-red-600 dark:hover:text-red-200">
                            Ver detalles
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
            @endif

            <!-- Próximos a Vencer -->
            @if($lotesProximosVencer->isNotEmpty())
            <div class="border-l-4 border-orange-500 dark:border-orange-400 bg-orange-50 dark:bg-orange-900/20 p-4 rounded-r-lg">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-orange-500 dark:text-orange-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3 flex-1">
                        <h4 class="text-sm font-semibold text-orange-800 dark:text-orange-200">Próximos a Vencer</h4>
                        <p class="mt-1 text-sm text-orange-700 dark:text-orange-300">
                            <strong class="text-2xl">{{ $lotesProximosVencer->count() }}</strong> lotes en los próximos 30 días
                        </p>
                        <a href="{{ route('inventario.alertas') }}" 
                           class="mt-2 inline-flex items-center text-sm font-medium text-orange-700 dark:text-orange-300 hover:text-orange-600 dark:hover:text-orange-200">
                            Ver detalles
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
            @endif

            <!-- Stock Bajo -->
            @if($productosStockBajo->isNotEmpty())
            <div class="border-l-4 border-yellow-500 dark:border-yellow-400 bg-yellow-50 dark:bg-yellow-900/20 p-4 rounded-r-lg">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-yellow-500 dark:text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3 flex-1">
                        <h4 class="text-sm font-semibold text-yellow-800 dark:text-yellow-200">Stock Bajo</h4>
                        <p class="mt-1 text-sm text-yellow-700 dark:text-yellow-300">
                            <strong class="text-2xl">{{ $productosStockBajo->count() }}</strong> productos bajo stock mínimo
                        </p>
                        <a href="{{ route('inventario.alertas') }}" 
                           class="mt-2 inline-flex items-center text-sm font-medium text-yellow-700 dark:text-yellow-300 hover:text-yellow-600 dark:hover:text-yellow-200">
                            Ver detalles
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endif

<!-- Dos Columnas -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    
    <!-- Inventario por Categoría -->
    <div class="group bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-800/50 overflow-hidden shadow-card hover:shadow-card-hover rounded-xl transition-all duration-300 hover:-translate-y-1 border border-gray-100 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-indigo-50 to-white dark:from-gray-800 dark:to-gray-800/50">
            <div class="flex items-center space-x-3">
                <div class="p-2 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg">
                    <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Inventario por Categoría</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Distribución del stock</p>
                </div>
            </div>
        </div>
        
        <div class="p-6">
            <div class="space-y-3">
                @forelse($resumenCategorias as $categoria)
                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600 hover:shadow-md transition-shadow">
                    <div class="flex items-center flex-1">
                        <div class="h-10 w-10 rounded-lg bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center mr-3">
                            <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $categoria->categoria }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $categoria->total_productos }} productos</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-bold text-slate-900 dark:text-white">
                            {{ number_format($categoria->stock_total) }}
                        </p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">unidades</p>
                    </div>
                </div>
                @empty
                <p class="text-center text-slate-500 dark:text-slate-400 py-8">No hay categorías registradas</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Compras Recientes -->
    <div class="group bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-800/50 overflow-hidden shadow-card hover:shadow-card-hover rounded-xl transition-all duration-300 hover:-translate-y-1 border border-gray-100 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-green-50 to-white dark:from-gray-800 dark:to-gray-800/50">
            <div class="flex items-center space-x-3">
                <div class="p-2 bg-green-100 dark:bg-green-900/30 rounded-lg">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Compras Recientes</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Últimas adquisiciones</p>
                </div>
            </div>
        </div>
        
        <div class="p-6">
            <div class="space-y-3">
                @forelse($comprasRecientes as $compra)
                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600 hover:shadow-md transition-shadow">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-900 dark:text-white flex items-center">
                            <span class="inline-block w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                            Compra #{{ str_pad($compra->id, 6, '0', STR_PAD_LEFT) }}
                        </p>
                        <p class="text-sm text-slate-600 dark:text-slate-400 truncate">
                            {{ $compra->proveedor->nombre }}
                        </p>
                        <p class="text-xs text-slate-500 dark:text-slate-500">
                            {{ $compra->fecha->format('d/m/Y H:i') }}
                        </p>
                    </div>
                    <div class="text-right ml-4">
                        <p class="text-sm font-bold text-slate-900 dark:text-white">
                            S/ {{ number_format($compra->total, 2) }}
                        </p>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300">
                            {{ ucfirst($compra->estado) }}
                        </span>
                    </div>
                </div>
                @empty
                <p class="text-center text-slate-500 dark:text-slate-400 py-8">No hay compras recientes</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Movimientos Recientes -->
<div class="group bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-800/50 overflow-hidden shadow-card hover:shadow-card-hover rounded-xl transition-all duration-300 hover:-translate-y-1 border border-gray-100 dark:border-gray-700">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-purple-50 to-white dark:from-gray-800 dark:to-gray-800/50">
        <div class="flex items-center space-x-3">
            <div class="p-2 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
                <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Movimientos Recientes de Inventario</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400">Últimas transacciones registradas</p>
            </div>
        </div>
    </div>
    
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                        Producto
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                        Lote
                    </th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                        Tipo
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                        Cantidad
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                        Usuario
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                        Fecha
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                @forelse($movimientosRecientes as $movimiento)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-semibold text-slate-900 dark:text-white">
                            {{ $movimiento->producto->nombre }}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="text-sm font-mono text-slate-600 dark:text-slate-400 bg-slate-100 dark:bg-slate-700 px-2 py-1 rounded">
                            {{ $movimiento->lote->numero_lote }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        @if($movimiento->tipo === 'entrada')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z" clip-rule="evenodd"></path>
                                </svg>
                                Entrada
                            </span>
                        @elseif($movimiento->tipo === 'salida')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z" clip-rule="evenodd"></path>
                                </svg>
                                Salida
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300">
                                Ajuste
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right">
                        <span class="text-sm font-bold {{ $movimiento->cantidad < 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                            {{ $movimiento->cantidad > 0 ? '+' : '' }}{{ $movimiento->cantidad }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="h-8 w-8 rounded-full bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center mr-2">
                                <span class="text-xs font-semibold text-purple-600 dark:text-purple-400">
                                    {{ strtoupper(substr($movimiento->usuario->name, 0, 2)) }}
                                </span>
                            </div>
                            <span class="text-sm text-slate-600 dark:text-slate-400">
                                {{ $movimiento->usuario->name }}
                            </span>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-slate-600 dark:text-slate-400">
                            {{ $movimiento->fecha_movimiento->format('d/m/Y') }}
                        </div>
                        <div class="text-xs text-slate-500 dark:text-slate-500">
                            {{ $movimiento->fecha_movimiento->format('H:i') }}
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                        </svg>
                        <p class="mt-2 text-slate-500 dark:text-slate-400">No hay movimientos recientes</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection