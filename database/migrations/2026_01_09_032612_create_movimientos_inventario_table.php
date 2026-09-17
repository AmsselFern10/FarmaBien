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
            $table->foreignId('producto_id')->constrained('productos')->restrictOnDelete();
            $table->foreignId('lote_id')->constrained('lotes')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            
            $table->enum('tipo', ['entrada', 'salida', 'ajuste']);
            $table->enum('subtipo', [
                'compra',
                'venta',
                'anulacion_venta',
                'anulacion_compra',
                'ajuste_manual',
                'merma_vencimiento',
                'merma_danio',
                'vencimiento_automatico',
                'modificacion_venta',
                'modificacion_compra'
            ])->default('ajuste_manual');
            
            $table->integer('cantidad'); // Positivo para entrada, negativo para salida (en unidades base)
            
            // Saldos para Kardex de auditoría
            $table->integer('stock_anterior');
            $table->integer('stock_posterior');
            
            // Costos para Kardex valorizado
            $table->decimal('costo_unitario', 10, 2)->default(0);
            $table->decimal('costo_total', 10, 2)->default(0);
            
            // Referencia polimórfica al origen
            $table->string('origen', 50)->nullable(); // 'venta', 'compra', 'ajuste_manual', etc.
            $table->unsignedBigInteger('origen_id')->nullable();
            
            $table->string('motivo', 255)->nullable();
            $table->dateTime('fecha_movimiento');
            
            $table->timestamps();
            
            $table->index(['producto_id', 'fecha_movimiento']);
            $table->index(['lote_id', 'fecha_movimiento']);
            $table->index(['tipo', 'subtipo']);
            $table->index(['origen', 'origen_id']);
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
