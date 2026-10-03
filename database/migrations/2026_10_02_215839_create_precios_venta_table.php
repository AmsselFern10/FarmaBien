<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('precios_venta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
            $table->foreignId('presentacion_id')->nullable()->constrained('presentaciones_producto')->onDelete('cascade');
            $table->decimal('precio', 12, 4)->comment('Precio de venta vigente o historico');
            $table->timestamp('vigente_desde')->useCurrent();
            $table->timestamp('vigente_hasta')->nullable()->default(null)->comment('NULL indica que es el precio vigente');
            $table->text('motivo')->nullable()->comment('Motivo o justificacion del cambio de precio');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index(['producto_id', 'vigente_hasta'], 'idx_pv_prod_vigente');
            $table->index(['producto_id', 'presentacion_id', 'vigente_hasta'], 'idx_pv_prod_pres_vigente');
            $table->index('user_id', 'idx_pv_user');
            $table->index('vigente_desde', 'idx_pv_desde');
        });

        // Población inicial / Backfill histórico desde los precios actuales de productos y presentaciones
        try {
            $primerUsuario = DB::table('users')->value('id');
            $ahora = now();

            // 1. Precios base de productos
            $productos = DB::table('productos')
                ->whereNull('deleted_at')
                ->select(['id', 'precio_venta', 'created_at'])
                ->get();

            $insertsBase = [];
            foreach ($productos as $p) {
                $precio = (float)($p->precio_venta ?? 0);
                if ($precio > 0) {
                    $insertsBase[] = [
                        'producto_id'      => $p->id,
                        'presentacion_id'  => null,
                        'precio'           => $precio,
                        'vigente_desde'    => $p->created_at ?? $ahora,
                        'vigente_hasta'    => null,
                        'motivo'           => 'Precio base inicial de catálogo',
                        'user_id'          => $primerUsuario,
                        'created_at'       => $ahora,
                        'updated_at'       => $ahora,
                    ];
                }
            }

            if (!empty($insertsBase)) {
                foreach (array_chunk($insertsBase, 200) as $chunk) {
                    DB::table('precios_venta')->insert($chunk);
                }
            }

            // 2. Precios de presentaciones de productos
            $presentaciones = DB::table('presentaciones_producto')
                ->select(['id', 'producto_id', 'precio_venta', 'nombre', 'created_at'])
                ->get();

            $insertsPres = [];
            foreach ($presentaciones as $pres) {
                $precioPres = (float)($pres->precio_venta ?? 0);
                if ($precioPres > 0) {
                    $insertsPres[] = [
                        'producto_id'      => $pres->producto_id,
                        'presentacion_id'  => $pres->id,
                        'precio'           => $precioPres,
                        'vigente_desde'    => $pres->created_at ?? $ahora,
                        'vigente_hasta'    => null,
                        'motivo'           => 'Precio inicial de presentación: ' . ($pres->nombre ?? 'Presentación'),
                        'user_id'          => $primerUsuario,
                        'created_at'       => $ahora,
                        'updated_at'       => $ahora,
                    ];
                }
            }

            if (!empty($insertsPres)) {
                foreach (array_chunk($insertsPres, 200) as $chunk) {
                    DB::table('precios_venta')->insert($chunk);
                }
            }
        } catch (\Throwable $e) {
            // Continuar si la base de datos estaba limpia
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('precios_venta');
    }
};
