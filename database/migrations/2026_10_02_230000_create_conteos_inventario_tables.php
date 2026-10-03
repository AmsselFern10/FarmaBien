<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conteos_inventario', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 200);                    // "Conteo Cíclico Oct 2026"
            $table->enum('estado', ['borrador', 'en_proceso', 'completado', 'cancelado'])->default('borrador');
            $table->text('notas')->nullable();
            $table->unsignedBigInteger('usuario_id');          // quien inició
            $table->unsignedBigInteger('aprobado_por_id')->nullable(); // quien aprobó
            $table->integer('total_lotes')->default(0);
            $table->integer('lotes_contados')->default(0);
            $table->integer('diferencia_total_unidades')->default(0);
            $table->timestamp('iniciado_en')->nullable();
            $table->timestamp('completado_en')->nullable();
            $table->timestamps();

            $table->foreign('usuario_id')->references('id')->on('users');
            $table->foreign('aprobado_por_id')->references('id')->on('users');
        });

        Schema::create('detalles_conteo', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('conteo_id');
            $table->unsignedBigInteger('lote_id');
            $table->unsignedBigInteger('producto_id');
            $table->integer('stock_sistema');     // snapshot en el momento de crear el conteo
            $table->integer('stock_fisico')->nullable();   // lo que el usuario cuenta físicamente
            $table->integer('diferencia')->default(0);    // stock_fisico - stock_sistema (negativo = faltante)
            $table->boolean('ajustado')->default(false);  // true si ya se aplicó el ajuste en Kardex
            $table->timestamps();

            $table->foreign('conteo_id')->references('id')->on('conteos_inventario')->onDelete('cascade');
            $table->foreign('lote_id')->references('id')->on('lotes');
            $table->foreign('producto_id')->references('id')->on('productos');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalles_conteo');
        Schema::dropIfExists('conteos_inventario');
    }
};
