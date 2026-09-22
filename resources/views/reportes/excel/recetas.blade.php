<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
@verbatim
<!--[if gte mso 9]>
<xml>
 <x:ExcelWorkbook>
  <x:ExcelWorksheets>
   <x:ExcelWorksheet>
    <x:Name>Control de Recetas</x:Name>
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
  .kpi-title { background-color: #f1f5f9; color: #334155; font-size: 9pt; font-weight: bold; text-align: center; border: 1px solid #94a3b8; height: 20pt; vertical-align: middle; }
  .kpi-val { background-color: #ffffff; color: #0f172a; font-size: 12pt; font-weight: bold; text-align: center; border: 1px solid #94a3b8; height: 24pt; vertical-align: middle; }
  .th-col { background-color: #047857; color: #ffffff; font-weight: bold; font-size: 10pt; text-align: center; border: 1px solid #065f46; height: 24pt; vertical-align: middle; }
  .td-text { mso-number-format: "\@"; padding: 6px 8px; border: 1px solid #cbd5e1; vertical-align: middle; }
  .td-center { text-align: center; padding: 6px 8px; border: 1px solid #cbd5e1; vertical-align: middle; }
  .td-num { mso-number-format: "\#,##0"; text-align: right; padding: 6px 8px; border: 1px solid #cbd5e1; vertical-align: middle; }
  .td-date { mso-number-format: "yyyy-mm-dd"; text-align: center; padding: 6px 8px; border: 1px solid #cbd5e1; vertical-align: middle; }
  .row-even { background-color: #f8fafc; }
  .row-odd { background-color: #ffffff; }
  .badge-procesada { background-color: #dcfce7; color: #15803d; font-weight: bold; text-align: center; }
  .badge-pendiente { background-color: #fef9c3; color: #854d0e; font-weight: bold; text-align: center; }
  .badge-vencida { background-color: #fee2e2; color: #b91c1c; font-weight: bold; text-align: center; }
  .badge-rechazada { background-color: #f1f5f9; color: #475569; font-weight: bold; text-align: center; }
  .total-row { background-color: #e2e8f0; font-weight: bold; border-top: 2px solid #047857; border-bottom: 2px double #047857; height: 25pt; vertical-align: middle; }
</style>
</head>
<body>
<table>
  <colgroup>
    <col style="width: 80pt;">
    <col style="width: 150pt;">
    <col style="width: 95pt;">
    <col style="width: 130pt;">
    <col style="width: 110pt;">
    <col style="width: 130pt;">
    <col style="width: 90pt;">
    <col style="width: 85pt;">
    <col style="width: 85pt;">
    <col style="width: 90pt;">
  </colgroup>
  <tr>
    <td colspan="10" class="header-brand">{{ strtoupper(configuracion('empresa_nombre', 'FARMABIEN')) }} - {{ strtoupper(configuracion('empresa_razon_social', 'Farmacia & Droguería FarmaBien C.A.')) }}</td>
  </tr>
  <tr>
    <td colspan="10" class="header-sub">RIF / RUC: {{ configuracion('empresa_ruc', 'J-40892154-0') }} &bull; Tel: {{ configuracion('empresa_telefono', '(0212) 555-0199') }} &bull; {{ configuracion('empresa_direccion', 'Caracas') }}</td>
  </tr>
  <tr>
    <td colspan="10" class="report-title">REPORTE AUDITOR&Iacute;A Y CONTROL DE RECETAS M&Eacute;DICAS</td>
  </tr>
  <tr>
    <td colspan="5" class="meta-cell"><strong>Per&iacute;odo:</strong> {{ \Carbon\Carbon::parse($fechaDesde)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($fechaHasta)->format('d/m/Y') }}</td>
    <td colspan="5" class="meta-cell"><strong>Emisi&oacute;n:</strong> {{ now()->format('d/m/Y H:i:s') }}</td>
  </tr>

  <!-- Separador -->
  <tr><td colspan="10" style="height: 10pt;"></td></tr>

  <!-- KPIs -->
  <tr>
    <td colspan="2" class="kpi-title">TOTAL RECETAS</td>
    <td colspan="2" class="kpi-title">PROCESADAS / DISPENSADAS</td>
    <td colspan="2" class="kpi-title">PENDIENTES</td>
    <td colspan="2" class="kpi-title">VENCIDAS</td>
    <td colspan="2" class="kpi-title">RECHAZADAS</td>
  </tr>
  <tr>
    <td colspan="2" class="kpi-val td-num">{{ $totalRecetas }}</td>
    <td colspan="2" class="kpi-val td-num" style="color: #15803d;">{{ $procesadas }}</td>
    <td colspan="2" class="kpi-val td-num" style="color: #854d0e;">{{ $pendientes }}</td>
    <td colspan="2" class="kpi-val td-num" style="color: #b91c1c;">{{ $vencidas }}</td>
    <td colspan="2" class="kpi-val td-num" style="color: #475569;">{{ $rechazadas }}</td>
  </tr>

  <!-- Separador -->
  <tr><td colspan="10" style="height: 12pt;"></td></tr>

  <!-- Encabezados de Columnas -->
  <thead>
    <tr>
      <th class="th-col" style="width: 80pt;">N&deg; RECETA</th>
      <th class="th-col" style="width: 150pt;">PACIENTE</th>
      <th class="th-col" style="width: 95pt;">DOCUMENTO</th>
      <th class="th-col" style="width: 130pt;">M&Eacute;DICO TRATANTE</th>
      <th class="th-col" style="width: 110pt;">ESPECIALIDAD</th>
      <th class="th-col" style="width: 130pt;">INSTITUCI&Oacute;N</th>
      <th class="th-col" style="width: 90pt;">TIPO RECETA</th>
      <th class="th-col" style="width: 85pt;">F. EMISI&Oacute;N</th>
      <th class="th-col" style="width: 85pt;">F. VENCIMIENTO</th>
      <th class="th-col" style="width: 90pt;">ESTADO</th>
    </tr>
  </thead>
  <tbody>
    @forelse($recetas as $index => $r)
      @php
        $rowClass = ($index % 2 == 0) ? 'row-even' : 'row-odd';
        $badgeClass = 'badge-' . ($r->estado ?? 'pendiente');
      @endphp
      <tr class="{{ $rowClass }}">
        <td class="td-text td-center">{{ $r->numero_receta ?? ('#' . str_pad($r->id, 5, '0', STR_PAD_LEFT)) }}</td>
        <td class="td-text" style="font-weight: bold;">{{ $r->paciente_nombre ?? ($r->cliente->nombre ?? 'N/A') }}</td>
        <td class="td-text td-center">{{ $r->paciente_documento ?? ($r->cliente->documento ?? 'N/A') }}</td>
        <td class="td-text">{{ $r->medico_nombre ?? 'N/A' }}</td>
        <td class="td-text">{{ $r->medico_especialidad ?? 'N/A' }}</td>
        <td class="td-text">{{ $r->institucion_salud ?? 'N/A' }}</td>
        <td class="td-text td-center">{{ ucfirst($r->tipo_receta ?? 'N/A') }}</td>
        <td class="td-date">{{ $r->fecha_emision ? \Carbon\Carbon::parse($r->fecha_emision)->format('Y-m-d') : '' }}</td>
        <td class="td-date">{{ $r->fecha_vencimiento ? \Carbon\Carbon::parse($r->fecha_vencimiento)->format('Y-m-d') : '' }}</td>
        <td class="{{ $badgeClass }} td-center">{{ strtoupper($r->estado ?? 'N/A') }}</td>
      </tr>
    @empty
      <tr>
        <td colspan="10" class="td-center" style="padding: 15pt; color: #64748b;">No se encontraron recetas m&eacute;dicas para los filtros seleccionados.</td>
      </tr>
    @endforelse
  </tbody>
  <tfoot>
    <tr class="total-row">
      <td colspan="9" style="text-align: right; padding-right: 10pt;">TOTAL DE REGISTROS:</td>
      <td class="td-num">{{ count($recetas) }}</td>
    </tr>
  </tfoot>
</table>
</body>
</html>
