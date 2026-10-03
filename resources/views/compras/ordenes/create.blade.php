@extends('layouts.app')

@section('title', 'Nueva Orden de Compra - FarmaBien')

@section('content')
@php
    $productosList = $productos->map(function($p) {
        return [
            'id' => $p->id,
            'nombre' => $p->nombre,
            'principio_activo' => $p->principio_activo ?? '',
            'codigo_barra' => $p->codigo_barra ?? '',
            'laboratorio' => $p->laboratorio->nombre ?? 'Sin Lab',
            'stock_actual' => (int) $p->stock_actual,
            'precio_compra' => (float) ($p->precio_compra > 0 ? $p->precio_compra : round($p->precio_venta * 0.7, 2)),
        ];
    })->values()->toArray();

    $proveedoresList = $proveedores->map(function($prov) {
        return [
            'id' => $prov->id,
            'nombre' => $prov->nombre,
            'ruc' => $prov->ruc ?? '',
            'contacto' => $prov->contacto ?? '',
            'telefono' => $prov->telefono ?? '',
            'email' => $prov->email ?? '',
        ];
    })->values()->toArray();
@endphp

<div class="space-y-5" 
     x-data="ordenCompraForm()"
     @keydown.window="
        if ($event.key === 'F3') { 
            $event.preventDefault(); 
            document.getElementById('ordenBuscadorProducto')?.focus(); 
        }
     ">

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
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">
                Nueva Orden de Compra
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Genere un pedido formal para enviar a su distribuidor farmacéutico por WhatsApp o documento impreso.
            </p>
        </div>
        <div class="flex items-center gap-2.5 shrink-0">
            <a href="{{ route('ordenes-compras.index') }}" 
               class="inline-flex items-center space-x-1.5 px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-xl shadow-xs transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Órdenes de Compra</span>
            </a>

            <!-- Botón Modo Full -->
            <button type="button" 
                    @click="$dispatch('toggle-pos-fullscreen')"
                    title="Modo Pantalla Completa / Ocultar Barras"
                    class="inline-flex items-center space-x-1.5 px-3 py-2 rounded-xl bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold shadow-2xs transition cursor-pointer">
                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                <span x-text="posFullscreen ? 'Salir Full' : 'Modo Full'">Modo Full</span>
            </button>
        </div>
    </div>

    <form method="POST" action="{{ route('ordenes-compras.store') }}" class="space-y-5" @submit="validarFormulario($event)">
        @csrf

        <!-- 1. Proveedor y Condiciones Comerciales -->
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4 shadow-xs space-y-4">
            <h2 class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider flex items-center gap-1.5">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                <span>1. Datos del Proveedor y Condiciones Comerciales</span>
            </h2>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Proveedor con Buscador Predictivo -->
                <div class="sm:col-span-2 relative" @click.outside="proveedorDropdownAbierto = false">
                    <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Proveedor Destinatario <span class="text-rose-500">*</span>
                    </label>
                    <input type="hidden" name="proveedor_id" :value="proveedorSeleccionadoId">

                    <!-- Estado: Proveedor Seleccionado -->
                    <template x-if="proveedorSeleccionado">
                        <div class="flex items-center justify-between p-2.5 rounded-xl border border-emerald-200 dark:border-emerald-800/80 bg-emerald-50/50 dark:bg-emerald-950/20">
                            <div class="min-w-0 pr-2">
                                <div class="text-xs font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    <span class="truncate" x-text="proveedorSeleccionado.nombre"></span>
                                </div>
                                <div class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5 flex flex-wrap items-center gap-2 font-mono">
                                    <span x-show="proveedorSeleccionado.ruc" x-text="'RUC: ' + proveedorSeleccionado.ruc"></span>
                                    <span x-show="proveedorSeleccionado.telefono" x-text="'Tel: ' + proveedorSeleccionado.telefono"></span>
                                    <span x-show="proveedorSeleccionado.contacto" x-text="'Contacto: ' + proveedorSeleccionado.contacto"></span>
                                </div>
                            </div>
                            <button type="button" 
                                    @click="deseleccionarProveedor()" 
                                    class="px-2.5 py-1 text-[11px] font-semibold text-rose-600 dark:text-rose-400 hover:bg-rose-100 dark:hover:bg-rose-950/50 rounded-lg transition shrink-0 cursor-pointer">
                                Cambiar
                            </button>
                        </div>
                    </template>

                    <!-- Estado: Buscador de Proveedor -->
                    <div x-show="!proveedorSeleccionado" class="relative">
                        <div class="relative">
                            <input type="text"
                                   x-model="proveedorQuery"
                                   @focus="proveedorDropdownAbierto = true"
                                   @input="proveedorDropdownAbierto = true"
                                   placeholder="Buscar proveedor por nombre, RUC o contacto..."
                                   class="w-full pl-8 pr-3 py-2 rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-xs focus:ring-emerald-500 focus:border-emerald-500">
                            <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-2.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>

                        <!-- Dropdown de Proveedores -->
                        <div x-show="proveedorDropdownAbierto && filtrarProveedores().length > 0" 
                             x-cloak
                             class="absolute left-0 right-0 top-full mt-1 z-50 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl shadow-xl max-h-56 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800">
                            <template x-for="prov in filtrarProveedores()" :key="prov.id">
                                <button type="button" 
                                        @click="seleccionarProveedor(prov)"
                                        class="w-full px-3 py-2 text-left hover:bg-emerald-50 dark:hover:bg-slate-800 flex items-center justify-between transition cursor-pointer">
                                    <div class="min-w-0">
                                        <div class="text-xs font-bold text-slate-800 dark:text-slate-100 truncate" x-text="prov.nombre"></div>
                                        <div class="text-[10px] text-slate-500 dark:text-slate-400 flex items-center gap-2 mt-0.5">
                                            <span x-show="prov.ruc" x-text="'RUC: ' + prov.ruc"></span>
                                            <span x-show="prov.telefono" x-text="'Tel: ' + prov.telefono"></span>
                                            <span x-show="prov.contacto" x-text="'Contacto: ' + prov.contacto"></span>
                                        </div>
                                    </div>
                                    <span class="text-[10px] font-semibold text-emerald-600 dark:text-emerald-400 shrink-0 ml-2">Seleccionar →</span>
                                </button>
                            </template>
                        </div>
                    </div>
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
                            class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-xs font-semibold focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="contado">Contado</option>
                        <option value="credito">Crédito (CXP)</option>
                    </select>
                </div>

                <!-- Días de Crédito (Plazo) -->
                <div x-show="condicionPago === 'credito'" x-transition class="sm:col-span-2 flex flex-col justify-end">
                    <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Plazo de Crédito
                    </label>
                    <div class="flex items-center gap-2">
                        <div class="flex items-center gap-1">
                            <button type="button" @click="diasCredito = 15" :class="diasCredito == 15 ? 'bg-amber-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300'" class="px-2 py-1 rounded-lg text-[10px] font-bold border border-slate-200 dark:border-slate-700">15d</button>
                            <button type="button" @click="diasCredito = 30" :class="diasCredito == 30 ? 'bg-amber-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300'" class="px-2 py-1 rounded-lg text-[10px] font-bold border border-slate-200 dark:border-slate-700">30d</button>
                            <button type="button" @click="diasCredito = 45" :class="diasCredito == 45 ? 'bg-amber-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300'" class="px-2 py-1 rounded-lg text-[10px] font-bold border border-slate-200 dark:border-slate-700">45d</button>
                            <button type="button" @click="diasCredito = 60" :class="diasCredito == 60 ? 'bg-amber-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300'" class="px-2 py-1 rounded-lg text-[10px] font-bold border border-slate-200 dark:border-slate-700">60d</button>
                        </div>
                        <input type="number" name="dias_credito" x-model="diasCredito" min="1" max="180"
                               class="w-20 rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-xs text-center font-bold focus:ring-emerald-500 focus:border-emerald-500">
                        <span class="text-xs text-slate-500">días</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Detalle de Medicamentos Solicitados con Buscador Ajax / Predictivo -->
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-3 bg-slate-50/50 dark:bg-slate-800/30">
                <div>
                    <h2 class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                        <span>2. Detalle de Medicamentos Solicitados</span>
                    </h2>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                        Busque interactivamente por nombre, fórmula o laboratorio y agregue a la orden.
                    </p>
                </div>

                <!-- Buscador Predictivo Live Autocomplete -->
                <div class="relative w-full md:w-96" @click.outside="productoDropdownAbierto = false">
                    <div class="relative">
                        <input type="text"
                               id="ordenBuscadorProducto"
                               x-model="productoQuery"
                               @focus="productoDropdownAbierto = true"
                               @input="productoDropdownAbierto = true"
                               @keydown.enter.prevent="procesarEnterProducto()"
                               @keydown.escape="productoDropdownAbierto = false"
                               placeholder="Buscar medicamento o principio activo... [F3]"
                               class="w-full pl-8 pr-10 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 font-medium">
                        <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-2.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <span class="absolute right-2 top-2 px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-700 text-[9px] font-mono text-slate-500 font-bold border border-slate-200 dark:border-slate-600 pointer-events-none">F3</span>
                    </div>

                    <!-- Dropdown Flotante de Resultados de Búsqueda -->
                    <div x-show="productoDropdownAbierto && filtrarProductos().length > 0"
                         x-cloak
                         class="absolute left-0 right-0 top-full mt-1 z-50 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl shadow-2xl max-h-64 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800 custom-scrollbar">
                        <template x-for="p in filtrarProductos()" :key="p.id">
                            <button type="button" 
                                    @click="agregarProducto(p)"
                                    class="w-full px-3 py-2.5 text-left hover:bg-emerald-50 dark:hover:bg-slate-800 flex items-center justify-between transition cursor-pointer group">
                                <div class="min-w-0 pr-2">
                                    <div class="text-xs font-bold text-slate-900 dark:text-white group-hover:text-emerald-600 dark:group-hover:text-emerald-400 truncate" x-text="p.nombre"></div>
                                    <div class="text-[10px] text-slate-500 dark:text-slate-400 flex items-center gap-2 mt-0.5">
                                        <span x-show="p.principio_activo" class="truncate" x-text="p.principio_activo"></span>
                                        <span x-show="p.laboratorio" class="px-1.5 py-0.2 rounded bg-slate-100 dark:bg-slate-800 text-[9px] font-semibold text-slate-600 dark:text-slate-300" x-text="p.laboratorio"></span>
                                        <span class="text-slate-400 font-mono" x-text="'Stock: ' + p.stock_actual"></span>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <span class="font-mono font-bold text-xs text-emerald-600 dark:text-emerald-400 block" x-text="formatoMoneda(p.precio_compra)"></span>
                                    <span class="text-[9px] font-bold text-slate-400 group-hover:text-emerald-600 transition">+ Añadir</span>
                                </div>
                            </button>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Tabla de Ítems -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                            <th class="px-4 py-3 text-center w-12">#</th>
                            <th class="px-4 py-3 min-w-[240px]">Medicamento / Laboratorio</th>
                            <th class="px-4 py-3 text-center w-36">Cant. Solicitada</th>
                            <th class="px-4 py-3 text-right w-40">Precio Est. (C$)</th>
                            <th class="px-4 py-3 text-right w-40">Subtotal</th>
                            <th class="px-4 py-3 text-center w-16">Quitar</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-700 dark:text-slate-300">
                        <template x-for="(item, index) in items" :key="item.producto_id">
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition">
                                <td class="px-4 py-3 text-center text-slate-400 font-bold" x-text="index + 1"></td>
                                <td class="px-4 py-3">
                                    <input type="hidden" :name="'items[' + index + '][producto_id]'" :value="item.producto_id">
                                    <span class="font-bold text-slate-900 dark:text-white" x-text="item.nombre"></span>
                                    <div class="text-[10px] text-slate-500 dark:text-slate-400 flex items-center gap-2 mt-0.5">
                                        <span x-show="item.principio_activo" x-text="item.principio_activo"></span>
                                        <span x-show="item.laboratorio" class="text-slate-400" x-text="'• Lab: ' + item.laboratorio"></span>
                                    </div>
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
                                    <div class="relative inline-block w-32">
                                        <span class="absolute left-2.5 top-2 text-[10px] font-bold text-slate-400 pointer-events-none">C$</span>
                                        <input type="number" 
                                               :name="'items[' + index + '][precio_unitario]'" 
                                               x-model.number="item.precio_unitario" 
                                               step="0.01" 
                                               min="0" 
                                               required
                                               class="w-full pl-7 pr-2 py-1.5 text-right text-xs font-bold rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-emerald-500">
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-right font-black text-slate-900 dark:text-white font-mono text-sm" 
                                    x-text="formatoMoneda(item.cantidad * item.precio_unitario)">
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <button type="button" 
                                            @click="removerItem(index)" 
                                            title="Eliminar de la orden"
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="items.length === 0">
                            <td colspan="6" class="px-4 py-12 text-center text-slate-500 dark:text-slate-400">
                                <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mx-auto mb-2 border border-emerald-100 dark:border-emerald-800/60">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                </div>
                                <p class="font-bold text-xs text-slate-800 dark:text-slate-200">No hay medicamentos en la orden</p>
                                <p class="text-[11px] text-slate-400 mt-0.5">Use el buscador superior [F3] para agregar productos al pedido.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Total Preview & Observaciones -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/40 dark:bg-slate-800/20">
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Observaciones / Instrucciones para el Proveedor
                    </label>
                    <textarea name="observaciones" rows="3" placeholder="Ej: Entregar en horario matutino, solicitar factura con crédito a 30 días, remitir certificación..."
                              class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-emerald-500"></textarea>
                </div>

                <div class="flex flex-col justify-between p-4 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs">
                    <div class="flex justify-between items-center text-xs text-slate-500">
                        <span>Total de Fármacos en Pedido:</span>
                        <span class="font-bold text-slate-900 dark:text-white" x-text="items.length + ' líneas (' + calcularUnidadesTotales() + ' unidades)'"></span>
                    </div>
                    <div class="flex justify-between items-baseline pt-2 border-t border-slate-100 dark:border-slate-800">
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Total Estimado del Pedido:</span>
                        <span class="text-2xl font-black text-emerald-900 dark:text-emerald-400 font-mono" x-text="formatoMoneda(calcularTotal())">C$ 0.00</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botones de Acción -->
        <div class="flex items-center justify-end gap-2.5 pt-1">
            <a href="{{ route('ordenes-compras.index') }}" 
               class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-300 dark:border-slate-700 transition">
                Cancelar
            </a>
            <button type="submit" 
                    :disabled="items.length === 0 || !proveedorSeleccionadoId"
                    :class="(items.length === 0 || !proveedorSeleccionadoId) ? 'opacity-50 cursor-not-allowed bg-slate-400' : 'bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 shadow-xs'"
                    class="px-6 py-2.5 rounded-xl text-xs font-bold text-white transition cursor-pointer flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Generar Orden de Compra</span>
            </button>
        </div>
    </form>
