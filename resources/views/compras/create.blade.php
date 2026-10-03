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

    $oldProductos = old('productos');
    $initialItems = [];
    if (is_array($oldProductos) && count($oldProductos) > 0) {
        foreach ($oldProductos as $idx => $op) {
            $prod = $productos->firstWhere('id', $op['producto_id'] ?? null);
            $presList = [];
            if ($prod) {
                $presList = $prod->presentacionesActivas->map(function($pres) {
                    return [
                        'id' => $pres->id,
                        'nombre' => $pres->nombre,
                        'unidades' => (int)$pres->unidades_por_presentacion,
                        'precio_compra' => (float)$pres->precio_compra
                    ];
                })->values()->toArray();
            }
            $selectedPres = $prod && !empty($op['presentacion_id']) ? $prod->presentacionesActivas->firstWhere('id', $op['presentacion_id']) : null;

            $initialItems[] = [
                'uid' => time() + $idx,
                'producto_id' => $op['producto_id'] ?? '',
                'presentacion_id' => $op['presentacion_id'] ?? '',
                'detalle_orden_compra_id' => $op['detalle_orden_compra_id'] ?? '',
                'factor' => $selectedPres ? (int)$selectedPres->unidades_por_presentacion : 1,
                'tipo_presentacion' => $selectedPres ? $selectedPres->nombre : 'Unidad Base',
                'cantidad' => $op['cantidad'] ?? 1,
                'precio_unitario' => $op['precio_unitario'] ?? '',
                'numero_lote' => $op['numero_lote'] ?? '',
                'fecha_vencimiento' => $op['fecha_vencimiento'] ?? '',
                'pedido' => isset($op['pedido']) ? (int)$op['pedido'] : null,
                'recibido' => isset($op['recibido']) ? (int)$op['recibido'] : null,
                'pendiente' => isset($op['pendiente']) ? (int)$op['pendiente'] : null,
                'presentacionesDisponibles' => $presList
            ];
        }
    } elseif (!empty($preloadedItems) && is_array($preloadedItems)) {
        foreach ($preloadedItems as $idx => $pi) {
            $prod = $productos->firstWhere('id', $pi['producto_id'] ?? null);
            $presList = [];
            if ($prod) {
                $presList = $prod->presentacionesActivas->map(function($pres) {
                    return [
                        'id' => $pres->id,
                        'nombre' => $pres->nombre,
                        'unidades' => (int)$pres->unidades_por_presentacion,
                        'precio_compra' => (float)$pres->precio_compra
                    ];
                })->values()->toArray();
            }
            $selectedPres = $prod && !empty($pi['presentacion_id']) ? $prod->presentacionesActivas->firstWhere('id', $pi['presentacion_id']) : null;

            $initialItems[] = [
                'uid' => time() + $idx,
                'producto_id' => $pi['producto_id'] ?? '',
                'presentacion_id' => $pi['presentacion_id'] ?? '',
                'detalle_orden_compra_id' => $pi['detalle_orden_compra_id'] ?? '',
                'factor' => $selectedPres ? (int)$selectedPres->unidades_por_presentacion : 1,
                'tipo_presentacion' => $selectedPres ? $selectedPres->nombre : 'Unidad Base',
                'cantidad' => $pi['cantidad'] ?? 1,
                'precio_unitario' => $pi['precio_unitario'] ?? ($prod ? (float)$prod->precio_compra : ''),
                'numero_lote' => $pi['numero_lote'] ?? '',
                'fecha_vencimiento' => $pi['fecha_vencimiento'] ?? '',
                'pedido' => isset($pi['pedido']) ? (int)$pi['pedido'] : null,
                'recibido' => isset($pi['recibido']) ? (int)$pi['recibido'] : null,
                'pendiente' => isset($pi['pendiente']) ? (int)$pi['pendiente'] : null,
                'presentacionesDisponibles' => $presList
            ];
        }
    }

    if (empty($initialItems)) {
        $initialItems = [
            [
                'uid' => time(),
                'producto_id' => '',
                'presentacion_id' => '',
                'detalle_orden_compra_id' => '',
                'factor' => 1,
                'tipo_presentacion' => 'Unidad Base',
                'cantidad' => 1,
                'precio_unitario' => '',
                'numero_lote' => '',
                'fecha_vencimiento' => '',
                'pedido' => null,
                'recibido' => null,
                'pendiente' => null,
                'presentacionesDisponibles' => []
            ]
        ];
    }
    $hasPreloadedData = !empty(old('proveedor_id')) || !empty($preloadedProveedorId) || !empty($preloadedOrdenCompraId) || !empty($preloadedItems);
@endphp

