<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket Venta #{{ $venta->id }}</title>
    <style>
        *{ margin:0; padding:0; box-sizing:border-box; }

        :root{
            --w: 80mm;
            --fs: 12px;
            --fs-sm: 10px;
        }

        body{
            font-family: "Courier New", ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: var(--fs);
            line-height: 1.35;
            width: var(--w);
            max-width: var(--w);
            margin: 0 auto;
            padding: 6mm;
            color:#000;
        }

        .header{
            text-align:center;
            margin-bottom: 8px;
            border-bottom: 2px dashed #000;
            padding-bottom: 8px;
        }
        .header h1{
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 4px;
        }
        .header p{ font-size: 11px; margin: 1px 0; }

        .info{
            margin: 8px 0;
            border-bottom: 1px dashed #000;
            padding-bottom: 8px;
            font-size: 11px;
        }
        .info-row{
            display:flex;
            justify-content:space-between;
            gap: 8px;
            margin: 2px 0;
        }
        .info-label{ font-weight: 700; }
        .info-value{ text-align:right; overflow:hidden; text-overflow: ellipsis; white-space: nowrap; }

        .productos{ margin: 8px 0; }
        table{
            width:100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        thead th{
            border-bottom: 1px solid #000;
            padding: 4px 0 5px 0;
            font-weight: 700;
            font-size: 11px;
        }
        tbody td{
            padding: 5px 0;
            vertical-align: top;
            font-size: 11px;
        }

        .col-prod{ width: 46%; }
        .col-pres{ width: 22%; }
        .col-cant{ width: 12%; }
        .col-subt{ width: 20%; }

        .text-right{ text-align:right; }
        .text-center{ text-align:center; }

        .prod-name{
            font-weight: 700;
            word-break: break-word;
            white-space: normal;
        }
        .prod-meta{
            font-size: var(--fs-sm);
            color:#333;
            word-break: break-word;
            white-space: normal;
            margin-top: 1px;
        }
        .muted{ color:#333; font-size: var(--fs-sm); }

        .totales{
            margin-top: 8px;
            border-top: 2px solid #000;
            padding-top: 8px;
            font-size: 12px;
        }
        .total-row{
            display:flex;
            justify-content:space-between;
            margin: 4px 0;
            gap: 10px;
        }
        .total-row span:last-child{ text-align:right; }
        .total-final{
            font-size: 15px;
            font-weight: 800;
            border-top: 2px solid #000;
            margin-top: 6px;
            padding-top: 6px;
        }

        .pago{
            margin-top: 8px;
            border-top: 1px dashed #000;
            padding-top: 8px;
            font-size: 11px;
        }

        .footer{
            margin-top: 12px;
            text-align:center;
            border-top: 2px dashed #000;
            padding-top: 10px;
            font-size: 11px;
        }

        .anulado{
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-25deg);
            font-size: 44px;
            font-weight: 900;
            color: rgba(255,0,0,.22);
            border: 4px solid rgba(255,0,0,.22);
            padding: 16px 28px;
            pointer-events: none;
            z-index: 1000;
            letter-spacing: 2px;
        }

        @media print{
            body{ padding: 0; margin: 0; }
            @page{
                size: 80mm auto;
                margin: 4mm;
            }
            .no-print{ display:none; }
        }
    </style>
</head>
<body>
@php
    $moneda = config('app.currency_symbol') ?: 'C$';

    $descuentoProductos = $venta->detalles->sum(fn($d) => (float)($d->descuento_monto ?? 0));
    $descuentoTotal = (float)($venta->descuento_monto_total ?? 0);
    $descuentoGlobalMonto = max(0, $descuentoTotal - $descuentoProductos);

    $montoRecibido = $venta->monto_recibido;
    $cambio = $venta->cambio;
@endphp

@if(($venta->estado ?? '') === 'anulada')
    <div class="anulado">ANULADO</div>
@endif

<div class="header">
    <h1>💊 FARMABIEN</h1>
    {{-- Ajusta estos datos a tu configuración --}}
    <p>RUC: {{ config('app.ruc', '—') }}</p>
    <p>{{ config('app.direccion', '—') }}</p>
    <p>Tel: {{ config('app.telefono', '—') }}</p>

    <p style="margin-top: 6px; font-weight: 800;">FACTURA / TICKET DE VENTA</p>
    <p>#{{ str_pad((string)$venta->id, 8, '0', STR_PAD_LEFT) }}</p>
</div>

<div class="info">
    <div class="info-row">
        <span class="info-label">Fecha:</span>
        <span class="info-value">{{ $venta->fecha?->format('d/m/Y H:i') ?? '—' }}</span>
    </div>

    <div class="info-row">
        <span class="info-label">Cliente:</span>
        <span class="info-value">{{ $venta->cliente?->nombre ?? 'PÚBLICO GENERAL' }}</span>
    </div>

    @if($venta->cliente && $venta->cliente->documento)
        <div class="info-row">
            <span class="info-label">Doc:</span>
            <span class="info-value">{{ $venta->cliente->documento }}</span>
        </div>
    @endif

    <div class="info-row">
        <span class="info-label">Cajero:</span>
        <span class="info-value">{{ $venta->usuario?->name ?? '—' }}</span>
    </div>

    <div class="info-row">
        <span class="info-label">Pago:</span>
        <span class="info-value">{{ strtoupper($venta->metodo_pago ?? '—') }}</span>
    </div>

    @if(!empty($venta->banco))
        <div class="info-row">
            <span class="info-label">Banco:</span>
            <span class="info-value">{{ strtoupper($venta->banco) }}</span>
        </div>
    @endif

    @if(!empty($venta->referencia_pago))
        <div class="info-row">
            <span class="info-label">Ref:</span>
            <span class="info-value">{{ $venta->referencia_pago }}</span>
        </div>
    @endif
</div>

<div class="productos">
    <table>
        <thead>
            <tr>
                <th class="col-prod">PRODUCTO</th>
                <th class="col-pres">PRES.</th>
                <th class="col-cant text-center">CANT</th>
                <th class="col-subt text-right">SUBTOTAL</th>
            </tr>
        </thead>
        <tbody>
            @forelse($venta->detalles as $detalle)
                @php
                    $presentacion = $detalle->tipo_presentacion ?: 'Unidad';
                    $unidades = (int)($detalle->unidades_por_presentacion ?? 1);
                    $cantPres = (int)($detalle->cantidad_presentaciones ?? 1);
                    $precioPres = (float)($detalle->precio_unitario ?? 0) * max(1, $unidades);
                    $subtotal = (float)($detalle->subtotal ?? 0);

                    $descPct = (float)($detalle->descuento_porcentaje ?? 0);
                    $descMonto = (float)($detalle->descuento_monto ?? 0);
                @endphp
                <tr>
                    <td class="col-prod">
                        <div class="prod-name">{{ $detalle->producto?->nombre ?? 'Producto' }}</div>
                        <div class="prod-meta">
                            {{ number_format($precioPres, 2) }} {{ $moneda }}
                            <span class="muted">• {{ number_format((float)($detalle->precio_unitario ?? 0), 2) }} / u</span>
                            @if($descMonto > 0)
                                <div class="muted">Desc: {{ number_format($descPct, 2) }}% (-{{ number_format($descMonto, 2) }} {{ $moneda }})</div>
                            @endif
                        </div>
                    </td>

                    <td class="col-pres">
                        <div class="prod-name" style="font-weight:700;">{{ $presentacion }}</div>
                        <div class="muted">{{ $unidades }} u/pres.</div>
                    </td>

                    <td class="col-cant text-center">
                        <div class="prod-name">{{ $cantPres }}</div>
                    </td>

                    <td class="col-subt text-right">
                        <div class="prod-name">{{ number_format($subtotal, 2) }} {{ $moneda }}</div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="padding:10px 0; text-align:center;">Sin productos.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="totales">
    <div class="total-row">
        <span>Subtotal bruto:</span>
        <span>{{ number_format((float)($venta->subtotal_bruto ?? 0), 2) }} {{ $moneda }}</span>
    </div>

    <div class="total-row">
        <span>Desc. productos:</span>
        <span>-{{ number_format((float)$descuentoProductos, 2) }} {{ $moneda }}</span>
    </div>

    <div class="total-row">
        <span>Desc. global:</span>
        <span>
            {{ number_format((float)($venta->descuento_porcentaje ?? 0), 2) }}%
            (-{{ number_format((float)$descuentoGlobalMonto, 2) }} {{ $moneda }})
        </span>
    </div>

    <div class="total-row total-final">
        <span>TOTAL:</span>
        <span>{{ number_format((float)($venta->total ?? 0), 2) }} {{ $moneda }}</span>
    </div>
</div>

<div class="pago">
    @if(($venta->metodo_pago ?? '') === 'efectivo')
        <div class="total-row">
            <span>Dinero recibido:</span>
            <span>{{ is_null($montoRecibido) ? '—' : number_format((float)$montoRecibido, 2).' '.$moneda }}</span>
        </div>
        <div class="total-row">
            <span>Cambio:</span>
            <span>{{ is_null($cambio) ? '—' : number_format((float)$cambio, 2).' '.$moneda }}</span>
        </div>
    @else
        <div class="total-row">
            <span>Pago:</span>
            <span>{{ strtoupper($venta->metodo_pago ?? '—') }}</span>
        </div>
    @endif

    @if(!empty($venta->observaciones))
        <div style="margin-top:6px;">
            <div class="info-label">Obs:</div>
            <div class="muted" style="white-space: pre-wrap;">{{ $venta->observaciones }}</div>
        </div>
    @endif
</div>

@if($venta->recetas && $venta->recetas->isNotEmpty())
    <div class="info" style="margin-top: 8px;">
        <div style="font-weight: 800; margin-bottom: 5px;">RECETAS MÉDICAS:</div>
        @foreach($venta->recetas as $receta)
            <div style="margin: 3px 0;">
                <div>Receta: {{ $receta->numero_receta }}</div>
                <div class="muted">Dr(a): {{ $receta->medico }}</div>
            </div>
        @endforeach
    </div>
@endif

@if(($venta->estado ?? '') === 'anulada')
    <div style="margin: 8px 0; padding: 8px; border: 2px solid #000; text-align: center;">
        <div style="font-weight: 900; font-size: 13px;">⚠️ VENTA ANULADA ⚠️</div>
        <div style="margin-top: 4px; font-size: 10px;">{{ $venta->fecha_anulacion?->format('d/m/Y H:i') ?? '' }}</div>
        <div style="margin-top: 4px; font-size: 10px;">Motivo: {{ $venta->motivo_anulacion ?? '—' }}</div>
    </div>
@endif

<div class="footer">
    <p style="margin: 5px 0;">¡Gracias por su compra!</p>
    <p style="margin: 5px 0;">Conserve este comprobante</p>
    <p style="margin-top: 10px; font-size: 10px;">
        Sistema FarmaBien<br>
        Impreso: {{ now()->format('d/m/Y H:i:s') }}
    </p>
</div>

<div class="no-print" style="text-align:center; margin-top: 14px;">
    <button onclick="window.print()" style="padding: 10px 22px; font-size: 14px; cursor: pointer; background: #16A34A; color: #fff; border: none; border-radius: 6px;">
        🖨️ Imprimir
    </button>
    <button onclick="window.close()" style="padding: 10px 22px; font-size: 14px; cursor: pointer; background: #6B7280; color: #fff; border: none; border-radius: 6px; margin-left: 8px;">
        Cerrar
    </button>
</div>

<script>
    // Si quieres auto-imprimir al abrir:
    // window.addEventListener('load', () => window.print());
</script>
</body>
</html>
