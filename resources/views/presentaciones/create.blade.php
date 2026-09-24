@extends('layouts.app')
@section('title', 'Nueva Presentacion - FarmaBien')
@push('scripts')
<script>
function presForm() {
    return {
        formLayout: localStorage.getItem('farmaFormViewMode') || 'modern',
        setLayout(mode) { this.formLayout=mode; localStorage.setItem('farmaFormViewMode',mode); window.dispatchEvent(new CustomEvent('farma:layout-changed',{detail:{mode}})); },
        productoId: null,
        productoQuery: '',
        productosFiltrados: [],
        showDropdown: false,
        nombre: '',
        descripcion: '',
        unidades: 1,
        precioCompra: '',
        precioVenta: '',
        codigoBarras: '',
        esUnidadBase: false,
        activo: true,
        orden: 0,
        productos: [],
        get margen() { const c=parseFloat(this.precioCompra)||0,v=parseFloat(this.precioVenta)||0; if(!c||!v)return null; return {valor:(v-c).toFixed(2),pct:(((v-c)/c)*100).toFixed(1)}; },
        init() {
            this.productos = window._presProductos || [];
            const oldId = this.$el.dataset.oldId;
            if (oldId) { const sel=this.productos.find(p=>String(p.id)===String(oldId)); if(sel){this.productoId=sel.id;this.productoQuery=sel.nombre;} }
        },
        filtrar() { const q=this.productoQuery.toLowerCase(); this.productosFiltrados=q?this.productos.filter(p=>p.nombre.toLowerCase().includes(q)).slice(0,12):this.productos.slice(0,8); this.showDropdown=true; },
        seleccionar(p) { this.productoId=p.id; this.productoQuery=p.nombre; this.showDropdown=false; },
        limpiar() { this.productoId=null;this.productoQuery='';this.nombre='';this.descripcion='';this.unidades=1;this.precioCompra='';this.precioVenta='';this.codigoBarras='';this.esUnidadBase=false;this.activo=true;this.orden=0; if(window.farmaClearDraft)window.farmaClearDraft(window.location.pathname); }
    };
}
</script>
@endpush
@section('content')
<script>window._presProductos = @json($productos->map(fn($p) => ['id' => $p->id, 'nombre' => $p->nombre])->values());</script>
<div
    x-data="presForm()"
    x-init="init()"
    :data-old-id="'{{ old('producto_id','') }}'"
    @keydown.window="if($event.key==='Escape'&&formLayout==='compact'){limpiar();}"
    :class="formLayout==='compact'?'w-full':'max-w-4xl mx-auto'"
    class="space-y-4 transition-all duration-200"
