<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Clientes y Frecuencia - FarmaBien</title>
    <style>
        @page { margin: 25px 25px 35px 25px; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 9.5px; color: #1e293b; line-height: 1.25; }
        .header-table { width: 100%; border-bottom: 2px solid #7c3aed; padding-bottom: 8px; margin-bottom: 10px; }
        .pharmacy-title { font-size: 16px; font-weight: bold; color: #059669; letter-spacing: 0.5px; }
        .pharmacy-subtitle { font-size: 8px; color: #64748b; margin-top: 1px; }
        .pharmacy-details { font-size: 7.5px; color: #475569; margin-top: 2px; line-height: 1.2; }
        .report-box { border: 1.5px solid #7c3aed; background-color: #f5f3ff; padding: 6px 10px; text-align: center; border-radius: 4px; }
        .report-title { font-size: 10px; font-weight: bold; color: #5b21b6; text-transform: uppercase; }
        .report-meta { font-size: 7.5px; color: #6d28d9; margin-top: 2px; }
        
        .filters-box { background-color: #f8fafc; border: 1px solid #cbd5e1; padding: 5px 8px; border-radius: 4px; margin-bottom: 10px; font-size: 8px; }
        .filter-item { display: inline-block; margin-right: 12px; }
        .filter-label { font-weight: bold; color: #475569; }
        
        .kpi-table { width: 100%; margin-bottom: 10px; border-collapse: separate; border-spacing: 4px 0; }
        .kpi-card { background-color: #f8fafc; border: 1px solid #cbd5e1; border-radius: 4px; padding: 5px; text-align: center; }
        .kpi-label { font-size: 7.5px; font-weight: bold; color: #64748b; text-transform: uppercase; }
        .kpi-value { font-size: 12px; font-weight: bold; color: #0f172a; margin-top: 2px; }
        
        .data-table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        .data-table th { background-color: #7c3aed; color: #ffffff; font-size: 8px; font-weight: bold; text-transform: uppercase; padding: 4px 5px; text-align: left; border: 1px solid #7c3aed; }
        .data-table td { padding: 3.5px 5px; border: 1px solid #e2e8f0; font-size: 8px; }
        .data-table tr:nth-child(even) { background-color: #f8fafc; }
        .data-table tfoot td { background-color: #f1f5f9; font-weight: bold; font-size: 8.5px; border-top: 1.5px solid #7c3aed; }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .text-emerald { color: #059669; }
        .text-violet { color: #7c3aed; }
        .medal-badge { display: inline-block; width: 14px; height: 14px; line-height: 14px; text-align: center; font-weight: bold; border-radius: 50%; font-size: 8px; }
        .medal-1 { background-color: #f59e0b; color: white; }
        .medal-2 { background-color: #94a3b8; color: white; }
        .medal-3 { background-color: #d97706; color: white; }
        .medal-other { background-color: #e2e8f0; color: #475569; }
        
        .footer-table { position: fixed; bottom: -20px; left: 0; right: 0; width: 100%; border-top: 1px solid #e2e8f0; padding-top: 4px; font-size: 7px; color: #94a3b8; }
    </style>
</head>
<body>

    {{-- Encabezado Institucional --}}
    @include('reportes.pdf.header', ['tituloReporte' => 'REPORTE DE CLIENTES Y FRECUENCIA DE COMPRA (TOP 20)'])

    {{-- Filtros --}}
    <div class="filters-box">
        <span class="filter-item"><span class="filter-label">Período:</span> {{ \Carbon\Carbon::parse($fechaDesde)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($fechaHasta)->format('d/m/Y') }}</span>
        <span class="filter-item"><span class="filter-label">Padrón Activo:</span> {{ number_format($totalClientes) }} clientes</span>
        <span class="filter-item"><span class="filter-label">Clientes con Compra:</span> {{ number_format($clientesConCompras) }}</span>
    </div>

    {{-- KPI Cards --}}
    <table class="kpi-table">
        <tr>
            <td class="kpi-card" style="width: 25%;">
                <div class="kpi-label">Padrón Total</div>
                <div class="kpi-value">{{ number_format($totalClientes) }}</div>
            </td>
            <td class="kpi-card" style="width: 25%;">
                <div class="kpi-label">Clientes Activos</div>
                <div class="kpi-value text-violet">{{ number_format($clientesConCompras) }}</div>
            </td>
            <td class="kpi-card" style="width: 25%;">
                <div class="kpi-label">Total Facturado</div>
                <div class="kpi-value text-emerald">${{ number_format($totalFacturadoClientes, 2) }}</div>
            </td>
            <td class="kpi-card" style="width: 25%;">
                <div class="kpi-label">Ticket Promedio</div>
                <div class="kpi-value">${{ number_format($ticketPromedio, 2) }}</div>
            </td>
        </tr>
    </table>

    {{-- Tabla de Clientes --}}
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 7%; text-align: center;">Pos.</th>
                <th style="width: 33%;">Cliente / Paciente</th>
                <th style="width: 16%;">Documento</th>
                <th style="width: 14%;">Teléfono</th>
                <th class="text-center" style="width: 10%;">Visitas</th>
                <th class="text-right" style="width: 10%;">Ticket Prom. ($)</th>
                <th class="text-right" style="width: 10%;">Total Gastado ($)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($topClientes as $i => $c)
            @php
                $mClass = match($i) { 0 => 'medal-1', 1 => 'medal-2', 2 => 'medal-3', default => 'medal-other' };
                $ticketC = $c->total_ventas > 0 ? $c->monto_total / $c->total_ventas : 0;
            @endphp
            <tr>
                <td class="text-center"><span class="medal-badge {{ $mClass }}">{{ $i + 1 }}</span></td>
                <td class="font-bold">{{ $c->nombre }}</td>
                <td>{{ $c->documento ?? '—' }}</td>
                <td>{{ $c->telefono ?? '—' }}</td>
                <td class="text-center font-bold text-violet">{{ number_format($c->total_ventas) }}</td>
                <td class="text-right">${{ number_format($ticketC, 2) }}</td>
                <td class="text-right font-bold text-emerald">${{ number_format($c->monto_total, 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center" style="padding: 15px;">No se registraron ventas a clientes identificados en este período.</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="font-bold">TOTAL GENERAL (TOP {{ $topClientes->count() }})</td>
                <td class="text-center font-bold text-violet">{{ number_format($topClientes->sum('total_ventas')) }}</td>
                <td class="text-right font-bold">${{ number_format($ticketPromedio, 2) }}</td>
                <td class="text-right font-bold text-emerald">${{ number_format($totalFacturadoClientes, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- Pie de página institucional --}}
    <table class="footer-table">
        <tr>
            <td style="width: 50%;">FarmaBien — Sistema de Control y Gestión Farmacéutica</td>
            <td style="width: 50%; text-align: right;">Documento de fidelización y seguimiento a pacientes</td>
        </tr>
    </table>

</body>
</html>
