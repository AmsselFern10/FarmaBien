@extends('layouts.app')

@section('title', 'Reporte de Auditoría y Trazabilidad - FarmaBien')

@section('content')
<div class="space-y-5" x-data="{
    modalDetalles: false,
    selectedLog: null,
    verPayload(log) {
        this.selectedLog = log;
        this.modalDetalles = true;
    },
    setDates(preset) {
        const now = new Date();
        let from = new Date(), to = new Date();
        if (preset === 'hoy') { /* same */ }
        else if (preset === '7dias')  { from.setDate(now.getDate() - 6); }
        else if (preset === '30dias') { from.setDate(now.getDate() - 29); }
        else if (preset === 'mes')    { from = new Date(now.getFullYear(), now.getMonth(), 1); to = new Date(now.getFullYear(), now.getMonth()+1, 0); }
        else if (preset === 'anio')   { from = new Date(now.getFullYear(), 0, 1); to = new Date(now.getFullYear(), 11, 31); }
        const fmt = d => d.toISOString().split('T')[0];
        document.getElementById('ra_desde').value = fmt(from);
        document.getElementById('ra_hasta').value = fmt(to);
    }
}">

    {{-- Breadcrumb --}}
    <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('reportes.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Centro de Reportes</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-800 dark:text-slate-200 font-semibold">Auditoría & Logs</span>
    </nav>

    {{-- Header & Actions --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-1 border-b border-slate-300/80 dark:border-slate-800 print:hidden">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">Auditoría y Trazabilidad del Sistema</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Bitácora inmutable de acciones, modificaciones críticas, accesos y eventos operativos.
            </p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('reportes.index') }}"
               class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Centro de Reportes</span>
            </a>
            {{-- Modo Full --}}
            <button type="button" @click="$dispatch('toggle-pos-fullscreen')"
                    class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 transition">
                <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                <span x-text="posFullscreen ? 'Salir Full' : 'Modo Full'">Modo Full</span>
            </button>
            <button type="button" onclick="window.print()"
                    class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 transition">
                <svg class="w-3.5 h-3.5 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Imprimir</span>
            </button>
            <a href="{{ route('reportes.auditorias', array_merge(request()->query(), ['export' => 'pdf'])) }}" target="_blank"
               class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white shadow-sm transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                <span>Exportar PDF</span>
            </a>
            <a href="{{ route('reportes.auditorias', array_merge(request()->query(), ['export' => 'excel'])) }}"
               class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-emerald-700 hover:bg-emerald-800 active:bg-emerald-900 text-white shadow-sm transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Exportar Excel</span>
            </a>
            <a href="{{ route('reportes.auditorias', array_merge(request()->query(), ['export' => 'csv'])) }}"
               class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-slate-700 hover:bg-slate-800 active:bg-slate-900 text-white shadow-sm transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <span>Exportar CSV</span>
            </a>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        {{-- Total Eventos --}}
        <div class="bg-white dark:bg-slate-900 rounded-xl p-3.5 border border-slate-300 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Total Eventos</p>
                <p class="text-lg font-bold text-slate-900 dark:text-white mt-0.5">{{ number_format($totalLogs) }}</p>
                <p class="text-[10px] text-slate-700 dark:text-slate-400 mt-0.5">En el rango seleccionado</p>
            </div>
            <div class="w-9 h-9 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
        </div>

        {{-- Usuarios Activos --}}
        <div class="bg-white dark:bg-slate-900 rounded-xl p-3.5 border border-slate-300 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Usuarios Activos</p>
                <p class="text-lg font-bold text-teal-600 dark:text-teal-400 mt-0.5">{{ $usuariosActivos }}</p>
                <p class="text-[10px] text-slate-700 dark:text-slate-400 mt-0.5">Generaron actividad</p>
            </div>
            <div class="w-9 h-9 rounded-lg bg-teal-50 dark:bg-teal-950/60 text-teal-600 dark:text-teal-400 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </div>
        </div>

        {{-- Módulos Auditados --}}
        <div class="bg-white dark:bg-slate-900 rounded-xl p-3.5 border border-slate-300 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Módulos Auditados</p>
                <p class="text-lg font-bold text-indigo-900 dark:text-indigo-400 mt-0.5">{{ $modulosAuditados }}</p>
                <p class="text-[10px] text-slate-700 dark:text-slate-400 mt-0.5">Áreas operativas</p>
            </div>
            <div class="w-9 h-9 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
            </div>
        </div>

        {{-- Acciones Críticas --}}
        <div class="bg-white dark:bg-slate-900 rounded-xl p-3.5 border border-slate-300 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Acciones de Impacto</p>
                <p class="text-lg font-bold text-amber-900 dark:text-amber-400 mt-0.5">{{ number_format($accionesCriticas) }}</p>
                <p class="text-[10px] text-slate-700 dark:text-slate-400 mt-0.5">Ediciones, anulación y ajustes</p>
            </div>
            <div class="w-9 h-9 rounded-lg bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
        </div>
    </div>

    {{-- Panel de Filtros --}}
    <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-300 dark:border-slate-800 shadow-xs print:hidden">
        <form method="GET" action="{{ route('reportes.auditorias') }}" class="space-y-3">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-800">
                <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Filtros de Búsqueda y Trazabilidad</span>
                <div class="flex items-center gap-1.5">
                    <button type="button" @click="setDates('hoy')"    class="px-2 py-0.5 text-[11px] font-semibold rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-emerald-100 hover:text-emerald-700 dark:hover:bg-emerald-950 dark:hover:text-emerald-300 text-slate-600 dark:text-slate-400 transition">Hoy</button>
                    <button type="button" @click="setDates('7dias')"  class="px-2 py-0.5 text-[11px] font-semibold rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-emerald-100 hover:text-emerald-700 dark:hover:bg-emerald-950 dark:hover:text-emerald-300 text-slate-600 dark:text-slate-400 transition">7 Días</button>
                    <button type="button" @click="setDates('30dias')" class="px-2 py-0.5 text-[11px] font-semibold rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-emerald-100 hover:text-emerald-700 dark:hover:bg-emerald-950 dark:hover:text-emerald-300 text-slate-600 dark:text-slate-400 transition">30 Días</button>
                    <button type="button" @click="setDates('mes')"    class="px-2 py-0.5 text-[11px] font-semibold rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-emerald-100 hover:text-emerald-700 dark:hover:bg-emerald-950 dark:hover:text-emerald-300 text-slate-600 dark:text-slate-400 transition">Este Mes</button>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-6 gap-3">
                <div>
                    <label class="block text-[11px] font-medium text-slate-500 dark:text-slate-400 mb-1">Fecha Desde</label>
                    <input type="date" id="ra_desde" name="fecha_desde" value="{{ $fechaDesde }}"
                           class="w-full px-2.5 py-1.5 text-xs bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-[11px] font-medium text-slate-500 dark:text-slate-400 mb-1">Fecha Hasta</label>
                    <input type="date" id="ra_hasta" name="fecha_hasta" value="{{ $fechaHasta }}"
                           class="w-full px-2.5 py-1.5 text-xs bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-[11px] font-medium text-slate-500 dark:text-slate-400 mb-1">Módulo</label>
                    <select name="modulo" class="w-full px-2.5 py-1.5 text-xs bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="">Todos los Módulos</option>
                        @foreach($modulos as $m)
                        <option value="{{ $m }}" {{ $modulo === $m ? 'selected' : '' }}>{{ ucfirst($m) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-medium text-slate-500 dark:text-slate-400 mb-1">Acción</label>
                    <select name="accion" class="w-full px-2.5 py-1.5 text-xs bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="">Todas las Acciones</option>
                        @foreach($acciones as $a)
                        <option value="{{ $a }}" {{ $accion === $a ? 'selected' : '' }}>{{ strtoupper($a) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-medium text-slate-500 dark:text-slate-400 mb-1">Usuario</label>
                    <select name="user_id" class="w-full px-2.5 py-1.5 text-xs bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="">Todos los Usuarios</option>
                        @foreach($usuarios as $u)
                        <option value="{{ $u->id }}" {{ $userId == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-medium text-slate-500 dark:text-slate-400 mb-1">Buscar Texto / IP</label>
                    <input type="text" name="buscar" value="{{ $buscar }}" placeholder="Descripción o IP..."
                           class="w-full px-2.5 py-1.5 text-xs bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                <a href="{{ route('reportes.auditorias') }}"
                   class="px-3 py-1.5 text-xs font-semibold text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 transition">
                    Limpiar
                </a>
                <button type="submit"
                        class="inline-flex items-center space-x-1.5 px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white rounded-xl text-xs font-bold shadow-sm transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    <span>Filtrar Logs</span>
                </button>
            </div>
        </form>
    </div>

    {{-- Tabla de Logs de Auditoría --}}
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-300 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-bold text-slate-900 dark:text-white">Registros de Eventos</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Mostrando {{ $logs->total() }} eventos ordenados cronológicamente</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-slate-50 dark:bg-slate-800/80 text-slate-600 dark:text-slate-300 font-semibold border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="px-3.5 py-3">Fecha y Hora</th>
                        <th class="px-3.5 py-3">Usuario</th>
                        <th class="px-3.5 py-3">Módulo</th>
                        <th class="px-3.5 py-3">Acción</th>
                        <th class="px-3.5 py-3">Descripción</th>
                        <th class="px-3.5 py-3">IP / Origen</th>
                        <th class="px-3.5 py-3 text-center print:hidden">Detalles</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($logs as $l)
                    @php
                        $modColor = match(strtolower($l->modulo ?? '')) {
                            'ventas', 'pos'     => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300',
                            'compras'           => 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300',
                            'inventario', 'lotes' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-300',
                            'cajas'             => 'bg-teal-100 text-teal-800 dark:bg-teal-950 dark:text-teal-300',
                            'recetas'           => 'bg-sky-100 text-sky-800 dark:bg-sky-950 dark:text-sky-300',
                            'usuarios', 'auth'  => 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300',
                            default             => 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-300',
                        };

                        $actUpper = strtoupper($l->accion ?? '');
                        $actColor = match(true) {
                            str_contains($actUpper, 'CREAR') || str_contains($actUpper, 'STORE') || str_contains($actUpper, 'INSERT') => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-400 dark:border-emerald-800',
                            str_contains($actUpper, 'EDIT') || str_contains($actUpper, 'UPDATE') || str_contains($actUpper, 'MODIFICAR') => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-400 dark:border-amber-800',
                            str_contains($actUpper, 'ELIMINAR') || str_contains($actUpper, 'DELETE') || str_contains($actUpper, 'ANULAR') || str_contains($actUpper, 'BAJA') => 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/40 dark:text-rose-400 dark:border-rose-800',
                            str_contains($actUpper, 'LOGIN') || str_contains($actUpper, 'AUTH') => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/40 dark:text-blue-400 dark:border-blue-800',
                            default => 'bg-slate-50 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700',
                        };
                    @endphp
                    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition">
                        <td class="px-3.5 py-3 whitespace-nowrap">
                            <span class="font-mono text-slate-700 dark:text-slate-300">{{ $l->created_at ? $l->created_at->format('d/m/Y') : 'N/A' }}</span>
                            <span class="text-[10px] text-slate-400 font-mono block">{{ $l->created_at ? $l->created_at->format('H:i:s') : '' }}</span>
                        </td>
                        <td class="px-3.5 py-3">
                            <div class="font-semibold text-slate-900 dark:text-white">{{ $l->user?->name ?? 'Sistema / Cron' }}</div>
                            <div class="text-[10px] text-slate-400">{{ $l->user?->email ?? 'Automático' }}</div>
                        </td>
                        <td class="px-3.5 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold {{ $modColor }}">
                                {{ ucfirst($l->modulo ?? 'General') }}
                            </span>
                        </td>
                        <td class="px-3.5 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded border text-[10px] font-mono font-bold {{ $actColor }}">
                                {{ strtoupper($l->accion ?? 'EVENTO') }}
                            </span>
                        </td>
                        <td class="px-3.5 py-3 text-slate-800 dark:text-slate-200 max-w-md">
                            <div class="line-clamp-2" title="{{ $l->descripcion }}">{{ $l->descripcion }}</div>
                        </td>
                        <td class="px-3.5 py-3 whitespace-nowrap font-mono text-[11px] text-slate-500 dark:text-slate-400">
                            {{ $l->ip ?? '127.0.0.1' }}
                        </td>
                        <td class="px-3.5 py-3 text-center print:hidden">
                            @if(!empty($l->detalles))
                            <button type="button" @click="verPayload({{ json_encode($l) }})"
                                    class="inline-flex items-center space-x-1 px-2 py-1 rounded bg-slate-100 dark:bg-slate-800 hover:bg-emerald-50 hover:text-emerald-700 dark:hover:bg-emerald-950 dark:hover:text-emerald-300 text-slate-600 dark:text-slate-400 font-medium text-[11px] transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                                <span>Payload</span>
                            </button>
                            @else
                            <span class="text-slate-300 dark:text-slate-700 text-[11px]">&mdash;</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-slate-400 text-xs">
                            No se encontraron registros de auditoría para los criterios seleccionados.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
        <div class="p-4 border-t border-slate-200 dark:border-slate-800 print:hidden">
            {{ $logs->links() }}
        </div>
        @endif
    </div>

    {{-- Modal para Inspección de Payload JSON --}}
    <template x-teleport="body">
        <div x-show="modalDetalles" x-cloak
             class="fixed inset-0 z-[9999] bg-slate-950/75 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4 overflow-y-auto"
             @keydown.escape.window="modalDetalles = false">
            <div @click.away="modalDetalles = false"
                 class="bg-white dark:bg-slate-900 rounded-2xl max-w-2xl w-full border border-slate-300 dark:border-slate-800 shadow-xl overflow-hidden my-auto animate-in fade-in zoom-in-95 duration-150">
                
                <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                            <span>Detalle de Auditoría &bull; Evento #<span x-text="selectedLog?.id"></span></span>
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5" x-text="selectedLog?.descripcion"></p>
                    </div>
                    <button type="button" @click="modalDetalles = false"
                            class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="p-5 space-y-3">
                    <div class="grid grid-cols-3 gap-2 text-xs bg-slate-50 dark:bg-slate-800/60 p-3 rounded-xl border border-slate-200 dark:border-slate-700">
                        <div>
                            <span class="text-slate-400 block text-[10px]">Usuario:</span>
                            <span class="font-semibold text-slate-800 dark:text-slate-200" x-text="selectedLog?.user?.name || 'Sistema'"></span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px]">IP:</span>
                            <span class="font-mono text-slate-800 dark:text-slate-200" x-text="selectedLog?.ip || 'N/A'"></span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px]">Acción:</span>
                            <span class="font-mono font-bold text-emerald-600 dark:text-emerald-400" x-text="selectedLog?.accion"></span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Payload / Información de Cambios:</label>
                        <pre class="bg-slate-950 text-emerald-400 p-3.5 rounded-xl text-xs font-mono overflow-x-auto max-h-72 select-all leading-relaxed"
                             x-text="JSON.stringify(selectedLog?.detalles, null, 2)"></pre>
                    </div>
                </div>

                <div class="px-5 py-3 bg-slate-50 dark:bg-slate-800/40 border-t border-slate-200 dark:border-slate-800 flex justify-end">
                    <button type="button" @click="modalDetalles = false"
                            class="px-4 py-1.5 bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-800 dark:text-slate-200 rounded-xl text-xs font-bold transition">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </template>

</div>
@endsection
