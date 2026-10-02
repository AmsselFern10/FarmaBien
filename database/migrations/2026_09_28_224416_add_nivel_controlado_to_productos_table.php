<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // No-op: Se unifica la clasificación sanitaria en tipo_control ('venta_libre', 'controlado')
    }

    public function down(): void
    {
        // No-op
    }
};
