@extends('layouts.app')
@section('title', 'Nueva Toma de Inventario - FarmaBien')
@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    {{-- Breadcrumb --}}
    <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
        <a href="{{ route('inventario.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inventario</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('inventario.conteos.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Tomas de Inventario</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-800 dark:text-slate-200 font-semibold">Nueva Toma</span>
    </nav>

    {{-- Header --}}
    <div>
        <h1 class="text-xl font-bold text-slate-900 dark:text-white">Nueva Toma de Inventario</h1>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
            Se generará un snapshot de <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ $totalLotesActivos }} lotes</span>
            activos con stock para que puedas contarlos físicamente.
        </p>
    </div>

    {{-- Info --}}
    <div class="flex items-start gap-3 px-4 py-3.5 bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 rounded-xl text-xs text-blue-800 dark:text-blue-300">
        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <div>
            <span class="font-bold block mb-0.5">¿Cómo funciona?</span>
            <ol class="list-decimal list-inside space-y-0.5">
                <li>Creas la sesión → el sistema congela el stock actual de cada lote.</li>
                <li>Vas a la farmacia y cuentas unidad por unidad, lote por lote.</li>
                <li>Ingresas las cantidades físicas en pantalla.</li>
                <li>Al aprobar → el sistema ajusta automáticamente el stock de cada lote con diferencia y registra el Kardex.</li>
            </ol>
        </div>
    </div>

    <form method="POST" action="{{ route('inventario.conteos.store') }}" class="space-y-5">
        @csrf

        <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm space-y-5">
            <div>
                <label for="nombre" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                    Nombre del Conteo <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="nombre" name="nombre"
                       value="{{ old('nombre', 'Toma de Inventario ' . now()->format('d/m/Y')) }}"
                       required maxlength="200"
                       class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition @error('nombre') border-rose-500 @enderror"
                       placeholder="Ej. Conteo Cíclico Octubre 2026, Auditoría MINSA Oct 2026...">
                @error('nombre') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="notas" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                    Notas / Contexto
                </label>
                <textarea id="notas" name="notas" rows="3"
                          placeholder="Observaciones, alcance del conteo, responsables, etc."
                          class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition">{{ old('notas') }}</textarea>
            </div>
        </div>

        <div class="flex items-center justify-between">
            <a href="{{ route('inventario.conteos.index') }}"
               class="px-5 py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-xl transition">
                Cancelar
            </a>
            <button type="submit"
                    class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-sm font-semibold rounded-xl shadow-sm transition inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                Iniciar Conteo ({{ $totalLotesActivos }} lotes)
            </button>
        </div>
    </form>
</div>
@endsection
