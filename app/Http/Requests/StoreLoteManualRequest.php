<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLoteManualRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('ajustar inventario');
    }

    public function rules(): array
    {
        $productoId = (int) $this->input('producto_id');

        return [
            'producto_id'       => ['required', 'integer', 'exists:productos,id'],
            'numero_lote'       => [
                'required',
                'string',
                'max:100',
                Rule::unique('lotes', 'numero_lote')->where('producto_id', $productoId),
            ],
            'fecha_vencimiento' => ['required', 'date', 'after:today'],
            'cantidad'          => ['required', 'integer', 'min:1', 'max:1000000'],
            'precio_compra'     => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'proveedor_id'      => ['nullable', 'integer', 'exists:proveedores,id'],
            'motivo'            => ['required', 'string', 'min:5', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'producto_id.required'       => 'Debes seleccionar el medicamento.',
            'producto_id.exists'         => 'El medicamento seleccionado no existe.',
            'numero_lote.required'       => 'El número de lote es obligatorio.',
            'numero_lote.unique'         => 'El número de lote ya está registrado para este medicamento.',
            'fecha_vencimiento.required' => 'La fecha de vencimiento es obligatoria.',
            'fecha_vencimiento.after'    => 'La fecha de vencimiento debe ser una fecha futura.',
            'cantidad.required'          => 'La cantidad inicial es obligatoria.',
            'cantidad.min'               => 'La cantidad debe ser al menos 1 unidad.',
            'cantidad.max'               => 'La cantidad ingresada excede el límite permitido.',
            'proveedor_id.exists'        => 'El proveedor seleccionado no existe.',
            'motivo.required'            => 'El motivo de apertura o creación del lote es obligatorio para auditoría regulatoria.',
            'motivo.min'                 => 'El motivo debe tener al menos 5 caracteres descriptivos.',
            'motivo.max'                 => 'El motivo no puede exceder los 500 caracteres.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'producto_id'   => (int) $this->input('producto_id'),
            'numero_lote'   => mb_strtoupper(trim((string) $this->input('numero_lote'))),
            'cantidad'      => (int) $this->input('cantidad'),
            'precio_compra' => $this->filled('precio_compra') ? (float) $this->input('precio_compra') : null,
            'proveedor_id'  => $this->filled('proveedor_id') ? (int) $this->input('proveedor_id') : null,
            'motivo'        => trim((string) $this->input('motivo')),
        ]);
    }
}
