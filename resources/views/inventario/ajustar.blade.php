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
        loteSeleccionado: null,
        stockNuevo: '',
        subtipo: 'ajuste_manual',
        motivo: '',
        lotes: [],
        get diferencia() {
            if (!this.loteSeleccionado || this.stockNuevo === '') return null;
            return parseInt(this.stockNuevo) - parseInt(this.loteSeleccionado.stock_actual);
        },
        get diferenciaLabel() {
            const d = this.diferencia;
            if (d === null) return '';
            if (d === 0) return '= Sin cambio';
            return (d > 0 ? '+' : '') + d + ' unidades';
        },
        get diferenciaClass() {
            const d = this.diferencia;
            if (d === null || d === 0) return 'text-slate-500';
            return d > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400';
        },
        init() {
            this.lotes = window._ajusteLotes || [];
            // Pre-select lote if passed via query string
            const urlParams = new URLSearchParams(window.location.search);
            const loteParam = urlParams.get('lote');
            if (loteParam) {
                const found = this.lotes.find(l => String(l.id) === String(loteParam));
                if (found) this.seleccionar(found);
            }
        },
        filtrar() {
            const q = this.loteQuery.toLowerCase();
            this.lotesFiltrados = q
                ? this.lotes.filter(l => l.numero_lote.toLowerCase().includes(q) || l.producto.toLowerCase().includes(q)).slice(0, 12)
                : this.lotes.slice(0, 10);
            this.showDropdown = true;
        },
        seleccionar(l) {
            this.loteId = l.id;
            this.loteQuery = l.numero_lote + ' — ' + l.producto;
            this.loteSeleccionado = l;
            this.stockNuevo = l.stock_actual;
            this.showDropdown = false;
        },
        deseleccionar() {
            this.loteId = null;
            this.loteQuery = '';
            this.loteSeleccionado = null;
            this.stockNuevo = '';
        },
        limpiar() {
            this.deseleccionar();
            this.subtipo = 'ajuste_manual';
            this.motivo = '';
        }
    };
}
</script>
@endpush
@section('content')
<script>
window._ajusteLotes = @json($lotes->map(fn($l) => [
    'id' => $l->id,
    'numero_lote' => $l->numero_lote,
    'producto' => $l->producto->nombre ?? 'N/A',
    'stock_actual' => $l->stock_actual,
    'stock_inicial' => $l->stock_inicial,
    'fecha_vencimiento' => $l->fecha_vencimiento?->format('d/m/Y') ?? '—',
    'vencido' => $l->estaVencido(),
    'precio_compra' => (float)$l->precio_compra,
])->values());
</script>

