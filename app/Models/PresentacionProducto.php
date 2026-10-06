<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PresentacionProducto extends Model
{
    use HasFactory;

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

    public function detallesCompra(): HasMany
    {
        return $this->hasMany(DetalleCompra::class, 'presentacion_id');
    }

    public function detallesVentas(): HasMany
    {
        return $this->hasMany(DetalleVenta::class, 'presentacion_id');
    }

    public function detallesVenta(): HasMany
    {
        return $this->hasMany(DetalleVenta::class, 'presentacion_id');
    }

    public function preciosVenta(): HasMany
    {
        return $this->hasMany(PrecioVenta::class, 'presentacion_id')->orderBy('vigente_desde', 'desc');
    }

    public function precioVentaVigente()
    {
        return $this->hasOne(PrecioVenta::class, 'presentacion_id')->whereNull('vigente_hasta');
    }

    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    public function scopePorProducto($query, int $productoId)
    {
        return $query->where('producto_id', $productoId);
    }

    public function scopeBuscar($query, string $buscar)
    {
        $buscar = trim($buscar);
        if ($buscar === '') {
            return $query;
        }

        return $query->where(function ($q) use ($buscar) {
            $q->where('nombre', 'like', "%{$buscar}%")
              ->orWhere('descripcion', 'like', "%{$buscar}%")
              ->orWhere('codigo_barras', 'like', "%{$buscar}%")
              ->orWhereHas('producto', fn ($p) => $p->where('nombre', 'like', "%{$buscar}%"));
        });
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
            $label .= ' - Venta: C$' . number_format($this->precio_venta, 2);
        }
        return $label;
    }

    /**
     * Calcula la cantidad equivalente en unidades base para esta presentación
     */
    public function calcularUnidadesBase(int $cantidad): int
    {
        return $cantidad * max(1, (int)$this->unidades_por_presentacion);
    }
}
