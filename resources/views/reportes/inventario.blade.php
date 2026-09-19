@extends('layouts.app')

@section('title', 'Reporte de Inventario, Valorización y Caducidad - FarmaBien')

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
                <span class="text-slate-800 dark:text-slate-200 font-semibold">Inventario & Caducidad</span>
            </nav>
            <div class="flex items-center space-x-2">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-300">
                    Auditoría de Stock
                </span>
                <span class="text-xs text-slate-400">&bull;</span>
                <span class="text-xs text-slate-500 dark:text-slate-400">Control PEPS / FEFO</span>
            </div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white mt-1">Reporte de Inventario, Valorización y Caducidad</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Valoración a costo y venta proyectada, semáforo de vencimientos y auditoría por lote.
            </p>
        </div>

        <div class="flex items-center space-x-2 flex-wrap">
            <!-- Botón Imprimir -->
            <button type="button" 
                    onclick="window.print()" 
                    class="inline-flex items-center space-x-1.5 px-3 py-2 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold rounded-lg shadow-2xs transition">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Imprimir</span>
            </button>

            <!-- Botón Exportar CSV -->
            <a href="{{ route('reportes.inventario', array_merge(request()->query(), ['export' => 'csv'])) }}" 
               class="inline-flex items-center space-x-1.5 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Exportar CSV / Excel</span>
            </a>

            @can('ajustar inventario')
            <a href="{{ route('inventario.ajustar') }}" 
               class="inline-flex items-center space-x-1.5 px-3 py-2 bg-slate-900 hover:bg-slate-800 dark:bg-slate-700 dark:hover:bg-slate-600 text-white text-xs font-semibold rounded-lg shadow-xs transition">
                <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Ajustar Stock</span>
            </a>
            @endcan
        </div>
    </div>

    <!-- Print Header -->
    <div class="hidden print:block border-b border-slate-300 pb-4 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">FarmaBien - Farmacia & Droguería</h1>
                <h2 class="text-base font-semibold text-slate-700 mt-1">Informe Oficial de Valorización de Inventario y Caducidad</h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Generado el {{ now()->format('d/m/Y H:i') }} &bull; Criterio de Valoración: Costo de Adquisición por Lote (PEPS)
                </p>
            </div>
            <div class="text-right">
                <span class="text-xs text-slate-500">Valor Total Almacén:</span>
                <p class="text-xl font-bold text-slate-900">${{ number_format($totalValorCosto, 2) }}</p>
                <p class="text-xs text-slate-500">{{ number_format($totalUnidadesStock) }} unidades en existencia</p>
            </div>
        </div>
    </div>

    <!-- 4 KPI Cards: Valoración y Existencias -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Valor Costo Almacén -->
        <div class="bg-white dark:bg-slate-900 rounded-xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Valor Costo Total</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-bold text-slate-900 dark:text-white font-mono">
                    ${{ number_format($totalValorCosto, 2) }}
                </div>
                <div class="flex items-center justify-between mt-2 text-xs">
                    <span class="text-slate-500 dark:text-slate-400">{{ number_format($totalUnidadesStock) }} unidades en stock</span>
                    <span class="text-slate-400">Inversión</span>
                </div>
            </div>
        </div>

        <!-- Valor Venta Proyectado -->
        <div class="bg-white dark:bg-slate-900 rounded-xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Valor Venta Proyectado</span>
                <div class="w-8 h-8 rounded-lg bg-teal-50 dark:bg-teal-950/50 text-teal-600 dark:text-teal-400 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-bold text-slate-900 dark:text-white font-mono">
                    ${{ number_format($totalValorVenta, 2) }}
                </div>
                <div class="flex items-center justify-between mt-2 text-xs">
                    <span class="text-emerald-600 dark:text-emerald-400 font-semibold">+{{ $margenProyectado }}% margen estimado</span>
                    <span class="text-slate-400">PVP</span>
                </div>
            </div>
        </div>

        <!-- Lotes Auditados -->
        <div class="bg-white dark:bg-slate-900 rounded-xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Lotes en Consulta</span>
                <div class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-bold text-slate-900 dark:text-white">
                    {{ number_format($lotes->total()) }}
                </div>
                <div class="flex items-center justify-between mt-2 text-xs">
                    <span class="text-slate-500 dark:text-slate-400">Lotes activos</span>
                    <a href="{{ route('inventario.lotes') }}" class="text-slate-600 dark:text-slate-400 font-medium hover:underline">Auditar &rarr;</a>
                </div>
            </div>
        </div>

        <!-- Stock Crítico -->
        <div class="bg-white dark:bg-slate-900 rounded-xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm {{ $totalProductosBajoStock > 0 ? 'border-amber-300 dark:border-amber-700/80' : '' }}">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Stock Crítico</span>
                <div class="w-8 h-8 rounded-lg {{ $totalProductosBajoStock > 0 ? 'bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400' : 'bg-slate-100 dark:bg-slate-800 text-slate-400' }} flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
            </div>
            <div class="mt-3">
                <div class="flex items-baseline space-x-1.5">
                    <span class="text-2xl font-bold {{ $totalProductosBajoStock > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-slate-900 dark:text-white' }}">
                        {{ $totalProductosBajoStock }}
                    </span>
                    <span class="text-xs text-slate-500">productos</span>
                </div>
                <div class="flex items-center justify-between mt-2 text-xs">
                    <span class="text-slate-500 dark:text-slate-400">Bajo mínimo</span>
                    <a href="{{ route('inventario.alertas') }}" class="font-medium text-amber-600 dark:text-amber-400 hover:underline">Ver alertas &rarr;</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Semáforo Interactivo de Caducidad (Quick Filters) -->
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4 shadow-sm print:hidden">
        <div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100 dark:border-slate-800">
            <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center space-x-2">
                <span>🚦 Semáforo de Caducidad de Lotes</span>
            </h3>
            <span class="text-[11px] text-slate-400">Haz clic en un nivel para filtrar rápidamente:</span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2.5">
            <!-- Vencidos -->
            <a href="{{ route('reportes.inventario', array_merge(request()->except(['page', 'estado_vencimiento']), ['estado_vencimiento' => 'vencidos'])) }}" 
               class="p-3 rounded-lg border transition-all text-left {{ $estadoVencimiento === 'vencidos' ? 'bg-rose-100 dark:bg-rose-950/80 border-rose-500 text-rose-900 dark:text-rose-100 ring-2 ring-rose-500/20' : 'bg-rose-50/70 dark:bg-rose-950/30 border-rose-200 dark:border-rose-900/60 hover:border-rose-400 text-rose-800 dark:text-rose-300' }}">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold">VENCIDOS</span>
                    <span class="w-2 h-2 rounded-full bg-rose-600 animate-pulse"></span>
                </div>
                <div class="text-xl font-extrabold mt-1 font-mono">{{ $semVencidos }}</div>
                <div class="text-[10px] text-rose-600 dark:text-rose-400 mt-0.5 font-medium">Baja inmediata</div>
            </a>

            <!-- Crítico <= 30 días -->
            <a href="{{ route('reportes.inventario', array_merge(request()->except(['page', 'estado_vencimiento']), ['estado_vencimiento' => 'critico_30'])) }}" 
               class="p-3 rounded-lg border transition-all text-left {{ $estadoVencimiento === 'critico_30' ? 'bg-amber-100 dark:bg-amber-950/80 border-amber-500 text-amber-900 dark:text-amber-100 ring-2 ring-amber-500/20' : 'bg-amber-50/70 dark:bg-amber-950/30 border-amber-200 dark:border-amber-900/60 hover:border-amber-400 text-amber-800 dark:text-amber-300' }}">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold">&le; 30 DÍAS</span>
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                </div>
                <div class="text-xl font-extrabold mt-1 font-mono">{{ $semCritico30 }}</div>
                <div class="text-[10px] text-amber-600 dark:text-amber-400 mt-0.5 font-medium">Rotación urgente</div>
            </a>

            <!-- Alerta 31-60 días -->
            <a href="{{ route('reportes.inventario', array_merge(request()->except(['page', 'estado_vencimiento']), ['estado_vencimiento' => 'alerta_60'])) }}" 
               class="p-3 rounded-lg border transition-all text-left {{ $estadoVencimiento === 'alerta_60' ? 'bg-yellow-100 dark:bg-yellow-950/80 border-yellow-500 text-yellow-900 dark:text-yellow-100 ring-2 ring-yellow-500/20' : 'bg-yellow-50/70 dark:bg-yellow-950/30 border-yellow-200 dark:border-yellow-900/60 hover:border-yellow-400 text-yellow-800 dark:text-yellow-300' }}">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold">31 - 60 DÍAS</span>
                    <span class="w-2 h-2 rounded-full bg-yellow-400"></span>
                </div>
                <div class="text-xl font-extrabold mt-1 font-mono">{{ $semAlerta60 }}</div>
                <div class="text-[10px] text-yellow-700 dark:text-yellow-400 mt-0.5 font-medium">Alerta media</div>
            </a>

            <!-- Preventivo 61-90 días -->
            <a href="{{ route('reportes.inventario', array_merge(request()->except(['page', 'estado_vencimiento']), ['estado_vencimiento' => 'preventivo_90'])) }}" 
               class="p-3 rounded-lg border transition-all text-left {{ $estadoVencimiento === 'preventivo_90' ? 'bg-emerald-100 dark:bg-emerald-950/80 border-emerald-500 text-emerald-900 dark:text-emerald-100 ring-2 ring-emerald-500/20' : 'bg-emerald-50/70 dark:bg-emerald-950/30 border-emerald-200 dark:border-emerald-900/60 hover:border-emerald-400 text-emerald-800 dark:text-emerald-300' }}">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold">61 - 90 DÍAS</span>
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                </div>
                <div class="text-xl font-extrabold mt-1 font-mono">{{ $semPreventivo90 }}</div>
                <div class="text-[10px] text-emerald-600 dark:text-emerald-400 mt-0.5 font-medium">Preventivo</div>
            </a>

            <!-- Vigentes > 90 días -->
            <a href="{{ route('reportes.inventario', array_merge(request()->except(['page', 'estado_vencimiento']), ['estado_vencimiento' => 'vigentes'])) }}" 
               class="p-3 rounded-lg border transition-all text-left {{ $estadoVencimiento === 'vigentes' ? 'bg-slate-200 dark:bg-slate-800 border-slate-500 text-slate-900 dark:text-white ring-2 ring-slate-400/20' : 'bg-slate-50 dark:bg-slate-800/60 border-slate-200 dark:border-slate-700 hover:border-slate-400 text-slate-700 dark:text-slate-300' }}">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold">&gt; 90 DÍAS</span>
                    <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                </div>
                <div class="text-xl font-extrabold mt-1 font-mono">{{ $semVigentes }}</div>
                <div class="text-[10px] text-slate-500 mt-0.5 font-medium">Vigencia óptima</div>
            </a>
        </div>
    </div>

    <!-- Filtros de Búsqueda Avanzada -->
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4 shadow-sm print:hidden">
        <form id="form-filtro-inventario" method="GET" action="{{ route('reportes.inventario') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 items-end">
            <!-- Buscar -->
            <div class="lg:col-span-2">
                <label for="inv_buscar" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                    Buscar Medicamento / Lote / Código
                </label>
                <div class="relative">
                    <input type="text" 
                           id="inv_buscar" 
                           name="buscar" 
                           value="{{ $buscar }}" 
                           placeholder="Nombre, código, lote o activo..." 
                           class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white pl-8 focus:ring-emerald-500 focus:border-emerald-500">
                    <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </div>

            <!-- Categoría -->
            <div>
                <label for="inv_categoria_id" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                    Categoría
                </label>
                <select id="inv_categoria_id" 
                        name="categoria_id" 
                        class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="">Todas las categorías</option>
                    @foreach($categorias as $cat)
                    <option value="{{ $cat->id }}" {{ (string)$categoriaId === (string)$cat->id ? 'selected' : '' }}>
                        {{ $cat->nombre }}
                    </option>
                    @endforeach
                </select>
            </div>

            <!-- Laboratorio -->
            <div>
                <label for="inv_laboratorio_id" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                    Laboratorio
                </label>
                <select id="inv_laboratorio_id" 
                        name="laboratorio_id" 
                        class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="">Todos los laboratorios</option>
                    @foreach($laboratorios as $lab)
                    <option value="{{ $lab->id }}" {{ (string)$laboratorioId === (string)$lab->id ? 'selected' : '' }}>
                        {{ $lab->nombre }}
                    </option>
                    @endforeach
                </select>
            </div>

            <!-- Estado de Caducidad -->
            <div>
                <label for="inv_estado_vencimiento" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                    Caducidad
                </label>
                <select id="inv_estado_vencimiento" 
                        name="estado_vencimiento" 
                        class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="todos" {{ $estadoVencimiento === 'todos' ? 'selected' : '' }}>Todos los lotes</option>
                    <option value="vencidos" {{ $estadoVencimiento === 'vencidos' ? 'selected' : '' }}>Vencidos</option>
                    <option value="critico_30" {{ $estadoVencimiento === 'critico_30' ? 'selected' : '' }}>Crítico (&le;30 días)</option>
                    <option value="alerta_60" {{ $estadoVencimiento === 'alerta_60' ? 'selected' : '' }}>Alerta (31-60 días)</option>
                    <option value="preventivo_90" {{ $estadoVencimiento === 'preventivo_90' ? 'selected' : '' }}>Preventivo (61-90 días)</option>
                    <option value="vigentes" {{ $estadoVencimiento === 'vigentes' ? 'selected' : '' }}>Vigentes (&gt;90 días)</option>
                </select>
            </div>

            <!-- Botones -->
            <div class="flex items-center space-x-2">
                <button type="submit" 
                        class="flex-1 px-3 py-2 bg-slate-900 hover:bg-slate-800 dark:bg-slate-700 dark:hover:bg-slate-600 text-white text-xs font-semibold rounded-lg transition text-center shadow-xs">
                    Filtrar
                </button>
                <a href="{{ route('reportes.inventario') }}" 
                   title="Restablecer filtros"
                   class="px-3 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 text-xs font-semibold rounded-lg transition">
                    Limpiar
                </a>
            </div>
        </form>
    </div>

    <!-- Tabla Detallada de Inventario Valorizado -->
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Auditoría Detallada de Lotes y Valorización</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">Mostrando {{ $lotes->firstItem() ?? 0 }} a {{ $lotes->lastItem() ?? 0 }} de {{ $lotes->total() }} lotes registrados</p>
            </div>
            <div class="text-right">
                <span class="text-xs text-slate-400">Valor Filtrado:</span>
                <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 font-mono ml-1">
                    Costo: ${{ number_format($totalValorCosto, 2) }}
                </span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-600 dark:text-slate-400 font-semibold uppercase tracking-wider text-[10px]">
                    <tr>
                        <th class="py-3 px-4">Lote</th>
                        <th class="py-3 px-4">Medicamento / Detalle</th>
                        <th class="py-3 px-4">Categoría & Lab</th>
                        <th class="py-3 px-4 text-center">Vencimiento & Caducidad</th>
                        <th class="py-3 px-4 text-right">Stock</th>
                        <th class="py-3 px-4 text-right">P. Compra</th>
                        <th class="py-3 px-4 text-right">Valor Costo</th>
                        <th class="py-3 px-4 text-right">PVP Unit.</th>
                        <th class="py-3 px-4 text-right">Valor Venta</th>
                        <th class="py-3 px-4 text-center print:hidden">Kardex</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @php
                        $today = now();
                    @endphp
                    @forelse($lotes as $l)
                    @php
                        $dias = (int) $today->diffInDays($l->fecha_vencimiento, false);
                        $valorCosto = round($l->stock_actual * (float)$l->precio_compra, 2);
                        $valorVenta = round($l->stock_actual * (float)($l->producto->precio_venta ?? 0), 2);
                    @endphp
                    <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition">
                        <!-- Lote -->
                        <td class="py-3 px-4 font-mono font-semibold text-slate-800 dark:text-slate-200">
                            {{ $l->numero_lote }}
                        </td>

                        <!-- Medicamento -->
                        <td class="py-3 px-4">
                            <span class="font-bold text-slate-900 dark:text-white block">
                                {{ $l->producto->nombre ?? 'N/A' }}
                            </span>
                            <span class="text-[11px] text-slate-400 block">
                                {{ $l->producto->principio_activo ?? '' }} {{ $l->producto->concentracion ? '· ' . $l->producto->concentracion : '' }}
                            </span>
                        </td>

                        <!-- Categoria & Lab -->
                        <td class="py-3 px-4 text-slate-600 dark:text-slate-400">
                            <span class="block text-slate-700 dark:text-slate-300 font-medium">{{ $l->producto->categoria->nombre ?? 'Sin cat.' }}</span>
                            <span class="text-[11px] text-slate-400 block">{{ $l->producto->laboratorio->nombre ?? 'Sin lab.' }}</span>
                        </td>

                        <!-- Vencimiento & Semáforo -->
                        <td class="py-3 px-4 text-center">
                            <span class="text-xs font-semibold block text-slate-800 dark:text-slate-200">
                                {{ $l->fecha_vencimiento ? $l->fecha_vencimiento->format('d/m/Y') : 'N/A' }}
                            </span>
                            @if($dias < 0)
                                <span class="inline-block mt-0.5 px-2 py-0.5 rounded text-[10px] font-extrabold bg-rose-100 dark:bg-rose-950 text-rose-800 dark:text-rose-300">
                                    VENCIDO (hace {{ abs($dias) }}d)
                                </span>
                            @elseif($dias <= 30)
                                <span class="inline-block mt-0.5 px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 dark:bg-amber-950 text-amber-800 dark:text-amber-300">
                                    Crítico ({{ $dias }}d)
                                </span>
                            @elseif($dias <= 60)
                                <span class="inline-block mt-0.5 px-2 py-0.5 rounded text-[10px] font-semibold bg-yellow-100 dark:bg-yellow-950 text-yellow-800 dark:text-yellow-300">
                                    Alerta ({{ $dias }}d)
                                </span>
                            @elseif($dias <= 90)
                                <span class="inline-block mt-0.5 px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-100 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-300">
                                    Preventivo ({{ $dias }}d)
                                </span>
                            @else
                                <span class="inline-block mt-0.5 px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">
                                    Vigente ({{ $dias }}d)
                                </span>
                            @endif
                        </td>

                        <!-- Stock -->
                        <td class="py-3 px-4 text-right">
                            <span class="font-mono font-bold {{ $l->stock_actual === 0 ? 'text-rose-600' : 'text-slate-900 dark:text-white' }}">
                                {{ number_format($l->stock_actual) }}
                            </span>
                            <span class="text-[10px] text-slate-400 block">unid.</span>
                        </td>

                        <!-- P. Compra -->
                        <td class="py-3 px-4 text-right font-mono text-slate-600 dark:text-slate-400">
                            ${{ number_format($l->precio_compra, 2) }}
                        </td>

                        <!-- Valor Costo -->
                        <td class="py-3 px-4 text-right font-mono font-bold text-slate-900 dark:text-white">
                            ${{ number_format($valorCosto, 2) }}
                        </td>

                        <!-- PVP Unitario -->
                        <td class="py-3 px-4 text-right font-mono text-slate-600 dark:text-slate-400">
                            ${{ number_format($l->producto->precio_venta ?? 0, 2) }}
                        </td>

                        <!-- Valor Venta -->
                        <td class="py-3 px-4 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400">
                            ${{ number_format($valorVenta, 2) }}
                        </td>

                        <!-- Acciones -->
                        <td class="py-3 px-4 text-center print:hidden">
                            <a href="{{ route('inventario.kardex-producto', $l->producto_id) }}" 
                               title="Ver Kardex de movimientos"
                               class="inline-flex items-center space-x-1 text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                                <span>Kardex</span>
                                <span>&rarr;</span>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="py-8 text-center text-slate-400 text-xs">
                            No se encontraron lotes registrados con los filtros aplicados.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        @if($lotes->hasPages())
        <div class="px-5 py-3 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900 print:hidden">
            {{ $lotes->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
