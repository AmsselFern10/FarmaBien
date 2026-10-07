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
        'laboratorios_ids', 'categorias_ids', 'regimen_venta', 'alcance_resumen', 'idempotency_key',
        'total_lotes', 'lotes_contados', 'diferencia_total_unidades',
        'iniciado_en', 'completado_en',
    ];

    protected $casts = [
        'laboratorios_ids'          => 'array',
        'categorias_ids'            => 'array',
        'alcance_resumen'           => 'array',
        'total_lotes'               => 'integer',
        'lotes_contados'            => 'integer',
        'diferencia_total_unidades' => 'integer',
        'iniciado_en'               => 'datetime',
        'completado_en'             => 'datetime',
    ];

    /**
     * Auto-invalidación de cachés al guardar o modificar tomas de inventario
     */
    protected static function booted(): void
    {
        static::saved(function () {
            \App\Support\RequestCache::forgetPrefixStatic('inventario:');
            \App\Support\RequestCache::forgetPrefixStatic('conteos:');
            \App\Support\RequestCache::forgetPrefixStatic('alertas:');
            \Illuminate\Support\Facades\Cache::forget('inventario_valorizacion');
            \App\Services\NotificacionService::clearCache();
        });
    }

    /**
     * Devuelve los chips legibles del alcance para la vista
     */
    public function getAlcanceChipsAttribute(): array
    {
        if (!empty($this->alcance_resumen) && is_array($this->alcance_resumen)) {
            return $this->alcance_resumen;
        }

        $chips = [];
        if (empty($this->laboratorios_ids) && empty($this->categorias_ids) && ($this->regimen_venta === 'todos' || empty($this->regimen_venta))) {
            return ['Todo el inventario'];
        }

        if (!empty($this->laboratorios_ids) && is_array($this->laboratorios_ids)) {
            $labs = Laboratorio::whereIn('id', $this->laboratorios_ids)->pluck('nombre')->toArray();
            foreach ($labs as $lab) {
                $chips[] = "Lab: {$lab}";
            }
        }

        if (!empty($this->categorias_ids) && is_array($this->categorias_ids)) {
            $cats = Categoria::whereIn('id', $this->categorias_ids)->pluck('nombre')->toArray();
            foreach ($cats as $cat) {
                $chips[] = "Categoría: {$cat}";
            }
        }

        if ($this->regimen_venta === 'venta_libre') {
            $chips[] = 'Venta Libre';
        } elseif ($this->regimen_venta === 'controlados') {
            $chips[] = 'Controlados';
        }

        return !empty($chips) ? $chips : ['Todo el inventario'];
    }

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
