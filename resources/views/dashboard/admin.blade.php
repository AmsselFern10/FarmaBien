@extends('layouts.app')

@section('title', 'Dashboard Administrador')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 dark:text-white leading-tight">
        Dashboard de Administración
    </h2>
@endsection

@section('content')

<!-- Tarjetas de Resumen con Diseño Mejorado -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    
    <!-- Ventas del Día -->
    <div class="group bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-800/50 overflow-hidden shadow-card hover:shadow-card-hover rounded-xl transition-all duration-300 hover:-translate-y-1 border border-gray-100 dark:border-gray-700">
        <div class="p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-3 shadow-lg group-hover:scale-110 transition-transform duration-300">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Ventas Hoy</dt>
                        <dd class="flex items-baseline">
                            <div class="text-2xl font-bold text-gray-900 dark:text-white">
                                S/ {{ number_format($ventasHoy, 2) }}
                            </div>
                        </dd>
                        <dd class="flex items-center text-xs text-gray-500 dark:text-gray-400 mt-1">
                            <svg class="w-4 h-4 mr-1 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3.293 9.707a1 1 0 010-1.414l6-6a1 1 0 011.414 0l6 6a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L4.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            {{ $cantidadVentasHoy }} transacciones
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="bg-gray-50 dark:bg-gray-700/50 px-6 py-3 border-t border-gray-100 dark:border-gray-700">
            <a href="{{ route('ventas.index') }}" class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition-colors duration-200">
                Ver detalles →
            </a>
        </div>
    </div>

    <!-- Ventas del Mes -->
    <div class="group bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-800/50 overflow-hidden shadow-card hover:shadow-card-hover rounded-xl transition-all duration-300 hover:-translate-y-1 border border-gray-100 dark:border-gray-700">
        <div class="p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-gradient-to-br from-green-500 to-green-600 rounded-xl p-3 shadow-lg group-hover:scale-110 transition-transform duration-300">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Ventas del Mes</dt>
                        <dd class="text-2xl font-bold text-gray-900 dark:text-white">
                            S/ {{ number_format($ventasMes, 2) }}
                        </dd>
                        <dd class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            {{ date('F Y') }}
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="bg-gray-50 dark:bg-gray-700/50 px-6 py-3 border-t border-gray-100 dark:border-gray-700">
            <a href="{{ route('reportes.ventas') }}" class="text-sm font-medium text-green-600 dark:text-green-400 hover:text-green-700 dark:hover:text-green-300 transition-colors duration-200">
                Ver reporte →
            </a>
        </div>
    </div>

    <!-- Productos -->
    <div class="group bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-800/50 overflow-hidden shadow-card hover:shadow-card-hover rounded-xl transition-all duration-300 hover:-translate-y-1 border border-gray-100 dark:border-gray-700">
        <div class="p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl p-3 shadow-lg group-hover:scale-110 transition-transform duration-300">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Productos Activos</dt>
                        <dd class="text-2xl font-bold text-gray-900 dark:text-white">{{ $totalProductos }}</dd>
                        @if($productosBajoStock > 0)
                        <dd class="flex items-center text-xs text-red-600 dark:text-red-400 mt-1">
                            <span class="relative flex h-2 w-2 mr-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                            </span>
                            {{ $productosBajoStock }} con stock bajo
                        </dd>
                        @else
                        <dd class="text-xs text-green-600 dark:text-green-400 mt-1">✓ Stock adecuado</dd>
                        @endif
                    </dl>
                </div>
            </div>
        </div>
        <div class="bg-gray-50 dark:bg-gray-700/50 px-6 py-3 border-t border-gray-100 dark:border-gray-700">
            <a href="{{ route('productos.index') }}" class="text-sm font-medium text-purple-600 dark:text-purple-400 hover:text-purple-700 dark:hover:text-purple-300 transition-colors duration-200">
                Ver productos →
            </a>
        </div>
    </div>

    <!-- Clientes -->
    <div class="group bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-800/50 overflow-hidden shadow-card hover:shadow-card-hover rounded-xl transition-all duration-300 hover:-translate-y-1 border border-gray-100 dark:border-gray-700">
        <div class="p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-xl p-3 shadow-lg group-hover:scale-110 transition-transform duration-300">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Clientes</dt>
                        <dd class="text-2xl font-bold text-gray-900 dark:text-white">{{ $totalClientes }}</dd>
                        @if($clientesNuevosHoy > 0)
                        <dd class="text-xs text-green-600 dark:text-green-400 mt-1">+{{ $clientesNuevosHoy }} nuevos hoy</dd>
                        @endif
                    </dl>
                </div>
            </div>
        </div>
        <div class="bg-gray-50 dark:bg-gray-700/50 px-6 py-3 border-t border-gray-100 dark:border-gray-700">
            <a href="{{ route('clientes.index') }}" class="text-sm font-medium text-yellow-600 dark:text-yellow-400 hover:text-yellow-700 dark:hover:text-yellow-300 transition-colors duration-200">
                Ver clientes →
            </a>
        </div>
    </div>
</div>

