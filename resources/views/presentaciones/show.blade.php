@extends('layouts.app')

@section('title', $presentacion->nombre . ' - Ficha de Presentación - FarmaBien')

@section('content')
<div class="space-y-5">
    <!-- Breadcrumb -->
    <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('presentaciones.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Presentaciones</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-800 dark:text-slate-200 font-semibold truncate">{{ $presentacion->nombre }}</span>
    </nav>

    <!-- Header & Quick Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <div class="flex items-center space-x-3">
                <h1 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <span>{{ $presentacion->nombre }}</span>
                </h1>
                @if($presentacion->es_unidad_base)
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black bg-indigo-100 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                    UNIDAD BASE
                </span>
                @endif
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold {{ $presentacion->activo ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-700' }}">
                    {{ $presentacion->activo ? 'Activa' : 'Inactiva' }}
                </span>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Medicamento: <a href="{{ route('productos.show', $presentacion->producto) }}" class="font-bold text-emerald-600 dark:text-emerald-400 hover:underline">{{ $presentacion->producto->nombre }}</a>
                @if($presentacion->producto->laboratorio)
                · <span class="text-slate-500 dark:text-slate-400">{{ $presentacion->producto->laboratorio->nombre }}</span>
                @endif
            </p>
        </div>

        <div class="flex items-center space-x-2 shrink-0">
            <a href="{{ route('presentaciones.index') }}"
               class="px-3.5 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-xl transition flex items-center space-x-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Volver a la Lista</span>
            </a>

            @can('editar productos')
            <a href="{{ route('presentaciones.edit', $presentacion) }}"
               class="inline-flex items-center space-x-1.5 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-semibold rounded-xl shadow-xs transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Editar Presentación</span>
            </a>
            @endcan

            @can('eliminar productos')
            <form action="{{ route('presentaciones.destroy', $presentacion) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar esta presentación?');" class="inline m-0 p-0">
                @csrf
                @method('DELETE')
                <button type="submit" 
                        class="inline-flex items-center space-x-1.5 px-3.5 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-xl shadow-xs transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    <span>Eliminar</span>
                </button>
            </form>
            @endcan
        </div>
    </div>

    <!-- Identificación Visual de Código de Barras y Presentación -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs p-5"
         x-data="{ copied: false }">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-center">
            
            <div class="lg:col-span-6 space-y-3">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                    <span>Código de Barra Específico de la Presentación</span>
                </span>

                <div class="flex items-center space-x-3 bg-slate-50 dark:bg-slate-800/80 p-3.5 rounded-xl border border-slate-200 dark:border-slate-700">
                    <div class="h-8 flex items-center space-x-0.5 shrink-0 px-1 bg-white dark:bg-slate-900 rounded border border-slate-200 dark:border-slate-700 select-none">
                        <div class="w-1 h-6 bg-slate-900 dark:bg-slate-100"></div>
                        <div class="w-0.5 h-6 bg-slate-900 dark:bg-slate-100"></div>
                        <div class="w-1.5 h-6 bg-slate-900 dark:bg-slate-100"></div>
                        <div class="w-1 h-6 bg-slate-900 dark:bg-slate-100"></div>
                        <div class="w-2 h-6 bg-slate-900 dark:bg-slate-100"></div>
                    </div>
                    <div class="min-w-0 flex-1">
                        <span class="font-mono text-base sm:text-lg font-black tracking-widest text-slate-900 dark:text-white block truncate">
                            {{ $presentacion->codigo_barras ?: ($presentacion->producto->codigo_barra ?: 'SIN CÓDIGO') }}
                        </span>
                        <span class="text-[10px] text-slate-400 font-mono">Presentación ID: #{{ str_pad($presentacion->id, 5, '0', STR_PAD_LEFT) }}</span>
                    </div>
                    @if($presentacion->codigo_barras || $presentacion->producto->codigo_barra)
                    <button type="button" 
                            @click="navigator.clipboard.writeText('{{ $presentacion->codigo_barras ?: $presentacion->producto->codigo_barra }}'); copied = true; setTimeout(() => copied = false, 2000)" 
                            class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-600 hover:bg-slate-100 transition shadow-2xs">
                        <span x-show="!copied">📋 Copiar</span>
                        <span x-show="copied" class="text-emerald-600 dark:text-emerald-400">✓ Listo</span>
                    </button>
                    @endif
                </div>
            </div>

            <!-- Resumen de Equivalencia -->
            <div class="lg:col-span-6 bg-slate-50 dark:bg-slate-800/60 p-4 rounded-xl border border-slate-200 dark:border-slate-700 space-y-2">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Factor de Fraccionamiento en POS</span>
                <p class="text-xs text-slate-700 dark:text-slate-300 leading-relaxed">
                    Al vender <strong>1 {{ strtolower($presentacion->nombre) }}</strong> en el Punto de Venta, el sistema descuenta automáticamente <strong>{{ $presentacion->unidades_por_presentacion }} unidad(es) base</strong> del inventario FEFO del medicamento.
                </p>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs">
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Unidades / Presentación</p>
            <p class="text-2xl font-black text-slate-900 dark:text-white mt-1">x{{ $presentacion->unidades_por_presentacion }}</p>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs">
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Precio de Venta</p>
            <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1">{{ $presentacion->precio_venta ? 'S/ '.number_format($presentacion->precio_venta,2) : '—' }}</p>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs">
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Ventas Registradas</p>
            <p class="text-2xl font-black text-slate-900 dark:text-white mt-1">{{ $presentacion->detalles_ventas_count ?? 0 }}</p>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs">
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Compras Registradas</p>
            <p class="text-2xl font-black text-slate-900 dark:text-white mt-1">{{ $presentacion->detalles_compras_count ?? 0 }}</p>
        </div>
    </div>

    <!-- Info Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <!-- Datos Generales -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-xs">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center space-x-2 mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
                <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                <span>Información General</span>
            </h3>
            <dl class="space-y-3">
                <div class="flex justify-between">
                    <dt class="text-xs font-medium text-slate-500 dark:text-slate-400">Nombre Comercial</dt>
                    <dd class="text-xs font-bold text-slate-900 dark:text-white">{{ $presentacion->nombre }}</dd>
                </div>
                @if($presentacion->descripcion)
                <div class="flex justify-between">
                    <dt class="text-xs font-medium text-slate-500 dark:text-slate-400">Descripción</dt>
                    <dd class="text-xs text-slate-700 dark:text-slate-300 text-right max-w-xs">{{ $presentacion->descripcion }}</dd>
                </div>
                @endif
                <div class="flex justify-between">
                    <dt class="text-xs font-medium text-slate-500 dark:text-slate-400">Unidades Contenidas</dt>
                    <dd class="text-xs font-bold text-slate-900 dark:text-white">x{{ $presentacion->unidades_por_presentacion }} unidades</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-xs font-medium text-slate-500 dark:text-slate-400">Código de Barras</dt>
                    <dd class="text-xs font-mono font-bold text-slate-700 dark:text-slate-300">{{ $presentacion->codigo_barras ?? '—' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-xs font-medium text-slate-500 dark:text-slate-400">Orden de Visualización</dt>
                    <dd class="text-xs text-slate-700 dark:text-slate-300">{{ $presentacion->orden ?? 0 }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-xs font-medium text-slate-500 dark:text-slate-400">Fecha Creación</dt>
                    <dd class="text-xs text-slate-500 dark:text-slate-400">{{ $presentacion->created_at->format('d/m/Y H:i') }}</dd>
                </div>
            </dl>
        </div>

        <!-- Precios -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-xs">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center space-x-2 mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span>Precios y Márgenes de Comercialización</span>
            </h3>
            <dl class="space-y-3">
                <div class="flex justify-between">
                    <dt class="text-xs font-medium text-slate-500 dark:text-slate-400">Precio de Compra</dt>
                    <dd class="text-xs font-bold text-slate-900 dark:text-white">{{ $presentacion->precio_compra ? 'S/ '.number_format($presentacion->precio_compra,2) : '—' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-xs font-medium text-slate-500 dark:text-slate-400">Precio de Venta</dt>
                    <dd class="text-xs font-bold text-emerald-600 dark:text-emerald-400">{{ $presentacion->precio_venta ? 'S/ '.number_format($presentacion->precio_venta,2) : '—' }}</dd>
                </div>
                @if($presentacion->precio_compra && $presentacion->precio_venta)
                @php
                    $margen = $presentacion->precio_venta - $presentacion->precio_compra;
                    $pct = $presentacion->precio_compra > 0 ? (($margen / $presentacion->precio_compra) * 100) : 0;
                @endphp
                <div class="flex justify-between border-t border-slate-100 dark:border-slate-800 pt-3">
                    <dt class="text-xs font-medium text-slate-500 dark:text-slate-400">Margen Bruto</dt>
                    <dd class="text-xs font-bold {{ $margen >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600' }}">
                        S/ {{ number_format($margen, 2) }} ({{ number_format($pct, 1) }}%)
                    </dd>
                </div>
                @endif
            </dl>
        </div>
    </div>
</div>
@endsection
