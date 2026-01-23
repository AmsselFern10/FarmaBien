@extends('layouts.app')

@section('title', 'Editar Usuario')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Editar Usuario: {{ $usuario->name }}
    </h2>
@endsection

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
        <div class="p-6">
            <form action="{{ route('usuarios.update', $usuario) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="space-y-6">
                    <!-- Nombre -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">
                            Nombre Completo <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" id="name" 
                               value="{{ old('name', $usuario->name) }}"
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
                               value="{{ old('email', $usuario->email) }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('email') border-red-500 @enderror"
                               required>
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Rol -->
                    <div>
                        <label for="rol" class="block text-sm font-medium text-gray-700">
                            Rol del Usuario <span class="text-red-500">*</span>
                        </label>
                        <select name="rol" id="rol" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('rol') border-red-500 @enderror"
                                required>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}" 
                                    {{ old('rol', $usuario->roles->first()?->name) === $role->name ? 'selected' : '' }}>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('rol')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500">Rol actual: <strong>{{ $usuario->roles->first()?->name }}</strong></p>
                    </div>

                    <!-- Cambiar Contraseña (Opcional) -->
                    <div class="border-t border-gray-200 pt-6">
                        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-blue-700">
                                        <strong>Cambiar Contraseña (Opcional)</strong>
                                        <br>
                                        Deja estos campos en blanco si NO deseas cambiar la contraseña del usuario.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <!-- Nueva Contraseña -->
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700">
                                    Nueva Contraseña
                                </label>
                                <input type="password" name="password" id="password" 
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('password') border-red-500 @enderror">
                                @error('password')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-sm text-gray-500">Mínimo 8 caracteres (solo si deseas cambiarla)</p>
                            </div>

                            <!-- Confirmar Nueva Contraseña -->
                            <div>
                                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                                    Confirmar Nueva Contraseña
                                </label>
                                <input type="password" name="password_confirmation" id="password_confirmation" 
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>
                    </div>

                    <!-- Información adicional -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Información del Registro</h4>
                        <div class="grid grid-cols-2 gap-4 text-sm text-gray-600">
                            <div>
                                <span class="font-medium">Creado:</span> {{ $usuario->created_at->format('d/m/Y H:i') }}
                            </div>
                            <div>
                                <span class="font-medium">Última actualización:</span> {{ $usuario->updated_at->format('d/m/Y H:i') }}
                            </div>
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Actualizar Usuario
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection