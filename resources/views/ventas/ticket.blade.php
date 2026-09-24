<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket Venta #{{ str_pad($venta->id, 6, '0', STR_PAD_LEFT) }}</title>
    <style>
        :root{
            --paper-width: 80mm; /* Cambia a 58mm si tu impresora es de 58mm */
            --pad: 8px;
        }
        *{ margin:0; padding:0; box-sizing:border-box; }
        body{
            font-family: "Courier New", monospace;
            font-size: 11px;
            line-height: 1.25;
            color:#000;
            background:#fff;
            width: var(--paper-width);
            margin: 0 auto;
            padding: var(--pad);
        }

        .no-print{ display:block; }
        @media print{
            body{ margin:0; padding:6px; width: var(--paper-width); }
            .no-print{ display:none !important; }
            @page{ size: var(--paper-width) auto; margin:0; }
        }

        .center{ text-align:center; }
        .right{ text-align:right; }
        .bold{ font-weight:700; }
        .small{ font-size:10px; }
        .xs{ font-size:9px; }
        .title{
            font-size: 15px;
            font-weight: 800;
            letter-spacing: .3px;
            text-transform: uppercase;
        }
        .doc{
            font-size: 13px;
            font-weight: 800;
            border: 2px solid #000;
            padding: 4px 0;
            margin: 6px 0 4px;
        }

        .rule{ border-top: 1px dashed #000; margin: 7px 0; }
        .rule-strong{ border-top: 2px solid #000; margin: 7px 0; }

        /* Info blocks */
        .kv{
            display:flex;
            justify-content: space-between;
            gap: 8px;
            margin: 2px 0;
        }
        .kv .k{ font-weight:700; }
        .kv .v{ text-align:right; white-space:nowrap; overflow:hidden; text-overflow: ellipsis; }

        .info-grid{
            display:flex;
            justify-content: space-between;
            gap: 10px;
        }
        .info-col{ flex:1; min-width: 0; }
        .info-col.right-col{ text-align:right; }
        .info-col.right-col .kv .k{ text-align:left; }

        .wrap{ overflow-wrap:anywhere; word-break:break-word; }

        /* Tables */
        table{ width:100%; border-collapse: collapse; }
        .items{ table-layout: fixed; }
        .items thead th{
            font-size: 10px;
            padding: 2px 0 3px;
            border-bottom: 1px solid #000;
        }
        .items tbody td{
            padding: 3px 0;
            vertical-align: top;
        }
        .items .col-item{ width: 52%; }
        .items .col-qty{ width: 12%; }
        .items .col-price{ width: 18%; }
        .items .col-subt{ width: 18%; }

        .item-name{
            font-weight: 800;
            overflow-wrap:anywhere;
            word-break:break-word;
        }
        .item-pres{
            font-size: 9px;
            color:#111;
            margin-top: 1px;
            overflow-wrap:anywhere;
            word-break:break-word;
        }

        .num{
            text-align:right;
            white-space: nowrap;
            font-variant-numeric: tabular-nums;
            -webkit-font-feature-settings: "tnum" 1;
            font-feature-settings: "tnum" 1;
        }
        .qty{
            text-align:center;
            white-space: nowrap;
            font-variant-numeric: tabular-nums;
            font-feature-settings: "tnum" 1;
        }

        /* Totals */
        .totals{ table-layout: fixed; }
        .totals td{ padding: 2px 0; font-size: 11px; }
        .totals .k{ width: 60%; font-weight: 800; }
        .totals .v{
            width: 40%;
            text-align:right;
            white-space: nowrap;
            font-variant-numeric: tabular-nums;
            font-feature-settings: "tnum" 1;
        }

        .total-final{
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            padding: 4px 0;
            margin: 5px 0;
            font-size: 14px;
            font-weight: 900;
        }
        .total-final .row{
            display:flex;
            justify-content: space-between;
            gap: 8px;
        }
        .total-final .value{ white-space:nowrap; }

        .badge-anulada{
            border: 2px solid #000;
            padding: 6px;
            margin: 6px 0;
            text-align:center;
            font-weight: 900;
        }

        .print-button{
            position: fixed;
            top: 10px;
            right: 10px;
            padding: 10px 14px;
            background:#000;
            color:#fff;
            border: none;
            border-radius: 6px;
            cursor:pointer;
            font-size: 13px;
            font-weight: 700;
            z-index: 1000;
        }
        .print-button:hover{ background:#222; }

        .footer{ margin-top: 10px; }
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
    $moneda = env('MONEDA_SIMBOLO', 'C$');
    $clienteNombre = \Illuminate\Support\Str::limit($venta->cliente?->nombre ?? 'PÚBLICO GENERAL', 26);
@endphp

    <button class="print-button no-print" onclick="window.print()">Imprimir</button>

    <div class="center">
        @if(configuracion('empresa_logo'))
            <div style="margin-bottom: 5px;">
                <img src="{{ asset('storage/' . configuracion('empresa_logo')) }}" alt="Logo" style="max-height: 45px; max-width: 140px; object-fit: contain;">
            </div>
        @endif
        <div class="title">{{ configuracion('empresa_nombre', 'FarmaBien') }}</div>
        <div class="small">{{ configuracion('empresa_razon_social', 'Farmacia & Droguería FarmaBien C.A.') }}</div>
        <div class="small">RIF/RUC: {{ configuracion('empresa_ruc', 'J-40892154-0') }}</div>
        <div class="small">{{ configuracion('empresa_direccion', 'Av. Principal Los Próceres, Caracas') }}</div>
        <div class="small">Tel: {{ configuracion('empresa_telefono', '(0212) 555-0199') }}</div>
    </div>

    <div class="rule-strong"></div>

    <div class="doc center">
        VENTA
        <div>N° {{ str_pad($venta->id, 6, '0', STR_PAD_LEFT) }}</div>
    </div>

    @if(($venta->estado ?? '') === 'anulada')
        <div class="badge-anulada">
            VENTA ANULADA
            @if(!empty($venta->fecha_anulacion))
                <div class="xs" style="margin-top:3px;">{{ $venta->fecha_anulacion?->format('d/m/Y H:i') }}</div>
            @endif
            @if(!empty($venta->motivo_anulacion))
                <div class="xs wrap" style="margin-top:3px;">Motivo: {{ $venta->motivo_anulacion }}</div>
            @endif
        </div>
    @endif

    <div class="rule"></div>

    {{-- INFO EN DOS COLUMNAS: IZQ (fecha/cajero) - DER (cliente) --}}
    <div class="info-grid">
        <div class="info-col">
            <div class="kv"><span class="k">FECHA:</span><span class="v">{{ optional($venta->fecha)->format('d/m/Y H:i') }}</span></div>
            <div class="kv"><span class="k">CAJERO:</span><span class="v">{{ $venta->usuario?->name ?? '-' }}</span></div>
        </div>
        <div class="info-col right-col">
            <div class="kv"><span class="k">CLIENTE:</span><span class="v">{{ $clienteNombre }}</span></div>
            {{-- DOC eliminado por solicitud --}}
        </div>
    </div>

    <div class="kv"><span class="k">PAGO:</span><span class="v">{{ strtoupper($venta->metodo_pago ?? '-') }}</span></div>
    @if(!empty($venta->referencia_pago))
        <div class="kv"><span class="k">REF:</span><span class="v">{{ \Illuminate\Support\Str::limit($venta->referencia_pago, 20) }}</span></div>
    @endif

    <div class="rule-strong"></div>

    <table class="items">
        <colgroup>
            <col class="col-item">
            <col class="col-qty">
            <col class="col-price">
            <col class="col-subt">
        </colgroup>
        <thead>
            <tr>
                <th>PRODUCTO / PRESENTACIÓN</th>
                <th class="center">CANT</th>
                <th class="right">PRECIO</th>
                <th class="right">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($venta->detalles as $detalle)
                @php
                    $cant = (int)($detalle->cantidad ?? 1);
                    $precio = (float)($detalle->precio_unitario ?? 0);
                    $totalLinea = (float)($detalle->subtotal ?? ($cant * $precio));
                    $nombrePres = $detalle->presentacion?->nombre ?? 'Unidad Base';
                @endphp
                <tr>
                    <td>
                        <div class="item-name">{{ $detalle->producto?->nombre ?? 'Medicamento' }}</div>
                        <div class="item-pres">{{ $nombrePres }}</div>
                    </td>
                    <td class="qty">{{ $cant }}</td>
                    <td class="num">
                        <div>{{ number_format($precio, 2) }}</div>
                    </td>
                    <td class="num">{{ number_format($totalLinea, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="rule-strong"></div>

    <table class="totals">
        <tr>
            <td class="k">SUBTOTAL:</td>
            <td class="v">{{ $moneda }} {{ number_format((float)($venta->subtotal ?? 0), 2) }}</td>
        </tr>
        @if(($venta->descuento ?? 0) > 0)
        <tr>
            <td class="k">DESCUENTO:</td>
            <td class="v">-{{ $moneda }} {{ number_format((float)($venta->descuento ?? 0), 2) }}</td>
        </tr>
        @endif
        @if(($venta->impuesto ?? 0) > 0)
        <tr>
            <td class="k">IMPUESTO:</td>
            <td class="v">{{ $moneda }} {{ number_format((float)($venta->impuesto ?? 0), 2) }}</td>
        </tr>
        @endif
    </table>

    <div class="total-final">
        <div class="row">
            <span>TOTAL:</span>
            <span class="value">{{ $moneda }} {{ number_format((float)($venta->total ?? 0), 2) }}</span>
        </div>
    </div>

    {{-- Caja (solo efectivo) --}}
    @if(($venta->metodo_pago ?? '') === 'efectivo')
        <div class="kv"><span class="k">RECIBIDO:</span><span class="v">{{ is_null($venta->monto_recibido) ? '-' : ($moneda.' '.number_format((float)$venta->monto_recibido, 2)) }}</span></div>
        <div class="kv"><span class="k">CAMBIO:</span><span class="v">{{ is_null($venta->cambio) ? '-' : ($moneda.' '.number_format((float)$venta->cambio, 2)) }}</span></div>
    @endif

    @if(!empty($venta->observaciones))
        <div class="rule"></div>
        <div class="bold">OBSERVACIONES:</div>
        <div class="small wrap" style="margin-top:2px;">{{ $venta->observaciones }}</div>
    @endif

    <div class="footer center">
        <div class="rule"></div>
        <div class="small bold" style="margin: 4px 0;">{{ configuracion('empresa_pie_ticket', '¡Gracias por su compra!') }}</div>
        <div class="xs">{{ configuracion('empresa_slogan', 'Tu salud y bienestar en las mejores manos.') }}</div>
        <div class="xs" style="margin-top: 3px;">{{ configuracion('empresa_nombre', 'FarmaBien') }} — {{ now()->format('d/m/Y H:i:s') }}</div>
        <div class="rule"></div>
    </div>

    <div style="height: 18px;"></div>
</body>
</html>
