@extends('layouts.app')

@section('title', 'Comparador de Precios y Cotizaciones - FarmaBien')

@section('content')
<div class="space-y-5" x-data="{
    modalCotizacion: false,
    cotizacionProdId: '{{ $productoSeleccionado->id ?? '' }}',
    cotizacionProdNombre: '{{ $productoSeleccionado->nombre ?? '' }}',
    cotizacionPresentaciones: @js($productoSeleccionado ? $productoSeleccionado->presentacionesActivas : []),
    cotizacionPrecio: '',
    cotizacionPresId: '',
    cotizacionUnidades: 1,
    abrirModalCotizacion(prodId, prodNombre, presentaciones) {
        this.cotizacionProdId = prodId || '{{ $productoSeleccionado->id ?? '' }}';
        this.cotizacionProdNombre = prodNombre || '{{ $productoSeleccionado->nombre ?? '' }}';
        this.cotizacionPresentaciones = presentaciones || @js($productoSeleccionado ? $productoSeleccionado->presentacionesActivas : []);
        this.cotizacionPrecio = '';
        this.cotizacionPresId = '';
        this.cotizacionUnidades = 1;
        this.modalCotizacion = true;
    },
    actualizarUnidades() {
        if (!this.cotizacionPresId) {
            this.cotizacionUnidades = 1;
            return;
        }
        const pres = this.cotizacionPresentaciones.find(p => p.id == this.cotizacionPresId);
        this.cotizacionUnidades = pres ? (pres.unidades_por_presentacion || 1) : 1;
    },
    get precioUnitarioCalculado() {
        const p = parseFloat(this.cotizacionPrecio) || 0;
        const u = parseInt(this.cotizacionUnidades) || 1;
        return u > 0 ? (p / u).toFixed(4) : '0.0000';
    }
}">

    <!-- Breadcrumb -->
    <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('compras.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Compras</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-800 dark:text-slate-200 font-semibold">Comparador de Precios</span>
    </nav>

    <!-- Header & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <span>Comparador de Precios y Cotizaciones</span>
                <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                    Proveedores
                </span>
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Evalúa cotizaciones entre distribuidores, detecta el mejor precio por unidad y optimiza los costos de compra.
            </p>
        </div>
        <div class="flex items-center gap-2.5 flex-wrap shrink-0">
            <!-- Botón Modo Full -->
            <button type="button" 
                    @click="$dispatch('toggle-pos-fullscreen')"
                    title="Modo Pantalla Completa / Ocultar Barras"
                    class="inline-flex items-center space-x-1.5 px-3 py-2 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold shadow-2xs transition cursor-pointer">
                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                <span>Modo Full</span>
            </button>

            @can('registrar compras')
            <button type="button" 
                    @click="abrirModalCotizacion()"
                    class="inline-flex items-center space-x-1.5 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-semibold rounded-xl shadow-xs transition cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Nueva Cotización</span>
            </button>
            <a href="{{ route('compras.sugerencias-reorden') }}" 
               class="inline-flex items-center space-x-1.5 px-3.5 py-2 bg-amber-500 hover:bg-amber-600 active:bg-amber-700 text-white text-xs font-semibold rounded-xl shadow-xs transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                <span>Reorden Inteligente</span>
            </a>
            @endcan
            <a href="{{ route('compras.index') }}" 
               class="inline-flex items-center space-x-1.5 px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-xl shadow-xs transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Historial de Compras</span>
            </a>
        </div>
    </div>

    <!-- Barra de Selección de Medicamento y Filtros -->
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4 shadow-xs">
        <form method="GET" action="{{ route('compras.comparador-precios') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
            <div class="lg:col-span-5">
                <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                    Seleccionar Medicamento
                </label>
                <select name="producto_id" onchange="this.form.submit()"
                        class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-xs focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="">— Elegir medicamento para comparar —</option>
                    @foreach($productos as $prod)
                    <option value="{{ $prod->id }}" {{ ($productoSeleccionado && $productoSeleccionado->id == $prod->id) ? 'selected' : '' }}>
                        {{ $prod->nombre }} ({{ $prod->laboratorio->nombre ?? 'Sin Lab' }}) — Ref: {{ formato_moneda($prod->precio_compra) }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="lg:col-span-3">
                <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                    Laboratorio
                </label>
                <select name="laboratorio_id" onchange="this.form.submit()"
                        class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-xs focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="">Todos los laboratorios</option>
                    @foreach($laboratorios as $lab)
                    <option value="{{ $lab->id }}" {{ request('laboratorio_id') == $lab->id ? 'selected' : '' }}>{{ $lab->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div class="lg:col-span-3">
                <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                    Buscar por nombre o código
                </label>
                <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Ej: Paracetamol, Amoxicilina..."
                       class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-xs focus:ring-emerald-500 focus:border-emerald-500">
            </div>

            <div class="lg:col-span-1 flex items-end gap-1.5">
                <button type="submit" class="w-full py-2 bg-slate-900 dark:bg-slate-700 hover:bg-slate-800 text-white rounded-xl text-xs font-semibold transition text-center">
                    Filtrar
                </button>
            </div>
        </form>

        @if(request()->hasAny(['producto_id', 'laboratorio_id', 'categoria_id', 'buscar']))
        <div class="mt-2.5 pt-2.5 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
            <span class="text-slate-500 dark:text-slate-400 text-[11px]">Filtros activos aplicados</span>
            <a href="{{ route('compras.comparador-precios') }}" class="text-emerald-600 dark:text-emerald-400 hover:underline text-[11px] font-medium">
                Limpiar filtros
            </a>
        </div>
        @endif
    </div>

    @if($productoSeleccionado)
    <!-- Banner de Medicamento Seleccionado -->
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center space-x-3.5">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 border border-emerald-200 dark:border-emerald-800">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
            </div>
            <div>
                <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <span>{{ $productoSeleccionado->nombre }}</span>
                    @if($productoSeleccionado->codigo_barra)
                    <span class="font-mono text-xs px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700">{{ $productoSeleccionado->codigo_barra }}</span>
                    @endif
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Lab: <strong class="text-slate-700 dark:text-slate-300">{{ $productoSeleccionado->laboratorio->nombre ?? 'Sin Laboratorio' }}</strong> &bull;
                    Categoría: <span class="text-slate-700 dark:text-slate-300">{{ $productoSeleccionado->categoria->nombre ?? 'General' }}</span> &bull;
                    Costo Ref. Catálogo: <span class="font-mono font-bold text-slate-800 dark:text-slate-200">{{ formato_moneda($productoSeleccionado->precio_compra) }}</span>
                </p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            @can('registrar compras')
            @if($proveedorRecomendado)
            <a href="{{ route('compras.create') }}?producto_id={{ $productoSeleccionado->id }}&proveedor_id={{ $proveedorRecomendado->id }}&precio_unitario={{ $mejorPrecio ?? $productoSeleccionado->precio_compra }}"
               class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-xs transition inline-flex items-center space-x-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Comprar con Proveedor Sugerido</span>
            </a>
            @endif
            <button type="button" 
                    @click="abrirModalCotizacion('{{ $productoSeleccionado->id }}', '{{ addslashes($productoSeleccionado->nombre) }}', @js($productoSeleccionado->presentacionesActivas))"
                    class="px-3 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-xl transition inline-flex items-center space-x-1">
                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                <span>+ Cotización</span>
            </button>
            @endcan
        </div>
    </div>

    <!-- 3 KPI Cards Unificados -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- KPI 1: Mejor Precio -->
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Mejor Precio por Unidad Base</p>
                <p class="text-xl font-bold text-emerald-600 dark:text-emerald-400 mt-0.5 font-mono">
                    {{ $mejorPrecio ? 'C$ ' . number_format($mejorPrecio, 4) : '—' }}
                </p>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 truncate max-w-[200px]">
                    {{ $proveedorRecomendado ? 'Proveedor: ' . $proveedorRecomendado->nombre : 'Sin cotizaciones' }}
                </p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <!-- KPI 2: Última Compra Registrada -->
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Último Costo Registrado</p>
                <p class="text-xl font-bold text-slate-900 dark:text-white mt-0.5 font-mono">
                    {{ $ultimoPrecioRegistrado ? 'C$ ' . number_format($ultimoPrecioRegistrado->precio_unitario_base, 4) : '—' }}
                </p>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                    {{ $ultimoPrecioRegistrado ? 'Fecha: ' . $ultimoPrecioRegistrado->fecha->format('d/m/Y') : 'Sin compras registradas' }}
                </p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <!-- KPI 3: Ahorro Máximo Detectado -->
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Ahorro Máx. Potencial / Unidad</p>
                <p class="text-xl font-bold text-slate-900 dark:text-white mt-0.5 font-mono">
                    {{ $ahorroMaximo > 0 ? 'C$ ' . number_format($ahorroMaximo, 4) : 'C$ 0.0000' }}
                </p>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                    Comparado con cotización más alta
                </p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center shrink-0">
                <span class="text-xs font-bold text-slate-700 dark:text-slate-300">C$</span>
            </div>
        </div>
    </div>

    <!-- Tabla Comparativa de Proveedores -->
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                    Ranking de Proveedores y Cotizaciones
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Proveedores que suministran o han cotizado este producto ordenados por mejor precio unitario.
                </p>
            </div>
            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                {{ $comparativa->count() }} proveedor(es)
            </span>
        </div>

        @if($comparativa->isNotEmpty())
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                        <th class="px-5 py-3">Proveedor</th>
                        <th class="px-5 py-3 text-right">Mejor Precio Base</th>
                        <th class="px-5 py-3 text-right">Último Precio Base</th>
                        <th class="px-5 py-3 text-center">Variación vs Promedio</th>
                        <th class="px-5 py-3 text-center">Operaciones</th>
                        <th class="px-5 py-3">Última Fecha</th>
                        <th class="px-5 py-3 text-center">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-700 dark:text-slate-300">
                    @foreach($comparativa as $comp)
                    @php
                        $esMejor = ($comp['mejor_precio_base'] == $mejorPrecio);
                        $deltaPromedio = $precioPromedio > 0 ? round((($comp['ultimo_precio_base'] - $precioPromedio) / $precioPromedio) * 100, 1) : 0;
                    @endphp
                    <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition {{ $esMejor ? 'bg-emerald-50/20 dark:bg-emerald-950/10' : '' }}">
                        <td class="px-5 py-3.5">
                            <div class="flex items-center space-x-2">
                                <span class="font-bold text-slate-900 dark:text-white">{{ $comp['proveedor']->nombre ?? 'N/A' }}</span>
                                @if($esMejor)
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-emerald-100 dark:bg-emerald-950/70 text-emerald-800 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700">
                                    ★ MEJOR PRECIO
                                </span>
                                @endif
                            </div>
                            <div class="text-[11px] text-slate-400 mt-0.5">
                                RUC: {{ $comp['proveedor']->ruc ?? '—' }} &bull; Tel: {{ $comp['proveedor']->telefono ?? '—' }}
                            </div>
                        </td>
                        <td class="px-5 py-3.5 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400">
                            C$ {{ number_format($comp['mejor_precio_base'], 4) }}
                        </td>
                        <td class="px-5 py-3.5 text-right font-mono font-bold text-slate-900 dark:text-white">
                            C$ {{ number_format($comp['ultimo_precio_base'], 4) }}
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            @if($deltaPromedio < 0)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                -{{ abs($deltaPromedio) }}% vs prom.
                            </span>
                            @elseif($deltaPromedio > 0)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-rose-50 dark:bg-rose-950/50 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                +{{ $deltaPromedio }}% vs prom.
                            </span>
                            @else
                            <span class="text-slate-400 text-[11px] font-mono">0.0%</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-center font-mono text-slate-600 dark:text-slate-300">
                            {{ $comp['total_operaciones'] }}
                        </td>
                        <td class="px-5 py-3.5 text-slate-500 dark:text-slate-400">
                            {{ $comp['ultimo_registro']->fecha->format('d/m/Y') }}
                        </td>
                        <td class="px-5 py-3.5 text-center whitespace-nowrap">
                            @can('registrar compras')
                            <a href="{{ route('compras.create') }}?producto_id={{ $productoSeleccionado->id }}&proveedor_id={{ $comp['proveedor']->id }}&precio_unitario={{ $comp['ultimo_precio_base'] }}" 
                               class="inline-flex items-center space-x-1 px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold transition shadow-xs">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                <span>Comprar</span>
                            </a>
                            @endcan
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="px-5 py-10 text-center text-xs text-slate-500 dark:text-slate-400">
            <svg class="w-8 h-8 mx-auto mb-2 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            No hay cotizaciones o compras registradas aún para este medicamento.
            <div class="mt-3">
                <button type="button" 
                        @click="abrirModalCotizacion('{{ $productoSeleccionado->id }}', '{{ addslashes($productoSeleccionado->nombre) }}', @js($productoSeleccionado->presentacionesActivas))"
                        class="px-3.5 py-2 bg-emerald-600 text-white text-xs font-semibold rounded-xl hover:bg-emerald-700 transition cursor-pointer">
                    + Registrar Primera Cotización
                </button>
            </div>
        </div>
        @endif
    </div>

    <!-- Historial Cronológico Reciente -->
    @if($historialDetallado->isNotEmpty())
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                Historial de Movimientos de Precio
            </h3>
            <span class="text-xs text-slate-500 dark:text-slate-400">{{ $historialDetallado->total() }} registros</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                        <th class="px-5 py-3">Tipo</th>
                        <th class="px-5 py-3">Fecha</th>
                        <th class="px-5 py-3">Proveedor</th>
                        <th class="px-5 py-3">Presentación</th>
                        <th class="px-5 py-3 text-right">Precio Pactado</th>
                        <th class="px-5 py-3 text-right">Costo Base Unitario</th>
                        <th class="px-5 py-3">Observaciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-700 dark:text-slate-300">
                    @foreach($historialDetallado as $hist)
                    <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition">
                        <td class="px-5 py-3">
                            @if($hist->tipo === 'compra')
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-emerald-100 dark:bg-emerald-950/70 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                COMPRA #{{ $hist->compra_id }}
                            </span>
                            @else
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-indigo-100 dark:bg-indigo-950/70 text-indigo-800 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                                COTIZACIÓN
                            </span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-slate-600 dark:text-slate-400 font-mono">
                            {{ $hist->fecha->format('d/m/Y') }}
                        </td>
                        <td class="px-5 py-3 font-semibold text-slate-900 dark:text-white">
                            {{ $hist->proveedor->nombre ?? 'N/A' }}
                        </td>
                        <td class="px-5 py-3 text-slate-600 dark:text-slate-300">
                            {{ $hist->tipo_presentacion }} (x{{ $hist->unidades_por_presentacion }})
                        </td>
                        <td class="px-5 py-3 text-right font-mono text-slate-600 dark:text-slate-400">
                            {{ formato_moneda($hist->precio_compra) }}
                        </td>
                        <td class="px-5 py-3 text-right font-mono font-bold text-slate-900 dark:text-white">
                            C$ {{ number_format($hist->precio_unitario_base, 4) }}
                        </td>
                        <td class="px-5 py-3 text-slate-500 dark:text-slate-400 truncate max-w-xs">
                            {{ $hist->observaciones ?? '—' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($historialDetallado->hasPages())
        <div class="p-3 border-t border-slate-200 dark:border-slate-800">
            {{ $historialDetallado->links() }}
        </div>
        @endif
    </div>
    @endif

    @else
    <!-- Vista Directorio General cuando no hay medicamento seleccionado -->
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                    Directorio de Medicamentos con Historial de Precios
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Selecciona cualquier medicamento para analizar la comparativa detallada de sus proveedores.
                </p>
            </div>
            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                {{ $productosConHistorial->total() ?? 0 }} medicamentos
            </span>
        </div>

        @if($productosConHistorial->isNotEmpty())
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                        <th class="px-5 py-3.5">Medicamento</th>
                        <th class="px-5 py-3.5">Laboratorio / Categoría</th>
                        <th class="px-5 py-3.5 text-right">Costo Catálogo</th>
                        <th class="px-5 py-3.5 text-center">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-700 dark:text-slate-300">
                    @foreach($productosConHistorial as $prod)
                    <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition">
                        <td class="px-5 py-3.5">
                            <span class="font-bold text-slate-900 dark:text-white block">{{ $prod->nombre }}</span>
                            @if($prod->codigo_barra)
                            <span class="font-mono text-[11px] text-slate-400">{{ $prod->codigo_barra }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="text-slate-700 dark:text-slate-300 font-medium">{{ $prod->laboratorio->nombre ?? 'Sin Laboratorio' }}</span>
                            <span class="text-slate-400 block text-[11px]">{{ $prod->categoria->nombre ?? 'General' }}</span>
                        </td>
                        <td class="px-5 py-3.5 text-right font-mono font-bold text-slate-900 dark:text-white">
                            {{ formato_moneda($prod->precio_compra) }}
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            <a href="{{ route('compras.comparador-precios', ['producto_id' => $prod->id]) }}" 
                               class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold transition shadow-xs">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                <span>Ver Comparativa</span>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($productosConHistorial->hasPages())
        <div class="p-3 border-t border-slate-200 dark:border-slate-800">
            {{ $productosConHistorial->links() }}
        </div>
        @endif
        @else
        <div class="px-5 py-12 text-center text-xs text-slate-500 dark:text-slate-400">
            <p>Selecciona un medicamento en la barra superior para comparar sus cotizaciones de proveedores.</p>
        </div>
        @endif
    </div>
    @endif

    <!-- Modal para Registrar Cotización Directa -->
    <div x-show="modalCotizacion" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
         @keydown.escape.window="modalCotizacion = false">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-xl max-w-lg w-full space-y-4"
             @click.away="modalCotizacion = false">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    </div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Registrar Nueva Cotización</h3>
                </div>
                <button type="button" @click="modalCotizacion = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="{{ route('compras.cotizaciones.store') }}" method="POST" class="space-y-3.5">
                @csrf
                
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Medicamento *</label>
                    <select name="producto_id" x-model="cotizacionProdId" required
                            class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-xs focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="">— Seleccionar Medicamento —</option>
                        @foreach($productos as $prod)
                        <option value="{{ $prod->id }}">{{ $prod->nombre }} ({{ $prod->laboratorio->nombre ?? 'Sin Lab' }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Proveedor Distribuidor *</label>
                    <select name="proveedor_id" required
                            class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-xs focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="">— Seleccionar Proveedor —</option>
                        @foreach($proveedores as $prov)
                        <option value="{{ $prov->id }}">{{ $prov->nombre }} (RUC: {{ $prov->ruc ?? '—' }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Presentación / Empaque</label>
                        <select name="presentacion_id" x-model="cotizacionPresId" @change="actualizarUnidades()"
                                class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-xs focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="">Unidad Base (x1)</option>
                            <template x-for="pres in cotizacionPresentaciones" :key="pres.id">
                                <option :value="pres.id" x-text="`${pres.nombre} (x${pres.unidades_por_presentacion})`"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Fecha Cotización *</label>
                        <input type="date" name="fecha" value="{{ date('Y-m-d') }}" required
                               class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-xs focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Precio Cotizado por el Proveedor (C$) *</label>
                    <input type="number" step="0.0001" min="0.0001" name="precio_compra" x-model="cotizacionPrecio" required placeholder="0.00"
                           class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-xs focus:ring-emerald-500 focus:border-emerald-500">
                    
                    <!-- Previsualización en vivo del costo unitario base -->
                    <div class="mt-2 p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 flex items-center justify-between text-xs">
                        <span class="text-slate-500 dark:text-slate-400">Costo Unitario Base Calculado:</span>
                        <span class="font-mono font-bold text-emerald-600 dark:text-emerald-400">C$ <span x-text="precioUnitarioCalculado"></span></span>
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Observaciones o Condiciones</label>
                    <textarea name="observaciones" rows="2" placeholder="Ej: Válido por 15 días, incluye flete..."
                              class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-xs focus:ring-emerald-500 focus:border-emerald-500"></textarea>
                </div>

                <div class="flex items-center justify-end space-x-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="modalCotizacion = false" 
                            class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer">
                        Cancelar
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 rounded-xl text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs transition cursor-pointer">
                        Guardar Cotización
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
