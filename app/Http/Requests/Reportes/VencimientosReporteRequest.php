<?php

namespace App\Http\Requests\Reportes;

use Illuminate\Foundation\Http\FormRequest;

class VencimientosReporteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'modo' => $this->input('modo', 'todos'),
            'dias' => (int) $this->input('dias', 30),
        ]);
    }

    public function rules(): array
    {
        return [
            'modo' => ['required', 'in:todos,vencidos,proximos'],
            'dias' => ['required', 'integer', 'min:1', 'max:365'],

            'producto_id' => ['nullable', 'integer'],
            'proveedor_id' => ['nullable', 'integer'],

            'activo' => ['nullable', 'boolean'],
            'con_stock' => ['nullable', 'boolean'],

            'export' => ['nullable', 'in:csv,excel,pdf'],
        ];
    }

    public function filters(): array
    {
        return [
            'modo' => (string) $this->input('modo', 'todos'),
            'dias' => (int) $this->input('dias', 30),
            'producto_id' => $this->input('producto_id'),
            'proveedor_id' => $this->input('proveedor_id'),
            'activo' => $this->has('activo') ? (bool) $this->boolean('activo') : null,
            'con_stock' => $this->has('con_stock') ? (bool) $this->boolean('con_stock') : null,
        ];
    }
}