</div>

<script>
function ordenCompraForm() {
    const catalogo = @js($productosList);
    const proveedores = @js($proveedoresList);
    const initialProvId = @js($proveedorId ?? old('proveedor_id') ?? '');
    const preloaded = @js($preloadedItems ?? []);

    return {
        catalogo: catalogo,
        proveedores: proveedores,
        condicionPago: @js(old('condicion_pago', 'contado')),
        diasCredito: @js(old('dias_credito', 30)),
        
        // Proveedor Search
        proveedorQuery: '',
        proveedorDropdownAbierto: false,
        proveedorSeleccionadoId: initialProvId,

        get proveedorSeleccionado() {
            if (!this.proveedorSeleccionadoId) return null;
            return this.proveedores.find(p => p.id == this.proveedorSeleccionadoId) || null;
        },

        filtrarProveedores() {
            const q = (this.proveedorQuery || '').trim().toLowerCase();
            if (!q) return this.proveedores.slice(0, 10);
            return this.proveedores.filter(p => {
                return (p.nombre && p.nombre.toLowerCase().includes(q)) ||
                       (p.ruc && p.ruc.toLowerCase().includes(q)) ||
                       (p.contacto && p.contacto.toLowerCase().includes(q)) ||
                       (p.telefono && p.telefono.toLowerCase().includes(q));
            }).slice(0, 10);
        },

        seleccionarProveedor(prov) {
            this.proveedorSeleccionadoId = prov.id;
            this.proveedorQuery = '';
            this.proveedorDropdownAbierto = false;
        },

        deseleccionarProveedor() {
            this.proveedorSeleccionadoId = '';
            this.proveedorQuery = '';
            this.proveedorDropdownAbierto = true;
            this.$nextTick(() => {
                const input = document.querySelector('input[x-model="proveedorQuery"]');
                if (input) input.focus();
            });
        },

        // Producto Search
        productoQuery: '',
        productoDropdownAbierto: false,

        filtrarProductos() {
            const q = (this.productoQuery || '').trim().toLowerCase();
            if (!q) return this.catalogo.slice(0, 15);
            return this.catalogo.filter(p => {
                return (p.nombre && p.nombre.toLowerCase().includes(q)) ||
                       (p.principio_activo && p.principio_activo.toLowerCase().includes(q)) ||
                       (p.codigo_barra && p.codigo_barra.toLowerCase().includes(q)) ||
                       (p.laboratorio && p.laboratorio.toLowerCase().includes(q));
            }).slice(0, 15);
        },

        procesarEnterProducto() {
            const resultados = this.filtrarProductos();
            if (resultados.length > 0) {
                this.agregarProducto(resultados[0]);
            }
        },

        // Items
        items: preloaded.map(i => {
            const p = catalogo.find(c => c.id == i.producto_id);
            return {
                producto_id: i.producto_id,
                nombre: i.nombre || (p ? p.nombre : 'Medicamento'),
                principio_activo: p ? p.principio_activo : '',
                laboratorio: p ? p.laboratorio : '',
                cantidad: i.cantidad || 10,
                precio_unitario: i.precio_estimado || (p ? p.precio_compra : 0)
            };
        }),

        agregarProducto(prod) {
            if (!prod) return;

            const existe = this.items.find(i => i.producto_id == prod.id);
            if (existe) {
                existe.cantidad = (parseInt(existe.cantidad) || 0) + 1;
            } else {
                this.items.push({
                    producto_id: prod.id,
                    nombre: prod.nombre,
                    principio_activo: prod.principio_activo,
                    laboratorio: prod.laboratorio,
                    cantidad: 10,
                    precio_unitario: prod.precio_compra || 0
                });
            }

            this.productoQuery = '';
            this.productoDropdownAbierto = false;
        },

        removerItem(index) {
            this.items.splice(index, 1);
        },

        calcularTotal() {
            return this.items.reduce((sum, item) => sum + ((parseFloat(item.cantidad) || 0) * (parseFloat(item.precio_unitario) || 0)), 0);
        },

        calcularUnidadesTotales() {
            return this.items.reduce((sum, item) => sum + (parseInt(item.cantidad) || 0), 0);
        },

        formatoMoneda(val) {
            return 'C$ ' + Number(val || 0).toLocaleString('es-NI', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },

        validarFormulario(e) {
            if (!this.proveedorSeleccionadoId) {
                e.preventDefault();
                alert('Por favor seleccione un proveedor destinatario para la orden.');
                return false;
            }
            if (this.items.length === 0) {
                e.preventDefault();
                alert('Debe agregar al menos un medicamento a la orden de compra.');
                return false;
            }
            return true;
        }
    };
}
</script>
@endsection
