@extends('layouts.app')

@section('title', 'Arqueo de Caja — ' . $sesion->caja->nombre)

@section('content')
<div class="space-y-4" x-data="{
    activeTab: 'ventas',
    modalMovimiento: false,
    modalCerrar: {{ request()->boolean('cerrar') ? 'true' : 'false' }},
    tipoMovimiento: 'ingreso',
    montoMovimiento: '',
    montoFinal: '',
    diferencia: 0,
    montoEsperado: {{ $sesion->monto_esperado_efectivo ?? 0 }},
    calcularDiferencia() {
        const mf = parseFloat(this.montoFinal) || 0;
        this.diferencia = mf - this.montoEsperado;
    },
    abrirMovimiento(tipo) {
        this.tipoMovimiento = tipo;
        this.montoMovimiento = '';
        this.modalMovimiento = true;
    }
}">

    {{-- Breadcrumb --}}
    <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('cajas.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Control de Cajas</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-800 dark:text-slate-200 font-semibold">{{ $sesion->caja->nombre }}</span>
    </nav>

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-1 border-b border-slate-300/80 dark:border-slate-800">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-xl font-bold text-slate-900 dark:text-white">
                    Arqueo — {{ $sesion->caja->nombre }}
                </h1>
                @if($sesion->estaAbierta())
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                        Turno Abierto
                    </span>
                @else
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                        Cerrado
                    </span>
                @endif
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Cajero: <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $sesion->usuario->name }}</span>
                &nbsp;&middot;&nbsp;
                Apertura: <span class="font-semibold">{{ $sesion->fecha_apertura->format('d/m/Y H:i') }}</span>
                @if($sesion->fecha_cierre)
                    &nbsp;&middot;&nbsp;
                    Cierre: <span class="font-semibold">{{ $sesion->fecha_cierre->format('d/m/Y H:i') }}</span>
                @endif
            </p>
        </div>

        <div class="flex items-center gap-2 shrink-0 flex-wrap">
            <a href="{{ route('cajas.index') }}"
               class="px-3.5 py-2 rounded-xl bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold transition flex items-center space-x-1.5 shrink-0 shadow-2xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Cajas</span>
            </a>

            <!-- Botón Modo Full -->
            <button type="button" 
                    @click="$dispatch('toggle-pos-fullscreen')"
                    title="Modo Pantalla Completa / Ocultar Barras"
                    class="inline-flex items-center space-x-1.5 px-3 py-2 rounded-xl bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold shadow-2xs transition cursor-pointer">
                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                <span x-text="posFullscreen ? 'Salir Full' : 'Modo Full'">Modo Full</span>
            </button>

            <a href="{{ route('cajas.ticket', $sesion) }}" target="_blank"
               class="inline-flex items-center space-x-1.5 px-3.5 py-2 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 hover:bg-indigo-100 dark:hover:bg-indigo-900/60 text-indigo-950 dark:text-indigo-200 text-xs font-bold border border-indigo-200 dark:border-indigo-800 transition shadow-2xs">
                <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Ticket Arqueo</span>
            </a>

            @if($sesion->estaAbierta())
                @if($sesion->user_id === auth()->id() || auth()->user()->hasRole(['Admin', 'admin']) || auth()->user()->can('administrar cajas'))
                    @can('cerrar caja')
                    <button type="button" @click="modalCerrar = true"
                            class="px-3.5 py-2 bg-rose-50 dark:bg-rose-950/60 hover:bg-rose-100 dark:hover:bg-rose-900/60 text-rose-950 dark:text-rose-200 border border-rose-200 dark:border-rose-800 rounded-xl text-xs font-bold shadow-2xs transition flex items-center space-x-1.5 cursor-pointer">
                        <svg class="w-3.5 h-3.5 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        <span>Cerrar Turno</span>
                    </button>
                    @endcan

                    @can('registrar movimientos caja')
                    <button type="button" @click="abrirMovimiento('ingreso')"
                            class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-xs transition flex items-center space-x-1.5 cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Movimiento</span>
                    </button>
                    @endcan
                @endif
            @endif
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div class="bg-white dark:bg-slate-900 rounded-xl p-3.5 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Fondo Inicial</p>
                <p class="text-lg font-bold text-slate-900 dark:text-white mt-0.5">C$ {{ number_format($sesion->monto_inicial, 2) }}</p>
            </div>
            <div class="w-9 h-9 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-xl p-3.5 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Total Ventas</p>
                <p class="text-lg font-bold text-emerald-950 dark:text-emerald-300 mt-0.5">C$ {{ number_format($sesion->total_ventas ?? 0, 2) }}</p>
            </div>
            <div class="w-9 h-9 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-xl p-3.5 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Efectivo Esperado</p>
                <p class="text-lg font-bold text-amber-950 dark:text-amber-300 mt-0.5">C$ {{ number_format($sesion->monto_esperado_efectivo ?? 0, 2) }}</p>
            </div>
            <div class="w-9 h-9 rounded-lg bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        @php
            $dif = ($sesion->monto_final_efectivo ?? null) !== null
                ? $sesion->monto_final_efectivo - $sesion->monto_esperado_efectivo
                : null;
        @endphp
        <div class="bg-white dark:bg-slate-900 rounded-xl p-3.5 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Diferencia</p>
                @if($dif !== null)
                    <p class="text-lg font-bold mt-0.5 {{ $dif == 0 ? 'text-emerald-950 dark:text-emerald-300' : ($dif > 0 ? 'text-sky-950 dark:text-sky-300' : 'text-rose-950 dark:text-rose-300') }}">
                        {{ $dif >= 0 ? '+' : '' }}C$ {{ number_format($dif, 2) }}
                    </p>
                @else
                    <p class="text-sm text-slate-400 dark:text-slate-500 mt-0.5">Pendiente</p>
                @endif
            </div>
            <div class="w-9 h-9 rounded-lg {{ $dif !== null && $dif < 0 ? 'bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400' : 'bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400' }} flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </div>
        </div>
    </div>

    {{-- Desglose por método de pago --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <div class="bg-white dark:bg-slate-900 rounded-xl p-3.5 border border-slate-200 dark:border-slate-800 shadow-xs">
            <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400 mb-1">Ventas en Efectivo</p>
            <p class="text-base font-bold text-slate-900 dark:text-white">C$ {{ number_format($sesion->total_ventas_efectivo ?? 0, 2) }}</p>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-xl p-3.5 border border-slate-200 dark:border-slate-800 shadow-xs">
            <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400 mb-1">Ventas con Tarjeta</p>
            <p class="text-base font-bold text-slate-900 dark:text-white">C$ {{ number_format($sesion->total_ventas_tarjeta ?? 0, 2) }}</p>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-xl p-3.5 border border-slate-200 dark:border-slate-800 shadow-xs">
            <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400 mb-1">Transferencias</p>
            <p class="text-base font-bold text-slate-900 dark:text-white">C$ {{ number_format($sesion->total_ventas_transferencia ?? 0, 2) }}</p>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="border-b border-slate-200 dark:border-slate-800">
            <nav class="flex space-x-0" aria-label="Tabs">
                <button @click="activeTab = 'ventas'" type="button"
                        :class="activeTab === 'ventas' ? 'border-b-2 border-emerald-600 text-emerald-700 dark:text-emerald-400 font-semibold bg-emerald-50/30 dark:bg-emerald-950/10' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200'"
                        class="px-5 py-3 text-xs transition flex items-center space-x-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Ventas del Turno ({{ $ventas->total() }})</span>
                </button>
                <button @click="activeTab = 'movimientos'" type="button"
                        :class="activeTab === 'movimientos' ? 'border-b-2 border-emerald-600 text-emerald-700 dark:text-emerald-400 font-semibold bg-emerald-50/30 dark:bg-emerald-950/10' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200'"
                        class="px-5 py-3 text-xs transition flex items-center space-x-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>
                    <span>Movimientos Manuales ({{ $movimientos->count() }})</span>
                </button>
            </nav>
        </div>

        {{-- Tab: Ventas --}}
        <div x-show="activeTab === 'ventas'" x-cloak>
            @if($ventas->isEmpty())
                <div class="p-8 text-center">
                    <svg class="w-10 h-10 text-slate-300 dark:text-slate-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <p class="text-sm text-slate-400 dark:text-slate-500">No hay ventas registradas en este turno.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-800/60">
                            <tr>
                                <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Comprobante</th>
                                <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Cliente</th>
                                <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Método</th>
                                <th class="px-4 py-2.5 text-right text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Total</th>
                                <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Hora</th>
                                <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach($ventas as $venta)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                                <td class="px-4 py-2.5">
                                    <a href="{{ route('ventas.show', $venta) }}" class="text-xs font-mono font-semibold text-emerald-700 dark:text-emerald-400 hover:underline">
                                        {{ $venta->numero_comprobante }}
                                    </a>
                                </td>
                                <td class="px-4 py-2.5 text-xs text-slate-700 dark:text-slate-300">{{ $venta->cliente?->nombre ?? 'Consumidor Final' }}</td>
                                <td class="px-4 py-2.5">
                                    <span class="px-1.5 py-0.5 rounded text-[11px] font-medium
                                        {{ $venta->metodo_pago === 'efectivo' ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300' : '' }}
                                        {{ $venta->metodo_pago === 'tarjeta' ? 'bg-sky-50 dark:bg-sky-950/60 text-sky-700 dark:text-sky-300' : '' }}
                                        {{ $venta->metodo_pago === 'transferencia' ? 'bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300' : '' }}">
                                        {{ ucfirst($venta->metodo_pago) }}
                                    </span>
                                </td>
                                <td class="px-4 py-2.5 text-xs font-semibold text-slate-900 dark:text-white text-right">C$ {{ number_format($venta->total, 2) }}</td>
                                <td class="px-4 py-2.5 text-xs text-slate-500 dark:text-slate-400">{{ $venta->fecha instanceof \Carbon\Carbon ? $venta->fecha->format('H:i') : \Carbon\Carbon::parse($venta->fecha)->format('H:i') }}</td>
                                <td class="px-4 py-2.5">
                                    <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold
                                        {{ $venta->estado === 'completada' ? 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300' : 'bg-rose-100 dark:bg-rose-950/60 text-rose-800 dark:text-rose-300' }}">
                                        {{ ucfirst($venta->estado) }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($ventas->hasPages())
                <div class="px-4 py-3 border-t border-slate-100 dark:border-slate-800">
                    {{ $ventas->links() }}
                </div>
                @endif
            @endif
        </div>

        {{-- Tab: Movimientos Manuales --}}
        <div x-show="activeTab === 'movimientos'" x-cloak>
            @if($movimientos->isEmpty())
                <div class="p-8 text-center">
                    <svg class="w-10 h-10 text-slate-300 dark:text-slate-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>
                    <p class="text-sm text-slate-400 dark:text-slate-500">No hay movimientos manuales en este turno.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-800/60">
                            <tr>
                                <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Tipo</th>
                                <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Concepto</th>
                                <th class="px-4 py-2.5 text-right text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Monto</th>
                                <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Registrado por</th>
                                <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Hora</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach($movimientos as $mov)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                                <td class="px-4 py-2.5">
                                    <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold
                                        {{ $mov->tipo === 'ingreso' ? 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300' : 'bg-rose-100 dark:bg-rose-950/60 text-rose-800 dark:text-rose-300' }}">
                                        {{ ucfirst($mov->tipo) }}
                                    </span>
                                </td>
                                <td class="px-4 py-2.5 text-xs text-slate-700 dark:text-slate-300">
                                    {{ $mov->concepto }}
                                    @if($mov->comprobante_referencia)
                                        <span class="text-slate-400 dark:text-slate-500">· Ref: {{ $mov->comprobante_referencia }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2.5 text-xs font-semibold text-right {{ $mov->tipo === 'ingreso' ? 'text-emerald-700 dark:text-emerald-400' : 'text-rose-700 dark:text-rose-400' }}">
                                    {{ $mov->tipo === 'ingreso' ? '+' : '-' }}C$ {{ number_format($mov->monto, 2) }}
                                </td>
                                <td class="px-4 py-2.5 text-xs text-slate-600 dark:text-slate-400">{{ $mov->usuario->name }}</td>
                                <td class="px-4 py-2.5 text-xs text-slate-500 dark:text-slate-400">{{ $mov->created_at->format('H:i') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- Modal: Movimiento Manual --}}
    <template x-teleport="body">
        <div x-show="modalMovimiento" x-cloak
             @keydown.escape.window="modalMovimiento = false"
             class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm"
             @click.self="modalMovimiento = false">
            <div @click.stop class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800">
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3 mb-5">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white" x-text="tipoMovimiento === 'ingreso' ? 'Registrar Ingreso Manual' : 'Registrar Egreso / Retiro'"></h3>
                    <button @click="modalMovimiento = false" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition" title="Cerrar">✕</button>
                </div>

                <form method="POST" action="{{ route('cajas.movimientos.store', $sesion) }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="tipo" :value="tipoMovimiento">

                    <div class="flex gap-2">
                        <button type="button" @click="tipoMovimiento = 'ingreso'"
                                :class="tipoMovimiento === 'ingreso' ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-300 dark:border-slate-700'"
                                class="flex-1 py-2 rounded-xl text-xs font-semibold border transition">Ingreso</button>
                        <button type="button" @click="tipoMovimiento = 'egreso'"
                                :class="tipoMovimiento === 'egreso' ? 'bg-rose-600 text-white border-rose-600' : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-300 dark:border-slate-700'"
                                class="flex-1 py-2 rounded-xl text-xs font-semibold border transition">Egreso / Retiro</button>
                    </div>

                    <!-- Indicador de Efectivo Disponible en caso de Egreso -->
                    <div x-show="tipoMovimiento === 'egreso'" class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs">
                        <div class="flex justify-between text-slate-600 dark:text-slate-400">
                            <span>Efectivo disponible en caja:</span>
                            <span class="font-bold text-emerald-600 dark:text-emerald-400 font-mono">C$ {{ number_format($sesion->monto_esperado_efectivo ?? 0, 2) }}</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Monto <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs font-semibold">C$</span>
                            <input type="number" 
                                   name="monto" 
                                   x-model="montoMovimiento"
                                   step="0.01" 
                                   min="0.01" 
                                   :max="tipoMovimiento === 'egreso' ? montoEsperado : null"
                                   required
                                   class="w-full pl-9 rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm focus:ring-emerald-500 focus:border-emerald-500 font-bold" placeholder="0.00">
                        </div>
                    </div>

                    <!-- Alerta si intenta sustraer más dinero del que hay -->
                    <div x-show="tipoMovimiento === 'egreso' && montoMovimiento !== '' && parseFloat(montoMovimiento) > montoEsperado"
                         class="p-2 rounded-lg bg-rose-100 dark:bg-rose-950/60 border border-rose-300 text-rose-800 dark:text-rose-200 text-xs font-bold animate-pulse">
                        ⚠️ No puedes retirar un monto mayor al efectivo disponible en caja (C$ {{ number_format($sesion->monto_esperado_efectivo ?? 0, 2) }}).
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Concepto / Motivo <span class="text-rose-500">*</span></label>
                        <input type="text" name="concepto" required maxlength="255"
                               class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm focus:ring-emerald-500 focus:border-emerald-500" placeholder="Ej: Pago a proveedor, retiro para caja chica...">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Referencia / Comprobante (opcional)</label>
                        <input type="text" name="comprobante_referencia" maxlength="100"
                               class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm focus:ring-emerald-500 focus:border-emerald-500" placeholder="Número de factura, recibo...">
                    </div>

                    <div class="flex gap-2 pt-1">
                        <button type="button" @click="modalMovimiento = false"
                                class="flex-1 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 transition cursor-pointer">
                            Cancelar
                        </button>
                        <button type="submit"
                                :disabled="tipoMovimiento === 'egreso' && (parseFloat(montoMovimiento) > montoEsperado || montoEsperado <= 0)"
                                :class="tipoMovimiento === 'ingreso' ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-rose-600 hover:bg-rose-700'"
                                class="flex-1 py-2 rounded-xl text-white text-xs font-semibold transition shadow-xs disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer">
                            Registrar Movimiento
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    {{-- Modal: Cierre de Turno --}}
    <template x-teleport="body">
        <div x-show="modalCerrar" x-cloak
             @keydown.escape.window="modalCerrar = false"
             class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm"
             @click.self="modalCerrar = false">
            <div @click.stop class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800">
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3 mb-5">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Cierre Formal de Turno</h3>
                    <button @click="modalCerrar = false" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition" title="Cerrar">✕</button>
                </div>

                <form method="POST" action="{{ route('cajas.cerrar', $sesion) }}" class="space-y-4">
                    @csrf

                    <div class="p-3 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 text-xs text-amber-800 dark:text-amber-300">
                        <p class="font-semibold mb-1">Efectivo esperado en caja: C$ {{ number_format($sesion->monto_esperado_efectivo ?? 0, 2) }}</p>
                        <p>Ingresa el efectivo físico recontado al cerrar el turno para calcular la diferencia.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Efectivo Físico Recontado <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs font-semibold">C$</span>
                            <input type="number" name="monto_final_efectivo" x-model="montoFinal" @input="calcularDiferencia" 
                                   step="0.01" min="0" required
                                   class="w-full pl-9 rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm focus:ring-emerald-500 focus:border-emerald-500" placeholder="0.00">
                        </div>
                    </div>

                    <div x-show="montoFinal !== ''" class="p-3 rounded-xl border text-xs font-semibold text-center transition"
                         :class="diferencia === 0 ? 'bg-emerald-50 dark:bg-emerald-950/40 border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300' : (diferencia > 0 ? 'bg-sky-50 dark:bg-sky-950/40 border-sky-200 dark:border-sky-800 text-sky-800 dark:text-sky-300' : 'bg-rose-50 dark:bg-rose-950/40 border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300')">
                        <span x-text="diferencia === 0 ? 'Cuadre exacto — sin diferencias' : (diferencia > 0 ? 'Sobrante de +C$ ' + parseFloat(diferencia).toFixed(2) : 'Faltante de -C$ ' + Math.abs(parseFloat(diferencia)).toFixed(2))"></span>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Observaciones de Cierre (opcional)</label>
                        <textarea name="observaciones_cierre" rows="2" maxlength="500"
                                  class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm focus:ring-emerald-500 focus:border-emerald-500"
                                  placeholder="Notas sobre el cierre, incidencias, etc."></textarea>
                    </div>

                    <div class="flex gap-2 pt-1">
                        <button type="button" @click="modalCerrar = false"
                                class="flex-1 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="flex-1 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-semibold transition shadow-xs">
                            Cerrar Turno
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>

</div>
@endsection
