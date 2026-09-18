@extends('layouts.app')
@section('title', 'Ajustar Stock - FarmaBien')
@push('scripts')
<script>
function ajusteForm() {
    return {
        loteId: null,
        loteQuery: '',
        lotesFiltrados: [],
        showDropdown: false,
        lote: null,
        stockNuevo: '',
        subtipo: 'ajuste_manual',
        motivo: '',
        lotes: [],
        get diferencia() {
            if (!this.lote || this.stockNuevo === '') return null;
            return parseInt(this.stockNuevo) - parseInt(this.lote.stock_actual);
        },
        get diferenciaLabel() {
            const d = this.diferencia;
            if (d === null) return '';
            if (d === 0) return 'Sin cambio';
            return (d > 0 ? '+' : '') + d + ' unidades';
        },
        sumar(n) {
            const actual = parseInt(this.stockNuevo) || 0;
            this.stockNuevo = Math.max(0, actual + n);
        },
        init() {
            this.lotes = window._ajusteLotes || [];
            const p = new URLSearchParams(window.location.search);
            const id = p.get('lote');
            if (id) { const f = this.lotes.find(l => String(l.id) === id); if(f) this.seleccionar(f); }
        },
        filtrar() {
            const q = this.loteQuery.toLowerCase();
            this.lotesFiltrados = q
                ? this.lotes.filter(l => l.numero_lote.toLowerCase().includes(q) || l.producto.toLowerCase().includes(q)).slice(0,12)
                : this.lotes.slice(0,10);
            this.showDropdown = true;
        },
        seleccionar(l) {
            this.loteId = l.id; this.loteQuery = l.numero_lote + ' — ' + l.producto;
            this.lote = l; this.stockNuevo = l.stock_actual; this.showDropdown = false;
        },
        deseleccionar() { this.loteId=null; this.loteQuery=''; this.lote=null; this.stockNuevo=''; },
        limpiar() { this.deseleccionar(); this.subtipo='ajuste_manual'; this.motivo=''; }
    };
}
</script>
@endpush
@section('content')
@php
$lotesJson = $lotes->map(function($l) {
    return [
        'id'               => $l->id,
        'numero_lote'      => $l->numero_lote,
        'producto'         => $l->producto->nombre ?? 'N/A',
        'stock_actual'     => $l->stock_actual,
        'stock_inicial'    => $l->stock_inicial,
        'fecha_vencimiento'=> $l->fecha_vencimiento?->format('d/m/Y') ?? '—',
        'vencido'          => $l->estaVencido(),
        'precio_compra'    => (float)$l->precio_compra,
    ];
})->values();
@endphp
<script>window._ajusteLotes = @json($lotesJson);</script>

