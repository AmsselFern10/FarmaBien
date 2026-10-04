<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrdenCompra extends Model
{
    protected $table = 'ordenes_compras';

    protected $fillable = [
        'proveedor_id',
        'user_id',
        'numero_orden',
        'fecha_emision',
        'fecha_esperada_entrega',
        'estado',
        'condicion_pago',
        'dias_credito',
        'subtotal',
        'impuesto',
        'total',
        'observaciones',
        'compra_id',
        'cerrada_con_faltante',
        'faltante_unidades',
        'cerrada_por_id',
        'fecha_cierre',
        'motivo_faltante',
    ];

    protected $casts = [
        'fecha_emision' => 'date',
        'fecha_esperada_entrega' => 'date',
        'fecha_cierre' => 'datetime',
        'cerrada_con_faltante' => 'boolean',
        'faltante_unidades' => 'integer',
        'subtotal' => 'decimal:2',
        'impuesto' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function cerradaPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cerrada_por_id');
    }

    public function compra(): BelongsTo
    {
        return $this->belongsTo(Compra::class, 'compra_id');
    }

    public function compras(): HasMany
    {
        return $this->hasMany(Compra::class, 'orden_compra_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleOrdenCompra::class, 'orden_compra_id');
    }

    public function getPendienteTotalAttribute(): int
    {
        return (int) $this->detalles->sum(function ($det) {
            return max(0, (int)$det->cantidad_solicitada - (int)$det->cantidad_recibida);
        });
    }

    public function getLineasPendientesCountAttribute(): int
    {
        return (int) $this->detalles->filter(function ($det) {
            return ((int)$det->cantidad_solicitada - (int)$det->cantidad_recibida) > 0;
        })->count();
    }

    public function estaCompleta(): bool
    {
        return $this->estado === 'recibida_total';
    }

    public function estaPendiente(): bool
    {
        return in_array($this->estado, ['borrador', 'enviada']);
    }

    public function estaParcial(): bool
    {
        return $this->estado === 'recibida_parcial';
    }
}
