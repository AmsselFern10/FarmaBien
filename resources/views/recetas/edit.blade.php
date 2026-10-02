@extends('layouts.app')
@section('title', 'Editar Receta Médica - FarmaBien')

@push('scripts')
<script>
function recetaEditForm() {
    return {
        formLayout: localStorage.getItem('farmaFormViewMode') || 'modern',
        setLayout(mode) {
            this.formLayout = mode;
            localStorage.setItem('farmaFormViewMode', mode);
            window.dispatchEvent(new CustomEvent('farma:layout-changed', { detail: { mode } }));
        },
        detalles: {!! $detallesJson !!},
        productos: [],
        clientes: [],
        medicos: [],
        formData: {
            cliente_id: @js(old('cliente_id', $receta->cliente_id ?? '')),
            paciente_nombre: @js(old('paciente_nombre', $receta->paciente_nombre ?? '')),
            paciente_documento: @js(old('paciente_documento', $receta->paciente_documento ?? '')),
            paciente_edad: @js(old('paciente_edad', $receta->paciente_edad ?? '')),
            medico_nombre: @js(old('medico_nombre', $receta->medico_nombre ?? '')),
            medico_colegiatura: @js(old('medico_colegiatura', $receta->medico_colegiatura ?? '')),
            medico_especialidad: @js(old('medico_especialidad', $receta->medico_especialidad ?? '')),
            institucion_salud: @js(old('institucion_salud', $receta->institucion_salud ?? '')),
            numero_receta: @js(old('numero_receta', $receta->numero_receta ?? '')),
            fecha_emision: @js(old('fecha_emision', optional($receta->fecha_emision)->format('Y-m-d') ?? '')),
            fecha_vencimiento: @js(old('fecha_vencimiento', optional($receta->fecha_vencimiento)->format('Y-m-d') ?? '')),
            observaciones: @js(old('observaciones', $receta->observaciones ?? ''))
        },
        showPacienteDropdown: false,
        showMedicoDropdown: false,
        init() {
            this.productos = window._rxProductos || [];
            this.clientes = window._rxClientes || [];
            this.medicos = window._rxMedicos || [];
            if (!this.detalles || this.detalles.length === 0) {
                this.agregarDetalle();
            }
        },
        filtrarPacientes() {
            const q = (this.formData.paciente_nombre || '').toLowerCase().trim();
            if (!q) return this.clientes.slice(0, 8);
            return this.clientes.filter(c => 
                (c.nombre && c.nombre.toLowerCase().includes(q)) || 
                (c.documento && c.documento.toLowerCase().includes(q))
            ).slice(0, 10);
        },
        seleccionarPaciente(c) {
            this.formData.cliente_id = c.id;
            this.formData.paciente_nombre = c.nombre;
            this.formData.paciente_documento = c.documento || '';
            this.showPacienteDropdown = false;
        },
        onClienteSelectChange(val) {
            this.formData.cliente_id = val;
            if (val) {
                const found = this.clientes.find(c => String(c.id) === String(val));
                if (found) {
                    this.formData.paciente_nombre = found.nombre;
                    this.formData.paciente_documento = found.documento || '';
                }
            }
        },
        filtrarMedicos() {
            const q = (this.formData.medico_nombre || '').toLowerCase().trim();
            if (!q) return this.medicos.slice(0, 8);
            return this.medicos.filter(m => 
                (m.medico_nombre && m.medico_nombre.toLowerCase().includes(q)) || 
                (m.medico_colegiatura && m.medico_colegiatura.toLowerCase().includes(q))
            ).slice(0, 10);
        },
        seleccionarMedico(m) {
            this.formData.medico_nombre = m.medico_nombre;
            this.formData.medico_colegiatura = m.medico_colegiatura || '';
            this.formData.medico_especialidad = m.medico_especialidad || '';
            this.formData.institucion_salud = m.institucion_salud || '';
            this.showMedicoDropdown = false;
        },
        agregarDetalle() {
            this.detalles.push({ producto_id: '', producto_nombre: '', cantidad_recetada: 1, posologia: '', query: '', filtrados: [], open: false });
        },
        quitarDetalle(idx) { 
            if (this.detalles.length > 1) {
                this.detalles.splice(idx, 1); 
            }
        },
        filtrar(idx) {
            const q = (this.detalles[idx].query || '').toLowerCase();
            this.detalles[idx].filtrados = q
                ? this.productos.filter(p => p.nombre.toLowerCase().includes(q)).slice(0, 12)
                : this.productos.slice(0, 8);
            this.detalles[idx].open = true;
        },
        seleccionar(idx, p) {
            this.detalles[idx].producto_id = p.id;
            this.detalles[idx].producto_nombre = p.nombre;
            this.detalles[idx].open = false;
        },
        deseleccionar(idx) {
            this.detalles[idx].producto_id = '';
            this.detalles[idx].producto_nombre = '';
            this.detalles[idx].query = '';
            this.detalles[idx].filtrados = [];
        }
    };
}
</script>
@endpush

