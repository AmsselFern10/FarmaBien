@extends('layouts.app')

@section('title', 'Registrar Compra')

@section('header')
    Registrar Compra
@endsection

@section('page-actions')
    <div>
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white">
            Nueva Compra de Inventario
        </h2>
        <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
            Registra el ingreso de productos al inventario
        </p>
    </div>
    <div class="flex gap-3">
        <button type="button"
                onclick="window.dispatchEvent(new CustomEvent('compras-limpiar'))"
                class="inline-flex items-center px-4 py-2 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300 font-semibold rounded-lg hover:bg-rose-100 dark:hover:bg-rose-900/30 transition-colors duration-200 shadow-sm">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            Limpiar compra
        </button>
        
        <a href="{{ route('compras.index') }}"
           class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-slate-700 dark:text-slate-300 font-semibold rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200 shadow-sm">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Volver
        </a>
    </div>
@endsection

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-1 lg:px-3 py-0 pb-2">

    {{-- Errores --}}
    @if ($errors->any())
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-4 text-red-700">
            <p class="font-bold mb-2">Hay errores en el formulario:</p>
            <ul class="list-disc ml-5 text-sm space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="formCompra" method="POST" action="{{ route('compras.store') }}" class="space-y-4 -mt-2">
        @csrf

        
<div class="space-y-4">

            {{-- Productos (ancho completo) --}}
{{-- Productos --}}
                        <div class="space-y-4">
            
                            {{-- Productos --}}
                            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-emerald-50 to-white dark:from-gray-800 dark:to-gray-800/50">
                                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                                        <div class="flex items-center space-x-3">
                                            <div class="p-2 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg">
                                                <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Productos a Comprar</h3>
                                                <p class="text-sm text-slate-500 dark:text-slate-400">Agrega productos y controla lotes/vencimientos</p>
                                            </div>
                                        </div>
            
                                        <div class="flex flex-wrap items-center gap-2">
                                           
            
                                            <div class="inline-flex rounded-lg overflow-hidden border border-gray-300 dark:border-gray-600 shadow-sm">
                                                <button type="button" onclick="cambiarVista('tabla')" id="btnVistaTabla"
                                                        class="px-4 py-2 bg-blue-600 text-white font-semibold">
                                                    Tabla
                                                </button>
                                                <button type="button" onclick="cambiarVista('formulario')" id="btnVistaFormulario"
                                                        class="px-4 py-2 bg-white dark:bg-gray-700 text-slate-700 dark:text-slate-200 font-semibold">
                                                    Cards
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
            
                                <div class="p-4 space-y-4">
            
                                    {{-- Barra de escaneo compacta --}}
                                                {{-- Barra de escaneo compacta --}}
<div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800/50 p-3">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-3 items-end">
        
        {{-- Input: Ahora ocupa 7 columnas para dejar espacio a los botones --}}
        <div class="lg:col-span-7">
            <label for="barcodeInput" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">
                Escanear / escribir código de barras
            </label>
            <input id="barcodeInput" type="text" inputmode="numeric" autocomplete="off"
                   class="w-full px-3 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                   placeholder="Clic aquí y escanea…">
        </div>

        {{-- Botón Agregar: Ocupa 2.5 columnas aprox --}}
        <div class="lg:col-span-2.5 flex">
            <button type="button" id="btnAgregarBarcode"
                    class="inline-flex items-center justify-center w-full px-4 py-2.5 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white font-semibold rounded-lg shadow-sm transition-all duration-200 hover:shadow-md disabled:opacity-50 disabled:cursor-not-allowed text-sm">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Agregar
            </button>
        </div>

        {{-- Botón Catálogo: Ocupa 2.5 columnas aprox --}}
        <div class="lg:col-span-2.5 flex">
            <button type="button" onclick="abrirModalProductos()"
                    class="inline-flex items-center justify-center w-full px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white rounded-lg font-semibold shadow-sm text-sm transition-colors">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
                </svg>
                Catálogo
            </button>
        </div>

    </div>
</div>
            

                                        <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">ENTER también agrega si tu escáner lo envía.</p>
{{-- Preview (compacto) --}}
                                        <div id="barcodePreview" class="mt-3 hidden">
                                            <div class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800">
                                                <div class="w-12 h-12 rounded-lg overflow-hidden bg-white dark:bg-gray-700 border border-slate-200 dark:border-gray-600 flex items-center justify-center">
                                                    <img id="barcodePreviewImg" src="" alt="Producto" class="w-full h-full object-cover hidden">
                                                    <svg id="barcodePreviewFallback" class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 11h18M7 15h10M7 19h10"/>
                                                    </svg>
                                                </div>
            
                                                <div class="min-w-0 flex-1">
                                                    <p id="barcodePreviewNombre" class="font-semibold text-slate-900 dark:text-white truncate">—</p>
                                                    <p id="barcodePreviewExtra" class="text-xs text-slate-600 dark:text-slate-400 truncate">—</p>
                                                </div>
            
                                                <span id="barcodePreviewBadge" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-slate-200 text-slate-700 dark:bg-gray-700 dark:text-slate-200">
                                                    Esperando…
                                                </span>
                                            </div>
                                        </div>
                                    </div>
            
                                    {{-- Vista Tabla --}}
                                    <div id="vistaTabla" class="vista-contenido">
                                        <div class="rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                                            <div class="max-h-[72vh] lg:max-h-[calc(100vh-280px)] overflow-auto">
                                                <table id="tablaProductos" class="min-w-[1600px] w-full text-sm table-fixed">
                                                    <thead class="bg-gray-50 dark:bg-gray-900/40">
                                                    <tr id="theadRowProductos" class="text-left text-slate-700 dark:text-slate-300">
                                                        <th class="px-2 py-3 w-10 text-center"></th>
                                                        <th data-col="1" class="px-3 py-3 relative select-none th-resizable min-w-[80px] w-[90px]">Producto<div class="col-resize-handle absolute top-0 right-0 h-full w-3 cursor-col-resize select-none z-20"></div></th>
                                                        <th data-col="2" class="px-3 py-3 relative select-none th-resizable min-w-[100px] w-[140px]">Presentación<div class="col-resize-handle absolute top-0 right-0 h-full w-3 cursor-col-resize select-none z-20"></div></th>
                                                        <th data-col="3" class="px-3 py-3 text-center relative select-none th-resizable min-w-[20px] w-[80px]">Unid/Pres<div class="col-resize-handle absolute top-0 right-0 h-full w-3 cursor-col-resize select-none z-20"></div></th>
                                                        <th data-col="4" class="px-3 py-3 text-center relative select-none th-resizable min-w-[20px] w-[100px]">Cant. pres.<div class="col-resize-handle absolute top-0 right-0 h-full w-3 cursor-col-resize select-none z-20"></div></th>
                                                        <th data-col="5" class="px-3 py-3 text-center relative select-none th-resizable min-w-[20px] w-[80px]">Total unid<div class="col-resize-handle absolute top-0 right-0 h-full w-3 cursor-col-resize select-none z-20"></div></th>
                                                        <th data-col="6" class="px-3 py-3 relative select-none th-resizable min-w-[50px] w-[80px]">Lote<div class="col-resize-handle absolute top-0 right-0 h-full w-3 cursor-col-resize select-none z-20"></div></th>
                                                        <th data-col="7" class="px-3 py-3 relative select-none th-resizable min-w-[110px] w-[120px]">Venc.<div class="col-resize-handle absolute top-0 right-0 h-full w-3 cursor-col-resize select-none z-20"></div></th>
                                                        <th data-col="8" class="px-3 py-3 text-right relative select-none th-resizable min-w-[110px] w-[120px]">Precio pres.<div class="col-resize-handle absolute top-0 right-0 h-full w-3 cursor-col-resize select-none z-20"></div></th>
                                                        <th data-col="9" class="px-3 py-3 text-center relative select-none th-resizable min-w-[90px] w-[110px] descuento-prod-col">Desc. %<div class="col-resize-handle absolute top-0 right-0 h-full w-3 cursor-col-resize select-none z-20"></div></th>
                                                        <th data-col="10" class="px-3 py-3 text-right relative select-none th-resizable min-w-[90px] w-[110px]">Subtotal neto<div class="col-resize-handle absolute top-0 right-0 h-full w-3 cursor-col-resize select-none z-20"></div></th>
                                                        <th class="px-3 py-3 w-16 text-center"></th>
                                                    </tr>
                                                    </thead>
                                                    <tbody id="detallesTabla" class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800"></tbody>
                                                </table>
                                            </div>
                                        </div>
            
                                        <div id="avisoSinProductos" class="mt-3 text-sm text-slate-600 dark:text-slate-400">
                                            Aún no has agregado productos.
                                        </div>
                                    </div>
            
                                    {{-- Vista Cards --}}
                                    <div id="vistaFormulario" class="vista-contenido hidden">
                                        <div class="max-h-[72vh] lg:max-h-[calc(100vh-280px)] overflow-auto pr-1">
                                            <div id="detallesFormulario" class="grid grid-cols-1 md:grid-cols-2 gap-4"></div>
                                        </div>
            
                                        <div id="avisoSinProductosCards" class="mt-3 text-sm text-slate-600 dark:text-slate-400">
                                            Aún no has agregado productos.
                                        </div>
                                    </div>
            
                                </div>
                            </div>
                        </div>
            

