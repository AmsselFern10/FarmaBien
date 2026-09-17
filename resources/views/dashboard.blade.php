@extends('layouts.app')

@section('title', 'Dashboard - FarmaBien')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">Panel Principal</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Resumen operativo en tiempo real &bull; {{ now()->translatedFormat('l, d \d\e F \d\e Y') }}
            </p>
        </div>

        <div class="flex items-center space-x-2">
            @can('realizar ventas')
            <a href="{{ route('ventas.create') }}" 
               class="inline-flex items-center space-x-2 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Nueva Venta</span>
                <kbd class="ml-1 px-1 py-0.2 bg-emerald-800 text-emerald-100 rounded text-[10px] font-mono">F2</kbd>
            </a>
            @endcan

            @can('crear compras')
            <a href="{{ route('compras.create') }}" 
               class="inline-flex items-center space-x-2 px-3.5 py-2 bg-slate-800 hover:bg-slate-700 dark:bg-slate-700 dark:hover:bg-slate-600 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <span>Recepción Compra</span>
                <kbd class="ml-1 px-1 py-0.2 bg-slate-900 text-slate-300 rounded text-[10px] font-mono">F4</kbd>
            </a>
            @endcan
        </div>
    </div>

    <!-- 4 KPI Cards: Cohesivas, limpias y profesionales -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Ventas Hoy -->
        <div class="bg-white dark:bg-slate-900 rounded-xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Ventas Hoy</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-bold text-slate-900 dark:text-white">
                    ${{ number_format($ventasHoy, 2) }}
                </div>
                <div class="flex items-center justify-between mt-2 text-xs">
                    <span class="text-slate-500 dark:text-slate-400">{{ $cantidadVentasHoy }} {{ Str::plural('ticket', $cantidadVentasHoy) }}</span>
                    <a href="{{ route('ventas.index') }}" class="font-medium text-emerald-600 dark:text-emerald-400 hover:underline">Ver todas &rarr;</a>
                </div>
            </div>
        </div>

        <!-- Compras del Mes -->
        <div class="bg-white dark:bg-slate-900 rounded-xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Compras del Mes</span>
                <div class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-bold text-slate-900 dark:text-white">
                    ${{ number_format($comprasMes, 2) }}
                </div>
                <div class="flex items-center justify-between mt-2 text-xs">
                    <span class="text-slate-500 dark:text-slate-400">Inversión acumulada</span>
                    <a href="{{ route('compras.index') }}" class="font-medium text-slate-700 dark:text-slate-300 hover:underline">Ver compras &rarr;</a>
                </div>
            </div>
        </div>

        <!-- Stock Crítico -->
        <div class="bg-white dark:bg-slate-900 rounded-xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Stock Crítico</span>
                <div class="w-8 h-8 rounded-lg {{ $productosBajoStockCount > 0 ? 'bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400' : 'bg-slate-100 dark:bg-slate-800 text-slate-400' }} flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
            </div>
            <div class="mt-3">
                <div class="flex items-baseline space-x-1.5">
                    <span class="text-2xl font-bold {{ $productosBajoStockCount > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-slate-900 dark:text-white' }}">
                        {{ $productosBajoStockCount }}
                    </span>
                    <span class="text-xs text-slate-500">ítems</span>
                </div>
                <div class="flex items-center justify-between mt-2 text-xs">
                    <span class="text-slate-500 dark:text-slate-400">Bajo el mínimo</span>
                    <a href="{{ route('inventario.alertas') }}" class="font-medium text-amber-600 dark:text-amber-400 hover:underline">Ver alertas &rarr;</a>
                </div>
            </div>
        </div>

        <!-- Lotes por Vencer -->
        <div class="bg-white dark:bg-slate-900 rounded-xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Lotes &lt; 30 días</span>
                <div class="w-8 h-8 rounded-lg {{ $lotesPorVencerCount > 0 ? 'bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400' : 'bg-slate-100 dark:bg-slate-800 text-slate-400' }} flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
            </div>
            <div class="mt-3">
                <div class="flex items-baseline space-x-1.5">
                    <span class="text-2xl font-bold {{ $lotesPorVencerCount > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-900 dark:text-white' }}">
                        {{ $lotesPorVencerCount }}
                    </span>
                    <span class="text-xs text-slate-500">lotes</span>
                </div>
                <div class="flex items-center justify-between mt-2 text-xs">
                    <span class="text-slate-500 dark:text-slate-400">Por expirar</span>
                    <a href="{{ route('inventario.lotes') }}" class="font-medium text-rose-600 dark:text-rose-400 hover:underline">Auditar &rarr;</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Barra de Alerta Clínica Si Hay Recetas Pendientes -->
    @if($recetasPendientesCount > 0)
    <div class="bg-amber-50/80 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/60 rounded-xl p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
        <div class="flex items-center space-x-3">
            <span class="text-amber-600 dark:text-amber-400 text-lg">⚠️</span>
            <div>
                <h4 class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                    Hay {{ $recetasPendientesCount }} {{ Str::plural('receta médica', $recetasPendientesCount) }} pendiente(s) de validación
                </h4>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Requiere confirmación médica antes del despacho en caja.
                </p>
            </div>
        </div>
        <a href="{{ route('recetas.index') }}" 
           class="px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold rounded-lg shadow-sm transition shrink-0">
            Atender Recetas
        </a>
    </div>
    @endif

    <!-- Tablas de Actividad Reciente (Diseño limpio y equilibrado) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Últimas Ventas -->
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Últimas Ventas</h3>
                <a href="{{ route('ventas.index') }}" class="text-xs font-medium text-emerald-600 dark:text-emerald-400 hover:underline">
                    Ver historial &rarr;
                </a>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-800/60 overflow-x-auto">
                @forelse($ultimasVentas as $venta)
                <div class="px-5 py-3 flex items-center justify-between hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition">
                    <div class="flex items-center space-x-3 min-w-0">
                        <span class="text-xs font-mono font-medium text-slate-400 dark:text-slate-500 shrink-0">
                            #{{ $venta->id }}
                        </span>
                        <div class="truncate">
                            <p class="text-sm font-medium text-slate-800 dark:text-slate-200 truncate">
                                {{ $venta->cliente ? $venta->cliente->nombre : 'Público General / Venta Mostrador' }}
                            </p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                {{ $venta->fecha ? \Carbon\Carbon::parse($venta->fecha)->format('d/m H:i') : $venta->created_at->format('d/m H:i') }} &bull; Cajero: {{ $venta->usuario->name ?? 'Sistema' }}
                            </p>
                        </div>
                    </div>
                    <div class="text-right shrink-0 ml-4">
                        <span class="text-sm font-semibold text-slate-900 dark:text-white block">
                            ${{ number_format($venta->total, 2) }}
                        </span>
                        <div class="flex items-center justify-end space-x-1 mt-0.5">
                            <span class="text-[11px] text-slate-500 dark:text-slate-400">
                                {{ ucfirst($venta->metodo_pago) }}
                            </span>
                            <a href="{{ route('ventas.show', $venta) }}" class="text-slate-400 hover:text-emerald-600 dark:hover:text-emerald-400 text-xs pl-1">
                                &rarr;
                            </a>
                        </div>
                    </div>
                </div>
                @empty
                <div class="p-6 text-center text-slate-400 text-xs">
                    No hay ventas registradas hoy.
                </div>
                @endforelse
            </div>
        </div>

        <!-- Últimas Compras -->
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Últimas Recepciones de Compra</h3>
                <a href="{{ route('compras.index') }}" class="text-xs font-medium text-slate-600 dark:text-slate-400 hover:underline">
                    Ver registro &rarr;
                </a>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-800/60 overflow-x-auto">
                @forelse($ultimasCompras as $compra)
                <div class="px-5 py-3 flex items-center justify-between hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition">
                    <div class="flex items-center space-x-3 min-w-0">
                        <span class="text-xs font-mono font-medium text-slate-400 dark:text-slate-500 shrink-0">
                            #{{ $compra->id }}
                        </span>
                        <div class="truncate">
                            <p class="text-sm font-medium text-slate-800 dark:text-slate-200 truncate">
                                {{ $compra->proveedor->razon_social ?? $compra->proveedor->nombre ?? 'Proveedor Droguería' }}
                            </p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                Factura: {{ $compra->numero_factura ?? 'S/N' }} &bull; {{ $compra->fecha ? \Carbon\Carbon::parse($compra->fecha)->format('d/m/Y') : $compra->created_at->format('d/m/Y') }}
                            </p>
                        </div>
                    </div>
                    <div class="text-right shrink-0 ml-4">
                        <span class="text-sm font-semibold text-slate-900 dark:text-white block">
                            ${{ number_format($compra->total, 2) }}
                        </span>
                        <div class="flex items-center justify-end space-x-1 mt-0.5">
                            <span class="text-[11px] text-slate-500 dark:text-slate-400">
                                {{ ucfirst($compra->estado) }}
                            </span>
                            <a href="{{ route('compras.show', $compra) }}" class="text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 text-xs pl-1">
                                &rarr;
                            </a>
                        </div>
                    </div>
                </div>
                @empty
                <div class="p-6 text-center text-slate-400 text-xs">
                    No hay compras recientes registradas.
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
