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
        Schema::table('conteos_inventario', function (Blueprint $table) {
            $table->json('laboratorios_ids')->nullable()->after('notas');
            $table->json('categorias_ids')->nullable()->after('laboratorios_ids');
            $table->string('regimen_venta', 50)->default('todos')->after('categorias_ids'); // 'todos', 'venta_libre', 'controlados'
            $table->json('alcance_resumen')->nullable()->after('regimen_venta');
            $table->string('idempotency_key', 100)->nullable()->unique()->after('alcance_resumen');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conteos_inventario', function (Blueprint $table) {
            $table->dropColumn([
                'laboratorios_ids',
                'categorias_ids',
                'regimen_venta',
                'alcance_resumen',
                'idempotency_key',
            ]);
        });
    }
};
