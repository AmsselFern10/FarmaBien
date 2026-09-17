@extends('layouts.app')

@section('title', 'Nuevo Usuario')

@section('header')
    Crear Usuario
@endsection

@section('page-actions')
    <div>
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white">
            Nuevo Usuario
        </h2>
        <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
            Registra un nuevo usuario en el sistema
        </p>
    </div>
    <div>
        <a href="{{ route('usuarios.index') }}" 
           class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-slate-700 dark:text-slate-300 font-semibold rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200 shadow-sm">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Volver
        </a>
    </div>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    <form action="{{ route('usuarios.store') }}" method="POST">
        @csrf

        <!-- Información del Usuario -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-blue-50 to-white dark:from-gray-800 dark:to-gray-800/50">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Información del Usuario</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Datos de identificación y acceso</p>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <!-- Nombre -->
                    <div class="md:col-span-2">
                        <label for="name" class="block text-sm font-medium text-slate-900 dark:text-white mb-2">
                            Nombre Completo <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <input type="text" 
                                   name="name" 
                                   id="name" 
                                   value="{{ old('name') }}"
                                   class="block w-full pl-10 pr-3 py-3 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 dark:border-red-500 @enderror"
                                   placeholder="Ej: Juan Pérez García"
                                   required
                                   autofocus>
                        </div>
                        @error('name')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div class="md:col-span-2">
                        <label for="email" class="block text-sm font-medium text-slate-900 dark:text-white mb-2">
                            Correo Electrónico <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                                </svg>
                            </div>
                            <input type="email" 
                                   name="email" 
                                   id="email" 
                                   value="{{ old('email') }}"
                                   class="block w-full pl-10 pr-3 py-3 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-500 dark:border-red-500 @enderror"
                                   placeholder="usuario@ejemplo.com"
                                   required>
                        </div>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Este será el nombre de usuario para iniciar sesión</p>
                        @error('email')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Rol -->
                    <div class="md:col-span-2">
                        <label for="rol" class="block text-sm font-medium text-slate-900 dark:text-white mb-2">
                            Rol del Usuario <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                                </svg>
                            </div>
                            <select name="rol" 
                                    id="rol" 
                                    class="block w-full pl-10 pr-3 py-3 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('rol') border-red-500 dark:border-red-500 @enderror"
                                    required>
                                <option value="">-- Seleccione un rol --</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->name }}" {{ old('rol') === $role->name ? 'selected' : '' }}>
                                        {{ $role->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('rol')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        <div class="mt-2 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                            <p class="text-xs text-blue-800 dark:text-blue-200">
                                <strong>Admin:</strong> Acceso total al sistema. 
                                <strong>Cajero:</strong> Realizar ventas. 
                                <strong>Inventario:</strong> Gestionar compras y stock.
                            </p>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- Configurar Contraseña -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-green-50 to-white dark:from-gray-800 dark:to-gray-800/50">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-green-100 dark:bg-green-900/30 rounded-lg">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Configurar Contraseña</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Credenciales de acceso al sistema</p>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <!-- Contraseña -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-900 dark:text-white mb-2">
                            Contraseña <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                                </svg>
                            </div>
                            <input type="password" 
                                   name="password" 
                                   id="password" 
                                   class="block w-full pl-10 pr-3 py-3 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('password') border-red-500 dark:border-red-500 @enderror"
                                   placeholder="••••••••"
                                   required>
                        </div>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Mínimo 8 caracteres</p>
                        @error('password')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Confirmar Contraseña -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-slate-900 dark:text-white mb-2">
                            Confirmar Contraseña <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <input type="password" 
                                   name="password_confirmation" 
                                   id="password_confirmation" 
                                   class="block w-full pl-10 pr-3 py-3 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="••••••••"
                                   required>
                        </div>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Debe coincidir con la contraseña</p>
                    </div>

                </div>
            </div>
        </div>

        <!-- Botones de Acción -->
        <div class="flex items-center justify-between sticky bottom-0 bg-white dark:bg-gray-800 p-4 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700">
            <a href="{{ route('usuarios.index') }}" 
               class="px-6 py-3 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-slate-700 dark:text-slate-300 font-semibold rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors duration-200">
                Cancelar
            </a>
            <button type="submit" 
                    class="inline-flex items-center px-8 py-3 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-semibold rounded-lg shadow-sm transition-all duration-200 hover:shadow-md hover:scale-105">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Crear Usuario
            </button>
        </div>

    </form>
</div>
@endsection