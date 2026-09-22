@extends('layouts.app')

@section('title', 'Historial de Turnos de Caja')

@section('content')
<div class="space-y-4">

    {{-- Breadcrumb --}}
    <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('cajas.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Control de Cajas</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-800 dark:text-slate-200 font-semibold">Historial de Turnos</span>
    </nav>

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-1 border-b border-slate-300/80 dark:border-slate-800">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">
                Historial de Turnos y Arqueos
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Registro histórico de todas las sesiones de caja con detalles de apertura, cierre y diferencias.
            </p>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <a href="{{ route('cajas.index') }}"
               class="px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-xl text-xs font-semibold shadow-xs transition flex items-center space-x-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/></svg>
                <span>Volver al Panel</span>
            </a>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs p-4">
        <form method="GET" action="{{ route('cajas.sesiones') }}" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[140px]">
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Desde</label>
                <input type="date" name="fecha_desde" value="{{ $fechaDesde }}"
                       class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm focus:ring-emerald-500 focus:border-emerald-500">
            </div>
            <div class="flex-1 min-w-[140px]">
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Hasta</label>
                <input type="date" name="fecha_hasta" value="{{ $fechaHasta }}"
                       class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm focus:ring-emerald-500 focus:border-emerald-500">
            </div>
            <div class="flex-1 min-w-[140px]">
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Caja</label>
                <select name="caja_id" class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="">Todas las cajas</option>
                    @foreach($cajas as $caja)
                        <option value="{{ $caja->id }}" {{ $cajaId == $caja->id ? 'selected' : '' }}>
                            {{ $caja->nombre }} ({{ $caja->codigo }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 min-w-[140px]">
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Cajero</label>
                <select name="cajero_id" class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="">Todos los cajeros</option>
                    @foreach($cajeros as $cajero)
                        <option value="{{ $cajero->id }}" {{ $cajeroId == $cajero->id ? 'selected' : '' }}>
                            {{ $cajero->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-[120px]">
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Estado</label>
                <select name="estado" class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="">Todos</option>
                    <option value="abierta" {{ $estado === 'abierta' ? 'selected' : '' }}>Abiertos</option>
                    <option value="cerrada" {{ $estado === 'cerrada' ? 'selected' : '' }}>Cerrados</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit"
                        class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold shadow-xs transition flex items-center space-x-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <span>Filtrar</span>
                </button>
                <a href="{{ route('cajas.sesiones') }}"
                   class="px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-xl text-xs font-semibold transition">
                    Limpiar
                </a>
            </div>
        </form>
    </div>

    {{-- Tabla de Sesiones --}}
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
        @if($sesiones->isEmpty())
            <div class="p-10 text-center">
                <svg class="w-12 h-12 text-slate-300 dark:text-slate-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">No se encontraron turnos para los filtros seleccionados.</p>
                <a href="{{ route('cajas.sesiones') }}" class="text-xs text-emerald-600 dark:text-emerald-400 hover:underline mt-1 inline-block">Quitar filtros</a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/60">
                        <tr>
                            <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Caja</th>
                            <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Cajero</th>
                            <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Apertura</th>
                            <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Cierre</th>
                            <th class="px-4 py-2.5 text-right text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Fondo Inicial</th>
                            <th class="px-4 py-2.5 text-right text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Total Ventas</th>
                            <th class="px-4 py-2.5 text-right text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Diferencia</th>
                            <th class="px-4 py-2.5 text-center text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Estado</th>
                            <th class="px-4 py-2.5 text-right text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($sesiones as $sesion)
                        @php
                            $difSesion = $sesion->fecha_cierre
                                ? ($sesion->monto_final_efectivo - $sesion->monto_esperado_efectivo)
                                : null;
                        @endphp
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                            <td class="px-4 py-2.5">
                                <p class="text-xs font-semibold text-slate-800 dark:text-slate-200">{{ $sesion->caja->nombre }}</p>
                                <p class="text-[11px] text-slate-400 dark:text-slate-500">{{ $sesion->caja->codigo }}</p>
                            </td>
                            <td class="px-4 py-2.5 text-xs text-slate-700 dark:text-slate-300">{{ $sesion->usuario->name }}</td>
                            <td class="px-4 py-2.5 text-xs text-slate-600 dark:text-slate-400">{{ $sesion->fecha_apertura->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-2.5 text-xs text-slate-600 dark:text-slate-400">
                                {{ $sesion->fecha_cierre ? $sesion->fecha_cierre->format('d/m/Y H:i') : '—' }}
                            </td>
                            <td class="px-4 py-2.5 text-xs text-slate-700 dark:text-slate-300 text-right font-mono">${{ number_format($sesion->monto_inicial, 2) }}</td>
                            <td class="px-4 py-2.5 text-xs font-semibold text-emerald-700 dark:text-emerald-400 text-right font-mono">${{ number_format($sesion->total_ventas ?? 0, 2) }}</td>
                            <td class="px-4 py-2.5 text-xs font-semibold text-right font-mono
                                {{ $difSesion === null ? 'text-slate-400' : ($difSesion == 0 ? 'text-emerald-700 dark:text-emerald-400' : ($difSesion > 0 ? 'text-sky-700 dark:text-sky-400' : 'text-rose-700 dark:text-rose-400')) }}">
                                @if($difSesion === null)
                                    —
                                @else
                                    {{ $difSesion >= 0 ? '+' : '' }}${{ number_format($difSesion, 2) }}
                                @endif
                            </td>
                            <td class="px-4 py-2.5 text-center">
                                @if($sesion->estado === 'abierta')
                                    <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300">Abierto</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">Cerrado</span>
                                @endif
                            </td>
                            <td class="px-4 py-2.5 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('cajas.show', $sesion) }}"
                                       class="px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 text-[11px] font-semibold transition">
                                        Ver
                                    </a>
                                    <a href="{{ route('cajas.ticket', $sesion) }}" target="_blank"
                                       class="px-2.5 py-1 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 text-[11px] font-semibold transition">
                                        Ticket
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Paginación --}}
            @if($sesiones->hasPages())
            <div class="px-4 py-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Mostrando {{ $sesiones->firstItem() }}–{{ $sesiones->lastItem() }} de {{ $sesiones->total() }} turnos
                </p>
                {{ $sesiones->links() }}
            </div>
            @else
            <div class="px-4 py-3 border-t border-slate-100 dark:border-slate-800">
                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $sesiones->total() }} turno(s) encontrado(s)</p>
            </div>
            @endif
        @endif
    </div>

</div>
@endsection
