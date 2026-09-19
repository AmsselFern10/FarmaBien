@extends('layouts.app')

@section('title', 'Presentaciones de Producto - FarmaBien')

@section('content')
<div class="space-y-5">
    <!-- Breadcrumb -->
    <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-800 dark:text-slate-200 font-semibold">Presentaciones</span>
    </nav>

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <span>Presentaciones de Producto</span>
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Gestiona las presentaciones comerciales (caja, blister, frasco, ampolla, etc.) de cada medicamento.
            </p>
        </div>
        @can('crear productos')
        <a href="{{ route('presentaciones.create') }}"
           class="inline-flex items-center space-x-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-semibold rounded-xl shadow-sm transition shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Nueva Presentación</span>
        </a>
        @endcan
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Total</p>
                <p class="text-xl font-bold text-slate-900 dark:text-white mt-0.5">{{ $presentaciones->total() }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">En esta Página</p>
                <p class="text-xl font-bold text-emerald-600 dark:text-emerald-400 mt-0.5">{{ $presentaciones->count() }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Página Actual</p>
                <p class="text-xl font-bold text-slate-800 dark:text-slate-200 mt-0.5">{{ $presentaciones->currentPage() }} / {{ $presentaciones->lastPage() }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Acceso Rápido</p>
                <a href="{{ route('productos.index') }}" class="text-xs font-bold text-emerald-600 dark:text-emerald-400 hover:underline mt-0.5 block">Ver Medicamentos &rarr;</a>
            </div>
            <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18"/></svg>
            </div>
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm">
        <form method="GET" action="{{ route('presentaciones.index') }}" class="flex flex-col md:flex-row items-center gap-3">
            <div class="relative flex-1 w-full">
                <label for="filtro_buscar_presentacion" class="sr-only">Buscar presentaciones</label>
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" id="filtro_buscar_presentacion" name="buscar" value="{{ request('buscar') }}"
                       placeholder="Buscar por nombre, código de barras o medicamento..."
                       class="w-full pl-10 pr-4 py-2 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
            </div>
            <div class="w-full md:w-56 shrink-0">
                <label for="filtro_producto_presentacion" class="sr-only">Filtrar por medicamento</label>
                <select id="filtro_producto_presentacion" name="producto_id" onchange="this.form.submit()"
                        class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 transition">
                    <option value="">Medicamento: Todos</option>
                    @foreach($productos as $prod)
                    <option value="{{ $prod->id }}" {{ request('producto_id') == $prod->id ? 'selected' : '' }}>{{ $prod->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-full md:w-40 shrink-0">
                <label for="filtro_estado_presentacion" class="sr-only">Filtrar por estado</label>
                <select id="filtro_estado_presentacion" name="estado" onchange="this.form.submit()"
                        class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 transition">
                    <option value="">Estado: Todos</option>
                    <option value="activos" {{ request('estado') === 'activos' ? 'selected' : '' }}>Solo Activas</option>
                    <option value="inactivos" {{ request('estado') === 'inactivos' ? 'selected' : '' }}>Solo Inactivas</option>
                </select>
            </div>
            <div class="flex items-center space-x-2 w-full md:w-auto shrink-0">
                <button type="submit" id="btn-filtrar-presentaciones" aria-label="Aplicar filtros" class="w-full md:w-auto px-4 py-2 bg-slate-900 hover:bg-slate-800 dark:bg-slate-700 dark:hover:bg-slate-600 text-white text-xs font-semibold rounded-xl transition cursor-pointer">Filtrar</button>
                @if(request('buscar') || request('producto_id') || request('estado'))
                <a href="{{ route('presentaciones.index') }}" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 text-xs font-semibold rounded-xl transition">Limpiar</a>
                @endif
            </div>
        </form>
    </div>

    @if(session('success'))
    <div class="px-4 py-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-300 dark:border-emerald-700 text-xs text-emerald-800 dark:text-emerald-300 font-semibold flex items-center space-x-2">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="px-4 py-3 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-300 dark:border-rose-700 text-xs text-rose-800 dark:text-rose-300 font-semibold flex items-center space-x-2">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    <!-- Table -->
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">Presentación</th>
                        <th class="px-5 py-3.5">Medicamento</th>
                        <th class="px-5 py-3.5 text-center">Unidades</th>
                        <th class="px-5 py-3.5 text-right">P. Compra</th>
                        <th class="px-5 py-3.5 text-right">P. Venta</th>
                        <th class="px-5 py-3.5">Cód. Barras</th>
                        <th class="px-5 py-3.5 text-center">Estado</th>
                        <th class="px-5 py-3.5 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-700 dark:text-slate-300">
                    @forelse($presentaciones as $pres)
                    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition">
                        <td class="px-5 py-3.5">
                            <div class="font-semibold text-slate-900 dark:text-white flex items-center gap-2">
                                {{ $pres->nombre }}
                                @if($pres->es_unidad_base)
                                <span class="px-1.5 py-0.5 rounded text-[9px] font-black bg-indigo-100 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300">BASE</span>
                                @endif
                            </div>
                            @if($pres->descripcion)
                            <div class="text-slate-400 text-[10px] mt-0.5">{{ $pres->descripcion }}</div>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-slate-600 dark:text-slate-400">
                            <a href="{{ route('productos.show', $pres->producto) }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition font-medium">
                                {{ $pres->producto->nombre }}
                            </a>
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                x{{ $pres->unidades_por_presentacion }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-right font-mono text-slate-700 dark:text-slate-300">
                            {{ $pres->precio_compra ? '$' . number_format($pres->precio_compra, 2) : '—' }}
                        </td>
                        <td class="px-5 py-3.5 text-right font-mono font-semibold text-emerald-700 dark:text-emerald-400">
                            {{ $pres->precio_venta ? '$' . number_format($pres->precio_venta, 2) : '—' }}
                        </td>
                        <td class="px-5 py-3.5 font-mono text-slate-500 dark:text-slate-400 text-[11px]">
                            {{ $pres->codigo_barras ?? '—' }}
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            @if($pres->activo)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">Activa</span>
                            @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-700">Inactiva</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-right whitespace-nowrap">
                            <div class="inline-flex items-center justify-end space-x-1">
                                @can('ver productos')
                                <a href="{{ route('presentaciones.show', $pres) }}"
                                   class="inline-flex items-center justify-center w-7 h-7 text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-800/80 rounded-lg transition"
                                   title="Ver Ficha">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                @endcan
                                @can('editar productos')
                                <a href="{{ route('presentaciones.edit', $pres) }}"
                                   class="inline-flex items-center justify-center w-7 h-7 text-slate-400 hover:text-emerald-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 rounded-lg transition"
                                   title="Editar">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                {{-- Toggle Activo/Inactivo --}}
                                <form method="POST" action="{{ route('presentaciones.toggle-activo', $pres) }}" class="inline-flex m-0 p-0">
                                    @csrf
                                    <button type="submit"
                                            class="inline-flex items-center justify-center w-7 h-7 rounded-lg transition {{ $pres->activo ? 'text-slate-400 hover:text-amber-500 hover:bg-slate-100 dark:hover:bg-slate-800/80' : 'text-slate-400 hover:text-emerald-500 hover:bg-slate-100 dark:hover:bg-slate-800/80' }}"
                                            title="{{ $pres->activo ? 'Desactivar' : 'Activar' }}">
                                        @if($pres->activo)
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                        @else
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        @endif
                                    </button>
                                </form>
                                @endcan
                                @can('eliminar productos')
                                <form method="POST" action="{{ route('presentaciones.destroy', $pres) }}" class="inline-flex m-0 p-0"
                                      onsubmit="return confirm('¿Eliminar la presentación {{ addslashes($pres->nombre) }}? Esta acción no se puede deshacer.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="inline-flex items-center justify-center w-7 h-7 text-slate-400 hover:text-rose-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 rounded-lg transition"
                                            title="Eliminar">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-5 py-12 text-center text-slate-400 text-xs">
                            No se encontraron presentaciones que coincidan con los filtros aplicados.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($presentaciones->hasPages())
        <div class="px-5 py-3.5 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900">
            {{ $presentaciones->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
