<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket Venta #{{ $venta->id }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.4;
            padding: 10mm;
            max-width: 80mm;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
            border-bottom: 2px dashed #000;
            padding-bottom: 10px;
        }

        .header h1 {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .header p {
            font-size: 11px;
            margin: 2px 0;
        }

        .info {
            margin: 10px 0;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin: 3px 0;
        }

        .info-label {
            font-weight: bold;
        }

        .productos {
            margin: 10px 0;
        }

        .productos table {
            width: 100%;
            border-collapse: collapse;
        }

        .productos th {
            text-align: left;
            border-bottom: 1px solid #000;
            padding: 5px 0;
            font-weight: bold;
        }

        .productos td {
            padding: 5px 0;
            vertical-align: top;
        }

        .producto-nombre {
            font-weight: bold;
        }

        .producto-detalle {
            font-size: 10px;
            color: #333;
        }

        .text-right {
            text-align: right;
        }

        .totales {
            margin-top: 10px;
            border-top: 2px solid #000;
            padding-top: 10px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin: 5px 0;
            font-size: 13px;
        }

        .total-final {
            font-size: 16px;
            font-weight: bold;
            border-top: 2px solid #000;
            margin-top: 5px;
            padding-top: 5px;
        }

        .footer {
            margin-top: 15px;
            text-align: center;
            border-top: 2px dashed #000;
            padding-top: 10px;
            font-size: 11px;
        }

        .anulado {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 48px;
            font-weight: bold;
            color: rgba(255, 0, 0, 0.3);
            border: 5px solid rgba(255, 0, 0, 0.3);
            padding: 20px 40px;
            pointer-events: none;
            z-index: 1000;
        }

        @media print {
            body {
                padding: 0;
            }
            
            @page {
                size: 80mm auto;
                margin: 0;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    @if($venta->estado === 'anulada')
    <div class="anulado">ANULADO</div>
    @endif

    <!-- Encabezado -->
    <div class="header">
        <h1>💊 FARMABIEN</h1>
        <p>RUC: 20123456789</p>
        <p>Av. Principal 123, Lima</p>
        <p>Tel: (01) 234-5678</p>
        <p style="margin-top: 5px; font-weight: bold;">TICKET DE VENTA</p>
        <p>#{{ str_pad($venta->id, 8, '0', STR_PAD_LEFT) }}</p>
    </div>

    <!-- Información de la Venta -->
    <div class="info">
        <div class="info-row">
            <span class="info-label">Fecha:</span>
            <span>{{ $venta->fecha->format('d/m/Y H:i') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Cliente:</span>
            <span>{{ $venta->cliente ? $venta->cliente->nombre : 'PÚBLICO GENERAL' }}</span>
        </div>
        @if($venta->cliente && $venta->cliente->documento)
        <div class="info-row">
            <span class="info-label">Doc:</span>
            <span>{{ $venta->cliente->documento }}</span>
        </div>
        @endif
        <div class="info-row">
            <span class="info-label">Vendedor:</span>
            <span>{{ $venta->usuario->name }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Pago:</span>
            <span>{{ strtoupper($venta->metodo_pago) }}</span>
        </div>
    </div>

    <!-- Productos -->
    <div class="productos">
        <table>
            <thead>
                <tr>
                    <th>DESCRIPCIÓN</th>
                    <th class="text-right">CANT</th>
                    <th class="text-right">P.UNIT</th>
                    <th class="text-right">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @foreach($venta->detalles as $detalle)
                <tr>
                    <td colspan="4">
                        <div class="producto-nombre">{{ $detalle->producto->nombre }}</div>
                        <div class="producto-detalle">Lote: {{ $detalle->lote->numero_lote }}</div>
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td class="text-right">{{ $detalle->cantidad }}</td>
                    <td class="text-right">{{ number_format($detalle->precio_unitario, 2) }}</td>
                    <td class="text-right">{{ number_format($detalle->subtotal, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Totales -->
    <div class="totales">
        <div class="total-row">
            <span>Items:</span>
            <span>{{ $venta->detalles->sum('cantidad') }} unidades</span>
        </div>
        <div class="total-row total-final">
            <span>TOTAL:</span>
            <span>S/ {{ number_format($venta->total, 2) }}</span>
        </div>
    </div>

    <!-- Recetas -->
    @if($venta->recetas->isNotEmpty())
    <div class="info" style="margin-top: 10px;">
        <div style="font-weight: bold; margin-bottom: 5px;">RECETAS MÉDICAS:</div>
        @foreach($venta->recetas as $receta)
        <div style="margin: 3px 0;">
            <div>Receta: {{ $receta->numero_receta }}</div>
            <div class="producto-detalle">Dr(a): {{ $receta->medico }}</div>
        </div>
        @endforeach
    </div>
    @endif

    <!-- Estado -->
    @if($venta->estado === 'anulada')
    <div style="margin: 10px 0; padding: 10px; border: 2px solid #000; text-align: center;">
        <div style="font-weight: bold; font-size: 14px;">⚠️ VENTA ANULADA ⚠️</div>
        <div style="margin-top: 5px; font-size: 10px;">{{ $venta->fecha_anulacion->format('d/m/Y H:i') }}</div>
        <div style="margin-top: 5px; font-size: 10px;">Motivo: {{ $venta->motivo_anulacion }}</div>
    </div>
    @endif

    <!-- Pie de página -->
    <div class="footer">
        <p style="margin: 5px 0;">¡Gracias por su compra!</p>
        <p style="margin: 5px 0;">Conserve este ticket</p>
        <p style="margin: 5px 0;">No se aceptan devoluciones</p>
        <p style="margin-top: 10px; font-size: 10px;">
            Sistema FarmaBien v1.0<br>
            Impreso: {{ now()->format('d/m/Y H:i:s') }}
        </p>
    </div>

    <!-- Botón de imprimir (solo en pantalla) -->
    <div class="no-print" style="text-align: center; margin-top: 20px;">
        <button onclick="window.print()" style="padding: 10px 30px; font-size: 14px; cursor: pointer; background: #3B82F6; color: white; border: none; border-radius: 5px;">
            🖨️ Imprimir Ticket
        </button>
        <button onclick="window.close()" style="padding: 10px 30px; font-size: 14px; cursor: pointer; background: #6B7280; color: white; border: none; border-radius: 5px; margin-left: 10px;">
            Cerrar
        </button>
    </div>

    <script>
        // Auto-imprimir al cargar (opcional)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>