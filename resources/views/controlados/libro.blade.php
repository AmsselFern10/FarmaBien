<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Libro de Control MINSA — {{ $farmacia['nombre'] }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Times New Roman', Times, serif; font-size: 9.5pt; color: #000; background: #fff; line-height: 1.25; }

        .no-print { }
        @media print {
            @page {
                size: landscape;
                margin: 8mm 10mm;
            }
            .no-print { display: none !important; }
            body { font-size: 8pt; }
            .page { max-width: 100% !important; padding: 0 !important; margin: 0 !important; }
            table { page-break-inside: auto; margin-top: 8px; }
            tr { page-break-inside: avoid; page-break-after: auto; }
            thead { display: table-header-group; }
            tfoot { display: table-footer-group; }
            .firmas { page-break-inside: avoid; margin-top: 24px; }
        }

        .page { max-width: 280mm; margin: 0 auto; padding: 10mm 15mm; }

        /* Header del libro */
        .libro-header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 8px; margin-bottom: 12px; }
        .libro-header h1 { font-size: 13pt; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; }
        .libro-header h2 { font-size: 11pt; font-weight: bold; margin-top: 3px; }
        .libro-header .meta { font-size: 8.5pt; margin-top: 4px; color: #222; }

        /* Tabla */
        table { width: 100%; border-collapse: collapse; font-size: 7.5pt; margin-top: 10px; }
        th { background: #f0f0f0; border: 1px solid #000; padding: 4px 3px; text-align: center; font-weight: bold; font-size: 7.5pt; text-transform: uppercase; vertical-align: middle; }
        td { border: 1px solid #444; padding: 3px 3px; vertical-align: middle; }
        tr:nth-child(even) td { background: #fafafa; }

        /* Firma al final */
        .firmas { margin-top: 28px; display: flex; justify-content: space-around; }
        .firma-box { text-align: center; width: 220px; }
        .firma-line { border-top: 1px solid #000; margin-top: 40px; padding-top: 4px; font-size: 8.5pt; }

        /* Badges & text */
        .txt-entrada { font-weight: bold; color: #047857; }
        .txt-salida { font-weight: bold; color: #1e293b; }
        .txt-merma { font-weight: bold; color: #b91c1c; }

        /* Action buttons bar */
        .action-bar {
            position: fixed; top: 16px; right: 16px; z-index: 999;
            display: flex; align-items: center; gap: 8px;
        }
        .btn-action {
            padding: 8px 14px; border: none; border-radius: 8px; font-size: 12px;
            cursor: pointer; font-family: sans-serif; font-weight: 600;
            text-decoration: none; display: inline-flex; align-items: center; gap: 6px;
            box-shadow: 0 2px 6px rgba(0,0,0,.15); transition: background 0.15s ease;
        }
        .btn-back { position: fixed; top: 16px; left: 16px; z-index: 999; background: #334155; color: #fff; }
        .btn-back:hover { background: #1e293b; }
        .btn-excel { background: #0f766e; color: #fff; }
        .btn-excel:hover { background: #115e59; }
        .btn-print { background: #059669; color: #fff; }
        .btn-print:hover { background: #047857; }
    </style>
</head>
<body>
    <a href="{{ route('controlados.index') }}" class="btn-action btn-back no-print">
        <svg style="width:14px; height:14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        <span>Bitácora MINSA</span>
    </a>

    <div class="action-bar no-print">
        <a href="{{ route('controlados.excel') }}?{{ http_build_query(request()->only(['desde','hasta','tipo_movimiento','tipo_despacho','producto_id','q'])) }}" 
           class="btn-action btn-excel">
            <svg style="width:14px; height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <span>Descargar Excel</span>
        </a>
        <button onclick="window.print()" class="btn-action btn-print">
            <svg style="width:15px; height:15px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            <span>Imprimir</span>
        </button>
    </div>

    <div class="page">
        {{-- Encabezado oficial --}}
        <div class="libro-header">
            <h1>República de Nicaragua — MINSA</h1>
            <h2>Libro Oficial de Medicamentos Controlados & Fiscalizados</h2>
            <div class="meta">
                <strong>Establecimiento:</strong> {{ $farmacia['nombre'] }}
                &nbsp;&nbsp;|&nbsp;&nbsp;
                <strong>Período:</strong>
                {{ \Carbon\Carbon::parse($desde)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($hasta)->format('d/m/Y') }}
                &nbsp;&nbsp;|&nbsp;&nbsp;
                <strong>Impreso:</strong> {{ now()->format('d/m/Y H:i') }}
                &nbsp;&nbsp;|&nbsp;&nbsp;
                <strong>Total movimientos:</strong> {{ $registros->count() }}
            </div>
        </div>

        @if($registros->isEmpty())
            <p style="text-align:center; padding: 40px; color: #666;">
                No hay movimientos de controlados asentados en el período seleccionado.
            </p>
        @else
            <table>
                <thead>
                    <tr>
                        <th style="width:3%">#</th>
                        <th style="width:7%">Fecha/Hora</th>
                        <th style="width:9%">Operación</th>
                        <th style="width:18%">Medicamento / Lote</th>
                        <th style="width:5%">Entrada (+)</th>
                        <th style="width:5%">Salida (-)</th>
                        <th style="width:13%">Paciente / Beneficiario</th>
                        <th style="width:8%">Cédula / RUC</th>
                        <th style="width:13%">Médico / Prescriptor</th>
                        <th style="width:11%">Diagnóstico / Justificación MINSA</th>
                        <th style="width:8%">Responsable</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($registros as $i => $reg)
                    <tr>
                        <td style="text-align:center; color:#666;">{{ $i + 1 }}</td>
                        <td style="text-align:center; white-space:nowrap;">
                            {{ $reg->created_at->format('d/m/Y') }}<br><small style="color:#666;">{{ $reg->created_at->format('H:i') }}</small>
                        </td>
                        <td style="text-align:center; font-size:7pt;">
                            <strong>{{ $reg->tipo_etiqueta }}</strong>
                            @if($reg->venta_id)
                                <br><small style="color:#555;">Venta #{{ $reg->venta_id }}</small>
                            @elseif($reg->devolucion)
                                <br><small style="color:#555;">{{ $reg->devolucion->numero_devolucion }}</small>
                            @elseif($reg->compra)
                                <br><small style="color:#555;">Compra #{{ $reg->compra->numero_comprobante }}</small>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $reg->producto->nombre ?? '—' }}</strong>
                            @if($reg->producto?->principio_activo)
                            <br><small style="color:#444;">{{ $reg->producto->principio_activo }}
                            @if($reg->producto?->concentracion) ({{ $reg->producto->concentracion }}) @endif
                            </small>
                            @endif
                            @if($reg->lote?->numero_lote)
                            <br><small style="color:#777;">Lote: {{ $reg->lote->numero_lote }}</small>
                            @endif
                        </td>
                        <td style="text-align:center;">
                            @if($reg->esEntrada())
                                <span class="txt-entrada">+{{ number_format($reg->cantidad, 0) }}</span>
                            @else
                                <span style="color:#aaa;">—</span>
                            @endif
                        </td>
                        <td style="text-align:center;">
                            @if($reg->esSalida())
                                <span class="{{ $reg->esMerma() ? 'txt-merma' : 'txt-salida' }}">-{{ number_format($reg->cantidad, 0) }}</span>
                            @else
                                <span style="color:#aaa;">—</span>
                            @endif
                        </td>
                        <td>{{ $reg->paciente_nombre ?: 'Público General' }}</td>
                        <td style="text-align:center;">{{ $reg->paciente_cedula ?: '—' }}</td>
                        <td>
                            @if($reg->medico_nombre)
                                Dr(a). {{ Str::title(mb_strtolower($reg->medico_nombre)) }}
                                @if($reg->medico_num_registro)
                                    <br><small style="color:#666;">Reg: {{ $reg->medico_num_registro }}</small>
                                @endif
                            @else
                                <span style="color:#777;">—</span>
                            @endif
                        </td>
                        <td style="font-size:7pt;">
                            @if($reg->motivo_omision)
                                <em>{{ $reg->motivo_omision }}</em>
                            @else
                                {{ $reg->diagnostico ?: 'Tratamiento prescrito' }}
                            @endif
                        </td>
                        <td style="font-size:7pt; text-align:center;">{{ $reg->despachador->name ?? 'Sistema' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        {{-- Firmas Oficiales --}}
        <div class="firmas">
            <div class="firma-box">
                <div class="firma-line">Responsable del Establecimiento</div>
            </div>
            <div class="firma-box">
                <div class="firma-line">Regente / Director Técnico</div>
            </div>
            <div class="firma-box">
                <div class="firma-line">Inspector Farmacéutico SILAIS/MINSA</div>
            </div>
        </div>
    </div>
</body>
</html>
