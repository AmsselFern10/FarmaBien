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
       Schema::create('movimientos_inventario', function (Blueprint $table) {
    $table->id();
    $table->foreignId('producto_id')->constrained('productos');
    $table->foreignId('lote_id')->constrained('lotes');
    $table->foreignId('user_id')->constrained('users');

    $table->enum('tipo', ['entrada', 'salida', 'ajuste']);
    $table->integer('cantidad');

    $table->integer('stock_anterior')->nullable();
    $table->integer('stock_actual')->nullable();

    $table->string('origen')->nullable();
    $table->unsignedBigInteger('origen_id')->nullable();

    $table->string('motivo', 150)->nullable();
    $table->dateTime('fecha_movimiento');

    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimientos_inventario');
    }
};
