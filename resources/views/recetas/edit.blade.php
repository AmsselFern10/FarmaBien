@extends('layouts.app')
@section('title', 'Editar Receta Medica - FarmaBien')
@push('scripts')
<script>
function recetaFormData() {
    return {
        formLayout: localStorage.getItem('farmaFormViewMode') || 'modern',
        setLayout(mode) { this.formLayout=mode; localStorage.setItem('farmaFormViewMode',mode); window.dispatchEvent(new CustomEvent('farma:layout-changed',{detail:{mode}})); },
        detalles: [],
        productos: [],
        init() {
            this.productos = window._recetaProductos || [];
            this.agregarDetalle();
        },
        getQueryKey(idx) { return 'q_'+idx; },
        getFiltrados(idx) { return this['_filt_'+idx] || []; },
        getDropdown(idx) { return this['_dd_'+idx] || false; },
        filtrarProductos(idx) {
            const q = (this['_q_'+idx]||'').toLowerCase();
            this['_filt_'+idx] = q.length<1 ? this.productos.slice(0,8) : this.productos.filter(p=>p.nombre.toLowerCase().includes(q)).slice(0,12);
            this['_dd_'+idx] = true;
        },
        seleccionarProducto(prod, idx) {
            this.detalles[idx].producto_id = prod.id;
            this.detalles[idx].producto_nombre = prod.nombre;
            this['_dd_'+idx] = false;
            this['_q_'+idx] = '';
        },
        agregarDetalle() {
            const idx = this.detalles.length;
            this.detalles.push({ producto_id:'', producto_nombre:'', cantidad_recetada:1, posologia:'' });
            this['_q_'+idx]=''; this['_filt_'+idx]=[]; this['_dd_'+idx]=false;
        },
        quitarDetalle(idx) { if(this.detalles.length>1) this.detalles.splice(idx,1); }
    };
}
</script>
@endpush
@section('content')
<div x-data="recetaFormData()"
     x-init="init()"
     :class="formLayout==='compact'?'w-full':'max-w-5xl mx-auto'"
     class="space-y-4 transition-all duration-200">

