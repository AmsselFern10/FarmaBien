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
        Schema::create('lotes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('producto_id')->constrained('productos');

            // Mantengo estas llaves por compatibilidad con tu proyecto actual.
            // (A futuro, la trazabilidad formal de la compra debería vivir en movimientos_inventario.)
            $table->foreignId('compra_id')->constrained('compras');
            $table->foreignId('proveedor_id')->constrained('proveedores');

            $table->string('numero_lote', 50);
            $table->date('fecha_vencimiento');

            // Fecha operativa de ingreso del lote (no depende del created_at)
            $table->dateTime('fecha_ingreso')->useCurrent();

            // Acumulado de entradas (útil para auditoría). Se actualiza SOLO por movimientos tipo 'entrada'.
            $table->integer('stock_inicial')->default(0);

            // Stock vigente del lote (saldo). Se actualiza SOLO por movimientos_inventario.
            $table->integer('stock_actual')->default(0);

            // Costo unitario base (unidad mínima) asociado al lote para valorización.
            $table->decimal('precio_compra', 12, 6)->default(0);

            // Estado operativo del lote (no reemplaza 'activo', pero ayuda a filtrar y auditar)
            // activo | agotado | vencido | bloqueado | inactivo
            $table->string('estado', 20)->default('activo');

            // Bloqueo sanitario/operativo
            $table->dateTime('bloqueado_at')->nullable();
            $table->foreignId('bloqueado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->string('motivo_bloqueo', 255)->nullable();

            $table->boolean('activo')->default(true);

            $table->timestamps();

            // Un número de lote debe ser único por producto
            $table->unique(['numero_lote', 'producto_id']);

            $table->index(['producto_id', 'activo', 'estado', 'fecha_vencimiento']);
            $table->index(['stock_actual']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lotes');
    }
};
