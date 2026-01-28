<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compra #{{ str_pad($compra->id, 6, '0', STR_PAD_LEFT) }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #000;
            background: #fff;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        /* Header */
        .header {
            border-bottom: 3px solid #2563eb;
            padding-bottom: 15px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        
        .company-info {
            flex: 1;
        }
        
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 5px;
        }
        
        .company-details {
            font-size: 11px;
            color: #666;
            line-height: 1.6;
        }
        
        .document-type {
            text-align: right;
            padding: 10px 15px;
            background: #2563eb;
            color: white;
            border-radius: 5px;
        }
        
        .document-type h2 {
            font-size: 18px;
            margin-bottom: 5px;
        }
        
        .document-number {
            font-size: 20px;
            font-weight: bold;
        }
        
        /* Info boxes */
        .info-section {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .info-box {
            flex: 1;
            border: 1px solid #e5e7eb;
            border-radius: 5px;
            padding: 12px;
            background: #f9fafb;
        }
        
        .info-box h3 {
            font-size: 11px;
            color: #6b7280;
            text-transform: uppercase;
            margin-bottom: 8px;
            font-weight: 600;
        }
        
        .info-box p {
            margin-bottom: 4px;
            font-size: 12px;
        }
        
        .info-box strong {
            color: #000;
        }
        
        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        thead {
            background: #1e40af;
            color: white;
        }
        
        th {
            padding: 10px 8px;
            text-align: left;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        th.right {
            text-align: right;
        }
        
        tbody tr {
            border-bottom: 1px solid #e5e7eb;
        }
        
        tbody tr:nth-child(even) {
            background: #f9fafb;
        }
        
        td {
            padding: 10px 8px;
            font-size: 12px;
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
        }
        
        .product-category {
            font-size: 10px;
            color: #6b7280;
        }
        
        .lote-info {
            font-family: 'Courier New', monospace;
            font-size: 11px;
            color: #059669;
        }
        
        /* Totals */
        .totals {
            margin-top: 20px;
            float: right;
            width: 300px;
        }
        
        .totals table {
            margin: 0;
        }
        
        .totals td {
            padding: 8px 12px;
            border: none;
        }
        
        .totals .label {
            text-align: right;
            font-weight: 600;
            color: #6b7280;
        }
        
        .totals .amount {
            text-align: right;
            font-weight: bold;
        }
        
        .total-final {
            background: #2563eb;
            color: white !important;
            font-size: 16px;
        }
        
        .total-final td {
            padding: 12px;
        }
        
        /* Footer */
        .footer {
            clear: both;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #e5e7eb;
            font-size: 11px;
            color: #6b7280;
            text-align: center;
        }
        
        .signatures {
            display: flex;
            justify-content: space-around;
            margin: 30px 0;
        }
        
        .signature-box {
            text-align: center;
            width: 200px;
        }
        
        .signature-line {
            border-top: 2px solid #000;
            margin-bottom: 5px;
            padding-top: 5px;
        }
        
        /* Print styles */
        @media print {
            body {
                background: white;
            }
            
            .container {
                max-width: 100%;
                padding: 0;
            }
            
            .no-print {
                display: none !important;
            }
            
            @page {
                margin: 1cm;
            }
        }
        
        /* Print button */
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 24px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            z-index: 1000;
        }
        
        .print-button:hover {
            background: #1e40af;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 11px;
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
    </style>
</head>
<body>
    <button class="print-button no-print" onclick="window.print()">
        🖨️ Imprimir
    </button>
    
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="company-info">
                <div class="company-name">{{ config('app.name', 'FarmaBien') }}</div>
                <div class="company-details">
                    <strong>RUC:</strong> 20123456789<br>
                    <strong>Dirección:</strong> Av. Principal 123, Lima<br>
                    <strong>Teléfono:</strong> (01) 234-5678<br>
                    <strong>Email:</strong> contacto@farmabien.com
                </div>
            </div>
            <div class="document-type">
                <h2>{{ strtoupper($compra->tipo_comprobante ?? 'COMPRA') }}</h2>
                <div class="document-number">#{{ str_pad($compra->id, 6, '0', STR_PAD_LEFT) }}</div>
            </div>
        </div>
        
        <!-- Info Section -->
        <div class="info-section">
            <div class="info-box">
                <h3>Proveedor</h3>
                <p><strong>{{ $compra->proveedor->nombre }}</strong></p>
                <p>RUC: {{ $compra->proveedor->ruc }}</p>
                @if($compra->proveedor->telefono)
                <p>Tel: {{ $compra->proveedor->telefono }}</p>
                @endif
                @if($compra->proveedor->email)
                <p>Email: {{ $compra->proveedor->email }}</p>
                @endif
            </div>
            
            <div class="info-box">
                <h3>Información de Compra</h3>
                <p><strong>Fecha:</strong> {{ $compra->fecha->format('d/m/Y') }}</p>
                <p><strong>Registrado por:</strong> {{ $compra->usuario->name }}</p>
                <p><strong>Estado:</strong> 
                    <span class="status-badge status-{{ $compra->estado }}">
                        {{ $compra->estado }}
                    </span>
                </p>
                @if($compra->numero_comprobante)
                <p><strong>N° Comprobante:</strong> {{ $compra->numero_comprobante }}</p>
                @endif
            </div>
        </div>
        
        <!-- Products Table -->
        <table>
            <thead>
                <tr>
                    <th style="width: 5%">#</th>
                    <th style="width: 35%">Producto</th>
                    <th style="width: 15%">Lote</th>
                    <th style="width: 12%">Vencimiento</th>
                    <th class="right" style="width: 10%">Cantidad</th>
                    <th class="right" style="width: 11%">P. Unit.</th>
                    <th class="right" style="width: 12%">Subtotal</th>
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
        <div class="totals">
            <table>
                <tr class="total-final">
                    <td class="label">TOTAL</td>
                    <td class="amount">S/ {{ number_format($compra->total, 2) }}</td>
                </tr>
            </table>
        </div>
        
        <div style="clear: both;"></div>
        
        <!-- Signatures -->
        <div class="signatures">
            <div class="signature-box">
                <div style="height: 60px;"></div>
                <div class="signature-line">
                    Recibido por
                </div>
                <div style="font-size: 10px; color: #6b7280;">Nombre y Firma</div>
            </div>
            
            <div class="signature-box">
                <div style="height: 60px;"></div>
                <div class="signature-line">
                    Entregado por
                </div>
                <div style="font-size: 10px; color: #6b7280;">Nombre y Firma</div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <p><strong>{{ config('app.name', 'FarmaBien') }}</strong> - Sistema de Gestión Farmacéutica</p>
            <p>Documento generado el {{ now()->format('d/m/Y H:i:s') }}</p>
            @if($compra->observaciones)
            <p style="margin-top: 10px;"><strong>Observaciones:</strong> {{ $compra->observaciones }}</p>
            @endif
        </div>
    </div>
    
    <script>
        // Auto-imprimir al cargar (opcional)
        // window.onload = function() { window.print(); }
        
        // Cerrar ventana después de imprimir
        window.onafterprint = function() {
            // window.close();
        }
    </script>
</body>
</html>