<!-- Gráficos y Tablas -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    
    <!-- Productos Más Vendidos -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-card rounded-xl border border-gray-100 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-gray-50 to-white dark:from-gray-800 dark:to-gray-800/50">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center">
                <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
                Top 5 Productos Más Vendidos
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Este mes</p>
        </div>
        <div class="p-6">
            @if($productosMasVendidos->count() > 0)
            <div class="space-y-4">
                @foreach($productosMasVendidos as $index => $producto)
                <div class="group flex justify-between items-center p-4 bg-gray-50 dark:bg-gray-700/50 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-xl transition-all duration-200 border border-transparent hover:border-blue-200 dark:hover:border-blue-800">
                    <div class="flex items-center space-x-4 flex-1">
                        <div class="flex-shrink-0">
                            <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-blue-600 text-white font-bold text-sm shadow-md">
                                {{ $index + 1 }}
                            </span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-gray-900 dark:text-white truncate">{{ $producto->nombre }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 flex items-center mt-1">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                                {{ $producto->total_vendido }} unidades
                            </p>
                        </div>
                    </div>
                    <div class="text-right ml-4">
                        <p class="font-bold text-green-600 dark:text-green-400">S/ {{ number_format($producto->total_ingresos, 2) }}</p>
                        <div class="mt-1 bg-green-100 dark:bg-green-900/30 rounded-full h-2 w-24">
                            <div class="bg-gradient-to-r from-green-500 to-green-600 h-2 rounded-full transition-all duration-500" 
                                 style="width: {{ ($producto->total_ingresos / $productosMasVendidos->max('total_ingresos')) * 100 }}%"></div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <p class="text-gray-500 dark:text-gray-400 mt-3">No hay datos disponibles</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Productos con Stock Bajo -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-card rounded-xl border border-gray-100 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-gray-50 to-white dark:from-gray-800 dark:to-gray-800/50">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center">
                <svg class="w-5 h-5 mr-2 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                Alertas de Stock
            </h3>
        </div>
        <div class="p-6">
            @if($productosAlerta->count() > 0)
            <div class="space-y-3">
                @foreach($productosAlerta as $producto)
                <div class="flex justify-between items-center p-4 bg-gradient-to-r from-red-50 to-orange-50 dark:from-red-900/20 dark:to-orange-900/20 rounded-xl border border-red-100 dark:border-red-800 hover:shadow-md transition-all duration-200">
                    <div class="flex-1">
                        <p class="font-medium text-gray-900 dark:text-white">{{ $producto->nombre }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 flex items-center">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd"></path>
                            </svg>
                            Mínimo: {{ $producto->stock_minimo }} unidades
                        </p>
                    </div>
                    <div class="text-right ml-4">
                        @if($producto->stock_total == 0)
                            <span class="px-3 py-1.5 text-xs rounded-full bg-red-500 text-white font-semibold shadow-md animate-pulse">
                                Agotado
                            </span>
                        @else
                            <span class="px-3 py-1.5 text-xs rounded-full bg-yellow-500 text-white font-semibold shadow-md">
                                {{ $producto->stock_total }} unid.
                            </span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            <a href="{{ route('productos.index') }}" class="block mt-4 text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 text-center font-medium transition-colors duration-200">
                Ver todos los productos →
            </a>
            @else
            <div class="text-center py-12">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 dark:bg-green-900/30">
                    <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <p class="text-gray-500 dark:text-gray-400 mt-3">✓ Todos los productos tienen stock adecuado</p>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Ventas por Día (Últimos 7 días) -->
<div class="bg-white dark:bg-gray-800 overflow-hidden shadow-card rounded-xl mb-6 border border-gray-100 dark:border-gray-700">
    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-gray-50 to-white dark:from-gray-800 dark:to-gray-800/50">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center">
            <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
            </svg>
            Ventas de los Últimos 7 Días
        </h3>
    </div>
    <div class="p-6">
        @if($ventasPorDia->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Fecha</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Transacciones</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Vendido</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($ventasPorDia as $venta)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 font-medium">
                            {{ \Carbon\Carbon::parse($venta->dia)->format('d/m/Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300">
                                {{ $venta->cantidad }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 dark:text-white text-right">
                            S/ {{ number_format($venta->total, 2) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
            <p class="text-gray-500 dark:text-gray-400 mt-3">No hay ventas registradas en los últimos 7 días</p>
        </div>
        @endif
    </div>
</div>

<!-- Resumen Financiero -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    
    <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 overflow-hidden shadow-card rounded-xl text-white">
        <div class="p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-medium text-indigo-100 mb-2">Compras Hoy</h3>
                    <p class="text-3xl font-bold">S/ {{ number_format($comprasHoy, 2) }}</p>
                </div>
                <div class="bg-indigo-400/30 rounded-full p-3">
                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-gradient-to-br from-purple-500 to-purple-600 overflow-hidden shadow-card rounded-xl text-white">
        <div class="p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-medium text-purple-100 mb-2">Compras del Mes</h3>
                    <p class="text-3xl font-bold">S/ {{ number_format($comprasMes, 2) }}</p>
                </div>
                <div class="bg-purple-400/30 rounded-full p-3">
                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-gradient-to-br {{ ($ventasMes - $comprasMes) >= 0 ? 'from-green-500 to-green-600' : 'from-red-500 to-red-600' }} overflow-hidden shadow-card rounded-xl text-white">
        <div class="p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-medium {{ ($ventasMes - $comprasMes) >= 0 ? 'text-green-100' : 'text-red-100' }} mb-2">Balance del Mes</h3>
                    <p class="text-3xl font-bold">
                        S/ {{ number_format($ventasMes - $comprasMes, 2) }}
                    </p>
                </div>
                <div class="{{ ($ventasMes - $comprasMes) >= 0 ? 'bg-green-400/30' : 'bg-red-400/30' }} rounded-full p-3">
                    @if(($ventasMes - $comprasMes) >= 0)
                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd"></path>
                    </svg>
                    @else
                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M12 13a1 1 0 100 2h5a1 1 0 001-1V9a1 1 0 10-2 0v2.586l-4.293-4.293a1 1 0 00-1.414 0L8 9.586 3.707 5.293a1 1 0 00-1.414 1.414l5 5a1 1 0 001.414 0L11 9.414 14.586 13H12z" clip-rule="evenodd"></path>
                    </svg>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection