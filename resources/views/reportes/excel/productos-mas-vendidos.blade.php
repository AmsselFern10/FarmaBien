<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
@verbatim
<!--[if gte mso 9]>
<xml>
 <x:ExcelWorkbook>
  <x:ExcelWorksheets>
   <x:ExcelWorksheet>
    <x:Name>Medicamentos Más Vendidos</x:Name>
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
  .td-money { mso-number-format: "\$#,##0.00"; text-align: right; padding: 6px 8px; border: 1px solid #cbd5e1; vertical-align: middle; }
  .row-even { background-color: #f8fafc; }
  .row-odd { background-color: #ffffff; }
  .pos-1 { background-color: #fef9c3; font-weight: bold; text-align: center; }
  .pos-2 { background-color: #f1f5f9; font-weight: bold; text-align: center; }
  .pos-3 { background-color: #ffedd5; font-weight: bold; text-align: center; }
  .total-row { background-color: #e2e8f0; font-weight: bold; border-top: 2px solid #047857; border-bottom: 2px double #047857; height: 25pt; vertical-align: middle; }
</style>
</head>
<body>
<table>
  <colgroup>
    <col style="width: 60pt;">
    <col style="width: 220pt;">
    <col style="width: 180pt;">
    <col style="width: 120pt;">
    <col style="width: 130pt;">
  </colgroup>
  <tr>
    <td colspan="5" class="header-brand">{{ strtoupper(configuracion('empresa_nombre', 'FARMABIEN')) }} - {{ strtoupper(configuracion('empresa_razon_social', 'Farmacia & Droguería FarmaBien C.A.')) }}</td>
  </tr>
  <tr>
    <td colspan="5" class="header-sub">RIF / RUC: {{ configuracion('empresa_ruc', 'J-40892154-0') }} &bull; Tel: {{ configuracion('empresa_telefono', '(0212) 555-0199') }} &bull; {{ configuracion('empresa_direccion', 'Caracas') }}</td>
  </tr>
  <tr>
    <td colspan="5" class="report-title">RANKING TOP 20: MEDICAMENTOS M&Aacute;S VENDIDOS</td>
  </tr>
  <tr>
    <td colspan="3" class="meta-cell"><strong>Per&iacute;odo:</strong> {{ \Carbon\Carbon::parse($fechaDesde)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($fechaHasta)->format('d/m/Y') }}</td>
    <td colspan="2" class="meta-cell"><strong>Emisi&oacute;n:</strong> {{ now()->format('d/m/Y H:i:s') }}</td>
  </tr>

  <!-- Separador -->
  <tr><td colspan="5" style="height: 10pt;"></td></tr>

  <!-- KPIs -->
  <tr>
    <td colspan="2" class="kpi-title">TOTAL UNIDADES DESPACHADAS</td>
    <td colspan="3" class="kpi-title">INGRESOS TOTALES TOP 20</td>
  </tr>
  <tr>
    <td colspan="2" class="kpi-val td-num">{{ $ranking->sum('total_unidades_vendidas') }}</td>
    <td colspan="3" class="kpi-val td-money">{{ number_format($ranking->sum('total_ingresos'), 2, '.', '') }}</td>
  </tr>

  <!-- Separador -->
  <tr><td colspan="5" style="height: 12pt;"></td></tr>

  <!-- Encabezados de Columnas -->
  <thead>
    <tr>
      <th class="th-col" style="width: 60pt;">POSICI&Oacute;N</th>
      <th class="th-col" style="width: 220pt;">MEDICAMENTO / PRODUCTO</th>
      <th class="th-col" style="width: 180pt;">PRINCIPIO ACTIVO</th>
      <th class="th-col" style="width: 120pt;">UNIDADES VENDIDAS</th>
      <th class="th-col" style="width: 130pt;">INGRESOS GENERADOS ($)</th>
    </tr>
  </thead>
  <tbody>
    @php
      $sumUnidades = 0;
      $sumIngresos = 0;
    @endphp
    @forelse($ranking as $index => $item)
      @php
        $sumUnidades += $item->total_unidades_vendidas;
        $sumIngresos += $item->total_ingresos;
        $rowClass = ($index % 2 == 0) ? 'row-even' : 'row-odd';
        $posClass = $index === 0 ? 'pos-1' : ($index === 1 ? 'pos-2' : ($index === 2 ? 'pos-3' : 'td-center'));
      @endphp
      <tr class="{{ $rowClass }}">
        <td class="{{ $posClass }} td-num">#{{ $index + 1 }}</td>
        <td class="td-text" style="font-weight: bold;">{{ $item->nombre }}</td>
        <td class="td-text">{{ $item->principio_activo ?? 'N/A' }}</td>
        <td class="td-num">{{ $item->total_unidades_vendidas }}</td>
        <td class="td-money">{{ number_format($item->total_ingresos, 2, '.', '') }}</td>
      </tr>
    @empty
      <tr>
        <td colspan="5" class="td-center" style="padding: 15pt; color: #64748b;">No hay ventas registradas en el per&iacute;odo seleccionado.</td>
      </tr>
    @endforelse
  </tbody>
  <tfoot>
    <tr class="total-row">
      <td colspan="3" style="text-align: right; padding-right: 10pt;">TOTALES DEL RANKING:</td>
      <td class="td-num">{{ $sumUnidades }}</td>
      <td class="td-money">{{ number_format($sumIngresos, 2, '.', '') }}</td>
    </tr>
  </tfoot>
</table>
</body>
</html>
