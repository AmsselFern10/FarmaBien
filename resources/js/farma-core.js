/**
 * FarmaBien Core UX Suite
 * 1. Top Loading Progress Bar (Instant Navigation Feedback)
 * 2. Robust Form Draft Persistence (Debounced Auto-save & Instant Restore)
 * 3. Non-intrusive Universal Autofocus
 */

// ==========================================
// GLOBAL DRAFT REPOSITORY HELPERS
// ==========================================
window.farmaGetDraft = function(path, defaults = {}) {
    try {
        const clean = (path || window.location.pathname).replace(/\/+$/, '') || '/';
        const key = 'farma_draft:' + clean;
        const saved = sessionStorage.getItem(key) || localStorage.getItem(key);
        if (saved) {
            const parsed = JSON.parse(saved);
            return Object.assign({}, defaults, parsed);
        }
    } catch (e) {
        console.error('[FarmaDraftEngine] Error leyendo borrador:', e);
    }
    return defaults;
};

window.farmaSaveDraft = function(path, data) {
    try {
        const clean = (path || window.location.pathname).replace(/\/+$/, '') || '/';
        const key = 'farma_draft:' + clean;
        const json = JSON.stringify(data);
        sessionStorage.setItem(key, json);
        localStorage.setItem(key, json);

        let dirtyList = JSON.parse(sessionStorage.getItem('farma_dirty_drafts') || '[]');
        if (!dirtyList.includes(clean)) {
            dirtyList.push(clean);
            sessionStorage.setItem('farma_dirty_drafts', JSON.stringify(dirtyList));
        }
        window.dispatchEvent(new CustomEvent('farma:draft-changed', { detail: { path: clean, dirty: true } }));
    } catch (e) {
        console.error('[FarmaDraftEngine] Error guardando borrador:', e);
    }
};

window.farmaClearDraft = function(path) {
    try {
        const clean = (path || window.location.pathname).replace(/\/+$/, '') || '/';
        const key = 'farma_draft:' + clean;
        sessionStorage.removeItem(key);
        localStorage.removeItem(key);

        let dirtyList = JSON.parse(sessionStorage.getItem('farma_dirty_drafts') || '[]');
        dirtyList = dirtyList.filter(p => p !== clean);
        sessionStorage.setItem('farma_dirty_drafts', JSON.stringify(dirtyList));

        window.dispatchEvent(new CustomEvent('farma:draft-changed', { detail: { path: clean, dirty: false } }));

        const indicator = document.getElementById('farma-draft-indicator');
        if (indicator) indicator.remove();
    } catch (e) {
        console.error('[FarmaDraftEngine] Error limpiando borrador:', e);
    }
};

window.farmaHasDirtyDraft = function(url) {
    try {
        const cleanPath = (url || window.location.pathname).split('?')[0].replace(/\/+$/, '') || '/';
        const dirtyList = JSON.parse(sessionStorage.getItem('farma_dirty_drafts') || '[]');
        return dirtyList.includes(cleanPath);
    } catch (e) {
        return false;
    }
};

// ==========================================
// 1. TOP PROGRESS BAR (Instant Navigation)
// ==========================================
class FarmaProgressBar {
    constructor() {
        this.bar = null;
        this.timer = null;
        this.init();
    }

    init() {
        if (document.getElementById('farma-progress-bar')) {
            this.bar = document.getElementById('farma-progress-bar');
            return;
        }

        const bar = document.createElement('div');
        bar.id = 'farma-progress-bar';
        bar.className = 'fixed top-0 left-0 h-[2.5px] z-[9999] bg-gradient-to-r from-emerald-500 via-teal-400 to-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.8)] transition-all duration-200 pointer-events-none opacity-0';
        bar.style.width = '0%';
        document.body.appendChild(bar);
        this.bar = bar;

        // Listen to genuine link navigation
        document.addEventListener('click', (e) => {
            if (e.defaultPrevented) return;
            const link = e.target.closest('a');
            if (!link || !link.href) return;

            const target = link.getAttribute('target');
            if (target === '_blank') return;
            
            const rawHref = link.getAttribute('href') || '';
            if (rawHref === '#' || rawHref.startsWith('#') || rawHref.startsWith('javascript:')) return;
            if (link.pathname === window.location.pathname && link.search === window.location.search && link.hash) return;

            const currentOrigin = window.location.origin;
            if (link.href.startsWith(currentOrigin)) {
                this.start();
            }
        });

        window.addEventListener('beforeunload', () => {
            this.progressTo(95, 100);
        });

        window.addEventListener('load', () => {
            this.finish();
        });
    }

    start() {
        if (!this.bar) return;
        this.bar.style.transition = 'width 250ms ease-out, opacity 100ms ease-in';
        this.bar.style.opacity = '1';
        this.bar.style.width = '30%';

        clearTimeout(this.timer);
        this.timer = setTimeout(() => {
            if (this.bar) this.bar.style.width = '75%';
        }, 150);
    }

