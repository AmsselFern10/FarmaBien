<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lote extends Model
{
    use HasFactory;

    protected $table = 'lotes';

    protected $fillable = [
        'producto_id',
        'compra_id',
        'proveedor_id',
        'numero_lote',
        'fecha_vencimiento',
        'stock_inicial',
        'stock_actual',
        'precio_compra',
        'activo',
    ];

    protected $casts = [
        'fecha_vencimiento' => 'date',
        'stock_inicial' => 'integer',
        'stock_actual' => 'integer',
        'precio_compra' => 'decimal:2',
        'activo' => 'boolean',
    ];

    // Relaciones
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    public function compra(): BelongsTo
    {
        return $this->belongsTo(Compra::class);
    }

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoInventario::class);
    }

    public function detallesVenta(): HasMany
    {
        return $this->hasMany(DetalleVenta::class);
    }

    public function detallesVentas(): HasMany
    {
        return $this->hasMany(DetalleVenta::class);
    }

    public function detallesCompra(): HasMany
    {
        return $this->hasMany(DetalleCompra::class);
    }

    public function detallesCompras(): HasMany
    {
        return $this->hasMany(DetalleCompra::class);
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }

    public function scopeDisponibles($query)
    {
        return $query->where('activo', true)
            ->where('fecha_vencimiento', '>', now()->toDateString())
            ->where('stock_actual', '>', 0);
    }

    public function scopeVencidos($query)
    {
        return $query->where('fecha_vencimiento', '<=', now()->toDateString());
    }

    public function scopeVigentes($query)
    {
        return $query->where('fecha_vencimiento', '>', now()->toDateString());
    }

    public function scopeProximosVencer($query, int $dias = 30)
    {
        return $query->where('fecha_vencimiento', '<=', now()->addDays($dias)->toDateString())
            ->where('fecha_vencimiento', '>', now()->toDateString())
            ->where('stock_actual', '>', 0);
    }

    public function scopeBuscar($query, ?string $termino)
    {
        if (empty($termino)) {
            return $query;
        }

        $buscar = trim($termino);
        return $query->where(function ($q) use ($buscar) {
            $q->where('numero_lote', 'like', "%{$buscar}%")
              ->orWhereHas('producto', function ($qp) use ($buscar) {
                  $qp->where('nombre', 'like', "%{$buscar}%")
                     ->orWhere('principio_activo', 'like', "%{$buscar}%")
                     ->orWhere('codigo_barra', 'like', "%{$buscar}%");
              });
        });
    }

    public function scopeOrdenarPor($query, string $criterio = 'vencimiento_asc')
    {
        return match ($criterio) {
            'vencimiento_desc' => $query->orderBy('fecha_vencimiento', 'desc'),
            'ingreso_desc'     => $query->orderBy('created_at', 'desc')->orderBy('id', 'desc'),
            'ingreso_asc'      => $query->orderBy('created_at', 'asc')->orderBy('id', 'asc'),
            'stock_desc'       => $query->orderBy('stock_actual', 'desc'),
            'stock_asc'        => $query->orderBy('stock_actual', 'asc'),
            default            => $query->orderBy('fecha_vencimiento', 'asc'),
        };
    }

    // Métodos
    public function estaVencido(): bool
    {
        return $this->fecha_vencimiento <= now()->toDateString();
    }

    public function proximoAVencer(int $dias = 30): bool
    {
        return $this->fecha_vencimiento <= now()->addDays($dias)->toDateString() 
            && !$this->estaVencido();
    }

    public function tieneStock(int $cantidad = 1): bool
    {
        return $this->stock_actual >= $cantidad;
    }

    /**
     * Cálculo único y homogéneo de días restantes sin desviación horaria
     */
    public function getDiasRestantesAttribute(): int
    {
        if (!$this->fecha_vencimiento) {
            return 0;
        }

        return (int) now()->startOfDay()->diffInDays($this->fecha_vencimiento->startOfDay(), false);
    }
}
