@extends('layouts.app')

@section('title', 'Nueva Venta')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Registrar Nueva Venta
    </h2>
@endsection

@section('content')
<div x-data="ventaForm()" x-init="init()">
    <form @submit.prevent="submitForm" class="space-y-6">
        @csrf

        <!-- Información del Cliente -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-900">Información del Cliente</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cliente (Opcional)</label>
                        <select x-model="clienteId" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Público General</option>
                            @foreach($clientes as $cliente)
                                <option value="{{ $cliente->id }}">{{ $cliente->nombre }} - {{ $cliente->documento }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Método de Pago <span class="text-red-500">*</span></label>
                        <select x-model="metodoPago" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">-- Seleccione --</option>
                            <option value="efectivo">Efectivo</option>
                            <option value="tarjeta">Tarjeta</option>
                            <option value="transferencia">Transferencia</option>
                            <option value="yape">Yape</option>
                            <option value="plin">Plin</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Buscar Productos -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-900">Buscar Productos</h3>
            </div>
            <div class="p-6">
                <div class="relative">
                    <input 
                        type="text" 
                        x-model="busqueda"
                        @input.debounce.300ms="buscarProductos"
                        placeholder="Buscar por nombre o código de barras..."
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 pl-10">
                    <svg class="absolute left-3 top-3 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>

                    <!-- Resultados de búsqueda -->
                    <div x-show="resultados.length > 0" 
                         @click.away="resultados = []"
                         class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto">
                        <template x-for="producto in resultados" :key="producto.id">
                            <div @click="seleccionarProducto(producto)" 
                                 class="px-4 py-3 hover:bg-gray-100 cursor-pointer border-b border-gray-200">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="font-medium text-gray-900" x-text="producto.nombre"></p>
                                        <p class="text-sm text-gray-500">
                                            Categoría: <span x-text="producto.categoria.nombre"></span>
                                        </p>
                                        <p class="text-sm text-gray-500">
                                            Stock disponible: <span x-text="producto.lotes.reduce((sum, l) => sum + l.stock_actual, 0)"></span>
                                        </p>
                                    </div>
                                    <p class="text-lg font-semibold text-blue-600">
                                        S/ <span x-text="Number(producto.precio_venta).toFixed(2)"></span>
                                    </p>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Productos en el Carrito -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-900">Productos en la Venta</h3>
                <span class="text-sm text-gray-600" x-text="`${productosVenta.length} producto(s)`"></span>
            </div>
            <div class="p-6">
                <template x-if="productosVenta.length === 0">
                    <div class="text-center py-12 text-gray-500">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                        <p class="mt-2">No hay productos agregados</p>
                        <p class="text-sm">Busca y selecciona productos arriba</p>
                    </div>
                </template>

                <div class="space-y-3">
                    <template x-for="(item, index) in productosVenta" :key="index">
                        <div class="flex items-center space-x-4 p-4 bg-gray-50 rounded-lg">
                            <div class="flex-1">
                                <p class="font-medium text-gray-900" x-text="item.nombre"></p>
                                <p class="text-sm text-gray-500">
                                    Lote: <span x-text="item.lote.numero_lote"></span> | 
                                    Vence: <span x-text="new Date(item.lote.fecha_vencimiento).toLocaleDateString()"></span>
                                </p>
                                <p class="text-sm text-gray-500">
                                    Precio: S/ <span x-text="Number(item.precio_unitario).toFixed(2)"></span>
                                </p>
                            </div>
                            <div class="flex items-center space-x-2">
                                <button type="button" @click="cambiarCantidad(index, -1)" 
                                        class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-1 px-2 rounded">
                                    -
                                </button>
                                <input 
                                    type="number" 
                                    x-model.number="item.cantidad"
                                    @change="actualizarSubtotal(index)"
                                    min="1" 
                                    :max="item.lote.stock_actual"
                                    class="w-16 text-center rounded-md border-gray-300"
                                    required>
                                <button type="button" @click="cambiarCantidad(index, 1)" 
                                        class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-1 px-2 rounded">
                                    +
                                </button>
                            </div>
                            <div class="text-right w-24">
                                <p class="font-semibold text-gray-900">
                                    S/ <span x-text="item.subtotal.toFixed(2)"></span>
                                </p>
                            </div>
                            <button type="button" @click="eliminarProducto(index)" 
                                    class="text-red-600 hover:text-red-900">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </template>
                </div>

                <!-- Total -->
                <template x-if="productosVenta.length > 0">
                    <div class="mt-6 border-t border-gray-200 pt-4">
                        <div class="flex justify-between items-center text-2xl font-bold">
                            <span>TOTAL:</span>
                            <span class="text-blue-600">S/ <span x-text="calcularTotal().toFixed(2)"></span></span>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Botones -->
        <div class="flex justify-end space-x-3">
            <a href="{{ route('ventas.index') }}" 
               class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-6 rounded">
                Cancelar
            </a>
            <button type="submit" 
                    :disabled="productosVenta.length === 0 || loading"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded disabled:opacity-50 disabled:cursor-not-allowed flex items-center">
                <svg x-show="loading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span x-text="loading ? 'Procesando...' : 'Registrar Venta'"></span>
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function ventaForm() {
    return {
        clienteId: '',
        metodoPago: '',
        busqueda: '',
        resultados: [],
        productosVenta: [],
        loading: false,

        init() {
            // Inicialización
        },

        async buscarProductos() {
            if (this.busqueda.length < 2) {
                this.resultados = [];
                return;
            }

            try {
                const response = await fetch(`/api/buscar-productos?termino=${encodeURIComponent(this.busqueda)}`);
                const data = await response.json();
                this.resultados = data;
            } catch (error) {
                console.error('Error al buscar productos:', error);
            }
        },

        async seleccionarProducto(producto) {
            // Verificar si el producto requiere receta
            if (producto.requiere_receta) {
                alert('⚠️ Este producto requiere receta médica. Asegúrese de registrarla.');
            }

            // Seleccionar el lote más próximo a vencer (FIFO)
            const loteDisponible = producto.lotes.find(l => l.stock_actual > 0);
            
            if (!loteDisponible) {
                alert('No hay stock disponible para este producto');
                return;
            }

            // Verificar si el lote está próximo a vencer
            const diasParaVencer = Math.ceil((new Date(loteDisponible.fecha_vencimiento) - new Date()) / (1000 * 60 * 60 * 24));
            if (diasParaVencer <= 15) {
                if (!confirm(`⚠️ ALERTA: Este lote vence en ${diasParaVencer} días (${loteDisponible.fecha_vencimiento}). ¿Desea continuar?`)) {
                    return;
                }
            }

            // Agregar al carrito
            this.productosVenta.push({
                producto_id: producto.id,
                lote_id: loteDisponible.id,
                nombre: producto.nombre,
                lote: loteDisponible,
                cantidad: 1,
                precio_unitario: parseFloat(producto.precio_venta),
                subtotal: parseFloat(producto.precio_venta)
            });

            this.busqueda = '';
            this.resultados = [];
        },

        cambiarCantidad(index, cambio) {
            const item = this.productosVenta[index];
            const nuevaCantidad = item.cantidad + cambio;
            
            if (nuevaCantidad < 1) return;
            if (nuevaCantidad > item.lote.stock_actual) {
                alert(`Stock máximo disponible: ${item.lote.stock_actual}`);
                return;
            }

            item.cantidad = nuevaCantidad;
            this.actualizarSubtotal(index);
        },

        actualizarSubtotal(index) {
            const item = this.productosVenta[index];
            item.subtotal = item.cantidad * item.precio_unitario;
        },

        eliminarProducto(index) {
            this.productosVenta.splice(index, 1);
        },

        calcularTotal() {
            return this.productosVenta.reduce((sum, item) => sum + item.subtotal, 0);
        },

        async submitForm() {
            if (this.productosVenta.length === 0) {
                alert('Debe agregar al menos un producto');
                return;
            }

            if (!this.metodoPago) {
                alert('Debe seleccionar un método de pago');
                return;
            }

            this.loading = true;

            try {
                const formData = {
                    cliente_id: this.clienteId || null,
                    metodo_pago: this.metodoPago,
                    productos: this.productosVenta.map(item => ({
                        producto_id: item.producto_id,
                        lote_id: item.lote_id,
                        cantidad: item.cantidad,
                        precio_unitario: item.precio_unitario
                    }))
                };

                const response = await fetch('{{ route("ventas.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(formData)
                });

                const data = await response.json();

                if (response.ok) {
                    window.location.href = data.redirect || '{{ route("ventas.index") }}';
                } else {
                    alert(data.message || 'Error al procesar la venta');
                    this.loading = false;
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al procesar la venta');
                this.loading = false;
            }
        }
    }
}
</script>
@endpush