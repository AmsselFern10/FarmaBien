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
        'precio_sugerido',
        'codigo_barras',
        'activo',
        'orden',
    ];

    protected $casts = [
        'unidades_por_presentacion' => 'integer',
        'precio_sugerido' => 'decimal:2',
        'activo' => 'boolean',
        'orden' => 'integer',
    ];

    /**
     * Producto al que pertenece esta presentación
     */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    /**
     * Detalles de compras que usaron esta presentación
     */
    public function detallesCompras(): HasMany
    {
        return $this->hasMany(DetalleCompra::class, 'presentacion_id');
    }

    /**
     * Scope para presentaciones activas
     */
    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para ordenar por orden personalizado
     */
    public function scopeOrdenado($query)
    {
        return $query->orderBy('orden', 'asc')
            ->orderBy('nombre', 'asc');
    }

    /**
     * Calcular precio unitario base desde precio de presentación
     * 
     * @param float $precioPresentacion
     * @return float
     */
    public function calcularPrecioUnitario(float $precioPresentacion): float
    {
        return round($precioPresentacion / $this->unidades_por_presentacion, 2);
    }

    /**
     * Calcular precio de presentación desde precio unitario
     * 
     * @param float $precioUnitario
     * @return float
     */
    public function calcularPrecioPresentacion(float $precioUnitario): float
    {
        return round($precioUnitario * $this->unidades_por_presentacion, 2);
    }

    /**
     * Obtener nombre completo con descripción
     * 
     * @return string
     */
    public function getNombreCompletoAttribute(): string
    {
        if ($this->descripcion) {
            return "{$this->nombre} - {$this->descripcion}";
        }
        
        return "{$this->nombre} (x{$this->unidades_por_presentacion})";
    }

    /**
     * Validar que el precio sugerido sea coherente
     * 
     * @return bool
     */
    public function validarPrecioSugerido(): bool
    {
        if (!$this->precio_sugerido || !$this->producto->precio_compra) {
            return true;
        }

        $precioUnitarioCalculado = $this->calcularPrecioUnitario($this->precio_sugerido);
        
        // El precio unitario no debería ser menor al precio de compra del producto
        return $precioUnitarioCalculado >= $this->producto->precio_compra * 0.8; // 20% tolerancia
    }

    /**
     * Obtener label para select
     * 
     * @return string
     */
    public function getSelectLabelAttribute(): string
    {
        $label = $this->nombre_completo;
        
        if ($this->precio_sugerido) {
            $label .= " - S/ " . number_format($this->precio_sugerido, 2);
        }
        
        return $label;
    }

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        // Al eliminar una presentación, actualizar los detalles de compra
        static::deleting(function ($presentacion) {
            // Guardar el nombre en tipo_presentacion antes de eliminar
            $presentacion->detallesCompras()->update([
                'tipo_presentacion' => $presentacion->nombre,
                'presentacion_id' => null
            ]);
        });
    }
}