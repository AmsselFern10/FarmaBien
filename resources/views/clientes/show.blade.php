@extends('layouts.app')

@section('title', $cliente->nombre . ' - Ficha del Paciente - FarmaBien')

@section('content')
<div class="space-y-5">
    <!-- Breadcrumb -->
    <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('clientes.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Clientes / Pacientes</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-800 dark:text-slate-200 font-semibold truncate">{{ $cliente->nombre }}</span>
    </nav>

    <!-- Header & Quick Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <div class="flex items-center space-x-3">
                <h1 class="text-xl font-bold text-slate-900 dark:text-white">{{ $cliente->nombre }}</h1>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold {{ $cliente->activo ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-700' }}">
                    {{ $cliente->activo ? 'Activo' : 'Inactivo' }}
                </span>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Padrón clínico de pacientes, historial de dispensaciones farmacéuticas y recetas médicas archivadas.
            </p>
        </div>

        <div class="flex items-center space-x-2 shrink-0">
            <a href="{{ route('clientes.index') }}" 
               class="px-3.5 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold transition flex items-center space-x-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Volver al Padrón</span>
            </a>

            @can('editar clientes')
            <a href="{{ route('clientes.edit', $cliente) }}" 
               class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-semibold shadow-xs transition flex items-center space-x-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Editar Paciente</span>
            </a>
            @endcan
        </div>
    </div>

    <!-- Highlight Metrics Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Documento con Copia Rápida -->
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs"
             x-data="{ copied: false }">
            <div class="flex items-center justify-between">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Documento (DNI/RUC)</p>
                @if($cliente->documento)
                <button type="button" 
                        @click="navigator.clipboard.writeText('{{ $cliente->documento }}'); copied = true; setTimeout(() => copied = false, 2000)" 
                        class="text-[10px] font-semibold text-slate-500 hover:text-emerald-600 dark:text-slate-400 dark:hover:text-emerald-400">
                    <span x-show="!copied">📋 Copiar</span>
                    <span x-show="copied" class="text-emerald-600">✓ Listo</span>
                </button>
                @endif
            </div>
            <p class="text-base font-mono font-black text-slate-900 dark:text-white mt-1">{{ $cliente->documento ?? 'SIN DOCUMENTO' }}</p>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs">
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Teléfono / Celular</p>
            <p class="text-xs font-bold text-slate-900 dark:text-white mt-1.5 truncate">{{ $cliente->telefono ?? 'Sin teléfono registrado' }}</p>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs">
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Compras</p>
            <p class="text-base font-black text-emerald-600 dark:text-emerald-400 mt-1">{{ $cliente->ventas->count() }} operaciones</p>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs">
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Recetas Vinculadas</p>
            <p class="text-base font-black text-amber-600 dark:text-amber-400 mt-1">{{ $cliente->recetas->count() }} recetas</p>
        </div>
    </div>

    <!-- Address & Email Banner -->
    <div class="bg-white dark:bg-slate-900 rounded-xl p-5 border border-slate-200 dark:border-slate-800 shadow-xs grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <p class="text-[11px] font-bold text-slate-400 uppercase">Dirección Domiciliaria</p>
            <p class="text-xs sm:text-sm text-slate-800 dark:text-slate-200 font-medium mt-0.5">{{ $cliente->direccion ?? 'Sin dirección registrada' }}</p>
        </div>
        <div>
            <p class="text-[11px] font-bold text-slate-400 uppercase">Correo Electrónico</p>
            <p class="text-xs sm:text-sm text-slate-800 dark:text-slate-200 font-medium mt-0.5">{{ $cliente->email ?? 'Sin correo registrado' }}</p>
        </div>
    </div>

    <!-- Dual Tab / Columns: Historial de Compras & Historial de Recetas Médicas -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Sales History -->
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden flex flex-col">
            <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <span>Últimas Compras Realizadas</span>
                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300">
                            {{ $cliente->ventas->count() }}
                        </span>
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">Historial reciente de transacciones en caja.</p>
                </div>
                @can('realizar ventas')
                <a href="{{ route('ventas.create') }}" class="text-xs font-bold text-emerald-600 dark:text-emerald-400 hover:underline">
                    + Nueva Venta (F2)
                </a>
                @endcan
            </div>
            <div class="overflow-x-auto flex-1">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800 uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="px-4 py-3">Ticket</th>
                            <th class="px-4 py-3">Fecha</th>
                            <th class="px-4 py-3 text-right">Total</th>
                            <th class="px-4 py-3 text-center">Estado</th>
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
                                S/ {{ number_format($venta->total, 2) }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($venta->estado === 'completada')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300">
                                    Completada
                                </span>
                                @elseif($venta->estado === 'anulada')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300">
                                    Anulada
                                </span>
                                @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
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
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden flex flex-col">
            <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <span>Historial de Recetas Médicas</span>
                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300">
                            {{ $cliente->recetas->count() }}
                        </span>
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">Recetas archivadas y control de tratamientos.</p>
                </div>
                @can('crear recetas')
                <a href="{{ route('recetas.create') }}" class="text-xs font-bold text-emerald-600 dark:text-emerald-400 hover:underline">
                    + Nueva Receta
                </a>
                @endcan
            </div>
            <div class="overflow-x-auto flex-1">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800 uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="px-4 py-3">Receta #</th>
                            <th class="px-4 py-3">Médico / C.M.P.</th>
                            <th class="px-4 py-3 text-center">Tipo</th>
                            <th class="px-4 py-3 text-center">Estado</th>
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
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                    Retenida
                                </span>
                                @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                                    Simple
                                </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($receta->estado === 'dispensada_total')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300">
                                    Dispensada
                                </span>
                                @elseif($receta->estado === 'anulada')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300">
                                    Anulada
                                </span>
                                @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300">
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
