<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DevolucionVenta extends Model
{
    protected $table = 'devoluciones_ventas';

    protected $fillable = [
        'venta_id',
        'user_id',
        'sesion_caja_id',
        'numero_devolucion',
        'tipo',
        'motivo',
        'observaciones',
        'monto_total',
        'metodo_reembolso',
        'banco',
        'numero_transaccion',
        'estado',
        'fecha',
    ];

    protected $casts = [
        'monto_total' => 'decimal:2',
        'fecha' => 'datetime',
    ];

    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class, 'venta_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function sesionCaja(): BelongsTo
    {
        return $this->belongsTo(SesionCaja::class, 'sesion_caja_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleDevolucionVenta::class, 'devolucion_venta_id');
    }
}
