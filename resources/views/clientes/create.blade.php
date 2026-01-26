@extends('layouts.app')

@section('title', 'Crear Cliente')

@section('header')
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Registrar Nuevo Cliente
        </h2>
        <a href="{{ route('clientes.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
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
        <form action="{{ route('clientes.store') }}" method="POST" class="p-6">
            @csrf

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
                               value="{{ old('nombre') }}"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('nombre') border-red-500 @enderror"
                               required
                               autofocus>
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
                            <option value="DNI" {{ old('tipo_documento') == 'DNI' ? 'selected' : '' }}>DNI</option>
                            <option value="RUC" {{ old('tipo_documento') == 'RUC' ? 'selected' : '' }}>RUC</option>
                            <option value="CE" {{ old('tipo_documento') == 'CE' ? 'selected' : '' }}>Carnet de Extranjería</option>
                            <option value="Pasaporte" {{ old('tipo_documento') == 'Pasaporte' ? 'selected' : '' }}>Pasaporte</option>
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
                               value="{{ old('documento') }}"
                               maxlength="20"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('documento') border-red-500 @enderror"
                               required>
                        @error('documento')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">DNI: 8 dígitos, RUC: 11 dígitos</p>
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
                               placeholder="999999999"
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
                               placeholder="ejemplo@correo.com"
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
                                  placeholder="Av. Principal 123, Lima"
                                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('direccion') border-red-500 @enderror">{{ old('direccion') }}</textarea>
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
                               value="{{ old('fecha_nacimiento') }}"
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
                                  placeholder="Alergias, condiciones médicas, etc."
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
                        Cliente Activo
                    </label>
                </div>
            </div>

            <!-- Botones de Acción -->
            <div class="flex justify-end space-x-3 mt-6 pt-6 border-t border-gray-200">
                <a href="{{ route('clientes.index') }}" 
                   class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-6 rounded">
                    Cancelar
                </a>
                <button type="submit" 
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded">
                    Registrar Cliente
                </button>
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
        documentoInput.setAttribute('placeholder', '12345678');
    } else if (tipo === 'RUC') {
        documentoInput.setAttribute('maxlength', '11');
        documentoInput.setAttribute('pattern', '[0-9]{11}');
        documentoInput.setAttribute('placeholder', '12345678901');
    } else if (tipo === 'CE') {
        documentoInput.setAttribute('maxlength', '12');
        documentoInput.removeAttribute('pattern');
        documentoInput.setAttribute('placeholder', 'A12345678');
    } else {
        documentoInput.setAttribute('maxlength', '20');
        documentoInput.removeAttribute('pattern');
        documentoInput.setAttribute('placeholder', '');
    }
    
    documentoInput.value = '';
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