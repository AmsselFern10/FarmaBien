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

        // Snapshot (histórico)
        'numero_lote',

        // Presentación (capa comercial)
        'presentacion_id',
        'tipo_presentacion',
        'unidades_por_presentacion',
        'cantidad_presentaciones',

        // Inventario (unidades base)
        'cantidad_unidades_base',

        // Monetarios
        'precio_unitario',
        'subtotal_bruto',
        'descuento_porcentaje',
        'descuento_monto',
        'subtotal',
    ];

    protected $casts = [
        'presentacion_id' => 'integer',
        'unidades_por_presentacion' => 'integer',
        'cantidad_presentaciones' => 'integer',
        'cantidad_unidades_base' => 'integer',

        'precio_unitario' => 'decimal:2',
        'subtotal_bruto' => 'decimal:2',
        'descuento_porcentaje' => 'decimal:2',
        'descuento_monto' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    /* =======================
     * Relaciones
     * ======================= */

    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class, 'venta_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function lote(): BelongsTo
    {
        return $this->belongsTo(Lote::class, 'lote_id');
    }

    public function presentacion(): BelongsTo
    {
        return $this->belongsTo(PresentacionProducto::class, 'presentacion_id');
    }
}
