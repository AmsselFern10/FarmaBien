@extends('layouts.app')

@section('title', 'Editar Categoría - FarmaBien')

@section('content')
<div x-data="{
    formLayout: localStorage.getItem('farmaFormViewMode') || '{{ configuracion('interfaz_vista_formularios_default', 'modern') }}',
    setLayout(mode) {
        this.formLayout = mode;
        localStorage.setItem('farmaFormViewMode', mode);
        window.dispatchEvent(new CustomEvent('farma:layout-changed', { detail: { mode } }));
    },
    formData: {
        nombre: @js(old('nombre', $categoria->nombre)),
        descripcion: @js(old('descripcion', $categoria->descripcion ?? '')),
        activo: @js(old('activo', $categoria->activo) ? true : false)
    },
    limpiarFormulario() {
        this.formData = {
            nombre: @js($categoria->nombre),
            descripcion: @js($categoria->descripcion ?? ''),
            activo: @js((bool)$categoria->activo)
        };
    }
}"
@keydown.window="if ($event.key === 'Escape' && formLayout === 'compact') { limpiarFormulario(); }"
:class="formLayout === 'compact' ? 'w-full' : 'max-w-4xl mx-auto'"
class="space-y-4 transition-all duration-200">
    
    <!-- Breadcrumb & View Toggle Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-1 border-b border-slate-300/80 dark:border-slate-800">
        <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
            <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
            <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <a href="{{ route('categorias.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Categorías</a>
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
            <h1 class="text-lg sm:text-xl font-bold text-slate-900 dark:text-white">Editar Categoría: {{ $categoria->nombre }}</h1>
            <p class="text-xs text-slate-600 dark:text-slate-400">
                Actualiza el nombre, descripción y disponibilidad de este grupo terapéutico.
            </p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ route('categorias.show', $categoria) }}" 
               class="inline-flex items-center space-x-1 px-3 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-xl transition shadow-2xs">
                <span>Ver Ficha</span>
            </a>
            <a href="{{ route('categorias.index') }}" 
               class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-xl transition shrink-0 shadow-2xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Volver</span>
            </a>
        </div>
    </div>

    <!-- Form -->
    <form x-ref="categoriaEditForm" method="POST" action="{{ route('categorias.update', $categoria) }}">
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
                        <span class="text-xs font-extrabold text-slate-800 dark:text-slate-200 uppercase tracking-wide">EDICIÓN RÁPIDA DE CATEGORÍA</span>
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
                    
                    <!-- Panel 1: Nombre y Estado -->
                    <div class="p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/40 dark:bg-slate-800/30 space-y-3">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center space-x-1.5 border-b border-slate-200/60 dark:border-slate-700/60 pb-1.5">
                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                            <span>Datos Principales</span>
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Nombre de la Categoría <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" 
                                   name="nombre" 
                                   x-model="formData.nombre"
                                   required 
                                   autofocus
                                   placeholder="Ej. Analgésicos y Antipiréticos..." 
                                   class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500 @error('nombre') border-rose-500 @enderror">
                            @error('nombre')
                                <p class="text-rose-500 text-[10px] mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="pt-1">
                            <input type="hidden" name="activo" value="0">
                            <label class="inline-flex items-center space-x-2 cursor-pointer text-xs">
                                <input type="checkbox" name="activo" x-model="formData.activo" value="1" class="rounded border-slate-300 text-emerald-600 w-3.5 h-3.5">
                                <span class="text-[11px] font-semibold text-emerald-700 dark:text-emerald-400">Categoría Activa en el Catálogo</span>
                            </label>
                        </div>
                    </div>

                    <!-- Panel 2: Descripción Clínica -->
                    <div class="p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/40 dark:bg-slate-800/30 space-y-3">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center space-x-1.5 border-b border-slate-200/60 dark:border-slate-700/60 pb-1.5">
                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span>Descripción Clínica / Indicaciones</span>
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Descripción / Notas Generales
                            </label>
                            <textarea name="descripcion" 
                                      x-model="formData.descripcion"
                                      rows="3" 
                                      placeholder="Mecanismo de acción, afecciones tratadas o notas de dispensación..." 
                                      class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Footer Compacto -->
                <div class="pt-2 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between text-xs text-slate-500">
                    <span class="text-[11px]">Cambios listos para guardar.</span>
                    <button type="submit" 
                            class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs transition flex items-center space-x-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Actualizar Categoría</span>
                    </button>
                </div>
            </div>
        </template>

        <!-- ========================================== -->
        <!-- MODO MODERNO (TARJETAS GRANDES)           -->
        <!-- ========================================== -->
        <template x-if="formLayout === 'modern'">
            <div class="space-y-6 animate-fadeIn">
                <!-- Card: Información Principal -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm space-y-5">
                    <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center space-x-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span>Definición del Grupo Terapéutico</span>
                        </h3>
                        <p class="text-xs text-slate-400 mt-0.5">Nombre oficial y descripción clínica del grupo farmacológico.</p>
                    </div>

                    <div class="space-y-4">
                        <!-- Nombre -->
                        <div>
                            <label for="nombre_mod" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Nombre de la Categoría <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" 
                                   id="nombre_mod" 
                                   name="nombre" 
                                   x-model="formData.nombre" 
                                   required 
                                   autofocus 
                                   placeholder="Ej. Analgésicos y Antipiréticos..." 
                                   class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition @error('nombre') border-rose-500 @enderror">
                            @error('nombre')
                                <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Descripción -->
                        <div>
                            <label for="descripcion_mod" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Descripción Clínica / Indicaciones Generales
                            </label>
                            <textarea id="descripcion_mod" 
                                      name="descripcion" 
                                      x-model="formData.descripcion" 
                                      rows="3" 
                                      placeholder="Breve reseña sobre el mecanismo de acción, afecciones tratadas o notas de dispensación..." 
                                      class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition @error('descripcion') border-rose-500 @enderror"></textarea>
                            @error('descripcion')
                                <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Card: Estado Operativo -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm">
                    <input type="hidden" name="activo" value="0">
                    <label class="flex items-center space-x-3 cursor-pointer">
                        <input type="checkbox" 
                               name="activo" 
                               value="1" 
                               x-model="formData.activo"
                               class="w-4 h-4 rounded bg-slate-50 dark:bg-slate-800 border-slate-300 dark:border-slate-700 text-emerald-600 focus:ring-emerald-500">
                        <div>
                            <span class="text-sm font-semibold text-slate-800 dark:text-slate-200 block">Categoría Activa en el Catálogo</span>
                            <span class="text-xs text-slate-400">Permite seleccionar esta categoría al crear o editar medicamentos.</span>
                        </div>
                    </label>
                </div>

                <!-- Sticky Footer Actions -->
                <div class="sticky bottom-0 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-3.5 bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border-t border-slate-200 dark:border-slate-800 shadow-lg z-20 flex items-center justify-between transition-all rounded-b-2xl">
                    <a href="{{ route('categorias.index') }}" 
                       class="px-5 py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-xl transition">
                        Cancelar
                    </a>
                    <div class="flex items-center space-x-3">
                        <button type="submit" 
                                class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-sm font-semibold rounded-xl shadow-sm transition inline-flex items-center space-x-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Actualizar Categoría</span>
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </form>
</div>
@endsection
