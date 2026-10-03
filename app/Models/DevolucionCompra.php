<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DevolucionCompra extends Model
{
    protected $table = 'devoluciones_compra';

    protected $fillable = [
        'numero_devolucion', 'proveedor_id', 'compra_id', 'usuario_id',
        'estado', 'motivo', 'total_devolucion', 'fecha_envio',
    ];

    protected $casts = [
        'total_devolucion' => 'decimal:2',
        'fecha_envio'      => 'datetime',
    ];

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function compra(): BelongsTo
    {
        return $this->belongsTo(Compra::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleDevolucionCompra::class, 'devolucion_compra_id');
    }

    public static function generarNumero(): string
    {
        $ultimo = static::max('id') ?? 0;
        return 'DEV-COMP-' . str_pad($ultimo + 1, 6, '0', STR_PAD_LEFT);
    }

    public function getEstadoBadgeAttribute(): string
    {
        return match($this->estado) {
            'pendiente'  => 'bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border-amber-200 dark:border-amber-800',
            'enviada'    => 'bg-blue-100 dark:bg-blue-950/60 text-blue-800 dark:text-blue-300 border-blue-200 dark:border-blue-800',
            'confirmada' => 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800',
            'rechazada'  => 'bg-rose-100 dark:bg-rose-950/60 text-rose-800 dark:text-rose-300 border-rose-200 dark:border-rose-800',
            default      => 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700',
        };
    }
}
