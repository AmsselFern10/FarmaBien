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

            // Idempotencia (evita doble submit)
            $table->uuid('uuid')->unique();

            $table->foreignId('producto_id')->constrained('productos');
            $table->foreignId('lote_id')->constrained('lotes');
            $table->foreignId('user_id')->constrained('users');

            $table->enum('tipo', ['entrada', 'salida', 'ajuste']);

            // Cantidad en UNIDAD BASE (entero).
            // Convención: entrada = +N, salida = -N, ajuste = +/-N
            $table->integer('cantidad');

            // Saldos del lote al momento del movimiento (kardex auditable)
            $table->integer('saldo_anterior')->nullable();
            $table->integer('saldo_nuevo')->nullable();

            // Reversa (para correcciones sin editar movimientos)
            $table->foreignId('reversa_de_id')->nullable()
                ->constrained('movimientos_inventario')
                ->nullOnDelete();

            // Referencia al origen (compatibilidad con tu esquema actual)
            $table->string('origen')->nullable(); // 'venta', 'compra', 'ajuste', etc.
            $table->unsignedBigInteger('origen_id')->nullable();

            // Costos (sobre todo en entradas)
            $table->decimal('costo_unitario', 12, 6)->nullable();
            $table->decimal('costo_total', 12, 2)->nullable();

            $table->string('motivo', 200)->nullable();

            // Fecha operativa del movimiento (no depende de created_at)
            $table->dateTime('fecha_movimiento')->useCurrent();

            $table->timestamps();

            $table->index(['producto_id', 'fecha_movimiento']);
            $table->index(['lote_id', 'tipo']);
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
