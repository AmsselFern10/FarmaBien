@extends('layouts.app')

@section('title', $producto->nombre . ' - Detalle Farmacológico - FarmaBien')

@section('content')
<div class="space-y-5">
    
    <!-- Breadcrumbs -->
    <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('productos.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Medicamentos</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-800 dark:text-slate-200 font-semibold truncate max-w-[200px]">{{ $producto->nombre }}</span>
    </nav>

    <!-- Header & Quick Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <div class="flex items-center space-x-3">
                <h1 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <span>{{ $producto->nombre }}</span>
                </h1>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold {{ $producto->activo ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-700' }}">
                    {{ $producto->activo ? 'Activo' : 'Inactivo' }}
                </span>
                @if($producto->requiere_receta)
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500 text-white shadow-xs">
                    Rx Receta Obligatoria
                </span>
                @endif
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                {{ $producto->principio_activo ?: 'Fórmula Farmacéutica' }} @if($producto->concentracion) • {{ $producto->concentracion }} @endif @if($producto->forma_farmaceutica) ({{ $producto->forma_farmaceutica }}) @endif
            </p>
        </div>

        <div class="flex items-center space-x-2 shrink-0">
            <a href="{{ route('productos.index') }}" 
               class="px-3.5 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold transition flex items-center space-x-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Volver al Catálogo</span>
            </a>

            <!-- Botón Consulta IA -->
            <button type="button" 
                    data-id="{{ $producto->id }}"
                    data-name="{{ $producto->nombre }}"
                    onclick="abrirModalProductoIA(this.dataset.id, this.dataset.name)"
                    class="px-3.5 py-2 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-700 hover:to-indigo-700 text-white text-xs font-semibold shadow-xs transition flex items-center space-x-1.5">
                <span>✨</span>
                <span>Consultar Ficha IA</span>
            </button>

            @can('editar productos')
            <a href="{{ route('productos.edit', $producto) }}" 
               class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-xs transition flex items-center space-x-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Editar Fármaco</span>
            </a>
            @endcan
        </div>
    </div>

    <!-- SECCIÓN ULTRA-DESTACADA: IDENTIFICACIÓN Y CÓDIGO DE BARRAS -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs p-5 overflow-hidden">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-center">
            
            <!-- Foto del Producto -->
            <div class="lg:col-span-3">
                <div class="relative h-52 rounded-xl bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-800 dark:to-slate-800/60 flex items-center justify-center p-4 border border-slate-200 dark:border-slate-700 overflow-hidden">
                    @if($producto->imagen)
                        <img src="{{ asset('storage/' . $producto->imagen) }}" 
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
                    <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                        📁 {{ $producto->categoria?->nombre ?? 'General' }}
                    </span>
                    <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                        🔬 Lab: {{ $producto->laboratorio?->nombre ?? 'N/A' }}
                    </span>
                    @if($producto->ubicacion)
                    <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                        📍 Ubicación: {{ $producto->ubicacion }}
                    </span>
                    @endif
                </div>

                <!-- Tarjeta Visual de Código de Barras (Alta Legibilidad) -->
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
                            <span x-show="!copied">📋 Copiar</span>
                            <span x-show="copied" class="text-emerald-600 dark:text-emerald-400">✓ ¡Copiado!</span>
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
                <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700">
                    <span class="text-[10px] uppercase font-bold text-slate-400">Precio Venta (Base)</span>
                    <div class="text-xl font-black text-emerald-600 dark:text-emerald-400 mt-0.5">
                        S/ {{ number_format($producto->precio_venta, 2) }}
                    </div>
                    <span class="text-[10px] text-slate-400">por unidad</span>
                </div>

                <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700">
                    <span class="text-[10px] uppercase font-bold text-slate-400">Precio Compra</span>
                    <div class="text-xl font-black text-slate-700 dark:text-slate-300 mt-0.5">
                        S/ {{ number_format($producto->precio_compra ?? 0, 2) }}
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
                <span class="text-emerald-500">📋</span>
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
                    <span class="text-[10px] text-slate-400 uppercase font-semibold block">Registro Sanitario (DIGEMID)</span>
                    <span class="font-mono font-bold text-slate-800 dark:text-slate-200 text-sm">{{ $producto->registro_sanitario ?: 'En trámite / No registrado' }}</span>
                </div>

                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                    <span class="text-[10px] text-slate-400 uppercase font-semibold block">Régimen de Control</span>
                    <span class="font-bold text-slate-800 dark:text-slate-200 text-sm">
                        @if($producto->tipo_control === 'venta_libre') Venta Libre Sin Receta
                        @elseif($producto->tipo_control === 'receta_medica') Venta Bajo Receta Médica
                        @elseif($producto->tipo_control === 'receta_retenida') Psicotrópico / Receta Retenida
                        @else {{ ucfirst(str_replace('_', ' ', $producto->tipo_control)) }}
                        @endif
                    </span>
                </div>

                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                    <span class="text-[10px] text-slate-400 uppercase font-semibold block">Ubicación en Botica</span>
                    <span class="font-bold text-slate-800 dark:text-slate-200 text-sm">{{ $producto->ubicacion ?: 'Estante Principal' }}</span>
                </div>
            </div>
        </div>

        <!-- Panel de Stock Actual y Alertas -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-xs space-y-4">
            <h2 class="text-sm font-bold text-slate-900 dark:text-white flex items-center space-x-2">
                <span class="text-emerald-500">📦</span>
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
                <p class="text-xs text-rose-700 dark:text-rose-300 mt-2 font-medium">
                    ⚠️ Producto sin existencias. Se requiere reabastecimiento urgente.
                </p>
                @elseif($isBajo)
                <p class="text-xs text-amber-700 dark:text-amber-300 mt-2 font-medium">
                    ⚠️ El stock actual está por debajo del umbral mínimo de {{ $producto->stock_minimo }} unid.
                </p>
                @else
                <p class="text-xs text-emerald-700 dark:text-emerald-300 mt-2 font-medium">
                    ✓ Nivel de existencias óptimo para la venta regular.
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
                    <span class="text-emerald-500">💊</span>
                    <span>Presentaciones Comerciales y Venta Fraccionada (Caja, Blíster, Unidad)</span>
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Control de existencias unificado: el stock se descuenta en unidades base automáticamente al vender por caja, blíster o pastilla suelta.
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
                    $sobrantes = $pres->unidades_por_presentacion > 0 ? ($stockTotal % $pres->unidades_por_presentacion) : 0;
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
                        <div class="text-base font-black text-emerald-600 dark:text-emerald-400">
                            S/ {{ number_format($pres->precio_venta, 2) }}
                        </div>
                        <div class="text-[11px] text-slate-400">
                            (S/ {{ number_format($unitPrice, 2) }} / unid.)
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
                        <div class="text-base font-black text-emerald-600 dark:text-emerald-400">
                            S/ {{ number_format($producto->precio_venta, 2) }}
                        </div>
                        <div class="text-[11px] text-slate-400">por pastilla</div>
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
                        <div class="text-base font-black text-slate-800 dark:text-slate-200">
                            S/ {{ number_format($blisterPrice, 2) }}
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
                        <div class="text-base font-black text-slate-800 dark:text-slate-200">
                            S/ {{ number_format($cajaPrice, 2) }}
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
                <span class="text-emerald-500">🗓️</span>
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
<div id="modalProductoIA" class="hidden fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
        <div class="fixed inset-0 bg-slate-900/70 backdrop-blur-xs transition-opacity" onclick="cerrarModalProductoIA()"></div>

        <div class="inline-block align-bottom bg-white dark:bg-slate-900 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-200 dark:border-slate-800">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 bg-gradient-to-r from-emerald-50 via-teal-50 to-white dark:from-emerald-950/30 dark:via-slate-900 dark:to-slate-900 flex items-center justify-between">
                <div class="flex items-center space-x-2.5">
                    <div class="w-9 h-9 rounded-xl bg-emerald-600 text-white flex items-center justify-center shadow-xs text-base">💊</div>
                    <div>
                        <div class="flex items-center space-x-2">
                            <h3 id="modalProductoIA_title" class="text-base font-bold text-slate-900 dark:text-white">Ficha Farmacológica IA</h3>
                            <span id="ia_status_badge" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">Motor Local</span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Guía asistida de uso clínico, posología, advertencias y recomendaciones farmacéuticas.</p>
                    </div>
                </div>
                <button type="button" onclick="cerrarModalProductoIA()" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition">✕</button>
            </div>

            <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto">
                <div id="modalProductoIA_loading" class="py-8 text-center space-y-2">
                    <div class="inline-block w-7 h-7 border-2 border-emerald-500 border-t-transparent rounded-full animate-spin"></div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Consultando IA farmacológica...</p>
                </div>

                <div id="modalProductoIA_content" class="hidden space-y-3.5">
                    <!-- Uso Clínico -->
                    <div class="p-3.5 rounded-xl bg-indigo-50/70 dark:bg-indigo-950/30 border border-indigo-200 dark:border-indigo-800/60">
                        <div class="text-xs font-bold text-indigo-800 dark:text-indigo-300 mb-1">🎯 Uso Clínico e Indicaciones Terapéuticas</div>
                        <p id="ia_uso_clinico" class="text-xs text-indigo-950 dark:text-indigo-200 leading-relaxed"></p>
                    </div>

                    <!-- Posología -->
                    <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/70 border border-slate-200 dark:border-slate-700">
                        <div class="text-xs font-bold text-emerald-700 dark:text-emerald-300 mb-1">⏱️ Posología y Modo de Uso Recomendado</div>
                        <p id="ia_posologia" class="text-xs text-slate-700 dark:text-slate-300 leading-relaxed"></p>
                    </div>

                    <!-- Recomendaciones Farmacéuticas -->
                    <div class="p-3.5 rounded-xl bg-teal-50/70 dark:bg-teal-950/30 border border-teal-200 dark:border-teal-800/60">
                        <div class="text-xs font-bold text-teal-800 dark:text-teal-300 mb-1">💡 Recomendaciones del Farmacéutico al Paciente</div>
                        <p id="ia_recomendaciones" class="text-xs text-teal-950 dark:text-teal-200 leading-relaxed"></p>
                    </div>

                    <!-- Advertencias -->
                    <div class="p-3.5 rounded-xl bg-amber-50/70 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/60">
                        <div class="text-xs font-bold text-amber-800 dark:text-amber-300 mb-1">⚠️ Advertencias y Efectos Adversos</div>
                        <p id="ia_advertencias" class="text-xs text-amber-900 dark:text-amber-200 leading-relaxed"></p>
                    </div>

                    <!-- Contraindicaciones -->
                    <div class="p-3.5 rounded-xl bg-rose-50/70 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800/60">
                        <div class="text-xs font-bold text-rose-700 dark:text-rose-300 mb-1">🚫 Contraindicaciones Clínicas e Interacciones</div>
                        <p id="ia_contraindicaciones" class="text-xs text-rose-800 dark:text-rose-200 leading-relaxed"></p>
                    </div>

                    <!-- Sustitutos -->
                    <div class="space-y-2 pt-1 border-t border-slate-100 dark:border-slate-800">
                        <div class="text-xs font-bold text-slate-800 dark:text-slate-200">🔄 Alternativas y Sustitutos en Inventario FarmaBien</div>
                        <div id="ia_sustitutos" class="grid grid-cols-1 sm:grid-cols-2 gap-2"></div>
                    </div>
                </div>

                <div id="modalProductoIA_error" class="hidden p-3 rounded-xl bg-rose-50 text-rose-700 text-xs border border-rose-200"></div>
            </div>

            <div class="px-6 py-3 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-800 flex justify-end">
                <button type="button" onclick="cerrarModalProductoIA()" class="px-3.5 py-1.5 rounded-xl bg-slate-200 dark:bg-slate-700 text-xs font-semibold text-slate-800 dark:text-slate-200 hover:bg-slate-300 transition">
                    Cerrar
                </button>
            </div>
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
                badge.textContent = '✨ ' + (data.generado_por || 'IA Externa');
            } else {
                badge.className = 'px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800';
                badge.textContent = '🧪 Motor Local (Configura AI_API_KEY en .env)';
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
                <a href="${escapeHtml(s.url)}" class="p-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/40 hover:bg-emerald-50/50 transition block">
                    <div class="font-bold text-slate-800 dark:text-slate-200 truncate">${escapeHtml(s.nombre)}</div>
                    <div class="text-[11px] text-slate-500 truncate">${escapeHtml(s.principio || 'Equivalente')}</div>
                    <div class="text-xs font-bold text-emerald-600 dark:text-emerald-400 mt-1">S/ ${escapeHtml(s.precio)}</div>
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