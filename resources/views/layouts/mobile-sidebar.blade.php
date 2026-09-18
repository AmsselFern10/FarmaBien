<!-- Mobile Slide-over Drawer with Accordion Submenus -->
<div x-show="!posFullscreen && mobileSidebarOpen" 
     x-data="{
         openMobileMenus: {
             operaciones: {{ request()->routeIs('ventas.*') || request()->routeIs('compras.*') || request()->routeIs('recetas.*') ? 'true' : 'false' }},
             inventario: {{ request()->routeIs('inventario.*') ? 'true' : 'false' }},
             catalogos: {{ request()->routeIs('productos.*') || request()->routeIs('laboratorios.*') || request()->routeIs('categorias.*') || request()->routeIs('clientes.*') || request()->routeIs('proveedores.*') ? 'true' : 'true' }},
             administracion: {{ request()->routeIs('reportes.*') || request()->routeIs('usuarios.*') || request()->routeIs('admin.*') ? 'true' : 'false' }}
         }
     }"
     class="fixed inset-0 z-50 flex md:hidden" 
     style="display: none;">
    <!-- Backdrop -->
    <div x-show="mobileSidebarOpen" 
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="mobileSidebarOpen = false" 
         class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm"></div>

    <!-- Sidebar Panel -->
    <div x-show="mobileSidebarOpen" 
         x-transition:enter="transition ease-in-out duration-300 transform"
         x-transition:enter-start="-translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in-out duration-300 transform"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="-translate-x-full"
         class="relative flex-1 flex flex-col max-w-xs w-full bg-slate-900 border-r border-slate-800">
        
        <!-- Header -->
        <div class="h-16 flex items-center justify-between px-5 border-b border-slate-800 bg-slate-950/40">
            <a href="{{ route('dashboard') }}" class="flex items-center space-x-2.5">
                <div class="w-8 h-8 rounded-lg bg-emerald-600 flex items-center justify-center text-white font-black text-lg">
                    +
                </div>
                <span class="font-bold text-lg text-white">Farma<span class="text-emerald-400">Bien</span></span>
            </a>
            <button @click="mobileSidebarOpen = false" class="text-slate-400 hover:text-white p-1">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Navigation -->
        <div class="flex-1 overflow-y-auto px-4 py-4 space-y-3">
            <a href="{{ route('dashboard') }}" 
               @click="mobileSidebarOpen = false"
               class="flex items-center px-3 py-2 rounded-lg text-xs font-semibold {{ request()->routeIs('dashboard') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
                <span>Dashboard</span>
            </a>

            @can('realizar ventas')
            <a href="{{ route('ventas.create') }}" 
               @click="mobileSidebarOpen = false"
               class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-bold bg-emerald-950/60 text-emerald-300 border border-emerald-800">
                <span>⚡ Punto de Venta (POS)</span>
                <kbd class="px-1.5 py-0.5 text-[9px] font-mono bg-emerald-900 border border-emerald-700 rounded text-emerald-200">F2</kbd>
            </a>
            @endcan

            <!-- Operaciones -->
            @canany(['ver ventas', 'ver ventas propias', 'ver compras', 'ver recetas'])
            <div class="space-y-1">
                <button @click="openMobileMenus.operaciones = !openMobileMenus.operaciones"
                        class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold text-slate-400 hover:text-slate-200 hover:bg-slate-800">
                    <span>Operaciones</span>
                    <svg class="w-3.5 h-3.5 transition-transform" :class="openMobileMenus.operaciones ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="openMobileMenus.operaciones" class="pl-4 space-y-1 border-l border-slate-800 ml-3">
                    @canany(['ver ventas', 'ver ventas propias'])
                    <a href="{{ route('ventas.index') }}" @click="mobileSidebarOpen = false" class="block px-2 py-1.5 rounded text-xs text-slate-300 hover:bg-slate-800">Historial Ventas</a>
                    @endcanany
                    @can('ver compras')
                    <a href="{{ route('compras.index') }}" @click="mobileSidebarOpen = false" class="block px-2 py-1.5 rounded text-xs text-slate-300 hover:bg-slate-800">Compras & Lotes</a>
                    @endcan
                    @can('ver recetas')
                    <a href="{{ route('recetas.index') }}" @click="mobileSidebarOpen = false" class="block px-2 py-1.5 rounded text-xs text-slate-300 hover:bg-slate-800">Recetas Médicas</a>
                    @endcan
                </div>
            </div>
            @endcanany

            <!-- Inventario -->
            @can('ver movimientos inventario')
            <div class="space-y-1">
                <button @click="openMobileMenus.inventario = !openMobileMenus.inventario"
                        class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold text-slate-400 hover:text-slate-200 hover:bg-slate-800">
                    <span>Inventario</span>
                    <svg class="w-3.5 h-3.5 transition-transform" :class="openMobileMenus.inventario ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="openMobileMenus.inventario" class="pl-4 space-y-1 border-l border-slate-800 ml-3">
                    <a href="{{ route('inventario.index') }}" @click="mobileSidebarOpen = false" class="block px-2 py-1.5 rounded text-xs text-slate-300 hover:bg-slate-800">Stock & Monitor</a>
                    <a href="{{ route('inventario.movimientos') }}" @click="mobileSidebarOpen = false" class="block px-2 py-1.5 rounded text-xs text-slate-300 hover:bg-slate-800">Kardex de Movimientos</a>
                    <a href="{{ route('inventario.lotes') }}" @click="mobileSidebarOpen = false" class="block px-2 py-1.5 rounded text-xs text-slate-300 hover:bg-slate-800">Lotes y Vencimientos</a>
                    <a href="{{ route('inventario.alertas') }}" @click="mobileSidebarOpen = false" class="block px-2 py-1.5 rounded text-xs text-slate-300 hover:bg-slate-800">Alertas de Stock</a>
                </div>
            </div>
            @endcan

            <!-- Catálogos -->
            @canany(['ver productos', 'ver laboratorios', 'ver categorias', 'ver clientes', 'ver proveedores'])
            <div class="space-y-1">
                <button @click="openMobileMenus.catalogos = !openMobileMenus.catalogos"
                        class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold text-slate-400 hover:text-slate-200 hover:bg-slate-800">
                    <span>Catálogos</span>
                    <svg class="w-3.5 h-3.5 transition-transform" :class="openMobileMenus.catalogos ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="openMobileMenus.catalogos" class="pl-4 space-y-1 border-l border-slate-800 ml-3">
                    @can('ver productos')<a href="{{ route('productos.index') }}" @click="mobileSidebarOpen = false" class="block px-2 py-1.5 rounded text-xs text-slate-300 hover:bg-slate-800">Medicamentos</a>@endcan
                    @can('ver laboratorios')<a href="{{ route('laboratorios.index') }}" @click="mobileSidebarOpen = false" class="block px-2 py-1.5 rounded text-xs text-slate-300 hover:bg-slate-800">Laboratorios</a>@endcan
                    @can('ver categorias')<a href="{{ route('categorias.index') }}" @click="mobileSidebarOpen = false" class="block px-2 py-1.5 rounded text-xs text-slate-300 hover:bg-slate-800">Categorías</a>@endcan
                    @can('ver proveedores')<a href="{{ route('proveedores.index') }}" @click="mobileSidebarOpen = false" class="block px-2 py-1.5 rounded text-xs text-slate-300 hover:bg-slate-800">Proveedores</a>@endcan
                    @can('ver clientes')<a href="{{ route('clientes.index') }}" @click="mobileSidebarOpen = false" class="block px-2 py-1.5 rounded text-xs text-slate-300 hover:bg-slate-800">Clientes / Pacientes</a>@endcan
                </div>
            </div>
            @endcanany

            <!-- Administración -->
            @canany(['ver reportes ventas', 'ver usuarios'])
            <div class="space-y-1">
                <button @click="openMobileMenus.administracion = !openMobileMenus.administracion"
                        class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold text-slate-400 hover:text-slate-200 hover:bg-slate-800">
                    <span>Administración</span>
                    <svg class="w-3.5 h-3.5 transition-transform" :class="openMobileMenus.administracion ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="openMobileMenus.administracion" class="pl-4 space-y-1 border-l border-slate-800 ml-3">
                    @can('ver reportes ventas')<a href="{{ route('reportes.index') }}" @click="mobileSidebarOpen = false" class="block px-2 py-1.5 rounded text-xs text-slate-300 hover:bg-slate-800">Reportes</a>@endcan
                    @role('Admin')<a href="{{ route('usuarios.index') }}" @click="mobileSidebarOpen = false" class="block px-2 py-1.5 rounded text-xs text-slate-300 hover:bg-slate-800">Usuarios & Roles</a>@endrole
                </div>
            </div>
            @endcanany
        </div>

        <!-- Footer User -->
        <div class="p-4 border-t border-slate-800 bg-slate-950/40">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-white">{{ Auth::user()->name }}</p>
                    <p class="text-[11px] text-slate-400">{{ Auth::user()->roles->first()?->name ?? 'Usuario' }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-xs font-bold text-rose-400 hover:text-rose-300">Salir</button>
                </form>
            </div>
        </div>
    </div>
</div>
