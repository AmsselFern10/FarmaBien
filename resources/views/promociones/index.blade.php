@extends('layouts.app')

@section('title', 'Promociones y Descuentos - FarmaBien')

@section('content')
<div class="space-y-5" x-data="{ fullWidth: false }">
    <!-- Breadcrumb -->
    <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-800 dark:text-slate-200 font-semibold">Promociones & Descuentos</span>
    </nav>

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <span>Promociones y Descuentos</span>
                <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                    {{ $stats['vigentes'] }} activas y vigentes
                </span>
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Campañas promocionales automáticas vinculadas a Catálogos y Punto de Venta (POS).
            </p>
        </div>
        <div class="flex items-center space-x-2">
            <!-- Botón Modo Full Width -->
            <button @click="fullWidth = !fullWidth; $dispatch('toggle-full-width', { full: fullWidth })" 
                    type="button" 
                    class="p-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition"
                    :title="fullWidth ? 'Modo estándar' : 'Modo pantalla completa'">
                <svg x-show="!fullWidth" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                <svg x-show="fullWidth" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 9L4 4m0 0h4M4 4v4m11 0l5-5m0 0h-4m4 0v4M9 15l-5 5m0 0h4m-4 0v-4m11 0l5 5m0 0h-4m4 0v-4"/></svg>
            </button>

            @can('crear promociones')
            <a href="{{ route('promociones.create') }}" 
               class="inline-flex items-center space-x-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-semibold rounded-xl shadow-xs transition shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Nueva Promoción</span>
            </a>
            @endcan
        </div>
    </div>

    <!-- Quick Stats Cards Row -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Total Campañas</p>
                <p class="text-xl font-bold text-slate-900 dark:text-white mt-0.5">{{ $stats['total'] }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">En Vigor (Hoy)</p>
                <p class="text-xl font-bold text-emerald-600 dark:text-emerald-400 mt-0.5">{{ $stats['vigentes'] }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Programadas</p>
                <p class="text-xl font-bold text-indigo-600 dark:text-indigo-400 mt-0.5">{{ $stats['programadas'] }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Expiradas / Histórico</p>
                <p class="text-xl font-bold text-slate-400 dark:text-slate-500 mt-0.5">{{ $stats['vencidas'] }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-400 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
    </div>

    <!-- Search & Advanced Filter Bar -->
    <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs">
        <form method="GET" action="{{ route('promociones.index') }}" class="flex flex-col md:flex-row items-center gap-3">
            <!-- Search Input -->
            <div class="relative flex-1 w-full">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" 
                       name="buscar" 
                       value="{{ request('buscar') }}" 
                       placeholder="Buscar por nombre de campaña, medicamento, categoría o laboratorio..." 
                       class="w-full pl-10 pr-4 py-2 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
            </div>

            <!-- Tipo Filter -->
            <div class="w-full md:w-44 shrink-0">
                <select name="tipo" 
                        onchange="this.form.submit()" 
                        class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                    <option value="">Tipo: Todos</option>
                    <option value="porcentaje" {{ request('tipo') === 'porcentaje' ? 'selected' : '' }}>Porcentaje (%)</option>
                    <option value="monto_fijo" {{ request('tipo') === 'monto_fijo' ? 'selected' : '' }}>Monto Fijo ($)</option>
                    <option value="2x1" {{ request('tipo') === '2x1' ? 'selected' : '' }}>Combo 2x1</option>
                    <option value="3x2" {{ request('tipo') === '3x2' ? 'selected' : '' }}>Combo 3x2</option>
                </select>
            </div>

            <!-- Alcance Filter -->
            <div class="w-full md:w-44 shrink-0">
                <select name="alcance" 
                        onchange="this.form.submit()" 
                        class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                    <option value="">Alcance: Todos</option>
                    <option value="producto" {{ request('alcance') === 'producto' ? 'selected' : '' }}>Por Producto</option>
                    <option value="categoria" {{ request('alcance') === 'categoria' ? 'selected' : '' }}>Por Categoría</option>
                    <option value="laboratorio" {{ request('alcance') === 'laboratorio' ? 'selected' : '' }}>Por Laboratorio</option>
                    <option value="general" {{ request('alcance') === 'general' ? 'selected' : '' }}>Catálogo Completo</option>
                </select>
            </div>

            <!-- Estado Filter -->
            <div class="w-full md:w-44 shrink-0">
                <select name="estado" 
                        onchange="this.form.submit()" 
                        class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                    <option value="">Estado: Todos</option>
                    <option value="vigentes" {{ request('estado') === 'vigentes' ? 'selected' : '' }}>Vigentes y Activas</option>
                    <option value="programadas" {{ request('estado') === 'programadas' ? 'selected' : '' }}>Programadas</option>
                    <option value="vencidas" {{ request('estado') === 'vencidas' ? 'selected' : '' }}>Vencidas</option>
                    <option value="inactivas" {{ request('estado') === 'inactivas' ? 'selected' : '' }}>Inactivas</option>
                </select>
            </div>

            @if(request()->anyFilled(['buscar', 'tipo', 'alcance', 'estado']))
            <a href="{{ route('promociones.index') }}" 
               class="px-3 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-400 text-xs font-medium rounded-xl transition shrink-0 flex items-center space-x-1"
               title="Limpiar filtros">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                <span>Limpiar</span>
            </a>
            @endif
        </form>
    </div>

    <!-- Table Card -->
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/75 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 font-semibold uppercase tracking-wider">
                        <th class="py-3 px-4">Campaña / Nombre</th>
                        <th class="py-3 px-4">Tipo & Beneficio</th>
                        <th class="py-3 px-4">Alcance / Destino</th>
                        <th class="py-3 px-4">Vigencia</th>
                        <th class="py-3 px-4 text-center">Unidades / Límite</th>
                        <th class="py-3 px-4 text-center">Estado</th>
                        <th class="py-3 px-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                    @forelse($promociones as $promo)
                    @php
                        $isVigente = $promo->esVigente();
                        $isProgramada = $promo->activo && $promo->fecha_inicio > now();
                        $isExpirada = $promo->fecha_fin < now();
                    @endphp
                    <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition">
                        <td class="py-3.5 px-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 rounded-lg bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center font-bold text-xs shrink-0">
                                    {{ $promo->badge_texto }}
                                </div>
                                <div class="min-w-0">
                                    <a href="{{ route('promociones.show', $promo) }}" class="font-bold text-slate-900 dark:text-white hover:text-emerald-600 dark:hover:text-emerald-400 transition block truncate">
                                        {{ $promo->nombre }}
                                    </a>
                                    @if($promo->descripcion)
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate max-w-xs">{{ $promo->descripcion }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold
                                @if($promo->tipo === 'porcentaje') bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800
                                @elseif($promo->tipo === 'monto_fijo') bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800
                                @else bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 @endif">
                                {{ strtoupper($promo->tipo) }}: {{ $promo->badge_texto }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="text-xs">
                                <span class="font-semibold text-slate-800 dark:text-slate-200 block">
                                    {{ ucfirst($promo->alcance) }}
                                </span>
                                <span class="text-[11px] text-slate-500 dark:text-slate-400">
                                    @if($promo->alcance === 'producto' && $promo->producto)
                                        {{ $promo->producto->nombre }}
                                    @elseif($promo->alcance === 'categoria' && $promo->categoria)
                                        {{ $promo->categoria->nombre }}
                                    @elseif($promo->alcance === 'laboratorio' && $promo->laboratorio)
                                        {{ $promo->laboratorio->nombre }}
                                    @elseif($promo->alcance === 'general')
                                        Todo el inventario
                                    @else
                                        -
                                    @endif
                                </span>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            <div class="text-[11px]">
                                <p class="text-slate-800 dark:text-slate-200 font-medium">
                                    Desde: {{ $promo->fecha_inicio->format('d/m/Y H:i') }}
                                </p>
                                <p class="text-slate-500 dark:text-slate-400">
                                    Hasta: {{ $promo->fecha_fin->format('d/m/Y H:i') }}
                                </p>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 text-center whitespace-nowrap">
                            <div class="text-[11px]">
                                <span class="text-slate-800 dark:text-slate-200 font-bold">
                                    {{ $promo->stock_consumido }}
                                </span>
                                <span class="text-slate-400">/</span>
                                <span class="text-slate-500">{{ $promo->stock_limite ? $promo->stock_limite . ' máx' : 'Ilimitado' }}</span>
                            </div>
                            @if($promo->min_unidades > 1)
                            <span class="text-[10px] text-indigo-600 dark:text-indigo-400 font-semibold block">Mín. {{ $promo->min_unidades }} unids</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-center whitespace-nowrap">
                            @if(!$promo->activo)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                                    Inactiva
                                </span>
                            @elseif($isExpirada)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                    Expirada
                                </span>
                            @elseif($isProgramada)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                                    Programada
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 animate-pulse">
                                    ● En Vigor
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right whitespace-nowrap">
                            <div class="flex items-center justify-end space-x-1">
                                <a href="{{ route('promociones.show', $promo) }}" 
                                   class="p-1.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition"
                                   title="Ver detalles">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>

                                @can('editar promociones')
                                <a href="{{ route('promociones.edit', $promo) }}" 
                                   class="p-1.5 text-indigo-500 hover:text-indigo-700 dark:hover:text-indigo-300 hover:bg-indigo-50 dark:hover:bg-indigo-950/50 rounded-lg transition"
                                   title="Editar promoción">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>

                                <form method="POST" action="{{ route('promociones.toggle-activo', $promo) }}" class="inline">
                                    @csrf
                                    <button type="submit" 
                                            class="p-1.5 {{ $promo->activo ? 'text-amber-500 hover:text-amber-700 hover:bg-amber-50 dark:hover:bg-amber-950/50' : 'text-emerald-500 hover:text-emerald-700 hover:bg-emerald-50 dark:hover:bg-emerald-950/50' }} rounded-lg transition"
                                            title="{{ $promo->activo ? 'Pausar/Desactivar' : 'Activar' }}">
                                        @if($promo->activo)
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        @else
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        @endif
                                    </button>
                                </form>
                                @endcan

                                @can('desactivar promociones')
                                <form method="POST" action="{{ route('promociones.destroy', $promo) }}" class="inline" onsubmit="return confirm('¿Seguro que deseas eliminar esta promoción?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="p-1.5 text-rose-400 hover:text-rose-600 dark:hover:text-rose-300 hover:bg-rose-50 dark:hover:bg-rose-950/50 rounded-lg transition"
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
                        <td colspan="7" class="py-8 text-center text-slate-400 dark:text-slate-500">
                            <div class="flex flex-col items-center justify-center space-y-2">
                                <svg class="w-8 h-8 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                <p class="text-xs">No se encontraron promociones con los filtros seleccionados.</p>
                                @can('crear promociones')
                                <a href="{{ route('promociones.create') }}" class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 hover:underline">Crear primera promoción &rarr;</a>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($promociones->hasPages())
        <div class="p-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
            {{ $promociones->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
