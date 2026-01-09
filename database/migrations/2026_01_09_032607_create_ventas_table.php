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
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->nullable()->constrained('clientes');
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('anulado_por')->nullable()->constrained('users');
            $table->decimal('total', 10, 2);
            $table->string('metodo_pago', 30);
            $table->enum('estado', ['completada', 'anulada'])->default('completada');
            $table->dateTime('fecha');
            $table->dateTime('fecha_anulacion')->nullable();
            $table->text('motivo_anulacion')->nullable();
            $table->timestamps();
            
            $table->index(['fecha', 'estado']);
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};
