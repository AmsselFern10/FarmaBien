@extends('layouts.app')

@section('title', 'Editar Producto')

@section('header')
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Editar Producto: {{ $producto->nombre }}
        </h2>
        <a href="{{ route('productos.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Volver
        </a>
    </div>
@endsection

@section('content')
<div class="bg-white overflow-hidden shadow-sm rounded-lg">
    <form action="{{ route('productos.update', $producto) }}" method="POST" enctype="multipart/form-data" class="p-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <!-- Columna Izquierda -->
            <div class="space-y-4">
                
                <!-- Nombre -->
                <div>
                    <label for="nombre" class="block text-sm font-medium text-gray-700 mb-1">
                        Nombre del Producto <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="nombre" 
                           id="nombre" 
                           value="{{ old('nombre', $producto->nombre) }}"
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('nombre') border-red-500 @enderror"
                           required>
                    @error('nombre')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Descripción -->
                <div>
                    <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-1">
                        Descripción
                    </label>
                    <textarea name="descripcion" 
                              id="descripcion" 
                              rows="3"
                              class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('descripcion') border-red-500 @enderror">{{ old('descripcion', $producto->descripcion) }}</textarea>
                    @error('descripcion')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Categoría -->
                <div>
                    <label for="categoria_id" class="block text-sm font-medium text-gray-700 mb-1">
                        Categoría <span class="text-red-500">*</span>
                    </label>
                    <select name="categoria_id" 
                            id="categoria_id"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('categoria_id') border-red-500 @enderror"
                            required>
                        <option value="">Seleccione una categoría</option>
                        @foreach($categorias as $categoria)
                            <option value="{{ $categoria->id }}" 
                                {{ old('categoria_id', $producto->categoria_id) == $categoria->id ? 'selected' : '' }}>
                                {{ $categoria->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('categoria_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Código de Barra -->
                <div>
                    <label for="codigo_barra" class="block text-sm font-medium text-gray-700 mb-1">
                        Código de Barra
                    </label>
                    <input type="text" 
                           name="codigo_barra" 
                           id="codigo_barra"
                           value="{{ old('codigo_barra', $producto->codigo_barra) }}"
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('codigo_barra') border-red-500 @enderror">
                    @error('codigo_barra')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Ubicación -->
                <div>
                    <label for="ubicacion" class="block text-sm font-medium text-gray-700 mb-1">
                        Ubicación en Almacén
                    </label>
                    <input type="text" 
                           name="ubicacion" 
                           id="ubicacion"
                           value="{{ old('ubicacion', $producto->ubicacion) }}"
                           placeholder="Ej: Estante A-3"
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('ubicacion') border-red-500 @enderror">
                    @error('ubicacion')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

            </div>

            <!-- Columna Derecha -->
            <div class="space-y-4">

                <!-- Precio Compra -->
                <div>
                    <label for="precio_compra" class="block text-sm font-medium text-gray-700 mb-1">
                        Precio de Compra (S/) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" 
                           name="precio_compra" 
                           id="precio_compra"
                           value="{{ old('precio_compra', $producto->precio_compra) }}"
                           step="0.01"
                           min="0"
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('precio_compra') border-red-500 @enderror"
                           required>
                    @error('precio_compra')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Precio Venta -->
                <div>
                    <label for="precio_venta" class="block text-sm font-medium text-gray-700 mb-1">
                        Precio de Venta (S/) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" 
                           name="precio_venta" 
                           id="precio_venta"
                           value="{{ old('precio_venta', $producto->precio_venta) }}"
                           step="0.01"
                           min="0"
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('precio_venta') border-red-500 @enderror"
                           required>
                    @error('precio_venta')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Stock Mínimo -->
                <div>
                    <label for="stock_minimo" class="block text-sm font-medium text-gray-700 mb-1">
                        Stock Mínimo <span class="text-red-500">*</span>
                    </label>
                    <input type="number" 
                           name="stock_minimo" 
                           id="stock_minimo"
                           value="{{ old('stock_minimo', $producto->stock_minimo) }}"
                           min="0"
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('stock_minimo') border-red-500 @enderror"
                           required>
                    @error('stock_minimo')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Imagen Actual -->
                @if($producto->imagen)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Imagen Actual
                    </label>
                    <img src="{{ asset('storage/' . $producto->imagen) }}" 
                         alt="{{ $producto->nombre }}"
                         class="w-32 h-32 object-cover rounded border">
                </div>
                @endif

                <!-- Nueva Imagen -->
                <div>
                    <label for="imagen" class="block text-sm font-medium text-gray-700 mb-1">
                        {{ $producto->imagen ? 'Cambiar Imagen' : 'Imagen del Producto' }}
                    </label>
                    <input type="file" 
                           name="imagen" 
                           id="imagen"
                           accept="image/*"
                           class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 @error('imagen') border-red-500 @enderror">
                    <p class="mt-1 text-xs text-gray-500">Formatos: JPG, PNG. Máximo 2MB</p>
                    @error('imagen')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Preview de Nueva Imagen -->
                <div id="imagePreview" class="hidden">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Vista Previa
                    </label>
                    <img src="" alt="Preview" class="w-32 h-32 object-cover rounded border">
                </div>

                <!-- Checkboxes -->
                <div class="space-y-3">
                    <!-- Requiere Receta -->
                    <div class="flex items-center">
                        <input type="checkbox" 
                               name="requiere_receta" 
                               id="requiere_receta"
                               value="1"
                               {{ old('requiere_receta', $producto->requiere_receta) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <label for="requiere_receta" class="ml-2 text-sm text-gray-700">
                            Requiere Receta Médica
                        </label>
                    </div>

                    <!-- Producto Activo -->
                    <div class="flex items-center">
                        <input type="checkbox" 
                               name="activo" 
                               id="activo"
                               value="1"
                               {{ old('activo', $producto->activo) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <label for="activo" class="ml-2 text-sm text-gray-700">
                            Producto Activo
                        </label>
                    </div>
                </div>

            </div>
        </div>

        <!-- Información de Stock (solo lectura) -->
        <div class="mt-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
            <h3 class="font-semibold text-gray-700 mb-2">Información de Stock</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div>
                    <span class="text-gray-600">Stock Total:</span>
                    <span class="font-semibold ml-2">{{ $producto->stock_total }} unidades</span>
                </div>
                <div>
                    <span class="text-gray-600">Stock Disponible:</span>
                    <span class="font-semibold ml-2">{{ $producto->stock_disponible }} unidades</span>
                </div>
                <div>
                    <span class="text-gray-600">Stock Reservado:</span>
                    <span class="font-semibold ml-2">{{ $producto->stock_reservado }} unidades</span>
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-2">
                <strong>Nota:</strong> Para modificar el stock, utilice el módulo de Movimientos de Inventario
            </p>
        </div>

        <!-- Botones de Acción -->
        <div class="flex justify-between mt-6 pt-6 border-t border-gray-200">
            <!-- Eliminar (si tiene permiso) -->
            <div>
                @can('eliminar productos')
                <button type="button" 
                        onclick="confirmarEliminacion()"
                        class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-6 rounded">
                    Eliminar Producto
                </button>
                @endcan
            </div>

            <!-- Guardar y Cancelar -->
            <div class="flex space-x-3">
                <a href="{{ route('productos.index') }}" 
                   class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-6 rounded">
                    Cancelar
                </a>
                <button type="submit" 
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded">
                    Actualizar Producto
                </button>
            </div>
        </div>

    </form>

    <!-- Formulario oculto para eliminar -->
    <form id="deleteForm" action="{{ route('productos.destroy', $producto) }}" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>
</div>

@push('scripts')
<script>
    // Preview de imagen
    document.getElementById('imagen').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('imagePreview');
                preview.querySelector('img').src = e.target.result;
                preview.classList.remove('hidden');
            }
            reader.readAsDataURL(file);
        }
    });

    // Confirmar eliminación
    function confirmarEliminacion() {
        if (confirm('¿Está seguro de eliminar este producto?\n\nEsta acción no se puede deshacer.')) {
            document.getElementById('deleteForm').submit();
        }
    }
</script>
@endpush
@endsection