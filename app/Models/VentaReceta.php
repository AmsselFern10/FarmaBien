<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VentaReceta extends Model
{
    protected $table = 'venta_receta';

    protected $fillable = [
        'venta_id',
        'receta_id',
    ];

    // Relaciones
    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class);
    }

    public function receta(): BelongsTo
    {
        return $this->belongsTo(Receta::class);
    }
}