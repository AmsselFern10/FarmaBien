<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' || false, sidebarOpen: true, sidebarExpanded: true }" x-init="$watch('darkMode', val => localStorage.setItem('darkMode', val)); $watch('sidebarExpanded', val => localStorage.setItem('sidebarExpanded', val)); if (localStorage.getItem('sidebarExpanded') !== null) { sidebarExpanded = localStorage.getItem('sidebarExpanded') === 'true' }" :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'FarmaBien') }} - @yield('title', 'Sistema de Farmacia')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    @stack('styles')
    
    <style>
        /* Scrollbar personalizado */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
        
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }
        
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Dark mode scrollbar */
        .dark .custom-scrollbar::-webkit-scrollbar-track {
            background: #1e293b;
        }
        
        .dark .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #475569;
        }
        
        .dark .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #64748b;
        }

        /* Overlay para mobile */
        .sidebar-overlay {
            position: fixed;
            inset: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 35;
        }

        /* Tooltip para modo colapsado */
        .tooltip {
            position: absolute;
            left: 100%;
            margin-left: 0.5rem;
            padding: 0.5rem 0.75rem;
            background-color: #1f2937;
            color: white;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s;
            z-index: 50;
        }

        .dark .tooltip {
            background-color: #374151;
        }

        .group:hover .tooltip {
            opacity: 1;
        }
    </style>
