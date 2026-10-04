<?php

namespace Database\Factories;

use App\Models\Cliente;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClienteFactory extends Factory
{
    protected $model = Cliente::class;

    public function definition(): array
    {
        return [
            'nombre'    => fake()->name(),
            'documento' => fake()->unique()->numerify('001-######-####') . fake()->randomLetter(),
            'telefono'  => '+505 ' . fake()->numerify('8###-####'),
            'email'     => fake()->unique()->safeEmail(),
            'direccion' => fake()->address(),
            'activo'    => true,
        ];
    }
}
