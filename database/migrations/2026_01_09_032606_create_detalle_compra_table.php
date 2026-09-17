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
        Schema::create('detalle_compra', function (Blueprint $table) {
            $table->id();
            $table->foreignId('compra_id')->constrained('compras')->onDelete('cascade');
            $table->foreignId('producto_id')->constrained('productos')->restrictOnDelete();
            $table->foreignId('lote_id')->constrained('lotes')->restrictOnDelete();
            $table->foreignId('presentacion_id')->nullable()->constrained('presentaciones_producto')->nullOnDelete();
            
            $table->string('tipo_presentacion', 100)->nullable();
            $table->integer('unidades_por_presentacion')->default(1);
            $table->integer('cantidad_presentaciones')->default(1);
            $table->integer('cantidad_unidades_base'); // Total unidades base agregadas al lote
            
            $table->decimal('precio_unitario', 10, 2); // Precio por presentación
            $table->decimal('subtotal', 10, 2);
            
            $table->timestamps();
            
            $table->index(['compra_id', 'producto_id']);
            $table->index('lote_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_compra');
    }
};
