<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Purgar logs de actividad y accesos de más de 30 días diariamente a las 02:00 AM
        $schedule->command('farma:purge-logs --days=30 --force')
            ->dailyAt('02:00')
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/db-maintenance.log'));
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
