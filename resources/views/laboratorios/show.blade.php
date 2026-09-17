@extends('layouts.app')

@section('title', 'Ficha de Laboratorio - FarmaBien')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <!-- Breadcrumb & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('laboratorios.index') }}" class="hover:text-emerald-600 transition">Laboratorios</a>
                <span>/</span>
                <span class="text-slate-800 dark:text-slate-300 font-medium">Ficha Técnica</span>
            </div>
            <div class="flex items-center space-x-3">
                <h1 class="text-xl font-bold text-slate-900 dark:text-white">{{ $laboratorio->nombre }}</h1>
                @if($laboratorio->activo)
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                    Activo
                </span>
                @else
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400">
                    Inactivo
                </span>
                @endif
            </div>
        </div>
        <div class="flex items-center space-x-2 shrink-0">
            @can('editar laboratorios')
            <a href="{{ route('laboratorios.edit', $laboratorio) }}" 
               class="inline-flex items-center space-x-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-semibold rounded-xl transition shadow-sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Editar</span>
            </a>
            @endcan
            <a href="{{ route('laboratorios.index') }}" 
               class="px-3.5 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-xl transition">
                &larr; Volver
            </a>
        </div>
    </div>

    <!-- Metadata Highlight Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm">
            <p class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">Código / Sigla</p>
            <p class="text-base font-mono font-bold text-slate-900 dark:text-white mt-1">{{ $laboratorio->codigo ?? 'Sin código' }}</p>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm">
            <p class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">País de Origen</p>
            <p class="text-base font-bold text-slate-900 dark:text-white mt-1">{{ $laboratorio->pais_origen ?? 'No especificado' }}</p>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm">
            <p class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">Medicamentos Registrados</p>
            <p class="text-base font-bold text-emerald-600 dark:text-emerald-400 mt-1">{{ $productos->total() ?? 0 }} productos</p>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm">
            <p class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">Contacto Directo</p>
            <p class="text-xs font-semibold text-slate-700 dark:text-slate-300 mt-1 truncate">{{ $laboratorio->contacto ?? 'S/C' }} ({{ $laboratorio->telefono ?? 'S/T' }})</p>
        </div>
    </div>

    <!-- Contact & Email Detailed Banner -->
    <div class="bg-white dark:bg-slate-900 rounded-xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <p class="text-[11px] font-medium text-slate-400 uppercase">Correo Electrónico Oficial</p>
            <p class="text-xs text-slate-800 dark:text-slate-200 font-medium mt-0.5">{{ $laboratorio->email ?? 'No especificado' }}</p>
        </div>
        <div>
            <p class="text-[11px] font-medium text-slate-400 uppercase">Registro en Sistema</p>
            <p class="text-xs text-slate-800 dark:text-slate-200 font-medium mt-0.5">{{ $laboratorio->created_at ? $laboratorio->created_at->format('d/m/Y H:i') : '—' }}</p>
        </div>
    </div>

    <!-- Associated Products Section -->
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                    Catálogo de Medicamentos Fabricados ({{ $productos->total() ?? 0 }})
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Productos de este laboratorio disponibles en el inventario.</p>
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
                        <th class="px-5 py-3">Categoría</th>
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
                            {{ $prod->categoria->nombre ?? '—' }}
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
