<?xml version="1.0" encoding="UTF-8"?>
<?mso-application progid="Excel.Sheet"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns:o="urn:schemas-microsoft-com:office:office"
      xmlns:x="urn:schemas-microsoft-com:office:excel"
      xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<!--[if gte mso 9]>
<xml>
 <x:ExcelWorkbook>
  <x:ExcelWorksheets>
   <x:ExcelWorksheet>
    <x:Name>Libro Controlados MINSA</x:Name>
    <x:WorksheetOptions>
     <x:DisplayGridlines/>
    </x:WorksheetOptions>
   </x:ExcelWorksheet>
  </x:ExcelWorksheets>
 </x:ExcelWorkbook>
</xml>
<![endif]-->
<style>
  body, table { font-family: Calibri, Arial, Helvetica, sans-serif; font-size: 10.5pt; color: #1e293b; }
  table { border-collapse: collapse; width: 100%; }
  /* Encabezado institucional MINSA */
  .header-minsa  { background-color: #1d4ed8; color: #ffffff; font-size: 15pt; font-weight: bold; text-align: center; height: 33pt; vertical-align: middle; border: 1px solid #1e3a8a; }
  .header-sub    { background-color: #1e3a8a; color: #bfdbfe; font-size: 9pt; text-align: center; height: 17pt; vertical-align: middle; border: 1px solid #1e40af; }
  .report-title  { background-color: #eff6ff; color: #1d4ed8; font-size: 12.5pt; font-weight: bold; text-align: center; height: 26pt; vertical-align: middle; border: 1px solid #bfdbfe; }
  /* Metadatos */
  .meta-cell     { background-color: #f8fafc; color: #475569; font-size: 9pt; padding: 4px 8px; border: 1px solid #cbd5e1; }
  /* KPI */
  .kpi-title     { background-color: #f1f5f9; color: #334155; font-size: 9.5pt; font-weight: bold; text-align: center; border: 1px solid #94a3b8; height: 20pt; vertical-align: middle; }
  .kpi-val       { background-color: #ffffff; color: #0f172a; font-size: 13pt; font-weight: bold; text-align: center; border: 1px solid #94a3b8; height: 24pt; vertical-align: middle; }
  /* Encabezado de tabla */
  .th-col        { background-color: #1d4ed8; color: #ffffff; font-weight: bold; font-size: 9.5pt; text-align: center; border: 1px solid #1e3a8a; height: 22pt; vertical-align: middle; }
  /* Celdas de datos */
  .td-text       { mso-number-format:"\@"; padding: 5px 7px; border: 1px solid #cbd5e1; vertical-align: middle; }
  .td-center     { text-align: center; padding: 5px 7px; border: 1px solid #cbd5e1; vertical-align: middle; }
  .td-num        { mso-number-format:"\#,##0"; text-align: right; padding: 5px 7px; border: 1px solid #cbd5e1; vertical-align: middle; }
  .td-date       { mso-number-format:"dd/mm/yyyy hh:mm"; text-align: center; padding: 5px 7px; border: 1px solid #cbd5e1; vertical-align: middle; }
  /* Filas de entrada y salida */
  .row-entrada   { background-color: #eff6ff; }
  .row-salida    { background-color: #fafafa; }
  .row-even      { background-color: #f8fafc; }
  .row-odd       { background-color: #ffffff; }
  /* Badges de tipo */
  .badge-entrada { color: #1d4ed8; font-weight: bold; }
  .badge-salida  { color: #dc2626; font-weight: bold; }
  /* Fila de totales */
  .total-row     { background-color: #dbeafe; font-weight: bold; border-top: 2px solid #1d4ed8; border-bottom: 2px double #1d4ed8; height: 24pt; vertical-align: middle; }
  /* Firma */
  .firma-row     { border-top: 1px solid #64748b; text-align: center; font-size: 8.5pt; color: #64748b; padding-top: 4px; }
</style>
</head>
<body>
<table>
  <colgroup>
    <col style="width: 32pt;">   {{-- N° --}}
    <col style="width: 58pt;">   {{-- Fecha --}}
    <col style="width: 44pt;">   {{-- Hora --}}
    <col style="width: 90pt;">   {{-- Tipo Operación --}}
    <col style="width: 55pt;">   {{-- Efecto --}}
    <col style="width: 130pt;">  {{-- Medicamento --}}
    <col style="width: 110pt;">  {{-- Principio Activo --}}
    <col style="width: 65pt;">   {{-- Concentración --}}
    <col style="width: 100pt;">  {{-- Laboratorio --}}
    <col style="width: 75pt;">   {{-- Lote FEFO --}}
    <col style="width: 65pt;">   {{-- Vencimiento --}}
    <col style="width: 45pt;">   {{-- Cantidad --}}
    <col style="width: 50pt;">   {{-- Unidad --}}
    <col style="width: 100pt;">  {{-- Paciente --}}
    <col style="width: 70pt;">   {{-- Cédula --}}
    <col style="width: 55pt;">   {{-- Edad --}}
    <col style="width: 100pt;">  {{-- Médico --}}
    <col style="width: 70pt;">   {{-- Reg. MINSA --}}
    <col style="width: 130pt;">  {{-- Diagnóstico --}}
    <col style="width: 90pt;">   {{-- Responsable --}}
    <col style="width: 80pt;">   {{-- Doc. Ref --}}
  </colgroup>

  {{-- Encabezado Institucional MINSA --}}
  <tr>
    <td colspan="21" class="header-minsa">REPÚBLICA DE NICARAGUA — MINISTERIO DE SALUD (MINSA) / SILAIS</td>
  </tr>
  <tr>
    <td colspan="21" class="header-sub">Establecimiento: {{ configuracion('empresa_nombre', 'FarmaBien') }} &bull; RUC: {{ configuracion('empresa_ruc', '—') }} &bull; Dir: {{ configuracion('empresa_direccion', '—') }} &bull; Tel: {{ configuracion('empresa_telefono', '—') }}</td>
  </tr>
  <tr>
    <td colspan="21" class="report-title">LIBRO OFICIAL DE MEDICAMENTOS CONTROLADOS &amp; FISCALIZADOS</td>
  </tr>

  {{-- Metadatos del reporte --}}
  <tr>
    <td colspan="11" class="meta-cell"><strong>Período:</strong> {{ \Carbon\Carbon::parse($desde)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($hasta)->format('d/m/Y') }}</td>
    <td colspan="10" class="meta-cell"><strong>Emisión:</strong> {{ now()->format('d/m/Y H:i:s') }} &bull; <strong>Usuario:</strong> {{ auth()->user()->name ?? 'Sistema' }}</td>
  </tr>
  <tr>
    <td colspan="21" class="meta-cell"><strong>Total de movimientos en el período:</strong> {{ $registros->count() }} &bull; <strong>Entradas:</strong> {{ $registros->filter(fn($r) => $r->esEntrada())->count() }} &bull; <strong>Salidas:</strong> {{ $registros->reject(fn($r) => $r->esEntrada())->count() }}</td>
  </tr>

  {{-- Separador --}}
  <tr><td colspan="21" style="height: 8pt;"></td></tr>

  {{-- KPIs --}}
  <tr>
    <td colspan="7" class="kpi-title">TOTAL MOVIMIENTOS</td>
    <td colspan="7" class="kpi-title">TOTAL ENTRADAS</td>
    <td colspan="7" class="kpi-title">TOTAL SALIDAS</td>
  </tr>
  <tr>
    <td colspan="7" class="kpi-val">{{ $registros->count() }}</td>
    <td colspan="7" class="kpi-val">{{ $registros->filter(fn($r) => $r->esEntrada())->sum('cantidad') }}</td>
    <td colspan="7" class="kpi-val">{{ $registros->reject(fn($r) => $r->esEntrada())->sum('cantidad') }}</td>
  </tr>

  {{-- Separador --}}
  <tr><td colspan="21" style="height: 8pt;"></td></tr>

  {{-- Encabezados de tabla --}}
  <tr>
    <th class="th-col">#</th>
    <th class="th-col">Fecha</th>
    <th class="th-col">Hora</th>
    <th class="th-col">Tipo Operación</th>
    <th class="th-col">Efecto</th>
    <th class="th-col">Medicamento / Lote</th>
    <th class="th-col">Principio Activo</th>
    <th class="th-col">Concentración</th>
    <th class="th-col">Laboratorio</th>
    <th class="th-col">Lote FEFO</th>
    <th class="th-col">Vencimiento</th>
    <th class="th-col">Cantidad</th>
    <th class="th-col">Unidad</th>
    <th class="th-col">Paciente / Beneficiario</th>
    <th class="th-col">Cédula / RUC</th>
    <th class="th-col">Edad</th>
    <th class="th-col">Médico / Prescriptor</th>
    <th class="th-col">Reg. MINSA</th>
    <th class="th-col">Diagnóstico / Justificación MINSA</th>
    <th class="th-col">Responsable</th>
    <th class="th-col">Doc. Referencia</th>
  </tr>

  {{-- Filas de datos --}}
  @php $folio = 1; @endphp
  @forelse($registros as $reg)
    @php
      $esEntrada = $reg->esEntrada();
      $docRef = '';
      if ($reg->venta_id) {
          $docRef = 'Venta #' . str_pad($reg->venta_id, 5, '0', STR_PAD_LEFT);
      } elseif ($reg->devolucion) {
          $docRef = $reg->devolucion->numero_devolucion;
      } elseif ($reg->compra) {
          $docRef = 'Compra #' . $reg->compra->numero_comprobante;
      } elseif ($reg->movimiento_inventario_id) {
          $docRef = 'Ajuste #' . $reg->movimiento_inventario_id;
      }
    @endphp
    <tr class="{{ $esEntrada ? 'row-entrada' : ($folio % 2 === 0 ? 'row-even' : 'row-odd') }}">
      <td class="td-center">{{ $folio++ }}</td>
      <td class="td-center td-text">{{ $reg->created_at->format('d/m/Y') }}</td>
      <td class="td-center td-text">{{ $reg->created_at->format('H:i') }}</td>
      <td class="td-text">{{ $reg->tipo_etiqueta }}</td>
      <td class="td-center {{ $esEntrada ? 'badge-entrada' : 'badge-salida' }}">{{ $esEntrada ? 'ENTRADA (+)' : 'SALIDA (-)' }}</td>
      <td class="td-text"><strong>{{ $reg->producto->nombre ?? 'N/A' }}</strong>@if($reg->lote)<br><small>{{ $reg->producto->principio_activo ?? '' }}{{ $reg->producto->concentracion ? ' · ' . $reg->producto->concentracion : '' }}</small>@endif</td>
      <td class="td-text">{{ $reg->producto?->principio_activo ?? '—' }}</td>
      <td class="td-center td-text">{{ $reg->producto?->concentracion ?? '—' }}</td>
      <td class="td-text">{{ $reg->producto?->laboratorio?->nombre ?? '—' }}</td>
      <td class="td-center td-text">{{ $reg->lote?->numero_lote ?? '—' }}</td>
      <td class="td-center td-text">{{ $reg->lote?->fecha_vencimiento ? $reg->lote->fecha_vencimiento->format('d/m/Y') : '—' }}</td>
      <td class="td-center {{ $esEntrada ? 'badge-entrada' : 'badge-salida' }}">{{ ($esEntrada ? '+' : '-') . number_format($reg->cantidad, 0) }}</td>
      <td class="td-center td-text">{{ $reg->unidad ?: 'unid.' }}</td>
      <td class="td-text">{{ $reg->paciente_nombre ?: 'Público General' }}</td>
      <td class="td-center td-text">{{ $reg->paciente_cedula ?? '—' }}</td>
      <td class="td-center td-text">{{ $reg->paciente_edad ? $reg->paciente_edad . ' años' : '—' }}</td>
      <td class="td-text">{{ $reg->medico_nombre ?? '—' }}</td>
      <td class="td-center td-text">{{ $reg->medico_num_registro ?? '—' }}</td>
      <td class="td-text">{{ $reg->motivo_omision ?: ($reg->diagnostico ?: 'Tratamiento prescrito') }}</td>
      <td class="td-text">{{ $reg->despachador->name ?? 'Sistema' }}</td>
      <td class="td-center td-text">{{ $docRef ?: '—' }}</td>
    </tr>
  @empty
    <tr>
      <td colspan="21" class="td-center" style="height: 30pt; color: #94a3b8;">No se registraron movimientos en el período seleccionado.</td>
    </tr>
  @endforelse

  {{-- Fila totales --}}
  <tr class="total-row">
    <td colspan="11" class="td-text"><strong>TOTALES DEL PERÍODO {{ \Carbon\Carbon::parse($desde)->format('d/m/Y') }} — {{ \Carbon\Carbon::parse($hasta)->format('d/m/Y') }}</strong></td>
    <td class="td-center"><strong>{{ $registros->count() }}</strong></td>
    <td colspan="9" class="td-text">registros &bull; Entradas: {{ $registros->filter(fn($r) => $r->esEntrada())->count() }} &bull; Salidas: {{ $registros->reject(fn($r) => $r->esEntrada())->count() }}</td>
  </tr>

  {{-- Separador para firmas --}}
  <tr><td colspan="21" style="height: 30pt;"></td></tr>

  {{-- Firmas --}}
  <tr>
    <td colspan="7" class="firma-row">___________________________<br>Responsable del Establecimiento</td>
    <td colspan="7" class="firma-row">___________________________<br>Regente / Director Técnico</td>
    <td colspan="7" class="firma-row">___________________________<br>Inspector Farmacéutico SILAIS/MINSA</td>
  </tr>
  <tr>
    <td colspan="21" style="height: 6pt;"></td>
  </tr>
  <tr>
    <td colspan="21" class="meta-cell" style="font-size: 8pt; color: #94a3b8;">
      Documento generado electrónicamente por el Sistema FarmaBien | {{ now()->format('d/m/Y H:i:s') }} | Este libro debe conservarse por un mínimo de 5 años según regulación MINSA Nicaragua.
    </td>
  </tr>
</table>
</body>
</html>
