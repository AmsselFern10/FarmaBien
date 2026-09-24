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
     * Run the migrations for high-performance database indexing.
     */
    public function up(): void
    {
        // 1. Índices para tabla productos
        $this->safeAddIndex('productos', ['activo', 'nombre'], 'idx_productos_activo_nombre');
        $this->safeAddIndex('productos', ['activo', 'categoria_id'], 'idx_productos_activo_categoria');
        $this->safeAddIndex('productos', ['activo', 'laboratorio_id'], 'idx_productos_activo_laboratorio');
        $this->safeAddIndex('productos', ['requiere_receta', 'activo'], 'idx_productos_rx_activo');

        // 2. Índices para tabla lotes
        $this->safeAddIndex('lotes', ['activo', 'stock_actual', 'fecha_vencimiento'], 'idx_lotes_fefo_disp');
        $this->safeAddIndex('lotes', ['producto_id', 'activo', 'stock_actual'], 'idx_lotes_prod_disp');
        $this->safeAddIndex('lotes', 'compra_id', 'idx_lotes_compra_id');
        $this->safeAddIndex('lotes', 'proveedor_id', 'idx_lotes_proveedor_id');

        // 3. Índices para tabla ventas
        $this->safeAddIndex('ventas', 'numero_comprobante', 'idx_ventas_numero_comp');
        $this->safeAddIndex('ventas', 'metodo_pago', 'idx_ventas_metodo_pago');
        $this->safeAddIndex('ventas', 'tipo_comprobante', 'idx_ventas_tipo_comp');
        $this->safeAddIndex('ventas', 'sesion_caja_id', 'idx_ventas_sesion_caja');
        $this->safeAddIndex('ventas', ['estado', 'fecha'], 'idx_ventas_estado_fecha');

        // 4. Índices para tabla clientes
        $this->safeAddIndex('clientes', ['activo', 'nombre'], 'idx_clientes_activo_nombre');
        $this->safeAddIndex('clientes', 'telefono', 'idx_clientes_telefono');

        // 5. Índices para tabla recetas (si existe)
        $this->safeAddIndex('recetas', 'numero_receta', 'idx_recetas_numero');
        $this->safeAddIndex('recetas', ['estado', 'fecha_emision'], 'idx_recetas_estado_fecha');
        $this->safeAddIndex('recetas', ['cliente_id', 'estado'], 'idx_recetas_cliente_estado');

        // 6. Índices para movimientos_inventario
        $this->safeAddIndex('movimientos_inventario', ['producto_id', 'tipo', 'fecha_movimiento'], 'idx_movs_prod_tipo_fecha');
        $this->safeAddIndex('movimientos_inventario', ['lote_id', 'fecha_movimiento'], 'idx_movs_lote_fecha');

        // 7. Índices para presentaciones_producto
        $this->safeAddIndex('presentaciones_producto', ['producto_id', 'activo'], 'idx_pres_prod_activo');
        $this->safeAddIndex('presentaciones_producto', 'codigo_barras', 'idx_pres_codigo_barras');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->safeDropIndex('productos', 'idx_productos_activo_nombre');
        $this->safeDropIndex('productos', 'idx_productos_activo_categoria');
        $this->safeDropIndex('productos', 'idx_productos_activo_laboratorio');
        $this->safeDropIndex('productos', 'idx_productos_rx_activo');

        $this->safeDropIndex('lotes', 'idx_lotes_fefo_disp');
        $this->safeDropIndex('lotes', 'idx_lotes_prod_disp');
        $this->safeDropIndex('lotes', 'idx_lotes_compra_id');
        $this->safeDropIndex('lotes', 'idx_lotes_proveedor_id');

        $this->safeDropIndex('ventas', 'idx_ventas_numero_comp');
        $this->safeDropIndex('ventas', 'idx_ventas_metodo_pago');
        $this->safeDropIndex('ventas', 'idx_ventas_tipo_comp');
        $this->safeDropIndex('ventas', 'idx_ventas_sesion_caja');
        $this->safeDropIndex('ventas', 'idx_ventas_estado_fecha');

        $this->safeDropIndex('clientes', 'idx_clientes_activo_nombre');
        $this->safeDropIndex('clientes', 'idx_clientes_telefono');

        $this->safeDropIndex('recetas', 'idx_recetas_numero');
        $this->safeDropIndex('recetas', 'idx_recetas_estado_fecha');
        $this->safeDropIndex('recetas', 'idx_recetas_cliente_estado');

        $this->safeDropIndex('movimientos_inventario', 'idx_movs_prod_tipo_fecha');
        $this->safeDropIndex('movimientos_inventario', 'idx_movs_lote_fecha');

        $this->safeDropIndex('presentaciones_producto', 'idx_pres_prod_activo');
        $this->safeDropIndex('presentaciones_producto', 'idx_pres_codigo_barras');
    }
};
