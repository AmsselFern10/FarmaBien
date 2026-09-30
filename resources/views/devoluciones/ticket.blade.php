<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobante Devolución #{{ $devolucion->numero_devolucion }}</title>
    <style>
        :root {
            --paper-width: 80mm;
            --pad: 8px;
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: "Courier New", monospace;
            font-size: 11px;
            line-height: 1.25;
            color: #000;
            background: #fff;
            width: var(--paper-width);
            margin: 0 auto;
            padding: var(--pad);
        }
        .no-print { display: block; }
        @media print {
            body { margin:0; padding:6px; width: var(--paper-width); }
            .no-print { display: none !important; }
            @page { size: var(--paper-width) auto; margin:0; }
        }
        .center { text-align: center; }
        .right { text-align: right; }
        .bold { font-weight: 700; }
        .small { font-size: 10px; }
        .xs { font-size: 9px; }
        .title {
            font-size: 15px;
            font-weight: 800;
            letter-spacing: .3px;
            text-transform: uppercase;
        }
        .doc {
            font-size: 13px;
            font-weight: 800;
            border: 2px solid #000;
            padding: 4px 0;
            margin: 6px 0 4px;
        }
        .rule { border-top: 1px dashed #000; margin: 7px 0; }
        .rule-strong { border-top: 2px solid #000; margin: 7px 0; }
        .kv {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            margin: 2px 0;
        }
        .kv .k { font-weight: 700; }
        .kv .v { text-align: right; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        table { width: 100%; border-collapse: collapse; }
        .items table-layout: fixed;
        .items thead th {
            font-size: 10px;
            padding: 2px 0 3px;
            border-bottom: 1px solid #000;
        }
        .items tbody td {
            padding: 3px 0;
            vertical-align: top;
        }
        .num { text-align: right; white-space: nowrap; font-variant-numeric: tabular-nums; }
        .qty { text-align: center; white-space: nowrap; font-variant-numeric: tabular-nums; }
        .total-final {
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            padding: 4px 0;
            margin: 5px 0;
            font-size: 14px;
            font-weight: 900;
        }
        .total-final .row {
            display: flex;
            justify-content: space-between;
            gap: 8px;
        }
        .print-button {
            position: fixed;
            top: 10px;
            right: 10px;
            padding: 10px 14px;
            background: #000;
            color: #fff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 700;
            z-index: 1000;
        }
        .print-button:hover { background: #222; }
        .footer { margin-top: 10px; }
    </style>
    <script>
        window.addEventListener('DOMContentLoaded', function() {
            const params = new URLSearchParams(window.location.search);
            if (params.get('autoprint') === '1' || params.has('autoprint')) {
                setTimeout(function() { window.print(); }, 250);
            }
        });
    </script>
</head>
<body>
@php
    $moneda = 'C$';
    $clienteNombre = \Illuminate\Support\Str::limit($devolucion->venta?->cliente?->nombre ?? 'PÚBLICO GENERAL', 26);
@endphp

    <button class="print-button no-print" onclick="window.print()">Imprimir</button>

    <div class="center">
        @if(configuracion('empresa_logo'))
            <div style="margin-bottom: 5px;">
                <img src="{{ asset('storage/' . configuracion('empresa_logo')) }}" alt="Logo" style="max-height: 45px; max-width: 140px; object-fit: contain;">
            </div>
        @endif
        <div class="title">{{ configuracion('empresa_nombre', 'FarmaBien') }}</div>
        <div class="small">{{ configuracion('empresa_razon_social', 'Farmacia FarmaBien') }}</div>
        <div class="small">RUC: {{ configuracion('empresa_ruc', 'J-40892154-0') }}</div>
        <div class="small">{{ configuracion('empresa_direccion', 'Managua, Nicaragua') }}</div>
        <div class="small">Tel: {{ configuracion('empresa_telefono', '+(505) 2222-0000') }}</div>
    </div>

    <div class="rule-strong"></div>

    <div class="doc center">
        NOTA DE DEVOLUCIÓN
        <div>{{ $devolucion->numero_devolucion }}</div>
    </div>

    <div class="rule"></div>

    <div class="kv"><span class="k">FECHA:</span><span class="v">{{ $devolucion->fecha->format('d/m/Y H:i') }}</span></div>
    <div class="kv"><span class="k">RESPONSABLE:</span><span class="v">{{ $devolucion->usuario?->name ?? 'Sistema' }}</span></div>
    <div class="kv"><span class="k">VENTA ORIG.:</span><span class="v">{{ $devolucion->venta?->numero_comprobante ?? 'Venta #'.$devolucion->venta_id }}</span></div>
    <div class="kv"><span class="k">CLIENTE:</span><span class="v">{{ $clienteNombre }}</span></div>
    <div class="kv"><span class="k">MOTIVO:</span><span class="v">{{ ucfirst(str_replace('_', ' ', $devolucion->motivo)) }}</span></div>
    <div class="kv"><span class="k">REEMBOLSO:</span><span class="v">{{ strtoupper($devolucion->metodo_reembolso) }}</span></div>
    @if($devolucion->banco)
    <div class="kv"><span class="k">BANCO:</span><span class="v">{{ $devolucion->banco }}</span></div>
    @endif
    @if($devolucion->numero_transaccion)
    <div class="kv"><span class="k">TRANSACCIÓN:</span><span class="v">{{ $devolucion->numero_transaccion }}</span></div>
    @endif

    <div class="rule-strong"></div>

    <table class="items">
        <thead>
            <tr>
                <th style="width: 50%;">PRODUCTO</th>
                <th class="qty" style="width: 15%;">CANT</th>
                <th class="num" style="width: 17%;">PRECIO</th>
                <th class="num" style="width: 18%;">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($devolucion->detalles as $detalle)
                @php
                    $cant = (int)$detalle->cantidad;
                    $precio = (float)$detalle->precio_unitario;
                    $subtotal = (float)$detalle->subtotal;
                @endphp
                <tr>
                    <td>
                        <div class="bold">{{ $detalle->producto?->nombre ?? 'Medicamento' }}</div>
                        <div class="xs">Lote: {{ $detalle->lote?->numero_lote ?? 'N/A' }}</div>
                        <div class="xs">Destino: {{ $detalle->reingresa_a_stock ? 'Stock' : 'Baja/Merma' }}</div>
                    </td>
                    <td class="qty">{{ $cant }}</td>
                    <td class="num">{{ number_format($precio, 2) }}</td>
                    <td class="num">{{ number_format($subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="rule-strong"></div>

    <div class="total-final">
        <div class="row">
            <span>TOTAL REEMBOLSADO:</span>
            <span>{{ $moneda }} {{ number_format((float)$devolucion->monto_total, 2) }}</span>
        </div>
    </div>

    @if(!empty($devolucion->observaciones))
        <div class="rule"></div>
        <div class="bold">OBSERVACIONES:</div>
        <div class="small" style="margin-top:2px;">{{ $devolucion->observaciones }}</div>
    @endif

    <div class="footer center">
        <div class="rule"></div>
        <div class="xs">{{ configuracion('empresa_nombre', 'FarmaBien') }} — Control de Devoluciones</div>
        <div class="xs">{{ now()->format('d/m/Y H:i:s') }}</div>
        <div class="rule"></div>
    </div>

    <div style="height: 18px;"></div>
</body>
</html>
