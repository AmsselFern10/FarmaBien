@extends('layouts.app')

@section('title', 'Kardex del Lote')

@section('header')
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Kardex del Lote: {{ $lote->numero_lote }}
        </h2>
        <div class="flex space-x-2">
            <a href="{{ route('inventario.show-lote', $lote) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
                Ver Detalle
            </a>
            <a href="{{ route('inventario.lotes') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Volver
            </a>
        </div>
    </div>
@endsection

@section('content')

<!-- Información del Lote -->
<div class="bg-white overflow-hidden shadow-sm rounded-lg mb-6">
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <p class="text-sm text-gray-600">Producto</p>
                <p class="font-semibold text-gray-900">{{ $lote->producto->nombre }}</p>
                <p class="text-xs text-gray-500">{{ $lote->producto->categoria->nombre }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Número de Lote</p>
                <p class="font-bold text-gray-900 font-mono text-lg">{{ $lote->numero_lote }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Proveedor</p>
                <p class="font-semibold text-gray-900">{{ $lote->proveedor->nombre }}</p>
                @if($lote->compra)
                <p class="text-xs text-gray-500">Compra #{{ $lote->compra_id }}</p>
                @endif
            </div>
            <div>
                <p class="text-sm text-gray-600">Stock</p>
                <p class="text-2xl font-bold {{ $lote->cantidad_actual == 0 ? 'text-red-600' : 'text-blue-600' }}">
                    {{ $lote->cantidad_actual }} / {{ $lote->cantidad_inicial }}
                </p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Vencimiento</p>
                <p class="font-semibold {{ $lote->estaVencido() ? 'text-red-600' : 'text-gray-900' }}">
                    {{ $lote->fecha_vencimiento->format('d/m/Y') }}
                </p>
                <p class="text-xs {{ $lote->estaVencido() ? 'text-red-500' : 'text-gray-500' }}">
                    {{ $lote->fecha_vencimiento->diffForHumans() }}
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Tabla Kardex del Lote -->
<div class="bg-white overflow-hidden shadow-sm rounded-lg">
    <div class="p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Historial de Movimientos del Lote</h3>
            <button onclick="window.print()" 
                    class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                Imprimir
            </button>
        </div>
        
        @if($movimientos && count($movimientos) > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha/Hora</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo Movimiento</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Referencia</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Motivo</th>
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
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $movimiento->created_at->format('d/m/Y') }}</div>
                            <div class="text-xs text-gray-500">{{ $movimiento->created_at->format('H:i:s') }}</div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if($movimiento->tipo_movimiento == 'entrada')
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    Entrada
                                </span>
                            @elseif($movimiento->tipo_movimiento == 'salida')
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                    Salida
                                </span>
                            @elseif($movimiento->tipo_movimiento == 'ajuste_entrada')
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                                    Ajuste Entrada
                                </span>
                            @elseif($movimiento->tipo_movimiento == 'ajuste_salida')
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800">
                                    Ajuste Salida
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm">
                            @if($movimiento->venta_id)
                                <a href="{{ route('ventas.show', $movimiento->venta_id) }}" class="text-blue-600 hover:text-blue-800 font-medium">
                                    Venta #{{ $movimiento->venta_id }}
                                </a>
                            @elseif($movimiento->compra_id)
                                <a href="{{ route('compras.show', $movimiento->compra_id) }}" class="text-blue-600 hover:text-blue-800 font-medium">
                                    Compra #{{ $movimiento->compra_id }}
                                </a>
                            @else
                                <span class="text-gray-500">Ajuste Manual</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">
                            {{ Str::limit($movimiento->motivo, 40) }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            @if($movimiento->tipo_movimiento == 'entrada' || $movimiento->tipo_movimiento == 'ajuste_entrada')
                                <span class="text-sm font-bold text-green-600">{{ $movimiento->cantidad }}</span>
                            @else
                                <span class="text-sm text-gray-300">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            @if($movimiento->tipo_movimiento == 'salida' || $movimiento->tipo_movimiento == 'ajuste_salida')
                                <span class="text-sm font-bold text-red-600">{{ abs($movimiento->cantidad) }}</span>
                            @else
                                <span class="text-sm text-gray-300">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right bg-blue-50">
                            <span class="text-sm font-bold text-blue-600">{{ $saldoAcumulado }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm text-gray-900">{{ $movimiento->usuario->name }}</div>
                            <div class="text-xs text-gray-500">{{ $movimiento->usuario->email }}</div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-100 font-semibold">
                    <tr>
                        <td colspan="4" class="px-4 py-3 text-right text-gray-700">Totales:</td>
                        <td class="px-4 py-3 text-right text-green-700">
                            {{ $movimientos->whereIn('tipo_movimiento', ['entrada', 'ajuste_entrada'])->sum('cantidad') }}
                        </td>
                        <td class="px-4 py-3 text-right text-red-700">
                            {{ abs($movimientos->whereIn('tipo_movimiento', ['salida', 'ajuste_salida'])->sum('cantidad')) }}
                        </td>
                        <td class="px-4 py-3 text-right bg-blue-100 text-blue-700">
                            {{ $saldoAcumulado }}
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Estadísticas del Lote -->
        <div class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                <p class="text-xs text-green-600 mb-1">Total Entradas</p>
                <p class="text-2xl font-bold text-green-700">
                    {{ $movimientos->whereIn('tipo_movimiento', ['entrada', 'ajuste_entrada'])->sum('cantidad') }}
                </p>
            </div>
            <div class="bg-red-50 rounded-lg p-4 border border-red-200">
                <p class="text-xs text-red-600 mb-1">Total Salidas</p>
                <p class="text-2xl font-bold text-red-700">
                    {{ abs($movimientos->whereIn('tipo_movimiento', ['salida', 'ajuste_salida'])->sum('cantidad')) }}
                </p>
            </div>
            <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                <p class="text-xs text-blue-600 mb-1">Stock Actual</p>
                <p class="text-2xl font-bold text-blue-700">{{ $lote->cantidad_actual }}</p>
            </div>
            <div class="bg-purple-50 rounded-lg p-4 border border-purple-200">
                <p class="text-xs text-purple-600 mb-1">Total Movimientos</p>
                <p class="text-2xl font-bold text-purple-700">{{ count($movimientos) }}</p>
            </div>
        </div>
        @else
        <p class="text-gray-500 text-center py-12">No hay movimientos registrados para este lote</p>
        @endif
    </div>
</div>

@endsection