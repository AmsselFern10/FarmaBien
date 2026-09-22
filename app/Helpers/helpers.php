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
