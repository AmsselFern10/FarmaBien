@extends('layouts.app')

@section('title', 'Terminal Punto de Venta (POS)')

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
    
    // Buscador y filtros
    busqueda: '',
    filtroCategoriaId: '',
    
    // Datos de la Venta
    formData: {
        cliente_id: '',
        tipo_comprobante: 'ticket',
        serie: '',
        numero_comprobante: '',
        metodo_pago: 'efectivo',
        descuento: 0,
        monto_recibido: ''
    },
    
    // Ítems del Carrito
    items: [],
    
    // Modales
    modalCobro: false,
    modalTicketPreview: false,
    modalReceta: false,
    ticketData: null,
    procesandoVenta: false,
    errorMsg: '',

    // Receta médica temporal
    recetaInfo: {
        medico_nombre: '',
        medico_cmp: '',
        paciente_nombre: '',
        observaciones: ''
    },

    // Métodos del Carrito
    agregarAlCarrito(producto, presentacionId = null, loteId = null) {
        if (!producto || !producto.lotes || producto.lotes.length === 0) {
            alert('Este medicamento no tiene lotes con stock disponible.');
            return;
        }

        // Seleccionar lote por defecto (primer lote FEFO)
        const loteDefault = loteId ? producto.lotes.find(l => l.id == loteId) : producto.lotes[0];
        if (!loteDefault) {
            alert('Lote no disponible.');
            return;
        }

        // Presentaciones disponibles (Unidad base + presentaciones activas)
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

        // Verificar si ya está en carrito con el mismo lote y presentación
        const existeIdx = this.items.findIndex(it => it.producto_id == producto.id && it.lote_id == loteDefault.id && it.presentacion_id == presentacionId);
        
        if (existeIdx !== -1) {
            this.items[existeIdx].cantidad++;
            return;
        }

        // Determinar presentación inicial
        let presSel = presDisponibles[0];
        if (presentacionId) {
            const encontrada = presDisponibles.find(p => p.id == presentacionId);
            if (encontrada) presSel = encontrada;
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
            presentacion_id: presSel.id,
            factor: presSel.unidades,
            precio_unitario: presSel.precio,
            cantidad: 1
        });
    },

    agregarItemVacio() {
        if (this.catalogo.length === 0) return;
        const p = this.catalogo[0];
        this.agregarAlCarrito(p);
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

    limpiarVenta() {
        if (this.items.length > 0 && !confirm('¿Desea vaciar el carrito actual?')) {
            return;
        }
        this.items = [];
        this.formData.descuento = 0;
        this.formData.monto_recibido = '';
        this.errorMsg = '';
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
    },

    calcularVuelto() {
        const total = parseFloat(this.calcularTotalGeneral()) || 0;
        const recibido = parseFloat(this.formData.monto_recibido) || 0;
        return Math.max(0, recibido - total).toFixed(2);
    },

    setMontoRecibido(monto) {
        this.formData.monto_recibido = monto;
    },

    tieneProductosRx() {
        return this.items.some(it => it.requiere_receta);
    },

    // Filtrar catálogo interactivo para vista moderna
    productosFiltrados() {
        let prods = this.catalogo;
        if (this.filtroCategoriaId) {
            prods = prods.filter(p => p.categoria_id == this.filtroCategoriaId);
        }
        if (this.busqueda && this.busqueda.trim().length > 0) {
            const q = this.busqueda.toLowerCase().trim();
            prods = prods.filter(p => 
                (p.nombre && p.nombre.toLowerCase().includes(q)) ||
                (p.principio_activo && p.principio_activo.toLowerCase().includes(q)) ||
                (p.codigo_barra && p.codigo_barra.includes(q))
            );
        }
        return prods;
    },

    // Enviar venta por AJAX / Form
    async procesarVentaFinal() {
        if (this.items.length === 0) {
            alert('El carrito está vacío. Agregue al menos un medicamento.');
            return;
        }

        // Validar stocks
        for (let it of this.items) {
            const uTotales = this.calcularUnidadesBase(it);
            if (it.lote_obj && it.lote_obj.stock_actual < uTotales) {
                alert(`Stock insuficiente en el lote ${it.lote_obj.numero_lote} de ${it.nombre}. Disponible: ${it.lote_obj.stock_actual} unid., Solicitado: ${uTotales} unid.`);
                return;
            }
        }

        this.procesandoVenta = true;
        this.errorMsg = '';

        try {
            const payload = {
                cliente_id: this.formData.cliente_id || null,
                tipo_comprobante: this.formData.tipo_comprobante,
                serie: this.formData.serie || null,
                numero_comprobante: this.formData.numero_comprobante || null,
                metodo_pago: this.formData.metodo_pago,
                descuento: parseFloat(this.formData.descuento) || 0,
                productos: this.items.map(it => ({
                    producto_id: it.producto_id,
                    lote_id: it.lote_id,
                    presentacion_id: it.presentacion_id || null,
                    cantidad: parseInt(it.cantidad) || 1,
                    precio_unitario: parseFloat(it.precio_unitario) || 0
                }))
            };

            const token = document.querySelector('meta[name=\'csrf-token\']')?.getAttribute('content') || '';
            const res = await fetch('{{ route('ventas.store') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify(payload)
            });

            const data = await res.json();
            if (!res.ok || !data.success) {
                throw new Error(data.message || 'Error al procesar la venta');
            }

            // Éxito: abrir preview de ticket o redirigir
            this.modalCobro = false;
            if (data.ticket_url) {
                window.open(data.ticket_url, '_blank', 'width=400,height=600');
            }
            window.location.href = '{{ route('ventas.index') }}';

        } catch (err) {
            this.errorMsg = err.message;
        } finally {
            this.procesandoVenta = false;
        }
    },

    // Escanear código de barras con Enter
    buscarPorCodigoBarra() {
        if (!this.busqueda) return;
        const match = this.catalogo.find(p => p.codigo_barra && p.codigo_barra.trim() === this.busqueda.trim());
        if (match) {
            this.agregarAlCarrito(match);
            this.busqueda = '';
        }
    }
}"
@keydown.window="
    if ($event.key === 'F2') { $event.preventDefault(); document.getElementById('posBuscador')?.focus(); }
    if ($event.key === 'F4' && items.length > 0) { $event.preventDefault(); modalCobro = true; }
    if ($event.key === 'Escape' && !modalCobro && !modalTicketPreview) { limpiarVenta(); }
