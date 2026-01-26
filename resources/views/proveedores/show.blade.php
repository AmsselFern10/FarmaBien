@extends('layouts.app')

@section('title', 'Detalle del Proveedor')

@section('header')
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Proveedor: {{ $proveedor->nombre }}
        </h2>
        <div class="flex space-x-2">
            @can('editar proveedores')
            <a href="{{ route('proveedores.edit', $proveedor) }}" 
               class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Editar
            </a>
            @endcan
            @can('registrar compras')
            <a href="{{ route('compras.create', ['proveedor_id' => $proveedor->id]) }}" 
               class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Nueva Compra
            </a>
            @endcan
            <a href="{{ route('proveedores.index') }}" 
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

<!-- Estado del Proveedor -->
@if(!$proveedor->activo)
<div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
    <div class="flex">
        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
        </svg>
        <div class="ml-3">
            <p class="text-sm text-red-700">
                <strong>Proveedor Inactivo:</strong> Este proveedor está desactivado en el sistema.
            </p>
        </div>
    </div>
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <!-- Información del Proveedor -->
    <div class="lg:col-span-2 space-y-6">
        
        <!-- Datos de la Empresa -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Información de la Empresa</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm text-gray-600">Razón Social</p>
                            <p class="font-semibold text-gray-900">{{ $proveedor->nombre }}</p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-600">RUC</p>
                            <p class="font-semibold text-gray-900 font-mono">{{ $proveedor->ruc }}</p>
                        </div>

                        @if($proveedor->contacto)
                        <div>
                            <p class="text-sm text-gray-600">Contacto Principal</p>
                            <p class="font-semibold text-gray-900">{{ $proveedor->contacto }}</p>
                        </div>
                        @endif

                        <div>
                            <p class="text-sm text-gray-600">Estado</p>
                            <p>
                                @if($proveedor->activo)
                                    <span class="px-3 py-1 text-sm rounded-full bg-green-100 text-green-800 font-semibold">Activo</span>
                                @else
                                    <span class="px-3 py-1 text-sm rounded-full bg-red-100 text-red-800 font-semibold">Inactivo</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="space-y-3">
                        @if($proveedor->telefono)
                        <div>
                            <p class="text-sm text-gray-600">Teléfono</p>
                            <p class="font-semibold text-gray-900">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                </svg>
                                {{ $proveedor->telefono }}
                            </p>
                        </div>
                        @endif

                        @if($proveedor->email)
                        <div>
                            <p class="text-sm text-gray-600">Correo Electrónico</p>
                            <p class="font-semibold text-gray-900">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                                <a href="mailto:{{ $proveedor->email }}" class="text-blue-600 hover:text-blue-800">
                                    {{ $proveedor->email }}
                                </a>
                            </p>
                        </div>
                        @endif

                        @if($proveedor->sitio_web)
                        <div>
                            <p class="text-sm text-gray-600">Sitio Web</p>
                            <p class="font-semibold text-gray-900">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path>
                                </svg>
                                <a href="{{ $proveedor->sitio_web }}" target="_blank" class="text-blue-600 hover:text-blue-800">
                                    {{ $proveedor->sitio_web }}
                                </a>
                            </p>
                        </div>
                        @endif

                        @if($proveedor->direccion)
                        <div>
                            <p class="text-sm text-gray-600">Dirección</p>
                            <p class="font-semibold text-gray-900">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                {{ $proveedor->direccion }}
                            </p>
                        </div>
                        @endif
                    </div>
                </div>

                @if($proveedor->observaciones)
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <p class="text-sm text-gray-600 mb-1">Observaciones</p>
                    <p class="text-gray-900">{{ $proveedor->observaciones }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Historial de Compras -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Historial de Compras (Últimas 10)</h3>
                
                @if($proveedor->compras && $proveedor->compras->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">N° Compra</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Comprobante</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Estado</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($proveedor->compras as $compra)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                    {{ $compra->fecha->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                    #{{ $compra->id }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                    <div>{{ strtoupper($compra->tipo_comprobante) }}</div>
                                    <div class="text-xs text-gray-500">{{ $compra->numero_comprobante }}</div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-gray-900 text-right">
                                    S/ {{ number_format($compra->total, 2) }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-center">
                                    @if($compra->estado == 'completada')
                                        <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Completada</span>
                                    @elseif($compra->estado == 'anulada')
                                        <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">Anulada</span>
                                    @elseif($compra->estado == 'modificada')
                                        <span class="px-2 py-1 text-xs rounded-full bg-purple-100 text-purple-800">Modificada</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-center">
                                    <a href="{{ route('compras.show', $compra) }}" 
                                       class="text-blue-600 hover:text-blue-900 text-xs">
                                        Ver detalle
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                @if($proveedor->compras->count() >= 10)
                <div class="mt-4 text-center">
                    <a href="{{ route('compras.index', ['proveedor_id' => $proveedor->id]) }}" 
                       class="text-sm text-blue-600 hover:text-blue-800">
                        Ver todas las compras →
                    </a>
                </div>
                @endif
                @else
                <p class="text-gray-500 text-center py-4">No hay compras registradas</p>
                @endif
            </div>
        </div>

    </div>

    <!-- Barra Lateral -->
    <div class="lg:col-span-1 space-y-6">
        
        <!-- Estadísticas del Proveedor -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Estadísticas</h3>
                
                <div class="space-y-4">
                    <div class="bg-purple-50 rounded-lg p-4">
                        <p class="text-xs text-purple-600 mb-1">Total Compras</p>
                        <p class="text-2xl font-bold text-purple-600">
                            {{ $proveedor->compras ? $proveedor->compras->count() : 0 }}
                        </p>
                    </div>

                    <div class="bg-blue-50 rounded-lg p-4">
                        <p class="text-xs text-blue-600 mb-1">Monto Total</p>
                        <p class="text-2xl font-bold text-blue-600">
                            S/ {{ $proveedor->compras ? number_format($proveedor->compras->where('estado', 'completada')->sum('total'), 2) : '0.00' }}
                        </p>
                    </div>

                    @if($proveedor->compras && $proveedor->compras->count() > 0)
                    <div class="bg-green-50 rounded-lg p-4">
                        <p class="text-xs text-green-600 mb-1">Última Compra</p>
                        <p class="text-sm font-semibold text-green-600">
                            {{ $proveedor->compras->first()->fecha->format('d/m/Y') }}
                        </p>
                        <p class="text-xs text-green-500 mt-1">
                            S/ {{ number_format($proveedor->compras->first()->total, 2) }}
                        </p>
                    </div>
                    @endif

                    @if($proveedor->compras && $proveedor->compras->count() > 0)
                    <div class="bg-yellow-50 rounded-lg p-4">
                        <p class="text-xs text-yellow-600 mb-1">Promedio por Compra</p>
                        <p class="text-xl font-bold text-yellow-600">
                            S/ {{ number_format($proveedor->compras->where('estado', 'completada')->avg('total'), 2) }}
                        </p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Acciones Rápidas -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Acciones</h3>
                
                <div class="space-y-2">
                    @can('registrar compras')
                    <a href="{{ route('compras.create', ['proveedor_id' => $proveedor->id]) }}" 
                       class="w-full bg-green-100 hover:bg-green-200 text-green-800 font-semibold py-2 px-4 rounded inline-flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Nueva Compra
                    </a>
                    @endcan

                    <button onclick="window.print()" 
                            class="w-full bg-gray-100 hover:bg-gray-200 text-gray-800 font-semibold py-2 px-4 rounded inline-flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                        </svg>
                        Imprimir
                    </button>

                    <a href="{{ route('proveedores.index') }}" 
                       class="w-full bg-blue-100 hover:bg-blue-200 text-blue-800 font-semibold py-2 px-4 rounded inline-flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                        </svg>
                        Ver Todos
                    </a>
                </div>
            </div>
        </div>

        <!-- Información del Sistema -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Información del Sistema</h3>
                
                <div class="space-y-2 text-sm text-gray-600">
                    <div>
                        <span class="font-medium">Registrado:</span><br>
                        {{ $proveedor->created_at->format('d/m/Y H:i') }}
                    </div>
                    <div>
                        <span class="font-medium">Última actualización:</span><br>
                        {{ $proveedor->updated_at->format('d/m/Y H:i') }}
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection