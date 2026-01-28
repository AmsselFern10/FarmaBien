async function cargarPresentaciones(productoId, selectElement) {
    try {
        const response = await fetch(`/api/productos/${productoId}/presentaciones`);
        const data = await response.json();
        
        // Limpiar select
        selectElement.innerHTML = '<option value="">Seleccione presentación...</option>';
        
        // Opción: Unidad base
        const optionUnidad = document.createElement('option');
        optionUnidad.value = '';
        optionUnidad.textContent = 'Unidad base';
        optionUnidad.dataset.unidades = '1';
        optionUnidad.dataset.precio = data.producto.precio_compra;
        selectElement.appendChild(optionUnidad);
        
        // Opciones: Presentaciones
        data.presentaciones.forEach(pres => {
            const option = document.createElement('option');
            option.value = pres.id;
            option.textContent = `${pres.nombre} (x${pres.unidades_por_presentacion})`;
            option.dataset.unidades = pres.unidades_por_presentacion;
            option.dataset.precio = pres.precio_sugerido || (data.producto.precio_compra * pres.unidades_por_presentacion);
            
            if (pres.precio_sugerido) {
                option.textContent += ` - S/ ${parseFloat(pres.precio_sugerido).toFixed(2)}`;
            }
            
            selectElement.appendChild(option);
        });
        
        return data;
        
    } catch (error) {
        console.error('Error al cargar presentaciones:', error);
        alert('Error al cargar las presentaciones del producto');
    }
}

/**
 * Calcular totales según presentación seleccionada
 */
function calcularTotalesConPresentacion(index) {
    const row = document.querySelector(`[data-producto-index="${index}"]`);
    if (!row) return;
    
    const selectPresentacion = row.querySelector('.select-presentacion');
    const inputCantidadPresentaciones = row.querySelector('.input-cantidad-presentaciones');
    const inputPrecioUnitario = row.querySelector('.input-precio-unitario');
    const spanUnidadesTotales = row.querySelector('.span-unidades-totales');
    const spanPrecioPresentacion = row.querySelector('.span-precio-presentacion');
    const spanSubtotal = row.querySelector('.span-subtotal');
    
    // Obtener datos de la presentación seleccionada
    const selectedOption = selectPresentacion.options[selectPresentacion.selectedIndex];
    const unidadesPorPresentacion = parseInt(selectedOption.dataset.unidades || 1);
    const precioSugerido = parseFloat(selectedOption.dataset.precio || 0);
    
    const cantidadPresentaciones = parseInt(inputCantidadPresentaciones.value || 0);
    const precioUnitario = parseFloat(inputPrecioUnitario.value || 0);
    
    // Calcular totales
    const unidadesTotales = cantidadPresentaciones * unidadesPorPresentacion;
    const precioPorPresentacion = precioUnitario * unidadesPorPresentacion;
    const subtotal = precioPorPresentacion * cantidadPresentaciones;
    
    // Actualizar UI
    if (spanUnidadesTotales) {
        spanUnidadesTotales.textContent = `${unidadesTotales} unidades`;
    }
    
    if (spanPrecioPresentacion) {
        spanPrecioPresentacion.textContent = `S/ ${precioPorPresentacion.toFixed(2)}`;
    }
    
    if (spanSubtotal) {
        spanSubtotal.textContent = `S/ ${subtotal.toFixed(2)}`;
    }
    
    // Guardar en campos hidden para envío
    const hiddenPresentacionId = row.querySelector('.hidden-presentacion-id');
    const hiddenUnidadesPorPresentacion = row.querySelector('.hidden-unidades-por-presentacion');
    const hiddenCantidadPresentaciones = row.querySelector('.hidden-cantidad-presentaciones');
    
    if (hiddenPresentacionId) {
        hiddenPresentacionId.value = selectPresentacion.value || '';
    }
    
    if (hiddenUnidadesPorPresentacion) {
        hiddenUnidadesPorPresentacion.value = unidadesPorPresentacion;
    }
    
    if (hiddenCantidadPresentaciones) {
        hiddenCantidadPresentaciones.value = cantidadPresentaciones;
    }
    
    // Recalcular total general de la compra
    calcularTotalCompra();
}

/**
 * Cuando se selecciona una presentación, actualizar precio sugerido
 */
function alSeleccionarPresentacion(index) {
    const row = document.querySelector(`[data-producto-index="${index}"]`);
    const selectPresentacion = row.querySelector('.select-presentacion');
    const inputPrecioUnitario = row.querySelector('.input-precio-unitario');
    const inputCantidadPresentaciones = row.querySelector('.input-cantidad-presentaciones');
    
    const selectedOption = selectPresentacion.options[selectPresentacion.selectedIndex];
    const precioSugerido = parseFloat(selectedOption.dataset.precio || 0);
    const unidadesPorPresentacion = parseInt(selectedOption.dataset.unidades || 1);
    
    // Calcular precio unitario desde el precio de presentación
    if (precioSugerido > 0) {
        const precioUnitario = precioSugerido / unidadesPorPresentacion;
        inputPrecioUnitario.value = precioUnitario.toFixed(2);
    }
    
    // Si no hay cantidad, poner 1
    if (!inputCantidadPresentaciones.value || inputCantidadPresentaciones.value === '0') {
        inputCantidadPresentaciones.value = 1;
    }
    
    calcularTotalesConPresentacion(index);
}

