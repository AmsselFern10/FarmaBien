@extends('layouts.app')

@section('title', 'Nueva Orden de Compra - FarmaBien')

@section('content')
<div class="space-y-5" x-data="ordenCompraForm()">
    <!-- Breadcrumbs -->
    <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('compras.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Compras</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('ordenes-compras.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Órdenes de Compra</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-800 dark:text-slate-200 font-semibold">Nueva Orden</span>
    </nav>

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <span>Nueva Orden de Compra</span>
                <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-blue-100 dark:bg-blue-950/60 text-blue-800 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                    Borrador PO
                </span>
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Genere un pedido formal para enviar a su distribuidor por WhatsApp o documento impreso.
            </p>
        </div>
        <div class="flex items-center gap-2.5 shrink-0">
            <!-- Botón Modo Full -->
            <button type="button" 
                    @click="$dispatch('toggle-pos-fullscreen')"
                    title="Modo Pantalla Completa / Ocultar Barras"
                    class="inline-flex items-center space-x-1.5 px-3 py-2 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold shadow-2xs transition cursor-pointer">
                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                <span x-text="posFullscreen ? 'Salir Full' : 'Modo Full'">Modo Full</span>
            </button>

            <a href="{{ route('ordenes-compras.index') }}" 
               class="inline-flex items-center space-x-1.5 px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-xl shadow-xs transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Volver</span>
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('ordenes-compras.store') }}" class="space-y-5">
        @csrf

        <!-- Datos Principales -->
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4 shadow-xs space-y-4">
            <h2 class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">
                1. Datos del Proveedor y Condiciones Comerciales
            </h2>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Proveedor -->
                <div class="sm:col-span-2">
                    <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Proveedor Destinatario <span class="text-rose-500">*</span>
                    </label>
                    <select name="proveedor_id" required 
                            class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-xs focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="">Seleccione un proveedor...</option>
                        @foreach($proveedores as $prov)
                        <option value="{{ $prov->id }}" {{ ($proveedorId == $prov->id || old('proveedor_id') == $prov->id) ? 'selected' : '' }}>
                            {{ $prov->nombre }}{{ $prov->contacto ? ' — ' . $prov->contacto : '' }}{{ $prov->telefono ? ' (' . $prov->telefono . ')' : '' }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Fecha Emisión -->
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Fecha de Emisión <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="fecha_emision" value="{{ old('fecha_emision', now()->toDateString()) }}" required
                           class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-xs focus:ring-emerald-500 focus:border-emerald-500">
                </div>

                <!-- Fecha Esperada Entrega -->
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Fecha Estimada de Entrega
                    </label>
                    <input type="date" name="fecha_esperada_entrega" value="{{ old('fecha_esperada_entrega', now()->addDays(3)->toDateString()) }}"
                           class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-xs focus:ring-emerald-500 focus:border-emerald-500">
                </div>

                <!-- Condición de Pago -->
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Condición de Pago <span class="text-rose-500">*</span>
                    </label>
                    <select name="condicion_pago" x-model="condicionPago" required 
                            class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-xs focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="contado">Contado</option>
                        <option value="credito">Crédito</option>
                    </select>
                </div>

                <!-- Días de Crédito -->
                <div x-show="condicionPago === 'credito'" style="display: none;">
                    <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Días de Crédito (Plazo)
                    </label>
                    <input type="number" name="dias_credito" value="30" min="1" max="180"
                           class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-xs focus:ring-emerald-500 focus:border-emerald-500">
                </div>
            </div>
        </div>

        <!-- Productos a Solicitar -->
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h2 class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">
                        2. Detalle de Medicamentos Solicitados
                    </h2>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                        Busque y agregue los medicamentos que enviará a cotizar o surtir.
                    </p>
                </div>

                <!-- Buscador rápido y selector -->
                <div class="flex items-center gap-2 max-w-lg w-full sm:w-auto">
                    <div class="relative flex-1 sm:w-72">
                        <select x-model="productoSeleccionadoId" 
                                class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-xs focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="">Seleccione medicamento para añadir...</option>
                            @foreach($productos as $p)
                            <option value="{{ $p->id }}" 
                                    data-nombre="{{ $p->nombre }}" 
                                    data-lab="{{ $p->laboratorio->nombre ?? 'Sin Lab' }}"
                                    data-precio="{{ $p->precio_compra > 0 ? $p->precio_compra : round($p->precio_venta * 0.7, 2) }}">
                                {{ $p->nombre }} ({{ $p->laboratorio->nombre ?? 'Sin Lab' }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="button" 
                            @click="agregarProducto()" 
                            class="px-3.5 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-xs transition shrink-0">
                        + Añadir
                    </button>
                </div>
            </div>

            <!-- Tabla de Ítems -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                            <th class="px-4 py-3">#</th>
                            <th class="px-4 py-3">Medicamento / Presentación</th>
                            <th class="px-4 py-3 text-center w-36">Cant. Solicitada</th>
                            <th class="px-4 py-3 text-right w-40">Precio Est. (C$)</th>
                            <th class="px-4 py-3 text-right w-40">Subtotal</th>
                            <th class="px-4 py-3 text-center w-16">Quitar</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-700 dark:text-slate-300">
                        <template x-for="(item, index) in items" :key="index">
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition">
                                <td class="px-4 py-3 text-slate-400 font-bold" x-text="index + 1"></td>
                                <td class="px-4 py-3">
                                    <input type="hidden" :name="'items[' + index + '][producto_id]'" :value="item.producto_id">
                                    <span class="font-bold text-slate-900 dark:text-white" x-text="item.nombre"></span>
                                    <div class="text-[10px] text-slate-400" x-text="item.lab ? 'Laboratorio: ' + item.lab : ''"></div>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <input type="number" 
                                           :name="'items[' + index + '][cantidad]'" 
                                           x-model.number="item.cantidad" 
                                           min="1" 
                                           required
                                           class="w-24 text-center text-xs font-bold rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-emerald-500">
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <input type="number" 
                                           :name="'items[' + index + '][precio_unitario]'" 
                                           x-model.number="item.precio_unitario" 
                                           step="0.01" 
                                           min="0" 
                                           required
                                           class="w-28 text-right text-xs font-bold rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-emerald-500">
                                </td>
                                <td class="px-4 py-3 text-right font-black text-slate-900 dark:text-white font-mono" 
                                    x-text="formatoMoneda(item.cantidad * item.precio_unitario)">
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <button type="button" 
                                            @click="removerItem(index)" 
                                            class="p-1 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="items.length === 0">
                            <td colspan="6" class="px-4 py-12 text-center text-slate-500 dark:text-slate-400">
                                <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-400 flex items-center justify-center mx-auto mb-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                </div>
                                <p class="font-semibold text-xs">No hay medicamentos en la orden</p>
                                <p class="text-[11px] text-slate-400 mt-0.5">Seleccione un medicamento en el buscador superior y presione "+ Añadir".</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Total Preview & Observaciones -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/40 dark:bg-slate-800/20">
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Observaciones / Instrucciones de Envío
                    </label>
                    <textarea name="observaciones" rows="3" placeholder="Ej: Entregar en horario matutino, solicitar factura con crédito a 30 días..."
                              class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-emerald-500"></textarea>
                </div>

                <div class="flex flex-col justify-between p-4 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs">
                    <div class="flex justify-between items-center text-xs text-slate-500">
                        <span>Total de Productos en Pedido:</span>
                        <span class="font-bold text-slate-900 dark:text-white" x-text="items.length + ' medicamentos'"></span>
                    </div>
                    <div class="flex justify-between items-baseline pt-2 border-t border-slate-100 dark:border-slate-800">
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Total Estimado del Pedido:</span>
                        <span class="text-2xl font-black text-emerald-600 dark:text-emerald-400 font-mono" x-text="formatoMoneda(calcularTotal())">C$ 0.00</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botones de Acción -->
        <div class="flex items-center justify-end gap-2.5 pt-1">
            <a href="{{ route('ordenes-compras.index') }}" 
               class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                Cancelar
            </a>
            <button type="submit" 
                    :disabled="items.length === 0"
                    :class="items.length === 0 ? 'opacity-50 cursor-not-allowed bg-slate-400' : 'bg-emerald-600 hover:bg-emerald-700 shadow-xs'"
                    class="px-5 py-2 rounded-xl text-xs font-bold text-white transition cursor-pointer">
                Generar Orden de Compra
            </button>
        </div>
    </form>
</div>

<script>
function ordenCompraForm() {
    return {
        condicionPago: 'contado',
        productoSeleccionadoId: '',
        items: {!! json_encode($preloadedItems) !!},

        agregarProducto() {
            if (!this.productoSeleccionadoId) return;

            const select = document.querySelector('select[x-model="productoSeleccionadoId"]');
            const option = select.options[select.selectedIndex];
            const nombre = option.dataset.nombre;
            const lab = option.dataset.lab || '';
            const precio = parseFloat(option.dataset.precio) || 0;

            const existe = this.items.find(i => i.producto_id == this.productoSeleccionadoId);
            if (existe) {
                existe.cantidad += 10;
            } else {
                this.items.push({
                    producto_id: this.productoSeleccionadoId,
                    nombre: nombre,
                    lab: lab,
                    cantidad: 10,
                    precio_unitario: precio
                });
            }

            this.productoSeleccionadoId = '';
        },

        removerItem(index) {
            this.items.splice(index, 1);
        },

        calcularTotal() {
            return this.items.reduce((sum, item) => sum + ((parseFloat(item.cantidad) || 0) * (parseFloat(item.precio_unitario) || 0)), 0);
        },

        formatoMoneda(val) {
            return 'C$ ' + Number(val).toLocaleString('es-NI', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
    };
}
</script>
@endsection
