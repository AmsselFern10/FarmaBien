@extends('layouts.app')

@section('title', 'Crear Proveedor')

@section('header')
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Registrar Nuevo Proveedor
        </h2>
        <a href="{{ route('proveedores.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Volver
        </a>
    </div>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
        <form action="{{ route('proveedores.store') }}" method="POST" class="p-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <!-- Columna Izquierda -->
                <div class="space-y-4">
                    
                    <!-- Nombre Comercial/Razón Social -->
                    <div>
                        <label for="nombre" class="block text-sm font-medium text-gray-700 mb-1">
                            Razón Social / Nombre Comercial <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               name="nombre" 
                               id="nombre" 
                               value="{{ old('nombre') }}"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('nombre') border-red-500 @enderror"
                               required
                               autofocus>
                        @error('nombre')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- RUC -->
                    <div>
                        <label for="ruc" class="block text-sm font-medium text-gray-700 mb-1">
                            RUC <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               name="ruc" 
                               id="ruc"
                               value="{{ old('ruc') }}"
                               maxlength="11"
                               pattern="[0-9]{11}"
                               placeholder="20123456789"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('ruc') border-red-500 @enderror"
                               required>
                        @error('ruc')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">11 dígitos numéricos</p>
                    </div>

                    <!-- Contacto Principal -->
                    <div>
                        <label for="contacto" class="block text-sm font-medium text-gray-700 mb-1">
                            Contacto Principal
                        </label>
                        <input type="text" 
                               name="contacto" 
                               id="contacto"
                               value="{{ old('contacto') }}"
                               placeholder="Nombre del contacto"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('contacto') border-red-500 @enderror">
                        @error('contacto')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Teléfono -->
                    <div>
                        <label for="telefono" class="block text-sm font-medium text-gray-700 mb-1">
                            Teléfono
                        </label>
                        <input type="text" 
                               name="telefono" 
                               id="telefono"
                               value="{{ old('telefono') }}"
                               maxlength="15"
                               placeholder="(01) 123-4567"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('telefono') border-red-500 @enderror">
                        @error('telefono')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                </div>

                <!-- Columna Derecha -->
                <div class="space-y-4">

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                            Correo Electrónico
                        </label>
                        <input type="email" 
                               name="email" 
                               id="email"
                               value="{{ old('email') }}"
                               placeholder="proveedor@empresa.com"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('email') border-red-500 @enderror">
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Dirección -->
                    <div>
                        <label for="direccion" class="block text-sm font-medium text-gray-700 mb-1">
                            Dirección
                        </label>
                        <textarea name="direccion" 
                                  id="direccion"
                                  rows="3"
                                  placeholder="Av. Principal 123, Distrito, Ciudad"
                                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('direccion') border-red-500 @enderror">{{ old('direccion') }}</textarea>
                        @error('direccion')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Sitio Web -->
                    <div>
                        <label for="sitio_web" class="block text-sm font-medium text-gray-700 mb-1">
                            Sitio Web
                        </label>
                        <input type="url" 
                               name="sitio_web" 
                               id="sitio_web"
                               value="{{ old('sitio_web') }}"
                               placeholder="https://www.ejemplo.com"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('sitio_web') border-red-500 @enderror">
                        @error('sitio_web')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Observaciones -->
                    <div>
                        <label for="observaciones" class="block text-sm font-medium text-gray-700 mb-1">
                            Observaciones
                        </label>
                        <textarea name="observaciones" 
                                  id="observaciones"
                                  rows="2"
                                  placeholder="Información adicional del proveedor..."
                                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('observaciones') border-red-500 @enderror">{{ old('observaciones') }}</textarea>
                        @error('observaciones')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                </div>
            </div>

            <!-- Estado Activo (por defecto) -->
            <div class="mt-6">
                <div class="flex items-center">
                    <input type="checkbox" 
                           name="activo" 
                           id="activo"
                           value="1"
                           {{ old('activo', true) ? 'checked' : '' }}
                           class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <label for="activo" class="ml-2 text-sm text-gray-700">
                        Proveedor Activo
                    </label>
                </div>
            </div>

            <!-- Botones de Acción -->
            <div class="flex justify-end space-x-3 mt-6 pt-6 border-t border-gray-200">
                <a href="{{ route('proveedores.index') }}" 
                   class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-6 rounded">
                    Cancelar
                </a>
                <button type="submit" 
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded">
                    Registrar Proveedor
                </button>
            </div>

        </form>
    </div>
</div>

@push('scripts')
<script>
// Solo números para RUC
document.getElementById('ruc').addEventListener('input', function() {
    this.value = this.value.replace(/[^0-9]/g, '');
});

// Solo números para teléfono
document.getElementById('telefono').addEventListener('input', function() {
    this.value = this.value.replace(/[^0-9()\s-]/g, '');
});
</script>
@endpush
@endsection