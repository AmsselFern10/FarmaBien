<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            // 0 = sin control, 1 = Nivel I (básico/controlado), 2 = Nivel II (opioides), 3 = Nivel III (narcóticos)
            $table->tinyInteger('nivel_controlado')->unsigned()->default(0)->after('requiere_receta')
                  ->comment('0=libre,1=nivel I,2=nivel II opioides,3=nivel III narcóticos');
            $table->index('nivel_controlado', 'idx_productos_nivel_controlado');
        });
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropIndex('idx_productos_nivel_controlado');
            $table->dropColumn('nivel_controlado');
        });
    }
};
