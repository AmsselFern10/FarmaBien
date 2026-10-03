@extends('layouts.app')

@section('title', "Ficha de Precio — {$producto->nombre} — FarmaBien")

@section('content')
<div class="space-y-4">

    {{-- Fila 1 — Breadcrumb --}}
    <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('precios.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Precios de Venta</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-800 dark:text-slate-200 font-semibold truncate">{{ $producto->nombre }}</span>
    </nav>

    {{-- Fila 2 — Título y Acciones --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">{{ $producto->nombre }}</h1>
            <div class="flex flex-wrap items-center gap-2 text-xs text-slate-500 dark:text-slate-400 mt-1">
                <span>{{ $producto->principio_activo ?? 'Sin principio activo' }}</span>
                <span>·</span>
                <span class="font-medium text-slate-700 dark:text-slate-300">{{ $producto->categoria->nombre ?? 'General' }}</span>
                <span>·</span>
                <span>{{ $producto->laboratorio->nombre ?? 'Sin Laboratorio' }}</span>
                @if($producto->codigo_barra)
                <span>·</span>
                <span class="font-mono text-slate-500">EAN: {{ $producto->codigo_barra }}</span>
                @endif
            </div>
        </div>

        {{-- Barra de Acciones --}}
        <div class="flex items-center gap-2 flex-wrap shrink-0">
            {{-- Botón de Navegación --}}
            <a href="{{ route('precios.index') }}"
               class="inline-flex items-center space-x-1.5 px-3.5 py-1.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold shadow-2xs transition">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Precios de Venta</span>
            </a>

            {{-- Modo Full --}}
            <button type="button" 
                    @click="$dispatch('toggle-pos-fullscreen')"
                    title="Modo Pantalla Completa"
                    class="inline-flex items-center space-x-1.5 px-3.5 py-1.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold shadow-2xs transition cursor-pointer">
                <svg class="w-4 h-4 text-emerald-900 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                <span x-text="posFullscreen ? 'Salir Full' : 'Modo Full'">Modo Full</span>
            </button>

            {{-- Secundario Pastel Azul: Ver Ficha del Medicamento --}}
            <a href="{{ route('productos.show', $producto) }}"
               class="inline-flex items-center space-x-1.5 px-3.5 py-1.5 rounded-xl bg-sky-50 dark:bg-sky-950/60 border border-sky-200 dark:border-sky-800/60 text-sky-950 dark:text-sky-300 hover:bg-sky-100 dark:hover:bg-sky-900/60 text-xs font-semibold shadow-2xs transition">
                <svg class="w-4 h-4 text-sky-600 dark:text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Ver Ficha Medicamento</span>
            </a>

            {{-- Principal Verde Sólido: Editar Precio --}}
            <a href="{{ route('precios.edit', $producto) }}"
               class="inline-flex items-center space-x-1.5 px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-semibold rounded-xl shadow-xs transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Editar Precio</span>
            </a>
        </div>
    </div>

    {{-- Cards de Precios y Rentabilidad --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Card 1: Precio Venta Base --}}
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Precio Venta Base</p>
                <p class="text-xl font-bold text-emerald-950 dark:text-emerald-400 font-mono mt-0.5">C$ {{ number_format($precioBase, 2) }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-900 dark:text-emerald-400 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        {{-- Card 2: Costo de Adquisición --}}
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Costo Adquisición</p>
                <p class="text-xl font-bold text-slate-900 dark:text-white font-mono mt-0.5">C$ {{ number_format($costoReferencia, 2) }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
        </div>

        {{-- Card 3: Margen Estimado --}}
        @php
            if ($margenEstimado < 0) {
                $margenClass = 'text-red-950 dark:text-red-400';
            } elseif ($margenEstimado < 25) {
                $margenClass = 'text-amber-950 dark:text-amber-400';
            } else {
                $margenClass = 'text-emerald-950 dark:text-emerald-400';
            }
        @endphp
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Margen Estimado</p>
                <p class="text-xl font-bold {{ $margenClass }} mt-0.5">{{ number_format($margenEstimado, 1) }}%</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-900 dark:text-blue-400 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            </div>
        </div>

        {{-- Card 4: Promoción Vigente --}}
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Promoción Vigente</p>
                @if($promocionVigente)
                <p class="text-sm font-bold text-rose-950 dark:text-rose-400 truncate max-w-[150px] mt-0.5">{{ $promocionVigente->nombre }}</p>
                @else
                <p class="text-sm font-medium text-slate-400 mt-0.5">Sin promo activa</p>
                @endif
            </div>
            <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
            </div>
        </div>
    </div>

    {{-- Tabla de Presentaciones y Factores --}}
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="px-4 py-3 bg-slate-100/80 dark:bg-slate-800/80 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200">
                Presentaciones Comerciales & Factores
            </h2>
            <span class="text-[11px] text-slate-500">{{ $producto->presentaciones->count() }} presentación(es) configurada(s)</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-800/50 text-[11px] text-slate-500 font-semibold border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="px-4 py-2.5">Presentación</th>
                        <th class="px-3 py-2.5 text-center">Factor (Unidades)</th>
                        <th class="px-3 py-2.5 text-right">Costo Estimado</th>
                        <th class="px-3 py-2.5 text-right">Precio de Venta</th>
                        <th class="px-3 py-2.5 text-center">Margen</th>
                        <th class="px-3 py-2.5">Código de Barras</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200/70 dark:divide-slate-800/70">
                    {{-- Fila Base --}}
                    <tr class="bg-emerald-50/20 dark:bg-emerald-500/5">
                        <td class="px-4 py-2.5 font-bold text-slate-900 dark:text-white">
                            Unidad Base (Pastilla / Unidad)
                            <span class="ml-1.5 px-1.5 py-0.5 rounded text-[10px] bg-emerald-100 dark:bg-emerald-800/40 text-emerald-900 dark:text-emerald-300 font-semibold">Base</span>
                        </td>
                        <td class="px-3 py-2.5 text-center font-mono font-medium">1 u.</td>
                        <td class="px-3 py-2.5 text-right font-mono text-slate-700 dark:text-slate-400">C$ {{ number_format($costoReferencia, 2) }}</td>
                        <td class="px-3 py-2.5 text-right font-mono font-bold text-emerald-900 dark:text-emerald-400">C$ {{ number_format($precioBase, 2) }}</td>
                        <td class="px-3 py-2.5 text-center">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-bold {{ $margenClass }}">
                                {{ number_format($margenEstimado, 1) }}%
                            </span>
                        </td>
                        <td class="px-3 py-2.5 font-mono text-slate-500">{{ $producto->codigo_barra ?? '—' }}</td>
                    </tr>

                    @foreach($producto->presentaciones as $pres)
                    @php
                        $factor = max(1, (int)$pres->unidades_por_presentacion);
                        $costoPres = $costoReferencia * $factor;
                        $precioPres = (float)($pres->precio_venta ?? 0);
                        $margenPres = $precioPres > 0 ? (($precioPres - $costoPres) / $precioPres) * 100 : 0;
                        
                        if ($margenPres < 0) {
                            $mClass = 'text-red-900 dark:text-red-400 bg-red-50 dark:bg-red-500/10 border border-red-200';
                        } elseif ($margenPres < 25) {
                            $mClass = 'text-amber-900 dark:text-amber-400 bg-amber-50 dark:bg-amber-500/10 border border-amber-200';
                        } else {
                            $mClass = 'text-emerald-900 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200';
                        }
                    @endphp
                    <tr>
                        <td class="px-4 py-2.5 font-medium text-slate-900 dark:text-white">
                            {{ $pres->nombre }}
                            @if($pres->descripcion)
                            <span class="text-[11px] text-slate-500 ml-1">({{ $pres->descripcion }})</span>
                            @endif
                        </td>
                        <td class="px-3 py-2.5 text-center font-mono font-medium">{{ $factor }} u.</td>
                        <td class="px-3 py-2.5 text-right font-mono text-slate-700 dark:text-slate-400">C$ {{ number_format($costoPres, 2) }}</td>
                        <td class="px-3 py-2.5 text-right font-mono font-bold text-emerald-900 dark:text-emerald-400">C$ {{ number_format($precioPres, 2) }}</td>
                        <td class="px-3 py-2.5 text-center">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-bold {{ $mClass }}">
                                {{ number_format($margenPres, 1) }}%
                            </span>
                        </td>
                        <td class="px-3 py-2.5 font-mono text-slate-500">{{ $pres->codigo_barras ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Tabla de Historial de Cambios de Precio (Auditoría) --}}
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="px-4 py-3 bg-slate-100/80 dark:bg-slate-800/80 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200">
                Historial de Cambios & Auditoría de Precios
            </h2>
            <span class="text-[11px] text-slate-500">Orden cronológico descendente</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-800/50 text-[11px] text-slate-500 font-semibold border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="px-4 py-2.5">Fecha y Hora</th>
                        <th class="px-3 py-2.5">Usuario</th>
                        <th class="px-3 py-2.5">Ámbito / Presentación</th>
                        <th class="px-3 py-2.5 text-right">Precio Registrado</th>
                        <th class="px-3 py-2.5 text-center">Estado Vigencia</th>
                        <th class="px-4 py-2.5">Motivo / Justificación</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200/70 dark:divide-slate-800/70">
                    @forelse($historial as $h)
                    <tr>
                        <td class="px-4 py-2.5 font-mono text-slate-700 dark:text-slate-400">
                            {{ $h->vigente_desde ? $h->vigente_desde->format('d/m/Y H:i') : $h->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-3 py-2.5 font-medium text-slate-800 dark:text-slate-200">
                            {{ $h->usuario->name ?? 'Sistema' }}
                        </td>
                        <td class="px-3 py-2.5">
                            @if($h->presentacion)
                            <span class="px-2 py-0.5 rounded-md bg-indigo-50 dark:bg-indigo-500/10 text-indigo-900 dark:text-indigo-300 border border-indigo-200 text-[11px] font-medium">
                                {{ $h->presentacion->nombre }} (x{{ $h->presentacion->unidades_por_presentacion }})
                            </span>
                            @else
                            <span class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-[11px] font-medium">
                                Precio Base (Unidad)
                            </span>
                            @endif
                        </td>
                        <td class="px-3 py-2.5 text-right font-mono font-bold text-slate-900 dark:text-white">
                            C$ {{ number_format($h->precio, 2) }}
                        </td>
                        <td class="px-3 py-2.5 text-center">
                            @if(is_null($h->vigente_hasta))
                            <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-900 dark:text-emerald-400 border border-emerald-200">
                                Vigente
                            </span>
                            @else
                            <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-500">
                                Histórico (Hasta {{ $h->vigente_hasta->format('d/m/Y') }})
                            </span>
                            @endif
                        </td>
                        <td class="px-4 py-2.5 text-slate-700 dark:text-slate-400">
                            {{ $h->motivo ?? 'Sin motivo registrado' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-slate-500">
                            No se han registrado modificaciones de precio para este medicamento.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
