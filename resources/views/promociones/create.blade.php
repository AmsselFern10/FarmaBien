@extends('layouts.app')

@section('title', 'Nueva Promoción - FarmaBien')

@section('content')
<div x-data="{
    formLayout: localStorage.getItem('farmaFormViewMode') || 'modern',
    setLayout(mode) {
        this.formLayout = mode;
        localStorage.setItem('farmaFormViewMode', mode);
        window.dispatchEvent(new CustomEvent('farma:layout-changed', { detail: { mode } }));
    },
    formData: (window.farmaGetDraft ? window.farmaGetDraft('{{ request()->getPathInfo() }}', {
        nombre: @js(old('nombre', '')),
        descripcion: @js(old('descripcion', '')),
        tipo: @js(old('tipo', 'porcentaje')),
        valor: @js(old('valor', 15)),
        min_unidades: @js(old('min_unidades', 1)),
        stock_limite: @js(old('stock_limite', '')),
        alcance: @js(old('alcance', 'producto')),
        producto_id: @js(old('producto_id', '')),
        categoria_id: @js(old('categoria_id', '')),
        laboratorio_id: @js(old('laboratorio_id', '')),
        fecha_inicio: @js(old('fecha_inicio', now()->format('Y-m-d\TH:i'))),
        fecha_fin: @js(old('fecha_fin', now()->addDays(15)->format('Y-m-d\TH:i'))),
        activo: @js(old('activo', 1) ? true : false)
    }) : {
        nombre: @js(old('nombre', '')),
        descripcion: @js(old('descripcion', '')),
        tipo: @js(old('tipo', 'porcentaje')),
        valor: @js(old('valor', 15)),
        min_unidades: @js(old('min_unidades', 1)),
        stock_limite: @js(old('stock_limite', '')),
        alcance: @js(old('alcance', 'producto')),
        producto_id: @js(old('producto_id', '')),
        categoria_id: @js(old('categoria_id', '')),
        laboratorio_id: @js(old('laboratorio_id', '')),
        fecha_inicio: @js(old('fecha_inicio', now()->format('Y-m-d\TH:i'))),
        fecha_fin: @js(old('fecha_fin', now()->addDays(15)->format('Y-m-d\TH:i'))),
        activo: @js(old('activo', 1) ? true : false)
    }),
    samplePrice: 20.00,
    updateTipo(newTipo) {
        this.formData.tipo = newTipo;
        if (newTipo === '2x1') {
            this.formData.min_unidades = 2;
            this.formData.valor = 50;
        } else if (newTipo === '3x2') {
            this.formData.min_unidades = 3;
            this.formData.valor = 33.33;
        }
    },
    calculateDiscount(qty = 1) {
        const val = parseFloat(this.formData.valor) || 0;
        const price = parseFloat(this.samplePrice) || 0;
        if (this.formData.tipo === 'porcentaje') {
            return (price * (val / 100)) * qty;
        } else if (this.formData.tipo === 'monto_fijo') {
            return Math.min(price, val) * qty;
        } else if (this.formData.tipo === '2x1') {
            return Math.floor(qty / 2) * price;
        } else if (this.formData.tipo === '3x2') {
            return Math.floor(qty / 3) * price;
        }
        return 0;
    },
    limpiarFormulario() {
        this.formData = {
            nombre: '',
            descripcion: '',
            tipo: 'porcentaje',
            valor: 15,
            min_unidades: 1,
            stock_limite: '',
            alcance: 'producto',
            producto_id: '',
            categoria_id: '',
            laboratorio_id: '',
            fecha_inicio: '{{ now()->format('Y-m-d\TH:i') }}',
            fecha_fin: '{{ now()->addDays(15)->format('Y-m-d\TH:i') }}',
            activo: true
        };
        if (window.farmaClearDraft) {
            window.farmaClearDraft('{{ request()->getPathInfo() }}');
        }
    }
}"
@keydown.window="if ($event.key === 'Escape' && formLayout === 'compact') { limpiarFormulario(); }"
:class="formLayout === 'compact' ? 'w-full' : 'max-w-6xl mx-auto'"
class="space-y-4 transition-all duration-200">

    <!-- Fila 1: Breadcrumb Únicamente -->
    <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('promociones.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Promociones</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-800 dark:text-slate-200 font-semibold">Nueva Promoción</span>
    </nav>

    <!-- Fila 2: Header & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <span>Registrar Nueva Promoción o Descuento</span>
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Configura reglas automáticas de descuento para el Punto de Venta (POS) y Catálogos.
            </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap shrink-0">
            <!-- 1. Volver -->
            <a href="{{ route('promociones.index') }}" 
               class="inline-flex items-center space-x-1.5 px-3.5 py-1.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold shadow-2xs transition">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Promociones</span>
            </a>

            <!-- 2. Botón Modo Full -->
            <button type="button" 
                    @click="$dispatch('toggle-pos-fullscreen')"
                    title="Modo Pantalla Completa / Ocultar Barras"
                    class="inline-flex items-center space-x-1.5 px-3.5 py-1.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold shadow-2xs transition cursor-pointer">
                <svg class="w-4 h-4 text-emerald-900 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                <span x-text="posFullscreen ? 'Salir Full' : 'Modo Full'">Modo Full</span>
            </button>

            <!-- 3. Botón Principal Guardar -->
            <button type="button" 
                    onclick="document.querySelector('form[action*=\'promociones\']').submit()"
                    class="inline-flex items-center space-x-1.5 px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-semibold rounded-xl shadow-xs transition cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>Guardar Promoción</span>
            </button>

            <!-- 4. Toggle Diseño Moderna / Compacta -->
            <div class="inline-flex items-center p-0.5 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-semibold shadow-2xs">
                <button type="button" 
                        @click="setLayout('modern')"
                        :class="formLayout === 'modern' ? 'bg-white dark:bg-slate-700 text-emerald-950 dark:text-emerald-400 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200'"
                        class="px-2.5 py-1 rounded-lg transition flex items-center space-x-1.5 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    <span>Moderna</span>
                </button>
                <button type="button" 
                        @click="setLayout('compact')"
                        :class="formLayout === 'compact' ? 'bg-white dark:bg-slate-700 text-emerald-950 dark:text-emerald-400 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200'"
                        class="px-2.5 py-1 rounded-lg transition flex items-center space-x-1.5 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <span>Compacta</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Errors -->
    @if($errors->any())
    <div class="p-3.5 rounded-xl bg-rose-50 dark:bg-rose-950/60 border border-rose-300 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-xs shadow-xs">
        <p class="font-bold mb-1 flex items-center gap-1.5">
            <svg class="w-4 h-4 text-red-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            Por favor corrige los siguientes errores:
        </p>
        <ul class="list-disc list-inside space-y-0.5 ml-1">
            @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
        </ul>
    </div>
    @endif

    <!-- ========================================== -->
    <!-- MODO 1: VISTA COMPACTA (POS / ERP RÁPIDA)  -->
    <!-- ========================================== -->
    <div x-show="formLayout === 'compact'" x-cloak class="space-y-3">
        <form action="{{ route('promociones.store') }}" method="POST" class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-300 dark:border-slate-800 shadow-sm p-4 space-y-4">
            @csrf

            <!-- Toolbar Superior -->
            <div class="flex items-center justify-between pb-3 border-b border-slate-300 dark:border-slate-800 bg-slate-100/80 dark:bg-slate-800/60 -mx-4 -mt-4 p-3 rounded-t-2xl">
                <div class="flex items-center space-x-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shadow-xs"></span>
                    <span class="text-xs font-extrabold text-slate-800 dark:text-slate-200 uppercase tracking-wide">FICHA RÁPIDA DE PROMOCIÓN & BENEFICIO</span>
                    <span class="text-[10px] px-2 py-0.5 rounded-md bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-mono font-bold">Esc = Limpiar</span>
                </div>

                <div class="flex items-center space-x-2">
                    <button type="button" 
                            @click="limpiarFormulario()"
                            class="px-3 py-1.5 rounded-lg border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-800 text-xs font-bold transition cursor-pointer">
                        Limpiar (Esc)
                    </button>
                    <button type="submit" 
                            class="px-4 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-extrabold shadow-sm transition flex items-center space-x-1.5 cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Guardar Promoción</span>
                    </button>
                </div>
            </div>

            <!-- Grid Compacta 2 Columnas -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
                
                <!-- Panel Izquierdo (7 cols): Datos de Oferta & Reglas -->
                <div class="lg:col-span-7 p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/40 dark:bg-slate-800/30 space-y-3">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center space-x-1.5 border-b border-slate-200/60 dark:border-slate-700/60 pb-1.5">
                        <svg class="w-3.5 h-3.5 text-emerald-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                        <span>1. Nombre y Mecánica de Descuento</span>
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Nombre de la Promoción <span class="text-red-900">*</span>
                        </label>
                        <input type="text" 
                               name="nombre" 
                               x-model="formData.nombre" 
                               required 
                               maxlength="150"
                               placeholder="Ej: 20% en Antigripales, 2x1 Vitaminas C, $2.00 Descuento Genéricos..."
                               class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs font-medium text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                    </div>

                    <!-- Selector de Tipo en Chips -->
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Tipo de Beneficio <span class="text-red-900">*</span>
                        </label>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-1.5">
                            <button type="button" 
                                    @click="updateTipo('porcentaje')"
                                    :class="formData.tipo === 'porcentaje' ? 'bg-emerald-600 text-white font-bold' : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-700'"
                                    class="py-1.5 px-2 rounded-lg text-[11px] transition flex items-center justify-center space-x-1 cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                <span>% Porcentual</span>
                            </button>
                            <button type="button" 
                                    @click="updateTipo('monto_fijo')"
                                    :class="formData.tipo === 'monto_fijo' ? 'bg-emerald-600 text-white font-bold' : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-700'"
                                    class="py-1.5 px-2 rounded-lg text-[11px] transition flex items-center justify-center space-x-1 cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>$ Monto Fijo</span>
                            </button>
                            <button type="button" 
                                    @click="updateTipo('2x1')"
                                    :class="formData.tipo === '2x1' ? 'bg-indigo-600 text-white font-bold' : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-700'"
                                    class="py-1.5 px-2 rounded-lg text-[11px] transition flex items-center justify-center space-x-1 cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/></svg>
                                <span>2x1 Combo</span>
                            </button>
                            <button type="button" 
                                    @click="updateTipo('3x2')"
                                    :class="formData.tipo === '3x2' ? 'bg-indigo-600 text-white font-bold' : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-700'"
                                    class="py-1.5 px-2 rounded-lg text-[11px] transition flex items-center justify-center space-x-1 cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                <span>3x2 Pack</span>
                            </button>
                        </div>
                        <input type="hidden" name="tipo" :value="formData.tipo">
                    </div>

                    <!-- Valores Financieros y Límites -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                <span x-show="formData.tipo === 'porcentaje'">Descuento (%)</span>
                                <span x-show="formData.tipo === 'monto_fijo'">Descuento ($)</span>
                                <span x-show="formData.tipo === '2x1' || formData.tipo === '3x2'">Valor Equivalente</span>
                                <span class="text-red-900">*</span>
                            </label>
                            <div class="relative">
                                <input type="number" 
                                       step="0.01" 
                                       min="0" 
                                       name="valor" 
                                       x-model="formData.valor" 
                                       required
                                       class="w-full px-2.5 py-1.5 font-mono font-bold bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                                <span class="absolute right-2.5 top-1.5 text-[11px] font-bold text-slate-400" x-text="formData.tipo === 'porcentaje' ? '%' : '$'"></span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Mínimo Unidades <span class="text-red-900">*</span>
                            </label>
                            <input type="number" 
                                   min="1" 
                                   name="min_unidades" 
                                   x-model="formData.min_unidades" 
                                   required
                                   class="w-full px-2.5 py-1.5 font-mono bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Tope Stock (Opcional)
                            </label>
                            <input type="number" 
                                   min="1" 
                                   name="stock_limite" 
                                   x-model="formData.stock_limite" 
                                   placeholder="Ilimitado"
                                   class="w-full px-2.5 py-1.5 font-mono bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                        </div>
                    </div>

                    <!-- Mini Live Simulation Badge -->
                    <div class="p-2.5 rounded-lg bg-emerald-50/80 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 flex items-center justify-between text-xs">
                        <div class="flex items-center space-x-2">
                            <span class="text-emerald-900 dark:text-emerald-300 font-bold text-[11px]">Simulación POS (Base $20):</span>
                            <span class="text-slate-700 dark:text-slate-300 text-[11px]">
                                1 unidad: <strong class="text-emerald-900 dark:text-emerald-400" x-text="'$' + (20.00 - calculateDiscount(1)).toFixed(2)"></strong>
                            </span>
                        </div>
                        <span class="text-[11px] font-extrabold px-2 py-0.5 rounded bg-emerald-200 dark:bg-emerald-900/60 text-emerald-800 dark:text-emerald-200"
                              x-text="'Ahorro: $' + calculateDiscount(formData.min_unidades).toFixed(2)">
                        </span>
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Descripción / Nota Interna
                        </label>
                        <input type="text" 
                               name="descripcion" 
                               x-model="formData.descripcion" 
                               maxlength="1000"
                               placeholder="Detalles u observaciones de la campaña..."
                               class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                    </div>
                </div>

                <!-- Panel Derecho (5 cols): Alcance, Fechas y Estado -->
                <div class="lg:col-span-5 p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/40 dark:bg-slate-800/30 space-y-3">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center space-x-1.5 border-b border-slate-200/60 dark:border-slate-700/60 pb-1.5">
                        <svg class="w-3.5 h-3.5 text-emerald-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>2. Alcance & Vigencia</span>
                    </div>

                    <!-- Selector de Alcance -->
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Alcance de Aplicación <span class="text-red-900">*</span>
                        </label>
                        <div class="grid grid-cols-2 gap-1.5">
                            <label class="flex items-center space-x-1.5 p-1.5 rounded-lg border cursor-pointer text-xs"
                                   :class="formData.alcance === 'producto' ? 'border-emerald-500 bg-emerald-50/60 dark:bg-emerald-950/40 text-emerald-900 dark:text-emerald-200 font-bold' : 'border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300'">
                                <input type="radio" name="alcance" value="producto" x-model="formData.alcance" class="sr-only">
                                <span>Por Medicamento</span>
                            </label>
                            <label class="flex items-center space-x-1.5 p-1.5 rounded-lg border cursor-pointer text-xs"
                                   :class="formData.alcance === 'categoria' ? 'border-emerald-500 bg-emerald-50/60 dark:bg-emerald-950/40 text-emerald-900 dark:text-emerald-200 font-bold' : 'border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300'">
                                <input type="radio" name="alcance" value="categoria" x-model="formData.alcance" class="sr-only">
                                <span>Por Categoría</span>
                            </label>
                            <label class="flex items-center space-x-1.5 p-1.5 rounded-lg border cursor-pointer text-xs"
                                   :class="formData.alcance === 'laboratorio' ? 'border-emerald-500 bg-emerald-50/60 dark:bg-emerald-950/40 text-emerald-900 dark:text-emerald-200 font-bold' : 'border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300'">
                                <input type="radio" name="alcance" value="laboratorio" x-model="formData.alcance" class="sr-only">
                                <span>Por Laboratorio</span>
                            </label>
                            <label class="flex items-center space-x-1.5 p-1.5 rounded-lg border cursor-pointer text-xs"
                                   :class="formData.alcance === 'general' ? 'border-emerald-500 bg-emerald-50/60 dark:bg-emerald-950/40 text-emerald-900 dark:text-emerald-200 font-bold' : 'border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300'">
                                <input type="radio" name="alcance" value="general" x-model="formData.alcance" class="sr-only">
                                <span>Catálogo Global</span>
                            </label>
                        </div>
                    </div>

                    <!-- Target Selector Dinámico -->
                    <div x-show="formData.alcance === 'producto'" x-cloak>
                        <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Seleccionar Medicamento <span class="text-red-900">*</span>
                        </label>
                        <select name="producto_id" x-model="formData.producto_id" class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                            <option value="">-- Selecciona medicamento --</option>
                            @foreach($productos as $prod)
                            <option value="{{ $prod->id }}">{{ $prod->nombre }} {{ $prod->concentracion }} (${{ number_format($prod->precio_venta, 2) }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div x-show="formData.alcance === 'categoria'" x-cloak>
                        <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Seleccionar Categoría <span class="text-red-900">*</span>
                        </label>
                        <select name="categoria_id" x-model="formData.categoria_id" class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                            <option value="">-- Selecciona categoría --</option>
                            @foreach($categorias as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div x-show="formData.alcance === 'laboratorio'" x-cloak>
                        <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Seleccionar Laboratorio <span class="text-red-900">*</span>
                        </label>
                        <select name="laboratorio_id" x-model="formData.laboratorio_id" class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                            <option value="">-- Selecciona laboratorio --</option>
                            @foreach($laboratorios as $lab)
                            <option value="{{ $lab->id }}">{{ $lab->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Fechas de Vigencia -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Fecha Inicio <span class="text-red-900">*</span>
                            </label>
                            <input type="datetime-local" 
                                   name="fecha_inicio" 
                                   x-model="formData.fecha_inicio" 
                                   required
                                   class="w-full px-2.5 py-1.5 font-mono bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Fecha Fin <span class="text-red-900">*</span>
                            </label>
                            <input type="datetime-local" 
                                   name="fecha_fin" 
                                   x-model="formData.fecha_fin" 
                                   required
                                   class="w-full px-2.5 py-1.5 font-mono bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                        </div>
                    </div>

                    <div class="pt-1">
                        <label class="inline-flex items-center space-x-2 cursor-pointer text-xs">
                            <input type="checkbox" name="activo" x-model="formData.activo" value="1" class="rounded border-slate-300 text-emerald-900 w-3.5 h-3.5">
                            <span class="text-[11px] font-semibold text-emerald-900 dark:text-emerald-400">Activar campaña inmediatamente para ventas</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Footer Compacto -->
            <div class="pt-2 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between text-xs text-slate-500">
                <span class="text-[11px]">Borrador guardado automáticamente en memoria local.</span>
                <div class="flex items-center gap-3">
                    <label class="inline-flex items-center gap-2 cursor-pointer select-none text-slate-700 dark:text-slate-300 text-xs font-medium">
                        <input type="checkbox" name="crear_otro" value="1"
                               {{ configuracion('interfaz_mantener_en_crear') ? 'checked' : '' }}
                               class="rounded border-slate-300 text-emerald-900 focus:ring-emerald-500">
                        <span>Guardar y crear otra</span>
                    </label>
                    <button type="submit" 
                            class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs transition flex items-center space-x-1.5 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Guardar Promoción</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- ========================================== -->
    <!-- MODO 2: VISTA MODERNA (ESTÁNDAR COMPLETA) -->
    <!-- ========================================== -->
    <div x-show="formLayout === 'modern'" x-cloak class="space-y-4">
        <form action="{{ route('promociones.store') }}" method="POST" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <!-- Columna Izquierda / Central: Formulario de Configuración -->
                <div class="lg:col-span-2 space-y-4">
                    <!-- Tarjeta 1: Información Básica -->
                    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs p-5 space-y-4">
                        <h2 class="text-sm font-bold text-slate-800 dark:text-slate-200 border-b border-slate-100 dark:border-slate-800 pb-2 flex items-center gap-2">
                            <span class="flex items-center justify-center w-5 h-5 rounded-full bg-emerald-100 dark:bg-emerald-950 text-emerald-900 dark:text-emerald-300 text-xs font-bold">1</span>
                            Información General de la Campaña
                        </h2>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                    Nombre de la Promoción <span class="text-red-900">*</span>
                                </label>
                                <input type="text" name="nombre" x-model="formData.nombre" required maxlength="150"
                                       placeholder="Ej: Descuento de Temporada Antigripales, 2x1 Vitaminas C, 15% Laboratorios Bayer..."
                                       class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm focus:ring-emerald-500 focus:border-emerald-500">
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                    Descripción o Justificación Comercial
                                </label>
                                <textarea name="descripcion" x-model="formData.descripcion" rows="2" maxlength="1000"
                                          placeholder="Breve descripción de la campaña u oferta visible en reportes..."
                                          class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-xs focus:ring-emerald-500 focus:border-emerald-500"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Tarjeta 2: Tipo de Beneficio y Descuento -->
                    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs p-5 space-y-4">
                        <h2 class="text-sm font-bold text-slate-800 dark:text-slate-200 border-b border-slate-100 dark:border-slate-800 pb-2 flex items-center gap-2">
                            <span class="flex items-center justify-center w-5 h-5 rounded-full bg-emerald-100 dark:bg-emerald-950 text-emerald-900 dark:text-emerald-300 text-xs font-bold">2</span>
                            Tipo de Descuento y Reglas Financieras
                        </h2>

                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            <label class="flex flex-col items-center p-3 rounded-xl border cursor-pointer transition text-center"
                                   :class="formData.tipo === 'porcentaje' ? 'border-emerald-500 bg-emerald-50/60 dark:bg-emerald-950/40 text-emerald-900 dark:text-emerald-200 font-bold shadow-xs' : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/60 text-slate-700 dark:text-slate-400'">
                                <input type="radio" name="tipo" value="porcentaje" class="sr-only" @click="updateTipo('porcentaje')">
                                <svg class="w-5 h-5 mb-1 text-emerald-900 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                <span class="text-xs">Porcentual (%)</span>
                            </label>

                            <label class="flex flex-col items-center p-3 rounded-xl border cursor-pointer transition text-center"
                                   :class="formData.tipo === 'monto_fijo' ? 'border-emerald-500 bg-emerald-50/60 dark:bg-emerald-950/40 text-emerald-900 dark:text-emerald-200 font-bold shadow-xs' : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/60 text-slate-700 dark:text-slate-400'">
                                <input type="radio" name="tipo" value="monto_fijo" class="sr-only" @click="updateTipo('monto_fijo')">
                                <svg class="w-5 h-5 mb-1 text-emerald-900 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span class="text-xs">Monto Fijo ($)</span>
                            </label>

                            <label class="flex flex-col items-center p-3 rounded-xl border cursor-pointer transition text-center"
                                   :class="formData.tipo === '2x1' ? 'border-indigo-500 bg-indigo-50/60 dark:bg-indigo-950/40 text-indigo-900 dark:text-indigo-200 font-bold shadow-xs' : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/60 text-slate-700 dark:text-slate-400'">
                                <input type="radio" name="tipo" value="2x1" class="sr-only" @click="updateTipo('2x1')">
                                <svg class="w-5 h-5 mb-1 text-indigo-900 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/></svg>
                                <span class="text-xs">2x1 Combo</span>
                            </label>

                            <label class="flex flex-col items-center p-3 rounded-xl border cursor-pointer transition text-center"
                                   :class="formData.tipo === '3x2' ? 'border-indigo-500 bg-indigo-50/60 dark:bg-indigo-950/40 text-indigo-900 dark:text-indigo-200 font-bold shadow-xs' : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/60 text-slate-700 dark:text-slate-400'">
                                <input type="radio" name="tipo" value="3x2" class="sr-only" @click="updateTipo('3x2')">
                                <svg class="w-5 h-5 mb-1 text-indigo-900 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                <span class="text-xs">3x2 Pack</span>
                            </label>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                    <span x-show="formData.tipo === 'porcentaje'">Porcentaje de Descuento (%)</span>
                                    <span x-show="formData.tipo === 'monto_fijo'">Monto de Descuento ($)</span>
                                    <span x-show="formData.tipo === '2x1' || formData.tipo === '3x2'">Valor Equivalente</span>
                                    <span class="text-red-900">*</span>
                                </label>
                                <div class="relative">
                                    <input type="number" step="0.01" min="0" name="valor" x-model="formData.valor" required
                                           class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm font-bold focus:ring-emerald-500 focus:border-emerald-500">
                                    <span class="absolute right-3.5 top-2.5 text-xs font-bold text-slate-400" x-text="formData.tipo === 'porcentaje' ? '%' : '$'"></span>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                    Mínimo de Unidades <span class="text-red-900">*</span>
                                </label>
                                <input type="number" min="1" name="min_unidades" x-model="formData.min_unidades" required
                                       class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm focus:ring-emerald-500 focus:border-emerald-500">
                                <p class="text-[10px] text-slate-400 mt-0.5">Cantidad mínima para aplicar oferta.</p>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                    Límite de Unidades (Opcional)
                                </label>
                                <input type="number" min="1" name="stock_limite" x-model="formData.stock_limite"
                                       placeholder="Ilimitado"
                                       class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm focus:ring-emerald-500 focus:border-emerald-500">
                                <p class="text-[10px] text-slate-400 mt-0.5">Tope total de unidades en promoción.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Tarjeta 3: Alcance y Destino -->
                    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs p-5 space-y-4">
                        <h2 class="text-sm font-bold text-slate-800 dark:text-slate-200 border-b border-slate-100 dark:border-slate-800 pb-2 flex items-center gap-2">
                            <span class="flex items-center justify-center w-5 h-5 rounded-full bg-emerald-100 dark:bg-emerald-950 text-emerald-900 dark:text-emerald-300 text-xs font-bold">3</span>
                            Alcance de Aplicación
                        </h2>

                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            <label class="flex items-center space-x-2 p-2.5 rounded-xl border cursor-pointer transition"
                                   :class="formData.alcance === 'producto' ? 'border-emerald-500 bg-emerald-50/50 dark:bg-emerald-950/30 font-semibold text-emerald-800 dark:text-emerald-300 shadow-xs' : 'border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-400'">
                                <input type="radio" name="alcance" value="producto" x-model="formData.alcance" class="text-emerald-900 focus:ring-emerald-500">
                                <span class="text-xs">Por Medicamento</span>
                            </label>

                            <label class="flex items-center space-x-2 p-2.5 rounded-xl border cursor-pointer transition"
                                   :class="formData.alcance === 'categoria' ? 'border-emerald-500 bg-emerald-50/50 dark:bg-emerald-950/30 font-semibold text-emerald-800 dark:text-emerald-300 shadow-xs' : 'border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-400'">
                                <input type="radio" name="alcance" value="categoria" x-model="formData.alcance" class="text-emerald-900 focus:ring-emerald-500">
                                <span class="text-xs">Por Categoría</span>
                            </label>

                            <label class="flex items-center space-x-2 p-2.5 rounded-xl border cursor-pointer transition"
                                   :class="formData.alcance === 'laboratorio' ? 'border-emerald-500 bg-emerald-50/50 dark:bg-emerald-950/30 font-semibold text-emerald-800 dark:text-emerald-300 shadow-xs' : 'border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-400'">
                                <input type="radio" name="alcance" value="laboratorio" x-model="formData.alcance" class="text-emerald-900 focus:ring-emerald-500">
                                <span class="text-xs">Por Laboratorio</span>
                            </label>

                            <label class="flex items-center space-x-2 p-2.5 rounded-xl border cursor-pointer transition"
                                   :class="formData.alcance === 'general' ? 'border-emerald-500 bg-emerald-50/50 dark:bg-emerald-950/30 font-semibold text-emerald-800 dark:text-emerald-300 shadow-xs' : 'border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-400'">
                                <input type="radio" name="alcance" value="general" x-model="formData.alcance" class="text-emerald-900 focus:ring-emerald-500">
                                <span class="text-xs">Catálogo Global</span>
                            </label>
                        </div>

                        <!-- Selector Específico según Alcance -->
                        <div x-show="formData.alcance === 'producto'" x-cloak class="pt-2">
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Seleccionar Medicamento / Producto <span class="text-red-900">*</span>
                            </label>
                            <select name="producto_id" x-model="formData.producto_id" class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-xs focus:ring-emerald-500 focus:border-emerald-500">
                                <option value="">-- Seleccione un medicamento --</option>
                                @foreach($productos as $prod)
                                <option value="{{ $prod->id }}">
                                    {{ $prod->nombre }} {{ $prod->concentracion }} (Precio regular: ${{ number_format($prod->precio_venta, 2) }})
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div x-show="formData.alcance === 'categoria'" x-cloak class="pt-2">
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Seleccionar Categoría Terapéutica <span class="text-red-900">*</span>
                            </label>
                            <select name="categoria_id" x-model="formData.categoria_id" class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-xs focus:ring-emerald-500 focus:border-emerald-500">
                                <option value="">-- Seleccione una categoría --</option>
                                @foreach($categorias as $cat)
                                <option value="{{ $cat->id }}">
                                    {{ $cat->nombre }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div x-show="formData.alcance === 'laboratorio'" x-cloak class="pt-2">
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Seleccionar Laboratorio Fabricante <span class="text-red-900">*</span>
                            </label>
                            <select name="laboratorio_id" x-model="formData.laboratorio_id" class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-xs focus:ring-emerald-500 focus:border-emerald-500">
                                <option value="">-- Seleccione un laboratorio --</option>
                                @foreach($laboratorios as $lab)
                                <option value="{{ $lab->id }}">
                                    {{ $lab->nombre }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Tarjeta 4: Período de Vigencia -->
                    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs p-5 space-y-4">
                        <h2 class="text-sm font-bold text-slate-800 dark:text-slate-200 border-b border-slate-100 dark:border-slate-800 pb-2 flex items-center gap-2">
                            <span class="flex items-center justify-center w-5 h-5 rounded-full bg-emerald-100 dark:bg-emerald-950 text-emerald-900 dark:text-emerald-300 text-xs font-bold">4</span>
                            Período de Vigencia y Estado
                        </h2>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                    Fecha y Hora de Inicio <span class="text-red-900">*</span>
                                </label>
                                <input type="datetime-local" name="fecha_inicio" required
                                       x-model="formData.fecha_inicio"
                                       class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-xs focus:ring-emerald-500 focus:border-emerald-500">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                    Fecha y Hora de Finalización <span class="text-red-900">*</span>
                                </label>
                                <input type="datetime-local" name="fecha_fin" required
                                       x-model="formData.fecha_fin"
                                       class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-xs focus:ring-emerald-500 focus:border-emerald-500">
                            </div>

                            <div class="sm:col-span-2 pt-2">
                                <label class="inline-flex items-center space-x-2.5 cursor-pointer">
                                    <input type="checkbox" name="activo" value="1" x-model="formData.activo"
                                           class="rounded border-slate-300 text-emerald-900 focus:ring-emerald-500">
                                    <span class="text-xs font-semibold text-slate-800 dark:text-slate-200">Activar campaña inmediatamente para ventas</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Columna Derecha: Simulador de Descuento y Acciones -->
                <div class="space-y-4">
                    <div class="bg-gradient-to-br from-emerald-500/10 via-teal-500/5 to-transparent dark:from-emerald-950/40 dark:via-slate-900 dark:to-slate-900 rounded-xl border border-emerald-200 dark:border-emerald-800/60 p-5 sticky top-20 shadow-xs">
                        <h3 class="text-xs font-bold text-emerald-800 dark:text-emerald-300 uppercase tracking-wider mb-3 flex items-center space-x-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            <span>Simulador POS en Vivo</span>
                        </h3>

                        <div class="space-y-3 bg-white dark:bg-slate-800/90 rounded-xl p-4 border border-slate-200/80 dark:border-slate-700">
                            <div>
                                <label class="block text-[11px] text-slate-500 dark:text-slate-400 mb-1">Precio Unitario Base de Prueba:</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1.5 text-xs text-slate-400 font-bold">$</span>
                                    <input type="number" step="0.5" x-model="samplePrice" class="w-full pl-6 pr-3 py-1 rounded-lg border-slate-200 dark:border-slate-700 dark:bg-slate-900 text-xs font-bold text-slate-900 dark:text-white">
                                </div>
                            </div>

                            <div class="pt-2 border-t border-slate-100 dark:border-slate-700 space-y-2">
                                <div class="flex justify-between items-center text-xs">
                                    <span class="text-slate-500">Por 1 unidad:</span>
                                    <div class="text-right">
                                        <span class="line-through text-slate-400" x-show="calculateDiscount(1) > 0" x-text="'$' + Number(samplePrice).toFixed(2)"></span>
                                        <span class="font-bold text-emerald-900 dark:text-emerald-400 ml-1" x-text="'$' + (samplePrice - calculateDiscount(1)).toFixed(2)"></span>
                                    </div>
                                </div>

                                <div class="flex justify-between items-center text-xs" x-show="formData.min_unidades > 1 || formData.tipo === '2x1' || formData.tipo === '3x2'">
                                    <span class="text-slate-500" x-text="'Por ' + formData.min_unidades + ' unidades (Combo):'"></span>
                                    <div class="text-right">
                                        <span class="line-through text-slate-400" x-text="'$' + (samplePrice * formData.min_unidades).toFixed(2)"></span>
                                        <span class="font-bold text-emerald-900 dark:text-emerald-400 ml-1" x-text="'$' + ((samplePrice * formData.min_unidades) - calculateDiscount(formData.min_unidades)).toFixed(2)"></span>
                                    </div>
                                </div>

                                <div class="p-2.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 text-[11px] text-emerald-800 dark:text-emerald-300">
                                    <p class="font-bold">Ahorro para el cliente:</p>
                                    <p class="mt-0.5" x-text="'$' + calculateDiscount(formData.min_unidades).toFixed(2) + ' de descuento directo en el ticket POS.'"></p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 space-y-2">
                            <label class="inline-flex items-center gap-2 cursor-pointer select-none text-slate-700 dark:text-slate-300 text-xs font-medium pb-1">
                                <input type="checkbox" name="crear_otro" value="1"
                                       {{ configuracion('interfaz_mantener_en_crear') ? 'checked' : '' }}
                                       class="rounded border-slate-300 text-emerald-900 focus:ring-emerald-500">
                                <span>Guardar y crear otra</span>
                            </label>

                            <button type="submit" 
                                    class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white rounded-xl text-xs font-bold shadow-xs transition flex items-center justify-center space-x-1.5 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>Guardar y Activar Promoción</span>
                            </button>

                            <a href="{{ route('promociones.index') }}" 
                               class="w-full py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-400 rounded-xl text-xs font-semibold transition text-center block">
                                Cancelar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
