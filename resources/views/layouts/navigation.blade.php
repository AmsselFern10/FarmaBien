<nav x-data="{ open: false }" class="bg-white dark:bg-slate-800 border-b border-slate-200/80 dark:border-slate-700/80 shadow-sm sticky top-0 z-50 transition-colors duration-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <!-- Left Side: Logo & Main Navigation -->
            <div class="flex items-center space-x-3 lg:space-x-8">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center space-x-2.5 group">
                        <div class="w-10 h-10 bg-emerald-600 rounded-xl flex items-center justify-center text-white font-extrabold text-2xl shadow-md shadow-emerald-600/20 group-hover:bg-emerald-500 transition">
                            +
                        </div>
                        <span class="font-extrabold text-xl text-slate-900 dark:text-white tracking-tight">
                            Farma<span class="text-emerald-600 dark:text-emerald-400">Bien</span>
                        </span>
                    </a>
                </div>

                <!-- Desktop / Laptop Navigation Links (Organized & Uncluttered) -->
                <div class="hidden md:flex items-center space-x-1 lg:space-x-2">
                    <!-- Dashboard -->
                    <a href="{{ route('dashboard') }}" 
                       class="px-3 py-2 rounded-xl text-sm font-semibold transition {{ request()->routeIs('dashboard') ? 'bg-slate-100 dark:bg-slate-700 text-emerald-600 dark:text-emerald-400' : 'text-slate-700 dark:text-slate-200 hover:bg-slate-100/70 dark:hover:bg-slate-700/50' }}">
                        Dashboard
                    </a>

                    <!-- Ventas / POS (Highlighted) -->
                    @can('realizar ventas')
                    <a href="{{ route('ventas.create') }}" 
                       class="inline-flex items-center space-x-1.5 px-3 py-2 rounded-xl text-sm font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm transition transform active:scale-95">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>POS Venta</span>
                        <kbd class="px-1 py-0.2 bg-emerald-800 rounded text-[10px] font-mono">F2</kbd>
                    </a>
                    @endcan

                    @canany(['ver ventas', 'ver ventas propias'])
                    <a href="{{ route('ventas.index') }}" 
                       class="px-3 py-2 rounded-xl text-sm font-semibold transition {{ request()->routeIs('ventas.index') || request()->routeIs('ventas.show') ? 'bg-slate-100 dark:bg-slate-700 text-emerald-600 dark:text-emerald-400' : 'text-slate-700 dark:text-slate-200 hover:bg-slate-100/70 dark:hover:bg-slate-700/50' }}">
                        Ventas
                    </a>
                    @endcanany

                    <!-- Control de Cajas -->
                    @can('ver cajas')
                    <a href="{{ route('cajas.index') }}" 
                       class="px-3 py-2 rounded-xl text-sm font-semibold transition {{ request()->routeIs('cajas.*') ? 'bg-slate-100 dark:bg-slate-700 text-amber-600 dark:text-amber-400' : 'text-slate-700 dark:text-slate-200 hover:bg-slate-100/70 dark:hover:bg-slate-700/50' }}">
                        Cajas
                    </a>
                    @endcan

                    <!-- Compras -->
                    @can('ver compras')
                    <a href="{{ route('compras.index') }}" 
                       class="px-3 py-2 rounded-xl text-sm font-semibold transition {{ request()->routeIs('compras.*') ? 'bg-slate-100 dark:bg-slate-700 text-indigo-600 dark:text-indigo-400' : 'text-slate-700 dark:text-slate-200 hover:bg-slate-100/70 dark:hover:bg-slate-700/50' }}">
                        Compras
                    </a>
                    @endcan

                    <!-- Inventario Dropdown -->
                    @can('ver movimientos inventario')
                    <x-dropdown align="left" width="56">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center space-x-1 px-3 py-2 rounded-xl text-sm font-semibold transition {{ request()->routeIs('inventario.*') ? 'bg-slate-100 dark:bg-slate-700 text-slate-900 dark:text-white' : 'text-slate-700 dark:text-slate-200 hover:bg-slate-100/70 dark:hover:bg-slate-700/50' }}">
                                <span>Inventario</span>
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <x-dropdown-link :href="route('inventario.index')">📦 Monitor General</x-dropdown-link>
                            <x-dropdown-link :href="route('inventario.movimientos')">📑 Kardex de Movimientos</x-dropdown-link>
                            <x-dropdown-link :href="route('inventario.lotes')">⏳ Control de Lotes & Vencimientos</x-dropdown-link>
                            <x-dropdown-link :href="route('inventario.alertas')">⚠️ Alertas de Stock Bajo</x-dropdown-link>
                            @can('ajustar inventario')
                            <div class="border-t border-slate-100 dark:border-slate-700 my-1"></div>
                            <x-dropdown-link :href="route('inventario.ajustar')">🛠️ Registrar Ajuste Físico</x-dropdown-link>
                            @endcan
                        </x-slot>
                    </x-dropdown>
                    @endcan

                    <!-- Recetas -->
                    @can('ver recetas')
                    <a href="{{ route('recetas.index') }}" 
                       class="px-3 py-2 rounded-xl text-sm font-semibold transition {{ request()->routeIs('recetas.*') ? 'bg-slate-100 dark:bg-slate-700 text-teal-600 dark:text-teal-400' : 'text-slate-700 dark:text-slate-200 hover:bg-slate-100/70 dark:hover:bg-slate-700/50' }}">
                        Recetas
                    </a>
                    @endcan

                    <!-- Catálogos Dropdown -->
                    @canany(['ver productos', 'ver laboratorios', 'ver categorias', 'ver clientes', 'ver proveedores'])
                    <x-dropdown align="left" width="56">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center space-x-1 px-3 py-2 rounded-xl text-sm font-semibold transition {{ request()->routeIs('productos.*') || request()->routeIs('laboratorios.*') || request()->routeIs('categorias.*') || request()->routeIs('clientes.*') || request()->routeIs('proveedores.*') || request()->routeIs('presentaciones.*') ? 'bg-slate-100 dark:bg-slate-700 text-slate-900 dark:text-white' : 'text-slate-700 dark:text-slate-200 hover:bg-slate-100/70 dark:hover:bg-slate-700/50' }}">
                                <span>Catálogos</span>
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            @can('ver productos')<x-dropdown-link :href="route('productos.index')">💊 Medicamentos</x-dropdown-link>@endcan
                            @can('ver laboratorios')<x-dropdown-link :href="route('laboratorios.index')">🔬 Laboratorios</x-dropdown-link>@endcan
                            @can('ver categorias')<x-dropdown-link :href="route('categorias.index')">🏷️ Categorías</x-dropdown-link>@endcan
                            @can('ver productos')<x-dropdown-link :href="route('presentaciones.index')">📦 Presentaciones</x-dropdown-link>@endcan
                            @can('ver clientes')<x-dropdown-link :href="route('clientes.index')">👤 Clientes / Pacientes</x-dropdown-link>@endcan
                            @can('ver proveedores')<x-dropdown-link :href="route('proveedores.index')">🚚 Proveedores</x-dropdown-link>@endcan
                        </x-slot>
                    </x-dropdown>
                    @endcanany

                    <!-- Reportes -->
                    @can('ver reportes ventas')
                    <a href="{{ route('reportes.index') }}" 
                       class="px-3 py-2 rounded-xl text-sm font-semibold transition {{ request()->routeIs('reportes.*') ? 'bg-slate-100 dark:bg-slate-700 text-slate-900 dark:text-white' : 'text-slate-700 dark:text-slate-200 hover:bg-slate-100/70 dark:hover:bg-slate-700/50' }}">
                        Reportes
                    </a>
                    @endcan

                    <!-- Admin & Ajustes Dropdown -->
                    @canany(['ver usuarios', 'ver ajustes'])
                    <x-dropdown align="left" width="56">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center space-x-1 px-3 py-2 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.*') || request()->routeIs('usuarios.*') || request()->routeIs('ajustes.*') ? 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/60 dark:text-indigo-300' : 'text-slate-700 dark:text-slate-200 hover:bg-slate-100/70 dark:hover:bg-slate-700/50' }}">
                                <span>⚙️ Admin</span>
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            @role('Admin')<x-dropdown-link :href="route('usuarios.index')">👥 Usuarios & Roles</x-dropdown-link>@endrole
                            @can('ver ajustes')<x-dropdown-link :href="route('ajustes.index')">⚙️ Ajustes & Configuración</x-dropdown-link>@endcan
                            @if(configuracion('catalogo_publico_activo', true))
                                <x-dropdown-link :href="route('catalogo.publico')" target="_blank">🌐 Catálogo Público</x-dropdown-link>
                            @endif
                        </x-slot>
                    </x-dropdown>
                    @endcanany
                </div>
            </div>

            <!-- Right Side: Dark Mode Toggle, Shortcuts & User Profile -->
            <div class="hidden sm:flex sm:items-center space-x-3">
                <!-- Dark Mode Toggle Button -->
                <button @click="toggleDarkMode()" 
                        type="button" 
                        class="p-2 rounded-xl text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 transition"
                        :title="darkMode ? 'Cambiar a Modo Claro' : 'Cambiar a Modo Oscuro'">
                    <!-- Sun Icon (for Dark Mode active) -->
                    <svg x-show="darkMode" class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <!-- Moon Icon (for Light Mode active) -->
                    <svg x-show="!darkMode" class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                </button>

                <!-- Keyboard Shortcuts Button -->
                <button @click="showShortcutsModal = true" 
                        type="button"
                        class="px-2.5 py-1.5 rounded-xl text-xs font-bold text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 transition flex items-center space-x-1"
                        title="Ver atajos de teclado (F1)">
                    <span>⌨️</span>
                    <span class="hidden xl:inline">Atajos</span>
                </button>

                <!-- Role Badge -->
                @php
                    $roleName = Auth::user()->roles->first()?->name ?? 'Usuario';
                    $roleBadgeStyles = [
                        'Admin' => 'bg-indigo-100 text-indigo-800 border-indigo-200 dark:bg-indigo-950 dark:text-indigo-300 dark:border-indigo-800',
                        'Farmaceutico' => 'bg-teal-100 text-teal-800 border-teal-200 dark:bg-teal-950 dark:text-teal-300 dark:border-teal-800',
                        'Cajero' => 'bg-emerald-100 text-emerald-800 border-emerald-200 dark:bg-emerald-950 dark:text-emerald-300 dark:border-emerald-800',
                        'Inventario' => 'bg-purple-100 text-purple-800 border-purple-200 dark:bg-purple-950 dark:text-purple-300 dark:border-purple-800',
                    ][$roleName] ?? 'bg-slate-100 text-slate-800 border-slate-200 dark:bg-slate-700 dark:text-slate-300 dark:border-slate-600';
                @endphp
                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold border {{ $roleBadgeStyles }}">
                    {{ $roleName }}
                </span>

                <!-- User Profile Dropdown -->
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center space-x-2 px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 text-sm font-bold text-slate-800 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition">
                            <div class="w-6 h-6 rounded-full bg-emerald-600 text-white flex items-center justify-center text-xs font-bold">
                                {{ substr(Auth::user()->name, 0, 1) }}
                            </div>
                            <span class="max-w-[120px] truncate">{{ Auth::user()->name }}</span>
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">👤 Mi Perfil</x-dropdown-link>
                        @can('ver ajustes')
                            <x-dropdown-link :href="route('ajustes.index')">⚙️ Ajustes</x-dropdown-link>
                        @endcan
                        <div class="border-t border-slate-100 dark:border-slate-700 my-1"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();" class="text-rose-600 dark:text-rose-400 font-semibold">
                                🚪 Cerrar Sesión
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger Mobile Button -->
            <div class="flex items-center space-x-2 sm:hidden">
                <button @click="toggleDarkMode()" type="button" class="p-2 text-slate-500 hover:text-slate-700 dark:text-slate-300">
                    <svg x-show="darkMode" class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <svg x-show="!darkMode" class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                </button>

                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-xl text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Menu Drawer -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-white dark:bg-slate-800 border-t border-slate-200 dark:border-slate-700 px-4 pt-3 pb-6 space-y-2">
        <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">📊 Dashboard</x-responsive-nav-link>
        @can('realizar ventas')<x-responsive-nav-link :href="route('ventas.create')" :active="request()->routeIs('ventas.create')" class="text-emerald-600 dark:text-emerald-400 font-bold">⚡ Punto de Venta (POS)</x-responsive-nav-link>@endcan
        @canany(['ver ventas', 'ver ventas propias'])<x-responsive-nav-link :href="route('ventas.index')" :active="request()->routeIs('ventas.index')">💰 Historial Ventas</x-responsive-nav-link>@endcanany
        @can('ver cajas')<x-responsive-nav-link :href="route('cajas.index')" :active="request()->routeIs('cajas.*')">💵 Control de Cajas</x-responsive-nav-link>@endcan
        @can('ver compras')<x-responsive-nav-link :href="route('compras.index')" :active="request()->routeIs('compras.*')">🚚 Compras</x-responsive-nav-link>@endcan
        @can('ver movimientos inventario')<x-responsive-nav-link :href="route('inventario.index')" :active="request()->routeIs('inventario.*')">📦 Inventario & Kardex</x-responsive-nav-link>@endcan
        @can('ver recetas')<x-responsive-nav-link :href="route('recetas.index')" :active="request()->routeIs('recetas.*')">📋 Recetas Médicas</x-responsive-nav-link>@endcan
        @can('ver productos')<x-responsive-nav-link :href="route('productos.index')" :active="request()->routeIs('productos.*')">💊 Medicamentos</x-responsive-nav-link>@endcan
        @can('ver laboratorios')<x-responsive-nav-link :href="route('laboratorios.index')" :active="request()->routeIs('laboratorios.*')">🔬 Laboratorios</x-responsive-nav-link>@endcan
        @can('ver reportes ventas')<x-responsive-nav-link :href="route('reportes.index')" :active="request()->routeIs('reportes.*')">📈 Reportes Gerenciales</x-responsive-nav-link>@endcan
        @role('Admin')<x-responsive-nav-link :href="route('usuarios.index')" :active="request()->routeIs('usuarios.*')">👥 Usuarios & Roles</x-responsive-nav-link>@endrole
        @can('ver ajustes')<x-responsive-nav-link :href="route('ajustes.index')" :active="request()->routeIs('ajustes.*')">⚙️ Ajustes & Configuración</x-responsive-nav-link>@endcan

        <div class="pt-4 border-t border-slate-200 dark:border-slate-700">
            <div class="font-bold text-slate-800 dark:text-slate-200">{{ Auth::user()->name }} ({{ Auth::user()->roles->first()?->name }})</div>
            <div class="text-xs text-slate-500">{{ Auth::user()->email }}</div>
            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">👤 Mi Perfil</x-responsive-nav-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();" class="text-rose-600 dark:text-rose-400 font-bold">
                        🚪 Cerrar Sesión
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
