<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Categoria;
use App\Models\Laboratorio;
use App\Models\Proveedor;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\PresentacionProducto;

class DatosInicialesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Categorías
        $catAnalgesicos = Categoria::firstOrCreate(['nombre' => 'Analgésicos y Antiinflamatorios'], ['descripcion' => 'Medicamentos para el dolor y fiebre', 'activo' => true]);
        $catAntibioticos = Categoria::firstOrCreate(['nombre' => 'Antibióticos y Antimicrobianos'], ['descripcion' => 'Fármacos contra infecciones bacterianas', 'activo' => true]);
        $catRespiratorio = Categoria::firstOrCreate(['nombre' => 'Sistema Respiratorio'], ['descripcion' => 'Antitusígenos, mucolíticos y antihistamínicos', 'activo' => true]);
        $catGastro = Categoria::firstOrCreate(['nombre' => 'Gastroenterología'], ['descripcion' => 'Antiácidos, protectores gástricos', 'activo' => true]);

        // 2. Laboratorios
        $labBayer = Laboratorio::firstOrCreate(['nombre' => 'Bayer'], ['codigo' => 'LAB-BAY', 'pais_origen' => 'Alemania', 'activo' => true]);
        $labGenfar = Laboratorio::firstOrCreate(['nombre' => 'Genfar'], ['codigo' => 'LAB-GEN', 'pais_origen' => 'Colombia', 'activo' => true]);
        $labPfizer = Laboratorio::firstOrCreate(['nombre' => 'Pfizer'], ['codigo' => 'LAB-PFI', 'pais_origen' => 'Estados Unidos', 'activo' => true]);
        $labBago = Laboratorio::firstOrCreate(['nombre' => 'Laboratorios Bagó'], ['codigo' => 'LAB-BAG', 'pais_origen' => 'Argentina', 'activo' => true]);

        // 3. Proveedores
        $prov1 = Proveedor::firstOrCreate(
            ['ruc' => '20100070970'],
            [
                'nombre' => 'Droguería Distribuidora Farmacéutica del Perú S.A.C.',
                'contacto' => 'Carlos Mendoza',
                'telefono' => '987654321',
                'email' => 'ventas@distrifarma.com',
                'direccion' => 'Av. Los Frutales 123',
                'activo' => true
            ]
        );

        // 4. Clientes
        $cli1 = Cliente::firstOrCreate(
            ['documento' => '00000000'],
            [
                'nombre' => 'Público General / Venta Mostrador',
                'telefono' => '000000000',
                'direccion' => 'Local Principal',
                'activo' => true
            ]
        );

        $cli2 = Cliente::firstOrCreate(
            ['documento' => '45678912'],
            [
                'nombre' => 'Juan Pérez Gómez',
                'telefono' => '912345678',
                'email' => 'juan.perez@example.com',
                'direccion' => 'Calle Las Magnolias 450',
                'activo' => true
            ]
        );

        // 5. Productos y Presentaciones
        // Producto 1: Paracetamol 500mg
        $paracetamol = Producto::firstOrCreate(
            ['codigo_barra' => '7751234567890'],
            [
                'nombre' => 'Paracetamol',
                'principio_activo' => 'Paracetamol',
                'concentracion' => '500 mg',
                'forma_farmaceutica' => 'Tableta',
                'categoria_id' => $catAnalgesicos->id,
                'laboratorio_id' => $labGenfar->id,
                'registro_sanitario' => 'EN-04521',
                'tipo_control' => 'venta_libre',
                'precio_compra' => 0.05,
                'precio_venta' => 0.20,
                'stock_minimo' => 50,
                'ubicacion' => 'Anaquel A-1',
                'requiere_receta' => false,
                'activo' => true
            ]
        );

        PresentacionProducto::firstOrCreate(
            ['producto_id' => $paracetamol->id, 'nombre' => 'Unidad (Pastilla)'],
            ['unidades_por_presentacion' => 1, 'precio_compra' => 0.05, 'precio_venta' => 0.20, 'es_unidad_base' => true, 'activo' => true, 'orden' => 1]
        );
        PresentacionProducto::firstOrCreate(
            ['producto_id' => $paracetamol->id, 'nombre' => 'Blíster x 10'],
            ['unidades_por_presentacion' => 10, 'precio_compra' => 0.45, 'precio_venta' => 1.80, 'es_unidad_base' => false, 'activo' => true, 'orden' => 2]
        );
        PresentacionProducto::firstOrCreate(
            ['producto_id' => $paracetamol->id, 'nombre' => 'Caja x 100'],
            ['unidades_por_presentacion' => 100, 'precio_compra' => 4.00, 'precio_venta' => 15.00, 'es_unidad_base' => false, 'activo' => true, 'orden' => 3]
        );

        // Producto 2: Amoxicilina 500mg (Requiere Receta)
        $amoxicilina = Producto::firstOrCreate(
            ['codigo_barra' => '7759876543210'],
            [
                'nombre' => 'Amoxicilina',
                'principio_activo' => 'Amoxicilina Trihidrato',
                'concentracion' => '500 mg',
                'forma_farmaceutica' => 'Cápsula',
                'categoria_id' => $catAntibioticos->id,
                'laboratorio_id' => $labBago->id,
                'registro_sanitario' => 'EE-09874',
                'tipo_control' => 'receta_medica',
                'precio_compra' => 0.15,
                'precio_venta' => 0.50,
                'stock_minimo' => 30,
                'ubicacion' => 'Anaquel B-3 (Antibióticos)',
                'requiere_receta' => true,
                'activo' => true
            ]
        );

        PresentacionProducto::firstOrCreate(
            ['producto_id' => $amoxicilina->id, 'nombre' => 'Unidad (Cápsula)'],
            ['unidades_por_presentacion' => 1, 'precio_compra' => 0.15, 'precio_venta' => 0.50, 'es_unidad_base' => true, 'activo' => true, 'orden' => 1]
        );
        PresentacionProducto::firstOrCreate(
            ['producto_id' => $amoxicilina->id, 'nombre' => 'Caja x 50'],
            ['unidades_por_presentacion' => 50, 'precio_compra' => 7.00, 'precio_venta' => 22.00, 'es_unidad_base' => false, 'activo' => true, 'orden' => 2]
        );

        echo " Datos iniciales (Categorías, Laboratorios, Proveedores, Clientes, Productos y Presentaciones) creados.\n";
    }
}
