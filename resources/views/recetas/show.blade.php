@extends('layouts.app')
@section('title', 'Detalle de Receta #{{ $receta->numero_receta }} - FarmaBien')
@section('content')
@php
$estadoBadge = match($receta->estado ?? 'pendiente') {
    'pendiente'          => ['bg-amber-100 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800', 'Pendiente'],
    'validada'           => ['bg-emerald-100 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800', 'Validada'],
    'dispensada'         => ['bg-indigo-100 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800', 'Dispensada'],
    'dispensada_parcial' => ['bg-cyan-100 dark:bg-cyan-950/40 text-cyan-700 dark:text-cyan-300 border-cyan-200 dark:border-cyan-800', 'Dispensada Parcial'],
    'anulada'            => ['bg-rose-100 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800', 'Anulada'],
    default              => ['bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700', $receta->estado],
};
$vencida = $receta->estaVencida();
@endphp

<div class="space-y-5">
    {{-- Breadcrumb --}}
    <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('recetas.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Recetas Medicas</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-800 dark:text-slate-200 font-semibold">Receta #{{ $receta->numero_receta }}</span>
    </nav>

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-3">
                Receta Medica
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold border {{ $estadoBadge[0] }}">{{ $estadoBadge[1] }}</span>
                @if($vencida)<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800">¡Vencida!</span>@endif
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">N° {{ $receta->numero_receta }} · Emitida el {{ $receta->fecha_emision?->format('d/m/Y') }}</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            @can('registrar recetas')
            <a href="{{ route('recetas.edit', $receta) }}" class="inline-flex items-center space-x-2 px-3.5 py-2 bg-amber-500 hover:bg-amber-600 active:bg-amber-700 text-white text-xs font-semibold rounded-xl shadow-xs transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Editar</span>
            </a>
            @endcan
            <a href="{{ route('recetas.index') }}" class="inline-flex items-center space-x-2 px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-xl transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Volver</span>
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="px-4 py-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-xs text-emerald-700 dark:text-emerald-300 flex items-center gap-2">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        {{ session('success') }}
    </div>
    @endif

    {{-- Grid principal --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- Col izquierda 2/3: datos receta + medicamentos --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Card: Info Receta --}}
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="flex items-center gap-2 px-5 py-3.5 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30">
                    <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200">Informacion de la Receta</h3>
                </div>
                <div class="p-5 grid grid-cols-2 sm:grid-cols-3 gap-4 text-xs">
                    <div><p class="font-semibold text-slate-400 uppercase tracking-wider mb-0.5">Numero</p><p class="font-bold text-slate-800 dark:text-slate-200 font-mono">{{ $receta->numero_receta }}</p></div>
                    <div><p class="font-semibold text-slate-400 uppercase tracking-wider mb-0.5">Fecha Emision</p><p class="font-semibold text-slate-700 dark:text-slate-300">{{ $receta->fecha_emision?->format('d/m/Y') }}</p></div>
                    <div><p class="font-semibold text-slate-400 uppercase tracking-wider mb-0.5">Vencimiento</p><p class="font-semibold {{ $vencida ? 'text-rose-600 dark:text-rose-400' : 'text-slate-700 dark:text-slate-300' }}">{{ $receta->fecha_vencimiento?->format('d/m/Y') ?? '—' }}</p></div>
                    <div><p class="font-semibold text-slate-400 uppercase tracking-wider mb-0.5">Tipo</p><p class="font-semibold text-slate-700 dark:text-slate-300 capitalize">{{ str_replace('_',' ',$receta->tipo_receta ?? '—') }}</p></div>
                    <div><p class="font-semibold text-slate-400 uppercase tracking-wider mb-0.5">Institucion</p><p class="font-semibold text-slate-700 dark:text-slate-300">{{ $receta->institucion_salud ?? '—' }}</p></div>
                    <div><p class="font-semibold text-slate-400 uppercase tracking-wider mb-0.5">Estado</p><span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $estadoBadge[0] }}">{{ $estadoBadge[1] }}</span></div>
                    @if($receta->observaciones)
                    <div class="col-span-2 sm:col-span-3"><p class="font-semibold text-slate-400 uppercase tracking-wider mb-0.5">Observaciones</p><p class="text-slate-600 dark:text-slate-400 italic">{{ $receta->observaciones }}</p></div>
                    @endif
                </div>
            </div>

            {{-- Card: Medico --}}
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="flex items-center gap-2 px-5 py-3.5 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30">
                    <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200">Medico Tratante</h3>
                </div>
                <div class="p-5 grid grid-cols-2 sm:grid-cols-3 gap-4 text-xs">
                    <div><p class="font-semibold text-slate-400 uppercase tracking-wider mb-0.5">Nombre</p><p class="font-bold text-slate-800 dark:text-slate-200">{{ $receta->medico_nombre ?? '—' }}</p></div>
                    <div><p class="font-semibold text-slate-400 uppercase tracking-wider mb-0.5">Colegiatura</p><p class="font-semibold text-slate-700 dark:text-slate-300 font-mono">{{ $receta->medico_colegiatura ?? '—' }}</p></div>
                    <div><p class="font-semibold text-slate-400 uppercase tracking-wider mb-0.5">Especialidad</p><p class="font-semibold text-slate-700 dark:text-slate-300">{{ $receta->medico_especialidad ?? '—' }}</p></div>
                </div>
            </div>

            {{-- Card: Medicamentos prescritos --}}
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="flex items-center justify-between px-5 py-3.5 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200">Medicamentos Prescritos</h3>
                    </div>
                    <span class="text-xs font-extrabold px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400">{{ $receta->detalles->count() }}</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead class="bg-slate-50 dark:bg-slate-800/50">
                            <tr>
                                <th class="text-left px-4 py-2.5 font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">#</th>
                                <th class="text-left px-4 py-2.5 font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Medicamento</th>
                                <th class="text-right px-4 py-2.5 font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Cantidad</th>
                                <th class="text-left px-4 py-2.5 font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Posologia / Instrucciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($receta->detalles as $i => $det)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition">
                                <td class="px-4 py-2.5 text-slate-400 font-mono">{{ $i+1 }}</td>
                                <td class="px-4 py-2.5 font-semibold text-slate-800 dark:text-slate-200">{{ $det->producto->nombre ?? $det->nombre_medicamento ?? '—' }}</td>
                                <td class="px-4 py-2.5 text-right font-bold text-slate-700 dark:text-slate-300">{{ $det->cantidad_recetada }}</td>
                                <td class="px-4 py-2.5 text-slate-500 dark:text-slate-400 italic">{{ $det->posologia ?? '—' }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="px-4 py-8 text-center text-slate-400">Sin medicamentos registrados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Imagen/Archivo --}}
            @if($receta->archivo_receta)
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm p-5">
                <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200 mb-3">Archivo Adjunto</h3>
                <a href="{{ Storage::url($receta->archivo_receta) }}" target="_blank" class="inline-flex items-center space-x-2 px-4 py-2.5 bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-sm font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-200 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                    <span>Ver Receta Adjunta</span>
                </a>
            </div>
            @endif
        </div>

        {{-- Col derecha 1/3: paciente + estado --}}
        <div class="space-y-5">

            {{-- Card: Paciente --}}
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="flex items-center gap-2 px-5 py-3.5 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30">
                    <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200">Paciente</h3>
                </div>
                <div class="p-5 space-y-3 text-xs">
                    @if($receta->cliente)
                    <div class="flex items-center gap-2 p-2.5 bg-emerald-50 dark:bg-emerald-950/30 rounded-lg border border-emerald-200 dark:border-emerald-800">
                        <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                        <a href="{{ route('clientes.show', $receta->cliente) }}" class="text-emerald-700 dark:text-emerald-400 font-semibold hover:underline">{{ $receta->cliente->nombre }}</a>
                    </div>
                    @endif
                    <div><p class="font-semibold text-slate-400 uppercase tracking-wider mb-0.5">Nombre Paciente</p><p class="font-bold text-slate-800 dark:text-slate-200">{{ $receta->paciente_nombre ?? '—' }}</p></div>
                    <div><p class="font-semibold text-slate-400 uppercase tracking-wider mb-0.5">Documento</p><p class="font-semibold text-slate-700 dark:text-slate-300 font-mono">{{ $receta->paciente_documento ?? '—' }}</p></div>
                    <div><p class="font-semibold text-slate-400 uppercase tracking-wider mb-0.5">Edad</p><p class="font-semibold text-slate-700 dark:text-slate-300">{{ $receta->paciente_edad ? $receta->paciente_edad.' años' : '—' }}</p></div>
                </div>
            </div>

            {{-- Card: Cambiar Estado --}}
            @can('registrar recetas')
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="flex items-center gap-2 px-5 py-3.5 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30">
                    <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200">Cambiar Estado</h3>
                </div>
                <div class="p-5 space-y-3">
                    {{-- Botones rapidos de estado --}}
                    <div class="grid grid-cols-1 gap-2">
                        @php
                        $estados = [
                            'pendiente'          => ['Pendiente',          'bg-amber-50 dark:bg-amber-950/30 border-amber-200 dark:border-amber-700 text-amber-700 dark:text-amber-300 hover:bg-amber-100'],
                            'validada'           => ['Validada',           'bg-emerald-50 dark:bg-emerald-950/30 border-emerald-200 dark:border-emerald-700 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100'],
                            'dispensada'         => ['Dispensada',         'bg-indigo-50 dark:bg-indigo-950/30 border-indigo-200 dark:border-indigo-700 text-indigo-700 dark:text-indigo-300 hover:bg-indigo-100'],
                            'dispensada_parcial' => ['Dispensada Parcial', 'bg-cyan-50 dark:bg-cyan-950/30 border-cyan-200 dark:border-cyan-700 text-cyan-700 dark:text-cyan-300 hover:bg-cyan-100'],
                            'anulada'            => ['Anulada',            'bg-rose-50 dark:bg-rose-950/30 border-rose-200 dark:border-rose-700 text-rose-700 dark:text-rose-300 hover:bg-rose-100'],
                        ];
                        @endphp
                        @foreach($estados as $val => [$label, $cls])
                        @if($receta->estado !== $val)
                        <form method="POST" action="{{ route('recetas.estado', $receta) }}">
                            @csrf
                            <input type="hidden" name="estado" value="{{ $val }}">
                            <button type="submit" class="w-full px-3 py-2 rounded-xl border text-xs font-semibold transition {{ $cls }}">
                                Marcar como <strong>{{ $label }}</strong>
                            </button>
                        </form>
                        @else
                        <div class="w-full px-3 py-2 rounded-xl border text-xs font-bold {{ $cls }} opacity-60 cursor-default flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            Estado actual: <strong>{{ $label }}</strong>
                        </div>
                        @endif
                        @endforeach
                    </div>
                </div>
            </div>
            @endcan

            {{-- Card: Historial --}}
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm p-5 text-xs space-y-2">
                <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200 mb-3">Registro</h3>
                <div><p class="font-semibold text-slate-400 uppercase tracking-wider mb-0.5">Creada</p><p class="text-slate-600 dark:text-slate-400">{{ $receta->created_at->format('d/m/Y H:i') }}</p></div>
                <div><p class="font-semibold text-slate-400 uppercase tracking-wider mb-0.5">Ultima actualizacion</p><p class="text-slate-600 dark:text-slate-400">{{ $receta->updated_at->format('d/m/Y H:i') }}</p></div>
                @if($receta->ventas->count())
                <div><p class="font-semibold text-slate-400 uppercase tracking-wider mb-0.5">Ventas asociadas</p><p class="font-bold text-emerald-600 dark:text-emerald-400">{{ $receta->ventas->count() }} venta(s)</p></div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
