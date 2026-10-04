@extends('layouts.app')
@section('title', 'Tomas de Inventario - FarmaBien')
@section('content')
<div class="space-y-5">

    {{-- Fila 1: Breadcrumb Completo --}}
    <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('inventario.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inventario</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-800 dark:text-slate-200 font-semibold">Tomas de Inventario</span>
    </nav>

    {{-- Fila 2: Título + Botones de Acción --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <span>Tomas de Inventario Físico</span>
                @if($tomasEnProcesoCount > 0)
                <span class="px-2 py-0.5 text-xs font-bold rounded-full bg-blue-50 text-blue-900 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200 dark:border-blue-800 flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-600 animate-pulse"></span>
                    <span>{{ $tomasEnProcesoCount }} en proceso</span>
                </span>
                @endif
            </h1>
            <p class="text-xs text-slate-700 dark:text-slate-400 mt-0.5">
                Conteo cíclico y auditoría física: compara el stock del sistema vs. el conteo físico y aplica ajustes automáticamente en el Kardex.
            </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap shrink-0">
            <!-- 1. Botón Predecesor (Blanco) -->
            <a href="{{ route('inventario.index') }}" 
               class="px-3.5 py-1.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold transition inline-flex items-center gap-1.5 shrink-0 cursor-pointer shadow-2xs">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Inventario</span>
            </a>

            <!-- 2. Botón Modo Full (Blanco) -->
            <button type="button" 
                    @click="$dispatch('toggle-pos-fullscreen')"
                    title="Modo Pantalla Completa / Ocultar Barras"
                    class="px-3.5 py-1.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold transition inline-flex items-center gap-1.5 shrink-0 cursor-pointer shadow-2xs">
                <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                <span>Modo Full</span>
            </button>

            <!-- 3. Botón Principal (Verde Sólido Institucional) -->
            <a href="{{ route('inventario.conteos.create') }}"
               class="px-3.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-semibold shadow-xs transition inline-flex items-center gap-1.5 shrink-0 cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Nueva Toma de Inventario</span>
            </a>
        </div>
    </div>

    {{-- Alertas del Sistema --}}
    @if(session('success'))
    <div class="px-4 py-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-300 dark:border-emerald-700 text-xs text-emerald-900 dark:text-emerald-300 font-semibold flex items-center space-x-2">
        <svg class="w-4 h-4 shrink-0 text-emerald-700 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span>{{ session('success') }}</span>
    </div>
    @endif
    @if(session('error'))
    <div class="px-4 py-3 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-300 dark:border-rose-700 text-xs text-red-900 dark:text-rose-300 font-semibold flex items-center space-x-2">
        <svg class="w-4 h-4 shrink-0 text-red-700 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    {{-- Tabla de Tomas de Inventario --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-700 dark:text-slate-400 font-bold border-b border-slate-200 dark:border-slate-800 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">Nombre</th>
                        <th class="px-5 py-3.5">Alcance</th>
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
                        {{-- 1. Nombre --}}
                        <td class="px-5 py-3.5">
                            <a href="{{ route('inventario.conteos.show', $conteo) }}" class="font-bold text-slate-900 dark:text-white hover:text-emerald-700 dark:hover:text-emerald-400 transition">
                                {{ $conteo->nombre }}
                            </a>
                            @if($conteo->notas)
                            <div class="text-slate-700 dark:text-slate-400 text-[10px] mt-0.5 truncate max-w-xs">{{ $conteo->notas }}</div>
                            @endif
                        </td>

                        {{-- 2. Alcance (Chips) --}}
                        <td class="px-5 py-3.5">
                            <div class="flex flex-wrap gap-1 max-w-xs">
                                @foreach($conteo->alcance_chips as $chip)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-200 border border-slate-200 dark:border-slate-700">
                                    {{ $chip }}
                                </span>
                                @endforeach
                            </div>
                        </td>

                        {{-- 3. Estado con punto --}}
                        <td class="px-5 py-3.5 text-center whitespace-nowrap">
                            @if($conteo->estado === 'en_proceso')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-900 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                <span class="w-1.5 h-1.5 rounded-full bg-blue-600 animate-pulse inline-block"></span>
                                <span>En Proceso</span>
                            </span>
                            @elseif($conteo->estado === 'completado')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-900 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 inline-block"></span>
                                <span>Aprobada</span>
                            </span>
                            @elseif($conteo->estado === 'cancelado')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400 inline-block"></span>
                                <span>Cancelada</span>
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-900 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-600 inline-block"></span>
                                <span>Borrador</span>
                            </span>
                            @endif
                        </td>

                        {{-- 4. Progreso --}}
                        <td class="px-5 py-3.5 text-center whitespace-nowrap">
                            @if($conteo->total_lotes > 0)
                            <div class="flex flex-col items-center gap-1">
                                <span class="text-xs font-bold text-slate-900 dark:text-slate-200">
                                    {{ $conteo->lotes_contados }}/{{ $conteo->total_lotes }}
                                </span>
                                @php $pct = round(($conteo->lotes_contados / $conteo->total_lotes) * 100); @endphp
                                <div class="w-20 bg-slate-200 dark:bg-slate-700 rounded-full h-1.5 overflow-hidden">
                                    <div class="bg-emerald-500 h-1.5 rounded-full transition-all" style="width: {{ $pct }}%"></div>
                                </div>
                            </div>
                            @else
                            <span class="text-slate-700">—</span>
                            @endif
                        </td>

                        {{-- 5. Diferencia --}}
                        <td class="px-5 py-3.5 text-center whitespace-nowrap font-mono">
                            @if($conteo->diferencia_total_unidades > 0)
                            <span class="font-bold text-emerald-900 dark:text-emerald-400">
                                +{{ $conteo->diferencia_total_unidades }}
                            </span>
                            @elseif($conteo->diferencia_total_unidades < 0)
                            <span class="font-bold text-red-900 dark:text-rose-400">
                                {{ $conteo->diferencia_total_unidades }}
                            </span>
                            @else
                            <span class="text-slate-700 dark:text-slate-500">0</span>
                            @endif
                        </td>

                        {{-- 6. Responsable --}}
                        <td class="px-5 py-3.5 text-slate-700 dark:text-slate-300 font-medium">
                            {{ $conteo->usuario->name ?? '—' }}
                        </td>

                        {{-- 7. Fecha --}}
                        <td class="px-5 py-3.5 text-slate-700 dark:text-slate-400 whitespace-nowrap">
                            {{ $conteo->created_at->format('d/m/Y H:i') }}
                        </td>

                        {{-- 8. Acciones --}}
                        <td class="px-5 py-3.5 text-right whitespace-nowrap">
                            @if($conteo->estado === 'en_proceso')
                            <a href="{{ route('inventario.conteos.show', $conteo) }}"
                               class="h-8 px-3 rounded-full text-xs font-semibold bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/40 dark:hover:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 transition inline-flex items-center gap-1.5 cursor-pointer shadow-2xs">
                                <span>Continuar</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </a>
                            @else
                            <a href="{{ route('inventario.conteos.show', $conteo) }}"
                               class="w-8 h-8 rounded-full bg-blue-50 hover:bg-blue-100 dark:bg-blue-950/50 dark:hover:bg-blue-900/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800 transition inline-flex items-center justify-center cursor-pointer shadow-2xs"
                               title="Ver detalles de la toma">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-5 py-12 text-center text-slate-700 dark:text-slate-400 text-xs">
                            No hay tomas de inventario registradas. Inicia la primera con el botón de arriba.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($conteos->hasPages())
        <div class="px-5 py-3.5 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900">
            {{ $conteos->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
