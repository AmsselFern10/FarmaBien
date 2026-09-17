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
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();
            
            // Datos del Paciente
            $table->string('paciente_nombre', 150);
            $table->string('paciente_documento', 50)->nullable();
            $table->integer('paciente_edad')->nullable();
            
            // Datos del Médico Prescriptor
            $table->string('medico_nombre', 150);
            $table->string('medico_colegiatura', 50); // Cédula o número de colegiatura médica
            $table->string('medico_especialidad', 100)->nullable();
            $table->string('institucion_salud', 150)->nullable(); // Hospital / Clínica
            
            // Identificación y Vigencia de la Receta
            $table->string('numero_receta', 50)->unique();
            $table->date('fecha_emision');
            $table->date('fecha_vencimiento')->nullable();
            
            // Clasificación y Control
            $table->enum('tipo_receta', ['simple', 'retenida'])->default('simple');
            $table->string('archivo_receta')->nullable(); // Imagen / PDF escaneado
            $table->enum('estado', ['pendiente', 'dispensada_parcial', 'dispensada_total', 'anulada'])->default('pendiente');
            $table->text('observaciones')->nullable();
            
            $table->timestamps();
            
            $table->index('cliente_id');
            $table->index('numero_receta');
            $table->index('fecha_emision');
            $table->index('estado');
            $table->index('tipo_receta');
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
