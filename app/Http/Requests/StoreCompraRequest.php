<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompraRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('registrar compras');
    }

    public function rules(): array
    {
        return [
            'proveedor_id' => 'required|exists:proveedores,id',
            'fecha' => 'nullable|date|before_or_equal:today',
            
            // Productos
            'productos' => 'required|array|min:1',
            'productos.*.producto_id' => 'required|exists:productos,id',
            'productos.*.numero_lote' => 'required|string|max:50',
            'productos.*.fecha_vencimiento' => 'required|date|after:today',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.precio_unitario' => 'required|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'proveedor_id.required' => 'Debe seleccionar un proveedor.',
            'proveedor_id.exists' => 'El proveedor seleccionado no existe.',
            'fecha.before_or_equal' => 'La fecha no puede ser futura.',
            
            'productos.required' => 'Debe agregar al menos un producto.',
            'productos.min' => 'Debe agregar al menos un producto.',
            
            'productos.*.numero_lote.required' => 'El número de lote es obligatorio.',
            'productos.*.fecha_vencimiento.required' => 'La fecha de vencimiento es obligatoria.',
            'productos.*.fecha_vencimiento.after' => 'La fecha de vencimiento debe ser posterior a hoy.',
            'productos.*.cantidad.required' => 'La cantidad es obligatoria.',
            'productos.*.cantidad.min' => 'La cantidad debe ser al menos 1.',
            'productos.*.precio_unitario.required' => 'El precio de compra es obligatorio.',
            'productos.*.precio_unitario.min' => 'El precio debe ser mayor a 0.',
        ];
    }
}