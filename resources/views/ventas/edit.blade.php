@extends('layouts.app')

@section('title', 'Modificar Venta')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Modificar Venta #{{ $venta->id }}
    </h2>
@endsection

@section('content')
<div x-data="editarVentaForm()" x-init="init()">
    <!-- Advertencia -->
    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6 rounded">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-yellow-800">⚠️ Importante: Modificación de Venta</h3>
                <div class="mt-2 text-sm text-yellow-700">
                    <p>• La venta original (#{{ $venta->id }}) será <strong>anulada</strong></p>
                    <p>• Se creará una <strong>nueva venta</strong> con los datos corregidos</p>
                    <p>• El inventario se ajustará automáticamente</p>
                    <p>• Debe indicar el <strong>motivo</strong> de la modificación</p>
                </div>
            </div>
        </div>
    </div>

    <form @submit.prevent="submitForm" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Motivo de Modificación -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-900">Motivo de la Modificación</h3>
            </div>
            <div class="p-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    ¿Por qué modifica esta venta? <span class="text-red-500">*</span>
                </label>
                <textarea 
                    x-model="motivo"
                    rows="3"
                    required
                    minlength="10"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    placeholder="Ej: Cliente solicitó cambio de cantidad, error en el producto seleccionado, etc."></textarea>
                <p class="mt-1 text-sm text-gray-500">Mínimo 10 caracteres</p>
            </div>
        </div>

        <!-- Información del Cliente -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-900">Información del Cliente</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cliente</label>
                        <select x-model="clienteId" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Público General</option>
                            @foreach($clientes as $cliente)
                                <option value="{{ $cliente->id }}" {{ $venta->cliente_id == $cliente->id ? 'selected' : '' }}>
                                    {{ $cliente->nombre }} - {{ $cliente->documento }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Método de Pago <span class="text-red-500">*</span></label>
                        <select x-model="metodoPago" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="efectivo" {{ $venta->metodo_pago == 'efectivo' ? 'selected' : '' }}>Efectivo</option>
                            <option value="tarjeta" {{ $venta->metodo_pago == 'tarjeta' ? 'selected' : '' }}>Tarjeta</option>
                            <option value="transferencia" {{ $venta->metodo_pago == 'transferencia' ? 'selected' : '' }}>Transferencia</option>
                            <option value="yape" {{ $venta->metodo_pago == 'yape' ? 'selected' : '' }}>Yape</option>
                            <option value="plin" {{ $venta->metodo_pago == 'plin' ? 'selected' : '' }}>Plin</option>
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

                    <!-- Resultados -->
                    <div x-show="resultados.length > 0" 
                         @click.away="resultados = []"
                         class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto">
                        <template x-for="producto in resultados" :key="producto.id">
                            <div @click="seleccionarProducto(producto)" 
                                 class="px-4 py-3 hover:bg-gray-100 cursor-pointer border-b">
                                <div class="flex justify-between">
                                    <div>
                                        <p class="font-medium" x-text="producto.nombre"></p>
                                        <p class="text-sm text-gray-500">Stock: <span x-text="producto.lotes.reduce((s, l) => s + l.stock_actual, 0)"></span></p>
                                    </div>
                                    <p class="text-lg font-semibold text-blue-600">S/ <span x-text="Number(producto.precio_venta).toFixed(2)"></span></p>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Productos en la Venta -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-900">Productos Modificados</h3>
            </div>
            <div class="p-6">
                <div class="space-y-3">
                    <template x-for="(item, index) in productosVenta" :key="index">
                        <div class="flex items-center space-x-4 p-4 bg-gray-50 rounded-lg">
                            <div class="flex-1">
                                <p class="font-medium text-gray-900" x-text="item.nombre"></p>
                                <p class="text-sm text-gray-500">Lote: <span x-text="item.lote.numero_lote"></span></p>
                            </div>
                            <div class="flex items-center space-x-2">
                                <button type="button" @click="cambiarCantidad(index, -1)" 
                                        class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-1 px-2 rounded">-</button>
                                <input type="number" x-model.number="item.cantidad" @change="actualizarSubtotal(index)"
                                       min="1" :max="item.lote.stock_actual" class="w-16 text-center rounded-md border-gray-300">
                                <button type="button" @click="cambiarCantidad(index, 1)" 
                                        class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-1 px-2 rounded">+</button>
                            </div>
                            <div class="text-right w-24">
                                <p class="font-semibold">S/ <span x-text="item.subtotal.toFixed(2)"></span></p>
                            </div>
                            <button type="button" @click="eliminarProducto(index)" class="text-red-600 hover:text-red-900">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </template>
                </div>

                <template x-if="productosVenta.length > 0">
                    <div class="mt-6 border-t pt-4">
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
            <a href="{{ route('ventas.show', $venta) }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-6 rounded">
                Cancelar
            </a>
            <button type="submit" :disabled="productosVenta.length === 0 || !motivo || loading"
                    class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-6 rounded disabled:opacity-50">
                <span x-text="loading ? 'Procesando...' : 'Guardar Modificación'"></span>
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function editarVentaForm() {
    return {
        clienteId: '{{ $venta->cliente_id ?? "" }}',
        metodoPago: '{{ $venta->metodo_pago }}',
        motivo: '',
        busqueda: '',
        resultados: [],
        productosVenta: @json($venta->detalles->map(function($d) {
            return [
                'producto_id' => $d->producto_id,
                'lote_id' => $d->lote_id,
                'nombre' => $d->producto->nombre,
                'lote' => [
                    'id' => $d->lote->id,
                    'numero_lote' => $d->lote->numero_lote,
                    'stock_actual' => $d->lote->stock_actual + $d->cantidad,
                    'fecha_vencimiento' => $d->lote->fecha_vencimiento
                ],
                'cantidad' => $d->cantidad,
                'precio_unitario' => (float)$d->precio_unitario,
                'subtotal' => (float)$d->subtotal
            ];
        })->values()),
        loading: false,

        init() {},

        async buscarProductos() {
            if (this.busqueda.length < 2) {
                this.resultados = [];
                return;
            }
            try {
                const response = await fetch(`/api/buscar-productos?termino=${encodeURIComponent(this.busqueda)}`);
                this.resultados = await response.json();
            } catch (error) {
                console.error('Error:', error);
            }
        },

        seleccionarProducto(producto) {
            const loteDisponible = producto.lotes.find(l => l.stock_actual > 0);
            if (!loteDisponible) {
                alert('No hay stock disponible');
                return;
            }

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
            if (nuevaCantidad < 1 || nuevaCantidad > item.lote.stock_actual) return;
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
            if (!this.motivo || this.motivo.length < 10) {
                alert('Debe indicar el motivo de la modificación (mínimo 10 caracteres)');
                return;
            }

            this.loading = true;

            try {
                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('_method', 'PUT');
                formData.append('motivo', this.motivo);
                formData.append('cliente_id', this.clienteId || '');
                formData.append('metodo_pago', this.metodoPago);
                formData.append('productos', JSON.stringify(this.productosVenta.map(item => ({
                    producto_id: item.producto_id,
                    lote_id: item.lote_id,
                    cantidad: item.cantidad,
                    precio_unitario: item.precio_unitario
                }))));

                const response = await fetch('{{ route("ventas.update", $venta) }}', {
                    method: 'POST',
                    body: formData
                });

                if (response.ok) {
                    window.location.href = '{{ route("ventas.index") }}';
                } else {
                    alert('Error al modificar la venta');
                    this.loading = false;
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al procesar la modificación');
                this.loading = false;
            }
        }
    }
}
</script>
@endpush