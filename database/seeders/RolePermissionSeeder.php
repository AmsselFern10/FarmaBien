<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpiar caché de permisos
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // =====================================================================
        // DEFINIR PERMISOS POR MÓDULO
        // =====================================================================
        
        $permissions = [
            // Dashboard y Reportes
            'ver dashboard',
            'ver reportes ventas',
            'ver reportes inventario',
            'ver reportes compras',
            'exportar reportes',

            // Alertas
            'ver alertas vencimientos',
            'ver alertas stock bajo',

            // Categorías
            'ver categorias',
            'crear categorias',
            'editar categorias',
            'desactivar categorias',

            // Productos
            'ver productos',
            'crear productos',
            'editar productos',
            'desactivar productos',

            // Lotes
            'ver lotes',
            'crear lotes',
            'editar lotes',
            'desactivar lotes',

            // Proveedores
            'ver proveedores',
            'crear proveedores',
            'editar proveedores',
            'desactivar proveedores',

            // Clientes
            'ver clientes',
            'crear clientes',
            'editar clientes',
            'desactivar clientes',

            // Compras
            'ver compras',
            'registrar compras',
            'anular compras',
            'ver detalle compras',

            // Ventas
            'ver ventas',
            'ver ventas propias', // Solo sus propias ventas
            'realizar ventas',
            'anular ventas',
            'ver detalle ventas',

            // Movimientos de inventario
            'ver movimientos inventario',
            'ajustar inventario',

            // Recetas
            'ver recetas',
            'registrar recetas',

            // Venta - Receta (tabla pivot)
            'ver ventas recetas',

            // Usuarios
            'ver usuarios',
            'crear usuarios',
            'editar usuarios',
            'desactivar usuarios',
            'asignar roles',
        ];

        // Crear todos los permisos
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // =====================================================================
        // CREAR ROLES
        // =====================================================================
        
        $admin = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $cajero = Role::firstOrCreate(['name' => 'Cajero', 'guard_name' => 'web']);
        $inventario = Role::firstOrCreate(['name' => 'Inventario', 'guard_name' => 'web']);

        // =====================================================================
        // ASIGNAR PERMISOS A ROLES
        // =====================================================================

        // ---------------------------------------------------------------------
        // ADMIN → TODOS LOS PERMISOS
        // ---------------------------------------------------------------------
        $admin->syncPermissions(Permission::all());

        // ---------------------------------------------------------------------
        // CAJERO → Ventas, Clientes, Recetas
        // ---------------------------------------------------------------------
        $cajero->syncPermissions([
            // Dashboard básico
            'ver dashboard',
            
            // Productos (solo lectura para buscar en ventas)
            'ver productos',
            
            // Lotes (CRÍTICO: necesita ver lotes para vender)
            'ver lotes',
            
            // Clientes
            'ver clientes',
            'crear clientes',
            'editar clientes',

            // Ventas
            'realizar ventas',
            'ver ventas propias', // Solo ve sus propias ventas
            'ver detalle ventas',

            // Recetas
            'ver recetas',
            'registrar recetas',
            'ver ventas recetas',
            
            // Alertas
            'ver alertas vencimientos', // Para no vender productos vencidos
        ]);

        // ---------------------------------------------------------------------
        // INVENTARIO → Compras, Productos, Lotes, Proveedores
        // ---------------------------------------------------------------------
        $inventario->syncPermissions([
            // Dashboard
            'ver dashboard',
            'ver reportes inventario',
            'ver reportes compras',
            
            // Alertas
            'ver alertas vencimientos',
            'ver alertas stock bajo',
            
            // Categorías
            'ver categorias',
            'crear categorias',
            'editar categorias',
            'desactivar categorias',

            // Productos
            'ver productos',
            'crear productos',
            'editar productos',
            'desactivar productos',

            // Lotes
            'ver lotes',
            'crear lotes',
            'editar lotes',
            'desactivar lotes',

            // Proveedores
            'ver proveedores',
            'crear proveedores',
            'editar proveedores',
            'desactivar proveedores',

            // Compras
            'ver compras',
            'registrar compras',
            'ver detalle compras',

            // Movimientos de Inventario
            'ver movimientos inventario',
            'ajustar inventario',
            
            // Ventas (solo lectura para análisis)
            'ver ventas',
            'ver detalle ventas',
        ]);

        echo "✅ Roles y permisos creados correctamente\n";
        echo "📊 Total permisos: " . Permission::count() . "\n";
        echo "👥 Roles creados: Admin, Cajero, Inventario\n";
    }
}