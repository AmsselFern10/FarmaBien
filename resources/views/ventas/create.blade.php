@extends('layouts.app')

@section('title', 'Terminal Punto de Venta (POS) - FarmaBien')

@section('content')
<script>
function posVentaData() {
    return {
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
            referencia_pago: '',
            descuento: 0,
            monto_recibido: ''
        },
        
        // Ítems del Carrito
        items: [],
        
        // Modales
        modalCobro: false,
        modalTicketPreview: false,
        modalInfoProducto: false,
        modalNuevoCliente: false,
        productoInfo: null,
        procesandoVenta: false,
        errorMsg: '',

        // Nuevo Cliente Rápido
        nuevoCliente: {
            nombre: '',
            documento: '',
            telefono: '',
            email: '',
            direccion: ''
        },
        guardandoCliente: false,
        errorClienteMsg: '',

        // Receta médica
        recetaInfo: {
            medico_nombre: '',
            medico_cmp: '',
            paciente_nombre: '',
            observaciones: ''
        },

        init() {
            try {
                const saved = window.farmaGetDraft ? window.farmaGetDraft('{{ request()->getPathInfo() }}', null) : null;
                if (saved) {
                    const hadItems = saved.items && Array.isArray(saved.items) && saved.items.length > 0;
                    const hadFormData = saved.formData && Object.values(saved.formData).some(v => v !== '' && v !== null && v !== undefined && v !== 0 && v !== false);

                    if (saved.formData) Object.assign(this.formData, saved.formData);
                    if (saved.items && Array.isArray(saved.items)) this.items = saved.items;
                    if (saved.recetaInfo) Object.assign(this.recetaInfo, saved.recetaInfo);

                    // Mostrar banner de borrador recuperado (solo en carga de página, no en cambio de layout)
                    if (hadItems || hadFormData) {
                        this.$nextTick(() => {
                            if (window.farmaDraftEngine) {
                                window.farmaDraftEngine.showDraftIndicator(true);
                            }
                        });
                    }
                }
            } catch (e) {}

            this.$watch('formData', () => this.persistirBorrador(), { deep: true });
            this.$watch('items', () => this.persistirBorrador(), { deep: true });
            this.$watch('recetaInfo', () => this.persistirBorrador(), { deep: true });
        },

        persistirBorrador() {
            if (window.farmaSaveDraft) {
                window.farmaSaveDraft('{{ request()->getPathInfo() }}', {
                    formData: this.formData,
                    items: this.items,
                    recetaInfo: this.recetaInfo
                });
            }
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

            // Determinar presentación inicial
            let presSel = presDisponibles[0];
            if (presentacionId) {
                const encontrada = presDisponibles.find(p => p.id == presentacionId);
                if (encontrada) presSel = encontrada;
            }

            // Verificar si ya está en carrito con el mismo lote y presentación
            const existeIdx = this.items.findIndex(it => it.producto_id == producto.id && it.lote_id == loteDefault.id && it.presentacion_id == presSel.id);
            
            if (existeIdx !== -1) {
                this.items[existeIdx].cantidad++;
                return;
            }

            this.items.push({
                uid: Date.now() + Math.random().toString(36).substr(2, 5),
                producto_id: producto.id,
                nombre: producto.nombre,
                principio_activo: producto.principio_activo || '',
                concentracion: producto.concentracion || '',
                laboratorio: producto.laboratorio?.nombre || '',
                ubicacion: producto.ubicacion || 'Sin asignar',
                requiere_receta: !!producto.requiere_receta,
                lotesDisponibles: producto.lotes,
                lote_id: loteDefault.id,
                lote_obj: loteDefault,
                presentacionesDisponibles: presDisponibles,
                presentacion_id: presSel.id,
                factor: presSel.unidades,
                precio_unitario: presSel.precio,
                descuento: 0,
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
            item.concentracion = prod.concentracion || '';
            item.laboratorio = prod.laboratorio?.nombre || '';
            item.ubicacion = prod.ubicacion || 'Sin asignar';
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
            item.descuento = 0;
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
            if (this.items.length > 0 && !confirm('¿Desea vaciar el carrito y reiniciar la venta actual?')) {
                return;
            }
            this.items = [];
            this.formData.cliente_id = '';
            this.formData.descuento = 0;
            this.formData.monto_recibido = '';
            this.formData.referencia_pago = '';
            this.recetaInfo = { medico_nombre: '', medico_cmp: '', paciente_nombre: '', observaciones: '' };
            this.errorMsg = '';
            if (window.farmaClearDraft) {
                window.farmaClearDraft('{{ request()->getPathInfo() }}');
            }
        },

        abrirInfoProducto(producto) {
            this.productoInfo = producto;
            this.modalInfoProducto = true;
        },

        // Cálculos
        calcularSubtotal(item) {
            const cant = parseInt(item.cantidad) || 0;
            const prec = parseFloat(item.precio_unitario) || 0;
            const desc = parseFloat(item.descuento) || 0;
            return Math.max(0, (cant * prec) - desc).toFixed(2);
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

        getClienteNombre() {
            if (!this.formData.cliente_id) return 'PÚBLICO GENERAL';
            const cl = this.clientes.find(c => c.id == this.formData.cliente_id);
            return cl ? cl.nombre : 'PÚBLICO GENERAL';
        },

        getClienteDocumento() {
            if (!this.formData.cliente_id) return 'Sin Documento';
            const cl = this.clientes.find(c => c.id == this.formData.cliente_id);
            return cl && cl.documento ? cl.documento : 'Sin Documento';
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

        // Registrar Cliente Rápido vía AJAX
        async registrarClienteRapido() {
            if (!this.nuevoCliente.nombre || this.nuevoCliente.nombre.trim().length === 0) {
                this.errorClienteMsg = 'El nombre del cliente es obligatorio.';
                return;
            }

            this.guardandoCliente = true;
            this.errorClienteMsg = '';

            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const res = await fetch('{{ route('clientes.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token
                    },
                    body: JSON.stringify(this.nuevoCliente)
                });

                const data = await res.json();
                if (!res.ok || !data.success) {
                    throw new Error(data.message || 'Error al registrar cliente');
                }

                // Agregar al selector y auto-seleccionar
                this.clientes.unshift(data.cliente);
                this.formData.cliente_id = data.cliente.id;
                this.modalNuevoCliente = false;
                this.nuevoCliente = { nombre: '', documento: '', telefono: '', email: '', direccion: '' };
            } catch (err) {
                this.errorClienteMsg = err.message;
            } finally {
                this.guardandoCliente = false;
            }
        },

        // Enviar venta por AJAX
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
                    referencia_pago: this.formData.referencia_pago || null,
                    descuento: parseFloat(this.formData.descuento) || 0,
                    monto_recibido: this.formData.monto_recibido ? parseFloat(this.formData.monto_recibido) : null,
                    productos: this.items.map(it => ({
                        producto_id: it.producto_id,
                        lote_id: it.lote_id,
                        presentacion_id: it.presentacion_id || null,
                        cantidad: parseInt(it.cantidad) || 1,
                        precio_unitario: parseFloat(it.precio_unitario) || 0,
                        descuento: parseFloat(it.descuento) || 0
                    }))
                };

                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
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

                // Éxito: limpiar borrador, abrir preview de ticket o redirigir
                if (window.farmaClearDraft) {
                    window.farmaClearDraft('{{ request()->getPathInfo() }}');
                }
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
    };
}
</script>

