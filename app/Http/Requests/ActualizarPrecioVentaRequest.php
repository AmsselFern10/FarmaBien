<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ActualizarPrecioVentaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('editar precios') || $this->user()->can('editar productos');
    }

    public function rules(): array
    {
        return [
            'precio_base'                  => ['required', 'numeric', 'min:0.01', 'max:9999999.99'],
            'motivo'                       => ['required', 'string', 'min:3', 'max:500'],
            'vigente_desde'                => ['nullable', 'date'],
            'presentaciones'               => ['nullable', 'array'],
            'presentaciones.*.id'          => ['required_with:presentaciones', 'integer', 'exists:presentaciones_producto,id'],
            'presentaciones.*.precio_venta'=> ['required_with:presentaciones', 'numeric', 'min:0.01', 'max:9999999.99'],
        ];
    }

    public function messages(): array
    {
        return [
            'precio_base.required' => 'El precio de venta base es obligatorio.',
            'precio_base.min'      => 'El precio de venta base debe ser mayor a 0.',
            'motivo.required'      => 'El motivo del cambio de precio es obligatorio para fines de auditoría.',
            'motivo.min'           => 'Indique un motivo claro de al menos 3 caracteres.',
        ];
    }
}
