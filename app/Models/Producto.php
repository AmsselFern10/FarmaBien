<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\PresentacionProducto;

class Producto extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'codigo_barra',
        'nombre',
        'descripcion',
        'laboratorio',
        'principio_activo',
        'concentracion',
        'via_administracion',
        'imagen',
        'categoria_id',
        'precio_compra',      // ✅ AGREGADO
        'precio_venta',
        'stock_minimo',
        'ubicacion',          // Por si lo tienes
        'requiere_receta',
        'activo',
    ];

    protected $casts = [
        'precio_compra' => 'decimal:2',  // ✅ AGREGADO
        'precio_venta' => 'decimal:2',
        'requiere_receta' => 'boolean',
        'activo' => 'boolean',
    ];

    // Relaciones
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    public function lotes(): HasMany
    {
        return $this->hasMany(Lote::class);
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoInventario::class);
    }

    public function detallesCompra(): HasMany
    {
        return $this->hasMany(DetalleCompra::class);
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopeConReceta($query)
    {
        return $query->where('requiere_receta', true);
    }

    public function scopeBajoStock($query)
    {
        return $query->whereRaw('(
            SELECT COALESCE(SUM(stock_inicial), 0) 
            FROM lotes 
            WHERE lotes.producto_id = productos.id 
            AND lotes.activo = 1
        ) < stock_minimo');
    }

    // Accessor: Stock total actual
    public function getStockTotalAttribute(): int
    {
        return $this->lotes()
            ->where('activo', true)
            ->get()
            ->sum('stock_actual');
    }

    // ✅ NUEVO: Accessor para stock disponible
    public function getStockDisponibleAttribute(): int
    {
        return $this->lotes()
            ->disponibles() // Asume que tienes un scope en Lote
            ->get()
            ->sum('stock_actual');
    }

    // Método: Verificar si tiene stock disponible
    public function tieneStock(int $cantidad = 1): bool
    {
        return $this->stock_total >= $cantidad;
    }

    // ✅ NUEVO: Obtener último precio de compra real
    public function ultimoPrecioCompra(): ?float
    {
        $ultimaCompra = $this->detallesCompra()
            ->whereHas('compra', function($q) {
                $q->where('estado', 'recibida')
                  ->whereNull('reemplazada_por');
            })
            ->latest()
            ->first();

        return $ultimaCompra?->precio_compra;
    }

    // ✅ NUEVO: Calcular margen de ganancia
    public function getMargenGananciaAttribute(): ?float
    {
        if (!$this->precio_compra || $this->precio_compra <= 0) {
            return null;
        }

        return (($this->precio_venta - $this->precio_compra) / $this->precio_compra) * 100;
    }

    public function presentaciones(): HasMany
    {
    return $this->hasMany(PresentacionProducto::class)->ordenado();
    }

    /**
 * Presentaciones activas
 */
public function presentacionesActivas(): HasMany
{
    return $this->hasMany(PresentacionProducto::class)->activas()->ordenado();
}

/**
 * Obtener presentación por defecto (unidad base)
 */
public function getPresentacionUnidadAttribute(): array
{
    return [
        'id' => null,
        'nombre' => 'Unidad',
        'descripcion' => 'Unidad base',
        'unidades_por_presentacion' => 1,
        'precio_sugerido' => $this->precio_compra,
        'nombre_completo' => 'Unidad base',
    ];
}

/**
 * Obtener todas las presentaciones incluyendo la unidad base
 */
public function getPresentacionesConUnidadAttribute()
{
    $presentaciones = $this->presentacionesActivas->map(function ($pres) {
        return [
            'id' => $pres->id,
            'nombre' => $pres->nombre,
            'descripcion' => $pres->descripcion,
            'unidades_por_presentacion' => $pres->unidades_por_presentacion,
            'precio_sugerido' => $pres->precio_sugerido,
            'nombre_completo' => $pres->nombre_completo,
            'select_label' => $pres->select_label,
        ];
    })->prepend($this->presentacion_unidad);
    
    return $presentaciones;
}

/**
 * Verificar si tiene presentaciones configuradas
 */
public function tienePresentaciones(): bool
{
    return $this->presentaciones()->activas()->exists();
}

/**
 * Calcular precio de compra por presentación
 * 
 * @param int $presentacionId
 * @param float $precioUnitario
 * @return float
 */
public function calcularPrecioPorPresentacion($presentacionId, float $precioUnitario): float
{
    if (!$presentacionId) {
        return $precioUnitario; // Es unidad base
    }

    $presentacion = $this->presentaciones()->find($presentacionId);
    
    if (!$presentacion) {
        return $precioUnitario;
    }

    return $presentacion->calcularPrecioPresentacion($precioUnitario);
}

/**
 * Obtener unidades por presentación
 * 
 * @param int|null $presentacionId
 * @return int
 */
public function getUnidadesPorPresentacion($presentacionId): int
{
    if (!$presentacionId) {
        return 1; // Unidad base
    }

    $presentacion = $this->presentaciones()->find($presentacionId);
    
    return $presentacion ? $presentacion->unidades_por_presentacion : 1;
}
}