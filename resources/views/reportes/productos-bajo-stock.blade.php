@extends('layouts.app')

@section('title', 'Reporte de Stock Mínimo - FarmaBien')

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 print:hidden">
        <div>
            <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
                <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <a href="{{ route('reportes.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition font-medium">Centro de Reportes</a>
                <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="text-slate-800 dark:text-slate-200 font-semibold">Stock Mínimo</span>
            </nav>
            <div class="flex items-center space-x-2">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300">
                    Alerta de Abastecimiento
                </span>
                <span class="text-xs text-slate-400">&bull;</span>
                <span class="text-xs text-slate-500 dark:text-slate-400">Medicamentos bajo nivel mínimo</span>
            </div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white mt-1">Reporte de Stock Mínimo</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Productos que requieren reposición inmediata o urgente. Stock actual por debajo del mínimo configurado.
            </p>
        </div>

        <div class="flex items-center space-x-2 flex-wrap">
            <button onclick="window.print()"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:border-slate-400 transition-colors shadow-sm print:hidden">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Imprimir
            </button>
            @can('gestionar compras')
                <a href="{{ route('compras.create') }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-rose-500 hover:bg-rose-600 text-white transition-colors shadow-sm print:hidden">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Nueva Orden de Compra
                </a>
            @endcan
        </div>
    </div>

    <!-- Banner de alerta crítica -->
    @php
        $criticos = collect($productos)->filter(fn($p) => $p->stock_disponible === 0);
        $bajos = collect($productos)->filter(fn($p) => $p->stock_disponible > 0);
        $totalAfectados = count($productos);
    @endphp

    @if($criticos->count() > 0)
        <div class="bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800 rounded-2xl p-4 flex items-start gap-3 print:hidden">
            <div class="w-9 h-9 rounded-xl bg-rose-100 dark:bg-rose-950 flex items-center justify-center flex-shrink-0 mt-0.5">
                <svg class="w-5 h-5 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div>
                <p class="text-sm font-bold text-rose-800 dark:text-rose-300">
                    ¡Atención! {{ $criticos->count() }} {{ $criticos->count() === 1 ? 'medicamento agotado' : 'medicamentos agotados' }} — Stock en cero
                </p>
                <p class="text-xs text-rose-600 dark:text-rose-400 mt-0.5">
                    Estos productos no tienen stock disponible y no pueden ser vendidos. Requieren reposición inmediata.
                </p>
            </div>
        </div>
    @endif

    <!-- KPIs -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total bajo stock -->
        <div class="bg-white dark:bg-slate-800/60 rounded-2xl border border-rose-200 dark:border-rose-800/50 shadow-sm p-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400">Bajo Mínimo</span>
                <span class="w-8 h-8 rounded-xl bg-rose-100 dark:bg-rose-950/50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/></svg>
                </span>
            </div>
            <div class="text-2xl font-bold text-rose-700 dark:text-rose-400">{{ $totalAfectados }}</div>
            <div class="text-xs text-slate-400 mt-0.5">Productos afectados</div>
        </div>
        <!-- Agotados -->
        <div class="bg-white dark:bg-slate-800/60 rounded-2xl border border-rose-200 dark:border-rose-800/50 shadow-sm p-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400">Agotados</span>
                <span class="w-8 h-8 rounded-xl bg-rose-100 dark:bg-rose-950/50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </span>
            </div>
            <div class="text-2xl font-bold text-rose-700 dark:text-rose-400">{{ $criticos->count() }}</div>
            <div class="text-xs text-slate-400 mt-0.5">Stock = 0 unidades</div>
        </div>
        <!-- Con poco stock -->
        <div class="bg-white dark:bg-slate-800/60 rounded-2xl border border-amber-200 dark:border-amber-800/50 shadow-sm p-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400">Stock Bajo</span>
                <span class="w-8 h-8 rounded-xl bg-amber-100 dark:bg-amber-950/50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </span>
            </div>
            <div class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ $bajos->count() }}</div>
            <div class="text-xs text-slate-400 mt-0.5">Por debajo del mínimo</div>
        </div>
        <!-- Timestamp -->
        <div class="bg-white dark:bg-slate-800/60 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400">Actualizado</span>
                <span class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-700 flex items-center justify-center">
                    <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <div class="text-sm font-bold text-slate-900 dark:text-white">{{ now()->format('d/m/Y') }}</div>
            <div class="text-xs text-slate-400 mt-0.5">{{ now()->format('H:i') }} hs — Tiempo real</div>
        </div>
    </div>

    <!-- Tabla de Productos Bajo Stock -->
    <div class="bg-white dark:bg-slate-800/60 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-700">
            <h2 class="text-sm font-bold text-slate-900 dark:text-white">Productos con Stock Crítico</h2>
            <p class="text-xs text-slate-400">{{ $totalAfectados }} {{ $totalAfectados === 1 ? 'producto requiere' : 'productos requieren' }} atención inmediata</p>
        </div>

        @if(empty($productos))
            <div class="flex flex-col items-center justify-center py-16 text-center">
                <div class="w-14 h-14 rounded-2xl bg-emerald-100 dark:bg-emerald-950/50 flex items-center justify-center mb-3">
                    <svg class="w-7 h-7 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <p class="text-sm font-bold text-emerald-700 dark:text-emerald-400">¡Inventario saludable!</p>
                <p class="text-xs text-slate-400 mt-1">Todos los productos están por encima del stock mínimo configurado.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-900/40 text-left">
                            <th class="px-4 py-2.5 font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400">Estado</th>
                            <th class="px-4 py-2.5 font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400">Medicamento</th>
                            <th class="px-4 py-2.5 font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400">Categoría</th>
                            <th class="px-4 py-2.5 font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400">Laboratorio</th>
                            <th class="px-4 py-2.5 font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400 text-center">Stock Actual</th>
                            <th class="px-4 py-2.5 font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400 text-center">Stock Mínimo</th>
                            <th class="px-4 py-2.5 font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400 text-center">Déficit</th>
                            <th class="px-4 py-2.5 font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400 text-center print:hidden">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                        @foreach($productos as $producto)
                            @php
                                $stockActual = $producto->stock_disponible ?? 0;
                                $stockMin = $producto->stock_minimo ?? 0;
                                $deficit = max(0, $stockMin - $stockActual);
                                $esAgotado = $stockActual === 0;
                            @endphp
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/30 transition-colors {{ $esAgotado ? 'bg-rose-50/50 dark:bg-rose-950/10' : '' }}">
                                <td class="px-4 py-3">
                                    @if($esAgotado)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse inline-block"></span>
                                            Agotado
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 inline-block"></span>
                                            Stock bajo
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-semibold text-slate-800 dark:text-slate-200">{{ $producto->nombre }}</div>
                                    @if($producto->principio_activo)
                                        <div class="text-slate-400">{{ $producto->principio_activo }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-slate-500 dark:text-slate-400">
                                    {{ $producto->categoria?->nombre ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-slate-500 dark:text-slate-400">
                                    {{ $producto->laboratorio?->nombre ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="font-bold {{ $esAgotado ? 'text-rose-700 dark:text-rose-400' : 'text-amber-600 dark:text-amber-400' }}">
                                        {{ number_format($stockActual) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center text-slate-600 dark:text-slate-300 font-semibold">
                                    {{ number_format($stockMin) }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="font-bold text-rose-700 dark:text-rose-400">
                                        −{{ number_format($deficit) }}
                                    </span>
                                    <span class="text-slate-400"> un.</span>
                                </td>
                                <td class="px-4 py-3 text-center print:hidden">
                                    <div class="flex items-center justify-center gap-1.5">
                                        @can('ver productos')
                                            <a href="{{ route('productos.show', $producto) }}"
                                               class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-semibold bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                Ver
                                            </a>
                                        @endcan
                                        @can('gestionar compras')
                                            <a href="{{ route('compras.create') }}"
                                               class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-semibold bg-rose-100 dark:bg-rose-950/40 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-800 hover:bg-rose-200 dark:hover:bg-rose-900/50 transition-colors">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                                Reponer
                                            </a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Footer resumen -->
            <div class="px-5 py-3 border-t border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/20">
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    <span class="font-semibold text-rose-600 dark:text-rose-400">{{ $criticos->count() }}</span> agotados &bull;
                    <span class="font-semibold text-amber-600 dark:text-amber-400">{{ $bajos->count() }}</span> bajo mínimo &bull;
                    Total déficit: <span class="font-semibold text-slate-700 dark:text-slate-300">
                        {{ number_format(collect($productos)->sum(fn($p) => max(0, ($p->stock_minimo ?? 0) - ($p->stock_disponible ?? 0)))) }}
                    </span> unidades a reponer
                </p>
            </div>
        @endif
    </div>
</div>

<style>
@media print {
    .print\:hidden { display: none !important; }
    body { background: white !important; }
    .rounded-2xl { border-radius: 0 !important; }
    .shadow-sm { box-shadow: none !important; }
    .animate-pulse { animation: none !important; }
}
</style>
@endsection
