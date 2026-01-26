@extends('layouts.app')

@section('title', 'Editar Cliente')

@section('header')
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Editar Cliente: {{ $cliente->nombre }}
        </h2>
        <div class="flex space-x-2">
            <a href="{{ route('clientes.show', $cliente) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
                Ver
            </a>
            <a href="{{ route('clientes.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
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
        <form action="{{ route('clientes.update', $cliente) }}" method="POST" class="p-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <!-- Columna Izquierda -->
                <div class="space-y-4">
                    
                    <!-- Nombre Completo -->
                    <div>
                        <label for="nombre" class="block text-sm font-medium text-gray-700 mb-1">
                            Nombre Completo <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               name="nombre" 
                               id="nombre" 
                               value="{{ old('nombre', $cliente->nombre) }}"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('nombre') border-red-500 @enderror"
                               required>
                        @error('nombre')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Tipo de Documento -->
                    <div>
                        <label for="tipo_documento" class="block text-sm font-medium text-gray-700 mb-1">
                            Tipo de Documento <span class="text-red-500">*</span>
                        </label>
                        <select name="tipo_documento" 
                                id="tipo_documento"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('tipo_documento') border-red-500 @enderror"
                                required>
                            <option value="">Seleccione...</option>
                            <option value="DNI" {{ old('tipo_documento', $cliente->tipo_documento) == 'DNI' ? 'selected' : '' }}>DNI</option>
                            <option value="RUC" {{ old('tipo_documento', $cliente->tipo_documento) == 'RUC' ? 'selected' : '' }}>RUC</option>
                            <option value="CE" {{ old('tipo_documento', $cliente->tipo_documento) == 'CE' ? 'selected' : '' }}>Carnet de Extranjería</option>
                            <option value="Pasaporte" {{ old('tipo_documento', $cliente->tipo_documento) == 'Pasaporte' ? 'selected' : '' }}>Pasaporte</option>
                        </select>
                        @error('tipo_documento')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Número de Documento -->
                    <div>
                        <label for="documento" class="block text-sm font-medium text-gray-700 mb-1">
                            Número de Documento <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               name="documento" 
                               id="documento"
                               value="{{ old('documento', $cliente->documento) }}"
                               maxlength="20"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('documento') border-red-500 @enderror"
                               required>
                        @error('documento')
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
                               value="{{ old('telefono', $cliente->telefono) }}"
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
                               value="{{ old('email', $cliente->email) }}"
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
                                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('direccion') border-red-500 @enderror">{{ old('direccion', $cliente->direccion) }}</textarea>
                        @error('direccion')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Fecha de Nacimiento -->
                    <div>
                        <label for="fecha_nacimiento" class="block text-sm font-medium text-gray-700 mb-1">
                            Fecha de Nacimiento
                        </label>
                        <input type="date" 
                               name="fecha_nacimiento" 
                               id="fecha_nacimiento"
                               value="{{ old('fecha_nacimiento', $cliente->fecha_nacimiento?->format('Y-m-d')) }}"
                               max="{{ date('Y-m-d') }}"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('fecha_nacimiento') border-red-500 @enderror">
                        @error('fecha_nacimiento')
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
                                  rows="3"
                                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('observaciones') border-red-500 @enderror">{{ old('observaciones', $cliente->observaciones) }}</textarea>
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
                           {{ old('activo', $cliente->activo) ? 'checked' : '' }}
                           class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <label for="activo" class="ml-2 text-sm text-gray-700">
                        Cliente Activo
                    </label>
                </div>
            </div>

            <!-- Información de Auditoría -->
            <div class="mt-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                <h3 class="text-sm font-semibold text-gray-700 mb-2">Información del Sistema</h3>
                <div class="grid grid-cols-2 gap-4 text-sm text-gray-600">
                    <div>
                        <span class="font-medium">Registrado:</span> 
                        {{ $cliente->created_at->format('d/m/Y H:i') }}
                    </div>
                    <div>
                        <span class="font-medium">Última actualización:</span> 
                        {{ $cliente->updated_at->format('d/m/Y H:i') }}
                    </div>
                </div>
            </div>

            <!-- Botones de Acción -->
            <div class="flex justify-between mt-6 pt-6 border-t border-gray-200">
                <!-- Desactivar -->
                <div>
                    @can('desactivar clientes')
                        @if($cliente->activo)
                        <form action="{{ route('clientes.destroy', $cliente) }}" 
                              method="POST" 
                              onsubmit="return confirm('¿Está seguro de desactivar este cliente?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-6 rounded">
                                Desactivar Cliente
                            </button>
                        </form>
                        @endif
                    @endcan
                </div>

                <!-- Guardar y Cancelar -->
                <div class="flex space-x-3">
                    <a href="{{ route('clientes.show', $cliente) }}" 
                       class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-6 rounded">
                        Cancelar
                    </a>
                    <button type="submit" 
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded">
                        Actualizar Cliente
                    </button>
                </div>
            </div>

        </form>
    </div>
</div>

@push('scripts')
<script>
// Validar número de documento según el tipo
document.getElementById('tipo_documento').addEventListener('change', function() {
    const documentoInput = document.getElementById('documento');
    const tipo = this.value;
    
    if (tipo === 'DNI') {
        documentoInput.setAttribute('maxlength', '8');
        documentoInput.setAttribute('pattern', '[0-9]{8}');
    } else if (tipo === 'RUC') {
        documentoInput.setAttribute('maxlength', '11');
        documentoInput.setAttribute('pattern', '[0-9]{11}');
    } else if (tipo === 'CE') {
        documentoInput.setAttribute('maxlength', '12');
        documentoInput.removeAttribute('pattern');
    } else {
        documentoInput.setAttribute('maxlength', '20');
        documentoInput.removeAttribute('pattern');
    }
});

// Solo números para DNI y RUC
document.getElementById('documento').addEventListener('input', function() {
    const tipo = document.getElementById('tipo_documento').value;
    if (tipo === 'DNI' || tipo === 'RUC') {
        this.value = this.value.replace(/[^0-9]/g, '');
    }
});

// Solo números para teléfono
document.getElementById('telefono').addEventListener('input', function() {
    this.value = this.value.replace(/[^0-9]/g, '');
});
</script>
@endpush
@endsection