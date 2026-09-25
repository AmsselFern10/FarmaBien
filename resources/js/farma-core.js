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
// 1. TOP PROGRESS BAR & NAVIGATION GUARD (Anti-Bounce & Instant Feedback)
// ==========================================
class FarmaProgressBar {
    constructor() {
        this.bar = null;
        this.timer = null;
        this.isNavigating = false;
        this.navTimeout = null;
        this.lastClickedUrl = null;
        this.lastClickedTime = 0;
        this.init();
    }

    init() {
        if (document.getElementById('farma-progress-bar')) {
            this.bar = document.getElementById('farma-progress-bar');
            return;
        }

        const bar = document.createElement('div');
        bar.id = 'farma-progress-bar';
        bar.className = 'fixed top-0 left-0 h-[3px] z-[9999] bg-gradient-to-r from-emerald-500 via-teal-400 to-emerald-400 shadow-[0_0_10px_rgba(16,185,129,0.9)] transition-all duration-200 pointer-events-none opacity-0';
        bar.style.width = '0%';
        document.body.appendChild(bar);
        this.bar = bar;

        // Global delegated link navigation with anti-bounce protection
        document.addEventListener('click', (e) => {
            if (e.defaultPrevented) return;
            const link = e.target.closest('a');
            if (!link || !link.href) return;

            const target = link.getAttribute('target');
            if (target === '_blank') return;
            
            const rawHref = link.getAttribute('href') || '';
            if (rawHref === '#' || rawHref.startsWith('#') || rawHref.startsWith('javascript:')) return;
            if (link.pathname === window.location.pathname && link.search === window.location.search && link.hash) return;

            const now = Date.now();
            const currentOrigin = window.location.origin;

            if (link.href.startsWith(currentOrigin)) {
                // Anti-bounce debounce: prevent rapid duplicate clicks to identical URL within 400ms
                if (this.isNavigating && this.lastClickedUrl === link.href && (now - this.lastClickedTime < 400)) {
                    e.preventDefault();
                    return;
                }

                this.lastClickedUrl = link.href;
                this.lastClickedTime = now;
                this.start();
            }
        }, true);

        window.addEventListener('beforeunload', () => {
            this.progressTo(95, 100);
        });

        window.addEventListener('load', () => {
            this.finish();
        });

        window.addEventListener('pageshow', (event) => {
            if (event.persisted) {
                this.finish();
            }
        });
    }

    start() {
        if (!this.bar) return;
        this.isNavigating = true;

        clearTimeout(this.navTimeout);
        this.navTimeout = setTimeout(() => {
            this.isNavigating = false;
        }, 3000);

        this.bar.style.transition = 'width 250ms ease-out, opacity 100ms ease-in';
        this.bar.style.opacity = '1';
        this.bar.style.width = '35%';

        clearTimeout(this.timer);
        this.timer = setTimeout(() => {
            if (this.bar && this.isNavigating) this.bar.style.width = '75%';
        }, 120);
    }

    progressTo(percent, duration = 200) {
        if (!this.bar) return;
        this.bar.style.transition = `width ${duration}ms ease-out`;
        this.bar.style.width = percent + '%';
    }

