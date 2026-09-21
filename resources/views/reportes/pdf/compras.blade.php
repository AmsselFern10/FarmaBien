<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Proveedores y Compras - FarmaBien</title>
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
        .text-amber { color: #d97706; }
        .badge { display: inline-block; padding: 1px 4px; font-size: 7px; font-weight: bold; border-radius: 2px; text-transform: uppercase; }
        .badge-success { background-color: #d1fae5; color: #065f46; }
        .badge-warning { background-color: #fef3c7; color: #92400e; }
        .badge-info { background-color: #e0e7ff; color: #3730a3; }
        
        .footer-table { position: fixed; bottom: -20px; left: 0; right: 0; width: 100%; border-top: 1px solid #e2e8f0; padding-top: 4px; font-size: 7px; color: #94a3b8; }
    </style>
</head>
<body>

    {{-- Encabezado Institucional --}}
    @include('reportes.pdf.header', ['tituloReporte' => 'REPORTE DE ABASTECIMIENTO Y COMPRAS'])

    {{-- Filtros --}}
    <div class="filters-box">
        <span class="filter-item"><span class="filter-label">Período:</span> {{ \Carbon\Carbon::parse($fechaDesde)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($fechaHasta)->format('d/m/Y') }}</span>
        <span class="filter-item"><span class="filter-label">Órdenes Recibidas:</span> {{ number_format($compras->count()) }}</span>
    </div>

    {{-- KPI Cards --}}
    @php
        $cantOrdenes = $compras->count();
        $promedioOrden = $cantOrdenes > 0 ? $totalComprado / $cantOrdenes : 0;
        $proveedorLider = $compras->groupBy('proveedor_id')->map(function($group) {
            return [
                'nombre' => $group->first()->proveedor?->nombre ?? 'N/A',
                'monto' => $group->sum('total')
            ];
        })->sortByDesc('monto')->first();
    @endphp
    <table class="kpi-table">
        <tr>
            <td class="kpi-card" style="width: 25%;">
                <div class="kpi-label">Total Comprado</div>
                <div class="kpi-value text-amber">${{ number_format($totalComprado, 2) }}</div>
            </td>
            <td class="kpi-card" style="width: 25%;">
                <div class="kpi-label">Órdenes Procesadas</div>
                <div class="kpi-value">{{ number_format($cantOrdenes) }}</div>
            </td>
            <td class="kpi-card" style="width: 25%;">
                <div class="kpi-label">Promedio / Orden</div>
                <div class="kpi-value">${{ number_format($promedioOrden, 2) }}</div>
            </td>
            <td class="kpi-card" style="width: 25%;">
                <div class="kpi-label">Proveedor Líder</div>
                <div class="kpi-value" style="font-size: 10px;">{{ $proveedorLider['nombre'] ?? '—' }}</div>
            </td>
        </tr>
    </table>

    {{-- Tabla de Compras --}}
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 10%;">N° Orden</th>
                <th style="width: 26%;">Proveedor</th>
                <th style="width: 14%;">N° Factura</th>
                <th style="width: 13%;">Fecha</th>
                <th style="width: 15%;">Responsable</th>
                <th style="width: 10%;">Estado</th>
                <th class="text-right" style="width: 12%;">Total ($)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($compras as $c)
            <tr>
                <td class="font-bold">#{{ str_pad($c->id, 4, '0', STR_PAD_LEFT) }}</td>
                <td>{{ $c->proveedor?->nombre ?? '—' }}</td>
                <td>{{ $c->numero_factura ?? '—' }}</td>
                <td>{{ $c->fecha ? \Carbon\Carbon::parse($c->fecha)->format('d/m/Y') : '—' }}</td>
                <td>{{ $c->usuario?->name ?? 'Sistema' }}</td>
                <td><span class="badge badge-success">{{ ucfirst($c->estado ?? 'Recibida') }}</span></td>
                <td class="text-right font-bold">${{ number_format($c->total, 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center" style="padding: 15px;">No se registraron compras en el período seleccionado.</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6" class="font-bold">TOTAL GENERAL ({{ number_format($compras->count()) }} ÓRDENES)</td>
                <td class="text-right font-bold text-amber">${{ number_format($totalComprado, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- Pie de página institucional --}}
    <table class="footer-table">
        <tr>
            <td style="width: 50%;">FarmaBien — Sistema de Control y Gestión Farmacéutica</td>
            <td style="width: 50%; text-align: right;">Documento de control administrativo y abastecimiento</td>
        </tr>
    </table>

</body>
</html>
