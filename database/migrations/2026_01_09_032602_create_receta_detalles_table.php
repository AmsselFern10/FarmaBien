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
        Schema::create('receta_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receta_id')->constrained('recetas')->onDelete('cascade');
            $table->foreignId('producto_id')->constrained('productos')->restrictOnDelete();
            
            $table->integer('cantidad_recetada');
            $table->integer('cantidad_dispensada')->default(0);
            $table->string('posologia', 255)->nullable(); // Ej: 1 cada 8 horas por 7 días
            
            $table->timestamps();
            
            $table->index(['receta_id', 'producto_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receta_detalles');
    }
};
