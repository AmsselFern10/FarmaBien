<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AnularDevolucionCompraRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->can('anular compras');
    }

    public function rules(): array
    {
        return [
            'motivo' => ['required', 'string', 'min:5', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'motivo.required' => 'Debe especificar el motivo de anulación de la devolución.',
            'motivo.min'      => 'El motivo de anulación debe contener al menos 5 caracteres.',
            'motivo.max'      => 'El motivo de anulación no puede exceder 500 caracteres.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'motivo' => $this->filled('motivo') ? trim($this->input('motivo')) : null,
        ]);
    }
}
