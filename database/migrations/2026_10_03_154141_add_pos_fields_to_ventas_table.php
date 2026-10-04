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
        Schema::table('ventas', function (Blueprint $table) {
            if (!Schema::hasColumn('ventas', 'tipo_descuento')) {
                $table->string('tipo_descuento', 20)->default('monto')->after('descuento');
            }
            if (!Schema::hasColumn('ventas', 'porcentaje_descuento')) {
                $table->decimal('porcentaje_descuento', 5, 2)->default(0)->after('tipo_descuento');
            }
            if (!Schema::hasColumn('ventas', 'receta_modalidad')) {
                $table->string('receta_modalidad', 30)->default('sin_receta')->after('cambio');
            }
            if (!Schema::hasColumn('ventas', 'receta_omision_motivo')) {
                $table->string('receta_omision_motivo', 500)->nullable()->after('receta_modalidad');
            }
            if (!Schema::hasColumn('ventas', 'referencia_pago')) {
                $table->string('referencia_pago', 100)->nullable()->after('receta_omision_motivo');
            }
            if (!Schema::hasColumn('ventas', 'observaciones')) {
                $table->text('observaciones')->nullable()->after('referencia_pago');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $columnsToDrop = [];
            foreach (['tipo_descuento', 'porcentaje_descuento', 'receta_modalidad', 'receta_omision_motivo', 'referencia_pago', 'observaciones'] as $col) {
                if (Schema::hasColumn('ventas', $col)) {
                    $columnsToDrop[] = $col;
                }
            }
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
