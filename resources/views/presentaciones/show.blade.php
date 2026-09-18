@extends('layouts.app')

@section('title', 'Ficha de Presentación - FarmaBien')

@section('content')
<div class="space-y-5">
    <!-- Breadcrumb -->
    <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('presentaciones.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Presentaciones</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-800 dark:text-slate-200 font-semibold">{{ $presentacion->nombre }}</span>
    </nav>

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-3">
                {{ $presentacion->nombre }}
                @if($presentacion->es_unidad_base)
                <span class="px-2 py-0.5 rounded-lg text-[10px] font-black bg-indigo-100 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300">UNIDAD BASE</span>
                @endif
                @if($presentacion->activo)
                <span class="px-2 py-0.5 rounded-lg text-[10px] font-black bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300">ACTIVA</span>
                @else
                <span class="px-2 py-0.5 rounded-lg text-[10px] font-black bg-slate-100 dark:bg-slate-800 text-slate-500">INACTIVA</span>
                @endif
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Medicamento: <a href="{{ route('productos.show', $presentacion->producto) }}" class="font-semibold text-emerald-600 dark:text-emerald-400 hover:underline">{{ $presentacion->producto->nombre }}</a>
                @if($presentacion->producto->laboratorio)
                · <span class="text-slate-400">{{ $presentacion->producto->laboratorio->nombre }}</span>
                @endif
            </p>
        </div>
        <div class="flex items-center space-x-2">
            @can('editar productos')
            <a href="{{ route('presentaciones.edit', $presentacion) }}"
               class="inline-flex items-center space-x-1.5 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Editar</span>
            </a>
            @endcan
            <a href="{{ route('presentaciones.index') }}"
               class="px-3.5 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-xl transition">
                &larr; Volver
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm">
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Unidades / Presentación</p>
            <p class="text-2xl font-black text-slate-900 dark:text-white mt-1">x{{ $presentacion->unidades_por_presentacion }}</p>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm">
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Precio de Venta</p>
            <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1">{{ $presentacion->precio_venta ? '$'.number_format($presentacion->precio_venta,2) : '—' }}</p>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm">
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Total Ventas</p>
            <p class="text-2xl font-black text-slate-900 dark:text-white mt-1">{{ $presentacion->detalles_ventas_count }}</p>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm">
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Total Compras</p>
            <p class="text-2xl font-black text-slate-900 dark:text-white mt-1">{{ $presentacion->detalles_compras_count }}</p>
        </div>
    </div>

    <!-- Info Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <!-- Datos Generales -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center space-x-2 mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
                <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                <span>Información General</span>
            </h3>
            <dl class="space-y-3">
                <div class="flex justify-between">
                    <dt class="text-xs font-medium text-slate-500 dark:text-slate-400">Nombre</dt>
                    <dd class="text-xs font-semibold text-slate-900 dark:text-white">{{ $presentacion->nombre }}</dd>
                </div>
                @if($presentacion->descripcion)
                <div class="flex justify-between">
                    <dt class="text-xs font-medium text-slate-500 dark:text-slate-400">Descripción</dt>
                    <dd class="text-xs text-slate-700 dark:text-slate-300 text-right max-w-xs">{{ $presentacion->descripcion }}</dd>
                </div>
                @endif
                <div class="flex justify-between">
                    <dt class="text-xs font-medium text-slate-500 dark:text-slate-400">Unidades</dt>
                    <dd class="text-xs font-bold text-slate-900 dark:text-white">x{{ $presentacion->unidades_por_presentacion }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-xs font-medium text-slate-500 dark:text-slate-400">Código de Barras</dt>
                    <dd class="text-xs font-mono text-slate-700 dark:text-slate-300">{{ $presentacion->codigo_barras ?? '—' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-xs font-medium text-slate-500 dark:text-slate-400">Orden</dt>
                    <dd class="text-xs text-slate-700 dark:text-slate-300">{{ $presentacion->orden ?? 0 }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-xs font-medium text-slate-500 dark:text-slate-400">Creado</dt>
                    <dd class="text-xs text-slate-500 dark:text-slate-400">{{ $presentacion->created_at->format('d/m/Y H:i') }}</dd>
                </div>
            </dl>
        </div>

        <!-- Precios -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center space-x-2 mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                <span>Precios y Márgenes</span>
            </h3>
            <dl class="space-y-3">
                <div class="flex justify-between">
                    <dt class="text-xs font-medium text-slate-500 dark:text-slate-400">Precio de Compra</dt>
                    <dd class="text-xs font-bold text-slate-900 dark:text-white">{{ $presentacion->precio_compra ? '$'.number_format($presentacion->precio_compra,2) : '—' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-xs font-medium text-slate-500 dark:text-slate-400">Precio de Venta</dt>
                    <dd class="text-xs font-bold text-emerald-600 dark:text-emerald-400">{{ $presentacion->precio_venta ? '$'.number_format($presentacion->precio_venta,2) : '—' }}</dd>
                </div>
                @if($presentacion->precio_compra && $presentacion->precio_venta)
                @php
                    $margen = $presentacion->precio_venta - $presentacion->precio_compra;
                    $pct = $presentacion->precio_compra > 0 ? (($margen / $presentacion->precio_compra) * 100) : 0;
                @endphp
                <div class="flex justify-between border-t border-slate-100 dark:border-slate-800 pt-3">
                    <dt class="text-xs font-medium text-slate-500 dark:text-slate-400">Margen Bruto</dt>
                    <dd class="text-xs font-bold {{ $margen >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600' }}">
                        ${{ number_format($margen, 2) }} ({{ number_format($pct, 1) }}%)
                    </dd>
                </div>
                @endif
            </dl>
        </div>
    </div>
</div>
@endsection
