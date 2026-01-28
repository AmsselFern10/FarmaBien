<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Compra #{{ str_pad($compra->id, 6, '0', STR_PAD_LEFT) }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', 'Arial', sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #000;
        }
        
        .container {
            padding: 20px;
        }
        
        /* Header */
        .header {
            border-bottom: 3px solid #2563eb;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        
        .header-content {
            display: table;
            width: 100%;
        }
        
        .company-info {
            display: table-cell;
            width: 60%;
            vertical-align: top;
        }
        
        .company-name {
            font-size: 22px;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 5px;
        }
        
        .company-details {
            font-size: 10px;
            color: #666;
            line-height: 1.6;
        }
        
        .document-type {
            display: table-cell;
            width: 40%;
            text-align: right;
            vertical-align: top;
            padding: 10px;
            background: #2563eb;
            color: white;
            border-radius: 5px;
        }
        
        .document-type h2 {
            font-size: 16px;
            margin-bottom: 5px;
        }
        
        .document-number {
            font-size: 18px;
            font-weight: bold;
        }
        
        /* Info boxes */
        .info-section {
            margin-bottom: 15px;
        }
        
        .info-box {
            display: inline-block;
            width: 48%;
            border: 1px solid #e5e7eb;
            border-radius: 5px;
            padding: 10px;
            background: #f9fafb;
            vertical-align: top;
            margin-right: 2%;
        }
        
        .info-box:last-child {
            margin-right: 0;
        }
        
        .info-box h3 {
            font-size: 10px;
            color: #6b7280;
            text-transform: uppercase;
            margin-bottom: 6px;
            font-weight: 600;
        }
        
        .info-box p {
            margin-bottom: 3px;
            font-size: 11px;
        }
        
        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        
        thead {
            background: #1e40af;
            color: white;
        }
        
        th {
            padding: 8px 6px;
            text-align: left;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        th.right {
            text-align: right;
        }
        
        th.center {
            text-align: center;
        }
        
        tbody tr {
            border-bottom: 1px solid #e5e7eb;
        }
        
        tbody tr:nth-child(even) {
            background: #f9fafb;
        }
        
        td {
            padding: 7px 6px;
            font-size: 10px;
        }
        
        td.right {
            text-align: right;
        }
        
        td.center {
            text-align: center;
        }
        
        .product-name {
            font-weight: 600;
            color: #000;
            font-size: 11px;
        }
        
        .product-category {
            font-size: 9px;
            color: #6b7280;
        }
        
        .lote-info {
            font-family: 'Courier New', monospace;
            font-size: 9px;
            color: #059669;
        }
        
        /* Totals */
        .totals-section {
            margin-top: 15px;
        }
        
        .totals-table {
            float: right;
            width: 280px;
        }
        
        .totals-table table {
            margin: 0;
        }
        
        .totals-table td {
            padding: 6px 10px;
            border: none;
        }
        
        .totals-table .label {
            text-align: right;
            font-weight: 600;
            color: #6b7280;
        }
        
        .totals-table .amount {
            text-align: right;
            font-weight: bold;
        }
        
        .total-final {
            background: #2563eb;
            color: white;
            font-size: 14px;
        }
        
        .total-final td {
            padding: 10px;
            font-weight: bold;
        }
        
        /* Footer */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 15px 20px;
            border-top: 2px solid #e5e7eb;
            font-size: 9px;
            color: #6b7280;
            text-align: center;
            background: white;
        }
        
        .signatures {
            margin: 25px 0 80px 0;
            clear: both;
        }
        
        .signature-box {
            display: inline-block;
            text-align: center;
            width: 45%;
            margin: 0 2%;
        }
        
        .signature-line {
            border-top: 2px solid #000;
            margin-top: 50px;
            margin-bottom: 5px;
            padding-top: 5px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 10px;
            font-size: 9px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-recibida {
            background: #d1fae5;
            color: #065f46;
        }
        
        .status-anulada {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .clearfix {
            clear: both;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-content">
                <div class="company-info">
                    <div class="company-name">{{ $empresa['nombre'] }}</div>
                    <div class="company-details">
                        <strong>RUC:</strong> {{ $empresa['ruc'] }}<br>
                        <strong>Dirección:</strong> {{ $empresa['direccion'] }}<br>
                        <strong>Teléfono:</strong> {{ $empresa['telefono'] }}<br>
                        <strong>Email:</strong> {{ $empresa['email'] }}
                    </div>
                </div>
                <div class="document-type">
                    <h2>{{ strtoupper($compra->tipo_comprobante ?? 'COMPRA') }}</h2>
                    <div class="document-number">#{{ str_pad($compra->id, 6, '0', STR_PAD_LEFT) }}</div>
                </div>
            </div>
        </div>
        
        <!-- Info Section -->
        <div class="info-section">
            <div class="info-box">
                <h3>Proveedor</h3>
                <p><strong>{{ $compra->proveedor->nombre }}</strong></p>
                <p>RUC: {{ $compra->proveedor->ruc }}</p>
                @if($compra->proveedor->direccion)
                <p>Dir: {{ $compra->proveedor->direccion }}</p>
                @endif
                @if($compra->proveedor->telefono)
                <p>Tel: {{ $compra->proveedor->telefono }}</p>
                @endif
            </div>
            
            <div class="info-box">
                <h3>Información de Compra</h3>
                <p><strong>Fecha:</strong> {{ $compra->fecha->format('d/m/Y') }}</p>
                <p><strong>Hora:</strong> {{ $compra->created_at->format('H:i:s') }}</p>
                <p><strong>Registrado por:</strong> {{ $compra->usuario->name }}</p>
                <p><strong>Estado:</strong> 
                    <span class="status-badge status-{{ $compra->estado }}">
                        {{ $compra->estado }}
                    </span>
                </p>
            </div>
        </div>
        
        <!-- Products Table -->
        <table>
            <thead>
                <tr>
                    <th style="width: 5%" class="center">#</th>
                    <th style="width: 35%">Producto</th>
                    <th style="width: 13%">Lote</th>
                    <th style="width: 11%" class="center">Vencimiento</th>
                    <th style="width: 10%" class="right">Cant.</th>
                    <th style="width: 13%" class="right">P. Unit.</th>
                    <th style="width: 13%" class="right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($compra->detalles as $index => $detalle)
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td>
                        <div class="product-name">{{ $detalle->producto->nombre }}</div>
                        <div class="product-category">{{ $detalle->producto->categoria->nombre }}</div>
                    </td>
                    <td>
                        <span class="lote-info">{{ $detalle->lote->numero_lote }}</span>
                    </td>
                    <td class="center">{{ $detalle->lote->fecha_vencimiento->format('d/m/Y') }}</td>
                    <td class="right">{{ $detalle->cantidad }}</td>
                    <td class="right">S/ {{ number_format($detalle->precio_unitario, 2) }}</td>
                    <td class="right"><strong>S/ {{ number_format($detalle->subtotal, 2) }}</strong></td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        <!-- Totals -->
        <div class="totals-section">
            <div class="totals-table">
                <table>
                    <tr class="total-final">
                        <td class="label">TOTAL</td>
                        <td class="amount">S/ {{ number_format($compra->total, 2) }}</td>
                    </tr>
                </table>
            </div>
        </div>
        
        <div class="clearfix"></div>
        
        @if($compra->observaciones)
        <div style="margin-top: 15px; padding: 10px; background: #fef3c7; border-left: 3px solid #f59e0b;">
            <strong style="color: #92400e;">Observaciones:</strong><br>
            <span style="font-size: 10px; color: #78350f;">{{ $compra->observaciones }}</span>
        </div>
        @endif
        
        <!-- Signatures -->
        <div class="signatures">
            <div class="signature-box">
                <div class="signature-line">
                    Recibido por
                </div>
                <div style="font-size: 9px; color: #6b7280;">Nombre y Firma</div>
            </div>
            
            <div class="signature-box">
                <div class="signature-line">
                    Entregado por
                </div>
                <div style="font-size: 9px; color: #6b7280;">Nombre y Firma</div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <p><strong>{{ $empresa['nombre'] }}</strong> - Sistema de Gestión Farmacéutica</p>
            <p>Documento generado el {{ now()->format('d/m/Y H:i:s') }} | Compra #{{ str_pad($compra->id, 6, '0', STR_PAD_LEFT) }}</p>
        </div>
    </div>
</body>
</html>