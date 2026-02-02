@extends('layouts.app')

@section('title', 'Editar Compra')

@section('header')
    Editar Compra
@endsection

@section('page-actions')
    <div>
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white">
            Editar Compra de Inventario
        </h2>
        <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
            Modifica los productos ingresados al inventario (Compra #{{ $compra->id }})
        </p>
    </div>
    <div class="flex gap-3">
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
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

    {{-- Errores --}}
    @if ($errors->any())
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-red-700">
            <p class="font-bold mb-2">Hay errores en el formulario:</p>
            <ul class="list-disc ml-5 text-sm space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="formCompra" method="POST" action="{{ route('compras.update', $compra) }}" class="space-y-6 -mt-2">
        @csrf
        @method('PUT')

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-amber-50 to-white dark:from-gray-800 dark:to-gray-800/50">
        <div class="flex items-center space-x-3">
            <div class="p-2 bg-amber-100 dark:bg-amber-900/30 rounded-lg">
                <svg class="w-6 h-6 text-amber-700 dark:text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h10M7 12h10M7 17h10M5 5h14v14H5z"/>
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Escanear producto</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400">Escanea con tu pistola el código de barras y luego presiona “Agregar”.</p>
            </div>
        </div>
    </div>

    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <div class="md:col-span-2">
                <label for="barcodeInput" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Código de barras</label>
                <input id="barcodeInput" type="text" inputmode="numeric" autocomplete="off"
                       class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                       placeholder="Haz clic aquí y escanea…">
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Tip: muchos escáneres envían ENTER al final; aquí también funciona.</p>
            </div>

            <div class="md:text-right">
                <button type="button" id="btnAgregarBarcode"
                        class="inline-flex items-center justify-center w-full md:w-auto px-5 py-2.5 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white font-semibold rounded-lg shadow-sm transition-all duration-200 hover:shadow-md disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Agregar
                </button>
            </div>
        </div>

        <!-- Preview -->
        <div id="barcodePreview" class="mt-4 hidden">
            <div class="flex items-center gap-4 p-4 rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800/50">
                <div class="w-14 h-14 rounded-lg overflow-hidden bg-white dark:bg-gray-700 border border-slate-200 dark:border-gray-600 flex items-center justify-center">
                    <img id="barcodePreviewImg" src="" alt="Producto" class="w-full h-full object-cover hidden">
                    <svg id="barcodePreviewFallback" class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 11h18M7 15h10M7 19h10"/>
                    </svg>
                </div>

                <div class="min-w-0 flex-1">
                    <p id="barcodePreviewNombre" class="font-semibold text-slate-900 dark:text-white truncate">—</p>
                    <p id="barcodePreviewExtra" class="text-sm text-slate-600 dark:text-slate-400 truncate">—</p>
                </div>

                <span id="barcodePreviewBadge" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-slate-200 text-slate-700 dark:bg-gray-700 dark:text-slate-200">
                    Esperando…
                </span>
            </div>
        </div>
    </div>
</div>

        

        {{-- Datos generales --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-blue-50 to-white dark:from-gray-800 dark:to-gray-800/50">
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

            <div class="p-6">

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Proveedor</label>
                    <select name="proveedor_id" required
                            class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                        <option value="">Seleccionar proveedor...</option>
                        @foreach($proveedores as $p)
                            <option value="{{ $p->id }}" @selected(old('proveedor_id', $compra->proveedor_id) == $p->id)>{{ $p->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Fecha</label>
                    <input type="date" name="fecha" value="{{ old('fecha', optional($compra->fecha)->format('Y-m-d') ?? $compra->fecha ?? now()->toDateString()) }}"
                           class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="md:text-right">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Total</label>
                    <div class="inline-flex items-center justify-end w-full md:w-auto px-4 py-2 rounded-xl bg-gradient-to-r from-blue-500 to-blue-600 text-white font-bold">
                        S/ <span id="totalDisplay" class="ml-2">0.00</span>
                    </div>
                    <input type="hidden" name="total" id="total" value="0">
                </div>
            </div>
            </div>
        </div>

        {{-- Detalle --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-emerald-50 to-white dark:from-gray-800 dark:to-gray-800/50">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
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
                <div class="flex gap-2">
                    <button type="button" onclick="abrirModalProductos()"
                            class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-semibold shadow-sm">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Agregar producto
                    </button>

                    <button type="button" onclick="cambiarVista('tabla')" id="btnVistaTabla"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg font-semibold shadow-sm">
                        Tabla
                    </button>
                    <button type="button" onclick="cambiarVista('formulario')" id="btnVistaFormulario"
                            class="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-slate-700 dark:text-slate-200 rounded-lg font-semibold shadow-sm">
                        Cards
                    </button>
                </div>
            </div>

            <div class="p-6">

            {{-- Vista Tabla --}}
            <div id="vistaTabla" class="vista-contenido">
                <div class="overflow-x-auto overflow-y-visible rounded-xl border border-gray-200 dark:border-gray-700">
                    <table class="min-w-[1100px] w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-900/40">
                        <tr class="text-left text-slate-700 dark:text-slate-300">
                            <th class="px-2 py-3 w-10 text-center"></th>
                            <th class="px-3 py-3">Producto</th>
                            <th class="px-3 py-3">Presentación</th>
                            <th class="px-3 py-3 text-center w-28">Unid/Pres</th>
                            <th class="px-3 py-3 w-28 text-right">Cantidad</th>
                            <th class="px-3 py-3 text-center w-28">Total unid</th>
                            <th class="px-3 py-3 w-36">Lote</th>
                            <th class="px-3 py-3 w-36">Venc.</th>
                            <th class="px-3 py-3 w-40 text-right">Precio unit</th>
                            <th class="px-3 py-3 w-40 text-right">Subtotal</th>
                            <th class="px-3 py-3 w-16 text-center"></th>
                        </tr>
                        </thead>
                        <tbody id="detallesTabla" class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800"></tbody>
                    </table>
                </div>

                <div id="avisoSinProductos" class="mt-4 text-sm text-slate-600 dark:text-slate-400">
                    Aún no has agregado productos.
                </div>
            </div>

            {{-- Vista Cards --}}
            <div id="vistaFormulario" class="vista-contenido hidden">
                <div id="detallesFormulario" class="grid grid-cols-1 md:grid-cols-2 gap-4"></div>
                <div id="avisoSinProductosCards" class="mt-4 text-sm text-slate-600 dark:text-slate-400">
                    Aún no has agregado productos.
                </div>
            </div>
            </div>
        </div>


        {{-- Motivo de modificación --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-amber-50 to-white dark:from-gray-800 dark:to-gray-800/50">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-amber-100 dark:bg-amber-900/30 rounded-lg">
                        <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 20h9"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.5 3.5a2.1 2.1 0 013 3L7 19l-4 1 1-4L16.5 3.5z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Motivo de modificación</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Obligatorio para registrar la modificación.</p>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Motivo</label>
                <textarea name="motivo" required rows="3"
                          class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500"
                          placeholder="Ej: corrección de lote, ajuste de cantidad, cambio de proveedor...">{{ old('motivo') }}</textarea>
                <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Este motivo quedará registrado en el historial de la compra.</p>
                @error('motivo')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Botones --}}
        <div class="flex items-center justify-between sticky bottom-0 bg-white dark:bg-gray-800 p-4 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700">
            <a href="{{ route('compras.index') }}"
               class="px-6 py-3 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-slate-700 dark:text-slate-300 font-semibold rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                Cancelar
            </a>
            <button type="submit"
                    class="inline-flex items-center px-8 py-3 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-semibold rounded-lg shadow-sm transition-all hover:shadow-md hover:scale-105">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                Guardar cambios
            </button>
        </div>
    </form>
</div>

{{-- MODAL PRODUCTOS --}}
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
                 class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 max-h-[60vh] overflow-auto pr-1">
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
            'codigo_barras' => $p->codigo_barras ?? null,
            'imagen_url' => $imgUrl,
        ];
    })->values();


    $detallesData = $compra->detalles->map(function($d){
        return [
            'producto_id' => $d->producto_id,
            'presentacion_id' => $d->presentacion_id,
            'tipo_presentacion' => $d->tipo_presentacion,
            'unidades_por_presentacion' => (int)($d->unidades_por_presentacion ?? 1),
            'cantidad_presentaciones' => (int)($d->cantidad_presentaciones ?? 1),
            'numero_lote' => optional($d->lote)->numero_lote,
            'fecha_vencimiento' => optional(optional($d->lote)->fecha_vencimiento)->format('Y-m-d') ?? optional($d->lote)->fecha_vencimiento ?? null,
            'precio_unitario' => (float)($d->precio_unitario ?? 0),
        ];
    })->values();
@endphp
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
/** ================================
 *  DATA
 *  ================================= */
const productosData = @json($productosData);
const detallesData = @json($detallesData);


let contadorProductos = 0;
let vistaActual = 'tabla';

function cambiarVista(vista) {
    vistaActual = vista;

    document.getElementById('vistaTabla').classList.toggle('hidden', vista !== 'tabla');
    document.getElementById('vistaFormulario').classList.toggle('hidden', vista !== 'formulario');

    document.getElementById('btnVistaTabla').classList.toggle('bg-blue-600', vista === 'tabla');
    document.getElementById('btnVistaTabla').classList.toggle('text-white', vista === 'tabla');
    document.getElementById('btnVistaTabla').classList.toggle('bg-white', vista !== 'tabla');

    document.getElementById('btnVistaFormulario').classList.toggle('bg-blue-600', vista === 'formulario');
    document.getElementById('btnVistaFormulario').classList.toggle('text-white', vista === 'formulario');
    document.getElementById('btnVistaFormulario').classList.toggle('bg-white', vista !== 'formulario');
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
    precargarDetallesEdicion();
    actualizarEstadoCatalogo();
}

function cerrarModalProductos() {
    document.getElementById('modalProductos').classList.add('hidden');
}

function filtrarCatalogoProductos(valor) {
    catalogoFiltro = (valor || '').toLowerCase().trim();
    renderCatalogoProductos();
    precargarDetallesEdicion();
}

function limpiarSeleccionCatalogo() {
    catalogoSeleccionados = new Set();
    renderCatalogoProductos();
    precargarDetallesEdicion();
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
    const cb = (p.codigo_barras || '').toLowerCase();
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
                            <span class="font-mono">${(p.codigo_barras || '—')}</span>
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
}


let precargaDetallesEjecutada = false;

async function precargarDetallesEdicion() {
    if (precargaDetallesEjecutada) return;
    if (!Array.isArray(detallesData) || detallesData.length === 0) return;

    precargaDetallesEjecutada = true;

    // Forzamos vista tabla por defecto al entrar (puedes cambiarlo si quieres)
    vistaActual = 'tabla';
    cambiarVista('tabla');

    for (const det of detallesData) {
        const producto = productosData.find(p => String(p.id) === String(det.producto_id));
        if (!producto) continue;

        const index = contadorProductos;
        agregarProducto(producto, false);

        const row = document.getElementById(`producto_${index}`);
        if (!row) continue;

        // Inputs visibles
        const qty = row.querySelector(`.input-cantidad-${index}`);
        if (qty) qty.value = det.cantidad_presentaciones ?? 1;

        const lote = row.querySelector(`.input-lote-${index}`);
        if (lote) lote.value = det.numero_lote ?? '';

        const vence = row.querySelector(`.input-vence-${index}`);
        if (vence && det.fecha_vencimiento) vence.value = det.fecha_vencimiento;

        const precio = row.querySelector(`.input-precio-${index}`);
        if (precio) precio.value = det.precio_unitario ?? 0;

        // Hidden inputs (por si no se disparan eventos)
        const hidQty = row.querySelector(`input[name="productos[${index}][cantidad_presentaciones]"]`);
        if (hidQty) hidQty.value = det.cantidad_presentaciones ?? 1;

        const hidLote = row.querySelector(`input[name="productos[${index}][numero_lote]"]`);
        if (hidLote) hidLote.value = det.numero_lote ?? '';

        const hidVence = row.querySelector(`input[name="productos[${index}][fecha_vencimiento]"]`);
        if (hidVence) hidVence.value = det.fecha_vencimiento ?? '';

        const hidPrecio = row.querySelector(`input[name="productos[${index}][precio_unitario]"]`);
        if (hidPrecio) hidPrecio.value = det.precio_unitario ?? 0;

        // Presentaciones
        await cargarPresentaciones(det.producto_id, index);

        const selPres = row.querySelector(`.select-presentacion-${index}`);
        if (selPres) {
            selPres.value = det.presentacion_id ? String(det.presentacion_id) : '';
            await alSeleccionarPresentacion(index, true);
        }

        calcularTotales(index);
    }

    actualizarAvisosVacio();
}

function agregarProductoTabla(index, productoPreseleccionado, usarSelect) {
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

        <td class="px-3 py-3 align-top">${selectProducto}</td>

        <td class="px-3 py-3 align-top">
            <div class="space-y-2">
                <select class="select-presentacion-${index} w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500"
                        onchange="alSeleccionarPresentacion(${index})">
                    <option value="">Cargando...</option>
                </select>
                <a href="javascript:void(0)" onclick="abrirModalNuevaPresentacion(${index})"
                   class="text-xs font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 underline">
                    + Crear presentación
                </a>
            </div>
        </td>

        <td class="px-3 py-3 text-center align-top">
            <span class="span-unidades-${index} inline-flex items-center justify-center px-3 py-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-sm font-bold text-slate-900 dark:text-white">1</span>
            <input type="hidden" class="input-unidades-${index}" value="1">
        </td>

        <td class="px-3 py-3 align-top">
            <input type="number"
                   class="input-cantidad-${index} w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-right text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500"
                   min="1" value="1" placeholder="Ej: 5"
                   title="Cantidad de presentaciones (Ej: 5 cajas)."
                   oninput="calcularTotales(${index})" required>
        </td>

        <td class="px-3 py-3 text-center align-top">
            <span class="span-total-${index} inline-flex items-center px-3 py-1.5 rounded-lg bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-200 font-bold">1</span>
        </td>

        <td class="px-3 py-3 align-top">
            <input type="text"
                   class="input-lote-${index} w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-mono text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:ring-2 focus:ring-blue-500"
                   placeholder="LOT-2025-001" required>
        </td>

        <td class="px-3 py-3 align-top">
            <input type="date"
                   class="input-vence-${index} w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500"
                    required>
        </td>

        <td class="px-3 py-3 align-top">
            <div class="space-y-1">
                <div class="relative">
                    <span class="absolute inset-y-0 left-3 flex items-center text-slate-500 dark:text-slate-400 text-sm font-semibold">S/</span>
                    <input type="number"
                           class="input-precio-${index} w-full pl-8 pr-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-right text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500"
                           step="0.01" min="0"
                           value="${productoPreseleccionado ? (productoPreseleccionado.precio_compra || 0) : 0}"
                           oninput="calcularTotales(${index})" required>
                </div>
                <div class="text-xs text-slate-500 dark:text-slate-400 text-right">
                    Por pres: <span class="span-precio-pres-${index} font-semibold text-slate-700 dark:text-slate-200">S/ 0.00</span>
                </div>
            </div>
        </td>

        <td class="px-3 py-3 text-right align-top">
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
        </td>

        <input type="hidden" name="productos[${index}][presentacion_id]" value="">
        <input type="hidden" name="productos[${index}][tipo_presentacion]" value="">
        <input type="hidden" name="productos[${index}][unidades_por_presentacion]" value="1" class="hidden-unidades-${index}">
        <input type="hidden" name="productos[${index}][cantidad_presentaciones]" value="1">
        <input type="hidden" name="productos[${index}][numero_lote]" value="">
        <input type="hidden" name="productos[${index}][fecha_vencimiento]" value="">
        <input type="hidden" name="productos[${index}][precio_unitario]" value="0">
    `;

    tbody.appendChild(row);

    if (productoPreseleccionado && !usarSelect) {
        cargarPresentaciones(productoPreseleccionado.id, index);
    }

    asociarEventos(index);
    calcularTotales(index);
}

function agregarProductoCard(index, productoPreseleccionado, usarSelect) {
    const container = document.getElementById('detallesFormulario');
    const card = document.createElement('div');
    card.id = `producto_${index}`;
    card.className = 'bg-white dark:bg-gray-700 rounded-xl border border-gray-200 dark:border-gray-600 p-5 shadow-sm';
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
                    <a href="javascript:void(0)" onclick="abrirModalNuevaPresentacion(${index})"
                       class="text-xs font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 underline">
                        + Crear presentación
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div class="text-center">
                    <div class="text-xs font-semibold text-slate-500 dark:text-slate-300 uppercase">Unid/Pres</div>
                    <div class="mt-1 span-unidades-${index} font-bold text-slate-900 dark:text-white">1</div>
                    <input type="hidden" class="input-unidades-${index}" value="1">
                </div>

                <div>
                    <div class="text-xs font-semibold text-slate-500 dark:text-slate-300 uppercase mb-1">Cantidad</div>
                    <input type="number" min="1" value="1"
                           class="input-cantidad-${index} w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-right text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500"
                           placeholder="Ej: 5"
                           title="Cantidad de presentaciones (Ej: 5 cajas)."
                           oninput="calcularTotales(${index})" required>
                </div>

                <div class="text-center">
                    <div class="text-xs font-semibold text-slate-500 dark:text-slate-300 uppercase">Total unid</div>
                    <div class="mt-1 span-total-${index} font-bold text-purple-700 dark:text-purple-200">1</div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <div class="text-xs font-semibold text-slate-500 dark:text-slate-300 uppercase mb-1">Lote</div>
                    <input type="text"
                           class="input-lote-${index} w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-mono text-slate-900 dark:text-white"
                           placeholder="LOT-2025-001" required>
                </div>
                <div>
                    <div class="text-xs font-semibold text-slate-500 dark:text-slate-300 uppercase mb-1">Venc.</div>
                    <input type="date"
                           class="input-vence-${index} w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-slate-900 dark:text-white"
                            required>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 items-end">
                <div>
                    <div class="text-xs font-semibold text-slate-500 dark:text-slate-300 uppercase mb-1">Precio unit</div>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-3 flex items-center text-slate-500 dark:text-slate-400 text-sm font-semibold">S/</span>
                        <input type="number" step="0.01" min="0"
                               class="input-precio-${index} w-full pl-8 pr-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-right text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500"
                               value="${productoPreseleccionado ? (productoPreseleccionado.precio_compra || 0) : 0}"
                               oninput="calcularTotales(${index})" required>
                    </div>
                    <div class="text-xs text-slate-500 dark:text-slate-400 text-right mt-1">
                        Por pres: <span class="span-precio-pres-${index} font-semibold text-slate-700 dark:text-slate-200">S/ 0.00</span>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-xs font-semibold text-slate-500 dark:text-slate-300 uppercase mb-1">Subtotal</div>
                    <div class="inline-flex items-center px-3 py-2 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-200 font-bold">
                        S/ <span class="span-subtotal-${index} ml-1">0.00</span>
                    </div>
                </div>
            </div>
        </div>

        <input type="hidden" name="productos[${index}][presentacion_id]" value="">
        <input type="hidden" name="productos[${index}][tipo_presentacion]" value="">
        <input type="hidden" name="productos[${index}][unidades_por_presentacion]" value="1" class="hidden-unidades-${index}">
        <input type="hidden" name="productos[${index}][cantidad_presentaciones]" value="1">
        <input type="hidden" name="productos[${index}][numero_lote]" value="">
        <input type="hidden" name="productos[${index}][fecha_vencimiento]" value="">
        <input type="hidden" name="productos[${index}][precio_unitario]" value="0">
    `;

    container.appendChild(card);

    if (productoPreseleccionado && !usarSelect) {
        cargarPresentaciones(productoPreseleccionado.id, index);
    }

    asociarEventos(index);
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
    const unidadesInput = row.querySelector(`.input-unidades-${index}`);
    const cantidad = row.querySelector(`.input-cantidad-${index}`);

    if (lote) lote.addEventListener('input', () => {
        const hidden = row.querySelector(`input[name="productos[${index}][numero_lote]"]`);
        if (hidden) hidden.value = lote.value;
    });

    if (vence) vence.addEventListener('input', () => {
        const hidden = row.querySelector(`input[name="productos[${index}][fecha_vencimiento]"]`);
        if (hidden) hidden.value = vence.value;
    });

    if (precio) precio.addEventListener('input', () => {
        const hidden = row.querySelector(`input[name="productos[${index}][precio_unitario]"]`);
        if (hidden) hidden.value = precio.value;
    });

    if (unidadesInput) unidadesInput.addEventListener('input', () => {
        const hidden = row.querySelector(`input[name="productos[${index}][unidades_por_presentacion]"]`);
        if (hidden) hidden.value = unidadesInput.value;
    });

    if (cantidad) cantidad.addEventListener('input', () => {
        const hidden = row.querySelector(`input[name="productos[${index}][cantidad_presentaciones]"]`);
        if (hidden) hidden.value = cantidad.value;
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
        const selectElement = document.querySelector(`#producto_${index} .select-presentacion-${index}`);
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
    const selectElement = document.querySelector(`#producto_${index} .select-presentacion-${index}`);
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

        // aplicar unidad base por defecto
        selectElement.value = '';

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
            precioInput.value = (presPrecio / unidades).toFixed(2);
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
    const precioUnit = parseFloat(row.querySelector(`.input-precio-${index}`)?.value || '0') || 0;

    const totalUnid = unidades * cantidad;
    const precioPres = precioUnit * unidades;
    const subtotal = precioPres * cantidad;

    // UI
    const spanTotal = row.querySelector(`.span-total-${index}`);
    if (spanTotal) spanTotal.textContent = String(totalUnid);

    const spanPrecioPres = row.querySelector(`.span-precio-pres-${index}`);
    if (spanPrecioPres) spanPrecioPres.textContent = `S/ ${precioPres.toFixed(2)}`;

    const spanSub = row.querySelector(`.span-subtotal-${index}`);
    if (spanSub) spanSub.textContent = subtotal.toFixed(2);

    // Hidden
    row.querySelector(`input[name="productos[${index}][cantidad_presentaciones]"]`).value = String(cantidad);
    row.querySelector(`input[name="productos[${index}][precio_unitario]"]`).value = String(precioUnit);

    calcularTotalGeneral();
}

function calcularTotalGeneral() {
    const subtotales = document.querySelectorAll(`[id^="producto_"] .span-subtotal-0, [id^="producto_"] [class*="span-subtotal-"]`);
    // La línea de arriba es un fallback; calculamos directamente iterando rows:
    let total = 0;
    document.querySelectorAll('#detallesTabla tr, #detallesFormulario > div').forEach(el => {
        const idx = el.dataset.productoIndex;
        if (idx === undefined) return;
        const span = el.querySelector(`.span-subtotal-${idx}`);
        const val = parseFloat(span?.textContent || '0') || 0;
        total += val;
    });

    document.getElementById('totalDisplay').textContent = total.toFixed(2);
    document.getElementById('total').value = total.toFixed(2);
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


// =======================
// Escaneo por código de barras (pistola)
// =======================
let productoBarcodeActual = null;

function normalizarBarcode(v) {
    return (v || '').toString().trim();
}

function buscarProductoPorBarcode(code) {
    const c = normalizarBarcode(code);
    if (!c) return null;
    // búsqueda exacta por código de barras (producto base)
    return productosData.find(p => normalizarBarcode(p.codigo_barras) === c) || null;
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
    const cb = producto.codigo_barras ? `CB: ${producto.codigo_barras}` : `CB: ${code}`;
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


document.addEventListener('DOMContentLoaded', () => {
    actualizarAvisosVacio();

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

    // Pre-render del catálogo
    renderCatalogoProductos();
    precargarDetallesEdicion();
    actualizarEstadoCatalogo();
});
</script>
@endpush
