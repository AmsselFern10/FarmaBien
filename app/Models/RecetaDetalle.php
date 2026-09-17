<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecetaDetalle extends Model
{
    protected $table = 'receta_detalles';

    protected $fillable = [
        'receta_id',
        'producto_id',
        'cantidad_recetada',
        'cantidad_dispensada',
        'posologia',
    ];

    protected $casts = [
        'cantidad_recetada' => 'integer',
        'cantidad_dispensada' => 'integer',
    ];

    public function receta(): BelongsTo
    {
        return $this->belongsTo(Receta::class);
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    public function detallesVenta(): HasMany
    {
        return $this->hasMany(DetalleVenta::class, 'receta_detalle_id');
    }

    public function getPendienteDispensarAttribute(): int
    {
        return max(0, $this->cantidad_recetada - $this->cantidad_dispensada);
    }

    public function estaCompletamenteDispensada(): bool
    {
        return $this->cantidad_dispensada >= $this->cantidad_recetada;
    }
}
