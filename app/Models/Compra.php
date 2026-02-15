<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Compra extends Model
{
    protected $fillable = [
        'proveedor_id',
        'user_id',
        'anulado_por',
        'reemplazada_por',
        'compra_original_id',

        // Totales (nuevo esquema)
        'subtotal_bruto',
        'descuento_porcentaje',
        'descuento_monto_total',
        'total',

        'observaciones',

        'estado',
        'fecha',
        'fecha_anulacion',
        'motivo_anulacion',
    ];

    protected $casts = [
        'subtotal_bruto' => 'decimal:2',
        'descuento_porcentaje' => 'decimal:2',
        'descuento_monto_total' => 'decimal:2',
        'total' => 'decimal:2',

        'fecha' => 'datetime',
        'fecha_anulacion' => 'datetime',
    ];

    /**
     * Alias de compatibilidad:
     * En formularios el campo suele llamarse "descuento" (porcentaje global).
     */
    public function setDescuentoAttribute($value): void
    {
        $this->attributes['descuento_porcentaje'] = $value;
    }

    public function getDescuentoAttribute()
    {
        return $this->descuento_porcentaje;
    }

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class);
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
        return $this->hasMany(DetalleCompra::class);
    }

    public function lotes(): HasMany
    {
        return $this->hasMany(Lote::class);
    }

    public function compraOriginal(): BelongsTo
    {
        return $this->belongsTo(Compra::class, 'compra_original_id');
    }

    public function reemplazadaPor(): BelongsTo
    {
        return $this->belongsTo(Compra::class, 'reemplazada_por');
    }

    public function puedeAnularse(): bool
    {
        return $this->estado === 'recibida' && is_null($this->reemplazada_por);
    }

    public function puedeModificarse(): bool
    {
        return $this->estado === 'recibida' && is_null($this->reemplazada_por);
    }

    public function esModificacion(): bool
    {
        return !is_null($this->compra_original_id);
    }

    public function fueModificada(): bool
    {
        return !is_null($this->reemplazada_por);
    }
    public function cadenaModificaciones(): Collection
{
    // 1) Subir hasta la compra raíz (la original)
    $root = $this;

    while (!is_null($root->compra_original_id)) {
        $root->loadMissing('compraOriginal');
        $root = $root->compraOriginal;

        // Seguridad por si hay datos dañados
        if (!$root) {
            return collect([$this]);
        }
    }

    // 2) Bajar por la cadena usando reemplazada_por (versiones sucesivas)
    $cadena = collect([$root]);
    $actual = $root;

    while (!is_null($actual->reemplazada_por)) {
        $actual->loadMissing('reemplazadaPor');
        $siguiente = $actual->reemplazadaPor;

        if (!$siguiente) break;

        $cadena->push($siguiente);
        $actual = $siguiente;
    }

    return $cadena->unique('id')->values();
}
}
