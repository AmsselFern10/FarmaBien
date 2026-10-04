<header x-show="!posFullscreen" class="h-14 bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border-b border-slate-200 dark:border-slate-800 flex items-center justify-between px-3 sm:px-6 z-20 shrink-0 select-none transition-colors">
    <!-- Left: Mobile Menu Trigger & Tabs Bar -->
    <div class="flex items-center flex-1 min-w-0 mr-3">
        <!-- Mobile Sidebar Toggle -->
        <button @click="mobileSidebarOpen = !mobileSidebarOpen" 
                type="button" 
                class="md:hidden p-1.5 mr-2 rounded-lg text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>

        <!-- Dynamic Multitask Tabs (Integrated in Navbar) -->
        <div x-data="farmaNavbarTabs()" class="flex items-center flex-1 min-w-0 overflow-hidden">
            <div class="flex items-center space-x-1 overflow-x-auto scrollbar-none py-1 flex-1 min-w-0">
                <template x-for="(tab, idx) in tabs" :key="tab.id || tab.url">
                    <div class="group inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-lg text-xs font-medium transition-all shrink-0 border cursor-pointer select-none"
                         :class="isTabActive(tab)
                             ? 'bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-white border-slate-300 dark:border-slate-700 shadow-xs font-semibold'
                             : 'bg-transparent text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/50 border-transparent'"
                         @click="navigateToTab(tab)">
                        
                        <!-- Dot Indicator: Amber if dirty draft, Emerald if active, Slate otherwise -->
                        <span class="w-1.5 h-1.5 rounded-full shrink-0"
                              :class="hasDirtyDraft(tab) 
                                  ? 'bg-amber-500 animate-pulse' 
                                  : (isTabActive(tab) ? 'bg-emerald-500' : 'bg-slate-300 dark:bg-slate-600')"></span>

                        <!-- Tab Title -->
                        <span x-text="tab.title" class="truncate max-w-[100px] sm:max-w-[130px] lg:max-w-[160px] whitespace-nowrap"></span>

                        <!-- Dirty Draft Indicator -->
                        <template x-if="hasDirtyDraft(tab)">
                            <span title="Tiene cambios sin guardar" class="text-amber-500 inline-flex items-center">
                                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                            </span>
                        </template>

                        <!-- Close Tab Button (Hidden on pinned dashboard) -->
                        <template x-if="!tab.pinned">
                            <button type="button"
                                    @click="closeTab(idx, $event)"
                                    class="w-4 h-4 ml-0.5 rounded-full inline-flex items-center justify-center text-slate-400 hover:text-rose-500 hover:bg-slate-200/80 dark:hover:bg-slate-700 transition"
                                    title="Cerrar pestaña">
                                &times;
                            </button>
                        </template>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- Right: Actions & Theme Toggle (Fixed & Bounded - Never Overlapped) -->
    <div class="flex items-center space-x-2 shrink-0 pl-2">
        <!-- Date / Shift info -->
        <div class="hidden md:flex items-center space-x-1.5 px-2.5 py-1.5 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-medium text-slate-600 dark:text-slate-300 shrink-0">
            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            <span>{{ now()->translatedFormat('d M, Y') }}</span>
        </div>

        <!-- Búsqueda Global Ctrl+K -->
        <button @click="window.dispatchEvent(new KeyboardEvent('keydown', { ctrlKey: true, key: 'k', bubbles: true }))"
                type="button"
                class="hidden sm:inline-flex items-center space-x-2 px-3 py-1.5 rounded-lg text-xs font-medium text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white bg-slate-100 hover:bg-white dark:bg-slate-800 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 transition shrink-0"
                title="Búsqueda global (Ctrl+K)">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <span class="hidden lg:inline">Buscar</span>
            <kbd class="px-1 py-0.5 text-[9px] font-mono bg-slate-200 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded text-slate-500 dark:text-slate-400">Ctrl+K</kbd>
        </button>

        <!-- Centro de Notificaciones (Campana) -->
        <div x-data="farmaCentroNotificaciones()" x-init="init()" class="relative shrink-0">
            <button @click="toggleOpen()" 
                    type="button" 
                    class="relative p-2 rounded-lg text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 transition"
                    :title="notificaciones.total_count > 0 ? notificaciones.total_count + ' alertas pendientes' : 'Sin notificaciones pendientes'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                
                <!-- Badge de Contador -->
                <template x-if="notificaciones.total_count > 0">
                    <span class="absolute -top-1 -right-1 flex h-4 min-w-[16px] px-1 items-center justify-center rounded-full bg-rose-600 text-[10px] font-black text-white shadow-xs animate-pulse"
                          x-text="notificaciones.total_count > 99 ? '99+' : notificaciones.total_count"></span>
                </template>
            </button>

            <!-- Dropdown Panel -->
            <div x-show="abierto" 
                 x-cloak
                 @click.outside="abierto = false"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                 x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                 class="absolute right-0 mt-2 w-80 sm:w-96 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xl z-50 overflow-hidden select-text">
                
                <!-- Dropdown Header -->
                <div class="px-4 py-3 bg-slate-50/80 dark:bg-slate-800/80 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <span class="font-bold text-xs text-slate-900 dark:text-white">Centro de Alertas</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] font-black bg-rose-100 text-rose-700 dark:bg-rose-900/60 dark:text-rose-300"
                              x-text="notificaciones.total_count"></span>
                    </div>
                    <button type="button" @click="cargarNotificaciones()" class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 hover:underline">
                        Actualizar
                    </button>
                </div>

                <!-- Filter Tabs -->
                <div class="flex items-center px-2 py-1.5 border-b border-slate-100 dark:border-slate-800 space-x-1 overflow-x-auto scrollbar-none text-[11px]">
                    <button type="button" @click="tabActivo = 'todos'"
                            :class="tabActivo === 'todos' ? 'bg-slate-200 dark:bg-slate-700 text-slate-900 dark:text-white font-bold' : 'text-slate-500 hover:text-slate-800 dark:text-slate-400'"
                            class="px-2 py-0.5 rounded-lg whitespace-nowrap transition">
                        Todos (<span x-text="notificaciones.total_count"></span>)
                    </button>
                    <button type="button" @click="tabActivo = 'stock'"
                            :class="tabActivo === 'stock' ? 'bg-slate-200 dark:bg-slate-700 text-slate-900 dark:text-white font-bold' : 'text-slate-500 hover:text-slate-800 dark:text-slate-400'"
                            class="px-2 py-0.5 rounded-lg whitespace-nowrap transition">
                        Stock (<span x-text="notificaciones.stock_count || 0"></span>)
                    </button>
                    <button type="button" @click="tabActivo = 'vencimientos'"
                            :class="tabActivo === 'vencimientos' ? 'bg-slate-200 dark:bg-slate-700 text-slate-900 dark:text-white font-bold' : 'text-slate-500 hover:text-slate-800 dark:text-slate-400'"
                            class="px-2 py-0.5 rounded-lg whitespace-nowrap transition">
                        Vencimientos (<span x-text="notificaciones.vencimientos_count || 0"></span>)
                    </button>
                    <button type="button" @click="tabActivo = 'cuentas_pagar'"
                            :class="tabActivo === 'cuentas_pagar' ? 'bg-slate-200 dark:bg-slate-700 text-slate-900 dark:text-white font-bold' : 'text-slate-500 hover:text-slate-800 dark:text-slate-400'"
                            class="px-2 py-0.5 rounded-lg whitespace-nowrap transition">
                        CxP (<span x-text="notificaciones.cuentas_pagar_count || 0"></span>)
                    </button>
                </div>

                <!-- Notifications List -->
                <div class="max-h-72 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800">
                    <!-- Loading state -->
                    <div x-show="cargando" class="p-6 text-center text-xs text-slate-400">
                        Cargando alertas del sistema...
                    </div>

                    <!-- Items -->
                    <template x-for="item in itemsFiltrados()" :key="item.id">
                        <a :href="item.url" class="flex items-start p-3 hover:bg-slate-50 dark:hover:bg-slate-800/60 transition group">
                            <!-- Urgency Dot / Icon -->
                            <div class="w-7 h-7 rounded-xl flex items-center justify-center shrink-0 mr-2.5"
                                 :class="item.urgencia === 'critica' ? 'bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400' : (item.urgencia === 'alta' ? 'bg-amber-100 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400' : 'bg-blue-100 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400')">
                                <span class="text-xs font-bold" x-text="item.urgencia === 'critica' ? '!' : (item.urgencia === 'alta' ? '!' : 'i')"></span>
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <p class="text-xs font-bold text-slate-900 dark:text-white truncate group-hover:text-emerald-600 dark:group-hover:text-emerald-400" x-text="item.titulo"></p>
                                </div>
                                <p class="text-[11px] text-slate-600 dark:text-slate-300 mt-0.5 line-clamp-2" x-text="item.mensaje"></p>
                            </div>
                        </a>
                    </template>

                    <!-- Empty state -->
                    <div x-show="!cargando && itemsFiltrados().length === 0" class="p-6 text-center text-xs text-slate-400">
                        No hay alertas pendientes en esta categoría.
                    </div>
                </div>

                <!-- Footer Quick Links -->
                <div class="px-4 py-2.5 bg-slate-50/50 dark:bg-slate-800/40 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-[11px]">
                    <a href="{{ route('inventario.alertas') }}" class="font-semibold text-slate-600 dark:text-slate-300 hover:text-emerald-600">
                        Monitor de Inventario
                    </a>
                    <a href="{{ route('compras.sugerencias-reorden') }}" class="font-semibold text-emerald-600 dark:text-emerald-400 hover:underline">
                        Reorden Sugerido &rarr;
                    </a>
                </div>
            </div>
        </div>

        <!-- Keyboard Shortcuts Trigger -->
        <button @click="showShortcutsModal = true" 
                type="button" 
                class="inline-flex items-center space-x-1.5 px-2.5 py-1.5 rounded-lg text-xs font-semibold text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 transition shrink-0"
                title="Ver atajos de teclado (F1)">
            <span>⌨️</span>
            <span class="hidden lg:inline">Atajos</span>
        </button>

        <!-- Dark / Light Mode Switch -->
        <button @click="toggleDarkMode()" 
                type="button" 
                class="p-2 rounded-lg text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 transition shrink-0"
                :title="darkMode ? 'Cambiar a Modo Claro' : 'Cambiar a Modo Oscuro'">
            <!-- Sun Icon (for dark mode) -->
            <svg x-show="darkMode" class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            <!-- Moon Icon (for light mode) -->
            <svg x-show="!darkMode" class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
        </button>
    </div>
