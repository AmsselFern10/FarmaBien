<!-- Desktop Collapsible Sidebar with Accordion Submenus -->
<aside x-show="!posFullscreen"
       :class="sidebarCollapsed ? 'w-16' : 'w-64'"
       x-data="{
           openMenus: {
               operaciones: {{ request()->routeIs('ventas.*') || request()->routeIs('compras.*') || request()->routeIs('recetas.*') || request()->routeIs('cajas.*') ? 'true' : 'false' }},
               inventario: {{ request()->routeIs('inventario.*') ? 'true' : 'false' }},
               catalogos: {{ request()->routeIs('productos.*') || request()->routeIs('laboratorios.*') || request()->routeIs('categorias.*') || request()->routeIs('clientes.*') || request()->routeIs('proveedores.*') || request()->routeIs('presentaciones.*') ? 'true' : 'false' }},
               administracion: {{ request()->routeIs('reportes.*') || request()->routeIs('usuarios.*') || request()->routeIs('admin.*') || request()->routeIs('ajustes.*') ? 'true' : 'false' }}
           },
           toggleMenu(menu) {
               if (this.sidebarCollapsed) {
                   this.sidebarCollapsed = false;
                   localStorage.setItem('farma_sidebar_collapsed', 'false');
                   this.openMenus[menu] = true;
               } else {
                   this.openMenus[menu] = !this.openMenus[menu];
               }
           }
       }"
       class="hidden md:flex md:flex-col bg-slate-900 border-r border-slate-800 shrink-0 z-30 select-none transition-all duration-300 ease-in-out">
    
    <!-- Brand & Collapse Header -->
    <div class="h-16 flex items-center justify-between px-3.5 border-b border-slate-800 bg-slate-950/40">
        <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 overflow-hidden">
            <div class="w-9 h-9 rounded-lg bg-emerald-600 flex items-center justify-center text-white font-black text-xl shadow-sm shrink-0">
                +
            </div>
            <span x-show="!sidebarCollapsed" 
                  x-transition:enter="transition ease-out duration-200"
                  x-transition:enter-start="opacity-0 -translate-x-2"
                  x-transition:enter-end="opacity-100 translate-x-0"
                  class="font-bold text-lg text-white tracking-tight whitespace-nowrap">
                Farma<span class="text-emerald-400">Bien</span>
            </span>
        </a>

        <!-- Toggle Collapse Button -->
        <button @click="toggleSidebar()" 
                type="button" 
                class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition shrink-0"
                :title="sidebarCollapsed ? 'Expandir menú lateral' : 'Colapsar menú lateral'">
            <svg class="w-4 h-4 transition-transform duration-300" 
                 :class="sidebarCollapsed ? 'rotate-180' : ''"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
            </svg>
        </button>
    </div>

    <!-- Navigation Links -->
    <div class="flex-1 overflow-y-auto px-2 py-4 space-y-3 scrollbar-hide">
        
        <!-- Direct Item: Dashboard -->
        <div>
            <a href="{{ route('dashboard') }}" 
               title="Panel Principal"
               class="flex items-center px-2.5 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('dashboard') ? 'bg-emerald-600/20 text-emerald-400 border border-emerald-500/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('dashboard') ? 'text-emerald-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span x-show="!sidebarCollapsed" class="ml-3 whitespace-nowrap">Dashboard</span>
            </a>
        </div>

        <!-- Direct Item: Punto de Venta (POS) -->
        @can('realizar ventas')
        <div>
            <a href="{{ route('ventas.create') }}" 
               title="Punto de Venta (POS) - Tecla F2"
               class="flex items-center justify-between px-2.5 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('ventas.create') ? 'bg-emerald-600 text-white shadow-sm' : 'bg-emerald-950/40 text-emerald-300 border border-emerald-800/60 hover:bg-emerald-900/50' }}">
                <div class="flex items-center min-w-0">
                    <svg class="w-4 h-4 shrink-0 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="ml-3 whitespace-nowrap font-bold">Punto de Venta</span>
                </div>
                <kbd x-show="!sidebarCollapsed" class="px-1.5 py-0.5 text-[9px] font-mono bg-emerald-900/80 border border-emerald-700 text-emerald-200 rounded">F2</kbd>
            </a>
        </div>
        @endcan

        <!-- Accordion 1: Operaciones / Transacciones -->
        @canany(['ver ventas', 'ver ventas propias', 'ver compras', 'ver recetas', 'ver cajas'])
        <div class="space-y-1">
            <button @click="toggleMenu('operaciones')" 
                    type="button" 
                    title="Operaciones y Transacciones"
                    class="w-full flex items-center justify-between px-2.5 py-2 rounded-lg text-xs font-semibold text-slate-400 hover:text-slate-200 hover:bg-slate-800/60 transition {{ request()->routeIs('ventas.*') || request()->routeIs('compras.*') || request()->routeIs('recetas.*') || request()->routeIs('cajas.*') ? 'text-slate-200 bg-slate-800/40' : '' }}">
                <div class="flex items-center min-w-0">
                    <svg class="w-4 h-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="ml-3 whitespace-nowrap">Operaciones</span>
                </div>
                <svg x-show="!sidebarCollapsed" 
                     class="w-3.5 h-3.5 text-slate-500 transition-transform duration-200" 
                     :class="openMenus.operaciones ? 'rotate-180' : ''"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <!-- Submenu -->
            <div x-show="openMenus.operaciones && !sidebarCollapsed" 
                 x-collapse
                 class="pl-4 pr-1 py-1 space-y-1 border-l-2 border-slate-800 ml-3.5">
                @canany(['ver ventas', 'ver ventas propias'])
                <a href="{{ route('ventas.index') }}" 
                   class="flex items-center px-2 py-1.5 rounded-md text-xs font-medium transition {{ request()->routeIs('ventas.index') || request()->routeIs('ventas.show') ? 'bg-emerald-600/20 text-emerald-400 font-semibold' : 'text-slate-400 hover:bg-slate-800/80 hover:text-white' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-500 mr-2 {{ request()->routeIs('ventas.index') || request()->routeIs('ventas.show') ? '!bg-emerald-400' : '' }}"></span>
                    <span>Historial Ventas</span>
                </a>
                @endcanany

                @can('ver cajas')
                <a href="{{ route('cajas.index') }}" 
                   class="flex items-center px-2 py-1.5 rounded-md text-xs font-medium transition {{ request()->routeIs('cajas.*') ? 'bg-emerald-600/20 text-emerald-400 font-semibold' : 'text-slate-400 hover:bg-slate-800/80 hover:text-white' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-500 mr-2 {{ request()->routeIs('cajas.*') ? '!bg-emerald-400' : '' }}"></span>
                    <span>Control de Cajas</span>
                </a>
                @endcan

                @can('ver compras')
                <a href="{{ route('compras.index') }}" 
                   class="flex items-center justify-between px-2 py-1.5 rounded-md text-xs font-medium transition {{ request()->routeIs('compras.*') ? 'bg-emerald-600/20 text-emerald-400 font-semibold' : 'text-slate-400 hover:bg-slate-800/80 hover:text-white' }}">
                    <div class="flex items-center">
                        <span class="w-1.5 h-1.5 rounded-full bg-slate-500 mr-2 {{ request()->routeIs('compras.*') ? '!bg-emerald-400' : '' }}"></span>
                        <span>Compras & Lotes</span>
                    </div>
                    <kbd class="px-1 py-0.2 text-[9px] font-mono bg-slate-800 text-slate-400 rounded">F4</kbd>
                </a>
                @endcan

                @can('ver recetas')
                <a href="{{ route('recetas.index') }}" 
                   class="flex items-center px-2 py-1.5 rounded-md text-xs font-medium transition {{ request()->routeIs('recetas.*') ? 'bg-emerald-600/20 text-emerald-400 font-semibold' : 'text-slate-400 hover:bg-slate-800/80 hover:text-white' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-500 mr-2 {{ request()->routeIs('recetas.*') ? '!bg-emerald-400' : '' }}"></span>
                    <span>Recetas Médicas</span>
                </a>
                @endcan
            </div>
        </div>
        @endcanany

        <!-- Accordion 2: Inventario & Kardex -->
        @can('ver movimientos inventario')
        <div class="space-y-1">
            <button @click="toggleMenu('inventario')" 
                    type="button" 
                    title="Control de Inventario y Kardex"
                    class="w-full flex items-center justify-between px-2.5 py-2 rounded-lg text-xs font-semibold text-slate-400 hover:text-slate-200 hover:bg-slate-800/60 transition {{ request()->routeIs('inventario.*') ? 'text-slate-200 bg-slate-800/40' : '' }}">
                <div class="flex items-center min-w-0">
                    <svg class="w-4 h-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="ml-3 whitespace-nowrap">Inventario</span>
                </div>
                <svg x-show="!sidebarCollapsed" 
                     class="w-3.5 h-3.5 text-slate-500 transition-transform duration-200" 
                     :class="openMenus.inventario ? 'rotate-180' : ''"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <!-- Submenu -->
            <div x-show="openMenus.inventario && !sidebarCollapsed" 
                 x-collapse
                 class="pl-4 pr-1 py-1 space-y-1 border-l-2 border-slate-800 ml-3.5">
                <a href="{{ route('inventario.index') }}" 
                   class="flex items-center px-2 py-1.5 rounded-md text-xs font-medium transition {{ request()->routeIs('inventario.index') ? 'bg-emerald-600/20 text-emerald-400 font-semibold' : 'text-slate-400 hover:bg-slate-800/80 hover:text-white' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-500 mr-2 {{ request()->routeIs('inventario.index') ? '!bg-emerald-400' : '' }}"></span>
                    <span>Monitor de Stock</span>
                </a>

                <a href="{{ route('inventario.movimientos') }}" 
                   class="flex items-center px-2 py-1.5 rounded-md text-xs font-medium transition {{ request()->routeIs('inventario.movimientos') ? 'bg-emerald-600/20 text-emerald-400 font-semibold' : 'text-slate-400 hover:bg-slate-800/80 hover:text-white' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-500 mr-2 {{ request()->routeIs('inventario.movimientos') ? '!bg-emerald-400' : '' }}"></span>
                    <span>Kardex Físico</span>
                </a>

                <a href="{{ route('inventario.lotes') }}" 
                   class="flex items-center px-2 py-1.5 rounded-md text-xs font-medium transition {{ request()->routeIs('inventario.lotes') ? 'bg-emerald-600/20 text-emerald-400 font-semibold' : 'text-slate-400 hover:bg-slate-800/80 hover:text-white' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-500 mr-2 {{ request()->routeIs('inventario.lotes') ? '!bg-emerald-400' : '' }}"></span>
                    <span>Lotes y Vencimientos</span>
                </a>

                <a href="{{ route('inventario.alertas') }}" 
                   class="flex items-center px-2 py-1.5 rounded-md text-xs font-medium transition {{ request()->routeIs('inventario.alertas') ? 'bg-emerald-600/20 text-emerald-400 font-semibold' : 'text-slate-400 hover:bg-slate-800/80 hover:text-white' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-500 mr-2 {{ request()->routeIs('inventario.alertas') ? '!bg-emerald-400' : '' }}"></span>
                    <span>Alertas de Stock</span>
                </a>

                @can('ajustar inventario')
                <a href="{{ route('inventario.ajustar') }}" 
                   class="flex items-center px-2 py-1.5 rounded-md text-xs font-medium transition {{ request()->routeIs('inventario.ajustar*') ? 'bg-indigo-600/20 text-indigo-400 font-semibold' : 'text-slate-400 hover:bg-slate-800/80 hover:text-white' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-500 mr-2 {{ request()->routeIs('inventario.ajustar*') ? '!bg-indigo-400' : '' }}"></span>
                    <span>Ajustar Stock</span>
                </a>
                @endcan
            </div>
        </div>
        @endcan

        <!-- Accordion 3: Catálogos Maestros -->
        @canany(['ver productos', 'ver laboratorios', 'ver categorias', 'ver clientes', 'ver proveedores', 'ver promociones'])
        <div class="space-y-1">
            <button @click="toggleMenu('catalogos')" 
                    type="button" 
                    title="Catálogos del Sistema"
                    class="w-full flex items-center justify-between px-2.5 py-2 rounded-lg text-xs font-semibold text-slate-400 hover:text-slate-200 hover:bg-slate-800/60 transition {{ request()->routeIs('productos.*') || request()->routeIs('laboratorios.*') || request()->routeIs('categorias.*') || request()->routeIs('clientes.*') || request()->routeIs('proveedores.*') || request()->routeIs('promociones.*') ? 'text-slate-200 bg-slate-800/40' : '' }}">
                <div class="flex items-center min-w-0">
                    <svg class="w-4 h-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="ml-3 whitespace-nowrap">Catálogos</span>
                </div>
                <svg x-show="!sidebarCollapsed" 
                     class="w-3.5 h-3.5 text-slate-500 transition-transform duration-200" 
                     :class="openMenus.catalogos ? 'rotate-180' : ''"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <!-- Submenu -->
            <div x-show="openMenus.catalogos && !sidebarCollapsed" 
                 x-collapse
                 class="pl-4 pr-1 py-1 space-y-1 border-l-2 border-slate-800 ml-3.5">
                @can('ver productos')
                <a href="{{ route('productos.index') }}" 
                   class="flex items-center px-2 py-1.5 rounded-md text-xs font-medium transition {{ request()->routeIs('productos.*') ? 'bg-emerald-600/20 text-emerald-400 font-semibold' : 'text-slate-400 hover:bg-slate-800/80 hover:text-white' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-500 mr-2 {{ request()->routeIs('productos.*') ? '!bg-emerald-400' : '' }}"></span>
                    <span>Medicamentos</span>
                </a>
                @endcan

                @can('ver promociones')
                <a href="{{ route('promociones.index') }}" 
                   class="flex items-center px-2 py-1.5 rounded-md text-xs font-medium transition {{ request()->routeIs('promociones.*') ? 'bg-emerald-600/20 text-emerald-400 font-semibold' : 'text-slate-400 hover:bg-slate-800/80 hover:text-white' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-500 mr-2 {{ request()->routeIs('promociones.*') ? '!bg-emerald-400' : '' }}"></span>
                    <span class="flex items-center space-x-1.5">
                        <span>Promociones & Descuentos</span>
                        <span class="text-[9px] px-1 py-0.2 rounded bg-rose-500/20 text-rose-400 font-bold">Ofertas</span>
                    </span>
                </a>
                @endcan

                @can('ver laboratorios')
                <a href="{{ route('laboratorios.index') }}" 
                   class="flex items-center px-2 py-1.5 rounded-md text-xs font-medium transition {{ request()->routeIs('laboratorios.*') ? 'bg-emerald-600/20 text-emerald-400 font-semibold' : 'text-slate-400 hover:bg-slate-800/80 hover:text-white' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-500 mr-2 {{ request()->routeIs('laboratorios.*') ? '!bg-emerald-400' : '' }}"></span>
                    <span>Laboratorios</span>
                </a>
                @endcan

                @can('ver categorias')
                <a href="{{ route('categorias.index') }}" 
                   class="flex items-center px-2 py-1.5 rounded-md text-xs font-medium transition {{ request()->routeIs('categorias.*') ? 'bg-emerald-600/20 text-emerald-400 font-semibold' : 'text-slate-400 hover:bg-slate-800/80 hover:text-white' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-500 mr-2 {{ request()->routeIs('categorias.*') ? '!bg-emerald-400' : '' }}"></span>
                    <span>Categorías</span>
                </a>
                @endcan

                @can('ver productos')
                <a href="{{ route('presentaciones.index') }}" 
                   class="flex items-center px-2 py-1.5 rounded-md text-xs font-medium transition {{ request()->routeIs('presentaciones.*') ? 'bg-emerald-600/20 text-emerald-400 font-semibold' : 'text-slate-400 hover:bg-slate-800/80 hover:text-white' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-500 mr-2 {{ request()->routeIs('presentaciones.*') ? '!bg-emerald-400' : '' }}"></span>
                    <span>Presentaciones</span>
                </a>
                @endcan

                @can('ver proveedores')
                <a href="{{ route('proveedores.index') }}" 
                   class="flex items-center px-2 py-1.5 rounded-md text-xs font-medium transition {{ request()->routeIs('proveedores.*') ? 'bg-emerald-600/20 text-emerald-400 font-semibold' : 'text-slate-400 hover:bg-slate-800/80 hover:text-white' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-500 mr-2 {{ request()->routeIs('proveedores.*') ? '!bg-emerald-400' : '' }}"></span>
                    <span>Proveedores</span>
                </a>
                @endcan

                @can('ver clientes')
                <a href="{{ route('clientes.index') }}" 
                   class="flex items-center px-2 py-1.5 rounded-md text-xs font-medium transition {{ request()->routeIs('clientes.*') ? 'bg-emerald-600/20 text-emerald-400 font-semibold' : 'text-slate-400 hover:bg-slate-800/80 hover:text-white' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-500 mr-2 {{ request()->routeIs('clientes.*') ? '!bg-emerald-400' : '' }}"></span>
                    <span>Clientes / Pacientes</span>
                </a>
                @endcan
            </div>
        </div>
        @endcanany

        <!-- Accordion 4: Administración y Reportes -->
        @canany(['ver reportes ventas', 'ver reportes inventario', 'ver reportes compras', 'ver usuarios', 'ver ajustes'])
        <div class="space-y-1">
            <button @click="toggleMenu('administracion')" 
                    type="button" 
                    title="Administración y Reportes"
                    class="w-full flex items-center justify-between px-2.5 py-2 rounded-lg text-xs font-semibold text-slate-400 hover:text-slate-200 hover:bg-slate-800/60 transition {{ request()->routeIs('reportes.*') || request()->routeIs('usuarios.*') || request()->routeIs('admin.*') || request()->routeIs('ajustes.*') ? 'text-slate-200 bg-slate-800/40' : '' }}">
                <div class="flex items-center min-w-0">
                    <svg class="w-4 h-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="ml-3 whitespace-nowrap">Administración</span>
                </div>
                <svg x-show="!sidebarCollapsed" 
                     class="w-3.5 h-3.5 text-slate-500 transition-transform duration-200" 
                     :class="openMenus.administracion ? 'rotate-180' : ''"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <!-- Submenu -->
            <div x-show="openMenus.administracion && !sidebarCollapsed" 
                 x-collapse
                 class="pl-4 pr-1 py-1 space-y-1 border-l-2 border-slate-800 ml-3.5">
                @canany(['ver reportes ventas', 'ver reportes inventario', 'ver reportes compras'])
                <a href="{{ route('reportes.index') }}" 
                   class="flex items-center px-2 py-1.5 rounded-md text-xs font-medium transition {{ request()->routeIs('reportes.*') ? 'bg-emerald-600/20 text-emerald-400 font-semibold' : 'text-slate-400 hover:bg-slate-800/80 hover:text-white' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-500 mr-2 {{ request()->routeIs('reportes.*') ? '!bg-emerald-400' : '' }}"></span>
                    <span>Reportes Gerenciales</span>
                </a>
                @endcan

                @role('Admin')
                <a href="{{ route('usuarios.index') }}" 
                   class="flex items-center px-2 py-1.5 rounded-md text-xs font-medium transition {{ request()->routeIs('usuarios.*') || request()->routeIs('admin.*') ? 'bg-emerald-600/20 text-emerald-400 font-semibold' : 'text-slate-400 hover:bg-slate-800/80 hover:text-white' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-500 mr-2 {{ request()->routeIs('usuarios.*') || request()->routeIs('admin.*') ? '!bg-emerald-400' : '' }}"></span>
                    <span>Usuarios & Roles</span>
                </a>
                @endrole

                @can('ver ajustes')
                <a href="{{ route('ajustes.index') }}" 
                   class="flex items-center px-2 py-1.5 rounded-md text-xs font-medium transition {{ request()->routeIs('ajustes.*') ? 'bg-emerald-600/20 text-emerald-400 font-semibold' : 'text-slate-400 hover:bg-slate-800/80 hover:text-white' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-500 mr-2 {{ request()->routeIs('ajustes.*') ? '!bg-emerald-400' : '' }}"></span>
                    <span>Ajustes del Sistema</span>
                </a>
                @endcan
            </div>
        </div>
        @endcanany

    </div>

    <!-- User Profile Footer -->
    <div class="p-3 border-t border-slate-800 bg-slate-950/40 overflow-hidden">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2.5 min-w-0">
                <div class="w-8 h-8 rounded-lg bg-slate-800 border border-slate-700 text-emerald-400 flex items-center justify-center font-bold text-xs shrink-0">
                    {{ substr(Auth::user()->name, 0, 1) }}
                </div>
                <div x-show="!sidebarCollapsed" class="truncate">
                    <p class="text-xs font-semibold text-white truncate">{{ Auth::user()->name }}</p>
                    <p class="text-[10px] text-slate-400 truncate">{{ Auth::user()->roles->first()?->name ?? 'Usuario' }}</p>
                </div>
            </div>
            <form x-show="!sidebarCollapsed" method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-400 transition" title="Cerrar Sesión">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                </button>
            </form>
        </div>
    </div>
</aside>
