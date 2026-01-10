<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Producto extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'codigo_barra',
        'nombre',
        'descripcion',
        'imagen',
        'categoria_id',
        'precio_venta',
        'stock_minimo',
        'requiere_receta',
        'activo',
    ];

    protected $casts = [
        'precio_venta' => 'decimal:2',
        'requiere_receta' => 'boolean',
        'activo' => 'boolean',
    ];

    // Relaciones
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    public function lotes(): HasMany
    {
        return $this->hasMany(Lote::class);
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoInventario::class);
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopeConReceta($query)
    {
        return $query->where('requiere_receta', true);
    }

    public function scopeBajoStock($query)
    {
        return $query->whereRaw('(
            SELECT COALESCE(SUM(stock_inicial), 0) 
            FROM lotes 
            WHERE lotes.producto_id = productos.id 
            AND lotes.activo = 1
        ) < stock_minimo');
    }

    // Accessor: Stock total actual
    public function getStockTotalAttribute(): int
    {
        return $this->lotes()
            ->where('activo', true)
            ->get()
            ->sum('stock_actual');
    }

    // Método: Verificar si tiene stock disponible
    public function tieneStock(int $cantidad = 1): bool
    {
        return $this->stock_total >= $cantidad;
    }
}
