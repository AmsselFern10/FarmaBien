<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();

            // Relaciones
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users');

            // Anulación
            $table->foreignId('anulado_por')->nullable()->constrained('users')->nullOnDelete();

            // Desglose monetario
            $table->decimal('subtotal_bruto', 10, 2)->default(0);
            $table->decimal('descuento_porcentaje', 5, 2)->default(0);
            $table->decimal('descuento_monto_total', 10, 2)->default(0);

            // Total NETO (bruto - descuentos)
            $table->decimal('total', 10, 2)->default(0);

            // Comercial
            $table->string('metodo_pago', 30);

            // : caja / pago
            $table->decimal('monto_recibido', 10, 2)->nullable();
            $table->decimal('cambio', 10, 2)->nullable();
            $table->string('referencia_pago', 100)->nullable();

            $table->text('observaciones')->nullable();

            // Estado / fechas
            $table->enum('estado', ['completada', 'anulada'])->default('completada');
            $table->dateTime('fecha')->useCurrent();
            $table->dateTime('fecha_anulacion')->nullable();
            $table->text('motivo_anulacion')->nullable();

            // Trazabilidad de modificaciones
            $table->foreignId('reemplazada_por')->nullable()->constrained('ventas')->nullOnDelete();
            $table->foreignId('venta_original_id')->nullable()->constrained('ventas')->nullOnDelete();

            $table->timestamps();

            // Índices
            $table->index(['fecha', 'estado']);
            $table->index('user_id');
            $table->index('reemplazada_por');
            $table->index('venta_original_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};