<div x-data="ajusteForm()" x-init="init()" class="max-w-2xl mx-auto space-y-5">

    {{-- Breadcrumb + Header --}}
    <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('inventario.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inventario</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-800 dark:text-slate-200 font-semibold">Ajuste de Stock</span>
    </nav>

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">Ajuste Manual de Inventario</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Cada ajuste queda registrado en el Kardex con auditoria completa.</p>
        </div>
        <a href="{{ route('inventario.index') }}" class="inline-flex items-center space-x-2 px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-xl transition shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Volver</span>
        </a>
    </div>

    @if(session('error'))
    <div class="px-4 py-3 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-xs text-rose-700 dark:text-rose-300">{{ session('error') }}</div>
    @endif
    @if($errors->any())
    <div class="px-4 py-3 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-xs text-rose-700 dark:text-rose-300">
        <ul class="list-disc list-inside space-y-0.5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <form method="POST" action="{{ route('inventario.ajustar.store') }}">
    @csrf

    {{-- Panel: Buscar Lote --}}
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm p-5 space-y-4">
        <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Seleccionar Lote
        </h3>
        <div>
            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Busca por numero de lote o nombre del medicamento <span class="text-rose-500">*</span></label>
            <input type="hidden" name="lote_id" :value="loteId">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" x-model="loteQuery" @focus="filtrar()" @input="filtrar()" @keydown.escape="showDropdown=false" :readonly="loteId !== null" placeholder="Ej: L-2024-001 o Ibuprofeno..." class="w-full pl-10 pr-10 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition" :class="loteId ? 'bg-emerald-50 dark:bg-emerald-950/20 border-emerald-300 dark:border-emerald-700 cursor-default' : ''">
                <div x-show="loteId" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                    <button type="button" @click="deseleccionar()" class="text-slate-400 hover:text-rose-500 transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                </div>
                <div x-show="showDropdown && !loteId" @click.outside="showDropdown=false" class="absolute z-50 w-full mt-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-lg max-h-60 overflow-y-auto">
                    <template x-for="l in lotesFiltrados" :key="l.id">
                        <button type="button" @click="seleccionar(l)" class="w-full text-left px-4 py-3 text-sm hover:bg-emerald-50 dark:hover:bg-emerald-950/20 transition border-b border-slate-100 dark:border-slate-700/50 last:border-0">
                            <div class="flex items-center justify-between gap-2">
                                <div>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200" x-text="l.producto"></span>
                                    <span class="text-xs text-slate-400 font-mono ml-2" x-text="'Lote: '+l.numero_lote"></span>
                                    <span x-show="l.vencido" class="ml-2 text-[10px] font-bold text-rose-500 uppercase">Vencido</span>
                                </div>
                                <div class="text-right shrink-0">
                                    <p class="text-sm font-bold text-emerald-600 dark:text-emerald-400" x-text="l.stock_actual+' un.'"></p>
                                    <p class="text-[10px] text-slate-400" x-text="l.fecha_vencimiento"></p>
                                </div>
                            </div>
                        </button>
                    </template>
                    <div x-show="lotesFiltrados.length===0" class="px-4 py-4 text-sm text-slate-400 text-center">Sin resultados</div>
                </div>
            </div>
        </div>

        {{-- Info lote seleccionado --}}
        <div x-show="lote" class="grid grid-cols-3 gap-3">
            <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl p-3 border border-slate-200 dark:border-slate-700">
                <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Medicamento</p>
                <p class="text-xs font-bold text-slate-800 dark:text-slate-200 mt-1" x-text="lote?.producto"></p>
            </div>
            <div class="bg-emerald-50 dark:bg-emerald-950/30 rounded-xl p-3 border border-emerald-200 dark:border-emerald-800">
                <p class="text-[10px] font-semibold text-emerald-600 uppercase tracking-wider">Stock Actual</p>
                <p class="text-xl font-extrabold text-emerald-700 dark:text-emerald-300 mt-0.5" x-text="lote?.stock_actual"></p>
            </div>
            <div class="rounded-xl p-3 border" :class="lote?.vencido ? 'bg-rose-50 dark:bg-rose-950/30 border-rose-200 dark:border-rose-800' : 'bg-slate-50 dark:bg-slate-800/50 border-slate-200 dark:border-slate-700'">
                <p class="text-[10px] font-semibold uppercase tracking-wider" :class="lote?.vencido ? 'text-rose-500' : 'text-slate-400'">Vencimiento</p>
                <p class="text-xs font-bold mt-1" :class="lote?.vencido ? 'text-rose-700 dark:text-rose-300' : 'text-slate-700 dark:text-slate-300'" x-text="lote?.fecha_vencimiento"></p>
                <p x-show="lote?.vencido" class="text-[10px] font-bold text-rose-500 uppercase">Vencido</p>
            </div>
        </div>
    </div>

    {{-- Panel: Ajuste de Stock con botones rapidos --}}
    <div x-show="lote" class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm p-5 space-y-5">
        <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-amber-400"></span> Ajuste de Stock
        </h3>

        {{-- Botones rapidos + input --}}
        <div>
            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-2">Ajuste rapido <span class="text-slate-400 font-normal">(o escribe el valor directamente)</span></label>
            {{-- Quick buttons: restar --}}
            <div class="flex flex-wrap items-center gap-2 mb-3">
                <span class="text-xs font-semibold text-slate-400 w-full sm:w-auto">Restar:</span>
                <button type="button" @click="sumar(-50)" class="px-3 py-1.5 rounded-lg bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-700 text-rose-700 dark:text-rose-300 text-xs font-bold hover:bg-rose-100 dark:hover:bg-rose-950/50 transition">-50</button>
                <button type="button" @click="sumar(-20)" class="px-3 py-1.5 rounded-lg bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-700 text-rose-700 dark:text-rose-300 text-xs font-bold hover:bg-rose-100 dark:hover:bg-rose-950/50 transition">-20</button>
                <button type="button" @click="sumar(-10)" class="px-3 py-1.5 rounded-lg bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-700 text-rose-700 dark:text-rose-300 text-xs font-bold hover:bg-rose-100 dark:hover:bg-rose-950/50 transition">-10</button>
                <button type="button" @click="sumar(-5)"  class="px-3 py-1.5 rounded-lg bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-700 text-rose-700 dark:text-rose-300 text-xs font-bold hover:bg-rose-100 dark:hover:bg-rose-950/50 transition">-5</button>
                <button type="button" @click="sumar(-1)"  class="px-3 py-1.5 rounded-lg bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-700 text-rose-700 dark:text-rose-300 text-xs font-bold hover:bg-rose-100 dark:hover:bg-rose-950/50 transition">-1</button>
            </div>
            <div class="flex flex-wrap items-center gap-2 mb-4">
                <span class="text-xs font-semibold text-slate-400 w-full sm:w-auto">Sumar:</span>
                <button type="button" @click="sumar(1)"   class="px-3 py-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-700 text-emerald-700 dark:text-emerald-300 text-xs font-bold hover:bg-emerald-100 dark:hover:bg-emerald-950/50 transition">+1</button>
                <button type="button" @click="sumar(5)"   class="px-3 py-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-700 text-emerald-700 dark:text-emerald-300 text-xs font-bold hover:bg-emerald-100 dark:hover:bg-emerald-950/50 transition">+5</button>
                <button type="button" @click="sumar(10)"  class="px-3 py-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-700 text-emerald-700 dark:text-emerald-300 text-xs font-bold hover:bg-emerald-100 dark:hover:bg-emerald-950/50 transition">+10</button>
                <button type="button" @click="sumar(20)"  class="px-3 py-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-700 text-emerald-700 dark:text-emerald-300 text-xs font-bold hover:bg-emerald-100 dark:hover:bg-emerald-950/50 transition">+20</button>
                <button type="button" @click="sumar(50)"  class="px-3 py-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-700 text-emerald-700 dark:text-emerald-300 text-xs font-bold hover:bg-emerald-100 dark:hover:bg-emerald-950/50 transition">+50</button>
                <button type="button" @click="stockNuevo=0" class="px-3 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-600 text-slate-600 dark:text-slate-400 text-xs font-bold hover:bg-slate-200 transition">Vaciar (0)</button>
            </div>

            {{-- Campo de stock nuevo + diferencia visual --}}
            <div class="flex items-center gap-4 p-4 bg-slate-50 dark:bg-slate-800/40 rounded-xl border border-slate-200 dark:border-slate-700">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Nuevo Stock Total <span class="text-rose-500">*</span></label>
                    <input type="number" name="stock_nuevo" x-model="stockNuevo" min="0" required placeholder="0" class="w-32 px-3 py-2.5 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-lg font-bold font-mono text-slate-900 dark:text-white text-center focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition" :class="parseInt(stockNuevo)<0 ? 'border-rose-500 bg-rose-50' : ''">
                </div>
                <div class="flex-1">
                    <p class="text-xs text-slate-400 mb-1">Diferencia resultante</p>
                    <div x-show="diferencia !== null" class="flex items-center gap-2">
                        <svg x-show="diferencia > 0" class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                        <svg x-show="diferencia < 0" class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                        <svg x-show="diferencia === 0" class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                        <span class="text-xl font-extrabold" :class="diferencia > 0 ? 'text-emerald-600 dark:text-emerald-400' : diferencia < 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-400'" x-text="diferenciaLabel"></span>
                    </div>
                    <p x-show="diferencia === 0 && stockNuevo !== ''" class="text-xs text-amber-600 dark:text-amber-400 font-semibold mt-1">Sin cambios — el nuevo stock es identico al actual.</p>
                </div>
            </div>
        </div>

        {{-- Motivo + Subtipo --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Tipo de Ajuste <span class="text-rose-500">*</span></label>
                <select name="subtipo" x-model="subtipo" required class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition">
                    <option value="ajuste_manual">Ajuste Manual (General)</option>
                    <option value="ajuste_positivo">Ajuste Positivo (Conteo mayor)</option>
                    <option value="ajuste_negativo">Ajuste Negativo (Conteo menor)</option>
                    <option value="merma_danio">Merma por Danio / Deterioro</option>
                    <option value="merma_vencimiento">Merma por Vencimiento</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Descripcion del Ajuste <span class="text-rose-500">*</span></label>
                <input type="text" name="motivo" x-model="motivo" required minlength="5" maxlength="255" placeholder="Ej: Conteo fisico del 18/09/2026..." class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition">
            </div>
        </div>

        {{-- Alerta lote vencido --}}
        <div x-show="lote && lote.vencido" class="flex items-start gap-2.5 px-4 py-3 rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-700 text-xs text-amber-800 dark:text-amber-300">
            <svg class="w-4 h-4 shrink-0 mt-0.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <span><strong>Lote vencido.</strong> Para darlo de baja correctamente usa tipo "Merma por Vencimiento" y presiona el boton "Vaciar (0)".</span>
        </div>
    </div>

    {{-- Footer --}}
    <div class="flex items-center justify-between gap-3">
        <button type="button" @click="limpiar()" class="px-4 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-xl transition">Limpiar</button>
        <button type="submit" :disabled="!loteId || stockNuevo==='' || diferencia===0 || motivo.length<5" class="inline-flex items-center space-x-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-sm font-semibold rounded-xl shadow-sm transition disabled:opacity-40 disabled:cursor-not-allowed">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span>Registrar Ajuste en Kardex</span>
        </button>
    </div>

    </form>

    {{-- Info --}}
    <div class="px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 text-xs text-slate-500 dark:text-slate-400 space-y-1">
        <p><strong class="text-slate-700 dark:text-slate-300">Como funciona:</strong> El ajuste actualiza el stock del lote con bloqueo de concurrencia (DB transaction).</p>
        <p>Se genera un registro permanente en el Kardex con stock anterior, nuevo, diferencia, costo valorado y usuario. No es eliminable — se corrige con otro ajuste.</p>
    </div>
</div>
@endsection
