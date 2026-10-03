@extends('layouts.app')

@section('title', 'Precios de Venta — FarmaBien')

@section('content')
<div class="space-y-4">

    {{-- Fila 1 — Breadcrumb --}}
    <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-800 dark:text-slate-200 font-semibold">Precios de Venta</span>
    </nav>

    {{-- Fila 2 — Título y Acciones --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-1">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white">Precios de Venta</h1>
            <p class="text-xs text-slate-600 dark:text-slate-400 mt-0.5">Catálogo maestro centralizado para la administración de precios base, factores de presentación y márgenes comerciales.</p>
        </div>

        {{-- Barra de Acciones Superior: Modo Full, Secundarios Pastel, Principal Verde --}}
        <div class="flex items-center space-x-2 self-start sm:self-auto flex-wrap gap-y-2">
            {{-- Modo Full --}}
            <button type="button" 
                    @click="$dispatch('toggle-pos-fullscreen')"
                    title="Modo Pantalla Completa / Ocultar Barras"
                    class="h-10 px-4 rounded-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm font-medium transition inline-flex items-center gap-2 shrink-0 cursor-pointer shadow-2xs">
                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                <span>Modo Full</span>
            </button>

            {{-- Secundario Pastel: Historial General --}}
            <a href="{{ route('precios.historial') }}"
               class="h-10 px-4 rounded-full bg-indigo-50 dark:bg-indigo-500/10 border border-indigo-200 dark:border-indigo-500/30 text-indigo-900 dark:text-indigo-300 hover:bg-indigo-100 dark:hover:bg-indigo-500/20 text-sm font-medium transition inline-flex items-center gap-2 cursor-pointer shadow-2xs">
                <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Historial General</span>
            </a>

            {{-- Secundario Pastel: Exportar --}}
            <a href="{{ route('precios.exportar') }}"
               class="h-10 px-4 rounded-full bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/30 text-emerald-900 dark:text-emerald-300 hover:bg-emerald-100 dark:hover:bg-emerald-500/20 text-sm font-medium transition inline-flex items-center gap-2 cursor-pointer shadow-2xs">
                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Exportar</span>
            </a>

            {{-- Principal Verde Sólido: Actualización Masiva --}}
            <a href="{{ route('precios.masivo') }}"
               class="h-10 px-4 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium transition inline-flex items-center gap-2 shadow-xs cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                <span>Actualización Masiva</span>
            </a>
        </div>
    </div>

    {{-- Tarjetas Superiores de Métricas --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
        {{-- Card 1: Productos con precio --}}
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-600 dark:text-slate-400">Medicamentos con Precio</span>
                <span class="p-2 rounded-xl bg-blue-50 dark:bg-blue-500/10 text-blue-900 dark:text-blue-300">
                    <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </span>
            </div>
            <div class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($totalProductosConPrecio) }}</div>
            <div class="mt-0.5 text-[11px] text-slate-500">Vigentes en catálogo activo</div>
        </div>

        {{-- Card 2: Margen promedio --}}
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-600 dark:text-slate-400">Margen Bruto Promedio</span>
                <span class="p-2 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-900 dark:text-emerald-300">
                    <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </span>
            </div>
            <div class="mt-2 text-2xl font-bold text-emerald-900 dark:text-emerald-400">{{ number_format($margenPromedio, 1) }}%</div>
            <div class="mt-0.5 text-[11px] text-slate-500">Sobre costo de adquisición</div>
        </div>

        {{-- Card 3: Margen bajo --}}
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-600 dark:text-slate-400">Margen Bajo (&lt; 25%)</span>
                <span class="p-2 rounded-xl bg-amber-50 dark:bg-amber-500/10 text-amber-900 dark:text-amber-300">
                    <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </span>
            </div>
            <div class="mt-2 text-2xl font-bold text-amber-900 dark:text-amber-400">{{ number_format($productosMargenBajo) }}</div>
            <div class="mt-0.5 text-[11px] text-slate-500">Requieren revisión comercial</div>
        </div>

        {{-- Card 4: Cambios últimos 30 días --}}
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-600 dark:text-slate-400">Actualizaciones (30 días)</span>
                <span class="p-2 rounded-xl bg-purple-50 dark:bg-purple-500/10 text-purple-900 dark:text-purple-300">
                    <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <div class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($cambiosUltimos30Dias) }}</div>
            <div class="mt-0.5 text-[11px] text-slate-500">Ajustes registrados en auditoría</div>
        </div>
    </div>

    {{-- Filtros con Buscadores AJAX y Botón Filtrar Verde Sólido --}}
    <div x-data="filtroPrecios()" class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xs space-y-3">
        <form method="GET" action="{{ route('precios.index') }}" id="formFiltrosPrecios" class="space-y-3">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3">
                {{-- Búsqueda por texto general --}}
                <div class="lg:col-span-4">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Medicamento / Principio Activo</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </span>
                        <input type="text" 
                               name="q" 
                               value="{{ request('q') }}" 
                               placeholder="Nombre, código o principio..." 
                               class="w-full pl-9 pr-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                    </div>
                </div>

                {{-- Autocompletado AJAX: Categoría --}}
                <div class="lg:col-span-3 relative" @click.outside="catOpen = false">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Categoría</label>
                    <input type="hidden" name="categoria_id" :value="catSelected ? catSelected.id : ''">
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </span>
                        <input type="text" 
                               x-model="catQuery" 
                               @input.debounce.300ms="buscarCategorias()" 
                               @focus="catOpen = true"
                               placeholder="Escribe categoría..." 
                               class="w-full pl-9 pr-7 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                        <template x-if="catSelected">
                            <button type="button" @click="limpiarCat()" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </template>
                    </div>
                    <div x-show="catOpen && catResults.length > 0" x-cloak class="absolute z-50 w-full mt-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-lg max-h-48 overflow-y-auto">
                        <template x-for="c in catResults" :key="c.id">
                            <button type="button" @click="seleccionarCat(c)" class="w-full text-left px-3 py-2 text-xs hover:bg-emerald-50 dark:hover:bg-slate-700 flex items-center justify-between text-slate-800 dark:text-slate-200">
                                <span x-text="c.text"></span>
                            </button>
                        </template>
                    </div>
                </div>

                {{-- Autocompletado AJAX: Laboratorio --}}
                <div class="lg:col-span-3 relative" @click.outside="labOpen = false">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Laboratorio</label>
                    <input type="hidden" name="laboratorio_id" :value="labSelected ? labSelected.id : ''">
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </span>
                        <input type="text" 
                               x-model="labQuery" 
                               @input.debounce.300ms="buscarLaboratorios()" 
                               @focus="labOpen = true"
                               placeholder="Escribe laboratorio..." 
                               class="w-full pl-9 pr-7 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                        <template x-if="labSelected">
                            <button type="button" @click="limpiarLab()" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </template>
                    </div>
                    <div x-show="labOpen && labResults.length > 0" x-cloak class="absolute z-50 w-full mt-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-lg max-h-48 overflow-y-auto">
                        <template x-for="l in labResults" :key="l.id">
                            <button type="button" @click="seleccionarLab(l)" class="w-full text-left px-3 py-2 text-xs hover:bg-emerald-50 dark:hover:bg-slate-700 flex items-center justify-between text-slate-800 dark:text-slate-200">
                                <span x-text="l.text"></span>
                            </button>
                        </template>
                    </div>
                </div>

                {{-- Estado de Margen --}}
                <div class="lg:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Margen Comercial</label>
                    <select name="estado_margen" class="w-full px-2.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                        <option value="">Todos los márgenes</option>
                        <option value="saludable" {{ request('estado_margen') === 'saludable' ? 'selected' : '' }}>Saludable (&ge; 25%)</option>
                        <option value="bajo" {{ request('estado_margen') === 'bajo' ? 'selected' : '' }}>Bajo (0% a 24.9%)</option>
                        <option value="negativo" {{ request('estado_margen') === 'negativo' ? 'selected' : '' }}>Negativo (&lt; 0%)</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center justify-between pt-2 border-t border-slate-200/80 dark:border-slate-800">
                <div class="text-[11px] text-slate-500 dark:text-slate-400">
                    Mostrando <span class="font-bold text-slate-800 dark:text-slate-200">{{ $productos->total() }}</span> productos
                </div>
                <div class="flex items-center space-x-2">
                    @if(request()->hasAny(['q', 'categoria_id', 'laboratorio_id', 'estado_margen']))
                    <a href="{{ route('precios.index') }}" class="px-3 py-1.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-xs font-medium hover:bg-slate-200 transition">
                        Limpiar filtros
                    </a>
                    @endif
                    <button type="submit" class="h-9 px-4 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-medium transition inline-flex items-center gap-1.5 shadow-2xs cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                        <span>Filtrar</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Tabla de Precios de Venta con Edición Rápida en Línea --}}
    <div x-data="tablaPreciosInline()" class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700 dark:text-slate-300">
                <thead class="bg-slate-100/80 dark:bg-slate-800/80 text-[11px] uppercase tracking-wider text-slate-600 dark:text-slate-400 font-bold border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="px-4 py-3">Medicamento / Principio</th>
                        <th class="px-3 py-3">Categoría & Lab</th>
                        <th class="px-3 py-3 text-right">Costo Compra</th>
                        <th class="px-3 py-3 text-right">Precio Venta (Base)</th>
                        <th class="px-3 py-3 text-center">Margen</th>
                        <th class="px-3 py-3">Presentaciones</th>
                        <th class="px-3 py-3 text-center">Promoción</th>
                        <th class="px-3 py-3">Última Actualización</th>
                        <th class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200/70 dark:divide-slate-800/70">
                    @forelse($productos as $prod)
                    @php
                        $costo = (float)($prod->precio_compra ?? 0);
                        $precio = (float)($prod->precio_venta ?? 0);
                        $margen = $precio > 0 ? (($precio - $costo) / $precio) * 100 : 0;
                        
                        // Determinar color de margen según especificación
                        if ($margen < 0) {
                            $margenBadge = 'bg-red-50 dark:bg-red-500/10 text-red-900 dark:text-red-400 border border-red-200 dark:border-red-500/30';
                        } elseif ($margen < 25) {
                            $margenBadge = 'bg-amber-50 dark:bg-amber-500/10 text-amber-900 dark:text-amber-400 border border-amber-200 dark:border-amber-500/30';
                        } else {
                            $margenBadge = 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-900 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/30';
                        }

                        // Verificar si tiene promoción
                        $promoProd = null;
                        foreach ($promocionesActivas as $pr) {
                            if ($pr->aplica_a_todo || $pr->categoria_id == $prod->categoria_id) {
                                $promoProd = $pr;
                                break;
                            }
                        }
                    @endphp
                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition" id="fila-prod-{{ $prod->id }}">
                        {{-- Producto / Principio --}}
                        <td class="px-4 py-3">
                            <div class="font-bold text-slate-900 dark:text-white">{{ $prod->nombre }}</div>
                            <div class="text-[11px] text-slate-500">{{ $prod->principio_activo ?? 'Sin principio activo' }} {{ $prod->concentracion ? '· ' . $prod->concentracion : '' }}</div>
                            @if($prod->codigo_barra)
                            <div class="text-[10px] font-mono text-slate-400">EAN: {{ $prod->codigo_barra }}</div>
                            @endif
                        </td>

                        {{-- Categoría & Lab --}}
                        <td class="px-3 py-3">
                            <div class="font-medium text-slate-800 dark:text-slate-200">{{ $prod->categoria->nombre ?? 'General' }}</div>
                            <div class="text-[11px] text-slate-500">{{ $prod->laboratorio->nombre ?? 'Sin lab' }}</div>
                        </td>

                        {{-- Costo Compra --}}
                        <td class="px-3 py-3 text-right font-mono font-medium text-slate-600 dark:text-slate-400">
                            C$ {{ number_format($costo, 2) }}
                        </td>

                        {{-- Precio de Venta (Base) con Edición Rápida --}}
                        <td class="px-3 py-3 text-right">
                            <div x-show="editingId !== {{ $prod->id }}" class="inline-flex items-center gap-1.5 group cursor-pointer" @click="iniciarEdicion({{ $prod->id }}, {{ $precio }}, {{ $costo }})">
                                <span class="font-mono font-bold text-slate-900 dark:text-white group-hover:text-emerald-600 transition text-sm" id="precio-txt-{{ $prod->id }}">
                                    C$ {{ number_format($precio, 2) }}
                                </span>
                                <svg class="w-3 h-3 text-slate-400 opacity-0 group-hover:opacity-100 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                            </div>

                            {{-- Formulario Inline --}}
                            <div x-show="editingId === {{ $prod->id }}" x-cloak class="flex items-center justify-end gap-1">
                                <input type="number" 
                                       step="0.01" 
                                       min="0.01" 
                                       x-model="editPrecio" 
                                       @keydown.enter="abrirModalMotivo({{ $prod->id }})"
                                       @keydown.escape="cancelarEdicion()"
                                       class="w-20 px-2 py-1 bg-white dark:bg-slate-800 border border-emerald-500 rounded text-xs font-mono font-bold text-slate-900 dark:text-white text-right focus:ring-1 focus:ring-emerald-500">
                                <button type="button" @click="abrirModalMotivo({{ $prod->id }})" class="p-1 rounded bg-emerald-600 text-white hover:bg-emerald-700" title="Confirmar">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </button>
                                <button type="button" @click="cancelarEdicion()" class="p-1 rounded bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-300" title="Cancelar">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </td>

                        {{-- Margen --}}
                        <td class="px-3 py-3 text-center">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-bold {{ $margenBadge }}" id="margen-badge-{{ $prod->id }}">
                                {{ number_format($margen, 1) }}%
                            </span>
                        </td>

                        {{-- Presentaciones --}}
                        <td class="px-3 py-3">
                            @if($prod->presentaciones->isNotEmpty())
                            <div class="flex flex-wrap gap-1">
                                @foreach($prod->presentaciones as $pres)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-[11px] text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                    <span class="font-medium mr-1">{{ $pres->nombre }}</span>
                                    <span class="font-mono font-bold text-emerald-900 dark:text-emerald-400">C$ {{ number_format($pres->precio_venta, 2) }}</span>
                                </span>
                                @endforeach
                            </div>
                            @else
                            <span class="text-[11px] text-slate-400">Solo unidad base</span>
                            @endif
                        </td>

                        {{-- Promoción --}}
                        <td class="px-3 py-3 text-center">
                            @if($promoProd)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 dark:bg-rose-500/10 text-rose-900 dark:text-rose-400 border border-rose-200 dark:border-rose-500/30">
                                {{ $promoProd->nombre }}
                            </span>
                            @else
                            <span class="text-[11px] text-slate-400">—</span>
                            @endif
                        </td>

                        {{-- Última Actualización --}}
                        <td class="px-3 py-3 text-[11px] text-slate-500">
                            @if($prod->precioVentaVigente)
                            <div>{{ $prod->precioVentaVigente->vigente_desde ? $prod->precioVentaVigente->vigente_desde->format('d/m/Y H:i') : $prod->updated_at->format('d/m/Y') }}</div>
                            <div class="text-[10px] text-slate-400">{{ $prod->precioVentaVigente->usuario->name ?? 'Sistema' }}</div>
                            @else
                            <div>{{ $prod->updated_at->format('d/m/Y') }}</div>
                            <div class="text-[10px] text-slate-400">Catálogo inicial</div>
                            @endif
                        </td>

                        {{-- Acciones (Botones Cuadrados Pastel: Ver Historial ojo azul, Editar lápiz verde) --}}
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end space-x-1.5">
                                {{-- Ver Historial (Ojo azul pastel) --}}
                                <a href="{{ route('precios.show', $prod) }}" 
                                   title="Ver Ficha y Historial de Precios"
                                   class="w-8 h-8 rounded-xl bg-blue-50 dark:bg-blue-500/10 border border-blue-200 dark:border-blue-500/30 text-blue-900 dark:text-blue-300 hover:bg-blue-100 dark:hover:bg-blue-500/20 flex items-center justify-center transition shadow-2xs">
                                    <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>

                                {{-- Editar Precio (Lápiz verde pastel) --}}
                                <a href="{{ route('precios.edit', $prod) }}" 
                                   title="Editar Precios y Presentaciones"
                                   class="w-8 h-8 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/30 text-emerald-900 dark:text-emerald-300 hover:bg-emerald-100 dark:hover:bg-emerald-500/20 flex items-center justify-center transition shadow-2xs">
                                    <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-4 py-8 text-center text-slate-500">
                            No se encontraron medicamentos con los filtros aplicados.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginación --}}
        @if($productos->hasPages())
        <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50">
            {{ $productos->links() }}
        </div>
        @endif

        {{-- Modal Centrado para Justificación / Motivo de Edición Rápida --}}
        <div x-show="modalMotivoOpen" 
             x-cloak 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs"
             @keydown.escape.window="modalMotivoOpen = false">
            <div class="w-full max-w-md bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xl overflow-hidden animate-scaleIn"
                 @click.outside="modalMotivoOpen = false">
                <div class="p-5 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800">
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Confirmar Cambio de Precio</h3>
                            <p class="text-xs text-slate-500">Registro obligatorio de auditoría</p>
                        </div>
                        <button type="button" @click="modalMotivoOpen = false" class="text-slate-400 hover:text-slate-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800 space-y-1 text-xs">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Nuevo Precio:</span>
                            <span class="font-bold text-emerald-900 dark:text-emerald-400 font-mono" x-text="'C$ ' + Number(editPrecio).toFixed(2)"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Margen Resultante:</span>
                            <span class="font-bold" :class="calcularMargenTemp() < 0 ? 'text-red-900 dark:text-red-400' : (calcularMargenTemp() < 25 ? 'text-amber-900 dark:text-amber-400' : 'text-emerald-900 dark:text-emerald-400')" x-text="calcularMargenTemp() + '%'"></span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Motivo del Ajuste <span class="text-rose-500">*</span></label>
                        <input type="text" 
                               x-model="editMotivo" 
                               placeholder="Ej: Ajuste por inflación, cambio de proveedor, etc." 
                               class="w-full px-3 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                    </div>

                    <div class="flex items-center justify-end space-x-2 pt-3 border-t border-slate-200 dark:border-slate-800">
                        <button type="button" @click="modalMotivoOpen = false" class="h-9 px-4 rounded-full bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-medium transition">
                            Cancelar
                        </button>
                        <button type="button" 
                                @click="guardarEdicionInline()" 
                                :disabled="guardando || editMotivo.trim().length < 3"
                                class="h-9 px-4 rounded-full bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white text-xs font-medium transition inline-flex items-center gap-1.5 shadow-xs cursor-pointer">
                            <span x-show="!guardando">Aplicar Precio</span>
                            <span x-show="guardando">Guardando...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
