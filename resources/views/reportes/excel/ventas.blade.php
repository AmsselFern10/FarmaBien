<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
@verbatim
<!--[if gte mso 9]>
<xml>
 <x:ExcelWorkbook>
  <x:ExcelWorksheets>
   <x:ExcelWorksheet>
    <x:Name>Reporte de Ventas</x:Name>
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
  .td-date { mso-number-format: "yyyy-mm-dd hh:mm"; text-align: center; padding: 6px 8px; border: 1px solid #cbd5e1; vertical-align: middle; }
  .row-even { background-color: #f8fafc; }
  .row-odd { background-color: #ffffff; }
  .total-row { background-color: #e2e8f0; font-weight: bold; border-top: 2px solid #047857; border-bottom: 2px double #047857; height: 25pt; vertical-align: middle; }
</style>
</head>
<body>
<table>
  <colgroup>
    <col style="width: 90pt;">
    <col style="width: 120pt;">
    <col style="width: 150pt;">
    <col style="width: 100pt;">
    <col style="width: 120pt;">
    <col style="width: 100pt;">
    <col style="width: 80pt;">
    <col style="width: 90pt;">
    <col style="width: 100pt;">
  </colgroup>
  <!-- Encabezado Institucional -->
  <tr>
    <td colspan="9" class="header-brand">{{ strtoupper(configuracion('empresa_nombre', 'FARMABIEN')) }} - {{ strtoupper(configuracion('empresa_razon_social', 'Farmacia & Droguería FarmaBien C.A.')) }}</td>
  </tr>
  <tr>
    <td colspan="9" class="header-sub">RIF / RUC: {{ configuracion('empresa_ruc', 'J-40892154-0') }} &bull; Tel: {{ configuracion('empresa_telefono', '(0212) 555-0199') }} &bull; {{ configuracion('empresa_direccion', 'Caracas') }}</td>
  </tr>
  <tr>
    <td colspan="9" class="report-title">REPORTE DETALLADO DE VENTAS E INGRESOS</td>
  </tr>
  <tr>
    <td colspan="5" class="meta-cell"><strong>Per&iacute;odo:</strong> {{ \Carbon\Carbon::parse($fechaDesde)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($fechaHasta)->format('d/m/Y') }}</td>
    <td colspan="4" class="meta-cell"><strong>Emisi&oacute;n:</strong> {{ now()->format('d/m/Y H:i:s') }} | <strong>Usuario:</strong> {{ auth()->user()->name ?? 'Sistema' }}</td>
  </tr>
  <tr>
    <td colspan="9" class="meta-cell"><strong>Filtros aplicados:</strong> M&eacute;todo de Pago: {{ $metodoPago ? ucfirst(str_replace('_', ' ', $metodoPago)) : 'Todos' }} | Cajero: {{ $cajeroId ? 'ID #'.$cajeroId : 'Todos los cajeros' }}</td>
  </tr>

  <!-- Separador -->
  <tr><td colspan="9" style="height: 10pt;"></td></tr>

  <!-- Resumen de KPIs -->
  <tr>
    <td colspan="3" class="kpi-title">TOTAL VENDIDO</td>
    <td colspan="3" class="kpi-title">CANTIDAD DE VENTAS</td>
    <td colspan="3" class="kpi-title">TICKET PROMEDIO</td>
  </tr>
  <tr>
    <td colspan="3" class="kpi-val td-money">{{ number_format($totalVendido, 2, '.', '') }}</td>
    <td colspan="3" class="kpi-val td-num">{{ $cantidadVentas }}</td>
    <td colspan="3" class="kpi-val td-money">{{ number_format($ticketPromedio, 2, '.', '') }}</td>
  </tr>

  <!-- Separador -->
  <tr><td colspan="9" style="height: 12pt;"></td></tr>

  <!-- Encabezados de Columnas -->
  <thead>
    <tr>
      <th class="th-col" style="width: 90pt;">N&deg; FACTURA</th>
      <th class="th-col" style="width: 120pt;">FECHA Y HORA</th>
      <th class="th-col" style="width: 150pt;">CLIENTE / PACIENTE</th>
      <th class="th-col" style="width: 100pt;">DOCUMENTO</th>
      <th class="th-col" style="width: 120pt;">CAJERO / USUARIO</th>
      <th class="th-col" style="width: 100pt;">M&Eacute;TODO DE PAGO</th>
      <th class="th-col" style="width: 80pt;">ITEMS</th>
      <th class="th-col" style="width: 90pt;">ESTADO</th>
      <th class="th-col" style="width: 100pt;">TOTAL ($)</th>
    </tr>
  </thead>
  <tbody>
    @php $sumItems = 0; @endphp
    @forelse($ventas as $index => $venta)
      @php 
        $cantItems = $venta->detalles ? $venta->detalles->sum('cantidad') : 0;
        $sumItems += $cantItems;
        $rowClass = ($index % 2 == 0) ? 'row-even' : 'row-odd';
      @endphp
      <tr class="{{ $rowClass }}">
        <td class="td-text td-center">{{ $venta->numero_factura ?? ('FAC-' . str_pad($venta->id, 6, '0', STR_PAD_LEFT)) }}</td>
        <td class="td-date">{{ \Carbon\Carbon::parse($venta->fecha)->format('Y-m-d H:i') }}</td>
        <td class="td-text">{{ $venta->cliente->nombre ?? 'Cliente Ocasional' }}</td>
        <td class="td-text td-center">{{ $venta->cliente->documento ?? 'N/A' }}</td>
        <td class="td-text">{{ $venta->usuario->name ?? 'N/A' }}</td>
        <td class="td-text td-center">{{ ucfirst(str_replace('_', ' ', $venta->metodo_pago ?? 'N/A')) }}</td>
        <td class="td-num">{{ $cantItems }}</td>
        <td class="td-text td-center">{{ ucfirst($venta->estado ?? 'N/A') }}</td>
        <td class="td-money">{{ number_format($venta->total, 2, '.', '') }}</td>
      </tr>
    @empty
      <tr>
        <td colspan="9" class="td-center" style="padding: 15pt; color: #64748b;">No se encontraron registros de ventas para los filtros seleccionados.</td>
      </tr>
    @endforelse
  </tbody>
  <tfoot>
    <tr class="total-row">
      <td colspan="6" style="text-align: right; padding-right: 10pt;">TOTALES GENERALES:</td>
      <td class="td-num">{{ $sumItems }}</td>
      <td></td>
      <td class="td-money">{{ number_format($totalVendido, 2, '.', '') }}</td>
    </tr>
  </tfoot>
</table>
</body>
</html>
