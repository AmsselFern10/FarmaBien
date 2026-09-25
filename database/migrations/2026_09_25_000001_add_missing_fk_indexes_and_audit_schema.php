<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // -------------------------------------------------------------------------
    // Helpers idempotentes (mismo patrón que migraciones previas del proyecto)
    // -------------------------------------------------------------------------

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
                // Ignorar si ya existe por FK automático u otra causa
            }
        }
    }

    protected function safeDropIndex(string $table, string $indexName): void
    {
        if (!Schema::hasTable($table)) return;

        if ($this->indexExists($table, $indexName)) {
            try {
                Schema::table($table, function (Blueprint $t) use ($indexName) {
                    $t->dropIndex($indexName);
                });
            } catch (\Throwable $e) {
                // Ignorar si ya fue eliminado
            }
        }
    }

    // -------------------------------------------------------------------------
    // up(): SOLO índices no cubiertos por ninguna migración anterior
    // -------------------------------------------------------------------------

    public function up(): void
    {
        /*
         * AUDITORÍA REALIZADA — Índices YA EXISTENTES (no se repiten aquí):
         * ────────────────────────────────────────────────────────────────────
         * productos   : [categoria_id,activo], [laboratorio_id,activo], nombre,
         *               principio_activo, tipo_control, [activo,nombre],
         *               [requiere_receta,activo]
         * lotes       : [producto_id,activo,fecha_vencimiento],
         *               [producto_id,stock_actual], numero_lote,
         *               compra_id, proveedor_id, [activo,stock_actual,fecha_vencimiento]
         * ventas      : [fecha,estado], [user_id,fecha], cliente_id,
         *               numero_comprobante, metodo_pago, tipo_comprobante,
         *               sesion_caja_id, [estado,fecha]
         * detalle_venta: [venta_id,producto_id], lote_id, receta_detalle_id
         * detalle_compra: [compra_id,producto_id], lote_id, presentacion_id
         * compras     : [proveedor_id,fecha], estado, [estado,fecha],
         *               [proveedor_id,estado], [user_id,fecha], numero_comprobante
         * recetas     : cliente_id, numero_receta, fecha_emision, estado,
         *               tipo_receta, [estado,fecha_emision], [cliente_id,estado],
         *               medico_nombre, medico_colegiatura, paciente_nombre
         * receta_detalles: [receta_id,producto_id]
         * movimientos_inventario: [producto_id,fecha_movimiento],
         *               [lote_id,fecha_movimiento], [tipo,subtipo],
         *               [origen,origen_id], [producto_id,tipo,fecha_movimiento]
         * sesiones_caja: [caja_id,estado], [user_id,estado], fecha_apertura,
         *               [estado,fecha_apertura]
         * movimientos_caja: [sesion_caja_id,tipo], [sesion_caja_id,created_at]
         * login_logs  : created_at, [user_id,created_at], [tipo,created_at]
         * clientes    : [activo,nombre], telefono, documento, [activo,documento]
         * proveedores : ruc, [activo,ruc], [activo,nombre]
         * presentaciones_producto: [producto_id,activo], codigo_barras
         * promociones : [activo,fecha_inicio,fecha_fin], [alcance,activo],
         *               producto_id, categoria_id, laboratorio_id
         *   (FK auto-indexes: producto_id, categoria_id, laboratorio_id ya existen)
         * venta_receta: unique([venta_id,receta_id])  → cubre búsquedas por venta_id
         * ────────────────────────────────────────────────────────────────────
         *
         * ÍNDICES GENUINAMENTE FALTANTES (los que se agregan a continuación):
         */

        // ── 1. detalle_venta ──────────────────────────────────────────────────
        // presentacion_id: el FK crea índice automático con nombre largo
        // (_foreign), pero necesitamos uno nombrado para referenciarlo en EXPLAIN.
        // El safeAddIndex no lo duplicará si ya existe bajo cualquier nombre.
        $this->safeAddIndex('detalle_venta', 'presentacion_id',          'idx_det_venta_pres_id');

        // Composite para reportes de ventas filtradas por producto+lote
        $this->safeAddIndex('detalle_venta', ['producto_id', 'lote_id'], 'idx_det_venta_prod_lote');

        // Composite para JOIN venta→detalle→lote en kardex de ventas
        $this->safeAddIndex('detalle_venta', ['venta_id', 'lote_id'],    'idx_det_venta_venta_lote');

        // ── 2. venta_receta ────────────────────────────────────────────────────
        // receta_id simple para búsqueda inversa (receta → ventas)
        $this->safeAddIndex('venta_receta', 'receta_id', 'idx_venta_receta_receta_id');

        // ── 3. receta_detalles ─────────────────────────────────────────────────
        // producto_id solo para buscar en qué recetas aparece un producto
        $this->safeAddIndex('receta_detalles', 'producto_id', 'idx_receta_det_producto_id');

        // ── 4. movimientos_inventario ──────────────────────────────────────────
        // user_id: FK auto-index existe, pero lo nombramos para reporting
        $this->safeAddIndex('movimientos_inventario', 'user_id', 'idx_movinv_user_id');

        // [user_id, fecha_movimiento] para auditoría por cajero/fecha
        $this->safeAddIndex('movimientos_inventario', ['user_id', 'fecha_movimiento'], 'idx_movinv_user_fecha');

        // [origen, origen_id, tipo] para lookup reverso compra/venta → movimientos
        $this->safeAddIndex('movimientos_inventario', ['origen', 'origen_id', 'tipo'], 'idx_movinv_origen_tipo');

        // ── 5. sesiones_caja ───────────────────────────────────────────────────
        // cerrado_por: FK auto-index largo; nombramos uno accesible
        $this->safeAddIndex('sesiones_caja', 'cerrado_por', 'idx_sesion_caja_cerrado_por');

        // ── 6. movimientos_caja ────────────────────────────────────────────────
        // user_id solo (FK auto-index ya existe, nombramos para reporting)
        $this->safeAddIndex('movimientos_caja', 'user_id', 'idx_movcaja_user_id');

        // [tipo, created_at] para filtrar ingresos/egresos por fecha
        $this->safeAddIndex('movimientos_caja', ['tipo', 'created_at'], 'idx_movcaja_tipo_fecha');

        // ── 7. compras ─────────────────────────────────────────────────────────
        // anulado_por: FK sin índice de búsqueda directa
        $this->safeAddIndex('compras', 'anulado_por', 'idx_compras_anulado_por');

        // ── 8. ventas ──────────────────────────────────────────────────────────
        // anulado_por: FK sin índice de búsqueda directa
        $this->safeAddIndex('ventas', 'anulado_por', 'idx_ventas_anulado_por');

        // [cliente_id, fecha] para historial de compras de cliente
        $this->safeAddIndex('ventas', ['cliente_id', 'fecha'], 'idx_ventas_cliente_fecha');

        // [sesion_caja_id, estado] para dashboard de caja actual
        $this->safeAddIndex('ventas', ['sesion_caja_id', 'estado'], 'idx_ventas_sesion_estado');

        // ── 9. lotes ───────────────────────────────────────────────────────────
        // [proveedor_id, fecha_vencimiento] para alertas de vencimiento por proveedor
        $this->safeAddIndex('lotes', ['proveedor_id', 'fecha_vencimiento'], 'idx_lotes_prov_venc');

        // ── 10. login_logs ─────────────────────────────────────────────────────
        // ip: para detección de accesos desde IP sospechosa
        $this->safeAddIndex('login_logs', 'ip', 'idx_login_logs_ip');
    }

    // -------------------------------------------------------------------------
    // down(): revertir en orden inverso
    // -------------------------------------------------------------------------

    public function down(): void
    {
        $this->safeDropIndex('login_logs',            'idx_login_logs_ip');

        $this->safeDropIndex('lotes',                 'idx_lotes_prov_venc');

        $this->safeDropIndex('ventas',                'idx_ventas_sesion_estado');
        $this->safeDropIndex('ventas',                'idx_ventas_cliente_fecha');
        $this->safeDropIndex('ventas',                'idx_ventas_anulado_por');

        $this->safeDropIndex('compras',               'idx_compras_anulado_por');

        $this->safeDropIndex('movimientos_caja',      'idx_movcaja_tipo_fecha');
        $this->safeDropIndex('movimientos_caja',      'idx_movcaja_user_id');

        $this->safeDropIndex('sesiones_caja',         'idx_sesion_caja_cerrado_por');

        $this->safeDropIndex('movimientos_inventario','idx_movinv_origen_tipo');
        $this->safeDropIndex('movimientos_inventario','idx_movinv_user_fecha');
        $this->safeDropIndex('movimientos_inventario','idx_movinv_user_id');

        $this->safeDropIndex('receta_detalles',       'idx_receta_det_producto_id');

        $this->safeDropIndex('venta_receta',          'idx_venta_receta_receta_id');

        $this->safeDropIndex('detalle_venta',         'idx_det_venta_venta_lote');
        $this->safeDropIndex('detalle_venta',         'idx_det_venta_prod_lote');
        $this->safeDropIndex('detalle_venta',         'idx_det_venta_pres_id');
    }
};
