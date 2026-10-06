<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registros_venta_controlados', function (Blueprint $table) {
            $table->id();

            // Tipo de operación / movimiento fiscalizado
            $table->string('tipo_movimiento', 40)->default('VENTA');

            // Vínculos a documentos de origen
            $table->foreignId('venta_id')->nullable()->constrained('ventas')->nullOnDelete();
            $table->unsignedBigInteger('devolucion_id')->nullable();
            $table->foreignId('compra_id')->nullable()->constrained('compras')->nullOnDelete();
            $table->foreignId('movimiento_inventario_id')->nullable()->constrained('movimientos_inventario')->nullOnDelete();

            $table->foreignId('producto_id')->constrained('productos');
            $table->foreignId('lote_id')->nullable()->constrained('lotes')->nullOnDelete();

            // Nivel de control / tipo (1=controlado por defecto)
            $table->tinyInteger('nivel_controlado')->unsigned()->default(1);

            // Datos del paciente / cliente / beneficiario
            $table->string('paciente_nombre', 150)->nullable();
            $table->string('paciente_cedula', 30)->nullable();
            $table->tinyInteger('paciente_edad')->unsigned()->nullable();

            // Datos del médico prescriptor
            $table->string('medico_nombre', 150)->nullable();
            $table->string('medico_cedula', 30)->nullable();
            $table->string('medico_num_registro', 60)->nullable()
                  ->comment('Número de registro MINSA del médico');

            // Omisión, Justificación MINSA y Evidencia Fotográfica
            $table->text('motivo_omision')->nullable()
                  ->comment('Justificación de omisión de receta o motivo oficial del movimiento');
            $table->string('ruta_foto_receta')->nullable()
                  ->comment('Ruta de imagen/documento de la receta física o evidencia');

            // Datos del movimiento
            $table->string('diagnostico', 255)->nullable();
            $table->decimal('cantidad', 10, 2);
            $table->string('unidad', 50)->default('unidad');

            // Quién registró / despachó
            $table->foreignId('user_id')->constrained('users');

            $table->timestamps();

            // Índices para consultas frecuentes del libro de control
            $table->index(['tipo_movimiento', 'created_at'], 'idx_rvc_tipo_fecha');
            $table->index(['venta_id'], 'idx_rvc_venta');
            $table->index(['devolucion_id'], 'idx_rvc_devolucion');
            $table->index(['producto_id', 'created_at'], 'idx_rvc_producto_fecha');
            $table->index(['lote_id', 'created_at'], 'idx_rvc_lote_fecha');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registros_venta_controlados');
    }
};
