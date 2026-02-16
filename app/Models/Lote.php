<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lote extends Model
{
    protected $fillable = [
        'producto_id',
        'compra_id',
        'proveedor_id',
        'numero_lote',
        'fecha_vencimiento',
        'fecha_ingreso',
        'stock_inicial',
        'stock_actual',
        'precio_compra',
        'estado',
        'bloqueado_at',
        'bloqueado_por',
        'motivo_bloqueo',
        'activo',
    ];

    protected $casts = [
        'fecha_vencimiento' => 'date',
        'fecha_ingreso' => 'datetime',
        'precio_compra' => 'decimal:6',
        'activo' => 'boolean',
        'bloqueado_at' => 'datetime',
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

    public function bloqueador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'bloqueado_por');
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Lotes disponibles para venta:
     * - activo
     * - no bloqueado
     * - no vencido (por fecha, no por hora)
     * - stock_actual > 0
     */
    public function scopeDisponibles($query)
    {
        return $query
            ->where('activo', true)
            ->whereNull('bloqueado_at')
            ->where('estado', '!=', 'bloqueado')
            ->where(function ($q) {
                // Si NO tiene fecha de vencimiento, se considera vendible.
                // Si tiene, se permite vender HASTA el día del vencimiento (>= hoy).
                $q->whereNull('fecha_vencimiento')
                  ->orWhereDate('fecha_vencimiento', '>=', today());
            })
            ->where('stock_actual', '>', 0);
    }

    public function scopeVencidos($query)
    {
        return $query
            ->whereNotNull('fecha_vencimiento')
            ->whereDate('fecha_vencimiento', '<', today());
    }

    public function scopeProximosVencer($query, int $dias = 30)
    {
        return $query
            ->whereNotNull('fecha_vencimiento')
            ->whereDate('fecha_vencimiento', '>=', today())
            ->whereDate('fecha_vencimiento', '<=', today()->addDays($dias));
    }

    // Métodos de estado
    public function estaVencido(): bool
    {
        return $this->fecha_vencimiento
            ? $this->fecha_vencimiento->lt(today())
            : false;
    }

    public function proximoAVencer(int $dias = 30): bool
    {
        if (!$this->fecha_vencimiento) {
            return false;
        }

        // Incluye el mismo día (0 días) hasta N días.
        return $this->fecha_vencimiento->gte(today())
            && $this->fecha_vencimiento->lte(today()->addDays($dias));
    }

    public function tieneStock(int $cantidad = 1): bool
    {
        return $this->stock_actual >= $cantidad;
    }

    public function estaBloqueado(): bool
    {
        return !is_null($this->bloqueado_at) || $this->estado === 'bloqueado';
    }
}
