<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Venta #{{ str_pad((string)$venta->id, 6, '0', STR_PAD_LEFT) }}</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}

        body{
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color:#000;
            background:#fff;
        }

        .container{max-width: 900px; margin: 0 auto; padding: 20px;}

        /* Header */
        .header{
            border-bottom: 3px solid #16a34a;
            padding-bottom: 15px;
            margin-bottom: 18px;
            display:flex;
            justify-content: space-between;
            align-items:flex-start;
            gap: 16px;
        }

        .company-info{flex:1;}
        .company-name{font-size: 24px; font-weight: 800; color:#14532d; margin-bottom: 4px;}
        .company-details{font-size: 11px; color:#666; line-height: 1.6;}

        .document-type{
            min-width: 210px;
            text-align:right;
            padding: 10px 14px;
            background:#16a34a;
            color:#fff;
            border-radius: 6px;
        }
        .document-type h2{font-size: 16px; margin-bottom: 4px;}
        .document-number{font-size: 20px; font-weight: 900; letter-spacing: .5px;}

        /* Info boxes */
        .info-section{display:flex; gap: 14px; margin-bottom: 16px;}
        .info-box{flex:1; border:1px solid #e5e7eb; border-radius: 6px; padding: 12px; background:#f9fafb;}
        .info-box h3{font-size: 11px; color:#6b7280; text-transform:uppercase; margin-bottom: 8px; font-weight: 700;}
        .info-box p{margin-bottom: 4px; font-size: 12px;}
        .info-box strong{color:#000;}

        .status-badge{
            display:inline-block;
            padding: 4px 12px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
        }
        .status-completada{background:#d1fae5;color:#065f46;}
        .status-anulada{background:#fee2e2;color:#991b1b;}
        .status-borrador{background:#e5e7eb;color:#374151;}

        /* Table */
        table{width:100%; border-collapse: collapse; table-layout: fixed;}
        thead{background:#14532d; color:#fff;}
        th{
            padding: 10px 8px;
            text-align:left;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            word-wrap: break-word;
            overflow-wrap: anywhere;
        }
        th.right{text-align:right;}
        th.center{text-align:center;}

        tbody tr{border-bottom:1px solid #e5e7eb;}
        tbody tr:nth-child(even){background:#f9fafb;}
        td{
            padding: 10px 8px;
            font-size: 12px;
            vertical-align: top;
            word-wrap: break-word;
            overflow-wrap: anywhere;
        }
        td.right{text-align:right;}
        td.center{text-align:center;}

        .product-name{font-weight: 800;}
        .product-meta{font-size: 10px; color:#6b7280; margin-top: 2px;}
        .muted{font-size: 10px; color:#6b7280;}

        /* Totals */
        .totals{margin-top: 18px; float:right; width: 320px;}
        .totals table{margin:0;}
        .totals td{border:none; padding: 8px 12px;}
        .totals .label{text-align:right; font-weight: 800; color:#6b7280;}
        .totals .amount{text-align:right; font-weight: 900;}

        .total-final{background:#16a34a; color:#fff; font-size: 16px;}
        .total-final td{padding: 12px; font-weight: 900;}

        .clearfix{clear: both;}

        /* Footer */
        .footer{
            clear: both;
            margin-top: 38px;
            padding-top: 18px;
            border-top: 2px solid #e5e7eb;
            font-size: 11px;
            color:#6b7280;
            text-align:center;
        }

        .watermark{
            position: fixed;
            top: 42%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-24deg);
            font-size: 88px;
            font-weight: 900;
            color: rgba(220, 38, 38, .12);
            letter-spacing: 6px;
            z-index: 0;
            pointer-events:none;
            user-select:none;
        }

        /* Print */
        @media print{
            .no-print{display:none!important;}
            @page{margin: 1cm;}
        }

        .print-button{
            position: fixed;
            top: 18px;
            right: 18px;
            padding: 12px 20px;
            background:#16a34a;
            color:#fff;
            border:none;
            border-radius: 8px;
            cursor:pointer;
            font-size: 14px;
            font-weight: 800;
            box-shadow: 0 6px 14px rgba(0,0,0,.12);
            z-index: 50;
        }
    </style>
</head>
<body>
@php
    $moneda = config('app.currency_symbol') ?: 'C$';

    $venta->loadMissing(['cliente','usuario','detalles.producto','detalles.presentacion','recetas','anuladoPor']);

    $descuentoProductos = $venta->detalles->sum(fn($d) => (float)($d->descuento_monto ?? 0));
    $descuentoTotal = (float)($venta->descuento_monto_total ?? 0);
    $descuentoGlobalMonto = max(0, $descuentoTotal - $descuentoProductos);

    $estado = (string)($venta->estado ?? '');
    $badge = in_array($estado, ['completada','anulada','borrador'], true) ? $estado : 'borrador';
@endphp

@if($estado === 'anulada')
    <div class="watermark">ANULADA</div>
@endif

<button class="print-button no-print" onclick="window.print()">🖨️ Imprimir</button>

<div class="container" style="position: relative; z-index: 5;">
    <div class="header">
        <div class="company-info">
            <div class="company-name">{{ env('EMPRESA_NOMBRE', config('app.name', 'FarmaBien')) }}</div>
            <div class="company-details">
                <strong>RUC:</strong> {{ env('EMPRESA_RUC', '—') }}<br>
                <strong>Dirección:</strong> {{ env('EMPRESA_DIRECCION', '—') }}<br>
                <strong>Teléfono:</strong> {{ env('EMPRESA_TELEFONO', '—') }}<br>
                <strong>Email:</strong> {{ env('EMPRESA_EMAIL', '—') }}
            </div>
        </div>

        <div class="document-type">
            <h2>{{ strtoupper($venta->tipo_comprobante ?? 'VENTA') }}</h2>
            <div class="document-number">#{{ str_pad((string)$venta->id, 6, '0', STR_PAD_LEFT) }}</div>
        </div>
    </div>

    <div class="info-section">
        <div class="info-box">
            <h3>Cliente</h3>
            <p><strong>{{ $venta->cliente?->nombre ?? 'PÚBLICO GENERAL' }}</strong></p>
            @if(!empty($venta->cliente?->documento))
                <p>Doc: {{ $venta->cliente->documento }}</p>
            @endif
            @if(!empty($venta->cliente?->telefono))
                <p>Tel: {{ $venta->cliente->telefono }}</p>
            @endif
            @if(!empty($venta->cliente?->email))
                <p>Email: {{ $venta->cliente->email }}</p>
            @endif
        </div>

        <div class="info-box">
            <h3>Información de Venta</h3>
            <p><strong>Fecha:</strong> {{ $venta->fecha?->format('d/m/Y') ?? '—' }}</p>
            <p><strong>Hora:</strong> {{ $venta->fecha?->format('H:i:s') ?? optional($venta->created_at)->format('H:i:s') ?? '—' }}</p>
            <p><strong>Cajero:</strong> {{ $venta->usuario?->name ?? '—' }}</p>
            <p><strong>Método de pago:</strong> {{ strtoupper($venta->metodo_pago ?? '—') }}</p>
            <p><strong>Estado:</strong>
                <span class="status-badge status-{{ $badge }}">{{ $estado ?: '—' }}</span>
            </p>

            @if(!empty($venta->referencia_pago))
                <p><strong>Referencia:</strong> {{ $venta->referencia_pago }}</p>
            @endif
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 4%" class="center">#</th>
                <th style="width: 30%">Producto</th>
                <th style="width: 14%">Presentación</th>
                <th style="width: 7%" class="center">Unid/Pres</th>
                <th style="width: 8%" class="right">Cant. Pres</th>
                <th style="width: 8%" class="right">Total Unid</th>
                <th style="width: 10%" class="right">P. Unit</th>
                <th style="width: 9%" class="right">Desc.</th>
                <th style="width: 10%" class="right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($venta->detalles as $i => $detalle)
                @php
                    $unidPres = (int)($detalle->unidades_por_presentacion ?? 1);
                    $cantPres = (int)($detalle->cantidad_presentaciones ?? 1);
                    $totalUnid = (int)($detalle->cantidad_unidades_base ?? ($cantPres * max($unidPres,1)));

                    $tipoPres = $detalle->tipo_presentacion
                        ?: ($detalle->presentacion?->nombre ?? 'Unidad');

                    $precioUnit = (float)($detalle->precio_unitario ?? 0);
                    $precioPres = round($precioUnit * max($unidPres,1), 2);

                    $descMonto = (float)($detalle->descuento_monto ?? 0);
                    $descPct = (float)($detalle->descuento_porcentaje ?? 0);

                    $subtotal = (float)($detalle->subtotal ?? 0);
                @endphp
                <tr>
                    <td class="center">{{ $i + 1 }}</td>
                    <td>
                        <div class="product-name">{{ $detalle->producto?->nombre ?? 'Producto' }}</div>
                        @if(!empty($detalle->producto?->codigo_barra))
                            <div class="product-meta">CB: {{ $detalle->producto->codigo_barra }}</div>
                        @endif
                        <div class="product-meta">
                            Precio pres: {{ number_format($precioPres, 2) }} {{ $moneda }}
                            <span class="muted"> ({{ number_format($precioUnit, 2) }} / u)</span>
                        </div>
                    </td>
                    <td>
                        <div class="product-name">{{ $tipoPres }}</div>
                        @if(!empty($detalle->presentacion?->descripcion))
                            <div class="product-meta">{{ $detalle->presentacion->descripcion }}</div>
                        @endif
                    </td>
                    <td class="center">{{ $unidPres }}</td>
                    <td class="right">{{ $cantPres }}</td>
                    <td class="right">{{ $totalUnid }}</td>
                    <td class="right">{{ number_format($precioUnit, 2) }}</td>
                    <td class="right">
                        @if($descMonto > 0)
                            {{ number_format($descPct, 2) }}%<br>
                            <span class="muted">-{{ number_format($descMonto, 2) }}</span>
                        @else
                            —
                        @endif
                    </td>
                    <td class="right" style="font-weight: 900;">{{ number_format($subtotal, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="padding: 14px; text-align: center;">Sin productos.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="totals">
        <table>
            <tr>
                <td class="label">Subtotal bruto</td>
                <td class="amount">{{ number_format((float)($venta->subtotal_bruto ?? 0), 2) }} {{ $moneda }}</td>
            </tr>
            <tr>
                <td class="label">Desc. productos</td>
                <td class="amount">-{{ number_format((float)$descuentoProductos, 2) }} {{ $moneda }}</td>
            </tr>
            <tr>
                <td class="label">Desc. global</td>
                <td class="amount">
                    {{ number_format((float)($venta->descuento_porcentaje ?? 0), 2) }}%
                    (-{{ number_format((float)$descuentoGlobalMonto, 2) }} {{ $moneda }})
                </td>
            </tr>
            <tr class="total-final">
                <td class="label">TOTAL</td>
                <td class="amount">{{ number_format((float)($venta->total ?? 0), 2) }} {{ $moneda }}</td>
            </tr>
        </table>
    </div>

    <div class="clearfix"></div>

    <div style="margin-top: 16px; border: 1px solid #e5e7eb; border-radius: 6px; padding: 12px; background: #fff;">
        <div style="font-weight: 800; margin-bottom: 6px;">Pago</div>
        @if(($venta->metodo_pago ?? '') === 'efectivo')
            <div><strong>Recibido:</strong> {{ is_null($venta->monto_recibido) ? '—' : number_format((float)$venta->monto_recibido, 2).' '.$moneda }}</div>
            <div><strong>Cambio:</strong> {{ is_null($venta->cambio) ? '—' : number_format((float)$venta->cambio, 2).' '.$moneda }}</div>
        @else
            <div><strong>Método:</strong> {{ strtoupper($venta->metodo_pago ?? '—') }}</div>
            @if(!empty($venta->referencia_pago))
                <div><strong>Referencia:</strong> {{ $venta->referencia_pago }}</div>
            @endif
        @endif

        @if(!empty($venta->observaciones))
            <div style="margin-top: 10px;">
                <strong>Observaciones:</strong>
                <div class="muted" style="white-space: pre-wrap; margin-top: 4px;">{{ $venta->observaciones }}</div>
            </div>
        @endif

        @if($venta->recetas && $venta->recetas->isNotEmpty())
            <div style="margin-top: 10px;">
                <strong>Recetas médicas:</strong>
                <div class="muted" style="margin-top: 4px;">
                    @foreach($venta->recetas as $receta)
                        <div>• {{ $receta->numero_receta }} — Dr(a): {{ $receta->medico }}</div>
                    @endforeach
                </div>
            </div>
        @endif

        @if($estado === 'anulada')
            <div style="margin-top: 10px; padding: 10px; border: 2px solid #991b1b; background: #fee2e2;">
                <div style="font-weight: 900; color:#991b1b;">VENTA ANULADA</div>
                <div class="muted" style="color:#991b1b;">{{ $venta->fecha_anulacion?->format('d/m/Y H:i') ?? '' }}</div>
                <div class="muted" style="color:#991b1b; margin-top: 4px;">Motivo: {{ $venta->motivo_anulacion ?? '—' }}</div>
                @if($venta->anuladoPor)
                    <div class="muted" style="color:#991b1b; margin-top: 4px;">Anulada por: {{ $venta->anuladoPor->name }}</div>
                @endif
            </div>
        @endif
    </div>

    <div class="footer">
        <p><strong>{{ config('app.name', 'FarmaBien') }}</strong> - Sistema de Gestión Farmacéutica</p>
        <p>Documento generado el {{ now()->format('d/m/Y H:i:s') }} | Venta #{{ str_pad((string)$venta->id, 6, '0', STR_PAD_LEFT) }}</p>
    </div>
</div>

<script>
    // window.onload = () => window.print();
</script>
</body>
</html>