>
{{-- Breadcrumb & Toggle --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-1 border-b border-slate-300/80 dark:border-slate-800">
    <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 transition">Inicio</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('presentaciones.index') }}" class="hover:text-emerald-600 transition">Presentaciones</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-800 dark:text-slate-200 font-semibold">Nueva</span>
    </nav>
        <div class="flex items-center gap-2">
            <button type="button" 
                    @click="$dispatch('toggle-pos-fullscreen')"
                    title="Modo Pantalla Completa / Ocultar Barras"
                    class="px-2.5 py-1.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold transition flex items-center space-x-1.5 shrink-0 shadow-2xs cursor-pointer">
                <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                <span class="hidden sm:inline">Modo Full</span>
            </button>
            <div class="inline-flex items-center p-0.5 rounded-xl bg-slate-200/80 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-xs font-semibold shadow-2xs">
                <button type="button" @click="setLayout('modern')" :class="formLayout==='modern'?'bg-white dark:bg-slate-700 text-emerald-700 dark:text-emerald-400 shadow-xs font-bold':'text-slate-600 hover:text-slate-900 dark:text-slate-400'" class="px-2.5 py-1 rounded-lg transition flex items-center space-x-1.5 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    <span>Moderna</span>
                </button>
                <button type="button" @click="setLayout('compact')" :class="formLayout==='compact'?'bg-white dark:bg-slate-700 text-emerald-700 dark:text-emerald-400 shadow-xs font-bold':'text-slate-600 hover:text-slate-900 dark:text-slate-400'" class="px-2.5 py-1 rounded-lg transition flex items-center space-x-1.5 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <span>Compacta (POS / ERP)</span>
                </button>
            </div>
        </div>
</div>
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
    <div>
        <h1 class="text-lg sm:text-xl font-bold text-slate-900 dark:text-white">Nueva Presentacion de Producto</h1>
        <p class="text-xs text-slate-500 dark:text-slate-400">Caja, frasco, blister, ampolla...</p>
    </div>
    <a href="{{ route('presentaciones.index') }}" class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-xl transition shrink-0 shadow-2xs">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        <span>Volver a la lista</span>
    </a>
</div>
@if($errors->any())
<div class="px-4 py-3 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-300 text-xs text-rose-800 dark:text-rose-300">
    <ul class="list-disc list-inside space-y-0.5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif
<form method="POST" action="{{ route('presentaciones.store') }}">
@csrf
{{-- ===== COMPACTA ===== --}}
<template x-if="formLayout === 'compact'">
<div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-300 dark:border-slate-800 shadow-md p-4 space-y-4">
    {{-- Toolbar --}}
    <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800 bg-slate-100/80 dark:bg-slate-800/60 -mx-4 -mt-4 p-3 rounded-t-2xl">
        <div class="flex items-center space-x-2">
            <span class="w-2.5 h-2.5 rounded-full bg-indigo-500 shadow-xs"></span>
            <span class="text-xs font-extrabold text-slate-800 dark:text-slate-200 uppercase tracking-wide">FICHA RAPIDA DE PRESENTACION</span>
            <span class="text-[10px] px-2 py-0.5 rounded-md bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-mono font-bold">Esc = Limpiar</span>
        </div>
        <div class="flex items-center space-x-2">
            <button type="button" @click="limpiar()" class="px-3 py-1.5 rounded-lg border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-800 text-xs font-bold transition cursor-pointer">Limpiar (Esc)</button>
            <button type="submit" class="px-4 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-extrabold shadow-sm transition flex items-center space-x-1.5 cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>Guardar</span>
            </button>
        </div>
    </div>
    {{-- Grid 2 cols --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        {{-- Panel 1: Medicamento + Datos --}}
        <div class="p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/40 dark:bg-slate-800/30 space-y-3">
            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center space-x-1.5 border-b border-slate-200/60 dark:border-slate-700/60 pb-1.5">
                <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                <span>Medicamento y Datos Principales</span>
            </div>
            <input type="hidden" id="pres_compact_producto_id" name="producto_id" :value="productoId">
            <div>
                <label for="pres_compact_producto_query" class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Medicamento <span class="text-rose-500">*</span></label>
                <div class="relative">
                    <input type="text" id="pres_compact_producto_query" name="producto_busqueda" x-model="productoQuery" @focus="filtrar()" @input="filtrar()" @keydown.escape="showDropdown=false" placeholder="Buscar medicamento..." class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:ring-1 focus:ring-emerald-500">
                    <div x-show="showDropdown" @click.outside="showDropdown=false" class="absolute z-50 w-full mt-0.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-lg max-h-40 overflow-y-auto">
                        <template x-for="p in productosFiltrados" :key="p.id">
                            <button type="button" @click="seleccionar(p)" :class="productoId==p.id?'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 font-semibold':'text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700'" class="w-full text-left px-3 py-2 text-xs transition" x-text="p.nombre"></button>
                        </template>
                        <div x-show="productosFiltrados.length===0" class="px-3 py-2 text-xs text-slate-400">Sin resultados</div>
                    </div>
                </div>
                <p x-show="productoId" class="text-[10px] text-emerald-600 dark:text-emerald-400 mt-0.5 font-medium">Sel.: <span x-text="productoQuery"></span></p>
            </div>
            <div>
                <label for="pres_compact_nombre" class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Nombre <span class="text-rose-500">*</span></label>
                <input type="text" id="pres_compact_nombre" name="nombre" x-model="nombre" required placeholder="Caja x 30 tabs..." class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label for="pres_compact_unidades" class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Unidades <span class="text-rose-500">*</span></label>
                    <input type="number" id="pres_compact_unidades" name="unidades_por_presentacion" x-model="unidades" min="1" required class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                </div>
                <div>
                    <label for="pres_compact_codigo_barras" class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Cod. Barras</label>
                    <input type="text" id="pres_compact_codigo_barras" name="codigo_barras" x-model="codigoBarras" placeholder="EAN-13..." class="w-full font-mono px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                </div>
            </div>
            <div>
                <label for="pres_compact_descripcion" class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Descripcion</label>
                <textarea id="pres_compact_descripcion" name="descripcion" x-model="descripcion" rows="2" placeholder="Descripcion adicional..." class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500"></textarea>
            </div>
        </div>
        {{-- Panel 2: Precios + Estado --}}
        <div class="p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/40 dark:bg-slate-800/30 space-y-3">
            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center space-x-1.5 border-b border-slate-200/60 dark:border-slate-700/60 pb-1.5">
                <svg class="w-3.5 h-3.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Precios y Configuracion</span>
            </div>
            <div>
                <label for="pres_compact_precio_compra" class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Precio de Compra ($)</label>
                <input type="number" id="pres_compact_precio_compra" name="precio_compra" x-model="precioCompra" step="0.01" min="0" placeholder="0.00" class="w-full font-mono px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
            </div>
            <div>
                <label for="pres_compact_precio_venta" class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Precio de Venta ($)</label>
                <input type="number" id="pres_compact_precio_venta" name="precio_venta" x-model="precioVenta" step="0.01" min="0" placeholder="0.00" class="w-full font-mono px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
            </div>
            <div x-show="margen" class="px-2.5 py-2 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-xs font-semibold text-emerald-700 dark:text-emerald-300">
                Margen: $<span x-text="margen&&margen.valor"></span> (<span x-text="margen&&margen.pct"></span>%)
            </div>
            <div>
                <label for="pres_compact_orden" class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Orden de Visualizacion</label>
                <input type="number" id="pres_compact_orden" name="orden" x-model="orden" min="0" class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
            </div>
            <div class="pt-2 space-y-2">
                <input type="hidden" name="es_unidad_base" value="0">
                <label for="pres_compact_es_unidad_base" class="inline-flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" id="pres_compact_es_unidad_base" name="es_unidad_base" value="1" x-model="esUnidadBase" class="rounded border-slate-300 text-indigo-600 w-3.5 h-3.5">
                    <span class="text-[11px] font-semibold text-indigo-700 dark:text-indigo-400">Es Unidad Base</span>
                </label>
                <br>
                <input type="hidden" name="activo" value="0">
                <label for="pres_compact_activo" class="inline-flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" id="pres_compact_activo" name="activo" value="1" x-model="activo" class="rounded border-slate-300 text-emerald-600 w-3.5 h-3.5">
                    <span class="text-[11px] font-semibold text-emerald-700 dark:text-emerald-400">Presentacion Activa para POS</span>
                </label>
            </div>
        </div>
    </div>
    {{-- Footer Compacto --}}
    <div class="pt-2 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between text-xs text-slate-500">
        <span class="text-[11px]">Los datos se sincronizan automaticamente en borrador temporal.</span>
        <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs transition flex items-center space-x-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span>Guardar Presentacion</span>
        </button>
    </div>
</div>
</template>
{{-- ===== MODERNA ===== --}}
<template x-if="formLayout === 'modern'">
<div class="space-y-6">
    {{-- Medicamento --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
        <div class="border-b border-slate-100 dark:border-slate-800 pb-3"><h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center space-x-2"><span class="w-2 h-2 rounded-full bg-emerald-500"></span><span>Medicamento Asociado</span></h3></div>
        <div>
            <label for="pres_modern_producto_query" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Medicamento <span class="text-rose-500">*</span></label>
            <input type="hidden" id="pres_modern_producto_id" name="producto_id" :value="productoId">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg></div>
                <input type="text" id="pres_modern_producto_query" name="producto_busqueda" x-model="productoQuery" @focus="filtrar()" @input="filtrar()" @keydown.escape="showDropdown=false" placeholder="Buscar medicamento por nombre..." class="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition">
                <div x-show="showDropdown" @click.outside="showDropdown=false" class="absolute z-50 w-full mt-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-lg max-h-52 overflow-y-auto">
                    <template x-for="p in productosFiltrados" :key="p.id">
                        <button type="button" @click="seleccionar(p)" :class="productoId==p.id?'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 font-semibold':'text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700'" class="w-full text-left px-4 py-2.5 text-sm transition flex items-center justify-between">
                            <span x-text="p.nombre"></span>
                            <svg x-show="productoId==p.id" class="w-4 h-4 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        </button>
                    </template>
                    <div x-show="productosFiltrados.length===0" class="px-4 py-3 text-sm text-slate-400 text-center">Sin resultados</div>
                </div>
            </div>
            <p x-show="productoId" class="text-xs text-emerald-600 dark:text-emerald-400 mt-1.5 font-medium">Seleccionado: <strong x-text="productoQuery"></strong></p>
        </div>
    </div>
    {{-- Datos --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm space-y-5">
        <div class="border-b border-slate-100 dark:border-slate-800 pb-3"><h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center space-x-2"><span class="w-2 h-2 rounded-full bg-indigo-500"></span><span>Datos de la Presentacion</span></h3></div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label for="pres_modern_nombre" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Nombre <span class="text-rose-500">*</span></label>
                <input type="text" id="pres_modern_nombre" name="nombre" x-model="nombre" required placeholder="Caja x 30 Comprimidos, Frasco 500mL..." class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition @error('nombre') border-rose-500 @enderror">
                @error('nombre')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="pres_modern_unidades" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Unidades por Presentacion <span class="text-rose-500">*</span></label>
                <input type="number" id="pres_modern_unidades" name="unidades_por_presentacion" x-model="unidades" min="1" required class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition">
            </div>
            <div class="md:col-span-2">
                <label for="pres_modern_descripcion" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Descripcion</label>
                <textarea id="pres_modern_descripcion" name="descripcion" x-model="descripcion" rows="2" placeholder="Descripcion adicional..." class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition"></textarea>
            </div>
            <div>
                <label for="pres_modern_codigo_barras" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Codigo de Barras</label>
                <input type="text" id="pres_modern_codigo_barras" name="codigo_barras" x-model="codigoBarras" placeholder="EAN-13, UPC..." class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-mono text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition">
            </div>
            <div>
                <label for="pres_modern_orden" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Orden de Visualizacion</label>
                <input type="number" id="pres_modern_orden" name="orden" x-model="orden" min="0" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition">
            </div>
        </div>
    </div>
    {{-- Precios --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm space-y-5">
        <div class="border-b border-slate-100 dark:border-slate-800 pb-3"><h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center space-x-2"><span class="w-2 h-2 rounded-full bg-amber-500"></span><span>Precios</span></h3></div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label for="pres_modern_precio_compra" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Precio de Compra ($)</label>
                <input type="number" id="pres_modern_precio_compra" name="precio_compra" x-model="precioCompra" step="0.01" min="0" placeholder="0.00" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-mono text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition">
            </div>
            <div>
                <label for="pres_modern_precio_venta" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Precio de Venta ($)</label>
                <input type="number" id="pres_modern_precio_venta" name="precio_venta" x-model="precioVenta" step="0.01" min="0" placeholder="0.00" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-mono text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition">
            </div>
            <div x-show="margen" class="md:col-span-2 px-4 py-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-sm font-semibold text-emerald-700 dark:text-emerald-300 flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                <span>Margen bruto: <strong>$<span x-text="margen&&margen.valor"></span></strong> (<span x-text="margen&&margen.pct"></span>%)</span>
            </div>
        </div>
    </div>
    {{-- Estado --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
        <div class="border-b border-slate-100 dark:border-slate-800 pb-3"><h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center space-x-2"><span class="w-2 h-2 rounded-full bg-slate-500"></span><span>Configuracion</span></h3></div>
        <input type="hidden" name="es_unidad_base" value="0">
        <label for="pres_modern_es_unidad_base" class="flex items-center space-x-3 cursor-pointer">
            <input type="checkbox" id="pres_modern_es_unidad_base" name="es_unidad_base" value="1" x-model="esUnidadBase" class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
            <div>
                <span class="text-sm font-semibold text-slate-800 dark:text-slate-200 block">Es Unidad Base</span>
                <span class="text-xs text-slate-400">Unidad minima de venta del medicamento.</span>
            </div>
        </label>
        <input type="hidden" name="activo" value="0">
        <label for="pres_modern_activo" class="flex items-center space-x-3 cursor-pointer">
            <input type="checkbox" id="pres_modern_activo" name="activo" value="1" x-model="activo" class="w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
            <div>
                <span class="text-sm font-semibold text-slate-800 dark:text-slate-200 block">Presentacion Activa</span>
                <span class="text-xs text-slate-400">Disponible para ventas y compras.</span>
            </div>
        </label>
    </div>
    {{-- Sticky Footer --}}
    <div class="sticky bottom-0 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-3.5 bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border-t border-slate-200 dark:border-slate-800 shadow-lg z-20 flex items-center justify-between rounded-b-2xl">
        <a href="{{ route('presentaciones.index') }}" class="px-5 py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-xl transition">Cancelar</a>
        <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-sm font-semibold rounded-xl shadow-sm transition inline-flex items-center space-x-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span>Guardar Presentacion</span>
        </button>
    </div>
</div>
</template>
</form>
</div>
@endsection
