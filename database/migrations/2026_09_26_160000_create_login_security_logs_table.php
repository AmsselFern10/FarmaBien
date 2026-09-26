<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('login_security_logs', function (Blueprint $table) {
            $table->id();
            $table->string('ip', 45)->index();
            $table->string('email')->nullable()->index();
            $table->string('user_agent', 500)->nullable();
            $table->enum('evento', ['intento_fallido', 'bloqueado', 'desbloqueado', 'exitoso']);
            $table->unsignedTinyInteger('intentos_acumulados')->default(0);
            $table->timestamp('bloqueado_hasta')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('login_security_logs');
    }
};
