<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Venta extends Model
{
    protected $fillable = [
        'cliente_id',
        'user_id',
        'anulado_por',
        'reemplazada_por',        // ✅ NUEVO
        'venta_original_id',      // ✅ NUEVO
        'total',
        'metodo_pago',
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
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
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
        return $this->hasMany(DetalleVenta::class);
    }

    public function recetas(): BelongsToMany
    {
        return $this->belongsToMany(Receta::class, 'venta_receta')
            ->withTimestamps();
    }

    // ✅ NUEVAS RELACIONES para modificaciones
    
    /**
     * Venta original (si esta venta es una modificación)
     */
    public function ventaOriginal(): BelongsTo
    {
        return $this->belongsTo(Venta::class, 'venta_original_id');
    }

    /**
     * Venta que reemplazó a esta (si fue modificada)
     */
    public function reemplazadaPor(): BelongsTo
    {
        return $this->belongsTo(Venta::class, 'reemplazada_por');
    }

    /**
     * Historial completo de modificaciones de esta venta
     */
    public function historialModificaciones(): HasMany
    {
        return $this->hasMany(Venta::class, 'venta_original_id');
    }

    // Scopes existentes
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

    // ✅ NUEVOS SCOPES
    
    /**
     * Ventas que son versiones originales (no son modificaciones)
     */
    public function scopeOriginales($query)
    {
        return $query->whereNull('venta_original_id');
    }

    /**
     * Ventas que son modificaciones de otras
     */
    public function scopeModificaciones($query)
    {
        return $query->whereNotNull('venta_original_id');
    }

    /**
     * Ventas activas (completadas y no reemplazadas)
     */
    public function scopeActivas($query)
    {
        return $query->where('estado', 'completada')
            ->whereNull('reemplazada_por');
    }

    // ✅ NUEVOS MÉTODOS
    
    /**
     * Verificar si puede ser anulada
     */
    public function puedeAnularse(): bool
    {
        return $this->estado === 'completada' 
            && is_null($this->reemplazada_por);
    }

    /**
     * Verificar si puede ser modificada
     */
    public function puedeModificarse(): bool
    {
        return $this->estado === 'completada' 
            && is_null($this->reemplazada_por);
    }

    /**
     * Verificar si es una modificación de otra venta
     */
    public function esModificacion(): bool
    {
        return !is_null($this->venta_original_id);
    }

    /**
     * Verificar si fue modificada (reemplazada por otra)
     */
    public function fueModificada(): bool
    {
        return !is_null($this->reemplazada_por);
    }

    /**
     * Obtener la venta activa final (siguiendo la cadena de modificaciones)
     */
    public function ventaActiva()
    {
        $venta = $this;
        
        while ($venta->reemplazada_por) {
            $venta = $venta->reemplazadaPor;
        }
        
        return $venta;
    }

    /**
     * Obtener toda la cadena de modificaciones
     */
    public function cadenaModificaciones()
    {
        $cadena = collect([$this]);
        $venta = $this;
        
        // Ir hacia atrás hasta la original
        while ($venta->ventaOriginal) {
            $venta = $venta->ventaOriginal;
            $cadena->prepend($venta);
        }
        
        // Ir hacia adelante hasta la última modificación
        $venta = $this;
        while ($venta->reemplazadaPor) {
            $venta = $venta->reemplazadaPor;
            $cadena->push($venta);
        }
        
        return $cadena->unique('id')->values();
    }
}