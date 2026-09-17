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
        Schema::create('detalle_venta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venta_id')->constrained('ventas')->onDelete('cascade');
            $table->foreignId('producto_id')->constrained('productos')->restrictOnDelete();
            $table->foreignId('lote_id')->constrained('lotes')->restrictOnDelete();
            $table->foreignId('presentacion_id')->nullable()->constrained('presentaciones_producto')->nullOnDelete();
            $table->foreignId('receta_detalle_id')->nullable()->constrained('receta_detalles')->nullOnDelete();
            
            $table->integer('cantidad'); // Cantidad de presentaciones vendidas
            $table->integer('unidades_por_presentacion')->default(1);
            $table->integer('cantidad_unidades_base'); // Unidades base descontadas del lote
            
            $table->decimal('precio_unitario', 10, 2); // Precio cobrado por presentación
            $table->decimal('subtotal', 10, 2);
            
            $table->timestamps();
            
            $table->index(['venta_id', 'producto_id']);
            $table->index('lote_id');
            $table->index('receta_detalle_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_venta');
    }
};
