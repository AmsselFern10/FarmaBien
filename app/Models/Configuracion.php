<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Configuracion extends Model
{
    use HasFactory;

    protected $table = 'configuraciones';

    protected $fillable = [
        'clave',
        'valor',
        'tipo',
        'grupo',
        'descripcion',
    ];

    /**
     * Cache key para almacenar todas las configuraciones en memoria
     */
    public const CACHE_KEY = 'app_configuraciones_assoc';

    /**
     * Obtener el valor de una configuración por su clave
     */
    public static function get(string $clave, mixed $default = null): mixed
    {
        $configs = static::allAsAssoc();

        if (!array_key_exists($clave, $configs)) {
            return $default;
        }

        $item = $configs[$clave];
        $val = $item['valor'] ?? null;
        $tipo = $item['tipo'] ?? 'string';

        if ($val === null) {
            return $default;
        }

        return match ($tipo) {
            'boolean', 'bool' => filter_var($val, FILTER_VALIDATE_BOOLEAN),
            'integer', 'int'   => (int) $val,
            'float', 'numeric' => (float) $val,
            'json', 'array'    => json_decode($val, true) ?? $default,
            default            => (string) $val,
        };
    }

    /**
     * Establecer / Guardar una configuración
     */
    public static function set(string $clave, mixed $valor, string $grupo = 'general', string $tipo = 'string', ?string $descripcion = null): self
    {
        $serialized = match ($tipo) {
            'boolean', 'bool' => $valor ? '1' : '0',
            'json', 'array'    => is_string($valor) ? $valor : json_encode($valor),
            default            => (string) $valor,
        };

        $config = static::updateOrCreate(
            ['clave' => $clave],
            [
                'valor'       => $serialized,
                'grupo'       => $grupo,
                'tipo'        => $tipo,
                'descripcion' => $descripcion,
            ]
        );

        Cache::forget(static::CACHE_KEY);

        return $config;
    }

    /**
     * Obtener todas las configuraciones indexadas por clave (desde caché)
     */
    public static function allAsAssoc(): array
    {
        return Cache::rememberForever(static::CACHE_KEY, function () {
            return static::all(['clave', 'valor', 'tipo', 'grupo', 'descripcion'])
                ->keyBy('clave')
                ->toArray();
        });
    }

    /**
     * Limpiar caché de configuraciones
     */
    public static function clearCache(): void
    {
        Cache::forget(static::CACHE_KEY);
    }
}
