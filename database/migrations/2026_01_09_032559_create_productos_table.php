<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id();

            $table->string('codigo_barra', 50)->unique()->nullable();
            $table->string('nombre', 150);
            $table->text('descripcion')->nullable();
            $table->string('imagen')->nullable();

            $table->foreignId('categoria_id')->constrained('categorias');

            // Precios
            $table->decimal('precio_venta', 10, 2);
            $table->decimal('precio_compra', 10, 2)->nullable();

            // Stock / ubicación
            $table->integer('stock_minimo')->default(0);
            $table->string('ubicacion', 100)->nullable();

            // Flags
            $table->boolean('requiere_receta')->default(false);
            $table->boolean('activo')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['categoria_id', 'activo']);
            $table->index('nombre');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};