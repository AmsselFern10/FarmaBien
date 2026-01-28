<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ForceAddPrecioCompraToProductosTable extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE productos
            ADD COLUMN precio_compra DECIMAL(10,2)
            NULL
            AFTER precio_venta
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE productos
            DROP COLUMN precio_compra
        ");
    }
}
