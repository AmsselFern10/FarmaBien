@extends('layouts.app')

@section('title', 'Cuentas por Pagar a Proveedores - FarmaBien')

@section('content')
<div class="space-y-5" x-data="cuentasPorPagarApp()">
    
    <!-- Breadcrumbs -->
    <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('compras.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Compras</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-800 dark:text-slate-200 font-semibold">Cuentas por Pagar (CxP)</span>
    </nav>

    <!-- Header & Action Buttons -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <span>Cuentas por Pagar</span>
                <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                    {{ $compras->total() }} registros
                </span>
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Control de facturas a crédito, vencimientos y registro de abonos a proveedores con conciliación de caja.
            </p>
        </div>
        
        <div class="flex items-center gap-2.5 shrink-0 flex-wrap">
            <!-- Botón Modo Full -->
            <button type="button" 
                    @click="$dispatch('toggle-pos-fullscreen')"
                    title="Modo Pantalla Completa / Ocultar Barras"
                    class="inline-flex items-center space-x-1.5 px-3 py-2 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold shadow-2xs transition cursor-pointer">
                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                <span x-text="posFullscreen ? 'Salir Full' : 'Modo Full'">Modo Full</span>
            </button>

            <a href="{{ route('compras.index') }}" 
               class="inline-flex items-center space-x-1.5 px-3 py-2 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold shadow-2xs transition">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                <span>Compras & Lotes</span>
            </a>

            @can('registrar compras')
            <a href="{{ route('compras.create') }}" 
               class="inline-flex items-center space-x-1.5 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-semibold rounded-xl shadow-xs transition cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Nueva Compra</span>
            </a>
            @endcan
        </div>
    </div>

    <!-- Quick Stats Cards Row -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Deuda Total Pendiente</p>
                <p class="text-xl font-bold text-slate-900 dark:text-white mt-0.5">{{ formato_moneda($metricas['total_deuda']) }}</p>
                <p class="text-[11px] text-slate-400 mt-0.5">{{ $metricas['total_facturas_pendientes'] }} factura(s)</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-rose-200 dark:border-rose-900/50 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-rose-600 dark:text-rose-400">Deuda Vencida (Mora)</p>
                <p class="text-xl font-bold text-rose-600 dark:text-rose-400 mt-0.5">{{ formato_moneda($metricas['deuda_vencida']) }}</p>
                <p class="text-[11px] text-rose-500 mt-0.5">Atención prioritaria</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-amber-200 dark:border-amber-900/50 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-amber-600 dark:text-amber-400">Vence en ≤ 7 Días</p>
                <p class="text-xl font-bold text-amber-600 dark:text-amber-400 mt-0.5">{{ formato_moneda($metricas['deuda_por_vencer_7_dias']) }}</p>
                <p class="text-[11px] text-amber-500 mt-0.5">Programar pagos</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Bancos Autorizados</p>
                <p class="text-xs font-bold text-slate-800 dark:text-slate-200 mt-0.5">LAFISE • BAC • Banpro</p>
                <p class="text-[11px] text-slate-400">Ficohsa • Avanz • BDF</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/></svg>
            </div>
        </div>
    </div>

    <!-- Filters & State Tabs -->
    <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs space-y-3">
        <!-- Tabs -->
        <div class="flex items-center space-x-2 border-b border-slate-200 dark:border-slate-800 pb-3 overflow-x-auto">
            <a href="{{ route('cuentas-por-pagar.index', array_merge(request()->except('estado_pago', 'page'), ['estado_pago' => 'pendientes'])) }}"
               class="px-3 py-1.5 rounded-xl text-xs font-bold transition whitespace-nowrap {{ $filtroEstado === 'pendientes' ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                Pendientes y Parciales
            </a>
            <a href="{{ route('cuentas-por-pagar.index', array_merge(request()->except('estado_pago', 'page'), ['estado_pago' => 'vencidas'])) }}"
               class="px-3 py-1.5 rounded-xl text-xs font-bold transition whitespace-nowrap {{ $filtroEstado === 'vencidas' ? 'bg-rose-600 text-white' : 'text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40' }}">
                Vencidas
            </a>
            <a href="{{ route('cuentas-por-pagar.index', array_merge(request()->except('estado_pago', 'page'), ['estado_pago' => 'pagadas'])) }}"
               class="px-3 py-1.5 rounded-xl text-xs font-bold transition whitespace-nowrap {{ $filtroEstado === 'pagadas' ? 'bg-emerald-600 text-white' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                Pagadas Totalmente
            </a>
            <a href="{{ route('cuentas-por-pagar.index', array_merge(request()->except('estado_pago', 'page'), ['estado_pago' => 'todas'])) }}"
               class="px-3 py-1.5 rounded-xl text-xs font-bold transition whitespace-nowrap {{ $filtroEstado === 'todas' ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                Todas las Compras a Crédito
            </a>
        </div>

        <!-- Form Filters -->
        <form method="GET" action="{{ route('cuentas-por-pagar.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <input type="hidden" name="estado_pago" value="{{ $filtroEstado }}">
            <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Buscar Factura</label>
                <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="N° Factura, Proveedor..."
                       class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:border-emerald-500 focus:ring-emerald-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Proveedor</label>
                <select name="proveedor_id" class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">Todos los proveedores</option>
                    @foreach($proveedores as $prov)
                    <option value="{{ $prov->id }}" {{ request('proveedor_id') == $prov->id ? 'selected' : '' }}>
                        {{ $prov->nombre ?? $prov->nombre_empresa }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Desde</label>
                <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}"
                       class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:border-emerald-500 focus:ring-emerald-500">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 rounded-xl text-xs font-semibold text-white bg-slate-800 hover:bg-slate-700 dark:bg-slate-700 dark:hover:bg-slate-600 transition">
                    Filtrar
                </button>
                @if(request()->hasAny(['buscar', 'proveedor_id', 'fecha_desde', 'fecha_hasta']))
                <a href="{{ route('cuentas-por-pagar.index') }}" class="p-2 rounded-xl text-xs font-medium text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition" title="Limpiar filtros">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800 text-[11px] font-semibold uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3">Factura / Compra</th>
                        <th class="px-4 py-3">Proveedor</th>
                        <th class="px-4 py-3">Vencimiento</th>
                        <th class="px-4 py-3 text-right">Total Factura</th>
                        <th class="px-4 py-3 text-right">Abonado</th>
                        <th class="px-4 py-3 text-right">Saldo Pendiente</th>
                        <th class="px-4 py-3 text-center">Estado</th>
                        <th class="px-4 py-3 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                    @forelse($compras as $c)
                    @php
                        $abonado = $c->total - $c->saldo_pendiente;
                        $porcentajePagado = $c->total > 0 ? min(100, round(($abonado / $c->total) * 100)) : 100;
                        $diasRestantes = $c->fecha_vencimiento_pago ? (int) now()->startOfDay()->diffInDays($c->fecha_vencimiento_pago->startOfDay(), false) : null;
                        $estaVencida = $c->saldo_pendiente > 0 && $diasRestantes !== null && $diasRestantes < 0;
                    @endphp
                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition">
                        <td class="px-4 py-3">
                            <span class="font-bold text-slate-900 dark:text-white">{{ $c->numero_comprobante ?? 'Compra #' . $c->id }}</span>
                            <div class="text-[11px] text-slate-500">Emisión: {{ $c->fecha->format('d/m/Y') }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="font-bold text-slate-800 dark:text-slate-200">{{ $c->proveedor->nombre ?? $c->proveedor->nombre_empresa ?? 'Proveedor' }}</span>
                            @if($c->proveedor->telefono)
                            <div class="text-[11px] text-slate-400">Tel: {{ $c->proveedor->telefono }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($c->fecha_vencimiento_pago)
                                <span class="font-medium {{ $estaVencida ? 'text-rose-600 font-bold' : '' }}">
                                    {{ $c->fecha_vencimiento_pago->format('d/m/Y') }}
                                </span>
                                @if($c->saldo_pendiente > 0)
                                    @if($estaVencida)
                                    <div class="text-[10px] font-bold text-rose-500">Vencida hace {{ abs($diasRestantes) }} d</div>
                                    @else
                                    <div class="text-[10px] text-slate-400">En {{ $diasRestantes }} días</div>
                                    @endif
                                @endif
                            @else
                                <span class="text-slate-400">Sin fecha</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right font-medium">
                            {{ formato_moneda($c->total) }}
                        </td>
                        <td class="px-4 py-3 text-right font-semibold text-emerald-600 dark:text-emerald-400">
                            {{ formato_moneda($abonado) }}
                            <div class="text-[10px] text-slate-400">{{ $porcentajePagado }}%</div>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <span class="font-black {{ $c->saldo_pendiente > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-400' }}">
                                {{ formato_moneda($c->saldo_pendiente) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($c->saldo_pendiente <= 0)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                                Pagada
                            </span>
                            @elseif($estaVencida)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300">
                                Vencida
                            </span>
                            @elseif($abonado > 0)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                                Parcial
                            </span>
                            @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">
                                Pendiente
                            </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                @if($c->saldo_pendiente > 0)
                                <button type="button" 
                                        @click="abrirModalAbono({{ json_encode([
                                            'id' => $c->id,
                                            'numero' => $c->numero_comprobante ?? 'Compra #'.$c->id,
                                            'proveedor' => $c->proveedor->nombre ?? 'Proveedor',
                                            'saldo' => (float)$c->saldo_pendiente,
                                            'total' => (float)$c->total,
                                            'url' => route('cuentas-por-pagar.abonos.store', $c)
                                        ]) }})"
                                        class="px-2.5 py-1 rounded-lg text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 transition"
                                        title="Registrar Abono">
                                    Abonar
                                </button>
                                @endif
                                <a href="{{ route('cuentas-por-pagar.show', $c) }}" 
                                   class="p-1.5 rounded-lg text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                                   title="Ver Estado de Cuenta">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-slate-500 dark:text-slate-400">
                            No se encontraron compras a crédito registradas con los filtros actuales.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($compras->hasPages())
        <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-800">
            {{ $compras->links() }}
        </div>
        @endif
    </div>

    <!-- Modal Registrar Abono -->
    <div x-show="modalAbonoOpen" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
         @keydown.escape.window="modalAbonoOpen = false">
        
        <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-lg w-full p-6 border border-slate-200 dark:border-slate-800 shadow-2xl space-y-4"
             @click.outside="modalAbonoOpen = false">
            
            <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Registrar Abono a Proveedor</h3>
                    <p class="text-xs text-slate-500" x-text="compraSeleccionada.proveedor + ' (' + compraSeleccionada.numero + ')'"></p>
                </div>
                <button @click="modalAbonoOpen = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-lg font-bold">✕</button>
            </div>

            <!-- Balance Banner -->
            <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex justify-between items-center text-xs">
                <div>
                    <span class="text-slate-500">Saldo Pendiente:</span>
                    <p class="text-lg font-black text-rose-600 dark:text-rose-400" x-text="formatoMoneda(compraSeleccionada.saldo)"></p>
                </div>
                <button type="button" 
                        @click="montoAbono = compraSeleccionada.saldo"
                        class="px-2.5 py-1 rounded-lg text-xs font-bold text-emerald-700 dark:text-emerald-300 bg-emerald-100 dark:bg-emerald-950 border border-emerald-300 dark:border-emerald-800">
                    Pagar Totalidad
                </button>
            </div>

            <form :action="compraSeleccionada.url" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Monto a Abonar (C$) *</label>
                    <input type="number" 
                           name="monto" 
                           x-model="montoAbono" 
                           step="0.01" 
                           min="0.01" 
                           :max="compraSeleccionada.saldo" 
                           required 
                           class="w-full text-sm font-bold rounded-xl border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-emerald-500">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Forma de Pago *</label>
                        <select name="metodo_pago" x-model="metodoPago" required 
                                class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-emerald-500">
                            <option value="transferencia">Transferencia Bancaria</option>
                            <option value="efectivo">Efectivo</option>
                            <option value="cheque">Cheque</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Fecha de Pago</label>
                        <input type="date" name="fecha_pago" value="{{ now()->toDateString() }}" 
                               class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-emerald-500">
                    </div>
                </div>

                <div x-show="metodoPago === 'transferencia' || metodoPago === 'cheque'" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Banco Emisor / Receptor</label>
                        <select name="banco" class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-emerald-500">
                            <option value="">Seleccione Banco...</option>
                            <option value="BAC Credomatic">BAC Credomatic</option>
                            <option value="Banco LAFISE Bancentro">Banco LAFISE Bancentro</option>
                            <option value="Banpro Grupo Promerica">Banpro Grupo Promerica</option>
                            <option value="Banco Ficohsa">Banco Ficohsa</option>
                            <option value="Banco Avanz">Banco Avanz</option>
                            <option value="Banco BDF">Banco BDF</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">N° de Referencia / Cheque</label>
                        <input type="text" name="numero_referencia" placeholder="Ej: TR-984812"
                               class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-emerald-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Observaciones</label>
                    <textarea name="observaciones" rows="2" placeholder="Nota sobre el pago..."
                              class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-emerald-500"></textarea>
                </div>

                <div x-show="metodoPago === 'efectivo'">
                    <label class="inline-flex items-center text-xs text-slate-600 dark:text-slate-300 cursor-pointer">
                        <input type="checkbox" name="registrar_en_caja" value="1" checked class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 mr-2">
                        Registrar egreso automáticamente en la caja activa del turno
                    </label>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-200 dark:border-slate-800">
                    <button type="button" @click="modalAbonoOpen = false" 
                            class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                        Cancelar
                    </button>
                    <button type="submit" 
                            class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm transition">
                        Guardar Abono
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function cuentasPorPagarApp() {
    return {
        modalAbonoOpen: false,
        metodoPago: 'transferencia',
        montoAbono: 0,
        compraSeleccionada: {
            id: null,
            numero: '',
            proveedor: '',
            saldo: 0,
            total: 0,
            url: ''
        },

        abrirModalAbono(data) {
            this.compraSeleccionada = data;
            this.montoAbono = data.saldo;
            this.modalAbonoOpen = true;
        },

        formatoMoneda(val) {
            return 'C$ ' + Number(val).toLocaleString('es-NI', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
    };
}
</script>
@endsection
