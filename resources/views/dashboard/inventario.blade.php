@extends('layouts.app')

@section('title', 'Dashboard Inventario')

@section('header')
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Dashboard Inventario
        </h2>
        <a href="{{ route('compras.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Nueva Compra
        </a>
    </div>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Estadísticas Principales -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Compras del Mes -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">
                                Compras del Mes
                            </dt>
                            <dd class="text-2xl font-semibold text-gray-900">
                                S/ {{ number_format($comprasMes, 2) }}
                            </dd>
                            <dd class="text-sm text-gray-500">
                                {{ $cantidadComprasMes }} compras
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-3">
                <a href="{{ route('compras.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                    Ver compras →
                </a>
            </div>
        </div>

        <!-- Valorización Total -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">
                                Valor Inventario
                            </dt>
                            <dd class="text-2xl font-semibold text-gray-900">
                                S/ {{ number_format($valorizacion['valor_total'], 2) }}
                            </dd>
                            <dd class="text-sm text-gray-500">
                                {{ number_format($valorizacion['cantidad_total_unidades']) }} unidades
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-3">
                <a href="{{ route('inventario.valorizacion') }}" class="text-sm font-medium text-green-600 hover:text-green-500">
                    Ver detalle →
                </a>
            </div>
        </div>

        <!-- Stock Bajo -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">
                                Stock Bajo
                            </dt>
                            <dd class="text-2xl font-semibold text-gray-900">
                                {{ $productosStockBajo->count() }}
                            </dd>
                            <dd class="text-sm text-gray-500">
                                productos
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-3">
                <a href="{{ route('inventario.alertas') }}" class="text-sm font-medium text-yellow-600 hover:text-yellow-500">
                    Ver alertas →
                </a>
            </div>
        </div>

        <!-- Lotes Activos -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">
                                Lotes Activos
                            </dt>
                            <dd class="text-2xl font-semibold text-gray-900">
                                {{ $valorizacion['total_lotes_activos'] }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-3">
                <a href="{{ route('inventario.lotes') }}" class="text-sm font-medium text-purple-600 hover:text-purple-500">
                    Ver lotes →
                </a>
            </div>
        </div>
    </div>

    <!-- Alertas Críticas -->
    @if($lotesVencidos->isNotEmpty() || $lotesProximosVencer->isNotEmpty() || $productosStockBajo->isNotEmpty())
    <div class="bg-white shadow-sm rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">🚨 Alertas Críticas</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Lotes Vencidos -->
            @if($lotesVencidos->isNotEmpty())
            <div class="border-l-4 border-red-500 bg-red-50 p-4 rounded">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3 flex-1">
                        <h4 class="text-sm font-medium text-red-800">Lotes Vencidos</h4>
                        <p class="mt-1 text-sm text-red-700">
                            {{ $lotesVencidos->count() }} lotes vencidos con stock
                        </p>
                        <a href="{{ route('inventario.alertas') }}" class="mt-2 inline-flex text-sm font-medium text-red-700 hover:text-red-600">
                            Ver detalles →
                        </a>
                    </div>
                </div>
            </div>
            @endif

            <!-- Próximos a Vencer -->
            @if($lotesProximosVencer->isNotEmpty())
            <div class="border-l-4 border-orange-500 bg-orange-50 p-4 rounded">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-orange-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3 flex-1">
                        <h4 class="text-sm font-medium text-orange-800">Próximos a Vencer</h4>
                        <p class="mt-1 text-sm text-orange-700">
                            {{ $lotesProximosVencer->count() }} lotes (30 días)
                        </p>
                        <a href="{{ route('inventario.alertas') }}" class="mt-2 inline-flex text-sm font-medium text-orange-700 hover:text-orange-600">
                            Ver detalles →
                        </a>
                    </div>
                </div>
            </div>
            @endif

            <!-- Stock Bajo -->
            @if($productosStockBajo->isNotEmpty())
            <div class="border-l-4 border-yellow-500 bg-yellow-50 p-4 rounded">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3 flex-1">
                        <h4 class="text-sm font-medium text-yellow-800">Stock Bajo</h4>
                        <p class="mt-1 text-sm text-yellow-700">
                            {{ $productosStockBajo->count() }} productos
                        </p>
                        <a href="{{ route('inventario.alertas') }}" class="mt-2 inline-flex text-sm font-medium text-yellow-700 hover:text-yellow-600">
                            Ver detalles →
                        </a>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Dos Columnas -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Resumen por Categoría -->
        <div class="bg-white shadow-sm rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Inventario por Categoría</h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @foreach($resumenCategorias as $categoria)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $categoria->categoria }}</p>
                            <p class="text-xs text-gray-500">{{ $categoria->total_productos }} productos</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold text-gray-900">
                                {{ number_format($categoria->stock_total) }} unidades
                            </p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Compras Recientes -->
        <div class="bg-white shadow-sm rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Compras Recientes</h3>
            </div>
            <div class="p-6">
                <div class="flow-root">
                    <ul class="-my-5 divide-y divide-gray-200">
                        @forelse($comprasRecientes as $compra)
                        <li class="py-4">
                            <div class="flex items-center space-x-4">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900">
                                        Compra #{{ $compra->id }}
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        {{ $compra->proveedor->nombre }}
                                    </p>
                                    <p class="text-xs text-gray-400">
                                        {{ $compra->fecha->format('d/m/Y H:i') }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-semibold text-gray-900">
                                        S/ {{ number_format($compra->total, 2) }}
                                    </p>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        {{ $compra->estado }}
                                    </span>
                                </div>
                            </div>
                        </li>
                        @empty
                        <li class="py-4 text-center text-gray-500">
                            No hay compras recientes
                        </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Movimientos Recientes -->
    <div class="bg-white shadow-sm rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Movimientos Recientes de Inventario</h3>
        </div>
        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Producto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lote</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cantidad</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Usuario</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($movimientosRecientes as $movimiento)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $movimiento->producto->nombre }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $movimiento->lote->numero_lote }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($movimiento->tipo === 'entrada')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Entrada
                                    </span>
                                @elseif($movimiento->tipo === 'salida')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        Salida
                                    </span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        Ajuste
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm {{ $movimiento->cantidad < 0 ? 'text-red-600' : 'text-green-600' }} font-semibold">
                                {{ $movimiento->cantidad > 0 ? '+' : '' }}{{ $movimiento->cantidad }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $movimiento->usuario->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $movimiento->fecha_movimiento->format('d/m/Y H:i') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection