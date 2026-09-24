@extends('layouts.app')

@section('title', $proveedor->nombre . ' - Ficha de Proveedor - FarmaBien')

@section('content')
<div class="space-y-5">
    <!-- Breadcrumb -->
    <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('proveedores.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Proveedores</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-800 dark:text-slate-200 font-semibold truncate">{{ $proveedor->nombre }}</span>
    </nav>

    <!-- Header & Quick Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <div class="flex items-center space-x-3">
                <h1 class="text-xl font-bold text-slate-900 dark:text-white">{{ $proveedor->nombre }}</h1>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold {{ $proveedor->activo ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-700' }}">
                    {{ $proveedor->activo ? 'Activo' : 'Inactivo' }}
                </span>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Droguería mayorista y proveedor autorizado de abastecimiento farmacéutico.
            </p>
        </div>

        <div class="flex items-center space-x-2 shrink-0">
            <a href="{{ route('proveedores.index') }}" 
               class="px-3.5 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold transition flex items-center space-x-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Volver al Directorio</span>
            </a>

            @can('editar proveedores')
            <a href="{{ route('proveedores.edit', $proveedor) }}" 
               class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-semibold shadow-xs transition flex items-center space-x-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Editar Proveedor</span>
            </a>
            @endcan
        </div>
    </div>

    <!-- Highlight Metrics Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- RUC con Copia Rápida -->
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs"
             x-data="{ copied: false }">
            <div class="flex items-center justify-between">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">RUC Tributario (11 Dígitos)</p>
                @if($proveedor->ruc)
                <button type="button" 
                        @click="navigator.clipboard.writeText('{{ $proveedor->ruc }}'); copied = true; setTimeout(() => copied = false, 2000)" 
                        class="text-[10px] font-semibold text-slate-500 hover:text-emerald-600 dark:text-slate-400 dark:hover:text-emerald-400">
                    <span x-show="!copied">📋 Copiar</span>
                    <span x-show="copied" class="text-emerald-600">✓ Listo</span>
                </button>
                @endif
            </div>
            <p class="text-base font-mono font-black text-slate-900 dark:text-white mt-1">{{ $proveedor->ruc }}</p>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs">
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Contacto Comercial</p>
            <p class="text-xs font-bold text-slate-900 dark:text-white mt-1.5 truncate">{{ $proveedor->contacto ?? 'Sin asignar' }}</p>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs">
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Teléfono / Pedidos</p>
            <p class="text-xs font-bold text-slate-900 dark:text-white mt-1.5 truncate">{{ $proveedor->telefono ?? 'Sin teléfono' }}</p>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs">
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Historial de Compras</p>
            <p class="text-base font-black text-purple-600 dark:text-purple-400 mt-1">{{ $proveedor->compras->count() }} registradas</p>
        </div>
    </div>

    <!-- Address & Fiscal Banner -->
    <div class="bg-white dark:bg-slate-900 rounded-xl p-5 border border-slate-200 dark:border-slate-800 shadow-xs grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <p class="text-[11px] font-bold text-slate-400 uppercase">Dirección Fiscal / Almacén Central</p>
            <p class="text-xs sm:text-sm text-slate-800 dark:text-slate-200 font-medium mt-0.5">{{ $proveedor->direccion ?? 'Sin dirección registrada' }}</p>
        </div>
        <div>
            <p class="text-[11px] font-bold text-slate-400 uppercase">Correo Electrónico de Facturación</p>
            <p class="text-xs sm:text-sm text-slate-800 dark:text-slate-200 font-medium mt-0.5">{{ $proveedor->email ?? 'Sin correo registrado' }}</p>
        </div>
    </div>

    <!-- Purchases History Section -->
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <span>Órdenes de Compra y Recepción de Lotes</span>
                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                        {{ $proveedor->compras->count() }}
                    </span>
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Historial de operaciones de compra realizadas a este proveedor.</p>
            </div>
            @can('registrar compras')
            <a href="{{ route('compras.create') }}" class="text-xs font-bold text-emerald-600 dark:text-emerald-400 hover:underline">
                + Nueva Compra (F4)
            </a>
            @endcan
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800 uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="px-5 py-3.5">Comprobante</th>
                        <th class="px-5 py-3.5">Fecha de Emisión</th>
                        <th class="px-5 py-3.5 text-right">Subtotal</th>
                        <th class="px-5 py-3.5 text-right">Monto Total</th>
                        <th class="px-5 py-3.5 text-center">Estado</th>
                        <th class="px-5 py-3.5 text-center">Acciones</th>
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
                            S/ {{ number_format($compra->subtotal, 2) }}
                        </td>
                        <td class="px-5 py-3.5 text-right font-mono font-bold text-slate-900 dark:text-white">
                            S/ {{ number_format($compra->total, 2) }}
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
