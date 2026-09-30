<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ordenes_compras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proveedor_id')->constrained('proveedores')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            
            $table->string('numero_orden', 50)->unique();
            $table->date('fecha_emision');
            $table->date('fecha_esperada_entrega')->nullable();
            
            $table->enum('estado', ['borrador', 'enviada', 'recibida_parcial', 'recibida_total', 'cancelada'])->default('borrador');
            $table->enum('condicion_pago', ['contado', 'credito'])->default('contado');
            $table->integer('dias_credito')->default(0);
            
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('impuesto', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            
            $table->text('observaciones')->nullable();
            $table->foreignId('compra_id')->nullable()->constrained('compras')->nullOnDelete();
            
            $table->timestamps();

            $table->index(['proveedor_id', 'fecha_emision']);
            $table->index('estado');
        });

        Schema::create('detalle_ordenes_compras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orden_compra_id')->constrained('ordenes_compras')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos')->restrictOnDelete();
            
            $table->integer('cantidad_solicitada');
            $table->integer('cantidad_recibida')->default(0);
            $table->decimal('precio_unitario_estimado', 10, 2)->default(0);
            $table->decimal('subtotal', 10, 2)->default(0);
            
            $table->timestamps();

            $table->index(['orden_compra_id', 'producto_id'], 'det_oc_prod_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_ordenes_compras');
        Schema::dropIfExists('ordenes_compras');
    }
};