    progressTo(percent, duration = 200) {
        if (!this.bar) return;
        this.bar.style.transition = `width ${duration}ms ease-out`;
        this.bar.style.width = percent + '%';
    }

    finish() {
        if (!this.bar) return;
        this.bar.style.transition = 'width 100ms ease-out, opacity 150ms ease-in 100ms';
        this.bar.style.width = '100%';
        setTimeout(() => {
            if (this.bar) {
                this.bar.style.opacity = '0';
                setTimeout(() => {
                    if (this.bar) this.bar.style.width = '0%';
                }, 200);
            }
        }, 120);
    }
}

// ==========================================
// 2. FORM DRAFT PERSISTENCE ENGINE (DEBOUNCED)
// ==========================================
class FarmaDraftEngine {
    constructor() {
        this.cleanPath = window.location.pathname.replace(/\/+$/, '') || '/';
        this.storageKey = 'farma_draft:' + this.cleanPath;
        this.isFormPage = this.cleanPath.includes('/create') || this.cleanPath.includes('/edit');
        this.form = null;
        this.dirty = false;
        this.isRestoring = false;
        this.hasRestored = false;
        this.saveTimer = null;

        this.boot();
    }

    boot() {
        const initRun = () => {
            this.findForm();
            if (this.isFormPage && !this.hasRestored) {
                this.restoreDraft();
            }
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initRun);
        } else {
            initRun();
        }

        // Alpine ready hook
        document.addEventListener('alpine:initialized', () => {
            this.findForm();
            if (this.isFormPage && !this.hasRestored) {
                this.restoreDraft();
            }
        });

        // Re-apply when layout toggles (Modern <-> Compact) - No notification banner
        window.addEventListener('farma:layout-changed', () => {
            this.findForm();
            if (this.isFormPage) {
                this.restoreDraft(false);
            }
        });

        // Global delegated input listeners (Debounced to prevent CPU spikes)
        document.addEventListener('input', (e) => this.handleGlobalInput(e), { passive: true });
        document.addEventListener('change', (e) => this.handleGlobalInput(e), { passive: true });

