@extends('layouts.app')

@section('title', 'Comparador de Precios y Cotizaciones - FarmaBien')

@section('content')
@php
    $productosList = $productos->map(function($p) {
        return [
            'id' => $p->id,
            'nombre' => $p->nombre,
            'principio_activo' => $p->principio_activo ?? '',
            'codigo_barra' => $p->codigo_barra ?? '',
            'laboratorio' => $p->laboratorio->nombre ?? 'Sin Lab',
            'precio_compra' => (float) $p->precio_compra,
            'presentaciones' => $p->presentacionesActivas ? $p->presentacionesActivas->map(function($pres) {
                return [
                    'id' => $pres->id,
                    'nombre' => $pres->nombre,
                    'unidades_por_presentacion' => (int) $pres->unidades_por_presentacion,
                    'precio_compra' => (float) $pres->precio_compra,
                ];
            })->values()->toArray() : [],
        ];
    })->values()->toArray();

    $proveedoresList = $proveedores->map(function($prov) {
        return [
            'id' => $prov->id,
            'nombre' => $prov->nombre,
            'ruc' => $prov->ruc ?? '',
            'contacto' => $prov->contacto ?? '',
            'telefono' => $prov->telefono ?? '',
        ];
    })->values()->toArray();
@endphp

<div class="space-y-5" x-data="{
    modalCotizacion: false,
    catalogoProds: @js($productosList),
    catalogoProvs: @js($proveedoresList),
    
    // Cotizacion State
    cotizacionProdId: '{{ $productoSeleccionado->id ?? '' }}',
    cotizacionProdObj: null,
    cotizacionProvId: '',
    cotizacionProvObj: null,
    cotizacionPresentaciones: @js($productoSeleccionado ? $productoSeleccionado->presentacionesActivas : []),
    cotizacionPrecio: '',
    cotizacionPresId: '',
    cotizacionUnidades: 1,

    // Buscador AJAX Principal
    mainProdQuery: '',
    mainProdDropdown: false,
    buscandoProdsMain: false,
    prodsMainResultados: @js(array_slice($productosList, 0, 12)),

    async buscarProdsMainAjax() {
        const q = (this.mainProdQuery || '').trim();
        if (!q) {
            this.prodsMainResultados = this.catalogoProds.slice(0, 12);
            return;
        }
        this.buscandoProdsMain = true;
        try {
            const res = await fetch(`{{ route('api.productos.buscar') }}?q=${encodeURIComponent(q)}&limit=15`);
            if (res.ok) {
                const data = await res.json();
                this.prodsMainResultados = data.map(p => ({
                    id: p.id,
                    nombre: p.nombre,
                    principio_activo: p.principio_activo || '',
                    codigo_barra: p.codigo_barra || '',
                    laboratorio: p.laboratorio?.nombre || 'Sin Lab',
                    precio_compra: parseFloat(p.precio_compra) || 0
                }));
            }
        } catch (e) {
            // Fallback a filtrado en memoria
            const qLower = q.toLowerCase();
            this.prodsMainResultados = this.catalogoProds.filter(p => {
                return (p.nombre && p.nombre.toLowerCase().includes(qLower)) ||
                       (p.principio_activo && p.principio_activo.toLowerCase().includes(qLower)) ||
                       (p.codigo_barra && p.codigo_barra.toLowerCase().includes(qLower));
            }).slice(0, 15);
        } finally {
            this.buscandoProdsMain = false;
        }
    },

    seleccionarProdMain(p) {
        if (!p || !p.id) return;
        window.location.href = `{{ route('compras.comparador-precios') }}?producto_id=${p.id}`;
    },

    // Buscadores Modal
    prodModalQuery: '',
    prodModalDropdown: false,
    provModalQuery: '',
    provModalDropdown: false,

    init() {
        if (this.cotizacionProdId) {
            const p = this.catalogoProds.find(x => x.id == this.cotizacionProdId);
            if (p) {
                this.cotizacionProdObj = p;
                this.cotizacionPresentaciones = p.presentaciones || [];
            }
        }
    },

    filtrarProdsModal() {
        const q = (this.prodModalQuery || '').trim().toLowerCase();
        if (!q) return this.catalogoProds.slice(0, 10);
        return this.catalogoProds.filter(p => {
            return (p.nombre && p.nombre.toLowerCase().includes(q)) ||
                   (p.principio_activo && p.principio_activo.toLowerCase().includes(q)) ||
                   (p.codigo_barra && p.codigo_barra.toLowerCase().includes(q)) ||
                   (p.laboratorio && p.laboratorio.toLowerCase().includes(q));
        }).slice(0, 10);
    },

    seleccionarProdModal(p) {
        this.cotizacionProdId = p.id;
        this.cotizacionProdObj = p;
        this.cotizacionPresentaciones = p.presentaciones || [];
        this.cotizacionPresId = '';
        this.actualizarUnidades();
        this.prodModalDropdown = false;
        this.prodModalQuery = '';
    },

    deseleccionarProdModal() {
        this.cotizacionProdId = '';
        this.cotizacionProdObj = null;
        this.cotizacionPresentaciones = [];
        this.cotizacionPresId = '';
        this.actualizarUnidades();
        this.prodModalDropdown = true;
        this.$nextTick(() => {
            document.getElementById('cotizProdSearchInput')?.focus();
        });
    },

    filtrarProvsModal() {
        const q = (this.provModalQuery || '').trim().toLowerCase();
        if (!q) return this.catalogoProvs.slice(0, 10);
        return this.catalogoProvs.filter(p => {
            return (p.nombre && p.nombre.toLowerCase().includes(q)) ||
                   (p.ruc && p.ruc.toLowerCase().includes(q)) ||
                   (p.contacto && p.contacto.toLowerCase().includes(q)) ||
                   (p.telefono && p.telefono.toLowerCase().includes(q));
        }).slice(0, 10);
    },

    seleccionarProvModal(prov) {
        this.cotizacionProvId = prov.id;
        this.cotizacionProvObj = prov;
        this.provModalDropdown = false;
        this.provModalQuery = '';
    },

    deseleccionarProvModal() {
        this.cotizacionProvId = '';
        this.cotizacionProvObj = null;
        this.provModalDropdown = true;
        this.$nextTick(() => {
            document.getElementById('cotizProvSearchInput')?.focus();
        });
    },

    abrirModalCotizacion(prodId, prodNombre, presentaciones) {
        if (prodId) {
            this.cotizacionProdId = prodId;
            const p = this.catalogoProds.find(x => x.id == prodId);
            this.cotizacionProdObj = p || { id: prodId, nombre: prodNombre, presentaciones: presentaciones || [] };
            this.cotizacionPresentaciones = presentaciones || (p ? p.presentaciones : []);
        } else {
            this.cotizacionProdId = '';
            this.cotizacionProdObj = null;
            this.cotizacionPresentaciones = [];
        }
        this.cotizacionProvId = '';
        this.cotizacionProvObj = null;
        this.cotizacionPrecio = '';
        this.cotizacionPresId = '';
        this.cotizacionUnidades = 1;
        this.prodModalQuery = '';
        this.provModalQuery = '';
        this.modalCotizacion = true;
    },

    actualizarUnidades() {
        if (!this.cotizacionPresId) {
            this.cotizacionUnidades = 1;
            return;
        }
        const pres = this.cotizacionPresentaciones.find(p => p.id == this.cotizacionPresId);
        this.cotizacionUnidades = pres ? (pres.unidades_por_presentacion || 1) : 1;
    },

    get precioUnitarioCalculado() {
        const p = parseFloat(this.cotizacionPrecio) || 0;
        const u = parseInt(this.cotizacionUnidades) || 1;
        return u > 0 ? (p / u).toFixed(4) : '0.0000';
    }
}">

    <!-- Breadcrumb -->
    <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('compras.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Compras</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-800 dark:text-slate-200 font-semibold">Comparador de Precios</span>
    </nav>

    <!-- Header & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <span>Comparador de Precios y Cotizaciones</span>
                <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                    Proveedores
                </span>
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Evalúa cotizaciones entre distribuidores, detecta el mejor precio por unidad y optimiza los costos de compra.
            </p>
        </div>
        <div class="flex items-center gap-2.5 flex-wrap shrink-0">
            <!-- Navigation Button First -->
            <a href="{{ route('compras.index') }}" 
               class="inline-flex items-center space-x-1.5 px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-xl shadow-xs transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Compras</span>
            </a>

            <!-- Botón Modo Full -->
            <button type="button" 
                    @click="$dispatch('toggle-pos-fullscreen')"
                    title="Modo Pantalla Completa / Ocultar Barras"
                    class="inline-flex items-center space-x-1.5 px-3 py-2 rounded-xl bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold shadow-2xs transition cursor-pointer">
                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                <span>Modo Full</span>
            </button>

            @can('registrar compras')
            <button type="button" 
                    @click="abrirModalCotizacion()"
                    class="inline-flex items-center space-x-1.5 px-3.5 py-2 bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-300 dark:border-emerald-800 text-emerald-900 dark:text-emerald-300 hover:bg-emerald-100 dark:hover:bg-emerald-900/80 text-xs font-bold rounded-xl shadow-xs transition cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Nueva Cotización</span>
            </button>
            <a href="{{ route('compras.sugerencias-reorden') }}" 
               class="inline-flex items-center space-x-1.5 px-3.5 py-2 rounded-xl bg-amber-50/80 hover:bg-amber-100/90 dark:bg-amber-950/40 dark:hover:bg-amber-900/60 text-amber-900 dark:text-amber-300 text-xs font-bold border border-amber-200 dark:border-amber-800/80 shadow-2xs transition">
                <svg class="w-4 h-4 text-amber-700 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                <span>Reorden Inteligente</span>
            </a>
            @endcan
        </div>
    </div>

    <!-- Barra de Selección de Medicamento y Filtros -->
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4 shadow-xs">
        <form method="GET" action="{{ route('compras.comparador-precios') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
            
            <!-- Selector Inteligente AJAX de Medicamento -->
            <div class="lg:col-span-6 relative" @click.away="mainProdDropdown = false">
                <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1 flex items-center justify-between">
                    <span>Medicamento a Comparar (Búsqueda Rápida AJAX)</span>
                    <span x-show="buscandoProdsMain" class="text-[10px] text-emerald-600 dark:text-emerald-400 font-bold animate-pulse">Buscando...</span>
                </label>
                <div class="relative">
                    <input type="text" 
                           x-model="mainProdQuery" 
                           @input.debounce.250ms="buscarProdsMainAjax(); mainProdDropdown = true"
                           @focus="mainProdDropdown = true"
                           placeholder="{{ $productoSeleccionado ? $productoSeleccionado->nombre . ' (' . ($productoSeleccionado->laboratorio->nombre ?? 'Sin Lab') . ')' : 'Escriba nombre, principio activo o código...' }}"
                           class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-xs pl-8 pr-8 focus:ring-emerald-500 focus:border-emerald-500">
                    <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <button type="button" 
                            x-show="mainProdQuery" 
                            @click="mainProdQuery = ''; buscarProdsMainAjax()"
                            class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 text-xs font-bold">
                        &times;
                    </button>
                </div>

                <!-- Dropdown de Resultados AJAX -->
                <div x-show="mainProdDropdown && prodsMainResultados.length > 0" 
                     x-cloak
                     class="absolute z-50 left-0 right-0 mt-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-xl max-h-60 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-700">
                    <template x-for="p in prodsMainResultados" :key="p.id">
                        <div @click="seleccionarProdMain(p)" 
                             class="p-2.5 hover:bg-emerald-50 dark:hover:bg-slate-700/80 cursor-pointer flex items-center justify-between transition text-xs">
                            <div>
                                <div class="font-bold text-slate-900 dark:text-white" x-text="p.nombre"></div>
                                <div class="text-[10px] text-slate-500 dark:text-slate-400">
                                    <span x-text="p.laboratorio"></span>
                                    <span x-show="p.principio_activo" x-text="' &bull; ' + p.principio_activo"></span>
                                </div>
                            </div>
                            <span class="text-[10px] font-mono font-bold text-emerald-600 dark:text-emerald-400 shrink-0">
                                Ref: C$<span x-text="parseFloat(p.precio_compra).toFixed(2)"></span>
                            </span>
                        </div>
                    </template>
                </div>
            </div>

            <div class="lg:col-span-3">
                <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                    Laboratorio
                </label>
                <select name="laboratorio_id" onchange="this.form.submit()"
                        class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-xs focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="">Todos los laboratorios</option>
                    @foreach($laboratorios as $lab)
                    <option value="{{ $lab->id }}" {{ request('laboratorio_id') == $lab->id ? 'selected' : '' }}>{{ $lab->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div class="lg:col-span-3 flex items-end gap-1.5">
                <div class="w-full">
                    <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Buscar texto libre
                    </label>
                    <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Paracetamol, etc..."
                           class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-xs focus:ring-emerald-500 focus:border-emerald-500">
                </div>
                <button type="submit" class="py-2 px-4 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white rounded-xl text-xs font-bold transition text-center cursor-pointer shrink-0 shadow-xs inline-flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    <span>Filtrar</span>
                </button>
            </div>
        </form>

        @if(request()->hasAny(['producto_id', 'laboratorio_id', 'categoria_id', 'buscar']))
        <div class="mt-2.5 pt-2.5 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
            <span class="text-slate-500 dark:text-slate-400 text-[11px]">Filtros activos aplicados</span>
            <a href="{{ route('compras.comparador-precios') }}" class="text-emerald-700 dark:text-emerald-400 hover:underline text-[11px] font-bold">
                Limpiar filtros
            </a>
        </div>
        @endif
    </div>

    @if($productoSeleccionado)
    <!-- Banner de Medicamento Seleccionado -->
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center space-x-3.5">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 border border-emerald-200 dark:border-emerald-800">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
            </div>
            <div>
                <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <span>{{ $productoSeleccionado->nombre }}</span>
                    @if($productoSeleccionado->codigo_barra)
                    <span class="font-mono text-xs px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700">{{ $productoSeleccionado->codigo_barra }}</span>
                    @endif
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Lab: <strong class="text-slate-700 dark:text-slate-300">{{ $productoSeleccionado->laboratorio->nombre ?? 'Sin Laboratorio' }}</strong> &bull;
                    Categoría: <span class="text-slate-700 dark:text-slate-300">{{ $productoSeleccionado->categoria->nombre ?? 'General' }}</span> &bull;
                    Costo Ref. Catálogo: <span class="font-mono font-bold text-slate-800 dark:text-slate-200">{{ formato_moneda($productoSeleccionado->precio_compra) }}</span>
                </p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            @can('registrar compras')
            @if($proveedorRecomendado)
            <a href="{{ route('compras.create') }}?producto_id={{ $productoSeleccionado->id }}&proveedor_id={{ $proveedorRecomendado->id }}&precio_unitario={{ $mejorPrecio ?? $productoSeleccionado->precio_compra }}"
               class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-bold rounded-xl shadow-xs transition inline-flex items-center space-x-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Comprar con Proveedor Sugerido</span>
            </a>
            @endif
            <button type="button" 
                    @click="abrirModalCotizacion('{{ $productoSeleccionado->id }}', '{{ addslashes($productoSeleccionado->nombre) }}', @js($productoSeleccionado->presentacionesActivas))"
                    class="px-3 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-xl transition inline-flex items-center space-x-1 cursor-pointer">
                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                <span>+ Cotización</span>
            </button>
            @endcan
        </div>
    </div>

    <!-- 3 KPI Cards Unificados -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- KPI 1: Mejor Precio -->
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Mejor Precio por Unidad Base</p>
                <p class="text-xl font-bold text-emerald-900 dark:text-emerald-400 mt-0.5 font-mono">
                    {{ $mejorPrecio ? 'C$ ' . number_format($mejorPrecio, 4) : '—' }}
                </p>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 truncate max-w-[200px]">
                    {{ $proveedorRecomendado ? 'Proveedor: ' . $proveedorRecomendado->nombre : 'Sin cotizaciones' }}
                </p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                <span class="text-xs font-bold text-emerald-900 dark:text-emerald-300">C$</span>
            </div>
        </div>

        <!-- KPI 2: Ahorro Potencial -->
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Diferencial / Ahorro Máximo</p>
                <p class="text-xl font-bold text-emerald-900 dark:text-emerald-400 mt-0.5 font-mono">
                    {{ $ahorroMaximo > 0 ? 'C$ ' . number_format($ahorroMaximo, 4) : 'C$ 0.00' }}
                </p>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                    Por unidad respecto al más alto
                </p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            </div>
        </div>

        <!-- KPI 3: Precio Promedio -->
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Precio Promedio de Mercado</p>
                <p class="text-xl font-bold text-slate-900 dark:text-white mt-0.5 font-mono">
                    {{ $precioPromedio > 0 ? 'C$ ' . number_format($precioPromedio, 4) : '—' }}
                </p>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                    {{ $comparativa->count() }} distribuidores comparados
                </p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </div>
        </div>
    </div>

    <!-- Tabla Comparativa de Distribuidores -->
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <h3 class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">
                Matriz Comparativa por Distribuidor
            </h3>
            <span class="text-xs text-slate-500 dark:text-slate-400">
                Ordenado de menor a mayor costo por unidad base
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                        <th class="px-4 py-3">Distribuidor</th>
                        <th class="px-4 py-3">Última Presentación</th>
                        <th class="px-4 py-3 text-right">Precio Presentación</th>
                        <th class="px-4 py-3 text-right">Costo / Unidad Base</th>
                        <th class="px-4 py-3 text-center">Fecha Cotización</th>
                        <th class="px-4 py-3 text-center">Estado</th>
                        <th class="px-4 py-3 text-center">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                    @forelse($comparativa as $idx => $comp)
                    @php
                        $esElMejor = ($idx === 0);
                        $diferencia = $comp['ultimo_precio_base'] - $mejorPrecio;
                        $porcentajeMas = $mejorPrecio > 0 ? round(($diferencia / $mejorPrecio) * 100, 1) : 0;
                    @endphp
                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition {{ $esElMejor ? 'bg-emerald-50/40 dark:bg-emerald-950/20' : '' }}">
                        <td class="px-4 py-3.5">
                            <div class="flex items-center space-x-2">
                                @if($esElMejor)
                                <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                                @endif
                                <div>
                                    <span class="font-bold text-slate-900 dark:text-white">{{ $comp['proveedor']->nombre }}</span>
                                    @if($comp['proveedor']->contacto)
                                    <div class="text-[10px] text-slate-400">Contacto: {{ $comp['proveedor']->contacto }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3.5">
                            <span class="font-semibold">{{ $comp['ultima_presentacion'] ?? ($comp['ultimo_registro']->presentacion->nombre ?? 'Unidad Base') }}</span>
                            <span class="text-[10px] text-slate-400">({{ $comp['unidades_por_presentacion'] ?? 1 }} unid.)</span>
                        </td>
                        <td class="px-4 py-3.5 text-right font-mono">
                            {{ formato_moneda($comp['ultimo_precio_compra'] ?? $comp['ultimo_precio_base']) }}
                        </td>
                        <td class="px-4 py-3.5 text-right font-mono font-black {{ $esElMejor ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-900 dark:text-white' }}">
                            C$ {{ number_format($comp['ultimo_precio_base'] ?? 0, 4) }}
                        </td>
                        <td class="px-4 py-3.5 text-center text-slate-500">
                            {{ !empty($comp['ultima_fecha']) ? \Carbon\Carbon::parse($comp['ultima_fecha'])->format('d/m/Y') : '-' }}
                        </td>
                        <td class="px-4 py-3.5 text-center">
                            @if($esElMejor)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-900 dark:bg-emerald-950/80 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800">
                                Mejor Precio
                            </span>
                            @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-400">
                                +{{ $porcentajeMas }}%
                            </span>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 text-center">
                            @can('registrar compras')
                            <a href="{{ route('compras.create') }}?producto_id={{ $productoSeleccionado->id }}&proveedor_id={{ $comp['proveedor']->id }}&precio_unitario={{ $comp['ultimo_precio_compra'] ?? $comp['ultimo_precio_base'] }}"
                               title="Pedir / Comprar"
                               class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-bold text-emerald-900 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-300 dark:border-emerald-800 hover:bg-emerald-100 dark:hover:bg-emerald-900/80 transition">
                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                <span>Pedir</span>
                            </a>
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-slate-500 dark:text-slate-400">
                            No se han registrado cotizaciones ni compras con diferentes proveedores para este medicamento.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Modal para Registrar Cotización Directa con Autocompletado AJAX / Predictivo -->
    <template x-teleport="body">
        <div x-show="modalCotizacion" 
             x-cloak
             class="fixed inset-0 z-[9999] flex items-start sm:items-center justify-center p-3 sm:p-6 bg-slate-950/75 backdrop-blur-sm overflow-y-auto"
             @keydown.escape.window="modalCotizacion = false">
            
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 sm:p-6 shadow-2xl max-w-lg w-full my-auto flex flex-col max-h-[calc(100vh-2.5rem)] overflow-hidden"
                 @click.away="modalCotizacion = false">
                
                <!-- Header Modal -->
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3 shrink-0">
                    <div class="flex items-center space-x-2.5">
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Registrar Nueva Cotización</h3>
                            <p class="text-[11px] text-slate-500">Agrega o actualiza precios de cotización de distribuidores</p>
                        </div>
                    </div>
                    <button type="button" @click="modalCotizacion = false" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition" title="Cerrar">✕</button>
                </div>

                <!-- Form Container -->
                <form action="{{ route('compras.cotizaciones.store') }}" method="POST" class="flex flex-col flex-1 overflow-hidden">
                    @csrf
                    
                    <div class="space-y-3.5 py-3 overflow-y-auto pr-1 flex-1 custom-scrollbar">
                        
                        <!-- 1. Buscador Predictivo de Medicamento -->
                        <div class="relative z-30" @click.outside="prodModalDropdown = false">
                            <label class="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-1">
                                Medicamento <span class="text-rose-500">*</span>
                            </label>
                            <input type="hidden" name="producto_id" :value="cotizacionProdId" required>

                            <!-- Estado: Medicamento Seleccionado -->
                            <template x-if="cotizacionProdObj">
                                <div class="flex items-center justify-between p-2.5 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-300 dark:border-emerald-700 rounded-xl text-xs">
                                    <div class="min-w-0 pr-2">
                                        <div class="font-bold text-emerald-950 dark:text-emerald-200 truncate" x-text="cotizacionProdObj.nombre"></div>
                                        <div class="text-[10px] text-emerald-700 dark:text-emerald-400 flex items-center gap-2 mt-0.5">
                                            <span x-show="cotizacionProdObj.principio_activo" x-text="cotizacionProdObj.principio_activo"></span>
                                            <span x-show="cotizacionProdObj.laboratorio" class="text-slate-400" x-text="'• Lab: ' + cotizacionProdObj.laboratorio"></span>
                                        </div>
                                    </div>
                                    <button type="button" @click="deseleccionarProdModal()" class="text-[11px] font-bold text-rose-600 dark:text-rose-400 hover:underline shrink-0 cursor-pointer">
                                        Cambiar
                                    </button>
                                </div>
                            </template>

                            <!-- Estado: Input de Búsqueda de Medicamento -->
                            <template x-if="!cotizacionProdObj">
                                <div class="relative">
                                    <input type="text"
                                           id="cotizProdSearchInput"
                                           x-model="prodModalQuery"
                                           @focus="prodModalDropdown = true"
                                           @input="prodModalDropdown = true"
                                           placeholder="Escribe nombre, principio activo o código de medicamento..."
                                           class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-medium focus:ring-2 focus:ring-emerald-500">
                                    
                                    <div x-show="prodModalDropdown && filtrarProdsModal().length > 0"
                                         x-cloak
                                         class="absolute left-0 right-0 top-full mt-1 z-50 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-xl max-h-48 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-700/50">
                                        <template x-for="p in filtrarProdsModal()" :key="p.id">
                                            <button type="button" 
                                                    @click="seleccionarProdModal(p)"
                                                    class="w-full px-3.5 py-2 text-left hover:bg-emerald-50 dark:hover:bg-slate-700 flex items-center justify-between transition cursor-pointer">
                                                <div class="min-w-0 pr-2">
                                                    <span class="text-xs font-bold text-slate-800 dark:text-slate-100 block truncate" x-text="p.nombre"></span>
                                                    <span class="text-[10px] text-slate-400" x-text="p.principio_activo ? p.principio_activo + ' (' + p.laboratorio + ')' : p.laboratorio"></span>
                                                </div>
                                                <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 shrink-0">Elegir →</span>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- 2. Buscador Predictivo de Proveedor -->
                        <div class="relative z-20" @click.outside="provModalDropdown = false">
                            <label class="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-1">
                                Proveedor Distribuidor <span class="text-rose-500">*</span>
                            </label>
                            <input type="hidden" name="proveedor_id" :value="cotizacionProvId" required>

                            <!-- Estado: Proveedor Seleccionado -->
                            <template x-if="cotizacionProvObj">
                                <div class="flex items-center justify-between p-2.5 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-300 dark:border-emerald-700 rounded-xl text-xs">
                                    <div class="min-w-0 pr-2">
                                        <div class="font-bold text-emerald-950 dark:text-emerald-200 truncate" x-text="cotizacionProvObj.nombre"></div>
                                        <div class="text-[10px] text-emerald-700 dark:text-emerald-400 flex items-center gap-2 mt-0.5">
                                            <span x-show="cotizacionProvObj.ruc" x-text="'RUC: ' + cotizacionProvObj.ruc"></span>
                                            <span x-show="cotizacionProvObj.contacto" class="text-slate-400" x-text="'• ' + cotizacionProvObj.contacto"></span>
                                        </div>
                                    </div>
                                    <button type="button" @click="deseleccionarProvModal()" class="text-[11px] font-bold text-rose-600 dark:text-rose-400 hover:underline shrink-0 cursor-pointer">
                                        Cambiar
                                    </button>
                                </div>
                            </template>

                            <!-- Estado: Input de Búsqueda de Proveedor -->
                            <template x-if="!cotizacionProvObj">
                                <div class="relative">
                                    <input type="text"
                                           id="cotizProvSearchInput"
                                           x-model="provModalQuery"
                                           @focus="provModalDropdown = true"
                                           @input="provModalDropdown = true"
                                           placeholder="Escribe nombre o RUC del proveedor..."
                                           class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-medium focus:ring-2 focus:ring-emerald-500">
                                    
                                    <div x-show="provModalDropdown && filtrarProvsModal().length > 0"
                                         x-cloak
                                         class="absolute left-0 right-0 top-full mt-1 z-50 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-xl max-h-48 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-700/50">
                                        <template x-for="p in filtrarProvsModal()" :key="p.id">
                                            <button type="button" 
                                                    @click="seleccionarProvModal(p)"
                                                    class="w-full px-3.5 py-2 text-left hover:bg-emerald-50 dark:hover:bg-slate-700 flex items-center justify-between transition cursor-pointer">
                                                <div class="min-w-0 pr-2">
                                                    <span class="text-xs font-bold text-slate-800 dark:text-slate-100 block truncate" x-text="p.nombre"></span>
                                                    <span class="text-[10px] text-slate-400" x-text="p.contacto ? 'Contacto: ' + p.contacto : (p.ruc ? 'RUC: ' + p.ruc : '')"></span>
                                                </div>
                                                <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 shrink-0">Elegir →</span>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- 3. Presentación y Fecha -->
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Presentación / Empaque</label>
                                <select name="presentacion_id" x-model="cotizacionPresId" @change="actualizarUnidades()"
                                        class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-xs focus:ring-emerald-500 focus:border-emerald-500 font-medium">
                                    <option value="">Unidad Base (x1)</option>
                                    <template x-for="pres in cotizacionPresentaciones" :key="pres.id">
                                        <option :value="pres.id" x-text="`${pres.nombre} (x${pres.unidades_por_presentacion})`"></option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Fecha Cotización *</label>
                                <input type="date" name="fecha" value="{{ date('Y-m-d') }}" required
                                       class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-xs focus:ring-emerald-500 focus:border-emerald-500 font-medium">
                            </div>
                        </div>

                        <!-- 4. Precio Cotizado y Cálculo en Vivo -->
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Precio Cotizado por el Proveedor (C$) *</label>
                            <input type="number" step="0.0001" min="0.0001" name="precio_compra" x-model="cotizacionPrecio" required placeholder="0.00"
                                   class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-xs focus:ring-emerald-500 focus:border-emerald-500 font-bold">
                            
                            <!-- Previsualización en vivo del costo unitario base -->
                            <div class="mt-2 p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 flex items-center justify-between text-xs">
                                <span class="text-slate-500 dark:text-slate-400">Costo Unitario Base Calculado:</span>
                                <span class="font-mono font-bold text-emerald-900 dark:text-emerald-400">C$ <span x-text="precioUnitarioCalculado"></span></span>
                            </div>
                        </div>

                        <!-- 5. Observaciones -->
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Observaciones o Condiciones</label>
                            <textarea name="observaciones" rows="2" placeholder="Ej: Válido por 15 días, incluye flete..."
                                      class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-xs focus:ring-emerald-500 focus:border-emerald-500"></textarea>
                        </div>
                    </div>

                    <!-- Footer (Fixed) -->
                    <div class="flex items-center justify-end space-x-2 pt-3 border-t border-slate-100 dark:border-slate-800 shrink-0">
                        <button type="button" @click="modalCotizacion = false" 
                                class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer">
                            Cancelar
                        </button>
                        <button type="submit" 
                                :disabled="!cotizacionProdId || !cotizacionProvId"
                                :class="(!cotizacionProdId || !cotizacionProvId) ? 'opacity-50 cursor-not-allowed bg-slate-400' : 'bg-emerald-600 hover:bg-emerald-700 shadow-xs'"
                                class="px-4 py-2 rounded-xl text-xs font-semibold text-white transition cursor-pointer">
                            Guardar Cotización
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>

</div>
@endsection
