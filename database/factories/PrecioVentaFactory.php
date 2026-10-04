<?php

namespace Database\Factories;

use App\Models\PrecioVenta;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PrecioVentaFactory extends Factory
{
    protected $model = PrecioVenta::class;

    public function definition(): array
    {
        return [
            'producto_id'     => Producto::factory(),
            'presentacion_id' => null,
            'precio'          => 20.00,
            'vigente_desde'   => now()->subDays(5),
            'vigente_hasta'   => null,
            'motivo'          => 'Ajuste regular de catálogo',
            'user_id'         => User::factory(),
        ];
    }

    public function historico(): static
    {
        return $this->state(fn (array $attributes) => [
            'vigente_hasta' => now()->subDay(),
        ]);
    }
}
