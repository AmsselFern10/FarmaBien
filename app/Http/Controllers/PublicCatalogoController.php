<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Configuracion;
use Illuminate\Http\Request;

class PublicCatalogoController extends Controller
{
    /**
     * Catálogo público de medicamentos para clientes finales
     */
    public function index(Request $request)
    {
        $catalogoActivo = Configuracion::get('catalogo_publico_activo', true);

        // Si el módulo está apagado por el administrador, mostrar pantalla de mantenimiento/desactivado
        if (!$catalogoActivo) {
            return response()->view('catalogo.mantenimiento', [
                'empresaNombre'    => Configuracion::get('empresa_nombre', 'FARMABIEN'),
                'empresaTelefono'  => Configuracion::get('empresa_telefono', '(0212) 555-0199'),
                'empresaWhatsapp'  => Configuracion::get('empresa_whatsapp', '+58 412-1234567'),
                'empresaDireccion' => Configuracion::get('empresa_direccion', 'Av. Principal Los Próceres, Caracas'),
                'empresaLogo'      => Configuracion::get('empresa_logo'),
            ], 503);
        }

        $buscar = trim($request->input('buscar', ''));

        $query = Producto::activos()
            ->with(['categoria', 'laboratorio', 'lotes' => function ($q) {
                $q->activos()->vigentes();
            }]);

        // Búsqueda simplificada para clientes por Nombre comercial o Principio activo
        if (!empty($buscar)) {
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('principio_activo', 'like', "%{$buscar}%");
            });
        }

        $productos = $query->orderBy('nombre')->paginate(24)->withQueryString();

        $mostrarPrecios = Configuracion::get('catalogo_publico_mostrar_precios', true);
        $mostrarStock = Configuracion::get('catalogo_publico_mostrar_stock', true);
        $whatsappEmpresa = Configuracion::get('empresa_whatsapp', '+58 412-1234567');

        return view('catalogo.index', compact(
            'productos',
            'buscar',
            'mostrarPrecios',
            'mostrarStock',
            'whatsappEmpresa'
        ));
    }
}
