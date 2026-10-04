<?php

namespace Database\Factories;

use App\Models\Laboratorio;
use Illuminate\Database\Eloquent\Factories\Factory;

class LaboratorioFactory extends Factory
{
    protected $model = Laboratorio::class;

    public function definition(): array
    {
        return [
            'nombre'      => 'Laboratorios ' . fake()->unique()->company(),
            'codigo'      => 'LAB-' . strtoupper(fake()->unique()->lexify('???')),
            'contacto'    => fake()->name(),
            'telefono'    => '+505 ' . fake()->numerify('2###-####'),
            'email'       => fake()->unique()->companyEmail(),
            'pais_origen' => fake()->country(),
            'activo'      => true,
        ];
    }
}
