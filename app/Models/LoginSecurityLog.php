<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginSecurityLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'ip',
        'email',
        'user_agent',
        'evento',
        'intentos_acumulados',
        'bloqueado_hasta',
        'created_at',
    ];

    protected $casts = [
        'created_at'     => 'datetime',
        'bloqueado_hasta' => 'datetime',
    ];
}
