<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devoluciones_compra', function (Blueprint $table) {
            $table->id();
            $table->string('numero_devolucion', 30)->unique(); // DEV-COMP-000001
            $table->unsignedBigInteger('proveedor_id');
            $table->unsignedBigInteger('compra_id')->nullable(); // compra de origen (optional)
            $table->unsignedBigInteger('usuario_id');
            $table->enum('estado', ['pendiente', 'enviada', 'confirmada', 'rechazada'])->default('pendiente');
            $table->text('motivo');                  // motivo general
            $table->decimal('total_devolucion', 12, 2)->default(0);
            $table->timestamp('fecha_envio')->nullable();
            $table->timestamps();

            $table->foreign('proveedor_id')->references('id')->on('proveedores');
            $table->foreign('compra_id')->references('id')->on('compras')->nullOnDelete();
            $table->foreign('usuario_id')->references('id')->on('users');
        });

        Schema::create('detalles_devolucion_compra', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('devolucion_compra_id');
            $table->unsignedBigInteger('lote_id');
            $table->unsignedBigInteger('producto_id');
            $table->integer('cantidad');             // unidades devueltas
            $table->decimal('precio_unitario', 10, 2)->default(0); // costo unitario al momento
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->string('motivo_detalle', 300)->nullable(); // por item: vencido, dañado, error pedido
            $table->timestamps();

            $table->foreign('devolucion_compra_id')->references('id')->on('devoluciones_compra')->onDelete('cascade');
            $table->foreign('lote_id')->references('id')->on('lotes');
            $table->foreign('producto_id')->references('id')->on('productos');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalles_devolucion_compra');
        Schema::dropIfExists('devoluciones_compra');
    }
};
