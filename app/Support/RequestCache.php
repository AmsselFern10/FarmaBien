<?php

namespace App\Support;

use Closure;

class RequestCache
{
    /**
     * @var array<string, mixed>
     */
    protected array $storage = [];

    /**
     * Obtener un valor del caché por clave o ejecutar el callback y almacenar el resultado.
     */
    public function remember(string $key, Closure $callback): mixed
    {
        if (array_key_exists($key, $this->storage)) {
            return $this->storage[$key];
        }

        $value = $callback();
        $this->storage[$key] = $value;

        return $value;
    }

    /**
     * Obtener un valor del caché por clave.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return array_key_exists($key, $this->storage) ? $this->storage[$key] : $default;
    }

    /**
     * Establecer un valor en el caché.
     */
    public function put(string $key, mixed $value): void
    {
        $this->storage[$key] = $value;
    }

    /**
     * Determinar si existe una clave en el caché.
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->storage);
    }

    /**
     * Eliminar una clave específica del caché.
     */
    public function forget(string $key): void
    {
        unset($this->storage[$key]);
    }

    /**
     * Eliminar todas las claves que comiencen con el prefijo dado.
     */
    public function forgetPrefix(string $prefix): void
    {
        foreach (array_keys($this->storage) as $key) {
            if (str_starts_with($key, $prefix)) {
                unset($this->storage[$key]);
            }
        }
    }

    /**
     * Limpiar completamente el almacenamiento en memoria.
     */
    public function flush(): void
    {
        $this->storage = [];
    }

    // Static forwarders
    public static function rememberStatic(string $key, Closure $callback): mixed
    {
        return app(self::class)->remember($key, $callback);
    }

    public static function getStatic(string $key, mixed $default = null): mixed
    {
        return app(self::class)->get($key, $default);
    }

    public static function putStatic(string $key, mixed $value): void
    {
        app(self::class)->put($key, $value);
    }

    public static function hasStatic(string $key): bool
    {
        return app(self::class)->has($key);
    }

    public static function forgetStatic(string $key): void
    {
        app(self::class)->forget($key);
    }

    public static function forgetPrefixStatic(string $prefix): void
    {
        app(self::class)->forgetPrefix($prefix);
    }

    public static function flushStatic(): void
    {
        app(self::class)->flush();
    }
}
