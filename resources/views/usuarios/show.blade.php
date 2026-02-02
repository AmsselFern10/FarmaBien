@extends('layouts.app')

@section('title', 'Detalle de Usuario')

@section('header')
    Usuario: {{ $usuario->name }}
@endsection

@section('page-actions')
    <div>
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white">
            {{ $usuario->name }}
        </h2>
        <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
            {{ $usuario->email }}
        </p>
    </div>
    <div class="flex gap-3">
        @can('editar usuarios')
        <a href="{{ route('usuarios.edit', $usuario) }}" 
           class="inline-flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white font-semibold rounded-lg transition-colors shadow-sm">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
            Editar
        </a>
        @endcan
        <a href="{{ route('usuarios.index') }}" 
           class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-slate-700 dark:text-slate-300 font-semibold rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors shadow-sm">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Volver
        </a>
    </div>
@endsection

@section('content')

<!-- Tarjeta Principal del Usuario -->
<div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden mb-6">
    <div class="p-6">
        <div class="flex items-start space-x-6">
            <!-- Avatar Grande -->
            <div class="flex-shrink-0">
                <div class="h-32 w-32 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 dark:from-blue-600 dark:to-blue-700 flex items-center justify-center text-white text-4xl font-bold shadow-lg ring-4 ring-blue-100 dark:ring-blue-900/30">
                    {{ strtoupper(substr($usuario->name, 0, 2)) }}
                </div>
            </div>
            
            <!-- Información Principal -->
            <div class="flex-1">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-2xl font-bold text-slate-900 dark:text-white">{{ $usuario->name }}</h3>
                        <p class="text-slate-600 dark:text-slate-400 mt-1">{{ $usuario->email }}</p>
                    </div>
                    <div>
                        @foreach($usuario->roles as $role)
                            <span class="px-4 py-2 inline-flex items-center text-sm leading-5 font-semibold rounded-full 
                                @if($role->name === 'Admin') bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-300
                                @elseif($role->name === 'Cajero') bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300
                                @else bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300
                                @endif">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                                </svg>
                                {{ $role->name }}
                            </span>
                        @endforeach
                    </div>
                </div>

                <dl class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div class="bg-gradient-to-br from-slate-50 to-slate-100 dark:from-gray-700 dark:to-gray-700/50 p-4 rounded-lg border border-slate-200 dark:border-gray-600">
                        <dt class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-1">ID de Usuario</dt>
                        <dd class="text-lg font-bold text-slate-900 dark:text-white">#{{ str_pad($usuario->id, 4, '0', STR_PAD_LEFT) }}</dd>
                    </div>
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-900/10 p-4 rounded-lg border border-blue-200 dark:border-blue-800">
                        <dt class="text-xs font-medium text-blue-600 dark:text-blue-400 uppercase tracking-wide mb-1">Fecha de Registro</dt>
                        <dd class="text-lg font-bold text-blue-900 dark:text-blue-300">{{ $usuario->created_at->format('d/m/Y') }}</dd>
                    </div>
                    <div class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-900/10 p-4 rounded-lg border border-green-200 dark:border-green-800">
                        <dt class="text-xs font-medium text-green-600 dark:text-green-400 uppercase tracking-wide mb-1">Última Actualización</dt>
                        <dd class="text-lg font-bold text-green-900 dark:text-green-300">{{ $usuario->updated_at->diffForHumans() }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</div>

<!-- Permisos del Rol -->
<div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden mb-6">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-indigo-50 to-white dark:from-gray-800 dark:to-gray-800/50">
        <div class="flex items-center space-x-3">
            <div class="p-2 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg">
                <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Permisos del Rol</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400">Capacidades asignadas al usuario</p>
            </div>
        </div>
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
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:shadow-md dark:hover:shadow-gray-900/30 transition-shadow bg-white dark:bg-gray-800/50">
                        <h4 class="font-semibold text-slate-900 dark:text-white mb-3 pb-2 border-b border-gray-200 dark:border-gray-700 flex items-center">
                            <svg class="w-4 h-4 mr-2 text-blue-500 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                            </svg>
                            {{ $modulo }}
                        </h4>
                        <ul class="space-y-2">
                            @foreach($permisosModulo as $permiso)
                                <li class="flex items-start text-sm text-slate-700 dark:text-slate-300">
                                    <svg class="w-5 h-5 mr-2 text-green-500 dark:text-green-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
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
            <div class="mt-6 bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500 dark:border-blue-400 p-4 rounded-r-lg">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-blue-500 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-800 dark:text-blue-200">
                            <strong>Total de permisos:</strong> {{ $usuario->getAllPermissions()->count() }} permisos asignados a través del rol 
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-blue-200 dark:bg-blue-800 text-blue-900 dark:text-blue-100">
                                {{ $usuario->roles->first()->name }}
                            </span>
                        </p>
                    </div>
                </div>
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-16 w-16 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
                <p class="mt-4 text-slate-500 dark:text-slate-400 font-medium">Este usuario no tiene roles ni permisos asignados</p>
            </div>
        @endif
    </div>
</div>

<!-- Zona de Peligro -->
@can('desactivar usuarios')
@if($usuario->id !== auth()->id())
<div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border-l-4 border-red-500 dark:border-red-400 overflow-hidden">
    <div class="p-6">
        <div class="flex items-start space-x-3 mb-4">
            <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-red-500 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
            <div class="flex-1">
                <h3 class="text-lg font-semibold text-red-900 dark:text-red-200 mb-1">Zona de Peligro</h3>
                <p class="text-sm text-red-700 dark:text-red-300 mb-4">
                    Las siguientes acciones son irreversibles. Por favor procede con precaución.
                </p>
                <form action="{{ route('usuarios.destroy', $usuario) }}" method="POST" 
                      onsubmit="return confirm('¿Está seguro de desactivar este usuario? Esta acción no se puede deshacer.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 dark:bg-red-500 dark:hover:bg-red-600 text-white font-semibold rounded-lg transition-colors shadow-sm">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Desactivar Usuario
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif
@endcan

@endsection