<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Índices para Auditoría y Logs de Acceso (login_logs)
        if (Schema::hasTable('login_logs')) {
            Schema::table('login_logs', function (Blueprint $table) {
                $table->index('created_at', 'idx_login_logs_created_at');
                $table->index(['user_id', 'created_at'], 'idx_login_logs_user_date');
                $table->index(['tipo', 'created_at'], 'idx_login_logs_tipo_date');
            });
        }

        // 2. Índices para Compras y Adquisiciones (compras)
        if (Schema::hasTable('compras')) {
            Schema::table('compras', function (Blueprint $table) {
                $table->index(['estado', 'fecha_emision'], 'idx_compras_estado_fecha');
                $table->index(['proveedor_id', 'estado'], 'idx_compras_prov_estado');
                $table->index(['user_id', 'fecha_emision'], 'idx_compras_user_fecha');
                $table->index('numero_comprobante', 'idx_compras_num_comprobante');
            });
        }

        // 3. Índices para Detalle de Compras (detalle_compra)
        if (Schema::hasTable('detalle_compra')) {
            Schema::table('detalle_compra', function (Blueprint $table) {
                $table->index(['compra_id', 'producto_id'], 'idx_det_compra_compra_prod');
                $table->index('lote_id', 'idx_det_compra_lote');
                $table->index('presentacion_id', 'idx_det_compra_pres');
            });
        }

        // 4. Índices para Clientes (clientes)
        if (Schema::hasTable('clientes')) {
            Schema::table('clientes', function (Blueprint $table) {
                $table->index('documento', 'idx_clientes_documento');
                $table->index(['activo', 'documento'], 'idx_clientes_activo_doc');
            });
        }

        // 5. Índices para Proveedores (proveedores)
        if (Schema::hasTable('proveedores')) {
            Schema::table('proveedores', function (Blueprint $table) {
                $table->index('documento', 'idx_proveedores_documento');
                $table->index(['activo', 'documento'], 'idx_proveedores_activo_doc');
                $table->index(['activo', 'nombre'], 'idx_proveedores_activo_nombre');
            });
        }

        // 6. Índices para Recetas Médicas (recetas)
        if (Schema::hasTable('recetas')) {
            Schema::table('recetas', function (Blueprint $table) {
                $table->index('medico_nombre', 'idx_recetas_medico_nombre');
                $table->index('medico_colegiatura', 'idx_recetas_medico_colegiatura');
                $table->index('paciente_nombre', 'idx_recetas_paciente_nombre');
            });
        }

        // 7. Índices para Control de Cajas (cajas, sesiones_caja, movimientos_caja)
        if (Schema::hasTable('cajas')) {
            Schema::table('cajas', function (Blueprint $table) {
                $table->index(['activo', 'estado'], 'idx_cajas_activo_estado');
            });
        }

        if (Schema::hasTable('sesiones_caja')) {
            Schema::table('sesiones_caja', function (Blueprint $table) {
                $table->index(['estado', 'fecha_apertura'], 'idx_sesiones_caja_estado_apertura');
            });
        }

        if (Schema::hasTable('movimientos_caja')) {
            Schema::table('movimientos_caja', function (Blueprint $table) {
                $table->index(['sesion_caja_id', 'created_at'], 'idx_mov_caja_sesion_created');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('movimientos_caja')) {
            Schema::table('movimientos_caja', function (Blueprint $table) {
                $table->dropIndex('idx_mov_caja_sesion_created');
            });
        }

        if (Schema::hasTable('sesiones_caja')) {
            Schema::table('sesiones_caja', function (Blueprint $table) {
                $table->dropIndex('idx_sesiones_caja_estado_apertura');
            });
        }

        if (Schema::hasTable('cajas')) {
            Schema::table('cajas', function (Blueprint $table) {
                $table->dropIndex('idx_cajas_activo_estado');
            });
        }

        if (Schema::hasTable('recetas')) {
            Schema::table('recetas', function (Blueprint $table) {
                $table->dropIndex('idx_recetas_medico_nombre');
                $table->dropIndex('idx_recetas_medico_colegiatura');
                $table->dropIndex('idx_recetas_paciente_nombre');
            });
        }

        if (Schema::hasTable('proveedores')) {
            Schema::table('proveedores', function (Blueprint $table) {
                $table->dropIndex('idx_proveedores_documento');
                $table->dropIndex('idx_proveedores_activo_doc');
                $table->dropIndex('idx_proveedores_activo_nombre');
            });
        }

        if (Schema::hasTable('clientes')) {
            Schema::table('clientes', function (Blueprint $table) {
                $table->dropIndex('idx_clientes_documento');
                $table->dropIndex('idx_clientes_activo_doc');
            });
        }

        if (Schema::hasTable('detalle_compra')) {
            Schema::table('detalle_compra', function (Blueprint $table) {
                $table->dropIndex('idx_det_compra_compra_prod');
                $table->dropIndex('idx_det_compra_lote');
                $table->dropIndex('idx_det_compra_pres');
            });
        }

        if (Schema::hasTable('compras')) {
            Schema::table('compras', function (Blueprint $table) {
                $table->dropIndex('idx_compras_estado_fecha');
                $table->dropIndex('idx_compras_prov_estado');
                $table->dropIndex('idx_compras_user_fecha');
                $table->dropIndex('idx_compras_num_comprobante');
            });
        }

        if (Schema::hasTable('login_logs')) {
            Schema::table('login_logs', function (Blueprint $table) {
                $table->dropIndex('idx_login_logs_created_at');
                $table->dropIndex('idx_login_logs_user_date');
                $table->dropIndex('idx_login_logs_tipo_date');
            });
        }
    }
};
