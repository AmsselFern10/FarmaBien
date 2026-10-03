@extends('layouts.app')

@section('title', "Orden de Compra {$orden->numero_orden} - FarmaBien")

@section('content')
<div :class="posFullscreen ? 'w-full max-w-full' : 'max-w-7xl mx-auto'" class="space-y-4 transition-all duration-200">
    
    <!-- Breadcrumbs -->
    <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('compras.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Compras</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('ordenes-compras.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Órdenes de Compra</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-800 dark:text-slate-200 font-semibold">{{ $orden->numero_orden }}</span>
    </nav>

    <!-- Header & Action Toolbar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-3">
                <h1 class="text-xl font-bold text-slate-900 dark:text-white">
                    Orden de Compra {{ $orden->numero_orden }}
                </h1>
                
                <!-- Status Badge -->
                @if($orden->estado === 'recibida_total')
                    <span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-emerald-50 dark:bg-emerald-950/60 text-emerald-950 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                        ✓ Recibida Total
                    </span>
                @elseif($orden->estado === 'recibida_parcial')
                    <span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-amber-50 dark:bg-amber-950/60 text-amber-900 dark:text-amber-300 border border-amber-200 dark:border-amber-700">
                        ⚠ Recibida Parcialmente
                    </span>
                @elseif($orden->estado === 'cancelada')
                    <span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-rose-50 dark:bg-rose-950/60 text-rose-950 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                        Cancelada
                    </span>
                @else
                    <span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-blue-50 dark:bg-blue-950/60 text-blue-950 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                        {{ ucfirst(str_replace('_', ' ', $orden->estado)) }}
                    </span>
                @endif
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Emitida el {{ $orden->fecha_emision->format('d/m/Y') }} por <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $orden->usuario->name ?? 'Sistema' }}</span> • Condición: <span class="font-semibold text-slate-700 dark:text-slate-300">{{ ucfirst($orden->condicion_pago) }} {{ $orden->condicion_pago === 'credito' ? "({$orden->dias_credito} días)" : '' }}</span>
            </p>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-wrap items-center gap-2">
            <!-- Navigation Button First -->
            <a href="{{ route('ordenes-compras.index') }}" 
               class="inline-flex items-center space-x-1.5 px-3.5 py-2 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 shadow-xs transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Órdenes de Compra</span>
            </a>

            <!-- Modo Full -->
            <button type="button" 
                    @click="$dispatch('toggle-pos-fullscreen')"
                    title="Modo Pantalla Completa / Ocultar Barras"
                    class="inline-flex items-center space-x-1.5 px-3 py-2 rounded-xl bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold shadow-2xs transition cursor-pointer">
                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                <span x-text="posFullscreen ? 'Salir Full' : 'Modo Full'">Modo Full</span>
            </button>

            <!-- WhatsApp Export -->
            <a href="{{ $whatsappUrl }}" target="_blank"
               class="inline-flex items-center space-x-1.5 px-3.5 py-2 rounded-xl text-xs font-bold text-emerald-950 dark:text-emerald-300 bg-emerald-50/80 hover:bg-emerald-100 dark:bg-emerald-950/40 dark:hover:bg-emerald-900/60 border border-emerald-200 dark:border-emerald-800 shadow-2xs transition">
                <svg class="w-4 h-4 text-emerald-700 dark:text-emerald-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.771-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.275.072.376-.043c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.043.072.043.419-.101.824z"/></svg>
                <span>WhatsApp</span>
            </a>

            <!-- Print -->
            <a href="{{ route('ordenes-compras.imprimir', $orden) }}" target="_blank"
               class="inline-flex items-center space-x-1.5 px-3.5 py-2 rounded-xl text-xs font-bold text-indigo-950 dark:text-indigo-300 bg-indigo-50/80 hover:bg-indigo-100 dark:bg-indigo-950/40 dark:hover:bg-indigo-900/60 border border-indigo-200 dark:border-indigo-800 shadow-2xs transition">
                <svg class="w-4 h-4 text-indigo-700 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Imprimir</span>
            </a>

            <!-- Cancelar Orden -->
            @if($orden->estado === 'enviada')
            <form action="{{ route('ordenes-compras.cancelar', $orden) }}" method="POST" onsubmit="return confirm('¿Está seguro de cancelar esta orden de compra?')" class="inline-flex m-0 p-0">
                @csrf
                <button type="submit" 
                        class="inline-flex items-center space-x-1.5 px-3.5 py-2 rounded-xl text-xs font-bold text-rose-950 dark:text-rose-300 bg-rose-50/80 hover:bg-rose-100 dark:bg-rose-950/40 dark:hover:bg-rose-900/60 border border-rose-200 dark:border-rose-800 shadow-2xs transition cursor-pointer">
                    <svg class="w-4 h-4 text-rose-700 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    <span>Cancelar Orden</span>
                </button>
            </form>
            @endif

            <!-- Recepcionar (primera vez o complemento parcial) -->
            @if(in_array($orden->estado, ['enviada', 'recibida_parcial']))
            <a href="{{ route('ordenes-compras.recibir', $orden) }}" 
               class="inline-flex items-center space-x-1.5 px-4 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 shadow-sm transition cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>{{ $orden->estado === 'recibida_parcial' ? 'Recepcionar Resto Pendiente' : 'Recepcionar y Crear Compra' }}</span>
            </a>
            @endif
        </div>
    </div>

    <!-- Alert / Notice si la orden fue cerrada con faltante -->
    @if($orden->cerrada_con_faltante)
    <div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-950/50 border border-amber-300 dark:border-amber-800 text-amber-950 dark:text-amber-200 text-xs space-y-1">
        <div class="font-bold flex items-center space-x-2">
            <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <span>ORDEN CERRADA CON FALTANTE ({{ $orden->faltante_unidades }} UNIDADES)</span>
        </div>
        <p class="text-[11px] text-amber-900 dark:text-amber-300">
            <strong>Cerrada por:</strong> {{ $orden->cerradaPor->name ?? 'Sistema' }} el {{ $orden->fecha_cierre ? $orden->fecha_cierre->format('d/m/Y H:i') : '-' }} • 
            <strong>Motivo:</strong> {{ $orden->motivo_faltante ?: 'Sin motivo registrado' }}
        </p>
    </div>
    @endif

    <!-- Information Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <!-- Proveedor -->
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-300 dark:border-slate-800 shadow-xs space-y-2">
            <div class="flex items-center space-x-2 pb-2 border-b border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-800 dark:text-slate-200">
                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                <span>Datos del Proveedor</span>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-900 dark:text-white">{{ $orden->proveedor->nombre ?? $orden->proveedor->nombre_empresa ?? 'N/A' }}</p>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">RUC / NIT: {{ $orden->proveedor->ruc ?? 'Sin RUC' }}</p>
                @if($orden->proveedor && $orden->proveedor->contacto)
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Contacto: {{ $orden->proveedor->contacto }}</p>
                @endif
                @if($orden->proveedor && $orden->proveedor->telefono)
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Tel: {{ $orden->proveedor->telefono }}</p>
                @endif
            </div>
        </div>

        <!-- Detalle de la Orden -->
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-300 dark:border-slate-800 shadow-xs space-y-2">
            <div class="flex items-center space-x-2 pb-2 border-b border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-800 dark:text-slate-200">
                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Detalle de la Orden</span>
            </div>
            <div class="text-[11px] space-y-1 text-slate-600 dark:text-slate-300">
                <div class="flex justify-between">
                    <span class="text-slate-400">N° de Orden:</span>
                    <span class="font-bold text-slate-900 dark:text-white">{{ $orden->numero_orden }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Fecha Emisión:</span>
                    <span class="font-bold text-slate-900 dark:text-white">{{ $orden->fecha_emision->format('d/m/Y') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Entrega Estimada:</span>
                    <span class="font-semibold text-slate-900 dark:text-white">{{ $orden->fecha_esperada_entrega ? $orden->fecha_esperada_entrega->format('d/m/Y') : 'Inmediata' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Condición de Pago:</span>
                    <span class="font-semibold text-slate-900 dark:text-white">{{ ucfirst($orden->condicion_pago) }} {{ $orden->condicion_pago === 'credito' ? "({$orden->dias_credito} días)" : '' }}</span>
                </div>
            </div>
        </div>

        <!-- Totales Financieros -->
        <div class="bg-white dark:bg-slate-900 text-slate-900 dark:text-white rounded-xl p-4 border border-slate-300 dark:border-slate-800 shadow-xs space-y-2 flex flex-col justify-between">
            <div class="flex items-center space-x-2 pb-2 border-b border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-800 dark:text-slate-200">
                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Resumen Estimado</span>
            </div>
            <div class="space-y-1 text-xs">
                <div class="flex justify-between text-slate-600 dark:text-slate-300">
                    <span>Líneas Solicitadas:</span>
                    <span class="font-bold text-slate-900 dark:text-white">{{ $orden->detalles->count() }} producto(s)</span>
                </div>
                <div class="flex justify-between text-slate-600 dark:text-slate-300">
                    <span>Total Unidades:</span>
                    <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ $orden->detalles->sum('cantidad_solicitada') }} u.</span>
                </div>
                <div class="pt-2 border-t border-slate-200 dark:border-slate-800 flex justify-between items-baseline">
                    <span class="font-semibold text-slate-600 dark:text-slate-300">Monto Total Est.:</span>
                    <span class="text-xl font-extrabold text-emerald-600 dark:text-emerald-400">{{ formato_moneda($orden->total) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Observaciones / Notas -->
    @if($orden->observaciones)
    <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-300 dark:border-slate-800 shadow-xs space-y-1.5">
        <div class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
            Observaciones e Instrucciones del Pedido
        </div>
        <p class="text-xs text-slate-800 dark:text-slate-200 leading-relaxed font-medium">
            {{ $orden->observaciones }}
        </p>
    </div>
    @endif

    <!-- Items Table -->
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-300 dark:border-slate-800 shadow-xs overflow-hidden space-y-0">
        <div class="p-3.5 bg-slate-50 dark:bg-slate-800/60 border-b border-slate-300 dark:border-slate-800 flex items-center justify-between">
            <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                Medicamentos Solicitados en la Orden
            </h3>
            <span class="px-2 py-0.5 text-[11px] font-bold rounded bg-emerald-50 dark:bg-emerald-950/60 text-emerald-950 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                {{ $orden->detalles->count() }} partidas
            </span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-100 dark:bg-slate-800/80 text-[10px] font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">
                        <th class="px-4 py-3">Producto</th>
                        <th class="px-4 py-3">Laboratorio / Principio</th>
                        <th class="px-4 py-3 text-center">Solicitado</th>
                        <th class="px-4 py-3 text-center">Recibido</th>
                        <th class="px-4 py-3 text-center">Pendiente</th>
                        <th class="px-4 py-3 text-right">Precio Est. (C$)</th>
                        <th class="px-4 py-3 text-right">Subtotal Est.</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                    @foreach($orden->detalles as $det)
                    @php $pendiente = max(0, $det->cantidad_solicitada - $det->cantidad_recibida); @endphp
                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition {{ $pendiente === 0 && $det->cantidad_recibida > 0 ? 'opacity-60' : '' }}">
                        <td class="px-4 py-3 font-bold text-slate-900 dark:text-white">
                            {{ $det->producto->nombre }}
                            @if($det->producto->codigo_barra)
                                <span class="block text-[10px] font-mono text-slate-400 font-normal">Cód: {{ $det->producto->codigo_barra }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-slate-500">
                            <div>{{ $det->producto->laboratorio->nombre ?? 'Sin Laboratorio' }}</div>
                            @if($det->producto->principio_activo)
                                <div class="text-[10px] text-slate-400 italic">{{ $det->producto->principio_activo }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="font-mono font-bold text-sm text-slate-900 dark:text-white bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded">
                                {{ $det->cantidad_solicitada }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="font-mono font-bold text-sm px-2 py-0.5 rounded
                                {{ $det->cantidad_recibida >= $det->cantidad_solicitada
                                    ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300'
                                    : ($det->cantidad_recibida > 0
                                        ? 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300'
                                        : 'bg-slate-100 dark:bg-slate-800 text-slate-500') }}">
                                {{ $det->cantidad_recibida }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($pendiente > 0)
                                <span class="font-mono font-bold text-sm bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 px-2 py-0.5 rounded">
                                    {{ $pendiente }}
                                </span>
                            @else
                                <span class="text-emerald-600 dark:text-emerald-400 font-bold text-sm">✓</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right font-mono text-slate-700 dark:text-slate-300">
                            {{ formato_moneda($det->precio_unitario_estimado) }}
                        </td>
                        <td class="px-4 py-3 text-right font-mono font-bold text-emerald-950 dark:text-emerald-400">
                            {{ formato_moneda($det->subtotal) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recepciones Registradas Sobre Esta Orden (Compras Vinculadas) -->
    @if($orden->compras->isNotEmpty())
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-300 dark:border-slate-800 shadow-xs overflow-hidden space-y-0">
        <div class="p-3.5 bg-indigo-50/70 dark:bg-indigo-950/40 border-b border-indigo-200 dark:border-indigo-900/50 flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <h3 class="text-xs font-bold text-indigo-950 dark:text-indigo-200 uppercase tracking-wider">
                    Recepciones Registradas Sobre Esta Orden (Facturas / Compras)
                </h3>
            </div>
            <span class="text-xs font-bold text-indigo-900 dark:text-indigo-300">
                {{ $orden->compras->count() }} recepción(es) registrada(s)
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-100 dark:bg-slate-800/80 text-[10px] font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">
                        <th class="py-2.5 px-3">N° Compra</th>
                        <th class="py-2.5 px-3">Comprobante Fiscal</th>
                        <th class="py-2.5 px-3">Fecha Recepción</th>
                        <th class="py-2.5 px-3">Condición Pago</th>
                        <th class="py-2.5 px-3 text-center">Líneas</th>
                        <th class="py-2.5 px-3 text-center">Unidades al Kardex</th>
                        <th class="py-2.5 px-3 text-right">Total Factura</th>
                        <th class="py-2.5 px-3">Registrado Por</th>
                        <th class="py-2.5 px-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800 font-medium">
                    @foreach($orden->compras as $compra)
                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition">
                        <td class="py-3 px-3 font-mono font-bold text-slate-900 dark:text-white">
                            #{{ str_pad($compra->id, 5, '0', STR_PAD_LEFT) }}
                        </td>
                        <td class="py-3 px-3 font-semibold text-slate-800 dark:text-slate-200">
                            {{ $compra->numero_comprobante ?: 'Sin N° fiscal' }}
                        </td>
                        <td class="py-3 px-3 text-slate-600 dark:text-slate-300">
                            {{ $compra->fecha ? $compra->fecha->format('d/m/Y') : ($compra->created_at ? $compra->created_at->format('d/m/Y') : '-') }}
                        </td>
                        <td class="py-3 px-3 capitalize">
                            <span class="font-semibold">{{ $compra->condicion_pago }}</span>
                            @if($compra->condicion_pago === 'credito')
                                <span class="text-[10px] text-slate-400">({{ $compra->dias_credito }}d)</span>
                            @endif
                        </td>
                        <td class="py-3 px-3 text-center font-bold text-slate-800 dark:text-slate-200">
                            {{ $compra->detalles->count() }}
                        </td>
                        <td class="py-3 px-3 text-center font-bold text-emerald-600 dark:text-emerald-400">
                            {{ $compra->detalles->sum('cantidad_unidades_base') }} u.
                        </td>
                        <td class="py-3 px-3 text-right font-bold text-slate-900 dark:text-white">
                            {{ formato_moneda($compra->total) }}
                        </td>
                        <td class="py-3 px-3 text-slate-600 dark:text-slate-300">
                            {{ $compra->usuario->name ?? 'Sistema' }}
                        </td>
                        <td class="py-3 px-3 text-right">
                            <a href="{{ route('compras.show', $compra) }}" 
                               class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 transition">
                                Ver Compra &rarr;
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>
@endsection
