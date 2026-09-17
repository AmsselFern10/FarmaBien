<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comprobante de Compra #{{ str_pad($compra->id, 5, '0', STR_PAD_LEFT) }}</title>
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
    </style>
</head>
<body>

    <!-- Header -->
    <table class="header-table">
        <tr>
            <td style="width: 60%; vertical-align: middle;">
                <div class="company-name">FarmaBien</div>
                <div style="font-size: 10px; color: #64748b; margin-top: 2px;">
                    Sistema Integral de Farmacia & Control de Inventario Kardex<br>
                    RUC: 20489123851 • Central: (01) 555-0199
                </div>
            </td>
            <td style="width: 40%; vertical-align: middle;">
                <div class="doc-title-box">
                    <div class="doc-title">GUÍA DE RECEPCIÓN DE COMPRA</div>
                    <div class="doc-number">N° {{ str_pad($compra->id, 6, '0', STR_PAD_LEFT) }}</div>
                    <div style="font-size: 9px; color: #475569; margin-top: 3px;">
                        Estado: <strong>{{ strtoupper($compra->estado) }}</strong>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Info Grid -->
    <table style="width: 100%; margin-bottom: 10px;">
        <tr>
            <!-- Proveedor -->
            <td style="width: 50%; vertical-align: top; padding-right: 10px;">
                <div class="section-title">Información del Proveedor</div>
                <table class="info-table">
                    <tr>
                        <td class="label">Razón Social:</td>
                        <td><strong>{{ $compra->proveedor->nombre ?? 'N/A' }}</strong></td>
                    </tr>
                    <tr>
                        <td class="label">RUC / NIT:</td>
                        <td>{{ $compra->proveedor->ruc ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Teléfono:</td>
                        <td>{{ $compra->proveedor->telefono ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Dirección:</td>
                        <td>{{ $compra->proveedor->direccion ?? '-' }}</td>
                    </tr>
                </table>
            </td>

            <!-- Comprobante & Recepción -->
            <td style="width: 50%; vertical-align: top; padding-left: 10px;">
                <div class="section-title">Detalles del Comprobante</div>
                <table class="info-table">
                    <tr>
                        <td class="label">N° Factura/Doc:</td>
                        <td><strong>{{ $compra->numero_comprobante ?: 'Sin N° Doc' }}</strong></td>
                    </tr>
                    <tr>
                        <td class="label">Fecha Emisión:</td>
                        <td>{{ $compra->fecha ? $compra->fecha->format('d/m/Y') : '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Registrado por:</td>
                        <td>{{ $compra->usuario->name ?? 'Sistema' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Fecha Recepción:</td>
                        <td>{{ $compra->created_at ? $compra->created_at->format('d/m/Y H:i') : '-' }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Tabla de Ítems y Lotes -->
    <div class="section-title">Detalle de Medicamentos y Lotes Ingresados al Kardex</div>
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 20px;" class="text-center">#</th>
                <th>Medicamento / Fármaco</th>
                <th>Presentación</th>
                <th class="text-center" style="width: 45px;">Cant.</th>
                <th class="text-center" style="width: 45px;">Factor</th>
                <th class="text-center" style="width: 60px;">U. Base</th>
                <th>Lote & Vencimiento</th>
                <th class="text-right" style="width: 60px;">P. Unit.</th>
                <th class="text-right" style="width: 65px;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($compra->detalles as $i => $det)
            <tr>
                <td class="text-center">{{ $i + 1 }}</td>
                <td>
                    <strong>{{ $det->producto->nombre ?? 'Producto eliminado' }}</strong>
                    @if($det->producto && $det->producto->principio_activo)
                        <br><span style="color: #64748b; font-size: 8.5px;">{{ $det->producto->principio_activo }}</span>
                    @endif
                </td>
                <td>{{ $det->tipo_presentacion ?: 'Unidad Base' }}</td>
                <td class="text-center font-bold">{{ $det->cantidad_presentaciones ?: $det->cantidad_unidades_base }}</td>
                <td class="text-center" style="color: #64748b;">x{{ $det->unidades_por_presentacion ?: 1 }}</td>
                <td class="text-center font-bold" style="color: #047857;">{{ $det->cantidad_unidades_base }}</td>
                <td>
                    @if($det->lote)
                        <strong>{{ $det->lote->numero_lote }}</strong>
                        <br><span style="color: #64748b; font-size: 8.5px;">Vence: {{ $det->lote->fecha_vencimiento ? $det->lote->fecha_vencimiento->format('d/m/Y') : '-' }}</span>
                    @else
                        -
                    @endif
                </td>
                <td class="text-right">${{ number_format($det->precio_unitario, 2) }}</td>
                <td class="text-right font-bold">${{ number_format($det->subtotal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totales -->
    <table class="totals-table">
        <tr>
            <td class="label">Total Ítems:</td>
            <td class="text-right font-bold">{{ $compra->detalles->count() }}</td>
        </tr>
        <tr>
            <td class="label">Total U. Base Kardex:</td>
            <td class="text-right font-bold" style="color: #047857;">{{ $compra->detalles->sum('cantidad_unidades_base') }} u.</td>
        </tr>
        <tr class="total-row">
            <td>TOTAL COMPRA:</td>
            <td class="text-right">${{ number_format($compra->total, 2) }}</td>
        </tr>
    </table>

    <!-- Firmas -->
    <table class="signatures">
        <tr>
            <td>
                <div class="sig-line">
                    <strong>Recibido por (Almacén / Farmacia)</strong><br>
                    Firma y Sello
                </div>
            </td>
            <td>
                <div class="sig-line">
                    <strong>Autorizado por (Administración)</strong><br>
                    Firma y Sello
                </div>
            </td>
        </tr>
    </table>

</body>
</html>