<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Limpieza de índices duplicados — FarmaBien v2.0
 * ─────────────────────────────────────────────────────────────────────────────
 * En MySQL/InnoDB, cuando Laravel usa foreignId()->constrained() crea
 * automáticamente un índice de FK con nombre "{tabla}_{columna}_foreign".
 * Si luego una migración agrega manualmente index('columna') sobre la misma
 * columna, quedan DOS índices sobre el mismo campo, lo que:
 *   • Duplica la RAM de buffer pool usada por el índice
 *   • Lentifica INSERT / UPDATE / DELETE (MySQL mantiene ambos en cada escritura)
 *   • No aporta ninguna mejora en SELECT (el optimizador elige uno solo)
 *
 * Esta migración elimina ÚNICAMENTE los índices manuales redundantes.
 * Las FK constraints y sus índices automáticos (_foreign) se conservan intactos.
 * ─────────────────────────────────────────────────────────────────────────────
 */
return new class extends Migration
{
    // ─── Helpers idempotentes ─────────────────────────────────────────────────

    protected function indexExists(string $table, string $indexName): bool
    {
        try {
            $driver = Schema::getConnection()->getDriverName();
            if ($driver === 'sqlite') {
                $indexes = DB::select("PRAGMA index_list('{$table}')");
                foreach ($indexes as $idx) {
                    if (($idx->name ?? null) === $indexName) return true;
                }
                return false;
            }
            $database = Schema::getConnection()->getDatabaseName();
            return DB::table('information_schema.statistics')
                ->where('table_schema', $database)
                ->where('table_name', $table)
                ->where('index_name', $indexName)
                ->count() > 0;
        } catch (\Throwable $e) {
            return false;
        }
    }

    protected function safeDropIndex(string $table, string $indexName): void
    {
        if (!Schema::hasTable($table)) return;
        if (!$this->indexExists($table, $indexName)) return;
        try {
            Schema::table($table, fn (Blueprint $t) => $t->dropIndex($indexName));
        } catch (\Throwable $e) {
            // Ignorar si ya fue eliminado o no existe
        }
    }

    protected function safeAddIndex(string $table, $columns, string $indexName): void
    {
        if (!Schema::hasTable($table)) return;
        $cols = is_array($columns) ? $columns : [$columns];
        foreach ($cols as $col) {
            if (!Schema::hasColumn($table, $col)) return;
        }
        if ($this->indexExists($table, $indexName)) return;
        try {
            Schema::table($table, fn (Blueprint $t) => $t->index($columns, $indexName));
        } catch (\Throwable $e) {
            // Ignorar
        }
    }

    // ─── Nombres reales de los índices automáticos de FK en MySQL ─────────────
    // Laravel genera: {tabla}_{columna}_foreign
    // Esos se CONSERVAN. Solo eliminamos los índices manuales redundantes.

    public function up(): void
    {
        /*
         * ── 1. detalle_venta ──────────────────────────────────────────────────
         * FK auto (conservar): detalle_venta_lote_id_foreign
         * Manual redundante  : detalle_venta_lote_id_index  ← ELIMINAR
         *
         * FK auto (conservar): detalle_venta_receta_detalle_id_foreign
         * Manual redundante  : detalle_venta_receta_detalle_id_index  ← ELIMINAR
         */
        $this->safeDropIndex('detalle_venta', 'detalle_venta_lote_id_index');
        $this->safeDropIndex('detalle_venta', 'detalle_venta_receta_detalle_id_index');

        /*
         * ── 2. detalle_compra ─────────────────────────────────────────────────
         * FK auto (conservar): detalle_compra_lote_id_foreign
         * Manual redundante  : idx_det_compra_lote  ← ELIMINAR
         *   (añadido por 2026_09_24_080000; el auto FK ya lo cubre)
         */
        $this->safeDropIndex('detalle_compra', 'idx_det_compra_lote');

        /*
         * ── 3. lotes ──────────────────────────────────────────────────────────
         * FK auto (conservar): lotes_compra_id_foreign
         * Manual redundante  : idx_lotes_compra_id  ← ELIMINAR
         *
         * FK auto (conservar): lotes_proveedor_id_foreign
         * Manual redundante  : idx_lotes_proveedor_id  ← ELIMINAR
         *   (ambos añadidos por 2026_09_23_220000)
         */
        $this->safeDropIndex('lotes', 'idx_lotes_compra_id');
        $this->safeDropIndex('lotes', 'idx_lotes_proveedor_id');

        /*
         * ── 4. ventas ─────────────────────────────────────────────────────────
         * FK auto (conservar): ventas_cliente_id_foreign
         * Manual redundante  : ventas_cliente_id_index  ← ELIMINAR
         *
         * FK auto (conservar): ventas_sesion_caja_id_foreign
         * Manual redundante  : idx_ventas_sesion_caja  ← ELIMINAR
         *   (añadido por 2026_09_23_220000)
         */
        $this->safeDropIndex('ventas', 'ventas_cliente_id_index');
        $this->safeDropIndex('ventas', 'idx_ventas_sesion_caja');

        /*
         * ── 5. recetas ────────────────────────────────────────────────────────
         * FK auto (conservar): recetas_cliente_id_foreign
         * Manual redundante  : recetas_cliente_id_index  ← ELIMINAR
         *
         * UNIQUE (conservar) : recetas_numero_receta_unique
         * Manual redundante  : idx_recetas_numero  ← ELIMINAR
         *   (añadido por 2026_09_23_220000; el UNIQUE ya actúa como índice)
         */
        $this->safeDropIndex('recetas', 'recetas_cliente_id_index');
        $this->safeDropIndex('recetas', 'idx_recetas_numero');
    }

    public function down(): void
    {
        /*
         * Al revertir, restauramos los índices manuales que se eliminaron.
         * (Los FK _foreign y _unique nunca se tocaron, siguen intactos.)
         */

        // detalle_venta
        $this->safeAddIndex('detalle_venta', 'lote_id',           'detalle_venta_lote_id_index');
        $this->safeAddIndex('detalle_venta', 'receta_detalle_id', 'detalle_venta_receta_detalle_id_index');

        // detalle_compra
        $this->safeAddIndex('detalle_compra', 'lote_id', 'idx_det_compra_lote');

        // lotes
        $this->safeAddIndex('lotes', 'compra_id',    'idx_lotes_compra_id');
        $this->safeAddIndex('lotes', 'proveedor_id', 'idx_lotes_proveedor_id');

        // ventas
        $this->safeAddIndex('ventas', 'cliente_id',     'ventas_cliente_id_index');
        $this->safeAddIndex('ventas', 'sesion_caja_id', 'idx_ventas_sesion_caja');

        // recetas
        $this->safeAddIndex('recetas', 'cliente_id',    'recetas_cliente_id_index');
        $this->safeAddIndex('recetas', 'numero_receta', 'idx_recetas_numero');
    }
};
