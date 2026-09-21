<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Top Medicamentos Más Vendidos - FarmaBien</title>
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
        .text-indigo { color: #4f46e5; }
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
    @include('reportes.pdf.header', ['tituloReporte' => 'RANKING DE MEDICAMENTOS MÁS VENDIDOS (TOP 20)'])

    {{-- Filtros --}}
    <div class="filters-box">
        <span class="filter-item"><span class="filter-label">Período:</span> {{ \Carbon\Carbon::parse($fechaDesde)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($fechaHasta)->format('d/m/Y') }}</span>
        <span class="filter-item"><span class="filter-label">Total Productos Listados:</span> {{ number_format($ranking->count()) }}</span>
    </div>

    {{-- KPI Cards --}}
    @php
        $top1 = $ranking->first();
        $totalU = $ranking->sum('total_unidades_vendidas');
        $totalI = $ranking->sum('total_ingresos');
    @endphp
    <table class="kpi-table">
        <tr>
            <td class="kpi-card" style="width: 33%;">
                <div class="kpi-label">Medicamento #1</div>
                <div class="kpi-value" style="font-size: 10px;">{{ $top1->nombre ?? 'N/A' }}</div>
            </td>
            <td class="kpi-card" style="width: 33%;">
                <div class="kpi-label">Unidades Despachadas (Top 20)</div>
                <div class="kpi-value text-indigo">{{ number_format($totalU) }}</div>
            </td>
            <td class="kpi-card" style="width: 34%;">
                <div class="kpi-label">Ingresos Generados (Top 20)</div>
                <div class="kpi-value text-emerald">${{ number_format($totalI, 2) }}</div>
            </td>
        </tr>
    </table>

    {{-- Tabla Ranking --}}
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 8%; text-align: center;">Posición</th>
                <th style="width: 38%;">Medicamento</th>
                <th style="width: 24%;">Principio Activo</th>
                <th class="text-right" style="width: 15%;">Unidades Vendidas</th>
                <th class="text-right" style="width: 15%;">Total Ingresos ($)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ranking as $i => $item)
            @php
                $mClass = match($i) { 0 => 'medal-1', 1 => 'medal-2', 2 => 'medal-3', default => 'medal-other' };
            @endphp
            <tr>
                <td class="text-center"><span class="medal-badge {{ $mClass }}">{{ $i + 1 }}</span></td>
                <td class="font-bold">{{ $item->nombre }}</td>
                <td>{{ $item->principio_activo ?? '—' }}</td>
                <td class="text-right font-bold text-indigo">{{ number_format($item->total_unidades_vendidas) }} un.</td>
                <td class="text-right font-bold text-emerald">${{ number_format($item->total_ingresos, 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center" style="padding: 15px;">No se registraron ventas en el período.</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="font-bold">TOTALES TOP {{ $ranking->count() }}</td>
                <td class="text-right font-bold text-indigo">{{ number_format($totalU) }} un.</td>
                <td class="text-right font-bold text-emerald">${{ number_format($totalI, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- Pie de página institucional --}}
    <table class="footer-table">
        <tr>
            <td style="width: 50%;">FarmaBien — Sistema de Control y Gestión Farmacéutica</td>
            <td style="width: 50%; text-align: right;">Análisis de rotación y demanda farmacológica</td>
        </tr>
    </table>

</body>
</html>
