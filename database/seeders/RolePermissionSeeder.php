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

            // Laboratorios
            'ver laboratorios',
            'crear laboratorios',
            'editar laboratorios',
            'desactivar laboratorios',

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

            // Presentaciones de Productos
            'ver presentaciones',
            'crear presentaciones',
            'editar presentaciones',
            'desactivar presentaciones',

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
            'ver ventas propias',
            'realizar ventas',
            'anular ventas',
            'ver detalle ventas',

            // Kardex y Movimientos de Inventario
            'ver movimientos inventario',
            'ajustar inventario',
            'ver kardex',
            'exportar kardex',

            // Recetas Médicas
            'ver recetas',
            'registrar recetas',
            'validar recetas',
            'dispensar recetas',
            'anular recetas',
            'ver ventas recetas',

            // Cajas y Arqueos
            'ver cajas',
            'crear cajas',
            'editar cajas',
            'desactivar cajas',
            'abrir caja',
            'cerrar caja',
            'registrar movimientos caja',
            'ver arqueos caja',

            // Ajustes y Configuración General
            'ver ajustes',
            'editar ajustes',

            // Usuarios y Seguridad
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
        // CREAR ROLES PROFESIONALES FARMACÉUTICOS
        // =====================================================================
        
        $admin = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $farmaceutico = Role::firstOrCreate(['name' => 'Farmaceutico', 'guard_name' => 'web']);
        $cajero = Role::firstOrCreate(['name' => 'Cajero', 'guard_name' => 'web']);
        $inventario = Role::firstOrCreate(['name' => 'Inventario', 'guard_name' => 'web']);

        // =====================================================================
        // ASIGNACIÓN DE PERMISOS A ROLES
        // =====================================================================

        // 1. ADMIN → TODOS LOS PERMISOS
        $admin->syncPermissions(Permission::all());

        // 2. FARMACÉUTICO / REGENTE → Control Técnico, Recetas Retenidas, Lotes, Catálogos, Alertas, Auditoría Cajas
        $farmaceutico->syncPermissions([
            'ver dashboard',
            'ver alertas vencimientos',
            'ver alertas stock bajo',
            'ver reportes ventas',
            'ver reportes inventario',
            'ver reportes compras',
            'exportar reportes',

            // Catálogos
            'ver laboratorios', 'crear laboratorios', 'editar laboratorios',
            'ver categorias', 'crear categorias', 'editar categorias',
            'ver productos', 'crear productos', 'editar productos',
            'ver presentaciones', 'crear presentaciones', 'editar presentaciones',
            'ver proveedores',
            'ver clientes', 'crear clientes', 'editar clientes',

            // Lotes e Inventario
            'ver lotes', 'crear lotes', 'editar lotes', 'desactivar lotes',
            'ver movimientos inventario', 'ajustar inventario', 'ver kardex', 'exportar kardex',

            // Recetas (Control Total de Prescripciones)
            'ver recetas', 'registrar recetas', 'validar recetas', 'dispensar recetas', 'anular recetas', 'ver ventas recetas',

            // Compras y Ventas (Auditoría)
            'ver compras', 'ver detalle compras',
            'ver ventas', 'ver detalle ventas',

            // Cajas (Auditoría)
            'ver cajas', 'ver arqueos caja',
        ]);

        // 3. CAJERO / DISPENSADOR → Ventas, Dispensación de Recetas, Clientes, Cajas
        $cajero->syncPermissions([
            'ver dashboard',
            'ver alertas vencimientos',
            
            // Catálogos (Lectura para consulta en mostrador)
            'ver productos',
            'ver presentaciones',
            'ver lotes',
            'ver clientes', 'crear clientes', 'editar clientes',

            // Ventas
            'realizar ventas',
            'ver ventas propias',
            'ver detalle ventas',

            // Cajas (Operación Diaria)
            'ver cajas',
            'abrir caja',
            'cerrar caja',
            'registrar movimientos caja',
            'ver arqueos caja',

            // Recetas
            'ver recetas',
            'registrar recetas',
            'dispensar recetas',
            'ver ventas recetas',
        ]);

        // 4. INVENTARIO / BODEGA → Compras, Lotes, Kardex, Ajustes, Proveedores
        $inventario->syncPermissions([
            'ver dashboard',
            'ver alertas vencimientos',
            'ver alertas stock bajo',
            'ver reportes inventario',
            'ver reportes compras',

            // Catálogos
            'ver laboratorios', 'crear laboratorios', 'editar laboratorios',
            'ver categorias', 'crear categorias', 'editar categorias',
            'ver productos', 'crear productos', 'editar productos',
            'ver presentaciones', 'crear presentaciones', 'editar presentaciones',
            'ver proveedores', 'crear proveedores', 'editar proveedores',

            // Lotes y Compras
            'ver lotes', 'crear lotes', 'editar lotes', 'desactivar lotes',
            'ver compras', 'registrar compras', 'ver detalle compras',

            // Kardex
            'ver movimientos inventario', 'ajustar inventario', 'ver kardex', 'exportar kardex',

            // Cajas (Auditoría de Cierres para Bodega)
            'ver cajas', 'ver arqueos caja',
        ]);

        echo " Roles y permisos sincronizados exitosamente.\n";
        echo " Total de permisos: " . Permission::count() . "\n";
        echo " Roles configurados: Admin, Farmaceutico, Cajero, Inventario\n";
    }
}
