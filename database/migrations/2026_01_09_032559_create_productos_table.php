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
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_barra', 50)->nullable()->unique();
            $table->string('nombre', 150);
            $table->string('principio_activo', 200)->nullable();
            $table->string('concentracion', 100)->nullable(); // Ej: 500mg, 100mg/5ml
            $table->string('forma_farmaceutica', 100)->nullable(); // Ej: Tableta, Jarabe, Suspensión, Cápsula
            $table->text('descripcion')->nullable();
            $table->string('imagen')->nullable();
            
            $table->foreignId('categoria_id')->constrained('categorias')->restrictOnDelete();
            $table->foreignId('laboratorio_id')->nullable()->constrained('laboratorios')->nullOnDelete();
            
            $table->string('registro_sanitario', 100)->nullable();
            $table->enum('tipo_control', ['venta_libre', 'receta_medica', 'receta_retenida'])->default('venta_libre');
            
            $table->decimal('precio_compra', 10, 2)->default(0);
            $table->decimal('precio_venta', 10, 2);
            $table->integer('stock_minimo')->default(0);
            $table->string('ubicacion', 100)->nullable(); // Estante/Anaquel
            $table->boolean('requiere_receta')->default(false);
            $table->boolean('activo')->default(true);
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['categoria_id', 'activo']);
            $table->index(['laboratorio_id', 'activo']);
            $table->index('nombre');
            $table->index('principio_activo');
            $table->index('tipo_control');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
