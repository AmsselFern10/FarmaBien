@extends('layouts.app')

@section('title', 'Alertas de Inventario')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Alertas de Inventario
    </h2>
@endsection

@section('content')

<!-- Resumen de Alertas -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-6 rounded-lg">
        <div class="flex items-center">
            <svg class="h-8 w-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
            <div class="ml-4">
                <p class="text-sm font-medium text-yellow-800">Stock Bajo</p>
                <p class="text-3xl font-bold text-yellow-900">{{ $productosStockBajo->count() }}</p>
            </div>
        </div>
    </div>

    <div class="bg-orange-50 border-l-4 border-orange-400 p-6 rounded-lg">
        <div class="flex items-center">
            <svg class="h-8 w-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div class="ml-4">
                <p class="text-sm font-medium text-orange-800">Próximos a Vencer</p>
                <p class="text-3xl font-bold text-orange-900">{{ $lotesProximosVencer->count() }}</p>
            </div>
        </div>
    </div>

    <div class="bg-red-50 border-l-4 border-red-400 p-6 rounded-lg">
        <div class="flex items-center">
            <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
            <div class="ml-4">
                <p class="text-sm font-medium text-red-800">Vencidos</p>
                <p class="text-3xl font-bold text-red-900">{{ $lotesVencidos->count() }}</p>
            </div>
        </div>
    </div>
</div>

<!-- Productos con Stock Bajo -->
<div class="bg-white overflow-hidden shadow-sm rounded-lg mb-6">
    <div class="p-6 border-b border-gray-200 bg-yellow-50">
        <h3 class="text-lg font-semibold text-gray-900">
            ⚠️ Productos con Stock Bajo ({{ $productosStockBajo->count() }})
        </h3>
    </div>
    <div class="p-6">
        @if($productosStockBajo->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Producto</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Stock Actual</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Stock Mínimo</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Faltante</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($productosStockBajo as $producto)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ $producto->nombre }}</div>
                            <div class="text-xs text-gray-500">{{ $producto->categoria->nombre }}</div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="px-3 py-1 text-sm font-bold rounded-full {{ $producto->stock_total == 0 ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800' }}">
                                {{ $producto->stock_total }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center text-sm text-gray-900">
                            {{ $producto->stock_minimo }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="text-sm font-semibold text-red-600">
                                {{ max(0, $producto->stock_minimo - $producto->stock_total) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <a href="{{ route('compras.create', ['producto_id' => $producto->id]) }}" 
                               class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                                Realizar Compra
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="text-gray-500 text-center py-8">✓ Todos los productos tienen stock adecuado</p>
        @endif
    </div>
</div>

<!-- Lotes Próximos a Vencer -->
<div class="bg-white overflow-hidden shadow-sm rounded-lg mb-6">
    <div class="p-6 border-b border-gray-200 bg-orange-50">
        <div class="flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-900">
                🕐 Lotes Próximos a Vencer ({{ $lotesProximosVencer->count() }})
            </h3>
            <form method="GET" class="flex items-center space-x-2">
                <label class="text-sm text-gray-700">Días:</label>
                <input type="number" name="dias_vencimiento" value="{{ $diasVencimiento }}" 
                       class="w-20 rounded-md border-gray-300 text-sm" min="1">
                <button type="submit" class="bg-orange-500 text-white px-3 py-1 rounded text-sm">
                    Filtrar
                </button>
            </form>
        </div>
    </div>
    <div class="p-6">
        @if($lotesProximosVencer->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Producto</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">N° Lote</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Stock</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Vence</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Tiempo Restante</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($lotesProximosVencer as $lote)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ $lote->producto->nombre }}</div>
                        </td>
                        <td class="px-6 py-4 text-sm font-mono">{{ $lote->numero_lote }}</td>
                        <td class="px-6 py-4 text-center text-sm font-semibold">{{ $lote->cantidad_actual }}</td>
                        <td class="px-6 py-4 text-center text-sm">{{ $lote->fecha_vencimiento->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 text-center">
                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800">
                                {{ $lote->fecha_vencimiento->diffForHumans() }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="text-gray-500 text-center py-8">✓ No hay lotes próximos a vencer</p>
        @endif
    </div>
</div>

<!-- Lotes Vencidos -->
<div class="bg-white overflow-hidden shadow-sm rounded-lg">
    <div class="p-6 border-b border-gray-200 bg-red-50">
        <h3 class="text-lg font-semibold text-gray-900">
            ❌ Lotes Vencidos ({{ $lotesVencidos->count() }})
        </h3>
    </div>
    <div class="p-6">
        @if($lotesVencidos->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Producto</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">N° Lote</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Stock</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Vencimiento</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Estado</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($lotesVencidos as $lote)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ $lote->producto->nombre }}</div>
                        </td>
                        <td class="px-6 py-4 text-sm font-mono">{{ $lote->numero_lote }}</td>
                        <td class="px-6 py-4 text-center text-sm font-semibold text-red-600">{{ $lote->cantidad_actual }}</td>
                        <td class="px-6 py-4 text-center text-sm text-red-600">{{ $lote->fecha_vencimiento->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 text-center">
                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                Vencido hace {{ $lote->fecha_vencimiento->diffForHumans() }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="text-gray-500 text-center py-8">✓ No hay lotes vencidos</p>
        @endif
    </div>
</div>

@endsection