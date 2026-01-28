@extends('layouts.app')

@section('title', 'Modificar Compra')

@section('header')
    Modificar Compra
@endsection

@section('page-actions')
    <div>
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white">
            Modificar Compra #{{ $compra->id }}
        </h2>
        <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
            Se creará una nueva versión de esta compra
        </p>
    </div>
    <div class="flex gap-3">
        <a href="{{ route('compras.show', $compra) }}" 
           class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-slate-700 dark:text-slate-300 font-semibold rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200 shadow-sm">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Cancelar
        </a>
    </div>
@endsection

@section('content')

<!-- Alerta Informativa -->
<div class="mb-6 bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-400 dark:border-blue-600 p-4 rounded-lg">
    <div class="flex">
        <svg class="h-6 w-6 text-blue-600 dark:text-blue-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
        </svg>
        <div>
            <p class="text-sm font-semibold text-blue-800 dark:text-blue-300">Importante: Modificación de Compra</p>
            <p class="text-sm text-blue-700 dark:text-blue-400 mt-1">
                Al modificar esta compra se creará un nuevo registro (#{{ $compra->id }}). La compra original se marcará como "Modificada" y se mantendrá para auditoría.
            </p>
        </div>
    </div>
</div>

<form action="{{ route('compras.update', $compra) }}" method="POST" id="formCompra" class="max-w-7xl mx-auto space-y-6">
    @csrf
    @method('PUT')

    <!-- Motivo de Modificación -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-red-50 to-white dark:from-gray-800 dark:to-gray-800/50">
            <div class="flex items-center space-x-3">
                <div class="p-2 bg-red-100 dark:bg-red-900/30 rounded-lg">
                    <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Motivo de Modificación</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Explica por qué se modifica esta compra</p>
                </div>
            </div>
        </div>
        
        <div class="p-6">
            <label for="motivo" class="block text-sm font-medium text-slate-900 dark:text-white mb-2">
                Motivo <span class="text-red-500">*</span>
            </label>
            <textarea name="motivo" 
                      id="motivo"
                      rows="3"
                      class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 @error('motivo') border-red-500 @enderror"
                      placeholder="Ej: Error en cantidades ingresadas, cambio de proveedor, corrección de precios..."
                      required>{{ old('motivo') }}</textarea>
            @error('motivo')
                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <!-- Información General -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-blue-50 to-white dark:from-gray-800 dark:to-gray-800/50">
            <div class="flex items-center space-x-3">
                <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Información General</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Datos de la compra y proveedor</p>
                </div>
            </div>
        </div>
        
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <!-- Fecha -->
                <div>
                    <label for="fecha" class="block text-sm font-medium text-slate-900 dark:text-white mb-2">
                        Fecha de Compra <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <input type="date" 
                               name="fecha" 
                               id="fecha"
                               value="{{ old('fecha', $compra->fecha->format('Y-m-d')) }}"
                               max="{{ date('Y-m-d') }}"
                               class="block w-full pl-10 pr-3 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('fecha') border-red-500 @enderror"
                               required>
                    </div>
                    @error('fecha')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Proveedor -->
                <div>
                    <label for="proveedor_id" class="block text-sm font-medium text-slate-900 dark:text-white mb-2">
                        Proveedor <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        </div>
                        <select name="proveedor_id" 
                                id="proveedor_id"
                                class="block w-full pl-10 pr-3 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('proveedor_id') border-red-500 @enderror"
                                required>
                            <option value="">Seleccione un proveedor</option>
                            @foreach($proveedores as $proveedor)
                                <option value="{{ $proveedor->id }}" {{ old('proveedor_id', $compra->proveedor_id) == $proveedor->id ? 'selected' : '' }}>
                                    {{ $proveedor->nombre }} - RUC: {{ $proveedor->ruc }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @error('proveedor_id')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <!-- Detalle de Productos -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-green-50 to-white dark:from-gray-800 dark:to-gray-800/50">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-green-100 dark:bg-green-900/30 rounded-lg">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Detalle de Productos</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Modifica productos y cantidades</p>
                    </div>
                </div>
                <button type="button" 
                        onclick="agregarProducto()"
                        class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-semibold rounded-lg shadow-sm transition-all duration-200 hover:shadow-md hover:scale-105">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Agregar Producto
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700" id="tablaProductos">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                       <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">Producto</th>
        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">Presentación</th>
        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider">Cant. Pres.</th>
        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider">Unid. Totales</th>
        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider">P. Unit. (Base)</th>
        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">Lote / Vence</th>
        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider">Subtotal</th>
        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider"></th>
                            <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                            </svg>
                        </th>
                    </tr>
                </thead>
                <tbody id="detallesCompra" class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                    <!-- Se cargarán los productos vía JavaScript -->
                </tbody>
            </table>
        </div>

        <div id="mensajeSinProductos" class="hidden p-12 text-center bg-gray-50 dark:bg-gray-800/50">
            <svg class="mx-auto h-16 w-16 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
            </svg>
            <p class="mt-4 text-slate-500 dark:text-slate-400 font-medium">No hay productos agregados</p>
            <p class="text-sm text-slate-400 dark:text-slate-500 mt-1">Haz clic en "Agregar Producto" para comenzar</p>
        </div>
    </div>

    <!-- Totales -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-purple-50 to-white dark:from-gray-800 dark:to-gray-800/50">
            <div class="flex items-center space-x-3">
                <div class="p-2 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Resumen de Compra</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Total calculado automáticamente</p>
                </div>
            </div>
        </div>
        
        <div class="p-6">
            <div class="relative overflow-hidden bg-gradient-to-br from-blue-500 to-blue-600 dark:from-blue-600 dark:to-blue-700 rounded-xl p-6 border border-blue-400 dark:border-blue-500">
                <div class="relative z-10">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-sm font-semibold text-blue-100 uppercase tracking-wide">Total de Compra</p>
                        <svg class="w-6 h-6 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <p class="text-4xl font-bold text-white">
                        S/ <span id="totalDisplay">0.00</span>
                    </p>
                    <input type="hidden" name="total" id="total" value="0">
                </div>
                <div class="absolute top-0 right-0 -mt-8 -mr-8 w-32 h-32 bg-blue-400/20 rounded-full"></div>
            </div>
        </div>
    </div>

    <!-- Botones de Acción -->
    <div class="flex items-center justify-end gap-3 sticky bottom-0 bg-white dark:bg-gray-800 p-4 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700">
        <a href="{{ route('compras.show', $compra) }}" 
           class="px-6 py-3 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-slate-700 dark:text-slate-300 font-semibold rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors duration-200">
            Cancelar
        </a>
        <button type="submit" 
                class="inline-flex items-center px-8 py-3 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-semibold rounded-lg shadow-sm transition-all duration-200 hover:shadow-md hover:scale-105">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            Guardar Modificación
        </button>
    </div>
</form>

@push('scripts')
<script>
let contadorProductos = 0;
const productos = @json($productos);
const detallesExistentes = @json($compra->detalles);

// Cargar productos existentes al iniciar
document.addEventListener('DOMContentLoaded', function() {
    detallesExistentes.forEach(detalle => {
        cargarProductoExistente(detalle);
    });
});

function cargarProductoExistente(detalle) {
    const tbody = document.getElementById('detallesCompra');
    const index = contadorProductos++;
    const row = document.createElement('tr');
    row.id = `producto_${index}`;
    row.className = 'hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150';
    row.innerHTML = `
        <td class="px-4 py-3">
            <select name="productos[${index}][producto_id]" 
                    class="w-full min-w-[200px] rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:ring-2 focus:ring-blue-500 text-sm"
                    onchange="actualizarPrecio(${index})"
                    required>
                <option value="">Seleccionar...</option>
                ${productos.map(p => `<option value="${p.id}" data-precio="${p.precio_compra || 0}" ${p.id == detalle.producto_id ? 'selected' : ''}>${p.nombre}</option>`).join('')}
            </select>
        </td>
        <td class="px-4 py-3">
            <input type="text" 
                   name="productos[${index}][numero_lote]"
                   value="${detalle.lote.numero_lote}"
                   placeholder="LOT-2025-001"
                   class="w-full min-w-[120px] rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:ring-2 focus:ring-blue-500 text-sm font-mono"
                   required>
        </td>
        <td class="px-4 py-3">
            <input type="date" 
                   name="productos[${index}][fecha_vencimiento]"
                   value="${detalle.lote.fecha_vencimiento.split('T')[0]}"
                   min="{{ date('Y-m-d') }}"
                   class="w-full min-w-[140px] rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:ring-2 focus:ring-blue-500 text-sm"
                   required>
        </td>
        <td class="px-4 py-3">
            <input type="number" 
                   name="productos[${index}][cantidad]"
                   min="1"
                   value="${detalle.cantidad}"
                   class="w-full min-w-[80px] rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:ring-2 focus:ring-blue-500 text-sm text-right"
                   onchange="calcularSubtotalFila(${index})"
                   required>
        </td>
        <td class="px-4 py-3">
            <div class="relative">
                <span class="absolute inset-y-0 left-3 flex items-center text-slate-500 dark:text-slate-400 text-sm font-semibold">S/</span>
                <input type="number" 
                       name="productos[${index}][precio_unitario]"
                       step="0.01"
                       min="0"
                       value="${detalle.precio_unitario}"
                       class="w-full min-w-[100px] pl-8 pr-3 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:ring-2 focus:ring-blue-500 text-sm text-right"
                       onchange="calcularSubtotalFila(${index})"
                       required>
            </div>
        </td>
        <td class="px-4 py-3 text-right">
            <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 font-bold text-sm">
                S/ <span id="subtotal_${index}" class="ml-1">${(detalle.cantidad * detalle.precio_unitario).toFixed(2)}</span>
            </span>
        </td>
        <td class="px-4 py-3 text-center">
            <button type="button" 
                    onclick="eliminarProducto(${index})"
                    class="p-2 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors duration-200"
                    title="Eliminar">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
            </button>
        </td>
    `;
    
    tbody.appendChild(row);
    calcularTotales();
}

function agregarProducto() {
    const tbody = document.getElementById('detallesCompra');
    const mensaje = document.getElementById('mensajeSinProductos');
    mensaje.classList.add('hidden');
    
    const index = contadorProductos++;
    const row = document.createElement('tr');
    row.id = `producto_${index}`;
    row.className = 'hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150';
    row.innerHTML = `
        <td class="px-4 py-3">
            <select name="productos[${index}][producto_id]" 
                    class="w-full min-w-[200px] rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:ring-2 focus:ring-blue-500 text-sm"
                    onchange="actualizarPrecio(${index})"
                    required>
                <option value="">Seleccionar...</option>
                ${productos.map(p => `<option value="${p.id}" data-precio="${p.precio_compra || 0}">${p.nombre}</option>`).join('')}
            </select>
        </td>
        <td class="px-4 py-3">
            <input type="text" 
                   name="productos[${index}][numero_lote]"
                   placeholder="LOT-2025-001"
                   class="w-full min-w-[120px] rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:ring-2 focus:ring-blue-500 text-sm font-mono"
                   required>
        </td>
        <td class="px-4 py-3">
            <input type="date" 
                   name="productos[${index}][fecha_vencimiento]"
                   min="{{ date('Y-m-d') }}"
                   class="w-full min-w-[140px] rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:ring-2 focus:ring-blue-500 text-sm"
                   required>
        </td>
        <td class="px-4 py-3">
            <input type="number" 
                   name="productos[${index}][cantidad]"
                   min="1"
                   value="1"
                   class="w-full min-w-[80px] rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:ring-2 focus:ring-blue-500 text-sm text-right"
                   onchange="calcularSubtotalFila(${index})"
                   required>
        </td>
        <td class="px-4 py-3">
            <div class="relative">
                <span class="absolute inset-y-0 left-3 flex items-center text-slate-500 dark:text-slate-400 text-sm font-semibold">S/</span>
                <input type="number" 
                       name="productos[${index}][precio_unitario]"
                       step="0.01"
                       min="0"
                       value="0"
                       class="w-full min-w-[100px] pl-8 pr-3 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:ring-2 focus:ring-blue-500 text-sm text-right"
                       onchange="calcularSubtotalFila(${index})"
                       required>
            </div>
        </td>
        <td class="px-4 py-3 text-right">
            <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 font-bold text-sm">
                S/ <span id="subtotal_${index}" class="ml-1">0.00</span>
            </span>
        </td>
        <td class="px-4 py-3 text-center">
            <button type="button" 
                    onclick="eliminarProducto(${index})"
                    class="p-2 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors duration-200"
                    title="Eliminar">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
            </button>
        </td>
    `;
    
    tbody.appendChild(row);
}

function actualizarPrecio(index) {
    const select = document.querySelector(`select[name="productos[${index}][producto_id]"]`);
    const precioInput = document.querySelector(`input[name="productos[${index}][precio_unitario]"]`);
    
    if (select.selectedIndex > 0) {
        const precio = select.options[select.selectedIndex].dataset.precio;
        precioInput.value = parseFloat(precio || 0).toFixed(2);
        calcularSubtotalFila(index);
    }
}

function calcularSubtotalFila(index) {
    const cantidad = parseFloat(document.querySelector(`input[name="productos[${index}][cantidad]"]`).value) || 0;
    const precioUnitario = parseFloat(document.querySelector(`input[name="productos[${index}][precio_unitario]"]`).value) || 0;
    const subtotal = cantidad * precioUnitario;
    
    document.getElementById(`subtotal_${index}`).textContent = subtotal.toFixed(2);
    calcularTotales();
}

function eliminarProducto(index) {
    const row = document.getElementById(`producto_${index}`);
    row.style.opacity = '0';
    row.style.transform = 'scale(0.95)';
    setTimeout(() => {
        row.remove();
        calcularTotales();
        
        const tbody = document.getElementById('detallesCompra');
        if (tbody.children.length === 0) {
            document.getElementById('mensajeSinProductos').classList.remove('hidden');
        }
    }, 200);
}

function calcularTotales() {
    let total = 0;
    
    document.querySelectorAll('[id^="subtotal_"]').forEach(span => {
        total += parseFloat(span.textContent) || 0;
    });
    
    document.getElementById('total').value = total.toFixed(2);
    document.getElementById('totalDisplay').textContent = total.toFixed(2);
}

// Validar antes de enviar
document.getElementById('formCompra').addEventListener('submit', function(e) {
    const tbody = document.getElementById('detallesCompra');
    if (tbody.children.length === 0) {
        e.preventDefault();
        alert('⚠️ Debe agregar al menos un producto a la compra');
        return false;
    }
});

</script>
@endpush
@endsection