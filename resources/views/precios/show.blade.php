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
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-1">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white">{{ $producto->nombre }}</h1>
            <div class="flex flex-wrap items-center gap-2 text-xs text-slate-600 dark:text-slate-400 mt-1">
                <span>{{ $producto->principio_activo ?? 'Sin principio activo' }}</span>
                <span>·</span>
                <span class="font-medium text-slate-800 dark:text-slate-200">{{ $producto->categoria->nombre ?? 'General' }}</span>
                <span>·</span>
                <span>{{ $producto->laboratorio->nombre ?? 'Sin Laboratorio' }}</span>
                @if($producto->codigo_barra)
                <span>·</span>
                <span class="font-mono text-slate-500">EAN: {{ $producto->codigo_barra }}</span>
                @endif
            </div>
        </div>

        {{-- Barra de Acciones: ← Precios de Venta, Modo Full, Ver Ficha (Azul pastel), Editar Precio (Verde sólido) --}}
        <div class="flex items-center space-x-2 self-start sm:self-auto flex-wrap gap-y-2">
            {{-- Botón de Navegación --}}
            <a href="{{ route('precios.index') }}"
               class="h-10 px-4 rounded-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm font-medium transition inline-flex items-center gap-2 cursor-pointer shadow-2xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Precios de Venta</span>
            </a>

            {{-- Modo Full --}}
            <button type="button" 
                    @click="$dispatch('toggle-pos-fullscreen')"
                    title="Modo Pantalla Completa"
                    class="h-10 px-4 rounded-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm font-medium transition inline-flex items-center gap-2 shrink-0 cursor-pointer shadow-2xs">
                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                <span>Modo Full</span>
            </button>

            {{-- Secundario Pastel Azul: Ver Ficha del Medicamento --}}
            <a href="{{ route('productos.show', $producto) }}"
               class="h-10 px-4 rounded-full bg-blue-50 dark:bg-blue-500/10 border border-blue-200 dark:border-blue-500/30 text-blue-900 dark:text-blue-300 hover:bg-blue-100 dark:hover:bg-blue-500/20 text-sm font-medium transition inline-flex items-center gap-2 cursor-pointer shadow-2xs">
                <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Ver Ficha del Medicamento</span>
            </a>

            {{-- Principal Verde Sólido: Editar Precio --}}
            <a href="{{ route('precios.edit', $producto) }}"
               class="h-10 px-4 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium transition inline-flex items-center gap-2 shadow-xs cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Editar Precio</span>
            </a>
        </div>
    </div>

    {{-- Cards de Precios y Rentabilidad --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
        {{-- Card 1: Precio Venta Base --}}
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xs">
            <div class="text-xs font-semibold text-slate-600 dark:text-slate-400">Precio de Venta Base</div>
            <div class="mt-2 text-2xl font-bold text-emerald-900 dark:text-emerald-400 font-mono">
                C$ {{ number_format($precioBase, 2) }}
            </div>
            <div class="mt-0.5 text-[11px] text-slate-500">Por unidad base / pastilla</div>
        </div>

        {{-- Card 2: Costo de Adquisición --}}
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xs">
            <div class="text-xs font-semibold text-slate-600 dark:text-slate-400">Costo de Adquisición</div>
            <div class="mt-2 text-2xl font-bold text-slate-900 dark:text-white font-mono">
                C$ {{ number_format($costoReferencia, 2) }}
            </div>
            <div class="mt-0.5 text-[11px] text-slate-500">Último costo de compra</div>
        </div>

        {{-- Card 3: Margen Estimado --}}
        @php
            if ($margenEstimado < 0) {
                $margenClass = 'text-red-900 dark:text-red-400';
            } elseif ($margenEstimado < 25) {
                $margenClass = 'text-amber-900 dark:text-amber-400';
            } else {
                $margenClass = 'text-emerald-900 dark:text-emerald-400';
            }
        @endphp
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xs">
            <div class="text-xs font-semibold text-slate-600 dark:text-slate-400">Margen Bruto Estimado</div>
            <div class="mt-2 text-2xl font-bold {{ $margenClass }}">
                {{ number_format($margenEstimado, 1) }}%
            </div>
            <div class="mt-0.5 text-[11px] text-slate-500">Fórmula: (Precio − Costo) / Precio</div>
        </div>

        {{-- Card 4: Promoción Vigente --}}
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xs">
            <div class="text-xs font-semibold text-slate-600 dark:text-slate-400">Promoción Vigente</div>
            @if($promocionVigente)
            <div class="mt-2 text-lg font-bold text-rose-900 dark:text-rose-400 truncate">
                {{ $promocionVigente->nombre }}
            </div>
            <div class="mt-0.5 text-[11px] text-slate-500">
                Descuento activo · {{ $promocionVigente->fecha_fin ? 'Hasta ' . \Carbon\Carbon::parse($promocionVigente->fecha_fin)->format('d/m/Y') : 'Vigencia permanente' }}
            </div>
            @else
            <div class="mt-2 text-lg font-semibold text-slate-400">
                Sin Promoción Activa
            </div>
            <div class="mt-0.5 text-[11px] text-slate-500">Se aplica precio de lista normal</div>
            @endif
        </div>
    </div>

    {{-- Tabla de Presentaciones y Factores --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-2xs overflow-hidden">
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
                        <td class="px-3 py-2.5 text-right font-mono text-slate-600 dark:text-slate-400">C$ {{ number_format($costoReferencia, 2) }}</td>
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
                        <td class="px-3 py-2.5 text-right font-mono text-slate-600 dark:text-slate-400">C$ {{ number_format($costoPres, 2) }}</td>
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
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-2xs overflow-hidden">
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
                        <td class="px-4 py-2.5 font-mono text-slate-600 dark:text-slate-400">
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
                        <td class="px-4 py-2.5 text-slate-600 dark:text-slate-400">
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
