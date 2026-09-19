@extends('layouts.app')
@section('title', 'Receta #' . $receta->numero_receta . ' - FarmaBien')
@section('content')
@php
$estadoMap = [
    'pendiente'          => ['label'=>'Pendiente',              'badge'=>'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800'],
    'dispensada_parcial' => ['label'=>'Parcialmente Dispensada','badge'=>'bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border-blue-200 dark:border-blue-800'],
    'dispensada_total'   => ['label'=>'Dispensada Total',       'badge'=>'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800'],
    'anulada'            => ['label'=>'Anulada',                'badge'=>'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800'],
];
$st      = $estadoMap[$receta->estado ?? 'pendiente'] ?? ['label'=>$receta->estado,'badge'=>'bg-slate-100 dark:bg-slate-800 text-slate-600 border-slate-200'];
$vencida = $receta->estaVencida();
@endphp

<div class="max-w-6xl mx-auto space-y-6">

    {{-- ── Header ── --}}
    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400 mb-1">
                <a href="{{ route('recetas.index') }}" class="hover:text-emerald-600 transition">Recetas Medicas</a>
                <span>/</span>
                <span class="font-medium text-slate-700 dark:text-slate-300">Receta #{{ $receta->numero_receta }}</span>
            </div>
            <div class="flex items-center gap-3 flex-wrap">
                <h1 class="text-xl font-bold text-slate-900 dark:text-white">
                    Receta #{{ $receta->numero_receta }}
                </h1>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold border {{ $st['badge'] }}">{{ $st['label'] }}</span>
                @if($vencida)
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    Vencida
                </span>
                @endif
            </div>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            @can('registrar recetas')
            <a href="{{ route('recetas.edit', $receta) }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-sm transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Editar
            </a>
            @endcan
            <a href="{{ route('recetas.index') }}"
               class="px-3.5 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-xl transition">
                &larr; Volver
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="flex items-center gap-2 px-4 py-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-xs text-emerald-700 dark:text-emerald-300">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        {{ session('success') }}
    </div>
    @endif

    {{-- ── Metric Cards ── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm">
            <p class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">N° Receta</p>
            <p class="text-sm font-mono font-bold text-slate-900 dark:text-white mt-1">{{ $receta->numero_receta }}</p>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm">
            <p class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">Tipo de Receta</p>
            <p class="text-xs font-bold text-slate-900 dark:text-white mt-1 capitalize">{{ str_replace('_',' ',$receta->tipo_receta ?? '—') }}</p>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm">
            <p class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">Medicamentos</p>
            <p class="text-base font-bold text-emerald-600 dark:text-emerald-400 mt-1">{{ $receta->detalles->count() }} items</p>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm">
            <p class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">Vence</p>
            <p class="text-xs font-bold mt-1 {{ $vencida ? 'text-rose-600 dark:text-rose-400' : 'text-slate-900 dark:text-white' }}">
                {{ $receta->fecha_vencimiento?->format('d/m/Y') ?? 'Sin limite' }}
            </p>
        </div>
    </div>

    {{-- ── Main layout: 2/3 left · 1/3 right ── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- LEFT (col-span-2): Datos + Paciente + Médico + Medicamentos --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Paciente & Médico en grid --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                {{-- Paciente --}}
                <div class="bg-white dark:bg-slate-900 rounded-xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm">
                    <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-4 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Paciente
                    </h3>
                    @if($receta->cliente)
                    <a href="{{ route('clientes.show', $receta->cliente) }}"
                       class="inline-flex items-center gap-1.5 mb-3 text-xs font-semibold text-emerald-600 dark:text-emerald-400 hover:underline">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                        {{ $receta->cliente->nombre }}
                    </a>
                    @endif
                    <dl class="space-y-2.5 text-xs">
                        <div><dt class="text-slate-400">Nombre</dt><dd class="font-semibold text-slate-800 dark:text-slate-200 mt-0.5">{{ $receta->paciente_nombre ?? '—' }}</dd></div>
                        <div class="grid grid-cols-2 gap-2">
                            <div><dt class="text-slate-400">Documento</dt><dd class="font-mono font-semibold text-slate-800 dark:text-slate-200 mt-0.5">{{ $receta->paciente_documento ?? '—' }}</dd></div>
                            <div><dt class="text-slate-400">Edad</dt><dd class="font-semibold text-slate-800 dark:text-slate-200 mt-0.5">{{ $receta->paciente_edad ? $receta->paciente_edad.' años' : '—' }}</dd></div>
                        </div>
                    </dl>
                </div>

                {{-- Médico --}}
                <div class="bg-white dark:bg-slate-900 rounded-xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm">
                    <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-4 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                        Medico Tratante
                    </h3>
                    <dl class="space-y-2.5 text-xs">
                        <div><dt class="text-slate-400">Nombre</dt><dd class="font-semibold text-slate-800 dark:text-slate-200 mt-0.5">{{ $receta->medico_nombre ?? '—' }}</dd></div>
                        <div><dt class="text-slate-400">Colegiatura</dt><dd class="font-mono font-semibold text-slate-800 dark:text-slate-200 mt-0.5">{{ $receta->medico_colegiatura ?? '—' }}</dd></div>
                        <div><dt class="text-slate-400">Especialidad</dt><dd class="font-semibold text-slate-800 dark:text-slate-200 mt-0.5">{{ $receta->medico_especialidad ?? '—' }}</dd></div>
                        <div><dt class="text-slate-400">Institucion</dt><dd class="font-semibold text-slate-800 dark:text-slate-200 mt-0.5">{{ $receta->institucion_salud ?? '—' }}</dd></div>
                    </dl>
                </div>
            </div>

            {{-- Medicamentos Prescritos --}}
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="flex items-center justify-between px-5 py-3.5 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        Medicamentos Prescritos
                    </h3>
                    <span class="text-xs font-extrabold px-2.5 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                        {{ $receta->detalles->count() }} items
                    </span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead class="bg-slate-50 dark:bg-slate-800/50">
                            <tr>
                                <th class="text-left px-5 py-2.5 font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">#</th>
                                <th class="text-left px-5 py-2.5 font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Medicamento</th>
                                <th class="text-center px-5 py-2.5 font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Cant.</th>
                                <th class="text-left px-5 py-2.5 font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Posologia / Instrucciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($receta->detalles as $i => $det)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition">
                                <td class="px-5 py-3 text-slate-400 font-mono">{{ $i+1 }}</td>
                                <td class="px-5 py-3 font-semibold text-slate-900 dark:text-slate-200">
                                    {{ $det->producto->nombre ?? $det->nombre_medicamento ?? '—' }}
                                    @if(!empty($det->producto->concentracion))
                                    <span class="ml-1 text-slate-400 font-normal">{{ $det->producto->concentracion }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-center font-bold text-slate-700 dark:text-slate-300">{{ $det->cantidad_recetada }}</td>
                                <td class="px-5 py-3 text-slate-500 dark:text-slate-400 italic">{{ $det->posologia ?? '—' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-5 py-10 text-center text-slate-400">Sin medicamentos registrados en esta receta.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Observaciones --}}
            @if($receta->observaciones || $receta->archivo_receta)
            <div class="bg-white dark:bg-slate-900 rounded-xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm">
                <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-3">Observaciones y Adjunto</h3>
                @if($receta->observaciones)
                <p class="text-sm text-slate-600 dark:text-slate-400 italic leading-relaxed">{{ $receta->observaciones }}</p>
                @endif
                @if($receta->archivo_receta)
                <a href="{{ Storage::url($receta->archivo_receta) }}" target="_blank"
                   class="inline-flex items-center gap-1.5 mt-3 text-xs font-semibold text-emerald-600 dark:text-emerald-400 hover:underline">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                    Ver Receta Adjunta
                </a>
                @endif
            </div>
            @endif

        </div>

        {{-- RIGHT (col-span-1): Info + Cambiar Estado --}}
        <div class="space-y-5">

            {{-- Info Receta --}}
            <div class="bg-white dark:bg-slate-900 rounded-xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm">
                <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-4 flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Info de la Receta
                </h3>
                <dl class="space-y-3 text-xs">
                    <div>
                        <dt class="text-slate-400 mb-0.5">Estado actual</dt>
                        <dd><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold border {{ $st['badge'] }}">{{ $st['label'] }}</span></dd>
                    </div>
                    <div>
                        <dt class="text-slate-400 mb-0.5">Emision</dt>
                        <dd class="font-semibold text-slate-800 dark:text-slate-200">{{ $receta->fecha_emision?->format('d/m/Y') ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-400 mb-0.5">Vencimiento</dt>
                        <dd class="font-semibold {{ $vencida ? 'text-rose-600 dark:text-rose-400' : 'text-slate-800 dark:text-slate-200' }}">
                            {{ $receta->fecha_vencimiento?->format('d/m/Y') ?? 'Sin limite' }}
                            @if($vencida) <span class="text-rose-400">(vencida)</span>@endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-slate-400 mb-0.5">Tipo</dt>
                        <dd class="font-semibold text-slate-800 dark:text-slate-200 capitalize">{{ str_replace('_',' ',$receta->tipo_receta ?? '—') }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-400 mb-0.5">Creada</dt>
                        <dd class="font-semibold text-slate-800 dark:text-slate-200">{{ $receta->created_at->format('d/m/Y H:i') }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Cambiar Estado --}}
            @can('registrar recetas')
            <div class="bg-white dark:bg-slate-900 rounded-xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm">
                <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Cambiar Estado
                </h3>
                @php
                $estadoOps = [
                    'pendiente'          => ['Pendiente',           'border-amber-200  dark:border-amber-800  hover:bg-amber-50  dark:hover:bg-amber-950/30  text-amber-700  dark:text-amber-300'],
                    'dispensada_parcial' => ['Dispensada Parcial',  'border-blue-200   dark:border-blue-800   hover:bg-blue-50   dark:hover:bg-blue-950/30   text-blue-700   dark:text-blue-300'],
                    'dispensada_total'   => ['Dispensada Total',    'border-emerald-200 dark:border-emerald-800 hover:bg-emerald-50 dark:hover:bg-emerald-950/30 text-emerald-700 dark:text-emerald-300'],
                    'anulada'            => ['Anular Receta',       'border-rose-200    dark:border-rose-800    hover:bg-rose-50    dark:hover:bg-rose-950/30    text-rose-700    dark:text-rose-300'],
                ];
                @endphp
                <div class="space-y-1.5">
                    @foreach($estadoOps as $val => [$label, $cls])
                    @if($receta->estado === $val)
                    <div class="flex items-center gap-2 px-3 py-2 rounded-lg bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-500 dark:text-slate-400">
                        <svg class="w-3.5 h-3.5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        <span class="font-bold text-slate-700 dark:text-slate-200">{{ $label }}</span>
                        <span class="text-[10px]">(actual)</span>
                    </div>
                    @else
                    <form method="POST" action="{{ route('recetas.estado', $receta) }}">
                        @csrf
                        <input type="hidden" name="estado" value="{{ $val }}">
                        <button type="submit"
                            class="w-full text-left flex items-center gap-2 px-3 py-2 rounded-lg border text-xs font-semibold transition bg-white dark:bg-slate-900 {{ $cls }}">
                            <svg class="w-3 h-3 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            {{ $label }}
                        </button>
                    </form>
                    @endif
                    @endforeach
                </div>
            </div>
            @endcan

        </div>
    </div>

</div>
@endsection
