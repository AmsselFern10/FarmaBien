@extends('layouts.app')

@section('title', 'Detalle de Promoción - FarmaBien')

@section('content')
<div class="space-y-4" x-data="{ fullWidth: false }">
    <!-- Breadcrumb -->
    <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('promociones.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Promociones</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-800 dark:text-slate-200 font-semibold">{{ $promocion->nombre }}</span>
    </nav>

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-1 border-b border-slate-300/80 dark:border-slate-800">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <span>{{ $promocion->nombre }}</span>
                <span class="px-2.5 py-0.5 text-xs font-bold rounded-lg bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                    {{ $promocion->badge_texto }}
                </span>
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Vigencia del {{ $promocion->fecha_inicio->format('d/m/Y H:i') }} al {{ $promocion->fecha_fin->format('d/m/Y H:i') }}
            </p>
        </div>
        <div class="flex items-center space-x-2 shrink-0">
            <button @click="fullWidth = !fullWidth; $dispatch('toggle-full-width', { full: fullWidth })" 
                    type="button" 
                    class="p-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition"
                    :title="fullWidth ? 'Modo estándar' : 'Modo pantalla completa'">
                <svg x-show="!fullWidth" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                <svg x-show="fullWidth" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 9L4 4m0 0h4M4 4v4m11 0l5-5m0 0h-4m4 0v4M9 15l-5 5m0 0h4m-4 0v-4m11 0l5 5m0 0h-4m4 0v-4"/></svg>
            </button>

            @can('editar promociones')
            <a href="{{ route('promociones.edit', $promocion) }}" 
               class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl shadow-xs transition flex items-center space-x-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Editar</span>
            </a>
            @endcan

            <a href="{{ route('promociones.index') }}" 
               class="px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-xl text-xs font-semibold shadow-xs transition">
                &larr; Volver
            </a>
        </div>
    </div>

    <!-- Overview Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs">
            <p class="text-xs text-slate-500 dark:text-slate-400">Tipo de Beneficio</p>
            <p class="text-lg font-bold text-slate-900 dark:text-white mt-1">{{ strtoupper($promocion->tipo) }}</p>
            <p class="text-[11px] text-emerald-600 font-semibold">{{ $promocion->badge_texto }} de descuento</p>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs">
            <p class="text-xs text-slate-500 dark:text-slate-400">Alcance Configurado</p>
            <p class="text-lg font-bold text-slate-900 dark:text-white mt-1">{{ ucfirst($promocion->alcance) }}</p>
            <p class="text-[11px] text-slate-500 truncate">{{ $promocion->alcance_descripcion }}</p>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs">
            <p class="text-xs text-slate-500 dark:text-slate-400">Consumo de Stock en Oferta</p>
            <p class="text-lg font-bold text-slate-900 dark:text-white mt-1">
                {{ $promocion->stock_consumido }} <span class="text-xs font-normal text-slate-400">/ {{ $promocion->stock_limite ?? '∞' }} unids</span>
            </p>
            <p class="text-[11px] text-indigo-600 font-medium">Mín. {{ $promocion->min_unidades }} unids por ticket</p>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs">
            <p class="text-xs text-slate-500 dark:text-slate-400">Estado Operativo</p>
            <p class="text-lg font-bold mt-1">
                @if($promocion->esVigente())
                    <span class="text-emerald-600 dark:text-emerald-400">● En Vigor (Activa)</span>
                @elseif(!$promocion->activo)
                    <span class="text-slate-400">Inactiva (Pausada)</span>
                @else
                    <span class="text-rose-500">Expirada / Fuera de Rango</span>
                @endif
            </p>
            <p class="text-[11px] text-slate-500">Cierra: {{ $promocion->fecha_fin->diffForHumans() }}</p>
        </div>
    </div>

    <!-- Medicamentos Afectados -->
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs p-5 space-y-3">
        <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center justify-between">
            <span>Medicamentos y Productos con Descuento Aplicado</span>
            <span class="text-xs text-slate-500">{{ $productosAfectados->count() }} producto(s) en esta vista</span>
        </h3>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/75 dark:bg-slate-800/50 text-slate-500 uppercase tracking-wider font-semibold">
                        <th class="py-2.5 px-3">Producto</th>
                        <th class="py-2.5 px-3">Laboratorio</th>
                        <th class="py-2.5 px-3 text-right">Precio Regular</th>
                        <th class="py-2.5 px-3 text-right">Precio en Oferta</th>
                        <th class="py-2.5 px-3 text-right">Ahorro</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                    @forelse($productosAfectados as $prod)
                    @php
                        $precioReg = (float) $prod->precio_venta;
                        $precioOferta = $promocion->calcularPrecioUnitario($precioReg);
                        $ahorro = max(0, $precioReg - $precioOferta);
                    @endphp
                    <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30">
                        <td class="py-2.5 px-3 font-medium text-slate-900 dark:text-white">
                            {{ $prod->nombre }} {{ $prod->concentracion }}
                        </td>
                        <td class="py-2.5 px-3 text-slate-500">
                            {{ $prod->laboratorio->nombre ?? 'N/A' }}
                        </td>
                        <td class="py-2.5 px-3 text-right font-mono line-through text-slate-400">
                            ${{ number_format($precioReg, 2) }}
                        </td>
                        <td class="py-2.5 px-3 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400">
                            ${{ number_format($precioOferta, 2) }}
                        </td>
                        <td class="py-2.5 px-3 text-right font-mono text-rose-600 dark:text-rose-400 font-semibold">
                            -${{ number_format($ahorro, 2) }} ({{ $promocion->badge_texto }})
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-6 text-center text-slate-400">
                            No se registran productos asociados a esta categoría o laboratorio actualmente.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
