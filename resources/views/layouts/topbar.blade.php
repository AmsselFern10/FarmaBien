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
                            <span title="Tiene cambios sin guardar" class="text-[10px] leading-none text-amber-500">✏️</span>
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
            <span>📅 {{ now()->translatedFormat('d M, Y') }}</span>
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
            if (this.isTabActive(tab) || this.navigating) return;
            this.navigating = true;
            if (window.farmaNavigate) { window.farmaNavigate(tab.url); } else { window.location.href = tab.url; }
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

        closeTab(index, event) {
            event.stopPropagation();
            event.preventDefault();

            const closed = this.tabs[index];
            if (!closed || closed.pinned) return;

            if (this.hasDirtyDraft(closed)) {
                if (!confirm(`La pestaña "${closed.title}" tiene cambios sin guardar. ¿Deseas cerrarla y descartar el borrador?`)) {
                    return;
                }
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
                if (window.farmaNavigate) { window.farmaNavigate(nextTab.url); } else { window.location.href = nextTab.url; }
            }
        }
    };
}
</script>

