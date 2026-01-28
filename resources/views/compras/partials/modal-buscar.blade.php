<!-- Modal de Búsqueda Rápida de Compras -->
<div id="modalBuscarCompra" class="hidden fixed z-50 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 dark:bg-gray-900 bg-opacity-75 dark:bg-opacity-80 transition-opacity" onclick="cerrarModalBuscar()"></div>
        
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-blue-50 to-white dark:from-gray-800 dark:to-gray-800/50">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Buscar Compra</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Ingresa el ID o número de transacción</p>
                    </div>
                </div>
            </div>
            
            <!-- Body -->
            <div class="p-6">
                <!-- Input de búsqueda -->
                <div class="mb-4">
                    <label for="buscarCompraId" class="block text-sm font-medium text-slate-900 dark:text-white mb-2">
                        ID de Compra
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"></path>
                            </svg>
                        </div>
                        <input type="number" 
                               id="buscarCompraId" 
                               class="block w-full pl-10 pr-3 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white text-lg font-mono"
                               placeholder="Ej: 123"
                               min="1"
                               autofocus>
                    </div>
                    <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                        Presiona Enter para buscar
                    </p>
                </div>

                <!-- Resultado de la búsqueda -->
                <div id="resultadoBusqueda" class="hidden">
                    <!-- Success -->
                    <div id="compraEncontrada" class="hidden">
                        <div class="bg-green-50 dark:bg-green-900/20 border-l-4 border-green-400 dark:border-green-600 p-4 rounded-lg mb-4">
                            <div class="flex items-start">
                                <svg class="h-6 w-6 text-green-600 dark:text-green-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div class="flex-1">
                                    <p class="text-sm font-semibold text-green-800 dark:text-green-300">¡Compra Encontrada!</p>
                                </div>
                            </div>
                        </div>

                        <!-- Datos de la compra -->
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 space-y-3">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Compra</p>
                                    <p class="text-xl font-bold text-slate-900 dark:text-white">#<span id="compraIdDisplay"></span></p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Fecha</p>
                                    <p class="text-sm font-semibold text-slate-900 dark:text-white" id="compraFechaDisplay"></p>
                                </div>
                            </div>

                            <div class="border-t border-gray-200 dark:border-gray-600 pt-3">
                                <p class="text-xs text-slate-500 dark:text-slate-400 mb-1">Proveedor</p>
                                <p class="text-sm font-semibold text-slate-900 dark:text-white" id="compraProveedorDisplay"></p>
                            </div>

                            <div class="flex justify-between items-center border-t border-gray-200 dark:border-gray-600 pt-3">
                                <div>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Total</p>
                                    <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">S/ <span id="compraTotalDisplay"></span></p>
                                </div>
                                <div id="compraEstadoContainer"></div>
                            </div>

                            <div class="text-xs text-slate-500 dark:text-slate-400 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                                <span id="compraProductosDisplay"></span> producto(s)
                            </div>
                        </div>

                        <!-- Acciones -->
                        <div class="mt-4 grid grid-cols-2 gap-3">
                            <a id="btnVerCompra" href="#" class="inline-flex justify-center items-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors duration-200">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                Ver Detalles
                            </a>

                            <a id="btnImprimirCompra" href="#" target="_blank" class="inline-flex justify-center items-center px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition-colors duration-200">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                </svg>
                                Imprimir
                            </a>
                        </div>

                        <div class="mt-2">
                            <a id="btnDescargarPDF" href="#" class="block text-center px-4 py-2 bg-red-50 dark:bg-red-900/20 hover:bg-red-100 dark:hover:bg-red-900/30 text-red-700 dark:text-red-400 font-semibold rounded-lg transition-colors duration-200">
                                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Descargar PDF
                            </a>
                        </div>
                    </div>

                    <!-- Error -->
                    <div id="compraNoEncontrada" class="hidden">
                        <div class="bg-red-50 dark:bg-red-900/20 border-l-4 border-red-400 dark:border-red-600 p-4 rounded-lg">
                            <div class="flex items-start">
                                <svg class="h-6 w-6 text-red-600 dark:text-red-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div>
                                    <p class="text-sm font-semibold text-red-800 dark:text-red-300">No se encontró la compra</p>
                                    <p class="text-sm text-red-700 dark:text-red-400 mt-1" id="mensajeError"></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Loading -->
                    <div id="buscandoCompra" class="hidden text-center py-8">
                        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
                        <p class="mt-4 text-sm text-slate-600 dark:text-slate-400">Buscando compra...</p>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 flex justify-end gap-3">
                <button type="button" 
                        onclick="cerrarModalBuscar()"
                        class="px-6 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-slate-700 dark:text-slate-300 font-semibold rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors duration-200">
                    Cerrar
                </button>
                <button type="button" 
                        onclick="buscarCompra()"
                        class="px-6 py-2.5 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-semibold rounded-lg shadow-sm transition-all duration-200 hover:shadow-md">
                    Buscar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function abrirModalBuscar() {
    document.getElementById('modalBuscarCompra').classList.remove('hidden');
    document.getElementById('buscarCompraId').focus();
    // Limpiar resultados anteriores
    document.getElementById('resultadoBusqueda').classList.add('hidden');
    document.getElementById('compraEncontrada').classList.add('hidden');
    document.getElementById('compraNoEncontrada').classList.add('hidden');
    document.getElementById('buscarCompraId').value = '';
}