{{-- Datos + Resumen (abajo, después de productos) --}}
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
    
    {{-- Columna Izquierda: Datos de compra --}}
    <div class="lg:col-span-8 h-full">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden h-full flex flex-col">
            <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-blue-50 to-white dark:from-gray-800 dark:to-gray-800/50">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 11h18M7 15h10M7 19h10"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Datos de la Compra</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Información general del registro</p>
                    </div>
                </div>
            </div>

            <div class="p-6 flex-grow">
                <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
                    <div class="sm:col-span-8">
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Proveedor</label>
                        <select id="proveedor_id" name="proveedor_id" required
                                class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                            <option value="">Seleccionar proveedor...</option>
                            @foreach($proveedores as $p)
                                <option value="{{ $p->id }}" @selected(old('proveedor_id') == $p->id)>{{ $p->nombre }}</option>
                            @endforeach
                        </select>
                        <button type="button" id="btnCrearProveedor"
                                class="mt-2 text-sm font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 inline-flex items-center">
                            + Crear proveedor
                        </button>
                    </div>

                    <div class="sm:col-span-4">
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Fecha</label>
                        <input type="date" name="fecha" value="{{ old('fecha', now()->toDateString()) }}"
                               class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                    </div>

                    {{-- Aumentamos rows a 5 para dar más altura a esta columna --}}
                    <div class="sm:col-span-12">
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Observaciones</label>
                        <textarea name="observaciones" rows="5"
                                  placeholder="Opcional: factura, condiciones, notas…"
                                  class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500">{{ old('observaciones') }}</textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Columna Derecha: Resumen Total --}}
    <div class="lg:col-span-4 h-full" x-data="{ open: false, accion: '{{ old('accion', 'guardar') }}' }">
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg p-6 space-y-4 h-full flex flex-col justify-between">
            
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-semibold text-slate-600 dark:text-slate-400">Subtotal bruto</span>
                    <span class="text-sm font-bold text-slate-900 dark:text-white">S/ <span id="subtotalDisplay">0.00</span></span>
                </div>

                <div class="flex items-center justify-between gap-3">
                    <label for="descuento" class="text-sm font-semibold text-slate-600 dark:text-slate-400">Descuento (%)</label>
                    <div class="flex items-center gap-2">
                        <input type="number" step="0.01" min="0" max="100" name="descuento" id="descuento" value="{{ old('descuento', 0) }}"
                               class="w-24 px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 text-right">
                        <span class="text-sm font-semibold text-slate-500 dark:text-slate-400">%</span>
                    </div>
                </div>

                {{-- Campos restaurados --}}
                <div class="flex items-center justify-between gap-3">
                    <span class="text-xs text-slate-500 dark:text-slate-400">Desc. productos</span>
                    <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">S/ <span id="descuentoLineasDisplay">0.00</span></span>
                </div>

                <div class="flex items-center justify-between gap-3 -mt-1">
                    <span class="text-xs text-slate-500 dark:text-slate-400">Monto descuento (total)</span>
                    <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">S/ <span id="descuentoMontoDisplay">0.00</span></span>
                </div>

                <div class="pt-3 border-t-2 border-dashed border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <span class="text-base font-bold text-slate-800 dark:text-slate-200">TOTAL</span>
                    <span class="text-2xl font-black text-blue-600 dark:text-blue-400">
                        S/ <span id="totalDisplay">{{ number_format(old('total', 0), 2) }}</span>
                    </span>
                </div>
                <input type="hidden" name="total" id="total" value="{{ old('total', 0) }}">
                <input type="hidden" name="accion" :value="accion">
            </div>

            {{-- Footer y Botones (Estilo Ventas) --}}
            <div class="pt-4 border-t border-gray-200 dark:border-gray-700 space-y-2.5">
                
                {{-- Checkbox --}}
                <label class="inline-flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300 cursor-pointer">
                    <input type="checkbox" name="mantener_en_pantalla" value="1" @checked(old('mantener_en_pantalla'))
                           class="w-4 h-4 rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500">
                    Mantenerme aquí
                </label>

                <div class="flex flex-col gap-3">
                    {{-- GRUPO DE BOTÓN DIVIDIDO (Dropup) --}}
                    <div class="relative flex w-full">
                        {{-- Botón Principal --}}
                        <button type="button" onclick="submitCompra()"
                                class="flex-1 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-l-xl font-bold text-base shadow-lg active:scale-[0.98] transition-transform uppercase border-r border-blue-500">
                            <span x-text="accion === 'facturar' ? 'Guardar y Facturar' : 'GUARDAR COMPRA'"></span>
                        </button>

                        {{-- Flecha Dropup --}}
                        <button type="button" @click="open = !open" @click.away="open = false"
                                class="px-3 bg-blue-600 hover:bg-blue-700 text-white rounded-r-xl shadow-lg active:scale-[0.98] transition-transform">
                            <svg class="w-5 h-5 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                            </svg>
                        </button>

                        {{-- Menú que abre hacia arriba --}}
                        <div x-show="open" x-transition:enter="transition ease-out duration-100" 
                             x-transition:enter-start="opacity-0 transform scale-95" 
                             x-transition:enter-end="opacity-100 transform scale-100"
                             class="absolute bottom-full mb-2 right-0 w-full bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg shadow-2xl z-50 overflow-hidden">
                            <button type="button" @click="accion = 'guardar'; open = false" 
                                    class="w-full px-4 py-3 text-left text-sm font-semibold hover:bg-blue-50 dark:hover:bg-blue-900/30 text-slate-700 dark:text-slate-200 border-b border-gray-100 dark:border-gray-600 uppercase">
                                Solo Guardar Compra
                            </button>
                            <button type="button" @click="accion = 'facturar'; open = false" 
                                    class="w-full px-4 py-3 text-left text-sm font-semibold hover:bg-blue-50 dark:hover:bg-blue-900/30 text-slate-700 dark:text-slate-200 uppercase">
                                Guardar y Facturar
                            </button>
                        </div>
                    </div>

                    {{-- BOTÓN CANCELAR --}}
                    <a href="{{ route('compras.index') }}" 
                       class="px-4 py-2 text-center bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-slate-700 dark:text-slate-300 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-600 transition-colors">
                        Cancelar Operación
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
{{-- MODAL: advertencia precio en 0 --}}
<div id="modalPrecioCero" class="fixed inset-0 z-[90] hidden">
    <div class="absolute inset-0 bg-black/50" onclick="cerrarModalPrecioCero()"></div>
    <div class="relative mx-auto my-8 w-[95%] max-w-lg">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-amber-50 to-white dark:from-gray-800 dark:to-gray-800/50">
                <div class="flex items-start gap-3">
                    <div class="p-2 bg-amber-100 dark:bg-amber-900/30 rounded-lg">
                        <svg class="w-6 h-6 text-amber-700 dark:text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 4h.01M10.29 3.86l-8.02 13.9A2 2 0 004 21h16a2 2 0 001.73-3.24l-8.02-13.9a2 2 0 00-3.42 0z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Precio en 0 detectado</h3>
                        <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
                            Hay productos con <span class="font-semibold">Precio pres.</span> igual a <span class="font-semibold">0.00</span>. ¿Deseas continuar de todas formas?
                        </p>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4">
                <div class="text-sm text-slate-700 dark:text-slate-200 font-semibold mb-2">Productos afectados:</div>
                <ul id="listaPrecioCero" class="text-sm text-slate-600 dark:text-slate-300 list-disc ml-5 space-y-1 max-h-48 overflow-auto pr-1"></ul>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-3">
                    Recomendación: registra el precio de la <span class="font-semibold">presentación</span> (caja/blíster/frasco). El sistema calcula el unitario automáticamente.
                </p>
            </div>

            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-2 bg-white dark:bg-gray-800">
                <button type="button" onclick="cerrarModalPrecioCero()"
                        class="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-slate-700 dark:text-slate-200">
                    Revisar precios
                </button>
                <button type="button" onclick="confirmarContinuarPrecioCero()"
                        class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg font-semibold shadow-sm">
                    Sí, continuar
                </button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL PRODUCTOS --}}

<!-- Modal: Crear Proveedor (iframe) -->
<div id="modalProveedor" class="fixed inset-0 z-[80] hidden">
    <div class="absolute inset-0 bg-black/50" onclick="cerrarModalProveedor()"></div>
    <div class="relative mx-auto my-6 w-[95%] max-w-5xl">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-blue-50 to-white dark:from-gray-800 dark:to-gray-800/50 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Crear proveedor</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Registra un proveedor sin salir de compras</p>
                    </div>
                </div>
                <button type="button" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700" onclick="cerrarModalProveedor()">
                    <svg class="w-6 h-6 text-slate-600 dark:text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="px-6 py-5 bg-white dark:bg-gray-800">
                <div id="proveedorModalError" class="hidden mb-4 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700"></div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Nombre <span class="text-red-600">*</span></label>
                        <input id="nuevoProveedorNombre" type="text" autocomplete="off"
                               class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500"
                               placeholder="Ej: Farmacias XYZ" />
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">RUC</label>
                        <input id="nuevoProveedorRuc" type="text" autocomplete="off"
                               class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500"
                               placeholder="Opcional" />
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Teléfono</label>
                        <input id="nuevoProveedorTelefono" type="text" autocomplete="off"
                               class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500"
                               placeholder="Opcional" />
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Dirección</label>
                        <input id="nuevoProveedorDireccion" type="text" autocomplete="off"
                               class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500"
                               placeholder="Opcional" />
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Correo</label>
                        <input id="nuevoProveedorEmail" type="email" autocomplete="off"
                               class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500"
                               placeholder="Opcional" />
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Al guardar, el proveedor se agregará y quedará seleccionado.</p>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex items-center justify-end gap-3 bg-white dark:bg-gray-800">
                <button type="button" onclick="cerrarModalProveedor()"
                        class="px-5 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-slate-700 dark:text-slate-200">
                    Cancelar
                </button>
                <button type="button" id="btnGuardarProveedorModal" onclick="guardarNuevoProveedor()"
                        class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold shadow-sm">
                    Guardar proveedor
                </button>
            </div>
        </div>
    </div>
</div>

<div id="modalProductos" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-6xl w-full overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <div>
                <h3 class="text-xl font-bold text-slate-900 dark:text-white">Catálogo de Productos</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Busca por nombre, descripción o código de barras y selecciona varios productos.
                </p>
            </div>
            <button type="button" onclick="cerrarModalProductos()" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                <svg class="w-6 h-6 text-slate-600 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <div class="p-6 space-y-4">
            <div class="flex flex-col md:flex-row md:items-center gap-3">
                <div class="flex-1">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Buscar</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-3 flex items-center text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1010.5 18.5a7.5 7.5 0 006.15-3.85z"></path>
                            </svg>
                        </span>
                        <input id="modalBuscarProducto" type="text"
                               class="w-full pl-11 pr-4 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500"
                               placeholder="Escribe para buscar (nombre / descripción / código de barras)..."
                               oninput="filtrarCatalogoProductos(this.value)"
                               autocomplete="off">
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Tip: usa palabras clave (ej. “paracetamol”, “jarabe”, “7750...”).
                    </p>
                </div>

                <div class="md:text-right">
                    <div class="text-sm font-semibold text-slate-700 dark:text-slate-300">Seleccionados</div>
                    <div class="mt-1 inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-100 dark:bg-gray-700 text-slate-900 dark:text-white font-bold">
                        <span id="modalSeleccionadosCount">0</span>
                        <span class="text-sm font-semibold text-slate-600 dark:text-slate-300">producto(s)</span>
                    </div>
                </div>
            </div>

            <div id="modalProductosGrid"
                 class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 max-h-[68vh] overflow-auto pr-1">
                {{-- Renderizado por JS --}}
            </div>
        </div>

        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <button type="button" onclick="limpiarSeleccionCatalogo()"
                    class="inline-flex items-center justify-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-slate-700 dark:text-slate-200 hover:bg-gray-50 dark:hover:bg-gray-600">
                Limpiar selección
            </button>

            <div class="flex items-center justify-end gap-2">
                <button type="button" onclick="cerrarModalProductos()"
                        class="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-slate-700 dark:text-slate-200">
                    Cancelar
                </button>
                <button type="button" onclick="agregarSeleccionadosDesdeCatalogo()"
                        class="inline-flex items-center justify-center px-5 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-semibold shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
                        id="btnAgregarSeleccionados" disabled>
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Agregar seleccionados
                </button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL: NUEVA PRESENTACIÓN --}}
<div id="modalNuevaPresentacion" class="hidden fixed inset-0 bg-black/50 z-[60] flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-xl w-full overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-start justify-between">
            <div>
                <h3 class="text-xl font-bold text-slate-900 dark:text-white">Crear nueva presentación</h3>
                <p id="modalNuevaPresentacionSub" class="text-sm text-slate-500 dark:text-slate-400 mt-1">Producto:</p>
            </div>
            <button type="button" onclick="cerrarModalNuevaPresentacion()" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                <svg class="w-6 h-6 text-slate-600 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <div class="p-6 space-y-4">
            <div id="pres_error" class="hidden rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700"></div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Nombre</label>
                <input id="pres_nombre" type="text"
                       class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm"
                       placeholder="Ej: Caja, Blíster, Frasco">
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Nombre corto de la presentación.</p>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Descripción (opcional)</label>
                <input id="pres_descripcion" type="text"
                       class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm"
                       placeholder="Ej: Caja x 20 tabletas">
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Describe el contenido (ej. “x20 tabletas”, “120 ml”).</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Unidades por presentación</label>
                    <input id="pres_unidades" type="number" min="1"
                           class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm"
                           placeholder="Ej: 20">
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Cuántas unidades base contiene 1 presentación.</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Precio sugerido (opcional)</label>
                    <input id="pres_precio" type="number" step="0.01" min="0"
                           class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm"
                           placeholder="Ej: 35.50">
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Si lo pones, lo usaremos como sugerencia.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Código de barras (opcional)</label>
                    <input id="pres_codigo" type="text"
                           class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm"
                           placeholder="Ej: 7750123456789">
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Único. Déjalo vacío si no aplica.</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Orden</label>
                    <input id="pres_orden" type="number" min="0" value="0"
                           class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm"
                           placeholder="0">
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Orden de visualización en el select.</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <input id="pres_activo" type="checkbox" class="rounded border-gray-300" checked>
                <label for="pres_activo" class="text-sm text-slate-700 dark:text-slate-300">Activa para compras</label>
            </div>
        </div>

        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-2">
            <button type="button" onclick="cerrarModalNuevaPresentacion()"
                    class="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-slate-700 dark:text-slate-200">
                Cancelar
            </button>
            <button type="button" onclick="guardarNuevaPresentacion()"
                    class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-semibold">
                Guardar
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
@php
    $productosData = $productos->map(function($p){
        $img = $p->imagen ?? null;
        $imgUrl = null;
        if ($img) {
            $imgUrl = str_starts_with($img, 'http') ? $img : asset('storage/'.$img);
        }
        return [
            'id' => $p->id,
            'nombre' => $p->nombre,
            'precio_compra' => (float)($p->precio_compra ?? 0),
            'descripcion' => $p->descripcion ?? null,
            'codigo_barras' => $p->codigo_barras ?? ($p->codigo_barra ?? null),
            'codigo_barra'  => $p->codigo_barra  ?? ($p->codigo_barras ?? null),
            'imagen_url' => $imgUrl,
        ];
    })->values();
@endphp
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<style>
/* Column resize UX (mouse + touch) */
.col-resize-handle{touch-action:none;}
th.th-resizable{overflow:visible;}
/* Allow rows to grow naturally */
#vistaTabla td{vertical-align:top;}

/* ===== MIN WIDTHS + NO VERTICAL EXPAND (Producto) ===== */
#tablaProductos th[data-col="1"], #tablaProductos td[data-col="1"]{min-width:240px;}
#tablaProductos th[data-col="2"], #tablaProductos td[data-col="2"]{min-width:260px;}
#tablaProductos th[data-col="3"], #tablaProductos td[data-col="3"]{min-width:80px;}
#tablaProductos th[data-col="4"], #tablaProductos td[data-col="4"]{min-width:120px;}
#tablaProductos th[data-col="5"], #tablaProductos td[data-col="5"]{min-width:80px;}
#tablaProductos th[data-col="6"], #tablaProductos td[data-col="6"]{min-width:140px;}
#tablaProductos th[data-col="7"], #tablaProductos td[data-col="7"]{min-width:140px;}
#tablaProductos th[data-col="8"], #tablaProductos td[data-col="8"]{min-width:150px;}
#tablaProductos th[data-col="9"], #tablaProductos td[data-col="9"]{min-width:150px;}
#tablaProductos td[data-col="1"]{white-space:normal;word-break:break-word;}

// Sticky header dentro del scroll
#tablaProductos thead th{position:sticky;top:0;z-index:30;background:inherit;}
</style>
<script>
// ================================
//  Estado (para no perder productos al cambiar de vista)
// ================================
let vistaActual = 'tabla';
let contadorProductos = 0;
// indices activos en el orden actual
let indicesActivos = [];

