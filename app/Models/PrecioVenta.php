<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class PrecioVenta extends Model
{
    use HasFactory;

    protected $table = 'precios_venta';

    protected $fillable = [
        'producto_id',
        'presentacion_id',
        'precio',
        'vigente_desde',
        'vigente_hasta',
        'motivo',
        'user_id',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'vigente_desde' => 'datetime',
        'vigente_hasta' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saved(function () {
            \App\Facades\RequestCache::forgetPrefix('precios:');
            \App\Facades\RequestCache::forgetPrefix('productos:');
            \Illuminate\Support\Facades\Cache::forget('precio_metricas_v1');
        });

        static::deleted(function () {
            \App\Facades\RequestCache::forgetPrefix('precios:');
            \App\Facades\RequestCache::forgetPrefix('productos:');
            \Illuminate\Support\Facades\Cache::forget('precio_metricas_v1');
        });
    }

    /**
     * Relación con el producto principal
     */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    /**
     * Relación con la presentación (si aplica, null = unidad base)
     */
    public function presentacion(): BelongsTo
    {
        return $this->belongsTo(PresentacionProducto::class, 'presentacion_id');
    }

    /**
     * Usuario que registró o modificó el precio
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scope para precios vigentes (vigente_hasta es NULL)
     */
    public function scopeVigente(Builder $query): Builder
    {
        return $query->whereNull('vigente_hasta');
    }

    /**
     * Scope para precios históricos (vigente_hasta no es NULL)
     */
    public function scopeHistorico(Builder $query): Builder
    {
        return $query->whereNotNull('vigente_hasta');
    }

    /**
     * Scope para precios base (sin presentación específica)
     */
    public function scopeBase(Builder $query): Builder
    {
        return $query->whereNull('presentacion_id');
    }

    /**
     * Scope para precios de presentaciones
     */
    public function scopePresentaciones(Builder $query): Builder
    {
        return $query->whereNotNull('presentacion_id');
    }
}