<div x-data="ajusteForm()" x-init="init()" class="max-w-2xl mx-auto space-y-4">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-300/80 dark:border-slate-800 pb-3">
        <div>
            <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400 mb-1">
                <a href="{{ route('inventario.index') }}" class="hover:text-indigo-600 transition">Inventario</a>
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="text-slate-800 dark:text-slate-200 font-semibold">Ajuste de Stock</span>
            </nav>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">Ajuste Manual de Inventario</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">Cada ajuste queda registrado en el Kardex con auditoría completa.</p>
        </div>
        <a href="{{ route('inventario.index') }}" class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold transition shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Volver</span>
        </a>
    </div>

    @if(session('error'))
    <div class="px-4 py-3 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-300 text-xs text-rose-800 dark:text-rose-300">{{ session('error') }}</div>
    @endif
    @if($errors->any())
    <div class="px-4 py-3 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-300 text-xs text-rose-800 dark:text-rose-300">
        <ul class="list-disc list-inside space-y-0.5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <form method="POST" action="{{ route('inventario.ajustar.store') }}">
    @csrf

    {{-- Panel principal --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-6 space-y-5">

        {{-- Combobox lote --}}
        <div>
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                Lote a Ajustar <span class="text-rose-500">*</span>
                <span class="text-xs font-normal text-slate-400 ml-1">Busca por numero de lote o nombre del medicamento</span>
            </label>
            <input type="hidden" name="lote_id" :value="loteId">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input
                    type="text"
                    x-model="loteQuery"
                    @focus="filtrar()"
                    @input="filtrar()"
                    @keydown.escape="showDropdown=false"
                    :readonly="loteId !== null"
                    placeholder="Buscar lote o medicamento..."
                    class="w-full pl-10 pr-10 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition"
                    :class="loteId ? 'bg-indigo-50 dark:bg-indigo-950/30 border-indigo-300 dark:border-indigo-700 cursor-default' : ''"
                >
                <div x-show="loteId" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                    <button type="button" @click="deseleccionar()" class="text-slate-400 hover:text-rose-500 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div
                    x-show="showDropdown && !loteId"
                    @click.outside="showDropdown=false"
                    class="absolute z-50 w-full mt-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-lg max-h-60 overflow-y-auto"
                >
                    <template x-for="l in lotesFiltrados" :key="l.id">
                        <button type="button" @click="seleccionar(l)" class="w-full text-left px-4 py-2.5 text-sm hover:bg-slate-50 dark:hover:bg-slate-700 transition border-b border-slate-100 dark:border-slate-700/50 last:border-0">
                            <div class="flex items-center justify-between gap-2">
                                <div>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200" x-text="l.producto"></span>
                                    <span class="text-xs text-slate-400 font-mono ml-2" x-text="'Lote: ' + l.numero_lote"></span>
                                </div>
                                <div class="text-right shrink-0">
                                    <span class="text-sm font-bold text-slate-700 dark:text-slate-300" x-text="l.stock_actual + ' un.'"></span>
                                    <span x-show="l.vencido" class="ml-1 text-[10px] font-bold text-rose-500">VENCIDO</span>
                                    <p class="text-[10px] text-slate-400" x-text="'Vence: ' + l.fecha_vencimiento"></p>
                                </div>
                            </div>
                        </button>
                    </template>
                    <div x-show="lotesFiltrados.length === 0" class="px-4 py-3 text-sm text-slate-400 text-center">Sin resultados</div>
                </div>
            </div>
        </div>

        {{-- Info del lote seleccionado --}}
        <div x-show="loteSeleccionado" class="p-4 rounded-xl bg-indigo-50 dark:bg-indigo-950/30 border border-indigo-200 dark:border-indigo-800 space-y-2">
            <p class="text-xs font-bold uppercase text-indigo-600 dark:text-indigo-400 tracking-wider">Lote Seleccionado</p>
            <div class="grid grid-cols-3 gap-4 text-sm">
                <div>
                    <p class="text-xs text-slate-400">Medicamento</p>
                    <p class="font-semibold text-slate-800 dark:text-slate-200" x-text="loteSeleccionado?.producto"></p>
                </div>
                <div>
                    <p class="text-xs text-slate-400">Stock Actual</p>
                    <p class="font-bold text-indigo-700 dark:text-indigo-300" x-text="loteSeleccionado?.stock_actual + ' unidades'"></p>
                </div>
                <div>
                    <p class="text-xs text-slate-400">Vencimiento</p>
                    <p class="font-semibold" :class="loteSeleccionado?.vencido ? 'text-rose-600' : 'text-slate-700 dark:text-slate-300'" x-text="loteSeleccionado?.fecha_vencimiento"></p>
                </div>
            </div>
        </div>

        {{-- Stock nuevo + diferencia --}}
        <div x-show="loteSeleccionado">
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                Nuevo Stock <span class="text-rose-500">*</span>
                <span class="text-xs font-normal text-slate-400 ml-1">Valor absoluto final del stock (no la diferencia)</span>
            </label>
            <div class="flex items-center gap-3">
                <input
                    type="number"
                    name="stock_nuevo"
                    x-model="stockNuevo"
                    min="0"
                    required
                    placeholder="0"
                    class="w-40 px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-mono font-bold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition"
                    :class="parseInt(stockNuevo) < 0 ? 'border-rose-500' : ''"
                >
                <div x-show="diferencia !== null" class="flex items-center space-x-2">
                    <svg x-show="diferencia > 0" class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                    <svg x-show="diferencia < 0" class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                    <span class="text-base font-extrabold" :class="diferenciaClass" x-text="diferenciaLabel"></span>
                </div>
            </div>
            <div x-show="diferencia === 0 && stockNuevo !== ''" class="mt-1.5 text-xs text-amber-600 dark:text-amber-400 font-semibold">
                El stock nuevo es identico al actual. No se registrara ningun movimiento.
            </div>
        </div>

        {{-- Subtipo --}}
        <div x-show="loteSeleccionado">
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Motivo del Ajuste <span class="text-rose-500">*</span></label>
            <select name="subtipo" x-model="subtipo" required class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition">
                <option value="ajuste_manual">Ajuste Manual (General)</option>
                <option value="ajuste_positivo">Ajuste Positivo (Conteo fisico mayor)</option>
                <option value="ajuste_negativo">Ajuste Negativo (Conteo fisico menor)</option>
                <option value="merma_danio">Merma por Danio o Deterioro</option>
                <option value="merma_vencimiento">Merma por Vencimiento</option>
            </select>
        </div>

        {{-- Descripcion --}}
        <div x-show="loteSeleccionado">
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Descripcion del Ajuste <span class="text-rose-500">*</span></label>
            <textarea
                name="motivo"
                x-model="motivo"
                rows="2"
                required
                minlength="5"
                maxlength="255"
                placeholder="Ej: Conteo fisico del 18/09/2026. Se encontraron 3 cajas danadas..."
                class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition resize-none"
            ></textarea>
            <p class="text-xs text-slate-400 mt-1">Minimo 5 caracteres. Se guardara en el historial Kardex.</p>
        </div>

        {{-- Warning lote vencido --}}
        <div x-show="loteSeleccionado && loteSeleccionado.vencido" class="flex items-start gap-2 px-4 py-3 rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-300 dark:border-amber-700 text-xs text-amber-800 dark:text-amber-300">
            <svg class="w-4 h-4 shrink-0 mt-0.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <span><strong>Lote vencido.</strong> Se recomienda registrar el ajuste con subtipo "Merma por Vencimiento" y colocar el nuevo stock en <strong>0</strong> para dar de baja correctamente.</span>
        </div>
    </div>

    {{-- Footer --}}
    <div class="flex items-center justify-between gap-3 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm px-6 py-4">
        <button type="button" @click="limpiar()" class="px-5 py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-xl transition">Limpiar</button>
        <button
            type="submit"
            :disabled="!loteId || stockNuevo === '' || diferencia === 0 || motivo.length < 5"
            class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white text-sm font-semibold rounded-xl shadow-sm transition inline-flex items-center space-x-2 disabled:opacity-40 disabled:cursor-not-allowed"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span>Registrar Ajuste en Kardex</span>
        </button>
    </div>
    </form>

    {{-- Info box --}}
    <div class="px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 text-xs text-slate-500 dark:text-slate-400 space-y-1">
        <p><strong class="text-slate-700 dark:text-slate-300">Como funciona:</strong> Este ajuste actualiza el stock del lote de forma atomica con bloqueo de concurrencia.</p>
        <p>Cada cambio genera un registro permanente en el Kardex con: stock anterior, stock nuevo, diferencia, costo valorado, subtipo y usuario.</p>
        <p>El ajuste NO se puede eliminar — es parte del historial de auditoria. Solo se puede corregir con otro ajuste.</p>
    </div>
</div>
@endsection
