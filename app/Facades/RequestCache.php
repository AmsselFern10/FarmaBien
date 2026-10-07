<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;
use App\Support\RequestCache as RequestCacheService;

/**
 * @method static mixed remember(string $key, \Closure $callback)
 * @method static mixed get(string $key, mixed $default = null)
 * @method static void put(string $key, mixed $value)
 * @method static bool has(string $key)
 * @method static void forget(string $key)
 * @method static void forgetPrefix(string $prefix)
 * @method static void flush()
 *
 * @see \App\Support\RequestCache
 */
class RequestCache extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return RequestCacheService::class;
    }
}
