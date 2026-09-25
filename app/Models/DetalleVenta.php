<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetalleVenta extends Model
{
    protected $table = 'detalle_venta';

    protected $fillable = [
        'venta_id',
        'producto_id',
        'lote_id',
        'presentacion_id',
        'receta_detalle_id',
        'cantidad',
        'unidades_por_presentacion',
        'cantidad_unidades_base',
        'precio_unitario',
        'subtotal',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'unidades_por_presentacion' => 'integer',
        'cantidad_unidades_base' => 'integer',
        'precio_unitario' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    // Relaciones
    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class);
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

    public function recetaDetalle(): BelongsTo
    {
        return $this->belongsTo(RecetaDetalle::class, 'receta_detalle_id');
    }

    public function usaPresentacion(): bool
    {
        return !empty($this->presentacion_id);
    }
}
