<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetalleConteo extends Model
{
    protected $table = 'detalles_conteo';

    protected $fillable = [
        'conteo_id', 'lote_id', 'producto_id',
        'stock_sistema', 'stock_fisico', 'diferencia', 'ajustado',
    ];

    protected $casts = [
        'stock_sistema' => 'integer',
        'stock_fisico'  => 'integer',
        'diferencia'    => 'integer',
        'ajustado'      => 'boolean',
    ];

    public function conteo(): BelongsTo
    {
        return $this->belongsTo(ConteoInventario::class, 'conteo_id');
    }

    public function lote(): BelongsTo
    {
        return $this->belongsTo(Lote::class);
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    public function tieneDiferencia(): bool
    {
        return $this->stock_fisico !== null && $this->diferencia !== 0;
    }
}
