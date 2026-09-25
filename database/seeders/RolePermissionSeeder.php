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

            // Promociones y Ofertas
            'ver promociones',
            'crear promociones',
            'editar promociones',
            'desactivar promociones',

            // Ajustes y Configuración General
            'ver ajustes',
            'editar ajustes',

            // Usuarios y Seguridad
            'ver usuarios',
            'crear usuarios',
            'editar usuarios',
            'desactivar usuarios',
            'asignar roles',

            // Nomenclatura Estándar modulo.accion (Dot-Notation)
            'dashboard.index',
            'reportes.ventas', 'reportes.inventario', 'reportes.compras', 'reportes.export',
            'alertas.vencimientos', 'alertas.stock',
            'laboratorios.index', 'laboratorios.create', 'laboratorios.edit', 'laboratorios.destroy',
            'categorias.index', 'categorias.create', 'categorias.edit', 'categorias.destroy',
            'productos.index', 'productos.create', 'productos.edit', 'productos.destroy',
            'promociones.index', 'promociones.create', 'promociones.edit', 'promociones.destroy',
            'presentaciones.index', 'presentaciones.create', 'presentaciones.edit', 'presentaciones.destroy',
            'lotes.index', 'lotes.create', 'lotes.edit', 'lotes.destroy',
            'proveedores.index', 'proveedores.create', 'proveedores.edit', 'proveedores.destroy',
            'clientes.index', 'clientes.create', 'clientes.edit', 'clientes.destroy',
            'compras.index', 'compras.create', 'compras.anular', 'compras.show',
            'ventas.index', 'ventas.own', 'ventas.create', 'ventas.anular', 'ventas.show',
            'inventario.index', 'inventario.ajuste', 'inventario.kardex', 'inventario.export',
            'recetas.index', 'recetas.create', 'recetas.validar', 'recetas.dispensar', 'recetas.anular', 'recetas.ventas',
            'cajas.index', 'cajas.create', 'cajas.edit', 'cajas.destroy', 'cajas.open', 'cajas.close', 'cajas.movimientos', 'cajas.arqueo',
            'ajustes.index', 'ajustes.edit',
            'usuarios.index', 'usuarios.create', 'usuarios.edit', 'usuarios.destroy', 'usuarios.roles',
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
        // ASIGNACIÓN DE PERMISOS A ROLES (Principio de Menor Privilegio)
        // =====================================================================

        // 1. ADMIN → TODOS LOS PERMISOS
        $admin->syncPermissions(Permission::all());

        // 2. FARMACÉUTICO / REGENTE → Control Técnico, Recetas Retenidas, Lotes, Catálogos, Alertas, Auditoría
        $farmaceutico->syncPermissions([
            'ver dashboard', 'dashboard.index',
            'ver alertas vencimientos', 'alertas.vencimientos',
            'ver alertas stock bajo', 'alertas.stock',
            'ver reportes ventas', 'reportes.ventas',
            'ver reportes inventario', 'reportes.inventario',
            'ver reportes compras', 'reportes.compras',
            'exportar reportes', 'reportes.export',

            // Catálogos
            'ver laboratorios', 'laboratorios.index', 'crear laboratorios', 'laboratorios.create', 'editar laboratorios', 'laboratorios.edit',
            'ver categorias', 'categorias.index', 'crear categorias', 'categorias.create', 'editar categorias', 'categorias.edit',
            'ver productos', 'productos.index', 'crear productos', 'productos.create', 'editar productos', 'productos.edit',
            'ver promociones', 'promociones.index', 'crear promociones', 'promociones.create', 'editar promociones', 'promociones.edit', 'desactivar promociones', 'promociones.destroy',
            'ver presentaciones', 'presentaciones.index', 'crear presentaciones', 'presentaciones.create', 'editar presentaciones', 'presentaciones.edit',
            'ver proveedores', 'proveedores.index',
            'ver clientes', 'clientes.index', 'crear clientes', 'clientes.create', 'editar clientes', 'clientes.edit',

            // Lotes e Inventario
            'ver lotes', 'lotes.index', 'crear lotes', 'lotes.create', 'editar lotes', 'lotes.edit', 'desactivar lotes', 'lotes.destroy',
            'ver movimientos inventario', 'inventario.index', 'ajustar inventario', 'inventario.ajuste', 'ver kardex', 'inventario.kardex', 'exportar kardex', 'inventario.export',

            // Recetas (Control Total de Prescripciones)
            'ver recetas', 'recetas.index', 'registrar recetas', 'recetas.create', 'validar recetas', 'recetas.validar', 'dispensar recetas', 'recetas.dispensar', 'anular recetas', 'recetas.anular', 'ver ventas recetas', 'recetas.ventas',

            // Compras y Ventas (Auditoría y Operación en Mostrador)
            'ver compras', 'compras.index', 'ver detalle compras', 'compras.show',
            'ver ventas', 'ventas.index', 'ver ventas propias', 'ventas.own', 'realizar ventas', 'ventas.create', 'ver detalle ventas', 'ventas.show',

            // Cajas (Auditoría y Operación)
            'ver cajas', 'cajas.index', 'abrir caja', 'cajas.open', 'cerrar caja', 'cajas.close', 'registrar movimientos caja', 'cajas.movimientos', 'ver arqueos caja', 'cajas.arqueo',
        ]);

        // 3. CAJERO / DISPENSADOR → Ventas, Dispensación de Recetas, Clientes, Cajas
        $cajero->syncPermissions([
            'ver dashboard', 'dashboard.index',
            'ver alertas vencimientos', 'alertas.vencimientos',
            
            // Catálogos (Lectura para consulta en mostrador)
            'ver productos', 'productos.index',
            'ver presentaciones', 'presentaciones.index',
            'ver lotes', 'lotes.index',
            'ver clientes', 'clientes.index', 'crear clientes', 'clientes.create', 'editar clientes', 'clientes.edit',

            // Ventas
            'realizar ventas', 'ventas.create',
            'ver ventas propias', 'ventas.own',
            'ver detalle ventas', 'ventas.show',

            // Cajas (Operación Diaria)
            'ver cajas', 'cajas.index',
            'abrir caja', 'cajas.open',
            'cerrar caja', 'cajas.close',
            'registrar movimientos caja', 'cajas.movimientos',
            'ver arqueos caja', 'cajas.arqueo',

            // Recetas
            'ver recetas', 'recetas.index',
            'registrar recetas', 'recetas.create',
            'dispensar recetas', 'recetas.dispensar',
            'ver ventas recetas', 'recetas.ventas',
        ]);

        // 4. INVENTARIO / BODEGA → Compras, Lotes, Kardex, Ajustes, Proveedores
        $inventario->syncPermissions([
            'ver dashboard', 'dashboard.index',
            'ver alertas vencimientos', 'alertas.vencimientos',
            'ver alertas stock bajo', 'alertas.stock',
            'ver reportes inventario', 'reportes.inventario',
            'ver reportes compras', 'reportes.compras',

            // Catálogos
            'ver laboratorios', 'laboratorios.index', 'crear laboratorios', 'laboratorios.create', 'editar laboratorios', 'laboratorios.edit',
            'ver categorias', 'categorias.index', 'crear categorias', 'categorias.create', 'editar categorias', 'categorias.edit',
            'ver productos', 'productos.index', 'crear productos', 'productos.create', 'editar productos', 'productos.edit',
            'ver presentaciones', 'presentaciones.index', 'crear presentaciones', 'presentaciones.create', 'editar presentaciones', 'presentaciones.edit',
            'ver proveedores', 'proveedores.index', 'crear proveedores', 'proveedores.create', 'editar proveedores', 'proveedores.edit',

            // Lotes y Compras
            'ver lotes', 'lotes.index', 'crear lotes', 'lotes.create', 'editar lotes', 'lotes.edit', 'desactivar lotes', 'lotes.destroy',
            'ver compras', 'compras.index', 'registrar compras', 'compras.create', 'ver detalle compras', 'compras.show',

            // Kardex
            'ver movimientos inventario', 'inventario.index', 'ajustar inventario', 'inventario.ajuste', 'ver kardex', 'inventario.kardex', 'exportar kardex', 'inventario.export',

            // Cajas (Auditoría de Cierres para Bodega)
            'ver cajas', 'cajas.index', 'ver arqueos caja', 'cajas.arqueo',
        ]);

        echo " Roles y permisos sincronizados exitosamente.\n";
        echo " Total de permisos: " . Permission::count() . "\n";
        echo " Roles configurados: Admin, Farmaceutico, Cajero, Inventario\n";
    }
}
