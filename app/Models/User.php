<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'active' => 'boolean',
    ];

    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class);
    }

    public function compras(): HasMany
    {
        return $this->hasMany(Compra::class);
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoInventario::class);
    }

    
    public function loginLogs(): HasMany
    {
        return $this->hasMany(LoginLog::class)->orderByDesc('created_at');
    }

    public function sesionesCaja(): HasMany
    {
        return $this->hasMany(SesionCaja::class, 'user_id');
    }

    public function movimientosCaja(): HasMany
    {
        return $this->hasMany(MovimientoCaja::class, 'user_id');
    }

    /**
     * Obtener la sesión de caja actualmente abierta para este usuario
     */
    public function sesionCajaActiva()
    {
        return $this->sesionesCaja()->where('estado', 'abierta')->with('caja')->first();
    }

    public function scopeActivos($query)
    {
        return $query->where('active', true);
    }
}

