@extends('layouts.app')

@section('title', 'Registro de Compras e Ingreso de Lotes - FarmaBien')

@section('content')
<div x-data="{
    modalAnular: false,
    compraAnularId: null,
    compraAnularDoc: '',
    motivoAnulacion: '',
    abrirModalAnular(id, doc) {
        this.compraAnularId = id;
        this.compraAnularDoc = doc;
        this.motivoAnulacion = '';
        this.modalAnular = true;
    }
}" class="space-y-5">
    
    <!-- Breadcrumbs -->
    <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-800 dark:text-slate-200 font-semibold">Compras y Recepción de Lotes</span>
    </nav>

    <!-- Header & Action Buttons -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <span>Compras y Facturas de Proveedores</span>
                <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                    {{ $compras->total() }} registros
                </span>
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Ingreso de mercadería, generación automática de lotes con fecha de vencimiento y actualización del Kardex.
            </p>
        </div>
        
        <div class="flex items-center space-x-2 shrink-0">
            <a href="{{ route('inventario.lotes') }}" 
               class="inline-flex items-center space-x-1.5 px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold border border-slate-200 dark:border-slate-700 shadow-2xs transition">
                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                <span>Kardex y Lotes</span>
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
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="bg-white dark:bg-slate-900 rounded-xl p-3.5 border border-slate-300 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Total Comprobantes</p>
                <p class="text-lg font-bold text-slate-900 dark:text-white mt-0.5">{{ $compras->total() }}</p>
            </div>
            <div class="w-9 h-9 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-xl p-3.5 border border-slate-300 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400">En esta página</p>
                <p class="text-lg font-bold text-emerald-600 dark:text-emerald-400 mt-0.5">{{ $compras->count() }}</p>
            </div>
            <div class="w-9 h-9 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-xl p-3.5 border border-slate-300 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Monto Página</p>
                <p class="text-lg font-bold text-slate-900 dark:text-white mt-0.5">${{ number_format($compras->sum('total'), 2) }}</p>
            </div>
            <div class="w-9 h-9 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center">
                <span class="text-xs font-bold text-slate-700 dark:text-slate-300">$</span>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-xl p-3.5 border border-slate-300 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Control Vencimientos</p>
                <a href="{{ route('inventario.alertas') }}" class="text-xs font-bold text-amber-600 dark:text-amber-400 hover:underline mt-0.5 block">Alertas de Lotes &rarr;</a>
            </div>
            <div class="w-9 h-9 rounded-lg bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-300 dark:border-slate-800 shadow-xs space-y-3">
        <form method="GET" action="{{ route('compras.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-12 gap-2.5">
            <!-- Search Input -->
            <div class="relative md:col-span-4">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" 
                       name="buscar" 
                       value="{{ request('buscar') }}" 
                       placeholder="Buscar por N° factura o proveedor..." 
                       class="w-full pl-9 pr-3 py-2 bg-slate-50 dark:bg-slate-800/90 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
            </div>

            <!-- Estado Filter -->
            <div class="md:col-span-2">
                <select name="estado" 
                        onchange="this.form.submit()" 
                        class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800/90 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                    <option value="">Estado: Todos</option>
                    <option value="recibida" {{ request('estado') === 'recibida' ? 'selected' : '' }}>Recibidas</option>
                    <option value="anulada" {{ request('estado') === 'anulada' ? 'selected' : '' }}>Anuladas</option>
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

            <!-- Fecha Hasta -->
            <div class="md:col-span-2">
                <input type="date" 
                       name="fecha_hasta" 
                       value="{{ request('fecha_hasta') }}" 
                       class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800/90 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition"
                       placeholder="Hasta">
            </div>

            <!-- Botones Acción -->
            <div class="md:col-span-2 flex items-center space-x-2">
                <button type="submit" 
                        class="flex-1 px-3 py-2 bg-slate-800 hover:bg-slate-900 dark:bg-slate-700 dark:hover:bg-slate-600 text-white rounded-xl text-xs font-semibold shadow-2xs transition cursor-pointer">
                    Filtrar
                </button>
                @if(request()->hasAny(['buscar', 'estado', 'fecha_desde', 'fecha_hasta']))
                <a href="{{ route('compras.index') }}" 
                   title="Limpiar filtros"
                   class="px-2.5 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-xl text-xs font-semibold border border-slate-300 dark:border-slate-700 transition">
                    ✕
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Compras Table -->
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-300 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-300 dark:border-slate-800 text-[11px] font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">
                        <th class="py-3 px-4">ID / Comprobante</th>
                        <th class="py-3 px-4">Proveedor</th>
                        <th class="py-3 px-4">Fecha</th>
                        <th class="py-3 px-4 text-center">Ítems</th>
                        <th class="py-3 px-4 text-right">Total</th>
                        <th class="py-3 px-4 text-center">Estado</th>
                        <th class="py-3 px-4">Registrado por</th>
                        <th class="py-3 px-4 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800 font-medium">
                    @forelse($compras as $compra)
                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition">
                        <!-- ID / Comprobante -->
                        <td class="py-3 px-4">
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('compras.show', $compra) }}" class="font-bold text-emerald-700 dark:text-emerald-400 hover:underline">
                                    #{{ str_pad($compra->id, 5, '0', STR_PAD_LEFT) }}
                                </a>
                                @if($compra->esModificacion())
                                    <span class="px-1.5 py-0.5 text-[9px] font-bold rounded bg-purple-100 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800" title="Reemplaza a la compra #{{ $compra->compra_original_id }}">
                                        v2
                                    </span>
                                @endif
                            </div>
                            <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 font-mono">
                                {{ $compra->numero_comprobante ? 'Doc: ' . $compra->numero_comprobante : 'Sin N° Doc' }}
                            </div>
                        </td>

                        <!-- Proveedor -->
                        <td class="py-3 px-4">
                            <div class="font-semibold text-slate-900 dark:text-white">
                                {{ $compra->proveedor->nombre ?? 'Proveedor no disponible' }}
                            </div>
                            @if($compra->proveedor && $compra->proveedor->ruc)
                                <div class="text-[10px] text-slate-400 dark:text-slate-500 font-mono">
                                    RUC/ID: {{ $compra->proveedor->ruc }}
                                </div>
                            @endif
                        </td>

                        <!-- Fecha -->
                        <td class="py-3 px-4 text-slate-700 dark:text-slate-300">
                            <div>{{ $compra->fecha ? $compra->fecha->format('d/m/Y') : '-' }}</div>
                            <div class="text-[10px] text-slate-400">{{ $compra->fecha ? $compra->fecha->format('H:i') : '' }}</div>
                        </td>

                        <!-- Ítems -->
                        <td class="py-3 px-4 text-center">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                {{ $compra->detalles_count }} prod.
                            </span>
                        </td>

                        <!-- Total -->
                        <td class="py-3 px-4 text-right font-bold text-slate-900 dark:text-white">
                            ${{ number_format($compra->total, 2) }}
                        </td>

                        <!-- Estado -->
                        <td class="py-3 px-4 text-center">
                            @if($compra->estado === 'recibida')
                                @if($compra->fueModificada())
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                        Modificada
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                        Recibida
                                    </span>
                                @endif
                            @elseif($compra->estado === 'anulada')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 dark:bg-rose-950/60 text-rose-800 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                    Anulada
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-800">
                                    {{ ucfirst($compra->estado) }}
                                </span>
                            @endif
                        </td>

                        <!-- Usuario Responsable -->
                        <td class="py-3 px-4 text-slate-600 dark:text-slate-400">
                            {{ $compra->usuario->name ?? 'Sistema' }}
                        </td>

                        <!-- Acciones Cuarteto -->
                        <td class="py-3 px-4 text-center whitespace-nowrap">
                            <div class="inline-flex items-center justify-center space-x-1">
                                <!-- Ver Detalle -->
                                <a href="{{ route('compras.show', $compra) }}" 
                                   title="Ver Ficha Completa"
                                   class="w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 inline-flex items-center justify-center transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>

                                <!-- Ticket -->
                                <a href="{{ route('compras.ticket', $compra) }}" 
                                   target="_blank"
                                   title="Imprimir Ticket"
                                   class="w-7 h-7 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 hover:bg-indigo-100 dark:hover:bg-indigo-900/60 text-indigo-600 dark:text-indigo-300 inline-flex items-center justify-center transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                </a>

                                <!-- PDF -->
                                <a href="{{ route('compras.pdf', $compra) }}" 
                                   title="Descargar PDF"
                                   class="w-7 h-7 rounded-lg bg-rose-50 dark:bg-rose-950/60 hover:bg-rose-100 dark:hover:bg-rose-900/60 text-rose-600 dark:text-rose-300 inline-flex items-center justify-center transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                </a>

                                <!-- Editar -->
                                @if($compra->puedeModificarse())
                                    @can('registrar compras')
                                    <a href="{{ route('compras.edit', $compra) }}" 
                                       title="Editar Compra"
                                       class="w-7 h-7 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 text-emerald-600 dark:text-emerald-400 inline-flex items-center justify-center transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    @endcan
                                @endif

                                <!-- Anular -->
                                @if($compra->puedeAnularse())
                                    @can('anular compras')
                                    <button type="button" 
                                            @click="abrirModalAnular({{ $compra->id }}, '{{ $compra->numero_comprobante ?? '#' . $compra->id }}')" 
                                            title="Anular Compra"
                                            class="w-7 h-7 rounded-lg bg-rose-50 dark:bg-rose-950/60 hover:bg-rose-100 dark:hover:bg-rose-900/60 text-rose-600 dark:text-rose-400 inline-flex items-center justify-center transition cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                    @endcan
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 mb-3">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-200">No se encontraron compras registradas</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-sm">
                                    Comience registrando una factura o boleta de compra para ingresar productos y lotes al Kardex.
                                </p>
                                @can('registrar compras')
                                <a href="{{ route('compras.create') }}" class="mt-4 inline-flex items-center space-x-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-xs transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    <span>Registrar Primera Compra</span>
                                </a>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($compras->hasPages())
        <div class="px-4 py-3 border-t border-slate-300 dark:border-slate-800">
            {{ $compras->links() }}
        </div>
        @endif
    </div>

    <!-- Modal Anular Compra -->
    <div x-show="modalAnular" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity"
         @keydown.escape.window="modalAnular = false">
        
        <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full p-5 border border-slate-300 dark:border-slate-800 shadow-xl space-y-4"
             @click.outside="modalAnular = false">
            
            <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 rounded-lg bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Anular Compra</h3>
                </div>
                <button @click="modalAnular = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">✕</button>
            </div>

            <p class="text-xs text-slate-600 dark:text-slate-300">
                ¿Está seguro de anular la compra <span class="font-bold text-slate-900 dark:text-white" x-text="compraAnularDoc"></span>? 
                Esta acción revertirá el stock de todos los lotes asociados y registrará la salida en el Kardex.
            </p>

            <form :action="'{{ url('compras') }}/' + compraAnularId + '/anular'" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Motivo de anulación <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="motivo" 
                              x-model="motivoAnulacion"
                              rows="2" 
                              required 
                              minlength="5"
                              placeholder="Ej: Factura cancelada por el proveedor o error en productos recibidos..."
                              class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-rose-500 focus:border-rose-500"></textarea>
                </div>

                <div class="flex items-center justify-end space-x-2 pt-2">
                    <button type="button" 
                            @click="modalAnular = false" 
                            class="px-3 py-1.5 rounded-xl border border-slate-300 dark:border-slate-700 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                        Cancelar
                    </button>
                    <button type="submit" 
                            :disabled="motivoAnulacion.trim().length < 5"
                            :class="motivoAnulacion.trim().length < 5 ? 'opacity-50 cursor-not-allowed' : ''"
                            class="px-3.5 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold shadow-xs transition">
                        Confirmar Anulación
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection