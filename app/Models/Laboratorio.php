<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Laboratorio extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'laboratorios';

    protected $fillable = [
        'nombre',
        'codigo',
        'contacto',
        'telefono',
        'email',
        'pais_origen',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    protected static ?\Illuminate\Database\Eloquent\Collection $cachedActivos = null;

    protected static function booted(): void
    {
        static::saved(function () {
            static::clearCache();
        });
        static::deleted(function () {
            static::clearCache();
        });
    }

    public static function getCachedActivos(): \Illuminate\Database\Eloquent\Collection
    {
        if (static::$cachedActivos !== null) {
            return static::$cachedActivos;
        }

        return static::$cachedActivos = \Illuminate\Support\Facades\Cache::remember('catalog_laboratorios_base', 300, function () {
            return static::select(['id', 'nombre', 'codigo'])->activos()->orderBy('nombre')->get();
        });
    }

    public static function clearCache(): void
    {
        static::$cachedActivos = null;
        \Illuminate\Support\Facades\Cache::forget('catalog_laboratorios_base');
    }

    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class);
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }

    public function scopeBuscar($query, ?string $termino)
    {
        if (empty($termino)) {
            return $query;
        }

        $termino = trim($termino);
        return $query->where(function ($q) use ($termino) {
            $q->where('nombre', 'like', "%{$termino}%")
              ->orWhere('codigo', 'like', "%{$termino}%")
              ->orWhere('contacto', 'like', "%{$termino}%")
              ->orWhere('pais_origen', 'like', "%{$termino}%")
              ->orWhere('email', 'like', "%{$termino}%")
              ->orWhere('telefono', 'like', "%{$termino}%");
        });
    }
}
