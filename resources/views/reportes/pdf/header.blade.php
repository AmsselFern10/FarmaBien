<table class="header-table">
    <tr>
        <td style="width: 60%; vertical-align: top;">
            <div class="pharmacy-title">{{ configuracion('empresa_nombre', 'FARMABIEN') }}</div>
            <div class="pharmacy-subtitle">{{ configuracion('empresa_razon_social', 'Farmacia & Droguería FarmaBien C.A.') }} · Sistema Farmacéutico</div>
            <div class="pharmacy-details">
                <strong>RIF / RUC:</strong> {{ configuracion('empresa_ruc', 'J-40892154-0') }} &nbsp;|&nbsp; <strong>Teléfono:</strong> {{ configuracion('empresa_telefono', '(0212) 555-0199') }}<br>
                <strong>Dirección:</strong> {{ configuracion('empresa_direccion', 'Av. Principal Los Próceres, Caracas') }}<br>
                <strong>Correo:</strong> {{ configuracion('empresa_email', 'contacto@farmabien.com') }} &nbsp;|&nbsp; <strong>Sucursal:</strong> Principal (001)
            </div>
        </td>
        <td style="width: 40%; vertical-align: top; text-align: right;">
            <div class="report-box">
                <div class="report-title">{{ $tituloReporte ?? 'INFORME GERENCIAL' }}</div>
                <div class="report-meta">
                    <strong>Fecha Emisión:</strong> {{ now()->format('d/m/Y H:i:s') }}<br>
                    <strong>Emitido por:</strong> {{ Auth::user()->name ?? 'Sistema' }} ({{ Auth::user()->roles->first()?->name ?? 'Usuario' }})<br>
                    <strong>ID Reporte:</strong> #REP-{{ strtoupper(substr(md5(now()->timestamp . ($tituloReporte ?? '')), 0, 8)) }}
                </div>
            </div>
        </td>
    </tr>
</table>
