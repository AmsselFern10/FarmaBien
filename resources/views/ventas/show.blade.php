@extends('layouts.app')

@section('title', 'Detalle de Venta')

@section('header')
    Detalle de Venta
@endsection

@section('page-actions')
    <div>
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white">
            Venta #{{ $venta->id }}
        </h2>
        <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
            {{ optional($venta->fecha)->format('d/m/Y H:i') ?? '—' }}
            @if($venta->cliente)
                — {{ $venta->cliente->nombre }}
            @else
                — Público general
            @endif
        </p>
    </div>

    <div class="flex flex-wrap gap-3">
        <a href="{{ route('ventas.index') }}"
           class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-slate-700 dark:text-slate-300 font-semibold rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200 shadow-sm">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Volver
        </a>

        @can('anular ventas')
            @if($venta->puedeModificarse())
                <a href="{{ route('ventas.edit', $venta) }}"
                   class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-yellow-500 to-yellow-600 hover:from-yellow-600 hover:to-yellow-700 text-white font-semibold rounded-lg shadow-sm transition-all duration-200 hover:shadow-md hover:scale-105">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Modificar
                </a>

                <button onclick="modalAnular()"
                        class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-semibold rounded-lg shadow-sm transition-all duration-200 hover:shadow-md hover:scale-105">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    Anular
                </button>
            @endif
        @endcan
    </div>
@endsection

