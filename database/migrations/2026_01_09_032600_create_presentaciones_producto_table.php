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
            $table->foreignId('producto_id')->constrained()->onDelete('cascade');
            
            // Nombre de la presentación
            $table->string('nombre', 50); // Ej: "Caja", "Blíster", "Frasco"
            
            // Descripción adicional
            $table->string('descripcion')->nullable(); // Ej: "Caja x 20 tabletas"
            
            // Cantidad de unidades base que contiene esta presentación
            $table->integer('unidades_por_presentacion'); // Ej: 20 tabletas, 120 ml
            
            // Precio sugerido de compra para esta presentación (opcional)
            $table->decimal('precio_sugerido', 10, 2)->nullable();
            
            // Código de barras de la presentación (si es diferente al producto base)
            $table->string('codigo_barras', 50)->nullable()->unique();
            
            // Si está activa para compras
            $table->boolean('activo')->default(true);
            
            // Orden de visualización
            $table->integer('orden')->default(0);
            
            $table->timestamps();
            
            // Índices
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