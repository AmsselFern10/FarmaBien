@extends('layouts.app')

@section('title', 'Modificar Venta #' . str_pad($venta->id, 5, '0', STR_PAD_LEFT))

@section('content')
<div x-data="{
    formLayout: localStorage.getItem('farma_pos_layout') || 'compact',
    setLayout(layout) {
        this.formLayout = layout;
        localStorage.setItem('farma_pos_layout', layout);
    },
    catalogo: @js($productos ?? []),
    clientes: @js($clientes ?? []),
    categorias: @js($categorias ?? []),
    
    // Datos de la Venta a Modificar
    formData: {
        cliente_id: '{{ old('cliente_id', $venta->cliente_id ?? '') }}',
        tipo_comprobante: '{{ old('tipo_comprobante', $venta->tipo_comprobante ?? 'ticket') }}',
        serie: '{{ old('serie', $venta->serie ?? '') }}',
        numero_comprobante: '{{ old('numero_comprobante', $venta->numero_comprobante ?? '') }}',
        metodo_pago: '{{ old('metodo_pago', $venta->metodo_pago ?? 'efectivo') }}',
        descuento: {{ old('descuento', $venta->descuento ?? 0) }},
        motivo_modificacion: '{{ old('motivo_modificacion', '') }}'
    },
    
    // Carga inicial de ítems existentes
    items: @js(
        $venta->detalles->map(function($d) {
            $p = $d->producto;
            $presDisponibles = collect([
                ['id' => null, 'nombre' => 'Unidad Base', 'unidades' => 1, 'precio' => (float)($p->precio_venta ?? 0)]
            ])->concat(
                collect($p->presentacionesActivas ?? [])->map(fn($pr) => [
                    'id' => $pr->id,
                    'nombre' => $pr->nombre,
                    'unidades' => (int)$pr->unidades_por_presentacion,
                    'precio' => (float)$pr->precio_venta ?: ((float)$p->precio_venta * (int)$pr->unidades_por_presentacion)
                ])
            )->values();

            return [
                'uid' => uniqid('item_'),
                'producto_id' => $d->producto_id,
                'nombre' => $p->nombre ?? 'Medicamento',
                'principio_activo' => $p->principio_activo ?? '',
                'requiere_receta' => (bool)($p->requiere_receta ?? false),
                'lotesDisponibles' => $p->lotes ?? [],
                'lote_id' => $d->lote_id,
                'lote_obj' => $d->lote,
                'presentacionesDisponibles' => $presDisponibles,
                'presentacion_id' => $d->presentacion_id,
                'factor' => (int)($d->unidades_por_presentacion ?? 1),
                'precio_unitario' => (float)$d->precio_unitario,
                'cantidad' => (int)$d->cantidad
            ];
        })
    ),

    busqueda: '',
    filtroCategoriaId: '',

    // Métodos de Carrito
    agregarAlCarrito(producto) {
        if (!producto || !producto.lotes || producto.lotes.length === 0) {
            alert('Este medicamento no tiene lotes disponibles.');
            return;
        }
        const loteDefault = producto.lotes[0];
        const presDisponibles = [
            { id: null, nombre: 'Unidad Base', unidades: 1, precio: parseFloat(producto.precio_venta) || 0 }
        ];
        if (producto.presentaciones_activas && producto.presentaciones_activas.length > 0) {
            producto.presentaciones_activas.forEach(pr => {
                presDisponibles.push({
                    id: pr.id,
                    nombre: pr.nombre,
                    unidades: parseInt(pr.unidades_por_presentacion) || 1,
                    precio: parseFloat(pr.precio_venta) || (parseFloat(producto.precio_venta) * (parseInt(pr.unidades_por_presentacion) || 1))
                });
            });
        }

        this.items.push({
            uid: Date.now() + Math.random().toString(36).substr(2, 5),
            producto_id: producto.id,
            nombre: producto.nombre,
            principio_activo: producto.principio_activo || '',
            requiere_receta: !!producto.requiere_receta,
            lotesDisponibles: producto.lotes,
            lote_id: loteDefault.id,
            lote_obj: loteDefault,
            presentacionesDisponibles: presDisponibles,
            presentacion_id: null,
            factor: 1,
            precio_unitario: parseFloat(producto.precio_venta) || 0,
            cantidad: 1
        });
    },

    agregarItemVacio() {
        if (this.catalogo.length === 0) return;
        this.agregarAlCarrito(this.catalogo[0]);
    },

    onProductoChange(idx) {
        const item = this.items[idx];
        const prod = this.catalogo.find(p => p.id == item.producto_id);
        if (!prod) return;

        item.nombre = prod.nombre;
        item.principio_activo = prod.principio_activo || '';
        item.requiere_receta = !!prod.requiere_receta;
        item.lotesDisponibles = prod.lotes || [];
        if (prod.lotes && prod.lotes.length > 0) {
            item.lote_id = prod.lotes[0].id;
            item.lote_obj = prod.lotes[0];
        }

        const presDisponibles = [
            { id: null, nombre: 'Unidad Base', unidades: 1, precio: parseFloat(prod.precio_venta) || 0 }
        ];
        if (prod.presentaciones_activas && prod.presentaciones_activas.length > 0) {
            prod.presentaciones_activas.forEach(pr => {
                presDisponibles.push({
                    id: pr.id,
                    nombre: pr.nombre,
                    unidades: parseInt(pr.unidades_por_presentacion) || 1,
                    precio: parseFloat(pr.precio_venta) || (parseFloat(prod.precio_venta) * (parseInt(pr.unidades_por_presentacion) || 1))
                });
            });
        }
        item.presentacionesDisponibles = presDisponibles;
        item.presentacion_id = null;
        item.factor = 1;
        item.precio_unitario = parseFloat(prod.precio_venta) || 0;
    },

    onPresentacionChange(idx) {
        const item = this.items[idx];
        const pres = item.presentacionesDisponibles.find(p => p.id == item.presentacion_id);
        if (pres) {
            item.factor = pres.unidades;
            item.precio_unitario = pres.precio;
        } else {
            item.factor = 1;
            const prod = this.catalogo.find(p => p.id == item.producto_id);
            item.precio_unitario = prod ? parseFloat(prod.precio_venta) || 0 : 0;
        }
    },

    onLoteChange(idx) {
        const item = this.items[idx];
        const lote = item.lotesDisponibles.find(l => l.id == item.lote_id);
        if (lote) {
            item.lote_obj = lote;
        }
    },

    eliminarItem(idx) {
        this.items.splice(idx, 1);
    },

    // Cálculos
    calcularSubtotal(item) {
        const cant = parseInt(item.cantidad) || 0;
        const prec = parseFloat(item.precio_unitario) || 0;
        return (cant * prec).toFixed(2);
    },

    calcularUnidadesBase(item) {
        const cant = parseInt(item.cantidad) || 0;
        const fac = parseInt(item.factor) || 1;
        return cant * fac;
    },

    calcularSubtotalGeneral() {
        return this.items.reduce((acc, it) => acc + (parseFloat(this.calcularSubtotal(it)) || 0), 0).toFixed(2);
    },

    calcularTotalGeneral() {
        const sub = parseFloat(this.calcularSubtotalGeneral()) || 0;
        const desc = parseFloat(this.formData.descuento) || 0;
        return Math.max(0, sub - desc).toFixed(2);
    }
}"
class="space-y-4">

    <!-- Header & Mode Switcher -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-1 border-b border-slate-300/80 dark:border-slate-800">
        <div>
            <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
                <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <a href="{{ route('ventas.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Ventas</a>
                <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="text-slate-800 dark:text-slate-200 font-semibold">Editar Venta #{{ str_pad($venta->id, 5, '0', STR_PAD_LEFT) }}</span>
            </nav>
            <h1 class="text-lg sm:text-xl font-bold text-slate-900 dark:text-white flex items-center space-x-2">
                <span>Modificar Operación de Venta</span>
            </h1>
        </div>

        <div class="flex items-center space-x-3 self-start sm:self-auto">
            <div class="inline-flex items-center p-0.5 rounded-xl bg-slate-200/80 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-xs font-semibold shadow-2xs">
                <button type="button" 
                        @click="setLayout('modern')"
                        :class="formLayout === 'modern' ? 'bg-white dark:bg-slate-700 text-emerald-700 dark:text-emerald-400 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200'"
                        class="px-2.5 py-1 rounded-lg transition flex items-center space-x-1.5 cursor-pointer">
                    <span>Moderna</span>
                </button>
                <button type="button" 
                        @click="setLayout('compact')"
                        :class="formLayout === 'compact' ? 'bg-white dark:bg-slate-700 text-emerald-700 dark:text-emerald-400 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200'"
                        class="px-2.5 py-1 rounded-lg transition flex items-center space-x-1.5 cursor-pointer">
                    <span>Compacta (ERP)</span>
                </button>
            </div>

            <a href="{{ route('ventas.show', $venta) }}" 
               class="px-3 py-1.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold transition flex items-center space-x-1.5 shrink-0 shadow-2xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Cancelar</span>
            </a>
        </div>
    </div>

    <!-- Error Alert -->
    @if ($errors->any())
    <div class="p-3 rounded-xl bg-rose-50 dark:bg-rose-950/60 border border-rose-300 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-xs">
        <ul class="list-disc list-inside space-y-0.5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('ventas.update', $venta) }}" method="POST">
        @csrf
        @method('PUT')

        <!-- ============================================================== -->
        <!-- MODO COMPACTO (ERP UNIFIED CONTAINER)                          -->
        <!-- ============================================================== -->
        <template x-if="formLayout === 'compact'">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-300 dark:border-slate-800 shadow-md p-4 space-y-4 animate-fadeIn">
                
                <!-- Toolbar Superior -->
                <div class="flex items-center justify-between pb-3 border-b border-slate-300 dark:border-slate-800 bg-slate-100/80 dark:bg-slate-800/60 -mx-4 -mt-4 p-3 rounded-t-2xl">
                    <div class="flex items-center space-x-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500 shadow-xs"></span>
                        <span class="text-xs font-extrabold text-slate-800 dark:text-slate-200 uppercase tracking-wide">EDICIÓN Y AJUSTE DE VENTA #{{ str_pad($venta->id, 5, '0', STR_PAD_LEFT) }}</span>
                        <span class="text-[10px] px-2 py-0.5 rounded-md bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-mono font-bold">Esc = Cancelar</span>
                    </div>

                    <div class="flex items-center space-x-2">
                        <a href="{{ route('ventas.show', $venta) }}" 
                           class="px-3 py-1.5 rounded-lg border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-800 text-xs font-bold transition">
                            Cancelar (Esc)
                        </a>
                        <button type="submit" 
                                :disabled="!formData.motivo_modificacion || formData.motivo_modificacion.trim().length < 5 || items.length === 0"
                                class="px-4 py-1.5 rounded-lg bg-amber-600 hover:bg-amber-700 disabled:opacity-50 text-white text-xs font-extrabold shadow-sm transition flex items-center space-x-1.5 cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Actualizar Venta</span>
                        </button>
                    </div>
                </div>

                <!-- Grid de Paneles Superiores (Motivo + Cliente + Resumen) -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
                    <!-- Panel 1: Motivo y Datos de Venta (8 cols) -->
                    <div class="lg:col-span-8 p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/40 dark:bg-slate-800/30 space-y-3">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center space-x-1.5 border-b border-slate-200/60 dark:border-slate-700/60 pb-1.5">
                            <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            <span>1. Motivo de Modificación & Datos de Cabecera</span>
                        </div>

                        <!-- Motivo Modificación -->
                        <div>
                            <label class="block text-[11px] font-bold text-amber-800 dark:text-amber-400 mb-1">
                                Motivo de la Modificación <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" 
                                   name="motivo_modificacion" 
                                   x-model="formData.motivo_modificacion" 
                                   required
                                   minlength="5"
                                   placeholder="Ej: Corrección en medicamento despachado, cambio solicitado por el cliente..."
                                   class="w-full px-2.5 py-1.5 bg-amber-50/50 dark:bg-slate-800 border border-amber-300 dark:border-amber-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-amber-500 font-medium">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-2.5">
                            <!-- Cliente (Col 6) -->
                            <div class="sm:col-span-6">
                                <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                    Cliente
                                </label>
                                <select name="cliente_id" 
                                        x-model="formData.cliente_id" 
                                        class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white font-medium focus:ring-1 focus:ring-emerald-500">
                                    <option value="">Público General (Venta Libre)</option>
                                    @foreach($clientes as $cl)
                                        <option value="{{ $cl->id }}">
                                            {{ $cl->nombre }} {{ $cl->documento ? "({$cl->documento})" : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Tipo Comprobante (Col 3) -->
                            <div class="sm:col-span-3">
                                <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                    Comprobante
                                </label>
                                <select name="tipo_comprobante" 
                                        x-model="formData.tipo_comprobante" 
                                        class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white font-medium focus:ring-1 focus:ring-emerald-500">
                                    <option value="ticket">Ticket</option>
                                    <option value="boleta">Boleta</option>
                                    <option value="factura">Factura</option>
                                </select>
                            </div>

                            <!-- Método Pago (Col 3) -->
                            <div class="sm:col-span-3">
                                <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                    Método Pago
                                </label>
                                <select name="metodo_pago" 
                                        x-model="formData.metodo_pago" 
                                        class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white font-medium focus:ring-1 focus:ring-emerald-500">
                                    <option value="efectivo">Efectivo</option>
                                    <option value="tarjeta">Tarjeta (POS)</option>
                                    <option value="transferencia">Yape / Plin / Transferencia</option>
                                    <option value="mixto">Pago Mixto</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Panel 2: Resumen Liquidación Actualizado (4 cols) -->
                    <div class="lg:col-span-4 p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/40 dark:bg-slate-800/30 flex flex-col justify-between space-y-2">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center space-x-1.5 border-b border-slate-200/60 dark:border-slate-700/60 pb-1.5">
                            <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Resumen Actualizado</span>
                        </div>

                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <div>
                                <span class="text-[10px] font-semibold text-slate-500 block">Ítems a Despachar:</span>
                                <span class="font-bold text-slate-900 dark:text-white text-sm" x-text="items.length + ' líneas'"></span>
                            </div>
                            <div>
                                <span class="text-[10px] font-semibold text-slate-500 block">Descuento ($):</span>
                                <input type="number" 
                                       step="0.10" 
                                       min="0"
                                       name="descuento"
                                       x-model="formData.descuento" 
                                       class="w-20 px-1.5 py-0.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded text-xs font-bold text-slate-900 dark:text-white text-right">
                            </div>
                        </div>

                        <div class="pt-1.5 border-t border-slate-200/80 dark:border-slate-700/80 flex items-center justify-between">
                            <span class="text-xs font-semibold text-slate-600 dark:text-slate-300">Total Recalculado:</span>
                            <span class="text-2xl font-black text-emerald-600 dark:text-emerald-400">
                                $<span x-text="calcularTotalGeneral()"></span>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Panel 3: Desglose de Fármacos y Lotes -->
                <div class="p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/40 dark:bg-slate-800/30 space-y-3">
                    <div class="flex items-center justify-between border-b border-slate-200/60 dark:border-slate-700/60 pb-1.5">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center space-x-1.5">
                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                            <span>2. Desglose y Ajuste de Fármacos, Presentaciones y Lotes</span>
                        </div>

                        <button type="button" 
                                @click="agregarItemVacio()" 
                                class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-bold shadow-2xs transition flex items-center space-x-1 cursor-pointer">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span>Agregar Fármaco</span>
                        </button>
                    </div>

                    <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800/80">
                        <table class="w-full text-left text-xs border-collapse min-w-[850px]">
                            <thead>
                                <tr class="bg-slate-100/90 dark:bg-slate-700/80 text-[10px] font-bold text-slate-700 dark:text-slate-200 uppercase tracking-wider border-b border-slate-200 dark:border-slate-700">
                                    <th class="py-2 px-2 text-center w-8">#</th>
                                    <th class="py-2 px-3 min-w-[200px]">Medicamento / Fármaco</th>
                                    <th class="py-2 px-3 min-w-[170px]">Presentación</th>
                                    <th class="py-2 px-3 min-w-[190px]">Lote (FEFO) & Stock</th>
                                    <th class="py-2 px-2 text-center w-20">Cant.</th>
                                    <th class="py-2 px-2 text-right w-24">P. Venta ($)</th>
                                    <th class="py-2 px-3 text-right w-24">Subtotal</th>
                                    <th class="py-2 px-2 text-center w-8"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                                <template x-for="(item, idx) in items" :key="item.uid">
                                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition">
                                        <td class="py-2 px-2 text-center text-[11px] font-bold text-slate-400" x-text="idx + 1"></td>
                                        
                                        <!-- Medicamento -->
                                        <td class="py-2 px-3">
                                            <input type="hidden" :name="'productos[' + idx + '][producto_id]'" :value="item.producto_id">
                                            <select x-model="item.producto_id" 
                                                    @change="onProductoChange(idx)"
                                                    class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg text-xs text-slate-900 dark:text-white font-medium focus:ring-1 focus:ring-emerald-500">
                                                <template x-for="p in catalogo" :key="p.id">
                                                    <option :value="p.id" x-text="p.nombre + (p.principio_activo ? ' (' + p.principio_activo + ')' : '')"></option>
                                                </template>
                                            </select>
                                        </td>

                                        <!-- Presentación -->
                                        <td class="py-2 px-3">
                                            <input type="hidden" :name="'productos[' + idx + '][presentacion_id]'" :value="item.presentacion_id">
                                            <select x-model="item.presentacion_id" 
                                                    @change="onPresentacionChange(idx)"
                                                    class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                                                <template x-for="pres in item.presentacionesDisponibles" :key="pres.id">
                                                    <option :value="pres.id" x-text="pres.nombre + ' (x' + pres.unidades + ')'"></option>
                                                </template>
                                            </select>
                                        </td>

                                        <!-- Lote -->
                                        <td class="py-2 px-3">
                                            <input type="hidden" :name="'productos[' + idx + '][lote_id]'" :value="item.lote_id">
                                            <select x-model="item.lote_id" 
                                                    @change="onLoteChange(idx)"
                                                    class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg text-xs text-slate-900 dark:text-white font-mono focus:ring-1 focus:ring-emerald-500">
                                                <template x-for="l in item.lotesDisponibles" :key="l.id">
                                                    <option :value="l.id" x-text="l.numero_lote + ' (' + l.stock_actual + 'u)'"></option>
                                                </template>
                                            </select>
                                        </td>

                                        <!-- Cantidad -->
                                        <td class="py-2 px-2">
                                            <input type="number" 
                                                   :name="'productos[' + idx + '][cantidad]'"
                                                   x-model.number="item.cantidad" 
                                                   min="1" 
                                                   class="w-full px-2 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg text-xs text-slate-900 dark:text-white font-bold text-center focus:ring-1 focus:ring-emerald-500">
                                        </td>

                                        <!-- Precio Venta -->
                                        <td class="py-2 px-2 text-right">
                                            <input type="number" 
                                                   step="0.01" 
                                                   min="0" 
                                                   :name="'productos[' + idx + '][precio_unitario]'"
                                                   x-model="item.precio_unitario" 
                                                   class="w-full px-2 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg text-xs text-slate-900 dark:text-white font-bold text-right focus:ring-1 focus:ring-emerald-500">
                                        </td>

                                        <!-- Subtotal -->
                                        <td class="py-2 px-3 text-right font-bold text-slate-900 dark:text-white">
                                            $<span x-text="calcularSubtotal(item)"></span>
                                        </td>

                                        <!-- Botón Eliminar Fila -->
                                        <td class="py-2 px-2 text-center">
                                            <button type="button" 
                                                    @click="eliminarItem(idx)" 
                                                    title="Eliminar ítem"
                                                    class="p-1 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition cursor-pointer">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Footer / Toolbar Inferior -->
                <div class="flex items-center justify-between pt-2 border-t border-slate-200 dark:border-slate-800 text-slate-400 text-[11px]">
                    <span>Los cambios registrarán una nueva versión de venta con ajuste automático en Kardex.</span>
                    <button type="submit" 
                            :disabled="!formData.motivo_modificacion || formData.motivo_modificacion.trim().length < 5 || items.length === 0"
                            class="px-5 py-2 rounded-lg bg-amber-600 hover:bg-amber-700 disabled:opacity-50 text-white text-xs font-extrabold shadow-sm transition flex items-center space-x-1.5 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Actualizar y Generar Versión</span>
                    </button>
                </div>
            </div>
        </template>
    </form>
</div>
@endsection