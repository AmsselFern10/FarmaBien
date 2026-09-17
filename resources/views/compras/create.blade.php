@extends('layouts.app')

@section('title', 'Nueva Compra e Ingreso de Lotes - FarmaBien')

@section('content')
<div x-data="{
    formLayout: localStorage.getItem('farmaFormViewMode') || 'modern',
    setLayout(mode) {
        this.formLayout = mode;
        localStorage.setItem('farmaFormViewMode', mode);
        window.dispatchEvent(new CustomEvent('farma:layout-changed', { detail: { mode: mode } }));
    },
    formData: (window.farmaGetDraft ? window.farmaGetDraft('{{ request()->getPathInfo() }}', {
        proveedor_id: @js(old('proveedor_id', '')),
        numero_comprobante: @js(old('numero_comprobante', '')),
        fecha: @js(old('fecha', date('Y-m-d'))),
        notas: @js(old('notas', ''))
    }) : {
        proveedor_id: @js(old('proveedor_id', '')),
        numero_comprobante: @js(old('numero_comprobante', '')),
        fecha: @js(old('fecha', date('Y-m-d'))),
        notas: @js(old('notas', ''))
    }),
    productosData: @js($productos),
    items: [
        {
            producto_id: '',
            presentacion_id: '',
            numero_lote: '',
            fecha_vencimiento: '',
            cantidad_presentaciones: 1,
            precio_unitario: 0.00,
            presentaciones_disponibles: []
        }
    ],
    onProductoChange(index) {
        const prodId = parseInt(this.items[index].producto_id);
        const prod = this.productosData.find(p => p.id === prodId);
        if (prod) {
            this.items[index].presentaciones_disponibles = prod.presentaciones_activas || [];
            if (this.items[index].presentaciones_disponibles.length > 0) {
                this.items[index].presentacion_id = this.items[index].presentaciones_disponibles[0].id;
            } else {
                this.items[index].presentacion_id = '';
            }
            if (prod.precio_compra) {
                this.items[index].precio_unitario = parseFloat(prod.precio_compra);
            }
        } else {
            this.items[index].presentaciones_disponibles = [];
            this.items[index].presentacion_id = '';
        }
    },
    agregarItem() {
        this.items.push({
            producto_id: '',
            presentacion_id: '',
            numero_lote: '',
            fecha_vencimiento: '',
            cantidad_presentaciones: 1,
            precio_unitario: 0.00,
            presentaciones_disponibles: []
        });
    },
    eliminarItem(index) {
        if (this.items.length > 1) {
            this.items.splice(index, 1);
        } else {
            alert('Debe incluir al menos un producto/lote en la compra.');
        }
    },
    calcularSubtotal(item) {
        const cant = parseFloat(item.cantidad_presentaciones) || 0;
        const prec = parseFloat(item.precio_unitario) || 0;
        return (cant * prec).toFixed(2);
    },
    calcularTotal() {
        return this.items.reduce((acc, item) => {
            return acc + (parseFloat(this.calcularSubtotal(item)) || 0);
        }, 0).toFixed(2);
    }
}"
@keydown.window="
    if ($event.key === 'Escape' && formLayout === 'compact') { window.location.href = '{{ route('compras.index') }}'; }
    if ($event.key === 'F4') { $event.preventDefault(); $refs.formCompraSubmit.click(); }
