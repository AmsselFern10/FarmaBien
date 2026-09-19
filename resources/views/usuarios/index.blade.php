@extends('layouts.app')
@section('title', 'Usuarios del Sistema - FarmaBien')
@section('content')
<div class="space-y-5">
    {{-- Breadcrumb --}}
    <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-800 dark:text-slate-200 font-semibold">Usuarios</span>
    </nav>

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">Gestion de Usuarios</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Administra usuarios del sistema y sus roles de acceso.</p>
        </div>
        @can('crear usuarios')
        <a href="{{ route('usuarios.create') }}"
           class="inline-flex items-center space-x-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-semibold rounded-xl shadow-sm transition shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Nuevo Usuario</span>
        </a>
        @endcan
    </div>

    @if(session('success'))
    <div class="px-4 py-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-xs text-emerald-700 dark:text-emerald-300 flex items-center gap-2">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="px-4 py-3 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-xs text-rose-700 dark:text-rose-300 flex items-center gap-2">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        {{ session('error') }}
    </div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm">
            <p class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">Total Usuarios</p>
            <p class="text-xl font-bold text-slate-900 dark:text-white mt-1">{{ $usuarios->total() }}</p>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm">
            <p class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">Activos</p>
            <p class="text-xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">{{ $totalActivos ?? $usuarios->where('active',true)->count() }}</p>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm">
            <p class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">En esta pagina</p>
            <p class="text-xl font-bold text-slate-900 dark:text-white mt-1">{{ $usuarios->count() }}</p>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm">
            <p class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">Roles definidos</p>
            <p class="text-xl font-bold text-indigo-600 dark:text-indigo-400 mt-1">{{ $totalRoles ?? '—' }}</p>
        </div>
    </div>

    {{-- Filtros --}}
    <form method="GET" action="{{ route('usuarios.index') }}" class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm p-4">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="sm:col-span-2">
                <label class="block text-[10px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Buscar usuario</label>
                <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Nombre o correo electrónico..."
                    class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl transition">Buscar</button>
                @if(request('buscar'))
                <a href="{{ route('usuarios.index') }}" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-xl transition">Limpiar</a>
                @endif
            </div>
        </div>
    </form>

    {{-- Tabla --}}
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="text-left px-5 py-3 font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Usuario</th>
                        <th class="text-left px-5 py-3 font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Correo</th>
                        <th class="text-left px-5 py-3 font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Rol</th>
                        <th class="text-center px-5 py-3 font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Estado</th>
                        <th class="text-left px-5 py-3 font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Ultimo acceso</th>
                        <th class="text-right px-5 py-3 font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($usuarios as $usuario)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition group">
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 flex items-center justify-center text-xs font-bold shrink-0 uppercase">
                                    {{ substr($usuario->name, 0, 1) }}
                                </div>
                                <div>
                                    <p class="font-semibold text-slate-900 dark:text-white">{{ $usuario->name }}</p>
                                    @if($usuario->id === auth()->id())
                                    <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-semibold">(tu cuenta)</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3.5 text-slate-500 dark:text-slate-400">{{ $usuario->email }}</td>
                        <td class="px-5 py-3.5">
                            @foreach($usuario->roles as $rol)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">{{ $rol->name }}</span>
                            @endforeach
                            @if($usuario->roles->isEmpty())<span class="text-slate-400 italic">Sin rol</span>@endif
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            @if($usuario->active)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">Activo</span>
                            @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-700">Inactivo</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-slate-400">
                            @if($usuario->loginLogs->first())
                            <span title="{{ $usuario->loginLogs->first()->created_at->format('d/m/Y H:i') }}">
                                {{ $usuario->loginLogs->first()->created_at->diffForHumans() }}
                            </span>
                            @else
                            <span class="italic">Nunca</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <div class="inline-flex items-center justify-end space-x-1">
                                @can('ver usuarios')
                                <a href="{{ route('usuarios.show', $usuario) }}" class="inline-flex items-center justify-center w-7 h-7 text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-800/80 rounded-lg transition" title="Ver ficha">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                @endcan
                                @can('editar usuarios')
                                <a href="{{ route('usuarios.edit', $usuario) }}" class="inline-flex items-center justify-center w-7 h-7 text-slate-400 hover:text-emerald-500 hover:bg-slate-100 dark:hover:bg-slate-800/80 rounded-lg transition" title="Editar">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                @endcan
                                @can('desactivar usuarios')
                                @if($usuario->id !== auth()->id())
                                <form method="POST" action="{{ route('usuarios.destroy', $usuario) }}" class="inline-flex m-0 p-0">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center justify-center w-7 h-7 rounded-lg transition {{ $usuario->active ? 'text-slate-400 hover:text-amber-500 hover:bg-slate-100 dark:hover:bg-slate-800/80' : 'text-slate-400 hover:text-emerald-500 hover:bg-slate-100 dark:hover:bg-slate-800/80' }}" title="{{ $usuario->active ? 'Desactivar' : 'Activar' }}">
                                        @if($usuario->active)
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                        @else
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        @endif
                                    </button>
                                </form>
                                @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-12 text-center text-slate-400">
                            <svg class="w-8 h-8 mx-auto mb-2 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            No se encontraron usuarios.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($usuarios->hasPages())
        <div class="px-5 py-3.5 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900">
            {{ $usuarios->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
