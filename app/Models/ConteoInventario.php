<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConteoInventario extends Model
{
    protected $table = 'conteos_inventario';

    protected $fillable = [
         'nombre', 'estado', 'notas', 'usuario_id', 'aprobado_por_id',
        'total_lotes', 'lotes_contados', 'diferencia_total_unidades',
        'iniciado_en', 'completado_en',
    ];

    protected $casts = [
        'total_lotes'               => 'integer',
        'lotes_contados'            => 'integer',
        'diferencia_total_unidades' => 'integer',
        'iniciado_en'               => 'datetime',
        'completado_en'             => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function aprobadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'aprobado_por_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleConteo::class, 'conteo_id');
    }

    public function estaEnProceso(): bool
    {
        return $this->estado === 'en_proceso';
    }

    public function estaCompletado(): bool
    {
        return $this->estado === 'completado';
    }

    public function scopeBorrador($query)
    {
        return $query->where('estado', 'borrador');
    }

    public function scopeEnProceso($query)
    {
        return $query->where('estado', 'en_proceso');
    }
}
