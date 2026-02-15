<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compras', function (Blueprint $table) {
            $table->id();

            // =========================
            // Relaciones principales
            // =========================
            $table->foreignId('proveedor_id')->constrained('proveedores');
            $table->foreignId('user_id')->constrained('users');

            // =========================
            // Desglose monetario
            // =========================
            $table->decimal('subtotal_bruto', 10, 2)->default(0);
            $table->decimal('descuento_porcentaje', 5, 2)->default(0);
            $table->decimal('descuento_monto_total', 10, 2)->default(0);

            // Total NETO (después de descuentos)
            $table->decimal('total', 10, 2)->default(0);

            // =========================
            // Observaciones
            // =========================
            $table->text('observaciones')->nullable();

            // =========================
            // Estado / fechas
            // =========================
            $table->enum('estado', ['recibida', 'anulada'])->default('recibida');
            $table->dateTime('fecha')->useCurrent();

            // =========================
            // Anulación
            // =========================
            $table->foreignId('anulado_por')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->dateTime('fecha_anulacion')->nullable();
            $table->text('motivo_anulacion')->nullable();

            // =========================
            // Trazabilidad de modificaciones
            // =========================
            $table->foreignId('reemplazada_por')
                ->nullable()
                ->constrained('compras')
                ->nullOnDelete();

            $table->foreignId('compra_original_id')
                ->nullable()
                ->constrained('compras')
                ->nullOnDelete();

            $table->timestamps();

            // =========================
            // Índices
            // =========================
            $table->index(['fecha', 'estado']);
            $table->index('proveedor_id');
            $table->index('reemplazada_por');
            $table->index('compra_original_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compras');
    }
};