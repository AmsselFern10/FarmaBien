@extends('layouts.app')

@section('title', 'Productos')

@section('header')
    Productos
@endsection

@section('page-actions')
    <div>
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white">
            Inventario de Productos
        </h2>
        <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
            Gestiona el catálogo completo de productos de tu farmacia
        </p>
    </div>
    <div class="flex gap-3">
        @can('crear productos')
        <a href="{{ route('productos.create') }}" 
           class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-semibold rounded-lg shadow-sm transition-all duration-200 hover:shadow-md hover:scale-105">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Nuevo Producto
        </a>
        @endcan
    </div>
@endsection

@section('content')
<div x-data="{ 
    viewMode: localStorage.getItem('productsViewMode') || 'grid',
    showInactive: {{ request('mostrar_inactivos') ? 'true' : 'false' }}
}" 
x-init="$watch('viewMode', val => localStorage.setItem('productsViewMode', val))"
class="space-y-6">

    <!-- Panel de Filtros y Controles -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700">
        <div class="p-6">
            <form method="GET" action="{{ route('productos.index') }}" class="space-y-4">
                <!-- Fila 1: Búsqueda y Filtros -->
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                    <!-- Búsqueda -->
                    <div class="md:col-span-5">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            Buscar producto
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <input type="text" 
                                   name="buscar" 
                                   value="{{ request('buscar') }}"
                                   placeholder="Buscar por nombre o código de barras..."
                                   class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </div>
                    </div>

                    <!-- Categoría -->
                    <div class="md:col-span-3">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            Categoría
                        </label>
                        <select name="categoria_id" 
                                class="block w-full py-2.5 px-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                            <option value="">Todas las categorías</option>
                            @foreach($categorias as $categoria)
                                <option value="{{ $categoria->id }}" {{ request('categoria_id') == $categoria->id ? 'selected' : '' }}>
                                    {{ $categoria->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="md:col-span-4 flex items-end gap-2">
                        <button type="submit" 
                                class="flex-1 inline-flex justify-center items-center px-4 py-2.5 bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white font-semibold rounded-lg transition-all duration-200">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                            </svg>
                            Filtrar
                        </button>
                        @if(request()->hasAny(['buscar', 'categoria_id']))
                        <a href="{{ route('productos.index') }}" 
                           class="px-4 py-2.5 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-slate-700 dark:text-slate-300 font-semibold rounded-lg transition-colors duration-200">
                            Limpiar
                        </a>
                        @endif
                    </div>
                </div>

                <!-- Fila 2: Opciones adicionales -->
                <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
                    <!-- Checkbox mostrar inactivos -->
                    <div class="flex items-center">
                        <input type="checkbox" 
                               name="mostrar_inactivos" 
                               id="mostrar_inactivos"
                               value="1"
                               {{ request('mostrar_inactivos') ? 'checked' : '' }}
                               onchange="this.form.submit()"
                               class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                        <label for="mostrar_inactivos" class="ml-2 text-sm font-medium text-slate-700 dark:text-slate-300">
                            Mostrar productos inactivos
                        </label>
                    </div>

                    <!-- Selector de Vista -->
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-slate-700 dark:text-slate-300 mr-2">Vista:</span>
                        <div class="inline-flex rounded-lg shadow-sm" role="group">
                            <button type="button"
                                    @click="viewMode = 'grid'"
                                    :class="viewMode === 'grid' ? 'bg-blue-500 text-white' : 'bg-white dark:bg-gray-700 text-slate-700 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-gray-600'"
                                    class="px-4 py-2 text-sm font-medium border border-gray-300 dark:border-gray-600 rounded-l-lg transition-colors duration-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                                </svg>
                            </button>
                            <button type="button"
                                    @click="viewMode = 'list'"
                                    :class="viewMode === 'list' ? 'bg-blue-500 text-white' : 'bg-white dark:bg-gray-700 text-slate-700 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-gray-600'"
                                    class="px-4 py-2 text-sm font-medium border-t border-b border-gray-300 dark:border-gray-600 transition-colors duration-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                                </svg>
                            </button>
                            <button type="button"
                                    @click="viewMode = 'table'"
                                    :class="viewMode === 'table' ? 'bg-blue-500 text-white' : 'bg-white dark:bg-gray-700 text-slate-700 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-gray-600'"
                                    class="px-4 py-2 text-sm font-medium border border-gray-300 dark:border-gray-600 rounded-r-lg transition-colors duration-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Contador de resultados -->
    <div class="flex items-center justify-between">
        <p class="text-sm text-slate-600 dark:text-slate-400">
            Mostrando <span class="font-semibold text-slate-900 dark:text-white">{{ $productos->count() }}</span> 
            de <span class="font-semibold text-slate-900 dark:text-white">{{ $productos->total() }}</span> productos
        </p>
    </div>

    <!-- VISTA GRID (Cards con imágenes) -->
    <div x-show="viewMode === 'grid'" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform scale-95"
         x-transition:enter-end="opacity-100 transform scale-100"
         class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @forelse($productos as $producto)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-xl transition-all duration-300 hover:-translate-y-1 {{ !$producto->activo ? 'opacity-60' : '' }}">
            <!-- Imagen -->
            <div class="relative h-48 bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-800 flex items-center justify-center overflow-hidden group">
                @if($producto->imagen)
                    <img src="{{ asset('storage/' . $producto->imagen) }}" 
                         alt="{{ $producto->nombre }}"
                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                @else
                    <svg class="w-20 h-20 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                @endif
                <!-- Badge de estado -->
                <div class="absolute top-3 right-3">
                    @if($producto->activo)
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-500 text-white shadow-lg">Activo</span>
                    @else
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-500 text-white shadow-lg">Inactivo</span>
                    @endif
                </div>
                @if($producto->requiere_receta)
                <div class="absolute top-3 left-3">
                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-orange-500 text-white shadow-lg flex items-center">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Receta
                    </span>
                </div>
                @endif
            </div>

            <!-- Contenido -->
            <div class="p-4">
                <h3 class="font-bold text-slate-900 dark:text-white text-base mb-2 line-clamp-2 min-h-[3rem]">
                    {{ $producto->nombre }}
                </h3>

                <p class="text-xs text-slate-500 dark:text-slate-400 mb-2 flex items-center">
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                    </svg>
                    {{ $producto->categoria->nombre }}
                </p>

                @if($producto->codigo_barra)
                <p class="text-xs text-slate-400 dark:text-slate-500 mb-3 font-mono">{{ $producto->codigo_barra }}</p>
                @endif

                <!-- Precio -->
                <div class="flex items-baseline justify-between mb-3">
                    <span class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                        S/ {{ number_format($producto->precio_venta, 2) }}
                    </span>
                </div>

                <!-- Stock -->
                <div class="mb-4 p-2 rounded-lg {{ $producto->stock_total <= $producto->stock_minimo ? 'bg-red-50 dark:bg-red-900/20' : 'bg-gray-50 dark:bg-gray-700/50' }}">
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-slate-600 dark:text-slate-400">Stock:</span>
                        <span class="font-bold {{ $producto->stock_total <= $producto->stock_minimo ? 'text-red-600 dark:text-red-400' : 'text-slate-900 dark:text-white' }}">
                            {{ $producto->stock_total }} unidades
                        </span>
                    </div>
                    @if($producto->stock_total <= $producto->stock_minimo)
                    <p class="text-xs text-red-600 dark:text-red-400 mt-1 flex items-center">
                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                        Stock bajo
                    </p>
                    @endif
                </div>

                <!-- Acciones -->
                <div class="grid grid-cols-2 gap-2">
                    @can('ver productos')
                    <a href="{{ route('productos.show', $producto) }}" 
                       class="inline-flex justify-center items-center px-3 py-2 bg-blue-500 hover:bg-blue-600 text-white text-sm font-semibold rounded-lg transition-colors duration-200">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        Ver
                    </a>
                    @endcan

                    @can('editar productos')
                    <a href="{{ route('productos.edit', $producto) }}" 
                       class="inline-flex justify-center items-center px-3 py-2 bg-gray-500 hover:bg-gray-600 text-white text-sm font-semibold rounded-lg transition-colors duration-200">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Editar
                    </a>
                    @endcan
                </div>

                <!-- Botón Desactivar -->
                @can('desactivar productos')
                @if($producto->activo && $producto->stock_total == 0)
                <form action="{{ route('productos.destroy', $producto) }}" method="POST" class="mt-2" onsubmit="return confirm('¿Está seguro de desactivar este producto?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full inline-flex justify-center items-center px-3 py-2 bg-red-50 dark:bg-red-900/20 border border-red-300 dark:border-red-800 text-red-600 dark:text-red-400 text-sm font-semibold rounded-lg hover:bg-red-100 dark:hover:bg-red-900/30 transition-colors duration-200">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                        </svg>
                        Desactivar
                    </button>
                </form>
                @endif
                @endcan
            </div>
        </div>
        @empty
        <div class="col-span-full">
            <div class="text-center py-12 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
                <p class="mt-4 text-slate-500 dark:text-slate-400 font-medium">No se encontraron productos</p>
                <p class="text-sm text-slate-400 dark:text-slate-500">Intenta ajustar los filtros de búsqueda</p>
            </div>
        </div>
        @endforelse
    </div>

    <!-- VISTA LISTA (Compacta) -->
    <div x-show="viewMode === 'list'" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform scale-95"
         x-transition:enter-end="opacity-100 transform scale-100"
         class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="divide-y divide-gray-200 dark:divide-gray-700">
            @forelse($productos as $producto)
            <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150 {{ !$producto->activo ? 'opacity-60' : '' }}">
                <div class="flex items-center gap-4">
                    <!-- Imagen pequeña -->
                    <div class="flex-shrink-0 w-16 h-16 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                        @if($producto->imagen)
                            <img src="{{ asset('storage/' . $producto->imagen) }}" alt="{{ $producto->nombre }}" class="w-full h-full object-cover">
                        @else
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                        @endif
                    </div>

                    <!-- Información -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1">
                                <h3 class="font-bold text-slate-900 dark:text-white text-sm mb-1">{{ $producto->nombre }}</h3>
                                <div class="flex items-center gap-3 text-xs text-slate-500 dark:text-slate-400">
                                    <span>{{ $producto->categoria->nombre }}</span>
                                    @if($producto->codigo_barra)
                                    <span class="font-mono">{{ $producto->codigo_barra }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="flex items-center gap-4">
                                <!-- Precio -->
                                <div class="text-right">
                                    <p class="text-xl font-bold text-blue-600 dark:text-blue-400">S/ {{ number_format($producto->precio_venta, 2) }}</p>
                                </div>

                                <!-- Stock -->
                                <div class="text-right min-w-[80px]">
                                    <p class="text-sm font-bold {{ $producto->stock_total <= $producto->stock_minimo ? 'text-red-600 dark:text-red-400' : 'text-slate-900 dark:text-white' }}">
                                        {{ $producto->stock_total }} unid.
                                    </p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Stock</p>
                                </div>

                                <!-- Badges -->
                                <div class="flex flex-col gap-1">
                                    @if($producto->activo)
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300">Activo</span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300">Inactivo</span>
                                    @endif
                                    @if($producto->requiere_receta)
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-300">Receta</span>
                                    @endif
                                </div>

                                <!-- Acciones -->
                                <div class="flex gap-2">
                                    @can('ver productos')
                                    <a href="{{ route('productos.show', $producto) }}" 
                                       class="p-2 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900/50 rounded-lg transition-colors duration-200"
                                       title="Ver detalles">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </a>
                                    @endcan

                                    @can('editar productos')
                                    <a href="{{ route('productos.edit', $producto) }}" 
                                       class="p-2 bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-600 rounded-lg transition-colors duration-200"
                                       title="Editar">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </a>
                                    @endcan

                                    @can('desactivar productos')
                                    @if($producto->activo && $producto->stock_total == 0)
                                    <form action="{{ route('productos.destroy', $producto) }}" method="POST" class="inline" onsubmit="return confirm('¿Está seguro de desactivar este producto?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="p-2 bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/30 rounded-lg transition-colors duration-200"
                                                title="Desactivar">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                                            </svg>
                                        </button>
                                    </form>
                                    @endif
                                    @endcan
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="p-12 text-center">
                <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
                <p class="mt-4 text-slate-500 dark:text-slate-400 font-medium">No se encontraron productos</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- VISTA TABLA (Estilo Excel) -->
    <div x-show="viewMode === 'table'" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform scale-95"
         x-transition:enter-end="opacity-100 transform scale-100"
         class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Producto</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Categoría</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Código</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Precio</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Stock</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($productos as $producto)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150 {{ !$producto->activo ? 'opacity-60' : '' }}">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center overflow-hidden">
                                    @if($producto->imagen)
                                        <img src="{{ asset('storage/' . $producto->imagen) }}" alt="{{ $producto->nombre }}" class="w-full h-full object-cover">
                                    @else
                                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                        </svg>
                                    @endif
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-slate-900 dark:text-white">{{ $producto->nombre }}</div>
                                    @if($producto->requiere_receta)
                                    <div class="text-xs text-orange-600 dark:text-orange-400">Requiere receta</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600 dark:text-slate-400">
                            {{ $producto->categoria->nombre }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600 dark:text-slate-400 font-mono">
                            {{ $producto->codigo_barra ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-blue-600 dark:text-blue-400">
                            S/ {{ number_format($producto->precio_venta, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <span class="text-sm font-bold {{ $producto->stock_total <= $producto->stock_minimo ? 'text-red-600 dark:text-red-400' : 'text-slate-900 dark:text-white' }}">
                                {{ $producto->stock_total }}
                            </span>
                            <span class="text-xs text-slate-500 dark:text-slate-400"> / {{ $producto->stock_minimo }} mín.</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            @if($producto->activo)
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300">
                                    Activo
                                </span>
                            @else
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300">
                                    Inactivo
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end gap-2">
                                @can('ver productos')
                                <a href="{{ route('productos.show', $producto) }}" 
                                   class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300"
                                   title="Ver">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </a>
                                @endcan

                                @can('editar productos')
                                <a href="{{ route('productos.edit', $producto) }}" 
                                   class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-300"
                                   title="Editar">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </a>
                                @endcan

                                @can('desactivar productos')
                                @if($producto->activo && $producto->stock_total == 0)
                                <form action="{{ route('productos.destroy', $producto) }}" method="POST" class="inline" onsubmit="return confirm('¿Está seguro de desactivar este producto?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300"
                                            title="Desactivar">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                                        </svg>
                                    </button>
                                </form>
                                @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                            <p class="mt-4 text-slate-500 dark:text-slate-400 font-medium">No se encontraron productos</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Paginación -->
    @if($productos->hasPages())
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 px-6 py-4">
        {{ $productos->links() }}
    </div>
    @endif
</div>
@endsection