<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LoginLog;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PurgeLogsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'farma:purge-logs 
                            {--days=30 : Antigüedad en días para depurar los logs (default: 30)}
                            {--force : Ejecutar sin solicitar confirmación interactiva}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Depurar y archivar registros de logs de auditoría y accesos con antigüedad superior a N días para mantener la base de datos optimizada.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');
        if ($days <= 0) {
            $days = 30;
        }

        $cutoffDate = Carbon::now()->subDays($days);

        $this->info("Iniciando depuración de logs de auditoría anteriores a: {$cutoffDate->toDateTimeString()} ({$days} días)...");

        // 1. Depuración de login_logs
        $totalLoginLogs = LoginLog::where('created_at', '<', $cutoffDate)->count();

        if ($totalLoginLogs === 0) {
            $this->line("• No se encontraron registros de accesos (login_logs) para purgar.");
        } else {
            $this->comment("• Se encontraron {$totalLoginLogs} registros en 'login_logs' para purgar.");

            if (!$this->option('force') && !$this->confirm("¿Desea eliminar estos {$totalLoginLogs} registros permanentemente?", true)) {
                $this->warn("Operación cancelada por el usuario.");
                return self::SUCCESS;
            }

            // Eliminación por lotes para evitar bloqueos prolongados en tablas MySQL de alta concurrencia
            $deleted = 0;
            $batchSize = 1000;

            do {
                $batchDeleted = LoginLog::where('created_at', '<', $cutoffDate)
                    ->limit($batchSize)
                    ->delete();
                $deleted += $batchDeleted;
            } while ($batchDeleted > 0);

            $this->info("✓ Se purgaron exitosamente {$deleted} registros de 'login_logs'.");
            Log::info("Mantenimiento DBA: Purgados {$deleted} registros de login_logs anteriores a {$cutoffDate->toDateString()}");
        }

        // 2. Depuración de audit_logs (Eventos de sistema, catálogo y errores)
        $totalAuditLogs = \App\Models\AuditLog::where('created_at', '<', $cutoffDate)->count();

        if ($totalAuditLogs === 0) {
            $this->line("• No se encontraron registros de auditoría de sistema (audit_logs) para purgar.");
        } else {
            $this->comment("• Se encontraron {$totalAuditLogs} registros en 'audit_logs' para purgar.");

            $deletedAudit = 0;
            $batchSize = 1000;

            do {
                $batchDeleted = \App\Models\AuditLog::where('created_at', '<', $cutoffDate)
                    ->limit($batchSize)
                    ->delete();
                $deletedAudit += $batchDeleted;
            } while ($batchDeleted > 0);

            $this->info("✓ Se purgaron exitosamente {$deletedAudit} registros de 'audit_logs'.");
            Log::info("Mantenimiento DBA: Purgados {$deletedAudit} registros de audit_logs anteriores a {$cutoffDate->toDateString()}");
        }

        // 3. Optimización de tablas (MySQL OPTIMIZE TABLE / SQLite VACUUM)
        $driver = DB::connection()->getDriverName();
        if ($driver === 'mysql') {
            try {
                $this->info("Ejecutando desfragmentación y optimización de espacio en tablas MySQL...");
                DB::statement("OPTIMIZE TABLE login_logs, audit_logs");
                $this->info("✓ Tablas 'login_logs' y 'audit_logs' desfragmentadas y optimizadas.");
            } catch (\Exception $e) {
                $this->warn("Aviso de optimización: " . $e->getMessage());
            }
        }

        $this->info("Mantenimiento y depuración de logs completada exitosamente.");
        return self::SUCCESS;
    }
}
