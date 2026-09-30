<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Libro de Control MINSA — {{ $farmacia['nombre'] }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Times New Roman', Times, serif; font-size: 11pt; color: #000; background: #fff; }

        .no-print { }
        @media print { .no-print { display: none !important; } }

        .page { max-width: 210mm; margin: 0 auto; padding: 15mm; }

        /* Header del libro */
        .libro-header { text-align: center; border-bottom: 3px double #000; padding-bottom: 8px; margin-bottom: 16px; }
        .libro-header h1 { font-size: 14pt; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
        .libro-header h2 { font-size: 12pt; font-weight: bold; margin-top: 4px; }
        .libro-header .meta { font-size: 9pt; margin-top: 6px; color: #333; }

        /* Tabla */
        table { width: 100%; border-collapse: collapse; font-size: 8.5pt; margin-top: 12px; }
        th { background: #e8e8e8; border: 1px solid #000; padding: 4px 5px; text-align: center; font-weight: bold; font-size: 8pt; text-transform: uppercase; vertical-align: middle; }
        td { border: 1px solid #555; padding: 3px 5px; vertical-align: top; }
        tr:nth-child(even) td { background: #f9f9f9; }

        /* Firma al final */
        .firmas { margin-top: 32px; display: flex; justify-content: space-around; }
        .firma-box { text-align: center; width: 200px; }
        .firma-line { border-top: 1px solid #000; margin-top: 48px; padding-top: 4px; font-size: 9pt; }

        /* Badge nivel */
        .nivel-i  { font-weight: bold; color: #92400e; }
        .nivel-ii { font-weight: bold; color: #c2410c; }
        .nivel-iii{ font-weight: bold; color: #be123c; }

        /* Print button */
        .print-btn {
            position: fixed; top: 16px; right: 16px; z-index: 999;
            padding: 10px 20px; background: #065f46; color: #fff;
            border: none; border-radius: 8px; font-size: 14px;
            cursor: pointer; font-family: sans-serif; font-weight: 600;
            box-shadow: 0 2px 8px rgba(0,0,0,.2);
        }
        .print-btn:hover { background: #047857; }
        @media print { .print-btn { display: none; } }

        /* Back link */
        .back-link {
            position: fixed; top: 16px; left: 16px; z-index: 999;
            padding: 10px 20px; background: #1e293b; color: #fff;
            border: none; border-radius: 8px; font-size: 13px;
            cursor: pointer; font-family: sans-serif; font-weight: 600;
            text-decoration: none; display: inline-block;
        }
        @media print { .back-link { display: none; } }
    </style>
</head>
<body>
    <a href="{{ route('controlados.index') }}" class="back-link no-print">← Volver</a>
    <button onclick="window.print()" class="print-btn no-print">🖨️ Imprimir</button>

    <div class="page">
        {{-- Encabezado oficial --}}
        <div class="libro-header">
            <h1>República de Nicaragua — MINSA</h1>
            <h2>Libro de Control de Estupefacientes y Psicotrópicos</h2>
            <div class="meta">
                <strong>Establecimiento:</strong> {{ $farmacia['nombre'] }}
                &nbsp;&nbsp;|&nbsp;&nbsp;
                <strong>Período:</strong>
                {{ \Carbon\Carbon::parse($desde)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($hasta)->format('d/m/Y') }}
                @if($nivel)
                    &nbsp;&nbsp;|&nbsp;&nbsp;
                    <strong>Nivel:</strong>
                    @if($nivel == 1) I — Control Básico @elseif($nivel == 2) II — Opioides @else III — Narcóticos @endif
                @endif
                &nbsp;&nbsp;|&nbsp;&nbsp;
                <strong>Impreso:</strong> {{ now()->format('d/m/Y H:i') }}
                &nbsp;&nbsp;|&nbsp;&nbsp;
                <strong>Total registros:</strong> {{ $registros->count() }}
            </div>
        </div>

        @if($registros->isEmpty())
            <p style="text-align:center; padding: 40px; color: #666;">
                No hay despachos de controlados en el período seleccionado.
            </p>
        @else
            <table>
                <thead>
                    <tr>
                        <th style="width:5%">#</th>
                        <th style="width:9%">Fecha</th>
                        <th style="width:5%">Niv.</th>
                        <th style="width:18%">Medicamento / Principio Activo</th>
                        <th style="width:5%">Cant.</th>
                        <th style="width:14%">Paciente</th>
                        <th style="width:9%">Cédula Pac.</th>
                        <th style="width:5%">Edad</th>
                        <th style="width:14%">Médico Prescriptor</th>
                        <th style="width:9%">Reg. MINSA</th>
                        <th style="width:12%">Diagnóstico</th>
                        <th style="width:8%">Farmacéutico</th>
                        <th style="width:6%">Venta #</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($registros as $i => $reg)
                    <tr>
                        <td style="text-align:center; color:#666;">{{ $i + 1 }}</td>
                        <td style="text-align:center; white-space:nowrap;">{{ $reg->created_at->format('d/m/Y') }}<br><small>{{ $reg->created_at->format('H:i') }}</small></td>
                        <td style="text-align:center;">
                            @if($reg->nivel_controlado == 1)
                                <span class="nivel-i">Niv.I</span>
                            @elseif($reg->nivel_controlado == 2)
                                <span class="nivel-ii">Niv.II</span>
                            @else
                                <span class="nivel-iii">Niv.III</span>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $reg->producto->nombre ?? '—' }}</strong>
                            @if($reg->producto?->principio_activo)
                            <br><small style="color:#555;">{{ $reg->producto->principio_activo }}
                            @if($reg->producto?->concentracion) {{ $reg->producto->concentracion }} @endif
                            </small>
                            @endif
                            @if($reg->lote?->numero_lote)
                            <br><small style="color:#888;">Lote: {{ $reg->lote->numero_lote }}</small>
                            @endif
                        </td>
                        <td style="text-align:center;">{{ number_format($reg->cantidad, 0) }}<br><small>{{ $reg->unidad }}</small></td>
                        <td>{{ $reg->paciente_nombre }}</td>
                        <td style="text-align:center;">{{ $reg->paciente_cedula ?: '—' }}</td>
                        <td style="text-align:center;">{{ $reg->paciente_edad ? $reg->paciente_edad.' a.' : '—' }}</td>
                        <td>{{ $reg->medico_nombre }}</td>
                        <td style="text-align:center;">{{ $reg->medico_num_registro ?: '—' }}</td>
                        <td style="font-size:7.5pt;">{{ $reg->diagnostico ?: '—' }}</td>
                        <td style="font-size:7.5pt;">{{ $reg->despachador->name ?? '—' }}</td>
                        <td style="text-align:center;">#{{ $reg->venta_id }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        {{-- Firmas --}}
        <div class="firmas">
            <div class="firma-box">
                <div class="firma-line">Responsable del Establecimiento</div>
            </div>
            <div class="firma-box">
                <div class="firma-line">Director Técnico / Farmacéutico</div>
            </div>
            <div class="firma-box">
                <div class="firma-line">Inspector MINSA</div>
            </div>
        </div>
    </div>
</body>
</html>
