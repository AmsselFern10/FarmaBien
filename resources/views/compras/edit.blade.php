@extends('layouts.app')

@section('title', 'Modificar Compra')

@section('header')
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Modificar Compra #{{ $compra->id }}
        </h2>
        <a href="{{ route('compras.show', $compra) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Volver
        </a>
    </div>
@endsection

@section('content')

<!-- Alerta Informativa -->
<div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
    <div class="flex">
        <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
        </svg>
        <div class="ml-3">
            <p class="text-sm text-blue-700">
                <strong>Importante:</strong> Al modificar esta compra se creará un nuevo registro. La compra original (#{{ $compra->id }}) se marcará como "Modificada" y se mantendrá para auditoría.
            </p>
        </div>
    </div>
</div>

<form action="{{ route('compras.update', $compra) }}" method="POST" id="formCompra">
    @csrf
    @method('PUT')

    <!-- Motivo de Modificación -->
    <div class="bg-white overflow-hidden shadow-sm rounded-lg mb-6">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Motivo de Modificación</h3>
            
            <div>
                <label for="motivo" class="block text-sm font-medium text-gray-700 mb-1">
                    Explique el motivo de la modificación <span class="text-red-500">*</span>
                </label>
                <textarea name="motivo" 
                          id="motivo"
                          rows="3"
                          class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('motivo') border-red-500 @enderror"
                          placeholder="Ej: Error en cantidades ingresadas, cambio de proveedor, etc."
                          required>{{ old('motivo') }}</textarea>
                @error('motivo')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    <!-- Información General -->
    <div class="bg-white overflow-hidden shadow-sm rounded-lg mb-6">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Información General</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                
                <!-- Fecha -->
                <div>
                    <label for="fecha" class="block text-sm font-medium text-gray-700 mb-1">
                        Fecha de Compra <span class="text-red-500">*</span>
                    </label>
                    <input type="date" 
                           name="fecha" 
                           id="fecha"
                           value="{{ old('fecha', $compra->fecha->format('Y-m-d')) }}"
                           max="{{ date('Y-m-d') }}"
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('fecha') border-red-500 @enderror"
                           required>
                    @error('fecha')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Proveedor -->
                <div>
                    <label for="proveedor_id" class="block text-sm font-medium text-gray-700 mb-1">
                        Proveedor <span class="text-red-500">*</span>
                    </label>
                    <select name="proveedor_id" 
                            id="proveedor_id"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('proveedor_id') border-red-500 @enderror"
                            required>
                        <option value="">Seleccione un proveedor</option>
                        @foreach($proveedores as $proveedor)
                            <option value="{{ $proveedor->id }}" 
                                    {{ old('proveedor_id', $compra->proveedor_id) == $proveedor->id ? 'selected' : '' }}>
                                {{ $proveedor->nombre }} - {{ $proveedor->ruc }}
                            </option>
                        @endforeach
                    </select>
                    @error('proveedor_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tipo Comprobante -->
                <div>
                    <label for="tipo_comprobante" class="block text-sm font-medium text-gray-700 mb-1">
                        Tipo de Comprobante <span class="text-red-500">*</span>
                    </label>
                    <select name="tipo_comprobante" 
                            id="tipo_comprobante"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('tipo_comprobante') border-red-500 @enderror"
                            required>
                        <option value="">Seleccione</option>
                        <option value="factura" {{ old('tipo_comprobante', $compra->tipo_comprobante) == 'factura' ? 'selected' : '' }}>Factura</option>
                        <option value="boleta" {{ old('tipo_comprobante', $compra->tipo_comprobante) == 'boleta' ? 'selected' : '' }}>Boleta</option>
                        <option value="nota_credito" {{ old('tipo_comprobante', $compra->tipo_comprobante) == 'nota_credito' ? 'selected' : '' }}>Nota de Crédito</option>
                        <option value="guia_remision" {{ old('tipo_comprobante', $compra->tipo_comprobante) == 'guia_remision' ? 'selected' : '' }}>Guía de Remisión</option>
                    </select>
                    @error('tipo_comprobante')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Número de Comprobante -->
                <div>
                    <label for="numero_comprobante" class="block text-sm font-medium text-gray-700 mb-1">
                        N° de Comprobante <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="numero_comprobante" 
                           id="numero_comprobante"
                           value="{{ old('numero_comprobante', $compra->numero_comprobante) }}"
                           placeholder="001-0001234"
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('numero_comprobante') border-red-500 @enderror"
                           required>
                    @error('numero_comprobante')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Observaciones -->
                <div class="md:col-span-2">
                    <label for="observaciones" class="block text-sm font-medium text-gray-700 mb-1">
                        Observaciones
                    </label>
                    <textarea name="observaciones" 
                              id="observaciones"
                              rows="2"
                              class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('observaciones') border-red-500 @enderror">{{ old('observaciones', $compra->observaciones) }}</textarea>
                    @error('observaciones')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

            </div>
        </div>
    </div>

    <!-- Detalle de Productos -->
    <div class="bg-white overflow-hidden shadow-sm rounded-lg mb-6">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Detalle de Productos</h3>
                <button type="button" 
                        onclick="agregarProducto()"
                        class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded inline-flex items-center text-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Agregar Producto
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200" id="tablaProductos">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Producto</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">N° Lote</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">F. Vencimiento</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Cantidad</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">P. Unitario</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Acción</th>
                        </tr>
                    </thead>
                    <tbody id="detallesCompra" class="bg-white divide-y divide-gray-200">
                        <!-- Se cargarán los productos existentes -->
                    </tbody>
                </table>
            </div>

            <div id="mensajeSinProductos" class="hidden text-center py-8 text-gray-500">
                <p>No hay productos agregados</p>
            </div>
        </div>
    </div>

    <!-- Totales -->
    <div class="bg-white overflow-hidden shadow-sm rounded-lg mb-6">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Resumen de Compra</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-sm text-gray-600 mb-1">Subtotal</p>
                    <p class="text-2xl font-bold text-gray-900">S/ <span id="subtotalDisplay">0.00</span></p>
                    <input type="hidden" name="subtotal" id="subtotal" value="0">
                </div>

                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-sm text-gray-600 mb-1">IGV (18%)</p>
                    <p class="text-2xl font-bold text-gray-900">S/ <span id="igvDisplay">0.00</span></p>
                    <input type="hidden" name="igv" id="igv" value="0">
                </div>

                <div class="bg-blue-50 rounded-lg p-4 border-2 border-blue-200">
                    <p class="text-sm text-blue-600 mb-1">Total</p>
                    <p class="text-3xl font-bold text-blue-600">S/ <span id="totalDisplay">0.00</span></p>
                    <input type="hidden" name="total" id="total" value="0">
                </div>
            </div>
        </div>
    </div>

    <!-- Botones de Acción -->
    <div class="flex justify-end space-x-3">
        <a href="{{ route('compras.show', $compra) }}" 
           class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-3 px-6 rounded">
            Cancelar
        </a>
        <button type="submit" 
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded">
            Guardar Modificación
        </button>
    </div>
</form>

@push('scripts')
<script>
let contadorProductos = 0;
const productos = @json($productos);
const detallesExistentes = @json($compra->detalles);

// Cargar productos existentes al cargar la página
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
    row.innerHTML = `
        <td class="px-4 py-2">
            <select name="detalles[${index}][producto_id]" 
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                    onchange="actualizarPrecio(${index})"
                    required>
                <option value="">Seleccionar...</option>
                ${productos.map(p => `<option value="${p.id}" data-precio="${p.precio_compra}" ${p.id == detalle.producto_id ? 'selected' : ''}>${p.nombre}</option>`).join('')}
            </select>
        </td>
        <td class="px-4 py-2">
            <input type="text" 
                   name="detalles[${index}][numero_lote]"
                   value="${detalle.numero_lote}"
                   placeholder="LOTE-001"
                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                   required>
        </td>
        <td class="px-4 py-2">
            <input type="date" 
                   name="detalles[${index}][fecha_vencimiento]"
                   value="${detalle.fecha_vencimiento.split(' ')[0]}"
                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                   required>
        </td>
        <td class="px-4 py-2">
            <input type="number" 
                   name="detalles[${index}][cantidad]"
                   min="1"
                   value="${detalle.cantidad}"
                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm text-right"
                   onchange="calcularSubtotalFila(${index})"
                   required>
        </td>
        <td class="px-4 py-2">
            <input type="number" 
                   name="detalles[${index}][precio_unitario]"
                   step="0.01"
                   min="0"
                   value="${detalle.precio_unitario}"
                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm text-right"
                   onchange="calcularSubtotalFila(${index})"
                   required>
        </td>
        <td class="px-4 py-2 text-right font-semibold">
            S/ <span id="subtotal_${index}">${(detalle.cantidad * detalle.precio_unitario).toFixed(2)}</span>
        </td>
        <td class="px-4 py-2 text-center">
            <button type="button" 
                    onclick="eliminarProducto(${index})"
                    class="text-red-600 hover:text-red-900">
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
    row.innerHTML = `
        <td class="px-4 py-2">
            <select name="detalles[${index}][producto_id]" 
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                    onchange="actualizarPrecio(${index})"
                    required>
                <option value="">Seleccionar...</option>
                ${productos.map(p => `<option value="${p.id}" data-precio="${p.precio_compra}">${p.nombre}</option>`).join('')}
            </select>
        </td>
        <td class="px-4 py-2">
            <input type="text" 
                   name="detalles[${index}][numero_lote]"
                   placeholder="LOTE-001"
                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                   required>
        </td>
        <td class="px-4 py-2">
            <input type="date" 
                   name="detalles[${index}][fecha_vencimiento]"
                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                   required>
        </td>
        <td class="px-4 py-2">
            <input type="number" 
                   name="detalles[${index}][cantidad]"
                   min="1"
                   value="1"
                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm text-right"
                   onchange="calcularSubtotalFila(${index})"
                   required>
        </td>
        <td class="px-4 py-2">
            <input type="number" 
                   name="detalles[${index}][precio_unitario]"
                   step="0.01"
                   min="0"
                   value="0"
                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm text-right"
                   onchange="calcularSubtotalFila(${index})"
                   required>
        </td>
        <td class="px-4 py-2 text-right font-semibold">
            S/ <span id="subtotal_${index}">0.00</span>
        </td>
        <td class="px-4 py-2 text-center">
            <button type="button" 
                    onclick="eliminarProducto(${index})"
                    class="text-red-600 hover:text-red-900">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
            </button>
        </td>
    `;
    
    tbody.appendChild(row);
}

function actualizarPrecio(index) {
    const select = document.querySelector(`select[name="detalles[${index}][producto_id]"]`);
    const precioInput = document.querySelector(`input[name="detalles[${index}][precio_unitario]"]`);
    
    if (select.selectedIndex > 0) {
        const precio = select.options[select.selectedIndex].dataset.precio;
        precioInput.value = parseFloat(precio).toFixed(2);
        calcularSubtotalFila(index);
    }
}

function calcularSubtotalFila(index) {
    const cantidad = parseFloat(document.querySelector(`input[name="detalles[${index}][cantidad]"]`).value) || 0;
    const precioUnitario = parseFloat(document.querySelector(`input[name="detalles[${index}][precio_unitario]"]`).value) || 0;
    const subtotal = cantidad * precioUnitario;
    
    document.getElementById(`subtotal_${index}`).textContent = subtotal.toFixed(2);
    calcularTotales();
}

function eliminarProducto(index) {
    document.getElementById(`producto_${index}`).remove();
    calcularTotales();
    
    const tbody = document.getElementById('detallesCompra');
    if (tbody.children.length === 0) {
        document.getElementById('mensajeSinProductos').classList.remove('hidden');
    }
}

function calcularTotales() {
    let subtotal = 0;
    
    document.querySelectorAll('[id^="subtotal_"]').forEach(span => {
        subtotal += parseFloat(span.textContent) || 0;
    });
    
    const igv = subtotal * 0.18;
    const total = subtotal + igv;
    
    document.getElementById('subtotal').value = subtotal.toFixed(2);
    document.getElementById('igv').value = igv.toFixed(2);
    document.getElementById('total').value = total.toFixed(2);
    
    document.getElementById('subtotalDisplay').textContent = subtotal.toFixed(2);
    document.getElementById('igvDisplay').textContent = igv.toFixed(2);
    document.getElementById('totalDisplay').textContent = total.toFixed(2);
}

// Validar antes de enviar
document.getElementById('formCompra').addEventListener('submit', function(e) {
    const tbody = document.getElementById('detallesCompra');
    if (tbody.children.length === 0) {
        e.preventDefault();
        alert('Debe agregar al menos un producto');
        return false;
    }
});
</script>
@endpush
@endsection