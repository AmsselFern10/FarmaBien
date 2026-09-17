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
            $table->foreignId('proveedor_id')->constrained('proveedores')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            
            $table->string('numero_comprobante', 50)->nullable(); // Factura / Guía de remisión
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('impuesto', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            
            $table->enum('estado', ['pendiente', 'recibida', 'anulada'])->default('recibida');
            $table->dateTime('fecha');
            
            // Trazabilidad de anulaciones y modificaciones
            $table->dateTime('fecha_anulacion')->nullable();
            $table->foreignId('anulado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->string('motivo_anulacion', 255)->nullable();
            $table->foreignId('compra_original_id')->nullable()->constrained('compras')->nullOnDelete();
            $table->foreignId('reemplazada_por')->nullable()->constrained('compras')->nullOnDelete();
            
            $table->timestamps();
            
            $table->index(['proveedor_id', 'fecha']);
            $table->index('estado');
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
