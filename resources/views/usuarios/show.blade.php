@extends('layouts.app')

@section('title', 'Detalle de Usuario')

@section('header')
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Detalle del Usuario
        </h2>
        <div class="flex space-x-2">
            @can('editar usuarios')
            <a href="{{ route('usuarios.edit', $usuario) }}" 
               class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Editar
            </a>
            @endcan
            <a href="{{ route('usuarios.index') }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-400">
                Volver
            </a>
        </div>
    </div>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Tarjeta Principal del Usuario -->
    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
        <div class="p-6">
            <div class="flex items-start space-x-6">
                <!-- Avatar Grande -->
                <div class="flex-shrink-0">
                    <div class="h-32 w-32 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white text-4xl font-bold shadow-lg">
                        {{ strtoupper(substr($usuario->name, 0, 2)) }}
                    </div>
                </div>
                
                <!-- Información Principal -->
                <div class="flex-1">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900">{{ $usuario->name }}</h3>
                            <p class="text-gray-600">{{ $usuario->email }}</p>
                        </div>
                        <div>
                            @foreach($usuario->roles as $role)
                                <span class="px-4 py-2 inline-flex text-sm leading-5 font-semibold rounded-full 
                                    @if($role->name === 'Admin') bg-purple-100 text-purple-800
                                    @elseif($role->name === 'Cajero') bg-green-100 text-green-800
                                    @else bg-blue-100 text-blue-800
                                    @endif">
                                    🔑 {{ $role->name }}
                                </span>
                            @endforeach
                        </div>
                    </div>

                    <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-3">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <dt class="text-sm font-medium text-gray-500 mb-1">ID de Usuario</dt>
                            <dd class="text-lg font-semibold text-gray-900">#{{ $usuario->id }}</dd>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <dt class="text-sm font-medium text-gray-500 mb-1">Fecha de Registro</dt>
                            <dd class="text-lg font-semibold text-gray-900">{{ $usuario->created_at->format('d/m/Y') }}</dd>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <dt class="text-sm font-medium text-gray-500 mb-1">Última Actualización</dt>
                            <dd class="text-lg font-semibold text-gray-900">{{ $usuario->updated_at->diffForHumans() }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Permisos del Rol -->
    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                </svg>
                Permisos del Rol
            </h3>
        </div>
        <div class="p-6">
            @if($usuario->roles->isNotEmpty())
                @php
                    $permisos = $usuario->getAllPermissions()->groupBy(function($permiso) {
                        $partes = explode(' ', $permiso->name);
                        return ucfirst($partes[1] ?? 'Otros');
                    });
                @endphp
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($permisos as $modulo => $permisosModulo)
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                            <h4 class="font-semibold text-gray-900 mb-3 pb-2 border-b border-gray-200 flex items-center">
                                <svg class="w-4 h-4 mr-2 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                                </svg>
                                {{ $modulo }}
                            </h4>
                            <ul class="space-y-2">
                                @foreach($permisosModulo as $permiso)
                                    <li class="flex items-start text-sm text-gray-700">
                                        <svg class="w-5 h-5 mr-2 text-green-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                        <span>{{ ucfirst($permiso->name) }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>

                <!-- Resumen de Permisos -->
                <div class="mt-6 bg-blue-50 border-l-4 border-blue-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700">
                                <strong>Total de permisos:</strong> {{ $usuario->getAllPermissions()->count() }} permisos asignados a través del rol <strong>{{ $usuario->roles->first()->name }}</strong>
                            </p>
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    <p class="mt-2 text-gray-500">Este usuario no tiene roles ni permisos asignados</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Acciones Adicionales -->
    @can('desactivar usuarios')
    @if($usuario->id !== auth()->id())
    <div class="bg-white overflow-hidden shadow-sm rounded-lg border-l-4 border-red-400">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Zona de Peligro</h3>
            <p class="text-sm text-gray-600 mb-4">
                Las siguientes acciones son irreversibles. Por favor procede con precaución.
            </p>
            <form action="{{ route('usuarios.destroy', $usuario) }}" method="POST" 
                  onsubmit="return confirm('¿Está seguro de desactivar este usuario? Esta acción no se puede deshacer.');">
                @csrf
                @method('DELETE')
                <button type="submit" 
                        class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    Desactivar Usuario
                </button>
            </form>
        </div>
    </div>
    @endif
    @endcan
</div>
@endsection