<?php

namespace Database\Factories;

use App\Models\Promocion;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

class PromocionFactory extends Factory
{
    protected $model = Promocion::class;

    public function definition(): array
    {
        return [
            'nombre'          => 'Descuento Especial 10%',
            'descripcion'     => 'Promoción temporal de prueba',
            'tipo'            => 'porcentaje',
            'valor'           => 10.00,
            'alcance'         => 'producto',
            'producto_id'     => Producto::factory(),
            'categoria_id'    => null,
            'laboratorio_id'  => null,
            'fecha_inicio'    => now()->subDays(1),
            'fecha_fin'       => now()->addDays(7),
            'min_unidades'    => 1,
            'stock_limite'    => 100,
            'stock_consumido' => 0,
            'activo'          => true,
        ];
    }

    public function dosPorUno(): static
    {
        return $this->state(fn (array $attributes) => [
            'nombre' => 'Promo 2x1',
            'tipo'   => '2x1',
            'valor'  => 0,
        ]);
    }

    public function tresPorDos(): static
    {
        return $this->state(fn (array $attributes) => [
            'nombre' => 'Promo 3x2',
            'tipo'   => '3x2',
            'valor'  => 0,
        ]);
    }

    public function montoFijo(float $monto = 5.00): static
    {
        return $this->state(fn (array $attributes) => [
            'nombre' => "Descuento de $monto",
            'tipo'   => 'monto_fijo',
            'valor'  => $monto,
        ]);
    }

    public function vencida(): static
    {
        return $this->state(fn (array $attributes) => [
            'fecha_inicio' => now()->subDays(10),
            'fecha_fin'    => now()->subDays(2),
        ]);
    }

    public function programada(): static
    {
        return $this->state(fn (array $attributes) => [
            'fecha_inicio' => now()->addDays(2),
            'fecha_fin'    => now()->addDays(10),
        ]);
    }
}
