<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lote extends Model
{
    protected $fillable = [
        'producto_id',
        'compra_id',
        'proveedor_id',
        'numero_lote',
        'fecha_vencimiento',
        'stock_inicial',
        'precio_compra',
        'activo',
    ];

    protected $casts = [
        'fecha_vencimiento' => 'date',
        'precio_compra' => 'decimal:2',
        'activo' => 'boolean',
    ];

    // Relaciones
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    public function compra(): BelongsTo
    {
        return $this->belongsTo(Compra::class);
    }

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoInventario::class);
    }

    // Accessor: Stock actual calculado
    public function getStockActualAttribute(): int
    {
        return $this->stock_inicial + $this->movimientos()->sum('cantidad');
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopeDisponibles($query)
    {
        return $query->where('activo', true)
            ->where('fecha_vencimiento', '>', now())
            ->whereRaw('stock_inicial + (
                SELECT COALESCE(SUM(cantidad), 0) 
                FROM movimientos_inventario 
                WHERE movimientos_inventario.lote_id = lotes.id
            ) > 0');
    }

    public function scopeVencidos($query)
    {
        return $query->where('fecha_vencimiento', '<', now());
    }

    public function scopeProximosVencer($query, int $dias = 30)
    {
        return $query->where('fecha_vencimiento', '<=', now()->addDays($dias))
            ->where('fecha_vencimiento', '>', now());
    }

    // Métodos
    public function estaVencido(): bool
    {
        return $this->fecha_vencimiento < now();
    }

    public function proximoAVencer(int $dias = 30): bool
    {
        return $this->fecha_vencimiento <= now()->addDays($dias) 
            && !$this->estaVencido();
    }

    public function tieneStock(int $cantidad = 1): bool
    {
        return $this->stock_actual >= $cantidad;
    }
}
