@extends('layouts.app')

@section('title', 'Detalle de Compra #' . str_pad($compra->id, 5, '0', STR_PAD_LEFT) . ' - FarmaBien')

@section('content')
<div x-data="{
    modalAnular: false,
    motivoAnulacion: ''
}" class="space-y-5">
    
    <!-- Breadcrumbs -->
    <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('compras.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Compras</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-800 dark:text-slate-200 font-semibold">Comprobante #{{ str_pad($compra->id, 5, '0', STR_PAD_LEFT) }}</span>
    </nav>

    <!-- Header & Action Toolbar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-3 flex-wrap gap-y-1">
                <h1 class="text-xl font-bold text-slate-900 dark:text-white">
                    Compra #{{ str_pad($compra->id, 5, '0', STR_PAD_LEFT) }}
                </h1>
                
                <!-- Status Badges -->
                @if($compra->estado === 'recibida')
                    @if($compra->fueModificada())
                        <span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-amber-100 dark:bg-amber-950/60 text-amber-900 dark:text-amber-300 border border-amber-300 dark:border-amber-800">
                            Modificada (Reemplazada)
                        </span>
                    @elseif($compra->condicion_pago === 'credito')
                        @if($compra->saldo_pendiente <= 0)
                            <span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-900 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800">
                                Recibida / Crédito Pagado
                            </span>
                        @elseif($compra->saldo_pendiente < $compra->total)
                            <span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-amber-100 dark:bg-amber-950/60 text-amber-900 dark:text-amber-300 border border-amber-300 dark:border-amber-800">
                                Recibida / Crédito Parcial
                            </span>
                        @else
                            <span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-blue-100 dark:bg-blue-950/60 text-blue-900 dark:text-blue-300 border border-blue-300 dark:border-blue-800">
                                Recibida / Crédito Pendiente
                            </span>
                        @endif
                    @else
                        <span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-900 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800">
                            Recibida en Kardex (Contado)
                        </span>
                    @endif

                    @if($compra->esDevuelta())
                        <span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-rose-100 dark:bg-rose-950/60 text-rose-900 dark:text-rose-300 border border-rose-300 dark:border-rose-800">
                            Devuelta
                        </span>
                    @elseif($compra->esDevueltaParcial())
                        <span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-amber-100 dark:bg-amber-950/60 text-amber-900 dark:text-amber-300 border border-amber-300 dark:border-amber-800">
                            Devolución Parcial
                        </span>
                    @endif
                @elseif($compra->estado === 'anulada')
                    <span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-rose-100 dark:bg-rose-950/60 text-rose-900 dark:text-rose-300 border border-rose-300 dark:border-rose-800">
                        Anulada
                    </span>
                @endif
            </div>
            <p class="text-xs text-slate-700 dark:text-slate-400 mt-0.5">
                Comprobante: <span class="font-semibold text-slate-900 dark:text-slate-300">{{ $compra->numero_comprobante ?: 'Sin número fiscal' }}</span> • Registrado por: <span class="font-semibold text-slate-900 dark:text-slate-300">{{ $compra->usuario->name ?? 'Sistema' }}</span> el {{ $compra->fecha ? $compra->fecha->format('d/m/Y') : ($compra->created_at ? $compra->created_at->format('d/m/Y') : '-') }}
                @if($compra->condicion_pago === 'credito')
                    • <span class="font-bold text-amber-900 dark:text-amber-300">Crédito a {{ $compra->dias_credito }} días</span>
                @endif
            </p>
        </div>

        <!-- Action Buttons (Exact Order: ← Historial, Modo Full, Procesar Devolución, Modificar, Anular Compra, Imprimir) -->
        <div class="flex flex-wrap items-center gap-2">
            <!-- 1. Historial de Compras -->
            <a href="{{ route('compras.index') }}" 
               class="h-10 px-4 rounded-full text-sm font-medium inline-flex items-center gap-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 transition">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Compras</span>
            </a>

            <!-- 2. Modo Full -->
            <button type="button" 
                    @click="$dispatch('toggle-pos-fullscreen')"
                    title="Modo Pantalla Completa / Ocultar Barras"
                    class="h-10 px-4 rounded-full text-sm font-medium inline-flex items-center gap-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 transition cursor-pointer">
                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                <span x-text="posFullscreen ? 'Salir Full' : 'Modo Full'">Modo Full</span>
            </button>

            <!-- 3. Procesar Devolución -->
            @can('registrar compras')
                @if($compra->estado !== 'anulada' && $compra->lotes->sum('stock_actual') > 0)
                <a href="{{ route('compras.devoluciones.create', ['compra_id' => $compra->id]) }}" 
                   class="h-10 px-4 rounded-full text-sm font-medium inline-flex items-center gap-2 bg-indigo-50 border border-indigo-200 text-indigo-900 hover:bg-indigo-100 dark:bg-indigo-500/10 dark:border-indigo-500/30 dark:text-indigo-300 transition">
                    <svg class="w-4 h-4 text-indigo-700 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H4m0 0l4-4m-4 4l4 4m6 4v1a3 3 0 003 3h3a3 3 0 003-3V7a3 3 0 00-3-3h-3a3 3 0 00-3 3v1"/></svg>
                    <span>Procesar Devolución</span>
                </a>
                @endif
            @endcan

            <!-- 4. Modificar -->
            @if($compra->puedeModificarse())
                @can('registrar compras')
                <a href="{{ route('compras.edit', $compra) }}" 
                   class="h-10 px-4 rounded-full text-sm font-medium inline-flex items-center gap-2 bg-amber-50 border border-amber-200 text-amber-900 hover:bg-amber-100 dark:bg-amber-500/10 dark:border-amber-500/30 dark:text-amber-300 transition">
                    <svg class="w-4 h-4 text-amber-700 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>Modificar</span>
                </a>
                @endcan
            @endif

            <!-- 5. Anular Compra -->
            @if($compra->puedeAnularse())
                @can('anular compras')
                <button type="button" 
                        @click="modalAnular = true" 
                        class="h-10 px-4 rounded-full text-sm font-medium inline-flex items-center gap-2 bg-red-50 border border-red-200 text-red-900 hover:bg-red-100 dark:bg-red-500/10 dark:border-red-500/30 dark:text-red-300 transition cursor-pointer">
                    <svg class="w-4 h-4 text-rose-700 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    <span>Anular Compra</span>
                </button>
                @endcan
            @endif

            <!-- 6. Imprimir (Principal Verde Sólido) -->
            <a href="{{ route('compras.imprimir', $compra) }}" 
               target="_blank"
               class="h-10 px-4 rounded-full text-sm font-medium inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Imprimir</span>
            </a>
        </div>
    </div>

    <!-- Traceability / Modification Banner -->
    @if($compra->esModificacion())
        <div class="p-4 rounded-xl bg-purple-50 dark:bg-purple-950/50 border border-purple-300 dark:border-purple-800 text-purple-900 dark:text-purple-200 text-xs flex items-center justify-between">
            <div class="flex items-center space-x-2.5">
                <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                <span>Esta compra es una <strong>versión modificada</strong> que reemplaza a la compra original <strong>#{{ str_pad($compra->compra_original_id, 5, '0', STR_PAD_LEFT) }}</strong>.</span>
            </div>
            <a href="{{ route('compras.show', $compra->compraOriginal) }}" class="font-bold text-purple-700 dark:text-purple-300 underline hover:no-underline">
                Ver Compra Original &rarr;
            </a>
        </div>
    @endif

    @if($compra->fueModificada())
        <div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-950/50 border border-amber-300 dark:border-amber-800 text-amber-900 dark:text-amber-200 text-xs flex items-center justify-between">
            <div class="flex items-center space-x-2.5">
                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                <span>Esta compra fue <strong>modificada</strong> y ha sido sustituida por la nueva versión <strong>#{{ str_pad($compra->reemplazada_por, 5, '0', STR_PAD_LEFT) }}</strong>.</span>
            </div>
            <a href="{{ route('compras.show', $compra->reemplazadaPor) }}" class="font-bold text-amber-700 dark:text-amber-300 underline hover:no-underline">
                Ver Nueva Versión Activa &rarr;
            </a>
        </div>
    @endif

    @if($compra->estado === 'anulada')
        <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/50 border border-rose-300 dark:border-rose-800 text-rose-900 dark:text-rose-200 text-xs space-y-1">
            <div class="font-bold flex items-center space-x-2">
                <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>COMPRA ANULADA</span>
            </div>
            <p class="text-[11px] text-rose-800 dark:text-rose-300">
                <strong>Motivo:</strong> {{ $compra->motivo_anulacion ?: 'No especificado' }} • 
                <strong>Anulada por:</strong> {{ $compra->anuladoPor->name ?? 'Sistema' }} el {{ $compra->fecha_anulacion ? $compra->fecha_anulacion->format('d/m/Y H:i') : '-' }}
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
                <p class="text-xs font-bold text-slate-900 dark:text-white">{{ $compra->proveedor->nombre ?? 'N/A' }}</p>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">RUC / NIT: {{ $compra->proveedor->ruc ?? 'Sin RUC' }}</p>
                @if($compra->proveedor && $compra->proveedor->telefono)
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Tel: {{ $compra->proveedor->telefono }}</p>
                @endif
                @if($compra->proveedor && $compra->proveedor->direccion)
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Dir: {{ $compra->proveedor->direccion }}</p>
                @endif
            </div>
        </div>

        <!-- Comprobante y Registro -->
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-300 dark:border-slate-800 shadow-xs space-y-2">
            <div class="flex items-center space-x-2 pb-2 border-b border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-800 dark:text-slate-200">
                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Detalle Fiscal & Recepción</span>
            </div>
            <div class="text-[11px] space-y-1 text-slate-600 dark:text-slate-300">
                <div class="flex justify-between">
                    <span class="text-slate-400">N° Comprobante:</span>
                    <span class="font-bold text-slate-900 dark:text-white">{{ $compra->numero_comprobante ?: 'Sin número' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Fecha Documento:</span>
                    <span class="font-bold text-slate-900 dark:text-white">{{ $compra->fecha ? $compra->fecha->format('d/m/Y') : '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Registrado Por:</span>
                    <span class="font-semibold">{{ $compra->usuario->name ?? 'Sistema' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Fecha Creación:</span>
                    <span>{{ $compra->created_at ? $compra->created_at->format('d/m/Y H:i') : '-' }}</span>
                </div>
            </div>
        </div>

        <!-- Totales Financieros -->
        <div class="bg-white dark:bg-slate-900 text-slate-900 dark:text-white rounded-xl p-4 border border-slate-300 dark:border-slate-800 shadow-xs space-y-2 flex flex-col justify-between">
            <div class="flex items-center space-x-2 pb-2 border-b border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-800 dark:text-slate-200">
                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Resumen de Liquidación</span>
            </div>
            <div class="space-y-1.5 text-xs">
                <div class="flex justify-between text-slate-600 dark:text-slate-300">
                    <span>Condición de Pago:</span>
                    <span class="font-bold {{ $compra->condicion_pago === 'credito' ? 'text-amber-900 dark:text-amber-300' : 'text-slate-900 dark:text-white' }} uppercase">
                        {{ $compra->condicion_pago }} {{ $compra->condicion_pago === 'credito' ? '(' . $compra->dias_credito . 'd)' : '' }}
                    </span>
                </div>
                @if($compra->condicion_pago === 'credito')
                <div class="flex justify-between text-slate-600 dark:text-slate-300">
                    <span>Saldo Pendiente:</span>
                    <span class="font-extrabold {{ $compra->saldo_pendiente > 0 ? 'text-rose-900 dark:text-rose-400' : 'text-emerald-900 dark:text-emerald-400' }}">
                        {{ formato_moneda($compra->saldo_pendiente) }}
                    </span>
                </div>
                @if($compra->saldo_pendiente < $compra->total)
                <div class="flex justify-between text-slate-600 dark:text-slate-300">
                    <span>Total Abonado:</span>
                    <span class="font-bold text-emerald-900 dark:text-emerald-400">
                        {{ formato_moneda($compra->total - $compra->saldo_pendiente) }}
                    </span>
                </div>
                @endif
                @endif
                <div class="flex justify-between text-slate-600 dark:text-slate-300">
                    <span>Unidades al Kardex:</span>
                    <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ $compra->detalles->sum('cantidad_unidades_base') }} u.</span>
                </div>
                <div class="pt-2 border-t border-slate-200 dark:border-slate-800 flex justify-between items-baseline">
                    <span class="font-semibold text-slate-600 dark:text-slate-300">Monto Total:</span>
                    <span class="text-xl font-extrabold text-emerald-600 dark:text-emerald-400">{{ formato_moneda($compra->total) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Items & Lots Breakdown Table -->
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-300 dark:border-slate-800 shadow-xs overflow-hidden space-y-0">
        <div class="p-3.5 bg-slate-50 dark:bg-slate-800/60 border-b border-slate-300 dark:border-slate-800 flex items-center justify-between">
            <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                Desglose de Medicamentos y Lotes Recibidos
            </h3>
            <div class="flex items-center space-x-2">
                @if($compra->devoluciones->isNotEmpty())
                    <span class="px-2 py-0.5 text-[11px] font-bold rounded bg-rose-100 dark:bg-rose-950/60 text-rose-800 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                        {{ $compra->devoluciones->count() }} devolución(es) vinculada(s)
                    </span>
                @endif
                <span class="px-2 py-0.5 text-[11px] font-bold rounded bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300">
                    {{ $compra->detalles->count() }} líneas registradas
                </span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-100 dark:bg-slate-800/80 text-[10px] font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">
                        <th class="py-2.5 px-3">#</th>
                        <th class="py-2.5 px-3">Medicamento / Fármaco</th>
                        <th class="py-2.5 px-3">Presentación</th>
                        <th class="py-2.5 px-3 text-center">Cant. Comprada</th>
                        <th class="py-2.5 px-3 text-center">Factor Conversión</th>
                        <th class="py-2.5 px-3 text-center">Total Unidades Base</th>
                        @if($compra->devoluciones->isNotEmpty())
                            <th class="py-2.5 px-3 text-center text-rose-700 dark:text-rose-300">Devuelto</th>
                        @endif
                        <th class="py-2.5 px-3">Lote & Vencimiento</th>
                        <th class="py-2.5 px-3 text-right">P. Compra (C$)</th>
                        <th class="py-2.5 px-3 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800 font-medium">
                    @foreach($compra->detalles as $idx => $detalle)
                    @php
                        $cantDevuelta = $compra->devoluciones->flatMap->detalles
                            ->where('producto_id', $detalle->producto_id)
                            ->when($detalle->lote_id, fn($q) => $q->where('lote_id', $detalle->lote_id))
                            ->sum('cantidad');
                    @endphp
                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition">
                        <!-- # -->
                        <td class="py-3 px-3 text-slate-400 font-bold">{{ $idx + 1 }}</td>

                        <!-- Medicamento -->
                        <td class="py-3 px-3">
                            <a href="{{ route('productos.show', $detalle->producto) }}" class="font-bold text-slate-900 dark:text-white hover:text-emerald-600 dark:hover:text-emerald-400 transition">
                                {{ $detalle->producto->nombre ?? 'Producto eliminado' }}
                            </a>
                            <div class="text-[10px] text-slate-400 mt-0.5">
                                {{ $detalle->producto->principio_activo ?? '' }} • Lab: {{ $detalle->producto->laboratorio->nombre ?? 'Sin Lab' }}
                            </div>
                        </td>

                        <!-- Presentación -->
                        <td class="py-3 px-3 text-slate-700 dark:text-slate-300">
                            {{ $detalle->tipo_presentacion ?: 'Unidad Base' }}
                        </td>

                        <!-- Cantidad Comprada -->
                        <td class="py-3 px-3 text-center font-bold text-slate-900 dark:text-white">
                            {{ $detalle->cantidad_presentaciones ?: $detalle->cantidad_unidades_base }}
                        </td>

                        <!-- Factor -->
                        <td class="py-3 px-3 text-center text-slate-500 dark:text-slate-400">
                            x{{ $detalle->unidades_por_presentacion ?: 1 }}
                        </td>

                        <!-- Total Base -->
                        <td class="py-3 px-3 text-center font-bold text-emerald-600 dark:text-emerald-400">
                            {{ $detalle->cantidad_unidades_base }} u.
                        </td>

                        <!-- Devuelto -->
                        @if($compra->devoluciones->isNotEmpty())
                            <td class="py-3 px-3 text-center font-bold text-rose-600 dark:text-rose-400">
                                @if($cantDevuelta > 0)
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-900/50">
                                        -{{ $cantDevuelta }} u.
                                    </span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                        @endif

                        <!-- Lote y Vencimiento -->
                        <td class="py-3 px-3">
                            @if($detalle->lote)
                                <div class="font-mono font-bold text-slate-900 dark:text-white text-[11px]">
                                    {{ $detalle->lote->numero_lote }}
                                </div>
                                <div class="flex items-center space-x-1.5 mt-0.5">
                                    <span class="text-[10px] text-slate-400">
                                        Vence: {{ $detalle->lote->fecha_vencimiento ? $detalle->lote->fecha_vencimiento->format('d/m/Y') : '-' }}
                                    </span>
                                    @if($detalle->lote->estaVencido())
                                        <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-rose-100 text-rose-700">Vencido</span>
                                    @elseif($detalle->lote->proximoAVencer(30))
                                        <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-amber-100 text-amber-700">Próx. Vencer</span>
                                    @else
                                        <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-emerald-100 text-emerald-700">Vigente</span>
                                    @endif
                                </div>
                            @else
                                <span class="text-slate-400 italic text-[10px]">Sin lote asignado</span>
                            @endif
                        </td>

                        <!-- Precio Compra -->
                        <td class="py-3 px-3 text-right text-slate-700 dark:text-slate-300">
                            {{ formato_moneda($detalle->precio_unitario) }}
                        </td>

                        <!-- Subtotal -->
                        <td class="py-3 px-3 text-right font-bold text-slate-900 dark:text-white">
                            {{ formato_moneda($detalle->subtotal) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Devoluciones a Proveedor Procesadas Sobre Esta Compra -->
    @if($compra->devoluciones->isNotEmpty())
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-300 dark:border-slate-800 shadow-xs overflow-hidden space-y-0">
        <div class="p-3.5 bg-rose-50/70 dark:bg-rose-950/40 border-b border-rose-200 dark:border-rose-900/50 flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <svg class="w-4 h-4 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H4m0 0l4-4m-4 4l4 4m6 4v1a3 3 0 003 3h3a3 3 0 003-3V7a3 3 0 00-3-3h-3a3 3 0 00-3 3v1"/></svg>
                <h3 class="text-xs font-bold text-rose-950 dark:text-rose-200 uppercase tracking-wider">
                    Devoluciones a Proveedor Procesadas Sobre Esta Compra
                </h3>
            </div>
            <span class="text-xs font-bold text-rose-900 dark:text-rose-300">
                Total devuelto: {{ formato_moneda($compra->totalDevuelto()) }}
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-100 dark:bg-slate-800/80 text-[10px] font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">
                        <th class="py-2.5 px-3">N° Devolución</th>
                        <th class="py-2.5 px-3">Fecha</th>
                        <th class="py-2.5 px-3">Motivo</th>
                        <th class="py-2.5 px-3">Tipo</th>
                        <th class="py-2.5 px-3 text-center">Unidades Devueltas</th>
                        <th class="py-2.5 px-3 text-right">Monto Devuelto</th>
                        <th class="py-2.5 px-3">Usuario</th>
                        <th class="py-2.5 px-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800 font-medium">
                    @foreach($compra->devoluciones as $dev)
                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition">
                        <td class="py-3 px-3 font-mono font-bold text-slate-900 dark:text-white">
                            {{ $dev->numero_devolucion ?? ('DEV-' . str_pad($dev->id, 6, '0', STR_PAD_LEFT)) }}
                        </td>
                        <td class="py-3 px-3 text-slate-600 dark:text-slate-300">
                            {{ $dev->fecha ? $dev->fecha->format('d/m/Y') : ($dev->created_at ? $dev->created_at->format('d/m/Y') : '-') }}
                        </td>
                        <td class="py-3 px-3 text-slate-700 dark:text-slate-300 max-w-xs truncate" title="{{ $dev->motivo }}">
                            {{ $dev->motivo }}
                        </td>
                        <td class="py-3 px-3">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $dev->tipo === 'total' ? 'bg-rose-100 text-rose-900 dark:bg-rose-950 dark:text-rose-300' : 'bg-amber-100 text-amber-900 dark:bg-amber-950 dark:text-amber-300' }}">
                                {{ ucfirst($dev->tipo ?? 'parcial') }}
                            </span>
                        </td>
                        <td class="py-3 px-3 text-center font-bold text-rose-600 dark:text-rose-400">
                            -{{ $dev->detalles->sum('cantidad') }} u.
                        </td>
                        <td class="py-3 px-3 text-right font-bold text-slate-900 dark:text-white">
                            {{ formato_moneda($dev->total) }}
                        </td>
                        <td class="py-3 px-3 text-slate-600 dark:text-slate-300">
                            {{ $dev->usuario->name ?? 'Sistema' }}
                        </td>
                        <td class="py-3 px-3 text-right">
                            <a href="{{ route('compras.devoluciones.show', $dev) }}" 
                               class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 transition">
                                Ver Detalle &rarr;
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Modal Anular Compra -->
    <template x-teleport="body">
    <div x-show="modalAnular" 
         x-cloak
         class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-slate-950/75 backdrop-blur-sm transition-opacity overflow-y-auto"
         @keydown.escape.window="modalAnular = false"
         @click.self="modalAnular = false">
        
        <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full p-5 border border-slate-300 dark:border-slate-800 shadow-xl space-y-4 my-auto"
             @click.stop>
            
            <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 rounded-lg bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Anular Compra #{{ str_pad($compra->id, 5, '0', STR_PAD_LEFT) }}</h3>
                </div>
                <button @click="modalAnular = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer">✕</button>
            </div>

            <p class="text-xs text-slate-600 dark:text-slate-300">
                ¿Está completamente seguro de anular esta compra? El stock de todos los lotes asociados será revertido del Kardex.
            </p>

            <form action="{{ route('compras.anular', $compra) }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Motivo de anulación <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="motivo" 
                              x-model="motivoAnulacion"
                              rows="2" 
                              required 
                              minlength="5"
                              placeholder="Ej: Factura anulada por el proveedor o devolución total de mercadería..."
                              class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-rose-500 focus:border-rose-500"></textarea>
                </div>

                <div class="flex items-center justify-end space-x-2 pt-2">
                    <button type="button" 
                            @click="modalAnular = false" 
                            class="px-3 py-1.5 rounded-xl border border-slate-300 dark:border-slate-700 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer">
                        Cancelar
                    </button>
                    <button type="submit" 
                            :disabled="motivoAnulacion.trim().length < 5"
                            :class="motivoAnulacion.trim().length < 5 ? 'opacity-50 cursor-not-allowed' : ''"
                            class="px-3.5 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold shadow-xs transition cursor-pointer">
                        Confirmar Anulación
                    </button>
                </div>
            </form>
        </div>
    </div>
    </template>
</div>
@endsection