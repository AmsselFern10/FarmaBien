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
    ];

    protected $casts = [
        'fecha_emision' => 'date',
        'fecha_esperada_entrega' => 'date',
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

    public function compra(): BelongsTo
    {
        return $this->belongsTo(Compra::class, 'compra_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleOrdenCompra::class, 'orden_compra_id');
    }
}