function filtroPrecios() {
    return {
        catOpen: false,
        catQuery: '',
        catSelected: @js(request('categoria_id') ? ['id' => request('categoria_id'), 'text' => 'Categoría Seleccionada'] : null),
        catResults: [],
        
        labOpen: false,
        labQuery: '',
        labSelected: @js(request('laboratorio_id') ? ['id' => request('laboratorio_id'), 'text' => 'Laboratorio Seleccionado'] : null),
        labResults: [],

        buscarCategorias() {
            if (this.catQuery.length < 2) { this.catResults = []; return; }
            fetch(`{{ route('precios.buscar-ajax') }}?tipo=categoria&q=${encodeURIComponent(this.catQuery)}`)
                .then(r => r.json())
                .then(data => { this.catResults = data.results || []; this.catOpen = true; });
        },
        seleccionarCat(c) {
            this.catSelected = c;
            this.catQuery = c.text;
            this.catOpen = false;
        },
        limpiarCat() {
            this.catSelected = null;
            this.catQuery = '';
            this.catResults = [];
        },

        buscarLaboratorios() {
            if (this.labQuery.length < 2) { this.labResults = []; return; }
            fetch(`{{ route('precios.buscar-ajax') }}?tipo=laboratorio&q=${encodeURIComponent(this.labQuery)}`)
                .then(r => r.json())
                .then(data => { this.labResults = data.results || []; this.labOpen = true; });
        },
        seleccionarLab(l) {
            this.labSelected = l;
            this.labQuery = l.text;
            this.labOpen = false;
        },
        limpiarLab() {
            this.labSelected = null;
            this.labQuery = '';
            this.labResults = [];
        }
    };
}

