{{-- resources/views/usuarios/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Usuarios')

@section('header')
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Gestión de Usuarios
        </h2>
        @can('crear usuarios')
        <a href="{{ route('usuarios.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Nuevo Usuario
        </a>
        @endcan
    </div>
@endsection

@section('content')
<div class="bg-white overflow-hidden shadow-sm rounded-lg">
    <!-- Filtros -->
    <div class="p-6 border-b border-gray-200">
        <form method="GET" action="{{ route('usuarios.index') }}" class="flex gap-4">
            <div class="flex-1">
                <input type="text" name="buscar" placeholder="Buscar por nombre o email..." 
                       value="{{ request('buscar') }}"
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div class="w-48">
                <select name="rol" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Todos los roles</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->name }}" {{ request('rol') === $role->name ? 'selected' : '' }}>
                            {{ $role->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Filtrar
            </button>
            @if(request()->hasAny(['buscar', 'rol']))
                <a href="{{ route('usuarios.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                    Limpiar
                </a>
            @endif
        </form>
    </div>

    <!-- Tabla -->
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rol</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Registrado</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($usuarios as $usuario)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        {{ $usuario->id }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10">
                                <div class="h-10 w-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold">
                                    {{ strtoupper(substr($usuario->name, 0, 2)) }}
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">{{ $usuario->name }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $usuario->email }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @foreach($usuario->roles as $role)
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                @if($role->name === 'Admin') bg-purple-100 text-purple-800
                                @elseif($role->name === 'Cajero') bg-green-100 text-green-800
                                @else bg-blue-100 text-blue-800
                                @endif">
                                {{ $role->name }}
                            </span>
                        @endforeach
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $usuario->created_at->format('d/m/Y') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex justify-end space-x-2">
                            @can('ver usuarios')
                            <a href="{{ route('usuarios.show', $usuario) }}" class="text-blue-600 hover:text-blue-900" title="Ver">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </a>
                            @endcan

                            @can('editar usuarios')
                            <a href="{{ route('usuarios.edit', $usuario) }}" class="text-indigo-600 hover:text-indigo-900" title="Editar">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </a>
                            @endcan

                            @can('desactivar usuarios')
                            @if($usuario->id !== auth()->id())
                            <form action="{{ route('usuarios.destroy', $usuario) }}" method="POST" class="inline" 
                                  onsubmit="return confirm('¿Está seguro de desactivar este usuario?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900" title="Desactivar">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </form>
                            @endif
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                        No hay usuarios registrados
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <div class="px-6 py-4 border-t border-gray-200">
        {{ $usuarios->links() }}
    </div>
</div>
@endsection

{{-- resources/views/usuarios/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Nuevo Usuario')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Nuevo Usuario
    </h2>
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
        <div class="p-6">
            <form action="{{ route('usuarios.store') }}" method="POST">
                @csrf

                <!-- Nombre -->
                <div class="mb-4">
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
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700">
                        Email <span class="text-red-500">*</span>
                    </label>
                    <input type="email" name="email" id="email" 
                           value="{{ old('email') }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('email') border-red-500 @enderror"
                           required>
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Rol -->
                <div class="mb-4">
                    <label for="rol" class="block text-sm font-medium text-gray-700">
                        Rol <span class="text-red-500">*</span>
                    </label>
                    <select name="rol" id="rol" 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('rol') border-red-500 @enderror"
                            required>
                        <option value="">Seleccione un rol</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}" {{ old('rol') === $role->name ? 'selected' : '' }}>
                                {{ $role->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('rol')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

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
                    <p class="mt-1 text-sm text-gray-500">Mínimo 8 caracteres</p>
                </div>

                <!-- Confirmar Contraseña -->
                <div class="mb-6">
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                        Confirmar Contraseña <span class="text-red-500">*</span>
                    </label>
                    <input type="password" name="password_confirmation" id="password_confirmation" 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                           required>
                </div>

                <!-- Botones -->
                <div class="flex items-center justify-end space-x-2">
                    <a href="{{ route('usuarios.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                        Cancelar
                    </a>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Crear Usuario
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

{{-- resources/views/usuarios/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Editar Usuario')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Editar Usuario: {{ $usuario->name }}
    </h2>
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
        <div class="p-6">
            <form action="{{ route('usuarios.update', $usuario) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Nombre -->
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700">
                        Nombre Completo <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" id="name" 
                           value="{{ old('name', $usuario->name) }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('name') border-red-500 @enderror"
                           required>
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700">
                        Email <span class="text-red-500">*</span>
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
                <div class="mb-4">
                    <label for="rol" class="block text-sm font-medium text-gray-700">
                        Rol <span class="text-red-500">*</span>
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
                </div>

                <!-- Cambiar Contraseña (Opcional) -->
                <div class="mb-4 p-4 bg-gray-50 rounded-lg">
                    <h4 class="text-sm font-medium text-gray-700 mb-3">Cambiar Contraseña (Opcional)</h4>
                    
                    <div class="mb-3">
                        <label for="password" class="block text-sm font-medium text-gray-700">
                            Nueva Contraseña
                        </label>
                        <input type="password" name="password" id="password" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('password') border-red-500 @enderror">
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500">Dejar en blanco para no cambiar</p>
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                            Confirmar Nueva Contraseña
                        </label>
                        <input type="password" name="password_confirmation" id="password_confirmation" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>

                <!-- Botones -->
                <div class="flex items-center justify-end space-x-2">
                    <a href="{{ route('usuarios.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                        Cancelar
                    </a>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Actualizar Usuario
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

{{-- resources/views/usuarios/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Detalle de Usuario')

@section('header')
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Usuario: {{ $usuario->name }}
        </h2>
        <div class="flex space-x-2">
            @can('editar usuarios')
            <a href="{{ route('usuarios.edit', $usuario) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Editar
            </a>
            @endcan
            <a href="{{ route('usuarios.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                Volver
            </a>
        </div>
    </div>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Información del Usuario -->
    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Información del Usuario</h3>
        </div>
        <div class="p-6">
            <div class="flex items-start space-x-6">
                <!-- Avatar -->
                <div class="flex-shrink-0">
                    <div class="h-24 w-24 rounded-full bg-blue-500 flex items-center justify-center text-white text-3xl font-bold">
                        {{ strtoupper(substr($usuario->name, 0, 2)) }}
                    </div>
                </div>
                
                <!-- Detalles -->
                <div class="flex-1">
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">ID</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $usuario->id }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Rol</dt>
                            <dd class="mt-1">
                                @foreach($usuario->roles as $role)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        @if($role->name === 'Admin') bg-purple-100 text-purple-800
                                        @elseif($role->name === 'Cajero') bg-green-100 text-green-800
                                        @else bg-blue-100 text-blue-800
                                        @endif">
                                        {{ $role->name }}
                                    </span>
                                @endforeach
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Nombre Completo</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $usuario->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Email</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $usuario->email }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Registrado</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $usuario->created_at->format('d/m/Y H:i') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Última Actualización</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $usuario->updated_at->format('d/m/Y H:i') }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Permisos del Rol -->
    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Permisos del Rol</h3>
        </div>
        <div class="p-6">
            @if($usuario->roles->isNotEmpty())
                @php
                    $permisos = $usuario->getAllPermissions()->groupBy(function($permiso) {
                        return explode(' ', $permiso->name)[1] ?? 'Otros';
                    });
                @endphp
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($permisos as $modulo => $permisosModulo)
                        <div class="border border-gray-200 rounded-lg p-4">
                            <h4 class="font-semibold text-gray-900 mb-2 capitalize">{{ $modulo }}</h4>
                            <ul class="space-y-1">
                                @foreach($permisosModulo as $permiso)
                                    <li class="flex items-center text-sm text-gray-600">
                                        <svg class="w-4 h-4 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                        {{ $permiso->name }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500">Este usuario no tiene roles asignados</p>
            @endif
        </div>
    </div>
</div>
@endsection