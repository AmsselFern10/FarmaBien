@extends('layouts.app')

@section('title', 'Ajustar Inventario')

@section('header')
    Inventario
@endsection

@section('page-actions')
    <div>
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white">
            Ajustar Inventario
        </h2>
        <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
            El ajuste fija el <span class="font-semibold">stock_nuevo</span> del lote y queda registrado como movimiento.
        </p>
    </div>
    <div class="flex flex-wrap gap-3">
        <a href="{{ route('inventario.index') }}"
           class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-slate-700 dark:text-slate-300 font-semibold rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200 shadow-sm">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Volver
        </a>
    </div>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Nota informativa -->
    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800/50 rounded-xl p-4">
        <div class="flex gap-3">
            <div class="mt-0.5">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-300" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                          d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                          clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="text-sm text-blue-800 dark:text-blue-200">
                <span class="font-semibold">Importante:</span>
                Usa este formulario cuando el inventario físico no coincide con el sistema.
                El ajuste no edita movimientos previos; registra un movimiento nuevo (trazabilidad).
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-xl border border-gray-200 dark:border-gray-700">
        <form action="{{ route('inventario.store-ajuste') }}" method="POST" id="formAjuste" class="p-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- Producto -->
                <div class="md:col-span-2">
                    <label for="producto_id" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Producto <span class="text-red-500">*</span>
                    </label>
                    <select name="producto_id"
                            id="producto_id"
                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900/40 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('producto_id') border-red-500 @enderror"
                            required
                            onchange="cargarLotes()">
                        <option value="">Seleccione un producto...</option>
                        @foreach($productos as $producto)
                            <option value="{{ $producto->id }}"
                                    data-lotes='@json($producto->lotes)'
                                    {{ old('producto_id') == $producto->id ? 'selected' : '' }}>
                                {{ $producto->nombre }} — Stock total: {{ $producto->stock_total }}
                            </option>
                        @endforeach
                    </select>
                    @error('producto_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Lote -->
                <div class="md:col-span-2">
                    <label for="lote_id" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Lote <span class="text-red-500">*</span>
                    </label>
                    <select name="lote_id"
                            id="lote_id"
                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900/40 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('lote_id') border-red-500 @enderror"
                            required
                            onchange="mostrarInfoLote()">
                        <option value="">Primero seleccione un producto...</option>
                    </select>
                    @error('lote_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Info Lote -->
                <div id="infoLote" class="md:col-span-2 hidden bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4 border border-gray-200 dark:border-gray-600">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                        <div>
                            <p class="text-slate-500 dark:text-slate-400">Número de lote</p>
                            <p class="font-semibold text-slate-900 dark:text-white font-mono" id="numeroLote">-</p>
                        </div>
                        <div>
                            <p class="text-slate-500 dark:text-slate-400">Stock actual (sistema)</p>
                            <p class="font-bold text-lg text-blue-600 dark:text-blue-300" id="stockActual">0</p>
                        </div>
                        <div>
                            <p class="text-slate-500 dark:text-slate-400">Vencimiento</p>
                            <p class="font-semibold text-slate-900 dark:text-white" id="fechaVencimiento">-</p>
                        </div>
                    </div>
                </div>

                <!-- Stock nuevo -->
                <div>
                    <label for="stock_nuevo" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Stock físico (nuevo) <span class="text-red-500">*</span>
                    </label>
                    <input type="number"
                           name="stock_nuevo"
                           id="stock_nuevo"
                           value="{{ old('stock_nuevo') }}"
                           min="0"
                           class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900/40 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('stock_nuevo') border-red-500 @enderror"
                           required
                           oninput="actualizarVistaPrevia()">
                    @error('stock_nuevo')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Ingresa el conteo real del lote.</p>
                </div>

                <!-- Diferencia (solo UI) -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Diferencia (solo lectura)
                    </label>
                    <div class="w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 px-4 py-2">
                        <span id="diferenciaTexto" class="font-semibold text-slate-900 dark:text-white">—</span>
                    </div>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Se registrará un movimiento de ajuste con esa diferencia.</p>
                </div>

                <!-- Motivo -->
                <div class="md:col-span-2">
                    <label for="motivo" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Motivo <span class="text-red-500">*</span>
                    </label>
                    <textarea name="motivo"
                              id="motivo"
                              rows="3"
                              class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900/40 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('motivo') border-red-500 @enderror"
                              placeholder="Ej: Diferencia encontrada en inventario físico, producto dañado, etc."
                              required>{{ old('motivo') }}</textarea>
                    @error('motivo')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Vista previa -->
                <div id="vistaPrevia" class="md:col-span-2 hidden bg-blue-50 dark:bg-blue-900/20 rounded-xl p-4 border border-blue-200 dark:border-blue-800/40">
                    <h4 class="font-semibold text-blue-900 dark:text-blue-100 mb-3">Vista previa</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="text-center">
                            <p class="text-xs text-blue-700 dark:text-blue-200">Stock actual</p>
                            <p class="text-2xl font-bold text-slate-900 dark:text-white" id="prevStockActual">0</p>
                        </div>
                        <div class="text-center">
                            <p class="text-xs text-blue-700 dark:text-blue-200">Ajuste</p>
                            <p class="text-2xl font-bold" id="prevAjuste">—</p>
                        </div>
                        <div class="text-center">
                            <p class="text-xs text-blue-700 dark:text-blue-200">Stock nuevo</p>
                            <p class="text-3xl font-bold text-blue-700 dark:text-blue-200" id="prevStockNuevo">0</p>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Botones -->
            <div class="flex justify-end gap-3 mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('inventario.index') }}"
                   class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-slate-700 dark:text-slate-300 font-semibold rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors shadow-sm">
                    Cancelar
                </a>
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-semibold rounded-lg shadow-sm transition-all duration-200 hover:shadow-md"
                        onclick="return confirmarAjuste()">
                    Realizar ajuste
                </button>
            </div>
        </form>
    </div>

</div>

@push('scripts')
<script>
    let stockActualGlobal = null;

    function formatearFecha(fecha) {
        if (!fecha) return '—';
        const date = new Date(fecha);
        return date.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' });
    }

    function cargarLotes() {
        const selectProducto = document.getElementById('producto_id');
        const selectLote = document.getElementById('lote_id');
        const infoLote = document.getElementById('infoLote');
        const vistaPrevia = document.getElementById('vistaPrevia');

        // Reset
        selectLote.innerHTML = '<option value="">Seleccione un lote...</option>';
        infoLote.classList.add('hidden');
        vistaPrevia.classList.add('hidden');
        document.getElementById('stock_nuevo').value = '';
        document.getElementById('diferenciaTexto').textContent = '—';
        stockActualGlobal = null;

        if (!selectProducto.value) {
            selectLote.innerHTML = '<option value="">Primero seleccione un producto...</option>';
            return;
        }

        const option = selectProducto.options[selectProducto.selectedIndex];
        const lotes = JSON.parse(option.dataset.lotes || '[]');

        if (!Array.isArray(lotes) || lotes.length === 0) {
            selectLote.innerHTML = '<option value="">No hay lotes activos para este producto</option>';
            return;
        }

        lotes.forEach(lote => {
            const opt = document.createElement('option');
            opt.value = lote.id;
            opt.textContent = `${lote.numero_lote} — Stock: ${lote.stock_actual ?? 0} — Vence: ${formatearFecha(lote.fecha_vencimiento)}`;
            opt.dataset.stockActual = lote.stock_actual ?? 0;
            opt.dataset.numeroLote = lote.numero_lote;
            opt.dataset.fechaVencimiento = lote.fecha_vencimiento;
            selectLote.appendChild(opt);
        });

        // Restaura lote si venimos de old()
        const oldLoteId = "{{ old('lote_id') }}";
        if (oldLoteId) {
            selectLote.value = oldLoteId;
            mostrarInfoLote();
        }
    }

    function mostrarInfoLote() {
        const selectLote = document.getElementById('lote_id');
        const infoLote = document.getElementById('infoLote');
        const vistaPrevia = document.getElementById('vistaPrevia');

        vistaPrevia.classList.add('hidden');

        if (!selectLote.value) {
            infoLote.classList.add('hidden');
            stockActualGlobal = null;
            return;
        }

        const option = selectLote.options[selectLote.selectedIndex];
        stockActualGlobal = parseInt(option.dataset.stockActual || '0', 10);

        document.getElementById('numeroLote').textContent = option.dataset.numeroLote || '—';
        document.getElementById('stockActual').textContent = stockActualGlobal;
        document.getElementById('fechaVencimiento').textContent = formatearFecha(option.dataset.fechaVencimiento);

        // por defecto, igualamos el stock nuevo al actual (más rápido para corregir)
        const stockNuevoInput = document.getElementById('stock_nuevo');
        if (!stockNuevoInput.value) {
            stockNuevoInput.value = stockActualGlobal;
        }

        infoLote.classList.remove('hidden');
        actualizarVistaPrevia();
    }

    function actualizarVistaPrevia() {
        const stockNuevo = parseInt(document.getElementById('stock_nuevo').value || '0', 10);
        const vistaPrevia = document.getElementById('vistaPrevia');

        if (stockActualGlobal === null) {
            vistaPrevia.classList.add('hidden');
            return;
        }

        const diferencia = stockNuevo - stockActualGlobal;

        // Diferencia UI
        const diferenciaTexto = document.getElementById('diferenciaTexto');
        if (diferencia > 0) {
            diferenciaTexto.textContent = `+${diferencia}`;
            diferenciaTexto.className = 'font-semibold text-emerald-600 dark:text-emerald-400';
        } else if (diferencia < 0) {
            diferenciaTexto.textContent = `${diferencia}`;
            diferenciaTexto.className = 'font-semibold text-red-600 dark:text-red-400';
        } else {
            diferenciaTexto.textContent = '0';
            diferenciaTexto.className = 'font-semibold text-slate-900 dark:text-white';
        }

        // Preview
        document.getElementById('prevStockActual').textContent = stockActualGlobal;
        document.getElementById('prevStockNuevo').textContent = stockNuevo;

        const prevAjuste = document.getElementById('prevAjuste');
        if (diferencia > 0) {
            prevAjuste.innerHTML = `<span class="text-emerald-600 dark:text-emerald-400">+${diferencia}</span>`;
        } else if (diferencia < 0) {
            prevAjuste.innerHTML = `<span class="text-red-600 dark:text-red-400">${diferencia}</span>`;
        } else {
            prevAjuste.innerHTML = `<span class="text-slate-900 dark:text-white">0</span>`;
        }

        vistaPrevia.classList.remove('hidden');
    }

    function confirmarAjuste() {
        const selectProducto = document.getElementById('producto_id');
        const selectLote = document.getElementById('lote_id');

        if (!selectProducto.value || !selectLote.value || stockActualGlobal === null) return true;

        const producto = selectProducto.options[selectProducto.selectedIndex].text;
        const lote = selectLote.options[selectLote.selectedIndex].dataset.numeroLote;
        const stockNuevo = parseInt(document.getElementById('stock_nuevo').value || '0', 10);
        const diferencia = stockNuevo - stockActualGlobal;

        let mensaje = `¿Confirmar ajuste de inventario?\n\n`;
        mensaje += `Producto: ${producto}\n`;
        mensaje += `Lote: ${lote}\n`;
        mensaje += `Stock actual: ${stockActualGlobal}\n`;
        mensaje += `Stock nuevo: ${stockNuevo}\n`;
        mensaje += `Diferencia: ${diferencia > 0 ? '+'+diferencia : diferencia}`;

        return confirm(mensaje);
    }

    // Si venimos de old(producto_id), precargamos lotes
    document.addEventListener('DOMContentLoaded', function () {
        const oldProducto = "{{ old('producto_id') }}";
        if (oldProducto) {
            cargarLotes();
        }
    });
</script>
@endpush
@endsection
