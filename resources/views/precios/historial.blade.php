@extends('layouts.app')

@section('title', 'Historial General de Cambios de Precio — FarmaBien')

@section('content')
<div class="space-y-4">

    {{-- Fila 1 — Breadcrumb --}}
    <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('precios.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Precios de Venta</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-800 dark:text-slate-200 font-semibold">Historial General</span>
    </nav>

    {{-- Fila 2 — Título y Acciones --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">Historial de Auditoría de Precios</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Registro inmutable de todas las variaciones de precios realizadas en el sistema con usuario, fecha y justificación.</p>
        </div>

        {{-- Barra de Acciones: ← Precios de Venta, Modo Full --}}
        <div class="flex items-center gap-2 flex-wrap shrink-0">
            {{-- Botón de Navegación --}}
            <a href="{{ route('precios.index') }}"
               class="inline-flex items-center space-x-1.5 px-3.5 py-1.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold shadow-xs transition">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Precios de Venta</span>
            </a>

            {{-- Modo Full --}}
            <button type="button" 
                    @click="$dispatch('toggle-pos-fullscreen')"
                    title="Modo Pantalla Completa"
                    class="inline-flex items-center space-x-1.5 px-3.5 py-1.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold shadow-xs transition cursor-pointer">
                <svg class="w-4 h-4 text-emerald-900 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                <span>Modo Full</span>
            </button>
        </div>
    </div>

    {{-- Filtros de Auditoría --}}
    <div class="p-4 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs space-y-3">
        <form method="GET" action="{{ route('precios.historial') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3">
            {{-- Buscador Texto --}}
            <div class="lg:col-span-4">
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Medicamento / Principio</label>
                <input type="text" 
                       name="q" 
                       value="{{ request('q') }}" 
                       placeholder="Nombre, código o principio..." 
                       class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
            </div>

            {{-- Usuario --}}
            <div class="lg:col-span-3">
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Usuario Responsable</label>
                <select name="user_id" class="w-full px-2.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                    <option value="">Todos los usuarios</option>
                    @foreach($usuarios as $u)
                    <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Fecha Desde --}}
            <div class="lg:col-span-2">
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Desde</label>
                <input type="date" 
                       name="desde" 
                       value="{{ request('desde') }}" 
                       class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
            </div>

            {{-- Fecha Hasta --}}
            <div class="lg:col-span-2">
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Hasta</label>
                <input type="date" 
                       name="hasta" 
                       value="{{ request('hasta') }}" 
                       class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
            </div>

            {{-- Botón Filtrar --}}
            <div class="lg:col-span-1 flex items-end">
                <button type="submit" class="w-full px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-semibold transition inline-flex items-center justify-center gap-1.5 shadow-xs cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    <span>Filtrar</span>
                </button>
            </div>
        </form>
    </div>

    {{-- Tabla de Registros de Historial --}}
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700 dark:text-slate-300">
                <thead class="bg-slate-100/80 dark:bg-slate-800/80 text-[11px] uppercase tracking-wider text-slate-700 dark:text-slate-400 font-bold border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="px-4 py-3">Fecha y Hora</th>
                        <th class="px-3 py-3">Medicamento</th>
                        <th class="px-3 py-3">Presentación</th>
                        <th class="px-3 py-3 text-right">Precio</th>
                        <th class="px-3 py-3 text-center">Vigencia</th>
                        <th class="px-3 py-3">Usuario</th>
                        <th class="px-4 py-3">Motivo / Justificación</th>
                        <th class="px-3 py-3 text-right">Ficha</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200/70 dark:divide-slate-800/70">
                    @forelse($historial as $h)
                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition">
                        <td class="px-4 py-2.5 font-mono text-slate-700 dark:text-slate-400">
                            {{ $h->vigente_desde ? $h->vigente_desde->format('d/m/Y H:i') : $h->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-3 py-2.5">
                            <div class="font-bold text-slate-900 dark:text-white">{{ $h->producto->nombre ?? 'Eliminado' }}</div>
                            <div class="text-[11px] text-slate-500">{{ $h->producto->categoria->nombre ?? '' }} · {{ $h->producto->laboratorio->nombre ?? '' }}</div>
                        </td>
                        <td class="px-3 py-2.5">
                            @if($h->presentacion)
                            <span class="px-2 py-0.5 rounded-md bg-indigo-50 dark:bg-indigo-500/10 text-indigo-950 dark:text-indigo-300 border border-indigo-200 text-[11px] font-medium">
                                {{ $h->presentacion->nombre }} (x{{ $h->presentacion->unidades_por_presentacion }})
                            </span>
                            @else
                            <span class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-[11px] font-medium">
                                Unidad Base
                            </span>
                            @endif
                        </td>
                        <td class="px-3 py-2.5 text-right font-mono font-bold text-slate-900 dark:text-white text-sm">
                            C$ {{ number_format($h->precio, 2) }}
                        </td>
                        <td class="px-3 py-2.5 text-center">
                            @if(is_null($h->vigente_hasta))
                            <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-950 dark:text-emerald-400 border border-emerald-200">
                                Vigente
                            </span>
                            @else
                            <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-500">
                                Cerrado ({{ $h->vigente_hasta->format('d/m/Y') }})
                            </span>
                            @endif
                        </td>
                        <td class="px-3 py-2.5 font-medium text-slate-800 dark:text-slate-200">
                            {{ $h->usuario->name ?? 'Sistema' }}
                        </td>
                        <td class="px-4 py-2.5 text-slate-700 dark:text-slate-400 text-xs">
                            {{ $h->motivo ?? 'Sin motivo registrado' }}
                        </td>
                        <td class="px-3 py-2.5 text-right">
                            @if($h->producto)
                            <a href="{{ route('precios.show', $h->producto) }}" class="p-1.5 rounded-lg bg-blue-50 text-blue-950 hover:bg-blue-100 inline-flex" title="Ver ficha del medicamento">
                                <svg class="w-3.5 h-3.5 text-blue-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-slate-500">
                            No se encontraron registros de cambios de precio con los filtros indicados.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($historial->hasPages())
        <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50">
            {{ $historial->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
