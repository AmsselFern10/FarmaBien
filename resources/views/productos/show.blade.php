@extends('layouts.app')

@section('title', $producto->nombre . ' - Detalle Farmacológico - FarmaBien')

@section('content')
<div class="space-y-5">
    
    <!-- Fila 1: Breadcrumbs Únicamente -->
    <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('productos.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Medicamentos</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-800 dark:text-slate-200 font-semibold truncate max-w-[200px]">{{ $producto->nombre }}</span>
    </nav>

    <!-- Fila 2: Header & Quick Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex flex-wrap items-center gap-2">
                <h1 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <span>{{ $producto->nombre }}</span>
                </h1>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold {{ $producto->activo ? 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-900 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700' }}">
                    {{ $producto->activo ? 'Activo' : 'Inactivo' }}
                </span>
                @if($producto->esControlado())
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-900 border border-purple-200 dark:bg-purple-950/60 dark:text-purple-300 dark:border-purple-800">
                        Controlado / Con Receta (MINSA)
                    </span>
                @else
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-900 border border-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800">
                        Venta Libre (OTC)
                    </span>
                @endif
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                {{ $producto->principio_activo ?: 'Fórmula Farmacéutica' }} @if($producto->concentracion) • {{ $producto->concentracion }} @endif @if($producto->forma_farmaceutica) ({{ $producto->forma_farmaceutica }}) @endif
            </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap shrink-0">
            <!-- 1. Volver -->
            <a href="{{ route('productos.index') }}" 
               class="h-10 px-4 rounded-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm font-medium transition inline-flex items-center gap-2 shrink-0 cursor-pointer shadow-2xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>&larr; Medicamentos</span>
            </a>

            <!-- 2. Botón Modo Full -->
            <button type="button" 
                    @click="$dispatch('toggle-pos-fullscreen')"
                    title="Modo Pantalla Completa / Ocultar Barras"
                    class="h-10 px-4 rounded-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm font-medium transition inline-flex items-center gap-2 shrink-0 cursor-pointer shadow-2xs">
                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                <span class="hidden sm:inline">Modo Full</span>
            </button>

            <!-- 3. Botón Consulta IA (Pastel Violeta) -->
            <button type="button" 
                    data-id="{{ $producto->id }}"
                    data-name="{{ $producto->nombre }}"
                    onclick="abrirModalProductoIA(this.dataset.id, this.dataset.name)"
                    class="h-10 px-4 rounded-full bg-violet-50 dark:bg-violet-950/60 border border-violet-200 dark:border-violet-800 hover:bg-violet-100 dark:hover:bg-violet-900/60 text-violet-900 dark:text-violet-300 text-sm font-medium transition inline-flex items-center gap-2 cursor-pointer shadow-2xs">
                <svg class="w-4 h-4 text-violet-700 dark:text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                <span>Ficha IA</span>
            </button>

            <!-- 4. Botón Vender en POS (Pastel Esmeralda) -->
            @can('crear ventas')
            <a href="{{ route('ventas.create', ['producto_id' => $producto->id]) }}" 
               title="Cargar este producto directamente en el terminal POS"
               class="h-10 px-4 rounded-full bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 text-emerald-900 dark:text-emerald-300 text-sm font-medium transition inline-flex items-center gap-2 shadow-2xs">
                <svg class="w-4 h-4 text-emerald-700 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <span>Vender en POS</span>
            </a>
            @endcan

            <!-- 5. Botón Principal Editar -->
            @can('editar productos')
            <a href="{{ route('productos.edit', $producto) }}" 
               class="h-10 px-4 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium transition inline-flex items-center gap-2 shadow-xs cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Editar Medicamento</span>
            </a>
            @endcan
        </div>
    </div>

    @if($producto->esControlado())
    <!-- Banner Regulatorio MINSA -->
    <div class="rounded-2xl p-4 bg-purple-50/80 dark:bg-purple-950/30 border border-purple-200 dark:border-purple-800/60 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-start space-x-3">
            <div class="w-10 h-10 rounded-xl bg-purple-100 dark:bg-purple-900/50 text-purple-700 dark:text-purple-300 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <div>
                <div class="flex items-center space-x-2">
                    <h3 class="text-xs font-bold text-purple-950 dark:text-purple-200">
                        Medicamento Bajo Control y Fiscalización Sanitaria (MINSA)
                    </h3>
                    <span class="px-2 py-0.5 rounded text-[10px] font-black bg-purple-200 dark:bg-purple-800 text-purple-900 dark:text-purple-100 uppercase">
                        Controlado
                    </span>
                </div>
                <p class="text-xs text-purple-800 dark:text-purple-300 mt-0.5">
                    Toda dispensación en caja POS genera trazabilidad en la bitácora oficial MINSA con datos clínicos del médico/paciente o motivo de omisión.
                </p>
            </div>
        </div>
        <div class="shrink-0 flex items-center gap-2">
            <a href="{{ route('controlados.libro') }}" class="px-3 py-1.5 rounded-xl bg-purple-700 hover:bg-purple-800 text-white text-xs font-bold shadow-2xs transition flex items-center space-x-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                <span>Libro Oficial</span>
            </a>
        </div>
    </div>
    @endif

    <!-- SECCIÓN IDENTIFICACIÓN Y CÓDIGO DE BARRAS -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs p-5 overflow-hidden">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-center">
            
            <!-- Foto del Producto -->
            <div class="lg:col-span-3">
                <div class="relative h-52 rounded-xl bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-800 dark:to-slate-800/60 flex items-center justify-center p-4 border border-slate-200 dark:border-slate-700 overflow-hidden">
                    @if($producto->imagen)
                        <img src="{{ route('img.serve', ['path' => $producto->imagen]) }}" 
                             alt="{{ $producto->nombre }}" 
                             class="w-full h-full object-contain">
                    @else
                        <div class="text-center text-slate-400">
                            <svg class="w-14 h-14 mx-auto mb-1 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                            <span class="text-[11px] font-medium">Sin imagen</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Bloque de Identificación, Código de Barras y Categorización -->
            <div class="lg:col-span-5 space-y-3.5">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                        <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                        <span>{{ $producto->categoria?->nombre ?? 'General' }}</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                        <span>Lab: {{ $producto->laboratorio?->nombre ?? 'N/A' }}</span>
                    </span>
                    @if($producto->ubicacion)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                        <svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>Ubicación: {{ $producto->ubicacion }}</span>
                    </span>
                    @endif
                </div>

                <!-- Tarjeta Visual de Código de Barras -->
                <div class="bg-slate-50 dark:bg-slate-800/80 rounded-xl p-3.5 border border-slate-200 dark:border-slate-700 space-y-2"
                     x-data="{ copied: false }">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                            <span>Código de Barra / Identificador Escaneable</span>
                        </span>
                        @if($producto->codigo_barra)
                        <button type="button" 
                                @click="navigator.clipboard.writeText('{{ $producto->codigo_barra }}'); copied = true; setTimeout(() => copied = false, 2000)" 
                                class="inline-flex items-center space-x-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-600 hover:bg-slate-100 transition shadow-2xs cursor-pointer">
                            <span x-show="!copied" class="inline-flex items-center gap-1">
                                <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                                <span>Copiar</span>
                            </span>
                            <span x-show="copied" class="text-emerald-600 dark:text-emerald-400 inline-flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>¡Copiado!</span>
                            </span>
                        </button>
                        @endif
                    </div>

                    <div class="flex items-center space-x-3 bg-white dark:bg-slate-900 p-2.5 rounded-lg border border-slate-200/80 dark:border-slate-700/80">
                        <!-- Representación Gráfica de Barras -->
                        <div class="h-8 flex items-center space-x-0.5 shrink-0 px-1 bg-white dark:bg-slate-800 rounded border border-slate-200 dark:border-slate-700 select-none" title="Código de Barra FarmaBien">
                            <div class="w-1 h-6 bg-slate-900 dark:bg-slate-100"></div>
                            <div class="w-0.5 h-6 bg-slate-900 dark:bg-slate-100"></div>
                            <div class="w-1.5 h-6 bg-slate-900 dark:bg-slate-100"></div>
                            <div class="w-0.5 h-6 bg-slate-900 dark:bg-slate-100"></div>
                            <div class="w-1 h-6 bg-slate-900 dark:bg-slate-100"></div>
                            <div class="w-2 h-6 bg-slate-900 dark:bg-slate-100"></div>
                            <div class="w-0.5 h-6 bg-slate-900 dark:bg-slate-100"></div>
                            <div class="w-1.5 h-6 bg-slate-900 dark:bg-slate-100"></div>
                            <div class="w-1 h-6 bg-slate-900 dark:bg-slate-100"></div>
                            <div class="w-0.5 h-6 bg-slate-900 dark:bg-slate-100"></div>
                            <div class="w-2 h-6 bg-slate-900 dark:bg-slate-100"></div>
                        </div>

                        <!-- Número en Monospace Grande y Claro -->
                        <div class="min-w-0 flex-1">
                            <span class="font-mono text-base sm:text-lg font-black tracking-widest text-slate-900 dark:text-white block truncate">
                                {{ $producto->codigo_barra ?: 'SIN CÓDIGO REGISTRADO' }}
                            </span>
                            <span class="text-[10px] text-slate-400 font-mono">SKU ID: #{{ str_pad($producto->id, 6, '0', STR_PAD_LEFT) }}</span>
                        </div>
                    </div>
                </div>

                @if($producto->descripcion)
                <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed bg-slate-50 dark:bg-slate-800/50 p-2.5 rounded-xl border border-slate-100 dark:border-slate-800 line-clamp-2">
                    {{ $producto->descripcion }}
                </p>
                @endif
            </div>

            <!-- Indicadores Financieros y Precios -->
            <div class="lg:col-span-4 grid grid-cols-2 gap-3">
                <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 flex flex-col justify-between">
                    <div>
                        <span class="text-[10px] uppercase font-bold text-slate-400">Precio Venta (Base)</span>
                        <div class="text-xl font-black text-emerald-600 dark:text-emerald-400 mt-0.5">
                            {{ formato_moneda($producto->precio_venta) }}
                        </div>
                        <span class="text-[10px] text-slate-400">por unidad</span>
                    </div>
                    <a href="{{ route('precios.show', $producto) }}" class="mt-2 text-[11px] font-bold text-emerald-700 dark:text-emerald-400 hover:underline inline-flex items-center gap-1">
                        <span>Ver Historial de Precios</span>
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>

                <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700">
                    <span class="text-[10px] uppercase font-bold text-slate-400">Precio Compra</span>
                    <div class="text-xl font-black text-slate-700 dark:text-slate-300 mt-0.5">
                        {{ formato_moneda($producto->precio_compra ?? 0) }}
                    </div>
                    <span class="text-[10px] text-slate-400">costo adquisición</span>
                </div>

                <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700">
                    <span class="text-[10px] uppercase font-bold text-slate-400">Margen Estimado</span>
                    @php
                        $compra = (float)($producto->precio_compra ?? 0);
                        $venta = (float)$producto->precio_venta;
                        $margen = $venta > 0 ? (($venta - $compra) / $venta) * 100 : 0;
                    @endphp
                    <div class="text-xl font-black text-slate-800 dark:text-slate-200 mt-0.5">
                        {{ number_format($margen, 1) }}%
                    </div>
                    <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-semibold">Rentabilidad</span>
                </div>

                <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700">
                    <span class="text-[10px] uppercase font-bold text-slate-400">Stock Mínimo</span>
                    <div class="text-xl font-black text-slate-700 dark:text-slate-300 mt-0.5">
                        {{ $producto->stock_minimo }} <span class="text-xs font-normal">unid.</span>
                    </div>
                    <span class="text-[10px] text-slate-400">umbral de alerta</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Ficha Técnica Farmacológica y Estado de Inventario -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        
        <!-- Ficha Técnica -->
        <div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-xs space-y-4">
            <h2 class="text-sm font-bold text-slate-900 dark:text-white flex items-center space-x-2">
                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Ficha Técnica y Farmacológica</span>
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                    <span class="text-[10px] text-slate-400 uppercase font-semibold block">Principio Activo</span>
                    <span class="font-bold text-slate-800 dark:text-slate-200 text-sm">{{ $producto->principio_activo ?: 'No especificado' }}</span>
                </div>

                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                    <span class="text-[10px] text-slate-400 uppercase font-semibold block">Concentración</span>
                    <span class="font-bold text-slate-800 dark:text-slate-200 text-sm">{{ $producto->concentracion ?: 'Estándar' }}</span>
                </div>

                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                    <span class="text-[10px] text-slate-400 uppercase font-semibold block">Forma Farmacéutica</span>
                    <span class="font-bold text-slate-800 dark:text-slate-200 text-sm">{{ $producto->forma_farmaceutica ?: 'Tableta / Cápsula' }}</span>
                </div>

                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                    <span class="text-[10px] text-slate-400 uppercase font-semibold block">Registro Sanitario / MINSA</span>
                    <span class="font-mono font-bold text-slate-800 dark:text-slate-200 text-sm">{{ $producto->registro_sanitario ?: 'En trámite / No registrado' }}</span>
                </div>

                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                    <span class="text-[10px] text-slate-400 uppercase font-semibold block">Régimen de Control</span>
                    <span class="font-bold text-slate-800 dark:text-slate-200 text-sm">
                        @if($producto->esControlado()) 🟣 Controlado / Con Receta Médica (MINSA)
                        @else 🟢 Venta Libre (OTC)
                        @endif
                    </span>
                </div>

                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                    <span class="text-[10px] text-slate-400 uppercase font-semibold block">Ubicación en Farmacia</span>
                    <span class="font-bold text-slate-800 dark:text-slate-200 text-sm">{{ $producto->ubicacion ?: 'Estante Principal' }}</span>
                </div>
            </div>
        </div>

        <!-- Panel de Stock Actual y Alertas -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-xs space-y-4">
            <h2 class="text-sm font-bold text-slate-900 dark:text-white flex items-center space-x-2">
                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                <span>Estado de Existencias</span>
            </h2>

            @php
                $stockTotal = $producto->lotes->where('activo', true)->sum('stock_actual');
                $isAgotado = $stockTotal <= 0;
                $isBajo = !$isAgotado && ($stockTotal <= $producto->stock_minimo);
            @endphp

            <div class="p-4 rounded-xl text-center {{ $isAgotado ? 'bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800' : ($isBajo ? 'bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800' : 'bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800') }}">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Stock Total Disponible</span>
                <div class="text-3xl font-black mt-1 {{ $isAgotado ? 'text-rose-600 dark:text-rose-400' : ($isBajo ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400') }}">
                    {{ $stockTotal }} <span class="text-sm font-semibold">unidades</span>
                </div>

                @if($isAgotado)
                <p class="text-xs text-rose-700 dark:text-rose-300 mt-2 font-medium flex items-center justify-center gap-1">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>Producto sin existencias. Reabastecimiento urgente.</span>
                </p>
                @elseif($isBajo)
                <p class="text-xs text-amber-700 dark:text-amber-300 mt-2 font-medium flex items-center justify-center gap-1">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>Stock por debajo del umbral mínimo de {{ $producto->stock_minimo }} unid.</span>
                </p>
                @else
                <p class="text-xs text-emerald-700 dark:text-emerald-300 mt-2 font-medium flex items-center justify-center gap-1">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Nivel de existencias óptimo para la venta.</span>
                </p>
                @endif
            </div>

            <div class="pt-2 border-t border-slate-100 dark:border-slate-800 space-y-2 text-xs">
                <div class="flex justify-between text-slate-600 dark:text-slate-400">
                    <span>Lotes Activos:</span>
                    <span class="font-bold text-slate-900 dark:text-white">{{ $producto->lotes->where('activo', true)->count() }}</span>
                </div>
                <div class="flex justify-between text-slate-600 dark:text-slate-400">
                    <span>Fecha Registro:</span>
                    <span class="font-bold text-slate-900 dark:text-white">{{ $producto->created_at->format('d/m/Y H:i') }}</span>
                </div>
                <div class="flex justify-between text-slate-600 dark:text-slate-400">
                    <span>Último Movimiento:</span>
                    <span class="font-bold text-slate-900 dark:text-white">{{ $producto->updated_at->diffForHumans() }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Presentaciones Comerciales y Dispensación Fraccionada -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-xs space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h2 class="text-sm font-bold text-slate-900 dark:text-white flex items-center space-x-2">
                    <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    <span>Presentaciones Comerciales y Venta Fraccionada (Caja, Blíster, Unidad)</span>
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Control de existencias unificado: el stock se descuenta en unidades base automáticamente al vender por caja, blíster o unidad.
                </p>
            </div>
            <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 shrink-0">
                Stock Base: {{ $stockTotal }} unidades
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @if($producto->presentaciones && $producto->presentaciones->count() > 0)
                @foreach($producto->presentaciones as $pres)
                @php
                    $cantEnPres = $pres->unidades_por_presentacion > 0 ? floor($stockTotal / $pres->unidades_por_presentacion) : 0;
                    $unitPrice = $pres->unidades_por_presentacion > 0 ? ($pres->precio_venta / $pres->unidades_por_presentacion) : $pres->precio_venta;
                @endphp
                <div class="p-4 rounded-xl border {{ $pres->es_unidad_base ? 'border-emerald-300 dark:border-emerald-700 bg-emerald-50/40 dark:bg-emerald-950/20' : 'border-slate-200 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-800/40' }} space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-slate-900 dark:text-white text-xs">{{ $pres->nombre }}</span>
                        @if($pres->es_unidad_base)
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-200 dark:bg-emerald-900 text-emerald-800 dark:text-emerald-200">Unidad Base</span>
                        @else
                        <span class="text-[11px] font-mono text-slate-500">x{{ $pres->unidades_por_presentacion }} unid.</span>
                        @endif
                    </div>
                    <div class="flex items-baseline justify-between pt-1">
                        <div class="text-base font-black text-emerald-600 dark:text-emerald-400 font-mono">
                            {{ formato_moneda($pres->precio_venta) }}
                        </div>
                        <div class="text-[11px] text-slate-400">
                            ({{ formato_moneda($unitPrice) }} / unid.)
                        </div>
                    </div>
                    <div class="pt-2 border-t border-slate-200/60 dark:border-slate-700 text-xs flex justify-between items-center">
                        <span class="text-slate-500">Equivalente disponible:</span>
                        <span class="font-bold text-slate-800 dark:text-slate-200">{{ $cantEnPres }} {{ strtolower($pres->nombre) }}s</span>
                    </div>
                </div>
                @endforeach
            @else
                <!-- Previsualización Estándar de Fraccionamiento FarmaBien -->
                <div class="p-4 rounded-xl border border-emerald-300 dark:border-emerald-700 bg-emerald-50/40 dark:bg-emerald-950/20 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-slate-900 dark:text-white text-xs">Unidad / Pastilla Suelta</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-200 dark:bg-emerald-900 text-emerald-800 dark:text-emerald-200">Base</span>
                    </div>
                    <div class="flex items-baseline justify-between pt-1">
                        <div class="text-base font-black text-emerald-600 dark:text-emerald-400 font-mono">
                            {{ formato_moneda($producto->precio_venta) }}
                        </div>
                        <div class="text-[11px] text-slate-400">por unidad</div>
                    </div>
                    <div class="pt-2 border-t border-slate-200/60 dark:border-slate-700 text-xs flex justify-between items-center">
                        <span class="text-slate-500">Disponible:</span>
                        <span class="font-bold text-slate-800 dark:text-slate-200">{{ $stockTotal }} unidades</span>
                    </div>
                </div>

                <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-800/40 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-slate-900 dark:text-white text-xs">Blíster (Tira x 10)</span>
                        <span class="text-[11px] font-mono text-slate-500">x10 unid.</span>
                    </div>
                    @php
                        $blisterPrice = $producto->precio_venta * 10 * 0.95; // 5% dto por blister
                        $blistersDisp = floor($stockTotal / 10);
                    @endphp
                    <div class="flex items-baseline justify-between pt-1">
                        <div class="text-base font-black text-slate-800 dark:text-slate-200 font-mono">
                            {{ formato_moneda($blisterPrice) }}
                        </div>
                        <div class="text-[11px] text-slate-400">sugerido</div>
                    </div>
                    <div class="pt-2 border-t border-slate-200/60 dark:border-slate-700 text-xs flex justify-between items-center">
                        <span class="text-slate-500">Equivalente:</span>
                        <span class="font-bold text-slate-800 dark:text-slate-200">{{ $blistersDisp }} blísteres</span>
                    </div>
                </div>

                <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-800/40 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-slate-900 dark:text-white text-xs">Caja Completa (x 100)</span>
                        <span class="text-[11px] font-mono text-slate-500">x100 unid.</span>
                    </div>
                    @php
                        $cajaPrice = $producto->precio_venta * 100 * 0.90; // 10% dto por caja
                        $cajasDisp = floor($stockTotal / 100);
                    @endphp
                    <div class="flex items-baseline justify-between pt-1">
                        <div class="text-base font-black text-slate-800 dark:text-slate-200 font-mono">
                            {{ formato_moneda($cajaPrice) }}
                        </div>
                        <div class="text-[11px] text-slate-400">sugerido</div>
                    </div>
                    <div class="pt-2 border-t border-slate-200/60 dark:border-slate-700 text-xs flex justify-between items-center">
                        <span class="text-slate-500">Equivalente:</span>
                        <span class="font-bold text-slate-800 dark:text-slate-200">{{ $cajasDisp }} cajas</span>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Desglose de Lotes con Fechas de Vencimiento -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-xs space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-900 dark:text-white flex items-center space-x-2">
                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span>Lotes y Control de Vencimientos</span>
            </h2>
            <a href="{{ route('inventario.lotes') }}" class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 hover:underline">
                Gestionar Lotes &rarr;
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50 text-slate-500 font-semibold uppercase text-[11px]">
                        <th class="py-2.5 px-3">Número de Lote</th>
                        <th class="py-2.5 px-3">Fecha Vencimiento</th>
                        <th class="py-2.5 px-3 text-right">Stock Inicial</th>
                        <th class="py-2.5 px-3 text-right">Stock Disponible</th>
                        <th class="py-2.5 px-3 text-center">Estado de Caducidad</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($producto->lotes as $lote)
                    @php
                        $fechaVenc = \Carbon\Carbon::parse($lote->fecha_vencimiento);
                        $diasRestantes = now()->diffInDays($fechaVenc, false);
                    @endphp
                    <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40">
                        <td class="py-2.5 px-3 font-mono font-semibold text-slate-900 dark:text-white">
                            {{ $lote->numero_lote }}
                        </td>
                        <td class="py-2.5 px-3 text-slate-700 dark:text-slate-300">
                            {{ $fechaVenc->format('d/m/Y') }}
                        </td>
                        <td class="py-2.5 px-3 text-right text-slate-500">
                            {{ $lote->stock_inicial }}
                        </td>
                        <td class="py-2.5 px-3 text-right font-bold text-slate-900 dark:text-white">
                            {{ $lote->stock_actual }}
                        </td>
                        <td class="py-2.5 px-3 text-center">
                            @if($diasRestantes < 0)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300">
                                    Vencido hace {{ abs((int)$diasRestantes) }} días
                                </span>
                            @elseif($diasRestantes <= 60)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300">
                                    Por vencer ({{ (int)$diasRestantes }} días)
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300">
                                    Vigente ({{ (int)$diasRestantes }} días)
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-6 text-center text-slate-400">
                            No hay lotes registrados para este medicamento. Los lotes se crean automáticamente al registrar una compra.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Ficha Clínica IA -->
<div id="modalProductoIA" class="hidden fixed inset-0 z-[9999] flex items-center justify-center p-3 sm:p-4 bg-slate-950/75 backdrop-blur-sm overflow-y-auto" role="dialog" aria-modal="true" onclick="if(event.target === this) cerrarModalProductoIA()">
    <div class="bg-white dark:bg-slate-900 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all w-full max-w-2xl border border-slate-200 dark:border-slate-800 my-auto">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 bg-gradient-to-r from-emerald-50 via-teal-50 to-white dark:from-emerald-950/30 dark:via-slate-900 dark:to-slate-900 flex items-center justify-between">
                <div class="flex items-center space-x-2.5">
                    <div class="w-9 h-9 rounded-xl bg-emerald-600 text-white flex items-center justify-center shadow-xs">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                    </div>
                    <div>
                        <div class="flex items-center space-x-2">
                            <h3 id="modalProductoIA_title" class="text-base font-bold text-slate-900 dark:text-white">Ficha Farmacológica IA</h3>
                            <span id="ia_status_badge" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">Motor Local</span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Guía asistida de uso clínico, posología, advertencias y recomendaciones farmacéuticas.</p>
                    </div>
                </div>
                <button type="button" onclick="cerrarModalProductoIA()" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto">
                <div id="modalProductoIA_loading" class="py-8 text-center space-y-2">
                    <div class="inline-block w-7 h-7 border-2 border-emerald-500 border-t-transparent rounded-full animate-spin"></div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Consultando IA farmacológica...</p>
                </div>

                <div id="modalProductoIA_content" class="hidden space-y-3.5">
                    <!-- Uso Clínico -->
                    <div class="p-3.5 rounded-xl bg-indigo-50/70 dark:bg-indigo-950/30 border border-indigo-200 dark:border-indigo-800/60">
                        <div class="flex items-center space-x-2 text-xs font-bold text-indigo-800 dark:text-indigo-300 mb-1">
                            <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Uso Clínico e Indicaciones Terapéuticas</span>
                        </div>
                        <p id="ia_uso_clinico" class="text-xs text-indigo-950 dark:text-indigo-200 leading-relaxed"></p>
                    </div>

                    <!-- Posología -->
                    <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/70 border border-slate-200 dark:border-slate-700">
                        <div class="flex items-center space-x-2 text-xs font-bold text-emerald-700 dark:text-emerald-300 mb-1">
                            <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Posología y Modo de Uso Recomendado</span>
                        </div>
                        <p id="ia_posologia" class="text-xs text-slate-700 dark:text-slate-300 leading-relaxed"></p>
                    </div>

                    <!-- Recomendaciones Farmacéuticas -->
                    <div class="p-3.5 rounded-xl bg-teal-50/70 dark:bg-teal-950/30 border border-teal-200 dark:border-teal-800/60">
                        <div class="flex items-center space-x-2 text-xs font-bold text-teal-800 dark:text-teal-300 mb-1">
                            <svg class="w-4 h-4 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Recomendaciones del Farmacéutico al Paciente</span>
                        </div>
                        <p id="ia_recomendaciones" class="text-xs text-teal-950 dark:text-teal-200 leading-relaxed"></p>
                    </div>

                    <!-- Advertencias -->
                    <div class="p-3.5 rounded-xl bg-amber-50/70 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/60">
                        <div class="flex items-center space-x-2 text-xs font-bold text-amber-800 dark:text-amber-300 mb-1">
                            <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <span>Advertencias y Efectos Adversos</span>
                        </div>
                        <p id="ia_advertencias" class="text-xs text-amber-900 dark:text-amber-200 leading-relaxed"></p>
                    </div>

                    <!-- Contraindicaciones -->
                    <div class="p-3.5 rounded-xl bg-rose-50/70 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800/60">
                        <div class="flex items-center space-x-2 text-xs font-bold text-rose-700 dark:text-rose-300 mb-1">
                            <svg class="w-4 h-4 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                            <span>Contraindicaciones Clínicas e Interacciones</span>
                        </div>
                        <p id="ia_contraindicaciones" class="text-xs text-rose-800 dark:text-rose-200 leading-relaxed"></p>
                    </div>

                    <!-- Sustitutos -->
                    <div class="space-y-2 pt-1 border-t border-slate-100 dark:border-slate-800">
                        <div class="flex items-center space-x-2 text-xs font-bold text-slate-800 dark:text-slate-200">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span>Alternativas y Sustitutos en Inventario FarmaBien</span>
                        </div>
                        <div id="ia_sustitutos" class="grid grid-cols-1 sm:grid-cols-2 gap-2"></div>
                    </div>
                </div>

                <div id="modalProductoIA_error" class="hidden p-3 rounded-xl bg-rose-50 dark:bg-rose-950/50 text-rose-700 dark:text-rose-300 text-xs border border-rose-200 dark:border-rose-800"></div>
            </div>

            <div class="px-6 py-3 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-800 flex justify-end">
                <button type="button" onclick="cerrarModalProductoIA()" class="px-3.5 py-1.5 rounded-xl bg-slate-200 dark:bg-slate-700 text-xs font-semibold text-slate-800 dark:text-slate-200 hover:bg-slate-300 dark:hover:bg-slate-600 transition cursor-pointer">
                    Cerrar
                </button>
            </div>
        </div>
</div>

@push('scripts')
<script>
const __productoIaUrlTpl = @json(route('productos.ia.ficha', ['producto' => '__ID__']));

function escapeHtml(str) {
    return String(str ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function abrirModalProductoIA(id, nombre) {
    const modal = document.getElementById('modalProductoIA');
    const title = document.getElementById('modalProductoIA_title');
    const loading = document.getElementById('modalProductoIA_loading');
    const content = document.getElementById('modalProductoIA_content');
    const errorBox = document.getElementById('modalProductoIA_error');

    title.textContent = `Ficha IA: ${nombre}`;
    loading.classList.remove('hidden');
    content.classList.add('hidden');
    errorBox.classList.add('hidden');

    modal.classList.remove('hidden');

    const url = __productoIaUrlTpl.replace('__ID__', id);
    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(async (r) => {
        const data = await r.json().catch(() => ({}));
        if (!r.ok || !data.ok) throw new Error(data.message || 'Error obteniendo datos');

        loading.classList.add('hidden');
        content.classList.remove('hidden');

        // Status badge
        const badge = document.getElementById('ia_status_badge');
        if (badge) {
            if (data.fuente_ia === 'api_externa') {
                badge.className = 'px-2 py-0.5 rounded-full text-[10px] font-bold bg-violet-100 dark:bg-violet-950/60 text-violet-700 dark:text-violet-300 border border-violet-200 dark:border-violet-800';
                badge.textContent = data.generado_por || 'IA Externa';
            } else {
                badge.className = 'px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800';
                badge.textContent = 'Motor Local FarmaBien';
            }
        }

        document.getElementById('ia_uso_clinico').textContent = data.uso_clinico || 'Consulte al profesional de la salud.';
        document.getElementById('ia_posologia').textContent = data.posologia || 'Consulte al farmacéutico.';
        document.getElementById('ia_recomendaciones').textContent = data.recomendaciones || 'Seguir las pautas indicadas en el empaque del fabricante.';
        document.getElementById('ia_advertencias').textContent = data.advertencias || 'Mantener fuera del alcance de niños.';
        document.getElementById('ia_contraindicaciones').textContent = data.contraindicaciones || 'Ninguna descrita.';

        const sustBox = document.getElementById('ia_sustitutos');
        if (data.sustitutos && data.sustitutos.length > 0) {
            sustBox.innerHTML = data.sustitutos.map(s => `
                <a href="${escapeHtml(s.url)}" class="p-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/40 hover:bg-emerald-50/50 dark:hover:bg-emerald-950/30 transition block">
                    <div class="font-bold text-slate-800 dark:text-slate-200 truncate">${escapeHtml(s.nombre)}</div>
                    <div class="text-[11px] text-slate-500 truncate">${escapeHtml(s.principio || 'Equivalente')}</div>
                    <div class="text-xs font-bold text-emerald-600 dark:text-emerald-400 mt-1">C$ ${escapeHtml(s.precio)}</div>
                </a>
            `).join('');
        } else {
            sustBox.innerHTML = `<div class="col-span-2 text-xs text-slate-400">No hay otros productos sustitutos en inventario.</div>`;
        }
    })
    .catch((err) => {
        loading.classList.add('hidden');
        errorBox.classList.remove('hidden');
        errorBox.textContent = err.message || 'No se pudo generar la ficha clínica.';
    });
}

function cerrarModalProductoIA() {
    document.getElementById('modalProductoIA').classList.add('hidden');
}

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') cerrarModalProductoIA();
});
</script>
@endpush
@endsection