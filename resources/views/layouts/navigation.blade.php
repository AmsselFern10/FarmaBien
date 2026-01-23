<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="text-xl font-bold text-blue-600">
                        💊 FarmaBien
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                    <!-- Dashboard -->
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        Dashboard
                    </x-nav-link>

                    <!-- Ventas -->
                    @can('realizar ventas')
                        <x-nav-link :href="route('ventas.create')" :active="request()->routeIs('ventas.create')">
                            Nueva Venta
                        </x-nav-link>
                    @endcan

                    @can('ver ventas')
                        <x-nav-link :href="route('ventas.index')" :active="request()->routeIs('ventas.*')">
                            Ventas
                        </x-nav-link>
                    @endcan

                    <!-- Compras -->
                    @can('registrar compras')
                        <x-nav-link :href="route('compras.index')" :active="request()->routeIs('compras.*')">
                            Compras
                        </x-nav-link>
                    @endcan

                    <!-- Inventario -->
                    @can('ver movimientos inventario')
                        <x-nav-link :href="route('inventario.index')" :active="request()->routeIs('inventario.*')">
                            Inventario
                        </x-nav-link>
                    @endcan

                    <!-- Productos (dropdown) -->
                    @canany(['ver productos', 'ver categorias'])
                        <div class="hidden sm:flex sm:items-center">
                            <x-dropdown align="right" width="48">
                                <x-slot name="trigger">
                                    <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition">
                                        <div>Catálogo</div>
                                        <svg class="ml-2 -mr-0.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                </x-slot>
                                <x-slot name="content">
                                    @can('ver productos')
                                        <x-dropdown-link :href="route('productos.index')">
                                            Productos
                                        </x-dropdown-link>
                                    @endcan
                                    @can('ver categorias')
                                        <x-dropdown-link :href="route('categorias.index')">
                                            Categorías
                                        </x-dropdown-link>
                                    @endcan
                                </x-slot>
                            </x-dropdown>
                        </div>
                    @endcanany

                    <!-- Gestión -->
                    @canany(['ver clientes', 'ver proveedores', 'ver recetas'])
                        <div class="hidden sm:flex sm:items-center">
                            <x-dropdown align="right" width="48">
                                <x-slot name="trigger">
                                    <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition">
                                        <div>Gestión</div>
                                        <svg class="ml-2 -mr-0.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                </x-slot>
                                <x-slot name="content">
                                    @can('ver clientes')
                                        <x-dropdown-link :href="route('clientes.index')">
                                            Clientes
                                        </x-dropdown-link>
                                    @endcan
                                    @can('ver proveedores')
                                        <x-dropdown-link :href="route('proveedores.index')">
                                            Proveedores
                                        </x-dropdown-link>
                                    @endcan
                                    @can('ver recetas')
                                        <x-dropdown-link :href="route('recetas.index')">
                                            Recetas Médicas
                                        </x-dropdown-link>
                                    @endcan
                                </x-slot>
                            </x-dropdown>
                        </div>
                    @endcanany

                    <!-- Reportes -->
                    @canany(['ver reportes ventas', 'ver reportes compras', 'ver reportes inventario'])
                        <x-nav-link :href="route('reportes.index')" :active="request()->routeIs('reportes.*')">
                            Reportes
                        </x-nav-link>
                    @endcanany
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ml-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition">
                            <div>{{ Auth::user()->name }}</div>
                            <svg class="ml-1 h-4 w-4 fill-current" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </x-slot>
                    <x-slot name="content">
                        <div class="px-4 py-2 text-xs text-gray-400 border-b">
                            {{ Auth::user()->roles->pluck('name')->join(', ') }}
                        </div>

                        <!-- Usuarios (Solo Admin) -->
                        @role('Admin')
                            <x-dropdown-link :href="route('usuarios.index')">
                                Usuarios
                            </x-dropdown-link>
                        @endrole

                        <x-dropdown-link :href="route('profile.edit')">
                            Mi Perfil
                        </x-dropdown-link>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                Cerrar Sesión
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-mr-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none transition">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                Dashboard
            </x-responsive-nav-link>

            @can('realizar ventas')
                <x-responsive-nav-link :href="route('ventas.create')" :active="request()->routeIs('ventas.create')">
                    Nueva Venta
                </x-responsive-nav-link>
            @endcan

            @can('ver ventas')
                <x-responsive-nav-link :href="route('ventas.index')" :active="request()->routeIs('ventas.*')">
                    Ventas
                </x-responsive-nav-link>
            @endcan

            @can('registrar compras')
                <x-responsive-nav-link :href="route('compras.index')" :active="request()->routeIs('compras.*')">
                    Compras
                </x-responsive-nav-link>
            @endcan

            @can('ver productos')
                <x-responsive-nav-link :href="route('productos.index')" :active="request()->routeIs('productos.*')">
                    Productos
                </x-responsive-nav-link>
            @endcan

            @can('ver categorias')
                <x-responsive-nav-link :href="route('categorias.index')" :active="request()->routeIs('categorias.*')">
                    Categorías
                </x-responsive-nav-link>
            @endcan

            @can('ver usuarios')
                <x-responsive-nav-link :href="route('usuarios.index')" :active="request()->routeIs('usuarios.*')">
                    Usuarios
                </x-responsive-nav-link>
            @endcan
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    Mi Perfil
                </x-responsive-nav-link>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                        Cerrar Sesión
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>