function capturarIndicesDesdeDOM() {
    const cont = (vistaActual === 'tabla') ? document.getElementById('detallesTabla') : document.getElementById('detallesFormulario');
    if (!cont) return;
    const ids = Array.from(cont.querySelectorAll('[id^="producto_"]')).map(el => {
        const m = el.id.match(/producto_(\d+)/);
        return m ? parseInt(m[1], 10) : null;
    }).filter(v => v !== null);
    indicesActivos = ids;
}


// Guarda valores actuales (de la vista visible) para re-renderizar en la otra vista
const estadoDetalles = {}; // { index: { producto_id, presentacion_id, unidades, cantidad, lote, vence, precio, tipo_presentacion, nombre_producto } }

/** ================================
 *  BORRADOR (LocalStorage) - similar a Ventas
 *  ================================= */
const COMPRA_DRAFT_KEY = 'compras_draft_v1';
const COMPRA_VISTA_KEY = 'compras_vista';
let restaurandoDraft = false;
let draftTimer = null;

function saveCompraDraftDebounced() {
    clearTimeout(draftTimer);
    draftTimer = setTimeout(() => {
        saveCompraDraft();
    }, 250);
}

function saveCompraDraft() {
    try {
        // sincroniza estado actual desde el DOM
        capturarIndicesDesdeDOM();
        capturarEstadoActual();

        const proveedorId = document.getElementById('proveedor_id')?.value || document.querySelector('select[name="proveedor_id"]')?.value || '';
        const fecha = document.querySelector('input[name="fecha"]')?.value || '';
        const observaciones = document.querySelector('textarea[name="observaciones"]')?.value || '';
        const descuento = document.getElementById('descuento')?.value || '0';

        const accion = document.getElementById('accion_compra')?.value || 'guardar';
        const mantener = document.getElementById('mantener_en_pantalla')?.checked ? 1 : 0;

        const payload = {
            vistaActual,
            contadorProductos,
            indicesActivos,
            estadoDetalles,
            proveedor_id: proveedorId,
            fecha,
            observaciones,
            descuento,
            accion,
            mantener_en_pantalla: mantener,
        };

        localStorage.setItem(COMPRA_DRAFT_KEY, JSON.stringify(payload));
        localStorage.setItem(COMPRA_VISTA_KEY, vistaActual);
    } catch (e) {
        // silent
    }
}

function restoreCompraDraft() {
    try {
        const raw = localStorage.getItem(COMPRA_DRAFT_KEY);
        if (!raw) {
            // restaura vista si existe
            const v = localStorage.getItem(COMPRA_VISTA_KEY);
            if (v) {
                vistaActual = v;
                restaurandoDraft = true;
                cambiarVista(vistaActual);
                restaurandoDraft = false;
            }
            return;
        }
        const data = JSON.parse(raw);
        if (!data || typeof data !== 'object') return;

        // campos
        const selProv = document.getElementById('proveedor_id') || document.querySelector('select[name="proveedor_id"]');
        if (selProv && data.proveedor_id !== undefined) selProv.value = String(data.proveedor_id || '');
        const inpFecha = document.querySelector('input[name="fecha"]');
        if (inpFecha && data.fecha !== undefined) inpFecha.value = String(data.fecha || '');
        const txtObs = document.querySelector('textarea[name="observaciones"]');
        if (txtObs && data.observaciones !== undefined) txtObs.value = String(data.observaciones || '');
        const inpDesc = document.getElementById('descuento');
        if (inpDesc && data.descuento !== undefined) inpDesc.value = String(data.descuento ?? '0');

        // acción + mantenerme aquí
        const inpAccion = document.getElementById('accion_compra');
        if (inpAccion && data.accion !== undefined) inpAccion.value = String(data.accion || 'guardar');

        const chkMant = document.getElementById('mantener_en_pantalla');
        if (chkMant && data.mantener_en_pantalla !== undefined) chkMant.checked = !!Number(data.mantener_en_pantalla);

        try { syncAccionCompraUI(); } catch (e) {}

        // estado productos
        const v = data.vistaActual || localStorage.getItem(COMPRA_VISTA_KEY) || vistaActual;
        const savedIndices = Array.isArray(data.indicesActivos) ? data.indicesActivos : [];
        const savedEstado = data.estadoDetalles && typeof data.estadoDetalles === 'object' ? data.estadoDetalles : {};

        // limpia estado actual
        indicesActivos = [];
        Object.keys(estadoDetalles).forEach(k => delete estadoDetalles[k]);

        indicesActivos = savedIndices.map(x => Number(x)).filter(x => !Number.isNaN(x));
        Object.entries(savedEstado).forEach(([k, st]) => {
            estadoDetalles[k] = st;
        });

        // contador: al menos max+1
        const maxIdx = indicesActivos.length ? Math.max(...indicesActivos) : 0;
        contadorProductos = Math.max(Number(data.contadorProductos || 0) || 0, maxIdx + 1);

        // render
        restaurandoDraft = true;
        cambiarVista(v);
        restaurandoDraft = false;

        calcularTotalGeneral();
        actualizarAvisosVacio();
    } catch (e) {
        // silent
    }
}

function clearCompraDraft() {
    try {
        localStorage.removeItem(COMPRA_DRAFT_KEY);
    } catch (e) {}
}

function resetCompra(clearStorage = false) {
    // limpiar productos
    const tbody = document.getElementById('detallesTabla');
    if (tbody) tbody.innerHTML = '';
    const cards = document.getElementById('detallesFormulario');
    if (cards) cards.innerHTML = '';

    indicesActivos = [];
    Object.keys(estadoDetalles).forEach(k => delete estadoDetalles[k]);
    contadorProductos = 0;

    // limpiar campos
    const selProv = document.getElementById('proveedor_id') || document.querySelector('select[name="proveedor_id"]');
    if (selProv) selProv.value = '';
    const inpFecha = document.querySelector('input[name="fecha"]');
    if (inpFecha) inpFecha.value = new Date().toISOString().split('T')[0];
    const txtObs = document.querySelector('textarea[name="observaciones"]');
    if (txtObs) txtObs.value = '';
    const inpDesc = document.getElementById('descuento');
    if (inpDesc) inpDesc.value = '0.00';

    // barcode
    const barcodeInput = document.getElementById('barcodeInput');
    if (barcodeInput) barcodeInput.value = '';
    try { mostrarPreviewBarcode(null, ''); } catch (e) {}

    // acción + mantenerme aquí
    const inpAccion = document.getElementById('accion_compra');
    if (inpAccion) inpAccion.value = 'guardar';
    const chkMant = document.getElementById('mantener_en_pantalla');
    if (chkMant) chkMant.checked = false;
    try { syncAccionCompraUI(); } catch (e) {}

    calcularTotalGeneral();
    actualizarAvisosVacio();

    if (clearStorage) {
        clearCompraDraft();
    } else {
        saveCompraDraft();
    }
}

// Lee valores desde DOM (tabla o card) para un index
// IMPORTANTE: se “scopéa” al contenedor #producto_{index} para evitar tomar inputs de la vista oculta
// cuando existen dos vistas en el DOM (tabla + cards) durante el cambio.
function capturarEstadoIndex(index) {
    const row = document.getElementById(`producto_${index}`);
    if (!row) return;

    // Primero sincroniza hidden desde visibles
    sincronizarHiddenDesdeVisibles(index);

    const get = (sel) => row.querySelector(sel);

    const productoIdEl = get(`input[name="productos[${index}][producto_id]"]`);
    const productoSelectEl = get(`select[name="productos[${index}][producto_id]"]`);
    const presentacionIdEl = get(`input[name="productos[${index}][presentacion_id]"]`);
    const tipoPresEl = get(`input[name="productos[${index}][tipo_presentacion]"]`);
    const unidadesHiddenEl = get(`input[name="productos[${index}][unidades_por_presentacion]"]`);

    const cantidadEl = get(`.input-cantidad-${index}`);
    const loteEl = get(`.input-lote-${index}`);
    const venceEl = get(`.input-vence-${index}`);
    const precioEl = get(`.input-precio-${index}`);
    const descuentoEl = get(`.input-descuento-${index}`);

    estadoDetalles[index] = {
        producto_id: (productoIdEl ? productoIdEl.value : (productoSelectEl ? productoSelectEl.value : '')),
        presentacion_id: presentacionIdEl ? presentacionIdEl.value : '',
        tipo_presentacion: tipoPresEl ? tipoPresEl.value : '',
        unidades: unidadesHiddenEl ? unidadesHiddenEl.value : (get(`.input-unidades-${index}`)?.value || '1'),
        cantidad: cantidadEl ? cantidadEl.value : '1',
        lote: loteEl ? loteEl.value : '',
        vence: venceEl ? venceEl.value : '',
        precio: precioEl ? precioEl.value : '0',
        descuento: descuentoEl ? descuentoEl.value : '0'
    };
}

function capturarEstadoActual() {
    indicesActivos.forEach(i => capturarEstadoIndex(i));
}



// Copia inputs visibles -> hidden inputs (para submit y para re-render consistente)
function sincronizarHiddenDesdeVisibles(index) {
    const row = document.getElementById(`producto_${index}`);
    if (!row) return;

    const cantidad = row.querySelector(`.input-cantidad-${index}`);
    const lote = row.querySelector(`.input-lote-${index}`);
    const vence = row.querySelector(`.input-vence-${index}`);
    const precio = row.querySelector(`.input-precio-${index}`);
    const descuento = row.querySelector(`.input-descuento-${index}`);
    const unidadesInput = row.querySelector(`.input-unidades-${index}`);
    const selectPres = row.querySelector(`.select-presentacion-${index}`);

    const setHidden = (name, val) => {
        const el = row.querySelector(`input[name="productos[${index}][${name}]"]`);
        if (el) el.value = (val ?? '');
    };

    // Unidades / Presentación
    const unidades = unidadesInput ? (unidadesInput.value || '1') : '1';
    setHidden('unidades_por_presentacion', unidades);
    const hiddenUn = row.querySelector(`.hidden-unidades-${index}`);
    if (hiddenUn) hiddenUn.value = unidades;

    // Presentación
    if (selectPres) {
        setHidden('presentacion_id', selectPres.value || '');
    }

    // Cantidad
    if (cantidad) setHidden('cantidad_presentaciones', cantidad.value || '1');

    // Lote y vencimiento
    if (lote) setHidden('numero_lote', lote.value || '');
    if (vence) setHidden('fecha_vencimiento', vence.value || '');


// Guardamos precio_unitario REAL (por unidad base) = precioPres / unidades
if (precio) {
    const precioPres = parseFloat(precio.value || '0') || 0;
    const un = parseInt(unidades || '1', 10) || 1;
    const precioUnit = (un > 0) ? (precioPres / un) : 0;
    setHidden('precio_unitario', precioUnit.toFixed(2));
}
    // Descuento por producto (%)
    if (descuento) setHidden('descuento', descuento.value || '0');
}

// ================================
//  Normaliza data-col (anti-bug al mover/resize columnas)
//  Asegura que cada celda editable tenga data-col, para no perder inputs.
// ================================
function normalizarDataColsFila(tr) {
    if (!tr) return;
    // Producto
    const tdProducto = tr.querySelector('td[data-col="1"]') || (tr.querySelector('input[name*="[producto_id]"], select[name*="[producto_id]"]')?.closest('td'));
    if (tdProducto) tdProducto.dataset.col = '1';

    // Presentación (select)
    const tdPres = tr.querySelector('td[data-col="2"]') || (tr.querySelector('.select-presentacion')?.closest('td'));
    if (tdPres) tdPres.dataset.col = '2';

    // Unid/Pres
    const tdUnid = tr.querySelector('td[data-col="3"]') || (tr.querySelector('input[name*="[unidades_por_presentacion]"]')?.closest('td'));
    if (tdUnid) tdUnid.dataset.col = '3';

    // Cantidad
    const tdCant = tr.querySelector('td[data-col="4"]') || (tr.querySelector('input[name*="[cantidad_presentaciones]"]')?.closest('td'));
    if (tdCant) tdCant.dataset.col = '4';

    // Total unid (span o input readonly)
    const tdTotalU = tr.querySelector('td[data-col="5"]') || (tr.querySelector('[data-total-unid], .total-unid, .totalUnidDisplay')?.closest('td'));
    if (tdTotalU) tdTotalU.dataset.col = '5';

    // Lote
    const tdLote = tr.querySelector('td[data-col="6"]') || (tr.querySelector('textarea[name*="[numero_lote]"], input[name*="[numero_lote]"]')?.closest('td'));
    if (tdLote) tdLote.dataset.col = '6';

    // Vencimiento
    const tdVenc = tr.querySelector('td[data-col="7"]') || (tr.querySelector('input[name*="[fecha_vencimiento]"]')?.closest('td'));
    if (tdVenc) tdVenc.dataset.col = '7';

    // Precio pres.
    const tdPrecio = tr.querySelector('td[data-col="8"]') || (tr.querySelector('input[name*="[precio_unitario]"]')?.closest('td'));
    if (tdPrecio) tdPrecio.dataset.col = '8';

    // Descuento % (por producto)
    const tdDesc = tr.querySelector('td[data-col="9"]') || (tr.querySelector('input[name*="[descuento]"]')?.closest('td'));
    if (tdDesc) tdDesc.dataset.col = '9';

    // Subtotal neto
    const tdSub = tr.querySelector('td[data-col="10"]') || (tr.querySelector('[data-subtotal], .subtotalDisplay')?.closest('td'));
    if (tdSub) tdSub.dataset.col = '10';
}

