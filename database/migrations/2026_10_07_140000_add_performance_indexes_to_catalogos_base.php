<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Añade índices de rendimiento compuestos y específicos para los 4 catálogos base:
     * - Clientes (activo + nombre, documento)
     * - Proveedores (activo + nombre, ruc)
     * - Categorías (activo + nombre)
     * - Laboratorios (activo + nombre, codigo)
     */
    public function up(): void
    {
        // 1. Clientes
        if (Schema::hasTable('clientes')) {
            Schema::table('clientes', function (Blueprint $table) {
                if (!$this->indexExists('clientes', 'idx_clientes_activo_nombre')) {
                    $table->index(['activo', 'nombre'], 'idx_clientes_activo_nombre');
                }
                if (!$this->indexExists('clientes', 'idx_clientes_documento') && !$this->indexExists('clientes', 'clientes_documento_index')) {
                    $table->index('documento', 'idx_clientes_documento');
                }
            });
        }

        // 2. Proveedores
        if (Schema::hasTable('proveedores')) {
            Schema::table('proveedores', function (Blueprint $table) {
                if (!$this->indexExists('proveedores', 'idx_proveedores_activo_nombre')) {
                    $table->index(['activo', 'nombre'], 'idx_proveedores_activo_nombre');
                }
                if (!$this->indexExists('proveedores', 'idx_proveedores_ruc') && !$this->indexExists('proveedores', 'proveedores_ruc_unique')) {
                    $table->index('ruc', 'idx_proveedores_ruc');
                }
            });
        }

        // 3. Categorías
        if (Schema::hasTable('categorias')) {
            Schema::table('categorias', function (Blueprint $table) {
                if (!$this->indexExists('categorias', 'idx_categorias_activo_nombre') && !$this->indexExists('categorias', 'categorias_activo_nombre_index')) {
                    $table->index(['activo', 'nombre'], 'idx_categorias_activo_nombre');
                }
            });
        }

        // 4. Laboratorios
        if (Schema::hasTable('laboratorios')) {
            Schema::table('laboratorios', function (Blueprint $table) {
                if (!$this->indexExists('laboratorios', 'idx_laboratorios_activo_nombre')) {
                    $table->index(['activo', 'nombre'], 'idx_laboratorios_activo_nombre');
                }
                if (!$this->indexExists('laboratorios', 'idx_laboratorios_codigo') && !$this->indexExists('laboratorios', 'laboratorios_codigo_unique')) {
                    $table->index('codigo', 'idx_laboratorios_codigo');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $drops = [
            'clientes'     => ['idx_clientes_activo_nombre', 'idx_clientes_documento'],
            'proveedores'  => ['idx_proveedores_activo_nombre', 'idx_proveedores_ruc'],
            'categorias'   => ['idx_categorias_activo_nombre'],
            'laboratorios' => ['idx_laboratorios_activo_nombre', 'idx_laboratorios_codigo'],
        ];

        foreach ($drops as $table => $indexes) {
            if (!Schema::hasTable($table)) {
                continue;
            }
            Schema::table($table, function (Blueprint $blueprint) use ($table, $indexes) {
                foreach ($indexes as $idx) {
                    if ($this->indexExists($table, $idx)) {
                        $blueprint->dropIndex($idx);
                    }
                }
            });
        }
    }

    /**
     * Verifica si un índice existe por nombre de forma agnóstica al motor de BD.
     */
    private function indexExists(string $table, string $indexName): bool
    {
        try {
            $driver = DB::getDriverName();
            if ($driver === 'sqlite') {
                $indexes = DB::select("PRAGMA index_list('{$table}')");
                foreach ($indexes as $idx) {
                    $name = is_array($idx) ? ($idx['name'] ?? '') : ($idx->name ?? '');
                    if ($name === $indexName) {
                        return true;
                    }
                }
                return false;
            }

            if ($driver === 'mysql') {
                $indexes = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
                return !empty($indexes);
            }

            if ($driver === 'pgsql') {
                $indexes = DB::select("SELECT indexname FROM pg_indexes WHERE tablename = ? AND indexname = ?", [$table, $indexName]);
                return !empty($indexes);
            }

            return false;
        } catch (\Throwable $e) {
            return false;
        }
    }
};
