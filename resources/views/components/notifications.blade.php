{{--
    ╔══════════════════════════════════════════════════════════════╗
    ║  FarmaBien — Sistema Unificado de Notificaciones             ║
    ║                                                              ║
    ║  Incluye:                                                    ║
    ║  • Toast animado (esquina superior derecha, auto-cierre 4s)  ║
    ║  • Modal de confirmación personalizado (reemplaza confirm()) ║
    ║                                                              ║
    ║  Uso desde PHP (flash):                                      ║
    ║    return redirect()->with('success', 'Mensaje');            ║
    ║    return redirect()->with('error', 'Mensaje');              ║
    ║    return redirect()->with('warning', 'Mensaje');            ║
    ║    return redirect()->with('info', 'Mensaje');               ║
    ║                                                              ║
    ║  Uso desde JS (async):                                       ║
    ║    window.farmaToast.success('Guardado correctamente');      ║
    ║    window.farmaToast.error('No se pudo guardar');            ║
    ║    const ok = await window.farmaConfirm({                    ║
    ║        title: '¿Eliminar?',                                  ║
    ║        body: 'Esta acción no se puede deshacer.',            ║
    ║        type: 'danger' // danger | warning | default          ║
    ║    });                                                        ║
    ║                                                              ║
    ║  Uso en formularios (sin JS):                                ║
    ║    <form data-confirm                                        ║
    ║          data-confirm-title="¿Desactivar producto?"          ║
    ║          data-confirm-body="Quedará oculto del sistema."     ║
    ║          data-confirm-type="warning"                         ║
    ║          data-confirm-ok="Sí, desactivar">                   ║
    ╚══════════════════════════════════════════════════════════════╝
--}}
<div
    x-data="{
        toasts: [],
        dlg: {
            open: false,
            title: '',
            body: '',
            type: 'default',
            ok: '',
            _resolve: null
        },

        /* ── Toast API ─────────────────────────────────────── */
        addToast(type, message, duration) {
            if (typeof duration !== 'number' || duration <= 0 || isNaN(duration)) {
                duration = (type === 'error' ? 6000 : type === 'warning' ? 5000 : 4000);
            }
            const id = Date.now() + Math.random();
            this.toasts.push({ id, type, message, progress: 100, visible: true });
            const steps = 60;
            const interval = duration / steps;
            let step = 0;
            const self = this;
            const timer = setInterval(function() {
                step++;
                const idx = self.toasts.findIndex(function(t) { return t.id === id; });
                if (idx === -1) { clearInterval(timer); return; }
                self.toasts[idx].progress = 100 - (step / steps * 100);
                if (step >= steps) { clearInterval(timer); self.removeToast(id); }
            }, interval);
        },

        removeToast(id) {
            const idx = this.toasts.findIndex(function(t) { return t.id === id; });
            if (idx !== -1) {
                this.toasts[idx].visible = false;
                const self = this;
                setTimeout(function() {
                    self.toasts = self.toasts.filter(function(t) { return t.id !== id; });
                }, 300);
            }
        },

        /* ── Confirm API ───────────────────────────────────── */
        showConfirm(title, body, type, ok) {
            const self = this;
            return new Promise(function(resolve) {
                self.dlg.title = title || '¿Confirmar acción?';
                self.dlg.body  = body  || '';
                self.dlg.type  = type  || 'default';
                self.dlg.ok    = ok    || '';
                self.dlg._resolve = resolve;
                self.dlg.open  = true;
            });
        },

        resolveConfirm(result) {
            this.dlg.open = false;
            if (this.dlg._resolve) {
                this.dlg._resolve(result);
                this.dlg._resolve = null;
            }
        },

        checkStorageFlash() {
            try {
                const rawFlash = localStorage.getItem('farma_flash') || sessionStorage.getItem('farma_flash');
                if (rawFlash) {
                    localStorage.removeItem('farma_flash');
                    sessionStorage.removeItem('farma_flash');
                    const _jsFlash = JSON.parse(rawFlash);
                    if (_jsFlash && _jsFlash.message) {
                        this.addToast(_jsFlash.type || 'success', _jsFlash.message);
                    }
                }
            } catch(e) {
                try { localStorage.removeItem('farma_flash'); } catch(e) {}
                try { sessionStorage.removeItem('farma_flash'); } catch(e) {}
            }
        }
    }"
    @notify.window="addToast($event.detail.type || 'info', $event.detail.message, $event.detail.duration)"
    x-init="
        /* ── Flash messages de PHP → toasts ───────────────── */
        @if(session('success'))
            $nextTick(() => addToast('success', @js(session('success'))));
        @endif
        @if(session('error'))
            $nextTick(() => addToast('error', @js(session('error'))));
        @endif
        @if(session('warning'))
            $nextTick(() => addToast('warning', @js(session('warning'))));
        @endif
        @if(session('info'))
            $nextTick(() => addToast('info', @js(session('info'))));
        @endif

        $nextTick(() => checkStorageFlash());

        /* ── API global para JS/async externo ──────────────── */
        window.farmaToast = {
            success : (msg, dur) => window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message: msg, duration: dur } })),
            error   : (msg, dur) => window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error',   message: msg, duration: dur } })),
            warning : (msg, dur) => window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'warning', message: msg, duration: dur } })),
            info    : (msg, dur) => window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'info',    message: msg, duration: dur } }))
        };

        window.farmaConfirm = opts => {
            opts = opts || {};
            return showConfirm(opts.title, opts.body, opts.type, opts.ok);
        };

        /* ── Interceptar formularios con data-confirm ──────── */
        document.addEventListener('submit', async e => {
            const form = e.target;
            if (!form.hasAttribute('data-confirm')) return;
            e.preventDefault();
            const confirmed = await showConfirm(
                form.dataset.confirmTitle || '¿Confirmar acción?',
                form.dataset.confirmBody  || 'Esta acción no se puede deshacer.',
                form.dataset.confirmType  || 'default',
                form.dataset.confirmOk    || ''
            );
            if (confirmed) form.submit();
        }, true);
    "
    id="farma-notifications"
