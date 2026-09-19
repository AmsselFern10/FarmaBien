@extends('layouts.app')

@section('title', 'Reporte de Ventas e Ingresos - FarmaBien')

@section('content')
<div class="space-y-6">
    <!-- Header with Back & Export Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 print:hidden">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
                <a href="{{ route('reportes.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 flex items-center space-x-1 font-medium">
                    <span>&larr; Centro de Reportes</span>
                </a>
                <span>&bull;</span>
                <span>Ventas e Ingresos</span>
            </div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white mt-1">Reporte de Ventas e Ingresos</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Período: <span class="font-semibold text-slate-700 dark:text-slate-200">{{ \Carbon\Carbon::parse($fechaDesde)->format('d/m/Y') }}</span> al <span class="font-semibold text-slate-700 dark:text-slate-200">{{ \Carbon\Carbon::parse($fechaHasta)->format('d/m/Y') }}</span>
            </p>
        </div>

        <div class="flex items-center space-x-2">
            <!-- Botón Imprimir -->
            <button type="button" 
                    onclick="window.print()" 
                    class="inline-flex items-center space-x-2 px-3 py-2 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold rounded-lg shadow-2xs transition">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Imprimir</span>
            </button>

            <!-- Botón Exportar CSV / Excel -->
            <a href="{{ route('reportes.ventas', array_merge(request()->query(), ['export' => 'csv'])) }}" 
               class="inline-flex items-center space-x-2 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Exportar CSV / Excel</span>
            </a>
        </div>
    </div>

    <!-- Print Header (Visible only when printing) -->
    <div class="hidden print:block border-b border-slate-300 pb-4 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">FarmaBien - Farmacia & Droguería</h1>
                <h2 class="text-base font-semibold text-slate-700 mt-1">Informe Gerencial de Ventas e Ingresos</h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Período: {{ \Carbon\Carbon::parse($fechaDesde)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($fechaHasta)->format('d/m/Y') }} &bull; Generado el {{ now()->format('d/m/Y H:i') }}
                </p>
            </div>
            <div class="text-right">
                <span class="text-xs text-slate-500">Total Liquidado:</span>
                <p class="text-xl font-bold text-slate-900">${{ number_format($totalVendido, 2) }}</p>
            </div>
        </div>
    </div>

    <!-- Filtros Avanzados y Presets -->
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4 shadow-sm print:hidden"
         x-data="{
             setDates(preset) {
                 const now = new Date();
                 let from = new Date();
                 let to = new Date();

                 if (preset === 'hoy') {
                     // today
                 } else if (preset === '7dias') {
                     from.setDate(now.getDate() - 7);
                 } else if (preset === 'este_mes') {
                     from = new Date(now.getFullYear(), now.getMonth(), 1);
                     to = new Date(now.getFullYear(), now.getMonth() + 1, 0);
                 } else if (preset === 'mes_anterior') {
                     from = new Date(now.getFullYear(), now.getMonth() - 1, 1);
                     to = new Date(now.getFullYear(), now.getMonth(), 0);
                 } else if (preset === 'anio') {
                     from = new Date(now.getFullYear(), 0, 1);
                     to = new Date(now.getFullYear(), 11, 31);
                 }

                 const formatDate = (d) => {
                     const year = d.getFullYear();
                     const month = String(d.getMonth() + 1).padStart(2, '0');
                     const day = String(d.getDate()).padStart(2, '0');
                     return `${year}-${month}-${day}`;
                 };

                 document.getElementById('rep_fecha_desde').value = formatDate(from);
                 document.getElementById('rep_fecha_hasta').value = formatDate(to);
                 document.getElementById('form-filtro-ventas').submit();
             }
         }">
        
        <!-- Presets rápidos -->
        <div class="flex items-center flex-wrap gap-1.5 mb-3.5 pb-3 border-b border-slate-100 dark:border-slate-800 text-xs">
            <span class="text-slate-400 font-medium mr-1 text-[11px]">Rango rápido:</span>
            <button type="button" @click="setDates('hoy')" class="px-2.5 py-1 rounded-md bg-slate-100 dark:bg-slate-800 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 text-slate-700 dark:text-slate-300 hover:text-emerald-600 transition font-medium">Hoy</button>
            <button type="button" @click="setDates('7dias')" class="px-2.5 py-1 rounded-md bg-slate-100 dark:bg-slate-800 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 text-slate-700 dark:text-slate-300 hover:text-emerald-600 transition font-medium">Últimos 7 Días</button>
            <button type="button" @click="setDates('este_mes')" class="px-2.5 py-1 rounded-md bg-slate-100 dark:bg-slate-800 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 text-slate-700 dark:text-slate-300 hover:text-emerald-600 transition font-medium">Este Mes</button>
            <button type="button" @click="setDates('mes_anterior')" class="px-2.5 py-1 rounded-md bg-slate-100 dark:bg-slate-800 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 text-slate-700 dark:text-slate-300 hover:text-emerald-600 transition font-medium">Mes Anterior</button>
            <button type="button" @click="setDates('anio')" class="px-2.5 py-1 rounded-md bg-slate-100 dark:bg-slate-800 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 text-slate-700 dark:text-slate-300 hover:text-emerald-600 transition font-medium">Año Actual</button>
        </div>

        <form id="form-filtro-ventas" method="GET" action="{{ route('reportes.ventas') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
            <!-- Fecha Desde -->
            <div>
                <label for="rep_fecha_desde" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                    Fecha Desde
                </label>
                <input type="date" 
                       id="rep_fecha_desde" 
                       name="fecha_desde" 
                       value="{{ $fechaDesde }}" 
                       class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-emerald-500 focus:border-emerald-500">
            </div>

            <!-- Fecha Hasta -->
            <div>
                <label for="rep_fecha_hasta" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                    Fecha Hasta
                </label>
                <input type="date" 
                       id="rep_fecha_hasta" 
                       name="fecha_hasta" 
                       value="{{ $fechaHasta }}" 
                       class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-emerald-500 focus:border-emerald-500">
            </div>

            <!-- Método de Pago -->
            <div>
                <label for="rep_metodo_pago" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                    Método de Pago
                </label>
                <select id="rep_metodo_pago" 
                        name="metodo_pago" 
                        class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="">Todos los métodos</option>
                    <option value="efectivo" {{ $metodoPago === 'efectivo' ? 'selected' : '' }}>Efectivo</option>
                    <option value="tarjeta" {{ $metodoPago === 'tarjeta' ? 'selected' : '' }}>Tarjeta</option>
                    <option value="transferencia" {{ $metodoPago === 'transferencia' ? 'selected' : '' }}>Transferencia</option>
                </select>
            </div>

            <!-- Cajero / Usuario -->
            <div>
                <label for="rep_cajero_id" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                    Cajero / Personal
                </label>
                <select id="rep_cajero_id" 
                        name="cajero_id" 
                        class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="">Todos los cajeros</option>
                    @foreach($cajeros as $caj)
                    <option value="{{ $caj->id }}" {{ (string)$cajeroId === (string)$caj->id ? 'selected' : '' }}>
                        {{ $caj->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <!-- Botones de Acción -->
            <div class="flex items-center space-x-2">
                <button type="submit" 
                        class="flex-1 px-3 py-2 bg-slate-900 hover:bg-slate-800 dark:bg-slate-700 dark:hover:bg-slate-600 text-white text-xs font-semibold rounded-lg transition text-center shadow-xs">
                    Filtrar
                </button>
                <a href="{{ route('reportes.ventas') }}" 
                   title="Restablecer filtros"
                   class="px-3 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 text-xs font-semibold rounded-lg transition">
                    Limpiar
                </a>
            </div>
        </form>
    </div>

    <!-- 4 Tarjetas de Métricas del Período -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Facturado -->
        <div class="bg-white dark:bg-slate-900 rounded-xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm">
            <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Total Ingresos Período</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl font-bold text-slate-900 dark:text-white">
                    ${{ number_format($totalVendido, 2) }}
                </span>
                <span class="text-xs px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 font-semibold">
                    100%
                </span>
            </div>
            <p class="text-[11px] text-slate-400 mt-1">Suma neta de ventas completadas</p>
        </div>

        <!-- Transacciones / Tickets -->
        <div class="bg-white dark:bg-slate-900 rounded-xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm">
            <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Volumen de Ventas</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl font-bold text-slate-900 dark:text-white">
                    {{ number_format($cantidadVentas) }}
                </span>
                <span class="text-xs text-slate-500">tickets</span>
            </div>
            <p class="text-[11px] text-slate-400 mt-1">Transacciones procesadas</p>
        </div>

        <!-- Ticket Promedio -->
        <div class="bg-white dark:bg-slate-900 rounded-xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm">
            <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Ticket Promedio</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl font-bold text-slate-900 dark:text-white">
                    ${{ number_format($ticketPromedio, 2) }}
                </span>
                <span class="text-xs text-slate-500">/ venta</span>
            </div>
            <p class="text-[11px] text-slate-400 mt-1">Gasto promedio por paciente</p>
        </div>

        <!-- Método Líder -->
        @php
            $metodoLider = $ventasPorMetodo->sortByDesc('total')->first();
        @endphp
        <div class="bg-white dark:bg-slate-900 rounded-xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm">
            <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Método Principal</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl font-bold text-slate-900 dark:text-white">
                    {{ $metodoLider ? ucfirst($metodoLider->metodo_pago) : 'N/A' }}
                </span>
                <span class="text-xs font-bold text-emerald-600">
                    {{ $metodoLider && $totalVendido > 0 ? round(($metodoLider->total / $totalVendido) * 100, 1) . '%' : '0%' }}
                </span>
            </div>
            <p class="text-[11px] text-slate-400 mt-1">Mayor recaudación por canal</p>
        </div>
    </div>

    <!-- Desglose por Método y Top 10 Medicamentos -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Métodos de Pago del Período -->
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm p-5">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-3">Recaudación por Método de Pago</h3>
            <div class="space-y-3">
                @forelse($ventasPorMetodo as $m)
                @php
                    $pct = $totalVendido > 0 ? round(($m->total / $totalVendido) * 100, 1) : 0;
                @endphp
                <div class="p-3 rounded-lg bg-slate-50 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-800">
                    <div class="flex items-center justify-between text-xs mb-1">
                        <span class="font-bold text-slate-800 dark:text-slate-200">{{ ucfirst($m->metodo_pago) }}</span>
                        <span class="font-bold text-slate-900 dark:text-white">${{ number_format($m->total, 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between text-[11px] text-slate-500 mb-1.5">
                        <span>{{ $m->cantidad }} {{ Str::plural('ticket', $m->cantidad) }}</span>
                        <span>{{ $pct }}%</span>
                    </div>
                    <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-1.5 overflow-hidden">
                        <div class="{{ $m->metodo_pago === 'efectivo' ? 'bg-emerald-500' : ($m->metodo_pago === 'tarjeta' ? 'bg-blue-500' : 'bg-purple-500') }} h-1.5 rounded-full" style="width: {{ $pct }}%"></div>
                    </div>
                </div>
                @empty
                <div class="text-center py-6 text-xs text-slate-400">
                    Sin ventas para este período.
                </div>
                @endforelse
            </div>
        </div>

        <!-- Top 10 Medicamentos más Vendidos del Período -->
        <div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm p-5">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-3">Top 10 Medicamentos más Vendidos en el Período</h3>
            <div class="divide-y divide-slate-100 dark:divide-slate-800 overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-slate-400 dark:text-slate-500 font-semibold border-b border-slate-100 dark:border-slate-800 pb-2">
                            <th class="py-2 pr-2">#</th>
                            <th class="py-2 px-2">Medicamento</th>
                            <th class="py-2 px-2">Principio Activo</th>
                            <th class="py-2 px-2 text-right">Unidades</th>
                            <th class="py-2 pl-2 text-right">Ingresos</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                        @forelse($topProductos as $idx => $tp)
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition">
                            <td class="py-2.5 pr-2 font-mono text-slate-400">{{ $idx + 1 }}</td>
                            <td class="py-2.5 px-2 font-bold text-slate-800 dark:text-slate-200">{{ $tp->nombre }}</td>
                            <td class="py-2.5 px-2 text-slate-500 dark:text-slate-400">{{ $tp->principio_activo ?? '-' }}</td>
                            <td class="py-2.5 px-2 text-right font-semibold text-slate-700 dark:text-slate-300">{{ $tp->total_unidades }}</td>
                            <td class="py-2.5 pl-2 text-right font-bold text-emerald-600 dark:text-emerald-400">${{ number_format($tp->total_ingreso, 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-6 text-slate-400">
                                No se registran ítems vendidos en el rango seleccionado.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tabla Detallada de Transacciones -->
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Listado Detallado de Transacciones</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">Mostrando {{ $ventas->firstItem() ?? 0 }} a {{ $ventas->lastItem() ?? 0 }} de {{ $ventas->total() }} ventas</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-600 dark:text-slate-400 font-semibold uppercase tracking-wider text-[10px]">
                    <tr>
                        <th class="py-3 px-4">Ticket</th>
                        <th class="py-3 px-4">Fecha & Hora</th>
                        <th class="py-3 px-4">Cliente / Paciente</th>
                        <th class="py-3 px-4">Cajero</th>
                        <th class="py-3 px-4">Método</th>
                        <th class="py-3 px-4 text-right">Total ($)</th>
                        <th class="py-3 px-4 text-center print:hidden">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($ventas as $v)
                    <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition">
                        <td class="py-3 px-4 font-mono font-semibold text-slate-800 dark:text-slate-200">
                            #{{ $v->id }}
                        </td>
                        <td class="py-3 px-4 text-slate-600 dark:text-slate-300">
                            {{ $v->fecha ? \Carbon\Carbon::parse($v->fecha)->format('d/m/Y H:i') : $v->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="py-3 px-4">
                            <span class="font-medium text-slate-800 dark:text-slate-200 block">
                                {{ $v->cliente ? $v->cliente->nombre : 'Público General' }}
                            </span>
                            @if($v->cliente && $v->cliente->identificacion)
                            <span class="text-[11px] text-slate-400">Doc: {{ $v->cliente->identificacion }}</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-slate-600 dark:text-slate-400">
                            {{ $v->usuario->name ?? 'Sistema' }}
                        </td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-0.5 rounded text-[11px] font-semibold {{ $v->metodo_pago === 'efectivo' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : ($v->metodo_pago === 'tarjeta' ? 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300' : 'bg-purple-100 text-purple-800 dark:bg-purple-950 dark:text-purple-300') }}">
                                {{ ucfirst($v->metodo_pago) }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right font-bold text-slate-900 dark:text-white">
                            ${{ number_format($v->total, 2) }}
                        </td>
                        <td class="py-3 px-4 text-center print:hidden">
                            <a href="{{ route('ventas.show', $v) }}" 
                               class="inline-flex items-center space-x-1 text-xs font-semibold text-emerald-600 dark:text-emerald-400 hover:underline">
                                <span>Ver</span>
                                <span>&rarr;</span>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-400 text-xs">
                            No se encontraron registros de ventas con los filtros aplicados.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        @if($ventas->hasPages())
        <div class="px-5 py-3 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900 print:hidden">
            {{ $ventas->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
