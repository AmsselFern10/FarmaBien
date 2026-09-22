<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Configuracion;

class ConfiguracionSeeder extends Seeder
{
    public function run(): void
    {
        $configuraciones = [
            // Empresa / Local
            [
                'clave'       => 'empresa_nombre',
                'valor'       => 'FARMABIEN',
                'tipo'        => 'string',
                'grupo'       => 'empresa',
                'descripcion' => 'Nombre comercial de la farmacia',
            ],
            [
                'clave'       => 'empresa_razon_social',
                'valor'       => 'Farmacia & Droguería FarmaBien C.A.',
                'tipo'        => 'string',
                'grupo'       => 'empresa',
                'descripcion' => 'Razón social legal',
            ],
            [
                'clave'       => 'empresa_ruc',
                'valor'       => 'J-40892154-0',
                'tipo'        => 'string',
                'grupo'       => 'empresa',
                'descripcion' => 'Número de RIF / RUC / NIT fiscal',
            ],
            [
                'clave'       => 'empresa_telefono',
                'valor'       => '(0212) 555-0199 / +58 412-1234567',
                'tipo'        => 'string',
                'grupo'       => 'empresa',
                'descripcion' => 'Teléfonos de atención',
            ],
            [
                'clave'       => 'empresa_whatsapp',
                'valor'       => '+58 412-1234567',
                'tipo'        => 'string',
                'grupo'       => 'empresa',
                'descripcion' => 'Número de WhatsApp para pedidos de clientes',
            ],
            [
                'clave'       => 'empresa_email',
                'valor'       => 'contacto@farmabien.com',
                'tipo'        => 'string',
                'grupo'       => 'empresa',
                'descripcion' => 'Correo electrónico institucional',
            ],
            [
                'clave'       => 'empresa_direccion',
                'valor'       => 'Av. Principal Los Próceres, Edif. FarmaBien, Caracas - Venezuela',
                'tipo'        => 'string',
                'grupo'       => 'empresa',
                'descripcion' => 'Dirección física de la farmacia',
            ],
            [
                'clave'       => 'empresa_ciudad',
                'valor'       => 'Caracas, Venezuela',
                'tipo'        => 'string',
                'grupo'       => 'empresa',
                'descripcion' => 'Ciudad y país',
            ],
            [
                'clave'       => 'empresa_slogan',
                'valor'       => 'Tu salud y bienestar en las mejores manos.',
                'tipo'        => 'string',
                'grupo'       => 'empresa',
                'descripcion' => 'Lema o slogan institucional',
            ],
            [
                'clave'       => 'empresa_pie_ticket',
                'valor'       => '¡Gracias por su compra! Conserve su ticket para cambios dentro de las 48h.',
                'tipo'        => 'string',
                'grupo'       => 'empresa',
                'descripcion' => 'Mensaje de pie en tickets de venta',
            ],
            [
                'clave'       => 'empresa_logo',
                'valor'       => null,
                'tipo'        => 'image',
                'grupo'       => 'empresa',
                'descripcion' => 'Ruta del logotipo institucional',
            ],

            // Preferencias de Interfaz
            [
                'clave'       => 'interfaz_modo_oscuro_default',
                'valor'       => 'system',
                'tipo'        => 'string',
                'grupo'       => 'interfaz',
                'descripcion' => 'Preferencia de tema (light, dark, system)',
            ],
            [
                'clave'       => 'interfaz_vista_formularios_default',
                'valor'       => 'modern',
                'tipo'        => 'string',
                'grupo'       => 'interfaz',
                'descripcion' => 'Vista predeterminada de formularios en crear y editar (modern, compact)',
            ],
            [
                'clave'       => 'interfaz_registros_por_pagina',
                'valor'       => '25',
                'tipo'        => 'integer',
                'grupo'       => 'interfaz',
                'descripcion' => 'Registros de paginación por defecto',
            ],

            // Control de Módulos (Feature Flags)
            [
                'clave'       => 'catalogo_publico_activo',
                'valor'       => '1',
                'tipo'        => 'boolean',
                'grupo'       => 'modulos',
                'descripcion' => 'Habilitar acceso público al catálogo de medicamentos para clientes',
            ],
            [
                'clave'       => 'catalogo_publico_mostrar_precios',
                'valor'       => '1',
                'tipo'        => 'boolean',
                'grupo'       => 'modulos',
                'descripcion' => 'Mostrar precios en el catálogo público',
            ],
            [
                'clave'       => 'catalogo_publico_mostrar_stock',
                'valor'       => '1',
                'tipo'        => 'boolean',
                'grupo'       => 'modulos',
                'descripcion' => 'Mostrar badge de disponibilidad en catálogo público',
            ],
            [
                'clave'       => 'modulo_cajas_estricto',
                'valor'       => '1',
                'tipo'        => 'boolean',
                'grupo'       => 'modulos',
                'descripcion' => 'Exigir caja abierta para registrar ventas',
            ],
        ];

        foreach ($configuraciones as $conf) {
            Configuracion::updateOrCreate(
                ['clave' => $conf['clave']],
                $conf
            );
        }

        Configuracion::clearCache();
    }
}
