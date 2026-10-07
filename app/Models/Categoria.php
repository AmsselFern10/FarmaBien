<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Facades\RequestCache;

class Categoria extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nombre',
        'descripcion',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saved(function () {
            static::clearCache();
        });
        static::deleted(function () {
            static::clearCache();
        });
        static::restored(function () {
            static::clearCache();
        });
    }

    public static function getCachedActivos(): \Illuminate\Database\Eloquent\Collection
    {
        return RequestCache::remember('categorias:activas', function () {
            return static::select(['id', 'nombre'])->activos()->orderBy('nombre')->get();
        });
    }

    public static function clearCache(): void
    {
        RequestCache::forgetPrefix('categorias:');
    }

    // Relaciones
    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class);
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopeActivas($query)
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
              ->orWhere('descripcion', 'like', "%{$termino}%");
        });
    }
}
