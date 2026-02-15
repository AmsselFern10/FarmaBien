<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Venta extends Model
{
    protected $table = 'ventas';

    protected $fillable = [
        'cliente_id',
        'user_id',
        'anulado_por',

        'subtotal_bruto',
        'descuento_porcentaje',
        'descuento_monto_total',
        'total',

        'metodo_pago',

        // Caja / pago
        'monto_recibido',
        'cambio',
        'referencia_pago',

        'estado',
        'fecha',
        'fecha_anulacion',
        'motivo_anulacion',
        'observaciones',

        // Trazabilidad de modificaciones
        'reemplazada_por',
        'venta_original_id',
    ];

    protected $casts = [
        'subtotal_bruto' => 'decimal:2',
        'descuento_porcentaje' => 'decimal:2',
        'descuento_monto_total' => 'decimal:2',
        'total' => 'decimal:2',

        'monto_recibido' => 'decimal:2',
        'cambio' => 'decimal:2',

        'fecha' => 'datetime',
        'fecha_anulacion' => 'datetime',
    ];

    /* =======================
     * Relaciones
     * ======================= */

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function anuladoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'anulado_por');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleVenta::class, 'venta_id');
    }

    public function recetas(): BelongsToMany
    {
        return $this->belongsToMany(Receta::class, 'venta_receta', 'venta_id', 'receta_id');
    }

    /* =======================
     * Trazabilidad
     * ======================= */

    public function ventaOriginal(): BelongsTo
    {
        return $this->belongsTo(Venta::class, 'venta_original_id');
    }

    public function reemplazadaPor(): BelongsTo
    {
        return $this->belongsTo(Venta::class, 'reemplazada_por');
    }

    public function historialModificaciones(): HasMany
    {
        return $this->hasMany(Venta::class, 'venta_original_id');
    }

    /* =======================
     * Helpers de estado
     * ======================= */

    public function puedeAnularse(): bool
    {
        return $this->estado === 'completada' && is_null($this->reemplazada_por);
    }

    public function puedeModificarse(): bool
    {
        return $this->puedeAnularse();
    }
}
