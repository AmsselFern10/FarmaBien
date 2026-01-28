<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetalleCompra extends Model
{
    protected $table = 'detalle_compra';

  

    // Relaciones
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

    // Agregar al $fillable:
protected $fillable = [
    'compra_id',
    'producto_id',
    'lote_id',
    'presentacion_id',           // NUEVO
    'tipo_presentacion',          // NUEVO
    'unidades_por_presentacion',  // NUEVO
    'cantidad_presentaciones',    // NUEVO
    // 'cantidad_unidades_base' es calculado automáticamente
    'cantidad_legacy',            // RENOMBRADO (antes 'cantidad')
    'precio_unitario',
    'subtotal',
];

// Agregar al $casts:
protected $casts = [
    'unidades_por_presentacion' => 'integer',
    'cantidad_presentaciones' => 'integer',
    'cantidad_unidades_base' => 'integer',
    'cantidad_legacy' => 'integer',
    'precio_unitario' => 'decimal:2',
    'subtotal' => 'decimal:2',
];

// AGREGAR ESTAS RELACIONES Y MÉTODOS:

/**
 * Presentación utilizada en esta compra
 */
public function presentacion(): BelongsTo
{
    return $this->belongsTo(PresentacionProducto::class, 'presentacion_id');
}

/**
 * Obtener la cantidad efectiva (compatibilidad con código anterior)
 * 
 * @return int
 */
public function getCantidadAttribute(): int
{
    // Si es compra nueva (con presentaciones)
    if ($this->cantidad_unidades_base) {
        return $this->cantidad_unidades_base;
    }
    
    // Si es compra antigua (sin presentaciones)
    return $this->cantidad_legacy ?? 0;
}

/**
 * Obtener descripción de la presentación
 * 
 * @return string
 */
public function getDescripcionPresentacionAttribute(): string
{
    if (!$this->presentacion_id && !$this->tipo_presentacion) {
        return 'Unidad base';
    }

    $nombre = $this->tipo_presentacion ?? $this->presentacion?->nombre ?? 'Desconocida';
    
    return "{$this->cantidad_presentaciones} {$nombre}(s) x {$this->unidades_por_presentacion} unidades";
}

/**
 * Obtener texto resumido para mostrar
 * 
 * @return string
 */
public function getResumenCantidadAttribute(): string
{
    if ($this->unidades_por_presentacion === 1) {
        return "{$this->cantidad_unidades_base} unidades";
    }

    return "{$this->cantidad_presentaciones} × {$this->unidades_por_presentacion} = {$this->cantidad_unidades_base} unidades";
}

/**
 * Calcular precio total de la presentación
 * 
 * @return float
 */
public function getPrecioPresentacionAttribute(): float
{
    return round($this->precio_unitario * $this->unidades_por_presentacion, 2);
}

/**
 * Calcular subtotal basado en presentaciones
 * 
 * @return float
 */
public function calcularSubtotal(): float
{
    return round($this->precio_presentacion * $this->cantidad_presentaciones, 2);
}

/**
 * Verificar si usa presentación
 * 
 * @return bool
 */
public function usaPresentacion(): bool
{
    return $this->presentacion_id !== null || $this->tipo_presentacion !== null;
}

/**
 * Obtener nombre de la presentación
 * 
 * @return string
 */
public function getNombrePresentacionAttribute(): string
{
    if ($this->presentacion) {
        return $this->presentacion->nombre;
    }
    
    return $this->tipo_presentacion ?? 'Unidad';
}

/**
 * Boot method
 */
protected static function boot()
{
    parent::boot();

    // Al guardar, calcular el subtotal
    static::saving(function ($detalle) {
        $detalle->subtotal = $detalle->calcularSubtotal();
    });
}

/**
 * Accessor para mantener compatibilidad con código que usa 'cantidad'
 */
public function getCantidadTotalAttribute(): int
{
    return $this->cantidad;
}
}
