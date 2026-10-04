<?php

namespace Database\Factories;

use App\Models\Proveedor;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProveedorFactory extends Factory
{
    protected $model = Proveedor::class;

    public function definition(): array
    {
        return [
            'nombre'    => fake()->company() . ' S.A.',
            'ruc'       => 'J' . fake()->unique()->numerify('031000######'),
            'contacto'  => fake()->name(),
            'telefono'  => '+505 ' . fake()->numerify('2###-####'),
            'email'     => fake()->unique()->companyEmail(),
            'direccion' => fake()->address(),
            'activo'    => true,
        ];
    }
}
