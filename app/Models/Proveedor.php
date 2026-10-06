<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Proveedor extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'proveedores';

    protected $fillable = [
        'nombre',
        'ruc',
        'contacto',
        'telefono',
        'email',
        'direccion',
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

        return static::$cachedActivos = \Illuminate\Support\Facades\Cache::remember('catalog_proveedores_base', 300, function () {
            return static::select(['id', 'nombre', 'ruc', 'telefono'])->activos()->orderBy('nombre')->get();
        });
    }

    public static function clearCache(): void
    {
        static::$cachedActivos = null;
        \Illuminate\Support\Facades\Cache::forget('catalog_proveedores_base');
    }

    // Relaciones
    public function compras(): HasMany
    {
        return $this->hasMany(Compra::class);
    }

    public function lotes(): HasMany
    {
        return $this->hasMany(Lote::class);
    }

    public function ordenesCompra(): HasMany
    {
        return $this->hasMany(OrdenCompra::class);
    }

    // Scopes
    public function scopeActivos($query)
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
              ->orWhere('ruc', 'like', "%{$termino}%")
              ->orWhere('contacto', 'like', "%{$termino}%")
              ->orWhere('telefono', 'like', "%{$termino}%")
              ->orWhere('email', 'like', "%{$termino}%")
              ->orWhere('direccion', 'like', "%{$termino}%");
        });
    }
}

