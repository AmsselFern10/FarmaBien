<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('editar lotes') || $this->user()->can('ajustar inventario');
    }

    public function rules(): array
    {
        $lote = $this->route('lote');
        $loteId = $lote ? (is_object($lote) ? $lote->id : (int) $lote) : null;
        $productoId = $lote ? (is_object($lote) ? $lote->producto_id : null) : null;

        return [
            'numero_lote'       => [
                'required',
                'string',
                'max:100',
                Rule::unique('lotes', 'numero_lote')
                    ->where(fn ($q) => $productoId ? $q->where('producto_id', $productoId) : $q)
                    ->ignore($loteId),
            ],
            'fecha_vencimiento' => ['required', 'date'],
            'proveedor_id'      => ['nullable', 'integer', 'exists:proveedores,id'],
            'motivo_cambio'     => ['required', 'string', 'min:5', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'numero_lote.required'       => 'El número de lote es obligatorio.',
            'numero_lote.unique'         => 'El número de lote ya está registrado para este medicamento.',
            'fecha_vencimiento.required' => 'La fecha de vencimiento es obligatoria.',
            'proveedor_id.exists'        => 'El proveedor seleccionado no existe.',
            'motivo_cambio.required'     => 'El motivo de la modificación es obligatorio para auditoría regulatoria.',
            'motivo_cambio.min'          => 'El motivo debe tener al menos 5 caracteres descriptivos.',
            'motivo_cambio.max'          => 'El motivo no puede exceder los 255 caracteres.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'numero_lote'   => mb_strtoupper(trim((string) $this->input('numero_lote'))),
            'proveedor_id'  => $this->filled('proveedor_id') ? (int) $this->input('proveedor_id') : null,
            'motivo_cambio' => trim((string) $this->input('motivo_cambio')),
        ]);
    }
}
