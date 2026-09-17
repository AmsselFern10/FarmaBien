@extends('layouts.app')

@section('title', 'Nueva Categoría - FarmaBien')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Breadcrumb & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('categorias.index') }}" class="hover:text-emerald-600 transition">Categorías</a>
                <span>/</span>
                <span class="text-slate-800 dark:text-slate-300 font-medium">Nueva</span>
            </div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">Registrar Categoría Terapéutica</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Crea una nueva familia o clase terapéutica para clasificar medicamentos e insumos.
            </p>
        </div>
        <a href="{{ route('categorias.index') }}" 
           class="inline-flex items-center space-x-1.5 px-3.5 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-xl transition shrink-0">
            <span>&larr;</span>
            <span>Volver a la lista</span>
        </a>
    </div>

    <!-- Form -->
    <form method="POST" action="{{ route('categorias.store') }}" class="space-y-6">
        @csrf

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
                    <label for="nombre" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        Nombre de la Categoría <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" 
                           id="nombre" 
                           name="nombre" 
                           value="{{ old('nombre') }}" 
                           required 
                           autofocus 
                           placeholder="Ej. Analgésicos y Antipiréticos, Antibióticos de Amplio Espectro, Antihipertensivos..." 
                           class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 focus-visible:ring-2 focus-visible:ring-emerald-500 transition @error('nombre') border-rose-500 @enderror">
                    @error('nombre')
                        <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Descripción -->
                <div>
                    <label for="descripcion" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        Descripción Clínica / Indicaciones Generales
                    </label>
                    <textarea id="descripcion" 
                              name="descripcion" 
                              rows="3" 
                              placeholder="Breve reseña sobre el mecanismo de acción, afecciones tratadas o notas de dispensación..." 
                              class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 focus-visible:ring-2 focus-visible:ring-emerald-500 transition @error('descripcion') border-rose-500 @enderror">{{ old('descripcion') }}</textarea>
                    @error('descripcion')
                        <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Card: Estado Operativo -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm">
            <label class="flex items-center space-x-3 cursor-pointer">
                <input type="checkbox" 
                       name="activo" 
                       value="1" 
                       {{ old('activo', 1) ? 'checked' : '' }} 
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
                    <span>Guardar Categoría</span>
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
