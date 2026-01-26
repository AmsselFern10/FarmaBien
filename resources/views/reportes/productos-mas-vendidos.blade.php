@extends('layouts.app')

@section('title', 'Productos Más Vendidos')

@section('header')
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Top Productos Más Vendidos</h2>
        <a href="{{ route('reportes.index') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Volver</a>
    </div>
@endsection

@section('content')

<!-- Filtros -->
<div class="bg-white shadow-sm rounded-lg mb-6 p-6">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Inicio</label>
            <input type="date" name="fecha_inicio" value="{{ $fechaInicio }}" class="w-full rounded-md border-gray-300">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Fin</label>
            <input type="date" name="fecha_fin" value="{{ $fechaFin }}" class="w-full rounded-md border-gray-300">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Límite</label>
            <select name="limite" class="w-full rounded-md border-gray-300">
                <option value="10" {{ $limite == 10 ? 'selected' : '' }}>Top 10</option>
                <option value="20" {{ $limite == 20 ? 'selected' : '' }}>Top 20</option>
                <option value="50" {{ $limite == 50 ? 'selected' : '' }}>Top 50</option>
            </select>
        </div>
        <div class="flex items-end">
            <button type="submit" class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Generar</button>
        </div>
    </form>
</div>

<!-- Ranking de Productos -->
<div class="bg-white shadow-sm rounded-lg p-6">
    <h3 class="text-lg font-semibold mb-4">🏆 Ranking de Productos</h3>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Posición</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Producto</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Categoría</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Cantidad Vendida</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Ingresos</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">N° Ventas</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($productos as $index => $producto)
                <tr class="hover:bg-gray-50 {{ $index < 3 ? 'bg-yellow-50' : '' }}">
                    <td class="px-6 py-4 text-center">
                        @if($index == 0)
                            <span class="text-3xl">🥇</span>
                        @elseif($index == 1)
                            <span class="text-3xl">🥈</span>
                        @elseif($index == 2)
                            <span class="text-3xl">🥉</span>
                        @else
                            <span class="text-lg font-semibold text-gray-600">{{ $index + 1 }}</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-gray-900">{{ $producto->nombre }}</div>
                        <div class="text-xs text-gray-500">ID: {{ $producto->id }}</div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $producto->categoria }}</td>
                    <td class="px-6 py-4 text-right">
                        <span class="text-sm font-bold text-blue-600">{{ number_format($producto->total_vendido) }}</span>
                        <span class="text-xs text-gray-500">unidades</span>
                    </td>
                    <td class="px-6 py-4 text-right text-sm font-bold text-green-600">
                        S/ {{ number_format($producto->total_ingresos, 2) }}
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="px-2 py-1 text-xs rounded-full bg-purple-100 text-purple-800">
                            {{ $producto->numero_ventas }} ventas
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-gray-100">
                <tr>
                    <td colspan="3" class="px-6 py-3 text-right font-bold text-gray-700">Totales:</td>
                    <td class="px-6 py-3 text-right font-bold text-blue-600">
                        {{ number_format($productos->sum('total_vendido')) }} unidades
                    </td>
                    <td class="px-6 py-3 text-right font-bold text-green-600">
                        S/ {{ number_format($productos->sum('total_ingresos'), 2) }}
                    </td>
                    <td class="px-6 py-3 text-center font-bold text-purple-600">
                        {{ $productos->sum('numero_ventas') }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

@endsection