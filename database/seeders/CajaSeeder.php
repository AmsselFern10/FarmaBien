<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Caja;

class CajaSeeder extends Seeder
{
    public function run(): void
    {
        $cajas = [
            [
                'nombre'      => 'Caja Principal 01',
                'codigo'      => 'CAJA-01',
                'descripcion' => 'Caja de atención y cobro en mostrador principal',
                'ubicacion'   => 'Mostrador Central - Planta Baja',
                'activo'      => true,
            ],
            [
                'nombre'      => 'Caja Mostrador 02',
                'codigo'      => 'CAJA-02',
                'descripcion' => 'Caja secundaria para horas de alta afluencia',
                'ubicacion'   => 'Mostrador Lateral - Planta Baja',
                'activo'      => true,
            ],
            [
                'nombre'      => 'Caja Turno Noche / Emergencia',
                'codigo'      => 'CAJA-03',
                'descripcion' => 'Caja asignada para turno extendido y guardia',
                'ubicacion'   => 'Ventanilla de Guardia',
                'activo'      => true,
            ],
        ];

        foreach ($cajas as $caja) {
            Caja::firstOrCreate(
                ['codigo' => $caja['codigo']],
                $caja
            );
        }
    }
}
