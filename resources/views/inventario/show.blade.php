@extends('layouts.app')

@section('title', 'Detalle del Lote')

@section('header')
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Lote: {{ $lote->numero_lote }}
        </h2>
        <div class="flex space-x-2">
            <a href="{{ route('inventario.kardex-lote', $lote) }}" 
               class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Ver Kardex
            </a>
            <a href="{{ route('inventario.lotes') }}" 
               class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Volver
            </a>
        </div>
    </div>
@endsection

@section('content')

<!-- Estado del Lote -->
@if($lote->estado == 'vencido')
<div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
    <div class="flex">
        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
        </svg>
        <div class="ml-3">
            <p class="text-sm text-red-700">
                <strong>Lote Vencido:</strong> Este lote venció el {{ $lote->fecha_vencimiento->format('d/m/Y') }} 
                ({{ $lote->fecha_vencimiento->diffForHumans() }})
            </p>
        </div>
    </div>
</div>
@elseif($lote->proximoVencer(30))
<div class="bg-orange-50 border-l-4 border-orange-400 p-4 mb-6">
    <div class="flex">
        <svg class="h-5 w-5 text-orange-400" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
        </svg>
        <div class="ml-3">
            <p class="text-sm text-orange-700">
                <strong>Próximo a Vencer:</strong> Este lote vence {{ $lote->fecha_vencimiento->diffForHumans() }} 
                ({{ $lote->fecha_vencimiento->format('d/m/Y') }})
            </p>
        </div>
    </div>
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <!-- Información Principal -->
    <div class="lg:col-span-2 space-y-6">
        
        <!-- Datos del Lote -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Información del Lote</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">Número de Lote</p>
                        <p class="text-xl font-bold text-gray-900 font-mono">{{ $lote->numero_lote }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-600">Estado</p>
                        <p>
                            @if($lote->estado == 'disponible')
                                <span class="px-3 py-1 text-sm rounded-full bg-green-100 text-green-800 font-semibold">Disponible</span>
                            @elseif($lote->estado == 'agotado')
                                <span class="px-3 py-1 text-sm rounded-full bg-gray-100 text-gray-800 font-semibold">Agotado</span>
                            @elseif($lote->estado == 'vencido')
                                <span class="px-3 py-1 text-sm rounded-full bg-red-100 text-red-800 font-semibold">Vencido</span>
                            @endif
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-600">Producto</p>
                        <p class="font-semibold text-gray-900">{{ $lote->producto->nombre }}</p>
                        <p class="text-xs text-gray-500">{{ $lote->producto->categoria->nombre }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-600">Proveedor</p>
                        <p class="font-semibold text-gray-900">{{ $lote->proveedor->nombre }}</p>
                        <p class="text-xs text-gray-500">RUC: {{ $lote->proveedor->ruc }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-600">Fecha de Vencimiento</p>
                        <p class="font-semibold {{ $lote->estaVencido() ? 'text-red-600' : 'text-gray-900' }}">
                            {{ $lote->fecha_vencimiento->format('d/m/Y') }}
                        </p>
                        <p class="text-xs {{ $lote->estaVencido() ? 'text-red-500' : 'text-gray-500' }}">
                            {{ $lote->fecha_vencimiento->diffForHumans() }}
                        </p>
                    </div>

                    @if($lote->compra)
                    <div>
                        <p class="text-sm text-gray-600">Compra de Origen</p>
                        <a href="{{ route('compras.show', $lote->compra) }}" 
                           class="font-semibold text-blue-600 hover:text-blue-800">
                            Compra #{{ $lote->compra_id }}
                        </a>
                        <p class="text-xs text-gray-500">
                            {{ $lote->compra->fecha->format('d/m/Y') }}
                            por {{ $lote->compra->usuario->name }}
                        </p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Stock del Lote -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Información de Stock</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                        <p class="text-xs text-blue-600 mb-1">Stock Inicial</p>
                        <p class="text-3xl font-bold text-blue-700">{{ $lote->cantidad_inicial }}</p>
                    </div>

                    <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                        <p class="text-xs text-green-600 mb-1">Stock Actual</p>
                        <p class="text-3xl font-bold text-green-700">{{ $lote->cantidad_actual }}</p>
                    </div>

                    <div class="bg-purple-50 rounded-lg p-4 border border-purple-200">
                        <p class="text-xs text-purple-600 mb-1">Stock Utilizado</p>
                        <p class="text-3xl font-bold text-purple-700">{{ $lote->cantidad_inicial - $lote->cantidad_actual }}</p>
                    </div>
                </div>

                <!-- Barra de Progreso -->
                <div class="mt-4">
                    <div class="flex justify-between text-xs text-gray-600 mb-1">
                        <span>Utilización del Lote</span>
                        <span>{{ number_format((($lote->cantidad_inicial - $lote->cantidad_actual) / $lote->cantidad_inicial) * 100, 1) }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div class="bg-blue-600 h-3 rounded-full transition-all" 
                             style="width: {{ (($lote->cantidad_inicial - $lote->cantidad_actual) / $lote->cantidad_inicial) * 100 }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Resumen de Movimientos -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Resumen de Movimientos</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="border-l-4 border-green-400 pl-4">
                        <p class="text-sm text-gray-600">Total Entradas</p>
                        <p class="text-2xl font-bold text-green-600">{{ $totalEntradas }}</p>
                    </div>

                    <div class="border-l-4 border-red-400 pl-4">
                        <p class="text-sm text-gray-600">Total Salidas</p>
                        <p class="text-2xl font-bold text-red-600">{{ $totalSalidas }}</p>
                    </div>

                    <div class="border-l-4 border-yellow-400 pl-4">
                        <p class="text-sm text-gray-600">Total Ajustes</p>
                        <p class="text-2xl font-bold text-yellow-600">{{ $totalAjustes }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Últimos Movimientos -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Últimos Movimientos (5)</h3>
                    <a href="{{ route('inventario.kardex-lote', $lote) }}" 
                       class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                        Ver todos →
                    </a>
                </div>
                
                @if($lote->movimientos && $lote->movimientos->count() > 0)
                <div class="space-y-3">
                    @foreach($lote->movimientos->take(5) as $movimiento)
                    <div class="flex justify-between items-center border-b pb-2">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900">
                                @if($movimiento->tipo_movimiento == 'entrada')
                                    <span class="text-green-600">Entrada</span>
                                @elseif($movimiento->tipo_movimiento == 'salida')
                                    <span class="text-blue-600">Salida</span>
                                @else
                                    <span class="text-purple-600">Ajuste</span>
                                @endif
                                de {{ abs($movimiento->cantidad) }} unidades
                            </p>
                            <p class="text-xs text-gray-500">
                                {{ $movimiento->created_at->format('d/m/Y H:i') }} - {{ $movimiento->usuario->name }}
                            </p>
                        </div>
                        <div class="text-right">
                            @if($movimiento->venta_id)
                                <a href="{{ route('ventas.show', $movimiento->venta_id) }}" 
                                   class="text-xs text-blue-600 hover:text-blue-800">
                                    Venta #{{ $movimiento->venta_id }}
                                </a>
                            @elseif($movimiento->compra_id)
                                <a href="{{ route('compras.show', $movimiento->compra_id) }}" 
                                   class="text-xs text-blue-600 hover:text-blue-800">
                                    Compra #{{ $movimiento->compra_id }}
                                </a>
                            @else
                                <span class="text-xs text-gray-500">Ajuste manual</span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-gray-500 text-center py-4">No hay movimientos registrados</p>
                @endif
            </div>
        </div>

    </div>

    <!-- Barra Lateral -->
    <div class="lg:col-span-1 space-y-6">
        
        <!-- Estadísticas Rápidas -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Estadísticas</h3>
                
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Días desde creación</span>
                        <span class="font-semibold text-gray-900">{{ $lote->created_at->diffInDays() }}</span>
                    </div>

                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Días hasta vencer</span>
                        <span class="font-semibold {{ $lote->estaVencido() ? 'text-red-600' : 'text-gray-900' }}">
                            {{ $lote->estaVencido() ? 'Vencido' : $lote->fecha_vencimiento->diffInDays() }}
                        </span>
                    </div>

                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">% Utilizado</span>
                        <span class="font-semibold text-gray-900">
                            {{ number_format((($lote->cantidad_inicial - $lote->cantidad_actual) / $lote->cantidad_inicial) * 100, 1) }}%
                        </span>
                    </div>

                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Total Movimientos</span>
                        <span class="font-semibold text-gray-900">{{ $lote->movimientos->count() }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Acciones Rápidas -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Acciones</h3>
                
                <div class="space-y-2">
                    <a href="{{ route('inventario.kardex-lote', $lote) }}" 
                       class="w-full bg-purple-100 hover:bg-purple-200 text-purple-800 font-semibold py-2 px-4 rounded inline-flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Ver Kardex Completo
                    </a>

                    <a href="{{ route('productos.show', $lote->producto) }}" 
                       class="w-full bg-blue-100 hover:bg-blue-200 text-blue-800 font-semibold py-2 px-4 rounded inline-flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                        Ver Producto
                    </a>

                    @if($lote->compra)
                    <a href="{{ route('compras.show', $lote->compra) }}" 
                       class="w-full bg-green-100 hover:bg-green-200 text-green-800 font-semibold py-2 px-4 rounded inline-flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Ver Compra Origen
                    </a>
                    @endif

                    <button onclick="window.print()" 
                            class="w-full bg-gray-100 hover:bg-gray-200 text-gray-800 font-semibold py-2 px-4 rounded inline-flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                        </svg>
                        Imprimir
                    </button>
                </div>
            </div>
        </div>

        <!-- Información del Sistema -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Sistema</h3>
                
                <div class="space-y-2 text-sm text-gray-600">
                    <div>
                        <span class="font-medium">Creado:</span><br>
                        {{ $lote->created_at->format('d/m/Y H:i') }}
                    </div>
                    <div>
                        <span class="font-medium">Actualizado:</span><br>
                        {{ $lote->updated_at->format('d/m/Y H:i') }}
                    </div>
                    <div>
                        <span class="font-medium">ID del Lote:</span><br>
                        {{ $lote->id }}
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection