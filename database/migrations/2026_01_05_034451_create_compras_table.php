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
       Schema::create('compras', function (Blueprint $table) {
    $table->id();
    $table->foreignId('proveedor_id')->constrained('proveedores');
    $table->foreignId('user_id')->constrained('users');
    $table->decimal('total', 10, 2);
    $table->enum('estado', ['recibida', 'anulada'])->default('recibida');
    $table->dateTime('fecha');
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compras');
    }
};
