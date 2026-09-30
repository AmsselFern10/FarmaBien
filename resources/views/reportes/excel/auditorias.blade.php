<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
@verbatim
<!--[if gte mso 9]>
<xml>
 <x:ExcelWorkbook>
  <x:ExcelWorksheets>
   <x:ExcelWorksheet>
    <x:Name>Auditoría del Sistema</x:Name>
    <x:WorksheetOptions>
     <x:DisplayGridlines/>
    </x:WorksheetOptions>
   </x:ExcelWorksheet>
  </x:ExcelWorksheets>
 </x:ExcelWorkbook>
</xml>
<![endif]-->
@endverbatim
<style>
  body, table { font-family: Calibri, Arial, Helvetica, sans-serif; font-size: 11pt; color: #1e293b; }
  table { border-collapse: collapse; width: 100%; }
  .header-brand { background-color: #047857; color: #ffffff; font-size: 16pt; font-weight: bold; text-align: center; height: 35pt; vertical-align: middle; border: 1px solid #065f46; }
  .header-sub { background-color: #065f46; color: #d1fae5; font-size: 9.5pt; text-align: center; height: 18pt; vertical-align: middle; border: 1px solid #044e39; }
  .report-title { background-color: #ecfdf5; color: #047857; font-size: 13pt; font-weight: bold; text-align: center; height: 26pt; vertical-align: middle; border: 1px solid #a7f3d0; }
  .meta-cell { background-color: #f8fafc; color: #475569; font-size: 9pt; padding: 4px 8px; border: 1px solid #cbd5e1; }
  .kpi-title { background-color: #f1f5f9; color: #334155; font-size: 9.5pt; font-weight: bold; text-align: center; border: 1px solid #94a3b8; height: 20pt; vertical-align: middle; }
  .kpi-val { background-color: #ffffff; color: #0f172a; font-size: 13pt; font-weight: bold; text-align: center; border: 1px solid #94a3b8; height: 24pt; vertical-align: middle; }
  .th-col { background-color: #047857; color: #ffffff; font-weight: bold; font-size: 10.5pt; text-align: center; border: 1px solid #065f46; height: 24pt; vertical-align: middle; }
  .td-text { mso-number-format: "\@"; padding: 6px 8px; border: 1px solid #cbd5e1; vertical-align: middle; }
  .td-center { text-align: center; padding: 6px 8px; border: 1px solid #cbd5e1; vertical-align: middle; }
  .td-num { mso-number-format: "\#,##0"; text-align: right; padding: 6px 8px; border: 1px solid #cbd5e1; vertical-align: middle; }
  .td-date { mso-number-format: "yyyy-mm-dd hh:mm:ss"; text-align: center; padding: 6px 8px; border: 1px solid #cbd5e1; vertical-align: middle; }
  .row-even { background-color: #f8fafc; }
  .row-odd { background-color: #ffffff; }
  .total-row { background-color: #e2e8f0; font-weight: bold; border-top: 2px solid #047857; border-bottom: 2px double #047857; height: 25pt; vertical-align: middle; }
</style>
</head>
<body>
<table>
  <colgroup>
    <col style="width: 60pt;">
    <col style="width: 120pt;">
    <col style="width: 130pt;">
    <col style="width: 150pt;">
    <col style="width: 100pt;">
    <col style="width: 100pt;">
    <col style="width: 250pt;">
    <col style="width: 100pt;">
  </colgroup>
  <tr>
    <td colspan="8" class="header-brand">{{ strtoupper(configuracion('empresa_nombre', 'FARMABIEN')) }} - {{ strtoupper(configuracion('empresa_razon_social', 'Farmacia & Droguería FarmaBien C.A.')) }}</td>
  </tr>
  <tr>
    <td colspan="8" class="header-sub">RIF / RUC: {{ configuracion('empresa_ruc', 'J-40892154-0') }} &bull; Tel: {{ configuracion('empresa_telefono', '(0212) 555-0199') }}</td>
  </tr>
  <tr>
    <td colspan="8" class="report-title">REPORTE DE AUDITORÍA Y TRAZABILIDAD OPERATIVA</td>
  </tr>
  <tr>
    <td colspan="4" class="meta-cell"><strong>Per&iacute;odo:</strong> {{ \Carbon\Carbon::parse($fechaDesde)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($fechaHasta)->format('d/m/Y') }}</td>
    <td colspan="4" class="meta-cell"><strong>Emisi&oacute;n:</strong> {{ now()->format('d/m/Y H:i:s') }} | <strong>Usuario:</strong> {{ auth()->user()->name ?? 'Sistema' }}</td>
  </tr>
  <tr>
    <td colspan="8" class="meta-cell"><strong>Filtros aplicados:</strong> M&oacute;dulo: {{ ucfirst($modulo ?: 'Todos') }} | Acci&oacute;n: {{ strtoupper($accion ?: 'Todas') }} | Usuario: {{ $userId ? 'ID #'.$userId : 'Todos' }}</td>
  </tr>

  <tr><td colspan="8" style="height: 10pt;"></td></tr>

  <tr>
    <td colspan="2" class="kpi-title">TOTAL EVENTOS</td>
    <td colspan="2" class="kpi-title">USUARIOS ACTIVOS</td>
    <td colspan="2" class="kpi-title">MÓDULOS AUDITADOS</td>
    <td colspan="2" class="kpi-title">ACCIONES CRÍTICAS</td>
  </tr>
  <tr>
    <td colspan="2" class="kpi-val td-num">{{ number_format($totalLogs) }}</td>
    <td colspan="2" class="kpi-val td-num">{{ number_format($usuariosActivos) }}</td>
    <td colspan="2" class="kpi-val td-num">{{ number_format($modulosAuditados) }}</td>
    <td colspan="2" class="kpi-val td-num">{{ number_format($accionesCriticas) }}</td>
  </tr>

  <tr><td colspan="8" style="height: 12pt;"></td></tr>

  <thead>
    <tr>
      <th class="th-col">ID</th>
      <th class="th-col">FECHA Y HORA</th>
      <th class="th-col">USUARIO</th>
      <th class="th-col">EMAIL</th>
      <th class="th-col">MÓDULO</th>
      <th class="th-col">ACCIÓN</th>
      <th class="th-col">DESCRIPCIÓN</th>
      <th class="th-col">DIRECCIÓN IP</th>
    </tr>
  </thead>
  <tbody>
    @forelse($logs as $index => $l)
      @php $rowClass = ($index % 2 == 0) ? 'row-even' : 'row-odd'; @endphp
      <tr class="{{ $rowClass }}">
        <td class="td-num td-center">#{{ $l->id }}</td>
        <td class="td-date">{{ $l->created_at ? $l->created_at->format('Y-m-d H:i:s') : '' }}</td>
        <td class="td-text">{{ $l->user?->name ?? 'Sistema / Cron' }}</td>
        <td class="td-text">{{ $l->user?->email ?? 'N/A' }}</td>
        <td class="td-text td-center">{{ ucfirst($l->modulo ?? 'General') }}</td>
        <td class="td-text td-center">{{ strtoupper($l->accion ?? 'EVENTO') }}</td>
        <td class="td-text">{{ $l->descripcion }}</td>
        <td class="td-text td-center">{{ $l->ip ?? '127.0.0.1' }}</td>
      </tr>
    @empty
      <tr>
        <td colspan="8" class="td-center" style="padding: 15pt; color: #64748b;">No se encontraron registros de auditoría para los filtros seleccionados.</td>
      </tr>
    @endforelse
  </tbody>
  <tfoot>
    <tr class="total-row">
      <td colspan="8" style="text-align: center;">FIN DEL INFORME &bull; TOTAL REGISTROS: {{ number_format($logs->count()) }}</td>
    </tr>
  </tfoot>
</table>
</body>
</html>
