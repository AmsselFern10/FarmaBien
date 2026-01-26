@extends('layouts.app')

@section('title', 'Detalle de Receta')

@section('header')
    <div class="flex justify-between items-center">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Detalle de Receta Médica
            </h2>
            <p class="mt-1 text-sm text-gray-600">Receta N° {{ $receta->numero_receta }}</p>
        </div>
        <div class="flex space-x-2">
            @can('registrar recetas')
            <a href="{{ route('recetas.edit', $receta) }}" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Editar
            </a>
            @endcan
            <a href="{{ route('recetas.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Volver
            </a>
        </div>
    </div>
@endsection

@section('content')
<div class="max-w-7xl mx-auto">

    <!-- Estado de la Receta -->
    <div class="mb-6">
        @if($receta->esta_vencida)
            <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-md">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700">
                            <strong>Receta Vencida:</strong> Esta receta venció el {{ $receta->fecha_vencimiento->format('d/m/Y') }} 
                            ({{ $receta->fecha_vencimiento->diffForHumans() }})
                        </p>
                    </div>
                </div>
            </div>
        @else
            <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-md">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-green-700">
                            <strong>Receta Vigente</strong>
                            @if($receta->fecha_vencimiento)
                                - Válida hasta el {{ $receta->fecha_vencimiento->format('d/m/Y') }}
                            @else
                                - Sin fecha de vencimiento
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Columna Principal -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Información de la Receta -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4 pb-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Información de la Receta
                        </h3>
                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $receta->esta_vencida ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                            {{ $receta->esta_vencida ? 'Vencida' : 'Vigente' }}
                        </span>
                    </div>

                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Número de Receta</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ $receta->numero_receta }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">Fecha de Emisión</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                {{ $receta->fecha->format('d/m/Y') }}
                                <span class="text-gray-500">({{ $receta->fecha->diffForHumans() }})</span>
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">Fecha de Vencimiento</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                @if($receta->fecha_vencimiento)
                                    {{ $receta->fecha_vencimiento->format('d/m/Y') }}
                                    <span class="text-gray-500">({{ $receta->fecha_vencimiento->diffForHumans() }})</span>
                                @else
                                    <span class="text-gray-500 italic">Sin vencimiento</span>
                                @endif
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">Estado</dt>
                            <dd class="mt-1">
                                @if($receta->esta_vencida)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M
                                            10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
