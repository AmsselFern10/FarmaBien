@extends('layouts.app')

@section('title', 'Reporte de Compras')

@section('header')
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Reporte de Compras</h2>
        <div class="flex space-x-2">
            <button onclick="window.print()" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Imprimir
            </button>
            <a href="{{ route('reportes.index') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Volver
            </a>
        </div>
    </div>
@endsection

@section('content')

<!-- Filtros -->
<div class="bg-white shadow-sm rounded-lg mb-6 p-6">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Inicio</label>
            <input type="date" name="fecha_inicio" value="{{ $fechaInicio }}" class="w-full rounded-md border-gray-300">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Fin</label>
            <input type="date" name="fecha_fin" value="{{ $fechaFin }}" max="{{ date('Y-m-d') }}" class="w-full rounded-md border-gray-300">
        </div>
        <div class="flex items-end">
            <button type="submit" class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Generar
            </button>
        </div>
    </form>
</div>

<!-- Resumen -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div class="bg-white shadow-sm rounded-lg p-6">
        <p class="text-sm text-gray-500">Total Compras</p>
        <p class="text-2xl font-bold text-blue-600">S/ {{ number_format($totalCompras, 2) }}</p>
    </div>
    <div class="bg-white shadow-sm rounded-lg p-6">
        <p class="text-sm text-gray-500">Cantidad</p>
        <p class="text-2xl font-bold text-purple-600">{{ $cantidadCompras }}</p>
    </div>
    <div class="bg-white shadow-sm rounded-lg p-6">
        <p class="text-sm text-gray-500">Promedio</p>
        <p class="text-2xl font-bold text-green-600">S/ {{ number_format($promedioCompra, 2) }}</p>
    </div>
    <div class="bg-white shadow-sm rounded-lg p-6">
        <p class="text-sm text-gray-500">Productos Comprados</p>
        <p class="text-2xl font-bold text-orange-600">{{ $totalProductosComprados }}</p>
    </div>
</div>

<!-- Compras por Proveedor -->
<div class="bg-white shadow-sm rounded-lg mb-6 p-6">
    <h3 class="text-lg font-semibold mb-4">Compras por Proveedor</h3>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Proveedor</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Cantidad</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">%</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($comprasPorProveedor as $compraProveedor)
                <tr>
                    <td class="px-6 py-4 text-sm text-gray-900">{{ $compraProveedor['proveedor'] }}</td>
                    <td class="px-6 py-4 text-center text-sm">{{ $compraProveedor['cantidad'] }}</td>
                    <td class="px-6 py-4 text-right text-sm font-semibold">S/ {{ number_format($compraProveedor['total'], 2) }}</td>
                    <td class="px-6 py-4 text-center text-sm">{{ number_format(($compraProveedor['total'] / $totalCompras) * 100, 1) }}%</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Detalle -->
<div class="bg-white shadow-sm rounded-lg p-6">
    <h3 class="text-lg font-semibold mb-4">Detalle de Compras</h3>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Proveedor</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Comprobante</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($compras as $compra)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm">#{{ $compra->id }}</td>
                    <td class="px-4 py-3 text-sm">{{ $compra->fecha->format('d/m/Y') }}</td>
                    <td class="px-4 py-3 text-sm">{{ $compra->proveedor->nombre }}</td>
                    <td class="px-4 py-3 text-sm">{{ strtoupper($compra->tipo_comprobante) }} {{ $compra->numero_comprobante }}</td>
                    <td class="px-4 py-3 text-right text-sm font-semibold">S/ {{ number_format($compra->total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection