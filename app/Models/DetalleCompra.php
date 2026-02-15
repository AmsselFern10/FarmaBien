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

        // Presentación (snapshot)
        'presentacion_id',
        'tipo_presentacion',
        'unidades_por_presentacion',
        'cantidad_presentaciones',

        // Cantidad real en unidades base (inventario)
        'cantidad_unidades_base',

        // Precios y descuentos
        'precio_unitario',        // BRUTO por unidad base
        'subtotal_bruto',
        'descuento_porcentaje',   // % por producto
        'descuento_monto',
        'subtotal',               // NETO
    ];

    protected $casts = [
        'unidades_por_presentacion' => 'integer',
        'cantidad_presentaciones' => 'integer',
        'cantidad_unidades_base' => 'integer',

        'precio_unitario' => 'decimal:2',
        'subtotal_bruto' => 'decimal:2',
        'descuento_porcentaje' => 'decimal:2',
        'descuento_monto' => 'decimal:2',
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

    /**
     * Compatibilidad: vistas antiguas que usan $detalle->cantidad.
     * Ahora la cantidad real es cantidad_unidades_base.
     */
    public function getCantidadAttribute(): int
    {
        return (int)($this->cantidad_unidades_base ?? 0);
    }

    public function usaPresentacion(): bool
    {
        return !empty($this->presentacion_id) || !empty($this->tipo_presentacion);
    }

    public function getNombrePresentacionAttribute(): string
    {
        return $this->tipo_presentacion
            ?? $this->presentacion?->nombre
            ?? 'Unidad';
    }

    public function getDescripcionPresentacionAttribute(): string
    {
        if (!$this->usaPresentacion()) {
            return 'Unidad';
        }

        $nombre = $this->getNombrePresentacionAttribute();
        $unidades = (int)($this->unidades_por_presentacion ?? 1);
        $cantPres = (int)($this->cantidad_presentaciones ?? 0);

        return "{$cantPres} {$nombre}(s) x {$unidades}";
    }

    public function getResumenCantidadAttribute(): string
    {
        $base = (int)($this->cantidad_unidades_base ?? 0);
        $unidades = (int)($this->unidades_por_presentacion ?? 1);
        $cantPres = (int)($this->cantidad_presentaciones ?? 0);

        if ($this->usaPresentacion() && $unidades > 1) {
            return "{$cantPres} × {$unidades} = {$base} unidades";
        }

        return "{$base} unidades";
    }

    public function getPrecioPresentacionAttribute(): float
    {
        $unidades = (int)($this->unidades_por_presentacion ?? 1);
        return round(((float)$this->precio_unitario) * $unidades, 2);
    }
}
