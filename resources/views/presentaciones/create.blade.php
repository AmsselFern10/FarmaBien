@extends('layouts.app')

@section('title', 'Nueva Presentación - FarmaBien')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Breadcrumb & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('presentaciones.index') }}" class="hover:text-emerald-600 transition">Presentaciones</a>
                <span>/</span>
                <span class="text-slate-800 dark:text-slate-300 font-medium">Nueva</span>
            </div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">Registrar Presentación de Producto</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Agrega una presentación comercial (caja, frasco, ampolla, blister) a un medicamento del catálogo.
            </p>
        </div>
        <a href="{{ route('presentaciones.index') }}"
           class="inline-flex items-center space-x-1.5 px-3.5 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-xl transition shrink-0">
            <span>&larr;</span>
            <span>Volver a la lista</span>
        </a>
    </div>

    <form method="POST" action="{{ route('presentaciones.store') }}" class="space-y-6">
        @csrf

        <!-- Card: Medicamento -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm space-y-5">
            <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center space-x-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>Medicamento Asociado</span>
                </h3>
                <p class="text-xs text-slate-400 mt-0.5">Selecciona el medicamento al que pertenece esta presentación.</p>
            </div>
            <div>
                <label for="producto_id" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                    Medicamento <span class="text-rose-500">*</span>
                </label>
                <select id="producto_id" name="producto_id" required
                        class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition @error('producto_id') border-rose-500 @enderror">
                    <option value="">-- Seleccionar medicamento --</option>
                    @foreach($productos as $prod)
                    <option value="{{ $prod->id }}" {{ old('producto_id') == $prod->id ? 'selected' : '' }}>{{ $prod->nombre }}</option>
                    @endforeach
                </select>
                @error('producto_id')
                    <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Card: Datos de Presentación -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm space-y-5">
            <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center space-x-2">
                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                    <span>Datos de la Presentación</span>
                </h3>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label for="nombre" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        Nombre <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="nombre" name="nombre" value="{{ old('nombre') }}" required
                           placeholder="Ej. Caja x 30 Comprimidos, Frasco 500mL..."
                           class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition @error('nombre') border-rose-500 @enderror">
                    @error('nombre') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="unidades_por_presentacion" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        Unidades por Presentación <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" id="unidades_por_presentacion" name="unidades_por_presentacion" value="{{ old('unidades_por_presentacion', 1) }}" min="1" required
                           class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition @error('unidades_por_presentacion') border-rose-500 @enderror">
                    @error('unidades_por_presentacion') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="md:col-span-2">
                    <label for="descripcion" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Descripción</label>
                    <textarea id="descripcion" name="descripcion" rows="2" placeholder="Descripción adicional de la presentación..."
                              class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition">{{ old('descripcion') }}</textarea>
                </div>
                <div>
                    <label for="orden" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Orden de visualización</label>
                    <input type="number" id="orden" name="orden" value="{{ old('orden', 0) }}" min="0"
                           class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition">
                </div>
                <div>
                    <label for="codigo_barras" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Código de Barras</label>
                    <input type="text" id="codigo_barras" name="codigo_barras" value="{{ old('codigo_barras') }}" placeholder="EAN-13, UPC..."
                           class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition">
                </div>
            </div>
        </div>

        <!-- Card: Precios -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm space-y-5">
            <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center space-x-2">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    <span>Precios</span>
                </h3>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label for="precio_compra" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Precio de Compra ($)</label>
                    <input type="number" id="precio_compra" name="precio_compra" step="0.01" min="0" value="{{ old('precio_compra') }}" placeholder="0.00"
                           class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition">
                </div>
                <div>
                    <label for="precio_venta" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Precio de Venta ($)</label>
                    <input type="number" id="precio_venta" name="precio_venta" step="0.01" min="0" value="{{ old('precio_venta') }}" placeholder="0.00"
                           class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition">
                </div>
            </div>
        </div>

        <!-- Card: Estado -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
            <input type="hidden" name="es_unidad_base" value="0">
            <label class="flex items-center space-x-3 cursor-pointer">
                <input type="checkbox" name="es_unidad_base" value="1" {{ old('es_unidad_base') ? 'checked' : '' }}
                       class="w-4 h-4 rounded bg-slate-50 dark:bg-slate-800 border-slate-300 dark:border-slate-700 text-indigo-600 focus:ring-indigo-500">
                <div>
                    <span class="text-sm font-semibold text-slate-800 dark:text-slate-200 block">Es Unidad Base</span>
                    <span class="text-xs text-slate-400">Indica que esta es la unidad mínima de venta del medicamento.</span>
                </div>
            </label>
            <input type="hidden" name="activo" value="0">
            <label class="flex items-center space-x-3 cursor-pointer">
                <input type="checkbox" name="activo" value="1" {{ old('activo', 1) ? 'checked' : '' }}
                       class="w-4 h-4 rounded bg-slate-50 dark:bg-slate-800 border-slate-300 dark:border-slate-700 text-emerald-600 focus:ring-emerald-500">
                <div>
                    <span class="text-sm font-semibold text-slate-800 dark:text-slate-200 block">Presentación Activa</span>
                    <span class="text-xs text-slate-400">Permite seleccionar esta presentación al realizar ventas y compras.</span>
                </div>
            </label>
        </div>

        <!-- Sticky Footer -->
        <div class="sticky bottom-0 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-3.5 bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border-t border-slate-200 dark:border-slate-800 shadow-lg z-20 flex items-center justify-between transition-all rounded-b-2xl">
            <a href="{{ route('presentaciones.index') }}"
               class="px-5 py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-xl transition">
                Cancelar
            </a>
            <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-sm font-semibold rounded-xl shadow-sm transition inline-flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>Guardar Presentación</span>
            </button>
        </div>
    </form>
</div>
@endsection
