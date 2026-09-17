@extends('layouts.app')

@section('title', 'Gestión de Compras - FarmaBien')

@section('content')
<div class="space-y-6">
    <!-- Header Page Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200 dark:border-slate-700">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <svg class="w-7 h-7 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
                Gestión de Compras e Ingreso de Lotes
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Registra compras de proveedores, ingresa lotes de productos y controla fechas de vencimiento.
            </p>
        </div>

        @can('registrar compras')
            <a href="{{ route('compras.create') }}"
               class="inline-flex items-center px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-medium text-sm rounded-xl shadow-sm hover:shadow transition-all duration-200 gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>Nueva Compra</span>
            </a>
        @endcan
    </div>

    @php
        $statsTotal = $compras->total();
        $statsRecibidas = $compras->where('estado', 'recibida')->count();
        $statsAnuladas = $compras->where('estado', 'anulada')->count();
        $statsMontoRecibidas = $compras->where('estado', 'recibida')->sum('total');
    @endphp

    <!-- Indicadores / Métricas -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-800 rounded-xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Compras</p>
                <p class="text-2xl font-bold text-slate-900 dark:text-white mt-1">{{ $statsTotal }}</p>
            </div>
            <div class="p-3 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-xl">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Recibidas</p>
                <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">{{ $statsRecibidas }}</p>
            </div>
            <div class="p-3 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-xl">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Anuladas</p>
                <p class="text-2xl font-bold text-rose-600 dark:text-rose-400 mt-1">{{ $statsAnuladas }}</p>
            </div>
            <div class="p-3 bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 rounded-xl">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </div>
        </div>

        <div class="bg-gradient-to-br from-emerald-600 to-teal-700 rounded-xl p-5 text-white shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-medium uppercase tracking-wider text-emerald-100">Monto Invertido</p>
                <p class="text-2xl font-bold mt-1">C$ {{ number_format($statsMontoRecibidas, 2) }}</p>
            </div>
            <div class="p-3 bg-white/20 rounded-xl">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- Filtros & Listado Tabla -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/80">
            <form method="GET" action="{{ route('compras.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
                <div>
                    <input type="text" name="buscar" value="{{ request('buscar') }}"
                           placeholder="Buscar por N° comprobante o proveedor..."
                           class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white shadow-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                </div>

                <div>
                    <select name="estado" class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white shadow-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="">Todos los Estados</option>
                        <option value="recibida" {{ request('estado') == 'recibida' ? 'selected' : '' }}>Recibida</option>
                        <option value="anulada" {{ request('estado') == 'anulada' ? 'selected' : '' }}>Anulada</option>
                    </select>
                </div>

                <div>
                    <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}"
                           class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white shadow-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="flex-1 py-2 px-3 bg-slate-800 dark:bg-slate-700 hover:bg-slate-900 text-white font-medium text-xs rounded-xl transition">
                        Filtrar
                    </button>
                    @if(request()->hasAny(['buscar', 'estado', 'fecha_desde']))
                        <a href="{{ route('compras.index') }}" class="py-2 px-3 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 font-medium text-xs rounded-xl hover:bg-slate-300 transition">
                            Limpiar
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-100 dark:bg-slate-700/50 text-slate-600 dark:text-slate-300 uppercase font-semibold tracking-wider">
                        <th class="py-3 px-4">Compra / Fecha</th>
                        <th class="py-3 px-4">Comprobante</th>
                        <th class="py-3 px-4">Proveedor</th>
                        <th class="py-3 px-4 text-center">Ítems / Lotes</th>
                        <th class="py-3 px-4 text-right">Total (C$)</th>
                        <th class="py-3 px-4 text-center">Estado</th>
                        <th class="py-3 px-4 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700 text-slate-700 dark:text-slate-200">
                    @forelse($compras as $compra)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors {{ $compra->estado == 'anulada' ? 'opacity-60 bg-rose-50/20' : '' }}">
                            <td class="py-3 px-4 font-semibold text-slate-900 dark:text-white">
                                <div class="font-bold">#{{ str_pad((string) $compra->id, 5, '0', STR_PAD_LEFT) }}</div>
                                <div class="text-[11px] font-normal text-slate-500 dark:text-slate-400">
                                    {{ $compra->fecha?->format('d/m/Y H:i') ?? '—' }}
                                </div>
                            </td>

                            <td class="py-3 px-4">
                                <span class="font-medium text-slate-800 dark:text-slate-200">
                                    {{ $compra->numero_comprobante ?? 'Sin Comprobante' }}
                                </span>
                            </td>

                            <td class="py-3 px-4">
                                <div class="font-semibold text-slate-900 dark:text-white">{{ $compra->proveedor->nombre }}</div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400">RUC: {{ $compra->proveedor->ruc ?? 'N/A' }}</div>
                            </td>

                            <td class="py-3 px-4 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300">
                                    {{ $compra->detalles_count ?? $compra->detalles->count() }} Lote(s)
                                </span>
                            </td>

                            <td class="py-3 px-4 text-right font-bold text-slate-900 dark:text-white">
                                C$ {{ number_format($compra->total, 2) }}
                            </td>

                            <td class="py-3 px-4 text-center">
                                @if($compra->estado == 'recibida')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 dark:bg-emerald-900/30 text-emerald-800 dark:text-emerald-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                                        Recibida
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 dark:bg-rose-900/30 text-rose-800 dark:text-rose-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500 mr-1.5"></span>
                                        Anulada
                                    </span>
                                @endif
                            </td>

                            <td class="py-3 px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    @can('ver compras')
                                        <a href="{{ route('compras.show', $compra) }}"
                                           class="p-1.5 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900/50 rounded-lg transition"
                                           title="Ver Detalle">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>
                                    @endcan

                                    @can('registrar compras')
                                        @if($compra->puedeModificarse())
                                            <a href="{{ route('compras.edit', $compra) }}"
                                               class="p-1.5 bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 hover:bg-amber-100 rounded-lg transition"
                                               title="Modificar Compra">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </a>
                                        @endif
                                    @endcan

                                    @can('anular compras')
                                        @if($compra->estado == 'recibida')
                                            <button type="button" onclick="modalAnular({{ $compra->id }})"
                                                    class="p-1.5 bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 hover:bg-rose-100 rounded-lg transition"
                                                    title="Anular Compra">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-500 dark:text-slate-400">
                                <svg class="w-12 h-12 mx-auto text-slate-300 dark:text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p class="text-sm font-semibold">No hay compras registradas</p>
                                <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Intenta ajustar los filtros de búsqueda</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($compras->hasPages())
            <div class="p-4 border-t border-slate-200 dark:border-slate-700">
                {{ $compras->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal Anular Compra -->
<div id="modalAnular" class="hidden fixed z-50 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" onclick="cerrarModalAnular()"></div>

        <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-200 dark:border-slate-700">
            <div class="p-5 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    Anular Compra #<span id="compraIdSpan"></span>
                </h3>
                <button type="button" onclick="cerrarModalAnular()" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form id="formAnular" method="POST">
                @csrf
                <div class="p-5 space-y-3">
                    <p class="text-xs text-slate-600 dark:text-slate-400">
                        Esta acción anulará la compra y revertirá las cantidades del inventario ingresadas por este comprobante.
                    </p>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Motivo de la Anulación <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="motivo" rows="3" required
                                  placeholder="Escribe el motivo por el cual se anula la compra..."
                                  class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white shadow-sm focus:ring-2 focus:ring-rose-500 focus:border-rose-500"></textarea>
                    </div>
                </div>

                <div class="p-4 bg-slate-50 dark:bg-slate-800/80 border-t border-slate-200 dark:border-slate-700 flex justify-end gap-2">
                    <button type="button" onclick="cerrarModalAnular()"
                            class="px-4 py-2 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-200 text-xs font-semibold rounded-xl hover:bg-slate-100 transition">
                        Cancelar
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-xl shadow-sm transition">
                        Confirmar Anulación
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function modalAnular(id) {
    document.getElementById('compraIdSpan').textContent = String(id).padStart(5, '0');
    document.getElementById('formAnular').action = `/compras/${id}/anular`;
    document.getElementById('modalAnular').classList.remove('hidden');
}

function cerrarModalAnular() {
    document.getElementById('modalAnular').classList.add('hidden');
}
</script>
@endpush
@endsection
