@extends('layouts.app')

@section('title', 'Proveedores y Droguerías - FarmaBien')

@section('content')
<div class="space-y-5">
    <!-- Breadcrumb -->
    <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-800 dark:text-slate-200 font-semibold">Proveedores</span>
    </nav>

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <span>Directorio de Proveedores</span>
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Distribuidores mayoristas, droguerías autorizadas y gestión de compras de abastecimiento.
            </p>
        </div>
        @can('crear proveedores')
        <a href="{{ route('proveedores.create') }}" 
           class="inline-flex items-center space-x-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-semibold rounded-xl shadow-sm transition shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Nuevo Proveedor</span>
        </a>
        @endcan
    </div>

    <!-- Quick Stats Cards Row -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Total Proveedores</p>
                <p class="text-xl font-bold text-slate-900 dark:text-white mt-0.5">{{ $proveedores->total() }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-2m-4-1v8m0 0l3-3m-3 3L9 8m-5 5h2.586a1 1 0 01.707.293l2.414 2.414a1 1 0 00.707.293h3.172a1 1 0 00.707-.293l2.414-2.414a1 1 0 01.707-.293H20"/></svg>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">En esta Página</p>
                <p class="text-xl font-bold text-emerald-600 dark:text-emerald-400 mt-0.5">{{ $proveedores->count() }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Página Actual</p>
                <p class="text-xl font-bold text-slate-800 dark:text-slate-200 mt-0.5">{{ $proveedores->currentPage() }} / {{ $proveedores->lastPage() }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Acceso Rápido</p>
                <a href="{{ route('compras.index') }}" class="text-xs font-bold text-emerald-600 dark:text-emerald-400 hover:underline mt-0.5 block">Historial Compras &rarr;</a>
            </div>
            <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
            </div>
        </div>
    </div>

    <!-- Search & Advanced Filter Bar -->
    <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm">
        <form method="GET" action="{{ route('proveedores.index') }}" class="flex flex-col md:flex-row items-center gap-3">
            <!-- Search Input -->
            <div class="relative flex-1 w-full">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" 
                       name="buscar" 
                       value="{{ request('buscar') }}" 
                       placeholder="Buscar por RUC (11 dígitos), razón social, contacto o dirección..." 
                       class="w-full pl-10 pr-4 py-2 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
            </div>

            <!-- Estado Filter -->
            <div class="w-full md:w-48 shrink-0">
                <select name="estado" 
                        onchange="this.form.submit()" 
                        class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                    <option value="">Estado: Todos</option>
                    <option value="activos" {{ request('estado') === 'activos' ? 'selected' : '' }}>Solo Activos</option>
                    <option value="inactivos" {{ request('estado') === 'inactivos' ? 'selected' : '' }}>Solo Inactivos</option>
                </select>
            </div>

            <!-- Buttons -->
            <div class="flex items-center space-x-2 w-full md:w-auto shrink-0">
                <button type="submit" class="w-full md:w-auto px-4 py-2 bg-slate-900 hover:bg-slate-800 dark:bg-slate-700 dark:hover:bg-slate-600 text-white text-xs font-semibold rounded-xl transition">
                    Filtrar
                </button>
                @if(request('buscar') || request('estado'))
                <a href="{{ route('proveedores.index') }}" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 text-xs font-semibold rounded-xl transition">
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
                        <th class="px-5 py-3.5">Proveedor / Razón Social</th>
                        <th class="px-5 py-3.5">RUC</th>
                        <th class="px-5 py-3.5">Contacto / Teléfono</th>
                        <th class="px-5 py-3.5">Correo</th>
                        <th class="px-5 py-3.5 text-center">Órdenes Compra</th>
                        <th class="px-5 py-3.5 text-center">Estado</th>
                        <th class="px-5 py-3.5 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-700 dark:text-slate-300">
                    @forelse($proveedores as $prov)
                    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition">
                        <td class="px-5 py-3.5 font-semibold text-slate-900 dark:text-white">
                            {{ $prov->nombre }}
                            @if($prov->direccion)
                            <span class="block text-[11px] font-normal text-slate-400 truncate max-w-xs">{{ $prov->direccion }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 font-mono font-medium text-slate-700 dark:text-slate-300">
                            {{ $prov->ruc }}
                        </td>
                        <td class="px-5 py-3.5 text-slate-500 dark:text-slate-400">
                            {{ $prov->contacto ? $prov->contacto . ' (' . ($prov->telefono ?? 'S/T') . ')' : ($prov->telefono ?? '—') }}
                        </td>
                        <td class="px-5 py-3.5 text-slate-500 dark:text-slate-400">
                            {{ $prov->email ?? '—' }}
                        </td>
                        <td class="px-5 py-3.5 text-center font-medium">
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                {{ $prov->compras_count }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            @if($prov->activo)
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
                                @can('ver proveedores')
                                <a href="{{ route('proveedores.show', $prov) }}" 
                                   class="inline-flex items-center justify-center w-7 h-7 text-slate-400 hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/80 rounded-lg transition" 
                                   title="Ver Ficha e Historial">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                @endcan

                                @can('editar proveedores')
                                <a href="{{ route('proveedores.edit', $prov) }}" 
                                   class="inline-flex items-center justify-center w-7 h-7 text-slate-400 hover:text-emerald-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 rounded-lg transition" 
                                   title="Editar Proveedor">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                @endcan

                                @can('desactivar proveedores')
                                <form method="POST" action="{{ route('proveedores.destroy', $prov) }}" class="inline-flex items-center m-0 p-0" onsubmit="return confirm('¿Deseas cambiar el estado de este proveedor?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="inline-flex items-center justify-center w-7 h-7 text-slate-400 hover:text-rose-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 rounded-lg transition" 
                                            title="{{ $prov->activo ? 'Desactivar' : 'Activar' }}">
                                        @if($prov->activo)
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
                            No se encontraron proveedores que coincidan con los filtros aplicados.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($proveedores->hasPages())
        <div class="px-5 py-3.5 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900">
            {{ $proveedores->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
