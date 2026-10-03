@extends('layouts.app')
@section('title', 'Crear Lote Manual - FarmaBien')
@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    {{-- Breadcrumb --}}
    <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('inventario.lotes') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Lotes</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-800 dark:text-slate-200 font-semibold">Crear Lote Manual</span>
    </nav>

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">Crear Lote Manual</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Para stock de apertura, migración al sistema, donaciones o correcciones iniciales.<br>
                Se registra un movimiento de <span class="font-semibold text-emerald-600 dark:text-emerald-400">entrada</span> en el Kardex con auditoría completa.
            </p>
        </div>
        <a href="{{ route('inventario.lotes') }}"
           class="inline-flex items-center space-x-1.5 px-3.5 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-xl transition shrink-0">
            <span>&larr;</span><span>Volver a Lotes</span>
        </a>
    </div>

    {{-- Aviso de uso --}}
    <div class="flex items-start gap-3 px-4 py-3.5 bg-amber-50 dark:bg-amber-950/40 border border-amber-300 dark:border-amber-700 rounded-xl text-xs text-amber-800 dark:text-amber-300">
        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        <div>
            <span class="font-bold block mb-0.5">¿Cuándo usar este formulario?</span>
            Usa esto para cargar stock existente al migrar al sistema, registrar donaciones o existencias que no entran por compra.
            Para inventario normal, usa el módulo de <a href="{{ route('compras.create') }}" class="underline font-semibold hover:text-amber-900 dark:hover:text-amber-200">Compras</a>.
        </div>
    </div>

    @if($errors->any())
    <div class="px-4 py-3 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-300 dark:border-rose-700 text-xs text-rose-800 dark:text-rose-300">
        <p class="font-bold mb-1">Corrige los siguientes errores:</p>
        <ul class="list-disc list-inside space-y-0.5">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('inventario.lotes.store') }}" class="space-y-5">
        @csrf

        {{-- Card: Medicamento --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm space-y-5">
            <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center space-x-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block"></span>
                    <span>Medicamento</span>
                </h3>
            </div>
            <div>
                <label for="producto_id" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                    Medicamento <span class="text-rose-500">*</span>
                </label>
                <select id="producto_id" name="producto_id" required
                        class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition @error('producto_id') border-rose-500 @enderror">
                    <option value="">-- Seleccionar medicamento --</option>
                    @foreach($productos as $prod)
                    <option value="{{ $prod->id }}"
                        {{ old('producto_id', $productoPreseleccionado?->id) == $prod->id ? 'selected' : '' }}>
                        {{ $prod->nombre }}{{ $prod->principio_activo ? ' — ' . $prod->principio_activo : '' }}
                    </option>
                    @endforeach
                </select>
                @error('producto_id') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Card: Datos del Lote --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm space-y-5">
            <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center space-x-2">
                    <span class="w-2 h-2 rounded-full bg-indigo-500 inline-block"></span>
                    <span>Datos del Lote</span>
                </h3>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label for="numero_lote" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        Número de Lote <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="numero_lote" name="numero_lote" value="{{ old('numero_lote') }}"
                           required placeholder="Ej. LT-2024-001, MIGRACIÓN-001..."
                           class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition @error('numero_lote') border-rose-500 @enderror">
                    @error('numero_lote') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="fecha_vencimiento" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        Fecha de Vencimiento <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" id="fecha_vencimiento" name="fecha_vencimiento"
                           value="{{ old('fecha_vencimiento') }}" required
                           min="{{ now()->addDay()->format('Y-m-d') }}"
                           class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition @error('fecha_vencimiento') border-rose-500 @enderror">
                    @error('fecha_vencimiento') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="cantidad" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        Cantidad Inicial (unidades) <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" id="cantidad" name="cantidad" value="{{ old('cantidad') }}"
                           required min="1" placeholder="0"
                           class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition @error('cantidad') border-rose-500 @enderror">
                    @error('cantidad') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="precio_compra" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        Precio de Costo (C$)
                    </label>
                    <input type="number" id="precio_compra" name="precio_compra"
                           value="{{ old('precio_compra') }}" step="0.01" min="0" placeholder="0.00"
                           class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition">
                    <p class="text-slate-400 text-[10px] mt-1">Opcional — para valorización de inventario.</p>
                </div>
            </div>
        </div>

        {{-- Card: Proveedor y Auditoría --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm space-y-5">
            <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center space-x-2">
                    <span class="w-2 h-2 rounded-full bg-amber-500 inline-block"></span>
                    <span>Proveedor y Auditoría</span>
                </h3>
            </div>
            <div>
                <label for="proveedor_id" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                    Proveedor
                </label>
                <select id="proveedor_id" name="proveedor_id"
                        class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition">
                    <option value="">-- Sin proveedor / Sin dato --</option>
                    @foreach($proveedores as $prov)
                    <option value="{{ $prov->id }}" {{ old('proveedor_id') == $prov->id ? 'selected' : '' }}>
                        {{ $prov->nombre }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="motivo" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                    Motivo / Justificación <span class="text-rose-500">*</span>
                </label>
                <textarea id="motivo" name="motivo" rows="3" required
                          placeholder="Ej: Stock de apertura al migrar al sistema FarmaBien. Conteo físico realizado el 02/10/2026. Responsable: Juan Pérez."
                          class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition @error('motivo') border-rose-500 @enderror">{{ old('motivo') }}</textarea>
                <p class="text-slate-400 text-[10px] mt-1">Mínimo 5 caracteres. Se guarda en el Kardex para auditoría regulatoria (MINSA).</p>
                @error('motivo') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Footer sticky --}}
        <div class="sticky bottom-0 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-3.5
                    bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border-t border-slate-200 dark:border-slate-800
                    shadow-lg z-20 flex items-center justify-between">
            <a href="{{ route('inventario.lotes') }}"
               class="px-5 py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700
                      text-slate-700 dark:text-slate-300 text-sm font-medium rounded-xl transition">
                Cancelar
            </a>
            <button type="submit"
                    class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800
                           text-white text-sm font-semibold rounded-xl shadow-sm transition
                           inline-flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span>Crear Lote e Ingresar al Inventario</span>
            </button>
        </div>
    </form>
</div>
@endsection
