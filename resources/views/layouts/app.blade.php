<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" 
      x-data="{ 
          darkMode: localStorage.getItem('farma_theme') === 'dark' || (!localStorage.getItem('farma_theme') && window.matchMedia('(prefers-color-scheme: dark)').matches),
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
            if (localStorage.getItem('farma_theme') === 'dark' || (!localStorage.getItem('farma_theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }

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
            window.addEventListener('keydown', (e) => {
                const isInput = ['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName);
                
                if (e.key === 'F2') {
                    e.preventDefault();
                    window.location.href = '{{ route('ventas.create') }}';
                } else if (e.key === 'F4') {
                    e.preventDefault();
                    window.location.href = '{{ route('compras.create') }}';
                } else if (e.altKey && e.key.toLowerCase() === 'i') {
                    e.preventDefault();
                    window.location.href = '{{ route('inventario.index') }}';
                } else if (e.altKey && e.key.toLowerCase() === 'r') {
                    e.preventDefault();
                    window.location.href = '{{ route('recetas.index') }}';
                } else if (e.altKey && e.key.toLowerCase() === 'p') {
                    e.preventDefault();
                    window.location.href = '{{ route('productos.index') }}';
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
                    <!-- Flash Alerts -->
                    <div x-show="!posFullscreen" class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
                        @if (session('success'))
                            <div class="bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-300 dark:border-emerald-800 p-4 rounded-xl shadow-sm flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-7 h-7 rounded-full bg-emerald-100 dark:bg-emerald-900 flex items-center justify-center shrink-0 text-emerald-600 dark:text-emerald-300 font-bold text-xs">
                                        ✓
                                    </div>
                                    <span class="text-emerald-900 dark:text-emerald-100 font-medium text-sm">{{ session('success') }}</span>
                                </div>
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="bg-rose-50 dark:bg-rose-950/60 border border-rose-300 dark:border-rose-800 p-4 rounded-xl shadow-sm flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-7 h-7 rounded-full bg-rose-100 dark:bg-rose-900 flex items-center justify-center shrink-0 text-rose-600 dark:text-rose-300 font-bold text-xs">
                                        ✕
                                    </div>
                                    <span class="text-rose-900 dark:text-rose-100 font-medium text-sm">{{ session('error') }}</span>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Page View Content -->
                    <main class="page-fade-in mx-auto w-full transition-all duration-200"
                          :class="posFullscreen ? 'max-w-none px-2 sm:px-4 py-3' : 'max-w-[1600px] px-4 sm:px-6 lg:px-8 py-6'">
                        {{ $slot ?? '' }}
                        @yield('content')
                    </main>
                </div>
            </div>
        </div>

        <!-- Global Keyboard Shortcuts Modal -->
        <div x-show="showShortcutsModal" 
             x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 transform scale-100"
             x-transition:leave-end="opacity-0 transform scale-95"
             @click.self="showShortcutsModal = false"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm">
            
            <div @click.stop class="bg-white dark:bg-slate-900 rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800">
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
        @stack('scripts')
    </body>
</html>
