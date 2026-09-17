@extends('layouts.app')

@section('title', 'Ficha de Categoría - FarmaBien')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <!-- Breadcrumb & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('categorias.index') }}" class="hover:text-emerald-600 transition">Categorías</a>
                <span>/</span>
                <span class="text-slate-800 dark:text-slate-300 font-medium">Ficha Técnica</span>
            </div>
            <div class="flex items-center space-x-3">
                <h1 class="text-xl font-bold text-slate-900 dark:text-white">{{ $categoria->nombre }}</h1>
                @if($categoria->activo)
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                    Activa
                </span>
                @else
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400">
                    Inactiva
                </span>
                @endif
            </div>
        </div>
        <div class="flex items-center space-x-2 shrink-0">
            @can('editar categorias')
            <a href="{{ route('categorias.edit', $categoria) }}" 
               class="inline-flex items-center space-x-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-semibold rounded-xl transition shadow-sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Editar</span>
            </a>
            @endcan
            <a href="{{ route('categorias.index') }}" 
               class="px-3.5 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-xl transition">
                &larr; Volver
            </a>
        </div>
    </div>

    <!-- Highlight Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm">
            <p class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">Medicamentos en esta Categoría</p>
            <p class="text-base font-bold text-emerald-600 dark:text-emerald-400 mt-1">{{ $productos->total() ?? 0 }} productos</p>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm">
            <p class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">Estado en el Catálogo</p>
            <p class="text-base font-bold text-slate-900 dark:text-white mt-1">{{ $categoria->activo ? 'Habilitada' : 'Deshabilitada' }}</p>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm">
            <p class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">Fecha de Creación</p>
            <p class="text-xs font-semibold text-slate-700 dark:text-slate-300 mt-1">{{ $categoria->created_at ? $categoria->created_at->format('d/m/Y H:i') : '—' }}</p>
        </div>
    </div>

    <!-- Description Card -->
    <div class="bg-white dark:bg-slate-900 rounded-xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm">
        <p class="text-[11px] font-medium text-slate-400 uppercase">Descripción / Indicaciones Clínicas</p>
        <p class="text-xs sm:text-sm text-slate-700 dark:text-slate-300 mt-1 leading-relaxed">
            {{ $categoria->descripcion ?? 'Sin descripción adicional registrada para esta categoría terapéutica.' }}
        </p>
    </div>

    <!-- Associated Products Section -->
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                    Medicamentos Clasificados en este Grupo ({{ $productos->total() ?? 0 }})
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Productos e insumos vinculados terapéuticamente a esta categoría.</p>
            </div>
            @can('crear productos')
            <a href="{{ route('productos.create') }}" class="text-xs font-bold text-emerald-600 dark:text-emerald-400 hover:underline">
                + Nuevo Medicamento
            </a>
            @endcan
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3">Código de Barra</th>
                        <th class="px-5 py-3">Medicamento / Principio Activo</th>
                        <th class="px-5 py-3">Laboratorio</th>
                        <th class="px-5 py-3 text-right">Precio Venta</th>
                        <th class="px-5 py-3 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-700 dark:text-slate-300">
                    @forelse($productos as $prod)
                    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition">
                        <td class="px-5 py-3.5 font-mono text-slate-500 dark:text-slate-400">
                            {{ $prod->codigo_barra ?? '#' . $prod->id }}
                        </td>
                        <td class="px-5 py-3.5 font-semibold text-slate-900 dark:text-white">
                            {{ $prod->nombre_completo }}
                        </td>
                        <td class="px-5 py-3.5 text-slate-500 dark:text-slate-400">
                            {{ $prod->laboratorio->nombre ?? '—' }}
                        </td>
                        <td class="px-5 py-3.5 text-right font-mono font-bold text-slate-900 dark:text-white">
                            ${{ number_format($prod->precio_venta, 2) }}
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
                            No hay medicamentos registrados bajo esta categoría terapéutica.
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