@section('content')

    {{-- Alertas de estado / trazabilidad --}}
    @if($venta->estado === 'anulada')
        <div class="mb-6 bg-red-50 dark:bg-red-900/20 border-l-4 border-red-400 dark:border-red-600 p-4 rounded-lg">
            <div class="flex">
                <svg class="h-6 w-6 text-red-600 dark:text-red-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <p class="text-sm font-semibold text-red-800 dark:text-red-300">
                        Venta Anulada — {{ optional($venta->fecha_anulacion)->format('d/m/Y H:i') ?? '—' }}
                    </p>
                    @if($venta->motivo_anulacion)
                        <p class="text-sm text-red-700 dark:text-red-400 mt-1">
                            Motivo: {{ $venta->motivo_anulacion }}
                        </p>
                    @endif
                    @if($venta->anuladoPor)
                        <p class="text-xs text-red-600 dark:text-red-500 mt-1">
                            Anulado por: {{ $venta->anuladoPor->name }}
                        </p>
                    @endif
                </div>
            </div>
        </div>
    @endif

    @if($venta->reemplazada_por)
        <div class="mb-6 bg-purple-50 dark:bg-purple-900/20 border-l-4 border-purple-400 dark:border-purple-600 p-4 rounded-lg">
            <div class="flex">
                <svg class="h-6 w-6 text-purple-600 dark:text-purple-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <p class="text-sm font-semibold text-purple-800 dark:text-purple-300">
                        Venta Modificada — Esta venta fue reemplazada por
                        <a href="{{ route('ventas.show', $venta->reemplazada_por) }}" class="underline hover:text-purple-900 dark:hover:text-purple-200">
                            Venta #{{ $venta->reemplazada_por }}
                        </a>
                    </p>
                </div>
            </div>
        </div>
    @endif

    @if($venta->venta_original_id)
        <div class="mb-6 bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-400 dark:border-blue-600 p-4 rounded-lg">
            <div class="flex">
                <svg class="h-6 w-6 text-blue-600 dark:text-blue-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <p class="text-sm font-semibold text-blue-800 dark:text-blue-300">
                        Esta venta es una modificación de
                        <a href="{{ route('ventas.show', $venta->venta_original_id) }}" class="underline hover:text-blue-900 dark:hover:text-blue-200">
                            Venta #{{ $venta->venta_original_id }}
                        </a>
                    </p>
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Columna principal --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Información general --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-purple-50 to-white dark:from-gray-800 dark:to-gray-800/50">
                    <div class="flex items-center space-x-3">
                        <div class="p-2 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
                            <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-slate-900 dark:text-white">Información de la Venta</h3>
                            <p class="text-base text-slate-500 dark:text-slate-400">Datos generales</p>
                        </div>
                    </div>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Cliente</p>
                            <p class="text-base font-bold text-slate-900 dark:text-white mt-1">
                                {{ $venta->cliente?->nombre ?? 'Público general' }}
                            </p>
                        </div>

                        <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Cajero</p>
                            <p class="text-base font-bold text-slate-900 dark:text-white mt-1">
                                {{ $venta->usuario?->name ?? '—' }}
                            </p>
                        </div>

                        <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Método de pago</p>
                            <p class="text-base font-bold text-slate-900 dark:text-white mt-1">
                                {{ $venta->metodo_pago ?? '—' }}
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                        <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Fecha</p>
                            <p class="text-base font-bold text-slate-900 dark:text-white mt-1">
                                {{ optional($venta->fecha)->format('d/m/Y H:i') ?? '—' }}
                            </p>
                        </div>

                        <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Estado</p>
                            <div class="mt-2">
                                @if($venta->estado === 'completada')
                                    <span class="px-3 py-1.5 inline-flex items-center text-xs font-semibold rounded-full bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300">
                                        <span class="w-2 h-2 bg-green-500 rounded-full mr-2 animate-pulse"></span>
                                        Completada
                                    </span>
                                @else
                                    <span class="px-3 py-1.5 inline-flex items-center text-xs font-semibold rounded-full bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300">
                                        Anulada
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Recetas</p>
                            <p class="text-base font-bold text-slate-900 dark:text-white mt-1">
                                {{ $venta->recetas?->count() ?? 0 }}
                            </p>
                        </div>
                    </div>

                    @if($venta->observaciones)
                        <div class="mt-5 p-4 bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-800/30 rounded-xl">
                            <p class="text-xs font-semibold text-amber-700 dark:text-amber-300 uppercase tracking-wider">Observaciones</p>
                            <p class="text-sm text-amber-800 dark:text-amber-200 mt-1 whitespace-pre-line">{{ $venta->observaciones }}</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Detalle de productos --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Detalle de Productos</h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400">Items vendidos (con trazabilidad por lote)</p>
                        </div>
                        <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                            {{ $venta->detalles->count() }} item(s)
                        </span>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Producto</th>
                                <th class="px-4 py-3 text-center text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Presentación</th>
                                <th class="px-4 py-3 text-center text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Unid/Pres</th>
                                <th class="px-4 py-3 text-center text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Cant. Pres</th>
                                <th class="px-4 py-3 text-center text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Total Unid</th>
                                <th class="px-4 py-3 text-right text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">P. Unit</th>
                                <th class="px-4 py-3 text-right text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Desc</th>
                                <th class="px-6 py-3 text-right text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($venta->detalles as $d)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="font-semibold text-slate-900 dark:text-white">
                                            {{ $d->producto?->nombre ?? '—' }}
                                        </div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                            Lote: <span class="font-semibold">{{ $d->numero_lote ?? $d->lote?->numero_lote ?? '—' }}</span>
                                            @if($d->lote?->fecha_vencimiento)
                                                · Vence: {{ $d->lote->fecha_vencimiento->format('d/m/Y') }}
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-center text-sm text-slate-700 dark:text-slate-200">
                                        {{ $d->tipo_presentacion ?? ($d->presentacion?->nombre ?? 'Unidad') }}
                                    </td>
                                    <td class="px-4 py-4 text-center text-sm text-slate-700 dark:text-slate-200">
                                        {{ (int) ($d->unidades_por_presentacion ?? 1) }}
                                    </td>
                                    <td class="px-4 py-4 text-center text-sm font-semibold text-slate-900 dark:text-white">
                                        {{ (int) ($d->cantidad_presentaciones ?? 0) }}
                                    </td>
                                    <td class="px-4 py-4 text-center text-sm font-semibold text-slate-900 dark:text-white">
                                        {{ (int) ($d->cantidad_unidades_base ?? 0) }}
                                    </td>
                                    <td class="px-4 py-4 text-right text-sm text-slate-700 dark:text-slate-200">
                                        {{ number_format((float) ($d->precio_unitario ?? 0), 2) }}
                                    </td>
                                    <td class="px-4 py-4 text-right text-sm text-slate-700 dark:text-slate-200">
                                        @if(((float)($d->descuento_monto ?? 0)) > 0)
                                            <span class="text-red-600 dark:text-red-400 font-semibold">
                                                -{{ number_format((float) $d->descuento_monto, 2) }}
                                            </span>
                                            <div class="text-[11px] text-slate-500 dark:text-slate-400">({{ number_format((float)($d->descuento_porcentaje ?? 0), 2) }}%)</div>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <span class="text-sm font-bold text-slate-900 dark:text-white">
                                            {{ number_format((float) ($d->subtotal ?? 0), 2) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        {{-- Unidades Totales --}}
        <div class="text-sm text-slate-600 dark:text-slate-300">
            <span class="font-semibold">Total unidades:</span>
            {{ (int) $venta->detalles->sum('cantidad_unidades_base') }}
        </div>

        {{-- Cálculo dinámico del descuento basado en los items de la tabla --}}
        <div class="text-sm text-slate-600 dark:text-slate-300">
            <span class="font-semibold">Descuento total (Items):</span>
            <span class="text-red-600 dark:text-red-400 font-bold">
                {{-- Sumamos el campo descuento_monto de cada fila del detalle --}}
                -{{ number_format((float) $venta->detalles->sum('descuento_monto'), 2) }}
            </span>
        </div>
    </div>
</div>
            </div>

            {{-- Recetas asociadas --}}
            @if($venta->recetas && $venta->recetas->count())
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Recetas asociadas</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Requeridas para medicamentos controlados</p>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($venta->recetas as $r)
                                <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl border border-gray-200 dark:border-gray-700">
                                    <div class="font-semibold text-slate-900 dark:text-white">Receta #{{ $r->id }}</div>
                                    <div class="text-sm text-slate-600 dark:text-slate-300 mt-1">{{ $r->paciente ?? 'Paciente: —' }}</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                        {{ $r->created_at?->format('d/m/Y H:i') ?? '' }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            {{-- Historial de modificaciones (cadena) --}}
            @if(!empty($historial) && $historial->count())
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Historial de modificaciones</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Cadena completa de versiones</p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Versión</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Venta</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Fecha</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Cliente</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Usuario</th>
                                    <th class="px-6 py-3 text-right text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Total</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Estado</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($historial as $h)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                        <td class="px-6 py-4 text-sm font-semibold text-slate-900 dark:text-white">V{{ $h['version'] }}</td>
                                        <td class="px-6 py-4">
                                            <a class="text-purple-700 dark:text-purple-300 font-semibold hover:underline" href="{{ route('ventas.show', $h['id']) }}">
                                                #{{ $h['id'] }}
                                            </a>
                                            @if($h['es_activa'])
                                                <span class="ml-2 px-2 py-1 text-[11px] font-bold rounded-full bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300">ACTIVA</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-sm text-slate-700 dark:text-slate-200">{{ optional($h['fecha'])->format('d/m/Y H:i') }}</td>
                                        <td class="px-6 py-4 text-sm text-slate-700 dark:text-slate-200">{{ $h['cliente'] }}</td>
                                        <td class="px-6 py-4 text-sm text-slate-700 dark:text-slate-200">{{ $h['usuario'] }}</td>
                                        <td class="px-6 py-4 text-right text-sm font-bold text-slate-900 dark:text-white">{{ number_format((float)($h['total'] ?? 0), 2) }}</td>
                                        <td class="px-6 py-4">
                                            @if(($h['estado'] ?? '') === 'completada')
                                                <span class="px-3 py-1.5 inline-flex items-center text-xs font-semibold rounded-full bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300">Completada</span>
                                            @else
                                                <span class="px-3 py-1.5 inline-flex items-center text-xs font-semibold rounded-full bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300">Anulada</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

        </div>

        {{-- Sidebar --}}
       <div class="space-y-6">
    {{-- Resumen Financiero Estilo Blue Premium --}}
    <div class="relative bg-gradient-to-br from-blue-600 to-blue-700 dark:from-blue-700 dark:to-blue-800 rounded-xl shadow-lg overflow-hidden">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-white uppercase tracking-wider text-blue-100">Resumen de Venta</h3>
                <div class="p-2 bg-white/10 rounded-lg">
                    <svg class="w-6 h-6 text-blue-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                </div>
            </div>
            
            @php
                $subtotalBruto = (float)($venta->subtotal_bruto ?? 0);
                $descItems = (float)$venta->detalles->sum('descuento_monto'); // Descuento por cada producto
                $descGlobal = (float)($venta->descuento_monto_total ?? 0) - $descItems; // El resto es el global
                $totalFinal = (float)($venta->total ?? 0);
            @endphp

            <div class="space-y-4">
                {{-- Subtotal --}}
                <div class="flex justify-between items-center text-white/90">
                    <span class="text-sm font-medium">Subtotal Bruto:</span>
                    <span class="text-base font-bold text-white">S/ {{ number_format($subtotalBruto, 2) }}</span>
                </div>

                {{-- Desglose de Descuentos --}}
                <div class="space-y-2 py-3 border-y border-white/10">
                    <div class="flex justify-between items-center text-blue-100">
                        <span class="text-xs">Descuento por Items:</span>
                        <span class="text-sm font-semibold">- S/ {{ number_format($descItems, 2) }}</span>
                    </div>
                    
                    @if($descGlobal > 0.01)
                        <div class="flex justify-between items-center text-blue-100">
                            <span class="text-xs">Descuento Global ({{ number_format((float)$venta->descuento_porcentaje, 1) }}%):</span>
                            <span class="text-sm font-semibold">- S/ {{ number_format($descGlobal, 2) }}</span>
                        </div>
                    @endif
                </div>

                {{-- Descuento Total (Lo que se ahorró realmente) --}}
                <div class="flex justify-between items-center">
                    <span class="text-sm font-bold text-orange-200 uppercase">Ahorro Total:</span>
                    <span class="text-base font-black text-orange-300">
                        - S/ {{ number_format((float)($venta->descuento_monto_total ?? 0), 2) }}
                    </span>
                </div>

                {{-- Total Final --}}
                <div class="pt-2 flex justify-between items-end text-white">
                    <span class="text-base font-bold uppercase tracking-tight">Total a Cobrar:</span>
                    <div class="text-right">
                        <span class="block text-4xl font-black leading-none">S/ {{ number_format($totalFinal, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
        {{-- Decoración fondo --}}
        <div class="absolute -bottom-6 -right-6 w-24 h-24 bg-white/5 rounded-full"></div>
    </div>

    {{-- Pago / caja (Se mantiene igual pero con toques de tu estilo) --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-700/30">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-widest">Información de Pago</h3>
        </div>
        <div class="p-6 space-y-3">
            <div class="flex justify-between items-center">
                <span class="text-sm text-slate-500">Método de Pago:</span>
                <span class="px-2 py-1 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 text-xs font-bold rounded-md">
                    {{ strtoupper($venta->metodo_pago ?? '—') }}
                </span>
            </div>

            @php
                $esEfectivo = \Illuminate\Support\Str::of((string)($venta->metodo_pago ?? ''))->lower()->contains('efectivo');
            @endphp

            @if($esEfectivo)
                <div class="grid grid-cols-2 gap-4 pt-2">
                    <div class="p-3 bg-slate-50 dark:bg-gray-700/50 rounded-lg border border-slate-100 dark:border-gray-600 text-center">
                        <span class="text-[10px] text-slate-400 block uppercase font-bold">Recibido</span>
                        <span class="text-sm font-bold text-slate-900 dark:text-white">S/ {{ number_format((float)$venta->monto_recibido, 2) }}</span>
                    </div>
                    <div class="p-3 bg-slate-50 dark:bg-gray-700/50 rounded-lg border border-slate-100 dark:border-gray-600 text-center">
                        <span class="text-[10px] text-slate-400 block uppercase font-bold">Cambio</span>
                        <span class="text-sm font-bold text-blue-600 dark:text-blue-400">S/ {{ number_format((float)$venta->cambio, 2) }}</span>
                    </div>
                </div>
            @endif

            @if(!empty($venta->referencia_pago))
                <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                    <p class="text-[10px] font-bold text-blue-600 dark:text-blue-400 uppercase tracking-tighter">Referencia de Operación</p>
                    <p class="text-sm font-mono font-medium text-slate-700 dark:text-slate-200 mt-1 break-words">{{ $venta->referencia_pago }}</p>
                </div>
            @endif
        </div>
    </div>

            {{-- Estadísticas --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Estadísticas</h3>

                    <div class="space-y-4">
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm text-slate-600 dark:text-slate-400">Productos</span>
                                <span class="font-bold text-slate-900 dark:text-white">{{ $venta->detalles->count() }}</span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div class="bg-purple-600 h-2 rounded-full" style="width: 100%"></div>
                            </div>
                        </div>

                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm text-slate-600 dark:text-slate-400">Unidades totales</span>
                                <span class="font-bold text-slate-900 dark:text-white">{{ (int) $venta->detalles->sum('cantidad_unidades_base') }}</span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div class="bg-green-600 h-2 rounded-full" style="width: 100%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Acciones de impresión --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Acciones de Impresión</h3>

                    <div class="space-y-2">
                        {{-- Ticket POS (siempre) --}}
                        <a href="{{ route('ventas.ticket', $venta) }}"
                           target="_blank"
                           class="w-full inline-flex justify-center items-center px-4 py-3 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-semibold rounded-lg transition-all duration-200 hover:shadow-md">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z"></path>
                            </svg>
                            Imprimir Ticket (POS)
                        </a>

                        {{-- Imprimir A4 (si existe la ruta) --}}
                        @if (\Illuminate\Support\Facades\Route::has('ventas.imprimir'))
                            <a href="{{ route('ventas.imprimir', $venta) }}"
                               target="_blank"
                               class="w-full inline-flex justify-center items-center px-4 py-3 bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white font-semibold rounded-lg transition-all duration-200 hover:shadow-md">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                </svg>
                                Imprimir A4
                            </a>
                        @endif

                        {{-- Descargar PDF (si existe la ruta) --}}
                        @if (\Illuminate\Support\Facades\Route::has('ventas.pdf'))
                            <a href="{{ route('ventas.pdf', $venta) }}"
                               class="w-full inline-flex justify-center items-center px-4 py-3 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-semibold rounded-lg transition-all duration-200 hover:shadow-md">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Descargar PDF
                            </a>
                        @endif

                        <div class="border-t border-gray-200 dark:border-gray-700 my-3"></div>

                        <a href="{{ route('ventas.index') }}"
                           class="w-full inline-flex justify-center items-center px-4 py-3 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-slate-800 dark:text-slate-200 font-semibold rounded-lg transition-colors duration-200">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                            </svg>
                            Ver Todas las Ventas
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- Modal anular (igual estilo que compras) --}}
    <div id="modalAnular" class="hidden fixed z-50 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 dark:bg-gray-900 bg-opacity-75 dark:bg-opacity-80 transition-opacity" onclick="cerrarModalAnular()"></div>

            <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center space-x-3">
                        <div class="p-2 bg-red-100 dark:bg-red-900/30 rounded-lg">
                            <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Anular Venta #{{ $venta->id }}</h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400">Esta acción no se puede deshacer</p>
                        </div>
                    </div>
                </div>

                <form action="{{ route('ventas.anular', $venta) }}" method="POST">
                    @csrf

                    <div class="p-6">
                        <label class="block text-sm font-medium text-slate-900 dark:text-white mb-2">
                            Motivo de Anulación <span class="text-red-500">*</span>
                        </label>
                        <textarea name="motivo"
                                  rows="4"
                                  required
                                  class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                  placeholder="Ingrese el motivo de la anulación..."></textarea>
                    </div>

                    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 flex justify-end gap-3">
                        <button type="button"
                                onclick="cerrarModalAnular()"
                                class="px-6 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-slate-700 dark:text-slate-300 font-semibold rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors duration-200">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="px-6 py-2.5 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-semibold rounded-lg shadow-sm transition-all duration-200 hover:shadow-md">
                            Anular Venta
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function modalAnular() {
            const el = document.getElementById('modalAnular');
            if (!el) return;
            el.classList.remove('hidden');
        }

        function cerrarModalAnular() {
            const el = document.getElementById('modalAnular');
            if (!el) return;
            el.classList.add('hidden');
        }

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                cerrarModalAnular();
            }
        });
    </script>

@endsection
