<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AjusteController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:ver ajustes')->only(['index']);
        $this->middleware('permission:editar ajustes')->only(['update', 'toggleCatalogo']);
    }

    /**
     * Vista principal de Ajustes y Configuración del Sistema
     */
    public function index(Request $request)
    {
        $tab = $request->input('tab', 'empresa');
        $configs = Configuracion::allAsAssoc();

        return view('ajustes.index', compact('configs', 'tab'));
    }

    /**
     * Guardar o actualizar la configuración
     */
    public function update(Request $request)
    {
        $request->validate([
            'empresa_nombre'                     => ['nullable', 'string', 'max:100'],
            'empresa_razon_social'               => ['nullable', 'string', 'max:150'],
            'empresa_ruc'                        => ['nullable', 'string', 'max:50'],
            'empresa_telefono'                   => ['nullable', 'string', 'max:100'],
            'empresa_whatsapp'                   => ['nullable', 'string', 'max:50'],
            'empresa_email'                      => ['nullable', 'email', 'max:100'],
            'empresa_direccion'                  => ['nullable', 'string', 'max:255'],
            'empresa_ciudad'                     => ['nullable', 'string', 'max:100'],
            'empresa_slogan'                     => ['nullable', 'string', 'max:255'],
            'empresa_pie_ticket'                 => ['nullable', 'string', 'max:255'],
            'empresa_logo'                       => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'interfaz_modo_oscuro_default'       => ['nullable', 'string', 'in:light,dark,system'],
            'interfaz_vista_formularios_default' => ['nullable', 'string', 'in:modern,compact'],
            'interfaz_registros_por_pagina'      => ['nullable', 'integer', 'min:10', 'max:100'],
            'catalogo_publico_activo'            => ['nullable', 'boolean'],
            'catalogo_publico_mostrar_precios'   => ['nullable', 'boolean'],
            'catalogo_publico_mostrar_stock'     => ['nullable', 'boolean'],
            'modulo_cajas_estricto'              => ['nullable', 'boolean'],
            'impresora_tipo'                     => ['nullable', 'string', 'in:red,bluetooth,usb,navegador'],
            'impresora_ip'                       => ['nullable', 'string', 'max:50'],
            'impresora_puerto'                   => ['nullable', 'integer', 'min:1', 'max:65535'],
            'impresora_ancho_papel'              => ['nullable', 'string', 'in:58,80'],
            'impresora_corte_automatico'         => ['nullable', 'boolean'],
            'impresora_abrir_cajon'              => ['nullable', 'boolean'],
            'impresora_impresion_automatica'     => ['nullable', 'boolean'],
            'cajon_tipo'                         => ['nullable', 'string', 'in:escpos,usb,manual'],
            'lector_modo'                        => ['nullable', 'string', 'in:hid,usb_serial,camara'],
            'lector_sufijo'                      => ['nullable', 'string', 'in:enter,tab,none'],
        ]);

        $tab = $request->input('tab', 'empresa');

        // Manejo de carga de logo
        if ($request->hasFile('empresa_logo')) {
            $path = $request->file('empresa_logo')->store('logos', 'public');
            Configuracion::set('empresa_logo', $path, 'empresa', 'image', 'Logotipo oficial de la farmacia');
        } elseif ($request->boolean('eliminar_logo')) {
            $logoAnterior = Configuracion::get('empresa_logo');
            if ($logoAnterior && Storage::disk('public')->exists($logoAnterior)) {
                Storage::disk('public')->delete($logoAnterior);
            }
            Configuracion::set('empresa_logo', null, 'empresa', 'image', 'Logotipo oficial de la farmacia');
        }

        // Empresa
        if ($tab === 'empresa' || $request->has('empresa_nombre')) {
            if ($request->has('empresa_nombre')) Configuracion::set('empresa_nombre', $request->input('empresa_nombre'), 'empresa');
            if ($request->has('empresa_razon_social')) Configuracion::set('empresa_razon_social', $request->input('empresa_razon_social'), 'empresa');
            if ($request->has('empresa_ruc')) Configuracion::set('empresa_ruc', $request->input('empresa_ruc'), 'empresa');
            if ($request->has('empresa_telefono')) Configuracion::set('empresa_telefono', $request->input('empresa_telefono'), 'empresa');
            if ($request->has('empresa_whatsapp')) Configuracion::set('empresa_whatsapp', $request->input('empresa_whatsapp'), 'empresa');
            if ($request->has('empresa_email')) Configuracion::set('empresa_email', $request->input('empresa_email'), 'empresa');
            if ($request->has('empresa_direccion')) Configuracion::set('empresa_direccion', $request->input('empresa_direccion'), 'empresa');
            if ($request->has('empresa_ciudad')) Configuracion::set('empresa_ciudad', $request->input('empresa_ciudad'), 'empresa');
            if ($request->has('empresa_slogan')) Configuracion::set('empresa_slogan', $request->input('empresa_slogan'), 'empresa');
            if ($request->has('empresa_pie_ticket')) Configuracion::set('empresa_pie_ticket', $request->input('empresa_pie_ticket'), 'empresa');
        }

        // Interfaz
        if ($tab === 'interfaz' || $request->has('interfaz_modo_oscuro_default') || $request->has('interfaz_vista_formularios_default')) {
            if ($request->has('interfaz_modo_oscuro_default')) Configuracion::set('interfaz_modo_oscuro_default', $request->input('interfaz_modo_oscuro_default'), 'interfaz');
            if ($request->has('interfaz_vista_formularios_default')) Configuracion::set('interfaz_vista_formularios_default', $request->input('interfaz_vista_formularios_default'), 'interfaz');
            if ($request->has('interfaz_registros_por_pagina')) Configuracion::set('interfaz_registros_por_pagina', $request->input('interfaz_registros_por_pagina'), 'interfaz', 'integer');
        }

        // Módulos
        if ($tab === 'modulos' || $request->has('modulo_cajas_estricto') || $request->has('catalogo_publico_activo')) {
            Configuracion::set('catalogo_publico_activo', $request->boolean('catalogo_publico_activo'), 'modulos', 'boolean');
            Configuracion::set('catalogo_publico_mostrar_precios', $request->boolean('catalogo_publico_mostrar_precios'), 'modulos', 'boolean');
            Configuracion::set('catalogo_publico_mostrar_stock', $request->boolean('catalogo_publico_mostrar_stock'), 'modulos', 'boolean');
            Configuracion::set('modulo_cajas_estricto', $request->boolean('modulo_cajas_estricto'), 'modulos', 'boolean');
        }

        // Hardware & Periféricos
        if ($tab === 'hardware' || $request->has('impresora_tipo')) {
            if ($request->has('impresora_tipo')) Configuracion::set('impresora_tipo', $request->input('impresora_tipo'), 'hardware');
            if ($request->has('impresora_ip')) Configuracion::set('impresora_ip', $request->input('impresora_ip'), 'hardware');
            if ($request->has('impresora_puerto')) Configuracion::set('impresora_puerto', $request->input('impresora_puerto', '9100'), 'hardware', 'integer');
            if ($request->has('impresora_ancho_papel')) Configuracion::set('impresora_ancho_papel', $request->input('impresora_ancho_papel', '80'), 'hardware', 'integer');
            Configuracion::set('impresora_corte_automatico', $request->boolean('impresora_corte_automatico'), 'hardware', 'boolean');
            Configuracion::set('impresora_abrir_cajon', $request->boolean('impresora_abrir_cajon'), 'hardware', 'boolean');
            Configuracion::set('impresora_impresion_automatica', $request->boolean('impresora_impresion_automatica'), 'hardware', 'boolean');
            if ($request->has('cajon_tipo')) Configuracion::set('cajon_tipo', $request->input('cajon_tipo', 'escpos'), 'hardware');
            if ($request->has('lector_modo')) Configuracion::set('lector_modo', $request->input('lector_modo', 'hid'), 'hardware');
            if ($request->has('lector_sufijo')) Configuracion::set('lector_sufijo', $request->input('lector_sufijo', 'enter'), 'hardware');
        }

        Configuracion::clearCache();

        AuditLog::log('ajustes', 'actualizar', "Configuraciones del sistema actualizadas (pestaña: {$tab})", [
            'tab' => $tab,
            'user_id' => auth()->id(),
        ]);

        // Si se guardó la preferencia de tema o vista de formularios, pasarla como flash para que el layout
        // la propague a localStorage del navegador en la siguiente carga
        $redirect = redirect()->route('ajustes.index', ['tab' => $tab])
            ->with('success', 'Configuraciones guardadas y sincronizadas exitosamente.');

        if ($tab === 'interfaz') {
            if ($request->has('interfaz_modo_oscuro_default')) {
                $redirect = $redirect->with('tema_aplicado', $request->input('interfaz_modo_oscuro_default'));
            }
            if ($request->has('interfaz_vista_formularios_default')) {
                $redirect = $redirect->with('form_view_aplicado', $request->input('interfaz_vista_formularios_default'));
            }
        }

        return $redirect;
    }

    /**
     * Alternar rápidamente el estado del Catálogo Público (Switch Toggle AJAX / Directo)
     */
    public function toggleCatalogo(Request $request)
    {
        $actual = Configuracion::get('catalogo_publico_activo', true);
        $nuevo = !$actual;

        Configuracion::set('catalogo_publico_activo', $nuevo, 'modulos', 'boolean');
        Configuracion::clearCache();

        AuditLog::log('ajustes', 'toggle_catalogo', "Catálogo público " . ($nuevo ? 'activado' : 'desactivado'), [
            'activo' => $nuevo,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'activo'  => $nuevo,
                'mensaje' => $nuevo ? 'Catálogo público activado para clientes.' : 'Catálogo público desactivado temporalmente.',
            ]);
        }

        return back()->with('success', $nuevo ? 'Catálogo público activado para clientes.' : 'Catálogo público desactivado temporalmente.');
    }
}
