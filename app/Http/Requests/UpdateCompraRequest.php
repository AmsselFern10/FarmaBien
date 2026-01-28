<?php

/**
 * ACTUALIZAR STORECOMPRAREQUEST PARA VALIDAR PRESENTACIONES
 * 
 * Archivo: app/Http/Requests/StoreCompraRequest.php
 */

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompraRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('realizar compras');
    }

    public function rules(): array
    {
        return [
            'proveedor_id' => 'required|exists:proveedores,id',
            'fecha' => 'nullable|date|before_or_equal:today',
            
            // Productos con presentaciones
            'productos' => 'required|array|min:1',
            'productos.*.producto_id' => 'required|exists:productos,id',
            
            // Presentación (opcional - NULL = unidad base)
            'productos.*.presentacion_id' => 'nullable|exists:presentaciones_producto,id',
            
            // Cantidad de presentaciones compradas
            'productos.*.cantidad_presentaciones' => 'required|integer|min:1',
            
            // Unidades por presentación (si no hay presentacion_id, debe ser 1)
            'productos.*.unidades_por_presentacion' => 'required|integer|min:1',
            
            // Precio unitario (por unidad base)
            'productos.*.precio_unitario' => 'required|numeric|min:0',
            
            // Lote
            'productos.*.numero_lote' => 'required|string|max:50',
            'productos.*.fecha_vencimiento' => 'required|date|after:today',
        ];
    }

    public function messages(): array
    {
        return [
            'proveedor_id.required' => 'Debe seleccionar un proveedor.',
            'proveedor_id.exists' => 'El proveedor seleccionado no existe.',
            
            'fecha.date' => 'La fecha debe ser válida.',
            'fecha.before_or_equal' => 'La fecha de compra no puede ser futura.',
            
            'productos.required' => 'Debe agregar al menos un producto.',
            'productos.min' => 'Debe agregar al menos un producto.',
            
            'productos.*.producto_id.required' => 'El producto es obligatorio.',
            'productos.*.producto_id.exists' => 'El producto seleccionado no existe.',
            
            'productos.*.presentacion_id.exists' => 'La presentación seleccionada no existe.',
            
            'productos.*.cantidad_presentaciones.required' => 'La cantidad es obligatoria.',
            'productos.*.cantidad_presentaciones.min' => 'La cantidad debe ser al menos 1.',
            
            'productos.*.unidades_por_presentacion.required' => 'Las unidades por presentación son obligatorias.',
            'productos.*.unidades_por_presentacion.min' => 'Las unidades por presentación deben ser al menos 1.',
            
            'productos.*.precio_unitario.required' => 'El precio unitario es obligatorio.',
            'productos.*.precio_unitario.min' => 'El precio debe ser mayor o igual a 0.',
            
            'productos.*.numero_lote.required' => 'El número de lote es obligatorio.',
            'productos.*.numero_lote.max' => 'El número de lote no puede exceder 50 caracteres.',
            
            'productos.*.fecha_vencimiento.required' => 'La fecha de vencimiento es obligatoria.',
            'productos.*.fecha_vencimiento.after' => 'La fecha de vencimiento debe ser futura.',
        ];
    }

    public function attributes(): array
    {
        return [
            'proveedor_id' => 'proveedor',
            'fecha' => 'fecha de compra',
            'productos' => 'productos',
            'productos.*.producto_id' => 'producto',
            'productos.*.presentacion_id' => 'presentación',
            'productos.*.cantidad_presentaciones' => 'cantidad',
            'productos.*.unidades_por_presentacion' => 'unidades por presentación',
            'productos.*.precio_unitario' => 'precio unitario',
            'productos.*.numero_lote' => 'número de lote',
            'productos.*.fecha_vencimiento' => 'fecha de vencimiento',
        ];
    }

    /**
     * Validación adicional
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validar que si hay presentacion_id, pertenece al producto
            if ($this->has('productos')) {
                foreach ($this->productos as $index => $item) {
                    if (isset($item['presentacion_id']) && $item['presentacion_id']) {
                        $presentacion = \App\Models\PresentacionProducto::find($item['presentacion_id']);
                        
                        if ($presentacion && $presentacion->producto_id != $item['producto_id']) {
                            $validator->errors()->add(
                                "productos.{$index}.presentacion_id",
                                'La presentación no corresponde al producto seleccionado.'
                            );
                        }
                        
                        if ($presentacion && !$presentacion->activo) {
                            $validator->errors()->add(
                                "productos.{$index}.presentacion_id",
                                'La presentación seleccionada no está activa.'
                            );
                        }
                    }
                    
                    // Validar coherencia: si no hay presentacion_id, unidades_por_presentacion debe ser 1
                    if (empty($item['presentacion_id']) && $item['unidades_por_presentacion'] != 1) {
                        $validator->errors()->add(
                            "productos.{$index}.unidades_por_presentacion",
                            'Para unidad base, las unidades por presentación deben ser 1.'
                        );
                    }
                }
            }
        });
    }

    /**
     * Preparar datos para validación
     */
    protected function prepareForValidation()
    {
        // Si productos viene como JSON, decodificarlo
        if ($this->has('productos') && is_string($this->productos)) {
            $this->merge([
                'productos' => json_decode($this->productos, true)
            ]);
        }

        // Asegurar valores por defecto
        if ($this->has('productos')) {
            $productos = $this->productos;
            
            foreach ($productos as $index => $item) {
                // Si no tiene presentacion_id, asegurar que sea unidad base
                if (!isset($item['presentacion_id']) || !$item['presentacion_id']) {
                    $productos[$index]['presentacion_id'] = null;
                    $productos[$index]['unidades_por_presentacion'] = 1;
                }
                
                // Si no tiene cantidad_presentaciones pero tiene cantidad (compatibilidad)
                if (!isset($item['cantidad_presentaciones']) && isset($item['cantidad'])) {
                    $productos[$index]['cantidad_presentaciones'] = $item['cantidad'];
                }
            }
            
            $this->merge(['productos' => $productos]);
        }
    }
}