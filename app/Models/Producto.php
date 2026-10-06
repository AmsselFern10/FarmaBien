<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Producto extends Model
{
    use HasFactory, SoftDeletes;

    public const TIPO_VENTA_LIBRE = 'venta_libre';
    public const TIPO_CONTROLADO = 'controlado';

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

    public function lotesActivos(): HasMany
    {
        return $this->hasMany(Lote::class)->where('activo', true);
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoInventario::class);
    }

    public function detallesCompra(): HasMany
    {
        return $this->hasMany(DetalleCompra::class);
    }

    public function detallesCompras(): HasMany
    {
        return $this->hasMany(DetalleCompra::class);
    }

    public function detallesVenta(): HasMany
    {
        return $this->hasMany(DetalleVenta::class);
    }

    public function detallesVentas(): HasMany
    {
        return $this->hasMany(DetalleVenta::class);
    }

    public function recetaDetalles(): HasMany
    {
        return $this->hasMany(RecetaDetalle::class);
    }

    public function preciosVenta(): HasMany
    {
        return $this->hasMany(PrecioVenta::class)->orderBy('vigente_desde', 'desc');
    }

    public function precioVentaVigente()
    {
        return $this->hasOne(PrecioVenta::class)->whereNull('presentacion_id')->whereNull('vigente_hasta');
    }

    public function unidadBase()
    {
        return $this->hasOne(PresentacionProducto::class)->where('es_unidad_base', true);
    }

    // Helpers de Control Sanitario
    public function esControlado(): bool
    {
        return $this->tipo_control === self::TIPO_CONTROLADO || (bool) $this->requiere_receta;
    }

    public function esVentaLibre(): bool
    {
        return !$this->esControlado();
    }

    /**
     * Calcula la cantidad equivalente en unidades base
     */
    public function calcularUnidadesBase(int $cantidad, ?PresentacionProducto $presentacion = null): int
    {
        return $presentacion ? $presentacion->calcularUnidadesBase($cantidad) : $cantidad;
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('productos.activo', true);
    }

    public function scopeActivo($query)
    {
        return $query->where('productos.activo', true);
    }

    public function scopeBuscar($query, string $buscar, array $principiosIa = [])
    {
        $buscar = trim($buscar);
        if ($buscar === '') {
            return $query;
        }

        return $query->where(function ($q) use ($buscar, $principiosIa) {
            $q->where('nombre', 'like', "%{$buscar}%")
              ->orWhere('principio_activo', 'like', "%{$buscar}%")
              ->orWhere('descripcion', 'like', "%{$buscar}%")
              ->orWhere('codigo_barra', 'like', "%{$buscar}%");

            if (!empty($principiosIa)) {
                foreach ($principiosIa as $pActivo) {
                    $q->orWhere('principio_activo', 'like', "%{$pActivo}%");
                }
            }
        });
    }

    public function scopePorCategoria($query, int $categoriaId)
    {
        return $query->where('categoria_id', $categoriaId);
    }

    public function scopePorLaboratorio($query, int $laboratorioId)
    {
        return $query->where('laboratorio_id', $laboratorioId);
    }

    public function scopePorRegimen($query, string $regimen)
    {
        if ($regimen === 'controlado' || $regimen === 'controlados') {
            return $this->scopeControlados($query);
        }

        if ($regimen === 'venta_libre') {
            return $this->scopeVentaLibre($query);
        }

        return $query;
    }

    public function scopeControlados($query)
    {
        return $query->where(function ($q) {
            $q->where('tipo_control', self::TIPO_CONTROLADO)
              ->orWhere('requiere_receta', true);
        });
    }

    public function scopeVentaLibre($query)
    {
        return $query->where('tipo_control', self::TIPO_VENTA_LIBRE)
                     ->where('requiere_receta', false);
    }

    public function scopeConReceta($query)
    {
        return $this->scopeControlados($query);
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
        ) <= stock_minimo', [$today]);
    }

    // Accessors
    public function getStockTotalAttribute(): int
    {
        if (array_key_exists('stock_total', $this->attributes) && $this->attributes['stock_total'] !== null) {
            return (int) $this->attributes['stock_total'];
        }

        if ($this->relationLoaded('lotes')) {
            return (int) $this->lotes->where('activo', true)->sum('stock_actual');
        }

        return (int) $this->lotes()
            ->where('activo', true)
            ->sum('stock_actual');
    }

    public function getStockDisponibleAttribute(): int
    {
        if (array_key_exists('stock_disponible', $this->attributes) && $this->attributes['stock_disponible'] !== null) {
            return (int) $this->attributes['stock_disponible'];
        }

        if ($this->relationLoaded('lotes')) {
            $today = now()->toDateString();
            return (int) $this->lotes->where('activo', true)->where('fecha_vencimiento', '>', $today)->sum('stock_actual');
        }

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
        $promociones = \Illuminate\Support\Facades\Cache::remember('promociones_vigentes_pos', 60, function () {
            return Promocion::vigentes()->orderBy('id', 'desc')->get();
        });

        return $promociones->first(function ($p) {
            return $p->alcance === 'producto' && (int)$p->producto_id === (int)$this->id;
        }) ?? $promociones->first(function ($p) {
            return $p->alcance === 'categoria' && (int)$p->categoria_id === (int)$this->categoria_id;
        }) ?? $promociones->first(function ($p) {
            return $p->alcance === 'laboratorio' && (int)$p->laboratorio_id === (int)$this->laboratorio_id;
        }) ?? $promociones->first(function ($p) {
            return $p->alcance === 'general';
        });
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
