@extends('layouts.app')

@section('title', 'Ajustar Inventario')

@section('header')
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Ajustar Inventario
        </h2>
        <a href="{{ route('inventario.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Volver
        </a>
    </div>
@endsection

@section('content')

<!-- Información de Ajuste -->
<div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
    <div class="flex">
        <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
        </svg>
        <div class="ml-3">
            <p class="text-sm text-blue-700">
                <strong>Importante:</strong> Use esta función para corregir diferencias en el inventario físico vs. el inventario del sistema.
                Cada ajuste quedará registrado en el historial de movimientos.
            </p>
        </div>
    </div>
</div>

<div class="bg-white overflow-hidden shadow-sm rounded-lg">
    <form action="{{ route('inventario.store-ajuste') }}" method="POST" id="formAjuste" class="p-6">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <!-- Seleccionar Producto -->
            <div class="md:col-span-2">
                <label for="producto_id" class="block text-sm font-medium text-gray-700 mb-1">
                    Producto <span class="text-red-500">*</span>
                </label>
                <select name="producto_id" 
                        id="producto_id"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('producto_id') border-red-500 @enderror"
                        required
                        onchange="cargarLotes()">
                    <option value="">Seleccione un producto...</option>
                    @foreach($productos as $producto)
                        <option value="{{ $producto->id }}" 
                                data-lotes='@json($producto->lotes)'
                                {{ old('producto_id') == $producto->id ? 'selected' : '' }}>
                            {{ $producto->nombre }} - Stock: {{ $producto->stock_total }}
                        </option>
                    @endforeach
                </select>
                @error('producto_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Seleccionar Lote -->
            <div class="md:col-span-2">
                <label for="lote_id" class="block text-sm font-medium text-gray-700 mb-1">
                    Lote <span class="text-red-500">*</span>
                </label>
                <select name="lote_id" 
                        id="lote_id"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('lote_id') border-red-500 @enderror"
                        required
                        onchange="mostrarStockActual()">
                    <option value="">Primero seleccione un producto...</option>
                </select>
                @error('lote_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Información del Lote Seleccionado -->
            <div id="infoLote" class="md:col-span-2 hidden bg-gray-50 rounded-lg p-4 border border-gray-200">
                <div class="grid grid-cols-3 gap-4 text-sm">
                    <div>
                        <p class="text-gray-600">Número de Lote:</p>
                        <p class="font-semibold text-gray-900" id="numeroLote">-</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Stock Actual (Sistema):</p>
                        <p class="font-semibold text-blue-600 text-lg" id="stockActual">0</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Fecha Vencimiento:</p>
                        <p class="font-semibold text-gray-900" id="fechaVencimiento">-</p>
                    </div>
                </div>
            </div>

            <!-- Tipo de Ajuste -->
            <div>
                <label for="tipo_movimiento" class="block text-sm font-medium text-gray-700 mb-1">
                    Tipo de Ajuste <span class="text-red-500">*</span>
                </label>
                <select name="tipo_movimiento" 
                        id="tipo_movimiento"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('tipo_movimiento') border-red-500 @enderror"
                        required>
                    <option value="">Seleccione...</option>
                    <option value="ajuste_entrada" {{ old('tipo_movimiento') == 'ajuste_entrada' ? 'selected' : '' }}>
                        Ajuste por Entrada (Incrementar)
                    </option>
                    <option value="ajuste_salida" {{ old('tipo_movimiento') == 'ajuste_salida' ? 'selected' : '' }}>
                        Ajuste por Salida (Decrementar)
                    </option>
                </select>
                @error('tipo_movimiento')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Cantidad -->
            <div>
                <label for="cantidad" class="block text-sm font-medium text-gray-700 mb-1">
                    Cantidad <span class="text-red-500">*</span>
                </label>
                <input type="number" 
                       name="cantidad" 
                       id="cantidad"
                       value="{{ old('cantidad') }}"
                       min="1"
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('cantidad') border-red-500 @enderror"
                       required
                       onchange="calcularNuevoStock()">
                @error('cantidad')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Motivo del Ajuste -->
            <div class="md:col-span-2">
                <label for="motivo" class="block text-sm font-medium text-gray-700 mb-1">
                    Motivo del Ajuste <span class="text-red-500">*</span>
                </label>
                <textarea name="motivo" 
                          id="motivo"
                          rows="3"
                          class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('motivo') border-red-500 @enderror"
                          placeholder="Ej: Diferencia encontrada en inventario físico, producto dañado, etc."
                          required>{{ old('motivo') }}</textarea>
                @error('motivo')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Vista Previa del Ajuste -->
            <div id="vistaPrevia" class="md:col-span-2 hidden bg-blue-50 rounded-lg p-4 border-2 border-blue-200">
                <h4 class="font-semibold text-blue-900 mb-3">Vista Previa del Ajuste</h4>
                <div class="grid grid-cols-3 gap-4">
                    <div class="text-center">
                        <p class="text-xs text-blue-600">Stock Actual</p>
                        <p class="text-2xl font-bold text-gray-900" id="prevStockActual">0</p>
                    </div>
                    <div class="text-center">
                        <p class="text-xs text-blue-600">Ajuste</p>
                        <p class="text-2xl font-bold" id="prevAjuste">
                            <span class="text-green-600">+0</span>
                        </p>
                    </div>
                    <div class="text-center">
                        <p class="text-xs text-blue-600">Nuevo Stock</p>
                        <p class="text-3xl font-bold text-blue-600" id="prevNuevoStock">0</p>
                    </div>
                </div>
            </div>

        </div>

        <!-- Botones de Acción -->
        <div class="flex justify-end space-x-3 mt-6 pt-6 border-t border-gray-200">
            <a href="{{ route('inventario.index') }}" 
               class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-6 rounded">
                Cancelar
            </a>
            <button type="submit" 
                    id="btnGuardar"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded"
                    onclick="return confirmarAjuste()">
                Realizar Ajuste
            </button>
        </div>

    </form>
</div>

@push('scripts')
<script>
let stockActualGlobal = 0;

function cargarLotes() {
    const select = document.getElementById('producto_id');
    const loteSelect = document.getElementById('lote_id');
    const infoLote = document.getElementById('infoLote');
    const vistaPrevia = document.getElementById('vistaPrevia');
    
    // Limpiar
    loteSelect.innerHTML = '<option value="">Seleccione un lote...</option>';
    infoLote.classList.add('hidden');
    vistaPrevia.classList.add('hidden');
    
    if (select.value) {
        const option = select.options[select.selectedIndex];
        const lotes = JSON.parse(option.dataset.lotes || '[]');
        
        if (lotes.length > 0) {
            lotes.forEach(lote => {
                const opt = document.createElement('option');
                opt.value = lote.id;
                opt.textContent = `${lote.numero_lote} - Stock: ${lote.cantidad_actual} - Vence: ${formatearFecha(lote.fecha_vencimiento)}`;
                opt.dataset.stockActual = lote.cantidad_actual;
                opt.dataset.numeroLote = lote.numero_lote;
                opt.dataset.fechaVencimiento = lote.fecha_vencimiento;
                loteSelect.appendChild(opt);
            });
        } else {
            loteSelect.innerHTML = '<option value="">No hay lotes disponibles</option>';
        }
    }
}

function mostrarStockActual() {
    const loteSelect = document.getElementById('lote_id');
    const infoLote = document.getElementById('infoLote');
    
    if (loteSelect.value) {
        const option = loteSelect.options[loteSelect.selectedIndex];
        stockActualGlobal = parseInt(option.dataset.stockActual);
        
        document.getElementById('numeroLote').textContent = option.dataset.numeroLote;
        document.getElementById('stockActual').textContent = stockActualGlobal;
        document.getElementById('fechaVencimiento').textContent = formatearFecha(option.dataset.fechaVencimiento);
        
        infoLote.classList.remove('hidden');
        calcularNuevoStock();
    } else {
        infoLote.classList.add('hidden');
    }
}

function calcularNuevoStock() {
    const tipoMovimiento = document.getElementById('tipo_movimiento').value;
    const cantidad = parseInt(document.getElementById('cantidad').value) || 0;
    const vistaPrevia = document.getElementById('vistaPrevia');
    
    if (stockActualGlobal > 0 && tipoMovimiento && cantidad > 0) {
        let nuevoStock = stockActualGlobal;
        let ajusteTexto = '';
        
        if (tipoMovimiento === 'ajuste_entrada') {
            nuevoStock = stockActualGlobal + cantidad;
            ajusteTexto = `<span class="text-green-600">+${cantidad}</span>`;
        } else if (tipoMovimiento === 'ajuste_salida') {
            nuevoStock = Math.max(0, stockActualGlobal - cantidad);
            ajusteTexto = `<span class="text-red-600">-${cantidad}</span>`;
        }
        
        document.getElementById('prevStockActual').textContent = stockActualGlobal;
        document.getElementById('prevAjuste').innerHTML = ajusteTexto;
        document.getElementById('prevNuevoStock').textContent = nuevoStock;
        
        vistaPrevia.classList.remove('hidden');
    } else {
        vistaPrevia.classList.add('hidden');
    }
}

function formatearFecha(fecha) {
    const date = new Date(fecha);
    return date.toLocaleDateString('es-PE', { day: '2-digit', month: '2-digit', year: 'numeric' });
}

function confirmarAjuste() {
    const tipoMovimiento = document.getElementById('tipo_movimiento').value;
    const cantidad = parseInt(document.getElementById('cantidad').value) || 0;
    const producto = document.getElementById('producto_id').options[document.getElementById('producto_id').selectedIndex].text;
    const lote = document.getElementById('lote_id').options[document.getElementById('lote_id').selectedIndex].dataset.numeroLote;
    
    let mensaje = `¿Confirmar ajuste de inventario?\n\n`;
    mensaje += `Producto: ${producto}\n`;
    mensaje += `Lote: ${lote}\n`;
    mensaje += `Stock actual: ${stockActualGlobal}\n`;
    
    if (tipoMovimiento === 'ajuste_entrada') {
        mensaje += `Ajuste: +${cantidad}\n`;
        mensaje += `Nuevo stock: ${stockActualGlobal + cantidad}`;
    } else {
        mensaje += `Ajuste: -${cantidad}\n`;
        mensaje += `Nuevo stock: ${Math.max(0, stockActualGlobal - cantidad)}`;
    }
    
    return confirm(mensaje);
}

// Event Listeners
document.getElementById('tipo_movimiento').addEventListener('change', calcularNuevoStock);
document.getElementById('cantidad').addEventListener('input', calcularNuevoStock);
</script>
@endpush
@endsection