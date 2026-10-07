<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use DomainException;

class MovimientoInventario extends Model
{
    use HasFactory;

    protected $table = 'movimientos_inventario';

    /**
     * Blindaje de inmutabilidad: el Kardex es un registro de solo agregado (Append-Only).
     * No se permite actualizar ni eliminar movimientos existentes.
     */
    protected static function booted(): void
    {
        static::created(function () {
            \App\Support\RequestCache::forgetPrefixStatic('inventario:');
            \App\Support\RequestCache::forgetPrefixStatic('alertas:');
            \App\Support\RequestCache::forgetPrefixStatic('kardex:');
            \Illuminate\Support\Facades\Cache::forget('inventario_valorizacion');
            \App\Services\NotificacionService::clearCache();
        });

        static::updating(function () {
            throw new DomainException('Los registros de Kardex son inmutables y no pueden ser modificados.');
        });

        static::deleting(function () {
            throw new DomainException('Los registros de Kardex son inmutables y no pueden ser eliminados.');
        });
    }

    protected $fillable = [
        'producto_id',
        'lote_id',
        'user_id',
        'tipo',
        'subtipo',
        'cantidad',
        'stock_anterior',
        'stock_posterior',
        'costo_unitario',
        'costo_total',
        'origen',
        'origen_id',
        'motivo',
        'fecha_movimiento',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'stock_anterior' => 'integer',
        'stock_posterior' => 'integer',
        'costo_unitario' => 'decimal:2',
        'costo_total' => 'decimal:2',
        'fecha_movimiento' => 'datetime',
    ];

    // Relaciones
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    public function lote(): BelongsTo
    {
        return $this->belongsTo(Lote::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function registroVentaControlado(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(RegistroVentaControlado::class, 'movimiento_inventario_id');
    }

    // Scopes
    public function scopeEntradas($query)
    {
        return $query->where('tipo', 'entrada');
    }

    public function scopeSalidas($query)
    {
        return $query->where('tipo', 'salida');
    }

    public function scopeAjustes($query)
    {
        return $query->where('tipo', 'ajuste');
    }

    /**
     * Etiqueta formal en español para el subtipo de movimiento de Kardex
     */
    public function getSubtipoEtiquetaAttribute(): string
    {
        return match ($this->subtipo) {
            'compra'                 => 'Ingreso por Compra',
            'venta'                  => 'Despacho por Venta',
            'anulacion_venta'        => 'Anulación de Venta',
            'anulacion_compra'       => 'Reversión por Anulación de Compra',
            'devolucion_reingreso'   => 'Devolución de Cliente (Reingreso)',
            'devolucion_proveedor'   => 'Devolución a Proveedor',
            'merma_vencimiento'      => 'Merma por Vencimiento',
            'merma_danio'            => 'Merma por Daño / Descarte',
            'vencimiento_automatico' => 'Baja Automática por Vencimiento',
            'ajuste_manual'          => 'Ajuste Manual de Stock',
            'ajuste_toma'            => 'Ajuste por Toma de Inventario',
            'lote_manual'            => 'Stock Inicial / Lote Manual',
            default                  => ucfirst(str_replace('_', ' ', $this->subtipo ?? 'Movimiento')),
        };
    }
}