function normalizarDataColsTabla() {
    const table = document.getElementById('tablaProductos');
    if (!table) return;
    table.querySelectorAll('tbody tr').forEach(tr => normalizarDataColsFila(tr));
}


// Re-renderiza la vista objetivo usando estadoDetalles (evita “se pierden”)
function renderVista(vista) {
    if (vista === 'tabla') {
        const tbody = document.getElementById('detallesTabla');
        tbody.innerHTML = '';
        indicesActivos.forEach(index => {
            const st = estadoDetalles[index] || {};
            const p = st.producto_id ? productosData.find(x => String(x.id) === String(st.producto_id)) : null;
            agregarProductoTabla(index, p, false, st);
        });
        iniciarSortableTabla();
    } else {
        const cont = document.getElementById('detallesFormulario');
        cont.innerHTML = '';
        indicesActivos.forEach(index => {
            const st = estadoDetalles[index] || {};
            const p = st.producto_id ? productosData.find(x => String(x.id) === String(st.producto_id)) : null;
            agregarProductoCard(index, p, false, st);
        });
        iniciarSortableCards();
    }

    indicesActivos.forEach(i => sincronizarHiddenDesdeVisibles(i));
    calcularTotalGeneral();

    // avisos vacíos
    const hay = indicesActivos.length > 0;
    document.getElementById('avisoSinProductos').classList.toggle('hidden', hay);
    document.getElementById('avisoSinProductosCards').classList.toggle('hidden', hay);
}

// ================================
//  Column resizing (Excel-like)
// ================================
function initResizableColumns() {
    const table = document.getElementById('tablaProductos');
    if (!table) return;
    const ths = table.querySelectorAll('th.th-resizable');
    ths.forEach(th => {
        const handle = th.querySelector('.col-resize-handle');
        if (!handle) return;

        let startX = 0;
        let startW = 0;

        const onDown = (e) => {
            e.preventDefault();
            e.stopPropagation();
            startX = (e.touches ? e.touches[0].clientX : e.clientX);
            startW = th.getBoundingClientRect().width;

            document.addEventListener('mousemove', onMove);
            document.addEventListener('mouseup', onUp);
            document.addEventListener('touchmove', onMove, { passive: false });
            document.addEventListener('touchend', onUp);
        };

        const onMove = (e) => {
            e.preventDefault();
            const x = (e.touches ? e.touches[0].clientX : e.clientX);
            const dx = x - startX;
            const newW = Math.max(80, startW + dx);
            th.style.width = newW + 'px';
            // aplicar al resto de celdas de esa columna
            normalizarDataColsTabla();
            const col = th.dataset.col;
            if (col) {
                table.querySelectorAll(`tbody td[data-col="${col}"]`).forEach(td => td.style.width = newW + 'px');
            }
        };

        const onUp = () => {
            document.removeEventListener('mousemove', onMove);
            document.removeEventListener('mouseup', onUp);
            document.removeEventListener('touchmove', onMove);
            document.removeEventListener('touchend', onUp);
        };

        handle.addEventListener('mousedown', onDown);
        handle.addEventListener('touchstart', onDown, { passive: false });
    });
}



// ================================
//  Column reordering (Excel-like)
// ================================
function initReorderableColumns() {
    const table = document.getElementById('tablaProductos');
    const headerRow = document.getElementById('theadRowProductos');
    if (!table || !headerRow) return;

    const ensureSortable = () => {
        if (typeof Sortable === 'undefined') return false;
        // Evita duplicar instancias
        if (headerRow.__sortableCols) return true;

        headerRow.__sortableCols = new Sortable(headerRow, {
            animation: 150,
            draggable: 'th[data-col]',
            filter: '.col-resize-handle',
            preventOnFilter: false,
            onEnd: () => {
                const order = Array.from(headerRow.querySelectorAll('th[data-col]')).map(th => th.dataset.col);

                table.querySelectorAll('tbody tr').forEach(tr => {
                    const fixed = Array.from(tr.children).filter(td => !td.dataset.col); // ej: [drag, acciones]
                    const byCol = {};
                    tr.querySelectorAll('td[data-col]').forEach(td => { byCol[td.dataset.col] = td; });

                    tr.innerHTML = '';
                    if (fixed[0]) tr.appendChild(fixed[0]);
                    order.forEach(col => { if (byCol[col]) tr.appendChild(byCol[col]); });
                    if (fixed.length > 1) tr.appendChild(fixed[fixed.length - 1]);
                });
            }
        });

        return true;
    };

    // Si CDN falló, intenta fallback a unpkg y reintenta
    if (!ensureSortable()) {
        if (!document.getElementById('sortableFallback')) {
            const sc = document.createElement('script');
            sc.id = 'sortableFallback';
            sc.src = 'https://unpkg.com/sortablejs@1.15.2/Sortable.min.js';
            sc.onload = () => ensureSortable();
            document.head.appendChild(sc);
        }
    }
}

// Modal proveedor
function abrirModalProveedor() {
    const modal = document.getElementById('modalProveedor');
    if (!modal) return;
    // reset inputs
    const err = document.getElementById('proveedorModalError');
    if (err) { err.classList.add('hidden'); err.textContent = ''; }
    const set = (id, v='') => { const el = document.getElementById(id); if (el) el.value = v; };
    set('nuevoProveedorNombre');
    set('nuevoProveedorRuc');
    set('nuevoProveedorTelefono');
    set('nuevoProveedorDireccion');
    set('nuevoProveedorEmail');

    modal.classList.remove('hidden');

    setTimeout(() => {
        document.getElementById('nuevoProveedorNombre')?.focus();
    }, 50);
}
function cerrarModalProveedor() {
    const modal = document.getElementById('modalProveedor');
    if (!modal) return;
    modal.classList.add('hidden');
}