    finish() {
        this.isNavigating = false;
        clearTimeout(this.navTimeout);
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
// STRICT LOGOUT CLEANUP SYSTEM
// ==========================================
window.farmaPerformStrictLogoutCleanup = function() {
    try {
        console.log('[FarmaCore] Realizando purga estricta de almacenamiento por cierre de sesión...');
        const savedTheme = localStorage.getItem('farma_theme');

        // 1. Borrar todos los borradores y estados volátiles
        sessionStorage.clear();

        // 2. Borrar pestañas abiertas, caches y registros locales
        localStorage.clear();

        // 3. Restaurar preferencia de tema si existía
        if (savedTheme) {
            localStorage.setItem('farma_theme', savedTheme);
        }
    } catch (e) {
        console.error('[FarmaCore] Error limpiando almacenamiento en logout:', e);
    }
};

// Global interceptors for logout forms & links
document.addEventListener('submit', (e) => {
    const f = e.target;
    const action = (f.getAttribute('action') || '').toLowerCase();
    if (action.includes('/logout')) {
        window.farmaPerformStrictLogoutCleanup();
    }
}, true);

document.addEventListener('click', (e) => {
    const btn = e.target.closest('button, a');
    if (!btn) return;
    const form = btn.closest('form');
    if (form && (form.getAttribute('action') || '').toLowerCase().includes('/logout')) {
        window.farmaPerformStrictLogoutCleanup();
    }
    const href = (btn.getAttribute('href') || '').toLowerCase();
    if (href.includes('/logout')) {
        window.farmaPerformStrictLogoutCleanup();
    }
}, true);

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

// ==========================================
// 4. ENTERPRISE TOAST NOTIFICATION ENGINE & HTTP INTERCEPTOR
// ==========================================
class FarmaToastEngine {
    constructor() {
        this.container = null;
        this.init();
    }

    init() {
        if (typeof document === 'undefined') return;
        if (document.getElementById('farma-toast-container')) {
            this.container = document.getElementById('farma-toast-container');
            return;
        }

        const c = document.createElement('div');
        c.id = 'farma-toast-container';
        c.className = 'fixed top-4 right-4 z-[99999] flex flex-col gap-2.5 max-w-md w-full pointer-events-none px-3 sm:px-0';
        document.body.appendChild(c);
        this.container = c;

        // Global Event Listener
        window.addEventListener('farma:notify', (e) => {
            const { type, message, title, duration } = e.detail || {};
            this.show(type || 'info', message || '', title || null, duration || 4500);
        });
    }

    show(type, message, title = null, duration = 4500) {
        if (!this.container) this.init();
        if (!this.container) return;

        const toast = document.createElement('div');
        toast.className = 'pointer-events-auto transform transition-all duration-300 ease-out translate-y-[-10px] opacity-0 shadow-xl rounded-2xl p-3.5 border flex items-start space-x-3 text-xs backdrop-blur-md cursor-pointer';

        let bgClass, borderClass, iconSvg, titleText, titleColor, descColor;

        switch (type) {
            case 'success':
                bgClass = 'bg-emerald-50/95 dark:bg-emerald-950/90';
                borderClass = 'border-emerald-300 dark:border-emerald-700/80';
                titleColor = 'text-emerald-900 dark:text-emerald-200';
                descColor = 'text-emerald-700 dark:text-emerald-300';
                titleText = title || 'Operación Exitosa';
                iconSvg = `<svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>`;
                break;
            case 'warning':
                bgClass = 'bg-amber-50/95 dark:bg-amber-950/90';
                borderClass = 'border-amber-300 dark:border-amber-700/80';
                titleColor = 'text-amber-900 dark:text-amber-200';
                descColor = 'text-amber-800 dark:text-amber-300';
                titleText = title || 'Validación Requerida';
                iconSvg = `<svg class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>`;
                break;
            case 'error':
                bgClass = 'bg-rose-50/95 dark:bg-rose-950/90';
                borderClass = 'border-rose-300 dark:border-rose-700/80';
                titleColor = 'text-rose-900 dark:text-rose-200';
                descColor = 'text-rose-800 dark:text-rose-300';
                titleText = title || 'Error en la Solicitud';
                iconSvg = `<svg class="w-4 h-4 text-rose-600 dark:text-rose-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>`;
                break;
            default: // info
                bgClass = 'bg-indigo-50/95 dark:bg-indigo-950/90';
                borderClass = 'border-indigo-300 dark:border-indigo-700/80';
                titleColor = 'text-indigo-900 dark:text-indigo-200';
                descColor = 'text-indigo-800 dark:text-indigo-300';
                titleText = title || 'Información del Sistema';
                iconSvg = `<svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`;
                break;
        }

        toast.className += ` ${bgClass} ${borderClass}`;
        toast.innerHTML = `
            <div class="mt-0.5">${iconSvg}</div>
            <div class="flex-1 min-w-0">
                <div class="font-bold ${titleColor} text-[12px] leading-tight">${titleText}</div>
                <div class="${descColor} text-[11px] mt-0.5 leading-relaxed break-words">${message}</div>
            </div>
            <button type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 ml-1 p-0.5 rounded-md transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        `;

        const closeBtn = toast.querySelector('button');
        const dismiss = () => {
            toast.classList.remove('opacity-100', 'translate-y-0');
            toast.classList.add('opacity-0', 'translate-y-[-10px]');
            setTimeout(() => toast.remove(), 250);
        };

        if (closeBtn) {
            closeBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                dismiss();
            });
        }
        toast.addEventListener('click', dismiss);

        this.container.appendChild(toast);

        // Animate entrance
        requestAnimationFrame(() => {
            toast.classList.remove('opacity-0', 'translate-y-[-10px]');
            toast.classList.add('opacity-100', 'translate-y-0');
        });

        // Auto dismiss
        let autoDismissTimer = setTimeout(dismiss, duration);
        toast.addEventListener('mouseenter', () => clearTimeout(autoDismissTimer));
        toast.addEventListener('mouseleave', () => {
            autoDismissTimer = setTimeout(dismiss, 2000);
        });
    }

