<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetalleDevolucionVenta extends Model
{
    protected $table = 'detalle_devoluciones_ventas';

    protected $fillable = [
        'devolucion_venta_id',
        'detalle_venta_id',
        'producto_id',
        'lote_id',
        'cantidad',
        'unidades_por_presentacion',
        'cantidad_unidades_base',
        'precio_unitario',
        'subtotal',
        'reingresa_a_stock',
        'estado_producto',
    ];

    protected $casts = [
        'precio_unitario' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'reingresa_a_stock' => 'boolean',
    ];

    public function devolucion(): BelongsTo
    {
        return $this->belongsTo(DevolucionVenta::class, 'devolucion_venta_id');
    }

    public function detalleVenta(): BelongsTo
    {
        return $this->belongsTo(DetalleVenta::class, 'detalle_venta_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function lote(): BelongsTo
    {
        return $this->belongsTo(Lote::class, 'lote_id');
    }
}
