@extends('layouts.app')

@section('title', 'Nueva Venta')

@section('header')
    Registrar Venta
@endsection

@section('page-actions')
    <div>
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white">
            Nueva Venta
        </h2>
        <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
            Registra una nueva transacción de venta
        </p>
    </div>
    <div>
        <a href="{{ route('ventas.index') }}" 
           class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-slate-700 dark:text-slate-300 font-semibold rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors shadow-sm">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Volver
        </a>
    </div>
@endsection

@section('content')
<div x-data="ventaForm()" x-init="init()" class="space-y-6">
    <form @submit.prevent="submitForm">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- COLUMNA IZQUIERDA: Cliente y Productos -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Cliente y Método de Pago -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-blue-50 to-white dark:from-gray-800 dark:to-gray-800/50">
                        <div class="flex items-center space-x-3">
                            <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                                <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Cliente</h3>
                                <p class="text-sm text-slate-500 dark:text-slate-400">Selecciona o registra un cliente</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-6 space-y-4">
                        <!-- Selector de Cliente -->
                        <div>
                            <label for="cliente_id" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                                Cliente
                            </label>
                            <select x-model="clienteId" 
                                    id="cliente_id"
                                    class="block w-full px-4 py-3 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Público General (Sin cliente)</option>
                                @foreach($clientes as $cliente)
                                    <option value="{{ $cliente->id }}">{{ $cliente->nombre }} - {{ $cliente->documento }}</option>
                                @endforeach
                            </select>
                            <a href="{{ route('clientes.create') }}" 
                               target="_blank"
                               class="mt-2 inline-flex items-center text-sm font-medium text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Agregar nuevo cliente
                            </a>
                        </div>

                        <!-- Método de Pago -->
                        <div>
                            <label for="metodo_pago" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                                Método de Pago <span class="text-red-500">*</span>
                            </label>
                            <select x-model="metodoPago" 
                                    @change="resetCamposPago()"
                                    id="metodo_pago"
                                    required
                                    class="block w-full px-4 py-3 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">-- Seleccione método de pago --</option>
                                <option value="efectivo">💵 Efectivo</option>
                                <option value="transferencia">🏦 Transferencia Bancaria</option>
                                <option value="credito">💳 Tarjeta de Crédito</option>
                                <option value="debito">💳 Tarjeta de Débito</option>
                                <option value="otros">📱 Otros (Yape, Plin, etc.)</option>
                            </select>
                        </div>

                        <!-- Campos específicos por método de pago -->
                        
                        <!-- EFECTIVO -->
                        <div x-show="metodoPago === 'efectivo'" x-transition class="space-y-4 p-4 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-green-800 dark:text-green-200 mb-2">
                                        Dinero Recibido <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-green-600 dark:text-green-400 font-semibold">S/</span>
                                        <input type="number" 
                                               x-model.number="dineroRecibido"
                                               @input="calcularVuelto()"
                                               step="0.01"
                                               min="0"
                                               :required="metodoPago === 'efectivo'"
                                               class="block w-full pl-10 pr-4 py-3 bg-white dark:bg-gray-700 border border-green-300 dark:border-green-700 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-green-500">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-green-800 dark:text-green-200 mb-2">
                                        Vuelto
                                    </label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-green-600 dark:text-green-400 font-semibold">S/</span>
                                        <input type="text" 
                                               :value="vuelto.toFixed(2)"
                                               readonly
                                               class="block w-full pl-10 pr-4 py-3 bg-green-100 dark:bg-green-900/30 border border-green-300 dark:border-green-700 rounded-lg text-green-900 dark:text-green-100 font-bold">
                                    </div>
                                </div>
                            </div>
                            <p x-show="vuelto < 0" class="text-sm text-red-600 dark:text-red-400 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                                El dinero recibido es insuficiente
                            </p>
                        </div>

                        <!-- TARJETA DE CRÉDITO / DÉBITO -->
                        <div x-show="metodoPago === 'credito' || metodoPago === 'debito'" x-transition class="p-4 bg-purple-50 dark:bg-purple-900/20 rounded-lg border border-purple-200 dark:border-purple-800">
                            <label class="block text-sm font-medium text-purple-800 dark:text-purple-200 mb-2">
                                Banco Emisor
                            </label>
                            <input type="text"
                                   x-model="bancoEmisor"
                                   list="bancos"
                                   :required="metodoPago === 'credito' || metodoPago === 'debito'"
                                   placeholder="Ej: BAC, LAFISE, BANPRO, FICOHSA..."
                                   class="block w-full px-4 py-3 bg-white dark:bg-gray-700 border border-purple-300 dark:border-purple-700 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-purple-500">
                            <datalist id="bancos">
                                <option value="BAC">
                                <option value="LAFISE">
                                <option value="BANPRO">
                                <option value="FICOHSA">
                                <option value="BANCO POPULAR">
                                <option value="BANCO GENERAL">
                            </datalist>
                        </div>

                        <!-- TRANSFERENCIA -->
                        <div x-show="metodoPago === 'transferencia'" x-transition class="p-4 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg border border-indigo-200 dark:border-indigo-800">
                            <label class="block text-sm font-medium text-indigo-800 dark:text-indigo-200 mb-2">
                                Banco de Transferencia
                            </label>
                            <input type="text"
                                   x-model="bancoTransferencia"
                                   list="bancos"
                                   :required="metodoPago === 'transferencia'"
                                   placeholder="Ej: BAC, LAFISE, BANPRO, FICOHSA..."
                                   class="block w-full px-4 py-3 bg-white dark:bg-gray-700 border border-indigo-300 dark:border-indigo-700 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <!-- OTROS (Yape, Plin, etc) -->
                        <div x-show="metodoPago === 'otros'" x-transition class="p-4 bg-amber-50 dark:bg-amber-900/20 rounded-lg border border-amber-200 dark:border-amber-800">
                            <label class="block text-sm font-medium text-amber-800 dark:text-amber-200 mb-2">
                                Especificar Método
                            </label>
                            <input type="text"
                                   x-model="metodoOtros"
                                   :required="metodoPago === 'otros'"
                                   placeholder="Ej: Yape, Plin, PayPal, etc..."
                                   class="block w-full px-4 py-3 bg-white dark:bg-gray-700 border border-amber-300 dark:border-amber-700 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-amber-500">
                        </div>
                    </div>
                </div>

                <!-- Búsqueda y Filtro de Productos -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-emerald-50 to-white dark:from-gray-800 dark:to-gray-800/50">
                        <div class="flex items-center space-x-3">
                            <div class="p-2 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg">
                                <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Buscar Productos</h3>
                                <p class="text-sm text-slate-500 dark:text-slate-400">Busca por nombre o código de barras</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-6">
                        <!-- Buscador -->
                        <div class="relative mb-6">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <input type="text" 
                                   x-model="busqueda"
                                   @input.debounce.300ms="buscarProductos"
                                   placeholder="Buscar productos..."
                                   class="block w-full pl-12 pr-4 py-3 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                        </div>

                        <!-- Escaneo de Código de Barras -->
                        <div class="p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg mb-6">
                            <div class="flex items-center space-x-3 mb-3">
                                <svg class="w-5 h-5 text-amber-700 dark:text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                                </svg>
                                <span class="text-sm font-semibold text-amber-900 dark:text-amber-100">Código de Barras</span>
                            </div>
                            <div class="flex gap-3">
                                <input type="text"
                                       x-model="codigoBarras"
                                       @keyup.enter="buscarPorCodigoBarras"
                                       placeholder="Escanea o escribe el código..."
                                       class="flex-1 px-4 py-2 bg-white dark:bg-gray-700 border border-amber-300 dark:border-amber-700 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-amber-500">
                                <button type="button"
                                        @click="buscarPorCodigoBarras"
                                        class="px-6 py-2 bg-amber-500 hover:bg-amber-600 text-white font-semibold rounded-lg transition-colors">
                                    Buscar
                                </button>
                            </div>
                        </div>

                        <!-- Grid de Productos (Cards con Imágenes) -->
                        <div x-show="resultados.length > 0" class="space-y-3">
                            <p class="text-sm font-medium text-slate-700 dark:text-slate-300">
                                <span x-text="resultados.length"></span> producto(s) encontrado(s)
                            </p>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-4 max-h-[500px] overflow-y-auto pr-2">
                                <template x-for="producto in resultados" :key="producto.id">
                                    <div @click="seleccionarProducto(producto)"
                                         class="group cursor-pointer bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl overflow-hidden hover:shadow-lg hover:border-emerald-500 dark:hover:border-emerald-400 transition-all duration-200">
                                        
                                        <!-- Imagen del Producto -->
                                        <div class="aspect-square bg-gray-100 dark:bg-gray-600 overflow-hidden relative">
                                            <template x-if="producto.imagen">
                                                <img :src="`/storage/${producto.imagen}`" 
                                                     :alt="producto.nombre"
                                                     class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                                            </template>
                                            <template x-if="!producto.imagen">
                                                <div class="w-full h-full flex items-center justify-center">
                                                    <svg class="w-16 h-16 text-gray-300 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                                    </svg>
                                                </div>
                                            </template>
                                            <!-- Badge de Stock -->
                                            <div class="absolute top-2 right-2">
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full"
                                                      :class="producto.stock_disponible > 10 
                                                        ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' 
                                                        : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300'">
                                                    <span x-text="producto.stock_disponible"></span> unid.
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Info del Producto -->
                                        <div class="p-3">
                                            <h4 class="font-semibold text-sm text-slate-900 dark:text-white truncate mb-1" x-text="producto.nombre"></h4>
                                            <p class="text-xs text-slate-500 dark:text-slate-400 mb-2" x-text="producto.categoria?.nombre || 'Sin categoría'"></p>
                                            <div class="flex items-center justify-between">
                                                <span class="text-lg font-bold text-emerald-600 dark:text-emerald-400">
                                                    S/ <span x-text="Number(producto.precio_venta).toFixed(2)"></span>
                                                </span>
                                                <button type="button"
                                                        class="p-1.5 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 rounded-lg group-hover:bg-emerald-500 group-hover:text-white transition-colors">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Estado Vacío -->
                        <div x-show="busqueda.length >= 2 && resultados.length === 0" class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M12 12h.01M12 20l9-9m0 0l-9-9m9 9H3"></path>
                            </svg>
                            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">No se encontraron productos</p>
                        </div>
                    </div>
                </div>

            </div>

            <!-- COLUMNA DERECHA: Ticket de Venta -->
            <div class="lg:col-span-1">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden sticky top-6">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-slate-50 to-white dark:from-gray-800 dark:to-gray-800/50">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="p-2 bg-slate-100 dark:bg-slate-700 rounded-lg">
                                    <svg class="w-6 h-6 text-slate-600 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Ticket de Venta</h3>
                                    <p class="text-sm text-slate-500 dark:text-slate-400" x-text="`${productosVenta.length} producto(s)`"></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Productos en el Ticket -->
                    <div class="p-4 max-h-[400px] overflow-y-auto">
                        <template x-if="productosVenta.length === 0">
                            <div class="text-center py-12">
                                <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400 font-medium">Carrito vacío</p>
                                <p class="text-xs text-slate-400 dark:text-slate-500">Agrega productos a la venta</p>
                            </div>
                        </template>

                        <div class="space-y-2">
                            <template x-for="(item, index) in productosVenta" :key="index">
                                <div class="p-3 bg-slate-50 dark:bg-gray-700/50 rounded-lg border border-slate-200 dark:border-gray-600">
                                    <div class="flex items-start justify-between mb-2">
                                        <div class="flex-1 min-w-0 pr-2">
                                            <p class="font-semibold text-sm text-slate-900 dark:text-white truncate" x-text="item.nombre"></p>
                                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                                S/ <span x-text="Number(item.precio_unitario).toFixed(2)"></span> × <span x-text="item.cantidad"></span>
                                            </p>
                                        </div>
                                        <button type="button" 
                                                @click="eliminarProducto(index)"
                                                class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-2">
                                            <button type="button" 
                                                    @click="cambiarCantidad(index, -1)"
                                                    class="p-1 bg-slate-200 dark:bg-gray-600 hover:bg-slate-300 dark:hover:bg-gray-500 rounded">
                                                <svg class="w-4 h-4 text-slate-700 dark:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                                </svg>
                                            </button>
                                            <input type="number" 
                                                   x-model.number="item.cantidad"
                                                   @change="actualizarSubtotal(index)"
                                                   min="1"
                                                   class="w-14 text-center px-2 py-1 text-sm bg-white dark:bg-gray-600 border border-slate-300 dark:border-gray-500 rounded text-slate-900 dark:text-white">
                                            <button type="button" 
                                                    @click="cambiarCantidad(index, 1)"
                                                    class="p-1 bg-slate-200 dark:bg-gray-600 hover:bg-slate-300 dark:hover:bg-gray-500 rounded">
                                                <svg class="w-4 h-4 text-slate-700 dark:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                                </svg>
                                            </button>
                                        </div>
                                        <span class="text-sm font-bold text-slate-900 dark:text-white">
                                            S/ <span x-text="item.subtotal.toFixed(2)"></span>
                                        </span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Resumen y Totales -->
                    <template x-if="productosVenta.length > 0">
                        <div class="border-t border-gray-200 dark:border-gray-700 p-4 space-y-4">
                            <!-- Subtotal -->
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-slate-600 dark:text-slate-400">Subtotal:</span>
                                <span class="font-semibold text-slate-900 dark:text-white">
                                    S/ <span x-text="calcularSubtotal().toFixed(2)"></span>
                                </span>
                            </div>

                            <!-- Descuento -->
                            <div>
                                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">
                                    Descuento
                                </label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-sm">S/</span>
                                    <input type="number"
                                           x-model.number="descuento"
                                           @input="calcularTotal()"
                                           step="0.01"
                                           min="0"
                                           :max="calcularSubtotal()"
                                           placeholder="0.00"
                                           class="block w-full pl-9 pr-3 py-2 text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-slate-900 dark:text-white">
                                </div>
                            </div>

                            <!-- Observaciones -->
                            <div>
                                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">
                                    Observaciones
                                </label>
                                <textarea x-model="observaciones"
                                          rows="2"
                                          placeholder="Notas adicionales..."
                                          class="block w-full px-3 py-2 text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 resize-none"></textarea>
                            </div>

                            <!-- Total -->
                            <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                                <div class="flex items-center justify-between">
                                    <span class="text-lg font-bold text-slate-900 dark:text-white">TOTAL:</span>
                                    <span class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                        S/ <span x-text="calcularTotal().toFixed(2)"></span>
                                    </span>
                                </div>
                            </div>

                            <!-- Botón de Venta -->
                            <button type="submit"
                                    :disabled="productosVenta.length === 0 || loading || (metodoPago === 'efectivo' && vuelto < 0)"
                                    class="w-full py-3 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 disabled:from-gray-400 disabled:to-gray-500 text-white font-bold rounded-lg shadow-sm transition-all duration-200 disabled:cursor-not-allowed flex items-center justify-center">
                                <svg x-show="loading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span x-text="loading ? 'Procesando...' : '💳 Registrar Venta'"></span>
                            </button>
                        </div>
                    </template>
                </div>
            </div>

        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function ventaForm() {
    return {
        // Cliente y Pago
        clienteId: '',
        metodoPago: '',
        
        // Campos específicos de pago
        dineroRecibido: 0,
        vuelto: 0,
        bancoEmisor: '',
        bancoTransferencia: '',
        metodoOtros: '',
        
        // Búsqueda de productos
        busqueda: '',
        codigoBarras: '',
        resultados: [],
        
        // Carrito
        productosVenta: [],
        descuento: 0,
        observaciones: '',
        
        // Estado
        loading: false,

        init() {
            // Inicialización
            console.log('Formulario de venta inicializado');
        },

        resetCamposPago() {
            this.dineroRecibido = 0;
            this.vuelto = 0;
            this.bancoEmisor = '';
            this.bancoTransferencia = '';
            this.metodoOtros = '';
        },

        calcularVuelto() {
            const total = this.calcularTotal();
            this.vuelto = this.dineroRecibido - total;
        },

        async buscarProductos() {
            if (this.busqueda.length < 2) {
                this.resultados = [];
                return;
            }

            try {
                const response = await fetch(`/api/buscar-productos?termino=${encodeURIComponent(this.busqueda)}`);
                const data = await response.json();
                this.resultados = data;
            } catch (error) {
                console.error('Error al buscar productos:', error);
            }
        },

        async buscarPorCodigoBarras() {
            if (!this.codigoBarras) return;

            try {
                const response = await fetch(`/api/buscar-productos?codigo_barras=${encodeURIComponent(this.codigoBarras)}`);
                const data = await response.json();
                
                if (data.length > 0) {
                    this.seleccionarProducto(data[0]);
                    this.codigoBarras = '';
                } else {
                    alert('Producto no encontrado');
                }
            } catch (error) {
                console.error('Error:', error);
            }
        },

        seleccionarProducto(producto) {
            // Lógica de selección (mantiene tu código original)
            if (producto.requiere_receta) {
                alert('⚠️ Este producto requiere receta médica.');
            }

            const loteDisponible = producto.lotes?.find(l => l.stock_actual > 0);
            
            if (!loteDisponible) {
                alert('No hay stock disponible');
                return;
            }

            this.productosVenta.push({
                producto_id: producto.id,
                lote_id: loteDisponible.id,
                nombre: producto.nombre,
                lote: loteDisponible,
                cantidad: 1,
                precio_unitario: parseFloat(producto.precio_venta),
                subtotal: parseFloat(producto.precio_venta)
            });

            this.busqueda = '';
            this.resultados = [];
            this.calcularTotal();
        },

        cambiarCantidad(index, cambio) {
            const item = this.productosVenta[index];
            const nuevaCantidad = item.cantidad + cambio;
            
            if (nuevaCantidad < 1) return;
            
            item.cantidad = nuevaCantidad;
            this.actualizarSubtotal(index);
        },

        actualizarSubtotal(index) {
            const item = this.productosVenta[index];
            item.subtotal = item.cantidad * item.precio_unitario;
            this.calcularTotal();
        },

        eliminarProducto(index) {
            this.productosVenta.splice(index, 1);
            this.calcularTotal();
        },

        calcularSubtotal() {
            return this.productosVenta.reduce((sum, item) => sum + item.subtotal, 0);
        },

        calcularTotal() {
            const subtotal = this.calcularSubtotal();
            const total = Math.max(0, subtotal - this.descuento);
            
            // Recalcular vuelto si es efectivo
            if (this.metodoPago === 'efectivo') {
                this.calcularVuelto();
            }
            
            return total;
        },

        async submitForm() {
            // Validaciones
            if (this.productosVenta.length === 0) {
                alert('Debe agregar al menos un producto');
                return;
            }

            if (!this.metodoPago) {
                alert('Debe seleccionar un método de pago');
                return;
            }

            if (this.metodoPago === 'efectivo' && this.vuelto < 0) {
                alert('El dinero recibido es insuficiente');
                return;
            }

            this.loading = true;

            try {
                const formData = {
                    cliente_id: this.clienteId || null,
                    metodo_pago: this.metodoPago,
                    descuento: this.descuento,
                    observaciones: this.observaciones,
                    datos_pago: {
                        dinero_recibido: this.dineroRecibido,
                        vuelto: this.vuelto,
                        banco_emisor: this.bancoEmisor,
                        banco_transferencia: this.bancoTransferencia,
                        metodo_otros: this.metodoOtros
                    },
                    productos: this.productosVenta.map(item => ({
                        producto_id: item.producto_id,
                        lote_id: item.lote_id,
                        cantidad: item.cantidad,
                        precio_unitario: item.precio_unitario
                    }))
                };

                const response = await fetch('{{ route("ventas.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(formData)
                });

                const data = await response.json();

                if (response.ok) {
                    window.location.href = data.redirect || '{{ route("ventas.index") }}';
                } else {
                    alert(data.message || 'Error al procesar la venta');
                    this.loading = false;
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al procesar la venta');
                this.loading = false;
            }
        }
    }
}
</script>
@endpush