</header>

<script>
function farmaNavbarTabs() {
    return {
        tabs: [],
        navigating: false,
        currentUrl: window.location.pathname + window.location.search,
        currentPath: window.location.pathname,
        currentTitle: '{{ trim($__env->yieldContent('title', 'FarmaBien')) }}'.replace(' - FarmaBien', '').trim() || 'Dashboard',

        navigateToTab(tab) {
            if (this.isTabActive(tab)) return;
            window.farmaNavigate
                ? window.farmaNavigate(tab.url)
                : (window.location.href = tab.url);
        },

        init() {
            try {
                const saved = localStorage.getItem('farma_open_tabs');
                if (saved) {
                    this.tabs = JSON.parse(saved);
                }
            } catch (e) {
                this.tabs = [];
            }

            // Ensure Dashboard is always pinned at index 0
            const dashboardUrl = '{{ route('dashboard', [], false) }}';
            const hasDashboard = this.tabs.some(t => t.url === dashboardUrl || t.url === '/dashboard');
            if (!hasDashboard) {
                this.tabs.unshift({
                    id: 'dashboard',
                    title: 'Dashboard',
                    url: dashboardUrl,
                    pinned: true,
                    openedAt: 0,
                    lastVisited: 0
                });
            } else {
                const dashIndex = this.tabs.findIndex(t => t.url === dashboardUrl || t.url === '/dashboard');
                if (dashIndex > 0) {
                    const dash = this.tabs.splice(dashIndex, 1)[0];
                    dash.pinned = true;
                    this.tabs.unshift(dash);
                } else if (dashIndex === 0) {
                    this.tabs[0].pinned = true;
                }
            }

            // Register current view if not Dashboard
            if (this.currentPath !== dashboardUrl && this.currentPath !== '/dashboard' && this.currentPath !== '/') {
                const existingIndex = this.tabs.findIndex(t => t.url.split('?')[0] === this.currentPath);
                
                if (existingIndex >= 0) {
                    this.tabs[existingIndex].url = this.currentUrl;
                    this.tabs[existingIndex].title = this.currentTitle;
                    this.tabs[existingIndex].lastVisited = Date.now();
                } else {
                    // FIFO: Maximum 5 dynamic tabs allowed (Total max tabs = 1 + 5 = 6)
                    const dynamicTabs = this.tabs.filter(t => !t.pinned);
                    
                    if (dynamicTabs.length >= 5) {
                        // Find candidate to close: oldest visited that does NOT have a dirty draft
                        const sortedCandidates = [...dynamicTabs].sort((a, b) => (a.lastVisited || a.openedAt || 0) - (b.lastVisited || b.openedAt || 0));
                        let tabToEvict = null;

                        for (const cand of sortedCandidates) {
                            const isDirty = window.farmaHasDirtyDraft ? window.farmaHasDirtyDraft(cand.url) : false;
                            if (!isDirty) {
                                tabToEvict = cand;
                                break;
                            }
                        }

                        // Fallback to oldest if all have drafts
                        if (!tabToEvict) {
                            tabToEvict = sortedCandidates[0];
                        }

                        this.tabs = this.tabs.filter(t => t.url !== tabToEvict.url);
                    }

                    this.tabs.push({
                        id: this.currentPath,
                        title: this.currentTitle,
                        url: this.currentUrl,
                        pinned: false,
                        openedAt: Date.now(),
                        lastVisited: Date.now()
                    });
                }
            }

            this.saveTabs();

            // Reactive listener for draft changes across views
            window.addEventListener('farma:draft-changed', () => {
                this.tabs = [...this.tabs];
            });
        },

        saveTabs() {
            try {
                localStorage.setItem('farma_open_tabs', JSON.stringify(this.tabs));
            } catch (e) {}
        },

        isTabActive(tab) {
            return this.currentPath === tab.url.split('?')[0];
        },

        hasDirtyDraft(tab) {
            return window.farmaHasDirtyDraft ? window.farmaHasDirtyDraft(tab.url) : false;
        },


        async closeTab(index, event) {
            event.stopPropagation();
            event.preventDefault();

            const closed = this.tabs[index];
            if (!closed || closed.pinned) return;

            if (this.hasDirtyDraft(closed)) {
                const ok = await window.farmaConfirm({
                    title: 'Cambios sin guardar',
                    body: `La pestaña "${closed.title}" tiene cambios que no se han guardado. ¿Deseas cerrarla y descartar el borrador?`,
                    type: 'warning',
                    ok: 'Sí, descartar y cerrar'
                });
                if (!ok) return;
                sessionStorage.removeItem('farma_draft:' + closed.url.split('?')[0]);
                try {
                    let dirtyList = JSON.parse(sessionStorage.getItem('farma_dirty_drafts') || '[]');
                    dirtyList = dirtyList.filter(p => p !== closed.url.split('?')[0]);
                    sessionStorage.setItem('farma_dirty_drafts', JSON.stringify(dirtyList));
                } catch(e) {}
            }

            const isActive = this.isTabActive(closed);
            this.tabs.splice(index, 1);
            this.saveTabs();

            if (isActive) {
                const nextTab = this.tabs[Math.max(0, index - 1)] || this.tabs[0];
                window.farmaNavigate
                    ? window.farmaNavigate(nextTab.url)
                    : (window.location.href = nextTab.url);
            }
        }
    };
}

