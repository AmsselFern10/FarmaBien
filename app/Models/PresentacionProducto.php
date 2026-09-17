<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PresentacionProducto extends Model
{
    protected $table = 'presentaciones_producto';

    protected $fillable = [
        'producto_id',
        'nombre',
        'descripcion',
        'unidades_por_presentacion',
        'precio_compra',
        'precio_venta',
        'codigo_barras',
        'es_unidad_base',
        'activo',
        'orden',
    ];

    protected $casts = [
        'unidades_por_presentacion' => 'integer',
        'precio_compra' => 'decimal:2',
        'precio_venta' => 'decimal:2',
        'es_unidad_base' => 'boolean',
        'activo' => 'boolean',
        'orden' => 'integer',
    ];

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    public function detallesCompras(): HasMany
    {
        return $this->hasMany(DetalleCompra::class, 'presentacion_id');
    }

    public function detallesVentas(): HasMany
    {
        return $this->hasMany(DetalleVenta::class, 'presentacion_id');
    }

    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    public function scopeOrdenado($query)
    {
        return $query->orderBy('orden', 'asc')->orderBy('nombre', 'asc');
    }

    public function getNombreCompletoAttribute(): string
    {
        if ($this->descripcion) {
            return "{$this->nombre} - {$this->descripcion}";
        }
        
        return "{$this->nombre} (x{$this->unidades_por_presentacion})";
    }

    public function getSelectLabelAttribute(): string
    {
        $label = $this->nombre_completo;
        if ($this->precio_venta) {
            $label .= " - Venta: $" . number_format($this->precio_venta, 2);
        }
        return $label;
    }
}
