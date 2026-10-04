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
        // 1. compras: orden_compra_id
        Schema::table('compras', function (Blueprint $table) {
            if (!Schema::hasColumn('compras', 'orden_compra_id')) {
                $table->foreignId('orden_compra_id')->nullable()->after('reemplazada_por')->constrained('ordenes_compras')->nullOnDelete();
            }
        });

        // 2. detalle_compra: detalle_orden_compra_id
        Schema::table('detalle_compra', function (Blueprint $table) {
            if (!Schema::hasColumn('detalle_compra', 'detalle_orden_compra_id')) {
                $table->foreignId('detalle_orden_compra_id')->nullable()->after('presentacion_id')->constrained('detalle_ordenes_compras')->nullOnDelete();
            }
        });

        // 3. ordenes_compras: campos de cierre con faltante
        Schema::table('ordenes_compras', function (Blueprint $table) {
            if (!Schema::hasColumn('ordenes_compras', 'cerrada_con_faltante')) {
                $table->boolean('cerrada_con_faltante')->default(false)->after('compra_id');
            }
            if (!Schema::hasColumn('ordenes_compras', 'faltante_unidades')) {
                $table->integer('faltante_unidades')->default(0)->after('cerrada_con_faltante');
            }
            if (!Schema::hasColumn('ordenes_compras', 'cerrada_por_id')) {
                $table->foreignId('cerrada_por_id')->nullable()->after('faltante_unidades')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('ordenes_compras', 'fecha_cierre')) {
                $table->dateTime('fecha_cierre')->nullable()->after('cerrada_por_id');
            }
            if (!Schema::hasColumn('ordenes_compras', 'motivo_faltante')) {
                $table->text('motivo_faltante')->nullable()->after('fecha_cierre');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('compras', function (Blueprint $table) {
            if (Schema::hasColumn('compras', 'orden_compra_id')) {
                $table->dropForeign(['orden_compra_id']);
                $table->dropColumn('orden_compra_id');
            }
        });

        Schema::table('detalle_compra', function (Blueprint $table) {
            if (Schema::hasColumn('detalle_compra', 'detalle_orden_compra_id')) {
                $table->dropForeign(['detalle_orden_compra_id']);
                $table->dropColumn('detalle_orden_compra_id');
            }
        });

        Schema::table('ordenes_compras', function (Blueprint $table) {
            $cols = ['cerrada_con_faltante', 'faltante_unidades', 'cerrada_por_id', 'fecha_cierre', 'motivo_faltante'];
            $drop = [];
            foreach ($cols as $c) {
                if (Schema::hasColumn('ordenes_compras', $c)) {
                    $drop[] = $c;
                }
            }
            if (!empty($drop)) {
                $table->dropColumn($drop);
            }
        });
    }
};