function farmaCentroNotificaciones() {
    return {
        abierto: false,
        cargando: false,
        tabActivo: 'todos',
        notificaciones: {
            total_count: 0,
            stock: [],
            stock_count: 0,
            vencimientos: [],
            vencimientos_count: 0,
            cuentas_pagar: [],
            cuentas_pagar_count: 0,
            reorden: [],
            reorden_count: 0
        },

        init() {
            this.cargarNotificaciones();
            // Polling cada 1 hora — la BD ya cachea 30s; no tiene sentido saturar
            // con peticiones cada 45s cuando el stock farmacéutico cambia cada horas.
            setInterval(() => {
                this.cargarNotificaciones(true);
            }, 3_600_000);
        },

        toggleOpen() {
            this.abierto = !this.abierto;
            if (this.abierto) {
                this.cargarNotificaciones();
            }
        },

        async cargarNotificaciones(silencioso = false) {
            if (!silencioso) this.cargando = true;
            try {
                const res = await fetch('/api/notificaciones/resumen', {
                    headers: { 'Accept': 'application/json' }
                });
                if (res.ok) {
                    this.notificaciones = await res.json();
                }
            } catch (e) {
                console.error('Error al cargar notificaciones:', e);
            } finally {
                this.cargando = false;
            }
        },

        itemsFiltrados() {
            if (this.tabActivo === 'stock') {
                return this.notificaciones.stock || [];
            }
            if (this.tabActivo === 'vencimientos') {
                return this.notificaciones.vencimientos || [];
            }
            if (this.tabActivo === 'cuentas_pagar') {
                return this.notificaciones.cuentas_pagar || [];
            }
            // 'todos'
            const all = [
                ...(this.notificaciones.stock || []),
                ...(this.notificaciones.vencimientos || []),
                ...(this.notificaciones.cuentas_pagar || []),
                ...(this.notificaciones.reorden || [])
            ];
            // Ordenar: críticas primero
            return all.sort((a, b) => {
                const orden = { 'critica': 1, 'alta': 2, 'media': 3, 'baja': 4 };
                return (orden[a.urgencia] || 5) - (orden[b.urgencia] || 5);
            });
        }
    };
}
</script>

