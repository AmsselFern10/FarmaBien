@extends('layouts.app')

@section('title', 'Detalle del Producto')

@section('header')
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Detalle del Producto
        </h2>
        <div class="flex space-x-2">
            @can('editar productos')
            <a href="{{ route('productos.edit', $producto) }}" 
               class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Editar
            </a>
            @endcan
            <a href="{{ route('productos.index') }}" 
               class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Volver
            </a>
        </div>
    </div>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <!-- Columna Izquierda: Imagen y Datos Básicos -->
    <div class="lg:col-span-1 space-y-6">
        
        <!-- Imagen del Producto -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
            <div class="bg-gray-100 rounded-lg overflow-hidden flex items-center justify-center" style="height: 300px;">
                @if($producto->imagen)
                    <img src="{{ asset('storage/' . $producto->imagen) }}" 
                         alt="{{ $producto->nombre }}"
                         class="w-full h-full object-contain">
                @else
                    <svg class="w-32 h-32 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                @endif
            </div>
        </div>

        <!-- Estado y Badges -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
            <h3 class="font-semibold text-gray-700 mb-4">Estado</h3>
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Estado:</span>
                    @if($producto->activo)
                        <span class="px-3 py-1 text-sm rounded-full bg-green-100 text-green-800 font-semibold">Activo</span>
                    @else
                        <span class="px-3 py-1 text-sm rounded-full bg-red-100 text-red-800 font-semibold">Inactivo</span>
                    @endif
                </div>
                
                @if($producto->requiere_receta)
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Receta:</span>
                    <span class="px-3 py-1 text-sm rounded-full bg-orange-100 text-orange-800 font-semibold">Requerida</span>
                </div>
                @endif

                <!-- Stock Alert -->
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Stock:</span>
                    @if($producto->stock_total <= 0)
                        <span class="px-3 py-1 text-sm rounded-full bg-red-100 text-red-800 font-semibold">Agotado</span>
                    @elseif($producto->stock_total <= $producto->stock_minimo)
                        <span class="px-3 py-1 text-sm rounded-full bg-yellow-100 text-yellow-800 font-semibold">Bajo</span>
                    @else
                        <span class="px-3 py-1 text-sm rounded-full bg-green-100 text-green-800 font-semibold">Normal</span>
                    @endif
                </div>
            </div>
        </div>

    </div>

    <!-- Columna Derecha: Información Detallada -->
    <div class="lg:col-span-2 space-y-6">
        
        <!-- Información General -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
            <h3 class="font-semibold text-lg text-gray-800 mb-4">Información General</h3>
            
            <div class="space-y-3">
                <div class="grid grid-cols-3 border-b pb-3">
                    <span class="text-sm font-medium text-gray-600">Nombre:</span>
                    <span class="col-span-2 text-sm text-gray-900">{{ $producto->nombre }}</span>
                </div>

                @if($producto->descripcion)
                <div class="grid grid-cols-3 border-b pb-3">
                    <span class="text-sm font-medium text-gray-600">Descripción:</span>
                    <span class="col-span-2 text-sm text-gray-900">{{ $producto->descripcion }}</span>
                </div>
                @endif

                <div class="grid grid-cols-3 border-b pb-3">
                    <span class="text-sm font-medium text-gray-600">Categoría:</span>
                    <span class="col-span-2 text-sm text-gray-900">
                        <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-800 text-xs font-semibold">
                            {{ $producto->categoria->nombre }}
                        </span>
                    </span>
                </div>

                @if($producto->codigo_barra)
                <div class="grid grid-cols-3 border-b pb-3">
                    <span class="text-sm font-medium text-gray-600">Código de Barra:</span>
                    <span class="col-span-2 text-sm text-gray-900 font-mono">{{ $producto->codigo_barra }}</span>
                </div>
                @endif

                @if($producto->ubicacion)
                <div class="grid grid-cols-3 border-b pb-3">
                    <span class="text-sm font-medium text-gray-600">Ubicación:</span>
                    <span class="col-span-2 text-sm text-gray-900">{{ $producto->ubicacion }}</span>
                </div>
                @endif
            </div>
        </div>

        <!-- Precios -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
            <h3 class="font-semibold text-lg text-gray-800 mb-4">Precios</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-blue-50 rounded-lg p-4">
                    <p class="text-xs text-gray-600 mb-1">Precio de Compra</p>
                    <p class="text-2xl font-bold text-blue-600">S/ {{ number_format($producto->precio_compra, 2) }}</p>
                </div>

                <div class="bg-green-50 rounded-lg p-4">
                    <p class="text-xs text-gray-600 mb-1">Precio de Venta</p>
                    <p class="text-2xl font-bold text-green-600">S/ {{ number_format($producto->precio_venta, 2) }}</p>
                </div>

                <div class="bg-purple-50 rounded-lg p-4">
                    <p class="text-xs text-gray-600 mb-1">Margen de Ganancia</p>
                    <p class="text-2xl font-bold text-purple-600">
                        {{ $producto->precio_compra > 0 ? number_format((($producto->precio_venta - $producto->precio_compra) / $producto->precio_compra) * 100, 1) : 0 }}%
                    </p>
                </div>
            </div>
        </div>

        <!-- Inventario -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
            <h3 class="font-semibold text-lg text-gray-800 mb-4">Inventario</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="border rounded-lg p-4">
                    <div class="flex justify-between items-start mb-2">
                        <span class="text-sm text-gray-600">Stock Total</span>
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                    <p class="text-3xl font-bold text-gray-900">{{ $producto->stock_total }}</p>
                    <p class="text-xs text-gray-500 mt-1">unidades</p>
                </div>

                <div class="border rounded-lg p-4">
                    <div class="flex justify-between items-start mb-2">
                        <span class="text-sm text-gray-600">Stock Disponible</span>
                        <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <p class="text-3xl font-bold text-green-600">{{ $producto->stock_disponible }}</p>
                    <p class="text-xs text-gray-500 mt-1">unidades</p>
                </div>

                <div class="border rounded-lg p-4">
                    <div class="flex justify-between items-start mb-2">
                        <span class="text-sm text-gray-600">Stock Reservado</span>
                        <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>
                    <p class="text-3xl font-bold text-yellow-600">{{ $producto->stock_reservado }}</p>
                    <p class="text-xs text-gray-500 mt-1">unidades</p>
                </div>

                <div class="border rounded-lg p-4">
                    <div class="flex justify-between items-start mb-2">
                        <span class="text-sm text-gray-600">Stock Mínimo</span>
                        <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <p class="text-3xl font-bold text-red-600">{{ $producto->stock_minimo }}</p>
                    <p class="text-xs text-gray-500 mt-1">unidades</p>
                </div>
            </div>

            @if($producto->stock_total <= $producto->stock_minimo && $producto->stock_total > 0)
            <div class="mt-4 bg-yellow-50 border-l-4 border-yellow-400 p-4">
                <div class="flex">
                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">
                            <strong>Alerta:</strong> El stock está por debajo del mínimo establecido. Se recomienda realizar un pedido.
                        </p>
                    </div>
                </div>
            </div>
            @elseif($producto->stock_total <= 0)
            <div class="mt-4 bg-red-50 border-l-4 border-red-400 p-4">
                <div class="flex">
                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <div class="ml-3">
                        <p class="text-sm text-red-700">
                            <strong>Producto Agotado:</strong> No hay stock disponible.
                        </p>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Metadatos -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
            <h3 class="font-semibold text-lg text-gray-800 mb-4">Información del Sistema</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="text-gray-600">Creado el:</span>
                    <span class="ml-2 text-gray-900">{{ $producto->created_at->format('d/m/Y H:i') }}</span>
                </div>
                <div>
                    <span class="text-gray-600">Última actualización:</span>
                    <span class="ml-2 text-gray-900">{{ $producto->updated_at->format('d/m/Y H:i') }}</span>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection