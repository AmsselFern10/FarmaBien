@extends('layouts.app')

@section('title', 'Detalle del Producto')

@section('header')
    Detalle del Producto
@endsection

@section('page-actions')
    <div>
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white">
            {{ $producto->nombre }}
        </h2>
        <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
            Información completa del producto
        </p>
    </div>
    <div class="flex gap-3">
        <a href="{{ route('productos.index') }}" 
           class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-slate-700 dark:text-slate-300 font-semibold rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200 shadow-sm">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Volver
        </a>
        @can('editar productos')
        <a href="{{ route('productos.edit', $producto) }}" 
           class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-semibold rounded-lg shadow-sm transition-all duration-200 hover:shadow-md hover:scale-105">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
            Editar
        </a>
        @endcan
    </div>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <!-- Columna Izquierda: Imagen y Estado -->
    <div class="lg:col-span-1 space-y-6">
        
        <!-- Imagen del Producto -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-4 bg-gradient-to-r from-gray-50 to-white dark:from-gray-800 dark:to-gray-800/50 border-b border-gray-200 dark:border-gray-700">
                <h3 class="font-semibold text-slate-900 dark:text-white">Imagen</h3>
            </div>
            <div class="p-6">
                <div class="relative bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-800 rounded-xl overflow-hidden flex items-center justify-center group" style="height: 320px;">
                    @if($producto->imagen)
                        <img src="{{ asset('storage/' . $producto->imagen) }}" 
                             alt="{{ $producto->nombre }}"
                             class="w-full h-full object-contain group-hover:scale-110 transition-transform duration-300">
                    @else
                        <div class="text-center">
                            <svg class="mx-auto w-24 h-24 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-4">Sin imagen</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Estado y Badges -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-4 bg-gradient-to-r from-gray-50 to-white dark:from-gray-800 dark:to-gray-800/50 border-b border-gray-200 dark:border-gray-700">
                <h3 class="font-semibold text-slate-900 dark:text-white">Estado y Clasificación</h3>
            </div>
            <div class="p-6 space-y-4">
                <!-- Estado Activo/Inactivo -->
                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Estado</span>
                    </div>
                    @if($producto->activo)
                        <span class="px-3 py-1.5 inline-flex items-center text-sm font-semibold rounded-full bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300">
                            <span class="w-2 h-2 bg-green-500 rounded-full mr-2 animate-pulse"></span>
                            Activo
                        </span>
                    @else
                        <span class="px-3 py-1.5 inline-flex items-center text-sm font-semibold rounded-full bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300">
                            <span class="w-2 h-2 bg-red-500 rounded-full mr-2"></span>
                            Inactivo
                        </span>
                    @endif
                </div>
                
                <!-- Requiere Receta -->
                @if($producto->requiere_receta)
                <div class="flex items-center justify-between p-4 bg-orange-50 dark:bg-orange-900/20 rounded-lg border border-orange-200 dark:border-orange-800">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <span class="text-sm font-medium text-orange-800 dark:text-orange-300">Requiere Receta</span>
                    </div>
                    <svg class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                @endif

                <!-- Nivel de Stock -->
                <div class="p-4 rounded-lg {{ $producto->stock_total <= 0 ? 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800' : ($producto->stock_total <= $producto->stock_minimo ? 'bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800' : 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800') }}">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2 {{ $producto->stock_total <= 0 ? 'text-red-600 dark:text-red-400' : ($producto->stock_total <= $producto->stock_minimo ? 'text-yellow-600 dark:text-yellow-400' : 'text-green-600 dark:text-green-400') }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                            <span class="text-sm font-medium {{ $producto->stock_total <= 0 ? 'text-red-800 dark:text-red-300' : ($producto->stock_total <= $producto->stock_minimo ? 'text-yellow-800 dark:text-yellow-300' : 'text-green-800 dark:text-green-300') }}">
                                Nivel de Stock
                            </span>
                        </div>
                        @if($producto->stock_total <= 0)
                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300">Agotado</span>
                        @elseif($producto->stock_total <= $producto->stock_minimo)
                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300">Bajo</span>
                        @else
                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300">Normal</span>
                        @endif
                    </div>
                </div>

                <!-- Categoría -->
                <div class="flex items-center justify-between p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                        </svg>
                        <span class="text-sm font-medium text-blue-800 dark:text-blue-300">Categoría</span>
                    </div>
                    <span class="px-3 py-1 text-sm font-semibold rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300">
                        {{ $producto->categoria->nombre }}
                    </span>
                </div>
            </div>
        </div>

    </div>

    <!-- Columna Derecha: Información Detallada -->
    <div class="lg:col-span-2 space-y-6">
        
        <!-- Información General -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-white dark:from-gray-800 dark:to-gray-800/50 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Información General</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Datos básicos del producto</p>
                    </div>
                </div>
            </div>
            
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Nombre -->
                    <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Nombre del Producto</p>
                        <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $producto->nombre }}</p>
                    </div>

                    <!-- Código de Barra -->
                    @if($producto->codigo_barra)
                    <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Código de Barra</p>
                        <p class="text-sm font-mono font-semibold text-slate-900 dark:text-white">{{ $producto->codigo_barra }}</p>
                    </div>
                    @endif

                    <!-- Ubicación -->
                    @if($producto->ubicacion)
                    <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Ubicación en Almacén</p>
                        <p class="text-sm font-semibold text-slate-900 dark:text-white flex items-center">
                            <svg class="w-4 h-4 mr-1 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            {{ $producto->ubicacion }}
                        </p>
                    </div>
                    @endif

                    <!-- Descripción -->
                    @if($producto->descripcion)
                    <div class="md:col-span-2 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-2">Descripción</p>
                        <p class="text-sm text-slate-700 dark:text-slate-300">{{ $producto->descripcion }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Precios -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-white dark:from-gray-800 dark:to-gray-800/50 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-green-100 dark:bg-green-900/30 rounded-lg">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Información de Precios</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Precios y márgenes de ganancia</p>
                    </div>
                </div>
            </div>
            
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Precio de Compra -->
                    <div class="relative overflow-hidden bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-900/30 rounded-xl p-6 border border-blue-200 dark:border-blue-800">
                        <div class="relative z-10">
                            <div class="flex items-center justify-between mb-2">
                                <p class="text-xs font-semibold text-blue-700 dark:text-blue-300 uppercase tracking-wide">Compra</p>
                                <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                            <p class="text-3xl font-bold text-blue-700 dark:text-blue-300">S/ {{ number_format($producto->precio_compra, 2) }}</p>
                        </div>
                        <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-blue-200/30 dark:bg-blue-800/20 rounded-full"></div>
                    </div>

                    <!-- Precio de Venta -->
                    <div class="relative overflow-hidden bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-900/30 rounded-xl p-6 border border-green-200 dark:border-green-800">
                        <div class="relative z-10">
                            <div class="flex items-center justify-between mb-2">
                                <p class="text-xs font-semibold text-green-700 dark:text-green-300 uppercase tracking-wide">Venta</p>
                                <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                            <p class="text-3xl font-bold text-green-700 dark:text-green-300">S/ {{ number_format($producto->precio_venta, 2) }}</p>
                        </div>
                        <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-green-200/30 dark:bg-green-800/20 rounded-full"></div>
                    </div>

                    <!-- Margen de Ganancia -->
                    <div class="relative overflow-hidden bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-900/30 rounded-xl p-6 border border-purple-200 dark:border-purple-800">
                        <div class="relative z-10">
                            <div class="flex items-center justify-between mb-2">
                                <p class="text-xs font-semibold text-purple-700 dark:text-purple-300 uppercase tracking-wide">Margen</p>
                                <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                </svg>
                            </div>
                            <p class="text-3xl font-bold text-purple-700 dark:text-purple-300">
                                {{ $producto->precio_compra > 0 ? number_format((($producto->precio_venta - $producto->precio_compra) / $producto->precio_compra) * 100, 1) : 0 }}%
                            </p>
                        </div>
                        <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-purple-200/30 dark:bg-purple-800/20 rounded-full"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Inventario -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-white dark:from-gray-800 dark:to-gray-800/50 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg">
                        <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Control de Inventario</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Niveles de stock y disponibilidad</p>
                    </div>
                </div>
            </div>
            
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Stock Total -->
                    <div class="p-5 border-2 border-gray-200 dark:border-gray-700 rounded-xl hover:border-blue-300 dark:hover:border-blue-600 transition-colors duration-200">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Total</span>
                            <div class="p-2 bg-gray-100 dark:bg-gray-700 rounded-lg">
                                <svg class="w-4 h-4 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-slate-900 dark:text-white mb-1">{{ $producto->stock_total }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">unidades</p>
                    </div>

                    <!-- Stock Disponible -->
                    <div class="p-5 border-2 border-green-200 dark:border-green-800 rounded-xl hover:border-green-300 dark:hover:border-green-600 transition-colors duration-200 bg-green-50/50 dark:bg-green-900/10">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-semibold text-green-700 dark:text-green-400 uppercase tracking-wide">Disponible</span>
                            <div class="p-2 bg-green-100 dark:bg-green-900/30 rounded-lg">
                                <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-green-700 dark:text-green-400 mb-1">{{ $stockDisponible }}</p>
                        <p class="text-xs text-green-600 dark:text-green-500">unidades</p>
                    </div>

                    <!-- Stock Reservado -->
                    <div class="p-5 border-2 border-yellow-200 dark:border-yellow-800 rounded-xl hover:border-yellow-300 dark:hover:border-yellow-600 transition-colors duration-200 bg-yellow-50/50 dark:bg-yellow-900/10">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-semibold text-yellow-700 dark:text-yellow-400 uppercase tracking-wide">Reservado</span>
                            <div class="p-2 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg">
                                <svg class="w-4 h-4 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-yellow-700 dark:text-yellow-400 mb-1">{{ $producto->stock_total - $stockDisponible }}</p>
                        <p class="text-xs text-yellow-600 dark:text-yellow-500">unidades</p>
                    </div>

                    <!-- Stock Mínimo -->
                    <div class="p-5 border-2 border-red-200 dark:border-red-800 rounded-xl hover:border-red-300 dark:hover:border-red-600 transition-colors duration-200 bg-red-50/50 dark:bg-red-900/10">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-semibold text-red-700 dark:text-red-400 uppercase tracking-wide">Mínimo</span>
                            <div class="p-2 bg-red-100 dark:bg-red-900/30 rounded-lg">
                                <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-red-700 dark:text-red-400 mb-1">{{ $producto->stock_minimo }}</p>
                        <p class="text-xs text-red-600 dark:text-red-500">unidades</p>
                    </div>
                </div>

                <!-- Alertas de Stock -->
                @if($producto->stock_total <= $producto->stock_minimo && $producto->stock_total > 0)
                <div class="mt-6 bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-400 dark:border-yellow-600 p-4 rounded-lg">
                    <div class="flex">
                        <svg class="h-6 w-6 text-yellow-600 dark:text-yellow-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <p class="text-sm font-semibold text-yellow-800 dark:text-yellow-300">Alerta de Stock Bajo</p>
                            <p class="text-sm text-yellow-700 dark:text-yellow-400 mt-1">
                                El stock está por debajo del mínimo establecido ({{ $producto->stock_minimo }} unidades). Se recomienda realizar un pedido pronto.
                            </p>
                        </div>
                    </div>
                </div>
                @elseif($producto->stock_total <= 0)
                <div class="mt-6 bg-red-50 dark:bg-red-900/20 border-l-4 border-red-400 dark:border-red-600 p-4 rounded-lg">
                    <div class="flex">
                        <svg class="h-6 w-6 text-red-600 dark:text-red-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <p class="text-sm font-semibold text-red-800 dark:text-red-300">Producto Agotado</p>
                            <p class="text-sm text-red-700 dark:text-red-400 mt-1">
                                No hay stock disponible de este producto. Es necesario realizar un pedido urgente.
                            </p>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Metadatos -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-white dark:from-gray-800 dark:to-gray-800/50 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-gray-100 dark:bg-gray-700 rounded-lg">
                        <svg class="w-6 h-6 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Información del Sistema</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Registro y actualizaciones</p>
                    </div>
                </div>
            </div>
            
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex items-center p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <svg class="w-5 h-5 text-slate-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <div>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Creado el</p>
                            <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $producto->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                    <div class="flex items-center p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <svg class="w-5 h-5 text-slate-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        <div>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Última actualización</p>
                            <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $producto->updated_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection