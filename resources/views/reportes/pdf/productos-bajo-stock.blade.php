<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Alertas de Stock Mínimo - FarmaBien</title>
    <style>
        @page { margin: 25px 25px 35px 25px; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 9.5px; color: #1e293b; line-height: 1.25; }
        .header-table { width: 100%; border-bottom: 2px solid #e11d48; padding-bottom: 8px; margin-bottom: 10px; }
        .pharmacy-title { font-size: 16px; font-weight: bold; color: #059669; letter-spacing: 0.5px; }
        .pharmacy-subtitle { font-size: 8px; color: #64748b; margin-top: 1px; }
        .pharmacy-details { font-size: 7.5px; color: #475569; margin-top: 2px; line-height: 1.2; }
        .report-box { border: 1.5px solid #e11d48; background-color: #fff1f2; padding: 6px 10px; text-align: center; border-radius: 4px; }
        .report-title { font-size: 10px; font-weight: bold; color: #9f1239; text-transform: uppercase; }
        .report-meta { font-size: 7.5px; color: #be123c; margin-top: 2px; }
        
        .filters-box { background-color: #f8fafc; border: 1px solid #cbd5e1; padding: 5px 8px; border-radius: 4px; margin-bottom: 10px; font-size: 8px; }
        .filter-item { display: inline-block; margin-right: 12px; }
        .filter-label { font-weight: bold; color: #475569; }
        
        .kpi-table { width: 100%; margin-bottom: 10px; border-collapse: separate; border-spacing: 4px 0; }
        .kpi-card { background-color: #f8fafc; border: 1px solid #cbd5e1; border-radius: 4px; padding: 5px; text-align: center; }
        .kpi-label { font-size: 7.5px; font-weight: bold; color: #64748b; text-transform: uppercase; }
        .kpi-value { font-size: 12px; font-weight: bold; color: #0f172a; margin-top: 2px; }
        
        .data-table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        .data-table th { background-color: #e11d48; color: #ffffff; font-size: 8px; font-weight: bold; text-transform: uppercase; padding: 4px 5px; text-align: left; border: 1px solid #e11d48; }
        .data-table td { padding: 3.5px 5px; border: 1px solid #e2e8f0; font-size: 8px; }
        .data-table tr:nth-child(even) { background-color: #f8fafc; }
        .data-table tfoot td { background-color: #f1f5f9; font-weight: bold; font-size: 8.5px; border-top: 1.5px solid #e11d48; }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .text-rose { color: #e11d48; }
        .badge { display: inline-block; padding: 1px 4px; font-size: 7px; font-weight: bold; border-radius: 2px; text-transform: uppercase; }
        .badge-danger { background-color: #ffe4e6; color: #9f1239; }
        .badge-warning { background-color: #fef3c7; color: #92400e; }
        
        .footer-table { position: fixed; bottom: -20px; left: 0; right: 0; width: 100%; border-top: 1px solid #e2e8f0; padding-top: 4px; font-size: 7px; color: #94a3b8; }
    </style>
</head>
<body>

    {{-- Encabezado Institucional --}}
    @include('reportes.pdf.header', ['tituloReporte' => 'ALERTAS DE STOCK MÍNIMO Y REPOSICIÓN'])

    @php
        $criticos = collect($productos)->filter(fn($p) => ($p->stock_disponible ?? 0) === 0);
        $bajos = collect($productos)->filter(fn($p) => ($p->stock_disponible ?? 0) > 0);
        $total = count($productos);
        $deficitTotal = collect($productos)->sum(fn($p)=>max(0,($p->stock_minimo??0)-($p->stock_disponible??0)));
    @endphp

    {{-- KPI Cards --}}
    <table class="kpi-table">
        <tr>
            <td class="kpi-card" style="width: 25%;">
                <div class="kpi-label">Productos Afectados</div>
                <div class="kpi-value text-rose">{{ $total }}</div>
            </td>
            <td class="kpi-card" style="width: 25%;">
                <div class="kpi-label">Agotados (Stock = 0)</div>
                <div class="kpi-value text-rose">{{ $criticos->count() }}</div>
            </td>
            <td class="kpi-card" style="width: 25%;">
                <div class="kpi-label">Stock Bajo Mínimo</div>
                <div class="kpi-value" style="color: #d97706;">{{ $bajos->count() }}</div>
            </td>
            <td class="kpi-card" style="width: 25%;">
                <div class="kpi-label">Déficit Total de Unidades</div>
                <div class="kpi-value text-rose">−{{ number_format($deficitTotal) }}</div>
            </td>
        </tr>
    </table>

    {{-- Tabla de Productos Bajo Stock --}}
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 12%;">Estado</th>
                <th style="width: 32%;">Medicamento</th>
                <th style="width: 20%;">Categoría / Laboratorio</th>
                <th class="text-center" style="width: 12%;">Stock Actual</th>
                <th class="text-center" style="width: 12%;">Stock Mínimo</th>
                <th class="text-center" style="width: 12%;">Déficit</th>
            </tr>
        </thead>
        <tbody>
            @forelse($productos as $p)
            @php
                $sa = $p->stock_disponible ?? 0;
                $sm = $p->stock_minimo ?? 0;
                $def = max(0, $sm - $sa);
                $agotado = $sa === 0;
            @endphp
            <tr>
                <td>
                    <span class="badge {{ $agotado ? 'badge-danger' : 'badge-warning' }}">{{ $agotado ? 'Agotado' : 'Bajo Stock' }}</span>
                </td>
                <td class="font-bold">
                    {{ $p->nombre }}
                    @if($p->principio_activo)
                    <br><span style="font-weight: normal; color: #64748b; font-size: 7.5px;">{{ $p->principio_activo }}</span>
                    @endif
                </td>
                <td>{{ $p->categoria?->nombre ?? '—' }}<br><span style="color: #64748b; font-size: 7.5px;">{{ $p->laboratorio?->nombre ?? '' }}</span></td>
                <td class="text-center font-bold {{ $agotado ? 'text-rose' : '' }}">{{ number_format($sa) }}</td>
                <td class="text-center">{{ number_format($sm) }}</td>
                <td class="text-center font-bold text-rose">−{{ number_format($def) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center" style="padding: 15px;">Todos los productos cuentan con niveles de stock óptimos.</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="font-bold">TOTALES ({{ $total }} PRODUCTOS)</td>
                <td class="text-center font-bold">{{ number_format(collect($productos)->sum('stock_disponible')) }}</td>
                <td class="text-center font-bold">{{ number_format(collect($productos)->sum('stock_minimo')) }}</td>
                <td class="text-center font-bold text-rose">−{{ number_format($deficitTotal) }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- Pie de página institucional --}}
    <table class="footer-table">
        <tr>
            <td style="width: 50%;">FarmaBien — Sistema de Control y Gestión Farmacéutica</td>
            <td style="width: 50%; text-align: right;">Documento de alerta para el departamento de compras y adquisiciones</td>
        </tr>
    </table>

</body>
</html>
