@extends('layouts.app')

@section('title', 'Medicamentos Controlados — FarmaBien')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white flex items-center gap-2">
                <span class="text-2xl">🔴</span>
                Registro de Medicamentos Controlados
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                Despachos de medicamentos bajo control MINSA Nicaragua
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('controlados.libro') }}?{{ http_build_query(request()->only(['desde','hasta','nivel'])) }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-800 dark:bg-slate-700 text-white text-sm font-semibold hover:bg-slate-700 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Libro de Control (MINSA)
            </a>
        </div>
    </div>

    {{-- Niveles de control — leyenda --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div class="flex items-center gap-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-3">
            <span class="w-2.5 h-2.5 rounded-full bg-amber-400 shrink-0"></span>
            <div>
                <p class="text-xs font-bold text-amber-700 dark:text-amber-400">Nivel I — Control Básico</p>
                <p class="text-[11px] text-amber-600 dark:text-amber-500">Anticonvulsivos, benzodiacepinas leves</p>
            </div>
        </div>
        <div class="flex items-center gap-3 bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded-xl p-3">
            <span class="w-2.5 h-2.5 rounded-full bg-orange-400 shrink-0"></span>
            <div>
                <p class="text-xs font-bold text-orange-700 dark:text-orange-400">Nivel II — Opioides</p>
                <p class="text-[11px] text-orange-600 dark:text-orange-500">Tramadol, codeína, morfina leve</p>
            </div>
        </div>
        <div class="flex items-center gap-3 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 rounded-xl p-3">
            <span class="w-2.5 h-2.5 rounded-full bg-rose-400 shrink-0"></span>
            <div>
                <p class="text-xs font-bold text-rose-700 dark:text-rose-400">Nivel III — Narcóticos</p>
                <p class="text-[11px] text-rose-600 dark:text-rose-500">Morfina, fentanilo, opioides fuertes</p>
            </div>
        </div>
    </div>

    {{-- Filtros --}}
    <form method="GET" action="{{ route('controlados.index') }}"
          class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 p-4">
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-5 gap-3">
            <div class="col-span-2 sm:col-span-2">
                <input type="text" name="q" value="{{ request('q') }}"
                       placeholder="Buscar paciente, cédula, médico…"
                       class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500"/>
            </div>
            <div>
                <select name="nivel" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">Todos los niveles</option>
                    <option value="1" @selected(request('nivel') == '1')>Nivel I</option>
                    <option value="2" @selected(request('nivel') == '2')>Nivel II</option>
                    <option value="3" @selected(request('nivel') == '3')>Nivel III</option>
                </select>
            </div>
            <div>
                <input type="date" name="desde" value="{{ request('desde', now()->startOfMonth()->toDateString()) }}"
                       class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500"/>
            </div>
            <div class="flex gap-2">
                <input type="date" name="hasta" value="{{ request('hasta', now()->toDateString()) }}"
                       class="flex-1 px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500"/>
                <button type="submit"
                        class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl transition">
                    Filtrar
                </button>
            </div>
        </div>
    </form>

    {{-- Tabla --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
        @if($registros->isEmpty())
            <div class="py-16 text-center">
                <span class="text-5xl block mb-3">🔴</span>
                <p class="text-slate-500 dark:text-slate-400 font-medium">No hay registros de controlados con esos filtros.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-700">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Fecha</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Medicamento</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Nivel</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Paciente</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Médico</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Cantidad</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Venta</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Despachó</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($registros as $reg)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                            <td class="px-4 py-3 text-xs text-slate-500 whitespace-nowrap">
                                {{ $reg->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-medium text-slate-800 dark:text-slate-200 truncate max-w-[180px]">
                                    {{ $reg->producto->nombre ?? '—' }}
                                </p>
                                @if($reg->producto?->principio_activo)
                                <p class="text-xs text-slate-400">{{ $reg->producto->principio_activo }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $colors = [1=>'amber', 2=>'orange', 3=>'rose'];
                                    $labels = [1=>'Nivel I', 2=>'Nivel II', 3=>'Nivel III'];
                                    $c = $colors[$reg->nivel_controlado] ?? 'slate';
                                    $l = $labels[$reg->nivel_controlado] ?? '—';
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-{{ $c }}-100 dark:bg-{{ $c }}-900/40 text-{{ $c }}-700 dark:text-{{ $c }}-400">
                                    {{ $l }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-medium text-slate-800 dark:text-slate-200">{{ $reg->paciente_nombre }}</p>
                                @if($reg->paciente_cedula)
                                <p class="text-xs text-slate-400">Cédula: {{ $reg->paciente_cedula }}</p>
                                @endif
                                @if($reg->paciente_edad)
                                <p class="text-xs text-slate-400">{{ $reg->paciente_edad }} años</p>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-medium text-slate-800 dark:text-slate-200">{{ $reg->medico_nombre }}</p>
                                @if($reg->medico_num_registro)
                                <p class="text-xs text-slate-400">Reg. MINSA: {{ $reg->medico_num_registro }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-700 dark:text-slate-300">
                                {{ number_format($reg->cantidad, 0) }} {{ $reg->unidad }}
                            </td>
                            <td class="px-4 py-3">
                                @if($reg->venta)
                                <a href="{{ route('ventas.show', $reg->venta_id) }}"
                                   class="text-emerald-600 dark:text-emerald-400 hover:underline font-medium text-xs">
                                    #{{ $reg->venta_id }}
                                </a>
                                @else
                                <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-slate-500">
                                {{ $reg->despachador->name ?? '—' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Paginación --}}
            @if($registros->hasPages())
            <div class="px-4 py-3 border-t border-slate-100 dark:border-slate-800">
                {{ $registros->links() }}
            </div>
            @endif
        @endif
    </div>
</div>
@endsection
