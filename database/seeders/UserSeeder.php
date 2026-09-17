<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Administrador General
        $admin = User::firstOrCreate(
            ['email' => 'admin@farmabien.com'],
            [
                'name' => 'Administrador FarmaBien',
                'password' => Hash::make('password'),
                'active' => true,
            ]
        );
        $admin->syncRoles(['Admin']);

        // 2. Farmacéutico / Director Técnico
        $farmaceutico = User::firstOrCreate(
            ['email' => 'farmaceutico@farmabien.com'],
            [
                'name' => 'Dr. Farmacéutico Regente',
                'password' => Hash::make('password'),
                'active' => true,
            ]
        );
        $farmaceutico->syncRoles(['Farmaceutico']);

        // 3. Cajero / Vendedor
        $cajero = User::firstOrCreate(
            ['email' => 'cajero@farmabien.com'],
            [
                'name' => 'Cajero Mostrador 1',
                'password' => Hash::make('password'),
                'active' => true,
            ]
        );
        $cajero->syncRoles(['Cajero']);

        // 4. Encargado de Inventario / Bodega
        $inventario = User::firstOrCreate(
            ['email' => 'inventario@farmabien.com'],
            [
                'name' => 'Encargado de Inventario',
                'password' => Hash::make('password'),
                'active' => true,
            ]
        );
        $inventario->syncRoles(['Inventario']);

        echo " Usuarios base creados con sus roles asignados.\n";
    }
}
