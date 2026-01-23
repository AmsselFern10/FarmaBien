@extends('layouts.app')

@section('title', 'Nuevo Usuario')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Crear Nuevo Usuario
    </h2>
@endsection

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
        <div class="p-6">
            <form action="{{ route('usuarios.store') }}" method="POST">
                @csrf

                <div class="space-y-6">
                    <!-- Nombre -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">
                            Nombre Completo <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" id="name" 
                               value="{{ old('name') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('name') border-red-500 @enderror"
                               required autofocus>
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">
                            Correo Electrónico <span class="text-red-500">*</span>
                        </label>
                        <input type="email" name="email" id="email" 
                               value="{{ old('email') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('email') border-red-500 @enderror"
                               required>
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500">Este será el nombre de usuario para iniciar sesión</p>
                    </div>

                    <!-- Rol -->
                    <div>
                        <label for="rol" class="block text-sm font-medium text-gray-700">
                            Rol del Usuario <span class="text-red-500">*</span>
                        </label>
                        <select name="rol" id="rol" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('rol') border-red-500 @enderror"
                                required>
                            <option value="">-- Seleccione un rol --</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}" {{ old('rol') === $role->name ? 'selected' : '' }}>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('rol')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500">
                            <strong>Admin:</strong> Acceso total al sistema. 
                            <strong>Cajero:</strong> Realizar ventas. 
                            <strong>Inventario:</strong> Gestionar compras y stock.
                        </p>
                    </div>

                    <div class="border-t border-gray-200 pt-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Configurar Contraseña</h3>
                        
                        <!-- Contraseña -->
                        <div class="mb-4">
                            <label for="password" class="block text-sm font-medium text-gray-700">
                                Contraseña <span class="text-red-500">*</span>
                            </label>
                            <input type="password" name="password" id="password" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('password') border-red-500 @enderror"
                                   required>
                            @error('password')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500">Debe tener al menos 8 caracteres</p>
                        </div>

                        <!-- Confirmar Contraseña -->
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                                Confirmar Contraseña <span class="text-red-500">*</span>
                            </label>
                            <input type="password" name="password_confirmation" id="password_confirmation" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                   required>
                        </div>
                    </div>
                </div>

                <!-- Botones -->
                <div class="mt-6 flex items-center justify-end space-x-3">
                    <a href="{{ route('usuarios.index') }}" 
                       class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Cancelar
                    </a>
                    <button type="submit" 
                            class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="w-5 h-5 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Crear Usuario
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection