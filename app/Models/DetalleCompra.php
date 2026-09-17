<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetalleCompra extends Model
{
    protected $table = 'detalle_compra';

    protected $fillable = [
        'compra_id',
        'producto_id',
        'lote_id',
        'presentacion_id',
        'tipo_presentacion',
        'unidades_por_presentacion',
        'cantidad_presentaciones',
        'cantidad_unidades_base',
        'precio_unitario',
        'subtotal',
    ];

    protected $casts = [
        'unidades_por_presentacion' => 'integer',
        'cantidad_presentaciones' => 'integer',
        'cantidad_unidades_base' => 'integer',
        'precio_unitario' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function compra(): BelongsTo
    {
        return $this->belongsTo(Compra::class);
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    public function lote(): BelongsTo
    {
        return $this->belongsTo(Lote::class);
    }

    public function presentacion(): BelongsTo
    {
        return $this->belongsTo(PresentacionProducto::class, 'presentacion_id');
    }
}
