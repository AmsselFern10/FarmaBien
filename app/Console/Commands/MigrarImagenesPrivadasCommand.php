<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Migra las imágenes de productos del disco público (storage/app/public/productos/)
 * al disco privado (storage/app/images/productos/).
 * Actualiza la columna 'imagen' en la tabla 'productos' si cambia la ruta.
 */
class MigrarImagenesPrivadasCommand extends Command
{
    protected $signature   = 'imagenes:migrar {--dry-run : Solo mostrar qué se haría, sin mover archivos}';
    protected $description = 'Mueve las imágenes de productos del disco público al disco privado';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        $this->info($dryRun ? '🔍 DRY-RUN — no se mueven archivos' : '🚀 Iniciando migración de imágenes privadas...');

        $productos = DB::table('productos')
            ->whereNotNull('imagen')
            ->where('imagen', '!=', '')
            ->select('id', 'imagen')
            ->get();

        if ($productos->isEmpty()) {
            $this->info('No hay productos con imagen registrada.');
            return self::SUCCESS;
        }

        $migrated = 0;
        $skipped  = 0;
        $errors   = 0;

        $diskPublic  = Storage::disk('public');
        $diskPrivate = Storage::disk('private_images');

        foreach ($productos as $producto) {
            $path = $producto->imagen; // ej: "productos/abc123xyz.webp"

            // Ya está en el disco privado — no hacer nada
            if ($diskPrivate->exists($path)) {
                $this->line("  ✓ Ya migrado: {$path}");
                $skipped++;
                continue;
            }

            // Buscar en el disco público
            if (! $diskPublic->exists($path)) {
                $this->warn("  ⚠ No encontrado en público: {$path} (producto #{$producto->id})");
                $skipped++;
                continue;
            }

            if ($dryRun) {
                $this->line("  [DRY] Moverá: {$path}");
                $migrated++;
                continue;
            }

            try {
                // Leer contenido y escribir en disco privado
                $contents = $diskPublic->get($path);
                $diskPrivate->put($path, $contents);

                // Verificar que se escribió correctamente
                if (! $diskPrivate->exists($path)) {
                    throw new \RuntimeException("El archivo no existe en destino tras la escritura.");
                }

                // Eliminar original del disco público
                $diskPublic->delete($path);

                $migrated++;
                $this->line("  ✔ Migrado: {$path}");
            } catch (\Throwable $e) {
                $errors++;
                $this->error("  ✘ Error en {$path}: " . $e->getMessage());
            }
        }

        $this->newLine();
        $this->info("Completado: {$migrated} migradas | {$skipped} omitidas | {$errors} errores.");

        if ($errors > 0) {
            $this->warn('Revisa los errores antes de continuar.');
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
