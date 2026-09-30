<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Auditoría y Trazabilidad - FarmaBien</title>
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
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .text-emerald { color: #059669; }
        .badge { display: inline-block; padding: 1px 3px; font-size: 6.5px; font-weight: bold; border-radius: 2px; text-transform: uppercase; }
        .badge-success { background-color: #d1fae5; color: #065f46; }
        .badge-warning { background-color: #fef3c7; color: #92400e; }
        .badge-danger { background-color: #ffe4e6; color: #9f1239; }
        .badge-info { background-color: #e0e7ff; color: #3730a3; }
        
        .footer-table { position: fixed; bottom: -15px; left: 0; right: 0; width: 100%; border-top: 1px solid #e2e8f0; padding-top: 3px; font-size: 7px; color: #94a3b8; }
    </style>
</head>
<body>

    {{-- Encabezado Institucional --}}
    @include('reportes.pdf.header', ['tituloReporte' => 'REPORTE DE AUDITORÍA Y TRAZABILIDAD DEL SISTEMA'])

    {{-- Resumen de Filtros --}}
    <div class="filters-box">
        <span class="filter-item"><span class="filter-label">Período:</span> {{ \Carbon\Carbon::parse($fechaDesde)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($fechaHasta)->format('d/m/Y') }}</span>
        <span class="filter-item"><span class="filter-label">Módulo:</span> {{ ucfirst($modulo ?: 'Todos') }}</span>
        <span class="filter-item"><span class="filter-label">Acción:</span> {{ strtoupper($accion ?: 'Todas') }}</span>
        <span class="filter-item"><span class="filter-label">Usuario:</span> {{ $userId ? (\App\Models\User::find($userId)?->name ?? 'N/A') : 'Todos' }}</span>
        <span class="filter-item"><span class="filter-label">Total Eventos:</span> {{ number_format($totalLogs) }}</span>
    </div>

    {{-- KPI Cards --}}
    <table class="kpi-table">
        <tr>
            <td class="kpi-card" style="width: 25%;">
                <div class="kpi-label">Total Eventos</div>
                <div class="kpi-value text-emerald">{{ number_format($totalLogs) }}</div>
            </td>
            <td class="kpi-card" style="width: 25%;">
                <div class="kpi-label">Usuarios Activos</div>
                <div class="kpi-value">{{ number_format($usuariosActivos) }}</div>
            </td>
            <td class="kpi-card" style="width: 25%;">
                <div class="kpi-label">Módulos Auditados</div>
                <div class="kpi-value">{{ number_format($modulosAuditados) }}</div>
            </td>
            <td class="kpi-card" style="width: 25%;">
                <div class="kpi-label">Acciones Críticas</div>
                <div class="kpi-value" style="color: #d97706;">{{ number_format($accionesCriticas) }}</div>
            </td>
        </tr>
    </table>

    {{-- Tabla de Logs --}}
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 6%;">ID</th>
                <th style="width: 14%;">Fecha y Hora</th>
                <th style="width: 18%;">Usuario</th>
                <th style="width: 12%;">Módulo</th>
                <th style="width: 12%;">Acción</th>
                <th style="width: 26%;">Descripción</th>
                <th style="width: 12%;">IP Origen</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $l)
            @php
                $actUpper = strtoupper($l->accion ?? '');
                $bClass = match(true) {
                    str_contains($actUpper, 'CREAR') || str_contains($actUpper, 'STORE') => 'badge-success',
                    str_contains($actUpper, 'EDIT') || str_contains($actUpper, 'UPDATE') => 'badge-warning',
                    str_contains($actUpper, 'ELIMINAR') || str_contains($actUpper, 'DELETE') || str_contains($actUpper, 'ANULAR') => 'badge-danger',
                    default => 'badge-info',
                };
            @endphp
            <tr>
                <td class="font-bold">#{{ $l->id }}</td>
                <td>{{ $l->created_at ? $l->created_at->format('d/m/Y H:i:s') : 'N/A' }}</td>
                <td>{{ $l->user?->name ?? 'Sistema' }}</td>
                <td><span class="badge badge-info">{{ ucfirst($l->modulo ?? 'General') }}</span></td>
                <td><span class="badge {{ $bClass }}">{{ strtoupper($l->accion ?? 'EVENTO') }}</span></td>
                <td>{{ $l->descripcion }}</td>
                <td style="font-family: monospace;">{{ $l->ip ?? '127.0.0.1' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center" style="padding: 12px;">No se registraron eventos de auditoría para los criterios seleccionados.</td>
            </tr>
            @endforelse
        </tbody>
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
