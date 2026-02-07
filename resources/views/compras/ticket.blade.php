<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket #{{ str_pad($compra->id, 6, '0', STR_PAD_LEFT) }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.3;
            color: #000;
            background: #fff;
            width: 80mm; /* Ancho para impresora térmica 80mm */
            margin: 0 auto;
            padding: 10px;
        }
        
        /* Para impresoras de 58mm, cambiar a: width: 58mm; */
        
        .ticket {
            width: 100%;
        }
        
        .center {
            text-align: center;
        }
        
        .left {
            text-align: left;
        }
        
        .right {
            text-align: right;
        }
        
        .bold {
            font-weight: bold;
        }
        
        .large {
            font-size: 16px;
            font-weight: bold;
        }
        
        .medium {
            font-size: 14px;
        }
        
        .small {
            font-size: 10px;
        }
        
        .line {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }
        
        .double-line {
            border-top: 2px solid #000;
            margin: 8px 0;
        }
        
        .header {
            margin-bottom: 10px;
        }
        
        .company-name {
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .document-type {
            font-size: 16px;
            font-weight: bold;
            margin: 8px 0;
            padding: 5px;
            border: 2px solid #000;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin: 3px 0;
        }
        
        .label {
            font-weight: bold;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 5px 0;
        }
        
        th {
            border-bottom: 1px solid #000;
            padding: 3px 0;
            font-size: 11px;
            text-align: left;
        }
        
        td {
            padding: 3px 0;
            font-size: 11px;
        }
        
        .item-name {
            font-weight: bold;
        }
        
        .item-lote {
            font-size: 9px;
            color: #333;
        }
        
        .total-section {
            margin-top: 8px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin: 3px 0;
        }
        
        .total-final {
            font-size: 16px;
            font-weight: bold;
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            padding: 5px 0;
            margin: 5px 0;
        }
        
        .footer {
            margin-top: 10px;
            font-size: 10px;
        }
        
        .qr-placeholder {
            width: 100px;
            height: 100px;
            border: 2px solid #000;
            margin: 10px auto;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
        }
        
        @media print {
            body {
                width: 80mm;
                margin: 0;
                padding: 5px;
            }
            
            .no-print {
                display: none !important;
            }
            
            @page {
                size: 80mm auto;
                margin: 0;
            }
        }
        
        .print-button {
            position: fixed;
            top: 10px;
            right: 10px;
            padding: 10px 20px;
            background: #000;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            z-index: 1000;
        }
        
        .print-button:hover {
            background: #333;
        }
    </style>
</head>
<body>
    <button class="print-button no-print" onclick="window.print()">🖨️ Imprimir Ticket</button>
    
    <div class="ticket">
        <!-- Header -->
        <div class="header center">
            <div class="company-name">{{ $empresa['nombre'] }}</div>
            <div class="small">RUC: {{ $empresa['ruc'] }}</div>
            <div class="small">{{ $empresa['direccion'] }}</div>
            <div class="small">Tel: {{ $empresa['telefono'] }}</div>
        </div>
        
        <div class="double-line"></div>
        
        <!-- Document Type -->
        <div class="document-type center">
            {{ strtoupper($compra->tipo_comprobante ?? 'COMPRA') }}
            <div>N° {{ str_pad($compra->id, 6, '0', STR_PAD_LEFT) }}</div>
        </div>
        
        <div class="line"></div>
        
        <!-- Purchase Info -->
        <div class="info-row">
            <span class="label">FECHA:</span>
            <span>{{ $compra->fecha->format('d/m/Y H:i') }}</span>
        </div>
        
        <div class="info-row">
            <span class="label">PROVEEDOR:</span>
        </div>
        <div style="margin-left: 10px;">
            {{ $compra->proveedor->nombre }}
        </div>
        <div style="margin-left: 10px; font-size: 10px;">
            RUC: {{ $compra->proveedor->ruc }}
        </div>
        
        @if($compra->numero_comprobante)
        <div class="info-row">
            <span class="label">N° COMPROBANTE:</span>
            <span>{{ $compra->numero_comprobante }}</span>
        </div>
        @endif
        
        <div class="info-row">
            <span class="label">CAJERO:</span>
            <span>{{ $compra->usuario->name }}</span>
        </div>
        
        <div class="double-line"></div>
        
        <!-- Products -->
        <table>
            <thead>
                <tr>
                    <th style="width: 50%">PRODUCTO</th>
                    <th style="width: 15%" class="center">CANT</th>
                    <th style="width: 17%" class="right">P.U.</th>
                    <th style="width: 18%" class="right">SUBT</th>
                </tr>
            </thead>
            <tbody>
                @foreach($compra->detalles as $detalle)
                <tr>
                    <td colspan="4" class="item-name">{{ $detalle->producto->nombre }}</td>
                </tr>
                <tr>
                    <td colspan="4" class="item-lote">
                        @if($detalle->usaPresentacion())
                            Pres: {{ $detalle->nombre_presentacion }} @if($detalle->unidades_por_presentacion > 1)(x{{ (int) $detalle->unidades_por_presentacion }})@endif
                            | Cant: {{ (int) $detalle->cantidad_presentaciones }}
                            @if($detalle->unidades_por_presentacion > 1)
                                → {{ (int) ($detalle->cantidad_unidades_base ?? $detalle->cantidad) }} unid
                            @endif
                        @else
                            Unidad base | Cant: {{ (int) ($detalle->cantidad_unidades_base ?? $detalle->cantidad) }}
                        @endif
                    </td>
                </tr>
                <tr>
                  <tr>
    <td class="item-lote">
        {{-- Lote: {{ $detalle->lote->numero_lote ?? '-' }}
        @if($detalle->lote?->fecha_vencimiento)
            | Venc: {{ $detalle->lote->fecha_vencimiento->format('d/m/Y') }}
        @endif --}}
    </td>

    <td class="right">
        {{ (int) ($detalle->cantidad_unidades_base ?? $detalle->cantidad) }}
    </td>

    <td class="right">
        {{ number_format((float) $detalle->precio_unitario, 2) }}
    </td>

    <td class="right">
        {{ number_format((float) $detalle->subtotal, 2) }}
    </td>
</tr>

                </tr>
                @endforeach
            </tbody>
        </table>
        
        <div class="double-line"></div>
        
        <!-- Totals -->
        <div class="total-section">
            <div class="total-row total-final">
                <span class="label">TOTAL:</span>
                <span>S/ {{ number_format($compra->total, 2) }}</span>
            </div>
        </div>
        
        <div class="line"></div>
        
        <!-- Additional Info -->
        @if($compra->observaciones)
        <div style="margin: 8px 0;">
            <div class="label">OBSERVACIONES:</div>
            <div class="small">{{ $compra->observaciones }}</div>
        </div>
        <div class="line"></div>
        @endif
        
        <!-- QR Code Placeholder -->
        <div class="qr-placeholder no-print">
            QR CODE
            <br>
            <small>ID: {{ $compra->id }}</small>
        </div>
        
        <!-- Footer -->
        <div class="footer center">
            <div class="line"></div>
            <div class="small">{{ config('app.name') }}</div>
            <div class="small">Sistema de Gestión</div>
            <div class="small">{{ now()->format('d/m/Y H:i:s') }}</div>
            <div class="line"></div>
            <div class="small bold">GRACIAS POR SU COMPRA</div>
            <div style="margin-top: 10px;">
                <div class="small">_________________________</div>
                <div class="small">Firma y Sello</div>
            </div>
        </div>
        
        <!-- Espacio para corte -->
        <div style="height: 30px;"></div>
    </div>
    
    <script>
        // Configurar para impresora térmica
        window.onload = function() {
            // Auto-imprimir (opcional)
            // setTimeout(function() { window.print(); }, 500);
        }
        
        // Cerrar después de imprimir
        window.onafterprint = function() {
            // window.close();
        }
    </script>
</body>
</html>