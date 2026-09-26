@extends('layouts.app')

@section('title', 'Recetas Médicas - FarmaBien')

@section('content')
<div class="space-y-5">
    <!-- Breadcrumb -->
    <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-800 dark:text-slate-200 font-semibold">Recetas Médicas</span>
    </nav>

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">Recetas Médicas</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Registro y control de recetas médicas y dispensación de medicamentos con Rx.</p>
        </div>
        @can('registrar recetas')
        <a href="{{ route('recetas.create') }}"
           class="inline-flex items-center space-x-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-sm transition shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Nueva Receta</span>
        </a>
        @endcan
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Total Recetas</p>
                <p class="text-xl font-bold text-slate-900 dark:text-white mt-0.5">{{ $recetas->total() }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">En esta Página</p>
                <p class="text-xl font-bold text-emerald-600 dark:text-emerald-400 mt-0.5">{{ $recetas->count() }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 flex items-center justify-center">
                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Página</p>
                <p class="text-xl font-bold text-slate-800 dark:text-slate-200 mt-0.5">{{ $recetas->currentPage() }} / {{ $recetas->lastPage() }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Acceso Rápido</p>
                <a href="{{ route('ventas.create') }}" class="text-xs font-bold text-emerald-600 dark:text-emerald-400 hover:underline mt-0.5 block">Ir al POS &rarr;</a>
            </div>
            <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/60 flex items-center justify-center">
                <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm">
        <form method="GET" action="{{ route('recetas.index') }}" class="flex flex-col gap-3">
            <div class="flex flex-col md:flex-row gap-3">
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" name="buscar" value="{{ request('buscar') }}"
                           placeholder="Buscar por N° receta, paciente o médico..."
                           class="w-full pl-10 pr-4 py-2 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 transition">
                </div>
                <select name="estado" onchange="this.form.submit()" class="w-full md:w-48 px-3 py-2 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 transition">
                    <option value="">Estado: Todos</option>
                    <option value="pendiente" {{ request('estado') === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                    <option value="dispensada_parcial" {{ request('estado') === 'dispensada_parcial' ? 'selected' : '' }}>Dispensada Parcial</option>
                    <option value="dispensada_total" {{ request('estado') === 'dispensada_total' ? 'selected' : '' }}>Dispensada Total</option>
                    <option value="anulada" {{ request('estado') === 'anulada' ? 'selected' : '' }}>Anulada</option>
                </select>
                <select name="tipo_receta" onchange="this.form.submit()" class="w-full md:w-40 px-3 py-2 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 transition">
                    <option value="">Tipo: Todos</option>
                    <option value="simple" {{ request('tipo_receta') === 'simple' ? 'selected' : '' }}>Simple</option>
                    <option value="retenida" {{ request('tipo_receta') === 'retenida' ? 'selected' : '' }}>Retenida</option>
                </select>
            </div>
            <div class="flex flex-col md:flex-row gap-3 items-center">
                <div class="flex items-center gap-2 flex-1">
                    <span class="text-xs text-slate-500 shrink-0">Desde:</span>
                    <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}" class="flex-1 px-3 py-2 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 transition">
                    <span class="text-xs text-slate-500 shrink-0">Hasta:</span>
                    <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}" class="flex-1 px-3 py-2 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 transition">
                </div>
                <div class="flex items-center space-x-2 shrink-0">
                    <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 dark:bg-slate-700 dark:hover:bg-slate-600 text-white text-xs font-semibold rounded-xl shadow-2xs transition inline-flex items-center justify-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                        <span>Filtrar</span>
                    </button>
                    @if(request()->anyFilled(['buscar','estado','tipo_receta','fecha_desde','fecha_hasta']))
                    <a href="{{ route('recetas.index') }}" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 text-xs font-semibold rounded-xl transition inline-flex items-center justify-center gap-1">
                        <span>Limpiar</span>
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    @if(session('success'))
    <div class="px-4 py-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-300 dark:border-emerald-700 text-xs text-emerald-800 dark:text-emerald-300 font-semibold">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="px-4 py-3 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-300 dark:border-rose-700 text-xs text-rose-800 dark:text-rose-300 font-semibold">{{ session('error') }}</div>
    @endif

    <!-- Table -->
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800 uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="px-5 py-3.5">N° Receta</th>
                        <th class="px-5 py-3.5">Paciente</th>
                        <th class="px-5 py-3.5">Médico</th>
                        <th class="px-5 py-3.5 text-center">Tipo</th>
                        <th class="px-5 py-3.5 text-center">Estado</th>
                        <th class="px-5 py-3.5 text-center">Meds.</th>
                        <th class="px-5 py-3.5">Emisión</th>
                        <th class="px-5 py-3.5">Vencimiento</th>
                        <th class="px-5 py-3.5 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-700 dark:text-slate-300">
                    @forelse($recetas as $rec)
                    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition">
                        <td class="px-5 py-3.5">
                            <span class="font-mono font-bold text-slate-900 dark:text-white">{{ $rec->numero_receta }}</span>
                            @if($rec->tipo_receta === 'retenida')
                            <span class="ml-1 px-1.5 py-0.5 rounded text-[9px] font-black bg-rose-100 dark:bg-rose-950/60 text-rose-700">RET</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="font-semibold text-slate-900 dark:text-white">{{ $rec->paciente_nombre }}</div>
                            @if($rec->cliente)
                            <div class="text-[10px] text-slate-400">{{ $rec->cliente->nombre }}</div>
                            @endif
                        </td>
                        <td class="px-5 py-3.5">
                            <div>{{ $rec->medico_nombre }}</div>
                            <div class="text-[10px] text-slate-400">CMP: {{ $rec->medico_colegiatura }}</div>
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            @if($rec->tipo_receta === 'retenida')
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800">Retenida</span>
                            @else
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700">Simple</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            @php
                                $estadoClases = [
                                    'pendiente'          => 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800',
                                    'dispensada_parcial' => 'bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border-blue-200 dark:border-blue-800',
                                    'dispensada_total'   => 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800',
                                    'anulada'            => 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800',
                                ];
                                $estadoLabels = [
                                    'pendiente'          => 'Pendiente',
                                    'dispensada_parcial' => 'Parcial',
                                    'dispensada_total'   => 'Completada',
                                    'anulada'            => 'Anulada',
                                ];
                            @endphp
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold border {{ $estadoClases[$rec->estado] ?? 'bg-slate-100 text-slate-600 border-slate-200' }}">
                                {{ $estadoLabels[$rec->estado] ?? $rec->estado }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">{{ $rec->detalles_count }}</span>
                        </td>
                        <td class="px-5 py-3.5 text-slate-600 dark:text-slate-400">
                            {{ $rec->fecha_emision ? $rec->fecha_emision->format('d/m/Y') : '—' }}
                        </td>
                        <td class="px-5 py-3.5">
                            @if($rec->fecha_vencimiento)
                            <span class="{{ $rec->estaVencida() ? 'text-rose-600 dark:text-rose-400 font-semibold' : 'text-slate-600 dark:text-slate-400' }}">
                                {{ $rec->fecha_vencimiento->format('d/m/Y') }}
                            </span>
                            @if($rec->estaVencida())
                            <span class="ml-1 text-[9px] font-black text-rose-600">VENCIDA</span>
                            @endif
                            @else
                            <span class="text-slate-400">Sin venc.</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-center whitespace-nowrap">
                            <div class="inline-flex items-center justify-center gap-1.5">
                                @can('ver recetas')
                                <a href="{{ route('recetas.show', $rec) }}"
                                   class="w-8 h-8 rounded-lg bg-sky-50 dark:bg-sky-950/60 text-sky-700 dark:text-sky-300 hover:bg-sky-100 dark:hover:bg-sky-900/60 border border-sky-200 dark:border-sky-800/60 inline-flex items-center justify-center transition shadow-2xs" title="Ver">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                @endcan
                                @can('registrar recetas')
                                @if($rec->estado !== 'anulada')
                                <a href="{{ route('recetas.edit', $rec) }}"
                                   class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 border border-emerald-200 dark:border-emerald-800/60 inline-flex items-center justify-center transition shadow-2xs" title="Editar">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                @endif
                                @if($rec->estado === 'pendiente')
                                <form method="POST" action="{{ route('recetas.destroy', $rec) }}" class="inline-flex m-0 p-0"
                                      onsubmit="return confirm('¿Eliminar la receta #{{ $rec->numero_receta }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-8 h-8 rounded-lg bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 hover:bg-rose-100 dark:hover:bg-rose-900/60 border border-rose-200 dark:border-rose-800/60 inline-flex items-center justify-center transition shadow-2xs" title="Eliminar">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                                @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="px-5 py-12 text-center text-slate-400 text-xs">No se encontraron recetas médicas con los filtros aplicados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($recetas->hasPages())
        <div class="px-5 py-3.5 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900">{{ $recetas->links() }}</div>
        @endif
    </div>
</div>
@endsection
