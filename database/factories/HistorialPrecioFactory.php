<?php

namespace Database\Factories;

use App\Models\HistorialPrecio;
use App\Models\Producto;
use App\Models\Proveedor;
use Illuminate\Database\Eloquent\Factories\Factory;

class HistorialPrecioFactory extends Factory
{
    protected $model = HistorialPrecio::class;

    public function definition(): array
    {
        return [
            'producto_id'               => Producto::factory(),
            'proveedor_id'              => Proveedor::factory(),
            'compra_id'                 => null,
            'presentacion_id'           => null,
            'tipo_presentacion'         => 'Unidad Base',
            'unidades_por_presentacion' => 1,
            'precio_compra'             => 10.00,
            'precio_unitario_base'      => 10.00,
            'tipo'                      => 'compra',
            'fecha'                     => now(),
            'observaciones'             => 'Compra estándar de stock',
        ];
    }

    public function cotizacion(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo'          => 'cotizacion',
            'observaciones' => 'Cotización recibida por correo',
        ]);
    }
}
