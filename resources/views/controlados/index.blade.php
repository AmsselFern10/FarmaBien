@extends('layouts.app')

@section('title', 'Libro de Medicamentos Controlados (MINSA) - FarmaBien')

@section('content')
<div class="space-y-5" x-data="{ modalFoto: false, modalUpload: false, uploadUrl: '', registroActual: null }">

    <!-- Breadcrumbs -->
    <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('productos.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Medicamentos</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-800 dark:text-slate-200 font-semibold">Libro de Medicamentos Controlados</span>
    </nav>

    <!-- Header & Action Buttons -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-3">
                <h1 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <span>Libro & Kardex Oficial de Controlados</span>
                </h1>
                <span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 border border-slate-200 dark:border-slate-700">
                    Fiscalización MINSA
                </span>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Bitácora oficial inmutable de trazabilidad: despachos, reingresos por devolución, bajas por merma y ajustes físicos.
            </p>
        </div>
        
        <div class="flex items-center gap-2 shrink-0 flex-wrap">
            <a href="{{ route('productos.index') }}"
               class="inline-flex items-center space-x-1.5 px-3 py-2 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold transition shrink-0 shadow-2xs">
                <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Medicamentos</span>
            </a>

            <!-- Ver Catálogo Filtrado -->
            <a href="{{ route('productos.index', ['tipo_control' => 'controlados']) }}" 
               class="inline-flex items-center space-x-1.5 px-3 py-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 text-emerald-950 dark:text-emerald-300 text-xs font-semibold transition shrink-0 shadow-2xs">
                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                <span>Catálogo Controlados</span>
            </a>

            <!-- Exportar Excel MINSA -->
            <a href="{{ route('controlados.excel') }}?{{ http_build_query(request()->only(['desde','hasta','tipo_movimiento','tipo_despacho','producto_id','q'])) }}" 
               class="inline-flex items-center space-x-1.5 px-3 py-2 bg-emerald-50 dark:bg-emerald-950/60 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 text-emerald-950 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 text-xs font-semibold rounded-xl shadow-2xs transition">
                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Exportar Excel</span>
            </a>

            <!-- Imprimir Libro Oficial MINSA -->
            <a href="{{ route('controlados.libro') }}?{{ http_build_query(request()->only(['desde','hasta','tipo_movimiento','tipo_despacho','producto_id','q'])) }}" 
               target="_blank"
               class="inline-flex items-center space-x-1.5 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-semibold rounded-xl shadow-xs transition cursor-pointer">
                <svg class="w-4 h-4 text-emerald-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Imprimir Libro MINSA</span>
            </a>
        </div>
    </div>

    <!-- Quick Classification Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Movimientos -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Total Operaciones</p>
                <p class="text-xl font-bold text-slate-900 dark:text-white mt-0.5">{{ $totalMovimientos }}</p>
                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">En el período consultado</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
        </div>

        <!-- Entradas / Reingresos -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Entradas & Reingresos (+)</p>
                <p class="text-xl font-bold text-emerald-950 dark:text-emerald-300 mt-0.5">+{{ number_format($totalEntradas, 0) }}</p>
                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Devoluciones, compras y ajustes (+)</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/50 flex items-center justify-center font-black text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            </div>
        </div>

        <!-- Salidas / Despachos -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Salidas & Despachos (-)</p>
                <p class="text-xl font-bold text-slate-900 dark:text-white mt-0.5">-{{ number_format($totalSalidas, 0) }}</p>
                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Dispensación en POS de ventas</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 flex items-center justify-center font-black text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
            </div>
        </div>

        <!-- Bajas / Mermas -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Bajas & Mermas (-)</p>
                <p class="text-xl font-bold text-rose-950 dark:text-rose-300 mt-0.5">-{{ number_format($totalMermas, 0) }}</p>
                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Roturas, vencidos y descarte</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 border border-rose-100 dark:border-rose-900/50 flex items-center justify-center font-black text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </div>
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs">
        <form method="GET" action="{{ route('controlados.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
            
            <!-- Buscador general -->
            <div class="sm:col-span-3 relative">
                <input type="text" name="q" value="{{ request('q') }}" 
                       placeholder="Buscar por paciente, cédula, médico, lote..."
                       class="w-full pl-9 pr-3.5 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white font-medium focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </div>

            <!-- Filtro Tipo de Operación -->
            <div class="sm:col-span-3">
                <select name="tipo_movimiento" 
                        class="w-full px-3 py-2 text-xs font-semibold rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-purple-500">
                    <option value="">Operación: Todas</option>
                    <optgroup label="Grupos de Movimiento">
                        <option value="entradas" @selected(request('tipo_movimiento') == 'entradas')>🟢 Solo Entradas & Reingresos (+)</option>
                        <option value="salidas" @selected(request('tipo_movimiento') == 'salidas')>🟣 Solo Salidas & Despachos (-)</option>
                        <option value="mermas" @selected(request('tipo_movimiento') == 'mermas')>🔴 Solo Bajas & Mermas (-)</option>
                    </optgroup>
                        <option value="{{ \App\Models\RegistroVentaControlado::TIPO_VENTA }}" @selected(request('tipo_movimiento') == \App\Models\RegistroVentaControlado::TIPO_VENTA)>Venta / Despacho POS (-)</option>
                        <option value="{{ \App\Models\RegistroVentaControlado::TIPO_ANULACION_VENTA }}" @selected(request('tipo_movimiento') == \App\Models\RegistroVentaControlado::TIPO_ANULACION_VENTA)>Reingreso Anulación Venta (+)</option>
                        <option value="{{ \App\Models\RegistroVentaControlado::TIPO_DEVOLUCION_STOCK }}" @selected(request('tipo_movimiento') == \App\Models\RegistroVentaControlado::TIPO_DEVOLUCION_STOCK)>Reingreso por Devolución (+)</option>
                        <option value="{{ \App\Models\RegistroVentaControlado::TIPO_DEVOLUCION_MERMA }}" @selected(request('tipo_movimiento') == \App\Models\RegistroVentaControlado::TIPO_DEVOLUCION_MERMA)>Baja por Devolución (Merma) (-)</option>
                        <option value="{{ \App\Models\RegistroVentaControlado::TIPO_AJUSTE_INGRESO }}" @selected(request('tipo_movimiento') == \App\Models\RegistroVentaControlado::TIPO_AJUSTE_INGRESO)>Ajuste Físico Positivo (+)</option>
                        <option value="{{ \App\Models\RegistroVentaControlado::TIPO_AJUSTE_EGRESO }}" @selected(request('tipo_movimiento') == \App\Models\RegistroVentaControlado::TIPO_AJUSTE_EGRESO)>Baja por Ajuste Físico (-)</option>
                        <option value="{{ \App\Models\RegistroVentaControlado::TIPO_COMPRA }}" @selected(request('tipo_movimiento') == \App\Models\RegistroVentaControlado::TIPO_COMPRA)>Ingreso por Compra (+)</option>
                        <option value="{{ \App\Models\RegistroVentaControlado::TIPO_ANULACION_COMPRA }}" @selected(request('tipo_movimiento') == \App\Models\RegistroVentaControlado::TIPO_ANULACION_COMPRA)>Reversión Anulación Compra (-)</option>
                    </optgroup>
                </select>
            </div>

            <!-- Filtro Fármaco Específico -->
            <div class="sm:col-span-2">
                <select name="producto_id" 
                        class="w-full px-3 py-2 text-xs font-semibold rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-purple-500">
                    <option value="">Fármaco: Todos</option>
                    @foreach($productosControlados as $p)
                        <option value="{{ $p->id }}" @selected(request('producto_id') == $p->id)>{{ $p->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Fecha Desde -->
            <div class="sm:col-span-1">
                <input type="date" name="desde" value="{{ request('desde', now()->startOfMonth()->toDateString()) }}"
                       class="w-full px-2 py-2 text-xs font-semibold rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-purple-500">
            </div>

            <!-- Fecha Hasta -->
            <div class="sm:col-span-1">
                <input type="date" name="hasta" value="{{ request('hasta', now()->toDateString()) }}"
                       class="w-full px-2 py-2 text-xs font-semibold rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-purple-500">
            </div>

            <!-- Botones -->
            <div class="sm:col-span-2 flex items-center gap-2 justify-end">
                <button type="submit" 
                        class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-bold rounded-xl transition shadow-xs cursor-pointer">
                    Filtrar
                </button>
                @if(request()->hasAny(['q', 'tipo_movimiento', 'tipo_despacho', 'desde', 'hasta', 'producto_id']))
                <a href="{{ route('controlados.index') }}" 
                   class="px-3 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 text-xs font-semibold rounded-xl transition">
                    Limpiar
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table of Controlled Drug Dispatches & Movements -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50/75 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800 text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                        <th class="px-5 py-3.5">Fecha & Folio</th>
                        <th class="px-5 py-3.5">Operación</th>
                        <th class="px-5 py-3.5">Medicamento & Lote</th>
                        <th class="px-5 py-3.5">Paciente / Beneficiario</th>
                        <th class="px-5 py-3.5">Prescriptor / Justificación MINSA</th>
                        <th class="px-5 py-3.5 text-center">Cantidad</th>
                        <th class="px-5 py-3.5 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    @forelse($registros as $reg)
                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition">
                        <!-- Fecha y Folio -->
                        <td class="px-5 py-3.5 whitespace-nowrap">
                            <span class="font-bold text-slate-900 dark:text-white block font-mono">
                                {{ $reg->created_at->format('d/m/Y') }}
                            </span>
                            <span class="text-[11px] text-slate-400 font-mono">
                                {{ $reg->created_at->format('H:i') }} hrs &bull; #{{ str_pad($reg->id, 5, '0', STR_PAD_LEFT) }}
                            </span>
                        </td>

                        <!-- Tipo de Operación -->
                        <td class="px-5 py-3.5 whitespace-nowrap">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold border {{ $reg->badge_class }}">
                                {{ $reg->tipo_etiqueta }}
                            </span>
                            @if($reg->venta_id)
                                <span class="text-[10px] text-slate-400 block mt-0.5 font-mono">Venta #{{ str_pad($reg->venta_id, 5, '0', STR_PAD_LEFT) }}</span>
                            @elseif($reg->devolucion)
                                <span class="text-[10px] text-slate-400 block mt-0.5 font-mono">{{ $reg->devolucion->numero_devolucion }}</span>
                            @elseif($reg->compra)
                                <span class="text-[10px] text-slate-400 block mt-0.5 font-mono">Compra #{{ $reg->compra->numero_comprobante }}</span>
                            @elseif($reg->movimiento_inventario_id)
                                <span class="text-[10px] text-slate-400 block mt-0.5 font-mono">Ajuste Kardex</span>
                            @endif
                        </td>

                        <!-- Medicamento & Lote -->
                        <td class="px-5 py-3.5">
                            <span class="font-bold text-slate-800 dark:text-slate-200 block">
                                {{ $reg->producto->nombre ?? 'Medicamento no disponible' }}
                            </span>
                            <div class="flex items-center gap-2 text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                <span class="truncate max-w-[180px]">{{ $reg->producto?->principio_activo ?: 'Fórmula regulada' }}</span>
                                @if($reg->lote?->numero_lote)
                                <span class="px-1.5 py-0.2 rounded bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-slate-200 font-mono font-bold text-[10px] border border-slate-200 dark:border-slate-700">
                                    Lote: {{ $reg->lote->numero_lote }}
                                </span>
                                @endif
                            </div>
                        </td>

                        <!-- Paciente / Beneficiario -->
                        <td class="px-5 py-3.5">
                            <span class="font-bold text-slate-900 dark:text-slate-200 block">
                                {{ $reg->paciente_nombre ?: 'Público General' }}
                            </span>
                            <div class="text-[11px] text-slate-500 dark:text-slate-400 flex items-center space-x-1.5">
                                @if($reg->paciente_cedula)
                                <span class="font-mono">Doc: {{ $reg->paciente_cedula }}</span>
                                @endif
                                @if($reg->paciente_edad)
                                <span>&bull; {{ $reg->paciente_edad }} años</span>
                                @endif
                            </div>
                        </td>

                        <!-- Prescriptor / Justificación MINSA -->
                        <td class="px-5 py-3.5">
                            @if($reg->medico_nombre)
                                <span class="font-bold text-slate-900 dark:text-slate-200 block">
                                    {{ Str::title(mb_strtolower($reg->medico_nombre)) }}
                                </span>
                                @if($reg->medico_num_registro)
                                <span class="text-[11px] text-slate-700 dark:text-slate-300 font-mono font-bold">
                                    Reg: {{ $reg->medico_num_registro }}
                                </span>
                                @endif
                            @elseif($reg->motivo_omision)
                                <span class="text-[11px] text-slate-600 dark:text-slate-300 block italic max-w-[220px]">
                                    {{ $reg->motivo_omision }}
                                </span>
                            @else
                                <span class="text-[11px] text-slate-400 italic">Dispensación regulada</span>
                            @endif
                        </td>

                        <!-- Cantidad (Con Signo y Color) -->
                        <td class="px-5 py-3.5 text-center whitespace-nowrap">
                            <span class="font-extrabold block text-sm {{ $reg->esEntrada() ? 'text-emerald-950 dark:text-emerald-300' : ($reg->esMerma() ? 'text-rose-950 dark:text-rose-300' : 'text-slate-900 dark:text-white') }}">
                                {{ $reg->signo }}{{ number_format($reg->cantidad, 0) }}
                            </span>
                            <span class="text-[10px] text-slate-500 font-semibold uppercase">
                                {{ $reg->unidad ?: 'unid.' }}
                            </span>
                        </td>

                        <!-- Acciones (Estandarizado Icon Button Cards) -->
                        <td class="px-5 py-3.5 text-center whitespace-nowrap">
                            <div class="inline-flex items-center justify-center gap-1.5">
                                <!-- Ver Detalle Completo -->
                                <a href="{{ route('controlados.show', $reg) }}"
                                   class="w-8 h-8 rounded-lg bg-sky-50 dark:bg-sky-950/60 text-sky-950 dark:text-sky-300 hover:bg-sky-100 dark:hover:bg-sky-900/60 border border-sky-200 dark:border-sky-800/60 inline-flex items-center justify-center transition shadow-2xs"
                                   title="Ver Detalle Completo de Auditoría">
                                    <svg class="w-3.5 h-3.5 text-sky-600 dark:text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>

                                <!-- Evidencia Fotográfica / Digital -->
                                @if($reg->tieneRecetaAdjunta())
                                    <a href="{{ route('controlados.evidencia', $reg) }}" target="_blank"
                                       class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-950 dark:text-emerald-300 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 border border-emerald-200 dark:border-emerald-800/60 inline-flex items-center justify-center transition shadow-2xs"
                                       title="Ver Evidencia Digital / Justificante">
                                        <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    </a>
                                @else
                                    <button type="button"
                                            @click="uploadUrl = '{{ route('controlados.evidencia.store', $reg) }}'; modalUpload = true;"
                                            class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-950 dark:text-emerald-300 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 border border-emerald-200 dark:border-emerald-800/60 inline-flex items-center justify-center transition shadow-2xs cursor-pointer"
                                            title="Adjuntar Documento / Evidencia">
                                        <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center text-slate-500 dark:text-slate-400">
                            <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 flex items-center justify-center mx-auto mb-2">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <p class="font-bold text-slate-700 dark:text-slate-300">No hay movimientos registrados de medicamentos controlados</p>
                            <p class="text-xs text-slate-400 mt-0.5">Los despachos, compras, devoluciones y ajustes de fármacos controlados se asentarán automáticamente aquí.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($registros->hasPages())
        <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-800">
            {{ $registros->links() }}
        </div>
        @endif
    </div>

    <!-- Modal para Subir Evidencia Fotográfica o Documental -->
    <template x-teleport="body">
        <div x-show="modalUpload" x-cloak
             class="fixed inset-0 z-[9999] flex items-center justify-center p-3 sm:p-4 bg-slate-950/75 backdrop-blur-sm overflow-y-auto"
             @keydown.escape.window="modalUpload = false"
             @click.self="modalUpload = false">
            <div @click.stop
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full p-5 border border-slate-200 dark:border-slate-800 shadow-2xl space-y-4 my-auto">
                <div class="flex items-center justify-between pb-2 border-b border-slate-200 dark:border-slate-800">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        <span>Adjuntar Evidencia o Justificante MINSA</span>
                    </h3>
                    <button type="button" @click="modalUpload = false" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
                </div>
                <form :action="uploadUrl" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Selecciona el archivo escaneado o foto (JPG, PNG, PDF, WEBP):
                        </label>
                        <input type="file" name="foto_receta" accept="image/*,application/pdf" required
                               class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-emerald-600 file:text-white hover:file:bg-emerald-700 cursor-pointer">
                        <p class="text-[10px] text-slate-400 mt-1">Tamaño máximo: 5 MB.</p>
                    </div>
                    <div class="flex justify-end gap-2 pt-2 border-t border-slate-200 dark:border-slate-800">
                        <button type="button" @click="modalUpload = false" class="px-3.5 py-1.5 rounded-xl border border-slate-300 dark:border-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-100 transition">Cancelar</button>
                        <button type="submit" class="px-4 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-xs">Subir y Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

</div>
@endsection
