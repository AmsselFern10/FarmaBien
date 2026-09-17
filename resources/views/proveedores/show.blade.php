@extends('layouts.app')

@section('title', 'Ficha de Proveedor - FarmaBien')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <!-- Breadcrumb & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('proveedores.index') }}" class="hover:text-emerald-600 transition">Proveedores</a>
                <span>/</span>
                <span class="text-slate-800 dark:text-slate-300 font-medium">Ficha Comercial</span>
            </div>
            <div class="flex items-center space-x-3">
                <h1 class="text-xl font-bold text-slate-900 dark:text-white">{{ $proveedor->nombre }}</h1>
                @if($proveedor->activo)
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
            @can('editar proveedores')
            <a href="{{ route('proveedores.edit', $proveedor) }}" 
               class="inline-flex items-center space-x-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-semibold rounded-xl transition shadow-sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Editar</span>
            </a>
            @endcan
            <a href="{{ route('proveedores.index') }}" 
               class="px-3.5 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-xl transition">
                &larr; Volver
            </a>
        </div>
    </div>

    <!-- Highlight Metrics Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm">
            <p class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">RUC Tributario</p>
            <p class="text-base font-mono font-bold text-slate-900 dark:text-white mt-1">{{ $proveedor->ruc }}</p>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm">
            <p class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">Contacto Comercial</p>
            <p class="text-xs font-bold text-slate-900 dark:text-white mt-1 truncate">{{ $proveedor->contacto ?? 'Sin asignar' }}</p>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm">
            <p class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">Teléfono / Pedidos</p>
            <p class="text-xs font-bold text-slate-900 dark:text-white mt-1 truncate">{{ $proveedor->telefono ?? 'Sin teléfono' }}</p>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm">
            <p class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">Historial de Compras</p>
            <p class="text-base font-bold text-purple-600 dark:text-purple-400 mt-1">{{ $proveedor->compras->count() }} registradas</p>
        </div>
    </div>

    <!-- Address & Fiscal Banner -->
    <div class="bg-white dark:bg-slate-900 rounded-xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <p class="text-[11px] font-medium text-slate-400 uppercase">Dirección Fiscal / Almacén Central</p>
            <p class="text-xs sm:text-sm text-slate-800 dark:text-slate-200 font-medium mt-0.5">{{ $proveedor->direccion ?? 'Sin dirección registrada' }}</p>
        </div>
        <div>
            <p class="text-[11px] font-medium text-slate-400 uppercase">Correo Electrónico de Facturación</p>
            <p class="text-xs sm:text-sm text-slate-800 dark:text-slate-200 font-medium mt-0.5">{{ $proveedor->email ?? 'Sin correo registrado' }}</p>
        </div>
    </div>

    <!-- Purchases History Section -->
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                    Órdenes de Compra y Recepción de Lotes
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Últimas 10 operaciones de compra realizadas a este proveedor.</p>
            </div>
            @can('crear compras')
            <a href="{{ route('compras.create') }}" class="text-xs font-bold text-emerald-600 dark:text-emerald-400 hover:underline">
                + Nueva Compra (F4)
            </a>
            @endcan
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3">Comprobante</th>
                        <th class="px-5 py-3">Fecha de Emisión</th>
                        <th class="px-5 py-3 text-right">Subtotal</th>
                        <th class="px-5 py-3 text-right">Monto Total</th>
                        <th class="px-5 py-3 text-center">Estado</th>
                        <th class="px-5 py-3 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-700 dark:text-slate-300">
                    @forelse($proveedor->compras as $compra)
                    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition">
                        <td class="px-5 py-3.5 font-mono font-bold text-slate-900 dark:text-white">
                            {{ $compra->numero_comprobante }}
                        </td>
                        <td class="px-5 py-3.5 text-slate-500 dark:text-slate-400">
                            {{ $compra->fecha ? $compra->fecha->format('d/m/Y H:i') : '—' }}
                        </td>
                        <td class="px-5 py-3.5 text-right font-mono text-slate-600 dark:text-slate-300">
                            ${{ number_format($compra->subtotal, 2) }}
                        </td>
                        <td class="px-5 py-3.5 text-right font-mono font-bold text-slate-900 dark:text-white">
                            ${{ number_format($compra->total, 2) }}
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            @if($compra->estado === 'recibida')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                Recibida
                            </span>
                            @elseif($compra->estado === 'anulada')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                Anulada
                            </span>
                            @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                {{ ucfirst($compra->estado) }}
                            </span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            @can('ver compras')
                            <a href="{{ route('compras.show', $compra) }}" class="text-emerald-600 dark:text-emerald-400 hover:underline font-semibold">Ver Detalle &rarr;</a>
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-8 text-center text-slate-400">
                            No se registran compras previas a este proveedor.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
