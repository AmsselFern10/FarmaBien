@extends('layouts.app')

@section('title', 'Modificar Compra #' . str_pad($compra->id, 5, '0', STR_PAD_LEFT) . ' - FarmaBien')

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

    $detallesExistentes = $compra->detalles->map(function($det) {
        $prod = $det->producto;
        $presentaciones = $prod ? $prod->presentacionesActivas->map(function($pres) {
            return [
                'id' => $pres->id,
                'nombre' => $pres->nombre,
                'unidades' => (int)$pres->unidades_por_presentacion,
                'precio_compra' => (float)$pres->precio_compra
            ];
        })->values()->toArray() : [];

        return [
            'uid' => $det->id,
            'producto_id' => $det->producto_id,
            'presentacion_id' => $det->presentacion_id ?? '',
            'factor' => (int)($det->unidades_por_presentacion ?: 1),
            'tipo_presentacion' => $det->tipo_presentacion ?: 'Unidad Base',
            'cantidad' => (int)($det->cantidad_presentaciones ?: $det->cantidad_unidades_base),
            'precio_unitario' => (float)$det->precio_unitario,
            'numero_lote' => $det->lote->numero_lote ?? '',
            'fecha_vencimiento' => $det->lote && $det->lote->fecha_vencimiento ? $det->lote->fecha_vencimiento->format('Y-m-d') : '',
            'presentacionesDisponibles' => $presentaciones
        ];
    })->values()->toArray();
@endphp

