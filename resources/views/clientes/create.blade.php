@extends('layouts.app')

@section('title', 'Nuevo Cliente - FarmaBien')

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
        documento: @js(old('documento', '')),
        telefono: @js(old('telefono', '')),
        email: @js(old('email', '')),
        direccion: @js(old('direccion', '')),
        activo: @js(old('activo', 1) ? true : false)
    }) : {
        nombre: @js(old('nombre', '')),
        documento: @js(old('documento', '')),
        telefono: @js(old('telefono', '')),
        email: @js(old('email', '')),
        direccion: @js(old('direccion', '')),
        activo: @js(old('activo', 1) ? true : false)
    }),
    limpiarFormulario() {
        this.formData = { nombre: '', documento: '', telefono: '', email: '', direccion: '', activo: true };
        if (window.farmaClearDraft) {
            window.farmaClearDraft('{{ request()->getPathInfo() }}');
        }
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
            <a href="{{ route('clientes.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Clientes</a>
            <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-slate-800 dark:text-slate-200 font-semibold">Nuevo Registro</span>
        </nav>

        <!-- View Mode Switcher -->
        <div class="flex items-center space-x-2 self-start sm:self-auto">
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

    <!-- Header Title -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
        <div>
            <h1 class="text-lg sm:text-xl font-bold text-slate-900 dark:text-white">Registrar Nuevo Cliente / Paciente</h1>
            <p class="text-xs text-slate-600 dark:text-slate-400">
                Ingresa la filiación personal, documento de identidad y vías de contacto del paciente.
            </p>
        </div>
        <a href="{{ route('clientes.index') }}" 
           class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-xl transition shrink-0 self-start sm:self-auto shadow-2xs">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Volver a la lista</span>
        </a>
    </div>

    <!-- Form -->
    <form x-ref="clienteForm" method="POST" action="{{ route('clientes.store') }}">
        @csrf

        <!-- ========================================== -->
        <!-- MODO COMPACTO ESCRITORIO (SIN SCROLL)     -->
        <!-- ========================================== -->
        <template x-if="formLayout === 'compact'">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-300 dark:border-slate-800 shadow-md p-4 space-y-4 animate-fadeIn">
                
                <!-- Toolbar Superior -->
                <div class="flex items-center justify-between pb-3 border-b border-slate-300 dark:border-slate-800 bg-slate-100/80 dark:bg-slate-800/60 -mx-4 -mt-4 p-3 rounded-t-2xl">
                    <div class="flex items-center space-x-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shadow-xs"></span>
                        <span class="text-xs font-extrabold text-slate-800 dark:text-slate-200 uppercase tracking-wide">FICHA RÁPIDA DE CLIENTE / PACIENTE</span>
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
                            <span>Guardar Cliente</span>
                        </button>
                    </div>
                </div>

                <!-- Grid Compacta -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    
                    <!-- Panel 1: Filiación y Documento -->
                    <div class="p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/40 dark:bg-slate-800/30 space-y-3">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center space-x-1.5 border-b border-slate-200/60 dark:border-slate-700/60 pb-1.5">
                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <span>Datos del Paciente / Titular</span>
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Documento de Identidad (DNI / CE / RUC)
                            </label>
                            <input type="text" 
                                   name="documento" 
                                   x-model="formData.documento"
                                   placeholder="Ej. 72481920 o 20601234567" 
                                   class="w-full font-mono px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Nombre Completo o Razón Social <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" 
                                   name="nombre" 
                                   x-model="formData.nombre"
                                   required 
                                   autofocus
                                   placeholder="Ej. Juan Manuel Pérez Salazar" 
                                   class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                        </div>

                        <div class="pt-2">
                            <label class="inline-flex items-center space-x-2 cursor-pointer text-xs">
                                <input type="checkbox" name="activo" x-model="formData.activo" value="1" class="rounded border-slate-300 text-emerald-600 w-3.5 h-3.5">
                                <span class="text-[11px] font-semibold text-emerald-700 dark:text-emerald-400">Cliente Activo para Facturación POS</span>
                            </label>
                        </div>
                    </div>

                    <!-- Panel 2: Contacto y Residencia -->
                    <div class="p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/40 dark:bg-slate-800/30 space-y-3">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center space-x-1.5 border-b border-slate-200/60 dark:border-slate-700/60 pb-1.5">
                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            <span>Contacto y Dirección</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                    Teléfono / Celular
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
                                       placeholder="paciente@correo.com" 
                                       class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Dirección Domiciliaria / Entrega
                            </label>
                            <input type="text" 
                                   name="direccion" 
                                   x-model="formData.direccion"
                                   placeholder="Av. Javier Prado Este 2580, Dpto 402, Lima" 
                                   class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                        </div>
                    </div>
                </div>

                <!-- Footer Compacto -->
                <div class="pt-2 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between text-xs text-slate-500">
                    <span class="text-[11px]">Los datos se sincronizan automáticamente en borrador temporal.</span>
                    <button type="submit" 
                            class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs transition flex items-center space-x-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Guardar Cliente</span>
                    </button>
                </div>
            </div>
        </template>

        <!-- ========================================== -->
        <!-- MODO MODERNO (TARJETAS GRANDES)           -->
        <!-- ========================================== -->
        <template x-if="formLayout === 'modern'">
            <div class="space-y-6 animate-fadeIn">
                <!-- Section 1: Identificación Personal / Legal -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm space-y-5">
                    <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center space-x-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span>Datos Personales y Documentación</span>
                        </h3>
                        <p class="text-xs text-slate-400 mt-0.5">Identificación del paciente o empresa receptora del comprobante.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <!-- Nombre Completo -->
                        <div class="md:col-span-2">
                            <label for="nombre_mod" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Nombre Completo o Razón Social <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" 
                                   id="nombre_mod" 
                                   name="nombre" 
                                   x-model="formData.nombre"
                                   required 
                                   placeholder="Ej. Juan Manuel Pérez Salazar / Clínica San Rafael S.A.C." 
                                   class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition @error('nombre') border-rose-500 @enderror">
                            @error('nombre')
                                <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Documento de Identidad -->
                        <div>
                            <label for="documento_mod" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Documento (DNI / CE / RUC)
                            </label>
                            <input type="text" 
                                   id="documento_mod" 
                                   name="documento" 
                                   x-model="formData.documento"
                                   placeholder="Ej. 72481920 o 20601234567" 
                                   class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-mono text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition @error('documento') border-rose-500 @enderror">
                            @error('documento')
                                <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Section 2: Contacto y Dirección -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm space-y-5">
                    <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center space-x-2">
                            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                            <span>Información de Contacto y Residencia</span>
                        </h3>
                        <p class="text-xs text-slate-400 mt-0.5">Para emisión de comprobantes electrónicos y recordatorios de recetas.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <!-- Teléfono -->
                        <div>
                            <label for="telefono_mod" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Teléfono / Celular de Contacto
                            </label>
                            <input type="text" 
                                   id="telefono_mod" 
                                   name="telefono" 
                                   x-model="formData.telefono"
                                   placeholder="Ej. +51 987 654 321" 
                                   class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition @error('telefono') border-rose-500 @enderror">
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
                                   placeholder="paciente@correo.com" 
                                   class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition @error('email') border-rose-500 @enderror">
                            @error('email')
                                <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Dirección -->
                        <div class="md:col-span-2">
                            <label for="direccion_mod" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Dirección Domiciliaria / Entrega
                            </label>
                            <input type="text" 
                                   id="direccion_mod" 
                                   name="direccion" 
                                   x-model="formData.direccion"
                                   placeholder="Ej. Av. Javier Prado Este 2580, Dpto 402, San Borja" 
                                   class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition @error('direccion') border-rose-500 @enderror">
                            @error('direccion')
                                <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Section 3: Estado Operativo -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm">
                    <label class="flex items-center space-x-3 cursor-pointer">
                        <input type="checkbox" 
                               name="activo" 
                               x-model="formData.activo"
                               value="1" 
                               class="w-4 h-4 rounded bg-slate-50 dark:bg-slate-800 border-slate-300 dark:border-slate-700 text-emerald-600 focus:ring-emerald-500">
                        <div>
                            <span class="text-sm font-semibold text-slate-800 dark:text-slate-200 block">Cliente Activo en el Sistema</span>
                            <span class="text-xs text-slate-400">Permite asociar ventas en el Punto de Venta (POS) y emitir recetas médicas.</span>
                        </div>
                    </label>
                </div>

                <!-- Sticky Footer Actions -->
                <div class="sticky bottom-0 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-3.5 bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border-t border-slate-200 dark:border-slate-800 shadow-lg z-20 flex items-center justify-between transition-all rounded-b-2xl">
                    <a href="{{ route('clientes.index') }}" 
                       class="px-5 py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-xl transition">
                        Cancelar
                    </a>
                    <button type="submit" 
                            class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-sm font-semibold rounded-xl shadow-sm transition inline-flex items-center space-x-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Guardar Cliente</span>
                    </button>
                </div>
            </div>
        </template>
    </form>
</div>
@endsection