    success(msg, title = null, duration = 4500) { this.show('success', msg, title, duration); }
    warning(msg, title = null, duration = 5500) { this.show('warning', msg, title, duration); }
    error(msg, title = null, duration = 6500) { this.show('error', msg, title, duration); }
    info(msg, title = null, duration = 4500) { this.show('info', msg, title, duration); }
}

// Global Interceptor Setup
function setupGlobalHttpInterceptors() {
    if (window.axios) {
        window.axios.interceptors.response.use(
            response => response,
            error => {
                if (error.response) {
                    const status = error.response.status;
                    const data = error.response.data || {};

                    if (status === 422) {
                        let formattedMsg = '';
                        if (data.errors && typeof data.errors === 'object') {
                            const messages = [];
                            Object.values(data.errors).forEach(errList => {
                                if (Array.isArray(errList)) {
                                    messages.push(...errList);
                                } else if (typeof errList === 'string') {
                                    messages.push(errList);
                                }
                            });
                            formattedMsg = messages.join(' ');
                        }
                        if (!formattedMsg) {
                            formattedMsg = data.message || 'Por favor verifica los campos requeridos.';
                        }
                        if (window.farmaToast) {
                            window.farmaToast.warning(formattedMsg, 'Validación Requerida');
                        }
                    } else if (status === 403) {
                        if (window.farmaToast) {
                            window.farmaToast.error(data.message || 'No tienes permisos suficientes para realizar esta acción.', 'Acceso Denegado');
                        }
                    } else if (status === 419) {
                        if (window.farmaToast) {
                            window.farmaToast.error('La sesión ha expirado por inactividad. Por favor recarga la página.', 'Sesión Expirada');
                        }
                    } else if (status >= 500) {
                        if (window.farmaToast) {
                            window.farmaToast.error(data.message || 'Ocurrió un inconveniente al procesar la solicitud en el servidor.', 'Error del Sistema');
                        }
                    }
                }
                return Promise.reject(error);
            }
        );
    }
}

// Global helper
window.farmaNotify = function(message, type = 'info', title = null) {
    if (window.farmaToast) {
        window.farmaToast.show(type, message, title);
    }
};

// Instantiate systems
window.farmaProgressBar = new FarmaProgressBar();
window.farmaDraftEngine = new FarmaDraftEngine();
window.farmaToast = new FarmaToastEngine();
setupGlobalHttpInterceptors();

