<?php

namespace Database\Factories;

use App\Models\Lote;
use App\Models\Producto;
use App\Models\Proveedor;
use Illuminate\Database\Eloquent\Factories\Factory;

class LoteFactory extends Factory
{
    protected $model = Lote::class;

    public function definition(): array
    {
        return [
            'producto_id'       => Producto::factory(),
            'proveedor_id'      => Proveedor::factory(),
            'numero_lote'       => 'LOT-' . fake()->unique()->numerify('#####'),
            'fecha_vencimiento' => now()->addYear()->toDateString(),
            'stock_inicial'     => 100,
            'stock_actual'      => 100,
            'precio_compra'     => 10.00,
            'activo'            => true,
        ];
    }

    public function vencido(): static
    {
        return $this->state(fn (array $attributes) => [
            'fecha_vencimiento' => now()->subMonths(2)->toDateString(),
        ]);
    }

    public function porVencer(): static
    {
        return $this->state(fn (array $attributes) => [
            'fecha_vencimiento' => now()->addDays(15)->toDateString(),
        ]);
    }

    public function agotado(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock_actual' => 0,
        ]);
    }
}
