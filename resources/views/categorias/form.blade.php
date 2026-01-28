{{-- resources/views/categorias/form.blade.php --}}

<div class="space-y-6">
    <!-- Nombre -->
    <div>
        <label for="nombre" class="block text-sm font-medium text-slate-900 dark:text-white mb-2">
            Nombre de la Categoría <span class="text-red-500">*</span>
        </label>
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                </svg>
            </div>
            <input type="text" 
                   name="nombre" 
                   id="nombre" 
                   value="{{ old('nombre', $categoria->nombre ?? '') }}"
                   class="block w-full pl-10 pr-3 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors duration-200 @error('nombre') border-red-500 @enderror" 
                   placeholder="Ej: Analgésicos, Antibióticos, etc."
                   required>
        </div>
        @error('nombre')
            <p class="mt-2 text-sm text-red-600 dark:text-red-400 flex items-center">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
                {{ $message }}
            </p>
        @enderror
    </div>

    <!-- Descripción -->
    <div>
        <label for="descripcion" class="block text-sm font-medium text-slate-900 dark:text-white mb-2">
            Descripción
        </label>
        <div class="relative">
            <div class="absolute top-3 left-3 pointer-events-none">
                <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path>
                </svg>
            </div>
            <textarea name="descripcion" 
                      id="descripcion" 
                      rows="4"
                      class="block w-full pl-10 pr-3 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors duration-200 @error('descripcion') border-red-500 @enderror"
                      placeholder="Describe brevemente esta categoría (opcional)">{{ old('descripcion', $categoria->descripcion ?? '') }}</textarea>
        </div>
        @error('descripcion')
            <p class="mt-2 text-sm text-red-600 dark:text-red-400 flex items-center">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
                {{ $message }}
            </p>
        @enderror
    </div>

    <!-- Estado -->
    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
        <div class="flex items-start">
            <div class="flex items-center h-5">
                <input type="checkbox" 
                       name="activo" 
                       id="activo" 
                       value="1"
                       {{ old('activo', $categoria->activo ?? true) ? 'checked' : '' }}
                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
            </div>
            <div class="ml-3">
                <label for="activo" class="font-medium text-slate-900 dark:text-white">
                    Categoría Activa
                </label>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Las categorías activas estarán disponibles para asignar a productos
                </p>
            </div>
        </div>
    </div>
</div>