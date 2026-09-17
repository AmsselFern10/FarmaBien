@extends('layouts.app')

@section('title', 'Panel de Administración - FarmaBien')

@section('content')
<div class="space-y-6">
    <!-- Header Admin -->
    <div class="bg-gradient-to-r from-slate-900 to-indigo-950 text-white rounded-2xl p-6 shadow-md flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center space-x-4">
            <div class="w-14 h-14 rounded-2xl bg-indigo-500/20 text-indigo-400 flex items-center justify-center font-bold text-2xl shadow-inner border border-indigo-500/30">
                ⚙️
            </div>
            <div>
                <div class="flex items-center space-x-2">
                    <h1 class="text-2xl font-black tracking-tight">Centro de Control Administrativo</h1>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-500/30 text-indigo-200 border border-indigo-500/40">
                        Acceso Root / Admin
                    </span>
                </div>
                <p class="text-sm text-slate-300 mt-0.5">
                    Gestión de usuarios, control de roles, auditorías de seguridad e integridad del sistema.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('usuarios.create') }}" 
               class="inline-flex items-center space-x-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-xl shadow-md transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                <span>Nuevo Usuario</span>
            </a>
            <a href="{{ route('dashboard') }}" 
               class="inline-flex items-center space-x-2 px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-sm font-medium rounded-xl border border-slate-700 transition">
                <span>&larr; Volver al POS</span>
            </a>
        </div>
    </div>

    <!-- Módulos de Administración -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Usuarios y Seguridad -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-sm border border-slate-200/80 dark:border-slate-700/80 hover:shadow-md transition">
            <div class="w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 flex items-center justify-center text-xl mb-4">
                👥
            </div>
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Usuarios y Permisos</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 mb-4">
                Administra los farmacéuticos, cajeros y personal de inventario con roles de acceso RBAC.
            </p>
            <a href="{{ route('usuarios.index') }}" class="inline-flex items-center text-sm font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400">
                Gestionar Usuarios &rarr;
            </a>
        </div>

        <!-- Reportes y Auditoría -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-sm border border-slate-200/80 dark:border-slate-700/80 hover:shadow-md transition">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xl mb-4">
                📊
            </div>
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Reportes Gerenciales</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 mb-4">
                Reportes consolidados de ventas, compras, márgenes y movimientos de Kardex.
            </p>
            <a href="{{ route('reportes.index') }}" class="inline-flex items-center text-sm font-semibold text-emerald-600 hover:text-emerald-700 dark:text-emerald-400">
                Ver Reportes &rarr;
            </a>
        </div>

        <!-- Auditoría de Inventario -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-sm border border-slate-200/80 dark:border-slate-700/80 hover:shadow-md transition">
            <div class="w-12 h-12 rounded-xl bg-purple-50 dark:bg-purple-950/50 text-purple-600 dark:text-purple-400 flex items-center justify-center text-xl mb-4">
                📦
            </div>
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Ajustes y Kardex</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 mb-4">
                Audita la trazabilidad de lotes, mermas sanitarias y ajustes directos de stock.
            </p>
            <a href="{{ route('inventario.movimientos') }}" class="inline-flex items-center text-sm font-semibold text-purple-600 hover:text-purple-700 dark:text-purple-400">
                Auditar Kardex &rarr;
            </a>
        </div>
    </div>
</div>
@endsection
