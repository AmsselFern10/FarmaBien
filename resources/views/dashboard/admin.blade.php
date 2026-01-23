@extends('layouts.app')

@section('title', 'Dashboard Administrador')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Dashboard de Administración
    </h2>
@endsection

@section('content')

<!-- Tarjetas de Resumen -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    
    <!-- Ventas del Día -->
    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
        <div class="p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-blue-100 rounded-md p-3">
                    <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Ventas Hoy</dt>
                        <dd class="flex items-baseline">
                            <div class="text-2xl font-semibold text-gray-900">
                                S/ {{ number_format($ventasHoy, 2) }}
                            </div>
                        </dd>
                        <dd class="text-xs text-gray-500">{{ $cantidadVentasHoy }} transacciones</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Ventas del Mes -->
    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
        <div class="p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-green-100 rounded-md p-3">
                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Ventas del Mes</dt>
                        <dd class="text-2xl font-semibold text-gray-900">
                            S/ {{ number_format($ventasMes, 2) }}
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Productos -->
    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
        <div class="p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-purple-100 rounded-md p-3">
                    <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Productos Activos</dt>
                        <dd class="text-2xl font-semibold text-gray-900">{{ $totalProductos }}</dd>
                        @if($productosBajoStock > 0)
                        <dd class="text-xs text-red-600">{{ $productosBajoStock }} con stock bajo</dd>
                        @endif
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Clientes -->
    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
        <div class="p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-yellow-100 rounded-md p-3">
                    <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Total Clientes</dt>
                        <dd class="text-2xl font-semibold text-gray-900">{{ $totalClientes }}</dd>
                        @if($clientesNuevosHoy > 0)
                        <dd class="text-xs text-green-600">+{{ $clientesNuevosHoy }} hoy</dd>
                        @endif
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Gráficos y Tablas -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    
    <!-- Productos Más Vendidos -->
    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Top 5 Productos Más Vendidos (Este Mes)</h3>
            
            @if($productosMasVendidos->count() > 0)
            <div class="space-y-3">
                @foreach($productosMasVendidos as $producto)
                <div class="flex justify-between items-center border-b pb-2">
                    <div class="flex-1">
                        <p class="font-medium text-gray-900">{{ $producto->nombre }}</p>
                        <p class="text-xs text-gray-500">{{ $producto->total_vendido }} unidades</p>
                    </div>
                    <div class="text-right">
                        <p class="font-semibold text-green-600">S/ {{ number_format($producto->total_ingresos, 2) }}</p>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-gray-500 text-center py-4">No hay datos disponibles</p>
            @endif
        </div>
    </div>

    <!-- Productos con Stock Bajo -->
    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Alertas de Stock</h3>
            
            @if($productosAlerta->count() > 0)
            <div class="space-y-3">
                @foreach($productosAlerta as $producto)
                <div class="flex justify-between items-center border-b pb-2">
                    <div class="flex-1">
                        <p class="font-medium text-gray-900">{{ $producto->nombre }}</p>
                        <p class="text-xs text-gray-500">Mínimo: {{ $producto->stock_minimo }}</p>
                    </div>
                    <div class="text-right">
                        @if($producto->stock_total == 0)
                            <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800 font-semibold">Agotado</span>
                        @else
                            <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800 font-semibold">{{ $producto->stock_total }} unid.</span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            <a href="{{ route('productos.index') }}" class="block mt-4 text-sm text-blue-600 hover:text-blue-800 text-center">
                Ver todos los productos →
            </a>
            @else
            <p class="text-gray-500 text-center py-4">✓ Todos los productos tienen stock adecuado</p>
            @endif
        </div>
    </div>
</div>

<!-- Ventas por Día (Últimos 7 días) -->
<div class="bg-white overflow-hidden shadow-sm rounded-lg mb-6">
    <div class="p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Ventas de los Últimos 7 Días</h3>
        
        @if($ventasPorDia->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Cantidad</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($ventasPorDia as $venta)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ \Carbon\Carbon::parse($venta->dia)->format('d/m/Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                            {{ $venta->cantidad }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 text-right">
                            S/ {{ number_format($venta->total, 2) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="text-gray-500 text-center py-4">No hay ventas registradas en los últimos 7 días</p>
        @endif
    </div>
</div>

<!-- Resumen Financiero -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    
    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
        <div class="p-6">
            <h3 class="text-sm font-medium text-gray-500 mb-2">Compras Hoy</h3>
            <p class="text-3xl font-bold text-gray-900">S/ {{ number_format($comprasHoy, 2) }}</p>
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
        <div class="p-6">
            <h3 class="text-sm font-medium text-gray-500 mb-2">Compras del Mes</h3>
            <p class="text-3xl font-bold text-gray-900">S/ {{ number_format($comprasMes, 2) }}</p>
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
        <div class="p-6">
            <h3 class="text-sm font-medium text-gray-500 mb-2">Balance del Mes</h3>
            <p class="text-3xl font-bold {{ ($ventasMes - $comprasMes) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                S/ {{ number_format($ventasMes - $comprasMes, 2) }}
            </p>
        </div>
    </div>
</div>

@endsection