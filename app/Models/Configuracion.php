<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use App\Facades\RequestCache;

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
     * Cache key para almacenar todas las configuraciones en memoria persistente
     */
    public const CACHE_KEY = 'app_configuraciones_assoc';

    protected static function booted(): void
    {
        static::saved(function () {
            static::clearCache();
        });
        static::deleted(function () {
            static::clearCache();
        });
    }

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

        static::clearCache();

        return $config;
    }

    /**
     * Obtener todas las configuraciones indexadas por clave (desde RequestCache por ciclo de petición)
     */
    public static function allAsAssoc(): array
    {
        return RequestCache::remember('config:all_assoc', function () {
            return Cache::rememberForever(static::CACHE_KEY, function () {
                return static::all(['clave', 'valor', 'tipo', 'grupo', 'descripcion'])
                    ->keyBy('clave')
                    ->toArray();
            });
        });
    }

    /**
     * Limpiar caché de configuraciones
     */
    public static function clearCache(): void
    {
        RequestCache::forgetPrefix('config:');
        Cache::forget(static::CACHE_KEY);
    }
}