@php
$recetaProductosJson = $productos->map(function($p){ return ['id'=>$p->id,'nombre'=>$p->nombre]; })->values();
@endphp
<script>window._recetaProductos = @json($recetaProductosJson);</script>

    {{-- Breadcrumb & Toggle --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-1 border-b border-slate-300/80 dark:border-slate-800">
        <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
            <a href="{{ route('recetas.index') }}" class="hover:text-emerald-600 transition">Recetas Medicas</a>
            <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-slate-800 dark:text-slate-200 font-semibold">Nueva</span>
        </nav>
        <div class="inline-flex items-center p-0.5 rounded-xl bg-slate-200/80 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-xs font-semibold shadow-2xs">
            <button type="button" @click="setLayout('modern')" :class="formLayout==='modern'?'bg-white dark:bg-slate-700 text-emerald-700 dark:text-emerald-400 shadow-xs font-bold':'text-slate-600 hover:text-slate-900 dark:text-slate-400'" class="px-2.5 py-1 rounded-lg transition flex items-center space-x-1 cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                <span>Moderna</span>
            </button>
            <button type="button" @click="setLayout('compact')" :class="formLayout==='compact'?'bg-white dark:bg-slate-700 text-emerald-700 dark:text-emerald-400 shadow-xs font-bold':'text-slate-600 hover:text-slate-900 dark:text-slate-400'" class="px-2.5 py-1 rounded-lg transition flex items-center space-x-1 cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                <span>Compacta</span>
            </button>
        </div>
    </div>

    <div class="flex items-center justify-between gap-2">
        <div><h1 class="text-lg sm:text-xl font-bold text-slate-900 dark:text-white">Editar Receta: {{ \$receta->numero_receta }}</h1><p class="text-xs text-slate-500 dark:text-slate-400">Modifica los datos de la receta medica.</p></div>
        <a href="{{ route('recetas.index') }}" class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-100 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-xl transition shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Volver</span>
        </a>
    </div>

    @if($errors->any())
    <div class="px-4 py-3 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-300 text-xs text-rose-800 dark:text-rose-300">
        <ul class="list-disc list-inside space-y-0.5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <form method="POST" action="{{ route('recetas.update', \$receta) }}" enctype="multipart/form-data">
    @csrf
        @method('PUT')
    {{-- ===== COMPACTA ===== --}}
    <template x-if="formLayout === 'compact'">
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-300 dark:border-slate-800 shadow-md p-4 space-y-4 animate-fadeIn">
        <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800 bg-slate-100/80 dark:bg-slate-800/60 -mx-4 -mt-4 p-3 rounded-t-2xl">
            <div class="flex items-center space-x-2">
                <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                <span class="text-xs font-extrabold text-slate-800 dark:text-slate-200 uppercase tracking-wide">EDITAR RECETA MEDICA</span>
            </div>
            <div class="flex items-center space-x-2">
                <button type="button" @click="agregarDetalle()" class="px-3 py-1.5 rounded-lg border border-emerald-300 dark:border-emerald-700 text-emerald-700 dark:text-emerald-400 hover:bg-emerald-50 text-xs font-bold transition">+ Med.</button>
                <button type="submit" class="px-4 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-extrabold shadow-sm transition flex items-center space-x-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Actualizar</span>
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
            {{-- Col 1: Paciente --}}
            <div class="p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/40 dark:bg-slate-800/30 space-y-2.5">
                <div class="text-[11px] font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400 border-b border-slate-200/60 dark:border-slate-700/60 pb-1.5">Paciente</div>
                <div><label class="block text-[10px] font-semibold text-slate-500 mb-0.5">Cliente (opt.)</label>
                    <select name="cliente_id" class="w-full px-2 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                        <option value="">-- Sin cliente --</option>
                        @foreach($clientes as $cli)<option value="{{ $cli->id }}" {{ old('cliente_id')==$cli->id?'selected':'' }}>{{ $cli->nombre }}</option>@endforeach
                    </select>
                </div>
                <div><label class="block text-[10px] font-semibold text-slate-500 mb-0.5">Nombre Paciente *</label><input type="text" name="paciente_nombre" value="{{ old('paciente_nombre', $receta->paciente_nombre) }}" required placeholder="Nombre completo..." class="w-full px-2 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500"></div>
                <div class="grid grid-cols-2 gap-1.5">
                    <div><label class="block text-[10px] font-semibold text-slate-500 mb-0.5">DNI</label><input type="text" name="paciente_documento" value="{{ old('paciente_documento', $receta->paciente_documento) }}" placeholder="DNI..." class="w-full px-2 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500"></div>
                    <div><label class="block text-[10px] font-semibold text-slate-500 mb-0.5">Edad</label><input type="number" name="paciente_edad" value="{{ old('paciente_edad', $receta->paciente_edad) }}" min="0" max="130" placeholder="Anos" class="w-full px-2 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500"></div>
                </div>
            </div>

            {{-- Col 2: Medico --}}
            <div class="p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/40 dark:bg-slate-800/30 space-y-2.5">
                <div class="text-[11px] font-bold uppercase tracking-wider text-indigo-700 dark:text-indigo-400 border-b border-slate-200/60 dark:border-slate-700/60 pb-1.5">Medico Prescriptor</div>
                <div><label class="block text-[10px] font-semibold text-slate-500 mb-0.5">Nombre Medico *</label><input type="text" name="medico_nombre" value="{{ old('medico_nombre', $receta->medico_nombre) }}" required placeholder="Dr. ..." class="w-full px-2 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500"></div>
                <div><label class="block text-[10px] font-semibold text-slate-500 mb-0.5">CMP *</label><input type="text" name="medico_colegiatura" value="{{ old('medico_colegiatura', $receta->medico_colegiatura) }}" required placeholder="CMP-12345" class="w-full px-2 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500"></div>
                <div><label class="block text-[10px] font-semibold text-slate-500 mb-0.5">Especialidad</label><input type="text" name="medico_especialidad" value="{{ old('medico_especialidad', $receta->medico_especialidad ?? '') }}" placeholder="Med. General..." class="w-full px-2 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500"></div>
                <div><label class="block text-[10px] font-semibold text-slate-500 mb-0.5">Institucion</label><input type="text" name="institucion_salud" value="{{ old('institucion_salud', $receta->institucion_salud ?? '') }}" placeholder="Hospital..." class="w-full px-2 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500"></div>
            </div>

            {{-- Col 3: Receta --}}
            <div class="p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/40 dark:bg-slate-800/30 space-y-2.5">
                <div class="text-[11px] font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400 border-b border-slate-200/60 dark:border-slate-700/60 pb-1.5">Datos Receta</div>
                <div><label class="block text-[10px] font-semibold text-slate-500 mb-0.5">N Receta *</label><input type="text" name="numero_receta" value="{{ old('numero_receta', $receta->numero_receta) }}" required placeholder="RX-001..." class="w-full px-2 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500"></div>
                <div><label class="block text-[10px] font-semibold text-slate-500 mb-0.5">Tipo *</label>
                    <select name="tipo_receta" required class="w-full px-2 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                        <option value="simple" {{ old('tipo_receta','simple')==='simple'?'selected':'' }}>Simple</option>
                        <option value="retenida" {{ old('tipo_receta')==='retenida'?'selected':'' }}>Retenida</option>
                    </select>
                </div>
                <div><label class="block text-[10px] font-semibold text-slate-500 mb-0.5">Emision *</label><input type="date" name="fecha_emision" value="{{ old('fecha_emision', $receta->fecha_emision?->toDateString()) }}" required class="w-full px-2 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500"></div>
                <div><label class="block text-[10px] font-semibold text-slate-500 mb-0.5">Vencimiento</label><input type="date" name="fecha_vencimiento" value="{{ old('fecha_vencimiento', $receta->fecha_vencimiento?->toDateString()) }}" class="w-full px-2 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500"></div>
                <div><label class="block text-[10px] font-semibold text-slate-500 mb-0.5">Observaciones</label><textarea name="observaciones" rows="2" class="w-full px-2 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">{{ old('observaciones') }}</textarea></div>
            </div>

            {{-- Col 4: Medicamentos --}}
            <div class="p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/40 dark:bg-slate-800/30 space-y-2.5">
                <div class="text-[11px] font-bold uppercase tracking-wider text-rose-700 dark:text-rose-400 border-b border-slate-200/60 dark:border-slate-700/60 pb-1.5">Medicamentos</div>
                <template x-for="(det, idx) in detalles" :key="idx">
                    <div class="p-2 bg-white dark:bg-slate-900 rounded-lg border border-slate-200 dark:border-slate-700 space-y-1.5">
                        <input type="hidden" :name="'detalles['+idx+'][producto_id]'" :value="det.producto_id">
                        <div>
                            <template x-if="det.producto_id">
                                <div class="flex items-center justify-between px-2 py-1 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 rounded-md">
                                    <span class="text-[10px] font-semibold text-emerald-700 dark:text-emerald-300 truncate" x-text="det.producto_nombre"></span>
                                    <button type="button" @click="det.producto_id='';det.producto_nombre=''" class="text-slate-400 hover:text-rose-500 ml-1 shrink-0">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            </template>
                            <template x-if="!det.producto_id">
                                <div class="relative">
                                    <input type="text" :value="this['_q_'+idx]" @input="this['_q_'+idx]=$event.target.value; filtrarProductos(idx)" @focus="filtrarProductos(idx)" @keydown.escape="this['_dd_'+idx]=false" placeholder="Buscar medicamento..." class="w-full px-2 py-1 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-md text-[11px] text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                                    <div x-show="this['_dd_'+idx]" @click.outside="this['_dd_'+idx]=false" class="absolute z-50 w-full mt-0.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg shadow-lg max-h-36 overflow-y-auto">
                                        <template x-for="p in (this['_filt_'+idx]||[])" :key="p.id">
                                            <button type="button" @click="seleccionarProducto(p,idx)" class="w-full text-left px-2.5 py-1.5 text-[11px] text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition" x-text="p.nombre"></button>
                                        </template>
                                        <div x-show="!(this['_filt_'+idx]||[]).length" class="px-2.5 py-1.5 text-[11px] text-slate-400">Sin resultados</div>
                                    </div>
                                </div>
                            </template>
                        </div>
                        <div class="grid grid-cols-2 gap-1.5">
                            <div><label class="block text-[10px] text-slate-400 mb-0.5">Cant. *</label><input type="number" :name="'detalles['+idx+'][cantidad_recetada]'" x-model="det.cantidad_recetada" min="1" required class="w-full px-2 py-1 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-md text-[11px] text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500"></div>
                            <div class="flex items-end">
                                <button type="button" @click="quitarDetalle(idx)" :disabled="detalles.length<=1" class="w-full py-1 text-[10px] rounded-md text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/40 border border-rose-200 dark:border-rose-800 transition disabled:opacity-30 disabled:cursor-not-allowed font-semibold">Quitar</button>
                            </div>
                        </div>
                        <div><input type="text" :name="'detalles['+idx+'][posologia]'" x-model="det.posologia" placeholder="1 tab c/8h..." class="w-full px-2 py-1 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-md text-[11px] text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500"></div>
                    </div>
                </template>
                <button type="button" @click="agregarDetalle()" class="w-full py-1.5 text-[11px] rounded-lg border border-dashed border-emerald-300 dark:border-emerald-700 text-emerald-700 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-950/30 transition font-semibold">+ Agregar Medicamento</button>
            </div>
        </div>

        <div class="pt-2 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <span class="text-[11px] text-slate-400">Adjunto disponible en vista Moderna.</span>
            <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs transition flex items-center space-x-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>Actualizar Receta</span>
            </button>
        </div>
    </div>
    </template>

    {{-- ===== MODERNA ===== --}}
    <template x-if="formLayout === 'modern'">
    <div class="space-y-6 animate-fadeIn">

        {{-- Paciente --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm space-y-5">
            <div class="border-b border-slate-100 dark:border-slate-800 pb-3"><h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center space-x-2"><span class="w-2 h-2 rounded-full bg-emerald-500"></span><span>Datos del Paciente</span></h3></div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Cliente Registrado <span class="text-slate-400 font-normal">(Opcional)</span></label>
                    <select name="cliente_id" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition">
                        <option value="">-- Sin cliente registrado --</option>
                        @foreach($clientes as $cli)<option value="{{ $cli->id }}" {{ old('cliente_id')==$cli->id?'selected':'' }}>{{ $cli->nombre }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Nombre del Paciente <span class="text-rose-500">*</span></label>
                    <input type="text" name="paciente_nombre" value="{{ old('paciente_nombre', $receta->paciente_nombre) }}" required placeholder="Nombre completo del paciente..." class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition @error('paciente_nombre') border-rose-500 @enderror">
                    @error('paciente_nombre')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">N Documento / DNI</label>
                    <input type="text" name="paciente_documento" value="{{ old('paciente_documento', $receta->paciente_documento) }}" placeholder="DNI, Pasaporte..." class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Edad</label>
                    <input type="number" name="paciente_edad" value="{{ old('paciente_edad', $receta->paciente_edad) }}" min="0" max="130" placeholder="Anos" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition">
                </div>
            </div>
        </div>

        {{-- Medico --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm space-y-5">
            <div class="border-b border-slate-100 dark:border-slate-800 pb-3"><h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center space-x-2"><span class="w-2 h-2 rounded-full bg-indigo-500"></span><span>Medico Prescriptor</span></h3></div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Nombre del Medico <span class="text-rose-500">*</span></label>
                    <input type="text" name="medico_nombre" value="{{ old('medico_nombre', $receta->medico_nombre) }}" required placeholder="Dr. Juan Perez..." class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition @error('medico_nombre') border-rose-500 @enderror">
                    @error('medico_nombre')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">N Colegiatura / CMP <span class="text-rose-500">*</span></label>
                    <input type="text" name="medico_colegiatura" value="{{ old('medico_colegiatura', $receta->medico_colegiatura) }}" required placeholder="CMP-12345..." class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition @error('medico_colegiatura') border-rose-500 @enderror">
                    @error('medico_colegiatura')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Especialidad</label>
                    <input type="text" name="medico_especialidad" value="{{ old('medico_especialidad', $receta->medico_especialidad ?? '') }}" placeholder="Cardiologia, Medicina General..." class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Institucion de Salud</label>
                    <input type="text" name="institucion_salud" value="{{ old('institucion_salud', $receta->institucion_salud ?? '') }}" placeholder="Hospital, Clinica..." class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition">
                </div>
            </div>
        </div>

        {{-- Datos Receta --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm space-y-5">
            <div class="border-b border-slate-100 dark:border-slate-800 pb-3"><h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center space-x-2"><span class="w-2 h-2 rounded-full bg-amber-500"></span><span>Datos de la Receta</span></h3></div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">N de Receta <span class="text-rose-500">*</span></label>
                    <input type="text" name="numero_receta" value="{{ old('numero_receta', $receta->numero_receta) }}" required placeholder="RX-2024-001..." class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition @error('numero_receta') border-rose-500 @enderror">
                    @error('numero_receta')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Tipo de Receta <span class="text-rose-500">*</span></label>
                    <select name="tipo_receta" required class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition">
                        <option value="simple" {{ old('tipo_receta','simple')==='simple'?'selected':'' }}>Simple</option>
                        <option value="retenida" {{ old('tipo_receta')==='retenida'?'selected':'' }}>Retenida (queda en farmacia)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Fecha de Emision <span class="text-rose-500">*</span></label>
                    <input type="date" name="fecha_emision" value="{{ old('fecha_emision', $receta->fecha_emision?->toDateString()) }}" required class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition @error('fecha_emision') border-rose-500 @enderror">
                    @error('fecha_emision')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Fecha de Vencimiento</label>
                    <input type="date" name="fecha_vencimiento" value="{{ old('fecha_vencimiento', $receta->fecha_vencimiento?->toDateString()) }}" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Adjunto (PDF o Imagen)</label>
                    <input type="file" name="archivo_receta" accept=".pdf,.jpg,.jpeg,.png" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-600 file:text-white hover:file:bg-emerald-700 cursor-pointer">
                    <p class="text-xs text-slate-400 mt-1">Max. 5 MB. Formatos: PDF, JPG, PNG.</p>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Observaciones</label>
                    <textarea name="observaciones" rows="2" placeholder="Notas adicionales..." class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition">{{ old('observaciones') }}</textarea>
                </div>
            </div>
        </div>

        {{-- Medicamentos --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
            <div class="border-b border-slate-100 dark:border-slate-800 pb-3 flex items-center justify-between">
                <div><h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center space-x-2"><span class="w-2 h-2 rounded-full bg-rose-500"></span><span>Medicamentos Prescritos</span></h3><p class="text-xs text-slate-400 mt-0.5">Minimo 1 medicamento requerido.</p></div>
                <button type="button" @click="agregarDetalle()" class="inline-flex items-center space-x-1.5 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Agregar</span>
                </button>
            </div>
            @error('detalles')<p class="text-rose-500 text-xs">{{ $message }}</p>@enderror
            <template x-for="(det, idx) in detalles" :key="idx">
                <div class="flex flex-col md:flex-row gap-3 p-4 bg-slate-50 dark:bg-slate-800/40 rounded-xl border border-slate-200 dark:border-slate-700">
                    <input type="hidden" :name="'detalles['+idx+'][producto_id]'" :value="det.producto_id">
                    <div class="flex-1 min-w-0">
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Medicamento <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <div x-show="det.producto_id" class="flex items-center justify-between px-3 py-2 bg-white dark:bg-slate-900 border border-emerald-300 dark:border-emerald-700 rounded-xl">
                                <span class="text-xs font-semibold text-slate-800 dark:text-slate-200" x-text="det.producto_nombre"></span>
                                <button type="button" @click="det.producto_id='';det.producto_nombre=''" class="text-slate-400 hover:text-rose-500 ml-2">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                            <div x-show="!det.producto_id" class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                </div>
                                <input type="text" :value="this['_q_'+idx]" @input="this['_q_'+idx]=$event.target.value;filtrarProductos(idx)" @focus="filtrarProductos(idx)" @keydown.escape="this['_dd_'+idx]=false" placeholder="Buscar medicamento por nombre..."
                                       class="w-full pl-9 pr-3 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500">
                                <div x-show="this['_dd_'+idx]" @click.outside="this['_dd_'+idx]=false" class="absolute z-50 w-full mt-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-lg max-h-44 overflow-y-auto">
                                    <template x-for="p in (this['_filt_'+idx]||[])" :key="p.id">
                                        <button type="button" @click="seleccionarProducto(p,idx)" class="w-full text-left px-3 py-2 text-xs text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition" x-text="p.nombre"></button>
                                    </template>
                                    <div x-show="!(this['_filt_'+idx]||[]).length" class="px-3 py-2 text-xs text-slate-400 text-center">Sin resultados</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="w-full md:w-28 shrink-0">
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Cantidad <span class="text-rose-500">*</span></label>
                        <input type="number" :name="'detalles['+idx+'][cantidad_recetada]'" x-model="det.cantidad_recetada" min="1" required class="w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500">
                    </div>
                    <div class="flex-1 min-w-0">
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Posologia / Indicaciones</label>
                        <input type="text" :name="'detalles['+idx+'][posologia]'" x-model="det.posologia" placeholder="1 comp. cada 8h..." class="w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500">
                    </div>
                    <div class="flex items-end shrink-0">
                        <button type="button" @click="quitarDetalle(idx)" :disabled="detalles.length<=1" class="p-2 rounded-xl text-slate-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition disabled:opacity-30 disabled:cursor-not-allowed">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </div>
            </template>
        </div>

        {{-- Sticky Footer --}}
        <div class="sticky bottom-0 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-3.5 bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border-t border-slate-200 dark:border-slate-800 shadow-lg z-20 flex items-center justify-between rounded-b-2xl">
            <a href="{{ route('recetas.index') }}" class="px-5 py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-xl transition">Cancelar</a>
            <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-sm font-semibold rounded-xl shadow-sm transition inline-flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>Actualizar Receta</span>
            </button>
        </div>
    </div>
    </template>

    </form>
</div>
@endsection

