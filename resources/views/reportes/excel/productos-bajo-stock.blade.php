<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
@verbatim
<!--[if gte mso 9]>
<xml>
 <x:ExcelWorkbook>
  <x:ExcelWorksheets>
   <x:ExcelWorksheet>
    <x:Name>Alertas de Stock</x:Name>
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
  .report-title { background-color: #fef2f2; color: #b91c1c; font-size: 13pt; font-weight: bold; text-align: center; height: 26pt; vertical-align: middle; border: 1px solid #fecaca; }
  .meta-cell { background-color: #f8fafc; color: #475569; font-size: 9pt; padding: 4px 8px; border: 1px solid #cbd5e1; }
  .kpi-title { background-color: #f1f5f9; color: #334155; font-size: 9.5pt; font-weight: bold; text-align: center; border: 1px solid #94a3b8; height: 20pt; vertical-align: middle; }
  .kpi-val { background-color: #ffffff; color: #0f172a; font-size: 13pt; font-weight: bold; text-align: center; border: 1px solid #94a3b8; height: 24pt; vertical-align: middle; }
  .th-col { background-color: #047857; color: #ffffff; font-weight: bold; font-size: 10.5pt; text-align: center; border: 1px solid #065f46; height: 24pt; vertical-align: middle; }
  .td-text { mso-number-format: "\@"; padding: 6px 8px; border: 1px solid #cbd5e1; vertical-align: middle; }
  .td-center { text-align: center; padding: 6px 8px; border: 1px solid #cbd5e1; vertical-align: middle; }
  .td-num { mso-number-format: "\#,##0"; text-align: right; padding: 6px 8px; border: 1px solid #cbd5e1; vertical-align: middle; }
  .row-even { background-color: #f8fafc; }
  .row-odd { background-color: #ffffff; }
  .badge-agotado { background-color: #fee2e2; color: #991b1b; font-weight: bold; text-align: center; }
  .badge-bajo { background-color: #ffedd5; color: #9a3412; font-weight: bold; text-align: center; }
  .total-row { background-color: #e2e8f0; font-weight: bold; border-top: 2px solid #047857; border-bottom: 2px double #047857; height: 25pt; vertical-align: middle; }
</style>
</head>
<body>
<table>
  <colgroup>
    <col style="width: 170pt;">
    <col style="width: 140pt;">
    <col style="width: 110pt;">
    <col style="width: 110pt;">
    <col style="width: 90pt;">
    <col style="width: 90pt;">
    <col style="width: 90pt;">
    <col style="width: 100pt;">
  </colgroup>
  <tr>
    <td colspan="8" class="header-brand">FARMABIEN - FARMACIA &amp; DROGUER&Iacute;A C.A.</td>
  </tr>
  <tr>
    <td colspan="8" class="header-sub">RIF / RUC: J-40892154-0 &bull; Tel: (0212) 555-0199 &bull; Av. Principal Los Pr&oacute;ceres, Caracas</td>
  </tr>
  <tr>
    <td colspan="8" class="report-title">ALERTA CR&Iacute;TICA: MEDICAMENTOS CON STOCK M&Iacute;NIMO O AGOTADOS</td>
  </tr>
  <tr>
    <td colspan="4" class="meta-cell"><strong>Fecha de Control:</strong> {{ now()->format('d/m/Y H:i:s') }}</td>
    <td colspan="4" class="meta-cell"><strong>Emitido por:</strong> {{ auth()->user()->name ?? 'Sistema' }}</td>
  </tr>

  <!-- Separador -->
  <tr><td colspan="8" style="height: 10pt;"></td></tr>

  <!-- KPIs -->
  <tr>
    <td colspan="2" class="kpi-title">TOTAL ALERTAS</td>
    <td colspan="2" class="kpi-title">PRODUCTOS AGOTADOS (0 STOCK)</td>
    <td colspan="2" class="kpi-title">BAJO NIVEL M&Iacute;NIMO</td>
    <td colspan="2" class="kpi-title">D&Eacute;FICIT TOTAL UNIDADES</td>
  </tr>
  <tr>
    <td colspan="2" class="kpi-val td-num" style="color: #b91c1c;">{{ count($productos) }}</td>
    <td colspan="2" class="kpi-val td-num" style="color: #dc2626;">{{ collect($productos)->where('stock_disponible', '<=', 0)->count() }}</td>
    <td colspan="2" class="kpi-val td-num" style="color: #ea580c;">{{ collect($productos)->where('stock_disponible', '>', 0)->count() }}</td>
    <td colspan="2" class="kpi-val td-num">{{ collect($productos)->sum(fn($p) => max(0, ($p->stock_minimo ?? 0) - ($p->stock_disponible ?? 0))) }}</td>
  </tr>

  <!-- Separador -->
  <tr><td colspan="8" style="height: 12pt;"></td></tr>

  <!-- Encabezados de Columnas -->
  <thead>
    <tr>
      <th class="th-col" style="width: 170pt;">MEDICAMENTO</th>
      <th class="th-col" style="width: 140pt;">PRINCIPIO ACTIVO</th>
      <th class="th-col" style="width: 110pt;">CATEGOR&Iacute;A</th>
      <th class="th-col" style="width: 110pt;">LABORATORIO</th>
      <th class="th-col" style="width: 90pt;">STOCK ACTUAL</th>
      <th class="th-col" style="width: 90pt;">STOCK M&Iacute;NIMO</th>
      <th class="th-col" style="width: 90pt;">D&Eacute;FICIT (UNID)</th>
      <th class="th-col" style="width: 100pt;">ESTADO CR&Iacute;TICO</th>
    </tr>
  </thead>
  <tbody>
    @php
      $sumActual = 0;
      $sumMinimo = 0;
      $sumDeficit = 0;
    @endphp
    @forelse($productos as $index => $p)
      @php
        $sa = $p->stock_disponible ?? 0;
        $sm = $p->stock_minimo ?? 0;
        $def = max(0, $sm - $sa);
        $sumActual += $sa;
        $sumMinimo += $sm;
        $sumDeficit += $def;
        $rowClass = ($index % 2 == 0) ? 'row-even' : 'row-odd';
        $badgeClass = ($sa <= 0) ? 'badge-agotado' : 'badge-bajo';
        $estadoTexto = ($sa <= 0) ? 'AGOTADO' : 'BAJO MÍNIMO';
      @endphp
      <tr class="{{ $rowClass }}">
        <td class="td-text" style="font-weight: bold;">{{ $p->nombre }}</td>
        <td class="td-text">{{ $p->principio_activo ?? 'N/A' }}</td>
        <td class="td-text">{{ $p->categoria->nombre ?? 'Sin categoría' }}</td>
        <td class="td-text">{{ $p->laboratorio->nombre ?? 'Sin laboratorio' }}</td>
        <td class="td-num" style="font-weight: bold; color: {{ $sa <= 0 ? '#dc2626' : '#ea580c' }};">{{ $sa }}</td>
        <td class="td-num">{{ $sm }}</td>
        <td class="td-num" style="font-weight: bold; color: #b91c1c;">+{{ $def }}</td>
        <td class="{{ $badgeClass }} td-center">{{ $estadoTexto }}</td>
      </tr>
    @empty
      <tr>
        <td colspan="8" class="td-center" style="padding: 15pt; color: #166534; background-color: #f0fdf4;">&iexcl;Excelente! Todos los medicamentos se encuentran por encima del stock m&iacute;nimo.</td>
      </tr>
    @endforelse
  </tbody>
  <tfoot>
    <tr class="total-row">
      <td colspan="4" style="text-align: right; padding-right: 10pt;">TOTALES:</td>
      <td class="td-num">{{ $sumActual }}</td>
      <td class="td-num">{{ $sumMinimo }}</td>
      <td class="td-num" style="color: #b91c1c;">{{ $sumDeficit }}</td>
      <td></td>
    </tr>
  </tfoot>
</table>
</body>
</html>
