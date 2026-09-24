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
        Schema::create('promociones', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150);
            $table->text('descripcion')->nullable();
            
            // Tipo de descuento / beneficio
            $table->enum('tipo', ['porcentaje', 'monto_fijo', '2x1', '3x2'])->default('porcentaje');
            $table->decimal('valor', 10, 2)->default(0); // 15.00 para 15% o $5.00
            
            // Nivel de alcance
            $table->enum('alcance', ['producto', 'categoria', 'laboratorio', 'general'])->default('producto');
            $table->foreignId('producto_id')->nullable()->constrained('productos')->cascadeOnDelete();
            $table->foreignId('categoria_id')->nullable()->constrained('categorias')->cascadeOnDelete();
            $table->foreignId('laboratorio_id')->nullable()->constrained('laboratorios')->cascadeOnDelete();
            
            // Período de vigencia
            $table->dateTime('fecha_inicio');
            $table->dateTime('fecha_fin');
            
            // Reglas de aplicación y límites
            $table->integer('min_unidades')->default(1);
            $table->integer('stock_limite')->nullable(); // Cantidad máxima de unidades que pueden venderse con descuento
            $table->integer('stock_consumido')->default(0); // Unidades vendidas bajo la promo
            $table->boolean('activo')->default(true);
            
            $table->timestamps();
            $table->softDeletes();
            
            // Índices para consultas de alta velocidad en POS y Catálogos
            $table->index(['activo', 'fecha_inicio', 'fecha_fin']);
            $table->index(['alcance', 'activo']);
            $table->index('producto_id');
            $table->index('categoria_id');
            $table->index('laboratorio_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promociones');
    }
};
