@extends('layouts.app')

@section('title', 'Crear Lote Manual - FarmaBien')

@section('content')
<div x-data="{
    formLayout: localStorage.getItem('farmaFormViewMode') || '{{ configuracion('interfaz_vista_formularios_default', 'modern') }}',
    setLayout(mode) {
        this.formLayout = mode;
        localStorage.setItem('farmaFormViewMode', mode);
        window.dispatchEvent(new CustomEvent('farma:layout-changed', { detail: { mode } }));
    },
    formData: (window.farmaGetDraft ? window.farmaGetDraft('{{ request()->getPathInfo() }}', {
        producto_id: @js(old('producto_id', $productoPreseleccionado?->id ?? '')),
        numero_lote: @js(old('numero_lote', '')),
        fecha_vencimiento: @js(old('fecha_vencimiento', '')),
        cantidad: @js(old('cantidad', '')),
        precio_compra: @js(old('precio_compra', '')),
        proveedor_id: @js(old('proveedor_id', '')),
        motivo: @js(old('motivo', ''))
    }) : {
        producto_id: @js(old('producto_id', $productoPreseleccionado?->id ?? '')),
        numero_lote: @js(old('numero_lote', '')),
        fecha_vencimiento: @js(old('fecha_vencimiento', '')),
        cantidad: @js(old('cantidad', '')),
        precio_compra: @js(old('precio_compra', '')),
        proveedor_id: @js(old('proveedor_id', '')),
        motivo: @js(old('motivo', ''))
    }),
    limpiarFormulario() {
        this.formData = {
            producto_id: '',
            numero_lote: '',
            fecha_vencimiento: '',
            cantidad: '',
            precio_compra: '',
            proveedor_id: '',
            motivo: ''
        };
        if (window.farmaClearDraft) {
            window.farmaClearDraft('{{ request()->getPathInfo() }}');
        }
    }
}"
@keydown.window="if ($event.key === 'Escape' && formLayout === 'compact') { limpiarFormulario(); }"
:class="formLayout === 'compact' ? 'w-full' : 'max-w-5xl mx-auto'"
class="space-y-4 transition-all duration-200">

    <!-- Fila 1: Breadcrumbs -->
    <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('inventario.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inventario</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('inventario.lotes') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Lotes de Inventario</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-800 dark:text-slate-200 font-semibold">Crear Lote Manual</span>
    </nav>

    <!-- Fila 2: Título + Barra de Acciones Superior -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <span>Crear Lote Manual</span>
            </h1>
            <p class="text-xs text-slate-700 dark:text-slate-400 mt-0.5">
                Para stock de apertura, migración al sistema, donaciones o correcciones iniciales.<br class="hidden sm:inline">
                Se registrará un movimiento de <span class="font-bold text-emerald-900 dark:text-emerald-400">entrada</span> en el Kardex con auditoría completa.
            </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap shrink-0">
            <!-- 1. Botón Predecesor (Blanco) -->
            <a href="{{ route('inventario.lotes') }}" 
               class="px-3.5 py-1.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold shadow-2xs transition inline-flex items-center gap-1.5 cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Lotes de Inventario</span>
            </a>

            <!-- 2. Botón Modo Full (Blanco) -->
            <button type="button" 
                    @click="$dispatch('toggle-pos-fullscreen')"
                    title="Modo Pantalla Completa / Ocultar Barras"
                    class="px-3.5 py-1.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold shadow-2xs transition inline-flex items-center gap-1.5 cursor-pointer">
                <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                <span>Modo Full</span>
            </button>

            <!-- 3. Botón Principal Guardar Lote (Verde Sólido Institucional) -->
            <button type="button" 
                    onclick="document.querySelector('form[x-ref=loteForm]').submit()"
                    class="px-3.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-semibold shadow-xs transition inline-flex items-center gap-1.5 cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>Guardar Lote</span>
            </button>

            <!-- 4. Toggle Diseño Moderna / Compacta -->
            <div class="inline-flex items-center p-0.5 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-xs font-semibold shadow-2xs">
                <button type="button" 
                        @click="setLayout('modern')"
                        :class="formLayout === 'modern' ? 'bg-white dark:bg-slate-700 text-emerald-900 dark:text-emerald-400 shadow-xs font-bold' : 'text-slate-700 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200'"
                        class="px-2.5 py-1 rounded-lg transition flex items-center gap-1.5 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    <span>Moderna</span>
                </button>
                <button type="button" 
                        @click="setLayout('compact')"
                        :class="formLayout === 'compact' ? 'bg-white dark:bg-slate-700 text-emerald-900 dark:text-emerald-400 shadow-xs font-bold' : 'text-slate-700 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200'"
                        class="px-2.5 py-1 rounded-lg transition flex items-center gap-1.5 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <span>Compacta</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Aviso de uso -->
    <div class="flex items-start gap-3 px-4 py-3 bg-amber-50 dark:bg-amber-950/40 border border-amber-300 dark:border-amber-800 rounded-2xl text-xs text-amber-900 dark:text-amber-300 shadow-2xs">
        <svg class="w-4 h-4 shrink-0 mt-0.5 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        <div>
            <span class="font-bold block mb-0.5">¿Cuándo usar este formulario?</span>
            Usa esto para cargar stock existente al migrar al sistema, registrar donaciones o existencias que no entran por compra.
            Para inventario normal con factura de proveedor, usa el módulo de <a href="{{ route('compras.create') }}" class="underline font-bold text-amber-950 dark:text-amber-200 hover:text-amber-800">Compras</a>.
        </div>
    </div>

    <!-- Alerta de Errores de Validación -->
    @if($errors->any())
    <div class="px-4 py-3 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-300 dark:border-rose-800 text-xs text-red-900 dark:text-rose-300 shadow-2xs">
        <p class="font-bold mb-1">Por favor corrija los siguientes errores:</p>
        <ul class="list-disc list-inside space-y-0.5">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Formulario Principal -->
    <form x-ref="loteForm" method="POST" action="{{ route('inventario.lotes.store') }}">
        @csrf

        <!-- ========================================== -->
        <!-- MODO COMPACTO ESCRITORIO (ALTA DENSIDAD)   -->
        <!-- ========================================== -->
        <template x-if="formLayout === 'compact'">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-4 space-y-4 animate-fadeIn">
                
                <!-- Toolbar Superior del Card Compacto -->
                <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-800/60 -mx-4 -mt-4 p-3 rounded-t-2xl">
                    <div class="flex items-center space-x-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shadow-xs"></span>
                        <span class="text-xs font-black text-slate-800 dark:text-slate-200 uppercase tracking-wide">FICHA RÁPIDA DE INGRESO MANUAL DE LOTE</span>
                        <span class="text-[10px] px-2 py-0.5 rounded-md bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-mono font-bold hidden sm:inline">Esc = Limpiar</span>
                    </div>

                    <div class="flex items-center space-x-2">
                        <button type="button" 
                                @click="limpiarFormulario()" 
                                class="px-3 py-1.5 rounded-lg border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-800 text-xs font-bold transition cursor-pointer">
                            Limpiar (Esc)
                        </button>
                    </div>
                </div>

                <!-- Grid de Paneles Compactos -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-3.5">
                    
                    <!-- Panel 1: Medicamento y Proveedor (Col 5) -->
                    <div class="lg:col-span-5 p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30 space-y-3">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center space-x-1.5 border-b border-slate-200 dark:border-slate-700 pb-1.5">
                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                            <span>1. Medicamento & Proveedor</span>
                        </div>

                        {{-- Medicamento con Componente C AJAX --}}
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                Medicamento <span class="text-red-900 font-bold">*</span>
                            </label>
                            <x-ajax-select 
                                name="producto_id" 
                                :endpoint="route('api.medicamentos.buscar-ajax')"
                                placeholder="Escriba el nombre o busque en medicamentos..."
                                type="medicamento"
                                :value="old('producto_id', $productoPreseleccionado?->id)"
                                :initial-item="$productoPreseleccionado ? ['id' => $productoPreseleccionado->id, 'nombre' => $productoPreseleccionado->nombre, 'principio_activo' => $productoPreseleccionado->principio_activo, 'laboratorio' => $productoPreseleccionado->laboratorio->nombre ?? ''] : null"
                                :required="true" />
                        </div>

                        {{-- Proveedor con Componente C AJAX --}}
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                Proveedor Origen (Opcional)
                            </label>
                            <x-ajax-select 
                                name="proveedor_id" 
                                :endpoint="route('api.proveedores.buscar-ajax')"
                                placeholder="Buscar proveedor por nombre o RUC..."
                                type="proveedor"
                                :value="old('proveedor_id')" />
                        </div>
                    </div>

                    <!-- Panel 2: Datos del Lote Físico (Col 7) -->
                    <div class="lg:col-span-7 p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30 space-y-3">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center space-x-1.5 border-b border-slate-200 dark:border-slate-700 pb-1.5">
                            <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                            <span>2. Datos del Lote Físico</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                    Número de Lote <span class="text-red-900 font-bold">*</span>
                                </label>
                                <input type="text" name="numero_lote" x-model="formData.numero_lote" required
                                       placeholder="Ej: LT-2026-001, APERTURA-01"
                                       class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white uppercase font-mono font-bold focus:ring-1 focus:ring-emerald-500">
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                    Fecha de Vencimiento <span class="text-red-900 font-bold">*</span>
                                </label>
                                <input type="date" name="fecha_vencimiento" x-model="formData.fecha_vencimiento" required
                                       min="{{ now()->addDay()->format('Y-m-d') }}"
                                       class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white font-medium focus:ring-1 focus:ring-emerald-500">
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                    Cantidad Inicial (unidades base) <span class="text-red-900 font-bold">*</span>
                                </label>
                                <input type="number" name="cantidad" x-model="formData.cantidad" required min="1"
                                       placeholder="100"
                                       class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white font-bold focus:ring-1 focus:ring-emerald-500">
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                    Precio de Costo Unitario (C$)
                                </label>
                                <input type="number" name="precio_compra" x-model="formData.precio_compra" step="0.01" min="0"
                                       placeholder="0.00"
                                       class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white text-right font-bold focus:ring-1 focus:ring-emerald-500">
                                <p class="text-slate-700 dark:text-slate-400 text-[10px] mt-0.5">Opcional — para valorización del stock en inventario</p>
                            </div>
                        </div>
                    </div>

                    <!-- Panel 3: Justificación y Auditoría (Col 12) -->
                    <div class="lg:col-span-12 p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30 space-y-2">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center space-x-1.5 border-b border-slate-200 dark:border-slate-700 pb-1.5">
                            <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span>3. Justificación y Auditoría Kardex (MINSA)</span>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                Motivo / Justificación <span class="text-red-900 font-bold">*</span>
                            </label>
                            <textarea name="motivo" x-model="formData.motivo" rows="2" required minlength="5"
                                      placeholder="Ej: Stock de apertura al migrar al sistema FarmaBien. Conteo físico realizado en sucursal..."
                                      class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500"></textarea>
                            <p class="text-slate-700 dark:text-slate-400 text-[10px] mt-0.5">Mínimo 5 caracteres. Se registrará un movimiento de <span class="font-bold text-emerald-900 dark:text-emerald-400">entrada</span> en el Kardex con el usuario activo.</p>
                        </div>
                    </div>
                </div>
            </div>
        </template>


        <!-- ========================================== -->
        <!-- MODO MODERNO (TARJETAS ESPACIOSAS)         -->
        <!-- ========================================== -->
        <template x-if="formLayout === 'modern'">
            <div class="space-y-5 animate-fadeIn">
                
                <!-- Tarjeta 1: Medicamento Seleccionado -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/60 flex items-center space-x-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-slate-200">
                            Medicamento Seleccionado
                        </h2>
                    </div>

                    <div class="p-5 space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1.5">
                                Medicamento <span class="text-red-900 font-bold">*</span>
                            </label>
                            <x-ajax-select 
                                name="producto_id" 
                                :endpoint="route('api.medicamentos.buscar-ajax')"
                                placeholder="Escriba el nombre o busque en medicamentos..."
                                type="medicamento"
                                :value="old('producto_id', $productoPreseleccionado?->id)"
                                :initial-item="$productoPreseleccionado ? ['id' => $productoPreseleccionado->id, 'nombre' => $productoPreseleccionado->nombre, 'principio_activo' => $productoPreseleccionado->principio_activo, 'laboratorio' => $productoPreseleccionado->laboratorio->nombre ?? ''] : null"
                                :required="true" />
                            <p class="text-slate-700 dark:text-slate-400 text-xs mt-1.5">
                                El lote quedará asociado permanentemente a este medicamento.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Tarjeta 2: Datos del Lote -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/60 flex items-center space-x-2">
                        <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-slate-200">
                            Datos del Lote Físico
                        </h2>
                    </div>

                    <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1.5">
                                Número de Lote <span class="text-red-900 font-bold">*</span>
                            </label>
                            <input type="text" name="numero_lote" x-model="formData.numero_lote" required
                                   placeholder="Ej: LT-2026-001, APERTURA-01"
                                   class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white uppercase font-mono font-bold focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition shadow-2xs">
                            <p class="text-slate-700 dark:text-slate-400 text-xs mt-1">Tal como aparece impreso en el empaque secundario.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1.5">
                                Fecha de Vencimiento <span class="text-red-900 font-bold">*</span>
                            </label>
                            <input type="date" name="fecha_vencimiento" x-model="formData.fecha_vencimiento" required
                                   min="{{ now()->addDay()->format('Y-m-d') }}"
                                   class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white font-medium focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition shadow-2xs">
                            <p class="text-slate-700 dark:text-slate-400 text-xs mt-1">Debe ser una fecha futura.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1.5">
                                Cantidad Inicial (unidades base) <span class="text-red-900 font-bold">*</span>
                            </label>
                            <input type="number" name="cantidad" x-model="formData.cantidad" required min="1"
                                   placeholder="100"
                                   class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white font-bold focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition shadow-2xs">
                            <p class="text-slate-700 dark:text-slate-400 text-xs mt-1">Unidades físicas reales contadas.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1.5">
                                Precio de Costo Unitario (C$)
                            </label>
                            <input type="number" name="precio_compra" x-model="formData.precio_compra" step="0.01" min="0"
                                   placeholder="0.00"
                                   class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white text-right font-bold focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition shadow-2xs">
                            <p class="text-slate-700 dark:text-slate-400 text-xs mt-1">Opcional — para valorización del stock en inventario</p>
                        </div>
                    </div>
                </div>

                <!-- Tarjeta 3: Proveedor y Justificación Kardex -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/60 flex items-center space-x-2">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-slate-200">
                            Proveedor y Justificación Kardex
                        </h2>
                    </div>

                    <div class="p-5 space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1.5">
                                Proveedor Origen (Opcional)
                            </label>
                            <x-ajax-select 
                                name="proveedor_id" 
                                :endpoint="route('api.proveedores.buscar-ajax')"
                                placeholder="Buscar proveedor por nombre o RUC..."
                                type="proveedor"
                                :value="old('proveedor_id')" />
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1.5">
                                Motivo / Justificación de Entrada <span class="text-red-900 font-bold">*</span>
                            </label>
                            <textarea name="motivo" x-model="formData.motivo" rows="3" required minlength="5"
                                      placeholder="Ej: Stock de apertura al migrar al sistema FarmaBien. Conteo físico realizado en sucursal..."
                                      class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition shadow-2xs"></textarea>
                            <p class="text-slate-700 dark:text-slate-400 text-xs mt-1">
                                Mínimo 5 caracteres. Se registrará un movimiento de <span class="font-bold text-emerald-900 dark:text-emerald-400">entrada</span> en el Kardex con el usuario activo.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </form>
</div>
@endsection
