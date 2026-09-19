@extends('layouts.app')

@section('title', 'Ventas & Facturación')

@section('content')
<div class="space-y-4" x-data="{
    modalAnular: false,
    ventaId: null,
    comprobanteInfo: '',
    motivoAnulacion: '',
    abrirModalAnular(id, info) {
        this.ventaId = id;
        this.comprobanteInfo = info;
        this.motivoAnulacion = '';
        this.modalAnular = true;
    }
}">

    <!-- Breadcrumb & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-1 border-b border-slate-300/80 dark:border-slate-800">
        <div>
            <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
                <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="text-slate-800 dark:text-slate-200 font-semibold">Punto de Venta & Facturación</span>
            </nav>
            <h1 class="text-lg sm:text-xl font-bold text-slate-900 dark:text-white flex items-center space-x-2">
                <span>Historial de Ventas & Comprobantes</span>
            </h1>
        </div>

        <div class="flex items-center space-x-2">
            @can('realizar ventas')
            <a href="{{ route('ventas.create') }}" 
               class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-sm transition flex items-center space-x-1.5 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Nueva Venta (POS)</span>
            </a>
            @endcan
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
    <div class="p-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-300 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 text-xs flex items-center justify-between">
        <div class="flex items-center space-x-2">
            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('success') }}</span>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-slate-400 hover:text-slate-600 text-sm font-bold">&times;</button>
    </div>
    @endif

    @if(session('error'))
    <div class="p-3 rounded-xl bg-rose-50 dark:bg-rose-950/60 border border-rose-300 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-xs flex items-center justify-between">
        <div class="flex items-center space-x-2">
            <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('error') }}</span>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-slate-400 hover:text-slate-600 text-sm font-bold">&times;</button>
    </div>
    @endif

    <!-- 4 KPI Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <!-- Total Ventas -->
        <div class="bg-white dark:bg-slate-900 rounded-xl p-3.5 border border-slate-300 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Total Operaciones</p>
                <p class="text-lg font-bold text-slate-900 dark:text-white mt-0.5">{{ number_format($stats['total'] ?? 0) }}</p>
            </div>
            <div class="w-9 h-9 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
        </div>

        <!-- Completadas -->
        <div class="bg-white dark:bg-slate-900 rounded-xl p-3.5 border border-slate-300 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Ventas Exitosas</p>
                <p class="text-lg font-bold text-emerald-600 dark:text-emerald-400 mt-0.5">{{ number_format($stats['completadas'] ?? 0) }}</p>
            </div>
            <div class="w-9 h-9 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>
        </div>

        <!-- Anuladas -->
        <div class="bg-white dark:bg-slate-900 rounded-xl p-3.5 border border-slate-300 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Operaciones Anuladas</p>
                <p class="text-lg font-bold text-rose-600 dark:text-rose-400 mt-0.5">{{ number_format($stats['anuladas'] ?? 0) }}</p>
            </div>
            <div class="w-9 h-9 rounded-lg bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
            </div>
        </div>

        <!-- Ingresos Totales -->
        <div class="bg-white dark:bg-slate-900 rounded-xl p-3.5 border border-slate-300 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Total Ingresos</p>
                <p class="text-lg font-bold text-emerald-600 dark:text-emerald-400 mt-0.5">${{ number_format($stats['ingresos'] ?? 0, 2) }}</p>
            </div>
            <div class="w-9 h-9 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-300 dark:border-slate-800 shadow-xs space-y-3">
        <form method="GET" action="{{ route('ventas.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-12 gap-2.5">
            <!-- Search Input -->
            <div class="relative md:col-span-4">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" 
                       name="buscar" 
                       value="{{ request('buscar') }}" 
                       placeholder="Buscar por N° comprobante o cliente..." 
                       class="w-full pl-9 pr-3 py-2 bg-slate-50 dark:bg-slate-800/90 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
            </div>

            <!-- Estado Filter -->
            <div class="md:col-span-2">
                <select name="estado" 
                        onchange="this.form.submit()" 
                        class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800/90 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                    <option value="">Estado: Todos</option>
                    <option value="completada" {{ request('estado') === 'completada' ? 'selected' : '' }}>Completadas</option>
                    <option value="anulada" {{ request('estado') === 'anulada' ? 'selected' : '' }}>Anuladas</option>
                </select>
            </div>

            <!-- Tipo Comprobante Filter -->
            <div class="md:col-span-2">
                <select name="tipo_comprobante" 
                        onchange="this.form.submit()" 
                        class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800/90 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                    <option value="">Tipo: Todos</option>
                    <option value="ticket" {{ request('tipo_comprobante') === 'ticket' ? 'selected' : '' }}>Ticket</option>
                    <option value="boleta" {{ request('tipo_comprobante') === 'boleta' ? 'selected' : '' }}>Boleta</option>
                    <option value="factura" {{ request('tipo_comprobante') === 'factura' ? 'selected' : '' }}>Factura</option>
                </select>
            </div>

            <!-- Fecha Desde -->
            <div class="md:col-span-2">
                <input type="date" 
                       name="fecha_desde" 
                       value="{{ request('fecha_desde') }}" 
                       class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800/90 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition"
                       placeholder="Desde">
            </div>

            <!-- Fecha Hasta & Botones -->
            <div class="md:col-span-2 flex items-center space-x-2">
                <input type="date" 
                       name="fecha_hasta" 
                       value="{{ request('fecha_hasta') }}" 
                       class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800/90 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition"
                       placeholder="Hasta">
                <button type="submit" 
                        class="px-3 py-2 bg-slate-800 hover:bg-slate-900 dark:bg-slate-700 dark:hover:bg-slate-600 text-white rounded-xl text-xs font-semibold shadow-2xs transition cursor-pointer">
                    Filtrar
                </button>
                @if(request()->hasAny(['buscar', 'estado', 'tipo_comprobante', 'fecha_desde', 'fecha_hasta']))
                <a href="{{ route('ventas.index') }}" 
                   title="Limpiar filtros"
                   class="px-2.5 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-xl text-xs font-semibold border border-slate-300 dark:border-slate-700 transition">
                    ✕
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Ventas Table -->
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-300 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 text-[11px] font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">
                        <th class="py-3 px-4">Comprobante</th>
                        <th class="py-3 px-4">Cliente</th>
                        <th class="py-3 px-4">Fecha & Hora</th>
                        <th class="py-3 px-4 text-center">Ítems</th>
                        <th class="py-3 px-4 text-right">Total</th>
                        <th class="py-3 px-4 text-center">Método Pago</th>
                        <th class="py-3 px-4 text-center">Estado</th>
                        <th class="py-3 px-4">Cajero</th>
                        <th class="py-3 px-4 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    @forelse($ventas as $venta)
                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition {{ $venta->estado === 'anulada' ? 'opacity-60 bg-slate-50/30 dark:bg-slate-950/30' : '' }}">
                        <!-- Comprobante -->
                        <td class="py-3 px-4">
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('ventas.show', $venta) }}" class="font-bold text-emerald-600 dark:text-emerald-400 hover:underline">
                                    #{{ str_pad($venta->id, 5, '0', STR_PAD_LEFT) }}
                                </a>
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase {{ $venta->tipo_comprobante === 'factura' ? 'bg-indigo-100 dark:bg-indigo-950/60 text-indigo-800 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800' : ($venta->tipo_comprobante === 'boleta' ? 'bg-cyan-100 dark:bg-cyan-950/60 text-cyan-800 dark:text-cyan-300 border border-cyan-200 dark:border-cyan-800' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700') }}">
                                    {{ $venta->tipo_comprobante }}
                                </span>
                            </div>
                            <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 font-mono">
                                {{ $venta->numero_comprobante ? ($venta->serie ? $venta->serie . '-' : '') . $venta->numero_comprobante : 'Ticket Venta' }}
                            </div>
                        </td>

                        <!-- Cliente -->
                        <td class="py-3 px-4">
                            <div class="font-semibold text-slate-900 dark:text-white">
                                {{ $venta->cliente->nombre ?? 'Público General (Venta Libre)' }}
                            </div>
                            @if($venta->cliente && $venta->cliente->documento)
                                <div class="text-[10px] text-slate-400 dark:text-slate-500 font-mono">
                                    Doc: {{ $venta->cliente->documento }}
                                </div>
                            @endif
                        </td>

                        <!-- Fecha -->
                        <td class="py-3 px-4 text-slate-700 dark:text-slate-300">
                            <div>{{ $venta->fecha ? $venta->fecha->format('d/m/Y') : '-' }}</div>
                            <div class="text-[10px] text-slate-400">{{ $venta->fecha ? $venta->fecha->format('H:i') : '' }}</div>
                        </td>

                        <!-- Ítems -->
                        <td class="py-3 px-4 text-center">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                {{ $venta->detalles_count }} prod.
                            </span>
                        </td>

                        <!-- Total -->
                        <td class="py-3 px-4 text-right font-bold text-slate-900 dark:text-white">
                            ${{ number_format($venta->total, 2) }}
                            @if($venta->descuento > 0)
                                <div class="text-[10px] text-emerald-600 dark:text-emerald-400 font-normal">
                                    Desc: -${{ number_format($venta->descuento, 2) }}
                                </div>
                            @endif
                        </td>

                        <!-- Método Pago -->
                        <td class="py-3 px-4 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                {{ ucfirst($venta->metodo_pago ?? 'Efectivo') }}
                            </span>
                        </td>

                        <!-- Estado -->
                        <td class="py-3 px-4 text-center">
                            @if($venta->estado === 'completada')
                                @if($venta->fueModificada())
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                        Modificada
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                        Completada
                                    </span>
                                @endif
                            @elseif($venta->estado === 'anulada')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 dark:bg-rose-950/60 text-rose-800 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                    Anulada
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-800">
                                    {{ ucfirst($venta->estado) }}
                                </span>
                            @endif
                        </td>

                        <!-- Cajero Responsable -->
                        <td class="py-3 px-4 text-slate-600 dark:text-slate-400">
                            {{ $venta->usuario->name ?? 'Cajero' }}
                        </td>

                        <!-- Acciones Cuarteto -->
                        <td class="py-3 px-4 text-center whitespace-nowrap">
                            <div class="inline-flex items-center justify-center space-x-1">
                                <!-- Ver Ficha -->
                                <a href="{{ route('ventas.show', $venta) }}" 
                                   title="Ver Detalle de Venta"
                                   class="w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 inline-flex items-center justify-center transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>

                                <!-- Imprimir Ticket -->
                                <a href="{{ route('ventas.ticket', $venta) }}" 
                                   target="_blank"
                                   title="Imprimir Ticket Térmico"
                                   class="w-7 h-7 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 hover:bg-indigo-100 dark:hover:bg-indigo-900/60 text-indigo-600 dark:text-indigo-300 inline-flex items-center justify-center transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                </a>

                                <!-- Editar -->
                                @if($venta->puedeModificarse())
                                    @can('realizar ventas')
                                    <a href="{{ route('ventas.edit', $venta) }}" 
                                       title="Editar Venta"
                                       class="w-7 h-7 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 text-emerald-600 dark:text-emerald-400 inline-flex items-center justify-center transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </a>
                                    @endcan
                                @endif

                                <!-- Anular -->
                                @if($venta->puedeAnularse())
                                    @can('anular ventas')
                                    <button type="button" 
                                            @click="abrirModalAnular({{ $venta->id }}, '{{ $venta->numero_comprobante ?? '#' . $venta->id }}')" 
                                            title="Anular Venta"
                                            class="w-7 h-7 rounded-lg bg-rose-50 dark:bg-rose-950/60 hover:bg-rose-100 dark:hover:bg-rose-900/60 text-rose-600 dark:text-rose-400 inline-flex items-center justify-center transition cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                    </button>
                                    @endcan
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="py-12 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 mb-3">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                </div>
                                <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-200">No se encontraron ventas registradas</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-sm">
                                    Utilice el terminal POS para registrar ventas de mostrador y emitir tickets o comprobantes fiscales.
                                </p>
                                @can('realizar ventas')
                                <a href="{{ route('ventas.create') }}" class="mt-4 inline-flex items-center space-x-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-xs transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    <span>Abrir Terminal POS</span>
                                </a>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($ventas->hasPages())
        <div class="p-3 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900">
            {{ $ventas->links() }}
        </div>
        @endif
    </div>

    <!-- Modal Anular Venta -->
    <div x-show="modalAnular" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-950/70 backdrop-blur-xs overflow-y-auto" 
         aria-labelledby="modal-title" 
         role="dialog" 
         aria-modal="true"
         @keydown.escape.window="modalAnular = false"
         @click.self="modalAnular = false">
        <div @click.stop
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="bg-white dark:bg-slate-900 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all w-full max-w-lg border border-slate-300 dark:border-slate-800 my-auto">
            
            <form :action="'/ventas/' + ventaId + '/anular'" method="POST">
                @csrf
                <div class="p-6">
                    <div class="flex items-center space-x-3 text-rose-600 mb-4">
                        <div class="w-10 h-10 rounded-full bg-rose-100 dark:bg-rose-950/60 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white" id="modal-title">
                                Anular Venta <span x-text="comprobanteInfo"></span>
                            </h3>
                            <p class="text-xs text-slate-500">Esta acción revertirá las salidas del inventario y reingresará el stock a los lotes originales.</p>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                Motivo de Anulación <span class="text-rose-500">*</span>
                            </label>
                            <textarea name="motivo" 
                                      x-model="motivoAnulacion" 
                                      rows="3" 
                                      required
                                      minlength="5"
                                      placeholder="Indique la justificación (ej: error en producto solicitado por cliente, devolución inmediata, error de digitación)..."
                                      class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-rose-500 placeholder-slate-400"></textarea>
                        </div>
                    </div>
                </div>

                <div class="bg-slate-50 dark:bg-slate-800/50 px-6 py-3 border-t border-slate-100 dark:border-slate-800 flex justify-end space-x-2">
                    <button type="button" 
                            @click="modalAnular = false" 
                            class="px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                        Cancelar
                    </button>
                    <button type="submit" 
                            :disabled="!motivoAnulacion || motivoAnulacion.trim().length < 5"
                            class="px-4 py-2 bg-rose-600 hover:bg-rose-700 disabled:opacity-50 text-white rounded-xl text-xs font-bold shadow-xs transition">
                        Confirmar Anulación
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection