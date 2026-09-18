@extends('layouts.app')
@section('title', 'Editar Presentacion - FarmaBien')
@section('content')
<div x-data="{
    formLayout: localStorage.getItem('farmaFormViewMode') || 'modern',
    setLayout(mode) { this.formLayout = mode; localStorage.setItem('farmaFormViewMode', mode); window.dispatchEvent(new CustomEvent('farma:layout-changed', { detail: { mode } })); },
    formData: { producto_id: @js(old('producto_id', \->producto_id)), nombre: @js(old('nombre', \->nombre)), descripcion: @js(old('descripcion', \->descripcion ?? '')), unidades: @js(old('unidades_por_presentacion', \->unidades_por_presentacion)), precio_compra: @js(old('precio_compra', \->precio_compra ?? '')), precio_venta: @js(old('precio_venta', \->precio_venta ?? '')), codigo_barras: @js(old('codigo_barras', \->codigo_barras ?? '')), es_unidad_base: @js(old('es_unidad_base', \->es_unidad_base) ? true : false), activo: @js(old('activo', \->activo) ? true : false), orden: @js(old('orden', \->orden)) },
    productos: @json($productos->map(fn($p) => ['id' => $p->id, 'nombre' => $p->nombre])->values()),
    productoQuery: '',
    productosFiltrados: [],
    showDropdown: false,
    get margen() { const c=parseFloat(this.formData.precio_compra)||0,v=parseFloat(this.formData.precio_venta)||0; if(!c||!v)return null; return {valor:(v-c).toFixed(2),pct:(((v-c)/c)*100).toFixed(1)}; },
    initCombo() { const sel=this.productos.find(p=>p.id==this.formData.producto_id); if(sel)this.productoQuery=sel.nombre; this.productosFiltrados=this.productos.slice(0,8); },
    filtrarProductos() { const q=this.productoQuery.toLowerCase(); this.productosFiltrados=q?this.productos.filter(p=>p.nombre.toLowerCase().includes(q)).slice(0,12):this.productos.slice(0,8); this.showDropdown=true; },
    seleccionarProducto(p) { this.formData.producto_id=p.id; this.productoQuery=p.nombre; this.showDropdown=false; },
    limpiarFormulario() { this.formData={producto_id:'',nombre:'',descripcion:'',unidades:1,precio_compra:'',precio_venta:'',codigo_barras:'',es_unidad_base:false,activo:true,orden:0}; this.productoQuery=''; if(window.farmaClearDraft)window.farmaClearDraft('{{ request()->getPathInfo() }}'); }
}"
x-init="initCombo()"
@keydown.window="if($event.key==='Escape'&&formLayout==='compact'){limpiarFormulario();}"
:class="formLayout==='compact'?'w-full':'max-w-4xl mx-auto'"
class="space-y-4 transition-all duration-200">

