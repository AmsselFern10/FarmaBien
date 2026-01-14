<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AjusteInventarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('ajustar inventario');
    }

    public function rules(): array
    {
        return [
            'lote_id' => 'required|exists:lotes,id',
            'stock_nuevo' => 'required|integer|min:0',
            'motivo' => 'required|string|min:10|max:200',
        ];
    }

    public function messages(): array
    {
        return [
            'lote_id.required' => 'Debe seleccionar un lote.',
            'lote_id.exists' => 'El lote seleccionado no existe.',
            'stock_nuevo.required' => 'El stock nuevo es obligatorio.',
            'stock_nuevo.integer' => 'El stock debe ser un número entero.',
            'stock_nuevo.min' => 'El stock no puede ser negativo.',
            'motivo.required' => 'Debe indicar el motivo del ajuste.',
            'motivo.min' => 'El motivo debe tener al menos 10 caracteres.',
            'motivo.max' => 'El motivo no puede exceder 200 caracteres.',
        ];
    }
}