"
class="space-y-4">

    <!-- Header & Mode Switcher -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-1 border-b border-slate-300/80 dark:border-slate-800">
        <div>
            <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
                <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <a href="{{ route('ventas.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Ventas</a>
                <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="text-slate-800 dark:text-slate-200 font-semibold">Terminal POS Mostrador</span>
            </nav>
            <h1 class="text-lg sm:text-xl font-bold text-slate-900 dark:text-white flex items-center space-x-2">
                <span>Punto de Venta (POS Rápido)</span>
            </h1>
        </div>

        <div class="flex items-center space-x-3 self-start sm:self-auto">
            <!-- Mode Switcher -->
            <div class="inline-flex items-center p-0.5 rounded-xl bg-slate-200/80 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-xs font-semibold shadow-2xs">
                <button type="button" 
                        @click="setLayout('modern')"
                        :class="formLayout === 'modern' ? 'bg-white dark:bg-slate-700 text-emerald-700 dark:text-emerald-400 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200'"
                        class="px-2.5 py-1 rounded-lg transition flex items-center space-x-1.5 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    <span>Moderna (Touch)</span>
                </button>
                <button type="button" 
                        @click="setLayout('compact')"
                        :class="formLayout === 'compact' ? 'bg-white dark:bg-slate-700 text-emerald-700 dark:text-emerald-400 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200'"
                        class="px-2.5 py-1 rounded-lg transition flex items-center space-x-1.5 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <span>Compacta (ERP)</span>
                </button>
            </div>

            <a href="{{ route('ventas.index') }}" 
               class="px-3 py-1.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold transition flex items-center space-x-1.5 shrink-0 shadow-2xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Volver</span>
            </a>
        </div>
    </div>

    <!-- Error Alert Box -->
    <div x-show="errorMsg" x-cloak class="p-3 rounded-xl bg-rose-50 dark:bg-rose-950/60 border border-rose-300 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-xs flex items-center justify-between">
        <div class="flex items-center space-x-2">
            <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span x-text="errorMsg"></span>
        </div>
        <button type="button" @click="errorMsg = ''" class="text-slate-400 hover:text-slate-600">&times;</button>
    </div>

    <!-- ============================================================== -->
    <!-- MODO 1: VISTA COMPACTA (ERP / TECLADO RÁPIDO / ALTA DENSIDAD)  -->
    <!-- ============================================================== -->
    <template x-if="formLayout === 'compact'">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-300 dark:border-slate-800 shadow-md p-4 space-y-4 animate-fadeIn">
            
            <!-- Toolbar Superior -->
            <div class="flex items-center justify-between pb-3 border-b border-slate-300 dark:border-slate-800 bg-slate-100/80 dark:bg-slate-800/60 -mx-4 -mt-4 p-3 rounded-t-2xl">
                <div class="flex items-center space-x-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shadow-xs"></span>
                    <span class="text-xs font-extrabold text-slate-800 dark:text-slate-200 uppercase tracking-wide">TERMINAL POS DE VENTA RÁPIDA</span>
                    <span class="text-[10px] px-2 py-0.5 rounded-md bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-mono font-bold hidden sm:inline">F2 = Buscar | F4 = Cobrar | Esc = Limpiar</span>
                </div>

                <div class="flex items-center space-x-2">
                    <button type="button" 
                            @click="limpiarVenta()"
                            class="px-3 py-1.5 rounded-lg border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-800 text-xs font-bold transition">
                        Limpiar (Esc)
                    </button>
                    <button type="button" 
                            @click="modalCobro = true"
                            :disabled="items.length === 0"
                            class="px-4 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 disabled:opacity-40 text-white text-xs font-extrabold shadow-sm transition flex items-center space-x-1.5 cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <span>Cobrar (F4)</span>
                    </button>
                </div>
            </div>

            <!-- Grid de Paneles Superiores (Cliente + Resumen Liquidación) -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
                
                <!-- Panel 1: Datos del Cliente y Comprobante (8 cols) -->
                <div class="lg:col-span-8 p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/40 dark:bg-slate-800/30 space-y-3">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center space-x-1.5 border-b border-slate-200/60 dark:border-slate-700/60 pb-1.5">
                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <span>1. Identificación del Cliente & Comprobante</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-2.5">
                        <!-- Cliente (Col 6) -->
                        <div class="sm:col-span-6">
                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Cliente
                            </label>
                            <select x-model="formData.cliente_id" 
                                    class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white font-medium focus:ring-1 focus:ring-emerald-500">
                                <option value="">Público General (Venta Libre)</option>
                                <template x-for="cl in clientes" :key="cl.id">
                                    <option :value="cl.id" x-text="cl.nombre + (cl.documento ? ' (' + cl.documento + ')' : '')"></option>
                                </template>
                            </select>
                        </div>

                        <!-- Tipo Comprobante (Col 3) -->
                        <div class="sm:col-span-3">
                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Comprobante
                            </label>
                            <select x-model="formData.tipo_comprobante" 
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
                            <select x-model="formData.metodo_pago" 
                                    class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white font-medium focus:ring-1 focus:ring-emerald-500">
                                <option value="efectivo">Efectivo</option>
                                <option value="tarjeta">Tarjeta (POS)</option>
                                <option value="transferencia">Yape / Plin / Transferencia</option>
                                <option value="mixto">Pago Mixto</option>
                            </select>
                        </div>
                    </div>

                    <!-- Buscador Rápido con Código de Barras -->
                    <div class="pt-1">
                        <div class="relative">
                            <input type="text" 
                                   id="posBuscador"
                                   x-model="busqueda" 
                                   @keydown.enter.prevent="buscarPorCodigoBarra()"
                                   placeholder="[F2] Escanear código de barras o escribir medicamento / principio activo..."
                                   class="w-full pl-8 pr-20 py-2 bg-emerald-50/50 dark:bg-slate-800 border border-emerald-300 dark:border-emerald-700 rounded-xl text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-emerald-500 font-medium">
                            <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-emerald-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </div>
                            <div class="absolute inset-y-0 right-0 pr-2 flex items-center space-x-1">
                                <span class="text-[10px] font-mono bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-500 dark:text-slate-300 px-1.5 py-0.5 rounded">Enter</span>
                            </div>
                        </div>

                        <!-- Dropdown de Resultados Rápidos al Escribir -->
                        <div x-show="busqueda.trim().length >= 2" 
                             x-cloak 
                             class="absolute z-30 mt-1 w-full max-w-xl bg-white dark:bg-slate-800 rounded-xl shadow-xl border border-slate-200 dark:border-slate-700 max-h-60 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-700">
                            <template x-for="prod in productosFiltrados().slice(0, 8)" :key="prod.id">
                                <div @click="agregarAlCarrito(prod); busqueda = '';" 
                                     class="p-2.5 hover:bg-emerald-50 dark:hover:bg-slate-700/60 cursor-pointer flex items-center justify-between transition">
                                    <div>
                                        <div class="font-bold text-xs text-slate-800 dark:text-white flex items-center space-x-1.5">
                                            <span x-text="prod.nombre"></span>
                                            <span x-show="prod.requiere_receta" class="text-[9px] px-1.5 py-0.2 rounded font-bold bg-amber-500 text-white">Rx</span>
                                        </div>
                                        <div class="text-[10px] text-slate-500 dark:text-slate-400">
                                            <span x-text="prod.principio_activo || 'Fórmula general'"></span> &bull; 
                                            Lote FEFO: <span class="font-semibold text-emerald-600" x-text="prod.lotes && prod.lotes[0] ? prod.lotes[0].numero_lote + ' (Stock: ' + prod.lotes[0].stock_actual + ')' : 'Sin stock'"></span>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="font-extrabold text-xs text-slate-900 dark:text-white" x-text="'$' + parseFloat(prod.precio_venta).toFixed(2)"></span>
                                    </div>
                                </div>
                            </template>
                            <div x-show="productosFiltrados().length === 0" class="p-3 text-center text-xs text-slate-400">
                                No se encontraron medicamentos con stock disponible para "<span x-text="busqueda"></span>"
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Panel 2: Resumen de Liquidación (4 cols) -->
                <div class="lg:col-span-4 p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/40 dark:bg-slate-800/30 flex flex-col justify-between space-y-2">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center space-x-1.5 border-b border-slate-200/60 dark:border-slate-700/60 pb-1.5">
                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Resumen de Liquidación</span>
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div>
                            <span class="text-[10px] font-semibold text-slate-500 block">Ítems / Líneas:</span>
                            <span class="font-bold text-slate-900 dark:text-white text-sm" x-text="items.length + ' ítems'"></span>
                        </div>
                        <div>
                            <span class="text-[10px] font-semibold text-slate-500 block">Descuento ($):</span>
                            <input type="number" 
                                   step="0.10" 
                                   min="0"
                                   x-model="formData.descuento" 
                                   class="w-20 px-1.5 py-0.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded text-xs font-bold text-slate-900 dark:text-white text-right">
                        </div>
                    </div>

                    <!-- Total Gigante -->
                    <div class="pt-1.5 border-t border-slate-200/80 dark:border-slate-700/80 flex items-center justify-between">
                        <span class="text-xs font-semibold text-slate-600 dark:text-slate-300">Total a Pagar:</span>
                        <span class="text-2xl font-black text-emerald-600 dark:text-emerald-400">
                            $<span x-text="calcularTotalGeneral()"></span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Panel 3: Desglose de Fármacos, Presentaciones y Lotes FEFO -->
            <div class="p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/40 dark:bg-slate-800/30 space-y-3">
                <div class="flex items-center justify-between border-b border-slate-200/60 dark:border-slate-700/60 pb-1.5">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center space-x-1.5">
                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                        <span>2. Desglose de Medicamentos, Presentaciones y Lotes (FEFO)</span>
                    </div>

                    <button type="button" 
                            @click="agregarItemVacio()" 
                            class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-bold shadow-2xs transition flex items-center space-x-1 cursor-pointer">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Agregar Fármaco</span>
                    </button>
                </div>

                <!-- Tabla de Venta -->
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
                                    <!-- Indice -->
                                    <td class="py-2 px-2 text-center text-[11px] font-bold text-slate-400" x-text="idx + 1"></td>

                                    <!-- Producto -->
                                    <td class="py-2 px-3">
                                        <div class="flex items-center space-x-1.5">
                                            <select x-model="item.producto_id" 
                                                    @change="onProductoChange(idx)"
                                                    class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg text-xs text-slate-900 dark:text-white font-medium focus:ring-1 focus:ring-emerald-500">
                                                <template x-for="p in catalogo" :key="p.id">
                                                    <option :value="p.id" x-text="p.nombre + (p.principio_activo ? ' (' + p.principio_activo + ')' : '')"></option>
                                                </template>
                                            </select>
                                            <span x-show="item.requiere_receta" 
                                                  title="Requiere Receta Médica" 
                                                  class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-amber-500 text-white shrink-0">
                                                Rx
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Presentación -->
                                    <td class="py-2 px-3">
                                        <select x-model="item.presentacion_id" 
                                                @change="onPresentacionChange(idx)"
                                                class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                                            <template x-for="pres in item.presentacionesDisponibles" :key="pres.id">
                                                <option :value="pres.id" x-text="pres.nombre + ' (x' + pres.unidades + ')'"></option>
                                            </template>
                                        </select>
                                        <div class="text-[10px] text-emerald-600 dark:text-emerald-400 font-semibold mt-0.5" x-show="item.factor > 1">
                                            Total Base: <span x-text="calcularUnidadesBase(item)"></span> u.
                                        </div>
                                    </td>

                                    <!-- Lote FEFO -->
                                    <td class="py-2 px-3">
                                        <select x-model="item.lote_id" 
                                                @change="onLoteChange(idx)"
                                                class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg text-xs text-slate-900 dark:text-white font-mono focus:ring-1 focus:ring-emerald-500">
                                            <template x-for="l in item.lotesDisponibles" :key="l.id">
                                                <option :value="l.id" x-text="l.numero_lote + ' (Vence: ' + (l.fecha_vencimiento ? l.fecha_vencimiento.substring(0, 10) : 'N/A') + ' | Stock: ' + l.stock_actual + ')'"></option>
                                            </template>
                                        </select>
                                    </td>

                                    <!-- Cantidad -->
                                    <td class="py-2 px-2">
                                        <input type="number" 
                                               x-model.number="item.cantidad" 
                                               min="1" 
                                               class="w-full px-2 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg text-xs text-slate-900 dark:text-white font-bold text-center focus:ring-1 focus:ring-emerald-500">
                                    </td>

                                    <!-- Precio Venta -->
                                    <td class="py-2 px-2 text-right">
                                        <input type="number" 
                                               step="0.01" 
                                               min="0" 
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
                            <tr x-show="items.length === 0">
                                <td colspan="8" class="py-8 text-center text-slate-400 text-xs">
                                    El carrito está vacío. Escanee un código de barras o use el buscador superior [F2].
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Footer / Toolbar Inferior -->
            <div class="flex items-center justify-between pt-2 border-t border-slate-200 dark:border-slate-800 text-slate-400 text-[11px]">
                <div class="flex items-center space-x-2">
                    <span x-show="tieneProductosRx()" class="px-2 py-0.5 rounded bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 font-bold">
                        ⚠️ Contiene fármacos que requieren Receta Médica
                    </span>
                    <span x-show="!tieneProductosRx()">Venta lista para despacho.</span>
                </div>
                <button type="button" 
                        @click="modalCobro = true"
                        :disabled="items.length === 0"
                        class="px-5 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 disabled:opacity-40 text-white text-xs font-extrabold shadow-sm transition flex items-center space-x-1.5 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <span>Cobrar Venta (F4) &bull; $<span x-text="calcularTotalGeneral()"></span></span>
                </button>
            </div>
        </div>
    </template>


    <!-- ============================================================== -->
    <!-- MODO 2: VISTA MODERNA (GRID TOUCH + PANEL LATERAL DE CARRITO)  -->
    <!-- ============================================================== -->
    <template x-if="formLayout === 'modern'">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 animate-fadeIn">
            
            <!-- Columna Izquierda: Catálogo Interactivo (7 cols) -->
            <div class="lg:col-span-7 space-y-4">
                <!-- Buscador y Filtro de Categoría -->
                <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-300 dark:border-slate-800 shadow-xs space-y-3">
                    <div class="relative">
                        <input type="text" 
                               x-model="busqueda" 
                               placeholder="Buscar medicamento, principio activo o código..." 
                               class="w-full pl-9 pr-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-emerald-500">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                    </div>

                    <!-- Pills de Categorías -->
                    <div class="flex items-center space-x-1.5 overflow-x-auto pb-1 text-xs">
                        <button type="button" 
                                @click="filtroCategoriaId = ''"
                                :class="filtroCategoriaId === '' ? 'bg-emerald-600 text-white font-bold' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200'"
                                class="px-3 py-1 rounded-lg shrink-0 transition">
                            Todos
                        </button>
                        <template x-for="cat in categorias" :key="cat.id">
                            <button type="button" 
                                    @click="filtroCategoriaId = cat.id"
                                    :class="filtroCategoriaId == cat.id ? 'bg-emerald-600 text-white font-bold' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200'"
                                    class="px-3 py-1 rounded-lg shrink-0 transition" 
                                    x-text="cat.nombre">
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Grid de Tarjetas de Medicamentos -->
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3">
                    <template x-for="prod in productosFiltrados()" :key="prod.id">
                        <div @click="agregarAlCarrito(prod)"
                             class="group bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-3 shadow-xs hover:border-emerald-500 hover:shadow-md transition cursor-pointer flex flex-col justify-between space-y-2">
                            <div>
                                <div class="flex items-start justify-between gap-1">
                                    <h4 class="font-bold text-xs text-slate-900 dark:text-white group-hover:text-emerald-600 transition" x-text="prod.nombre"></h4>
                                    <span x-show="prod.requiere_receta" class="text-[9px] px-1 py-0.5 rounded font-bold bg-amber-500 text-white shrink-0">Rx</span>
                                </div>
                                <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5" x-text="prod.principio_activo || 'Fórmula general'"></p>
                            </div>

                            <div class="pt-2 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between">
                                <span class="text-[10px] font-semibold text-slate-500">
                                    Lote FEFO: <span class="text-emerald-600" x-text="prod.lotes && prod.lotes[0] ? prod.lotes[0].stock_actual + ' u.' : '0'"></span>
                                </span>
                                <span class="text-xs font-black text-slate-900 dark:text-white" x-text="'$' + parseFloat(prod.precio_venta).toFixed(2)"></span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Columna Derecha: Carrito & Cobro (5 cols) -->
            <div class="lg:col-span-5 space-y-4">
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-300 dark:border-slate-800 shadow-md p-4 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800">
                        <div class="flex items-center space-x-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shadow-xs"></span>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200">Carrito de Despacho</h3>
                        </div>
                        <button type="button" @click="limpiarVenta()" class="text-xs text-rose-600 hover:underline">Vaciar</button>
                    </div>

                    <!-- Lista de Ítems en Carrito -->
                    <div class="space-y-3 max-h-96 overflow-y-auto pr-1">
                        <template x-for="(item, idx) in items" :key="item.uid">
                            <div class="p-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/40 space-y-2">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <div class="font-bold text-xs text-slate-900 dark:text-white" x-text="item.nombre"></div>
                                        <div class="text-[10px] text-slate-500" x-text="item.principio_activo"></div>
                                    </div>
                                    <button type="button" @click="eliminarItem(idx)" class="text-slate-400 hover:text-rose-600">&times;</button>
                                </div>

                                <div class="grid grid-cols-2 gap-2 text-xs">
                                    <!-- Presentación -->
                                    <div>
                                        <label class="block text-[9px] font-semibold text-slate-500 mb-0.5">Presentación</label>
                                        <select x-model="item.presentacion_id" @change="onPresentacionChange(idx)" class="w-full px-2 py-1 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded text-[11px]">
                                            <template x-for="pres in item.presentacionesDisponibles" :key="pres.id">
                                                <option :value="pres.id" x-text="pres.nombre + ' (x' + pres.unidades + ')'"></option>
                                            </template>
                                        </select>
                                    </div>

                                    <!-- Lote FEFO -->
                                    <div>
                                        <label class="block text-[9px] font-semibold text-slate-500 mb-0.5">Lote (FEFO)</label>
                                        <select x-model="item.lote_id" @change="onLoteChange(idx)" class="w-full px-2 py-1 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded text-[11px] font-mono">
                                            <template x-for="l in item.lotesDisponibles" :key="l.id">
                                                <option :value="l.id" x-text="l.numero_lote + ' (' + l.stock_actual + 'u)'"></option>
                                            </template>
                                        </select>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between pt-1">
                                    <div class="flex items-center space-x-2">
                                        <button type="button" @click="if(item.cantidad > 1) item.cantidad--" class="w-6 h-6 rounded bg-slate-200 dark:bg-slate-700 font-bold text-xs">-</button>
                                        <span class="font-bold text-xs text-slate-900 dark:text-white" x-text="item.cantidad"></span>
                                        <button type="button" @click="item.cantidad++" class="w-6 h-6 rounded bg-slate-200 dark:bg-slate-700 font-bold text-xs">+</button>
                                    </div>
                                    <div class="font-bold text-xs text-slate-900 dark:text-white" x-text="'$' + calcularSubtotal(item)"></div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Totales y Botón Cobro -->
                    <div class="pt-3 border-t border-slate-200 dark:border-slate-800 space-y-2">
                        <div class="flex justify-between text-xs text-slate-600 dark:text-slate-300">
                            <span>Subtotal:</span>
                            <span class="font-bold" x-text="'$' + calcularSubtotalGeneral()"></span>
                        </div>
                        <div class="flex justify-between text-xs text-slate-600 dark:text-slate-300">
                            <span>Descuento:</span>
                            <span class="font-bold text-emerald-600" x-text="'-$' + (parseFloat(formData.descuento) || 0).toFixed(2)"></span>
                        </div>
                        <div class="flex justify-between text-base font-black text-slate-900 dark:text-white pt-2 border-t border-slate-200 dark:border-slate-800">
                            <span>Total a Pagar:</span>
                            <span class="text-xl text-emerald-600 dark:text-emerald-400" x-text="'$' + calcularTotalGeneral()"></span>
                        </div>

                        <button type="button" 
                                @click="modalCobro = true"
                                :disabled="items.length === 0"
                                class="w-full py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 disabled:opacity-40 text-white text-xs font-extrabold shadow-sm transition flex items-center justify-center space-x-2 cursor-pointer mt-3">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            <span>Cobrar y Emitir Comprobante (F4)</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>


    <!-- ============================================================== -->
    <!-- MODAL DE COBRO & CONFIRMACIÓN (CALCULADORA DE VUELTO)          -->
    <!-- ============================================================== -->
    <div x-show="modalCobro" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto" 
         aria-labelledby="modal-cobro-title" 
         role="dialog" 
         aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="modalCobro" 
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" 
                 @click="modalCobro = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="modalCobro" 
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 class="inline-block align-bottom bg-white dark:bg-slate-900 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-300 dark:border-slate-800">
                
                <div class="p-6 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800">
                        <div class="flex items-center space-x-2">
                            <span class="w-3 h-3 rounded-full bg-emerald-500 shadow-xs"></span>
                            <h3 class="text-sm font-black uppercase text-slate-800 dark:text-slate-200">
                                Cobro y Liquidación de Venta
                            </h3>
                        </div>
                        <button type="button" @click="modalCobro = false" class="text-slate-400 hover:text-slate-600 text-lg font-bold">&times;</button>
                    </div>

                    <!-- Total Gigante en Modal -->
                    <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 text-center">
                        <span class="text-xs font-semibold text-slate-500 block">Total a Cobrar</span>
                        <span class="text-3xl font-black text-emerald-600 dark:text-emerald-400">
                            $<span x-text="calcularTotalGeneral()"></span>
                        </span>
                    </div>

                    <!-- Método de Pago -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Método de Pago
                        </label>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                            <button type="button" 
                                    @click="formData.metodo_pago = 'efectivo'"
                                    :class="formData.metodo_pago === 'efectivo' ? 'bg-emerald-600 text-white font-bold' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700'"
                                    class="py-2 rounded-xl text-xs transition">
                                💵 Efectivo
                            </button>
                            <button type="button" 
                                    @click="formData.metodo_pago = 'tarjeta'"
                                    :class="formData.metodo_pago === 'tarjeta' ? 'bg-emerald-600 text-white font-bold' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700'"
                                    class="py-2 rounded-xl text-xs transition">
                                💳 Tarjeta
                            </button>
                            <button type="button" 
                                    @click="formData.metodo_pago = 'transferencia'"
                                    :class="formData.metodo_pago === 'transferencia' ? 'bg-emerald-600 text-white font-bold' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700'"
                                    class="py-2 rounded-xl text-xs transition">
                                📱 Yape / Plin
                            </button>
                            <button type="button" 
                                    @click="formData.metodo_pago = 'mixto'"
                                    :class="formData.metodo_pago === 'mixto' ? 'bg-emerald-600 text-white font-bold' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700'"
                                    class="py-2 rounded-xl text-xs transition">
                                🔄 Mixto
                            </button>
                        </div>
                    </div>

                    <!-- Calculadora de Vuelto (Si es Efectivo) -->
                    <div x-show="formData.metodo_pago === 'efectivo'" class="space-y-3 p-3.5 rounded-xl border border-emerald-200 dark:border-emerald-950/40 bg-emerald-50/40 dark:bg-slate-800/40">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                    Monto Recibido ($)
                                </label>
                                <input type="number" 
                                       step="0.10"
                                       x-model="formData.monto_recibido" 
                                       placeholder="0.00"
                                       class="w-full px-3 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-sm font-black text-slate-900 dark:text-white text-right focus:ring-2 focus:ring-emerald-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                    Cambio / Vuelto
                                </label>
                                <div class="w-full px-3 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-sm font-black text-emerald-600 dark:text-emerald-400 text-right" 
                                     x-text="'$' + calcularVuelto()"></div>
                            </div>
                        </div>

                        <!-- Botones de Denominaciones Rápidas -->
                        <div class="flex items-center space-x-1.5 text-xs">
                            <span class="text-[10px] text-slate-500 font-bold">Rápido:</span>
                            <button type="button" @click="setMontoRecibido(calcularTotalGeneral())" class="px-2 py-0.5 rounded bg-white dark:bg-slate-700 border text-[11px] font-semibold">Exacto</button>
                            <button type="button" @click="setMontoRecibido(10)" class="px-2 py-0.5 rounded bg-white dark:bg-slate-700 border text-[11px] font-semibold">$10</button>
                            <button type="button" @click="setMontoRecibido(20)" class="px-2 py-0.5 rounded bg-white dark:bg-slate-700 border text-[11px] font-semibold">$20</button>
                            <button type="button" @click="setMontoRecibido(50)" class="px-2 py-0.5 rounded bg-white dark:bg-slate-700 border text-[11px] font-semibold">$50</button>
                            <button type="button" @click="setMontoRecibido(100)" class="px-2 py-0.5 rounded bg-white dark:bg-slate-700 border text-[11px] font-semibold">$100</button>
                        </div>
                    </div>
                </div>

                <div class="bg-slate-50 dark:bg-slate-800/50 px-6 py-3 border-t border-slate-200 dark:border-slate-800 flex justify-end space-x-2">
                    <button type="button" 
                            @click="modalCobro = false" 
                            class="px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                        Volver a la Venta
                    </button>
                    <button type="button" 
                            @click="procesarVentaFinal()"
                            :disabled="procesandoVenta"
                            class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white rounded-xl text-xs font-bold shadow-xs transition flex items-center space-x-1.5 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span x-text="procesandoVenta ? 'Procesando...' : 'Confirmar & Emitir Ticket'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