<div x-data="posVentaData()"
     @keydown.window="
         if ($event.key === 'F2') { $event.preventDefault(); document.getElementById('posBuscador')?.focus(); }
         if ($event.key === 'F4' && items.length > 0) { $event.preventDefault(); modalCobro = true; }
         if ($event.key === 'F7' && items.length > 0) { $event.preventDefault(); modalTicketPreview = true; }
         if ($event.key === 'Escape' && !modalCobro && !modalTicketPreview && !modalInfoProducto && !modalNuevoCliente) { limpiarVenta(); }
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

        <div class="flex items-center space-x-2 self-start sm:self-auto">
            <!-- Modo Full Screen (Ocultar Barras) -->
            <button type="button" 
                    @click="$dispatch('toggle-pos-fullscreen')"
                    title="Modo Pantalla Completa / Ocultar Barras"
                    class="px-2.5 py-1.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold transition flex items-center space-x-1.5 shrink-0 shadow-2xs cursor-pointer">
                <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                <span class="hidden sm:inline">Modo Full</span>
            </button>

            <!-- Ticket Preview Shortcut Button -->
            <button type="button" 
                    @click="modalTicketPreview = true"
                    title="Ver Vista Previa del Ticket [F7]"
                    class="px-3 py-1.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/60 dark:hover:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 text-xs font-bold transition flex items-center space-x-1.5 cursor-pointer shadow-2xs">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Ticket (F7)</span>
            </button>

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
                    <span class="text-[10px] px-2 py-0.5 rounded-md bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-mono font-bold hidden sm:inline">F2 = Buscar | F4 = Cobrar | F7 = Ticket | Esc = Limpiar</span>
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
                
                <!-- Panel 1: Datos del Cliente (8 cols) -->
                <div class="lg:col-span-8 p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/40 dark:bg-slate-800/30 space-y-3">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center justify-between border-b border-slate-200/60 dark:border-slate-700/60 pb-1.5">
                        <div class="flex items-center space-x-1.5">
                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <span>1. Identificación del Cliente & Búsqueda</span>
                        </div>
                        <!-- Atajo Agregar Cliente -->
                        <button type="button" 
                                @click="modalNuevoCliente = true"
                                class="px-2 py-0.5 rounded-md bg-emerald-100 dark:bg-emerald-950/60 hover:bg-emerald-200 text-emerald-800 dark:text-emerald-300 text-[10px] font-bold transition flex items-center space-x-1">
                            <span>+ Nuevo Cliente</span>
                        </button>
                    </div>

                    <!-- Cliente (Ancho Completo) -->
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Cliente
                        </label>
                        <div class="flex items-center space-x-1">
                            <select x-model="formData.cliente_id" 
                                    class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white font-medium focus:ring-1 focus:ring-emerald-500">
                                <option value="">Público General (Venta Libre)</option>
                                <template x-for="cl in clientes" :key="cl.id">
                                    <option :value="cl.id" x-text="cl.nombre + (cl.documento ? ' (' + cl.documento + ')' : '')"></option>
                                </template>
                            </select>
                            <button type="button" 
                                    @click="modalNuevoCliente = true"
                                    title="Registrar nuevo cliente rápido"
                                    class="p-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white transition shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            </button>
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
                                            Ubicación: <span class="font-semibold text-slate-700 dark:text-slate-300" x-text="prod.ubicacion || 'Sin estante'"></span> &bull;
                                            Lote FEFO: <span class="font-semibold text-emerald-600" x-text="prod.lotes && prod.lotes[0] ? prod.lotes[0].numero_lote + ' (Stock: ' + prod.lotes[0].stock_actual + ')' : 'Sin stock'"></span>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="font-extrabold text-xs text-slate-900 dark:text-white" x-text="'$' + parseFloat(prod.precio_venta).toFixed(2)"></span>
                                    </div>
                                </div>
                            </template>
                            <div x-show="productosFiltrados().length === 0" class="p-3 text-center text-xs text-slate-400">
                                No se encontraron medicamentos con stock disponible para \"<span x-text="busqueda"></span>\"
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
                            <span class="text-[10px] font-semibold text-slate-500 block">Descuento Global ($):</span>
                            <input type="number" 
                                   step="0.10" 
                                   min="0"
                                   x-model="formData.descuento" 
                                   class="w-24 px-1.5 py-0.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded text-xs font-bold text-slate-900 dark:text-white text-right">
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

                <!-- Tabla de Venta con Scroll Interno tras 5 ítems -->
                <div class="overflow-x-auto overflow-y-auto max-h-[350px] rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800/80 shadow-2xs">
                    <table class="w-full text-left text-xs border-collapse min-w-[900px]">
                        <thead class="sticky top-0 z-10 bg-slate-100 dark:bg-slate-800 shadow-2xs">
                            <tr class="text-[10px] font-bold text-slate-700 dark:text-slate-200 uppercase tracking-wider border-b border-slate-200 dark:border-slate-700">
                                <th class="py-2 px-2 text-center w-8">#</th>
                                <th class="py-2 px-3 min-w-[200px]">Medicamento / Fármaco</th>
                                <th class="py-2 px-3 min-w-[160px]">Presentación</th>
                                <th class="py-2 px-3 min-w-[190px]">Lote (FEFO) & Ubicación</th>
                                <th class="py-2 px-2 text-center w-16">Cant.</th>
                                <th class="py-2 px-2 text-right w-20">P. Venta</th>
                                <th class="py-2 px-2 text-right w-20">Desc. ($)</th>
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

                                    <!-- Lote FEFO & Ubicación -->
                                    <td class="py-2 px-3">
                                        <select x-model="item.lote_id" 
                                                @change="onLoteChange(idx)"
                                                class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg text-xs text-slate-900 dark:text-white font-mono focus:ring-1 focus:ring-emerald-500">
                                            <template x-for="l in item.lotesDisponibles" :key="l.id">
                                                <option :value="l.id" x-text="l.numero_lote + ' (Vence: ' + (l.fecha_vencimiento ? l.fecha_vencimiento.substring(0, 10) : 'N/A') + ' | Stock: ' + l.stock_actual + ')'"></option>
                                            </template>
                                        </select>
                                        <div class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">
                                            📍 Estante: <span class="font-semibold text-slate-700 dark:text-slate-300" x-text="item.ubicacion"></span>
                                        </div>
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

                                    <!-- Descuento Ítem -->
                                    <td class="py-2 px-2 text-right">
                                        <input type="number" 
                                               step="0.10" 
                                               min="0" 
                                               x-model="item.descuento" 
                                               placeholder="0.00"
                                               class="w-full px-2 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg text-xs text-rose-600 font-bold text-right focus:ring-1 focus:ring-emerald-500">
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
                                <td colspan="9" class="py-8 text-center text-slate-400 text-xs">
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
                <div class="flex items-center space-x-2">
                    <button type="button" 
                            @click="modalTicketPreview = true"
                            :disabled="items.length === 0"
                            class="px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 text-xs font-bold transition flex items-center space-x-1.5 cursor-pointer">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        <span>Vista Previa Ticket</span>
                    </button>
                    <button type="button" 
                            @click="modalCobro = true"
                            :disabled="items.length === 0"
                            class="px-5 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 disabled:opacity-40 text-white text-xs font-extrabold shadow-sm transition flex items-center space-x-1.5 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <span>Cobrar Venta (F4) &bull; $<span x-text="calcularTotalGeneral()"></span></span>
                    </button>
                </div>
            </div>
        </div>
    </template>


    <!-- ============================================================== -->
    <!-- MODO 2: VISTA MODERNA (TOUCH / ORGANIZACIÓN ESPACIOSA)          -->
    <!-- ============================================================== -->
    <!-- ============================================================== -->
    <!-- MODO 2: VISTA MODERNA (TOUCH / ORGANIZACIÓN ESPACIOSA)          -->
    <!-- ============================================================== -->
    <template x-if="formLayout === 'modern'">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 animate-fadeIn">
            
            <!-- Columna Izquierda: Cliente + Buscador & Catálogo de Medicamentos (5 cols) -->
            <div class="lg:col-span-5 space-y-3.5">
                
                <!-- Card 1: Identificación del Cliente (Solo en el lado de productos) -->
                <div class="bg-white dark:bg-slate-900 p-3.5 rounded-2xl border border-slate-300 dark:border-slate-800 shadow-xs space-y-2.5">
                    <div class="flex items-center justify-between border-b border-slate-200/80 dark:border-slate-800 pb-1.5">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center space-x-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span>Cliente Registrado</span>
                        </span>
                        <button type="button" 
                                @click="modalNuevoCliente = true"
                                class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 hover:underline flex items-center space-x-1 cursor-pointer">
                            <span>+ Nuevo Cliente</span>
                        </button>
                    </div>

                    <!-- Selector de Cliente -->
                    <div>
                        <div class="flex items-center space-x-1.5">
                            <select x-model="formData.cliente_id" 
                                    class="w-full px-2.5 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white font-medium focus:ring-1 focus:ring-emerald-500">
                                <option value="">Público General (Venta Libre)</option>
                                <template x-for="cl in clientes" :key="cl.id">
                                    <option :value="cl.id" x-text="cl.nombre + (cl.documento ? ' (' + cl.documento + ')' : '')"></option>
                                </template>
                            </select>
                            <button type="button" 
                                    @click="modalNuevoCliente = true"
                                    title="Registrar nuevo cliente rápido"
                                    class="p-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white transition shrink-0 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Buscador y Filtro de Categoría -->
                <div class="bg-white dark:bg-slate-900 p-3.5 rounded-2xl border border-slate-300 dark:border-slate-800 shadow-xs space-y-2.5">
                    <div class="relative">
                        <input type="text" 
                               x-model="busqueda" 
                               placeholder="[F2] Buscar medicamento, principio activo..." 
                               class="w-full pl-8 pr-3 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-emerald-500">
                        <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                    </div>

                    <!-- Pills de Categorías -->
                    <div class="flex items-center space-x-1.5 overflow-x-auto pb-0.5 text-xs custom-scrollbar">
                        <button type="button" 
                                @click="filtroCategoriaId = ''"
                                :class="filtroCategoriaId === '' ? 'bg-emerald-600 text-white font-bold shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200'"
                                class="px-2.5 py-1 rounded-lg shrink-0 text-[11px] transition cursor-pointer">
                            Todos
                        </button>
                        <template x-for="cat in categorias" :key="cat.id">
                            <button type="button" 
                                    @click="filtroCategoriaId = cat.id"
                                    :class="filtroCategoriaId == cat.id ? 'bg-emerald-600 text-white font-bold shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200'"
                                    class="px-2.5 py-1 rounded-lg shrink-0 text-[11px] transition cursor-pointer" 
                                    x-text="cat.nombre">
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Grid de Tarjetas de Medicamentos -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 max-h-[480px] overflow-y-auto pr-1 custom-scrollbar">
                    <template x-for="prod in productosFiltrados()" :key="prod.id">
                        <div @click="agregarAlCarrito(prod)"
                             class="group bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-2.5 shadow-xs hover:border-emerald-500 hover:shadow-md transition cursor-pointer flex flex-col justify-between space-y-1.5 relative">
                            <div>
                                <div class="flex items-start justify-between gap-1">
                                    <h4 class="font-bold text-xs text-slate-900 dark:text-white group-hover:text-emerald-600 transition" x-text="prod.nombre"></h4>
                                    <div class="flex items-center space-x-1 shrink-0">
                                        <span x-show="prod.requiere_receta" class="text-[9px] px-1 py-0.2 rounded font-bold bg-amber-500 text-white">Rx</span>
                                        
                                        <!-- Botón Información / Ubicación Lote -->
                                        <button type="button" 
                                                @click.stop="abrirInfoProducto(prod)"
                                                title="Ver ubicación física y lotes disponibles"
                                                class="p-0.5 rounded-md text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-slate-800 transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        </button>
                                    </div>
                                </div>
                                <p class="text-[10px] text-slate-500 dark:text-slate-400 line-clamp-1" x-text="prod.principio_activo || 'Fórmula general'"></p>
                                
                                <!-- Ubicación Física Badge -->
                                <div class="mt-1 flex items-center space-x-1 text-[10px] text-slate-600 dark:text-slate-400">
                                    <span>📍</span>
                                    <span class="font-medium text-slate-700 dark:text-slate-300" x-text="prod.ubicacion || 'Sin estante'"></span>
                                </div>
                            </div>

                            <div class="pt-1.5 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between">
                                <span class="text-[10px] font-semibold text-slate-500">
                                    Lote FEFO: <span class="text-emerald-600 font-bold" x-text="prod.lotes && prod.lotes[0] ? prod.lotes[0].stock_actual + ' u.' : '0'"></span>
                                </span>
                                <span class="text-xs font-black text-slate-900 dark:text-white" x-text="'$' + parseFloat(prod.precio_venta).toFixed(2)"></span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Columna Derecha: Carrito de Despacho Amplio y Compacto (7 cols) -->
            <div class="lg:col-span-7 space-y-3.5">
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-300 dark:border-slate-800 shadow-md p-4 space-y-3">
                    
                    <!-- Header Carrito -->
                    <div class="flex items-center justify-between pb-2.5 border-b border-slate-200 dark:border-slate-800">
                        <div class="flex items-center space-x-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shadow-xs"></span>
                            <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-800 dark:text-slate-200">
                                Carrito de Despacho (<span x-text="items.length"></span> ítems)
                            </h3>
                        </div>
                        <button type="button" @click="limpiarVenta()" class="text-xs font-bold text-rose-600 hover:underline cursor-pointer">Vaciar Todo</button>
                    </div>

                    <!-- Lista de Ítems en Carrito Compacta y Elegante -->
                    <div class="space-y-2.5 max-h-[460px] overflow-y-auto pr-1 custom-scrollbar">
                        <template x-for="(item, idx) in items" :key="item.uid">
                            <div class="p-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-800/50 space-y-2 shadow-2xs">
                                <!-- Fila 1: Título, Principio Activo, Precio Unitario y Botón Eliminar -->
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0 flex-1">
                                        <div class="font-bold text-xs text-slate-900 dark:text-white flex items-center space-x-1.5">
                                            <span class="truncate" x-text="item.nombre"></span>
                                            <span x-show="item.requiere_receta" class="text-[9px] px-1 py-0.2 rounded font-bold bg-amber-500 text-white shrink-0">Rx</span>
                                        </div>
                                        <div class="text-[10px] text-slate-500 truncate" x-text="item.principio_activo"></div>
                                    </div>
                                    <div class="flex items-center space-x-2 shrink-0">
                                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300" x-text="'$' + parseFloat(item.precio_unitario).toFixed(2) + '/u'"></span>
                                        <button type="button" @click="eliminarItem(idx)" class="text-slate-400 hover:text-rose-600 text-sm font-bold transition p-0.5 cursor-pointer">&times;</button>
                                    </div>
                                </div>

                                <!-- Fila 2: Selectores de Presentación y Lote FEFO -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                                    <!-- Presentación -->
                                    <div>
                                        <label class="block text-[9px] font-bold text-slate-500 dark:text-slate-400 mb-0.5">Presentación</label>
                                        <select x-model="item.presentacion_id" @change="onPresentacionChange(idx)" class="w-full px-2 py-1 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs font-medium">
                                            <template x-for="pres in item.presentacionesDisponibles" :key="pres.id">
                                                <option :value="pres.id" x-text="pres.nombre + ' (x' + pres.unidades + ')'"></option>
                                            </template>
                                        </select>
                                    </div>

                                    <!-- Lote FEFO -->
                                    <div>
                                        <label class="block text-[9px] font-bold text-slate-500 dark:text-slate-400 mb-0.5">Lote (FEFO: Stock)</label>
                                        <select x-model="item.lote_id" @change="onLoteChange(idx)" class="w-full px-2 py-1 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs font-mono font-medium">
                                            <template x-for="l in item.lotesDisponibles" :key="l.id">
                                                <option :value="l.id" x-text="l.numero_lote + ' (' + l.stock_actual + 'u)'"></option>
                                            </template>
                                        </select>
                                    </div>
                                </div>

                                <!-- Fila 3: Stepper de Cantidad, Descuento y Subtotal -->
                                <div class="flex items-center justify-between pt-1.5 border-t border-slate-200/80 dark:border-slate-700/80">
                                    <!-- Selector Cantidad -->
                                    <div class="flex items-center space-x-1">
                                        <button type="button" @click="if(item.cantidad > 1) item.cantidad--" class="w-6 h-6 rounded bg-slate-200 dark:bg-slate-700 font-black text-xs hover:bg-slate-300 transition">-</button>
                                        <input type="number" 
                                               min="1" 
                                               x-model.number="item.cantidad" 
                                               class="w-16 py-0.5 text-center font-black text-xs bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded text-slate-900 dark:text-white">
                                        <button type="button" @click="item.cantidad++" class="w-6 h-6 rounded bg-slate-200 dark:bg-slate-700 font-black text-xs hover:bg-slate-300 transition">+</button>
                                    </div>

                                    <!-- Descuento Ítem -->
                                    <div class="flex items-center space-x-1">
                                        <span class="text-[10px] text-slate-500 font-bold">Desc $:</span>
                                        <input type="number" 
                                               step="0.10" 
                                               min="0" 
                                               x-model="item.descuento" 
                                               placeholder="0.00"
                                               class="w-14 px-1 py-0.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded text-xs font-bold text-rose-600 text-right">
                                    </div>

                                    <!-- Subtotal por Fila -->
                                    <div class="text-right">
                                        <span class="font-black text-xs text-slate-900 dark:text-white" x-text="'$' + calcularSubtotal(item)"></span>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <div x-show="items.length === 0" class="py-12 text-center text-slate-400 text-xs">
                            🛒 El carrito está vacío. Seleccione medicamentos del catálogo para comenzar.
                        </div>
                    </div>

                    <!-- Totales y Botones de Cobro / Ticket -->
                    <div class="pt-3 border-t border-slate-200 dark:border-slate-800 space-y-2">
                        <div class="flex justify-between text-xs text-slate-600 dark:text-slate-300">
                            <span>Subtotal Bruto:</span>
                            <span class="font-bold" x-text="'$' + calcularSubtotalGeneral()"></span>
                        </div>
                        <div class="flex justify-between items-center text-xs text-slate-600 dark:text-slate-300">
                            <span>Descuento Global:</span>
                            <div class="flex items-center space-x-1">
                                <span class="text-rose-600 font-bold">-$</span>
                                <input type="number" 
                                       step="0.10" 
                                       min="0" 
                                       x-model="formData.descuento" 
                                       class="w-20 px-1.5 py-0.5 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs font-bold text-rose-600 text-right">
                            </div>
                        </div>
                        <div class="flex justify-between text-base font-black text-slate-900 dark:text-white pt-2 border-t border-slate-200 dark:border-slate-800">
                            <span>Total a Pagar:</span>
                            <span class="text-2xl text-emerald-600 dark:text-emerald-400" x-text="'$' + calcularTotalGeneral()"></span>
                        </div>

                        <div class="grid grid-cols-2 gap-2 pt-1.5">
                            <button type="button" 
                                    @click="modalTicketPreview = true"
                                    class="py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold transition flex items-center justify-center space-x-1.5 cursor-pointer">
                                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                <span>Vista Previa Ticket (F7)</span>
                            </button>
                            <button type="button" 
                                    @click="modalCobro = true"
                                    :disabled="items.length === 0"
                                    class="py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 disabled:opacity-40 text-white text-xs font-extrabold shadow-sm transition flex items-center justify-center space-x-1.5 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                <span>Cobrar (F4)</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>


    <!-- ============================================================== -->
    <!-- MODAL DE COBRO INTEGRADO CON VISTA PREVIA DE TICKET A LA PAR   -->
    <!-- ============================================================== -->
    <div x-show="modalCobro" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-950/70 backdrop-blur-xs overflow-y-auto" 
         aria-labelledby="modal-cobro-title" 
         role="dialog" 
         aria-modal="true"
         @keydown.escape.window="modalCobro = false"
         @click.self="modalCobro = false">
        <div @click.stop
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="bg-white dark:bg-slate-900 rounded-2xl text-left shadow-2xl transform transition-all w-full max-w-4xl border border-slate-300 dark:border-slate-800 my-auto flex flex-col max-h-[92vh]">
            
            <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <span class="w-3 h-3 rounded-full bg-emerald-500 shadow-xs"></span>
                        <h3 class="text-sm font-black uppercase text-slate-800 dark:text-slate-200">
                            Cobro & Emisión de Comprobante
                        </h3>
                    </div>
                    <button type="button" @click="modalCobro = false" class="text-slate-400 hover:text-slate-600 text-lg font-bold">&times;</button>
                </div>

                <!-- Modal Body: 2 Columnas (Izquierda: Formulario de Pago / Derecha: Vista Previa Ticket en Vivo) -->
                <div class="p-6 grid grid-cols-1 lg:grid-cols-12 gap-6 overflow-y-auto flex-1">
                    
                    <!-- Columna Izquierda: Opciones de Cobro & Calculadora (7 cols) -->
                    <div class="lg:col-span-7 space-y-4">
                        <!-- Total Gigante en Modal -->
                        <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 text-center">
                            <span class="text-xs font-semibold text-slate-500 block">Total a Cobrar</span>
                            <span class="text-3xl font-black text-emerald-600 dark:text-emerald-400">
                                $<span x-text="calcularTotalGeneral()"></span>
                            </span>
                        </div>

                        <!-- Método de Pago -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                                Método de Pago
                            </label>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                <button type="button" 
                                        @click="formData.metodo_pago = 'efectivo'"
                                        :class="formData.metodo_pago === 'efectivo' ? 'bg-emerald-600 text-white font-bold' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700'"
                                        class="py-2 rounded-xl text-xs transition flex items-center justify-center space-x-1">
                                    <span>💵</span> <span>Efectivo</span>
                                </button>
                                <button type="button" 
                                        @click="formData.metodo_pago = 'tarjeta'"
                                        :class="formData.metodo_pago === 'tarjeta' ? 'bg-emerald-600 text-white font-bold' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700'"
                                        class="py-2 rounded-xl text-xs transition flex items-center justify-center space-x-1">
                                    <span>💳</span> <span>Tarjeta</span>
                                </button>
                                <button type="button" 
                                        @click="formData.metodo_pago = 'transferencia'"
                                        :class="formData.metodo_pago === 'transferencia' ? 'bg-emerald-600 text-white font-bold' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700'"
                                        class="py-2 rounded-xl text-xs transition flex items-center justify-center space-x-1">
                                    <span>📱</span> <span>Yape/Plin</span>
                                </button>
                                <button type="button" 
                                        @click="formData.metodo_pago = 'mixto'"
                                        :class="formData.metodo_pago === 'mixto' ? 'bg-emerald-600 text-white font-bold' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700'"
                                        class="py-2 rounded-xl text-xs transition flex items-center justify-center space-x-1">
                                    <span>🔄</span> <span>Mixto</span>
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

                        <!-- Referencia de Pago (Para Tarjeta / Transferencia) -->
                        <div x-show="formData.metodo_pago !== 'efectivo'">
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                N° de Operación / Referencia
                            </label>
                            <input type="text" 
                                   x-model="formData.referencia_pago"
                                   placeholder="Ej: OP-98342 o Código de Aprobación POS..."
                                   class="w-full px-3 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white">
                        </div>

                        <!-- Sección Opcional: Datos de Receta Médica (Se activa si hay ítems con Rx) -->
                        <div x-show="tieneProductosRx()" class="p-3 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-300 dark:border-amber-800 space-y-2">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-1.5 text-xs font-bold text-amber-800 dark:text-amber-300">
                                    <span class="px-1.5 py-0.5 rounded bg-amber-500 text-white text-[9px] font-black">Rx</span>
                                    <span>Datos de Receta Médica</span>
                                </div>
                                <span class="text-[10px] text-amber-600 dark:text-amber-400 font-semibold">(Opcional)</span>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-[10px] font-semibold text-slate-700 dark:text-slate-300 mb-0.5">Médico Tratante</label>
                                    <input type="text" 
                                           x-model="recetaInfo.medico_nombre" 
                                           placeholder="Dr. Nombre y Apellido" 
                                           class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-semibold text-slate-700 dark:text-slate-300 mb-0.5">N° Colegiatura / Receta</label>
                                    <input type="text" 
                                           x-model="recetaInfo.medico_cmp" 
                                           placeholder="CMP / N° Receta" 
                                           class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Columna Derecha: Vista Previa del Ticket en Vivo (5 cols) -->
                    <div class="lg:col-span-5 bg-slate-100 dark:bg-slate-950 p-4 rounded-xl border border-slate-200 dark:border-slate-800 space-y-3">
                        <div class="flex items-center justify-between text-[11px] font-bold text-slate-500 uppercase pb-1 border-b border-slate-200 dark:border-slate-800">
                            <span>🧾 Vista Previa de Ticket</span>
                            <span class="text-[9px] bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 px-1.5 py-0.2 rounded font-mono">80mm ESC/POS</span>
                        </div>

                        <!-- Ticket Papel Térmico Simulado -->
                        <div class="bg-white text-slate-900 font-mono text-[10px] p-3 rounded-lg shadow-sm border border-slate-300 space-y-2 max-h-[380px] overflow-y-auto leading-tight">
                            <div class="text-center">
                                <div class="font-extrabold text-xs uppercase">{{ config('app.name', 'FarmaBien') }}</div>
                                <div>RUC: {{ env('EMPRESA_RUC', '20123456789') }}</div>
                                <div class="text-[9px] text-slate-600">{{ env('EMPRESA_DIRECCION', 'Av. Principal 123, Managua') }}</div>
                                <div class="text-[9px] text-slate-600">Tel: {{ env('EMPRESA_TELEFONO', '2244-5566') }}</div>
                            </div>

                            <div class="border-t border-dashed border-slate-400 my-1"></div>

                            <div>
                                <div>FECHA: {{ now()->format('d/m/Y H:i') }}</div>
                                <div>CAJERO: {{ auth()->user()->name ?? 'Cajero 1' }}</div>
                                <div>CLIENTE: <span class="font-bold" x-text="getClienteNombre()"></span></div>
                                <div>DOC: <span x-text="getClienteDocumento()"></span></div>
                                <div>PAGO: <span class="uppercase font-bold" x-text="formData.metodo_pago"></span></div>
                                <div x-show="recetaInfo.medico_nombre" class="text-amber-800 font-bold text-[9px] pt-0.5">
                                    MÉDICO: <span x-text="recetaInfo.medico_nombre"></span> <span x-show="recetaInfo.medico_cmp" x-text="'(CMP: ' + recetaInfo.medico_cmp + ')'"></span>
                                </div>
                            </div>

                            <div class="border-t border-dashed border-slate-400 my-1"></div>

                            <table class="w-full text-[9px] text-left">
                                <thead>
                                    <tr class="border-b border-slate-300 font-bold">
                                        <th>CANT/ITEM</th>
                                        <th class="text-right">PRECIO</th>
                                        <th class="text-right">TOTAL</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="item in items" :key="item.uid">
                                        <tr class="border-b border-slate-100">
                                            <td class="py-1">
                                                <div class="font-bold" x-text="item.nombre"></div>
                                                <div class="text-[8px] text-slate-500" x-text="(item.presentacion_id ? item.presentacionesDisponibles.find(p => p.id == item.presentacion_id)?.nombre : 'Unidad') + ' x' + item.cantidad"></div>
                                            </td>
                                            <td class="py-1 text-right" x-text="'$' + parseFloat(item.precio_unitario).toFixed(2)"></td>
                                            <td class="py-1 text-right font-bold" x-text="'$' + calcularSubtotal(item)"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>

                            <div class="border-t border-dashed border-slate-400 my-1"></div>

                            <div class="space-y-0.5 text-right">
                                <div class="flex justify-between">
                                    <span>SUBTOTAL:</span>
                                    <span class="font-bold" x-text="'$' + calcularSubtotalGeneral()"></span>
                                </div>
                                <div class="flex justify-between" x-show="formData.descuento > 0">
                                    <span>DESCUENTO:</span>
                                    <span class="font-bold text-rose-600" x-text="'-$' + parseFloat(formData.descuento).toFixed(2)"></span>
                                </div>
                                <div class="flex justify-between font-extrabold text-xs border-t border-slate-300 pt-1">
                                    <span>TOTAL:</span>
                                    <span class="text-emerald-700" x-text="'$' + calcularTotalGeneral()"></span>
                                </div>
                                <div class="flex justify-between text-[9px]" x-show="formData.metodo_pago === 'efectivo' && formData.monto_recibido > 0">
                                    <span>RECIBIDO:</span>
                                    <span x-text="'$' + parseFloat(formData.monto_recibido).toFixed(2)"></span>
                                </div>
                                <div class="flex justify-between text-[9px]" x-show="formData.metodo_pago === 'efectivo' && formData.monto_recibido > 0">
                                    <span>CAMBIO:</span>
                                    <span class="font-bold" x-text="'$' + calcularVuelto()"></span>
                                </div>
                            </div>

                            <div class="border-t border-dashed border-slate-400 my-1"></div>
                            <div class="text-center text-[8px] text-slate-500">
                                ¡Gracias por su compra en FarmaBien!
                            </div>
                        </div>
                    </div>
                </div><!-- /.grid (modal body) -->

                <!-- Modal Footer -->
                <div class="bg-slate-50 dark:bg-slate-800/50 px-6 py-3 border-t border-slate-200 dark:border-slate-800 flex justify-end space-x-2 flex-shrink-0 rounded-b-2xl">
                    <button type="button" 
                            @click="modalCobro = false" 
                            class="px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer">
                        Volver a la Venta
                    </button>
                    <button type="button" 
                            @click="procesarVentaFinal()"
                            :disabled="procesandoVenta || items.length === 0"
                            class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white rounded-xl text-xs font-bold shadow-xs transition flex items-center space-x-1.5 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span x-text="procesandoVenta ? 'Procesando...' : 'Confirmar & Emitir Ticket'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>


    <!-- ============================================================== -->
    <!-- MODAL 2: VISTA PREVIA DEDICADA DE TICKET TÉRMICO (MODAL F7)   -->
    <!-- ============================================================== -->
    <div x-show="modalTicketPreview" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-950/70 backdrop-blur-xs overflow-y-auto" 
         @keydown.escape.window="modalTicketPreview = false"
         @click.self="modalTicketPreview = false">
        <div @click.stop
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="bg-white dark:bg-slate-900 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all w-full max-w-md border border-slate-300 dark:border-slate-800 my-auto">
            
            <div class="px-5 py-3 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-indigo-500 shadow-xs"></span>
                    <h3 class="text-xs font-bold uppercase text-slate-800 dark:text-slate-200">Vista Previa de Impresión Térmica</h3>
                </div>
                <button type="button" @click="modalTicketPreview = false" class="text-slate-400 hover:text-slate-600 text-lg font-bold">&times;</button>
            </div>

            <div class="p-5 bg-slate-100 dark:bg-slate-950 flex justify-center">
                <div class="bg-white text-slate-900 font-mono text-xs p-4 rounded-xl shadow-md border border-slate-300 space-y-2.5 w-full max-w-xs">
                    <div class="text-center">
                        <div class="font-extrabold text-sm uppercase">{{ config('app.name', 'FarmaBien') }}</div>
                        <div class="text-[10px]">RUC: {{ env('EMPRESA_RUC', '20123456789') }}</div>
                        <div class="text-[9px] text-slate-600">{{ env('EMPRESA_DIRECCION', 'Av. Principal 123') }}</div>
                        <div class="text-[9px] text-slate-600">Tel: {{ env('EMPRESA_TELEFONO', '2244-5566') }}</div>
                    </div>

                    <div class="border-t border-dashed border-slate-400 my-1"></div>

                    <div class="text-[10px] space-y-0.5">
                        <div>FECHA: {{ now()->format('d/m/Y H:i') }}</div>
                        <div>CAJERO: {{ auth()->user()->name ?? 'Cajero 1' }}</div>
                        <div>CLIENTE: <strong x-text="getClienteNombre()"></strong></div>
                        <div>DOC: <span x-text="getClienteDocumento()"></span></div>
                        <div>PAGO: <span class="uppercase font-bold" x-text="formData.metodo_pago"></span></div>
                        <div x-show="recetaInfo.medico_nombre" class="text-amber-800 font-bold text-[9px] pt-0.5">
                            MÉDICO: <span x-text="recetaInfo.medico_nombre"></span> <span x-show="recetaInfo.medico_cmp" x-text="'(CMP: ' + recetaInfo.medico_cmp + ')'"></span>
                        </div>
                    </div>

                    <div class="border-t border-dashed border-slate-400 my-1"></div>

                    <table class="w-full text-[10px] text-left">
                        <thead>
                            <tr class="border-b border-slate-300 font-bold">
                                <th>CANT/ITEM</th>
                                <th class="text-right">TOTAL</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="item in items" :key="item.uid">
                                <tr class="border-b border-slate-100">
                                    <td class="py-1">
                                        <div class="font-bold" x-text="item.nombre"></div>
                                        <div class="text-[9px] text-slate-500" x-text="(item.presentacion_id ? item.presentacionesDisponibles.find(p => p.id == item.presentacion_id)?.nombre : 'Unidad') + ' x' + item.cantidad + ' ($' + parseFloat(item.precio_unitario).toFixed(2) + ')'"></div>
                                    </td>
                                    <td class="py-1 text-right font-bold" x-text="'$' + calcularSubtotal(item)"></td>
                                </tr>
                            </template>
                            <tr x-show="items.length === 0">
                                <td colspan="2" class="py-3 text-center text-slate-400 text-xs">
                                    Sin medicamentos en el carrito
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="border-t border-dashed border-slate-400 my-1"></div>

                    <div class="space-y-0.5 text-right text-[11px]">
                        <div class="flex justify-between">
                            <span>SUBTOTAL:</span>
                            <span class="font-bold" x-text="'$' + calcularSubtotalGeneral()"></span>
                        </div>
                        <div class="flex justify-between text-rose-600" x-show="formData.descuento > 0">
                            <span>DESCUENTO:</span>
                            <span class="font-bold" x-text="'-$' + parseFloat(formData.descuento).toFixed(2)"></span>
                        </div>
                        <div class="flex justify-between font-black text-sm border-t border-slate-300 pt-1">
                            <span>TOTAL:</span>
                            <span class="text-emerald-700" x-text="'$' + calcularTotalGeneral()"></span>
                        </div>
                    </div>

                    <div class="border-t border-dashed border-slate-400 my-1"></div>
                    <div class="text-center text-[9px] text-slate-500">
                        ¡Gracias por su preferencia!
                    </div>
                </div>
            </div>

            <div class="bg-slate-50 dark:bg-slate-800/50 px-5 py-3 border-t border-slate-200 dark:border-slate-800 flex justify-end space-x-2">
                <button type="button" @click="modalTicketPreview = false" class="px-4 py-1.5 border border-slate-300 dark:border-slate-700 rounded-xl text-xs font-semibold">
                    Cerrar
                </button>
                <button type="button" @click="modalTicketPreview = false; modalCobro = true;" class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold">
                    Proceder al Cobro &rarr;
                </button>
            </div>
        </div>
    </div>


    <!-- ============================================================== -->
    <!-- MODAL 3: INFORMACIÓN DE PRODUCTO & DESGLOSE DE LOTES / UBICACIÓN -->
    <!-- ============================================================== -->
    <div x-show="modalInfoProducto" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-950/70 backdrop-blur-xs overflow-y-auto" 
         @keydown.escape.window="modalInfoProducto = false"
         @click.self="modalInfoProducto = false">
        <div @click.stop
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="bg-white dark:bg-slate-900 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all w-full max-w-lg border border-slate-300 dark:border-slate-800 my-auto">
            
            <template x-if="productoInfo">
                <div>
                    <!-- Header -->
                    <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50 dark:bg-slate-800/60">
                        <div class="flex items-center space-x-2.5">
                            <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white" x-text="productoInfo.nombre"></h3>
                                <p class="text-[11px] text-slate-500" x-text="productoInfo.principio_activo || 'Sin principio activo'"></p>
                            </div>
                        </div>
                        <button type="button" @click="modalInfoProducto = false" class="text-slate-400 hover:text-slate-600 text-lg font-bold">&times;</button>
                    </div>

                    <div class="p-6 space-y-4">
                        <!-- Ubicación Física Destacada -->
                        <div class="p-3.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <span class="text-lg">📍</span>
                                <div>
                                    <span class="text-[10px] font-bold uppercase text-emerald-800 dark:text-emerald-300 block">Ubicación Física en Farmacia:</span>
                                    <span class="text-xs font-black text-emerald-900 dark:text-emerald-200" x-text="productoInfo.ubicacion || 'Sin estantería asignada'"></span>
                                </div>
                            </div>
                            <span class="text-[10px] px-2 py-0.5 rounded-md bg-white dark:bg-slate-800 font-semibold border border-emerald-200 dark:border-emerald-700 text-emerald-800 dark:text-emerald-300">
                                Laboratorio: <span x-text="productoInfo.laboratorio?.nombre || 'General'"></span>
                            </span>
                        </div>

                        <!-- Tabla de Lotes Disponibles -->
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wide">
                                    Lotes Disponibles (Criterio FEFO)
                                </h4>
                                <span class="text-[10px] text-slate-500" x-text="(productoInfo.lotes?.length || 0) + ' lote(s)'"></span>
                            </div>

                            <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
                                <table class="w-full text-left text-xs border-collapse">
                                    <thead class="bg-slate-50 dark:bg-slate-800 text-[10px] font-bold text-slate-600 dark:text-slate-300 uppercase">
                                        <tr>
                                            <th class="py-2 px-3">N° Lote</th>
                                            <th class="py-2 px-3">Vencimiento</th>
                                            <th class="py-2 px-3 text-center">Stock</th>
                                            <th class="py-2 px-3 text-center">Estado</th>
                                            <th class="py-2 px-3 text-right">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium">
                                        <template x-for="(lote, lIdx) in (productoInfo.lotes || [])" :key="lote.id">
                                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40">
                                                <td class="py-2.5 px-3 font-mono font-bold text-slate-900 dark:text-white" x-text="lote.numero_lote"></td>
                                                <td class="py-2.5 px-3 text-slate-600 dark:text-slate-300" x-text="lote.fecha_vencimiento ? lote.fecha_vencimiento.substring(0, 10) : 'N/A'"></td>
                                                <td class="py-2.5 px-3 text-center font-bold text-emerald-600" x-text="lote.stock_actual + ' u.'"></td>
                                                <td class="py-2.5 px-3 text-center">
                                                    <span x-show="lIdx === 0" class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                                                        FEFO (Sale 1°)
                                                    </span>
                                                    <span x-show="lIdx > 0" class="px-1.5 py-0.5 rounded text-[9px] font-semibold bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                                        Siguiente
                                                    </span>
                                                </td>
                                                <td class="py-2.5 px-3 text-right">
                                                    <button type="button" 
                                                            @click="agregarAlCarrito(productoInfo, null, lote.id); modalInfoProducto = false;"
                                                            class="px-2 py-1 rounded bg-emerald-600 hover:bg-emerald-700 text-white text-[10px] font-bold transition">
                                                        Despachar
                                                    </button>
                                                </td>
                                            </tr>
                                        </template>
                                        <tr x-show="!productoInfo.lotes || productoInfo.lotes.length === 0">
                                            <td colspan="5" class="py-4 text-center text-slate-400 text-xs">
                                                No hay lotes con stock para este medicamento.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="bg-slate-50 dark:bg-slate-800/50 px-6 py-3 border-t border-slate-200 dark:border-slate-800 flex justify-end">
                        <button type="button" @click="modalInfoProducto = false" class="px-4 py-1.5 bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 rounded-xl text-xs font-semibold">
                            Cerrar
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>


    <!-- ============================================================== -->
    <!-- MODAL 4: REGISTRAR NUEVO CLIENTE RÁPIDO                        -->
    <!-- ============================================================== -->
    <div x-show="modalNuevoCliente" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-950/70 backdrop-blur-xs overflow-y-auto" 
         @keydown.escape.window="modalNuevoCliente = false"
         @click.self="modalNuevoCliente = false">
        <div @click.stop
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="bg-white dark:bg-slate-900 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all w-full max-w-md border border-slate-300 dark:border-slate-800 my-auto">
            
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                    </div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Registrar Nuevo Cliente Rápido</h3>
                </div>
                <button type="button" @click="modalNuevoCliente = false" class="text-slate-400 hover:text-slate-600 text-lg font-bold">&times;</button>
            </div>

            <div class="p-6 space-y-3">
                <div x-show="errorClienteMsg" class="p-2.5 rounded-lg bg-rose-50 text-rose-700 text-xs" x-text="errorClienteMsg"></div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Nombre Completo / Razón Social <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" 
                           x-model="nuevoCliente.nombre" 
                           placeholder="Ej: Juan Pérez o Distribuidora S.A."
                           required
                           class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white">
                </div>

                <div class="grid grid-cols-2 gap-2.5">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Cédula / RUC
                        </label>
                        <input type="text" 
                               x-model="nuevoCliente.documento" 
                               placeholder="001-000000-0000A"
                               class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Teléfono
                        </label>
                        <input type="text" 
                               x-model="nuevoCliente.telefono" 
                               placeholder="8888-9999"
                               class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Dirección
                    </label>
                    <input type="text" 
                           x-model="nuevoCliente.direccion" 
                           placeholder="Dirección o barrio..."
                           class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white">
                </div>
            </div>

            <div class="bg-slate-50 dark:bg-slate-800/50 px-6 py-3 border-t border-slate-200 dark:border-slate-800 flex justify-end space-x-2">
                <button type="button" @click="modalNuevoCliente = false" class="px-4 py-1.5 border border-slate-300 dark:border-slate-700 rounded-xl text-xs font-semibold">
                    Cancelar
                </button>
                <button type="button" 
                        @click="registrarClienteRapido()"
                        :disabled="guardandoCliente"
                        class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition flex items-center space-x-1">
                    <span x-text="guardandoCliente ? 'Guardando...' : 'Guardar y Seleccionar'"></span>
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
