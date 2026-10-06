@extends('layouts.app')

@section('title', 'Cuentas por Pagar a Proveedores - FarmaBien')

@section('content')
<div class="space-y-5" x-data="cuentasPorPagarApp(@js($proveedores))">
    
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
                <span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                    {{ $compras->total() }} registros
                </span>
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Control de facturas a crédito, compras de insumos, vencimientos y registro de abonos a proveedores.
            </p>
        </div>
        
        <div class="flex items-center gap-2 shrink-0 flex-wrap">
            <!-- Navigation Button First -->
            <a href="{{ route('compras.index') }}" 
               class="inline-flex items-center space-x-1.5 px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-xl shadow-xs transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Compras</span>
            </a>

            <!-- Botón Modo Full -->
            <button type="button" 
                    @click="$dispatch('toggle-pos-fullscreen')"
                    title="Modo Pantalla Completa / Ocultar Barras"
                    class="inline-flex items-center space-x-1.5 px-3 py-2 rounded-xl bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold shadow-2xs transition cursor-pointer">
                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                <span>Modo Full</span>
            </button>

            <!-- Registrar Deuda / Factura Directa -->
            <button type="button" 
                    @click="abrirModalDirecta()" 
                    class="inline-flex items-center space-x-1.5 px-3.5 py-2 rounded-xl bg-indigo-50/80 hover:bg-indigo-100/90 dark:bg-indigo-950/40 dark:hover:bg-indigo-900/60 text-indigo-900 dark:text-indigo-300 text-xs font-bold border border-indigo-200 dark:border-indigo-800/80 shadow-2xs transition cursor-pointer">
                <svg class="w-4 h-4 text-indigo-700 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>+ Factura / Gasto Directo</span>
            </button>

            <!-- Registrar Compra de Inventario -->
            @can('registrar compras')
            <a href="{{ route('compras.create') }}" 
               class="inline-flex items-center space-x-1.5 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-bold rounded-xl shadow-xs transition cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>+ Compra Inventario</span>
            </a>
            @endcan
        </div>
    </div>

    <!-- Quick Stats Cards Row -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-900 dark:text-slate-300">Deuda Total Pendiente</p>
                <p class="text-xl font-extrabold text-slate-900 dark:text-white mt-0.5">{{ formato_moneda($metricas['total_deuda']) }}</p>
                <p class="text-[11px] text-slate-500 mt-0.5">{{ $metricas['total_facturas_pendientes'] }} factura(s)</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-900 dark:text-slate-300">Deuda Vencida (Mora)</p>
                <p class="text-xl font-extrabold text-rose-900 dark:text-rose-400 mt-0.5">{{ formato_moneda($metricas['deuda_vencida']) }}</p>
                <p class="text-[11px] text-red-950 dark:text-red-300 font-bold mt-0.5">Atención prioritaria</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-900 dark:text-slate-300">Vence en ≤ 7 Días</p>
                <p class="text-xl font-extrabold text-amber-900 dark:text-amber-400 mt-0.5">{{ formato_moneda($metricas['deuda_por_vencer_7_dias']) }}</p>
                <p class="text-[11px] text-amber-950 dark:text-amber-300 font-bold mt-0.5">Programar pagos</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-900 dark:text-slate-300">Bancos Autorizados</p>
                <p class="text-xs font-bold text-slate-800 dark:text-slate-200 mt-0.5">LAFISE • BAC • Banpro</p>
                <p class="text-[11px] text-slate-400">Ficohsa • Avanz • BDF</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/></svg>
            </div>
        </div>
    </div>

    <!-- Filters & State Tabs -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs space-y-3">
        <!-- Tabs -->
        <div class="flex items-center space-x-2 border-b border-slate-200 dark:border-slate-800 pb-3 overflow-x-auto">
            <a href="{{ route('cuentas-por-pagar.index', array_merge(request()->except('estado_pago', 'page'), ['estado_pago' => 'pendientes'])) }}"
               class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition whitespace-nowrap {{ $filtroEstado === 'pendientes' ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900' : 'text-slate-700 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                Pendientes y Parciales
            </a>
            <a href="{{ route('cuentas-por-pagar.index', array_merge(request()->except('estado_pago', 'page'), ['estado_pago' => 'vencidas'])) }}"
               class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition whitespace-nowrap {{ $filtroEstado === 'vencidas' ? 'bg-rose-600 text-white' : 'text-rose-900 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40' }}">
                Vencidas
            </a>
            <a href="{{ route('cuentas-por-pagar.index', array_merge(request()->except('estado_pago', 'page'), ['estado_pago' => 'pagadas'])) }}"
               class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition whitespace-nowrap {{ $filtroEstado === 'pagadas' ? 'bg-emerald-600 text-white' : 'text-slate-700 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                Pagadas Totalmente
            </a>
        </div>

        <!-- Filter Form -->
        <form method="GET" action="{{ route('cuentas-por-pagar.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
            <input type="hidden" name="estado_pago" value="{{ $filtroEstado }}">

            <!-- Buscar -->
            <div class="sm:col-span-5 relative">
                <input type="text" name="buscar" value="{{ request('buscar') }}" 
                       placeholder="Buscar por N° Factura, Proveedor..."
                       class="w-full pl-9 pr-3.5 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white font-medium focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </div>

            <!-- Proveedor Select -->
            <div class="sm:col-span-3">
                <select name="proveedor_id" 
                        class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white font-medium focus:ring-2 focus:ring-emerald-500">
                    <option value="">Todos los Proveedores</option>
                    @foreach($proveedores as $prov)
                    <option value="{{ $prov->id }}" {{ request('proveedor_id') == $prov->id ? 'selected' : '' }}>
                        {{ $prov->nombre ?? $prov->nombre_empresa }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-4 flex items-center gap-2 justify-end">
                <button type="submit" 
                        class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition shadow-xs">
                    Filtrar
                </button>
                @if(request()->hasAny(['buscar', 'proveedor_id', 'fecha_desde', 'fecha_hasta']))
                <a href="{{ route('cuentas-por-pagar.index', ['estado_pago' => $filtroEstado]) }}" 
                   class="px-3 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 text-xs font-semibold rounded-xl transition">
                    Limpiar
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table of Accounts Payable -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50/75 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800 text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                        <th class="px-4 py-3">Factura / Compra</th>
                        <th class="px-4 py-3">Proveedor</th>
                        <th class="px-4 py-3 text-center">Vencimiento</th>
                        <th class="px-4 py-3 text-right">Total Factura</th>
                        <th class="px-4 py-3 text-right">Saldo Pendiente</th>
                        <th class="px-4 py-3 text-center">Estado Pago</th>
                        <th class="px-4 py-3 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    @forelse($compras as $compra)
                    @php
                        $diasRestantes = now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($compra->fecha_vencimiento_pago)->startOfDay(), false);
                        $estaVencida = $diasRestantes < 0 && $compra->saldo_pendiente > 0;
                    @endphp
                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition">
                        <!-- Factura / Fecha -->
                        <td class="px-4 py-3">
                            <span class="font-bold text-slate-900 dark:text-white block font-mono">
                                {{ $compra->numero_comprobante ?: 'COMP-' . str_pad($compra->id, 5, '0', STR_PAD_LEFT) }}
                            </span>
                            <span class="text-[11px] text-slate-400">
                                Emisión: {{ \Carbon\Carbon::parse($compra->fecha)->format('d/m/Y') }}
                            </span>
                        </td>

                        <!-- Proveedor -->
                        <td class="px-4 py-3">
                            <span class="font-bold text-slate-800 dark:text-slate-200 block">
                                {{ $compra->proveedor->nombre_empresa ?? $compra->proveedor->nombre ?? 'Proveedor no asignado' }}
                            </span>
                            <span class="text-[11px] text-slate-400" x-show="{{ $compra->proveedor?->telefono ? 'true' : 'false' }}">
                                Tel: {{ $compra->proveedor?->telefono ?? '—' }}
                            </span>
                        </td>

                        <!-- Vencimiento -->
                        <td class="px-4 py-3 text-center whitespace-nowrap">
                            @if($compra->fecha_vencimiento_pago)
                                <span class="font-semibold text-slate-800 dark:text-slate-200 block">
                                    {{ \Carbon\Carbon::parse($compra->fecha_vencimiento_pago)->format('d/m/Y') }}
                                </span>
                                @if($compra->saldo_pendiente > 0)
                                    @if($estaVencida)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300">
                                        Vencida hace {{ abs($diasRestantes) }} d.
                                    </span>
                                    @elseif($diasRestantes == 0)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300">
                                        Vence Hoy
                                    </span>
                                    @else
                                    <span class="text-[10px] text-slate-400">
                                        Vence en {{ $diasRestantes }} d.
                                    </span>
                                    @endif
                                @endif
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>

                        <!-- Total Factura -->
                        <td class="px-4 py-3 text-right font-mono font-bold text-slate-800 dark:text-slate-200">
                            {{ formato_moneda($compra->total) }}
                        </td>

                        <!-- Saldo Pendiente -->
                        <td class="px-4 py-3 text-right font-mono">
                            @if($compra->saldo_pendiente > 0)
                                <span class="font-extrabold text-sm text-rose-900 dark:text-rose-400">
                                    {{ formato_moneda($compra->saldo_pendiente) }}
                                </span>
                            @else
                                <span class="font-bold text-xs text-emerald-900 dark:text-emerald-400">
                                    C$ 0.00
                                </span>
                            @endif
                        </td>

                        <!-- Estado Pago -->
                        <td class="px-4 py-3 text-center whitespace-nowrap">
                            @if($compra->saldo_pendiente <= 0)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-900 border border-emerald-300 dark:bg-emerald-950 dark:text-emerald-300 dark:border-emerald-800">
                                    Pagado
                                </span>
                            @elseif($compra->saldo_pendiente < $compra->total)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-300 dark:bg-amber-950 dark:text-amber-300 dark:border-amber-800">
                                    Parcial
                                </span>
                            @elseif($estaVencida)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-900 border border-rose-300 dark:bg-rose-950 dark:text-rose-300 dark:border-rose-800">
                                    Mora
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-900 border border-blue-300 dark:bg-blue-950 dark:text-blue-300 dark:border-blue-800">
                                    Pendiente
                                </span>
                            @endif
                        </td>

                        <!-- Acciones -->
                        <td class="px-4 py-3 text-center whitespace-nowrap">
                            <div class="inline-flex items-center justify-center gap-1.5">
                                @if($compra->saldo_pendiente > 0)
                                <button type="button" 
                                        @click="abrirModalAbono({
                                            id: {{ $compra->id }},
                                            numero: '{{ $compra->numero_comprobante ?: 'COMP-' . $compra->id }}',
                                            proveedor: '{{ addslashes($compra->proveedor->nombre_empresa ?? $compra->proveedor->nombre ?? 'Proveedor') }}',
                                            saldo: {{ $compra->saldo_pendiente }},
                                            total: {{ $compra->total }},
                                            url: '{{ route('cuentas-por-pagar.abonos.store', $compra) }}'
                                        })"
                                        title="Registrar Abono"
                                        class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-900 dark:text-emerald-300 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 border border-emerald-300 dark:border-emerald-800/60 inline-flex items-center justify-center transition shadow-2xs cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                </button>
                                @endif

                                <a href="{{ route('cuentas-por-pagar.show', $compra) }}" 
                                   title="Ver Estado de Cuenta y Pagos"
                                   class="w-8 h-8 rounded-lg bg-sky-50 dark:bg-sky-950/60 text-sky-900 dark:text-sky-300 hover:bg-sky-100 dark:hover:bg-sky-900/60 border border-sky-300 dark:border-sky-800/60 inline-flex items-center justify-center transition shadow-2xs">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-slate-500 dark:text-slate-400">
                            <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 flex items-center justify-center mx-auto mb-2">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <p class="font-bold text-slate-700 dark:text-slate-300">No hay cuentas por pagar registradas</p>
                            <p class="text-xs text-slate-400 mt-0.5">Las compras a crédito y gastos con proveedores aparecerán aquí automáticamente.</p>
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

    <!-- ============================================================== -->
    <!-- MODAL 1: REGISTRAR ABONO A PROVEEDOR                          -->
    <!-- ============================================================== -->
    <template x-teleport="body">
    <div x-show="modalAbonoOpen" 
         x-cloak
         class="fixed inset-0 z-[9999] p-3 sm:p-6 flex items-center justify-center bg-slate-950/75 backdrop-blur-sm transition-opacity overflow-y-auto"
         @keydown.escape.window="modalAbonoOpen = false">
        
        <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-lg w-full p-5 sm:p-6 border border-slate-200 dark:border-slate-800 shadow-2xl my-auto flex flex-col max-h-[calc(100vh-2.5rem)] overflow-hidden"
             @click.outside="modalAbonoOpen = false">
            
            <!-- Modal Header (Fixed) -->
            <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800 shrink-0">
                <div class="flex items-center space-x-2.5">
                    <div class="w-9 h-9 rounded-xl bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Registrar Abono a Proveedor</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 truncate max-w-[280px]" x-text="compraSeleccionada.proveedor + ' (' + compraSeleccionada.numero + ')'"></p>
                    </div>
                </div>
                <button type="button" @click="modalAbonoOpen = false" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer" title="Cerrar">✕</button>
            </div>

            <!-- Form Container -->
            <form :action="compraSeleccionada.url" method="POST" x-ref="formAbono" @submit.prevent="solicitarGuardarAbono()" class="flex flex-col flex-1 overflow-hidden">
                @csrf
                
                <!-- Scrollable Form Body -->
                <div class="space-y-4 py-3 overflow-y-auto pr-1 flex-1 custom-scrollbar">
                    <!-- Balance Banner -->
                    <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 flex justify-between items-center text-xs">
                        <div>
                            <span class="text-slate-500 dark:text-slate-400 font-medium block">Saldo Pendiente:</span>
                            <p class="text-base font-black text-rose-600 dark:text-rose-400 mt-0.5" x-text="formatoMoneda(compraSeleccionada.saldo)"></p>
                        </div>
                        <button type="button" 
                                @click="montoAbono = compraSeleccionada.saldo"
                                class="px-3 py-1.5 rounded-lg text-xs font-bold text-emerald-700 dark:text-emerald-300 bg-emerald-100 dark:bg-emerald-950 border border-emerald-300 dark:border-emerald-800 hover:bg-emerald-200 transition cursor-pointer">
                            Pagar Totalidad
                        </button>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Monto a Abonar (C$) <span class="text-rose-500">*</span></label>
                        <input type="number" 
                               name="monto" 
                               x-model="montoAbono" 
                               step="0.01" 
                               min="0.01" 
                               :max="compraSeleccionada.saldo" 
                               required 
                               class="w-full px-3.5 py-2 text-sm font-bold rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Forma de Pago <span class="text-rose-500">*</span></label>
                            <select name="metodo_pago" x-model="metodoPago" required 
                                    class="w-full px-3 py-2 text-xs font-semibold rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                                <option value="transferencia">Transferencia Bancaria</option>
                                <option value="efectivo">Efectivo</option>
                                <option value="cheque">Cheque</option>
                                <option value="otro">Otro</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Fecha de Pago</label>
                            <input type="date" name="fecha_pago" value="{{ now()->toDateString() }}" 
                                   class="w-full px-3 py-2 text-xs font-semibold rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                        </div>
                    </div>

                    <div x-show="metodoPago === 'transferencia' || metodoPago === 'cheque'" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Banco Emisor / Receptor</label>
                            <select name="banco" class="w-full px-3 py-2 text-xs font-semibold rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
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
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">N° Referencia / Cheque</label>
                            <input type="text" name="numero_referencia" placeholder="Ej: TR-984812"
                                   class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Observaciones</label>
                        <textarea name="observaciones" rows="2" placeholder="Nota o detalle sobre el abono..."
                                  class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500"></textarea>
                    </div>

                    <div x-show="metodoPago === 'efectivo'" class="pt-1">
                        <label class="inline-flex items-center text-xs text-slate-700 dark:text-slate-300 font-medium cursor-pointer">
                            <input type="checkbox" name="registrar_en_caja" value="1" x-model="registrarEnCaja" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 mr-2">
                            Registrar egreso automáticamente en la caja activa del turno
                        </label>
                    </div>
                </div>

                <!-- Modal Footer (Fixed at Bottom) -->
                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-200 dark:border-slate-800 shrink-0">
                    <button type="button" @click="modalAbonoOpen = false" 
                            class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer">
                        Cancelar
                    </button>
                    <button type="submit" 
                            class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-xs transition cursor-pointer">
                        Guardar Abono
                    </button>
                </div>
            </form>
        </div>
    </div>
    </template>

    <!-- ============================================================== -->
    <!-- MODAL 2: REGISTRAR CUENTA POR PAGAR DIRECTA (SERVICIOS/INSUMOS)-->
    <!-- ============================================================== -->
    <template x-teleport="body">
    <div x-show="modalDirectaOpen" 
         x-cloak
         class="fixed inset-0 z-[9999] p-3 sm:p-6 flex items-center justify-center bg-slate-950/75 backdrop-blur-sm transition-opacity overflow-y-auto"
         @keydown.escape.window="modalDirectaOpen = false">
        
        <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-xl w-full p-5 sm:p-6 border border-slate-200 dark:border-slate-800 shadow-2xl my-auto flex flex-col max-h-[calc(100vh-2.5rem)] overflow-hidden"
             @click.outside="modalDirectaOpen = false">
            
            <!-- Modal Header (Fixed) -->
            <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800 shrink-0">
                <div class="flex items-center space-x-2.5">
                    <div class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Nueva Factura / Gasto a Crédito</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Registra deudas con proveedores sin requerir inventario de medicamentos</p>
                    </div>
                </div>
                <button type="button" @click="modalDirectaOpen = false" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer" title="Cerrar">✕</button>
            </div>

            <!-- Form Container -->
            <form action="{{ route('cuentas-por-pagar.directa.store') }}" method="POST" class="flex flex-col flex-1 overflow-hidden">
                @csrf

                <!-- Scrollable Form Body -->
                <div class="space-y-4 py-3 overflow-y-auto pr-1 flex-1 custom-scrollbar">
                    <!-- Proveedor con Buscador AJAX / Predictivo -->
                    <div class="relative z-30" @click.outside="provDropdown = false">
                        <label class="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-1">
                            Proveedor / Acreedor <span class="text-rose-500">*</span>
                        </label>
                        <input type="hidden" name="proveedor_id" :value="directa.proveedor_id">

                        <!-- Proveedor Seleccionado -->
                        <template x-if="directa.proveedorSeleccionado">
                            <div class="flex items-center justify-between px-3.5 py-2 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-300 dark:border-emerald-700 rounded-xl text-xs">
                                <div class="flex items-center space-x-2 truncate">
                                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shrink-0"></span>
                                    <span class="font-bold text-emerald-950 dark:text-emerald-200 truncate" x-text="directa.proveedorSeleccionado.nombre_empresa || directa.proveedorSeleccionado.nombre"></span>
                                    <span class="text-emerald-700 dark:text-emerald-400 font-mono text-[11px]" x-show="directa.proveedorSeleccionado.ruc" x-text="'• RUC: ' + directa.proveedorSeleccionado.ruc"></span>
                                </div>
                                <button type="button" @click="directa.proveedor_id = ''; directa.proveedorSeleccionado = null; provQuery = ''" class="text-[11px] font-bold text-rose-600 dark:text-rose-400 hover:underline shrink-0 ml-2 cursor-pointer">
                                    Cambiar
                                </button>
                            </div>
                        </template>

                        <!-- Input de Búsqueda de Proveedor -->
                        <template x-if="!directa.proveedorSeleccionado">
                            <div class="relative">
                                <input type="text" 
                                       name="proveedor_nombre"
                                       x-model="provQuery" 
                                       @focus="provDropdown = true"
                                       @input="provDropdown = true"
                                       placeholder="Escribe nombre o RUC del proveedor..."
                                       class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-medium focus:ring-2 focus:ring-emerald-500">
                                
                                <div x-show="provDropdown && filtrarProveedores().length > 0"
                                     x-cloak
                                     class="absolute left-0 right-0 top-full mt-1 z-50 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-xl max-h-48 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-700/50">
                                    <template x-for="p in filtrarProveedores()" :key="p.id">
                                        <button type="button" 
                                                @click="seleccionarProveedor(p)"
                                                class="w-full px-3.5 py-2 text-left hover:bg-emerald-50 dark:hover:bg-slate-700 flex items-center justify-between transition cursor-pointer">
                                            <div>
                                                <span class="text-xs font-bold text-slate-800 dark:text-slate-100 block" x-text="p.nombre_empresa || p.nombre"></span>
                                                <span class="text-[10px] text-slate-400" x-text="p.contacto ? 'Contacto: ' + p.contacto : (p.ruc ? 'RUC: ' + p.ruc : '')"></span>
                                            </div>
                                            <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400">Elegir →</span>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <!-- N° Factura -->
                        <div>
                            <label class="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-1">N° Factura / Documento <span class="text-rose-500">*</span></label>
                            <input type="text" name="numero_comprobante" required placeholder="Ej: F001-00293"
                                   class="w-full px-3.5 py-2 text-xs font-mono rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                        </div>

                        <!-- Monto Total (C$) -->
                        <div>
                            <label class="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-1">Monto Total de la Factura (C$) <span class="text-rose-500">*</span></label>
                            <input type="number" step="0.01" min="0.01" name="total" required placeholder="0.00"
                                   class="w-full px-3.5 py-2 text-xs font-bold rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 text-right">
                        </div>
                    </div>

                    <!-- Concepto / Detalle -->
                    <div>
                        <label class="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-1">Concepto / Descripción del Gasto</label>
                        <input type="text" name="concepto" placeholder="Ej: Insumos de empaque, Servicio de transporte, Mantenimiento..."
                               class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <!-- Fechas y Plazos -->
                    <div class="p-3.5 rounded-xl bg-amber-50/70 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/60 space-y-3">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-amber-900 dark:text-amber-300 mb-1">Fecha Emisión <span class="text-rose-500">*</span></label>
                                <input type="date" name="fecha" x-model="directa.fecha" @change="calcularVencimientoDirecta()" required
                                       class="w-full px-3 py-1.5 text-xs font-bold rounded-xl border border-amber-300 dark:border-amber-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-amber-900 dark:text-amber-300 mb-1">Fecha de Vencimiento</label>
                                <input type="date" name="fecha_vencimiento_pago" x-model="directa.fecha_vencimiento_pago" required
                                       class="w-full px-3 py-1.5 text-xs font-bold rounded-xl border border-amber-300 dark:border-amber-700 bg-white dark:bg-slate-800 text-amber-900 dark:text-amber-200">
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-2 pt-1">
                            <span class="text-xs font-bold text-amber-900 dark:text-amber-300">Plazo de Crédito:</span>
                            <div class="flex items-center gap-1">
                                <button type="button" @click="setDiasDirecta(15)" :class="directa.dias_credito == 15 ? 'bg-amber-600 text-white' : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-700'" class="px-2.5 py-1 rounded-lg text-xs font-bold transition cursor-pointer">15d</button>
                                <button type="button" @click="setDiasDirecta(30)" :class="directa.dias_credito == 30 ? 'bg-amber-600 text-white' : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-700'" class="px-2.5 py-1 rounded-lg text-xs font-bold transition cursor-pointer">30d</button>
                                <button type="button" @click="setDiasDirecta(45)" :class="directa.dias_credito == 45 ? 'bg-amber-600 text-white' : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-700'" class="px-2.5 py-1 rounded-lg text-xs font-bold transition cursor-pointer">45d</button>
                                <button type="button" @click="setDiasDirecta(60)" :class="directa.dias_credito == 60 ? 'bg-amber-600 text-white' : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-700'" class="px-2.5 py-1 rounded-lg text-xs font-bold transition cursor-pointer">60d</button>
                            </div>
                            <input type="number" name="dias_credito" x-model="directa.dias_credito" @input="calcularVencimientoDirecta()" min="1" max="365" class="w-16 px-2 py-1 text-center text-xs font-bold rounded-lg border-amber-300 dark:border-amber-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                            <span class="text-xs text-amber-800 dark:text-amber-400 font-semibold">días</span>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer (Fixed at Bottom) -->
                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-200 dark:border-slate-800 shrink-0">
                    <button type="button" @click="modalDirectaOpen = false" 
                            class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer">
                        Cancelar
                    </button>
                    <button type="submit" 
                            class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 dark:bg-slate-700 dark:hover:bg-slate-600 shadow-xs transition cursor-pointer">
                        Guardar Cuenta por Pagar
                    </button>
                </div>
            </form>
        </div>
    </div>
    </template>

    <!-- ============================================================== -->
    <!-- MODAL 3: CONFIRMACIÓN DE SEGURIDAD DE EGRESO DE CAJA          -->
    <!-- ============================================================== -->
    <template x-teleport="body">
    <div x-show="modalConfirmEgresoOpen" 
         x-cloak
         class="fixed inset-0 z-[10000] p-4 flex items-center justify-center bg-slate-950/80 backdrop-blur-xs transition-opacity"
         @keydown.escape.window="modalConfirmEgresoOpen = false">
        
        <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full p-5 sm:p-6 border border-amber-300 dark:border-amber-700 shadow-2xl space-y-4 text-center"
             @click.outside="modalConfirmEgresoOpen = false">
            
            <div class="w-14 h-14 mx-auto rounded-2xl bg-amber-100 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center text-2xl shadow-inner">
                ⚠️
            </div>

            <div class="space-y-2">
                <h3 class="text-base font-bold text-slate-900 dark:text-white">
                    Confirmación de Egreso de Caja
                </h3>
                <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                    ¿Está seguro de que desea registrar un egreso por <strong class="text-slate-950 dark:text-white font-extrabold text-sm" x-text="formatoMoneda(montoAbono)"></strong> de la <strong class="text-emerald-950 dark:text-emerald-300 font-extrabold">{{ $sesionCaja?->caja?->nombre ?? 'Caja Principal 01' }}</strong>?
                </p>
                <p class="text-[11px] font-medium text-amber-900 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/40 p-2.5 rounded-xl border border-amber-200 dark:border-amber-800/60">
                    <em>Esta acción descontará inmediatamente el efectivo del arqueo del turno actual.</em>
                </p>
            </div>

            <div class="flex items-center justify-center gap-3 pt-2">
                <button type="button" 
                        @click="modalConfirmEgresoOpen = false" 
                        class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition cursor-pointer">
                    Cancelar
                </button>
                <button type="button" 
                        @click="confirmarYEnviarAbono()" 
                        class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 shadow-md transition cursor-pointer">
                    Sí, Confirmar Egreso
                </button>
            </div>
        </div>
    </div>
    </template>

</div>

<script>
function cuentasPorPagarApp(proveedoresLista = []) {
    return {
        modalAbonoOpen: false,
        modalDirectaOpen: false,
        modalConfirmEgresoOpen: false,
        metodoPago: 'transferencia',
        registrarEnCaja: true,
        montoAbono: 0,
        proveedores: proveedoresLista,
        provQuery: '',
        provDropdown: false,

        compraSeleccionada: {
            id: null,
            numero: '',
            proveedor: '',
            saldo: 0,
            total: 0,
            url: ''
        },

        directa: {
            proveedor_id: '',
            proveedorSeleccionado: null,
            fecha: new Date().toISOString().split('T')[0],
            dias_credito: 30,
            fecha_vencimiento_pago: ''
        },

        init() {
            this.calcularVencimientoDirecta();
        },

        abrirModalAbono(data) {
            this.compraSeleccionada = data;
            this.montoAbono = data.saldo;
            this.modalConfirmEgresoOpen = false;
            this.modalAbonoOpen = true;
        },

        solicitarGuardarAbono() {
            if (this.metodoPago === 'efectivo' && this.registrarEnCaja) {
                this.modalConfirmEgresoOpen = true;
            } else {
                this.$refs.formAbono.submit();
            }
        },

        confirmarYEnviarAbono() {
            this.modalConfirmEgresoOpen = false;
            this.$refs.formAbono.submit();
        },

        abrirModalDirecta() {
            this.directa.proveedor_id = '';
            this.directa.proveedorSeleccionado = null;
            this.provQuery = '';
            this.directa.fecha = new Date().toISOString().split('T')[0];
            this.directa.dias_credito = 30;
            this.calcularVencimientoDirecta();
            this.modalDirectaOpen = true;
        },

        filtrarProveedores() {
            if (!this.provQuery.trim()) return this.proveedores.slice(0, 10);
            const q = this.provQuery.toLowerCase();
            return this.proveedores.filter(p => {
                const nombre = (p.nombre_empresa || p.nombre || '').toLowerCase();
                const contacto = (p.contacto || p.nombre_contacto || '').toLowerCase();
                const ruc = (p.ruc || '').toLowerCase();
                return nombre.includes(q) || contacto.includes(q) || ruc.includes(q);
            }).slice(0, 10);
        },

        seleccionarProveedor(prov) {
            this.directa.proveedor_id = prov.id;
            this.directa.proveedorSeleccionado = prov;
            this.provQuery = prov.nombre_empresa || prov.nombre;
            this.provDropdown = false;
        },

        setDiasDirecta(dias) {
            this.directa.dias_credito = dias;
            this.calcularVencimientoDirecta();
        },

        calcularVencimientoDirecta() {
            if (!this.directa.fecha) return;
            const d = new Date(this.directa.fecha + 'T00:00:00');
            d.setDate(d.getDate() + parseInt(this.directa.dias_credito || 0));
            this.directa.fecha_vencimiento_pago = d.toISOString().split('T')[0];
        },

        formatoMoneda(val) {
            return 'C$ ' + Number(val || 0).toLocaleString('es-NI', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
    };
}
</script>
@endsection
