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

            // Vínculos a la venta
            $table->foreignId('venta_id')->constrained('ventas')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos');
            $table->foreignId('lote_id')->nullable()->constrained('lotes')->nullOnDelete();

            // Nivel de control del medicamento (copiado en el momento del despacho)
            $table->tinyInteger('nivel_controlado')->unsigned()->default(1);

            // Datos del paciente
            $table->string('paciente_nombre', 150);
            $table->string('paciente_cedula', 30)->nullable();
            $table->tinyInteger('paciente_edad')->unsigned()->nullable();

            // Datos del médico prescriptor
            $table->string('medico_nombre', 150);
            $table->string('medico_cedula', 30)->nullable();
            $table->string('medico_num_registro', 60)->nullable()
                  ->comment('Número de registro MINSA del médico');

            // Datos del despacho
            $table->string('diagnostico', 255)->nullable();
            $table->decimal('cantidad', 10, 2);
            $table->string('unidad', 50)->default('unidad');

            // Quién despachó
            $table->foreignId('user_id')->constrained('users');

            $table->timestamps();

            // Índices para consultas frecuentes del libro de control
            $table->index(['venta_id'], 'idx_rvc_venta');
            $table->index(['producto_id', 'created_at'], 'idx_rvc_producto_fecha');
            $table->index(['nivel_controlado', 'created_at'], 'idx_rvc_nivel_fecha');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registros_venta_controlados');
    }
};
