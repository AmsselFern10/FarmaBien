<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobante de Arqueo #{{ str_pad($sesion->id, 5, '0', STR_PAD_LEFT) }}</title>
    <style>
        :root{
            --paper-width: 80mm;
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

        .kv{
            display:flex;
            justify-content: space-between;
            gap: 8px;
            margin: 2px 0;
        }
        .kv .k{ font-weight:700; }
        .kv .v{ text-align:right; }

        .wrap{ overflow-wrap:anywhere; word-break:break-word; }

        .actions{
            margin-bottom: 12px;
            display: flex;
            gap: 8px;
            justify-content: center;
        }
        .btn{
            font-family: system-ui, -apple-system, sans-serif;
            font-size: 12px;
            padding: 6px 12px;
            border-radius: 6px;
            border: 1px solid #ccc;
            background: #f4f4f5;
            cursor: pointer;
            font-weight: 600;
        }
        .btn-primary{
            background: #059669;
            color: #fff;
            border-color: #047857;
        }

        .sign-area{
            margin-top: 25px;
            display: flex;
            justify-content: space-between;
            gap: 15px;
        }
        .sign-box{
            flex: 1;
            text-align: center;
            border-top: 1px solid #000;
            padding-top: 4px;
            font-size: 9px;
        }
    </style>
</head>
<body>

    <!-- Botones de Acción (Ocultos en impresión) -->
    <div class="actions no-print">
        <button class="btn btn-primary" onclick="window.print()">🖨️ Imprimir Ticket</button>
        <button class="btn" onclick="window.close()">✕ Cerrar</button>
    </div>

    <!-- Encabezado de la Empresa -->
    <div class="center">
        @if(configuracion('empresa_logo'))
            <div style="margin-bottom: 5px;">
                <img src="{{ asset('storage/' . configuracion('empresa_logo')) }}" alt="Logo" style="max-height: 40px; max-width: 130px; object-fit: contain;">
            </div>
        @endif
        <div class="title">{{ configuracion('empresa_nombre', 'FARMABIEN') }}</div>
        @if(configuracion('empresa_razon_social'))
            <div class="small">{{ configuracion('empresa_razon_social') }}</div>
        @endif
        @if(configuracion('empresa_ruc'))
            <div class="small">RUC/NIT: {{ configuracion('empresa_ruc') }}</div>
        @endif
        @if(configuracion('empresa_direccion'))
            <div class="xs wrap">{{ configuracion('empresa_direccion') }}</div>
        @endif
        @if(configuracion('empresa_telefono'))
            <div class="xs">TELF: {{ configuracion('empresa_telefono') }}</div>
        @endif
    </div>

    <!-- Título del Documento -->
    <div class="doc center">
        ARQUEO Y CORTE DE CAJA
    </div>
    <div class="center bold small">
        SESIÓN #{{ str_pad($sesion->id, 6, '0', STR_PAD_LEFT) }}
    </div>

    <div class="rule"></div>

    <!-- Datos del Turno y Terminal -->
    <div class="kv"><span class="k">Caja / Terminal:</span><span class="v bold">{{ $sesion->caja->nombre }} ({{ $sesion->caja->codigo }})</span></div>
    <div class="kv"><span class="k">Cajero Apertura:</span><span class="v">{{ $sesion->usuario->name }}</span></div>
    @if($sesion->usuarioCierre)
        <div class="kv"><span class="k">Cajero Cierre:</span><span class="v">{{ $sesion->usuarioCierre->name }}</span></div>
    @endif
    <div class="kv"><span class="k">F. Apertura:</span><span class="v">{{ $sesion->fecha_apertura->format('d/m/Y H:i:s') }}</span></div>
    @if($sesion->fecha_cierre)
        <div class="kv"><span class="k">F. Cierre:</span><span class="v">{{ $sesion->fecha_cierre->format('d/m/Y H:i:s') }}</span></div>
    @else
        <div class="kv"><span class="k">Estado:</span><span class="v bold">** TURNO EN CURSO **</span></div>
    @endif

    <div class="rule-strong"></div>

    <!-- Resumen Financiero -->
    <div class="bold center small" style="margin-bottom: 4px;">DESGLOSE FINANCIERO</div>

    <div class="kv"><span class="k">Fondo Inicial de Caja:</span><span class="v bold">${{ number_format($sesion->monto_inicial, 2) }}</span></div>
    
    <div class="rule"></div>
    <div class="kv"><span class="k">(+) Ventas en Efectivo:</span><span class="v bold">+${{ number_format($sesion->total_ventas_efectivo, 2) }}</span></div>
    <div class="kv"><span class="k">(+) Tarjeta (POS):</span><span class="v">${{ number_format($sesion->total_ventas_tarjeta, 2) }}</span></div>
    <div class="kv"><span class="k">(+) Transferencia:</span><span class="v">${{ number_format($sesion->total_ventas_transferencia, 2) }}</span></div>
    <div class="kv"><span class="k">TOTAL TODAS LAS VENTAS:</span><span class="v bold">${{ number_format($sesion->total_ventas, 2) }}</span></div>

    <div class="rule"></div>
    <div class="kv"><span class="k">(+) Ingresos Manuales:</span><span class="v">+${{ number_format($sesion->total_ingresos_manuales, 2) }}</span></div>
    <div class="kv"><span class="k">(-) Egresos / Gastos:</span><span class="v">-${{ number_format($sesion->total_egresos_manuales, 2) }}</span></div>

    <div class="rule-strong"></div>
    <div class="kv" style="font-size: 12px;"><span class="k">EFECTIVO ESPERADO:</span><span class="v bold">${{ number_format($sesion->monto_esperado_efectivo, 2) }}</span></div>

    @if(!$sesion->estaAbierta())
        @php
            $dif = (float) $sesion->diferencia_efectivo;
        @endphp
        <div class="kv" style="font-size: 12px;"><span class="k">EFECTIVO DECLARADO:</span><span class="v bold">${{ number_format($sesion->monto_final_efectivo, 2) }}</span></div>
        <div class="rule"></div>
        <div class="kv" style="font-size: 12px;">
            <span class="k">DIFERENCIA:</span>
            <span class="v bold">
                @if($dif == 0)
                    $0.00 (CUADRADO)
                @elseif($dif > 0)
                    +${{ number_format($dif, 2) }} (SOBRANTE)
                @else
                    -${{ number_format(abs($dif), 2) }} (FALTANTE)
                @endif
            </span>
        </div>
    @endif

    @if($sesion->observaciones_apertura || $sesion->observaciones_cierre)
        <div class="rule"></div>
        <div class="small bold">OBSERVACIONES:</div>
        @if($sesion->observaciones_apertura)
            <div class="xs wrap"><span class="bold">Apertura:</span> {{ $sesion->observaciones_apertura }}</div>
        @endif
        @if($sesion->observaciones_cierre)
            <div class="xs wrap"><span class="bold">Cierre:</span> {{ $sesion->observaciones_cierre }}</div>
        @endif
    @endif

    <div class="sign-area">
        <div class="sign-box">
            Firma Cajero(a)<br>
            {{ $sesion->usuario->name }}
        </div>
        <div class="sign-box">
            Firma Supervisor(a)<br>
            Auditoría
        </div>
    </div>

    <div class="center xs" style="margin-top: 15px;">
        Impreso el {{ now()->format('d/m/Y H:i:s') }}<br>
        FarmaBien - Software de Gestión Farmacéutica
    </div>

</body>
</html>