function cerrarModalBuscar() {
    document.getElementById('modalBuscarCompra').classList.add('hidden');
}

function buscarCompra() {
    const compraId = document.getElementById('buscarCompraId').value;
    
    if (!compraId) {
        alert('⚠️ Por favor ingrese un ID de compra');
        return;
    }
    
    // Mostrar loading
    document.getElementById('resultadoBusqueda').classList.remove('hidden');
    document.getElementById('buscandoCompra').classList.remove('hidden');
    document.getElementById('compraEncontrada').classList.add('hidden');
    document.getElementById('compraNoEncontrada').classList.add('hidden');
    
    // Hacer petición AJAX
    fetch(`/compras/buscar/${compraId}`)
        .then(response => response.json())
        .then(data => {
            // Ocultar loading
            document.getElementById('buscandoCompra').classList.add('hidden');
            
            if (data.success) {
                // Mostrar compra encontrada
                document.getElementById('compraEncontrada').classList.remove('hidden');
                
                // Llenar datos
                document.getElementById('compraIdDisplay').textContent = String(data.compra.id).padStart(6, '0');
                document.getElementById('compraFechaDisplay').textContent = data.compra.fecha;
                document.getElementById('compraProveedorDisplay').textContent = data.compra.proveedor;
                document.getElementById('compraTotalDisplay').textContent = data.compra.total;
                document.getElementById('compraProductosDisplay').textContent = data.compra.productos_count;
                
                // Estado badge
                let estadoHTML = '';
                if (data.compra.estado === 'recibida') {
                    estadoHTML = '<span class="px-3 py-1.5 inline-flex items-center text-xs font-semibold rounded-full bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300"><span class="w-2 h-2 bg-green-500 rounded-full mr-2 animate-pulse"></span>Recibida</span>';
                } else {
                    estadoHTML = '<span class="px-3 py-1.5 inline-flex items-center text-xs font-semibold rounded-full bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300">Anulada</span>';
                }
                document.getElementById('compraEstadoContainer').innerHTML = estadoHTML;
                
                // Enlaces
                document.getElementById('btnVerCompra').href = data.compra.url_show;
                document.getElementById('btnImprimirCompra').href = data.compra.url_imprimir;
                document.getElementById('btnDescargarPDF').href = data.compra.url_pdf;
            } else {
                // Mostrar error
                document.getElementById('compraNoEncontrada').classList.remove('hidden');
                document.getElementById('mensajeError').textContent = data.message;
            }
        })
        .catch(error => {
            document.getElementById('buscandoCompra').classList.add('hidden');
            document.getElementById('compraNoEncontrada').classList.remove('hidden');
            document.getElementById('mensajeError').textContent = 'Error al buscar la compra. Por favor intente nuevamente.';
            console.error('Error:', error);
        });
}

// Enter para buscar
document.getElementById('buscarCompraId')?.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        buscarCompra();
    }
});

// Cerrar con ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        cerrarModalBuscar();
    }
});
</script>