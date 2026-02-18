@extends('layouts.app')

@section('title', 'Crear Producto')

@section('header')
    Crear Producto
@endsection

@section('page-actions')
    <div>
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white">
            Nuevo Producto
        </h2>
        <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
            Completa la información para agregar un nuevo producto al inventario
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
    </div>
@endsection

@section('content')
<div class="max-w-5xl mx-auto">
    <form action="{{ route('productos.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <!-- Información Básica -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-gray-50 to-white dark:from-gray-800 dark:to-gray-800/50">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Información Básica</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Datos generales del producto</p>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <!-- Nombre -->
                    <div class="md:col-span-2">
                        <label for="nombre" class="block text-sm font-medium text-slate-900 dark:text-white mb-2">
                            Nombre del Producto <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                            </div>
                            <input type="text" 
                                   name="nombre" 
                                   id="nombre" 
                                   value="{{ old('nombre') }}"
                                   class="block w-full pl-10 pr-3 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors duration-200 @error('nombre') border-red-500 @enderror"
                                   placeholder="Ej: Paracetamol 500mg"
                                   required>
                        </div>
                        @error('nombre')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Categoría -->
                    <div>
                        <label for="categoria_id" class="block text-sm font-medium text-slate-900 dark:text-white mb-2">
                            Categoría <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                </svg>
                            </div>
                            <select name="categoria_id" 
                                    id="categoria_id"
                                    class="block w-full pl-10 pr-3 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors duration-200 @error('categoria_id') border-red-500 @enderror"
                                    required>
                                <option value="">Seleccione una categoría</option>
                                @foreach($categorias as $categoria)
                                    <option value="{{ $categoria->id }}" {{ old('categoria_id') == $categoria->id ? 'selected' : '' }}>
                                        {{ $categoria->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('categoria_id')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Código de Barra -->
                    <div>
                        <label for="codigo_barra" class="block text-sm font-medium text-slate-900 dark:text-white mb-2">
                            Código de Barra
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                                </svg>
                            </div>
                            <input type="text" 
                                   name="codigo_barra" 
                                   id="codigo_barra"
                                   value="{{ old('codigo_barra') }}"
                                   class="block w-full pl-10 pr-3 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white font-mono transition-colors duration-200 @error('codigo_barra') border-red-500 @enderror"
                                   placeholder="7501234567890">
                        </div>
                        @error('codigo_barra')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Descripción -->
                    <div class="md:col-span-2">
                        <label for="descripcion" class="block text-sm font-medium text-slate-900 dark:text-white mb-2">
                            Descripción
                        </label>
                        <div class="relative">
                            <div class="absolute top-3 left-3 pointer-events-none">
                                <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path>
                                </svg>
                            </div>
                            <textarea name="descripcion" 
                                      id="descripcion" 
                                      rows="3"
                                      class="block w-full pl-10 pr-3 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors duration-200 @error('descripcion') border-red-500 @enderror"
                                      placeholder="Descripción detallada del producto (opcional)">{{ old('descripcion') }}</textarea>
                        </div>
                        @error('descripcion')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>


                    <!-- Datos farmacológicos (opcional) -->
                    <div class="md:col-span-2">
                        <div class="flex items-center gap-2 mb-3 mt-2">
                            <div class="p-2 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg">
                                <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8m-8 0a2 2 0 00-2 2v2a2 2 0 002 2h8a2 2 0 002-2V6a2 2 0 00-2-2m-8 0V2m8 2V2"></path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-sm font-semibold text-slate-900 dark:text-white">Datos farmacológicos (opcional)</h4>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Mejora la búsqueda y la ficha IA del producto</p>
                            </div>
                        </div>
                    </div>

                    <!-- Principio activo -->
                    <div>
                        <label for="principio_activo" class="block text-sm font-medium text-slate-900 dark:text-white mb-2">
                            Principio activo
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M12 21a9 9 0 100-18 9 9 0 000 18z"></path>
                                </svg>
                            </div>
                            <input type="text"
                                   name="principio_activo"
                                   id="principio_activo"
                                   value="{{ old('principio_activo') }}"
                                   class="block w-full pl-10 pr-3 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white transition-colors duration-200 @error('principio_activo') border-red-500 @enderror"
                                   placeholder="Ej: Paracetamol">
                        </div>
                        @error('principio_activo')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Concentración -->
                    <div>
                        <label for="concentracion" class="block text-sm font-medium text-slate-900 dark:text-white mb-2">
                            Concentración
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v18m0 0l-4-4m4 4l4-4M6 7h12"></path>
                                </svg>
                            </div>
                            <input type="text"
                                   name="concentracion"
                                   id="concentracion"
                                   value="{{ old('concentracion') }}"
                                   class="block w-full pl-10 pr-3 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white transition-colors duration-200 @error('concentracion') border-red-500 @enderror"
                                   placeholder="Ej: 500 mg, 250 mg/5 mL">
                        </div>
                        @error('concentracion')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Laboratorio -->
                    <div>
                        <label for="laboratorio" class="block text-sm font-medium text-slate-900 dark:text-white mb-2">
                            Laboratorio
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18M5 21V7a2 2 0 012-2h10a2 2 0 012 2v14M9 21V9h6v12"></path>
                                </svg>
                            </div>
                            <input type="text"
                                   name="laboratorio"
                                   id="laboratorio"
                                   value="{{ old('laboratorio') }}"
                                   class="block w-full pl-10 pr-3 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white transition-colors duration-200 @error('laboratorio') border-red-500 @enderror"
                                   placeholder="Ej: Bayer, MK, Genfar">
                        </div>
                        @error('laboratorio')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Vía de administración -->
                    <div>
                        <label for="via_administracion" class="block text-sm font-medium text-slate-900 dark:text-white mb-2">
                            Vía de administración
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8L13 15M3 17l6-6m0 0V5m0 6H3"></path>
                                </svg>
                            </div>
                            <input type="text"
                                   name="via_administracion"
                                   id="via_administracion"
                                   value="{{ old('via_administracion') }}"
                                   class="block w-full pl-10 pr-3 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white transition-colors duration-200 @error('via_administracion') border-red-500 @enderror"
                                   placeholder="Ej: Oral, Tópica, IM, IV">
                        </div>
                        @error('via_administracion')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>


                    <!-- Ubicación -->
                    <div>
                        <label for="ubicacion" class="block text-sm font-medium text-slate-900 dark:text-white mb-2">
                            Ubicación en Almacén
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                            <input type="text" 
                                   name="ubicacion" 
                                   id="ubicacion"
                                   value="{{ old('ubicacion') }}"
                                   placeholder="Ej: Estante A-3, Anaquel 2"
                                   class="block w-full pl-10 pr-3 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors duration-200 @error('ubicacion') border-red-500 @enderror">
                        </div>
                        @error('ubicacion')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Stock Mínimo -->
                    <div>
                        <label for="stock_minimo" class="block text-sm font-medium text-slate-900 dark:text-white mb-2">
                            Stock Mínimo <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                            <input type="number" 
                                   name="stock_minimo" 
                                   id="stock_minimo"
                                   value="{{ old('stock_minimo', 10) }}"
                                   min="0"
                                   class="block w-full pl-10 pr-3 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors duration-200 @error('stock_minimo') border-red-500 @enderror"
                                   required>
                        </div>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Cantidad mínima antes de alertar por stock bajo</p>
                        @error('stock_minimo')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Precios -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-gray-50 to-white dark:from-gray-800 dark:to-gray-800/50">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-green-100 dark:bg-green-900/30 rounded-lg">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Precios</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Precio de compra y venta</p>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    
                    <!-- Precio Compra -->
                    <div>
                        <label for="precio_compra" class="block text-sm font-medium text-slate-900 dark:text-white mb-2">
                            Precio de Compra (S/) <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-slate-500 dark:text-slate-400 font-bold">S/</span>
                            </div>
                            <input type="number" 
                                   name="precio_compra" 
                                   id="precio_compra"
                                   value="{{ old('precio_compra') }}"
                                   step="0.01"
                                   min="0"
                                   class="block w-full pl-10 pr-3 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 dark:bg-gray-700 dark:text-white transition-colors duration-200 @error('precio_compra') border-red-500 @enderror"
                                   placeholder="0.00"
                                   required>
                        </div>
                        @error('precio_compra')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Precio Venta -->
                    <div>
                        <label for="precio_venta" class="block text-sm font-medium text-slate-900 dark:text-white mb-2">
                            Precio de Venta (S/) <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-slate-500 dark:text-slate-400 font-bold">S/</span>
                            </div>
                            <input type="number" 
                                   name="precio_venta" 
                                   id="precio_venta"
                                   value="{{ old('precio_venta') }}"
                                   step="0.01"
                                   min="0"
                                   class="block w-full pl-10 pr-3 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 dark:bg-gray-700 dark:text-white transition-colors duration-200 @error('precio_venta') border-red-500 @enderror"
                                   placeholder="0.00"
                                   required>
                        </div>
                        @error('precio_venta')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Margen de Ganancia -->
                    <div>
                        <label class="block text-sm font-medium text-slate-900 dark:text-white mb-2">
                            Margen de Ganancia
                        </label>
                        <div class="flex items-center h-[50px] px-4 py-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                            <span id="margen" class="text-lg font-bold text-blue-600 dark:text-blue-400">0%</span>
                        </div>
                    </div>
                </div>

                <!-- Ayuda de cálculo -->
                <div class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                    <div class="flex">
                        <svg class="h-5 w-5 text-blue-600 dark:text-blue-400 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                        </svg>
                        <p class="text-sm text-blue-800 dark:text-blue-300">
                            El precio de venta se calculará automáticamente con un <strong>30% de margen</strong> sobre el precio de compra. Puedes modificarlo manualmente si lo deseas.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Imagen y Opciones -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-gray-50 to-white dark:from-gray-800 dark:to-gray-800/50">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Imagen y Opciones</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Imagen del producto y configuraciones adicionales</p>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <!-- Imagen -->
                    <div>
                        <label for="imagen" class="block text-sm font-medium text-slate-900 dark:text-white mb-2">
                            Imagen del Producto
                        </label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 dark:border-gray-600 border-dashed rounded-lg hover:border-blue-400 dark:hover:border-blue-500 transition-colors duration-200">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-gray-600 dark:text-gray-400">
                                    <label for="imagen" class="relative cursor-pointer bg-white dark:bg-gray-700 rounded-md font-medium text-blue-600 dark:text-blue-400 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                        <span>Subir archivo</span>
                                        <input id="imagen" name="imagen" type="file" class="sr-only" accept="image/*">
                                    </label>
                                    <p class="pl-1">o arrastrar aquí</p>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">PNG, JPG hasta 2MB</p>
                            </div>
                        </div>
                        @error('imagen')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Preview de Imagen -->
                    <div id="imagePreview" class="hidden">
                        <label class="block text-sm font-medium text-slate-900 dark:text-white mb-2">
                            Vista Previa
                        </label>
                        <div class="relative">
                            <img src="" alt="Preview" class="w-full h-48 object-cover rounded-lg border border-gray-300 dark:border-gray-600">
                            <button type="button" 
                                    onclick="document.getElementById('imagen').value=''; document.getElementById('imagePreview').classList.add('hidden');"
                                    class="absolute top-2 right-2 p-2 bg-red-500 text-white rounded-full hover:bg-red-600 transition-colors duration-200">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Checkboxes -->
                    <div class="md:col-span-2 space-y-4">
                        <!-- Requiere Receta -->
                        <div class="flex items-start p-4 bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded-lg">
                            <div class="flex items-center h-5">
                                <input type="checkbox" 
                                       name="requiere_receta" 
                                       id="requiere_receta"
                                       value="1"
                                       {{ old('requiere_receta') ? 'checked' : '' }}
                                       class="w-4 h-4 text-orange-600 bg-gray-100 border-gray-300 rounded focus:ring-orange-500 dark:focus:ring-orange-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                            </div>
                            <div class="ml-3">
                                <label for="requiere_receta" class="font-medium text-slate-900 dark:text-white">
                                    Requiere Receta Médica
                                </label>
                                <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
                                    Marca esta opción si el producto necesita receta médica para su venta
                                </p>
                            </div>
                        </div>

                        <!-- Producto Activo -->
                        <div class="flex items-start p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                            <div class="flex items-center h-5">
                                <input type="checkbox" 
                                       name="activo" 
                                       id="activo"
                                       value="1"
                                       {{ old('activo', true) ? 'checked' : '' }}
                                       class="w-4 h-4 text-green-600 bg-gray-100 border-gray-300 rounded focus:ring-green-500 dark:focus:ring-green-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                            </div>
                            <div class="ml-3">
                                <label for="activo" class="font-medium text-slate-900 dark:text-white">
                                    Producto Activo
                                </label>
                                <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
                                    Los productos activos estarán disponibles para la venta
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botones de Acción -->
        <div class="flex items-center justify-between">
            <a href="{{ route('productos.index') }}" 
               class="px-6 py-3 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-slate-700 dark:text-slate-300 font-semibold rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors duration-200">
                Cancelar
            </a>
            <button type="submit" 
                    class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-semibold rounded-lg shadow-sm transition-all duration-200 hover:shadow-md hover:scale-105">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                Crear Producto
            </button>
        </div>

    </form>
</div>

@push('scripts')
<script>
    // Preview de imagen
    document.getElementById('imagen').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('imagePreview');
                preview.querySelector('img').src = e.target.result;
                preview.classList.remove('hidden');
            }
            reader.readAsDataURL(file);
        }
    });

    // Calcular precio de venta sugerido (30% de margen) y mostrar margen
    function calcularMargen() {
        const precioCompra = parseFloat(document.getElementById('precio_compra').value) || 0;
        const precioVenta = parseFloat(document.getElementById('precio_venta').value) || 0;
        
        if (precioCompra > 0 && precioVenta > 0) {
            const margen = ((precioVenta - precioCompra) / precioCompra * 100).toFixed(2);
            document.getElementById('margen').textContent = margen + '%';
        } else {
            document.getElementById('margen').textContent = '0%';
        }
    }

    document.getElementById('precio_compra').addEventListener('input', function() {
        const precioCompra = parseFloat(this.value);
        const precioVentaInput = document.getElementById('precio_venta');
        
        if (precioCompra > 0 && !precioVentaInput.value) {
            const sugerido = (precioCompra * 1.30).toFixed(2);
            precioVentaInput.value = sugerido;
        }
        
        calcularMargen();
    });

    document.getElementById('precio_venta').addEventListener('input', function() {
        calcularMargen();
    });
</script>
@endpush
@endsection