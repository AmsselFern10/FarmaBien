@extends('layouts.app')

@section('title', 'Kardex del Producto')

@section('header')
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Kardex: {{ $producto->nombre }}
        </h2>
        <div class="flex space-x-2">
            <a href="{{ route('productos.show', $producto) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
                Ver Producto
            </a>
            <a href="{{ route('inventario.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Volver
            </a>
        </div>
    </div>
@endsection

@section('content')

<!-- Información del Producto -->
<div class="bg-white overflow-hidden shadow-sm rounded-lg mb-6">
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <p class="text-sm text-gray-600">Producto</p>
                <p class="font-semibold text-gray-900">{{ $producto->nombre }}</p>
                <p class="text-xs text-gray-500">{{ $producto->categoria->nombre }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Código de Barra</p>
                <p class="font-semibold text-gray-900 font-mono">{{ $producto->codigo_barra ?: 'N/A' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Stock Total Actual</p>
                <p class="text-2xl font-bold text-blue-600">{{ $producto->stock_total }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Stock Mínimo</p>
                <p class="text-lg font-semibold {{ $producto->stock_total <= $producto->stock_minimo ? 'text-red-600' : 'text-green-600' }}">
                    {{ $producto->stock_minimo }}
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Filtros de Fecha -->
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
                    Filtrar
                </button>
                @if($fechaInicio || $fechaFin)
                <a href="{{ route('inventario.kardex-producto', $producto) }}" 
                   class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                    Limpiar
                </a>
                @endif
            </div>
            <div class="flex items-end">
                <button type="button" 
                        onclick="window.print()"
                        class="w-full bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded inline-flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                    Imprimir
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Tabla Kardex -->
<div class="bg-white overflow-hidden shadow-sm rounded-lg">
    <div class="p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Historial de Movimientos</h3>
        
        @if($movimientos && count($movimientos) > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lote</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Referencia</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Entrada</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Salida</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase bg-blue-50">Saldo</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Usuario</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @php $saldoAcumulado = 0; @endphp
                    @foreach($movimientos as $movimiento)
                    @php
                        if($movimiento->tipo_movimiento == 'entrada' || $movimiento->tipo_movimiento == 'ajuste_entrada') {
                            $saldoAcumulado += $movimiento->cantidad;
                        } else {
                            $saldoAcumulado -= abs($movimiento->cantidad);
                        }
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                            {{ $movimiento->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if($movimiento->tipo_movimiento == 'entrada')
                                <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Entrada</span>
                            @elseif($movimiento->tipo_movimiento == 'salida')
                                <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">Salida</span>
                            @elseif($movimiento->tipo_movimiento == 'ajuste_entrada')
                                <span class="px-2 py-1 text-xs rounded-full bg-purple-100 text-purple-800">Ajuste +</span>
                            @elseif($movimiento->tipo_movimiento == 'ajuste_salida')
                                <span class="px-2 py-1 text-xs rounded-full bg-orange-100 text-orange-800">Ajuste -</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm font-mono">
                            {{ $movimiento->lote->numero_lote }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">
                            @if($movimiento->venta_id)
                                <a href="{{ route('ventas.show', $movimiento->venta_id) }}" class="text-blue-600 hover:text-blue-800">
                                    Venta #{{ $movimiento->venta_id }}
                                </a>
                            @elseif($movimiento->compra_id)
                                <a href="{{ route('compras.show', $movimiento->compra_id) }}" class="text-blue-600 hover:text-blue-800">
                                    Compra #{{ $movimiento->compra_id }}
                                </a>
                            @else
                                <span class="text-gray-500">{{ Str::limit($movimiento->motivo, 30) }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            @if($movimiento->tipo_movimiento == 'entrada' || $movimiento->tipo_movimiento == 'ajuste_entrada')
                                <span class="text-sm font-semibold text-green-600">{{ $movimiento->cantidad }}</span>
                            @else
                                <span class="text-sm text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            @if($movimiento->tipo_movimiento == 'salida' || $movimiento->tipo_movimiento == 'ajuste_salida')
                                <span class="text-sm font-semibold text-red-600">{{ abs($movimiento->cantidad) }}</span>
                            @else
                                <span class="text-sm text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right bg-blue-50">
                            <span class="text-sm font-bold text-blue-600">{{ $saldoAcumulado }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">
                            {{ $movimiento->usuario->name }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-100">
                    <tr>
                        <td colspan="4" class="px-4 py-3 text-right font-bold text-gray-700">Totales:</td>
                        <td class="px-4 py-3 text-right font-bold text-green-600">
                            {{ $movimientos->whereIn('tipo_movimiento', ['entrada', 'ajuste_entrada'])->sum('cantidad') }}
                        </td>
                        <td class="px-4 py-3 text-right font-bold text-red-600">
                            {{ abs($movimientos->whereIn('tipo_movimiento', ['salida', 'ajuste_salida'])->sum('cantidad')) }}
                        </td>
                        <td class="px-4 py-3 text-right font-bold text-blue-600 bg-blue-100">
                            {{ $saldoAcumulado }}
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @else
        <p class="text-gray-500 text-center py-8">No hay movimientos registrados para este producto en el periodo seleccionado</p>
        @endif
    </div>
</div>

@endsection