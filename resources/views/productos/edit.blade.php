@extends('layouts.app')

@section('title', 'Editar: ' . $producto->nombre . ' - FarmaBien')

@section('content')
<div x-data="{
    formLayout: localStorage.getItem('farmaFormViewMode') || 'modern',
    setLayout(mode) {
        this.formLayout = mode;
        localStorage.setItem('farmaFormViewMode', mode);
        window.dispatchEvent(new CustomEvent('farma:layout-changed', { detail: { mode: mode } }));
    },
    formData: (window.farmaGetDraft ? window.farmaGetDraft('{{ request()->getPathInfo() }}', {
        nombre: @js(old('nombre', $producto->nombre)),
        codigo_barra: @js(old('codigo_barra', $producto->codigo_barra)),
        principio_activo: @js(old('principio_activo', $producto->principio_activo)),
        concentracion: @js(old('concentracion', $producto->concentracion)),
        forma_farmaceutica: @js(old('forma_farmaceutica', $producto->forma_farmaceutica)),
        descripcion: @js(old('descripcion', $producto->descripcion)),
        categoria_id: @js(old('categoria_id', $producto->categoria_id)),
        laboratorio_id: @js(old('laboratorio_id', $producto->laboratorio_id)),
        tipo_control: @js(old('tipo_control', $producto->tipo_control)),
        registro_sanitario: @js(old('registro_sanitario', $producto->registro_sanitario)),
        requiere_receta: @js(old('requiere_receta', $producto->requiere_receta) ? true : false),
        activo: @js(old('activo', $producto->activo) ? true : false),
        precio_compra: @js(old('precio_compra', $producto->precio_compra ?? '')),
        precio_venta: @js(old('precio_venta', $producto->precio_venta)),
        stock_minimo: @js(old('stock_minimo', $producto->stock_minimo ?? 10)),
        ubicacion: @js(old('ubicacion', $producto->ubicacion ?? ''))
    }) : {
        nombre: @js(old('nombre', $producto->nombre)),
        codigo_barra: @js(old('codigo_barra', $producto->codigo_barra)),
        principio_activo: @js(old('principio_activo', $producto->principio_activo)),
        concentracion: @js(old('concentracion', $producto->concentracion)),
        forma_farmaceutica: @js(old('forma_farmaceutica', $producto->forma_farmaceutica)),
        descripcion: @js(old('descripcion', $producto->descripcion)),
        categoria_id: @js(old('categoria_id', $producto->categoria_id)),
        laboratorio_id: @js(old('laboratorio_id', $producto->laboratorio_id)),
        tipo_control: @js(old('tipo_control', $producto->tipo_control)),
        registro_sanitario: @js(old('registro_sanitario', $producto->registro_sanitario)),
        requiere_receta: @js(old('requiere_receta', $producto->requiere_receta) ? true : false),
        activo: @js(old('activo', $producto->activo) ? true : false),
        precio_compra: @js(old('precio_compra', $producto->precio_compra ?? '')),
        precio_venta: @js(old('precio_venta', $producto->precio_venta)),
        stock_minimo: @js(old('stock_minimo', $producto->stock_minimo ?? 10)),
        ubicacion: @js(old('ubicacion', $producto->ubicacion ?? ''))
    }),
    presentaciones: @js($producto->presentaciones->isNotEmpty() ? $producto->presentaciones->map(function($p) {
        return [
            'id' => $p->id,
            'nombre' => $p->nombre,
            'unidades_por_presentacion' => $p->unidades_por_presentacion,
            'precio_compra' => $p->precio_compra,
            'precio_venta' => $p->precio_venta,
            'codigo_barras' => $p->codigo_barras,
            'es_unidad_base' => (bool)$p->es_unidad_base
        ];
    })->values()->all() : [
        ['nombre' => 'Unidad Base (Pastilla / Ampolla)', 'unidades_por_presentacion' => 1, 'precio_compra' => $producto->precio_compra, 'precio_venta' => $producto->precio_venta, 'codigo_barras' => '', 'es_unidad_base' => true]
    ]),
    agregarPresentacion(tipo = 'Blíster', factor = 10) {
        const pCompraBase = parseFloat(this.formData.precio_compra) || 0;
        const pVentaBase = parseFloat(this.formData.precio_venta) || 0;
        this.presentaciones.push({
            nombre: tipo + ' (' + factor + ' unidades)',
            unidades_por_presentacion: factor,
            precio_compra: pCompraBase > 0 ? (pCompraBase * factor).toFixed(2) : '',
            precio_venta: pVentaBase > 0 ? (pVentaBase * factor).toFixed(2) : '',
            codigo_barras: '',
            es_unidad_base: false
        });
    },
    eliminarPresentacion(idx) {
        if (this.presentaciones[idx].es_unidad_base) {
            alert('No se puede eliminar la unidad base.');
            return;
        }
        this.presentaciones.splice(idx, 1);
    },
    imgPreview: @js($producto->imagen ? asset('storage/' . $producto->imagen) : null),
    calcularMargen() {
        const c = parseFloat(this.formData.precio_compra) || 0;
        const v = parseFloat(this.formData.precio_venta) || 0;
        if (v <= 0) return 0;
        return (((v - c) / v) * 100).toFixed(1);
    },
    previewFile(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (ev) => { this.imgPreview = ev.target.result; };
            reader.readAsDataURL(file);
        }
    }
}"
@keydown.window="if ($event.key === 'Escape' && formLayout === 'compact') { window.location.href = '{{ route('productos.index') }}'; }"
:class="formLayout === 'compact' ? 'w-full max-w-full' : 'max-w-6xl mx-auto'"
class="space-y-4 transition-all duration-200">
    
    <!-- Breadcrumbs & View Toggle Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-1 border-b border-slate-300/80 dark:border-slate-800">
        <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
            <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
            <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <a href="{{ route('productos.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Medicamentos</a>
            <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-slate-800 dark:text-slate-200 font-semibold truncate max-w-[200px]">Editar: {{ $producto->nombre }}</span>
        </nav>

        <!-- View Mode Switcher -->
        <div class="flex items-center space-x-2 self-start sm:self-auto">
            <span class="text-[11px] font-semibold text-slate-600 dark:text-slate-400 hidden md:inline">Diseño:</span>
            <div class="inline-flex items-center p-0.5 rounded-xl bg-slate-200/80 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-xs font-semibold shadow-2xs">
                <button type="button" 
                        @click="setLayout('modern')"
                        :class="formLayout === 'modern' ? 'bg-white dark:bg-slate-700 text-emerald-700 dark:text-emerald-400 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200'"
                        class="px-3 py-1 rounded-lg transition flex items-center space-x-1.5 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    <span>Moderna</span>
                </button>
                <button type="button" 
                        @click="setLayout('compact')"
                        :class="formLayout === 'compact' ? 'bg-white dark:bg-slate-700 text-emerald-700 dark:text-emerald-400 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200'"
                        class="px-3 py-1 rounded-lg transition flex items-center space-x-1.5 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <span>Compacta (POS / ERP)</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
        <div>
            <h1 class="text-lg sm:text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <span>Editar Medicamento: {{ $producto->nombre }}</span>
            </h1>
            <p class="text-xs text-slate-600 dark:text-slate-400">
                Código: <span class="font-mono text-emerald-700 dark:text-emerald-400 font-bold">{{ $producto->codigo_barra ?? 'S/C' }}</span> &bull; Stock actual registrado en lotes.
            </p>
        </div>
        <div class="flex items-center space-x-2 shrink-0 self-start sm:self-auto">
            <a href="{{ route('productos.show', $producto) }}" 
               class="px-3.5 py-1.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-300 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs font-bold transition flex items-center space-x-1.5 shadow-2xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                <span>Ver Ficha</span>
            </a>
            <a href="{{ route('productos.index') }}" 
               class="px-3.5 py-1.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold transition flex items-center space-x-1.5 shadow-2xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Catálogo</span>
            </a>
        </div>
    </div>

    <!-- FORMULARIO -->
        <form x-ref="productoEditForm"
          action="{{ route('productos.update', $producto) }}" 
          method="POST" 
          enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <!-- ========================================== -->
        <!-- MODO COMPACTO ESCRITORIO (SIN SCROLL / POS) -->
        <!-- ========================================== -->
        <template x-if="formLayout === 'compact'">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-300 dark:border-slate-800 shadow-md p-4 space-y-4 animate-fadeIn">
                
                <!-- Toolbar Superior -->
                <div class="flex items-center justify-between pb-3 border-b border-slate-300 dark:border-slate-800 bg-slate-100/80 dark:bg-slate-800/60 -mx-4 -mt-4 p-3 rounded-t-2xl">
                    <div class="flex items-center space-x-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shadow-xs"></span>
                        <span class="text-xs font-extrabold text-slate-800 dark:text-slate-200 uppercase tracking-wide">EDICIÓN ERP / POS #{{ $producto->id }}</span>
                        <span class="text-[10px] px-2 py-0.5 rounded-md bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-mono font-bold">Esc = Salir</span>
                    </div>

                    <div class="flex items-center space-x-2">
                        <a href="{{ route('productos.index') }}" 
                           class="px-3 py-1.5 rounded-lg border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-800 text-xs font-bold transition">
                            Cancelar (Esc)
                        </a>
                        <button type="submit" 
                                class="px-4 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-extrabold shadow-sm transition flex items-center space-x-1.5 cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Guardar Cambios</span>
                        </button>
                    </div>
                </div>

                <!-- Grid Compacta de 2 Paneles -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    
                    <!-- Panel 1: Identificación Farmacológica y Clasificación -->
                    <div class="p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/40 dark:bg-slate-800/30 space-y-3">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center space-x-1.5 border-b border-slate-200/60 dark:border-slate-700/60 pb-1.5">
                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                            <span>1. Identificación Farmacológica & Clasificación</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-2.5">
                            <div class="sm:col-span-8">
                                <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                    Nombre Comercial <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" 
                                       name="nombre" 
                                       x-model="formData.nombre"
                                       required 
                                       autofocus
                                       placeholder="Ej: Panadol Antigripal NF" 
                                       class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white font-medium focus:ring-1 focus:ring-emerald-500">
                            </div>

                            <div class="sm:col-span-4">
                                <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                    Código de Barras / EAN
                                </label>
                                <input type="text" 
                                       name="codigo_barra" 
                                       x-model="formData.codigo_barra"
                                       placeholder="775123456789" 
                                       class="w-full font-mono px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-2.5">
                            <div class="sm:col-span-6">
                                <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                    Principio Activo (DCI)
                                </label>
                                <input type="text" 
                                       name="principio_activo" 
                                       x-model="formData.principio_activo"
                                       placeholder="Ej: Paracetamol" 
                                       class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                            </div>

                            <div class="sm:col-span-3">
                                <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                    Concentración
                                </label>
                                <input type="text" 
                                       name="concentracion" 
                                       x-model="formData.concentracion"
                                       placeholder="500 mg" 
                                       class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                            </div>

                            <div class="sm:col-span-3">
                                <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                    Forma Farmacéutica
                                </label>
                                <input type="text" 
                                       name="forma_farmaceutica" 
                                       x-model="formData.forma_farmaceutica"
                                       placeholder="Tabletas" 
                                       class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                    Categoría <span class="text-rose-500">*</span>
                                </label>
                                <select name="categoria_id" 
                                        x-model="formData.categoria_id"
                                        required 
                                        class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                                    <option value="">Seleccione categoría...</option>
                                    @foreach($categorias as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                    Laboratorio Farmacéutico
                                </label>
                                <select name="laboratorio_id" 
                                        x-model="formData.laboratorio_id"
                                        class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                                    <option value="">Sin especificar</option>
                                    @foreach($laboratorios as $lab)
                                    <option value="{{ $lab->id }}">{{ $lab->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                    Régimen de Venta <span class="text-rose-500">*</span>
                                </label>
                                <select name="tipo_control" 
                                        x-model="formData.tipo_control"
                                        required 
                                        class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                                    <option value="venta_libre">Venta Libre (OTC)</option>
                                    <option value="receta_medica">Receta Médica Simple</option>
                                    <option value="receta_retenida">Psicotrópico / Controlado</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                    Reg. Sanitario (DIGEMID)
                                </label>
                                <input type="text" 
                                       name="registro_sanitario" 
                                       x-model="formData.registro_sanitario"
                                       placeholder="EE-12345" 
                                       class="w-full font-mono px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Descripción / Acción Breve
                            </label>
                            <input type="text" 
                                   name="descripcion" 
                                   x-model="formData.descripcion"
                                   placeholder="Indicación breve, posología o acción terapéutica..." 
                                   class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                        </div>

                        <div class="flex items-center space-x-4 pt-1">
                            <label class="inline-flex items-center space-x-2 cursor-pointer text-xs">
                                <input type="checkbox" name="requiere_receta" x-model="formData.requiere_receta" value="1" class="rounded border-slate-300 text-emerald-600 w-3.5 h-3.5">
                                <span class="text-[11px] font-semibold text-slate-700 dark:text-slate-300">Exigir Receta Médica</span>
                            </label>

                            <label class="inline-flex items-center space-x-2 cursor-pointer text-xs">
                                <input type="checkbox" name="activo" x-model="formData.activo" value="1" class="rounded border-slate-300 text-emerald-600 w-3.5 h-3.5">
                                <span class="text-[11px] font-semibold text-emerald-700 dark:text-emerald-400">Producto Activo para Venta</span>
                            </label>
                        </div>
                    </div>

                    <!-- Panel 2: Precios, Stock, Presentaciones & Foto -->
                    <div class="p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/40 dark:bg-slate-800/30 space-y-3">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center justify-between border-b border-slate-200/60 dark:border-slate-700/60 pb-1.5">
                            <div class="flex items-center space-x-1.5">
                                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>2. Precios, Stock & Presentaciones</span>
                            </div>
                            <span class="text-[10px] text-emerald-700 dark:text-emerald-400 font-bold">Unidad Base</span>
                        </div>

                        <!-- Precios y Margen -->
                        <div class="grid grid-cols-3 gap-2.5">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                    P. Compra (S/)
                                </label>
                                <input type="number" 
                                       step="0.01" 
                                       min="0" 
                                       name="precio_compra" 
                                       x-model="formData.precio_compra" 
                                       placeholder="0.00" 
                                       class="w-full font-mono px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                            </div>

                            <div>
                                <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                    P. Venta Base <span class="text-rose-500">*</span>
                                </label>
                                <input type="number" 
                                       step="0.01" 
                                       min="0" 
                                       name="precio_venta" 
                                       x-model="formData.precio_venta" 
                                       required 
                                       placeholder="0.00" 
                                       class="w-full font-mono font-bold px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-emerald-500 rounded-lg text-xs text-emerald-700 dark:text-emerald-300 focus:ring-1 focus:ring-emerald-500">
                            </div>

                            <div>
                                <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1 text-center">
                                    Margen
                                </label>
                                <div class="px-2.5 py-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-300 dark:border-emerald-800 text-xs font-bold text-emerald-700 dark:text-emerald-400 text-center">
                                    <span x-text="calcularMargen() + '%'">0%</span>
                                </div>
                            </div>
                        </div>

                        <!-- Stock Mínimo y Ubicación -->
                        <div class="grid grid-cols-2 gap-2.5">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                    Stock Mínimo de Alerta
                                </label>
                                <input type="number" 
                                       min="0" 
                                       name="stock_minimo" 
                                       x-model="formData.stock_minimo" 
                                       class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                            </div>

                            <div>
                                <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                    Ubicación / Anaquel
                                </label>
                                <input type="text" 
                                       name="ubicacion" 
                                       x-model="formData.ubicacion" 
                                       placeholder="Ej. Estante A-3" 
                                       class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                            </div>
                        </div>

                        <!-- Presentaciones Comerciales -->
                        <div class="pt-2 border-t border-slate-200/60 dark:border-slate-700/60 space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-[11px] font-bold text-slate-700 dark:text-slate-300">Presentaciones (Fraccionamiento):</span>
                                <div class="flex items-center gap-1">
                                    <button type="button" @click="agregarPresentacion('Blíster', 10)" class="px-1.5 py-0.5 rounded bg-emerald-100 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-300 border border-emerald-300 text-[10px] font-semibold hover:bg-emerald-200 cursor-pointer">+ Blíster 10</button>
                                    <button type="button" @click="agregarPresentacion('Blíster', 20)" class="px-1.5 py-0.5 rounded bg-emerald-100 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-300 border border-emerald-300 text-[10px] font-semibold hover:bg-emerald-200 cursor-pointer">+ Blíster 20</button>
                                    <button type="button" @click="agregarPresentacion('Caja', 30)" class="px-1.5 py-0.5 rounded bg-blue-100 dark:bg-blue-950 text-blue-800 dark:text-blue-300 border border-blue-300 text-[10px] font-semibold hover:bg-blue-200 cursor-pointer">+ Caja 30</button>
                                    <button type="button" @click="agregarPresentacion('Caja', 100)" class="px-1.5 py-0.5 rounded bg-blue-100 dark:bg-blue-950 text-blue-800 dark:text-blue-300 border border-blue-300 text-[10px] font-semibold hover:bg-blue-200 cursor-pointer">+ Caja 100</button>
                                    <button type="button" @click="agregarPresentacion('Personalizado', 1)" class="px-1.5 py-0.5 rounded bg-slate-200 dark:bg-slate-700 text-slate-800 dark:text-slate-200 border border-slate-300 text-[10px] font-semibold hover:bg-slate-300 cursor-pointer">+ Otra</button>
                                </div>
                            </div>

                            <div class="space-y-1.5 max-h-36 overflow-y-auto pr-1">
                                <template x-for="(pres, idx) in presentaciones" :key="idx">
                                    <div class="p-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 flex items-center justify-between gap-2 text-xs">
                                        <template x-if="pres.id"><input type="hidden" :name="'presentaciones[' + idx + '][id]'" :value="pres.id"></template>
                                        <input type="hidden" :name="'presentaciones[' + idx + '][es_unidad_base]'" :value="pres.es_unidad_base ? 1 : 0">
                                        <input type="hidden" :name="'presentaciones[' + idx + '][precio_compra]'" :value="pres.es_unidad_base ? formData.precio_compra : pres.precio_compra">
                                        <div class="flex-1 min-w-0">
                                            <input type="text" :name="'presentaciones[' + idx + '][nombre]'" x-model="pres.nombre" required placeholder="Nombre presentación" class="w-full text-xs font-semibold bg-transparent border-0 p-0 focus:ring-0 text-slate-900 dark:text-slate-100">
                                            <div class="flex flex-wrap items-center gap-2 text-[11px] text-slate-600 dark:text-slate-400 mt-0.5">
                                                <span>Factor: <input type="number" min="1" :name="'presentaciones[' + idx + '][unidades_por_presentacion]'" x-model="pres.unidades_por_presentacion" :readonly="!!pres.es_unidad_base" :class="pres.es_unidad_base ? 'bg-slate-200 dark:bg-slate-700 cursor-not-allowed opacity-75' : 'bg-white dark:bg-slate-800 focus:ring-1 focus:ring-emerald-500'" class="w-14 text-center font-bold rounded px-1 py-0.5 border border-slate-300 dark:border-slate-600 text-xs"> unid.</span>
                                                <span>P. Venta: S/ <input type="number" step="0.01" :name="'presentaciones[' + idx + '][precio_venta]'" x-model="pres.es_unidad_base ? formData.precio_venta : pres.precio_venta" required class="w-16 font-mono font-bold bg-slate-100 dark:bg-slate-700 rounded px-1 py-0.5 border border-slate-300 dark:border-slate-600 text-xs text-emerald-700 dark:text-emerald-400"></span>
                                                <span>Barra: <input type="text" :name="'presentaciones[' + idx + '][codigo_barras]'" x-model="pres.codigo_barras" placeholder="EAN / Barra" class="w-24 font-mono bg-slate-100 dark:bg-slate-700 rounded px-1 py-0.5 border border-slate-300 dark:border-slate-600 text-xs text-slate-800 dark:text-slate-200"></span>
                                            </div>
                                        </div>
                                        <template x-if="!pres.es_unidad_base">
                                            <button type="button" @click="eliminarPresentacion(idx)" class="text-rose-600 hover:text-rose-800 p-1 cursor-pointer">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Fotografía -->
                        <div class="pt-2 border-t border-slate-200/60 dark:border-slate-700/60">
                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Fotografía del Producto (Actual / Reemplazar)
                            </label>
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-lg border border-dashed border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 flex items-center justify-center shrink-0 overflow-hidden">
                                    <template x-if="imgPreview">
                                        <img :src="imgPreview" class="w-full h-full object-contain">
                                    </template>
                                    <template x-if="!imgPreview">
                                        @if($producto->imagen)
                                            <img src="{{ asset('storage/' . $producto->imagen) }}" class="w-full h-full object-cover">
                                        @else
                                            <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        @endif
                                    </template>
                                </div>
                                <input type="file" 
                                       name="imagen" 
                                       accept="image/*"
                                       @change="previewFile"
                                       class="w-full text-xs text-slate-600 dark:text-slate-400 file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border file:border-emerald-300 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-800 hover:file:bg-emerald-100 dark:file:bg-slate-800 dark:file:text-emerald-400">
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Footer Compacto -->
                <div class="pt-2 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between text-xs text-slate-500">
                    <span class="text-[11px]">Los datos se sincronizan automáticamente en borrador temporal.</span>
                    <button type="submit" 
                            class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs transition flex items-center space-x-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Guardar Cambios</span>
                    </button>
                </div>
            </div>
        </template>

        <!-- ========================================== -->
        <!-- MODO MODERNO (TARJETAS ESPACIOSAS)        -->
        <!-- ========================================== -->
        <template x-if="formLayout === 'modern'">
            <div class="space-y-5 animate-fadeIn">
                <!-- Tarjeta 1: Información Básica del Fármaco -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-300 dark:border-slate-800 shadow-md overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-slate-200 dark:border-slate-800 bg-slate-100/70 dark:bg-slate-800/60 flex items-center space-x-2">
                        <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200">
                            Datos Principales y Fórmula
                        </h2>
                    </div>

                    <div class="p-5 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                            <!-- Nombre Comercial -->
                            <div class="md:col-span-8">
                                <label for="nombre_mod" class="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-1.5">
                                    Nombre Comercial del Medicamento <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" 
                                       name="nombre" 
                                       id="nombre_mod" 
                                       x-model="formData.nombre"
                                       required 
                                       placeholder="Ej: Panadol Antigripal NF, Amoxil, Ibuprofeno..." 
                                       class="w-full px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white font-semibold placeholder-slate-400 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition shadow-2xs @error('nombre') border-rose-500 @enderror">
                                @error('nombre')
                                <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Código de Barras -->
                            <div class="md:col-span-4">
                                <label for="codigo_barra_mod" class="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-1.5">
                                    Código de Barras / EAN
                                </label>
                                <input type="text" 
                                       name="codigo_barra" 
                                       id="codigo_barra_mod" 
                                       x-model="formData.codigo_barra"
                                       placeholder="7751234567890" 
                                       class="w-full font-mono px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition shadow-2xs @error('codigo_barra') border-rose-500 @enderror">
                                @error('codigo_barra')
                                <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Principio Activo -->
                            <div class="md:col-span-5">
                                <label for="principio_activo_mod" class="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-1.5">
                                    Principio Activo (DCI)
                                </label>
                                <input type="text" 
                                       name="principio_activo" 
                                       id="principio_activo_mod" 
                                       x-model="formData.principio_activo"
                                       placeholder="Ej: Paracetamol, Amoxicilina, Loratadina..." 
                                       class="w-full px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition shadow-2xs @error('principio_activo') border-rose-500 @enderror">
                                @error('principio_activo')
                                <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Concentración -->
                            <div class="md:col-span-3">
                                <label for="concentracion_mod" class="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-1.5">
                                    Concentración
                                </label>
                                <input type="text" 
                                       name="concentracion" 
                                       id="concentracion_mod" 
                                       x-model="formData.concentracion"
                                       placeholder="Ej: 500 mg, 1 g, 20 mg/5ml" 
                                       class="w-full px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition shadow-2xs @error('concentracion') border-rose-500 @enderror">
                                @error('concentracion')
                                <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Forma Farmacéutica -->
                            <div class="md:col-span-4">
                                <label for="forma_farmaceutica_mod" class="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-1.5">
                                    Forma Farmacéutica
                                </label>
                                <input type="text" 
                                       name="forma_farmaceutica" 
                                       id="forma_farmaceutica_mod" 
                                       x-model="formData.forma_farmaceutica"
                                       placeholder="Ej: Tabletas recubiertas, Jarabe, Gotas..." 
                                       class="w-full px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition shadow-2xs @error('forma_farmaceutica') border-rose-500 @enderror">
                                @error('forma_farmaceutica')
                                <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Descripción / Acción Terapéutica -->
                            <div class="md:col-span-12">
                                <label for="descripcion_mod" class="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-1.5">
                                    Descripción / Indicaciones Terapéuticas
                                </label>
                                <textarea name="descripcion" 
                                          id="descripcion_mod" 
                                          rows="2" 
                                          x-model="formData.descripcion"
                                          placeholder="Indicaciones clínicas, posología básica o advertencias..." 
                                          class="w-full px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition shadow-2xs @error('descripcion') border-rose-500 @enderror"></textarea>
                                @error('descripcion')
                                <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tarjeta 2: Clasificación Farmacéutica y Régimen -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-300 dark:border-slate-800 shadow-md overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-slate-200 dark:border-slate-800 bg-slate-100/70 dark:bg-slate-800/60 flex items-center space-x-2">
                        <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200">
                            Clasificación, Laboratorio y Régimen Sanitario
                        </h2>
                    </div>

                    <div class="p-5 space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            <!-- Categoría -->
                            <div>
                                <label for="categoria_id_mod" class="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-1.5">
                                    Categoría <span class="text-rose-500">*</span>
                                </label>
                                <select name="categoria_id" 
                                        id="categoria_id_mod" 
                                        x-model="formData.categoria_id"
                                        required 
                                        class="w-full px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white font-semibold focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition shadow-2xs @error('categoria_id') border-rose-500 @enderror">
                                    <option value="">Selecciona categoría</option>
                                    @foreach($categorias as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Laboratorio -->
                            <div>
                                <label for="laboratorio_id_mod" class="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-1.5">
                                    Laboratorio Fabricante
                                </label>
                                <select name="laboratorio_id" 
                                        id="laboratorio_id_mod" 
                                        x-model="formData.laboratorio_id"
                                        class="w-full px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition shadow-2xs">
                                    <option value="">Sin especificar</option>
                                    @foreach($laboratorios as $lab)
                                    <option value="{{ $lab->id }}">{{ $lab->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Tipo de Control -->
                            <div>
                                <label for="tipo_control_mod" class="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-1.5">
                                    Régimen de Venta <span class="text-rose-500">*</span>
                                </label>
                                <select name="tipo_control" 
                                        id="tipo_control_mod" 
                                        x-model="formData.tipo_control"
                                        required 
                                        class="w-full px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white font-semibold focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition shadow-2xs">
                                    <option value="venta_libre">Venta Libre (OTC)</option>
                                    <option value="receta_medica">Receta Médica Simple</option>
                                    <option value="receta_retenida">Psicotrópico / Retenida</option>
                                </select>
                            </div>

                            <!-- Registro Sanitario -->
                            <div>
                                <label for="registro_sanitario_mod" class="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-1.5">
                                    Registro Sanitario
                                </label>
                                <input type="text" 
                                       name="registro_sanitario" 
                                       id="registro_sanitario_mod" 
                                       x-model="formData.registro_sanitario"
                                       placeholder="EE-12345 / DIGEMID" 
                                       class="w-full px-3.5 py-2 font-mono bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition shadow-2xs">
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-6 pt-2 border-t border-slate-200 dark:border-slate-800">
                            <label class="inline-flex items-center space-x-2 cursor-pointer select-none text-xs text-slate-800 dark:text-slate-200">
                                <input type="checkbox" 
                                       name="requiere_receta" 
                                       x-model="formData.requiere_receta"
                                       value="1" 
                                       class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 w-4 h-4">
                                <span class="font-bold">Exigir validación de Receta Médica en el POS de Ventas</span>
                            </label>

                            <label class="inline-flex items-center space-x-2 cursor-pointer select-none text-xs text-slate-800 dark:text-slate-200">
                                <input type="checkbox" 
                                       name="activo" 
                                       x-model="formData.activo"
                                       value="1" 
                                       class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 w-4 h-4">
                                <span class="font-bold text-emerald-700 dark:text-emerald-400">Producto Activo en el Catálogo</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Tarjeta 3: Precios e Inventario Base -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-300 dark:border-slate-800 shadow-md overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-slate-200 dark:border-slate-800 bg-slate-100/70 dark:bg-slate-800/60 flex items-center space-x-2">
                        <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200">
                            Precios de Compra / Venta e Inventario Base
                        </h2>
                    </div>

                    <div class="p-5 space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            <!-- Precio de Compra -->
                            <div>
                                <label for="precio_compra_mod" class="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-1.5">
                                    Precio Compra Base (S/)
                                </label>
                                <input type="number" 
                                       step="0.01" 
                                       min="0" 
                                       name="precio_compra" 
                                       id="precio_compra_mod" 
                                       x-model="formData.precio_compra" 
                                       placeholder="0.00" 
                                       class="w-full font-mono px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition shadow-2xs">
                            </div>

                            <!-- Precio de Venta -->
                            <div>
                                <label for="precio_venta_mod" class="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-1.5">
                                    Precio Venta Unitario (S/) <span class="text-rose-500">*</span>
                                </label>
                                <input type="number" 
                                       step="0.01" 
                                       min="0" 
                                       name="precio_venta" 
                                       id="precio_venta_mod" 
                                       x-model="formData.precio_venta" 
                                       required 
                                       placeholder="0.00" 
                                       class="w-full font-mono font-extrabold px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition shadow-2xs @error('precio_venta') border-rose-500 @enderror">
                                @error('precio_venta')
                                <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Margen Calculado -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                                    Margen Comercial
                                </label>
                                <div class="px-3.5 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-xs font-extrabold text-slate-800 dark:text-slate-200 flex items-center justify-between shadow-2xs">
                                    <span>Estimado:</span>
                                    <span class="text-emerald-700 dark:text-emerald-400" x-text="calcularMargen() + '%'"></span>
                                </div>
                            </div>

                            <!-- Stock Mínimo -->
                            <div>
                                <label for="stock_minimo_mod" class="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-1.5">
                                    Stock Mínimo de Alerta
                                </label>
                                <input type="number" 
                                       min="0" 
                                       name="stock_minimo" 
                                       id="stock_minimo_mod" 
                                       x-model="formData.stock_minimo" 
                                       class="w-full px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition shadow-2xs">
                            </div>
                        </div>

                        <!-- Ubicación Física -->
                        <div>
                            <label for="ubicacion_mod" class="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-1.5">
                                Ubicación Física en Farmacia (Estantería / Gaveta)
                            </label>
                            <input type="text" 
                                   name="ubicacion" 
                                   id="ubicacion_mod" 
                                   x-model="formData.ubicacion" 
                                   placeholder="Ej: Estante A-3, Cajón de Analgésicos..." 
                                   class="w-full px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition shadow-2xs">
                        </div>
                    </div>
                </div>

                <!-- Tarjeta 4: Presentaciones Comerciales y Fraccionamiento de Venta -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-300 dark:border-slate-800 shadow-md overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-slate-200 dark:border-slate-800 bg-slate-100/70 dark:bg-slate-800/60 flex flex-wrap items-center justify-between gap-2">
                        <div class="flex items-center space-x-2">
                            <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            <div>
                                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200">
                                    Presentaciones Comerciales y Fraccionamiento de Venta
                                </h2>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">Define las modalidades de venta (Unidad suelta, Blíster x10/x20, Caja x30/x100, Frasco).</p>
                            </div>
                        </div>

                        <!-- Botones de inserción rápida -->
                        <div class="flex items-center gap-1.5">
                            <button type="button" 
                                    @click="agregarPresentacion('Blíster', 10)"
                                    class="px-2.5 py-1 rounded-lg bg-emerald-100 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800 hover:bg-emerald-200 text-[11px] font-bold transition flex items-center space-x-1 cursor-pointer">
                                <span>+ Blíster (10u)</span>
                            </button>
                            <button type="button" 
                                    @click="agregarPresentacion('Blíster', 20)"
                                    class="px-2.5 py-1 rounded-lg bg-emerald-100 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800 hover:bg-emerald-200 text-[11px] font-bold transition flex items-center space-x-1 cursor-pointer">
                                <span>+ Blíster (20u)</span>
                            </button>
                            <button type="button" 
                                    @click="agregarPresentacion('Caja', 30)"
                                    class="px-2.5 py-1 rounded-lg bg-blue-100 dark:bg-blue-950/40 text-blue-800 dark:text-blue-300 border border-blue-300 dark:border-blue-800 hover:bg-blue-200 text-[11px] font-bold transition flex items-center space-x-1 cursor-pointer">
                                <span>+ Caja (30u)</span>
                            </button>
                            <button type="button" 
                                    @click="agregarPresentacion('Caja', 100)"
                                    class="px-2.5 py-1 rounded-lg bg-blue-100 dark:bg-blue-950/40 text-blue-800 dark:text-blue-300 border border-blue-300 dark:border-blue-800 hover:bg-blue-200 text-[11px] font-bold transition flex items-center space-x-1 cursor-pointer">
                                <span>+ Caja (100u)</span>
                            </button>
                            <button type="button" 
                                    @click="agregarPresentacion('Personalizado', 1)"
                                    class="px-2.5 py-1 rounded-lg bg-slate-200 dark:bg-slate-800 text-slate-800 dark:text-slate-200 border border-slate-300 dark:border-slate-700 hover:bg-slate-300 text-[11px] font-bold transition cursor-pointer">
                                <span>+ Otra</span>
                            </button>
                        </div>
                    </div>

                    <div class="p-4 overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="border-b border-slate-300 dark:border-slate-800 text-[11px] font-extrabold text-slate-700 dark:text-slate-300 uppercase">
                                    <th class="py-2 px-3">Presentación / Nombre</th>
                                    <th class="py-2 px-3 text-center w-28">Factor (Unidades)</th>
                                    <th class="py-2 px-3 text-right w-32">P. Compra (S/)</th>
                                    <th class="py-2 px-3 text-right w-32">P. Venta (S/)</th>
                                    <th class="py-2 px-3 w-40">Código Barras</th>
                                    <th class="py-2 px-3 text-center w-16">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-800/60">
                                <template x-for="(pres, idx) in presentaciones" :key="idx">
                                    <tr class="hover:bg-slate-100/60 dark:hover:bg-slate-800/30 transition">
                                        <td class="py-2 px-3">
                                            <input type="hidden" :name="'presentaciones[' + idx + '][es_unidad_base]'" :value="pres.es_unidad_base ? 1 : 0">
                                            <div class="flex items-center space-x-2">
                                                <template x-if="pres.es_unidad_base">
                                                    <span class="px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 text-[10px] font-extrabold shrink-0 border border-emerald-300">BASE (x1)</span>
                                                </template>
                                                <input type="text" 
                                                       :name="'presentaciones[' + idx + '][nombre]'" 
                                                       x-model="pres.nombre" 
                                                       required
                                                       placeholder="Ej: Blíster (10 unidades)" 
                                                       class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white font-semibold focus:ring-1 focus:ring-emerald-500 shadow-2xs">
                                            </div>
                                        </td>
                                        <td class="py-2 px-3 text-center">
                                            <input type="number" 
                                                   :name="'presentaciones[' + idx + '][unidades_por_presentacion]'" 
                                                   x-model="pres.unidades_por_presentacion" 
                                                   :readonly="!!pres.es_unidad_base"
                                                   min="1" 
                                                   required
                                                   class="w-20 text-center font-mono font-extrabold px-2 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500 shadow-2xs">
                                        </td>
                                        <td class="py-2 px-3 text-right">
                                            <input type="number" 
                                                   step="0.01" 
                                                   min="0" 
                                                   :name="'presentaciones[' + idx + '][precio_compra]'" 
                                                   x-model="pres.es_unidad_base ? formData.precio_compra : pres.precio_compra" 
                                                   placeholder="0.00" 
                                                   class="w-full text-right font-mono px-2 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500 shadow-2xs">
                                        </td>
                                        <td class="py-2 px-3 text-right">
                                            <input type="number" 
                                                   step="0.01" 
                                                   min="0" 
                                                   :name="'presentaciones[' + idx + '][precio_venta]'" 
                                                   x-model="pres.es_unidad_base ? formData.precio_venta : pres.precio_venta" 
                                                   required
                                                   placeholder="0.00" 
                                                   class="w-full text-right font-mono font-extrabold px-2 py-1.5 bg-white dark:bg-slate-800 border border-emerald-400 dark:border-emerald-600 rounded-lg text-xs text-emerald-800 dark:text-emerald-300 focus:ring-1 focus:ring-emerald-500 shadow-2xs">
                                        </td>
                                        <td class="py-2 px-3">
                                            <input type="text" 
                                                   :name="'presentaciones[' + idx + '][codigo_barras]'" 
                                                   x-model="pres.codigo_barras" 
                                                   placeholder="EAN / Barra" 
                                                   class="w-full font-mono px-2 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500 shadow-2xs">
                                        </td>
                                        <td class="py-2 px-3 text-center">
                                            <template x-if="!pres.es_unidad_base">
                                                <button type="button" 
                                                        @click="eliminarPresentacion(idx)" 
                                                        class="p-1.5 rounded-lg text-rose-600 hover:bg-rose-100 dark:hover:bg-rose-950/40 transition cursor-pointer"
                                                        title="Eliminar presentación">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </template>
                                            <template x-if="pres.es_unidad_base">
                                                <span class="text-[10px] text-slate-400 font-bold">-</span>
                                            </template>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tarjeta 5: Fotografía del Medicamento -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-300 dark:border-slate-800 shadow-md overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-slate-200 dark:border-slate-800 bg-slate-100/70 dark:bg-slate-800/60 flex items-center space-x-2">
                        <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200">
                            Fotografía del Medicamento
                        </h2>
                    </div>

                    <div class="p-5">
                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-5 items-center">
                            <div class="sm:col-span-8">
                                <label for="imagen_mod" class="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-1.5">
                                    Seleccionar archivo (JPG, PNG, WebP máx. 2MB)
                                </label>
                                <input type="file" 
                                       name="imagen" 
                                       id="imagen_mod" 
                                       accept="image/*"
                                       @change="previewFile"
                                       class="w-full text-xs text-slate-600 dark:text-slate-400 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border file:border-emerald-300 file:text-xs file:font-bold file:bg-emerald-50 file:text-emerald-800 hover:file:bg-emerald-100 dark:file:bg-slate-800 dark:file:text-emerald-400">
                                <p class="text-[11px] text-slate-500 mt-1">La imagen se mostrará en la vista en cuadrícula del catálogo y en el terminal POS.</p>
                                @error('imagen')
                                <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="sm:col-span-4 flex justify-center">
                                <div class="w-28 h-28 rounded-2xl border-2 border-dashed border-slate-400 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 flex items-center justify-center overflow-hidden shadow-2xs">
                                    <template x-if="imgPreview">
                                        <img :src="imgPreview" class="w-full h-full object-contain">
                                    </template>
                                    <template x-if="!imgPreview">
                                        @if($producto->imagen)
                                            <img src="{{ asset('storage/' . $producto->imagen) }}" class="w-full h-full object-cover">
                                        @else
                                            <span class="text-xs text-slate-400 text-center px-2">Sin imagen</span>
                                        @endif
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sticky Action Footer -->
                <div class="sticky bottom-4 z-10 bg-white/95 dark:bg-slate-900/95 backdrop-blur-md p-4 rounded-2xl border border-slate-300 dark:border-slate-800 shadow-xl flex items-center justify-between">
                    <a href="{{ route('productos.index') }}" 
                       class="px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-xs font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition shadow-2xs">
                        Cancelar
                    </a>

                    <button type="submit" 
                            class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-bold shadow-md transition flex items-center space-x-1.5 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Guardar Cambios</span>
                    </button>
                </div>
            </div>
        </template>
    </form>
</div>
@endsection
