<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" 
      x-data="{ 
          sidebarOpen: $persist(true), 
          darkMode: $persist(false) 
      }" 
      :class="darkMode ? 'dark' : ''"
      x-cloak>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'FarmaBien'))</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        [x-cloak] { display: none !important; }
        
        /* Transiciones globales */
        * {
            transition-property: background-color, border-color, color, fill, stroke, opacity, box-shadow, transform;
            transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
            transition-duration: 200ms;
        }
        
        /* Scroll personalizado */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            @apply bg-gray-100 dark:bg-slate-800;
        }
        ::-webkit-scrollbar-thumb {
            @apply bg-gray-300 dark:bg-slate-600 rounded-full;
        }
        ::-webkit-scrollbar-thumb:hover {
            @apply bg-gray-400 dark:bg-slate-500;
        }
        
        /* Sombras personalizadas */
        .shadow-smooth {
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        }
        .shadow-elevated {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .shadow-floating {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        .dark .shadow-floating {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.5), 0 4px 6px -2px rgba(0, 0, 0, 0.3);
        }
        
        /* Glass effect */
        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
        .dark .glass-effect {
            background: rgba(30, 41, 59, 0.95);
        }
        
        /* Animación de pulsación para notificaciones */
        @keyframes pulse-ring {
            0% {
                transform: scale(0.8);
                opacity: 1;
            }
            100% {
                transform: scale(1.2);
                opacity: 0;
            }
        }
        .pulse-ring {
            animation: pulse-ring 1.5s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
    </style>
    
    @stack('styles')
</head>
<body class="font-sans antialiased bg-gray-50 dark:bg-slate-900 text-gray-900 dark:text-gray-100" 
      x-data="appManager()" 
      @keydown.window="handleGlobalShortcuts($event)">
    
    <!-- SIDEBAR -->
    <aside 
        x-show="sidebarOpen || $screen('lg')"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="-translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="-translate-x-full"
        :class="sidebarOpen ? 'w-64' : 'w-20'"
        class="fixed left-0 top-0 z-40 h-screen bg-gradient-to-b from-slate-800 to-slate-900 dark:from-slate-900 dark:to-black shadow-floating transition-all duration-300 ease-in-out"
        @click.away="if(!$screen('lg')) sidebarOpen = false"
    >
        <!-- Logo y Toggle -->
        <div class="flex items-center justify-between h-16 px-4 border-b border-slate-700/50">
            <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 overflow-hidden group">
                <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg flex items-center justify-center transform group-hover:scale-110 transition-transform">
                    <span class="text-xl">💊</span>
                </div>
                <span x-show="sidebarOpen" class="text-white font-bold text-lg whitespace-nowrap">
                    FarmaBien
                </span>
            </a>
            
            <button 
                @click="sidebarOpen = !sidebarOpen"
                class="hidden lg:flex items-center justify-center w-8 h-8 text-slate-400 hover:text-white hover:bg-slate-700 rounded-lg transition-colors"
                title="Colapsar sidebar (Ctrl+B)"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </div>

        <!-- Navegación Principal -->
        <nav class="flex-1 overflow-y-auto py-4 px-2 space-y-1">
            
            <!-- Dashboard -->
            <a href="{{ route('dashboard') }}" 
               class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
               title="Dashboard">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span x-show="sidebarOpen">Dashboard</span>
            </a>

            <!-- Ventas -->
            @canany(['realizar ventas', 'ver ventas'])
            <div x-data="{ open: {{ request()->routeIs('ventas.*') ? 'true' : 'false' }} }">
                <button @click="open = !open" class="sidebar-link w-full" title="Ventas">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span x-show="sidebarOpen" class="flex-1 text-left">Ventas</span>
                    <svg x-show="sidebarOpen" :class="open ? 'rotate-180' : ''" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                
                <div x-show="open && sidebarOpen" x-collapse class="ml-4 mt-1 space-y-1">
                    @can('realizar ventas')
                    <a href="{{ route('ventas.create') }}" class="sidebar-sublink">
                        Nueva Venta
                    </a>
                    @endcan
                    @can('ver ventas')
                    <a href="{{ route('ventas.index') }}" class="sidebar-sublink">
                        Historial
                    </a>
                    @endcan
                </div>
            </div>
            @endcanany

            <!-- Compras -->
            @can('registrar compras')
            <a href="{{ route('compras.index') }}" 
               class="sidebar-link {{ request()->routeIs('compras.*') ? 'active' : '' }}"
               title="Compras">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
                <span x-show="sidebarOpen">Compras</span>
            </a>
            @endcan

            <!-- Inventario -->
            @can('ver movimientos inventario')
            <a href="{{ route('inventario.index') }}" 
               class="sidebar-link {{ request()->routeIs('inventario.*') ? 'active' : '' }}"
               title="Inventario">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                <span x-show="sidebarOpen">Inventario</span>
            </a>
            @endcan

            <!-- Catálogo -->
            @canany(['ver productos', 'ver categorias'])
            <div x-data="{ open: {{ request()->routeIs('productos.*', 'categorias.*') ? 'true' : 'false' }} }">
                <button @click="open = !open" class="sidebar-link w-full" title="Catálogo">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    <span x-show="sidebarOpen" class="flex-1 text-left">Catálogo</span>
                    <svg x-show="sidebarOpen" :class="open ? 'rotate-180' : ''" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                
                <div x-show="open && sidebarOpen" x-collapse class="ml-4 mt-1 space-y-1">
                    @can('ver productos')
                    <a href="{{ route('productos.index') }}" class="sidebar-sublink">
                        Productos
                    </a>
                    @endcan
                    @can('ver categorias')
                    <a href="{{ route('categorias.index') }}" class="sidebar-sublink">
                        Categorías
                    </a>
                    @endcan
                </div>
            </div>
            @endcanany

            <!-- Gestión -->
            @canany(['ver clientes', 'ver proveedores', 'ver recetas'])
            <div x-data="{ open: {{ request()->routeIs('clientes.*', 'proveedores.*', 'recetas.*') ? 'true' : 'false' }} }">
                <button @click="open = !open" class="sidebar-link w-full" title="Gestión">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span x-show="sidebarOpen" class="flex-1 text-left">Gestión</span>
                    <svg x-show="sidebarOpen" :class="open ? 'rotate-180' : ''" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                
                <div x-show="open && sidebarOpen" x-collapse class="ml-4 mt-1 space-y-1">
                    @can('ver clientes')
                    <a href="{{ route('clientes.index') }}" class="sidebar-sublink">
                        Clientes
                    </a>
                    @endcan
                    @can('ver proveedores')
                    <a href="{{ route('proveedores.index') }}" class="sidebar-sublink">
                        Proveedores
                    </a>
                    @endcan
                    @can('ver recetas')
                    <a href="{{ route('recetas.index') }}" class="sidebar-sublink">
                        Recetas Médicas
                    </a>
                    @endcan
                </div>
            </div>
            @endcanany

            <!-- Reportes -->
            @canany(['ver reportes ventas', 'ver reportes compras', 'ver reportes inventario'])
            <a href="{{ route('reportes.index') }}" 
               class="sidebar-link {{ request()->routeIs('reportes.*') ? 'active' : '' }}"
               title="Reportes">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span x-show="sidebarOpen">Reportes</span>
            </a>
            @endcanany

        </nav>

        <!-- Usuario info (bottom) -->
        <div class="border-t border-slate-700/50 p-4">
            <div class="flex items-center space-x-3">
                <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-br from-purple-500 to-purple-600 rounded-full flex items-center justify-center text-white font-semibold shadow-lg">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
                <div x-show="sidebarOpen" class="flex-1 overflow-hidden">
                    <p class="text-sm font-medium text-white truncate">{{ Auth::user()->name }}</p>
                    <p class="text-xs text-slate-400 truncate">{{ Auth::user()->roles->pluck('name')->first() }}</p>
                </div>
            </div>
        </div>
    </aside>

    <!-- NAVBAR SUPERIOR -->
    <nav 
        :class="sidebarOpen ? 'lg:ml-64' : 'lg:ml-20'"
        class="fixed top-0 right-0 left-0 lg:left-auto z-30 h-16 bg-white dark:bg-slate-800 border-b border-gray-200 dark:border-slate-700 shadow-smooth glass-effect transition-all duration-300"
    >
        <div class="h-full px-4 flex items-center justify-between gap-4">
            
            <!-- Mobile Toggle -->
            <button 
                @click="sidebarOpen = !sidebarOpen"
                class="lg:hidden p-2 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 rounded-lg"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            <!-- Acciones Rápidas -->
            <div class="flex items-center gap-2 overflow-x-auto">
                
                @can('realizar ventas')
                <button 
                    @click="navigateTo('{{ route('ventas.create') }}')"
                    class="quick-action-btn group"
                    title="Nueva Venta (F1)"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    <span class="hidden md:inline">Nueva Venta</span>
                    <span class="shortcut-badge">F1</span>
                </button>
                @endcan

                @can('registrar compras')
                <button 
                    @click="navigateTo('{{ route('compras.index') }}')"
                    class="quick-action-btn group"
                    title="Nueva Compra (F2)"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                    <span class="hidden md:inline">Nueva Compra</span>
                    <span class="shortcut-badge">F2</span>
                </button>
                @endcan

                @can('crear productos')
                <button 
                    @click="navigateTo('{{ route('productos.create') }}')"
                    class="quick-action-btn group"
                    title="Crear Producto (F3)"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    <span class="hidden md:inline">Producto</span>
                    <span class="shortcut-badge">F3</span>
                </button>
                @endcan

                @can('ver movimientos inventario')
                <button 
                    @click="navigateTo('{{ route('inventario.index') }}')"
                    class="quick-action-btn group"
                    title="Inventario (F4)"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <span class="hidden md:inline">Inventario</span>
                    <span class="shortcut-badge">F4</span>
                </button>
                @endcan
            </div>

            <!-- Utilidades -->
            <div class="flex items-center gap-2">
                
                <!-- Búsqueda Global -->
                <button 
                    @click="showSearch = true"
                    class="p-2 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 rounded-lg"
                    title="Búsqueda Global (Ctrl+K)"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </button>

                <!-- Dark Mode Toggle -->
                <button 
                    @click="darkMode = !darkMode"
                    class="p-2 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 rounded-lg"
                    title="Cambiar tema (Ctrl+T)"
                >
                    <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    </svg>
                    <svg x-show="darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </button>

                <!-- Notificaciones -->
                <div class="relative">
                    <button 
                        class="relative p-2 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 rounded-lg"
                        title="Notificaciones"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        <!-- Badge de notificaciones -->
                        <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full pulse-ring"></span>
                        <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                    </button>
                </div>

                <!-- User Menu -->
                <div x-data="{ open: false }" class="relative">
                    <button 
                        @click="open = !open"
                        class="flex items-center space-x-2 p-2 hover:bg-gray-100 dark:hover:bg-slate-700 rounded-lg"
                    >
                        <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                        <svg class="w-4 h-4 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <div 
                        x-show="open"
                        @click.away="open = false"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        class="absolute right-0 mt-2 w-56 bg-white dark:bg-slate-800 rounded-lg shadow-floating border border-gray-200 dark:border-slate-700 py-1 z-50"
                    >
                        <div class="px-4 py-3 border-b border-gray-200 dark:border-slate-700">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ Auth::user()->email }}</p>
                        </div>

                        @role('Admin')
                        <a href="{{ route('usuarios.index') }}" class="dropdown-item">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                            Usuarios
                        </a>
                        @endrole
                        
                        <a href="{{ route('profile.edit') }}" class="dropdown-item">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Mi Perfil
                        </a>
                        
                        <hr class="my-1 border-gray-200 dark:border-slate-700">
                        
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item w-full text-left text-red-600 dark:text-red-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                Cerrar Sesión
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- MODAL DE BÚSQUEDA GLOBAL -->
    <div 
        x-show="showSearch"
        x-transition.opacity
        class="fixed inset-0 z-50 overflow-y-auto"
        @keydown.escape.window="showSearch = false"
    >
        <div class="flex items-start justify-center min-h-screen pt-20 px-4">
            <!-- Overlay -->
            <div 
                @click="showSearch = false"
                class="fixed inset-0 bg-black/50 backdrop-blur-sm"
            ></div>

            <!-- Modal -->
            <div 
                x-show="showSearch"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-90"
                x-transition:enter-end="opacity-100 scale-100"
                class="relative w-full max-w-2xl bg-white dark:bg-slate-800 rounded-xl shadow-2xl"
            >
                <!-- Search Input -->
                <div class="p-4 border-b border-gray-200 dark:border-slate-700">
                    <div class="relative">
                        <svg class="absolute left-4 top-3.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input 
                            type="text" 
                            x-model="searchQuery"
                            @input="performSearch()"
                            placeholder="Buscar productos, clientes, ventas..."
                            class="w-full pl-12 pr-4 py-3 text-lg bg-gray-50 dark:bg-slate-900 border-0 focus:ring-0 text-gray-900 dark:text-white placeholder-gray-400"
                            autofocus
                        >
                    </div>
                </div>

                <!-- Search Results -->
                <div class="max-h-96 overflow-y-auto p-4">
                    <div x-show="searchLoading" class="text-center py-8">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Buscando...</p>
                    </div>

                    <div x-show="!searchLoading && searchResults.length === 0 && searchQuery.length > 0" class="text-center py-8">
                        <svg class="mx-auto w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="mt-2 text-gray-500 dark:text-gray-400">No se encontraron resultados</p>
                    </div>

                    <div x-show="!searchLoading && searchResults.length > 0" class="space-y-2">
                        <template x-for="result in searchResults" :key="result.id">
                            <a 
                                :href="result.url"
                                class="block p-3 hover:bg-gray-50 dark:hover:bg-slate-700 rounded-lg transition-colors"
                            >
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0 w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                                        <span x-text="result.icon" class="text-xl"></span>
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-medium text-gray-900 dark:text-white" x-text="result.title"></p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400" x-text="result.description"></p>
                                    </div>
                                    <span class="text-xs text-gray-400 uppercase" x-text="result.type"></span>
                                </div>
                            </a>
                        </template>
                    </div>

                    <!-- Atajos de teclado -->
                    <div x-show="searchQuery.length === 0" class="space-y-4 pt-4">
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Atajos de teclado</p>
                        <div class="grid grid-cols-2 gap-2">
                            <div class="flex items-center justify-between p-2 bg-gray-50 dark:bg-slate-700 rounded">
                                <span class="text-sm text-gray-700 dark:text-gray-300">Nueva Venta</span>
                                <kbd class="px-2 py-1 text-xs bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-600 rounded">F1</kbd>
                            </div>
                            <div class="flex items-center justify-between p-2 bg-gray-50 dark:bg-slate-700 rounded">
                                <span class="text-sm text-gray-700 dark:text-gray-300">Nueva Compra</span>
                                <kbd class="px-2 py-1 text-xs bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-600 rounded">F2</kbd>
                            </div>
                            <div class="flex items-center justify-between p-2 bg-gray-50 dark:bg-slate-700 rounded">
                                <span class="text-sm text-gray-700 dark:text-gray-300">Crear Producto</span>
                                <kbd class="px-2 py-1 text-xs bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-600 rounded">F3</kbd>
                            </div>
                            <div class="flex items-center justify-between p-2 bg-gray-50 dark:bg-slate-700 rounded">
                                <span class="text-sm text-gray-700 dark:text-gray-300">Inventario</span>
                                <kbd class="px-2 py-1 text-xs bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-600 rounded">F4</kbd>
                            </div>
                            <div class="flex items-center justify-between p-2 bg-gray-50 dark:bg-slate-700 rounded">
                                <span class="text-sm text-gray-700 dark:text-gray-300">Cambiar Tema</span>
                                <kbd class="px-2 py-1 text-xs bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-600 rounded">Ctrl+T</kbd>
                            </div>
                            <div class="flex items-center justify-between p-2 bg-gray-50 dark:bg-slate-700 rounded">
                                <span class="text-sm text-gray-700 dark:text-gray-300">Sidebar</span>
                                <kbd class="px-2 py-1 text-xs bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-600 rounded">Ctrl+B</kbd>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CONTENIDO PRINCIPAL -->
    <main 
        :class="sidebarOpen ? 'lg:ml-64' : 'lg:ml-20'"
        class="pt-16 min-h-screen transition-all duration-300"
    >
        <div class="p-4 md:p-6 lg:p-8">
            @yield('content')
        </div>
    </main>

    <!-- Toast Notifications Container -->
    <div 
        id="toast-container"
        class="fixed bottom-4 right-4 z-50 space-y-2"
        x-data="{ toasts: [] }"
        @toast.window="toasts.push($event.detail); setTimeout(() => toasts.shift(), 5000)"
    >
        <template x-for="toast in toasts" :key="toast.id">
            <div 
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform translate-x-8"
                x-transition:enter-end="opacity-100 transform translate-x-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                :class="{
                    'bg-green-500': toast.type === 'success',
                    'bg-red-500': toast.type === 'error',
                    'bg-blue-500': toast.type === 'info',
                    'bg-yellow-500': toast.type === 'warning'
                }"
                class="px-6 py-4 rounded-lg shadow-floating text-white max-w-sm"
            >
                <p class="font-medium" x-text="toast.message"></p>
            </div>
        </template>
    </div>

    <!-- ESTILOS PERSONALIZADOS -->
    <style>
       .sidebar-link svg {
    @apply w-5 h-5 flex-shrink-0;
}
        .sidebar-link.active {
            @apply bg-gradient-to-r from-blue-600 to-blue-500 text-white shadow-lg shadow-blue-500/30;
        }
        .sidebar-sublink {
    @apply flex items-center pl-11 pr-3 py-2 text-sm text-slate-400 
           rounded-lg hover:bg-slate-700/30 hover:text-white;
}

        
        /* Quick Action Buttons */
        .quick-action-btn {
            @apply relative flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-slate-700 border border-gray-300 dark:border-slate-600 rounded-lg hover:bg-gray-50 dark:hover:bg-slate-600 hover:shadow-md;
        }
        .shortcut-badge {
            @apply hidden group-hover:flex items-center justify-center px-2 py-0.5 text-xs font-semibold text-blue-600 dark:text-blue-400 bg-blue-100 dark:bg-blue-900 rounded;
        }
        
        /* Dropdown Items */
        .dropdown-item {
            @apply flex items-center gap-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-slate-700;
        }
    </style>

    <!-- JAVASCRIPT PRINCIPAL -->
    <script>
        function appManager() {
            return {
                showSearch: false,
                searchQuery: '',
                searchResults: [],
                searchLoading: false,
                searchTimeout: null,

                navigateTo(url) {
                    window.location.href = url;
                },

                performSearch() {
                    clearTimeout(this.searchTimeout);
                    
                    if (this.searchQuery.length < 2) {
                        this.searchResults = [];
                        return;
                    }

                    this.searchLoading = true;

                    this.searchTimeout = setTimeout(() => {
                        // Aquí implementarías la búsqueda real con AJAX
                        // Ejemplo simulado:
                        fetch(`/api/search?q=${encodeURIComponent(this.searchQuery)}`)
                            .then(response => response.json())
                            .then(data => {
                                this.searchResults = data.results || [];
                            })
                            .catch(() => {
                                // Resultados de ejemplo (eliminar en producción)
                                this.searchResults = [
                                    {
                                        id: 1,
                                        title: 'Paracetamol 500mg',
                                        description: 'Stock: 250 unidades',
                                        type: 'Producto',
                                        icon: '💊',
                                        url: '/productos/1'
                                    },
                                    {
                                        id: 2,
                                        title: 'Venta #001234',
                                        description: 'Cliente: Juan Pérez - $125.50',
                                        type: 'Venta',
                                        icon: '🧾',
                                        url: '/ventas/1234'
                                    }
                                ].filter(item => 
                                    item.title.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                                    item.description.toLowerCase().includes(this.searchQuery.toLowerCase())
                                );
                            })
                            .finally(() => {
                                this.searchLoading = false;
                            });
                    }, 300);
                },

                handleGlobalShortcuts(event) {
                    // Prevenir atajos en inputs
                    if (['INPUT', 'TEXTAREA', 'SELECT'].includes(event.target.tagName)) {
                        return;
                    }

                    const key = event.key.toLowerCase();
                    const ctrl = event.ctrlKey || event.metaKey;

                    // Ctrl/Cmd + K: Búsqueda
                    if (ctrl && key === 'k') {
                        event.preventDefault();
                        this.showSearch = true;
                    }

                    // Ctrl/Cmd + B: Toggle Sidebar
                    if (ctrl && key === 'b') {
                        event.preventDefault();
                        this.sidebarOpen = !this.sidebarOpen;
                    }

                    // Ctrl/Cmd + T: Toggle Dark Mode
                    if (ctrl && key === 't') {
                        event.preventDefault();
                        this.darkMode = !this.darkMode;
                    }

                    // F1-F4: Acciones rápidas
                    switch(event.key) {
                        case 'F1':
                            event.preventDefault();
                            @can('realizar ventas')
                            this.navigateTo('{{ route("ventas.create") }}');
                            @endcan
                            break;
                        case 'F2':
                            event.preventDefault();
                            @can('registrar compras')
                            this.navigateTo('{{ route("compras.index") }}');
                            @endcan
                            break;
                        case 'F3':
                            event.preventDefault();
                            @can('crear productos')
                            this.navigateTo('{{ route("productos.create") }}');
                            @endcan
                            break;
                        case 'F4':
                            event.preventDefault();
                            @can('ver movimientos inventario')
                            this.navigateTo('{{ route("inventario.index") }}');
                            @endcan
                            break;
                    }
                },

                showToast(message, type = 'info') {
                    window.dispatchEvent(new CustomEvent('toast', {
                        detail: {
                            id: Date.now(),
                            message: message,
                            type: type
                        }
                    }));
                }
            }
        }
    </script>

    @stack('scripts')
</body>
</html>