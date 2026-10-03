@extends('layouts.app')

@section('title', 'Reporte de Cajas y Arqueos - FarmaBien')

@section('content')
<div class="space-y-5" x-data="{
    setDates(preset) {
        const now = new Date();
        let from = new Date(), to = new Date();
        if (preset === 'hoy') { /* same */ }
        else if (preset === '7dias')  { from.setDate(now.getDate() - 6); }
        else if (preset === 'mes')    { from = new Date(now.getFullYear(), now.getMonth(), 1); to = new Date(now.getFullYear(), now.getMonth()+1, 0); }
        else if (preset === 'mesant') { from = new Date(now.getFullYear(), now.getMonth()-1, 1); to = new Date(now.getFullYear(), now.getMonth(), 0); }
        else if (preset === 'anio')   { from = new Date(now.getFullYear(), 0, 1); to = new Date(now.getFullYear(), 11, 31); }
        const fmt = d => d.toISOString().split('T')[0];
        document.getElementById('rc_desde').value = fmt(from);
        document.getElementById('rc_hasta').value = fmt(to);
    }
}">

    {{-- Breadcrumb --}}
    <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('reportes.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Centro de Reportes</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-800 dark:text-slate-200 font-semibold">Cajas y Arqueos</span>
    </nav>

    {{-- Header & Actions --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-1 border-b border-slate-300/80 dark:border-slate-800 print:hidden">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">Reporte Gerencial de Cajas y Arqueos</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Auditoría de aperturas, cierres, ventas por método de pago, ingresos/egresos y cuadres de caja.
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
            <a href="{{ route('reportes.cajas', array_merge(request()->query(), ['export' => 'pdf'])) }}" target="_blank"
               class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white shadow-sm transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                <span>Exportar PDF</span>
            </a>
            <a href="{{ route('reportes.cajas', array_merge(request()->query(), ['export' => 'excel'])) }}"
               class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-emerald-700 hover:bg-emerald-800 active:bg-emerald-900 text-white shadow-sm transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Exportar Excel</span>
            </a>
            <a href="{{ route('reportes.cajas', array_merge(request()->query(), ['export' => 'csv'])) }}"
               class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-slate-700 hover:bg-slate-800 active:bg-slate-900 text-white shadow-sm transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <span>Exportar CSV</span>
            </a>
        </div>
    </div>

    {{-- Print header institucional --}}
    <div class="hidden print:block border-b-2 border-emerald-600 pb-3 mb-4">
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-xl font-bold text-emerald-700 uppercase tracking-wide">FARMABIEN</h1>
                <p class="text-xs text-slate-600">Farmacia & Droguería FarmaBien C.A. &bull; Sistema de Gestión Farmacéutica</p>
                <p class="text-[10px] text-slate-500 mt-0.5"><strong>RIF / RUC:</strong> J-40892154-0 &bull; <strong>Teléfono:</strong> (0212) 555-0199</p>
            </div>
            <div class="text-right">
                <div class="inline-block border border-emerald-600 bg-emerald-50 px-3 py-1.5 rounded text-center">
                    <p class="text-xs font-bold text-emerald-800">REPORTE DE CONTROL DE CAJAS</p>
                    <p class="text-[9px] text-emerald-700 mt-0.5">Emisión: {{ now()->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        {{-- Total Ventas en Cajas --}}
        <div class="bg-white dark:bg-slate-900 rounded-xl p-3.5 border border-slate-300 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Total Ventas</p>
                <p class="text-lg font-bold text-emerald-900 dark:text-emerald-400 mt-0.5">C$ {{ number_format($totalVentasCajas, 2) }}</p>
                <p class="text-[10px] text-slate-700 dark:text-slate-400 mt-0.5">{{ $totalSesiones }} turnos auditados</p>
            </div>
            <div class="w-9 h-9 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        {{-- Ventas en Efectivo --}}
        <div class="bg-white dark:bg-slate-900 rounded-xl p-3.5 border border-slate-300 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Efectivo en Ventas</p>
                <p class="text-lg font-bold text-teal-600 dark:text-teal-400 mt-0.5">C$ {{ number_format($totalVentasEfectivo, 2) }}</p>
                <p class="text-[10px] text-slate-700 dark:text-slate-400 mt-0.5">Tarjeta: C$ {{ number_format($totalVentasTarjeta, 2) }} | Transf: C$ {{ number_format($totalVentasTransferencia, 2) }}</p>
            </div>
            <div class="w-9 h-9 rounded-lg bg-teal-50 dark:bg-teal-950/60 text-teal-600 dark:text-teal-400 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
        </div>

        {{-- Movimientos Manuales (Ingresos / Egresos) --}}
        <div class="bg-white dark:bg-slate-900 rounded-xl p-3.5 border border-slate-300 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Movimientos Caja</p>
                <div class="flex items-center gap-1.5 mt-0.5">
                    <span class="text-xs font-bold text-emerald-600">+C$ {{ number_format($totalIngresosManuales, 2) }}</span>
                    <span class="text-slate-300 dark:text-slate-700">/</span>
                    <span class="text-xs font-bold text-rose-600">-C$ {{ number_format($totalEgresosManuales, 2) }}</span>
                </div>
                <p class="text-[10px] text-slate-700 dark:text-slate-400 mt-0.5">Neto: C$ {{ number_format($totalIngresosManuales - $totalEgresosManuales, 2) }}</p>
            </div>
            <div class="w-9 h-9 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
            </div>
        </div>

        {{-- Diferencia Neta de Arqueos --}}
        <div class="bg-white dark:bg-slate-900 rounded-xl p-3.5 border border-slate-300 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Diferencia de Arqueo</p>
                <p class="text-lg font-bold mt-0.5 {{ $diferenciaTotal < 0 ? 'text-rose-600 dark:text-rose-400' : ($diferenciaTotal > 0 ? 'text-blue-600 dark:text-blue-400' : 'text-emerald-600 dark:text-emerald-400') }}">
                    {{ $diferenciaTotal > 0 ? '+' : '' }}C$ {{ number_format($diferenciaTotal, 2) }}
                </p>
                <p class="text-[10px] text-slate-700 dark:text-slate-400 mt-0.5">
                    @if($sesionesConDiferencia > 0)
                        <span class="text-amber-600 dark:text-amber-400 font-semibold">{{ $sesionesConDiferencia }} turnos con descuadre</span>
                    @else
                        <span>Arqueos 100% cuadrados</span>
                    @endif
                </p>
            </div>
            <div class="w-9 h-9 rounded-lg {{ $sesionesConDiferencia > 0 ? 'bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400' : 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400' }} flex items-center justify-center shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
    </div>

    {{-- Panel de Filtros --}}
    <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-300 dark:border-slate-800 shadow-xs print:hidden">
        <form method="GET" action="{{ route('reportes.cajas') }}" class="space-y-3">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-800">
                <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Filtrar Turnos y Sesiones</span>
                <div class="flex items-center gap-1.5">
                    <button type="button" @click="setDates('hoy')"    class="px-2 py-0.5 text-[11px] font-semibold rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-emerald-100 hover:text-emerald-700 dark:hover:bg-emerald-950 dark:hover:text-emerald-300 text-slate-600 dark:text-slate-400 transition">Hoy</button>
                    <button type="button" @click="setDates('7dias')"  class="px-2 py-0.5 text-[11px] font-semibold rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-emerald-100 hover:text-emerald-700 dark:hover:bg-emerald-950 dark:hover:text-emerald-300 text-slate-600 dark:text-slate-400 transition">7 Días</button>
                    <button type="button" @click="setDates('mes')"    class="px-2 py-0.5 text-[11px] font-semibold rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-emerald-100 hover:text-emerald-700 dark:hover:bg-emerald-950 dark:hover:text-emerald-300 text-slate-600 dark:text-slate-400 transition">Este Mes</button>
                    <button type="button" @click="setDates('mesant')" class="px-2 py-0.5 text-[11px] font-semibold rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-emerald-100 hover:text-emerald-700 dark:hover:bg-emerald-950 dark:hover:text-emerald-300 text-slate-600 dark:text-slate-400 transition">Mes Ant.</button>
                    <button type="button" @click="setDates('anio')"   class="px-2 py-0.5 text-[11px] font-semibold rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-emerald-100 hover:text-emerald-700 dark:hover:bg-emerald-950 dark:hover:text-emerald-300 text-slate-600 dark:text-slate-400 transition">Este Año</button>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3">
                <div>
                    <label class="block text-[11px] font-medium text-slate-500 dark:text-slate-400 mb-1">Fecha Desde</label>
                    <input type="date" id="rc_desde" name="fecha_desde" value="{{ $fechaDesde }}"
                           class="w-full px-2.5 py-1.5 text-xs bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-[11px] font-medium text-slate-500 dark:text-slate-400 mb-1">Fecha Hasta</label>
                    <input type="date" id="rc_hasta" name="fecha_hasta" value="{{ $fechaHasta }}"
                           class="w-full px-2.5 py-1.5 text-xs bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-[11px] font-medium text-slate-500 dark:text-slate-400 mb-1">Caja</label>
                    <select name="caja_id" class="w-full px-2.5 py-1.5 text-xs bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="">Todas las Cajas</option>
                        @foreach($cajas as $c)
                        <option value="{{ $c->id }}" {{ $cajaId == $c->id ? 'selected' : '' }}>{{ $c->nombre }} (#{{ $c->numero }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-medium text-slate-500 dark:text-slate-400 mb-1">Cajero / Usuario</label>
                    <select name="cajero_id" class="w-full px-2.5 py-1.5 text-xs bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="">Todos los Cajeros</option>
                        @foreach($cajeros as $u)
                        <option value="{{ $u->id }}" {{ $cajeroId == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-medium text-slate-500 dark:text-slate-400 mb-1">Estado</label>
                    <select name="estado" class="w-full px-2.5 py-1.5 text-xs bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="">Todos los Estados</option>
                        <option value="abierta" {{ $estado === 'abierta' ? 'selected' : '' }}>Abierta (En Turno)</option>
                        <option value="cerrada" {{ $estado === 'cerrada' ? 'selected' : '' }}>Cerrada (Arqueada)</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                <a href="{{ route('reportes.cajas') }}"
                   class="px-3 py-1.5 text-xs font-semibold text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 transition">
                    Limpiar
                </a>
                <button type="submit"
                        class="inline-flex items-center space-x-1.5 px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white rounded-xl text-xs font-bold shadow-sm transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    <span>Aplicar Filtros</span>
                </button>
            </div>
        </form>
    </div>

    {{-- Tabla de Sesiones de Caja --}}
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-300 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-bold text-slate-900 dark:text-white">Auditoría Detallada de Turnos y Sesiones</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Mostrando {{ $sesiones->total() }} registros ordenados por fecha de apertura</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-slate-50 dark:bg-slate-800/80 text-slate-600 dark:text-slate-300 font-semibold border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="px-3.5 py-3">Turno / Caja</th>
                        <th class="px-3.5 py-3">Cajero & Horario</th>
                        <th class="px-3.5 py-3 text-right">Monto Inicial</th>
                        <th class="px-3.5 py-3 text-right">Ventas Efectivo</th>
                        <th class="px-3.5 py-3 text-right">Otras Ventas</th>
                        <th class="px-3.5 py-3 text-right">Total Ventas</th>
                        <th class="px-3.5 py-3 text-right">Ing / Egr</th>
                        <th class="px-3.5 py-3 text-right">Esperado</th>
                        <th class="px-3.5 py-3 text-right">Declarado</th>
                        <th class="px-3.5 py-3 text-center">Diferencia</th>
                        <th class="px-3.5 py-3 text-center">Estado</th>
                        <th class="px-3.5 py-3 text-center print:hidden">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($sesiones as $s)
                    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition">
                        <td class="px-3.5 py-3 font-semibold text-slate-900 dark:text-white">
                            <div class="flex items-center space-x-2">
                                <span class="font-mono text-emerald-600 dark:text-emerald-400">#{{ str_pad($s->id, 5, '0', STR_PAD_LEFT) }}</span>
                                <span class="text-slate-400">&bull;</span>
                                <span class="truncate max-w-[120px]">{{ $s->caja?->nombre ?? ('Caja #' . $s->caja_id) }}</span>
                            </div>
                        </td>
                        <td class="px-3.5 py-3">
                            <div class="font-medium text-slate-800 dark:text-slate-200">{{ $s->usuario?->name ?? 'N/A' }}</div>
                            <div class="text-[10px] text-slate-400">
                                {{ $s->fecha_apertura ? $s->fecha_apertura->format('d/m/Y H:i') : '' }}
                                @if($s->fecha_cierre)
                                    &rarr; {{ $s->fecha_cierre->format('H:i') }}
                                @endif
                            </div>
                        </td>
                        <td class="px-3.5 py-3 text-right font-mono text-slate-700 dark:text-slate-300">
                            C$ {{ number_format($s->monto_inicial, 2) }}
                        </td>
                        <td class="px-3.5 py-3 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400">
                            C$ {{ number_format($s->total_ventas_efectivo, 2) }}
                        </td>
                        <td class="px-3.5 py-3 text-right font-mono text-slate-600 dark:text-slate-400 text-[11px]">
                            C$ {{ number_format($s->total_ventas_tarjeta + $s->total_ventas_transferencia + $s->total_ventas_otros, 2) }}
                        </td>
                        <td class="px-3.5 py-3 text-right font-mono font-bold text-slate-900 dark:text-white">
                            C$ {{ number_format($s->total_ventas, 2) }}
                        </td>
                        <td class="px-3.5 py-3 text-right font-mono text-[11px]">
                            <span class="text-emerald-600">+C$ {{ number_format($s->total_ingresos_manuales, 2) }}</span><br>
                            <span class="text-rose-600">-C$ {{ number_format($s->total_egresos_manuales, 2) }}</span>
                        </td>
                        <td class="px-3.5 py-3 text-right font-mono font-semibold text-slate-700 dark:text-slate-300">
                            C$ {{ number_format($s->monto_esperado_efectivo ?? $s->efectivo_esperado_calculado, 2) }}
                        </td>
                        <td class="px-3.5 py-3 text-right font-mono font-bold text-slate-900 dark:text-white">
                            @if($s->estado === 'cerrada')
                                C$ {{ number_format($s->monto_final_efectivo, 2) }}
                            @else
                                <span class="text-slate-400 italic">En turno</span>
                            @endif
                        </td>
                        <td class="px-3.5 py-3 text-center font-mono font-bold">
                            @if($s->estado === 'cerrada')
                                @if(round((float)$s->diferencia_efectivo, 2) == 0)
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">$0.00</span>
                                @elseif((float)$s->diferencia_efectivo < 0)
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300" title="Faltante en caja">
                                        -C$ {{ number_format(abs($s->diferencia_efectivo), 2) }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300" title="Sobrante en caja">
                                        +C$ {{ number_format($s->diferencia_efectivo, 2) }}
                                    </span>
                                @endif
                            @else
                                <span class="text-slate-400 text-[10px]">-</span>
                            @endif
                        </td>
                        <td class="px-3.5 py-3 text-center">
                            @if($s->estado === 'abierta')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1 animate-pulse"></span> Abierta
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                    Cerrada
                                </span>
                            @endif
                        </td>
                        <td class="px-3.5 py-3 text-center print:hidden">
                            <a href="{{ route('cajas.show', $s->id) }}"
                               class="inline-flex items-center justify-center p-1.5 text-slate-500 hover:text-emerald-600 dark:hover:text-emerald-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition"
                               title="Ver Arqueo y Movimientos">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="12" class="px-4 py-8 text-center text-slate-400 text-xs">
                            No se encontraron turnos de caja para los filtros seleccionados.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($sesiones->hasPages())
        <div class="p-4 border-t border-slate-200 dark:border-slate-800 print:hidden">
            {{ $sesiones->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
