@extends('layouts.app')

@section('title', 'Registrar Nueva Receta')

@section('header')
    Editar Receta
@endsection

@section('page-actions')
    <div>
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white">
            Editar Receta Médica
        </h2>
        <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
            Completa los datos obligatorios y agrega información clínica opcional.
        </p>
    </div>
    <div class="flex gap-3">
        <a href="{{ route('recetas.index') }}" 
           class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-slate-700 dark:text-slate-300 font-semibold rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200 shadow-sm">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Volver
        </a>
    </div>
@endsection

@section('content')
<div class="max-w-5xl mx-auto">

    @if ($errors->any())
        <div class="rounded-xl border border-rose-200 dark:border-rose-900/40 bg-rose-50 dark:bg-rose-900/20 p-4 mb-6">
            <div class="flex gap-3">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-rose-600 dark:text-rose-300" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-rose-800 dark:text-rose-200">Se encontraron {{ $errors->count() }} errores</h3>
                    <ul class="mt-2 text-sm text-rose-700 dark:text-rose-200/90 list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        <form action="{{ route('recetas.update', $receta) }}" method="POST" class="p-6">
            @csrf
            @method('PUT')

            <!-- Información de la Receta -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Datos de la Receta
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <div>
                        <label for="numero_receta" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Número de Receta <span class="text-rose-500">*</span>
                        </label>
                        <input type="text"
                               name="numero_receta"
                               id="numero_receta"
                               value="{{ old('numero_receta', $receta->numero_receta) }}"
                               class="w-full px-3 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:border-amber-500 @error('numero_receta') border-rose-500 @enderror"
                               required>
                        @error('numero_receta')
                            <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="fecha" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Fecha de Emisión <span class="text-rose-500">*</span>
                        </label>
                        <input type="date"
                               name="fecha"
                               id="fecha"
                               value="{{ old('fecha', $receta->fecha->format('Y-m-d')) }}"
                               max="{{ now()->format('Y-m-d') }}"
                               class="w-full px-3 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:border-amber-500 @error('fecha') border-rose-500 @enderror"
                               required>
                        @error('fecha')
                            <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="cliente_id" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Cliente/Paciente <span class="text-rose-500">*</span>
                        </label>
                        <select name="cliente_id"
                                id="cliente_id"
                                class="w-full px-3 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:border-amber-500 @error('cliente_id') border-rose-500 @enderror"
                                required>
                            <option value="">Seleccione un cliente</option>
                            @foreach($clientes as $cliente)
                                <option value="{{ $cliente->id }}" {{ (string)old('cliente_id', $receta->cliente_id) === (string)$cliente->id ? 'selected' : '' }}>
                                    {{ $cliente->nombre }} - {{ $cliente->tipo_documento }}: {{ $cliente->documento }}
                                </option>
                            @endforeach
                        </select>
                        @error('cliente_id')
                            <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                </div>
            </div>

            <!-- Información del Médico -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    Datos del Médico
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <div>
                        <label for="medico" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Nombre del Médico <span class="text-rose-500">*</span>
                        </label>
                        <input type="text"
                               name="medico"
                               id="medico"
                               value="{{ old('medico', $receta->medico) }}"
                               class="w-full px-3 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:border-amber-500 @error('medico') border-rose-500 @enderror"
                               required>
                        @error('medico')
                            <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="especialidad" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Especialidad</label>
                        <input type="text"
                               name="especialidad"
                               id="especialidad"
                               value="{{ old('especialidad', $receta->especialidad) }}"
                               class="w-full px-3 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:border-amber-500 @error('especialidad') border-rose-500 @enderror">
                        @error('especialidad')
                            <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Detalles Clínicos -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    Detalles Clínicos
                </h3>

                <div class="grid grid-cols-1 gap-6">

                    <div>
                        <label for="diagnostico" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Diagnóstico</label>
                        <textarea name="diagnostico"
                                  id="diagnostico"
                                  rows="3"
                                  class="w-full px-3 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:border-amber-500 @error('diagnostico') border-rose-500 @enderror">{{ old('diagnostico', $receta->diagnostico) }}</textarea>
                        @error('diagnostico')
                            <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="observaciones" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Observaciones</label>
                        <textarea name="observaciones"
                                  id="observaciones"
                                  rows="3"
                                  class="w-full px-3 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:border-amber-500 @error('observaciones') border-rose-500 @enderror">{{ old('observaciones', $receta->observaciones) }}</textarea>
                        @error('observaciones')
                            <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('recetas.show', $receta) }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-slate-700 dark:text-slate-200 font-semibold rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                    Cancelar
                </a>
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-semibold rounded-lg shadow-sm transition-colors">
                    Guardar Cambios
                </button>
            </div>

        </form>
    </div>

</div>

@endsection
