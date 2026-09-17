@extends('layouts.app')

@section('title', 'Recetas Médicas')

@section('header')
    <div class="flex items-start justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Gestión de Recetas Médicas</h2>
            <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">Listado y consulta de recetas por cliente y número.</p>
        </div>
        @can('registrar recetas')
        <a href="{{ route('recetas.create') }}"
           class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg shadow-sm transition-colors">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Nueva Receta
        </a>
        @endcan
    </div>
@endsection

@section('content')
@endsection
