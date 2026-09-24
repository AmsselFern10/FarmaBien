@extends('layouts.app')

@section('title', 'Nueva Promoción - FarmaBien')

@section('content')
<div class="space-y-4" x-data="{
    fullWidth: false,
    alcance: '{{ old('alcance', 'producto') }}',
    tipo: '{{ old('tipo', 'porcentaje') }}',
    valor: {{ old('valor', 15) }},
    minUnidades: {{ old('min_unidades', 1) }},
    samplePrice: 20.00,
    updateTipo(newTipo) {
        this.tipo = newTipo;
        if (newTipo === '2x1') {
            this.minUnidades = 2;
            this.valor = 50;
        } else if (newTipo === '3x2') {
            this.minUnidades = 3;
            this.valor = 33.33;
        }
    },
    calculateDiscount(qty = 1) {
        if (this.tipo === 'porcentaje') {
            return (this.samplePrice * (this.valor / 100)) * qty;
        } else if (this.tipo === 'monto_fijo') {
            return Math.min(this.samplePrice, this.valor) * qty;
        } else if (this.tipo === '2x1') {
            return Math.floor(qty / 2) * this.samplePrice;
        } else if (this.tipo === '3x2') {
            return Math.floor(qty / 3) * this.samplePrice;
        }
        return 0;
    }
}">
    <!-- Breadcrumb -->
    <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('promociones.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Promociones</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-800 dark:text-slate-200 font-semibold">Nueva Promoción</span>
    </nav>

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-1 border-b border-slate-300/80 dark:border-slate-800">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">Registrar Nueva Promoción</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Configura reglas automáticas de descuento para el Punto de Venta y Catálogos.
            </p>
        </div>
        <div class="flex items-center space-x-2 shrink-0">
            <button @click="fullWidth = !fullWidth; $dispatch('toggle-full-width', { full: fullWidth })" 
                    type="button" 
                    class="p-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition"
                    :title="fullWidth ? 'Modo estándar' : 'Modo pantalla completa'">
                <svg x-show="!fullWidth" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                <svg x-show="fullWidth" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 9L4 4m0 0h4M4 4v4m11 0l5-5m0 0h-4m4 0v4M9 15l-5 5m0 0h4m-4 0v-4m11 0l5 5m0 0h-4m4 0v-4"/></svg>
            </button>
            <a href="{{ route('promociones.index') }}" 
               class="px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-xl text-xs font-semibold shadow-xs transition">
                &larr; Volver al Listado
            </a>
        </div>
    </div>

    <!-- Errors -->
    @if($errors->any())
    <div class="p-3 rounded-xl bg-rose-50 dark:bg-rose-950/60 border border-rose-300 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-xs">
        <p class="font-bold mb-1">Por favor corrige los siguientes errores:</p>
        <ul class="list-disc list-inside space-y-0.5">
            @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
        </ul>
    </div>
    @endif

    <!-- Form & Live Simulator -->
    <form action="{{ route('promociones.store') }}" method="POST" class="space-y-4">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <!-- Columna Izquierda / Central: Formulario de Configuración -->
            <div class="lg:col-span-2 space-y-4">
                <!-- Tarjeta 1: Información Básica -->
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs p-5 space-y-4">
                    <h2 class="text-sm font-bold text-slate-800 dark:text-slate-200 border-b border-slate-100 dark:border-slate-800 pb-2">
                        1. Información General de la Campaña
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Nombre de la Promoción <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="nombre" value="{{ old('nombre') }}" required maxlength="150"
                                   placeholder="Ej: Descuento de Temporada Antigripales, 2x1 Vitaminas C, 15% Laboratorios Bayer..."
                                   class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm focus:ring-emerald-500 focus:border-emerald-500">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Descripción o Justificación Comercial
                            </label>
                            <textarea name="descripcion" rows="2" maxlength="1000"
                                      placeholder="Breve descripción de la campaña u oferta visible en reportes..."
                                      class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-xs focus:ring-emerald-500 focus:border-emerald-500">{{ old('descripcion') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Tarjeta 2: Tipo de Beneficio y Descuento -->
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs p-5 space-y-4">
                    <h2 class="text-sm font-bold text-slate-800 dark:text-slate-200 border-b border-slate-100 dark:border-slate-800 pb-2">
                        2. Tipo de Descuento y Reglas Financieras
                    </h2>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <label class="flex flex-col items-center p-3 rounded-xl border cursor-pointer transition text-center"
                               :class="tipo === 'porcentaje' ? 'border-emerald-500 bg-emerald-50/60 dark:bg-emerald-950/40 text-emerald-900 dark:text-emerald-200 font-bold' : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/60 text-slate-600 dark:text-slate-400'">
                            <input type="radio" name="tipo" value="porcentaje" class="sr-only" @click="updateTipo('porcentaje')" {{ old('tipo', 'porcentaje') === 'porcentaje' ? 'checked' : '' }}>
                            <span class="text-lg mb-1">🏷️ %</span>
                            <span class="text-xs">Porcentual</span>
                        </label>

                        <label class="flex flex-col items-center p-3 rounded-xl border cursor-pointer transition text-center"
                               :class="tipo === 'monto_fijo' ? 'border-emerald-500 bg-emerald-50/60 dark:bg-emerald-950/40 text-emerald-900 dark:text-emerald-200 font-bold' : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/60 text-slate-600 dark:text-slate-400'">
                            <input type="radio" name="tipo" value="monto_fijo" class="sr-only" @click="updateTipo('monto_fijo')" {{ old('tipo') === 'monto_fijo' ? 'checked' : '' }}>
                            <span class="text-lg mb-1">💵 $</span>
                            <span class="text-xs">Monto Fijo</span>
                        </label>

                        <label class="flex flex-col items-center p-3 rounded-xl border cursor-pointer transition text-center"
                               :class="tipo === '2x1' ? 'border-indigo-500 bg-indigo-50/60 dark:bg-indigo-950/40 text-indigo-900 dark:text-indigo-200 font-bold' : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/60 text-slate-600 dark:text-slate-400'">
                            <input type="radio" name="tipo" value="2x1" class="sr-only" @click="updateTipo('2x1')" {{ old('tipo') === '2x1' ? 'checked' : '' }}>
                            <span class="text-lg mb-1">🎁 2x1</span>
                            <span class="text-xs">Lleva 2 Paga 1</span>
                        </label>

                        <label class="flex flex-col items-center p-3 rounded-xl border cursor-pointer transition text-center"
                               :class="tipo === '3x2' ? 'border-indigo-500 bg-indigo-50/60 dark:bg-indigo-950/40 text-indigo-900 dark:text-indigo-200 font-bold' : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/60 text-slate-600 dark:text-slate-400'">
                            <input type="radio" name="tipo" value="3x2" class="sr-only" @click="updateTipo('3x2')" {{ old('tipo') === '3x2' ? 'checked' : '' }}>
                            <span class="text-lg mb-1">📦 3x2</span>
                            <span class="text-xs">Lleva 3 Paga 2</span>
                        </label>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                <span x-show="tipo === 'porcentaje'">Porcentaje de Descuento (%)</span>
                                <span x-show="tipo === 'monto_fijo'">Monto de Descuento ($)</span>
                                <span x-show="tipo === '2x1' || tipo === '3x2'">Valor Equivalente</span>
                                <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="number" step="0.01" min="0" name="valor" x-model="valor" required
                                       class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm font-bold focus:ring-emerald-500 focus:border-emerald-500">
                                <span class="absolute right-3.5 top-2.5 text-xs font-bold text-slate-400" x-text="tipo === 'porcentaje' ? '%' : '$'"></span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Mínimo de Unidades
                            </label>
                            <input type="number" min="1" name="min_unidades" x-model="minUnidades" required
                                   class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm focus:ring-emerald-500 focus:border-emerald-500">
                            <p class="text-[10px] text-slate-400 mt-0.5">Cantidad mínima para que aplique la oferta.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Límite de Unidades (Opcional)
                            </label>
                            <input type="number" min="1" name="stock_limite" value="{{ old('stock_limite') }}"
                                   placeholder="Ilimitado"
                                   class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm focus:ring-emerald-500 focus:border-emerald-500">
                            <p class="text-[10px] text-slate-400 mt-0.5">Tope total de unidades en promoción.</p>
                        </div>
                    </div>
                </div>

                <!-- Tarjeta 3: Alcance y Destino -->
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs p-5 space-y-4">
                    <h2 class="text-sm font-bold text-slate-800 dark:text-slate-200 border-b border-slate-100 dark:border-slate-800 pb-2">
                        3. Alcance de Aplicación
                    </h2>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <label class="flex items-center space-x-2 p-2.5 rounded-xl border cursor-pointer transition"
                               :class="alcance === 'producto' ? 'border-emerald-500 bg-emerald-50/50 dark:bg-emerald-950/30 font-semibold text-emerald-800 dark:text-emerald-300' : 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400'">
                            <input type="radio" name="alcance" value="producto" x-model="alcance" class="text-emerald-600 focus:ring-emerald-500">
                            <span class="text-xs">Por Producto</span>
                        </label>

                        <label class="flex items-center space-x-2 p-2.5 rounded-xl border cursor-pointer transition"
                               :class="alcance === 'categoria' ? 'border-emerald-500 bg-emerald-50/50 dark:bg-emerald-950/30 font-semibold text-emerald-800 dark:text-emerald-300' : 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400'">
                            <input type="radio" name="alcance" value="categoria" x-model="alcance" class="text-emerald-600 focus:ring-emerald-500">
                            <span class="text-xs">Por Categoría</span>
                        </label>

                        <label class="flex items-center space-x-2 p-2.5 rounded-xl border cursor-pointer transition"
                               :class="alcance === 'laboratorio' ? 'border-emerald-500 bg-emerald-50/50 dark:bg-emerald-950/30 font-semibold text-emerald-800 dark:text-emerald-300' : 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400'">
                            <input type="radio" name="alcance" value="laboratorio" x-model="alcance" class="text-emerald-600 focus:ring-emerald-500">
                            <span class="text-xs">Por Laboratorio</span>
                        </label>

                        <label class="flex items-center space-x-2 p-2.5 rounded-xl border cursor-pointer transition"
                               :class="alcance === 'general' ? 'border-emerald-500 bg-emerald-50/50 dark:bg-emerald-950/30 font-semibold text-emerald-800 dark:text-emerald-300' : 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400'">
                            <input type="radio" name="alcance" value="general" x-model="alcance" class="text-emerald-600 focus:ring-emerald-500">
                            <span class="text-xs">Catálogo Global</span>
                        </label>
                    </div>

                    <!-- Selector Específico según Alcance -->
                    <div x-show="alcance === 'producto'" x-cloak class="pt-2">
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Seleccionar Medicamento / Producto <span class="text-rose-500">*</span>
                        </label>
                        <select name="producto_id" class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-xs focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="">-- Seleccione un medicamento --</option>
                            @foreach($productos as $prod)
                            <option value="{{ $prod->id }}" {{ old('producto_id') == $prod->id ? 'selected' : '' }}>
                                {{ $prod->nombre }} {{ $prod->concentracion }} (Precio regular: ${{ number_format($prod->precio_venta, 2) }})
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div x-show="alcance === 'categoria'" x-cloak class="pt-2">
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Seleccionar Categoría Terapéutica <span class="text-rose-500">*</span>
                        </label>
                        <select name="categoria_id" class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-xs focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="">-- Seleccione una categoría --</option>
                            @foreach($categorias as $cat)
                            <option value="{{ $cat->id }}" {{ old('categoria_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->nombre }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div x-show="alcance === 'laboratorio'" x-cloak class="pt-2">
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Seleccionar Laboratorio Fabricante <span class="text-rose-500">*</span>
                        </label>
                        <select name="laboratorio_id" class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-xs focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="">-- Seleccione un laboratorio --</option>
                            @foreach($laboratorios as $lab)
                            <option value="{{ $lab->id }}" {{ old('laboratorio_id') == $lab->id ? 'selected' : '' }}>
                                {{ $lab->nombre }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Tarjeta 4: Período de Vigencia -->
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs p-5 space-y-4">
                    <h2 class="text-sm font-bold text-slate-800 dark:text-slate-200 border-b border-slate-100 dark:border-slate-800 pb-2">
                        4. Período de Vigencia y Estado
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Fecha y Hora de Inicio <span class="text-rose-500">*</span>
                            </label>
                            <input type="datetime-local" name="fecha_inicio" required
                                   value="{{ old('fecha_inicio', now()->format('Y-m-d\TH:i')) }}"
                                   class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-xs focus:ring-emerald-500 focus:border-emerald-500">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Fecha y Hora de Finalización <span class="text-rose-500">*</span>
                            </label>
                            <input type="datetime-local" name="fecha_fin" required
                                   value="{{ old('fecha_fin', now()->addDays(15)->format('Y-m-d\TH:i')) }}"
                                   class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-xs focus:ring-emerald-500 focus:border-emerald-500">
                        </div>

                        <div class="sm:col-span-2 pt-2">
                            <label class="inline-flex items-center space-x-2.5 cursor-pointer">
                                <input type="checkbox" name="activo" value="1" {{ old('activo', '1') == '1' ? 'checked' : '' }}
                                       class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                <span class="text-xs font-semibold text-slate-800 dark:text-slate-200">Activar campaña inmediatamente</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Columna Derecha: Simulador de Descuento y Vista Previa -->
            <div class="space-y-4">
                <div class="bg-gradient-to-br from-emerald-500/10 via-teal-500/5 to-transparent dark:from-emerald-950/40 dark:via-slate-900 dark:to-slate-900 rounded-xl border border-emerald-200 dark:border-emerald-800/60 p-5 sticky top-20 shadow-xs">
                    <h3 class="text-xs font-bold text-emerald-800 dark:text-emerald-300 uppercase tracking-wider mb-3 flex items-center space-x-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        <span>Simulador POS en Vivo</span>
                    </h3>

                    <div class="space-y-3 bg-white dark:bg-slate-800/90 rounded-xl p-4 border border-slate-200/80 dark:border-slate-700">
                        <div>
                            <label class="block text-[11px] text-slate-500 dark:text-slate-400 mb-1">Precio Unitario Base de Prueba:</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1.5 text-xs text-slate-400 font-bold">$</span>
                                <input type="number" step="0.5" x-model="samplePrice" class="w-full pl-6 pr-3 py-1 rounded-lg border-slate-200 dark:border-slate-700 dark:bg-slate-900 text-xs font-bold text-slate-900 dark:text-white">
                            </div>
                        </div>

                        <div class="pt-2 border-t border-slate-100 dark:border-slate-700 space-y-2">
                            <div class="flex justify-between items-center text-xs">
                                <span class="text-slate-500">Por 1 unidad:</span>
                                <div class="text-right">
                                    <span class="line-through text-slate-400" x-show="calculateDiscount(1) > 0" x-text="'$' + Number(samplePrice).toFixed(2)"></span>
                                    <span class="font-bold text-emerald-600 dark:text-emerald-400 ml-1" x-text="'$' + (samplePrice - calculateDiscount(1)).toFixed(2)"></span>
                                </div>
                            </div>

                            <div class="flex justify-between items-center text-xs" x-show="minUnidades > 1 || tipo === '2x1' || tipo === '3x2'">
                                <span class="text-slate-500" x-text="'Por ' + minUnidades + ' unidades (Combo):'"></span>
                                <div class="text-right">
                                    <span class="line-through text-slate-400" x-text="'$' + (samplePrice * minUnidades).toFixed(2)"></span>
                                    <span class="font-bold text-emerald-600 dark:text-emerald-400 ml-1" x-text="'$' + ((samplePrice * minUnidades) - calculateDiscount(minUnidades)).toFixed(2)"></span>
                                </div>
                            </div>

                            <div class="p-2.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 text-[11px] text-emerald-800 dark:text-emerald-300">
                                <p class="font-bold">Ahorro para el cliente:</p>
                                <p class="mt-0.5" x-text="'$' + calculateDiscount(minUnidades).toFixed(2) + ' de descuento directo en el ticket POS.'"></p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 space-y-2">
                        <button type="submit" 
                                class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white rounded-xl text-xs font-bold shadow-xs transition flex items-center justify-center space-x-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Guardar y Activar Promoción</span>
                        </button>

                        <a href="{{ route('promociones.index') }}" 
                           class="w-full py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-400 rounded-xl text-xs font-semibold transition text-center block">
                            Cancelar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
