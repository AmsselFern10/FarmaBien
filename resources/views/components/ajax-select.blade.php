@props([
    'name' => 'item_id',
    'endpoint' => '',
    'placeholder' => 'Buscar...',
    'type' => 'generic', // 'medicamento', 'proveedor', 'laboratorio', 'categoria', 'generic'
    'multiple' => false,
    'value' => null,
    'initialItem' => null,
    'required' => false,
    'disabled' => false,
    'id' => null,
])

@php
    $componentId = $id ?? 'ajax_select_' . str_replace(['[', ']', '.'], '_', $name) . '_' . uniqid();
@endphp

<div x-data="{
    open: false,
    query: '',
    loading: false,
    results: [],
    highlightedIndex: -1,
    multiple: @js((bool)$multiple),
    selectedSingle: @js($initialItem ?: ($value ? ['id' => $value, 'nombre' => 'Cargando...'] : null)),
    selectedMultiple: @js($initialItem && is_array($initialItem) ? $initialItem : []),
    timer: null,

    init() {
        if (this.selectedSingle && !this.selectedSingle.nombre && this.selectedSingle.id) {
            // Cargar datos del ítem si solo vino el ID
            this.cargarItemPorId(this.selectedSingle.id);
        }
    },

    buscar() {
        clearTimeout(this.timer);
        if (this.query.trim().length < (this.multiple ? 0 : 2) && '{{ $type }}' !== 'laboratorio' && '{{ $type }}' !== 'categoria') {
            if (this.query.trim().length === 0) {
                this.results = [];
                this.open = false;
            }
            return;
        }

        this.loading = true;
        this.open = true;
        this.highlightedIndex = -1;

        this.timer = setTimeout(() => {
            fetch(`{{ $endpoint }}?q=${encodeURIComponent(this.query.trim())}`)
                .then(res => res.json())
                .then(data => {
                    this.results = Array.isArray(data) ? data : [];
                    this.loading = false;
                })
                .catch(err => {
                    console.error('Error en búsqueda AJAX:', err);
                    this.results = [];
                    this.loading = false;
                });
        }, 280);
    },

    abrirDropdown() {
        if ('{{ $type }}' === 'laboratorio' || '{{ $type }}' === 'categoria' || this.query.length >= 2) {
            this.buscar();
        } else {
            this.open = true;
        }
    },

    seleccionar(item) {
        if (this.multiple) {
            if (!this.selectedMultiple.some(i => i.id == item.id)) {
                this.selectedMultiple.push(item);
                this.$dispatch('ajax-select-change', { name: '{{ $name }}', items: this.selectedMultiple });
            }
            this.query = '';
            this.results = [];
            this.open = false;
        } else {
            this.selectedSingle = item;
            this.query = '';
            this.open = false;
            this.$dispatch('ajax-select-change', { name: '{{ $name }}', item: item });
        }
    },

    removerItem(idx) {
        if (this.multiple) {
            this.selectedMultiple.splice(idx, 1);
            this.$dispatch('ajax-select-change', { name: '{{ $name }}', items: this.selectedMultiple });
        } else {
            this.selectedSingle = null;
            this.query = '';
            this.$dispatch('ajax-select-change', { name: '{{ $name }}', item: null });
            this.$nextTick(() => {
                const input = $refs.searchInput;
                if (input) input.focus();
            });
        }
    },

    moverHighlight(step) {
        if (!this.open || this.results.length === 0) return;
        this.highlightedIndex = (this.highlightedIndex + step + this.results.length) % this.results.length;
        this.scrollIntoView();
    },

    scrollIntoView() {
        this.$nextTick(() => {
            const list = $refs.dropdownList;
            if (!list) return;
            const active = list.children[this.highlightedIndex];
            if (active) {
                active.scrollIntoView({ block: 'nearest' });
            }
        });
    },

    seleccionarHighlight() {
        if (this.highlightedIndex >= 0 && this.highlightedIndex < this.results.length) {
            this.seleccionar(this.results[this.highlightedIndex]);
        }
    },

    cargarItemPorId(id) {
        fetch(`{{ $endpoint }}?q=${encodeURIComponent(id)}`)
            .then(res => res.json())
            .then(data => {
                if (Array.isArray(data)) {
                    const match = data.find(i => i.id == id) || data[0];
                    if (match) this.selectedSingle = match;
                }
            })
            .catch(() => {});
    }
}" 
id="{{ $componentId }}" 
class="relative w-full"
@click.outside="open = false"
@keydown.escape="open = false">

    {{-- MODO SELECCIÓN ÚNICA (Medicamento / Proveedor) --}}
    @if(!$multiple)
        <template x-if="selectedSingle">
            <div class="flex items-center justify-between px-3 py-2 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-300 dark:border-emerald-700 rounded-xl shadow-2xs transition animate-fadeIn">
                <div class="flex items-center space-x-2.5 overflow-hidden">
                    <span class="w-2 h-2 rounded-full bg-emerald-600 shrink-0"></span>
                    <div class="truncate">
                        <span class="text-xs font-bold text-emerald-950 dark:text-emerald-200 block truncate" x-text="selectedSingle.nombre"></span>
                        <div class="text-[10px] text-emerald-800 dark:text-emerald-400 flex items-center space-x-2 font-medium">
                            <span x-text="selectedSingle.principio_activo || selectedSingle.contacto || ''"></span>
                            <span x-show="selectedSingle.laboratorio" class="opacity-60">&bull;</span>
                            <span x-show="selectedSingle.laboratorio" x-text="selectedSingle.laboratorio"></span>
                            <span x-show="selectedSingle.codigo_barras || selectedSingle.ruc" class="font-mono text-[9px] opacity-75 font-semibold" x-text="selectedSingle.codigo_barras ? 'Cód: ' + selectedSingle.codigo_barras : (selectedSingle.ruc || '')"></span>
                        </div>
                    </div>
                </div>
                
                @if(!$disabled)
                <button type="button" 
                        @click="removerItem()" 
                        class="w-6 h-6 rounded-lg flex items-center justify-center text-emerald-700 hover:text-rose-600 dark:text-emerald-400 dark:hover:text-rose-400 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 transition cursor-pointer shrink-0 ml-2"
                        title="Cambiar selección">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
                @endif
                
                <input type="hidden" name="{{ $name }}" :value="selectedSingle.id" @if($required) required @endif>
            </div>
        </template>

        <template x-if="!selectedSingle">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text"
                       x-ref="searchInput"
                       x-model="query"
                       @input="buscar()"
                       @focus="abrirDropdown()"
                       @keydown.down.prevent="moverHighlight(1)"
                       @keydown.up.prevent="moverHighlight(-1)"
                       @keydown.enter.prevent="seleccionarHighlight()"
                       placeholder="{{ $placeholder }}"
                       @if($disabled) disabled @endif
                       class="w-full pl-10 pr-4 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition shadow-2xs">
                
                {{-- Spinner loading --}}
                <div x-show="loading" class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none">
                    <svg class="w-3.5 h-3.5 animate-spin text-emerald-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                </div>

                <input type="hidden" name="{{ $name }}" value="" @if($required) required @endif>
            </div>
        </template>

    {{-- MODO SELECCIÓN MÚLTIPLE CON CHIPS (Laboratorio / Categoría) --}}
    @else
        <div class="p-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl shadow-2xs space-y-1.5 focus-within:ring-2 focus-within:ring-emerald-500/30 focus-within:border-emerald-500 transition">
            <!-- Chips seleccionados -->
            <div class="flex flex-wrap gap-1" x-show="selectedMultiple.length > 0">
                <template x-for="(item, idx) in selectedMultiple" :key="item.id">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-300 dark:border-emerald-700 text-xs font-bold text-emerald-950 dark:text-emerald-200">
                        <span x-text="item.nombre"></span>
                        <button type="button" @click.stop="removerItem(idx)" class="text-emerald-700 hover:text-rose-600 dark:text-emerald-400 dark:hover:text-rose-400 transition cursor-pointer">
                            &times;
                        </button>
                        <input type="hidden" name="{{ $name }}[]" :value="item.id">
                    </span>
                </template>
            </div>

            <!-- Input de búsqueda / añadir -->
            <div class="relative flex items-center">
                <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text"
                       x-ref="searchInput"
                       x-model="query"
                       @input="buscar()"
                       @focus="abrirDropdown()"
                       @keydown.down.prevent="moverHighlight(1)"
                       @keydown.up.prevent="moverHighlight(-1)"
                       @keydown.enter.prevent="seleccionarHighlight()"
                       placeholder="{{ $placeholder }}"
                       class="w-full pl-8 pr-3 py-1 bg-transparent border-0 text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-0">
                
                <div x-show="loading" class="absolute inset-y-0 right-0 pr-2 flex items-center pointer-events-none">
                    <svg class="w-3 h-3 animate-spin text-emerald-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                </div>
            </div>
        </div>
    @endif

    {{-- LISTA FLOTANTE DE RESULTADOS (Dropdown) --}}
    <div x-show="open" 
         x-cloak
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="opacity-0 translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-1"
         class="absolute z-50 left-0 right-0 mt-1 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl shadow-xl max-h-64 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800">
        
        <div x-ref="dropdownList" class="p-1 space-y-0.5">
            <!-- Loading message -->
            <div x-show="loading && results.length === 0" class="px-3 py-3 text-center text-xs text-slate-500 dark:text-slate-400 font-medium">
                Buscando resultados...
            </div>

            <!-- Empty message -->
            <div x-show="!loading && results.length === 0 && query.length >= 2" class="px-3 py-3 text-center text-xs text-slate-500 dark:text-slate-400 font-medium">
                Sin resultados para "<span x-text="query" class="font-bold"></span>"
            </div>

            <!-- Items -->
            <template x-for="(item, idx) in results" :key="item.id">
                <div @click="seleccionar(item)"
                     @mouseenter="highlightedIndex = idx"
                     :class="highlightedIndex === idx ? 'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-950 dark:text-white' : 'text-slate-800 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800'"
                     class="px-3 py-2 rounded-lg cursor-pointer transition flex items-center justify-between text-xs gap-2">
                    
                    <div class="min-w-0 flex-1">
                        <!-- Título Principal en Negro/Oscuro -->
                        <div class="font-bold text-slate-900 dark:text-white truncate" x-text="item.nombre"></div>
                        
                        <!-- Secundario en text-slate-700 -->
                        <div class="text-[10px] text-slate-700 dark:text-slate-400 flex items-center space-x-1.5 truncate mt-0.5" x-show="item.principio_activo || item.contacto || item.laboratorio">
                            <span x-text="item.principio_activo || item.contacto || ''"></span>
                            <span x-show="item.laboratorio" class="opacity-50">&bull;</span>
                            <span x-show="item.laboratorio" x-text="item.laboratorio"></span>
                        </div>
                    </div>

                    <!-- Código a la derecha en monoespaciado text-slate-700 -->
                    <div class="text-right shrink-0">
                        <span x-show="item.codigo_barras && item.codigo_barras !== 'S/C'" class="text-[10px] font-mono text-slate-700 dark:text-slate-400 block" x-text="'Cód: ' + item.codigo_barras"></span>
                        <span x-show="item.ruc" class="text-[10px] font-mono text-slate-700 dark:text-slate-400 block" x-text="item.ruc"></span>
                        <span x-show="item.precio_venta" class="text-[10px] font-bold text-emerald-900 dark:text-emerald-400 block" x-text="'C$ ' + parseFloat(item.precio_venta).toFixed(2)"></span>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>
