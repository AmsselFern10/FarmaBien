<!-- Mobile Slide-over Drawer with Accordion Submenus -->
<div x-show="!posFullscreen && mobileSidebarOpen" 
     x-data="{
         openMobileMenus: {
             operaciones: {{ request()->routeIs('ventas.*') || request()->routeIs('compras.*') || request()->routeIs('recetas.*') || request()->routeIs('cajas.*') ? 'true' : 'false' }},
             inventario: {{ request()->routeIs('inventario.*') ? 'true' : 'false' }},
             catalogos: {{ request()->routeIs('productos.*') || request()->routeIs('laboratorios.*') || request()->routeIs('categorias.*') || request()->routeIs('clientes.*') || request()->routeIs('proveedores.*') || request()->routeIs('presentaciones.*') || request()->routeIs('promociones.*') ? 'true' : 'false' }},
             administracion: {{ request()->routeIs('reportes.*') || request()->routeIs('usuarios.*') || request()->routeIs('admin.*') || request()->routeIs('ajustes.*') ? 'true' : 'false' }}
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
            <!-- Dashboard -->
            <a href="{{ route('dashboard') }}" 
               @click="mobileSidebarOpen = false"
               class="flex items-center px-3 py-2 rounded-lg text-xs font-semibold {{ request()->routeIs('dashboard') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
                <svg class="w-4 h-4 mr-2.5 shrink-0 {{ request()->routeIs('dashboard') ? 'text-white' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span>Dashboard</span>
            </a>

            <!-- POS -->
            @can('realizar ventas')
            <a href="{{ route('ventas.create') }}" 
               @click="mobileSidebarOpen = false"
               class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-bold bg-emerald-950/60 text-emerald-300 border border-emerald-800">
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2.5 shrink-0 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    <span>Punto de Venta (POS)</span>
                </div>
                <kbd class="px-1.5 py-0.5 text-[9px] font-mono bg-emerald-900 border border-emerald-700 rounded text-emerald-200">F2</kbd>
            </a>
            @endcan

            <!-- 1. Operaciones -->
            @canany(['ver ventas', 'ver ventas propias', 'ver compras', 'ver recetas', 'ver cajas'])
            <div class="space-y-1">
                <button @click="openMobileMenus.operaciones = !openMobileMenus.operaciones"
                        class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold text-slate-400 hover:text-slate-200 hover:bg-slate-800">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mr-2.5 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                        <span>Operaciones</span>
                    </div>
                    <svg class="w-3.5 h-3.5 transition-transform" :class="openMobileMenus.operaciones ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="openMobileMenus.operaciones" class="pl-4 space-y-1 border-l border-slate-800 ml-3">
                    @canany(['ver ventas', 'ver ventas propias'])
                    <a href="{{ route('ventas.index') }}" @click="mobileSidebarOpen = false" class="block px-2 py-1.5 rounded text-xs text-slate-300 hover:bg-slate-800">Historial Ventas</a>
                    <a href="{{ route('devoluciones.index') }}" @click="mobileSidebarOpen = false" class="block px-2 py-1.5 rounded text-xs text-slate-300 hover:bg-slate-800">Devoluciones</a>
                    @endcanany
                    @can('ver cajas')
                    <a href="{{ route('cajas.index') }}" @click="mobileSidebarOpen = false" class="block px-2 py-1.5 rounded text-xs text-slate-300 hover:bg-slate-800">Control de Cajas</a>
                    @endcan
                    @can('ver compras')
                    <a href="{{ route('compras.index') }}" @click="mobileSidebarOpen = false" class="block px-2 py-1.5 rounded text-xs text-slate-300 hover:bg-slate-800">Compras & Lotes</a>
                    <a href="{{ route('cuentas-por-pagar.index') }}" @click="mobileSidebarOpen = false" class="block px-2 py-1.5 rounded text-xs text-slate-300 hover:bg-slate-800">Cuentas por Pagar</a>
                    <a href="{{ route('ordenes-compras.index') }}" @click="mobileSidebarOpen = false" class="block px-2 py-1.5 rounded text-xs text-slate-300 hover:bg-slate-800">Órdenes de Compra</a>
                    <a href="{{ route('compras.comparador-precios') }}" @click="mobileSidebarOpen = false" class="block px-2 py-1.5 rounded text-xs text-slate-300 hover:bg-slate-800">Comparador Precios</a>
                    <a href="{{ route('compras.sugerencias-reorden') }}" @click="mobileSidebarOpen = false" class="block px-2 py-1.5 rounded text-xs text-slate-300 hover:bg-slate-800">Reorden Inteligente</a>
                    @endcan
                    @can('ver recetas')
                    <a href="{{ route('recetas.index') }}" @click="mobileSidebarOpen = false" class="block px-2 py-1.5 rounded text-xs text-slate-300 hover:bg-slate-800">Recetas Médicas</a>
                    @endcan
                </div>
            </div>
            @endcanany

            <!-- 2. Inventario -->
            @can('ver movimientos inventario')
            <div class="space-y-1">
                <button @click="openMobileMenus.inventario = !openMobileMenus.inventario"
                        class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold text-slate-400 hover:text-slate-200 hover:bg-slate-800">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mr-2.5 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                        <span>Inventario</span>
                    </div>
                    <svg class="w-3.5 h-3.5 transition-transform" :class="openMobileMenus.inventario ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="openMobileMenus.inventario" class="pl-4 space-y-1 border-l border-slate-800 ml-3">
                    <a href="{{ route('inventario.index') }}" @click="mobileSidebarOpen = false" class="block px-2 py-1.5 rounded text-xs text-slate-300 hover:bg-slate-800">Monitor de Stock</a>
                    <a href="{{ route('inventario.movimientos') }}" @click="mobileSidebarOpen = false" class="block px-2 py-1.5 rounded text-xs text-slate-300 hover:bg-slate-800">Kardex Físico</a>
                    <a href="{{ route('inventario.lotes') }}" @click="mobileSidebarOpen = false" class="block px-2 py-1.5 rounded text-xs text-slate-300 hover:bg-slate-800">Lotes y Vencimientos</a>
                    <a href="{{ route('inventario.alertas') }}" @click="mobileSidebarOpen = false" class="block px-2 py-1.5 rounded text-xs text-slate-300 hover:bg-slate-800">Alertas de Stock</a>
                    @can('ajustar inventario')
                    <a href="{{ route('inventario.ajustar') }}" @click="mobileSidebarOpen = false" class="block px-2 py-1.5 rounded text-xs text-indigo-400 hover:bg-slate-800">Ajustar Stock</a>
                    @endcan
                </div>
            </div>
            @endcan

            <!-- 3. Catálogos -->
            @canany(['ver productos', 'ver laboratorios', 'ver categorias', 'ver clientes', 'ver proveedores', 'ver promociones', 'ver presentaciones'])
            <div class="space-y-1">
                <button @click="openMobileMenus.catalogos = !openMobileMenus.catalogos"
                        class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold text-slate-400 hover:text-slate-200 hover:bg-slate-800">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mr-2.5 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                        <span>Catálogos</span>
                    </div>
                    <svg class="w-3.5 h-3.5 transition-transform" :class="openMobileMenus.catalogos ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="openMobileMenus.catalogos" class="pl-4 space-y-1 border-l border-slate-800 ml-3">
                    @can('ver productos')
                    <a href="{{ route('productos.index') }}" @click="mobileSidebarOpen = false" class="block px-2 py-1.5 rounded text-xs text-slate-300 hover:bg-slate-800">Medicamentos</a>
                    @endcan
                    @can('ver productos')
                    <a href="{{ route('precios.index') }}" @click="mobileSidebarOpen = false" class="block px-2 py-1.5 rounded text-xs text-slate-300 hover:bg-slate-800">Precios de Venta</a>
                    @endcan
                    @can('ver promociones')
                    <a href="{{ route('promociones.index') }}" @click="mobileSidebarOpen = false" class="block px-2 py-1.5 rounded text-xs text-slate-300 hover:bg-slate-800">Promociones & Descuentos</a>
                    @endcan
                    @can('ver laboratorios')
                    <a href="{{ route('laboratorios.index') }}" @click="mobileSidebarOpen = false" class="block px-2 py-1.5 rounded text-xs text-slate-300 hover:bg-slate-800">Laboratorios</a>
                    @endcan
                    @can('ver categorias')
                    <a href="{{ route('categorias.index') }}" @click="mobileSidebarOpen = false" class="block px-2 py-1.5 rounded text-xs text-slate-300 hover:bg-slate-800">Categorías</a>
                    @endcan
                    @can('ver productos')
                    <a href="{{ route('presentaciones.index') }}" @click="mobileSidebarOpen = false" class="block px-2 py-1.5 rounded text-xs text-slate-300 hover:bg-slate-800">Presentaciones</a>
                    @endcan
                    @can('ver proveedores')
                    <a href="{{ route('proveedores.index') }}" @click="mobileSidebarOpen = false" class="block px-2 py-1.5 rounded text-xs text-slate-300 hover:bg-slate-800">Proveedores</a>
                    @endcan
                    @can('ver clientes')
                    <a href="{{ route('clientes.index') }}" @click="mobileSidebarOpen = false" class="block px-2 py-1.5 rounded text-xs text-slate-300 hover:bg-slate-800">Clientes / Pacientes</a>
                    @endcan
                </div>
            </div>
            @endcanany

            <!-- 4. Administración & Preferencias -->
            @if(auth()->check())
            <div class="space-y-1">
                <button @click="openMobileMenus.administracion = !openMobileMenus.administracion"
                        class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold text-slate-400 hover:text-slate-200 hover:bg-slate-800">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mr-2.5 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span>Administración</span>
                    </div>
                    <svg class="w-3.5 h-3.5 transition-transform" :class="openMobileMenus.administracion ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="openMobileMenus.administracion" class="pl-4 space-y-1 border-l border-slate-800 ml-3">
                    @canany(['ver reportes ventas', 'ver reportes inventario', 'ver reportes compras'])
                    <a href="{{ route('reportes.index') }}" @click="mobileSidebarOpen = false" class="block px-2 py-1.5 rounded text-xs text-slate-300 hover:bg-slate-800">Reportes Gerenciales</a>
                    @endcan
                    @role('Admin')
                    <a href="{{ route('usuarios.index') }}" @click="mobileSidebarOpen = false" class="block px-2 py-1.5 rounded text-xs text-slate-300 hover:bg-slate-800">Usuarios & Roles</a>
                    @endrole
                    <a href="{{ route('ajustes.index') }}" @click="mobileSidebarOpen = false" class="block px-2 py-1.5 rounded text-xs text-slate-300 hover:bg-slate-800">Ajustes & Preferencias</a>
                </div>
            </div>
            @endif
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