<div x-data="{
    formLayout: localStorage.getItem('farmaFormViewMode') || 'modern',
    setLayout(mode) {
        this.formLayout = mode;
        localStorage.setItem('farmaFormViewMode', mode);
        window.dispatchEvent(new CustomEvent('farma:layout-changed', { detail: { mode } }));
    },
    catalogo: @js($productosCatalogo),
    proveedores: @js($proveedores ?? []),
    historialMap: @js($historialMap ?? []),
    proveedorQuery: '',
    proveedorDropdownAbierto: false,
    proveedorSeleccionado() {
        if (!this.formData.proveedor_id) return null;
        return this.proveedores.find(p => p.id == this.formData.proveedor_id) || null;
    },
    filtrarProveedores() {
        const q = (this.proveedorQuery || '').trim().toLowerCase();
        if (!q) return this.proveedores.slice(0, 15);
        return this.proveedores.filter(p => {
            return (p.nombre && p.nombre.toLowerCase().includes(q)) ||
                   (p.ruc && p.ruc.toLowerCase().includes(q)) ||
                   (p.telefono && p.telefono.toLowerCase().includes(q));
        }).slice(0, 15);
    },
    seleccionarProveedor(prov) {
        if (!prov) return;
        this.formData.proveedor_id = prov.id;
        this.proveedorQuery = '';
        this.proveedorDropdownAbierto = false;
    },
    deseleccionarProveedor() {
        this.formData.proveedor_id = '';
        this.proveedorQuery = '';
        this.proveedorDropdownAbierto = false;
    },
    getPrecioAnterior(productoId) {
        if (!productoId || !this.historialMap[productoId]) return null;
        const entry = this.historialMap[productoId];
        const provId = this.formData.proveedor_id;
        if (provId && entry.proveedores && entry.proveedores[provId]) {
            return {
                precio: entry.proveedores[provId].precio_unitario_base,
                proveedor: this.proveedorSeleccionado()?.nombre || 'Este proveedor',
                fecha: entry.proveedores[provId].fecha,
                tipo: entry.proveedores[provId].tipo,
                esDelProveedorActual: true
            };
        }
        return {
            precio: entry.ultimo_precio,
            proveedor: entry.ultimo_proveedor,
            fecha: entry.ultima_fecha,
            tipo: 'compra',
            esDelProveedorActual: false
        };
    },
    getDeltaPrecio(productoId, precioUnitario, factor = 1) {
        const anterior = this.getPrecioAnterior(productoId);
        if (!anterior || !precioUnitario || parseFloat(precioUnitario) <= 0) return null;
        const costoBaseActual = parseFloat(precioUnitario) / Math.max(1, parseInt(factor) || 1);
        const costoBaseAnterior = parseFloat(anterior.precio);
        if (costoBaseAnterior <= 0) return null;
        const diff = costoBaseActual - costoBaseAnterior;
        const pct = ((diff / costoBaseAnterior) * 100).toFixed(1);
        return {
            diff,
            pct,
            esAumento: diff > 0.0001,
            esRebaja: diff < -0.0001,
            esIgual: Math.abs(diff) <= 0.0001
        };
    },
    formData: (@js($hasPreloadedData) || !window.farmaGetDraft) ? {
        proveedor_id: @js(old('proveedor_id', $preloadedProveedorId ?? '')),
        orden_compra_id: @js(old('orden_compra_id', $preloadedOrdenCompraId ?? '')),
        numero_comprobante: @js(old('numero_comprobante', '')),
        fecha: @js(old('fecha', date('Y-m-d'))),
        condicion_pago: @js(old('condicion_pago', $preloadedCondicionPago ?? 'contado')),
        dias_credito: @js(old('dias_credito', $preloadedDiasCredito ?? 30)),
        fecha_vencimiento_pago: @js(old('fecha_vencimiento_pago', ''))
    } : window.farmaGetDraft('{{ request()->getPathInfo() }}', {
        proveedor_id: @js(old('proveedor_id', $preloadedProveedorId ?? '')),
        orden_compra_id: @js(old('orden_compra_id', $preloadedOrdenCompraId ?? '')),
        numero_comprobante: @js(old('numero_comprobante', '')),
        fecha: @js(old('fecha', date('Y-m-d'))),
        condicion_pago: @js(old('condicion_pago', $preloadedCondicionPago ?? 'contado')),
        dias_credito: @js(old('dias_credito', $preloadedDiasCredito ?? 30)),
        fecha_vencimiento_pago: @js(old('fecha_vencimiento_pago', ''))
    }),
    setDiasCredito(dias) {
        this.formData.dias_credito = dias;
        this.calcularFechaVencimientoPago();
    },
    calcularFechaVencimientoPago() {
        if (this.formData.condicion_pago !== 'credito') {
            this.formData.fecha_vencimiento_pago = '';
            return;
        }
        const fechaStr = this.formData.fecha || '{{ date('Y-m-d') }}';
        const d = new Date(fechaStr + 'T00:00:00');
        const dias = parseInt(this.formData.dias_credito) || 0;
        d.setDate(d.getDate() + dias);
        const yyyy = d.getFullYear();
        const mm = String(d.getMonth() + 1).padStart(2, '0');
        const dd = String(d.getDate()).padStart(2, '0');
        this.formData.fecha_vencimiento_pago = `${yyyy}-${mm}-${dd}`;
    },
    guardandoCompra: false,
    hasOldItems: @js(!empty(old('productos')) || !empty($preloadedItems)),
    items: @js($initialItems),
    // Estados para recepción de Orden de Compra
    modalOrdenPendiente: false,
    modalOrdenExcedente: false,
    cerrarOrdenCompleta: false,
    motivoFaltante: '',
    decisionFaltanteTomada: false,
    decisionExcedenteTomada: false,
    lineasFaltantes: [],
    lineasExcedentes: [],
    forzarEnvio: false,
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
    async limpiarFormulario() {
        if (this.items.length > 0) {
            const ok = await window.farmaConfirm({
                title: '¿Limpiar formulario?',
                body: 'Se eliminarán todos los productos ingresados y se reiniciarán los campos de la compra.',
                type: 'warning',
                ok: 'Sí, limpiar'
            });
            if (!ok) return;
        }
        this.formData = { proveedor_id: '', numero_comprobante: '', fecha: '{{ date('Y-m-d') }}' };
        this.items = [{
            uid: Date.now(),
            producto_id: '',
            presentacion_id: '',
            detalle_orden_compra_id: '',
            factor: 1,
            tipo_presentacion: 'Unidad Base',
            cantidad: 1,
            precio_unitario: '',
            numero_lote: '',
            fecha_vencimiento: '',
            pedido: null,
            recibido: null,
            pendiente: null,
            presentacionesDisponibles: []
        }];
        if (window.farmaClearDraft) {
            window.farmaClearDraft('{{ request()->getPathInfo() }}');
        }
    },
    persistirBorrador() {
        if (window.farmaSaveDraft) {
            window.farmaSaveDraft('{{ request()->getPathInfo() }}', {
                proveedor_id: this.formData.proveedor_id,
                numero_comprobante: this.formData.numero_comprobante,
                fecha: this.formData.fecha,
                savedItems: this.items
            });
        }
    },
    validarYEnviar(e) {
        if (this.guardandoCompra) {
            e.preventDefault();
            return;
        }
        if (!this.formData.proveedor_id) {
            e.preventDefault();
            if (window.farmaToast) {
                window.farmaToast.error('Por favor seleccione un proveedor registrado antes de procesar la compra.');
            } else {
                alert('Por favor seleccione un proveedor registrado antes de procesar la compra.');
            }
            this.proveedorDropdownAbierto = true;
            return;
        }
        if (!this.formData.fecha) {
            e.preventDefault();
            if (window.farmaToast) {
                window.farmaToast.error('Por favor indique la fecha del documento.');
            } else {
                alert('Por favor indique la fecha del documento.');
            }
            return;
        }
        for (let i = 0; i < this.items.length; i++) {
            const it = this.items[i];
            if (!it.producto_id) {
                e.preventDefault();
                if (window.farmaToast) window.farmaToast.error(`Línea ${i + 1}: Debe seleccionar un medicamento.`);
                else alert(`Línea ${i + 1}: Debe seleccionar un medicamento.`);
                return;
            }
            if (!it.cantidad || it.cantidad < 1) {
                e.preventDefault();
                if (window.farmaToast) window.farmaToast.error(`Línea ${i + 1}: La cantidad debe ser mayor a 0.`);
                else alert(`Línea ${i + 1}: La cantidad debe ser mayor a 0.`);
                return;
            }
            if (!it.precio_unitario || it.precio_unitario <= 0) {
                e.preventDefault();
                if (window.farmaToast) window.farmaToast.error(`Línea ${i + 1}: Debe especificar un precio unitario de compra.`);
                else alert(`Línea ${i + 1}: Debe especificar un precio unitario de compra.`);
                return;
            }
            if (!it.numero_lote || it.numero_lote.trim() === '') {
                e.preventDefault();
                if (window.farmaToast) window.farmaToast.error(`Línea ${i + 1}: Debe ingresar el número de lote.`);
                else alert(`Línea ${i + 1}: Debe ingresar el número de lote.`);
                return;
            }
            if (!it.fecha_vencimiento) {
                e.preventDefault();
                if (window.farmaToast) window.farmaToast.error(`Línea ${i + 1}: Debe indicar la fecha de vencimiento del lote.`);
                else alert(`Línea ${i + 1}: Debe indicar la fecha de vencimiento del lote.`);
                return;
            }
        }

        // Verificación de Orden de Compra si está vinculada
        const ordenId = this.formData.orden_compra_id || '{{ $preloadedOrdenCompraId ?? '' }}';
        if (ordenId && !this.forzarEnvio) {
            // 1. Verificar si hay excedentes
            if (!this.decisionExcedenteTomada) {
                const excedentes = [];
                this.items.forEach(it => {
                    if (it.pendiente !== null && it.pendiente !== undefined) {
                        const cantBase = (parseInt(it.cantidad) || 0) * (parseInt(it.factor) || 1);
                        if (cantBase > it.pendiente) {
                            const prod = this.catalogo.find(p => p.id == it.producto_id);
                            excedentes.push({
                                nombre: prod ? prod.nombre : 'Medicamento',
                                pendiente: it.pendiente,
                                ingresando: cantBase,
                                diferencia: cantBase - it.pendiente
                            });
                        }
                    }
                });

                if (excedentes.length > 0) {
                    e.preventDefault();
                    this.lineasExcedentes = excedentes;
                    this.modalOrdenExcedente = true;
                    return;
                }
            }

            // 2. Verificar si quedan unidades pendientes (recepción parcial)
            if (!this.decisionFaltanteTomada) {
                const faltantes = [];
                this.items.forEach(it => {
                    if (it.pendiente !== null && it.pendiente !== undefined) {
                        const cantBase = (parseInt(it.cantidad) || 0) * (parseInt(it.factor) || 1);
                        if (it.pendiente > cantBase) {
                            const prod = this.catalogo.find(p => p.id == it.producto_id);
                            faltantes.push({
                                nombre: prod ? prod.nombre : 'Medicamento',
                                pedido: it.pedido,
                                recibido_prev: it.recibido,
                                ingresando: cantBase,
                                pendiente_anterior: it.pendiente,
                                nuevo_pendiente: it.pendiente - cantBase
                            });
                        }
                    }
                });

                if (faltantes.length > 0) {
                    e.preventDefault();
                    this.lineasFaltantes = faltantes;
                    this.modalOrdenPendiente = true;
                    return;
                }
            }
        }

        this.guardandoCompra = true;
    },
    confirmarExcedente() {
        this.decisionExcedenteTomada = true;
        this.modalOrdenExcedente = false;
        // Validar si quedan faltantes
        const fakeE = { preventDefault() {} };
        this.validarYEnviar(fakeE);
        if (!this.modalOrdenPendiente && !this.modalOrdenExcedente) {
            this.forzarEnvio = true;
            this.guardandoCompra = true;
            this.$nextTick(() => {
                document.getElementById('formCompra').submit();
            });
        }
    },
    confirmarCierreOrden(cerrarCompleta) {
        this.cerrarOrdenCompleta = cerrarCompleta;
        this.decisionFaltanteTomada = true;
        this.modalOrdenPendiente = false;
        this.forzarEnvio = true;
        this.guardandoCompra = true;
        this.$nextTick(() => {
            document.getElementById('formCompra').submit();
        });
    },
    init() {
        if (!this.hasOldItems && this.items.length === 1 && !this.items[0].producto_id && this.formData && this.formData.savedItems && this.formData.savedItems.length > 0) {
            this.items = this.formData.savedItems;
        }
        this.$watch('formData', () => this.persistirBorrador(), { deep: true });
        this.$watch('items', () => this.persistirBorrador(), { deep: true });
    },
    // Buscador Dinámico de Medicamentos
    busquedaProducto: '',
    busquedaDropdownAbierta: false,
    filtrarCatalogo() {
        const q = (this.busquedaProducto || '').trim().toLowerCase();
        if (!q) return [];
        return this.catalogo.filter(p => {
            return (p.nombre && p.nombre.toLowerCase().includes(q)) ||
                   (p.principio_activo && p.principio_activo.toLowerCase().includes(q)) ||
                   (p.codigo_barra && p.codigo_barra.toLowerCase().includes(q)) ||
                   (p.laboratorio && p.laboratorio.toLowerCase().includes(q));
        }).slice(0, 15);
    },
    seleccionarProductoDesdeBuscador(prod) {
        if (!prod) return;
        
        // Si el único ítem existente está completamente vacío, reemplazarlo
        if (this.items.length === 1 && !this.items[0].producto_id) {
            this.items[0].producto_id = prod.id;
            this.items[0].presentacionesDisponibles = prod.presentaciones || [];
            this.items[0].presentacion_id = '';
            this.items[0].factor = 1;
            this.items[0].tipo_presentacion = 'Unidad Base';
            this.items[0].precio_unitario = prod.precio_compra > 0 ? prod.precio_compra : '';
        } else {
            // Agregar de primero (unshift)
            this.items.unshift({
                uid: Date.now() + Math.random(),
                producto_id: prod.id,
                presentacion_id: '',
                factor: 1,
                tipo_presentacion: 'Unidad Base',
                cantidad: 1,
                precio_unitario: prod.precio_compra > 0 ? prod.precio_compra : '',
                numero_lote: '',
                fecha_vencimiento: '',
                presentacionesDisponibles: prod.presentaciones || []
            });
        }

        this.busquedaProducto = '';
        this.busquedaDropdownAbierta = false;
    },
    procesarEnterBuscador() {
        const res = this.filtrarCatalogo();
        if (res.length > 0) {
            this.seleccionarProductoDesdeBuscador(res[0]);
        }
    },
    agregarItem() {
        // Agregar de primero (unshift)
        this.items.unshift({
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
@keydown.window="
    if ($event.key === 'F3') { $event.preventDefault(); (document.getElementById('compraBuscador') || document.querySelector('select[name*=\'producto_id\']'))?.focus(); }
    if ($event.key === 'Escape' && formLayout === 'compact' && !modalNuevaPres) { limpiarFormulario(); }
"
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
        <div class="flex items-center space-x-2 self-start sm:self-auto flex-wrap">
            <!-- 1. Volver a Compras (Primera posición a la izquierda) -->
            <a href="{{ route('compras.index') }}" 
               class="px-3.5 py-1.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold transition flex items-center space-x-1.5 shrink-0 shadow-2xs">
                <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Compras</span>
            </a>

            <!-- 2. Modo Full Screen (Segunda posición) -->
            <button type="button" 
                    @click="$dispatch('toggle-pos-fullscreen')"
                    title="Modo Pantalla Completa / Ocultar Barras"
                    class="px-3 py-1.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold transition flex items-center space-x-1.5 shrink-0 shadow-2xs cursor-pointer">
                <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                <span class="hidden sm:inline" x-text="posFullscreen ? 'Salir Full' : 'Modo Full'">Modo Full</span>
            </button>

            <!-- 3. Selector de Diseño (Extrema derecha) -->
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
    <form action="{{ route('compras.store') }}" method="POST" id="formCompra" @submit="validarYEnviar($event)" novalidate>
        @csrf
        <input type="hidden" name="orden_compra_id" :value="formData.orden_compra_id || '{{ $preloadedOrdenCompraId ?? '' }}'">
        <input type="hidden" name="cerrar_orden_completa" :value="cerrarOrdenCompleta ? '1' : '0'">
        <input type="hidden" name="motivo_faltante" :value="motivoFaltante">

        @if (!empty($preloadedOrdenCompraId))
        @php
            $totalPendientePreload = collect($preloadedItems)->sum('pendiente');
            $cantLineasPreload = count($preloadedItems);
        @endphp
        <!-- Preloaded Orden Compra Banner -->
        <div class="mb-4 p-3.5 rounded-2xl bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-200 dark:border-indigo-800 text-indigo-900 dark:text-indigo-200 flex items-center justify-between shadow-xs">
            <div class="flex items-center space-x-3">
                <div class="w-9 h-9 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-bold shadow-xs shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                <div>
                    <div class="font-bold text-xs sm:text-sm flex items-center space-x-2 flex-wrap gap-y-1">
                        <span>Recepcionando Orden de Compra #{{ $preloadedNumeroOrden ?? $preloadedOrdenCompraId }}</span>
                        <span class="px-2 py-0.5 text-[10px] uppercase font-bold tracking-wider rounded-full bg-indigo-100 dark:bg-indigo-900/80 text-indigo-800 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-700">Vinculada</span>
                        @if($totalPendientePreload > 0)
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-amber-100 dark:bg-amber-950/80 text-amber-900 dark:text-amber-300 border border-amber-300 dark:border-amber-700">
                                Pendiente por recibir: {{ $totalPendientePreload }} u. en {{ $cantLineasPreload }} línea(s)
                            </span>
                        @endif
                    </div>
                    <p class="text-[11px] text-indigo-800 dark:text-indigo-300 mt-0.5">Se han precargado las líneas con saldo pendiente. Ingrese número de lote y fecha de vencimiento física para dar entrada al inventario.</p>
                </div>
            </div>
            <a href="{{ route('ordenes-compras.show', $preloadedOrdenCompraId) }}" target="_blank" class="text-xs font-bold text-indigo-700 dark:text-indigo-300 hover:underline px-2.5 py-1 rounded-lg hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition shrink-0">
                Ver Orden (PO) &rarr;
            </a>
        </div>
        @endif

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
        <!-- VISTA COMPACTA (ERP / UNIFIED CONTAINER / ALTA DENSIDAD)       -->
        <!-- ============================================================== -->
        <template x-if="formLayout === 'compact'">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-300 dark:border-slate-800 shadow-md p-4 space-y-4 animate-fadeIn">
                
                <!-- Toolbar Superior -->
                <div class="flex items-center justify-between pb-3 border-b border-slate-300 dark:border-slate-800 bg-slate-100/80 dark:bg-slate-800/60 -mx-4 -mt-4 p-3 rounded-t-2xl">
                    <div class="flex items-center space-x-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shadow-xs"></span>
                        <span class="text-xs font-extrabold text-slate-800 dark:text-slate-200 uppercase tracking-wide">FICHA RÁPIDA DE COMPRA & INGRESO DE LOTES</span>
                        <span class="text-[10px] px-2 py-0.5 rounded-md bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-mono font-bold hidden sm:inline">F3 = Enfocar Medicamento | Esc = Limpiar</span>
                    </div>

                    <div class="flex items-center space-x-2">
                        <button type="button" 
                                @click="limpiarFormulario()" 
                                :disabled="guardandoCompra"
                                class="px-3 py-1.5 rounded-lg border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-800 text-xs font-bold transition cursor-pointer">
                            Limpiar (Esc)
                        </button>
                        <button type="submit" 
                                :disabled="guardandoCompra"
                                class="px-4 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed text-white text-xs font-extrabold shadow-sm transition flex items-center space-x-1.5 cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span x-text="guardandoCompra ? 'Guardando...' : 'Guardar Compra'">Guardar Compra</span>
                        </button>
                    </div>
                </div>

                <!-- Grid de Paneles Superiores -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
                    <!-- Panel 1: Datos Fiscales y Proveedor (8 cols) -->
                    <div class="lg:col-span-8 p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/40 dark:bg-slate-800/30 space-y-3">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center space-x-1.5 border-b border-slate-200/60 dark:border-slate-700/60 pb-1.5">
                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            <span>1. Identificación del Proveedor & Comprobante</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-2.5">
                            <!-- Proveedor (Col 4) -->
                            <div class="sm:col-span-4 relative z-30" @click.outside="proveedorDropdownAbierto = false">
                                <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                    Proveedor <span class="text-rose-500">*</span>
                                </label>
                                <input type="hidden" name="proveedor_id" :value="formData.proveedor_id">
                                
                                <template x-if="proveedorSeleccionado()">
                                    <div class="flex items-center justify-between px-2.5 py-1 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-300 dark:border-emerald-700 rounded-lg text-xs">
                                        <div class="flex items-center space-x-1.5 min-w-0">
                                            <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                                            <span class="font-bold text-emerald-900 dark:text-emerald-200 truncate" x-text="proveedorSeleccionado().nombre"></span>
                                            <span class="text-[10px] text-emerald-700 dark:text-emerald-300 font-mono" x-show="proveedorSeleccionado().ruc" x-text="'(' + proveedorSeleccionado().ruc + ')'"></span>
                                        </div>
                                        <button type="button" @click="deseleccionarProveedor()" class="ml-1.5 text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 p-0.5" title="Cambiar proveedor">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                </template>

                                <template x-if="!proveedorSeleccionado()">
                                    <div class="relative">
                                        <input type="text" 
                                               x-model="proveedorQuery" 
                                               @focus="proveedorDropdownAbierto = true"
                                               @input="proveedorDropdownAbierto = true"
                                               @keydown.enter.prevent="if (filtrarProveedores().length > 0) seleccionarProveedor(filtrarProveedores()[0])"
                                               placeholder="Buscar proveedor o RUC..."
                                               class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white font-medium focus:ring-1 focus:ring-emerald-500">
                                        <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none text-slate-400">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                        </div>
                                    </div>
                                </template>

                                <!-- Dropdown predictivo -->
                                <div x-show="proveedorDropdownAbierto && !proveedorSeleccionado()" 
                                     x-transition
                                     class="absolute left-0 right-0 top-full mt-1 z-50 bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 shadow-2xl max-h-52 overflow-y-auto">
                                    <template x-for="prov in filtrarProveedores()" :key="prov.id">
                                        <button type="button" 
                                                @click="seleccionarProveedor(prov)"
                                                class="w-full px-2.5 py-1.5 text-left hover:bg-emerald-50 dark:hover:bg-slate-700/60 flex items-center justify-between border-b border-slate-100 dark:border-slate-700/40 last:border-0 transition-colors">
                                            <div class="min-w-0">
                                                <div class="text-xs font-semibold text-slate-800 dark:text-slate-100 truncate" x-text="prov.nombre"></div>
                                                <div class="text-[10px] text-slate-500 dark:text-slate-400 flex items-center space-x-2">
                                                    <span x-show="prov.ruc" x-text="'RUC: ' + prov.ruc"></span>
                                                    <span x-show="prov.telefono" x-text="'Tel: ' + prov.telefono"></span>
                                                </div>
                                            </div>
                                            <span class="text-[10px] font-semibold text-emerald-600 dark:text-emerald-400 shrink-0 ml-1.5">Elegir →</span>
                                        </button>
                                    </template>
                                    <div x-show="filtrarProveedores().length === 0" class="px-3 py-2 text-center text-xs text-slate-500 dark:text-slate-400">
                                        No se encontró proveedor
                                    </div>
                                </div>
                            </div>

                            <!-- N° Comprobante (Col 3) -->
                            <div class="sm:col-span-3">
                                <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                    N° Factura / Boleta
                                </label>
                                <input type="text" 
                                       name="numero_comprobante" 
                                       x-model="formData.numero_comprobante" 
                                       placeholder="Ej: F001-0004523"
                                       class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500 font-mono">
                            </div>

                            <!-- Fecha Documento (Col 2) -->
                            <div class="sm:col-span-2">
                                <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                    Fecha <span class="text-rose-500">*</span>
                                </label>
                                <input type="date" 
                                       name="fecha" 
                                       x-model="formData.fecha" 
                                       @change="calcularFechaVencimientoPago()"
                                       required
                                       class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                            </div>

                            <!-- Condición de Pago (Col 3) -->
                            <div class="sm:col-span-3">
                                <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                    Condición de Pago
                                </label>
                                <select name="condicion_pago" 
                                        x-model="formData.condicion_pago" 
                                        @change="calcularFechaVencimientoPago()"
                                        class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs font-bold text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                                    <option value="contado">Contado (Pagada)</option>
                                    <option value="credito">Crédito (Cuentas por Pagar)</option>
                                </select>
                            </div>
                        </div>

                        <!-- Fila Condicional de Crédito -->
                        <div x-show="formData.condicion_pago === 'credito'" x-transition class="pt-2 border-t border-slate-200/70 dark:border-slate-700/70 grid grid-cols-1 sm:grid-cols-12 gap-2.5 items-center">
                            <div class="sm:col-span-6 flex items-center gap-1.5">
                                <span class="text-[11px] font-semibold text-slate-600 dark:text-slate-300 shrink-0">Días Crédito:</span>
                                <div class="flex items-center gap-1">
                                    <button type="button" @click="setDiasCredito(15)" :class="formData.dias_credito == 15 ? 'bg-amber-600 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200'" class="px-2 py-0.5 rounded text-[10px] font-bold">15d</button>
                                    <button type="button" @click="setDiasCredito(30)" :class="formData.dias_credito == 30 ? 'bg-amber-600 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200'" class="px-2 py-0.5 rounded text-[10px] font-bold">30d</button>
                                    <button type="button" @click="setDiasCredito(45)" :class="formData.dias_credito == 45 ? 'bg-amber-600 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200'" class="px-2 py-0.5 rounded text-[10px] font-bold">45d</button>
                                    <button type="button" @click="setDiasCredito(60)" :class="formData.dias_credito == 60 ? 'bg-amber-600 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200'" class="px-2 py-0.5 rounded text-[10px] font-bold">60d</button>
                                </div>
                                <input type="number" name="dias_credito" x-model="formData.dias_credito" @input="calcularFechaVencimientoPago()" min="1" max="365" class="w-14 px-1.5 py-0.5 text-center text-xs font-bold rounded border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800">
                            </div>
                            <div class="sm:col-span-6 flex items-center gap-2">
                                <span class="text-[11px] font-semibold text-slate-600 dark:text-slate-300 shrink-0">Vencimiento CxP:</span>
                                <input type="date" name="fecha_vencimiento_pago" x-model="formData.fecha_vencimiento_pago" class="w-full px-2 py-1 text-xs font-bold rounded border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-amber-700 dark:text-amber-400">
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
                                <span class="text-[10px] font-semibold text-slate-500 block">Lotes a Ingresar:</span>
                                <span class="font-bold text-slate-900 dark:text-white text-sm" x-text="items.length + ' líneas'"></span>
                            </div>
                            <div>
                                <span class="text-[10px] font-semibold text-slate-500 block">Unidades al Kardex:</span>
                                <span class="font-bold text-emerald-600 dark:text-emerald-400 text-sm" x-text="calcularTotalUnidadesBase() + ' u.'"></span>
                            </div>
                        </div>

                        <div class="pt-1.5 border-t border-slate-200/80 dark:border-slate-700/80 flex items-center justify-between">
                            <span class="text-xs font-semibold text-slate-600 dark:text-slate-300">Total Liquidado:</span>
                            <span class="text-xl font-extrabold text-emerald-600 dark:text-emerald-400 font-mono">
                                C$ <span x-text="calcularTotalGeneral()"></span>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Panel 3: Desglose de Fármacos, Presentaciones y Lotes -->
                <div class="p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/40 dark:bg-slate-800/30 space-y-3">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 border-b border-slate-200/60 dark:border-slate-700/60 pb-2">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center space-x-1.5 shrink-0">
                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                            <span>2. Desglose de Medicamentos, Presentaciones y Lotes</span>
                        </div>

                        <!-- Buscador Dinámico de Fármacos (Como en Ventas) -->
                        <div class="flex items-center space-x-2 flex-1 max-w-lg">
                            <div class="relative flex-1" @click.outside="busquedaDropdownAbierta = false">
                                <div class="relative">
                                    <input type="text"
                                           id="compraBuscador"
                                           x-model="busquedaProducto"
                                           @focus="busquedaDropdownAbierta = true"
                                           @input="busquedaDropdownAbierta = true"
                                           @keydown.enter.prevent="procesarEnterBuscador()"
                                           @keydown.escape="busquedaDropdownAbierta = false"
                                           placeholder="Buscar por código, principio activo o nombre... [F3]"
                                           class="w-full pl-8 pr-10 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:ring-1 focus:ring-emerald-500 font-medium">
                                    <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                    <span class="absolute right-2 top-1.5 px-1 py-0.2 rounded bg-slate-100 dark:bg-slate-700 text-[9px] font-mono text-slate-500 font-bold border border-slate-200 dark:border-slate-600 pointer-events-none">F3</span>
                                </div>

                                <!-- Dropdown flotante de resultados -->
                                <div x-show="busquedaDropdownAbierta && filtrarCatalogo().length > 0"
                                     x-cloak
                                     class="absolute left-0 right-0 top-full mt-1 z-50 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl shadow-2xl max-h-60 overflow-y-auto custom-scrollbar divide-y divide-slate-100 dark:divide-slate-800">
                                    <template x-for="p in filtrarCatalogo()" :key="p.id">
                                        <button type="button" 
                                                @click="seleccionarProductoDesdeBuscador(p)"
                                                class="w-full px-3 py-2 text-left hover:bg-emerald-50 dark:hover:bg-slate-800/80 flex items-center justify-between transition cursor-pointer group">
                                            <div class="flex items-center space-x-2 min-w-0">
                                                <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                                                <div class="truncate">
                                                    <span class="font-bold text-xs text-slate-900 dark:text-white group-hover:text-emerald-600 dark:group-hover:text-emerald-400" x-text="p.nombre"></span>
                                                    <div class="text-[10px] text-slate-500 dark:text-slate-400 flex items-center space-x-1.5">
                                                        <span x-show="p.principio_activo" x-text="p.principio_activo"></span>
                                                        <span x-show="p.codigo_barra" class="font-mono text-slate-400" x-text="'• ' + p.codigo_barra"></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="text-right shrink-0 pl-2">
                                                <span class="font-mono font-bold text-xs text-emerald-600 dark:text-emerald-400" x-text="'C$' + (parseFloat(p.precio_compra) || 0).toFixed(2)"></span>
                                            </div>
                                        </button>
                                    </template>
                                </div>
                            </div>

                            <button type="button" 
                                    @click="agregarItem()" 
                                    class="px-2.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-bold shadow-2xs transition flex items-center space-x-1 shrink-0 cursor-pointer">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                <span>+ Fila Manual</span>
                            </button>
                        </div>
                    </div>

                    <!-- Tabla de Lotes con scroll interno (máx ~5 items antes de scrollear) -->
                    <div class="overflow-x-auto overflow-y-auto max-h-[340px] rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800/80 custom-scrollbar">
                        <table class="w-full text-left text-xs border-collapse min-w-[850px]">
                            <thead class="sticky top-0 z-10 bg-slate-100 dark:bg-slate-700 shadow-2xs">
                                <tr class="text-[10px] font-bold text-slate-700 dark:text-slate-200 uppercase tracking-wider border-b border-slate-200 dark:border-slate-700">
                                    <th class="py-2.5 px-2 text-center w-8">#</th>
                                    <th class="py-2.5 px-3 min-w-[200px]">Medicamento / Fármaco</th>
                                    <th class="py-2.5 px-3 min-w-[190px]">Presentación</th>
                                    <th class="py-2.5 px-2 text-center w-24">Cant.</th>
                                    <th class="py-2.5 px-2 text-right w-24">P. Compra (C$)</th>
                                    <th class="py-2.5 px-2 w-28">N° Lote</th>
                                    <th class="py-2.5 px-2 w-32">F. Vencimiento</th>
                                    <th class="py-2.5 px-3 text-right w-24">Subtotal</th>
                                    <th class="py-2.5 px-2 text-center w-8"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                                <template x-for="(item, idx) in items" :key="item.uid">
                                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition">
                                        <!-- Indice -->
                                        <td class="py-2 px-2 text-center text-[11px] font-bold text-slate-400" x-text="idx + 1"></td>

                                        <!-- Producto & Hidden fields -->
                                        <td class="py-2 px-3">
                                            <input type="hidden" :name="'productos[' + idx + '][detalle_orden_compra_id]'" :value="item.detalle_orden_compra_id || ''">
                                            <input type="hidden" :name="'productos[' + idx + '][pedido]'" :value="item.pedido !== null && item.pedido !== undefined ? item.pedido : ''">
                                            <input type="hidden" :name="'productos[' + idx + '][recibido]'" :value="item.recibido !== null && item.recibido !== undefined ? item.recibido : ''">
                                            <input type="hidden" :name="'productos[' + idx + '][pendiente]'" :value="item.pendiente !== null && item.pendiente !== undefined ? item.pendiente : ''">
                                            <select :name="'productos[' + idx + '][producto_id]'" 
                                                    x-model="item.producto_id" 
                                                    @change="onProductoChange(idx)"
                                                    required
                                                    class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg text-xs text-slate-900 dark:text-white font-medium focus:ring-1 focus:ring-emerald-500">
                                                <option value="">Seleccione Medicamento...</option>
                                                <template x-for="p in catalogo" :key="p.id">
                                                    <option :value="p.id" x-text="p.nombre + (p.principio_activo ? ' (' + p.principio_activo + ')' : '')"></option>
                                                </template>
                                            </select>
                                        </td>

                                        <!-- Presentación con Atajo -->
                                        <td class="py-2.5 px-3">
                                            <div class="flex items-center space-x-1.5">
                                                <select :name="'productos[' + idx + '][presentacion_id]'" 
                                                        x-model="item.presentacion_id" 
                                                        @change="onPresentacionChange(idx)"
                                                        class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                                                    <option value="">Unidad Base (x1)</option>
                                                    <template x-for="pres in item.presentacionesDisponibles" :key="pres.id">
                                                        <option :value="pres.id" x-text="pres.nombre + ' (x' + pres.unidades + ')'"></option>
                                                    </template>
                                                </select>
                                                <button type="button" 
                                                        @click="abrirModalPresentacion(idx)"
                                                        :disabled="!item.producto_id"
                                                        title="Crear nueva presentación para este medicamento"
                                                        class="p-1.5 rounded-lg border border-emerald-300 dark:border-emerald-700 bg-emerald-50 dark:bg-emerald-950/60 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300 transition shrink-0 disabled:opacity-40 cursor-pointer">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                                </button>
                                            </div>
                                            <div class="text-[10px] text-emerald-600 dark:text-emerald-400 font-semibold mt-0.5" x-show="item.factor > 1">
                                                Total Base: <span x-text="calcularUnidadesBase(item)"></span> u.
                                            </div>
                                        </td>

                                        <!-- Cantidad con indicador de orden -->
                                        <td class="py-2 px-2">
                                            <input type="number" 
                                                   :name="'productos[' + idx + '][cantidad_presentaciones]'" 
                                                   x-model.number="item.cantidad" 
                                                   min="1" 
                                                   required
                                                   placeholder="1"
                                                   class="w-full px-2 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg text-xs text-slate-900 dark:text-white font-bold text-center focus:ring-1 focus:ring-emerald-500">
                                            <div class="text-[9px] text-slate-500 dark:text-slate-400 mt-0.5 whitespace-nowrap text-center" x-show="item.pedido !== null && item.pedido !== undefined">
                                                <span>Ped: <strong x-text="item.pedido"></strong> · Rec: <span x-text="item.recibido"></span> · <strong class="text-amber-800 dark:text-amber-300">Pend: <span x-text="item.pendiente"></span></strong></span>
                                            </div>
                                        </td>

                                        <!-- Precio Compra -->
                                        <td class="py-2 px-2">
                                            <input type="number" 
                                                   step="0.0001" 
                                                   min="0" 
                                                   :name="'productos[' + idx + '][precio_unitario]'" 
                                                   x-model="item.precio_unitario" 
                                                   required
                                                   placeholder="0.00"
                                                   class="w-full px-2 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg text-xs text-slate-900 dark:text-white font-bold text-right focus:ring-1 focus:ring-emerald-500">
                                            
                                            <!-- Badge Precio Anterior & Variación -->
                                            <div class="mt-1 flex flex-col items-end gap-0.5" x-show="item.producto_id && getPrecioAnterior(item.producto_id)">
                                                <span class="text-[10px] text-slate-400" :title="'Proveedor: ' + (getPrecioAnterior(item.producto_id)?.proveedor || '') + ' (' + (getPrecioAnterior(item.producto_id)?.fecha || '') + ')'">
                                                    Ant: C$<span x-text="(getPrecioAnterior(item.producto_id)?.precio || 0).toFixed(2)"></span>
                                                </span>
                                                <template x-if="getDeltaPrecio(item.producto_id, item.precio_unitario, item.factor)">
                                                    <span class="text-[9px] font-extrabold px-1 rounded inline-flex items-center"
                                                          :class="{
                                                              'bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300': getDeltaPrecio(item.producto_id, item.precio_unitario, item.factor).esAumento,
                                                              'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300': getDeltaPrecio(item.producto_id, item.precio_unitario, item.factor).esRebaja,
                                                              'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400': getDeltaPrecio(item.producto_id, item.precio_unitario, item.factor).esIgual
                                                          }">
                                                        <span x-show="getDeltaPrecio(item.producto_id, item.precio_unitario, item.factor).esAumento">↑ +</span>
                                                        <span x-show="getDeltaPrecio(item.producto_id, item.precio_unitario, item.factor).esRebaja">↓ </span>
                                                        <span x-text="getDeltaPrecio(item.producto_id, item.precio_unitario, item.factor).pct + '%'"></span>
                                                    </span>
                                                </template>
                                            </div>
                                        </td>

                                        <!-- N° Lote -->
                                        <td class="py-2 px-2">
                                            <input type="text" 
                                                   :name="'productos[' + idx + '][numero_lote]'" 
                                                   x-model="item.numero_lote" 
                                                   required
                                                   placeholder="LOTE-123"
                                                   class="w-full px-2 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg text-xs text-slate-900 dark:text-white uppercase font-mono focus:ring-1 focus:ring-emerald-500">
                                        </td>

                                        <!-- Fecha Vencimiento -->
                                        <td class="py-2 px-2">
                                            <input type="date" 
                                                   :name="'productos[' + idx + '][fecha_vencimiento]'" 
                                                   x-model="item.fecha_vencimiento" 
                                                   required
                                                   class="w-full px-2 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                                        </td>

                                        <!-- Subtotal -->
                                        <td class="py-2 px-3 text-right font-bold text-slate-900 dark:text-white">
                                            C$<span x-text="calcularSubtotal(item)"></span>
                                        </td>

                                        <!-- Botón Eliminar Fila -->
                                        <td class="py-2 px-2 text-center">
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

                <!-- Footer / Toolbar Inferior -->
                <div class="flex items-center justify-between pt-2 border-t border-slate-200 dark:border-slate-800 text-slate-400 text-[11px]">
                    <span>Los datos se sincronizan automáticamente en borrador temporal.</span>
                    <div class="flex items-center gap-3">
                        <label class="inline-flex items-center gap-2 cursor-pointer select-none text-slate-700 dark:text-slate-300 text-xs font-medium">
                            <input type="checkbox" name="crear_otro" value="1"
                                   {{ configuracion('interfaz_mantener_en_crear') ? 'checked' : '' }}
                                   class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                            <span>Guardar y registrar otra</span>
                        </label>
                        <button type="submit" 
                                :disabled="guardandoCompra"
                                class="px-4 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white text-xs font-extrabold shadow-sm transition flex items-center space-x-1.5 cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span x-text="guardandoCompra ? 'Guardando...' : 'Guardar Compra'">Guardar Compra</span>
                        </button>
                    </div>
                </div>
            </div>
        </template>


        <!-- ============================================================== -->
        <!-- VISTA MODERNA (TARJETAS ESPACIOSAS / HEADER STRIPS CON SVG)   -->
        <!-- ============================================================== -->
        <template x-if="formLayout === 'modern'">
            <div class="space-y-5 animate-fadeIn">
                
                <!-- Tarjeta 1: Datos del Comprobante y Proveedor -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-300 dark:border-slate-800 shadow-md">
                    <div class="px-5 py-3.5 border-b border-slate-200 dark:border-slate-800 bg-slate-100/70 dark:bg-slate-800/60 flex items-center space-x-2 rounded-t-2xl">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200">
                            Datos de la Factura y Proveedor
                        </h2>
                    </div>

                    <div class="p-5 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                            <!-- Proveedor (Col 4) -->
                            <div class="md:col-span-4 relative z-30" @click.outside="proveedorDropdownAbierto = false">
                                <label class="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-1.5">
                                    Proveedor Registrado <span class="text-rose-500">*</span>
                                </label>
                                <input type="hidden" name="proveedor_id" :value="formData.proveedor_id">
                                
                                <template x-if="proveedorSeleccionado()">
                                    <div class="flex items-center justify-between px-3.5 py-2 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-300 dark:border-emerald-700 rounded-xl text-xs shadow-2xs">
                                        <div class="flex items-center space-x-2 min-w-0">
                                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shrink-0"></span>
                                            <span class="font-bold text-emerald-950 dark:text-emerald-200 truncate" x-text="proveedorSeleccionado().nombre"></span>
                                            <span class="text-[11px] text-emerald-700 dark:text-emerald-300 font-mono" x-show="proveedorSeleccionado().ruc" x-text="'• RUC: ' + proveedorSeleccionado().ruc"></span>
                                        </div>
                                        <button type="button" @click="deseleccionarProveedor()" class="ml-2 px-2 py-0.5 text-[11px] font-semibold text-rose-600 dark:text-rose-400 hover:bg-rose-100 dark:hover:bg-rose-950/50 rounded-lg transition" title="Cambiar proveedor">
                                            Cambiar
                                        </button>
                                    </div>
                                </template>

                                <template x-if="!proveedorSeleccionado()">
                                    <div class="relative">
                                        <input type="text" 
                                               x-model="proveedorQuery" 
                                               @focus="proveedorDropdownAbierto = true"
                                               @input="proveedorDropdownAbierto = true"
                                               @keydown.enter.prevent="if (filtrarProveedores().length > 0) seleccionarProveedor(filtrarProveedores()[0])"
                                               placeholder="Buscar proveedor por nombre o RUC..."
                                               class="w-full pl-9 pr-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white font-semibold focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition shadow-2xs">
                                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                        </div>
                                    </div>
                                </template>

                                <!-- Dropdown predictivo -->
                                <div x-show="proveedorDropdownAbierto && !proveedorSeleccionado()" 
                                     x-transition
                                     class="absolute left-0 right-0 top-full mt-1 z-50 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-2xl max-h-56 overflow-y-auto">
                                    <template x-for="prov in filtrarProveedores()" :key="prov.id">
                                        <button type="button" 
                                                @click="seleccionarProveedor(prov)"
                                                class="w-full px-3.5 py-2.5 text-left hover:bg-emerald-50 dark:hover:bg-slate-700/60 flex items-center justify-between border-b border-slate-100 dark:border-slate-700/40 last:border-0 transition-colors">
                                            <div class="min-w-0">
                                                <div class="text-xs font-semibold text-slate-800 dark:text-slate-100 truncate" x-text="prov.nombre"></div>
                                                <div class="text-[10px] text-slate-500 dark:text-slate-400 flex items-center space-x-2 mt-0.5">
                                                    <span x-show="prov.ruc" x-text="'RUC: ' + prov.ruc"></span>
                                                    <span x-show="prov.telefono" x-text="'• Tel: ' + prov.telefono"></span>
                                                </div>
                                            </div>
                                            <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 shrink-0 ml-2">Seleccionar →</span>
                                        </button>
                                    </template>
                                    <div x-show="filtrarProveedores().length === 0" class="px-4 py-3 text-center text-xs text-slate-500 dark:text-slate-400">
                                        No se encontró ningún proveedor con "<span x-text="proveedorQuery" class="font-medium"></span>"
                                    </div>
                                </div>
                            </div>

                            <!-- Número Comprobante (Col 3) -->
                            <div class="md:col-span-3">
                                <label class="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-1.5">
                                    Número de Factura / Boleta
                                </label>
                                <input type="text" 
                                       name="numero_comprobante" 
                                       x-model="formData.numero_comprobante" 
                                       placeholder="Ej: F001-0004523"
                                       class="w-full px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition shadow-2xs font-mono">
                            </div>

                            <!-- Fecha (Col 2) -->
                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-1.5">
                                    Fecha Emisión <span class="text-rose-500">*</span>
                                </label>
                                <input type="date" 
                                       name="fecha" 
                                       x-model="formData.fecha" 
                                       @change="calcularFechaVencimientoPago()"
                                       required
                                       class="w-full px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white font-semibold focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition shadow-2xs">
                            </div>

                            <!-- Condición de Pago (Col 3) -->
                            <div class="md:col-span-3">
                                <label class="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-1.5">
                                    Condición de Pago <span class="text-rose-500">*</span>
                                </label>
                                <select name="condicion_pago" 
                                        x-model="formData.condicion_pago" 
                                        @change="calcularFechaVencimientoPago()"
                                        class="w-full px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white font-bold focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition shadow-2xs">
                                    <option value="contado">Contado (Pagado Inmediato)</option>
                                    <option value="credito">Crédito (Cuentas por Pagar)</option>
                                </select>
                            </div>
                        </div>

                        <!-- Opciones de Crédito Expandidas -->
                        <div x-show="formData.condicion_pago === 'credito'" x-transition class="p-3.5 rounded-xl bg-amber-50/70 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/60 grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
                            <div class="sm:col-span-6 flex flex-wrap items-center gap-2">
                                <span class="text-xs font-bold text-amber-900 dark:text-amber-300">Plazo de Crédito:</span>
                                <div class="flex items-center gap-1">
                                    <button type="button" @click="setDiasCredito(15)" :class="formData.dias_credito == 15 ? 'bg-amber-600 text-white shadow-xs' : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-700'" class="px-2.5 py-1 rounded-lg text-xs font-bold transition">15 días</button>
                                    <button type="button" @click="setDiasCredito(30)" :class="formData.dias_credito == 30 ? 'bg-amber-600 text-white shadow-xs' : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-700'" class="px-2.5 py-1 rounded-lg text-xs font-bold transition">30 días</button>
                                    <button type="button" @click="setDiasCredito(45)" :class="formData.dias_credito == 45 ? 'bg-amber-600 text-white shadow-xs' : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-700'" class="px-2.5 py-1 rounded-lg text-xs font-bold transition">45 días</button>
                                    <button type="button" @click="setDiasCredito(60)" :class="formData.dias_credito == 60 ? 'bg-amber-600 text-white shadow-xs' : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-700'" class="px-2.5 py-1 rounded-lg text-xs font-bold transition">60 días</button>
                                </div>
                                <div class="flex items-center gap-1">
                                    <input type="number" name="dias_credito" x-model="formData.dias_credito" @input="calcularFechaVencimientoPago()" min="1" max="365" class="w-16 px-2 py-1 text-center text-xs font-bold rounded-lg border-amber-300 dark:border-amber-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                                    <span class="text-xs text-amber-800 dark:text-amber-400 font-semibold">días</span>
                                </div>
                            </div>
                            <div class="sm:col-span-6 flex items-center justify-end gap-2">
                                <span class="text-xs font-bold text-amber-900 dark:text-amber-300">Vence en Cuentas por Pagar:</span>
                                <input type="date" name="fecha_vencimiento_pago" x-model="formData.fecha_vencimiento_pago" class="px-3 py-1.5 text-xs font-bold rounded-xl border-amber-300 dark:border-amber-700 bg-white dark:bg-slate-800 text-amber-900 dark:text-amber-200">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tarjeta 2: Ingreso de Medicamentos y Lotes -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-300 dark:border-slate-800 shadow-md overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-slate-200 dark:border-slate-800 bg-slate-100/70 dark:bg-slate-800/60 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center space-x-2 shrink-0">
                            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200">
                                Medicamentos, Presentaciones y Lotes
                            </h2>
                        </div>

                        <!-- Buscador Dinámico de Fármacos en Modo Moderno -->
                        <div class="flex items-center space-x-2 flex-1 max-w-lg">
                            <div class="relative flex-1" @click.outside="busquedaDropdownAbierta = false">
                                <div class="relative">
                                    <input type="text"
                                           x-model="busquedaProducto"
                                           @focus="busquedaDropdownAbierta = true"
                                           @input="busquedaDropdownAbierta = true"
                                           @keydown.enter.prevent="procesarEnterBuscador()"
                                           @keydown.escape="busquedaDropdownAbierta = false"
                                           placeholder="Buscar por código, principio activo o nombre... [F3]"
                                           class="w-full pl-8 pr-10 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition shadow-2xs font-medium">
                                    <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                    <span class="absolute right-2 top-1.5 px-1.5 py-0.2 rounded bg-slate-100 dark:bg-slate-700 text-[9px] font-mono text-slate-500 font-bold border border-slate-200 dark:border-slate-600 pointer-events-none">F3</span>
                                </div>

                                <!-- Dropdown flotante de resultados -->
                                <div x-show="busquedaDropdownAbierta && filtrarCatalogo().length > 0"
                                     x-cloak
                                     class="absolute left-0 right-0 top-full mt-1.5 z-50 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl shadow-2xl max-h-60 overflow-y-auto custom-scrollbar divide-y divide-slate-100 dark:divide-slate-800">
                                    <template x-for="p in filtrarCatalogo()" :key="p.id">
                                        <button type="button" 
                                                @click="seleccionarProductoDesdeBuscador(p)"
                                                class="w-full px-3.5 py-2 text-left hover:bg-emerald-50 dark:hover:bg-slate-800/80 flex items-center justify-between transition cursor-pointer group">
                                            <div class="flex items-center space-x-2 min-w-0">
                                                <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                                                <div class="truncate">
                                                    <span class="font-bold text-xs text-slate-900 dark:text-white group-hover:text-emerald-600 dark:group-hover:text-emerald-400" x-text="p.nombre"></span>
                                                    <div class="text-[10px] text-slate-500 dark:text-slate-400 flex items-center space-x-1.5">
                                                        <span x-show="p.principio_activo" x-text="p.principio_activo"></span>
                                                        <span x-show="p.codigo_barra" class="font-mono text-slate-400" x-text="'• ' + p.codigo_barra"></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="text-right shrink-0 pl-2">
                                                <span class="font-mono font-bold text-xs text-emerald-600 dark:text-emerald-400" x-text="'C$' + (parseFloat(p.precio_compra) || 0).toFixed(2)"></span>
                                            </div>
                                        </button>
                                    </template>
                                </div>
                            </div>

                            <button type="button" 
                                    @click="agregarItem()" 
                                    class="inline-flex items-center space-x-1 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition shrink-0 cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                <span>+ Fila</span>
                            </button>
                        </div>
                    </div>

                    <div class="p-5 space-y-4 max-h-[580px] overflow-y-auto custom-scrollbar">
                        <template x-for="(item, idx) in items" :key="item.uid">
                            <div class="p-4 rounded-xl bg-slate-50/80 dark:bg-slate-800/50 border border-slate-300 dark:border-slate-700 space-y-3 transition">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300 flex items-center space-x-1.5">
                                        <span class="w-5 h-5 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 flex items-center justify-center text-[10px] font-bold" x-text="idx + 1"></span>
                                        <span>Lote de Compra</span>
                                    </span>

                                    <div class="flex items-center space-x-3">
                                        <div class="text-xs font-bold text-slate-800 dark:text-slate-200">
                                            Subtotal: <span class="text-emerald-600 dark:text-emerald-400 text-sm">C$<span x-text="calcularSubtotal(item)"></span></span>
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
                                        <input type="hidden" :name="'productos[' + idx + '][detalle_orden_compra_id]'" :value="item.detalle_orden_compra_id || ''">
                                        <input type="hidden" :name="'productos[' + idx + '][pedido]'" :value="item.pedido !== null && item.pedido !== undefined ? item.pedido : ''">
                                        <input type="hidden" :name="'productos[' + idx + '][recibido]'" :value="item.recibido !== null && item.recibido !== undefined ? item.recibido : ''">
                                        <input type="hidden" :name="'productos[' + idx + '][pendiente]'" :value="item.pendiente !== null && item.pendiente !== undefined ? item.pendiente : ''">
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

                                    <!-- Cantidad con indicador de orden -->
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
                                        <div class="text-[10px] text-slate-500 dark:text-slate-400 mt-1 whitespace-nowrap" x-show="item.pedido !== null && item.pedido !== undefined">
                                            <span>Pedido <strong x-text="item.pedido"></strong> · Recibido <span x-text="item.recibido"></span> · <strong class="text-amber-800 dark:text-amber-300">Pend. <span x-text="item.pendiente"></span></strong></span>
                                        </div>
                                    </div>

                                    <!-- Precio Unitario -->
                                    <div class="md:col-span-3">
                                        <div class="flex items-center justify-between mb-1">
                                            <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300">
                                                Precio Unit. (C$) <span class="text-rose-500">*</span>
                                            </label>
                                            <template x-if="item.producto_id && getPrecioAnterior(item.producto_id)">
                                                <span class="text-[10px] text-slate-400" :title="'Proveedor: ' + (getPrecioAnterior(item.producto_id)?.proveedor || '') + ' (' + (getPrecioAnterior(item.producto_id)?.fecha || '') + ')'">
                                                    Ant: C$<span x-text="(getPrecioAnterior(item.producto_id)?.precio || 0).toFixed(2)"></span>
                                                </span>
                                            </template>
                                        </div>
                                        <div class="relative">
                                            <input type="number" 
                                                   step="0.0001" 
                                                   min="0" 
                                                   :name="'productos[' + idx + '][precio_unitario]'" 
                                                   x-model="item.precio_unitario" 
                                                   required
                                                   placeholder="0.00"
                                                   class="w-full px-3 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white font-bold text-right focus:ring-2 focus:ring-emerald-500">
                                        </div>
                                        <template x-if="item.producto_id && getDeltaPrecio(item.producto_id, item.precio_unitario, item.factor)">
                                            <div class="mt-1 flex items-center justify-end space-x-1">
                                                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded inline-flex items-center"
                                                      :class="{
                                                          'bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300': getDeltaPrecio(item.producto_id, item.precio_unitario, item.factor).esAumento,
                                                          'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300': getDeltaPrecio(item.producto_id, item.precio_unitario, item.factor).esRebaja,
                                                          'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400': getDeltaPrecio(item.producto_id, item.precio_unitario, item.factor).esIgual
                                                      }">
                                                    <span x-show="getDeltaPrecio(item.producto_id, item.precio_unitario, item.factor).esAumento">↑ +</span>
                                                    <span x-show="getDeltaPrecio(item.producto_id, item.precio_unitario, item.factor).esRebaja">↓ </span>
                                                    <span x-text="getDeltaPrecio(item.producto_id, item.precio_unitario, item.factor).pct + '%'"></span>
                                                    <span class="ml-1" x-show="getDeltaPrecio(item.producto_id, item.precio_unitario, item.factor).esAumento">más caro</span>
                                                    <span class="ml-1" x-show="getDeltaPrecio(item.producto_id, item.precio_unitario, item.factor).esRebaja">más barato</span>
                                                </span>
                                            </div>
                                        </template>
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

                <!-- Tarjeta 3: Totales y Acciones Sticky Bottom -->
                <div class="sticky bottom-3 z-20 bg-white/95 dark:bg-slate-900/95 backdrop-blur-md rounded-2xl p-4 sm:p-5 border border-slate-300 dark:border-slate-800 shadow-xl flex flex-col md:flex-row items-center justify-between gap-4">
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
                            <span class="block text-slate-400 text-[11px]">Monto Total:</span>
                            <span class="font-extrabold text-emerald-600 dark:text-emerald-400 text-xl">
                                C$<span x-text="calcularTotalGeneral()"></span>
                            </span>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row items-center gap-3 w-full md:w-auto">
                        <label class="inline-flex items-center gap-2 cursor-pointer select-none text-slate-700 dark:text-slate-300 text-xs font-medium self-start sm:self-auto">
                            <input type="checkbox" name="crear_otro" value="1"
                                   {{ configuracion('interfaz_mantener_en_crear') ? 'checked' : '' }}
                                   class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                            <span>Guardar y registrar otra</span>
                        </label>
                        <div class="flex items-center space-x-2 w-full sm:w-auto">
                            <a href="{{ route('compras.index') }}" 
                               class="flex-1 sm:flex-none px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition text-center">
                                Cancelar
                            </a>
                            <button type="submit" 
                                    :disabled="guardandoCompra"
                                    class="flex-1 sm:flex-none px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed text-white text-xs font-bold shadow-xs transition flex items-center justify-center space-x-2 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span x-text="guardandoCompra ? 'Guardando...' : 'Guardar Compra e Ingresar Lotes'">Guardar Compra e Ingresar Lotes</span>
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </template>

    </form>

    <!-- ============================================================== -->
    <!-- MODAL RÁPIDO: CREAR NUEVA PRESENTACIÓN                        -->
    <!-- ============================================================== -->
    <template x-teleport="body">
    <div x-show="modalNuevaPres" 
         x-cloak
         class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-slate-950/75 backdrop-blur-sm transition-opacity overflow-y-auto"
         @keydown.escape.window="modalNuevaPres = false"
         @click.self="modalNuevaPres = false">
        
        <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full p-5 border border-slate-300 dark:border-slate-800 shadow-xl space-y-4 my-auto"
             @click.stop>
            
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
                <button @click="modalNuevaPres = false" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer" title="Cerrar">✕</button>
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
                            Precio de Compra (C$)
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
                            Precio de Venta (C$)
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
                        class="px-3.5 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer">
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
    </template>

    <!-- ============================================================== -->
    <!-- MODAL: ORDEN CON UNIDADES PENDIENTES                          -->
    <!-- ============================================================== -->
    <template x-teleport="body">
    <div x-show="modalOrdenPendiente" 
         x-cloak
         class="fixed inset-0 z-[10000] flex items-center justify-center p-4 bg-slate-950/75 backdrop-blur-sm transition-opacity overflow-y-auto"
         @keydown.escape.window="modalOrdenPendiente = false"
         @click.self="modalOrdenPendiente = false">
        
        <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-lg w-full p-5 border border-slate-300 dark:border-slate-800 shadow-2xl space-y-4 my-auto"
             @click.stop>
            
            <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800">
                <div class="flex items-center space-x-2.5">
                    <div class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Orden de Compra con Unidades Pendientes</h3>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">Esta recepción no cubre la totalidad de los medicamentos solicitados.</p>
                    </div>
                </div>
                <button @click="modalOrdenPendiente = false" class="w-7 h-7 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer" title="Cerrar">✕</button>
            </div>

            <p class="text-xs text-slate-600 dark:text-slate-300">
                Quedan medicamentos pendientes por recibir en esta orden de compra:
            </p>

            <!-- Tabla de líneas faltantes -->
            <div class="overflow-x-auto max-h-48 border border-slate-200 dark:border-slate-800 rounded-xl">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-100 dark:bg-slate-800 text-[10px] font-bold text-slate-700 dark:text-slate-300 uppercase">
                        <tr>
                            <th class="py-2 px-3">Medicamento</th>
                            <th class="py-2 px-2 text-center">Pedido</th>
                            <th class="py-2 px-2 text-center">Ingresando</th>
                            <th class="py-2 px-2 text-center text-amber-800 dark:text-amber-300">Pendiente</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <template x-for="linea in lineasFaltantes" :key="linea.nombre">
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                <td class="py-2 px-3 font-semibold text-slate-800 dark:text-slate-200" x-text="linea.nombre"></td>
                                <td class="py-2 px-2 text-center text-slate-500" x-text="linea.pedido + ' u.'"></td>
                                <td class="py-2 px-2 text-center font-bold text-emerald-600 dark:text-emerald-400" x-text="linea.ingresando + ' u.'"></td>
                                <td class="py-2 px-2 text-center font-extrabold text-amber-900 dark:text-amber-300" x-text="linea.nuevo_pendiente + ' u.'"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Pregunta de decisión -->
            <div class="p-3 bg-amber-50/70 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800 rounded-xl space-y-2">
                <p class="text-xs font-bold text-amber-950 dark:text-amber-200">
                    ¿Desea dar esta orden por COMPLETA (cerrada con faltante) o DEJARLA PENDIENTE para futuras recepciones?
                </p>
                <div>
                    <label class="block text-[11px] font-semibold text-amber-900 dark:text-amber-300 mb-1">
                        Motivo del faltante (si decide cerrarla como completa):
                    </label>
                    <textarea x-model="motivoFaltante" 
                              rows="2" 
                              placeholder="Ej: Proveedor no entregará el resto por desabastecimiento de laboratorio..."
                              class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-amber-300 dark:border-amber-700 rounded-lg text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:ring-1 focus:ring-amber-500"></textarea>
                </div>
            </div>

            <!-- Botones de Acción (Requisito: No, dejar pendiente = verde sólido; Sí, cerrar como completa = pastel amber) -->
            <div class="flex flex-col sm:flex-row items-center justify-end gap-2 pt-2 border-t border-slate-200 dark:border-slate-800">
                <button type="button" 
                        @click="modalOrdenPendiente = false" 
                        class="w-full sm:w-auto px-3.5 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer order-3 sm:order-1">
                    Revisar Cantidades
                </button>
                <button type="button" 
                        @click="confirmarCierreOrden(true)" 
                        class="w-full sm:w-auto px-3.5 py-2 rounded-xl bg-amber-100 hover:bg-amber-200 active:bg-amber-300 text-amber-950 dark:bg-amber-950/70 dark:hover:bg-amber-900/80 dark:text-amber-200 text-xs font-bold border border-amber-300 dark:border-amber-700 transition cursor-pointer order-2">
                    Sí, cerrar como completa
                </button>
                <button type="button" 
                        @click="confirmarCierreOrden(false)" 
                        class="w-full sm:w-auto px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-bold shadow-xs transition cursor-pointer order-1 sm:order-3">
                    No, dejar pendiente
                </button>
            </div>
        </div>
    </div>
    </template>

    <!-- ============================================================== -->
    <!-- MODAL: UNIDADES EXCEDEN LO SOLICITADO                         -->
    <!-- ============================================================== -->
    <template x-teleport="body">
    <div x-show="modalOrdenExcedente" 
         x-cloak
         class="fixed inset-0 z-[10000] flex items-center justify-center p-4 bg-slate-950/75 backdrop-blur-sm transition-opacity overflow-y-auto"
         @keydown.escape.window="modalOrdenExcedente = false"
         @click.self="modalOrdenExcedente = false">
        
        <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full p-5 border border-slate-300 dark:border-slate-800 shadow-2xl space-y-4 my-auto"
             @click.stop>
            
            <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800">
                <div class="flex items-center space-x-2.5">
                    <div class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Unidades Exceden la Orden</h3>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">Cantidad mayor a lo pendiente en la orden.</p>
                    </div>
                </div>
                <button @click="modalOrdenExcedente = false" class="w-7 h-7 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer" title="Cerrar">✕</button>
            </div>

            <p class="text-xs text-slate-600 dark:text-slate-300">
                Las siguientes líneas ingresan más unidades de las pactadas en la orden:
            </p>

            <div class="overflow-x-auto max-h-40 border border-slate-200 dark:border-slate-800 rounded-xl">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-100 dark:bg-slate-800 text-[10px] font-bold text-slate-700 dark:text-slate-300 uppercase">
                        <tr>
                            <th class="py-2 px-3">Medicamento</th>
                            <th class="py-2 px-2 text-center">Pendiente</th>
                            <th class="py-2 px-2 text-center">Ingresando</th>
                            <th class="py-2 px-2 text-center text-blue-700 dark:text-blue-300">Excedente</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <template x-for="linea in lineasExcedentes" :key="linea.nombre">
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                <td class="py-2 px-3 font-semibold text-slate-800 dark:text-slate-200" x-text="linea.nombre"></td>
                                <td class="py-2 px-2 text-center text-slate-500" x-text="linea.pendiente + ' u.'"></td>
                                <td class="py-2 px-2 text-center font-bold text-emerald-600 dark:text-emerald-400" x-text="linea.ingresando + ' u.'"></td>
                                <td class="py-2 px-2 text-center font-extrabold text-blue-800 dark:text-blue-300" x-text="'+' + linea.diferencia + ' u.'"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div class="flex items-center justify-end space-x-2 pt-2 border-t border-slate-200 dark:border-slate-800">
                <button type="button" 
                        @click="modalOrdenExcedente = false" 
                        class="px-3.5 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer">
                    Ajustar Cantidad
                </button>
                <button type="button" 
                        @click="confirmarExcedente()" 
                        class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-xs transition cursor-pointer">
                    Aceptar Excedente
                </button>
            </div>
        </div>
    </div>
    </template>

</div>
@endsection