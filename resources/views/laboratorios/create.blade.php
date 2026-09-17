@extends('layouts.app')

@section('title', 'Nuevo Laboratorio - FarmaBien')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <!-- Breadcrumb & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('laboratorios.index') }}" class="hover:text-emerald-600 transition">Laboratorios</a>
                <span>/</span>
                <span class="text-slate-800 dark:text-slate-300 font-medium">Nuevo Registro</span>
            </div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">Registrar Nuevo Laboratorio</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Ingresa los datos generales y de contacto de la casa fabricante de medicamentos.
            </p>
        </div>
        <a href="{{ route('laboratorios.index') }}" 
           class="inline-flex items-center space-x-1.5 px-3.5 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-xl transition shrink-0">
            <span>&larr;</span>
            <span>Volver a la lista</span>
        </a>
    </div>

    <!-- Form -->
    <form method="POST" action="{{ route('laboratorios.store') }}" class="space-y-6">
        @csrf

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
                    <label for="nombre" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        Razón Social / Nombre Comercial <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" 
                           id="nombre" 
                           name="nombre" 
                           value="{{ old('nombre') }}" 
                           required 
                           autofocus 
                           placeholder="Ej. Laboratorios Pfizer S.A., Genfar S.A., Bayer Pharma..." 
                           class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 focus-visible:ring-2 focus-visible:ring-emerald-500 transition @error('nombre') border-rose-500 @enderror">
                    @error('nombre')
                        <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Código Interno / Sigla -->
                <div>
                    <label for="codigo" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        Código / Sigla Abreviada
                    </label>
                    <input type="text" 
                           id="codigo" 
                           name="codigo" 
                           value="{{ old('codigo') }}" 
                           placeholder="Ej. PFIZ, GNF, BYR" 
                           class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-mono uppercase text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 focus-visible:ring-2 focus-visible:ring-emerald-500 transition @error('codigo') border-rose-500 @enderror">
                    @error('codigo')
                        <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- País de Origen -->
                <div class="md:col-span-1">
                    <label for="pais_origen" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        País de Origen
                    </label>
                    <input type="text" 
                           id="pais_origen" 
                           name="pais_origen" 
                           value="{{ old('pais_origen') }}" 
                           placeholder="Ej. Perú, Alemania, Suiza, EE.UU." 
                           class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 focus-visible:ring-2 focus-visible:ring-emerald-500 transition @error('pais_origen') border-rose-500 @enderror">
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
                    <label for="contacto" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        Representante / Contacto
                    </label>
                    <input type="text" 
                           id="contacto" 
                           name="contacto" 
                           value="{{ old('contacto') }}" 
                           placeholder="Ej. Lic. Carlos Mendoza (Visitador Médico)" 
                           class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 focus-visible:ring-2 focus-visible:ring-emerald-500 transition @error('contacto') border-rose-500 @enderror">
                    @error('contacto')
                        <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Teléfono -->
                <div>
                    <label for="telefono" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        Teléfono / Línea Directa
                    </label>
                    <input type="text" 
                           id="telefono" 
                           name="telefono" 
                           value="{{ old('telefono') }}" 
                           placeholder="Ej. +51 987 654 321 / (01) 444-5555" 
                           class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 focus-visible:ring-2 focus-visible:ring-emerald-500 transition @error('telefono') border-rose-500 @enderror">
                    @error('telefono')
                        <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        Correo Electrónico
                    </label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           value="{{ old('email') }}" 
                           placeholder="contacto@laboratorio.com" 
                           class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 focus-visible:ring-2 focus-visible:ring-emerald-500 transition @error('email') border-rose-500 @enderror">
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
                       value="1" 
                       {{ old('activo', 1) ? 'checked' : '' }} 
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
                    <span>Guardar Laboratorio</span>
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
