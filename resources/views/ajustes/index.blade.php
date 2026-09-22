@extends('layouts.app')

@section('title', 'Ajustes del Sistema')

@section('content')
<div class="space-y-4" x-data="{
    activeTab: '{{ $tab ?? 'empresa' }}',
    logoPreview: '{{ !empty($configs['empresa_logo']['valor']) ? asset('storage/' . $configs['empresa_logo']['valor']) : '' }}',
    previewLogo(event) {
        const file = event.target.files[0];
        if (file) this.logoPreview = URL.createObjectURL(file);
    },
    catalogoActivo: {{ ($configs['catalogo_publico_activo']['valor'] ?? '1') == '1' ? 'true' : 'false' }}
}">

    {{-- Breadcrumb --}}
    <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-800 dark:text-slate-200 font-semibold">Ajustes del Sistema</span>
    </nav>

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-1 border-b border-slate-300/80 dark:border-slate-800">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">Ajustes y Configuración</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Datos fiscales de la farmacia, preferencias de interfaz y control de módulos.
            </p>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <a href="{{ route('catalogo.publico') }}" target="_blank"
               class="px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-xl text-xs font-semibold shadow-xs transition flex items-center space-x-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                <span>Ver Catálogo Público</span>
            </a>
        </div>
    </div>

    {{-- Flash --}}
    @if(session('success'))
    <div class="p-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-300 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 text-xs flex items-center justify-between">
        <div class="flex items-center space-x-2">
            <svg class="w-4 h-4 shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('success') }}</span>
        </div>
        <button onclick="this.parentElement.remove()" class="text-slate-400 hover:text-slate-600 font-bold">&times;</button>
    </div>
    @endif
    @if($errors->any())
    <div class="p-3 rounded-xl bg-rose-50 dark:bg-rose-950/60 border border-rose-300 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-xs">
        <ul class="list-disc list-inside space-y-0.5">
            @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
        </ul>
    </div>
    @endif

    {{-- Tabs --}}
    <div class="border-b border-slate-200 dark:border-slate-800">
        <nav class="flex space-x-0 overflow-x-auto scrollbar-none" aria-label="Ajustes tabs">
            <button @click="activeTab = 'empresa'" type="button"
                    :class="activeTab === 'empresa'
                        ? 'border-b-2 border-indigo-600 text-indigo-700 dark:text-indigo-400 font-semibold'
                        : 'border-b-2 border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200'"
                    class="group inline-flex items-center space-x-2 py-3 px-4 text-xs transition shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                <span>Datos del Local</span>
            </button>
            <button @click="activeTab = 'interfaz'" type="button"
                    :class="activeTab === 'interfaz'
                        ? 'border-b-2 border-indigo-600 text-indigo-700 dark:text-indigo-400 font-semibold'
                        : 'border-b-2 border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200'"
                    class="group inline-flex items-center space-x-2 py-3 px-4 text-xs transition shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17H3a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2h-2"/></svg>
                <span>Preferencias de Interfaz</span>
            </button>
            <button @click="activeTab = 'modulos'" type="button"
                    :class="activeTab === 'modulos'
                        ? 'border-b-2 border-indigo-600 text-indigo-700 dark:text-indigo-400 font-semibold'
                        : 'border-b-2 border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200'"
                    class="group inline-flex items-center space-x-2 py-3 px-4 text-xs transition shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                <span>Control de Módulos</span>
            </button>
        </nav>
    </div>

    {{-- Formulario principal --}}
    <form action="{{ route('ajustes.update') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
        @csrf
        <input type="hidden" name="tab" :value="activeTab">

        {{-- TAB 1: DATOS DEL LOCAL --}}
        <div x-show="activeTab === 'empresa'" x-cloak class="space-y-4">
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs p-5">
                <h2 class="text-sm font-bold text-slate-800 dark:text-slate-200 border-b border-slate-100 dark:border-slate-800 pb-2 mb-4">
                    Identificación Fiscal y Contacto
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 -mt-2 mb-4">Estos datos se sincronizan automáticamente con tickets de venta, reportes PDF y el catálogo público.</p>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nombre del Local / Farmacia</label>
                        <input type="text" name="empresa_nombre" maxlength="100"
                               value="{{ $configs['empresa_nombre']['valor'] ?? '' }}"
                               class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm focus:ring-emerald-500 focus:border-emerald-500"
                               placeholder="Ej: FarmaBien">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">RUC / NIT</label>
                        <input type="text" name="empresa_ruc" maxlength="50"
                               value="{{ $configs['empresa_ruc']['valor'] ?? '' }}"
                               class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm focus:ring-emerald-500 focus:border-emerald-500 font-mono"
                               placeholder="Ej: J-12345678-9">
                    </div>
                    <div class="sm:col-span-2 lg:col-span-3">
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Razón Social</label>
                        <input type="text" name="empresa_razon_social" maxlength="150"
                               value="{{ $configs['empresa_razon_social']['valor'] ?? '' }}"
                               class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm focus:ring-emerald-500 focus:border-emerald-500"
                               placeholder="Razón social o denominación legal">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Teléfono Principal</label>
                        <input type="text" name="empresa_telefono" maxlength="100"
                               value="{{ $configs['empresa_telefono']['valor'] ?? '' }}"
                               class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm focus:ring-emerald-500 focus:border-emerald-500"
                               placeholder="Ej: 0414-123-4567">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">WhatsApp</label>
                        <input type="text" name="empresa_whatsapp" maxlength="50"
                               value="{{ $configs['empresa_whatsapp']['valor'] ?? '' }}"
                               class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm focus:ring-emerald-500 focus:border-emerald-500"
                               placeholder="Ej: 58414-123-4567">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Correo Electrónico</label>
                        <input type="email" name="empresa_email" maxlength="100"
                               value="{{ $configs['empresa_email']['valor'] ?? '' }}"
                               class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm focus:ring-emerald-500 focus:border-emerald-500"
                               placeholder="contacto@farmacia.com">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Dirección Física</label>
                        <input type="text" name="empresa_direccion" maxlength="255"
                               value="{{ $configs['empresa_direccion']['valor'] ?? '' }}"
                               class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm focus:ring-emerald-500 focus:border-emerald-500"
                               placeholder="Calle / Av. / Sector / Urbanización">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Ciudad / Municipio</label>
                        <input type="text" name="empresa_ciudad" maxlength="100"
                               value="{{ $configs['empresa_ciudad']['valor'] ?? '' }}"
                               class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm focus:ring-emerald-500 focus:border-emerald-500"
                               placeholder="Ej: Caracas">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Eslogan / Lema</label>
                        <input type="text" name="empresa_slogan" maxlength="255"
                               value="{{ $configs['empresa_slogan']['valor'] ?? '' }}"
                               class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm focus:ring-emerald-500 focus:border-emerald-500"
                               placeholder="Aparece en el encabezado del catálogo y reportes">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Pie de Ticket de Venta</label>
                        <input type="text" name="empresa_pie_ticket" maxlength="255"
                               value="{{ $configs['empresa_pie_ticket']['valor'] ?? '' }}"
                               class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm focus:ring-emerald-500 focus:border-emerald-500"
                               placeholder="Ej: ¡Gracias por su compra!">
                    </div>
                </div>
            </div>

            {{-- Logo --}}
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs p-5">
                <h2 class="text-sm font-bold text-slate-800 dark:text-slate-200 border-b border-slate-100 dark:border-slate-800 pb-2 mb-4">
                    Logotipo Institucional
                </h2>
                <div class="flex flex-col sm:flex-row gap-5 items-start">
                    <div class="w-24 h-24 rounded-xl border-2 border-dashed border-slate-300 dark:border-slate-700 flex items-center justify-center bg-slate-50 dark:bg-slate-800 shrink-0 overflow-hidden">
                        <template x-if="logoPreview">
                            <img :src="logoPreview" class="w-full h-full object-contain p-1">
                        </template>
                        <template x-if="!logoPreview">
                            <svg class="w-8 h-8 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </template>
                    </div>
                    <div class="flex-1 space-y-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Subir imagen (PNG, JPG, SVG, WebP — máx. 2MB)</label>
                            <input type="file" name="empresa_logo" accept="image/*" @change="previewLogo($event)"
                                   class="block w-full text-xs text-slate-500 dark:text-slate-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 dark:file:bg-emerald-950/60 dark:file:text-emerald-300 hover:file:bg-emerald-100 file:transition cursor-pointer">
                        </div>
                        @if(!empty($configs['empresa_logo']['valor']))
                        <label class="inline-flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" name="eliminar_logo" value="1"
                                   class="rounded border-rose-300 text-rose-600 focus:ring-rose-500">
                            <span class="text-xs text-rose-600 dark:text-rose-400 font-semibold">Eliminar logotipo actual</span>
                        </label>
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit"
                        class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-semibold shadow-xs transition">
                    Guardar Datos del Local
                </button>
            </div>
        </div>

        {{-- TAB 2: PREFERENCIAS DE INTERFAZ --}}
        <div x-show="activeTab === 'interfaz'" x-cloak class="space-y-4">
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs p-5">
                <h2 class="text-sm font-bold text-slate-800 dark:text-slate-200 border-b border-slate-100 dark:border-slate-800 pb-2 mb-4">
                    Tema Visual
                </h2>
                @php $temaActual = $configs['interfaz_modo_oscuro_default']['valor'] ?? 'system'; @endphp
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 max-w-xl">
                    <label class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition
                        {{ $temaActual === 'light' ? 'border-indigo-400 dark:border-indigo-600 bg-indigo-50 dark:bg-indigo-950/30' : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}">
                        <input type="radio" name="interfaz_modo_oscuro_default" value="light"
                               {{ $temaActual === 'light' ? 'checked' : '' }}
                               class="text-indigo-600 focus:ring-indigo-500">
                        <div>
                            <svg class="w-4 h-4 text-amber-500 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            <p class="text-xs font-semibold text-slate-800 dark:text-slate-200">Modo Claro</p>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition
                        {{ $temaActual === 'dark' ? 'border-indigo-400 dark:border-indigo-600 bg-indigo-50 dark:bg-indigo-950/30' : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}">
                        <input type="radio" name="interfaz_modo_oscuro_default" value="dark"
                               {{ $temaActual === 'dark' ? 'checked' : '' }}
                               class="text-indigo-600 focus:ring-indigo-500">
                        <div>
                            <svg class="w-4 h-4 text-slate-500 dark:text-slate-300 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                            <p class="text-xs font-semibold text-slate-800 dark:text-slate-200">Modo Oscuro</p>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition
                        {{ $temaActual === 'system' ? 'border-indigo-400 dark:border-indigo-600 bg-indigo-50 dark:bg-indigo-950/30' : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}">
                        <input type="radio" name="interfaz_modo_oscuro_default" value="system"
                               {{ $temaActual === 'system' ? 'checked' : '' }}
                               class="text-indigo-600 focus:ring-indigo-500">
                        <div>
                            <svg class="w-4 h-4 text-slate-500 dark:text-slate-400 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17H3a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2h-2"/></svg>
                            <p class="text-xs font-semibold text-slate-800 dark:text-slate-200">Automático</p>
                            <p class="text-[11px] text-slate-400 dark:text-slate-500">Sistema operativo</p>
                        </div>
                    </label>
                </div>
                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-3">El tema se aplica en la siguiente carga de página tras guardar.</p>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs p-5">
                <h2 class="text-sm font-bold text-slate-800 dark:text-slate-200 border-b border-slate-100 dark:border-slate-800 pb-2 mb-4">
                    Diseño de Formularios y Paginación
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 max-w-xl">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-2">Vista predeterminada en Crear y Editar</label>
                        @php $vistaFormActual = $configs['interfaz_vista_formularios_default']['valor'] ?? 'modern'; @endphp
                        <div class="space-y-2">
                            <label class="flex items-center gap-2 p-2.5 rounded-xl border cursor-pointer transition {{ $vistaFormActual === 'modern' ? 'border-indigo-400 dark:border-indigo-600 bg-indigo-50 dark:bg-indigo-950/30' : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}">
                                <input type="radio" name="interfaz_vista_formularios_default" value="modern"
                                       {{ $vistaFormActual === 'modern' ? 'checked' : '' }}
                                       class="text-indigo-600 focus:ring-indigo-500">
                                <div>
                                    <span class="text-xs font-semibold text-slate-800 dark:text-slate-200 block">Vista Moderna</span>
                                    <span class="text-[10px] text-slate-500 dark:text-slate-400">Tarjetas completas estructuradas</span>
                                </div>
                            </label>
                            <label class="flex items-center gap-2 p-2.5 rounded-xl border cursor-pointer transition {{ $vistaFormActual === 'compact' ? 'border-indigo-400 dark:border-indigo-600 bg-indigo-50 dark:bg-indigo-950/30' : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}">
                                <input type="radio" name="interfaz_vista_formularios_default" value="compact"
                                       {{ $vistaFormActual === 'compact' ? 'checked' : '' }}
                                       class="text-indigo-600 focus:ring-indigo-500">
                                <div>
                                    <span class="text-xs font-semibold text-slate-800 dark:text-slate-200 block">Vista Compacta (POS / ERP)</span>
                                    <span class="text-[10px] text-slate-500 dark:text-slate-400">Ficha rápida sin desplazamiento</span>
                                </div>
                            </label>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-2">Registros por página</label>
                        @php $paginacion = $configs['interfaz_registros_por_pagina']['valor'] ?? '25'; @endphp
                        <select name="interfaz_registros_por_pagina"
                                class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="10" {{ $paginacion == '10' ? 'selected' : '' }}>10 registros por página</option>
                            <option value="15" {{ $paginacion == '15' ? 'selected' : '' }}>15 registros por página</option>
                            <option value="25" {{ $paginacion == '25' ? 'selected' : '' }}>25 registros por página</option>
                            <option value="50" {{ $paginacion == '50' ? 'selected' : '' }}>50 registros por página</option>
                            <option value="100" {{ $paginacion == '100' ? 'selected' : '' }}>100 registros por página</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit"
                        class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-semibold shadow-xs transition">
                    Guardar Preferencias de Interfaz
                </button>
            </div>
        </div>

        {{-- TAB 3: CONTROL DE MÓDULOS --}}
        <div x-show="activeTab === 'modulos'" x-cloak class="space-y-4">
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs p-5">
                <h2 class="text-sm font-bold text-slate-800 dark:text-slate-200 border-b border-slate-100 dark:border-slate-800 pb-2 mb-4">
                    Catálogo Público para Clientes
                </h2>
                <div class="space-y-3 max-w-lg">
                    <label class="flex items-start gap-3 p-3.5 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/40 cursor-pointer transition">
                        <input type="checkbox" name="catalogo_publico_activo" value="1"
                               {{ ($configs['catalogo_publico_activo']['valor'] ?? '1') == '1' ? 'checked' : '' }}
                               class="mt-0.5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                        <div>
                            <p class="text-xs font-semibold text-slate-800 dark:text-slate-200">Activar Catálogo Público</p>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Permite a los clientes consultar el catálogo de productos disponibles sin necesidad de iniciar sesión.</p>
                        </div>
                    </label>
                    <label class="flex items-start gap-3 p-3.5 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/40 cursor-pointer transition">
                        <input type="checkbox" name="catalogo_publico_mostrar_precios" value="1"
                               {{ ($configs['catalogo_publico_mostrar_precios']['valor'] ?? '1') == '1' ? 'checked' : '' }}
                               class="mt-0.5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                        <div>
                            <p class="text-xs font-semibold text-slate-800 dark:text-slate-200">Mostrar precios en el catálogo</p>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Muestra el precio de venta de cada producto en el catálogo público.</p>
                        </div>
                    </label>
                    <label class="flex items-start gap-3 p-3.5 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/40 cursor-pointer transition">
                        <input type="checkbox" name="catalogo_publico_mostrar_stock" value="1"
                               {{ ($configs['catalogo_publico_mostrar_stock']['valor'] ?? '0') == '1' ? 'checked' : '' }}
                               class="mt-0.5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                        <div>
                            <p class="text-xs font-semibold text-slate-800 dark:text-slate-200">Mostrar disponibilidad de stock</p>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Muestra si el producto está disponible o agotado en el catálogo público.</p>
                        </div>
                    </label>
                </div>
                <div class="mt-4 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 flex items-center justify-between gap-3">
                    <p class="text-xs text-slate-600 dark:text-slate-400">URL del catálogo público:</p>
                    <a href="{{ route('catalogo.publico') }}" target="_blank"
                       class="text-xs font-mono text-emerald-700 dark:text-emerald-400 hover:underline truncate max-w-xs">
                        {{ route('catalogo.publico') }}
                    </a>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs p-5">
                <h2 class="text-sm font-bold text-slate-800 dark:text-slate-200 border-b border-slate-100 dark:border-slate-800 pb-2 mb-4">
                    Control de Cajas
                </h2>
                <div class="max-w-lg">
                    <label class="flex items-start gap-3 p-3.5 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/40 cursor-pointer transition">
                        <input type="checkbox" name="modulo_cajas_estricto" value="1"
                               {{ ($configs['modulo_cajas_estricto']['valor'] ?? '0') == '1' ? 'checked' : '' }}
                               class="mt-0.5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                        <div>
                            <p class="text-xs font-semibold text-slate-800 dark:text-slate-200">Modo estricto de cajas</p>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Cuando está activo, los cajeros deben tener un turno de caja abierto para poder registrar ventas. Sin turno activo, se bloquea el acceso al POS.</p>
                        </div>
                    </label>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit"
                        class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-semibold shadow-xs transition">
                    Guardar Configuración de Módulos
                </button>
            </div>
        </div>
    </form>

</div>
@endsection
