@extends('layouts.app')

@section('title', 'Recetas Médicas')

@section('header')
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Gestión de Recetas Médicas
        </h2>
        @can('registrar recetas')
        <a href="{{ route('recetas.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Nueva Receta
        </a>
        @endcan
    </div>
@endsection

@section('content')
<div class="bg-white overflow-hidden shadow-sm rounded-lg">
    
    <!-- Filtros -->
    <div class="p-6 border-b border-gray-200 bg-gray-50">
        <form method="GET" action="{{ route('recetas.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            
            <!-- Número de Receta -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">N° Receta</label>
                <input type="text" 
                       name="numero_receta" 
                       placeholder="Buscar por número..."
                       value="{{ request('numero_receta') }}"
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            <!-- Cliente -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Cliente</label>
                <select name="cliente_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Todos los clientes</option>
                    @foreach($clientes as $cliente)
                        <option value="{{ $cliente->id }}" {{ request('cliente_id') == $cliente->id ? 'selected' : '' }}>
                            {{ $cliente->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Botones -->
            <div class="flex items-end space-x-2">
                <button type="submit" class="flex-1 bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Filtrar
                </button>
                @if(request()->hasAny(['numero_receta', 'cliente_id']))
                <a href="{{ route('recetas.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                    Limpiar
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabla de Recetas -->
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        N° Receta
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Cliente
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Médico
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Fecha
                    </th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Estado
                    </th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Acciones
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($recetas as $receta)
                <tr class="hover:bg-gray-50">
                    
                    <!-- Número de Receta -->
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10 flex items-center justify-center rounded-full bg-blue-100">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-bold text-gray-900">{{ $receta->numero_receta }}</div>
                                <div class="text-xs text-gray-500">ID: #{{ $receta->id }}</div>
                            </div>
                        </div>
                    </td>

                    <!-- Cliente -->
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-gray-900">{{ $receta->cliente->nombre }}</div>
                        <div class="text-xs text-gray-500">{{ $receta->cliente->tipo_documento }}: {{ $receta->cliente->documento }}</div>
                    </td>

                    <!-- Médico -->
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-900">{{ $receta->medico }}</div>
                        @if($receta->especialidad)
                        <div class="text-xs text-gray-500">{{ $receta->especialidad }}</div>
                        @endif
                    </td>

                    <!-- Fecha -->
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $receta->fecha->format('d/m/Y') }}</div>
                        <div class="text-xs text-gray-500">{{ $receta->fecha->diffForHumans() }}</div>
                    </td>

                    <!-- Estado -->
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        @if($receta->esta_vencida)
                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                Vencida
                            </span>
                        @else
                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                Vigente
                            </span>
                        @endif
                    </td>

                    <!-- Acciones -->
                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                        <div class="flex justify-center space-x-2">
                            @can('ver recetas')
                            <a href="{{ route('recetas.show', $receta) }}" 
                               class="text-blue-600 hover:text-blue-900" 
                               title="Ver detalles">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </a>
                            @endcan

                            @can('registrar recetas')
                            <a href="{{ route('recetas.edit', $receta) }}" 
                               class="text-yellow-600 hover:text-yellow-900"
                               title="Editar">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </a>
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <p class="mt-2 text-gray-500">No se encontraron recetas médicas</p>
                        @can('registrar recetas')
                        <a href="{{ route('recetas.create') }}" class="mt-4 inline-block text-blue-600 hover:text-blue-800">
                            Registrar primera receta
                        </a>
                        @endcan
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <div class="px-6 py-4 border-t border-gray-200">
        {{ $recetas->links() }}
    </div>
</div>

<!-- Estadísticas -->
<div class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-6">
    <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0 bg-blue-100 rounded-md p-3">
                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
            <div class="ml-5 w-0 flex-1">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Total Recetas</dt>
                    <dd class="text-lg font-semibold text-gray-900">{{ $recetas->total() }}</dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0 bg-green-100 rounded-md p-3">
                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="ml-5 w-0 flex-1">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Vigentes</dt>
                    <dd class="text-lg font-semibold text-gray-900">
                        {{ $recetas->where('esta_vencida', false)->count() }}
                    </dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0 bg-red-100 rounded-md p-3">
                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="ml-5 w-0 flex-1">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Vencidas</dt>
                    <dd class="text-lg font-semibold text-gray-900">
                        {{ $recetas->where('esta_vencida', true)->count() }}
                    </dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0 bg-purple-100 rounded-md p-3">
                <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </div>
            <div class="ml-5 w-0 flex-1">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Esta Página</dt>
                    <dd class="text-lg font-semibold text-gray-900">{{ $recetas->count() }}</dd>
                </dl>
            </div>
        </div>
    </div>
</div>
@endsection