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
            'lote_id'     => ['required', 'integer', 'exists:lotes,id'],
            'stock_nuevo' => ['required', 'integer', 'min:0', 'max:1000000'],
            'subtipo'     => ['required', 'string', 'in:ajuste_manual,merma_vencimiento,merma_danio'],
            'motivo'      => ['required', 'string', 'min:5', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'lote_id.required'     => 'Debes seleccionar un lote para realizar el ajuste.',
            'lote_id.exists'       => 'El lote seleccionado no existe en el sistema.',
            'stock_nuevo.required' => 'El nuevo stock es obligatorio.',
            'stock_nuevo.integer'  => 'El stock debe ser un número entero.',
            'stock_nuevo.min'      => 'El stock no puede ser negativo.',
            'stock_nuevo.max'      => 'El stock ingresado excede el límite permitido.',
            'subtipo.required'     => 'Debes seleccionar el tipo de ajuste o merma.',
            'subtipo.in'           => 'El tipo de ajuste seleccionado no es válido.',
            'motivo.required'      => 'Debes justificar el motivo del ajuste de inventario.',
            'motivo.min'           => 'El motivo debe tener al menos 5 caracteres descriptivos.',
            'motivo.max'           => 'El motivo no puede exceder los 255 caracteres.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $subtipo = trim((string)$this->input('subtipo', 'ajuste_manual'));

        // Normalización de subtipos hacia los enums soportados por la BD
        if (in_array($subtipo, ['ajuste_positivo', 'ajuste_negativo', 'ajuste'])) {
            $subtipo = 'ajuste_manual';
        }

        $this->merge([
            'lote_id'     => (int)$this->input('lote_id'),
            'stock_nuevo' => (int)$this->input('stock_nuevo'),
            'subtipo'     => $subtipo,
            'motivo'      => trim((string)$this->input('motivo', '')),
        ]);
    }
}
