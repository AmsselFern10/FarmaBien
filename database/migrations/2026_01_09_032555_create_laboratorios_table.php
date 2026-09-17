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
        Schema::create('laboratorios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150)->unique();
            $table->string('codigo', 50)->nullable()->unique();
            $table->string('contacto', 100)->nullable();
            $table->string('telefono', 50)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('pais_origen', 80)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            
            $table->index('nombre');
            $table->index('activo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laboratorios');
    }
};
