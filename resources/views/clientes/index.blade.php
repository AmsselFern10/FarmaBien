@extends('layouts.app')

@section('title', 'Clientes y Pacientes - FarmaBien')

@section('content')
<div class="space-y-5">
    <!-- Breadcrumb -->
    <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-800 dark:text-slate-200 font-semibold">Clientes / Pacientes</span>
    </nav>

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <span>Padrón de Clientes y Pacientes</span>
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Directorio de pacientes, historial de dispensación y vinculación de recetas médicas.
            </p>
        </div>
        @can('crear clientes')
        <a href="{{ route('clientes.create') }}" 
           class="inline-flex items-center space-x-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-semibold rounded-xl shadow-sm transition shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Nuevo Cliente / Paciente</span>
        </a>
        @endcan
    </div>

    <!-- Quick Stats Cards Row -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Total Pacientes</p>
                <p class="text-xl font-bold text-slate-900 dark:text-white mt-0.5">{{ $clientes->total() }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">En esta Página</p>
                <p class="text-xl font-bold text-emerald-600 dark:text-emerald-400 mt-0.5">{{ $clientes->count() }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Página Actual</p>
                <p class="text-xl font-bold text-slate-800 dark:text-slate-200 mt-0.5">{{ $clientes->currentPage() }} / {{ $clientes->lastPage() }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Recetas Médicas</p>
                <a href="{{ route('recetas.index') }}" class="text-xs font-bold text-amber-600 dark:text-amber-400 hover:underline mt-0.5 block">Ver Recetas &rarr;</a>
            </div>
            <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
        </div>
    </div>

    <!-- Search & Advanced Filter Bar -->
    <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm">
        <form method="GET" action="{{ route('clientes.index') }}" class="flex flex-col md:flex-row items-center gap-3">
            <!-- Search Input -->
            <div class="relative flex-1 w-full">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" 
                       name="buscar" 
                       value="{{ request('buscar') }}" 
                       placeholder="Buscar por DNI/RUC, nombre completo, teléfono o dirección..." 
                       class="w-full pl-10 pr-4 py-2 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
            </div>

            <!-- Estado Filter -->
            <div class="w-full md:w-44 shrink-0">
                <select name="estado" 
                        onchange="this.form.submit()" 
                        class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                    <option value="">Estado: Todos</option>
                    <option value="activos" {{ request('estado') === 'activos' ? 'selected' : '' }}>Solo Activos</option>
                    <option value="inactivos" {{ request('estado') === 'inactivos' ? 'selected' : '' }}>Solo Inactivos</option>
                </select>
            </div>

            <!-- Con Recetas Filter -->
            <div class="w-full md:w-44 shrink-0">
                <select name="con_recetas" 
                        onchange="this.form.submit()" 
                        class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                    <option value="">Recetas: Todas</option>
                    <option value="si" {{ request('con_recetas') === 'si' ? 'selected' : '' }}>Con Recetas</option>
                    <option value="no" {{ request('con_recetas') === 'no' ? 'selected' : '' }}>Sin Recetas</option>
                </select>
            </div>

            <!-- Buttons -->
            <div class="flex items-center space-x-2 w-full md:w-auto shrink-0">
                <button type="submit" class="w-full md:w-auto px-4 py-2 bg-slate-900 hover:bg-slate-800 dark:bg-slate-700 dark:hover:bg-slate-600 text-white text-xs font-semibold rounded-xl transition">
                    Filtrar
                </button>
                @if(request('buscar') || request('estado') || request('con_recetas'))
                <a href="{{ route('clientes.index') }}" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 text-xs font-semibold rounded-xl transition">
                    Limpiar
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">Cliente / Paciente</th>
                        <th class="px-5 py-3.5">Documento (DNI/RUC)</th>
                        <th class="px-5 py-3.5">Contacto / Teléfono</th>
                        <th class="px-5 py-3.5 text-center">Compras</th>
                        <th class="px-5 py-3.5 text-center">Recetas</th>
                        <th class="px-5 py-3.5 text-center">Estado</th>
                        <th class="px-5 py-3.5 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-700 dark:text-slate-300">
                    @forelse($clientes as $cli)
                    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition">
                        <td class="px-5 py-3.5 font-semibold text-slate-900 dark:text-white">
                            {{ $cli->nombre }}
                            @if($cli->email)
                            <span class="block text-[11px] font-normal text-slate-400">{{ $cli->email }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 font-mono font-medium text-slate-700 dark:text-slate-300">
                            {{ $cli->documento ?? '—' }}
                        </td>
                        <td class="px-5 py-3.5 text-slate-500 dark:text-slate-400">
                            {{ $cli->telefono ?? '—' }}
                        </td>
                        <td class="px-5 py-3.5 text-center font-medium">
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                {{ $cli->ventas_count }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-center font-medium">
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                {{ $cli->recetas_count }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            @if($cli->activo)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                Activo
                            </span>
                            @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                                Inactivo
                            </span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-right whitespace-nowrap">
                            <div class="inline-flex items-center justify-end space-x-1">
                                @can('ver clientes')
                                <a href="{{ route('clientes.show', $cli) }}" 
                                   class="inline-flex items-center justify-center w-7 h-7 text-slate-400 hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/80 rounded-lg transition" 
                                   title="Ver Ficha y Registro Clínico">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                @endcan

                                @can('editar clientes')
                                <a href="{{ route('clientes.edit', $cli) }}" 
                                   class="inline-flex items-center justify-center w-7 h-7 text-slate-400 hover:text-emerald-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 rounded-lg transition" 
                                   title="Editar Cliente">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                @endcan

                                @can('desactivar clientes')
                                <form method="POST" action="{{ route('clientes.destroy', $cli) }}" class="inline-flex items-center m-0 p-0" onsubmit="return confirm('¿Deseas cambiar el estado de este cliente?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="inline-flex items-center justify-center w-7 h-7 text-slate-400 hover:text-rose-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 rounded-lg transition" 
                                            title="{{ $cli->activo ? 'Desactivar' : 'Activar' }}">
                                        @if($cli->activo)
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                        @else
                                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        @endif
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center text-slate-400 text-xs">
                            No se encontraron clientes que coincidan con los filtros aplicados.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($clientes->hasPages())
        <div class="px-5 py-3.5 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900">
            {{ $clientes->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
