<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $table = 'audit_logs';

    protected $fillable = [
        'user_id',
        'modulo',
        'accion',
        'descripcion',
        'detalles',
        'ip',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'detalles' => 'array',
        'created_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->created_at)) {
                $model->created_at = now();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Registrar un evento de auditoría de forma segura y no bloqueante
     */
    public static function log(string $modulo, string $accion, string $descripcion, ?array $detalles = null): ?self
    {
        try {
            return self::create([
                'user_id'     => auth()->id(),
                'modulo'      => \Illuminate\Support\Str::limit($modulo, 50, ''),
                'accion'      => \Illuminate\Support\Str::limit($accion, 50, ''),
                'descripcion' => \Illuminate\Support\Str::limit($descripcion, 250, '...'),
                'detalles'    => $detalles,
                'ip'          => request()->ip(),
                'user_agent'  => request()->userAgent(),
                'created_at'  => now(),
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Fallo al persistir AuditLog: {$e->getMessage()}");
            return null;
        }
    }
}
