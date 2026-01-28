<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AnularVentaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('anular ventas');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'motivo' => 'required|string|min:10|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'motivo.required' => 'Debe indicar el motivo de la anulación.',
            'motivo.string' => 'El motivo debe ser un texto válido.',
            'motivo.min' => 'El motivo debe tener al menos 10 caracteres.',
            'motivo.max' => 'El motivo no puede exceder 500 caracteres.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'motivo' => 'motivo de anulación',
        ];
    }

    /**
     * Validación adicional
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validar que la venta pueda anularse
            $venta = $this->route('venta');
            
            if ($venta && !$venta->puedeAnularse()) {
                $validator->errors()->add(
                    'venta',
                    'Esta venta no puede ser anulada. Puede estar ya anulada o haber sido reemplazada.'
                );
            }
        });
    }
}