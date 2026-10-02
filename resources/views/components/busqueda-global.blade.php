{{-- ============================================================
     Búsqueda Global — Modal Ctrl+K
     Incluido en layouts/app.blade.php
     ============================================================ --}}
<div
    x-data="farmaGlobalSearch()"
    x-on:keydown.window="onGlobalKey($event)"
    x-cloak>

    {{-- Overlay + Modal --}}
    <template x-teleport="body">
        <div x-show="open"
             x-cloak
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[10000] flex items-start justify-center pt-[10vh] px-4 overflow-y-auto"
             @click.self="close()">

            {{-- Backdrop --}}
            <div class="fixed inset-0 bg-slate-950/75 backdrop-blur-sm"></div>

        {{-- Panel --}}
        <div x-show="open"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
             class="relative w-full max-w-2xl bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 overflow-hidden z-10">

            {{-- Search Input --}}
            <div class="flex items-center border-b border-slate-200 dark:border-slate-700 px-4">
                <svg class="w-5 h-5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input
                    x-ref="searchInput"
                    x-model.debounce.250ms="query"
                    @input="search()"
                    @keydown.arrow-down.prevent="moveDown()"
                    @keydown.arrow-up.prevent="moveUp()"
                    @keydown.enter.prevent="goToSelected()"
                    @keydown.escape="close()"
                    type="text"
                    placeholder="Buscar medicamento, cliente o venta…"
                    class="flex-1 py-4 px-3 bg-transparent text-sm text-slate-800 dark:text-slate-100 placeholder-slate-400 outline-none border-none focus:ring-0"/>
                <kbd class="hidden sm:inline-flex items-center px-1.5 py-0.5 text-[10px] font-mono bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-600 rounded">Esc</kbd>
            </div>

            {{-- Results --}}
            <div class="max-h-[60vh] overflow-y-auto" x-ref="resultsList">

                {{-- Loading --}}
                <div x-show="loading" class="flex items-center justify-center py-8 text-slate-400">
                    <svg class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                    </svg>
                    <span class="text-sm">Buscando…</span>
                </div>

                {{-- Empty --}}
                <div x-show="!loading && query.length >= 2 && totalResults === 0"
                     class="py-12 text-center">
                    <svg class="w-10 h-10 text-slate-300 dark:text-slate-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm text-slate-400">Sin resultados para "<span x-text="query" class="font-medium"></span>"</p>
                </div>

                {{-- Initial hint --}}
                <div x-show="!loading && query.length < 2"
                     class="px-4 py-6">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-3">Accesos rápidos</p>
                    <div class="grid grid-cols-3 gap-2">
                        <a href="{{ route('productos.index') }}" @click="close()" class="flex flex-col items-center p-3 rounded-xl bg-slate-50 dark:bg-slate-800 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 transition group">
                            <svg class="w-6 h-6 text-slate-400 group-hover:text-emerald-500 mb-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                            <span class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">Medicamentos</span>
                        </a>
                        <a href="{{ route('clientes.index') }}" @click="close()" class="flex flex-col items-center p-3 rounded-xl bg-slate-50 dark:bg-slate-800 hover:bg-blue-50 dark:hover:bg-blue-900/30 transition group">
                            <svg class="w-6 h-6 text-slate-400 group-hover:text-blue-500 mb-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">Clientes</span>
                        </a>
                        <a href="{{ route('ventas.index') }}" @click="close()" class="flex flex-col items-center p-3 rounded-xl bg-slate-50 dark:bg-slate-800 hover:bg-purple-50 dark:hover:bg-purple-900/30 transition group">
                            <svg class="w-6 h-6 text-slate-400 group-hover:text-purple-500 mb-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                            <span class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">Ventas</span>
                        </a>
                    </div>
                </div>

                {{-- Results list --}}
                <div x-show="!loading && totalResults > 0" class="py-2">

                    {{-- Medicamentos --}}
                    <template x-if="results.productos && results.productos.length > 0">
                        <div>
                            <div class="px-4 py-1.5">
                                <span class="text-[10px] font-semibold uppercase tracking-widest text-slate-400">Medicamentos</span>
                            </div>
                            <template x-for="(item, i) in results.productos" :key="'p'+i">
                                <a :href="item.url"
                                   @click="close()"
                                   :data-idx="flatIndex(0, i)"
                                   :class="selectedIdx === flatIndex(0, i) ? 'bg-emerald-50 dark:bg-emerald-900/30' : 'hover:bg-slate-50 dark:hover:bg-slate-800/60'"
                                   class="flex items-center gap-3 px-4 py-2.5 cursor-pointer transition-colors group">
                                    <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-900/40 flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-slate-800 dark:text-slate-100 truncate" x-text="item.nombre"></p>
                                        <p class="text-xs text-slate-400 truncate" x-text="item.subtitulo"></p>
                                    </div>
                                    <div class="flex items-center gap-2 shrink-0">
                                        <template x-if="item.es_controlado || item.tipo_control === 'controlado'">
                                            <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300 uppercase tracking-wide">
                                                Controlado
                                            </span>
                                        </template>
                                        <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400">C$ <span x-text="item.precio"></span></span>
                                    </div>
                                </a>
                            </template>
                        </div>
                    </template>

                    {{-- Clientes --}}
                    <template x-if="results.clientes && results.clientes.length > 0">
                        <div>
                            <div class="px-4 py-1.5 mt-1">
                                <span class="text-[10px] font-semibold uppercase tracking-widest text-slate-400">Clientes / Pacientes</span>
                            </div>
                            <template x-for="(item, i) in results.clientes" :key="'c'+i">
                                <a :href="item.url"
                                   @click="close()"
                                   :data-idx="flatIndex(1, i)"
                                   :class="selectedIdx === flatIndex(1, i) ? 'bg-blue-50 dark:bg-blue-900/30' : 'hover:bg-slate-50 dark:hover:bg-slate-800/60'"
                                   class="flex items-center gap-3 px-4 py-2.5 cursor-pointer transition-colors">
                                    <div class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-slate-800 dark:text-slate-100 truncate" x-text="item.nombre"></p>
                                        <p class="text-xs text-slate-400 truncate" x-text="item.subtitulo"></p>
                                    </div>
                                    <template x-if="!item.activo">
                                        <span class="text-[9px] bg-slate-100 dark:bg-slate-800 text-slate-400 px-1.5 py-0.5 rounded font-medium">Inactivo</span>
                                    </template>
                                </a>
                            </template>
                        </div>
                    </template>

                    {{-- Ventas --}}
                    <template x-if="results.ventas && results.ventas.length > 0">
                        <div>
                            <div class="px-4 py-1.5 mt-1">
                                <span class="text-[10px] font-semibold uppercase tracking-widest text-slate-400">Ventas</span>
                            </div>
                            <template x-for="(item, i) in results.ventas" :key="'v'+i">
                                <a :href="item.url"
                                   @click="close()"
                                   :data-idx="flatIndex(2, i)"
                                   :class="selectedIdx === flatIndex(2, i) ? 'bg-purple-50 dark:bg-purple-900/30' : 'hover:bg-slate-50 dark:hover:bg-slate-800/60'"
                                   class="flex items-center gap-3 px-4 py-2.5 cursor-pointer transition-colors">
                                    <div class="w-8 h-8 rounded-lg bg-purple-100 dark:bg-purple-900/40 flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-slate-800 dark:text-slate-100 truncate" x-text="item.nombre"></p>
                                        <p class="text-xs text-slate-400 truncate" x-text="item.subtitulo"></p>
                                    </div>
                                    <span :class="{
                                        'bg-emerald-100 text-emerald-700': item.estado === 'completada',
                                        'bg-amber-100 text-amber-700': item.estado === 'pendiente',
                                        'bg-rose-100 text-rose-700': item.estado === 'anulada',
                                    }" class="text-[9px] font-semibold px-1.5 py-0.5 rounded capitalize" x-text="item.estado"></span>
                                </a>
                            </template>
                        </div>
                    </template>

                </div>
            </div>

            {{-- Footer hint --}}
            <div class="border-t border-slate-100 dark:border-slate-800 px-4 py-2 flex items-center gap-4 text-[10px] text-slate-400">
                <span><kbd class="font-mono bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded px-1">↑↓</kbd> Navegar</span>
                <span><kbd class="font-mono bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded px-1">Enter</kbd> Abrir</span>
                <span><kbd class="font-mono bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded px-1">Esc</kbd> Cerrar</span>
                <span class="ml-auto">Ctrl+K para abrir en cualquier módulo</span>
            </div>
        </div>
    </div>
    </template>
