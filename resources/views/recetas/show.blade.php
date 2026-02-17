@extends('layouts.app')

@section('title', 'Detalle de Receta')

@section('header')
    Receta Médica
@endsection

@section('page-actions')
    <div>
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white">
            Detalle de Receta Médica
        </h2>
        <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
            Receta Nº {{ $receta->numero_receta }}
        </p>
    </div>
    <div class="flex gap-3">
        @can('registrar recetas')
        <a href="{{ route('recetas.edit', $receta) }}" 
           class="inline-flex items-center px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white font-semibold rounded-lg transition-colors shadow-sm">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
            Editar
        </a>
        @endcan
        <a href="{{ route('recetas.index') }}" 
           class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-slate-700 dark:text-slate-300 font-semibold rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors shadow-sm">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Volver
        </a>
    </div>
@endsection

@section('content')

<!-- Alerta de Estado Vencida -->
@if($receta->esta_vencida)
<div class="mb-6 bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 dark:border-red-400 p-4 rounded-r-lg">
    <div class="flex items-start">
        <div class="flex-shrink-0">
            <svg class="h-6 w-6 text-red-500 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
            </svg>
        </div>
        <div class="ml-3 flex-1">
            <h3 class="text-sm font-semibold text-red-800 dark:text-red-200">
                ⚠️ Receta Vencida
            </h3>
            <p class="mt-1 text-sm text-red-700 dark:text-red-300">
                Esta receta ha excedido su vigencia y no debe ser utilizada para dispensar medicamentos.
            </p>
        </div>
    </div>
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <!-- COLUMNA PRINCIPAL (2/3) -->
    <div class="lg:col-span-2 space-y-6">
        
        <!-- Información de la Receta -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-blue-50 to-white dark:from-gray-800 dark:to-gray-800/50">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Información de la Receta</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Datos de identificación</p>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <!-- Número de Receta -->
                    <div class="md:col-span-2">
                        <dt class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">
                            Número de Receta
                        </dt>
                        <dd class="text-lg font-bold text-slate-900 dark:text-white bg-slate-100 dark:bg-slate-700 px-4 py-2 rounded-lg font-mono">
                            {{ $receta->numero_receta }}
                        </dd>
                    </div>

                    <!-- ID de Receta -->
                    <div>
                        <dt class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">
                            ID de Receta
                        </dt>
                        <dd class="text-base font-semibold text-slate-900 dark:text-white">
                            #{{ str_pad($receta->id, 6, '0', STR_PAD_LEFT) }}
                        </dd>
                    </div>

                    <!-- Fecha de Emisión -->
                    <div>
                        <dt class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">
                            Fecha de Emisión
                        </dt>
                        <dd class="text-base font-semibold text-slate-900 dark:text-white">
                            {{ $receta->fecha->format('d/m/Y') }}
                            <span class="text-xs text-slate-500 dark:text-slate-400 ml-2">
                                ({{ $receta->fecha->diffForHumans() }})
                            </span>
                        </dd>
                    </div>

                    <!-- Estado -->
                    <div class="md:col-span-2">
                        <dt class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-2">
                            Estado de Vigencia
                        </dt>
                        <dd>
                            @if($receta->esta_vencida)
                                <span class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300">
                                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                    </svg>
                                    Receta Vencida
                                </span>
                            @else
                                <span class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300">
                                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    Receta Vigente
                                </span>
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Información del Paciente -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-indigo-50 to-white dark:from-gray-800 dark:to-gray-800/50">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg">
                        <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Información del Paciente</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Cliente asociado a la receta</p>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <div class="flex items-start space-x-4">
                    <div class="h-16 w-16 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center flex-shrink-0">
                        <span class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">
                            {{ strtoupper(substr($receta->cliente->nombre, 0, 2)) }}
                        </span>
                    </div>
                    <div class="flex-1">
                        <h4 class="text-xl font-bold text-slate-900 dark:text-white mb-2">
                            {{ $receta->cliente->nombre }}
                        </h4>
                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <dt class="text-xs font-medium text-slate-500 dark:text-slate-400">
                                    Tipo de Documento
                                </dt>
                                <dd class="text-sm font-semibold text-slate-900 dark:text-white">
                                    {{ strtoupper($receta->cliente->tipo_documento) }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-slate-500 dark:text-slate-400">
                                    Número de Documento
                                </dt>
                                <dd class="text-sm font-semibold text-slate-900 dark:text-white font-mono">
                                    {{ $receta->cliente->documento }}
                                </dd>
                            </div>
                        </dl>
                        <a href="{{ route('clientes.show', $receta->cliente) }}" 
                           class="mt-3 inline-flex items-center text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">
                            Ver perfil completo del paciente
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Información del Médico -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-emerald-50 to-white dark:from-gray-800 dark:to-gray-800/50">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg">
                        <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Médico Prescriptor</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Profesional que emitió la receta</p>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <div class="flex items-start space-x-4">
                    <div class="h-14 w-14 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center flex-shrink-0">
                        <svg class="w-8 h-8 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h4 class="text-lg font-bold text-slate-900 dark:text-white">
                            {{ $receta->medico }}
                        </h4>
                        @if($receta->especialidad)
                        <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 dark:bg-emerald-900/30 text-emerald-800 dark:text-emerald-300">
                                {{ $receta->especialidad }}
                            </span>
                        </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Detalles Clínicos -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-purple-50 to-white dark:from-gray-800 dark:to-gray-800/50">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Detalles Clínicos</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Diagnóstico y observaciones médicas</p>
                    </div>
                </div>
            </div>

            <div class="p-6 space-y-6">
                
                <!-- Diagnóstico -->
                <div>
                    <dt class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                        Diagnóstico
                    </dt>
                    @if($receta->diagnostico)
                    <dd class="text-sm text-slate-900 dark:text-white bg-slate-50 dark:bg-slate-700/50 p-4 rounded-lg whitespace-pre-wrap">{{ $receta->diagnostico }}</dd>
                    @else
                    <dd class="text-sm text-slate-500 dark:text-slate-400 italic">No se registró diagnóstico</dd>
                    @endif
                </div>

                <!-- Observaciones -->
                <div>
                    <dt class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                        Observaciones
                    </dt>
                    @if($receta->observaciones)
                    <dd class="text-sm text-slate-900 dark:text-white bg-slate-50 dark:bg-slate-700/50 p-4 rounded-lg whitespace-pre-wrap">{{ $receta->observaciones }}</dd>
                    @else
                    <dd class="text-sm text-slate-500 dark:text-slate-400 italic">No se registraron observaciones</dd>
                    @endif
                </div>
            </div>
        </div>

    </div>

    <!-- COLUMNA LATERAL (1/3) -->
    <div class="lg:col-span-1 space-y-6">
        
        <!-- Acciones Rápidas -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-slate-50 to-white dark:from-gray-800 dark:to-gray-800/50">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Acciones Rápidas</h3>
            </div>
            <div class="p-4 space-y-2">
                @can('registrar recetas')
                <a href="{{ route('recetas.edit', $receta) }}" 
                   class="w-full inline-flex items-center justify-center px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white font-semibold rounded-lg transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Editar Receta
                </a>
                @endcan
        

                <a href="{{ route('recetas.index') }}" 
                   class="w-full inline-flex items-center justify-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-slate-700 dark:text-slate-300 font-semibold rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                    </svg>
                    Ver Todas
                </a>
            </div>
        </div>

        <!-- Información del Sistema -->
        <div class="bg-gradient-to-br from-slate-50 to-slate-100 dark:from-gray-700 dark:to-gray-800 rounded-xl shadow-lg border border-slate-200 dark:border-gray-600 overflow-hidden">
            <div class="p-6">
                <div class="flex items-center space-x-2 mb-4">
                    <svg class="w-5 h-5 text-slate-600 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300">Información del Sistema</h3>
                </div>
                <div class="space-y-3 text-sm">
                    <div class="flex items-center text-slate-600 dark:text-slate-400">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        <span class="font-medium">Creada:</span>
                        <span class="ml-auto">{{ $receta->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="flex items-center text-slate-600 dark:text-slate-400">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        <span class="font-medium">Modificada:</span>
                        <span class="ml-auto">{{ $receta->updated_at->format('d/m/Y H:i') }}</span>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>

@endsection