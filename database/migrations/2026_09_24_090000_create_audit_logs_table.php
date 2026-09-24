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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('modulo', 50); // productos, categorias, laboratorios, ventas, compras, cajas, inventario, errores
            $table->string('accion', 50); // crear, editar, eliminar, anular, ajuste, excepcion, apertura, cierre
            $table->string('descripcion', 255);
            $table->json('detalles')->nullable();
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('created_at', 'idx_audit_logs_created_at');
            $table->index(['modulo', 'accion'], 'idx_audit_logs_modulo_accion');
            $table->index(['user_id', 'created_at'], 'idx_audit_logs_user_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
