<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Cajas y Arqueos - FarmaBien</title>
    <style>
        @page { margin: 20px 20px 30px 20px; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 8.5px; color: #1e293b; line-height: 1.25; }
        .header-table { width: 100%; border-bottom: 2px solid #059669; padding-bottom: 8px; margin-bottom: 10px; }
        
        .filters-box { background-color: #f8fafc; border: 1px solid #cbd5e1; padding: 5px 8px; border-radius: 4px; margin-bottom: 8px; font-size: 8px; }
        .filter-item { display: inline-block; margin-right: 12px; }
        .filter-label { font-weight: bold; color: #475569; }
        
        .kpi-table { width: 100%; margin-bottom: 8px; border-collapse: separate; border-spacing: 4px 0; }
        .kpi-card { background-color: #f8fafc; border: 1px solid #cbd5e1; border-radius: 4px; padding: 5px; text-align: center; }
        .kpi-label { font-size: 7px; font-weight: bold; color: #64748b; text-transform: uppercase; }
        .kpi-value { font-size: 11px; font-weight: bold; color: #0f172a; margin-top: 2px; }
        
        .data-table { width: 100%; border-collapse: collapse; margin-top: 4px; }
        .data-table th { background-color: #059669; color: #ffffff; font-size: 7.5px; font-weight: bold; text-transform: uppercase; padding: 4px; text-align: left; border: 1px solid #059669; }
        .data-table td { padding: 3px 4px; border: 1px solid #e2e8f0; font-size: 7.5px; }
        .data-table tr:nth-child(even) { background-color: #f8fafc; }
        .data-table tfoot td { background-color: #f1f5f9; font-weight: bold; font-size: 8px; border-top: 1.5px solid #059669; }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .text-emerald { color: #059669; }
        .text-rose { color: #e11d48; }
        .badge { display: inline-block; padding: 1px 3px; font-size: 6.5px; font-weight: bold; border-radius: 2px; text-transform: uppercase; }
        .badge-success { background-color: #d1fae5; color: #065f46; }
        .badge-danger { background-color: #ffe4e6; color: #9f1239; }
        .badge-info { background-color: #e0e7ff; color: #3730a3; }
        .badge-warning { background-color: #fef3c7; color: #92400e; }
        
        .footer-table { position: fixed; bottom: -15px; left: 0; right: 0; width: 100%; border-top: 1px solid #e2e8f0; padding-top: 3px; font-size: 7px; color: #94a3b8; }
    </style>
</head>
<body>

    {{-- Encabezado Institucional --}}
    @include('reportes.pdf.header', ['tituloReporte' => 'REPORTE GERENCIAL DE CAJAS Y ARQUEOS'])

    {{-- Resumen de Filtros --}}
    <div class="filters-box">
        <span class="filter-item"><span class="filter-label">Período:</span> {{ \Carbon\Carbon::parse($fechaDesde)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($fechaHasta)->format('d/m/Y') }}</span>
        <span class="filter-item"><span class="filter-label">Caja:</span> {{ $cajaId ? (\App\Models\Caja::find($cajaId)?->nombre ?? 'N/A') : 'Todas' }}</span>
        <span class="filter-item"><span class="filter-label">Cajero:</span> {{ $cajeroId ? (\App\Models\User::find($cajeroId)?->name ?? 'N/A') : 'Todos' }}</span>
        <span class="filter-item"><span class="filter-label">Estado:</span> {{ ucfirst($estado ?: 'Todos') }}</span>
        <span class="filter-item"><span class="filter-label">Total Turnos:</span> {{ number_format($totalSesiones) }}</span>
    </div>

    {{-- KPI Cards --}}
    <table class="kpi-table">
        <tr>
            <td class="kpi-card" style="width: 25%;">
                <div class="kpi-label">Total Ventas</div>
                <div class="kpi-value text-emerald">${{ number_format($totalVentasCajas, 2) }}</div>
            </td>
            <td class="kpi-card" style="width: 25%;">
                <div class="kpi-label">Ventas Efectivo</div>
                <div class="kpi-value">${{ number_format($totalVentasEfectivo, 2) }}</div>
            </td>
            <td class="kpi-card" style="width: 25%;">
                <div class="kpi-label">Movimientos Manuales (Neto)</div>
                <div class="kpi-value">${{ number_format($totalIngresosManuales - $totalEgresosManuales, 2) }}</div>
            </td>
            <td class="kpi-card" style="width: 25%;">
                <div class="kpi-label">Diferencia Neta Arqueos</div>
                <div class="kpi-value {{ $diferenciaTotal < 0 ? 'text-rose' : 'text-emerald' }}">{{ $diferenciaTotal > 0 ? '+' : '' }}${{ number_format($diferenciaTotal, 2) }}</div>
            </td>
        </tr>
    </table>

    {{-- Tabla de Cajas --}}
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 7%;">Turno</th>
                <th style="width: 14%;">Caja</th>
                <th style="width: 15%;">Cajero Responsable</th>
                <th style="width: 12%;">Apertura</th>
                <th style="width: 12%;">Cierre</th>
                <th class="text-right" style="width: 8%;">Monto Inic.</th>
                <th class="text-right" style="width: 8%;">Ventas Efec.</th>
                <th class="text-right" style="width: 8%;">Total Ventas</th>
                <th class="text-right" style="width: 8%;">Esperado</th>
                <th class="text-right" style="width: 8%;">Declarado</th>
                <th class="text-center" style="width: 8%;">Diferencia</th>
                <th class="text-center" style="width: 6%;">Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sesiones as $s)
            <tr>
                <td class="font-bold">#{{ str_pad($s->id, 5, '0', STR_PAD_LEFT) }}</td>
                <td>{{ $s->caja?->nombre ?? ('Caja #' . $s->caja_id) }}</td>
                <td>{{ $s->usuario?->name ?? 'N/A' }}</td>
                <td>{{ $s->fecha_apertura ? $s->fecha_apertura->format('d/m/Y H:i') : 'N/A' }}</td>
                <td>{{ $s->fecha_cierre ? $s->fecha_cierre->format('d/m/Y H:i') : 'En curso' }}</td>
                <td class="text-right">${{ number_format($s->monto_inicial, 2) }}</td>
                <td class="text-right">${{ number_format($s->total_ventas_efectivo, 2) }}</td>
                <td class="text-right font-bold">${{ number_format($s->total_ventas, 2) }}</td>
                <td class="text-right">${{ number_format($s->monto_esperado_efectivo ?? $s->efectivo_esperado_calculado, 2) }}</td>
                <td class="text-right font-bold">${{ number_format($s->monto_final_efectivo ?? 0, 2) }}</td>
                <td class="text-center font-bold">
                    @if($s->estado === 'cerrada')
                        @if(round((float)$s->diferencia_efectivo, 2) == 0)
                            <span class="badge badge-success">$0.00</span>
                        @elseif((float)$s->diferencia_efectivo < 0)
                            <span class="badge badge-danger">-${{ number_format(abs($s->diferencia_efectivo), 2) }}</span>
                        @else
                            <span class="badge badge-info">+${{ number_format($s->diferencia_efectivo, 2) }}</span>
                        @endif
                    @else
                        -
                    @endif
                </td>
                <td class="text-center">
                    <span class="badge {{ $s->estado === 'abierta' ? 'badge-warning' : 'badge-success' }}">{{ ucfirst($s->estado) }}</span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="12" class="text-center" style="padding: 12px;">No se encontraron registros de turnos de caja para los filtros seleccionados.</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="font-bold">TOTAL GENERAL ({{ number_format($sesiones->count()) }} TURNOS)</td>
                <td class="text-right">${{ number_format($sesiones->sum('monto_inicial'), 2) }}</td>
                <td class="text-right">${{ number_format($totalVentasEfectivo, 2) }}</td>
                <td class="text-right font-bold text-emerald">${{ number_format($totalVentasCajas, 2) }}</td>
                <td colspan="2"></td>
                <td class="text-center font-bold {{ $diferenciaTotal < 0 ? 'text-rose' : 'text-emerald' }}">
                    {{ $diferenciaTotal > 0 ? '+' : '' }}${{ number_format($diferenciaTotal, 2) }}
                </td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    {{-- Pie de página --}}
    <table class="footer-table">
        <tr>
            <td style="width: 50%;">FarmaBien — Sistema de Control y Gestión Farmacéutica</td>
            <td style="width: 50%; text-align: right;">Documento generado electrónicamente</td>
        </tr>
    </table>

</body>
</html>
