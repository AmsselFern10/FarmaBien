@extends('layouts.app')

@section('title', 'Dashboard de Inventario')

@section('header')
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Dashboard de Inventario
        </h2>
        <div class="flex space-x-2">
            @can('ajustar inventario')
            <a href="{{ route('inventario.ajustar') }}" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Ajustar Inventario
            </a>
            @endcan
            <a href="{{ route('inventario.lotes') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
                Ver Lotes
            </a>
            <a href="{{ route('inventario.alertas') }}" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                Alertas
            </a>
        </div>
    </div>
@endsection

@section('content')

<!-- Tarjetas de Valorización -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0 bg-blue-100 rounded-md p-3">
                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="ml-5 w-0 flex-1">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Valor Total Inventario</dt>
                    <dd class="text-2xl font-semibold text-gray-900">
                        S/ {{ number_format($valorizacion['valor_total'], 2) }}
                    </dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0 bg-green-100 rounded-md p-3">
                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
            </div>
            <div class="ml-5 w-0 flex-1">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Total Productos</dt>
                    <dd class="text-2xl font-semibold text-gray-900">{{ $valorizacion['total_productos'] }}</dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0 bg-purple-100 rounded-md p-3">
                <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
            </div>
            <div class="ml-5 w-0 flex-1">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Total Unidades</dt>
                    <dd class="text-2xl font-semibold text-gray-900">{{ number_format($valorizacion['total_unidades']) }}</dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0 bg-yellow-100 rounded-md p-3">
                <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
            </div>
            <div class="ml-5 w-0 flex-1">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Categorías</dt>
                    <dd class="text-2xl font-semibold text-gray-900">{{ count($resumenCategorias) }}</dd>
                </dl>
            </div>
        </div>
    </div>
</div>

<!-- Resumen por Categoría -->
<div class="bg-white overflow-hidden shadow-sm rounded-lg mb-6">
    <div class="p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Inventario por Categoría</h3>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Categoría</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Productos</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Stock Total</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Valor S/</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">% del Total</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($resumenCategorias as $categoria)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="h-8 w-8 flex items-center justify-center rounded-full bg-blue-100">
                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                    </svg>
                                </div>
                                <span class="ml-3 text-sm font-medium text-gray-900">{{ $categoria->categoria_nombre }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">
                            {{ $categoria->total_productos }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold text-gray-900">
                            {{ number_format($categoria->stock_total) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold text-green-600">
                            S/ {{ number_format($categoria->valor_total, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <div class="flex items-center justify-center">
                                <div class="w-full max-w-xs">
                                    <div class="bg-gray-200 rounded-full h-2">
                                        <div class="bg-blue-600 h-2 rounded-full" 
                                             style="width: {{ ($categoria->valor_total / $valorizacion['valor_total']) * 100 }}%"></div>
                                    </div>
                                </div>
                                <span class="ml-2 text-xs font-medium text-gray-700">
                                    {{ number_format(($categoria->valor_total / $valorizacion['valor_total']) * 100, 1) }}%
                                </span>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    
    <!-- Productos con Stock Bajo -->
    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Productos con Stock Bajo</h3>
                <span class="px-3 py-1 text-sm rounded-full bg-yellow-100 text-yellow-800 font-semibold">
                    {{ $productosStockBajo->count() }} productos
                </span>
            </div>
            
            @if($productosStockBajo->count() > 0)
            <div class="space-y-3 max-h-96 overflow-y-auto">
                @foreach($productosStockBajo as $producto)
                <div class="flex justify-between items-center border-b pb-2">
                    <div class="flex-1">
                        <p class="font-medium text-gray-900">{{ $producto->nombre }}</p>
                        <p class="text-xs text-gray-500">{{ $producto->categoria->nombre }}</p>
                    </div>
                    <div class="text-right ml-4">
                        <p class="text-sm">
                            <span class="font-bold text-red-600">{{ $producto->stock_total }}</span>
                            <span class="text-gray-500">/ {{ $producto->stock_minimo }}</span>
                        </p>
                        <a href="{{ route('inventario.kardex-producto', $producto) }}" 
                           class="text-xs text-blue-600 hover:text-blue-800">
                            Ver kardex →
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-gray-500 text-center py-4">✓ Todos los productos tienen stock adecuado</p>
            @endif
        </div>
    </div>

    <!-- Lotes Próximos a Vencer -->
    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Lotes Próximos a Vencer (30 días)</h3>
                <span class="px-3 py-1 text-sm rounded-full bg-orange-100 text-orange-800 font-semibold">
                    {{ $lotesProximosVencer->count() }} lotes
                </span>
            </div>
            
            @if($lotesProximosVencer->count() > 0)
            <div class="space-y-3 max-h-96 overflow-y-auto">
                @foreach($lotesProximosVencer as $lote)
                <div class="flex justify-between items-center border-b pb-2">
                    <div class="flex-1">
                        <p class="font-medium text-gray-900">{{ $lote->producto->nombre }}</p>
                        <p class="text-xs text-gray-500">Lote: {{ $lote->numero_lote }}</p>
                    </div>
                    <div class="text-right ml-4">
                        <p class="text-sm font-semibold text-orange-600">
                            {{ $lote->fecha_vencimiento->format('d/m/Y') }}
                        </p>
                        <p class="text-xs text-gray-500">
                            {{ $lote->fecha_vencimiento->diffForHumans() }}
                        </p>
                        <p class="text-xs text-gray-600">Stock: {{ $lote->cantidad_actual }}</p>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-gray-500 text-center py-4">✓ No hay lotes próximos a vencer</p>
            @endif
        </div>
    </div>
</div>

<!-- Lotes Vencidos -->
@if($lotesVencidos->count() > 0)
<div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
    <div class="flex">
        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
        </svg>
        <div class="ml-3 flex-1">
            <h3 class="text-sm font-medium text-red-800">
                Atención: {{ $lotesVencidos->count() }} lote(s) vencido(s)
            </h3>
            <div class="mt-2 text-sm text-red-700">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach($lotesVencidos->take(5) as $lote)
                    <li>
                        <strong>{{ $lote->producto->nombre }}</strong> - Lote: {{ $lote->numero_lote }} 
                        (Vencido: {{ $lote->fecha_vencimiento->format('d/m/Y') }}) - 
                        Stock: {{ $lote->cantidad_actual }}
                    </li>
                    @endforeach
                    @if($lotesVencidos->count() > 5)
                    <li class="text-xs">... y {{ $lotesVencidos->count() - 5 }} más</li>
                    @endif
                </ul>
            </div>
            <div class="mt-4">
                <a href="{{ route('inventario.alertas') }}" 
                   class="text-sm font-medium text-red-800 hover:text-red-600">
                    Ver todas las alertas →
                </a>
            </div>
        </div>
    </div>
</div>
@endif

@endsection