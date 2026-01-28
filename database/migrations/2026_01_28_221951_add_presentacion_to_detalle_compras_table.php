<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('detalle_compra', function (Blueprint $table) {
            // 1. Verificamos si las columnas ya existen antes de crearlas
            if (!Schema::hasColumn('detalle_compra', 'presentacion_id')) {
                $table->foreignId('presentacion_id')
                    ->nullable()
                    ->after('producto_id')
                    ->constrained('presentaciones_producto')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('detalle_compra', 'tipo_presentacion')) {
                $table->string('tipo_presentacion', 50)->nullable()->after('presentacion_id');
            }

            if (!Schema::hasColumn('detalle_compra', 'unidades_por_presentacion')) {
                $table->integer('unidades_por_presentacion')->default(1)->after('tipo_presentacion');
            }

            if (!Schema::hasColumn('detalle_compra', 'cantidad_presentaciones')) {
                $table->integer('cantidad_presentaciones')->default(1)->after('unidades_por_presentacion');
            }
        });

        // 2. Renombrar 'cantidad' a 'cantidad_legacy' usando sintaxis compatible con MariaDB
        // Solo si 'cantidad' existe y 'cantidad_legacy' no.
        if (Schema::hasColumn('detalle_compra', 'cantidad') && !Schema::hasColumn('detalle_compra', 'cantidad_legacy')) {
            DB::statement('ALTER TABLE detalle_compra CHANGE cantidad cantidad_legacy INT(11) NOT NULL');
        }

        // 3. Agregar columna calculada (Si no existe)
        if (!Schema::hasColumn('detalle_compra', 'cantidad_unidades_base')) {
            DB::statement('
                ALTER TABLE detalle_compra 
                ADD COLUMN cantidad_unidades_base INT(11) 
                GENERATED ALWAYS AS (cantidad_presentaciones * unidades_por_presentacion) STORED
                AFTER cantidad_presentaciones
            ');
        }

        // 4. Sincronizar datos (Migrar la cantidad anterior a la nueva estructura)
        DB::statement('
            UPDATE detalle_compra 
            SET 
                cantidad_presentaciones = cantidad_legacy,
                unidades_por_presentacion = 1,
                tipo_presentacion = "Unidad"
            WHERE presentacion_id IS NULL
        ');
    }

    public function down(): void
    {
        Schema::table('detalle_compra', function (Blueprint $table) {
            // Eliminar columna generada
            if (Schema::hasColumn('detalle_compra', 'cantidad_unidades_base')) {
                $table->dropColumn('cantidad_unidades_base');
            }
        });

        // Revertir el nombre de la columna legacy
        if (Schema::hasColumn('detalle_compra', 'cantidad_legacy')) {
            DB::statement('ALTER TABLE detalle_compra CHANGE cantidad_legacy cantidad INT(11) NOT NULL');
        }

        Schema::table('detalle_compra', function (Blueprint $table) {
            $table->dropForeign(['presentacion_id']);
            $table->dropColumn([
                'presentacion_id',
                'tipo_presentacion',
                'unidades_por_presentacion',
                'cantidad_presentaciones'
            ]);
        });
    }
};