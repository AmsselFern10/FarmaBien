@extends('layouts.app')

@section('title', "Devolución {$devolucion->numero_devolucion} - FarmaBien")

@section('content')
<div class="max-w-4xl mx-auto space-y-5">
    
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

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $devolucion->tipo === 'total' ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300' }}">
                    Devolución {{ ucfirst($devolucion->tipo) }}
                </span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                    {{ ucfirst($devolucion->estado) }}
                </span>
            </div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white mt-1">{{ $devolucion->numero_devolucion }}</h1>
            <p class="text-xs text-slate-500">Procesada el {{ $devolucion->fecha->format('d/m/Y \a \l\a\s H:i') }} por {{ $devolucion->usuario->name ?? 'Usuario' }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('devoluciones.ticket', $devolucion) }}" target="_blank"
               class="inline-flex items-center px-4 py-2 rounded-xl text-xs font-bold text-white bg-slate-800 hover:bg-slate-700 dark:bg-slate-700 dark:hover:bg-slate-600 shadow-xs transition">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Imprimir Comprobante
            </a>
            <a href="{{ route('devoluciones.index') }}" 
               class="inline-flex items-center px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 shadow-xs transition">
                Volver
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-4 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs">
            <span class="text-xs font-medium text-slate-500">Monto Reembolsado</span>
            <p class="text-2xl font-black text-rose-600 dark:text-rose-400 mt-1">{{ formato_moneda($devolucion->monto_total) }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">Método: <span class="capitalize font-semibold text-slate-600 dark:text-slate-300">{{ $devolucion->metodo_reembolso }}</span></p>
        </div>
        <div class="p-4 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs">
            <span class="text-xs font-medium text-slate-500">Venta Relacionada</span>
            <p class="text-base font-bold text-slate-900 dark:text-white mt-1">
                <a href="{{ route('ventas.show', $devolucion->venta_id) }}" class="text-emerald-600 dark:text-emerald-400 hover:underline">
                    {{ $devolucion->venta->numero_comprobante ?? 'Venta #' . $devolucion->venta_id }}
                </a>
            </p>
            <p class="text-[11px] text-slate-400 mt-0.5">Cliente: {{ $devolucion->venta->cliente->nombre ?? 'Cliente General' }}</p>
        </div>
        <div class="p-4 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs">
            <span class="text-xs font-medium text-slate-500">Motivo Registrado</span>
            <p class="text-sm font-bold text-slate-800 dark:text-slate-200 mt-1 capitalize">{{ str_replace('_', ' ', $devolucion->motivo) }}</p>
            @if($devolucion->banco)
            <p class="text-[11px] text-slate-400 mt-0.5">Banco: {{ $devolucion->banco }} ({{ $devolucion->numero_transaccion ?? 'S/Ref' }})</p>
            @endif
        </div>
    </div>

    @if($devolucion->observaciones)
    <div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/50 text-xs text-amber-800 dark:text-amber-200">
        <span class="font-bold">Observaciones:</span> {{ $devolucion->observaciones }}
    </div>
    @endif

    <!-- Returned Products Table -->
    <div class="rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Medicamentos Devueltos</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800 text-[11px] font-semibold uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3">Producto</th>
                        <th class="px-4 py-3">Lote / Vencimiento</th>
                        <th class="px-4 py-3 text-center">Cant. Devuelta</th>
                        <th class="px-4 py-3 text-right">Precio Unit.</th>
                        <th class="px-4 py-3 text-right">Subtotal</th>
                        <th class="px-4 py-3 text-center">Estado / Destino</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                    @foreach($devolucion->detalles as $det)
                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition">
                        <td class="px-4 py-3">
                            <span class="font-bold text-slate-900 dark:text-white">{{ $det->producto->nombre }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="font-mono">{{ $det->lote->numero_lote ?? 'N/A' }}</span>
                            <div class="text-[11px] text-slate-500">{{ $det->lote->fecha_vencimiento ? $det->lote->fecha_vencimiento->format('d/m/Y') : 'N/A' }}</div>
                        </td>
                        <td class="px-4 py-3 text-center font-bold">{{ $det->cantidad }}</td>
                        <td class="px-4 py-3 text-right">{{ formato_moneda($det->precio_unitario) }}</td>
                        <td class="px-4 py-3 text-right font-black text-rose-600 dark:text-rose-400">{{ formato_moneda($det->subtotal) }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($det->reingresa_a_stock)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                                Reingresó a Stock
                            </span>
                            @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300">
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
