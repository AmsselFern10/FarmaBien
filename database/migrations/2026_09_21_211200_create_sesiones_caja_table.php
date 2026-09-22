<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sesiones_caja', function (Blueprint $table) {
            $table->id();
            $table->foreignId('caja_id')->constrained('cajas')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // Cajero que abre la sesión
            
            // Apertura
            $table->decimal('monto_inicial', 12, 2)->default(0);
            $table->dateTime('fecha_apertura');
            $table->text('observaciones_apertura')->nullable();

            // Cierre y Arqueo
            $table->dateTime('fecha_cierre')->nullable();
            $table->foreignId('cerrado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('monto_final_efectivo', 12, 2)->nullable(); // Efectivo recontado / declarado
            $table->decimal('monto_esperado_efectivo', 12, 2)->default(0); // Inicial + Ventas Efectivo + Ingresos - Egresos
            $table->decimal('diferencia_efectivo', 12, 2)->default(0); // monto_final_efectivo - monto_esperado_efectivo

            // Totales por Método de Pago en Ventas
            $table->decimal('total_ventas_efectivo', 12, 2)->default(0);
            $table->decimal('total_ventas_tarjeta', 12, 2)->default(0);
            $table->decimal('total_ventas_transferencia', 12, 2)->default(0);
            $table->decimal('total_ventas_otros', 12, 2)->default(0);
            $table->decimal('total_ventas', 12, 2)->default(0);

            // Movimientos Manuales
            $table->decimal('total_ingresos_manuales', 12, 2)->default(0);
            $table->decimal('total_egresos_manuales', 12, 2)->default(0);

            // Estado y Notas
            $table->enum('estado', ['abierta', 'cerrada'])->default('abierta');
            $table->text('observaciones_cierre')->nullable();
            $table->timestamps();

            $table->index(['caja_id', 'estado']);
            $table->index(['user_id', 'estado']);
            $table->index('fecha_apertura');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sesiones_caja');
    }
};
