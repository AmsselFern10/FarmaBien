<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Facades\RequestCache;

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
        return RequestCache::remember('clientes:pos_top50', function () {
            return static::activos()->orderBy('nombre')->limit(50)->get(['id', 'nombre', 'documento', 'telefono']);
        });
    }

    public static function clearCache(): void
    {
        RequestCache::forgetPrefix('clientes:');
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