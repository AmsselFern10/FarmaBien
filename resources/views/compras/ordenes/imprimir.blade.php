<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orden de Compra {{ $orden->numero_orden }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #059669;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .logo-title {
            font-size: 24px;
            font-weight: 900;
            color: #059669;
        }
        .doc-title {
            font-size: 18px;
            font-weight: 800;
            color: #111827;
            text-align: right;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 25px;
        }
        .card {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px;
        }
        .card h4 {
            margin: 0 0 8px 0;
            font-size: 13px;
            color: #111827;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 4px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        th, td {
            padding: 10px;
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
        }
        th {
            background: #f3f4f6;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .total-box {
            float: right;
            width: 250px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 30px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            font-weight: 900;
            color: #059669;
        }
        .footer-signatures {
            clear: both;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            margin-top: 80px;
            text-align: center;
        }
        .sig-line {
            border-top: 1px solid #6b7280;
            padding-top: 8px;
            font-size: 11px;
            color: #4b5563;
        }
        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #059669;
            color: #fff;
            border: none;
            padding: 10px 18px;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
        }
        @media print {
            .print-btn { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>
    <button class="print-btn" onclick="window.print()">Imprimir Orden</button>

    <div class="header">
        <div>
            <div class="logo-title">FarmaBien</div>
            <div style="font-size: 11px; color: #6b7280;">Farmacia & Droguería FarmaBien</div>
            <div style="font-size: 11px; color: #6b7280;">RUC: J-40892154-0 | Managua, Nicaragua</div>
            <div style="font-size: 11px; color: #6b7280;">Tel: +(505) 2222-0000 | info@farmabien.ni</div>
        </div>
        <div>
            <div class="doc-title">ORDEN DE COMPRA</div>
            <div style="font-size: 14px; font-weight: bold; color: #059669; text-align: right;">{{ $orden->numero_orden }}</div>
            <div style="font-size: 11px; color: #6b7280; text-align: right; margin-top: 4px;">Fecha: {{ $orden->fecha_emision->format('d/m/Y') }}</div>
        </div>
    </div>

    <div class="info-grid">
        <div class="card">
            <h4>DATOS DEL PROVEEDOR</h4>
            <div><strong>Empresa:</strong> {{ $orden->proveedor->nombre_empresa }}</div>
            <div><strong>Contacto:</strong> {{ $orden->proveedor->nombre_contacto ?? 'Atención a Ventas' }}</div>
            <div><strong>Teléfono:</strong> {{ $orden->proveedor->telefono ?? 'N/A' }}</div>
            <div><strong>Email:</strong> {{ $orden->proveedor->email ?? 'N/A' }}</div>
        </div>

        <div class="card">
            <h4>CONDICIONES COMERCIALES</h4>
            <div><strong>Condición de Pago:</strong> {{ ucfirst($orden->condicion_pago) }} {{ $orden->condicion_pago === 'credito' ? "({$orden->dias_credito} días)" : '' }}</div>
            <div><strong>Fecha Estimada Entrega:</strong> {{ $orden->fecha_esperada_entrega ? $orden->fecha_esperada_entrega->format('d/m/Y') : 'Inmediata' }}</div>
            <div><strong>Comprador Responsable:</strong> {{ $orden->usuario->name ?? 'FarmaBien' }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 40px;">#</th>
                <th>DESCRIPCIÓN DEL MEDICAMENTO</th>
                <th>LABORATORIO</th>
                <th class="text-center" style="width: 100px;">CANTIDAD</th>
                <th class="text-right" style="width: 120px;">PRECIO UNIT.</th>
                <th class="text-right" style="width: 120px;">SUBTOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orden->detalles as $idx => $det)
            <tr>
                <td style="color: #6b7280;">{{ $idx + 1 }}</td>
                <td><strong>{{ $det->producto->nombre }}</strong></td>
                <td>{{ $det->producto->laboratorio->nombre ?? 'N/A' }}</td>
                <td class="text-center"><strong>{{ $det->cantidad_solicitada }}</strong></td>
                <td class="text-right">{{ formato_moneda($det->precio_unitario_estimado) }}</td>
                <td class="text-right"><strong>{{ formato_moneda($det->subtotal) }}</strong></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total-box">
        <div class="total-row">
            <span>TOTAL ESTIMADO:</span>
            <span>{{ formato_moneda($orden->total) }}</span>
        </div>
    </div>

    @if($orden->observaciones)
    <div style="clear: both; margin-top: 15px; font-size: 11px; background: #fffbeb; border: 1px solid #fef3c7; padding: 10px; border-radius: 6px;">
        <strong>Observaciones:</strong> {{ $orden->observaciones }}
    </div>
    @endif

    <div class="footer-signatures">
        <div>
            <div class="sig-line">Solicitado por / FarmaBien</div>
        </div>
        <div>
            <div class="sig-line">Recibido y Aceptado / Proveedor</div>
        </div>
    </div>
</body>
</html>
