@extends('layouts.app')

@section('title', 'Detalle de Compra #' . str_pad((string) $compra->id, 5, '0', STR_PAD_LEFT) . ' - FarmaBien')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <!-- Header Page Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200 dark:border-slate-700">
        <div>
            <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 transition">Inicio</a>
                <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <a href="{{ route('compras.index') }}" class="hover:text-emerald-600 transition">Compras</a>
                <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="text-slate-800 dark:text-slate-200 font-semibold">Detalle de Compra</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white flex items-center gap-3">
                <span>Compra #{{ str_pad((string) $compra->id, 5, '0', STR_PAD_LEFT) }}</span>
                @if($compra->estado == 'recibida')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 dark:bg-emerald-900/40 text-emerald-800 dark:text-emerald-300">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 mr-2 animate-pulse"></span>
                        Recibida
                    </span>
                @else
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-rose-100 dark:bg-rose-900/40 text-rose-800 dark:text-rose-300">
                        <span class="w-2 h-2 rounded-full bg-rose-500 mr-2"></span>
                        Anulada
                    </span>
                @endif
            </h1>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('compras.index') }}"
               class="px-4 py-2 bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 font-semibold text-xs rounded-xl transition">
                ← Volver al Listado
            </a>

            @can('registrar compras')
                @if($compra->puedeModificarse())
                    <a href="{{ route('compras.edit', $compra) }}"
                       class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-semibold text-xs rounded-xl shadow-xs transition flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Modificar
                    </a>
                @endif
            @endcan

            <a href="{{ route('compras.ticket', $compra) }}" target="_blank"
               class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white font-semibold text-xs rounded-xl shadow-xs transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Imprimir Ticket
            </a>

            <a href="{{ route('compras.pdf', $compra) }}" target="_blank"
               class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-semibold text-xs rounded-xl shadow-xs transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                PDF
            </a>
        </div>
    </div>

    <!-- Info Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Card Proveedor -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm space-y-3">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100 dark:border-slate-700 pb-2">
                Información del Proveedor
            </h3>
            <div>
                <p class="text-base font-bold text-slate-900 dark:text-white">{{ $compra->proveedor->nombre }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">RUC / Identificación: <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $compra->proveedor->ruc ?? 'No especificado' }}</span></p>
                <p class="text-xs text-slate-500 dark:text-slate-400">Teléfono: <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $compra->proveedor->telefono ?? 'Sin teléfono' }}</span></p>
                <p class="text-xs text-slate-500 dark:text-slate-400">Dirección: <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $compra->proveedor->direccion ?? 'Sin dirección' }}</span></p>
            </div>
        </div>

        <!-- Card Documento & Registro -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm space-y-3">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100 dark:border-slate-700 pb-2">
                Datos del Comprobante y Registro
            </h3>
            <div class="grid grid-cols-2 gap-3 text-xs">
                <div>
                    <span class="text-slate-400 block">N° Comprobante</span>
                    <span class="font-bold text-slate-900 dark:text-white text-sm">{{ $compra->numero_comprobante ?? 'Sin número' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block">Fecha de Emisión</span>
                    <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $compra->fecha?->format('d/m/Y H:i') ?? '—' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block">Registrado Por</span>
                    <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $compra->usuario?->name ?? 'Usuario Sistema' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block">Fecha de Registro</span>
                    <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $compra->created_at?->format('d/m/Y H:i') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla Desglose de Productos e Lotes -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80">
            <h2 class="text-sm font-bold text-slate-900 dark:text-white">
                Lotes e Ítems Ingresados ({{ $compra->detalles->count() }})
            </h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-100 dark:bg-slate-700/60 text-slate-600 dark:text-slate-300 uppercase font-semibold">
                        <th class="py-3 px-4">Producto</th>
                        <th class="py-3 px-4">Presentación</th>
                        <th class="py-3 px-4">N° Lote</th>
                        <th class="py-3 px-4">Vencimiento</th>
                        <th class="py-3 px-4 text-center">Cantidad</th>
                        <th class="py-3 px-4 text-right">Precio Unitario (C$)</th>
                        <th class="py-3 px-4 text-right">Subtotal (C$)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700 text-slate-700 dark:text-slate-200">
                    @foreach($compra->detalles as $det)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition">
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-900 dark:text-white">{{ $det->producto?->nombre ?? 'Producto Eliminado' }}</div>
                                <div class="text-[11px] text-slate-400">{{ $det->producto?->laboratorio?->nombre ?? '' }}</div>
                            </td>

                            <td class="py-3 px-4 font-medium">
                                {{ $det->tipo_presentacion ?? 'Unidad Base' }}
                                @if($det->unidades_por_presentacion > 1)
                                    <span class="text-[11px] text-slate-400 block">({{ $det->unidades_por_presentacion }} un. base)</span>
                                @endif
                            </td>

                            <td class="py-3 px-4">
                                <span class="font-mono font-bold text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/30 px-2 py-0.5 rounded-md">
                                    {{ $det->lote?->numero_lote ?? 'N/A' }}
                                </span>
                            </td>

                            <td class="py-3 px-4 font-medium">
                                {{ $det->lote?->fecha_vencimiento ? $det->lote->fecha_vencimiento->format('d/m/Y') : 'N/A' }}
                            </td>

                            <td class="py-3 px-4 text-center font-bold">
                                {{ number_format($det->cantidad_presentaciones) }}
                            </td>

                            <td class="py-3 px-4 text-right font-semibold">
                                C$ {{ number_format($det->precio_unitario, 2) }}
                            </td>

                            <td class="py-3 px-4 text-right font-extrabold text-slate-900 dark:text-white">
                                C$ {{ number_format($det->subtotal, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="p-5 bg-slate-900 text-white flex justify-end">
            <div class="text-right space-y-1">
                <span class="text-xs text-slate-400 uppercase font-medium">Total Liquidado</span>
                <p class="text-3xl font-black text-emerald-400">C$ {{ number_format($compra->total, 2) }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
