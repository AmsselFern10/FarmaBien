<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Receta extends Model
{
    protected $table = 'recetas';

    protected $fillable = [
        'cliente_id',
        'paciente_nombre',
        'paciente_documento',
        'paciente_edad',
        'medico_nombre',
        'medico_colegiatura',
        'medico_especialidad',
        'institucion_salud',
        'numero_receta',
        'fecha_emision',
        'fecha_vencimiento',
        'tipo_receta',
        'archivo_receta',
        'estado',
        'observaciones',
    ];

    protected $casts = [
        'paciente_edad' => 'integer',
        'fecha_emision' => 'date',
        'fecha_vencimiento' => 'date',
    ];

    // Relaciones
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(RecetaDetalle::class);
    }

    public function ventas(): BelongsToMany
    {
        return $this->belongsToMany(Venta::class, 'venta_receta')->withTimestamps();
    }

    // Scopes
    public function scopePendientes($query)
    {
        return $query->whereIn('estado', ['pendiente', 'dispensada_parcial']);
    }

    public function scopeRetenidas($query)
    {
        return $query->where('tipo_receta', 'retenida');
    }

    public function scopeVigentes($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('fecha_vencimiento')
              ->orWhere('fecha_vencimiento', '>=', now()->toDateString());
        });
    }

    // Métodos
    public function estaVencida(): bool
    {
        return $this->fecha_vencimiento && $this->fecha_vencimiento < now()->toDateString();
    }
}
