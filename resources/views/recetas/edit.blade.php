@extends('layouts.app')

@section('title', 'Editar Receta')

@section('header')
    <div class="flex justify-between items-center">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Editar Receta Médica
            </h2>
            <p class="mt-1 text-sm text-gray-600">Receta N° {{ $receta->numero_receta }}</p>
        </div>
        <div class="flex space-x-2">
            <a href="{{ route('recetas.show', $receta) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
                Ver Receta
            </a>
            <a href="{{ route('recetas.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Volver al Listado
            </a>
        </div>
    </div>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    
    <!-- Alerta de Receta Vencida -->
    @if($receta->esta_vencida)
    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6 rounded-md">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-yellow-700">
                    <strong>Advertencia:</strong> Esta receta está vencida desde el {{ $receta->fecha_vencimiento->format('d/m/Y') }}. No podrá ser utilizada para nuevas ventas.
                </p>
            </div>
        </div>
    </div>
    @endif

    <!-- Mensajes de Error Generales -->
    @if ($errors->any())
    <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-md">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">Se encontraron {{ $errors->count() }} errores:</h3>
                <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    @endif

    <!-- Formulario Principal -->
    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
        <form action="{{ route('recetas.update', $receta) }}" method="POST" class="p-6">
            @csrf
            @method('PUT')

            <!-- Información de la Receta -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Datos de la Receta
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <!-- Número de Receta -->
                    <div>
                        <label for="numero_receta" class="block text-sm font-medium text-gray-700 mb-1">
                            Número de Receta <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               name="numero_receta" 
                               id="numero_receta"
                               value="{{ old('numero_receta', $receta->numero_receta) }}"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('numero_receta') border-red-500 @enderror"
                               placeholder="Ej: RX-2025-00001"
                               required>
                        @error('numero_receta')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Número único de identificación de la receta</p>
                    </div>

                    <!-- Fecha de Emisión -->
                    <div>
                        <label for="fecha" class="block text-sm font-medium text-gray-700 mb-1">
                            Fecha de Emisión <span class="text-red-500">*</span>
                        </label>
                        <input type="date" 
                               name="fecha" 
                               id="fecha"
                               value="{{ old('fecha', $receta->fecha->format('Y-m-d')) }}"
                               max="{{ now()->format('Y-m-d') }}"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('fecha') border-red-500 @enderror"
                               required>
                        @error('fecha')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Fecha de Vencimiento -->
                    <div>
                        <label for="fecha_vencimiento" class="block text-sm font-medium text-gray-700 mb-1">
                            Fecha de Vencimiento
                        </label>
                        <input type="date" 
                               name="fecha_vencimiento" 
                               id="fecha_vencimiento"
                               value="{{ old('fecha_vencimiento', $receta->fecha_vencimiento?->format('Y-m-d')) }}"
                               min="{{ now()->format('Y-m-d') }}"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('fecha_vencimiento') border-red-500 @enderror">
                        @error('fecha_vencimiento')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Opcional. Si no se especifica, la receta no vencerá</p>
                    </div>

                    <!-- Cliente -->
                    <div>
                        <label for="cliente_id" class="block text-sm font-medium text-gray-700 mb-1">
                            Cliente/Paciente <span class="text-red-500">*</span>
                        </label>
                        <select name="cliente_id" 
                                id="cliente_id"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('cliente_id') border-red-500 @enderror"
                                required>
                            <option value="">Seleccione un cliente</option>
                            @foreach($clientes as $cliente)
                                <option value="{{ $cliente->id }}" {{ old('cliente_id', $receta->cliente_id) == $cliente->id ? 'selected' : '' }}>
                                    {{ $cliente->nombre }} - {{ $cliente->tipo_documento }}: {{ $cliente->documento }}
                                </option>
                            @endforeach
                        </select>
                        @error('cliente_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                </div>
            </div>

            <!-- Información del Médico -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    Datos del Médico
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <!-- Nombre del Médico -->
                    <div>
                        <label for="medico" class="block text-sm font-medium text-gray-700 mb-1">
                            Nombre del Médico <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               name="medico" 
                               id="medico"
                               value="{{ old('medico', $receta->medico) }}"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('medico') border-red-500 @enderror"
                               placeholder="Dr. Juan Pérez"
                               required>
                        @error('medico')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Especialidad -->
                    <div>
                        <label for="especialidad" class="block text-sm font-medium text-gray-700 mb-1">
                            Especialidad
                        </label>
                        <input type="text" 
                               name="especialidad" 
                               id="especialidad"
                               value="{{ old('especialidad', $receta->especialidad) }}"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('especialidad') border-red-500 @enderror"
                               placeholder="Ej: Medicina General, Cardiología">
                        @error('especialidad')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- CMP -->
                    <div>
                        <label for="cmp" class="block text-sm font-medium text-gray-700 mb-1">
                            CMP
                        </label>
                        <input type="text" 
                               name="cmp" 
                               id="cmp"
                               value="{{ old('cmp', $receta->cmp) }}"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('cmp') border-red-500 @enderror"
                               placeholder="Número de colegiatura">
                        @error('cmp')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                </div>
            </div>

            <!-- Detalles Clínicos -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                    </svg>
                    Detalles Clínicos
                </h3>

                <div class="grid grid-cols-1 gap-6">
                    
                    <!-- Diagnóstico -->
                    <div>
                        <label for="diagnostico" class="block text-sm font-medium text-gray-700 mb-1">
                            Diagnóstico
                        </label>
                        <textarea name="diagnostico" 
                                  id="diagnostico"
                                  rows="3"
                                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('diagnostico') border-red-500 @enderror"
                                  placeholder="Descripción del diagnóstico médico">{{ old('diagnostico', $receta->diagnostico) }}</textarea>
                        @error('diagnostico')
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
                                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('observaciones') border-red-500 @enderror"
                                  placeholder="Indicaciones adicionales, restricciones, etc.">{{ old('observaciones', $receta->observaciones) }}</textarea>
                        @error('observaciones')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                </div>
            </div>

            <!-- Información de Auditoría -->
            <div class="mb-8 bg-gray-50 p-4 rounded-md">
                <h4 class="text-sm font-medium text-gray-700 mb-2">Información del Sistema</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600">
                    <div>
                        <span class="font-medium">Creado:</span> 
                        {{ $receta->created_at->format('d/m/Y H:i') }}
                    </div>
                    <div>
                        <span class="font-medium">Última actualización:</span> 
                        {{ $receta->updated_at->format('d/m/Y H:i') }}
                    </div>
                </div>
            </div>

            <!-- Botones de Acción -->
            <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
                <a href="{{ route('recetas.show', $receta) }}" 
                   class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-6 rounded inline-flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    Cancelar
                </a>
                <button type="submit" 
                        class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-6 rounded inline-flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Actualizar Receta
                </button>
            </div>

        </form>
    </div>

    <!-- Información de Ayuda -->
    <div class="mt-6 bg-blue-50 border-l-4 border-blue-400 p-4 rounded-md">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">Información importante</h3>
                <div class="mt-2 text-sm text-blue-700">
                    <ul class="list-disc list-inside space-y-1">
                        <li>Los campos marcados con <span class="text-red-500">*</span> son obligatorios</li>
                        <li>Cambios en el número de receta deben ser únicos en el sistema</li>
                        <li>Si la receta ya tiene ventas asociadas, algunos cambios pueden afectar los registros</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection