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
        this.bar          = null;
        this.timer        = null;
        this.navLock      = false;       // true desde el click hasta que la nueva página carga
        this.navTarget    = null;        // URL hacia donde se está navegando
        this.navLockTimer = null;        // fallback: libera el lock si la navegación no ocurre
        this.init();
    }

    init() {
        if (document.getElementById('farma-progress-bar')) {
            this.bar = document.getElementById('farma-progress-bar');
        } else {
            const bar = document.createElement('div');
            bar.id = 'farma-progress-bar';
            bar.className = 'fixed top-0 left-0 h-[3px] z-[9999] bg-gradient-to-r from-emerald-500 via-teal-400 to-emerald-400 shadow-[0_0_10px_rgba(16,185,129,0.9)] pointer-events-none opacity-0';
            bar.style.cssText = 'width:0%;transition:none';
            document.body.appendChild(bar);
            this.bar = bar;
        }

        // --- window.farmaNavigate: función global para navegación con transición ---
        // Todos los elementos del sistema (tabs, sidebar, atajos) deben usarla.
        window.farmaNavigate = (url) => {
            if (!url) return;
            const dest = String(url);

            // Mismo origen: anti-rebote — ignora clic duplicado al mismo destino
            try {
                const destUrl = new URL(dest, window.location.origin);
                const curPath = window.location.pathname + window.location.search;
                const destPath = destUrl.pathname + destUrl.search;

                if (destPath === curPath) return;               // ya estamos aquí
                if (this.navLock && this.navTarget === dest) return; // ya navegando al mismo destino
            } catch (_) {}

            this.navLock   = true;
            this.navTarget = dest;

            // Inicia barra de progreso
            this.start();

            // Animación de salida del contenido (~80ms) antes de redirigir
            const main = document.querySelector('main.page-fade-in, main');
            if (main) {
                main.classList.add('page-navigating-out');
            }

            // Redirige tras el fade-out. requestAnimationFrame garantiza que el
            // frame de salida se pintó antes de que el navegador empiece a cargar.
            setTimeout(() => {
                window.location.href = dest;
            }, 90);

            // Fallback: si tras 8s no hubo unload, libera el lock
            clearTimeout(this.navLockTimer);
            this.navLockTimer = setTimeout(() => {
                this.navLock   = false;
                this.navTarget = null;
                if (main) main.classList.remove('page-navigating-out');
                this.finish();
            }, 8000);
        };

        // --- Click delegado: intercepta <a> normales y activa farmaNavigate ---
        document.addEventListener('click', (e) => {
            if (e.defaultPrevented || e.ctrlKey || e.metaKey || e.shiftKey || e.altKey) return;
            const link = e.target.closest('a[href]');
            if (!link) return;

            const target = link.getAttribute('target');
            if (target === '_blank' || target === '_parent' || target === '_top') return;

            const rawHref = link.getAttribute('href') || '';
            if (!rawHref || rawHref === '#' || rawHref.startsWith('#') || rawHref.startsWith('javascript:') || rawHref.startsWith('mailto:') || rawHref.startsWith('tel:')) return;

            // Solo misma origin
            try {
                const destUrl = new URL(link.href, window.location.origin);
                if (destUrl.origin !== window.location.origin) return;

                // Enlace con hash al mismo path (scroll interno)
                if (destUrl.pathname === window.location.pathname && destUrl.hash) return;
            } catch (_) { return; }

            e.preventDefault();
            window.farmaNavigate(link.href);
        }, true);

        // --- beforeunload: barra al 95% cuando el browser confirma la salida ---
        window.addEventListener('beforeunload', () => {
            this.progressTo(95, 80);
        });

        // --- load: completa la barra al llegar la nueva página ---
        window.addEventListener('load', () => {
            this.finish();
        });

        // --- pageshow: maneja BFCache (página restaurada desde caché del browser) ---
        window.addEventListener('pageshow', (event) => {
            // event.persisted = true → página viene del BFCache
            this.navLock   = false;
            this.navTarget = null;
            clearTimeout(this.navLockTimer);

            if (event.persisted) {
                // Fuerza re-animación de entrada para que no parezca "congelada"
                const main = document.querySelector('main');
                if (main) {
                    main.classList.remove('page-navigating-out', 'page-fade-in');
                    void main.offsetHeight; // reflow para reiniciar animación
                    main.classList.add('page-fade-in');
                }
                this.finish();
            }
        });
    }

    start() {
        if (!this.bar) return;
        // Reset instantáneo sin transición
        this.bar.style.transition = 'none';
        this.bar.style.width      = '0%';
        this.bar.style.opacity    = '1';

        // Siguiente frame: animamos hasta 40%
        requestAnimationFrame(() => {
            this.bar.style.transition = 'width 300ms cubic-bezier(0.4,0,0.2,1)';
            this.bar.style.width      = '40%';

            clearTimeout(this.timer);
            this.timer = setTimeout(() => {
                if (this.bar) {
                    this.bar.style.transition = 'width 4000ms cubic-bezier(0.1,0,0.3,1)';
                    this.bar.style.width      = '85%';
                }
            }, 320);
        });
    }

    progressTo(percent, duration = 200) {
        if (!this.bar) return;
        this.bar.style.transition = `width ${duration}ms ease-out`;
        this.bar.style.width      = percent + '%';
    }

    finish() {
        clearTimeout(this.timer);
        if (!this.bar) return;
        this.bar.style.transition = 'width 120ms ease-out';
        this.bar.style.width      = '100%';
        setTimeout(() => {
            if (!this.bar) return;
            this.bar.style.transition = 'opacity 180ms ease-in';
            this.bar.style.opacity    = '0';
            setTimeout(() => {
                if (this.bar) {
                    this.bar.style.transition = 'none';
                    this.bar.style.width      = '0%';
                }
            }, 200);
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

            // Si no hay claves con valor real, no restaurar
            const hasData = Object.values(formData).some(v =>
                v !== null && v !== undefined && v !== '' && v !== false
            );
            if (!hasData) return;

            let restoredCount = 0;
            this.isRestoring = true;

            // 1. Sync with Alpine reactive state (x-model pages — no tienen 'name')
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

            // 2. Populate native DOM inputs (tienen name=)
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
            this.dirty = true;

            // Mostrar banner si existen datos guardados, sin importar restoredCount.
            // Las páginas Alpine con x-model tienen restoredCount=0 (no usan name=)
            // pero el borrador SÍ fue restaurado al estado reactivo de Alpine.
            if (showNotification && hasData) {
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

        if (indicator) return; // ya visible

        // Buscar el contenedor más apropiado para insertar el banner:
        // Subimos al primer ancestro directo de main que contiene el h1/h2,
        // para que el banner quede ENCIMA del bloque de encabezado completo (incluyendo botones de acción).
        const heading = document.querySelector('main h1, main h2, .page-header');
        const mainContent = document.querySelector('main > div, main > section, .content-wrapper');
        const targetContainer = mainContent || document.querySelector('main') || this.form || document.body;

        // Buscar el bloque contenedor del heading (su padre directo dentro de main > div)
        // para insertar el banner ANTES de ese bloque, no dentro del flex-row del título.
        let headingBlock = null;
        if (heading) {
            // Subir hasta encontrar un hijo directo del targetContainer
            let el = heading;
            while (el && el.parentElement && el.parentElement !== targetContainer) {
                el = el.parentElement;
            }
            headingBlock = (el && el.parentElement === targetContainer) ? el : null;
        }

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

        // Insertar antes del bloque completo de encabezado (no dentro del flex-row del título)
        if (headingBlock) {
            targetContainer.insertBefore(indicator, headingBlock);
        } else if (heading && heading.parentNode) {
            heading.parentNode.insertBefore(indicator, heading);
        } else {
            targetContainer.prepend(indicator);
        }

        document.getElementById('btn-discard-draft')?.addEventListener('click', async (e) => {
            e.preventDefault();
            e.stopPropagation();
            const fn = typeof window.farmaConfirm === 'function'
                ? window.farmaConfirm
                : opts => Promise.resolve(window.confirm(opts.body || opts.title));
            const ok = await fn({
                title: 'Descartar borrador',
                body: '¿Deseas descartar los datos del borrador y reiniciar el formulario? Esta acción no puede deshacerse.',
                type: 'warning',
                ok: 'Sí, descartar'
            });
            if (ok) {
                this.clearDraft();
                window.location.reload();
            }
        });
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

// ==========================================
// AUTO-COLLAPSE SIDEBAR ON MODAL OPEN
// ==========================================
class FarmaModalWatcher {
    constructor() {
        this.init();
    }

    init() {
        const modalEvents = ['open-modal', 'modal-open', 'farma:modal-open', 'confirmar'];
        modalEvents.forEach(evt => {
            window.addEventListener(evt, () => this.triggerCollapse());
        });

        const observer = new MutationObserver((mutations) => {
            for (const mutation of mutations) {
                if (mutation.type === 'childList') {
                    for (const node of mutation.addedNodes) {
                        if (node.nodeType === Node.ELEMENT_NODE && this.isModalElement(node)) {
                            if (this.isElementVisible(node)) {
                                this.triggerCollapse();
                                return;
                            }
                        }
                    }
                } else if (mutation.type === 'attributes' && (mutation.attributeName === 'style' || mutation.attributeName === 'class')) {
                    const target = mutation.target;
                    if (target.nodeType === Node.ELEMENT_NODE && this.isModalElement(target)) {
                        if (this.isElementVisible(target)) {
                            this.triggerCollapse();
                            return;
                        }
                    }
                }
            }
        });

        if (document.body) {
            observer.observe(document.body, {
                childList: true,
                subtree: true,
                attributes: true,
                attributeFilter: ['style', 'class']
            });
        } else {
            document.addEventListener('DOMContentLoaded', () => {
                observer.observe(document.body, {
                    childList: true,
                    subtree: true,
                    attributes: true,
                    attributeFilter: ['style', 'class']
                });
            });
        }
    }

    isModalElement(el) {
        if (!el || !el.classList) return false;
        return (
            (el.classList.contains('fixed') && el.classList.contains('inset-0') && (el.classList.contains('z-[9999]') || el.classList.contains('z-[10000]') || el.classList.contains('z-50'))) ||
            el.getAttribute('role') === 'dialog' ||
            (typeof el.id === 'string' && el.id.startsWith('modal'))
        );
    }

    isElementVisible(el) {
        if (el.classList.contains('hidden')) return false;
        if (el.style.display === 'none') return false;
        if (el.hasAttribute('x-cloak') && !window.Alpine) return false;
        return true;
    }

    triggerCollapse() {
        window.dispatchEvent(new CustomEvent('collapse-sidebar'));
    }
}

// Instantiate systems
window.farmaProgressBar = new FarmaProgressBar();
window.farmaDraftEngine = new FarmaDraftEngine();
window.farmaToast = new FarmaToastEngine();
window.farmaModalWatcher = new FarmaModalWatcher();
setupGlobalHttpInterceptors();