</svg>
Vencida
</span>
@else
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
<svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
</svg>
Vigente
</span>
@endif
</dd>
</div>
</dl>
</div>
</div>
<!-- Información del Médico -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-4 border-b border-gray-200 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    Médico Tratante
                </h3>

                <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Nombre del Médico</dt>
                        <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ $receta->medico }}</dd>
                    </div>

                    @if($receta->especialidad)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Especialidad</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $receta->especialidad }}</dd>
                    </div>
                    @endif

                    @if($receta->cmp)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">CMP</dt>
                        <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $receta->cmp }}</dd>
                    </div>
                    @endif
                </dl>
            </div>
        </div>

        <!-- Detalles Clínicos -->
        @if($receta->diagnostico || $receta->observaciones)
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-4 border-b border-gray-200 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    Detalles Clínicos
                </h3>

                <dl class="space-y-4">
                    @if($receta->diagnostico)
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Diagnóstico</dt>
                        <dd class="text-sm text-gray-900 bg-gray-50 p-3 rounded-md">{{ $receta->diagnostico }}</dd>
                    </div>
                    @endif

                    @if($receta->observaciones)
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Observaciones</dt>
                        <dd class="text-sm text-gray-900 bg-gray-50 p-3 rounded-md">{{ $receta->observaciones }}</dd>
                    </div>
                    @endif
                </dl>
            </div>
        </div>
        @endif

        <!-- Ventas Asociadas -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4 pb-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <svg class="w-6 h-6 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        Ventas Realizadas
                    </h3>
                    <span class="px-3 py-1 bg-indigo-100 text-indigo-800 text-xs font-semibold rounded-full">
                        {{ $receta->ventas->count() }} {{ Str::plural('venta', $receta->ventas->count()) }}
                    </span>
                </div>

                @if($receta->ventas->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">N° Venta</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                                    <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                                    <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($receta->ventas as $venta)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $venta->numero_comprobante ?? "V-{$venta->id}" }}</div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $venta->created_at->format('d/m/Y') }}</div>
                                        <div class="text-xs text-gray-500">{{ $venta->created_at->format('H:i') }}</div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-right">
                                        <div class="text-sm font-semibold text-gray-900">S/ {{ number_format($venta->total, 2) }}</div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-center">
                                        @can('ver ventas')
                                        <a href="{{ route('ventas.show', $venta) }}" class="text-blue-600 hover:text-blue-900" title="Ver venta">
                                            <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </a>
                                        @endcan
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <p class="mt-2 text-sm text-gray-500">No hay ventas asociadas a esta receta</p>
                    </div>
                @endif
            </div>
        </div>

    </div>

    <!-- Columna Lateral -->
    <div class="lg:col-span-1 space-y-6">
        
        <!-- Información del Cliente -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-4 border-b border-gray-200 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    Paciente
                </h3>

                <div class="space-y-3">
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase">Nombre</dt>
                        <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ $receta->cliente->nombre }}</dd>
                    </div>

                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase">Documento</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $receta->cliente->tipo_documento }}: {{ $receta->cliente->documento }}
                        </dd>
                    </div>

                    @if($receta->cliente->telefono)
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase">Teléfono</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $receta->cliente->telefono }}</dd>
                    </div>
                    @endif

                    @if($receta->cliente->email)
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase">Email</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $receta->cliente->email }}</dd>
                    </div>
                    @endif

                    <div class="pt-3 border-t border-gray-200">
                        @can('ver clientes')
                        <a href="{{ route('clientes.show', $receta->cliente) }}" class="text-sm text-blue-600 hover:text-blue-800 flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                            </svg>
                            Ver perfil completo
                        </a>
                        @endcan
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas Rápidas -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-4 border-b border-gray-200">
                    Estadísticas
                </h3>

                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-100 rounded-md p-2">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-xs text-gray-500">Ventas</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $receta->ventas->count() }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-green-100 rounded-md p-2">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-xs text-gray-500">Total Vendido</p>
                                <p class="text-lg font-semibold text-gray-900">
                                    S/ {{ number_format($receta->ventas->sum('total'), 2) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    @if($receta->fecha_vencimiento && !$receta->esta_vencida)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-yellow-100 rounded-md p-2">
                                <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-xs text-gray-500">Días Restantes</p>
                                <p class="text-lg font-semibold text-gray-900">
                                    {{ now()->diffInDays($receta->fecha_vencimiento, false) }}
                                </p>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Acciones Rápidas -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-4 border-b border-gray-200">
                    Acciones
                </h3>

                <div class="space-y-2">
                    @can('registrar recetas')
                    <a href="{{ route('recetas.edit', $receta) }}" class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Editar Receta
                    </a>
                    @endcan

                    <button onclick="window.print()" class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                        </svg>
                        Imprimir
                    </button>

                    @can('crear ventas')
                    @if(!$receta->esta_vencida)
                    <a href="{{ route('ventas.create', ['receta_id' => $receta->id]) }}" class="w-full flex items-center justify-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Nueva Venta
                    </a>
                    @endif
                    @endcan
                </div>
            </div>
        </div>

        <!-- Auditoría -->
        <div class="bg-gray-50 overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-3 uppercase">
                    Información del Sistema
                </h3>
                <dl class="space-y-2 text-xs">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">ID:</dt>
                        <dd class="text-gray-900 font-mono">#{{ $receta->id }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Creado:</dt>
                        <dd class="text-gray-900">{{ $receta->created_at->format('d/m/Y H:i') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Actualizado:</dt>
                        <dd class="text-gray-900">{{ $receta->updated_at->format('d/m/Y H:i') }}</dd>
                    </div>
                    @if($receta->created_at != $receta->updated_at)
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Última modificación:</dt>
                        <dd class="text-gray-900">{{ $receta->updated_at->diffForHumans() }}</dd>
                    </div>
                    @endif
                </dl>
            </div>
        </div>

    </div>

</div></div>
@push('styles')
<style>
    @media print {
        nav, .no-print, button, a[href] {
            display: none !important;
        }
        
        body {
            background: white;
        }
        
        .bg-white {
            box-shadow: none !important;
        }
    }
</style>
@endpush
@endsection