<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePresentacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('crear presentaciones') || $this->user()->can('crear productos');
    }

    public function rules(): array
    {
        return [
            'producto_id'               => ['required', 'integer', 'exists:productos,id'],
            'nombre'                    => ['required', 'string', 'max:100'],
            'descripcion'               => ['nullable', 'string', 'max:255'],
            'unidades_por_presentacion' => ['required', 'integer', 'min:1', 'max:100000'],
            'precio_compra'             => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'precio_venta'              => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
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
            'producto_id.exists'                 => 'El medicamento seleccionado no existe.',
            'nombre.required'                    => 'El nombre de la presentación es obligatorio.',
            'nombre.max'                         => 'El nombre no puede superar 100 caracteres.',
            'unidades_por_presentacion.required' => 'Las unidades por presentación son obligatorias.',
            'unidades_por_presentacion.min'      => 'Debe haber al menos 1 unidad por presentación.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $codigoBarras = trim((string)$this->input('codigo_barras', ''));
        $descripcion = trim((string)$this->input('descripcion', ''));

        $this->merge([
            'nombre'         => trim((string)$this->input('nombre', '')),
            'descripcion'    => $descripcion !== '' ? $descripcion : null,
            'codigo_barras'  => $codigoBarras !== '' ? $codigoBarras : null,
            'es_unidad_base' => $this->boolean('es_unidad_base'),
            'activo'         => $this->has('activo') ? $this->boolean('activo') : true,
            'orden'          => $this->filled('orden') ? (int)$this->input('orden') : 0,
        ]);
    }
}
