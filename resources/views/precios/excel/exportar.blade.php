<?xml version="1.0" encoding="UTF-8"?>
<?mso-application progid="Excel.Sheet"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns:o="urn:schemas-microsoft-com:office:office"
      xmlns:x="urn:schemas-microsoft-com:office:excel"
      xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
@verbatim
<!--[if gte mso 9]>
<xml>
 <x:ExcelWorkbook>
  <x:ExcelWorksheets>
   <x:ExcelWorksheet>
    <x:Name>Precios de Venta</x:Name>
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
  body, table { font-family: Calibri, Arial, Helvetica, sans-serif; font-size: 10.5pt; color: #1e293b; }
  table { border-collapse: collapse; width: 100%; }
  .header-brand  { background-color: #047857; color: #ffffff; font-size: 15pt; font-weight: bold; text-align: center; height: 33pt; vertical-align: middle; border: 1px solid #065f46; }
  .header-sub    { background-color: #065f46; color: #d1fae5; font-size: 9pt; text-align: center; height: 17pt; vertical-align: middle; border: 1px solid #044e39; }
  .report-title  { background-color: #ecfdf5; color: #047857; font-size: 12.5pt; font-weight: bold; text-align: center; height: 26pt; vertical-align: middle; border: 1px solid #a7f3d0; }
  .meta-cell     { background-color: #f8fafc; color: #475569; font-size: 9pt; padding: 4px 8px; border: 1px solid #cbd5e1; }
  .kpi-title     { background-color: #f1f5f9; color: #334155; font-size: 9.5pt; font-weight: bold; text-align: center; border: 1px solid #94a3b8; height: 20pt; vertical-align: middle; }
  .kpi-val       { background-color: #ffffff; color: #0f172a; font-size: 13pt; font-weight: bold; text-align: center; border: 1px solid #94a3b8; height: 24pt; vertical-align: middle; }
  .th-col        { background-color: #047857; color: #ffffff; font-weight: bold; font-size: 9.5pt; text-align: center; border: 1px solid #065f46; height: 22pt; vertical-align: middle; }
  .td-text       { mso-number-format:"\@"; padding: 5px 7px; border: 1px solid #cbd5e1; vertical-align: middle; }
  .td-center     { text-align: center; padding: 5px 7px; border: 1px solid #cbd5e1; vertical-align: middle; }
  .td-money      { mso-number-format:"\#,##0.00"; text-align: right; font-family: "Courier New", monospace; padding: 5px 10px; border: 1px solid #cbd5e1; vertical-align: middle; }
  .td-pct        { mso-number-format:"\@"; text-align: center; font-weight: bold; padding: 5px 7px; border: 1px solid #cbd5e1; vertical-align: middle; }
  .row-even      { background-color: #f8fafc; }
  .row-odd       { background-color: #ffffff; }
  /* Semáforo de margen */
  .margen-ok     { color: #047857; font-weight: bold; }
  .margen-warn   { color: #b45309; font-weight: bold; }
  .margen-low    { color: #dc2626; font-weight: bold; }
  .total-row     { background-color: #e2e8f0; font-weight: bold; border-top: 2px solid #047857; border-bottom: 2px double #047857; height: 24pt; vertical-align: middle; }
  .firma-row     { border-top: 1px solid #64748b; text-align: center; font-size: 8.5pt; color: #64748b; padding-top: 4px; }
</style>
</head>
<body>
<table>
  <colgroup>
    <col style="width: 30pt;">    {{-- # --}}
    <col style="width: 160pt;">   {{-- Medicamento --}}
    <col style="width: 70pt;">    {{-- Código barras --}}
    <col style="width: 100pt;">   {{-- Categoría --}}
    <col style="width: 110pt;">   {{-- Laboratorio --}}
    <col style="width: 70pt;">    {{-- Costo Compra --}}
    <col style="width: 80pt;">    {{-- Precio Base --}}
    <col style="width: 55pt;">    {{-- Margen % --}}
    <col style="width: 200pt;">   {{-- Presentaciones --}}
    <col style="width: 80pt;">    {{-- Estado --}}
    <col style="width: 80pt;">    {{-- Última Actualización --}}
  </colgroup>

  {{-- Encabezado Institucional --}}
  <tr>
    <td colspan="11" class="header-brand">{{ strtoupper(configuracion('empresa_nombre', 'FARMABIEN')) }} — {{ strtoupper(configuracion('empresa_razon_social', 'Farmacia & Droguería FarmaBien')) }}</td>
  </tr>
  <tr>
    <td colspan="11" class="header-sub">RUC: {{ configuracion('empresa_ruc', '—') }} &bull; Tel: {{ configuracion('empresa_telefono', '—') }} &bull; {{ configuracion('empresa_direccion', '—') }}</td>
  </tr>
  <tr>
    <td colspan="11" class="report-title">CATÁLOGO OFICIAL DE PRECIOS DE VENTA VIGENTES</td>
  </tr>

  {{-- Metadatos --}}
  <tr>
    <td colspan="6" class="meta-cell"><strong>Fecha de Emisión:</strong> {{ now()->format('d/m/Y H:i:s') }}</td>
    <td colspan="5" class="meta-cell"><strong>Elaborado por:</strong> {{ auth()->user()->name ?? 'Sistema' }}</td>
  </tr>
  <tr>
    <td colspan="11" class="meta-cell">
      <strong>Total de medicamentos:</strong> {{ $productos->count() }} &bull;
      <strong>Con precio base &gt; 0:</strong> {{ $productos->filter(fn($p) => ($p->precio_venta ?? 0) > 0)->count() }} &bull;
      <strong>Margen promedio:</strong>
      @php
        $productosConPrecio = $productos->filter(fn($p) => ($p->precio_venta ?? 0) > 0 && ($p->precio_compra ?? 0) > 0);
        $margenProm = $productosConPrecio->count() > 0
          ? $productosConPrecio->avg(fn($p) => round((($p->precio_venta - $p->precio_compra) / $p->precio_venta) * 100, 1))
          : 0;
      @endphp
      {{ number_format($margenProm, 1) }}%
    </td>
  </tr>

  {{-- Separador --}}
  <tr><td colspan="11" style="height: 8pt;"></td></tr>

  {{-- KPIs --}}
  <tr>
    <td colspan="3" class="kpi-title">TOTAL MEDICAMENTOS</td>
    <td colspan="4" class="kpi-title">CON PRECIO CONFIGURADO</td>
    <td colspan="4" class="kpi-title">MARGEN PROMEDIO</td>
  </tr>
  <tr>
    <td colspan="3" class="kpi-val">{{ $productos->count() }}</td>
    <td colspan="4" class="kpi-val">{{ $productos->filter(fn($p) => ($p->precio_venta ?? 0) > 0)->count() }}</td>
    <td colspan="4" class="kpi-val">{{ number_format($margenProm, 1) }}%</td>
  </tr>

  {{-- Separador --}}
  <tr><td colspan="11" style="height: 8pt;"></td></tr>

  {{-- Encabezados tabla --}}
  <tr>
    <th class="th-col">#</th>
    <th class="th-col">Medicamento / Principio Activo</th>
    <th class="th-col">Cód. Barras</th>
    <th class="th-col">Categoría</th>
    <th class="th-col">Laboratorio</th>
    <th class="th-col">Costo Compra (C$)</th>
    <th class="th-col">Precio Base (C$)</th>
    <th class="th-col">Margen %</th>
    <th class="th-col">Presentaciones (nombre · factor · precio)</th>
    <th class="th-col">Control Sanitario</th>
    <th class="th-col">Últ. Actualización</th>
  </tr>

  {{-- Filas de datos --}}
  @php $fila = 1; @endphp
  @forelse($productos as $p)
    @php
      $costo  = (float)($p->precio_compra ?? 0);
      $precio = (float)($p->precio_venta  ?? 0);
      $margen = $precio > 0 ? round((($precio - $costo) / $precio) * 100, 1) : 0;
      $margenClass = $margen >= 30 ? 'margen-ok' : ($margen >= 15 ? 'margen-warn' : 'margen-low');

      $presList = $p->presentacionesActivas->map(fn($pr) =>
          "{$pr->nombre} x{$pr->unidades_por_presentacion}: C$ " . number_format($pr->precio_venta ?? 0, 2)
      )->implode(' | ');

      $rowClass = $fila % 2 === 0 ? 'row-even' : 'row-odd';
    @endphp
    <tr class="{{ $rowClass }}">
      <td class="td-center">{{ $fila++ }}</td>
      <td class="td-text">
        <strong>{{ $p->nombre }}</strong>
        @if($p->principio_activo)<br><small>{{ $p->principio_activo }}{{ $p->concentracion ? ' · ' . $p->concentracion : '' }}</small>@endif
      </td>
      <td class="td-center td-text">{{ $p->codigo_barra ?? '—' }}</td>
      <td class="td-text">{{ $p->categoria->nombre ?? 'Sin categoría' }}</td>
      <td class="td-text">{{ $p->laboratorio->nombre ?? 'Sin laboratorio' }}</td>
      <td class="td-money">{{ number_format($costo, 2) }}</td>
      <td class="td-money" style="font-weight: bold; color: #047857;">{{ number_format($precio, 2) }}</td>
      <td class="td-pct {{ $margenClass }}">{{ $margen }}%</td>
      <td class="td-text" style="font-size: 9pt;">{{ $presList ?: '—' }}</td>
      <td class="td-center td-text" style="font-size: 9pt;">{{ $p->tipo_control ?? 'venta_libre' }}</td>
      <td class="td-center td-text" style="font-size: 8.5pt;">{{ $p->updated_at ? $p->updated_at->format('d/m/Y') : '—' }}</td>
    </tr>
  @empty
    <tr>
      <td colspan="11" class="td-center" style="height: 30pt; color: #94a3b8;">No se encontraron medicamentos con precio configurado.</td>
    </tr>
  @endforelse

  {{-- Fila de totales --}}
  <tr class="total-row">
    <td colspan="5" class="td-text"><strong>TOTALES — {{ now()->format('d/m/Y') }}</strong></td>
    <td class="td-money"><strong>—</strong></td>
    <td class="td-money" style="color: #047857;">—</td>
    <td class="td-pct"><strong>{{ number_format($margenProm, 1) }}%</strong></td>
    <td colspan="3" class="td-text"><strong>{{ $productos->count() }} medicamentos exportados</strong></td>
  </tr>

  {{-- Separador --}}
  <tr><td colspan="11" style="height: 30pt;"></td></tr>

  {{-- Firmas --}}
  <tr>
    <td colspan="5" class="firma-row">___________________________<br>Responsable de Farmacia</td>
    <td colspan="6" class="firma-row">___________________________<br>Regente / Director Técnico Farmacéutico</td>
  </tr>
  <tr><td colspan="11" style="height: 6pt;"></td></tr>
  <tr>
    <td colspan="11" class="meta-cell" style="font-size: 8pt; color: #94a3b8;">
      Documento generado electrónicamente por el Sistema FarmaBien | {{ now()->format('d/m/Y H:i:s') }} | Los precios indicados son los vigentes al momento de la impresión. Precios en Córdobas nicaragüenses (C$).
    </td>
  </tr>
</table>
</body>
</html>
