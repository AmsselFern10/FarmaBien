{{-- Modal Buscar Venta (Búsqueda rápida por ID) --}}
<div id="modalBuscarVenta" class="hidden fixed z-50 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 dark:bg-gray-900 bg-opacity-75 dark:bg-opacity-80 transition-opacity" onclick="cerrarModalBuscar()"></div>

        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Buscar Venta por ID</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Ingrese el número de la venta para acceder rápido</p>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-900 dark:text-white mb-2">ID de Venta</label>
                        <div class="flex gap-3">
                            <input type="text"
                                   id="buscarVentaId"
                                   class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                   placeholder="Ej: 12 o 000012"
                                   autocomplete="off">
                            <button type="button"
                                    onclick="buscarVenta()"
                                    class="px-6 py-2.5 bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white font-semibold rounded-lg shadow-sm transition-all duration-200 hover:shadow-md">
                                Buscar
                            </button>
                        </div>
                        <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Tip: puedes pegar el número con ceros a la izquierda.</p>
                    </div>

                    <div id="resultadoBusqueda" class="hidden">

                        {{-- Venta encontrada --}}
                        <div id="ventaEncontrada" class="hidden">
                            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800/30 rounded-xl p-5">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="text-sm font-semibold text-green-800 dark:text-green-300">Venta encontrada</p>
                                        <p class="text-sm text-green-700 dark:text-green-200 mt-1">
                                            <span class="font-bold">#</span><span id="ventaIdDisplay"></span>
                                            <span class="mx-2">•</span>
                                            <span id="ventaFechaDisplay"></span>
                                        </p>
                                        <p class="text-sm text-green-700 dark:text-green-200 mt-1">
                                            Cliente: <span class="font-semibold" id="ventaClienteDisplay"></span>
                                        </p>
                                    </div>

                                    <div class="text-right">
                                        <div id="ventaEstadoContainer" class="mb-1"></div>
                                        <p class="text-lg font-extrabold text-green-800 dark:text-green-200">$ <span id="ventaTotalDisplay"></span></p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400"><span id="ventaProductosDisplay"></span> producto(s)</p>
                                    </div>
                                </div>

                                <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    <a id="btnVerVenta" href="#"
                                       class="inline-flex justify-center items-center px-4 py-2.5 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg transition-colors duration-200">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        Ver
                                    </a>

                                    <a id="btnImprimirVenta" href="#" target="_blank"
                                       class="inline-flex justify-center items-center px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition-colors duration-200">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                        </svg>
                                        Imprimir
                                    </a>

                                    <a id="btnDescargarPDF" href="#"
                                       class="inline-flex justify-center items-center px-4 py-2.5 bg-red-50 dark:bg-red-900/20 hover:bg-red-100 dark:hover:bg-red-900/30 text-red-700 dark:text-red-400 font-semibold rounded-lg transition-colors duration-200">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        PDF
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- Error --}}
                        <div id="ventaNoEncontrada" class="hidden">
                            <div class="bg-red-50 dark:bg-red-900/20 border-l-4 border-red-400 dark:border-red-600 p-4 rounded-lg">
                                <div class="flex items-start">
                                    <svg class="h-6 w-6 text-red-600 dark:text-red-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <div>
                                        <p class="text-sm font-semibold text-red-800 dark:text-red-300">No se encontró la venta</p>
                                        <p class="text-sm text-red-700 dark:text-red-400 mt-1" id="mensajeError"></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Loading --}}
                        <div id="buscandoVenta" class="hidden text-center py-8">
                            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-purple-600"></div>
                            <p class="mt-4 text-sm text-slate-600 dark:text-slate-400">Buscando venta...</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 flex justify-end gap-3">
                <button type="button"
                        onclick="cerrarModalBuscar()"
                        class="px-6 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-slate-700 dark:text-slate-300 font-semibold rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors duration-200">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function abrirModalBuscar() {
        const modal = document.getElementById('modalBuscarVenta');
        if (!modal) return;
        modal.classList.remove('hidden');

        const input = document.getElementById('buscarVentaId');
        if (input) {
            input.value = '';
            setTimeout(() => input.focus(), 50);
        }

        // Limpiar resultados anteriores
        document.getElementById('resultadoBusqueda')?.classList.add('hidden');
        document.getElementById('ventaEncontrada')?.classList.add('hidden');
        document.getElementById('ventaNoEncontrada')?.classList.add('hidden');
        document.getElementById('buscandoVenta')?.classList.add('hidden');
        const msg = document.getElementById('mensajeError');
        if (msg) msg.textContent = '';
    }

    function cerrarModalBuscar() {
        document.getElementById('modalBuscarVenta')?.classList.add('hidden');
    }

    function normalizarIdVenta(raw) {
        const v = String(raw ?? '').trim();
        // Permitir "#000012" / "000012" / "12"
        const soloDigitos = v.replace(/[^0-9]/g, '');
        return soloDigitos.replace(/^0+(\d)/, '$1');
    }

    async function buscarVenta() {
        const input = document.getElementById('buscarVentaId');
        const raw = input ? input.value : '';
        const ventaId = normalizarIdVenta(raw);

        if (!ventaId) {
            alert('⚠️ Por favor ingrese un ID de venta');
            return;
        }

        // Mostrar loading
        document.getElementById('resultadoBusqueda')?.classList.remove('hidden');
        document.getElementById('buscandoVenta')?.classList.remove('hidden');
        document.getElementById('ventaEncontrada')?.classList.add('hidden');
        document.getElementById('ventaNoEncontrada')?.classList.add('hidden');

        const baseUrl = @json(url('ventas/buscar'));
        const url = `${baseUrl}/${encodeURIComponent(ventaId)}`;

        try {
            const response = await fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });

            let data = null;
            const contentType = response.headers.get('content-type') || '';
            if (contentType.includes('application/json')) {
                data = await response.json();
            }

            // Ocultar loading
            document.getElementById('buscandoVenta')?.classList.add('hidden');

            if (!response.ok) {
                // Si no es JSON (por ejemplo, 404 HTML), mensaje más claro
                const msg = (data && data.message)
                    ? data.message
                    : (response.status === 404
                        ? 'Ruta de búsqueda no configurada (ventas/buscar/{id}) o venta inexistente.'
                        : `Error HTTP ${response.status}`);

                document.getElementById('ventaNoEncontrada')?.classList.remove('hidden');
                const el = document.getElementById('mensajeError');
                if (el) el.textContent = msg;
                return;
            }

            if (data && data.success) {
                document.getElementById('ventaEncontrada')?.classList.remove('hidden');

                document.getElementById('ventaIdDisplay').textContent = String(data.venta.id).padStart(6, '0');
                document.getElementById('ventaFechaDisplay').textContent = data.venta.fecha;
                document.getElementById('ventaClienteDisplay').textContent = data.venta.cliente;
                document.getElementById('ventaTotalDisplay').textContent = data.venta.total;
                document.getElementById('ventaProductosDisplay').textContent = data.venta.productos_count;

                // Estado badge
                let estadoHTML = '';
                if (data.venta.estado === 'completada') {
                    estadoHTML = '<span class="px-3 py-1.5 inline-flex items-center text-xs font-semibold rounded-full bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300"><span class="w-2 h-2 bg-green-500 rounded-full mr-2 animate-pulse"></span>Completada</span>';
                } else {
                    estadoHTML = '<span class="px-3 py-1.5 inline-flex items-center text-xs font-semibold rounded-full bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300">Anulada</span>';
                }
                document.getElementById('ventaEstadoContainer').innerHTML = estadoHTML;

                // Enlaces
                document.getElementById('btnVerVenta').href = data.venta.url_show;
                document.getElementById('btnImprimirVenta').href = data.venta.url_imprimir;
                document.getElementById('btnDescargarPDF').href = data.venta.url_pdf;
            } else {
                document.getElementById('ventaNoEncontrada')?.classList.remove('hidden');
                const el = document.getElementById('mensajeError');
                if (el) el.textContent = (data && data.message) ? data.message : 'No se encontró la venta.';
            }
        } catch (error) {
            document.getElementById('buscandoVenta')?.classList.add('hidden');
            document.getElementById('ventaNoEncontrada')?.classList.remove('hidden');
            const el = document.getElementById('mensajeError');
            if (el) el.textContent = 'Error al buscar la venta. Verifique que exista la ruta ventas/buscar/{id} y que tenga permisos.';
            console.error('Error:', error);
        }
    }

    // Enter para buscar
    document.getElementById('buscarVentaId')?.addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            buscarVenta();
        }
    });

    // Cerrar con ESC
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            cerrarModalBuscar();
        }
    });
</script>
