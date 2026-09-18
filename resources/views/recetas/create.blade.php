@extends('layouts.app')

@section('title', 'Nueva Receta Médica - FarmaBien')

@push('scripts')
<script>
function recetaFormData() {
    return {
        detalles: [],
        productosBuscados: @json($productos->map(fn($p) => ['id' => $p->id, 'nombre' => $p->nombre])->values()),
        queryPorIdx: {},
        filtradosPorIdx: {},
        dropdownPorIdx: {},

        init() { this.agregarDetalle(); },

        filtrarProductos(idx) {
            const q = (this.queryPorIdx[idx] || '').toLowerCase();
            if (q.length < 1) { this.filtradosPorIdx[idx] = []; this.dropdownPorIdx[idx] = false; return; }
            this.filtradosPorIdx[idx] = this.productosBuscados.filter(p => p.nombre.toLowerCase().includes(q)).slice(0, 10);
            this.dropdownPorIdx[idx] = this.filtradosPorIdx[idx].length > 0;
        },

        seleccionarProducto(prod, idx) {
            this.detalles[idx].producto_id = prod.id;
            this.detalles[idx].producto_nombre = prod.nombre;
            this.dropdownPorIdx[idx] = false;
            this.queryPorIdx[idx] = '';
        },

        agregarDetalle() {
            const idx = this.detalles.length;
            this.detalles.push({ producto_id: '', producto_nombre: '', cantidad_recetada: 1, posologia: '' });
            this.queryPorIdx[idx] = '';
            this.filtradosPorIdx[idx] = [];
            this.dropdownPorIdx[idx] = false;
        },

        quitarDetalle(idx) { if (this.detalles.length > 1) this.detalles.splice(idx, 1); }
    };
}
</script>
@endpush

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <!-- Breadcrumb & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('recetas.index') }}" class="hover:text-emerald-600 transition">Recetas Médicas</a>
                <span>/</span>
                <span class="text-slate-800 dark:text-slate-300 font-medium">Nueva</span>
            </div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">Registrar Receta Médica</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Completa los datos del paciente, médico prescriptor y los medicamentos recetados.</p>
        </div>
        <a href="{{ route('recetas.index') }}"
           class="inline-flex items-center space-x-1.5 px-3.5 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-xl transition shrink-0">
            <span>&larr;</span><span>Volver a la lista</span>
        </a>
    </div>

    <form method="POST" action="{{ route('recetas.store') }}" enctype="multipart/form-data" class="space-y-6" x-data="recetaFormData()">
        @csrf

        @if($errors->any())
        <div class="px-4 py-3 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-300 dark:border-rose-700 text-xs text-rose-800 dark:text-rose-300">
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
        @endif

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
                    <select id="cliente_id" name="cliente_id"
                            class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition">
                        <option value="">-- Sin cliente registrado --</option>
                        @foreach($clientes as $cli)
                        <option value="{{ $cli->id }}" {{ old('cliente_id') == $cli->id ? 'selected' : '' }}>{{ $cli->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="paciente_nombre" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Nombre del Paciente <span class="text-rose-500">*</span></label>
                    <input type="text" id="paciente_nombre" name="paciente_nombre" value="{{ old('paciente_nombre') }}" required
                           placeholder="Nombre completo del paciente..."
                           class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition @error('paciente_nombre') border-rose-500 @enderror">
                    @error('paciente_nombre') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="paciente_documento" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">N° Documento / DNI</label>
                    <input type="text" id="paciente_documento" name="paciente_documento" value="{{ old('paciente_documento') }}" placeholder="DNI, Pasaporte..."
                           class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition">
                </div>
                <div>
                    <label for="paciente_edad" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Edad</label>
                    <input type="number" id="paciente_edad" name="paciente_edad" value="{{ old('paciente_edad') }}" min="0" max="130" placeholder="Años"
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
                    <input type="text" id="medico_nombre" name="medico_nombre" value="{{ old('medico_nombre') }}" required placeholder="Dr. Juan Pérez..."
                           class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition @error('medico_nombre') border-rose-500 @enderror">
                    @error('medico_nombre') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="medico_colegiatura" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">N° Colegiatura / CMP <span class="text-rose-500">*</span></label>
                    <input type="text" id="medico_colegiatura" name="medico_colegiatura" value="{{ old('medico_colegiatura') }}" required placeholder="CMP-12345..."
                           class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition @error('medico_colegiatura') border-rose-500 @enderror">
                    @error('medico_colegiatura') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="medico_especialidad" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Especialidad</label>
                    <input type="text" id="medico_especialidad" name="medico_especialidad" value="{{ old('medico_especialidad') }}" placeholder="Cardiología, Medicina General..."
                           class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition">
                </div>
                <div>
                    <label for="institucion_salud" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Institución de Salud</label>
                    <input type="text" id="institucion_salud" name="institucion_salud" value="{{ old('institucion_salud') }}" placeholder="Hospital, Clínica..."
                           class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition">
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
                    <input type="text" id="numero_receta" name="numero_receta" value="{{ old('numero_receta') }}" required placeholder="RX-2024-001..."
                           class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition @error('numero_receta') border-rose-500 @enderror">
                    @error('numero_receta') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="tipo_receta" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Tipo de Receta <span class="text-rose-500">*</span></label>
                    <select id="tipo_receta" name="tipo_receta" required
                            class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition">
                        <option value="simple" {{ old('tipo_receta','simple') === 'simple' ? 'selected' : '' }}>Simple</option>
                        <option value="retenida" {{ old('tipo_receta') === 'retenida' ? 'selected' : '' }}>Retenida (queda en farmacia)</option>
                    </select>
                </div>
                <div>
                    <label for="fecha_emision" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Fecha de Emisión <span class="text-rose-500">*</span></label>
                    <input type="date" id="fecha_emision" name="fecha_emision" value="{{ old('fecha_emision', now()->toDateString()) }}" required
                           class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition @error('fecha_emision') border-rose-500 @enderror">
                    @error('fecha_emision') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="fecha_vencimiento" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Fecha de Vencimiento</label>
                    <input type="date" id="fecha_vencimiento" name="fecha_vencimiento" value="{{ old('fecha_vencimiento') }}"
                           class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition">
                </div>
                <div class="md:col-span-2">
                    <label for="archivo_receta" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Adjunto (PDF o Imagen)</label>
                    <input type="file" id="archivo_receta" name="archivo_receta" accept=".pdf,.jpg,.jpeg,.png"
                           class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-600 file:text-white hover:file:bg-emerald-700 cursor-pointer">
                    <p class="text-xs text-slate-400 mt-1">Máx. 5 MB. Formatos: PDF, JPG, PNG.</p>
                </div>
                <div class="md:col-span-2">
                    <label for="observaciones" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Observaciones</label>
                    <textarea id="observaciones" name="observaciones" rows="2" placeholder="Notas adicionales sobre la receta..."
                              class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition">{{ old('observaciones') }}</textarea>
                </div>
            </div>
        </div>

        <!-- Card: Medicamentos -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
            <div class="border-b border-slate-100 dark:border-slate-800 pb-3 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center space-x-2">
                        <span class="w-2 h-2 rounded-full bg-rose-500"></span><span>Medicamentos Prescritos</span>
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">Mínimo 1 medicamento requerido.</p>
                </div>
                <button type="button" @click="agregarDetalle()"
                        class="inline-flex items-center space-x-1.5 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Agregar Medicamento</span>
                </button>
            </div>

            @error('detalles') <p class="text-rose-500 text-xs">{{ $message }}</p> @enderror

            <template x-for="(det, idx) in detalles" :key="idx">
                <div class="flex flex-col md:flex-row gap-3 p-4 bg-slate-50 dark:bg-slate-800/40 rounded-xl border border-slate-200 dark:border-slate-700">
                    <input type="hidden" :name="'detalles[' + idx + '][producto_id]'" :value="det.producto_id">
                    <!-- Medicamento búsqueda -->
                    <div class="flex-1 min-w-0">
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Medicamento <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <!-- Seleccionado -->
                            <div x-show="det.producto_id" class="flex items-center justify-between px-3 py-2 bg-white dark:bg-slate-900 border border-emerald-300 dark:border-emerald-700 rounded-xl">
                                <span class="text-xs font-semibold text-slate-800 dark:text-slate-200" x-text="det.producto_nombre"></span>
                                <button type="button" @click="det.producto_id = ''; det.producto_nombre = ''" class="text-slate-400 hover:text-rose-500 ml-2">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                            <!-- Búsqueda -->
                            <div x-show="!det.producto_id" class="relative">
                                <input type="text" :value="queryPorIdx[idx]" @input="queryPorIdx[idx] = $event.target.value; filtrarProductos(idx)"
                                       @focus="filtrarProductos(idx)"
                                       placeholder="Buscar medicamento..."
                                       class="w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500">
                                <div x-show="dropdownPorIdx[idx]" class="absolute z-50 w-full mt-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-lg max-h-40 overflow-y-auto">
                                    <template x-for="prod in (filtradosPorIdx[idx] || [])" :key="prod.id">
                                        <button type="button" @click="seleccionarProducto(prod, idx)"
                                                class="w-full text-left px-3 py-2 text-xs text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition" x-text="prod.nombre"></button>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Cantidad -->
                    <div class="w-full md:w-28 shrink-0">
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Cantidad <span class="text-rose-500">*</span></label>
                        <input type="number" :name="'detalles[' + idx + '][cantidad_recetada]'" x-model="det.cantidad_recetada" min="1" required
                               class="w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500">
                    </div>
                    <!-- Posología -->
                    <div class="flex-1 min-w-0">
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Posología / Indicaciones</label>
                        <input type="text" :name="'detalles[' + idx + '][posologia]'" x-model="det.posologia"
                               placeholder="1 comp. cada 8h por 7 días..."
                               class="w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500">
                    </div>
                    <!-- Quitar -->
                    <div class="flex items-end shrink-0">
                        <button type="button" @click="quitarDetalle(idx)" :disabled="detalles.length <= 1"
                                class="p-2 rounded-xl text-slate-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition disabled:opacity-30 disabled:cursor-not-allowed">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </div>
            </template>
        </div>

        <!-- Sticky Footer -->
        <div class="sticky bottom-0 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-3.5 bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border-t border-slate-200 dark:border-slate-800 shadow-lg z-20 flex items-center justify-between transition-all rounded-b-2xl">
            <a href="{{ route('recetas.index') }}"
               class="px-5 py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-xl transition">Cancelar</a>
            <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-sm font-semibold rounded-xl shadow-sm transition inline-flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>Registrar Receta</span>
            </button>
        </div>
    </form>
</div>
@endsection