>

    {{-- ═══════════════════════  TOAST STACK  ═══════════════════════ --}}
    <div
        class="fixed top-5 right-5 z-[99999] flex flex-col gap-2.5 items-end pointer-events-none"
        style="max-width:420px;width:calc(100vw - 2.5rem)"
    >
        <template x-for="toast in toasts" :key="toast.id">
            <div
                x-show="toast.visible"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-x-10 scale-95"
                x-transition:enter-end="opacity-100 translate-x-0 scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-x-0 scale-100"
                x-transition:leave-end="opacity-0 translate-x-10 scale-95"
                class="relative w-full rounded-xl shadow-lg border overflow-hidden pointer-events-auto"
                :class="{
                    'bg-emerald-50 border-emerald-200 dark:bg-emerald-950/90 dark:border-emerald-800' : toast.type === 'success',
                    'bg-rose-50    border-rose-200    dark:bg-rose-950/90    dark:border-rose-800'    : toast.type === 'error',
                    'bg-amber-50   border-amber-200   dark:bg-amber-950/90   dark:border-amber-800'   : toast.type === 'warning',
                    'bg-sky-50     border-sky-200     dark:bg-sky-950/90     dark:border-sky-800'     : toast.type === 'info'
                }"
            >
                <div class="flex items-start gap-3 px-4 py-3.5">
                    {{-- Ícono --}}
                    <div
                        class="shrink-0 w-7 h-7 rounded-full flex items-center justify-center text-sm font-bold mt-0.5"
                        :class="{
                            'bg-emerald-100 dark:bg-emerald-900 text-emerald-600 dark:text-emerald-300' : toast.type === 'success',
                            'bg-rose-100    dark:bg-rose-900    text-rose-600    dark:text-rose-300'    : toast.type === 'error',
                            'bg-amber-100   dark:bg-amber-900   text-amber-600   dark:text-amber-300'   : toast.type === 'warning',
                            'bg-sky-100     dark:bg-sky-900     text-sky-600     dark:text-sky-300'     : toast.type === 'info'
                        }"
                    >
                        <svg x-show="toast.type === 'success'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        <svg x-show="toast.type === 'error'"   class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                        <svg x-show="toast.type === 'warning'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                        <svg x-show="toast.type === 'info'"    class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>

                    {{-- Mensaje --}}
                    <p
                        class="flex-1 text-sm font-medium leading-snug pt-0.5"
                        :class="{
                            'text-emerald-900 dark:text-emerald-100' : toast.type === 'success',
                            'text-rose-900    dark:text-rose-100'    : toast.type === 'error',
                            'text-amber-900   dark:text-amber-100'   : toast.type === 'warning',
                            'text-sky-900     dark:text-sky-100'     : toast.type === 'info'
                        }"
                        x-text="toast.message"
                    ></p>

                    {{-- Cerrar --}}
                    <button
                        @click="removeToast(toast.id)"
                        type="button"
                        class="shrink-0 w-5 h-5 flex items-center justify-center rounded opacity-40 hover:opacity-80 transition-opacity mt-0.5"
                        :class="{
                            'text-emerald-700 dark:text-emerald-300' : toast.type === 'success',
                            'text-rose-700    dark:text-rose-300'    : toast.type === 'error',
                            'text-amber-700   dark:text-amber-300'   : toast.type === 'warning',
                            'text-sky-700     dark:text-sky-300'     : toast.type === 'info'
                        }"
                        title="Cerrar"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Barra de progreso --}}
                <div
                    class="h-0.5 w-full"
                    :class="{
                        'bg-emerald-100 dark:bg-emerald-900' : toast.type === 'success',
                        'bg-rose-100    dark:bg-rose-900'    : toast.type === 'error',
                        'bg-amber-100   dark:bg-amber-900'   : toast.type === 'warning',
                        'bg-sky-100     dark:bg-sky-900'     : toast.type === 'info'
                    }"
                >
                    <div
                        class="h-full transition-all ease-linear duration-100"
                        :class="{
                            'bg-emerald-500' : toast.type === 'success',
                            'bg-rose-500'    : toast.type === 'error',
                            'bg-amber-500'   : toast.type === 'warning',
                            'bg-sky-500'     : toast.type === 'info'
                        }"
                        :style="`width:${toast.progress}%`"
                    ></div>
                </div>
            </div>
        </template>
    </div>

    {{-- ═══════════════════  MODAL DE CONFIRMACIÓN  ═════════════════ --}}
    <div
        x-show="dlg.open"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @keydown.escape.window="resolveConfirm(false)"
        @click.self="resolveConfirm(false)"
        class="fixed inset-0 z-[9998] flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm"
    >
        <div
            x-show="dlg.open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            @click.stop
            class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 w-full max-w-md"
        >
            {{-- Cabecera con ícono --}}
            <div class="flex items-start gap-4 p-6 pb-4">
                {{-- Ícono por tipo --}}
                <div
                    class="shrink-0 w-11 h-11 rounded-full flex items-center justify-center"
                    :class="{
                        'bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400'     : dlg.type === 'danger',
                        'bg-amber-100 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400'  : dlg.type === 'warning',
                        'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400' : dlg.type === 'default'
                    }"
                >
                    {{-- danger: papelera --}}
                    <svg x-show="dlg.type === 'danger'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    {{-- warning: triángulo --}}
                    <svg x-show="dlg.type === 'warning'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    </svg>
                    {{-- default: signo de interrogación --}}
                    <svg x-show="dlg.type === 'default'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>

                {{-- Textos --}}
                <div class="flex-1 min-w-0 pt-0.5">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white leading-snug" x-text="dlg.title"></h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1.5 leading-relaxed" x-text="dlg.body" x-show="dlg.body"></p>
                </div>
            </div>

            {{-- Acciones --}}
            <div class="flex items-center justify-end gap-2.5 px-6 pb-6 pt-2">
                <button
                    @click="resolveConfirm(false)"
                    type="button"
                    class="px-4 py-2 text-sm font-semibold rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 transition-colors"
                >
                    Cancelar
                </button>
                <button
                    @click="resolveConfirm(true)"
                    type="button"
                    class="px-5 py-2 text-sm font-semibold rounded-xl text-white transition-colors"
                    :class="{
                        'bg-rose-600 hover:bg-rose-700'   : dlg.type === 'danger',
                        'bg-amber-500 hover:bg-amber-600' : dlg.type === 'warning',
                        'bg-emerald-600 hover:bg-emerald-700' : dlg.type === 'default'
                    }"
                >
                    <span x-show="dlg.ok" x-text="dlg.ok"></span>
                    <span x-show="!dlg.ok && dlg.type === 'danger'">Sí, eliminar</span>
                    <span x-show="!dlg.ok && dlg.type === 'warning'">Sí, continuar</span>
                    <span x-show="!dlg.ok && dlg.type === 'default'">Confirmar</span>
                </button>
            </div>
        </div>
    </div>
</div>
