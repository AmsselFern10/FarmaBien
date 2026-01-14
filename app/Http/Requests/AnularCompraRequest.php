<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AnularCompraRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('anular compras');
    }

    public function rules(): array
    {
        return [
            'motivo' => 'required|string|min:10|max:200',
        ];
    }

    public function messages(): array
    {
        return [
            'motivo.required' => 'Debe indicar el motivo de la anulación.',
            'motivo.min' => 'El motivo debe tener al menos 10 caracteres.',
            'motivo.max' => 'El motivo no puede exceder 200 caracteres.',
        ];
    }

    public function attributes(): array
    {
        return [
            'motivo' => 'motivo de anulación',
        ];
    }
}