@extends('layouts.app')
@section('title', 'Toma de Inventario — ' . $conteo->nombre . ' - FarmaBien')
@section('content')
<div x-data="{
    buscar: '',
    filtroLab: '',
    filtroCat: '',
    filtroRegimen: 'todos',
    soloDiferencias: false,
    soloPendientes: false,
    modalAprobarOpen: false,
    guardandoFilaId: null,
    
    // Métricas en vivo
    totalLotes: {{ $conteo->total_lotes }},
    contados: {{ $contados }},
    conDiferencia: {{ $conDiferencia }},
    diferenciaNeta: {{ $diferenciaNeta }},
    progresoPct: {{ $progresoPct }},
    estado: '{{ $conteo->estado }}',

    get faltantes() {
        return Math.max(0, this.totalLotes - this.contados);
    },

    get listoParaAprobar() {
        return this.estado === 'en_proceso' && this.faltantes === 0 && this.totalLotes > 0;
    },

    limpiarFiltros() {
        this.buscar = '';
        this.filtroLab = '';
        this.filtroCat = '';
        this.filtroRegimen = 'todos';
        this.soloDiferencias = false;
        this.soloPendientes = false;
        this.filtrarFilas();
    },

    autoGuardarFila(input, detalleId, stockSistema) {
        const val = input.value.trim();
        const stockFisico = val === '' ? null : parseInt(val, 10);
        
        // Actualizar visualmente la fila de inmediato
        const fila = input.closest('tr');
        const celdaDif = fila.querySelector('.col-dif');
        
        if (stockFisico === null) {
            celdaDif.innerHTML = '<span class=\"text-slate-400\">—</span>';
            fila.dataset.contado = 'false';
            fila.dataset.diferencia = '0';
            fila.classList.remove('bg-rose-50/50', 'dark:bg-rose-950/20', 'bg-emerald-50/50', 'dark:bg-emerald-950/20');
        } else {
            const dif = stockFisico - stockSistema;
            fila.dataset.contado = 'true';
            fila.dataset.diferencia = String(dif);
            if (dif < 0) {
                celdaDif.innerHTML = `<span class=\"font-bold font-mono text-red-900 dark:text-rose-400\">${dif}</span>`;
                fila.classList.add('bg-rose-50/50', 'dark:bg-rose-950/20');
                fila.classList.remove('bg-emerald-50/50', 'dark:bg-emerald-950/20');
            } else if (dif > 0) {
                celdaDif.innerHTML = `<span class=\"font-bold font-mono text-emerald-900 dark:text-emerald-400\">+${dif}</span>`;
                fila.classList.add('bg-emerald-50/50', 'dark:bg-emerald-950/20');
                fila.classList.remove('bg-rose-50/50', 'dark:bg-rose-950/20');
            } else {
                celdaDif.innerHTML = '<span class=\"text-slate-700 dark:text-slate-500 font-mono\">0</span>';
                fila.classList.remove('bg-rose-50/50', 'dark:bg-rose-950/20', 'bg-emerald-50/50', 'dark:bg-emerald-950/20');
            }
        }

        // Petición AJAX silenciosa para auto-guardar
        this.guardandoFilaId = detalleId;
        fetch('{{ route('inventario.conteos.guardar-fila', $conteo) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                detalle_id: detalleId,
                stock_fisico: stockFisico
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                this.contados = data.contados;
                this.conDiferencia = data.con_diferencia;
                this.diferenciaNeta = data.diferencia_neta;
                this.progresoPct = data.progreso_pct;
            }
            this.guardandoFilaId = null;
        })
        .catch(err => {
            console.error('Error auto-guardando fila:', err);
            this.guardandoFilaId = null;
        });
    },

    pasarSiguienteFila(e) {
        const inputs = Array.from(document.querySelectorAll('#tbodyConteo input[type=number]:not([disabled])'));
        const idx = inputs.indexOf(e.target);
        if (idx !== -1 && idx + 1 < inputs.length) {
            inputs[idx + 1].focus();
            inputs[idx + 1].select();
        }
    },

    filtrarFilas() {
        const q = this.buscar.toLowerCase().trim();
        const lab = this.filtroLab;
        const cat = this.filtroCat;
        const reg = this.filtroRegimen;
        const soloDif = this.soloDiferencias;
        const soloPend = this.soloPendientes;

        let visibles = 0;
        const filas = document.querySelectorAll('#tbodyConteo tr[data-fila]');

        filas.forEach(f => {
            const fNombre = f.dataset.nombre || '';
            const fLab = f.dataset.lab || '';
            const fCat = f.dataset.cat || '';
            const fReg = f.dataset.reg || '';
            const fDif = parseInt(f.dataset.diferencia || '0', 10);
            const fContado = f.dataset.contado === 'true';

            let match = true;
            if (q && !fNombre.includes(q)) match = false;
            if (lab && fLab !== lab) match = false;
            if (cat && fCat !== cat) match = false;
            if (reg === 'venta_libre' && fReg !== 'libre') match = false;
            if (reg === 'controlados' && fReg !== 'controlado') match = false;
            if (soloDif && fDif === 0) match = false;
            if (soloPend && fContado) match = false;

            if (match) {
                f.style.display = '';
                visibles++;
            } else {
                f.style.display = 'none';
            }
        });

        const spanContador = document.getElementById('contadorVisibles');
        if (spanContador) {
            spanContador.textContent = visibles;
        }
    }
}" 
x-init="$watch('buscar', () => filtrarFilas()); $watch('filtroLab', () => filtrarFilas()); $watch('filtroCat', () => filtrarFilas()); $watch('filtroRegimen', () => filtrarFilas()); $watch('soloDiferencias', () => filtrarFilas()); $watch('soloPendientes', () => filtrarFilas());"
class="space-y-5">

    {{-- Fila 1: Breadcrumb Completo --}}
    <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('inventario.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inventario</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('inventario.conteos.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Tomas de Inventario</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-800 dark:text-slate-200 font-semibold truncate max-w-xs">{{ $conteo->nombre }}</span>
    </nav>

    {{-- Fila 2: Título + Badge de Estado + Subtítulo + Botones de Cabecera --}}
    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5 flex-wrap">
                <h1 class="text-xl font-bold text-slate-900 dark:text-white">
                    {{ $conteo->nombre }}
                </h1>
                @if($conteo->estado === 'en_proceso')
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-900 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-600 animate-pulse inline-block"></span>
                    <span>En Proceso</span>
                </span>
                @elseif($conteo->estado === 'completado')
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-900 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 inline-block"></span>
                    <span>Aprobada</span>
                </span>
                @elseif($conteo->estado === 'cancelado')
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400 inline-block"></span>
                    <span>Cancelada</span>
                </span>
                @endif
            </div>

            <p class="text-xs text-slate-700 dark:text-slate-400 mt-1">
                Iniciado por <span class="font-bold text-slate-900 dark:text-white">{{ $conteo->usuario->name ?? '—' }}</span>
                el {{ $conteo->created_at->format('d/m/Y H:i') }}
                @if($conteo->aprobadoPor)
                · Aprobado por <span class="font-bold text-emerald-900 dark:text-emerald-400">{{ $conteo->aprobadoPor->name }}</span>
                el {{ $conteo->completado_en?->format('d/m/Y H:i') }}
                @endif
            </p>

            {{-- Chips con el Alcance de la Toma --}}
            <div class="flex items-center gap-1.5 flex-wrap mt-2">
                <span class="text-[11px] font-bold text-slate-700 dark:text-slate-400">Alcance:</span>
                @foreach($conteo->alcance_chips as $chip)
                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-200 border border-slate-200 dark:border-slate-700">
                    {{ $chip }}
                </span>
                @endforeach
            </div>
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
        </div>
    </div>

    {{-- Alertas del Sistema --}}
    @if(session('success'))
    <div class="px-4 py-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-300 dark:border-emerald-700 text-xs text-emerald-900 dark:text-emerald-300 font-semibold flex items-center space-x-2">
        <svg class="w-4 h-4 shrink-0 text-emerald-700 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span>{{ session('success') }}</span>
    </div>
    @endif
    @if(session('error'))
    <div class="px-4 py-3 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-300 dark:border-rose-700 text-xs text-red-900 dark:text-rose-300 font-semibold flex items-center space-x-2">
        <svg class="w-4 h-4 shrink-0 text-red-700 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    {{-- Tarjetas de Resumen Global (No afectadas por filtros) --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Total Lotes --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 border border-slate-200 dark:border-slate-800 shadow-2xs">
            <p class="text-xs font-bold text-slate-700 dark:text-slate-400">Total Lotes</p>
            <p class="text-2xl font-black text-slate-900 dark:text-white mt-1" x-text="totalLotes">{{ $conteo->total_lotes }}</p>
        </div>

        {{-- Contados --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 border border-slate-200 dark:border-slate-800 shadow-2xs">
            <p class="text-xs font-bold text-slate-700 dark:text-slate-400">Contados</p>
            <div class="flex items-end gap-2 mt-1">
                <p class="text-2xl font-black text-emerald-900 dark:text-emerald-400" x-text="contados">{{ $contados }}</p>
                <p class="text-xs font-bold text-slate-700 dark:text-slate-400 mb-0.5">
                    / <span x-text="totalLotes">{{ $conteo->total_lotes }}</span> (<span x-text="progresoPct">{{ $progresoPct }}</span>%)
                </p>
            </div>
            <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-1.5 mt-2 overflow-hidden">
                <div class="bg-emerald-500 h-1.5 rounded-full transition-all duration-300" :style="'width: ' + progresoPct + '%'"></div>
            </div>
        </div>

        {{-- Lotes con Diferencia --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 border border-slate-200 dark:border-slate-800 shadow-2xs">
            <p class="text-xs font-bold text-slate-700 dark:text-slate-400">Lotes con Diferencia</p>
            <p class="text-2xl font-black mt-1" 
               :class="conDiferencia > 0 ? 'text-red-900 dark:text-rose-400' : 'text-slate-900 dark:text-white'"
               x-text="conDiferencia">
                {{ $conDiferencia }}
            </p>
        </div>

        {{-- Diferencia Neta --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 border border-slate-200 dark:border-slate-800 shadow-2xs">
            <p class="text-xs font-bold text-slate-700 dark:text-slate-400">Diferencia Neta</p>
            <p class="text-2xl font-black font-mono mt-1" 
               :class="diferenciaNeta > 0 ? 'text-emerald-900 dark:text-emerald-400' : (diferenciaNeta < 0 ? 'text-red-900 dark:text-rose-400' : 'text-slate-900 dark:text-white')">
                <span x-text="diferenciaNeta > 0 ? '+' + diferenciaNeta : diferenciaNeta">{{ $diferenciaNeta > 0 ? '+' . $diferenciaNeta : $diferenciaNeta }}</span>
                <span class="text-xs font-bold text-slate-700 dark:text-slate-400">unid.</span>
            </p>
        </div>
    </div>

    {{-- Barra de Filtros del Conteo (Tarjeta) --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 border border-slate-200 dark:border-slate-800 shadow-2xs space-y-3">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-center">
            {{-- Búsqueda Medicamento o Lote --}}
            <div class="lg:col-span-4 relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" 
                       x-model="buscar"
                       placeholder="Filtrar por medicamento o lote..."
                       class="w-full pl-10 pr-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition shadow-2xs">
            </div>

            {{-- Filtro Laboratorio --}}
            <div class="lg:col-span-3">
                <select x-model="filtroLab" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 transition shadow-2xs">
                    <option value="">Todos los Laboratorios</option>
                    @foreach($laboratoriosEnConteo as $lab)
                    <option value="{{ $lab->id }}">{{ $lab->nombre }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Filtro Categoría --}}
            <div class="lg:col-span-3">
                <select x-model="filtroCat" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 transition shadow-2xs">
                    <option value="">Todas las Categorías</option>
                    @foreach($categoriasEnConteo as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Botón Filtrar y Limpiar --}}
            <div class="lg:col-span-2 flex items-center justify-end gap-2">
                <button type="button" 
                        @click="filtrarFilas()"
                        class="h-9 px-3.5 rounded-full text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white transition inline-flex items-center gap-1.5 shadow-2xs cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    <span>Filtrar</span>
                </button>
                <button type="button" 
                        @click="limpiarFiltros()"
                        class="text-xs font-bold text-emerald-900 dark:text-emerald-400 hover:underline cursor-pointer">
                    Limpiar
                </button>
            </div>
        </div>

        {{-- Casillas rápidas y contador de resultados --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pt-2 border-t border-slate-100 dark:border-slate-800 text-xs">
            <div class="flex items-center gap-4">
                <label class="flex items-center gap-2 font-bold text-slate-700 dark:text-slate-300 cursor-pointer select-none">
                    <input type="checkbox" x-model="soloDiferencias" class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300">
                    <span>Solo con diferencia</span>
                </label>
                <label class="flex items-center gap-2 font-bold text-slate-700 dark:text-slate-300 cursor-pointer select-none">
                    <input type="checkbox" x-model="soloPendientes" class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300">
                    <span>Solo pendientes</span>
                </label>
            </div>

            <div class="text-slate-700 dark:text-slate-400 font-bold">
                Mostrando <span id="contadorVisibles" class="text-slate-900 dark:text-white">{{ $conteo->total_lotes }}</span> de {{ $conteo->total_lotes }} lotes
            </div>
        </div>
    </div>

    {{-- Formulario Principal de Conteo --}}
    <form id="formConteoLotes" method="POST" action="{{ route('inventario.conteos.guardar', $conteo) }}">
        @csrf

        {{-- Tabla de Lotes --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden" id="tablaConteo">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-700 dark:text-slate-400 font-bold border-b border-slate-200 dark:border-slate-800 uppercase tracking-wider">
                        <tr>
                            <th class="px-5 py-3.5">Medicamento</th>
                            <th class="px-5 py-3.5">N° Lote</th>
                            <th class="px-5 py-3.5">Vence</th>
                            <th class="px-5 py-3.5 text-center">Stock Sistema</th>
                            <th class="px-5 py-3.5 text-center w-36">Conteo Físico</th>
                            <th class="px-5 py-3.5 text-center">Diferencia</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-700 dark:text-slate-300" id="tbodyConteo">
                        @foreach($detalles as $det)
                        @php
                            $dif = $det->stock_fisico !== null ? ($det->stock_fisico - $det->stock_sistema) : null;
                            $esFaltante = $dif !== null && $dif < 0;
                            $esSobrante = $dif !== null && $dif > 0;
                            $esControlado = $det->producto?->tipo_control === 'controlado' || $det->producto?->requiere_receta;
                        @endphp
                        <tr data-fila
                            data-nombre="{{ strtolower($det->producto->nombre ?? '') }} {{ strtolower($det->lote->numero_lote ?? '') }}"
                            data-lab="{{ $det->producto?->laboratorio_id }}"
                            data-cat="{{ $det->producto?->categoria_id }}"
                            data-reg="{{ $esControlado ? 'controlado' : 'libre' }}"
                            data-diferencia="{{ $dif ?? '0' }}"
                            data-contado="{{ $det->stock_fisico !== null ? 'true' : 'false' }}"
                            class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition {{ $esFaltante ? 'bg-rose-50/50 dark:bg-rose-950/20' : ($esSobrante ? 'bg-emerald-50/50 dark:bg-emerald-950/20' : '') }}">
                            
                            {{-- Medicamento --}}
                            <td class="px-5 py-3">
                                <div class="font-bold text-slate-900 dark:text-white flex items-center gap-1.5 flex-wrap">
                                    <span>{{ $det->producto->nombre }}</span>
                                    @if($esControlado)
                                    <span class="px-1.5 py-0.2 rounded text-[9px] font-black bg-purple-100 text-purple-900 dark:bg-purple-950/80 dark:text-purple-300 border border-purple-300 dark:border-purple-800">
                                        CONTROLADO
                                    </span>
                                    @endif
                                </div>
                                @if($det->producto->laboratorio)
                                <div class="text-slate-700 dark:text-slate-400 text-[10px]">{{ $det->producto->laboratorio->nombre }}</div>
                                @endif
                            </td>

                            {{-- N° Lote --}}
                            <td class="px-5 py-3 font-mono font-bold text-slate-700 dark:text-slate-300 text-xs">
                                {{ $det->lote->numero_lote ?? '—' }}
                            </td>

                            {{-- Vence --}}
                            <td class="px-5 py-3">
                                @if($det->lote && $det->lote->fecha_vencimiento)
                                @php 
                                    $vence = $det->lote->fecha_vencimiento;
                                    $vencido = $vence->isPast();
                                    $proximo = !$vencido && $vence <= now()->addDays(90);
                                @endphp
                                <span class="font-bold text-xs {{ $vencido ? 'text-red-900 dark:text-rose-400' : ($proximo ? 'text-amber-900 dark:text-amber-400' : 'text-slate-700 dark:text-slate-300') }}">
                                    {{ $vence->format('d/m/Y') }}
                                </span>
                                @else
                                <span class="text-slate-400">—</span>
                                @endif
                            </td>

                            {{-- Stock Sistema (Congelado) --}}
                            <td class="px-5 py-3 text-center font-mono font-black text-slate-900 dark:text-white text-sm">
                                {{ $det->stock_sistema }}
                            </td>

                            {{-- Conteo Físico --}}
                            <td class="px-5 py-3 text-center">
                                @if($conteo->estado === 'en_proceso')
                                <input type="number"
                                       name="cantidades[{{ $det->id }}]"
                                       value="{{ $det->stock_fisico }}"
                                       min="0"
                                       placeholder="—"
                                       @blur="autoGuardarFila($el, {{ $det->id }}, {{ $det->stock_sistema }})"
                                       @keydown.enter.prevent="autoGuardarFila($el, {{ $det->id }}, {{ $det->stock_sistema }}); pasarSiguienteFila($event)"
                                       class="w-24 text-center px-2 py-1.5 bg-slate-50 dark:bg-slate-800 border-2 border-slate-300 dark:border-slate-600 rounded-xl text-xs font-mono font-bold text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition shadow-2xs">
                                @else
                                <span class="font-mono font-bold text-sm text-slate-900 dark:text-white">
                                    {{ $det->stock_fisico ?? '—' }}
                                </span>
                                @endif
                            </td>

                            {{-- Diferencia --}}
                            <td class="px-5 py-3 text-center col-dif font-mono">
                                @if($dif !== null)
                                    @if($dif < 0)
                                    <span class="font-bold text-red-900 dark:text-rose-400">{{ $dif }}</span>
                                    @elseif($dif > 0)
                                    <span class="font-bold text-emerald-900 dark:text-emerald-400">+{{ $dif }}</span>
                                    @else
                                    <span class="text-slate-700 dark:text-slate-500">0</span>
                                    @endif
                                @else
                                <span class="text-slate-400">—</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Barra Fija Inferior (Sticky Footer para Conteo) --}}
        @if($conteo->estado === 'en_proceso')
        <div class="sticky bottom-0 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-3.5
                    bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border-t border-slate-200 dark:border-slate-800
                    shadow-xl z-20 flex flex-col sm:flex-row items-center justify-between gap-3">
            
            <div class="text-xs font-bold text-slate-700 dark:text-slate-400 flex items-center gap-2">
                <span>Contados: <span class="text-slate-900 dark:text-white font-black" x-text="contados + '/' + totalLotes">{{ $contados }}/{{ $conteo->total_lotes }}</span></span>
                <span x-show="faltantes > 0" class="text-amber-900 dark:text-amber-400 font-bold" x-text="'&bull; ' + faltantes + ' pendientes'">
                    &bull; {{ $conteo->total_lotes - $contados }} pendientes
                </span>
            </div>

            <div class="flex items-center gap-2">
                {{-- 1. Guardar Avance (Secundario Pastel Verde) --}}
                <button type="submit"
                        class="px-3.5 py-1.5 rounded-xl text-xs font-bold bg-emerald-50 hover:bg-emerald-100 text-emerald-900 dark:bg-emerald-950/60 dark:hover:bg-emerald-900 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 transition inline-flex items-center gap-1.5 cursor-pointer shadow-2xs">
                    <svg class="w-3.5 h-3.5 text-emerald-700 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                    <span>Guardar Avance</span>
                </button>

                {{-- 2. Aprobar Toma (Único Principal Verde Sólido) --}}
                <button type="button"
                        @click="modalAprobarOpen = true"
                        :disabled="!listoParaAprobar"
                        :class="!listoParaAprobar ? 'bg-emerald-600/40 cursor-not-allowed text-white/70' : 'bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white cursor-pointer'"
                        class="px-3.5 py-1.5 rounded-xl text-xs font-semibold shadow-xs transition inline-flex items-center gap-1.5"
                        :title="!listoParaAprobar ? 'Faltan ' + faltantes + ' lotes por contar' : 'Aprobar y ajustar diferencias en Kardex'">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span x-text="listoParaAprobar ? 'Aprobar Toma' : 'Faltan ' + faltantes + ' lotes por contar'">
                        Aprobar Toma
                    </span>
                </button>
            </div>
        </div>
        @endif
    </form>

    {{-- Cancelar Toma Link (Discreto al pie) --}}
    @if($conteo->estado === 'en_proceso')
    <div class="flex justify-end pt-2">
        <form method="POST" action="{{ route('inventario.conteos.cancelar', $conteo) }}"
              onsubmit="return confirm('¿Seguro que deseas cancelar esta toma de inventario sin aplicar ajustes?')">
            @csrf
            <button type="submit"
                    class="text-xs font-bold text-red-900 dark:text-rose-400 hover:underline p-1 cursor-pointer">
                Cancelar esta toma de inventario
            </button>
        </form>
    </div>
    @endif

    {{-- ============================================================== --}}
    {{-- MODAL CENTRADO: APROBAR TOMA DE INVENTARIO                     --}}
    {{-- ============================================================== --}}
    <template x-teleport="body">
        <div x-show="modalAprobarOpen" 
             x-cloak
             class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-slate-950/75 backdrop-blur-sm"
             @keydown.escape.window="modalAprobarOpen = false">
            <div @click.stop
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 class="bg-white dark:bg-slate-900 rounded-3xl max-w-2xl w-full border border-slate-200 dark:border-slate-800 shadow-2xl flex flex-col max-h-[90vh] overflow-hidden">
                
                {{-- Modal Header --}}
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <div class="flex items-center space-x-2.5">
                        <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                        <h3 class="text-base font-extrabold text-slate-900 dark:text-white">
                            Aprobar toma de inventario
                        </h3>
                    </div>
                    <button type="button" @click="modalAprobarOpen = false" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
                </div>

                {{-- Modal Body --}}
                <div class="p-6 space-y-4 overflow-y-auto flex-1 text-xs">
                    <p class="text-slate-700 dark:text-slate-300 font-medium">
                        Se procederá a cerrar la sesión <span class="font-bold text-slate-900 dark:text-white">{{ $conteo->nombre }}</span> y aplicar los ajustes correspondientes en el stock de cada lote con diferencia:
                    </p>

                    {{-- Resumen de Diferencias --}}
                    <div class="grid grid-cols-3 gap-3 p-3 bg-slate-50 dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 text-center">
                        <div>
                            <span class="text-[10px] font-bold text-slate-700 dark:text-slate-400 block uppercase">Con Diferencia</span>
                            <span class="text-lg font-black text-red-900 dark:text-rose-400" x-text="conDiferencia">{{ $conDiferencia }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-slate-700 dark:text-slate-400 block uppercase">Diferencia Neta</span>
                            <span class="text-lg font-black font-mono text-slate-900 dark:text-white" x-text="diferenciaNeta > 0 ? '+' + diferenciaNeta : diferenciaNeta">{{ $diferenciaNeta }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-slate-700 dark:text-slate-400 block uppercase">Sin Diferencia</span>
                            <span class="text-lg font-black text-emerald-900 dark:text-emerald-400" x-text="totalLotes - conDiferencia">{{ $conteo->total_lotes - $conDiferencia }}</span>
                        </div>
                    </div>

                    {{-- Aviso de Transacción Atómica y Kardex --}}
                    <div class="p-3.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-900 dark:text-emerald-300 space-y-1">
                        <span class="font-bold block flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Impacto en Inventario y Kardex:</span>
                        </span>
                        <p class="text-[11px] font-medium text-emerald-900 dark:text-emerald-300">
                            Se registrará un movimiento de Kardex con motivo <span class="font-mono font-bold">Ajuste por toma física</span> para cada lote que tenga diferencia y la toma pasará al estado de solo lectura <span class="font-bold">Aprobada</span>.
                        </p>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/60 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2">
                    <button type="button" 
                            @click="modalAprobarOpen = false"
                            class="px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition cursor-pointer shadow-2xs">
                        Cancelar
                    </button>
                    
                    <form method="POST" action="{{ route('inventario.conteos.aprobar', $conteo) }}">
                        @csrf
                        <button type="submit"
                                class="px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white shadow-xs transition inline-flex items-center gap-1.5 cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Aprobar y Ajustar Stock</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </template>
</div>
@endsection