@section('content')
<script>
window._rxProductos = {!! $productosJson !!};
window._rxClientes = {!! $clientesJson !!};
window._rxMedicos = {!! $medicosJson !!};
</script>

<div
    x-data="recetaEditForm()"
    :class="formLayout === 'compact' ? 'w-full' : 'max-w-5xl mx-auto'"
    class="space-y-4 transition-all duration-200"
>
    {{-- Breadcrumb & Toggle --}}
    {{-- Breadcrumb --}}
    <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400 mb-0.5">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('recetas.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Recetas Médicas</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-800 dark:text-slate-200 font-semibold">Editar Receta #{{ $receta->numero_receta }}</span>
    </nav>

    {{-- Header & Action Toolbar --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-1 border-b border-slate-300/80 dark:border-slate-800">
        <div>
            <h1 class="text-lg sm:text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <span>Editar Receta Médica: {{ $receta->numero_receta }}</span>
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Modifica los datos del paciente, médico prescriptor o medicamentos indicados.</p>
        </div>
        <div class="flex items-center gap-2 shrink-0 flex-wrap">
            <!-- 1. Botón Volver / Precedente (Primero a la izquierda) -->
            <a href="{{ route('recetas.show', $receta) }}" 
               class="inline-flex items-center space-x-1.5 px-3.5 py-2 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold shadow-2xs transition shrink-0">
                <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Receta #{{ $receta->numero_receta }}</span>
            </a>

            <!-- 2. Selector de Diseño (Moderna / Compacta) integrado en la misma barra -->
            <div class="inline-flex items-center p-0.5 rounded-xl bg-slate-200/80 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-xs font-semibold shadow-2xs">
                <button type="button" @click="setLayout('modern')" :class="formLayout==='modern'?'bg-white dark:bg-slate-700 text-emerald-900 dark:text-emerald-400 shadow-xs font-bold':'text-slate-600 hover:text-slate-900 dark:text-slate-400'" class="px-2.5 py-1.5 rounded-lg transition flex items-center space-x-1.5 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    <span>Moderna</span>
                </button>
                <button type="button" @click="setLayout('compact')" :class="formLayout==='compact'?'bg-white dark:bg-slate-700 text-emerald-900 dark:text-emerald-400 shadow-xs font-bold':'text-slate-600 hover:text-slate-900 dark:text-slate-400'" class="px-2.5 py-1.5 rounded-lg transition flex items-center space-x-1.5 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <span>Compacta (POS / ERP)</span>
                </button>
            </div>
        </div>
    </div>

    @if($errors->any())
    <div class="px-4 py-3 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-300 text-xs text-rose-800 dark:text-rose-300">
        <ul class="list-disc list-inside space-y-0.5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <form method="POST" action="{{ route('recetas.update', $receta) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- ===== COMPACTA ===== --}}
        <template x-if="formLayout === 'compact'">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-300 dark:border-slate-800 shadow-md p-4 space-y-4">
                {{-- Toolbar Superior --}}
                <div class="flex items-center justify-between pb-3 border-b border-slate-300 dark:border-slate-800 bg-slate-100/80 dark:bg-slate-800/60 -mx-4 -mt-4 p-3 rounded-t-2xl">
                    <div class="flex items-center space-x-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-500 shadow-xs"></span>
                        <span class="text-xs font-extrabold text-slate-800 dark:text-slate-200 uppercase tracking-wide">EDICIÓN RÁPIDA DE RECETA MÉDICA</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('recetas.index') }}" class="px-3 py-1.5 rounded-lg border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-800 text-xs font-bold transition">Cancelar</a>
                        <button type="submit" class="px-4 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-extrabold shadow-sm transition flex items-center space-x-1.5 cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Guardar Cambios</span>
                        </button>
                    </div>
                </div>

                {{-- Grid 2 cols --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

                    {{-- Panel 1: Paciente y Medico --}}
                    <div class="p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/40 dark:bg-slate-800/30 space-y-3">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center space-x-1.5 border-b border-slate-200/60 dark:border-slate-700/60 pb-1.5">
                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <span>Paciente y Médico Prescriptor</span>
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Cliente Registrado (Opcional)</label>
                            <select name="cliente_id" x-model="formData.cliente_id" @change="onClienteSelectChange($event.target.value)" class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                                <option value="">-- Sin cliente seleccionado --</option>
                                <template x-for="cli in clientes" :key="cli.id">
                                    <option :value="cli.id" :selected="String(cli.id) === String(formData.cliente_id)" x-text="cli.nombre + (cli.documento ? ' (' + cli.documento + ')' : '')"></option>
                                </template>
                            </select>
                        </div>
                        <div class="relative" @click.outside="showPacienteDropdown = false">
                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Nombre del Paciente <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" 
                                   name="paciente_nombre" 
                                   x-model="formData.paciente_nombre" 
                                   @focus="showPacienteDropdown = true" 
                                   @input="showPacienteDropdown = true" 
                                   @keydown.escape="showPacienteDropdown = false" 
                                   required 
                                   placeholder="Escriba el nombre o busque cliente..." 
                                   class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                            <div x-show="showPacienteDropdown && filtrarPacientes().length > 0" 
                                 x-cloak 
                                 class="absolute z-50 w-full mt-0.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-lg max-h-48 overflow-y-auto">
                                <template x-for="c in filtrarPacientes()" :key="c.id">
                                    <button type="button" 
                                            @click="seleccionarPaciente(c)" 
                                            class="w-full text-left px-3 py-2 text-xs hover:bg-emerald-50 dark:hover:bg-slate-700 flex items-center justify-between transition cursor-pointer">
                                        <span class="font-medium text-slate-900 dark:text-white truncate" x-text="c.nombre"></span>
                                        <span class="text-[10px] text-slate-400 font-mono shrink-0 ml-2" x-text="c.documento ? 'Doc: ' + c.documento : 'Cliente'"></span>
                                    </button>
                                </template>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">DNI / Documento</label>
                                <input type="text" name="paciente_documento" x-model="formData.paciente_documento" placeholder="DNI..." class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Edad</label>
                                <input type="number" name="paciente_edad" x-model="formData.paciente_edad" min="0" max="130" placeholder="Años" class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                            </div>
                        </div>
                        <div class="border-t border-slate-200/60 dark:border-slate-700/60 pt-2 space-y-2">
                            <div class="text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400">Médico Prescriptor (Sugerencias al escribir)</div>
                            <div class="relative" @click.outside="showMedicoDropdown = false">
                                <input type="text" 
                                       name="medico_nombre" 
                                       x-model="formData.medico_nombre" 
                                       @focus="showMedicoDropdown = true" 
                                       @input="showMedicoDropdown = true" 
                                       @keydown.escape="showMedicoDropdown = false" 
                                       required 
                                       placeholder="Dr. Nombre Completo..." 
                                       class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                                <div x-show="showMedicoDropdown && filtrarMedicos().length > 0" 
                                     x-cloak 
                                     class="absolute z-50 w-full mt-0.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-lg max-h-48 overflow-y-auto">
                                    <template x-for="m in filtrarMedicos()" :key="m.medico_nombre">
                                        <button type="button" 
                                                @click="seleccionarMedico(m)" 
                                                class="w-full text-left px-3 py-2 text-xs hover:bg-emerald-50 dark:hover:bg-slate-700 flex items-center justify-between transition cursor-pointer">
                                            <div>
                                                <span class="font-medium text-slate-900 dark:text-white block" x-text="m.medico_nombre"></span>
                                                <span class="text-[10px] text-slate-400" x-text="m.medico_especialidad || 'Médico'"></span>
                                            </div>
                                            <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-mono shrink-0 ml-2" x-text="m.medico_colegiatura"></span>
                                        </button>
                                    </template>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <input type="text" name="medico_colegiatura" x-model="formData.medico_colegiatura" required placeholder="Colegiatura / CMP" class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                                <input type="text" name="medico_especialidad" x-model="formData.medico_especialidad" placeholder="Especialidad..." class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                            </div>
                            <input type="text" name="institucion_salud" x-model="formData.institucion_salud" placeholder="Hospital / Clínica..." class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                        </div>
                    </div>

                    {{-- Panel 2: Receta y Medicamentos --}}
                    <div class="p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/40 dark:bg-slate-800/30 space-y-3">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center space-x-1.5 border-b border-slate-200/60 dark:border-slate-700/60 pb-1.5">
                            <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span>Datos de la Receta y Medicamentos</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">N° Receta <span class="text-rose-500">*</span></label>
                                <input type="text" name="numero_receta" x-model="formData.numero_receta" required placeholder="RX-001..." class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Fecha Emisión <span class="text-rose-500">*</span></label>
                                <input type="date" name="fecha_emision" x-model="formData.fecha_emision" required class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Vencimiento</label>
                                <input type="date" name="fecha_vencimiento" x-model="formData.fecha_vencimiento" class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                            </div>
                        </div>

                        {{-- Archivo Adjunto Actual / Reemplazo --}}
                        <div class="border-t border-slate-200/60 dark:border-slate-700/60 pt-2 space-y-2">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Archivo / Foto de Receta</label>
                                @if($receta->archivo_receta)
                                    <div class="flex items-center justify-between p-2 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-xs text-emerald-800 dark:text-emerald-300 mb-1.5">
                                        <span class="truncate">Archivo adjunto guardado</span>
                                        <a href="{{ route('recetas.archivo', $receta) }}" target="_blank" class="font-bold underline text-[11px] ml-2 shrink-0">Ver / Descargar</a>
                                    </div>
                                @endif
                                <input type="file" name="archivo_receta" accept=".pdf,.jpg,.jpeg,.png,image/*" class="w-full text-xs text-slate-500 file:mr-2 file:py-1 file:px-2.5 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 dark:file:bg-emerald-950 dark:file:text-emerald-300 cursor-pointer">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Observaciones / Diagnóstico</label>
                                <input type="text" name="observaciones" x-model="formData.observaciones" placeholder="Indicaciones adicionales..." class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                            </div>
                        </div>

                        {{-- Medicamentos list --}}
                        <div class="border-t border-slate-200/60 dark:border-slate-700/60 pt-2 space-y-2">
                            <div class="flex items-center justify-between">
                                <div class="text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400">Medicamentos Prescritos</div>
                                <button type="button" @click="agregarDetalle()" class="px-2.5 py-1 text-[10px] font-bold rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white transition">+ Agregar</button>
                            </div>
                            <template x-for="(det, idx) in detalles" :key="idx">
                                <div class="p-2 bg-white dark:bg-slate-900 rounded-lg border border-slate-200 dark:border-slate-700 space-y-1.5">
                                    <input type="hidden" :name="'detalles['+idx+'][producto_id]'" :value="det.producto_id">
                                    {{-- Combobox state: selected --}}
                                    <div x-show="det.producto_id" class="flex items-center justify-between px-2.5 py-1.5 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 rounded-lg">
                                        <span class="text-[11px] font-semibold text-emerald-700 dark:text-emerald-300 truncate" x-text="det.producto_nombre"></span>
                                        <button type="button" @click="deseleccionar(idx)" class="text-slate-400 hover:text-rose-500 ml-1.5 shrink-0">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                    {{-- Combobox state: searching --}}
                                    <div x-show="!det.producto_id" class="relative">
                                        <input type="text" x-model="det.query" @input="filtrar(idx)" @focus="filtrar(idx)" @keydown.escape="det.open=false" placeholder="Buscar medicamento..." class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-[11px] text-slate-900 dark:text-white placeholder-slate-400 focus:ring-1 focus:ring-emerald-500">
                                        <div x-show="det.open" @click.outside="det.open=false" class="absolute z-50 w-full mt-0.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-lg max-h-40 overflow-y-auto">
                                            <template x-for="p in det.filtrados" :key="p.id">
                                                <button type="button" @click="seleccionar(idx, p)" class="w-full text-left px-3 py-2 text-[11px] text-slate-700 dark:text-slate-300 hover:bg-emerald-50 dark:hover:bg-emerald-950/30 hover:text-emerald-700 transition" x-text="p.nombre"></button>
                                            </template>
                                            <div x-show="det.filtrados.length===0" class="px-3 py-2 text-[11px] text-slate-400 text-center">Sin resultados</div>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-3 gap-1.5">
                                        <div>
                                            <label class="block text-[10px] text-slate-400 mb-0.5">Cant. <span class="text-rose-500">*</span></label>
                                            <input type="number" :name="'detalles['+idx+'][cantidad_recetada]'" x-model="det.cantidad_recetada" min="1" required class="w-full px-2 py-1 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-md text-[11px] text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                                        </div>
                                        <div class="col-span-2">
                                            <label class="block text-[10px] text-slate-400 mb-0.5">Posología</label>
                                            <input type="text" :name="'detalles['+idx+'][posologia]'" x-model="det.posologia" placeholder="1 tab c/8h..." class="w-full px-2 py-1 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-md text-[11px] text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                                        </div>
                                    </div>
                                    <div class="flex justify-end">
                                        <button type="button" @click="quitarDetalle(idx)" :disabled="detalles.length<=1" class="px-2 py-0.5 text-[10px] font-semibold rounded text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition disabled:opacity-30">Quitar</button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Footer Compacto --}}
                <div class="pt-2 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between text-xs text-slate-500">
                    <span class="text-[11px]">Los cambios actualizan los datos de la receta médica en el sistema.</span>
                    <div class="flex items-center gap-3">
                        <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs transition flex items-center space-x-1.5 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Guardar Cambios</span>
                        </button>
                    </div>
                </div>
            </div>
        </template>

        {{-- ===== MODERNA ===== --}}
        <template x-if="formLayout === 'modern'">
            <div class="space-y-6">
                {{-- Paciente --}}
                <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm space-y-5">
                    <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center space-x-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span>Datos del Paciente</span>
                        </h3>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Cliente Registrado <span class="text-slate-400 font-normal">(Opcional)</span></label>
                            <select name="cliente_id" x-model="formData.cliente_id" @change="onClienteSelectChange($event.target.value)" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition">
                                <option value="">-- Sin cliente registrado --</option>
                                <template x-for="cli in clientes" :key="cli.id">
                                    <option :value="cli.id" :selected="String(cli.id) === String(formData.cliente_id)" x-text="cli.nombre + (cli.documento ? ' (' + cli.documento + ')' : '')"></option>
                                </template>
                            </select>
                        </div>
                        <div class="relative" @click.outside="showPacienteDropdown = false">
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Nombre del Paciente <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" 
                                   name="paciente_nombre" 
                                   x-model="formData.paciente_nombre" 
                                   @focus="showPacienteDropdown = true" 
                                   @input="showPacienteDropdown = true" 
                                   @keydown.escape="showPacienteDropdown = false" 
                                   required 
                                   placeholder="Escriba el nombre o busque en clientes..." 
                                   class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition @error('paciente_nombre') border-rose-500 @enderror">
                            <div x-show="showPacienteDropdown && filtrarPacientes().length > 0" 
                                 x-cloak 
                                 class="absolute z-50 w-full mt-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-xl max-h-56 overflow-y-auto">
                                <template x-for="c in filtrarPacientes()" :key="c.id">
                                    <button type="button" 
                                            @click="seleccionarPaciente(c)" 
                                            class="w-full text-left px-4 py-2.5 text-sm hover:bg-emerald-50 dark:hover:bg-slate-700 flex items-center justify-between transition cursor-pointer">
                                        <span class="font-medium text-slate-900 dark:text-white" x-text="c.nombre"></span>
                                        <span class="text-xs text-slate-400 font-mono" x-text="c.documento ? 'Doc: ' + c.documento : 'Cliente'"></span>
                                    </button>
                                </template>
                            </div>
                            @error('paciente_nombre')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">N° Documento / DNI</label>
                            <input type="text" name="paciente_documento" x-model="formData.paciente_documento" placeholder="DNI, Pasaporte..." class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Edad</label>
                            <input type="number" name="paciente_edad" x-model="formData.paciente_edad" min="0" max="130" placeholder="Años" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition">
                        </div>
                    </div>
                </div>

                {{-- Medico --}}
                <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm space-y-5">
                    <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center space-x-2">
                            <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                            <span>Médico Prescriptor (Sugerencias al escribir)</span>
                        </h3>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="relative" @click.outside="showMedicoDropdown = false">
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Nombre del Médico <span class="text-rose-500">*</span></label>
                            <input type="text" 
                                   name="medico_nombre" 
                                   x-model="formData.medico_nombre" 
                                   @focus="showMedicoDropdown = true" 
                                   @input="showMedicoDropdown = true" 
                                   @keydown.escape="showMedicoDropdown = false" 
                                   required 
                                   placeholder="Dr. Juan Pérez López..." 
                                   class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition @error('medico_nombre') border-rose-500 @enderror">
                            <div x-show="showMedicoDropdown && filtrarMedicos().length > 0" 
                                 x-cloak 
                                 class="absolute z-50 w-full mt-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-xl max-h-56 overflow-y-auto">
                                <template x-for="m in filtrarMedicos()" :key="m.medico_nombre">
                                    <button type="button" 
                                            @click="seleccionarMedico(m)" 
                                            class="w-full text-left px-4 py-2.5 text-sm hover:bg-emerald-50 dark:hover:bg-slate-700 flex items-center justify-between transition cursor-pointer">
                                        <div>
                                            <span class="font-medium text-slate-900 dark:text-white block" x-text="m.medico_nombre"></span>
                                            <span class="text-xs text-slate-400" x-text="m.medico_especialidad || 'Médico'"></span>
                                        </div>
                                        <span class="text-xs text-emerald-600 dark:text-emerald-400 font-mono shrink-0 ml-2" x-text="m.medico_colegiatura"></span>
                                    </button>
                                </template>
                            </div>
                            @error('medico_nombre')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">N° Colegiatura / Registro <span class="text-rose-500">*</span></label>
                            <input type="text" name="medico_colegiatura" x-model="formData.medico_colegiatura" required placeholder="CMP-12345..." class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition @error('medico_colegiatura') border-rose-500 @enderror">
                            @error('medico_colegiatura')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Especialidad</label>
                            <input type="text" name="medico_especialidad" x-model="formData.medico_especialidad" placeholder="Medicina General, Pediatría..." class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Institución / Clínica / Hospital</label>
                            <input type="text" name="institucion_salud" x-model="formData.institucion_salud" placeholder="Hospital Nacional..." class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition">
                        </div>
                    </div>
                </div>

                {{-- Receta --}}
                <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm space-y-5">
                    <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center space-x-2">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            <span>Datos de la Receta</span>
                        </h3>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">N° de Receta <span class="text-rose-500">*</span></label>
                            <input type="text" name="numero_receta" x-model="formData.numero_receta" required placeholder="RX-2024-001..." class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition @error('numero_receta') border-rose-500 @enderror">
                            @error('numero_receta')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Fecha de Emisión <span class="text-rose-500">*</span></label>
                            <input type="date" name="fecha_emision" x-model="formData.fecha_emision" required class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition @error('fecha_emision') border-rose-500 @enderror">
                            @error('fecha_emision')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Fecha de Vencimiento <span class="text-slate-400 font-normal">(Opcional)</span></label>
                            <input type="date" name="fecha_vencimiento" x-model="formData.fecha_vencimiento" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Archivo / Foto de la Receta (Opcional)</label>
                            @if($receta->archivo_receta)
                                <div class="flex items-center justify-between p-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-sm text-emerald-800 dark:text-emerald-300 mb-2">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        <span class="font-medium">Existe un archivo de receta previamente adjuntado.</span>
                                    </div>
                                    <a href="{{ route('recetas.archivo', $receta) }}" target="_blank" class="font-bold underline text-xs ml-3 shrink-0">Ver / Descargar</a>
                                </div>
                            @endif
                            <input type="file" name="archivo_receta" accept="image/*,application/pdf" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 dark:file:bg-emerald-950 dark:file:text-emerald-300">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Observaciones / Diagnóstico</label>
                            <textarea name="observaciones" x-model="formData.observaciones" rows="2" placeholder="Diagnóstico, indicaciones especiales, alergias..." class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition"></textarea>
                        </div>
                    </div>
                </div>

                {{-- Medicamentos Prescritos --}}
                <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm space-y-5">
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center space-x-2">
                            <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                            <span>Medicamentos Prescritos</span>
                        </h3>
                        <button type="button" @click="agregarDetalle()" class="inline-flex items-center space-x-1 px-3 py-1.5 bg-emerald-50 dark:bg-emerald-950/60 hover:bg-emerald-100 text-emerald-700 dark:text-emerald-300 text-xs font-semibold rounded-lg transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span>Agregar Medicamento</span>
                        </button>
                    </div>
                    <div class="space-y-4">
                        <template x-for="(det, idx) in detalles" :key="idx">
                            <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-700/60 space-y-3">
                                <input type="hidden" :name="'detalles[' + idx + '][producto_id]'" :value="det.producto_id">
                                <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                                    {{-- Selector / Buscador del Producto --}}
                                    <div class="md:col-span-6 relative">
                                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                            Medicamento con Rx <span class="text-rose-500">*</span>
                                        </label>
                                        <div x-show="det.producto_id" class="flex items-center justify-between px-3.5 py-2.5 bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 rounded-xl">
                                            <span class="text-sm font-medium text-emerald-800 dark:text-emerald-200" x-text="det.producto_nombre"></span>
                                            <button type="button" @click="deseleccionar(idx)" class="text-slate-400 hover:text-rose-500 transition ml-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </div>
                                        <div x-show="!det.producto_id" class="relative">
                                            <input type="text" 
                                                   x-model="det.query" 
                                                   @input="filtrar(idx)" 
                                                   @focus="filtrar(idx)" 
                                                   @keydown.escape="det.open = false" 
                                                   placeholder="Escriba para buscar medicamento..." 
                                                   class="w-full px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition">
                                            <div x-show="det.open" 
                                                 @click.outside="det.open = false" 
                                                 class="absolute z-50 w-full mt-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-xl max-h-48 overflow-y-auto">
                                                <template x-for="p in det.filtrados" :key="p.id">
                                                    <button type="button" 
                                                            @click="seleccionar(idx, p)" 
                                                            class="w-full text-left px-4 py-2.5 text-sm hover:bg-emerald-50 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 transition cursor-pointer" 
                                                            x-text="p.nombre"></button>
                                                </template>
                                                <div x-show="det.filtrados.length === 0" class="px-4 py-3 text-xs text-slate-400 text-center">
                                                    No se encontraron medicamentos con receta.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    {{-- Cantidad --}}
                                    <div class="md:col-span-2">
                                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Cantidad <span class="text-rose-500">*</span></label>
                                        <input type="number" 
                                               :name="'detalles[' + idx + '][cantidad_recetada]'" 
                                               x-model="det.cantidad_recetada" 
                                               min="1" 
                                               required 
                                               class="w-full px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition">
                                    </div>
                                    {{-- Posología --}}
                                    <div class="md:col-span-3">
                                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Posología / Indicación</label>
                                        <input type="text" 
                                               :name="'detalles[' + idx + '][posologia]'" 
                                               x-model="det.posologia" 
                                               placeholder="Ej: 1 tableta cada 8 hrs por 7 días" 
                                               class="w-full px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition">
                                    </div>
                                    {{-- Eliminar Fila --}}
                                    <div class="md:col-span-1 flex justify-end">
                                        <button type="button" 
                                                @click="quitarDetalle(idx)" 
                                                :disabled="detalles.length <= 1" 
                                                class="p-2.5 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/50 rounded-xl transition disabled:opacity-30 disabled:cursor-not-allowed" 
                                                title="Eliminar fila">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Botones de Acción --}}
                <div class="flex items-center justify-between pt-2">
                    <a href="{{ route('recetas.show', $receta) }}" class="px-5 py-2.5 border border-slate-300 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm font-semibold rounded-xl transition">
                        Cancelar
                    </a>
                    <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-sm font-semibold rounded-xl shadow-xs transition flex items-center space-x-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Guardar Cambios</span>
                    </button>
                </div>
            </div>
        </template>
    </form>
</div>
@endsection
