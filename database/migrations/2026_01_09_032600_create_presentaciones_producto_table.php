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
        Schema::create('presentaciones_producto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
            
            $table->string('nombre', 100); // Ej: "Caja x 100", "Blíster x 10", "Unidad / Pastilla"
            $table->string('descripcion', 150)->nullable();
            
            // Factor de conversión a unidad mínima base (Ej: Caja x 100 = 100, Blíster x 10 = 10, Unidad = 1)
            $table->integer('unidades_por_presentacion')->default(1);
            
            $table->decimal('precio_compra', 10, 2)->nullable();
            $table->decimal('precio_venta', 10, 2)->nullable();
            $table->string('codigo_barras', 50)->nullable();
            $table->boolean('es_unidad_base')->default(false);
            $table->boolean('activo')->default(true);
            $table->integer('orden')->default(0);
            
            $table->timestamps();
            
            $table->index(['producto_id', 'activo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presentaciones_producto');
    }
};