async function guardarNuevoProveedor() {
    const btn = document.getElementById('btnGuardarProveedorModal');
    const err = document.getElementById('proveedorModalError');

    const nombre = (document.getElementById('nuevoProveedorNombre')?.value || '').trim();
    const ruc = (document.getElementById('nuevoProveedorRuc')?.value || '').trim();
    const telefono = (document.getElementById('nuevoProveedorTelefono')?.value || '').trim();
    const direccion = (document.getElementById('nuevoProveedorDireccion')?.value || '').trim();
    const email = (document.getElementById('nuevoProveedorEmail')?.value || '').trim();

    if (!nombre) {
        if (err) {
            err.textContent = 'El nombre del proveedor es obligatorio.';
            err.classList.remove('hidden');
        }
        document.getElementById('nuevoProveedorNombre')?.focus();
        return;
    }

    if (btn) {
        btn.disabled = true;
        btn.textContent = 'Guardando...';
    }
    if (err) { err.classList.add('hidden'); err.textContent = ''; }

    try {
        const res = await fetch(`{{ route('proveedores.store') }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': `{{ csrf_token() }}`,
            },
            body: JSON.stringify({ nombre, ruc, telefono, direccion, email })
        });

        if (res.status === 422) {
            const data = await res.json();
            const messages = [];
            if (data && data.errors) {
                Object.values(data.errors).forEach(arr => {
                    if (Array.isArray(arr)) arr.forEach(m => messages.push(m));
                });
            }
            if (err) {
                err.textContent = messages.length ? messages.join(' ') : 'Revisa los datos del proveedor.';
                err.classList.remove('hidden');
            }
            return;
        }

        if (!res.ok) {
            const txt = await res.text();
            if (err) {
                err.textContent = 'No se pudo guardar el proveedor. ' + (txt ? 'Intenta de nuevo.' : '');
                err.classList.remove('hidden');
            }
            return;
        }

        const data = await res.json();
        const proveedor = data.proveedor || data;
        const id = proveedor.id;
        const nombreProv = proveedor.nombre || nombre;

        const select = document.getElementById('proveedor_id') || document.querySelector('select[name="proveedor_id"]');
        if (select && id) {
            let opt = Array.from(select.options).find(o => String(o.value) === String(id));
            if (!opt) {
                opt = new Option(nombreProv, id);
                select.add(opt);
            } else {
                opt.textContent = nombreProv;
            }
            select.value = String(id);
            // dispara change para lógica externa / autosave
            select.dispatchEvent(new Event('change', { bubbles: true }));
        }

        cerrarModalProveedor();
    } catch (e) {
        if (err) {
            err.textContent = 'Error al guardar el proveedor. Verifica tu conexión.';
            err.classList.remove('hidden');
        }
    } finally {
        if (btn) {
            btn.disabled = false;
            btn.textContent = 'Guardar proveedor';
        }
    }
}

// ================================
//  Advertencia: Precio en 0 (confirmación antes de guardar)
// ================================
let permitirSubmitPrecioCero = false;

function abrirModalPrecioCero(nombres = []) {
    const modal = document.getElementById('modalPrecioCero');
    const lista = document.getElementById('listaPrecioCero');
    if (!modal || !lista) return;

    lista.innerHTML = '';
    nombres.forEach(n => {
        const li = document.createElement('li');
        li.textContent = n;
        lista.appendChild(li);
    });

    modal.classList.remove('hidden');
}

function cerrarModalPrecioCero() {
    const modal = document.getElementById('modalPrecioCero');
    if (!modal) return;
    modal.classList.add('hidden');
}

function confirmarContinuarPrecioCero() {
    permitirSubmitPrecioCero = true;
    cerrarModalPrecioCero();
    const form = document.getElementById('formCompra');
    if (form) form.requestSubmit ? form.requestSubmit() : form.submit();
}

function obtenerNombreProducto(index) {
    const row = document.getElementById(`producto_${index}`);
    if (!row) return `Producto #${index}`;

    // Si hay select de producto
    const sel = row.querySelector(`select[name="productos[${index}][producto_id]"]`);
    if (sel && sel.selectedOptions && sel.selectedOptions[0]) {
        const txt = sel.selectedOptions[0].textContent || '';
        return txt.trim() || `Producto #${index}`;
    }

    // Si el producto viene preseleccionado (hidden)
    const nameEl = row.querySelector('.font-semibold, .font-bold');
    if (nameEl) {
        const txt = (nameEl.textContent || '').trim();
        if (txt) return txt;
    }

    // fallback con ID
    const hid = row.querySelector(`input[name="productos[${index}][producto_id]"]`);
    if (hid && hid.value) return `Producto ID ${hid.value}`;

    return `Producto #${index}`;
}

/** ================================
 *  DATA
 *  ================================= */
const productosData = @json($productosData);

function cambiarVista(vista) {
    // Captura indices y valores desde la vista actual antes de cambiar
    if (!restaurandoDraft) {
        capturarIndicesDesdeDOM();
        capturarEstadoActual();
    }

    vistaActual = vista;

    // alterna contenedores
    document.getElementById('vistaTabla').classList.toggle('hidden', vista !== 'tabla');
    document.getElementById('vistaFormulario').classList.toggle('hidden', vista !== 'formulario');

    // IMPORTANTE: limpiar la vista oculta para evitar IDs duplicados (tabla vs cards)
    if (vista === 'tabla') {
        const contCards = document.getElementById('detallesFormulario');
        if (contCards) contCards.innerHTML = '';
    } else {
        const contTabla = document.getElementById('detallesTabla');
        if (contTabla) contTabla.innerHTML = '';
    }

    // estilos botones
    document.getElementById('btnVistaTabla').classList.toggle('bg-blue-600', vista === 'tabla');
    document.getElementById('btnVistaTabla').classList.toggle('text-white', vista === 'tabla');
    document.getElementById('btnVistaTabla').classList.toggle('bg-white', vista !== 'tabla');

    document.getElementById('btnVistaFormulario').classList.toggle('bg-blue-600', vista === 'formulario');
    document.getElementById('btnVistaFormulario').classList.toggle('text-white', vista === 'formulario');
    document.getElementById('btnVistaFormulario').classList.toggle('bg-white', vista !== 'formulario');

    // Renderiza la vista destino desde el estado (para que no "se pierdan")
    renderVista(vista);

    if (vista === 'tabla') initResizableColumns();
    initReorderableColumns();

    // persistir vista / borrador
    if (!restaurandoDraft) saveCompraDraftDebounced();
}



/** ================================
 *  MODAL CATÁLOGO PRODUCTOS (GRID + BÚSQUEDA + MULTI)
 *  ================================= */
let catalogoSeleccionados = new Set();
let catalogoFiltro = '';

function abrirModalProductos() {
    document.getElementById('modalProductos').classList.remove('hidden');
    // Reset UI
    const input = document.getElementById('modalBuscarProducto');
    if (input) {
        input.value = '';
        input.focus();
    }
    catalogoFiltro = '';
    // no limpiamos selección automáticamente para permitir elegir varios sin perder por error
    renderCatalogoProductos();
    actualizarEstadoCatalogo();
}

function cerrarModalProductos() {
    document.getElementById('modalProductos').classList.add('hidden');
}

function filtrarCatalogoProductos(valor) {
    catalogoFiltro = (valor || '').toLowerCase().trim();
    renderCatalogoProductos();
}

function limpiarSeleccionCatalogo() {
    catalogoSeleccionados = new Set();
    renderCatalogoProductos();
    actualizarEstadoCatalogo();
}

function toggleSeleccionProductoCatalogo(productoId) {
    const id = String(productoId);
    if (catalogoSeleccionados.has(id)) catalogoSeleccionados.delete(id);
    else catalogoSeleccionados.add(id);
    actualizarEstadoCatalogo();
}

function actualizarEstadoCatalogo() {
    const countEl = document.getElementById('modalSeleccionadosCount');
    if (countEl) countEl.textContent = String(catalogoSeleccionados.size);

    const btn = document.getElementById('btnAgregarSeleccionados');
    if (btn) btn.disabled = catalogoSeleccionados.size === 0;
}

function productoCoincideFiltro(p, filtro) {
    if (!filtro) return true;
    const nombre = (p.nombre || '').toLowerCase();
    const desc = (p.descripcion || '').toLowerCase();
    const cb = ((p.codigo_barras || p.codigo_barra) || '').toLowerCase();
    return nombre.includes(filtro) || desc.includes(filtro) || cb.includes(filtro);
}

function renderCatalogoProductos() {
    const grid = document.getElementById('modalProductosGrid');
    if (!grid) return;

    const filtro = catalogoFiltro;
    const items = productosData.filter(p => productoCoincideFiltro(p, filtro));

    if (items.length === 0) {
        grid.innerHTML = `
            <div class="col-span-full rounded-xl border border-dashed border-gray-300 dark:border-gray-600 p-8 text-center">
                <div class="text-sm font-semibold text-slate-700 dark:text-slate-200">Sin resultados</div>
                <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">Prueba con otro nombre, descripción o código de barras.</div>
            </div>
        `;
        return;
    }

    grid.innerHTML = items.map(p => {
        const id = String(p.id);
        const checked = catalogoSeleccionados.has(id);
        const img = p.imagen_url || '';
        const precio = Number(p.precio_compra || 0);

        return `
            <label class="relative flex gap-3 p-4 rounded-2xl border ${checked ? 'border-green-300 dark:border-green-700 bg-green-50/60 dark:bg-green-900/10' : 'border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50'} transition cursor-pointer">
                <input type="checkbox" class="mt-1.5 h-4 w-4 rounded border-gray-300 text-green-600 focus:ring-green-500"
                       ${checked ? 'checked' : ''}
                       onchange="toggleSeleccionProductoCatalogo(${p.id})">
                <div class="shrink-0">
                    <img src="${img}"
                         onerror="this.src='https://via.placeholder.com/64?text=%20';"
                         class="w-16 h-16 rounded-xl border border-gray-200 dark:border-gray-600 object-cover bg-gray-50 dark:bg-gray-700"
                         alt="Producto">
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <div class="font-bold text-slate-900 dark:text-white truncate">${p.nombre || ''}</div>
                            <div class="text-xs text-slate-500 dark:text-slate-400 line-clamp-2 mt-0.5">${p.descripcion || ''}</div>
                        </div>
                        <div class="text-right">
                            <div class="text-xs text-slate-500 dark:text-slate-400">Compra</div>
                            <div class="font-bold text-slate-900 dark:text-white">S/ ${precio.toFixed(2)}</div>
                        </div>
                    </div>
                    <div class="mt-2 flex items-center justify-between gap-2">
                        <div class="text-xs text-slate-500 dark:text-slate-400">
                            <span class="font-semibold text-slate-700 dark:text-slate-200">CB:</span>
                            <span class="font-mono">${((p.codigo_barras || p.codigo_barra) || '—')}</span>
                        </div>
                        <span class="text-xs ${checked ? 'text-green-700 dark:text-green-300' : 'text-slate-500 dark:text-slate-400'} font-semibold">
                            ${checked ? 'Seleccionado' : 'Seleccionar'}
                        </span>
                    </div>
                </div>
            </label>
        `;
    }).join('');

    // sincroniza conteo/botón
    actualizarEstadoCatalogo();
}

function agregarSeleccionadosDesdeCatalogo() {
    if (catalogoSeleccionados.size === 0) return;

    // Agregar seleccionados a la vista actual (tabla/cards)
    const ids = Array.from(catalogoSeleccionados);
    ids.reverse();
    ids.forEach(id => {
        const p = productosData.find(x => String(x.id) === String(id));
        if (p) agregarProducto(p, false);
    });

    // limpiamos selección para siguiente uso (más intuitivo)
    catalogoSeleccionados = new Set();
    actualizarEstadoCatalogo();
    cerrarModalProductos();
}/** ================================
 *  CREAR DETALLE
 *  ================================= */
function agregarProducto(productoPreseleccionado = null, usarSelect = true) {
    const index = contadorProductos++;
    if (vistaActual === 'tabla') {
        agregarProductoTabla(index, productoPreseleccionado, usarSelect);
    } else {
        agregarProductoCard(index, productoPreseleccionado, usarSelect);
    }
    actualizarAvisosVacio();

    // Actualiza resumen (subtotal/total) desde el estado actual
    calcularTotalGeneral();
}

function agregarProductoTabla(index, productoPreseleccionado, usarSelect, st = null) {
    const tbody = document.getElementById('detallesTabla');
    const row = document.createElement('tr');
    row.id = `producto_${index}`;
    row.dataset.productoIndex = index;

    const selectProducto = usarSelect ? generarSelectProductos(index, productoPreseleccionado) : `
        <input type="hidden" name="productos[${index}][producto_id]" value="${productoPreseleccionado.id}">
        <div class="font-semibold text-slate-900 dark:text-white">${productoPreseleccionado.nombre}</div>
    `;

    row.innerHTML = `
        <td class="px-2 py-3 align-top text-center">
            <span class="drag-handle inline-flex items-center justify-center p-2 rounded-lg text-slate-500 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-move" title="Arrastrar para reordenar">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 6h.01M8 12h.01M8 18h.01M16 6h.01M16 12h.01M16 18h.01"></path>
                </svg>
            </span>
        </td>

        <td data-col="1" class="px-3 py-3 align-top whitespace-normal break-words">${selectProducto}</td>

        <td data-col="2" class="px-3 py-3 align-top">
            <div class="space-y-2">
                <select class="select-presentacion-${index} w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500"
                        onchange="alSeleccionarPresentacion(${index})">
                    <option value="">Cargando...</option>
                </select>
                <div class="presentation-label-${index} text-xs text-slate-600 dark:text-slate-300 whitespace-normal break-words"></div>
                <a href="javascript:void(0)" onclick="abrirModalNuevaPresentacion(${index})"
                   class="text-xs font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 underline">
                    + Crear presentación
                </a>
            </div>
        </td>

        <td data-col="3" class="px-3 py-3 text-center align-top">
            <span class="span-unidades-${index} inline-flex items-center justify-center px-3 py-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-sm font-bold text-slate-900 dark:text-white">1</span>
            <input type="hidden" class="input-unidades-${index}" value="1">
        </td>

        <td data-col="4" class="px-3 py-3 align-top">
            <input type="number"
                   class="input-cantidad-${index} w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-right text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500"
                   min="1" value="${st && st.cantidad ? st.cantidad : 1}" placeholder="Ej: 5"
                   title="Cantidad de presentaciones (o unidades base si seleccionas 'Unidad base')."
                   oninput="calcularTotales(${index})" required>
        </td>

        <td data-col="5" class="px-3 py-3 text-center align-top">
            <span class="span-total-${index} inline-flex items-center px-3 py-1.5 rounded-lg bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-200 font-bold">1</span>
        </td>

        <td data-col="6" class="px-3 py-3 align-top">
            <textarea rows="1"
                   class="input-lote-${index} w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-mono text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:ring-2 focus:ring-blue-500 resize-none overflow-hidden whitespace-pre-wrap break-words"
                   placeholder="LOT-2025-001">${st && st.lote ? st.lote : ''}</textarea>
        </td>

        <td data-col="7" class="px-3 py-3 align-top">
            <input type="date"
                   value="${st && st.vence ? st.vence : '' }"
                   value="${st && st.vence ? st.vence : '' }"
                               class="input-vence-${index} w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500"
                   min="${new Date().toISOString().split('T')[0]}" required>
        </td>

        <td data-col="8" class="px-3 py-3 align-top">
            <div class="space-y-1">
                <div class="relative">
                    <span class="absolute inset-y-0 left-3 flex items-center text-slate-500 dark:text-slate-400 text-sm font-semibold">S/</span>
                    <input type="number"
                           class="input-precio-${index} w-full pl-8 pr-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-right text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500"
                           step="0.01" min="0"
                           value="${st && st.precio !== undefined ? st.precio : (productoPreseleccionado ? (productoPreseleccionado.precio_compra || 0) : 0)}"
                           oninput="calcularTotales(${index})" required>
                </div>
                <div class="text-xs text-slate-500 dark:text-slate-400 text-right">
                    Unit: <span class="span-precio-unit-${index} font-semibold text-slate-700 dark:text-slate-200">S/ 0.00</span>
                </div>
            </div>
        </td>

        <td data-col="9" class="px-3 py-3 text-center align-top descuento-prod-col">
            <div class="space-y-1">
                <div class="flex items-center justify-center gap-2">
                    <input type="number" step="0.01" min="0" max="100"
                           class="input-descuento-${index} w-24 px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-right text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500"
                           value="${st && st.descuento !== undefined ? st.descuento : 0}"
                           oninput="calcularTotales(${index})">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">%</span>
                </div>
                <div class="text-[11px] text-slate-500 dark:text-slate-400">
                    Desc: <span class="span-descmonto-${index} font-semibold text-slate-700 dark:text-slate-200">S/ 0.00</span>
                </div>
            </div>
        </td>

        <td data-col="10" class="px-3 py-3 text-right align-top">
            <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-200 font-bold text-sm">
                S/ <span class="span-subtotal-${index} ml-1">0.00</span>
            </span>
        </td>

        <td class="px-3 py-3 text-center align-top">
            <button type="button" onclick="eliminarProducto(${index})"
                    class="p-2 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
            </button>

            <!-- Hidden inputs deben permanecer dentro de una celda para que al mover columnas NO se pierdan -->
            <div class="hidden">
                <input type="hidden" name="productos[${index}][presentacion_id]" value="">
                <input type="hidden" name="productos[${index}][tipo_presentacion]" value="">
                <input type="hidden" name="productos[${index}][unidades_por_presentacion]" value="1" class="hidden-unidades-${index}">
                <input type="hidden" name="productos[${index}][cantidad_presentaciones]" value="1">
                <input type="hidden" name="productos[${index}][numero_lote]" value="">
                <input type="hidden" name="productos[${index}][fecha_vencimiento]" value="">
                <input type="hidden" name="productos[${index}][precio_unitario]" value="0">
        <input type="hidden" name="productos[${index}][descuento]" value="0">
</div>
        </td>
    `;

    // Nuevo producto siempre arriba
    tbody.prepend(row);
    normalizarDataColsFila(row);

    const pid = productoPreseleccionado && !usarSelect ? productoPreseleccionado.id : (st && st.producto_id ? st.producto_id : null);
    if (pid && !usarSelect) {
        cargarPresentaciones(pid, index).then(async () => {
            if (st && st.presentacion_id) {
                const sel = document.querySelector(`${(vistaActual === 'tabla') ? '#vistaTabla' : '#vistaFormulario'} #producto_${index} .select-presentacion-${index}`);
                if (sel) {
                    sel.value = st.presentacion_id;
                    await alSeleccionarPresentacion(index, true);
                }
            }

            // Restaurar campos visibles (por si alSeleccionarPresentacion ajustó algo)
            const row2 = document.getElementById(`producto_${index}`);
            if (row2 && st) {
                const cant = row2.querySelector(`.input-cantidad-${index}`);
                if (cant && st.cantidad) cant.value = st.cantidad;

                const lote = row2.querySelector(`.input-lote-${index}`);
                if (lote && st.lote !== undefined) lote.value = st.lote;

                const vence = row2.querySelector(`.input-vence-${index}`);
                if (vence && st.vence !== undefined) vence.value = st.vence;

                const precio = row2.querySelector(`.input-precio-${index}`);
                if (precio && st.precio !== undefined) precio.value = st.precio;

                sincronizarHiddenDesdeVisibles(index);
                calcularTotales(index);
            }
        });
    }

    asociarEventos(index);
    sincronizarHiddenDesdeVisibles(index);
    calcularTotales(index);
}

function agregarProductoCard(index, productoPreseleccionado, usarSelect, st = null) {
    const container = document.getElementById('detallesFormulario');
    const card = document.createElement('div');
    card.id = `producto_${index}`;
    card.className = 'bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 shadow-sm';
    card.dataset.productoIndex = index;

    const selectProducto = usarSelect ? generarSelectProductos(index, productoPreseleccionado) : `
        <input type="hidden" name="productos[${index}][producto_id]" value="${productoPreseleccionado.id}">
        <div class="font-bold text-slate-900 dark:text-white">${productoPreseleccionado.nombre}</div>
    `;

    card.innerHTML = `
        <div class="flex items-start justify-between gap-3">
            <div class="flex-1">
                <div class="text-xs font-semibold text-slate-500 dark:text-slate-300 uppercase mb-1">Producto</div>
                ${selectProducto}
            </div>
            <div class="flex items-center gap-2">
                <span class="card-drag-handle inline-flex items-center justify-center p-2 rounded-lg text-slate-500 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-gray-600 cursor-move" title="Arrastrar para reordenar">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 6h.01M8 12h.01M8 18h.01M16 6h.01M16 12h.01M16 18h.01"></path>
                    </svg>
                </span>
                <button type="button" onclick="eliminarProducto(${index})"
                    class="p-2 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
            </button>
            </div>
        </div>

        <div class="mt-4 grid grid-cols-1 gap-4">
    <div>
        <div class="text-xs font-semibold text-slate-500 dark:text-slate-300 uppercase mb-1">Presentación</div>
        <div class="space-y-2">
            <select class="select-presentacion-${index} w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500"
                    onchange="alSeleccionarPresentacion(${index})">
                <option value="">Cargando...</option>
            </select>
            <div class="presentation-label-${index} text-xs text-slate-600 dark:text-slate-300 whitespace-normal break-words"></div>
            <a href="javascript:void(0)" onclick="abrirModalNuevaPresentacion(${index})"
               class="text-xs font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 underline">
                + Crear presentación
            </a>
        </div>
    </div>

    <div class="grid grid-cols-3 gap-3">
        <div class="text-center rounded-xl border border-slate-200 dark:border-gray-600 bg-slate-50 dark:bg-gray-800/50 p-3">
            <div class="text-[11px] font-semibold text-slate-500 dark:text-slate-300 uppercase">Unid/Pres</div>
            <div class="mt-1 span-unidades-${index} text-lg font-bold text-slate-900 dark:text-white">1</div>
            <input type="hidden" class="input-unidades-${index}" value="1">
        </div>

        <div class="rounded-xl border border-slate-200 dark:border-gray-600 bg-white dark:bg-gray-800 p-3">
            <div class="text-[11px] font-semibold text-slate-500 dark:text-slate-300 uppercase mb-1">Cantidad (pres.)</div>
            <input type="number" min="1" value="${st && st.cantidad ? st.cantidad : 1}"
                   class="input-cantidad-${index} w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-right text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500"
                   placeholder="Ej: 5"
                   title="Cantidad de presentaciones (o unidades base si seleccionas 'Unidad base')."
                   oninput="calcularTotales(${index})" required>
        </div>

        <div class="text-center rounded-xl border border-slate-200 dark:border-gray-600 bg-purple-50 dark:bg-purple-900/20 p-3">
            <div class="text-[11px] font-semibold text-slate-500 dark:text-slate-300 uppercase">Total unid</div>
            <div class="mt-1 span-total-${index} text-lg font-bold text-purple-700 dark:text-purple-200">1</div>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-3">
        <div>
            <div class="text-xs font-semibold text-slate-500 dark:text-slate-300 uppercase mb-1">Lote</div>
            <input type="text"
                   class="input-lote-${index} w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-mono text-slate-900 dark:text-white"
                   placeholder="LOT-2025-001" value="${st && st.lote ? st.lote : '' }" required>
        </div>
        <div>
            <div class="text-xs font-semibold text-slate-500 dark:text-slate-300 uppercase mb-1">Venc.</div>
            <input type="date"
                   class="input-vence-${index} w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-slate-900 dark:text-white"
                   value="${st && st.vence ? st.vence : '' }"
                   min="${new Date().toISOString().split('T')[0]}" required>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-3 items-start">
        <div>
            <div class="text-xs font-semibold text-slate-500 dark:text-slate-300 uppercase mb-1">Precio pres.</div>
            <div class="relative">
                <span class="absolute inset-y-0 left-3 flex items-center text-slate-500 dark:text-slate-400 text-sm font-semibold">S/</span>
                <input type="number" step="0.01" min="0"
                       class="input-precio-${index} w-full pl-8 pr-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-right text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500"
                       value="${st && st.precio !== undefined ? st.precio : (productoPreseleccionado ? (productoPreseleccionado.precio_compra || 0) : 0)}"
                       oninput="calcularTotales(${index})" required>
            </div>
            <div class="mt-1 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                <span>Unit</span>
                <span class="span-precio-unit-${index} font-semibold text-slate-700 dark:text-slate-200">S/ 0.00</span>
            </div>
        </div>

        <div class="desc-prod-card-field">
            <div class="text-xs font-semibold text-slate-500 dark:text-slate-300 uppercase mb-1">Desc. % (prod.)</div>
            <div class="flex items-center justify-end gap-2">
                <input type="number" step="0.01" min="0" max="100"
                       class="input-descuento-${index} w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-right text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500"
                       value="${st && st.descuento !== undefined ? st.descuento : 0}"
                       oninput="calcularTotales(${index})">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">%</span>
            </div>
            <div class="mt-1 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                <span>Desc</span>
                <span class="span-descmonto-${index} font-semibold text-slate-700 dark:text-slate-200">S/ 0.00</span>
            </div>
        </div>
    </div>

    <div class="rounded-xl border border-slate-200 dark:border-gray-600 bg-slate-50 dark:bg-gray-800/50 p-3 flex items-center justify-between">
        <span class="text-[11px] font-semibold text-slate-500 dark:text-slate-300 uppercase">Subtotal neto</span>
        <span class="inline-flex items-center px-3 py-2 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-200 font-bold">
            S/ <span class="span-subtotal-${index} ml-1">0.00</span>
        </span>
    </div>
</div>

        <input type="hidden" name="productos[${index}][presentacion_id]" value="">
        <input type="hidden" name="productos[${index}][tipo_presentacion]" value="">
        <input type="hidden" name="productos[${index}][unidades_por_presentacion]" value="1" class="hidden-unidades-${index}">
        <input type="hidden" name="productos[${index}][cantidad_presentaciones]" value="1">
        <input type="hidden" name="productos[${index}][numero_lote]" value="">
        <input type="hidden" name="productos[${index}][fecha_vencimiento]" value="">
        <input type="hidden" name="productos[${index}][precio_unitario]" value="0">
        <input type="hidden" name="productos[${index}][descuento]" value="0">
`;

    // Nuevo producto siempre arriba
    container.prepend(card);

    const pid = productoPreseleccionado && !usarSelect ? productoPreseleccionado.id : (st && st.producto_id ? st.producto_id : null);
    if (pid && !usarSelect) {
        cargarPresentaciones(pid, index).then(async () => {
            // restaurar selección si existe
            if (st && st.presentacion_id) {
                const sel = document.querySelector(`${(vistaActual === 'tabla') ? '#vistaTabla' : '#vistaFormulario'} #producto_${index} .select-presentacion-${index}`);
                if (sel) {
                    sel.value = st.presentacion_id;
                    await alSeleccionarPresentacion(index, true);
                }
            }

            const row2 = document.getElementById(`producto_${index}`);
            if (row2 && st) {
                const cant = row2.querySelector(`.input-cantidad-${index}`);
                if (cant && st.cantidad) cant.value = st.cantidad;

                const lote = row2.querySelector(`.input-lote-${index}`);
                if (lote && st.lote !== undefined) lote.value = st.lote;

                const vence = row2.querySelector(`.input-vence-${index}`);
                if (vence && st.vence !== undefined) vence.value = st.vence;

                const precio = row2.querySelector(`.input-precio-${index}`);
                if (precio && st.precio !== undefined) precio.value = st.precio;

                sincronizarHiddenDesdeVisibles(index);
                calcularTotales(index);
            }
        });
    }

    asociarEventos(index);
    sincronizarHiddenDesdeVisibles(index);
    calcularTotales(index);
}

function generarSelectProductos(index, preseleccionado) {
    let options = '<option value="">Seleccionar producto...</option>';
    productosData.forEach(p => {
        const selected = preseleccionado && String(p.id) === String(preseleccionado.id) ? 'selected' : '';
        // guardamos el nombre para filtro rápido
        options += `<option value="${p.id}" data-precio="${p.precio_compra || 0}" data-nombre="${(p.nombre || '').toLowerCase()}" ${selected}>${p.nombre}</option>`;
    });
    return `
        <div class="space-y-2">
            <input type="text"
                   class="buscador-producto-${index} w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500"
                   placeholder="Buscar producto por nombre..."
                   oninput="filtrarProductos(${index}, this.value)"
                   autocomplete="off">

            <select name="productos[${index}][producto_id]" required
                    class="select-producto-${index} w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500"
                    onchange="alSeleccionarProducto(${index})">
                ${options}
            </select>
        </div>
    `;
}



function filtrarProductos(index, query) {
    const row = document.getElementById(`producto_${index}`) || document.getElementById(`card_${index}`);
    const select = row?.querySelector(`.select-producto-${index}`);
    if (!select) return;

    const q = (query || '').toLowerCase().trim();
    Array.from(select.options).forEach((opt, i) => {
        if (i === 0) { // "Seleccionar producto..."
            opt.hidden = false;
            return;
        }
        const name = (opt.dataset.nombre || opt.textContent || '').toLowerCase();
        opt.hidden = q.length > 0 && !name.includes(q);
    });

    // si el seleccionado quedó oculto, limpiar selección
    const selectedOpt = select.options[select.selectedIndex];
    if (selectedOpt && selectedOpt.hidden) {
        select.value = '';
    }
}

function asociarEventos(index) {
    const row = document.getElementById(`producto_${index}`);
    if (!row) return;

    const lote = row.querySelector(`.input-lote-${index}`);
    const vence = row.querySelector(`.input-vence-${index}`);
    const precio = row.querySelector(`.input-precio-${index}`);
    const descuento = row.querySelector(`.input-descuento-${index}`);
    const unidadesInput = row.querySelector(`.input-unidades-${index}`);
    const cantidad = row.querySelector(`.input-cantidad-${index}`);

    if (lote) {
        const autoGrow = () => {
            if (lote.tagName === 'TEXTAREA') {
                lote.style.height = 'auto';
                lote.style.height = (lote.scrollHeight) + 'px';
            }
        };
        autoGrow();
        lote.addEventListener('input', () => {
            autoGrow();
            const hidden = row.querySelector(`input[name="productos[${index}][numero_lote]"]`);
            if (hidden) hidden.value = lote.value;
        });
    }

    if (vence) vence.addEventListener('input', () => {
        const hidden = row.querySelector(`input[name="productos[${index}][fecha_vencimiento]"]`);
        if (hidden) hidden.value = vence.value;
    });

    if (precio) precio.addEventListener('input', () => {
        calcularTotales(index);
    });

  if (unidadesInput) unidadesInput.addEventListener('input', () => {
    const hidden = row.querySelector(`input[name="productos[${index}][unidades_por_presentacion]"]`);
    if (hidden) hidden.value = unidadesInput.value;

    const hiddenUn = row.querySelector(`.hidden-unidades-${index}`);
    if (hiddenUn) hiddenUn.value = unidadesInput.value;

    calcularTotales(index);
});

if (cantidad) cantidad.addEventListener('input', () => {
    const hidden = row.querySelector(`input[name="productos[${index}][cantidad_presentaciones]"]`);
    if (hidden) hidden.value = cantidad.value;

    calcularTotales(index);
});
}

/** ================================
 *  MODAL NUEVA PRESENTACIÓN
 *  ================================= */
let contextoNuevaPresentacion = { index: null, productoId: null, productoNombre: '' };

function abrirModalNuevaPresentacion(index) {
    const row = document.getElementById(`producto_${index}`);
    if (!row) return;

    const sel = row.querySelector(`select[name="productos[${index}][producto_id]"]`);
    const productoId = sel?.value || row.querySelector(`input[name="productos[${index}][producto_id]"]`)?.value;

    if (!productoId) {
        alert('⚠️ Primero selecciona un producto para poder crear una presentación.');
        return;
    }

    let productoNombre = '';
    if (sel && sel.selectedIndex >= 0) {
        productoNombre = sel.options[sel.selectedIndex].textContent?.trim() || '';
    } else {
        const prod = productosData.find(p => String(p.id) === String(productoId));
        productoNombre = prod ? prod.nombre : '';
    }

    contextoNuevaPresentacion = { index, productoId, productoNombre };

    document.getElementById('pres_nombre').value = '';
    document.getElementById('pres_descripcion').value = '';
    document.getElementById('pres_unidades').value = '';
    document.getElementById('pres_precio').value = '';
    document.getElementById('pres_codigo').value = '';
    document.getElementById('pres_activo').checked = true;
    document.getElementById('pres_orden').value = 0;

    const err = document.getElementById('pres_error');
    err.classList.add('hidden'); err.textContent = '';

    document.getElementById('modalNuevaPresentacionSub').textContent = `Producto: ${productoNombre || ('#' + productoId)}`;
    document.getElementById('modalNuevaPresentacion').classList.remove('hidden');
}

function cerrarModalNuevaPresentacion() {
    document.getElementById('modalNuevaPresentacion').classList.add('hidden');
}

async function guardarNuevaPresentacion() {
    const { index, productoId } = contextoNuevaPresentacion;
    if (index === null || !productoId) return;

    const nombre = document.getElementById('pres_nombre').value.trim();
    const descripcion = document.getElementById('pres_descripcion').value.trim();
    const unidades = parseInt(document.getElementById('pres_unidades').value, 10);
    const precio = document.getElementById('pres_precio').value;
    const codigo = document.getElementById('pres_codigo').value.trim();
    const activo = document.getElementById('pres_activo').checked;
    const orden = parseInt(document.getElementById('pres_orden').value || '0', 10);

    const err = document.getElementById('pres_error');
    const showError = (msg) => { err.textContent = msg; err.classList.remove('hidden'); };

    if (!nombre) return showError('El nombre es obligatorio.');
    if (!unidades || unidades < 1) return showError('Las unidades por presentación deben ser >= 1.');

    const payload = {
        nombre,
        descripcion: descripcion || null,
        unidades_por_presentacion: unidades,
        precio_sugerido: (precio === '' ? null : Number(precio)),
        codigo_barras: (codigo || null),
        activo,
        orden: isNaN(orden) ? 0 : orden
    };

    try {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        const res = await fetch(`/api/productos/${productoId}/presentaciones`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                ...(csrf ? {'X-CSRF-TOKEN': csrf} : {})
            },
            body: JSON.stringify(payload)
        });

        const data = await res.json();

        if (!res.ok || !data.ok) {
            const msg = data?.message || (data?.errors ? Object.values(data.errors).flat().join(' ') : 'Error al guardar.');
            return showError(msg);
        }

        // Insertar en combobox y seleccionar
        const scope = (vistaActual === 'tabla') ? '#vistaTabla' : '#vistaFormulario';
    const selectElement = document.querySelector(`${scope} #producto_${index} .select-presentacion-${index}`);
        if (selectElement) {
            const pres = data.presentacion;
            const option = document.createElement('option');
            option.value = pres.id;
            option.textContent = `${pres.nombre} (x${pres.unidades_por_presentacion})` + (pres.precio_sugerido ? ` - S/ ${parseFloat(pres.precio_sugerido).toFixed(2)}` : '');
            option.dataset.unidades = String(pres.unidades_por_presentacion);
            option.dataset.precio = pres.precio_sugerido ?? '';
            selectElement.appendChild(option);
            selectElement.value = pres.id;
        }

        cerrarModalNuevaPresentacion();
        await alSeleccionarPresentacion(index, true);

    } catch (e) {
        return showError('Error de red al guardar la presentación.');
    }
}

