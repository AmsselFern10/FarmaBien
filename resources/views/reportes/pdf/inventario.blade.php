<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Inventario y Caducidad - FarmaBien</title>
    <style>
        @page { margin: 25px 25px 35px 25px; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 9px; color: #1e293b; line-height: 1.25; }
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
        .kpi-value { font-size: 11.5px; font-weight: bold; color: #0f172a; margin-top: 2px; }
        
        .data-table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        .data-table th { background-color: #059669; color: #ffffff; font-size: 7.5px; font-weight: bold; text-transform: uppercase; padding: 4px 4px; text-align: left; border: 1px solid #059669; }
        .data-table td { padding: 3px 4px; border: 1px solid #e2e8f0; font-size: 7.5px; }
        .data-table tr:nth-child(even) { background-color: #f8fafc; }
        .data-table tfoot td { background-color: #f1f5f9; font-weight: bold; font-size: 8px; border-top: 1.5px solid #059669; }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .text-emerald { color: #059669; }
        .text-indigo { color: #4f46e5; }
        .badge { display: inline-block; padding: 1px 3px; font-size: 6.5px; font-weight: bold; border-radius: 2px; text-transform: uppercase; }
        .badge-danger { background-color: #ffe4e6; color: #9f1239; }
        .badge-warning { background-color: #fef3c7; color: #92400e; }
        .badge-info { background-color: #e0e7ff; color: #3730a3; }
        .badge-success { background-color: #d1fae5; color: #065f46; }
        
        .footer-table { position: fixed; bottom: -20px; left: 0; right: 0; width: 100%; border-top: 1px solid #e2e8f0; padding-top: 4px; font-size: 7px; color: #94a3b8; }
    </style>
</head>
<body>

    {{-- Encabezado Institucional --}}
    @include('reportes.pdf.header', ['tituloReporte' => 'VALORIZACIÓN DE INVENTARIO Y CADUCIDAD (PEPS)'])

    {{-- Filtros --}}
    <div class="filters-box">
        <span class="filter-item"><span class="filter-label">Búsqueda:</span> {{ $buscar ?: 'Todos' }}</span>
        <span class="filter-item"><span class="filter-label">Categoría:</span> {{ $categoriaId ? (\App\Models\Categoria::find($categoriaId)?->nombre ?? 'N/A') : 'Todas' }}</span>
        <span class="filter-item"><span class="filter-label">Laboratorio:</span> {{ $laboratorioId ? (\App\Models\Laboratorio::find($laboratorioId)?->nombre ?? 'N/A') : 'Todos' }}</span>
        <span class="filter-item"><span class="filter-label">Caducidad:</span> {{ ucfirst($estadoVencimiento ?: 'Todos') }}</span>
        <span class="filter-item"><span class="filter-label">Stock:</span> {{ ucfirst($estadoStock ?: 'Todos') }}</span>
    </div>

    {{-- KPI Cards --}}
    <table class="kpi-table">
        <tr>
            <td class="kpi-card" style="width: 20%;">
                <div class="kpi-label">Valor a Costo</div>
                <div class="kpi-value text-indigo">${{ number_format($totalValorCosto, 2) }}</div>
            </td>
            <td class="kpi-card" style="width: 20%;">
                <div class="kpi-label">Venta Proyectada</div>
                <div class="kpi-value text-emerald">${{ number_format($totalValorVenta, 2) }}</div>
            </td>
            <td class="kpi-card" style="width: 20%;">
                <div class="kpi-label">Unidades en Stock</div>
                <div class="kpi-value">{{ number_format($totalUnidadesStock) }}</div>
            </td>
            <td class="kpi-card" style="width: 20%;">
                <div class="kpi-label">Margen Proyectado</div>
                <div class="kpi-value">{{ $margenProyectado }}%</div>
            </td>
            <td class="kpi-card" style="width: 20%;">
                <div class="kpi-label">Vencidos / Críticos</div>
                <div class="kpi-value" style="color: #e11d48;">{{ $semVencidos }} / {{ $semCritico30 }}</div>
            </td>
        </tr>
    </table>

    {{-- Tabla de Lotes --}}
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 10%;">Lote</th>
                <th style="width: 24%;">Medicamento</th>
                <th style="width: 14%;">Laboratorio</th>
                <th style="width: 11%;">Vencimiento</th>
                <th style="width: 11%;">Estado</th>
                <th class="text-right" style="width: 8%;">Stock</th>
                <th class="text-right" style="width: 11%;">Costo Total ($)</th>
                <th class="text-right" style="width: 11%;">Venta Proy. ($)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($lotes as $l)
            @php
                $dias = (int) now()->diffInDays($l->fecha_vencimiento, false);
                [$sem, $bClass] = $dias < 0
                    ? ['Vencido', 'badge-danger']
                    : ($dias <= 30
                        ? ['≤ 30d', 'badge-warning']
                        : ($dias <= 90
                            ? ['≤ 90d', 'badge-info']
                            : ['Vigente', 'badge-success']));
                $valCosto = round($l->stock_actual * (float)$l->precio_compra, 2);
                $valVenta = round($l->stock_actual * (float)($l->producto->precio_venta ?? 0), 2);
            @endphp
            <tr>
                <td class="font-bold">{{ $l->numero_lote }}</td>
                <td>{{ $l->producto->nombre ?? 'N/A' }}</td>
                <td>{{ $l->producto->laboratorio->nombre ?? '—' }}</td>
                <td>{{ $l->fecha_vencimiento?->format('d/m/Y') ?? '—' }}</td>
                <td><span class="badge {{ $bClass }}">{{ $sem }}</span></td>
                <td class="text-right font-bold">{{ number_format($l->stock_actual) }}</td>
                <td class="text-right">${{ number_format($valCosto, 2) }}</td>
                <td class="text-right font-bold text-emerald">${{ number_format($valVenta, 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center" style="padding: 15px;">No se encontraron lotes con los filtros aplicados.</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="font-bold">TOTAL GENERAL ({{ number_format($lotes->count()) }} LOTES)</td>
                <td class="text-right font-bold">{{ number_format($totalUnidadesStock) }}</td>
                <td class="text-right font-bold text-indigo">${{ number_format($totalValorCosto, 2) }}</td>
                <td class="text-right font-bold text-emerald">${{ number_format($totalValorVenta, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- Pie de página institucional --}}
    <table class="footer-table">
        <tr>
            <td style="width: 50%;">FarmaBien — Sistema de Control y Gestión Farmacéutica</td>
            <td style="width: 50%; text-align: right;">Documento confidencial para control de auditoría e inventario</td>
        </tr>
    </table>

</body>
</html>
