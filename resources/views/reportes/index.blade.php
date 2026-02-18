@extends('layouts.app')

@section('title', 'Reportes')

@section('header')
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-black text-slate-900 dark:text-white">Reportes</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">Ventas, compras, inventario, caja y auditoría</p>
        </div>
    </div>
@endsection

@section('content')

    @php
        $flujoRouteName = \Illuminate\Support\Facades\Route::has('reportes.flujoCaja')
            ? 'reportes.flujoCaja'
            : (\Illuminate\Support\Facades\Route::has('reportes.flujo-caja') ? 'reportes.flujo-caja' : 'reportes.index');
    @endphp

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">

        {{-- Ventas --}}
        <a href="{{ route('reportes.ventas') }}" class="group bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 hover:border-emerald-300 dark:hover:border-emerald-600 transition">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-xs font-black uppercase tracking-wide text-slate-500 dark:text-slate-400">Reporte</div>
                    <div class="mt-1 text-lg font-black text-slate-900 dark:text-white">Ventas</div>
                    <div class="mt-1 text-sm text-slate-500 dark:text-slate-400">Filtros + agrupación + export</div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-700 dark:text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3v18h18" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 15l3-3 3 3 6-6" /></svg>
                </div>
            </div>
        </a>

        {{-- Compras --}}
        <a href="{{ route('reportes.compras') }}" class="group bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 hover:border-blue-300 dark:hover:border-blue-600 transition">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-xs font-black uppercase tracking-wide text-slate-500 dark:text-slate-400">Reporte</div>
                    <div class="mt-1 text-lg font-black text-slate-900 dark:text-white">Compras</div>
                    <div class="mt-1 text-sm text-slate-500 dark:text-slate-400">Proveedores + productos + export</div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-700 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 12h18M3 17h18" /></svg>
                </div>
            </div>
        </a>

        {{-- Flujo de Caja --}}
       <a href="{{ route('reportes.flujo-caja') }}" class="group bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 hover:border-amber-300 dark:hover:border-amber-600 transition">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-xs font-black uppercase tracking-wide text-slate-500 dark:text-slate-400">Reporte</div>
                    <div class="mt-1 text-lg font-black text-slate-900 dark:text-white">Flujo de Caja</div>
                    <div class="mt-1 text-sm text-slate-500 dark:text-slate-400">Efectivo vs bancarizado + salidas</div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-700 dark:text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V6m0 12v-2" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2a10 10 0 100 20 10 10 0 000-20z" /></svg>
                </div>
            </div>
        </a>

        {{-- Inventario --}}
        <a href="{{ route('reportes.inventario') }}" class="group bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 hover:border-violet-300 dark:hover:border-violet-600 transition">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-xs font-black uppercase tracking-wide text-slate-500 dark:text-slate-400">Reporte</div>
                    <div class="mt-1 text-lg font-black text-slate-900 dark:text-white">Inventario</div>
                    <div class="mt-1 text-sm text-slate-500 dark:text-slate-400">Stock bajo + vencimientos + export</div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-violet-100 dark:bg-violet-900/30 flex items-center justify-center">
                    <svg class="w-5 h-5 text-violet-700 dark:text-violet-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V7a2 2 0 00-2-2H6a2 2 0 00-2 2v6" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 13h16v6a2 2 0 01-2 2H6a2 2 0 01-2-2v-6z" /></svg>
                </div>
            </div>
        </a>

        {{-- Movimientos Inventario --}}
        <a href="{{ route('reportes.movimientos') }}" class="group bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 hover:border-slate-300 dark:hover:border-slate-500 transition">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-xs font-black uppercase tracking-wide text-slate-500 dark:text-slate-400">Auditoría</div>
                    <div class="mt-1 text-lg font-black text-slate-900 dark:text-white">Movimientos</div>
                    <div class="mt-1 text-sm text-slate-500 dark:text-slate-400">Kardex / trazabilidad</div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-900/30 flex items-center justify-center">
                    <svg class="w-5 h-5 text-slate-700 dark:text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 16h6" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 8h6" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20h10a2 2 0 002-2V6a2 2 0 00-2-2H7a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                </div>
            </div>
        </a>

        {{-- Ajustes --}}
        <a href="{{ route('reportes.ajustes') }}" class="group bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 hover:border-rose-300 dark:hover:border-rose-600 transition">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-xs font-black uppercase tracking-wide text-slate-500 dark:text-slate-400">Auditoría</div>
                    <div class="mt-1 text-lg font-black text-slate-900 dark:text-white">Ajustes</div>
                    <div class="mt-1 text-sm text-slate-500 dark:text-slate-400">Cambios manuales de stock</div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-rose-100 dark:bg-rose-900/30 flex items-center justify-center">
                    <svg class="w-5 h-5 text-rose-700 dark:text-rose-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                </div>
            </div>
        </a>

        {{-- Lotes --}}
        <a href="{{ route('reportes.lotes') }}" class="group bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 hover:border-teal-300 dark:hover:border-teal-600 transition">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-xs font-black uppercase tracking-wide text-slate-500 dark:text-slate-400">Inventario</div>
                    <div class="mt-1 text-lg font-black text-slate-900 dark:text-white">Lotes</div>
                    <div class="mt-1 text-sm text-slate-500 dark:text-slate-400">Entradas por lote</div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-teal-100 dark:bg-teal-900/30 flex items-center justify-center">
                    <svg class="w-5 h-5 text-teal-700 dark:text-teal-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4v10l8 4 8-4V7z" /></svg>
                </div>
            </div>
        </a>

        {{-- Vencimientos --}}
        <a href="{{ route('reportes.vencimientos') }}" class="group bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 hover:border-red-300 dark:hover:border-red-600 transition">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-xs font-black uppercase tracking-wide text-slate-500 dark:text-slate-400">Inventario</div>
                    <div class="mt-1 text-lg font-black text-slate-900 dark:text-white">Vencimientos</div>
                    <div class="mt-1 text-sm text-slate-500 dark:text-slate-400">Próximos y vencidos</div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-700 dark:text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2a10 10 0 100 20 10 10 0 000-20z" /></svg>
                </div>
            </div>
        </a>

    </div>

@endsection
