<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Categoria;
use App\Models\Laboratorio;
use App\Models\Proveedor;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\PresentacionProducto;
use App\Models\Lote;
use App\Models\MovimientoInventario;
use App\Models\Compra;
use App\Models\DetalleCompra;
use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\Receta;
use App\Models\RecetaDetalle;
use App\Models\Caja;
use App\Models\SesionCaja;
use App\Models\MovimientoCaja;
use App\Models\Promocion;
use App\Models\User;
use Carbon\Carbon;

class DatosInicialesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::first() ?? User::create([
            'name' => 'Administrador FarmaBien',
            'email' => 'admin@farmabien.com',
            'password' => bcrypt('password'),
            'active' => true,
        ]);
        $cajero = User::where('email', 'cajero@farmabien.com')->first() ?? $admin;

        // =========================================================================
        // 1. CATEGORÍAS FARMACÉUTICAS
        // =========================================================================
        $categoriasData = [
            ['nombre' => 'Analgésicos y Antipiréticos', 'descripcion' => 'Alivio del dolor de leve a moderado y control de la fiebre.'],
            ['nombre' => 'Antibióticos y Antimicrobianos', 'descripcion' => 'Tratamiento de infecciones bacterianas de diversas etiologías.'],
            ['nombre' => 'Antiinflamatorios y Antirreumáticos', 'descripcion' => 'Reducción de inflamación muscular, articular y postraumática.'],
            ['nombre' => 'Antihistamínicos y Antialérgicos', 'descripcion' => 'Control de rinitis, urticarias, alergias estacionales y prurito.'],
            ['nombre' => 'Antihipertensivos y Cardiológicos', 'descripcion' => 'Control de la presión arterial y prevención cardiovascular.'],
            ['nombre' => 'Antidiabéticos y Metabólicos', 'descripcion' => 'Regulación de niveles de glucosa sérica en diabetes tipo 2.'],
            ['nombre' => 'Gastroprotectores y Antiácidos', 'descripcion' => 'Tratamiento de reflujo gastroesofágico, gastritis y úlceras pépticas.'],
            ['nombre' => 'Vitaminas, Minerales y Suplementos', 'descripcion' => 'Complejos vitamínicos, neurotróficos y multivitamínicos.'],
            ['nombre' => 'Dermatológicos y Cicatrizantes', 'descripcion' => 'Cremas, pomadas, ungüentos y antibióticos tópicos.'],
            ['nombre' => 'Respiratorios, Antitusígenos y Expectorantes', 'descripcion' => 'Jarabes para tos seca o con flema, broncodilatadores y mucolíticos.'],
            ['nombre' => 'Oftálmicos y Óticos', 'descripcion' => 'Gotas estériles para afecciones oculares y del conducto auditivo.'],
            ['nombre' => 'Psicotrópicos y Fármacos Controlados', 'descripcion' => 'Ansiolíticos, sedantes y analgésicos centrales bajo receta retenida.'],
            ['nombre' => 'Antifúngicos y Antiparasitarios', 'descripcion' => 'Erradicación de hongos cutáneos/sistémicos y parásitos intestinales.'],
            ['nombre' => 'Salud Femenina y Anticonceptivos', 'descripcion' => 'Píldoras anticonceptivas hormonales y cuidado de la mujer.'],
        ];

        $categorias = [];
        foreach ($categoriasData as $data) {
            $categorias[$data['nombre']] = Categoria::firstOrCreate(
                ['nombre' => $data['nombre']],
                ['descripcion' => $data['descripcion'], 'activo' => true]
            );
        }

        // =========================================================================
        // 2. LABORATORIOS FARMACÉUTICOS (Nicaragua y Regionales)
        // =========================================================================
        $laboratoriosData = [
            ['nombre' => 'Laboratorios Ramos S.A.', 'codigo' => 'LAB-RAM', 'pais_origen' => 'Nicaragua'],
            ['nombre' => 'Laboratorios RARPE S.A.', 'codigo' => 'LAB-RAR', 'pais_origen' => 'Nicaragua'],
            ['nombre' => 'Laboratorios Cevallos', 'codigo' => 'LAB-CEV', 'pais_origen' => 'Nicaragua'],
            ['nombre' => 'Bayer Centroamérica', 'codigo' => 'LAB-BAY', 'pais_origen' => 'Alemania / Centroamérica'],
            ['nombre' => 'MK / Tecnoquímicas', 'codigo' => 'LAB-MK', 'pais_origen' => 'Colombia'],
            ['nombre' => 'Sanofi Aventis', 'codigo' => 'LAB-SAN', 'pais_origen' => 'Francia'],
            ['nombre' => 'Pfizer Centroamérica', 'codigo' => 'LAB-PFI', 'pais_origen' => 'Estados Unidos'],
            ['nombre' => 'Laboratorios Roemmers', 'codigo' => 'LAB-ROE', 'pais_origen' => 'Argentina'],
            ['nombre' => 'Laboratorios Stein', 'codigo' => 'LAB-STE', 'pais_origen' => 'Costa Rica'],
            ['nombre' => 'Menarini Centroamérica', 'codigo' => 'LAB-MEN', 'pais_origen' => 'Italia / Guatemala'],
            ['nombre' => 'Laboratorios Vijosa', 'codigo' => 'LAB-VIJ', 'pais_origen' => 'El Salvador'],
            ['nombre' => 'Abbott Healthcare', 'codigo' => 'LAB-ABB', 'pais_origen' => 'Estados Unidos'],
            ['nombre' => 'Laboratorios Rowe', 'codigo' => 'LAB-ROW', 'pais_origen' => 'República Dominicana'],
            ['nombre' => 'Calox International', 'codigo' => 'LAB-CAL', 'pais_origen' => 'Costa Rica'],
        ];

        $laboratorios = [];
        foreach ($laboratoriosData as $data) {
            $lab = Laboratorio::updateOrCreate(
                ['codigo' => $data['codigo']],
                ['nombre' => $data['nombre'], 'pais_origen' => $data['pais_origen'], 'activo' => true]
            );
            $laboratorios[$data['nombre']] = $lab;
        }

        // =========================================================================
        // 3. PROVEEDORES / DROGUERÍAS DISTRIBUIDORAS EN NICARAGUA
        // =========================================================================
        $proveedoresData = [
            [
                'ruc' => 'J0310000012345',
                'nombre' => 'Droguería CEFA Nicaragua S.A.',
                'contacto' => 'Lic. Marlon Bermúdez',
                'telefono' => '+505 2266-4400',
                'email' => 'ventas.nicaragua@cefa.com.ni',
                'direccion' => 'Km 4.5 Carretera Norte, Complejo Industrial CEFA, Managua',
            ],
            [
                'ruc' => 'J0310000054321',
                'nombre' => 'Distribuidora Farmacéutica Medina S.A. (DIFARMED)',
                'contacto' => 'Ing. Claudia Somarriba',
                'telefono' => '+505 2278-1122',
                'email' => 'pedidos@difarmed.com.ni',
                'direccion' => 'Altamira D\'Este, de la Vicky 2c. al sur, Managua',
            ],
            [
                'ruc' => 'J0310000098765',
                'nombre' => 'Distribuidora DILABSA S.A.',
                'contacto' => 'Lic. Roberto Chamorro',
                'telefono' => '+505 2268-9090',
                'email' => 'atencion@dilabsa.com.ni',
                'direccion' => 'Linda Vista Sur, Semáforos 1c. arriba, Managua',
            ],
            [
                'ruc' => 'J0310000045678',
                'nombre' => 'Droguería Ramos S.A.',
                'contacto' => 'Dra. Patricia Morales',
                'telefono' => '+505 2249-1020',
                'email' => 'distribucion@labramos.com.ni',
                'direccion' => 'Km 6 Carretera Norte, Parque Industrial Ramos, Managua',
            ],
            [
                'ruc' => 'J0310000067890',
                'nombre' => 'Medipharm Nicaragua S.A.',
                'contacto' => 'Lic. Carlos Guevara',
                'telefono' => '+505 2270-5566',
                'email' => 'ventas@medipharm.com.ni',
                'direccion' => 'Plaza Los Robles, Edificio Medipharm Módulo 4, Managua',
            ],
            [
                'ruc' => 'J0210000034567',
                'nombre' => 'Farmacéuticos Asociados de Occidente S.A.',
                'contacto' => 'Lic. Silvio Poveda',
                'telefono' => '+505 2311-4500',
                'email' => 'occidente@farmaasoc.com.ni',
                'direccion' => 'Costado Sur de la Iglesia San Juan, León, Nicaragua',
            ],
        ];

        $proveedores = [];
        foreach ($proveedoresData as $data) {
            $proveedores[$data['nombre']] = Proveedor::firstOrCreate(
                ['ruc' => $data['ruc']],
                array_merge($data, ['activo' => true])
            );
        }

        // =========================================================================
        // 4. CLIENTES REALISTAS (Nicaragua con Cédulas de Identidad)
        // =========================================================================
        $clientesData = [
            [
                'documento' => '00000000',
                'nombre' => 'Público General / Venta Mostrador',
                'telefono' => '0000-0000',
                'email' => 'mostrador@farmabien.com',
                'direccion' => 'Local Principal, Managua',
            ],
            [
                'documento' => '001-140585-0023K',
                'nombre' => 'Lic. Juan Alberto Martínez Silva',
                'telefono' => '+505 8877-6655',
                'email' => 'juan.martinez@gmail.com',
                'direccion' => 'Colonia Maestro Gabriel, Casa F-12, Managua',
            ],
            [
                'documento' => '001-220890-0012L',
                'nombre' => 'Dra. María Elena Gutiérrez Rostrán',
                'telefono' => '+505 8922-3344',
                'email' => 'dra.gutierrez@yahoo.es',
                'direccion' => 'Reparto San Juan, Hotel Intercontinental 2c. al sur, Managua',
            ],
            [
                'documento' => '281-050378-0005M',
                'nombre' => 'Ing. Roberto Carlos Mendoza Fonseca',
                'telefono' => '+505 8455-1122',
                'email' => 'roberto.mendoza@ingenieria.ni',
                'direccion' => 'Barrio San Sebastián, Calle Real, León',
            ],
            [
                'documento' => '401-191162-0001H',
                'nombre' => 'Doña Carmen Rosa Espinoza Valle',
                'telefono' => '+505 8633-9988',
                'email' => 'carmen.espinoza@hotmail.com',
                'direccion' => 'Barrio Monimbó, de las 4 esquinas 1c. al lago, Masaya',
            ],
            [
                'documento' => '441-300495-0008P',
                'nombre' => 'Carlos Andrés Toruño Sequeira',
                'telefono' => '+505 8711-2233',
                'email' => 'carlos.toruno@outlook.com',
                'direccion' => 'Calle Real Xalteva, frente a Parque Central, Granada',
            ],
            [
                'documento' => '441-120788-0002B',
                'nombre' => 'Silvia Patricia Blandón Jarquín',
                'telefono' => '+505 8344-5566',
                'email' => 'silvia.blandon@matagalpa.com',
                'direccion' => 'Barrio Guanuca, del Puente 2c. al este, Matagalpa',
            ],
            [
                'documento' => '001-250982-0044V',
                'nombre' => 'Douglas Antonio Narváez Morales',
                'telefono' => '+505 8899-7711',
                'email' => 'douglas.narvaez@transporte.ni',
                'direccion' => 'Bello Horizonte, Rotonda 1c. al sur, Managua',
            ],
            [
                'documento' => '001-180199-0031W',
                'nombre' => 'Katherine Vanessa Morales Castillo',
                'telefono' => '+505 8522-6633',
                'email' => 'kathy.morales@gmail.com',
                'direccion' => 'Villa Fontana Sur, Club Terraza 3c. arriba, Managua',
            ],
            [
                'documento' => '001-100255-0009Q',
                'nombre' => 'Don Francisco Javier Chamorro Solórzano',
                'telefono' => '+505 8211-4477',
                'email' => 'fchamorro@empresas.ni',
                'direccion' => 'Las Colinas, Embajada de España 1c. abajo, Managua',
            ],
        ];

        $clientes = [];
        foreach ($clientesData as $data) {
            $clientes[$data['documento']] = Cliente::firstOrCreate(
                ['documento' => $data['documento']],
                array_merge($data, ['activo' => true])
            );
        }

        // =========================================================================
        // 5. LISTADO DE 40 MEDICAMENTOS REALES EN NICARAGUA
        // =========================================================================
        $productosData = [
            // --- ANALGÉSICOS Y ANTIINFLAMATORIOS ---
            [
                'codigo_barra' => '7401001234011',
                'nombre' => 'Acetaminofén 500mg Ramos',
                'principio_activo' => 'Paracetamol / Acetaminofén',
                'concentracion' => '500 mg',
                'forma_farmaceutica' => 'Tableta',
                'categoria' => 'Analgésicos y Antipiréticos',
                'laboratorio' => 'Laboratorios Ramos S.A.',
                'registro_sanitario' => 'MINSA-R-01452',
                'tipo_control' => 'venta_libre',
                'precio_compra' => 0.80,
                'precio_venta' => 2.00,
                'stock_minimo' => 100,
                'ubicacion' => 'Anaquel A-1 (Analgésicos)',
                'requiere_receta' => false,
                'presentaciones' => [
                    ['nombre' => 'Unidad (Pastilla)', 'unidades' => 1, 'compra' => 0.80, 'venta' => 2.00, 'es_base' => true],
                    ['nombre' => 'Blíster x 10', 'unidades' => 10, 'compra' => 7.50, 'venta' => 18.00, 'es_base' => false],
                    ['nombre' => 'Caja x 100', 'unidades' => 100, 'compra' => 70.00, 'venta' => 150.00, 'es_base' => false],
                ],
                'lotes' => [
                    ['numero' => 'LOT-RAM-2601', 'vencimiento' => Carbon::now()->addMonths(24), 'stock' => 500, 'compra' => 0.80],
                    ['numero' => 'LOT-RAM-2602', 'vencimiento' => Carbon::now()->addDays(20), 'stock' => 50, 'compra' => 0.75], // Próximo a vencer (Alerta)
                ]
            ],
            [
                'codigo_barra' => '7702057089123',
                'nombre' => 'Ibuprofeno 400mg MK',
                'principio_activo' => 'Ibuprofeno',
                'concentracion' => '400 mg',
                'forma_farmaceutica' => 'Cápsula Blanda',
                'categoria' => 'Antiinflamatorios y Antirreumáticos',
                'laboratorio' => 'MK / Tecnoquímicas',
                'registro_sanitario' => 'MINSA-R-08912',
                'tipo_control' => 'venta_libre',
                'precio_compra' => 2.50,
                'precio_venta' => 6.00,
                'stock_minimo' => 60,
                'ubicacion' => 'Anaquel A-2',
                'requiere_receta' => false,
                'presentaciones' => [
                    ['nombre' => 'Unidad (Cápsula)', 'unidades' => 1, 'compra' => 2.50, 'venta' => 6.00, 'es_base' => true],
                    ['nombre' => 'Blíster x 10', 'unidades' => 10, 'compra' => 22.00, 'venta' => 55.00, 'es_base' => false],
                    ['nombre' => 'Caja x 50', 'unidades' => 50, 'compra' => 100.00, 'venta' => 250.00, 'es_base' => false],
                ],
                'lotes' => [
                    ['numero' => 'LOT-MK-4401', 'vencimiento' => Carbon::now()->addMonths(18), 'stock' => 300, 'compra' => 2.50]
                ]
            ],
            [
                'codigo_barra' => '7702057089130',
                'nombre' => 'Ibuprofeno 600mg MK',
                'principio_activo' => 'Ibuprofeno',
                'concentracion' => '600 mg',
                'forma_farmaceutica' => 'Tableta Recubierta',
                'categoria' => 'Antiinflamatorios y Antirreumáticos',
                'laboratorio' => 'MK / Tecnoquímicas',
                'registro_sanitario' => 'MINSA-R-08915',
                'tipo_control' => 'venta_libre',
                'precio_compra' => 3.20,
                'precio_venta' => 8.00,
                'stock_minimo' => 40,
                'ubicacion' => 'Anaquel A-2',
                'requiere_receta' => false,
                'presentaciones' => [
                    ['nombre' => 'Unidad (Pastilla)', 'unidades' => 1, 'compra' => 3.20, 'venta' => 8.00, 'es_base' => true],
                    ['nombre' => 'Blíster x 10', 'unidades' => 10, 'compra' => 30.00, 'venta' => 75.00, 'es_base' => false],
                    ['nombre' => 'Caja x 30', 'unidades' => 30, 'compra' => 85.00, 'venta' => 210.00, 'es_base' => false],
                ],
                'lotes' => [
                    ['numero' => 'LOT-MK-6002', 'vencimiento' => Carbon::now()->addMonths(20), 'stock' => 250, 'compra' => 3.20]
                ]
            ],
            [
                'codigo_barra' => '7401001234028',
                'nombre' => 'Diclofenaco Sódico 75mg/3ml Inyectable Ramos',
                'principio_activo' => 'Diclofenaco Sódico',
                'concentracion' => '75 mg / 3 ml',
                'forma_farmaceutica' => 'Inyectable (Ampolla)',
                'categoria' => 'Antiinflamatorios y Antirreumáticos',
                'laboratorio' => 'Laboratorios Ramos S.A.',
                'registro_sanitario' => 'MINSA-R-02311',
                'tipo_control' => 'receta_medica',
                'precio_compra' => 10.00,
                'precio_venta' => 25.00,
                'stock_minimo' => 20,
                'ubicacion' => 'Vitrina Inyectables B-1',
                'requiere_receta' => true,
                'presentaciones' => [
                    ['nombre' => 'Ampolla Individual 3ml', 'unidades' => 1, 'compra' => 10.00, 'venta' => 25.00, 'es_base' => true],
                    ['nombre' => 'Caja x 5 Ampollas', 'unidades' => 5, 'compra' => 45.00, 'venta' => 110.00, 'es_base' => false],
                ],
                'lotes' => [
                    ['numero' => 'LOT-RAM-DIC01', 'vencimiento' => Carbon::now()->addMonths(30), 'stock' => 80, 'compra' => 10.00]
                ]
            ],
            [
                'codigo_barra' => '7441005678012',
                'nombre' => 'Dexametasona 8mg/2ml Inyectable Stein',
                'principio_activo' => 'Dexametasona Fosfato',
                'concentracion' => '8 mg / 2 ml',
                'forma_farmaceutica' => 'Inyectable (Ampolla)',
                'categoria' => 'Antiinflamatorios y Antirreumáticos',
                'laboratorio' => 'Laboratorios Stein',
                'registro_sanitario' => 'MINSA-R-03445',
                'tipo_control' => 'receta_medica',
                'precio_compra' => 14.00,
                'precio_venta' => 35.00,
                'stock_minimo' => 15,
                'ubicacion' => 'Vitrina Inyectables B-1',
                'requiere_receta' => true,
                'presentaciones' => [
                    ['nombre' => 'Ampolla Individual 2ml', 'unidades' => 1, 'compra' => 14.00, 'venta' => 35.00, 'es_base' => true],
                    ['nombre' => 'Caja x 5 Ampollas', 'unidades' => 5, 'compra' => 65.00, 'venta' => 160.00, 'es_base' => false],
                ],
                'lotes' => [
                    ['numero' => 'LOT-STE-DEX01', 'vencimiento' => Carbon::now()->addMonths(22), 'stock' => 60, 'compra' => 14.00]
                ]
            ],

            // --- ANTIBIÓTICOS Y ANTIMICROBIANOS ---
            [
                'codigo_barra' => '7791234567019',
                'nombre' => 'Amoxicilina + Ácido Clavulánico 875/125mg Roemmers',
                'principio_activo' => 'Amoxicilina Trihidrato + Clavulanato Potásico',
                'concentracion' => '875 mg / 125 mg',
                'forma_farmaceutica' => 'Tableta Recubierta',
                'categoria' => 'Antibióticos y Antimicrobianos',
                'laboratorio' => 'Laboratorios Roemmers',
                'registro_sanitario' => 'MINSA-R-07812',
                'tipo_control' => 'receta_medica',
                'precio_compra' => 16.00,
                'precio_venta' => 35.00,
                'stock_minimo' => 30,
                'ubicacion' => 'Anaquel B-2 (Antibióticos)',
                'requiere_receta' => true,
                'presentaciones' => [
                    ['nombre' => 'Unidad (Tableta)', 'unidades' => 1, 'compra' => 16.00, 'venta' => 35.00, 'es_base' => true],
                    ['nombre' => 'Blíster x 7', 'unidades' => 7, 'compra' => 105.00, 'venta' => 240.00, 'es_base' => false],
                    ['nombre' => 'Caja x 14', 'unidades' => 14, 'compra' => 200.00, 'venta' => 450.00, 'es_base' => false],
                ],
                'lotes' => [
                    ['numero' => 'LOT-ROE-AMX01', 'vencimiento' => Carbon::now()->addMonths(16), 'stock' => 140, 'compra' => 16.00]
                ]
            ],
            [
                'codigo_barra' => '7702057089147',
                'nombre' => 'Azitromicina 500mg MK',
                'principio_activo' => 'Azitromicina Dihidrato',
                'concentracion' => '500 mg',
                'forma_farmaceutica' => 'Tableta Recubierta',
                'categoria' => 'Antibióticos y Antimicrobianos',
                'laboratorio' => 'MK / Tecnoquímicas',
                'registro_sanitario' => 'MINSA-R-09123',
                'tipo_control' => 'receta_medica',
                'precio_compra' => 25.00,
                'precio_venta' => 55.00,
                'stock_minimo' => 20,
                'ubicacion' => 'Anaquel B-2 (Antibióticos)',
                'requiere_receta' => true,
                'presentaciones' => [
                    ['nombre' => 'Unidad (Tableta)', 'unidades' => 1, 'compra' => 25.00, 'venta' => 55.00, 'es_base' => true],
                    ['nombre' => 'Caja x 3 Tabletas', 'unidades' => 3, 'compra' => 70.00, 'venta' => 160.00, 'es_base' => false],
                ],
                'lotes' => [
                    ['numero' => 'LOT-MK-AZI01', 'vencimiento' => Carbon::now()->addMonths(20), 'stock' => 90, 'compra' => 25.00]
                ]
            ],
            [
                'codigo_barra' => '7401001234035',
                'nombre' => 'Ciprofloxacino 500mg Ramos',
                'principio_activo' => 'Ciprofloxacino Clorhidrato',
                'concentracion' => '500 mg',
                'forma_farmaceutica' => 'Tableta Recubierta',
                'categoria' => 'Antibióticos y Antimicrobianos',
                'laboratorio' => 'Laboratorios Ramos S.A.',
                'registro_sanitario' => 'MINSA-R-04120',
                'tipo_control' => 'receta_medica',
                'precio_compra' => 4.50,
                'precio_venta' => 12.00,
                'stock_minimo' => 40,
                'ubicacion' => 'Anaquel B-3',
                'requiere_receta' => true,
                'presentaciones' => [
                    ['nombre' => 'Unidad (Tableta)', 'unidades' => 1, 'compra' => 4.50, 'venta' => 12.00, 'es_base' => true],
                    ['nombre' => 'Blíster x 10', 'unidades' => 10, 'compra' => 42.00, 'venta' => 120.00, 'es_base' => false],
                    ['nombre' => 'Caja x 20', 'unidades' => 20, 'compra' => 80.00, 'venta' => 220.00, 'es_base' => false],
                ],
                'lotes' => [
                    ['numero' => 'LOT-RAM-CIP01', 'vencimiento' => Carbon::now()->addMonths(26), 'stock' => 200, 'compra' => 4.50]
                ]
            ],
            [
                'codigo_barra' => '7411002345018',
                'nombre' => 'Ceftriaxona 1g IM/IV Vijosa Inyectable',
                'principio_activo' => 'Ceftriaxona Sódica',
                'concentracion' => '1 g Vial + Diluyente 3.5ml',
                'forma_farmaceutica' => 'Inyectable (Vial Polvo)',
                'categoria' => 'Antibióticos y Antimicrobianos',
                'laboratorio' => 'Laboratorios Vijosa',
                'registro_sanitario' => 'MINSA-R-05510',
                'tipo_control' => 'receta_medica',
                'precio_compra' => 65.00,
                'precio_venta' => 160.00,
                'stock_minimo' => 15,
                'ubicacion' => 'Vitrina Inyectables B-2',
                'requiere_receta' => true,
                'presentaciones' => [
                    ['nombre' => 'Frasco Vial 1g + Diluyente', 'unidades' => 1, 'compra' => 65.00, 'venta' => 160.00, 'es_base' => true],
                    ['nombre' => 'Caja x 5 Viales', 'unidades' => 5, 'compra' => 300.00, 'venta' => 750.00, 'es_base' => false],
                ],
                'lotes' => [
                    ['numero' => 'LOT-VIJ-CEF01', 'vencimiento' => Carbon::now()->addMonths(14), 'stock' => 45, 'compra' => 65.00]
                ]
            ],

            // --- RESPIRATORIOS, JARABES Y ANTITUSIVOS ---
            [
                'codigo_barra' => '7402003456015',
                'nombre' => 'Ambroxol Jarabe Adulto 30mg/5ml Rarpe',
                'principio_activo' => 'Ambroxol Clorhidrato',
                'concentracion' => '30 mg / 5 ml',
                'forma_farmaceutica' => 'Jarabe',
                'categoria' => 'Respiratorios, Antitusígenos y Expectorantes',
                'laboratorio' => 'Laboratorios RARPE S.A.',
                'registro_sanitario' => 'MINSA-R-06214',
                'tipo_control' => 'venta_libre',
                'precio_compra' => 55.00,
                'precio_venta' => 130.00,
                'stock_minimo' => 20,
                'ubicacion' => 'Estante Jarabes C-1',
                'requiere_receta' => false,
                'presentaciones' => [
                    ['nombre' => 'Frasco x 120 ml', 'unidades' => 1, 'compra' => 55.00, 'venta' => 130.00, 'es_base' => true],
                ],
                'lotes' => [
                    ['numero' => 'LOT-RAR-AMB01', 'vencimiento' => Carbon::now()->addMonths(22), 'stock' => 50, 'compra' => 55.00]
                ]
            ],
            [
                'codigo_barra' => '7402003456022',
                'nombre' => 'Ambroxol Jarabe Pediátrico 15mg/5ml Rarpe',
                'principio_activo' => 'Ambroxol Clorhidrato',
                'concentracion' => '15 mg / 5 ml',
                'forma_farmaceutica' => 'Jarabe',
                'categoria' => 'Respiratorios, Antitusígenos y Expectorantes',
                'laboratorio' => 'Laboratorios RARPE S.A.',
                'registro_sanitario' => 'MINSA-R-06215',
                'tipo_control' => 'venta_libre',
                'precio_compra' => 48.00,
                'precio_venta' => 110.00,
                'stock_minimo' => 20,
                'ubicacion' => 'Estante Jarabes C-1',
                'requiere_receta' => false,
                'presentaciones' => [
                    ['nombre' => 'Frasco x 120 ml', 'unidades' => 1, 'compra' => 48.00, 'venta' => 110.00, 'es_base' => true],
                ],
                'lotes' => [
                    ['numero' => 'LOT-RAR-AMBP01', 'vencimiento' => Carbon::now()->addMonths(20), 'stock' => 40, 'compra' => 48.00]
                ]
            ],
            [
                'codigo_barra' => '7401001234042',
                'nombre' => 'Dextrometorfano Jarabe Antitusivo Ramos',
                'principio_activo' => 'Dextrometorfano Bromhidrato',
                'concentracion' => '15 mg / 5 ml',
                'forma_farmaceutica' => 'Jarabe',
                'categoria' => 'Respiratorios, Antitusígenos y Expectorantes',
                'laboratorio' => 'Laboratorios Ramos S.A.',
                'registro_sanitario' => 'MINSA-R-01980',
                'tipo_control' => 'venta_libre',
                'precio_compra' => 38.00,
                'precio_venta' => 95.00,
                'stock_minimo' => 25,
                'ubicacion' => 'Estante Jarabes C-2',
                'requiere_receta' => false,
                'presentaciones' => [
                    ['nombre' => 'Frasco x 120 ml', 'unidades' => 1, 'compra' => 38.00, 'venta' => 95.00, 'es_base' => true],
                ],
                'lotes' => [
                    ['numero' => 'LOT-RAM-DEX01', 'vencimiento' => Carbon::now()->addMonths(18), 'stock' => 60, 'compra' => 38.00]
                ]
            ],
            [
                'codigo_barra' => '7401001234059',
                'nombre' => 'Salbutamol Jarabe 2mg/5ml Ramos',
                'principio_activo' => 'Salbutamol Sulfato',
                'concentracion' => '2 mg / 5 ml',
                'forma_farmaceutica' => 'Jarabe',
                'categoria' => 'Respiratorios, Antitusígenos y Expectorantes',
                'laboratorio' => 'Laboratorios Ramos S.A.',
                'registro_sanitario' => 'MINSA-R-01123',
                'tipo_control' => 'receta_medica',
                'precio_compra' => 32.00,
                'precio_venta' => 85.00,
                'stock_minimo' => 15,
                'ubicacion' => 'Estante Jarabes C-2',
                'requiere_receta' => true,
                'presentaciones' => [
                    ['nombre' => 'Frasco x 120 ml', 'unidades' => 1, 'compra' => 32.00, 'venta' => 85.00, 'es_base' => true],
                ],
                'lotes' => [
                    ['numero' => 'LOT-RAM-SAL01', 'vencimiento' => Carbon::now()->addMonths(24), 'stock' => 35, 'compra' => 32.00]
                ]
            ],

            // --- ANTIHISTAMÍNICOS Y ANTIALÉRGICOS ---
            [
                'codigo_barra' => '7702057089154',
                'nombre' => 'Loratadina 10mg MK',
                'principio_activo' => 'Loratadina',
                'concentracion' => '10 mg',
                'forma_farmaceutica' => 'Tableta',
                'categoria' => 'Antihistamínicos y Antialérgicos',
                'laboratorio' => 'MK / Tecnoquímicas',
                'registro_sanitario' => 'MINSA-R-07741',
                'tipo_control' => 'venta_libre',
                'precio_compra' => 1.80,
                'precio_venta' => 5.00,
                'stock_minimo' => 50,
                'ubicacion' => 'Anaquel C-3',
                'requiere_receta' => false,
                'presentaciones' => [
                    ['nombre' => 'Unidad (Pastilla)', 'unidades' => 1, 'compra' => 1.80, 'venta' => 5.00, 'es_base' => true],
                    ['nombre' => 'Blíster x 10', 'unidades' => 10, 'compra' => 17.00, 'venta' => 45.00, 'es_base' => false],
                    ['nombre' => 'Caja x 100', 'unidades' => 100, 'compra' => 160.00, 'venta' => 400.00, 'es_base' => false],
                ],
                'lotes' => [
                    ['numero' => 'LOT-MK-LOR01', 'vencimiento' => Carbon::now()->addMonths(28), 'stock' => 400, 'compra' => 1.80]
                ]
            ],
            [
                'codigo_barra' => '7403004567018',
                'nombre' => 'Loratadina Jarabe 5mg/5ml Cevallos',
                'principio_activo' => 'Loratadina',
                'concentracion' => '5 mg / 5 ml',
                'forma_farmaceutica' => 'Jarabe',
                'categoria' => 'Antihistamínicos y Antialérgicos',
                'laboratorio' => 'Laboratorios Cevallos',
                'registro_sanitario' => 'MINSA-R-08201',
                'tipo_control' => 'venta_libre',
                'precio_compra' => 42.00,
                'precio_venta' => 105.00,
                'stock_minimo' => 20,
                'ubicacion' => 'Estante Jarabes C-3',
                'requiere_receta' => false,
                'presentaciones' => [
                    ['nombre' => 'Frasco x 60 ml', 'unidades' => 1, 'compra' => 42.00, 'venta' => 105.00, 'es_base' => true],
                ],
                'lotes' => [
                    ['numero' => 'LOT-CEV-LOR01', 'vencimiento' => Carbon::now()->addMonths(20), 'stock' => 40, 'compra' => 42.00]
                ]
            ],
            [
                'codigo_barra' => '7791234567026',
                'nombre' => 'Cetirizina 10mg Roemmers',
                'principio_activo' => 'Cetirizina Diclorhidrato',
                'concentracion' => '10 mg',
                'forma_farmaceutica' => 'Tableta Recubierta',
                'categoria' => 'Antihistamínicos y Antialérgicos',
                'laboratorio' => 'Laboratorios Roemmers',
                'registro_sanitario' => 'MINSA-R-08560',
                'tipo_control' => 'venta_libre',
                'precio_compra' => 5.50,
                'precio_venta' => 14.00,
                'stock_minimo' => 30,
                'ubicacion' => 'Anaquel C-3',
                'requiere_receta' => false,
                'presentaciones' => [
                    ['nombre' => 'Unidad (Tableta)', 'unidades' => 1, 'compra' => 5.50, 'venta' => 14.00, 'es_base' => true],
                    ['nombre' => 'Blíster x 10', 'unidades' => 10, 'compra' => 52.00, 'venta' => 140.00, 'es_base' => false],
                    ['nombre' => 'Caja x 30', 'unidades' => 30, 'compra' => 150.00, 'venta' => 390.00, 'es_base' => false],
                ],
                'lotes' => [
                    ['numero' => 'LOT-ROE-CET01', 'vencimiento' => Carbon::now()->addMonths(24), 'stock' => 180, 'compra' => 5.50]
                ]
            ],

            // --- GASTROENTEROLOGÍA Y ANTIÁCIDOS ---
            [
                'codigo_barra' => '7403004567025',
                'nombre' => 'Omeprazol 20mg Cevallos',
                'principio_activo' => 'Omeprazol',
                'concentracion' => '20 mg',
                'forma_farmaceutica' => 'Cápsula con Microgránulos',
                'categoria' => 'Gastroprotectores y Antiácidos',
                'laboratorio' => 'Laboratorios Cevallos',
                'registro_sanitario' => 'MINSA-R-05440',
                'tipo_control' => 'venta_libre',
                'precio_compra' => 1.40,
                'precio_venta' => 4.00,
                'stock_minimo' => 80,
                'ubicacion' => 'Anaquel D-1 (Gastro)',
                'requiere_receta' => false,
                'presentaciones' => [
                    ['nombre' => 'Unidad (Cápsula)', 'unidades' => 1, 'compra' => 1.40, 'venta' => 4.00, 'es_base' => true],
                    ['nombre' => 'Blíster x 10', 'unidades' => 10, 'compra' => 13.00, 'venta' => 35.00, 'es_base' => false],
                    ['nombre' => 'Caja x 100', 'unidades' => 100, 'compra' => 120.00, 'venta' => 300.00, 'es_base' => false],
                ],
                'lotes' => [
                    ['numero' => 'LOT-CEV-OME01', 'vencimiento' => Carbon::now()->addMonths(25), 'stock' => 600, 'compra' => 1.40]
                ]
            ],
            [
                'codigo_barra' => '7791234567033',
                'nombre' => 'Esomeprazol 40mg Roemmers (Nexium)',
                'principio_activo' => 'Esomeprazol Magnésico',
                'concentracion' => '40 mg',
                'forma_farmaceutica' => 'Tableta con Recubrimiento Entérico',
                'categoria' => 'Gastroprotectores y Antiácidos',
                'laboratorio' => 'Laboratorios Roemmers',
                'registro_sanitario' => 'MINSA-R-09880',
                'tipo_control' => 'receta_medica',
                'precio_compra' => 12.00,
                'precio_venta' => 28.00,
                'stock_minimo' => 25,
                'ubicacion' => 'Anaquel D-1 (Gastro)',
                'requiere_receta' => true,
                'presentaciones' => [
                    ['nombre' => 'Unidad (Tableta)', 'unidades' => 1, 'compra' => 12.00, 'venta' => 28.00, 'es_base' => true],
                    ['nombre' => 'Blíster x 14', 'unidades' => 14, 'compra' => 160.00, 'venta' => 380.00, 'es_base' => false],
                    ['nombre' => 'Caja x 28', 'unidades' => 28, 'compra' => 310.00, 'venta' => 720.00, 'es_base' => false],
                ],
                'lotes' => [
                    ['numero' => 'LOT-ROE-ESO01', 'vencimiento' => Carbon::now()->addMonths(18), 'stock' => 140, 'compra' => 12.00]
                ]
            ],
            [
                'codigo_barra' => '7401001234066',
                'nombre' => 'Hidróxido de Aluminio y Magnesio Suspensión Ramos',
                'principio_activo' => 'Hidróxido de Aluminio + Hidróxido de Magnesio + Simeticona',
                'concentracion' => '400mg/400mg/30mg por 5ml',
                'forma_farmaceutica' => 'Suspensión Oral',
                'categoria' => 'Gastroprotectores y Antiácidos',
                'laboratorio' => 'Laboratorios Ramos S.A.',
                'registro_sanitario' => 'MINSA-R-03200',
                'tipo_control' => 'venta_libre',
                'precio_compra' => 75.00,
                'precio_venta' => 190.00,
                'stock_minimo' => 15,
                'ubicacion' => 'Anaquel D-2 (Antiácidos)',
                'requiere_receta' => false,
                'presentaciones' => [
                    ['nombre' => 'Frasco x 360 ml', 'unidades' => 1, 'compra' => 75.00, 'venta' => 190.00, 'es_base' => true],
                ],
                'lotes' => [
                    ['numero' => 'LOT-RAM-ALU01', 'vencimiento' => Carbon::now()->addMonths(20), 'stock' => 35, 'compra' => 75.00]
                ]
            ],

            // --- CARDIOLÓGICOS Y ANTIHIPERTENSIVOS ---
            [
                'codigo_barra' => '7401001234073',
                'nombre' => 'Losartán Potásico 50mg Ramos',
                'principio_activo' => 'Losartán Potásico',
                'concentracion' => '50 mg',
                'forma_farmaceutica' => 'Tableta Recubierta',
                'categoria' => 'Antihipertensivos y Cardiológicos',
                'laboratorio' => 'Laboratorios Ramos S.A.',
                'registro_sanitario' => 'MINSA-R-06512',
                'tipo_control' => 'receta_medica',
                'precio_compra' => 1.40,
                'precio_venta' => 4.00,
                'stock_minimo' => 60,
                'ubicacion' => 'Anaquel E-1 (Cardio)',
                'requiere_receta' => true,
                'presentaciones' => [
                    ['nombre' => 'Unidad (Tableta)', 'unidades' => 1, 'compra' => 1.40, 'venta' => 4.00, 'es_base' => true],
                    ['nombre' => 'Blíster x 10', 'unidades' => 10, 'compra' => 13.00, 'venta' => 35.00, 'es_base' => false],
                    ['nombre' => 'Caja x 100', 'unidades' => 100, 'compra' => 125.00, 'venta' => 320.00, 'es_base' => false],
                ],
                'lotes' => [
                    ['numero' => 'LOT-RAM-LOS01', 'vencimiento' => Carbon::now()->addMonths(30), 'stock' => 400, 'compra' => 1.40]
                ]
            ],
            [
                'codigo_barra' => '7702057089161',
                'nombre' => 'Enalapril 20mg MK',
                'principio_activo' => 'Enalapril Maleato',
                'concentracion' => '20 mg',
                'forma_farmaceutica' => 'Tableta',
                'categoria' => 'Antihipertensivos y Cardiológicos',
                'laboratorio' => 'MK / Tecnoquímicas',
                'registro_sanitario' => 'MINSA-R-04890',
                'tipo_control' => 'receta_medica',
                'precio_compra' => 1.20,
                'precio_venta' => 3.50,
                'stock_minimo' => 40,
                'ubicacion' => 'Anaquel E-1 (Cardio)',
                'requiere_receta' => true,
                'presentaciones' => [
                    ['nombre' => 'Unidad (Tableta)', 'unidades' => 1, 'compra' => 1.20, 'venta' => 3.50, 'es_base' => true],
                    ['nombre' => 'Blíster x 10', 'unidades' => 10, 'compra' => 11.00, 'venta' => 30.00, 'es_base' => false],
                    ['nombre' => 'Caja x 50', 'unidades' => 50, 'compra' => 50.00, 'venta' => 130.00, 'es_base' => false],
                ],
                'lotes' => [
                    ['numero' => 'LOT-MK-ENA01', 'vencimiento' => Carbon::now()->addMonths(26), 'stock' => 250, 'compra' => 1.20]
                ]
            ],
            [
                'codigo_barra' => '7441005678029',
                'nombre' => 'Amlodipino 5mg Stein',
                'principio_activo' => 'Amlodipino Besilato',
                'concentracion' => '5 mg',
                'forma_farmaceutica' => 'Tableta',
                'categoria' => 'Antihipertensivos y Cardiológicos',
                'laboratorio' => 'Laboratorios Stein',
                'registro_sanitario' => 'MINSA-R-05781',
                'tipo_control' => 'receta_medica',
                'precio_compra' => 2.20,
                'precio_venta' => 6.00,
                'stock_minimo' => 30,
                'ubicacion' => 'Anaquel E-2',
                'requiere_receta' => true,
                'presentaciones' => [
                    ['nombre' => 'Unidad (Tableta)', 'unidades' => 1, 'compra' => 2.20, 'venta' => 6.00, 'es_base' => true],
                    ['nombre' => 'Blíster x 10', 'unidades' => 10, 'compra' => 20.00, 'venta' => 60.00, 'es_base' => false],
                    ['nombre' => 'Caja x 30', 'unidades' => 30, 'compra' => 58.00, 'venta' => 160.00, 'es_base' => false],
                ],
                'lotes' => [
                    ['numero' => 'LOT-STE-AML01', 'vencimiento' => Carbon::now()->addMonths(22), 'stock' => 180, 'compra' => 2.20]
                ]
            ],

            // --- ANTIDIABÉTICOS Y METABÓLICOS ---
            [
                'codigo_barra' => '7702057089178',
                'nombre' => 'Metformina 850mg MK',
                'principio_activo' => 'Metformina Clorhidrato',
                'concentracion' => '850 mg',
                'forma_farmaceutica' => 'Tableta Recubierta',
                'categoria' => 'Antidiabéticos y Metabólicos',
                'laboratorio' => 'MK / Tecnoquímicas',
                'registro_sanitario' => 'MINSA-R-07125',
                'tipo_control' => 'receta_medica',
                'precio_compra' => 1.80,
                'precio_venta' => 5.00,
                'stock_minimo' => 50,
                'ubicacion' => 'Anaquel E-3 (Diabetes)',
                'requiere_receta' => true,
                'presentaciones' => [
                    ['nombre' => 'Unidad (Tableta)', 'unidades' => 1, 'compra' => 1.80, 'venta' => 5.00, 'es_base' => true],
                    ['nombre' => 'Blíster x 10', 'unidades' => 10, 'compra' => 16.00, 'venta' => 45.00, 'es_base' => false],
                    ['nombre' => 'Caja x 60', 'unidades' => 60, 'compra' => 90.00, 'venta' => 250.00, 'es_base' => false],
                ],
                'lotes' => [
                    ['numero' => 'LOT-MK-MET01', 'vencimiento' => Carbon::now()->addMonths(28), 'stock' => 360, 'compra' => 1.80]
                ]
            ],
            [
                'codigo_barra' => '7401001234080',
                'nombre' => 'Glibenclamida 5mg Ramos',
                'principio_activo' => 'Glibenclamida',
                'concentracion' => '5 mg',
                'forma_farmaceutica' => 'Tableta',
                'categoria' => 'Antidiabéticos y Metabólicos',
                'laboratorio' => 'Laboratorios Ramos S.A.',
                'registro_sanitario' => 'MINSA-R-02890',
                'tipo_control' => 'receta_medica',
                'precio_compra' => 0.90,
                'precio_venta' => 2.50,
                'stock_minimo' => 40,
                'ubicacion' => 'Anaquel E-3 (Diabetes)',
                'requiere_receta' => true,
                'presentaciones' => [
                    ['nombre' => 'Unidad (Tableta)', 'unidades' => 1, 'compra' => 0.90, 'venta' => 2.50, 'es_base' => true],
                    ['nombre' => 'Blíster x 10', 'unidades' => 10, 'compra' => 8.00, 'venta' => 20.00, 'es_base' => false],
                    ['nombre' => 'Caja x 100', 'unidades' => 100, 'compra' => 75.00, 'venta' => 180.00, 'es_base' => false],
                ],
                'lotes' => [
                    ['numero' => 'LOT-RAM-GLI01', 'vencimiento' => Carbon::now()->addMonths(24), 'stock' => 300, 'compra' => 0.90]
                ]
            ],

            // --- VITAMINAS Y SUPLEMENTOS ---
            [
                'codigo_barra' => '7702057089185',
                'nombre' => 'Neurobión Forte Bayer (Grageas)',
                'principio_activo' => 'Vitamina B1 + B6 + B12',
                'concentracion' => '100mg / 200mg / 200mcg',
                'forma_farmaceutica' => 'Gragea',
                'categoria' => 'Vitaminas, Minerales y Suplementos',
                'laboratorio' => 'Bayer Centroamérica',
                'registro_sanitario' => 'MINSA-R-08990',
                'tipo_control' => 'venta_libre',
                'precio_compra' => 6.50,
                'precio_venta' => 18.00,
                'stock_minimo' => 50,
                'ubicacion' => 'Anaquel F-1 (Vitaminas)',
                'requiere_receta' => false,
                'presentaciones' => [
                    ['nombre' => 'Unidad (Gragea)', 'unidades' => 1, 'compra' => 6.50, 'venta' => 18.00, 'es_base' => true],
                    ['nombre' => 'Blíster x 10', 'unidades' => 10, 'compra' => 62.00, 'venta' => 170.00, 'es_base' => false],
                    ['nombre' => 'Caja x 30', 'unidades' => 30, 'compra' => 180.00, 'venta' => 480.00, 'es_base' => false],
                ],
                'lotes' => [
                    ['numero' => 'LOT-BAY-NEU01', 'vencimiento' => Carbon::now()->addMonths(20), 'stock' => 240, 'compra' => 6.50]
                ]
            ],
            [
                'codigo_barra' => '7702057089192',
                'nombre' => 'Neurobión 25000 Inyectable Bayer',
                'principio_activo' => 'Vitamina B1 + B6 + B12',
                'concentracion' => '100mg / 100mg / 25000mcg',
                'forma_farmaceutica' => 'Inyectable (Caja x 3 Ampollas)',
                'categoria' => 'Vitaminas, Minerales y Suplementos',
                'laboratorio' => 'Bayer Centroamérica',
                'registro_sanitario' => 'MINSA-R-08995',
                'tipo_control' => 'venta_libre',
                'precio_compra' => 180.00,
                'precio_venta' => 420.00,
                'stock_minimo' => 15,
                'ubicacion' => 'Vitrina Inyectables B-3',
                'requiere_receta' => false,
                'presentaciones' => [
                    ['nombre' => 'Caja x 3 Ampollas + Jeringas', 'unidades' => 1, 'compra' => 180.00, 'venta' => 420.00, 'es_base' => true],
                ],
                'lotes' => [
                    ['numero' => 'LOT-BAY-NEU25', 'vencimiento' => Carbon::now()->addMonths(18), 'stock' => 30, 'compra' => 180.00]
                ]
            ],
            [
                'codigo_barra' => '7702057089208',
                'nombre' => 'Vitamina C 1000mg Efervescente Redoxon',
                'principio_activo' => 'Ácido Ascórbico',
                'concentracion' => '1000 mg',
                'forma_farmaceutica' => 'Tableta Efervescente',
                'categoria' => 'Vitaminas, Minerales y Suplementos',
                'laboratorio' => 'Bayer Centroamérica',
                'registro_sanitario' => 'MINSA-R-09410',
                'tipo_control' => 'venta_libre',
                'precio_compra' => 65.00,
                'precio_venta' => 160.00,
                'stock_minimo' => 20,
                'ubicacion' => 'Anaquel F-2',
                'requiere_receta' => false,
                'presentaciones' => [
                    ['nombre' => 'Tubo x 10 Tabletas Efervescentes', 'unidades' => 1, 'compra' => 65.00, 'venta' => 160.00, 'es_base' => true],
                ],
                'lotes' => [
                    ['numero' => 'LOT-BAY-RED01', 'vencimiento' => Carbon::now()->addMonths(22), 'stock' => 40, 'compra' => 65.00]
                ]
            ],
            [
                'codigo_barra' => '7501008912015',
                'nombre' => 'Electrolit Suero Oral 500ml Fresa',
                'principio_activo' => 'Sodio + Potasio + Glucosa + Magnesio',
                'concentracion' => 'Solución Rehidratante 500ml',
                'forma_farmaceutica' => 'Solución Oral',
                'categoria' => 'Vitaminas, Minerales y Suplementos',
                'laboratorio' => 'Abbott Healthcare',
                'registro_sanitario' => 'MINSA-R-08112',
                'tipo_control' => 'venta_libre',
                'precio_compra' => 32.00,
                'precio_venta' => 70.00,
                'stock_minimo' => 30,
                'ubicacion' => 'Nevera / Mostrador Hidratación',
                'requiere_receta' => false,
                'presentaciones' => [
                    ['nombre' => 'Botella x 500 ml', 'unidades' => 1, 'compra' => 32.00, 'venta' => 70.00, 'es_base' => true],
                ],
                'lotes' => [
                    ['numero' => 'LOT-ABB-ELE01', 'vencimiento' => Carbon::now()->addMonths(14), 'stock' => 80, 'compra' => 32.00]
                ]
            ],

            // --- DERMATOLÓGICOS Y TÓPICOS ---
            [
                'codigo_barra' => '7702057089215',
                'nombre' => 'Quadriderm Crema Tópica 40g Bayer',
                'principio_activo' => 'Betametasona + Clotrimazol + Gentamicina',
                'concentracion' => '0.05% / 1% / 0.1%',
                'forma_farmaceutica' => 'Crema Dérmica (Tubo)',
                'categoria' => 'Dermatológicos y Cicatrizantes',
                'laboratorio' => 'Bayer Centroamérica',
                'registro_sanitario' => 'MINSA-R-09941',
                'tipo_control' => 'venta_libre',
                'precio_compra' => 120.00,
                'precio_venta' => 280.00,
                'stock_minimo' => 15,
                'ubicacion' => 'Anaquel F-3 (Cremas)',
                'requiere_receta' => false,
                'presentaciones' => [
                    ['nombre' => 'Tubo x 40 g', 'unidades' => 1, 'compra' => 120.00, 'venta' => 280.00, 'es_base' => true],
                ],
                'lotes' => [
                    ['numero' => 'LOT-BAY-QUA01', 'vencimiento' => Carbon::now()->addMonths(24), 'stock' => 35, 'compra' => 120.00]
                ]
            ],
            [
                'codigo_barra' => '7403004567032',
                'nombre' => 'Clotrimazol 1% Crema Dérmica Cevallos',
                'principio_activo' => 'Clotrimazol',
                'concentracion' => '1%',
                'forma_farmaceutica' => 'Crema Dérmica (Tubo)',
                'categoria' => 'Dermatológicos y Cicatrizantes',
                'laboratorio' => 'Laboratorios Cevallos',
                'registro_sanitario' => 'MINSA-R-04560',
                'tipo_control' => 'venta_libre',
                'precio_compra' => 28.00,
                'precio_venta' => 75.00,
                'stock_minimo' => 20,
                'ubicacion' => 'Anaquel F-3 (Cremas)',
                'requiere_receta' => false,
                'presentaciones' => [
                    ['nombre' => 'Tubo x 20 g', 'unidades' => 1, 'compra' => 28.00, 'venta' => 75.00, 'es_base' => true],
                ],
                'lotes' => [
                    ['numero' => 'LOT-CEV-CLO01', 'vencimiento' => Carbon::now()->addMonths(26), 'stock' => 45, 'compra' => 28.00]
                ]
            ],
            [
                'codigo_barra' => '7402003456039',
                'nombre' => 'Sulfadiazina de Plata 1% Crema Rarpe',
                'principio_activo' => 'Sulfadiazina de Plata',
                'concentracion' => '1%',
                'forma_farmaceutica' => 'Crema / Pomada para Quemaduras',
                'categoria' => 'Dermatológicos y Cicatrizantes',
                'laboratorio' => 'Laboratorios RARPE S.A.',
                'registro_sanitario' => 'MINSA-R-03810',
                'tipo_control' => 'venta_libre',
                'precio_compra' => 55.00,
                'precio_venta' => 140.00,
                'stock_minimo' => 10,
                'ubicacion' => 'Anaquel F-3',
                'requiere_receta' => false,
                'presentaciones' => [
                    ['nombre' => 'Pote x 60 g', 'unidades' => 1, 'compra' => 55.00, 'venta' => 140.00, 'es_base' => true],
                ],
                'lotes' => [
                    ['numero' => 'LOT-RAR-SUL01', 'vencimiento' => Carbon::now()->addMonths(16), 'stock' => 25, 'compra' => 55.00]
                ]
            ],

            // --- OFTÁLMICOS Y ÓTICOS ---
            [
                'codigo_barra' => '7441005678036',
                'nombre' => 'Tobramicina + Dexametasona Gotas Oftálmicas Stein',
                'principio_activo' => 'Tobramicina 0.3% + Dexametasona 0.1%',
                'concentracion' => '5 ml Gotero',
                'forma_farmaceutica' => 'Gotas Oftálmicas Estériles',
                'categoria' => 'Oftálmicos y Óticos',
                'laboratorio' => 'Laboratorios Stein',
                'registro_sanitario' => 'MINSA-R-06890',
                'tipo_control' => 'receta_medica',
                'precio_compra' => 95.00,
                'precio_venta' => 230.00,
                'stock_minimo' => 15,
                'ubicacion' => 'Vitrina Oftálmica G-1',
                'requiere_receta' => true,
                'presentaciones' => [
                    ['nombre' => 'Frasco Gotero x 5 ml', 'unidades' => 1, 'compra' => 95.00, 'venta' => 230.00, 'es_base' => true],
                ],
                'lotes' => [
                    ['numero' => 'LOT-STE-TOB01', 'vencimiento' => Carbon::now()->addMonths(18), 'stock' => 30, 'compra' => 95.00]
                ]
            ],
            [
                'codigo_barra' => '7401001234097',
                'nombre' => 'Gotas de Manzanilla + Nafazolina Ramos',
                'principio_activo' => 'Extracto de Manzanilla + Nafazolina Clorhidrato',
                'concentracion' => '15 ml Gotero',
                'forma_farmaceutica' => 'Gotas Oftálmicas Descongestionantes',
                'categoria' => 'Oftálmicos y Óticos',
                'laboratorio' => 'Laboratorios Ramos S.A.',
                'registro_sanitario' => 'MINSA-R-02190',
                'tipo_control' => 'venta_libre',
                'precio_compra' => 35.00,
                'precio_venta' => 90.00,
                'stock_minimo' => 25,
                'ubicacion' => 'Vitrina Oftálmica G-1',
                'requiere_receta' => false,
                'presentaciones' => [
                    ['nombre' => 'Frasco Gotero x 15 ml', 'unidades' => 1, 'compra' => 35.00, 'venta' => 90.00, 'es_base' => true],
                ],
                'lotes' => [
                    ['numero' => 'LOT-RAM-NAF01', 'vencimiento' => Carbon::now()->addMonths(20), 'stock' => 50, 'compra' => 35.00]
                ]
            ],

            // --- ANTIFÚNGICOS Y ANTIPARASITARIOS ---
            [
                'codigo_barra' => '7702057089222',
                'nombre' => 'Fluconazol 150mg MK',
                'principio_activo' => 'Fluconazol',
                'concentracion' => '150 mg',
                'forma_farmaceutica' => 'Cápsula',
                'categoria' => 'Antifúngicos y Antiparasitarios',
                'laboratorio' => 'MK / Tecnoquímicas',
                'registro_sanitario' => 'MINSA-R-07920',
                'tipo_control' => 'receta_medica',
                'precio_compra' => 25.00,
                'precio_venta' => 65.00,
                'stock_minimo' => 20,
                'ubicacion' => 'Anaquel G-2',
                'requiere_receta' => true,
                'presentaciones' => [
                    ['nombre' => 'Caja x 1 Cápsula', 'unidades' => 1, 'compra' => 25.00, 'venta' => 65.00, 'es_base' => true],
                    ['nombre' => 'Caja x 2 Cápsulas', 'unidades' => 2, 'compra' => 45.00, 'venta' => 120.00, 'es_base' => false],
                ],
                'lotes' => [
                    ['numero' => 'LOT-MK-FLU01', 'vencimiento' => Carbon::now()->addMonths(30), 'stock' => 40, 'compra' => 25.00]
                ]
            ],
            [
                'codigo_barra' => '7401001234103',
                'nombre' => 'Albendazol 400mg Ramos Masticable',
                'principio_activo' => 'Albendazol',
                'concentracion' => '400 mg',
                'forma_farmaceutica' => 'Tableta Masticable',
                'categoria' => 'Antifúngicos y Antiparasitarios',
                'laboratorio' => 'Laboratorios Ramos S.A.',
                'registro_sanitario' => 'MINSA-R-01850',
                'tipo_control' => 'venta_libre',
                'precio_compra' => 9.00,
                'precio_venta' => 25.00,
                'stock_minimo' => 30,
                'ubicacion' => 'Anaquel G-2',
                'requiere_receta' => false,
                'presentaciones' => [
                    ['nombre' => 'Unidad (Tableta Masticable)', 'unidades' => 1, 'compra' => 9.00, 'venta' => 25.00, 'es_base' => true],
                    ['nombre' => 'Caja x 2 Tabletas', 'unidades' => 2, 'compra' => 16.00, 'venta' => 45.00, 'es_base' => false],
                ],
                'lotes' => [
                    ['numero' => 'LOT-RAM-ALB01', 'vencimiento' => Carbon::now()->addMonths(28), 'stock' => 100, 'compra' => 9.00]
                ]
            ],

            // --- SALUD FEMENINA Y ANTICONCEPTIVOS ---
            [
                'codigo_barra' => '7702057089239',
                'nombre' => 'Microgynon 21 Píldoras Anticonceptivas Bayer',
                'principio_activo' => 'Levonorgestrel + Etinilestradiol',
                'concentracion' => '0.15 mg / 0.03 mg',
                'forma_farmaceutica' => 'Gragea / Píldora',
                'categoria' => 'Salud Femenina y Anticonceptivos',
                'laboratorio' => 'Bayer Centroamérica',
                'registro_sanitario' => 'MINSA-R-09150',
                'tipo_control' => 'receta_medica',
                'precio_compra' => 75.00,
                'precio_venta' => 180.00,
                'stock_minimo' => 20,
                'ubicacion' => 'Anaquel H-1 (Femenino)',
                'requiere_receta' => true,
                'presentaciones' => [
                    ['nombre' => 'Blíster x 21 Grageas', 'unidades' => 1, 'compra' => 75.00, 'venta' => 180.00, 'es_base' => true],
                ],
                'lotes' => [
                    ['numero' => 'LOT-BAY-MIC01', 'vencimiento' => Carbon::now()->addMonths(24), 'stock' => 45, 'compra' => 75.00]
                ]
            ],

            // --- PSICOTRÓPICOS Y CONTROLADOS (RECETA RETENIDA) ---
            [
                'codigo_barra' => '7791234567040',
                'nombre' => 'Alprazolam 0.5mg Roemmers (Tranquinal)',
                'principio_activo' => 'Alprazolam',
                'concentracion' => '0.5 mg',
                'forma_farmaceutica' => 'Tableta Ranurada',
                'categoria' => 'Psicotrópicos y Fármacos Controlados',
                'laboratorio' => 'Laboratorios Roemmers',
                'registro_sanitario' => 'MINSA-R-09820-PSI',
                'tipo_control' => 'receta_retenida',
                'precio_compra' => 4.00,
                'precio_venta' => 11.00,
                'stock_minimo' => 30,
                'ubicacion' => 'Caja de Seguridad Psicotrópicos',
                'requiere_receta' => true,
                'presentaciones' => [
                    ['nombre' => 'Unidad (Tableta)', 'unidades' => 1, 'compra' => 4.00, 'venta' => 11.00, 'es_base' => true],
                    ['nombre' => 'Blíster x 10', 'unidades' => 10, 'compra' => 38.00, 'venta' => 110.00, 'es_base' => false],
                    ['nombre' => 'Caja x 30', 'unidades' => 30, 'compra' => 110.00, 'venta' => 310.00, 'es_base' => false],
                ],
                'lotes' => [
                    ['numero' => 'LOT-ROE-ALP01', 'vencimiento' => Carbon::now()->addMonths(22), 'stock' => 120, 'compra' => 4.00],
                    ['numero' => 'LOT-ROE-ALP02', 'vencimiento' => Carbon::now()->addDays(25), 'stock' => 15, 'compra' => 4.00], // Próximo a vencer
                ]
            ],
            [
                'codigo_barra' => '7441005678043',
                'nombre' => 'Clonazepam 2mg Stein (Rivotril Genérico)',
                'principio_activo' => 'Clonazepam',
                'concentracion' => '2 mg',
                'forma_farmaceutica' => 'Tableta Ranurada',
                'categoria' => 'Psicotrópicos y Fármacos Controlados',
                'laboratorio' => 'Laboratorios Stein',
                'registro_sanitario' => 'MINSA-R-08412-PSI',
                'tipo_control' => 'receta_retenida',
                'precio_compra' => 4.80,
                'precio_venta' => 13.00,
                'stock_minimo' => 25,
                'ubicacion' => 'Caja de Seguridad Psicotrópicos',
                'requiere_receta' => true,
                'presentaciones' => [
                    ['nombre' => 'Unidad (Tableta)', 'unidades' => 1, 'compra' => 4.80, 'venta' => 13.00, 'es_base' => true],
                    ['nombre' => 'Blíster x 10', 'unidades' => 10, 'compra' => 45.00, 'venta' => 130.00, 'es_base' => false],
                    ['nombre' => 'Caja x 30', 'unidades' => 30, 'compra' => 130.00, 'venta' => 360.00, 'es_base' => false],
                ],
                'lotes' => [
                    ['numero' => 'LOT-STE-CLO01', 'vencimiento' => Carbon::now()->addMonths(20), 'stock' => 90, 'compra' => 4.80]
                ]
            ],
            [
                'codigo_barra' => '7401001234110',
                'nombre' => 'Tramadol 50mg Ramos',
                'principio_activo' => 'Tramadol Clorhidrato',
                'concentracion' => '50 mg',
                'forma_farmaceutica' => 'Cápsula',
                'categoria' => 'Psicotrópicos y Fármacos Controlados',
                'laboratorio' => 'Laboratorios Ramos S.A.',
                'registro_sanitario' => 'MINSA-R-03912-CON',
                'tipo_control' => 'receta_retenida',
                'precio_compra' => 4.50,
                'precio_venta' => 12.00,
                'stock_minimo' => 20,
                'ubicacion' => 'Caja de Seguridad Psicotrópicos',
                'requiere_receta' => true,
                'presentaciones' => [
                    ['nombre' => 'Unidad (Cápsula)', 'unidades' => 1, 'compra' => 4.50, 'venta' => 12.00, 'es_base' => true],
                    ['nombre' => 'Blíster x 10', 'unidades' => 10, 'compra' => 42.00, 'venta' => 120.00, 'es_base' => false],
                    ['nombre' => 'Caja x 20', 'unidades' => 20, 'compra' => 80.00, 'venta' => 220.00, 'es_base' => false],
                ],
                'lotes' => [
                    ['numero' => 'LOT-RAM-TRA01', 'vencimiento' => Carbon::now()->addMonths(24), 'stock' => 10, 'compra' => 4.50] // Stock bajo (Alerta)
                ]
            ],
        ];

        // Sembrar productos, presentaciones y lotes iniciales con Kardex
        $createdProducts = [];
        $createdPresentations = [];
        $createdLotes = [];
        $proveedorCEFA = $proveedores['Droguería CEFA Nicaragua S.A.'];
        $proveedorRamos = $proveedores['Droguería Ramos S.A.'];

        foreach ($productosData as $pData) {
            $cat = $categorias[$pData['categoria']] ?? $categorias['Analgésicos y Antipiréticos'];
            $lab = $laboratorios[$pData['laboratorio']] ?? $laboratorios['Laboratorios Ramos S.A.'];

            $producto = Producto::updateOrCreate(
                ['codigo_barra' => $pData['codigo_barra']],
                [
                    'nombre' => $pData['nombre'],
                    'principio_activo' => $pData['principio_activo'],
                    'concentracion' => $pData['concentracion'],
                    'forma_farmaceutica' => $pData['forma_farmaceutica'],
                    'descripcion' => "Medicamento de alta calidad para {$cat->nombre}.",
                    'categoria_id' => $cat->id,
                    'laboratorio_id' => $lab->id,
                    'registro_sanitario' => $pData['registro_sanitario'],
                    'tipo_control' => $pData['tipo_control'],
                    'precio_compra' => $pData['precio_compra'],
                    'precio_venta' => $pData['precio_venta'],
                    'stock_minimo' => $pData['stock_minimo'],
                    'ubicacion' => $pData['ubicacion'],
                    'requiere_receta' => $pData['requiere_receta'],
                    'activo' => true,
                ]
            );

            $createdProducts[$pData['nombre']] = $producto;

            // Presentaciones
            $orden = 1;
            foreach ($pData['presentaciones'] as $pres) {
                $pObj = PresentacionProducto::updateOrCreate(
                    ['producto_id' => $producto->id, 'nombre' => $pres['nombre']],
                    [
                        'unidades_por_presentacion' => $pres['unidades'],
                        'precio_compra' => $pres['compra'],
                        'precio_venta' => $pres['venta'],
                        'es_unidad_base' => $pres['es_base'],
                        'activo' => true,
                        'orden' => $orden++,
                    ]
                );
                $createdPresentations[$producto->id][$pres['nombre']] = $pObj;
            }

            // Lotes y Kardex
            foreach ($pData['lotes'] as $lData) {
                $lote = Lote::firstOrCreate(
                    ['producto_id' => $producto->id, 'numero_lote' => $lData['numero']],
                    [
                        'proveedor_id' => str_contains($lData['numero'], 'RAM') ? $proveedorRamos->id : $proveedorCEFA->id,
                        'fecha_vencimiento' => $lData['vencimiento'],
                        'stock_inicial' => $lData['stock'],
                        'stock_actual' => $lData['stock'],
                        'precio_compra' => $lData['compra'],
                        'activo' => true,
                    ]
                );

                $createdLotes[$producto->id][] = $lote;

                // Movimiento Kardex de Entrada Inicial si no existe
                if (!MovimientoInventario::where('lote_id', $lote->id)->where('subtipo', 'compra')->exists()) {
                    MovimientoInventario::create([
                        'producto_id' => $producto->id,
                        'lote_id' => $lote->id,
                        'user_id' => $admin->id,
                        'tipo' => 'entrada',
                        'subtipo' => 'compra',
                        'cantidad' => $lData['stock'],
                        'stock_anterior' => 0,
                        'stock_posterior' => $lData['stock'],
                        'costo_unitario' => $lData['compra'],
                        'costo_total' => round($lData['stock'] * $lData['compra'], 2),
                        'origen' => 'inventario_inicial',
                        'motivo' => "Ingreso inicial de lote {$lote->numero_lote}",
                        'fecha_movimiento' => Carbon::now()->subDays(10),
                    ]);
                }
            }
        }

        // =========================================================================
        // 6. RECETAS MÉDICAS REALISTAS EN NICARAGUA
        // =========================================================================
        $recetasData = [
            [
                'cliente' => $clientes['001-140585-0023K'], // Juan Martínez
                'paciente_nombre' => 'Juan Alberto Martínez Silva',
                'paciente_documento' => '001-140585-0023K',
                'paciente_edad' => 41,
                'medico_nombre' => 'Dr. Silvio Mendoza Arana',
                'medico_colegiatura' => 'MINSA-MED-38412',
                'medico_especialidad' => 'Medicina Interna',
                'institucion_salud' => 'Hospital Bautista de Managua',
                'numero_receta' => 'REC-HB-2026-00412',
                'fecha_emision' => Carbon::now()->subDays(2),
                'fecha_vencimiento' => Carbon::now()->addDays(28),
                'tipo_receta' => 'simple',
                'estado' => 'dispensada_total',
                'observaciones' => 'Cuadro infeccioso bacteriano respiratorio agudo.',
                'detalles' => [
                    ['producto' => 'Amoxicilina + Ácido Clavulánico 875/125mg Roemmers', 'cantidad' => 14, 'dispensada' => 14, 'posologia' => '1 tableta cada 12 horas por 7 días']
                ]
            ],
            [
                'cliente' => $clientes['001-220890-0012L'], // Dra. María Gutiérrez
                'paciente_nombre' => 'María Elena Gutiérrez Rostrán',
                'paciente_documento' => '001-220890-0012L',
                'paciente_edad' => 36,
                'medico_nombre' => 'Dra. Claudia Morales Fonseca',
                'medico_colegiatura' => 'MINSA-PSI-49102',
                'medico_especialidad' => 'Psiquiatría Clínica',
                'institucion_salud' => 'Hospital Militar Escuela Dr. Alejandro Dávila Bolaños',
                'numero_receta' => 'REC-HM-2026-00891',
                'fecha_emision' => Carbon::now()->subDays(1),
                'fecha_vencimiento' => Carbon::now()->addDays(29),
                'tipo_receta' => 'retenida',
                'estado' => 'pendiente', // Alerta en Dashboard
                'observaciones' => 'Trastorno de ansiedad generalizada e insomnio severo.',
                'detalles' => [
                    ['producto' => 'Alprazolam 0.5mg Roemmers (Tranquinal)', 'cantidad' => 30, 'dispensada' => 0, 'posologia' => '1/2 tableta por las noches antes de dormir']
                ]
            ],
            [
                'cliente' => $clientes['401-191162-0001H'], // Doña Carmen Espinoza
                'paciente_nombre' => 'Carmen Rosa Espinoza Valle',
                'paciente_documento' => '401-191162-0001H',
                'paciente_edad' => 64,
                'medico_nombre' => 'Dr. Jorge Luis Arana Castillo',
                'medico_colegiatura' => 'MINSA-CAR-29184',
                'medico_especialidad' => 'Cardiología',
                'institucion_salud' => 'Hospital Metropolitano Vivian Pellas',
                'numero_receta' => 'REC-HVP-2026-01550',
                'fecha_emision' => Carbon::now()->subDays(3),
                'fecha_vencimiento' => Carbon::now()->addDays(27),
                'tipo_receta' => 'simple',
                'estado' => 'dispensada_total',
                'observaciones' => 'Hipertensión arterial estadio II y diabetes mellitus 2.',
                'detalles' => [
                    ['producto' => 'Losartán Potásico 50mg Ramos', 'cantidad' => 60, 'dispensada' => 60, 'posologia' => '1 tableta cada 12 horas fija'],
                    ['producto' => 'Metformina 850mg MK', 'cantidad' => 60, 'dispensada' => 60, 'posologia' => '1 tableta después del almuerzo']
                ]
            ],
            [
                'cliente' => $clientes['281-050378-0005M'], // Roberto Mendoza
                'paciente_nombre' => 'Roberto Carlos Mendoza Fonseca',
                'paciente_documento' => '281-050378-0005M',
                'paciente_edad' => 48,
                'medico_nombre' => 'Dr. Carlos Alberto Toruño',
                'medico_colegiatura' => 'MINSA-TRA-18450',
                'medico_especialidad' => 'Traumatología y Ortopedia',
                'institucion_salud' => 'Hospital Escuela Oscar Danilo Rosales (HEODRA León)',
                'numero_receta' => 'REC-LEO-2026-00332',
                'fecha_emision' => Carbon::now(),
                'fecha_vencimiento' => Carbon::now()->addDays(30),
                'tipo_receta' => 'retenida',
                'estado' => 'pendiente', // Alerta en Dashboard
                'observaciones' => 'Postoperatorio de rodilla y dolor neuropático agudo.',
                'detalles' => [
                    ['producto' => 'Tramadol 50mg Ramos', 'cantidad' => 20, 'dispensada' => 0, 'posologia' => '1 cápsula cada 8 horas por 5 días según dolor']
                ]
            ]
        ];

        foreach ($recetasData as $rData) {
            $receta = Receta::firstOrCreate(
                ['numero_receta' => $rData['numero_receta']],
                [
                    'cliente_id' => $rData['cliente']->id,
                    'paciente_nombre' => $rData['paciente_nombre'],
                    'paciente_documento' => $rData['paciente_documento'],
                    'paciente_edad' => $rData['paciente_edad'],
                    'medico_nombre' => $rData['medico_nombre'],
                    'medico_colegiatura' => $rData['medico_colegiatura'],
                    'medico_especialidad' => $rData['medico_especialidad'],
                    'institucion_salud' => $rData['institucion_salud'],
                    'fecha_emision' => $rData['fecha_emision'],
                    'fecha_vencimiento' => $rData['fecha_vencimiento'],
                    'tipo_receta' => $rData['tipo_receta'],
                    'estado' => $rData['estado'],
                    'observaciones' => $rData['observaciones'],
                ]
            );

            foreach ($rData['detalles'] as $d) {
                $prod = $createdProducts[$d['producto']] ?? null;
                if ($prod) {
                    RecetaDetalle::firstOrCreate(
                        ['receta_id' => $receta->id, 'producto_id' => $prod->id],
                        [
                            'cantidad_recetada' => $d['cantidad'],
                            'cantidad_dispensada' => $d['dispensada'],
                            'posologia' => $d['posologia'],
                        ]
                    );
                }
            }
        }

        // =========================================================================
        // 7. SESIONES DE CAJA Y ARQUEOS
        // =========================================================================
        $caja1 = Caja::firstOrCreate(
            ['codigo' => 'CAJA-01'],
            ['nombre' => 'Caja Principal 01', 'ubicacion' => 'Mostrador Central', 'activo' => true]
        );

        // Sesión de caja previa cerrada
        $sesionAyer = SesionCaja::firstOrCreate(
            ['caja_id' => $caja1->id, 'fecha_apertura' => Carbon::yesterday()->setHour(8)->setMinute(0)],
            [
                'user_id' => $cajero->id,
                'monto_inicial' => 1000.00,
                'observaciones_apertura' => 'Apertura de turno matutino con fondo en Córdobas.',
                'fecha_cierre' => Carbon::yesterday()->setHour(18)->setMinute(0),
                'cerrado_por' => $admin->id,
                'monto_final_efectivo' => 4520.00,
                'monto_esperado_efectivo' => 4520.00,
                'diferencia_efectivo' => 0.00,
                'total_ventas_efectivo' => 3520.00,
                'total_ventas_tarjeta' => 1250.00,
                'total_ventas_transferencia' => 0.00,
                'total_ventas' => 4770.00,
                'total_ingresos_manuales' => 0.00,
                'total_egresos_manuales' => 0.00,
                'estado' => 'cerrada',
                'observaciones_cierre' => 'Arqueo cuadrado sin diferencias. Turno entregado conforme.',
            ]
        );

        // Sesión de caja activa de hoy
        $sesionHoy = SesionCaja::firstOrCreate(
            ['caja_id' => $caja1->id, 'estado' => 'abierta'],
            [
                'user_id' => $cajero->id,
                'monto_inicial' => 1500.00,
                'fecha_apertura' => Carbon::today()->setHour(8)->setMinute(0),
                'observaciones_apertura' => 'Apertura de caja diaria para facturación mostrador.',
                'total_ventas_efectivo' => 0.00,
                'total_ventas_tarjeta' => 0.00,
                'total_ventas_transferencia' => 0.00,
                'total_ventas' => 0.00,
                'total_ingresos_manuales' => 0.00,
                'total_egresos_manuales' => 0.00,
                'monto_esperado_efectivo' => 1500.00,
                'diferencia_efectivo' => 0.00,
                'estado' => 'abierta',
            ]
        );

        // =========================================================================
        // 8. COMPRAS RECIBIDAS DE DROGUERÍAS
        // =========================================================================
        $compra1 = Compra::firstOrCreate(
            ['numero_comprobante' => 'FAC-CEFA-2026-9812'],
            [
                'proveedor_id' => $proveedorCEFA->id,
                'user_id' => $admin->id,
                'subtotal' => 14500.00,
                'impuesto' => 0.00,
                'total' => 14500.00,
                'estado' => 'recibida',
                'fecha' => Carbon::now()->subDays(5),
            ]
        );

        $compra2 = Compra::firstOrCreate(
            ['numero_comprobante' => 'FAC-RAMOS-2026-4510'],
            [
                'proveedor_id' => $proveedorRamos->id,
                'user_id' => $admin->id,
                'subtotal' => 8950.00,
                'impuesto' => 0.00,
                'total' => 8950.00,
                'estado' => 'recibida',
                'fecha' => Carbon::now()->subDays(2),
            ]
        );

        // =========================================================================
        // 9. VENTAS COMPLETADAS (Históricas y de Hoy)
        // =========================================================================
        $ventasSimuladas = [
            [
                'cliente' => $clientes['00000000'],
                'tipo' => 'ticket',
                'numero' => 'TICK-000101',
                'metodo' => 'efectivo',
                'fecha' => Carbon::today()->setHour(9)->setMinute(15),
                'sesion' => $sesionHoy,
                'items' => [
                    ['producto' => 'Acetaminofén 500mg Ramos', 'presentacion' => 'Blíster x 10', 'cantidad' => 2, 'precio' => 18.00],
                    ['producto' => 'Ambroxol Jarabe Adulto 30mg/5ml Rarpe', 'presentacion' => 'Frasco x 120 ml', 'cantidad' => 1, 'precio' => 130.00],
                ]
            ],
            [
                'cliente' => $clientes['001-140585-0023K'],
                'tipo' => 'factura',
                'numero' => 'FAC-000201',
                'metodo' => 'tarjeta',
                'fecha' => Carbon::today()->setHour(10)->setMinute(40),
                'sesion' => $sesionHoy,
                'items' => [
                    ['producto' => 'Amoxicilina + Ácido Clavulánico 875/125mg Roemmers', 'presentacion' => 'Caja x 14', 'cantidad' => 1, 'precio' => 450.00],
                    ['producto' => 'Ibuprofeno 600mg MK', 'presentacion' => 'Blíster x 10', 'cantidad' => 1, 'precio' => 75.00],
                    ['producto' => 'Vitamina C 1000mg Efervescente Redoxon', 'presentacion' => 'Tubo x 10 Tabletas Efervescentes', 'cantidad' => 1, 'precio' => 160.00],
                ]
            ],
            [
                'cliente' => $clientes['401-191162-0001H'],
                'tipo' => 'boleta',
                'numero' => 'BOL-000301',
                'metodo' => 'efectivo',
                'fecha' => Carbon::today()->setHour(11)->setMinute(30),
                'sesion' => $sesionHoy,
                'items' => [
                    ['producto' => 'Losartán Potásico 50mg Ramos', 'presentacion' => 'Caja x 100', 'cantidad' => 1, 'precio' => 320.00],
                    ['producto' => 'Metformina 850mg MK', 'presentacion' => 'Caja x 60', 'cantidad' => 1, 'precio' => 250.00],
                    ['producto' => 'Omeprazol 20mg Cevallos', 'presentacion' => 'Blíster x 10', 'cantidad' => 2, 'precio' => 35.00],
                ]
            ],
            [
                'cliente' => $clientes['00000000'],
                'tipo' => 'ticket',
                'numero' => 'TICK-000102',
                'metodo' => 'efectivo',
                'fecha' => Carbon::today()->setHour(12)->setMinute(10),
                'sesion' => $sesionHoy,
                'items' => [
                    ['producto' => 'Electrolit Suero Oral 500ml Fresa', 'presentacion' => 'Botella x 500 ml', 'cantidad' => 2, 'precio' => 70.00],
                    ['producto' => 'Loratadina 10mg MK', 'presentacion' => 'Blíster x 10', 'cantidad' => 1, 'precio' => 45.00],
                ]
            ],
            [
                'cliente' => $clientes['001-180199-0031W'],
                'tipo' => 'factura',
                'numero' => 'FAC-000202',
                'metodo' => 'tarjeta',
                'fecha' => Carbon::yesterday()->setHour(14)->setMinute(20),
                'sesion' => $sesionAyer,
                'items' => [
                    ['producto' => 'Neurobión Forte Bayer (Grageas)', 'presentacion' => 'Caja x 30', 'cantidad' => 1, 'precio' => 480.00],
                    ['producto' => 'Quadriderm Crema Tópica 40g Bayer', 'presentacion' => 'Tubo x 40 g', 'cantidad' => 1, 'precio' => 280.00],
                ]
            ],
            [
                'cliente' => $clientes['001-250982-0044V'],
                'tipo' => 'boleta',
                'numero' => 'BOL-000302',
                'metodo' => 'transferencia',
                'fecha' => Carbon::yesterday()->setHour(16)->setMinute(45),
                'sesion' => $sesionAyer,
                'items' => [
                    ['producto' => 'Diclofenaco Sódico 75mg/3ml Inyectable Ramos', 'presentacion' => 'Caja x 5 Ampollas', 'cantidad' => 2, 'precio' => 110.00],
                    ['producto' => 'Dexametasona 8mg/2ml Inyectable Stein', 'presentacion' => 'Ampolla Individual 2ml', 'cantidad' => 2, 'precio' => 35.00],
                ]
            ]
        ];

        foreach ($ventasSimuladas as $vData) {
            $totalVenta = 0;
            foreach ($vData['items'] as $item) {
                $totalVenta += ($item['cantidad'] * $item['precio']);
            }

            $venta = Venta::firstOrCreate(
                ['numero_comprobante' => $vData['numero']],
                [
                    'cliente_id' => $vData['cliente']->id,
                    'user_id' => $cajero->id,
                    'sesion_caja_id' => $vData['sesion']->id,
                    'tipo_comprobante' => $vData['tipo'],
                    'serie' => '001',
                    'subtotal' => $totalVenta,
                    'descuento' => 0.00,
                    'impuesto' => 0.00,
                    'total' => $totalVenta,
                    'metodo_pago' => $vData['metodo'],
                    'estado' => 'completada',
                    'fecha' => $vData['fecha'],
                ]
            );

            // Detalle de Venta y Descuento FIFO
            foreach ($vData['items'] as $item) {
                $prod = $createdProducts[$item['producto']] ?? null;
                if (!$prod) continue;

                $pres = $createdPresentations[$prod->id][$item['presentacion']] ?? null;
                $lotes = $createdLotes[$prod->id] ?? [];
                $lote = $lotes[0] ?? null;

                if ($lote) {
                    $unidadesPorPres = $pres ? $pres->unidades_por_presentacion : 1;
                    $unidadesBase = $item['cantidad'] * $unidadesPorPres;
                    $subtotalItem = $item['cantidad'] * $item['precio'];

                    DetalleVenta::firstOrCreate(
                        ['venta_id' => $venta->id, 'producto_id' => $prod->id, 'lote_id' => $lote->id],
                        [
                            'presentacion_id' => $pres?->id,
                            'cantidad' => $item['cantidad'],
                            'unidades_por_presentacion' => $unidadesPorPres,
                            'cantidad_unidades_base' => $unidadesBase,
                            'precio_unitario' => $item['precio'],
                            'subtotal' => $subtotalItem,
                        ]
                    );

                    // Descontar stock y registrar Kardex si no se ha descontado
                    if (!MovimientoInventario::where('origen', 'venta')->where('origen_id', $venta->id)->where('lote_id', $lote->id)->exists()) {
                        $stockAnterior = $lote->stock_actual;
                        $stockNuevo = max(0, $stockAnterior - $unidadesBase);
                        $lote->update(['stock_actual' => $stockNuevo]);

                        MovimientoInventario::create([
                            'producto_id' => $prod->id,
                            'lote_id' => $lote->id,
                            'user_id' => $cajero->id,
                            'tipo' => 'salida',
                            'subtipo' => 'venta',
                            'cantidad' => -$unidadesBase,
                            'stock_anterior' => $stockAnterior,
                            'stock_posterior' => $stockNuevo,
                            'costo_unitario' => $lote->precio_compra,
                            'costo_total' => round($unidadesBase * $lote->precio_compra, 2),
                            'origen' => 'venta',
                            'origen_id' => $venta->id,
                            'motivo' => "Despacho por Venta #{$venta->id} ({$venta->numero_comprobante})",
                            'fecha_movimiento' => $vData['fecha'],
                        ]);
                    }
                }
            }
        }

        // Actualizar totales acumulados de la sesión de caja abierta de hoy
        $ventasHoyEfectivo = Venta::where('sesion_caja_id', $sesionHoy->id)->where('metodo_pago', 'efectivo')->sum('total');
        $ventasHoyTarjeta = Venta::where('sesion_caja_id', $sesionHoy->id)->where('metodo_pago', 'tarjeta')->sum('total');
        $ventasHoyTotal = Venta::where('sesion_caja_id', $sesionHoy->id)->sum('total');

        $sesionHoy->update([
            'total_ventas_efectivo' => $ventasHoyEfectivo,
            'total_ventas_tarjeta' => $ventasHoyTarjeta,
            'total_ventas' => $ventasHoyTotal,
            'monto_esperado_efectivo' => $sesionHoy->monto_inicial + $ventasHoyEfectivo,
        ]);

        // =========================================================================
        // 10. PROMOCIONES Y DESCUENTOS INICIALES (1 Activa y 1 Inactiva)
        // =========================================================================
        $categoriaAnalgesicos = $categorias['Analgésicos y Antipiréticos'] ?? null;
        $categoriaVitaminas = $categorias['Vitaminas, Minerales y Suplementos'] ?? null;

        // 1. Promoción Activa: 20% de Descuento en Analgésicos y Fiebre
        Promocion::updateOrCreate(
            ['nombre' => 'Campaña Alivio: 20% Descuento en Analgésicos'],
            [
                'descripcion' => '20% de descuento directo en todos los medicamentos de la categoría Analgésicos y Antipiréticos en mostrador.',
                'tipo' => 'porcentaje',
                'valor' => 20.00,
                'alcance' => 'categoria',
                'producto_id' => null,
                'categoria_id' => $categoriaAnalgesicos?->id,
                'laboratorio_id' => null,
                'fecha_inicio' => Carbon::now()->subDays(7),
                'fecha_fin' => Carbon::now()->addMonths(2),
                'min_unidades' => 1,
                'stock_limite' => 500,
                'stock_consumido' => 18,
                'activo' => true,
            ]
        );

        // 2. Promoción Inactiva: Combo 2x1 en Vitaminas y Suplementos (Inactiva / Fuera de Temporada)
        Promocion::updateOrCreate(
            ['nombre' => 'Combo 2x1 en Suplementos y Vitaminas (Inactiva)'],
            [
                'descripcion' => 'Lleva 2 y paga 1 en la línea de Vitaminas, Minerales y Suplementos. Promoción actualmente desactivada.',
                'tipo' => '2x1',
                'valor' => 0.00,
                'alcance' => 'categoria',
                'producto_id' => null,
                'categoria_id' => $categoriaVitaminas?->id,
                'laboratorio_id' => null,
                'fecha_inicio' => Carbon::now()->subMonths(2),
                'fecha_fin' => Carbon::now()->subDays(5),
                'min_unidades' => 2,
                'stock_limite' => 100,
                'stock_consumido' => 100,
                'activo' => false,
            ]
        );

        echo " Base de datos poblada exitosamente con el ecosistema farmacéutico completo de Nicaragua (40 Medicamentos, Presentaciones, Lotes, Recetas, Compras, Cajas, Ventas y Promociones Activa/Inactiva).\n";
    }
}