        // On submit, remove draft cleanly
        document.addEventListener('submit', (e) => {
            const f = e.target;
            const action = (f.getAttribute('action') || '').toLowerCase();
            if (!action.includes('/logout') && !action.includes('/anular') && !action.includes('/delete')) {
                this.clearDraft();
            }
        });
    }

    findForm() {
        const forms = document.querySelectorAll('form');
        for (const f of forms) {
            const action = (f.getAttribute('action') || '').toLowerCase();
            if (action.includes('/logout') || action.includes('/anular') || action.includes('/login') || action.includes('/register')) {
                continue;
            }

            const inputs = f.querySelectorAll('input:not([type="hidden"]), textarea, select');
            if (inputs.length >= 1) {
                this.form = f;
                break;
            }
        }
    }

    handleGlobalInput(e) {
        if (this.isRestoring || !this.isFormPage) return;
        const target = e.target;
        if (!target || !target.name || target.name === '_token' || target.name === '_method' || target.type === 'password' || target.type === 'file') return;

        this.dirty = true;
        clearTimeout(this.saveTimer);
        this.saveTimer = setTimeout(() => {
            this.saveDraft();
        }, 250);
    }

    saveDraft() {
        if (this.isRestoring || !this.isFormPage) return;

        const formData = {};
        let hasValues = false;

        // 1. Extract from Alpine reactive scope if available
        if (window.Alpine) {
            try {
                const alpineRoots = document.querySelectorAll('[x-data]');
                for (const root of alpineRoots) {
                    const data = window.Alpine.$data(root);
                    if (data && data.formData && typeof data.formData === 'object') {
                        Object.assign(formData, data.formData);
                        for (const k in data.formData) {
                            const v = data.formData[k];
                            if (v !== null && v !== undefined && v !== '' && v !== false) {
                                hasValues = true;
                            }
                        }
                    }
                }
            } catch (e) {}
        }

        // 2. Complement with DOM inputs
        const elements = document.querySelectorAll('input, select, textarea');
        elements.forEach(el => {
            const name = el.name;
            if (!name || name === '_token' || name === '_method' || el.type === 'password' || el.type === 'file') return;

            if (el.type === 'checkbox') {
                formData[name] = el.checked;
                if (el.checked) hasValues = true;
            } else if (el.type === 'radio') {
                if (el.checked) {
                    formData[name] = el.value;
                    hasValues = true;
                }
            } else {
                formData[name] = el.value;
                if (el.value && el.value.trim().length > 0) {
                    hasValues = true;
                }
            }
        });

        if (hasValues) {
            window.farmaSaveDraft(this.cleanPath, formData);
        }
    }

    restoreDraft(showNotification = true) {
        try {
            const saved = sessionStorage.getItem(this.storageKey) || localStorage.getItem(this.storageKey);
            if (!saved) return;

            const formData = JSON.parse(saved);
            let restoredCount = 0;
            this.isRestoring = true;

            // 1. Sync with Alpine state
            if (window.Alpine) {
                try {
                    const alpineRoots = document.querySelectorAll('[x-data]');
                    for (const root of alpineRoots) {
                        const data = window.Alpine.$data(root);
                        if (data && data.formData && typeof data.formData === 'object') {
                            Object.keys(formData).forEach(k => {
                                if (formData[k] !== undefined && formData[k] !== null) {
                                    data.formData[k] = formData[k];
                                    restoredCount++;
                                }
                            });
                        }
                    }
                } catch (e) {}
            }

            // 2. Populate DOM inputs directly
            Object.keys(formData).forEach(name => {
                const elements = document.querySelectorAll(`[name="${name}"]`);
                elements.forEach(el => {
                    const val = formData[name];
                    if (el.type === 'checkbox') {
                        el.checked = !!val;
                        restoredCount++;
                    } else if (el.type === 'radio') {
                        if (el.value === val) {
                            el.checked = true;
                            restoredCount++;
                        }
                    } else {
                        if (val !== undefined && val !== null) {
                            el.value = val;
                            restoredCount++;
                        }
                    }
                });
            });

            this.isRestoring = false;
            this.hasRestored = true;

            if (restoredCount > 0 && showNotification) {
                this.dirty = true;
                this.showDraftIndicator(true);
            }
        } catch (e) {
            this.isRestoring = false;
            console.error('[FarmaDraftEngine] Error al restaurar borrador:', e);
        }
    }

    clearDraft() {
        window.farmaClearDraft(this.cleanPath);
        this.dirty = false;
    }

    showDraftIndicator(show) {
        let indicator = document.getElementById('farma-draft-indicator');
        if (!show) {
            if (indicator) indicator.remove();
            return;
        }

        if (!this.form) this.findForm();
        const targetContainer = this.form || document.querySelector('main');
        if (!indicator && targetContainer) {
            indicator = document.createElement('div');
            indicator.id = 'farma-draft-indicator';
            indicator.className = 'mb-4 px-4 py-3 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-300/80 dark:border-emerald-700/60 flex flex-wrap items-center justify-between gap-3 shadow-xs text-xs animate-in fade-in duration-200';
            indicator.innerHTML = `
                <div class="flex items-center space-x-2.5">
                    <div class="w-7 h-7 rounded-xl bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                    </div>
                    <div>
                        <div class="font-bold text-emerald-900 dark:text-emerald-200">Borrador recuperado automáticamente</div>
                        <div class="text-[11px] text-emerald-700 dark:text-emerald-400">Los datos que estabas escribiendo se mantuvieron preservados.</div>
                    </div>
                </div>
                <div class="flex items-center space-x-2 shrink-0">
                    <button type="button" id="btn-discard-draft" class="px-3 py-1.5 rounded-xl bg-white dark:bg-slate-800 border border-emerald-300 dark:border-emerald-700 text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition font-semibold flex items-center space-x-1 shadow-2xs cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        <span>Descartar borrador</span>
                    </button>
                </div>
            `;
            targetContainer.prepend(indicator);

            document.getElementById('btn-discard-draft')?.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                if (confirm('¿Deseas descartar los datos del borrador y reiniciar el formulario?')) {
                    this.clearDraft();
                    window.location.reload();
                }
            });
        }
    }
}

// ==========================================
// 3. UNIVERSAL AUTOFOCUS ENGINE (NON-INTRUSIVE)
// ==========================================
window.farmaTriggerAutoFocus = function() {
    const isFormPage = window.location.pathname.includes('/create') || window.location.pathname.includes('/edit');
    if (!isFormPage) return;

    // Do not steal focus if the user already clicked/focused an element
    if (document.activeElement && document.activeElement !== document.body && document.activeElement !== document.documentElement) {
        return;
    }

    setTimeout(() => {
        const activeContainer = document.querySelector('form');
        if (!activeContainer) return;

        const inputs = Array.from(activeContainer.querySelectorAll('input:not([type="hidden"]):not([type="checkbox"]):not([type="radio"]):not([type="file"]):not([disabled]):not([readonly]), select:not([disabled]), textarea:not([disabled])'));
        
        const visibleInput = inputs.find(el => el.offsetParent !== null && !el.closest('[style*="display: none"]'));
        if (visibleInput && (!document.activeElement || document.activeElement === document.body)) {
            visibleInput.focus();
        }
    }, 80);
};

// Global focus listeners
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => window.farmaTriggerAutoFocus());
} else {
    window.farmaTriggerAutoFocus();
}
document.addEventListener('alpine:initialized', () => window.farmaTriggerAutoFocus());
window.addEventListener('farma:layout-changed', () => window.farmaTriggerAutoFocus());

// Instantiate systems
window.farmaProgressBar = new FarmaProgressBar();
window.farmaDraftEngine = new FarmaDraftEngine();
