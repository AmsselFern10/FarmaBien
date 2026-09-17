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
</head>
<body>
@php
    $moneda = env('MONEDA_SIMBOLO', 'C$');
    $clienteNombre = \Illuminate\Support\Str::limit($venta->cliente?->nombre ?? 'PÚBLICO GENERAL', 26);
@endphp

    <button class="print-button no-print" onclick="window.print()">Imprimir</button>

    <div class="center">
        <div class="title">{{ env('EMPRESA_NOMBRE', 'FarmaBien') }}</div>
        <div class="small">RUC: {{ env('EMPRESA_RUC', '-') }}</div>
        <div class="small">{{ env('EMPRESA_DIRECCION', '-') }}</div>
        <div class="small">Tel: {{ env('EMPRESA_TELEFONO', '-') }}</div>
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
                    $usaPres = !empty($detalle->presentacion_id) || !empty($detalle->tipo_presentacion);
                    $cantPres = (int)($detalle->cantidad_presentaciones ?? 1);
                    $cant = $usaPres ? $cantPres : (int)($detalle->cantidad_unidades_base ?? 0);

                    $precio = $usaPres
                        ? (float)($detalle->precio_unitario * max(1,(int)$detalle->unidades_por_presentacion))
                        : (float)$detalle->precio_unitario;

                    $totalLinea = (float)($detalle->subtotal ?? 0);
                    $descMonto = (float)($detalle->descuento_monto ?? 0);

                    $nombrePres = $detalle->tipo_presentacion
                        ?? $detalle->presentacion?->nombre
                        ?? 'Unidad';
                @endphp
                <tr>
                    <td>
                        <div class="item-name">{{ $detalle->producto?->nombre ?? 'Producto' }}</div>
                        <div class="item-pres">{{ $usaPres ? $nombrePres : 'Unidad' }}</div>
                    </td>
                    <td class="qty">{{ $cant }}</td>
                    <td class="num">
                        <div>{{ number_format($precio, 2) }}</div>
                        @if($descMonto > 0)
                            <div class="xs">Desc: -{{ number_format($descMonto, 2) }}</div>
                        @endif
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
            <td class="v">{{ $moneda }} {{ number_format((float)($venta->subtotal_bruto ?? 0), 2) }}</td>
        </tr>
        <tr>
            <td class="k">DESCUENTO:</td>
            <td class="v">{{ $moneda }} {{ number_format((float)($venta->descuento_monto_total ?? 0), 2) }}</td>
        </tr>
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
        <div class="xs">{{ config('app.name') }} — {{ now()->format('d/m/Y H:i:s') }}</div>
        <div class="rule"></div>
    </div>

    <div style="height: 18px;"></div>
</body>
</html>
