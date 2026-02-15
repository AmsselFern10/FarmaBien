<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Venta #{{ str_pad((string)$venta->id, 6, '0', STR_PAD_LEFT) }}</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}

        body{
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10.5px;
            line-height: 1.35;
            color:#000;
        }

        .container{padding: 20px;}

        /* Header */
        .header{border-bottom: 3px solid #16a34a; padding-bottom: 14px; margin-bottom: 16px;}
        .header-content{display: table; width: 100%;}
        .company-info{display: table-cell; width: 60%; vertical-align: top;}
        .company-name{font-size: 20px; font-weight: 900; color:#14532d; margin-bottom: 4px;}
        .company-details{font-size: 9.5px; color:#666; line-height: 1.55;}

        .document-type{
            display: table-cell;
            width: 40%;
            vertical-align: top;
            text-align: right;
            padding: 10px;
            background:#16a34a;
            color:#fff;
            border-radius: 6px;
        }
        .document-type h2{font-size: 14px; margin-bottom: 4px;}
        .document-number{font-size: 16px; font-weight: 900;}

        /* Info boxes */
        .info-section{margin-bottom: 12px;}
        .info-box{
            display:inline-block;
            width: 45%;
            vertical-align: top;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 10px;
            background:#f9fafb;
            margin-right: 2%;
        }
        .info-box:last-child{margin-right:0;}
        .info-box h3{font-size: 9.5px; color:#6b7280; text-transform:uppercase; margin-bottom: 6px; font-weight: 800;}
        .info-box p{margin-bottom: 3px; font-size: 10.5px;}

        .status-badge{
            display:inline-block;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 9px;
            font-weight: 900;
            text-transform: uppercase;
        }
        .status-completada{background:#d1fae5;color:#065f46;}
        .status-anulada{background:#fee2e2;color:#991b1b;}
        .status-borrador{background:#e5e7eb;color:#374151;}

        /* Table */
        table{width:100%; border-collapse: collapse; table-layout: fixed; margin-bottom: 12px;}
        thead{background:#14532d; color:#fff;}
        th{
            padding: 7px 6px;
            text-align:left;
            font-size: 9.5px;
            font-weight: 900;
            text-transform: uppercase;
            word-wrap: break-word;
            overflow-wrap: anywhere;
        }
        th.right{text-align:right;}
        th.center{text-align:center;}

        tbody tr{border-bottom: 1px solid #e5e7eb;}
        tbody tr:nth-child(even){background:#f9fafb;}
        td{
            padding: 7px 6px;
            font-size: 10px;
            vertical-align: top;
            word-wrap: break-word;
            overflow-wrap: anywhere;
        }
        td.right{text-align:right;}
        td.center{text-align:center;}

        .product-name{font-weight: 900; font-size: 10.5px;}
        .product-meta{font-size: 9px; color:#6b7280; margin-top: 2px;}

        /* Totals */
        .totals-section{margin-top: 10px;}
        .totals-table{float:right; width: 270px;}
        .totals-table td{border:none; padding: 6px 10px;}
        .totals-table .label{text-align:right; font-weight: 900; color:#6b7280;}
        .totals-table .amount{text-align:right; font-weight: 900;}
        .total-final{background:#16a34a; color:#fff; font-size: 12px;}
        .total-final td{padding: 9px 10px;}

        .clearfix{clear: both;}

        /* Footer */
        .footer{
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 12px 20px;
            border-top: 2px solid #e5e7eb;
            font-size: 8.5px;
            color:#6b7280;
            text-align:center;
            background: #fff;
        }

        .anulada-box{
            margin-top: 10px;
            padding: 10px;
            border: 2px solid #991b1b;
            background: #fee2e2;
        }

        .obs-box{
            margin-top: 10px;
            padding: 10px;
            border: 1px solid #e5e7eb;
            background: #fff;
        }

        .watermark{
            position: fixed;
            top: 42%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-24deg);
            font-size: 84px;
            font-weight: 900;
            color: rgba(220, 38, 38, .12);
            letter-spacing: 6px;
            z-index: 0;
        }
    </style>
</head>
<body>
@php
    $moneda = config('app.currency_symbol') ?: 'C$';

    $empresa = $empresa ?? [
        'nombre' => env('EMPRESA_NOMBRE', config('app.name', 'FarmaBien')),
        'ruc' => env('EMPRESA_RUC', '—'),
        'direccion' => env('EMPRESA_DIRECCION', '—'),
        'telefono' => env('EMPRESA_TELEFONO', '—'),
        'email' => env('EMPRESA_EMAIL', '—'),
    ];

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

<div class="container" style="position: relative; z-index: 5;">
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
                <h2>{{ strtoupper($venta->tipo_comprobante ?? 'VENTA') }}</h2>
                <div class="document-number">#{{ str_pad((string)$venta->id, 6, '0', STR_PAD_LEFT) }}</div>
            </div>
        </div>
    </div>

    <!-- Info -->
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
            @if(!empty($venta->cliente?->direccion))
                <p>Dir: {{ $venta->cliente->direccion }}</p>
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

    <!-- Table -->
    <table>
        <thead>
            <tr>
                <th style="width: 4%" class="center">#</th>
                <th style="width: 33%">Producto</th>
                <th style="width: 14%">Presentación</th>
                <th style="width: 7%" class="center">Unid/Pres</th>
                <th style="width: 8%" class="right">Cant.</th>
                <th style="width: 8%" class="right">Unid</th>
                <th style="width: 9%" class="right">P.Unit</th>
                <th style="width: 8%" class="right">Desc</th>
                <th style="width: 9%" class="right">Subtotal</th>
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
                        <div class="product-meta">Pres: {{ number_format($precioPres, 2) }} {{ $moneda }} ({{ number_format($precioUnit, 2) }} / u)</div>
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
                    <td class="right">{{ $descMonto > 0 ? number_format($descPct, 2).'%' : '—' }}</td>
                    <td class="right" style="font-weight: 900;">{{ number_format($subtotal, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="padding: 12px; text-align: center;">Sin productos.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Totals -->
    <div class="totals-section">
        <div class="totals-table">
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
                    <td class="amount">{{ number_format((float)($venta->descuento_porcentaje ?? 0), 2) }}% (-{{ number_format((float)$descuentoGlobalMonto, 2) }} {{ $moneda }})</td>
                </tr>
                <tr class="total-final">
                    <td class="label">TOTAL</td>
                    <td class="amount">{{ number_format((float)($venta->total ?? 0), 2) }} {{ $moneda }}</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="clearfix"></div>

    <!-- Pago + observaciones -->
    <div class="obs-box">
        <div style="font-weight: 900; margin-bottom: 6px;">Pago</div>
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
            <div style="margin-top: 8px;">
                <strong>Observaciones:</strong>
                <div style="margin-top: 4px; font-size: 9.5px; color:#374151; white-space: pre-wrap;">{{ $venta->observaciones }}</div>
            </div>
        @endif

        @if($venta->recetas && $venta->recetas->isNotEmpty())
            <div style="margin-top: 8px;">
                <strong>Recetas médicas:</strong>
                <div style="margin-top: 4px; font-size: 9.5px; color:#374151;">
                    @foreach($venta->recetas as $receta)
                        <div>• {{ $receta->numero_receta }} — Dr(a): {{ $receta->medico }}</div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    @if($estado === 'anulada')
        <div class="anulada-box">
            <div style="font-weight: 900; color:#991b1b;">VENTA ANULADA</div>
            <div style="font-size: 9.5px; color:#991b1b; margin-top: 4px;">{{ $venta->fecha_anulacion?->format('d/m/Y H:i') ?? '' }}</div>
            <div style="font-size: 9.5px; color:#991b1b; margin-top: 4px;">Motivo: {{ $venta->motivo_anulacion ?? '—' }}</div>
            @if($venta->anuladoPor)
                <div style="font-size: 9.5px; color:#991b1b; margin-top: 4px;">Anulada por: {{ $venta->anuladoPor->name }}</div>
            @endif
        </div>
    @endif
</div>

<div class="footer">
    <p><strong>{{ $empresa['nombre'] }}</strong> - Sistema de Gestión Farmacéutica</p>
    <p>Documento generado el {{ now()->format('d/m/Y H:i:s') }} | Venta #{{ str_pad((string)$venta->id, 6, '0', STR_PAD_LEFT) }}</p>
</div>
</body>
</html>
