@extends('layouts.app')
@section('title', $devolucionCompra->numero_devolucion . ' — Devolución a Proveedor — FarmaBien')
@section('content')
<div class="space-y-4">

    {{-- Fila 1 — Breadcrumb --}}
    <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('compras.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Compras</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('compras.devoluciones.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Devoluciones a Proveedor</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-800 dark:text-slate-200 font-semibold">{{ $devolucionCompra->numero_devolucion }}</span>
    </nav>

    {{-- Fila 2 — Título y Acciones --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white flex items-center gap-3 flex-wrap">
                <span>{{ $devolucionCompra->numero_devolucion }}</span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold border {{ $devolucionCompra->estadoBadge }}">
                    {{ ucfirst($devolucionCompra->estado) }}
                </span>
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Registrada por <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $devolucionCompra->usuario->name ?? '—' }}</span>
                el {{ $devolucionCompra->created_at->format('d/m/Y H:i') }}
                @if($devolucionCompra->fecha_envio)
                · Enviada al proveedor el {{ $devolucionCompra->fecha_envio->format('d/m/Y H:i') }}
                @endif
            </p>
        </div>

        <div class="flex items-center gap-2 shrink-0 flex-wrap">
            {{-- Botón de Navegación --}}
            <a href="{{ route('compras.devoluciones.index') }}"
               class="inline-flex items-center space-x-1.5 px-3.5 py-1.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold shadow-xs transition">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span>Devoluciones a Proveedor</span>
            </a>

            {{-- Modo Full --}}
            <button type="button"
                    @click="$dispatch('toggle-pos-fullscreen')"
                    title="Modo Pantalla Completa / Ocultar Barras"
                    class="inline-flex items-center space-x-1.5 px-3.5 py-1.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold shadow-xs transition cursor-pointer">
                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                <span x-text="posFullscreen ? 'Salir Full' : 'Modo Full'">Modo Full</span>
            </button>
        </div>
    </div>

    @if(session('success'))
    <div class="px-4 py-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-300 dark:border-emerald-700 text-xs text-emerald-950 dark:text-emerald-300 font-semibold flex items-center space-x-2">
        <svg class="w-4 h-4 shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span>{{ session('success') }}</span>
    </div>
    @endif
    @if(session('error'))
    <div class="px-4 py-3 rounded-xl bg-red-50 dark:bg-red-950/40 border border-red-300 dark:border-red-700 text-xs text-red-950 dark:text-red-300 font-semibold flex items-center space-x-2">
        <svg class="w-4 h-4 shrink-0 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    {{-- 4 Metric Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-600 dark:text-slate-400">Total Devuelto</p>
                <p class="text-xl font-bold text-red-950 dark:text-red-400 mt-0.5 font-mono">C$ {{ number_format($devolucionCompra->total_devolucion, 2) }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400 border border-red-100 dark:border-red-900/50 flex items-center justify-center font-bold text-xs shrink-0 shadow-2xs">
                <span>C$</span>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-600 dark:text-slate-400">Proveedor</p>
                <p class="text-sm font-bold text-slate-900 dark:text-white mt-0.5 truncate">{{ $devolucionCompra->proveedor->nombre ?? '—' }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 flex items-center justify-center shrink-0 shadow-2xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-600 dark:text-slate-400">Lotes Devueltos</p>
                <p class="text-xl font-bold text-slate-900 dark:text-white mt-0.5">{{ $devolucionCompra->detalles->count() }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/50 flex items-center justify-center shrink-0 shadow-2xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-600 dark:text-slate-400">Compra de Origen</p>
                <div class="mt-1">
                    @if($devolucionCompra->compra)
                    <a href="{{ route('compras.show', $devolucionCompra->compra) }}"
                       class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 hover:bg-indigo-100 dark:hover:bg-indigo-900/60 text-indigo-950 dark:text-indigo-200 border border-indigo-200 dark:border-indigo-800 text-xs font-semibold transition shadow-2xs">
                        <svg class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        <span>Compra #{{ $devolucionCompra->compra_id }}</span>
                    </a>
                    @else
                    <span class="text-xs text-slate-500 dark:text-slate-400">Sin vincular</span>
                    @endif
                </div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 flex items-center justify-center shrink-0 shadow-2xs">
                <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            </div>
        </div>
    </div>

    {{-- Motivo --}}
    <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs space-y-2">
        <div class="flex items-center space-x-2 pb-2 border-b border-slate-200 dark:border-slate-800">
            <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
            <h3 class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">Motivo de la Devolución</h3>
        </div>
        <p class="text-xs text-slate-700 dark:text-slate-300 leading-relaxed font-medium">{{ $devolucionCompra->motivo }}</p>
    </div>

    {{-- Tabla de lotes devueltos --}}
    <div class="rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 flex items-center justify-between">
            <h3 class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">Lotes Devueltos</h3>
            <span class="text-xs font-semibold text-slate-600 dark:text-slate-400">{{ $devolucionCompra->detalles->count() }} lote(s)</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700 dark:text-slate-300">
                <thead class="bg-slate-100/80 dark:bg-slate-800/80 text-slate-600 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800 text-[11px] font-bold uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3">Medicamento</th>
                        <th class="px-4 py-3">Nº Lote</th>
                        <th class="px-4 py-3 text-center">Cantidad</th>
                        <th class="px-4 py-3 text-right">C$ Unit.</th>
                        <th class="px-4 py-3 text-right">Subtotal</th>
                        <th class="px-4 py-3">Motivo Ítem</th>
                        <th class="px-4 py-3 text-center">MINSA</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200/70 dark:divide-slate-800/70">
                    @foreach($devolucionCompra->detalles as $det)
                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition">
                        <td class="px-4 py-3">
                            <span class="font-bold text-slate-900 dark:text-white block">{{ $det->producto->nombre }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="font-mono font-bold text-slate-800 dark:text-slate-200">{{ $det->lote->numero_lote ?? '—' }}</span>
                        </td>
                        <td class="px-4 py-3 text-center font-bold text-red-950 dark:text-red-400 font-mono">
                            -{{ $det->cantidad }}
                        </td>
                        <td class="px-4 py-3 text-right font-mono">C$ {{ number_format($det->precio_unitario, 2) }}</td>
                        <td class="px-4 py-3 text-right font-mono font-bold text-slate-900 dark:text-white">C$ {{ number_format($det->subtotal, 2) }}</td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $det->motivo_detalle ?? '—' }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($det->producto && $det->producto->esControlado())
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-950 border border-red-200">CONTROLADO</span>
                            @else
                            <span class="text-slate-400 text-[10px]">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-slate-50 dark:bg-slate-800/40 border-t border-slate-200 dark:border-slate-700">
                    <tr>
                        <td colspan="4" class="px-4 py-3 text-right text-xs font-bold text-slate-700 dark:text-slate-300">TOTAL</td>
                        <td class="px-4 py-3 text-right font-mono font-bold text-red-950 dark:text-red-400">
                            C$ {{ number_format($devolucionCompra->total_devolucion, 2) }}
                        </td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Acciones de estado y Anulación --}}
    <div x-data="{ modalAnular: false, motivo: '' }" class="pt-2">
        <div class="flex items-center gap-2 justify-end flex-wrap">
            @if($devolucionCompra->estado === 'pendiente')
            <form method="POST" action="{{ route('compras.devoluciones.enviar', $devolucionCompra) }}">
                @csrf
                <button type="submit"
                        class="inline-flex items-center space-x-1.5 px-3.5 py-2 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-xs font-semibold rounded-xl shadow-xs transition cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    <span>Marcar como Enviada al Proveedor</span>
                </button>
            </form>
            @elseif($devolucionCompra->estado === 'enviada')
            <form method="POST" action="{{ route('compras.devoluciones.confirmar', $devolucionCompra) }}">
                @csrf
                <button type="submit"
                        class="inline-flex items-center space-x-1.5 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-semibold rounded-xl shadow-xs transition cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Confirmar Aceptación del Proveedor</span>
                </button>
            </form>
            @endif

            @can('anular compras')
            @if($devolucionCompra->estado !== 'rechazada')
            <button type="button" 
                    @click="modalAnular = true"
                    class="inline-flex items-center space-x-1.5 px-3.5 py-2 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/40 dark:hover:bg-rose-900/60 text-rose-800 dark:text-rose-300 border border-rose-200 dark:border-rose-800/80 text-xs font-bold rounded-xl shadow-xs transition cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                <span>Anular Devolución (Revertir Stock)</span>
            </button>
            @endif
            @endcan
        </div>

        {{-- Modal de Anulación --}}
        <div x-show="modalAnular" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div @click.away="modalAnular = false"
                 class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4">
                <div class="flex items-center space-x-3 text-rose-600">
                    <div class="w-10 h-10 rounded-xl bg-rose-100 dark:bg-rose-950/60 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Anular Devolución</h3>
                        <p class="text-xs text-slate-500">{{ $devolucionCompra->numero_devolucion }}</p>
                    </div>
                </div>

                <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                    Al anular esta devolución, el stock de los lotes afectados será <strong>restituido automáticamente</strong>, se registrará un contra-asiento de entrada en el Kardex y se revertirá el egreso fiscal en el Libro MINSA si aplica.
                </p>

                <form method="POST" action="{{ route('compras.devoluciones.anular', $devolucionCompra) }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Motivo de la anulación *</label>
                        <textarea name="motivo" x-model="motivo" rows="3" required placeholder="Ej: Devolución rechazada por el proveedor / Error en lote seleccionado..." class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 p-2.5 text-slate-900 dark:text-white focus:ring-2 focus:ring-rose-500"></textarea>
                    </div>

                    <div class="flex items-center justify-end space-x-2 pt-2">
                        <button type="button" @click="modalAnular = false" class="px-4 py-2 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition">
                            Cancelar
                        </button>
                        <button type="submit" :disabled="motivo.trim().length < 5" class="px-4 py-2 text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 disabled:opacity-50 rounded-xl shadow-xs transition">
                            Confirmar Anulación
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection

