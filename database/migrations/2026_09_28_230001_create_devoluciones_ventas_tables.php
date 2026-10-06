<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devoluciones_ventas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venta_id')->constrained('ventas')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('sesion_caja_id')->nullable()->constrained('sesiones_caja')->nullOnDelete();
            
            $table->string('numero_devolucion', 50)->unique();
            $table->enum('tipo', ['parcial', 'total'])->default('parcial');
            $table->string('motivo', 100);
            $table->text('observaciones')->nullable();
            
            $table->decimal('monto_total', 10, 2)->default(0);
            $table->enum('metodo_reembolso', ['efectivo', 'transferencia', 'tarjeta', 'saldo_favor', 'sin_reembolso'])->default('efectivo');
            $table->string('banco', 100)->nullable();
            $table->string('numero_transaccion', 100)->nullable();
            
            $table->enum('estado', ['completada', 'anulada'])->default('completada');
            $table->dateTime('fecha');
            $table->timestamps();

            $table->index(['venta_id', 'fecha']);
            $table->index('numero_devolucion');
            $table->index('estado');
        });

        Schema::create('detalle_devoluciones_ventas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('devolucion_venta_id')->constrained('devoluciones_ventas')->cascadeOnDelete();
            $table->foreignId('detalle_venta_id')->constrained('detalle_venta')->restrictOnDelete();
            $table->foreignId('producto_id')->constrained('productos')->restrictOnDelete();
            $table->foreignId('lote_id')->constrained('lotes')->restrictOnDelete();
            
            $table->integer('cantidad'); // Cantidad devuelta
            $table->integer('unidades_por_presentacion')->default(1);
            $table->integer('cantidad_unidades_base'); // Unidades base a reingresar o descartar
            $table->decimal('precio_unitario', 10, 2);
            $table->decimal('subtotal', 10, 2);
            
            $table->boolean('reingresa_a_stock')->default(true);
            $table->enum('estado_producto', ['buen_estado', 'danado', 'vencido'])->default('buen_estado');
            $table->timestamps();

            $table->index(['devolucion_venta_id', 'producto_id'], 'det_dev_prod_idx');
        });

        if (Schema::hasTable('registros_venta_controlados')) {
            Schema::table('registros_venta_controlados', function (Blueprint $table) {
                $table->foreign('devolucion_id')->references('id')->on('devoluciones_ventas')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('registros_venta_controlados')) {
            Schema::table('registros_venta_controlados', function (Blueprint $table) {
                $table->dropForeign(['devolucion_id']);
            });
        }

        Schema::dropIfExists('detalle_devoluciones_ventas');
        Schema::dropIfExists('devoluciones_ventas');
    }
};
