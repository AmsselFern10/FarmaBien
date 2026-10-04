@extends('layouts.app')

@section('title', 'Detalle de Movimiento Controlado #' . str_pad($registro->id, 5, '0', STR_PAD_LEFT) . ' - FarmaBien')

@section('content')
@php
    $recetaAsociada = $registro->venta?->recetas?->first() 
        ?? $registro->venta?->detalles?->firstWhere('receta_detalle_id', '!=', null)?->recetaDetalle?->receta;
@endphp

<div class="max-w-6xl mx-auto space-y-6" x-data="{ modalUpload: false }">

    <!-- Breadcrumbs -->
    <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 transition">Inicio</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('controlados.index') }}" class="hover:text-emerald-600 transition">Bitácora MINSA</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="font-semibold text-slate-800 dark:text-slate-200">Folio #{{ str_pad($registro->id, 5, '0', STR_PAD_LEFT) }}</span>
    </nav>

    <!-- Header & Action Toolbar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-1 border-b border-slate-300/80 dark:border-slate-800">
        <div>
            <div class="flex items-center gap-3 flex-wrap">
                <h1 class="text-xl font-bold text-slate-900 dark:text-white">
                    Folio MINSA #{{ str_pad($registro->id, 5, '0', STR_PAD_LEFT) }}
                </h1>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $registro->badge_class }}">
                    {{ $registro->tipo_etiqueta }}
                </span>
                @if($registro->esEntrada())
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-900 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800">
                        Efecto: Entrada / Reingreso (+)
                    </span>
                @elseif($registro->esMerma())
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 dark:bg-rose-950/60 text-rose-900 dark:text-rose-300 border border-rose-300 dark:border-rose-800">
                        Efecto: Baja / Merma (-)
                    </span>
                @else
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-300 border border-slate-300 dark:border-slate-700">
                        Efecto: Salida por Venta (-)
                    </span>
                @endif
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Asentado el {{ $registro->created_at->format('d/m/Y H:i') }} hrs por <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $registro->despachador->name ?? 'Sistema' }}</span>
            </p>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center gap-2 shrink-0 flex-wrap">
            <!-- 1. Botón Volver / Precedente (Extrema Izquierda) -->
            <a href="{{ route('controlados.index') }}"
               class="inline-flex items-center space-x-1.5 px-3.5 py-2 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold transition shrink-0 shadow-2xs">
                <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Bitácora MINSA</span>
            </a>

            <!-- 2. Botón Modo Full Screen -->
            <button type="button" 
                    @click="$dispatch('toggle-pos-fullscreen')"
                    title="Modo Pantalla Completa / Ocultar Barras"
                    class="inline-flex items-center space-x-1.5 px-3 py-2 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold shadow-2xs transition cursor-pointer">
                <svg class="w-4 h-4 text-emerald-900 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                <span x-text="posFullscreen ? 'Salir Full' : 'Modo Full'">Modo Full</span>
            </button>

            <!-- 3. Venta Vinculada -->
            @if($registro->venta_id)
            <a href="{{ route('ventas.show', $registro->venta_id) }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 hover:bg-indigo-100 dark:hover:bg-indigo-900/60 text-indigo-950 dark:text-indigo-200 text-xs font-semibold border border-indigo-200 dark:border-indigo-800 transition shadow-2xs">
                <svg class="w-4 h-4 text-indigo-900 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                <span>Ver Venta</span>
            </a>
            @endif

            <!-- 4. Devolución Vinculada -->
            @if($registro->devolucion)
            <a href="{{ route('devoluciones.show', $registro->devolucion) }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-red-50 dark:bg-red-950/60 hover:bg-red-100 dark:hover:bg-red-900/60 text-red-950 dark:text-red-200 text-xs font-semibold border border-red-200 dark:border-red-800 transition shadow-2xs">
                <svg class="w-4 h-4 text-red-900 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H4m0 0l3-3m-3 3l3 3m5 4v1a3 3 0 003 3h4"/></svg>
                <span>Ver Devolución</span>
            </a>
            @endif

            <!-- 5. Compra Vinculada -->
            @if($registro->compra)
            <a href="{{ route('compras.show', $registro->compra) }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-teal-50 dark:bg-teal-950/60 hover:bg-teal-100 dark:hover:bg-teal-900/60 text-teal-950 dark:text-teal-200 text-xs font-semibold border border-teal-200 dark:border-teal-800 transition shadow-2xs">
                <svg class="w-4 h-4 text-teal-900 dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <span>Ver Compra</span>
            </a>
            @endif

            <!-- 6. Receta Médica Asociada -->
            @if($recetaAsociada)
            <a href="{{ route('recetas.show', $recetaAsociada) }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 hover:bg-indigo-100 dark:hover:bg-indigo-900/60 text-indigo-950 dark:text-indigo-200 text-xs font-semibold border border-indigo-200 dark:border-indigo-800 transition shadow-2xs">
                <svg class="w-4 h-4 text-indigo-900 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Ver Receta</span>
            </a>
            @endif

            <!-- 7. Acción Primaria: Ver/Subir Evidencia -->
            @if($registro->tieneRecetaAdjunta())
            <a href="{{ route('controlados.evidencia', $registro) }}" target="_blank"
               class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-bold rounded-xl shadow-xs transition cursor-pointer">
                <svg class="w-4 h-4 text-emerald-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                <span>Ver Evidencia / Justificante</span>
            </a>
            @else
            <button type="button" @click="modalUpload = true"
                    class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-bold rounded-xl shadow-xs transition cursor-pointer">
                <svg class="w-4 h-4 text-emerald-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                <span>Adjuntar Evidencia</span>
            </button>
            @endif
        </div>
    </div>

    <!-- Quick Metric Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm">
            <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Folio Bitácora</p>
            <p class="text-base font-mono font-bold text-slate-900 dark:text-white mt-1">#{{ str_pad($registro->id, 5, '0', STR_PAD_LEFT) }}</p>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm">
            <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Cantidad / Balance</p>
            <p class="text-base font-extrabold mt-1 {{ $registro->esEntrada() ? 'text-emerald-700 dark:text-emerald-400' : ($registro->esMerma() ? 'text-rose-700 dark:text-rose-400' : 'text-slate-900 dark:text-white') }}">
                {{ $registro->signo }}{{ number_format($registro->cantidad, 0) }} {{ $registro->unidad ?: 'unidades' }}
            </p>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm">
            <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Tipo Operación</p>
            <p class="text-xs font-bold text-slate-900 dark:text-white mt-1">
                {{ $registro->tipo_etiqueta }}
            </p>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm">
            <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Fecha / Hora</p>
            <p class="text-xs font-bold text-slate-900 dark:text-white mt-1 font-mono">
                {{ $registro->created_at->format('d/m/Y H:i') }} hrs
            </p>
        </div>
    </div>

    <!-- Main Detail Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Columna Izquierda: Fármaco, Paciente, Médico / Justificación (7 cols) -->
        <div class="lg:col-span-7 space-y-6">

            <!-- Card: Fármaco y Lote -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200 dark:border-slate-800 shadow-xs space-y-4">
                <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider flex items-center gap-2 border-b border-slate-100 dark:border-slate-800 pb-2">
                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                    <span>Medicamento Regulado & Trazabilidad de Lote</span>
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <span class="text-[11px] text-slate-400 block font-medium">Nombre Comercial:</span>
                        <span class="font-bold text-slate-900 dark:text-white text-sm block mt-0.5">
                            {{ $registro->producto->nombre ?? 'Medicamento no disponible' }}
                        </span>
                    </div>

                    <div>
                        <span class="text-[11px] text-slate-400 block font-medium">Principio Activo / Concentración:</span>
                        <span class="font-semibold text-slate-800 dark:text-slate-200 block mt-0.5">
                            {{ $registro->producto?->principio_activo ?: 'Fórmula general' }}
                            @if($registro->producto?->concentracion)
                                ({{ $registro->producto->concentracion }})
                            @endif
                        </span>
                    </div>

                    <div>
                        <span class="text-[11px] text-slate-400 block font-medium">Lote Involucrado:</span>
                        <span class="font-mono font-bold text-purple-700 dark:text-purple-300 block mt-0.5">
                            {{ $registro->lote?->numero_lote ?? 'Lote general de stock' }}
                        </span>
                    </div>

                    <div>
                        <span class="text-[11px] text-slate-400 block font-medium">Fecha de Vencimiento del Lote:</span>
                        <span class="font-medium text-slate-800 dark:text-slate-200 block mt-0.5">
                            {{ $registro->lote?->fecha_vencimiento ? $registro->lote->fecha_vencimiento->format('d/m/Y') : 'N/A' }}
                        </span>
                    </div>

                    <div>
                        <span class="text-[11px] text-slate-400 block font-medium">Laboratorio Fabricante:</span>
                        <span class="font-medium text-slate-800 dark:text-slate-200 block mt-0.5">
                            {{ $registro->producto?->laboratorio?->nombre ?: 'Sin laboratorio registrado' }}
                        </span>
                    </div>

                    <div>
                        <span class="text-[11px] text-slate-400 block font-medium">Cantidad del Movimiento:</span>
                        <span class="font-extrabold text-sm block mt-0.5 {{ $registro->esEntrada() ? 'text-emerald-700 dark:text-emerald-400' : ($registro->esMerma() ? 'text-rose-700 dark:text-rose-400' : 'text-slate-900 dark:text-white') }}">
                            {{ $registro->signo }}{{ number_format($registro->cantidad, 0) }} {{ $registro->unidad ?: 'unidades base' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Card: Paciente & Médico / Justificación -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Paciente / Beneficiario -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200 dark:border-slate-800 shadow-xs space-y-3">
                    <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider flex items-center gap-2 border-b border-slate-100 dark:border-slate-800 pb-2">
                        <svg class="w-4 h-4 text-emerald-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <span>Receptor / Entidad</span>
                    </h3>
                    <div class="space-y-2 text-xs">
                        <div>
                            <span class="text-[11px] text-slate-400 block">Nombre / Titular:</span>
                            <span class="font-bold text-slate-900 dark:text-white">{{ $registro->paciente_nombre ?: 'Público General' }}</span>
                        </div>
                        @if($registro->paciente_cedula)
                        <div>
                            <span class="text-[11px] text-slate-400 block">Cédula / Documento / RUC:</span>
                            <span class="font-mono text-slate-800 dark:text-slate-200 font-semibold">{{ $registro->paciente_cedula }}</span>
                        </div>
                        @endif
                        @if($registro->paciente_edad)
                        <div>
                            <span class="text-[11px] text-slate-400 block">Edad del Paciente:</span>
                            <span class="text-slate-800 dark:text-slate-200 font-semibold">{{ $registro->paciente_edad }} años</span>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Médico Prescriptor / Justificación Oficial -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200 dark:border-slate-800 shadow-xs space-y-3">
                    <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider flex items-center gap-2 border-b border-slate-100 dark:border-slate-800 pb-2">
                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Prescriptor / Motivo Oficial</span>
                    </h3>
                    <div class="space-y-2 text-xs">
                        @if($registro->medico_nombre)
                            <div>
                                <span class="text-[11px] text-slate-400 block">Médico Prescriptor:</span>
                                <span class="font-bold text-slate-900 dark:text-white">Dr(a). {{ Str::title(mb_strtolower($registro->medico_nombre)) }}</span>
                            </div>
                            @if($registro->medico_num_registro)
                            <div>
                                <span class="text-[11px] text-slate-400 block">N° Registro / CMP:</span>
                                <span class="font-mono text-purple-700 dark:text-purple-300 font-bold">{{ $registro->medico_num_registro }}</span>
                            </div>
                            @endif
                            @if($registro->diagnostico)
                            <div>
                                <span class="text-[11px] text-slate-400 block">Diagnóstico Asentado:</span>
                                <span class="text-slate-800 dark:text-slate-200">{{ $registro->diagnostico }}</span>
                            </div>
                            @endif
                        @elseif($registro->motivo_omision)
                            <div>
                                <span class="text-[11px] text-amber-900 dark:text-amber-400 font-bold block">Justificación Oficial Asentada:</span>
                                <p class="text-slate-800 dark:text-slate-200 italic mt-0.5 bg-amber-50 dark:bg-amber-950/40 p-2.5 rounded-xl border border-amber-200 dark:border-amber-800/60">
                                    {{ $registro->motivo_omision }}
                                </p>
                            </div>
                        @else
                            <p class="text-slate-400 italic">Movimiento rutinario de farmacia</p>
                        @endif
                    </div>
                </div>
            </div>

        </div>

        <!-- Columna Derecha: Auditoría Operativa & Evidencia Digital (5 cols) -->
        <div class="lg:col-span-5 space-y-6">

            <!-- Card: Trazabilidad Operativa -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200 dark:border-slate-800 shadow-xs space-y-3">
                <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider flex items-center gap-2 border-b border-slate-100 dark:border-slate-800 pb-2">
                    <svg class="w-4 h-4 text-indigo-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Auditoría & Vínculos del Sistema</span>
                </h3>

                <div class="space-y-3 text-xs">
                    @if($registro->venta_id)
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Ticket de Venta:</span>
                        <a href="{{ route('ventas.show', $registro->venta_id) }}" class="font-mono font-bold text-indigo-900 dark:text-indigo-400 hover:underline">
                            #{{ str_pad($registro->venta_id, 5, '0', STR_PAD_LEFT) }}
                        </a>
                    </div>
                    @endif

                    @if($recetaAsociada)
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Receta Médica Vinculada:</span>
                        <a href="{{ route('recetas.show', $recetaAsociada) }}" class="inline-flex items-center gap-1 font-mono font-bold text-purple-700 dark:text-purple-300 hover:underline" title="Ver Expediente Clínico de la Receta">
                            <span>#{{ $recetaAsociada->numero_receta }}</span>
                            <svg class="w-3.5 h-3.5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </a>
                    </div>
                    @endif

                    @if($registro->devolucion)
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Comprobante Devolución:</span>
                        <a href="{{ route('devoluciones.show', $registro->devolucion) }}" class="font-mono font-bold text-red-900 hover:underline">
                            {{ $registro->devolucion->numero_devolucion }}
                        </a>
                    </div>
                    @endif

                    @if($registro->compra)
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Comprobante Compra:</span>
                        <a href="{{ route('compras.show', $registro->compra) }}" class="font-mono font-bold text-teal-900 hover:underline">
                            #{{ $registro->compra->numero_comprobante }}
                        </a>
                    </div>
                    @endif

                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Usuario Responsable:</span>
                        <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $registro->despachador->name ?? 'Sistema' }}</span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Fecha y Hora de Asentamiento:</span>
                        <span class="font-mono text-slate-800 dark:text-slate-200">{{ $registro->created_at->format('d/m/Y H:i:s') }}</span>
                    </div>
                </div>
            </div>

            <!-- Card: Evidencia Digital Adjunta -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200 dark:border-slate-800 shadow-xs space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-2">
                    <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>Evidencia Digital / Justificante</span>
                    </h3>
                    @if(!$registro->tieneRecetaAdjunta())
                    <button type="button" @click="modalUpload = true" class="text-[10px] font-bold text-emerald-900 hover:underline cursor-pointer">
                        + Subir Archivo
                    </button>
                    @endif
                </div>

                @if($registro->tieneRecetaAdjunta())
                    <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/40 p-4 text-center space-y-3">
                        <div class="w-12 h-12 mx-auto rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-900 dark:text-emerald-300 flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-800 dark:text-slate-200">Documento Digitalizado Oficial</p>
                            <p class="text-[11px] text-slate-400 mt-0.5">Almacenado de forma segura en disco privado</p>
                        </div>
                        <div class="pt-1 flex items-center justify-center gap-2">
                            <a href="{{ route('controlados.evidencia', $registro) }}" target="_blank"
                                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-xs">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <span>Ver Documento</span>
                            </a>
                            <button type="button" @click="modalUpload = true"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold transition cursor-pointer">
                                <span>Reemplazar</span>
                            </button>
                        </div>
                    </div>
                @else
                    <div class="rounded-xl border border-dashed border-slate-300 dark:border-slate-700 p-6 text-center space-y-2">
                        <div class="w-10 h-10 mx-auto rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        </div>
                        <p class="text-xs font-semibold text-slate-700 dark:text-slate-300">Sin archivo adjunto</p>
                        <p class="text-[11px] text-slate-400">Puedes adjuntar el escaneado o fotografía de la receta médica física o acta para auditoría.</p>
                        <button type="button" @click="modalUpload = true"
                                class="mt-2 inline-flex items-center gap-1 px-3.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-xs cursor-pointer">
                            <span>Adjuntar Archivo</span>
                        </button>
                    </div>
                @endif
            </div>

        </div>

    </div>

    <!-- Modal para Subir / Reemplazar Evidencia -->
    <template x-teleport="body">
        <div x-show="modalUpload" x-cloak
             class="fixed inset-0 z-[9999] flex items-center justify-center p-3 sm:p-4 bg-slate-950/75 backdrop-blur-sm overflow-y-auto"
             @keydown.escape.window="modalUpload = false"
             @click.self="modalUpload = false">
            <div @click.stop
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full p-5 border border-slate-200 dark:border-slate-800 shadow-2xl space-y-4 my-auto">
                <div class="flex items-center justify-between pb-2 border-b border-slate-200 dark:border-slate-800">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-900 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        <span>Adjuntar Evidencia o Justificante MINSA</span>
                    </h3>
                    <button type="button" @click="modalUpload = false" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
                </div>
                <form action="{{ route('controlados.evidencia.store', $registro) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Selecciona el archivo digital o fotografía (PDF, JPG, PNG, WEBP):
                        </label>
                        <input type="file" name="foto_receta" accept=".pdf,.jpg,.jpeg,.png,.webp,image/*,application/pdf" required
                               class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-emerald-600 file:text-white hover:file:bg-emerald-700 cursor-pointer">
                        <p class="text-[10px] text-slate-400 mt-1">Tamaño máximo: 5 MB. Almacenado de forma segura.</p>
                    </div>
                    <div class="flex justify-end gap-2 pt-2 border-t border-slate-200 dark:border-slate-800">
                        <button type="button" @click="modalUpload = false" class="px-3.5 py-1.5 rounded-xl border border-slate-300 dark:border-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-100 transition">Cancelar</button>
                        <button type="submit" class="px-4 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-xs">Subir y Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

</div>
@endsection
