<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalle_compra', function (Blueprint $table) {
            $table->id();

            // =========================
            // Relaciones
            // =========================
            $table->foreignId('compra_id')
                ->constrained('compras')
                ->cascadeOnDelete();

            $table->foreignId('producto_id')
                ->constrained('productos');

            $table->foreignId('lote_id')
                ->constrained('lotes');

            // =========================
            // Presentación (snapshot)
            // =========================
            $table->foreignId('presentacion_id')
                ->nullable()
                ->constrained('presentaciones_producto')
                ->nullOnDelete();

            $table->string('tipo_presentacion', 50)->nullable();
            $table->integer('unidades_por_presentacion')->default(1);
            $table->integer('cantidad_presentaciones')->default(1);

            // Cantidad real (unidades base) - calculada por Laravel/Service
            $table->integer('cantidad_unidades_base')->default(0);

            // =========================
            // Monetarios
            // =========================
            // Precio unitario BRUTO por unidad base
            $table->decimal('precio_unitario', 10, 2);

            // Bruto = cantidad_unidades_base * precio_unitario
            $table->decimal('subtotal_bruto', 10, 2)->default(0);

            // Descuento por producto (% y monto)
            $table->decimal('descuento_porcentaje', 5, 2)->default(0);
            $table->decimal('descuento_monto', 10, 2)->default(0);

            // subtotal NETO (bruto - descuento_monto)
            $table->decimal('subtotal', 10, 2);

            $table->timestamps();

            // =========================
            // Índices
            // =========================
            $table->index('compra_id');
            $table->index('lote_id');
            $table->index('producto_id');
            $table->index('presentacion_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_compra');
    }
};