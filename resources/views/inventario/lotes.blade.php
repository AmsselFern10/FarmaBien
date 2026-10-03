@extends('layouts.app')
@section('title', 'Lotes de Inventario - FarmaBien')
@section('content')
<div x-data="{ 
    editModalOpen: false, 
    loteEdit: { id: '', numero_lote: '', producto: '', stock: 0, fecha_vencimiento: '', proveedor_id: '' }, 
    abrirEditar(lote) { 
        this.loteEdit = { ...lote }; 
        this.editModalOpen = true; 
    } 
}" class="space-y-5">

    <!-- Breadcrumb -->
    <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('inventario.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inventario</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-800 dark:text-slate-200 font-semibold">Lotes y Vencimientos</span>
    </nav>

    <!-- Header & Action Buttons -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <span>Lotes de Inventario</span>
                <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                    {{ $lotes->total() }} lotes
                </span>
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Control por lote, fecha de caducidad, proveedor y trazabilidad FEFO.</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap shrink-0">
            <!-- 1. Botón de navegación (Extrema Izquierda) -->
            <a href="{{ route('inventario.index') }}" 
               class="inline-flex items-center space-x-1.5 px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold rounded-xl shadow-xs transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Inventario</span>
            </a>

            <!-- 2. Botón Modo Full -->
            <button type="button" 
                    @click="$dispatch('toggle-pos-fullscreen')"
                    title="Modo Pantalla Completa / Ocultar Barras"
                    class="inline-flex items-center space-x-1.5 px-3.5 py-2 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold shadow-2xs transition cursor-pointer">
                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                <span>Modo Full</span>
            </button>

            @can('ajustar inventario')
            <!-- 3. Ajustar Stock (Verde Sólido Institucional) -->
            <a href="{{ route('inventario.ajustar') }}" 
               class="inline-flex items-center space-x-1.5 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-semibold rounded-xl shadow-2xs transition shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Ajustar Stock</span>
            </a>
            @endcan
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs">
        <form method="GET" action="{{ route('inventario.lotes') }}" class="flex flex-col gap-3">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-12 gap-3">
                <div class="md:col-span-5">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <input type="text" 
                               name="buscar" 
                               value="{{ request('buscar') }}" 
                               placeholder="Buscar por número de lote o medicamento..." 
                               class="w-full pl-10 pr-4 py-2 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                    </div>
                </div>
                <div class="md:col-span-3">
                    <select name="filtro_vencimiento" 
                            onchange="this.form.submit()" 
                            class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                        <option value="">Estado: Todos los lotes</option>
                        <option value="proximos_30" {{ request('filtro_vencimiento')=='proximos_30'?'selected':'' }}>Próximos 30 días</option>
                        <option value="proximos_60" {{ request('filtro_vencimiento')=='proximos_60'?'selected':'' }}>Próximos 60 días</option>
                        <option value="vencidos" {{ request('filtro_vencimiento')=='vencidos'?'selected':'' }}>Vencidos</option>
                    </select>
                </div>
                <div class="md:col-span-4">
                    <select name="orden" 
                            onchange="this.form.submit()" 
                            class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                        <option value="vencimiento_asc" {{ request('orden', 'vencimiento_asc')=='vencimiento_asc'?'selected':'' }}>Ordenar: Vencimiento (FEFO - Más Próximo)</option>
                        <option value="vencimiento_desc" {{ request('orden')=='vencimiento_desc'?'selected':'' }}>Ordenar: Vencimiento (Más Lejano)</option>
                        <option value="ingreso_desc" {{ request('orden')=='ingreso_desc'?'selected':'' }}>Ordenar: Fecha Entrada (Más Reciente)</option>
                        <option value="ingreso_asc" {{ request('orden')=='ingreso_asc'?'selected':'' }}>Ordenar: Fecha Entrada (Más Antigua)</option>
                        <option value="stock_desc" {{ request('orden')=='stock_desc'?'selected':'' }}>Ordenar: Mayor Stock Actual</option>
                        <option value="stock_asc" {{ request('orden')=='stock_asc'?'selected':'' }}>Ordenar: Menor Stock Actual</option>
                    </select>
                </div>
            </div>
            <div class="flex items-center justify-between pt-1">
                <div class="flex items-center gap-2">
                    <button type="submit" 
                            class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-semibold rounded-xl shadow-2xs transition inline-flex items-center justify-center gap-1.5 cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                        <span>Filtrar</span>
                    </button>
                    @if(request()->hasAny(['buscar', 'filtro_vencimiento', 'orden']))
                    <a href="{{ route('inventario.lotes') }}" 
                       class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 text-xs font-semibold rounded-xl transition inline-flex items-center justify-center gap-1">
                        <span>Limpiar</span>
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </a>
                    @endif
                </div>
                <span class="text-xs text-slate-400 font-medium">{{ $lotes->total() }} lotes encontrados</span>
            </div>
        </form>
    </div>

    <!-- Tabla -->
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                        <th class="px-5 py-3.5">N° Lote</th>
                        <th class="px-5 py-3.5">Medicamento</th>
                        <th class="px-5 py-3.5">Proveedor</th>
                        <th class="px-5 py-3.5">F. Entrada</th>
                        <th class="px-5 py-3.5 text-right">Stock Ini.</th>
                        <th class="px-5 py-3.5 text-right">Stock Act.</th>
                        <th class="px-5 py-3.5">Vencimiento</th>
                        <th class="px-5 py-3.5 text-center">Estado</th>
                        <th class="px-5 py-3.5 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-700 dark:text-slate-300">
                    @forelse($lotes as $lote)
                    @php
                        $hoy = now()->toDateString();
                        $venc = $lote->fecha_vencimiento->toDateString();
                        $dias = (int) now()->diffInDays($lote->fecha_vencimiento, false);
                        $estadoClass = $venc < $hoy ? 'bg-rose-50 dark:bg-rose-950/60 text-red-950 dark:text-rose-300 border-rose-200 dark:border-rose-800' : ($dias <= 30 ? 'bg-amber-50 dark:bg-amber-950/60 text-amber-950 dark:text-amber-300 border-amber-200 dark:border-amber-800' : 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-950 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800');
                        $estadoLabel = $venc < $hoy ? 'Vencido' : ($dias <= 30 ? 'Por vencer' : 'Vigente');
                    @endphp
                    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition {{ $venc < $hoy ? 'bg-rose-50/30 dark:bg-rose-950/10' : '' }}">
                        <td class="px-5 py-3.5 font-mono font-bold text-slate-900 dark:text-white">{{ $lote->numero_lote }}</td>
                        <td class="px-5 py-3.5">
                            <p class="font-semibold text-slate-900 dark:text-white">{{ $lote->producto->nombre ?? 'N/A' }}</p>
                            <p class="text-[10px] text-slate-500 dark:text-slate-400">{{ $lote->producto->categoria->nombre ?? '' }} · {{ $lote->producto->laboratorio->nombre ?? '' }}</p>
                        </td>
                        <td class="px-5 py-3.5 text-slate-700 dark:text-slate-300">{{ $lote->proveedor->nombre ?? '—' }}</td>
                        <td class="px-5 py-3.5 text-slate-600 dark:text-slate-400 font-mono text-[11px]">
                            {{ $lote->created_at ? $lote->created_at->format('d/m/Y') : '—' }}
                        </td>
                        <td class="px-5 py-3.5 text-right font-mono text-slate-600 dark:text-slate-400">{{ number_format($lote->stock_inicial) }}</td>
                        <td class="px-5 py-3.5 text-right">
                            <span class="font-mono font-bold text-slate-900 dark:text-white">{{ number_format($lote->stock_actual) }}</span>
                            @if($lote->stock_actual == 0)<span class="ml-1 text-[10px] text-red-900 dark:text-rose-400 font-bold">AGOTADO</span>@endif
                        </td>
                        <td class="px-5 py-3.5">
                            <p class="font-semibold {{ $venc < $hoy ? 'text-red-900 dark:text-rose-400 font-bold' : 'text-slate-800 dark:text-slate-200' }}">{{ $lote->fecha_vencimiento->format('d/m/Y') }}</p>
                            @if($dias > 0 && $dias <= 60)<p class="text-[10px] text-amber-900 dark:text-amber-300 font-bold">{{ $dias }} días restantes</p>@endif
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $estadoClass }}">{{ $estadoLabel }}</span>
                        </td>
                        <td class="px-5 py-3.5 text-center whitespace-nowrap">
                            <div class="inline-flex items-center justify-center gap-1.5">
                                <a href="{{ route('inventario.kardex-producto', $lote->producto_id) }}" 
                                   title="Ver Kardex"
                                   class="w-8 h-8 rounded-lg bg-sky-50 dark:bg-sky-950/60 text-sky-950 dark:text-sky-300 hover:bg-sky-100 dark:hover:bg-sky-900/60 border border-sky-200 dark:border-sky-800/60 inline-flex items-center justify-center transition shadow-2xs">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                </a>
                                @canany(['editar lotes', 'ajustar inventario'])
                                <button type="button" 
                                        @click="abrirEditar({
                                            id: {{ $lote->id }},
                                            numero_lote: '{{ addslashes($lote->numero_lote) }}',
                                            producto: '{{ addslashes($lote->producto->nombre ?? 'N/A') }}',
                                            stock: {{ (int)$lote->stock_actual }},
                                            fecha_vencimiento: '{{ $lote->fecha_vencimiento ? $lote->fecha_vencimiento->format('Y-m-d') : '' }}',
                                            proveedor_id: '{{ $lote->proveedor_id ?? '' }}'
                                        })"
                                        title="Editar Metadatos de Lote"
                                        class="w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-950/60 text-amber-950 dark:text-amber-300 hover:bg-amber-100 dark:hover:bg-amber-900/60 border border-amber-200 dark:border-amber-800/60 inline-flex items-center justify-center transition shadow-2xs cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                </button>
                                @endcanany
                                @can('ajustar inventario')
                                <a href="{{ route('inventario.ajustar') }}?lote={{ $lote->id }}" 
                                   title="Ajustar Lote"
                                   class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-950 dark:text-emerald-300 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 border border-emerald-200 dark:border-emerald-800/60 inline-flex items-center justify-center transition shadow-2xs">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-5 py-12 text-center text-slate-400 text-xs">
                            <svg class="w-10 h-10 mx-auto mb-3 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            No se encontraron lotes con los filtros aplicados.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($lotes->hasPages())
        <div class="px-5 py-3.5 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900">{{ $lotes->links() }}</div>
        @endif
    </div>

    <!-- Modal de Edición de Metadatos de Lote -->
    <div x-show="editModalOpen" 
         x-cloak 
         @keydown.escape.window="editModalOpen = false"
         class="fixed inset-0 z-50 overflow-y-auto" 
         style="display: none;">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" 
             @click="editModalOpen = false"
             x-show="editModalOpen" 
             x-transition:enter="ease-out duration-200" 
             x-transition:enter-start="opacity-0" 
             x-transition:enter-end="opacity-100" 
             x-transition:leave="ease-in duration-150" 
             x-transition:leave-start="opacity-100" 
             x-transition:leave-end="opacity-0"></div>

        <!-- Modal Box -->
        <div class="flex min-h-full items-center justify-center p-4 text-center">
            <div class="relative w-full max-w-lg transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 text-left shadow-xl transition-all"
                 x-show="editModalOpen"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

                <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
                    <div class="flex items-center space-x-2.5">
                        <div class="w-9 h-9 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-950 dark:text-amber-400 border border-amber-200 dark:border-amber-800/60 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Editar Metadatos de Lote</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Corrección de número de lote o fecha de vencimiento</p>
                        </div>
                    </div>
                    <button type="button" @click="editModalOpen = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form :action="'{{ url('inventario/lotes') }}/' + loteEdit.id" method="POST" class="mt-4 space-y-4">
                    @csrf
                    @method('PUT')

                    {{-- Info del producto y stock actual --}}
                    <div class="p-3 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-200 dark:border-slate-700 flex items-center justify-between">
                        <div>
                            <p class="text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 tracking-wider">Medicamento</p>
                            <p class="text-xs font-bold text-slate-900 dark:text-white" x-text="loteEdit.producto"></p>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 tracking-wider">Stock Actual</p>
                            <p class="text-xs font-bold font-mono text-emerald-950 dark:text-emerald-400" x-text="loteEdit.stock + ' unidades'"></p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Número de Lote <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" 
                                   name="numero_lote" 
                                   x-model="loteEdit.numero_lote" 
                                   required 
                                   maxlength="100"
                                   placeholder="Ej: LOT-2026-001"
                                   class="w-full px-3 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-mono text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Fecha de Vencimiento <span class="text-rose-500">*</span>
                            </label>
                            <input type="date" 
                                   name="fecha_vencimiento" 
                                   x-model="loteEdit.fecha_vencimiento" 
                                   required 
                                   class="w-full px-3 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Proveedor Asociado
                        </label>
                        <select name="proveedor_id" 
                                x-model="loteEdit.proveedor_id"
                                class="w-full px-3 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                            <option value="">Sin proveedor asignado</option>
                            @foreach($proveedores ?? [] as $prov)
                            <option value="{{ $prov->id }}">{{ $prov->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Motivo de la Modificación <span class="text-rose-500">*</span> <span class="text-[10px] text-slate-400 font-normal">(Auditoría)</span>
                        </label>
                        <input type="text" 
                               name="motivo_cambio" 
                               required 
                               minlength="5" 
                               maxlength="255"
                               placeholder="Ej: Corrección tipográfica según factura de compra original..."
                               class="w-full px-3 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                        <p class="text-[10px] text-slate-400 mt-1">Este cambio quedará asentado en el registro de auditoría del sistema.</p>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" 
                                @click="editModalOpen = false" 
                                class="px-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-xl shadow-2xs transition cursor-pointer">
                            Cancelar
                        </button>
                        <button type="submit" 
                                class="inline-flex items-center space-x-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-semibold rounded-xl shadow-2xs transition cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Guardar Cambios</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
