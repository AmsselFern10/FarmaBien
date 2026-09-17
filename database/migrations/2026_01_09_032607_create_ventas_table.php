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
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            
            // Facturación / Comprobante
            $table->enum('tipo_comprobante', ['ticket', 'boleta', 'factura'])->default('ticket');
            $table->string('serie', 20)->nullable();
            $table->string('numero_comprobante', 50)->nullable();
            
            // Importes
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('descuento', 10, 2)->default(0);
            $table->decimal('impuesto', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            
            $table->enum('metodo_pago', ['efectivo', 'tarjeta', 'transferencia', 'mixto'])->default('efectivo');
            $table->enum('estado', ['completada', 'anulada'])->default('completada');
            $table->dateTime('fecha');
            
            // Trazabilidad de anulaciones y modificaciones
            $table->dateTime('fecha_anulacion')->nullable();
            $table->foreignId('anulado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->string('motivo_anulacion', 255)->nullable();
            $table->foreignId('venta_original_id')->nullable()->constrained('ventas')->nullOnDelete();
            $table->foreignId('reemplazada_por')->nullable()->constrained('ventas')->nullOnDelete();
            
            $table->timestamps();
            
            $table->index(['fecha', 'estado']);
            $table->index(['user_id', 'fecha']);
            $table->index('cliente_id');
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
