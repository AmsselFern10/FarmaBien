@extends('layouts.app')

@section('title', 'Editar Receta Médica - FarmaBien')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <!-- Breadcrumb & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('recetas.index') }}" class="hover:text-emerald-600 transition">Recetas Médicas</a>
                <span>/</span>
                <span class="text-slate-800 dark:text-slate-300 font-medium">Editar</span>
            </div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">Editar Receta: {{ $receta->numero_receta }}</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Estado actual:
                @php
                    $estadoColor = ['pendiente'=>'text-amber-600','dispensada_parcial'=>'text-blue-600','dispensada_total'=>'text-emerald-600','anulada'=>'text-rose-600'];
                    $estadoLabel = ['pendiente'=>'Pendiente','dispensada_parcial'=>'Dispensada Parcial','dispensada_total'=>'Dispensada Total','anulada'=>'Anulada'];
                @endphp
                <span class="font-bold {{ $estadoColor[$receta->estado] ?? 'text-slate-600' }}">{{ $estadoLabel[$receta->estado] ?? $receta->estado }}</span>
            </p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ route('recetas.show', $receta) }}" class="px-3.5 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-xl transition">Ver Ficha</a>
            <a href="{{ route('recetas.index') }}" class="px-3.5 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-xl transition">&larr; Volver</a>
        </div>
    </div>

    @if($errors->any())
    <div class="px-4 py-3 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-300 dark:border-rose-700 text-xs text-rose-800 dark:text-rose-300">
        <ul class="list-disc list-inside space-y-0.5">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('recetas.update', $receta) }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Card: Paciente -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm space-y-5">
            <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center space-x-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span><span>Datos del Paciente</span>
                </h3>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="md:col-span-2">
                    <label for="cliente_id" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Cliente Registrado <span class="text-slate-400 font-normal">(Opcional)</span></label>
                    <select id="cliente_id" name="cliente_id" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition">
                        <option value="">-- Sin cliente registrado --</option>
                        @foreach($clientes as $cli)
                        <option value="{{ $cli->id }}" {{ old('cliente_id', $receta->cliente_id) == $cli->id ? 'selected' : '' }}>{{ $cli->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="paciente_nombre" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Nombre del Paciente <span class="text-rose-500">*</span></label>
                    <input type="text" id="paciente_nombre" name="paciente_nombre" value="{{ old('paciente_nombre', $receta->paciente_nombre) }}" required
                           class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition @error('paciente_nombre') border-rose-500 @enderror">
                    @error('paciente_nombre') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="paciente_documento" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">N° Documento</label>
                    <input type="text" id="paciente_documento" name="paciente_documento" value="{{ old('paciente_documento', $receta->paciente_documento) }}"
                           class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition">
                </div>
                <div>
                    <label for="paciente_edad" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Edad</label>
                    <input type="number" id="paciente_edad" name="paciente_edad" value="{{ old('paciente_edad', $receta->paciente_edad) }}" min="0" max="130"
                           class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition">
                </div>
            </div>
        </div>

        <!-- Card: Médico -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm space-y-5">
            <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center space-x-2">
                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span><span>Médico Prescriptor</span>
                </h3>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label for="medico_nombre" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Nombre del Médico <span class="text-rose-500">*</span></label>
                    <input type="text" id="medico_nombre" name="medico_nombre" value="{{ old('medico_nombre', $receta->medico_nombre) }}" required
                           class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition @error('medico_nombre') border-rose-500 @enderror">
                    @error('medico_nombre') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="medico_colegiatura" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">N° Colegiatura / CMP <span class="text-rose-500">*</span></label>
                    <input type="text" id="medico_colegiatura" name="medico_colegiatura" value="{{ old('medico_colegiatura', $receta->medico_colegiatura) }}" required
                           class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition @error('medico_colegiatura') border-rose-500 @enderror">
                    @error('medico_colegiatura') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="medico_especialidad" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Especialidad</label>
                    <input type="text" id="medico_especialidad" name="medico_especialidad" value="{{ old('medico_especialidad', $receta->medico_especialidad) }}"
                           class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition">
                </div>
                <div>
                    <label for="institucion_salud" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Institución de Salud</label>
                    <input type="text" id="institucion_salud" name="institucion_salud" value="{{ old('institucion_salud', $receta->institucion_salud) }}"
                           class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition">
                </div>
            </div>
        </div>

        <!-- Card: Datos de Receta -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm space-y-5">
            <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center space-x-2">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span><span>Datos de la Receta</span>
                </h3>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label for="numero_receta" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">N° de Receta <span class="text-rose-500">*</span></label>
                    <input type="text" id="numero_receta" name="numero_receta" value="{{ old('numero_receta', $receta->numero_receta) }}" required
                           class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition @error('numero_receta') border-rose-500 @enderror">
                    @error('numero_receta') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="tipo_receta" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Tipo de Receta <span class="text-rose-500">*</span></label>
                    <select id="tipo_receta" name="tipo_receta" required class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition">
                        <option value="simple" {{ old('tipo_receta', $receta->tipo_receta) === 'simple' ? 'selected' : '' }}>Simple</option>
                        <option value="retenida" {{ old('tipo_receta', $receta->tipo_receta) === 'retenida' ? 'selected' : '' }}>Retenida</option>
                    </select>
                </div>
                <div>
                    <label for="fecha_emision" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Fecha de Emisión <span class="text-rose-500">*</span></label>
                    <input type="date" id="fecha_emision" name="fecha_emision" value="{{ old('fecha_emision', $receta->fecha_emision?->toDateString()) }}" required
                           class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition">
                </div>
                <div>
                    <label for="fecha_vencimiento" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Fecha de Vencimiento</label>
                    <input type="date" id="fecha_vencimiento" name="fecha_vencimiento" value="{{ old('fecha_vencimiento', $receta->fecha_vencimiento?->toDateString()) }}"
                           class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition">
                </div>
                <div class="md:col-span-2">
                    <label for="archivo_receta" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Adjunto (reemplaza el actual)</label>
                    @if($receta->archivo_receta)
                    <p class="text-xs text-emerald-600 dark:text-emerald-400 mb-1.5">
                        Archivo actual: <a href="{{ Storage::url($receta->archivo_receta) }}" target="_blank" class="underline">ver adjunto</a>
                    </p>
                    @endif
                    <input type="file" id="archivo_receta" name="archivo_receta" accept=".pdf,.jpg,.jpeg,.png"
                           class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-600 file:text-white hover:file:bg-emerald-700 cursor-pointer">
                </div>
                <div class="md:col-span-2">
                    <label for="observaciones" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Observaciones</label>
                    <textarea id="observaciones" name="observaciones" rows="2"
                              class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition">{{ old('observaciones', $receta->observaciones) }}</textarea>
                </div>
            </div>
        </div>

        <!-- Card: Medicamentos prescritos (solo lectura si ya tiene dispensaciones) -->
        @if($receta->detalles->isNotEmpty())
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="border-b border-slate-100 dark:border-slate-800 pb-3 mb-4">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center space-x-2">
                    <span class="w-2 h-2 rounded-full bg-rose-500"></span><span>Medicamentos Prescritos</span>
                </h3>
                <p class="text-xs text-amber-600 dark:text-amber-400 mt-0.5">
                    Los medicamentos no se pueden modificar una vez registrados.
                    <a href="{{ route('recetas.show', $receta) }}" class="underline ml-1">Ver dispensaciones &rarr;</a>
                </p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                        <tr>
                            <th class="px-4 py-2.5 text-left font-semibold">Medicamento</th>
                            <th class="px-4 py-2.5 text-center font-semibold">Recetado</th>
                            <th class="px-4 py-2.5 text-center font-semibold">Dispensado</th>
                            <th class="px-4 py-2.5 text-center font-semibold">Pendiente</th>
                            <th class="px-4 py-2.5 text-left font-semibold">Posología</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                        @foreach($receta->detalles as $det)
                        <tr>
                            <td class="px-4 py-3 font-semibold text-slate-900 dark:text-white">{{ $det->producto->nombre ?? 'N/A' }}</td>
                            <td class="px-4 py-3 text-center">{{ $det->cantidad_recetada }}</td>
                            <td class="px-4 py-3 text-center text-emerald-600 dark:text-emerald-400 font-semibold">{{ $det->cantidad_dispensada }}</td>
                            <td class="px-4 py-3 text-center {{ $det->pendiente_dispensar > 0 ? 'text-amber-600 dark:text-amber-400 font-bold' : 'text-slate-400' }}">
                                {{ $det->pendiente_dispensar }}
                            </td>
                            <td class="px-4 py-3 text-slate-500">{{ $det->posologia ?? '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- Sticky Footer -->
        <div class="sticky bottom-0 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-3.5 bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border-t border-slate-200 dark:border-slate-800 shadow-lg z-20 flex items-center justify-between transition-all rounded-b-2xl">
            <a href="{{ route('recetas.index') }}" class="px-5 py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-xl transition">Cancelar</a>
            <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-sm font-semibold rounded-xl shadow-sm transition inline-flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>Actualizar Receta</span>
            </button>
        </div>
    </form>
</div>
@endsection
