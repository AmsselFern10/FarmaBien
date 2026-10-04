<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ActualizarPrecioInlineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('editar precios') || $this->user()->can('editar productos');
    }

    public function rules(): array
    {
        return [
            'precio_venta' => ['required', 'numeric', 'min:0.01', 'max:9999999.99'],
            'motivo'       => ['required', 'string', 'min:3', 'max:300'],
        ];
    }

    public function messages(): array
    {
        return [
            'precio_venta.required' => 'El precio de venta es obligatorio.',
            'precio_venta.min'      => 'El precio de venta debe ser mayor a 0.',
            'motivo.required'       => 'El motivo del cambio es obligatorio.',
            'motivo.min'            => 'El motivo debe contener al menos 3 caracteres.',
        ];
    }
}
