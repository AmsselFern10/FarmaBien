<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
@verbatim
<!--[if gte mso 9]>
<xml>
 <x:ExcelWorkbook>
  <x:ExcelWorksheets>
   <x:ExcelWorksheet>
    <x:Name>Compras a Proveedores</x:Name>
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
    <col style="width: 100pt;">
    <col style="width: 110pt;">
    <col style="width: 160pt;">
    <col style="width: 100pt;">
    <col style="width: 110pt;">
    <col style="width: 100pt;">
    <col style="width: 90pt;">
    <col style="width: 100pt;">
  </colgroup>
  <tr>
    <td colspan="8" class="header-brand">{{ strtoupper(configuracion('empresa_nombre', 'FARMABIEN')) }} - {{ strtoupper(configuracion('empresa_razon_social', 'Farmacia & Droguería FarmaBien C.A.')) }}</td>
  </tr>
  <tr>
    <td colspan="8" class="header-sub">RIF / RUC: {{ configuracion('empresa_ruc', 'J-40892154-0') }} &bull; Tel: {{ configuracion('empresa_telefono', '(0212) 555-0199') }} &bull; {{ configuracion('empresa_direccion', 'Caracas') }}</td>
  </tr>
  <tr>
    <td colspan="8" class="report-title">REPORTE DETALLADO DE COMPRAS Y REAPROVISIONAMIENTO</td>
  </tr>
  <tr>
    <td colspan="4" class="meta-cell"><strong>Per&iacute;odo:</strong> {{ \Carbon\Carbon::parse($fechaDesde)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($fechaHasta)->format('d/m/Y') }}</td>
    <td colspan="4" class="meta-cell"><strong>Emisi&oacute;n:</strong> {{ now()->format('d/m/Y H:i:s') }} | <strong>Usuario:</strong> {{ auth()->user()->name ?? 'Sistema' }}</td>
  </tr>

  <!-- Separador -->
  <tr><td colspan="8" style="height: 10pt;"></td></tr>

  <!-- KPIs -->
  <tr>
    <td colspan="2" class="kpi-title">TOTAL EN COMPRAS</td>
    <td colspan="2" class="kpi-title">&Oacute;RDENES REGISTRADAS</td>
    <td colspan="2" class="kpi-title">PROMEDIO POR ORDEN</td>
    <td colspan="2" class="kpi-title">PROVEEDORES ACTIVOS</td>
  </tr>
  <tr>
    <td colspan="2" class="kpi-val td-money">{{ number_format($totalComprado, 2, '.', '') }}</td>
    <td colspan="2" class="kpi-val td-num">{{ $cantidadCompras }}</td>
    <td colspan="2" class="kpi-val td-money">{{ number_format($promedioCompra, 2, '.', '') }}</td>
    <td colspan="2" class="kpi-val td-num">{{ $comprasPorProveedor->count() }}</td>
  </tr>

  <!-- Separador -->
  <tr><td colspan="8" style="height: 12pt;"></td></tr>

  <!-- Encabezados de Columnas -->
  <thead>
    <tr>
      <th class="th-col" style="width: 100pt;">N&deg; ORDEN</th>
      <th class="th-col" style="width: 110pt;">FECHA REGISTRO</th>
      <th class="th-col" style="width: 160pt;">PROVEEDOR</th>
      <th class="th-col" style="width: 100pt;">RIF / RUC</th>
      <th class="th-col" style="width: 110pt;">CONDICI&Oacute;N PAGO</th>
      <th class="th-col" style="width: 100pt;">REGISTRADO POR</th>
      <th class="th-col" style="width: 90pt;">ESTADO</th>
      <th class="th-col" style="width: 100pt;">TOTAL ($)</th>
    </tr>
  </thead>
  <tbody>
    @forelse($compras as $index => $compra)
      @php $rowClass = ($index % 2 == 0) ? 'row-even' : 'row-odd'; @endphp
      <tr class="{{ $rowClass }}">
        <td class="td-text td-center">{{ $compra->numero_factura ?? ('COM-' . str_pad($compra->id, 6, '0', STR_PAD_LEFT)) }}</td>
        <td class="td-date">{{ \Carbon\Carbon::parse($compra->fecha)->format('Y-m-d H:i') }}</td>
        <td class="td-text">{{ $compra->proveedor->nombre ?? 'N/A' }}</td>
        <td class="td-text td-center">{{ $compra->proveedor->ruc ?? 'N/A' }}</td>
        <td class="td-text td-center">{{ ucfirst(str_replace('_', ' ', $compra->condicion_pago ?? 'Contado')) }}</td>
        <td class="td-text">{{ $compra->usuario->name ?? 'N/A' }}</td>
        <td class="td-text td-center">{{ ucfirst($compra->estado ?? 'N/A') }}</td>
        <td class="td-money">{{ number_format($compra->total, 2, '.', '') }}</td>
      </tr>
    @empty
      <tr>
        <td colspan="8" class="td-center" style="padding: 15pt; color: #64748b;">No se encontraron registros de compras en el per&iacute;odo seleccionado.</td>
      </tr>
    @endforelse
  </tbody>
  <tfoot>
    <tr class="total-row">
      <td colspan="7" style="text-align: right; padding-right: 10pt;">TOTAL GENERAL DE COMPRAS:</td>
      <td class="td-money">{{ number_format($totalComprado, 2, '.', '') }}</td>
    </tr>
  </tfoot>
</table>
</body>
</html>
