<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Recetas Médicas - FarmaBien</title>
    <style>
        @page { margin: 25px 25px 35px 25px; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 9.5px; color: #1e293b; line-height: 1.25; }
        .header-table { width: 100%; border-bottom: 2px solid #0284c7; padding-bottom: 8px; margin-bottom: 10px; }
        .pharmacy-title { font-size: 16px; font-weight: bold; color: #059669; letter-spacing: 0.5px; }
        .pharmacy-subtitle { font-size: 8px; color: #64748b; margin-top: 1px; }
        .pharmacy-details { font-size: 7.5px; color: #475569; margin-top: 2px; line-height: 1.2; }
        .report-box { border: 1.5px solid #0284c7; background-color: #f0f9ff; padding: 6px 10px; text-align: center; border-radius: 4px; }
        .report-title { font-size: 10px; font-weight: bold; color: #0369a1; text-transform: uppercase; }
        .report-meta { font-size: 7.5px; color: #0284c7; margin-top: 2px; }
        
        .filters-box { background-color: #f8fafc; border: 1px solid #cbd5e1; padding: 5px 8px; border-radius: 4px; margin-bottom: 10px; font-size: 8px; }
        .filter-item { display: inline-block; margin-right: 12px; }
        .filter-label { font-weight: bold; color: #475569; }
        
        .kpi-table { width: 100%; margin-bottom: 10px; border-collapse: separate; border-spacing: 4px 0; }
        .kpi-card { background-color: #f8fafc; border: 1px solid #cbd5e1; border-radius: 4px; padding: 5px; text-align: center; }
        .kpi-label { font-size: 7.5px; font-weight: bold; color: #64748b; text-transform: uppercase; }
        .kpi-value { font-size: 12px; font-weight: bold; color: #0f172a; margin-top: 2px; }
        
        .data-table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        .data-table th { background-color: #0284c7; color: #ffffff; font-size: 8px; font-weight: bold; text-transform: uppercase; padding: 4px 5px; text-align: left; border: 1px solid #0284c7; }
        .data-table td { padding: 3.5px 5px; border: 1px solid #e2e8f0; font-size: 8px; }
        .data-table tr:nth-child(even) { background-color: #f8fafc; }
        .data-table tfoot td { background-color: #f1f5f9; font-weight: bold; font-size: 8.5px; border-top: 1.5px solid #0284c7; }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .text-emerald { color: #059669; }
        .text-sky { color: #0284c7; }
        .badge { display: inline-block; padding: 1px 4px; font-size: 7px; font-weight: bold; border-radius: 2px; text-transform: uppercase; }
        .badge-success { background-color: #d1fae5; color: #065f46; }
        .badge-warning { background-color: #fef3c7; color: #92400e; }
        .badge-danger { background-color: #ffe4e6; color: #9f1239; }
        .badge-slate { background-color: #f1f5f9; color: #475569; }
        
        .footer-table { position: fixed; bottom: -20px; left: 0; right: 0; width: 100%; border-top: 1px solid #e2e8f0; padding-top: 4px; font-size: 7px; color: #94a3b8; }
    </style>
</head>
<body>

    {{-- Encabezado Institucional --}}
    @include('reportes.pdf.header', ['tituloReporte' => 'CONTROL Y DISPENSACIÓN DE RECETAS MÉDICAS'])

    {{-- Filtros --}}
    <div class="filters-box">
        <span class="filter-item"><span class="filter-label">Período:</span> {{ \Carbon\Carbon::parse($fechaDesde)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($fechaHasta)->format('d/m/Y') }}</span>
        <span class="filter-item"><span class="filter-label">Filtro Estado:</span> {{ ucfirst($estadoFiltro ?: 'Todos') }}</span>
        <span class="filter-item"><span class="filter-label">Recetas Registradas:</span> {{ number_format($totalRecetas) }}</span>
    </div>

    {{-- KPI Cards --}}
    <table class="kpi-table">
        <tr>
            <td class="kpi-card" style="width: 20%;">
                <div class="kpi-label">Total Recetas</div>
                <div class="kpi-value text-sky">{{ number_format($totalRecetas) }}</div>
            </td>
            <td class="kpi-card" style="width: 20%;">
                <div class="kpi-label">Procesadas</div>
                <div class="kpi-value text-emerald">{{ number_format($procesadas) }}</div>
            </td>
            <td class="kpi-card" style="width: 20%;">
                <div class="kpi-label">Pendientes</div>
                <div class="kpi-value" style="color: #d97706;">{{ number_format($pendientes) }}</div>
            </td>
            <td class="kpi-card" style="width: 20%;">
                <div class="kpi-label">Vencidas</div>
                <div class="kpi-value" style="color: #e11d48;">{{ number_format($vencidas) }}</div>
            </td>
            <td class="kpi-card" style="width: 20%;">
                <div class="kpi-label">Rechazadas</div>
                <div class="kpi-value" style="color: #64748b;">{{ number_format($rechazadas) }}</div>
            </td>
        </tr>
    </table>

    {{-- Tabla de Recetas --}}
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 10%;">N° Receta</th>
                <th style="width: 25%;">Paciente</th>
                <th style="width: 20%;">Médico / Especialidad</th>
                <th style="width: 12%;">Tipo</th>
                <th style="width: 11%;">Emisión</th>
                <th style="width: 11%;">Vencimiento</th>
                <th class="text-center" style="width: 11%;">Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recetas as $r)
            @php
                $stClass = match($r->estado) {
                    'procesada' => 'badge-success',
                    'pendiente' => 'badge-warning',
                    'vencida'   => 'badge-danger',
                    default     => 'badge-slate'
                };
            @endphp
            <tr>
                <td class="font-bold">{{ $r->numero_receta ?? ('#' . str_pad($r->id, 5, '0', STR_PAD_LEFT)) }}</td>
                <td>
                    <span class="font-bold">{{ $r->paciente_nombre ?? ($r->cliente?->nombre ?? '—') }}</span>
                    @if($r->paciente_documento)
                    <br><span style="color: #64748b; font-size: 7px;">Doc: {{ $r->paciente_documento }}</span>
                    @endif
                </td>
                <td>
                    {{ $r->medico_nombre ?? '—' }}
                    @if($r->medico_especialidad)
                    <br><span style="color: #64748b; font-size: 7px;">{{ $r->medico_especialidad }}</span>
                    @endif
                </td>
                <td>{{ ucfirst($r->tipo_receta ?? '—') }}</td>
                <td>{{ $r->fecha_emision ? \Carbon\Carbon::parse($r->fecha_emision)->format('d/m/Y') : $r->created_at->format('d/m/Y') }}</td>
                <td>{{ $r->fecha_vencimiento ? \Carbon\Carbon::parse($r->fecha_vencimiento)->format('d/m/Y') : '—' }}</td>
                <td class="text-center"><span class="badge {{ $stClass }}">{{ ucfirst($r->estado) }}</span></td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center" style="padding: 15px;">No se registraron recetas médicas en el período.</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="font-bold">TOTAL REGISTROS ({{ number_format($recetas->count()) }})</td>
                <td colspan="2" class="text-right font-bold text-sky">{{ $procesadas }} procesadas · {{ $pendientes }} pendientes</td>
            </tr>
        </tfoot>
    </table>

    {{-- Pie de página institucional --}}
    <table class="footer-table">
        <tr>
            <td style="width: 50%;">FarmaBien — Sistema de Control y Gestión Farmacéutica</td>
            <td style="width: 50%; text-align: right;">Documento de control farmacológico y dispensación médica</td>
        </tr>
    </table>

</body>
</html>
