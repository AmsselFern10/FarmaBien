<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVentaRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Solo admin puede modificar ventas
        return $this->user()->can('anular ventas');
    }

    public function rules(): array
    {
        return [
            'motivo' => 'required|string|min:10|max:200',
            
            // Mismas validaciones que StoreVentaRequest
            'cliente_id' => 'nullable|exists:clientes,id',
            'metodo_pago' => 'required|string|in:efectivo,tarjeta,transferencia,yape,plin',
            
            'productos' => 'required|array|min:1',
            'productos.*.producto_id' => 'required|exists:productos,id',
            'productos.*.lote_id' => 'required|exists:lotes,id',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.precio_unitario' => 'nullable|numeric|min:0',
            
            'recetas' => 'nullable|array',
            'recetas.*' => 'exists:recetas,id',
        ];
    }

    public function messages(): array
    {
        return [
            'motivo.required' => 'Debe indicar el motivo de la modificación.',
            'motivo.min' => 'El motivo debe tener al menos 10 caracteres.',
            'motivo.max' => 'El motivo no puede exceder 200 caracteres.',
        ];
    }
}
