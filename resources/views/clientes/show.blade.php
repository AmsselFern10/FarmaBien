@extends('layouts.app')

@section('title', 'Ficha del Paciente - FarmaBien')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <!-- Breadcrumb & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('clientes.index') }}" class="hover:text-emerald-600 transition">Clientes / Pacientes</a>
                <span>/</span>
                <span class="text-slate-800 dark:text-slate-300 font-medium">Ficha del Paciente</span>
            </div>
            <div class="flex items-center space-x-3">
                <h1 class="text-xl font-bold text-slate-900 dark:text-white">{{ $cliente->nombre }}</h1>
                @if($cliente->activo)
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
            @can('editar clientes')
            <a href="{{ route('clientes.edit', $cliente) }}" 
               class="inline-flex items-center space-x-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-semibold rounded-xl transition shadow-sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Editar</span>
            </a>
            @endcan
            <a href="{{ route('clientes.index') }}" 
               class="px-3.5 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-xl transition">
                &larr; Volver
            </a>
        </div>
    </div>

    <!-- Highlight Metrics Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm">
            <p class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">Documento</p>
            <p class="text-base font-mono font-bold text-slate-900 dark:text-white mt-1">{{ $cliente->documento ?? 'Sin documento' }}</p>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm">
            <p class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">Teléfono / Celular</p>
            <p class="text-xs font-bold text-slate-900 dark:text-white mt-1 truncate">{{ $cliente->telefono ?? 'Sin teléfono' }}</p>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm">
            <p class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">Total Compras</p>
            <p class="text-base font-bold text-emerald-600 dark:text-emerald-400 mt-1">{{ $cliente->ventas->count() }} operaciones</p>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm">
            <p class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">Recetas Vinculadas</p>
            <p class="text-base font-bold text-amber-600 dark:text-amber-400 mt-1">{{ $cliente->recetas->count() }} recetas</p>
        </div>
    </div>

    <!-- Address & Email Banner -->
    <div class="bg-white dark:bg-slate-900 rounded-xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <p class="text-[11px] font-medium text-slate-400 uppercase">Dirección Domiciliaria</p>
            <p class="text-xs sm:text-sm text-slate-800 dark:text-slate-200 font-medium mt-0.5">{{ $cliente->direccion ?? 'Sin dirección registrada' }}</p>
        </div>
        <div>
            <p class="text-[11px] font-medium text-slate-400 uppercase">Correo Electrónico</p>
            <p class="text-xs sm:text-sm text-slate-800 dark:text-slate-200 font-medium mt-0.5">{{ $cliente->email ?? 'Sin correo registrado' }}</p>
        </div>
    </div>

    <!-- Dual Tab / Columns: Historial de Compras & Historial de Recetas Médicas -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Sales History -->
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden flex flex-col">
            <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                        Últimas Compras Realizadas
                    </h3>
                    <p class="text-xs text-slate-400">Historial reciente de transacciones</p>
                </div>
                @can('realizar ventas')
                <a href="{{ route('ventas.create') }}" class="text-xs font-bold text-emerald-600 dark:text-emerald-400 hover:underline">
                    + Nueva Venta (F2)
                </a>
                @endcan
            </div>
            <div class="overflow-x-auto flex-1">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800 uppercase tracking-wider">
                        <tr>
                            <th class="px-4 py-2.5">Ticket</th>
                            <th class="px-4 py-2.5">Fecha</th>
                            <th class="px-4 py-2.5 text-right">Total</th>
                            <th class="px-4 py-2.5 text-center">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-700 dark:text-slate-300">
                        @forelse($cliente->ventas as $venta)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition">
                            <td class="px-4 py-3 font-mono font-bold text-slate-900 dark:text-white">
                                <a href="{{ route('ventas.show', $venta) }}" class="hover:text-emerald-600 transition">
                                    {{ $venta->numero_comprobante }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-slate-500 dark:text-slate-400 text-[11px]">
                                {{ $venta->fecha ? $venta->fecha->format('d/m/Y H:i') : '—' }}
                            </td>
                            <td class="px-4 py-3 text-right font-mono font-bold text-slate-900 dark:text-white">
                                ${{ number_format($venta->total, 2) }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($venta->estado === 'completada')
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300">
                                    Completada
                                </span>
                                @elseif($venta->estado === 'anulada')
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300">
                                    Anulada
                                </span>
                                @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                                    {{ ucfirst($venta->estado) }}
                                </span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-slate-400 text-xs">
                                Sin compras registradas.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Prescriptions History -->
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden flex flex-col">
            <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                        Historial de Recetas Médicas
                    </h3>
                    <p class="text-xs text-slate-400">Recetas archivadas y dispensaciones</p>
                </div>
                @can('crear recetas')
                <a href="{{ route('recetas.create') }}" class="text-xs font-bold text-emerald-600 dark:text-emerald-400 hover:underline">
                    + Nueva Receta
                </a>
                @endcan
            </div>
            <div class="overflow-x-auto flex-1">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800 uppercase tracking-wider">
                        <tr>
                            <th class="px-4 py-2.5">Receta #</th>
                            <th class="px-4 py-2.5">Médico / C.M.P.</th>
                            <th class="px-4 py-2.5 text-center">Tipo</th>
                            <th class="px-4 py-2.5 text-center">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-700 dark:text-slate-300">
                        @forelse($cliente->recetas as $receta)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition">
                            <td class="px-4 py-3 font-mono font-bold text-slate-900 dark:text-white">
                                <a href="{{ route('recetas.show', $receta) }}" class="hover:text-emerald-600 transition">
                                    {{ $receta->numero_receta ?? '#' . $receta->id }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-slate-700 dark:text-slate-300">
                                <span class="font-medium">{{ $receta->medico_nombre }}</span>
                                <span class="block text-[10px] font-mono text-slate-400">CMP: {{ $receta->medico_colegiatura ?? 'S/C' }}</span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($receta->tipo_receta === 'retenida')
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                    Retenida
                                </span>
                                @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                                    Simple
                                </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($receta->estado === 'dispensada_total')
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300">
                                    Dispensada
                                </span>
                                @elseif($receta->estado === 'anulada')
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300">
                                    Anulada
                                </span>
                                @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300">
                                    {{ ucfirst(str_replace('_', ' ', $receta->estado)) }}
                                </span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-slate-400 text-xs">
                                Sin recetas asociadas registradas.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
