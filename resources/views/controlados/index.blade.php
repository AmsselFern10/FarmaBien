@extends('layouts.app')

@section('title', 'Medicamentos Controlados (MINSA) - FarmaBien')

@section('content')
<div class="space-y-5" x-data="{ posFullscreen: false }">

    <!-- Breadcrumbs -->
    <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('productos.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Medicamentos</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-800 dark:text-slate-200 font-semibold">Medicamentos Controlados (MINSA)</span>
    </nav>

    <!-- Header & Action Buttons -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-3">
                <h1 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <span>Libro & Registro de Controlados</span>
                </h1>
                <span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-purple-100 dark:bg-purple-950/60 text-purple-800 dark:text-purple-300 border border-purple-200 dark:border-purple-800">
                    Fiscalización MINSA
                </span>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Auditoría obligatoria de despachos de psicotrópicos, estupefacientes y recetas médicas retenidas.
            </p>
        </div>
        
        <div class="flex items-center gap-2 shrink-0 flex-wrap">
            <!-- Botón Modo Full -->
            <button type="button" 
                    @click="$dispatch('toggle-pos-fullscreen')"
                    title="Modo Pantalla Completa / Ocultar Barras"
                    class="inline-flex items-center space-x-1.5 px-3 py-2 rounded-xl bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold shadow-2xs transition cursor-pointer">
                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                <span>Modo Full</span>
            </button>

            <!-- Ver Catálogo Filtrado -->
            <a href="{{ route('productos.index', ['tipo_control' => 'controlados']) }}" 
               class="inline-flex items-center space-x-1.5 px-3 py-2 rounded-xl bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold shadow-2xs transition">
                <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                <span>Fármacos Nivel I-III</span>
            </a>

            <!-- Imprimir Libro Oficial MINSA -->
            <a href="{{ route('controlados.libro') }}?{{ http_build_query(request()->only(['desde','hasta','nivel','q'])) }}" 
               target="_blank"
               class="inline-flex items-center space-x-1.5 px-3.5 py-2 bg-purple-700 hover:bg-purple-800 active:bg-purple-900 text-white text-xs font-bold rounded-xl shadow-xs transition cursor-pointer">
                <svg class="w-4 h-4 text-purple-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Imprimir Libro MINSA</span>
            </a>
        </div>
    </div>

    <!-- Quick Classification Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Despachos -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Total Despachos</p>
                <p class="text-xl font-extrabold text-slate-900 dark:text-white mt-0.5">{{ $registros->total() }} registros</p>
                <p class="text-[11px] text-slate-400 mt-0.5">En el período consultado</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
        </div>

        <!-- Nivel I -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 border border-purple-200 dark:border-purple-900/50 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-purple-700 dark:text-purple-400">Nivel I (Psicotrópicos)</p>
                <p class="text-xs text-slate-700 dark:text-slate-300 font-medium mt-1">Benzodiacepinas, Anticonvulsivos</p>
                <p class="text-[11px] text-purple-600 dark:text-purple-400 mt-0.5 font-semibold">Control Ordinario MINSA</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 flex items-center justify-center font-black text-sm">
                N-I
            </div>
        </div>

        <!-- Nivel II -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 border border-indigo-200 dark:border-indigo-900/50 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-indigo-700 dark:text-indigo-400">Nivel II (Estupefacientes)</p>
                <p class="text-xs text-slate-700 dark:text-slate-300 font-medium mt-1">Tramadol, Codeína, Opioides leves</p>
                <p class="text-[11px] text-indigo-600 dark:text-indigo-400 mt-0.5 font-semibold">Receta Retenida</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 flex items-center justify-center font-black text-sm">
                N-II
            </div>
        </div>

        <!-- Nivel III -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 border border-rose-200 dark:border-rose-900/50 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-rose-700 dark:text-rose-400">Nivel III (Alto Control)</p>
                <p class="text-xs text-slate-700 dark:text-slate-300 font-medium mt-1">Morfina, Fentanilo, Narcóticos</p>
                <p class="text-[11px] text-rose-600 dark:text-rose-400 mt-0.5 font-semibold">Fiscalización Especial</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 flex items-center justify-center font-black text-sm">
                N-III
            </div>
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs">
        <form method="GET" action="{{ route('controlados.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
            
            <!-- Buscador general -->
            <div class="sm:col-span-4 relative">
                <input type="text" name="q" value="{{ request('q') }}" 
                       placeholder="Buscar por paciente, cédula, médico o medicamento..."
                       class="w-full pl-9 pr-3.5 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white font-medium focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </div>

            <!-- Filtro Nivel -->
            <div class="sm:col-span-2">
                <select name="nivel" 
                        class="w-full px-3 py-2 text-xs font-semibold rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-purple-500">
                    <option value="">Todos los Niveles</option>
                    <option value="1" @selected(request('nivel') == '1')>Nivel I (Psicotrópico)</option>
                    <option value="2" @selected(request('nivel') == '2')>Nivel II (Estupefaciente)</option>
                    <option value="3" @selected(request('nivel') == '3')>Nivel III (Alto Control)</option>
                </select>
            </div>

            <!-- Fecha Desde -->
            <div class="sm:col-span-2">
                <input type="date" name="desde" value="{{ request('desde', now()->startOfMonth()->toDateString()) }}"
                       class="w-full px-3 py-2 text-xs font-semibold rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-purple-500">
            </div>

            <!-- Fecha Hasta -->
            <div class="sm:col-span-2">
                <input type="date" name="hasta" value="{{ request('hasta', now()->toDateString()) }}"
                       class="w-full px-3 py-2 text-xs font-semibold rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-purple-500">
            </div>

            <!-- Botones -->
            <div class="sm:col-span-2 flex items-center gap-2 justify-end">
                <button type="submit" 
                        class="px-4 py-2 bg-purple-700 hover:bg-purple-800 text-white text-xs font-bold rounded-xl transition shadow-xs">
                    Filtrar
                </button>
                @if(request()->hasAny(['q', 'nivel', 'desde', 'hasta']))
                <a href="{{ route('controlados.index') }}" 
                   class="px-3 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 text-xs font-semibold rounded-xl transition">
                    Limpiar
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table of Controlled Drug Dispatches -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50/75 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800 text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                        <th class="px-4 py-3">Fecha & Folio</th>
                        <th class="px-4 py-3">Medicamento / Principio</th>
                        <th class="px-4 py-3 text-center">Nivel MINSA</th>
                        <th class="px-4 py-3">Paciente</th>
                        <th class="px-4 py-3">Médico Prescriptor</th>
                        <th class="px-4 py-3 text-center">Cantidad</th>
                        <th class="px-4 py-3 text-center">Venta / Ticket</th>
                        <th class="px-4 py-3">Despachado Por</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    @forelse($registros as $reg)
                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition">
                        <!-- Fecha y Hora -->
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span class="font-bold text-slate-900 dark:text-white block font-mono">
                                {{ $reg->created_at->format('d/m/Y') }}
                            </span>
                            <span class="text-[11px] text-slate-400 font-mono">
                                {{ $reg->created_at->format('H:i') }} hrs
                            </span>
                        </td>

                        <!-- Medicamento -->
                        <td class="px-4 py-3">
                            <span class="font-bold text-slate-800 dark:text-slate-200 block">
                                {{ $reg->producto->nombre ?? 'Medicamento no disponible' }}
                            </span>
                            <span class="text-[11px] text-slate-400 block truncate max-w-[200px]">
                                {{ $reg->producto?->principio_activo ?: 'Sin fórmula registrada' }}
                            </span>
                        </td>

                        <!-- Nivel MINSA -->
                        <td class="px-4 py-3 text-center whitespace-nowrap">
                            @if($reg->nivel_controlado == 1)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 dark:bg-purple-950/60 text-purple-800 dark:text-purple-300 border border-purple-200 dark:border-purple-800">
                                    Nivel I (Psicotrópico)
                                </span>
                            @elseif($reg->nivel_controlado == 2)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-100 dark:bg-indigo-950/60 text-indigo-800 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                                    Nivel II (Estupefaciente)
                                </span>
                            @elseif($reg->nivel_controlado == 3)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 dark:bg-rose-950/60 text-rose-800 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                    Nivel III (Alto Control)
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">
                                    General
                                </span>
                            @endif
                        </td>

                        <!-- Paciente -->
                        <td class="px-4 py-3">
                            <span class="font-bold text-slate-800 dark:text-slate-200 block">
                                {{ $reg->paciente_nombre }}
                            </span>
                            <div class="text-[11px] text-slate-400 flex items-center space-x-2">
                                @if($reg->paciente_cedula)
                                <span class="font-mono">Céd: {{ $reg->paciente_cedula }}</span>
                                @endif
                                @if($reg->paciente_edad)
                                <span>• {{ $reg->paciente_edad }} años</span>
                                @endif
                            </div>
                        </td>

                        <!-- Médico Prescriptor -->
                        <td class="px-4 py-3">
                            <span class="font-bold text-slate-800 dark:text-slate-200 block">
                                Dr(a). {{ $reg->medico_nombre }}
                            </span>
                            @if($reg->medico_num_registro)
                            <span class="text-[11px] text-purple-700 dark:text-purple-400 font-mono font-semibold">
                                Reg. MINSA: {{ $reg->medico_num_registro }}
                            </span>
                            @endif
                        </td>

                        <!-- Cantidad -->
                        <td class="px-4 py-3 text-center whitespace-nowrap">
                            <span class="font-extrabold text-slate-900 dark:text-white block text-sm">
                                {{ number_format($reg->cantidad, 0) }}
                            </span>
                            <span class="text-[10px] text-slate-400 font-semibold uppercase">
                                {{ $reg->unidad ?: 'unid.' }}
                            </span>
                        </td>

                        <!-- Venta -->
                        <td class="px-4 py-3 text-center whitespace-nowrap">
                            @if($reg->venta_id)
                            <a href="{{ route('ventas.show', $reg->venta_id) }}" 
                               class="font-mono font-bold text-emerald-600 dark:text-emerald-400 hover:underline">
                                #{{ str_pad($reg->venta_id, 5, '0', STR_PAD_LEFT) }}
                            </a>
                            @else
                            <span class="text-slate-400">—</span>
                            @endif
                        </td>

                        <!-- Despachó -->
                        <td class="px-4 py-3 whitespace-nowrap text-slate-600 dark:text-slate-400">
                            {{ $reg->usuario->name ?? 'Sistema' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center text-slate-500 dark:text-slate-400">
                            <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 flex items-center justify-center mx-auto mb-2">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <p class="font-bold text-slate-700 dark:text-slate-300">No hay registros de medicamentos controlados</p>
                            <p class="text-xs text-slate-400 mt-0.5">Los despachos de productos con Nivel I, II y III en el POS se registrarán aquí con datos del paciente y médico.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($registros->hasPages())
        <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-800">
            {{ $registros->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
