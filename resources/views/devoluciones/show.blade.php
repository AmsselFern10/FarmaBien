@extends('layouts.app')

@section('title', "Devolución {$devolucion->numero_devolucion} - FarmaBien")

@section('content')
<div class="space-y-5">
    
    <!-- Breadcrumbs -->
    <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('ventas.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Ventas</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('devoluciones.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Devoluciones</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-800 dark:text-slate-200 font-semibold">{{ $devolucion->numero_devolucion }}</span>
    </nav>

    <!-- Header & Action Toolbar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-1 border-b border-slate-300/80 dark:border-slate-800">
        <div>
            <h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">
                Devolución {{ $devolucion->numero_devolucion }}
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Procesada el {{ $devolucion->fecha ? $devolucion->fecha->format('d/m/Y \a \l\a\s H:i') : '-' }} hrs por <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $devolucion->usuario->name ?? 'Sistema' }}</span>
            </p>
        </div>

        <div class="flex items-center gap-2 shrink-0 flex-wrap">
            <!-- 1. Botón Volver / Precedente (Primero de izquierda a derecha en la barra de acciones) -->
            <a href="{{ route('devoluciones.index') }}" 
               class="inline-flex items-center space-x-1.5 px-3.5 py-2 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold shadow-2xs transition shrink-0">
                <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Devoluciones</span>
            </a>

            <!-- 2. Botón Modo Full Screen -->
            <button type="button" 
                    @click="$dispatch('toggle-pos-fullscreen')"
                    title="Modo Pantalla Completa / Ocultar Barras"
                    class="inline-flex items-center space-x-1.5 px-3 py-2 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold shadow-2xs transition cursor-pointer">
                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                <span x-text="posFullscreen ? 'Salir Full' : 'Modo Full'">Modo Full</span>
            </button>

            <!-- 3. Imprimir Comprobante (Acción Principal - Extrema Derecha) -->
            <a href="{{ route('devoluciones.ticket', $devolucion) }}" target="_blank"
               class="inline-flex items-center space-x-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-semibold rounded-xl shadow-xs transition cursor-pointer">
                <svg class="w-4 h-4 text-emerald-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                <span>Imprimir Comprobante</span>
            </a>
        </div>
    </div>

    <!-- 4 Metric Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Tarjeta 1: Monto Reembolsado -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">Monto Reembolsado</p>
                <p class="text-xl font-black text-rose-950 dark:text-rose-300 mt-0.5">{{ formato_moneda($devolucion->monto_total) }}</p>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Método: <span class="capitalize font-bold text-slate-900 dark:text-slate-200">{{ $devolucion->metodo_reembolso }}</span></p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 border border-rose-100 dark:border-rose-900/50 flex items-center justify-center font-bold text-xs shrink-0 shadow-2xs">
                <span>C$</span>
            </div>
        </div>

        <!-- Tarjeta 2: Estado de la Devolución -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">Estado de la Devolución</p>
                <div class="flex items-center gap-1.5 mt-1.5 flex-wrap">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-950 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                        {{ ucfirst($devolucion->estado) }}
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $devolucion->tipo === 'total' ? 'bg-purple-50 dark:bg-purple-950/60 text-purple-950 dark:text-purple-300 border border-purple-200 dark:border-purple-800' : 'bg-blue-50 dark:bg-blue-950/60 text-blue-950 dark:text-blue-300 border border-blue-200 dark:border-blue-800' }}">
                        {{ ucfirst($devolucion->tipo) }}
                    </span>
                </div>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Reversión verificada</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/50 flex items-center justify-center shrink-0 shadow-2xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <!-- Tarjeta 3: Venta Relacionada -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div class="min-w-0 pr-2">
                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">Venta Relacionada</p>
                <div class="mt-1">
                    <a href="{{ route('ventas.show', $devolucion->venta_id) }}" 
                       class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 hover:bg-indigo-100 dark:hover:bg-indigo-900/60 text-indigo-950 dark:text-indigo-200 border border-indigo-200 dark:border-indigo-800 text-xs font-bold transition shadow-2xs group"
                       title="Ver Detalle de Venta #{{ str_pad($devolucion->venta_id, 5, '0', STR_PAD_LEFT) }}">
                        <svg class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        <span>Ver Venta #{{ str_pad($devolucion->venta_id, 5, '0', STR_PAD_LEFT) }}</span>
                    </a>
                </div>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 truncate">Cliente: <span class="font-bold text-slate-900 dark:text-slate-200">{{ $devolucion->venta->cliente->nombre ?? 'Público General' }}</span></p>
            </div>
            <a href="{{ route('ventas.show', $devolucion->venta_id) }}" 
               class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 shadow-2xs transition flex items-center justify-center shrink-0" 
               title="Ver Detalle de Venta #{{ str_pad($devolucion->venta_id, 5, '0', STR_PAD_LEFT) }}">
                <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            </a>
        </div>

        <!-- Tarjeta 4: Motivo Registrado -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div class="min-w-0">
                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">Motivo Registrado</p>
                <p class="text-sm font-bold text-slate-900 dark:text-white mt-0.5 capitalize truncate" title="{{ str_replace('_', ' ', $devolucion->motivo) }}">
                    {{ str_replace('_', ' ', $devolucion->motivo) }}
                </p>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 truncate">
                    @if($devolucion->banco)
                        Banco: <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $devolucion->banco }}</span>
                    @else
                        Reintegro procesado
                    @endif
                </p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 flex items-center justify-center shrink-0 shadow-2xs">
                <svg class="w-5 h-5 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
        </div>
    </div>

    <!-- Contenedor Tarjeta Blanca Limpia: Observaciones y Notas de la Devolución -->
    @if($devolucion->observaciones)
    <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs space-y-2">
        <div class="flex items-center space-x-2 pb-2.5 border-b border-slate-200 dark:border-slate-800">
            <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
            <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                Observaciones y Notas de la Devolución
            </h3>
        </div>
        <p class="text-xs text-slate-700 dark:text-slate-300 leading-relaxed font-medium">
            {{ $devolucion->observaciones }}
        </p>
    </div>
    @endif

    <!-- Returned Products Table -->
    <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Medicamentos Devueltos</h3>
            <span class="text-xs font-bold text-slate-500 dark:text-slate-400">{{ $devolucion->detalles->count() }} ítems</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-slate-50/75 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800 text-[11px] font-semibold uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">Producto</th>
                        <th class="px-5 py-3.5">Lote / Vencimiento</th>
                        <th class="px-5 py-3.5 text-center">Cant. Devuelta</th>
                        <th class="px-5 py-3.5 text-right">Precio Unit.</th>
                        <th class="px-5 py-3.5 text-right">Subtotal</th>
                        <th class="px-5 py-3.5 text-center">Estado / Destino</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-700 dark:text-slate-300">
                    @foreach($devolucion->detalles as $det)
                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition">
                        <td class="px-5 py-3.5">
                            <span class="font-bold text-slate-900 dark:text-white block">{{ $det->producto->nombre }}</span>
                            <span class="text-[11px] text-slate-400">{{ $det->producto->codigo_barras ?? $det->producto->codigo_interno ?? '' }}</span>
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="font-mono font-bold text-slate-800 dark:text-slate-200 block">{{ $det->lote->numero_lote ?? 'N/A' }}</span>
                            <div class="text-[11px] text-slate-500">{{ $det->lote->fecha_vencimiento ? $det->lote->fecha_vencimiento->format('d/m/Y') : 'N/A' }}</div>
                        </td>
                        <td class="px-5 py-3.5 text-center font-bold text-slate-900 dark:text-white">{{ $det->cantidad }}</td>
                        <td class="px-5 py-3.5 text-right text-slate-900 dark:text-slate-200">{{ formato_moneda($det->precio_unitario) }}</td>
                        <td class="px-5 py-3.5 text-right font-black text-rose-950 dark:text-rose-300">{{ formato_moneda($det->subtotal) }}</td>
                        <td class="px-5 py-3.5 text-center">
                            @if($det->reingresa_a_stock)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-950 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                Reingresó a Stock
                            </span>
                            @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-950 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                Descarte / Merma ({{ $det->estado_producto }})
                            </span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
