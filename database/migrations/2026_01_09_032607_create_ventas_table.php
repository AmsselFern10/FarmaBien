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
            $table->foreignId('sesion_caja_id')->nullable()->constrained('sesiones_caja')->nullOnDelete();

            // Facturación / Comprobante
            $table->enum('tipo_comprobante', ['ticket', 'boleta', 'factura', 'credito_fiscal'])->default('ticket');
            $table->string('serie', 20)->nullable();
            $table->string('numero_comprobante', 50)->nullable();
            $table->string('idempotency_key', 64)->nullable()->unique();

            // Importes
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('descuento', 10, 2)->default(0);
            $table->decimal('impuesto', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);

            // POS — pago
            $table->decimal('monto_recibido', 10, 2)->nullable();
            $table->decimal('cambio', 10, 2)->nullable();
            $table->text('notas')->nullable();

            $table->enum('metodo_pago', ['efectivo', 'tarjeta', 'transferencia', 'mixto'])->default('efectivo');
            $table->enum('estado', ['completada', 'anulada'])->default('completada');
            $table->dateTime('fecha');

            // Trazabilidad de anulaciones
            $table->dateTime('fecha_anulacion')->nullable();
            $table->foreignId('anulado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->string('motivo_anulacion', 255)->nullable();
            $table->foreignId('venta_original_id')->nullable()->constrained('ventas')->nullOnDelete();
            $table->foreignId('reemplazada_por')->nullable()->constrained('ventas')->nullOnDelete();

            $table->timestamps();

            // Índices — sin duplicar los _foreign automáticos de MySQL/InnoDB
            $table->index(['fecha', 'estado'],           'idx_ventas_fecha_estado');
            $table->index(['user_id', 'fecha'],          'idx_ventas_user_fecha');
            $table->index(['estado', 'fecha'],           'idx_ventas_estado_fecha');
            $table->index(['cliente_id', 'fecha'],       'idx_ventas_cliente_fecha');
            $table->index(['sesion_caja_id', 'estado'],  'idx_ventas_sesion_estado');
            $table->index('numero_comprobante',          'idx_ventas_numero_comp');
            $table->index('metodo_pago',                 'idx_ventas_metodo_pago');
            $table->index('tipo_comprobante',            'idx_ventas_tipo_comp');
            $table->index('anulado_por',                 'idx_ventas_anulado_por');
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
