<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCompraRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Solo admin puede modificar compras
        return $this->user()->can('anular compras');
    }

    public function rules(): array
    {
        return [
            'motivo' => 'required|string|min:10|max:200',
            
            // Mismas validaciones que StoreCompraRequest
            'proveedor_id' => 'required|exists:proveedores,id',
            'fecha' => 'nullable|date|before_or_equal:today',
            
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
            'motivo.required' => 'Debe indicar el motivo de la modificación.',
            'motivo.min' => 'El motivo debe tener al menos 10 caracteres.',
            'motivo.max' => 'El motivo no puede exceder 200 caracteres.',
            
            'proveedor_id.required' => 'Debe seleccionar un proveedor.',
            'productos.required' => 'Debe agregar al menos un producto.',
            'productos.*.fecha_vencimiento.after' => 'La fecha de vencimiento debe ser posterior a hoy.',
        ];
    }
}
