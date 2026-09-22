<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Caja extends Model
{
    use HasFactory;

    protected $table = 'cajas';

    protected $fillable = [
        'nombre',
        'codigo',
        'descripcion',
        'ubicacion',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function sesiones(): HasMany
    {
        return $this->hasMany(SesionCaja::class, 'caja_id');
    }

    /**
     * Sesión actualmente abierta para esta caja
     */
    public function sesionActiva(): HasOne
    {
        return $this->hasOne(SesionCaja::class, 'caja_id')->where('estado', 'abierta')->latestOfMany('fecha_apertura');
    }

    /**
     * Comprobar si la caja está actualmente abierta
     */
    public function estaAbierta(): bool
    {
        return $this->sesiones()->where('estado', 'abierta')->exists();
    }

    /**
     * Scope para cajas activas
     */
    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }
}
