@extends('layouts.app')
@section('title', 'Ficha de Usuario - FarmaBien')
@section('content')
<div class="max-w-6xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400 mb-1">
                <a href="{{ route('usuarios.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Usuarios</a>
                <span>/</span>
                <span class="text-slate-800 dark:text-slate-300 font-medium">{{ $usuario->name }}</span>
            </div>
            <div class="flex items-center space-x-3">
                <h1 class="text-xl font-bold text-slate-900 dark:text-white">{{ $usuario->name }}</h1>
                @if($usuario->active)
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">Activo</span>
                @else
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-700">Inactivo</span>
                @endif
            </div>
        </div>
        <div class="flex items-center space-x-2 shrink-0">
            @can('editar usuarios')
            <a href="{{ route('usuarios.edit', $usuario) }}"
               class="inline-flex items-center space-x-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-semibold rounded-xl transition shadow-sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Editar</span>
            </a>
            @endcan
            <a href="{{ route('usuarios.index') }}"
               class="px-3.5 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-xl transition">
                &larr; Volver
            </a>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm">
            <p class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">Rol</p>
            <p class="text-base font-bold text-indigo-600 dark:text-indigo-400 mt-1">{{ $usuario->roles->first()?->name ?? 'Sin rol' }}</p>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm">
            <p class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">Ventas registradas</p>
            <p class="text-base font-bold text-emerald-600 dark:text-emerald-400 mt-1">{{ $usuario->ventas()->count() }}</p>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm">
            <p class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">Accesos totales</p>
            <p class="text-base font-bold text-slate-900 dark:text-white mt-1">{{ $loginLogs->where('tipo','login')->count() }}</p>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm">
            <p class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">Ultimo acceso</p>
            <p class="text-sm font-bold text-slate-900 dark:text-white mt-1">{{ $loginLogs->where('tipo','login')->first()?->created_at?->diffForHumans() ?? 'Nunca' }}</p>
        </div>
    </div>

    {{-- Info + Log Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Info --}}
        <div class="space-y-5">
            <div class="bg-white dark:bg-slate-900 rounded-xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm">
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-4 pb-3 border-b border-slate-200 dark:border-slate-800">Informacion</h3>
                <dl class="space-y-3 text-xs">
                    <div><dt class="font-medium text-slate-400 uppercase tracking-wider mb-0.5">Correo</dt><dd class="font-semibold text-slate-800 dark:text-slate-200">{{ $usuario->email }}</dd></div>
                    <div><dt class="font-medium text-slate-400 uppercase tracking-wider mb-0.5">Rol actual</dt>
                        <dd class="mt-0.5">
                            @foreach($usuario->roles as $rol)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">{{ $rol->name }}</span>
                            @endforeach
                        </dd>
                    </div>
                    <div><dt class="font-medium text-slate-400 uppercase tracking-wider mb-0.5">Cuenta creada</dt><dd class="font-semibold text-slate-800 dark:text-slate-200">{{ $usuario->created_at->format('d/m/Y H:i') }}</dd></div>
                    <div><dt class="font-medium text-slate-400 uppercase tracking-wider mb-0.5">Ultima modificacion</dt><dd class="font-semibold text-slate-800 dark:text-slate-200">{{ $usuario->updated_at->format('d/m/Y H:i') }}</dd></div>
                </dl>
            </div>
        </div>

        {{-- Log de accesos --}}
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="flex items-center justify-between px-5 py-3.5 border-b border-slate-200 dark:border-slate-800">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        Historial de Accesos
                    </h3>
                    {{-- Filtro por fecha --}}
                    <form method="GET" action="{{ route('usuarios.show', $usuario) }}" class="flex items-center gap-2">
                        <input type="date" name="desde" value="{{ request('desde') }}"
                            class="px-2 py-1 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 transition">
                        <span class="text-xs text-slate-400">a</span>
                        <input type="date" name="hasta" value="{{ request('hasta') }}"
                            class="px-2 py-1 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 transition">
                        <button type="submit" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white text-[10px] font-semibold rounded-lg transition">Filtrar</button>
                        @if(request('desde') || request('hasta'))
                        <a href="{{ route('usuarios.show', $usuario) }}" class="px-2.5 py-1 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-600 dark:text-slate-400 text-[10px] font-semibold rounded-lg transition">X</a>
                        @endif
                    </form>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead class="bg-slate-50 dark:bg-slate-800/50">
                            <tr>
                                <th class="text-left px-5 py-2.5 font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Tipo</th>
                                <th class="text-left px-5 py-2.5 font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Fecha y hora</th>
                                <th class="text-left px-5 py-2.5 font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">IP</th>
                                <th class="text-left px-5 py-2.5 font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider hidden lg:table-cell">Dispositivo</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($loginLogs as $log)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition">
                                <td class="px-5 py-3">
                                    @if($log->tipo === 'login')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                                        Login
                                    </span>
                                    @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                        Logout
                                    </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-slate-700 dark:text-slate-300 font-mono">
                                    {{ $log->created_at->format('d/m/Y H:i:s') }}
                                    <span class="text-slate-400 ml-1">({{ $log->created_at->diffForHumans() }})</span>
                                </td>
                                <td class="px-5 py-3 font-mono text-slate-500 dark:text-slate-400">{{ $log->ip ?? '—' }}</td>
                                <td class="px-5 py-3 text-slate-400 hidden lg:table-cell truncate max-w-xs" title="{{ $log->user_agent }}">
                                    {{ $log->user_agent ? Str::limit($log->user_agent, 40) : '—' }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-5 py-10 text-center text-slate-400">
                                    @if(request('desde') || request('hasta'))
                                    No hay registros en el rango de fechas seleccionado.
                                    @else
                                    Este usuario aun no ha iniciado sesion.
                                    @endif
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($loginLogs instanceof \Illuminate\Pagination\LengthAwarePaginator && $loginLogs->hasPages())
                <div class="px-5 py-3.5 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900">
                    {{ $loginLogs->appends(request()->query())->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>

</div>
@endsection
