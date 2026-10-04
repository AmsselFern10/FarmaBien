<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migración de índices de rendimiento para módulos nuevos.
 *
 * PROBLEMAS DETECTADOS (sin esta migración):
 * - devoluciones_compra: sin índice en proveedor_id, created_at, estado
 * - detalles_devolucion_compra: sin índice en devolucion_compra_id, lote_id, producto_id
 * - conteos_inventario: sin índice en estado, usuario_id, created_at
 * - detalles_conteo: sin índice compuesto conteo_id+lote_id ni ajustado
 * - pagos_cuentas_por_pagar: sin índice en metodo_pago (filtros de reporte)
 * - precios_venta: sin índice en producto_id+vigente (para precioVentaVigente relation)
 * - ordenes_compras: sin índice en user_id ni fecha_esperada_entrega
 * - productos: sin índice en deleted_at (SoftDeletes — cada query hace full scan extra)
 * - compras: sin índice en estado + fecha_vencimiento_pago (filtros CxP)
 */
return new class extends Migration
{
    public function up(): void
    {
        // ----------------------------------------------------------------
        // 1. devoluciones_compra — índices de filtro frecuente
        // ----------------------------------------------------------------
        Schema::table('devoluciones_compra', function (Blueprint $table) {
            if (!$this->indexExists('devoluciones_compra', 'dev_comp_prov_fecha_idx')) {
                $table->index(['proveedor_id', 'created_at'], 'dev_comp_prov_fecha_idx');
            }
            if (!$this->indexExists('devoluciones_compra', 'dev_comp_estado_idx')) {
                $table->index('estado', 'dev_comp_estado_idx');
            }
            // compra_id ya tiene FK pero no índice explícito para búsquedas inversas
            if (!$this->indexExists('devoluciones_compra', 'dev_comp_compra_idx')) {
                $table->index('compra_id', 'dev_comp_compra_idx');
            }
        });

        // ----------------------------------------------------------------
        // 2. detalles_devolucion_compra — N+1 en carga de relaciones
        // ----------------------------------------------------------------
        Schema::table('detalles_devolucion_compra', function (Blueprint $table) {
            if (!$this->indexExists('detalles_devolucion_compra', 'det_devcomp_parent_prod_idx')) {
                $table->index(['devolucion_compra_id', 'producto_id'], 'det_devcomp_parent_prod_idx');
            }
            if (!$this->indexExists('detalles_devolucion_compra', 'det_devcomp_lote_idx')) {
                $table->index('lote_id', 'det_devcomp_lote_idx');
            }
        });

        // ----------------------------------------------------------------
        // 3. conteos_inventario — filtros de listado y join
        // ----------------------------------------------------------------
        Schema::table('conteos_inventario', function (Blueprint $table) {
            if (!$this->indexExists('conteos_inventario', 'conteo_estado_fecha_idx')) {
                $table->index(['estado', 'created_at'], 'conteo_estado_fecha_idx');
            }
            if (!$this->indexExists('conteos_inventario', 'conteo_usuario_idx')) {
                $table->index('usuario_id', 'conteo_usuario_idx');
            }
        });

        // ----------------------------------------------------------------
        // 4. detalles_conteo — búsquedas por conteo + estado ajuste
        // ----------------------------------------------------------------
        Schema::table('detalles_conteo', function (Blueprint $table) {
            if (!$this->indexExists('detalles_conteo', 'det_conteo_parent_idx')) {
                $table->index(['conteo_id', 'producto_id'], 'det_conteo_parent_idx');
            }
            if (!$this->indexExists('detalles_conteo', 'det_conteo_ajustado_idx')) {
                $table->index(['conteo_id', 'ajustado'], 'det_conteo_ajustado_idx');
            }
            if (!$this->indexExists('detalles_conteo', 'det_conteo_lote_idx')) {
                $table->index('lote_id', 'det_conteo_lote_idx');
            }
        });

        // ----------------------------------------------------------------
        // 5. pagos_cuentas_por_pagar — filtros de reporte por método/fecha
        // ----------------------------------------------------------------
        Schema::table('pagos_cuentas_por_pagar', function (Blueprint $table) {
            if (!$this->indexExists('pagos_cuentas_por_pagar', 'pagos_cxp_metodo_fecha_idx')) {
                $table->index(['metodo_pago', 'fecha_pago'], 'pagos_cxp_metodo_fecha_idx');
            }
        });

        // ----------------------------------------------------------------
        // 6. precios_venta — relación precioVentaVigente (el más crítico)
        //    Cada productos.index hace: WHERE producto_id=? AND vigente_hasta IS NULL
        //    AND presentacion_id IS NULL LIMIT 1
        // ----------------------------------------------------------------
        if (Schema::hasTable('precios_venta')) {
            Schema::table('precios_venta', function (Blueprint $table) {
                if (!$this->indexExists('precios_venta', 'pv_prod_vigente_idx')) {
                    // Cubre la relación precioVentaVigente(): whereNull('vigente_hasta') + whereNull('presentacion_id')
                    $table->index(['producto_id', 'vigente_hasta', 'presentacion_id'], 'pv_prod_vigente_idx');
                }
                if (!$this->indexExists('precios_venta', 'pv_vigente_desde_idx')) {
                    $table->index(['vigente_desde'], 'pv_vigente_desde_idx');
                }
            });
        }

        // ----------------------------------------------------------------
        // 7. ordenes_compras — índice user_id y fecha_esperada
        // ----------------------------------------------------------------
        Schema::table('ordenes_compras', function (Blueprint $table) {
            if (!$this->indexExists('ordenes_compras', 'oc_user_fecha_idx')) {
                $table->index(['user_id', 'fecha_emision'], 'oc_user_fecha_idx');
            }
            if (!$this->indexExists('ordenes_compras', 'oc_fecha_entrega_idx')) {
                $table->index('fecha_esperada_entrega', 'oc_fecha_entrega_idx');
            }
        });

        // ----------------------------------------------------------------
        // 8. productos — deleted_at + filtros combo (SoftDeletes)
        //    Casi TODAS las queries usan whereNull('deleted_at')
        // ----------------------------------------------------------------
        Schema::table('productos', function (Blueprint $table) {
            if (!$this->indexExists('productos', 'prod_deleted_categoria_idx')) {
                $table->index(['deleted_at', 'categoria_id'], 'prod_deleted_categoria_idx');
            }
            if (!$this->indexExists('productos', 'prod_deleted_laboratorio_idx')) {
                $table->index(['deleted_at', 'laboratorio_id'], 'prod_deleted_laboratorio_idx');
            }
            if (!$this->indexExists('productos', 'prod_activo_nombre_idx')) {
                // Para búsquedas por nombre en POS y catálogo
                $table->index(['activo', 'nombre'], 'prod_activo_nombre_idx');
            }
        });

        // ----------------------------------------------------------------
        // 9. compras — filtros CxP: estado_pago + fecha_vencimiento
        // ----------------------------------------------------------------
        Schema::table('compras', function (Blueprint $table) {
            if (!$this->indexExists('compras', 'compras_estadopago_venc_idx')) {
                $table->index(['estado_pago', 'fecha_vencimiento_pago'], 'compras_estadopago_venc_idx');
            }
        });

        // ----------------------------------------------------------------
        // 10. historial_precios — índice para reportes por fecha
        // ----------------------------------------------------------------
        if (Schema::hasTable('historial_precios')) {
            Schema::table('historial_precios', function (Blueprint $table) {
                if (!$this->indexExists('historial_precios', 'hp_prod_fecha_idx')) {
                    $table->index(['producto_id', 'created_at'], 'hp_prod_fecha_idx');
                }
            });
        }

        // ----------------------------------------------------------------
        // 11. movimientos_inventario — índice para kardex por producto+fecha
        //    (ya tiene índice separado por producto_id y fecha_movimiento,
        //     pero el compuesto producto_id+lote_id acelera el kardex por lote)
        // ----------------------------------------------------------------
        Schema::table('movimientos_inventario', function (Blueprint $table) {
            if (!$this->indexExists('movimientos_inventario', 'mov_prod_lote_fecha_idx')) {
                $table->index(['producto_id', 'lote_id', 'fecha_movimiento'], 'mov_prod_lote_fecha_idx');
            }
        });
    }

    public function down(): void
    {
        $drops = [
            'devoluciones_compra'          => ['dev_comp_prov_fecha_idx', 'dev_comp_estado_idx', 'dev_comp_compra_idx'],
            'detalles_devolucion_compra'   => ['det_devcomp_parent_prod_idx', 'det_devcomp_lote_idx'],
            'conteos_inventario'           => ['conteo_estado_fecha_idx', 'conteo_usuario_idx'],
            'detalles_conteo'              => ['det_conteo_parent_idx', 'det_conteo_ajustado_idx', 'det_conteo_lote_idx'],
            'pagos_cuentas_por_pagar'      => ['pagos_cxp_metodo_fecha_idx'],
            'precios_venta'                => ['pv_prod_vigente_idx', 'pv_vigente_desde_idx'],
            'ordenes_compras'              => ['oc_user_fecha_idx', 'oc_fecha_entrega_idx'],
            'productos'                    => ['prod_deleted_categoria_idx', 'prod_deleted_laboratorio_idx', 'prod_activo_nombre_idx'],
            'compras'                      => ['compras_estadopago_venc_idx'],
            'historial_precios'            => ['hp_prod_fecha_idx'],
            'movimientos_inventario'       => ['mov_prod_lote_fecha_idx'],
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

    /** Verifica si un índice (por nombre) existe en la tabla */
    private function indexExists(string $table, string $indexName): bool
    {
        try {
            $indexes = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
            return !empty($indexes);
        } catch (\Throwable $e) {
            return false;
        }
    }
};
