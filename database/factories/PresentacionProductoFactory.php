<?php

namespace Database\Factories;

use App\Models\PresentacionProducto;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

class PresentacionProductoFactory extends Factory
{
    protected $model = PresentacionProducto::class;

    public function definition(): array
    {
        return [
            'producto_id'               => Producto::factory(),
            'nombre'                    => 'Caja x 30 tabletas',
            'descripcion'               => 'Caja dispensadora',
            'unidades_por_presentacion' => 30,
            'precio_compra'             => 300.00,
            'precio_venta'              => 450.00,
            'codigo_barras'             => fake()->ean13(),
            'es_unidad_base'            => false,
            'activo'                    => true,
            'orden'                     => 1,
        ];
    }

    public function unidadBase(): static
    {
        return $this->state(fn (array $attributes) => [
            'nombre'                    => 'Unidad Base',
            'descripcion'               => 'Pastilla individual',
            'unidades_por_presentacion' => 1,
            'es_unidad_base'            => true,
            'orden'                     => 0,
        ]);
    }

    public function inactivo(): static
    {
        return $this->state(fn (array $attributes) => [
            'activo' => false,
        ]);
    }
}
