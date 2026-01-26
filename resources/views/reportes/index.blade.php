@extends('layouts.app')

@section('title', 'Reportes')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Centro de Reportes
    </h2>
@endsection

@section('content')

<!-- Introducción -->
<div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
    <div class="flex">
        <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
        </svg>
        <div class="ml-3">
            <p class="text-sm text-blue-700">
                Acceda a reportes detallados de ventas, compras e inventario. Analice tendencias y tome decisiones informadas.
            </p>
        </div>
    </div>
</div>

<!-- Categorías de Reportes -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    
    <!-- Reportes de Ventas -->
    @can('ver reportes ventas')
    <div class="bg-white overflow-hidden shadow-sm rounded-lg hover:shadow-lg transition-shadow">
        <div class="p-6 border-b-4 border-green-400">
            <div class="flex items-center mb-4">
                <div class="flex-shrink-0 bg-green-100 rounded-md p-3">
                    <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900">Reportes de Ventas</h3>
                    <p class="text-sm text-gray-500">Análisis de ingresos</p>
                </div>
            </div>
            
            <div class="space-y-2">
                <a href="{{ route('reportes.ventas') }}" 
                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded transition">
                    📊 Reporte General de Ventas
                </a>
                <a href="{{ route('reportes.productos-mas-vendidos') }}" 
                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded transition">
                    🏆 Productos Más Vendidos
                </a>
                <a href="{{ route('reportes.ventas', ['agrupar' => 'usuario']) }}" 
                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded transition">
                    👥 Ventas por Usuario
                </a>
                <a href="{{ route('reportes.ventas', ['agrupar' => 'metodo_pago']) }}" 
                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded transition">
                    💳 Ventas por Método de Pago
                </a>
            </div>
        </div>
    </div>
    @endcan

    <!-- Reportes de Compras -->
    @can('ver reportes compras')
    <div class="bg-white overflow-hidden shadow-sm rounded-lg hover:shadow-lg transition-shadow">
        <div class="p-6 border-b-4 border-blue-400">
            <div class="flex items-center mb-4">
                <div class="flex-shrink-0 bg-blue-100 rounded-md p-3">
                    <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900">Reportes de Compras</h3>
                    <p class="text-sm text-gray-500">Análisis de egresos</p>
                </div>
            </div>
            
            <div class="space-y-2">
                <a href="{{ route('reportes.compras') }}" 
                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded transition">
                    📊 Reporte General de Compras
                </a>
                <a href="{{ route('reportes.compras', ['agrupar' => 'proveedor']) }}" 
                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded transition">
                    🏢 Compras por Proveedor
                </a>
                <a href="{{ route('reportes.compras', ['agrupar' => 'categoria']) }}" 
                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded transition">
                    📦 Compras por Categoría
                </a>
            </div>
        </div>
    </div>
    @endcan

    <!-- Reportes de Inventario -->
    @can('ver reportes inventario')
    <div class="bg-white overflow-hidden shadow-sm rounded-lg hover:shadow-lg transition-shadow">
        <div class="p-6 border-b-4 border-purple-400">
            <div class="flex items-center mb-4">
                <div class="flex-shrink-0 bg-purple-100 rounded-md p-3">
                    <svg class="h-8 w-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900">Reportes de Inventario</h3>
                    <p class="text-sm text-gray-500">Control de stock</p>
                </div>
            </div>
            
            <div class="space-y-2">
                <a href="{{ route('reportes.inventario') }}" 
                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded transition">
                    📊 Reporte General de Inventario
                </a>
                <a href="{{ route('reportes.valorizacion') }}" 
                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded transition">
                    💰 Valorización del Inventario
                </a>
                <a href="{{ route('reportes.movimientos') }}" 
                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded transition">
                    📝 Movimientos de Inventario
                </a>
                <a href="{{ route('inventario.alertas') }}" 
                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded transition">
                    ⚠️ Alertas de Stock
                </a>
            </div>
        </div>
    </div>
    @endcan
</div>

<!-- Accesos Rápidos -->
<div class="bg-white overflow-hidden shadow-sm rounded-lg">
    <div class="p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Accesos Rápidos</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            @can('ver reportes ventas')
            <a href="{{ route('reportes.ventas', ['fecha_inicio' => now()->startOfMonth()->format('Y-m-d'), 'fecha_fin' => now()->format('Y-m-d')]) }}" 
               class="flex items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition">
                <svg class="w-10 h-10 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                <div>
                    <p class="text-xs text-gray-600">Ventas del</p>
                    <p class="font-semibold text-gray-900">Mes Actual</p>
                </div>
            </a>
            @endcan

            @can('ver reportes compras')
            <a href="{{ route('reportes.compras', ['fecha_inicio' => now()->startOfMonth()->format('Y-m-d'), 'fecha_fin' => now()->format('Y-m-d')]) }}" 
               class="flex items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition">
                <svg class="w-10 h-10 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
                <div>
                    <p class="text-xs text-gray-600">Compras del</p>
                    <p class="font-semibold text-gray-900">Mes Actual</p>
                </div>
            </a>
            @endcan

            @can('ver reportes inventario')
            <a href="{{ route('reportes.valorizacion') }}" 
               class="flex items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition">
                <svg class="w-10 h-10 text-purple-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div>
                    <p class="text-xs text-gray-600">Valorización</p>
                    <p class="font-semibold text-gray-900">Actual</p>
                </div>
            </a>
            @endcan

            @can('ver reportes ventas')
            <a href="{{ route('reportes.productos-mas-vendidos') }}" 
               class="flex items-center p-4 bg-yellow-50 rounded-lg hover:bg-yellow-100 transition">
                <svg class="w-10 h-10 text-yellow-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                </svg>
                <div>
                    <p class="text-xs text-gray-600">Productos</p>
                    <p class="font-semibold text-gray-900">Más Vendidos</p>
                </div>
            </a>
            @endcan
        </div>
    </div>
</div>

<!-- Información Adicional -->
<div class="mt-6 bg-white overflow-hidden shadow-sm rounded-lg">
    <div class="p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">💡 Consejos para Reportes</h3>
        
        <ul class="space-y-2 text-sm text-gray-600">
            <li class="flex items-start">
                <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                Utilice los filtros de fecha para analizar periodos específicos
            </li>
            <li class="flex items-start">
                <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                Compare diferentes periodos para identificar tendencias
            </li>
            <li class="flex items-start">
                <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                Use el botón de imprimir para guardar los reportes en PDF
            </li>
            <li class="flex items-start">
                <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                Revise regularmente los productos más vendidos para optimizar el inventario
            </li>
        </ul>
    </div>
</div>

@endsection