@extends('layouts.app')

@section('title', $laboratorio->nombre . ' - Ficha de Laboratorio - FarmaBien')

@section('content')
<div class="space-y-5">
    <!-- Breadcrumb -->
    <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('laboratorios.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Laboratorios</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-800 dark:text-slate-200 font-semibold truncate">{{ $laboratorio->nombre }}</span>
    </nav>

    <!-- Header & Quick Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-3">
                <h1 class="text-xl font-bold text-slate-900 dark:text-white">{{ $laboratorio->nombre }}</h1>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold {{ $laboratorio->activo ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-950 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700' }}">
                    {{ $laboratorio->activo ? 'Activo' : 'Inactivo' }}
                </span>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Casa matriz fabricante y registro sanitario farmacéutico autorizado.
            </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap shrink-0">
            <!-- 1. Botón Volver -->
            <a href="{{ route('laboratorios.index') }}" 
               class="px-3.5 py-1.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold transition inline-flex items-center gap-1.5 shrink-0 cursor-pointer shadow-2xs">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>&larr; Laboratorios</span>
            </a>

            <!-- 2. Botón Modo Full -->
            <button type="button" 
                    @click="$dispatch('toggle-pos-fullscreen')"
                    title="Modo Pantalla Completa / Ocultar Barras"
                    class="px-3.5 py-1.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold transition inline-flex items-center gap-1.5 shrink-0 cursor-pointer shadow-2xs">
                <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                <span class="hidden sm:inline">Modo Full</span>
            </button>

            @can('editar laboratorios')
            <!-- 3. Botón Editar -->
            <a href="{{ route('laboratorios.edit', $laboratorio) }}" 
               class="px-3.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-semibold shadow-xs transition inline-flex items-center gap-1.5 cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Editar Laboratorio</span>
            </a>
            @endcan
        </div>
    </div>

    <!-- Highlight Metrics Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Código / Sigla con botón de copia -->
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs"
             x-data="{ copied: false }">
            <div class="flex items-center justify-between">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Código / Sigla</p>
                @if($laboratorio->codigo)
                <button type="button" 
                        @click="navigator.clipboard.writeText('{{ $laboratorio->codigo }}'); copied = true; setTimeout(() => copied = false, 2000)" 
                        class="text-[10px] font-semibold text-slate-500 hover:text-emerald-600 dark:text-slate-400 dark:hover:text-emerald-400 inline-flex items-center gap-1">
                    <span x-show="!copied" class="inline-flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                        <span>Copiar</span>
                    </span>
                    <span x-show="copied" class="text-emerald-600 dark:text-emerald-400">✓ Copiado</span>
                </button>
                @endif
            </div>
            <p class="text-base font-mono font-black text-slate-900 dark:text-white mt-1">{{ $laboratorio->codigo ?? 'SIN CÓDIGO' }}</p>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs">
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">País de Origen</p>
            <p class="text-base font-bold text-slate-900 dark:text-white mt-1">{{ $laboratorio->pais_origen ?? 'No especificado' }}</p>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs">
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Medicamentos Registrados</p>
            <p class="text-base font-black text-emerald-600 dark:text-emerald-400 mt-1">{{ $productos->total() ?? 0 }} productos</p>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs">
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Contacto Directo</p>
            <p class="text-xs font-bold text-slate-800 dark:text-slate-200 mt-1.5 truncate">{{ $laboratorio->contacto ?? 'Sin contacto' }}</p>
            <p class="text-[11px] font-mono text-slate-400 truncate">{{ $laboratorio->telefono ?? 'Sin teléfono' }}</p>
        </div>
    </div>

    <!-- Contact & Email Detailed Banner -->
    <div class="bg-white dark:bg-slate-900 rounded-xl p-5 border border-slate-200 dark:border-slate-800 shadow-xs grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <p class="text-[11px] font-bold text-slate-400 uppercase">Correo Electrónico Oficial</p>
            <p class="text-xs text-slate-800 dark:text-slate-200 font-medium mt-0.5">{{ $laboratorio->email ?? 'No especificado' }}</p>
        </div>
        <div>
            <p class="text-[11px] font-bold text-slate-400 uppercase">Registro en Sistema</p>
            <p class="text-xs text-slate-800 dark:text-slate-200 font-medium mt-0.5">{{ $laboratorio->created_at ? $laboratorio->created_at->format('d/m/Y H:i') : '—' }}</p>
        </div>
    </div>

    <!-- Associated Products Section -->
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <span>Medicamentos Fabricados</span>
                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300">
                        {{ $productos->total() ?? 0 }}
                    </span>
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Especialidades farmacéuticas vinculadas a este laboratorio.</p>
            </div>
            @can('crear productos')
            <a href="{{ route('productos.create') }}" class="text-xs font-bold text-emerald-600 dark:text-emerald-400 hover:underline">
                + Nuevo Medicamento
            </a>
            @endcan
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800 uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="px-5 py-3.5">Código de Barra</th>
                        <th class="px-5 py-3.5">Medicamento / Principio Activo</th>
                        <th class="px-5 py-3.5">Categoría</th>
                        <th class="px-5 py-3.5 text-right">Precio Venta</th>
                        <th class="px-5 py-3.5 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-700 dark:text-slate-300">
                    @forelse($productos as $prod)
                    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition">
                        <td class="px-5 py-3.5 font-mono text-slate-600 dark:text-slate-300">
                            {{ $prod->codigo_barra ?? '#' . $prod->id }}
                        </td>
                        <td class="px-5 py-3.5 font-semibold text-slate-900 dark:text-white">
                            {{ $prod->nombre_completo }}
                        </td>
                        <td class="px-5 py-3.5 text-slate-500 dark:text-slate-400">
                            {{ $prod->categoria->nombre ?? '—' }}
                        </td>
                        <td class="px-5 py-3.5 text-right font-mono font-bold text-slate-900 dark:text-white">
                            S/ {{ number_format($prod->precio_venta, 2) }}
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            @can('ver productos')
                            <a href="{{ route('productos.show', $prod) }}" class="text-emerald-600 dark:text-emerald-400 hover:underline font-semibold">Ver Ficha &rarr;</a>
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-5 py-8 text-center text-slate-400">
                            No hay medicamentos registrados bajo este laboratorio.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($productos->hasPages())
        <div class="px-5 py-3.5 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900">
            {{ $productos->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
