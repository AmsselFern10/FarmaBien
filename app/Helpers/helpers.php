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

if (!function_exists('perPage')) {
    /**
     * Registros por página según la preferencia global de interfaz (Ajustes → Diseño).
     * Si no está configurado usa el $default recibido.
     */
    function perPage(int $default = 15): int
    {
        $val = (int) configuracion('interfaz_registros_por_pagina', 0);
        return $val >= 10 ? $val : $default;
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

