@extends('layouts.app')

@section('title', 'Sugerencias de Reorden Automáticas - FarmaBien')

@section('content')
<div class="space-y-5" x-data="{
    seleccionados: {},
    itemsData: @js($sugerencias->map(function($s) {
        return [
            'id' => $s['producto']->id,
            'nombre' => $s['producto']->nombre,
            'proveedor_id' => $s['proveedor_recomendado']->id ?? null,
            'cantidad' => $s['cantidad_sugerida'],
            'precio_unitario' => $s['mejor_precio_base'],
        ];
    })->values()),
    todosSeleccionados: false,
    init() {
        this.itemsData.forEach(item => {
            this.seleccionados[item.id] = true;
        });
        this.todosSeleccionados = true;
    },
    toggleTodos() {
        this.todosSeleccionados = !this.todosSeleccionados;
        this.itemsData.forEach(item => {
            this.seleccionados[item.id] = this.todosSeleccionados;
        });
    },
    totalSeleccionadosCount() {
        return Object.values(this.seleccionados).filter(Boolean).length;
    },
    generarCompraDirecta() {
        const itemsParaEnviar = this.itemsData
            .filter(item => this.seleccionados[item.id])
            .map(item => ({
                producto_id: item.id,
                cantidad: item.cantidad,
                precio_unitario: item.precio_unitario,
                presentacion_id: '',
                numero_lote: '',
                fecha_vencimiento: ''
            }));

        if (itemsParaEnviar.length === 0) {
            alert('Por favor selecciona al menos un medicamento para generar la compra.');
            return;
        }

        const primerProv = this.itemsData.find(item => this.seleccionados[item.id] && item.proveedor_id)?.proveedor_id || '';

        const url = new URL('{{ route('compras.create') }}', window.location.origin);
        if (primerProv) url.searchParams.set('proveedor_id', primerProv);
        url.searchParams.set('items', JSON.stringify(itemsParaEnviar));

        window.location.href = url.toString();
    },
    generarOrdenPO() {
        const itemsParaEnviar = this.itemsData
            .filter(item => this.seleccionados[item.id])
            .map(item => ({
                producto_id: item.id,
                cantidad: item.cantidad,
                precio_unitario: item.precio_unitario,
            }));

        if (itemsParaEnviar.length === 0) {
            alert('Por favor selecciona al menos un medicamento para generar la orden de compra.');
            return;
        }

        const primerProv = this.itemsData.find(item => this.seleccionados[item.id] && item.proveedor_id)?.proveedor_id || '';

        const url = new URL('{{ route('ordenes-compras.create') }}', window.location.origin);
        if (primerProv) url.searchParams.set('proveedor_id', primerProv);
        url.searchParams.set('items', JSON.stringify(itemsParaEnviar));

        window.location.href = url.toString();
    }
}">

    <!-- Breadcrumb -->
    <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('compras.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Compras</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-800 dark:text-slate-200 font-semibold">Reorden Inteligente</span>
    </nav>

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <span>Alertas y Sugerencias de Reorden</span>
                <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                    {{ $sugerencias->count() }} sugerencias
                </span>
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Detección proactiva de stock bajo y agotado considerando órdenes en tránsito y mejores cotizaciones de proveedores.
            </p>
        </div>
        <div class="flex items-center gap-2.5 flex-wrap shrink-0">
            <!-- 1. Navigation Button -->
            <a href="{{ route('compras.index') }}" 
               class="inline-flex items-center space-x-1.5 px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-xl shadow-xs transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Compras</span>
            </a>

            <!-- 2. Comparador -->
            <a href="{{ route('compras.comparador-precios') }}" 
               class="inline-flex items-center space-x-1.5 px-3.5 py-2 rounded-xl bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/40 dark:hover:bg-indigo-900/60 text-indigo-950 dark:text-indigo-300 text-xs font-bold border border-indigo-200 dark:border-indigo-800 shadow-2xs transition">
                <svg class="w-4 h-4 text-indigo-700 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <span>Comparador</span>
            </a>

            @can('registrar compras')
            <!-- 3. Generar Orden PO -->
            <button type="button"
                    @click="generarOrdenPO()"
                    :disabled="totalSeleccionadosCount() === 0"
                    class="inline-flex items-center space-x-2 px-3.5 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 active:bg-purple-800 disabled:opacity-50 disabled:cursor-not-allowed text-white text-xs font-bold shadow-2xs transition cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Crear Orden PO (<span x-text="totalSeleccionadosCount()"></span>)</span>
            </button>

            <!-- 4. Compra Directa -->
            <button type="button" 
                    @click="generarCompraDirecta()"
                    :disabled="totalSeleccionadosCount() === 0"
                    class="inline-flex items-center space-x-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 disabled:opacity-50 disabled:cursor-not-allowed text-white text-xs font-bold rounded-xl shadow-xs transition cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                <span>Compra Directa (<span x-text="totalSeleccionadosCount()"></span>)</span>
            </button>
            @endcan
        </div>
    </div>

    <!-- 4 KPI Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Productos Bajo Stock</p>
                <p class="text-xl font-bold text-slate-900 dark:text-white mt-0.5 font-mono">{{ $sugerencias->count() }}</p>
                <p class="text-[11px] text-rose-500 dark:text-rose-400 mt-0.5 font-medium">
                    {{ $totalCriticos }} críticamente agotados
                </p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Inversión Estimada</p>
                <p class="text-xl font-bold text-slate-900 dark:text-white mt-0.5 font-mono">{{ formato_moneda($totalInversionEstimada) }}</p>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Reposición a nivel óptimo</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center shrink-0">
                <span class="text-xs font-bold text-slate-700 dark:text-slate-300">C$</span>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Ahorro Proyectado</p>
                <p class="text-xl font-bold text-emerald-900 dark:text-emerald-400 mt-0.5 font-mono">{{ formato_moneda($totalAhorroEstimado) }}</p>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Por mejor cotización</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">En Tránsito (OC)</p>
                <p class="text-xl font-bold text-blue-600 dark:text-blue-400 mt-0.5 font-mono">{{ $totalEnTransito }}</p>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Con orden abierta</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </div>
        </div>
    </div>

    <!-- Barra de Filtros -->
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4 shadow-xs">
        <form method="GET" action="{{ route('compras.sugerencias-reorden') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
            <div class="lg:col-span-3">
                <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Buscar Medicamento</label>
                <input type="text" name="buscar" value="{{ $buscar }}" placeholder="Nombre, código, principio..."
                       class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-xs focus:ring-emerald-500 focus:border-emerald-500">
            </div>

            <div class="lg:col-span-3">
                <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Proveedor Sugerido</label>
                <select name="proveedor_id" onchange="this.form.submit()"
                        class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-xs focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="">Todos los proveedores</option>
                    @foreach($proveedores as $prov)
                    <option value="{{ $prov->id }}" {{ $proveedorFiltro == $prov->id ? 'selected' : '' }}>{{ $prov->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div class="lg:col-span-2">
                <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Categoría</label>
                <select name="categoria_id" onchange="this.form.submit()"
                        class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-xs focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="">Todas las categorías</option>
                    @foreach($categorias as $cat)
                    <option value="{{ $cat->id }}" {{ $categoriaFiltro == $cat->id ? 'selected' : '' }}>{{ $cat->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div class="lg:col-span-2">
                <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Laboratorio</label>
                <select name="laboratorio_id" onchange="this.form.submit()"
                        class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-xs focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="">Todos los laboratorios</option>
                    @foreach($laboratorios as $lab)
                    <option value="{{ $lab->id }}" {{ ($laboratorioFiltro ?? '') == $lab->id ? 'selected' : '' }}>{{ $lab->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div class="lg:col-span-2 flex items-end gap-2">
                <label class="inline-flex items-center space-x-1.5 cursor-pointer h-10">
                    <input type="checkbox" name="solo_agotados" value="1" onchange="this.form.submit()"
                           {{ $soloAgotados ? 'checked' : '' }}
                           class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                    <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Solo Agotados (0)</span>
                </label>
                <button type="submit"
                        class="px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-2xs transition inline-flex items-center justify-center gap-1.5 shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    <span>Filtrar</span>
                </button>
            </div>
        </form>

        @if(request()->hasAny(['buscar', 'proveedor_id', 'categoria_id', 'laboratorio_id', 'solo_agotados']))
        <div class="mt-2.5 pt-2.5 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
            <span class="text-slate-500 dark:text-slate-400 text-[11px]">Filtros activos</span>
            <a href="{{ route('compras.sugerencias-reorden') }}" class="text-emerald-900 dark:text-emerald-400 hover:underline text-[11px] font-bold">
                Limpiar filtros
            </a>
        </div>
        @endif
    </div>

    <!-- Tabla Principal de Sugerencias -->
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <label class="inline-flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" @click="toggleTodos()" :checked="todosSeleccionados"
                           class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                    <span class="text-xs font-bold text-slate-800 dark:text-slate-200">Seleccionar Todos</span>
                </label>
            </div>
            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                <span x-text="totalSeleccionadosCount()"></span> de {{ $sugerencias->count() }} ítems seleccionados
            </span>
        </div>

        @if($sugerencias->isNotEmpty())
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                        <th class="px-4 py-3 text-center w-10"></th>
                        <th class="px-4 py-3">Medicamento</th>
                        <th class="px-4 py-3 text-center">Nivel Stock</th>
                        <th class="px-4 py-3 text-center">En Tránsito (OC)</th>
                        <th class="px-4 py-3 text-center">Cantidad Sugerida</th>
                        <th class="px-4 py-3">Proveedor Sugerido</th>
                        <th class="px-4 py-3 text-right">Precio Unit. Base</th>
                        <th class="px-4 py-3 text-right">Costo Estimado</th>
                        <th class="px-4 py-3 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-700 dark:text-slate-300">
                    @foreach($sugerencias as $idx => $s)
                    @php
                        $p = $s['producto'];
                        $stockActual = $s['stock_actual'];
                        $stockMin = $s['stock_minimo'];
                        $stockTransito = $s['stock_en_transito'];
                        $porcentajeStock = $stockMin > 0 ? min(100, round(($stockActual / $stockMin) * 100)) : 0;
                        $urgencia = $s['urgencia'];
                    @endphp
                    <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition">
                        <td class="px-4 py-3.5 text-center">
                            <input type="checkbox" 
                                   x-model="seleccionados['{{ $p->id }}']"
                                   class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 cursor-pointer">
                        </td>
                        <td class="px-4 py-3.5">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-slate-900 dark:text-white">
                                    {{ $p->nombre }}
                                </span>
                                @if($urgencia === 'critica')
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-rose-100 dark:bg-rose-950/70 text-rose-700 dark:text-rose-300">
                                    CRÍTICO
                                </span>
                                @elseif($urgencia === 'alta')
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-orange-100 dark:bg-orange-950/70 text-orange-700 dark:text-orange-300">
                                    ALTO
                                </span>
                                @endif
                            </div>
                            <div class="text-[11px] text-slate-400 mt-0.5">
                                Lab: {{ $p->laboratorio->nombre ?? 'N/A' }} &bull; Cat: {{ $p->categoria->nombre ?? 'General' }}
                                @if($p->codigo_barra)
                                &bull; <span class="font-mono">{{ $p->codigo_barra }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3.5 text-center">
                            @if($stockActual == 0)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 dark:bg-rose-950/70 text-rose-900 dark:text-rose-300 border border-rose-300 dark:border-rose-800">
                                AGOTADO (0)
                            </span>
                            @else
                            <div class="flex flex-col items-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 dark:bg-amber-950/70 text-amber-900 dark:text-amber-300 border border-amber-300 dark:border-amber-800">
                                    {{ $stockActual }} / {{ $stockMin }} mín
                                </span>
                                <div class="w-16 bg-slate-200 dark:bg-slate-700 h-1.5 rounded-full mt-1 overflow-hidden">
                                    <div class="bg-amber-500 h-full rounded-full" style="width: {{ $porcentajeStock }}%"></div>
                                </div>
                            </div>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 text-center">
                            @if($stockTransito > 0)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 dark:bg-blue-950/70 text-blue-800 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                +{{ $stockTransito }} en camino
                            </span>
                            @else
                            <span class="text-slate-400 font-mono text-[11px]">0</span>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 text-center">
                            <span class="font-mono font-bold text-slate-900 dark:text-white px-2 py-1 bg-slate-100 dark:bg-slate-800 rounded-lg">
                                +{{ $s['cantidad_sugerida'] }} uds
                            </span>
                        </td>
                        <td class="px-4 py-3.5">
                            @if($s['proveedor_recomendado'])
                            <div class="font-semibold text-slate-900 dark:text-white">
                                {{ $s['proveedor_recomendado']->nombre }}
                            </div>
                            <div class="text-[11px] text-slate-400">
                                RUC: {{ $s['proveedor_recomendado']->ruc ?? '—' }}
                            </div>
                            @else
                            <span class="text-slate-400 italic">Sin proveedor asignado</span>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 text-right font-mono font-bold text-emerald-900 dark:text-emerald-400">
                            C$ {{ number_format($s['mejor_precio_base'], 4) }}
                        </td>
                        <td class="px-4 py-3.5 text-right font-mono font-bold text-slate-900 dark:text-white">
                            {{ formato_moneda($s['costo_estimado']) }}
                        </td>
                        <td class="px-4 py-3.5 text-center whitespace-nowrap">
                            <div class="inline-flex items-center justify-center gap-1.5">
                                @can('registrar compras')
                                <a href="{{ route('ordenes-compras.create') }}?producto_id={{ $p->id }}&proveedor_id={{ $s['proveedor_recomendado']->id ?? '' }}&cantidad={{ $s['cantidad_sugerida'] }}"
                                   title="Crear Orden de Compra (PO)"
                                   class="w-8 h-8 rounded-lg bg-purple-50 dark:bg-purple-950/60 text-purple-900 dark:text-purple-300 hover:bg-purple-100 dark:hover:bg-purple-900/60 border border-purple-300 dark:border-purple-800/60 inline-flex items-center justify-center transition shadow-2xs">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </a>
                                <a href="{{ route('compras.create') }}?producto_id={{ $p->id }}&proveedor_id={{ $s['proveedor_recomendado']->id ?? '' }}&precio_unitario={{ $s['mejor_precio_base'] }}&cantidad={{ $s['cantidad_sugerida'] }}"
                                   title="Comprar directo"
                                   class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-900 dark:text-emerald-300 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 border border-emerald-300 dark:border-emerald-800/60 inline-flex items-center justify-center transition shadow-2xs">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                </a>
                                @endcan
                                <a href="{{ route('compras.comparador-precios', ['producto_id' => $p->id]) }}"
                                   title="Ver comparador de cotizaciones de proveedores"
                                   class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-900 dark:text-indigo-300 hover:bg-indigo-100 dark:hover:bg-indigo-900/60 border border-indigo-300 dark:border-indigo-800/60 inline-flex items-center justify-center transition shadow-2xs">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="px-5 py-16 text-center text-xs text-slate-500 dark:text-slate-400">
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <p class="text-sm font-bold text-slate-800 dark:text-slate-200">¡Inventario Abastecido!</p>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">No hay productos con stock por debajo del mínimo requerido en este momento.</p>
        </div>
        @endif
    </div>

</div>
@endsection
