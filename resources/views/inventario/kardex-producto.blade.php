@extends('layouts.app')
@section('title', 'Kardex - {{ $producto->nombre }} - FarmaBien')
@section('content')
@php $moneda = config('app.moneda', 'C$'); @endphp
<div class="space-y-5">

    {{-- Header con datos del producto --}}
    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 border-b border-slate-300/80 dark:border-slate-800 pb-4">
        <div>
            <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400 mb-2">
                <a href="{{ route('inventario.index') }}" class="hover:text-emerald-600 transition">Inventario</a>
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <a href="{{ route('inventario.lotes') }}" class="hover:text-emerald-600 transition">Lotes</a>
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="text-slate-800 dark:text-slate-200 font-semibold">Kardex</span>
            </nav>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">{{ $producto->nombre }}</h1>
            <div class="flex flex-wrap items-center gap-2 mt-1">
                @if($producto->concentracion)<span class="text-xs px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 font-mono">{{ $producto->concentracion }}</span>@endif
                @if($producto->forma_farmaceutica)<span class="text-xs px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">{{ $producto->forma_farmaceutica }}</span>@endif
                @if($producto->categoria)<span class="text-xs px-2 py-0.5 rounded-full bg-indigo-100 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400">{{ $producto->categoria->nombre }}</span>@endif
                @if($producto->laboratorio)<span class="text-xs px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400">{{ $producto->laboratorio->nombre }}</span>@endif
            </div>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            @can('ajustar inventario')
            <a href="{{ route('inventario.ajustar') }}" class="inline-flex items-center space-x-2 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-semibold rounded-xl shadow-xs transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Ajustar</span>
            </a>
            @endcan
            <a href="{{ route('inventario.lotes') }}" class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Volver</span>
            </a>
        </div>
    </div>

    {{-- Lotes activos del producto --}}
    @if($producto->lotes->count())
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
        @foreach($producto->lotes->where('activo', true) as $lote)
        @php
            $hoy = now()->toDateString();
            $venc = $lote->fecha_vencimiento->toDateString();
            $dias = (int)now()->diffInDays($lote->fecha_vencimiento, false);
            $borderColor = $venc < $hoy ? 'border-rose-300 dark:border-rose-700' : ($dias <= 30 ? 'border-amber-300 dark:border-amber-700' : 'border-slate-200 dark:border-slate-700');
            $stockColor = $lote->stock_actual == 0 ? 'text-rose-600' : ($dias <= 0 ? 'text-rose-600' : 'text-emerald-600 dark:text-emerald-400');
        @endphp
        <div class="bg-white dark:bg-slate-900 rounded-xl border {{ $borderColor }} p-3 shadow-sm">
            <p class="text-[10px] font-bold uppercase text-slate-400 tracking-wider mb-1">Lote</p>
            <p class="text-xs font-bold font-mono text-slate-700 dark:text-slate-300">{{ $lote->numero_lote }}</p>
            <div class="mt-2 flex items-end justify-between">
                <div>
                    <p class="text-[10px] text-slate-400">Stock actual</p>
                    <p class="text-xl font-extrabold {{ $stockColor }}">{{ number_format($lote->stock_actual) }}</p>
                </div>
                <div class="text-right">
                    <p class="text-[10px] text-slate-400">Vence</p>
                    <p class="text-[11px] font-semibold {{ $venc < $hoy ? 'text-rose-600' : ($dias <= 30 ? 'text-amber-600' : 'text-slate-600 dark:text-slate-400') }}">{{ $lote->fecha_vencimiento->format('d/m/Y') }}</p>
                    @if($dias > 0 && $dias <= 60)<p class="text-[10px] text-amber-500 font-semibold">{{ $dias }}d restantes</p>@elseif($venc < $hoy)<p class="text-[10px] text-rose-500 font-bold">VENCIDO</p>@endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Filtro de fechas --}}
    <form method="GET" action="{{ route('inventario.kardex-producto', $producto) }}" class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-4">
        <div class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Desde</label>
                <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}" class="px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Hasta</label>
                <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}" class="px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
            </div>
            <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition">Filtrar</button>
            <a href="{{ route('inventario.kardex-producto', $producto) }}" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-xl transition">Limpiar</a>
            <span class="text-xs text-slate-400">{{ count($movimientos) }} movimientos</span>
        </div>
    </form>

    {{-- Sumario entradas/salidas --}}
    @php
        $totalEntradas = $movimientos->where('cantidad', '>', 0)->sum('cantidad');
        $totalSalidas = abs($movimientos->where('cantidad', '<', 0)->sum('cantidad'));
        $saldoNeto = $totalEntradas - $totalSalidas;
        $valorEntradas = $movimientos->where('tipo', 'entrada')->sum('costo_total');
        $valorSalidas = $movimientos->where('tipo', 'salida')->sum('costo_total');
    @endphp
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="bg-emerald-50 dark:bg-emerald-950/30 rounded-xl border border-emerald-200 dark:border-emerald-800 p-3 text-center">
            <p class="text-[10px] font-semibold text-emerald-600 uppercase tracking-wider">Total Entradas</p>
            <p class="text-xl font-extrabold text-emerald-700 dark:text-emerald-400 mt-1">+{{ number_format($totalEntradas) }}</p>
            <p class="text-[10px] text-emerald-600 font-mono">{{ $moneda }} {{ number_format($valorEntradas, 2) }}</p>
        </div>
        <div class="bg-rose-50 dark:bg-rose-950/30 rounded-xl border border-rose-200 dark:border-rose-800 p-3 text-center">
            <p class="text-[10px] font-semibold text-rose-600 uppercase tracking-wider">Total Salidas</p>
            <p class="text-xl font-extrabold text-rose-700 dark:text-rose-400 mt-1">-{{ number_format($totalSalidas) }}</p>
            <p class="text-[10px] text-rose-600 font-mono">{{ $moneda }} {{ number_format($valorSalidas, 2) }}</p>
        </div>
        <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-700 p-3 text-center">
            <p class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider">Saldo Neto</p>
            <p class="text-xl font-extrabold {{ $saldoNeto >= 0 ? 'text-emerald-700 dark:text-emerald-400' : 'text-rose-700 dark:text-rose-400' }} mt-1">{{ $saldoNeto >= 0 ? '+' : '' }}{{ number_format($saldoNeto) }}</p>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-3 text-center">
            <p class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider">Stock Actual</p>
            <p class="text-xl font-extrabold text-slate-900 dark:text-white mt-1">{{ number_format($producto->stock_disponible) }}</p>
            <p class="text-[10px] text-slate-400">unidades disponibles</p>
        </div>
    </div>

    {{-- Tabla Kardex --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30">
            <h3 class="text-sm font-bold text-slate-700 dark:text-slate-300">Movimientos del Kardex</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/50">
                    <tr>
                        <th class="text-left px-4 py-3 font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap">Fecha / Hora</th>
                        <th class="text-left px-4 py-3 font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Lote</th>
                        <th class="text-left px-4 py-3 font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Tipo</th>
                        <th class="text-left px-4 py-3 font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Subtipo / Motivo</th>
                        <th class="text-right px-4 py-3 font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Cantidad</th>
                        <th class="text-right px-4 py-3 font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Saldo Ant.</th>
                        <th class="text-right px-4 py-3 font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Saldo Post.</th>
                        <th class="text-right px-4 py-3 font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap">C. Unitario</th>
                        <th class="text-right px-4 py-3 font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap">C. Total</th>
                        <th class="text-left px-4 py-3 font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Origen</th>
                        <th class="text-left px-4 py-3 font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Usuario</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($movimientos as $mov)
                    @php
                        $esMerma = in_array($mov->subtipo, ['merma_vencimiento', 'merma_danio', 'vencimiento_automatico']);
                        $rowClass = $esMerma ? 'bg-rose-50/30 dark:bg-rose-950/10' : '';
                        $tipoBadge = match($mov->tipo) {
                            'entrada' => 'bg-emerald-100 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400',
                            'salida'  => 'bg-rose-100 dark:bg-rose-950/40 text-rose-700 dark:text-rose-400',
                            default   => 'bg-indigo-100 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400',
                        };
                        $cantClass = $mov->cantidad > 0 ? 'text-emerald-700 dark:text-emerald-400' : 'text-rose-700 dark:text-rose-400';
                    @endphp
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition {{ $rowClass }}">
                        <td class="px-4 py-3 whitespace-nowrap">
                            <p class="font-semibold text-slate-700 dark:text-slate-300">{{ $mov->fecha_movimiento->format('d/m/Y') }}</p>
                            <p class="text-[10px] text-slate-400">{{ $mov->fecha_movimiento->format('H:i:s') }}</p>
                        </td>
                        <td class="px-4 py-3 font-mono text-slate-500 dark:text-slate-400">{{ $mov->lote->numero_lote ?? '—' }}</td>
                        <td class="px-4 py-3"><span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold {{ $tipoBadge }}">{{ ucfirst($mov->tipo) }}</span></td>
                        <td class="px-4 py-3">
                            <p class="text-slate-600 dark:text-slate-400 capitalize">{{ str_replace('_', ' ', $mov->subtipo) }}</p>
                            @if($mov->motivo)<p class="text-[10px] text-slate-400 italic truncate max-w-40" title="{{ $mov->motivo }}">{{ $mov->motivo }}</p>@endif
                        </td>
                        <td class="px-4 py-3 text-right font-mono font-bold {{ $cantClass }}">{{ $mov->cantidad > 0 ? '+' : '' }}{{ number_format($mov->cantidad) }}</td>
                        <td class="px-4 py-3 text-right font-mono text-slate-500">{{ number_format($mov->stock_anterior) }}</td>
                        <td class="px-4 py-3 text-right font-mono font-bold text-slate-800 dark:text-slate-200">{{ number_format($mov->stock_posterior) }}</td>
                        <td class="px-4 py-3 text-right font-mono text-slate-500">{{ $moneda }} {{ number_format($mov->costo_unitario, 2) }}</td>
                        <td class="px-4 py-3 text-right font-mono font-semibold text-slate-700 dark:text-slate-300">{{ $moneda }} {{ number_format($mov->costo_total, 2) }}</td>
                        <td class="px-4 py-3 text-slate-500 text-[10px] capitalize">{{ str_replace('_', ' ', $mov->origen ?? '—') }}</td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $mov->usuario->name ?? 'Sistema' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="11" class="px-4 py-12 text-center text-slate-400">
                        <svg class="w-10 h-10 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        No hay movimientos registrados para este medicamento {{ request('fecha_desde') || request('fecha_hasta') ? 'en el rango seleccionado.' : 'aun.' }}
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