</head>
<body class="font-sans antialiased bg-gray-50 dark:bg-gray-900 transition-colors duration-300">
    <div class="min-h-screen flex">
        
        <!-- Overlay para cerrar sidebar en mobile -->
        <div 
            x-show="sidebarOpen && window.innerWidth < 1024" 
            @click="sidebarOpen = false"
            x-transition:enter="transition-opacity ease-linear duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="sidebar-overlay lg:hidden"
        ></div>

        <!-- Sidebar -->
        <aside 
            x-show="sidebarOpen || window.innerWidth >= 1024"
            x-transition:enter="transition ease-out duration-300 transform"
            x-transition:enter-start="-translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-300 transform"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="-translate-x-full"
            @click.away="if (window.innerWidth < 1024) sidebarOpen = false"
            :class="sidebarExpanded ? 'w-64' : 'w-20'"
            class="fixed lg:sticky top-0 left-0 z-40 h-screen bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 shadow-xl transition-all duration-300"
        >
            <div class="flex flex-col h-full">
                <!-- Logo -->
                <div class="flex items-center justify-between h-16 px-4 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
                    <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 min-w-0">
                        <div class="flex items-center justify-center w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-md flex-shrink-0">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                            </svg>
                        </div>
                        <span x-show="sidebarExpanded" class="text-xl font-bold text-slate-900 dark:text-white transition-opacity duration-300 truncate">
                            FarmaBien
                        </span>
                    </a>
                    <!-- Botón toggle sidebar (SIEMPRE VISIBLE) -->
                    <button 
                        @click="sidebarExpanded = !sidebarExpanded" 
                        class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200 flex-shrink-0"
                        :class="sidebarExpanded ? '' : 'lg:mx-auto'"
                    >
                        <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                </div>

                <!-- Navigation Menu (Scrollable) -->
                <nav class="flex-1 overflow-y-auto custom-scrollbar py-4 px-3 space-y-2">
                    
                    <!-- Dashboard -->
                    <div class="relative group">
                        <a href="{{ route('dashboard') }}" 
                           @click="if (window.innerWidth < 1024) sidebarOpen = false"
                           :class="sidebarExpanded ? 'justify-start' : 'justify-center'"
                           class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition-all duration-200 {{ request()->routeIs('dashboard') ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400' : 'text-slate-700 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            </svg>
                            <span x-show="sidebarExpanded" class="font-medium">Dashboard</span>
                        </a>
                        <span x-show="!sidebarExpanded" class="tooltip">Dashboard</span>
                    </div>

                    <!-- Ventas (Accordion) -->
                    @canany(['realizar ventas', 'ver ventas'])
                    <div x-data="{ open: {{ request()->routeIs('ventas.*') ? 'true' : 'false' }} }" class="relative group">
                        <button @click="open = !open; if (!sidebarExpanded) sidebarExpanded = true" 
                                :class="sidebarExpanded ? 'justify-between' : 'justify-center'"
                                class="w-full flex items-center px-3 py-2.5 rounded-lg text-slate-700 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-200">
                            <div class="flex items-center space-x-3">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                <span x-show="sidebarExpanded" class="font-medium">Ventas</span>
                            </div>
                            <svg x-show="sidebarExpanded" :class="open ? 'rotate-180' : ''" class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <span x-show="!sidebarExpanded" class="tooltip">Ventas</span>
                        <div x-show="open && sidebarExpanded" x-collapse class="ml-8 mt-2 space-y-1">
                            @can('realizar ventas')
                            <a href="{{ route('ventas.create') }}" 
                               @click="if (window.innerWidth < 1024) sidebarOpen = false"
                               class="block px-3 py-2 text-sm rounded-lg transition-colors duration-200 {{ request()->routeIs('ventas.create') ? 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/30' : 'text-slate-600 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                                Nueva Venta
                            </a>
                            @endcan
                            @can('ver ventas')
                            <a href="{{ route('ventas.index') }}" 
                               @click="if (window.innerWidth < 1024) sidebarOpen = false"
                               class="block px-3 py-2 text-sm rounded-lg transition-colors duration-200 {{ request()->routeIs('ventas.index') ? 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/30' : 'text-slate-600 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                                Historial
                            </a>
                            @endcan
                        </div>
                    </div>
                    @endcanany

                    <!-- Compras (Accordion) -->
                    @can('registrar compras')
                    <div x-data="{ open: {{ request()->routeIs('compras.*') ? 'true' : 'false' }} }" class="relative group">
                        <button @click="open = !open; if (!sidebarExpanded) sidebarExpanded = true" 
                                :class="sidebarExpanded ? 'justify-between' : 'justify-center'"
                                class="w-full flex items-center px-3 py-2.5 rounded-lg text-slate-700 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-200">
                            <div class="flex items-center space-x-3">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                </svg>
                                <span x-show="sidebarExpanded" class="font-medium">Compras</span>
                            </div>
                            <svg x-show="sidebarExpanded" :class="open ? 'rotate-180' : ''" class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <span x-show="!sidebarExpanded" class="tooltip">Compras</span>
                        <div x-show="open && sidebarExpanded" x-collapse class="ml-8 mt-2 space-y-1">
                            <a href="{{ route('compras.create') }}" 
                               @click="if (window.innerWidth < 1024) sidebarOpen = false"
                               class="block px-3 py-2 text-sm rounded-lg transition-colors duration-200 {{ request()->routeIs('compras.create') ? 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/30' : 'text-slate-600 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                                Nueva Compra
                            </a>
                            <a href="{{ route('compras.index') }}" 
                               @click="if (window.innerWidth < 1024) sidebarOpen = false"
                               class="block px-3 py-2 text-sm rounded-lg transition-colors duration-200 {{ request()->routeIs('compras.index') ? 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/30' : 'text-slate-600 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                                Ver Compras
                            </a>
                        </div>
                    </div>
                    @endcan

                    <!-- Inventario -->
                    @can('ver movimientos inventario')
                    <div class="relative group">
                        <a href="{{ route('inventario.index') }}" 
                           @click="if (window.innerWidth < 1024) sidebarOpen = false"
                           :class="sidebarExpanded ? 'justify-start' : 'justify-center'"
                           class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition-all duration-200 {{ request()->routeIs('inventario.*') ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400' : 'text-slate-700 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                            <span x-show="sidebarExpanded" class="font-medium">Inventario</span>
                        </a>
                        <span x-show="!sidebarExpanded" class="tooltip">Inventario</span>
                    </div>
                    @endcan

                    <!-- Productos (Accordion) -->
                    @canany(['ver productos', 'ver categorias'])
                    <div x-data="{ open: {{ request()->routeIs('productos.*') || request()->routeIs('categorias.*') ? 'true' : 'false' }} }" class="relative group">
                        <button @click="open = !open; if (!sidebarExpanded) sidebarExpanded = true" 
                                :class="sidebarExpanded ? 'justify-between' : 'justify-center'"
                                class="w-full flex items-center px-3 py-2.5 rounded-lg text-slate-700 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-200">
                            <div class="flex items-center space-x-3">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                </svg>
                                <span x-show="sidebarExpanded" class="font-medium">Productos</span>
                            </div>
                            <svg x-show="sidebarExpanded" :class="open ? 'rotate-180' : ''" class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <span x-show="!sidebarExpanded" class="tooltip">Productos</span>
                        <div x-show="open && sidebarExpanded" x-collapse class="ml-8 mt-2 space-y-1">
                            @can('ver productos')
                            <a href="{{ route('productos.create') }}" 
                               @click="if (window.innerWidth < 1024) sidebarOpen = false"
                               class="block px-3 py-2 text-sm rounded-lg transition-colors duration-200 {{ request()->routeIs('productos.create') ? 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/30' : 'text-slate-600 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                                Crear Producto
                            </a>
                            <a href="{{ route('productos.index') }}" 
                               @click="if (window.innerWidth < 1024) sidebarOpen = false"
                               class="block px-3 py-2 text-sm rounded-lg transition-colors duration-200 {{ request()->routeIs('productos.index') ? 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/30' : 'text-slate-600 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                                Ver Productos
                            </a>
                            @endcan
                            @can('ver categorias')
                            <a href="{{ route('categorias.index') }}" 
                               @click="if (window.innerWidth < 1024) sidebarOpen = false"
                               class="block px-3 py-2 text-sm rounded-lg transition-colors duration-200 {{ request()->routeIs('categorias.index') ? 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/30' : 'text-slate-600 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                                Categorías
                            </a>
                            @endcan
                        </div>
                    </div>
                    @endcanany

                    <!-- Recetas -->
                    @can('ver recetas')
                    <div class="relative group">
                        <a href="{{ route('recetas.index') }}" 
                           @click="if (window.innerWidth < 1024) sidebarOpen = false"
                           :class="sidebarExpanded ? 'justify-start' : 'justify-center'"
                           class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition-all duration-200 {{ request()->routeIs('recetas.*') ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400' : 'text-slate-700 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <span x-show="sidebarExpanded" class="font-medium">Recetas</span>
                        </a>
                        <span x-show="!sidebarExpanded" class="tooltip">Recetas</span>
                    </div>
                    @endcan

                    <!-- Reportes -->
                    @canany(['ver reportes ventas', 'ver reportes compras', 'ver reportes inventario'])
                    <div class="relative group">
                        <a href="{{ route('reportes.index') }}" 
                           @click="if (window.innerWidth < 1024) sidebarOpen = false"
                           :class="sidebarExpanded ? 'justify-start' : 'justify-center'"
                           class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition-all duration-200 {{ request()->routeIs('reportes.*') ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400' : 'text-slate-700 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            <span x-show="sidebarExpanded" class="font-medium">Reportes</span>
                        </a>
                        <span x-show="!sidebarExpanded" class="tooltip">Reportes</span>
                    </div>
                    @endcanany

                    <!-- Gestión (Accordion) -->
                    @canany(['ver clientes', 'ver proveedores', 'ver usuarios'])
                    <div x-data="{ open: {{ request()->routeIs('clientes.*') || request()->routeIs('proveedores.*') || request()->routeIs('usuarios.*') ? 'true' : 'false' }} }" class="relative group">
                        <button @click="open = !open; if (!sidebarExpanded) sidebarExpanded = true" 
                                :class="sidebarExpanded ? 'justify-between' : 'justify-center'"
                                class="w-full flex items-center px-3 py-2.5 rounded-lg text-slate-700 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-200">
                            <div class="flex items-center space-x-3">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                                <span x-show="sidebarExpanded" class="font-medium">Gestión</span>
                            </div>
                            <svg x-show="sidebarExpanded" :class="open ? 'rotate-180' : ''" class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <span x-show="!sidebarExpanded" class="tooltip">Gestión</span>
                        <div x-show="open && sidebarExpanded" x-collapse class="ml-8 mt-2 space-y-1">
                            @can('ver clientes')
                            <a href="{{ route('clientes.index') }}" 
                               @click="if (window.innerWidth < 1024) sidebarOpen = false"
                               class="block px-3 py-2 text-sm rounded-lg transition-colors duration-200 {{ request()->routeIs('clientes.*') ? 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/30' : 'text-slate-600 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                                Clientes
                            </a>
                            @endcan
                            @can('ver proveedores')
                            <a href="{{ route('proveedores.index') }}" 
                               @click="if (window.innerWidth < 1024) sidebarOpen = false"
                               class="block px-3 py-2 text-sm rounded-lg transition-colors duration-200 {{ request()->routeIs('proveedores.*') ? 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/30' : 'text-slate-600 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                                Proveedores
                            </a>
                            @endcan
                            @role('Admin')
                            <a href="{{ route('usuarios.index') }}" 
                               @click="if (window.innerWidth < 1024) sidebarOpen = false"
                               class="block px-3 py-2 text-sm rounded-lg transition-colors duration-200 {{ request()->routeIs('usuarios.*') ? 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/30' : 'text-slate-600 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                                Usuarios
                            </a>
                            @endrole
                        </div>
                    </div>
                    @endcanany

                </nav>

                <!-- User Profile (Bottom) -->
                <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                    <div x-data="{ userMenuOpen: false }" class="relative">
                        <button @click="userMenuOpen = !userMenuOpen" 
                                :class="sidebarExpanded ? 'justify-start' : 'justify-center'"
                                class="w-full flex items-center space-x-3 px-3 py-2.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-200">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white font-semibold shadow-md">
                                    {{ substr(Auth::user()->name, 0, 2) }}
                                </div>
                            </div>
                            <div x-show="sidebarExpanded" class="flex-1 text-left min-w-0">
                                <p class="text-sm font-medium text-slate-900 dark:text-white truncate">{{ Auth::user()->name }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400 truncate">{{ Auth::user()->roles->pluck('name')->first() ?? 'Usuario' }}</p>
                            </div>
                            <svg x-show="sidebarExpanded" class="w-4 h-4 text-slate-500 dark:text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z"></path>
                            </svg>
                        </button>

                        <!-- User Dropdown Menu -->
                        <div x-show="userMenuOpen && sidebarExpanded" 
                             @click.away="userMenuOpen = false"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute bottom-full left-0 right-0 mb-2 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-1">
                            <a href="{{ route('profile.edit') }}" 
                               class="block px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200">
                                Mi Perfil
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" 
                                        class="w-full text-left px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200">
                                    Cerrar Sesión
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-h-screen">
            
            <!-- Top Navbar (Fixed) - Z-50 siempre encima -->
            <header class="fixed top-0 right-0 left-0 z-50 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shadow-sm transition-all duration-300"
                    :class="window.innerWidth >= 1024 ? (sidebarExpanded ? 'lg:left-64' : 'lg:left-20') : ''">
                <div class="flex items-center justify-between h-16 px-4 lg:px-6">
                    
                    <!-- Left: Hamburger (SOLO MOBILE) + Title -->
                    <div class="flex items-center space-x-4">
                        <!-- Hamburger Button (SOLO MOBILE) -->
                        <button 
                            @click="sidebarOpen = !sidebarOpen" 
                            class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200 lg:hidden"
                        >
                            <svg class="w-6 h-6 text-slate-600 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                        </button>
                        
                        @hasSection('header')
                            <div class="hidden md:block">
                                <h1 class="text-xl font-semibold text-slate-900 dark:text-white">
                                    @yield('header')
                                </h1>
                            </div>
                        @endif
                    </div>

                    <!-- Center: Quick Actions (F1-F5) - MÁS ESPACIO -->
                    <div class="hidden 2xl:flex items-center space-x-2">
                        @can('realizar ventas')
                        <button 
                            onclick="window.location.href='{{ route('ventas.create') }}'"
                            title="Nueva Venta (F1)"
                            class="group flex items-center space-x-2 px-3 py-2 text-sm bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg hover:from-green-600 hover:to-green-700 shadow-sm transition-all duration-200 hover:shadow-md hover:scale-105">
                            <span class="text-xs font-mono bg-green-700 px-1.5 py-0.5 rounded opacity-90 group-hover:opacity-100">F1</span>
                            <span class="font-medium">Venta</span>
                        </button>
                        @endcan
                        
                        @can('registrar compras')
                        <button 
                            onclick="window.location.href='{{ route('compras.create') }}'"
                            title="Nueva Compra (F2)"
                            class="group flex items-center space-x-2 px-3 py-2 text-sm bg-gradient-to-r from-indigo-500 to-indigo-600 text-white rounded-lg hover:from-indigo-600 hover:to-indigo-700 shadow-sm transition-all duration-200 hover:shadow-md hover:scale-105">
                            <span class="text-xs font-mono bg-indigo-700 px-1.5 py-0.5 rounded opacity-90 group-hover:opacity-100">F2</span>
                            <span class="font-medium">Compra</span>
                        </button>
                        @endcan
                        
                        @can('ajustar inventario')
                        <button 
                            onclick="window.location.href='{{ route('inventario.ajustar') }}'"
                            title="Ajustar Inventario (F3)"
                            class="group flex items-center space-x-2 px-3 py-2 text-sm bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-lg hover:from-purple-600 hover:to-purple-700 shadow-sm transition-all duration-200 hover:shadow-md hover:scale-105">
                            <span class="text-xs font-mono bg-purple-700 px-1.5 py-0.5 rounded opacity-90 group-hover:opacity-100">F3</span>
                            <span class="font-medium">Ajustar</span>
                        </button>
                        @endcan

                        @can('crear recetas')
                        <button 
                            onclick="window.location.href='{{ route('recetas.create') }}'"
                            title="Crear Receta (F4)"
                            class="group flex items-center space-x-2 px-3 py-2 text-sm bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg hover:from-orange-600 hover:to-orange-700 shadow-sm transition-all duration-200 hover:shadow-md hover:scale-105">
                            <span class="text-xs font-mono bg-orange-700 px-1.5 py-0.5 rounded opacity-90 group-hover:opacity-100">F4</span>
                            <span class="font-medium">Receta</span>
                        </button>
                        @endcan

                        @can('ver productos')
                        <button 
                            onclick="window.location.href='{{ route('productos.index') }}'"
                            title="Ver Productos (F5)"
                            class="group flex items-center space-x-2 px-3 py-2 text-sm bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg hover:from-blue-600 hover:to-blue-700 shadow-sm transition-all duration-200 hover:shadow-md hover:scale-105">
                            <span class="text-xs font-mono bg-blue-700 px-1.5 py-0.5 rounded opacity-90 group-hover:opacity-100">F5</span>
                            <span class="font-medium">Productos</span>
                        </button>
                        @endcan
                    </div>

                    <!-- Right: Search, Notifications, Dark Mode -->
                    <div class="flex items-center space-x-2 lg:space-x-3">
                        
                        <!-- Search -->
                        <div class="hidden md:block relative" x-data="{ searchOpen: false }">
                            <button @click="searchOpen = !searchOpen" 
                                    class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200"
                                    title="Buscar">
                                <svg class="w-5 h-5 text-slate-600 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </button>
                            
                            <div x-show="searchOpen" 
                                 @click.away="searchOpen = false"
                                 x-transition
                                 class="absolute right-0 mt-2 w-80 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 p-3">
                                <input type="text" 
                                       placeholder="Buscar productos, clientes..." 
                                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white text-sm">
                            </div>
                        </div>

                        <!-- Notifications -->
                        <div class="relative" x-data="{ notifOpen: false }">
                            <button @click="notifOpen = !notifOpen" 
                                    class="relative p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200"
                                    title="Notificaciones">
                                <svg class="w-5 h-5 text-slate-600 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                </svg>
                                <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                            </button>
                            
                            <div x-show="notifOpen" 
                                 @click.away="notifOpen = false"
                                 x-transition
                                 class="absolute right-0 mt-2 w-80 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 max-h-96 overflow-y-auto">
                                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                                    <h3 class="font-semibold text-slate-900 dark:text-white">Notificaciones</h3>
                                </div>
                                <div class="p-4 text-center text-slate-500 dark:text-slate-400 text-sm">
                                    No hay notificaciones nuevas
                                </div>
                            </div>
                        </div>

                        <!-- Dark Mode Toggle -->
                        <button @click="darkMode = !darkMode" 
                                class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200"
                                title="Cambiar tema">
                            <svg x-show="!darkMode" class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                            </svg>
                            <svg x-show="darkMode" class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                        </button>

                        <!-- User Menu (Desktop) -->
                        <div class="hidden md:block relative" x-data="{ userOpen: false }">
                            <button @click="userOpen = !userOpen" 
                                    class="flex items-center space-x-2 p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white text-sm font-semibold">
                                    {{ substr(Auth::user()->name, 0, 2) }}
                                </div>
                            </button>
                            
                            <div x-show="userOpen" 
                                 @click.away="userOpen = false"
                                 x-transition
                                 class="absolute right-0 mt-2 w-56 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 py-1">
                                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                                    <p class="text-sm font-medium text-slate-900 dark:text-white">{{ Auth::user()->name }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ Auth::user()->email }}</p>
                                </div>
                                <a href="{{ route('profile.edit') }}" 
                                   class="block px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                    Mi Perfil
                                </a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" 
                                            class="w-full text-left px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-700">
                                        Cerrar Sesión
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content (Scrollable) - Margen superior para el navbar fijo -->
            <main class="flex-1 overflow-y-auto custom-scrollbar bg-gray-50 dark:bg-gray-900 mt-16">
                <div class="py-6 px-4 lg:px-6">
                    
                    <!-- Page Header Section (ABAJO DEL NAVBAR, NO SOBREPUESTO) -->
                    @hasSection('page-actions')
                    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                        @yield('page-actions')
                    </div>
                    @endif

                    <!-- Alerts -->
                    @if(session('success'))
                        <div x-data="{ show: true }" 
                             x-show="show" 
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 transform translate-y-2"
                             x-transition:enter-end="opacity-100 transform translate-y-0"
                             x-transition:leave="transition ease-in duration-200"
                             x-transition:leave-start="opacity-100"
                             x-transition:leave-end="opacity-0"
                             x-init="setTimeout(() => show = false, 5000)" 
                             class="mb-6 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/30 dark:to-emerald-900/30 border-l-4 border-green-500 text-green-800 dark:text-green-300 px-6 py-4 rounded-lg shadow-md">
                            <div class="flex items-center">
                                <svg class="w-6 h-6 mr-3 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="font-medium">{{ session('success') }}</span>
                                <button @click="show = false" class="ml-auto text-green-700 dark:text-green-400 hover:text-green-900 dark:hover:text-green-200 transition-colors duration-200">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @endif

                    @if(session('error'))
                        <div x-data="{ show: true }" 
                             x-show="show"
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 transform translate-y-2"
                             x-transition:enter-end="opacity-100 transform translate-y-0"
                             x-transition:leave="transition ease-in duration-200"
                             x-transition:leave-start="opacity-100"
                             x-transition:leave-end="opacity-0"
                             x-init="setTimeout(() => show = false, 5000)"
                             class="mb-6 bg-gradient-to-r from-red-50 to-rose-50 dark:from-red-900/30 dark:to-rose-900/30 border-l-4 border-red-500 text-red-800 dark:text-red-300 px-6 py-4 rounded-lg shadow-md">
                            <div class="flex items-center">
                                <svg class="w-6 h-6 mr-3 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="font-medium">{{ session('error') }}</span>
                                <button @click="show = false" class="ml-auto text-red-700 dark:text-red-400 hover:text-red-900 dark:hover:text-red-200 transition-colors duration-200">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @endif

                    @if(session('info'))
                        <div x-data="{ show: true }" 
                             x-show="show"
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 transform translate-y-2"
                             x-transition:enter-end="opacity-100 transform translate-y-0"
                             x-transition:leave="transition ease-in duration-200"
                             x-transition:leave-start="opacity-100"
                             x-transition:leave-end="opacity-0"
                             x-init="setTimeout(() => show = false, 5000)"
                             class="mb-6 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/30 dark:to-indigo-900/30 border-l-4 border-blue-500 text-blue-800 dark:text-blue-300 px-6 py-4 rounded-lg shadow-md">
                            <div class="flex items-center">
                                <svg class="w-6 h-6 mr-3 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="font-medium">{{ session('info') }}</span>
                                <button @click="show = false" class="ml-auto text-blue-700 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-200 transition-colors duration-200">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @endif

                    @if($errors->any())
                        <div x-data="{ show: true }" 
                             x-show="show"
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 transform translate-y-2"
                             x-transition:enter-end="opacity-100 transform translate-y-0"
                             class="mb-6 bg-gradient-to-r from-red-50 to-rose-50 dark:from-red-900/30 dark:to-rose-900/30 border-l-4 border-red-500 text-red-800 dark:text-red-300 px-6 py-4 rounded-lg shadow-md">
                            <div class="flex">
                                <svg class="w-6 h-6 mr-3 text-red-600 dark:text-red-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div class="flex-1">
                                    <p class="font-bold mb-2">Por favor corrija los siguientes errores:</p>
                                    <ul class="mt-2 space-y-1 list-disc list-inside text-sm">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                                <button @click="show = false" class="ml-4 text-red-700 dark:text-red-400 hover:text-red-900 dark:hover:text-red-200 transition-colors duration-200">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @endif

                    <!-- Main Content -->
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <!-- Keyboard Shortcuts -->
    <script>
        document.addEventListener('keydown', function(e) {
            // F1 - Nueva Venta
            if (e.key === 'F1') {
                e.preventDefault();
                @can('realizar ventas')
                window.location.href = '{{ route("ventas.create") }}';
                @endcan
            }
            
            // F2 - Nueva Compra
            if (e.key === 'F2') {
                e.preventDefault();
                @can('registrar compras')
                window.location.href = '{{ route("compras.create") }}';
                @endcan
            }
            
            // F3 - Ajustar Inventario
            if (e.key === 'F3') {
                e.preventDefault();
                @can('ajustar inventario')
                window.location.href = '{{ route("inventario.ajustar") }}';
                @endcan
            }

            // F4 - Crear Receta
            if (e.key === 'F4') {
                e.preventDefault();
                @can('crear recetas')
                window.location.href = '{{ route("recetas.create") }}';
                @endcan
            }

            // F5 - Ver Productos (prevenir reload del navegador)
            if (e.key === 'F5') {
                e.preventDefault();
                @can('ver productos')
                window.location.href = '{{ route("productos.index") }}';
                @endcan
            }
        });
    </script>

    @stack('scripts')
</body>
</html>