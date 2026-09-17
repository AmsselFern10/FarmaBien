@extends('layouts.app')

@section('title', 'Registrar Compra e Ingreso de Lotes - FarmaBien')

@section('content')
@php
    $productosCatalogo = $productos->map(function($p) {
        return [
            'id' => $p->id,
            'nombre' => $p->nombre,
            'codigo_barra' => $p->codigo_barra,
            'principio_activo' => $p->principio_activo,
            'laboratorio' => $p->laboratorio->nombre ?? 'Sin Lab',
            'precio_compra' => (float)$p->precio_compra,
            'presentaciones' => $p->presentacionesActivas->map(function($pres) {
                return [
                    'id' => $pres->id,
                    'nombre' => $pres->nombre,
                    'unidades' => (int)$pres->unidades_por_presentacion,
                    'precio_compra' => (float)$pres->precio_compra
                ];
            })->values()->toArray()
        ];
    });
@endphp

<div x-data="{
    formLayout: localStorage.getItem('farmaFormViewMode') || 'modern',
    setLayout(mode) {
        this.formLayout = mode;
        localStorage.setItem('farmaFormViewMode', mode);
        window.dispatchEvent(new CustomEvent('farma:layout-changed', { detail: { mode } }));
    },
    catalogo: @js($productosCatalogo),
    formData: (window.farmaGetDraft ? window.farmaGetDraft('{{ request()->getPathInfo() }}', {
        proveedor_id: @js(old('proveedor_id', '')),
        numero_comprobante: @js(old('numero_comprobante', '')),
        fecha: @js(old('fecha', date('Y-m-d')))
    }) : {
        proveedor_id: @js(old('proveedor_id', '')),
        numero_comprobante: @js(old('numero_comprobante', '')),
        fecha: @js(old('fecha', date('Y-m-d')))
    }),
    items: [
        {
            uid: Date.now(),
            producto_id: '',
            presentacion_id: '',
            factor: 1,
            tipo_presentacion: 'Unidad Base',
            cantidad: 1,
            precio_unitario: '',
            numero_lote: '',
            fecha_vencimiento: '',
            presentacionesDisponibles: []
        }
    ],
    init() {
        if (this.formData && this.formData.savedItems && this.formData.savedItems.length > 0) {
            this.items = this.formData.savedItems;
        }
    },
    agregarItem() {
        this.items.push({
            uid: Date.now() + Math.random(),
            producto_id: '',
            presentacion_id: '',
            factor: 1,
            tipo_presentacion: 'Unidad Base',
            cantidad: 1,
            precio_unitario: '',
            numero_lote: '',
            fecha_vencimiento: '',
            presentacionesDisponibles: []
        });
    },
    eliminarItem(idx) {
        if (this.items.length <= 1) {
            alert('La compra debe contener al menos un producto.');
            return;
        }
        this.items.splice(idx, 1);
    },
    onProductoChange(idx) {
        const item = this.items[idx];
        const prod = this.catalogo.find(p => p.id == item.producto_id);
        if (!prod) {
            item.presentacionesDisponibles = [];
            item.presentacion_id = '';
            item.factor = 1;
            item.tipo_presentacion = 'Unidad Base';
            return;
        }
        item.presentacionesDisponibles = prod.presentaciones || [];
        item.presentacion_id = '';
        item.factor = 1;
        item.tipo_presentacion = 'Unidad Base';
        item.precio_unitario = prod.precio_compra > 0 ? prod.precio_compra : '';
    },
    onPresentacionChange(idx) {
        const item = this.items[idx];
        if (!item.presentacion_id) {
            item.factor = 1;
            item.tipo_presentacion = 'Unidad Base';
            const prod = this.catalogo.find(p => p.id == item.producto_id);
            if (prod && prod.precio_compra > 0) {
                item.precio_unitario = prod.precio_compra;
            }
            return;
        }
        const pres = item.presentacionesDisponibles.find(p => p.id == item.presentacion_id);
        if (pres) {
            item.factor = pres.unidades || 1;
            item.tipo_presentacion = pres.nombre;
            if (pres.precio_compra > 0) {
                item.precio_unitario = pres.precio_compra;
            } else {
                const prod = this.catalogo.find(p => p.id == item.producto_id);
                if (prod && prod.precio_compra > 0) {
                    item.precio_unitario = (prod.precio_compra * item.factor).toFixed(2);
                }
            }
        }
    },
    calcularSubtotal(item) {
        const cant = parseFloat(item.cantidad) || 0;
        const prec = parseFloat(item.precio_unitario) || 0;
        return (cant * prec).toFixed(2);
    },
    calcularUnidadesBase(item) {
        const cant = parseInt(item.cantidad) || 0;
        const fac = parseInt(item.factor) || 1;
        return cant * fac;
    },
    calcularTotalGeneral() {
        return this.items.reduce((acc, it) => acc + (parseFloat(this.calcularSubtotal(it)) || 0), 0).toFixed(2);
    },
    calcularTotalUnidadesBase() {
        return this.items.reduce((acc, it) => acc + this.calcularUnidadesBase(it), 0);
    }
}"
@keydown.window="if ($event.key === 'Escape' && formLayout === 'compact') { window.location.href = '{{ route('compras.index') }}'; }"
:class="formLayout === 'compact' ? 'w-full max-w-full' : 'max-w-7xl mx-auto'"
class="space-y-4 transition-all duration-200">

    <!-- Header & Layout Switcher -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-1 border-b border-slate-300/80 dark:border-slate-800">
        <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
            <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
            <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <a href="{{ route('compras.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Compras</a>
            <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-slate-800 dark:text-slate-200 font-semibold">Recepción y Registro de Lotes</span>
        </nav>

        <!-- Mode Switcher -->
        <div class="flex items-center space-x-2 self-start sm:self-auto">
            <span class="text-[11px] font-bold text-slate-700 dark:text-slate-400 hidden md:inline">Diseño:</span>
            <div class="inline-flex items-center p-0.5 rounded-xl bg-slate-200/80 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-xs font-semibold shadow-2xs">
                <button type="button" 
                        @click="setLayout('modern')"
                        :class="formLayout === 'modern' ? 'bg-white dark:bg-slate-700 text-emerald-700 dark:text-emerald-400 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200'"
                        class="px-2.5 py-1 rounded-lg transition flex items-center space-x-1.5 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    <span>Moderna</span>
                </button>
                <button type="button" 
                        @click="setLayout('compact')"
                        :class="formLayout === 'compact' ? 'bg-white dark:bg-slate-700 text-emerald-700 dark:text-emerald-400 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200'"
                        class="px-2.5 py-1 rounded-lg transition flex items-center space-x-1.5 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <span>Compacta (ERP)</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Main Form -->
    <form action="{{ route('compras.store') }}" method="POST" id="formCompra">
        @csrf

        <!-- Error Alert -->
        @if ($errors->any())
        <div class="p-3 mb-4 rounded-xl bg-rose-50 dark:bg-rose-950/60 border border-rose-300 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-xs">
            <div class="font-bold flex items-center space-x-1 mb-1">
                <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>Por favor corrija los siguientes errores:</span>
            </div>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- ============================================================== -->
        <!-- VISTA COMPACTA (ERP / ALTA DENSIDAD / 2 COLUMNAS / FULL WIDTH) -->
        <!-- ============================================================== -->
        <template x-if="formLayout === 'compact'">
            <div class="space-y-3">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-3">
                    
                    <!-- Columna Izquierda: Datos del Comprobante & Proveedor (4 cols) -->
                    <div class="lg:col-span-4 space-y-3">
                        <div class="bg-white dark:bg-slate-900 rounded-xl p-3.5 border border-slate-300 dark:border-slate-800 shadow-xs space-y-3">
                            <div class="flex items-center space-x-2 pb-2 border-b border-slate-200 dark:border-slate-800">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                <h3 class="text-xs font-bold uppercase text-slate-800 dark:text-slate-200 tracking-wider">1. Cabecera de Compra</h3>
                            </div>

                            <!-- Proveedor -->
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                    Proveedor <span class="text-rose-500">*</span>
                                </label>
                                <select name="proveedor_id" 
                                        x-model="formData.proveedor_id" 
                                        required
                                        class="w-full px-2.5 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                                    <option value="">-- Seleccione Proveedor --</option>
                                    @foreach($proveedores as $prov)
                                        <option value="{{ $prov->id }}">
                                            {{ $prov->nombre }} {{ $prov->ruc ? "({$prov->ruc})" : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- N° Comprobante -->
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                    N° Factura / Boleta / Guía
                                </label>
                                <input type="text" 
                                       name="numero_comprobante" 
                                       x-model="formData.numero_comprobante" 
                                       placeholder="Ej: F001-0004523"
                                       class="w-full px-2.5 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                            </div>

                            <!-- Fecha Emisión -->
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                    Fecha de Documento <span class="text-rose-500">*</span>
                                </label>
                                <input type="date" 
                                       name="fecha" 
                                       x-model="formData.fecha" 
                                       required
                                       class="w-full px-2.5 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                            </div>
                        </div>

                        <!-- Resumen y Totales Compacto -->
                        <div class="bg-slate-900 text-white rounded-xl p-3.5 border border-slate-800 shadow-xs space-y-2.5">
                            <h4 class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Resumen de Liquidación</h4>
                            
                            <div class="flex justify-between text-xs text-slate-300">
                                <span>Ítems / Lotes:</span>
                                <span class="font-bold text-white" x-text="items.length"></span>
                            </div>
                            <div class="flex justify-between text-xs text-slate-300">
                                <span>Unidades Base al Kardex:</span>
                                <span class="font-bold text-emerald-400" x-text="calcularTotalUnidadesBase()"></span>
                            </div>
                            <div class="pt-2 border-t border-slate-800 flex justify-between items-baseline">
                                <span class="text-xs font-semibold text-slate-300">Total a Pagar:</span>
                                <span class="text-xl font-extrabold text-emerald-400">
                                    $<span x-text="calcularTotalGeneral()"></span>
                                </span>
                            </div>

                            <!-- Botones Acción -->
                            <div class="pt-2 flex items-center space-x-2">
                                <a href="{{ route('compras.index') }}" 
                                   class="flex-1 text-center py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold transition">
                                    Cancelar (Esc)
                                </a>
                                <button type="submit" 
                                        class="flex-2 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold shadow-xs transition flex items-center justify-center space-x-1 cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    <span>Guardar Compra</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Columna Derecha: Tabla Dinámica de Productos y Lotes (8 cols) -->
                    <div class="lg:col-span-8 space-y-3">
                        <div class="bg-white dark:bg-slate-900 rounded-xl p-3.5 border border-slate-300 dark:border-slate-800 shadow-xs space-y-3">
                            <div class="flex items-center justify-between pb-2 border-b border-slate-200 dark:border-slate-800">
                                <div class="flex items-center space-x-2">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    <h3 class="text-xs font-bold uppercase text-slate-800 dark:text-slate-200 tracking-wider">
                                        2. Productos, Presentaciones y Lotes
                                    </h3>
                                </div>
                                <button type="button" 
                                        @click="agregarItem()" 
                                        class="inline-flex items-center space-x-1 px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-2xs transition cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    <span>Agregar Línea</span>
                                </button>
                            </div>

                            <!-- Tabla de Lotes -->
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-xs border-collapse">
                                    <thead>
                                        <tr class="bg-slate-100 dark:bg-slate-800/80 text-[10px] font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">
                                            <th class="py-2 px-2">#</th>
                                            <th class="py-2 px-2 w-48">Medicamento / Fármaco</th>
                                            <th class="py-2 px-2 w-36">Presentación</th>
                                            <th class="py-2 px-2 w-20">Cant.</th>
                                            <th class="py-2 px-2 w-24">Precio Compra</th>
                                            <th class="py-2 px-2 w-28">N° Lote</th>
                                            <th class="py-2 px-2 w-28">F. Vence</th>
                                            <th class="py-2 px-2 text-right">Subtotal</th>
                                            <th class="py-2 px-2 text-center w-8"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                                        <template x-for="(item, idx) in items" :key="item.uid">
                                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition">
                                                <!-- Indice -->
                                                <td class="py-2 px-2 text-[11px] font-bold text-slate-400" x-text="idx + 1"></td>

                                                <!-- Producto -->
                                                <td class="py-2 px-2">
                                                    <select :name="'productos[' + idx + '][producto_id]'" 
                                                            x-model="item.producto_id" 
                                                            @change="onProductoChange(idx)"
                                                            required
                                                            class="w-full px-2 py-1 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                                                        <option value="">-- Seleccionar --</option>
                                                        <template x-for="p in catalogo" :key="p.id">
                                                            <option :value="p.id" x-text="p.nombre + (p.principio_activo ? ' (' + p.principio_activo + ')' : '')"></option>
                                                        </template>
                                                    </select>
                                                </td>

                                                <!-- Presentación -->
                                                <td class="py-2 px-2">
                                                    <select :name="'productos[' + idx + '][presentacion_id]'" 
                                                            x-model="item.presentacion_id" 
                                                            @change="onPresentacionChange(idx)"
                                                            class="w-full px-2 py-1 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                                                        <option value="">Unidad Base (x1)</option>
                                                        <template x-for="pres in item.presentacionesDisponibles" :key="pres.id">
                                                            <option :value="pres.id" x-text="pres.nombre + ' (x' + pres.unidades + ')'"></option>
                                                        </template>
                                                    </select>
                                                    <div class="text-[9px] text-emerald-600 dark:text-emerald-400 font-semibold mt-0.5" x-show="item.factor > 1">
                                                        Total Base: <span x-text="calcularUnidadesBase(item)"></span> u.
                                                    </div>
                                                </td>

                                                <!-- Cantidad -->
                                                <td class="py-2 px-2">
                                                    <input type="number" 
                                                           :name="'productos[' + idx + '][cantidad_presentaciones]'" 
                                                           x-model.number="item.cantidad" 
                                                           min="1" 
                                                           required
                                                           placeholder="1"
                                                           class="w-full px-2 py-1 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white font-bold focus:ring-2 focus:ring-emerald-500">
                                                </td>

                                                <!-- Precio Compra -->
                                                <td class="py-2 px-2">
                                                    <input type="number" 
                                                           step="0.01" 
                                                           min="0" 
                                                           :name="'productos[' + idx + '][precio_unitario]'" 
                                                           x-model="item.precio_unitario" 
                                                           required
                                                           placeholder="0.00"
                                                           class="w-full px-2 py-1 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white font-bold text-right focus:ring-2 focus:ring-emerald-500">
                                                </td>

                                                <!-- N° Lote -->
                                                <td class="py-2 px-2">
                                                    <input type="text" 
                                                           :name="'productos[' + idx + '][numero_lote]'" 
                                                           x-model="item.numero_lote" 
                                                           required
                                                           placeholder="LOTE-123"
                                                           class="w-full px-2 py-1 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white uppercase font-mono focus:ring-2 focus:ring-emerald-500">
                                                </td>

                                                <!-- Fecha Vencimiento -->
                                                <td class="py-2 px-2">
                                                    <input type="date" 
                                                           :name="'productos[' + idx + '][fecha_vencimiento]'" 
                                                           x-model="item.fecha_vencimiento" 
                                                           required
                                                           class="w-full px-2 py-1 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                                                </td>

                                                <!-- Subtotal -->
                                                <td class="py-2 px-2 text-right font-bold text-slate-900 dark:text-white">
                                                    $<span x-text="calcularSubtotal(item)"></span>
                                                </td>

                                                <!-- Botón Eliminar Fila -->
                                                <td class="py-2 px-2 text-center">
                                                    <button type="button" 
                                                            @click="eliminarItem(idx)" 
                                                            title="Eliminar fila"
                                                            class="p-1 rounded text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 transition cursor-pointer">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    </button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>


        <!-- ============================================================== -->
        <!-- VISTA MODERNA (TARJETAS AMPLIAS / DISEÑO VISUAL ESPACIOSO)    -->
        <!-- ============================================================== -->
        <template x-if="formLayout === 'modern'">
            <div class="space-y-4">
                
                <!-- Tarjeta 1: Datos del Comprobante y Proveedor -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-300 dark:border-slate-800 shadow-xs space-y-4">
                    <div class="flex items-center space-x-3 pb-3 border-b border-slate-200 dark:border-slate-800">
                        <div class="w-9 h-9 rounded-xl bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-sm">
                            1
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Datos de la Factura y Proveedor</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Identifique el origen del comprobante fiscal y la fecha de recepción.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Proveedor -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                Proveedor Registrado <span class="text-rose-500">*</span>
                            </label>
                            <select name="proveedor_id" 
                                    x-model="formData.proveedor_id" 
                                    required
                                    class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                                <option value="">-- Seleccionar Proveedor --</option>
                                @foreach($proveedores as $prov)
                                    <option value="{{ $prov->id }}">
                                        {{ $prov->nombre }} {{ $prov->ruc ? "• RUC: {$prov->ruc}" : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Número Comprobante -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                Número de Comprobante / Factura
                            </label>
                            <input type="text" 
                                   name="numero_comprobante" 
                                   x-model="formData.numero_comprobante" 
                                   placeholder="Ej: F001-0004523"
                                   class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                        </div>

                        <!-- Fecha -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                Fecha de Recepción / Emisión <span class="text-rose-500">*</span>
                            </label>
                            <input type="date" 
                                   name="fecha" 
                                   x-model="formData.fecha" 
                                   required
                                   class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                        </div>
                    </div>
                </div>

                <!-- Tarjeta 2: Ingreso de Medicamentos y Lotes -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-300 dark:border-slate-800 shadow-xs space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800">
                        <div class="flex items-center space-x-3">
                            <div class="w-9 h-9 rounded-xl bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-sm">
                                2
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Medicamentos, Presentaciones y Lotes</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Agregue uno o más lotes de productos adquiridos con sus respectivas fechas de vencimiento.</p>
                            </div>
                        </div>

                        <button type="button" 
                                @click="agregarItem()" 
                                class="inline-flex items-center space-x-1.5 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span>Agregar Fármaco / Lote</span>
                        </button>
                    </div>

                    <!-- Items Cards List -->
                    <div class="space-y-3">
                        <template x-for="(item, idx) in items" :key="item.uid">
                            <div class="p-4 rounded-xl bg-slate-50/80 dark:bg-slate-800/50 border border-slate-300 dark:border-slate-700 space-y-3 transition">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300 flex items-center space-x-1.5">
                                        <span class="w-5 h-5 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 flex items-center justify-center text-[10px]" x-text="idx + 1"></span>
                                        <span>Ítem de Compra</span>
                                    </span>

                                    <div class="flex items-center space-x-3">
                                        <div class="text-xs font-bold text-slate-800 dark:text-slate-200">
                                            Subtotal: <span class="text-emerald-600 dark:text-emerald-400 text-sm">$<span x-text="calcularSubtotal(item)"></span></span>
                                        </div>
                                        <button type="button" 
                                                @click="eliminarItem(idx)" 
                                                title="Eliminar este ítem"
                                                class="p-1 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition cursor-pointer">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                                    <!-- Producto -->
                                    <div class="md:col-span-4">
                                        <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                            Medicamento / Producto <span class="text-rose-500">*</span>
                                        </label>
                                        <select :name="'productos[' + idx + '][producto_id]'" 
                                                x-model="item.producto_id" 
                                                @change="onProductoChange(idx)"
                                                required
                                                class="w-full px-3 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                                            <option value="">-- Seleccione Medicamento --</option>
                                            <template x-for="p in catalogo" :key="p.id">
                                                <option :value="p.id" x-text="p.nombre + (p.principio_activo ? ' (' + p.principio_activo + ')' : '')"></option>
                                            </template>
                                        </select>
                                    </div>

                                    <!-- Presentación -->
                                    <div class="md:col-span-3">
                                        <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                            Presentación de Compra
                                        </label>
                                        <select :name="'productos[' + idx + '][presentacion_id]'" 
                                                x-model="item.presentacion_id" 
                                                @change="onPresentacionChange(idx)"
                                                class="w-full px-3 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                                            <option value="">Unidad Base (x1)</option>
                                            <template x-for="pres in item.presentacionesDisponibles" :key="pres.id">
                                                <option :value="pres.id" x-text="pres.nombre + ' (x' + pres.unidades + ')'"></option>
                                            </template>
                                        </select>
                                    </div>

                                    <!-- Cantidad -->
                                    <div class="md:col-span-2">
                                        <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                            Cantidad <span class="text-rose-500">*</span>
                                        </label>
                                        <input type="number" 
                                               :name="'productos[' + idx + '][cantidad_presentaciones]'" 
                                               x-model.number="item.cantidad" 
                                               min="1" 
                                               required
                                               placeholder="1"
                                               class="w-full px-3 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white font-bold focus:ring-2 focus:ring-emerald-500">
                                    </div>

                                    <!-- Precio Unitario -->
                                    <div class="md:col-span-3">
                                        <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                            Precio Unit. ($) <span class="text-rose-500">*</span>
                                        </label>
                                        <input type="number" 
                                               step="0.01" 
                                               min="0" 
                                               :name="'productos[' + idx + '][precio_unitario]'" 
                                               x-model="item.precio_unitario" 
                                               required
                                               placeholder="0.00"
                                               class="w-full px-3 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white font-bold text-right focus:ring-2 focus:ring-emerald-500">
                                    </div>

                                    <!-- N° Lote -->
                                    <div class="md:col-span-6">
                                        <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                            Número de Lote de Fabricación <span class="text-rose-500">*</span>
                                        </label>
                                        <input type="text" 
                                               :name="'productos[' + idx + '][numero_lote]'" 
                                               x-model="item.numero_lote" 
                                               required
                                               placeholder="Ej: LOTE-A9842"
                                               class="w-full px-3 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white uppercase font-mono focus:ring-2 focus:ring-emerald-500">
                                    </div>

                                    <!-- Fecha Vencimiento -->
                                    <div class="md:col-span-6">
                                        <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                            Fecha de Vencimiento <span class="text-rose-500">*</span>
                                        </label>
                                        <input type="date" 
                                               :name="'productos[' + idx + '][fecha_vencimiento]'" 
                                               x-model="item.fecha_vencimiento" 
                                               required
                                               class="w-full px-3 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Tarjeta 3: Totales y Acciones -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-300 dark:border-slate-800 shadow-xs flex flex-col md:flex-row items-center justify-between gap-4">
                    <div class="flex items-center space-x-6 text-xs text-slate-600 dark:text-slate-300">
                        <div>
                            <span class="block text-slate-400 text-[11px]">Total de Líneas:</span>
                            <span class="font-bold text-slate-900 dark:text-white text-sm" x-text="items.length"></span>
                        </div>
                        <div>
                            <span class="block text-slate-400 text-[11px]">Unidades Base al Kardex:</span>
                            <span class="font-bold text-emerald-600 dark:text-emerald-400 text-sm" x-text="calcularTotalUnidadesBase()"></span>
                        </div>
                        <div>
                            <span class="block text-slate-400 text-[11px]">Monto Total:</span>
                            <span class="font-extrabold text-slate-900 dark:text-white text-xl">
                                $<span x-text="calcularTotalGeneral()"></span>
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center space-x-3 w-full md:w-auto">
                        <a href="{{ route('compras.index') }}" 
                           class="flex-1 md:flex-none px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition text-center">
                            Cancelar
                        </a>
                        <button type="submit" 
                                class="flex-1 md:flex-none px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-xs transition flex items-center justify-center space-x-2 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Guardar Compra e Ingresar Lotes</span>
                        </button>
                    </div>
                </div>

            </div>
        </template>

    </form>
</div>
@endsection