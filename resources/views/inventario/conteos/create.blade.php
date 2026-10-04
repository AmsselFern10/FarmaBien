@extends('layouts.app')
@section('title', 'Nueva Toma de Inventario - FarmaBien')
@section('content')
<div x-data="{
    nombre: @js(old('nombre', 'Toma de Inventario ' . now()->format('d/m/Y'))),
    notas: @js(old('notas', '')),
    regimenVenta: @js(old('regimen_venta', 'todos')),
    laboratoriosIds: [],
    categoriasIds: [],
    laboratoriosItems: [],
    categoriasItems: [],
    totalLotes: @js($totalLotesActivos),
    totalMedicamentos: @js($totalMedicamentosActivos),
    cargandoConteo: false,
    enviandoForm: false,
    idempotencyKey: 'toma_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9),
    timerDebounce: null,

    init() {
        // Escuchar eventos de cambio del componente AJAX
        window.addEventListener('ajax-select-change', (e) => {
            if (e.detail && e.detail.name === 'laboratorios_ids') {
                this.laboratoriosItems = e.detail.items || [];
                this.laboratoriosIds = this.laboratoriosItems.map(i => i.id);
                this.actualizarConteoVivo();
            } else if (e.detail && e.detail.name === 'categorias_ids') {
                this.categoriasItems = e.detail.items || [];
                this.categoriasIds = this.categoriasItems.map(i => i.id);
                this.actualizarConteoVivo();
            }
        });
    },

    setRegimen(r) {
        this.regimenVenta = r;
        this.actualizarConteoVivo();
    },

    actualizarConteoVivo() {
        clearTimeout(this.timerDebounce);
        this.cargandoConteo = true;

        this.timerDebounce = setTimeout(() => {
            fetch('{{ route('inventario.conteos.conteo-previo') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    laboratorios_ids: this.laboratoriosIds,
                    categorias_ids: this.categoriasIds,
                    regimen_venta: this.regimenVenta
                })
            })
            .then(res => res.json())
            .then(data => {
                this.totalLotes = typeof data.total_lotes === 'number' ? data.total_lotes : 0;
                this.totalMedicamentos = typeof data.total_medicamentos === 'number' ? data.total_medicamentos : 0;
                this.cargandoConteo = false;
            })
            .catch(err => {
                console.error('Error calculando conteo en vivo:', err);
                this.cargandoConteo = false;
            });
        }, 300);
    },

    enviarFormulario() {
        if (this.enviandoForm || this.totalLotes === 0) return;
        this.enviandoForm = true;
        document.getElementById('formCrearToma').submit();
    }
}" class="max-w-4xl mx-auto space-y-5">

    {{-- Fila 1: Breadcrumb Completo --}}
    <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('inventario.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inventario</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('inventario.conteos.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Tomas de Inventario</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-800 dark:text-slate-200 font-semibold">Nueva Toma</span>
    </nav>

    {{-- Fila 2: Título + Botones de Acción en la misma fila --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">Nueva Toma de Inventario</h1>
            <p class="text-xs text-slate-700 dark:text-slate-400 mt-0.5">
                Se generará un snapshot de 
                <span class="font-bold text-emerald-900 dark:text-emerald-400" x-text="totalLotes + ' lotes'">
                    {{ $totalLotesActivos }} lotes
                </span>
                activos con stock para que puedas contarlos físicamente.
            </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap shrink-0">
            <!-- 1. Botón Predecesor (Blanco) -->
            <a href="{{ route('inventario.conteos.index') }}" 
               class="px-3.5 py-1.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold transition inline-flex items-center gap-1.5 shrink-0 cursor-pointer shadow-2xs">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Tomas de Inventario</span>
            </a>

            <!-- 2. Botón Modo Full (Blanco) -->
            <button type="button" 
                    @click="$dispatch('toggle-pos-fullscreen')"
                    title="Modo Pantalla Completa / Ocultar Barras"
                    class="px-3.5 py-1.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold transition inline-flex items-center gap-1.5 shrink-0 cursor-pointer shadow-2xs">
                <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                <span>Modo Full</span>
            </button>

            <!-- 3. Botón Principal con Conteo en Vivo y Protección Doble Envío -->
            <button type="button"
                    @click="enviarFormulario()"
                    :disabled="totalLotes === 0 || enviandoForm"
                    :class="(totalLotes === 0 || enviandoForm) ? 'bg-emerald-600/40 cursor-not-allowed text-white/70' : 'bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white cursor-pointer'"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-semibold shadow-xs transition inline-flex items-center gap-1.5 shrink-0">
                <svg x-show="!enviandoForm" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                <svg x-show="enviandoForm" x-cloak class="w-3.5 h-3.5 animate-spin text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                <span x-text="enviandoForm ? 'Creando toma...' : 'Iniciar Conteo (' + totalLotes + ' lotes)'">
                    Iniciar Conteo ({{ $totalLotesActivos }} lotes)
                </span>
            </button>
        </div>
    </div>

    {{-- Alerta Informativa ¿Cómo funciona? --}}
    <div class="flex items-start gap-3.5 px-5 py-4 bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 rounded-2xl text-xs text-blue-900 dark:text-blue-300 shadow-2xs">
        <svg class="w-5 h-5 shrink-0 text-blue-700 dark:text-blue-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <div class="space-y-1">
            <span class="font-extrabold text-sm block text-blue-950 dark:text-blue-200">¿Cómo funciona la toma de inventario?</span>
            <ol class="list-decimal list-inside space-y-1 text-blue-900 dark:text-blue-300 font-medium">
                <li>Defines el alcance deseado y creas la sesión &rarr; el sistema congela el stock actual de cada lote en un snapshot.</li>
                <li>Realizas el conteo físico en anaqueles o bodega unidad por unidad.</li>
                <li>Ingresas las cantidades físicas contadas en pantalla (se guardan automáticamente).</li>
                <li>Al aprobar la toma &rarr; el sistema ajusta automáticamente el stock de cada lote con diferencia y genera los movimientos de auditoría en el Kardex.</li>
            </ol>
        </div>
    </div>

    @if(session('error'))
    <div class="px-4 py-3 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-300 dark:border-rose-700 text-xs text-red-900 dark:text-rose-300 font-semibold flex items-center space-x-2">
        <svg class="w-4 h-4 shrink-0 text-red-700 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    {{-- Formulario Principal --}}
    <form id="formCrearToma" method="POST" action="{{ route('inventario.conteos.store') }}" class="space-y-5">
        @csrf
        <input type="hidden" name="idempotency_key" :value="idempotencyKey">

        {{-- Tarjeta 1: Datos Generales de la Toma --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200 dark:border-slate-800 shadow-xs space-y-4">
            <div class="border-b border-slate-100 dark:border-slate-800 pb-2 flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-600"></span>
                    <span>Datos de la Toma</span>
                </h3>
                <span class="text-xs text-slate-700 dark:text-slate-400 font-medium">Campos requeridos con <span class="text-red-900 font-bold">*</span></span>
            </div>

            <div class="grid grid-cols-1 gap-4">
                <div>
                    <label for="nombre" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                        Nombre del Conteo <span class="text-red-900 font-black">*</span>
                    </label>
                    <input type="text" id="nombre" name="nombre"
                           x-model="nombre"
                           required maxlength="200"
                           class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition shadow-2xs @error('nombre') border-rose-500 @enderror"
                           placeholder="Ej. Conteo Cíclico Ramos, Auditoría MINSA Controlados...">
                    @error('nombre') <p class="text-red-900 text-[10px] font-bold mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="notas" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                        Notas / Contexto
                    </label>
                    <textarea id="notas" name="notas" rows="2"
                              x-model="notas"
                              placeholder="Observaciones, motivo de auditoría, personal asignado, etc."
                              class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition shadow-2xs">{{ old('notas') }}</textarea>
                </div>
            </div>
        </div>

        {{-- Tarjeta 2: Alcance del Conteo (Filtros Combinados) --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200 dark:border-slate-800 shadow-xs space-y-4">
            <div class="border-b border-slate-100 dark:border-slate-800 pb-2 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
                        <span>Alcance del Conteo</span>
                    </h3>
                    <p class="text-[11px] text-slate-700 dark:text-slate-400 mt-0.5">
                        Filtra qué medicamentos formarán parte de la toma física. Sin filtros se contará todo el inventario activo.
                    </p>
                </div>
                
                {{-- Badge de conteo en vivo --}}
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-900 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                        <span x-show="cargandoConteo" class="w-2 h-2 rounded-full bg-emerald-600 animate-ping"></span>
                        <span x-show="!cargandoConteo" class="w-2 h-2 rounded-full bg-emerald-600"></span>
                        <span x-text="totalLotes + ' lotes de ' + totalMedicamentos + ' medicamentos'">
                            {{ $totalLotesActivos }} lotes de {{ $totalMedicamentosActivos }} medicamentos
                        </span>
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- 1. Filtro de Laboratorios con Componente C --}}
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                        Laboratorio(s)
                    </label>
                    <x-ajax-select 
                        name="laboratorios_ids" 
                        :endpoint="route('api.laboratorios.buscar-ajax')"
                        placeholder="Buscar y seleccionar laboratorios..."
                        type="laboratorio"
                        :multiple="true" />
                    <p class="text-[10px] text-slate-700 dark:text-slate-400">Deja vacío para incluir todos los laboratorios.</p>
                </div>

                {{-- 2. Filtro de Categorías con Componente C --}}
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                        Categoría(s)
                    </label>
                    <x-ajax-select 
                        name="categorias_ids" 
                        :endpoint="route('api.categorias.buscar-ajax')"
                        placeholder="Buscar y seleccionar categorías..."
                        type="categoria"
                        :multiple="true" />
                    <p class="text-[10px] text-slate-700 dark:text-slate-400">Deja vacío para incluir todas las categorías.</p>
                </div>
            </div>

            {{-- 3. Régimen de Venta (Selector Segmentado) --}}
            <div class="pt-2 border-t border-slate-100 dark:border-slate-800 space-y-2">
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                    Régimen de Venta
                </label>
                <div class="inline-flex p-1 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-bold gap-1">
                    <button type="button"
                            @click="setRegimen('todos')"
                            :class="regimenVenta === 'todos' ? 'bg-white dark:bg-slate-700 text-emerald-900 dark:text-emerald-300 shadow-xs' : 'text-slate-700 dark:text-slate-400 hover:text-slate-900'"
                            class="px-3.5 py-1.5 rounded-lg transition cursor-pointer">
                        Todos
                    </button>
                    <button type="button"
                            @click="setRegimen('venta_libre')"
                            :class="regimenVenta === 'venta_libre' ? 'bg-white dark:bg-slate-700 text-emerald-900 dark:text-emerald-300 shadow-xs' : 'text-slate-700 dark:text-slate-400 hover:text-slate-900'"
                            class="px-3.5 py-1.5 rounded-lg transition cursor-pointer">
                        Venta Libre
                    </button>
                    <button type="button"
                            @click="setRegimen('controlados')"
                            :class="regimenVenta === 'controlados' ? 'bg-white dark:bg-slate-700 text-purple-900 dark:text-purple-300 shadow-xs' : 'text-slate-700 dark:text-slate-400 hover:text-slate-900'"
                            class="px-3.5 py-1.5 rounded-lg transition cursor-pointer">
                        Controlados (MINSA / Receta)
                    </button>
                </div>
                <input type="hidden" name="regimen_venta" :value="regimenVenta">
            </div>

            {{-- Resumen de Alcance & Estado de Validación --}}
            <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-400">Alcance seleccionado:</span>
                    <template x-if="laboratoriosItems.length === 0 && categoriasItems.length === 0 && regimenVenta === 'todos'">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-200 border border-slate-200">
                            Todo el inventario
                        </span>
                    </template>
                    <template x-for="lab in laboratoriosItems" :key="'sum_lab_' + lab.id">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-indigo-50 text-indigo-900 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-200" x-text="'Lab: ' + lab.nombre"></span>
                    </template>
                    <template x-for="cat in categoriasItems" :key="'sum_cat_' + cat.id">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-50 text-amber-900 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200" x-text="'Categoría: ' + cat.nombre"></span>
                    </template>
                    <template x-if="regimenVenta === 'venta_libre'">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-900 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200">Venta Libre</span>
                    </template>
                    <template x-if="regimenVenta === 'controlados'">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-purple-50 text-purple-900 dark:bg-purple-950/60 dark:text-purple-300 border border-purple-200">Controlados</span>
                    </template>
                </div>

                {{-- Aviso de cero resultados --}}
                <div x-show="totalLotes === 0 && !cargandoConteo" x-cloak class="text-xs font-bold text-red-900 dark:text-rose-400 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-red-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>No hay lotes con stock para este alcance.</span>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
