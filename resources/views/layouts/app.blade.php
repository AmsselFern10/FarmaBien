<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" 
      x-data="{ 
          darkMode: localStorage.getItem('farma_theme') === 'dark',
          toggleDarkMode() {
              this.darkMode = !this.darkMode;
              localStorage.setItem('farma_theme', this.darkMode ? 'dark' : 'light');
              if (this.darkMode) {
                  document.documentElement.classList.add('dark');
              } else {
                  document.documentElement.classList.remove('dark');
              }
          }
      }"
      x-init="if (darkMode) { document.documentElement.classList.add('dark'); } else { document.documentElement.classList.remove('dark'); }"
      :class="{ 'dark': darkMode }"
      class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <!-- Anti-flicker dark mode & Draft Pre-loader -->
        <script>
            (function() {
                // Preferencia guardada en localStorage (prioridad máxima)
                var saved = localStorage.getItem('farma_theme');

                // Preferencia guardada en el servidor (BD via Ajustes) — PHP→JS bridge
                var serverTheme = '{{ configuracion('interfaz_modo_oscuro_default', 'system') }}';

                var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                var isDark;
                if (saved === 'dark') {
                    isDark = true;
                } else if (saved === 'light') {
                    isDark = false;
                } else if (serverTheme === 'dark') {
                    // Sin localStorage: el servidor manda, escribir para futuras cargas
                    isDark = true;
                    localStorage.setItem('farma_theme', 'dark');
                } else if (serverTheme === 'light') {
                    isDark = false;
                    localStorage.setItem('farma_theme', 'light');
                } else {
                    // 'system' o sin preferencia
                    isDark = prefersDark;
                }

                if (isDark) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }

                // Sincronizar vista predeterminada de formularios (Moderna vs Compacta)
                var savedFormLayout = localStorage.getItem('farmaFormViewMode');
                var serverFormLayout = '{{ configuracion('interfaz_vista_formularios_default', 'modern') }}';
                if (!savedFormLayout && serverFormLayout) {
                    localStorage.setItem('farmaFormViewMode', serverFormLayout);
                }
            })();

            window.farmaGetDraft = function(path, defaults = {}) {
                try {
                    const clean = (path || window.location.pathname).replace(/\/+$/, '') || '/';
                    const key = 'farma_draft:' + clean;
                    const saved = sessionStorage.getItem(key) || localStorage.getItem(key);
                    if (saved) {
                        const parsed = JSON.parse(saved);
                        return Object.assign({}, defaults, parsed);
                    }
                } catch (e) {}
                return defaults;
            };
        </script>

        <title>{{ config('app.name', 'FarmaBien') }} - Sistema Farmacéutico</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts & Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('styles')
        <!-- Alpine x-cloak: oculta elementos hasta que Alpine termine de inicializar -->
        <style>[x-cloak] { display: none !important; }</style>



        <!--
            Speculation Rules API (Chrome 109+): prerender solo al hacer clic
            ('conservative'). 'eager' era tan agresivo como 'immediate' —
            prerenderizaba TODO el sidebar en cuanto cargaba la pagina, causando
            uso excesivo de CPU/RAM y posibles races en create/edit/show.
            'conservative' = prerender arranca exactamente con el clic real.
        -->
        <script type="speculationrules">
        {
            "prerender": [
                {
                    "where": { "selector_matches": "nav a[href], aside a[href]" },
                    "eagerness": "conservative"
                }
            ],
            "prefetch": [
                {
                    "where": { "selector_matches": "a[href]", "not": { "selector_matches": "a[href^='#'], a[href^='mailto:'], a[href^='tel:'], a[href*='/logout'], a[href*='/delete'], a[href*='/destroy']" } },
                    "eagerness": "conservative"
                }
            ]
        }
        </script>
    </head>
    <body x-data="{ 
        sidebarCollapsed: localStorage.getItem('farma_sidebar_collapsed') === 'true',
        mobileSidebarOpen: false,
        showShortcutsModal: false,
        posFullscreen: false,
        toggleSidebar() {
            this.sidebarCollapsed = !this.sidebarCollapsed;
            localStorage.setItem('farma_sidebar_collapsed', this.sidebarCollapsed);
        },
        togglePosFullscreen() {
            this.posFullscreen = !this.posFullscreen;
        },
        initShortcuts() {
            const nav = (url) => window.farmaNavigate ? window.farmaNavigate(url) : (window.location.href = url);

            window.addEventListener('keydown', (e) => {
                const isInput = ['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName);

                // Ctrl+K / Cmd+K — Búsqueda global (tiene prioridad sobre el resto)
                if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                    e.preventDefault();
                    return; // Lo maneja el componente farmaGlobalSearch directamente
                }

                if (e.key === 'F2') {
                    e.preventDefault();
                    nav('{{ route('ventas.create') }}');
                } else if (e.key === 'F4') {
                    e.preventDefault();
                    nav('{{ route('compras.create') }}');
                } else if (e.altKey && e.key.toLowerCase() === 'i') {
                    e.preventDefault();
                    nav('{{ route('inventario.index') }}');
                } else if (e.altKey && e.key.toLowerCase() === 'r') {
                    e.preventDefault();
                    nav('{{ route('recetas.index') }}');
                } else if (e.altKey && e.key.toLowerCase() === 'p') {
                    e.preventDefault();
                    nav('{{ route('productos.index') }}');
                } else if (e.key === 'F1' || (e.key === '?' && !isInput)) {
                    e.preventDefault();
                    this.showShortcutsModal = !this.showShortcutsModal;
                } else if (e.key === 'Escape' && this.showShortcutsModal) {
                    this.showShortcutsModal = false;
                }
            });

            window.addEventListener('toggle-pos-fullscreen', () => {
                this.posFullscreen = !this.posFullscreen;
            });
            window.addEventListener('exit-pos-fullscreen', () => {
                this.posFullscreen = false;
            });
            window.addEventListener('collapse-sidebar', () => {
                this.sidebarCollapsed = true;
                this.mobileSidebarOpen = false;
            });
        }
    }" 
    x-init="initShortcuts()"
    class="font-sans antialiased bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 h-full overflow-hidden">
        
        <div class="flex h-full">
            <!-- Desktop Sidebar -->
            @include('layouts.sidebar')

            <!-- Mobile Drawer -->
            @include('layouts.mobile-sidebar')

            <!-- Main Workspace -->
            <div class="flex-1 flex flex-col min-w-0 h-full overflow-hidden">
                <!-- Topbar with Integrated Tabs -->
                @include('layouts.topbar')

                <!-- Main Scrollable Area -->
                <div class="flex-1 overflow-y-auto">
                    <!-- Page View Content -->
                    <main id="main-content"
                          class="page-fade-in mx-auto w-full transition-all duration-200"
                          :class="posFullscreen ? 'max-w-none px-2 sm:px-4 py-3' : 'max-w-[1600px] px-4 sm:px-6 lg:px-8 py-6'">
                        {{ $slot ?? '' }}
                        @yield('content')
                    </main>
                </div>
            </div>
        </div>

        {{-- Sistema unificado de notificaciones (toast + modal de confirmación) --}}
        <x-notifications />

        {{-- Búsqueda Global — Ctrl+K --}}
        <x-busqueda-global />

        <!-- Global Keyboard Shortcuts Modal -->
        <template x-teleport="body">
            <div x-show="showShortcutsModal" 
                 x-cloak
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 transform scale-100"
                 x-transition:leave-end="opacity-0 transform scale-95"
                 @click.self="showShortcutsModal = false"
                 class="fixed inset-0 z-[9999] flex items-center justify-center p-3 sm:p-4 bg-slate-950/75 backdrop-blur-sm overflow-y-auto">
                
                <div @click.stop class="bg-white dark:bg-slate-900 rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800 my-auto">
                    <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3 mb-4">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center space-x-2">
                            <span>⌨️ Atajos de Teclado Rápidos</span>
                        </h3>
                        <button @click="showShortcutsModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="space-y-2.5">
                        <div class="flex items-center justify-between p-2.5 rounded-lg bg-slate-50 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-800">
                            <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Punto de Venta (POS) / Nueva Venta</span>
                            <kbd class="px-2 py-0.5 bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 rounded text-xs font-mono font-bold">F2</kbd>
                        </div>
                        <div class="flex items-center justify-between p-2.5 rounded-lg bg-slate-50 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-800">
                            <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Registrar Compra / Recepción Lote</span>
                            <kbd class="px-2 py-0.5 bg-slate-200 text-slate-800 dark:bg-slate-700 dark:text-slate-200 rounded text-xs font-mono font-bold">F4</kbd>
                        </div>
                        <div class="flex items-center justify-between p-2.5 rounded-lg bg-slate-50 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-800">
                            <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Panel de Inventario y Kardex</span>
                            <kbd class="px-2 py-0.5 bg-slate-200 text-slate-800 dark:bg-slate-700 dark:text-slate-200 rounded text-xs font-mono font-bold">Alt + I</kbd>
                        </div>
                        <div class="flex items-center justify-between p-2.5 rounded-lg bg-slate-50 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-800">
                            <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Recetas Médicas</span>
                            <kbd class="px-2 py-0.5 bg-slate-200 text-slate-800 dark:bg-slate-700 dark:text-slate-200 rounded text-xs font-mono font-bold">Alt + R</kbd>
                        </div>
                        <div class="flex items-center justify-between p-2.5 rounded-lg bg-slate-50 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-800">
                            <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Catálogo de Medicamentos</span>
                            <kbd class="px-2 py-0.5 bg-slate-200 text-slate-800 dark:bg-slate-700 dark:text-slate-200 rounded text-xs font-mono font-bold">Alt + P</kbd>
                        </div>
                        <div class="flex items-center justify-between p-2.5 rounded-lg bg-slate-50 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-800">
                            <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Ver esta guía de atajos</span>
                            <kbd class="px-2 py-0.5 bg-slate-200 text-slate-800 dark:bg-slate-700 dark:text-slate-200 rounded text-xs font-mono font-bold">F1 o ?</kbd>
                        </div>
                    </div>

                    <div class="mt-5 text-center">
                        <button @click="showShortcutsModal = false" class="px-4 py-2 bg-slate-900 text-white dark:bg-slate-800 rounded-lg text-xs font-semibold hover:bg-slate-800 transition">
                            Entendido
                        </button>
                    </div>
                </div>
            </div>
        </template>
        @stack('scripts')
        @if(session('tema_aplicado'))
        <script>
            (function() {
                var tema = '{{ session('tema_aplicado') }}';
                if (tema === 'dark') {
                    localStorage.setItem('farma_theme', 'dark');
                    document.documentElement.classList.add('dark');
                } else if (tema === 'light') {
                    localStorage.setItem('farma_theme', 'light');
                    document.documentElement.classList.remove('dark');
                } else {
                    // 'system': borrar preferencia para que el siguiente load use el sistema
                    localStorage.removeItem('farma_theme');
                }
            })();
        </script>
        @endif

        @if(session('form_view_aplicado'))
        <script>
            (function() {
                var formMode = '{{ session('form_view_aplicado') }}';
                localStorage.setItem('farmaFormViewMode', formMode);
            })();
        </script>
        @endif
    </body>
</html>
