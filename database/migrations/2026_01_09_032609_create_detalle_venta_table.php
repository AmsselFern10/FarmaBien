<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalle_venta', function (Blueprint $table) {
            $table->id();

            // Relaciones
            $table->foreignId('venta_id')
                ->constrained('ventas')
                ->cascadeOnDelete();

            $table->foreignId('producto_id')
                ->constrained('productos');

            $table->foreignId('lote_id')
                ->constrained('lotes');

            // snapshot del número de lote (para auditoría / histórico)
            $table->string('numero_lote', 100)->nullable();

            // =========================
            // Presentación (capa comercial) - snapshot
            // =========================
            $table->foreignId('presentacion_id')
                ->nullable()
                ->constrained('presentaciones_producto')
                ->nullOnDelete();

            $table->string('tipo_presentacion', 50)->nullable();
            $table->integer('unidades_por_presentacion')->default(1);
            $table->integer('cantidad_presentaciones')->default(1);

            // Cantidad real en unidades base (inventario)
            $table->integer('cantidad_unidades_base')->default(0);

            // =========================
            // Monetarios
            // =========================
            // Precio unitario BRUTO por unidad base
            $table->decimal('precio_unitario', 10, 2);

            // Bruto = cantidad_unidades_base * precio_unitario
            $table->decimal('subtotal_bruto', 10, 2)->default(0);

            // Descuento por línea
            $table->decimal('descuento_porcentaje', 5, 2)->default(0);
            $table->decimal('descuento_monto', 10, 2)->default(0);

            // Subtotal NETO (bruto - descuento)
            $table->decimal('subtotal', 10, 2);

            $table->timestamps();

            // Índices
            $table->index(['venta_id', 'producto_id']);
            $table->index('lote_id');
            $table->index('presentacion_id');
            $table->index('numero_lote');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_venta');
    }
};