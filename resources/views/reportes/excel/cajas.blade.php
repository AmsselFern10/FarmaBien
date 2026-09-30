<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
@verbatim
<!--[if gte mso 9]>
<xml>
 <x:ExcelWorkbook>
  <x:ExcelWorksheets>
   <x:ExcelWorksheet>
    <x:Name>Control de Cajas</x:Name>
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
    <col style="width: 70pt;">
    <col style="width: 130pt;">
    <col style="width: 140pt;">
    <col style="width: 110pt;">
    <col style="width: 110pt;">
    <col style="width: 90pt;">
    <col style="width: 90pt;">
    <col style="width: 90pt;">
    <col style="width: 90pt;">
    <col style="width: 90pt;">
    <col style="width: 90pt;">
    <col style="width: 80pt;">
  </colgroup>
  <tr>
    <td colspan="12" class="header-brand">{{ strtoupper(configuracion('empresa_nombre', 'FARMABIEN')) }} - {{ strtoupper(configuracion('empresa_razon_social', 'Farmacia & Droguería FarmaBien C.A.')) }}</td>
  </tr>
  <tr>
    <td colspan="12" class="header-sub">RIF / RUC: {{ configuracion('empresa_ruc', 'J-40892154-0') }} &bull; Tel: {{ configuracion('empresa_telefono', '(0212) 555-0199') }}</td>
  </tr>
  <tr>
    <td colspan="12" class="report-title">REPORTE DETALLADO DE CONTROL DE CAJAS Y ARQUEOS</td>
  </tr>
  <tr>
    <td colspan="6" class="meta-cell"><strong>Per&iacute;odo:</strong> {{ \Carbon\Carbon::parse($fechaDesde)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($fechaHasta)->format('d/m/Y') }}</td>
    <td colspan="6" class="meta-cell"><strong>Emisi&oacute;n:</strong> {{ now()->format('d/m/Y H:i:s') }} | <strong>Usuario:</strong> {{ auth()->user()->name ?? 'Sistema' }}</td>
  </tr>
  <tr>
    <td colspan="12" class="meta-cell"><strong>Filtros aplicados:</strong> Caja: {{ $cajaId ? 'ID #'.$cajaId : 'Todas' }} | Cajero: {{ $cajeroId ? 'ID #'.$cajeroId : 'Todos' }} | Estado: {{ ucfirst($estado ?: 'Todos') }}</td>
  </tr>

  <tr><td colspan="12" style="height: 10pt;"></td></tr>

  <tr>
    <td colspan="3" class="kpi-title">TOTAL VENTAS</td>
    <td colspan="3" class="kpi-title">VENTAS EFECTIVO</td>
    <td colspan="3" class="kpi-title">MOVIMIENTOS MANUALES</td>
    <td colspan="3" class="kpi-title">DIFERENCIA NETA</td>
  </tr>
  <tr>
    <td colspan="3" class="kpi-val td-money">{{ number_format($totalVentasCajas, 2, '.', '') }}</td>
    <td colspan="3" class="kpi-val td-money">{{ number_format($totalVentasEfectivo, 2, '.', '') }}</td>
    <td colspan="3" class="kpi-val td-money">{{ number_format($totalIngresosManuales - $totalEgresosManuales, 2, '.', '') }}</td>
    <td colspan="3" class="kpi-val td-money">{{ number_format($diferenciaTotal, 2, '.', '') }}</td>
  </tr>

  <tr><td colspan="12" style="height: 12pt;"></td></tr>

  <thead>
    <tr>
      <th class="th-col">N&deg; TURNO</th>
      <th class="th-col">CAJA</th>
      <th class="th-col">CAJERO RESPONSABLE</th>
      <th class="th-col">FECHA APERTURA</th>
      <th class="th-col">FECHA CIERRE</th>
      <th class="th-col">MONTO INICIAL</th>
      <th class="th-col">VTAS EFECTIVO</th>
      <th class="th-col">TOTAL VENTAS</th>
      <th class="th-col">EFECTIVO ESPERADO</th>
      <th class="th-col">EFECTIVO DECLARADO</th>
      <th class="th-col">DIFERENCIA</th>
      <th class="th-col">ESTADO</th>
    </tr>
  </thead>
  <tbody>
    @forelse($sesiones as $index => $s)
      @php $rowClass = ($index % 2 == 0) ? 'row-even' : 'row-odd'; @endphp
      <tr class="{{ $rowClass }}">
        <td class="td-text td-center">#{{ str_pad($s->id, 5, '0', STR_PAD_LEFT) }}</td>
        <td class="td-text">{{ $s->caja?->nombre ?? ('Caja #' . $s->caja_id) }}</td>
        <td class="td-text">{{ $s->usuario?->name ?? 'N/A' }}</td>
        <td class="td-date">{{ $s->fecha_apertura ? $s->fecha_apertura->format('Y-m-d H:i') : '' }}</td>
        <td class="td-date">{{ $s->fecha_cierre ? $s->fecha_cierre->format('Y-m-d H:i') : 'En curso' }}</td>
        <td class="td-money">{{ number_format($s->monto_inicial, 2, '.', '') }}</td>
        <td class="td-money">{{ number_format($s->total_ventas_efectivo, 2, '.', '') }}</td>
        <td class="td-money">{{ number_format($s->total_ventas, 2, '.', '') }}</td>
        <td class="td-money">{{ number_format($s->monto_esperado_efectivo ?? $s->efectivo_esperado_calculado, 2, '.', '') }}</td>
        <td class="td-money">{{ number_format($s->monto_final_efectivo ?? 0, 2, '.', '') }}</td>
        <td class="td-money">{{ number_format($s->diferencia_efectivo ?? 0, 2, '.', '') }}</td>
        <td class="td-text td-center">{{ ucfirst($s->estado) }}</td>
      </tr>
    @empty
      <tr>
        <td colspan="12" class="td-center" style="padding: 15pt; color: #64748b;">No se encontraron registros de turnos de caja para los filtros seleccionados.</td>
      </tr>
    @endforelse
  </tbody>
  <tfoot>
    <tr class="total-row">
      <td colspan="5" style="text-align: right; padding-right: 10pt;">TOTALES GENERALES:</td>
      <td class="td-money">{{ number_format($sesiones->sum('monto_inicial'), 2, '.', '') }}</td>
      <td class="td-money">{{ number_format($totalVentasEfectivo, 2, '.', '') }}</td>
      <td class="td-money">{{ number_format($totalVentasCajas, 2, '.', '') }}</td>
      <td colspan="2"></td>
      <td class="td-money">{{ number_format($diferenciaTotal, 2, '.', '') }}</td>
      <td></td>
    </tr>
  </tfoot>
</table>
</body>
</html>
