<?php

namespace Database\Factories;

use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\Lote;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MovimientoInventarioFactory extends Factory
{
    protected $model = MovimientoInventario::class;

    public function definition(): array
    {
        return [
            'producto_id'      => Producto::factory(),
            'lote_id'          => Lote::factory(),
            'user_id'          => User::factory(),
            'tipo'             => 'entrada',
            'subtipo'          => 'ajuste_manual',
            'cantidad'         => 10,
            'stock_anterior'   => 0,
            'stock_posterior'  => 10,
            'costo_unitario'   => 10.00,
            'costo_total'      => 100.00,
            'origen'           => 'ajuste_manual',
            'origen_id'        => null,
            'motivo'           => 'Movimiento de prueba en Kardex',
            'fecha_movimiento' => now(),
        ];
    }

    public function entrada(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo'     => 'entrada',
            'subtipo'  => 'compra',
            'cantidad' => abs($attributes['cantidad'] ?? 10),
        ]);
    }

    public function salida(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo'     => 'salida',
            'subtipo'  => 'venta',
            'cantidad' => -abs($attributes['cantidad'] ?? 10),
        ]);
    }
}
