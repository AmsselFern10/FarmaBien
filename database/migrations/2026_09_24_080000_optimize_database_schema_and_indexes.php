<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Helper to verify if an index exists in the current database driver.
     */
    protected function indexExists(string $table, string $indexName): bool
    {
        try {
            $driver = Schema::getConnection()->getDriverName();
            if ($driver === 'sqlite') {
                $indexes = DB::select("PRAGMA index_list('{$table}')");
                foreach ($indexes as $idx) {
                    if (($idx->name ?? null) === $indexName) {
                        return true;
                    }
                }
                return false;
            }

            $database = Schema::getConnection()->getDatabaseName();
            $count = DB::table('information_schema.statistics')
                ->where('table_schema', $database)
                ->where('table_name', $table)
                ->where('index_name', $indexName)
                ->count();
            return $count > 0;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Safely add an index only if table and columns exist, and index is not already present.
     */
    protected function safeAddIndex(string $table, $columns, string $indexName): void
    {
        if (!Schema::hasTable($table)) return;

        $cols = is_array($columns) ? $columns : [$columns];
        foreach ($cols as $col) {
            if (!Schema::hasColumn($table, $col)) return;
        }

        if (!$this->indexExists($table, $indexName)) {
            try {
                Schema::table($table, function (Blueprint $t) use ($columns, $indexName) {
                    $t->index($columns, $indexName);
                });
            } catch (\Throwable $e) {
                // Ignore if duplicate or already created
            }
        }
    }

    /**
     * Safely drop an index if table and index exist.
     */
    protected function safeDropIndex(string $table, string $indexName): void
    {
        if (!Schema::hasTable($table)) return;

        if ($this->indexExists($table, $indexName)) {
            try {
                Schema::table($table, function (Blueprint $t) use ($indexName) {
                    $t->dropIndex($indexName);
                });
            } catch (\Throwable $e) {
                // Ignore
            }
        }
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Índices para Auditoría y Logs de Acceso (login_logs)
        $this->safeAddIndex('login_logs', 'created_at', 'idx_login_logs_created_at');
        $this->safeAddIndex('login_logs', ['user_id', 'created_at'], 'idx_login_logs_user_date');
        $this->safeAddIndex('login_logs', ['tipo', 'created_at'], 'idx_login_logs_tipo_date');

        // 2. Índices para Compras y Adquisiciones (compras)
        $this->safeAddIndex('compras', ['estado', 'fecha'], 'idx_compras_estado_fecha');
        $this->safeAddIndex('compras', ['proveedor_id', 'estado'], 'idx_compras_prov_estado');
        $this->safeAddIndex('compras', ['user_id', 'fecha'], 'idx_compras_user_fecha');
        $this->safeAddIndex('compras', 'numero_comprobante', 'idx_compras_num_comprobante');

        // 3. Índices para Detalle de Compras (detalle_compra)
        $this->safeAddIndex('detalle_compra', ['compra_id', 'producto_id'], 'idx_det_compra_compra_prod');
        $this->safeAddIndex('detalle_compra', 'lote_id', 'idx_det_compra_lote');
        $this->safeAddIndex('detalle_compra', 'presentacion_id', 'idx_det_compra_pres');

        // 4. Índices para Clientes (clientes)
        $this->safeAddIndex('clientes', 'documento', 'idx_clientes_documento');
        $this->safeAddIndex('clientes', ['activo', 'documento'], 'idx_clientes_activo_doc');

        // 5. Índices para Proveedores (proveedores)
        $this->safeAddIndex('proveedores', 'ruc', 'idx_proveedores_ruc');
        $this->safeAddIndex('proveedores', ['activo', 'ruc'], 'idx_proveedores_activo_ruc');
        $this->safeAddIndex('proveedores', ['activo', 'nombre'], 'idx_proveedores_activo_nombre');

        // 6. Índices para Recetas Médicas (recetas)
        $this->safeAddIndex('recetas', 'medico_nombre', 'idx_recetas_medico_nombre');
        $this->safeAddIndex('recetas', 'medico_colegiatura', 'idx_recetas_medico_colegiatura');
        $this->safeAddIndex('recetas', 'paciente_nombre', 'idx_recetas_paciente_nombre');

        // 7. Índices para Control de Cajas (cajas, sesiones_caja, movimientos_caja)
        $this->safeAddIndex('cajas', 'activo', 'idx_cajas_activo_estado');
        $this->safeAddIndex('sesiones_caja', ['estado', 'fecha_apertura'], 'idx_sesiones_caja_estado_apertura');
        $this->safeAddIndex('movimientos_caja', ['sesion_caja_id', 'created_at'], 'idx_mov_caja_sesion_created');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->safeDropIndex('movimientos_caja', 'idx_mov_caja_sesion_created');
        $this->safeDropIndex('sesiones_caja', 'idx_sesiones_caja_estado_apertura');
        $this->safeDropIndex('cajas', 'idx_cajas_activo_estado');
        $this->safeDropIndex('recetas', 'idx_recetas_medico_nombre');
        $this->safeDropIndex('recetas', 'idx_recetas_medico_colegiatura');
        $this->safeDropIndex('recetas', 'idx_recetas_paciente_nombre');
        $this->safeDropIndex('proveedores', 'idx_proveedores_ruc');
        $this->safeDropIndex('proveedores', 'idx_proveedores_activo_ruc');
        $this->safeDropIndex('proveedores', 'idx_proveedores_activo_nombre');
        $this->safeDropIndex('clientes', 'idx_clientes_documento');
        $this->safeDropIndex('clientes', 'idx_clientes_activo_doc');
        $this->safeDropIndex('detalle_compra', 'idx_det_compra_compra_prod');
        $this->safeDropIndex('detalle_compra', 'idx_det_compra_lote');
        $this->safeDropIndex('detalle_compra', 'idx_det_compra_pres');
        $this->safeDropIndex('compras', 'idx_compras_estado_fecha');
        $this->safeDropIndex('compras', 'idx_compras_prov_estado');
        $this->safeDropIndex('compras', 'idx_compras_user_fecha');
        $this->safeDropIndex('compras', 'idx_compras_num_comprobante');
        $this->safeDropIndex('login_logs', 'idx_login_logs_created_at');
        $this->safeDropIndex('login_logs', 'idx_login_logs_user_date');
        $this->safeDropIndex('login_logs', 'idx_login_logs_tipo_date');
    }
};
