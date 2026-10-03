@extends('layouts.app')

@section('title', 'Registrar Devolución a Proveedor — FarmaBien')

@section('content')
<div x-data="devolucionProveedorForm()" class="space-y-4">

    {{-- Fila 1 — Breadcrumb --}}
    <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('compras.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Compras</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('compras.devoluciones.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Devoluciones a Proveedor</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-800 dark:text-slate-200 font-semibold">Nueva Devolución</span>
    </nav>

    {{-- Fila 2 — Título y Acciones --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">Registrar Devolución a Proveedor</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Seleccione la compra o los lotes a devolver. El stock se descuenta inmediatamente al confirmar.
            </p>
        </div>

        <div class="flex items-center gap-2 shrink-0 flex-wrap">
            {{-- Botón de Navegación --}}
            <a href="{{ route('compras.devoluciones.index') }}"
               class="inline-flex items-center space-x-1.5 px-3.5 py-1.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold shadow-xs transition">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span>Devoluciones a Proveedor</span>
            </a>

            {{-- Modo Full --}}
            <button type="button"
                    @click="$dispatch('toggle-pos-fullscreen')"
                    title="Modo Pantalla Completa / Ocultar Barras"
                    class="inline-flex items-center space-x-1.5 px-3.5 py-1.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold shadow-xs transition cursor-pointer">
                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                <span x-text="posFullscreen ? 'Salir Full' : 'Modo Full'">Modo Full</span>
            </button>
        </div>
    </div>

    @if(session('error') || $errors->any())
    <div class="px-4 py-3 rounded-xl bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800 text-xs text-red-950 dark:text-red-300 font-semibold space-y-1">
        @if(session('error'))<p>{{ session('error') }}</p>@endif
        @foreach($errors->all() as $e)<p>• {{ $e }}</p>@endforeach
    </div>
    @endif

    {{-- PASO 1: Buscar Compra o Lote (Si no se ha seleccionado ninguno) --}}
    @if(!$compra && empty($detallesDisponibles))
    <div class="space-y-4">

        {{-- Tarjeta Paso 1: Filtros y Selector Segmentado --}}
        <div class="p-4 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs space-y-3">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-2 border-b border-slate-200 dark:border-slate-800">
                <div class="flex items-center space-x-2">
                    <span class="w-5 h-5 rounded-full bg-emerald-100 dark:bg-emerald-900/40 text-emerald-950 dark:text-emerald-300 flex items-center justify-center text-[11px] font-bold">1</span>
                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200">
                        Paso 1: Buscar Compra o Lote
                    </h2>
                </div>

                {{-- Selector Segmentado: Por Compra | Por Lote --}}
                <div class="inline-flex items-center p-0.5 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-semibold shadow-2xs">
                    <button type="button" 
                            @click="modoBusqueda = 'compra'" 
                            :class="modoBusqueda === 'compra' ? 'bg-white dark:bg-slate-700 text-emerald-950 dark:text-emerald-400 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200'"
                            class="px-3 py-1 rounded-lg transition flex items-center space-x-1.5 cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        <span>Por Compra</span>
                    </button>
                    <button type="button" 
                            @click="modoBusqueda = 'lote'" 
                            :class="modoBusqueda === 'lote' ? 'bg-white dark:bg-slate-700 text-emerald-950 dark:text-emerald-400 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200'"
                            class="px-3 py-1 rounded-lg transition flex items-center space-x-1.5 cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        <span>Por Lote</span>
                    </button>
                </div>
            </div>

            {{-- Formulario de Búsqueda para Modo Por Compra --}}
            <div x-show="modoBusqueda === 'compra'">
                <form method="GET" action="{{ route('compras.devoluciones.create') }}" class="flex flex-col sm:flex-row gap-2.5">
                    <input type="hidden" name="modo" value="compra">
                    <div class="relative flex-1">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </span>
                        <input type="text" 
                               name="buscar_compra" 
                               value="{{ request('buscar_compra') }}" 
                               placeholder="Buscar por N° de factura, proveedor o producto..."
                               class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                    </div>
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 transition inline-flex items-center justify-center gap-1.5 shadow-xs cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <span>Buscar</span>
                    </button>
                    @if(request()->filled('buscar_compra'))
                    <a href="{{ route('compras.devoluciones.create', ['modo' => 'compra']) }}" class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 transition text-center shrink-0">
                        Ver Todas
                    </a>
                    @endif
                </form>
            </div>

            {{-- Formulario de Búsqueda para Modo Por Lote --}}
            <div x-show="modoBusqueda === 'lote'" x-cloak>
                <form method="GET" action="{{ route('compras.devoluciones.create') }}" class="flex flex-col sm:flex-row gap-2.5">
                    <input type="hidden" name="modo" value="lote">
                    <div class="relative flex-1">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </span>
                        <input type="text" 
                               name="buscar_lote" 
                               value="{{ request('buscar_lote') }}" 
                               placeholder="Buscar por producto, lote o proveedor..."
                               class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                    </div>
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 transition inline-flex items-center justify-center gap-1.5 shadow-xs cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <span>Buscar</span>
                    </button>
                    @if(request()->filled('buscar_lote'))
                    <a href="{{ route('compras.devoluciones.create', ['modo' => 'lote']) }}" class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 transition text-center shrink-0">
                        Ver Todos
                    </a>
                    @endif
                </form>
            </div>
        </div>

        {{-- TABLA 1: Compras Recientes Disponibles para Devolución (Modo Por Compra) --}}
        <div x-show="modoBusqueda === 'compra'" class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 flex items-center justify-between">
                <div>
                    <h3 class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">
                        Compras Recientes Disponibles para Devolución
                    </h3>
                    <p class="text-[11px] text-slate-500">Haga clic en "Seleccionar" en la compra correspondiente para cargar sus lotes</p>
                </div>
                <span class="text-xs font-semibold text-emerald-950 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/60 px-2.5 py-0.5 rounded-full border border-emerald-200 dark:border-emerald-800">
                    {{ $comprasRecientes->total() }} compras
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-700 dark:text-slate-300">
                    <thead class="bg-slate-100/80 dark:bg-slate-800/80 text-slate-600 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800 text-[11px] font-bold uppercase tracking-wider">
                        <tr>
                            <th class="px-4 py-3">Factura / Comprobante</th>
                            <th class="px-4 py-3">Proveedor</th>
                            <th class="px-4 py-3">Fecha y Hora</th>
                            <th class="px-4 py-3 text-center">Lotes</th>
                            <th class="px-4 py-3 text-right">Total Compra</th>
                            <th class="px-4 py-3 text-center">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/70 dark:divide-slate-800/70">
                        @forelse($comprasRecientes as $c)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition">
                            <td class="px-4 py-3">
                                <span class="font-bold text-slate-900 dark:text-white">{{ $c->numero_comprobante ?? 'Compra #' . $c->id }}</span>
                                <div class="text-[11px] text-slate-500">Registrado por: {{ $c->usuario->name ?? 'Sistema' }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="font-bold text-slate-800 dark:text-slate-200">{{ $c->proveedor->nombre ?? 'Sin proveedor' }}</span>
                                @if($c->proveedor?->ruc)
                                <div class="text-[11px] text-slate-500">RUC: {{ $c->proveedor->ruc }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 font-mono text-slate-600 dark:text-slate-400">
                                {{ $c->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-[11px] font-semibold border border-slate-200 dark:border-slate-700">
                                    {{ $c->lotes->where('stock_actual', '>', 0)->count() }} lote(s)
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right font-mono font-bold text-slate-900 dark:text-white">
                                C$ {{ number_format($c->total, 2) }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('compras.devoluciones.create', ['compra_id' => $c->id]) }}" 
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/40 dark:hover:bg-emerald-900/60 text-emerald-950 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 transition shadow-2xs">
                                    <span>Seleccionar</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-slate-500 dark:text-slate-400">
                                No se encontraron compras disponibles para devolución con stock en lotes.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($comprasRecientes->hasPages())
            <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50">
                {{ $comprasRecientes->appends(['modo' => 'compra'])->links() }}
            </div>
            @endif
        </div>

        {{-- TABLA 2: Lotes Disponibles para Devolución (Modo Por Lote) --}}
        <div x-show="modoBusqueda === 'lote'" x-cloak class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div>
                    <h3 class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">
                        Lotes Disponibles para Devolución
                    </h3>
                    <p class="text-[11px] text-slate-500">Seleccione los lotes que desea devolver (debe pertenecer al mismo proveedor):</p>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="text-xs text-slate-500">Seleccionados: <strong class="text-emerald-950 dark:text-emerald-300" x-text="lotesSeleccionados.length"></strong></span>
                    <button type="button" 
                            @click="continuarConLotesSeleccionados()"
                            :disabled="lotesSeleccionados.length === 0"
                            class="px-3.5 py-1.5 rounded-xl text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 disabled:opacity-50 disabled:cursor-not-allowed shadow-xs transition cursor-pointer">
                        Continuar con Lotes &rarr;
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-700 dark:text-slate-300">
                    <thead class="bg-slate-100/80 dark:bg-slate-800/80 text-slate-600 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800 text-[11px] font-bold uppercase tracking-wider">
                        <tr>
                            <th class="px-4 py-3 text-center w-10">Sel.</th>
                            <th class="px-4 py-3">Producto / Lote</th>
                            <th class="px-4 py-3">Proveedor</th>
                            <th class="px-4 py-3">Vencimiento</th>
                            <th class="px-4 py-3 text-center">Stock Disponible</th>
                            <th class="px-4 py-3">Compra de Origen</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/70 dark:divide-slate-800/70">
                        @forelse($lotesDisponibles as $l)
                        @php
                            $esControlado = $l->producto?->esControlado() ?? false;
                            $esVencido = $l->fecha_vencimiento && $l->fecha_vencimiento->isPast();
                            $esPorVencer = $l->fecha_vencimiento && !$esVencido && $l->fecha_vencimiento->diffInDays(now()) <= 60;
                        @endphp
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition"
                            :class="proveedorFijadoId && proveedorFijadoId != {{ $l->proveedor_id ?? 0 }} ? 'opacity-40 bg-slate-50 dark:bg-slate-800/20' : ''">
                            <td class="px-4 py-3 text-center">
                                <input type="checkbox" 
                                       value="{{ $l->id }}" 
                                       data-proveedor="{{ $l->proveedor_id }}"
                                       @change="toggleLoteCheckbox($event, {{ $l->id }}, {{ $l->proveedor_id ?? 0 }})"
                                       :disabled="proveedorFijadoId && proveedorFijadoId != {{ $l->proveedor_id ?? 0 }}"
                                       class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 w-4 h-4 cursor-pointer">
                            </td>
                            <td class="px-4 py-3">
                                <span class="font-bold text-slate-900 dark:text-white">{{ $l->producto->nombre ?? 'Sin producto' }}</span>
                                <div class="text-[11px] text-slate-500 flex items-center gap-1.5 flex-wrap">
                                    <span>Lote: <strong class="font-mono text-slate-800 dark:text-slate-200">{{ $l->numero_lote }}</strong></span>
                                    @if($esControlado)
                                    <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-red-100 text-red-950 border border-red-200">CONTROLADO</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="font-medium text-slate-800 dark:text-slate-200">{{ $l->proveedor->nombre ?? '—' }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-mono text-slate-700 dark:text-slate-300">{{ $l->fecha_vencimiento ? $l->fecha_vencimiento->format('d/m/Y') : 'N/A' }}</div>
                                @if($esVencido)
                                <span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-950 border border-red-200">Vencido</span>
                                @elseif($esPorVencer)
                                <span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-950 border border-amber-200">Por vencer</span>
                                @else
                                <span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-950 border border-emerald-200">Vigente</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center font-bold text-slate-900 dark:text-white font-mono">
                                {{ $l->stock_actual }} u.
                            </td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                                @if($l->compra)
                                <span class="font-medium">{{ $l->compra->numero_comprobante ?? '#' . $l->compra_id }}</span>
                                @else
                                <span>Inventario inicial</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-slate-500 dark:text-slate-400">
                                No se encontraron lotes activos disponibles para devolución.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($lotesDisponibles->hasPages())
            <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50">
                {{ $lotesDisponibles->appends(['modo' => 'lote'])->links() }}
            </div>
            @endif
        </div>

    </div>

    @else
    {{-- PASO 2: Cantidades a Devolver y Confirmación --}}
    <form method="POST" action="{{ route('compras.devoluciones.store') }}" id="formDevolucionCompra" class="space-y-4" @submit="validarFormulario($event)">
        @csrf
        <input type="hidden" name="proveedor_id" value="{{ $proveedorSeleccionado->id ?? '' }}">
        @if($compra)
        <input type="hidden" name="compra_id" value="{{ $compra->id }}">
        @endif

        {{-- Tarjeta de Resumen: COMPRA SELECCIONADA o LOTES SELECCIONADOS --}}
        <div class="p-4 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs">
            <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800 mb-3">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                    <h3 class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">
                        {{ $compra ? 'Compra Seleccionada' : 'Lotes de Proveedor Seleccionados' }}
                    </h3>
                </div>
                <a href="{{ route('compras.devoluciones.create') }}" class="text-xs font-semibold text-slate-600 hover:text-emerald-700 dark:text-slate-400 dark:hover:text-emerald-400 underline">
                    Cambiar Selección
                </a>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
                <div>
                    <span class="text-slate-500 block">Comprobante / Origen:</span>
                    <strong class="text-slate-900 dark:text-white text-sm font-bold">{{ $compra->numero_comprobante ?? ($compra ? 'Compra #' . $compra->id : 'Lotes seleccionados') }}</strong>
                </div>
                <div>
                    <span class="text-slate-500 block">Proveedor:</span>
                    <strong class="text-slate-800 dark:text-slate-200 text-sm">{{ $proveedorSeleccionado->nombre ?? 'Sin proveedor' }}</strong>
                </div>
                <div>
                    <span class="text-slate-500 block">Fecha de Compra:</span>
                    <span class="font-medium text-slate-800 dark:text-slate-200">{{ $compra ? $compra->created_at->format('d/m/Y H:i') : now()->format('d/m/Y') }}</span>
                </div>
                <div>
                    <span class="text-slate-500 block">Total Original:</span>
                    <strong class="text-emerald-950 dark:text-emerald-400 font-mono font-bold text-sm">
                        {{ $compra ? 'C$ ' . number_format($compra->total, 2) : 'N/A' }}
                    </strong>
                </div>
            </div>
        </div>

        {{-- Aviso de Medicamentos Controlados MINSA --}}
        <template x-if="tieneControladosConCantidad">
            <div class="p-3.5 rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800 flex items-start space-x-3 text-amber-950 dark:text-amber-300">
                <svg class="w-5 h-5 shrink-0 text-amber-600 dark:text-amber-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <div class="text-xs">
                    <span class="font-bold">Aviso MINSA:</span> Los lotes marcados con <span class="font-bold text-red-950 dark:text-red-300">CONTROLADO</span> generarán automáticamente un registro de egreso en el libro de medicamentos controlados MINSA al guardar.
                </div>
            </div>
        </template>

        {{-- Tarjeta Paso 2: Indique la Cantidad de Lotes a Devolver --}}
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div>
                    <h3 class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">
                        Paso 2: Indique la cantidad de lotes a devolver
                    </h3>
                    <p class="text-[11px] text-slate-500">Ajuste las unidades a devolver al proveedor según el stock actual del lote</p>
                </div>

                {{-- Botón secundario pastel: + Agregar Lote --}}
                <div class="relative" @click.outside="modalAgregarLoteOpen = false">
                    <button type="button" 
                            @click="modalAgregarLoteOpen = !modalAgregarLoteOpen"
                            class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/40 dark:hover:bg-indigo-900/60 text-indigo-950 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 text-xs font-semibold transition cursor-pointer">
                        <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>+ Agregar Lote</span>
                    </button>

                    {{-- Menú desplegable para agregar otro lote del proveedor --}}
                    <div x-show="modalAgregarLoteOpen" x-cloak class="absolute right-0 mt-2 z-50 w-80 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-xl p-3 space-y-2">
                        <div class="text-xs font-bold text-slate-800 dark:text-slate-200">Sumar otro lote del proveedor:</div>
                        <input type="text" x-model="filtroLoteModal" placeholder="Buscar por producto o lote..." class="w-full px-2.5 py-1.5 bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg text-xs text-slate-900 dark:text-white">
                        <div class="max-h-48 overflow-y-auto space-y-1 divide-y divide-slate-100 dark:divide-slate-700">
                            <template x-for="l in lotesDisponiblesExtra" :key="l.id">
                                <button type="button" @click="agregarLoteExtra(l)" class="w-full text-left p-1.5 hover:bg-emerald-50 dark:hover:bg-slate-700 rounded text-xs flex justify-between items-center">
                                    <div>
                                        <span class="font-bold text-slate-900 dark:text-white" x-text="l.producto"></span>
                                        <div class="text-[10px] text-slate-500 font-mono" x-text="'Lote: ' + l.lote + ' | Disp: ' + l.stock + ' u.'"></div>
                                    </div>
                                    <span class="text-emerald-700 font-bold text-[11px]">+ Añadir</span>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-700 dark:text-slate-300">
                    <thead class="bg-slate-100/80 dark:bg-slate-800/80 text-slate-600 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800 text-[11px] font-bold uppercase tracking-wider">
                        <tr>
                            <th class="px-4 py-3">Producto / Lote</th>
                            <th class="px-3 py-3 text-center">Comprada</th>
                            <th class="px-3 py-3 text-center">Ya Devuelta</th>
                            <th class="px-3 py-3 text-center">Disponible</th>
                            <th class="px-3 py-3 text-center">Cant. a Devolver</th>
                            <th class="px-3 py-3 text-right">Costo Unit.</th>
                            <th class="px-3 py-3 text-right">Subtotal</th>
                            <th class="px-3 py-3 text-center">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/70 dark:divide-slate-800/70">
                        <template x-for="(item, index) in items" :key="item.lote_id">
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition">
                                <td class="px-4 py-3">
                                    <input type="hidden" :name="'items[' + index + '][lote_id]'" :value="item.lote_id">
                                    <input type="hidden" :name="'items[' + index + '][precio_unitario]'" :value="item.costo">
                                    <span class="font-bold text-slate-900 dark:text-white" x-text="item.producto_nombre"></span>
                                    <div class="text-[11px] text-slate-500 flex items-center gap-1.5 flex-wrap">
                                        <span>Lote: <strong class="font-mono text-slate-800 dark:text-slate-200" x-text="item.numero_lote"></strong></span>
                                        <span>| Vence: <span class="font-mono" x-text="item.vencimiento"></span></span>
                                        <template x-if="item.es_controlado">
                                            <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-red-100 text-red-950 border border-red-200">CONTROLADO</span>
                                        </template>
                                    </div>
                                </td>
                                <td class="px-3 py-3 text-center font-mono" x-text="item.comprada + ' u.'"></td>
                                <td class="px-3 py-3 text-center text-slate-500 font-mono" x-text="item.ya_devuelta + ' u.'"></td>
                                <td class="px-3 py-3 text-center font-bold text-emerald-950 dark:text-emerald-400 font-mono" x-text="item.disponible + ' u.'"></td>
                                <td class="px-3 py-3 text-center">
                                    <template x-if="item.disponible > 0">
                                        <div class="inline-flex items-center border border-slate-300 dark:border-slate-700 rounded-lg overflow-hidden bg-white dark:bg-slate-800">
                                            <button type="button" 
                                                    @click="ajustarCantidad(index, -1)"
                                                    class="px-2.5 py-1 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-200 font-bold cursor-pointer">-</button>
                                            <input type="number" 
                                                   :name="'items[' + index + '][cantidad]'" 
                                                   min="0" 
                                                   :max="item.disponible" 
                                                   x-model.number="item.cantidad" 
                                                   @input="recalcularTotales()"
                                                   class="w-14 text-center text-xs font-bold font-mono border-0 bg-transparent text-slate-900 dark:text-white p-1 focus:ring-0">
                                            <button type="button" 
                                                    @click="ajustarCantidad(index, 1)"
                                                    class="px-2.5 py-1 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-200 font-bold cursor-pointer">+</button>
                                        </div>
                                    </template>
                                    <template x-if="item.disponible <= 0">
                                        <span class="text-[11px] font-bold text-slate-400">Sin Stock</span>
                                    </template>
                                </td>
                                <td class="px-3 py-3 text-right font-mono text-slate-700 dark:text-slate-300" x-text="'C$ ' + Number(item.costo).toFixed(2)"></td>
                                <td class="px-3 py-3 text-right font-mono font-bold text-slate-900 dark:text-white" x-text="'C$ ' + (item.cantidad * item.costo).toFixed(2)"></td>
                                <td class="px-3 py-3 text-center">
                                    <span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-bold" 
                                          :class="item.estado_venc === 'Vencido' ? 'bg-red-100 text-red-950 border border-red-200' : (item.estado_venc === 'Por vencer' ? 'bg-amber-100 text-amber-950 border border-amber-200' : 'bg-emerald-100 text-emerald-950 border border-emerald-200')" 
                                          x-text="item.estado_venc"></span>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Motivo y Resumen (Dos tarjetas en paralelo) --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            {{-- Tarjeta 1: Motivo y Justificación --}}
            <div class="p-4 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs space-y-3">
                <div class="text-xs font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200 border-b border-slate-200 dark:border-slate-800 pb-2 flex items-center space-x-2">
                    <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                    <span>Motivo y Justificación</span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                        Motivo de Devolución <span class="text-red-950 dark:text-red-400">*</span>
                    </label>
                    <select name="motivo_tipo" x-model="motivoTipo" @change="actualizarMotivoTexto()" required class="w-full px-2.5 py-2 text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                        <option value="Producto vencido">Producto vencido</option>
                        <option value="Producto dañado">Producto dañado / defectuoso</option>
                        <option value="Error en pedido">Error en pedido / discrepancia de factura</option>
                        <option value="Daños en transporte">Daños en transporte</option>
                        <option value="Otro">Otro motivo</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                        Observaciones y Detalle <span class="text-red-950 dark:text-red-400">*</span>
                    </label>
                    <textarea name="motivo" x-model="motivoTexto" rows="3" required minlength="5" placeholder="Detalle la justificación de la devolución para auditoría y Kardex..."
                              class="w-full px-3 py-2 text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500"></textarea>
                </div>
            </div>

            {{-- Tarjeta 2: Resumen de la Devolución --}}
            <div class="p-4 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs space-y-3 flex flex-col justify-between">
                <div>
                    <div class="text-xs font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200 border-b border-slate-200 dark:border-slate-800 pb-2 flex items-center space-x-2">
                        <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        <span>Resumen de la Devolución</span>
                    </div>

                    <div class="space-y-2 pt-2 text-xs">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Proveedor Receptor:</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200">{{ $proveedorSeleccionado->nombre ?? 'Sin proveedor' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Lotes a Devolver:</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200" x-text="lotesConCantidadCount + ' lote(s)'"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Total Unidades:</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200" x-text="unidadesDevueltasCount + ' unidades'"></span>
                        </div>
                    </div>
                </div>

                {{-- Recuadro Monto Total a Devolver --}}
                <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300 block">Monto Total a Devolver:</span>
                        <span class="text-[11px] text-slate-500">Calculado en base a los lotes devueltos</span>
                    </div>
                    <div class="text-right">
                        <span class="text-xl font-bold font-mono text-red-950 dark:text-red-400" x-text="'C$ ' + totalDevolucion.toFixed(2)">C$ 0.00</span>
                    </div>
                </div>
            </div>

        </div>

        {{-- Pie del Formulario: Botones Cancelar y Confirmar --}}
        <div class="flex items-center justify-end space-x-2 pt-2">
            <a href="{{ route('compras.devoluciones.create') }}" 
               class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 shadow-xs transition">
                Cancelar
            </a>
            <button type="submit" 
                    :disabled="totalDevolucion <= 0 || unidadesDevueltasCount === 0 || motivoTexto.trim().length < 5"
                    class="inline-flex items-center space-x-1.5 px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 disabled:opacity-50 disabled:cursor-not-allowed shadow-xs transition cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>Confirmar y Procesar Devolución</span>
            </button>
        </div>

    </form>
    @endif

</div>

@push('scripts')
<script>
function devolucionProveedorForm() {
    return {
        modoBusqueda: '{{ request('modo', 'compra') }}',
        lotesSeleccionados: [],
        proveedorFijadoId: null,
        modalAgregarLoteOpen: false,
        filtroLoteModal: '',

        motivoTipo: 'Producto vencido',
        motivoTexto: 'Producto vencido devuelto a proveedor.',

        // Lotes cargados en el paso 2
        items: [
            @if(!empty($detallesDisponibles))
                @foreach($detallesDisponibles as $d)
                {
                    lote_id: {{ $d['lote']->id }},
                    producto_nombre: @js($d['lote']->producto->nombre ?? 'Sin producto'),
                    numero_lote: @js($d['lote']->numero_lote),
                    vencimiento: @js($d['lote']->fecha_vencimiento ? $d['lote']->fecha_vencimiento->format('d/m/Y') : 'N/A'),
                    comprada: {{ (int)$d['cantidad_comprada'] }},
                    ya_devuelta: {{ (int)$d['cantidad_ya_devuelta'] }},
                    disponible: {{ (int)$d['cantidad_disponible'] }},
                    costo: {{ (float)$d['precio_unitario'] }},
                    cantidad: {{ (int)$d['cantidad_disponible'] > 0 ? (int)$d['cantidad_disponible'] : 0 }},
                    es_controlado: {{ $d['es_controlado'] ? 'true' : 'false' }},
                    estado_venc: @js($d['lote']->fecha_vencimiento && $d['lote']->fecha_vencimiento->isPast() ? 'Vencido' : ($d['lote']->fecha_vencimiento && $d['lote']->fecha_vencimiento->diffInDays(now()) <= 60 ? 'Por vencer' : 'Vigente')),
                },
                @endforeach
            @endif
        ],

        // Todos los lotes del sistema para el modal auxiliar
        catalogoLotes: [
            @if(isset($todosLotes))
                @foreach($todosLotes as $tl)
                {
                    id: {{ $tl->id }},
                    lote: @js($tl->numero_lote),
                    producto: @js($tl->producto->nombre ?? ''),
                    proveedor_id: {{ (int)$tl->proveedor_id }},
                    stock: {{ (int)$tl->stock_actual }},
                    costo: {{ (float)($tl->precio_compra ?? 0) }},
                    controlado: {{ ($tl->producto?->esControlado()) ? 'true' : 'false' }},
                    vence: @js($tl->fecha_vencimiento ? $tl->fecha_vencimiento->format('d/m/Y') : 'N/A'),
                },
                @endforeach
            @endif
        ],

        get lotesDisponiblesExtra() {
            const provId = {{ $proveedorSeleccionado->id ?? 0 }};
            const existentes = this.items.map(i => i.lote_id);
            const filtro = this.filtroLoteModal.toLowerCase().trim();

            return this.catalogoLotes.filter(l => {
                if (provId && l.proveedor_id !== provId) return false;
                if (existentes.includes(l.id)) return false;
                if (!filtro) return true;
                return l.producto.toLowerCase().includes(filtro) || l.lote.toLowerCase().includes(filtro);
            });
        },

        get totalDevolucion() {
            return this.items.reduce((acc, i) => acc + ((parseInt(i.cantidad) || 0) * (parseFloat(i.costo) || 0)), 0);
        },

        get unidadesDevueltasCount() {
            return this.items.reduce((acc, i) => acc + (parseInt(i.cantidad) || 0), 0);
        },

        get lotesConCantidadCount() {
            return this.items.filter(i => (parseInt(i.cantidad) || 0) > 0).length;
        },

        get tieneControladosConCantidad() {
            return this.items.some(i => i.es_controlado && (parseInt(i.cantidad) || 0) > 0);
        },

        toggleLoteCheckbox(e, loteId, proveedorId) {
            if (e.target.checked) {
                if (!this.proveedorFijadoId) {
                    this.proveedorFijadoId = proveedorId;
                }
                if (!this.lotesSeleccionados.includes(loteId)) {
                    this.lotesSeleccionados.push(loteId);
                }
            } else {
                this.lotesSeleccionados = this.lotesSeleccionados.filter(id => id !== loteId);
                if (this.lotesSeleccionados.length === 0) {
                    this.proveedorFijadoId = null;
                }
            }
        },

        continuarConLotesSeleccionados() {
            if (this.lotesSeleccionados.length === 0) {
                alert('Seleccione al menos un lote para continuar.');
                return;
            }
            window.location.href = "{{ route('compras.devoluciones.create') }}?lote_ids=" + this.lotesSeleccionados.join(',');
        },

        ajustarCantidad(index, delta) {
            const item = this.items[index];
            if (!item) return;
            let val = (parseInt(item.cantidad) || 0) + delta;
            val = Math.max(0, Math.min(item.disponible, val));
            item.cantidad = val;
        },

        recalcularTotales() {
            this.items.forEach(i => {
                let c = parseInt(i.cantidad) || 0;
                if (c > i.disponible) i.cantidad = i.disponible;
                if (c < 0) i.cantidad = 0;
            });
        },

        agregarLoteExtra(lote) {
            this.items.push({
                lote_id: lote.id,
                producto_nombre: lote.producto,
                numero_lote: lote.lote,
                vencimiento: lote.vence,
                comprada: lote.stock,
                ya_devuelta: 0,
                disponible: lote.stock,
                costo: lote.costo,
                cantidad: lote.stock,
                es_controlado: lote.controlado,
                estado_venc: 'Vigente'
            });
            this.modalAgregarLoteOpen = false;
            this.filtroLoteModal = '';
        },

        actualizarMotivoTexto() {
            if (!this.motivoTexto || this.motivoTexto.includes('devuelto a proveedor')) {
                this.motivoTexto = this.motivoTipo + ' devuelto a proveedor.';
            }
        },

        validarFormulario(e) {
            if (this.unidadesDevueltasCount <= 0 || this.totalDevolucion <= 0) {
                e.preventDefault();
                alert('Debe indicar al menos una cantidad mayor a 0 en los lotes a devolver.');
                return false;
            }
            if (this.motivoTexto.trim().length < 5) {
                e.preventDefault();
                alert('El motivo u observación de la devolución es obligatorio.');
                return false;
            }
            return true;
        }
    };
}
</script>
@endpush
@endsection