/** ================================
 *  PRESENTACIONES
 *  ================================= */
async function cargarPresentaciones(productoId, index) {
    const selectElement = document.querySelector(`${(vistaActual === 'tabla') ? '#vistaTabla' : '#vistaFormulario'} #producto_${index} .select-presentacion-${index}`);
    if (!selectElement) return;

    try {
        const response = await fetch(`/api/productos/${productoId}/presentaciones`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await response.json();

        selectElement.innerHTML = '';

        // Unidad base
        const optionUnidad = document.createElement('option');
        optionUnidad.value = '';
        optionUnidad.textContent = 'Unidad base (1 unidad)';
        optionUnidad.dataset.unidades = '1';
        optionUnidad.dataset.precio = data.producto?.precio_compra || 0;
        selectElement.appendChild(optionUnidad);

        if (data.presentaciones && data.presentaciones.length > 0) {
            data.presentaciones.forEach(pres => {
                const option = document.createElement('option');
                option.value = pres.id;
                option.textContent = `${pres.nombre} (x${pres.unidades_por_presentacion})`;
                option.dataset.unidades = pres.unidades_por_presentacion;
                option.dataset.precio = pres.precio_sugerido ?? '';
                if (pres.precio_sugerido) {
                    option.textContent += ` - S/ ${parseFloat(pres.precio_sugerido).toFixed(2)}`;
                }
                selectElement.appendChild(option);
            });
        }

        // aplicar selección guardada si existe; si no, unidad base
        const st = estadoDetalles[index] || {};
        if (st.presentacion_id) {
            selectElement.value = String(st.presentacion_id);
        } else {
            selectElement.value = '';
        }

        await alSeleccionarPresentacion(index, true);

    } catch (error) {
        console.error('Error al cargar presentaciones:', error);
        selectElement.innerHTML = '<option value="">Error al cargar</option>';
    }
}

function alSeleccionarProducto(index) {
    const container = document.getElementById(`producto_${index}`) || document.getElementById(`card_${index}`);
    if (!container) return;

    const sel = container.querySelector(`select[name="productos[${index}][producto_id]"]`) || container.querySelector(`.select-producto-${index}`);
    const productoId = sel?.value;

    // actualizar imagen
    const producto = productosData.find(p => String(p.id) === String(productoId));
    const img = container.querySelector(`.img-producto-${index}`);
    if (img) {
        img.src = producto?.imagen_url ? producto.imagen_url : 'https://via.placeholder.com/48?text=%20';
    }

    // Reset de presentación/unidades
    const hidPres = container.querySelector(`input[name="productos[${index}][presentacion_id]"]`);
    const hidTipo = container.querySelector(`input[name="productos[${index}][tipo_presentacion]"]`);
    const hidUnid = container.querySelector(`input[name="productos[${index}][unidades_por_presentacion]"]`);
    const hidUnid2 = container.querySelector(`.hidden-unidades-${index}`);

    if (hidPres) hidPres.value = '';
    if (hidTipo) hidTipo.value = '';
    if (hidUnid) hidUnid.value = '1';
    if (hidUnid2) hidUnid2.value = '1';

    const spanUn = container.querySelector(`.span-unidades-${index}`);
    if (spanUn) spanUn.textContent = '1';
    const unInp = container.querySelector(`.input-unidades-${index}`);
    if (unInp) unInp.value = '1';

    // set precio base desde option
    const precio = sel?.selectedOptions?.[0]?.dataset?.precio ?? 0;
    const precioInput = container.querySelector(`.input-precio-${index}`);
    if (precioInput) precioInput.value = precio;

    // cargar presentaciones para ese producto
    if (productoId) {
        cargarPresentaciones(productoId, index);
    }
}


async function alSeleccionarPresentacion(index, silent = false) {
    const row = document.getElementById(`producto_${index}`);
    if (!row) return;

    const select = row.querySelector(`.select-presentacion-${index}`);
    const opt = select?.selectedOptions?.[0];

    // Mostrar nombre de presentación en 2 líneas (wrap)
    const lbl = row.querySelector(`.presentation-label-${index}`);
    if (lbl) lbl.textContent = opt ? opt.textContent : '';


    const unidades = parseInt(opt?.dataset?.unidades || '1', 10) || 1;
    const presId = select?.value || '';

    // actualizar UI
    const spanUn = row.querySelector(`.span-unidades-${index}`);
    if (spanUn) spanUn.textContent = String(unidades);

    const unidadesInput = row.querySelector(`.input-unidades-${index}`);
    if (unidadesInput) unidadesInput.value = String(unidades);

    row.querySelector(`input[name="productos[${index}][unidades_por_presentacion]"]`).value = String(unidades);
    row.querySelector(`.hidden-unidades-${index}`).value = String(unidades);
    row.querySelector(`input[name="productos[${index}][presentacion_id]"]`).value = presId;

    // si tiene precio sugerido, reemplazar precio unitario por unidad base o mantener?
    // En tu modelo, precio_unitario es por unidad base, así que si precio sugerido viene por presentación,
    // lo convertimos a unitario base (precio_sugerido / unidades).
    const precioSug = opt?.dataset?.precio;
    const precioInput = row.querySelector(`.input-precio-${index}`);
    if (precioSug !== undefined && precioSug !== null && precioSug !== '' && precioInput) {
        const presPrecio = parseFloat(precioSug);
        if (!isNaN(presPrecio) && unidades > 0) {
            precioInput.value = presPrecio.toFixed(2);
        }
    }

    calcularTotales(index);
    if (!silent) calcularTotalGeneral();
}

/** ================================
 *  CÁLCULOS
 *  ================================= */

function calcularTotales(index) {
    const row = document.getElementById(`producto_${index}`);
    if (!row) return;

    const unidades = parseInt(row.querySelector(`.input-unidades-${index}`)?.value || '1', 10) || 1;
    const cantidad = parseInt(row.querySelector(`.input-cantidad-${index}`)?.value || '1', 10) || 1;

    // IMPORTANTE: en Compras el usuario ingresa el precio de la PRESENTACIÓN seleccionada (caja/frasco/blíster).
    // El sistema calcula el precio unitario base (para inventario/costo) como: precioPres / unidades.
    const precioPres = parseFloat(row.querySelector(`.input-precio-${index}`)?.value || '0') || 0;

    let descuentoPct = parseFloat(row.querySelector(`.input-descuento-${index}`)?.value || '0') || 0;
    if (descuentoPct < 0) descuentoPct = 0;
    if (descuentoPct > 100) descuentoPct = 100;

    const totalUnid = unidades * cantidad;
    const precioUnit = (unidades > 0) ? (precioPres / unidades) : 0;

    const subtotalBruto = precioPres * cantidad;
    const descuentoMonto = subtotalBruto * (descuentoPct / 100);
    const subtotalNeto = Math.max(0, subtotalBruto - descuentoMonto);

    // UI
    const spanTotal = row.querySelector(`.span-total-${index}`);
    if (spanTotal) spanTotal.textContent = String(totalUnid);

    const spanPrecioUnit = row.querySelector(`.span-precio-unit-${index}`);
    if (spanPrecioUnit) spanPrecioUnit.textContent = `S/ ${precioUnit.toFixed(2)}`;

    const spanSub = row.querySelector(`.span-subtotal-${index}`);
    if (spanSub) spanSub.textContent = subtotalNeto.toFixed(2);

    const spanDescMonto = row.querySelector(`.span-descmonto-${index}`);
    if (spanDescMonto) spanDescMonto.textContent = `S/ ${descuentoMonto.toFixed(2)}`;

    // Hidden
    row.querySelector(`input[name="productos[${index}][cantidad_presentaciones]"]`).value = String(cantidad);
    row.querySelector(`input[name="productos[${index}][precio_unitario]"]`).value = String(precioUnit.toFixed(2));

    const hiddenDesc = row.querySelector(`input[name="productos[${index}][descuento]"]`);
    if (hiddenDesc) hiddenDesc.value = String(descuentoPct.toFixed(2));

    calcularTotalGeneral();
}

function calcularTotalGeneral() {
    let subtotalBruto = 0;
    let descuentoLineas = 0;

    document.querySelectorAll('[data-producto-index]').forEach(el => {
        const idx = el.dataset.productoIndex;
        if (idx === undefined || idx === null) return;

        const row = document.getElementById(`producto_${idx}`);
        if (!row) return;

        const unidades = parseInt(row.querySelector(`.input-unidades-${idx}`)?.value || '1', 10) || 1;
        const cantidad = parseInt(row.querySelector(`.input-cantidad-${idx}`)?.value || '1', 10) || 1;
        const precioPres = parseFloat(row.querySelector(`.input-precio-${idx}`)?.value || '0') || 0;

        let descPct = parseFloat(row.querySelector(`.input-descuento-${idx}`)?.value || '0') || 0;
        if (descPct < 0) descPct = 0;
        if (descPct > 100) descPct = 100;

        const lineaBruto = precioPres * cantidad;
        const lineaDesc = lineaBruto * (descPct / 100);

        subtotalBruto += lineaBruto;
        descuentoLineas += lineaDesc;
    });

    subtotalBruto = Math.round(subtotalBruto * 100) / 100;
    descuentoLineas = Math.round(descuentoLineas * 100) / 100;

    const netoAntesGlobal = Math.max(0, subtotalBruto - descuentoLineas);

    const subtotalDisplay = document.getElementById('subtotalDisplay');
    if (subtotalDisplay) subtotalDisplay.textContent = subtotalBruto.toFixed(2);

    const descuentoInput = document.getElementById('descuento');
    const descuentoHint = document.getElementById('descuentoHint');

    let descuentoPct = 0;
    let descuentoAjustado = false;

    if (descuentoInput) {
        descuentoPct = parseFloat(descuentoInput.value || '0') || 0;

        if (descuentoPct < 0) { descuentoPct = 0; descuentoAjustado = true; }
        if (descuentoPct > 100) { descuentoPct = 100; descuentoAjustado = true; }

        if (descuentoAjustado) {
            descuentoInput.value = descuentoPct.toFixed(2);
            if (descuentoHint) descuentoHint.classList.remove('hidden');
        } else {
            if (descuentoHint) descuentoHint.classList.add('hidden');
        }
    }

    const descuentoGlobalMonto = netoAntesGlobal * (descuentoPct / 100);
    const descuentoTotal = Math.round((descuentoLineas + descuentoGlobalMonto) * 100) / 100;

    const descuentoMontoDisplay = document.getElementById('descuentoMontoDisplay');
    if (descuentoMontoDisplay) {
        descuentoMontoDisplay.textContent = descuentoTotal.toFixed(2);
    }

    const descLineasDisplay = document.getElementById('descuentoLineasDisplay');
    if (descLineasDisplay) {
        descLineasDisplay.textContent = descuentoLineas.toFixed(2);
    }

    const totalNeto = Math.max(0, netoAntesGlobal - descuentoGlobalMonto);

    const totalDisplay = document.getElementById('totalDisplay');
    if (totalDisplay) totalDisplay.textContent = totalNeto.toFixed(2);
const totalInput = document.getElementById('total');
    if (totalInput) totalInput.value = totalNeto.toFixed(2);

    // autosave borrador
    if (!restaurandoDraft) saveCompraDraftDebounced();
}

function eliminarProducto(index) {
    const el = document.getElementById(`producto_${index}`);
    if (el) el.remove();
    calcularTotalGeneral();
    actualizarAvisosVacio();
}

function actualizarAvisosVacio() {
    const hayTabla = document.querySelectorAll('#detallesTabla tr').length > 0;
    const hayCards = document.querySelectorAll('#detallesFormulario > div').length > 0;

    document.getElementById('avisoSinProductos').classList.toggle('hidden', hayTabla);
    document.getElementById('avisoSinProductosCards').classList.toggle('hidden', hayCards);
}

/** ================================
 *  ESCANEO (PISTOLA) - CÓDIGO DE BARRAS
 *  ================================= */
let productoBarcodeActual = null;

function normalizarBarcode(v) {
    return String(v ?? '').trim();
}

function buscarProductoPorBarcode(code) {
    const c = normalizarBarcode(code);
    if (!c) return null;
    // búsqueda exacta por código de barras (producto base)
    return productosData.find(p => normalizarBarcode((p.codigo_barras || p.codigo_barra)) === c) || null;
}

function mostrarPreviewBarcode(producto, code) {
    const preview = document.getElementById('barcodePreview');
    const img = document.getElementById('barcodePreviewImg');
    const fallback = document.getElementById('barcodePreviewFallback');
    const nombre = document.getElementById('barcodePreviewNombre');
    const extra = document.getElementById('barcodePreviewExtra');
    const badge = document.getElementById('barcodePreviewBadge');
    const btn = document.getElementById('btnAgregarBarcode');

    if (!preview) return;

    preview.classList.remove('hidden');

    if (!code) {
        productoBarcodeActual = null;
        if (img) { img.src = ''; img.classList.add('hidden'); }
        if (fallback) fallback.classList.remove('hidden');
        if (nombre) nombre.textContent = '—';
        if (extra) extra.textContent = 'Escanea un código de barras…';
        if (badge) {
            badge.textContent = 'Esperando…';
            badge.className = 'inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-slate-200 text-slate-700 dark:bg-gray-700 dark:text-slate-200';
        }
        if (btn) btn.disabled = true;
        return;
    }

    if (!producto) {
        productoBarcodeActual = null;
        if (img) { img.src = ''; img.classList.add('hidden'); }
        if (fallback) fallback.classList.remove('hidden');
        if (nombre) nombre.textContent = 'No encontrado';
        if (extra) extra.textContent = `Código: ${code}`;
        if (badge) {
            badge.textContent = 'No existe';
            badge.className = 'inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300';
        }
        if (btn) btn.disabled = true;
        return;
    }

    productoBarcodeActual = producto;

    if (img && producto.imagen_url) {
        img.src = producto.imagen_url;
        img.classList.remove('hidden');
        if (fallback) fallback.classList.add('hidden');
    } else {
        if (img) { img.src = ''; img.classList.add('hidden'); }
        if (fallback) fallback.classList.remove('hidden');
    }

    if (nombre) nombre.textContent = producto.nombre || 'Producto';
    const desc = producto.descripcion ? `• ${producto.descripcion}` : '';
    const cb = (producto.codigo_barras || producto.codigo_barra) ? `CB: ${(producto.codigo_barras || producto.codigo_barra)}` : `CB: ${code}`;
    if (extra) extra.textContent = `${cb} ${desc}`.trim();

    if (badge) {
        badge.textContent = 'Listo';
        badge.className = 'inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300';
    }

    if (btn) btn.disabled = false;
}

function agregarProductoDesdeBarcode() {
    if (!productoBarcodeActual) return;

    // Agrega el producto como preseleccionado (sin select)
    agregarProducto(productoBarcodeActual, false);

    // limpiar y volver a enfocar para el siguiente escaneo
    const input = document.getElementById('barcodeInput');
    if (input) {
        input.value = '';
        input.focus();
    }
    mostrarPreviewBarcode(null, '');
}


/** =========================
 *  Acciones: Guardar / Facturar (split button)
 *  ========================= */
function closeAccionCompraMenu() {
    const menu = document.getElementById('menuAccionCompra');
    if (menu) menu.classList.add('hidden');
}

function toggleAccionCompraMenu() {
    const menu = document.getElementById('menuAccionCompra');
    if (!menu) return;
    menu.classList.toggle('hidden');
}

function setAccionCompra(valor) {
    const inp = document.getElementById('accion_compra');
    if (inp) inp.value = valor;
    syncAccionCompraUI();
    closeAccionCompraMenu();
    try { saveCompraDraftDebounced(); } catch (e) {}
}

function syncAccionCompraUI() {
    const inp = document.getElementById('accion_compra');
    const accion = inp ? (inp.value || 'guardar') : 'guardar';

    const lblGuardar = document.getElementById('labelAccionGuardarCompra');
    const lblFacturar = document.getElementById('labelAccionFacturarCompra');

    if (lblGuardar && lblFacturar) {
        if (accion === 'facturar') {
            lblGuardar.classList.add('hidden');
            lblFacturar.classList.remove('hidden');
        } else {
            lblFacturar.classList.add('hidden');
            lblGuardar.classList.remove('hidden');
        }
    }
}

function submitCompra() {
    const form = document.getElementById('formCompra');
    if (!form) return;
    // RequestSubmit mantiene validación HTML5 + dispara el listener submit existente
    if (typeof form.requestSubmit === 'function') {
        form.requestSubmit();
    } else {
        form.submit();
    }
}

/** =========================
 *  Atajos (F6 a F11) - referencia Ventas
 *  ========================= */
function handleHotkeysCompras(e) {
    if (e.defaultPrevented) return;

    const key = e.key;
    const allowed = ['F6','F7','F8','F9','F10','F11'];
    if (!allowed.includes(key)) return;

    e.preventDefault();

    if (key === 'F6') {
        const inp = document.getElementById('barcodeInput');
        if (inp) {
            inp.focus();
            inp.select?.();
            inp.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    if (key === 'F7') {
        if (typeof abrirModalProductos === 'function') abrirModalProductos();
    }

    if (key === 'F8') {
        const sel = document.getElementById('proveedor_id') || document.querySelector('select[name="proveedor_id"]');
        if (sel) {
            sel.focus();
            sel.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    if (key === 'F9') {
        document.getElementById('btnCrearProveedor')?.click();
    }

    if (key === 'F10') {
        const desc = document.getElementById('descuento');
        if (desc) {
            desc.focus();
            desc.select?.();
            desc.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    if (key === 'F11') {
        submitCompra();
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const __hasErrors = {{ $errors->any() ? 'true' : 'false' }};
    try {
        const flag = sessionStorage.getItem('compra_clear_after_submit');
        if (flag && !__hasErrors) {
            // Compra guardada OK: limpiar borrador y formulario
            resetCompra(true);
            try { localStorage.removeItem(COMPRA_VISTA_KEY); } catch (e) {}
            sessionStorage.removeItem('compra_clear_after_submit');
        } else if (flag && __hasErrors) {
            // Hubo errores: no limpiar, y descartar flag
            sessionStorage.removeItem('compra_clear_after_submit');
        }
    } catch (e) {}

    // Restaurar borrador (si existe) antes de inicializar UI compleja
    restoreCompraDraft();

    initResizableColumns();
    initReorderableColumns();
    // Inicializa contador en base a filas existentes (por seguridad)
    const existentes = document.querySelectorAll('[id^="producto_"]').length;
    contadorProductos = Math.max(contadorProductos, existentes);

    document.getElementById('btnCrearProveedor')?.addEventListener('click', abrirModalProveedor);

    // Acción split button: sincroniza UI, cierre por click fuera y cambio de checkbox
    syncAccionCompraUI();
    document.getElementById('mantener_en_pantalla')?.addEventListener('change', saveCompraDraftDebounced);
    document.getElementById('accion_compra')?.addEventListener('change', saveCompraDraftDebounced);

    document.addEventListener('click', (ev) => {
        const menu = document.getElementById('menuAccionCompra');
        const btn = ev.target.closest && ev.target.closest('[onclick="toggleAccionCompraMenu()"]');
        const insideMenu = menu && menu.contains(ev.target);
        if (!btn && !insideMenu) closeAccionCompraMenu();
    });

    window.addEventListener('keydown', handleHotkeysCompras);

    // Botón global "Limpiar" (header)
    window.addEventListener('compras-limpiar', () => resetCompra(true));

    // Autosave de campos de cabecera
    document.getElementById('proveedor_id')?.addEventListener('change', saveCompraDraftDebounced);
    document.querySelector('input[name="fecha"]')?.addEventListener('change', saveCompraDraftDebounced);
    document.querySelector('textarea[name="observaciones"]')?.addEventListener('input', saveCompraDraftDebounced);

    actualizarAvisosVacio();

    // Descuento (monto) aplicado al total: recalcula subtotal/total neto
    const descuentoInput = document.getElementById('descuento');
    if (descuentoInput) {
        descuentoInput.addEventListener('input', () => calcularTotalGeneral());
        descuentoInput.addEventListener('blur', () => {
            let v = parseFloat(descuentoInput.value || '0') || 0;
            if (v < 0) v = 0;
            if (v > 100) v = 100;
            descuentoInput.value = v.toFixed(2);
            calcularTotalGeneral();
        });
    }

    // Inicializar resumen al cargar
    calcularTotalGeneral();

    // Sortable (reordenar filas/cards) - compatible mouse/táctil
    if (window.Sortable) {
        const tbody = document.getElementById('detallesTabla');
        if (tbody) {
            new Sortable(tbody, {
                animation: 150,
                handle: '.drag-handle',
                ghostClass: 'bg-yellow-50',
                onEnd: () => {
                    // el orden visual cambia; los índices internos se mantienen (no afecta el submit)
                }
            });
        }

        const cards = document.getElementById('detallesFormulario');
        if (cards) {
            new Sortable(cards, {
                animation: 150,
                handle: '.card-drag-handle',
                ghostClass: 'bg-yellow-50',
                onEnd: () => {}
            });
        }
    }

// Escaneo por código de barras (pistola)
const barcodeInput = document.getElementById('barcodeInput');
const btnBarcode = document.getElementById('btnAgregarBarcode');
if (btnBarcode) btnBarcode.disabled = true;

if (barcodeInput) {
    // al escribir/escaneo
    barcodeInput.addEventListener('input', (e) => {
        const code = normalizarBarcode(e.target.value);
        const prod = buscarProductoPorBarcode(code);
        mostrarPreviewBarcode(prod, code);
    });

    // ENTER suele venir desde la pistola
    barcodeInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            if (productoBarcodeActual) agregarProductoDesdeBarcode();
        }
    });

    // estado inicial
    mostrarPreviewBarcode(null, '');
}

if (btnBarcode) {
    btnBarcode.addEventListener('click', () => {
        agregarProductoDesdeBarcode();
    });
}


// Validación antes de guardar (mínimo 1 producto + precio en 0)
const formCompra = document.getElementById('formCompra');
if (formCompra) {
    formCompra.addEventListener('submit', (e) => {
        // Sincroniza estado visible -> hidden antes de enviar
        capturarIndicesDesdeDOM();
        capturarEstadoActual();
        indicesActivos.forEach(i => sincronizarHiddenDesdeVisibles(i));

        if (indicesActivos.length === 0) {
            e.preventDefault();
            alert('Agrega al menos un producto para registrar la compra.');
            return;
        }

        // Confirmación si hay precios en 0
        if (!permitirSubmitPrecioCero) {
            const nombresCero = [];
            indicesActivos.forEach(i => {
                const row = document.getElementById(`producto_${i}`);
                if (!row) return;
                const precioEl = row.querySelector(`.input-precio-${i}`);
                const precio = parseFloat((precioEl ? precioEl.value : '0') || '0') || 0;
                if (precio <= 0) nombresCero.push(obtenerNombreProducto(i));
            });

            if (nombresCero.length > 0) {
                e.preventDefault();
                abrirModalPrecioCero(nombresCero);
                return;
            }
        }
        // Marca para limpiar el formulario una vez la compra se guarde correctamente
        try { sessionStorage.setItem('compra_clear_after_submit', '1'); } catch (e) {}
    });
}
    // Pre-render del catálogo
    renderCatalogoProductos();
    actualizarEstadoCatalogo();
});
</script>
@endpush
