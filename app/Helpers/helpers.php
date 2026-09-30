<?php

use App\Models\Configuracion;

if (!function_exists('configuracion')) {
    /**
     * Helper global para acceder a las configuraciones del sistema
     */
    function configuracion(string $clave, mixed $default = null): mixed
    {
        return Configuracion::get($clave, $default);
    }
}

if (!function_exists('formato_moneda')) {
    /**
     * Helper global para formatear montos en moneda local (Córdoba por defecto)
     */
    function formato_moneda(float|int|string|null $monto, int $decimales = 2): string
    {
        $simbolo = configuracion('moneda_simbolo', 'C$');
        return $simbolo . ' ' . number_format((float) ($monto ?? 0), $decimales);
    }
}

