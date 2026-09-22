<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Venta extends Model
{
    protected $table = 'ventas';

    protected $fillable = [
        'cliente_id',
        'user_id',
        'sesion_caja_id',
        'tipo_comprobante',
        'serie',
        'numero_comprobante',
        'subtotal',
        'descuento',
        'impuesto',
        'total',
        'metodo_pago',
        'estado',
        'fecha',
        'fecha_anulacion',
        'anulado_por',
        'motivo_anulacion',
        'venta_original_id',
        'reemplazada_por',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'descuento' => 'decimal:2',
        'impuesto' => 'decimal:2',
        'total' => 'decimal:2',
        'fecha' => 'datetime',
        'fecha_anulacion' => 'datetime',
    ];

    // Relaciones
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function sesionCaja(): BelongsTo
    {
        return $this->belongsTo(SesionCaja::class, 'sesion_caja_id');
    }

    public function anuladoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'anulado_por');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleVenta::class);
    }

    public function recetas(): BelongsToMany
    {
        return $this->belongsToMany(Receta::class, 'venta_receta')
            ->withTimestamps();
    }

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

    // Scopes
    public function scopeCompletadas($query)
    {
        return $query->where('estado', 'completada');
    }

    public function scopeAnuladas($query)
    {
        return $query->where('estado', 'anulada');
    }

    public function scopeDelUsuario($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeOriginales($query)
    {
        return $query->whereNull('venta_original_id');
    }

    public function scopeModificaciones($query)
    {
        return $query->whereNotNull('venta_original_id');
    }

    public function scopeActivas($query)
    {
        return $query->where('estado', 'completada')
            ->whereNull('reemplazada_por');
    }

    // Métodos
    public function puedeAnularse(): bool
    {
        return $this->estado === 'completada' 
            && is_null($this->reemplazada_por);
    }

    public function puedeModificarse(): bool
    {
        return $this->estado === 'completada' 
            && is_null($this->reemplazada_por);
    }

    public function esModificacion(): bool
    {
        return !is_null($this->venta_original_id);
    }

    public function fueModificada(): bool
    {
        return !is_null($this->reemplazada_por);
    }

    public function ventaActiva()
    {
        $venta = $this;
        while ($venta->reemplazada_por) {
            $venta = $venta->reemplazadaPor;
        }
        return $venta;
    }

    public function cadenaModificaciones()
    {
        $cadena = collect([$this]);
        $venta = $this;
        
        while ($venta->ventaOriginal) {
            $venta = $venta->ventaOriginal;
            $cadena->prepend($venta);
        }
        
        $venta = $this;
        while ($venta->reemplazadaPor) {
            $venta = $venta->reemplazadaPor;
            $cadena->push($venta);
        }
        
        return $cadena->unique('id')->values();
    }
}
