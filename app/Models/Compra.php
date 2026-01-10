<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Compra extends Model
{
    protected $fillable = [
        'proveedor_id',
        'user_id',
        'anulado_por',
        'reemplazada_por',        // ✅ NUEVO
        'compra_original_id',     // ✅ NUEVO
        'total',
        'estado',
        'fecha',
        'fecha_anulacion',
        'motivo_anulacion',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'fecha' => 'datetime',
        'fecha_anulacion' => 'datetime',
    ];

    // Relaciones existentes
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

    // ✅ NUEVAS RELACIONES
    
    public function compraOriginal(): BelongsTo
    {
        return $this->belongsTo(Compra::class, 'compra_original_id');
    }

    public function reemplazadaPor(): BelongsTo
    {
        return $this->belongsTo(Compra::class, 'reemplazada_por');
    }

    public function historialModificaciones(): HasMany
    {
        return $this->hasMany(Compra::class, 'compra_original_id');
    }

    // Scopes
    public function scopeRecibidas($query)
    {
        return $query->where('estado', 'recibida');
    }

    public function scopeAnuladas($query)
    {
        return $query->where('estado', 'anulada');
    }

    public function scopeOriginales($query)
    {
        return $query->whereNull('compra_original_id');
    }

    public function scopeModificaciones($query)
    {
        return $query->whereNotNull('compra_original_id');
    }

    public function scopeActivas($query)
    {
        return $query->where('estado', 'recibida')
            ->whereNull('reemplazada_por');
    }

    // ✅ NUEVOS MÉTODOS
    
    public function puedeAnularse(): bool
    {
        return $this->estado === 'recibida' 
            && is_null($this->reemplazada_por);
    }

    public function puedeModificarse(): bool
    {
        return $this->estado === 'recibida' 
            && is_null($this->reemplazada_por);
    }

    public function esModificacion(): bool
    {
        return !is_null($this->compra_original_id);
    }

    public function fueModificada(): bool
    {
        return !is_null($this->reemplazada_por);
    }

    public function compraActiva()
    {
        $compra = $this;
        
        while ($compra->reemplazada_por) {
            $compra = $compra->reemplazadaPor;
        }
        
        return $compra;
    }

    public function cadenaModificaciones()
    {
        $cadena = collect([$this]);
        $compra = $this;
        
        while ($compra->compraOriginal) {
            $compra = $compra->compraOriginal;
            $cadena->prepend($compra);
        }
        
        $compra = $this;
        while ($compra->reemplazadaPor) {
            $compra = $compra->reemplazadaPor;
            $cadena->push($compra);
        }
        
        return $cadena->unique('id')->values();
    }
}