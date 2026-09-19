<?php

namespace App\Listeners;

use App\Models\LoginLog;
use Illuminate\Auth\Events\Logout;

class RecordUserLogout
{
    public function handle(Logout $event): void
    {
        if ($event->user) {
            LoginLog::create([
                'user_id'    => $event->user->id,
                'tipo'       => 'logout',
                'ip'         => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
            ]);
        }
    }
}
