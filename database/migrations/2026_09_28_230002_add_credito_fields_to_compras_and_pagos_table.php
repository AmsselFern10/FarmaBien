<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('compras', function (Blueprint $table) {
            $table->enum('condicion_pago', ['contado', 'credito'])->default('contado')->after('total');
            $table->integer('dias_credito')->default(0)->after('condicion_pago');
            $table->date('fecha_vencimiento_pago')->nullable()->after('dias_credito');
            $table->decimal('saldo_pendiente', 10, 2)->default(0)->after('fecha_vencimiento_pago');
            $table->enum('estado_pago', ['pagado', 'pendiente', 'parcial', 'vencido'])->default('pagado')->after('saldo_pendiente');
            
            $table->index(['condicion_pago', 'estado_pago']);
            $table->index('fecha_vencimiento_pago');
        });

        Schema::create('pagos_cuentas_por_pagar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('compra_id')->constrained('compras')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('sesion_caja_id')->nullable()->constrained('sesiones_caja')->nullOnDelete();
            
            $table->string('numero_pago', 50)->unique();
            $table->decimal('monto', 10, 2);
            $table->enum('metodo_pago', ['efectivo', 'transferencia', 'cheque', 'otro'])->default('efectivo');
            $table->string('banco', 100)->nullable();
            $table->string('numero_referencia', 100)->nullable();
            $table->dateTime('fecha_pago');
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index(['compra_id', 'fecha_pago']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos_cuentas_por_pagar');
        Schema::table('compras', function (Blueprint $table) {
            $table->dropIndex(['condicion_pago', 'estado_pago']);
            $table->dropIndex(['fecha_vencimiento_pago']);
            $table->dropColumn([
                'condicion_pago',
                'dias_credito',
                'fecha_vencimiento_pago',
                'saldo_pendiente',
                'estado_pago',
            ]);
        });
    }
};
