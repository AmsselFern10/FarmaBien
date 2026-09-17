<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Laboratorio extends Model
{
    protected $table = 'laboratorios';

    protected $fillable = [
        'nombre',
        'codigo',
        'contacto',
        'telefono',
        'email',
        'pais_origen',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class);
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}
