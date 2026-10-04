<?php

namespace Database\Factories;

use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Laboratorio;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductoFactory extends Factory
{
    protected $model = Producto::class;

    public function definition(): array
    {
        return [
            'codigo_barra'       => fake()->unique()->ean13(),
            'nombre'             => fake()->unique()->words(3, true),
            'principio_activo'   => fake()->word(),
            'concentracion'      => '500mg',
            'forma_farmaceutica' => 'Tabletas',
            'descripcion'        => fake()->sentence(),
            'categoria_id'       => Categoria::factory(),
            'laboratorio_id'     => Laboratorio::factory(),
            'registro_sanitario' => 'REG-' . fake()->numerify('####-##'),
            'tipo_control'       => Producto::TIPO_VENTA_LIBRE,
            'precio_compra'      => 10.00,
            'precio_venta'       => 15.00,
            'stock_minimo'       => 5,
            'ubicacion'          => 'Estante A1',
            'requiere_receta'    => false,
            'activo'             => true,
        ];
    }

    public function controlado(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo_control'    => Producto::TIPO_CONTROLADO,
            'requiere_receta' => true,
        ]);
    }

    public function inactivo(): static
    {
        return $this->state(fn (array $attributes) => [
            'activo' => false,
        ]);
    }
}
