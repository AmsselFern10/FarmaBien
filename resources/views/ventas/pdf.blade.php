<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura {{ $venta->serie ? $venta->serie . '-' : '' }}{{ str_pad($venta->numero_comprobante ?? $venta->id, 6, '0', STR_PAD_LEFT) }}</title>
    <style>
        @page {
            margin: 25px 30px;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #1e293b;
            line-height: 1.3;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #10b981;
            padding-bottom: 12px;
            margin-bottom: 15px;
        }
        .company-name {
            font-size: 20px;
            font-weight: bold;
            color: #047857;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .doc-title-box {
            border: 1.5px solid #047857;
            background-color: #ecfdf5;
            padding: 8px 12px;
            text-align: center;
            border-radius: 4px;
        }
        .doc-title {
            font-size: 13px;
            font-weight: bold;
            color: #065f46;
        }
        .doc-number {
            font-size: 15px;
            font-weight: bold;
            color: #047857;
            margin-top: 2px;
        }
        .section-title {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            color: #334155;
            background-color: #f1f5f9;
            padding: 4px 8px;
            margin-bottom: 6px;
            border-left: 3px solid #10b981;
        }
        .info-table {
            width: 100%;
            margin-bottom: 12px;
        }
        .info-table td {
            vertical-align: top;
            padding: 2px 4px;
        }
        .label {
            font-weight: bold;
            color: #475569;
            width: 120px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 15px;
        }
        .items-table th {
            background-color: #047857;
            color: #ffffff;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 6px 4px;
            text-align: left;
            border: 1px solid #047857;
        }
        .items-table td {
            padding: 5px 4px;
            border: 1px solid #cbd5e1;
            font-size: 9.5px;
        }
        .items-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }

        .totals-table {
            width: 40%;
            margin-left: auto;
            border-collapse: collapse;
            margin-top: 5px;
        }
        .totals-table td {
            padding: 4px 6px;
            font-size: 10px;
        }
        .totals-table .total-row {
            background-color: #ecfdf5;
            font-size: 12px;
            font-weight: bold;
            color: #047857;
            border-top: 1.5px solid #047857;
        }
        .signatures {
            margin-top: 40px;
            width: 100%;
        }
        .signatures td {
            width: 50%;
            text-align: center;
            vertical-align: bottom;
            padding: 0 30px;
        }
        .sig-line {
            border-top: 1px solid #64748b;
            padding-top: 5px;
            font-size: 10px;
            color: #475569;
        }
        .badge-anulada {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1.5px solid #f87171;
            padding: 3px 10px;
            font-size: 10px;
            font-weight: bold;
            border-radius: 4px;
            display: inline-block;
            margin-top: 4px;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <table class="header-table">
        <tr>
            <td style="width: 60%; vertical-align: middle;">
                <div class="company-name">FarmaBien</div>
                <div style="font-size: 10px; color: #64748b; margin-top: 2px;">
                    Sistema Integral de Farmacia &amp; Control de Inventario Kardex<br>
                    RUC: 20489123851 &bull; Central: (01) 555-0199
                </div>
            </td>
            <td style="width: 40%; vertical-align: middle;">
                <div class="doc-title-box">
                    <div class="doc-title">
                        @if($venta->tipo_comprobante === 'factura')
                            FACTURA DE VENTA
                        @elseif($venta->tipo_comprobante === 'credito_fiscal')
                            FACTURA CRÉDITO FISCAL
                        @else
                            COMPROBANTE DE VENTA
                        @endif
                    </div>
                    <div class="doc-number">
                        N° {{ $venta->serie ? $venta->serie . '-' : '' }}{{ str_pad($venta->numero_comprobante ?? $venta->id, 6, '0', STR_PAD_LEFT) }}
                    </div>
                    <div style="font-size: 9px; color: #475569; margin-top: 3px;">
                        Estado: <strong>{{ strtoupper($venta->estado) }}</strong>
                        @if($venta->estado === 'anulada')
                            <span class="badge-anulada">ANULADA</span>
                        @endif
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Info Grid -->
    <table style="width: 100%; margin-bottom: 10px;">
        <tr>
            <!-- Cliente -->
            <td style="width: 50%; vertical-align: top; padding-right: 10px;">
                <div class="section-title">Información del Cliente</div>
                <table class="info-table">
                    <tr>
                        <td class="label">Nombre:</td>
                        <td><strong>{{ $venta->cliente?->nombre ?? 'Consumidor Final' }}</strong></td>
                    </tr>
                    @if($venta->cliente?->documento)
                    <tr>
                        <td class="label">Documento:</td>
                        <td>{{ $venta->cliente->tipo_documento ?? 'CI' }}: {{ $venta->cliente->documento }}</td>
                    </tr>
                    @endif
                    @if($venta->cliente?->telefono)
                    <tr>
                        <td class="label">Teléfono:</td>
                        <td>{{ $venta->cliente->telefono }}</td>
                    </tr>
                    @endif
                    @if($venta->cliente?->direccion)
                    <tr>
                        <td class="label">Dirección:</td>
                        <td>{{ $venta->cliente->direccion }}</td>
                    </tr>
                    @endif
                </table>
            </td>

            <!-- Comprobante & Datos venta -->
            <td style="width: 50%; vertical-align: top; padding-left: 10px;">
                <div class="section-title">Detalles del Comprobante</div>
                <table class="info-table">
                    <tr>
                        <td class="label">Fecha Venta:</td>
                        <td><strong>{{ ($venta->fecha ?? $venta->created_at)?->format('d/m/Y H:i') }}</strong></td>
                    </tr>
                    <tr>
                        <td class="label">Método Pago:</td>
                        <td>{{ strtoupper(str_replace('_', ' ', $venta->metodo_pago ?? 'efectivo')) }}</td>
                    </tr>
                    @if($venta->monto_recibido)
                    <tr>
                        <td class="label">Monto Recibido:</td>
                        <td>C$ {{ number_format($venta->monto_recibido, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="label">Cambio:</td>
                        <td>C$ {{ number_format($venta->cambio ?? 0, 2) }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td class="label">Atendido por:</td>
                        <td>{{ $venta->usuario?->name ?? 'Sistema' }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Tabla de Ítems -->
    <div class="section-title">Detalle de Medicamentos Vendidos</div>
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 20px;" class="text-center">#</th>
                <th>Medicamento / Fármaco</th>
                <th>Presentación</th>
                <th class="text-center" style="width: 40px;">Cant.</th>
                <th>Lote</th>
                <th class="text-right" style="width: 65px;">P. Unit. C$</th>
                <th class="text-right" style="width: 70px;">Subtotal C$</th>
            </tr>
        </thead>
        <tbody>
            @foreach($venta->detalles as $i => $det)
            <tr>
                <td class="text-center">{{ $i + 1 }}</td>
                <td>
                    <strong>{{ $det->producto?->nombre ?? 'Producto eliminado' }}</strong>
                    @if($det->producto?->principio_activo)
                        <br><span style="color: #64748b; font-size: 8.5px;">{{ $det->producto->principio_activo }}</span>
                    @endif
                </td>
                <td>{{ $det->presentacion?->nombre ?? 'Unidad Base' }}</td>
                <td class="text-center font-bold">{{ $det->cantidad }}</td>
                <td style="font-size: 8.5px;">
                    @if($det->lote)
                        {{ $det->lote->numero_lote }}
                        <br><span style="color: #64748b;">Vence: {{ $det->lote->fecha_vencimiento?->format('d/m/Y') ?? '-' }}</span>
                    @else
                        -
                    @endif
                </td>
                <td class="text-right">{{ number_format($det->precio_unitario, 2) }}</td>
                <td class="text-right font-bold">{{ number_format($det->subtotal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totales -->
    <table class="totals-table">
        @if($venta->descuento > 0)
        <tr>
            <td class="label">Subtotal:</td>
            <td class="text-right">C$ {{ number_format($venta->subtotal, 2) }}</td>
        </tr>
        <tr>
            <td class="label">Descuento:</td>
            <td class="text-right" style="color: #dc2626;">- C$ {{ number_format($venta->descuento, 2) }}</td>
        </tr>
        @endif
        @if($venta->impuesto > 0)
        <tr>
            <td class="label">IVA / Impuesto:</td>
            <td class="text-right">C$ {{ number_format($venta->impuesto, 2) }}</td>
        </tr>
        @endif
        <tr class="total-row">
            <td>TOTAL VENTA:</td>
            <td class="text-right">C$ {{ number_format($venta->total, 2) }}</td>
        </tr>
    </table>

    <!-- Firmas -->
    <table class="signatures">
        <tr>
            <td>
                <div class="sig-line">
                    <strong>Entregado por (Farmacéutico)</strong><br>
                    Firma y Sello
                </div>
            </td>
            <td>
                <div class="sig-line">
                    <strong>Recibido por (Cliente)</strong><br>
                    Firma y Número de Documento
                </div>
            </td>
        </tr>
    </table>

</body>
</html>
