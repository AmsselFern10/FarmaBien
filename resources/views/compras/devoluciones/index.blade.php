@extends('layouts.app')
@section('title', 'Devoluciones a Proveedor - FarmaBien')
@section('content')
<div class="space-y-5">

    {{-- Breadcrumb --}}
    <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-800 dark:text-slate-200 font-semibold">Devoluciones a Proveedor</span>
    </nav>

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">Devoluciones a Proveedor</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Registra devoluciones de lotes a proveedores. El stock se descuenta automáticamente y los controlados MINSA quedan en el libro de controlados como ajuste de egreso.
            </p>
        </div>
        <a href="{{ route('compras.devoluciones.create') }}"
           class="inline-flex items-center space-x-2 px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-xl shadow-sm transition shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
            <span>Nueva Devolución</span>
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

    {{-- Tabla --}}
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">N° Devolución</th>
                        <th class="px-5 py-3.5">Proveedor</th>
                        <th class="px-5 py-3.5 text-center">Estado</th>
                        <th class="px-5 py-3.5 text-right">Total C\$</th>
                        <th class="px-5 py-3.5">Responsable</th>
                        <th class="px-5 py-3.5">Fecha</th>
                        <th class="px-5 py-3.5 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-700 dark:text-slate-300">
                    @forelse($devoluciones as $dev)
                    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition">
                        <td class="px-5 py-3.5 font-mono text-slate-800 dark:text-slate-200 font-semibold">
                            {{ $dev->numero_devolucion }}
                        </td>
                        <td class="px-5 py-3.5">{{ $dev->proveedor->nombre ?? '—' }}</td>
                        <td class="px-5 py-3.5 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold border {{ $dev->estadoBadge }}">
                                {{ ucfirst($dev->estado) }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-right font-mono font-bold text-slate-800 dark:text-slate-200">
                            C\$ {{ number_format($dev->total_devolucion, 2) }}
                        </td>
                        <td class="px-5 py-3.5 text-slate-500 dark:text-slate-400">{{ $dev->usuario->name ?? '—' }}</td>
                        <td class="px-5 py-3.5 text-slate-500 dark:text-slate-400">{{ $dev->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-5 py-3.5 text-right">
                            <a href="{{ route('compras.devoluciones.show', $dev) }}"
                               class="inline-flex items-center gap-1 px-3 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-[11px] font-semibold rounded-lg transition">
                                Ver
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center text-slate-400 text-xs">
                            No hay devoluciones a proveedor registradas.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($devoluciones->hasPages())
        <div class="px-5 py-3.5 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900">
            {{ $devoluciones->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
