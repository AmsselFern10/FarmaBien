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
        Schema::create('recetas', function (Blueprint $table) {
            $table->id();
            
            // Relación con cliente
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
            
            // Datos del Médico
            $table->string('medico', 100);
            $table->string('especialidad', 100)->nullable();
            
            // Datos de la Receta
            $table->string('numero_receta', 50)->unique();
            $table->date('fecha');
            
            // Datos Clínicos
            $table->text('diagnostico')->nullable();
            $table->text('observaciones')->nullable();
            
            $table->timestamps();

            // Índices para optimización de búsquedas
            $table->index('cliente_id');
            $table->index('fecha');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recetas');
    }
};