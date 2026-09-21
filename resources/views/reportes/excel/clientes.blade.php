<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
@verbatim
<!--[if gte mso 9]>
<xml>
 <x:ExcelWorkbook>
  <x:ExcelWorksheets>
   <x:ExcelWorksheet>
    <x:Name>Reporte de Clientes</x:Name>
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
  .total-row { background-color: #e2e8f0; font-weight: bold; border-top: 2px solid #047857; border-bottom: 2px double #047857; height: 25pt; vertical-align: middle; }
</style>
</head>
<body>
<table>
  <colgroup>
    <col style="width: 50pt;">
    <col style="width: 160pt;">
    <col style="width: 100pt;">
    <col style="width: 100pt;">
    <col style="width: 140pt;">
    <col style="width: 80pt;">
    <col style="width: 110pt;">
    <col style="width: 110pt;">
  </colgroup>
  <tr>
    <td colspan="8" class="header-brand">FARMABIEN - FARMACIA &amp; DROGUER&Iacute;A C.A.</td>
  </tr>
  <tr>
    <td colspan="8" class="header-sub">RIF / RUC: J-40892154-0 &bull; Tel: (0212) 555-0199 &bull; Av. Principal Los Pr&oacute;ceres, Caracas</td>
  </tr>
  <tr>
    <td colspan="8" class="report-title">REPORTE TOP 20: PACIENTES Y CLIENTES FRECUENTES</td>
  </tr>
  <tr>
    <td colspan="4" class="meta-cell"><strong>Per&iacute;odo:</strong> {{ \Carbon\Carbon::parse($fechaDesde)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($fechaHasta)->format('d/m/Y') }}</td>
    <td colspan="4" class="meta-cell"><strong>Emisi&oacute;n:</strong> {{ now()->format('d/m/Y H:i:s') }}</td>
  </tr>

  <!-- Separador -->
  <tr><td colspan="8" style="height: 10pt;"></td></tr>

  <!-- KPIs -->
  <tr>
    <td colspan="2" class="kpi-title">TOTAL FACTURADO TOP 20</td>
    <td colspan="2" class="kpi-title">CLIENTES ACTIVOS</td>
    <td colspan="2" class="kpi-title">CLIENTES CON COMPRAS</td>
    <td colspan="2" class="kpi-title">TICKET PROMEDIO GENERAL</td>
  </tr>
  <tr>
    <td colspan="2" class="kpi-val td-money">{{ number_format($totalFacturadoClientes, 2, '.', '') }}</td>
    <td colspan="2" class="kpi-val td-num">{{ $totalClientes }}</td>
    <td colspan="2" class="kpi-val td-num">{{ $clientesConCompras }}</td>
    <td colspan="2" class="kpi-val td-money">{{ number_format($ticketPromedio, 2, '.', '') }}</td>
  </tr>

  <!-- Separador -->
  <tr><td colspan="8" style="height: 12pt;"></td></tr>

  <!-- Encabezados de Columnas -->
  <thead>
    <tr>
      <th class="th-col" style="width: 50pt;">POS</th>
      <th class="th-col" style="width: 160pt;">CLIENTE / PACIENTE</th>
      <th class="th-col" style="width: 100pt;">DOCUMENTO</th>
      <th class="th-col" style="width: 100pt;">TEL&Eacute;FONO</th>
      <th class="th-col" style="width: 140pt;">CORREO ELECTR&Oacute;NICO</th>
      <th class="th-col" style="width: 80pt;">COMPRAS</th>
      <th class="th-col" style="width: 110pt;">TOTAL GASTADO ($)</th>
      <th class="th-col" style="width: 110pt;">TICKET PROMEDIO ($)</th>
    </tr>
  </thead>
  <tbody>
    @php
      $sumVentas = 0;
      $sumTotal = 0;
    @endphp
    @forelse($topClientes as $index => $cliente)
      @php
        $ticket = $cliente->total_ventas > 0 ? $cliente->monto_total / $cliente->total_ventas : 0;
        $sumVentas += $cliente->total_ventas;
        $sumTotal += $cliente->monto_total;
        $rowClass = ($index % 2 == 0) ? 'row-even' : 'row-odd';
      @endphp
      <tr class="{{ $rowClass }}">
        <td class="td-center td-num">#{{ $index + 1 }}</td>
        <td class="td-text" style="font-weight: bold;">{{ $cliente->nombre }}</td>
        <td class="td-text td-center">{{ $cliente->documento ?? 'N/A' }}</td>
        <td class="td-text td-center">{{ $cliente->telefono ?? 'N/A' }}</td>
        <td class="td-text">{{ $cliente->email ?? 'N/A' }}</td>
        <td class="td-num">{{ $cliente->total_ventas }}</td>
        <td class="td-money">{{ number_format($cliente->monto_total, 2, '.', '') }}</td>
        <td class="td-money">{{ number_format($ticket, 2, '.', '') }}</td>
      </tr>
    @empty
      <tr>
        <td colspan="8" class="td-center" style="padding: 15pt; color: #64748b;">No hay clientes con compras en el per&iacute;odo seleccionado.</td>
      </tr>
    @endforelse
  </tbody>
  <tfoot>
    <tr class="total-row">
      <td colspan="5" style="text-align: right; padding-right: 10pt;">TOTALES DEL GRUPO:</td>
      <td class="td-num">{{ $sumVentas }}</td>
      <td class="td-money">{{ number_format($sumTotal, 2, '.', '') }}</td>
      <td class="td-money">{{ $sumVentas > 0 ? number_format($sumTotal / $sumVentas, 2, '.', '') : '0.00' }}</td>
    </tr>
  </tfoot>
</table>
</body>
</html>
