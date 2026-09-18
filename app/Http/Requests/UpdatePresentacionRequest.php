<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePresentacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('editar productos');
    }

    public function rules(): array
    {
        return [
            'producto_id'               => ['required', 'integer', 'exists:productos,id'],
            'nombre'                    => ['required', 'string', 'max:100'],
            'descripcion'               => ['nullable', 'string', 'max:255'],
            'unidades_por_presentacion' => ['required', 'integer', 'min:1'],
            'precio_compra'             => ['nullable', 'numeric', 'min:0'],
            'precio_venta'              => ['nullable', 'numeric', 'min:0'],
            'codigo_barras'             => ['nullable', 'string', 'max:50'],
            'es_unidad_base'            => ['boolean'],
            'activo'                    => ['boolean'],
            'orden'                     => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'producto_id.required'               => 'Debes seleccionar un medicamento.',
            'nombre.required'                    => 'El nombre de la presentacion es obligatorio.',
            'unidades_por_presentacion.required' => 'Las unidades por presentacion son obligatorias.',
            'unidades_por_presentacion.min'      => 'Debe haber al menos 1 unidad por presentacion.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'es_unidad_base' => $this->boolean('es_unidad_base'),
            'activo'         => $this->boolean('activo'),
        ]);
    }
}
