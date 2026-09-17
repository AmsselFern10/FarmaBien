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
        Schema::create('lotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->restrictOnDelete();
            $table->foreignId('compra_id')->nullable()->constrained('compras')->nullOnDelete();
            $table->foreignId('proveedor_id')->nullable()->constrained('proveedores')->nullOnDelete();
            
            $table->string('numero_lote', 50);
            $table->date('fecha_vencimiento');
            
            // Stock en UNIDADES BASE mínimas
            $table->integer('stock_inicial');
            $table->integer('stock_actual'); // Columna física persistida e indexada para alto rendimiento
            
            // Costo de compra por unidad base
            $table->decimal('precio_compra', 10, 2);
            $table->boolean('activo')->default(true);
            
            $table->timestamps();
            
            // Índices para optimizar búsquedas FIFO y alertas de vencimiento
            $table->index(['producto_id', 'activo', 'fecha_vencimiento']);
            $table->index(['producto_id', 'stock_actual']);
            $table->index('numero_lote');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lotes');
    }
};