function tablaPreciosInline() {
    return {
        editingId: null,
        editPrecio: 0,
        editCosto: 0,
        editMotivo: '',
        modalMotivoOpen: false,
        guardando: false,

        iniciarEdicion(id, precio, costo) {
            this.editingId = id;
            this.editPrecio = precio;
            this.editCosto = costo;
            this.editMotivo = '';
        },
        cancelarEdicion() {
            this.editingId = null;
            this.editPrecio = 0;
            this.editMotivo = '';
        },
        abrirModalMotivo(id) {
            if (parseFloat(this.editPrecio) <= 0) {
                alert('El precio de venta debe ser mayor a 0');
                return;
            }
            this.modalMotivoOpen = true;
        },
        calcularMargenTemp() {
            const p = parseFloat(this.editPrecio) || 0;
            const c = parseFloat(this.editCosto) || 0;
            if (p <= 0) return 0;
            return (((p - c) / p) * 100).toFixed(1);
        },
        guardarEdicionInline() {
            if (this.editMotivo.trim().length < 3) {
                alert('Debe indicar un motivo válido.');
                return;
            }
            this.guardando = true;

            fetch(`/precios/${this.editingId}/inline`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    precio_venta: this.editPrecio,
                    motivo: this.editMotivo
                })
            })
            .then(r => r.json())
            .then(res => {
                this.guardando = false;
                if (res.success) {
                    const id = res.producto_id;
                    const txtEl = document.getElementById(`precio-txt-${id}`);
                    if (txtEl) txtEl.textContent = 'C$ ' + Number(res.precio_nuevo).toFixed(2);
                    
                    const mBadge = document.getElementById(`margen-badge-${id}`);
                    if (mBadge) {
                        mBadge.textContent = res.margen_nuevo + '%';
                        mBadge.className = 'inline-flex px-2 py-0.5 rounded-full text-xs font-bold ' + 
                            (res.margen_nuevo < 0 ? 'bg-red-50 dark:bg-red-500/10 text-red-900 dark:text-red-400 border border-red-200' : 
                            (res.margen_nuevo < 25 ? 'bg-amber-50 dark:bg-amber-500/10 text-amber-900 dark:text-amber-400 border border-amber-200' : 
                            'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-900 dark:text-emerald-400 border border-emerald-200'));
                    }

                    this.modalMotivoOpen = false;
                    this.editingId = null;
                } else {
                    alert(res.message || 'Error al actualizar el precio.');
                }
            })
            .catch(err => {
                this.guardando = false;
                alert('Error en la comunicación con el servidor.');
            });
        }
    };
}
</script>
@endpush
@endsection
