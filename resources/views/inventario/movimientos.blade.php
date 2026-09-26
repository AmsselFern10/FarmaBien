@extends('layouts.app')
@section('title', 'Kardex de Movimientos - FarmaBien')
@section('content')
<div class="space-y-5">

    {{-- Breadcrumb --}}
    <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('inventario.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inventario</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-800 dark:text-slate-200 font-semibold">Kardex / Movimientos</span>
    </nav>

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <span>Kardex — Historial de Movimientos</span>
                <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">{{ $movimientos->total() }}</span>
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Todas las entradas, salidas y ajustes de inventario auditados en tiempo real.</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap shrink-0">
            <button type="button" 
                    @click="$dispatch('toggle-pos-fullscreen')"
                    title="Modo Pantalla Completa / Ocultar Barras"
                    class="inline-flex items-center space-x-1.5 px-3.5 py-2 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold shadow-2xs transition cursor-pointer">
                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                <span>Modo Full</span>
            </button>
            <a href="{{ route('inventario.index') }}" class="inline-flex items-center space-x-1.5 px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold rounded-xl shadow-2xs transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Volver</span>
            </a>
            <a href="{{ route('inventario.ajustar') }}" class="inline-flex items-center space-x-1.5 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-semibold rounded-xl shadow-2xs transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Ajustar Stock</span>
            </a>
        </div>
    </div>

    {{-- Filtros --}}
    <form method="GET" action="{{ route('inventario.movimientos') }}" class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs space-y-3">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Medicamento</label>
                <select name="producto_id" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                    <option value="">Todos los medicamentos</option>
                    @foreach($productos as $p)
                    <option value="{{ $p->id }}" {{ request('producto_id')==$p->id?'selected':'' }}>{{ $p->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Tipo de Movimiento</label>
                <select name="tipo" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                    <option value="">Todos los tipos</option>
                    <option value="entrada" {{ request('tipo')=='entrada'?'selected':'' }}>Entrada (+)</option>
                    <option value="salida" {{ request('tipo')=='salida'?'selected':'' }}>Salida (-)</option>
                    <option value="ajuste" {{ request('tipo')=='ajuste'?'selected':'' }}>Ajuste (±)</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Fecha Desde</label>
                <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Fecha Hasta</label>
                <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
            </div>
        </div>
        <div class="flex items-center justify-between pt-1">
            <div class="flex items-center gap-2">
                <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 dark:bg-slate-700 dark:hover:bg-slate-600 text-white text-xs font-semibold rounded-xl shadow-2xs transition inline-flex items-center justify-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    <span>Filtrar</span>
                </button>
                <a href="{{ route('inventario.movimientos') }}" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 text-xs font-semibold rounded-xl transition inline-flex items-center justify-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span>Limpiar</span>
                </a>
            </div>
            <span class="text-xs text-slate-400 font-medium">{{ $movimientos->total() }} movimientos</span>
        </div>
    </form>

    {{-- Tabla Kardex --}}
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                    <tr>
                        <th class="text-left px-4 py-3 whitespace-nowrap">Fecha</th>
                        <th class="text-left px-4 py-3">Medicamento</th>
                        <th class="text-left px-4 py-3">Lote</th>
                        <th class="text-left px-4 py-3">Tipo</th>
                        <th class="text-left px-4 py-3">Subtipo</th>
                        <th class="text-right px-4 py-3">Cantidad</th>
                        <th class="text-right px-4 py-3">Saldo Ant.</th>
                        <th class="text-right px-4 py-3">Saldo Post.</th>
                        <th class="text-right px-4 py-3">Costo Total</th>
                        <th class="text-left px-4 py-3">Usuario</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-700 dark:text-slate-300">
                    @forelse($movimientos as $mov)
                    @php
                        $esMerma = in_array($mov->subtipo, ['merma_vencimiento', 'merma_danio']);
                        $rowClass = $esMerma ? 'bg-rose-50/40 dark:bg-rose-950/10' : '';
                        $tipoBadge = match($mov->tipo) {
                            'entrada' => 'bg-emerald-100 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800',
                            'salida'  => 'bg-rose-100 dark:bg-rose-950/40 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-800',
                            default   => 'bg-indigo-100 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800',
                        };
                        $cantClass = $mov->cantidad > 0 ? 'text-emerald-700 dark:text-emerald-400' : 'text-rose-700 dark:text-rose-400';
                        $moneda = config('app.moneda', 'C$');
                    @endphp
                    <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition {{ $rowClass }}">
                        <td class="px-4 py-3 whitespace-nowrap">
                            <p class="font-semibold text-slate-800 dark:text-slate-200">{{ $mov->fecha_movimiento->format('d/m/Y') }}</p>
                            <p class="text-[10px] text-slate-400">{{ $mov->fecha_movimiento->format('H:i') }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <a href="{{ route('inventario.kardex-producto', $mov->producto_id) }}" class="font-semibold text-slate-800 dark:text-slate-200 hover:text-emerald-600 dark:hover:text-emerald-400 transition">{{ $mov->producto->nombre ?? 'N/A' }}</a>
                        </td>
                        <td class="px-4 py-3 font-mono text-slate-500 dark:text-slate-400">{{ $mov->lote->numero_lote ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold {{ $tipoBadge }}">{{ ucfirst($mov->tipo) }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-[10px] text-slate-500 dark:text-slate-400">{{ str_replace('_', ' ', $mov->subtipo) }}</span>
                        </td>
                        <td class="px-4 py-3 text-right font-mono font-bold {{ $cantClass }}">
                            {{ $mov->cantidad > 0 ? '+' : '' }}{{ number_format($mov->cantidad) }}
                        </td>
                        <td class="px-4 py-3 text-right font-mono text-slate-500 dark:text-slate-400">{{ number_format($mov->stock_anterior) }}</td>
                        <td class="px-4 py-3 text-right font-mono font-bold text-slate-800 dark:text-slate-200">{{ number_format($mov->stock_posterior) }}</td>
                        <td class="px-4 py-3 text-right font-mono text-slate-600 dark:text-slate-400">{{ $moneda }} {{ number_format($mov->costo_total, 2) }}</td>
                        <td class="px-4 py-3">
                            <p class="text-slate-700 dark:text-slate-300 font-medium">{{ $mov->usuario->name ?? 'Sistema' }}</p>
                            @if($mov->motivo)<p class="text-[10px] text-slate-400 truncate max-w-28" title="{{ $mov->motivo }}">{{ $mov->motivo }}</p>@endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="10" class="px-4 py-12 text-center text-slate-400 dark:text-slate-500">
                        <svg class="w-10 h-10 mx-auto mb-3 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        No hay movimientos registrados con los filtros aplicados.
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($movimientos->hasPages())
        <div class="px-5 py-3.5 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900">{{ $movimientos->links() }}</div>
        @endif
    </div>
</div>
@endsection
