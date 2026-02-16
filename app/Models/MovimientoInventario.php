<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MovimientoInventario extends Model
{
    protected $table = 'movimientos_inventario';

    protected $fillable = [
        'uuid',
        'producto_id',
        'lote_id',
        'user_id',
        'tipo',
        'cantidad',
        'saldo_anterior',
        'saldo_nuevo',
        'reversa_de_id',
        'origen',
        'origen_id',
        'costo_unitario',
        'costo_total',
        'motivo',
        'fecha_movimiento',
    ];

    protected $casts = [
        'fecha_movimiento' => 'datetime',
        'costo_unitario' => 'decimal:6',
        'costo_total' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        /**
         * Reglas:
         * - Los movimientos deben crearse dentro de una transacción.
         * - Actualizan el saldo del lote (stock_actual) y guardan saldos en el movimiento.
         * - No se permite que el stock del lote quede negativo.
         * - Los movimientos NO se editan (solo se reversan).
         */
        static::creating(function (MovimientoInventario $mov) {
            if (DB::transactionLevel() === 0) {
                throw new Exception('Los movimientos de inventario deben crearse dentro de una transacción (DB::transaction).');
            }

            $mov->uuid = $mov->uuid ?: (string) Str::uuid();
            $mov->fecha_movimiento = $mov->fecha_movimiento ?: now();

            $mov->cantidad = self::normalizarCantidad($mov->tipo, $mov->cantidad);

            // Bloquea el lote para evitar condiciones de carrera.
            /** @var Lote $lote */
            $lote = Lote::whereKey($mov->lote_id)->lockForUpdate()->firstOrFail();

            // Forzamos consistencia: el producto del movimiento debe ser el del lote.
            $mov->producto_id = $lote->producto_id;

            $saldoAnterior = (int) $lote->stock_actual;
            $saldoNuevo = $saldoAnterior + (int) $mov->cantidad;

            if ($saldoNuevo < 0) {
                throw new Exception(
                    "Stock insuficiente en el lote {$lote->numero_lote}. " .
                    "Stock actual: {$saldoAnterior}, movimiento: {$mov->cantidad}"
                );
            }

            $mov->saldo_anterior = $saldoAnterior;
            $mov->saldo_nuevo = $saldoNuevo;

            // Costos (solo entradas normalmente)
            if ($mov->tipo === 'entrada' && $mov->costo_unitario !== null && $mov->costo_total === null) {
                $mov->costo_total = round(((float) $mov->costo_unitario) * abs((int) $mov->cantidad), 2);
            }

            // Actualiza el lote (saldo y acumulado de entradas)
            $lote->stock_actual = $saldoNuevo;

            if ($mov->tipo === 'entrada') {
                // stock_inicial: por compatibilidad, NO lo acumulamos aquí si ya viene definido (ej. desde CompraService).
                // Si está en 0 (lote creado sin stock_inicial), lo fijamos en la primera entrada.
                if (((int) $lote->stock_inicial) === 0) {
                    $lote->stock_inicial = abs((int) $mov->cantidad);
                }

                // Si es la primera entrada, fija fecha_ingreso si está vacío
                if (empty($lote->fecha_ingreso)) {
                    $lote->fecha_ingreso = now();
                }

                // Si el movimiento trae costo, puedes reflejarlo en el lote como costo unitario de referencia
                if ($mov->costo_unitario !== null) {
                    $lote->precio_compra = (float) $mov->costo_unitario;
                }
            }

            // Estado del lote por prioridad (bloqueado > vencido > agotado > activo)
            if (!is_null($lote->bloqueado_at)) {
                $lote->estado = 'bloqueado';
            } elseif ($lote->fecha_vencimiento && $lote->fecha_vencimiento->lt(today())) {
                $lote->estado = 'vencido';
            } elseif ($lote->stock_actual <= 0) {
                $lote->estado = 'agotado';
            } else {
                $lote->estado = 'activo';
            }

            $lote->save();
        });

        static::updating(function (MovimientoInventario $mov) {
            // No se permite editar movimientos (append-only). Para corregir, crea una reversa.
            $prohibidos = ['producto_id', 'lote_id', 'tipo', 'cantidad', 'saldo_anterior', 'saldo_nuevo', 'fecha_movimiento', 'costo_unitario', 'costo_total'];
            foreach ($prohibidos as $campo) {
                if ($mov->isDirty($campo)) {
                    throw new Exception('No se permite modificar un movimiento de inventario. Debes crear una reversa.');
                }
            }
        });

        static::deleting(function () {
            throw new Exception('No se permite eliminar movimientos de inventario (trazabilidad).');
        });
    }

    private static function normalizarCantidad(string $tipo, $cantidad): int
    {
        $cantidad = (int) $cantidad;

        return match ($tipo) {
            'entrada' => abs($cantidad),
            'salida' => -abs($cantidad),
            'ajuste' => $cantidad, // puede ser + o -
            default => $cantidad,
        };
    }

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

    public function reversaDe(): BelongsTo
    {
        return $this->belongsTo(MovimientoInventario::class, 'reversa_de_id');
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
}
