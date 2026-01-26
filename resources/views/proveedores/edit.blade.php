@extends('layouts.app')

@section('title', 'Editar Proveedor')

@section('header')
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Editar Proveedor: {{ $proveedor->nombre }}
        </h2>
        <div class="flex space-x-2">
            <a href="{{ route('proveedores.show', $proveedor) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
                Ver
            </a>
            <a href="{{ route('proveedores.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Volver
            </a>
        </div>
    </div>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
        <form action="{{ route('proveedores.update', $proveedor) }}" method="POST" class="p-6">
            @csrf
            @method('PUT')

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
                               value="{{ old('nombre', $proveedor->nombre) }}"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('nombre') border-red-500 @enderror"
                               required>
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
                               value="{{ old('ruc', $proveedor->ruc) }}"
                               maxlength="11"
                               pattern="[0-9]{11}"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('ruc') border-red-500 @enderror"
                               required>
                        @error('ruc')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Contacto Principal -->
                    <div>
                        <label for="contacto" class="block text-sm font-medium text-gray-700 mb-1">
                            Contacto Principal
                        </label>
                        <input type="text" 
                               name="contacto" 
                               id="contacto"
                               value="{{ old('contacto', $proveedor->contacto) }}"
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
                               value="{{ old('telefono', $proveedor->telefono) }}"
                               maxlength="15"
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
                               value="{{ old('email', $proveedor->email) }}"
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
                                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('direccion') border-red-500 @enderror">{{ old('direccion', $proveedor->direccion) }}</textarea>
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
                               value="{{ old('sitio_web', $proveedor->sitio_web) }}"
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
                                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('observaciones') border-red-500 @enderror">{{ old('observaciones', $proveedor->observaciones) }}</textarea>
                        @error('observaciones')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                </div>
            </div>

            <!-- Estado Activo -->
            <div class="mt-6">
                <div class="flex items-center">
                    <input type="checkbox" 
                           name="activo" 
                           id="activo"
                           value="1"
                           {{ old('activo', $proveedor->activo) ? 'checked' : '' }}
                           class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <label for="activo" class="ml-2 text-sm text-gray-700">
                        Proveedor Activo
                    </label>
                </div>
            </div>

            <!-- Información de Auditoría -->
            <div class="mt-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                <h3 class="text-sm font-semibold text-gray-700 mb-2">Información del Sistema</h3>
                <div class="grid grid-cols-2 gap-4 text-sm text-gray-600">
                    <div>
                        <span class="font-medium">Registrado:</span> 
                        {{ $proveedor->created_at->format('d/m/Y H:i') }}
                    </div>
                    <div>
                        <span class="font-medium">Última actualización:</span> 
                        {{ $proveedor->updated_at->format('d/m/Y H:i') }}
                    </div>
                </div>
            </div>

            <!-- Botones de Acción -->
            <div class="flex justify-between mt-6 pt-6 border-t border-gray-200">
                <!-- Desactivar -->
                <div>
                    @can('desactivar proveedores')
                        @if($proveedor->activo)
                        <form action="{{ route('proveedores.destroy', $proveedor) }}" 
                              method="POST" 
                              onsubmit="return confirm('¿Está seguro de desactivar este proveedor?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-6 rounded">
                                Desactivar Proveedor
                            </button>
                        </form>
                        @endif
                    @endcan
                </div>

                <!-- Guardar y Cancelar -->
                <div class="flex space-x-3">
                    <a href="{{ route('proveedores.show', $proveedor) }}" 
                       class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-6 rounded">
                        Cancelar
                    </a>
                    <button type="submit" 
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded">
                        Actualizar Proveedor
                    </button>
                </div>
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