{{-- Breadcrumb & Toggle --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-1 border-b border-slate-300/80 dark:border-slate-800">
    <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 transition">Inicio</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('presentaciones.index') }}" class="hover:text-emerald-600 transition">Presentaciones</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-800 dark:text-slate-200 font-semibold">Nueva</span>
    </nav>
    <div class="inline-flex items-center p-0.5 rounded-xl bg-slate-200/80 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-xs font-semibold shadow-2xs">
        <button type="button" @click="setLayout('modern')" :class="formLayout==='modern'?'bg-white dark:bg-slate-700 text-emerald-700 dark:text-emerald-400 shadow-xs font-bold':'text-slate-600 hover:text-slate-900 dark:text-slate-400'" class="px-2.5 py-1 rounded-lg transition flex items-center space-x-1 cursor-pointer">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
            <span>Moderna</span>
        </button>
        <button type="button" @click="setLayout('compact')" :class="formLayout==='compact'?'bg-white dark:bg-slate-700 text-emerald-700 dark:text-emerald-400 shadow-xs font-bold':'text-slate-600 hover:text-slate-900 dark:text-slate-400'" class="px-2.5 py-1 rounded-lg transition flex items-center space-x-1 cursor-pointer">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            <span>Compacta</span>
        </button>
    </div>
</div>
<div class="flex items-center justify-between gap-2">
    <div>
        <h1 class="text-lg sm:text-xl font-bold text-slate-900 dark:text-white">Editar Presentacion: {{ \->nombre }}</h1>
        <p class="text-xs text-slate-500 dark:text-slate-400">Modifica los datos de la presentacion de producto.</p>
    </div>
    <a href="{{ route('presentaciones.index') }}" class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-100 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-xl transition shrink-0">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        <span>Volver</span>
    </a>
</div>
@if($errors->any())
<div class="px-4 py-3 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-300 text-xs text-rose-800 dark:text-rose-300">
    <ul class="list-disc list-inside space-y-0.5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif
<form method="POST" action="{{ route('presentaciones.update', \) }}">
@csrf

{{-- ===== COMPACTA ===== --}}
<template x-if="formLayout === 'compact'">
<div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-300 dark:border-slate-800 shadow-md p-4 space-y-4 animate-fadeIn">
    <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800 bg-slate-100/80 dark:bg-slate-800/60 -mx-4 -mt-4 p-3 rounded-t-2xl">
        <div class="flex items-center space-x-2">
            <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
            <span class="text-xs font-extrabold text-slate-800 dark:text-slate-200 uppercase tracking-wide">EDITAR PRESENTACION</span>
            <span class="text-[10px] px-2 py-0.5 rounded-md bg-slate-200 dark:bg-slate-700 text-slate-700 font-mono font-bold">Esc = Limpiar</span>
        </div>
        <div class="flex items-center space-x-2">
            <button type="button" @click="limpiarFormulario()" class="px-3 py-1.5 rounded-lg border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-200 text-xs font-bold transition">Limpiar</button>
            <button type="submit" class="px-4 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-extrabold shadow-sm transition flex items-center space-x-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>Actualizar</span>
            </button>
        </div>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        {{-- Panel 1: Medicamento + nombre --}}
        <div class="p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/40 dark:bg-slate-800/30 space-y-3">
            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 flex items-center space-x-1.5 border-b border-slate-200/60 dark:border-slate-700/60 pb-1.5">
                <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                <span>Medicamento y Nombre</span>
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">Medicamento <span class="text-rose-500">*</span></label>
                <input type="hidden" name="producto_id" :value="formData.producto_id">
                <div class="relative">
                    <input type="text" x-model="productoQuery" @focus="filtrarProductos()" @input="filtrarProductos()" @keydown.escape="showDropdown=false" placeholder="Buscar medicamento..."
                           class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                    <div x-show="showDropdown" @click.outside="showDropdown=false" class="absolute z-50 w-full mt-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-lg max-h-44 overflow-y-auto">
                        <template x-for="p in productosFiltrados" :key="p.id">
                            <button type="button" @click="seleccionarProducto(p)" :class="formData.producto_id==p.id?'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 font-semibold':'text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700'" class="w-full text-left px-3 py-2 text-xs transition" x-text="p.nombre"></button>
                        </template>
                        <div x-show="productosFiltrados.length===0" class="px-3 py-2 text-xs text-slate-400 text-center">Sin resultados</div>
                    </div>
                </div>
                <p x-show="formData.producto_id" class="text-[10px] text-emerald-600 mt-0.5 font-medium">Sel.: <span x-text="productoQuery"></span></p>
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">Nombre <span class="text-rose-500">*</span></label>
                <input type="text" name="nombre" x-model="formData.nombre" required placeholder="Caja x 30 tabs..." class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">Unidades <span class="text-rose-500">*</span></label>
                    <input type="number" name="unidades_por_presentacion" x-model="formData.unidades" min="1" required class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">Orden</label>
                    <input type="number" name="orden" x-model="formData.orden" min="0" class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                </div>
            </div>
        </div>
        {{-- Panel 2: Precios --}}
        <div class="p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/40 dark:bg-slate-800/30 space-y-3">
            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 flex items-center space-x-1.5 border-b border-slate-200/60 dark:border-slate-700/60 pb-1.5">
                <svg class="w-3.5 h-3.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Precios</span>
            </div>
            <div><label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">P. Compra</label><input type="number" name="precio_compra" x-model="formData.precio_compra" step="0.01" min="0" placeholder="0.00" class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs font-mono text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500"></div>
            <div><label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">P. Venta</label><input type="number" name="precio_venta" x-model="formData.precio_venta" step="0.01" min="0" placeholder="0.00" class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs font-mono text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500"></div>
            <div x-show="margen" class="px-2.5 py-2 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-xs font-semibold text-emerald-700 dark:text-emerald-300">Margen: $<span x-text="margen?.valor"></span> (<span x-text="margen?.pct"></span>%)</div>
            <div><label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">Cod. Barras</label><input type="text" name="codigo_barras" x-model="formData.codigo_barras" placeholder="EAN-13..." class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs font-mono text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500"></div>
        </div>
        {{-- Panel 3: Estado --}}
        <div class="p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/40 dark:bg-slate-800/30 space-y-3">
            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 border-b border-slate-200/60 dark:border-slate-700/60 pb-1.5">Estado y Notas</div>
            <div><label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">Descripcion</label><textarea name="descripcion" x-model="formData.descripcion" rows="3" placeholder="Descripcion adicional..." class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500"></textarea></div>
            <input type="hidden" name="es_unidad_base" value="0">
            <label class="flex items-center space-x-2 cursor-pointer"><input type="checkbox" name="es_unidad_base" value="1" x-model="formData.es_unidad_base" class="rounded border-slate-300 text-indigo-600 w-3.5 h-3.5"><span class="text-[11px] font-semibold text-indigo-700 dark:text-indigo-400">Es Unidad Base</span></label>
            <input type="hidden" name="activo" value="0">
            <label class="flex items-center space-x-2 cursor-pointer"><input type="checkbox" name="activo" value="1" x-model="formData.activo" class="rounded border-slate-300 text-emerald-600 w-3.5 h-3.5"><span class="text-[11px] font-semibold text-emerald-700 dark:text-emerald-400">Presentacion Activa</span></label>
        </div>
    </div>
    <div class="pt-2 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between">
        <span class="text-[11px] text-slate-400">Datos en borrador temporal.</span>
        <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs transition flex items-center space-x-1.5"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg><span>Actualizar Presentacion</span></button>
    </div>
</div>
</template>

{{-- ===== MODERNA ===== --}}
<template x-if="formLayout === 'modern'">
<div class="space-y-6 animate-fadeIn">
    {{-- Medicamento --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
        <div class="border-b border-slate-100 dark:border-slate-800 pb-3"><h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center space-x-2"><span class="w-2 h-2 rounded-full bg-emerald-500"></span><span>Medicamento Asociado</span></h3></div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Medicamento <span class="text-rose-500">*</span></label>
            <input type="hidden" name="producto_id" :value="formData.producto_id">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg></div>
                <input type="text" x-model="productoQuery" @focus="filtrarProductos()" @input="filtrarProductos()" @keydown.escape="showDropdown=false" placeholder="Buscar medicamento por nombre..." class="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition">
                <div x-show="showDropdown" @click.outside="showDropdown=false" class="absolute z-50 w-full mt-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-lg max-h-52 overflow-y-auto">
                    <template x-for="p in productosFiltrados" :key="p.id">
                        <button type="button" @click="seleccionarProducto(p)" :class="formData.producto_id==p.id?'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 font-semibold':'text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700'" class="w-full text-left px-4 py-2.5 text-sm transition flex items-center justify-between">
                            <span x-text="p.nombre"></span>
                            <svg x-show="formData.producto_id==p.id" class="w-4 h-4 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        </button>
                    </template>
                    <div x-show="productosFiltrados.length===0" class="px-4 py-3 text-sm text-slate-400 text-center">Sin resultados</div>
                </div>
            </div>
            <p x-show="formData.producto_id" class="text-xs text-emerald-600 dark:text-emerald-400 mt-1.5 font-medium">Seleccionado: <strong x-text="productoQuery"></strong></p>
        </div>
    </div>

    {{-- Datos --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm space-y-5">
        <div class="border-b border-slate-100 dark:border-slate-800 pb-3"><h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center space-x-2"><span class="w-2 h-2 rounded-full bg-indigo-500"></span><span>Datos de la Presentacion</span></h3></div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Nombre <span class="text-rose-500">*</span></label>
                <input type="text" name="nombre" x-model="formData.nombre" required placeholder="Caja x 30 Comprimidos, Frasco 500mL..." class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition @error('nombre') border-rose-500 @enderror">
                @error('nombre')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Unidades por Presentacion <span class="text-rose-500">*</span></label>
                <input type="number" name="unidades_por_presentacion" x-model="formData.unidades" min="1" required class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Descripcion</label>
                <textarea name="descripcion" x-model="formData.descripcion" rows="2" placeholder="Descripcion adicional..." class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition"></textarea>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Codigo de Barras</label>
                <input type="text" name="codigo_barras" x-model="formData.codigo_barras" placeholder="EAN-13, UPC..." class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-mono text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Orden</label>
                <input type="number" name="orden" x-model="formData.orden" min="0" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition">
            </div>
        </div>
    </div>

    {{-- Precios --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm space-y-5">
        <div class="border-b border-slate-100 dark:border-slate-800 pb-3"><h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center space-x-2"><span class="w-2 h-2 rounded-full bg-amber-500"></span><span>Precios</span></h3></div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div><label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Precio de Compra ($)</label><input type="number" name="precio_compra" x-model="formData.precio_compra" step="0.01" min="0" placeholder="0.00" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-mono text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition"></div>
            <div><label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Precio de Venta ($)</label><input type="number" name="precio_venta" x-model="formData.precio_venta" step="0.01" min="0" placeholder="0.00" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-mono text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition"></div>
            <div x-show="margen" class="md:col-span-2 px-4 py-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-sm font-semibold text-emerald-700 dark:text-emerald-300 flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                <span>Margen bruto: <strong>$<span x-text="margen?.valor"></span></strong> (<span x-text="margen?.pct"></span>%)</span>
            </div>
        </div>
    </div>

    {{-- Estado --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
        <input type="hidden" name="es_unidad_base" value="0">
        <label class="flex items-center space-x-3 cursor-pointer"><input type="checkbox" name="es_unidad_base" value="1" x-model="formData.es_unidad_base" class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"><div><span class="text-sm font-semibold text-slate-800 dark:text-slate-200 block">Es Unidad Base</span><span class="text-xs text-slate-400">Unidad minima de venta del medicamento.</span></div></label>
        <input type="hidden" name="activo" value="0">
        <label class="flex items-center space-x-3 cursor-pointer"><input type="checkbox" name="activo" value="1" x-model="formData.activo" class="w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"><div><span class="text-sm font-semibold text-slate-800 dark:text-slate-200 block">Presentacion Activa</span><span class="text-xs text-slate-400">Disponible para ventas y compras.</span></div></label>
    </div>

    {{-- Sticky Footer --}}
    <div class="sticky bottom-0 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-3.5 bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border-t border-slate-200 dark:border-slate-800 shadow-lg z-20 flex items-center justify-between rounded-b-2xl">
        <a href="{{ route('presentaciones.index') }}" class="px-5 py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-xl transition">Cancelar</a>
        <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl shadow-sm transition inline-flex items-center space-x-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span>Actualizar Presentacion</span>
        </button>
    </div>
</div>
</template>

</form>
</div>
@endsection

