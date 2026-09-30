<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RegistroVentaControlado extends Model
{
    protected $table = 'registros_venta_controlados';

    protected $fillable = [
        'venta_id',
        'producto_id',
        'lote_id',
        'nivel_controlado',
        'paciente_nombre',
        'paciente_cedula',
        'paciente_edad',
        'medico_nombre',
        'medico_cedula',
        'medico_num_registro',
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

    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class);
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

    /** Etiqueta legible del nivel de control */
    public function nivelLabel(): string
    {
        return match ((int) $this->nivel_controlado) {
            1 => 'Nivel I — Control Básico',
            2 => 'Nivel II — Opioide',
            3 => 'Nivel III — Narcótico',
            default => 'Sin control',
        };
    }

    /** Color Tailwind para el badge */
    public function nivelColor(): string
    {
        return match ((int) $this->nivel_controlado) {
            1 => 'amber',
            2 => 'orange',
            3 => 'rose',
            default => 'slate',
        };
    }
}
