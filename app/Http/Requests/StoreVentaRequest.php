<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVentaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('realizar ventas');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'cliente_id' => 'nullable|exists:clientes,id',
            'metodo_pago' => 'required|string|in:efectivo,tarjeta,transferencia,yape,plin',
            
            // Productos
            'productos' => 'required|array|min:1',
            'productos.*.producto_id' => 'required|exists:productos,id',
            'productos.*.lote_id' => 'required|exists:lotes,id',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.precio_unitario' => 'nullable|numeric|min:0',
            
            // Recetas (opcional)
            'recetas' => 'nullable|array',
            'recetas.*' => 'exists:recetas,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'cliente_id.exists' => 'El cliente seleccionado no existe.',
            'metodo_pago.required' => 'Debe seleccionar un método de pago.',
            'metodo_pago.in' => 'El método de pago no es válido.',
            
            'productos.required' => 'Debe agregar al menos un producto.',
            'productos.min' => 'Debe agregar al menos un producto.',
            'productos.*.producto_id.required' => 'El ID del producto es obligatorio.',
            'productos.*.producto_id.exists' => 'Uno de los productos seleccionados no existe.',
            'productos.*.lote_id.required' => 'Debe seleccionar un lote para cada producto.',
            'productos.*.lote_id.exists' => 'Uno de los lotes seleccionados no existe.',
            'productos.*.cantidad.required' => 'La cantidad es obligatoria.',
            'productos.*.cantidad.min' => 'La cantidad debe ser al menos 1.',
            
            'recetas.*.exists' => 'Una de las recetas seleccionadas no existe.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'cliente_id' => 'cliente',
            'metodo_pago' => 'método de pago',
            'productos' => 'productos',
            'recetas' => 'recetas médicas',
        ];
    }
}