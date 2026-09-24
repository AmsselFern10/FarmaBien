@extends('layouts.app')

@section('title', 'Editar Laboratorio - FarmaBien')

@section('content')
<div x-data="{
    formLayout: localStorage.getItem('farmaFormViewMode') || '{{ configuracion('interfaz_vista_formularios_default', 'modern') }}',
    setLayout(mode) {
        this.formLayout = mode;
        localStorage.setItem('farmaFormViewMode', mode);
        window.dispatchEvent(new CustomEvent('farma:layout-changed', { detail: { mode } }));
    },
    formData: {
        nombre: @js(old('nombre', $laboratorio->nombre)),
        codigo: @js(old('codigo', $laboratorio->codigo ?? '')),
        pais_origen: @js(old('pais_origen', $laboratorio->pais_origen ?? '')),
        contacto: @js(old('contacto', $laboratorio->contacto ?? '')),
        telefono: @js(old('telefono', $laboratorio->telefono ?? '')),
        email: @js(old('email', $laboratorio->email ?? '')),
        activo: @js(old('activo', $laboratorio->activo) ? true : false)
    },
    limpiarFormulario() {
        this.formData = {
            nombre: @js($laboratorio->nombre),
            codigo: @js($laboratorio->codigo ?? ''),
            pais_origen: @js($laboratorio->pais_origen ?? ''),
            contacto: @js($laboratorio->contacto ?? ''),
            telefono: @js($laboratorio->telefono ?? ''),
            email: @js($laboratorio->email ?? ''),
            activo: @js((bool)$laboratorio->activo)
        };
    }
}"
@keydown.window="if ($event.key === 'Escape' && formLayout === 'compact') { limpiarFormulario(); }"
:class="formLayout === 'compact' ? 'w-full' : 'max-w-5xl mx-auto'"
class="space-y-4 transition-all duration-200">
    
    <!-- Breadcrumb & View Toggle Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-1 border-b border-slate-300/80 dark:border-slate-800">
        <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
            <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
            <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <a href="{{ route('laboratorios.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Laboratorios</a>
            <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-slate-800 dark:text-slate-200 font-semibold">Editar</span>
        </nav>

        <!-- View Mode Switcher -->
        <div class="flex items-center space-x-2 self-start sm:self-auto">
            <!-- Modo Full Screen (Ocultar Barras) -->
            <button type="button" 
                    @click="$dispatch('toggle-pos-fullscreen')"
                    title="Modo Pantalla Completa / Ocultar Barras"
                    class="px-2.5 py-1.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold transition flex items-center space-x-1.5 shrink-0 shadow-2xs cursor-pointer">
                <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                <span class="hidden sm:inline">Modo Full</span>
            </button>

            <span class="text-[11px] font-bold text-slate-700 dark:text-slate-400 hidden md:inline">Diseño:</span>
            <div class="inline-flex items-center p-0.5 rounded-xl bg-slate-200/80 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-xs font-semibold shadow-2xs">
                <button type="button" 
                        @click="setLayout('modern')"
                        :class="formLayout === 'modern' ? 'bg-white dark:bg-slate-700 text-emerald-700 dark:text-emerald-400 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200'"
                        class="px-2.5 py-1 rounded-lg transition flex items-center space-x-1.5 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    <span>Moderna</span>
                </button>
                <button type="button" 
                        @click="setLayout('compact')"
                        :class="formLayout === 'compact' ? 'bg-white dark:bg-slate-700 text-emerald-700 dark:text-emerald-400 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200'"
                        class="px-2.5 py-1 rounded-lg transition flex items-center space-x-1.5 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <span>Compacta (POS / ERP)</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
        <div>
            <h1 class="text-lg sm:text-xl font-bold text-slate-900 dark:text-white">Editar Laboratorio: {{ $laboratorio->nombre }}</h1>
            <p class="text-xs text-slate-600 dark:text-slate-400">
                Actualiza los datos institucionales y de contacto del fabricante.
            </p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ route('laboratorios.show', $laboratorio) }}" 
               class="inline-flex items-center space-x-1 px-3 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-xl transition shadow-2xs">
                <span>Ver Ficha</span>
            </a>
            <a href="{{ route('laboratorios.index') }}" 
               class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-xl transition shrink-0 shadow-2xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Volver</span>
            </a>
        </div>
    </div>

    <!-- Form -->
    <form x-ref="laboratorioEditForm" method="POST" action="{{ route('laboratorios.update', $laboratorio) }}">
        @csrf
        @method('PUT')

        <!-- ========================================== -->
        <!-- MODO COMPACTO ESCRITORIO (SIN SCROLL)     -->
        <!-- ========================================== -->
        <template x-if="formLayout === 'compact'">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-300 dark:border-slate-800 shadow-md p-4 space-y-4 animate-fadeIn">
                
                <!-- Toolbar Superior -->
                <div class="flex items-center justify-between pb-3 border-b border-slate-300 dark:border-slate-800 bg-slate-100/80 dark:bg-slate-800/60 -mx-4 -mt-4 p-3 rounded-t-2xl">
                    <div class="flex items-center space-x-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shadow-xs"></span>
                        <span class="text-xs font-extrabold text-slate-800 dark:text-slate-200 uppercase tracking-wide">EDICIÓN RÁPIDA DE LABORATORIO</span>
                        <span class="text-[10px] px-2 py-0.5 rounded-md bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-mono font-bold">Esc = Restaurar</span>
                    </div>

                    <div class="flex items-center space-x-2">
                        <button type="button" 
                                @click="limpiarFormulario()" 
                                class="px-3 py-1.5 rounded-lg border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-800 text-xs font-bold transition cursor-pointer">
                            Restaurar (Esc)
                        </button>
                        <button type="submit" 
                                class="px-4 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-extrabold shadow-sm transition flex items-center space-x-1.5 cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Guardar Cambios</span>
                        </button>
                    </div>
                </div>

                <!-- Grid Compacta -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    
                    <!-- Panel 1: Identificación y Código -->
                    <div class="p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/40 dark:bg-slate-800/30 space-y-3">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center space-x-1.5 border-b border-slate-200/60 dark:border-slate-700/60 pb-1.5">
                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            <span>Identificación del Fabricante</span>
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Razón Social / Nombre Comercial <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" 
                                   name="nombre" 
                                   x-model="formData.nombre"
                                   required 
                                   autofocus
                                   placeholder="Ej. Laboratorios Pfizer S.A..." 
                                   class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500 @error('nombre') border-rose-500 @enderror">
                            @error('nombre')
                                <p class="text-rose-500 text-[10px] mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-2.5">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                    Código / Sigla Abreviada
                                </label>
                                <input type="text" 
                                       name="codigo" 
                                       x-model="formData.codigo"
                                       placeholder="PFIZ, GNF..." 
                                       class="w-full font-mono uppercase px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                            </div>

                            <div>
                                <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                    País de Origen
                                </label>
                                <input type="text" 
                                       name="pais_origen" 
                                       x-model="formData.pais_origen"
                                       placeholder="Ej. Perú, Suiza..." 
                                       class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                            </div>
                        </div>

                        <div class="pt-1">
                            <input type="hidden" name="activo" value="0">
                            <label class="inline-flex items-center space-x-2 cursor-pointer text-xs">
                                <input type="checkbox" name="activo" x-model="formData.activo" value="1" class="rounded border-slate-300 text-emerald-600 w-3.5 h-3.5">
                                <span class="text-[11px] font-semibold text-emerald-700 dark:text-emerald-400">Laboratorio Activo y Habilitado</span>
                            </label>
                        </div>
                    </div>

                    <!-- Panel 2: Contacto y Comunicaciones -->
                    <div class="p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/40 dark:bg-slate-800/30 space-y-3">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center space-x-1.5 border-b border-slate-200/60 dark:border-slate-700/60 pb-1.5">
                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            <span>Contacto y Representante</span>
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Representante / Contacto
                            </label>
                            <input type="text" 
                                   name="contacto" 
                                   x-model="formData.contacto"
                                   placeholder="Ej. Lic. Carlos Mendoza" 
                                   class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                    Teléfono / Central
                                </label>
                                <input type="text" 
                                       name="telefono" 
                                       x-model="formData.telefono"
                                       placeholder="+51 987 654 321" 
                                       class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                            </div>

                            <div>
                                <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                    Correo Electrónico
                                </label>
                                <input type="email" 
                                       name="email" 
                                       x-model="formData.email"
                                       placeholder="contacto@laboratorio.com" 
                                       class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Compacto -->
                <div class="pt-2 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between text-xs text-slate-500">
                    <span class="text-[11px]">Cambios listos para guardar.</span>
                    <button type="submit" 
                            class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs transition flex items-center space-x-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Actualizar Laboratorio</span>
                    </button>
                </div>
            </div>
        </template>

        <!-- ========================================== -->
        <!-- MODO MODERNO (TARJETAS GRANDES)           -->
        <!-- ========================================== -->
        <template x-if="formLayout === 'modern'">
            <div class="space-y-6 animate-fadeIn">
                <!-- Section 1: Identificación y Origen -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm space-y-5">
                    <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center space-x-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span>Información Principal de la Empresa</span>
                        </h3>
                        <p class="text-xs text-slate-400 mt-0.5">Datos de identificación y origen de la casa farmacéutica.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <!-- Nombre -->
                        <div class="md:col-span-2">
                            <label for="nombre_mod" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Razón Social / Nombre Comercial <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" 
                                   id="nombre_mod" 
                                   name="nombre" 
                                   x-model="formData.nombre" 
                                   required 
                                   autofocus 
                                   placeholder="Ej. Laboratorios Pfizer S.A." 
                                   class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition @error('nombre') border-rose-500 @enderror">
                            @error('nombre')
                                <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Código Interno / Sigla -->
                        <div>
                            <label for="codigo_mod" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Código / Sigla Abreviada
                            </label>
                            <input type="text" 
                                   id="codigo_mod" 
                                   name="codigo" 
                                   x-model="formData.codigo" 
                                   placeholder="Ej. PFIZ, GNF, BYR" 
                                   class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-mono uppercase text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition @error('codigo') border-rose-500 @enderror">
                            @error('codigo')
                                <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- País de Origen -->
                        <div class="md:col-span-1">
                            <label for="pais_origen_mod" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                País de Origen
                            </label>
                            <input type="text" 
                                   id="pais_origen_mod" 
                                   name="pais_origen" 
                                   x-model="formData.pais_origen" 
                                   placeholder="Ej. Perú, Alemania, Suiza, EE.UU." 
                                   class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition @error('pais_origen') border-rose-500 @enderror">
                            @error('pais_origen')
                                <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Section 2: Contacto y Comunicaciones -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm space-y-5">
                    <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center space-x-2">
                            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                            <span>Datos de Contacto y Representante</span>
                        </h3>
                        <p class="text-xs text-slate-400 mt-0.5">Canales para consultas técnicas o soporte de lote.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <!-- Representante de Contacto -->
                        <div>
                            <label for="contacto_mod" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Representante / Contacto
                            </label>
                            <input type="text" 
                                   id="contacto_mod" 
                                   name="contacto" 
                                   x-model="formData.contacto" 
                                   placeholder="Ej. Lic. Representante Médico" 
                                   class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition @error('contacto') border-rose-500 @enderror">
                            @error('contacto')
                                <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Teléfono -->
                        <div>
                            <label for="telefono_mod" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Teléfono / Línea Directa
                            </label>
                            <input type="text" 
                                   id="telefono_mod" 
                                   name="telefono" 
                                   x-model="formData.telefono" 
                                   placeholder="Ej. +51 987 654 321" 
                                   class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition @error('telefono') border-rose-500 @enderror">
                            @error('telefono')
                                <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email_mod" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Correo Electrónico
                            </label>
                            <input type="email" 
                                   id="email_mod" 
                                   name="email" 
                                   x-model="formData.email" 
                                   placeholder="contacto@laboratorio.com" 
                                   class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition @error('email') border-rose-500 @enderror">
                            @error('email')
                                <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Section 3: Estado Operativo -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm">
                    <input type="hidden" name="activo" value="0">
                    <label class="flex items-center space-x-3 cursor-pointer">
                        <input type="checkbox" 
                               name="activo" 
                               value="1" 
                               x-model="formData.activo" 
                               class="w-4 h-4 rounded bg-slate-50 dark:bg-slate-800 border-slate-300 dark:border-slate-700 text-emerald-600 focus:ring-emerald-500">
                        <div>
                            <span class="text-sm font-semibold text-slate-800 dark:text-slate-200 block">Laboratorio Activo y Habilitado</span>
                            <span class="text-xs text-slate-400">Permite asociar este fabricante a nuevos medicamentos y recepciones de stock.</span>
                        </div>
                    </label>
                </div>

                <!-- Sticky Footer Actions -->
                <div class="sticky bottom-0 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-3.5 bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border-t border-slate-200 dark:border-slate-800 shadow-lg z-20 flex items-center justify-between transition-all rounded-b-2xl">
                    <a href="{{ route('laboratorios.index') }}" 
                       class="px-5 py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-xl transition">
                        Cancelar
                    </a>
                    <div class="flex items-center space-x-3">
                        <button type="submit" 
                                class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-sm font-semibold rounded-xl shadow-sm transition inline-flex items-center space-x-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Actualizar Laboratorio</span>
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </form>
</div>
@endsection
