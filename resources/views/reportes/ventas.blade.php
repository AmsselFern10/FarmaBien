@extends('layouts.app')

@section('title', 'Reporte de Ventas')

@section('header')
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Reporte de Ventas
        </h2>
        <div class="flex space-x-2">
            <button onclick="window.print()" 
                    class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                Imprimir
            </button>
            <a href="{{ route('reportes.index') }}" 
               class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Volver
            </a>
        </div>
    </div>
@endsection

@section('content')

<!-- Filtros -->
<div class="bg-white overflow-hidden shadow-sm rounded-lg mb-6">
    <div class="p-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Inicio</label>
                <input type="date" 
                       name="fecha_inicio"
                       value="{{ $fechaInicio }}"
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Fin</label>
                <input type="date" 
                       name="fecha_fin"
                       value="{{ $fechaFin }}"
                       max="{{ date('Y-m-d') }}"
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div class="flex items-end space-x-2">
                <button type="submit" class="flex-1 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Generar Reporte
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Resumen Ejecutivo -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0 bg-green-100 rounded-md p-3">
                <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="ml-5 w-0 flex-1">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Total Ventas</dt>
                    <dd class="text-2xl font-bold text-green-600">S/ {{ number_format($totalVentas, 2) }}</dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0 bg-blue-100 rounded-md p-3">
                <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
            <div class="ml-5 w-0 flex-1">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Cantidad de Ventas</dt>
                    <dd class="text-2xl font-bold text-blue-600">{{ $cantidadVentas }}</dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0 bg-purple-100 rounded-md p-3">
                <svg class="h-8 w-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
            </div>
            <div class="ml-5 w-0 flex-1">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Promedio por Venta</dt>
                    <dd class="text-2xl font-bold text-purple-600">S/ {{ number_format($promedioVenta, 2) }}</dd>
                </dl>
            </div>
        </div>
    </div>
</div>

<!-- Ventas por Día -->
<div class="bg-white overflow-hidden shadow-sm rounded-lg mb-6">
    <div class="p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Ventas por Día</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Cantidad</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">% del Total</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($ventasPorDia as $ventaDia)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $ventaDia['fecha'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900">{{ $ventaDia['cantidad'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold text-gray-900">
                            S/ {{ number_format($ventaDia['total'], 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <div class="flex items-center justify-center">
                                <div class="w-full max-w-xs">
                                    <div class="bg-gray-200 rounded-full h-2">
                                        <div class="bg-green-600 h-2 rounded-full" 
                                             style="width: {{ ($ventaDia['total'] / $totalVentas) * 100 }}%"></div>
                                    </div>
                                </div>
                                <span class="ml-2 text-xs text-gray-600">
                                    {{ number_format(($ventaDia['total'] / $totalVentas) * 100, 1) }}%
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

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    
    <!-- Ventas por Usuario -->
    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Ventas por Usuario</h3>
            <div class="space-y-3">
                @foreach($ventasPorUsuario as $ventaUsuario)
                <div class="flex justify-between items-center border-b pb-2">
                    <div class="flex-1">
                        <p class="font-medium text-gray-900">{{ $ventaUsuario['usuario'] }}</p>
                        <p class="text-xs text-gray-500">{{ $ventaUsuario['cantidad'] }} ventas</p>
                    </div>
                    <div class="text-right">
                        <p class="font-semibold text-green-600">S/ {{ number_format($ventaUsuario['total'], 2) }}</p>
                        <p class="text-xs text-gray-500">
                            {{ number_format(($ventaUsuario['total'] / $totalVentas) * 100, 1) }}%
                        </p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Ventas por Método de Pago -->
    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Ventas por Método de Pago</h3>
            <div class="space-y-3">
                @foreach($ventasPorMetodoPago as $ventaMetodo)
                <div class="flex justify-between items-center border-b pb-2">
                    <div class="flex-1">
                        <p class="font-medium text-gray-900">{{ ucfirst($ventaMetodo['metodo']) }}</p>
                        <p class="text-xs text-gray-500">{{ $ventaMetodo['cantidad'] }} transacciones</p>
                    </div>
                    <div class="text-right">
                        <p class="font-semibold text-green-600">S/ {{ number_format($ventaMetodo['total'], 2) }}</p>
                        <p class="text-xs text-gray-500">
                            {{ number_format(($ventaMetodo['total'] / $totalVentas) * 100, 1) }}%
                        </p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Detalle de Ventas -->
<div class="bg-white overflow-hidden shadow-sm rounded-lg mt-6">
    <div class="p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Detalle de Ventas</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Usuario</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Método</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($ventas as $venta)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm text-gray-900">#{{ $venta->id }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $venta->fecha->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">
                            {{ $venta->cliente ? $venta->cliente->nombre : 'Cliente General' }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $venta->usuario->name }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-1 text-xs rounded-full 
                                {{ $venta->metodo_pago == 'efectivo' ? 'bg-green-100 text-green-800' : 
                                   ($venta->metodo_pago == 'tarjeta' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800') }}">
                                {{ ucfirst($venta->metodo_pago) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right text-sm font-semibold text-gray-900">
                            S/ {{ number_format($venta->total, 2) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-100">
                    <tr>
                        <td colspan="5" class="px-4 py-3 text-right font-bold text-gray-700">Total:</td>
                        <td class="px-4 py-3 text-right font-bold text-green-600">
                            S/ {{ number_format($totalVentas, 2) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

@endsection