<div x-data="{
    formLayout: localStorage.getItem('farmaFormViewMode') || 'modern',
    setLayout(mode) {
        this.formLayout = mode;
        localStorage.setItem('farmaFormViewMode', mode);
        window.dispatchEvent(new CustomEvent('farma:layout-changed', { detail: { mode } }));
    },
    catalogo: @js($productosCatalogo),
    formData: {
        proveedor_id: @js(old('proveedor_id', $compra->proveedor_id)),
        numero_comprobante: @js(old('numero_comprobante', $compra->numero_comprobante ?? '')),
        fecha: @js(old('fecha', $compra->fecha ? $compra->fecha->format('Y-m-d') : date('Y-m-d'))),
        motivo_modificacion: @js(old('motivo_modificacion', ''))
    },
    items: @js($detallesExistentes),
    // Modal Nueva Presentación
    modalNuevaPres: false,
    nuevaPresItemIdx: null,
    nuevaPres: {
        producto_id: '',
        producto_nombre: '',
        nombre: '',
        unidades_por_presentacion: 10,
        precio_compra: '',
        precio_venta: '',
        codigo_barras: '',
        cargando: false,
        error: ''
    },
    abrirModalPresentacion(idx) {
        const item = this.items[idx];
        if (!item.producto_id) {
            alert('Por favor, seleccione un medicamento primero antes de crear una nueva presentación.');
            return;
        }
        const prod = this.catalogo.find(p => p.id == item.producto_id);
        this.nuevaPresItemIdx = idx;
        this.nuevaPres = {
            producto_id: item.producto_id,
            producto_nombre: prod ? prod.nombre : '',
            nombre: '',
            unidades_por_presentacion: 10,
            precio_compra: prod && prod.precio_compra > 0 ? (prod.precio_compra * 10).toFixed(2) : '',
            precio_venta: '',
            codigo_barras: '',
            cargando: false,
            error: ''
        };
        this.modalNuevaPres = true;
    },
    async guardarNuevaPresentacion() {
        if (!this.nuevaPres.nombre || this.nuevaPres.unidades_por_presentacion < 1) {
            this.nuevaPres.error = 'Complete el nombre y las unidades por presentación.';
            return;
        }
        this.nuevaPres.cargando = true;
        this.nuevaPres.error = '';

        try {
            const token = document.querySelector('meta[name=\'csrf-token\']')?.getAttribute('content') || '';
            const res = await fetch(`/api/productos/${this.nuevaPres.producto_id}/presentaciones`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({
                    nombre: this.nuevaPres.nombre,
                    unidades_por_presentacion: this.nuevaPres.unidades_por_presentacion,
                    precio_compra: this.nuevaPres.precio_compra || null,
                    precio_venta: this.nuevaPres.precio_venta || null,
                    codigo_barras: this.nuevaPres.codigo_barras || null
                })
            });

            const data = await res.json();
            if (!res.ok || !data.success) {
                throw new Error(data.message || 'Error al guardar la presentación');
            }

            const nueva = {
                id: data.presentacion.id,
                nombre: data.presentacion.nombre,
                unidades: parseInt(data.presentacion.unidades_por_presentacion) || 1,
                precio_compra: parseFloat(data.presentacion.precio_compra) || 0
            };

            // Actualizar catálogo local
            const prod = this.catalogo.find(p => p.id == this.nuevaPres.producto_id);
            if (prod) {
                prod.presentaciones.push(nueva);
            }

            // Actualizar fila activa
            if (this.nuevaPresItemIdx !== null && this.items[this.nuevaPresItemIdx]) {
                const item = this.items[this.nuevaPresItemIdx];
                item.presentacionesDisponibles = prod ? [...prod.presentaciones] : [nueva];
                item.presentacion_id = nueva.id;
                item.factor = nueva.unidades;
                item.tipo_presentacion = nueva.nombre;
                if (nueva.precio_compra > 0) {
                    item.precio_unitario = nueva.precio_compra;
                }
            }

            this.modalNuevaPres = false;
        } catch (err) {
            this.nuevaPres.error = err.message;
        } finally {
            this.nuevaPres.cargando = false;
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
@keydown.window="if ($event.key === 'Escape' && formLayout === 'compact' && !modalNuevaPres) { window.location.href = '{{ route('compras.show', $compra) }}'; }"
:class="formLayout === 'compact' ? 'w-full max-w-full' : 'max-w-7xl mx-auto'"
class="space-y-4 transition-all duration-200">

    <!-- Header & Layout Switcher -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-1 border-b border-slate-300/80 dark:border-slate-800">
        <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
            <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
            <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <a href="{{ route('compras.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Compras</a>
            <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <a href="{{ route('compras.show', $compra) }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Compra #{{ str_pad($compra->id, 5, '0', STR_PAD_LEFT) }}</a>
            <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-slate-800 dark:text-slate-200 font-semibold">Modificar e Historia</span>
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

    <!-- Traceability Notice Box -->
    <div class="p-3.5 rounded-xl bg-amber-50 dark:bg-amber-950/50 border border-amber-300 dark:border-amber-800 text-amber-900 dark:text-amber-200 text-xs flex items-start space-x-3">
        <div class="w-6 h-6 rounded-lg bg-amber-100 dark:bg-amber-900/60 text-amber-700 dark:text-amber-300 flex items-center justify-center shrink-0 mt-0.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div>
            <div class="font-bold">Política de Auditoría y Trazabilidad FarmaBien:</div>
            <p class="mt-0.5 text-amber-800 dark:text-amber-300/90 text-[11px]">
                Al modificar esta compra, la versión actual (<strong>#{{ str_pad($compra->id, 5, '0', STR_PAD_LEFT) }}</strong>) quedará marcada como modificada y se generará una nueva versión vinculada, recalculando con total precisión el Kardex y los lotes.
            </p>
        </div>
    </div>

    <!-- Main Form -->
    <form action="{{ route('compras.update', $compra) }}" method="POST" id="formCompra">
        @csrf
        @method('PUT')

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
        <!-- VISTA COMPACTA (ERP / FULL WIDTH / ALTA DENSIDAD)              -->
        <!-- ============================================================== -->
        <template x-if="formLayout === 'compact'">
            <div class="space-y-3">
                
                <!-- Panel Superior: Cabecera & Motivo + Liquidación en Grid Horizontal -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-3">
                    
                    <!-- Datos Cabecera y Motivo (8 cols) -->
                    <div class="lg:col-span-8 bg-white dark:bg-slate-900 rounded-xl p-3.5 border border-slate-300 dark:border-slate-800 shadow-xs space-y-3">
                        <div class="flex items-center space-x-2 pb-2 border-b border-slate-200 dark:border-slate-800">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500 shadow-xs"></span>
                            <h3 class="text-xs font-bold uppercase text-slate-800 dark:text-slate-200 tracking-wider">
                                1. Cabecera y Razón del Cambio
                            </h3>
                            <span class="text-[10px] px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 font-mono">Esc = Cancelar</span>
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
                                   placeholder="Ej: Corrección en cantidad o ajuste de comprobante del proveedor..."
                                   class="w-full px-2.5 py-1.5 bg-amber-50/40 dark:bg-slate-800 border border-amber-300 dark:border-amber-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-amber-500">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
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
                                    N° Factura / Boleta
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
                    </div>

                    <!-- Resumen de Liquidación (4 cols) - Light Mode Blanco -->
                    <div class="lg:col-span-4 bg-white dark:bg-slate-900 rounded-xl p-3.5 border border-slate-300 dark:border-slate-800 shadow-xs flex flex-col justify-between space-y-2">
                        <div class="flex items-center space-x-2 pb-1.5 border-b border-slate-200 dark:border-slate-800">
                            <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <h4 class="text-[11px] font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200">Resumen Actualizado</h4>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <div class="text-slate-600 dark:text-slate-300">
                                <span class="text-[10px] text-slate-500 block">Lotes a Ajustar:</span>
                                <span class="font-bold text-slate-900 dark:text-white text-sm" x-text="items.length + ' líneas'"></span>
                            </div>
                            <div class="text-slate-600 dark:text-slate-300">
                                <span class="text-[10px] text-slate-500 block">Unidades al Kardex:</span>
                                <span class="font-bold text-emerald-600 dark:text-emerald-400 text-sm" x-text="calcularTotalUnidadesBase() + ' u.'"></span>
                            </div>
                        </div>

                        <div class="pt-2 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between">
                            <span class="text-xs font-semibold text-slate-600 dark:text-slate-300">Total Recalculado:</span>
                            <span class="text-xl font-extrabold text-emerald-600 dark:text-emerald-400">
                                $<span x-text="calcularTotalGeneral()"></span>
                            </span>
                        </div>

                        <!-- Botones Acción -->
                        <div class="pt-1 flex items-center space-x-2">
                            <a href="{{ route('compras.show', $compra) }}" 
                               class="px-3 py-1.5 rounded-lg border border-slate-300 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-300 text-xs font-semibold transition text-center">
                                Cancelar
                            </a>
                            <button type="submit" 
                                    :disabled="!formData.motivo_modificacion || formData.motivo_modificacion.trim().length < 5"
                                    class="flex-1 py-1.5 rounded-lg bg-amber-600 hover:bg-amber-700 disabled:opacity-50 text-white text-xs font-bold shadow-xs transition flex items-center justify-center space-x-1.5 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>Actualizar Versión</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Panel Inferior: Tabla Full Width de Medicamentos, Presentaciones y Lotes -->
                <div class="bg-white dark:bg-slate-900 rounded-xl p-3.5 border border-slate-300 dark:border-slate-800 shadow-xs space-y-3">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-200 dark:border-slate-800">
                        <div class="flex items-center space-x-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shadow-xs"></span>
                            <h3 class="text-xs font-bold uppercase text-slate-800 dark:text-slate-200 tracking-wider">
                                2. Desglose y Ajuste de Productos y Lotes
                            </h3>
                        </div>
                        <button type="button" 
                                @click="agregarItem()" 
                                class="inline-flex items-center space-x-1 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-2xs transition cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span>Agregar Fármaco / Lote</span>
                        </button>
                    </div>

                    <!-- Tabla con Anchos Óptimos -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse min-w-[850px]">
                            <thead>
                                <tr class="bg-slate-100 dark:bg-slate-800/80 text-[10px] font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">
                                    <th class="py-2.5 px-2 text-center w-8">#</th>
                                    <th class="py-2.5 px-3 min-w-[200px]">Medicamento / Fármaco</th>
                                    <th class="py-2.5 px-3 min-w-[200px]">Presentación</th>
                                    <th class="py-2.5 px-2 text-center w-20">Cant.</th>
                                    <th class="py-2.5 px-2 text-right w-24">P. Compra ($)</th>
                                    <th class="py-2.5 px-2 w-28">N° Lote</th>
                                    <th class="py-2.5 px-2 w-32">F. Vencimiento</th>
                                    <th class="py-2.5 px-3 text-right w-24">Subtotal</th>
                                    <th class="py-2.5 px-2 text-center w-8"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                                <template x-for="(item, idx) in items" :key="item.uid">
                                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition">
                                        <!-- Indice -->
                                        <td class="py-2.5 px-2 text-center text-[11px] font-bold text-slate-400" x-text="idx + 1"></td>

                                        <!-- Producto -->
                                        <td class="py-2.5 px-3">
                                            <select :name="'productos[' + idx + '][producto_id]'" 
                                                    x-model="item.producto_id" 
                                                    @change="onProductoChange(idx)"
                                                    required
                                                    class="w-full px-2.5 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 font-medium">
                                                <option value="">-- Seleccionar Medicamento --</option>
                                                <template x-for="p in catalogo" :key="p.id">
                                                    <option :value="p.id" x-text="p.nombre + (p.principio_activo ? ' (' + p.principio_activo + ')' : '')"></option>
                                                </template>
                                            </select>
                                        </td>

                                        <!-- Presentación con Botón de Creación Rápida -->
                                        <td class="py-2.5 px-3">
                                            <div class="flex items-center space-x-1.5">
                                                <select :name="'productos[' + idx + '][presentacion_id]'" 
                                                        x-model="item.presentacion_id" 
                                                        @change="onPresentacionChange(idx)"
                                                        class="w-full px-2.5 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                                                    <option value="">Unidad Base (x1)</option>
                                                    <template x-for="pres in item.presentacionesDisponibles" :key="pres.id">
                                                        <option :value="pres.id" x-text="pres.nombre + ' (x' + pres.unidades + ')'"></option>
                                                    </template>
                                                </select>
                                                <button type="button" 
                                                        @click="abrirModalPresentacion(idx)"
                                                        :disabled="!item.producto_id"
                                                        :title="item.producto_id ? 'Crear nueva presentación para este medicamento' : 'Seleccione un medicamento primero'"
                                                        class="p-1.5 rounded-lg border border-emerald-300 dark:border-emerald-700 bg-emerald-50 dark:bg-emerald-950/60 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300 transition shrink-0 disabled:opacity-40 cursor-pointer">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                                </button>
                                            </div>
                                            <div class="text-[10px] text-emerald-600 dark:text-emerald-400 font-semibold mt-0.5" x-show="item.factor > 1">
                                                Total Base: <span x-text="calcularUnidadesBase(item)"></span> u.
                                            </div>
                                        </td>

                                        <!-- Cantidad -->
                                        <td class="py-2.5 px-2">
                                            <input type="number" 
                                                   :name="'productos[' + idx + '][cantidad_presentaciones]'" 
                                                   x-model.number="item.cantidad" 
                                                   min="1" 
                                                   required
                                                   placeholder="1"
                                                   class="w-full px-2 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white font-bold text-center focus:ring-2 focus:ring-emerald-500">
                                        </td>

                                        <!-- Precio Compra -->
                                        <td class="py-2.5 px-2">
                                            <input type="number" 
                                                   step="0.01" 
                                                   min="0" 
                                                   :name="'productos[' + idx + '][precio_unitario]'" 
                                                   x-model="item.precio_unitario" 
                                                   required
                                                   placeholder="0.00"
                                                   class="w-full px-2 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white font-bold text-right focus:ring-2 focus:ring-emerald-500">
                                        </td>

                                        <!-- N° Lote -->
                                        <td class="py-2.5 px-2">
                                            <input type="text" 
                                                   :name="'productos[' + idx + '][numero_lote]'" 
                                                   x-model="item.numero_lote" 
                                                   required
                                                   placeholder="LOTE-123"
                                                   class="w-full px-2 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white uppercase font-mono focus:ring-2 focus:ring-emerald-500">
                                        </td>

                                        <!-- Fecha Vencimiento -->
                                        <td class="py-2.5 px-2">
                                            <input type="date" 
                                                   :name="'productos[' + idx + '][fecha_vencimiento]'" 
                                                   x-model="item.fecha_vencimiento" 
                                                   required
                                                   class="w-full px-2 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                                        </td>

                                        <!-- Subtotal -->
                                        <td class="py-2.5 px-3 text-right font-bold text-slate-900 dark:text-white">
                                            $<span x-text="calcularSubtotal(item)"></span>
                                        </td>

                                        <!-- Botón Eliminar Fila -->
                                        <td class="py-2.5 px-2 text-center">
                                            <button type="button" 
                                                    @click="eliminarItem(idx)" 
                                                    title="Eliminar fila"
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
            </div>
        </template>


        <!-- ============================================================== -->
        <!-- VISTA MODERNA (TARJETAS ESPACIOSAS / HEADER STRIPS CON SVG)   -->
        <!-- ============================================================== -->
        <template x-if="formLayout === 'modern'">
            <div class="space-y-5 animate-fadeIn">
                
                <!-- Tarjeta 1: Datos del Comprobante, Proveedor y Motivo -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-300 dark:border-slate-800 shadow-md overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-slate-200 dark:border-slate-800 bg-slate-100/70 dark:bg-slate-800/60 flex items-center space-x-2">
                        <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200">
                            Datos de la Factura y Razón del Cambio
                        </h2>
                    </div>

                    <div class="p-5 space-y-4">
                        <!-- Motivo Modificación -->
                        <div>
                            <label class="block text-xs font-bold text-amber-800 dark:text-amber-300 mb-1.5">
                                Motivo de la Modificación <span class="text-rose-500">*</span>
                            </label>
                            <textarea name="motivo_modificacion" 
                                      x-model="formData.motivo_modificacion" 
                                      required
                                      minlength="5"
                                      rows="2" 
                                      placeholder="Explique el motivo del cambio (ej: Corrección en precio o cantidad de lote)..."
                                      class="w-full px-3.5 py-2 bg-amber-50/40 dark:bg-slate-800 border border-amber-300 dark:border-amber-700 rounded-xl text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition shadow-2xs"></textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Proveedor -->
                            <div>
                                <label class="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-1.5">
                                    Proveedor Registrado <span class="text-rose-500">*</span>
                                </label>
                                <select name="proveedor_id" 
                                        x-model="formData.proveedor_id" 
                                        required
                                        class="w-full px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white font-semibold focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition shadow-2xs">
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
                                <label class="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-1.5">
                                    Número de Comprobante / Factura
                                </label>
                                <input type="text" 
                                       name="numero_comprobante" 
                                       x-model="formData.numero_comprobante" 
                                       placeholder="Ej: F001-0004523"
                                       class="w-full px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition shadow-2xs">
                            </div>

                            <!-- Fecha -->
                            <div>
                                <label class="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-1.5">
                                    Fecha de Recepción / Emisión <span class="text-rose-500">*</span>
                                </label>
                                <input type="date" 
                                       name="fecha" 
                                       x-model="formData.fecha" 
                                       required
                                       class="w-full px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white font-semibold focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition shadow-2xs">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tarjeta 2: Modificación de Medicamentos y Lotes -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-300 dark:border-slate-800 shadow-md overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-slate-200 dark:border-slate-800 bg-slate-100/70 dark:bg-slate-800/60 flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200">
                                Medicamentos, Presentaciones y Lotes
                            </h2>
                        </div>

                        <button type="button" 
                                @click="agregarItem()" 
                                class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span>Agregar Fármaco / Lote</span>
                        </button>
                    </div>

                    <div class="p-5 space-y-4">
                        <template x-for="(item, idx) in items" :key="item.uid">
                            <div class="p-4 rounded-xl bg-slate-50/80 dark:bg-slate-800/50 border border-slate-300 dark:border-slate-700 space-y-3 transition">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300 flex items-center space-x-1.5">
                                        <span class="w-5 h-5 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 flex items-center justify-center text-[10px] font-bold" x-text="idx + 1"></span>
                                        <span>Lote de Compra</span>
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
                                                class="w-full px-3 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 font-medium">
                                            <option value="">-- Seleccione Medicamento --</option>
                                            <template x-for="p in catalogo" :key="p.id">
                                                <option :value="p.id" x-text="p.nombre + (p.principio_activo ? ' (' + p.principio_activo + ')' : '')"></option>
                                            </template>
                                        </select>
                                    </div>

                                    <!-- Presentación -->
                                    <div class="md:col-span-3">
                                        <div class="flex items-center justify-between mb-1">
                                            <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300">
                                                Presentación
                                            </label>
                                            <button type="button" 
                                                    @click="abrirModalPresentacion(idx)"
                                                    :disabled="!item.producto_id"
                                                    class="text-[10px] text-emerald-600 dark:text-emerald-400 hover:underline font-bold disabled:opacity-40">
                                                + Nueva
                                            </button>
                                        </div>
                                        <select :name="'productos[' + idx + '][presentacion_id]'" 
                                                x-model="item.presentacion_id" 
                                                @change="onPresentacionChange(idx)"
                                                class="w-full px-3 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                                            <option value="">Unidad Base (x1)</option>
                                            <template x-for="pres in item.presentacionesDisponibles" :key="pres.id">
                                                <option :value="pres.id" x-text="pres.nombre + ' (x' + pres.unidades + ')'"></option>
                                            </template>
                                        </select>
                                        <div class="text-[10px] text-emerald-600 dark:text-emerald-400 font-semibold mt-1" x-show="item.factor > 1">
                                            Total Base: <span x-text="calcularUnidadesBase(item)"></span> u.
                                        </div>
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

                <!-- Tarjeta 3: Totales y Acciones (Light Mode Blanco) -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-300 dark:border-slate-800 shadow-md flex flex-col md:flex-row items-center justify-between gap-4">
                    <div class="flex items-center space-x-6 text-xs text-slate-600 dark:text-slate-300">
                        <div>
                            <span class="block text-slate-400 text-[11px]">Total de Líneas:</span>
                            <span class="font-bold text-slate-900 dark:text-white text-sm" x-text="items.length"></span>
                        </div>
                        <div>
                            <span class="block text-slate-400 text-[11px]">Unidades al Kardex:</span>
                            <span class="font-bold text-emerald-600 dark:text-emerald-400 text-sm" x-text="calcularTotalUnidadesBase() + ' u.'"></span>
                        </div>
                        <div>
                            <span class="block text-slate-400 text-[11px]">Total Recalculado:</span>
                            <span class="font-extrabold text-emerald-600 dark:text-emerald-400 text-xl">
                                $<span x-text="calcularTotalGeneral()"></span>
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center space-x-3 w-full md:w-auto">
                        <a href="{{ route('compras.show', $compra) }}" 
                           class="flex-1 md:flex-none px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition text-center">
                            Cancelar
                        </a>
                        <button type="submit" 
                                :disabled="!formData.motivo_modificacion || formData.motivo_modificacion.trim().length < 5"
                                class="flex-1 md:flex-none px-6 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 disabled:opacity-50 text-white text-xs font-bold shadow-xs transition flex items-center justify-center space-x-2 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Actualizar Versión de Compra</span>
                        </button>
                    </div>
                </div>

            </div>
        </template>

    </form>

    <!-- ============================================================== -->
    <!-- MODAL RÁPIDO: CREAR NUEVA PRESENTACIÓN                        -->
    <!-- ============================================================== -->
    <div x-show="modalNuevaPres" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity"
         @keydown.escape.window="modalNuevaPres = false">
        
        <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full p-5 border border-slate-300 dark:border-slate-800 shadow-xl space-y-4"
             @click.outside="modalNuevaPres = false">
            
            <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Nueva Presentación</h3>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400" x-text="nuevaPres.producto_nombre"></p>
                    </div>
                </div>
                <button @click="modalNuevaPres = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">✕</button>
            </div>

            <!-- Error Banner in Modal -->
            <div x-show="nuevaPres.error" class="p-2.5 rounded-lg bg-rose-50 text-rose-700 text-xs font-medium border border-rose-200" x-text="nuevaPres.error"></div>

            <div class="space-y-3">
                <!-- Nombre Presentación -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                        Nombre de la Presentación <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" 
                           x-model="nuevaPres.nombre" 
                           placeholder="Ej: Blíster x 10, Caja x 100, Frasco 120ml"
                           class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 font-semibold">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <!-- Factor / Unidades -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Unidades Contenidas <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" 
                               x-model.number="nuevaPres.unidades_por_presentacion" 
                               min="1" 
                               required
                               placeholder="10"
                               class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white font-bold focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <!-- Código de Barras -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Código de Barras (Opcional)
                        </label>
                        <input type="text" 
                               x-model="nuevaPres.codigo_barras" 
                               placeholder="775123456"
                               class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white font-mono focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <!-- Precio Compra -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Precio de Compra ($)
                        </label>
                        <input type="number" 
                               step="0.01" 
                               min="0" 
                               x-model="nuevaPres.precio_compra" 
                               placeholder="0.00"
                               class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white font-bold text-right focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <!-- Precio Venta -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Precio de Venta ($)
                        </label>
                        <input type="number" 
                               step="0.01" 
                               min="0" 
                               x-model="nuevaPres.precio_venta" 
                               placeholder="0.00"
                               class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white font-bold text-right focus:ring-2 focus:ring-emerald-500">
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end space-x-2 pt-3 border-t border-slate-200 dark:border-slate-800">
                <button type="button" 
                        @click="modalNuevaPres = false" 
                        class="px-3.5 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                    Cancelar
                </button>
                <button type="button" 
                        @click="guardarNuevaPresentacion()" 
                        :disabled="nuevaPres.cargando"
                        class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-xs transition flex items-center space-x-1.5 cursor-pointer disabled:opacity-50">
                    <span x-show="!nuevaPres.cargando">Crear y Seleccionar</span>
                    <span x-show="nuevaPres.cargando">Guardando...</span>
                </button>
            </div>
        </div>
    </div>

</div>
@endsection