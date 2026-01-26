@extends('layouts.app')

@section('title', 'Detalle del Cliente')

@section('header')
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Cliente: {{ $cliente->nombre }}
        </h2>
        <div class="flex space-x-2">
            @can('editar clientes')
            <a href="{{ route('clientes.edit', $cliente) }}" 
               class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Editar
            </a>
            @endcan
            <a href="{{ route('clientes.index') }}" 
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

<!-- Estado del Cliente -->
@if(!$cliente->activo)
<div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
    <div class="flex">
        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
        </svg>
        <div class="ml-3">
            <p class="text-sm text-red-700">
                <strong>Cliente Inactivo:</strong> Este cliente está desactivado en el sistema.
            </p>
        </div>
    </div>
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <!-- Información Personal -->
    <div class="lg:col-span-2 space-y-6">
        
        <!-- Datos Principales -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Información Personal</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm text-gray-600">Nombre Completo</p>
                            <p class="font-semibold text-gray-900">{{ $cliente->nombre }}</p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-600">Tipo de Documento</p>
                            <p class="font-semibold text-gray-900">{{ $cliente->tipo_documento }}</p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-600">Número de Documento</p>
                            <p class="font-semibold text-gray-900 font-mono">{{ $cliente->documento }}</p>
                        </div>

                        @if($cliente->fecha_nacimiento)
                        <div>
                            <p class="text-sm text-gray-600">Fecha de Nacimiento</p>
                            <p class="font-semibold text-gray-900">
                                {{ $cliente->fecha_nacimiento->format('d/m/Y') }}
                                <span class="text-sm text-gray-500">({{ $cliente->fecha_nacimiento->age }} años)</span>
                            </p>
                        </div>
                        @endif
                    </div>

                    <div class="space-y-3">
                        @if($cliente->telefono)
                        <div>
                            <p class="text-sm text-gray-600">Teléfono</p>
                            <p class="font-semibold text-gray-900">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                </svg>
                                {{ $cliente->telefono }}
                            </p>
                        </div>
                        @endif

                        @if($cliente->email)
                        <div>
                            <p class="text-sm text-gray-600">Correo Electrónico</p>
                            <p class="font-semibold text-gray-900">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                                {{ $cliente->email }}
                            </p>
                        </div>
                        @endif

                        @if($cliente->direccion)
                        <div>
                            <p class="text-sm text-gray-600">Dirección</p>
                            <p class="font-semibold text-gray-900">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                {{ $cliente->direccion }}
                            </p>
                        </div>
                        @endif

                        <div>
                            <p class="text-sm text-gray-600">Estado</p>
                            <p>
                                @if($cliente->activo)
                                    <span class="px-3 py-1 text-sm rounded-full bg-green-100 text-green-800 font-semibold">Activo</span>
                                @else
                                    <span class="px-3 py-1 text-sm rounded-full bg-red-100 text-red-800 font-semibold">Inactivo</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                @if($cliente->observaciones)
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <p class="text-sm text-gray-600 mb-1">Observaciones</p>
                    <p class="text-gray-900">{{ $cliente->observaciones }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Historial de Compras -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Historial de Compras (Últimas 10)</h3>
                
                @if($cliente->ventas && $cliente->ventas->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">N° Venta</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Estado</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($cliente->ventas as $venta)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                    {{ $venta->fecha->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                    #{{ $venta->id }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-gray-900 text-right">
                                    S/ {{ number_format($venta->total, 2) }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-center">
                                    @if($venta->estado == 'completada')
                                        <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Completada</span>
                                    @elseif($venta->estado == 'anulada')
                                        <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">Anulada</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-center">
                                    <a href="{{ route('ventas.show', $venta) }}" 
                                       class="text-blue-600 hover:text-blue-900 text-xs">
                                        Ver detalle
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                @if($cliente->ventas->count() >= 10)
                <div class="mt-4 text-center">
                    <a href="{{ route('ventas.index', ['cliente_id' => $cliente->id]) }}" 
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

        <!-- Recetas Médicas -->
        @if($cliente->recetas && $cliente->recetas->count() > 0)
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Recetas Médicas</h3>
                
                <div class="space-y-3">
                    @foreach($cliente->recetas as $receta)
                    <div class="border rounded-lg p-4">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-semibold text-gray-900">Receta #{{ $receta->id }}</p>
                                <p class="text-sm text-gray-600">Médico: {{ $receta->medico }}</p>
                                <p class="text-xs text-gray-500">Fecha: {{ $receta->fecha->format('d/m/Y') }}</p>
                            </div>
                            <a href="{{ route('recetas.show', $receta) }}" 
                               class="text-blue-600 hover:text-blue-800 text-sm">
                                Ver detalle
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

    </div>

    <!-- Barra Lateral -->
    <div class="lg:col-span-1 space-y-6">
        
        <!-- Estadísticas del Cliente -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Estadísticas</h3>
                
                <div class="space-y-4">
                    <div class="bg-blue-50 rounded-lg p-4">
                        <p class="text-xs text-blue-600 mb-1">Total Compras</p>
                        <p class="text-2xl font-bold text-blue-600">
                            {{ $cliente->ventas ? $cliente->ventas->count() : 0 }}
                        </p>
                    </div>

                    <div class="bg-green-50 rounded-lg p-4">
                        <p class="text-xs text-green-600 mb-1">Monto Total</p>
                        <p class="text-2xl font-bold text-green-600">
                            S/ {{ $cliente->ventas ? number_format($cliente->ventas->where('estado', 'completada')->sum('total'), 2) : '0.00' }}
                        </p>
                    </div>

                    @if($cliente->ventas && $cliente->ventas->count() > 0)
                    <div class="bg-purple-50 rounded-lg p-4">
                        <p class="text-xs text-purple-600 mb-1">Última Compra</p>
                        <p class="text-sm font-semibold text-purple-600">
                            {{ $cliente->ventas->first()->fecha->format('d/m/Y') }}
                        </p>
                    </div>
                    @endif

                    @if($cliente->recetas && $cliente->recetas->count() > 0)
                    <div class="bg-yellow-50 rounded-lg p-4">
                        <p class="text-xs text-yellow-600 mb-1">Recetas Activas</p>
                        <p class="text-2xl font-bold text-yellow-600">
                            {{ $cliente->recetas->count() }}
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
                    @can('realizar ventas')
                    <a href="{{ route('ventas.create', ['cliente_id' => $cliente->id]) }}" 
                       class="w-full bg-blue-100 hover:bg-blue-200 text-blue-800 font-semibold py-2 px-4 rounded inline-flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Nueva Venta
                    </a>
                    @endcan

                    <button onclick="window.print()" 
                            class="w-full bg-gray-100 hover:bg-gray-200 text-gray-800 font-semibold py-2 px-4 rounded inline-flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                        </svg>
                        Imprimir
                    </button>

                    <a href="{{ route('clientes.index') }}" 
                       class="w-full bg-purple-100 hover:bg-purple-200 text-purple-800 font-semibold py-2 px-4 rounded inline-flex items-center justify-center">
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
                        {{ $cliente->created_at->format('d/m/Y H:i') }}
                    </div>
                    <div>
                        <span class="font-medium">Última actualización:</span><br>
                        {{ $cliente->updated_at->format('d/m/Y H:i') }}
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection