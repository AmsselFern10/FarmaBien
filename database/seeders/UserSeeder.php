<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // =====================================================================
        // CREAR USUARIO ADMINISTRADOR
        // =====================================================================
        $admin = User::firstOrCreate(
            ['email' => 'admin@farmabien.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('admin123'),
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole('Admin');

        // =====================================================================
        // CREAR USUARIO CAJERO
        // =====================================================================
        $cajero = User::firstOrCreate(
            ['email' => 'cajero@farmabien.com'],
            [
                'name' => 'Cajero',
                'password' => Hash::make('cajero123'),
                'email_verified_at' => now(),
            ]
        );
        $cajero->assignRole('Cajero');

        // =====================================================================
        // CREAR USUARIO INVENTARIO
        // =====================================================================
        $inventario = User::firstOrCreate(
            ['email' => 'inventario@farmabien.com'],
            [
                'name' => 'Inventario',
                'password' => Hash::make('inventario123'),
                'email_verified_at' => now(),
            ]
        );
        $inventario->assignRole('Inventario');

        // =====================================================================
        // MENSAJE DE CONFIRMACIÓN
        // =====================================================================
        echo "\n";
        echo "✅ Usuarios creados correctamente:\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "👤 ADMIN\n";
        echo "   Email: admin@farmabien.com\n";
        echo "   Password: admin123\n";
        echo "   Rol: Admin\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "👤 CAJERO\n";
        echo "   Email: cajero@farmabien.com\n";
        echo "   Password: cajero123\n";
        echo "   Rol: Cajero\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "👤 INVENTARIO\n";
        echo "   Email: inventario@farmabien.com\n";
        echo "   Password: inventario123\n";
        echo "   Rol: Inventario\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "\n";
    }
}