/**
 * Agregar producto con presentaciones al formulario
 */
function agregarProductoConPresentacion(producto) {
    const index = productoIndex++;
    
    const html = `
        <tr data-producto-index="${index}">
            <td class="px-4 py-3">
                <div class="font-medium text-slate-900 dark:text-white">${producto.nombre}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400">${producto.categoria?.nombre || ''}</div>
            </td>
            
            <!-- Presentación -->
            <td class="px-4 py-3">
                <select class="select-presentacion w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm"
                        onchange="alSeleccionarPresentacion(${index})">
                    <option value="">Cargando...</option>
                </select>
            </td>
            
            <!-- Cantidad de presentaciones -->
            <td class="px-4 py-3">
                <input type="number" 
                       class="input-cantidad-presentaciones w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm text-center"
                       min="1" 
                       value="1"
                       oninput="calcularTotalesConPresentacion(${index})">
            </td>
            
            <!-- Unidades totales (calculado) -->
            <td class="px-4 py-3 text-center">
                <span class="span-unidades-totales text-sm font-semibold text-blue-600 dark:text-blue-400">
                    0 unidades
                </span>
            </td>
            
            <!-- Precio Unitario (base) -->
            <td class="px-4 py-3">
                <div class="relative">
                    <span class="absolute left-3 top-2.5 text-sm text-gray-500">S/</span>
                    <input type="number" 
                           class="input-precio-unitario w-full pl-8 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm"
                           step="0.01" 
                           min="0"
                           value="${producto.precio_compra || 0}"
                           oninput="calcularTotalesConPresentacion(${index})">
                </div>
                <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    Por presentación: <span class="span-precio-presentacion font-semibold">S/ 0.00</span>
                </div>
            </td>
            
            <!-- Lote -->
            <td class="px-4 py-3">
                <input type="text" 
                       class="input-lote w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm font-mono"
                       placeholder="Ej: L001"
                       required>
            </td>
            
            <!-- Vencimiento -->
            <td class="px-4 py-3">
                <input type="date" 
                       class="input-vencimiento w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm"
                       min="${new Date().toISOString().split('T')[0]}"
                       required>
            </td>
            
            <!-- Subtotal -->
            <td class="px-4 py-3 text-right">
                <span class="span-subtotal inline-block px-3 py-1 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 font-bold text-sm">
                    S/ 0.00
                </span>
            </td>
            
            <!-- Acción -->
            <td class="px-4 py-3 text-center">
                <button type="button" 
                        onclick="eliminarProducto(${index})"
                        class="p-2 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                </button>
            </td>
            
            <!-- Campos hidden para envío -->
            <input type="hidden" class="hidden-producto-id" name="productos[${index}][producto_id]" value="${producto.id}">
            <input type="hidden" class="hidden-presentacion-id" name="productos[${index}][presentacion_id]" value="">
            <input type="hidden" class="hidden-unidades-por-presentacion" name="productos[${index}][unidades_por_presentacion]" value="1">
            <input type="hidden" class="hidden-cantidad-presentaciones" name="productos[${index}][cantidad_presentaciones]" value="1">
        </tr>
    `;
    
    document.getElementById('tbody-productos').insertAdjacentHTML('beforeend', html);
    
    // Cargar presentaciones del producto
    const row = document.querySelector(`[data-producto-index="${index}"]`);
    const selectPresentacion = row.querySelector('.select-presentacion');
    cargarPresentaciones(producto.id, selectPresentacion);
    
    // Asociar campos de lote y vencimiento a los inputs hidden
    const inputLote = row.querySelector('.input-lote');
    const inputVencimiento = row.querySelector('.input-vencimiento');
    
    inputLote.addEventListener('input', function() {
        const hidden = row.querySelector(`input[name="productos[${index}][numero_lote]"]`) || 
                      createHiddenInput(row, `productos[${index}][numero_lote]`);
        hidden.value = this.value;
    });
    
    inputVencimiento.addEventListener('input', function() {
        const hidden = row.querySelector(`input[name="productos[${index}][fecha_vencimiento]"]`) || 
                      createHiddenInput(row, `productos[${index}][fecha_vencimiento]`);
        hidden.value = this.value;
    });
    
    // Precio unitario
    const inputPrecio = row.querySelector('.input-precio-unitario');
    inputPrecio.addEventListener('input', function() {
        const hidden = row.querySelector(`input[name="productos[${index}][precio_unitario]"]`) || 
                      createHiddenInput(row, `productos[${index}][precio_unitario]`);
        hidden.value = this.value;
    });
}

function createHiddenInput(row, name) {
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = name;
    row.appendChild(input);
    return input;
}
