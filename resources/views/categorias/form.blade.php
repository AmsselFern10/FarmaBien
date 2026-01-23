
{{-- resources/views/categorias/form.blade.php --}}
<div class="space-y-4">
    <!-- Nombre -->
    <div>
        <label for="nombre" class="block text-sm font-medium text-gray-700">
            Nombre <span class="text-red-500">*</span>
        </label>
        <input type="text" name="nombre" id="nombre" 
               value="{{ old('nombre', $categoria->nombre ?? '') }}"
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('nombre') border-red-500 @enderror"
               required maxlength="100">
        @error('nombre')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Descripción -->
    <div>
        <label for="descripcion" class="block text-sm font-medium text-gray-700">
            Descripción
        </label>
        <textarea name="descripcion" id="descripcion" rows="3"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('descripcion') border-red-500 @enderror"
                  maxlength="200">{{ old('descripcion', $categoria->descripcion ?? '') }}</textarea>
        @error('descripcion')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Activo -->
    <div class="flex items-center">
        <input type="hidden" name="activo" value="0">
        <input type="checkbox" name="activo" id="activo" value="1"
               {{ old('activo', $categoria->activo ?? true) ? 'checked' : '' }}
               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
        <label for="activo" class="ml-2 block text-sm text-gray-900">
            Categoría activa
        </label>
    </div>
</div>