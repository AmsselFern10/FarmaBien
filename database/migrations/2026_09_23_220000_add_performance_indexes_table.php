<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for high-performance database indexing.
     */
    public function up(): void
    {
        // 1. Índices para tabla productos
        Schema::table('productos', function (Blueprint $table) {
            // Índices compuestos para catálogo y POS
            $table->index(['activo', 'nombre'], 'idx_productos_activo_nombre');
            $table->index(['activo', 'categoria_id'], 'idx_productos_activo_categoria');
            $table->index(['activo', 'laboratorio_id'], 'idx_productos_activo_laboratorio');
            $table->index(['requiere_receta', 'activo'], 'idx_productos_rx_activo');
        });

        // 2. Índices para tabla lotes
        Schema::table('lotes', function (Blueprint $table) {
            $table->index(['activo', 'stock_actual', 'fecha_vencimiento'], 'idx_lotes_fefo_disp');
            $table->index(['producto_id', 'activo', 'stock_actual'], 'idx_lotes_prod_disp');
            if (Schema::hasColumn('lotes', 'compra_id')) {
                $table->index('compra_id', 'idx_lotes_compra_id');
            }
            if (Schema::hasColumn('lotes', 'proveedor_id')) {
                $table->index('proveedor_id', 'idx_lotes_proveedor_id');
            }
        });

        // 3. Índices para tabla ventas
        Schema::table('ventas', function (Blueprint $table) {
            $table->index('numero_comprobante', 'idx_ventas_numero_comp');
            $table->index('metodo_pago', 'idx_ventas_metodo_pago');
            $table->index('tipo_comprobante', 'idx_ventas_tipo_comp');
            if (Schema::hasColumn('ventas', 'sesion_caja_id')) {
                $table->index('sesion_caja_id', 'idx_ventas_sesion_caja');
            }
            $table->index(['estado', 'fecha'], 'idx_ventas_estado_fecha');
        });

        // 4. Índices para tabla clientes
        Schema::table('clientes', function (Blueprint $table) {
            $table->index(['activo', 'nombre'], 'idx_clientes_activo_nombre');
            $table->index('telefono', 'idx_clientes_telefono');
        });

        // 5. Índices para tabla recetas (si existe)
        if (Schema::hasTable('recetas')) {
            Schema::table('recetas', function (Blueprint $table) {
                $table->index('numero_receta', 'idx_recetas_numero');
                $table->index(['estado', 'fecha_emision'], 'idx_recetas_estado_fecha');
                $table->index(['cliente_id', 'estado'], 'idx_recetas_cliente_estado');
            });
        }

        // 6. Índices para movimientos_inventario
        if (Schema::hasTable('movimientos_inventario')) {
            Schema::table('movimientos_inventario', function (Blueprint $table) {
                $table->index(['producto_id', 'tipo', 'fecha_movimiento'], 'idx_movs_prod_tipo_fecha');
                $table->index(['lote_id', 'fecha_movimiento'], 'idx_movs_lote_fecha');
            });
        }

        // 7. Índices para presentaciones_producto
        if (Schema::hasTable('presentaciones_producto')) {
            Schema::table('presentaciones_producto', function (Blueprint $table) {
                $table->index(['producto_id', 'activo'], 'idx_pres_prod_activo');
                $table->index('codigo_barras', 'idx_pres_codigo_barras');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropIndex('idx_productos_activo_nombre');
            $table->dropIndex('idx_productos_activo_categoria');
            $table->dropIndex('idx_productos_activo_laboratorio');
            $table->dropIndex('idx_productos_rx_activo');
        });

        Schema::table('lotes', function (Blueprint $table) {
            $table->dropIndex('idx_lotes_fefo_disp');
            $table->dropIndex('idx_lotes_prod_disp');
            if (Schema::hasColumn('lotes', 'compra_id')) {
                $table->dropIndex('idx_lotes_compra_id');
            }
            if (Schema::hasColumn('lotes', 'proveedor_id')) {
                $table->dropIndex('idx_lotes_proveedor_id');
            }
        });

        Schema::table('ventas', function (Blueprint $table) {
            $table->dropIndex('idx_ventas_numero_comp');
            $table->dropIndex('idx_ventas_metodo_pago');
            $table->dropIndex('idx_ventas_tipo_comp');
            if (Schema::hasColumn('ventas', 'sesion_caja_id')) {
                $table->dropIndex('idx_ventas_sesion_caja');
            }
            $table->dropIndex('idx_ventas_estado_fecha');
        });

        Schema::table('clientes', function (Blueprint $table) {
            $table->dropIndex('idx_clientes_activo_nombre');
            $table->dropIndex('idx_clientes_telefono');
        });

        if (Schema::hasTable('recetas')) {
            Schema::table('recetas', function (Blueprint $table) {
                $table->dropIndex('idx_recetas_numero');
                $table->dropIndex('idx_recetas_estado_fecha');
                $table->dropIndex('idx_recetas_cliente_estado');
            });
        }

        if (Schema::hasTable('movimientos_inventario')) {
            Schema::table('movimientos_inventario', function (Blueprint $table) {
                $table->dropIndex('idx_movs_prod_tipo_fecha');
                $table->dropIndex('idx_movs_lote_fecha');
            });
        }

        if (Schema::hasTable('presentaciones_producto')) {
            Schema::table('presentaciones_producto', function (Blueprint $table) {
                $table->dropIndex('idx_pres_prod_activo');
                $table->dropIndex('idx_pres_codigo_barras');
            });
        }
    }
};
