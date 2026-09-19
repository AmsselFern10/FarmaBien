@extends('layouts.app')
@section('title', 'Nuevo Usuario - FarmaBien')
@section('content')
<div class="max-w-2xl mx-auto space-y-5">
    {{-- Breadcrumb --}}
    <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('usuarios.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Usuarios</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-800 dark:text-slate-200 font-semibold">Nuevo Usuario</span>
    </nav>

    {{-- Header --}}
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">Nuevo Usuario</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Crea una cuenta y asigna su rol de acceso.</p>
        </div>
        <a href="{{ route('usuarios.index') }}" class="px-3.5 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-xl transition">&larr; Volver</a>
    </div>

    <form method="POST" action="{{ route('usuarios.store') }}" class="space-y-4">
        @csrf

        {{-- Card: Datos personales --}}
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="flex items-center gap-2 px-5 py-3.5 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30">
                <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200">Datos del Usuario</h3>
            </div>
            <div class="p-5 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Nombre completo <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" required autocomplete="name"
                            class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-800/80 border @error('name') border-rose-400 @else border-slate-200 dark:border-slate-700 @enderror rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition"
                            placeholder="Ej: Juan Perez">
                        @error('name')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Correo electronico <span class="text-rose-500">*</span></label>
                        <input type="email" name="email" value="{{ old('email') }}" required autocomplete="email"
                            class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-800/80 border @error('email') border-rose-400 @else border-slate-200 dark:border-slate-700 @enderror rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition"
                            placeholder="usuario@ejemplo.com">
                        @error('email')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Contrasena <span class="text-rose-500">*</span></label>
                        <input type="password" name="password" required autocomplete="new-password"
                            class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-800/80 border @error('password') border-rose-400 @else border-slate-200 dark:border-slate-700 @enderror rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition"
                            placeholder="Min. 8 caracteres">
                        @error('password')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Confirmar contrasena <span class="text-rose-500">*</span></label>
                        <input type="password" name="password_confirmation" required autocomplete="new-password"
                            class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition"
                            placeholder="Repite la contrasena">
                    </div>
                </div>
            </div>
        </div>

        {{-- Card: Rol y Estado --}}
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="flex items-center gap-2 px-5 py-3.5 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30">
                <svg class="w-4 h-4 text-indigo-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200">Permisos y Acceso</h3>
            </div>
            <div class="p-5 space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Rol de acceso <span class="text-rose-500">*</span></label>
                    <select name="role" required class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-800/80 border @error('role') border-rose-400 @else border-slate-200 dark:border-slate-700 @enderror rounded-xl text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition">
                        <option value="">-- Selecciona un rol --</option>
                        @foreach($roles as $rol)
                        <option value="{{ $rol->name }}" {{ old('role') === $rol->name ? 'selected' : '' }}>{{ $rol->name }}</option>
                        @endforeach
                    </select>
                    @error('role')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
                </div>
                <div class="flex items-center gap-3">
                    <input type="hidden" name="active" value="0">
                    <input type="checkbox" name="active" id="active" value="1" {{ old('active', '1') ? 'checked' : '' }}
                        class="w-4 h-4 rounded text-emerald-600 border-slate-300 focus:ring-emerald-500">
                    <label for="active" class="text-sm font-semibold text-slate-700 dark:text-slate-300">Cuenta activa</label>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="flex items-center justify-between gap-3">
            <a href="{{ route('usuarios.index') }}" class="px-4 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-xl transition">Cancelar</a>
            <button type="submit" class="inline-flex items-center space-x-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-sm font-semibold rounded-xl shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>Crear Usuario</span>
            </button>
        </div>
    </form>
</div>
@endsection
