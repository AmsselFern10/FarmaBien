@extends('layouts.app')
@section('title', $devolucionCompra->numero_devolucion . ' - Devolución a Proveedor - FarmaBien')
@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    {{-- Breadcrumb --}}
    <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
        <a href="{{ route('compras.devoluciones.index') }}" class="hover:text-rose-600 dark:hover:text-rose-400 transition">Devoluciones a Proveedor</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-800 dark:text-slate-200 font-semibold">{{ $devolucionCompra->numero_devolucion }}</span>
    </nav>

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-3 flex-wrap">
                {{ $devolucionCompra->numero_devolucion }}
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold border {{ $devolucionCompra->estadoBadge }}">
                    {{ ucfirst($devolucionCompra->estado) }}
                </span>
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Registrada por <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $devolucionCompra->usuario->name ?? '—' }}</span>
                el {{ $devolucionCompra->created_at->format('d/m/Y H:i') }}
                @if($devolucionCompra->fecha_envio)
                · Enviada al proveedor el {{ $devolucionCompra->fecha_envio->format('d/m/Y H:i') }}
                @endif
            </p>
        </div>
        <a href="{{ route('compras.devoluciones.index') }}"
           class="inline-flex items-center space-x-1.5 px-3.5 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-xl transition shrink-0">
            <span>&larr;</span><span>Volver</span>
        </a>
    </div>

    @if(session('success'))
    <div class="px-4 py-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-300 dark:border-emerald-700 text-xs text-emerald-800 dark:text-emerald-300 font-semibold flex items-center space-x-2">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span>{{ session('success') }}</span>
    </div>
    @endif
    @if(session('error'))
    <div class="px-4 py-3 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-300 dark:border-rose-700 text-xs text-rose-800 dark:text-rose-300 font-semibold flex items-center space-x-2">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    {{-- Info cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm">
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Proveedor</p>
            <p class="text-sm font-bold text-slate-900 dark:text-white mt-0.5 truncate">{{ $devolucionCompra->proveedor->nombre ?? '—' }}</p>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm">
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Total Devuelto</p>
            <p class="text-sm font-bold font-mono text-rose-600 dark:text-rose-400 mt-0.5">C\$ {{ number_format($devolucionCompra->total_devolucion, 2) }}</p>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm">
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Productos</p>
            <p class="text-sm font-bold text-slate-900 dark:text-white mt-0.5">{{ $devolucionCompra->detalles->count() }} lote(s)</p>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm">
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Compra de Origen</p>
            <p class="text-sm font-bold text-slate-900 dark:text-white mt-0.5">
                @if($devolucionCompra->compra)
                    <a href="{{ route('compras.show', $devolucionCompra->compra) }}" class="text-emerald-600 dark:text-emerald-400 hover:underline">#{{ $devolucionCompra->compra_id }}</a>
                @else
                    Sin vincular
                @endif
            </p>
        </div>
    </div>

    {{-- Motivo --}}
    <div class="bg-white dark:bg-slate-900 rounded-xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm">
        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Motivo</p>
        <p class="text-sm text-slate-800 dark:text-slate-200">{{ $devolucionCompra->motivo }}</p>
    </div>

    {{-- Detalle de lotes --}}
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Lotes Devueltos</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">Medicamento</th>
                        <th class="px-5 py-3.5">N° Lote</th>
                        <th class="px-5 py-3.5 text-center">Cantidad</th>
                        <th class="px-5 py-3.5 text-right">C\$ Unit.</th>
                        <th class="px-5 py-3.5 text-right">Subtotal</th>
                        <th class="px-5 py-3.5">Motivo Ítem</th>
                        <th class="px-5 py-3.5 text-center">MINSA</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-700 dark:text-slate-300">
                    @foreach($devolucionCompra->detalles as $det)
                    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition">
                        <td class="px-5 py-3.5 font-semibold text-slate-900 dark:text-white">
                            {{ $det->producto->nombre }}
                        </td>
                        <td class="px-5 py-3.5 font-mono text-slate-500 dark:text-slate-400 text-[11px]">
                            {{ $det->lote->numero_lote ?? '—' }}
                        </td>
                        <td class="px-5 py-3.5 text-center font-mono font-bold text-rose-600 dark:text-rose-400">
                            -{{ $det->cantidad }}
                        </td>
                        <td class="px-5 py-3.5 text-right font-mono">C\$ {{ number_format($det->precio_unitario, 2) }}</td>
                        <td class="px-5 py-3.5 text-right font-mono font-bold">C\$ {{ number_format($det->subtotal, 2) }}</td>
                        <td class="px-5 py-3.5 text-slate-500 dark:text-slate-400">{{ $det->motivo_detalle ?? '—' }}</td>
                        <td class="px-5 py-3.5 text-center">
                            @if($det->producto->esControlado())
                            <span class="px-2 py-0.5 rounded text-[9px] font-black bg-rose-100 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300">CONTROLADO</span>
                            @else
                            <span class="text-slate-300 dark:text-slate-600 text-[10px]">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-slate-50 dark:bg-slate-800/40 border-t border-slate-200 dark:border-slate-700">
                    <tr>
                        <td colspan="4" class="px-5 py-3 text-right text-xs font-bold text-slate-700 dark:text-slate-300">TOTAL</td>
                        <td class="px-5 py-3 text-right font-mono font-bold text-rose-600 dark:text-rose-400">
                            C\$ {{ number_format($devolucionCompra->total_devolucion, 2) }}
                        </td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Acciones de estado --}}
    @if($devolucionCompra->estado === 'pendiente')
    <div class="flex items-center gap-3 justify-end">
        <form method="POST" action="{{ route('compras.devoluciones.enviar', $devolucionCompra) }}">
            @csrf
            <button type="submit"
                    class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition">
                Marcar como Enviada al Proveedor
            </button>
        </form>
    </div>
    @elseif($devolucionCompra->estado === 'enviada')
    <div class="flex items-center gap-3 justify-end">
        <form method="POST" action="{{ route('compras.devoluciones.confirmar', $devolucionCompra) }}">
            @csrf
            <button type="submit"
                    class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl transition">
                Confirmar Aceptación del Proveedor
            </button>
        </form>
    </div>
    @endif

</div>
@endsection
