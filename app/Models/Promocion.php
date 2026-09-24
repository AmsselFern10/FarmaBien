<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Promocion extends Model
{
    use SoftDeletes;

    protected $table = 'promociones';

    protected $fillable = [
        'nombre',
        'descripcion',
        'tipo',
        'valor',
        'alcance',
        'producto_id',
        'categoria_id',
        'laboratorio_id',
        'fecha_inicio',
        'fecha_fin',
        'min_unidades',
        'stock_limite',
        'stock_consumido',
        'activo',
    ];

    protected $casts = [
        'valor'           => 'decimal:2',
        'fecha_inicio'    => 'datetime',
        'fecha_fin'       => 'datetime',
        'min_unidades'    => 'integer',
        'stock_limite'    => 'integer',
        'stock_consumido' => 'integer',
        'activo'          => 'boolean',
    ];

    // Relaciones
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    public function laboratorio(): BelongsTo
    {
        return $this->belongsTo(Laboratorio::class);
    }

    // Scopes
    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    public function scopeVigentes($query)
    {
        $now = now();
        return $query->where('activo', true)
            ->where('fecha_inicio', '<=', $now)
            ->where('fecha_fin', '>=', $now)
            ->where(function ($q) {
                $q->whereNull('stock_limite')
                  ->orWhereRaw('stock_consumido < stock_limite');
            });
    }

    // Helpers de Negocio
    public function esVigente(): bool
    {
        if (!$this->activo) return false;
        $now = now();
        if ($this->fecha_inicio > $now || $this->fecha_fin < $now) return false;
        if ($this->stock_limite !== null && $this->stock_consumido >= $this->stock_limite) return false;
        return true;
    }

    /**
     * Calcula el monto total descontado para un ítem según precio y cantidad.
     */
    public function calcularDescuento(float $precioUnitario, int $cantidad = 1): float
    {
        if (!$this->esVigente() || $cantidad < $this->min_unidades) {
            return 0.00;
        }

        switch ($this->tipo) {
            case 'porcentaje':
                $pct = min(100, max(0, (float) $this->valor));
                return round(($precioUnitario * ($pct / 100)) * $cantidad, 2);

            case 'monto_fijo':
                $descuentoUnitario = min($precioUnitario, (float) $this->valor);
                return round($descuentoUnitario * $cantidad, 2);

            case '2x1':
                // Por cada 2 unidades, 1 es gratis
                $paresGratis = intdiv($cantidad, 2);
                return round($paresGratis * $precioUnitario, 2);

            case '3x2':
                // Por cada 3 unidades, 1 es gratis
                $triosGratis = intdiv($cantidad, 3);
                return round($triosGratis * $precioUnitario, 2);

            default:
                return 0.00;
        }
    }

    /**
     * Calcula el precio unitario resultante estimado tras aplicar la promoción.
     */
    public function calcularPrecioUnitario(float $precioOriginal): float
    {
        switch ($this->tipo) {
            case 'porcentaje':
                $pct = min(100, max(0, (float) $this->valor));
                return max(0, round($precioOriginal * (1 - ($pct / 100)), 2));

            case 'monto_fijo':
                return max(0, round($precioOriginal - (float) $this->valor, 2));

            case '2x1':
                return round($precioOriginal * 0.5, 2);

            case '3x2':
                return round($precioOriginal * (2 / 3), 2);

            default:
                return $precioOriginal;
        }
    }

    /**
     * Etiqueta de insignia visual atractiva para UI
     */
    public function getBadgeTextoAttribute(): string
    {
        switch ($this->tipo) {
            case 'porcentaje':
                return '-' . number_format($this->valor, 0) . '%';
            case 'monto_fijo':
                return '-$' . number_format($this->valor, 2);
            case '2x1':
                return '2x1';
            case '3x2':
                return '3x2';
            default:
                return 'PROMO';
        }
    }

    /**
     * Texto descriptivo del alcance para tablas y listados
     */
    public function getAlcanceDescripcionAttribute(): string
    {
        switch ($this->alcance) {
            case 'producto':
                return 'Producto: ' . ($this->producto->nombre ?? 'N/A');
            case 'categoria':
                return 'Categoría: ' . ($this->categoria->nombre ?? 'N/A');
            case 'laboratorio':
                return 'Laboratorio: ' . ($this->laboratorio->nombre ?? 'N/A');
            case 'general':
                return 'Todo el Catálogo';
            default:
                return ucfirst($this->alcance);
        }
    }
}
