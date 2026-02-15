<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PresentacionesProductoSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Insertar Categorías Predeterminadas
        $categorias = [
            ['nombre' => 'General', 'descripcion' => 'Categoría base'],
            ['nombre' => 'Farmacia', 'descripcion' => 'Productos médicos'],
            ['nombre' => 'Cuidado Personal', 'descripcion' => 'Aseo y belleza'],
        ];

        foreach ($categorias as $cat) {
            DB::table('categorias')->updateOrInsert(['nombre' => $cat['nombre']], $cat);
        }

        // 2. Necesitamos UN producto para que la tabla presentaciones no dé error
        // Creamos un producto "MOLDE"
        $idProductoMolde = DB::table('productos')->insertGetId([
            'nombre' => 'PRODUCTO PLANTILLA',
            'categoria_id' => DB::table('categorias')->first()->id,
            'precio_compra' => 10,
            'precio_venta' => 15,
            'stock_minimo' => 20,
            'activo' => 1, // Desactivado para que no se venda
            'created_at' => now(),
        ]);

        // 3. Insertar las Presentaciones que quieres usar siempre
        $presentaciones = [
            [
                'producto_id' => $idProductoMolde,
                'nombre' => 'Unidad',
                'descripcion' => 'Venta por unidad individual',
                'unidades_por_presentacion' => 1,
                'precio_sugerido' => 0,
                'activo' => 1,
                'orden' => 1
            ],
            [
                'producto_id' => $idProductoMolde,
                'nombre' => 'Blíster x 10',
                'descripcion' => 'Paquete de 10 unidades',
                'unidades_por_presentacion' => 10,
                'precio_sugerido' => 0,
                'activo' => 1,
                'orden' => 2
            ],
            [
                'producto_id' => $idProductoMolde,
                'nombre' => 'Caja x 100',
                'descripcion' => 'Caja cerrada de 100 unidades',
                'unidades_por_presentacion' => 100,
                'precio_sugerido' => 0,
                'activo' => 1,
                'orden' => 3
            ]
        ];

        foreach ($presentaciones as $pres) {
            DB::table('presentaciones_producto')->insert(array_merge($pres, [
                'created_at' => now(),
                'updated_at' => now()
            ]));
        }

        $this->command->info('Categorías y Presentaciones Predeterminadas creadas.');
    }
}