@extends('layouts.app')

@section('title', 'Catálogo de Medicamentos - FarmaBien')

@section('content')
<div x-data="{
    viewMode: localStorage.getItem('productsViewMode') || 'grid',
    setViewMode(mode) {
        this.viewMode = mode;
        localStorage.setItem('productsViewMode', mode);
    }
}" class="space-y-5">
    
    <!-- Breadcrumbs -->
    <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-800 dark:text-slate-200 font-semibold">Medicamentos y Productos</span>
    </nav>

    <!-- Header & Action Buttons -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <span>Catálogo de Medicamentos</span>
                <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                    {{ $productos->total() }} registrados
                </span>
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Gestión integral de especialidades farmacéuticas, control de recetas, búsqueda semántica y vista dual interactiva.
            </p>
        </div>
        
        <div class="flex items-center space-x-2 shrink-0">
            <!-- Botón Lupa Inteligente (IA) -->
            <button type="button" 
                    onclick="abrirModalLupaIA()" 
                    class="inline-flex items-center space-x-2 px-3.5 py-2 rounded-xl bg-gradient-to-r from-violet-600 via-purple-600 to-indigo-600 hover:from-violet-700 hover:to-indigo-700 text-white text-xs font-semibold shadow-xs transition hover:shadow-md active:scale-98">
                <span class="text-sm">✨</span>
                <span class="hidden sm:inline">Lupa Inteligente con IA</span>
                <span class="sm:hidden">Lupa IA</span>
            </button>

            @can('crear productos')
            <a href="{{ route('productos.create') }}" 
               class="inline-flex items-center space-x-1.5 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-semibold rounded-xl shadow-xs transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Nuevo Fármaco</span>
            </a>
            @endcan
        </div>
    </div>

    <!-- Quick Stats Cards Row -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="bg-white dark:bg-slate-900 rounded-xl p-3.5 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Total Medicamentos</p>
                <p class="text-lg font-bold text-slate-900 dark:text-white mt-0.5">{{ $productos->total() }}</p>
            </div>
            <div class="w-9 h-9 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-xl p-3.5 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400">En esta Vista</p>
                <p class="text-lg font-bold text-emerald-600 dark:text-emerald-400 mt-0.5">{{ $productos->count() }}</p>
            </div>
            <div class="w-9 h-9 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-xl p-3.5 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Categorías Disponibles</p>
                <p class="text-lg font-bold text-slate-800 dark:text-slate-200 mt-0.5">{{ $categorias->count() }}</p>
            </div>
            <div class="w-9 h-9 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-xl p-3.5 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Control de Lotes</p>
                <a href="{{ route('inventario.lotes') }}" class="text-xs font-bold text-emerald-600 dark:text-emerald-400 hover:underline mt-0.5 block">Ver Vencimientos &rarr;</a>
            </div>
            <div class="w-9 h-9 rounded-lg bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
    </div>

    <!-- Search & Advanced Filter Bar + View Mode Toggle -->
    <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs space-y-3">
        <form method="GET" action="{{ route('productos.index') }}" class="space-y-3">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-2.5">
                <!-- Search Input (Supports Natural Language / Symptoms) -->
                <div class="relative md:col-span-4">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" 
                           name="buscar" 
                           value="{{ request('buscar') }}" 
                           placeholder="Buscar por fármaco, síntoma (ej: dolor de cabeza)..." 
                           class="w-full pl-9 pr-3 py-2 bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                </div>

                <!-- Categoría Filter -->
                <div class="md:col-span-3">
                    <select name="categoria_id" 
                            onchange="this.form.submit()" 
                            class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                        <option value="">Categoría: Todas</option>
                        @foreach($categorias as $cat)
                        <option value="{{ $cat->id }}" {{ request('categoria_id') == $cat->id ? 'selected' : '' }}>{{ $cat->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Laboratorio Filter -->
                <div class="md:col-span-3">
                    <select name="laboratorio_id" 
                            onchange="this.form.submit()" 
                            class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                        <option value="">Laboratorio: Todos</option>
                        @foreach($laboratorios as $lab)
                        <option value="{{ $lab->id }}" {{ request('laboratorio_id') == $lab->id ? 'selected' : '' }}>{{ $lab->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Control / Receta Filter -->
                <div class="md:col-span-2">
                    <select name="tipo_control" 
                            onchange="this.form.submit()" 
                            class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                        <option value="">Venta: Todas</option>
                        <option value="venta_libre" {{ request('tipo_control') == 'venta_libre' ? 'selected' : '' }}>Venta Libre</option>
                        <option value="receta_medica" {{ request('tipo_control') == 'receta_medica' ? 'selected' : '' }}>Con Receta</option>
                        <option value="receta_retenida" {{ request('tipo_control') == 'receta_retenida' ? 'selected' : '' }}>Receta Retenida</option>
                    </select>
                </div>
            </div>

            <!-- Fila Inferior: Checkbox bajo stock + Botones + Toggle de Vista -->
            <div class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-slate-100 dark:border-slate-800 text-xs">
                <div class="flex items-center space-x-4">
                    <label class="inline-flex items-center space-x-2 cursor-pointer select-none text-slate-700 dark:text-slate-300">
                        <input type="checkbox" 
                               name="bajo_stock" 
                               value="1" 
                               {{ request('bajo_stock') ? 'checked' : '' }} 
                               onchange="this.form.submit()" 
                               class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 w-4 h-4">
                        <span class="font-medium">Solo productos en stock bajo</span>
                    </label>

                    @if(request()->hasAny(['buscar', 'categoria_id', 'laboratorio_id', 'tipo_control', 'bajo_stock']))
                    <a href="{{ route('productos.index') }}" class="text-rose-600 dark:text-rose-400 hover:underline flex items-center space-x-1 font-medium">
                        <span>Limpiar filtros</span>
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </a>
                    @endif
                </div>

                <!-- Selector de Modo de Vista (Dual View: Lista / Cuadrícula) -->
                <div class="flex items-center space-x-2">
                    <span class="text-slate-500 dark:text-slate-400 font-medium">Visualización:</span>
                    <div class="inline-flex rounded-xl p-1 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                        <!-- Botón Vista Cuadrícula (Cards) -->
                        <button type="button" 
                                @click="setViewMode('grid')"
                                :class="viewMode === 'grid' ? 'bg-white dark:bg-slate-700 text-emerald-600 dark:text-emerald-400 shadow-xs font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200'"
                                class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-lg text-xs transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                            <span>Cuadrícula</span>
                        </button>
                        <!-- Botón Vista Lista (Tabla) -->
                        <button type="button" 
                                @click="setViewMode('table')"
                                :class="viewMode === 'table' ? 'bg-white dark:bg-slate-700 text-emerald-600 dark:text-emerald-400 shadow-xs font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200'"
                                class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-lg text-xs transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                            <span>Lista / Tabla</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- ======================================================== -->
    <!-- MODO 1: VISTA CUADRÍCULA (Cards con Imágenes y Badges)    -->
    <!-- ======================================================== -->
    <div x-show="viewMode === 'grid'" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="space-y-4">
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @forelse($productos as $producto)
            @php
                $stock = $producto->stock_total ?? 0;
                $minimo = $producto->stock_minimo ?? 0;
                $isAgotado = $stock <= 0;
                $isBajo = !$isAgotado && ($stock <= $minimo);
            @endphp
            <div class="group bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs hover:shadow-md hover:border-emerald-500/50 dark:hover:border-emerald-500/40 transition-all duration-200 flex flex-col overflow-hidden {{ !$producto->activo ? 'opacity-60 grayscale-30' : '' }}">
                
                <!-- Imagen del Fármaco y Badges Flotantes -->
                <div class="relative h-44 bg-gradient-to-br from-slate-100 to-slate-200/70 dark:from-slate-800 dark:to-slate-800/80 flex items-center justify-center p-4 overflow-hidden">
                    @if($producto->imagen)
                        <img src="{{ asset('storage/' . $producto->imagen) }}" 
                             alt="{{ $producto->nombre }}" 
                             class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-300">
                    @else
                        <div class="w-16 h-16 rounded-2xl bg-emerald-500/10 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                        </div>
                    @endif

                    <!-- Badges Top Left: Receta -->
                    <div class="absolute top-2.5 left-2.5 flex flex-col gap-1">
                        @if($producto->requiere_receta)
                        <span class="inline-flex items-center space-x-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/90 text-white backdrop-blur-xs shadow-xs">
                            <span>Rx</span>
                            <span>Receta</span>
                        </span>
                        @endif
                    </div>

                    <!-- Badges Top Right: Estado e Info IA -->
                    <div class="absolute top-2.5 right-2.5 flex items-center space-x-1.5">
                        <button type="button" 
                                data-id="{{ $producto->id }}"
                                data-name="{{ $producto->nombre }}"
                                onclick="abrirModalProductoIA(this.dataset.id, this.dataset.name)" 
                                title="Consultar Ficha IA (Posología, Contraindicaciones)"
                                class="w-7 h-7 rounded-full bg-white/90 dark:bg-slate-900/90 text-violet-600 dark:text-violet-400 hover:bg-violet-600 hover:text-white dark:hover:bg-violet-600 dark:hover:text-white flex items-center justify-center text-xs shadow-xs transition backdrop-blur-xs">
                            ✨
                        </button>

                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $producto->activo ? 'bg-emerald-500/90 text-white' : 'bg-rose-500/90 text-white' }} shadow-xs">
                            {{ $producto->activo ? 'Activo' : 'Inactivo' }}
                        </span>
                    </div>

                    <!-- Bottom Stock Alert Pill -->
                    @if($isAgotado)
                    <div class="absolute bottom-2 inset-x-2 text-center py-0.5 rounded-md bg-rose-600/90 text-white text-[10px] font-bold shadow-xs">
                        ⚠️ Agotado (0 unid.)
                    </div>
                    @elseif($isBajo)
                    <div class="absolute bottom-2 inset-x-2 text-center py-0.5 rounded-md bg-amber-500/90 text-white text-[10px] font-bold shadow-xs">
                        ⚠️ Stock Bajo ({{ $stock }} unid.)
                    </div>
                    @endif
                </div>

                <!-- Contenido de la Tarjeta -->
                <div class="p-3.5 flex-1 flex flex-col justify-between space-y-3">
                    <div>
                        <!-- Categoría y Laboratorio -->
                        <div class="flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400 mb-1">
                            <span class="truncate max-w-[120px] font-medium text-emerald-600 dark:text-emerald-400">
                                {{ $producto->categoria?->nombre ?? 'General' }}
                            </span>
                            <span class="truncate max-w-[100px] text-slate-400 dark:text-slate-500">
                                {{ $producto->laboratorio?->nombre ?? 'Genérico' }}
                            </span>
                        </div>

                        <!-- Título del Medicamento -->
                        <h3 class="font-bold text-slate-900 dark:text-white text-sm line-clamp-1 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition" title="{{ $producto->nombre }}">
                            {{ $producto->nombre }}
                        </h3>

                        <!-- Principio Activo y Concentración -->
                        <p class="text-xs text-slate-500 dark:text-slate-400 truncate mt-0.5">
                            {{ $producto->principio_activo ?: 'Sin fórmula registrada' }} 
                            @if($producto->concentracion) • <span class="font-medium">{{ $producto->concentracion }}</span> @endif
                        </p>

                        @if($producto->codigo_barra)
                        <p class="text-[10px] font-mono text-slate-400 mt-1 flex items-center gap-1">
                            <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                            <span>{{ $producto->codigo_barra }}</span>
                        </p>
                        @endif
                    </div>

                    <!-- Precio y Stock -->
                    <div class="pt-2 border-t border-slate-100 dark:border-slate-800/80 flex items-baseline justify-between">
                        <div>
                            <span class="text-[10px] text-slate-400 uppercase font-semibold">Precio Venta</span>
                            <div class="text-base font-extrabold text-slate-900 dark:text-white">
                                S/ {{ number_format($producto->precio_venta, 2) }}
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] text-slate-400 uppercase font-semibold">Disponible</span>
                            <div class="text-xs font-bold {{ $isAgotado ? 'text-rose-600 dark:text-rose-400' : ($isBajo ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400') }}">
                                {{ $stock }} unid.
                            </div>
                        </div>
                    </div>

                    <!-- Botones de Acción de la Tarjeta -->
                    <div class="pt-2 flex items-center justify-between gap-1.5 border-t border-slate-100 dark:border-slate-800">
                        <a href="{{ route('productos.show', $producto) }}" 
                           class="flex-1 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold text-center transition flex items-center justify-center space-x-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <span>Ver Ficha</span>
                        </a>

                        @can('editar productos')
                        <a href="{{ route('productos.edit', $producto) }}" 
                           title="Editar Fármaco"
                           class="w-7 h-7 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center transition shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        </a>
                        @endcan

                        @can('desactivar productos')
                        <form action="{{ route('productos.destroy', $producto) }}" method="POST" class="inline-flex m-0 p-0" onsubmit="return confirm('¿Confirmas {{ $producto->activo ? 'desactivar' : 'activar' }} este producto?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    title="{{ $producto->activo ? 'Desactivar' : 'Activar' }}" 
                                    class="w-7 h-7 rounded-lg {{ $producto->activo ? 'bg-rose-50 dark:bg-rose-950/60 hover:bg-rose-100 dark:hover:bg-rose-900/60 text-rose-600 dark:text-rose-400' : 'bg-emerald-50 dark:bg-emerald-950/60 hover:bg-emerald-100 text-emerald-600' }} flex items-center justify-center transition shrink-0">
                                @if($producto->activo)
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                @else
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                @endif
                            </button>
                        </form>
                        @endcan
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-full bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-12 text-center">
                <div class="w-16 h-16 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200">No se encontraron medicamentos</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">
                    Prueba modificando los filtros o utiliza la <span class="font-semibold text-violet-600 dark:text-violet-400">Lupa Inteligente con IA</span> para buscar por síntomas clínicos.
                </p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- MODO 2: VISTA TABLA / LISTA (Compacta con Acciones)      -->
    <!-- ======================================================== -->
    <div x-show="viewMode === 'table'" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/75 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 font-semibold uppercase tracking-wider text-[11px]">
                        <th class="py-3 px-4">Medicamento / Principio Activo</th>
                        <th class="py-3 px-4">Categoría & Lab</th>
                        <th class="py-3 px-4">Código Barra</th>
                        <th class="py-3 px-4 text-center">Control / Receta</th>
                        <th class="py-3 px-4 text-right">Stock Actual</th>
                        <th class="py-3 px-4 text-right">Precio Venta</th>
                        <th class="py-3 px-4 text-center">Estado</th>
                        <th class="py-3 px-4 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    @forelse($productos as $producto)
                    @php
                        $stock = $producto->stock_total ?? 0;
                        $minimo = $producto->stock_minimo ?? 0;
                        $isAgotado = $stock <= 0;
                        $isBajo = !$isAgotado && ($stock <= $minimo);
                    @endphp
                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition {{ !$producto->activo ? 'opacity-60 bg-slate-50/30 dark:bg-slate-950/30' : '' }}">
                        <!-- Medicamento & Foto -->
                        <td class="py-3 px-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-lg bg-slate-100 dark:bg-slate-800 overflow-hidden shrink-0 flex items-center justify-center border border-slate-200 dark:border-slate-700">
                                    @if($producto->imagen)
                                        <img src="{{ asset('storage/' . $producto->imagen) }}" alt="{{ $producto->nombre }}" class="w-full h-full object-cover">
                                    @else
                                        <span class="text-xs font-bold text-slate-400">Rx</span>
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <a href="{{ route('productos.show', $producto) }}" class="font-bold text-slate-900 dark:text-white hover:text-emerald-600 dark:hover:text-emerald-400 transition truncate block">
                                        {{ $producto->nombre }}
                                    </a>
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400 truncate">
                                        {{ $producto->principio_activo ?: 'Fórmula general' }} 
                                        @if($producto->concentracion) ({{ $producto->concentracion }}) @endif
                                    </div>
                                </div>
                            </div>
                        </td>

                        <!-- Categoría & Lab -->
                        <td class="py-3 px-4 whitespace-nowrap">
                            <div class="font-semibold text-slate-800 dark:text-slate-200">{{ $producto->categoria?->nombre ?? 'General' }}</div>
                            <div class="text-[11px] text-slate-400">{{ $producto->laboratorio?->nombre ?? 'N/A' }}</div>
                        </td>

                        <!-- Código Barra -->
                        <td class="py-3 px-4 font-mono text-slate-600 dark:text-slate-300 whitespace-nowrap text-[11px]">
                            {{ $producto->codigo_barra ?: '—' }}
                        </td>

                        <!-- Control / Receta -->
                        <td class="py-3 px-4 text-center whitespace-nowrap">
                            @if($producto->requiere_receta)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                    Con Receta
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">
                                    Venta Libre
                                </span>
                            @endif
                        </td>

                        <!-- Stock Actual -->
                        <td class="py-3 px-4 text-right whitespace-nowrap">
                            @if($isAgotado)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300">
                                    0 agotado
                                </span>
                            @elseif($isBajo)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300">
                                    {{ $stock }} unid. (Bajo)
                                </span>
                            @else
                                <span class="font-bold text-slate-800 dark:text-slate-200">
                                    {{ $stock }} unid.
                                </span>
                            @endif
                        </td>

                        <!-- Precio Venta -->
                        <td class="py-3 px-4 text-right whitespace-nowrap font-bold text-slate-900 dark:text-white">
                            S/ {{ number_format($producto->precio_venta, 2) }}
                        </td>

                        <!-- Estado -->
                        <td class="py-3 px-4 text-center whitespace-nowrap">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $producto->activo ? 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400' }}">
                                {{ $producto->activo ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>

                        <!-- Acciones Cuarteto -->
                        <td class="py-3 px-4 text-center whitespace-nowrap">
                            <div class="inline-flex items-center justify-center space-x-1">
                                <!-- Ver Ficha -->
                                <a href="{{ route('productos.show', $producto) }}" 
                                   title="Ver Ficha Completa" 
                                   class="w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 inline-flex items-center justify-center transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>

                                <!-- Ficha IA -->
                                <button type="button" 
                                        data-id="{{ $producto->id }}"
                                        data-name="{{ $producto->nombre }}"
                                        onclick="abrirModalProductoIA(this.dataset.id, this.dataset.name)" 
                                        title="Consultar Ficha IA" 
                                        class="w-7 h-7 rounded-lg bg-violet-50 dark:bg-violet-950/60 hover:bg-violet-100 dark:hover:bg-violet-900/60 text-violet-600 dark:text-violet-300 inline-flex items-center justify-center transition">
                                    ✨
                                </button>

                                @can('editar productos')
                                <!-- Editar -->
                                <a href="{{ route('productos.edit', $producto) }}" 
                                   title="Editar Fármaco" 
                                   class="w-7 h-7 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 text-emerald-600 dark:text-emerald-400 inline-flex items-center justify-center transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                </a>
                                @endcan

                                @can('desactivar productos')
                                <!-- Desactivar / Activar -->
                                <form action="{{ route('productos.destroy', $producto) }}" method="POST" class="inline-flex m-0 p-0" onsubmit="return confirm('¿Confirmas {{ $producto->activo ? 'desactivar' : 'activar' }} este medicamento?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            title="{{ $producto->activo ? 'Desactivar' : 'Activar' }}" 
                                            class="w-7 h-7 rounded-lg {{ $producto->activo ? 'bg-rose-50 dark:bg-rose-950/60 hover:bg-rose-100 dark:hover:bg-rose-900/60 text-rose-600 dark:text-rose-400' : 'bg-emerald-50 dark:bg-emerald-950/60 hover:bg-emerald-100 text-emerald-600' }} inline-flex items-center justify-center transition">
                                        @if($producto->activo)
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                        @else
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        @endif
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center text-slate-400">
                            No se encontraron productos registrados con los filtros aplicados.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination Links -->
    <div class="mt-4">
        {{ $productos->links() }}
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL: LUPA INTELIGENTE CON IA (Búsqueda Semántica)      -->
<!-- ======================================================== -->
<div id="modalLupaIA" class="hidden fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-950/70 backdrop-blur-xs overflow-y-auto" role="dialog" aria-modal="true" onclick="if(event.target === this) cerrarModalLupaIA()">
    <!-- Modal Panel -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all w-full max-w-2xl border border-slate-200 dark:border-slate-800 my-auto">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 bg-gradient-to-r from-violet-50 via-white to-indigo-50 dark:from-violet-950/30 dark:via-slate-900 dark:to-indigo-950/30 flex items-center justify-between">
            <div class="flex items-center space-x-2.5">
                <div class="w-9 h-9 rounded-xl bg-violet-600 text-white flex items-center justify-center shadow-xs text-base">
                    ✨
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Lupa Inteligente con IA</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Búsqueda semántica por síntomas, indicaciones y lenguaje natural.</p>
                </div>
            </div>
            <button type="button" onclick="cerrarModalLupaIA()" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer">
                ✕
            </button>
        </div>

        <!-- Body -->
        <div class="p-6 space-y-4">
            <div>
                <label for="lupa_q" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                    Describe el síntoma o consulta clínica:
                </label>
                <div class="flex gap-2">
                    <input type="text" 
                           id="lupa_q" 
                           name="lupa_q"
                           placeholder="Ej: algo para dolor de cabeza y fiebre, jarabe para tos seca..." 
                           class="flex-1 px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800/90 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-violet-500 focus:border-violet-500 transition">
                    
                    <button type="button" 
                            id="btn-consultar-lupa-ia"
                            onclick="buscarConLupaIA()" 
                            class="px-4 py-2.5 rounded-xl bg-violet-600 hover:bg-violet-700 active:bg-violet-800 text-white font-semibold text-xs shadow-xs transition flex items-center space-x-1.5 shrink-0 cursor-pointer">
                        <span>Consultar IA</span>
                    </button>
                </div>
                <!-- Ejemplos rápidos -->
                <div class="flex flex-wrap gap-1.5 mt-2">
                    <span class="text-[11px] text-slate-400 font-medium">Sugerencias:</span>
                    <button type="button" onclick="probarSintoma('dolor de cabeza y fiebre')" class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-violet-50 hover:text-violet-600 dark:hover:bg-violet-950/50 text-[11px] transition cursor-pointer">Dolor de cabeza</button>
                    <button type="button" onclick="probarSintoma('tos seca y garganta irritada')" class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-violet-50 hover:text-violet-600 dark:hover:bg-violet-950/50 text-[11px] transition cursor-pointer">Tos y garganta</button>
                    <button type="button" onclick="probarSintoma('acidez estomacal y gastritis')" class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-violet-50 hover:text-violet-600 dark:hover:bg-violet-950/50 text-[11px] transition cursor-pointer">Acidez / Gastritis</button>
                    <button type="button" onclick="probarSintoma('alergia y rinitis')" class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-violet-50 hover:text-violet-600 dark:hover:bg-violet-950/50 text-[11px] transition cursor-pointer">Alergia</button>
                </div>
            </div>

            <!-- Loading State -->
            <div id="lupa_loading" class="hidden py-8 text-center space-y-2">
                <div class="inline-block w-7 h-7 border-2 border-violet-500 border-t-transparent rounded-full animate-spin"></div>
                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Analizando consulta clínica y catálogo...</p>
            </div>

            <!-- Results Container -->
            <div id="lupa_results" class="max-h-80 overflow-y-auto space-y-2.5 pr-1"></div>

            <!-- Error Container -->
            <div id="lupa_error" class="hidden p-3 rounded-xl bg-rose-50 dark:bg-rose-950/50 text-rose-700 dark:text-rose-300 text-xs border border-rose-200 dark:border-rose-800"></div>
        </div>

        <!-- Footer -->
        <div class="px-6 py-3.5 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <button type="button" onclick="usarTextoLupaEnFiltro()" class="text-xs font-semibold text-violet-600 dark:text-violet-400 hover:underline cursor-pointer">
                🔍 Filtrar con este texto en la página principal
            </button>

            <button type="button" onclick="cerrarModalLupaIA()" class="px-3.5 py-1.5 rounded-xl border border-slate-300 dark:border-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer">
                Cerrar
            </button>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL: FICHA CLÍNICA IA DEL MEDICAMENTO                  -->
<!-- ======================================================== -->
<div id="modalProductoIA" class="hidden fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-950/70 backdrop-blur-xs overflow-y-auto" role="dialog" aria-modal="true" onclick="if(event.target === this) cerrarModalProductoIA()">
    <!-- Modal Panel -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all w-full max-w-2xl border border-slate-200 dark:border-slate-800 my-auto">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 bg-gradient-to-r from-emerald-50 via-teal-50 to-white dark:from-emerald-950/30 dark:via-slate-900 dark:to-slate-900 flex items-center justify-between">
            <div class="flex items-center space-x-2.5">
                <div class="w-9 h-9 rounded-xl bg-emerald-600 text-white flex items-center justify-center shadow-xs text-base">
                    💊
                </div>
                <div>
                    <div class="flex items-center space-x-2">
                        <h3 id="modalProductoIA_title" class="text-base font-bold text-slate-900 dark:text-white">Ficha Farmacológica IA</h3>
                        <span id="ia_status_badge" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">Motor Local</span>
                    </div>
                    <p id="modalProductoIA_subtitle" class="text-xs text-slate-500 dark:text-slate-400">Guía asistida de uso clínico, posología, advertencias y recomendaciones farmacéuticas.</p>
                </div>
            </div>
            <button type="button" onclick="cerrarModalProductoIA()" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                ✕
            </button>
        </div>

        <!-- Body -->
        <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto">
            <!-- Loading State -->
            <div id="modalProductoIA_loading" class="py-8 text-center space-y-2">
                <div class="inline-block w-7 h-7 border-2 border-emerald-500 border-t-transparent rounded-full animate-spin"></div>
                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Consultando base farmacológica e IA...</p>
            </div>

            <!-- Structured Content Container -->
            <div id="modalProductoIA_content" class="hidden space-y-3.5">
                <!-- Uso Clínico e Indicaciones -->
                <div class="p-3.5 rounded-xl bg-indigo-50/70 dark:bg-indigo-950/30 border border-indigo-200 dark:border-indigo-800/60">
                    <div class="flex items-center space-x-2 text-xs font-bold text-indigo-800 dark:text-indigo-300 mb-1">
                        <span>🎯</span>
                        <span>Uso Clínico e Indicaciones Terapéuticas</span>
                    </div>
                    <p id="ia_uso_clinico" class="text-xs text-indigo-950 dark:text-indigo-200 leading-relaxed"></p>
                </div>

                <!-- Posología -->
                <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/70 border border-slate-200 dark:border-slate-700">
                    <div class="flex items-center space-x-2 text-xs font-bold text-emerald-700 dark:text-emerald-300 mb-1">
                        <span>⏱️</span>
                        <span>Posología y Modo de Uso Recomendado</span>
                    </div>
                    <p id="ia_posologia" class="text-xs text-slate-700 dark:text-slate-300 leading-relaxed"></p>
                </div>

                <!-- Recomendaciones Farmacéuticas -->
                <div class="p-3.5 rounded-xl bg-teal-50/70 dark:bg-teal-950/30 border border-teal-200 dark:border-teal-800/60">
                    <div class="flex items-center space-x-2 text-xs font-bold text-teal-800 dark:text-teal-300 mb-1">
                        <span>💡</span>
                        <span>Recomendaciones del Farmacéutico al Paciente</span>
                    </div>
                    <p id="ia_recomendaciones" class="text-xs text-teal-950 dark:text-teal-200 leading-relaxed"></p>
                </div>

                <!-- Advertencias -->
                <div class="p-3.5 rounded-xl bg-amber-50/70 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/60">
                    <div class="flex items-center space-x-2 text-xs font-bold text-amber-800 dark:text-amber-300 mb-1">
                        <span>⚠️</span>
                        <span>Advertencias y Efectos Adversos</span>
                    </div>
                    <p id="ia_advertencias" class="text-xs text-amber-900 dark:text-amber-200 leading-relaxed"></p>
                </div>

                <!-- Contraindicaciones -->
                <div class="p-3.5 rounded-xl bg-rose-50/70 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800/60">
                    <div class="flex items-center space-x-2 text-xs font-bold text-rose-700 dark:text-rose-300 mb-1">
                        <span>🚫</span>
                        <span>Contraindicaciones Clínicas e Interacciones</span>
                    </div>
                    <p id="ia_contraindicaciones" class="text-xs text-rose-800 dark:text-rose-200 leading-relaxed"></p>
                </div>

                <!-- Sustitutos Genéricos en Catálogo -->
                <div class="space-y-2 pt-1 border-t border-slate-100 dark:border-slate-800">
                    <div class="flex items-center space-x-2 text-xs font-bold text-slate-800 dark:text-slate-200">
                        <span>🔄</span>
                        <span>Alternativas y Sustitutos en Inventario FarmaBien</span>
                    </div>
                    <div id="ia_sustitutos" class="grid grid-cols-1 sm:grid-cols-2 gap-2"></div>
                </div>
            </div>

            <!-- Error Container -->
            <div id="modalProductoIA_error" class="hidden p-3 rounded-xl bg-rose-50 dark:bg-rose-950/50 text-rose-700 dark:text-rose-300 text-xs border border-rose-200 dark:border-rose-800"></div>
        </div>

        <!-- Footer -->
        <div class="px-6 py-3.5 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs text-slate-400">
            <span>Nota: Orientación educativa. Requiere verificación médica/farmacéutica.</span>
            <button type="button" onclick="cerrarModalProductoIA()" class="px-3.5 py-1.5 rounded-xl bg-slate-200 dark:bg-slate-700 text-slate-800 dark:text-slate-200 font-semibold hover:bg-slate-300 dark:hover:bg-slate-600 transition">
                Entendido
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
const __lupaIaUrl = @json(route('productos.ia.buscar'));
const __productoIaUrlTpl = @json(route('productos.ia.ficha', ['producto' => '__ID__']));

function escapeHtml(str) {
    return String(str ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function csrfToken() {
    const el = document.querySelector('meta[name="csrf-token"]');
    return el ? el.getAttribute('content') : '';
}

/* ================= LUPA INTELIGENTE ================= */
function abrirModalLupaIA() {
    const modal = document.getElementById('modalLupaIA');
    if (!modal) return;
    modal.classList.remove('hidden');
    modal.style.display = 'flex';

    const normal = document.querySelector('input[name="buscar"]');
    const input = document.getElementById('lupa_q');
    if (input && normal) {
        input.value = (normal.value || '').trim();
    }
    setTimeout(() => input?.focus(), 80);
}

function cerrarModalLupaIA() {
    const modal = document.getElementById('modalLupaIA');
    if (!modal) return;
    modal.classList.add('hidden');
    modal.style.display = 'none';
}

function probarSintoma(texto) {
    const input = document.getElementById('lupa_q');
    if (input) {
        input.value = texto;
        buscarConLupaIA();
    }
}

function usarTextoLupaEnFiltro() {
    const q = (document.getElementById('lupa_q')?.value || '').trim();
    const normal = document.querySelector('input[name="buscar"]');
    if (normal) normal.value = q;
    const form = normal?.closest('form');
    if (form) form.submit();
}

let __lupaAbort = null;
function buscarConLupaIA() {
    const q = (document.getElementById('lupa_q')?.value || '').trim();
    if (q.length < 2) return;

    const loading = document.getElementById('lupa_loading');
    const results = document.getElementById('lupa_results');
    const errorBox = document.getElementById('lupa_error');

    if (__lupaAbort) __lupaAbort.abort();
    __lupaAbort = new AbortController();

    if (results) results.innerHTML = '';
    if (errorBox) { errorBox.classList.add('hidden'); errorBox.textContent = ''; }
    if (loading) loading.classList.remove('hidden');

    fetch(__lupaIaUrl, {
        method: 'POST',
        signal: __lupaAbort.signal,
        headers: {
            'X-CSRF-TOKEN': csrfToken(),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ q })
    })
    .then(async (r) => {
        const data = await r.json().catch(() => ({}));
        if (!r.ok || !data.ok) throw new Error(data.message || 'Error consultando IA');
        if (loading) loading.classList.add('hidden');
        renderResultadosLupa(data.results || []);
    })
    .catch((err) => {
        if (err?.name === 'AbortError') return;
        if (loading) loading.classList.add('hidden');
        if (errorBox) {
            errorBox.classList.remove('hidden');
            errorBox.textContent = err.message || 'No se pudo consultar la IA.';
        }
    });
}

function renderResultadosLupa(items) {
    const results = document.getElementById('lupa_results');
    if (!results) return;

    if (!items.length) {
        results.innerHTML = `
            <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800 text-center text-xs text-slate-500">
                No encontramos productos coincidentes con esa descripción. Intenta con otro síntoma o nombre comercial.
            </div>
        `;
        return;
    }

    results.innerHTML = items.map(p => `
        <div class="p-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-850 hover:bg-slate-50 dark:hover:bg-slate-800 transition flex items-start justify-between gap-3">
            <div class="min-w-0">
                <div class="flex items-center space-x-2 flex-wrap">
                    <span class="font-bold text-slate-900 dark:text-white text-xs">${escapeHtml(p.nombre)}</span>
                    <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold ${p.requiere_receta ? 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300' : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300'}">
                        ${p.requiere_receta ? 'Con Receta' : 'Venta Libre'}
                    </span>
                </div>
                <div class="text-[11px] text-slate-500 mt-0.5">
                    ${escapeHtml(p.principio_activo || 'Fórmula')} • ${escapeHtml(p.categoria)} • ${escapeHtml(p.laboratorio)}
                </div>
                ${p.explicacion ? `<div class="text-[11px] text-violet-700 dark:text-violet-300 mt-1 font-medium">${escapeHtml(p.explicacion)}</div>` : ''}
                <div class="mt-1 flex items-center space-x-3 text-xs">
                    <span class="font-bold text-emerald-600 dark:text-emerald-400">S/ ${escapeHtml(p.precio_venta)}</span>
                    <span class="text-slate-500">Stock: ${escapeHtml(p.stock_total)} unid.</span>
                </div>
            </div>
            <div class="flex flex-col space-y-1 shrink-0">
                <a href="${escapeHtml(p.url_show)}" class="px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 text-slate-700 dark:text-slate-200 text-[11px] font-semibold text-center transition">
                    Ver
                </a>
                <button type="button" 
                        data-id="${p.id}" 
                        data-name="${escapeHtml(p.nombre)}" 
                        onclick="abrirModalProductoIA(this.dataset.id, this.dataset.name)" 
                        class="px-2.5 py-1 rounded-lg bg-violet-50 dark:bg-violet-950/60 text-violet-700 dark:text-violet-300 text-[11px] font-semibold hover:bg-violet-100 transition cursor-pointer">
                    Ficha IA
                </button>
            </div>
        </div>
    `).join('');
}

/* ================= FICHA CLÍNICA IA ================= */
function abrirModalProductoIA(id, nombre) {
    const modal = document.getElementById('modalProductoIA');
    if (!modal) return;
    const title = document.getElementById('modalProductoIA_title');
    const subtitle = document.getElementById('modalProductoIA_subtitle');
    const loading = document.getElementById('modalProductoIA_loading');
    const content = document.getElementById('modalProductoIA_content');
    const errorBox = document.getElementById('modalProductoIA_error');

    if (title) title.textContent = `Ficha IA: ${nombre || ''}`;
    if (loading) loading.classList.remove('hidden');
    if (content) content.classList.add('hidden');
    if (errorBox) errorBox.classList.add('hidden');

    modal.classList.remove('hidden');
    modal.style.display = 'flex';

    const url = __productoIaUrlTpl.replace('__ID__', id);
    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken(),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(async (r) => {
        const data = await r.json().catch(() => ({}));
        if (!r.ok || !data.ok) throw new Error(data.message || 'Error obteniendo ficha IA');

        if (loading) loading.classList.add('hidden');
        if (content) content.classList.remove('hidden');

        // Status badge
        const badge = document.getElementById('ia_status_badge');
        if (badge) {
            if (data.fuente_ia === 'api_externa') {
                badge.className = 'px-2 py-0.5 rounded-full text-[10px] font-bold bg-violet-100 dark:bg-violet-950/60 text-violet-700 dark:text-violet-300 border border-violet-200 dark:border-violet-800';
                badge.textContent = '✨ ' + (data.generado_por || 'IA Externa');
            } else {
                badge.className = 'px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800';
                badge.textContent = '🧪 Motor Local';
            }
        }

        const usoEl = document.getElementById('ia_uso_clinico');
        if (usoEl) usoEl.textContent = data.uso_clinico || 'Consulte al profesional de la salud.';
        const posEl = document.getElementById('ia_posologia');
        if (posEl) posEl.textContent = data.posologia || 'Consulte al farmacéutico.';
        const recEl = document.getElementById('ia_recomendaciones');
        if (recEl) recEl.textContent = data.recomendaciones || 'Seguir las pautas indicadas en el empaque del fabricante.';
        const advEl = document.getElementById('ia_advertencias');
        if (advEl) advEl.textContent = data.advertencias || 'Mantener fuera del alcance de niños.';
        const conEl = document.getElementById('ia_contraindicaciones');
        if (conEl) conEl.textContent = data.contraindicaciones || 'Ninguna descrita.';

        const sustBox = document.getElementById('ia_sustitutos');
        if (sustBox) {
            if (data.sustitutos && data.sustitutos.length > 0) {
                sustBox.innerHTML = data.sustitutos.map(s => `
                    <a href="${escapeHtml(s.url)}" class="p-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/40 hover:bg-emerald-50/50 dark:hover:bg-emerald-950/30 transition block">
                        <div class="font-bold text-slate-800 dark:text-slate-200 truncate">${escapeHtml(s.nombre)}</div>
                        <div class="text-[11px] text-slate-500 truncate">${escapeHtml(s.principio || 'Equivalente')}</div>
                        <div class="text-xs font-bold text-emerald-600 dark:text-emerald-400 mt-1">S/ ${escapeHtml(s.precio)}</div>
                    </a>
                `).join('');
            } else {
                sustBox.innerHTML = `<div class="col-span-2 text-xs text-slate-400">No hay otros productos sustitutos en inventario actualmente.</div>`;
            }
        }
    })
    .catch((err) => {
        if (loading) loading.classList.add('hidden');
        if (errorBox) {
            errorBox.classList.remove('hidden');
            errorBox.textContent = err.message || 'No se pudo generar la ficha clínica.';
        }
    });
}

function cerrarModalProductoIA() {
    const modal = document.getElementById('modalProductoIA');
    if (!modal) return;
    modal.classList.add('hidden');
    modal.style.display = 'none';
}

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        cerrarModalLupaIA();
        cerrarModalProductoIA();
    }
    if (e.key === 'Enter') {
        const lupaModal = document.getElementById('modalLupaIA');
        const lupaInput = document.getElementById('lupa_q');
        if (!lupaModal.classList.contains('hidden') && document.activeElement === lupaInput) {
            e.preventDefault();
            buscarConLupaIA();
        }
    }
});
</script>
@endpush
@endsection
