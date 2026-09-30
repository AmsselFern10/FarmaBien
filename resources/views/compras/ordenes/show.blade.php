@extends('layouts.app')

@section('title', "Orden de Compra {$orden->numero_orden} - FarmaBien")

@section('content')
<div class="max-w-5xl mx-auto space-y-5">
    
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

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $orden->estado === 'recibida_total' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' : ($orden->estado === 'cancelada' ? 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300' : 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300') }}">
                    {{ ucfirst(str_replace('_', ' ', $orden->estado)) }}
                </span>
                <span class="text-xs text-slate-500 font-medium">Condición: {{ ucfirst($orden->condicion_pago) }} {{ $orden->condicion_pago === 'credito' ? "({$orden->dias_credito} días)" : '' }}</span>
            </div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white mt-1">{{ $orden->numero_orden }}</h1>
            <p class="text-xs text-slate-500">Emitida el {{ $orden->fecha_emision->format('d/m/Y') }} por {{ $orden->usuario->name ?? 'Sistema' }}</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <!-- WhatsApp Export -->
            <a href="{{ $whatsappUrl }}" target="_blank"
               class="inline-flex items-center px-3.5 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-xs transition">
                <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.771-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.275.072.376-.043c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.043.072.043.419-.101.824z"/></svg>
                Enviar por WhatsApp
            </a>

            <!-- Print -->
            <a href="{{ route('ordenes-compras.imprimir', $orden) }}" target="_blank"
               class="inline-flex items-center px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 shadow-xs transition">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Imprimir Pedido
            </a>

            <!-- 1-Click Receive Goods -->
            @if($orden->estado === 'enviada')
            <a href="{{ route('ordenes-compras.recibir', $orden) }}" 
               class="inline-flex items-center px-4 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm transition">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Recibir Mercancía
            </a>
            @endif

            <a href="{{ route('ordenes-compras.index') }}" 
               class="inline-flex items-center px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                Volver
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-4 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs">
            <span class="text-xs font-medium text-slate-500">Proveedor</span>
            <p class="text-base font-bold text-slate-900 dark:text-white mt-1">{{ $orden->proveedor->nombre ?? $orden->proveedor->nombre_empresa }}</p>
            <p class="text-[11px] text-slate-400">Contacto: {{ $orden->proveedor->contacto ?? 'N/A' }} {{ $orden->proveedor->telefono ? "({$orden->proveedor->telefono})" : '' }}</p>
        </div>

        <div class="p-4 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs">
            <span class="text-xs font-medium text-slate-500">Entrega Estimada</span>
            <p class="text-base font-bold text-slate-900 dark:text-white mt-1">{{ $orden->fecha_esperada_entrega ? $orden->fecha_esperada_entrega->format('d/m/Y') : 'Inmediata' }}</p>
            <p class="text-[11px] text-slate-400">Condición: {{ ucfirst($orden->condicion_pago) }}</p>
        </div>

        <div class="p-4 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs">
            <span class="text-xs font-medium text-slate-500">Monto Total Estimado</span>
            <p class="text-xl font-black text-emerald-600 dark:text-emerald-400 mt-1">{{ formato_moneda($orden->total) }}</p>
            <p class="text-[11px] text-slate-400">{{ $orden->detalles->count() }} producto(s) en la orden</p>
        </div>
    </div>

    @if($orden->observaciones)
    <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-xs text-slate-700 dark:text-slate-300">
        <span class="font-bold">Observaciones / Instrucciones:</span> {{ $orden->observaciones }}
    </div>
    @endif

    <!-- Items Table -->
    <div class="rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Medicamentos Solicitados</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800 text-[11px] font-semibold uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3">Producto</th>
                        <th class="px-4 py-3">Laboratorio</th>
                        <th class="px-4 py-3 text-center">Cant. Solicitada</th>
                        <th class="px-4 py-3 text-right">Precio Est.</th>
                        <th class="px-4 py-3 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                    @foreach($orden->detalles as $det)
                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition">
                        <td class="px-4 py-3 font-bold text-slate-900 dark:text-white">{{ $det->producto->nombre }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $det->producto->laboratorio->nombre ?? 'N/A' }}</td>
                        <td class="px-4 py-3 text-center font-bold text-base text-slate-900 dark:text-white">{{ $det->cantidad_solicitada }}</td>
                        <td class="px-4 py-3 text-right">{{ formato_moneda($det->precio_unitario_estimado) }}</td>
                        <td class="px-4 py-3 text-right font-black text-emerald-600 dark:text-emerald-400">{{ formato_moneda($det->subtotal) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Actions Footer -->
    @if($orden->estado === 'enviada')
    <div class="flex flex-col sm:flex-row items-center justify-between gap-3 p-4 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
        <div>
            <h4 class="text-xs font-bold text-slate-900 dark:text-white">¿La mercancía ya llegó a la farmacia?</h4>
            <p class="text-[11px] text-slate-500">Haga clic en "Recibir Mercancía" para convertir este pedido en un ingreso oficial al inventario con asignación de lotes.</p>
        </div>
        <form action="{{ route('ordenes-compras.cancelar', $orden) }}" method="POST" onsubmit="return confirm('¿Está seguro de cancelar esta orden de compra?')">
            @csrf
            <button type="submit" class="text-xs font-semibold text-rose-600 dark:text-rose-400 hover:underline">
                Cancelar Orden
            </button>
        </form>
    </div>
    @endif
</div>
@endsection
