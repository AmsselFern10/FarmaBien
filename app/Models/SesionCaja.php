<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SesionCaja extends Model
{
    use HasFactory;

    protected $table = 'sesiones_caja';

    protected $fillable = [
        'caja_id',
        'user_id',
        'monto_inicial',
        'fecha_apertura',
        'observaciones_apertura',
        'fecha_cierre',
        'cerrado_por',
        'monto_final_efectivo',
        'monto_esperado_efectivo',
        'diferencia_efectivo',
        'total_ventas_efectivo',
        'total_ventas_tarjeta',
        'total_ventas_transferencia',
        'total_ventas_otros',
        'total_ventas',
        'total_ingresos_manuales',
        'total_egresos_manuales',
        'estado',
        'observaciones_cierre',
    ];

    protected $casts = [
        'monto_inicial'              => 'decimal:2',
        'monto_final_efectivo'        => 'decimal:2',
        'monto_esperado_efectivo'     => 'decimal:2',
        'diferencia_efectivo'         => 'decimal:2',
        'total_ventas_efectivo'       => 'decimal:2',
        'total_ventas_tarjeta'        => 'decimal:2',
        'total_ventas_transferencia'  => 'decimal:2',
        'total_ventas_otros'          => 'decimal:2',
        'total_ventas'                => 'decimal:2',
        'total_ingresos_manuales'     => 'decimal:2',
        'total_egresos_manuales'      => 'decimal:2',
        'fecha_apertura'              => 'datetime',
        'fecha_cierre'                => 'datetime',
    ];

    public function caja(): BelongsTo
    {
        return $this->belongsTo(Caja::class, 'caja_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function usuarioCierre(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cerrado_por');
    }

    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class, 'sesion_caja_id');
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoCaja::class, 'sesion_caja_id');
    }

    /**
     * Scope para sesiones abiertas
     */
    public function scopeAbiertas($query)
    {
        return $query->where('estado', 'abierta');
    }

    /**
     * Scope para sesiones cerradas
     */
    public function scopeCerradas($query)
    {
        return $query->where('estado', 'cerrada');
    }

    /**
     * Comprobar si la sesión está abierta
     */
    public function estaAbierta(): bool
    {
        return $this->estado === 'abierta';
    }

    /**
     * Calcular en tiempo real el efectivo esperado
     */
    public function getEfectivoEsperadoCalculadoAttribute(): float
    {
        return (float)$this->monto_inicial 
             + (float)$this->total_ventas_efectivo 
             + (float)$this->total_ingresos_manuales 
             - (float)$this->total_egresos_manuales;
    }
}