</div>

<script>
function farmaGlobalSearch() {
    return {
        open: false,
        query: '',
        loading: false,
        results: { productos: [], clientes: [], ventas: [] },
        selectedIdx: -1,
        abortCtrl: null,

        get totalResults() {
            return (this.results.productos?.length || 0)
                 + (this.results.clientes?.length || 0)
                 + (this.results.ventas?.length || 0);
        },

        get flatItems() {
            return [
                ...(this.results.productos || []).map((r, i) => ({ ...r, _idx: this.flatIndex(0, i) })),
                ...(this.results.clientes || []).map((r, i) => ({ ...r, _idx: this.flatIndex(1, i) })),
                ...(this.results.ventas || []).map((r, i) => ({ ...r, _idx: this.flatIndex(2, i) })),
            ];
        },

        flatIndex(group, i) {
            const offsets = [
                0,
                (this.results.productos?.length || 0),
                (this.results.productos?.length || 0) + (this.results.clientes?.length || 0),
            ];
            return offsets[group] + i;
        },

        openModal() {
            this.open = true;
            this.$nextTick(() => this.$refs.searchInput?.focus());
        },

        close() {
            this.open = false;
            this.query = '';
            this.results = { productos: [], clientes: [], ventas: [] };
            this.selectedIdx = -1;
        },

        onGlobalKey(e) {
            // Ctrl+K o Cmd+K
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                this.open ? this.close() : this.openModal();
            }
        },

        async search() {
            if (this.query.length < 2) {
                this.results = { productos: [], clientes: [], ventas: [] };
                this.selectedIdx = -1;
                return;
            }

            // Cancela petición anterior
            if (this.abortCtrl) this.abortCtrl.abort();
            this.abortCtrl = new AbortController();
            this.loading = true;

            try {
                const res = await fetch(
                    `{{ route('busqueda.global') }}?q=${encodeURIComponent(this.query)}`,
                    {
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                        signal: this.abortCtrl.signal,
                    }
                );
                if (!res.ok) throw new Error('Error ' + res.status);
                this.results = await res.json();
                this.selectedIdx = this.totalResults > 0 ? 0 : -1;
            } catch (err) {
                if (err.name !== 'AbortError') {
                    this.results = { productos: [], clientes: [], ventas: [] };
                }
            } finally {
                this.loading = false;
            }
        },

        moveDown() {
            if (this.totalResults === 0) return;
            this.selectedIdx = (this.selectedIdx + 1) % this.totalResults;
            this.scrollToSelected();
        },

        moveUp() {
            if (this.totalResults === 0) return;
            this.selectedIdx = this.selectedIdx <= 0 ? this.totalResults - 1 : this.selectedIdx - 1;
            this.scrollToSelected();
        },

        scrollToSelected() {
            this.$nextTick(() => {
                const el = this.$refs.resultsList?.querySelector(`[data-idx="${this.selectedIdx}"]`);
                if (el) el.scrollIntoView({ block: 'nearest' });
            });
        },

        goToSelected() {
            const item = this.flatItems[this.selectedIdx];
            if (item?.url) {
                this.close();
                window.farmaNavigate ? window.farmaNavigate(item.url) : (window.location.href = item.url);
            }
        },
    };
}
</script>
