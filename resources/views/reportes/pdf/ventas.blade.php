<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Ventas e Ingresos - FarmaBien</title>
    <style>
        @page { margin: 25px 25px 35px 25px; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 9.5px; color: #1e293b; line-height: 1.25; }
        .header-table { width: 100%; border-bottom: 2px solid #059669; padding-bottom: 8px; margin-bottom: 10px; }
        .pharmacy-title { font-size: 16px; font-weight: bold; color: #059669; letter-spacing: 0.5px; }
        .pharmacy-subtitle { font-size: 8px; color: #64748b; margin-top: 1px; }
        .pharmacy-details { font-size: 7.5px; color: #475569; margin-top: 2px; line-height: 1.2; }
        .report-box { border: 1.5px solid #059669; background-color: #ecfdf5; padding: 6px 10px; text-align: center; border-radius: 4px; }
        .report-title { font-size: 10px; font-weight: bold; color: #065f46; text-transform: uppercase; }
        .report-meta { font-size: 7.5px; color: #047857; margin-top: 2px; }
        
        .filters-box { background-color: #f8fafc; border: 1px solid #cbd5e1; padding: 5px 8px; border-radius: 4px; margin-bottom: 10px; font-size: 8px; }
        .filter-item { display: inline-block; margin-right: 12px; }
        .filter-label { font-weight: bold; color: #475569; }
        
        .kpi-table { width: 100%; margin-bottom: 10px; border-collapse: separate; border-spacing: 4px 0; }
        .kpi-card { background-color: #f8fafc; border: 1px solid #cbd5e1; border-radius: 4px; padding: 5px; text-align: center; }
        .kpi-label { font-size: 7.5px; font-weight: bold; color: #64748b; text-transform: uppercase; }
        .kpi-value { font-size: 12px; font-weight: bold; color: #0f172a; margin-top: 2px; }
        
        .data-table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        .data-table th { background-color: #059669; color: #ffffff; font-size: 8px; font-weight: bold; text-transform: uppercase; padding: 4px 5px; text-align: left; border: 1px solid #059669; }
        .data-table td { padding: 3.5px 5px; border: 1px solid #e2e8f0; font-size: 8px; }
        .data-table tr:nth-child(even) { background-color: #f8fafc; }
        .data-table tfoot td { background-color: #f1f5f9; font-weight: bold; font-size: 8.5px; border-top: 1.5px solid #059669; }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .text-emerald { color: #059669; }
        .badge { display: inline-block; padding: 1px 4px; font-size: 7px; font-weight: bold; border-radius: 2px; text-transform: uppercase; }
        .badge-success { background-color: #d1fae5; color: #065f46; }
        .badge-info { background-color: #e0e7ff; color: #3730a3; }
        .badge-warning { background-color: #fef3c7; color: #92400e; }
        
        .footer-table { position: fixed; bottom: -20px; left: 0; right: 0; width: 100%; border-top: 1px solid #e2e8f0; padding-top: 4px; font-size: 7px; color: #94a3b8; }
    </style>
</head>
<body>

    {{-- Encabezado Institucional --}}
    @include('reportes.pdf.header', ['tituloReporte' => 'REPORTE GERENCIAL DE VENTAS E INGRESOS'])

    {{-- Resumen de Filtros --}}
    <div class="filters-box">
        <span class="filter-item"><span class="filter-label">Período:</span> {{ \Carbon\Carbon::parse($fechaDesde)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($fechaHasta)->format('d/m/Y') }}</span>
        <span class="filter-item"><span class="filter-label">Método de Pago:</span> {{ ucfirst($metodoPago ?: 'Todos') }}</span>
        <span class="filter-item"><span class="filter-label">Cajero:</span> {{ $cajeroId ? (\App\Models\User::find($cajeroId)?->name ?? 'N/A') : 'Todos' }}</span>
        <span class="filter-item"><span class="filter-label">Total Transacciones:</span> {{ number_format($cantidadVentas) }}</span>
    </div>

    {{-- KPI Cards --}}
    <table class="kpi-table">
        <tr>
            <td class="kpi-card" style="width: 25%;">
                <div class="kpi-label">Total Facturado</div>
                <div class="kpi-value text-emerald">${{ number_format($totalVendido, 2) }}</div>
            </td>
            <td class="kpi-card" style="width: 25%;">
                <div class="kpi-label">Ventas Realizadas</div>
                <div class="kpi-value">{{ number_format($cantidadVentas) }}</div>
            </td>
            <td class="kpi-card" style="width: 25%;">
                <div class="kpi-label">Ticket Promedio</div>
                <div class="kpi-value">${{ number_format($ticketPromedio, 2) }}</div>
            </td>
            <td class="kpi-card" style="width: 25%;">
                <div class="kpi-label">Método Principal</div>
                <div class="kpi-value">{{ ucfirst($ventasPorMetodo->first()->metodo_pago ?? 'N/A') }}</div>
            </td>
        </tr>
    </table>

    {{-- Tabla de Ventas --}}
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 7%;">Ticket</th>
                <th style="width: 14%;">Fecha y Hora</th>
                <th style="width: 25%;">Cliente</th>
                <th style="width: 18%;">Cajero / Usuario</th>
                <th style="width: 14%;">Método Pago</th>
                <th style="width: 10%;">Estado</th>
                <th class="text-right" style="width: 12%;">Total ($)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ventas as $v)
            @php
                $mClass = match($v->metodo_pago) { 'efectivo' => 'badge-success', 'tarjeta' => 'badge-info', default => 'badge-warning' };
            @endphp
            <tr>
                <td class="font-bold">#{{ str_pad($v->id, 5, '0', STR_PAD_LEFT) }}</td>
                <td>{{ $v->fecha ? \Carbon\Carbon::parse($v->fecha)->format('d/m/Y H:i') : $v->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ $v->cliente?->nombre ?? 'Público General' }}</td>
                <td>{{ $v->usuario?->name ?? 'Sistema' }}</td>
                <td><span class="badge {{ $mClass }}">{{ ucfirst($v->metodo_pago) }}</span></td>
                <td>{{ ucfirst($v->estado) }}</td>
                <td class="text-right font-bold">${{ number_format($v->total, 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center" style="padding: 15px;">No se registraron ventas en el período especificado.</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6" class="font-bold">TOTAL GENERAL ({{ number_format($ventas->count()) }} VENTAS)</td>
                <td class="text-right font-bold text-emerald">${{ number_format($totalVendido, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- Pie de página institucional --}}
    <table class="footer-table">
        <tr>
            <td style="width: 50%;">FarmaBien — Sistema de Control y Gestión Farmacéutica</td>
            <td style="width: 50%; text-align: right;">Documento generado electrónicamente</td>
        </tr>
    </table>

</body>
</html>
