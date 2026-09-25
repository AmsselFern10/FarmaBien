<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lote extends Model
{
    protected $table = 'lotes';

    protected $fillable = [
        'producto_id',
        'compra_id',
        'proveedor_id',
        'numero_lote',
        'fecha_vencimiento',
        'stock_inicial',
        'stock_actual',
        'precio_compra',
        'activo',
    ];

    protected $casts = [
        'fecha_vencimiento' => 'date',
        'stock_inicial' => 'integer',
        'stock_actual' => 'integer',
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

    public function detallesVenta(): HasMany
    {
        return $this->hasMany(DetalleVenta::class);
    }

    public function detallesVentas(): HasMany
    {
        return $this->hasMany(DetalleVenta::class);
    }

    public function detallesCompra(): HasMany
    {
        return $this->hasMany(DetalleCompra::class);
    }

    public function detallesCompras(): HasMany
    {
        return $this->hasMany(DetalleCompra::class);
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopeDisponibles($query)
    {
        return $query->where('activo', true)
            ->where('fecha_vencimiento', '>', now()->toDateString())
            ->where('stock_actual', '>', 0);
    }

    public function scopeVencidos($query)
    {
        return $query->where('fecha_vencimiento', '<=', now()->toDateString());
    }

    public function scopeVigentes($query)
    {
        return $query->where('fecha_vencimiento', '>', now()->toDateString());
    }

    public function scopeProximosVencer($query, int $dias = 30)
    {
        return $query->where('fecha_vencimiento', '<=', now()->addDays($dias)->toDateString())
            ->where('fecha_vencimiento', '>', now()->toDateString())
            ->where('stock_actual', '>', 0);
    }

    // Métodos
    public function estaVencido(): bool
    {
        return $this->fecha_vencimiento <= now()->toDateString();
    }

    public function proximoAVencer(int $dias = 30): bool
    {
        return $this->fecha_vencimiento <= now()->addDays($dias)->toDateString() 
            && !$this->estaVencido();
    }

    public function tieneStock(int $cantidad = 1): bool
    {
        return $this->stock_actual >= $cantidad;
    }
}
