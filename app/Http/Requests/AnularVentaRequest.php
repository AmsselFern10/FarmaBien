<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AnularVentaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('anular ventas');
    }

    public function rules(): array
    {
        return [
            'motivo' => ['required', 'string', 'min:5', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'motivo.required' => 'Debes ingresar un motivo para anular la venta.',
            'motivo.min'      => 'El motivo debe contener al menos 5 caracteres.',
            'motivo.max'      => 'El motivo no puede superar los 255 caracteres.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'motivo' => $this->filled('motivo') ? trim($this->input('motivo')) : null,
        ]);
    }
}
