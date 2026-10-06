<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cliente extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nombre',
        'documento',
        'telefono',
        'email',
        'direccion',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    protected static ?\Illuminate\Database\Eloquent\Collection $cachedPosClientes = null;

    protected static function booted(): void
    {
        static::saved(function () {
            static::clearCache();
        });
        static::deleted(function () {
            static::clearCache();
        });
    }

    public static function getCachedPosClientes(): \Illuminate\Database\Eloquent\Collection
    {
        if (static::$cachedPosClientes !== null) {
            return static::$cachedPosClientes;
        }

        return static::$cachedPosClientes = \Illuminate\Support\Facades\Cache::remember('pos_clientes_init_50', 60, function () {
            return static::activos()->orderBy('nombre')->limit(50)->get(['id', 'nombre', 'documento', 'telefono']);
        });
    }

    public static function clearCache(): void
    {
        static::$cachedPosClientes = null;
        \Illuminate\Support\Facades\Cache::forget('pos_clientes_init_50');
    }

    // Relaciones
    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class);
    }

    public function recetas(): HasMany
    {
        return $this->hasMany(Receta::class);
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
              ->orWhere('documento', 'like', "%{$termino}%")
              ->orWhere('telefono', 'like', "%{$termino}%")
              ->orWhere('email', 'like', "%{$termino}%")
              ->orWhere('direccion', 'like', "%{$termino}%");
        });
    }

    // Accessor: Nombre completo con documento
    public function getNombreCompletoAttribute(): string
    {
        return $this->documento 
            ? "{$this->nombre} ({$this->documento})"
            : $this->nombre;
    }
}