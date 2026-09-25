@extends('layouts.app')

@section('title', 'Control de Cajas')

@section('content')
<div class="space-y-4" x-data="{
    modalNuevaCaja: false,
    modalEditarCaja: false,
    modalAbrirTurno: false,
    cajaEdit: { id: null, nombre: '', codigo: '', ubicacion: '', descripcion: '' },
    cajaAbrir: { id: null, nombre: '' },
    abrirEditarCaja(caja) {
        this.cajaEdit = {
            id: caja.id,
            nombre: caja.nombre,
            codigo: caja.codigo,
            ubicacion: caja.ubicacion || '',
            descripcion: caja.descripcion || '',
        };
        this.modalEditarCaja = true;
    },
    abrirTurno(caja) {
        this.cajaAbrir = { id: caja.id, nombre: caja.nombre };
        this.modalAbrirTurno = true;
    }
}">

    {{-- Breadcrumb --}}
    <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-800 dark:text-slate-200 font-semibold">Control de Cajas</span>
    </nav>

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-1 border-b border-slate-300/80 dark:border-slate-800">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">Control de Cajas</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Gestión de turnos, apertura, cierre y arqueo de cajas registradoras.</p>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <a href="{{ route('cajas.sesiones') }}"
               class="px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-xl text-xs font-semibold shadow-xs transition flex items-center space-x-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Historial de Turnos</span>
            </a>
            @can('crear cajas')
            <button type="button" @click="modalNuevaCaja = true"
                    class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold shadow-xs transition flex items-center space-x-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Nueva Caja</span>
            </button>
            @endcan
        </div>
    </div>

    {{-- Flash --}}
    @if(session('success'))
    <div class="p-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-300 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 text-xs flex items-center justify-between">
        <div class="flex items-center space-x-2">
            <svg class="w-4 h-4 shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('success') }}</span>
        </div>
        <button onclick="this.parentElement.remove()" class="text-slate-400 hover:text-slate-600 font-bold">&times;</button>
    </div>
    @endif
    @if(session('error'))
    <div class="p-3 rounded-xl bg-rose-50 dark:bg-rose-950/60 border border-rose-300 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-xs flex items-center justify-between">
        <div class="flex items-center space-x-2">
            <svg class="w-4 h-4 shrink-0 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('error') }}</span>
        </div>
        <button onclick="this.parentElement.remove()" class="text-slate-400 hover:text-slate-600 font-bold">&times;</button>
    </div>
    @endif

    {{-- Sesión activa del usuario actual --}}
    @if($sesionUsuario)
    <div class="p-3.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 flex items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse shrink-0"></div>
            <div>
                <p class="text-xs font-semibold text-emerald-800 dark:text-emerald-300">Tienes un turno abierto en <span class="font-bold">{{ $sesionUsuario->caja->nombre }}</span></p>
                <p class="text-[11px] text-emerald-700 dark:text-emerald-400">Efectivo esperado: ${{ number_format($sesionUsuario->monto_esperado_efectivo, 2) }} · Apertura: {{ $sesionUsuario->fecha_apertura->format('H:i') }}</p>
            </div>
        </div>
        <a href="{{ route('cajas.show', $sesionUsuario) }}"
           class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold transition shrink-0">
            Ver Arqueo
        </a>
    </div>
    @endif

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="bg-white dark:bg-slate-900 rounded-xl p-3.5 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Total de Cajas</p>
                <p class="text-lg font-bold text-slate-900 dark:text-white mt-0.5">{{ $totalCajas }}</p>
            </div>
            <div class="w-9 h-9 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-xl p-3.5 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Turnos Abiertos</p>
                <p class="text-lg font-bold text-emerald-600 dark:text-emerald-400 mt-0.5">{{ $cajasAbiertas }}</p>
            </div>
            <div class="w-9 h-9 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-xl p-3.5 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Cajas Cerradas</p>
                <p class="text-lg font-bold text-slate-700 dark:text-slate-300 mt-0.5">{{ $cajasCerradas }}</p>
            </div>
            <div class="w-9 h-9 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-xl p-3.5 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Efectivo en Cajas</p>
                <p class="text-lg font-bold text-amber-600 dark:text-amber-400 mt-0.5">${{ number_format($efectivoTotalCajas, 2) }}</p>
            </div>
            <div class="w-9 h-9 rounded-lg bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
    </div>

    {{-- Lista de Cajas --}}
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-200">Cajas Registradas</h2>
            <span class="text-xs text-slate-400 dark:text-slate-500">{{ $totalCajas }} caja(s)</span>
        </div>

        @if($cajas->isEmpty())
        <div class="p-10 text-center">
            <svg class="w-12 h-12 text-slate-300 dark:text-slate-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
            </svg>
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">No hay cajas registradas.</p>
            @can('crear cajas')
            <button type="button" @click="modalNuevaCaja = true"
                    class="mt-3 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold transition inline-flex items-center space-x-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Registrar Primera Caja</span>
            </button>
            @endcan
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-800/60">
                    <tr>
                        <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Caja</th>
                        <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Código</th>
                        <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Ubicación</th>
                        <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Estado del Turno</th>
                        <th class="px-4 py-2.5 text-right text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Efectivo</th>
                        <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Cajero Activo</th>
                        <th class="px-4 py-2.5 text-center text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Estado Caja</th>
                        <th class="px-4 py-2.5 text-right text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($cajas as $caja)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                        <td class="px-4 py-3">
                            <p class="text-xs font-semibold text-slate-800 dark:text-slate-200">{{ $caja->nombre }}</p>
                            @if($caja->descripcion)
                                <p class="text-[11px] text-slate-400 dark:text-slate-500">{{ Str::limit($caja->descripcion, 40) }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-xs font-mono font-semibold text-slate-600 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded">{{ $caja->codigo }}</span>
                        </td>
                        <td class="px-4 py-3 text-xs text-slate-500 dark:text-slate-400">{{ $caja->ubicacion ?? '—' }}</td>
                        <td class="px-4 py-3">
                            @if($caja->sesionActiva)
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    Turno Abierto
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">Sin Turno</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            @if($caja->sesionActiva)
                                <span class="text-xs font-semibold text-slate-800 dark:text-slate-200 font-mono">${{ number_format($caja->sesionActiva->monto_esperado_efectivo, 2) }}</span>
                            @else
                                <span class="text-xs text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs text-slate-600 dark:text-slate-300">
                            {{ $caja->sesionActiva?->usuario?->name ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($caja->activo)
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-sky-100 dark:bg-sky-950/60 text-sky-800 dark:text-sky-300">Activa</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-rose-100 dark:bg-rose-950/60 text-rose-800 dark:text-rose-300">Inactiva</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-1.5 flex-wrap">
                                @if($caja->sesionActiva)
                                    {{-- Ver Arqueo: solo el dueño de la sesión O admin --}}
                                    @if($caja->sesionActiva->user_id === auth()->id() || auth()->user()->hasRole('admin'))
                                        <a href="{{ route('cajas.show', $caja->sesionActiva) }}"
                                           class="px-2.5 py-1 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 text-[11px] font-semibold transition inline-flex items-center gap-1"
                                           title="Ver detalle de ventas, movimientos y arqueo">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <span>Ver Arqueo</span>
                                        </a>
                                    @endif

                                    {{-- Cerrar: dueño de la sesión O admin (cerrar turno de otro) --}}
                                    @can('cerrar caja')
                                    @if($caja->sesionActiva->user_id === auth()->id())
                                        <a href="{{ route('cajas.show', ['sesion' => $caja->sesionActiva, 'cerrar' => 1]) }}"
                                           class="px-2.5 py-1 rounded-lg bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-400 hover:bg-rose-100 dark:hover:bg-rose-900/60 text-[11px] font-semibold transition inline-flex items-center gap-1"
                                           title="Cerrar mi turno y realizar arqueo">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            <span>Cerrar Turno</span>
                                        </a>
                                    @elseif(auth()->user()->hasRole('admin'))
                                        <a href="{{ route('cajas.show', ['sesion' => $caja->sesionActiva, 'cerrar' => 1]) }}"
                                           class="px-2.5 py-1 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-[11px] font-semibold transition inline-flex items-center gap-1 shadow-2xs"
                                           title="Cerrar el turno de {{ $caja->sesionActiva->usuario?->name }} como Administrador">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            <span>Cerrar Turno (Admin)</span>
                                        </a>
                                    @endif
                                    @endcan

                                    {{-- Indicador para otros usuarios: caja ocupada por otro cajero --}}
                                    @if($caja->sesionActiva->user_id !== auth()->id() && !auth()->user()->hasRole('admin'))
                                        <span class="px-2 py-0.5 rounded-lg bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 text-[10px] font-medium border border-amber-200 dark:border-amber-800"
                                              title="Turno abierto por {{ $caja->sesionActiva->usuario?->name }}">
                                            En uso · {{ Str::words($caja->sesionActiva->usuario?->name ?? '', 1, '') }}
                                        </span>
                                    @endif
                                @else
                                    @if($caja->activo)
                                        @can('abrir caja')
                                            @if(!$sesionUsuario)
                                                <button type="button"
                                                        @click="abrirTurno({ id: {{ $caja->id }}, nombre: '{{ addslashes($caja->nombre) }}' })"
                                                        class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-semibold transition inline-flex items-center gap-1 shadow-2xs">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                                    <span>Abrir Turno</span>
                                                </button>
                                            @else
                                                <span class="px-2 py-0.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 text-[10px] font-medium" title="Ya tienes el turno #{{ $sesionUsuario->id }} abierto en {{ $sesionUsuario->caja->nombre }}">
                                                    Turno activo en otra caja
                                                </span>
                                            @endif
                                        @endcan
                                    @endif

                                    @can('editar cajas')
                                    <button type="button"
                                            @click="abrirEditarCaja({ id: {{ $caja->id }}, nombre: '{{ addslashes($caja->nombre) }}', codigo: '{{ addslashes($caja->codigo) }}', ubicacion: '{{ addslashes($caja->ubicacion ?? '') }}', descripcion: '{{ addslashes($caja->descripcion ?? '') }}' })"
                                            class="px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700 text-[11px] font-semibold transition">
                                        Editar
                                    </button>
                                    @endcan

                                    @can('desactivar cajas')
                                    {{-- Botón Activar / Desactivar (Toggle) --}}
                                    <form method="POST" action="{{ route('cajas.toggle', $caja) }}" class="inline"
                                          onsubmit="return confirm('¿{{ $caja->activo ? 'Desactivar' : 'Activar' }} la caja \'{{ $caja->nombre }}\'?')">
                                        @csrf
                                        <button type="submit"
                                                class="px-2.5 py-1 rounded-lg {{ $caja->activo ? 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 hover:bg-amber-100' : 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 hover:bg-emerald-100' }} text-[11px] font-semibold transition"
                                                title="{{ $caja->activo ? 'Desactivar caja' : 'Activar caja' }}">
                                            {{ $caja->activo ? 'Desactivar' : 'Activar' }}
                                        </button>
                                    </form>

                                    {{-- Botón Eliminar Caja (Quitar permanentemente) --}}
                                    @if(auth()->user()->hasRole('admin') || auth()->user()->can('desactivar cajas'))
                                    <form method="POST" action="{{ route('cajas.destroy', $caja) }}" class="inline"
                                          onsubmit="return confirm('¿Estás seguro de ELIMINAR permanentemente la caja \'{{ $caja->nombre }}\' ({{ $caja->codigo }})? Esta acción la quitará por completo del sistema.')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="px-2.5 py-1 rounded-lg bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-400 hover:bg-rose-100 dark:hover:bg-rose-900/60 text-[11px] font-semibold transition inline-flex items-center gap-1"
                                                title="Eliminar permanentemente esta caja">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            <span>Eliminar</span>
                                        </button>
                                    </form>
                                    @endif
                                    @endcan
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    {{-- Modal: Nueva Caja --}}
    <div x-show="modalNuevaCaja" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm"
         @click.self="modalNuevaCaja = false">
        <div @click.stop class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800">
            <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3 mb-5">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Registrar Nueva Caja</h3>
                <button @click="modalNuevaCaja = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form method="POST" action="{{ route('cajas.store') }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div class="col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nombre de la Caja <span class="text-rose-500">*</span></label>
                        <input type="text" name="nombre" required maxlength="100" value="{{ old('nombre') }}"
                               class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm focus:ring-emerald-500 focus:border-emerald-500" placeholder="Ej: Caja Principal, Caja 2...">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Código Identificador <span class="text-rose-500">*</span></label>
                        <input type="text" name="codigo" required maxlength="50" value="{{ old('codigo') }}"
                               class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm focus:ring-emerald-500 focus:border-emerald-500 font-mono" placeholder="Ej: CAJA-01">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Ubicación</label>
                        <input type="text" name="ubicacion" maxlength="100" value="{{ old('ubicacion') }}"
                               class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm focus:ring-emerald-500 focus:border-emerald-500" placeholder="Mostrador, Farmacia...">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Descripción (opcional)</label>
                        <textarea name="descripcion" rows="2" maxlength="255"
                                  class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm focus:ring-emerald-500 focus:border-emerald-500">{{ old('descripcion') }}</textarea>
                    </div>
                </div>
                <div class="flex gap-2 pt-1">
                    <button type="button" @click="modalNuevaCaja = false"
                            class="flex-1 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                        Cancelar
                    </button>
                    <button type="submit"
                            class="flex-1 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold shadow-xs transition">
                        Registrar Caja
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal: Editar Caja --}}
    <div x-show="modalEditarCaja" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm"
         @click.self="modalEditarCaja = false">
        <div @click.stop class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800">
            <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3 mb-5">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Editar Caja</h3>
                <button @click="modalEditarCaja = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form method="POST" :action="`/cajas/${cajaEdit.id}`" class="space-y-4">
                @csrf @method('PUT')
                <div class="grid grid-cols-2 gap-3">
                    <div class="col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nombre de la Caja <span class="text-rose-500">*</span></label>
                        <input type="text" name="nombre" required maxlength="100" x-model="cajaEdit.nombre"
                               class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Código <span class="text-rose-500">*</span></label>
                        <input type="text" name="codigo" required maxlength="50" x-model="cajaEdit.codigo"
                               class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm focus:ring-emerald-500 focus:border-emerald-500 font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Ubicación</label>
                        <input type="text" name="ubicacion" maxlength="100" x-model="cajaEdit.ubicacion"
                               class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Descripción</label>
                        <textarea name="descripcion" rows="2" maxlength="255" x-model="cajaEdit.descripcion"
                                  class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm focus:ring-emerald-500 focus:border-emerald-500"></textarea>
                    </div>
                </div>
                <div class="flex gap-2 pt-1">
                    <button type="button" @click="modalEditarCaja = false"
                            class="flex-1 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                        Cancelar
                    </button>
                    <button type="submit"
                            class="flex-1 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold shadow-xs transition">
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal: Abrir Turno --}}
    <div x-show="modalAbrirTurno" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm"
         @click.self="modalAbrirTurno = false">
        <div @click.stop class="bg-white dark:bg-slate-900 rounded-2xl max-w-sm w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800">
            <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3 mb-5">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Abrir Turno</h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5" x-text="cajaAbrir.nombre"></p>
                </div>
                <button @click="modalAbrirTurno = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form method="POST" :action="`/cajas/${cajaAbrir.id}/abrir`" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Fondo Inicial de Apertura <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-semibold">$</span>
                        <input type="number" name="monto_inicial" step="0.01" min="0" required
                               class="w-full pl-7 rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm focus:ring-emerald-500 focus:border-emerald-500" placeholder="0.00">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Observaciones de Apertura (opcional)</label>
                    <textarea name="observaciones_apertura" rows="2" maxlength="255"
                              class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm focus:ring-emerald-500 focus:border-emerald-500"
                              placeholder="Notas sobre la apertura del turno..."></textarea>
                </div>
                <div class="flex gap-2 pt-1">
                    <button type="button" @click="modalAbrirTurno = false"
                            class="flex-1 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                        Cancelar
                    </button>
                    <button type="submit"
                            class="flex-1 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold shadow-xs transition">
                        Abrir Turno
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
