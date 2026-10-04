<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovimientoCaja extends Model
{
    use HasFactory;

    protected $table = 'movimientos_caja';

    /**
     * Los movimientos de caja son registros contables inmutables (Append-Only).
     * No se permite su modificación ni eliminación física.
     */
    protected static function booted(): void
    {
        static::updating(function () {
            throw new \DomainException('Los movimientos de caja son registros contables inmutables y no pueden ser modificados.');
        });

        static::deleting(function () {
            throw new \DomainException('Los movimientos de caja son inmutables y no pueden ser eliminados.');
        });
    }

    protected $fillable = [
        'sesion_caja_id',
        'user_id',
        'tipo',
        'monto',
        'concepto',
        'comprobante_referencia',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
    ];

    public function sesionCaja(): BelongsTo
    {
        return $this->belongsTo(SesionCaja::class, 'sesion_caja_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeIngresos($query)
    {
        return $query->where('tipo', 'ingreso');
    }

    public function scopeEgresos($query)
    {
        return $query->where('tipo', 'egreso');
    }
}
