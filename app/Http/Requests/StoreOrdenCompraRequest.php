<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrdenCompraRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->can('registrar compras');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'proveedor_id'            => ['required', 'integer', 'exists:proveedores,id'],
            'fecha_emision'           => ['required', 'date'],
            'fecha_esperada_entrega'  => ['nullable', 'date', 'after_or_equal:fecha_emision'],
            'condicion_pago'          => ['required', 'in:contado,credito'],
            'dias_credito'            => ['nullable', 'integer', 'min:0', 'max:180'],
            'observaciones'           => ['nullable', 'string', 'max:500'],
            'items'                   => ['required', 'array', 'min:1'],
            'items.*.producto_id'     => ['required', 'integer', 'exists:productos,id'],
            'items.*.cantidad'        => ['required', 'integer', 'min:1'],
            'items.*.precio_unitario' => ['required', 'numeric', 'min:0'],
        ];
    }

    /**
     * Custom error messages in Spanish.
     */
    public function messages(): array
    {
        return [
            'proveedor_id.required'                => 'Debe seleccionar un proveedor destinatario.',
            'proveedor_id.exists'                  => 'El proveedor seleccionado no es válido.',
            'fecha_emision.required'               => 'La fecha de emisión es obligatoria.',
            'fecha_esperada_entrega.after_or_equal'=> 'La fecha estimada de entrega no puede ser anterior a la fecha de emisión.',
            'condicion_pago.required'              => 'La condición de pago es obligatoria.',
            'condicion_pago.in'                    => 'La condición de pago debe ser Contado o Crédito.',
            'items.required'                       => 'Debe incluir al menos un medicamento en la orden de compra.',
            'items.min'                            => 'Debe incluir al menos un medicamento en la orden de compra.',
            'items.*.producto_id.required'         => 'Cada línea debe tener un medicamento válido.',
            'items.*.producto_id.exists'           => 'Uno de los medicamentos seleccionados no existe en el catálogo.',
            'items.*.cantidad.required'            => 'La cantidad solicitada es obligatoria en cada ítem.',
            'items.*.cantidad.min'                 => 'La cantidad solicitada debe ser de al menos 1 unidad.',
            'items.*.precio_unitario.required'     => 'El precio unitario estimado es obligatorio.',
            'items.*.precio_unitario.min'          => 'El precio unitario estimado no puede ser negativo.',
        ];
    }

    /**
     * Custom attribute names for validation.
     */
    public function attributes(): array
    {
        return [
            'proveedor_id'            => 'proveedor',
            'fecha_emision'           => 'fecha de emisión',
            'fecha_esperada_entrega'  => 'fecha estimada de entrega',
            'condicion_pago'          => 'condición de pago',
            'dias_credito'            => 'días de crédito',
            'items'                   => 'medicamentos',
            'items.*.producto_id'     => 'medicamento',
            'items.*.cantidad'        => 'cantidad',
            'items.*.precio_unitario' => 'precio estimado',
        ];
    }
}
