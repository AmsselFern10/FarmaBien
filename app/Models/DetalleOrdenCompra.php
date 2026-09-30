<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetalleOrdenCompra extends Model
{
    protected $table = 'detalle_ordenes_compras';

    protected $fillable = [
        'orden_compra_id',
        'producto_id',
        'cantidad_solicitada',
        'cantidad_recibida',
        'precio_unitario_estimado',
        'subtotal',
    ];

    protected $casts = [
        'precio_unitario_estimado' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function ordenCompra(): BelongsTo
    {
        return $this->belongsTo(OrdenCompra::class, 'orden_compra_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
