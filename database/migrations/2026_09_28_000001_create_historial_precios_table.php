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
        Schema::create('historial_precios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
            $table->foreignId('proveedor_id')->constrained('proveedores')->onDelete('cascade');
            $table->foreignId('compra_id')->nullable()->constrained('compras')->onDelete('set null');
            $table->foreignId('presentacion_id')->nullable()->constrained('presentaciones_producto')->onDelete('set null');
            $table->string('tipo_presentacion')->default('Unidad Base');
            $table->unsignedInteger('unidades_por_presentacion')->default(1);
            $table->decimal('precio_compra', 12, 4)->comment('Precio registrado por presentación o unidad comprada');
            $table->decimal('precio_unitario_base', 12, 4)->comment('Precio normalizado por unidad base individual');
            $table->string('tipo', 30)->default('compra')->comment('compra, cotizacion, manual');
            $table->datetime('fecha');
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index(['producto_id', 'proveedor_id']);
            $table->index(['producto_id', 'fecha']);
            $table->index(['proveedor_id', 'fecha']);
            $table->index('tipo');
        });

        // Población inicial / Backfill histórico desde compras y detalles existentes
        try {
            $compras = DB::table('detalle_compra')
                ->join('compras', 'detalle_compra.compra_id', '=', 'compras.id')
                ->where('compras.estado', '!=', 'anulada')
                ->select([
                    'detalle_compra.producto_id',
                    'compras.proveedor_id',
                    'detalle_compra.compra_id',
                    'detalle_compra.presentacion_id',
                    'detalle_compra.tipo_presentacion',
                    'detalle_compra.unidades_por_presentacion',
                    'detalle_compra.precio_unitario as precio_compra',
                    'compras.fecha',
                    'compras.created_at',
                    'compras.updated_at'
                ])
                ->get();

            $insertData = [];
            foreach ($compras as $item) {
                $factor = max(1, (int)($item->unidades_por_presentacion ?? 1));
                $precioUnitarioBase = round((float)$item->precio_compra / $factor, 4);

                $insertData[] = [
                    'producto_id'               => $item->producto_id,
                    'proveedor_id'              => $item->proveedor_id,
                    'compra_id'                 => $item->compra_id,
                    'presentacion_id'           => $item->presentacion_id,
                    'tipo_presentacion'         => $item->tipo_presentacion ?? 'Unidad Base',
                    'unidades_por_presentacion' => $factor,
                    'precio_compra'             => $item->precio_compra,
                    'precio_unitario_base'      => $precioUnitarioBase,
                    'tipo'                      => 'compra',
                    'fecha'                     => $item->fecha ?? $item->created_at ?? now(),
                    'observaciones'             => 'Migración histórica de compra inicial #' . $item->compra_id,
                    'created_at'                => $item->created_at ?? now(),
                    'updated_at'                => $item->updated_at ?? now(),
                ];
            }

            if (!empty($insertData)) {
                // Insertar en bloques para evitar exceder límites
                foreach (array_chunk($insertData, 200) as $chunk) {
                    DB::table('historial_precios')->insert($chunk);
                }
            }
        } catch (\Throwable $e) {
            // Continuar si la tabla detalle_compra aún no tenía datos
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historial_precios');
    }
};
