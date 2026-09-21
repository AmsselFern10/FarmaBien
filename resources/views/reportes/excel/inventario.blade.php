<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
@verbatim
<!--[if gte mso 9]>
<xml>
 <x:ExcelWorkbook>
  <x:ExcelWorksheets>
   <x:ExcelWorksheet>
    <x:Name>Inventario y Vencimientos</x:Name>
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
  .td-text { mso-number-format: "\@"; padding: 5px 8px; border: 1px solid #cbd5e1; vertical-align: middle; }
  .td-center { text-align: center; padding: 5px 8px; border: 1px solid #cbd5e1; vertical-align: middle; }
  .td-num { mso-number-format: "\#,##0"; text-align: right; padding: 5px 8px; border: 1px solid #cbd5e1; vertical-align: middle; }
  .td-money { mso-number-format: "\$#,##0.00"; text-align: right; padding: 5px 8px; border: 1px solid #cbd5e1; vertical-align: middle; }
  .td-date { mso-number-format: "yyyy-mm-dd"; text-align: center; padding: 5px 8px; border: 1px solid #cbd5e1; vertical-align: middle; }
  .row-even { background-color: #f8fafc; }
  .row-odd { background-color: #ffffff; }
  .badge-vencido { background-color: #fee2e2; color: #991b1b; font-weight: bold; text-align: center; }
  .badge-urgente { background-color: #ffedd5; color: #9a3412; font-weight: bold; text-align: center; }
  .badge-alerta { background-color: #fef9c3; color: #854d0e; font-weight: bold; text-align: center; }
  .badge-optimo { background-color: #dcfce7; color: #166534; font-weight: bold; text-align: center; }
  .total-row { background-color: #e2e8f0; font-weight: bold; border-top: 2px solid #047857; border-bottom: 2px double #047857; height: 25pt; vertical-align: middle; }
</style>
</head>
<body>
<table>
  <colgroup>
    <col style="width: 150pt;">
    <col style="width: 120pt;">
    <col style="width: 100pt;">
    <col style="width: 100pt;">
    <col style="width: 80pt;">
    <col style="width: 80pt;">
    <col style="width: 70pt;">
    <col style="width: 70pt;">
    <col style="width: 80pt;">
    <col style="width: 85pt;">
    <col style="width: 85pt;">
    <col style="width: 85pt;">
  </colgroup>
  <tr>
    <td colspan="12" class="header-brand">FARMABIEN - FARMACIA &amp; DROGUER&Iacute;A C.A.</td>
  </tr>
  <tr>
    <td colspan="12" class="header-sub">RIF / RUC: J-40892154-0 &bull; Tel: (0212) 555-0199 &bull; Av. Principal Los Pr&oacute;ceres, Caracas</td>
  </tr>
  <tr>
    <td colspan="12" class="report-title">REPORTE DE INVENTARIO Y CONTROL DE VENCIMIENTOS (PEPS)</td>
  </tr>
  <tr>
    <td colspan="6" class="meta-cell"><strong>Fecha de Corte:</strong> {{ now()->format('d/m/Y H:i:s') }}</td>
    <td colspan="6" class="meta-cell"><strong>Usuario:</strong> {{ auth()->user()->name ?? 'Sistema' }}</td>
  </tr>

  <!-- Separador -->
  <tr><td colspan="12" style="height: 10pt;"></td></tr>

  <!-- KPIs -->
  <tr>
    <td colspan="2" class="kpi-title">VALORIZACI&Oacute;N COSTO</td>
    <td colspan="2" class="kpi-title">VALORIZACI&Oacute;N VENTA</td>
    <td colspan="2" class="kpi-title">MARGEN PROYECTADO</td>
    <td colspan="2" class="kpi-title">LOTES VENCIDOS</td>
    <td colspan="2" class="kpi-title">CR&Iacute;TICOS (&le;30D)</td>
    <td colspan="2" class="kpi-title">ALERTA (&le;90D)</td>
  </tr>
  <tr>
    <td colspan="2" class="kpi-val td-money">{{ number_format($valorizacion['costo_total'] ?? 0, 2, '.', '') }}</td>
    <td colspan="2" class="kpi-val td-money">{{ number_format($valorizacion['venta_total'] ?? 0, 2, '.', '') }}</td>
    <td colspan="2" class="kpi-val td-money">{{ number_format(($valorizacion['venta_total'] ?? 0) - ($valorizacion['costo_total'] ?? 0), 2, '.', '') }}</td>
    <td colspan="2" class="kpi-val td-num" style="color: #dc2626;">{{ $lotesVencidosCount ?? 0 }}</td>
    <td colspan="2" class="kpi-val td-num" style="color: #ea580c;">{{ $lotesCriticosCount ?? 0 }}</td>
    <td colspan="2" class="kpi-val td-num" style="color: #ca8a04;">{{ $lotesAlertaCount ?? 0 }}</td>
  </tr>

  <!-- Separador -->
  <tr><td colspan="12" style="height: 12pt;"></td></tr>

  <!-- Encabezados de Columnas -->
  <thead>
    <tr>
      <th class="th-col" style="width: 150pt;">MEDICAMENTO</th>
      <th class="th-col" style="width: 120pt;">PRINCIPIO ACTIVO</th>
      <th class="th-col" style="width: 100pt;">CATEGOR&Iacute;A</th>
      <th class="th-col" style="width: 100pt;">LABORATORIO</th>
      <th class="th-col" style="width: 80pt;">N&deg; LOTE</th>
      <th class="th-col" style="width: 80pt;">F. VENCIMIENTO</th>
      <th class="th-col" style="width: 70pt;">D&Iacute;AS REST.</th>
      <th class="th-col" style="width: 70pt;">ESTADO</th>
      <th class="th-col" style="width: 80pt;">STOCK (UNID)</th>
      <th class="th-col" style="width: 85pt;">P. COMPRA</th>
      <th class="th-col" style="width: 85pt;">VALOR COSTO</th>
      <th class="th-col" style="width: 85pt;">VALOR VENTA</th>
    </tr>
  </thead>
  <tbody>
    @php
      $sumStock = 0;
      $sumCosto = 0;
      $sumVenta = 0;
    @endphp
    @forelse($lotes as $index => $lote)
      @php
        $dias = $lote->dias_para_vencer;
        $costoFila = $lote->stock_actual * ($lote->precio_compra ?? 0);
        $ventaFila = $lote->stock_actual * ($lote->producto->precio_base ?? 0);
        $sumStock += $lote->stock_actual;
        $sumCosto += $costoFila;
        $sumVenta += $ventaFila;
        $rowClass = ($index % 2 == 0) ? 'row-even' : 'row-odd';

        if ($dias < 0) {
          $badgeClass = 'badge-vencido';
          $estadoTexto = 'VENCIDO';
        } elseif ($dias <= 30) {
          $badgeClass = 'badge-urgente';
          $estadoTexto = 'URGENTE (≤30d)';
        } elseif ($dias <= 90) {
          $badgeClass = 'badge-alerta';
          $estadoTexto = 'ALERTA (≤90d)';
        } else {
          $badgeClass = 'badge-optimo';
          $estadoTexto = 'ÓPTIMO';
        }
      @endphp
      <tr class="{{ $rowClass }}">
        <td class="td-text">{{ $lote->producto->nombre ?? 'N/A' }}</td>
        <td class="td-text">{{ $lote->producto->principio_activo ?? 'N/A' }}</td>
        <td class="td-text">{{ $lote->producto->categoria->nombre ?? 'N/A' }}</td>
        <td class="td-text">{{ $lote->producto->laboratorio->nombre ?? 'N/A' }}</td>
        <td class="td-text td-center">{{ $lote->numero_lote }}</td>
        <td class="td-date">{{ \Carbon\Carbon::parse($lote->fecha_vencimiento)->format('Y-m-d') }}</td>
        <td class="td-num">{{ $dias }} d&iacute;as</td>
        <td class="td-center {{ $badgeClass }}">{{ $estadoTexto }}</td>
        <td class="td-num">{{ $lote->stock_actual }}</td>
        <td class="td-money">{{ number_format($lote->precio_compra ?? 0, 2, '.', '') }}</td>
        <td class="td-money">{{ number_format($costoFila, 2, '.', '') }}</td>
        <td class="td-money">{{ number_format($ventaFila, 2, '.', '') }}</td>
      </tr>
    @empty
      <tr>
        <td colspan="12" class="td-center" style="padding: 15pt; color: #64748b;">No hay lotes que coincidan con los criterios seleccionados.</td>
      </tr>
    @endforelse
  </tbody>
  <tfoot>
    <tr class="total-row">
      <td colspan="8" style="text-align: right; padding-right: 10pt;">TOTALES INVENTARIO:</td>
      <td class="td-num">{{ $sumStock }}</td>
      <td></td>
      <td class="td-money">{{ number_format($sumCosto, 2, '.', '') }}</td>
      <td class="td-money">{{ number_format($sumVenta, 2, '.', '') }}</td>
    </tr>
  </tfoot>
</table>
</body>
</html>
