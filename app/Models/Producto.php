<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Producto extends Model
{
    use SoftDeletes;

    protected $table = 'productos';

    protected $fillable = [
        'codigo_barra',
        'nombre',
        'principio_activo',
        'concentracion',
        'forma_farmaceutica',
        'descripcion',
        'imagen',
        'categoria_id',
        'laboratorio_id',
        'registro_sanitario',
        'tipo_control',
        'precio_compra',
        'precio_venta',
        'stock_minimo',
        'ubicacion',
        'requiere_receta',
        'activo',
    ];

    protected $casts = [
        'precio_compra' => 'decimal:2',
        'precio_venta' => 'decimal:2',
        'stock_minimo' => 'integer',
        'requiere_receta' => 'boolean',
        'activo' => 'boolean',
    ];

    // Relaciones
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    public function laboratorio(): BelongsTo
    {
        return $this->belongsTo(Laboratorio::class);
    }

    public function presentaciones(): HasMany
    {
        return $this->hasMany(PresentacionProducto::class)->orderBy('orden', 'asc');
    }

    public function presentacionesActivas(): HasMany
    {
        return $this->hasMany(PresentacionProducto::class)->where('activo', true)->orderBy('orden', 'asc');
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

    public function detallesVenta(): HasMany
    {
        return $this->hasMany(DetalleVenta::class);
    }

    public function recetaDetalles(): HasMany
    {
        return $this->hasMany(RecetaDetalle::class);
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopeConReceta($query)
    {
        return $query->where('requiere_receta', true)
            ->orWhereIn('tipo_control', ['receta_medica', 'receta_retenida']);
    }

    public function scopeBajoStock($query)
    {
        $today = now()->toDateString();
        return $query->whereRaw('(
            SELECT COALESCE(SUM(stock_actual), 0) 
            FROM lotes 
            WHERE lotes.producto_id = productos.id 
            AND lotes.activo = 1
            AND lotes.fecha_vencimiento > ?
        ) < stock_minimo', [$today]);
    }

    // Accessors
    public function getStockTotalAttribute(): int
    {
        return (int) $this->lotes()
            ->where('activo', true)
            ->sum('stock_actual');
    }

    public function getStockDisponibleAttribute(): int
    {
        return (int) $this->lotes()
            ->disponibles()
            ->sum('stock_actual');
    }

    public function tieneStock(int $cantidad = 1): bool
    {
        return $this->stock_disponible >= $cantidad;
    }

    public function getMargenGananciaAttribute(): ?float
    {
        if (!$this->precio_compra || $this->precio_compra <= 0) {
            return null;
        }

        return round((($this->precio_venta - $this->precio_compra) / $this->precio_compra) * 100, 2);
    }

    public function promociones(): HasMany
    {
        return $this->hasMany(Promocion::class);
    }

    /**
     * Obtiene la promoción activa y vigente prioritaria para el producto
     * (Prioridad: Específica por Producto > Por Categoría > Por Laboratorio > General)
     */
    public function getPromocionVigenteAttribute(): ?Promocion
    {
        // 1. Promoción directa por producto
        $promo = Promocion::vigentes()
            ->where('alcance', 'producto')
            ->where('producto_id', $this->id)
            ->latest('id')
            ->first();

        if ($promo) return $promo;

        // 2. Promoción por categoría
        if ($this->categoria_id) {
            $promo = Promocion::vigentes()
                ->where('alcance', 'categoria')
                ->where('categoria_id', $this->categoria_id)
                ->latest('id')
                ->first();

            if ($promo) return $promo;
        }

        // 3. Promoción por laboratorio
        if ($this->laboratorio_id) {
            $promo = Promocion::vigentes()
                ->where('alcance', 'laboratorio')
                ->where('laboratorio_id', $this->laboratorio_id)
                ->latest('id')
                ->first();

            if ($promo) return $promo;
        }

        // 4. Promoción general
        return Promocion::vigentes()
            ->where('alcance', 'general')
            ->latest('id')
            ->first();
    }

    public function getTieneOfertaAttribute(): bool
    {
        return $this->promocion_vigente !== null;
    }

    public function getPrecioOfertaAttribute(): ?float
    {
        $promo = $this->promocion_vigente;
        if (!$promo) return null;

        return $promo->calcularPrecioUnitario((float)$this->precio_venta);
    }

    public function getBadgeOfertaAttribute(): ?string
    {
        $promo = $this->promocion_vigente;
        return $promo ? $promo->badge_texto : null;
    }

    public function getNombreCompletoAttribute(): string
    {
        $partes = [$this->nombre];
        if ($this->concentracion) {
            $partes[] = $this->concentracion;
        }
        if ($this->forma_farmaceutica) {
            $partes[] = "({$this->forma_farmaceutica})";
        }
        return implode(' ', $partes);
    }
}
