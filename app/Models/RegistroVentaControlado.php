<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistroVentaControlado extends Model
{
    protected $table = 'registros_venta_controlados';

    // Constantes de Tipos de Movimientos Fiscalizados MINSA
    public const TIPO_VENTA = 'VENTA';
    public const TIPO_DEVOLUCION_STOCK = 'DEVOLUCION_STOCK';
    public const TIPO_DEVOLUCION_MERMA = 'DEVOLUCION_MERMA';
    public const TIPO_AJUSTE_INGRESO = 'AJUSTE_INGRESO';
    public const TIPO_AJUSTE_EGRESO = 'AJUSTE_EGRESO';
    public const TIPO_ANULACION_VENTA = 'ANULACION_VENTA';
    public const TIPO_ANULACION_COMPRA = 'ANULACION_COMPRA';
    public const TIPO_COMPRA = 'COMPRA';

    protected $fillable = [
        'tipo_movimiento',
        'venta_id',
        'devolucion_id',
        'compra_id',
        'movimiento_inventario_id',
        'producto_id',
        'lote_id',
        'nivel_controlado',
        'paciente_nombre',
        'paciente_cedula',
        'paciente_edad',
        'medico_nombre',
        'medico_cedula',
        'medico_num_registro',
        'motivo_omision',
        'ruta_foto_receta',
        'diagnostico',
        'cantidad',
        'unidad',
        'user_id',
    ];

    protected $casts = [
        'nivel_controlado' => 'integer',
        'paciente_edad'    => 'integer',
        'cantidad'         => 'decimal:2',
    ];

    /**
     * El Libro Oficial MINSA es un registro contable-sanitario strictly Append-Only.
     * Queda prohibido eliminar asientos físicos o mutar sus importes, referencias o entidades.
     */
    protected static function booted(): void
    {
        static::created(function () {
            \App\Support\RequestCache::forgetPrefixStatic('controlados:');
            \App\Support\RequestCache::forgetPrefixStatic('minsa:');
        });

        static::saved(function () {
            \App\Support\RequestCache::forgetPrefixStatic('controlados:');
            \App\Support\RequestCache::forgetPrefixStatic('minsa:');
        });

        static::updating(function ($registro) {
            $inmutables = [
                'tipo_movimiento',
                'venta_id',
                'devolucion_id',
                'compra_id',
                'movimiento_inventario_id',
                'producto_id',
                'lote_id',
                'cantidad',
                'unidad',
                'user_id',
            ];

            foreach ($inmutables as $campo) {
                if ($registro->isDirty($campo)) {
                    throw new \DomainException("No se permite modificar datos contables o fiscales de un asiento del Libro Oficial MINSA (campo '{$campo}'). Registre un contra-asiento de ajuste o anulación.");
                }
            }
        });

        static::deleting(function ($registro) {
            throw new \DomainException("Los asientos del Libro Oficial de Medicamentos Controlados (MINSA) son inmutables y no pueden ser eliminados.");
        });
    }

    // Relaciones
    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class);
    }

    public function devolucion(): BelongsTo
    {
        return $this->belongsTo(DevolucionVenta::class, 'devolucion_id');
    }

    public function compra(): BelongsTo
    {
        return $this->belongsTo(Compra::class, 'compra_id');
    }

    public function movimientoInventario(): BelongsTo
    {
        return $this->belongsTo(MovimientoInventario::class, 'movimiento_inventario_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    public function lote(): BelongsTo
    {
        return $this->belongsTo(Lote::class);
    }

    public function despachador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Helpers de Operación y Kardex
    public function esEntrada(): bool
    {
        return in_array($this->tipo_movimiento, [
            self::TIPO_DEVOLUCION_STOCK,
            self::TIPO_AJUSTE_INGRESO,
            self::TIPO_ANULACION_VENTA,
            self::TIPO_COMPRA,
        ], true);
    }

    public function esSalida(): bool
    {
        return in_array($this->tipo_movimiento, [
            self::TIPO_VENTA,
            self::TIPO_DEVOLUCION_MERMA,
            self::TIPO_AJUSTE_EGRESO,
            self::TIPO_ANULACION_COMPRA,
        ], true);
    }

    public function esMerma(): bool
    {
        return in_array($this->tipo_movimiento, [
            self::TIPO_DEVOLUCION_MERMA,
            self::TIPO_AJUSTE_EGRESO,
        ], true);
    }

    public function getTipoEtiquetaAttribute(): string
    {
        return match($this->tipo_movimiento) {
            self::TIPO_VENTA             => 'Venta / Despacho',
            self::TIPO_DEVOLUCION_STOCK  => 'Reingreso Devolución',
            self::TIPO_DEVOLUCION_MERMA  => 'Baja por Devolución (Merma)',
            self::TIPO_AJUSTE_INGRESO    => 'Ajuste Físico (+)',
            self::TIPO_AJUSTE_EGRESO     => 'Baja por Ajuste (-)',
            self::TIPO_ANULACION_VENTA   => 'Reingreso Anulación Venta',
            self::TIPO_ANULACION_COMPRA  => 'Reversión Anulación Compra',
            self::TIPO_COMPRA            => 'Ingreso por Compra',
            default                      => $this->tipo_movimiento ?: 'Despacho',
        };
    }

    public function getBadgeClassAttribute(): string
    {
        return match($this->tipo_movimiento) {
            self::TIPO_DEVOLUCION_STOCK,
            self::TIPO_AJUSTE_INGRESO,
            self::TIPO_ANULACION_VENTA,
            self::TIPO_COMPRA            => 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-950 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800',

            self::TIPO_DEVOLUCION_MERMA,
            self::TIPO_AJUSTE_EGRESO,
            self::TIPO_ANULACION_COMPRA  => 'bg-rose-50 dark:bg-rose-950/60 text-rose-950 dark:text-rose-300 border-rose-200 dark:border-rose-800',

            self::TIPO_VENTA             => 'bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-slate-200 border-slate-200 dark:border-slate-700',
            default                      => 'bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-slate-200 border-slate-200 dark:border-slate-700',
        };
    }

    public function getSignoAttribute(): string
    {
        return $this->esEntrada() ? '+' : '-';
    }

    public function esOmision(): bool
    {
        return !empty($this->motivo_omision) && $this->tipo_movimiento === self::TIPO_VENTA;
    }

    public function tieneRecetaAdjunta(): bool
    {
        return !empty($this->ruta_foto_receta);
    }

    // Scopes de Filtrado para Informes MINSA
    public function scopeTipoMovimiento($query, $tipo)
    {
        if ($tipo && $tipo !== 'todos') {
            if ($tipo === 'entradas') {
                $query->whereIn('tipo_movimiento', [
                    self::TIPO_DEVOLUCION_STOCK,
                    self::TIPO_AJUSTE_INGRESO,
                    self::TIPO_ANULACION_VENTA,
                    self::TIPO_COMPRA,
                ]);
            } elseif ($tipo === 'salidas') {
                $query->whereIn('tipo_movimiento', [
                    self::TIPO_VENTA,
                    self::TIPO_DEVOLUCION_MERMA,
                    self::TIPO_AJUSTE_EGRESO,
                ]);
            } elseif ($tipo === 'mermas') {
                $query->whereIn('tipo_movimiento', [
                    self::TIPO_DEVOLUCION_MERMA,
                    self::TIPO_AJUSTE_EGRESO,
                ]);
            } else {
                $query->where('tipo_movimiento', $tipo);
            }
        }
        return $query;
    }

    public function scopeFechaEntre($query, $desde, $hasta)
    {
        if ($desde) {
            $query->whereDate('created_at', '>=', $desde);
        }
        if ($hasta) {
            $query->whereDate('created_at', '<=', $hasta);
        }
        return $query;
    }

    public function scopeProducto($query, $productoId)
    {
        if ($productoId) {
            $query->where('producto_id', $productoId);
        }
        return $query;
    }

    public function scopeMedico($query, $medico)
    {
        if ($medico) {
            $query->where(function ($q) use ($medico) {
                $q->where('medico_nombre', 'like', "%{$medico}%")
                  ->orWhere('medico_num_registro', 'like', "%{$medico}%");
            });
        }
        return $query;
    }

    public function scopePaciente($query, $paciente)
    {
        if ($paciente) {
            $query->where(function ($q) use ($paciente) {
                $q->where('paciente_nombre', 'like', "%{$paciente}%")
                  ->orWhere('paciente_cedula', 'like', "%{$paciente}%");
            });
        }
        return $query;
    }

    public function scopeConOmision($query)
    {
        return $query->where('tipo_movimiento', self::TIPO_VENTA)
                     ->whereNotNull('motivo_omision')
                     ->where('motivo_omision', '!=', '');
    }

    public function scopeConReceta($query)
    {
        return $query->where('tipo_movimiento', self::TIPO_VENTA)
                     ->where(function ($q) {
                         $q->whereNull('motivo_omision')->orWhere('motivo_omision', '');
                     });
    }
}
