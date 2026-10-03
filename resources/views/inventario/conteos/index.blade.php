@extends('layouts.app')
@section('title', 'Tomas de Inventario - FarmaBien')
@section('content')
<div class="space-y-5">

    {{-- Breadcrumb --}}
    <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('inventario.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inventario</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-800 dark:text-slate-200 font-semibold">Tomas de Inventario</span>
    </nav>

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">Tomas de Inventario Físico</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Conteo cíclico: compara el stock del sistema vs. el conteo físico y aplica ajustes automáticamente.
            </p>
        </div>
        <a href="{{ route('inventario.conteos.create') }}"
           class="inline-flex items-center space-x-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-semibold rounded-xl shadow-sm transition shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Nueva Toma de Inventario</span>
        </a>
    </div>

    @if(session('success'))
    <div class="px-4 py-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-300 dark:border-emerald-700 text-xs text-emerald-800 dark:text-emerald-300 font-semibold flex items-center space-x-2">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span>{{ session('success') }}</span>
    </div>
    @endif
    @if(session('error'))
    <div class="px-4 py-3 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-300 dark:border-rose-700 text-xs text-rose-800 dark:text-rose-300 font-semibold flex items-center space-x-2">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    {{-- Table --}}
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">Nombre</th>
                        <th class="px-5 py-3.5 text-center">Estado</th>
                        <th class="px-5 py-3.5 text-center">Progreso</th>
                        <th class="px-5 py-3.5 text-center">Diferencia</th>
                        <th class="px-5 py-3.5">Responsable</th>
                        <th class="px-5 py-3.5">Fecha</th>
                        <th class="px-5 py-3.5 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-700 dark:text-slate-300">
                    @forelse($conteos as $conteo)
                    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition">
                        <td class="px-5 py-3.5">
                            <div class="font-semibold text-slate-900 dark:text-white">{{ $conteo->nombre }}</div>
                            @if($conteo->notas)
                            <div class="text-slate-400 text-[10px] mt-0.5 truncate max-w-xs">{{ $conteo->notas }}</div>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            @if($conteo->estado === 'en_proceso')
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse inline-block"></span>En Proceso
                            </span>
                            @elseif($conteo->estado === 'completado')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">✓ Completado</span>
                            @elseif($conteo->estado === 'cancelado')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-700">Cancelado</span>
                            @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800">Borrador</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            @if($conteo->total_lotes > 0)
                            <div class="flex items-center justify-center gap-2">
                                <div class="w-20 bg-slate-200 dark:bg-slate-700 rounded-full h-1.5">
                                    @php $pct = round(($conteo->lotes_contados / $conteo->total_lotes) * 100) @endphp
                                    <div class="bg-emerald-500 h-1.5 rounded-full" style="width: {{ $pct }}%"></div>
                                </div>
                                <span class="text-[10px] font-semibold text-slate-600 dark:text-slate-400">{{ $conteo->lotes_contados }}/{{ $conteo->total_lotes }}</span>
                            </div>
                            @else
                            <span class="text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            @if($conteo->diferencia_total_unidades != 0)
                            <span class="font-bold font-mono {{ $conteo->diferencia_total_unidades > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                {{ $conteo->diferencia_total_unidades > 0 ? '+' : '' }}{{ $conteo->diferencia_total_unidades }}
                            </span>
                            @else
                            <span class="text-slate-400">0</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-slate-600 dark:text-slate-400">{{ $conteo->usuario->name ?? '—' }}</td>
                        <td class="px-5 py-3.5 text-slate-500 dark:text-slate-400">{{ $conteo->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-5 py-3.5 text-right">
                            <a href="{{ route('inventario.conteos.show', $conteo) }}"
                               class="inline-flex items-center gap-1 px-3 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-[11px] font-semibold rounded-lg transition">
                                {{ $conteo->estado === 'en_proceso' ? 'Continuar' : 'Ver' }}
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center text-slate-400 text-xs">
                            No hay tomas de inventario registradas. Inicia la primera con el botón de arriba.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($conteos->hasPages())
        <div class="px-5 py-3.5 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900">
            {{ $conteos->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
