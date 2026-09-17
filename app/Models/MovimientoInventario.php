<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovimientoInventario extends Model
{
    protected $table = 'movimientos_inventario';

    protected $fillable = [
        'producto_id',
        'lote_id',
        'user_id',
        'tipo',
        'subtipo',
        'cantidad',
        'stock_anterior',
        'stock_posterior',
        'costo_unitario',
        'costo_total',
        'origen',
        'origen_id',
        'motivo',
        'fecha_movimiento',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'stock_anterior' => 'integer',
        'stock_posterior' => 'integer',
        'costo_unitario' => 'decimal:2',
        'costo_total' => 'decimal:2',
        'fecha_movimiento' => 'datetime',
    ];

    // Relaciones
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    public function lote(): BelongsTo
    {
        return $this->belongsTo(Lote::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Scopes
    public function scopeEntradas($query)
    {
        return $query->where('tipo', 'entrada');
    }

    public function scopeSalidas($query)
    {
        return $query->where('tipo', 'salida');
    }

    public function scopeAjustes($query)
    {
        return $query->where('tipo', 'ajuste');
    }
}