"
:class="formLayout === 'compact' ? 'w-full max-w-full' : 'max-w-7xl mx-auto'"
class="space-y-4 transition-all duration-200">

    <!-- Top Layout Bar & Breadcrumbs -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-2 border-b border-slate-300 dark:border-slate-700">
        <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
            <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 transition">Inicio</a>
            <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <a href="{{ route('compras.index') }}" class="hover:text-emerald-600 transition">Compras</a>
            <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-slate-800 dark:text-slate-200 font-semibold">Nueva Compra e Ingreso de Lotes</span>
        </nav>

        <!-- Mode Switcher -->
        <div class="flex items-center space-x-2">
            <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Vista:</span>
            <div class="inline-flex rounded-lg p-1 bg-slate-200 dark:bg-slate-700 border border-slate-300 dark:border-slate-600">
                <button type="button" @click="setLayout('modern')"
                        :class="formLayout === 'modern' ? 'bg-white dark:bg-slate-800 text-emerald-600 dark:text-emerald-400 shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'"
                        class="px-3 py-1 text-xs font-semibold rounded-md transition flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    Moderna
                </button>
                <button type="button" @click="setLayout('compact')"
                        :class="formLayout === 'compact' ? 'bg-white dark:bg-slate-800 text-emerald-600 dark:text-emerald-400 shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'"
                        class="px-3 py-1 text-xs font-semibold rounded-md transition flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    Compacta (ERP/POS)
                </button>
            </div>
        </div>
    </div>

    <!-- Alert / Validation Errors -->
    @if ($errors->any())
        <div class="rounded-xl border border-rose-300 dark:border-rose-800 bg-rose-50 dark:bg-rose-900/20 p-4 text-rose-700 dark:text-rose-300 text-xs">
            <p class="font-bold text-sm mb-1">Hubo errores al procesar la compra:</p>
            <ul class="list-disc pl-5 space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="formCompra" method="POST" action="{{ route('compras.store') }}" class="space-y-4">
        @csrf

        <!-- VISTA MODERNA (TARJETAS ESPACIOSAS) -->
        <template x-if="formLayout === 'modern'">
            <div class="space-y-5">
                <!-- Datos del Proveedor y Comprobante -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-300 dark:border-slate-700 shadow-sm space-y-4">
                    <div class="flex items-center space-x-2 border-b border-slate-200 dark:border-slate-700 pb-3">
                        <div class="p-2 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-xl">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white">Información del Proveedor y Factura</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Proveedor <span class="text-rose-500">*</span>
                            </label>
                            <select name="proveedor_id" x-model="formData.proveedor_id" required
                                    class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:ring-2 focus:ring-emerald-500">
                                <option value="">-- Seleccione Proveedor --</option>
                                @foreach($proveedores as $prov)
                                    <option value="{{ $prov->id }}">{{ $prov->nombre }} (RUC: {{ $prov->ruc ?? 'N/A' }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Número de Comprobante / Factura
                            </label>
                            <input type="text" name="numero_comprobante" x-model="formData.numero_comprobante"
                                   placeholder="Ej: F001-0001234"
                                   class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:ring-2 focus:ring-emerald-500">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Fecha de Emisión / Ingreso <span class="text-rose-500">*</span>
                            </label>
                            <input type="date" name="fecha" x-model="formData.fecha" required
                                   class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:ring-2 focus:ring-emerald-500">
                        </div>
                    </div>
                </div>

                <!-- Detalle de Lotes e Ítems -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-300 dark:border-slate-700 shadow-sm space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-700 pb-3">
                        <div class="flex items-center space-x-2">
                            <div class="p-2 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-xl">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            </div>
                            <div>
                                <h2 class="text-base font-bold text-slate-900 dark:text-white">Productos y Lotes a Ingresar</h2>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Ingresa número de lote, fecha de vencimiento y costo por presentación</p>
                            </div>
                        </div>

                        <button type="button" @click="agregarItem()"
                                class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl transition flex items-center gap-1.5 shadow-xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Agregar Otro Lote
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-slate-100 dark:bg-slate-700/60 text-slate-700 dark:text-slate-200 uppercase font-semibold">
                                    <th class="py-2.5 px-3 min-w-[220px]">Producto <span class="text-rose-500">*</span></th>
                                    <th class="py-2.5 px-3 min-w-[140px]">Presentación</th>
                                    <th class="py-2.5 px-3 min-w-[130px]">N° Lote <span class="text-rose-500">*</span></th>
                                    <th class="py-2.5 px-3 min-w-[130px]">Vencimiento <span class="text-rose-500">*</span></th>
                                    <th class="py-2.5 px-3 w-[100px] text-center">Cantidad <span class="text-rose-500">*</span></th>
                                    <th class="py-2.5 px-3 w-[120px] text-right">Precio Compra (C$) <span class="text-rose-500">*</span></th>
                                    <th class="py-2.5 px-3 w-[120px] text-right">Subtotal (C$)</th>
                                    <th class="py-2.5 px-2 w-[50px] text-center">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                                <template x-for="(item, index) in items" :key="index">
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition">
                                        <td class="py-2 px-3">
                                            <select :name="'productos[' + index + '][producto_id]'" x-model="item.producto_id" @change="onProductoChange(index)" required
                                                    class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:ring-2 focus:ring-emerald-500">
                                                <option value="">-- Seleccionar --</option>
                                                <template x-for="p in productosData" :key="p.id">
                                                    <option :value="p.id" x-text="p.nombre + (p.concentracion ? ' (' + p.concentracion + ')' : '')"></option>
                                                </template>
                                            </select>
                                        </td>

                                        <td class="py-2 px-3">
                                            <select :name="'productos[' + index + '][presentacion_id]'" x-model="item.presentacion_id"
                                                    class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:ring-2 focus:ring-emerald-500">
                                                <option value="">Unidad Base</option>
                                                <template x-for="pres in item.presentaciones_disponibles" :key="pres.id">
                                                    <option :value="pres.id" x-text="pres.nombre + ' (' + pres.unidades_por_presentacion + ' un)'"></option>
                                                </template>
                                            </select>
                                        </td>

                                        <td class="py-2 px-3">
                                            <input type="text" :name="'productos[' + index + '][numero_lote]'" x-model="item.numero_lote" required placeholder="LOTE-001"
                                                   class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:ring-2 focus:ring-emerald-500 uppercase">
                                        </td>

                                        <td class="py-2 px-3">
                                            <input type="date" :name="'productos[' + index + '][fecha_vencimiento]'" x-model="item.fecha_vencimiento" required
                                                   class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:ring-2 focus:ring-emerald-500">
                                        </td>

                                        <td class="py-2 px-3">
                                            <input type="number" :name="'productos[' + index + '][cantidad_presentaciones]'" x-model.number="item.cantidad_presentaciones" min="1" required
                                                   class="w-full text-xs text-center rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:ring-2 focus:ring-emerald-500 font-semibold">
                                        </td>

                                        <td class="py-2 px-3">
                                            <input type="number" step="0.01" :name="'productos[' + index + '][precio_unitario]'" x-model.number="item.precio_unitario" min="0" required
                                                   class="w-full text-xs text-right rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:ring-2 focus:ring-emerald-500 font-semibold">
                                        </td>

                                        <td class="py-2 px-3 text-right font-bold text-slate-900 dark:text-white" x-text="'C$ ' + calcularSubtotal(item)">
                                        </td>

                                        <td class="py-2 px-2 text-center">
                                            <button type="button" @click="eliminarItem(index)" class="p-1.5 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Footer Summary Bar & Submit -->
                <div class="bg-slate-900 text-white rounded-2xl p-5 shadow-lg flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div>
                        <span class="text-xs text-slate-400 block uppercase font-medium tracking-wider">Total de la Compra</span>
                        <span class="text-3xl font-extrabold text-emerald-400" x-text="'C$ ' + calcularTotal()"></span>
                    </div>

                    <div class="flex items-center space-x-3">
                        <a href="{{ route('compras.index') }}" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold rounded-xl transition">
                            Cancelar (Esc)
                        </a>
                        <button type="submit" x-ref="formCompraSubmit"
                                class="px-6 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-bold rounded-xl shadow-md transition flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Guardar Compra (F4)
                        </button>
                    </div>
                </div>
            </div>
        </template>

        <!-- VISTA COMPACTA (ERP/POS SINGLE-SCREEN ULTRA-APROVECHADA) -->
        <template x-if="formLayout === 'compact'">
            <div class="bg-white dark:bg-slate-800 rounded-xl border-2 border-slate-300 dark:border-slate-700 shadow-md p-3 space-y-3">
                <!-- Inline Top Header Inputs -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-2 bg-slate-50 dark:bg-slate-700/50 p-2.5 rounded-lg border border-slate-200 dark:border-slate-600">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-200">Proveedor *</label>
                        <select name="proveedor_id" x-model="formData.proveedor_id" required
                                class="w-full text-xs py-1 rounded-md border-slate-300 dark:border-slate-600 dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                            <option value="">-- Proveedor --</option>
                            @foreach($proveedores as $prov)
                                <option value="{{ $prov->id }}">{{ $prov->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-200">N° Comprobante</label>
                        <input type="text" name="numero_comprobante" x-model="formData.numero_comprobante" placeholder="F001-1234"
                               class="w-full text-xs py-1 rounded-md border-slate-300 dark:border-slate-600 dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-200">Fecha *</label>
                        <input type="date" name="fecha" x-model="formData.fecha" required
                               class="w-full text-xs py-1 rounded-md border-slate-300 dark:border-slate-600 dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                    </div>

                    <div class="flex items-end justify-between">
                        <div>
                            <span class="text-[10px] text-slate-500 block">TOTAL COMPRA</span>
                            <span class="text-xl font-extrabold text-emerald-600 dark:text-emerald-400" x-text="'C$ ' + calcularTotal()"></span>
                        </div>
                        <button type="button" @click="agregarItem()"
                                class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-md transition flex items-center gap-1">
                            + Lote
                        </button>
                    </div>
                </div>

                <!-- Compact Table -->
                <div class="overflow-x-auto border border-slate-200 dark:border-slate-700 rounded-lg">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-200 dark:bg-slate-700 text-slate-800 dark:text-slate-100 font-bold uppercase">
                                <th class="py-1.5 px-2">Producto *</th>
                                <th class="py-1.5 px-2">Presentación</th>
                                <th class="py-1.5 px-2 w-[110px]">N° Lote *</th>
                                <th class="py-1.5 px-2 w-[120px]">Vencimiento *</th>
                                <th class="py-1.5 px-2 w-[80px] text-center">Cant *</th>
                                <th class="py-1.5 px-2 w-[100px] text-right">Precio *</th>
                                <th class="py-1.5 px-2 w-[100px] text-right">Subtotal</th>
                                <th class="py-1.5 px-1 w-[40px] text-center"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            <template x-for="(item, index) in items" :key="index">
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/40">
                                    <td class="py-1 px-1.5">
                                        <select :name="'productos[' + index + '][producto_id]'" x-model="item.producto_id" @change="onProductoChange(index)" required
                                                class="w-full text-xs py-1 rounded-md border-slate-300 dark:border-slate-600 dark:bg-slate-800 text-slate-900 dark:text-white">
                                            <option value="">-- Producto --</option>
                                            <template x-for="p in productosData" :key="p.id">
                                                <option :value="p.id" x-text="p.nombre"></option>
                                            </template>
                                        </select>
                                    </td>

                                    <td class="py-1 px-1.5">
                                        <select :name="'productos[' + index + '][presentacion_id]'" x-model="item.presentacion_id"
                                                class="w-full text-xs py-1 rounded-md border-slate-300 dark:border-slate-600 dark:bg-slate-800 text-slate-900 dark:text-white">
                                            <option value="">Unidad Base</option>
                                            <template x-for="pres in item.presentaciones_disponibles" :key="pres.id">
                                                <option :value="pres.id" x-text="pres.nombre"></option>
                                            </template>
                                        </select>
                                    </td>

                                    <td class="py-1 px-1.5">
                                        <input type="text" :name="'productos[' + index + '][numero_lote]'" x-model="item.numero_lote" required placeholder="LOTE"
                                               class="w-full text-xs py-1 rounded-md border-slate-300 dark:border-slate-600 dark:bg-slate-800 text-slate-900 dark:text-white uppercase font-semibold">
                                    </td>

                                    <td class="py-1 px-1.5">
                                        <input type="date" :name="'productos[' + index + '][fecha_vencimiento]'" x-model="item.fecha_vencimiento" required
                                               class="w-full text-xs py-1 rounded-md border-slate-300 dark:border-slate-600 dark:bg-slate-800 text-slate-900 dark:text-white">
                                    </td>

                                    <td class="py-1 px-1.5">
                                        <input type="number" :name="'productos[' + index + '][cantidad_presentaciones]'" x-model.number="item.cantidad_presentaciones" min="1" required
                                               class="w-full text-xs py-1 text-center font-bold rounded-md border-slate-300 dark:border-slate-600 dark:bg-slate-800 text-slate-900 dark:text-white">
                                    </td>

                                    <td class="py-1 px-1.5">
                                        <input type="number" step="0.01" :name="'productos[' + index + '][precio_unitario]'" x-model.number="item.precio_unitario" min="0" required
                                               class="w-full text-xs py-1 text-right font-bold rounded-md border-slate-300 dark:border-slate-600 dark:bg-slate-800 text-slate-900 dark:text-white">
                                    </td>

                                    <td class="py-1 px-2 text-right font-extrabold text-slate-900 dark:text-white" x-text="'C$ ' + calcularSubtotal(item)">
                                    </td>

                                    <td class="py-1 px-1 text-center">
                                        <button type="button" @click="eliminarItem(index)" class="text-rose-600 hover:text-rose-800 p-1">
                                            ✕
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <!-- Compact Action Buttons -->
                <div class="flex items-center justify-end space-x-2 pt-1">
                    <a href="{{ route('compras.index') }}" class="px-3 py-1.5 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold rounded-md">
                        Volver (Esc)
                    </a>
                    <button type="submit" x-ref="formCompraSubmit"
                            class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-md shadow-xs flex items-center gap-1">
                        ✓ Guardar Compra (F4)
                    </button>
                </div>
            </div>
        </template>
    </form>
</div>
@endsection
