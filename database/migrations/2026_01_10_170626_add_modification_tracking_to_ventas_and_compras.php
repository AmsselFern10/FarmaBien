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
        // Agregar campos a VENTAS para trazabilidad de modificaciones
        Schema::table('ventas', function (Blueprint $table) {
            // Referencia a la venta que reemplazó a esta (si fue modificada)
            $table->foreignId('reemplazada_por')->nullable()
                ->after('anulado_por')
                ->constrained('ventas')
                ->onDelete('set null');
            
            // Referencia a la venta original (si esta es una modificación)
            $table->foreignId('venta_original_id')->nullable()
                ->after('reemplazada_por')
                ->constrained('ventas')
                ->onDelete('set null');
            
            // Índices para búsquedas rápidas
            $table->index('reemplazada_por');
            $table->index('venta_original_id');
        });

        // Agregar campos a COMPRAS para trazabilidad de modificaciones
        Schema::table('compras', function (Blueprint $table) {
            // Referencia a la compra que reemplazó a esta (si fue modificada)
            $table->foreignId('reemplazada_por')->nullable()
                ->after('anulado_por')
                ->constrained('compras')
                ->onDelete('set null');
            
            // Referencia a la compra original (si esta es una modificación)
            $table->foreignId('compra_original_id')->nullable()
                ->after('reemplazada_por')
                ->constrained('compras')
                ->onDelete('set null');
            
            // Índices
            $table->index('reemplazada_por');
            $table->index('compra_original_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropForeign(['reemplazada_por']);
            $table->dropForeign(['venta_original_id']);
            $table->dropColumn(['reemplazada_por', 'venta_original_id']);
        });

        Schema::table('compras', function (Blueprint $table) {
            $table->dropForeign(['reemplazada_por']);
            $table->dropForeign(['compra_original_id']);
            $table->dropColumn(['reemplazada_por', 'compra_original_id']);
        });
    }
};