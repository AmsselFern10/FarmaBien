<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetalleDevolucionCompra extends Model
{
    protected $table = 'detalles_devolucion_compra';

    protected $fillable = [
        'devolucion_compra_id', 'lote_id', 'producto_id',
        'cantidad', 'precio_unitario', 'subtotal', 'motivo_detalle',
    ];

    protected $casts = [
        'cantidad'       => 'integer',
        'precio_unitario'=> 'decimal:2',
        'subtotal'       => 'decimal:2',
    ];

    public function devolucionCompra(): BelongsTo
    {
        return $this->belongsTo(DevolucionCompra::class, 'devolucion_compra_id');
    }

    public function lote(): BelongsTo
    {
        return $this->belongsTo(Lote::class);
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }
}
