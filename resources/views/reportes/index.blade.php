@extends('layouts.app')

@section('title', 'Centro de Reportes Gerenciales - FarmaBien')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                    Módulo Gerencial
                </span>
                <span class="text-xs text-slate-400">&bull;</span>
                <span class="text-xs text-slate-500 dark:text-slate-400">Inteligencia de Negocio</span>
            </div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white mt-1">Centro de Reportes y Estadísticas</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Métricas financieras, rotación farmacéutica y análisis operativo consolidado.
            </p>
        </div>

        <div class="flex items-center space-x-2">
            <a href="{{ route('reportes.ventas') }}" 
               class="inline-flex items-center space-x-2 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <span>Reporte Detallado de Ventas</span>
            </a>
        </div>
    </div>

    <!-- 4 KPI Cards del Mes -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Ventas Hoy -->
        <div class="bg-white dark:bg-slate-900 rounded-xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Ventas Hoy</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-bold text-slate-900 dark:text-white">
                    ${{ number_format($ventasHoy, 2) }}
                </div>
                <div class="flex items-center justify-between mt-2 text-xs">
                    <span class="text-slate-500 dark:text-slate-400">Día actual</span>
                    <a href="{{ route('reportes.ventas', ['fecha_desde' => today()->toDateString(), 'fecha_hasta' => today()->toDateString()]) }}" class="font-medium text-emerald-600 dark:text-emerald-400 hover:underline">Ver detalle &rarr;</a>
                </div>
            </div>
        </div>

        <!-- Ventas del Mes -->
        <div class="bg-white dark:bg-slate-900 rounded-xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Ventas del Mes ({{ now()->translatedFormat('F') }})</span>
                <div class="w-8 h-8 rounded-lg bg-teal-50 dark:bg-teal-950/50 text-teal-600 dark:text-teal-400 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-bold text-slate-900 dark:text-white">
                    ${{ number_format($ventasMes, 2) }}
                </div>
                <div class="flex items-center justify-between mt-2 text-xs">
                    <span class="text-slate-500 dark:text-slate-400">{{ $cantidadVentasMes }} {{ Str::plural('transacción', $cantidadVentasMes) }}</span>
                    <a href="{{ route('reportes.ventas') }}" class="font-medium text-teal-600 dark:text-teal-400 hover:underline">Filtrar &rarr;</a>
                </div>
            </div>
        </div>

        <!-- Compras del Mes -->
        <div class="bg-white dark:bg-slate-900 rounded-xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Compras del Mes</span>
                <div class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-bold text-slate-900 dark:text-white">
                    ${{ number_format($comprasMes, 2) }}
                </div>
                <div class="flex items-center justify-between mt-2 text-xs">
                    <span class="text-slate-500 dark:text-slate-400">Reabastecimiento</span>
                    <a href="{{ route('compras.index') }}" class="font-medium text-slate-700 dark:text-slate-300 hover:underline">Ver compras &rarr;</a>
                </div>
            </div>
        </div>

        <!-- Valorización de Inventario -->
        <div class="bg-white dark:bg-slate-900 rounded-xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Valorización de Stock</span>
                <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-bold text-slate-900 dark:text-white">
                    ${{ number_format($valorizacion['costo_total'] ?? 0, 2) }}
                </div>
                <div class="flex items-center justify-between mt-2 text-xs">
                    <span class="text-slate-500 dark:text-slate-400">Costo almacén</span>
                    <a href="{{ route('inventario.index') }}" class="font-medium text-indigo-600 dark:text-indigo-400 hover:underline">Auditar stock &rarr;</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Catálogo de Módulos de Reporte Disponibles -->
    <div>
        <h2 class="text-sm font-bold text-slate-900 dark:text-white mb-3 flex items-center space-x-2">
            <span>📑 Catálogo de Informes Gerenciales</span>
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <!-- Reporte 1: Ventas e Ingresos (DISPONIBLE) -->
            <a href="{{ route('reportes.ventas') }}" 
               class="group relative bg-white dark:bg-slate-900 rounded-xl p-5 border border-emerald-300 dark:border-emerald-800/80 shadow-sm hover:shadow-md hover:border-emerald-500 dark:hover:border-emerald-500 transition-all block">
                <div class="flex items-start justify-between">
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-950/80 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg">
                        📈
                    </div>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                        Disponible
                    </span>
                </div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white mt-3 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition">
                    Reporte de Ventas e Ingresos
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    Desglose por fecha, ticket promedio, métodos de pago (Efectivo, Tarjeta, Transferencia), cajero y exportación CSV.
                </p>
                <div class="mt-4 flex items-center text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                    <span>Acceder al reporte</span>
                    <span class="ml-1 group-hover:translate-x-1 transition-transform">&rarr;</span>
                </div>
            </a>

            <!-- Reporte 2: Control de Inventario y Caducidad (DISPONIBLE) -->
            <a href="{{ route('reportes.inventario') }}" 
               class="group relative bg-white dark:bg-slate-900 rounded-xl p-5 border border-indigo-300 dark:border-indigo-800/80 shadow-sm hover:shadow-md hover:border-indigo-500 dark:hover:border-indigo-500 transition-all block">
                <div class="flex items-start justify-between">
                    <div class="w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-950/80 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-lg">
                        💊
                    </div>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-300">
                        Disponible
                    </span>
                </div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white mt-3 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition">
                    Control de Inventario y Caducidad
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    Semáforo PEPS a 30, 60 y 90 días, valorización total a costo y venta, alertas de stock mínimo y exportación CSV.
                </p>
                <div class="mt-4 flex items-center text-xs font-semibold text-indigo-600 dark:text-indigo-400">
                    <span>Acceder al reporte</span>
                    <span class="ml-1 group-hover:translate-x-1 transition-transform">&rarr;</span>
                </div>
            </a>

            <!-- Reporte 3: Clientes y Frecuencia -->
            <div class="bg-white dark:bg-slate-900 rounded-xl p-5 border border-slate-200 dark:border-slate-800 shadow-xs opacity-80">
                <div class="flex items-start justify-between">
                    <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 flex items-center justify-center text-lg">
                        👥
                    </div>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400">
                        Próxima fase
                    </span>
                </div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white mt-3">
                    Clientes y Frecuencia de Compra
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    Historial consolidado por paciente, recurrencia en medicamentos para enfermedades crónicas y ticket medio.
                </p>
                <div class="mt-4 text-xs text-slate-400">
                    <a href="{{ route('clientes.index') }}" class="text-emerald-600 hover:underline">Ver Catálogo de Pacientes &rarr;</a>
                </div>
            </div>

            <!-- Reporte 4: Proveedores y Compras -->
            <div class="bg-white dark:bg-slate-900 rounded-xl p-5 border border-slate-200 dark:border-slate-800 shadow-xs opacity-80">
                <div class="flex items-start justify-between">
                    <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 flex items-center justify-center text-lg">
                        🚚
                    </div>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400">
                        Próxima fase
                    </span>
                </div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white mt-3">
                    Proveedores y Recepción de Lotes
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    Volúmenes comprados por laboratorio droguería, cumplimiento de facturas y costos comparativos.
                </p>
                <div class="mt-4 text-xs text-slate-400">
                    <a href="{{ route('compras.index') }}" class="text-emerald-600 hover:underline">Ver Registro de Compras &rarr;</a>
                </div>
            </div>

            <!-- Reporte 5: Auditoría IA y Lupa Inteligente -->
            <div class="bg-white dark:bg-slate-900 rounded-xl p-5 border border-slate-200 dark:border-slate-800 shadow-xs opacity-80">
                <div class="flex items-start justify-between">
                    <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 flex items-center justify-center text-lg">
                        🤖
                    </div>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400">
                        Próxima fase
                    </span>
                </div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white mt-3">
                    Consultas de IA & Lupa Inteligente
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    Métricas de uso sintomático, términos médicos más consultados en el mostrador para anticipar stock.
                </p>
                <div class="mt-4 text-xs text-slate-400">
                    <a href="{{ route('productos.index') }}" class="text-emerald-600 hover:underline">Ver Catálogo IA &rarr;</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Tablas Resumen: Top Productos y Métodos de Pago del Mes -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Top 5 Productos del Mes -->
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm p-5">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3 mb-4">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Top 5 Medicamentos más Vendidos</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Mes en curso ({{ now()->translatedFormat('F Y') }})</p>
                </div>
                <a href="{{ route('reportes.ventas') }}" class="text-xs font-medium text-emerald-600 dark:text-emerald-400 hover:underline">
                    Ver ranking completo &rarr;
                </a>
            </div>

            <div class="space-y-3">
                @forelse($topProductosMes as $idx => $prod)
                <div>
                    <div class="flex items-center justify-between text-xs mb-1">
                        <span class="font-semibold text-slate-800 dark:text-slate-200 truncate max-w-[220px]">
                            <span class="text-slate-400 mr-1.5">#{{ $idx + 1 }}</span> {{ $prod->nombre }}
                        </span>
                        <div class="space-x-2 text-right">
                            <span class="font-bold text-slate-900 dark:text-white">${{ number_format($prod->total_monto, 2) }}</span>
                            <span class="text-slate-500">({{ $prod->total_unidades }} un.)</span>
                        </div>
                    </div>
                    @php
                        $maxUnits = $topProductosMes->first()->total_unidades ?? 1;
                        $percent = $maxUnits > 0 ? min(100, round(($prod->total_unidades / $maxUnits) * 100)) : 0;
                    @endphp
                    <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2 overflow-hidden">
                        <div class="bg-emerald-500 h-2 rounded-full transition-all duration-500" style="width: {{ $percent }}%"></div>
                    </div>
                </div>
                @empty
                <div class="text-center py-6 text-xs text-slate-400">
                    No hay suficientes ventas registradas este mes.
                </div>
                @endforelse
            </div>
        </div>

        <!-- Distribución por Método de Pago del Mes -->
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm p-5">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3 mb-4">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Distribución por Método de Pago</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Total acumulado del mes</p>
                </div>
                <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400">
                    ${{ number_format($ventasMes, 2) }}
                </span>
            </div>

            <div class="space-y-3">
                @forelse($metodosMes as $m)
                @php
                    $pct = $ventasMes > 0 ? round(($m->total / $ventasMes) * 100, 1) : 0;
                @endphp
                <div class="p-3 rounded-lg bg-slate-50 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-800">
                    <div class="flex items-center justify-between text-xs mb-1.5">
                        <div class="flex items-center space-x-2">
                            <span class="w-2 h-2 rounded-full {{ $m->metodo_pago === 'efectivo' ? 'bg-emerald-500' : ($m->metodo_pago === 'tarjeta' ? 'bg-blue-500' : 'bg-purple-500') }}"></span>
                            <span class="font-bold text-slate-800 dark:text-slate-200">{{ ucfirst($m->metodo_pago) }}</span>
                        </div>
                        <div class="space-x-2">
                            <span class="font-bold text-slate-900 dark:text-white">${{ number_format($m->total, 2) }}</span>
                            <span class="text-slate-500 font-mono">({{ $pct }}%)</span>
                        </div>
                    </div>
                    <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-1.5 overflow-hidden">
                        <div class="{{ $m->metodo_pago === 'efectivo' ? 'bg-emerald-500' : ($m->metodo_pago === 'tarjeta' ? 'bg-blue-500' : 'bg-purple-500') }} h-1.5 rounded-full" style="width: {{ $pct }}%"></div>
                    </div>
                </div>
                @empty
                <div class="text-center py-6 text-xs text-slate-400">
                    No hay transacciones registradas este mes.
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

