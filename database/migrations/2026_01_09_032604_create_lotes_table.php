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
         Schema::create('lotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos');
            $table->foreignId('compra_id')->constrained('compras'); // ✅ NUEVO
            $table->foreignId('proveedor_id')->constrained('proveedores');
            $table->string('numero_lote', 50);
            $table->date('fecha_vencimiento');
            $table->integer('stock_inicial'); // ✅ CAMBIADO de 'stock'
            $table->decimal('precio_compra', 10, 2);
            $table->boolean('activo')->default(true);
            $table->timestamps();
            
            // Un número de lote debe ser único por producto
            $table->unique(['numero_lote', 'producto_id']);
            $table->index(['producto_id', 'activo', 'fecha_vencimiento']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lotes');
    }
};
