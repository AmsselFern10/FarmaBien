@extends('layouts.app')

@section('title', 'Editar Proveedor: ' . $proveedor->nombre . ' - FarmaBien')

@section('content')
<div x-data="{
    formLayout: localStorage.getItem('farmaFormViewMode') || 'modern',
    setLayout(mode) {
        this.formLayout = mode;
        localStorage.setItem('farmaFormViewMode', mode);
        window.dispatchEvent(new CustomEvent('farma:layout-changed', { detail: { mode } }));
    },
    formData: (window.farmaGetDraft ? window.farmaGetDraft('{{ request()->getPathInfo() }}', {
        nombre: @js(old('nombre', $proveedor->nombre)),
        ruc: @js(old('ruc', $proveedor->ruc)),
        direccion: @js(old('direccion', $proveedor->direccion)),
        contacto: @js(old('contacto', $proveedor->contacto)),
        telefono: @js(old('telefono', $proveedor->telefono)),
        email: @js(old('email', $proveedor->email)),
        activo: @js(old('activo', $proveedor->activo) ? true : false)
    }) : {
        nombre: @js(old('nombre', $proveedor->nombre)),
        ruc: @js(old('ruc', $proveedor->ruc)),
        direccion: @js(old('direccion', $proveedor->direccion)),
        contacto: @js(old('contacto', $proveedor->contacto)),
        telefono: @js(old('telefono', $proveedor->telefono)),
        email: @js(old('email', $proveedor->email)),
        activo: @js(old('activo', $proveedor->activo) ? true : false)
    }),
    limpiarFormulario() {
        this.formData = {
            nombre: '',
            ruc: '',
            direccion: '',
            contacto: '',
            telefono: '',
            email: '',
            activo: true
        };
        if (window.farmaClearDraft) {
            window.farmaClearDraft('{{ request()->getPathInfo() }}');
        }
    }
}"
@keydown.window="if ($event.key === 'Escape' && formLayout === 'compact') { limpiarFormulario(); }"
:class="formLayout === 'compact' ? 'w-full' : 'max-w-5xl mx-auto'"
class="space-y-4 transition-all duration-200">
    
    <!-- Breadcrumb & View Toggle Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-1 border-b border-slate-200/80 dark:border-slate-800">
        <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
            <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
            <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <a href="{{ route('proveedores.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Proveedores</a>
            <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-slate-800 dark:text-slate-200 font-semibold truncate max-w-[200px]">Editar: {{ $proveedor->nombre }}</span>
        </nav>

        <!-- View Mode Switcher -->
        <div class="flex items-center space-x-2 self-start sm:self-auto">
            <span class="text-[11px] font-medium text-slate-400 dark:text-slate-500 hidden md:inline">Diseño:</span>
            <div class="inline-flex items-center p-0.5 rounded-xl bg-slate-100 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 text-xs font-semibold">
                <button type="button" 
                        @click="setLayout('modern')"
                        :class="formLayout === 'modern' ? 'bg-white dark:bg-slate-700 text-emerald-600 dark:text-emerald-400 shadow-xs' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-300'"
                        class="px-2.5 py-1 rounded-lg transition flex items-center space-x-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    <span>Moderna</span>
                </button>
                <button type="button" 
                        @click="setLayout('compact')"
                        :class="formLayout === 'compact' ? 'bg-white dark:bg-slate-700 text-emerald-600 dark:text-emerald-400 shadow-xs' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-300'"
                        class="px-2.5 py-1 rounded-lg transition flex items-center space-x-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <span>Compacta (POS / Escritorio)</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-lg sm:text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <span>Editar Proveedor: {{ $proveedor->nombre }}</span>
                <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $proveedor->activo ? 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300' : 'bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400' }}">
                    {{ $proveedor->activo ? 'Activo' : 'Inactivo' }}
                </span>
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">
                Actualiza los datos fiscales, comerciales y de contacto del distribuidor.
            </p>
        </div>
        <div class="flex items-center space-x-2 shrink-0">
            <a href="{{ route('proveedores.show', $proveedor) }}" 
               class="px-3.5 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-xl transition">
                Ver Ficha
            </a>
            <a href="{{ route('proveedores.index') }}" 
               class="px-3.5 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-xl transition">
                &larr; Volver
            </a>
        </div>
    </div>

    <!-- Form -->
    <form x-ref="proveedorEditForm" method="POST" action="{{ route('proveedores.update', $proveedor) }}">
        @csrf
        @method('PUT')

        <!-- ========================================== -->
        <!-- MODO COMPACTO ESCRITORIO (SIN SCROLL)     -->
        <!-- ========================================== -->
        <template x-if="formLayout === 'compact'">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-300 dark:border-slate-800 shadow-sm p-4 space-y-4 animate-fadeIn">
                
                <!-- Toolbar Superior -->
                <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800 bg-slate-50/90 dark:bg-slate-800/40 -mx-4 -mt-4 p-3 rounded-t-2xl">
                    <div class="flex items-center space-x-2">
                        <span class="w-2.5 h-2.5 rounded-full {{ $proveedor->activo ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                        <span class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase">EDICIÓN RÁPIDA: {{ $proveedor->nombre }}</span>
                        <span class="text-[10px] px-2 py-0.5 rounded-md bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300 font-mono">Esc = Limpiar</span>
                    </div>

                    <div class="flex items-center space-x-2">
                        <button type="button" 
                                @click="limpiarFormulario()"
                                class="px-3 py-1.5 rounded-lg border border-slate-300 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 text-xs font-medium transition cursor-pointer">
                            Limpiar (Esc)
                        </button>
                        <button type="submit" 
                                class="px-4 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-xs transition flex items-center space-x-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Guardar Cambios</span>
                        </button>
                    </div>
                </div>

                <!-- Grid Compacta -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    
                    <!-- Panel 1: Datos Fiscales -->
                    <div class="p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/40 dark:bg-slate-800/30 space-y-3">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center space-x-1.5 border-b border-slate-200/60 dark:border-slate-700/60 pb-1.5">
                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            <span>Identificación Fiscal & Razón Social</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-2.5">
                            <div class="sm:col-span-5">
                                <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                    RUC (11 Dígitos) <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" 
                                       name="ruc" 
                                       x-model="formData.ruc"
                                       required 
                                       maxlength="11" 
                                       placeholder="20123456789" 
                                       class="w-full font-mono font-medium px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                            </div>

                            <div class="sm:col-span-7">
                                <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                    Razón Social / Comercial <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" 
                                       name="nombre" 
                                       x-model="formData.nombre"
                                       required 
                                       autofocus
                                       placeholder="Distribuidora Droguería S.A.C." 
                                       class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Dirección Fiscal / Almacén Central
                            </label>
                            <input type="text" 
                                   name="direccion" 
                                   x-model="formData.direccion"
                                   placeholder="Av. Separadora Industrial 1450, Ate, Lima" 
                                   class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                        </div>

                        <div class="pt-2">
                            <label class="inline-flex items-center space-x-2 cursor-pointer text-xs">
                                <input type="checkbox" name="activo" x-model="formData.activo" value="1" class="rounded border-slate-300 text-emerald-600 w-3.5 h-3.5">
                                <span class="text-[11px] font-semibold text-emerald-700 dark:text-emerald-400">Proveedor Habilitado para Órdenes de Compra</span>
                            </label>
                        </div>
                    </div>

                    <!-- Panel 2: Contacto y Comunicaciones -->
                    <div class="p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/40 dark:bg-slate-800/30 space-y-3">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center space-x-1.5 border-b border-slate-200/60 dark:border-slate-700/60 pb-1.5">
                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            <span>Datos de Contacto y Ventas</span>
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Ejecutivo / Persona de Contacto
                            </label>
                            <input type="text" 
                                   name="contacto" 
                                   x-model="formData.contacto"
                                   placeholder="Ej. Lic. Roberto Gómez (Asesor Comercial)" 
                                   class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                    Teléfono de Pedidos
                                </label>
                                <input type="text" 
                                       name="telefono" 
                                       x-model="formData.telefono"
                                       placeholder="(01) 456-7890 / +51 987..." 
                                       class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                            </div>

                            <div>
                                <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                    Correo para Facturas
                                </label>
                                <input type="email" 
                                       name="email" 
                                       x-model="formData.email"
                                       placeholder="ventas@drogueria.com" 
                                       class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Compacto -->
                <div class="pt-2 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between text-xs text-slate-500">
                    <span class="text-[11px]">Modificando proveedor #{{ $proveedor->id }}</span>
                    <button type="submit" 
                            class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs transition flex items-center space-x-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Guardar Cambios</span>
                    </button>
                </div>
            </div>
        </template>

        <!-- ========================================== -->
        <!-- MODO MODERNO (TARJETAS GRANDES)           -->
        <!-- ========================================== -->
        <template x-if="formLayout === 'modern'">
            <div class="space-y-6 animate-fadeIn">
                <!-- Section 1: Identificación Fiscal -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm space-y-5">
                    <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center space-x-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span>Información Fiscal y Comercial</span>
                        </h3>
                        <p class="text-xs text-slate-400 mt-0.5">Identificación tributaria y razón social del proveedor.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <!-- Razón Social / Nombre -->
                        <div class="md:col-span-2">
                            <label for="nombre_mod" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Razón Social / Nombre Comercial <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" 
                                   id="nombre_mod" 
                                   name="nombre" 
                                   x-model="formData.nombre"
                                   required 
                                   placeholder="Ej. Distribuidora Farmacéutica del Perú S.A.C." 
                                   class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition @error('nombre') border-rose-500 @enderror">
                            @error('nombre')
                                <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- RUC -->
                        <div>
                            <label for="ruc_mod" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                RUC (11 Dígitos) <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" 
                                   id="ruc_mod" 
                                   name="ruc" 
                                   x-model="formData.ruc"
                                   required 
                                   maxlength="11" 
                                   placeholder="Ej. 20123456789" 
                                   class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-mono font-medium text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition @error('ruc') border-rose-500 @enderror">
                            @error('ruc')
                                <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Dirección Fiscal -->
                        <div class="md:col-span-3">
                            <label for="direccion_mod" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Dirección Fiscal / Almacén Central de Despacho
                            </label>
                            <input type="text" 
                                   id="direccion_mod" 
                                   name="direccion" 
                                   x-model="formData.direccion"
                                   placeholder="Ej. Av. Separadora Industrial 1450, Urb. Vulcano, Ate, Lima" 
                                   class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition @error('direccion') border-rose-500 @enderror">
                            @error('direccion')
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
                            <span>Datos de Contacto y Ventas</span>
                        </h3>
                        <p class="text-xs text-slate-400 mt-0.5">Ejecutivo de cuenta, cotizaciones y pedidos de reposición.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <!-- Contacto -->
                        <div>
                            <label for="contacto_mod" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Ejecutivo / Persona de Contacto
                            </label>
                            <input type="text" 
                                   id="contacto_mod" 
                                   name="contacto" 
                                   x-model="formData.contacto"
                                   placeholder="Ej. Lic. Roberto Gómez (Asesor Comercial)" 
                                   class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition @error('contacto') border-rose-500 @enderror">
                            @error('contacto')
                                <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Teléfono -->
                        <div>
                            <label for="telefono_mod" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Teléfono / Móvil de Pedidos
                            </label>
                            <input type="text" 
                                   id="telefono_mod" 
                                   name="telefono" 
                                   x-model="formData.telefono"
                                   placeholder="Ej. (01) 456-7890 / +51 987 654 321" 
                                   class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition @error('telefono') border-rose-500 @enderror">
                            @error('telefono')
                                <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email_mod" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Correo para Facturas y Pedidos
                            </label>
                            <input type="email" 
                                   id="email_mod" 
                                   name="email" 
                                   x-model="formData.email"
                                   placeholder="ventas@drogueria.com" 
                                   class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition @error('email') border-rose-500 @enderror">
                            @error('email')
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
                            <span class="text-sm font-semibold text-slate-800 dark:text-slate-200 block">Proveedor Activo para Órdenes de Compra</span>
                            <span class="text-xs text-slate-400">Permite registrar compras y recepcionar lotes de medicamentos de esta droguería.</span>
                        </div>
                    </label>
                </div>

                <!-- Sticky Footer Actions -->
                <div class="sticky bottom-0 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-3.5 bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border-t border-slate-200 dark:border-slate-800 shadow-lg z-20 flex items-center justify-between transition-all rounded-b-2xl">
                    <a href="{{ route('proveedores.index') }}" 
                       class="px-5 py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-xl transition">
                        Cancelar
                    </a>
                    <button type="submit" 
                            class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-sm font-semibold rounded-xl shadow-sm transition inline-flex items-center space-x-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Guardar Cambios</span>
                    </button>
                </div>
            </div>
        </template>
    </form>
</div>
@endsection
